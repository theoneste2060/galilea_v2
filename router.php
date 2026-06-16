<?php
// Router for PHP's built-in server: serve real files, route the rest to index.php.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Never expose application code or the database (they live inside the docroot
// under the flat layout). Mirrors the Apache rules in .htaccess.
if (preg_match('#^/(app|data)(/|$)#', $path)) {
    http_response_code(403);
    exit('Forbidden');
}

$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    return false; // let the built-in server serve the static asset
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';
