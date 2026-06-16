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
        <form id="inquiryForm" class="inquiry-form quote-wizard" autocomplete="on" novalidate>
          <?= csrf_field() ?>
          <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">

          <ol class="wiz-progress" aria-hidden="true">
            <li class="wiz-ind active"><span class="wiz-num">1</span>Shipment</li>
            <li class="wiz-ind"><span class="wiz-num">2</span>Details</li>
            <li class="wiz-ind"><span class="wiz-num">3</span>Contact</li>
          </ol>

          <!-- Step 1 — service & route -->
          <fieldset class="wiz-step" data-step="0">
            <div class="field is-select">
              <select id="c_service" name="service_interest">
                <option value="General Inquiry">General inquiry</option>
                <?php foreach ($services as $svc): ?><option value="<?= esc($svc['title']) ?>"><?= esc($svc['title']) ?></option><?php endforeach; ?>
              </select>
              <label for="c_service">Service of interest</label>
            </div>
            <div class="field-row">
              <div class="field"><input type="text" id="c_origin" name="_origin" placeholder=" " required><label for="c_origin">Origin (city / country) *</label></div>
              <div class="field"><input type="text" id="c_dest" name="_destination" placeholder=" " required><label for="c_dest">Destination *</label></div>
            </div>
            <div class="field-row">
              <div class="field"><input type="text" id="c_cargo" name="_cargo" placeholder=" "><label for="c_cargo">Cargo type (optional)</label></div>
              <div class="field"><input type="text" id="c_weight" name="_weight" placeholder=" "><label for="c_weight">Approx. weight / volume (optional)</label></div>
            </div>
          </fieldset>

          <!-- Step 2 — message -->
          <fieldset class="wiz-step" data-step="1">
            <div class="field"><textarea id="c_msg" name="message" rows="6" placeholder=" " required></textarea><label for="c_msg">Tell us about your shipment or request *</label></div>
          </fieldset>

          <!-- Step 3 — contact -->
          <fieldset class="wiz-step" data-step="2">
            <div class="field-row">
              <div class="field"><input type="text" id="c_name" name="full_name" placeholder=" " required><label for="c_name">Full name *</label></div>
              <div class="field"><input type="email" id="c_email" name="email" placeholder=" " required><label for="c_email">Email address *</label></div>
            </div>
            <div class="field-row">
              <div class="field"><input type="text" id="c_phone" name="phone" placeholder=" "><label for="c_phone">Phone (optional)</label></div>
              <div class="field"><input type="text" id="c_company" name="company" placeholder=" "><label for="c_company">Company (optional)</label></div>
            </div>
          </fieldset>

          <div class="wiz-nav">
            <button type="button" class="btn-ghost wiz-back" hidden>← Back</button>
            <button type="button" class="nl-submit wiz-next">Continue →</button>
            <button type="submit" class="nl-submit wiz-submit" hidden>Send Request</button>
          </div>
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
