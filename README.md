# WPEssential

WPEssential is a modular WordPress application platform for structured data, automation, integrations, admin tooling, workflows, and AI-ready operations.

Project website: **https://wpessential.org**

> **Status:** Source development is active under explicit `GOV-OWNER-CONSENT-001` and remains milestone-gated. RC1 core stabilization is closed/non-GA. P-006 bounded evidence remains **144 documented / 57 executed / 57 PASS / 0 FAIL / 0 INCONCLUSIVE / 0 certified Free/Pro pairs / 0 runtime certifications** after Wave 1T. All four B6 prerequisites are now merged: FP-89 isolated Platform API candidate (B6a), FP-86 fail-once marker seam (B6b), FP-94 diagnostics/redaction capture (B6c), and FP-90 disposable older-DB SQL snapshot identity (B6d). None of FP-86/89/90/94 is formally executed yet. B6d proves only a disposable MySQL snapshot/restore fixed point plus canonical compatibility preflight before pending migrations; Pro 220/221 remain uninvoked and product Backup/Restore remains unimplemented/uncertified. No formal runtime grant, pair/runtime/migration certification, permanent P-001/CF, updater/TUF, production deployment, release authority or ADR-0010 promotion follows.

## Current AI-Native Development Progress

- Exact current product/runtime main: `a79bf3e2e5b3f6a026ce31184f7652f933508969` after **Issue #1236 / PR #1237 — Bounded Native Default-Hidden State V1** merged terminal PASS. **Issue #1238 / PR #1239 — Bounded Native Default-Collapsed State V1** is the active FAST feature tranche.
- #1191 / PR #1192 exact-head evidence: Governance `35792377626` PASS; Architecture `35792377512` PASS; zero unresolved review threads and zero behind.
- Latest bounded source milestone: **Issue #1203 / PR #1209 — Bounded Site Targeting Source V1 PASS / merged** as `ecc4d3f01a3a330a039d1b774b84ad5ca70f1bf3`; exactly five authorized compiler/descriptor/adapter source/test files changed.
- Completed transition audit: **Issue #1193 / PR #1197 — Site Targeting Transition Audit V1**, merged as `d57c9439dfd8f6105c3f5a0b90f8cef68e2ac5f4`; Governance `35854095152` and Architecture `35854095151` PASS.
- Bounded site targeting is now implemented on exact main: optional `widget.target.scope = all_sites|site_ids`, bounded positive exact-integer `site_ids` normalization, descriptor-owned targeting, current-site eligibility before collision grouping, and independent network collision behavior.
- Malformed/unknown site targeting now fails closed at compilation; invalid/throwing current-site evidence yields zero native site registration side effects. Provider/source execution and broader remote/mutation behavior remain outside this tranche.
- Site-targeting contract promotion completed with `READY_FOR_BOUNDED_SITE_TARGETING_SOURCE_V1`; PR #1200 exact head passed Governance and Architecture.
- Site-targeting, Query/Data-Source, Empty-State, Renderer-Failure Error-State, shared-truth and **#1236/#1237 Native Default-Hidden** milestones are terminal PASS. **Issue #1238 / PR #1239 — Bounded Native Default-Collapsed State V1** is ACTIVE under FAST AI-Native mode.
- Duplicate audit Issues #1194 and #1196 are closed; #1193 is canonical.
- Contract V1 keeps Query as execution owner: `DataSourceRegistryInterface` supplies descriptor/schema/availability truth; `QueryReadConsumerInterface` V1 is the only read-execution seam. Surface 10 authors bounded query intent only, derives projection from query-bound fields, and maps normalized rows into existing trusted Blueprint bindings.
- Architecture/security correction is closed: resulting main now contributes `QueryModule` exactly once before `DashboardWidgetsModule`; the #1217/#1218 focused smoke verifies canonical `module.query.read-consumer` registration through `QueryReadConsumerInterface`.
- Generic registered-provider execution, direct IntegrationRegistry execution, remote/iframe/RSS, asset side effects, user-preference persistence, inventory removal/discovery, collapsible disablement/dismissible, loading, caching/refresh, actions and full-parity certification remain blocked/separately gated. #1236 default-hidden is terminal PASS; #1238 adds only preference-safe bounded native default-collapsed projection.
- Open repository blockers remain #858 external-admin required-CI reconciliation, #1102 separately authorization-gated P-006 Wave 1U, and #947 independent Worker-only audit.
- Dashboard Widgets P0_NATIVE bank coverage is now `██████████ 100%` (12/12 bounded capabilities). This does not certify full Surface 10 product parity. The active P1_CORE Cron Background-Job Reference V1 tranche is tracked separately.

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
- Commercial distribution matrix: **2 Free / 1 Platform Core / 53 Pro** through Issue #902 / merged PR #903 and `docs/PRODUCT/FREE-PRO-ENTITLEMENT-MATRIX-V1.md`.
- Physical Free/Pro package boundary: **DONE for Package Boundary Gate V1** through Issue #904 / merged PR #905. Free physically ships Platform Core + CPT + Taxonomy implementation; premium implementation is separated into the Pro add-on. Certified compatibility pairs, live licensing/provider integration, deployment and release remain separate gates.
- Canonical Edition Metadata + Local Entitlement Domain V1: **DONE** through Issue #908 / merged PR #909. The 12 currently implemented premium Module manifests now report `edition: 'pro'`; Pro installs a provider-neutral entitlement-aware activation policy before premium contribution; activation/read preservation is modeled separately from premium mutation authority. This does not certify every future premium mutation path or add a live license/billing/provider service.
- Read-only Modules Commercial Inventory V1: **DONE** through Issue #912 / merged PR #913. The existing Platform Runtime Observatory now exposes server-generated `Module | Edition | Package | Compatibility | Entitlement | Runtime state | Reason` diagnostics from canonical ModuleRegistry/runtime state and the same local entitlement-provider truth used by Pro activation. It adds no activation controls, secrets, live licensing/provider calls or compatibility certification.
- RC1 7-Day Core Stabilization: **COMPLETED / RC CORE PRODUCTION CANDIDATE NON-GA** through Lane A Issue #1017 / PR #1021 / `39b441b4c991515b3384c7f7285143855796acbc`, Lane C Issue #1019 / PR #1022 / `891cd201667efbc72cc40285206741dc5a8c9abb`, Lane B Issue #1018 / PR #1023 / `aaf73c702f0f8bc073e1891e0456dea5ebe23904`, and Supervisor Issue #1016 / merged PR #1024 / `0adcc769947bde9fe4392b506280847902792f1e`. The bounded milestone covers Surfaces 1/2, 6/8/9 and 3/4/5 respectively; no 56/56 parity, production deployment or GA claim follows.
- P-006 executable evidence: **Waves 1A–1T have terminal bounded results** at **144 documented / 57 executed / 57 PASS / 0 FAIL / 0 INCONCLUSIVE / 0 certified Free/Pro pairs / 0 runtime certifications**. B6 prerequisite implementation is now complete for FP-86/89/90/94: B6a isolated Platform API candidate, B6b fail-once migration-marker path, B6c diagnostics/redaction capture, and B6d disposable older-DB SQL snapshot fixed point. These are prerequisite results only; FP-86/89/90/94 remain not formally executed. FP-90 B6d stops before Pro 220/221 and does not implement/certify product Backup/Restore. FP-81 remains N/A; FP-82/83/84/85/87/91/92/93 remain blocked; FP-24 remains N/A; permanent P-001/CF remains uncertified; ADR-0010 remains Proposed; #947 remains independent Worker-only.
- Main protection/security: active ruleset `23374068` enforces pull-request-based main protection, deletion/non-fast-forward protection, review-thread resolution, strict required `governance` status and zero bypass. Issue #858 remains open for broader required-CI policy reconciliation; the legacy branch-protection subobject is not authoritative for ruleset enforcement.
- Phase 2 Gate A / Fields: **PASS for the certified native V1 scope**; RC1 Lane B audit required no Fields source change.
- Phase 2 Gate B / Relations: **PASS for the certified native V1 baseline**; RC1 Lane B additionally binds endpoint mutation execution context to the active WordPress user/site/network through #1018/#1023.
- Phase 2 Gate C / Query: **PASS for the certified bounded V1 baseline**; RC1 Lane C preserved Query ownership and used it only as bounded target proof for Admin Columns.
- Phase 2 Gate D / Admin Columns: **PASS for the certified bounded V1 baseline**; RC1 Lane C adds stale-View revision fail-closed protection before Query/Fields delegation through #1019/#1022.
- Phase 2 Gate E / Dynamic Listings: **PASS for the certified bounded V1 baseline**; RC1 Lane C audit required no Listings source change.
- Status Manager: **PASS for the certified bounded V1 baseline** via Issue #378 / merged PR #379; RC1 Lane B audit required no Status source change.
- Surface 7 / Custom Tables: **ACTIVE / NOT PASS — bounded runway 90%**, safe-paused with managed-table execution blocked after Issue #463 / merged PR #464 and Issue #465 / merged PR #466.
- Surface 1 / CPT Builder: **UX_CONTRACT_COMPLETE / RC1 BOUNDED RUNTIME + PACKAGED EDITOR EVIDENCE HARDENED / NOT full-parity RUNTIME_CERTIFIED** through Issue #1017 / merged PR #1021. The RC1 audit found no demonstrated critical production-runtime behavior defect requiring source expansion; runtime assertions, hidden-option/support preservation, packaged browser regression and axe accessibility evidence were strengthened instead.
- Surface 2 / Taxonomy Builder: **PASS for the certified bounded accepted V1 owner-runtime scope** through Issue #632 / merged PR #634. The V1 audit Issue #626 / merged PR #628 isolated three final admin-UX blockers; Issue #629 / merged PR #631 closed canonical role-impact preview, unsaved-change guard and sticky Validate/Save behavior with exact-head Architecture Guards, Distributable Package and Browser E2E/Accessibility green. RC1 Lane A retained Taxonomy as regression-only and its packaged regression evidence remained green. The full-parity Atomic Option Contract machine lifecycle remains `UX_CONTRACT_COMPLETE`; `PRODUCT_PARITY_CERTIFIED` remains unpromoted, taxonomy-key migration execution remains separately safety-gated, and generic package orchestration remains outside Surface 2.
- Surface 10 / Dashboard Widgets: **UX_CONTRACT_COMPLETE / P0_NATIVE 12/12 bounded coverage + runtime foundation + registration/visibility/trusted-rendering + Query/DataSource binding + native inventory/preferences/presentation seams merged; P1_CORE Cron Background-Job Reference V1 ACTIVE / NOT full-parity RUNTIME_CERTIFIED**. #1258 / PR #1259 is the current FAST P1_CORE feature tranche.
- Surfaces 11–21: **UX_CONTRACT_COMPLETE / BANK_REVIEWED / bounded read-only owner-runtime exposure activated / NOT full-parity RUNTIME_CERTIFIED**. Runtime Foundation V1 is merged through Issues #844–#854 / PRs #859–#869 and Supervisor closeout #870 / PR #871. Read-only Module/Ability exposure is merged through Issues #873–#883 / PRs #886–#896 and was originally centrally activated from the monolithic Free bootstrap through Issue #897 / merged PR #898. PR #905 moved product-Pro module contribution into the separate Pro add-on; PR #909 normalized the currently implemented premium module manifests to `edition: 'pro'` under the entitlement-aware activation policy. Exposure remains `get`/`catalog` only, `mutates:false`, `manage_options`, Internal/UI/REST and the WordPress Ability bridge over canonical `platform.definitions`; no AJAX writes, provider execution, destructive mutation or certification promotion follows.
- Surfaces 22–28: **planning/readiness COMPLETE / Bank UNSEEDED / runtime unpromoted** through Issues #588–#594 / merged PRs #601–#607. No schema-valid option-contract, UX lifecycle, runtime or product-parity promotion is implied.
- Surface 30 / Roles & Capabilities: **UX_CONTRACT_COMPLETE / bounded canonical read-only runtime seam accepted / NOT RUNTIME CERTIFIED** through Issue #615 / merged PR #617 and Issue #618 / merged PR #620. The Bank is `BANK_REVIEWED / 68` with 19 normalized Atomic Option Contracts and deterministic 68/68 projection. Roles is now physically Pro-owned and canonically `edition: 'pro'` through PRs #905 and #909. The accepted runtime seam is policy-gated and read-only; broader role/capability/user mutation and full runtime/product parity remain unpromoted.

README reconciliation anchor: `current main @ a79bf3e2e5b3f6a026ce31184f7652f933508969` after #1236 / PR #1237 terminal bounded Native Default-Hidden State merge. RB-0059 is terminal PASS; RB-0060 tracks active #1238 / PR #1239 Bounded Native Default-Collapsed State V1 as the single meaningful feature merge gate. Shared truth is carried in the feature PR; no separate post-merge reconciliation is expected by default. #858 remains external-admin, #1102 authorization-gated and #947 independent Worker-only.

## Module implementation progress — 56 / 56 modules listed

This dashboard is mandatory AI-Native closeout truth. Every meaningful repository-changing engineering cycle must keep **all 56 canonical product surfaces** visible before the query/cycle is reported final.

Canonical surface names, numbers and ordering come from `docs/MODULES/CANONICAL-OPTION-OWNERSHIP-INDEX-56-SURFACES.md`. A missing or duplicated canonical surface makes closeout incomplete.

**Progress percentages below measure the currently approved/certified bounded implementation milestone for that module, not full product parity.** Planning lifecycle states are not implementation percentages. A 100% row means its explicitly named bounded/native baseline is certified; it does **not** imply `PRODUCT_PARITY_CERTIFIED`, deployment or release readiness. Completed Surfaces 11–21 read-only exposure does not receive an invented percentage because no percentage scale was promoted for that tranche.

Timeline fields follow `docs/AI-NATIVE-README-MODULE-TIMELINE-CLOSEOUT.md`. Timestamps use ISO-8601 UTC when repository evidence proves an exact value. Historical dates are never reconstructed from chat memory. Planning-only surfaces remain visible with explicit non-fabricated values.

| # | Module / Surface | Lifecycle / Status | Progress | Start date/time (UTC) | Estimated completion date/time (UTC) | Actual completion date/time (UTC) | Latest evidence | Next gate |
|---:|---|---|---|---|---|---|---|---|
| 1 | CPT Builder | PLANNED — UX_CONTRACT_COMPLETE; Options Bank BANK_REVIEWED (107); 23 atomic contracts + deterministic 107/107 projection; RC1 bounded runtime + packaged editor evidence hardened; full-parity lifecycle not promoted | — / no full-parity percentage milestone | 2026-09-09T21:03:34Z | FORECAST PENDING / full-parity runtime transition requires a separate gate | — | Issue #473 / PR #477 + RC1 Issue #1017 / PR #1021 / merge `39b441b4...`; CPT runtime, packaged hidden-option/support preservation and axe evidence | RC1 bounded lane complete; full-parity `RUNTIME_CERTIFIED` / product-parity acceptance remains separate |
| 2 | Taxonomy Builder | PASS — certified bounded accepted V1 owner-runtime; RC1 regression-safe; full-parity Atomic Option Contract machine lifecycle remains `UX_CONTRACT_COMPLETE` | `██████████ 100%` for the certified bounded V1 owner-runtime milestone | 2026-09-09T21:03:50Z | — completed | 2026-09-12T12:52:35Z | Issue #632 / merged PR #634; final UX closure #629 / PR #631; package-boundary/entitlement regressions #904/#908; RC1 packaged regression evidence #1017/#1021 | Full-parity machine runtime/product-parity acceptance is separate; taxonomy-key migration execution remains separately safety-gated |
| 3 | Fields | PASS — certified native V1 | `██████████ 100%` | UNKNOWN / pending evidence audit | — completed | UNKNOWN / pending evidence audit | Gate A; RC1 Lane B audit #1018/#1023 required no Fields source change | Broader provider/full parity remains gated |
| 4 | Relations | PASS — certified native V1 baseline + RC1 active WordPress context-binding hardening | `██████████ 100%` for the certified native V1 baseline | UNKNOWN / pending evidence audit | — completed | UNKNOWN / pending evidence audit | Gate B; RC1 Issue #1018 / PR #1023 / merge `aaf73c70...` with mismatch user/site/network fail-closed tests | Richer provider/full parity remains gated; RC1 auth hardening does not promote machine full parity |
| 5 | Status | PASS — certified bounded V1 | `██████████ 100%` | UNKNOWN / pending evidence audit | — completed | UNKNOWN / pending evidence audit | Issue #378 / PR #379; RC1 Lane B audit #1018/#1023 required no Status source change | Workflow/provider/bulk parity remains gated |
| 6 | Query | PASS — certified bounded V1 | `██████████ 100%` | UNKNOWN / pending evidence audit | — completed | UNKNOWN / pending evidence audit | Gate C; RC1 Lane C #1019/#1022 preserved Query as bounded target-proof owner | Public execution/full parity remains gated |
| 7 | Custom Tables | ACTIVE / NOT PASS | `█████████░ 90%` | UNKNOWN / pending evidence audit | FORECAST PENDING / safe-paused; execution trust activation not authorized | — | Issue #463 / merged PR #464 + Issue #465 / merged PR #466; package decoupling #904 / PR #905 | Managed-table execution remains blocked; later explicit trust-activation audit required |
| 8 | Admin Columns | PASS — certified bounded V1 baseline + RC1 stale-View mutation guard | `██████████ 100%` for the certified bounded V1 baseline | UNKNOWN / pending evidence audit | — completed | UNKNOWN / pending evidence audit | Gate D; RC1 Issue #1019 / PR #1022 / merge `891cd201...`; expected View revision required before Query/Fields delegation | No unbounded mass-edit/provider-wide parity claim; full-parity lifecycle remains separate |
| 9 | Listings | PASS — certified bounded V1 | `██████████ 100%` | UNKNOWN / pending evidence audit | — completed | UNKNOWN / pending evidence audit | Issue #343 / PR #344; RC1 Lane C audit #1019/#1022 required no Listings source change | Richer async/builder parity remains gated |
| 10 | Dashboard Widgets | UX_CONTRACT_COMPLETE; Bank BANK_REVIEWED (123); P0_NATIVE 12/12 bounded implementation evidence complete; runtime/ability/activation/registration/visibility/trusted-rendering + Query/DataSource binding + native inventory/preferences/presentation seams merged; P1_CORE audit: 2 already covered, 3 owner-contract blockers, 10 higher-risk gaps, Cron Background-Job Reference V1 ACTIVE / NOT full-parity RUNTIME_CERTIFIED | `████████░░ 80%` for #1258 Cron Background-Job Reference; code/tests/shared truth prepared, merge gate pending; not full product parity | 2026-09-10T19:46:54Z | FAST Turn A active / Issue #1258 / PR #1259 | 2026-09-25T04:17:01+05:00 for #1258 feature activation | #1256/#1257 Inventory Remove PASS on `cfec6da42fb5...`; RB-0068 PASS; RB-0069 pending | Validate #1259 terminal exact-head CI + review; expected-head merge; resulting-main verification |
| 11 | Admin Menu | UX_CONTRACT_COMPLETE; BANK_REVIEWED (16); bounded read-only Runtime Foundation V1 + get/catalog Module/Ability exposure available from the Pro add-on; NOT full-parity RUNTIME_CERTIFIED | — / bounded read-only milestone complete; no percentage scale defined | 2026-09-10T19:47:04Z | — bounded read-only exposure milestone completed | 2026-09-14T13:23:05Z | Foundation #844 / PR #859; Module/Ability #873 / PR #886; physical Pro move #904 / PR #905; pro edition + entitlement policy #908 / PR #909 | No dependency-ready source slot; deeper menu mutation/full-parity runtime requires new Supervisor authorization |
| 12 | Settings Pages | UX_CONTRACT_COMPLETE; BANK_REVIEWED (17); bounded read-only Runtime Foundation V1 + get/catalog Module/Ability exposure available from the Pro add-on; NOT full-parity RUNTIME_CERTIFIED | — / bounded read-only milestone complete; no percentage scale defined | 2026-09-10T19:47:14Z | — bounded read-only exposure milestone completed | 2026-09-14T13:23:05Z | Foundation #845 / PR #860; Module/Ability #874 / PR #887; physical Pro move #904 / PR #905; pro edition + entitlement policy #908 / PR #909 | No dependency-ready source slot; settings/options/secrets writes and full parity require a new gate |
| 13 | Frontend Dashboard | UX_CONTRACT_COMPLETE; BANK_REVIEWED (17); bounded read-only Runtime Foundation V1 + get/catalog Module/Ability exposure available from the Pro add-on; NOT full-parity RUNTIME_CERTIFIED | — / bounded read-only milestone complete; no percentage scale defined | 2026-09-10T19:47:23Z | — bounded read-only exposure milestone completed | 2026-09-14T13:23:05Z | Foundation #846 / PR #861; Module/Ability #875 / PR #888; physical Pro move #904 / PR #905; pro edition + entitlement policy #908 / PR #909 | No dependency-ready source slot; endpoint/user/provider mutation and full parity require a new gate |
| 14 | User Profile | UX_CONTRACT_COMPLETE; BANK_REVIEWED (17); bounded read-only Runtime Foundation V1 + get/catalog Module/Ability exposure available from the Pro add-on; NOT full-parity RUNTIME_CERTIFIED | — / bounded read-only milestone complete; no percentage scale defined | 2026-09-10T19:47:33Z | — bounded read-only exposure milestone completed | 2026-09-14T13:23:05Z | Foundation #847 / PR #862; Module/Ability #876 / PR #889; physical Pro move #904 / PR #905; pro edition + entitlement policy #908 / PR #909 | No dependency-ready source slot; user/auth/role/credential mutation and full parity require a new gate |
| 15 | Membership | UX_CONTRACT_COMPLETE; BANK_REVIEWED (15); bounded read-only Runtime Foundation V1 + get/catalog Module/Ability exposure available from the Pro add-on; NOT full-parity RUNTIME_CERTIFIED | — / bounded read-only milestone complete; no percentage scale defined | 2026-09-10T19:47:51Z | — bounded read-only exposure milestone completed | 2026-09-14T13:23:05Z | Foundation #848 / PR #863; Module/Ability #877 / PR #890; physical Pro move #904 / PR #905; pro edition + entitlement policy #908 / PR #909 | No dependency-ready source slot; payment/subscription/role/provider execution and full parity require a new gate |
| 16 | Builder Widgets | UX_CONTRACT_COMPLETE; BANK_REVIEWED (18); bounded read-only Runtime Foundation V1 + get/catalog Module/Ability exposure available from the Pro add-on; NOT full-parity RUNTIME_CERTIFIED | — / bounded read-only milestone complete; no percentage scale defined | 2026-09-10T19:48:05Z | — bounded read-only exposure milestone completed | 2026-09-14T13:23:05Z | Foundation #849 / PR #864; Module/Ability #878 / PR #891; physical Pro move #904 / PR #905; pro edition + entitlement policy #908 / PR #909 | No dependency-ready source slot; live builder/render/provider execution and full parity require a new gate |
| 17 | Forms & Workflows | UX_CONTRACT_COMPLETE; BANK_REVIEWED (17); bounded read-only Runtime Foundation V1 + get/catalog Module/Ability exposure available from the Pro add-on; NOT full-parity RUNTIME_CERTIFIED | — / bounded read-only milestone complete; no percentage scale defined | 2026-09-10T19:48:14Z | — bounded read-only exposure milestone completed | 2026-09-14T13:23:05Z | Foundation #850 / PR #865; Module/Ability #879 / PR #892; physical Pro move #904 / PR #905; pro edition + entitlement policy #908 / PR #909 | No dependency-ready source slot; submission/action/job/provider execution and full parity require a new gate |
| 18 | Cron | UX_CONTRACT_COMPLETE; BANK_REVIEWED (15); bounded read-only Runtime Foundation V1 + get/catalog Module/Ability exposure available from the Pro add-on; NOT full-parity RUNTIME_CERTIFIED | — / bounded read-only milestone complete; no percentage scale defined | 2026-09-10T19:48:26Z | — bounded read-only exposure milestone completed | 2026-09-14T13:23:05Z | Foundation #851 / PR #866; Module/Ability #880 / PR #893; physical Pro move #904 / PR #905; pro edition + entitlement policy #908 / PR #909 | No dependency-ready source slot; run-now/reschedule/delete/job/provider execution and full parity require a new gate |
| 19 | Notifications | UX_CONTRACT_COMPLETE; BANK_REVIEWED (16); bounded read-only Runtime Foundation V1 + get/catalog Module/Ability exposure available from the Pro add-on; NOT full-parity RUNTIME_CERTIFIED | — / bounded read-only milestone complete; no percentage scale defined | UNKNOWN / pending evidence audit | — bounded read-only exposure milestone completed | 2026-09-14T13:23:05Z | Foundation #852 / PR #867; Module/Ability #881 / PR #894; physical Pro move #904 / PR #905; pro edition + entitlement policy #908 / PR #909 | No dependency-ready source slot; dispatch/enqueue/retry/provider execution and full parity require a new gate |
| 20 | Emails | UX_CONTRACT_COMPLETE; BANK_REVIEWED (16); bounded read-only Runtime Foundation V1 + get/catalog Module/Ability exposure available from the Pro add-on; NOT full-parity RUNTIME_CERTIFIED | — / bounded read-only milestone complete; no percentage scale defined | UNKNOWN / pending evidence audit | — bounded read-only exposure milestone completed | 2026-09-14T13:23:05Z | Foundation #853 / PR #868; Module/Ability #882 / PR #895; physical Pro move #904 / PR #905; pro edition + entitlement policy #908 / PR #909 | No dependency-ready source slot; render/send/credential/provider execution and full parity require a new gate |
| 21 | Chat | UX_CONTRACT_COMPLETE; BANK_REVIEWED (17); bounded read-only Runtime Foundation V1 + get/catalog Module/Ability exposure available from the Pro add-on; NOT full-parity RUNTIME_CERTIFIED | — / bounded read-only milestone complete; no percentage scale defined | UNKNOWN / pending evidence audit | — bounded read-only exposure milestone completed | 2026-09-14T13:23:05Z | Foundation #854 / PR #869; Module/Ability #883 / PR #896; physical Pro move #904 / PR #905; pro edition + entitlement policy #908 / PR #909 | No dependency-ready source slot; conversation/message/realtime/provider mutation and full parity require a new gate |
| 22 | REST API | PLANNED — ATOMIC_INVENTORY_COMPLETE; Bank-entry/readiness + provisional UX + runtime-gap planning COMPLETE; Bank remains UNSEEDED / 0; implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / Bank seeding + native/market review required | — | Issue #588 / merged PR #601 | Seed/review Bank, derive schema-valid contracts, re-review UX; endpoint registration unauthorized |
| 23 | Connections/Webhooks | PLANNED — ATOMIC_INVENTORY_COMPLETE; Bank-entry/readiness + provisional UX + runtime-gap planning COMPLETE; Bank remains UNSEEDED / 0; implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / Bank seeding + native/market review required | — | Issue #589 / merged PR #602 | Seed/review Bank, derive schema-valid contracts, re-review UX; network/OAuth/webhook execution unauthorized |
| 24 | Backup | PLANNED — ATOMIC_INVENTORY_COMPLETE; Bank-entry/readiness + provisional UX + runtime-gap planning COMPLETE; Bank remains UNSEEDED / 0; implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / Bank seeding + native/market/provider review required | — | Issue #590 / merged PR #603 | Seed/review Bank, derive schema-valid contracts, re-review UX; backup/restore side effects unauthorized |
| 25 | Reset | PLANNED — ATOMIC_INVENTORY_COMPLETE; Bank-entry/readiness + provisional UX + runtime-gap planning COMPLETE; Bank remains UNSEEDED / 0; implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / Bank seeding + native/market review required | — | Issue #591 / merged PR #604 | Seed/review Bank, derive schema-valid contracts, re-review UX; destructive reset unauthorized |
| 26 | Import/Export | PLANNED — ATOMIC_INVENTORY_COMPLETE; Bank-entry/readiness + provisional UX + runtime-gap planning COMPLETE; Bank remains UNSEEDED / 0; implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / Bank seeding + native/market review required | — | Issue #592 / merged PR #605 | Seed/review Bank, derive schema-valid contracts, re-review UX; package/data mutation unauthorized |
| 27 | Protector | PLANNED — ATOMIC_INVENTORY_COMPLETE; Bank-entry/readiness + provisional UX + runtime-gap planning COMPLETE; Bank remains UNSEEDED / 0; implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / Bank seeding + native/market review required | — | Issue #593 / merged PR #606 | Seed/review Bank, derive schema-valid contracts, re-review UX; request/login/header enforcement unauthorized |
| 28 | Media Operations | PLANNED — ATOMIC_INVENTORY_COMPLETE; Bank-entry/readiness + provisional UX + runtime-gap planning COMPLETE; Bank remains UNSEEDED / 0; implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / Bank seeding + native/market/offload review required | — | Issue #594 / merged PR #607 | Seed/review Bank, derive schema-valid contracts, re-review UX; attachment/file/reference/CDN mutation unauthorized |
| 29 | XML-RPC | PLANNED — ATOMIC_INVENTORY_COMPLETE / implementation baseline not promoted | — / no implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | Atomic option progress (2026-08-31) | Schema instances + UX contracts, then implementation authorization |
| 30 | Roles & Capabilities | PLANNED — UX_CONTRACT_COMPLETE; Options Bank BANK_REVIEWED (68); 19 atomic contracts + deterministic 68/68 projection; bounded canonical policy-gated read-only runtime seam accepted / full runtime-product lifecycle not promoted | — / no full-parity percentage milestone | — / no implementation start promoted | FORECAST PENDING / broader runtime and mutation authorization required | — | Issue #615 / merged PR #617; Issue #618 / merged PR #620; Free/Pro package evidence #904 / PR #905; pro edition + entitlement policy #908 / PR #909 | Canonical read seam is available to peer diagnostics when Pro activation is allowed; broader Surface 30 mutation/runtime scope and full certification require separate authorization |
| 31 | Platform | PLANNED — ATOMIC_INVENTORY_COMPLETE; shared foundation PASS for module handoff | — / no Surface 31 implementation baseline | — / no implementation start promoted | FORECAST PENDING / implementation baseline not promoted | — | WP121 + atomic option progress; physical Free/Pro shared-core boundary #904 / PR #905; entitlement-domain shared Platform seam #908 / PR #909 | Surface 31 implementation baseline requires separate authorization |
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

Surfaces 11–21 have now progressed beyond their earlier planning/Bank-only snapshots. Their canonical Banks are `BANK_REVIEWED`, their machine lifecycle is `UX_CONTRACT_COMPLETE`, their fail-closed DefinitionRepository-backed Runtime Foundation V1 is merged, and their initial read-only `get`/`catalog` Module/Ability exposure is available through the Pro add-on. PR #909 additionally normalizes the currently implemented premium manifests to `edition: 'pro'` under the provider-neutral entitlement-aware activation policy. PR #913 adds read-only commercial/runtime visibility for kernel-known modules without promoting any module lifecycle or mutation authority. This tranche deliberately has no invented percentage scale and does not promote full-parity `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED`. Writes, provider execution, destructive mutation, external dispatch, production migration, deployment and release remain separately gated.

The owner-directed Surfaces 22–28 planning/readiness wave remains complete as planning/readiness evidence only. Issues #588–#594 / PRs #601–#607 add per-surface Bank-entry/readiness, provisional UX and runtime-gap/prerequisite documents. Their Banks remain `UNSEEDED / 0`; no higher option-contract, `UX_CONTRACT_COMPLETE`, runtime or product-parity promotion follows.

## Free / Pro distribution and entitlement boundary

Accepted commercial truth is governed by ADR-0001, ADR-0007, `docs/PRODUCT/FREE-PRO-ENTITLEMENT-MATRIX-V1.md` and `docs/IMPLEMENTATION/EDITION-ENTITLEMENT-DOMAIN-V1.md`.

Package Boundary Gate V1 is complete through Issue #904 / merged PR #905:

- `artifacts/wpessential.zip` is the WordPress.org-target Free package;
- `artifacts/wpessential-pro.zip` is the separate premium add-on foundation;
- Free contains only CPT Builder + Taxonomy Builder module implementation plus the shared platform/kernel/contracts/bootstrap required by Free;
- Pro contains premium module implementation/assets and rejects duplicated Free Platform/Kernel/Contracts/Bootstrap and Free CPT/Taxonomy source;
- package builds are deterministic and CI executes Free-only, Free→Pro and Pro→Free bootstrap verification;
- Free-only Taxonomy remains functional without Pro and visibly degrades optional Surface 30 role-impact diagnostics to `unavailable` rather than resolving Pro implementation from the Free artifact;
- Free+Pro composition restores the canonical Surface 30-backed role-impact path through the shared neutral contract.

Canonical Edition Metadata + Local Entitlement Domain V1 is complete through Issue #908 / merged PR #909:

- canonical local states are `free`, `trial_active`, `pro_active`, `grace`, `expired`, `suspended`, `verification_stale`, `verification_unavailable` and `incompatible_version`;
- Free modules are entitlement-independent;
- the 12 currently implemented premium Module manifests now truthfully report `edition: 'pro'`;
- Pro installs the entitlement-aware activation policy through the existing pre-boot seam before premium module contribution;
- `trial_active`, `pro_active` and `grace` permit premium mutation in the V1 operation policy;
- `expired`, `suspended`, `verification_stale` and `verification_unavailable` preserve read-safe Pro activation but deny premium mutation;
- `free` and `incompatible_version` deny Pro module activation;
- temporary verification failure remains distinct from expiry and Membership/user authentication is not product-entitlement truth;
- when no deterministic local state is supplied, Pro defaults to `verification_unavailable`, not `expired`.

Read-only Modules Commercial Inventory V1 is complete through Issue #912 / merged PR #913:

- the existing Platform Runtime Observatory exposes `Module | Edition | Package | Compatibility | Entitlement | Runtime state | Reason` for modules known to the current kernel;
- inventory rows derive from canonical `ModuleRegistry` state, not a duplicate registry;
- Pro entitlement display uses the same provider-neutral entitlement snapshot that controls Pro activation; Membership/user authentication is not substituted as entitlement truth;
- Free rows report `not_applicable` entitlement; Pro rows report bounded local entitlement state without tokens, keys or secrets;
- compatibility reports local prerequisite truth only and explicitly does not certify ADR-0010 Free/Pro version pairs;
- the table exposes no module activation/deactivation or mutation controls;
- exact PR #913 head `1be9ca7cf19fd65b83899fd269f72d0ba225178b` passed Architecture Guards, PHP Quality Toolchain, Distributable Package, Platform Compatibility Matrix and Browser E2E Accessibility.

P-006 bounded compatibility evidence now includes:

- Wave 1A / Issue #945 / PR #946 — FP-01/02/05/07/08 PASS against immutable Free/Pro package identity;
- Wave 1B / Issue #948 / PR #951 — FP-03/10/11 PASS for bounded local compatibility metadata/preflight evidence;
- Wave 1C / Issue #962 / PR #965 — real disposable WordPress FP-13/14 PASS for Free-only baseline on the authorized minimum/reference cells;
- Wave 1D / Issue #972 / PR #975 — real disposable WordPress FP-15/16 PASS for compatible Free+Pro activation/load-order evidence on both authorized cells, including WordPress `plugin_missing_dependencies` Pro-first prevention and successful Free-then-Pro recovery;
- Wave 1E / Issue #983 / PR #986 — real disposable WordPress FP-17/18 PASS on both authorized cells: installed inactive Pro contributed no Pro files/runtime/compatibility state in FP-17, and WordPress `plugin_missing_dependencies` prevented Pro-without-Free activation in FP-18; zero unexpected runtime HTTP in both fixtures;
- Wave 1F / Issue #995 / PR #997 — real disposable WordPress FP-19/20 PASS on both authorized cells using separately hashed NON-RELEASE / TEST-ONLY Free `0.0.9` and `0.1.1` variants. FP-19 recorded canonical `free_version_too_old` / `free_version_below_supported_minimum` / `update_free`; FP-20 recorded `free_version_too_new` / `free_version_above_supported_maximum` / `update_pro`. Premium boot/migrations remained denied, Free CPT/Taxonomy remained available, and runtime HTTP attempts were zero;
- Wave 1G / Issue #1007 / PR #1008 — real disposable WordPress FP-23/25/26/27/28 PASS across old/new mismatch candidates and minimum/reference cells. Premium boot/migrations stayed fail-closed, Free CPT/Taxonomy stayed available, admin remediation remained exact/non-scripted/non-hijacking, frontend premium leakage stayed absent, REST stayed non-fatal, cron/WP-CLI-shaped contexts stayed inert, no unexpected Pro implementation/modules loaded and runtime HTTP attempts were zero;
- Wave 1H / Issue #1014 / PR #1028 — static/harness-only FP-29/30/31/40/41/42 PASS and FP-33 INCONCLUSIVE/STOP-REVIEW. Run `35146615997` / artifact `10467128983` pinned immutable candidates before evaluation. FP-33 discovered concrete packaged migration-owner types, while incompatible decisions still denied premium boot/migrations; no runtime behavior was changed to force PASS;
- Wave 1I / Issue #1015 / PR #1030 — disposable real-WordPress FP-34/44 PASS on WordPress 6.9/PHP 8.2/MySQL 8.4 and WordPress 7.1/PHP 8.5/MySQL 8.4. Corrected canonical evidence run `35351107887` pinned candidate artifact `10549554022` (Free `2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80`, Pro `bd74077f22c7268765519ac2d4c64dd785ba59c9eee68f5a5f1b4d8fd8de0467`, pair `28e96f5207d3dc195bbf1b1a883ea7dcea5a314580661de5400e4e299dbd5211`). FP-34 proved equivalent compatible admission across genuine Free→Pro and Pro→Free active-plugin orders. FP-44 used 20 warm-ups + 100 measured evaluator samples per cell; minimum median/p95 `0.002783/0.002996 ms`, reference `0.003144/0.003285 ms`, with zero outbound HTTP. CI is split into bounded build/FP-34/FP-44 jobs with per-cell artifacts and stale-run cancellation;
- Wave 1J / Issue #1031 / PR #1033 — pure/local FP-61…68/76 Platform API range semantics PASS in PHP 8.2 and PHP 8.5. Initial evidence run `35386874694` pinned candidate artifact `10564580440`, PHP 8.2 artifact `10564382281`, PHP 8.5 artifact `10564610456`, Free `2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80`, Pro `bd74077f22c7268765519ac2d4c64dd785ba59c9eee68f5a5f1b4d8fd8de0467`, pair `28e96f5207d3dc195bbf1b1a883ea7dcea5a314580661de5400e4e299dbd5211`, and preflight source `53333d4bc59947e8dcc22b5119f7f0edd5609150cc47a33a360e40aaf62470f2`. Exact/min/max boundaries pass, below/above/malformed/unknown-major cases fail closed as declared, marketing-version movement inside its supported range does not override compatible Platform API, and FP-76 proves the same semantics in pure PHP without WordPress/DB/provider services. Both cells produced determinism digest `ddf105e199387baf9e949249c8176fc289052fdb582eaafaab74b4d71836b632`;
- Wave 1K / Issue #1034 / PR #1036 — compatible independent update-order FP-45/46/53/60 PASS on WordPress 6.9/PHP 8.2/MySQL 8.4 and WordPress 7.1/PHP 8.5/MySQL 8.4. Initial run `35388639559` pinned complete deterministic NON-RELEASE ZIP nodes F0 `2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80`, F1 `f9401f81b4a65d6f610b05cd445d3d8bdec75b2996296e0deaa13a8a2a265185`, P0 `0b12ada8265f3032641e09a80ba09f9950d3aef8b526b63c2b3665663aca4178`, P1 `96acfb8527e62f3ee52f8ef61b801f770315a3a633502ead845c5c4f145b30ac`; pair IDs are F0/P0 `c564b2fdc079a02dbba57bee0d90dd64fd683605c0fdbca89ab48323b394f2a0`, F1/P0 `f5157c46d4a29af6df4929cafeaad831fc50a8d38e469c2df73340766bb3faef`, F0/P1 `532f93f984bb5ecee1793a4c325c904e84945e35f1f5f682e20b59cbdbc58b26`. FP-45 and FP-46 preserve compatible premium admission/module availability and unchanged API/schema across Free-first/Pro-first replacements; FP-53 proves same-exact-F1 compatibility-layer retry idempotency using independent extractions with no compatibility persistence; FP-60 records exact post-step health identity. Runtime HTTP attempts are zero. Harness symlink-target transport does not certify manual upload, automatic updater/TUF or interrupted replacement;
- Wave 1L / Issue #1037 / PR #1039 — complete-package breaking update-order FP-47/48 PASS on WordPress 6.9/PHP 8.2/MySQL 8.4 and WordPress 7.1/PHP 8.5/MySQL 8.4. Initial run `35390769301` pinned complete deterministic NON-RELEASE ZIP nodes F1 `f9401f81b4a65d6f610b05cd445d3d8bdec75b2996296e0deaa13a8a2a265185`, F2 `92e54db0220ea76323fcf2b0e3772ea31e379dac47ed62690b027edbccbff4dc`, P0 `0b12ada8265f3032641e09a80ba09f9950d3aef8b526b63c2b3665663aca4178`, P2 `2a56eb653a3e685a76a6c43e5f22cebfe92b0827b77475bdf2242afd094478c3`; pair IDs are F1/P0 `f5157c46d4a29af6df4929cafeaad831fc50a8d38e469c2df73340766bb3faef`, F2/P0 `72f260a4882bb04aaf05a8a4960aaaf7fa2bba0f83591aeb79f5ece4f303480c`, F1/P2 `a3bb184caaef86f4c303c2cf73ba5f7631d711cae4e280cbca035e95937f6501`. FP-47 proves Free-first complete breaking replacement fails closed as `free_version_too_new`; FP-48 proves Pro-first complete breaking replacement fails closed as `free_version_too_old`. Both deny premium boot/migrations, keep premium modules inert, preserve the booted Free kernel and Free `custom-post-types`/`taxonomies`, and observe zero outbound HTTP/compatibility persistence. This B3a evidence does not certify partial/interrupted replacement, manual upload, updater/TUF, rollback or migrations;
- B3 harness readiness / Issue #1040 / PR #1042 — **NON-RUNTIME REVIEW / NO COUNTER CHANGE**. FP-49 BLOCKED; FP-50 PARTIAL / NOT FULL-FIXTURE READY; FP-51 BLOCKED AS WRITTEN; FP-52 BLOCKED AS WRITTEN; FP-58 required a WordPress-owned manual replacement prerequisite. Current guards cover some missing-file states, but readable-truncated PHP lacks pre-include integrity/publication protection;
- B3 manual replacement prerequisite / Issue #1043 / PR #1045 — **HARNESS PREREQUISITE PASS / FP-58 NOT EXECUTED**. Initial exact head `816cca3ca04cac48337dc02cb018da04c2c70d2a`, harness run `35394229243`, candidate artifact `10567116617`. WordPress core `Plugin_Upgrader::install(local ZIP, overwrite_package=true)` passes minimum/reference Free and Pro cells with `FS_METHOD=direct`, real plugin roots, exact F0→F1 and P0→P1 payload trees, preserved activation, compatible fresh boot, zero outbound HTTP and no temp-backup residue. Cell artifacts: minimum Free `10567201765`, minimum Pro `10566906985`, reference Free `10566818273`, reference Pro `10567082791`;
- current formal P-006 fixture accounting is **144 documented / 45 executed / 44 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified Free/Pro pairs / 0 runtime certifications**;
- temporary grants `-001` through `-010` are consumed/non-reusable; permanent P-001/CF remains uncertified; ADR-0010 remains Proposed; FP-21/22/24 remain NOT EXECUTED; FP-34/44, FP-45/46/53/60, FP-47/48 and FP-61…68/76 are bounded PASS evidence and do not constitute pair/runtime certification, interrupted/manual-replacement certification, updater/TUF certification or rollback/migration certification.

These commercial and bounded evidence gates are provider-neutral local architecture/read-only observability plus partial compatibility evidence, not a live commercial license service or full P-006 certification. Wave 1H is static/harness-only and its FP-33 STOP/REVIEW result is intentionally not promoted to PASS; Wave 1I adds bounded real-WordPress FP-34/44 evidence; Wave 1J adds bounded pure/local FP-61…68/76 range-semantics evidence; Wave 1K adds bounded compatible independent update-order FP-45/46/53/60 evidence against complete test-only ZIPs; Wave 1L adds bounded complete-package breaking-order FP-47/48 safe-degradation evidence. None promotes a certified pair, interrupted/manual-replacement certification, updater/TUF certification, rollback/migration certification or runtime certification. These gates do **not** claim universal wiring of every future premium mutation path, remote verification/billing/provider integration, credential/secret persistence, ADR-0010 acceptance, a certified Free/Pro pair, P-006 runtime certification, multisite allocation semantics, deployment or release readiness.

### FAST AI-Native delivery mode

To reduce development latency and chat noise, the default workflow is now:

- **Turn A:** exact-main audit → accepted Issue → implementation → focused tests → README/shared-truth/coordination update → PR open.
- **Turn B:** one consolidated CI/review check → fix only real failures → expected-head merge → terminal resulting-main verification.
- Do not create separate contract PRs for routine bounded features; reserve them for auth/security, payments, destructive/privileged mutation, remote/provider transport or shared-platform boundary changes.
- Do not create a post-merge reconciliation PR by default. Carry shared truth in the implementation PR whenever its allowlist permits; use a reconciliation milestone only for real divergence/conflict or a technical scope restriction.
- Runner Benchmark tracks the meaningful merge gate instead of every administrative transition.
- Progress messages should report meaningful outcomes rather than internal micro-steps.
- Security requirements remain unchanged: exact scope, fail-closed behavior, path-applicable CI, architecture checks, zero-behind and expected-head merge.

## AI-Native work-cycle order

Every Supervisor/Worker `start`, `continue` or `resume` cycle follows this mandatory hard-gated order from `AUTO-AGENT.md`:

1. read `.ai/state/CURRENT-STATE.yaml` and `.ai/state/LAST-CHECKPOINT.md`;
2. refresh exact current `main` and reconcile stale compact state;
3. inspect and continue/solve accepted **OPEN Issues first**;
4. inspect/fix/review/merge eligible **OPEN PRs/MRs second**;
5. confirm no accepted actionable Issue/PR/MR path is being bypassed;
6. re-read active deterministic claims, the coordination queue and Runner Benchmark;
7. execute one **meaningful feature milestone** by default under FAST AI-Native mode: Turn A = audit + Issue + implementation + tests + README/shared truth + PR open; Turn B = one CI/review refresh + fixes if needed + expected-head merge + terminal verification. Avoid administrative micro-milestones and never tight-poll CI/status endpoints;
8. before reporting completion/blocked/waiting, persist compact durable state;
9. reconcile this complete 56 / 56 README dashboard only when module/public delivery truth changed or at a terminal product milestone/integration closeout.

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
36. **Physical Free/Pro decoupling** — Issue #904 / merged PR #905; Custom Tables implementation remains Pro-owned and optional from the Free bootstrap.

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

Current machine truth after Issue #473 / merged PR #477 and RC1 Issue #1017 / merged PR #1021:

- Options Bank `BANK_REVIEWED`, 107 records, zero unresolved review gates;
- 23 normalized Atomic Option Contracts with deterministic 107/107 source projection;
- `missing=0`, `unclassified=0`;
- reviewed Essential/Advanced/Expert UX contract;
- accepted runtime gap matrix against the existing CustomPostTypes baseline;
- existing Definition projection, validation, admin, Ability and registration/runtime baseline remains preserved;
- RC1 fresh audit found no demonstrated critical production-runtime defect requiring behavior expansion;
- real WordPress CPT runtime assertions now cover rewrite policy, custom query_var and explicit `can_export=false` propagation;
- packaged browser evidence proves visible editor updates preserve hidden advanced runtime options and hidden editor supports while existing Taxonomy packaged regressions remain green;
- axe accessibility evidence covers the bounded packaged CPT editor;
- dedicated CPT Runtime regression workflow remains green, but full runtime/product parity is not certified.

RC1 Lane A is complete. No additional CPT runtime source slice is open after RC1 closeout; any future full-parity `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED` transition requires a separate accepted exact-main gate.

### Surface 2 — Taxonomy Builder

Current machine truth through Issue #632 / merged PR #634 plus package-boundary and entitlement regression evidence from Issues #904/#908 and merged PRs #905/#909, with RC1 packaged regression evidence from Issue #1017 / PR #1021:

- Options Bank `BANK_REVIEWED`, 71 records, zero unresolved review items;
- full-parity Atomic Option Contract machine lifecycle remains `UX_CONTRACT_COMPLETE`;
- the accepted bounded V1 owner-runtime scope is independently certified PASS by the exact-main V2 audit;
- 20 normalized atomic contracts with deterministic 71/71 Bank source projection;
- `missing=0`, `unclassified=0`;
- reviewed Essential/Advanced/Expert UX contract and accepted runtime gap matrix;
- previously promoted Definition, provider-ID, diagnostics, labels, tiered visibility, setting search, default-term/object-term query, rewrite/REST/provider/capability/compatibility/portability evidence remains preserved;
- searchable/grouped object-type discovery with preserved external keys is promoted through PR #578;
- the post-object-discovery exact-main audit in PR #597 closes object-type search/grouping and Definition-list Revision as duplicate residuals;
- Issue #609 / merged PR #610 promotes one canonical server-authoritative read-only dependency/usage summary, Definition-list runtime health/dependency count and matching diagnostics presentation with exact-head PHP/Package/Compatibility/Architecture/Taxonomy Runtime/Browser-Axe evidence;
- Issue #611 / merged PR #612 records the historical role-impact blocker and establishes that Surface 2 must not implement a private role engine;
- Surface 30 Issue #615 / merged PR #617 promotes `BANK_REVIEWED / 68`, 19 normalized Atomic Option Contracts, deterministic 68/68 projection and `UX_CONTRACT_COMPLETE` planning truth;
- Surface 30 Issue #618 / merged PR #620 promotes a bounded canonical policy-gated read-only role catalog/capability-impact seam with explicit true/false/absent semantics, scoped degraded/unavailable states, site/network context and Super Admin caveats; this does not certify the whole Surface 30 product;
- Taxonomy Issue #621 / merged PR #623 consumes only that canonical Surface 30 seam for Definition `read_model.role_impact` and validation `diagnostics.role_impact`, preserves explicit allow/deny/absent and contextual/meta-capability caveats, deduplicates repeated capability queries, and fails visibly unavailable instead of scanning WordPress role storage directly;
- Issue #626 / merged PR #628 independently audited exact main and identified exactly three remaining reviewed admin-UX blockers: visual role-impact preview, unsaved-change guard and sticky Validate/Save;
- Issue #629 / merged PR #631 closes those three blockers and its final head `7c49f294...` passed Architecture Guards, Distributable Package and Browser E2E Accessibility, including packaged role-impact/dirty-state/sticky-command/Axe evidence;
- Issue #632 / merged PR #634 performs the fresh exact-main contract-by-contract V2 audit and finds no remaining accepted bounded V1 owner-runtime blocker, certifying that bounded V1 milestone without promoting the full-parity Atomic Option Contract lifecycle;
- Issue #904 / merged PR #905 proves the Free-only packaged Taxonomy path remains functional when Surface 30 Pro implementation is physically absent: role-impact diagnostics fail visibly to `unavailable`, not fatally or through a hidden Free→Pro source dependency;
- Issue #908 / merged PR #909 proves the separated Free+Pro Surface 30-backed role-impact path remains green after Roles and the other currently implemented premium modules are canonically `edition: 'pro'` and routed through the entitlement-aware activation policy;
- RC1 Lane A changed no Taxonomy source and retained packaged Taxonomy regression evidence on the same exact candidate;
- taxonomy-key migration planning exists, but **execution remains blocked by design** pending separate explicit safety authorization;
- generic package/import-export orchestration remains Surface 26-owned;
- machine full-parity `RUNTIME_CERTIFIED` and `PRODUCT_PARITY_CERTIFIED` remain unpromoted.

Current lane: **Taxonomy Builder Runtime Gap Closure V1 is complete and PASS-certified for the accepted bounded V1 owner-runtime scope; RC1 regression guard also passed**. No dependency-ready Surface 2 implementation slice remains in the current queue. Full-parity machine runtime/product-parity lifecycle, taxonomy-key migration execution and generic package orchestration are separate gates and are not implicitly authorized by the bounded V1 certification.

## Owner-directed planning waves

Surfaces 11–21 completed their staged planning-to-read-only-runtime progression through Bank seeding/audit/review, Atomic Option Contracts, UX contracts, Post-UX readiness, bounded Runtime Foundation V1, Module/Ability exposure and initial centralized plugin contribution. Issue #904 / merged PR #905 moved those product-Pro contributions behind the physically separate Pro add-on, Issue #908 / merged PR #909 normalized the currently implemented premium manifests to `edition: 'pro'` behind the provider-neutral entitlement-aware activation policy, and Issue #912 / merged PR #913 adds read-only commercial/runtime visibility in the shared Platform diagnostics surface. None of these commercial gates promotes deeper runtime mutation/provider authority. This remains a bounded read-only owner-runtime tranche, not full product parity.

Supervisor Issue #583 established the earlier 11 isolated planning lanes. Hosted coding-agent assignment was attempted after coordination PR #595 but the available GitHub connector returned HTTP 403; no hosted claim became active. Repository precedent was followed through fallback PR #596, and the deterministic planning lanes completed without overlapping writes. Surfaces 19–21 have since advanced through the same reviewed Bank/Atomic/UX/read-only runtime exposure progression as Surfaces 11–18. Surfaces 22–28 remain planning/readiness-only and `UNSEEDED / 0`.

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

## Planning / Bank snapshot

Planning certification and runtime implementation are separate lifecycle dimensions.

- Canonical modules planned: **56 / 56**
- README closeout dashboard: **56 / 56 modules listed**
- Current Options Bank and Atomic Option lifecycle truth: `config/product/options-bank-progress.json` and `config/product/atomic-option-contract-progress.json`
- Current Options Bank truth: **22 seeded / 22 NATIVE_AUDITED / 22 MARKET_AUDITED / 22 BANK_REVIEWED / 2139 records**.
- Product-parity targets: `config/product/competitor-parity-surfaces.json`
- Current conflict-safe development queue: `config/coordination/agent-work-queue.json`
- No bounded module pass in this README implies full product parity or release readiness.

`config/product/atomic-option-contract-progress.json` records all 56 surfaces in the atomic inventory, with **17 surfaces at or beyond `OPTION_CONTRACT_COMPLETE`**, **16 surfaces exactly at `UX_CONTRACT_COMPLETE`**, **0 surfaces at full-parity `RUNTIME_CERTIFIED`**, and **0 surfaces at `PRODUCT_PARITY_CERTIFIED`**. Machine-readable files are authoritative for lifecycle counts. Bounded runtime/read-only exposure evidence is tracked separately and does not advance this full-parity machine lifecycle unless explicitly promoted.

## Certified runtime / implementation gates

| Gate / Surface | Certified implementation state | Current boundary |
|---|---|---|
| CPT Builder | **RC1 bounded runtime/editor regression evidence hardened / NOT full-parity RUNTIME_CERTIFIED** | No accepted RC1-critical production behavior defect found; full-parity runtime/product certification remains separately gated |
| A — Fields | **PASS — certified native V1 scope** | RC1 Lane B audit required no source change; broader provider/full parity remains gated |
| B — Relations | **PASS — certified native V1 baseline + RC1 active-context auth hardening** | Endpoint mutation context is bound to active WP user/site/network; richer provider/full parity remains gated |
| C — Query | **PASS — certified bounded V1 baseline** | RC1 preserved Query ownership; public execution/full parity remains gated |
| D — Admin Columns | **PASS — certified bounded V1 baseline + RC1 stale-View mutation guard** | Expected View revision must match before Query/Fields delegation; no unbounded mass-edit/provider-wide parity claim |
| E — Dynamic Listings | **PASS — certified bounded V1 baseline** | RC1 audit required no source change; richer async/builder parity remains gated |
| Status Manager | **PASS — certified bounded V1 baseline** | RC1 Lane B audit required no source change; workflow/provider/bulk parity remains gated |
| Taxonomy Builder | **PASS — certified bounded accepted V1 owner-runtime scope + RC1 regression guard** | Full-parity machine lifecycle remains `UX_CONTRACT_COMPLETE`; product parity and taxonomy-key migration execution remain separate gates |
| Custom Tables | **ACTIVE / NOT PASS — bounded runway 90%** | Trusted evidence hardening promoted; managed-table execution remains blocked and Surface 7 is safe-paused |
| Surfaces 11–21 | **BOUNDED READ-ONLY FOUNDATION + MODULE/ABILITY EXPOSURE ACCEPTED IN PRO / NOT FULL-PARITY CERTIFIED** | `get`/`catalog` only when Pro activation is allowed; writes, providers, destructive operations and full-parity runtime/product certification remain separate gates |
| Free/Pro Package Boundary V1 | **PASS — physical distribution boundary accepted** | No certified compatibility pair, deployment or release claim |
| Edition Metadata + Local Entitlement Domain V1 | **PASS — provider-neutral local commercial-runtime model accepted** | No live license/billing/provider service, universal future premium-operation enforcement, ADR-0010 certification, multisite allocation, deployment or release claim |
| Modules Commercial Inventory V1 | **PASS — bounded read-only commercial/runtime diagnostics accepted** | No activation controls, secrets, remote verification/provider execution, ADR-0010 compatibility certification, lifecycle promotion, deployment or release claim |
| P-006 Waves 1A–1M | **45 PASS + 1 INCONCLUSIVE across 46 explicitly executed fixtures / NOT P-006 CERTIFIED** | 144 documented / 46 executed / 45 PASS / 0 FAIL / 1 INCONCLUSIVE; FP-58 manual replacement order PASS; FP-33 STOP-REVIEW; no certified pair/runtime; no interrupted-partial/updater/TUF/rollback/migration certification; ADR-0010 Proposed; FP-21/22/24 NOT EXECUTED |

`config/product/atomic-option-contract-progress.json` remains the authority for full-parity lifecycle flags. A bounded gate PASS or accepted read-only/commercial infrastructure tranche must never be reported as machine `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED` unless that machine state is explicitly promoted consistently with the canonical option-contract instance.

## Multi-agent development

WPEssential uses one Supervisor plus as many conflict-safe Workers as the dependency graph allows.

The authoritative queue is `config/coordination/agent-work-queue.json`.

### Supervisor

Start WPEssential Supervisor in AUTO mode.

Read `AUTO-AGENT.md` completely. Read compact `.ai/state` first, refresh exact current main, solve/continue accepted OPEN Issues first, then inspect/fix/review/merge eligible OPEN PRs/MRs, then active deterministic claims, coordination queue and Runner Benchmark before new work. New development must not bypass an accepted actionable Issue or PR/MR. FAST AI-Native mode is default: combine audit + Issue + implementation + focused tests + README/shared truth + PR open into one meaningful feature milestone when safe; use the next milestone for one consolidated CI/review refresh + required fixes + expected-head merge + terminal verification. Separate contract PRs are for high-risk boundaries only; separate post-merge reconciliation is exceptional, not default. Never tight-poll CI. Preserve exact allowlists, CI/architecture/security gates and expected-head merge protection.

### Workers

Start WPEssential Worker in AUTO mode.

Read `AUTO-AGENT.md` completely. Read compact `.ai/state` first, refresh exact current main, inspect/continue OPEN Issues first and OPEN PRs/MRs second, then inspect the coordination queue and Runner Benchmark only after those gates are clear. Workers must not duplicate accepted work or edit Supervisor-owned shared truth, must not tight-poll CI, and must report README reconciliation as an Integration Requirement only when the delivery-truth trigger applies.

RC1 implementation Lanes A/B/C are integrated and remain non-GA. Bounded P-006 Waves 1A–1M remain terminal at **46 executed / 45 PASS / 0 FAIL / 1 INCONCLUSIVE**. Queue v76 records the #1055 FP-49…52 applicability-review closeout and exposes **no Supervisor-claimable P-006 execution slot**. FP-49/51/52 are dependency-ready for separately authorized future runtime evidence; FP-50 requires a non-runtime expectation decision first. No FP-49…52 fixture has executed. #947 remains independent-review-only. ADR-0010 remains Proposed and permanent P-001/CF remains uncertified. Security issue #858 remains open for broader required-CI policy reconciliation.

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

RC1 core stabilization evidence now includes:

- `docs/IMPLEMENTATION/RC1-7-DAY-SPRINT-V1.md` — bounded sprint scope, lane ownership and verification model;
- Issue #1017 / merged PR #1021 — CPT runtime + packaged editor regression hardening with Taxonomy regression guard;
- Issue #1019 / merged PR #1022 — Admin Columns stale-View revision precondition before Query/Fields-owned mutation delegation;
- Issue #1018 / merged PR #1023 — Relations active WordPress user/site/network context binding before endpoint mutation authorization;
- Issue #1016 / merged PR #1024 / `0adcc769947bde9fe4392b506280847902792f1e` — serialized Supervisor closeout, exact-head FULL evidence and RC/non-GA truth reconciliation completed.

Commercial Free/Pro evidence now includes:

- `docs/DECISIONS/ADR-0001-free-pro-distribution.md`
- `docs/DECISIONS/ADR-0007-license-expiry-runtime.md`
- `docs/DECISIONS/ADR-0010-free-pro-compatibility.md` — remains Proposed; bounded P-006 evidence through PR #1039, the non-runtime Issue #1040 / PR #1042 readiness review and the prerequisite-only Issue #1043 / PR #1045 manual overwrite harness do not accept ADR-0010, certify a Free/Pro pair/runtime, or certify FP-58, interrupted replacement, updater/TUF, rollback or migration semantics
- `docs/COMMERCIAL-DISTRIBUTION.md`
- `docs/COMMERCIAL-POSITIONING-AND-PACKAGING.md`
- `docs/PRODUCT/FREE-PRO-ENTITLEMENT-MATRIX-V1.md`
- `docs/IMPLEMENTATION/EDITION-ENTITLEMENT-DOMAIN-V1.md`
- Issue #904 / merged PR #905 — physical Free/Pro Package Boundary Gate V1
- Issue #908 / merged PR #909 — Canonical Edition Metadata + Local Entitlement Domain V1
- Issue #912 / merged PR #913 — read-only Modules Commercial Inventory V1
- Issue #945 / merged PR #946 — P-006 Wave 1A static artifact evidence, five fixtures PASS
- Issue #948 / merged PR #951 — P-006 Wave 1B bounded local compatibility evidence, three fixtures PASS
- Issue #962 / merged PR #965 — P-006 Wave 1C real WordPress Free-only baseline, FP-13/14 PASS
- Issue #972 / merged PR #975 — P-006 Wave 1D real WordPress compatible Free+Pro baseline, FP-15/16 PASS
- Issue #983 / merged PR #986 — P-006 Wave 1E real WordPress inactive-Pro/dependency-state baseline, FP-17/18 PASS
- Issue #995 / merged PR #997 — P-006 Wave 1F real WordPress marketing-version mismatch baseline, FP-19/20 PASS with NON-RELEASE / TEST-ONLY Free variants
- Issue #1007 / merged PR #1008 — P-006 Wave 1G real WordPress mismatch-context evidence, FP-23/25/26/27/28 PASS with exact reused TEST-ONLY mismatch candidates
- Issue #1014 / PR #1028 — P-006 Wave 1H static/harness evidence, FP-29/30/31/40/41/42 PASS and FP-33 INCONCLUSIVE/STOP-REVIEW; no runtime/certification promotion

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
- `docs/IMPLEMENTATION/TAXONOMY-BUILDER-POST-OBJECT-DISCOVERY-RESIDUAL-AUDIT-V1.md`
- `docs/IMPLEMENTATION/TAXONOMY-CAPABILITY-ROLE-IMPACT-DEPENDENCY-AUDIT-V1.md`
- `docs/IMPLEMENTATION/TAXONOMY-ROLE-IMPACT-CONSUMER-V1.md`
- `docs/IMPLEMENTATION/ROLES-CAPABILITIES-READ-SEAM-RUNTIME-V1.md`
- `docs/IMPLEMENTATION/TAXONOMY-EXACT-MAIN-RUNTIME-CERTIFICATION-AUDIT-V1.md`
- `docs/IMPLEMENTATION/TAXONOMY-FINAL-ADMIN-UX-CLOSURE-V1.md`
- `docs/IMPLEMENTATION/TAXONOMY-EXACT-MAIN-RUNTIME-CERTIFICATION-AUDIT-V2.md`
- `config/product/option-contracts/dashboard-widgets.json`
- `docs/UI/DASHBOARD-WIDGETS-UX-CONTRACT-V1.md`
- `docs/IMPLEMENTATION/DASHBOARD-WIDGETS-RUNTIME-GAP-MATRIX-V1.md`

Surfaces 11–21 bounded read-only evidence is promoted through Runtime Foundation V1 Issues #844–#854 / PRs #859–#869, Supervisor closeout #870 / PR #871, read-only Module/Ability Issues #873–#883 / PRs #886–#896, original central activation #897 / PR #898, physical Pro-package contribution boundary #904 / PR #905, canonical Pro edition/entitlement activation policy #908 / PR #909, and shared read-only Modules commercial/runtime inventory #912 / PR #913. Each surface remains fail-closed and read-only over the canonical Definition Repository; full-parity runtime/product certification and side-effect authority remain unpromoted.

Surfaces 22–28 planning/readiness evidence is promoted through Issues #588–#594 / PRs #601–#607. Each surface has one `docs/PRODUCT/*-BANK-ENTRY-READINESS-V1.md`, one provisional `docs/UI/*-UX-CONTRACT-V1.md`, and one `docs/IMPLEMENTATION/*-RUNTIME-GAP-MATRIX-V1.md`; their Banks remain `UNSEEDED / 0`. None of these planning records authorize runtime implementation.

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
