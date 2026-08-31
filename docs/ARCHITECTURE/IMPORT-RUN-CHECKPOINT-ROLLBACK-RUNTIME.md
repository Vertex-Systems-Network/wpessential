# WPEssential — Import Run, Checkpoint, Identity Map & Rollback Runtime

Status: **Phase 0 paper architecture / no implementation authorized**  
Related: Import/Export exhaustive spec, Migration Compatibility Plan, Source Adapter Registry, Field Migration Matrix, Job Service, Backup, Connections.

## 1. Separation

Import architecture separates:

1. **Source Adapter** — discovers/parses source into neutral IR.
2. **Import Mapping/Plan** — user-reviewed target mappings and fidelity decisions.
3. **Dry Run Result** — validation/impact/conflicts before mutation.
4. **Import Run** — immutable execution intent pinned to source + Plan fingerprint.
5. **Run Item/Chunk State** — durable resumable progress.
6. **Source→Target Identity Map** — stable references for relations/media/updates/re-runs.
7. **Change Journal** — enough before/after metadata for supported compensation/revert.
8. **Verification/Reconciliation** — proves target state after run.

Source adapters do not write target tables directly.

## 2. Import Plan

Plan contains:
- source adapter/version;
- source artifact/API snapshot identity;
- selected domains/entities;
- field/type mappings;
- state/status mappings;
- relation mapping;
- user/entity matching keys;
- create/update/skip/conflict policies;
- media strategy;
- fidelity classifications;
- missing dependency handling;
- error thresholds;
- privacy/security rules;
- post-run verification;
- source-deactivation readiness checks optional.

Plan is reviewable/exportable configuration, not execution log.

## 3. Source fingerprint

Before dry-run/execution, derive source fingerprint from available stable metadata:
- file hash/size/version for artifact import;
- provider export ID/version;
- source site/plugin/schema/version + snapshot timestamp;
- source row/count/checksum samples where practical.

Execution compares against dry-run fingerprint.

Materially changed source requires new/updated dry-run; do not execute stale reviewed plan silently.

## 4. Dry Run

Dry Run performs no target mutations.

Outputs:
- records discovered;
- creates/updates/skips/conflicts;
- mapping fidelity counts;
- unsupported/lossy fields;
- duplicate/missing identities;
- relation/media dependencies;
- authorization/module/license/dependency blockers;
- storage/space/time estimate where possible;
- destructive implications;
- expected target counts;
- rollback/compensation coverage class.

Dry Run has fingerprint tying source + plan + target schema generations.

## 5. Execution pinning

Import Run pins:
- Plan revision/hash;
- source fingerprint;
- target WPE schema/platform versions;
- target Definition revisions needed;
- start actor/site/network scope;
- Job/runtime version;
- confirmation/backup reference if required.

Editing Plan while run is active does not mutate run behavior.

## 6. Run states

Candidate:
- prepared;
- waiting_confirmation;
- queued;
- running;
- paused;
- waiting_dependency;
- cancelling;
- cancelled;
- completed;
- completed_with_warnings;
- failed_recoverable;
- failed_manual_review;
- verifying;
- reconciled;
- rollback_planned;
- rollback_running;
- rolled_back_partial/full where accurately provable.

Never label `rolled_back` if irreversible/external side effects remain.

## 7. Chunk/checkpoint model

Large run processes bounded chunks.

Checkpoint stores:
- last durable source cursor/record identity;
- chunk number;
- processed/success/skipped/failed counts;
- identity-map generation;
- current dependency queue;
- last committed target change boundary;
- error threshold state;
- Job correlation ID.

Resume starts from last **committed** checkpoint, not last UI progress message.

## 8. Item state

Per-source item status may be persisted only when needed for resume/audit/repair:
- pending;
- normalized;
- mapped;
- created;
- updated;
- skipped;
- conflict;
- failed;
- waiting_reference;
- verified.

High-volume imports may compact old successful item state after reconciliation according to retention policy; errors/conflicts remain reviewable.

## 9. Identity map

Canonical source→target map records:
- run/import lineage;
- source adapter/domain;
- source entity type;
- source stable ID/key;
- target Data Source/type;
- target stable ID/UUID/local ID;
- mapping status;
- created-by-import vs preexisting-matched;
- version/fingerprint;
- timestamps.

Used for:
- relations;
- media references;
- update/re-import;
- duplicate avoidance;
- rollback ownership;
- diagnostics.

Numeric source IDs never become target IDs just because values match.

## 10. Matching/upsert

Target matching modes must be explicit:
- WPE stable UUID;
- certified external source identity map;
- unique email for user only under approved semantics;
- slug/key where uniqueness/domain semantics prove safe;
- selected custom unique key;
- manual mapping.

No fuzzy label/title match for destructive update by default.

Upsert requires unique deterministic match key and conflict behavior.

## 11. Write path

Every target mutation goes through owning module/Data Source API/Ability with import context:
- validation;
- Policy/privileged import capability;
- type normalization;
- relation integrity;
- Status transition/import mode;
- audit/source lineage;
- cache invalidation.

Import cannot bypass module invariants by direct SQL unless a dedicated internal bulk loader is explicitly part of that module's accepted migration architecture and produces equivalent validation/integrity.

## 12. Dependency ordering

Candidate phases:
1. definitions/config prerequisites;
2. independent root entities;
3. users/terms/media or mapped references according to domain;
4. dependent records;
5. relations/pivots;
6. derived projections/indexes;
7. verification/reconciliation.

Cycles use deferred reference resolution.

Missing required reference results in conflict/wait/skip policy, not silently set random target ID.

## 13. Media/imported remote files

Remote media retrieval uses Connections Safe HTTP/SSRF policy.

Rules:
- HTTP(S) safe public target by default;
- bounded size/time;
- MIME/extension verification;
- no internal/private URL SSRF;
- redirects revalidated;
- duplicate/media identity strategy;
- file checksum/source trace;
- failure does not create fake successful attachment.

## 14. Files supplied in archives

Archive parser:
- path traversal prevention;
- file count/size/depth limits;
- no executable extraction into plugin/theme directories through ordinary data import;
- MIME/extension validation;
- temp private storage;
- cleanup on completion/failure.

## 15. Update semantics

For matched existing target, per field/record policy:
- replace mapped value;
- keep existing;
- update only if target still equals prior imported value;
- merge typed collections where defined;
- manual conflict.

Avoid overwriting administrator changes on re-import without explicit policy.

## 16. Deletes

Source missing record does **not** imply target delete by default.

Sync-delete is a separate high-risk mode requiring:
- authoritative source declaration;
- imported ownership/identity map;
- preview count/list;
- relation/content impact;
- backup/rollback coverage;
- confirmation.

## 17. Change Journal

For mutations with rollback support record minimal compensation data:
- target identity;
- operation create/update/relation;
- before fingerprint/value subset needed to revert;
- after fingerprint;
- import-owned fields/relationships;
- timestamp/chunk;
- reversibility class.

Do not store plaintext secrets merely to enable rollback.

## 18. Rollback classes

### R0 — No automatic rollback
External/irreversible side effect or insufficient before state.

### R1 — Delete import-created records only
Safe when record is still import-owned/unchanged and dependencies permit.

### R2 — Restore import-modified mapped fields/relations
Only if target still matches expected post-import fingerprint; conflicts require review.

### R3 — Verified domain transaction rollback
Only for local bounded operations where module explicitly proves transactional reversal.

UI reports coverage before execution.

## 19. Rollback procedure

1. build rollback Plan from Change Journal;
2. compare current target fingerprints;
3. mark conflicting newer edits;
4. preview records/actions;
5. require capability/confirmation;
6. apply reverse actions in dependency-safe order;
7. verify;
8. report full/partial/conflicts honestly.

Never blind restore stale whole rows/options when unrelated changes occurred.

## 20. Backup integration

High-risk imports can require verified Backup Set/restore point before mutation according to risk/size.

Backup is recovery layer beyond item-level compensation.

Import “rollback available” does not replace full-site backup for broad destructive migration.

## 21. Pause/cancel

Pause stops dispatch after current safe checkpoint.

Cancel:
- stops new chunks;
- does not imply completed changes are undone;
- offers separate rollback Plan where coverage exists;
- cannot safely interrupt atomic module operation halfway if adapter does not support cancellation.

## 22. Error thresholds

Configurable candidates:
- continue individual validation errors up to threshold;
- stop immediately on schema/security/integrity error;
- pause on provider rate limit/dependency outage;
- abort if failure ratio/count exceeds plan.

Security/data-corruption class errors are non-skippable by casual “continue anyway”.

## 23. Idempotent resume

Resume may repeat last uncommitted chunk. Therefore writes need deterministic identity/idempotency via identity map and target API.

A crash after target create but before checkpoint must reconcile created entity rather than duplicate it.

## 24. Verification

After execution:
- target counts;
- source→target map completeness;
- required references/relations;
- field checksums/samples;
- status/state mappings;
- missing media/files;
- WPE definition health;
- access/permission behavior for migrated private data;
- provider/billing reconciliation where relevant.

Only then mark reconciled/ready for optional source deactivation.

## 25. Source deactivation readiness

WPE never auto-uninstalls/deletes competitor plugin after import.

Readiness report can say:
- target verified;
- unsupported/lossy items remain;
- runtime references still point to source plugin;
- billing/provider still source of truth;
- templates/shortcodes remain;
- safe next manual action.

## 26. Privacy

Import logs avoid full sensitive values.

Artifacts/temp files have retention/cleanup.

Identity maps/provider refs classified personal where applicable.

Imported data inherits target privacy policies; source classification cannot be silently downgraded.

## 27. Observability

Run metrics:
- records/bytes/chunks;
- throughput;
- creates/updates/skips/conflicts/failures;
- retries;
- media downloads;
- wait/reconciliation time;
- rollback coverage;
- correlation/run ID.

No raw secret/provider payload in generic log.

## 28. Future executable evidence — NOT AUTHORIZED

After explicit consent:
- 100k/1M record chunk/resume;
- crash between write/checkpoint;
- deterministic re-run/update;
- relation cycles;
- remote media SSRF/size/file safety;
- concurrent target edits then rollback;
- create/update/delete rollback classes;
- ACF/SCF/Meta Box/JetEngine/CPT UI fixtures;
- Membership migration/provider source mappings;
- backup integration;
- low memory/time limits;
- multisite.

## Paper recommendation

Accept durable **Plan + Dry Run fingerprint + Import Run + Checkpoints + Identity Map + bounded Change Journal + explicit rollback classes**, rather than a one-request CSV loop or fake universal rollback.