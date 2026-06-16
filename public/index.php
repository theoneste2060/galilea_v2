<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

send_security_headers();

$action = input('action');

/* ───────────────────────── JSON / form API actions ───────────────────── */

if ($action === 'track') {
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

/* ─────────────────────────────── Routing ─────────────────────────────── */

header('Cache-Control: private, max-age=60');
$path = current_path();
$segments = $path === '/' ? [] : explode('/', trim($path, '/'));
$views = dirname(__DIR__) . '/app/views/public';

// Home.
if ($path === '/') {
    $hero         = Database::all('SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY sort_order, id');
    $services     = Database::all('SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order, title LIMIT 6');
    $news         = Database::all('SELECT * FROM news_posts WHERE published = 1 ORDER BY published_at DESC LIMIT 3');
    $testimonials = Database::all('SELECT * FROM testimonials WHERE is_active = 1 ORDER BY id LIMIT 6');
    $team         = Database::all('SELECT * FROM team_members WHERE is_active = 1 ORDER BY sort_order, full_name LIMIT 8');
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
