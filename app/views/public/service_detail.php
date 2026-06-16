<?php
/** @var array $service @var array $more */
$meta = ['title' => esc($service['title']) . ' — Galilea Global Logistics', 'description' => $service['short_description']];
require __DIR__ . '/partials/head.php';
?>
<header class="page-hero">
  <div class="container">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><a href="/services">Services</a><span>/</span><span aria-current="page"><?= esc($service['title']) ?></span></nav>
    <h1 class="page-hero-title"><?= esc($service['title']) ?></h1>
    <p class="page-hero-sub"><?= esc($service['short_description']) ?></p>
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
      </aside>
    </div>

    <?php if ($more): ?>
    <div class="related-block">
      <h2 class="section-title" style="font-size:22px;margin-bottom:18px">Other services</h2>
      <div class="g3">
        <?php foreach ($more as $m): ?>
        <a class="mini-card" href="/services/<?= esc($m['slug']) ?>">
          <?php if (!empty($m['image_path'])): ?><img src="<?= esc($m['image_path']) ?>" alt="" loading="lazy"><?php endif; ?>
          <div class="mini-body"><h3><?= esc($m['title']) ?></h3><p><?= esc($m['short_description']) ?></p></div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/partials/foot.php'; ?>
