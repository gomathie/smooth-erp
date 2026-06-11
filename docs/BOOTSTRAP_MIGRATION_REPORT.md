# Bootstrap 3 → 5 Migration Report (Phase 1: Discovery)

**Date:** 2026-06-10
**Scope:** UI-only migration. No changes to controllers, models, DB queries, or business
logic (accounting, inventory, sales, quotations, invoices, payments, reporting, auth).
**Status:** Discovery complete. **No code changed.** One architectural decision is required
before Phase 2 (see §1 and §13).

---

## 1. Executive Summary — read this first

This is **not** a straight Bootstrap 3 → 5 swap. The entire admin UI is built on
**AdminLTE v2.4.0**, a dashboard theme whose compiled CSS (`views/dist/css/AdminLTE.css`)
is written against **Bootstrap 3.3.7** markup and the BS3 grid/mixins. The application
chrome — sidebar, top navbar, the `content-wrapper`, and the `box` component used as the
primary content card — is **AdminLTE**, not vanilla Bootstrap.

Consequences:

- Simply replacing `bootstrap.min.css` 3.3.7 with 5.x will **not** produce a Bootstrap‑5
  UI. AdminLTE 2.4.0's CSS still targets BS3 class names (`.col-xs-*`, `.pull-right`,
  panels, `.label`, input-group addons, `has-feedback`, etc.) and will visually break the
  sidebar, navbar, boxes, and forms.
- Bootstrap 5 **removed** the things AdminLTE 2 relies on: the `.col-xs-*` infix, jQuery
  plugin API, `.pull-left/right`, `.panel`, `.well`, Glyphicons, `data-toggle`, the
  `form-group`/`input-group-addon`/`has-feedback` form model, and `.label`.

**Therefore the real decision is how to handle the AdminLTE shell** (§13). Everything else
(grid, modals, utilities, icons) is mechanical and largely covered by a compatibility CSS
layer plus find/replace.

The good news from discovery:

- **Glyphicons are effectively already gone** — only **5** uses, all in
  `views/modules/login.php` (the form-feedback icons). Icons everywhere else are Font
  Awesome 4.7.0 (`fa fa-*`) and Ionicons, which are framework-independent. Icon migration
  is trivial.
- **No Bootstrap tabs** in use (`nav-tabs`/`tab-pane` = 0 matches) — one whole category removed.
- **Bootstrap's `panel`/`well` are barely used** — the app uses AdminLTE's `box` instead,
  so panel→card churn is small; box→card is the larger pattern.
- Programmatic Bootstrap JS calls (`.modal('show')`, etc.) are rare (**5** total).

---

## 2. Current Stack Inventory (verified)

| Library | Version | Loaded in | Notes |
|---|---|---|---|
| Bootstrap | **3.3.7** | `views/template.php` `<head>` | CSS + JS |
| AdminLTE | **2.4.0** | `views/dist/css/AdminLTE.css`, `views/dist/js/adminlte.min.js` | **BS3-coupled theme** |
| Font Awesome | **4.7.0** | head | framework-independent; keep |
| Ionicons | (bundled) | head | icon font; keep |
| jQuery | **3.2.1** | head | required by DataTables + plugins; **keep** |
| DataTables | datatables.net + `datatables.net-bs` (BS3 styling) | head | BS3 integration build |
| SweetAlert2 | bundled (`views/plugins/sweetalert2`) | head | framework-independent |
| iCheck | bundled (`views/plugins/iCheck`) | head | jQuery checkbox styling |
| bootstrap-daterangepicker | bundled + moment.js | head | BS-styled dropdown |
| InputMask | bundled | head | jQuery |
| jQuery Number | bundled | head | jQuery |
| Morris.js + Raphael | bundled | head | charts |
| Chart.js | bundled | head | charts |
| FastClick | bundled | head | **obsolete**, safe to drop later |

---

## 3. Bootstrap 3 Components / Classes In Use (measured)

Counts are occurrences across `**/*.php` (includes some dead `.html.php` files and a few
vendor/TCPDF false positives, noted). "Active files" = reachable via the route whitelist in
`views/template.php` or included as partials.

| Pattern | Occurrences | Files | Migration target | Effort |
|---|---|---|---|---|
| AdminLTE shell (`content-wrapper`, `box box-*`, `box-header/body/footer`, `main-sidebar`, `skin-*`) | **289** | 54 | AdminLTE decision (§13) + `box`→`card` | **High** |
| `data-toggle` / `data-target` / `data-dismiss` | **85** | 23 | `data-bs-*` | Medium |
| Modal markup (`.modal`, `modal fade`, modal toggles/dismiss) | **255** | 18 | BS5 modal markup + `data-bs-*` | **High (critical)** |
| Grid `col-xs-*` | **76** | 17 | `col-*` (BS5 dropped the `xs` infix) | Medium |
| `pull-left` / `pull-right` | **51** | 29 | `float-start` / `float-end` (compat layer) | Low |
| `img-responsive`, `help-block`, `input-sm`, `input-lg`, `well`, `center-block` | **130** | 25 | compat layer + targeted swaps | Medium |
| `has-feedback`, `form-control-feedback`, `btn-default`, `label-*`, `thumbnail`, `caret` | **94** | 37 | per-class (see §4); some vendor false positives | Medium |
| Bootstrap `panel*` | **12** | 9 | `card` (several are vendor/comment false positives → real count ~3–4) | Low |
| Glyphicons | **5** | 1 (`login.php`) | Font Awesome (already standard here) | Trivial |
| Bootstrap tabs (`nav-tabs`/`tab-pane`) | **0** | 0 | none | None |

---

## 4. Deprecated BS3 → BS5 Class Mapping (verified-in-context list)

These will be applied **carefully, with context checks** (not blind find/replace). Where a
1:1 utility exists it goes in the compatibility layer (§ Phase 2) so existing markup keeps
working without touching 54 files.

| Bootstrap 3 | Bootstrap 5 | Handling |
|---|---|---|
| `pull-left` / `pull-right` | `float-start` / `float-end` | **compat CSS** |
| `center-block` | `mx-auto d-block` | **compat CSS** |
| `img-responsive` | `img-fluid` | **compat CSS** |
| `input-sm` / `input-lg` | `form-control-sm` / `form-control-lg` | compat CSS (form controls) |
| `btn-sm`/`btn-lg` (BS3 `btn-xs`) | `btn-sm` / `btn-lg` (no `btn-xs`) | compat CSS adds `.btn-xs` |
| `help-block` | `form-text` | **compat CSS** |
| `well` | `card card-body` | compat CSS (visual) |
| `text-left` / `text-right` | `text-start` / `text-end` | compat CSS |
| `hidden-xs`/`visible-*` | `d-none d-sm-block` etc. | compat CSS |
| `col-xs-N` | `col-N` | targeted edit (infix removed) |
| `panel` / `panel-heading` / `panel-body` / `panel-footer` | `card` / `card-header` / `card-body` / `card-footer` | targeted edit |
| `label label-success` | `badge text-bg-success` | targeted edit |
| `btn-default` | `btn-secondary` (or `btn-outline-secondary`) | targeted edit |
| `has-feedback` + `form-control-feedback` | removed in BS5 → use input-group/icon | targeted edit (only `login.php`) |
| `input-group-addon` | `input-group-text` | targeted edit |
| `form-group` (layout) | spacing utility `mb-3` (still works visually via compat) | compat CSS keeps `.form-group` spacing |
| `data-toggle`/`data-target`/`data-dismiss` | `data-bs-*` | targeted edit |
| Glyphicons (`glyphicon glyphicon-*`) | `fa fa-*` | targeted edit (`login.php` only) |

---

## 5. Bootstrap JS Plugins In Use

| Plugin | Where | BS5 change | Plan |
|---|---|---|---|
| **Modal** | 18 files, 255 markup hits; JS `.modal()` in `accounting.js`, `payments.js`, `organizations.php` | jQuery plugin removed; `data-bs-toggle/target/dismiss`; JS via `bootstrap.Modal` | markup → `data-bs-*`; add a tiny jQuery `$.fn.modal` shim OR convert the 3–4 JS calls |
| **Dropdown** | header (theme/lang/user menus) | `data-bs-toggle="dropdown"`; markup tweaks | targeted edit in `header.php` |
| **Collapse** (sidebar treeview, navbar) | AdminLTE-driven | AdminLTE JS handles treeview, not Bootstrap collapse | verify after shell decision |
| Tabs | none | n/a | none |
| Tooltip/Popover | none detected | opt-in in BS5 | none |
| Carousel | none | n/a | none |

**Programmatic BS JS calls total: 5** (`payments.js`×1, `accounting.js`×2, `organizations.php`×1, plus demo). Low.

---

## 6. jQuery Dependencies (MUST keep)

jQuery 3.2.1 stays. Hard dependents: DataTables, iCheck, daterangepicker, InputMask,
jQuery Number, Morris, AdminLTE 2 JS, plus **all app scripts** (`views/js/*.js`:
template/users/categories/products/customers/sales/invoices/quotations/payments/accounting/reports).
Bootstrap 5 itself no longer needs jQuery, but the app does — **no jQuery removal in this migration.**

---

## 7. Glyphicons Usage

Only **5 occurrences, all in `views/modules/login.php`** (`glyphicon-envelope`,
`glyphicon-user`, `glyphicon-lock` as `form-control-feedback` icons). Everything else is
Font Awesome 4.7.0 / Ionicons. → Trivial. See `ICON_MIGRATION_MAP.md` (Phase: Icons).

---

## 8. Admin Template Analysis (the core issue)

**AdminLTE v2.4.0** provides: `main-header`/navbar, `main-sidebar`/`sidebar-menu` (with
`treeview` collapsibles), `content-wrapper`, the `box` card component (`box box-primary`,
`box-header with-border`, `box-body`, `box-footer`), `skin-*` themes, and the
`fixed sidebar-mini` body classes (see `views/template.php`). All of this is BS3-era CSS.

AdminLTE 2.4.0 **has no Bootstrap 5 version.** The lineage is:
- AdminLTE 2.x → Bootstrap 3
- AdminLTE 3.x → Bootstrap 4
- **AdminLTE 4.x → Bootstrap 5** (current; class names and some markup changed, e.g. boxes → cards, sidebar markup differs)

So a true "Bootstrap 5 UI" requires either upgrading the shell or replacing it. This is the
decision in §13. Note the app already has a **custom CSS-variable theme engine**
(`views/dist/css/pos-themes.css` + `views/js/themes.config.js`) layered on top of AdminLTE
skins — whatever path we choose must preserve that theming.

---

## 9. Third-Party Plugins Affected

| Plugin | Impact under BS5 | Action |
|---|---|---|
| **DataTables** (`datatables.net-bs` = BS3 styling) | BS3 style classes mismatch BS5 | switch to `datatables.net-bs5` styling build (drop-in; same JS/AJAX) |
| **daterangepicker** | renders its own dropdown; minor BS5 class drift | keep; verify popup styling, patch via compat CSS if needed |
| **iCheck** | unmaintained; styles checkboxes/radios via jQuery | keep for now; BS5 has native styled checks — optional later swap |
| **SweetAlert2** | framework-independent | no change |
| **Morris / Raphael / Chart.js** | framework-independent | no change |
| **InputMask / jQuery Number** | framework-independent | no change |
| **FastClick** | obsolete (touch 300ms fix unneeded) | safe to remove later (not required) |

---

## 10. Active vs Dead Files (scope control)

**Dead / legacy (NOT routed, NOT included) — exclude from migration, consider deleting separately:**
`views/modules/categories.html.php`, `products.html.php`, `users.html.php`,
`customers.html.php`, `sales.html.php`, `create-sales.php`, `manage-sales.php`,
`sales-report.php`. (`sales.html.php` is the only referrer of `create-sales`, and nothing
includes the `.html.php` files.)

**Active partials (in scope):** `header.php`, `sidebar.php`, `footer.php`, `login.php`,
`404.php`, `home/top-boxes.php`, `home/recent-products.php`,
`reports/{sales-graph,bestseller-products,sellers,buyers,filter}.php`.

**Active page modules (in scope):** the route whitelist in `views/template.php` — home,
users, categories, products, customers, sales, create-sale, edit-sale, sale-detail,
invoices, create-invoice, edit-invoice, invoice-detail, quotations, create-quotation,
edit-quotation, quotation-detail, customer-statement, accounting, expenses,
chart-of-accounts, settings, reports, report-*, organizations, org-currencies,
company-profile, currencies, sa-profile.

Excluding dead files cuts roughly 8 files and a meaningful slice of the raw counts.

---

## 11. Risk Assessment

| Area | Risk | Why |
|---|---|---|
| AdminLTE shell | **Critical** | No BS5 build of v2.4.0; shell breaks on naive core swap |
| Modals (forms/AJAX) | **High** | 255 markup hits; forms submit inside modals across every CRUD module |
| Grid `col-xs-*` | Medium | 76 hits; layout shifts if infix not handled |
| DataTables styling | Medium | needs BS5 styling build |
| daterangepicker / iCheck popups | Low–Medium | third-party class drift |
| Utilities (`pull-*`, `img-responsive`, etc.) | Low | covered by compat CSS |
| Icons / Glyphicons | Low | only login.php |
| Tabs | None | not used |

---

## 12. Proposed Phase Plan (after the §13 decision)

1. **Phase 2 — Compatibility layer:** add `views/dist/css/bootstrap3-compat.css` mapping
   residual BS3 utilities to BS5 so 54 files keep rendering without edits. Load it after BS5.
2. **Phase 3 — Shell** (per §13 choice): get sidebar/navbar/`content-wrapper`/`box` rendering
   under BS5; preserve the CSS-variable theme engine.
3. **Phase 4 — Modals:** markup → `data-bs-*`; add `$.fn.modal` jQuery shim so existing
   `.modal('show')` calls and inline triggers keep working; verify AJAX submit + validation.
4. **Phase 5 — Forms:** `input-group-addon`→`input-group-text`, `help-block`→`form-text`
   (compat covers visuals first), `has-feedback` in `login.php`.
5. **Phase 6 — Tables:** swap to `datatables.net-bs5`; verify load + AJAX + responsive.
6. **Phase 7 — Grid:** `col-xs-*`→`col-*` where compat can't fully cover.
7. **Phase 8 — Badges/buttons:** `label-*`→`badge text-bg-*`, `btn-default`→`btn-secondary`.
8. **Phase 9 — Icons + cleanup:** Glyphicons→FA in login; optional FastClick removal.

Each phase: validate the test matrix (login→reports), document per-file change blocks
(FILE/Reason/Changes/Risk/Rollback), and keep the app deployable.

---

## 13. DECISION REQUIRED before Phase 2

How should we handle the AdminLTE 2.4.0 shell? This determines everything downstream.

- **Option A — Custom thin BS5 shell + compat layer (recommended).** Replace AdminLTE 2's
  CSS/JS with a small hand-written BS5 layout for navbar + sidebar + content area, migrate
  `box`→`card`, and lean on `bootstrap3-compat.css` for the rest. Keeps our existing
  CSS-variable theme engine and sidebar structure largely intact. Most predictable, fully
  BS5, no third-party shell lock-in. Largest single piece of work is the shell, but it's
  contained to `header.php`/`sidebar.php`/`template.php` + the compat CSS.
- **Option B — Adopt AdminLTE 4 (BS5-native).** Pull in AdminLTE 4 and rewrite the 54
  layout files to its new markup (cards, new sidebar). "Official" path but a much larger
  rewrite of view markup and re-theming; closest to a redesign (conflicts with "do not
  redesign").
- **Option C — Keep AdminLTE 2, swap only Bootstrap core + heavy compat CSS.** Least file
  churn, but AdminLTE 2's own CSS uses BS3 classes/mixins internally — high chance of
  subtle visual breakage in the chrome; not a clean "BS5 UI." Lowest fidelity.

**Recommendation: Option A** — best balance of "true Bootstrap 5", "no redesign", preserved
theming, and deployability. Phase 2 (compat layer) is identical work under A or C and can
start immediately once chosen.

---

*End of Phase 1. No application code has been modified. Awaiting the §13 decision before
proceeding to Phase 2.*
