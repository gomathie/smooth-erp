# Icon Migration Map (Glyphicons → Font Awesome)

**Date:** 2026-06-10
**Status:** Plan only. No icon markup changed yet (executed in Phase 9).

## Summary

Bootstrap 3 Glyphicons are removed in Bootstrap 4/5. This codebase is **already almost
entirely Font Awesome 4.7.0** (`fa fa-*`) plus Ionicons. The **only** Glyphicon usage is in
`views/modules/login.php` — **5 instances**, all as Bootstrap form-feedback icons.

Because Font Awesome 4.7.0 is already loaded globally (`views/template.php` head) and is
framework-independent, the migration is a direct class swap. **No new icon library is
required.** (Bootstrap Icons is a viable alternative but unnecessary here — staying on Font
Awesome avoids adding a dependency and keeps every other icon in the app consistent.)

## Decision

- **Target library:** Font Awesome 4.7.0 (already present). No Bootstrap Icons added.
- Replace `glyphicon glyphicon-*` with the Font Awesome equivalent, preserving visual meaning.
- The BS3 `.form-control-feedback` positioning helper is also dropped in BS5; the feedback
  icons in `login.php` will be re-housed using an input-group/positioned icon during the
  Phase 5 (Forms) / Phase 9 work so the lock/user/envelope glyphs still sit inside the field.

## Mapping table (all occurrences)

| File | Line | Current (BS3) | Replacement (FA 4.7) | Visual meaning |
|---|---|---|---|---|
| `views/modules/login.php` | 27 | `glyphicon glyphicon-envelope form-control-feedback` | `fa fa-envelope` | email/identifier field |
| `views/modules/login.php` | 60 | `glyphicon glyphicon-lock form-control-feedback` | `fa fa-lock` | new password field |
| `views/modules/login.php` | 68 | `glyphicon glyphicon-lock form-control-feedback` | `fa fa-lock` | confirm password field |
| `views/modules/login.php` | 100 | `glyphicon glyphicon-user form-control-feedback` | `fa fa-user` | username field |
| `views/modules/login.php` | 108 | `glyphicon glyphicon-lock form-control-feedback` | `fa fa-lock` | password field |

## General Glyphicon → Font Awesome reference (for any future additions)

| Glyphicon | Font Awesome 4.7 |
|---|---|
| `glyphicon-envelope` | `fa fa-envelope` |
| `glyphicon-user` | `fa fa-user` |
| `glyphicon-lock` | `fa fa-lock` |
| `glyphicon-ok` | `fa fa-check` |
| `glyphicon-remove` | `fa fa-times` |
| `glyphicon-search` | `fa fa-search` |
| `glyphicon-cog` | `fa fa-cog` |
| `glyphicon-trash` | `fa fa-trash` |
| `glyphicon-pencil` | `fa fa-pencil` |
| `glyphicon-plus` | `fa fa-plus` |

## Risk

- **Risk level: Low.** Single file, presentational only, no logic. Font Awesome already loaded.
- **Rollback:** revert `views/modules/login.php` (the only affected file).
