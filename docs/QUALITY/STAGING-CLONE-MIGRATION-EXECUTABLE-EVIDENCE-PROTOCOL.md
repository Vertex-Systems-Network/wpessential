# WPEssential — Staging, Clone & Migration Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `STG-001…STG-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- Staging/clone/migration ≠ backup; DB snapshot ≠ full backup; clone ≠ same environment/entity identity.
- Production credentials, webhook subscriptions, provider IDs, payment/email/analytics endpoints and live side-effect authority must not be copied blindly.
- Copy success ≠ verified cutover; transport success ≠ application correctness; local restore ≠ remote-provider rollback.
- URL/path replacement is serialization-aware and scoped; blind string replacement is prohibited.
- Push/pull ownership and conflict policy are explicit; production overwrite is never inferred from staging freshness.
- Multisite conversion requires explicit topology/ID/domain mapping and cannot be guessed.
- Privacy/redaction may alter non-production copies and must be recorded as intentional divergence.

## Exact fixtures

### Group 1 — environment identity/topology
- `STG-001` Register environment with stable environment ID, class, site/network topology, canonical URL roots and ownership metadata.
- `STG-002` Two environments with same domain label but different IDs remain distinct; name alone is not identity.
- `STG-003` Production/staging/development/local class is explicit and environment class never grants authorization by itself.
- `STG-004` Environment fingerprint records WordPress/site/network identifiers, DB/files roots and configuration provenance without secrets.
- `STG-005` Topology distinguishes single-site, Multisite subdirectory/subdomain and network/site scope before copy planning.
- `STG-006` Environment identity drift after domain/DB/root change is detected before transfer.
- `STG-007` Clone target receives new environment identity even when copied from source byte-for-byte.
- `STG-008` Importing environment definition never imports live credentials/provider secrets.
- `STG-009` Caller-supplied environment/site ID is re-resolved server-side and is not authority.
- `STG-010` AI/MCP may draft topology/plan but cannot create/connect live environments outside Policy.
- `STG-011` Unknown topology/schema version blocks plan execution until explicit migration/compatibility mapping exists.

### Group 2 — staging creation
- `STG-012` Staging creation plan names source, target infrastructure, copy scope, exclusion/redaction policy and recovery prerequisites.
- `STG-013` Target path/domain/database collision is detected before any write.
- `STG-014` Existing target requires explicit replace/merge/new-target strategy; default is non-destructive.
- `STG-015` Staging creation refuses to inherit production environment ID.
- `STG-016` Initial DB copy pins source snapshot/window semantics and records whether copy is transactionally consistent.
- `STG-017` Initial file copy records included/excluded roots and source fingerprints/manifests.
- `STG-018` Config bootstrap disables/quarantines production-only side effects before application activation.
- `STG-019` URL/path transform dry run lists exact domains/paths/serialized fields to change.
- `STG-020` Failed staging creation leaves target in explicit incomplete/quarantined state and never reports ready.
- `STG-021` Repeated creation request with same operation identity is idempotent or conflicts safely.
- `STG-022` Staging becomes usable only after target verification criteria pass; transport completion alone is insufficient.

### Group 3 — environment side-effect safety
- `STG-023` Production payment gateway credentials/webhooks are disabled/quarantined in staging by default.
- `STG-024` Production email/SMS/push transport is blocked or redirected to safe sink unless explicitly certified for staging.
- `STG-025` Analytics/ads/tracking production identifiers are disabled/review-required to avoid polluting live data.
- `STG-026` Search indexing/robots environment policy prevents accidental public indexing where configured.
- `STG-027` Cron/queue/scheduled jobs with external side effects are disabled or environment-gated before target activation.
- `STG-028` Woo orders/subscriptions/refunds in copied data cannot trigger live provider actions merely from staging load.
- `STG-029` External sync/webhook connectors remain paused until target-specific connection mapping is approved.
- `STG-030` License/provider identity copied from production is classified review-required and not automatically activated.
- `STG-031` Admin environment cue clearly distinguishes production vs staging without serving as authorization.
- `STG-032` Side-effect quarantine status is verified before staging readiness is declared.
- `STG-033` AI/MCP cannot re-enable quarantined production side effects without same privileged owner approval.

### Group 4 — DB/files copy scope
- `STG-034` Copy plan can include full DB, selected tables/data classes or schema-only with explicit semantics.
- `STG-035` Table prefix/custom tables are discovered from target/source configuration rather than assumed `wp_`.
- `STG-036` Excluded high-risk tables/data classes remain excluded and are reported in manifest.
- `STG-037` File copy scope separates WordPress Core, plugins, themes, uploads, private storage and generated caches.
- `STG-038` Cache/temp/log directories can be regenerated/excluded without being treated as source business data.
- `STG-039` Symlinked paths obey approved roots and cannot escape copy boundary.
- `STG-040` File manifest records path/size/hash or equivalent fingerprint sufficient for later verification.
- `STG-041` DB copy failure/partial table copy yields incomplete state with exact affected tables.
- `STG-042` File copy partial failure yields exact missing/failed artifact list and does not report target complete.
- `STG-043` Copy process preserves source read-only intent where requested and does not mutate source as side effect.
- `STG-044` Protected/private storage is copied only under explicit classification/encryption/access policy.

### Group 5 — serialization-safe URL/path transforms
- `STG-045` Plain scalar URL values transform only within declared fields/tables/files and exact source→target mapping.
- `STG-046` PHP-serialized values are parsed/re-serialized safely so string lengths remain valid.
- `STG-047` JSON values are structurally transformed without corrupting escaping/types.
- `STG-048` Binary/blob fields are skipped unless a certified parser owns their format.
- `STG-049` GUID/content identifiers follow explicit WordPress semantics and are not blindly rewritten.
- `STG-050` Email/username/content text containing source domain is not changed unless field/rule explicitly targets it.
- `STG-051` URL transform respects scheme/host/path boundary and avoids substring collisions such as `old.example` inside unrelated text.
- `STG-052` Filesystem path replacement normalizes separators/root mapping and does not permit traversal.
- `STG-053` Dry run count/fingerprint can be compared to actual transformed rows/files before acceptance.
- `STG-054` Transform failure for one value is reported/quarantined according all-or-nothing/per-item plan; no silent truncation.
- `STG-055` Search/Replace engine owns reusable transform semantics; staging does not invent a second unsafe replacement engine.

### Group 6 — remote transfer/checkpoints
- `STG-056` Remote transfer connection pins authenticated source/target endpoints and environment IDs.
- `STG-057` File chunks include checksum/offset so retry does not duplicate/corrupt transferred file.
- `STG-058` DB/data chunk checkpoint is bound to source snapshot/query/version and target operation identity.
- `STG-059` Retry resumes from validated checkpoint rather than restarting destructive target apply blindly.
- `STG-060` Timeout after remote write is unknown/reconcile-required where target may have accepted data.
- `STG-061` Provider/transport success is not target-application verification.
- `STG-062` Transfer encryption/auth requirements are explicit; plaintext downgrade is prohibited by secure profile.
- `STG-063` Redirect/DNS/private-network behavior is bounded for connector endpoints and cannot become SSRF.
- `STG-064` Rate limits/backpressure prevent one site/transfer from exhausting shared worker/storage capacity.
- `STG-065` Remote credentials remain Vault-backed and are absent from manifests/logs/export.
- `STG-066` Checkpoint schema/version mismatch blocks resume and requires safe restart/reconciliation strategy.

### Group 7 — clone identity/secrets
- `STG-067` Clone gets new environment ID and new operation/site identity mappings where required.
- `STG-068` WordPress salts/keys/session security material follows clone policy and production-secret reuse is prohibited where unsafe.
- `STG-069` OAuth/API/provider tokens are quarantined placeholders by default, not active copied credentials.
- `STG-070` Webhook subscription IDs/secrets/endpoints are not reused as if clone were original environment.
- `STG-071` Payment provider customer/subscription/order IDs may remain copied data references but cannot authorize provider actions from clone.
- `STG-072` Email/domain verification/API callback identities are review-required before activation on clone.
- `STG-073` TUF/update/license/device installation identities are remapped/quarantined according owning system contract.
- `STG-074` User accounts/data may be copied according privacy policy but session/auth artifacts are invalidated as required.
- `STG-075` Cache/job/idempotency keys include new environment identity so clone cannot replay production operations.
- `STG-076` AI/MCP credentials/connection registrations are not blindly copied into active clone context.
- `STG-077` Clone verification reports every quarantined/unmapped secret/provider identity before readiness.

### Group 8 — live→staging pull
- `STG-078` Pull plan names allowed source production scope and target staging scope with explicit direction.
- `STG-079` Pull cannot overwrite protected staging-only data/config unless selected merge/replace rule permits it.
- `STG-080` Source production remains read-only for a pull operation except explicitly documented snapshot mechanisms.
- `STG-081` Target staging drift is detected before destructive replace.
- `STG-082` Selected DB/table/file exclusions and redaction policies are applied during pull.
- `STG-083` Target URLs/paths/environment identity are transformed/reasserted after data copy.
- `STG-084` Staging side-effect quarantine is re-applied after every pull before jobs/connectors resume.
- `STG-085` User-generated production data copied to staging follows privacy/minimization policy.
- `STG-086` Pull partial failure leaves target in incomplete/recovery-required state and preserves prior recovery point where planned.
- `STG-087` Repeated pull operation is versioned/idempotent and cannot double-apply transformations.
- `STG-088` AI/MCP may draft pull plan but cannot execute live-data pull without privileged approval.

### Group 9 — staging→live push
- `STG-089` Push plan requires explicit selected changes/scope; “push everything” is not implicit default.
- `STG-090` Production drift since staging baseline is detected before overwrite.
- `STG-091` Content/config/schema/files can have separate merge/replace/conflict policies rather than one global overwrite.
- `STG-092` Production user/order/submission/business data is protected from accidental replacement by staging snapshot unless explicit reviewed scope includes it.
- `STG-093` Pre-push production recovery point requirement is verified where policy mandates it.
- `STG-094` Push uses dry-run/impact preview listing rows/files/config expected to change.
- `STG-095` Production side-effect credentials remain production-owned; staging placeholders never overwrite them.
- `STG-096` Partial push failure records exact committed/uncommitted scope and enters recovery/reconciliation state.
- `STG-097` Unknown remote outcome is reconciled before replay of non-idempotent apply.
- `STG-098` Post-push verification checks application/data/files/schema/critical routes before declaring success.
- `STG-099` AI/MCP cannot approve/execute production push or resolve destructive conflict without explicit owner gate.

### Group 10 — drift/conflict detection
- `STG-100` Baseline fingerprint records source/target revisions needed to compare subsequent drift.
- `STG-101` DB row/config/file changed only in source is classified source-only drift.
- `STG-102` Changed only in target is classified target-only drift.
- `STG-103` Changed differently in both is explicit conflict requiring merge/choice rule.
- `STG-104` Deleted in one side vs edited in other is not silently resolved by last-write-wins.
- `STG-105` Binary/file conflicts use fingerprint/content ownership rules and never attempt unsafe textual merge.
- `STG-106` Serialized structured-data conflict uses owner-aware parser or remains manual unresolved.
- `STG-107` Conflict resolution records actor/strategy/provenance and resulting revision.
- `STG-108` Stale conflict report is invalidated when either environment changes before apply.
- `STG-109` Provider/external state drift is reported separately because local clone cannot be authoritative for remote provider.
- `STG-110` AI conflict recommendation remains advisory and cannot silently choose production-destructive resolution.

### Group 11 — recovery points/rollback
- `STG-111` Pre-operation recovery point references Backup owner/artifact and is verified available before high-risk push where required.
- `STG-112` Recovery point inventory distinguishes DB, files, config, secrets refs and external-provider state not captured.
- `STG-113` Rollback plan states which local effects can be reverted and which external effects require reconciliation.
- `STG-114` Failed push can restore verified local DB/files recovery point without claiming provider rollback.
- `STG-115` Recovery artifact hash/manifest is verified before restore.
- `STG-116` Restored production undergoes post-restore schema/app/route/provider reconciliation before declaring recovered.
- `STG-117` Recovery cannot resurrect revoked credentials/webhooks/share links as automatically valid.
- `STG-118` Rollback operation itself is audited/versioned and protected by elevated Policy.
- `STG-119` Missing/incomplete recovery artifact blocks “rollback available” claim.
- `STG-120` Restore to point before migrations reports forward/rollback migration compatibility explicitly.
- `STG-121` AI/MCP may explain recovery options but cannot execute rollback or recreate secrets/provider state.

### Group 12 — migration/cutover verification
- `STG-122` Migration plan records source/target topology, DNS/domain, DB/files, config, provider and cutover ownership.
- `STG-123` Target preflight verifies WordPress/PHP/DB/storage/runtime compatibility before transfer.
- `STG-124` Final delta/freeze window semantics are explicit so writes are not silently lost between initial copy and cutover.
- `STG-125` DNS/domain change is external state and not reported complete solely because application config changed.
- `STG-126` TLS/certificate/host routing readiness is verified separately from WordPress URL change.
- `STG-127` Critical pages/auth/admin/assets/uploads/API health are checked on target before cutover success.
- `STG-128` Background jobs/webhooks/providers are re-bound to target only after environment identity and endpoints are verified.
- `STG-129` Source remains available/retired according explicit rollback/cutover plan; no silent deletion after success.
- `STG-130` Post-cutover old-domain redirects/cache/CDN handling is explicit and provider-owned where applicable.
- `STG-131` Migration completion requires target verification + unresolved issue list; transport 100% alone is insufficient.
- `STG-132` AI/MCP cannot declare cutover success or alter DNS/provider endpoints outside their own authorized adapters.

### Group 13 — Multisite conversions
- `STG-133` Multisite→Multisite copy maps network/site IDs/domains explicitly and preserves site isolation.
- `STG-134` Single-site→Multisite conversion requires target site mapping and cannot assume blog_id 1 semantics blindly.
- `STG-135` Multisite site→single-site extraction inventories network-shared users/plugins/themes/options before conversion.
- `STG-136` Subdomain↔subdirectory conversion maps URLs/routing and flags server/DNS requirements outside DB transform.
- `STG-137` User identity/network membership vs site role mappings are handled separately.
- `STG-138` Network-active plugin/theme/config is not copied as ordinary site option.
- `STG-139` Upload paths/site IDs are remapped using detected WordPress topology rather than string guesses.
- `STG-140` Cross-site references/relationships remain unresolved until explicit destination mapping exists.
- `STG-141` Network cron/webhook/provider identities are quarantined/remapped per destination scope.
- `STG-142` Site deletion/extraction source remains unchanged until explicit post-verification cleanup.
- `STG-143` Multisite conversion dry run reports unsupported plugins/data owners and blocks false “fully portable” claim.

### Group 14 — privacy redaction
- `STG-144` Non-production clone profile can redact/anonymize selected PII fields using typed owner-aware transforms.
- `STG-145` Password hashes/auth secrets/session tokens follow explicit removal/rotation policy and are never transformed as generic text.
- `STG-146` Email/phone/address redaction preserves schema validity while making divergence from production explicit.
- `STG-147` Woo/customer/order data copied to staging follows minimization policy and does not retain live payment secrets.
- `STG-148` Uploaded private files can be excluded/replaced with safe fixtures according classification.
- `STG-149` Legal-hold/retention constraints are respected when creating/redacting copies.
- `STG-150` Redaction dry run reports affected counts/types without leaking full sensitive values.
- `STG-151` Redaction is deterministic/idempotent when repeatability is required and records algorithm/version.
- `STG-152` Redacted staging data must never be pushed back to production unless an explicit reviewed scope deliberately includes it.
- `STG-153` Logs/manifests redact PII/secrets and avoid full row payloads by default.
- `STG-154` AI/MCP cannot request unredacted clone or reveal source PII without Privacy/Policy authorization.

### Group 15 — large-site/network performance
- `STG-155` 10GB/100GB file-copy fixture later measures throughput/checkpoint memory with declared storage/network profile.
- `STG-156` 10M-row DB migration fixture later validates chunking/snapshot/delta strategy and bounded memory.
- `STG-157` Large serialized-data transform later measures correctness/performance without regex corruption.
- `STG-158` Incremental/delta transfer later measures changed-item detection vs full-copy cost and accuracy.
- `STG-159` Network with hundreds/thousands of sites later validates per-site mapping and fair scheduling.
- `STG-160` Many small files fixture later validates manifest/checksum overhead and resumability.
- `STG-161` Remote high-latency transfer later validates backpressure/retry/checkpoint behavior.
- `STG-162` Large redaction job later validates chunking and no raw PII in logs.
- `STG-163` Target disk-space/capacity preflight prevents starting copy that cannot complete safely.
- `STG-164` Progress estimates remain estimates and do not count as completion evidence.
- `STG-165` Performance claims remain NOT EXECUTED until actual reproducible environment/results are recorded.

### Group 16 — golden migration/staging regressions
- `STG-166` Golden production→staging clone creates new environment identity and quarantines production side effects.
- `STG-167` Golden serialization transform changes domain/path safely without corrupting serialized/JSON data.
- `STG-168` Golden live→staging pull preserves staging identity and reapplies privacy/side-effect safeguards.
- `STG-169` Golden staging→live selective push protects production users/orders and detects drift/conflict.
- `STG-170` Golden remote-transfer timeout reconciles unknown target state before replay.
- `STG-171` Golden rollback restores verified local artifacts while accurately reporting external provider state not rolled back.
- `STG-172` Golden migration cutover requires target verification beyond transport completion.
- `STG-173` Golden Multisite conversion maps site/network/user/upload identities explicitly.
- `STG-174` Golden privacy-redacted clone cannot accidentally push redacted customer data back to production.
- `STG-175` Golden large/partial failure scenario remains resumable/recoverable and never reports false completion.
- `STG-176` Golden adversarial AI/MCP scenario cannot clone production secrets, enable side effects, push to live or declare cutover success outside Policy.

## Execution gate

This document specifies evidence only. **STG executed remains 0/176.** No clone, DB/file copy, URL transform, redaction, provider rebind, push/pull, migration, test, benchmark or runtime operation is authorized by this protocol.