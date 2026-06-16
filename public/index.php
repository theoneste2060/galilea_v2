<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

send_security_headers();

$action = input('action');

/* ───────────────────────── JSON / form API actions ───────────────────── */

if ($action === 'track') {
    // Public shipment tracking lookup.
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
    if (mb_strlen($name) < 2)        $errors[] = 'Please enter your name.';
    if (!valid_email($email))        $errors[] = 'Please enter a valid email address.';
    if (mb_strlen($message) < 10)    $errors[] = 'Please tell us a little more (min 10 characters).';
    // Honeypot anti-spam field.
    if (input('website') !== '')     $errors[] = 'Spam detected.';

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
        Database::run(
            'INSERT INTO newsletter_subscribers (email, source) VALUES (?, ?)',
            [$email, 'website']
        );
    } catch (PDOException $e) {
        // Unique constraint = already subscribed; treat as success.
    }
    json_out(['ok' => true, 'message' => 'You are subscribed. Welcome aboard!']);
}

/* ────────────────────────────── Home page ────────────────────────────── */

// Let the visitor's own browser cache briefly. "private" because the page
// embeds a per-session CSRF token that must never be shared via a proxy/CDN.
header('Cache-Control: private, max-age=60');

$settings = [];
foreach (Database::all('SELECT key, value FROM site_settings') as $r) {
    $settings[$r['key']] = $r['value'];
}
$hero         = Database::all('SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY sort_order, id');
$services     = Database::all('SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order, title LIMIT 6');
$news         = Database::all('SELECT * FROM news_posts WHERE published = 1 ORDER BY published_at DESC LIMIT 3');
$testimonials = Database::all('SELECT * FROM testimonials WHERE is_active = 1 ORDER BY id LIMIT 6');
$team         = Database::all('SELECT * FROM team_members WHERE is_active = 1 ORDER BY sort_order, full_name LIMIT 8');

require dirname(__DIR__) . '/app/views/public/home.php';
