# Galilea Global Logistics — Website & Admin

A complete, self-contained **PHP + SQLite** web application for Galilea Global
Logistics: a fast public marketing/logistics website plus a secure admin
content-management portal.

> **Trusted Trade. Global Reach.**

---

## Features

### Public website (`/`)
- **DB-driven mega-menu** with multi-column dropdowns, icons, descriptions and a
  promo cell — fully accessible (keyboard, `aria-expanded`, ESC/outside-click to
  close) with a mobile accordion version.
- **Clean URL routing** with real pages: services listing + detail, insights
  listing (paginated) + article, contact/quote, track, and CMS pages
  (about/careers/privacy/terms/cookies), plus a branded 404.
- Hero slider, services grid, live shipment **Track & Trace**, stats, news,
  leadership team, testimonials slider, quote form, newsletter — all from the DB.
- **UX & accessibility:** skip-to-content link, visible focus styles, ARIA on
  menus/sliders/forms, breadcrumbs, inline form validation, loading states,
  `prefers-reduced-motion` support, and a cookie-consent banner.
- Loads fast: lazy-loaded images, deferred JS, font preconnect, browser caching.
- Tracking, quote requests, and newsletter signups are submitted over AJAX to
  JSON endpoints with CSRF protection and anti-spam honeypots.

### Admin portal (`/admin.php`)
- Secure login with bcrypt password hashing, session hardening, and per-IP
  **login rate limiting**.
- Dashboard with live content/engagement counts and an audit trail.
- Full CRUD for **Hero Slides, Services, News & Insights, Testimonials, Team
  Members, Shipments, FAQs, Static Pages, and the Navigation Menu** (build the
  mega-menu: top-level headings + child links with icons, columns and order).
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
# from the project root — the router enables clean URLs on the built-in server
php -S 127.0.0.1:8000 -t public public/router.php
```
Then open <http://127.0.0.1:8000/>. The database and schema are created
automatically on first request (`data/galilea.sqlite`) and seeded with demo
content. (Under Apache the root/`public` `.htaccess` handles routing; the
`router.php` shim is only needed for PHP's built-in dev server.)

### Public routes
`/` · `/services` · `/services/{slug}` · `/insights` · `/insights/{slug}` ·
`/track` · `/contact` · `/about` · `/careers` · `/privacy` · `/terms` ·
`/cookies` (and any active page slug) — unknown URLs render a branded 404.

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

## SEO & GEO
**Technical / on-page SEO**
- Per-page `<title>`, meta description, **canonical URL**, and robots directives.
- **Open Graph** + **Twitter Card** tags with a configurable default share image.
- Dynamic **`/sitemap.xml`** (home, services, insights, pages) and **`/robots.txt`**.
- JSON-LD structured data on every page: `Organization`, `WebSite` (with a
  Track-&-Trace `SearchAction`), `BreadcrumbList`, `Service`, `NewsArticle`,
  and `FAQPage` — all validated as a single `@graph`.
- Optional **Google Analytics (GA4)** — injected only when an ID is configured,
  with the CSP automatically widened just for that case.

**GEO — geographic / local SEO**
- `LocalBusiness` schema for each office (Kigali HQ, Guangzhou, Yiwu) with
  postal addresses and `GeoCoordinates`.
- `geo.region`, `geo.placename`, `geo.position` and `ICBM` meta tags.
- Editable HQ coordinates and placename in **Site Settings → Geo / Local SEO**.

**GEO — Generative Engine Optimization (AI answer engines)**
- **`/llms.txt`** — a concise, machine-readable company brief (services,
  contacts, key links) that LLM-based search engines can ingest and cite.
- An admin-managed **FAQ** section rendered with `FAQPage` schema — ideal for
  Google rich results and AI-generated answers.

All SEO/GEO values (canonical URL, share image, social handles, analytics ID,
geo coordinates, legal name) are editable under **Site Settings**.

## Performance
- **Image optimisation on upload** — raster uploads are downscaled (max 1600px)
  and re-encoded to **WebP** via GD (typically ~50% smaller); animated GIFs are
  preserved as-is. Falls back to the original file if GD is unavailable.
- **Cache-busting** — CSS/JS are served with a content-version query string so
  browsers cache aggressively yet always pick up new builds instantly.
- **Asset caching** — long-lived `Expires`/cache headers for static assets via
  `public/.htaccess`; lazy-loaded images and deferred JS site-wide.

> **Deployment note:** the admin rich-text editor (Summernote), jQuery and the
> 2FA QR helper load from the cdnjs CDN. In locked-down/offline environments,
> download those files into `public/assets/vendor/` and update the references in
> `app/views/admin/layout_top.php` / `layout_bottom.php` / `account.php`, then
> drop `cdnjs.cloudflare.com` from the CSP in `app/lib/helpers.php`.

## Security highlights
- **SQL injection** — every query uses PDO prepared statements.
- **XSS** — all output escaped via `esc()`; rich-text HTML passes through a
  strict allowlist sanitiser (`sanitize_html`) before storage.
- **CSRF** — token on every state-changing form, verified with `hash_equals`.
- **Two-factor auth (TOTP)** — optional per-account, RFC 6238, dependency-free;
  works with Google Authenticator/Authy/1Password and is enforced at login.
- **Sessions** — `HttpOnly` + `SameSite=Lax` (+ `Secure`/HSTS over HTTPS), ID
  regenerated on login, bound to a client fingerprint, with **idle (30 min)**
  and **absolute (8 h)** timeouts.
- **Role + delegated access** — super-admins have full control; editors are
  restricted to the exact sections granted to them.
- **Brute force / abuse** — per-IP login throttling with lockout, plus
  rate-limiting on the public track/inquiry/newsletter endpoints (HTTP 429).
- **Uploads** — real MIME type detected with `finfo` + `getimagesize`,
  randomised filenames, size cap, and script execution disabled in
  `public/uploads/` via `.htaccess`.
- **Headers** — Content-Security-Policy (auto-widened only when analytics is
  enabled), HSTS, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`,
  `Referrer-Policy`, `Permissions-Policy`.
- **Backups** — one-click `.sqlite` snapshot (`VACUUM INTO`) and CSV exports of
  inquiries/subscribers (super-admin only).
- **Disclosure** — `/.well-known/security.txt` published.
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
