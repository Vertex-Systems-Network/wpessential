# Frontend Dashboard — Native WordPress Audit V1

Surface: **13 / Frontend Dashboard**  
Issue: **#689**  
Parent: **#686**  
Exact-main audit anchor: `3839142cc8a225f443fcf9f6e0c26eb9641742e7`  
Seed: `config/product/options-bank/dashboard.json` — **15 records / BANK_SURFACE_SEEDED**

## Decision

**NATIVE AUDIT EVIDENCE COMPLETE WITH TWO ROUTING/RENDER FAMILIES REQUIRED.** No lifecycle promotion occurs in this worker branch.

WordPress supplies rewrite/query routing, authenticated-session helpers and capability checks, but no canonical “frontend account dashboard” product engine. A WPE dashboard is therefore a shell/composition layer over native routing/auth plus canonical Profile, Query, Listings, Forms, Membership, Documents and other resource owners.

## Current native sources

- `add_rewrite_endpoint()` — https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
- `flush_rewrite_rules()` — https://developer.wordpress.org/reference/functions/flush_rewrite_rules/
- `auth_redirect()` — https://developer.wordpress.org/reference/functions/auth_redirect/
- `is_user_logged_in()` — https://developer.wordpress.org/reference/functions/is_user_logged_in/
- `current_user_can()` — https://developer.wordpress.org/reference/functions/current_user_can/
- `template_redirect` — https://developer.wordpress.org/reference/hooks/template_redirect/
- `template_include` — https://developer.wordpress.org/reference/hooks/template_include/
- accepted planning evidence: `docs/PRODUCT/FRONTEND-DASHBOARD-NATIVE-MARKET-EVIDENCE-MATRIX-V1.md`

Rewrite endpoints create query-var/rule state; they require a bounded registration/flush lifecycle. Authentication redirects protect shell entry but do not authorize endpoint operations.

## Seed-record disposition

| Seed record | Native disposition | Integration action |
|---|---|---|
| `dashboard.identity.definition` | **WPE PRODUCT SEMANTIC** | no native claim; retain stable Definition lifecycle |
| `dashboard.identity.route` | **NATIVE-CONFIRMED SUBSTRATE** | attach rewrite endpoint/query-var provenance and collision diagnostics |
| `dashboard.access.login` | **NATIVE-CONFIRMED** | map to authenticated-session helpers without creating a private auth engine |
| `dashboard.access.policy` | **NATIVE CAPABILITY SUBSTRATE + CROSS-OWNER REFERENCES** | capability check is native; Membership/Role truth remains delegated |
| `dashboard.access.unauthorized` | **NATIVE-ADJACENT / WPE POLICY** | redirect is native UX substrate; denied-operation policy remains WPE/Policy-owned |
| `dashboard.navigation.structure` | **WPE/MARKET-ONLY** | no native product claim |
| `dashboard.navigation.visibility` | **WPE PRESENTATION + POLICY REFERENCE** | hiding nav cannot substitute for authorization |
| `dashboard.navigation.mobile` | **WPE PRESENTATION** | no native promotion |
| `dashboard.endpoint.account-profile` | **CROSS-SURFACE REFERENCE** | Profile/account semantics remain Profile/Auth owners |
| `dashboard.endpoint.content` | **CROSS-SURFACE REFERENCE** | user-content mutation/query remains canonical content owner |
| `dashboard.endpoint.services` | **CROSS-SURFACE REFERENCE** | Forms/Membership/Notifications/Documents semantics are referenced only |
| `dashboard.content.crud-owner` | **WPE OWNERSHIP CONTRACT** | preserve no-bypass mapping; not a native CRUD engine |
| `dashboard.presentation.states` | **WPE UX QUALITY** | no native claim |
| `dashboard.presentation.accessibility` | **WPE QUALITY REQUIREMENT** | keep mandatory semantics; no fake core dashboard API claim |
| `dashboard.portability.diagnostics` | **WPE/IMPORT-EXPORT DIAGNOSTIC** | retain definition-level portability only |

## Missing native families to add during integration

### 1. `dashboard.route.rewrite-lifecycle`

Model native endpoint registration/collision/flush truth explicitly:

- endpoint name/query-var;
- endpoint placement mask / route scope;
- collision diagnostics;
- activation/config-change flush requirement;
- prohibition on flushing rewrite rules every request.

Recommended classification: `NATIVE_HARD`, `HARD`, `CURRENT_NATIVE`.

### 2. `dashboard.render.template-handoff`

Model the bounded handoff from matched route/query state to a registered WPE template/render provider. This is a **registered provider/reference**, never an authored PHP callback/path.

Recommended classification: `SOFT_NATIVE`, `HYBRID`, `CURRENT_NATIVE`.

## Required Supervisor Bank integration

1. Populate `snapshot.native_sources` with rewrite/auth/template sources.
2. Add `route.rewrite-lifecycle` and `render.template-handoff` families.
3. Add native provenance to route/login/policy records.
4. Preserve all endpoint/service/content records as references to canonical owners; no duplicate Profile/CRUD/Membership engine.
5. Keep navigation/presentation/portability WPE-owned.

## Native completeness / unresolved items

After the two routing/render additions, no known core WordPress family remains missing for this V1 dashboard shell. Multisite route scope, exact Policy behavior, accessibility testing and template runtime remain later gates.

## Gate boundary

Worker conclusion: **native evidence complete; two Bank additions + integration pending**.  
Not promoted here: `NATIVE_AUDITED`, market review, `BANK_REVIEWED`, Atomic Option Contract, UX or runtime/product certification.