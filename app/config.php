<?php
/**
 * Galilea Global Logistics — application configuration.
 *
 * Values here can be overridden with environment variables so that secrets
 * never need to live in the repository in a real deployment.
 */

declare(strict_types=1);

return [
    // Absolute path to the SQLite database file (kept OUTSIDE the web root).
    'db_path'       => getenv('GALILEA_DB') ?: dirname(__DIR__) . '/data/galilea.sqlite',

    // Directory where uploaded images are stored (inside the web root so they
    // can be served, but writes are strictly validated).
    'upload_dir'    => dirname(__DIR__) . '/public/uploads',
    'upload_url'    => '/uploads',

    // Bootstrap admin account. Created on first run only; change the password
    // immediately after the first login (or set the env vars before first run).
    'seed_admin_user'     => getenv('GALILEA_ADMIN_USER') ?: 'admin',
    'seed_admin_password' => getenv('GALILEA_ADMIN_PASS') ?: 'Galilea@2025',
    'seed_admin_name'     => 'Site Administrator',

    // Upload constraints.
    'max_upload_bytes' => 4 * 1024 * 1024, // 4 MB
    'allowed_mimes'    => [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ],

    // Login throttling.
    'max_login_attempts' => 6,
    'login_lockout_secs' => 600, // 10 minutes

    'app_name' => 'Galilea Global Logistics',
];
