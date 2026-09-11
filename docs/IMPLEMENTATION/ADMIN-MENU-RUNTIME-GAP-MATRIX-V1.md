# Admin Menu — Runtime Gap Matrix V1

Surface: **11 / Admin Menu**  
Issue: **#494**  
Runtime authorization: **false**.

| Family | Planning state | Runtime gap | Evidence required before runtime promotion |
|---|---|---|---|
| Target identity | Seeded | No canonical Definition/runtime adapter | unit + WordPress runtime + multisite |
| Presentation transform | Seeded | No deterministic transform/apply layer | unit + integration + browser |
| Visibility policy | Seeded | No server-authoritative targeting evaluator | security + integration + multisite |
| Custom item | Seeded | No validated route/provider registration path | security + compatibility |
| Profile assignment | Seeded | No revisioned profile/assignment persistence | unit + portability + migration |
| Admin bar | Seeded | No typed node reconciliation layer | WordPress runtime + compatibility |
| Preview actor | Seeded | No redacted effective-menu diagnostic | security + browser + accessibility |
| Restore/recovery | Seeded | No revision/reset recovery service | unit + integration + recovery |

## Cross-cutting requirements

**Security:** capability/Policy checks are server authoritative; hiding navigation never authorizes access; reject arbitrary PHP/callback/class/script/HTML execution.  
**Multisite:** site, user-admin and network-admin graphs remain distinct; network overrides require explicit authorization.  
**Accessibility:** semantic controls, keyboard operation, visible focus, status announcements and no color-only state.  
**Compatibility:** preserve native menu slugs/hooks and tolerate missing/deactivated third-party targets without fatal failure.  
**Performance:** bounded transforms; no repeated unbounded menu scans; surface assets load only where needed.  
**Portability:** export definitions, not environment-specific executable references; unresolved targets import as safe warnings.

## Explicitly out of scope

- runtime implementation;
- destructive menu or authorization mutation;
- deployment/release;
- `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED` claims.

A later exact-main Supervisor gate must authorize any runtime lane after Bank and canonical option-contract review.