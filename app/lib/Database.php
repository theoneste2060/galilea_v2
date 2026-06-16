<?php
declare(strict_types=1);

/**
 * Thin PDO/SQLite wrapper plus schema migration and first-run seeding.
 */
final class Database
{
    private static ?PDO $pdo = null;
    private static array $config = [];

    public static function init(array $config): void
    {
        self::$config = $config;
    }

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $path = self::$config['db_path'];
        $dir  = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $pdo = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        // Reliability + integrity pragmas.
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');

        self::$pdo = $pdo;
        return $pdo;
    }

    /** Run a prepared statement and return the statement handle. */
    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function value(string $sql, array $params = [])
    {
        return self::run($sql, $params)->fetchColumn();
    }

    public static function migrate(): void
    {
        $pdo = self::pdo();
        $pdo->exec(<<<'SQL'
        CREATE TABLE IF NOT EXISTS admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            full_name TEXT NOT NULL,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'editor',
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS site_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            key TEXT NOT NULL UNIQUE,
            label TEXT NOT NULL,
            value TEXT NOT NULL DEFAULT '',
            group_name TEXT NOT NULL DEFAULT 'general',
            updated_at TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS hero_slides (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            eyebrow TEXT NOT NULL DEFAULT '',
            title TEXT NOT NULL,
            body TEXT NOT NULL DEFAULT '',
            image_path TEXT,
            sort_order INTEGER NOT NULL DEFAULT 0,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS services (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            short_description TEXT NOT NULL DEFAULT '',
            description TEXT NOT NULL DEFAULT '',
            image_path TEXT,
            featured INTEGER NOT NULL DEFAULT 0,
            sort_order INTEGER NOT NULL DEFAULT 0,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS news_posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            excerpt TEXT NOT NULL DEFAULT '',
            body TEXT NOT NULL DEFAULT '',
            category TEXT NOT NULL DEFAULT 'Company News',
            image_path TEXT,
            published INTEGER NOT NULL DEFAULT 1,
            published_at TEXT NOT NULL DEFAULT (datetime('now')),
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS testimonials (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            client_name TEXT NOT NULL,
            company TEXT NOT NULL DEFAULT '',
            country_flag TEXT NOT NULL DEFAULT '',
            quote TEXT NOT NULL DEFAULT '',
            rating INTEGER NOT NULL DEFAULT 5,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS team_members (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT '',
            bio TEXT NOT NULL DEFAULT '',
            phone TEXT NOT NULL DEFAULT '',
            email TEXT NOT NULL DEFAULT '',
            image_path TEXT,
            sort_order INTEGER NOT NULL DEFAULT 0,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS inquiries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name TEXT NOT NULL,
            email TEXT NOT NULL,
            phone TEXT,
            company TEXT,
            service_interest TEXT NOT NULL DEFAULT '',
            message TEXT NOT NULL DEFAULT '',
            status TEXT NOT NULL DEFAULT 'new',
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE,
            full_name TEXT,
            source TEXT NOT NULL DEFAULT 'website',
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS shipments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            reference_number TEXT NOT NULL UNIQUE,
            customer_name TEXT NOT NULL DEFAULT '',
            origin TEXT NOT NULL DEFAULT '',
            destination TEXT NOT NULL DEFAULT '',
            current_stage TEXT NOT NULL DEFAULT '',
            status TEXT NOT NULL DEFAULT 'In Transit',
            stages TEXT NOT NULL DEFAULT '[]',
            updated_at TEXT NOT NULL DEFAULT (datetime('now')),
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS login_attempts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip TEXT NOT NULL,
            attempted_at TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS activity_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            username TEXT NOT NULL DEFAULT '',
            action TEXT NOT NULL,
            detail TEXT NOT NULL DEFAULT '',
            ip TEXT NOT NULL DEFAULT '',
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE INDEX IF NOT EXISTS idx_login_attempts_ip ON login_attempts(ip, attempted_at);
        CREATE INDEX IF NOT EXISTS idx_services_active ON services(is_active, sort_order);
        CREATE INDEX IF NOT EXISTS idx_news_pub ON news_posts(published, published_at);
        SQL);
    }

    /** Seed bootstrap admin + demo content on first run. */
    public static function seed(array $config): void
    {
        $pdo = self::pdo();

        if ((int) self::value('SELECT COUNT(*) FROM admin_users') === 0) {
            self::run(
                'INSERT INTO admin_users (username, full_name, password_hash, role) VALUES (?,?,?,?)',
                [
                    $config['seed_admin_user'],
                    $config['seed_admin_name'],
                    password_hash($config['seed_admin_password'], PASSWORD_DEFAULT),
                    'superadmin',
                ]
            );
        }

        if ((int) self::value('SELECT COUNT(*) FROM site_settings') === 0) {
            $settings = [
                ['site_email', 'Primary Email', 'info@galileagloballogistics.rw', 'contact'],
                ['support_email', 'Support Email', 'support@galileagloballogistics.rw', 'contact'],
                ['phone_rw', 'Rwanda Phone', '+250 788 229 632', 'contact'],
                ['phone_rw_alt', 'Rwanda Phone (alt)', '+250 785 476 239', 'contact'],
                ['phone_cn', 'China Phone', '+86 195 8475 4091', 'contact'],
                ['address_kigali', 'Kigali Address', 'F1-8B Unify Building, Nyarugenge, Kigali', 'contact'],
                ['hero_eyebrow', 'Hero Eyebrow', 'Trusted Trade · Global Reach', 'hero'],
                ['stat_countries', 'Countries Served', '130', 'stats'],
                ['stat_ports', 'Port Partners', '48', 'stats'],
                ['stat_ontime', 'On-time Rate (%)', '99', 'stats'],
                ['stat_support', 'Support Hours', '24', 'stats'],
                ['seo_title', 'SEO Title', 'Galilea Global Logistics — Trusted Trade. Global Reach.', 'seo'],
                ['seo_description', 'SEO Description', 'Sea & air cargo, land freight, customs clearance, warehousing and China business connections from Kigali and Guangzhou to the world.', 'seo'],
            ];
            $stmt = $pdo->prepare('INSERT INTO site_settings (key, label, value, group_name) VALUES (?,?,?,?)');
            foreach ($settings as $s) {
                $stmt->execute($s);
            }
        }

        if ((int) self::value('SELECT COUNT(*) FROM hero_slides') === 0) {
            self::run(
                'INSERT INTO hero_slides (eyebrow, title, body, image_path, sort_order) VALUES (?,?,?,?,?)',
                [
                    'Trusted Trade · Global Reach',
                    'Your cargo moves. Your business grows.',
                    'Sea & air cargo, land freight, customs clearance, warehousing, and China business connections — from Kigali and Guangzhou to the world.',
                    '/assets/img/logistics-hero.jpg',
                    0,
                ]
            );
        }

        if ((int) self::value('SELECT COUNT(*) FROM services') === 0) {
            $services = [
                ['Sea Cargo & Ocean Freight', 'sea-cargo-ocean-freight', 'FCL and LCL (groupage) shipments on all major trade routes.', 'FCL and LCL (groupage) shipments on all major trade routes. Port-to-port and door-to-door, including dangerous goods and car dealership cargo.', '/assets/img/service-ocean-freight.jpg', 1, 1],
                ['Air Freight', 'air-freight', 'Expedited global shipping for time-sensitive and high-value cargo.', 'Expedited global shipping for time-sensitive and high-value cargo, utilizing a network of major international air hubs with full visibility.', '/assets/img/service-air-freight.jpg', 1, 2],
                ['Road & Land Transport', 'road-land-transport', 'Heavy-duty trucking and trailer transport across East Africa.', 'Heavy-duty trucking and trailer transport across East Africa including dangerous goods storage, last-mile delivery, and cross-border road freight.', '/assets/img/service-road-transport.jpg', 0, 3],
                ['Warehousing & Distribution', 'warehousing-distribution', 'Secure storage in Kigali, Guangzhou and Yiwu warehouses.', 'Secure storage in Kigali, Guangzhou (佛山), and Yiwu warehouses with inventory management, dangerous goods facilities, and cross-docking.', '/assets/img/service-warehousing.jpg', 0, 4],
                ['Customs Clearance & Declaration', 'customs-clearance', 'Full customs clearance, maritime and dangerous goods declaration.', 'Full customs clearance, maritime declaration, dangerous goods declaration, duty management, and all border documentation — handled by licensed experts.', null, 0, 5],
                ['China Business Connection', 'china-business-connection', 'Verified suppliers, factory visits, money transfers and sourcing support.', 'We connect you with verified suppliers, handle factory visits, money transfers to suppliers, invitation letters, hotel bookings, and full sourcing support from Guangzhou & Yiwu.', null, 1, 6],
            ];
            $stmt = $pdo->prepare('INSERT INTO services (title, slug, short_description, description, image_path, featured, sort_order) VALUES (?,?,?,?,?,?,?)');
            foreach ($services as $s) {
                $stmt->execute($s);
            }
        }

        if ((int) self::value('SELECT COUNT(*) FROM news_posts') === 0) {
            $news = [
                ['Galilea expands warehousing capacity in Guangzhou & Yiwu', 'guangzhou-yiwu-warehousing', 'Our Foshan and Yiwu warehouses now offer expanded dangerous goods storage and direct China-to-Kigali routing.', '<p>Our Foshan (佛山) and Yiwu warehouses now offer expanded dangerous goods storage, groupage consolidation, and direct China-to-Kigali routing for faster deliveries.</p>', 'East Africa Focus', '/assets/img/service-ocean-freight.jpg'],
                ['New direct air cargo routes: Kigali to Guangzhou now available', 'kigali-guangzhou-air-cargo', 'Fast & reliable air freight service connecting Rwanda directly to China.', '<p>Fast &amp; reliable air freight service connecting Rwanda directly to China, ideal for time-sensitive and high-value shipments.</p>', 'Air Freight', '/assets/img/service-air-freight.jpg'],
                ['Galilea launches financial support & money transfer service', 'financial-support-money-transfer', 'Send money directly to your suppliers in China and beyond — fast, secure and integrated.', '<p>Send money directly to your suppliers in China and beyond — fast, secure, and fully integrated with your shipment booking process.</p>', 'Technology', '/assets/img/service-warehousing.jpg'],
            ];
            $stmt = $pdo->prepare('INSERT INTO news_posts (title, slug, excerpt, body, category, image_path) VALUES (?,?,?,?,?,?)');
            foreach ($news as $n) {
                $stmt->execute($n);
            }
        }

        if ((int) self::value('SELECT COUNT(*) FROM testimonials') === 0) {
            $t = [
                ['James Kariuki', 'MD — Savanna Trade Supplies, Nairobi', '🇰🇪', 'Galilea cut our port clearance from 3 weeks to under 5 days. Not a single delayed shipment in 18 months.', 5],
                ['Sophia Nkosi', 'Supply Chain Director — PharmaBridge Africa', '🇿🇦', 'We ship pharmaceuticals — zero tolerance for errors. Galilea\'s compliance team is meticulous. World-class service.', 5],
                ['Tendai Moyo', 'Founder — Baobab Market, Harare', '🇿🇼', 'Galilea\'s fulfillment solution let us scale from 200 to 2,000 orders a month without missing a beat.', 5],
                ['Aisha Wambua', 'CEO — Rift Valley Fresh Export Co.', '🇰🇪', 'Fresh produce to Europe and the Middle East — timing is everything. Galilea\'s air freight team delivers on time, every time.', 5],
                ['Patrick Mugisha', 'Procurement — Kigali Infrastructure Group', '🇷🇼', 'Moving oversized machinery from China to Rwanda — Galilea handled permits, specialized trucking, everything.', 5],
                ['Clara Osei', 'Operations Manager — West-East Goods, Accra', '🇬🇭', 'The Mombasa SEZ warehouse cut our last-mile costs by 35% in Q1. Direct ERP integration made it seamless.', 5],
            ];
            $stmt = $pdo->prepare('INSERT INTO testimonials (client_name, company, country_flag, quote, rating) VALUES (?,?,?,?,?)');
            foreach ($t as $row) {
                $stmt->execute($row);
            }
        }

        if ((int) self::value('SELECT COUNT(*) FROM team_members') === 0) {
            $team = [
                ['ITANGIGABANYA Jean Damascene', 'Managing Director', 'Leads Galilea\'s global operations and strategic partnerships.', '0788343645', 'info@galileagloballogistics.rw', 1],
                ['Jules BAWESHEMA', 'Sales & Marketing Manager', 'Drives client relationships across East Africa and Asia.', '0788229632', 'info@galileagloballogistics.rw', 2],
                ['China Operations Lead', 'Guangzhou Office', 'Coordinates sourcing, consolidation and freight from China.', '+8619584754091', 'info@galileagloballogistics.rw', 3],
                ['Customs & Compliance', 'Kigali Office · F1-8B Unify', 'Handles clearance, declarations and border documentation.', '+250788562558', 'support@galileagloballogistics.rw', 4],
            ];
            $stmt = $pdo->prepare('INSERT INTO team_members (full_name, role, bio, phone, email, sort_order) VALUES (?,?,?,?,?,?)');
            foreach ($team as $row) {
                $stmt->execute($row);
            }
        }

        if ((int) self::value('SELECT COUNT(*) FROM shipments') === 0) {
            $stages = json_encode([
                ['label' => 'Container received — Mombasa Terminal', 'timestamp' => 'Jan 18, 2025 · 16:00 UTC', 'completed' => true],
                ['label' => 'Loaded on MV GALILEA PIONEER', 'timestamp' => 'Jan 22, 2025 · 14:30 UTC', 'completed' => true],
                ['label' => 'Departed Dubai Jebel Ali Port', 'timestamp' => 'Jan 28, 2025 · 09:45 UTC', 'completed' => true],
                ['label' => 'In transit to Shanghai', 'timestamp' => 'ETA Feb 8', 'completed' => false],
            ]);
            self::run(
                'INSERT INTO shipments (reference_number, customer_name, origin, destination, current_stage, status, stages) VALUES (?,?,?,?,?,?,?)',
                ['GALU1234567', 'Savanna Trade Supplies', 'Nairobi, Kenya', 'Shanghai, China', 'In transit to Shanghai', 'In Transit', $stages]
            );
        }
    }
}
