# Deployment files

Ops/config templates for deploying Galilea Global Logistics. This directory is
**denied from the web** (see the rules in the root `.htaccess`, `router.php`,
and `deploy/nginx.conf`), so it is safe to keep here.

## `nginx.conf`
A ready-to-use nginx + PHP-FPM `server {}` block for the flat web-root layout
(repository root = document root). Before using it, edit:

| Setting | Change to |
|---|---|
| `server_name` | your domain |
| `root` | absolute path to this repository |
| `fastcgi_pass` | your PHP-FPM socket (match the installed PHP version) |

Then:

```bash
sudo cp deploy/nginx.conf /etc/nginx/sites-available/galilea
sudo ln -s /etc/nginx/sites-available/galilea /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

For HTTPS, run `sudo certbot --nginx` (it rewrites the block automatically), or
follow the manual TLS notes at the bottom of `nginx.conf`.

### What the config enforces
- Clean-URL routing to `index.php`; `admin.php` served directly.
- `app/`, `data/`, and `deploy/` are not web-accessible (403).
- Dotfiles blocked, except `/.well-known/` (so `security.txt` is reachable).
- PHP execution disabled under `/uploads/`.
- 30-day caching for content-versioned static assets.
- Baseline security headers on every response (the app adds CSP/HSTS on top).

> **Requirements:** PHP-FPM for your PHP version (8.1+) and nginx with the
> standard module set. Ensure `data/` and `uploads/` are writable by the
> PHP-FPM user (e.g. `www-data`).
