<?php /** @var string|null $error */ ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — Galilea Admin</title>
<link rel="icon" href="/assets/img/logo.jpeg">
<link rel="preload" as="font" type="font/woff2" href="/assets/fonts/montserrat-var.woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<div class="ls" id="screen-login">
  <div class="ls-bg" id="ls-bg"></div>
  <div class="ls-card">
    <div class="ls-globe" style="overflow:hidden;padding:0">
      <img src="/assets/img/logo.jpeg" alt="Galilea" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
    </div>
    <div class="ls-bname">Galilea Global Logistics</div>
    <div class="ls-bsub">Administration Portal</div>
    <?php if ($error): ?><div class="ls-err show"><?= esc($error) ?></div><?php endif; ?>
    <?php if (($step ?? 'credentials') === '2fa'): ?>
    <div class="ls-title">Two-Factor Verification</div>
    <div class="ls-subtitle">Enter the 6-digit code from your authenticator app</div>
    <form method="post" action="/admin.php?p=login" autocomplete="one-time-code">
      <?= csrf_field() ?>
      <div class="ls-fields">
        <div class="ls-field">
          <input type="text" name="code" class="ls-input" placeholder="123456" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" required autofocus style="text-align:center;letter-spacing:.4em;font-size:18px">
          <span class="ls-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>
        </div>
      </div>
      <button class="ls-unlock" type="submit">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Verify &amp; Sign In
      </button>
    </form>
    <div class="ls-hint">Lost your device? Contact a super-admin to reset 2FA.</div>
    <?php else: ?>
    <form method="post" action="/admin.php?p=login" autocomplete="on">
      <?= csrf_field() ?>
      <div class="ls-fields">
        <div class="ls-field">
          <input type="text" name="username" class="ls-input" placeholder="Username" autocomplete="username" required autofocus value="<?= esc(input('username')) ?>">
          <span class="ls-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
        </div>
        <div class="ls-field">
          <input type="password" name="password" class="ls-input" placeholder="Password" autocomplete="current-password" required>
          <span class="ls-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></span>
        </div>
      </div>
      <button class="ls-unlock" type="submit">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 019.9-1"/></svg>
        Unlock Portal
      </button>
    </form>
    <div class="ls-hint">Protected area · authorised personnel only</div>
    <?php endif; ?>
    <a href="/" class="ls-back" style="text-decoration:none">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
      Back to website
    </a>
  </div>
</div>
<script>
// Subtle floating particles in the login background.
(function(){var bg=document.getElementById('ls-bg');if(!bg)return;for(var i=0;i<14;i++){var p=document.createElement('div');p.className='ls-p';var s=4+Math.random()*16;p.style.width=p.style.height=s+'px';p.style.left=(Math.random()*100)+'%';p.style.animationDuration=(8+Math.random()*10)+'s';p.style.animationDelay=(Math.random()*8)+'s';bg.appendChild(p);}})();
</script>
</body>
</html>
