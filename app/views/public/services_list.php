<?php
/** @var array $services */
$svcIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>';
$meta = ['title' => 'Services — Galilea Global Logistics', 'description' => 'Sea & air freight, road transport, warehousing, customs clearance and China sourcing — full-service logistics from Galilea.',
    'breadcrumbs' => [['name' => 'Home', 'url' => '/'], ['name' => 'Services', 'url' => '/services']]];
require __DIR__ . '/partials/head.php';
?>
<header class="page-hero">
  <div class="container">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><span aria-current="page">Services</span></nav>
    <p class="section-label" style="color:#C9A84C">What We Do</p>
    <h1 class="page-hero-title">Complete multimodal logistics solutions</h1>
    <p class="page-hero-sub">From Kigali to Guangzhou — sea, air, land, customs, warehousing and sourcing, delivered with precision.</p>
  </div>
</header>

<section class="section-pad">
  <div class="container">
    <div class="services-grid">
      <?php foreach ($services as $svc): ?>
      <a class="service-card" href="/services/<?= esc($svc['slug']) ?>">
        <?php if (!empty($svc['image_path'])): ?>
          <div class="sc-icon-wrap" style="padding:0;overflow:hidden"><img src="<?= esc($svc['image_path']) ?>" alt="<?= esc($svc['title']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;border-radius:inherit"></div>
        <?php else: ?>
          <div class="sc-icon-wrap"><?= $svcIcon ?></div>
        <?php endif; ?>
        <h2 class="sc-title"><?= esc($svc['title']) ?></h2>
        <p class="sc-body"><?= esc($svc['short_description']) ?></p>
        <span class="sc-more">Learn more <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
      </a>
      <?php endforeach; ?>
    </div>
    <div class="cta-band">
      <div><h2>Not sure which service fits?</h2><p>Talk to our team and get a tailored quote within one business day.</p></div>
      <a href="/contact" class="btn-gold-lg">Request a Quote</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/partials/foot.php'; ?>
