/* Galilea admin — editor + uploads + UI */
(function () {
  'use strict';

  // Summernote rich-text editor for any .summernote textarea.
  if (window.jQuery && jQuery.fn.summernote) {
    jQuery('.summernote').summernote({
      height: 220,
      toolbar: [
        ['style', ['bold', 'italic', 'underline', 'clear']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['insert', ['link']],
        ['view', ['codeview']],
      ],
      // Images are managed via the dedicated upload field, not pasted inline,
      // so disallow embedding to keep payloads small and safe.
      disableDragAndDrop: true,
      callbacks: {
        onImageUpload: function () { /* intentionally disabled */ }
      }
    });
  }

  // Restore sidebar collapse preference.
  try {
    if (localStorage.getItem('galilea_sb') === '1') {
      var sb = document.getElementById('sb'), mn = document.getElementById('mn');
      if (sb) sb.classList.add('collapsed');
      if (mn) mn.classList.add('collapsed');
    }
  } catch (e) {}
  var toggle = document.querySelector('.sb-toggle');
  if (toggle) {
    toggle.addEventListener('click', function () {
      try {
        var collapsed = document.getElementById('sb').classList.contains('collapsed');
        localStorage.setItem('galilea_sb', collapsed ? '1' : '0');
      } catch (e) {}
    });
  }

  // Drag & drop image upload zones.
  document.querySelectorAll('.img-drop').forEach(function (zone) {
    var input = zone.querySelector('.img-file');
    if (!input) return;
    var preview = zone.querySelector('.img-preview');

    function showPreview(file) {
      if (!file || !/^image\//.test(file.type)) return;
      var reader = new FileReader();
      reader.onload = function (e) {
        if (!preview) {
          preview = document.createElement('div');
          preview.className = 'img-preview';
          zone.insertBefore(preview, zone.firstChild);
        }
        preview.innerHTML = '<img src="' + e.target.result + '" alt="preview">';
      };
      reader.readAsDataURL(file);
    }

    zone.addEventListener('click', function (e) {
      if (e.target.closest('.img-remove')) return;
      input.click();
    });
    input.addEventListener('change', function () { if (input.files[0]) showPreview(input.files[0]); });

    ['dragenter', 'dragover'].forEach(function (ev) {
      zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.add('drag'); });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
      zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.remove('drag'); });
    });
    zone.addEventListener('drop', function (e) {
      var file = e.dataTransfer && e.dataTransfer.files[0];
      if (file) {
        // Assign dropped file to the hidden input so it submits with the form.
        try {
          var dt = new DataTransfer();
          dt.items.add(file);
          input.files = dt.files;
        } catch (err) {}
        showPreview(file);
      }
    });
  });

  // Render TOTP QR code (qrcodejs loaded only on the account setup page).
  (function () {
    var el = document.getElementById('totp-qr');
    if (el && window.QRCode && el.dataset.uri) {
      new QRCode(el, { text: el.dataset.uri, width: 158, height: 158, correctLevel: QRCode.CorrectLevel.M });
    }
  })();

  // ── THEME TOGGLE (dark mode) ──
  (function () {
    var btn = document.getElementById('themeToggle');
    if (!btn) return;
    btn.addEventListener('click', function () {
      var cur = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
      var next = cur === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', next);
      try { localStorage.setItem('galilea_theme', next); } catch (e) {}
    });
  })();

  // ── TOASTS ──
  var toastWrap = document.getElementById('toastWrap');
  function toast(msg, type) {
    if (!toastWrap) return;
    var t = document.createElement('div');
    t.className = 'toast ' + (type || 'ok');
    t.setAttribute('role', 'status');
    t.innerHTML = '<span>' + String(msg).replace(/</g, '&lt;') + '</span>';
    toastWrap.appendChild(t);
    requestAnimationFrame(function () { t.classList.add('show'); });
    setTimeout(function () { t.classList.remove('show'); setTimeout(function () { t.remove(); }, 320); }, 4000);
  }
  // Surface server flash messages as toasts.
  var fd = document.getElementById('flashData');
  if (fd) {
    try { (JSON.parse(fd.dataset.flashes) || []).forEach(function (f) { toast(f.msg, f.type === 'error' ? 'err' : 'ok'); }); } catch (e) {}
  }

  // ── COPY-TO-CLIPBOARD (media library) ──
  document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var text = btn.dataset.copy;
      var done = function () { toast('URL copied to clipboard.', 'ok'); };
      if (navigator.clipboard) { navigator.clipboard.writeText(text).then(done, function () {}); }
      else { var i = document.createElement('input'); i.value = text; document.body.appendChild(i); i.select(); try { document.execCommand('copy'); done(); } catch (e) {} i.remove(); }
    });
  });

  // ── BULK SELECT ──
  (function () {
    var all = document.getElementById('bulkAll');
    var bar = document.getElementById('bulkBar');
    var count = document.getElementById('bulkCount');
    var boxes = Array.prototype.slice.call(document.querySelectorAll('.row-check'));
    if (!boxes.length) return;
    function refresh() {
      var n = boxes.filter(function (b) { return b.checked; }).length;
      if (bar) bar.hidden = n === 0;
      if (count) count.textContent = n + ' selected';
      if (all) all.checked = n === boxes.length && n > 0;
    }
    if (all) all.addEventListener('change', function () { boxes.forEach(function (b) { b.checked = all.checked; }); refresh(); });
    boxes.forEach(function (b) { b.addEventListener('change', refresh); });
  })();

  // ── DRAG TO REORDER ──
  (function () {
    var table = document.querySelector('.dt[data-reorder]');
    if (!table) return;
    var tbody = table.querySelector('tbody');
    var dragEl = null;
    tbody.addEventListener('dragstart', function (e) {
      var tr = e.target.closest('tr'); if (!tr) return;
      dragEl = tr; tr.classList.add('dragging');
    });
    tbody.addEventListener('dragend', function () {
      if (dragEl) dragEl.classList.remove('dragging');
      tbody.querySelectorAll('.drag-over').forEach(function (r) { r.classList.remove('drag-over'); });
      dragEl = null;
    });
    tbody.addEventListener('dragover', function (e) {
      e.preventDefault();
      var tr = e.target.closest('tr');
      if (!tr || tr === dragEl) return;
      var rect = tr.getBoundingClientRect();
      var after = (e.clientY - rect.top) / rect.height > 0.5;
      tbody.insertBefore(dragEl, after ? tr.nextSibling : tr);
    });
    tbody.addEventListener('drop', function (e) {
      e.preventDefault();
      var order = Array.prototype.map.call(tbody.querySelectorAll('tr[data-id]'), function (tr) { return tr.dataset.id; });
      var csrf = (document.querySelector('input[name="_csrf"]') || {}).value || '';
      var body = new URLSearchParams(); body.append('_csrf', csrf);
      order.forEach(function (id) { body.append('order[]', id); });
      fetch('/admin.php?action=reorder&resource=' + table.dataset.reorder, { method: 'POST', body: body })
        .then(function (r) { return r.json(); })
        .then(function (j) { toast(j.ok ? 'Order saved.' : 'Could not save order.', j.ok ? 'ok' : 'err'); })
        .catch(function () { toast('Could not save order.', 'err'); });
    });
  })();
})();
