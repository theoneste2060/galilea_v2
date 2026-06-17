# Tools

Maintenance scripts. This directory is **denied from the web** (see the deny
rules in the root `.htaccess`, `router.php` and `deploy/nginx.conf`).

## `apply_authoritative_content.php`
Applies Galilea's authoritative company data (registered office, canonical URL,
legal name + TIN, business hours, the official About copy, the 10-item FAQ set,
the leadership team, and the Project Cargo + E-commerce Fulfilment services) to
a **live** database.

```bash
php tools/apply_authoritative_content.php
```

Idempotent and non-destructive: it **upserts** the relevant records (updates if
present, inserts if missing) and never deletes rows. Run it once on the server
after deploying; safe to re-run.
