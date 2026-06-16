/* Galilea Global Logistics — public site behaviour */
(function () {
  'use strict';

  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  function hidePreloader() { var pl = $('#preloader'); if (pl) pl.classList.add('hide'); }
  window.addEventListener('load', hidePreloader);
  setTimeout(hidePreloader, 2500);

  // ── GLOBAL TOAST ──
  var toastWrap = $('#toastWrap');
  var ICONS = {
    ok: '<svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>',
    err: '<svg viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>',
    info: '<svg viewBox="0 0 24 24" fill="none" stroke="#C9A84C" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>'
  };
  window.toast = function (msg, type) {
    if (!toastWrap) return;
    type = type || 'info';
    var t = document.createElement('div');
    t.className = 'toast ' + type;
    t.setAttribute('role', 'status');
    t.innerHTML = (ICONS[type] || ICONS.info) + '<span>' + String(msg).replace(/</g, '&lt;') + '</span>';
    toastWrap.appendChild(t);
    requestAnimationFrame(function () { t.classList.add('show'); });
    setTimeout(function () { t.classList.remove('show'); setTimeout(function () { t.remove(); }, 350); }, 4200);
  };

  // ── SCROLL / READING PROGRESS ──
  var progress = $('#scrollProgress');
  function updateProgress() {
    if (!progress) return;
    var h = document.documentElement;
    var max = (h.scrollHeight - h.clientHeight) || 1;
    progress.style.width = Math.min(100, (h.scrollTop / max) * 100) + '%';
  }

  // ── NAV SCROLL ──
  var nav = $('#mainNav'), btt = $('#btt');
  window.addEventListener('scroll', function () {
    if (nav) nav.classList.toggle('scrolled', window.scrollY > 60);
    if (btt) btt.classList.toggle('show', window.scrollY > 400);
    updateProgress();
  }, { passive: true });
  updateProgress();
  if (btt) btt.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });

  // ── MEGA-MENU (accessible) ──
  var megaItems = $$('.nav-item.has-mega');
  function closeAllMega(except) {
    megaItems.forEach(function (it) {
      var btn = it.querySelector('.nav-link-btn');
      if (btn && it !== except) btn.setAttribute('aria-expanded', 'false');
    });
  }
  megaItems.forEach(function (item) {
    var btn = item.querySelector('.nav-link-btn');
    if (!btn) return;
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var open = btn.getAttribute('aria-expanded') === 'true';
      closeAllMega(item);
      btn.setAttribute('aria-expanded', open ? 'false' : 'true');
    });
    // Open on keyboard focus, allow hover (CSS) too.
    item.addEventListener('focusin', function () { closeAllMega(item); btn.setAttribute('aria-expanded', 'true'); });
    item.addEventListener('mouseleave', function () { btn.setAttribute('aria-expanded', 'false'); });
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeAllMega(null); });
  document.addEventListener('click', function (e) { if (!e.target.closest('.nav-item.has-mega')) closeAllMega(null); });

  // ── MOBILE NAV + ACCORDION ──
  var hamburger = $('#hamburgerBtn'), mobileNav = $('#mobileNav'), mnClose = $('#mnClose');
  function openMobile() { mobileNav.classList.add('open'); mobileNav.setAttribute('aria-hidden', 'false'); hamburger.setAttribute('aria-expanded', 'true'); document.body.style.overflow = 'hidden'; }
  function closeMobile() { mobileNav.classList.remove('open'); mobileNav.setAttribute('aria-hidden', 'true'); hamburger.setAttribute('aria-expanded', 'false'); document.body.style.overflow = ''; }
  if (hamburger && mobileNav) {
    hamburger.addEventListener('click', openMobile);
    if (mnClose) mnClose.addEventListener('click', closeMobile);
    $$('.mn-link', mobileNav).forEach(function (l) { l.addEventListener('click', closeMobile); });
    $$('.mn-acc', mobileNav).forEach(function (acc) {
      acc.addEventListener('click', function () {
        var sub = acc.nextElementSibling, open = acc.getAttribute('aria-expanded') === 'true';
        acc.setAttribute('aria-expanded', open ? 'false' : 'true');
        if (sub) sub.classList.toggle('open', !open);
      });
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && mobileNav.classList.contains('open')) closeMobile(); });
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

  // ── REVEAL + COUNT UP ──
  var reveals = $$('.reveal');
  if ('IntersectionObserver' in window) {
    var ro = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('visible'); ro.unobserve(e.target); } });
    }, { threshold: 0.12 });
    reveals.forEach(function (el) { ro.observe(el); });

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

  // ── SMOOTH ANCHOR SCROLL (same-page only) ──
  $$('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var href = a.getAttribute('href');
      if (href === '#' || href.length < 2) return;
      var t = document.querySelector(href);
      if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth' }); }
    });
  });

  // ── SHIPMENT TRACKING ──
  var trackForm = $('#trackForm'), trackInput = $('#trackInput'), trackBtn = $('#trackBtn'), trackResult = $('#trackResult');
  function esc(v) { var d = document.createElement('div'); d.textContent = v == null ? '' : String(v); return d.innerHTML; }
  function trackErr(msg) {
    trackResult.innerHTML = '<div class="tr-card"><div class="tr-meta" style="border:none"><div><div class="val" style="color:#b91c1c">' + esc(msg) + '</div></div></div></div>';
    trackResult.classList.add('show');
  }
  function renderTrack(s) {
    var steps = (s.stages || []).map(function (st) {
      return '<div class="tr-step ' + (st.completed ? 'done' : '') + '"><div class="marker"><div class="dot"></div><div class="line"></div></div>' +
        '<div class="txt"><strong>' + esc(st.label) + '</strong><span>' + esc(st.timestamp || '') + '</span></div></div>';
    }).join('');
    trackResult.innerHTML =
      '<div class="tr-card"><div class="tr-top"><span class="tr-ref">' + esc(s.reference_number) + '</span>' +
        '<span class="tr-pill"><span style="width:7px;height:7px;border-radius:50%;background:#4ade80;display:inline-block"></span>' + esc(s.status) + '</span></div>' +
        '<div class="tr-meta"><div><div class="lbl">Origin</div><div class="val">' + esc(s.origin) + '</div></div>' +
        '<div><div class="lbl">Destination</div><div class="val">' + esc(s.destination) + '</div></div>' +
        '<div><div class="lbl">Current Stage</div><div class="val">' + esc(s.current_stage) + '</div></div></div>' +
        '<div class="tr-steps">' + steps + '</div></div>';
    trackResult.classList.add('show');
    trackResult.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
  function doTrack(e) {
    if (e) e.preventDefault();
    var ref = (trackInput.value || '').trim();
    if (ref.length < 3) { trackErr('Please enter a valid reference number (min 3 characters).'); return; }
    if (trackBtn) trackBtn.disabled = true;
    trackResult.innerHTML = '<div class="tr-skel">' +
      '<div class="sk-row skeleton" style="width:40%;height:18px"></div>' +
      '<div class="sk-row skeleton" style="width:80%"></div>' +
      '<div class="sk-row skeleton" style="width:65%"></div>' +
      '<div class="sk-row skeleton" style="width:72%;margin-bottom:0"></div></div>';
    trackResult.classList.add('show');
    fetch('/index.php?action=track&ref=' + encodeURIComponent(ref))
      .then(function (r) { return r.json(); })
      .then(function (d) { if (d.ok) renderTrack(d.shipment); else trackErr(d.error || 'Shipment not found.'); })
      .catch(function () { trackErr('Something went wrong. Please try again.'); })
      .finally(function () { if (trackBtn) trackBtn.disabled = false; });
  }
  if (trackForm) trackForm.addEventListener('submit', doTrack);

  // ── AJAX FORMS with inline validation ──
  function ajaxForm(formId, msgId, action) {
    var form = document.getElementById(formId);
    if (!form) return;
    var msg = document.getElementById(msgId);
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      // Lightweight inline validation.
      var invalid = null;
      $$('[required]', form).forEach(function (f) {
        var bad = !f.value.trim() || (f.type === 'email' && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(f.value));
        f.classList.toggle('field-invalid', bad);
        if (bad && !invalid) invalid = f;
      });
      if (invalid) { msg.textContent = 'Please complete the highlighted fields correctly.'; msg.className = 'form-msg err'; invalid.focus(); return; }

      msg.textContent = ''; msg.className = 'form-msg';
      var btn = form.querySelector('button[type="submit"], button:not([type])');
      if (btn) { btn.disabled = true; btn.dataset.label = btn.textContent; btn.textContent = 'Sending…'; }
      fetch('/index.php?action=' + action, { method: 'POST', body: new FormData(form) })
        .then(function (r) { return r.json(); })
        .then(function (j) {
          if (j.ok) { msg.textContent = j.message || 'Thank you!'; msg.className = 'form-msg ok'; form.reset(); window.toast(j.message || 'Done!', 'ok'); }
          else { msg.textContent = j.error || 'Please check your details and try again.'; msg.className = 'form-msg err'; window.toast(j.error || 'Please check your details.', 'err'); }
        })
        .catch(function () { msg.textContent = 'Network error. Please try again.'; msg.className = 'form-msg err'; })
        .finally(function () { if (btn) { btn.disabled = false; btn.textContent = btn.dataset.label; } });
    });
  }
  // ── QUOTE WIZARD (progressive enhancement of the contact form) ──
  (function () {
    var form = $('.quote-wizard');
    if (!form) return;
    var steps = Array.prototype.slice.call(form.querySelectorAll('.wiz-step'));
    var inds = Array.prototype.slice.call(form.querySelectorAll('.wiz-ind'));
    var back = form.querySelector('.wiz-back'), next = form.querySelector('.wiz-next'), submit = form.querySelector('.wiz-submit');
    if (steps.length < 2) return;
    form.classList.add('wizard-on');
    var cur = 0;

    function show(i) {
      cur = i;
      steps.forEach(function (s, n) { s.hidden = n !== i; });
      inds.forEach(function (el, n) { el.classList.toggle('active', n === i); el.classList.toggle('done', n < i); });
      back.hidden = i === 0;
      next.hidden = i === steps.length - 1;
      submit.hidden = i !== steps.length - 1;
      var f = steps[i].querySelector('input,select,textarea');
      if (f) setTimeout(function () { f.focus(); }, 40);
    }
    function validStep(i) {
      var ok = true;
      steps[i].querySelectorAll('[required]').forEach(function (f) {
        var good = f.checkValidity() && f.value.trim() !== '';
        f.classList.toggle('field-invalid', !good);
        if (!good && ok) { f.focus(); ok = false; }
      });
      return ok;
    }
    next.addEventListener('click', function () { if (validStep(cur)) show(cur + 1); });
    back.addEventListener('click', function () { show(cur - 1); });
    // Clear the invalid state as the user fixes a field.
    form.addEventListener('input', function (e) { if (e.target.classList) e.target.classList.remove('field-invalid'); });

    // Fold the route/cargo details into the message before the AJAX submit runs.
    form.addEventListener('submit', function (e) {
      if (!validStep(cur)) { e.preventDefault(); e.stopImmediatePropagation(); return; }
      var msg = form.querySelector('#c_msg');
      if (!msg || msg.dataset.composed) return;
      var get = function (id) { var el = form.querySelector(id); return el ? el.value.trim() : ''; };
      var lines = [];
      var route = [get('#c_origin'), get('#c_dest')].filter(Boolean).join(' → ');
      if (route) lines.push('Route: ' + route);
      if (get('#c_cargo')) lines.push('Cargo: ' + get('#c_cargo'));
      if (get('#c_weight')) lines.push('Weight/volume: ' + get('#c_weight'));
      if (lines.length) { msg.value = lines.join('\n') + '\n\n' + msg.value; msg.dataset.composed = '1'; }
    }, true); // capture: run before ajaxForm's handler

    // After a successful send ajaxForm resets the form — return to step 1.
    form.addEventListener('reset', function () {
      var msg = form.querySelector('#c_msg');
      if (msg) delete msg.dataset.composed;
      setTimeout(function () { show(0); }, 0);
    });

    show(0);
  })();

  ajaxForm('inquiryForm', 'inquiryMsg', 'inquiry');
  ajaxForm('newsletterForm', 'newsletterMsg', 'newsletter');

  // ── THEME TOGGLE (dark mode) ──
  (function () {
    var btn = $('#themeToggle');
    if (!btn) return;
    btn.addEventListener('click', function () {
      var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', next);
      try { localStorage.setItem('galilea_theme', next); } catch (e) {}
    });
  })();

  // ── SEARCH OVERLAY + TYPEAHEAD ──
  (function () {
    var toggle = $('#searchToggle'), overlay = $('#searchOverlay'), input = $('#searchInput');
    if (!toggle || !overlay) return;
    function open() { overlay.classList.add('open'); document.body.style.overflow = 'hidden'; setTimeout(function () { if (input) input.focus(); }, 60); }
    function close() { overlay.classList.remove('open'); document.body.style.overflow = ''; clear(); }
    toggle.addEventListener('click', open);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });

    // Suggestions dropdown.
    var box = overlay.querySelector('.search-box');
    var sug = document.createElement('div');
    sug.className = 'search-suggest';
    sug.setAttribute('role', 'listbox');
    if (box) box.appendChild(sug);
    var items = [], active = -1, timer = null, lastQ = '';

    function clear() { sug.innerHTML = ''; sug.classList.remove('show'); items = []; active = -1; }
    function highlight(text, q) {
      var i = text.toLowerCase().indexOf(q.toLowerCase());
      if (i < 0) return esc(text);
      return esc(text.slice(0, i)) + '<mark>' + esc(text.slice(i, i + q.length)) + '</mark>' + esc(text.slice(i + q.length));
    }
    function render(results, q) {
      if (!results.length) { sug.innerHTML = '<div class="sg-empty">No matches — press Enter to search anyway.</div>'; sug.classList.add('show'); items = []; active = -1; return; }
      sug.innerHTML = results.map(function (r, i) {
        return '<a class="sg-item" role="option" href="' + esc(r.url) + '" data-i="' + i + '">' +
          '<span class="sg-kind">' + esc(r.kind) + '</span>' +
          '<span class="sg-title">' + highlight(r.title, q) + '</span></a>';
      }).join('');
      sug.classList.add('show');
      items = Array.prototype.slice.call(sug.querySelectorAll('.sg-item'));
      active = -1;
      items.forEach(function (el) { el.addEventListener('mouseenter', function () { setActive(items.indexOf(el)); }); });
    }
    function setActive(i) {
      if (active >= 0 && items[active]) items[active].classList.remove('active');
      active = i;
      if (active >= 0 && items[active]) items[active].classList.add('active');
    }
    function query(q) {
      fetch('/search?ajax=1&q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'fetch' } })
        .then(function (r) { return r.json(); })
        .then(function (j) { if (input.value.trim() === q) render(j.results || [], q); })
        .catch(function () { clear(); });
    }
    if (input) {
      input.setAttribute('role', 'combobox');
      input.setAttribute('aria-autocomplete', 'list');
      input.addEventListener('input', function () {
        var q = input.value.trim();
        if (q === lastQ) return; lastQ = q;
        clearTimeout(timer);
        if (q.length < 2) { clear(); return; }
        timer = setTimeout(function () { query(q); }, 180);
      });
      input.addEventListener('keydown', function (e) {
        if (!items.length) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); setActive((active + 1) % items.length); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); setActive((active - 1 + items.length) % items.length); }
        else if (e.key === 'Enter' && active >= 0) { e.preventDefault(); window.location.href = items[active].href; }
      });
    }

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && overlay.classList.contains('open')) close();
      if (e.key === '/' && !/^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement.tagName)) { e.preventDefault(); open(); }
    });
  })();

  // ── COOKIE CONSENT ──
  (function () {
    var bar = $('#cookieBar');
    if (!bar) return;
    var stored;
    try { stored = localStorage.getItem('galilea_cookie'); } catch (e) {}
    if (!stored) bar.hidden = false;
    function set(v) { try { localStorage.setItem('galilea_cookie', v); } catch (e) {} bar.hidden = true; }
    var a = $('#cookieAccept'), d = $('#cookieDecline');
    if (a) a.addEventListener('click', function () { set('accepted'); });
    if (d) d.addEventListener('click', function () { set('declined'); });
  })();
})();
