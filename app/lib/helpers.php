<?php
declare(strict_types=1);

/* ─────────────────────────  Output / escaping  ───────────────────────── */

/** HTML-escape a value for safe output. */
function esc($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Escape for use inside a URL/attribute path. */
function esc_attr($value): string
{
    return esc($value);
}

/* ─────────────────────────────  Sessions  ────────────────────────────── */

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['SERVER_PORT'] ?? null) == 443;

    session_name('GALILEA_SID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    // Bind the session to a hardened fingerprint to limit fixation/hijacking.
    $fp = hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . '|galilea');
    if (!isset($_SESSION['_fp'])) {
        $_SESSION['_fp'] = $fp;
    } elseif (!hash_equals($_SESSION['_fp'], $fp)) {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['_fp'] = $fp;
    }
}

/* ─────────────────────────────  Security  ────────────────────────────── */

function send_security_headers(bool $allowSummernoteCdn = false): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header_remove('X-Powered-By');

    // Enforce HTTPS for a year (incl. subdomains) once served over TLS.
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['SERVER_PORT'] ?? null) == 443;
    if ($https) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }

    // Content Security Policy. Fonts come from Google; the admin rich-text
    // editor (Summernote) and jQuery load from a pinned CDN.
    $script = "'self'";
    $style  = "'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com";
    $font   = "'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com";
    $img    = "'self' data: blob:";
    $connect = "'self'";
    if ($allowSummernoteCdn) {
        $script .= ' https://cdnjs.cloudflare.com';
    }
    // Allow Google Analytics (GA4) only when an analytics ID is configured.
    if (function_exists('analytics_enabled') && analytics_enabled()) {
        $script .= " 'unsafe-inline' https://www.googletagmanager.com https://www.google-analytics.com";
        $img     .= ' https://www.google-analytics.com https://www.googletagmanager.com';
        $connect .= ' https://www.google-analytics.com https://www.googletagmanager.com';
    }
    $csp = "default-src 'self'; "
        . "img-src $img; "
        . "script-src $script; "
        . "style-src $style; "
        . "font-src $font; "
        . "connect-src $connect; "
        . "object-src 'none'; "
        . "base-uri 'self'; "
        . "form-action 'self'; "
        . "frame-ancestors 'none'";
    header("Content-Security-Policy: $csp");
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . esc(csrf_token()) . '">';
}

function csrf_check(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!is_string($token) || empty($_SESSION['_csrf']) || !hash_equals($_SESSION['_csrf'], $token)) {
        http_response_code(419);
        exit('Security token mismatch. Please reload the page and try again.');
    }
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/* ───────────────────────────  HTTP helpers  ──────────────────────────── */

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function flash(string $msg, string $type = 'success'): void
{
    $_SESSION['_flash'][] = ['msg' => $msg, 'type' => $type];
}

function take_flashes(): array
{
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

function json_out($data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/* ─────────────────────────  Input utilities  ─────────────────────────── */

function input(string $key, $default = ''): string
{
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    $text = trim($text, '-');
    return $text !== '' ? $text : 'item-' . substr((string) time(), -5);
}

function valid_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

/* ─────────────────────  HTML sanitisation (XSS-safe)  ─────────────────── */

/**
 * Sanitise rich-text HTML coming from the Summernote editor using a strict
 * tag/attribute allowlist. Prevents stored XSS while keeping basic formatting.
 */
function sanitize_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $allowedTags = [
        'p','br','b','strong','i','em','u','s','blockquote','span',
        'ul','ol','li','h1','h2','h3','h4','h5','h6','a','img',
        'hr','pre','code','table','thead','tbody','tr','td','th',
    ];
    $allowedAttrs = [
        'a'   => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title'],
    ];

    if (!class_exists('DOMDocument')) {
        // Fallback: strip everything but a safe tag subset, then drop attrs.
        $stripped = strip_tags($html, '<' . implode('><', $allowedTags) . '>');
        return preg_replace('/<([a-z0-9]+)[^>]*>/i', '<$1>', $stripped) ?? '';
    }

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML(
        '<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>',
        LIBXML_NOWARNING | LIBXML_NOERROR
    );
    libxml_clear_errors();

    $xpath = new DOMXPath($doc);
    // Only the wrapper's descendants — never the synthetic html/body/__root that
    // loadHTML adds (unwrapping <html> would destroy the whole tree).
    $nodes = iterator_to_array($xpath->query('//*[@id="__root"]//*') ?: []);
    foreach ($nodes as $node) {
        if (!$node instanceof DOMElement) {
            continue;
        }
        $tag = strtolower($node->nodeName);
        if (!in_array($tag, $allowedTags, true)) {
            // Unwrap disallowed tags: replace with their text content.
            $node->parentNode?->replaceChild(
                $doc->createTextNode($node->textContent),
                $node
            );
            continue;
        }
        $permitted = $allowedAttrs[$tag] ?? [];
        foreach (iterator_to_array($node->attributes ?? []) as $attr) {
            $name = strtolower($attr->nodeName);
            $val  = $attr->nodeValue ?? '';
            if (!in_array($name, $permitted, true)) {
                $node->removeAttribute($attr->nodeName);
                continue;
            }
            if (($name === 'href' || $name === 'src') && !safe_url($val)) {
                $node->removeAttribute($attr->nodeName);
            }
        }
        if ($tag === 'a' && $node->getAttribute('target') === '_blank') {
            $node->setAttribute('rel', 'noopener noreferrer');
        }
    }

    // NB: use XPath, not getElementById() — DOMDocument does not register `id`
    // attributes without a DTD, so getElementById('__root') returns null and
    // would silently drop all content.
    $rootNodes = $xpath->query('//*[@id="__root"]');
    $root = $rootNodes && $rootNodes->length ? $rootNodes->item(0) : null;
    $out  = '';
    if ($root) {
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $doc->saveHTML($child);
        }
    }
    return trim($out);
}

function safe_url(string $url): bool
{
    $url = trim($url);
    if ($url === '') {
        return false;
    }
    if (preg_match('/^\s*(javascript|data|vbscript):/i', $url)) {
        return false;
    }
    // Allow relative, root-relative, anchors, mailto/tel and http(s).
    return (bool) preg_match('#^(https?://|/|\#|mailto:|tel:|\.\.?/)#i', $url)
        || !str_contains($url, ':');
}

/* ───────────────────────────  File uploads  ──────────────────────────── */

/**
 * Validate and store an uploaded image. Returns the public URL path on
 * success, or null when no file was provided. Throws on invalid input.
 */
function handle_image_upload(string $field, array $config): ?string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed (code ' . $file['error'] . ').');
    }
    if ($file['size'] > $config['max_upload_bytes']) {
        throw new RuntimeException('Image is too large (max 4 MB).');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Invalid upload.');
    }

    // Detect the real MIME type from file contents, never trust the client.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']) ?: '';
    $allowed = $config['allowed_mimes'];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Unsupported image type. Use JPG, PNG, WEBP or GIF.');
    }

    // Re-verify with getimagesize so non-images masquerading as images fail.
    if (getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('File is not a valid image.');
    }

    $dir  = $config['upload_dir'];
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $base = bin2hex(random_bytes(16));
    $urlBase = rtrim($config['upload_url'], '/');

    // Optimise raster images to WebP (smaller, faster) when GD supports it.
    // Animated GIFs are kept as-is to preserve animation.
    if ($mime !== 'image/gif' && function_exists('imagewebp')) {
        $optimised = optimise_to_webp($file['tmp_name'], $mime, $dir . '/' . $base . '.webp');
        if ($optimised) {
            @chmod($dir . '/' . $base . '.webp', 0644);
            return $urlBase . '/' . $base . '.webp';
        }
    }

    // Fallback: store the original (validated) file unchanged.
    $name = $base . '.' . $allowed[$mime];
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Could not save the uploaded image.');
    }
    @chmod($dest, 0644);
    return $urlBase . '/' . $name;
}

/**
 * Downscale (max 1600px wide) and re-encode an image to WebP via GD.
 * Returns true on success. Never throws — callers fall back to the original.
 */
function optimise_to_webp(string $srcPath, string $mime, string $destPath): bool
{
    try {
        $img = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($srcPath),
            'image/png'  => @imagecreatefrompng($srcPath),
            'image/webp' => @imagecreatefromwebp($srcPath),
            default      => false,
        };
        if (!$img) {
            return false;
        }
        $w = imagesx($img);
        $h = imagesy($img);
        $max = 1600;
        if ($w > $max) {
            $nh = (int) round($h * $max / $w);
            $resized = imagecreatetruecolor($max, $nh);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $max, $nh, $w, $h);
            imagedestroy($img);
            $img = $resized;
        } else {
            imagesavealpha($img, true);
        }
        $ok = imagewebp($img, $destPath, 82);
        imagedestroy($img);
        return $ok && is_file($destPath);
    } catch (Throwable $e) {
        error_log('[galilea] webp optimise failed: ' . $e->getMessage());
        return false;
    }
}

/** Cache-busting URL for a local public asset (appends ?v=mtime). */
function asset_url(string $path): string
{
    $full = APP_ROOT . $path;
    $v = is_file($full) ? substr((string) filemtime($full), -6) : '1';
    return $path . '?v=' . $v;
}

/* ───────────────────────────  Activity log  ──────────────────────────── */

function log_activity(string $action, string $detail = ''): void
{
    $user = current_admin();
    Database::run(
        'INSERT INTO activity_log (user_id, username, action, detail, ip) VALUES (?,?,?,?,?)',
        [$user['id'] ?? null, $user['username'] ?? 'system', $action, $detail, client_ip()]
    );
}

/* ──────────────────────────────  Auth  ───────────────────────────────── */

function current_admin(): array
{
    return $_SESSION['admin'] ?? [];
}

function is_authenticated(): bool
{
    return !empty($_SESSION['admin']['id']);
}

const ADMIN_IDLE_TIMEOUT = 1800;    // 30 minutes of inactivity
const ADMIN_ABSOLUTE_TIMEOUT = 28800; // 8 hours max session age

function require_admin(): void
{
    if (!is_authenticated()) {
        redirect('/admin.php?p=login');
    }
    // Enforce idle + absolute session timeouts.
    $now = time();
    $last = $_SESSION['admin']['last_activity'] ?? $now;
    $start = $_SESSION['admin']['login_time'] ?? $now;
    if (($now - $last) > ADMIN_IDLE_TIMEOUT || ($now - $start) > ADMIN_ABSOLUTE_TIMEOUT) {
        $_SESSION = [];
        session_regenerate_id(true);
        flash('Your session expired. Please sign in again.', 'error');
        redirect('/admin.php?p=login');
    }
    $_SESSION['admin']['last_activity'] = $now;
}

function require_role(string ...$roles): void
{
    $role = current_admin()['role'] ?? '';
    if (!in_array($role, $roles, true)) {
        http_response_code(403);
        exit('Forbidden — insufficient privileges.');
    }
}

/** Whether the current admin may access a given section/page key. */
function can_access(string $section): bool
{
    $admin = current_admin();
    if (($admin['role'] ?? '') === 'superadmin') {
        return true;
    }
    // Always-available sections for any authenticated admin.
    if (in_array($section, ['dashboard', 'account', 'logout'], true)) {
        return true;
    }
    $allowed = $admin['allowed_sections'] ?? [];
    return in_array($section, $allowed, true);
}

function require_access(string $section): void
{
    if (!can_access($section)) {
        http_response_code(403);
        exit('Forbidden — you do not have access to this section.');
    }
}

/**
 * Fixed-window rate limiter backed by the api_hits table.
 * Returns true if the request is allowed, false if the limit is exceeded.
 */
function rate_limit(string $action, int $max, int $windowSecs): bool
{
    $ip = client_ip();
    Database::run("DELETE FROM api_hits WHERE created_at < datetime('now', ?)",
        ['-' . $windowSecs . ' seconds']);
    $count = (int) Database::value(
        'SELECT COUNT(*) FROM api_hits WHERE action = ? AND ip = ?',
        [$action, $ip]
    );
    Database::run('INSERT INTO api_hits (ip, action) VALUES (?, ?)', [$ip, $action]);
    return $count < $max;
}
