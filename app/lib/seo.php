<?php
declare(strict_types=1);

/**
 * SEO + GEO helpers: meta tags, Open Graph/Twitter, geo tags, JSON-LD
 * structured data (Organization, LocalBusiness for each office, WebSite,
 * BreadcrumbList, Service, Article, FAQPage) and analytics injection.
 *
 * "GEO" here means both geographic/local SEO and Generative Engine
 * Optimization — emitting clean, machine-readable facts that AI answer
 * engines (and classic crawlers) can cite.
 */

/** Absolute site base URL (configured, else derived from the request). */
function base_url(): string
{
    $configured = setting('site_url');
    if ($configured !== '') {
        return rtrim($configured, '/');
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

function abs_url(string $path): string
{
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    return base_url() . '/' . ltrim($path, '/');
}

/** The current request's absolute, canonical URL (path only, no query). */
function canonical_url(): string
{
    return base_url() . current_path();
}

/** Each company office, used for LocalBusiness schema + geo meta. */
function offices(): array
{
    $st = site_settings();
    return [
        [
            'name' => 'Galilea Global Logistics — Kigali (HQ)',
            'street' => $st['address_kigali'] ?? 'F1-8B Unify Building, Nyarugenge',
            'city' => 'Kigali', 'region' => 'Kigali', 'country' => 'RW',
            'phone' => $st['phone_rw'] ?? '', 'email' => $st['site_email'] ?? '',
            'lat' => $st['geo_lat'] ?? '-1.9441', 'lng' => $st['geo_lng'] ?? '30.0619', 'primary' => true,
        ],
        [
            'name' => 'Galilea Global Logistics — Guangzhou',
            'street' => '广园西路83号 204室', 'city' => 'Guangzhou', 'region' => 'Guangdong', 'country' => 'CN',
            'phone' => $st['phone_cn'] ?? '', 'email' => $st['site_email'] ?? '',
            'lat' => '23.1291', 'lng' => '113.2644', 'primary' => false,
        ],
        [
            'name' => 'Galilea Global Logistics — Yiwu',
            'street' => '龙海路825号普洛斯B2', 'city' => 'Yiwu', 'region' => 'Zhejiang', 'country' => 'CN',
            'phone' => $st['phone_cn'] ?? '', 'email' => $st['site_email'] ?? '',
            'lat' => '29.3068', 'lng' => '120.0760', 'primary' => false,
        ],
    ];
}

/**
 * Render all <head> SEO/GEO meta. $meta keys:
 *   title, description, type (website|article), image, robots,
 *   published, modified, breadcrumbs[], schema[] (extra JSON-LD nodes).
 */
function render_seo_head(array $meta): string
{
    $st     = site_settings();
    $title  = $meta['title'] ?? ($st['seo_title'] ?? 'Galilea Global Logistics');
    $desc   = $meta['description'] ?? ($st['seo_description'] ?? '');
    $canon  = canonical_url();
    $type   = $meta['type'] ?? 'website';
    $image  = abs_url($meta['image'] ?? ($st['og_image'] ?? '/assets/img/logo.jpeg'));
    $robots = $meta['robots'] ?? 'index, follow, max-image-preview:large';
    $name   = 'Galilea Global Logistics';
    $primary = offices()[0];
    $e = fn($v) => esc($v);

    $h  = "<meta name=\"description\" content=\"{$e($desc)}\">\n";
    $h .= "<meta name=\"robots\" content=\"{$e($robots)}\">\n";
    $h .= "<link rel=\"canonical\" href=\"{$e($canon)}\">\n";
    $h .= "<meta name=\"theme-color\" content=\"#0D2645\">\n";

    // Geographic targeting (local SEO / GEO).
    $h .= "<meta name=\"geo.region\" content=\"RW-01\">\n";
    $h .= "<meta name=\"geo.placename\" content=\"" . $e($st['geo_placename'] ?? 'Kigali, Rwanda') . "\">\n";
    $h .= "<meta name=\"geo.position\" content=\"{$e($primary['lat'])};{$e($primary['lng'])}\">\n";
    $h .= "<meta name=\"ICBM\" content=\"{$e($primary['lat'])}, {$e($primary['lng'])}\">\n";

    // Open Graph.
    $h .= "<meta property=\"og:type\" content=\"{$e($type)}\">\n";
    $h .= "<meta property=\"og:site_name\" content=\"{$e($name)}\">\n";
    $h .= "<meta property=\"og:title\" content=\"{$e($title)}\">\n";
    $h .= "<meta property=\"og:description\" content=\"{$e($desc)}\">\n";
    $h .= "<meta property=\"og:url\" content=\"{$e($canon)}\">\n";
    $h .= "<meta property=\"og:image\" content=\"{$e($image)}\">\n";
    $h .= "<meta property=\"og:locale\" content=\"en_US\">\n";
    if ($type === 'article') {
        if (!empty($meta['published'])) $h .= "<meta property=\"article:published_time\" content=\"" . $e(date('c', strtotime($meta['published']))) . "\">\n";
        if (!empty($meta['modified']))  $h .= "<meta property=\"article:modified_time\" content=\"" . $e(date('c', strtotime($meta['modified']))) . "\">\n";
    }

    // Twitter.
    $h .= "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";
    $h .= "<meta name=\"twitter:title\" content=\"{$e($title)}\">\n";
    $h .= "<meta name=\"twitter:description\" content=\"{$e($desc)}\">\n";
    $h .= "<meta name=\"twitter:image\" content=\"{$e($image)}\">\n";
    if (!empty($st['twitter_handle'])) {
        $h .= "<meta name=\"twitter:site\" content=\"{$e($st['twitter_handle'])}\">\n";
    }

    $h .= render_json_ld($meta);
    return $h;
}

/** Build the JSON-LD @graph for this page. */
function render_json_ld(array $meta): string
{
    $st   = site_settings();
    $name = $st['org_legal_name'] ?? 'Galilea Global Logistics Ltd.';
    $url  = base_url();
    $logo = abs_url('/assets/img/logo.jpeg');

    $sameAs = array_values(array_filter([
        $st['social_linkedin'] ?? '', $st['social_facebook'] ?? '', $st['social_youtube'] ?? '',
    ]));

    // Organization.
    $org = [
        '@type' => 'Organization',
        '@id'   => $url . '/#organization',
        'name'  => $name,
        'url'   => $url,
        'logo'  => $logo,
        'description' => $st['seo_description'] ?? '',
        'email' => $st['site_email'] ?? '',
        'areaServed' => ['Rwanda', 'East Africa', 'China', 'Worldwide'],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => $st['phone_rw'] ?? '',
            'contactType' => 'customer service',
            'areaServed' => ['RW', 'CN'],
            'availableLanguage' => ['English', 'French', 'Chinese'],
        ],
    ];
    if ($sameAs) $org['sameAs'] = $sameAs;

    // WebSite (with sitelinks search box).
    $website = [
        '@type' => 'WebSite',
        '@id'   => $url . '/#website',
        'url'   => $url,
        'name'  => 'Galilea Global Logistics',
        'publisher' => ['@id' => $url . '/#organization'],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => ['@type' => 'EntryPoint', 'urlTemplate' => $url . '/track?ref={ref}'],
            'query-input' => 'required name=ref',
        ],
    ];

    // LocalBusiness for each office (local SEO / GEO).
    $businesses = [];
    foreach (offices() as $i => $o) {
        $businesses[] = [
            '@type' => 'LocalBusiness',
            '@id'   => $url . '/#office-' . $i,
            'name'  => $o['name'],
            'parentOrganization' => ['@id' => $url . '/#organization'],
            'image' => $logo,
            'url'   => $url,
            'telephone' => $o['phone'],
            'email' => $o['email'],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $o['street'],
                'addressLocality' => $o['city'],
                'addressRegion' => $o['region'],
                'addressCountry' => $o['country'],
            ],
            'geo' => ['@type' => 'GeoCoordinates', 'latitude' => $o['lat'], 'longitude' => $o['lng']],
        ];
    }

    $graph = array_merge([$org, $website], $businesses);

    // Breadcrumbs.
    if (!empty($meta['breadcrumbs'])) {
        $items = [];
        foreach ($meta['breadcrumbs'] as $i => $b) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $b['name'],
                'item' => abs_url($b['url']),
            ];
        }
        $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => $items];
    }

    // Page-specific nodes (Service, Article, FAQPage, …).
    foreach (($meta['schema'] ?? []) as $node) {
        $graph[] = $node;
    }

    $data = ['@context' => 'https://schema.org', '@graph' => $graph];
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
    return "<script type=\"application/ld+json\">$json</script>\n";
}

/** Optional analytics snippet (GA4) — only if an ID is configured. */
function analytics_snippet(): string
{
    $id = setting('analytics_id');
    if ($id === '' || !preg_match('/^G-[A-Z0-9]+$/', $id)) {
        return '';
    }
    $id = esc($id);
    return "<script async src=\"https://www.googletagmanager.com/gtag/js?id=$id\"></script>\n"
        . "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','$id');</script>\n";
}

function analytics_enabled(): bool
{
    return (bool) preg_match('/^G-[A-Z0-9]+$/', setting('analytics_id'));
}
