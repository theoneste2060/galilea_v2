<?php
/** @var array $settings @var array $hero @var array $services @var array $news @var array $testimonials @var array $team */
$s = fn(string $k, string $d = '') => esc($settings[$k] ?? $d);
$raw = fn(string $k, string $d = '') => $settings[$k] ?? $d;

// Default service icon (used when a service has no uploaded image).
$svcIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12"/></svg>';
$testChunks = array_chunk($testimonials, 3);
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $s('seo_title', 'Galilea Global Logistics — Trusted Trade. Global Reach.') ?></title>
<meta name="description" content="<?= $s('seo_description') ?>">
<link rel="icon" href="/assets/img/logo.jpeg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/site.css">
</head>
<body>

<!-- Lightweight preloader so first paint is clean while fonts/images settle -->
<div id="preloader"><div class="pl-logo"><img src="/assets/img/logo.jpeg" alt="Galilea"></div></div>

<!-- ── TOP UTILITY BAR ── -->
<div class="top-bar">
  <div class="container">
    <div class="top-bar-left">
      <a href="mailto:<?= $s('site_email') ?>" class="top-bar-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 8l10 6 10-6"/></svg>
        <?= $s('site_email') ?>
      </a>
      <div class="divider"></div>
      <a href="tel:<?= esc(preg_replace('/\s+/', '', $raw('phone_rw'))) ?>" class="tb-btn tb-btn-phone">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.22 1.18 2 2 0 012.18 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.09a16 16 0 006 6l.56-.56a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
        <?= $s('phone_rw') ?>
      </a>
      <a href="tel:<?= esc(preg_replace('/\s+/', '', $raw('phone_cn'))) ?>" class="tb-btn tb-btn-phone">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.22 1.18 2 2 0 012.18 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.09a16 16 0 006 6l.56-.56a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
        <?= $s('phone_cn') ?>
      </a>
    </div>
    <div class="top-bar-right">
      <span class="tb-btn tb-btn-location">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <?= $s('address_kigali', 'Nyarugenge, Kigali') ?> 🇷🇼
      </span>
    </div>
  </div>
</div>

<!-- ── NAVIGATION ── -->
<nav id="mainNav">
  <div class="container">
    <div class="nav-inner">
      <a href="#" class="nav-logo">
        <img src="/assets/img/logo.jpeg" class="logo-mark" alt="Galilea Global Logistics" style="width:46px;height:46px;border-radius:50%;object-fit:cover;flex-shrink:0;">
        <div><span class="logo-text">Galilea Global</span><span class="logo-sub">Logistics Ltd.</span></div>
      </a>
      <div class="nav-links">
        <div class="nav-item">
          <button class="nav-link-btn">Services
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="dropdown-panel">
            <?php foreach (array_slice($services, 0, 5) as $svc): ?>
            <a href="#services">
              <div class="dp-icon"><?= $svcIcon ?></div>
              <div class="dp-text"><?= esc($svc['title']) ?><span><?= esc($svc['short_description']) ?></span></div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <a href="#tracking" class="nav-link-btn">Track &amp; Trace</a>
        <a href="#services" class="nav-link-btn">Solutions</a>
        <a href="#news" class="nav-link-btn">Insights</a>
        <a href="#team" class="nav-link-btn">About</a>
      </div>
      <div class="nav-right">
        <a href="/admin.php" class="nav-btn-ghost" style="text-decoration:none;display:inline-flex;align-items:center">Sign In</a>
        <a href="#contact" class="nav-btn-primary" style="text-decoration:none;display:inline-flex;align-items:center">Get a Quote</a>
        <button class="hamburger" id="hamburgerBtn" aria-label="Open menu">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.88)" stroke-width="2.5"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
        </button>
      </div>
    </div>
  </div>
</nav>

<!-- ── MOBILE NAV ── -->
<div class="mobile-nav" id="mobileNav">
  <div class="mn-header">
    <span class="mn-logo">Galilea Global Logistics</span>
    <button class="mn-close" id="mnClose"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
  </div>
  <div class="mn-body">
    <a href="#services" class="mn-link">Services</a>
    <a href="#tracking" class="mn-link">Track &amp; Trace</a>
    <a href="#news" class="mn-link">Insights</a>
    <a href="#team" class="mn-link">Our Team</a>
    <a href="#testimonials" class="mn-link">Client Stories</a>
    <a href="#contact" class="mn-link">Contact</a>
  </div>
  <div class="mn-actions">
    <a href="/admin.php" style="padding:14px;background:#0D2645;color:#fff;border:none;border-radius:6px;font-weight:700;font-size:15px;text-align:center;text-decoration:none">Sign In</a>
    <a href="#contact" style="padding:14px;background:#C9A84C;color:#0D2645;border:none;border-radius:6px;font-weight:700;font-size:15px;text-align:center;text-decoration:none">Get a Quote</a>
  </div>
</div>

<!-- ── HERO ── -->
<section class="hero" id="hero">
  <div class="hero-slides">
    <?php foreach ($hero as $i => $h): ?>
    <div class="hero-slide<?= $i === 0 ? ' active' : '' ?>">
      <?php if (!empty($h['image_path'])): ?><img src="<?= esc($h['image_path']) ?>" alt="<?= esc($h['title']) ?>"><?php endif; ?>
      <div class="overlay"></div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="hero-content">
    <div class="container">
      <div style="max-width:680px">
        <?php $h0 = $hero[0] ?? ['eyebrow' => $raw('hero_eyebrow'), 'title' => 'Your cargo moves. Your business grows.', 'body' => '']; ?>
        <p class="hero-eyebrow"><?= esc($h0['eyebrow'] ?: $raw('hero_eyebrow')) ?></p>
        <h1 class="hero-title"><?= esc($h0['title']) ?></h1>
        <p class="hero-body"><?= esc($h0['body']) ?></p>
        <div class="hero-actions">
          <a href="#tracking" class="btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            Track a Shipment
          </a>
          <a href="#services" class="btn-outline-white">Explore Services
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
        </div>
      </div>
    </div>
  </div>
  <?php if (count($hero) > 1): ?>
  <div class="hero-pagination">
    <?php foreach ($hero as $i => $h): ?><button class="hero-dot<?= $i === 0 ? ' active' : '' ?>" data-slide="<?= $i ?>"></button><?php endforeach; ?>
  </div>
  <?php endif; ?>
  <div class="scroll-hint">Scroll<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg></div>
</section>

<!-- ── QUICK TOOLS ── -->
<div class="quick-tools">
  <div class="container">
    <div class="quick-tools-inner">
      <a href="#tracking" class="qt-tab active"><div class="qt-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg></div><div class="qt-label">Track &amp; Trace <span>Monitor your shipment</span></div></a>
      <a href="#services" class="qt-tab"><div class="qt-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/></svg></div><div class="qt-label">China Sourcing <span>Supplier connections</span></div></a>
      <a href="#services" class="qt-tab"><div class="qt-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div><div class="qt-label">Schedules <span>Vessel &amp; flight times</span></div></a>
      <a href="#contact" class="qt-tab"><div class="qt-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg></div><div class="qt-label">Bookings <span>Create &amp; manage</span></div></a>
      <a href="#services" class="qt-tab"><div class="qt-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></div><div class="qt-label">Customs Docs <span>Clearance &amp; compliance</span></div></a>
    </div>
  </div>
</div>

<!-- ── SERVICES ── -->
<section class="services-section" id="services">
  <div class="container">
    <div class="services-header reveal">
      <div><p class="section-label">What We Do</p><h2 class="section-title">Complete multimodal<br>logistics solutions</h2></div>
      <a href="#contact" class="btn-link">All services<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
    <div class="services-grid reveal">
      <?php foreach ($services as $svc): ?>
      <div class="service-card">
        <?php if (!empty($svc['image_path'])): ?>
          <div class="sc-icon-wrap" style="padding:0;overflow:hidden"><img src="<?= esc($svc['image_path']) ?>" alt="<?= esc($svc['title']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;border-radius:inherit"></div>
        <?php else: ?>
          <div class="sc-icon-wrap"><?= $svcIcon ?></div>
        <?php endif; ?>
        <h3 class="sc-title"><?= esc($svc['title']) ?></h3>
        <p class="sc-body"><?= esc($svc['short_description']) ?></p>
        <a href="#contact" class="sc-more">Learn more <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── STATS ── -->
<section class="stats-section">
  <div class="container">
    <div class="stats-intro reveal">
      <p class="section-label">By the Numbers</p>
      <h2 class="section-title">Trusted across borders,<br>proven by results</h2>
      <p class="section-body">Our infrastructure spans every major trade corridor, delivering scale with the precision of a specialist partner.</p>
    </div>
    <div class="stats-grid reveal">
      <div class="stat-card"><div class="stat-number"><span class="count" data-target="<?= (int) $raw('stat_countries', '130') ?>">0</span><span class="stat-suffix">+</span></div><div class="stat-label">Countries Served</div><div class="stat-desc">Active global network</div></div>
      <div class="stat-card"><div class="stat-number"><span class="count" data-target="<?= (int) $raw('stat_ports', '48') ?>">0</span><span class="stat-suffix">+</span></div><div class="stat-label">Port Partners</div><div class="stat-desc">Major global terminals</div></div>
      <div class="stat-card"><div class="stat-number"><span class="count" data-target="<?= (int) $raw('stat_ontime', '99') ?>">0</span><span class="stat-suffix">%</span></div><div class="stat-label">On-time Rate</div><div class="stat-desc">Industry-leading reliability</div></div>
      <div class="stat-card"><div class="stat-number"><span class="count" data-target="<?= (int) $raw('stat_support', '24') ?>">0</span><span class="stat-suffix">/7</span></div><div class="stat-label">Operations Support</div><div class="stat-desc">Always-on team</div></div>
    </div>
  </div>
</section>

<!-- ── TRACKING ── -->
<section class="tracking-section" id="tracking">
  <div class="container">
    <div class="tracking-layout">
      <div class="tracking-visual reveal">
        <img src="/assets/img/logistics-warehouse.jpg" alt="Port operations" loading="lazy">
        <div class="track-overlay">
          <div class="track-route">
            <div class="track-port"><div class="port-code">KGL</div><div class="port-name">Kigali, RW</div></div>
            <div class="track-arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
            <div class="track-port"><div class="port-code">CAN</div><div class="port-name">Guangzhou, CN</div></div>
          </div>
          <div class="track-status"><div class="status-dot"></div>Live vessel tracking — updated every 15 minutes</div>
        </div>
      </div>
      <div class="tracking-form-wrap reveal">
        <p class="section-label">Track &amp; Trace</p>
        <h2 class="section-title">Follow your shipment live</h2>
        <p class="section-body" style="margin-bottom:0">Enter your container, booking, or bill of lading number. Need help? Call <a href="tel:<?= esc(preg_replace('/\s+/', '', $raw('phone_rw'))) ?>" style="color:#0D2645;font-weight:600;"><?= $s('phone_rw') ?></a></p>
        <div class="tracking-input-group" style="margin-top:22px">
          <input type="text" id="trackInput" placeholder="e.g. GALU1234567" autocomplete="off">
          <button class="track-submit" id="trackBtn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>Track</button>
        </div>
        <div class="tracking-result" id="trackResult"></div>
      </div>
    </div>
  </div>
</section>

<!-- ── NEWS ── -->
<section class="news-section" id="news">
  <div class="container">
    <div class="news-header reveal">
      <div><p class="section-label">Insights &amp; News</p><h2 class="section-title">Galilea Insights &amp; Updates</h2></div>
      <a href="#contact" class="btn-link">All insights<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
    <div class="news-grid reveal">
      <?php foreach ($news as $i => $n): ?>
      <div class="news-card<?= $i === 0 ? ' featured' : '' ?>">
        <div class="nc-img"><?php if (!empty($n['image_path'])): ?><img src="<?= esc($n['image_path']) ?>" alt="<?= esc($n['title']) ?>" loading="lazy"><?php endif; ?></div>
        <div class="nc-body">
          <span class="nc-tag tag-green"><?= esc($n['category']) ?></span>
          <h3 class="nc-title"><?= esc($n['title']) ?></h3>
          <p class="nc-meta"><?= esc(date('F j, Y', strtotime($n['published_at']))) ?></p>
          <p class="nc-excerpt"><?= esc($n['excerpt']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── WHY GALILEA ── -->
<section class="why-section">
  <div class="container">
    <div class="why-layout">
      <div class="why-left reveal">
        <p class="section-label">The Galilea Difference</p>
        <h2 class="section-title">Why businesses trust us with their supply chains</h2>
        <p class="section-body">From Kigali to Guangzhou, Yiwu to your door — we handle sea, air, land, customs, sourcing, and financial support so your business keeps moving.</p>
        <div class="promise-quote"><p>"Your Trusted Logistics Partner Worldwide — Connecting the World, Delivering Excellence."</p><cite>— Galilea Global Logistics</cite></div>
      </div>
      <div class="why-grid reveal">
        <div class="why-card"><div class="wc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/></svg></div><div class="wc-title">Global Network</div><div class="wc-body">Access to every major trade hub via sea, air, and land — 130+ countries, 48+ port partners.</div></div>
        <div class="why-card"><div class="wc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></div><div class="wc-title">Full Transparency</div><div class="wc-body">Real-time tracking and clear communication at every milestone, with zero surprises.</div></div>
        <div class="why-card"><div class="wc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div><div class="wc-title">Full-Service Logistics</div><div class="wc-body">Sea, air, land, customs, warehousing, sourcing and financial support — one partner, every step.</div></div>
        <div class="why-card"><div class="wc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></div><div class="wc-title">China–Africa Corridor</div><div class="wc-body">Offices in Kigali, Guangzhou &amp; Yiwu — direct access to Chinese factories and suppliers.</div></div>
      </div>
    </div>
  </div>
</section>

<!-- ── TEAM ── -->
<section class="team-section" id="team">
  <div class="container">
    <div class="team-header reveal"><p class="section-label">Our Leadership</p><h2 class="section-title">The people behind<br>Trusted Trade</h2></div>
    <div class="team-grid reveal">
      <?php foreach ($team as $m): ?>
      <div class="team-card">
        <div class="team-photo">
          <?php if (!empty($m['image_path'])): ?><img src="<?= esc($m['image_path']) ?>" alt="<?= esc($m['full_name']) ?>" loading="lazy">
          <?php else: ?><div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#0D2645;color:#C9A84C;font-family:'Playfair Display',serif;font-size:34px;font-weight:800"><?= esc(mb_substr($m['full_name'], 0, 1)) ?></div><?php endif; ?>
        </div>
        <div class="team-info">
          <div class="team-name"><?= esc($m['full_name']) ?></div>
          <div class="team-role"><?= esc($m['role']) ?></div>
          <div class="team-socials">
            <?php if (!empty($m['phone'])): ?><a href="tel:<?= esc(preg_replace('/\s+/', '', $m['phone'])) ?>" class="team-social-btn" aria-label="Phone"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.22 1.18 2 2 0 012.18 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.09a16 16 0 006 6l.56-.56a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg></a><?php endif; ?>
            <?php if (!empty($m['email'])): ?><a href="mailto:<?= esc($m['email']) ?>" class="team-social-btn" aria-label="Email"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 8l10 6 10-6"/></svg></a><?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── TESTIMONIALS ── -->
<?php if ($testimonials): ?>
<section class="testimonials-section" id="testimonials">
  <div class="container">
    <div class="testimonials-header reveal"><p class="section-label">Client Stories</p><h2 class="section-title">Trusted by businesses<br>across the globe</h2></div>
    <div class="tslider-wrap reveal">
      <div class="tslider-track" id="tsliderTrack">
        <?php foreach ($testChunks as $chunk): ?>
        <div class="tslide"><div class="testimonials-row">
          <?php foreach ($chunk as $j => $t): $initials = strtoupper(mb_substr($t['client_name'], 0, 1) . (str_contains($t['client_name'], ' ') ? mb_substr(strrchr($t['client_name'], ' '), 1, 1) : '')); ?>
          <div class="testimonial-card<?= $j === 0 ? ' featured' : '' ?>">
            <span class="quote-mark">"</span>
            <p class="testimonial-text"><?= esc($t['quote']) ?></p>
            <div class="testimonial-stars"><?= str_repeat('<span>★</span>', max(1, min(5, (int) $t['rating']))) ?></div>
            <div class="testimonial-author">
              <div class="author-avatar"><?= esc($initials) ?></div>
              <div><div class="author-name"><?= esc($t['client_name']) ?> <span class="author-flag"><?= esc($t['country_flag']) ?></span></div><div class="author-company"><?= esc($t['company']) ?></div></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div></div>
        <?php endforeach; ?>
      </div>
      <?php if (count($testChunks) > 1): ?>
      <div class="tslider-controls">
        <button class="tslider-btn" id="tPrev" aria-label="Previous"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg></button>
        <div class="tslider-dots"><?php foreach ($testChunks as $i => $c): ?><button class="tslider-dot<?= $i === 0 ? ' active' : '' ?>" data-slide="<?= $i ?>"></button><?php endforeach; ?></div>
        <button class="tslider-btn" id="tNext" aria-label="Next"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg></button>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── CONTACT / QUOTE ── -->
<section class="newsletter-section" id="contact">
  <div class="container">
    <div class="newsletter-inner reveal" style="max-width:680px">
      <p class="section-label">Get In Touch</p>
      <h2 class="section-title">Request a quote</h2>
      <p class="section-body">Tell us what you need to move. Our team replies within one business day. Or email <a href="mailto:<?= $s('site_email') ?>" style="color:#0D2645;font-weight:600;"><?= $s('site_email') ?></a></p>
      <form id="inquiryForm" class="inquiry-form" autocomplete="on">
        <?= csrf_field() ?>
        <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
        <div class="if-row">
          <input type="text" name="full_name" placeholder="Full name *" required>
          <input type="email" name="email" placeholder="Email address *" required>
        </div>
        <div class="if-row">
          <input type="text" name="phone" placeholder="Phone (optional)">
          <input type="text" name="company" placeholder="Company (optional)">
        </div>
        <select name="service_interest">
          <option value="General Inquiry">What can we help with?</option>
          <?php foreach ($services as $svc): ?><option value="<?= esc($svc['title']) ?>"><?= esc($svc['title']) ?></option><?php endforeach; ?>
        </select>
        <textarea name="message" rows="4" placeholder="Tell us about your shipment or request *" required></textarea>
        <button type="submit" class="nl-submit" style="width:100%">Send Request</button>
        <p class="form-msg" id="inquiryMsg" role="status"></p>
      </form>
    </div>
  </div>
</section>

<!-- ── NEWSLETTER ── -->
<section class="newsletter-section" style="padding-top:0">
  <div class="container">
    <div class="newsletter-inner reveal">
      <p class="section-label">Stay Informed</p>
      <h2 class="section-title">Stay connected with Galilea</h2>
      <p class="section-body">Get shipping updates, China sourcing tips, and East Africa trade news in your inbox.</p>
      <form class="nl-form" id="newsletterForm">
        <?= csrf_field() ?>
        <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
        <input type="email" name="email" placeholder="Your business email address" required>
        <button class="nl-submit" type="submit">Subscribe</button>
      </form>
      <p class="form-msg" id="newsletterMsg" role="status"></p>
    </div>
  </div>
</section>

<!-- ── CONTACT STRIP ── -->
<div style="background:#0D2645;padding:40px 0;border-top:3px solid #C9A84C;">
  <div class="container">
    <div class="contact-strip">
      <div><div class="cs-label">Email Us</div><a href="mailto:<?= $s('site_email') ?>" class="cs-a"><?= $s('site_email') ?></a><a href="mailto:<?= $s('support_email') ?>" class="cs-a dim"><?= $s('support_email') ?></a></div>
      <div><div class="cs-label">Rwanda Office</div><a href="tel:<?= esc(preg_replace('/\s+/', '', $raw('phone_rw'))) ?>" class="cs-a"><?= $s('phone_rw') ?></a><a href="tel:<?= esc(preg_replace('/\s+/', '', $raw('phone_rw_alt'))) ?>" class="cs-a dim"><?= $s('phone_rw_alt') ?></a></div>
      <div><div class="cs-label">China Office</div><a href="tel:<?= esc(preg_replace('/\s+/', '', $raw('phone_cn'))) ?>" class="cs-a"><?= $s('phone_cn') ?></a><span class="cs-a dim">Guangzhou &amp; Yiwu</span></div>
      <div><div class="cs-label">Kigali Address</div><span class="cs-a"><?= $s('address_kigali') ?></span><span class="cs-a dim">Nyarugenge, Kigali 🇷🇼</span></div>
    </div>
  </div>
</div>

<!-- ── FOOTER ── -->
<footer>
  <div class="container">
    <div class="footer-top">
      <div class="footer-brand">
        <a href="#" class="footer-logo">
          <img src="/assets/img/logo.jpeg" style="width:40px;height:40px;border-radius:50%;object-fit:cover;flex-shrink:0;" alt="Galilea Global Logistics">
          <div><span class="logo-text">Galilea Global</span><span class="logo-sub">Logistics Ltd.</span></div>
        </a>
        <p class="footer-tagline">Trusted Trade. Global Reach. Connecting East Africa and the world through seamless, technology-driven supply chain solutions.</p>
        <div class="footer-socials">
          <a href="#" class="social-btn" aria-label="LinkedIn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></a>
          <a href="#" class="social-btn" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg></a>
          <a href="#" class="social-btn" aria-label="YouTube"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46A2.78 2.78 0 001.46 6.42 29 29 0 001 12a29 29 0 00.46 5.58 2.78 2.78 0 001.95 1.96C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg></a>
        </div>
      </div>
      <div class="footer-col"><h5>Services</h5><ul><?php foreach (array_slice($services, 0, 6) as $svc): ?><li><a href="#services"><?= esc($svc['title']) ?></a></li><?php endforeach; ?></ul></div>
      <div class="footer-col"><h5>Tools</h5><ul><li><a href="#tracking">Track &amp; Trace</a></li><li><a href="#contact">Get a Quote</a></li><li><a href="#news">Insights</a></li><li><a href="#team">Our Team</a></li><li><a href="/admin.php">Admin Sign In</a></li></ul></div>
      <div class="footer-col"><h5>Contact</h5><ul><li><a href="mailto:<?= $s('site_email') ?>"><?= $s('site_email') ?></a></li><li><a href="mailto:<?= $s('support_email') ?>"><?= $s('support_email') ?></a></li><li><a href="tel:<?= esc(preg_replace('/\s+/', '', $raw('phone_rw'))) ?>"><?= $s('phone_rw') ?></a></li><li><a href="tel:<?= esc(preg_replace('/\s+/', '', $raw('phone_cn'))) ?>"><?= $s('phone_cn') ?></a></li><li><?= $s('address_kigali') ?></li></ul></div>
    </div>
    <div class="footer-bottom">
      <p>© <?= date('Y') ?> Galilea Global Logistics Ltd. · Kigali, Rwanda</p>
      <div class="footer-bottom-links"><a href="#">Privacy</a><a href="#">Terms</a><a href="#">Cookies</a><a href="#contact">Sitemap</a></div>
    </div>
  </div>
</footer>

<button id="btt" aria-label="Back to top"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 15l7-7 7 7"/></svg></button>

<script src="/assets/js/site.js" defer></script>
</body>
</html>
