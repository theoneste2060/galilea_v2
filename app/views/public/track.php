<?php
$st = site_settings();
$meta = ['title' => 'Track & Trace | Galilea Global Logistics', 'description' => 'Track your Galilea shipment live by container, booking reference or bill of lading number.',
    'breadcrumbs' => [['name' => 'Home', 'url' => '/'], ['name' => 'Track & Trace', 'url' => '/track']]];
require __DIR__ . '/partials/head.php';
?>
<header class="page-hero" style="background:linear-gradient(135deg, #0D2645 0%, #1a3a5c 100%)">
  <div class="container">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><span aria-current="page">Track &amp; Trace</span></nav>
    <p class="section-label" style="color:#C9A84C">Track &amp; Trace</p>
    <h1 class="page-hero-title" style="color:#fff">Follow your shipment live</h1>
    <p class="page-hero-sub" style="color:rgba(255,255,255,0.75)">Enter your container number, booking reference or bill of lading number to see real-time status and every milestone along the way.</p>
  </div>
</header>

<section class="section-pad">
  <div class="container" style="max-width:700px">
    <div style="text-align:center;margin-bottom:32px">
      <h2 class="section-title" style="font-size:22px">Where is your cargo right now?</h2>
      <p class="section-body" style="max-width:520px;margin:0 auto">Every Galilea shipment is assigned a unique reference that lets you track progress from booking to final delivery. Enter your reference below to see the latest status, milestone history and estimated arrival times.</p>
    </div>
    <form class="tracking-input-group" id="trackForm" role="search" style="margin-bottom:8px">
      <input type="text" id="trackInput" placeholder="e.g. GALU1234567" autocomplete="off" aria-label="Shipment reference">
      <button class="track-submit" id="trackBtn" type="submit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>Track</button>
    </form>
    <p style="font-size:12.5px;color:#8a95a7;text-align:center">Need help? Call <a href="<?= esc(tel_href($st['phone_rw'] ?? '')) ?>" style="color:#0D2645;font-weight:600"><?= esc($st['phone_rw'] ?? '') ?></a>.</p>
    <div class="tracking-result" id="trackResult" aria-live="polite" style="margin-top:24px"></div>
  </div>
</section>

<section class="section-pad" style="padding-top:0">
  <div class="container">
    <div class="g3 reveal">
      <div class="mini-card" style="text-align:center;padding:28px 20px">
        <div class="mini-body">
          <h3>Real-time visibility</h3>
          <p>Our tracking system updates every 15 minutes, showing you exactly where your shipment is and what stage it has reached — from booking confirmation to final delivery at your door.</p>
        </div>
      </div>
      <div class="mini-card" style="text-align:center;padding:28px 20px">
        <div class="mini-body">
          <h3>Milestone history</h3>
          <p>Every event in your shipment's journey is recorded: departure, arrival at transit hubs, customs clearance, border crossings and last-mile dispatch. You can review the complete timeline at any time.</p>
        </div>
      </div>
      <div class="mini-card" style="text-align:center;padding:28px 20px">
        <div class="mini-body">
          <h3>Proactive alerts</h3>
          <p>Sign up for email or SMS notifications and receive automatic updates when your shipment reaches key milestones — so you always know what is happening without having to check.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section-pad" style="background:#f8fafb">
  <div class="container">
    <div class="reveal" style="max-width:700px;margin:0 auto;text-align:center">
      <p class="section-label">What to track</p>
      <h2 class="section-title" style="font-size:22px">One reference, full visibility</h2>
      <p style="font-size:15px;line-height:1.7;color:#5A6478">You can use any of the following identifiers to look up your shipment:</p>
      <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin-top:16px">
        <span style="background:#fff;border:1px solid var(--line,#e0e5ee);border-radius:6px;padding:8px 16px;font-size:13px;font-weight:600">Container number</span>
        <span style="background:#fff;border:1px solid var(--line,#e0e5ee);border-radius:6px;padding:8px 16px;font-size:13px;font-weight:600">Booking reference</span>
        <span style="background:#fff;border:1px solid var(--line,#e0e5ee);border-radius:6px;padding:8px 16px;font-size:13px;font-weight:600">Bill of lading</span>
        <span style="background:#fff;border:1px solid var(--line,#e0e5ee);border-radius:6px;padding:8px 16px;font-size:13px;font-weight:600">Air waybill number</span>
        <span style="background:#fff;border:1px solid var(--line,#e0e5ee);border-radius:6px;padding:8px 16px;font-size:13px;font-weight:600">House waybill</span>
      </div>
      <p style="font-size:14px;color:#8a95a7;margin-top:24px">Not sure which reference to use? Contact our operations team and we will help you locate your shipment.</p>
    </div>
  </div>
</section>
<?php require __DIR__ . '/partials/foot.php'; ?>
