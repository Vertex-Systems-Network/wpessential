# WPEssential — Backup Manager Exhaustive Option Specification

Status: **Phase 0 — Exhaustive Option Spec / planning only / no implementation authorized**  
Edition: **Pro**  
Composes:
- `BACKUP-PROVIDER-CERTIFICATION-MATRIX.md`
- `BACKUP-RESTORE-SEMANTICS.md`
- `../SECURITY/BACKUP-ENCRYPTION-KEY-RECOVERY.md`
- `COMMON-OPTION-CONTRACTS.md`
- Job Service / Vault / Notifications / Audit contracts

## 1. Product rule
Backup Manager is not complete when an archive process exits successfully. WPEssential distinguishes capture, local verification, remote persistence, remote verification and restore-tested confidence.

A UI status must never collapse `partial`, `unverified` or `restore-not-tested` into a generic green Success.

---

# 2. Top-level screens

## 2.1 Overview
Cards/metrics, permission-filtered:
- last successful Backup Set;
- last verified level V0/V1/V2/V3;
- next scheduled run;
- protected restore points;
- destination health count;
- failed/partial backups count;
- local temporary storage usage;
- retained remote copy count when known;
- Job Service runner health;
- encryption/recovery readiness;
- latest restore status;
- alerts requiring action.

Quick actions:
- Create Backup;
- Create Plan;
- Add Destination;
- Restore;
- Verify Backup;
- Diagnostics.

## 2.2 Backup Sets list
Columns:
- checkbox;
- name/label;
- Backup UUID short display;
- created at;
- trigger;
- scope summary;
- source site/environment;
- size;
- encryption state;
- verification tier;
- destination copy summary;
- restore-point/protected flag;
- retention expiry/locked state;
- status;
- duration;
- initiated by;
- actions.

Filters:
- all/status;
- manual/scheduled/pre-reset/pre-restore/pre-update/imported;
- scope;
- encrypted/unencrypted;
- verification tier;
- destination;
- protected restore point;
- date range;
- source environment;
- warnings/errors.

Search:
- label;
- UUID;
- notes/tags;
- destination object reference where safe.

Row actions:
- Details;
- Verify;
- Restore;
- Download manifest;
- Download artifact only with capability/policy;
- Copy to destination;
- Protect/Unprotect from retention;
- Add note/tags;
- Retry failed destination;
- Delete copy;
- Delete Backup Set.

Bulk actions:
- Verify selected;
- Protect/Unprotect;
- Copy to destination;
- Delete eligible copies/sets;
- Export manifest report.

Destructive bulk operations require impact preview and cannot delete the only known verified recovery point when an active policy forbids it.

## 2.3 Backup Plans list
Columns:
- name;
- status enabled/paused/degraded;
- preset/scope;
- schedule;
- next run;
- destinations;
- retention summary;
- encryption;
- verification requirement;
- last result;
- owner/creator metadata if useful;
- actions.

Actions:
- Edit;
- Duplicate;
- Run Now;
- Pause/Resume;
- Validate;
- Export;
- Archive/Delete definition.

## 2.4 Destinations list
Columns:
- display name;
- adapter/provider;
- certification level;
- credential state;
- endpoint/region safe summary;
- last connection test;
- last successful upload;
- last remote verification;
- used by N plans;
- health;
- actions.

Actions:
- Test connection;
- Edit non-secret settings;
- Replace credentials;
- Test upload/download/delete in isolated probe location where provider permits;
- View usage/dependencies;
- Disable;
- Delete after dependency review.

## 2.5 Restore Runs
Columns:
- restore run ID;
- backup;
- target environment;
- scope;
- operator;
- started/completed;
- phase;
- state;
- pre-restore point;
- warnings;
- recovery action.

## 2.6 Activity / Logs
Filter by backup/run/destination/phase/outcome/date. Logs show safe metadata only.

---

# 3. Create Backup wizard

## Step 1 — Identity
Fields:
- label optional; default generated `Manual backup — <local datetime>`;
- internal note optional;
- tags optional;
- trigger fixed `manual` for ordinary wizard;
- protect from automatic retention toggle, default off for manual ordinary backup;
- create as restore point toggle, default off unless invoked by destructive workflow.

Validation:
- label length bounded;
- tags normalized;
- note excluded from filenames/provider keys unless sanitized separately.

## Step 2 — Scope preset
Preset radio:
- Database only;
- Content + uploads;
- Full site;
- WPEssential configuration only;
- Files only;
- Custom.

Changing preset populates explicit selections; UI shows exactly what will be captured.

## Step 3 — Database options
Controls:
- include database toggle;
- table selection mode: all WP tables / prefix tables / custom;
- include WPE custom tables toggle;
- include user-created Custom Tables Builder tables toggle;
- include non-prefix tables: off default + explicit selection;
- exclude transient/cache tables where adapter recognizes safe categories, default off until user chooses;
- row/data filters: not part of full backup default; logical selective export belongs Import/Export unless specifically certified;
- include views/triggers/routines: only displayed when DB adapter supports and certification exists;
- chunk/stream mode: automatic default;
- consistency strategy: automatic/recommended; advanced override only if supported;
- record charset/collation: mandatory metadata, not toggle.

Warnings:
- huge tables;
- non-transactional tables;
- unavailable privileges;
- unsupported DB objects;
- changing tables during capture.

## Step 4 — Files options
Root checkboxes:
- uploads;
- plugins;
- themes;
- mu-plugins;
- language files;
- selected wp-content folders;
- WordPress core optional;
- selected custom path within approved root.

Controls:
- include inactive plugins;
- include inactive themes;
- include cache directories default off when safely detectable;
- include logs default off when safely detectable;
- include existing backup archives default off;
- follow symlinks: off default;
- allow symlink target outside site root: unavailable in normal mode;
- unreadable file policy: fail required scope / partial with warning;
- changed-during-read retry count advanced;
- individual-file size warning threshold UI only;
- custom excludes list.

Exclude rule types:
- exact path;
- directory prefix;
- bounded glob;
- extension;
- size threshold as explicit user rule only.

Preview displays matched examples/count estimate where feasible.

## Step 5 — Archive
Controls:
- archive format: Automatic / ZIP / TAR+GZIP where certified;
- compression level: Fast / Balanced default / Maximum;
- split archive: Auto / Off / Custom part size;
- custom part size bounded and provider-aware;
- temporary workspace path read-only/advanced registry choice;
- manifest: mandatory, cannot disable;
- checksums: mandatory, algorithm chosen by accepted archive contract;
- encrypt archive toggle;
- encryption recovery method according to accepted encryption contract;
- test recovery material before run when encryption enabled.

Do not expose unsupported pseudo-encryption such as weak ZIP password mode as “secure backup encryption.”

## Step 6 — Destinations
Controls:
- Local/private storage;
- browser/manual download after generation;
- one or more configured remote destinations;
- Add Destination inline contextual action;
- mark destination Required / Optional mirror;
- per-destination path/prefix override;
- destination retention override if allowed;
- upload concurrency Advanced;
- provider chunk size Automatic default;
- remote verification method Automatic/certified method.

Overall success policy:
- all required destinations succeed — default;
- at least one required verified destination — optional advanced policy only when clearly displayed.

## Step 7 — Verification
Controls:
- target verification minimum: V1 Local Verified default for manual local backup;
- require V2 for remote plan when remote destination selected candidate default;
- immediate restore test unavailable on ordinary production site; V3 belongs certification/staging workflow unless future safe target exists;
- sample vs full checksum verification only where semantics support; manifest records actual method;
- delete temporary local artifact after verified remote upload toggle;
- retain local manifest always unless cleanup policy removes Backup Set metadata.

## Step 8 — Notifications
Controls:
- notify on success;
- notify on partial;
- notify on failure default on for scheduled plans;
- notify when verification below target;
- notify when destination credentials unhealthy;
- recipients through Notification System;
- email fallback only if configured;
- include safe summary, no secrets/archive keys.

## Step 9 — Review / Preflight
Shows:
- included/excluded scope;
- estimated bytes/files/tables;
- temporary disk estimate;
- destinations;
- encryption/recovery readiness;
- verification target;
- known compatibility warnings;
- current destructive-operation locks;
- runner health.

Actions:
- Save as Plan;
- Start Backup;
- Cancel.

---

# 4. Backup Plan editor

## General
- name required;
- key stable generated/editable before first publish according common contract;
- enabled toggle;
- description;
- tags;
- priority optional operational metadata;
- environment restrictions optional future.

## Schedule
- manual only;
- one-time;
- hourly only if system supports and user intentionally chooses;
- daily;
- weekly;
- monthly;
- custom interval;
- preferred local time;
- weekday/month day;
- site timezone default;
- DST preview;
- missed run: run once ASAP default / skip / catch up bounded;
- overlap: prevent default;
- maximum expected duration hint;
- runner health warning.

## Scope
Same controls as Create wizard, persisted.

## Destinations
Same Required/Optional semantics.

## Retention
Global/default controls:
- keep last N;
- keep for duration;
- never automatically delete manual/protected restore points default;
- GFS daily/weekly/monthly retention only when advanced feature accepted;
- minimum verified copies to preserve;
- delete local staging after verified remote;
- prune partial/failed artifacts after duration;
- preserve most recent successful verified backup regardless of age candidate safety default;
- per-destination override.

Retention ordering rule: create + verify replacement before deleting old recovery copy unless explicit storage emergency policy says otherwise.

## Verification policy
- minimum tier;
- remote re-verification cadence;
- corruption/missing copy response;
- auto-copy healthy mirror if one required destination fails future optional;
- restore-test certification scheduling if staging infrastructure exists future.

## Concurrency
- one run per plan default;
- site-wide backup capture concurrency: one by default candidate;
- destination uploads may parallelize through Job Service within resource limits;
- skip/queue behavior when another destructive operation active.

---

# 5. Destination editor common options

Common:
- name;
- adapter/provider;
- enabled;
- required certification notice;
- connection credential Vault reference;
- endpoint/region/bucket/container/folder according adapter;
- root path/prefix;
- storage class/tier where supported;
- server-side encryption option where provider supports, explicitly separate from WPE archive encryption;
- upload chunk size Auto default;
- request timeout adapter default;
- retry profile;
- proxy/network option only through shared Connections policy;
- retention override;
- test actions;
- health status.

Credential fields:
- write-only;
- saved state indicator;
- Replace / Revoke / Re-authorize;
- never reveal secret value.

### Local
- private root selection from approved locations;
- filesystem method;
- free-space check;
- web-access exposure test/warning;
- permissions health.

### S3-compatible family
- endpoint;
- region;
- bucket;
- access credential reference;
- path-style/virtual-host style only when adapter requires;
- prefix;
- multipart settings automatic;
- storage class;
- provider-specific immutable/object-lock status read-only where available;
- TLS verification mandatory.

### SFTP
- host;
- port default 22;
- username;
- auth method password/private key according Vault;
- host-key verification mandatory contract;
- remote path;
- passive-like settings do not invent FTP semantics;
- keepalive/timeouts adapter-managed.

### WebDAV
- HTTPS endpoint strongly required;
- username/token credential;
- root path;
- certificate validation;
- capability probe.

### OAuth drive
- Connect/Reconnect on provider/service flow;
- account safe label;
- target folder ID + display name;
- resumable upload capability;
- quota when provider exposes;
- token health;
- scopes displayed.

### Email/Gmail-style small-artifact delivery
Not a normal large-backup destination. Controls:
- recipient;
- artifact mode manifest only / small archive below configured/provider limit;
- max attachment safety bound;
- fallback link only when another protected destination exists and authorization permits;
- explicit warning that mailbox delivery is not a certified large restore store.

---

# 6. Backup details screen

Sections:
- Summary;
- Scope captured;
- Manifest;
- Artifacts/parts;
- Verification;
- Destination copies;
- Environment snapshot;
- Exclusions/skips;
- Logs/timeline;
- Restore readiness;
- Encryption/recovery status;
- Related restore runs;
- Audit.

Actions depend on current state/capability.

Verification details show:
- checksum algorithm/version;
- local result;
- remote provider method/result;
- verified timestamp;
- last failure;
- V tier;
- restore-tested environment/reference if V3.

---

# 7. Restore wizard exhaustive controls

Composes `BACKUP-RESTORE-SEMANTICS.md`.

## Select source
- local catalog;
- configured remote destination;
- import WPE backup manifest/artifact future certified flow;
- filter verified only default on.

## Preflight
Read-only checks + blockers:
- manifest schema;
- checksums;
- decryption/recovery readiness;
- disk/temp space;
- DB permissions;
- source/target WP/PHP/DB;
- site/network mode;
- Free/Pro Platform API compatibility;
- plugin/theme availability;
- table prefix;
- domain/path;
- active destructive locks;
- current backup/restore point readiness.

## Scope
- full selected backup scope;
- DB only;
- files only;
- uploads;
- selected plugin/theme directories;
- WPE configuration;
- selected tables only when dependency-safe warning accepted.

## Existing target behavior
Files:
- preserve extras default;
- mirror/remove extras advanced + preview.

DB:
- replace selected tables;
- no generic merge mode.

## Environment mapping
- source → target site URL;
- home URL;
- upload base;
- filesystem path mappings;
- DB prefix mapping;
- serialization-safe replacement enabled default for known WordPress structures;
- builder/plugin-specific transformation only via certified adapters.

## Plugin/theme strategy
- restore archived files;
- keep target version;
- per-package choice;
- explicit security/DB schema warnings.

## Pre-restore point
- required default;
- minimum verification threshold;
- storage target;
- override only dedicated high-risk capability + phrase/reason where policy allows.

## Confirmation
Shows irreversible/at-risk data, expected maintenance window semantics without promising duration, recovery route and Cancel availability boundary.

---

# 8. Delete / retention UX

Deleting one destination copy must not automatically delete Backup Set metadata if other copies remain.

Delete modes:
- delete selected copy;
- delete all artifacts/copies for Backup Set;
- delete catalog metadata only only when artifacts already absent/imported history, with warning;
- prune by retention job.

Controls:
- confirmation;
- typed phrase for protected/last verified copy;
- reason optional/required by policy;
- preserve manifest tombstone/history optional according retention/audit policy.

Provider deletion result must be verified where API semantics permit; permission failure leaves copy state `delete_failed`, not `deleted`.

---

# 9. Permissions

At minimum:
- `wpe_backup_read`
- `wpe_backup_create`
- `wpe_backup_plan_create`
- `wpe_backup_plan_update`
- `wpe_backup_plan_delete`
- `wpe_backup_destination_read`
- `wpe_backup_destination_manage`
- `wpe_backup_credentials_manage`
- `wpe_backup_run`
- `wpe_backup_cancel`
- `wpe_backup_verify`
- `wpe_backup_download`
- `wpe_backup_delete`
- `wpe_backup_restore`
- `wpe_backup_restore_unsafe_override`

Restore/credential/recovery-key operations are high-risk and may require recent re-auth.

---

# 10. Abilities

Candidate:
- `wpessential/backup.list`
- `wpessential/backup.get`
- `wpessential/backup.preview`
- `wpessential/backup.run`
- `wpessential/backup.status`
- `wpessential/backup.cancel`
- `wpessential/backup.verify`
- `wpessential/backup.copy`
- `wpessential/backup.delete`
- `wpessential/backup.restore_preview`
- `wpessential/backup.restore`
- `wpessential/backup.destination_test`
- plan CRUD/export/import abilities.

AI default exposure:
- list/get/status/preview/diagnostics only;
- run requires explicit allowlist;
- delete/restore/credential/recovery operations disabled by default.

---

# 11. Events

Emit safe typed events:
- backup queued/started/phase_changed/completed/partial/failed/cancelled;
- local verification completed/failed;
- destination upload completed/failed;
- remote verification completed/failed;
- retention prune completed/failed;
- restore requested/started/phase_changed/completed/failed;
- destination health changed;
- recovery readiness degraded.

Never include archive keys/secrets/private contents in generic event payload.

---

# 12. Failure and empty states

Explicit UI states:
- no backups yet;
- no plan;
- no destination;
- no verified recovery point;
- destination authentication expired;
- provider unreachable;
- local disk low;
- runner unhealthy;
- backup partial;
- verification failed;
- remote copy missing;
- recovery key unavailable;
- encrypted artifact but recovery method unknown;
- schema/version incompatibility;
- restore preflight blocked;
- restore recovery required.

Every failure includes next safe action; no generic silent spinner timeout.

---

# 13. Performance / resource controls

- stream DB/files/archive;
- never load full archive into PHP memory;
- bounded chunk size;
- Job Service phases resumable where safe;
- request/page list pagination;
- logs paginated;
- object listing provider API paginated;
- background retention chunked;
- upload concurrency bounded;
- disk-space reservation/preflight estimate;
- backup working directory cannot recursively include itself.

---

# 14. Accessibility / UX

- progress not color-only;
- phase text + percentage only when measurable;
- keyboard-accessible wizard;
- destructive confirmation focus management;
- errors linked to exact fields;
- long running operations can leave page and be resumed via run detail;
- no fake completion when browser closes;
- sizes/dates localized, stored canonical.

---

# 15. Acceptance tests after development consent

Must include:
- every preset resolves expected explicit scope;
- Required vs Optional destination outcomes;
- interrupted multipart resume;
- remote verification mismatch;
- local disk exhaustion;
- symlink escape;
- unreadable file;
- volatile file during capture;
- retention protects last good backup;
- encrypted backup recovery with original server unavailable;
- wrong recovery material;
- corrupt/missing part;
- selective restore dependency warning;
- serialized URL migration;
- plugin/theme mismatch;
- failed restore recovery point;
- unauthorized download/restore/delete;
- stale provider credential;
- multi-destination partial success;
- large lists/log pagination;
- module asset isolation.

## Maturity
**Exhaustive Option Spec**. Runtime archive formats, concrete crypto profile, DB snapshot implementation, provider certification and restore execution remain technical/evidence blockers and require explicit owner development consent before any executable spike.