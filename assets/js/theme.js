/* Applies the saved theme before first paint to avoid a flash of light mode.
   Loaded synchronously in <head> (CSP: script-src 'self'). */
(function () {
  try {
    var t = localStorage.getItem('galilea_theme');
    if (t === 'dark' || t === 'light') {
      document.documentElement.setAttribute('data-theme', t);
    }
  } catch (e) {}
})();
