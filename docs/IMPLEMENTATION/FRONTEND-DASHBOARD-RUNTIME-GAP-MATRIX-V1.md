# Frontend Dashboard — Runtime Gap Matrix V1

Surface: **13 / Frontend Dashboard**  
Issue: **#496**  
Runtime authorization: **false**.

| Family | Planning state | Runtime gap | Evidence required before runtime promotion |
|---|---|---|---|
| Route/host identity | Seeded | No canonical dashboard route Definition/router | unit + integration + collision tests |
| Access policy | Seeded | No composed server-side login/role/membership/capability evaluator | security + WordPress runtime |
| Navigation tree | Seeded | No typed endpoint/navigation renderer | browser + accessibility |
| Endpoint type | Seeded | No registered provider/endpoint adapter | compatibility + integration |
| Content ownership | Seeded | No resource-level ownership policy layer | security + REST/integration |
| Responsive layout | Seeded | No canonical responsive region renderer | browser + accessibility |
| Integration references | Seeded | No stable cross-surface reference resolver/health state | compatibility + portability |
| Accessibility navigation | Seeded | No packaged landmark/focus/keyboard evidence | browser + accessibility |

## Cross-cutting requirements

**Security:** server-authoritative Policy/Ability/resource checks, CSRF protection, no client-only authorization.  
**Multisite:** site/network scope and user visibility are explicit; prevent cross-site data leakage.  
**Accessibility:** landmarks, skip links, current-page semantics, logical focus order and visible focus.  
**Compatibility:** missing referenced modules/providers fail safely and never fatal the dashboard.  
**Performance:** lazy-load/paginate bounded endpoint data and isolate optional assets.  
**Portability:** stable IDs and remapping diagnostics for environment-specific routes/providers.

Runtime code, deployment/release and certification claims remain forbidden until a later exact-main gate.