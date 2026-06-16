<?php
$st = site_settings();
$meta = ['title' => 'Track & Trace — Galilea Global Logistics', 'description' => 'Track your Galilea shipment live by container, booking reference or bill of lading number.',
    'breadcrumbs' => [['name' => 'Home', 'url' => '/'], ['name' => 'Track & Trace', 'url' => '/track']]];
require __DIR__ . '/partials/head.php';
?>
<header class="page-hero">
  <div class="container">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><span aria-current="page">Track &amp; Trace</span></nav>
    <p class="section-label" style="color:#C9A84C">Track &amp; Trace</p>
    <h1 class="page-hero-title">Follow your shipment live</h1>
    <p class="page-hero-sub">Enter your container, booking, or bill of lading number to see real-time status and milestones.</p>
  </div>
</header>

<section class="section-pad">
  <div class="container" style="max-width:680px">
    <form class="tracking-input-group" id="trackForm" role="search" style="margin-bottom:8px">
      <input type="text" id="trackInput" placeholder="e.g. GALU1234567" autocomplete="off" aria-label="Shipment reference">
      <button class="track-submit" id="trackBtn" type="submit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>Track</button>
    </form>
    <p style="font-size:12.5px;color:#8a95a7">Need help? Call <a href="<?= esc(tel_href($st['phone_rw'] ?? '')) ?>" style="color:#0D2645;font-weight:600"><?= esc($st['phone_rw'] ?? '') ?></a>. Try the demo reference <strong>GALU1234567</strong>.</p>
    <div class="tracking-result" id="trackResult" aria-live="polite" style="margin-top:18px"></div>
  </div>
</section>
<?php require __DIR__ . '/partials/foot.php'; ?>
