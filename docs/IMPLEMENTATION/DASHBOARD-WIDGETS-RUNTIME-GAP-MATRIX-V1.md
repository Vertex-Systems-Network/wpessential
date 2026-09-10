# Dashboard Widgets — Runtime Gap Matrix V1

Surface: **10 / Dashboard Widgets**  
Issue: **#493**  
Status: planning evidence only; runtime implementation not authorized.

## Audited baseline

On the audited `main` baseline, `frameworks/Modules` has no dedicated canonical `DashboardWidgets` module directory. Therefore this matrix records **NO IMPLEMENTATION BASELINE** for Surface 10 rather than inventing partial runtime completion.

Existing shared platform capabilities that a future Surface 10 implementation should compose instead of duplicate include canonical Definition/persistence patterns, Policy/Ability authorization, AJAX/nonce handling, Query/Listings owners, diagnostics, audit and Multisite scoping.

## Required runtime families

| Family | Current Surface 10 runtime state | Required future evidence |
|---|---|---|
| Definition identity/lifecycle | MISSING | Typed Definition, revisions/CAS, status lifecycle, site/network scope |
| Widget registration/admin dashboard bridge | MISSING | Canonical registration owner and WordPress dashboard integration tests |
| Widget type registry | MISSING | Allowlisted built-ins/providers; no executable authored callbacks |
| Visibility policy | MISSING | Capability/role/user/condition policy with server authority |
| Presentation/layout | MISSING | Context/order/dimensions/collapse/dismiss semantics |
| Data/query bindings | MISSING | References to canonical Query/Listings/Analytics owners; no duplicate engines |
| Refresh/cache/stale handling | MISSING | TTL/scope/invalidation/retry semantics and performance evidence |
| User preferences | MISSING | Per-user reorder/hide/dismiss/reset with Multisite precedence |
| Ability-backed actions | MISSING | Policy/nonce/confirmation/audit with no UI-only authorization |
| Import/export | MISSING | Versioned definition portability and conflict diagnostics |
| Accessibility | MISSING | Browser/axe/keyboard/focus evidence |
| Performance | MISSING | Query budget, no N+1, cache evidence, large-dashboard behavior |

## Explicit non-gaps owned elsewhere

Surface 10 must reference rather than absorb:

- Query execution → Surface 6 Query.
- Listing rendering → Surface 9 Listings.
- analytics/journey semantics → Surface 33 Analytics & Journeys.
- action semantics → canonical Ability owner / target business surface.
- role grants → Surface 30 Roles & Capabilities.
- form runtime → Surface 17 Forms & Workflows.

## Certification boundary

A future green generic CI run alone cannot certify Surface 10. Runtime certification requires the accepted machine contract and UX contract plus exact-head unit/integration/WordPress runtime/browser/accessibility/security/Multisite/compatibility/performance evidence for the implemented families.

No line in this matrix authorizes runtime work, deployment, `RUNTIME_CERTIFIED`, or `PRODUCT_PARITY_CERTIFIED`.
