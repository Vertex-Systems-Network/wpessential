# Admin Columns Gate D Baseline Audit V1

Status: **ACTIVE / NOT PASS**  
Parent tracker: GitHub Issue #66  
Audit issue: GitHub Issue #193  
Audit anchor: `main @ a380fe6dd249e7e37491629623a5f6b33bd6a146`

This document audits Surface 8 Admin Columns against the Gate D baseline after Surface 6 Query Gate C passed. It is evidence-only. It does not authorize Gate E, Status runtime, release/deployment, product-parity certification or any private-owner bypass.

## Entry evidence

The dependency entry gate is satisfied:

- Query Gate C is **PASS FOR CERTIFIED BOUNDED V1 BASELINE** at the audit anchor. Query remains the owner of backend sort/filter/search semantics.
- Admin Columns Master Options Bank is **BANK_REVIEWED / 214**.
- `config/product/option-contracts/columns.json` is **UX_CONTRACT_COMPLETE** and projects all **214 / 214** Bank records to **41** canonical Atomic Options with `missing = 0` and `unclassified = 0`.
- `docs/PRODUCT/ADMIN-COLUMNS-UX-CONTRACT-V1.md` defines the reviewed module IA, authored/personal/effective/diagnostic state separation, ownership boundaries and safety/accessibility/performance expectations.
- PR #190 promoted shared lifecycle truth without claiming runtime or product parity.

This is sufficient to begin Gate D implementation, but it is not sufficient to mark Gate D complete.

## Gate D decision

Gate D remains **ACTIVE / NOT PASS**.

There is no `frameworks/Modules/AdminColumns` runtime boundary on the audit anchor and no Admin Columns-specific admin-ui source. The reviewed product/UX contracts therefore describe required behavior that still needs bounded implementation and exact-head evidence.

## Criterion matrix

| Gate D requirement | Audit status | Current evidence | Required implementation / closure evidence |
| --- | --- | --- | --- |
| Atomic Option + UX lifecycle integration | **CERTIFIED CONTRACT** | 214/214 Bank mapping, 41 Atomic Options, `UX_CONTRACT_COMPLETE`, shared progress promoted by PR #190. | Runtime must consume this contract without reinterpreting 214 Bank records as 214 authored controls. Do not advance runtime/product-parity lifecycle until independent gates pass. |
| Canonical View / Column Set definition lifecycle | **BLOCKED — IMPLEMENTATION MISSING** | UX contract defines shared revisioned View identity, target, ordered columns, assignment/layout and authored-state boundaries. | Issue #191 must establish the Surface 8-owned typed Definition/module foundation with stable identities, deterministic validation and fail-closed lifecycle semantics. |
| Admin authoring UX | **BLOCKED — IMPLEMENTATION MISSING** | Reviewed UX specifies Column Sets, Segments, Adapters, Diagnostics; collection/editor states; authored vs Personal vs effective vs diagnostic separation. | Issue #192 must add the accessible non-runtime scaffold. Canonical WordPress route/bootstrap/build integration is a later serialized tranche after PHP/UI contracts exist. |
| Query-owned backend sort/filter/search | **DEPENDENCY READY; ADAPTER BLOCKED** | Query Gate C provides bounded Policy-authorized planning/execution and owner-backed Relation/Field predicates. Surface 8 contract explicitly says Query owns backend query semantics. | Admin Columns must add a Surface 8-owned adapter that consumes public Query seams. It must not create SQL, `WP_Query`, `meta_query`, private Query AST shortcuts or another query engine. Queue slot remains blocked until #191 foundation is promoted. |
| Source-owner validation for inline/bulk mutation | **BLOCKED — IMPLEMENTATION MISSING** | Fields already exposes canonical target/value validation/authorization for its certified storage paths; Relations and other owners retain their own mutation semantics. UX contract requires capability/degraded-state presentation. | Later mutation tranche must route writes through source-owner public contracts/Abilities and Policy. No direct post meta, private relation tables, taxonomy/private provider writes or visibility-as-auth shortcuts. |
| Visibility and assignment safety | **CONTRACT CERTIFIED; RUNTIME MISSING** | Atomic/UX contracts state visibility is presentation-only and Policy remains authorization. | Runtime resolver must never treat hidden/visible columns, View assignment or UI availability as an authorization decision. Add negative tests proving Policy/source-owner denial wins. |
| Primary column / row actions | **CONTRACT CERTIFIED; RUNTIME MISSING** | UX requires explicit primary-column resolution and row-action policy constrained by target adapter. | Target adapter must validate primary-column eligibility and preserve WordPress/list-table action semantics; degraded/invalid primary state must fail closed or fall back only through an explicitly certified rule. |
| Export safety | **BLOCKED — IMPLEMENTATION MISSING** | UX/Atomic contract requires export scope/security, redaction and CSV formula-injection protection. | Separate export tranche must authorize source fields/rows, preserve scope, escape spreadsheet formulas, avoid secrets/private diagnostics and prove bounded memory/row behavior. |
| Performance / no-N+1 | **BLOCKED — IMPLEMENTATION MISSING** | UX requires batching/lazy delivery, DB/remote-call diagnostics and expensive-source warnings. Query/Relations/Fields already expose bounded upstream contracts for their certified slices. | Adapter/runtime evidence must prove bounded source calls per page, batching where owner supports it, no per-cell uncontrolled remote/database fan-out, page-size ceilings and deterministic diagnostics. |
| Accessibility / responsive / keyboard behavior | **CONTRACT CERTIFIED; UI EVIDENCE MISSING** | UX defines keyboard reorder alternatives, labels, focus/error/loading/recovery states and responsive behavior. | #192 and later integrated admin route must pass exact-head Browser E2E Accessibility evidence; runtime-only CI cannot substitute for browser evidence. |
| Personal preference separation | **CONTRACT CERTIFIED; RUNTIME MISSING** | UX says chosen View, temporary sort/filter, personal visibility/density are user-scoped and must not mutate shared View definitions. | Implement as a separate user-preference boundary after the shared Definition foundation; do not embed personal state in revisioned shared definitions. |
| Effective / diagnostic state separation | **CONTRACT CERTIFIED; RUNTIME MISSING** | UX classifies effective capabilities and diagnostics as read-only derived state. | Runtime projection must keep effective/diagnostic values out of authored storage and must authorize any sensitive diagnostic exposure. |
| Portability / multisite | **CONTRACT CERTIFIED; RUNTIME MISSING** | Atomic options mark revisioned definitions portable with environment-sensitive typed references where appropriate; site/user scope comes from trusted context. | Portability must preserve stable IDs, explicitly remap environment-sensitive refs, reject ambiguous/foreign owner refs and derive site/network/user scope from trusted execution context. |
| Woo/provider compatibility | **CONTRACT CERTIFIED; ADAPTER EVIDENCE MISSING** | UX/contract prohibit private storage assumptions and require supported provider/storage APIs, including Woo compatibility states. | Provider adapters must declare capabilities/degraded states and use supported APIs (including HPOS-safe paths where applicable); unsupported adapters fail closed. |
| Arbitrary executable configuration | **CERTIFIED PROHIBITION** | Atomic + UX contracts prohibit arbitrary PHP/JavaScript configuration and code-editor fallback. | Runtime/admin UI must retain this prohibition; structured formatting/render/source descriptors only. |
| Exact-head certification | **PENDING PER SLICE** | Upstream Query and product-contract slices are exact-head certified. | Each Gate D implementation tranche must pass its applicable exact-head Architecture/PHP/Matrix/Package/Browser or dedicated integration gates with clean review/thread state. |

## Canonical ownership rules

Gate D implementation must preserve these boundaries:

1. **Admin Columns owns presentation definitions**, View/Column identity, ordering within a View, presentation format policy and list-table UX composition.
2. **Query owns backend sort/filter/search composition and execution semantics.** Admin Columns may translate a certified View/Segment into typed Query input through a public adapter; it may not own a parallel query engine.
3. **Fields, Relations, Taxonomy, Status, Media, Tables and provider adapters own their source truth and mutation validation.** Surface 8 stores typed references, not peer-private keys/tables/callbacks.
4. **Policy owns authorization.** View assignment, column visibility, disabled controls and capability badges are presentation/effective state, never authorization grants.
5. **Content Order owns persistent manual content order.** Reordering columns changes presentation order only.
6. **Renderer/source adapters own value rendering contracts.** Arbitrary executable PHP/JavaScript is prohibited.

## Required state separation

The reviewed UX defines five classes that must not collapse into one persistence model:

- **Authored shared definition** — revisioned Surface 8 View/Column configuration.
- **Personal preference** — user-scoped selected View, temporary sort/filter, visibility/density.
- **Effective runtime state** — read-only capability/provider/primary/lazy outcomes.
- **Diagnostic state** — read-only bounded support/performance evidence.
- **Deferred/prohibited state** — unavailable expert/unsafe semantics, never silently normalized into ordinary controls.

The first core runtime tranche should implement only the authored shared definition foundation. Personal/effective/diagnostic storage/exposure need their own later contracts.

## Initial conflict-free work lanes

Three current lanes can proceed in parallel because their write boundaries do not overlap:

1. **#191 — runtime definition/module foundation V1**: new `frameworks/Modules/AdminColumns` PHP + Surface 8 unit evidence only.
2. **#192 — non-runtime authoring scaffold V1**: new Admin Columns `admin-ui` + browser/accessibility evidence only; no PHP/shared build wiring.
3. **#193 — this audit**: one documentation file only.

The Query-backed read adapter must remain blocked until #191 provides stable typed seams.

## Later serialized / parallel tranches

After the foundation is promoted, split remaining work by exclusive ownership rather than building one giant writer:

- Query-backed read/sort/filter/search adapter;
- source-owner inline/bulk mutation adapters;
- export security pipeline;
- personal preference store/resolver;
- target/primary-row-action adapter contract;
- batching/lazy/performance + bounded diagnostics evidence;
- portability/import-export reference remapping;
- canonical admin route/bootstrap/build integration as a serialized PHP/admin-build writer;
- real-WordPress/list-table and accessibility reference evidence;
- final Gate D closure audit and checkpoint/queue reconciliation.

These lanes may run concurrently only where file ownership and semantic ownership are disjoint.

## Gate D exit rule

Gate D may pass only after current `main` proves, for the declared bounded baseline:

- a canonical revisioned View/Column definition runtime;
- Query-owned backend sort/filter/search integration without a shadow query engine;
- source-owner + Policy-authorized mutation behavior for supported inline/bulk paths;
- visibility/assignment never bypass authorization;
- safe primary/row actions and export behavior;
- deterministic no-N+1/bounded performance evidence;
- accessible responsive admin integration;
- explicit personal/effective/diagnostic state separation;
- portability/multisite/provider boundaries for supported adapters;
- all applicable exact-head CI and a final exact-main criterion audit;
- `CHECKPOINT.md`, coordination queue and parent #66 synchronized to the same truth.

Until then: **Gate D ACTIVE / NOT PASS; Gate E Dynamic Listings and Status runtime remain blocked.**
