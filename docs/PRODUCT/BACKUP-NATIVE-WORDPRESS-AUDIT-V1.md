# Backup — Native WordPress Audit V1

Surface: **24 / Backup**
Issue: **#1450**
Planning parent: **#590 / merged PR #603**
Seed: `config/product/options-bank/backup.json` — **122 records**

## Decision

**NATIVE AUDIT EVIDENCE COMPLETE; WORDPRESS CORE PROVIDES DATABASE ACCESS, FILESYSTEM ROOTS, PLUGIN/THEME INVENTORY, CRON, MULTISITE, MAINTENANCE-MODE AND SITE HEALTH PRIMITIVES BUT NO BACKUP SET, MANIFEST, ARCHIVE ENCRYPTION, REMOTE COPY, VERIFICATION TIER OR SAFE RESTORE ENGINE.** Native primitives are capture/restore substrate only and never a recovery-correctness contract. No archive creation, DB dump, upload, extraction, maintenance mode or restore occurs here.

## Current native sources

- WordPress Backups (Advanced Administration) — https://developer.wordpress.org/advanced-administration/security/backup/
- Backing Up Your Database — https://developer.wordpress.org/advanced-administration/security/backup/database/
- Backing Up Your WordPress Files — https://developer.wordpress.org/advanced-administration/security/backup/files/
- wpdb — https://developer.wordpress.org/reference/classes/wpdb/
- wp_upload_dir() — https://developer.wordpress.org/reference/functions/wp_upload_dir/
- get_plugins() — https://developer.wordpress.org/reference/functions/get_plugins/
- wp_get_themes() — https://developer.wordpress.org/reference/functions/wp_get_themes/
- Filesystem API — https://developer.wordpress.org/apis/filesystem/
- wp_schedule_event() — https://developer.wordpress.org/reference/functions/wp_schedule_event/
- is_multisite() — https://developer.wordpress.org/reference/functions/is_multisite/
- maybe_unserialize() — https://developer.wordpress.org/reference/functions/maybe_unserialize/
- wp_maintenance() — https://developer.wordpress.org/reference/functions/wp_maintenance/
- WP_Site_Health — https://developer.wordpress.org/reference/classes/wp_site_health/
- Privacy (Plugin Handbook) — https://developer.wordpress.org/plugins/privacy/
- wp-config.php — https://developer.wordpress.org/apis/wp-config-php/
- WP-CLI wp db export — https://developer.wordpress.org/cli/commands/db/export/
- WP-CLI wp search-replace — https://developer.wordpress.org/cli/commands/search-replace/
- PHP Zip — https://www.php.net/manual/en/book.zip.php
- PHP Zlib — https://www.php.net/manual/en/book.zlib.php
- PHP Sodium — https://www.php.net/manual/en/book.sodium.php
- PHP hash_file() — https://www.php.net/manual/en/function.hash-file.php
- In-repo: `docs/MODULES/BACKUP-MANAGER-EXHAUSTIVE-SPEC.md`, `docs/MODULES/BACKUP-RESTORE-SEMANTICS.md`, `docs/SECURITY/BACKUP-ARCHIVE-ENCRYPTION-KEY-RECOVERY.md`, `docs/PRODUCT/BACKUP-BANK-ENTRY-READINESS-V1.md`, `docs/ARCHITECTURE/BACKUP-*` profiles
- In-repo read-only reference: `frameworks/Modules/Backup/Verification/LocalBackupManifestVerifier.php` and `frameworks/Modules/Backup/Manifest/BackupManifest.php` (manifest V1 local verification, fail-closed; never creates, modifies, restores or deletes)

Native classification of the readiness checklist: DB access/table prefix/site-network metadata (`wpdb` prefix and multisite table relationships; capture consistency and charset/collation metadata are WPE policy), filesystem roots (`wp_upload_dir()`, `WP_CONTENT_DIR`, plugins/themes/mu-plugins/languages via `get_plugins()`/`wp_get_themes()`; core optional), wp-content permissions/hosting restrictions (Filesystem API method/credentials substrate; free-space and web-exposure tests are WPE), ZIP/TAR/PHP extension availability (PHP Zip/Zlib/Sodium are optional extensions that must be capability-probed; CMP0 none fallback), WP-Cron/Job scheduling limits (`wp_schedule_event()` substrate only through the canonical Job Service; no long-running in-request work), multisite (`is_multisite()` substrate; Super Admin authorization and restore modes are WPE), plugin/theme activation/version/schema (native inventory; compatibility warnings and Free/Pro state machine are WPE), site URL/home/upload path mapping (native options; typed serialization-safe replacement is WPE, with WP-CLI `search-replace` as a precedent rather than a dependency), serialization-safe transformations (`maybe_unserialize()` substrate; never raw string replace), privacy/security of artifacts (WordPress privacy tooling is the reference; backups carry personal data so encryption, retention, private storage and access control apply). WordPress handbook backup guidance is manual and does not provide verification or restore safety.

## Seed-record disposition

| Seed record | Native disposition | Notes |
|---|---|---|
| `backup.set.identity` | **WPE PRODUCT/POLICY** | Generated UUID plus optional bounded label (default "Manual backup — <local datetime>"), normalized tags and internal note; note is excluded from filenames/provider keys unless sanitized separately. |
| `backup.set.trigger` | **WPE PRODUCT/POLICY** | Trigger is recorded truth, fixed to manual in the ordinary wizard; pre-reset/pre-restore triggers come from the destructive workflow that requires the restore point. |
| `backup.set.status-states` | **WPE PRODUCT/POLICY** | queued/preparing/capturing_database/capturing_files/packaging/verifying_local/uploading/verifying_remote/completed_verified/completed_partial/failed/cancelled/pruned; UI may simplify but internal state keeps truth and completed_partial is never a plain green success. |
| `backup.set.verification-tier` | **WPE-EXCEED (no native equivalent)** | Tiered recovery confidence instead of a single "Success"; V3 is a certification/staging outcome and a stale V3 never proves current remote presence. No native or common-market equivalent. |
| `backup.set.protect-restore-point` | **WPE PRODUCT/POLICY** | Protect toggle default off for ordinary manual backups; restore-point flag default off unless invoked by a destructive workflow; protected sets are never auto-pruned. |
| `backup.set.list-filters` | **WPE PRODUCT/POLICY** | Filter by status, trigger, scope, encryption, verification tier, destination, protected flag, date, source environment and warnings; search label/UUID/notes/tags and safe object references only. |
| `backup.set.bulk-actions` | **WPE PRODUCT/POLICY** | Destructive bulk operations require impact preview and cannot delete the only known verified recovery point when active policy forbids it; artifact download requires capability/policy. |
| `backup.set.overview` | **WPE PRODUCT/POLICY** | Permission-filtered last success, last verified tier, next run, protected points, destination health, failed/partial count, temp usage, runner health, encryption readiness, latest restore, alerts. |
| `backup.plan.identity` | **WPE PRODUCT/POLICY** | Required name and stable generated key editable before first publish per common contract; enabled/paused/degraded status; plan actions Edit/Duplicate/Run Now/Pause/Validate/Export/Archive. |
| `backup.plan.schedule` | **NATIVE SUBSTRATE + WPE POLICY** | WP-Cron (wp_schedule_event) is native substrate only, executed through the canonical Job Service (Surface 18); preferred local time, weekday/month day, site timezone default, DST preview; hourly only when intentionally chosen. |
| `backup.plan.missed-run-overlap` | **WPE PRODUCT/POLICY** | Missed run: run once ASAP default / skip / bounded catch-up; overlap prevented by default; maximum expected duration hint and runner health warning. |
| `backup.plan.environment-restrictions` | **DEFERRED / CROSS-OWNER** | Marked "optional future" by the spec; deferred until environment identity/staging (Surface 55) contracts exist. |
| `backup.scope.preset` | **WPE PRODUCT/POLICY** | Changing a preset populates explicit selections and the UI shows exactly what will be captured; WPE configuration scope uses the portable package service (Surface 26), not a full DB dump. |
| `backup.scope.db-tables` | **NATIVE SUBSTRATE + WPE POLICY** | wpdb table prefix and site/network table metadata are native substrate; WPE custom tables and Custom Tables Builder tables are explicit toggles; non-prefix tables off by default with explicit selection. |
| `backup.scope.db-transient-exclusion` | **WPE PRODUCT/POLICY** | Only where the adapter recognizes safe categories; default off until the user chooses; row/data filters are not part of full backup (logical selective export belongs to Import/Export). |
| `backup.scope.db-objects` | **DEFERRED / CROSS-OWNER** | Displayed only when the DB adapter supports it and certification exists; deferred until a certified DB adapter profile is accepted. |
| `backup.scope.db-consistency` | **WPE PRODUCT/POLICY** | Automatic chunk/stream default; consistent snapshot where engine supports, table locks only where safe; charset/collation is mandatory metadata, not a toggle; warnings for huge/non-transactional tables, missing privileges, unsupported objects. |
| `backup.scope.consistency-disclosure` | **WPE-EXCEED (no native equivalent)** | WPE states the consistency level actually achieved and never claims a globally atomic DB+files snapshot on ordinary hosting; cross DB/files drift is documented. Not offered by native or common market tools. |
| `backup.scope.files-roots` | **NATIVE SUBSTRATE + WPE POLICY** | wp_upload_dir(), WP_CONTENT_DIR, get_plugins() and wp_get_themes() are the native substrate; WordPress core optional rather than assumed; custom paths only within an approved root. |
| `backup.scope.files-inactive` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Native active/inactive inventory (get_plugins, wp_get_themes) drives the toggle; bounded optional choice. |
| `backup.scope.files-default-excludes` | **WPE PRODUCT/POLICY** | Cache and logs default off when safely detectable, existing backup archives and destination folders excluded to prevent recursive backup; never silently exclude user content by size; excluded rules recorded in the manifest. |
| `backup.scope.files-custom-excludes` | **WPE PRODUCT/POLICY** | Size threshold only as an explicit user rule; preview shows matched examples/count estimate where feasible. |
| `backup.scope.files-symlinks` | **WPE PRODUCT/POLICY** | Off by default; loops prevented; target path recorded safely; outside-root targets unavailable in normal mode. |
| `backup.scope.files-unreadable` | **WPE PRODUCT/POLICY** | Unresolved required capture errors prevent a fully verified label. |
| `backup.scope.files-volatility` | **WPE-EXCEED (no native equivalent)** | Record stat/checksum while reading, detect volatility, bounded retry count (advanced), report unresolved volatility in the manifest. |
| `backup.scope.content-logical` | **DEFERRED / CROSS-OWNER** | Future only; must not pretend logical export equals byte-for-byte DB restore; overlaps Surface 26 Import/Export. |
| `backup.archive.format` | **WPE PRODUCT/POLICY** | Canonical Backup Set is header + authoritative manifest + independently verifiable parts, not one ZIP; ZIP/TAR are convenience adapters only where the runtime capability and security limits are verified. |
| `backup.archive.compression` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Native PHP zlib/zip availability probed (CMP0 none fallback, CMP1 gzip streaming, CMP2 ZIP convenience, CMP3 zstd future); per-file bypass for already-compressed media; compression before encryption. |
| `backup.archive.split-parts` | **WPE PRODUCT/POLICY** | Custom part size bounded and provider-aware; chunk sizing profile governs defaults. |
| `backup.archive.workspace` | **WPE PRODUCT/POLICY** | Read-only/advanced registry choice; the working directory can never recursively include itself; private, non-web-accessible location. |
| `backup.archive.manifest` | **WPE PRODUCT/POLICY** | Cannot be disabled; records schema version, requested vs captured scope, tables/files, exclusions, checksums, algorithms, prefix, URLs, multisite, plugin/theme and WPE versions, part order, failed/skipped items. Existing read-only LocalBackupManifestVerifier consumes manifest V1 (reference only). |
| `backup.archive.checksums` | **WPE PRODUCT/POLICY** | H-B1 SHA-256 over exact stored bytes for every finalized required part; provider ETag is supplemental only; AEAD authentication and SHA-256 are never substituted for each other. |
| `backup.archive.encryption` | **WPE PRODUCT/POLICY** | Independent of HTTPS, provider SSE and Vault encryption; reviewed AEAD container design (native Sodium requirement) with per-part encryption; lost key may make restore impossible and the UI says so before enabling. |
| `backup.archive.recovery-method` | **WPE PRODUCT/POLICY** | Mode B passphrase (never stored, Argon2id-class KDF never silently lowered) and Mode C site-managed recovery key with exportable recovery kit; key never stored inseparably with the archive. |
| `backup.archive.recovery-readiness` | **WPE-EXCEED (no native equivalent)** | Recovery material is tested before an encrypted run and readiness is surfaced on Overview/details; not a common market capability. |
| `backup.archive.kms-adapter` | **DEFERRED / CROSS-OWNER** | Mode D / Slot C future only; requires separate accepted design. |
| `backup.archive.key-rotation` | **WPE-EXCEED (no native equivalent)** | Rotation must not make old backups undecryptable (WordPress salts are not the recovery root); compromised DEK/recovery key procedures defined separately. |
| `backup.archive.incremental-chain` | **DEFERRED / CROSS-OWNER** | Content-addressed/deduplicated chunk store is Alternative D only; current recommendation is manifest + independent parts. Deferred until chain/restore-dependency semantics are accepted. |
| `backup.dest.identity` | **WPE PRODUCT/POLICY** | Provider identity is family_key + provider_key + profile version + adapter version; numeric PF-xx aliases never persisted; listed providers are targets until certified (current certified count 0). |
| `backup.dest.credential-ref` | **WPE PRODUCT/POLICY** | Credentials are Vault references owned with Surface 23 Connections; saved-state indicator only; never revealed. |
| `backup.dest.endpoint-path` | **WPE PRODUCT/POLICY** | Per adapter profile; custom S3/WebDAV/SFTP endpoints require explicit destination trust/SSRF policy via shared Connections policy. |
| `backup.dest.storage-class-sse` | **WPE PRODUCT/POLICY** | Provider SSE is explicitly separate from WPE archive encryption. |
| `backup.dest.transfer-tuning` | **WPE PRODUCT/POLICY** | Chunk size Auto default, adapter-default timeout, retry profile; upload concurrency advanced and bounded by the Job Service. |
| `backup.dest.required-optional` | **WPE PRODUCT/POLICY** | Default: all required destinations succeed; "at least one required verified" is an advanced clearly displayed policy only. |
| `backup.dest.local` | **NATIVE SUBSTRATE + WPE POLICY** | WP_Filesystem method and approved private roots are native substrate; free-space, permissions and web-access exposure tests; staging names then manifest last. |
| `backup.dest.browser-export` | **WPE PRODUCT/POLICY** | Capability + nonce authorized streaming, sanitized filename, short-lived temp artifact, never secrets in URL; not a remote retention destination and never counted as a remote copy. |
| `backup.dest.s3` | **WPE PRODUCT/POLICY** | Endpoint/region/bucket/credential ref/prefix/storage class; multipart automatic; object-lock status read-only; capability-negotiated rather than feature-inherited; TLS mandatory. |
| `backup.dest.sftp` | **WPE PRODUCT/POLICY** | Host/port 22/username/password or private key via Vault; host-key verification mandatory; remote path; keepalive adapter-managed. |
| `backup.dest.ftp-ftps` | **WPE PRODUCT/POLICY** | FTPS preferred; plain FTP only as "Legacy / Insecure transport" with explicit administrator acknowledgement, Vault credentials and conditional resume. |
| `backup.dest.webdav` | **WPE PRODUCT/POLICY** | HTTPS endpoint strongly required, certificate validation, capability probe; Nextcloud/ownCloud are profiles, not generic guarantees. |
| `backup.dest.oauth-drive` | **WPE PRODUCT/POLICY** | OAuth lifecycle owned by Surface 23; account safe label, target folder ID, resumable capability, quota where exposed, scopes displayed. |
| `backup.dest.email-small` | **WPE PRODUCT/POLICY** | Not a normal large-backup destination: manifest-only or small archive below a safety bound; fallback link only when another protected destination exists and authorization permits; explicit non-certified warning. |
| `backup.dest.test-health` | **WPE PRODUCT/POLICY** | Test connection and upload/download/delete in an isolated probe location where provider permits; usage by plans; delete only after dependency review; disconnect never deletes remote backups. |
| `backup.dest.certification-levels` | **WPE-EXCEED (no native equivalent)** | A logo, OAuth success or one upload is not support; C0 connectable through C4 disaster-restore certified; static evidence SE levels never imply C-levels. |
| `backup.dest.remote-copy-lifecycle` | **WPE-EXCEED (no native equivalent)** | Each destination copy has independent transfer, commit and verification state; upload started never equals stored; provider visibility never equals verified. |
| `backup.dest.remote-verification` | **WPE PRODUCT/POLICY** | Provider-safe method recorded in manifest; V2 requires at least one verified remote copy. |
| `backup.verify.target-minimum` | **WPE PRODUCT/POLICY** | V1 Local Verified default for manual local backup; V2 candidate default for remote plans; immediate restore test unavailable on ordinary production sites; sample vs full method recorded. |
| `backup.verify.cleanup-local` | **WPE PRODUCT/POLICY** | Only after verified remote upload; local manifest retained unless cleanup removes Backup Set metadata. |
| `backup.verify.reverification` | **WPE-EXCEED (no native equivalent)** | Metadata-only, provider checksum, sampled range, full read/hash or V3 depth labeled with time; corruption/missing copy response policy. |
| `backup.verify.auto-mirror` | **DEFERRED / CROSS-OWNER** | Spec marks it future optional. |
| `backup.verify.restore-test-scheduling` | **DEFERRED / CROSS-OWNER** | Only if staging infrastructure exists (Surface 55 Staging owns the environment); future. |
| `backup.retention.rules` | **WPE PRODUCT/POLICY** | Retention engine operates on Backup Set/copy truth, not raw provider folder age; policy snapshot recorded per copy. |
| `backup.retention.protection` | **WPE PRODUCT/POLICY** | Manual/protected points never auto-deleted by default; minimum verified copies preserved; most recent verified backup preserved regardless of age. |
| `backup.retention.gfs` | **WPE PRODUCT/POLICY** | Only when the advanced feature is accepted; operates on copy truth. |
| `backup.retention.prune-ordering` | **WPE-EXCEED (no native equivalent)** | Unless an explicit storage emergency policy says otherwise; prune checks pins, healthy-copy minimum, in-progress Reset/Restore dependency and provider object lock. |
| `backup.retention.partial-prune` | **WPE PRODUCT/POLICY** | Cleanup distinguishes staging/orphan parts from committed parts. |
| `backup.delete.modes` | **WPE PRODUCT/POLICY** | Deleting one copy keeps Backup Set metadata if other copies remain; typed phrase for protected/last verified copy; reason per policy; tombstone/history per audit policy; provider permission failure leaves delete_failed, not deleted. |
| `backup.notify.policy` | **WPE PRODUCT/POLICY** | Failure on by default for scheduled plans; recipients via Surface 19 Notifications; email fallback only if configured; safe summary with no secrets/archive keys. |
| `backup.preflight.backup` | **WPE PRODUCT/POLICY** | Shows included/excluded scope, estimated bytes/files/tables, temp disk, destinations, encryption readiness, verification target, compatibility warnings, destructive locks and runner health before Start/Save as Plan. |
| `backup.concurrency.policy` | **WPE PRODUCT/POLICY** | One run per plan and one site-wide capture by default; destination uploads parallelize only within Job Service limits; skip/queue when another destructive operation is active. |
| `backup.performance.resources` | **WPE PRODUCT/POLICY** | Never load a full archive into PHP memory; Job Service phases resumable where safe; paginated lists/logs/provider listings; chunked retention; disk reservation estimate. |
| `backup.restore.source` | **WPE PRODUCT/POLICY** | Verified-only filter default on; restore source resolution follows remote copy truth. |
| `backup.restore.import-manifest` | **DEFERRED / CROSS-OWNER** | Future certified flow only; imported provenance must be explicit; generic package import stays Surface 26. |
| `backup.restore.preflight` | **WPE PRODUCT/POLICY** | Read-only checks and blockers before any destructive phase: authentication + high-risk capability, recent re-auth, manifest/integrity, decryption key, space, DB permissions, multisite mismatch, version and plugin/theme availability, impact; failure before maintenance does not take the site offline. |
| `backup.restore.scope` | **WPE PRODUCT/POLICY** | Selected tables only with accepted dependency-safe warning; selective restore only where dependency/integrity semantics are understood. |
| `backup.restore.files-existing` | **WPE PRODUCT/POLICY** | Restore to staging/temp, verify, prevent traversal, atomic rename where permitted, record added/replaced/removed files. |
| `backup.restore.db-existing` | **WPE PRODUCT/POLICY** | No generic merge mode; explicit target list, prefix mapping, charset/collation review, verify key tables/counts after import. |
| `backup.restore.env-mapping` | **NATIVE SUBSTRATE + WPE POLICY** | home/siteurl options, upload paths and wpdb prefix are native substrate; serialization-safe typed replacement (maybe_unserialize-aware, WP-CLI search-replace precedent) enabled by default for known structures; builder/plugin data only via certified adapters. |
| `backup.restore.plugin-theme` | **WPE PRODUCT/POLICY** | Explicit vulnerability and DB schema mismatch warnings; Free/Pro compatibility handled without fatal boot. |
| `backup.restore.core-files` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Core is normally reinstalled from trusted WordPress distributions; if restored, expected version verified and arbitrary executable core files never trusted. |
| `backup.restore.pre-restore-point` | **WPE PRODUCT/POLICY** | Required by default; override only with dedicated high-risk capability plus phrase/reason where policy allows. |
| `backup.restore.confirmation` | **WPE PRODUCT/POLICY** | No destructive restore without verified backup + explicit confirmation; no promised duration. |
| `backup.restore.maintenance` | **NATIVE SUBSTRATE + WPE POLICY** | Native .maintenance / wp_maintenance() substrate; entered only when ready and left automatically only after critical post-restore checks pass. |
| `backup.restore.post-checks` | **NATIVE SUBSTRATE + WPE POLICY** | WP_Site_Health summary is native substrate; failures enter recovery/degraded mode with safe next actions. |
| `backup.restore.status-cancel` | **WPE-EXCEED (no native equivalent)** | preflight → ... → completed/completed_with_warnings/failed_recoverable/failed_recovery_required/cancelled_before_destructive_phase; no fake Cancel after a commit/swap phase. |
| `backup.restore.failure-recovery` | **WPE PRODUCT/POLICY** | No automatic-rollback claim unless proven for scope; preserve failure state/log/correlation ID; offer pre-restore point restore; DB transaction rollback is not universal rollback. |
| `backup.restore.multisite` | **NATIVE SUBSTRATE + WPE POLICY** | is_multisite() and network/site table relationships are native substrate; Super Admin authorization; single-to-multisite conversion is not a simple restore. |
| `backup.restore.dr-bootstrap` | **DEFERRED / CROSS-OWNER** | Separate minimal restore runner when WordPress cannot boot; requires separate threat/distribution design; not assumed for first release. |
| `backup.restore.migration-conversion` | **DEFERRED / CROSS-OWNER** | Migration/conversion, not restore; must not be marketed without dedicated support. |
| `backup.details.screen` | **WPE PRODUCT/POLICY** | Verification details show algorithm/version, local and remote results, verified timestamp, last failure, tier and V3 reference. |
| `backup.activity.logs` | **WPE PRODUCT/POLICY** | Filter by backup/run/destination/phase/outcome/date; never DB row contents, secrets, private files or archive keys. |
| `backup.events.typed` | **WPE PRODUCT/POLICY** | Safe typed payloads only; never archive keys/secrets/private contents. |
| `backup.failure.states` | **WPE PRODUCT/POLICY** | No backups/plan/destination/verified point, auth expired, provider unreachable, disk low, runner unhealthy, partial, verification failed, copy missing, key unavailable, incompatibility, preflight blocked, recovery required; no silent spinner timeout. |
| `backup.permissions.caps` | **WPE PRODUCT/POLICY** | Read/create/plan CRUD/destination/credentials/run/cancel/verify/download/delete/restore separated; restore, credential and recovery-key operations high-risk with recent re-auth. |
| `backup.abilities.ai-exposure` | **WPE PRODUCT/POLICY** | AI default list/get/status/preview/diagnostics only; run requires explicit allowlist; delete/restore/credential/recovery disabled by default. |
| `backup.plan.portability` | **WPE PRODUCT/POLICY** | Plan definitions export without credentials/recovery keys; imported plans bind new Vault references; package movement owned by Surface 26. |
| `backup.privacy.artifacts` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | WordPress privacy tooling is the native reference; backups contain personal data, so encryption, retention and access controls apply; erasure requests do not silently rewrite immutable backups. |
| `backup.a11y.progress` | **WPE PRODUCT/POLICY** | Progress not colour-only, percentage only when measurable, keyboard wizard, focus-managed destructive confirmation, leave-and-resume via run detail, no fake completion on browser close. |
| `backup.safety.success-without-verification` | **REJECTED-UNSAFE CONFIRMED** | Rejected: capture, local verification, remote persistence, remote verification and restore-tested confidence stay distinct; partial/unverified never shown as generic green success. |
| `backup.safety.disable-manifest-checksums` | **REJECTED-UNSAFE CONFIRMED** | Rejected: manifest and SHA-256 checksums are mandatory and cannot be disabled. |
| `backup.safety.zip-password-encryption` | **REJECTED-UNSAFE CONFIRMED** | Rejected: only reviewed authenticated encryption is presented as backup encryption. |
| `backup.safety.symlink-outside-root` | **REJECTED-UNSAFE CONFIRMED** | Rejected: unavailable in normal mode; loops prevented. |
| `backup.safety.restore-without-preflight` | **REJECTED-UNSAFE CONFIRMED** | Rejected: preflight blockers run read-only before any destructive phase. |
| `backup.safety.restore-without-verified-backup` | **REJECTED-UNSAFE CONFIRMED** | Rejected: verified source plus explicit final confirmation (and pre-restore point by default) are required. |
| `backup.safety.mirror-delete-without-preview` | **REJECTED-UNSAFE CONFIRMED** | Rejected: mirror removal of extras and bulk deletes require preview. |
| `backup.safety.delete-last-verified` | **REJECTED-UNSAFE CONFIRMED** | Rejected: protected and last verified points survive retention and bulk delete against policy. |
| `backup.safety.reveal-credentials-keys` | **REJECTED-UNSAFE CONFIRMED** | Rejected: write-only Vault references; recovery keys never emailed/uploaded automatically or logged. |
| `backup.safety.key-stored-with-archive` | **REJECTED-UNSAFE CONFIRMED** | Rejected: key and encrypted data are never inseparably stored together; DEK never derived from salts, passwords, URL or provider credentials. |
| `backup.safety.generic-merge-restore` | **REJECTED-UNSAFE CONFIRMED** | Rejected: merge is not ordinary restore; no arbitrary SQL from untrusted backups. |
| `backup.safety.raw-serialized-replace` | **REJECTED-UNSAFE CONFIRMED** | Rejected: serialization-safe typed replacement only. |
| `backup.safety.ignore-tls` | **REJECTED-UNSAFE CONFIRMED** | Rejected: TLS validation mandatory by default. |
| `backup.safety.skip-sftp-host-key` | **REJECTED-UNSAFE CONFIRMED** | Rejected: host-key verification is mandatory. |
| `backup.safety.plain-ftp-secure-default` | **REJECTED-UNSAFE CONFIRMED** | Rejected: legacy/insecure label plus explicit acknowledgement only; no wording implying encryption in transit. |
| `backup.safety.in-request-long-running` | **REJECTED-UNSAFE CONFIRMED** | Rejected: phased Job Service work only. |
| `backup.safety.upload-as-remote-verified` | **REJECTED-UNSAFE CONFIRMED** | Rejected: upload, commit and verification are separate remote copy states. |
| `backup.safety.public-archive-urls` | **REJECTED-UNSAFE CONFIRMED** | Rejected: archives live in private non-web-accessible storage; downloads require capability + nonce and never carry secrets in URLs. |
| `backup.safety.extract-before-validation` | **REJECTED-UNSAFE CONFIRMED** | Rejected: path traversal, archive bombs, duplicate paths and forged parts are blocked before extraction. |
| `backup.safety.historical-plugin-download` | **REJECTED-UNSAFE CONFIRMED** | Rejected: no automatic downloading of historical paid/proprietary versions. |
| `backup.safety.disconnect-deletes-remote` | **REJECTED-UNSAFE CONFIRMED** | Rejected: disconnect never means delete; delete/prune is explicit and audited. |
| `backup.safety.ai-destructive-access` | **REJECTED-UNSAFE CONFIRMED** | Rejected: disabled by default; AI limited to list/get/status/preview/diagnostics. |
| `backup.safety.size-silent-exclusion` | **REJECTED-UNSAFE CONFIRMED** | Rejected: size exclusion only as an explicit visible user rule recorded in the manifest. |
| `backup.safety.secrets-in-job-payloads` | **REJECTED-UNSAFE CONFIRMED** | Rejected: never serialized into ordinary job payload tables, options, logs or browser storage. |

## Native completeness / unresolved items

No additional Backup V1 Bank family is required. Native unresolved items: **0**. Manifest, SHA-256 part integrity, authenticated archive encryption and recovery material, remote copy lifecycle, provider certification, verification tiers, retention/prune safety and restore preflight/rollback have no native WordPress equivalent and remain WPE/market-owned.

## Gate boundary

Not promoted here: capture, archive generation, encryption, upload, remote verification, retention pruning, restore execution, runtime, Atomic Option Contracts, UX contract closure or product parity.
