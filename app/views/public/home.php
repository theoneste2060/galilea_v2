<?php
/** @var array $hero @var array $services @var array $news @var array $testimonials @var array $team */
$st = site_settings();
$g = fn(string $k, string $d = '') => esc($st[$k] ?? $d);
$svcIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12"/></svg>';
$testChunks = array_chunk($testimonials, 3);
$meta = ['title' => setting('seo_title', 'Galilea Global Logistics — Trusted Trade. Global Reach.'), 'description' => setting('seo_description')];
require __DIR__ . '/partials/head.php';
?>

<!-- ── HERO ── -->
<section class="hero" id="hero">
  <div class="hero-slides">
    <?php foreach ($hero as $i => $h): ?>
    <div class="hero-slide<?= $i === 0 ? ' active' : '' ?>">
      <?php if (!empty($h['image_path'])): ?><img src="<?= esc($h['image_path']) ?>" alt="" <?= $i === 0 ? '' : 'loading="lazy"' ?>><?php endif; ?>
      <div class="overlay"></div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="hero-content">
    <div class="container">
      <div style="max-width:680px">
        <?php $h0 = $hero[0] ?? ['eyebrow' => $st['hero_eyebrow'] ?? '', 'title' => 'Your cargo moves. Your business grows.', 'body' => '']; ?>
        <p class="hero-eyebrow"><?= esc($h0['eyebrow'] ?: ($st['hero_eyebrow'] ?? '')) ?></p>
        <h1 class="hero-title"><?= esc($h0['title']) ?></h1>
        <p class="hero-body"><?= esc($h0['body']) ?></p>
        <div class="hero-actions">
          <a href="/track" class="btn-primary"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>Track a Shipment</a>
          <a href="/services" class="btn-outline-white">Explore Services<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
        </div>
      </div>
    </div>
  </div>
  <?php if (count($hero) > 1): ?>
  <div class="hero-pagination"><?php foreach ($hero as $i => $h): ?><button class="hero-dot<?= $i === 0 ? ' active' : '' ?>" data-slide="<?= $i ?>" aria-label="Slide <?= $i + 1 ?>"></button><?php endforeach; ?></div>
  <?php endif; ?>
</section>

<!-- ── QUICK TOOLS ── -->
<div class="quick-tools">
  <div class="container">
    <div class="quick-tools-inner">
      <a href="/track" class="qt-tab active"><div class="qt-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg></div><div class="qt-label">Track &amp; Trace <span>Monitor your shipment</span></div></a>
      <a href="/services/china-business-connection" class="qt-tab"><div class="qt-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/></svg></div><div class="qt-label">China Sourcing <span>Supplier connections</span></div></a>
      <a href="/services" class="qt-tab"><div class="qt-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div><div class="qt-label">Schedules <span>Vessel &amp; flight times</span></div></a>
      <a href="/contact" class="qt-tab"><div class="qt-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg></div><div class="qt-label">Bookings <span>Create &amp; manage</span></div></a>
      <a href="/services/customs-clearance" class="qt-tab"><div class="qt-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></div><div class="qt-label">Customs Docs <span>Clearance &amp; compliance</span></div></a>
    </div>
  </div>
</div>

<!-- ── SERVICES ── -->
<section class="services-section" id="services">
  <div class="container">
    <div class="services-header reveal">
      <div><p class="section-label">What We Do</p><h2 class="section-title">Complete multimodal<br>logistics solutions</h2></div>
      <a href="/services" class="btn-link">All services<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
    <div class="services-grid reveal">
      <?php foreach ($services as $svc): ?>
      <a class="service-card" href="/services/<?= esc($svc['slug']) ?>">
        <?php if (!empty($svc['image_path'])): ?>
          <div class="sc-icon-wrap" style="padding:0;overflow:hidden"><img src="<?= esc($svc['image_path']) ?>" alt="<?= esc($svc['title']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;border-radius:inherit"></div>
        <?php else: ?>
          <div class="sc-icon-wrap"><?= $svcIcon ?></div>
        <?php endif; ?>
        <h3 class="sc-title"><?= esc($svc['title']) ?></h3>
        <p class="sc-body"><?= esc($svc['short_description']) ?></p>
        <span class="sc-more">Learn more <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
      </a>
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
      <div class="stat-card"><div class="stat-number"><span class="count" data-target="<?= (int) ($st['stat_countries'] ?? 130) ?>">0</span><span class="stat-suffix">+</span></div><div class="stat-label">Countries Served</div><div class="stat-desc">Active global network</div></div>
      <div class="stat-card"><div class="stat-number"><span class="count" data-target="<?= (int) ($st['stat_ports'] ?? 48) ?>">0</span><span class="stat-suffix">+</span></div><div class="stat-label">Port Partners</div><div class="stat-desc">Major global terminals</div></div>
      <div class="stat-card"><div class="stat-number"><span class="count" data-target="<?= (int) ($st['stat_ontime'] ?? 99) ?>">0</span><span class="stat-suffix">%</span></div><div class="stat-label">On-time Rate</div><div class="stat-desc">Industry-leading reliability</div></div>
      <div class="stat-card"><div class="stat-number"><span class="count" data-target="<?= (int) ($st['stat_support'] ?? 24) ?>">0</span><span class="stat-suffix">/7</span></div><div class="stat-label">Operations Support</div><div class="stat-desc">Always-on team</div></div>
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
            <div class="track-arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
            <div class="track-port"><div class="port-code">CAN</div><div class="port-name">Guangzhou, CN</div></div>
          </div>
          <div class="track-status"><div class="status-dot"></div>Live vessel tracking — updated every 15 minutes</div>
        </div>
      </div>
      <div class="tracking-form-wrap reveal">
        <p class="section-label">Track &amp; Trace</p>
        <h2 class="section-title">Follow your shipment live</h2>
        <p class="section-body" style="margin-bottom:0">Enter your container, booking, or bill of lading number. Need help? Call <a href="<?= esc(tel_href($st['phone_rw'] ?? '')) ?>" style="color:#0D2645;font-weight:600;"><?= $g('phone_rw') ?></a></p>
        <form class="tracking-input-group" style="margin-top:22px" id="trackForm" role="search">
          <input type="text" id="trackInput" placeholder="e.g. GALU1234567" autocomplete="off" aria-label="Shipment reference">
          <button class="track-submit" id="trackBtn" type="submit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>Track</button>
        </form>
        <div class="tracking-result" id="trackResult" aria-live="polite"></div>
      </div>
    </div>
  </div>
</section>

<!-- ── NEWS ── -->
<section class="news-section" id="news">
  <div class="container">
    <div class="news-header reveal">
      <div><p class="section-label">Insights &amp; News</p><h2 class="section-title">Galilea Insights &amp; Updates</h2></div>
      <a href="/insights" class="btn-link">All insights<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
    <div class="news-grid reveal">
      <?php foreach ($news as $i => $n): ?>
      <a class="news-card<?= $i === 0 ? ' featured' : '' ?>" href="/insights/<?= esc($n['slug']) ?>">
        <div class="nc-img"><?php if (!empty($n['image_path'])): ?><img src="<?= esc($n['image_path']) ?>" alt="<?= esc($n['title']) ?>" loading="lazy"><?php endif; ?></div>
        <div class="nc-body">
          <span class="nc-tag tag-green"><?= esc($n['category']) ?></span>
          <h3 class="nc-title"><?= esc($n['title']) ?></h3>
          <p class="nc-meta"><?= esc(date('F j, Y', strtotime($n['published_at']))) ?></p>
          <p class="nc-excerpt"><?= esc($n['excerpt']) ?></p>
        </div>
      </a>
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
        <div class="why-card"><div class="wc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/></svg></div><div class="wc-title">Global Network</div><div class="wc-body">Access to every major trade hub via sea, air, and land — 130+ countries, 48+ port partners.</div></div>
        <div class="why-card"><div class="wc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></div><div class="wc-title">Full Transparency</div><div class="wc-body">Real-time tracking and clear communication at every milestone, with zero surprises.</div></div>
        <div class="why-card"><div class="wc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div><div class="wc-title">Full-Service Logistics</div><div class="wc-body">Sea, air, land, customs, warehousing, sourcing and financial support — one partner, every step.</div></div>
        <div class="why-card"><div class="wc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></div><div class="wc-title">China–Africa Corridor</div><div class="wc-body">Offices in Kigali, Guangzhou &amp; Yiwu — direct access to Chinese factories and suppliers.</div></div>
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
          <?php else: ?><div class="team-initial"><?= esc(mb_substr($m['full_name'], 0, 1)) ?></div><?php endif; ?>
        </div>
        <div class="team-info">
          <div class="team-name"><?= esc($m['full_name']) ?></div>
          <div class="team-role"><?= esc($m['role']) ?></div>
          <div class="team-socials">
            <?php if (!empty($m['phone'])): ?><a href="<?= esc(tel_href($m['phone'])) ?>" class="team-social-btn" aria-label="Call <?= esc($m['full_name']) ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.22 1.18 2 2 0 012.18 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.09a16 16 0 006 6l.56-.56a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg></a><?php endif; ?>
            <?php if (!empty($m['email'])): ?><a href="mailto:<?= esc($m['email']) ?>" class="team-social-btn" aria-label="Email <?= esc($m['full_name']) ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 8l10 6 10-6"/></svg></a><?php endif; ?>
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
            <div class="testimonial-stars" aria-label="<?= (int) $t['rating'] ?> out of 5 stars"><?= str_repeat('<span>★</span>', max(1, min(5, (int) $t['rating']))) ?></div>
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
        <button class="tslider-btn" id="tPrev" aria-label="Previous testimonials"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg></button>
        <div class="tslider-dots"><?php foreach ($testChunks as $i => $c): ?><button class="tslider-dot<?= $i === 0 ? ' active' : '' ?>" data-slide="<?= $i ?>" aria-label="Testimonial group <?= $i + 1 ?>"></button><?php endforeach; ?></div>
        <button class="tslider-btn" id="tNext" aria-label="Next testimonials"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg></button>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── QUOTE / CONTACT ── -->
<section class="newsletter-section" id="contact">
  <div class="container">
    <div class="newsletter-inner reveal" style="max-width:680px">
      <p class="section-label">Get In Touch</p>
      <h2 class="section-title">Request a quote</h2>
      <p class="section-body">Tell us what you need to move. Our team replies within one business day. Or email <a href="mailto:<?= $g('site_email') ?>" style="color:#0D2645;font-weight:600;"><?= $g('site_email') ?></a></p>
      <form id="inquiryForm" class="inquiry-form" autocomplete="on" novalidate>
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
      <form class="nl-form" id="newsletterForm" novalidate>
        <?= csrf_field() ?>
        <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
        <input type="email" name="email" placeholder="Your business email address" required aria-label="Email address">
        <button class="nl-submit" type="submit">Subscribe</button>
      </form>
      <p class="form-msg" id="newsletterMsg" role="status"></p>
    </div>
  </div>
</section>

<?php require __DIR__ . '/partials/foot.php'; ?>
