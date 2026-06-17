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

        CREATE TABLE IF NOT EXISTS menu_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            parent_id INTEGER,
            title TEXT NOT NULL,
            subtitle TEXT NOT NULL DEFAULT '',
            url TEXT NOT NULL DEFAULT '#',
            icon TEXT NOT NULL DEFAULT '',
            is_mega INTEGER NOT NULL DEFAULT 0,
            column_group INTEGER NOT NULL DEFAULT 1,
            sort_order INTEGER NOT NULL DEFAULT 0,
            is_active INTEGER NOT NULL DEFAULT 1
        );

        CREATE TABLE IF NOT EXISTS faqs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            question TEXT NOT NULL,
            answer TEXT NOT NULL DEFAULT '',
            sort_order INTEGER NOT NULL DEFAULT 0,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            body TEXT NOT NULL DEFAULT '',
            meta_description TEXT NOT NULL DEFAULT '',
            is_active INTEGER NOT NULL DEFAULT 1,
            updated_at TEXT NOT NULL DEFAULT (datetime('now')),
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

        CREATE TABLE IF NOT EXISTS api_hits (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip TEXT NOT NULL,
            action TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        );
        CREATE INDEX IF NOT EXISTS idx_api_hits ON api_hits(action, ip, created_at);

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

        // Incremental column additions for the admin_users security features.
        foreach ([
            'totp_secret'      => 'TEXT',
            'totp_enabled'     => 'INTEGER NOT NULL DEFAULT 0',
            'allowed_sections' => "TEXT NOT NULL DEFAULT '[]'",
            'last_login_at'    => 'TEXT',
            'password_changed_at' => 'TEXT',
        ] as $col => $def) {
            if (!self::hasColumn('admin_users', $col)) {
                $pdo->exec("ALTER TABLE admin_users ADD COLUMN $col $def");
            }
        }
    }

    public static function hasColumn(string $table, string $col): bool
    {
        foreach (self::all("PRAGMA table_info($table)") as $c) {
            if ($c['name'] === $col) {
                return true;
            }
        }
        return false;
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
                ['address_kigali', 'Kigali Address', 'F1-8B Unify Building, Inyarurembo, Kiyovu, Nyarugenge, Kigali', 'contact'],
                ['business_hours', 'Business Hours', 'Mon–Fri: 8:00 AM – 6:00 PM · Sat: 9:00 AM – 1:00 PM · Sun: Closed', 'contact'],
                ['whatsapp_number', 'WhatsApp Number (digits only, with country code)', '250788229632', 'contact'],
                ['email_enabled', 'Enable email sending', '0', 'email'],
                ['inquiry_notify_email', 'Send inquiry alerts to', 'info@galileagloballogistics.rw', 'email'],
                ['mail_autoreply', 'Send auto-reply to customers', '0', 'email'],
                ['mail_from_name', 'From name', 'Galilea Global Logistics', 'email'],
                ['mail_from_email', 'From email address', 'info@galileagloballogistics.rw', 'email'],
                ['email_logo', 'Email Logo (header)', '', 'email'],
                ['smtp_host', 'SMTP host (leave blank to use the server mail() function)', '', 'email'],
                ['smtp_port', 'SMTP port', '587', 'email'],
                ['smtp_secure', 'SMTP security', 'tls', 'email'],
                ['smtp_user', 'SMTP username', '', 'email'],
                ['smtp_pass', 'SMTP password', '', 'email'],
                ['hero_eyebrow', 'Hero Eyebrow', 'Trusted Trade · Global Reach', 'hero'],
                ['stat_countries', 'Countries Served', '130', 'stats'],
                ['stat_ports', 'Port Partners', '48', 'stats'],
                ['stat_ontime', 'On-time Rate (%)', '99', 'stats'],
                ['stat_support', 'Support Hours', '24', 'stats'],
                ['seo_title', 'SEO Title', 'Galilea Global Logistics — Trusted Trade. Global Reach.', 'seo'],
                ['seo_description', 'SEO Description', 'Sea & air cargo, land freight, customs clearance, warehousing and China business connections from Kigali and Guangzhou to the world.', 'seo'],
                ['site_url', 'Canonical Site URL (e.g. https://galileagloballogistics.rw)', 'https://galileagloballogistics.rw', 'seo'],
                ['og_image', 'Default Social Share Image', '/assets/img/logo.jpeg', 'seo'],
                ['org_legal_name', 'Legal Organization Name', 'Galilea Global Logistics LTD', 'seo'],
                ['company_reg', 'Company Registration / TIN', 'TIN 155855944 · Registered in Rwanda (RDB)', 'seo'],
                ['analytics_id', 'Google Analytics ID (G-XXXXXXXXXX)', '', 'analytics'],
                ['twitter_handle', 'Twitter/X Handle (@name)', '', 'social'],
                ['social_linkedin', 'LinkedIn URL', '', 'social'],
                ['social_facebook', 'Facebook URL', '', 'social'],
                ['social_youtube', 'YouTube URL', '', 'social'],
                ['social_facebook_enabled', 'Facebook', '0', 'social'],
                ['social_facebook_url', 'Facebook URL', '', 'social'],
                ['social_instagram_enabled', 'Instagram', '0', 'social'],
                ['social_instagram_url', 'Instagram URL', '', 'social'],
                ['social_linkedin_enabled', 'LinkedIn', '0', 'social'],
                ['social_linkedin_url', 'LinkedIn URL', '', 'social'],
                ['social_youtube_enabled', 'YouTube', '0', 'social'],
                ['social_youtube_url', 'YouTube URL', '', 'social'],
                ['social_twitter_enabled', 'X / Twitter', '0', 'social'],
                ['social_twitter_url', 'X / Twitter URL', '', 'social'],
                ['social_tiktok_enabled', 'TikTok', '0', 'social'],
                ['social_tiktok_url', 'TikTok URL', '', 'social'],
                ['social_whatsapp_enabled', 'WhatsApp Channel', '0', 'social'],
                ['social_whatsapp_url', 'WhatsApp Channel URL', '', 'social'],
                ['geo_placename', 'Geo Placename', 'Kigali, Rwanda', 'geo'],
                ['geo_lat', 'Headquarters Latitude', '-1.9441', 'geo'],
                ['geo_lng', 'Headquarters Longitude', '30.0619', 'geo'],
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
                ['Sea Cargo & Ocean Freight', 'sea-cargo-ocean-freight',
                 'FCL and LCL (groupage) shipments on every major trade lane, port-to-port or door-to-door.',
                 '<p>Ocean freight is the backbone of cost-effective international trade, and it is where Galilea moves the largest share of our clients\' cargo. Whether you are importing a single pallet or a full container load from Guangzhou to Kigali, we book the space, manage the documentation, and keep you informed at every milestone.</p>'
                 . '<h3>What we handle</h3><ul>'
                 . '<li><strong>Full Container Load (FCL)</strong> — dedicated 20ft and 40ft containers, including high-cube and reefer units for temperature-controlled goods. We secure competitive carrier rates and provide door-to-door tracking from origin to destination.</li>'
                 . '<li><strong>Less-than-Container Load (LCL / groupage)</strong> — pay only for the space you use through our weekly consolidation boxes out of South China. Your goods are consolidated at our Foshan warehouse alongside compatible cargo, then shipped under a single bill of lading.</li>'
                 . '<li><strong>Door-to-door delivery</strong> — we coordinate the inland leg on both ends so your cargo arrives at your warehouse, not just the port. Our team arranges trucking, transit bonds and last-mile delivery across Rwanda and the region.</li>'
                 . '<li><strong>Special cargo</strong> — dangerous goods (IMO classes 2–9), out-of-gauge project cargo, oversized machinery, and vehicle shipments for dealerships and fleet operators. Every consignment is risk-assessed and stowed according to international standards.</li>'
                 . '</ul>'
                 . '<h3>Why importers choose Galilea for ocean freight</h3><p>Our long-standing relationships with major carriers and our own consolidation warehouses in Foshan and Yiwu mean competitive rates and reliable sailing schedules. Every shipment is tracked end-to-end and cleared by our in-house customs team, so there are no surprises at the border.</p>'
                 . '<p>From the moment your cargo is booked, you receive a dedicated operations contact who oversees documentation, loading, transit milestones and final delivery. We handle the bill of lading, packing list, commercial invoice, certificate of origin and any destination-specific requirements — so your only job is to receive the goods when they arrive.</p>'
                 . '<p>We serve a broad mix of clients: construction companies importing heavy equipment, retailers bringing in consumer goods, manufacturers sourcing raw materials, and aid organisations moving relief supplies. Each shipment receives the same level of attention — proactive communication, accurate documentation and a relentless focus on on-time delivery.</p>'
                 . '<p>From the Far East to the heart of East Africa, your cargo is in experienced hands.</p>',
                 '/assets/img/service-ocean-freight.jpg', 1, 1],

                ['Air Freight', 'air-freight',
                 'Expedited global air shipping for time-sensitive, high-value and perishable cargo.',
                 '<p>When time is critical, air freight delivers. Galilea moves urgent and high-value shipments through a network of major international hubs, with direct routing between Kigali, Guangzhou and the world\'s key gateways. From a few kilograms of spare parts to multi-tonne consignments, we match the right service level to your deadline and budget.</p>'
                 . '<h3>Air services we offer</h3><ul>'
                 . '<li><strong>Express &amp; consolidated air freight</strong> — next-flight-out options for emergencies when every hour counts, and economical consolidations for planned shipments where cost and speed need to be balanced. We consolidate at major hubs including Guangzhou (CAN), Dubai (DXB), Addis Ababa (ADD) and Istanbul (IST).</li>'
                 . '<li><strong>Perishables &amp; pharma</strong> — temperature-aware handling for fresh produce, flowers, chilled meat and healthcare products. We work with carriers that offer active temperature-controlled containers and priority boarding for time-and-temperature-sensitive cargo.</li>'
                 . '<li><strong>High-value &amp; sensitive goods</strong> — electronics, machinery components, automotive parts and business-critical documents. Our secure chain-of-custody protocol ensures every hand-off is logged and every package is trackable in real time.</li>'
                 . '<li><strong>Charter solutions</strong> — for oversized or exceptionally urgent project cargo that cannot wait for scheduled services. We can arrange dedicated freighter aircraft for large-volume or heavy-lift requirements.</li>'
                 . '</ul>'
                 . '<h3>Speed without the stress</h3><p>Our team pre-clears documentation before the aircraft lands, so your goods move from tarmac to truck with minimum dwell time. We handle the air waybill, customs clearance, phytosanitary certificates and any special permits required for regulated goods.</p>'
                 . '<p>You receive proactive status updates at every milestone — booking confirmed, cargo received at origin warehouse, flown out, customs cleared, delivered. There is a single point of contact from booking to final delivery, giving you the kind of visibility that lets you make promises to your own customers with confidence.</p>'
                 . '<p>Whether you are a manufacturer waiting for a critical machine part, a retailer restocking ahead of peak season, or a humanitarian organisation shipping emergency supplies, Galilea delivers air freight solutions that are fast, reliable and fully transparent.</p>',
                 '/assets/img/service-air-freight.jpg', 1, 2],

                ['Road & Land Transport', 'road-land-transport',
                 'Heavy-duty trucking, trailer haulage and cross-border road freight across East Africa.',
                 '<p>Getting cargo off the ship or plane is only half the journey. Galilea operates and contracts a reliable fleet to move goods overland across Rwanda and the wider East African Community — from the ports of Mombasa and Dar es Salaam to Kigali and beyond.</p>'
                 . '<h3>Our road network covers</h3><ul>'
                 . '<li><strong>Port-to-door haulage</strong> — coordinated collection from Mombasa and Dar es Salaam with live transit updates. We manage the container release, port-side logistics and the full road transport corridor, including all bond and customs checkpoints along the way.</li>'
                 . '<li><strong>Cross-border freight</strong> — regular services to and from Rwanda, Uganda, Kenya, Tanzania, Burundi and the Democratic Republic of Congo. Every shipment is accompanied by complete COMESA and EAC transit documentation, managed by our in-house compliance team.</li>'
                 . '<li><strong>Specialised equipment</strong> — flatbeds for construction steel and timber, low-loaders for heavy machinery and earth-moving equipment, curtain-siders for general cargo, and secure transport for dangerous goods (ADR/IMDG classified). Our fleet is maintained to international standards and each vehicle is GPS-tracked.</li>'
                 . '<li><strong>Last-mile delivery</strong> — final distribution to your warehouse, construction site, retail outlet or residential address across Kigali, other Rwandan cities and regional urban centres. We operate smaller trucks for narrow-access deliveries and provide proof-of-delivery documentation for every consignment.</li>'
                 . '</ul>'
                 . '<h3>Built for African roads</h3><p>Cross-border trucking demands more than a truck — it demands knowledge of border posts, weighbridges, transit bonds and the bureaucratic realities of moving freight through multiple jurisdictions. Our drivers and operations team navigate the Northern and Central Corridors every week, so your cargo keeps moving while we handle the checkpoints, documentation and the inevitable surprises along the way.</p>'
                 . '<p>We maintain strong relationships with customs authorities, port operators and regulatory bodies in every country we serve. When a border post is congested, a road is under construction, or a new regulation comes into effect, we adapt your routing and keep you informed. Your cargo does not sit idle while we figure it out — because we have already been through that crossing dozens of times before.</p>'
                 . '<p>Whether you need a single truckload from Mombasa to Kigali or an ongoing distribution network across the Great Lakes region, Galilea provides road transport that is reliable, traceable and professionally managed from start to finish.</p>',
                 '/assets/img/service-road-transport.jpg', 0, 3],

                ['Warehousing & Distribution', 'warehousing-distribution',
                 'Secure, managed storage in Kigali, Guangzhou and Yiwu with inventory and cross-docking.',
                 '<p>A warehouse should do more than hold boxes — it should give you control over your supply chain. Galilea offers secure, managed storage on both ends of the China–East Africa trade lane, letting you consolidate purchases, hold stock close to your customers, and ship only when you are ready.</p>'
                 . '<h3>Facilities &amp; services</h3><ul>'
                 . '<li><strong>China consolidation</strong> — receive goods from multiple suppliers at our Foshan (佛山) and Yiwu (义乌) warehouses, then combine them into one cost-efficient shipment. Our team inspects incoming goods, cross-checks quantities against your purchase orders, and repackages items as needed before consolidation.</li>'
                 . '<li><strong>Kigali distribution centre</strong> — local storage with pick-and-pack and onward delivery across Rwanda. Your inventory is stored securely in our managed facility with 24/7 monitoring, fire suppression and pest control. Orders are picked, packed and dispatched on your instruction — same-day or next-day service available.</li>'
                 . '<li><strong>Inventory management</strong> — itemised stock records with goods-in and goods-out reporting. You receive regular inventory reports and can request real-time stock status at any time. Our system tracks items by SKU, batch number and expiry date where applicable.</li>'
                 . '<li><strong>Cross-docking &amp; dangerous-goods storage</strong> — fast transfer between inbound and outbound transport for time-sensitive shipments, and compliant handling of regulated cargo in designated storage zones.</li>'
                 . '</ul>'
                 . '<h3>One partner, both ends</h3><p>Because we operate storage in China and Rwanda, you deal with a single team and a single set of records across your entire supply chain. Sourcing from twelve different factories across Guangdong and Zhejiang? Send them all to our Yiwu warehouse, and we will check, consolidate, and ship — turning a logistical headache into one clean delivery.</p>'
                 . '<p>Our warehousing service is particularly valuable for importers who want to hold buffer stock close to their market without investing in their own facility. You pay only for the space and handling you use, with no long-term commitment. As your business grows, your storage scales with you — same partner, same processes, same visibility.</p>'
                 . '<p>From consolidation in China to distribution in Rwanda, Galilea warehousing gives you the flexibility to manage inventory efficiently, reduce per-unit shipping costs, and respond faster to market demand.</p>',
                 '/assets/img/service-warehousing.jpg', 0, 4],

                ['Customs Clearance & Declaration', 'customs-clearance',
                 'Licensed customs clearance, maritime and dangerous-goods declaration, and duty management.',
                 '<p>Customs is where shipments stall — unless they are handled by people who do it every day. Galilea\'s licensed clearing agents manage the full declaration process so your cargo clears quickly and correctly, with duties calculated accurately and documentation that stands up to inspection.</p>'
                 . '<h3>What we take care of</h3><ul>'
                 . '<li><strong>Import &amp; export clearance</strong> — complete entries lodged with the Rwanda Revenue Authority, Kenya Revenue Authority, Tanzania Revenue Authority and regional customs bodies. We handle the single administrative document (SAD), valuation, tariff classification and any post-clearance queries.</li>'
                 . '<li><strong>Maritime &amp; dangerous-goods declaration</strong> — correct classification and compliant paperwork for regulated cargo including IMDG-classed goods, hazardous materials and controlled substances. We prepare the dangerous goods note, container packing certificate and multimodal dangerous goods form.</li>'
                 . '<li><strong>Duty &amp; tax management</strong> — accurate HS-code classification and customs valuation to avoid overpayment and penalties. Our team reviews your product descriptions and identifies applicable duty relief schemes, free trade agreement preferences and temporary import provisions that can reduce your landed cost.</li>'
                 . '<li><strong>Permits &amp; certificates</strong> — coordination of pre-shipment inspections, standards bureau certificates, phytosanitary certificates, veterinary permits, import licences and any other regulatory approvals required for your specific commodity.</li>'
                 . '</ul>'
                 . '<h3>Clear the border, not your schedule</h3><p>A single mis-declared line can hold a container for days and add unexpected demurrage, detention and storage costs. Our specialists pre-validate every entry before submission and liaise directly with customs officers to resolve queries before they become delays.</p>'
                 . '<p>We provide a transparent breakdown of every duty, tax and fee — no hidden charges, no guesswork. If you qualify for a duty remission or a preferential rate under a trade agreement, we make sure you receive it. When an inspection is required, we coordinate the timing and logistics so it causes minimal disruption to your supply chain.</p>'
                 . '<p>Whether you are importing commercial goods, machinery, vehicles, food products or raw materials, Galilea customs clearance gets your cargo through the border with speed, accuracy and complete compliance.</p>',
                 null, 0, 5],

                ['China Business Connection', 'china-business-connection',
                 'Verified suppliers, factory visits, supplier payments and end-to-end sourcing support.',
                 '<p>For many businesses the hardest part of importing from China is everything that happens <em>before</em> the cargo ships — finding a trustworthy supplier, verifying quality, and paying safely. Galilea\'s on-the-ground team in Guangzhou and Yiwu acts as your eyes, ears and hands in China.</p>'
                 . '<h3>How we support your sourcing</h3><ul>'
                 . '<li><strong>Supplier sourcing &amp; verification</strong> — we identify, vet and visit factories so you deal with genuine manufacturers, not middlemen or trading companies. Our team assesses production capacity, quality control systems, export experience and compliance with international standards. You receive a detailed supplier report including photos, certifications and our recommendation.</li>'
                 . '<li><strong>Factory visits &amp; quality checks</strong> — physical inspection of goods before they are paid for and shipped. We check quantity, specification, packaging, labelling and overall workmanship against your approved sample or order sheet. Photos and a written inspection report are provided within 24 hours of the visit.</li>'
                 . '<li><strong>Supplier payments &amp; money transfer</strong> — secure, fast settlement to your suppliers in RMB, integrated with your booking. We manage the payment process so you never send money to an unknown supplier without confirmation that your goods exist and are ready for shipment.</li>'
                 . '<li><strong>Travel &amp; trade support</strong> — invitation letters (for business visa applications), hotel bookings, airport transfers and Mandarin or Cantonese interpreters for buyers visiting China for the first time. We can accompany you to Canton Fair or to supplier meetings across Guangdong, Zhejiang and Fujian provinces.</li>'
                 . '</ul>'
                 . '<h3>Buy from China with confidence</h3><p>We bridge the language, distance and trust gap that trips up so many first-time and growing importers. From the first supplier introduction to the final delivery in Kigali, Galilea manages the whole chain — so you can focus on selling, not chasing factories across a 10,000-kilometre supply line.</p>'
                 . '<p>Our China Business Connection service has helped hundreds of African importers source products ranging from furniture and clothing to electronics, construction materials and industrial machinery. We know the markets, the factory clusters and the negotiation culture, and we use that knowledge to protect your interests.</p>'
                 . '<p>If you are considering importing from China but are not sure where to start, Galilea provides the trusted on-the-ground presence that turns a daunting process into a manageable, profitable business activity.</p>',
                 null, 1, 6],

                ['Project Cargo', 'project-cargo',
                 'Specialised handling of oversized, heavy and high-value equipment — engineering, routing and permits.',
                 '<p>Some shipments do not fit in a container or a standard process. Project cargo — industrial machinery, construction plant, generators, mining equipment, vehicles and other out-of-gauge or high-value equipment — demands route surveys, lifting plans, special permits and careful coordination. Galilea has the experience to move it safely from origin to site.</p>'
                 . '<h3>How we handle project cargo</h3><ul>'
                 . '<li><strong>Engineering &amp; route survey</strong> — assessing weights, dimensions and the road, port and bridge constraints along the planned route. Our team identifies clearance issues, weight limits, overhead obstructions and turning-radius challenges before the cargo ever leaves the factory.</li>'
                 . '<li><strong>Specialised equipment</strong> — flatbeds, low-loaders, extendable trailers, cranes and lifting gear matched to the specific weight, dimensions and configuration of your cargo. We arrange all handling equipment at origin, transit points and destination.</li>'
                 . '<li><strong>Permits &amp; escorts</strong> — abnormal-load permits and convoy arrangements for cross-border moves. We liaise with transport authorities in each jurisdiction to secure the required permits, arrange police escorts where necessary, and plan for road closures or diversions.</li>'
                 . '<li><strong>End-to-end coordination</strong> — a single project lead from feasibility assessment through to delivery on site. We manage the entire transport chain including port-side heavy lift, inland barge where applicable, overland haulage and final positioning on your premises.</li>'
                 . '</ul>'
                 . '<h3>Built for the difficult moves</h3><p>We have delivered oversized machinery from China to project sites across Rwanda, handling the permits, specialised trucking and on-site delivery from start to finish. When the cargo is critical and the margins for error are small, experience matters.</p>'
                 . '<p>Project cargo moves are complex by nature — they require meticulous planning, clear communication between all stakeholders, and the ability to adapt when conditions on the ground change. Our project managers bring decades of combined experience in heavy-lift logistics, engineering transport and cross-border permitting.</p>'
                 . '<p>From a single oversized generator to a complete cement plant, Galilea delivers project cargo solutions that are engineered for safety, planned for reliability and executed with precision.</p>',
                 null, 0, 7],

                ['E-commerce Fulfilment', 'ecommerce-fulfilment',
                 'Scalable storage, pick-and-pack and dispatch for online retailers — grow without the growing pains.',
                 '<p>Selling online means your logistics has to keep pace with your orders — every day, without errors. Galilea offers scalable e-commerce fulfilment so you can hold stock close to your customers, ship the moment an order comes in, and scale up smoothly as your business grows.</p>'
                 . '<h3>What our fulfilment covers</h3><ul>'
                 . '<li><strong>Receiving &amp; storage</strong> — your inventory held securely in our Kigali and China facilities. We receive incoming stock, verify quantities against your purchase order, inspect for damage and update your inventory records within one business day.</li>'
                 . '<li><strong>Pick, pack &amp; dispatch</strong> — accurate order fulfilment with same- or next-day handling. Our team picks items from shelf to packing station, packs them securely in appropriate packaging (plain or branded), labels each parcel and dispatches through the most cost-effective carrier.</li>'
                 . '<li><strong>Inventory visibility</strong> — clear stock records and regular goods-in and goods-out reporting. You know exactly what is in stock, what is on order and what has been shipped — no spreadsheets, no guesswork, no overselling.</li>'
                 . '<li><strong>Returns handling</strong> — a simple process for receiving, inspecting and restocking returned items. We can quarantine faulty goods for supplier claims, refurbish returned items where possible, or dispose of unsaleable stock according to your instructions.</li>'
                 . '</ul>'
                 . '<h3>Scale without the headache</h3><p>One of our clients grew from 200 to over 2,000 orders a month without missing a beat, because sourcing, warehousing and last-mile delivery all ran through one partner. When you are launching a new product line or entering a new market, the last thing you need to worry about is whether the fulfilment operation can handle the volume.</p>'
                 . '<p>Our e-commerce fulfilment service is designed for online retailers, marketplace sellers and direct-to-consumer brands who need reliable, scalable logistics without the overhead of building and managing their own warehouse operation. You pay a simple per-order fee with monthly billing — no minimums, no complicated tier structures.</p>'
                 . '<p>Whether you are just starting out or already shipping thousands of orders, Galilea provides the fulfilment infrastructure and operational expertise to help you grow without the growing pains.</p>',
                 null, 0, 8],
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
                ['Savanna Trade Supplies', 'Wholesale Distribution · Nairobi, Kenya', '🇰🇪', 'Galilea cut our port-to-warehouse clearance from three weeks to under five days. In eighteen months of weekly imports from China, we have not had a single delayed shipment. They have become a genuine extension of our supply-chain team.', 5],
                ['PharmaBridge Africa Ltd', 'Pharmaceutical Importer · Johannesburg', '🇿🇦', 'We move temperature-sensitive medicines where there is zero tolerance for error. Galilea\'s compliance and documentation are meticulous, and their air-freight handling has kept our cold chain intact on every consignment.', 5],
                ['Baobab Market', 'E-commerce Retail · Harare, Zimbabwe', '🇿🇼', 'Galilea\'s consolidation and fulfilment let us scale from 200 to over 2,000 orders a month without missing a beat. Sourcing, warehousing and last-mile delivery all run through one partner — it transformed how we operate.', 5],
                ['Rift Valley Fresh Exporters', 'Perishables Export · Nakuru, Kenya', '🇰🇪', 'For fresh produce bound for Europe and the Middle East, timing is everything. Galilea\'s air-freight team consistently delivers on schedule, and their pre-clearance process means our cargo never sits waiting at the airport.', 5],
                ['Kigali Infrastructure Group', 'Construction & Machinery · Kigali', '🇷🇼', 'We needed oversized machinery moved from China to a project site in Rwanda. Galilea handled the permits, the specialised trucking and the cross-border paperwork end to end. Everything arrived intact and on time.', 5],
                ['West-East Goods Ltd', 'Consumer Goods Import · Accra, Ghana', '🇬🇭', 'Galilea\'s warehousing and distribution cut our last-mile costs by 35% in a single quarter. Their reporting gives us full visibility of stock, and the team is responsive whenever priorities shift.', 5],
            ];
            $stmt = $pdo->prepare('INSERT INTO testimonials (client_name, company, country_flag, quote, rating) VALUES (?,?,?,?,?)');
            foreach ($t as $row) {
                $stmt->execute($row);
            }
        }

        if ((int) self::value('SELECT COUNT(*) FROM team_members') === 0) {
            $team = [
                ['Jean Damascene ITANGIGABANYA', 'Managing Director', 'Leads Galilea\'s global operations, strategy and partnerships across the China–East Africa corridor.', '+250 788 229 632', 'info@galileagloballogistics.rw', 1],
                ['Jean Paul MULIGANDE', 'Chairperson of the Board', 'Provides governance and strategic direction for Galilea Global Logistics.', '', 'info@galileagloballogistics.rw', 2],
                ['Jules BAWESHEMA', 'Sales & Marketing Manager', 'Drives client relationships and growth across East Africa and Asia.', '+250 785 476 239', 'info@galileagloballogistics.rw', 3],
                ['China Operations', 'Guangzhou & Yiwu Offices', 'Coordinates supplier sourcing, consolidation and freight out of South China.', '+86 195 8475 4091', 'info@galileagloballogistics.rw', 4],
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

        if ((int) self::value('SELECT COUNT(*) FROM faqs') === 0) {
            $faqs = [
                ['What shipping services does Galilea Global Logistics offer?', 'Galilea offers sea cargo and ocean freight (FCL & LCL), air freight, road and land transport, warehousing and distribution, customs clearance and declaration, and China business connection including verified supplier sourcing and money transfers. We operate across 130+ countries with offices in Kigali, Guangzhou and Yiwu.', 1],
                ['How do I track my shipment?', 'Enter your container number, booking reference, or bill of lading on our Track & Trace page to see live status and milestones. You can also call our support line at +250 788 229 632 for real-time updates.', 2],
                ['Does Galilea handle customs clearance and dangerous goods?', 'Yes. We provide full customs clearance, maritime declaration, dangerous-goods declaration and duty management. Our compliance team ensures smooth border crossings for all cargo types, including pharmaceuticals and oversized equipment.', 3],
                ['Can Galilea help me source products from China?', 'Absolutely. Through our Guangzhou and Yiwu offices we connect you with verified suppliers, arrange factory visits, and provide money-transfer support — your direct link to Chinese manufacturers.', 4],
                ['What is the typical transit time from China to Rwanda?', 'Ocean freight from Guangzhou to Kigali typically takes 25–35 days (FCL) or 30–40 days (LCL). Air freight takes 3–7 days depending on the route. Contact us for a specific quote based on your shipment details.', 5],
                ['How do I request a freight quote?', 'Use the Get a Quote form on our Contact page, email info@galileagloballogistics.rw, or call +250 788 229 632. Our team typically responds within one business day with a tailored quote.', 6],
                ['Does Galilea handle oversized or project cargo?', 'Yes. We specialise in project cargo — oversized, heavy or high-value equipment that requires custom engineering, routing and permits. We have moved machinery from China to Rwanda, including permits and specialised trucking.', 7],
                ['What countries and regions does Galilea serve?', 'Galilea operates across 130+ countries with a focus on the China–East Africa corridor. We have offices in Kigali (Rwanda), Guangzhou and Yiwu (China), with partners across Europe, the Middle East and Africa.', 8],
                ['Does Galilea offer warehousing services?', 'Yes. We operate secure warehousing in Kigali, Guangzhou and Yiwu with inventory-management systems, and we provide e-commerce fulfilment including pick-and-pack and dispatch.', 9],
                ['What makes Galilea different from other logistics companies in Rwanda?', 'Three things: (1) physical offices in Kigali, Guangzhou and Yiwu giving you direct access to Chinese suppliers; (2) 24/7 operations support with a 99% on-time delivery rate; and (3) genuine end-to-end service — sea, air, land, customs, warehousing, sourcing and financial support — all under one roof.', 10],
            ];
            $stmt = $pdo->prepare('INSERT INTO faqs (question, answer, sort_order) VALUES (?,?,?)');
            foreach ($faqs as $f) { $stmt->execute($f); }
        }

        if ((int) self::value('SELECT COUNT(*) FROM pages') === 0) {
            $pages = [
                ['About Galilea', 'about',
                 '<p>Galilea Global Logistics LTD is a premier multimodal logistics provider specialising in end-to-end supply chain management, headquartered in Kigali, Rwanda. With infrastructure spanning sea, air and land, we operate at the intersection of traditional reliability and modern, technology-driven innovation.</p>'
                 . '<p>We don\'t just transport cargo; we manage the heartbeat of your business. From the first mile of manufacturing to the final mile of delivery, our team ensures your products navigate the global marketplace with precision and speed.</p>'
                 . '<h2>Our Vision</h2><p>To be the most reliable bridge between global markets, empowering businesses through seamless, technology-driven supply chain solutions.</p>'
                 . '<h2>Our Mission</h2><p>To simplify the complexities of international trade by providing transparent, efficient and scalable logistics services that guarantee "Trusted Trade and Global Reach."</p>'
                 . '<h2>Who we are</h2><p>Founded to solve the real frictions of trading between Africa and Asia, Galilea pairs local knowledge of East African corridors and customs with a permanent presence in the manufacturing heartlands of South China. From our offices in Kigali, Guangzhou and Yiwu, we manage shipments end to end so our clients can buy, sell and grow with confidence.</p>'
                 . '<h2>What we do</h2><ul>'
                 . '<li>Sea cargo &amp; ocean freight (FCL and LCL groupage)</li>'
                 . '<li>Air freight for urgent, high-value and perishable goods</li>'
                 . '<li>Road &amp; cross-border land transport across the EAC</li>'
                 . '<li>Warehousing, consolidation and distribution in China and Rwanda</li>'
                 . '<li>Licensed customs clearance and declaration</li>'
                 . '<li>China sourcing, supplier verification and payments</li>'
                 . '</ul>'
                 . '<h2>Why partner with Galilea</h2><ul>'
                 . '<li><strong>Global network</strong> — access to every major trade hub via sea, air and land, across 130+ countries.</li>'
                 . '<li><strong>Operational transparency</strong> — real-time tracking and clear communication at every milestone.</li>'
                 . '<li><strong>Scalability</strong> — solutions that grow with your business, from single pallets to massive project cargo.</li>'
                 . '<li><strong>Market expertise</strong> — deep local knowledge of the African and Chinese trade landscape.</li>'
                 . '</ul>'
                 . '<h2>Our promise</h2><p>In an unpredictable world, your supply chain shouldn\'t be a gamble. We provide the stability, technology and Trusted Trade your brand deserves. Every shipment is tracked, every duty is transparent, and every client has a named point of contact.</p>',
                 'Galilea Global Logistics LTD — a Kigali-headquartered multimodal freight forwarder connecting East Africa and China by sea, air and land.'],

                ['Careers', 'careers',
                 '<p>Galilea is a fast-growing logistics company operating across Rwanda and China. Our people are the reason cargo keeps moving and clients keep coming back — and we are always looking for talented, dependable team members to grow with us.</p>'
                 . '<h2>Where we hire</h2><ul>'
                 . '<li><strong>Operations &amp; freight coordination</strong> — Kigali-based roles overseeing shipment planning, carrier coordination and customer communication across sea, air and road transport.</li>'
                 . '<li><strong>Customs &amp; clearance specialists</strong> — Kigali. Prepare and lodge customs declarations, manage permits and liaise with the Rwanda Revenue Authority on behalf of our clients.</li>'
                 . '<li><strong>Sourcing &amp; warehouse</strong> — Guangzhou &amp; Yiwu, China. Conduct factory visits, quality inspections and manage consolidation warehouse operations.</li>'
                 . '<li><strong>Sales &amp; client success</strong> — Kigali. Build relationships with importers, manufacturers and trading companies across Rwanda and the region.</li>'
                 . '</ul>'
                 . '<h2>Why Galilea</h2><p>You will work on real international trade, learn both the African and Chinese sides of the supply chain, and have room to take ownership early. We value reliability, clear communication and a problem-solving mindset over formal titles. Our team is lean, which means your contribution is visible and your growth is not limited by hierarchy.</p>'
                 . '<p>We offer competitive compensation, a collaborative work environment and the opportunity to build a career at the intersection of Africa and Asia\'s fastest-growing trade corridors.</p>'
                 . '<h2>How to apply</h2><p>Send your CV and a short note about the role that interests you to <a href="mailto:info@galileagloballogistics.rw">info@galileagloballogistics.rw</a>. We review applications on a rolling basis and respond to every candidate. Please include "Application — [Role Title]" in the subject line.</p>',
                 'Careers at Galilea Global Logistics — operations, customs, sourcing and sales roles across Rwanda and China.'],

                ['Privacy Policy', 'privacy',
                 '<p>This policy explains how Galilea Global Logistics Ltd. ("Galilea", "we", "us") collects, uses and protects your personal data when you use our website and services.</p>'
                 . '<h2>1. Information we collect</h2><ul>'
                 . '<li><strong>Information you give us</strong> — your name, company name, email address, phone number and message when you submit a quote request, contact form or newsletter sign-up. We also collect shipment details necessary to provide our logistics services.</li>'
                 . '<li><strong>Technical data</strong> — basic, non-identifying information such as the pages you visit and the browser you use, collected through cookies (with your consent where required by law).</li>'
                 . '</ul>'
                 . '<h2>2. How we use your information</h2><p>We use your data to respond to enquiries, prepare quotations, provide and improve our logistics services, manage shipments, communicate with you about your bookings, and — where you have opted in — send occasional industry updates and company news. We do not sell your personal data to third parties.</p>'
                 . '<h2>3. Legal basis</h2><p>We process your data on the following bases: performance of a contract (to provide the logistics services you have requested), your consent (for optional cookies and marketing communications), and our legitimate interest in operating and improving our business.</p>'
                 . '<h2>4. Sharing</h2><p>We share information only as needed to deliver your shipment — for example with shipping lines, airlines, trucking companies, customs authorities and our offices in China. We also share data with trusted service providers (IT hosting, payment processing) who are bound by confidentiality agreements. We may disclose data where required by applicable law.</p>'
                 . '<h2>5. Data retention</h2><p>We keep personal data only for as long as necessary to fulfil the purposes described in this policy, to comply with legal and accounting obligations (typically five years after the end of a business relationship), and to resolve disputes.</p>'
                 . '<h2>6. Your rights</h2><p>Under applicable data protection law, you have the right to request access to, correction of, or deletion of your personal data. You may also withdraw consent for marketing communications at any time by clicking the unsubscribe link in any email or by contacting us directly. To exercise these rights, email <a href="mailto:info@galileagloballogistics.rw">info@galileagloballogistics.rw</a>.</p>'
                 . '<h2>7. Contact</h2><p>Questions about this policy or requests to exercise your data protection rights can be sent to <a href="mailto:info@galileagloballogistics.rw">info@galileagloballogistics.rw</a> or to our head office in Nyarugenge, Kigali, Rwanda.</p>',
                 'How Galilea Global Logistics collects, uses and protects your personal data.'],

                ['Terms of Service', 'terms',
                 '<p>These Terms of Service ("Terms") govern your access to and use of the Galilea Global Logistics website and the services we provide. By using our website or engaging our services, you confirm that you have read, understood and agree to these Terms.</p>'
                 . '<h2>1. Services</h2><p>Galilea provides freight forwarding, customs clearance, warehousing, road and air transport, project cargo handling and China sourcing support services. The specific scope, pricing, transit times and terms for any shipment are set out in the quotation, booking confirmation or service agreement applicable to that engagement.</p>'
                 . '<h2>2. Quotations &amp; bookings</h2><p>Quotations are based on the information you provide (cargo type, weight, dimensions, origin, destination) and are valid for the period stated on the quote. Rates may change if cargo details, routing, third-party charges (including but not limited to carrier surcharges, fuel adjustments, currency fluctuations or port fees) differ materially from those quoted. A booking is confirmed only when we issue a written booking confirmation.</p>'
                 . '<h2>3. Client responsibilities</h2><p>You are responsible for: (a) providing accurate and complete cargo descriptions, (b) ensuring that goods are legal to import and export in all relevant jurisdictions, (c) obtaining and providing any necessary licences, permits or certificates, (d) providing accurate documentation in a timely manner, and (e) paying all applicable duties, taxes, levies and charges associated with your shipment.</p>'
                 . '<h2>4. Prohibited goods</h2><p>You may not tender for transport any goods that are illegal, hazardous without proper declaration, or otherwise prohibited by applicable law or carrier policy. Galilea reserves the right to inspect and refuse any shipment that does not comply with these requirements.</p>'
                 . '<h2>5. Limitation of liability</h2><p>Our liability is limited to the extent permitted under applicable freight-forwarding conventions (including the Hague-Visby Rules for sea freight, the Montreal Convention for air freight, and the CMR Convention for road transport) and the standard trading conditions of the relevant carrier. We are not liable for delays or losses caused by events beyond our reasonable control, including but not limited to acts of God, customs inspections, weather events, port or border closures, strikes, carrier disruptions, or changes in regulations.</p>'
                 . '<h2>6. Insurance</h2><p>Galilea does not automatically insure your cargo. We strongly recommend that you arrange adequate cargo insurance for every shipment. If you wish us to arrange insurance on your behalf, please instruct us in writing at the time of booking.</p>'
                 . '<h2>7. Payment</h2><p>Invoices are payable within the terms stated on the invoice. We reserve the right to charge interest on overdue amounts at the rate permitted by applicable law and to hold cargo, documentation or other property against unpaid charges where permitted by law.</p>'
                 . '<h2>8. Governing law &amp; disputes</h2><p>These Terms are governed by the laws of the Republic of Rwanda. Any dispute arising out of or relating to these Terms or our services that cannot be resolved amicably shall be subject to the exclusive jurisdiction of the competent courts of Rwanda, unless otherwise agreed in writing.</p>'
                 . '<h2>9. Changes</h2><p>We may update these Terms from time to time. The latest version is always available on our website. Continued use of our services after changes take effect constitutes acceptance of the updated Terms.</p>'
                 . '<h2>10. Contact</h2><p>For questions about these Terms, please contact <a href="mailto:info@galileagloballogistics.rw">info@galileagloballogistics.rw</a> or write to our head office in Nyarugenge, Kigali, Rwanda.</p>',
                 'The terms governing the use of Galilea Global Logistics services and website.'],

                ['Cookie Policy', 'cookies',
                 '<p>This Cookie Policy explains how Galilea Global Logistics uses cookies and similar technologies on our website. It explains what cookies are, which ones we use, and how you can manage them.</p>'
                 . '<h2>1. What cookies are</h2><p>Cookies are small text files stored on your device (computer, tablet or smartphone) when you visit a website. They are widely used to make websites work efficiently, remember your preferences, and provide information to the site owner about how the site is used.</p>'
                 . '<h2>2. Cookies we use</h2><ul>'
                 . '<li><strong>Strictly necessary cookies</strong> — a session cookie that keeps you logged in during your visit, and a security (CSRF) token that protects our forms from cross-site request forgery attacks. These are essential for the site to function and cannot be switched off.</li>'
                 . '<li><strong>Analytics cookies (optional)</strong> — if you accept via our cookie consent banner, we use privacy-respecting analytics to measure page views, traffic sources and user behaviour. This helps us understand what content is useful and improve the website. These cookies are only set after you give your explicit consent.</li>'
                 . '</ul>'
                 . '<h2>3. How to manage cookies</h2><p>When you first visit our website, a cookie banner gives you the choice to accept or decline optional analytics cookies. You can change your preference at any time by clearing your browser cookies and reloading the site. You can also block or delete cookies through your browser settings — but please note that blocking strictly necessary cookies may affect the functionality of the site.</p>'
                 . '<h2>4. Changes to this policy</h2><p>We may update this Cookie Policy from time to time. Any changes will be posted on this page. We encourage you to review it periodically.</p>'
                 . '<h2>5. Contact</h2><p>If you have questions about this Cookie Policy or how we use cookies, please contact us at <a href="mailto:info@galileagloballogistics.rw">info@galileagloballogistics.rw</a>.</p>',
                 'How Galilea Global Logistics uses cookies and how you can manage them.'],
            ];
            $stmt = $pdo->prepare('INSERT INTO pages (title, slug, body, meta_description) VALUES (?,?,?,?)');
            foreach ($pages as $pg) {
                $stmt->execute($pg);
            }
        }

        if ((int) self::value('SELECT COUNT(*) FROM menu_items') === 0) {
            // Top-level mega-menu parents.
            $parents = [
                ['Services', '/services', 1, 1],
                ['Company', '/about', 1, 2],
            ];
            $pstmt = $pdo->prepare('INSERT INTO menu_items (title, url, is_mega, sort_order) VALUES (?,?,?,?)');
            $ids = [];
            foreach ($parents as $pr) { $pstmt->execute($pr); $ids[$pr[0]] = (int) $pdo->lastInsertId(); }

            $ship = '<path d="M3 17l9 4 9-4V7L12 3 3 7v10z"/><path d="M12 3v18M3 7l9 4 9-4"/>';
            $plane = '<path d="M17.8 19.2L16 11l3.5-3.5C21 6 21 4 20 3c-1-1-3-1-4.5.5L12 7 3.8 5.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/>';
            $truck = '<rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>';
            $box = '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>';
            $globe = '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/>';
            $check = '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>';

            $children = [
                // Services
                [$ids['Services'], 'Sea Cargo & Ocean Freight', 'FCL & LCL worldwide', '/services/sea-cargo-ocean-freight', $ship, 1, 1],
                [$ids['Services'], 'Air Freight', 'Time-critical cargo', '/services/air-freight', $plane, 1, 2],
                [$ids['Services'], 'Road & Land Transport', 'Inland & last-mile', '/services/road-land-transport', $truck, 1, 3],
                [$ids['Services'], 'Warehousing & Distribution', 'Strategic hubs', '/services/warehousing-distribution', $box, 2, 4],
                [$ids['Services'], 'Customs Clearance', 'Smooth border crossings', '/services/customs-clearance', $check, 2, 5],
                [$ids['Services'], 'China Business Connection', 'Sourcing & transfers', '/services/china-business-connection', $globe, 2, 6],
                // Solutions (col 1 under Company)
                [$ids['Company'], 'E-commerce Fulfilment', 'Pick, pack & dispatch', '/services', $box, 1, 1],
                [$ids['Company'], 'Project Cargo', 'Oversized & specialist loads', '/services', $ship, 1, 2],
                [$ids['Company'], 'Supply Chain Management', 'End-to-end optimisation', '/services', $globe, 1, 3],
                [$ids['Company'], 'Track & Trace', 'Live shipment tracking', '/track', $check, 1, 4],
                // Industries (col 2 under Company)
                [$ids['Company'], 'Pharmaceuticals', 'Compliant cold-chain', '/services', $check, 2, 1],
                [$ids['Company'], 'Fresh Produce & Perishables', 'Time-sensitive air freight', '/services', $plane, 2, 2],
                [$ids['Company'], 'Machinery & Equipment', 'Heavy & oversized', '/services', $truck, 2, 3],
                [$ids['Company'], 'Retail & E-commerce', 'Scalable fulfilment', '/services', $box, 2, 4],
                // Company (col 3 under Company)
                [$ids['Company'], 'About Galilea', 'Who we are', '/about', $globe, 3, 1],
                [$ids['Company'], 'Our Team', 'Leadership', '/#team', '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/>', 3, 2],
                [$ids['Company'], 'Insights & News', 'Latest updates', '/insights', '<path d="M4 22h16a2 2 0 002-2V4a2 2 0 00-2-2H8a2 2 0 00-2 2v16a2 2 0 01-2 2z"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8z"/>', 3, 3],
                [$ids['Company'], 'Careers', 'Join the team', '/careers', '<path d="M20 7h-4V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2H4a2 2 0 00-2 2v11a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>', 3, 4],
                [$ids['Company'], 'Contact', 'Get in touch', '/contact', '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>', 3, 5],
            ];
            $cstmt = $pdo->prepare('INSERT INTO menu_items (parent_id, title, subtitle, url, icon, column_group, sort_order) VALUES (?,?,?,?,?,?,?)');
            foreach ($children as $c) { $cstmt->execute($c); }
        }
    }
}
