# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-09 UTC**  
Canonical audited base anchor: **`main @ 56cbcce2da90a11232fd65b374ad237fc1c67fb0`**  
Project classification: **`ACTIVE_EXISTING_PROJECT`**  
Execution mode: **`IMPLEMENTATION_GATED`**  
Development approval: **`GOV-OWNER-CONSENT-001 ACTIVE / source scope 56/56`**

## AI-Native mandatory work-cycle order

Every Supervisor/Worker `start`, `continue` and `resume` cycle is hard-gated in this order:

1. resolve exact current `main`;
2. inspect/continue/solve accepted **OPEN Issues first**;
3. inspect/fix/review/merge eligible **OPEN PRs/MRs second**;
4. confirm no accepted actionable Issue/PR/MR path is being bypassed;
5. re-read deterministic claims and `config/coordination/agent-work-queue.json`;
6. only then claim/start dependency-ready development;
7. after a meaningful stable cycle, reconcile README and shared truth before reporting completion.

Accepted issue/PR work must not be bypassed by speculative replacement branches. Merge order follows dependencies, latest-main reconciliation, exact-head CI, clean review threads and shared-truth safety.

README closeout must retain the complete canonical **56 / 56 module table**. Planning-only modules remain visible without fabricated implementation percentages or timestamps. Percentages measure only the currently approved/certified bounded milestone and never imply full product parity unless machine-readable lifecycle truth explicitly promotes that state.

## Certified bounded implementation gates

- Fields / Surface 3 — **PASS FOR CERTIFIED NATIVE V1 SCOPE**.
- Relations / Surface 4 — **PASS FOR CERTIFIED NATIVE V1 BASELINE**.
- Query / Surface 6 — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Admin Columns / Surface 8 — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Listings / Surface 9 — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Status / Surface 5 — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**, final closure Issue #378 / merged PR #379.
- Custom Tables / Surface 7 — **ACTIVE / NOT PASS**, bounded runway **90%**. Runtime Composition and the post-Runtime audit do not define a higher percentage milestone.

These claims do not imply `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment or release certification.

## Current dependency gate — Custom Tables

Promoted Surface 7 evidence now includes:

1. Issue #382 / PR #383 — canonical DDL-free table Definition + deterministic schema descriptor.
2. Issue #385 / PR #387 — observed-schema normalization + pure Migration Plan with R0-R4 risk/blocking semantics.
3. Issue #389 / PR #391 — trusted CT1/PT-E physical identity + strictly read-only MySQL/MariaDB schema introspection.
4. Issue #393 / PR #395 — trusted provider capability profile + deterministic execution-free provider DDL preview compiler.
5. Issue #399 / PR #402 — immutable Migration Run state machine V1.
6. Issue #400 / PR #403 — typed Precondition Contract V1.
7. Issue #401 / PR #404 — Recovery + reviewed-source Revalidation V1.
8. Issue #407 / PR #410 — Migration Run Repository Contract V1.
9. Issue #408 / PR #411 — Precondition Evaluator V1.
10. Issue #409 / PR #412 — Recovery Readiness V1.
11. Issue #413 / PR #414 — post-contract-wave exact-main audit.
12. Issue #415 / PR #416 — AI-Native Issues-first/PR-second + README closeout governance.
13. Issue #423 / PR #427 — Migration Run Transition Service V1.
14. Issue #420 / PR #428 — Precondition Probe Registry V1.
15. Issue #421 / PR #429 — Recovery Evidence Source Contract V1.
16. Issue #422 / PR #430 — Migration Execution Readiness V1.
17. Issue #431 / PR #432 — post-composition-wave exact-main audit.
18. PR #437 — Migration Run Persistence Record Codec V1.
19. PR #438 — Precondition Read-Only Probe Plan V1.
20. Issue #435 / PR #439 — Recovery Evidence Binding/Freshness V1.
21. Issue #436 / PR #440 — Execution Authorization Envelope V1.
22. Issue #441 / PR #442 — post-hardening exact-main audit.
23. PR #447 — Metadata-only Precondition Probe Adapters V1.
24. Issue #445 / PR #448 — Recovery Verification Provider Port V1.
25. Issue #446 / PR #449 — Execution Authorization Policy Adapter V1.
26. Issue #443 / PR #450 — internal Migration Run Store Schema + WPDB Repository V1.
27. Issue #453 / PR #454 — post-adapter exact-main audit.
28. Issue #459 / PR #460 — Runtime Composition Readiness V1, merged as `56cbcce2da90a11232fd65b374ad237fc1c67fb0`.
29. Issue #461 — post-Runtime Composition exact-main audit and shared-truth reconciliation in the current audit PR.

## Runtime Composition audit result

Runtime Composition Readiness V1 is accepted as a bounded **no-dispatch composition foundation**.

Promoted evidence proves:

- internal Migration Run store registration through canonical Platform migrations;
- durable site-scoped `WpdbMigrationRunRepository` construction;
- site/network execution-context checks and persisted-run identity binding;
- metadata-only precondition allowlisting with row-scan kinds failing closed;
- reviewed/current generation and preview plan/provider binding;
- recovery verification + provider/version/freshness binding;
- canonical Policy-derived capability/actor facts;
- confirmation binding to the same run, plan, readiness revision and actor;
- immutable readiness output with `execution_allowed=false`;
- exact-head PR #460 CI green across PHP Quality, Architecture Guards, Platform Compatibility, Distributable Package, CPT Runtime, Taxonomy Runtime and Status Reference Application;
- clean PR #460 review threads at promotion.

### R1/R2 execution coordinator decision

**BLOCKED / NOT AUTHORIZED.**

The remaining blocker is production trust provenance, not basic readiness composition logic:

1. `MetadataPreconditionFacts` are still supplied to the composition service by its caller instead of being obtained from a canonical server-owned read-only facts source.
2. `CustomTablesRuntimeCompositionFactory::create()` accepts caller-supplied `RecoveryVerificationProviderInterface`; provider selection is not yet constrained by a canonical server-owned/allowlisted construction boundary.
3. The factory likewise accepts caller-supplied `MigrationExecutionConfirmationProviderInterface`; the promoted concrete implementation is static/reference-oriented rather than a server-owned durable scoped confirmation source.
4. The exact composition attack matrix should be expanded before any mutation boundary consumes readiness.

An execution dispatcher must not become the place where these upstream trust questions are solved.

## Next authorized work — Trusted Runtime Evidence Sources V1

Queue priority: **960**  
Role: **SUPERVISOR_ONLY / serialized trust-boundary integration**  
Execution allowed: **false**

Goal: make production Runtime Composition obtain its readiness facts only from canonical server-owned sources while preserving zero managed-table mutation.

Allowed scope:

- typed metadata-facts provider port;
- canonical read-only production metadata-facts implementation composed from existing `WordPressCt1SchemaIntrospector`, CT1 identity/schema metadata and already-trusted provider facts;
- no row payload reads, row aggregates or row scans;
- server-owned/allowlisted recovery verification provider construction/registry using existing typed recovery contracts;
- server-owned scoped revision-bound confirmation source or equivalent canonical construction boundary that arbitrary runtime callers cannot substitute;
- tighten `CustomTablesRuntimeCompositionFactory` around trusted provider ownership;
- focused attack tests for missing/mismatched plan/run/revision/site/network/actor/capability/recovery/provider facts and R3/R4;
- preserve immutable `execution_allowed=false` output.

## Physical managed-table mutation remains blocked

The current gate does **not** authorize:

- `CREATE`, `ALTER`, `DROP`, `RENAME`, `TRUNCATE` or `dbDelta()` against managed Custom Tables;
- generic managed-table DDL dispatch through `$wpdb->query()` or shared mutation APIs;
- R1/R2 statement execution;
- R3/R4 execution;
- live row-count/null/duplicate/range/max-length scans or row payload reads;
- Backup creation or restore side effects;
- leases/locks/retry workers or Action Scheduler migration execution;
- backfill, deduplication, shadow-copy or swap;
- row CRUD/Data Source/Query provider runtime;
- CT2/PT-D or CT3 runtime/topology conversion;
- external-table adoption;
- Custom Tables admin/REST/Ability mutation surfaces;
- product parity, deployment or release.

`frameworks/Platform/Database/Migrations/**` remains the canonical generic Platform migration boundary. Custom Tables must compose shared Platform infrastructure rather than create a duplicate private engine.

## Multi-agent coordination

`AUTO-AGENT.md` and `config/coordination/agent-work-queue.json` are the claim authority.

At every invocation:

- reconcile accepted OPEN Issues first;
- reconcile eligible OPEN PRs/MRs second;
- re-read exact current main, claims and queue third;
- only then claim dependency-ready work.

Priority 960 is Supervisor-only because it changes the production trust/composition boundary. No managed-table executor branch may be speculatively pre-created. Another exact-main Supervisor audit remains mandatory after trusted evidence provenance promotes before any R1/R2 statement execution lane can be considered.

## Product / planning truth

Accepted structural scope remains **56/56 Exhaustive**, Multisite **56/56**, AI Prompt **56/56**.

Machine-readable planning lifecycle truth remains authoritative in:

- `config/product/options-bank-progress.json`;
- `config/product/atomic-option-contract-progress.json`.

The reviewed Bank surfaces and record counts are planning/research state and must not be conflated with runtime implementation completion.

## Current next action

1. Promote Issue #461 audit/shared-truth PR only after current-main reconciliation and applicable exact-head review/CI gates.
2. Re-run exact-main → Issues → PR/MR preflight after that merge.
3. Only then claim priority 960 `custom-tables-trusted-runtime-evidence-sources-v1` on its deterministic Supervisor branch.
4. Keep `execution_allowed=false` and all managed-table statement dispatch blocked throughout that lane.
5. Reconcile the complete README 56/56 dashboard again after the next stable cycle.
6. Run another exact-main Supervisor audit before any R1/R2 execution coordinator can be authorized.

Repository evidence overrides conversational memory.