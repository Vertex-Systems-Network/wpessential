# Settings Pages — Runtime Gap Matrix V1

Surface: **12 / Settings Pages**  
Issue: **#495**  
Runtime authorization: **false**.

| Family | Planning state | Runtime gap | Evidence required before runtime promotion |
|---|---|---|---|
| Page identity | Seeded | No canonical Settings Page Definition/runtime registration | unit + WordPress runtime |
| Scope | Seeded | No explicit site/network/user scope resolver | security + multisite |
| Structure | Seeded | No canonical tabs/sections/panels renderer | browser + accessibility |
| Storage mode | Seeded | No typed persistence adapter across option/network/user-meta owners | integration + compatibility |
| Autoload | Seeded | No guarded expert autoload policy/effective diagnostics | performance + WordPress runtime |
| Inheritance | Seeded | No deterministic authored/default/effective resolver | unit + integration |
| Controls registry | Seeded | No composition adapter to canonical Fields/Control Registry | compatibility + browser |
| Secrets | Seeded | No Vault-reference settings adapter/export redaction | security + portability |

## Cross-cutting requirements

**Security:** server-authoritative capability/Policy checks and sanitization; no secret values in frontend/bootstrap/export payloads.  
**Multisite:** site/network/user scopes are explicit and isolated; network changes require network capability.  
**Accessibility:** semantic sections/tabs, keyboard navigation, visible focus, error/status announcements.  
**Compatibility:** tolerate missing/deactivated control providers and preserve native option semantics.  
**Performance:** bounded payloads, deliberate autoload behavior, no global optional-module assets.  
**Portability:** export portable definitions and Vault references only; environment-sensitive values require explicit remapping.

## Explicitly out of scope

Runtime implementation, destructive setting migration, deployment/release, and any `RUNTIME_CERTIFIED` / `PRODUCT_PARITY_CERTIFIED` claim remain forbidden in this lane.