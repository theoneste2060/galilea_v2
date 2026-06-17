<?php
/**
 * Shared <head> + opening chrome for every public page.
 * Expects $meta = ['title' => ..., 'description' => ..., 'path' => ...].
 */
$meta = $meta ?? [];
$pageTitle = $meta['title'] ?? setting('seo_title', 'Galilea Global Logistics');
$pageDesc  = $meta['description'] ?? setting('seo_description', '');
$st = site_settings();
$g = fn(string $k, string $d = '') => esc($st[$k] ?? $d);
$menu = nav_menu();
$here = current_path();
$isCur = fn(string $u): string => ('/' . trim($u, '/')) === $here ? ' aria-current="page"' : '';
?><!DOCTYPE html>
<html lang="en">
<head>
<script src="/assets/js/theme.js"></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($pageTitle) ?></title>
<?= render_seo_head($meta) ?>
<link rel="icon" href="/assets/img/logo.jpeg">
<link rel="preload" as="font" type="font/woff2" href="/assets/fonts/montserrat-var.woff2" crossorigin>
<link rel="stylesheet" href="<?= esc(asset_url('/assets/css/site.css')) ?>">
<?= analytics_snippet() ?>
</head>
<body>
<a href="#main" class="skip-link">Skip to content</a>
<div class="scroll-progress" id="scrollProgress" aria-hidden="true"></div>
<div class="toast-wrap" id="toastWrap" aria-live="polite" aria-atomic="false"></div>

<!-- Cookie consent -->
<div class="cookie-bar" id="cookieBar" role="dialog" aria-label="Cookie notice" hidden>
  <p>We use essential cookies for security, and optional analytics cookies to improve your experience. See our <a href="/cookies">Cookie Policy</a>.</p>
  <div class="cookie-actions">
    <button class="cookie-btn ghost" id="cookieDecline">Decline</button>
    <button class="cookie-btn primary" id="cookieAccept">Accept</button>
  </div>
</div>

<!-- ── TOP UTILITY BAR ── -->
<div class="top-bar">
  <div class="container">
    <div class="top-bar-left">
      <a href="mailto:<?= $g('site_email') ?>" class="top-bar-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 8l10 6 10-6"/></svg>
        <?= $g('site_email') ?>
      </a>
      <div class="divider"></div>
      <a href="<?= esc(tel_href($st['phone_rw'] ?? '')) ?>" class="tb-btn tb-btn-phone">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.22 1.18 2 2 0 012.18 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.09a16 16 0 006 6l.56-.56a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
        <?= $g('phone_rw') ?>
      </a>
      <a href="<?= esc(tel_href($st['phone_cn'] ?? '')) ?>" class="tb-btn tb-btn-phone">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.22 1.18 2 2 0 012.18 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.09a16 16 0 006 6l.56-.56a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
        <?= $g('phone_cn') ?>
      </a>
    </div>
    <div class="top-bar-right">
      <span class="tb-btn tb-btn-location">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <?= $g('address_kigali', 'Nyarugenge, Kigali') ?> 🇷🇼
      </span>
    </div>
  </div>
</div>

<!-- ── NAVIGATION + MEGA MENU ── -->
<nav id="mainNav" aria-label="Primary">
  <div class="container">
    <div class="nav-inner">
      <a href="/" class="nav-logo" aria-label="Galilea Global Logistics — home">
        <img src="/assets/img/logo.jpeg" class="logo-mark" alt="" width="46" height="46" style="width:46px;height:46px;border-radius:50%;object-fit:cover;flex-shrink:0;">
        <div><span class="logo-text">Galilea Global</span><span class="logo-sub">Logistics Ltd.</span></div>
      </a>

      <ul class="nav-links" role="menubar">
        <?php foreach ($menu as $top): $hasKids = !empty($top['children']); $cols = []; ?>
          <?php if ($hasKids): foreach ($top['children'] as $c) { $cols[(int) $c['column_group']][] = $c; } ?>
          <li class="nav-item has-mega" role="none">
            <button class="nav-link-btn" role="menuitem" aria-haspopup="true" aria-expanded="false">
              <?= esc($top['title']) ?>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div class="mega-panel" role="menu" aria-label="<?= esc($top['title']) ?>">
              <div class="mega-cols">
                <?php
                // WebFX-style per-column headings (optional; render only when mapped).
                $megaHeadings = [
                    'Services'   => ['Freight & Transport', 'Specialised Services'],
                    'Solutions'  => ['By Capability', 'Manage & Track'],
                    'Industries' => ['Sectors We Serve', 'More Sectors'],
                    'Company'    => ['Company', 'Resources'],
                ];
                $ci = 0;
                foreach ($cols as $colItems): $ci++;
                  $heading = $megaHeadings[$top['title']][$ci - 1] ?? '';
                  $headIcon = $colItems[0]['icon'] ?? '';
                ?>
                <div class="mega-col">
                  <?php if ($heading): ?>
                  <div class="mega-col-head">
                    <span class="mega-col-title"><?= esc($heading) ?></span>
                    <?php if ($headIcon): ?><span class="mega-col-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><?= $headIcon ?></svg></span><?php endif; ?>
                  </div>
                  <?php endif; ?>
                  <?php foreach ($colItems as $c): ?>
                  <a href="<?= esc($c['url']) ?>" class="mega-link" role="menuitem">
                    <span class="mega-link-title"><?= esc($c['title']) ?></span>
                    <?php if (!empty($c['subtitle'])): ?><span class="mega-link-sub"><?= esc($c['subtitle']) ?></span><?php endif; ?>
                  </a>
                  <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
                <div class="mega-promo">
                  <span class="mega-promo-eyebrow">Track &amp; Trace</span>
                  <p class="mega-promo-title">Follow your shipment live</p>
                  <a href="/track" class="mega-promo-btn">Track now
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                  </a>
                </div>
              </div>
            </div>
          </li>
          <?php else: ?>
          <li class="nav-item" role="none"><a href="<?= esc($top['url']) ?>" class="nav-link-btn<?= $isCur($top['url']) ? ' is-current' : '' ?>"<?= $isCur($top['url']) ?> role="menuitem"><?= esc($top['title']) ?></a></li>
          <?php endif; ?>
        <?php endforeach; ?>
        <li class="nav-item" role="none"><a href="/track" class="nav-link-btn<?= $here === '/track' ? ' is-current' : '' ?>"<?= $here === '/track' ? ' aria-current="page"' : '' ?> role="menuitem">Track &amp; Trace</a></li>
      </ul>

      <div class="nav-right">
        <button class="search-toggle" id="searchToggle" aria-label="Search" aria-haspopup="dialog"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg></button>
        <button class="theme-toggle" id="themeToggle" type="button" aria-label="Toggle dark mode" title="Toggle dark mode">
          <svg class="ic-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>
          <svg class="ic-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
        </button>
        <a href="/admin.php" class="nav-btn-ghost">Sign In</a>
        <a href="/contact" class="nav-btn-primary">Get a Quote</a>
        <button class="hamburger" id="hamburgerBtn" aria-label="Open menu" aria-expanded="false" aria-controls="mobileNav">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.88)" stroke-width="2.5" aria-hidden="true"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
        </button>
      </div>
    </div>
  </div>
</nav>

<!-- ── MOBILE NAV (accordion mega) ── -->
<div class="mobile-nav" id="mobileNav" aria-hidden="true">
  <div class="mn-header">
    <span class="mn-logo">Galilea Global Logistics</span>
    <button class="mn-close" id="mnClose" aria-label="Close menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
  </div>
  <div class="mn-body">
    <?php foreach ($menu as $top): ?>
      <?php if (!empty($top['children'])): ?>
      <div class="mn-group">
        <button class="mn-acc" aria-expanded="false"><?= esc($top['title']) ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg></button>
        <div class="mn-sub">
          <?php foreach ($top['children'] as $c): ?><a href="<?= esc($c['url']) ?>" class="mn-link sub"><?= esc($c['title']) ?></a><?php endforeach; ?>
        </div>
      </div>
      <?php else: ?>
      <a href="<?= esc($top['url']) ?>" class="mn-link"><?= esc($top['title']) ?></a>
      <?php endif; ?>
    <?php endforeach; ?>
    <a href="/track" class="mn-link">Track &amp; Trace</a>
  </div>
  <div class="mn-actions">
    <a href="/admin.php" class="mn-act-ghost">Sign In</a>
    <a href="/contact" class="mn-act-primary">Get a Quote</a>
  </div>
</div>

<main id="main">
