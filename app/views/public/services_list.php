<?php
/** @var array $services */
$svcIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>';
$meta = ['title' => 'Services | Galilea Global Logistics', 'description' => 'Sea & air freight, road transport, warehousing, customs clearance and China sourcing — full-service logistics from Galilea.',
    'breadcrumbs' => [['name' => 'Home', 'url' => '/'], ['name' => 'Services', 'url' => '/services']]];
require __DIR__ . '/partials/head.php';
?>
<header class="page-hero" style="background:linear-gradient(135deg, #0D2645 0%, #1a3a5c 100%);position:relative;overflow:hidden">
  <div class="container" style="position:relative;z-index:1">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><span aria-current="page">Services</span></nav>
    <p class="section-label" style="color:#C9A84C">What We Do</p>
    <h1 class="page-hero-title" style="color:#fff">Complete multimodal logistics solutions</h1>
    <p class="page-hero-sub" style="color:rgba(255,255,255,0.75);max-width:640px">From sea freight across the Indian Ocean to road transport through the heart of East Africa — Galilea moves your cargo with precision, transparency, and a single point of contact every step of the way.</p>
  </div>
</header>

<!-- ── INTRO ── -->
<section class="section-pad" style="padding-top:60px;padding-bottom:40px">
  <div class="container">
    <div class="reveal" style="max-width:800px;margin:0 auto;text-align:center">
      <p class="section-label">End-to-End Capability</p>
      <h2 class="section-title" style="font-size:28px">One partner across every link in your supply chain</h2>
      <p class="section-body" style="font-size:16px;line-height:1.7">Whether you are importing a full container of consumer goods from Guangzhou, rushing a time-sensitive spare part from Europe, or moving heavy machinery from Mombasa to Kigali, Galilea has the infrastructure, the relationships and the boots on the ground to deliver. We manage eight complementary service lines — so you never need to juggle multiple vendors, chase updates across time zones, or worry about who is responsible when something goes wrong.</p>
    </div>
  </div>
</section>

<!-- ── SERVICES GRID ── -->
<section class="section-pad" style="padding-top:0">
  <div class="container">
    <div class="services-grid reveal">
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
  </div>
</section>

<!-- ── WHY CHOOSE US ── -->
<section class="section-pad" style="background:#f8fafb">
  <div class="container">
    <div class="reveal" style="max-width:800px;margin:0 auto 40px;text-align:center">
      <p class="section-label">The Galilea Advantage</p>
      <h2 class="section-title">What sets our service apart</h2>
    </div>
    <div class="g3 reveal">
      <div class="mini-card" style="background:#fff">
        <div class="mini-body">
          <h3>China + East Africa presence</h3>
          <p>Offices in Kigali, Guangzhou and Yiwu mean we manage your cargo at both ends — no hand-offs, no language barriers, no gaps in communication.</p>
        </div>
      </div>
      <div class="mini-card" style="background:#fff">
        <div class="mini-body">
          <h3>In-house customs team</h3>
          <p>Licensed clearing agents handle every declaration, duty calculation and permit. Your cargo clears faster and you never pay a penalty for misclassification.</p>
        </div>
      </div>
      <div class="mini-card" style="background:#fff">
        <div class="mini-body">
          <h3>End-to-end visibility</h3>
          <p>Track your shipment from booking to delivery through our tracking portal. Real-time status, milestone alerts, and a single point of contact who knows your cargo.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── HOW IT WORKS ── -->
<section class="section-pad">
  <div class="container">
    <div class="reveal" style="max-width:800px;margin:0 auto 40px;text-align:center">
      <p class="section-label">Simple Process</p>
      <h2 class="section-title">How it works</h2>
    </div>
    <div class="g3 reveal" style="counter-reset:step">
      <div class="mini-card" style="text-align:center;padding:32px 20px;counter-increment:step">
        <div style="width:48px;height:48px;border-radius:50%;background:#0D2645;color:#C9A84C;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;margin:0 auto 16px">1</div>
        <div class="mini-body">
          <h3>Tell us what you need</h3>
          <p>Share your cargo details, origin and destination through our quote form or a quick call. We will ask the right questions to give you an accurate rate and transit time.</p>
        </div>
      </div>
      <div class="mini-card" style="text-align:center;padding:32px 20px;counter-increment:step">
        <div style="width:48px;height:48px;border-radius:50%;background:#0D2645;color:#C9A84C;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;margin:0 auto 16px">2</div>
        <div class="mini-body">
          <h3>We handle the logistics</h3>
          <p>Our team books space, prepares documentation, arranges inland haulage and clears customs. You receive a single booking confirmation and a tracking reference.</p>
        </div>
      </div>
      <div class="mini-card" style="text-align:center;padding:32px 20px;counter-increment:step">
        <div style="width:48px;height:48px;border-radius:50%;background:#0D2645;color:#C9A84C;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;margin:0 auto 16px">3</div>
        <div class="mini-body">
          <h3>Your cargo arrives on time</h3>
          <p>Track every milestone live. From departure to final delivery, you stay informed and in control — with a single invoice and a dedicated operations contact.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA ── -->
<section class="section-pad" style="background:linear-gradient(135deg, #0D2645 0%, #1a3a5c 100%)">
  <div class="container">
    <div class="cta-band" style="border:none;padding:0;color:#fff;text-align:center">
      <div><h2 style="color:#fff">Not sure which service fits?</h2><p style="color:rgba(255,255,255,0.75);max-width:540px;margin:0 auto">Talk to our team and get a tailored quote within one business day. We will help you choose the most cost-effective and reliable route.</p></div>
      <a href="/contact" class="btn-gold-lg" style="margin-top:20px">Request a Quote</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/partials/foot.php'; ?>
