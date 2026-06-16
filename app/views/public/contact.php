<?php
/** @var array $services */
$st = site_settings();
$g = fn(string $k, string $d = '') => esc($st[$k] ?? $d);
$meta = ['title' => 'Contact & Quote — Galilea Global Logistics', 'description' => 'Get in touch with Galilea Global Logistics in Kigali, Guangzhou and Yiwu. Request a freight quote today.',
    'breadcrumbs' => [['name' => 'Home', 'url' => '/'], ['name' => 'Contact', 'url' => '/contact']]];
require __DIR__ . '/partials/head.php';
?>
<header class="page-hero">
  <div class="container">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><span aria-current="page">Contact</span></nav>
    <p class="section-label" style="color:#C9A84C">Get In Touch</p>
    <h1 class="page-hero-title">Request a quote</h1>
    <p class="page-hero-sub">Tell us what you need to move — our team replies within one business day.</p>
  </div>
</header>

<section class="section-pad">
  <div class="container">
    <div class="detail-layout">
      <div class="card-soft">
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
          <textarea name="message" rows="5" placeholder="Tell us about your shipment or request *" required></textarea>
          <button type="submit" class="nl-submit" style="width:100%">Send Request</button>
          <p class="form-msg" id="inquiryMsg" role="status"></p>
        </form>
      </div>
      <aside class="detail-side">
        <div class="side-card">
          <h3>Rwanda — Head Office</h3>
          <p><?= $g('address_kigali') ?>, Nyarugenge 🇷🇼</p>
          <p><a href="<?= esc(tel_href($st['phone_rw'] ?? '')) ?>"><?= $g('phone_rw') ?></a><br><a href="<?= esc(tel_href($st['phone_rw_alt'] ?? '')) ?>"><?= $g('phone_rw_alt') ?></a></p>
          <p><a href="mailto:<?= $g('site_email') ?>"><?= $g('site_email') ?></a></p>
        </div>
        <div class="side-card">
          <h3>China — Guangzhou &amp; Yiwu</h3>
          <p><a href="<?= esc(tel_href($st['phone_cn'] ?? '')) ?>"><?= $g('phone_cn') ?></a></p>
          <p>Sourcing, consolidation &amp; freight</p>
        </div>
      </aside>
    </div>
  </div>
</section>
<?php require __DIR__ . '/partials/foot.php'; ?>
