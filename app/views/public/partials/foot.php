<?php
$st = site_settings();
$g = fn(string $k, string $d = '') => esc($st[$k] ?? $d);
$footServices = Database::all('SELECT title, slug FROM services WHERE is_active = 1 ORDER BY sort_order, title LIMIT 6');
?>
</main><!-- /#main -->

<!-- ── CONTACT STRIP ── -->
<div style="background:#0D2645;padding:40px 0;border-top:3px solid #C9A84C;">
  <div class="container">
    <div class="contact-strip">
      <div><div class="cs-label">Email Us</div><a href="mailto:<?= $g('site_email') ?>" class="cs-a"><?= $g('site_email') ?></a><a href="mailto:<?= $g('support_email') ?>" class="cs-a dim"><?= $g('support_email') ?></a></div>
      <div><div class="cs-label">Rwanda Office</div><a href="<?= esc(tel_href($st['phone_rw'] ?? '')) ?>" class="cs-a"><?= $g('phone_rw') ?></a><a href="<?= esc(tel_href($st['phone_rw_alt'] ?? '')) ?>" class="cs-a dim"><?= $g('phone_rw_alt') ?></a></div>
      <div><div class="cs-label">China Office</div><a href="<?= esc(tel_href($st['phone_cn'] ?? '')) ?>" class="cs-a"><?= $g('phone_cn') ?></a><span class="cs-a dim">Guangzhou &amp; Yiwu</span></div>
      <div><div class="cs-label">Kigali Address</div><span class="cs-a"><?= $g('address_kigali') ?></span><span class="cs-a dim">Nyarugenge, Kigali 🇷🇼</span></div>
    </div>
  </div>
</div>

<footer>
  <div class="container">
    <div class="footer-top">
      <div class="footer-brand">
        <a href="/" class="footer-logo">
          <img src="/assets/img/logo.jpeg" width="40" height="40" style="width:40px;height:40px;border-radius:50%;object-fit:cover;flex-shrink:0;" alt="Galilea Global Logistics">
          <div><span class="logo-text">Galilea Global</span><span class="logo-sub">Logistics Ltd.</span></div>
        </a>
        <p class="footer-tagline">Trusted Trade. Global Reach. Connecting East Africa and the world through seamless, technology-driven supply chain solutions.</p>
        <div class="footer-socials">
          <a href="#" class="social-btn" aria-label="LinkedIn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></a>
          <a href="#" class="social-btn" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg></a>
          <a href="#" class="social-btn" aria-label="YouTube"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46A2.78 2.78 0 001.46 6.42 29 29 0 001 12a29 29 0 00.46 5.58 2.78 2.78 0 001.95 1.96C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg></a>
        </div>
      </div>
      <div class="footer-col"><h5>Services</h5><ul><?php foreach ($footServices as $svc): ?><li><a href="/services/<?= esc($svc['slug']) ?>"><?= esc($svc['title']) ?></a></li><?php endforeach; ?></ul></div>
      <div class="footer-col"><h5>Company</h5><ul><li><a href="/about">About Galilea</a></li><li><a href="/insights">Insights &amp; News</a></li><li><a href="/careers">Careers</a></li><li><a href="/contact">Contact</a></li><li><a href="/track">Track &amp; Trace</a></li></ul></div>
      <div class="footer-col"><h5>Legal</h5><ul><li><a href="/privacy">Privacy Policy</a></li><li><a href="/terms">Terms of Service</a></li><li><a href="/cookies">Cookie Policy</a></li><li><a href="/admin.php">Admin Sign In</a></li></ul></div>
    </div>
    <div class="footer-bottom">
      <p>© <?= date('Y') ?> Galilea Global Logistics Ltd. · Kigali, Rwanda</p>
      <div class="footer-bottom-links"><a href="/privacy">Privacy</a><a href="/terms">Terms</a><a href="/cookies">Cookies</a><a href="/contact">Contact</a></div>
    </div>
  </div>
</footer>

<button id="btt" aria-label="Back to top"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 15l7-7 7 7"/></svg></button>

<script src="/assets/js/site.js" defer></script>
</body>
</html>
