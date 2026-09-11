# WPEssential

WPEssential is a modular WordPress application platform for structured data, automation, integrations, admin tooling, workflows, and AI-ready operations.

Project website: **https://wpessential.org**

> **Status:** Source development is active under explicit `GOV-OWNER-CONSENT-001` and remains milestone-gated. Production deployment, destructive live-provider operations, full runtime certification and product-parity certification are separate gates.

## Current lifecycle

- Accepted product scope: **56/56 Exhaustive**
- Multisite planning: **56/56**
- AI Prompt planning: **56/56**
- Planning closure/integration authority: through **ADR-0213**
- Implementation Baseline: **WP119 / ADR-0214 PASS**
- Machine-enforced architecture guards: **WP120 / ADR-0215 PASS**
- Platform Foundation: **WP121 DONE / PASS FOR MODULE HANDOFF**
- Owner engineering contract: **ADR-0216**
- Atomic compiled-registration persistence: **ADR-0217**
- Definition + Audit persistence: **ADR-0218**
- WordPress.org metadata + direct-access security: **ADR-0219**
- Real WordPress AJAX/nonce/Policy integration: **ADR-0220**
- Phase 2 Gate A / Fields: **PASS for the certified native V1 scope**
- Phase 2 Gate B / Relations: **PASS for the certified native V1 baseline**
- Phase 2 Gate C / Query: **PASS for the certified bounded V1 baseline**
- Phase 2 Gate D / Admin Columns: **PASS for the certified bounded V1 baseline**
- Phase 2 Gate E / Dynamic Listings: **PASS for the certified bounded V1 baseline**
- Status Manager: **PASS for the certified bounded V1 baseline** via Issue #378 / merged PR #379.
- Surface 7 / Custom Tables: **ACTIVE / NOT PASS — bounded runway 90%**, safe-paused with managed-table execution blocked after Issue #463 / merged PR #464 and Issue #465 / merged PR #466.
- Surface 1 / CPT Builder: **UX_CONTRACT_COMPLETE / NOT RUNTIME CERTIFIED** through Issue #473 / merged PR #477.
- Surface 2 / Taxonomy Builder: **UX_CONTRACT_COMPLETE / Runtime Gap Closure V1 ACTIVE**, with bounded Definition, provider-ID, diagnostics backend/admin rendering, adaptive hierarchy-aware label-generation, reviewed 28-label authoring UX, tiered visibility authoring, accessible Find Setting search, default-term + bounded object-term query authoring, and descriptor-redacted provider catalog health promoted through PRs #478, #479, #480, #483, #486, #489, #503, #506, #512 and #515; full runtime/product parity remains unpromoted.
- Surface 10 / Dashboard Widgets: **UX_CONTRACT_COMPLETE / NOT RUNTIME CERTIFIED** through Issue #493 / merged PR #508; reviewed 123-record Bank, 18 normalized contracts, deterministic 123/123 projection, reviewed UX contract and runtime gap matrix are promoted as planning truth only.
- Surfaces 11–18: **planning/contract wave ACTIVE only** under owner-directed Supervisor Issue #492 and Issues #494–#501; runtime implementation remains unpromoted for all eight surfaces.

README audited base anchor: `main @ d9204049a163bd79e41ad989af6d66c87e5388ae` on **2026-09-10 UTC**. CPT Options Bank closure is promoted through Issue #467 / merged PR #469 and CPT Atomic Option Contract + UX Closure through Issue #473 / merged PR #477. Taxonomy Atomic Option Contract + UX closure is promoted through Issue #468 / merged PR #470; bounded Runtime Gap Closure evidence is promoted through merged PRs #478, #479, #480, #483, #486, #489, #503, #506, #512 and #515 while Issue #474 remains open. Dashboard Widgets planning closure is promoted through Issue #493 / merged PR #508 and Supervisor reconciliation Issue #510 / merged PR #511 while runtime remains unpromoted. Issue #516 is the current Supervisor shared-truth reconciliation after PR #515; it does not promote lifecycle state. Issue #492 records the owner-directed ten-worker wave: Taxonomy remains the only runtime lane; Dashboard Widgets has completed its planning/contract gate; Surfaces 11–18 remain planning/contract-only. Repository, CI and machine-readable lifecycle files override this prose if it later becomes stale.

## Module implementation progress — 56 / 56 modules listed

This dashboard is mandatory AI-Native closeout truth. Every meaningful repository-changing engineering cycle must keep **all 56 canonical product surfaces** visible before the query/cycle is reported final.

Canonical surface names, numbers and ordering come from `docs/MODULES/CANONICAL-OPTION-OWNERSHIP-INDEX-56-SURFACES.md`. A missing or duplicated canonical surface makes closeout incomplete.

**Progress percentages below measure the currently approved/certified bounded implementation milestone for that module, not full product parity.** Planning lifecycle states are not implementation percentages. A 100% row means its explicitly named bounded/native baseline is certified; it does **not** imply `PRODUCT_PARITY_CERTIFIED`, deployment or release readiness.

Timeline fields follow `docs/AI-NATIVE-README-MODULE-TIMELINE-CLOSEOUT.md`. Timestamps use ISO-8601 UTC when repository evidence proves an exact value. Historical dates are never reconstructed from chat memory. Planning-only surfaces remain visible with explicit non-fabricated values.

| # | Module / Surface | Lifecycle / Status | Progress | Start date/time (UTC) | Estimated completion date/time (UTC) | Actual completion date/time (UTC) | Latest evidence | Next gate |
|---:|---|---|---|---|---|---|---|---|
| 1 | CPT Builder | PLANNED — UX_CONTRACT_COMPLETE; Options Bank BANK_REVIEWED (107); 23 atomic contracts + deterministic 107/107 projection; runtime baseline gap-audited / full-parity lifecycle not promoted | — / no full-parity percentage milestone | 2026-09-09T21:03:34Z | FORECAST PENDING / later exact-main CPT runtime transition audit required | — | Issue #473 / merged PR #477 + CPT contract/UX/runtime gap matrix | No CPT runtime-gap lane currently authorized; exact-main transition audit required |
| 2 | Taxonomy Builder | ACTIVE — UX_CONTRACT_COMPLETE; Runtime Gap Closure V1 in progress; Definition + provider-ID + diagnostics + adaptive labels + reviewed 28-label authoring + tiered visibility/inheritance UX + accessible Find Setting + default-term/bounded object-term query authoring + redacted provider catalog health promoted / full-parity lifecycle not promoted | — / no full-parity percentage milestone | 2026-09-09T21:03:50Z | FORECAST PENDING / remaining controlled provider selector + capability UX, rewrite/REST collision diagnostics, portability/compatibility, guarded migration planning and certification evidence | — | Issue #474 + merged PRs #478, #479, #480, #483, #486, #489, #503, #506, #512, #515; #515 exact head `b5753db7...` passed Architecture #1208, PHP Quality #635, Taxonomy Runtime #59, CPT Runtime #112, Platform Matrix #816 and Package #741 before merge `d9204049...` | Continue controlled provider selector/health using only registered catalog IDs, capability/effective-map and rewrite/REST collision diagnostics; then portability/CPT compatibility and separately gated key-migration planning |
| 3 | Fields | PASS — certified native V1 | `██████████ 100%` | UNKNOWN / pending evidence audit | — completed | UNKNOWN / pending evidence audit | Gate A | Broader provider/full parity remains gated |
| 4 | Relations | PASS — certified native V1 | `██████████ 100%` | UNKNOWN / pending evidence audit | — completed | UNKNOWN / pending evidence audit | Gate B | Richer provider/full parity remains gated |
| 5 | Status | PASS — certified bounded V1 | `██████████ 100%` | UNKNOWN / pending evidence audit | — completed | UNKNOWN / pending evidence audit | Issue #378 / PR #379 | Workflow/provider/bulk parity remains gated |
| 6 | Query | PASS — certified bounded V1 | `██████████ 100%` | UNKNOWN / pending evidence audit | — completed | UNKNOWN / pending evidence audit | Gate C | Public execution/full parity remains gated |
| 7 | Custom Tables | ACTIVE / NOT PASS | `█████████░ 90%` | UNKNOWN / pending evidence audit | FORECAST PENDING / safe-paused; execution trust activation not authorized | — | Issue #463 / merged PR #464 + Issue #465 / merged PR #466 | Managed-table execution remains blocked; later explicit trust-activation audit required |
| 8 | Admin Columns | PASS — certified bounded V1 | `██████████ 100%` | UNKNOWN / pending evidence audit | — completed | UNKNOWN / pending evidence audit | Gate D | No unbounded mass-edit/provider-wide parity claim |
| 9 | Listings | PASS — certified bounded V1 | `██████████ 100%` | UNKNOWN / pending evidence audit | — completed | UNKNOWN / pending evidence audit | Issue #343 / PR #344 | Richer async/builder parity remains gated |
| 10 | Dashboard Widgets | PLANNED — UX_CONTRACT_COMPLETE; Bank BANK_REVIEWED (123); 18 normalized Atomic Option Contracts + deterministic 123/123 projection; reviewed UX contract + runtime gap matrix / implementation baseline not promoted | — / no implementation baseline | 2026-09-10T19:46:54Z | FORECAST PENDING / later exact-main runtime transition audit required | — | Issue #493 / merged PR #508; exact reconciled head `ae041678...` passed Architecture Guards #1202 + Platform Compatibility Matrix #813 before squash merge `7e9c5af1...` | No Dashboard Widgets runtime lane currently authorized; later exact-main transition audit required |
| 11 | Admin Menu | PLANNED — ATOMIC_INVENTORY_COMPLETE; main Bank remains UNSEEDED; worker seed candidate ACTIVE / implementation baseline not promoted | — / no implementation baseline | 2026-09-10T19:47:04Z | FORECAST PENDING / native + market Bank review required | — | Issue #494; branch evidence head `ed2df4e1...` expands site/user/network menu and Admin Bar semantics over 8 normalized unreviewed candidate records | Continue native/market Bank expansion and semantic/policy resolution; no BANK_REVIEWED claim yet |
| 12 | Settings Pages | PLANNED — ATOMIC_INVENTORY_COMPLETE; main Bank remains UNSEEDED; worker seed candidate ACTIVE / implementation baseline not promoted | — / no implementation baseline | 2026-09-10T19:47:14Z | FORECAST PENDING / native + market Bank review required | — | Issue #495; branch evidence head `9642872f...` expands site/network/user persistence and sanitization evidence over 8 normalized unreviewed candidate records | Continue autoload/network-admin/privacy/market evidence; no BANK_REVIEWED claim yet |
| 13 | Frontend Dashboard | PLANNED — ATOMIC_INVENTORY_COMPLETE; main Bank remains UNSEEDED; worker seed candidate ACTIVE / implementation baseline not promoted | — / no implementation baseline | 2026-09-10T19:47:23Z | FORECAST PENDING / native + market Bank review required | — | Issue #496; branch evidence head `70c6bc4e...` expands native rewrite/auth and canonical-owner boundaries over 8 normalized unreviewed candidate records | Continue template/endpoint collision/login ownership + market evidence; no BANK_REVIEWED claim yet |
| 14 | User Profile | PLANNED — ATOMIC_INVENTORY_COMPLETE; main Bank remains UNSEEDED; worker seed candidate ACTIVE / implementation baseline not promoted | — / no implementation baseline | 2026-09-10T19:47:33Z | FORECAST PENDING / native + market Bank review required | — | Issue #497; branch evidence head `076f898f...` expands user-meta/profile lifecycle and authorization boundaries over 8 normalized unreviewed candidate records | Continue registration/privacy/auth/profile-routing market evidence; no BANK_REVIEWED claim yet |
| 15 | Membership | PLANNED — ATOMIC_INVENTORY_COMPLETE; main Bank remains UNSEEDED; worker seed candidate ACTIVE / implementation baseline not promoted | — / no implementation baseline | 2026-09-10T19:47:51Z | FORECAST PENDING / native/platform + market Bank review required | — | Issue #498; branch evidence head `5d6bab58...` expands plan/enrollment/entitlement/payment-state boundaries over 8 normalized unreviewed candidate records | Continue subscription/refund/tax/grace/entitlement evidence; payment execution remains unauthorized |
| 16 | Builder Widgets | PLANNED — ATOMIC_INVENTORY_COMPLETE; main Bank remains UNSEEDED; worker seed candidate ACTIVE / implementation baseline not promoted | — / no implementation baseline | 2026-09-10T19:48:05Z | FORECAST PENDING / WordPress + builder ecosystem Bank review required | — | Issue #499; branch evidence head `d5631be6...` expands block metadata/context/dynamic-render and adapter boundaries over 8 normalized unreviewed candidate records | Continue builder/provider coverage and safe style/asset evidence; no runtime adapter registration |
| 17 | Forms & Workflows | PLANNED — ATOMIC_INVENTORY_COMPLETE; main Bank remains UNSEEDED; worker seed candidate ACTIVE / implementation baseline not promoted | — / no implementation baseline | 2026-09-10T19:48:14Z | FORECAST PENDING / platform + forms/workflow market Bank review required | — | Issue #500; branch evidence head `11144f39...` expands nonce/auth, REST permission and submission lifecycle evidence over 8 normalized unreviewed candidate records | Continue anti-spam/multi-step/conditional/notification/provider evidence; workflow execution remains unauthorized |
| 18 | Cron | PLANNED — ATOMIC_INVENTORY_COMPLETE; main Bank remains UNSEEDED; worker seed candidate ACTIVE / implementation baseline not promoted | — / no implementation baseline | 2026-09-10T19:48:26Z | FORECAST PENDING / WP-Cron/provider + market Bank review required | — | Issue #501; branch evidence head `8421c401...` expands hook+args event identity, duplicate scheduling and timing/provider truth over 8 normalized unreviewed candidate records | Continue unscheduling/custom recurrence/Action Scheduler/system-cron evidence; never claim exact wall-clock execution |
| 19 | Notifications | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 20 | Emails | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 21 | Chat | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 22 | REST API | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 23 | Connections/Webhooks | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 24 | Backup | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 25 | Reset | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 26 | Import/Export | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 27 | Protector | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 28 | Media Operations | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 29 | XML-RPC | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 30 | Roles & Capabilities | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 31 | Platform | PLANNED — ATOMIC_INVENTORY_COMPLETE; shared foundation PASS for module handoff | — / no Surface 31 implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | WP121 + atomic option progress | Surface 31 implementation baseline requires separate authorization |
| 32 | Solution Blueprint Composer | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 33 | Analytics & Journeys | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 34 | Search & Indexing | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 35 | Decision/Formula/Scoring | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 36 | Ledger | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 37 | Reservations | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 38 | Placement/Personalization | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 39 | Experiments/Rollout | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 40 | Documents/Records | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 41 | Sync/ETL | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 42 | Geo/Territory | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 43 | AI Gateway/Copilot | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 44 | Redirect/Routing | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 45 | Search/Replace/Transform | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 46 | Dummy Data/Fixtures | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 47 | Link Health | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 48 | DB Maintenance | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 49 | Admin Theme | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 50 | Safe Script/Tag | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 51 | Content Order/Sequence | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 52 | Security Scanner | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 53 | Fonts/Typography | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 54 | User Stores | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 55 | Staging/Clone/Migration | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 56 | Theme Workspace | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |

The Custom Tables 90% value is a bounded-track progress indicator for the currently defined V1 foundation/composition runway; it is not a claim that 90% of all future Custom Tables product parity is implemented. Trusted Runtime Evidence Sources V1 and the post-trust audit do not invent a higher percentage because the repository defines no new percentage milestone for this wave.

The owner-directed Surfaces 10–18 wave is **planning/contract-only**. Surface 10 Dashboard Widgets has completed and promoted its planning/contract gate through Issue #493 / merged PR #508, but still has no implementation baseline or authorized runtime lane. For Surfaces 11–18, worker-branch seed candidates remain active-work evidence only and main `options-bank-progress.json` remains `UNSEEDED / 0` until reviewed worker PR evidence is merged and Supervisor reconciles that machine truth. Rows therefore retain `— / no implementation baseline` until a later repository-defined implementation milestone is explicitly promoted.

## AI-Native work-cycle order

Every Supervisor/Worker `start`, `continue` or `resume` cycle follows this mandatory hard-gated order from `AUTO-AGENT.md`:

1. refresh exact current `main`;
2. inspect and continue/solve accepted **OPEN Issues first**;
3. inspect/fix/review/merge eligible **OPEN PRs/MRs second**;
4. confirm no accepted actionable Issue/PR/MR path is being bypassed;
5. re-read active deterministic claims and the coordination queue;
6. only then start or claim new dependency-ready development;
7. after the cycle reaches a stable final state, reconcile this complete 56 / 56 README dashboard before reporting completion.

New development is forbidden while an accepted actionable Issue or PR/MR path is being bypassed unless repository evidence explicitly marks that path blocked or superseded.

Issue #415 / merged PR #416 promoted issue-first/PR-second README closeout. Issue #451 added timeline/forecast reconciliation. Issue #455 / merged PR #456 extends the Definition of Done to the complete canonical 56-surface dashboard and hard-gates new development behind Issues and PRs/MRs.

## Current Custom Tables state

Surface 7 is **ACTIVE / NOT PASS**. The following bounded foundations are promoted:

1. **Canonical table Definition + schema descriptor V1** — Issue #382 / merged PR #383.
2. **Observed schema normalization + pure Migration Plan V1** — Issue #385 / merged PR #387.
3. **Post-plan dependency audit** — Issue #388 / merged PR #390.
4. **Trusted CT1/PT-E physical identity + strictly read-only schema introspection V1** — Issue #389 / merged PR #391.
5. **Post-introspection next-lane audit V1** — Issue #392 / merged PR #394.
6. **Server-selected provider capability profile + pure DDL compiler preview V1** — Issue #393 / merged PR #395.
7. **Post-provider exact-main prerequisite audit V1** — Issue #397 / merged PR #398.
8. **Immutable Migration Run state machine V1** — Issue #399 / merged PR #402.
9. **Typed Precondition Contract V1** — Issue #400 / merged PR #403.
10. **Recovery + reviewed-source Revalidation V1** — Issue #401 / merged PR #404.
11. **Post-prerequisite exact-main audit V1** — Issue #405 / merged PR #406.
12. **Migration Run Repository Contract V1** — Issue #407 / merged PR #410.
13. **Precondition Evaluator V1** — Issue #408 / merged PR #411.
14. **Recovery Readiness V1** — Issue #409 / merged PR #412.
15. **Post-contract-wave exact-main audit V1** — Issue #413 / merged PR #414.
16. **AI-Native issue-first/PR-second + README closeout governance** — Issue #415 / merged PR #416.
17. **Migration Run Transition Service V1** — Issue #423 / merged PR #427.
18. **Precondition Probe Registry V1** — Issue #420 / merged PR #428.
19. **Recovery Evidence Source Contract V1** — Issue #421 / merged PR #429.
20. **Migration Execution Readiness V1** — Issue #422 / merged PR #430.
21. **Post-composition-wave exact-main audit + progress reconciliation** — Issue #431 / merged PR #432.
22. **Migration Run Persistence Record Codec V1** — merged PR #437.
23. **Precondition Read-Only Probe Plan V1** — merged PR #438.
24. **Recovery Evidence Binding/Freshness V1** — Issue #435 / merged PR #439.
25. **Execution Authorization Envelope V1** — Issue #436 / merged PR #440.
26. **Post-hardening-wave exact-main audit** — Issue #441 / merged PR #442.
27. **Metadata-only Precondition Probe Adapters V1** — merged PR #447.
28. **Recovery Verification Provider Port V1** — Issue #445 / merged PR #448.
29. **Execution Authorization Policy Adapter V1** — Issue #446 / merged PR #449.
30. **Internal Migration Run Store Schema + WPDB Repository V1** — Issue #443 / merged PR #450.
31. **Post-adapter exact-main audit V1** — Issue #453 / merged PR #454.
32. **Runtime Composition Readiness V1** — Issue #459 / merged PR #460.
33. **Post-Runtime Composition exact-main audit V1** — Issue #461 / merged PR #462.
34. **Trusted Runtime Evidence Sources V1** — Issue #463 / merged PR #464.
35. **Post-Trusted Runtime Evidence exact-main audit V1** — Issue #465 / merged PR #466.

PR #464 moves metadata-fact derivation behind a typed server-side provider, composes the existing read-only CT1 schema introspector, binds descriptors to persisted run identity/revision/schema, adds a site-scoped internal WPE confirmation metadata store + read-only confirmation source, removes provider arguments from canonical `create()`, and converts source failures into deterministic fail-closed readiness reasons. Its exact head passed all seven applicable workflows, including CPT Runtime and Taxonomy Runtime.

The post-trust audit accepts this hardening evidence but **does not authorize managed-table execution**. Public factory construction can still receive trusted-marker implementations, production recovery is intentionally fail-closed with no real allowlisted verifier, and no trusted confirmation issuance lifecycle has been promoted. Therefore R1/R2 execution remains **BLOCKED / NOT AUTHORIZED**.

### Still blocked

The current Custom Tables foundation does **not** authorize:

- physical `CREATE`, `ALTER`, `DROP`, `RENAME`, `TRUNCATE` or `dbDelta()` execution against managed Custom Tables;
- generic managed-table DDL dispatch through `$wpdb->query()` or shared database mutation interfaces;
- R1/R2 managed-table statement execution;
- row `INSERT`, `UPDATE`, `DELETE` or CRUD/Data Source runtime;
- R3/R4 managed-table execution;
- leases, retry workers or Action Scheduler migration execution;
- live row-count/null/duplicate/range/max-length precondition scans or row payload reads;
- Backup creation or restore side effects;
- backfill, deduplication, shadow-copy or swap flows;
- CT2/PT-D or CT3 runtime/topology conversion;
- external-table adoption;
- Custom Tables admin/REST/Ability/public execution mutation surfaces;
- `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment or release approval.

Custom Tables is safe-paused at its current bounded milestone until a later explicit trust-activation/hardening audit authorizes another lane.

## Owner-directed CPT + Taxonomy full-final program

The target for Surfaces 1 and 2 is **PARITY_OR_EXCEED**. Existing runtime code is preserved and gap-audited rather than rebuilt speculatively. A green CPT Runtime or Taxonomy Runtime workflow proves regression health of the current baseline; it does not by itself prove full product parity.

### Surface 1 — CPT Builder

Current machine truth after Issue #473 / merged PR #477:

- lifecycle `UX_CONTRACT_COMPLETE`;
- Options Bank `BANK_REVIEWED`, 107 records, zero unresolved review gates;
- 23 normalized Atomic Option Contracts with deterministic 107/107 source projection;
- `missing=0`, `unclassified=0`;
- reviewed Essential/Advanced/Expert UX contract;
- accepted runtime gap matrix against the existing CustomPostTypes baseline;
- existing Definition projection, validation, admin, Ability and registration/runtime baseline remains preserved;
- dedicated CPT Runtime regression workflow is green, but full runtime/product parity is not certified.

No CPT runtime-gap implementation lane is authorized by the current queue. The next CPT step requires a later exact-main Supervisor transition audit before any new runtime work is opened.

### Surface 2 — Taxonomy Builder

Current machine truth after merged PR #515:

- Options Bank `BANK_REVIEWED`, 71 records, zero unresolved review items;
- lifecycle `UX_CONTRACT_COMPLETE`;
- 20 normalized atomic contracts with deterministic 71/71 Bank source projection;
- `missing=0`, `unclassified=0`;
- reviewed Essential/Advanced/Expert UX contract and accepted live runtime gap matrix;
- Definition completeness promoted through PR #478;
- allowlisted runtime provider-ID architecture promoted through PR #479;
- read-only effective args, overrides, association health and REST/rewrite preview diagnostics backend promoted through PR #480;
- diagnostics admin rendering promoted through PR #483;
- adaptive hierarchy-aware label-generation runtime promoted through PR #486;
- reviewed complete 28-label authoring UX promoted through PR #489;
- tiered visibility authoring promoted through PR #503: Essential/Advanced/Expert navigation, truthful default/inherited/explicit states, inherited-key omission, hidden-value preservation and Expert boundary;
- accessible Find Setting search promoted through PR #506: searchable existing setting labels/IDs, automatic tier reveal, collapsed-section reveal, keyboard-first-result activation and canonical-control focus with no Taxonomy payload/provider/persistence/runtime semantic change;
- default-term and bounded object-term query authoring promoted through PR #512: canonical `default_term`, `sort`, allowlisted `args.orderby/order/fields`, reset/removal semantics, server-authoritative invalid-state validation, Find Setting integration and packaged axe/browser evidence;
- redacted provider catalog health promoted through PR #515: deterministic JSON-safe registered IDs + availability only, no class/callback descriptor exposure and no provider execution during catalog inspection;
- PR #515 exact head `b5753db7963fbf963e7ddbeaf6fcc6ffbeeba29d` passed Architecture Guards #1208, PHP Quality Toolchain #635, Taxonomy Runtime #59, CPT Runtime #112, Platform Compatibility Matrix #816 and Distributable Package #741 before squash merge as `d9204049a163bd79e41ad989af6d66c87e5388ae`;
- full runtime/product parity is not certified.

Current lane: **Taxonomy Builder Runtime Gap Closure V1** (Issue #474 / queue priority 1010).

Remaining work includes controlled provider selector UX using only registered redacted catalog IDs, capabilities/effective-map UX, rewrite/REST collision diagnostics, portability/CPT UI compatibility mapping, separately safety-gated taxonomy-key migration planning, and final runtime/browser/accessibility/security/compatibility/portability/performance certification evidence. This lane cannot claim `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED` until every accepted gap/evidence requirement closes.

## Owner-directed ten-worker parallel wave

Supervisor Issue #492 established ten distinct non-overlapping module claim lanes. Surface 2 / Taxonomy remains the only runtime implementation lane. Surface 10 / Dashboard Widgets has completed its planning/contract gate; Surfaces 11–18 remain planning/contract-only and cannot write runtime code until a later explicit gate.

- Worker 01: Surface 2 Taxonomy — Issue #474 / `agent/taxonomy-runtime-gap-closure-v1`; PR #515 promoted, broader program remains open and controlled selector UI remains outstanding.
- Worker 02: Surface 10 Dashboard Widgets — Issue #493 / merged PR #508; reviewed Bank 123, 18 normalized Atomic Option Contracts, deterministic five-shard 123/123 projection, reviewed Essential/Advanced/Expert UX contract and runtime gap matrix are promoted at `UX_CONTRACT_COMPLETE`; runtime remains unpromoted and no new runtime lane is opened.
- Worker 03: Surface 11 Admin Menu — Issue #494 / `agent/admin-menu-option-contract-ux-v1`; 8 normalized unreviewed candidate records plus expanded native/menu/admin-bar evidence at branch head `ed2df4e182d8c1f850c2f730d301bb1f9434c258`; main Bank remains UNSEEDED and current-main reconciliation is required after #516 before next write/promotion.
- Worker 04: Surface 12 Settings Pages — Issue #495 / `agent/settings-pages-option-contract-ux-v1`; 8 normalized unreviewed candidate records plus expanded site/network/user persistence evidence at branch head `9642872f1876e84b7af7a83492ce62b7eab25f20`; current-main reconciliation required after #516 before next write/promotion.
- Worker 05: Surface 13 Frontend Dashboard — Issue #496 / `agent/frontend-dashboard-option-contract-ux-v1`; 8 normalized unreviewed candidate records plus expanded routing/auth/ownership evidence at branch head `70c6bc4eed72db86a839f015f06c53fe02990109`; current-main reconciliation required after #516 before next write/promotion.
- Worker 06: Surface 14 User Profile — Issue #497 / `agent/user-profile-option-contract-ux-v1`; 8 normalized unreviewed candidate records plus expanded user-meta/profile lifecycle evidence at branch head `076f898f003d4fdfe63ca3ad4e43b5b87e38b64d`; current-main reconciliation required after #516 before next write/promotion.
- Worker 07: Surface 15 Membership — Issue #498 / `agent/membership-option-contract-ux-v1`; 8 normalized unreviewed candidate records plus expanded membership plan/enrollment/payment-boundary evidence at branch head `5d6bab5899e86ebc7d1a2c7d4f64bf21a9d41aee`; payment execution remains unauthorized and current-main reconciliation is required after #516 before next write/promotion.
- Worker 08: Surface 16 Builder Widgets — Issue #499 / `agent/builder-widgets-option-contract-ux-v1`; 8 normalized unreviewed candidate records plus expanded block metadata/context/dynamic-render evidence at branch head `d5631be6157ddcf284cf7aa319fb0b1e5206e459`; runtime adapter registration remains unauthorized and current-main reconciliation is required after #516 before next write/promotion.
- Worker 09: Surface 17 Forms & Workflows — Issue #500 / `agent/forms-workflows-option-contract-ux-v1`; 8 normalized unreviewed candidate records plus expanded nonce/auth/REST/submission-lifecycle evidence at branch head `11144f3931e1913e1136135b6167b293fbf588ed`; workflow execution remains unauthorized and current-main reconciliation is required after #516 before next write/promotion.
- Worker 10: Surface 18 Cron — Issue #501 / `agent/cron-option-contract-ux-v1`; 8 normalized unreviewed candidate records plus expanded hook+args/duplicate/timing/provider evidence at branch head `8421c401b2c2723952bda787a413198f9b53cab9`; WP-Cron is not represented as guaranteed wall-clock execution and current-main reconciliation is required after #516 before next write/promotion.

Workers 03–10 must continue evidence-first planning and must not edit README/CHECKPOINT/queue directly; any shared-truth lifecycle promotion returns to Supervisor. Branch-local normalized seed records do not themselves promote main Options Bank or Atomic Option lifecycle truth.

### What “full final” means

Neither CPT nor Taxonomy may be reported fully final until that surface explicitly promotes all of the following:

1. reviewed Options Bank with zero unresolved native/market/semantic items;
2. schema-valid Atomic Option Contract with `missing=0` and `unclassified=0`;
3. reviewed UX contract;
4. exhaustive existing-runtime gap matrix;
5. implementation of every accepted missing behavior through canonical owners;
6. applicable Multisite, security, REST/Ability, import/export/migration, accessibility, compatibility and performance evidence;
7. exact-head required runtime/browser/security/parity tests;
8. machine lifecycle `RUNTIME_CERTIFIED`;
9. competitor-parity acceptance `PRODUCT_PARITY_CERTIFIED`.

CPT Contract/UX planning is complete; Taxonomy Runtime Gap Closure remains the current authorized runtime lane. Dashboard Widgets Contract/UX planning is complete but no runtime lane is authorized. No new CPT or Dashboard Widgets runtime lane exists until a later exact-main transition audit explicitly opens one.

## Planning / Bank snapshot

Planning certification and runtime implementation are separate lifecycle dimensions.

- Canonical modules planned: **56 / 56**
- README closeout dashboard: **56 / 56 modules listed**
- Current Options Bank and Atomic Option lifecycle truth: `config/product/options-bank-progress.json` and `config/product/atomic-option-contract-progress.json`
- Product-parity targets: `config/product/competitor-parity-surfaces.json`
- Current conflict-safe development queue: `config/coordination/agent-work-queue.json`
- No bounded module pass in this README implies full product parity or release readiness.

`config/product/atomic-option-contract-progress.json` currently records all 56 surfaces in the atomic inventory, with **4 surfaces at `UX_CONTRACT_COMPLETE`** (CPT Builder, Taxonomy Builder, Admin Columns and Dashboard Widgets), **5 surfaces at or beyond `OPTION_CONTRACT_COMPLETE`**, and full-parity runtime/product certification still at zero. Machine-readable files are authoritative for planning lifecycle counts.

## Certified runtime / implementation gates

| Gate / Surface | Certified implementation state | Current boundary |
|---|---|---|
| A — Fields | **PASS — certified native V1 scope** | Broader provider/full parity remains gated |
| B — Relations | **PASS — certified native V1 baseline** | Richer provider/parity remains gated |
| C — Query | **PASS — certified bounded V1 baseline** | Public execution/full parity remains gated |
| D — Admin Columns | **PASS — certified bounded V1 baseline** | No unbounded mass-edit/provider-wide parity claim |
| E — Dynamic Listings | **PASS — certified bounded V1 baseline** | Richer async/builder parity remains gated |
| Status Manager | **PASS — certified bounded V1 baseline** | Workflow/provider/bulk parity remains gated |
| Custom Tables | **ACTIVE / NOT PASS — bounded runway 90%** | Trusted evidence hardening promoted; managed-table execution remains blocked and Surface 7 is safe-paused |

`config/product/atomic-option-contract-progress.json` remains the authority for full-parity lifecycle flags. A bounded gate PASS must never be reported as `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED` unless that machine state is explicitly promoted.

## Multi-agent development

WPEssential uses one Supervisor plus as many conflict-safe Workers as the dependency graph allows.

The authoritative queue is `config/coordination/agent-work-queue.json`.

### Supervisor

Start WPEssential Supervisor in AUTO mode.

Read `AUTO-AGENT.md` completely. Refresh exact current main, solve/continue accepted OPEN Issues first, then inspect/fix/review/merge eligible OPEN PRs/MRs, then active deterministic claim branches and the coordination queue before starting new work. New development must not bypass an accepted actionable Issue or PR/MR. The Supervisor owns shared truth, audit gates, merge serialization and complete 56 / 56 README progress/timeline reconciliation.

### Workers

Start WPEssential Worker in AUTO mode.

Read `AUTO-AGENT.md` completely. Refresh exact current main, inspect/continue OPEN Issues first and OPEN PRs/MRs second, then inspect the coordination queue only after those gates are clear. Workers must not duplicate accepted work or edit Supervisor-owned shared truth. Complete 56-module README progress/timeline changes are reported as an Integration Requirement.

Parallelism is encouraged only for non-overlapping dependency-safe lanes. Under owner-directed Issue #492, ten module lanes were established: Taxonomy runtime plus nine planning/contract-only lanes for Surfaces 10–18. Dashboard Widgets has now completed its planning/contract gate; the continuing active topology is Taxonomy runtime plus eight planning lanes for Surfaces 11–18. Planning workers may not promote runtime code or certification. Multiple workers must not independently modify the same runtime owner or bypass serialized safety gates.

## What WPEssential is

WPEssential is designed as one governed platform rather than a collection of unrelated mini-frameworks. Business modules compose shared contracts for:

- canonical data and semantic ownership;
- definitions and compiled WordPress registrations;
- capability and Policy authorization;
- Abilities and typed events;
- scoped persistence and migrations;
- jobs and external integrations;
- audit and diagnostics;
- WordPress bridges, AJAX, nonce and runtime security;
- Multisite isolation;
- AI/MCP-safe invocation boundaries.

Core rule: every business semantic has one canonical owner. UI, REST, Workflow, Cron, CLI and AI are invocation channels and cannot create private duplicate engines or bypass the canonical owner Policy/Ability/storage path.

## Engineering contract

Production implementation must preserve:

- namespace `WPEssential`;
- canonical PSR-4 source root `frameworks/`;
- global functions `wpessential_*`;
- constants `WPE_*`;
- exact custom filters `wpesential/apply_*`;
- custom actions `wpessential/hook_*`;
- one typed allowlisted AJAX gateway;
- centralized nonce operation handling;
- compile-on-write runtime registrations;
- bounded/redacted Runtime Observatory diagnostics;
- direct-access `ABSPATH` guards on shipped PHP source.

The asymmetric `wpesential` filter spelling is intentional public API.

See `CONTRIBUTING.md` for contribution and WordPress.org release rules. The mandatory WordPress.org/Plugin Check policy is `docs/QUALITY/WORDPRESS-ORG-PLUGIN-CHECK-COMPLIANCE.md`. `readme.txt` is the WordPress.org-facing plugin documentation draft for the current development line.

## Current foundation evidence

Dynamic Listings final bounded closure: `docs/IMPLEMENTATION/DYNAMIC-LISTINGS-GATE-E-FINAL-CLOSURE-AUDIT-V3.md`.

Status Manager final bounded closure: `docs/IMPLEMENTATION/STATUS-MANAGER-FINAL-CLOSURE-AUDIT-V3.md`.

Custom Tables dependency evidence currently includes:

- `docs/IMPLEMENTATION/POST-STATUS-NEXT-GATE-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-DEFINITION-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-PLAN-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-INTROSPECTION-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-PROVIDER-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-PREREQUISITE-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-CONTRACT-WAVE-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-COMPOSITION-WAVE-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-HARDENING-WAVE-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-ADAPTER-EXACT-MAIN-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-RUNTIME-COMPOSITION-EXACT-MAIN-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-TRUSTED-RUNTIME-EVIDENCE-EXACT-MAIN-AUDIT-V1.md`

CPT, Taxonomy and Dashboard Widgets planning/runtime evidence currently includes:

- `docs/PRODUCT/CPT-BUILDER-OPTIONS-BANK-AUDIT-CLOSURE-V1.md`
- `config/product/option-contracts/cpt.json`
- `docs/PRODUCT/CPT-BUILDER-UX-CONTRACT-V1.md`
- `docs/IMPLEMENTATION/CPT-BUILDER-RUNTIME-GAP-MATRIX-V1.md`
- `config/product/option-contracts/taxonomy.json`
- `docs/UI/TAXONOMY-BUILDER-UX-CONTRACT-V1.md`
- `docs/IMPLEMENTATION/TAXONOMY-BUILDER-RUNTIME-GAP-MATRIX-V1.md`
- `config/product/option-contracts/dashboard-widgets.json`
- `docs/UI/DASHBOARD-WIDGETS-UX-CONTRACT-V1.md`
- `docs/IMPLEMENTATION/DASHBOARD-WIDGETS-RUNTIME-GAP-MATRIX-V1.md`

Hosted CI provides architecture, PHP quality, WordPress/PHP/database compatibility and deterministic distributable-package evidence on applicable exact heads. WordPress.org release readiness remains a separate gate.

## Canonical planning maps

- `docs/ARCHITECTURE/CANONICAL-56-SURFACE-OWNERSHIP-REGISTRY.md`
- `docs/ARCHITECTURE/CROSS-MODULE-OPTION-OWNERSHIP-AND-NO-BYPASS-CONTRACT.md`
- `docs/MODULES/CANONICAL-OPTION-OWNERSHIP-INDEX-56-SURFACES.md`
- `docs/UI/ADMIN-INFORMATION-ARCHITECTURE-V2-56-SURFACES.md`
- `docs/SOLUTIONS/SYSTEM-PATTERN-TO-CANONICAL-SURFACE-MAP.md`
- `docs/ARCHITECTURE/CANONICAL-56-SURFACE-DEPENDENCY-RELATION-MATRIX.md`
- `docs/ARCHITECTURE/PER-SURFACE-CAPABILITY-ABILITY-EVENT-REGISTRY-32-56.md`
- `docs/ARCHITECTURE/DATA-OWNERSHIP-LIFECYCLE-REGISTRY-32-56.md`
- `docs/QUALITY/POST-P0-MODULE-OPTION-UI-SYSTEM-INTEGRITY-AUDIT.md`

Repository evidence and accepted ADRs override stale conversational summaries.