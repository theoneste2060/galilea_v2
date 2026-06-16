# Tests

A dependency-free smoke-test suite (no PHPUnit/Composer required).

```bash
php tests/run.php
```

It boots the app on a throwaway port with a temporary SQLite database
(`GALILEA_DB`), exercises the public site, admin portal and security rules over
real HTTP, then tears everything down. Exit code is `0` when all checks pass and
`1` otherwise, so it drops straight into CI or a pre-push hook.

## What it covers
- Public pages render (home, services, insights, contact, track, about) + 404.
- Site search (typeahead JSON + HTML results) and insights category filtering.
- Form handling: CSRF presence, validation rejection (422), happy path, and
  CSRF-mismatch rejection (419).
- Shipment tracking lookup (unknown reference → 404).
- Security: `app/`, `data/`, `tests/` are forbidden over the web (403).
- Admin: auth gate, login, dashboard + charts, core CRUD sections, list search,
  email settings group, and the shipment stage builder.

Override the port with `TEST_PORT=9000 php tests/run.php` if 8971 is busy.
