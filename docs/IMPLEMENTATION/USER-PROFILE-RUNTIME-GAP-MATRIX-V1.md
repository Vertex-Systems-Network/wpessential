# User Profile — Runtime Gap Matrix V1

Surface: **14 / User Profile**  
Issue: **#497**  
Runtime authorization: **false**.

| Family | Planning state | Runtime gap | Evidence required before runtime promotion |
|---|---|---|---|
| Profile fields | Seeded | No canonical profile Definition/registered field composition | unit + integration + compatibility |
| Field privacy/editability | Seeded | No server-side field-level policy/effective resolver | security + WordPress runtime |
| Public slug | Seeded | No canonical slug/rewrite/collision service | integration + multisite |
| Public visibility | Seeded | No resource visibility evaluator | security + browser |
| Registration policy | Seeded | No verification/approval state machine | security + integration + recovery |
| Authentication surface | Seeded | No safe adapter to canonical auth flows | security + compatibility |
| Directory query | Seeded | No privacy-aware bounded query/pagination adapter | performance + security + browser |
| Portability | Seeded | No versioned export/import/redaction/remapping path | portability + compatibility |

## Cross-cutting requirements

**Security/privacy:** resource-level authorization, CSRF protection, privacy-safe defaults, and no password/token/session export.  
**Multisite:** explicit site/network/user scope; no cross-site profile leakage.  
**Accessibility:** semantic form labels, error summaries, keyboard support, visible focus and announced async states.  
**Compatibility:** preserve WordPress user/profile lifecycle and tolerate missing custom-field/auth providers safely.  
**Performance:** bounded/paginated directories and no avoidable N+1 user-meta access.  
**Portability:** definitions only, with sensitive values redacted and environment/provider references remapped explicitly.

Runtime implementation, destructive user mutation, deployment/release and certification claims remain forbidden until a later exact-main gate.