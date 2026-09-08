# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-08**  
Canonical audited base anchor: **`main @ 18165d325dd97d6346ad2982a8464b450eb8273d`**  
Project classification: **`ACTIVE_EXISTING_PROJECT`**  
Execution mode: **`IMPLEMENTATION_GATED`**  
Development approval: **`GOV-OWNER-CONSENT-001 ACTIVE / source scope 56/56`**

## AI-Native mandatory work-cycle order

Issue #415 / merged PR #416 remains canonical:

1. resolve exact current `main`;
2. inspect and triage **OPEN Issues first**;
3. inspect/fix/review/merge eligible **OPEN PRs/MRs second**;
4. re-read deterministic claim branches and `config/coordination/agent-work-queue.json`;
5. only then claim/start new dependency-ready development;
6. after meaningful completed work reaches a stable final state, the Supervisor reconciles README current status and its module-wise progress table/progress bars before reporting completion.

Accepted issue/PR work must not be bypassed by speculative replacement branches. Merge order follows dependencies, latest-main reconciliation, exact-head CI, review-thread cleanliness and shared-truth safety.

README percentages measure the currently approved/certified **bounded implementation milestone**, never full product parity unless machine-readable lifecycle state explicitly promotes that claim.

## Certified bounded implementation gates

- Fields / Surface 3 — **PASS FOR CERTIFIED NATIVE V1 SCOPE**.
- Relations / Surface 4 — **PASS FOR CERTIFIED NATIVE V1 BASELINE**.
- Query / Surface 6 — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Admin Columns / Surface 8 — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Dynamic Listings / Surface 9 — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Status Manager / Surface 5 — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**, final closure Issue #378 / merged PR #379.
- Custom Tables / Surface 7 — **ACTIVE / NOT PASS**, bounded runway **90%**. The post-hardening wave advances evidence but does not define a new percentage milestone.

These bounded passes/progress values do not imply full Options Bank parity, `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment or release certification.

## Current dependency gate — Custom Tables

Promoted Surface 7 evidence includes:

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
12. Issue #415 / PR #416 — AI-Native issue-first/PR-second startup + README progress closeout governance.
13. Issue #423 / PR #427 — Migration Run Transition Service V1.
14. Issue #420 / PR #428 — Precondition Probe Registry V1.
15. Issue #421 / PR #429 — Recovery Evidence Source Contract V1.
16. Issue #422 / PR #430 — Migration Execution Readiness V1.
17. Issue #431 / merged PR #432 — post-composition exact-main audit and hardening-wave authorization.
18. PR #437 — Migration Run Persistence Record Codec V1.
19. PR #438 — Precondition Read-Only Probe Plan V1.
20. Issue #435 / PR #439 — Recovery Evidence Binding/Freshness V1, promoted at `08f91e5c617b4e2b8de1f253568f3ddfc0802f96`.
21. Issue #436 / PR #440 — Execution Authorization Envelope V1, promoted at `18165d325dd97d6346ad2982a8464b450eb8273d` after latest-main reconciliation and 4/4 exact-head CI.
22. Issue #441 — post-hardening exact-main audit and shared-truth reconciliation; effective when promoted.

Provider statements remain immutable review previews with `execution_allowed=false`. The hardening wave adds deterministic run-record serialization, bounded precondition probe plans, recovery evidence binding/freshness and a post-readiness authorization envelope. It still does not execute provider DDL, scan live row data, trigger Backup side effects, schedule migration work or mutate managed target tables.

`frameworks/Platform/Database/Migrations/**` is the canonical generic Platform migration boundary. Custom Tables must compose `MigrationRegistry`, `MigrationRunner`, `MigrationCoordinator` and migration-state infrastructure rather than build a duplicate private migration engine.

## Next authorized work after Issue #441 promotes

### A — Internal Migration Run Store Schema + WPDB Repository V1 — serialized Supervisor integration

- internal WPE metadata table only;
- bootstrap/versioning through canonical Platform migration infrastructure;
- canonical `MigrationRunRepositoryInterface` create/get/CAS semantics;
- use promoted persistence record codec for storage validation;
- explicit site/network scope;
- prepared values and controlled identifiers;
- focused MySQL/MariaDB integration evidence.

This lane may not execute a generated Custom Tables provider migration preview or mutate a managed target table.

### B — Metadata-Only Precondition Probe Adapters V1 — parallel Worker

Allowed first concrete probes are metadata-only: table existence/non-existence, trusted schema/column metadata match and already-observable provider capability facts. Live row-count/null/duplicate/range/max-length scans remain blocked.

### C — Recovery Verification Provider Port V1 — parallel Worker

Read/verify-only integration for an already-existing recovery artifact may produce bounded verification facts for the promoted binding/freshness model. Snapshot creation, chargeable provider side effects, restore and secret/provider payload persistence remain blocked.

### D — Execution Authorization Policy Adapter V1 — parallel Worker

Compose canonical WPE Policy/capability evaluation into the promoted execution authorization envelope so production boundaries do not trust caller-supplied authorization booleans. The adapter only produces bounded facts and cannot dispatch SQL, mutate capabilities, schedule jobs, expose public mutation or override R3/R4 denial.

### Physical managed-table mutation remains blocked

The current gate does not authorize:

- dispatching `CREATE`, `ALTER`, `DROP`, `RENAME`, `TRUNCATE` or `dbDelta()` against managed Custom Tables;
- generic managed-table DDL execution through `$wpdb->query()` or shared database mutation APIs;
- R3/R4 execution;
- live row-count/null/duplicate/range/max-length scans;
- Backup creation or restore side effects;
- leases/locks/retry workers or Action Scheduler migration execution;
- backfill, deduplication, shadow-copy or swap;
- row CRUD/Data Source/Query provider runtime;
- CT2/PT-D or CT3 runtime/topology conversion;
- external-table adoption;
- Custom Tables admin/REST/Ability mutation surfaces;
- product parity, deployment or release.

## Multi-agent coordination

`AUTO-AGENT.md` and `config/coordination/agent-work-queue.json` are the claim authority.

At every invocation:

- reconcile OPEN Issues first;
- reconcile OPEN PRs/MRs second;
- re-read current main/claims/queue;
- only then claim dependency-ready work.

After Issue #441 reconciliation promotes, Lane A is serialized/Supervisor-owned because it touches durable storage ownership and Platform migration composition. Lanes B, C and D are non-overlapping Worker lanes and may run in parallel.

No managed-table DDL executor branch may be speculatively pre-created. Another exact-main Supervisor audit is mandatory after A–D promote before an R1/R2-only managed-table execution coordinator may open.

## Product / planning truth

Accepted structural scope remains **56/56 Exhaustive**, Multisite **56/56**, AI Prompt **56/56**.

Machine-readable planning lifecycle truth remains authoritative in:

- `config/product/options-bank-progress.json`;
- `config/product/atomic-option-contract-progress.json`.

The reviewed Bank surfaces and record counts are planning/research state and must not be conflated with runtime implementation completion.

## Current next action

1. Promote Issue #441 shared-truth reconciliation after exact-head review/CI requirements.
2. Re-run mandatory exact-main → Issues → PR/MR preflight.
3. Claim Lane A only as serialized Supervisor integration and Lanes B/C/D only on their deterministic non-overlapping branches.
4. Merge each only after latest-main reconciliation, exact-head CI and clean review threads.
5. Reconcile README module progress again after the next stable cycle.
6. Run another exact-main Supervisor audit before any managed-table DDL execution coordinator is authorized.

Repository evidence overrides conversational memory.