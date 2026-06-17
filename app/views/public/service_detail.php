<?php
/** @var array $service @var array $more */
$meta = [
    'title' => $service['title'] . ' | Galilea Global Logistics',
    'description' => $service['short_description'],
    'image' => $service['image_path'] ?: '/assets/img/logo.jpeg',
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => '/'],
        ['name' => 'Services', 'url' => '/services'],
        ['name' => $service['title'], 'url' => '/services/' . $service['slug']],
    ],
    'schema' => [[
        '@type' => 'Service',
        'name' => $service['title'],
        'description' => $service['short_description'],
        'url' => abs_url('/services/' . $service['slug']),
        'serviceType' => $service['title'],
        'provider' => ['@id' => base_url() . '/#organization'],
        'areaServed' => ['Rwanda', 'East Africa', 'China', 'Worldwide'],
    ]],
];
$st = site_settings();
require __DIR__ . '/partials/head.php';
?>
<header class="page-hero" style="background:linear-gradient(135deg, #0D2645 0%, #1a3a5c 100%);position:relative;overflow:hidden">
  <?php if (!empty($service['image_path'])): ?>
  <div style="position:absolute;inset:0;opacity:0.12"><img src="<?= esc($service['image_path']) ?>" alt="" style="width:100%;height:100%;object-fit:cover"></div>
  <?php endif; ?>
  <div class="container" style="position:relative;z-index:1">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><a href="/services">Services</a><span>/</span><span aria-current="page"><?= esc($service['title']) ?></span></nav>
    <h1 class="page-hero-title" style="color:#fff"><?= esc($service['title']) ?></h1>
    <p class="page-hero-sub" style="color:rgba(255,255,255,0.75);max-width:640px"><?= esc($service['short_description']) ?></p>
  </div>
</header>

<section class="section-pad">
  <div class="container">
    <div class="detail-layout">
      <article class="article-body">
        <?php if (!empty($service['image_path'])): ?>
          <img src="<?= esc($service['image_path']) ?>" alt="<?= esc($service['title']) ?>" class="article-cover" loading="lazy">
        <?php endif; ?>
        <?= sanitize_html($service['description']) ?>
      </article>
      <aside class="detail-side">
        <div class="side-card">
          <h3>Get started</h3>
          <p>Request a quote for <?= esc($service['title']) ?> and our team will respond within one business day.</p>
          <a href="/contact" class="btn-gold-lg" style="width:100%;justify-content:center;margin-top:6px">Request a Quote</a>
          <a href="/track" class="btn-link" style="margin-top:14px">Track a shipment<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
        </div>
        <div class="side-card" style="margin-top:16px">
          <h3>Why Galilea</h3>
          <ul style="list-style:none;padding:0;margin:0;font-size:13px;line-height:1.6">
            <li style="padding:6px 0;border-bottom:1px solid var(--line,#e0e5ee)">&#10003; Licensed customs clearing</li>
            <li style="padding:6px 0;border-bottom:1px solid var(--line,#e0e5ee)">&#10003; Offices in China &amp; Rwanda</li>
            <li style="padding:6px 0;border-bottom:1px solid var(--line,#e0e5ee)">&#10003; Real-time tracking</li>
            <li style="padding:6px 0">&#10003; Single-point accountability</li>
          </ul>
        </div>
        <?php if (!empty($st['phone_rw'])): ?>
        <div class="side-card" style="margin-top:16px;text-align:center">
          <p style="font-size:12px;color:#8a95a7;margin:0 0 6px">Need help now?</p>
          <a href="tel:<?= esc($st['phone_rw']) ?>" style="font-size:18px;font-weight:700;color:var(--navy);text-decoration:none"><?= esc($st['phone_rw']) ?></a>
        </div>
        <?php endif; ?>
      </aside>
    </div>
  </div>
</section>

<?php if ($more): ?>
<section class="section-pad" style="padding-top:0">
  <div class="container">
    <div class="related-block">
      <div style="text-align:center;margin-bottom:32px">
        <p class="section-label">Explore More</p>
        <h2 class="section-title" style="font-size:24px">Other services you may need</h2>
        <p class="section-body" style="max-width:540px;margin:0 auto">Your supply chain does not stop at one mode. Explore our full range of complementary services.</p>
      </div>
      <div class="g3">
        <?php foreach ($more as $m): ?>
        <a class="mini-card" href="/services/<?= esc($m['slug']) ?>">
          <?php if (!empty($m['image_path'])): ?><img src="<?= esc($m['image_path']) ?>" alt="" loading="lazy"><?php endif; ?>
          <div class="mini-body"><h3><?= esc($m['title']) ?></h3><p><?= esc($m['short_description']) ?></p></div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section-pad" style="background:linear-gradient(135deg, #0D2645 0%, #1a3a5c 100%)">
  <div class="container">
    <div class="cta-band" style="border:none;padding:0;color:#fff;text-align:center">
      <div><h2 style="color:#fff;font-size:24px">Ready to move your cargo?</h2><p style="color:rgba(255,255,255,0.75);max-width:540px;margin:0 auto">Get a competitive quote for <?= esc($service['title']) ?> within one business day. Our team is standing by.</p></div>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:24px">
        <a href="/contact" class="btn-gold-lg">Request a Quote</a>
        <a href="/track" class="btn-outline-white" style="border-color:rgba(255,255,255,0.3);color:#fff">Track a Shipment</a>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/partials/foot.php'; ?>
