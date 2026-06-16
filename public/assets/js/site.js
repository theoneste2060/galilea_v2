/* Galilea Global Logistics — public site behaviour */
(function () {
  'use strict';

  // Preloader: hide as soon as the window is ready (with a hard fallback).
  function hidePreloader() {
    var pl = document.getElementById('preloader');
    if (pl) pl.classList.add('hide');
  }
  window.addEventListener('load', hidePreloader);
  setTimeout(hidePreloader, 2500);

  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  // ── NAV SCROLL ──
  var nav = $('#mainNav');
  var btt = $('#btt');
  window.addEventListener('scroll', function () {
    if (nav) nav.classList.toggle('scrolled', window.scrollY > 60);
    if (btt) btt.classList.toggle('show', window.scrollY > 400);
  }, { passive: true });

  // ── MOBILE NAV ──
  var hamburger = $('#hamburgerBtn'), mobileNav = $('#mobileNav'), mnClose = $('#mnClose');
  if (hamburger && mobileNav) {
    hamburger.addEventListener('click', function () { mobileNav.classList.add('open'); document.body.style.overflow = 'hidden'; });
    if (mnClose) mnClose.addEventListener('click', function () { mobileNav.classList.remove('open'); document.body.style.overflow = ''; });
    $$('.mn-link', mobileNav).forEach(function (l) {
      l.addEventListener('click', function () { mobileNav.classList.remove('open'); document.body.style.overflow = ''; });
    });
  }

  // ── HERO SLIDER ──
  var heroSlides = $$('.hero-slide'), heroDots = $$('.hero-dot'), current = 0, sliderTimer;
  function goTo(i) {
    if (!heroSlides.length) return;
    heroSlides[current].classList.remove('active');
    if (heroDots[current]) heroDots[current].classList.remove('active');
    current = i;
    heroSlides[current].classList.add('active');
    if (heroDots[current]) heroDots[current].classList.add('active');
  }
  if (heroSlides.length > 1) {
    sliderTimer = setInterval(function () { goTo((current + 1) % heroSlides.length); }, 5500);
    heroDots.forEach(function (dot, i) {
      dot.addEventListener('click', function () { clearInterval(sliderTimer); goTo(i); sliderTimer = setInterval(function () { goTo((current + 1) % heroSlides.length); }, 5500); });
    });
  }

  // ── REVEAL ON SCROLL ──
  var reveals = $$('.reveal');
  if ('IntersectionObserver' in window) {
    var ro = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('visible'); ro.unobserve(e.target); } });
    }, { threshold: 0.12 });
    reveals.forEach(function (el) { ro.observe(el); });

    // ── COUNT UP ──
    var co = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        var el = e.target, target = parseInt(el.dataset.target, 10) || 0, start = Date.now(), dur = 1800;
        (function up() {
          var p = Math.min((Date.now() - start) / dur, 1), eased = 1 - Math.pow(1 - p, 3);
          el.textContent = Math.round(target * eased);
          if (p < 1) requestAnimationFrame(up); else el.textContent = target;
        })();
        co.unobserve(el);
      });
    }, { threshold: 0.3 });
    $$('.count').forEach(function (c) { co.observe(c); });
  } else {
    reveals.forEach(function (el) { el.classList.add('visible'); });
  }

  if (btt) btt.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });

  // ── TESTIMONIALS SLIDER ──
  (function () {
    var track = $('#tsliderTrack');
    if (!track) return;
    var slides = $$('.tslide', track), dots = $$('.tslider-dot'), idx = 0, total = slides.length, timer;
    if (total <= 1) return;
    function to(i) { idx = (i + total) % total; track.style.transform = 'translateX(-' + (idx * 100) + '%)'; dots.forEach(function (d, k) { d.classList.toggle('active', k === idx); }); }
    function auto() { timer = setInterval(function () { to(idx + 1); }, 6000); }
    function reset() { clearInterval(timer); auto(); }
    var n = $('#tNext'), p = $('#tPrev');
    if (n) n.addEventListener('click', function () { to(idx + 1); reset(); });
    if (p) p.addEventListener('click', function () { to(idx - 1); reset(); });
    dots.forEach(function (d, i) { d.addEventListener('click', function () { to(i); reset(); }); });
    auto();
  })();

  // ── SMOOTH ANCHOR SCROLL ──
  $$('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var href = a.getAttribute('href');
      if (href === '#') return;
      var t = document.querySelector(href);
      if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth' }); }
    });
  });

  // ── SHIPMENT TRACKING ──
  var trackBtn = $('#trackBtn'), trackInput = $('#trackInput'), trackResult = $('#trackResult');
  function esc(v) { var d = document.createElement('div'); d.textContent = v == null ? '' : String(v); return d.innerHTML; }
  function doTrack() {
    var ref = (trackInput.value || '').trim();
    if (ref.length < 3) { renderTrackError('Please enter a valid reference number.'); return; }
    trackBtn.disabled = true;
    fetch('/index.php?action=track&ref=' + encodeURIComponent(ref))
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d.ok) renderTrack(d.shipment); else renderTrackError(d.error || 'Shipment not found.');
      })
      .catch(function () { renderTrackError('Something went wrong. Please try again.'); })
      .finally(function () { trackBtn.disabled = false; });
  }
  function renderTrackError(msg) {
    trackResult.innerHTML = '<div class="tr-card"><div class="tr-meta" style="border:none"><div><div class="val" style="color:#b91c1c">' + esc(msg) + '</div></div></div></div>';
    trackResult.classList.add('show');
  }
  function renderTrack(s) {
    var steps = (s.stages || []).map(function (st) {
      return '<div class="tr-step ' + (st.completed ? 'done' : '') + '"><div class="marker"><div class="dot"></div><div class="line"></div></div>' +
        '<div class="txt"><strong>' + esc(st.label) + '</strong><span>' + esc(st.timestamp || '') + '</span></div></div>';
    }).join('');
    trackResult.innerHTML =
      '<div class="tr-card">' +
        '<div class="tr-top"><span class="tr-ref">' + esc(s.reference_number) + '</span>' +
          '<span class="tr-pill"><span style="width:7px;height:7px;border-radius:50%;background:#4ade80;display:inline-block"></span>' + esc(s.status) + '</span></div>' +
        '<div class="tr-meta">' +
          '<div><div class="lbl">Origin</div><div class="val">' + esc(s.origin) + '</div></div>' +
          '<div><div class="lbl">Destination</div><div class="val">' + esc(s.destination) + '</div></div>' +
          '<div><div class="lbl">Current Stage</div><div class="val">' + esc(s.current_stage) + '</div></div>' +
        '</div>' +
        '<div class="tr-steps">' + steps + '</div>' +
      '</div>';
    trackResult.classList.add('show');
    trackResult.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
  if (trackBtn) trackBtn.addEventListener('click', doTrack);
  if (trackInput) trackInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') doTrack(); });

  // ── AJAX FORM HELPER ──
  function ajaxForm(formId, msgId, action, resetOnSuccess) {
    var form = document.getElementById(formId);
    if (!form) return;
    var msg = document.getElementById(msgId);
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      msg.textContent = ''; msg.className = 'form-msg';
      var btn = form.querySelector('button[type="submit"], button:not([type])');
      if (btn) btn.disabled = true;
      var fd = new FormData(form);
      fetch('/index.php?action=' + action, { method: 'POST', body: fd })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
        .then(function (res) {
          if (res.j.ok) {
            msg.textContent = res.j.message || 'Thank you!'; msg.className = 'form-msg ok';
            if (resetOnSuccess) form.reset();
          } else {
            msg.textContent = res.j.error || 'Please check your details and try again.'; msg.className = 'form-msg err';
          }
        })
        .catch(function () { msg.textContent = 'Network error. Please try again.'; msg.className = 'form-msg err'; })
        .finally(function () { if (btn) btn.disabled = false; });
    });
  }
  ajaxForm('inquiryForm', 'inquiryMsg', 'inquiry', true);
  ajaxForm('newsletterForm', 'newsletterMsg', 'newsletter', true);
})();
