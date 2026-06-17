<?php /** @var string|null $error */ ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In | Galilea Admin</title>
<link rel="icon" href="/assets/img/logo.jpeg">
<link rel="preload" as="font" type="font/woff2" href="/assets/fonts/montserrat-var.woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<div class="ls" id="screen-login">
  <div class="ls-bg" id="ls-bg"></div>
  <div class="ls-inner">
    <div class="ls-visual">
      <div class="ls-visual-bg"></div>
      <div class="ls-visual-overlay"></div>
      <div class="ls-visual-content">
        <div class="ls-brand">
          <div class="ls-brand-logo"><img src="/assets/img/logo.jpeg" alt="Galilea"></div>
          <div class="ls-brand-name">Galilea Global Logistics</div>
          <div class="ls-brand-sub">Your supply chain, streamlined</div>
        </div>
        <div class="ls-slider" id="lsSlider">
          <div class="ls-slide active">
            <div class="ls-slide-icon">
              <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="32" cy="32" r="28"/><path d="M32 4a37 37 0 010 56M32 4a37 37 0 000 56"/><path d="M4 32h56M16 16c6 8 14 14 16 16-2 2-10 8-16 16M48 16c-6 8-14 14-16 16 2 2 10 8 16 16"/></svg>
            </div>
            <div class="ls-slide-title">Global Logistics Network</div>
            <div class="ls-slide-text">Sea, air, and land freight across 130+ countries with 48+ trusted port partners worldwide.</div>
          </div>
          <div class="ls-slide">
            <div class="ls-slide-icon">
              <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="32" cy="32" r="28"/><path d="M20 44l12-24 12 24"/><path d="M14 44h36"/><path d="M24 36h16"/></svg>
            </div>
            <div class="ls-slide-title">China–Africa Trade Corridor</div>
            <div class="ls-slide-text">Physical offices in Kigali, Guangzhou, and Yiwu — our team on the ground at both ends.</div>
          </div>
          <div class="ls-slide">
            <div class="ls-slide-icon">
              <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="32" cy="32" r="28"/><circle cx="32" cy="32" r="8"/><circle cx="32" cy="32" r="18"/><path d="M32 4v8M32 52v8M4 32h8M52 32h8"/></svg>
            </div>
            <div class="ls-slide-title">Real-Time Cargo Visibility</div>
            <div class="ls-slide-text">Live tracking, milestone alerts, and proactive updates at every stage of your shipment.</div>
          </div>
        </div>
        <div class="ls-slider-dots" id="lsDots">
          <button class="ls-dot active" data-i="0" aria-label="Slide 1"></button>
          <button class="ls-dot" data-i="1" aria-label="Slide 2"></button>
          <button class="ls-dot" data-i="2" aria-label="Slide 3"></button>
        </div>
        <div class="ls-stats">
          <div class="ls-stat"><span class="ls-stat-num" data-count="130">0</span><span class="ls-stat-plus">+</span><span class="ls-stat-label">Countries</span></div>
          <div class="ls-stat"><span class="ls-stat-num" data-count="48">0</span><span class="ls-stat-plus">+</span><span class="ls-stat-label">Port Partners</span></div>
          <div class="ls-stat"><span class="ls-stat-num" data-count="99">0</span><span class="ls-stat-pct">%</span><span class="ls-stat-label">On-Time Rate</span></div>
        </div>
      </div>
      <div class="ls-ornament ls-orn-1"></div>
      <div class="ls-ornament ls-orn-2"></div>
    </div>
    <div class="ls-form-side">
      <div class="ls-card">
        <div class="ls-card-inner">
          <div class="ls-card-title" style="margin-bottom:36px">Administration Portal</div>
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
          <div class="ls-hint">Protected area &middot; authorised personnel only</div>
          <?php endif; ?>
          <a href="/" class="ls-back" style="text-decoration:none">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to website
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
(function(){
  // floating particles
  var bg=document.getElementById('ls-bg');if(bg){for(var i=0;i<18;i++){var p=document.createElement('div');p.className='ls-p';var s=4+Math.random()*20;p.style.width=p.style.height=s+'px';p.style.left=(Math.random()*100)+'%';p.style.animationDuration=(10+Math.random()*12)+'s';p.style.animationDelay=(Math.random()*10)+'s';bg.appendChild(p);}}
  // slider
  var slides=document.querySelectorAll('.ls-slide'),dots=document.querySelectorAll('.ls-dot'),cur=0,timer;
  function goTo(i){slides.forEach(function(s){s.classList.remove('active')});dots.forEach(function(d){d.classList.remove('active')});slides[i].classList.add('active');dots[i].classList.add('active');cur=i;}
  dots.forEach(function(d){d.addEventListener('click',function(){clearInterval(timer);goTo(parseInt(this.getAttribute('data-i')));startTimer();});});
  function startTimer(){timer=setInterval(function(){goTo((cur+1)%slides.length)},4800);}
  if(slides.length)startTimer();
  // stat counter animation
  var stats=document.querySelectorAll('.ls-stat-num');
  function animateStats(){stats.forEach(function(s){var target=parseInt(s.getAttribute('data-count')),current=0,step=Math.ceil(target/40);var iv=setInterval(function(){current+=step;if(current>=target){current=target;clearInterval(iv);}s.textContent=current;},30);});}
  var observer=new IntersectionObserver(function(entries){entries.forEach(function(e){if(e.isIntersecting){animateStats();observer.disconnect();}});});
  if(stats.length)observer.observe(stats[0].closest('.ls-stats'));
})();
</script>
</body>
</html>
