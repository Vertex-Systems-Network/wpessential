# WPEssential — Backup Competitive Parity Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `BKX-001…BKX-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- Backup remains Backup Manager truth; staging/migration remains Surface 55 truth.
- Incremental/differential chain validity depends on verified base/dependency graph; “latest job success” is not restore readiness.
- Local backup success ≠ remote destination durability; remote timeout/unknown outcome must reconcile before replay.
- Direct restore/provider features remain provider capabilities, not assumptions.
- Recovery point does not roll back external providers, credentials, webhooks or remote business side effects.
- CLI/MCP/AI surfaces use the same Policy, approvals and operation identity as UI/runtime owners.

## Exact fixtures

### Group 1 — profile/capabilities
- `BKX-001` Define backup profile with stable key, scope, content classes, schedule intent, destinations, encryption and retention semantics.
- `BKX-002` Reject unknown backup content class/destination capability before plan activation.
- `BKX-003` Profile revision update requires expected revision and preserves prior restore semantics.
- `BKX-004` Disabled profile preserves prior artifacts/history but schedules no new run.
- `BKX-005` Profile capability detection distinguishes full, incremental, differential, DB-only, files-only and provider-native restore support.
- `BKX-006` Unsupported capability remains unavailable/degraded rather than silently mapped to a different backup type.
- `BKX-007` Read permission, run permission, restore permission and provider-management permission remain separate.
- `BKX-008` Export excludes credentials/keys and uses Vault references/placeholders.
- `BKX-009` Site/network scope is server-resolved; request scope IDs do not grant access.
- `BKX-010` AI/MCP may draft profile but cannot schedule/run/restore without same Policy/approval.
- `BKX-011` Unknown profile schema/version fails typed or migrates explicitly.

### Group 2 — full/incremental/differential
- `BKX-012` Full backup manifest contains all declared required DB/files/config classes for that profile or reports incomplete.
- `BKX-013` Incremental run references exact verified base and records changed-set algorithm/version.
- `BKX-014` Differential run references full base and contains all changes since that base according declared semantics.
- `BKX-015` Incremental/differential labels are not used when backend cannot prove required change semantics.
- `BKX-016` Same unchanged source produces empty/minimal incremental set without falsely generating duplicate full payload.
- `BKX-017` Source mutation during backup is handled by snapshot/window semantics and recorded consistency level.
- `BKX-018` Partial DB/files failure marks backup incomplete even if other classes succeeded.
- `BKX-019` Repeated run request with same operation identity is idempotent or safely conflicts.
- `BKX-020` Backup artifact type cannot be changed after completion without creating new artifact identity.
- `BKX-021` Restore planner computes required chain members before declaring artifact restorable.
- `BKX-022` Metrics distinguish logical source size, transferred bytes and stored compressed bytes.

### Group 3 — chain/base graph
- `BKX-023` Incremental artifact stores immutable base/parent identity and chain generation.
- `BKX-024` Missing base marks dependent incremental chain unrestorable/degraded.
- `BKX-025` Corrupt base checksum invalidates all dependent restore paths until alternate verified base exists.
- `BKX-026` Cycle in chain/dependency graph is rejected as corruption.
- `BKX-027` Retention cannot delete required base while retained dependent artifact still needs it.
- `BKX-028` Rebase/compaction creates explicit new chain and never silently rewrites dependency history.
- `BKX-029` Chain verification checks every required member hash/manifest before restore.
- `BKX-030` Orphan incremental artifact is reported as orphan, not independent restore point.
- `BKX-031` Provider object missing for one chain member is explicit degraded state.
- `BKX-032` Cross-destination mirror chain can use only destinations whose required members are present/verified.
- `BKX-033` AI/MCP cannot label chain healthy from latest artifact alone; dependency evidence is required.

### Group 4 — file/hash candidates
- `BKX-034` Changed-file detection uses declared metadata/hash algorithm and source fingerprint.
- `BKX-035` Timestamp-only change does not imply content change when hash policy proves identical bytes.
- `BKX-036` Same-size file with changed bytes is detected by checksum where checksum mode is required.
- `BKX-037` Deleted file is represented as deletion/tombstone in incremental semantics where restore requires it.
- `BKX-038` Renamed/moved file is classified according declared detector, not guessed as same object solely by size/name.
- `BKX-039` Symlink handling is explicit and cannot escape approved root.
- `BKX-040` Unreadable file produces incomplete/unknown item state, not silently skipped success.
- `BKX-041` Huge file hashing/transfers use bounded streaming.
- `BKX-042` Hash cache is invalidated when metadata/fingerprint assumptions no longer hold.
- `BKX-043` Temporary/cache files excluded by profile are listed in exclusion evidence and not required for restore completeness.
- `BKX-044` File candidate detector never mutates source content.

### Group 5 — DB incremental truth
- `BKX-045` DB incremental mode records supported backend/change-capture mechanism; unsupported DB never claims incremental truth.
- `BKX-046` Row-change capture distinguishes inserts/updates/deletes where mechanism supports them.
- `BKX-047` Table/schema change is captured separately from row changes.
- `BKX-048` Snapshot/cursor/checkpoint is bound to database identity/version and cannot cross environments.
- `BKX-049` Late/overlapping changes are deduplicated according change identity where polling/CDC semantics require it.
- `BKX-050` DB transaction/window consistency level is explicit; “backup complete” never implies stronger consistency than captured.
- `BKX-051` Unsupported storage engine/table type is reported and affects completeness according required profile.
- `BKX-052` Serialized/blob data is backed up as bytes/data, not transformed by incremental detector.
- `BKX-053` DB credential remains Vault-backed and absent from artifact metadata/logs.
- `BKX-054` Restore applies DB incrementals in exact chain order and rejects missing/out-of-order member.
- `BKX-055` AI/MCP cannot infer point-in-time recovery from ordinary incrementals unless PITR evidence/profile exists.

### Group 6 — pre-change recovery points
- `BKX-056` Pre-update recovery point plan names exact pending update/change and required backup scope.
- `BKX-057` High-risk migration preflight verifies recovery artifact can be created/stored before change proceeds where policy requires.
- `BKX-058` Recovery point completion requires durable required destinations, not merely local temp file creation.
- `BKX-059` Optional mirror failure vs required destination failure affects readiness according profile.
- `BKX-060` Recovery point operation is idempotent for same protected change identity.
- `BKX-061` Change must not proceed when mandatory recovery point remains unknown/incomplete.
- `BKX-062` Recovery point manifest pins source revision/environment before protected change.
- `BKX-063` Restoring recovery point does not claim rollback of external provider actions executed after it.
- `BKX-064` Recovery point retention prevents premature purge until protected change verification window ends.
- `BKX-065` Pre-change recovery audit links backup artifact and change plan without storing secrets.
- `BKX-066` AI/MCP may recommend/create draft recovery requirement but cannot bypass owning change approval.

### Group 7 — multi-destination fanout
- `BKX-067` One backup run may fan out to multiple destinations with required/optional role per destination.
- `BKX-068` Artifact identity remains one logical backup with per-destination replica state/provenance.
- `BKX-069` Required destination failure prevents overall durable-success state.
- `BKX-070` Optional mirror failure yields degraded mirror state while primary success remains explicit.
- `BKX-071` Retry uploads only missing/failed/unknown replicas after reconciliation.
- `BKX-072` Same destination object cannot be uploaded twice on replay where provider idempotency/object-key contract prevents it.
- `BKX-073` Destination-specific encryption/key profile is recorded without exposing keys.
- `BKX-074` Destination retention policy cannot silently delete canonical-required replica contrary to profile.
- `BKX-075` Cross-region/data-residency restrictions are checked before destination selection.
- `BKX-076` Provider quota/rate limit is isolated so one mirror does not starve all sites unfairly.
- `BKX-077` Restore planner chooses verified available replica and records which destination supplied bytes.

### Group 8 — provider/direct restore matrix
- `BKX-078` Provider capability matrix pins provider/version for upload, list, checksum, delete, direct restore, multipart and server-side copy.
- `BKX-079` Missing direct-restore capability falls back only to explicitly supported download+local restore workflow.
- `BKX-080` Provider HTTP success does not prove object durability until provider contract/checksum/list verification succeeds where required.
- `BKX-081` Direct restore target/environment is revalidated before provider restore request.
- `BKX-082` Provider restore timeout becomes unknown/reconcile-required, not automatic retry.
- `BKX-083` Provider version/schema drift blocks unsupported direct restore before side effects.
- `BKX-084` Signed URLs/tokens are scoped/expiring and redacted from logs.
- `BKX-085` Provider object checksum/etag semantics are not assumed equivalent to source hash unless documented.
- `BKX-086` Deleting provider replica requires retention/chain dependency checks.
- `BKX-087` Revoked/expired provider credentials remain auth error, not backup corruption.
- `BKX-088` AI/MCP cannot invoke direct restore from provider without high-risk restore authorization.

### Group 9 — standalone recovery
- `BKX-089` Standalone recovery package identifies compatible backup manifest/schema/runtime prerequisites.
- `BKX-090` Recovery package contains no plaintext provider/Vault secrets by default.
- `BKX-091` Recovery loader verifies package/artifact signatures/checksums before restore.
- `BKX-092` Recovery tool cannot write outside selected target roots/database.
- `BKX-093` Unsupported WordPress/PHP/DB target profile blocks restore or reports explicit compatibility risk.
- `BKX-094` Recovery can inventory available chain members and refuses incomplete chain.
- `BKX-095` Recovery progress/checkpoints are local operation evidence and do not claim external provider rollback.
- `BKX-096` Recovery failure leaves target in explicit incomplete/recovery state with logs/evidence.
- `BKX-097` Recovery package version is pinned and historical artifacts remain readable only where compatibility is proven.
- `BKX-098` Standalone recovery access requires explicit operator authentication/physical control profile; public unauthenticated restore endpoint is prohibited.
- `BKX-099` Generated recovery package is a tool artifact, not proof backup is restorable until tested later.

### Group 10 — CLI
- `BKX-100` WP-CLI list/status command is read-only and returns same canonical backup state as UI/API.
- `BKX-101` CLI run requires same capability/Policy as UI run and explicit profile/site scope.
- `BKX-102` CLI cancel distinguishes requested cancel from confirmed stopped job.
- `BKX-103` CLI restore requires elevated restore permission and explicit artifact/target confirmation profile.
- `BKX-104` CLI arguments cannot inject arbitrary paths/provider endpoints outside registered profiles.
- `BKX-105` CLI secret parameters are prohibited/redacted; credentials resolve through Vault/config owner.
- `BKX-106` CLI JSON output is schema/versioned and does not leak signed URLs/secrets.
- `BKX-107` Repeated CLI run with same operation/idempotency identity avoids duplicate logical backups.
- `BKX-108` CLI exit code distinguishes success/degraded/incomplete/unknown/validation failure.
- `BKX-109` Multisite CLI requires explicit site/network scope and cannot infer authority from shell access alone.
- `BKX-110` CLI dry-run/plan mode performs no backup/provider write and clearly labels NOT EXECUTED.

### Group 11 — MCP/AI
- `BKX-111` MCP/AI read ability can summarize profile/job/artifact health only within caller Policy.
- `BKX-112` Draft backup plan created by AI remains non-executable until normal validation/approval.
- `BKX-113` AI cannot lower retention/encryption/required-destination policy silently.
- `BKX-114` Run ability is disabled by default for AI/MCP and, when enabled, uses canonical operation identity/approval.
- `BKX-115` Restore ability is excluded by default and cannot be inferred from run permission.
- `BKX-116` AI cannot reveal provider credentials, signed URLs or encrypted artifact keys.
- `BKX-117` AI health explanation cites chain/destination/age/verification facts rather than inventing “safe” status.
- `BKX-118` AI cannot call Staging/Migration push/pull through Backup surface as hidden bypass.
- `BKX-119` AI cancellation request follows same job-state semantics and cannot claim job stopped until confirmed.
- `BKX-120` MCP principal attribution is recorded in audit without replacing authenticated principal identity.
- `BKX-121` Prompt injection in backup metadata/logs cannot grant tool authorization or alter Policy.

### Group 12 — health/provenance
- `BKX-122` Backup health score, if used, derives from age, required-destination verification, chain integrity, restore confidence and encryption/recovery readiness.
- `BKX-123` Latest successful job alone cannot produce healthy status when required chain/destination is missing.
- `BKX-124` Health inputs store timestamps/source/provenance and stale evidence degrades confidence.
- `BKX-125` Restore test evidence is distinct from static manifest verification.
- `BKX-126` Encryption enabled flag is not key-recovery proof; key availability/recovery profile is separate.
- `BKX-127` Destination provider “exists” is distinct from checksum/object readability verification.
- `BKX-128` Retention gap/chain orphan lowers health without deleting artifacts.
- `BKX-129` Health thresholds are configuration/policy, not business/authorization truth.
- `BKX-130` Historical health preserves prior evidence and does not rewrite old state after later failures.
- `BKX-131` Health report redacts protected artifact/provider details for unauthorized viewers.
- `BKX-132` AI cannot promote health score to guaranteed disaster-recovery readiness.

### Group 13 — reconcile unknown outcome
- `BKX-133` Upload timeout after possible provider commit enters `unknown/reconcile_required`.
- `BKX-134` Reconciliation lists/queries provider by deterministic object/operation identity before replay.
- `BKX-135` Provider object found with matching checksum resolves unknown to success.
- `BKX-136` Provider object found with mismatched checksum resolves to conflict/corruption, not success.
- `BKX-137` Provider cannot confirm object and contract allows safe idempotent retry only after reconciliation attempt.
- `BKX-138` Multipart upload unknown state reconciles parts/session before restart.
- `BKX-139` Delete timeout reconciles object existence before retry to avoid false deletion state.
- `BKX-140` Direct-restore timeout reconciles target/provider job state before resubmission.
- `BKX-141` Local metadata commit failure after provider success is reconciled without duplicate upload.
- `BKX-142` Reconciliation retries are bounded/backoff-aware and persist progress.
- `BKX-143` Unknown outcome cannot be shown as failed simply to simplify dashboard counts.

### Group 14 — Multisite/environment
- `BKX-144` Site-scoped backup contains only owning site data/files according topology/profile.
- `BKX-145` Network backup inventories shared Core/plugins/themes/users/network tables plus per-site data explicitly.
- `BKX-146` Same profile key on different sites has isolated jobs/artifacts/cache/provider object namespace.
- `BKX-147` Site admin cannot restore another site's backup via artifact ID alone.
- `BKX-148` Network provider credential may be delegated without exposing secret or cross-site artifact visibility.
- `BKX-149` Site clone/staging does not reuse production backup operation/idempotency/environment identity blindly.
- `BKX-150` Staging backup is environment-distinct and cannot satisfy production recovery-health claim.
- `BKX-151` Site deletion retention/export handles site-owned artifacts without deleting shared network bases still required.
- `BKX-152` Network restore plan maps site IDs/domains explicitly and avoids accidental cross-site overwrite.
- `BKX-153` Backup artifacts copied between environments require provenance/environment remap and provider-secret quarantine.
- `BKX-154` AI/MCP site-scoped principal cannot enumerate network-wide backups without network Policy.

### Group 15 — resource budgets
- `BKX-155` Large-file backup later measures streaming memory/throughput with declared hardware/storage profile.
- `BKX-156` Million-file manifest later measures enumeration/hash/index overhead and checkpointing.
- `BKX-157` Large DB backup later measures chunking/snapshot duration and source load.
- `BKX-158` Incremental detector later measures changed-set scan cost vs full backup.
- `BKX-159` Multi-destination fanout uses bounded concurrency and per-provider quotas.
- `BKX-160` Compression/encryption pipeline uses bounded temp disk/memory and aborts safely on insufficient capacity.
- `BKX-161` Network backup scheduler enforces fair site/job budgets.
- `BKX-162` Restore verification streams/checks manifests without loading entire artifact into memory where unnecessary.
- `BKX-163` Logs/metrics remain bounded under huge artifact counts and redact secrets.
- `BKX-164` Capacity preflight blocks run when mandatory destination/local temp cannot safely hold required data.
- `BKX-165` All scale/performance claims remain NOT EXECUTED until reproducible results exist.

### Group 16 — golden recovery
- `BKX-166` Golden full backup scenario proves complete declared manifest and required destination verification.
- `BKX-167` Golden incremental chain scenario verifies full base + ordered incrementals and rejects missing base.
- `BKX-168` Golden differential scenario restores full base + selected differential without pretending incremental semantics.
- `BKX-169` Golden multi-destination scenario distinguishes required-primary failure from optional-mirror degradation.
- `BKX-170` Golden provider-timeout scenario reconciles unknown upload before replay.
- `BKX-171` Golden pre-migration recovery point blocks risky change until mandatory artifact is durable.
- `BKX-172` Golden standalone recovery scenario verifies checksums/chain and reports external side effects not rolled back.
- `BKX-173` Golden Multisite scenario proves site/network backup ownership and no cross-site restore authorization leak.
- `BKX-174` Golden retention scenario never deletes base required by retained incrementals.
- `BKX-175` Golden CLI/AI scenario shows read/draft parity but no hidden run/restore privilege.
- `BKX-176` Golden disaster-recovery report accurately distinguishes static verification, restore-test evidence and provider/owner unknowns without claiming guaranteed recovery.

## Execution gate

This document specifies evidence only. **BKX executed remains 0/176.** No backup run, provider upload, restore, CLI command, benchmark, WordPress/runtime or AI/MCP execution is authorized by this protocol.