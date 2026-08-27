# WPEssential — Import / Export Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0041, ADR-0095, `docs/ARCHITECTURE/IMPORT-RUN-PTD-PTE-PHYSICAL-RECOVERY-PROFILE.md`, JobService, Backup, Safe HTTP, Multisite, ADR-0014.

## 1. Purpose

Define executable evidence required before Import / Export can claim safe planning, parsing, mapping, resumability, identity preservation, crash reconciliation, rollback truth, media handling, export privacy, Multisite or scale support.

The execution invariant is fixed:

**reviewed Plan/Dry Run + source fingerprint + trusted target scope are pinned before execution; mutable Run/Checkpoint/Identity Map/Journal are separate runtime truth; Job delivery never proves a target mutation did or did not happen.**

## 2. Runtime profile

Future certification records:
- WordPress/PHP/database versions;
- IR1/PT-D and/or IR2/PT-E runtime profile;
- source adapter/version and source format;
- target Data Source/Definition/schema generations;
- JobService/backend profile;
- private temp/artifact storage profile;
- media/offload/Safe HTTP profile where applicable;
- Backup/restore prerequisite class;
- single-site/Multisite topology;
- rollback coverage/retention profile.

## 3. Plan/source/artifact fixtures

### IM-01 — Dry Run/Plan fingerprint
Reviewed Plan pins immutable mapping/config and current source fingerprint before execution.

### IM-02 — Plan edited after Run starts
Active Run continues against pinned revision; editor changes do not mutate execution semantics silently.

### IM-03 — Source changed after review
Material source fingerprint drift blocks or requires explicit re-plan/re-review.

### IM-04 — Source fingerprint unavailable
Weaker evidence is surfaced explicitly and cannot be reported as verified unchanged source.

### IM-05 — Private source staging
Uploaded/import source is staged in private bounded storage, not executable/public plugin/theme path.

### IM-06 — Archive traversal
`../`, absolute path and equivalent archive traversal cannot escape approved extraction root.

### IM-07 — Symlink/hardlink escape
Archive link semantics cannot write/read outside approved staging policy.

### IM-08 — Archive bomb/depth/count limits
Compressed size/expanded bytes/file count/depth are bounded before uncontrolled extraction/resource exhaustion.

### IM-09 — Unsupported/corrupt source
Safe failure preserves Run truth and target state; no partial garbage mapping is reported successful.

### IM-10 — Source MIME/format mismatch
File extension alone does not grant parser trust; actual supported adapter/profile validation is applied.

## 4. Mapping/authorization/identity fixtures

### IM-11 — Declared target mapping only
Source fields can map only to registered target fields/relations/actions in reviewed Plan.

### IM-12 — Raw table/column injection
Source/request data cannot choose arbitrary database table/column/class/Ability/direct SQL primitive.

### IM-13 — Target-domain Policy/invariants
Import path cannot bypass owning Data Source/domain validation/integrity solely for speed.

### IM-14 — Wrong-site target selector
Source numeric IDs/site IDs cannot change trusted target scope.

### IM-15 — Stable source identity map
Repeated deterministic source identity resolves the intended target/lineage rather than numeric coincidence.

### IM-16 — Existing target match
Matched preexisting target is classified distinctly from import-created target and update policy is explicit.

### IM-17 — Same-source concurrent Runs
Concurrent import of same source identity cannot silently create duplicate owned targets.

### IM-18 — Source reference forward resolution
Forward/out-of-order relation references enter bounded unresolved state and reconcile deterministically.

### IM-19 — Unknown relation/reference
Unsupported reference becomes explicit conflict/skip/error per Plan, never guessed by label similarity.

### IM-20 — Administrator edit after prior import
`update_if_unchanged_since_prior_import` preserves newer local admin edits when target fingerprint diverged.

### IM-21 — Source deletion
Source omission/deletion does not delete target unless explicit high-risk sync-delete semantics were pinned/reviewed.

### IM-22 — Sensitive credential material
Password/token/Vault/provider credentials are excluded or delegated to certified owning workflow; they never enter generic Journal/log/export plaintext.

## 5. Checkpoint/crash/Job fixtures

### IM-23 — Chunk checkpoint commit
Resume boundary advances only after target changes/Identity Map needed for that checkpoint are durably reconciled.

### IM-24 — Crash before target mutation
Retry processes item normally without phantom success.

### IM-25 — Crash after target commit before Identity Map
Recovery detects/adopts valid committed target rather than creating duplicate.

### IM-26 — Crash after Identity Map before Checkpoint
Resume recognizes completed target/map and does not repeat mutation.

### IM-27 — Crash after Checkpoint before next Job enqueue
Run reconciler can schedule continuation without reprocessing committed chunk.

### IM-28 — Duplicate Job delivery
At-least-once delivery cannot duplicate valid target mutations under certified identity/idempotency contract.

### IM-29 — Job lease expiry while work may continue
New worker does not assume expired lease means target mutation failed; ownership/reconciliation protects duplicates.

### IM-30 — Pause
Pause stops new work at safe checkpoint and preserves resumable state without claiming rollback.

### IM-31 — Resume
Resume revalidates Run/source/target fingerprint assumptions and continues from last committed checkpoint.

### IM-32 — Cancel
Cancel stops future work according to safe boundary and reports already committed effects truthfully.

### IM-33 — Enqueue failure
Committed Run/checkpoint with failed continuation enqueue remains discoverable/reconcilable.

### IM-34 — Site lifecycle drain
Deleting/archiving target site prevents new chunks after destructive boundary and reconciles active Run.

## 6. Rollback/recovery fixtures

### IM-35 — R0 no automatic rollback
UI/report does not promise rollback where effects are irreversible/unsupported.

### IM-36 — R1 remove import-created record
Only still safely import-owned created records are removed; later unrelated edits/dependencies prevent unsafe deletion.

### IM-37 — R2 restore mapped fields/relations
Reverse applies only when current target matches expected post-import fingerprint; newer unrelated edits are preserved/conflicted.

### IM-38 — R3 bounded transactional reversal
Only specifically certified local domain operation is reported R3 and passes atomic reversal fixture.

### IM-39 — Mixed rollback outcome
Partially reversible import reports unresolved/conflicted/external effects; never `fully rolled back` falsely.

### IM-40 — Backup prerequisite
Broad/high-risk import requiring Backup verifies the restore-point prerequisite independently; Change Journal is not represented as disaster backup.

### IM-41 — Restore copied active Run
Restored active/queued Run becomes revalidation/reconciliation-required; copied Job rows cannot auto-resume blindly.

### IM-42 — Restore Identity Map validation
Restored map references are checked against restored target identities/fingerprints before reuse.

## 7. Media/export/privacy fixtures

### IM-43 — Remote media fetch
Certified Safe HTTP/Connection adapter enforces scheme/host/size/time/privacy policy; source cannot trigger SSRF/local-file access.

### IM-44 — Media partial failure
Failed media does not corrupt parent target or falsely mark full item success when media is required.

### IM-45 — Offloaded media commit
Remote object is marked current only after certified upload/commit semantics; credentials never enter client/log/export.

### IM-46 — Configuration export
Definition/Plan export contains portable configuration with dependencies/versioning and no runtime Run secrets/state unless explicitly requested.

### IM-47 — Data export authorization
Export reauthorizes source rows/fields for actor and cannot leak inaccessible records through counts/relations/media.

### IM-48 — Secret/private field export
Secrets/credentials/protected fields are excluded/redacted/replaced by explicit placeholders according to classification.

### IM-49 — Site export isolation
One subsite export cannot include another site's runtime/map/data/network secrets through shared IR1 tables.

### IM-50 — Import package dependency conflict
Missing/incompatible Definition/module/provider dependency yields explicit conflict/degraded Plan rather than arbitrary best-effort execution.

## 8. Scale/topology fixtures

### IM-51 — 100k/1M record profile
Controlled datasets measure throughput, memory, checkpoint latency/write amplification, map/index contention and temp storage without weakening correctness.

### IM-52 — Large relations/media mix
High fan-out references/media remain bounded and resumable with explicit failed/conflict counts.

### IM-53 — IR1 noisy-neighbor Multisite
One large site import does not cause wrong-site rows or unacceptable starvation without JobService fairness/backpressure response.

### IM-54 — IR2 per-site lifecycle/versioning
Per-site table profile proves provisioning/migration/version-skew/site-delete behavior for advertised scale.

### IM-55 — 100/1k/10k-site isolation
Run/Checkpoint/Map/Journal identities/indexes remain scope-safe under large-network fixture.

### IM-56 — Retention/cleanup
Temp artifacts/item details/Journal/Map follow separate retention classes; cleanup cannot destroy still-required lineage/recovery state.

## 9. Pass gates

Certification fails if:
- archive traversal/symlink/bomb escapes bounded staging;
- stale changed source executes as reviewed unchanged;
- source data selects arbitrary target SQL/code primitive;
- wrong-site row/resource is read/mutated;
- valid crash/retry creates duplicate target;
- concurrent same-source import silently creates duplicate ownership;
- rollback overwrites newer unrelated edits or falsely claims full success;
- copied restored active Run auto-resumes without revalidation;
- export leaks unauthorized rows/secrets/another site's data;
- remote media path enables SSRF/credential leakage.

## 10. Required future evidence report

Include:
- runtime/source/target/topology profile;
- IM-01…IM-56 pass/fail;
- source/archive security evidence;
- Dry Run/source fingerprint evidence;
- crash-window/duplicate Job results;
- Identity Map race/conflict results;
- rollback R0–R3 truth report;
- media/Safe HTTP evidence;
- export privacy/scope tests;
- IR1/IR2 scale/index/storage/cleanup measurements.

## 11. Current state

**IM fixtures executed: 0/56.**

No import/export parse, archive extraction, target mutation, DB runtime row, Job, media fetch/upload, rollback, Restore, cleanup or benchmark has been executed.

## 12. Development gate

Execution requires explicit owner consent under ADR-0014.