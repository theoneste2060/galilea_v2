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
          <?php if (($st['social_facebook_enabled'] ?? '') === '1' && !empty($st['social_facebook_url'])): ?><a href="<?= $g('social_facebook_url') ?>" class="social-btn" aria-label="Facebook" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg></a><?php endif; ?>
          <?php if (($st['social_instagram_enabled'] ?? '') === '1' && !empty($st['social_instagram_url'])): ?><a href="<?= $g('social_instagram_url') ?>" class="social-btn" aria-label="Instagram" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a><?php endif; ?>
          <?php if (($st['social_linkedin_enabled'] ?? '') === '1' && !empty($st['social_linkedin_url'])): ?><a href="<?= $g('social_linkedin_url') ?>" class="social-btn" aria-label="LinkedIn" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></a><?php endif; ?>
          <?php if (($st['social_youtube_enabled'] ?? '') === '1' && !empty($st['social_youtube_url'])): ?><a href="<?= $g('social_youtube_url') ?>" class="social-btn" aria-label="YouTube" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46A2.78 2.78 0 001.46 6.42 29 29 0 001 12a29 29 0 00.46 5.58 2.78 2.78 0 001.95 1.96C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg></a><?php endif; ?>
          <?php if (($st['social_twitter_enabled'] ?? '') === '1' && !empty($st['social_twitter_url'])): ?><a href="<?= $g('social_twitter_url') ?>" class="social-btn" aria-label="X / Twitter" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a><?php endif; ?>
          <?php if (($st['social_tiktok_enabled'] ?? '') === '1' && !empty($st['social_tiktok_url'])): ?><a href="<?= $g('social_tiktok_url') ?>" class="social-btn" aria-label="TikTok" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg></a><?php endif; ?>
          <?php if (($st['social_whatsapp_enabled'] ?? '') === '1' && !empty($st['social_whatsapp_url'])): ?><a href="<?= $g('social_whatsapp_url') ?>" class="social-btn" aria-label="WhatsApp" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17.5 14.4c-.3-.15-1.77-.87-2.04-.97-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51l-.57-.01c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.88 1.22 3.08.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.62.71.23 1.36.2 1.87.12.57-.08 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.12-.27-.2-.57-.35z"/><path d="M12.05 21.5h-.01a9.4 9.4 0 01-4.79-1.31l-.34-.2-3.56.93.95-3.47-.22-.36a9.38 9.38 0 01-1.44-5 9.43 9.43 0 019.43-9.43c2.52 0 4.88.98 6.66 2.76a9.37 9.37 0 012.76 6.67c0 5.2-4.23 9.42-9.6 9.42zM20.52 3.45A11.32 11.32 0 0012.05.94C5.8.94.73 6.01.73 12.26c0 1.99.52 3.94 1.51 5.66L.64 23.06l5.28-1.38a11.3 11.3 0 005.4 1.38h.01c6.25 0 11.32-5.07 11.33-11.32a11.26 11.26 0 00-3.32-8.03z"/></svg></a><?php endif; ?>
        </div>
      </div>
      <div class="footer-col"><h5>Services</h5><ul><?php foreach ($footServices as $svc): ?><li><a href="/services/<?= esc($svc['slug']) ?>"><?= esc($svc['title']) ?></a></li><?php endforeach; ?></ul></div>
      <div class="footer-col"><h5>Company</h5><ul><li><a href="/">Home</a></li><li><a href="/about">About Galilea</a></li><li><a href="/insights">Insights &amp; News</a></li><li><a href="/careers">Careers</a></li><li><a href="/contact">Contact</a></li><li><a href="/track">Track &amp; Trace</a></li></ul></div>
      <div class="footer-col"><h5>Legal</h5><ul><li><a href="/privacy">Privacy Policy</a></li><li><a href="/terms">Terms of Service</a></li><li><a href="/cookies">Cookie Policy</a></li><li><a href="/admin.php">Admin Sign In</a></li></ul></div>
    </div>
    <div class="footer-bottom">
      <p>© <?= date('Y') ?> <?= esc($st['org_legal_name'] ?? 'Galilea Global Logistics LTD') ?> · Kigali, Rwanda<?= !empty($st['company_reg']) ? ' · ' . esc($st['company_reg']) : '' ?></p>
      <div class="footer-bottom-links"><a href="/privacy">Privacy</a><a href="/terms">Terms</a><a href="/cookies">Cookies</a><a href="/contact">Contact</a></div>
    </div>
  </div>
</footer>

<button id="btt" aria-label="Back to top"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 15l7-7 7 7"/></svg></button>

<?php $wa = preg_replace('/\D/', '', $st['whatsapp_number'] ?? ''); if ($wa): ?>
<a href="https://wa.me/<?= esc($wa) ?>?text=<?= rawurlencode('Hello Galilea, I would like a logistics quote.') ?>" class="wa-float" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
  <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.5 14.4c-.3-.15-1.77-.87-2.04-.97-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51l-.57-.01c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.88 1.22 3.08.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.62.71.23 1.36.2 1.87.12.57-.08 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.12-.27-.2-.57-.35zM12.05 21.5h-.01a9.4 9.4 0 01-4.79-1.31l-.34-.2-3.56.93.95-3.47-.22-.36a9.38 9.38 0 01-1.44-5 9.43 9.43 0 019.43-9.43c2.52 0 4.88.98 6.66 2.76a9.37 9.37 0 012.76 6.67c0 5.2-4.23 9.42-9.6 9.42zM20.52 3.45A11.32 11.32 0 0012.05.94C5.8.94.73 6.01.73 12.26c0 1.99.52 3.94 1.51 5.66L.64 23.06l5.28-1.38a11.3 11.3 0 005.4 1.38h.01c6.25 0 11.32-5.07 11.33-11.32a11.26 11.26 0 00-3.32-8.03z"/></svg>
</a>
<?php endif; ?>

<!-- Search overlay -->
<div class="search-overlay" id="searchOverlay" role="dialog" aria-modal="true" aria-label="Search the site">
  <div class="search-box">
    <form action="/search" method="get" role="search">
      <input type="search" name="q" id="searchInput" placeholder="Search services, insights…" aria-label="Search" autocomplete="off">
      <button type="submit" class="s-go">Search</button>
    </form>
    <p class="search-hint">Press <kbd>Esc</kbd> to close · try “air freight”, “customs”, “China”</p>
  </div>
</div>

<!-- Mobile sticky action bar -->
<div class="mobile-cta" aria-label="Quick actions">
  <a href="/track" class="mc-btn ghost"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>Track</a>
  <a href="<?= esc(tel_href($st['phone_rw'] ?? '')) ?>" class="mc-btn ghost"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.22 1.18 2 2 0 012.18 0h3a2 2 0 012 1.72c.13.96.36 1.9.7 2.81a2 2 0 01-.45 2.11L6.9 7.1a16 16 0 006 6l.56-.56a2 2 0 012.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0122 16.92z"/></svg>Call</a>
  <a href="/contact" class="mc-btn primary">Get a Quote</a>
</div>

<script src="<?= esc(asset_url('/assets/js/site.js')) ?>" defer></script>
</body>
</html>
