/* Galilea admin — editor + uploads + UI */
(function () {
  'use strict';

  // ── SHIPMENT STAGE BUILDER (progressive enhancement of the textarea) ──
  document.querySelectorAll('[data-stage-editor]').forEach(function (editor) {
    var src = editor.parentNode.querySelector('.stage-source');
    if (!src) return;

    function parse(text) {
      return text.split('\n').map(function (l) { return l.trim(); }).filter(Boolean).map(function (line) {
        var p = line.split('|').map(function (s) { return s.trim(); });
        return { label: p[0] || '', ts: p[1] || '', done: ['1', 'true', 'yes'].indexOf(p[2]) !== -1 };
      });
    }
    function serialize(rows) {
      return rows.map(function (r) { return r.label + ' | ' + r.ts + ' | ' + (r.done ? '1' : '0'); }).join('\n');
    }
    function collect() {
      return Array.prototype.map.call(editor.querySelectorAll('.stage-row'), function (row) {
        return {
          label: row.querySelector('.st-label').value.trim(),
          ts: row.querySelector('.st-ts').value.trim(),
          done: row.querySelector('.st-done').checked
        };
      });
    }
    function sync() { src.value = serialize(collect()); }

    function rowEl(stage) {
      var row = document.createElement('div');
      row.className = 'stage-row';
      row.innerHTML =
        '<span class="st-grip" title="Drag to reorder">⋮⋮</span>' +
        '<input class="fi st-label" type="text" placeholder="Stage, e.g. Departed Shanghai" value="">' +
        '<input class="fi st-ts" type="text" placeholder="When, e.g. 12 Jun 2026 09:30" value="">' +
        '<label class="st-done-wrap" title="Completed"><input class="st-done" type="checkbox"><span>Done</span></label>' +
        '<button type="button" class="st-del" title="Remove stage" aria-label="Remove stage">&times;</button>';
      row.querySelector('.st-label').value = stage.label;
      row.querySelector('.st-ts').value = stage.ts;
      row.querySelector('.st-done').checked = stage.done;
      row.setAttribute('draggable', 'true');
      row.querySelector('.st-del').addEventListener('click', function () { row.remove(); sync(); });
      row.addEventListener('input', sync);
      row.addEventListener('change', sync);
      return row;
    }

    var list = document.createElement('div');
    list.className = 'stage-list';
    parse(src.value).forEach(function (s) { list.appendChild(rowEl(s)); });

    var add = document.createElement('button');
    add.type = 'button';
    add.className = 'btn btn-ghost btn-sm stage-add';
    add.textContent = '+ Add stage';
    add.addEventListener('click', function () {
      var row = rowEl({ label: '', ts: '', done: false });
      list.appendChild(row);
      row.querySelector('.st-label').focus();
      sync();
    });

    // Drag-to-reorder within the list.
    var dragRow = null;
    list.addEventListener('dragstart', function (e) { dragRow = e.target.closest('.stage-row'); if (dragRow) dragRow.classList.add('dragging'); });
    list.addEventListener('dragend', function () { if (dragRow) { dragRow.classList.remove('dragging'); dragRow = null; sync(); } });
    list.addEventListener('dragover', function (e) {
      e.preventDefault();
      var over = e.target.closest('.stage-row');
      if (!over || over === dragRow) return;
      var rect = over.getBoundingClientRect();
      list.insertBefore(dragRow, (e.clientY - rect.top) / rect.height > 0.5 ? over.nextSibling : over);
    });

    editor.appendChild(list);
    editor.appendChild(add);
    src.hidden = true; // hide the raw textarea now the builder is live
  });

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

  // ── BULK SELECT (WordPress-style bulk actions) ──
  (function () {
    var all = document.getElementById('bulkAll');
    var count = document.getElementById('bulkCount');
    var action = document.getElementById('bulkAction');
    var form = document.getElementById('bulkForm');
    var boxes = Array.prototype.slice.call(document.querySelectorAll('.row-check'));
    if (!boxes.length) return;
    function selected() { return boxes.filter(function (b) { return b.checked; }); }
    function refresh() {
      var n = selected().length;
      if (count) count.textContent = n ? n + ' item' + (n === 1 ? '' : 's') + ' selected' : '';
      if (all) all.checked = n === boxes.length && n > 0;
      if (all) all.indeterminate = n > 0 && n < boxes.length;
    }
    if (all) all.addEventListener('change', function () { boxes.forEach(function (b) { b.checked = all.checked; }); refresh(); });
    boxes.forEach(function (b) { b.addEventListener('change', refresh); });
    if (form) form.addEventListener('submit', function (e) {
      var act = action ? action.value : '';
      var n = selected().length;
      if (act !== 'delete') { e.preventDefault(); if (typeof toast === 'function') toast('Choose an action from the menu first.', 'err'); return; }
      if (n === 0) { e.preventDefault(); if (typeof toast === 'function') toast('Select at least one item.', 'err'); return; }
      if (!confirm('Delete ' + n + ' selected item' + (n === 1 ? '' : 's') + ' permanently? This cannot be undone.')) { e.preventDefault(); }
    });
    refresh();
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
