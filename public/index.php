<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

send_security_headers();

$action = input('action');

/* ───────────────────────── JSON / form API actions ───────────────────── */

if ($action === 'track') {
    if (!rate_limit('track', 40, 60)) {
        json_out(['ok' => false, 'error' => 'Too many requests. Please slow down.'], 429);
    }
    $ref = input('ref');
    if (mb_strlen($ref) < 3) {
        json_out(['ok' => false, 'error' => 'Please enter a valid reference number.'], 422);
    }
    $row = Database::one(
        'SELECT reference_number, customer_name, origin, destination, current_stage, status, stages, updated_at
         FROM shipments WHERE reference_number = ? COLLATE NOCASE',
        [$ref]
    );
    if (!$row) {
        json_out(['ok' => false, 'error' => 'No shipment found for that reference. Please check and try again.'], 404);
    }
    $row['stages'] = json_decode($row['stages'] ?: '[]', true) ?: [];
    json_out(['ok' => true, 'shipment' => $row]);
}

if ($action === 'inquiry' && is_post()) {
    csrf_check();
    if (!rate_limit('inquiry', 5, 600)) {
        json_out(['ok' => false, 'error' => 'Too many submissions. Please try again later.'], 429);
    }
    $name    = input('full_name');
    $email   = input('email');
    $phone   = input('phone');
    $company = input('company');
    $service = input('service_interest') ?: 'General Inquiry';
    $message = input('message');

    $errors = [];
    if (mb_strlen($name) < 2)     $errors[] = 'Please enter your name.';
    if (!valid_email($email))     $errors[] = 'Please enter a valid email address.';
    if (mb_strlen($message) < 10) $errors[] = 'Please tell us a little more (min 10 characters).';
    if (input('website') !== '')  $errors[] = 'Spam detected.';

    if ($errors) {
        json_out(['ok' => false, 'error' => implode(' ', $errors)], 422);
    }
    Database::run(
        'INSERT INTO inquiries (full_name, email, phone, company, service_interest, message) VALUES (?,?,?,?,?,?)',
        [$name, $email, $phone, $company, $service, $message]
    );
    json_out(['ok' => true, 'message' => 'Thank you — our team will be in touch shortly.']);
}

if ($action === 'newsletter' && is_post()) {
    csrf_check();
    if (!rate_limit('newsletter', 5, 600)) {
        json_out(['ok' => false, 'error' => 'Too many submissions. Please try again later.'], 429);
    }
    $email = input('email');
    if (!valid_email($email)) {
        json_out(['ok' => false, 'error' => 'Please enter a valid email address.'], 422);
    }
    if (input('website') !== '') {
        json_out(['ok' => false, 'error' => 'Spam detected.'], 422);
    }
    try {
        Database::run('INSERT INTO newsletter_subscribers (email, source) VALUES (?, ?)', [$email, 'website']);
    } catch (PDOException $e) {
        // Already subscribed — treat as success.
    }
    json_out(['ok' => true, 'message' => 'You are subscribed. Welcome aboard!']);
}

/* ───────────────────────── SEO / GEO machine files ───────────────────── */

$reqPath = current_path();

if ($reqPath === '/security.txt' || $reqPath === '/.well-known/security.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    $st = site_settings();
    $email = $st['site_email'] ?? 'info@galileagloballogistics.rw';
    echo "Contact: mailto:$email\n";
    echo "Preferred-Languages: en\n";
    echo "Canonical: " . base_url() . "/.well-known/security.txt\n";
    echo "Expires: " . gmdate('Y-m-d\TH:i:s\Z', strtotime('+1 year')) . "\n";
    exit;
}

if ($reqPath === '/robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    $base = base_url();
    echo "User-agent: *\n";
    echo "Allow: /\n";
    echo "Disallow: /admin.php\n";
    echo "Disallow: /uploads/\n\n";
    echo "Sitemap: $base/sitemap.xml\n";
    exit;
}

if ($reqPath === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    $base = base_url();
    $urls = [
        ['/', '1.0', 'daily'],
        ['/services', '0.9', 'weekly'],
        ['/insights', '0.8', 'daily'],
        ['/track', '0.7', 'monthly'],
        ['/contact', '0.7', 'monthly'],
    ];
    foreach (Database::all('SELECT slug FROM services WHERE is_active = 1') as $r) {
        $urls[] = ['/services/' . $r['slug'], '0.8', 'monthly'];
    }
    foreach (Database::all('SELECT slug FROM news_posts WHERE published = 1') as $r) {
        $urls[] = ['/insights/' . $r['slug'], '0.6', 'monthly'];
    }
    foreach (Database::all('SELECT slug FROM pages WHERE is_active = 1') as $r) {
        $urls[] = ['/' . $r['slug'], '0.4', 'yearly'];
    }
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as [$loc, $pri, $freq]) {
        echo "  <url><loc>" . esc($base . $loc) . "</loc><changefreq>$freq</changefreq><priority>$pri</priority></url>\n";
    }
    echo '</urlset>';
    exit;
}

if ($reqPath === '/llms.txt') {
    // Generative Engine Optimization: a concise, machine-readable company brief
    // that AI answer engines can ingest and cite.
    header('Content-Type: text/plain; charset=utf-8');
    $st = site_settings();
    $base = base_url();
    echo "# Galilea Global Logistics Ltd.\n\n";
    echo "> Trusted Trade. Global Reach. A Kigali-headquartered freight forwarder connecting East Africa and China through sea, air and land freight, customs clearance, warehousing and supplier sourcing.\n\n";
    echo "## Contact\n";
    echo "- Email: " . ($st['site_email'] ?? '') . "\n";
    echo "- Rwanda phone: " . ($st['phone_rw'] ?? '') . "\n";
    echo "- China phone: " . ($st['phone_cn'] ?? '') . "\n";
    echo "- Head office: " . ($st['address_kigali'] ?? '') . ", Nyarugenge, Kigali, Rwanda\n";
    echo "- Other offices: Guangzhou and Yiwu, China\n\n";
    echo "## Services\n";
    foreach (Database::all('SELECT title, short_description, slug FROM services WHERE is_active = 1 ORDER BY sort_order') as $s) {
        echo "- [" . $s['title'] . "](" . $base . "/services/" . $s['slug'] . "): " . $s['short_description'] . "\n";
    }
    echo "\n## Key pages\n";
    echo "- [Services](" . $base . "/services)\n- [Track & Trace](" . $base . "/track)\n- [Insights](" . $base . "/insights)\n- [Contact / Quote](" . $base . "/contact)\n- [About](" . $base . "/about)\n";
    exit;
}

/* ─────────────────────────────── Routing ─────────────────────────────── */

header('Cache-Control: private, max-age=60');
$path = $reqPath;
$segments = $path === '/' ? [] : explode('/', trim($path, '/'));
$views = dirname(__DIR__) . '/app/views/public';

// Home.
if ($path === '/') {
    $hero         = Database::all('SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY sort_order, id');
    $services     = Database::all('SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order, title LIMIT 6');
    $news         = Database::all('SELECT * FROM news_posts WHERE published = 1 ORDER BY published_at DESC LIMIT 3');
    $testimonials = Database::all('SELECT * FROM testimonials WHERE is_active = 1 ORDER BY id LIMIT 6');
    $team         = Database::all('SELECT * FROM team_members WHERE is_active = 1 ORDER BY sort_order, full_name LIMIT 8');
    $faqs         = Database::all('SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order, id');
    require "$views/home.php";
    exit;
}

// Services.
if ($segments[0] === 'services') {
    if (isset($segments[1])) {
        $service = Database::one('SELECT * FROM services WHERE slug = ? AND is_active = 1', [$segments[1]]);
        if (!$service) { render_404(); }
        $more = Database::all('SELECT title, slug, short_description, image_path FROM services WHERE is_active = 1 AND id != ? ORDER BY sort_order LIMIT 3', [$service['id']]);
        require "$views/service_detail.php";
        exit;
    }
    $services = Database::all('SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order, title');
    require "$views/services_list.php";
    exit;
}

// Insights / news.
if ($segments[0] === 'insights') {
    if (isset($segments[1])) {
        $post = Database::one('SELECT * FROM news_posts WHERE slug = ? AND published = 1', [$segments[1]]);
        if (!$post) { render_404(); }
        $more = Database::all('SELECT title, slug, excerpt, image_path, published_at, category FROM news_posts WHERE published = 1 AND id != ? ORDER BY published_at DESC LIMIT 3', [$post['id']]);
        require "$views/insight_detail.php";
        exit;
    }
    $page = max(1, (int) input('page', '1'));
    $per = 9;
    $total = (int) Database::value('SELECT COUNT(*) FROM news_posts WHERE published = 1');
    $posts = Database::all('SELECT * FROM news_posts WHERE published = 1 ORDER BY published_at DESC LIMIT ? OFFSET ?', [$per, ($page - 1) * $per]);
    $pages = (int) ceil($total / $per);
    require "$views/insights_list.php";
    exit;
}

// Track & Trace.
if ($path === '/track') {
    require "$views/track.php";
    exit;
}

// Contact.
if ($path === '/contact') {
    $services = Database::all('SELECT title FROM services WHERE is_active = 1 ORDER BY sort_order, title');
    require "$views/contact.php";
    exit;
}

// Site search.
if ($path === '/search') {
    $q = trim(input('q'));
    $results = [];
    if (mb_strlen($q) >= 2) {
        $like = '%' . $q . '%';
        foreach (Database::all('SELECT title, slug, short_description AS excerpt FROM services WHERE is_active=1 AND (title LIKE ? OR short_description LIKE ? OR description LIKE ?) LIMIT 12', [$like, $like, $like]) as $r) {
            $results[] = ['kind' => 'Service', 'title' => $r['title'], 'url' => '/services/' . $r['slug'], 'excerpt' => $r['excerpt']];
        }
        foreach (Database::all('SELECT title, slug, excerpt FROM news_posts WHERE published=1 AND (title LIKE ? OR excerpt LIKE ? OR body LIKE ?) LIMIT 12', [$like, $like, $like]) as $r) {
            $results[] = ['kind' => 'Insight', 'title' => $r['title'], 'url' => '/insights/' . $r['slug'], 'excerpt' => $r['excerpt']];
        }
        foreach (Database::all('SELECT title, slug, meta_description AS excerpt FROM pages WHERE is_active=1 AND (title LIKE ? OR body LIKE ?) LIMIT 8', [$like, $like]) as $r) {
            $results[] = ['kind' => 'Page', 'title' => $r['title'], 'url' => '/' . $r['slug'], 'excerpt' => $r['excerpt']];
        }
        foreach (Database::all('SELECT question, answer FROM faqs WHERE is_active=1 AND (question LIKE ? OR answer LIKE ?) LIMIT 8', [$like, $like]) as $r) {
            $results[] = ['kind' => 'FAQ', 'title' => $r['question'], 'url' => '/#faq', 'excerpt' => mb_strimwidth($r['answer'], 0, 140, '…')];
        }
    }
    require "$views/search_results.php";
    exit;
}

// Static CMS pages (about, careers, privacy, terms, cookies, …).
if (count($segments) === 1) {
    $cms = Database::one('SELECT * FROM pages WHERE slug = ? AND is_active = 1', [$segments[0]]);
    if ($cms) {
        require "$views/page.php";
        exit;
    }
}

render_404();

function render_404(): never
{
    http_response_code(404);
    require dirname(__DIR__) . '/app/views/public/404.php';
    exit;
}
