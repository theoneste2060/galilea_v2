# Galilea Global Logistics — Website & Admin

A complete, self-contained **PHP + SQLite** web application for Galilea Global
Logistics: a fast public marketing/logistics website plus a secure admin
content-management portal.

> **Trusted Trade. Global Reach.**

---

## Features

### Public website (`/`)
- Hero slider, services grid, live shipment **Track & Trace**, stats, industry
  solutions, news/insights, leadership team, testimonials slider, quote-request
  form, and newsletter signup — all driven from the database.
- Loads fast: lightweight preloader, lazy-loaded images, deferred JS, font
  preconnect, and short-lived browser caching.
- Tracking, quote requests, and newsletter signups are submitted over AJAX to
  JSON endpoints with CSRF protection and anti-spam honeypots.

### Admin portal (`/admin.php`)
- Secure login with bcrypt password hashing, session hardening, and per-IP
  **login rate limiting**.
- Dashboard with live content/engagement counts and an audit trail.
- Full CRUD for **Hero Slides, Services, News & Insights, Testimonials, Team
  Members, and Shipments**.
- **Inquiries** inbox (with status workflow) and **Newsletter Subscribers**.
- **Site Settings** (contacts, stats, SEO), **Admin Users** (super-admin only),
  and **Activity Logs**.
- **Image uploads via drag-and-drop** (no external links) with strict MIME/size
  validation.
- **Summernote** rich-text editor for long-form content (service descriptions,
  news bodies).

---

## Requirements
- PHP **8.1+** with `pdo_sqlite`, `fileinfo`, `mbstring`, and `dom` extensions
  (all standard).
- No Composer dependencies, no build step.

## Running locally
```bash
# from the project root
php -S 127.0.0.1:8000 -t public
```
Then open <http://127.0.0.1:8000/>. The database and schema are created
automatically on first request (`data/galilea.sqlite`) and seeded with demo
content.

## Deploying (Apache / shared hosting)
- Point the document root at the `public/` directory **(recommended)**, or
- Drop the whole folder in place — the root `.htaccess` rewrites requests into
  `public/` and blocks access to `app/` and `data/`.

The `app/` and `data/` directories must **not** be web-accessible. Ensure
`data/` and `public/uploads/` are writable by the web server.

## First login
| | |
|---|---|
| URL | `/admin.php` |
| Username | `admin` |
| Password | `Galilea@2025` |

**Change this password immediately** after first login (Users → edit), or set
`GALILEA_ADMIN_USER` / `GALILEA_ADMIN_PASS` env vars *before* the first run.

---

## Security highlights
- **SQL injection** — every query uses PDO prepared statements.
- **XSS** — all output escaped via `esc()`; rich-text HTML passes through a
  strict allowlist sanitiser (`sanitize_html`) before storage.
- **CSRF** — token on every state-changing form, verified with `hash_equals`.
- **Sessions** — `HttpOnly` + `SameSite=Lax` (+ `Secure` over HTTPS), ID
  regenerated on login, bound to a client fingerprint.
- **Uploads** — real MIME type detected with `finfo` + `getimagesize`,
  randomised filenames, size cap, and script execution disabled in
  `public/uploads/` via `.htaccess`.
- **Brute force** — per-IP login throttling with lockout.
- **Headers** — Content-Security-Policy, `X-Frame-Options: DENY`,
  `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy`.
- **Auditing** — admin actions recorded to the activity log.

## Project layout
```
galilea_v2/
├── public/                  # web root (document root)
│   ├── index.php            # public site + JSON API (track/inquiry/newsletter)
│   ├── admin.php            # admin front controller (auth + routing)
│   ├── assets/              # css, js, images, logo
│   └── uploads/             # drag-and-drop image uploads (runtime)
├── app/
│   ├── bootstrap.php        # config, DB init, session
│   ├── config.php           # settings (env-overridable)
│   ├── lib/                 # Database.php, helpers.php (security/uploads/auth)
│   ├── admin/               # resources.php (schema) + engine.php (CRUD)
│   └── views/               # public/ and admin/ templates
└── data/                    # SQLite database (created at runtime)
```
