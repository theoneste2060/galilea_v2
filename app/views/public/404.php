<?php
$meta = ['title' => 'Page not found — Galilea Global Logistics', 'description' => 'The page you are looking for could not be found.'];
require __DIR__ . '/partials/head.php';
?>
<header class="page-hero">
  <div class="container">
    <p class="section-label" style="color:#C9A84C">Error 404</p>
    <h1 class="page-hero-title">We couldn't find that page</h1>
    <p class="page-hero-sub">The link may be broken or the page may have moved. Let's get you back on track.</p>
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px">
      <a href="/" class="btn-gold-lg">Back to home</a>
      <a href="/track" class="btn-outline-navy">Track a shipment</a>
    </div>
  </div>
</header>
<?php require __DIR__ . '/partials/foot.php'; ?>
