# WPEssential — Import Run PT-D/PT-E Physical & Recovery Profile

Status: **Phase 0 paper physical/recovery profile / no import, file, DB or Job execution authorized**  
Date: 2026-08-28  
Related: Import Run Checkpoint Rollback Runtime, JobService ADR-0083, Site Lifecycle ADR-0075, Backup ADR-0084, ADR-0014.

## Purpose

Define the physical ownership and recovery baseline for large resumable imports without confusing reviewed Plan configuration with mutable execution history or promising universal rollback.

## Domain split

### Definition Repository owns configuration
- reusable Import Mapping/Plan Definition where product stores it as reusable configuration;
- source-adapter configuration references;
- mapping rules/fidelity policy;
- target Definition dependencies.

### Import Runtime owns execution truth
- Import Run;
- immutable execution pin/fingerprint references;
- durable checkpoint/chunk state;
- source→target Identity Map;
- Change Journal/compensation metadata;
- verification/reconciliation state;
- bounded item/conflict/error state where required.

### JobService owns execution opportunity
Job/Attempt records schedule chunks, retries and continuations but are not the Import source of truth.

### Protected temporary artifact storage owns bytes
Source archives/files/media staging live in bounded private temporary storage/object references, not giant DB blobs in Import Run rows.

## Physical profiles

### IR1 — PT-D shared scoped Import Runtime — first baseline

One shared WPE runtime table family stores site-owned Import Runs/checkpoints/maps/journal with explicit site/network scope.

Advantages:
- one migration/repository path;
- centralized run diagnostics/retention;
- easier network health/admin discovery;
- avoids per-site import-runtime table proliferation;
- composes with shared JobService and lifecycle coordinator.

Hard requirements:
- every site-owned runtime row carries trusted scope;
- Run/Map/Journal child ownership cannot be resolved by numeric ID alone across sites;
- composite uniqueness/idempotency includes scope where needed;
- Site Backup/export/delete/clone touches only target-site rows;
- one noisy import cannot starve unrelated sites without backpressure/fairness controls.

### IR2 — PT-E per-site Import Runtime — mandatory comparison

Per-site table family with same logical entities.

Benefits:
- physical site isolation;
- local large import may avoid shared index/noisy-neighbor pressure;
- site Backup/delete mapping can be simpler.

Costs:
- table/migration/provisioning/version-skew overhead across large networks;
- network diagnostics require bounded enumeration;
- Job/backend references must still restore correct site context.

IR2 is mandatory evidence, not default approval.

## Logical store family

### A. Import Run
Candidate hot metadata:
- numeric physical ID;
- run UUID;
- trusted scope;
- Plan revision/fingerprint;
- source adapter/version;
- source fingerprint;
- target platform/schema generation snapshot;
- actor/principal reference;
- state;
- current checkpoint generation;
- counters summary;
- rollback coverage class;
- retention class;
- created/started/finished/reconciled timestamps;
- safe error/correlation metadata.

Do not store source archive bytes or entire normalized source dataset here.

### B. Checkpoint/Chunk State
Candidate:
- Run ID;
- monotonically ordered chunk/checkpoint ID;
- source cursor/stable record boundary;
- processed/success/skip/fail counts;
- last committed target-change boundary;
- Identity Map generation;
- dependency queue summary/reference;
- error-threshold state;
- Job correlation/reference;
- committed timestamp;
- state/fingerprint.

Resume uses last committed checkpoint only.

### C. Identity Map
Candidate key:
`scope + source adapter/domain/type + source stable identity + target domain/type`
plus run/import lineage where update/re-import semantics require it.

Stores:
- source identity/fingerprint;
- target stable identity/local ID;
- mapping status;
- created-by-import vs matched-preexisting;
- target fingerprint/version;
- first/last Run references;
- timestamps.

Raw numeric source ID never becomes target identity by coincidence.

### D. Change Journal
Stores minimal compensation data:
- Run/chunk;
- target identity;
- operation class;
- before fingerprint + only bounded value subset/reference required for supported reversal;
- after fingerprint;
- import-owned field/relation set;
- reversibility class R0–R3;
- timestamp/state.

No plaintext secrets, full user credential records or giant row snapshots merely for rollback convenience.

### E. Item/Conflict State — optional/bounded

Persist per-item rows only when required for:
- resumability;
- unresolved reference;
- conflict/error review;
- reconciliation.

Successful high-volume items can be compacted after verified reconciliation according to retention policy if Identity Map/Journals preserve required lineage.

## Run pinning

A Run pins:
- immutable Plan revision/hash;
- source fingerprint;
- target schema/Definition generations;
- source adapter/profile version;
- site/network scope;
- execution/runtime profile version;
- required Backup/confirmation reference where risk policy demands.

Editing the reusable Plan does not mutate an active Run.

## Source fingerprint gate

Before execution/re-entry:
- compare current available source fingerprint to reviewed Dry Run/Run pin;
- materially changed source blocks or requires explicit re-plan/re-review;
- unavailable fingerprint support is reported as weaker evidence, not silently treated as unchanged.

## Crash/reconciliation invariant

The critical ambiguous window is:
`target mutation committed → Identity Map/Checkpoint not yet committed`.

Recovery must:
1. detect/re-read deterministic source identity;
2. query Identity Map/target ownership/fingerprint;
3. determine whether target mutation already happened;
4. adopt/reconcile it if valid;
5. never blindly create a duplicate;
6. advance checkpoint only after durable reconciliation.

Job redelivery/lease expiry does not imply previous target mutation failed.

## Identity Map concurrency

Concurrent/repeated Runs using the same certified source identity cannot create duplicate target ownership silently.

Future implementation must compare:
- DB uniqueness;
- transaction/lock/recheck;
- target-domain idempotency key;
- existing target fingerprint/version.

Conflicting independent imports produce explicit conflict/lineage state.

## Update/re-import semantics

For preexisting/imported targets:
- update only according to pinned field policy;
- `update_if_unchanged_since_prior_import` compares stored prior-after/current fingerprint;
- administrator edits are not overwritten silently;
- source deletion never implies target deletion unless explicit high-risk sync-delete mode is pinned.

## Rollback/recovery truth

Rollback classes remain:
- R0 no automatic rollback;
- R1 remove import-created records only when still safely import-owned;
- R2 restore mapped fields/relations only when current state matches expected post-import fingerprint;
- R3 verified bounded domain transaction reversal.

Run state cannot say full rolled-back if unresolved external/irreversible/conflicted effects remain.

Broad/high-risk import may require verified Backup separately; item journal does not replace disaster recovery.

## JobService boundary

Import Runtime creates logical continuation intent/checkpoint; JobService schedules bounded chunks.

Requirements:
- enqueue failure after Import checkpoint/Run commit is reconcilable;
- duplicate Job cannot duplicate valid target mutation under certified identity/idempotency contract;
- pause/cancel stops new work at safe checkpoint but does not imply rollback;
- site lifecycle drain prevents new chunk dispatch after destructive boundary;
- backend Job cleanup cannot erase Import Run truth.

## Temp/source artifact profile

Archive/source files:
- private temp path/object;
- stable Run-scoped opaque reference;
- size/count/depth limits;
- path traversal prevention;
- no extraction into executable plugin/theme/public directories;
- MIME/type validation;
- retention/cleanup state;
- optional content hash/fingerprint;
- encrypted/protected storage where classification requires.

Remote source/media uses Safe HTTP/Connections and is not embedded in generic Import log.

## Index families to benchmark

Import Run:
- scope + state + updated/physical ID;
- Run UUID lookup;
- active/recoverable runs by scope.

Checkpoint:
- Run + checkpoint/chunk sequence;
- Run + state/current generation.

Identity Map:
- scope + source adapter/domain/type + source stable key;
- target identity reverse lookup;
- Run/import lineage where needed.

Journal:
- Run + reverse operation/chunk order for rollback planning;
- target identity for ownership/conflict checks;
- reversibility class where admin filtering requires.

Do not index full payload/error text.

## Retention

Separate policies:
- active/recoverable Run and latest checkpoint: retain while operationally needed;
- verified successful item details: compactable;
- Identity Map: can outlive individual Run where re-import/reference semantics need continuity;
- Change Journal: retained only for advertised rollback/recovery window and privacy/legal policy;
- source temp artifacts: shortest practical retention;
- conflicts/errors: longer review window than ordinary successful item details where appropriate.

Deleting a Run UI record cannot silently destroy Identity Map still required by live migrated relations or future certified re-import semantics.

## Multisite lifecycle

Site is default scope.

IR1:
- site deletion/archive drains active Runs first;
- scoped rows are retained/removed according to import/domain policy;
- shared table remains;
- Site Backup includes only site-owned Import metadata if recovery policy includes active/history state.

IR2:
- per-site table lifecycle is explicit and versioned;
- table drop is not automatic until retention/recovery policy permits;
- large-network upgrades detect missing/outdated tables.

Network import is a separate explicit mode; it cannot be obtained by omitting `site_id`.

## Restore semantics

Restoring database/runtime history must not blindly resume copied active/terminal import work.

On Restore:
- terminal reconciled Runs remain historical;
- active/queued/waiting Runs become `revalidation_required`/equivalent until source artifact, scope, Plan, target fingerprints and Job state are reconciled;
- stale copied Job rows are not trusted as continuation authority;
- Identity Map is revalidated against restored target identities;
- external source/provider changes are treated as potentially divergent.

## Security gates

Reject any profile if:
- wrong-site Run/Map/Journal row can be read or mutated by ID collision;
- source archive can path-traverse/extract executable content into unsafe location;
- source data can choose raw target table/column/Ability outside mapping registry;
- Import bypasses target-domain authorization/integrity through direct SQL shortcut;
- sensitive credentials/secrets enter Journal/log/temp export plaintext;
- stale source fingerprint executes as if reviewed unchanged;
- rollback overwrites newer unrelated target edits.

## Future executable evidence — NOT AUTHORIZED

Datasets:
- 100k and 1M records;
- large relations/media mixes;
- 100/1k/10k-site operational topology;
- one noisy large site.

Crash/race cases:
- before target mutation;
- after target commit before Identity Map;
- after Map before Checkpoint;
- after Checkpoint before next Job enqueue;
- duplicate Job delivery;
- concurrent same-source import;
- concurrent administrator target edit;
- pause/cancel/site delete;
- Restore with copied active Run.

Measure:
- checkpoint latency/write amplification;
- Identity Map lookup/insert contention;
- throughput/memory;
- storage/index growth;
- rollback-plan generation;
- cleanup/retention throughput;
- IR1 noisy-neighbor behavior;
- IR2 table/provision/version-skew cost.

Correctness requirements:
- duplicate target created after valid crash/retry fixture: **0**;
- wrong-site mutated/read rows: **0**;
- falsely reported full rollback: **0**.

Executed Import physical/recovery fixtures: **0**.

## Paper recommendation

Use **IR1/PT-D shared scoped Import Runtime** as the first physical benchmark because it centralizes durable Runs, checkpoints, Identity Maps and Journals while avoiding per-site table proliferation. Keep **IR2/PT-E mandatory** because large/private site imports may benefit from stronger physical isolation.

The selection cannot weaken crash reconciliation, rollback truth, target-domain invariants or Multisite scope.