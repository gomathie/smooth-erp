# Migrating Smooth ERP to Supabase (PostgreSQL)

The application was MySQL-only. The **code is now dual-driver** (`mysql` | `pgsql`)
and stays on MySQL by default, so nothing changed for your current XAMPP setup.
This guide covers the remaining, environment-specific steps to run on Supabase.

> ⚠️ The code-side port is done and verified on MySQL, but **the Postgres path
> could not be runtime-tested here** (no `pdo_pgsql` driver and no Supabase
> password in this environment). Work through the checklist below against your
> Supabase project and report any query that errors — most will be data-shape,
> not code.

---

## What the code change already did

| Area | Change | File |
|---|---|---|
| Connection | `connect()` branches on `DB_CONNECTION`; adds `pgsql` DSN + `sslmode=require` | `models/connection.php` |
| camelCase keys | Postgres lower-cases identifiers; a custom `CamelCaseStatement` remaps result keys back to camelCase (pgsql only) | `helpers/db_camelcase.php` |
| `INSERT IGNORE` | → `INSERT … ON CONFLICT DO NOTHING` on pgsql | organizations / reports_center models |
| `ON DUPLICATE KEY UPDATE` | → `ON CONFLICT (…) DO UPDATE` on pgsql | `models/settings.model.php` |
| `CAST(x AS UNSIGNED)` | → `CAST(x AS BIGINT)` on pgsql | quotations model + controller |
| Backtick identifiers | removed from the `Scope` helper (works on both) | `helpers/tenant_query.php` |

To switch a deployment to Postgres you only set env vars (Step 3) — no further code edits.

---

## Step 1 — Enable the PostgreSQL PDO driver

**XAMPP / local:** in `php.ini` uncomment:
```
extension=pdo_pgsql
extension=pgsql
```
then restart Apache. Verify: `php -m | findstr pgsql`.

**Docker:** already wired — the `dockerfile` now installs `libpq-dev` and builds
`pdo_pgsql pgsql` alongside `pdo_mysql`, so the image supports either engine. Just
rebuild: `docker compose build`.

---

## Step 2 — Move schema + data into Supabase

Use **pgloader** — the standard MySQL→PostgreSQL tool. It maps types, creates
IDENTITY/serial PKs, and **lower-cases identifiers**, which is exactly what the
`CamelCaseStatement` remapper expects.

```
# loader.load
LOAD DATABASE
     FROM mysql://root@localhost/posystem
     INTO postgresql://postgres:[YOUR-PASSWORD]@<SESSION-POOLER-HOST>:5432/postgres
WITH include drop, create tables, create indexes, reset sequences,
     downcase identifiers
SET work_mem to '64MB';
```
```
pgloader loader.load
```

If you'd rather hand-load: dump the MySQL schema, convert types
(`INT AUTO_INCREMENT`→`GENERATED ALWAYS AS IDENTITY`, `DATETIME`→`timestamp`,
`TINYINT`→`smallint`, `ENUM`→`varchar` + `CHECK`, drop `ENGINE/CHARSET/COLLATE`),
create lower-cased columns, then import data. pgloader is strongly recommended.

---

## Step 3 — Point `config/.env` at Supabase

Use the **Session Pooler** host (IPv4) for a PHP host, not the direct
`db.<ref>.supabase.co` (IPv6-only unless you buy the IPv4 add-on).

```dotenv
DB_CONNECTION=pgsql
DB_HOST=<your-session-pooler-host>      # e.g. aws-0-<region>.pooler.supabase.com
DB_PORT=5432
DB_NAME=postgres
DB_USER=postgres.<project-ref>          # pooler username form
DB_PASS=<YOUR-SUPABASE-PASSWORD>
DB_SSLMODE=require
```
`config/.env` is git-ignored and denied by `.htaccess` — keep the password out of source control.

---

## Step 4 — Test (the part only you can do)

1. Login + dashboard render (all small-boxes/widgets).
2. CRUD on each module: products, categories, customers, sales, quotations,
   invoices, expenses, accounting, currencies, users, settings.
3. Reports Center + each report (date ranges).
4. PDF endpoints (invoice/quotation/sale).
5. Watch `storage/logs/app.log` — the new global handler logs any DB/SQL error.

---

## Known PostgreSQL caveats to watch

- **String comparisons are case-sensitive** (MySQL's default collation was not).
  This can affect username/code lookups (`'Admin'` ≠ `'admin'`). If you rely on
  case-insensitive matching, use `ILIKE`/`lower()` or a `citext` column.
- **`ON UPDATE CURRENT_TIMESTAMP`** has no Postgres equivalent — auto-updating
  `modifiedDate`/`date` columns won't refresh on UPDATE. The app already sets
  `modifiedDate = NOW()` explicitly where it matters; add triggers if you need it elsewhere.
- **`lastInsertId()`** (4 spots) returns `lastval()` on pgsql — works for
  IDENTITY/serial PKs (which pgloader creates). Verify after inserts.
- **camelCase remap** (`ColumnMap` in `helpers/db_camelcase.php`) covers all known
  columns + aliases; if a screen shows blank values, a column/alias is missing
  from the list — add it there.
- **Booleans:** keep `TINYINT(1)` status flags as `smallint` (not `boolean`) so the
  existing `!= 0` / `=== 1` checks keep working (pgloader maps `tinyint`→`smallint`).
