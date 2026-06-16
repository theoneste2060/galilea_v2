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

  // Auto-dismiss flash messages.
  setTimeout(function () {
    document.querySelectorAll('.alert').forEach(function (a) {
      a.style.transition = 'opacity .4s'; a.style.opacity = '0';
      setTimeout(function () { a.remove(); }, 400);
    });
  }, 4500);
})();
