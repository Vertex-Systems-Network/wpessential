# Builder Widgets — Runtime Gap Matrix V1

Surface: **16 / Builder Widgets**  
Issue: **#499**  
Runtime authorization: **false**.

| Family | Planning state | Runtime gap | Evidence required before runtime promotion |
|---|---|---|---|
| Blueprint identity | Seeded | No canonical component Blueprint Definition/revision service | unit + integration + portability |
| Controls schema | Seeded | No canonical Control Registry composition adapter | compatibility + browser |
| Render mode | Seeded | No allowlisted server/client render-provider registry | security + integration |
| Dynamic bindings | Seeded | No typed context/reference resolver across canonical owners | security + compatibility + performance |
| Style bindings | Seeded | No validated token/responsive style compiler | security + browser + accessibility |
| Adapter registry | Seeded | No builder adapter registration/health layer | compatibility + integration |
| Parity diagnostics | Seeded | No deterministic active-adapter capability diff | unit + compatibility |
| Portability | Seeded | No versioned blueprint import/export/remapping path | portability + migration |

## Cross-cutting requirements

**Security:** allowlisted provider IDs only; reject arbitrary PHP/JS/callback/class/template/CSS execution input.  
**Multisite:** explicit site/network blueprint scope and environment-specific provider availability.  
**Accessibility:** generated controls/preview surfaces preserve semantic labels, keyboard access, visible focus and responsive readability.  
**Compatibility:** adapters are optional and isolated; missing/deactivated builders fail safely without deleting definitions.  
**Performance:** bounded render/binding resolution, no N+1 provider lookups and no global optional assets.  
**Portability:** versioned definitions with stable IDs; environment-specific adapters/references require explicit remapping.

Runtime adapter registration, runtime implementation, deployment/release and certification claims remain forbidden until a later exact-main gate.