<?php
declare(strict_types=1);

/**
 * Application bootstrap: load config, wire the database, start a hardened
 * session and run migrations/seed on first request.
 */

error_reporting(E_ALL);
// Never leak internals to visitors; log instead.
ini_set('display_errors', '0');
ini_set('log_errors', '1');

define('APP_ROOT', dirname(__DIR__));

$config = require __DIR__ . '/config.php';

require __DIR__ . '/lib/helpers.php';
require __DIR__ . '/lib/Database.php';
require __DIR__ . '/lib/site.php';
require __DIR__ . '/lib/seo.php';
require __DIR__ . '/lib/Totp.php';

Database::init($config);

// First-run schema + seed. Cheap to call (guarded by IF NOT EXISTS / COUNT).
try {
    Database::migrate();
    Database::seed($config);
} catch (Throwable $e) {
    error_log('[galilea] bootstrap failure: ' . $e->getMessage());
    http_response_code(500);
    exit('The application is temporarily unavailable. Please try again shortly.');
}

start_secure_session();

// Make config available to controllers.
$GLOBALS['galilea_config'] = $config;

function config(string $key = null)
{
    $c = $GLOBALS['galilea_config'] ?? [];
    return $key === null ? $c : ($c[$key] ?? null);
}
