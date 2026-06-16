<?php
declare(strict_types=1);

/** Shared front-end data helpers (settings + navigation). */

function site_settings(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (Database::all('SELECT key, value FROM site_settings') as $r) {
            $cache[$r['key']] = $r['value'];
        }
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    return site_settings()[$key] ?? $default;
}

/** Build the navigation tree (top-level items with their mega children). */
function nav_menu(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $rows = Database::all('SELECT * FROM menu_items WHERE is_active = 1 ORDER BY sort_order, id');
    $tops = [];
    $children = [];
    foreach ($rows as $r) {
        if (empty($r['parent_id'])) {
            $r['children'] = [];
            $tops[(int) $r['id']] = $r;
        } else {
            $children[(int) $r['parent_id']][] = $r;
        }
    }
    foreach ($children as $pid => $kids) {
        if (isset($tops[$pid])) {
            $tops[$pid]['children'] = $kids;
        }
    }
    return $cache = array_values($tops);
}

/** Current request path, normalised (no trailing slash, no query). */
function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = '/' . trim($path, '/');
    return $path === '/' ? '/' : $path;
}

/** Render a tel: href from a display phone number. */
function tel_href(string $phone): string
{
    return 'tel:' . preg_replace('/[^0-9+]/', '', $phone);
}
