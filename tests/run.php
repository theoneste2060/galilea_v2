<?php
/**
 * Galilea smoke-test suite — dependency-free (no PHPUnit/Composer).
 *
 * Boots the app on a throwaway port + database, exercises the public site,
 * admin portal and security rules over HTTP, and asserts the results.
 *
 *   php tests/run.php
 *
 * Exits 0 when everything passes, 1 otherwise (CI-friendly).
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$port = (int) (getenv('TEST_PORT') ?: 8971);
$base = "http://127.0.0.1:$port";
$dbPath = "$root/data/test_galilea_" . getmypid() . '.sqlite';
$cookieJar = sys_get_temp_dir() . '/galilea_test_' . getmypid() . '.cookie';
@unlink($dbPath);
@unlink($cookieJar);

/* ── Boot a throwaway server ────────────────────────────────────────────── */
$env = ['GALILEA_DB' => $dbPath, 'PATH' => getenv('PATH')];
$descriptor = [0 => ['pipe', 'r'], 1 => ['file', '/dev/null', 'a'], 2 => ['file', '/dev/null', 'a']];
$proc = proc_open(
    ['php', '-S', "127.0.0.1:$port", 'router.php'],
    $descriptor, $pipes, $root, $env
);
if (!is_resource($proc)) {
    fwrite(STDERR, "Could not start the test server.\n");
    exit(1);
}

register_shutdown_function(function () use ($proc, $dbPath, $cookieJar) {
    if (is_resource($proc)) { proc_terminate($proc); }
    @unlink($dbPath);
    @unlink($cookieJar);
});

/* ── Tiny HTTP client (keeps a cookie jar) ──────────────────────────────── */
function req(string $method, string $url, array $opts = []): array
{
    global $cookieJar;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => $opts['follow'] ?? false,
        CURLOPT_COOKIEJAR => $cookieJar,
        CURLOPT_COOKIEFILE => $cookieJar,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CUSTOMREQUEST => $method,
    ]);
    if (isset($opts['post'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($opts['post']));
    }
    $body = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $body];
}

/** Pull the CSRF token out of a rendered page. */
function csrf(string $html): string
{
    return preg_match('/name="_csrf" value="([a-f0-9]{64})"/', $html, $m) ? $m[1] : '';
}

/* ── Wait for the server to accept connections ──────────────────────────── */
$ready = false;
for ($i = 0; $i < 50; $i++) {
    $r = @req('GET', "$base/");
    if ($r['code'] === 200) { $ready = true; break; }
    usleep(120000);
}
if (!$ready) {
    fwrite(STDERR, "Test server never became ready.\n");
    exit(1);
}

/* ── Assertion harness ──────────────────────────────────────────────────── */
$pass = 0; $fail = 0; $failed = [];
function check(string $name, bool $cond): void
{
    global $pass, $fail, $failed;
    if ($cond) { $pass++; echo "  \033[32m✓\033[0m $name\n"; }
    else { $fail++; $failed[] = $name; echo "  \033[31m✗ $name\033[0m\n"; }
}
function group(string $t): void { echo "\n\033[1m$t\033[0m\n"; }

/* ── Public site ────────────────────────────────────────────────────────── */
group('Public pages render');
foreach (['/' => 'mainNav', '/services' => 'Services', '/insights' => 'Insights',
          '/contact' => 'inquiryForm', '/track' => 'trackForm', '/about' => 'About'] as $path => $needle) {
    $r = req('GET', "$base$path");
    check("GET $path → 200 + content", $r['code'] === 200 && stripos($r['body'], $needle) !== false);
}
check('404 for unknown URL', req('GET', "$base/no-such-page-xyz")['code'] === 404);

group('Site search');
$r = req('GET', "$base/search?ajax=1&q=air");
$json = json_decode($r['body'], true);
check('typeahead JSON returns results', $r['code'] === 200 && !empty($json['results']));
check('HTML search results render', stripos(req('GET', "$base/search?q=freight")['body'], 'search-result') !== false || stripos(req('GET', "$base/search?q=freight")['body'], 'No results') !== false);

group('Insights category filter');
$r = req('GET', "$base/insights");
check('filter chips present', stripos($r['body'], 'filter-chip') !== false);
check('invalid category falls back (still 200)', req('GET', "$base/insights?cat=__nope__")['code'] === 200);

group('Public forms (validation + happy path)');
$home = req('GET', "$base/")['body'];
$tok = csrf($home);
check('CSRF token present on page', $tok !== '');
$bad = req('POST', "$base/?action=inquiry", ['post' => ['_csrf' => $tok, 'full_name' => 'X', 'email' => 'bad', 'message' => 'short']]);
check('inquiry rejects invalid input (422)', $bad['code'] === 422);
$good = req('POST', "$base/?action=inquiry", ['post' => ['_csrf' => $tok, 'full_name' => 'Test User', 'email' => 'test@example.com', 'message' => 'This is a valid inquiry over ten chars.']]);
$gj = json_decode($good['body'], true);
check('inquiry accepts valid input', $good['code'] === 200 && !empty($gj['ok']));
check('CSRF mismatch is rejected (419)', req('POST', "$base/?action=inquiry", ['post' => ['_csrf' => 'nope', 'full_name' => 'A', 'email' => 'a@b.com', 'message' => 'ten chars here']])['code'] === 419);

group('Shipment tracking');
$tok = csrf(req('GET', "$base/track")['body']);
$miss = req('POST', "$base/?action=track", ['post' => ['_csrf' => $tok, 'ref' => 'ZZZ-NOPE-000']]);
check('unknown reference → 404 JSON', $miss['code'] === 404);

/* ── Security ───────────────────────────────────────────────────────────── */
group('Security: protected paths denied');
foreach (['/app/config.php', '/data/test_galilea_' . getmypid() . '.sqlite', '/app/lib/Mailer.php'] as $p) {
    check("$p is forbidden", req('GET', "$base$p")['code'] === 403);
}

/* ── Admin portal ───────────────────────────────────────────────────────── */
group('Admin authentication');
check('admin requires login (redirect/login)', stripos(req('GET', "$base/admin.php?p=dashboard", ['follow' => true])['body'], 'password') !== false);
$login = req('GET', "$base/admin.php?p=login")['body'];
$tok = csrf($login);
req('POST', "$base/admin.php?p=login", ['post' => ['_csrf' => $tok, 'username' => 'admin', 'password' => 'Galilea@2025'], 'follow' => true]);
$dash = req('GET', "$base/admin.php?p=dashboard");
check('valid credentials reach the dashboard', stripos($dash['body'], 'Welcome back') !== false);
check('dashboard charts render', stripos($dash['body'], 'chart-bars') !== false);

group('Admin CRUD + features');
foreach (['services', 'news', 'shipments', 'media', 'settings'] as $p) {
    check("admin/$p loads", req('GET', "$base/admin.php?p=$p")['code'] === 200);
}
$svc = req('GET', "$base/admin.php?p=services")['body'];
check('list search box present', stripos($svc, 'list-search') !== false);
check('email settings group present', stripos(req('GET', "$base/admin.php?p=settings")['body'], 'Email / SMTP') !== false);
check('shipment stage builder present', stripos(req('GET', "$base/admin.php?p=shipments")['body'] . req('GET', "$base/admin.php?p=shipments&new=1")['body'], 'data-stage-editor') !== false);

/* ── Summary ────────────────────────────────────────────────────────────── */
echo "\n" . str_repeat('─', 48) . "\n";
$total = $pass + $fail;
if ($fail === 0) {
    echo "\033[32m\033[1mAll $total checks passed.\033[0m\n";
} else {
    echo "\033[31m\033[1m$fail of $total checks FAILED:\033[0m\n";
    foreach ($failed as $f) { echo "  • $f\n"; }
}
exit($fail === 0 ? 0 : 1);
