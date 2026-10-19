# Backup — Market Audit V1

Status: `MARKET_AUDITED` (integrated in `config/product/options-bank-audits/backup-market-ecosystem.json`)

Surface: 24 — Backup
Snapshot: 2026-10-19
Issue: #1450

## 1. Scope and boundary

Reconciles all 122 Backup records. Surface 24 owns Backup Sets/Plans, capture scope, manifest, verification tiers, destination copy truth, retention and restore semantics. Connections (S23) owns provider transport, credentials, OAuth and Safe HTTP; Cron/Job Service (S18) durable scheduling; Notifications (S19) delivery; Reset (S25) consumes restore points; Import/Export (S26) generic packages; Staging (S55) restore-test environments; the Vault owns secret values and recovery material.

## 2. Evidence

- **E1 UpdraftPlus** — https://wordpress.org/plugins/updraftplus/, https://updraftplus.com/ — scheduled files/DB backups, many remote storage targets, retention counts, restore and migration; premium incremental, encryption of DB and multisite.
- **E2 BackWPup** — https://wordpress.org/plugins/backwpup/ — backup jobs with schedules, DB/files/XML export, multiple destinations, job logs and archive formats.
- **E3 Duplicator** — https://wordpress.org/plugins/duplicator/, https://duplicator.com/ — package-based backup/migration with archive + installer, URL/path replacement and scheduled backups in paid tiers.
- **E4 WPvivid Backup & Migration** — https://wordpress.org/plugins/wpvivid-backuprestore/, https://wpvivid.com/ — backup, restore, migration, remote storage, retention and staging in one plugin.
- **S1 Jetpack VaultPress Backup** — https://jetpack.com/backup/ — real-time/off-site backup with activity log and guided restore.
- **S2 BlogVault** — https://wordpress.org/plugins/blogvault-real-time-backup/ — incremental off-site backups, staging test restore and migration.
- **S3 All-in-One WP Migration** — https://wordpress.org/plugins/all-in-one-wp-migration/ — single-file export/import migration with URL replacement.
- **S4 Solid Backups** — https://solidwp.com/backups — scheduled backups, remote destinations and restore/rollback flows.
- **S5 restic** — https://restic.readthedocs.io/en/stable/ — authenticated encryption, content-addressed deduplication, `check` verification and `forget`/`prune` retention.
- **S6 BorgBackup** — https://borgbackup.readthedocs.io/en/stable/ — authenticated encryption modes, key export/recovery, deduplicated chunks, compression and prune retention.
- **S7 AWS S3 Object Integrity and Object Lock** — https://docs.aws.amazon.com/AmazonS3/latest/userguide/checking-object-integrity.html, https://docs.aws.amazon.com/AmazonS3/latest/userguide/object-lock.html — object checksums for upload integrity and Object Lock retention/immutability.
- **S8 NIST SP 800-34 Rev. 1 Contingency Planning** — https://csrc.nist.gov/pubs/sp/800/34/r1/upd1/final — contingency planning: backup testing, restoration verification and recovery planning.
- **S9 RFC 9106 Argon2** — https://www.rfc-editor.org/rfc/rfc9106 — memory-hard password KDF baseline for passphrase recovery slots.
- **S10 Snyk Zip Slip Research** — https://security.snyk.io/research/zip-slip-vulnerability — archive extraction path traversal class.
- **S11 RFC 4253 SSH Transport (Host Key Verification)** — https://www.rfc-editor.org/rfc/rfc4253 — SSH transport with server host-key verification.

## 3. Record reconciliation

| Record | Disposition |
|---|---|
| `backup.set.identity` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.set.trigger` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.set.status-states` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.set.verification-tier` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress backup plugins |
| `backup.set.protect-restore-point` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.set.list-filters` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.set.bulk-actions` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.set.overview` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.plan.identity` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.plan.schedule` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `backup.plan.missed-run-overlap` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.plan.environment-restrictions` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `backup.scope.preset` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.scope.db-tables` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `backup.scope.db-transient-exclusion` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.scope.db-objects` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `backup.scope.db-consistency` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.scope.consistency-disclosure` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress backup plugins |
| `backup.scope.files-roots` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `backup.scope.files-inactive` | MARKET_EVIDENCED_WITH_BOUNDS |
| `backup.scope.files-default-excludes` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.scope.files-custom-excludes` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.scope.files-symlinks` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.scope.files-unreadable` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.scope.files-volatility` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress backup plugins |
| `backup.scope.content-logical` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `backup.archive.format` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.archive.compression` | MARKET_EVIDENCED_WITH_BOUNDS |
| `backup.archive.split-parts` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.archive.workspace` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.archive.manifest` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.archive.checksums` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.archive.encryption` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.archive.recovery-method` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.archive.recovery-readiness` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress backup plugins |
| `backup.archive.kms-adapter` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `backup.archive.key-rotation` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress backup plugins |
| `backup.archive.incremental-chain` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `backup.dest.identity` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.dest.credential-ref` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.dest.endpoint-path` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.dest.storage-class-sse` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.dest.transfer-tuning` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.dest.required-optional` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.dest.local` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `backup.dest.browser-export` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.dest.s3` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.dest.sftp` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.dest.ftp-ftps` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.dest.webdav` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.dest.oauth-drive` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.dest.email-small` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.dest.test-health` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.dest.certification-levels` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress backup plugins |
| `backup.dest.remote-copy-lifecycle` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress backup plugins |
| `backup.dest.remote-verification` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.verify.target-minimum` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.verify.cleanup-local` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.verify.reverification` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress backup plugins |
| `backup.verify.auto-mirror` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `backup.verify.restore-test-scheduling` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `backup.retention.rules` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.retention.protection` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.retention.gfs` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.retention.prune-ordering` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress backup plugins |
| `backup.retention.partial-prune` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.delete.modes` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.notify.policy` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.preflight.backup` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.concurrency.policy` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.performance.resources` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.restore.source` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.restore.import-manifest` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `backup.restore.preflight` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.restore.scope` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.restore.files-existing` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.restore.db-existing` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.restore.env-mapping` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `backup.restore.plugin-theme` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.restore.core-files` | MARKET_EVIDENCED_WITH_BOUNDS |
| `backup.restore.pre-restore-point` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.restore.confirmation` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.restore.maintenance` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `backup.restore.post-checks` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `backup.restore.status-cancel` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress backup plugins |
| `backup.restore.failure-recovery` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.restore.multisite` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `backup.restore.dr-bootstrap` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `backup.restore.migration-conversion` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `backup.details.screen` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.activity.logs` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.events.typed` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.failure.states` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.permissions.caps` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.abilities.ai-exposure` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.plan.portability` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.privacy.artifacts` | MARKET_EVIDENCED_WITH_BOUNDS |
| `backup.a11y.progress` | MARKET_EVIDENCED / WPE_HARDENED |
| `backup.safety.success-without-verification` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.disable-manifest-checksums` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.zip-password-encryption` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.symlink-outside-root` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.restore-without-preflight` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.restore-without-verified-backup` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.mirror-delete-without-preview` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.delete-last-verified` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.reveal-credentials-keys` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.key-stored-with-archive` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.generic-merge-restore` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.raw-serialized-replace` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.ignore-tls` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.skip-sftp-host-key` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.plain-ftp-secure-default` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.in-request-long-running` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.upload-as-remote-verified` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.public-archive-urls` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.extract-before-validation` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.historical-plugin-download` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.disconnect-deletes-remote` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.ai-destructive-access` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.size-silent-exclusion` | KEEP `REJECTED_UNSAFE` |
| `backup.safety.secrets-in-job-payloads` | KEEP `REJECTED_UNSAFE` |

Coverage: **122 / 122**; 248 Bank references; 6 extra dispositions; unresolved **0**; record delta after market audit **0**.

## 4. Rejected market patterns

- Archives kept in predictable web-reachable directories (E1 layout class) → `backup.safety.public-archive-urls`, `backup.dest.local`.
- Installer/archive files left publicly reachable after migration (E3) → `backup.safety.public-archive-urls`; bootstrap runner deferred (`backup.restore.dr-bootstrap`).
- "Completed" job status without remote read-back verification (E2) → `backup.safety.success-without-verification`, `backup.safety.upload-as-remote-verified`.
- Plain FTP as an ordinary destination (E1) → `backup.safety.plain-ftp-secure-default`.
- Vendor-hosted off-site storage/managed restore (S1) is a provider business model, not a Surface 24 option; providers enter only through certification.
- Staging clone/push (E4) is out of surface and owned by Surface 55 Staging.

No capture, archive, upload, restore execution, runtime or product parity is authorized.
