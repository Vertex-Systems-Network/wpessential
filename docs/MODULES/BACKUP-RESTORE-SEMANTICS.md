# WPEssential — Backup & Restore Semantics

Status: **Phase 0 planning — no backup/restore implementation authorized**

## Goal
A backup is useful only if WPEssential can prove what it contains, detect corruption, retrieve it, restore intended scope safely, and explain recovery limitations.

Backup completion and restore readiness are separate states.

---

# Backup object model

## Backup Set
Logical restore point.

Fields/concepts:
- backup UUID;
- site/network identity snapshot;
- created-at;
- trigger: manual/schedule/pre-update/pre-reset/etc.;
- scope;
- manifest version;
- WordPress/PHP/DB/server metadata safe subset;
- active plugins/themes inventory + versions;
- WPE Product/Platform/schema versions;
- source URL/path/prefix metadata needed for migration;
- archive/chunk references;
- encryption state;
- local integrity state;
- destination copies;
- restore certification state;
- retention policy;
- notes/tags.

## Backup Artifact
Physical archive/chunk/database stream.

## Destination Copy
One remote/local copy of a Backup Set with its own transfer/integrity state.

One Backup Set can exist at multiple destinations.

---

# Scope options

## Full WordPress site candidate
- database selected scope;
- `wp-content/uploads`;
- plugins;
- themes;
- selected `wp-content` paths;
- WordPress core files optional/configurable rather than assumed necessary for every backup;
- safe site configuration files according to policy.

## Database only
- all selected WordPress tables;
- selected table groups;
- WPE tables only;
- custom tables selected.

## Files only
- uploads;
- themes;
- plugins;
- selected custom paths.

## WPE Configuration only
Uses portable WPE package service, not full DB dump.

## Content/application scope
Future selective logical backup may include posts/users/custom rows, but must not pretend logical export is equivalent to byte-for-byte DB restore.

---

# Mandatory manifest

Every Backup Set manifest records:
- manifest format/schema version;
- scope requested vs scope actually captured;
- table list + row/count/size metadata where practical;
- file list/chunk/path metadata according to scale strategy;
- exclusions;
- checksums;
- compression/encryption algorithms/versions;
- source WP table prefix;
- site URL/home URL;
- multisite metadata;
- plugin/theme versions;
- WPE schema/migration versions;
- archive/chunk order;
- failed/skipped items;
- destination copy status.

A backup with unresolved required capture errors cannot be labeled fully verified.

---

# Backup states

Candidate logical states:
- `queued`
- `preparing`
- `capturing_database`
- `capturing_files`
- `packaging`
- `verifying_local`
- `uploading`
- `verifying_remote`
- `completed_verified`
- `completed_partial`
- `failed`
- `cancelled`
- `pruned`

UI may simplify but internal state must preserve truth.

`completed_partial` is never displayed as a normal green successful full backup without warning.

---

# Consistency model

A live WordPress site can change during backup.

WPE must state the consistency level achieved.

## Database
Candidate strategies later evaluated:
- transaction/consistent snapshot where DB engine/storage supports it;
- logical dump with table locks only where safe/appropriate;
- application-aware capture limitations.

Do not claim globally atomic snapshot across DB + filesystem on ordinary shared hosting unless actually achieved.

## Files
Files may mutate during archive. Candidate mitigation:
- record stat/checksum while reading;
- detect changed-during-read files where feasible;
- retry bounded changed files;
- report unresolved volatility.

## Cross DB/files
A media row and its file can change at different moments. Manifest/restore verification must allow detection and document limitations.

---

# Exclusion rules

Default exclusions should consider:
- WPE temporary backup work directory;
- backup destination folders inside site to prevent recursive backup;
- caches/temp files where safe;
- logs according to user policy;
- existing giant backup archives;
- VCS/dev dependencies only when user selects/expects exclusion.

Never silently exclude user content based solely on size. Show excluded paths/rules in manifest.

Symlinks:
- do not blindly follow outside site root;
- configurable advanced behavior after security/path analysis;
- loops prevented;
- target path recorded safely.

---

# Compression

Candidate algorithms depend on host capabilities/portability.

Requirements:
- stream/chunk rather than full archive in memory;
- extension/tool availability detection;
- explicit compression level tradeoff;
- archive bomb/path traversal protections on restore;
- no shell-command assumption on all hosts.

Exact ZIP/TAR/compression implementation remains later evidence decision.

---

# Encryption

Backup archive encryption is independent from:
- HTTPS transport;
- provider server-side encryption;
- WPE Secrets Vault encryption.

Candidate modes later:
- no archive encryption with explicit warning/choice;
- password/key encrypted archive using reviewed authenticated-encryption/container design;
- site-managed backup key reference.

Never invent custom cryptography casually. Key recovery/rotation/loss semantics require separate accepted design before encrypted-backup feature ships.

A lost archive-encryption key means restore may be impossible; UI must state this.

---

# Restore entry modes

## In-site restore
Running WordPress/WPE initiates restore from accessible Backup Set.

## Disaster recovery bootstrap
Future separate minimal restore runner/package may be needed if WordPress cannot boot. This is not assumed for first Backup release and requires a separate threat/distribution design.

## Migration restore
Restore to changed domain/path/table prefix/environment with explicit transform options.

---

# Restore preflight

Before destructive restore:

1. authenticate + high-risk restore capability;
2. recent re-auth according to policy;
3. fetch/read manifest;
4. verify artifact checksum/integrity;
5. verify decryption key if encrypted;
6. verify available disk/temp space;
7. verify DB connection/permissions;
8. detect site/multisite mismatch;
9. compare WP/PHP/plugin/theme/WPE versions;
10. identify unavailable plugins/themes;
11. identify table-prefix/domain/path changes;
12. calculate restore scope/impact;
13. create pre-restore restore point when current environment is healthy enough and policy requires;
14. enter maintenance/recovery state only when ready;
15. show explicit final confirmation.

Failure before step 14 should not unnecessarily take site offline.

---

# Restore scopes

## Full restore
DB + selected files from Backup Set.

## Database restore
All or selected table groups.

## File restore
Uploads/themes/plugins/selected paths.

## Selective restore
Examples:
- one plugin/theme directory;
- selected upload path;
- WPE configuration package;
- selected WPE tables/config.

Selective restore only when dependency/integrity semantics are understood. Restoring one relation table without referenced data can corrupt application state.

---

# File restore semantics

Candidate safe pattern:
- restore into staging/temp location where feasible;
- verify extracted files/checksums;
- prevent path traversal;
- preserve/normalize permissions conservatively;
- swap/move into target using atomic rename where filesystem permits;
- per-file replacement fallback where atomic directory swap impossible;
- record files added/replaced/removed.

## Extra target files
Policy options must be explicit:
- preserve files not present in backup;
- mirror backup and remove extras within selected scope;
- preview extras before removal.

Default should avoid destructive removal of unrelated files unless user chose mirror behavior.

---

# Database restore semantics

Requirements:
- target table list explicit;
- prefix mapping;
- charset/collation compatibility review;
- SQL/parser/import designed for large data/chunking;
- no arbitrary SQL from untrusted backup without validation/trusted WPE backup format;
- foreign/logical dependency sequencing;
- disable/handle relevant triggers/constraints only through reviewed DB strategy;
- verify key tables/counts after import;
- update WPE migration/version state only from restored data truth.

## Existing tables
Candidate modes:
- replace selected tables;
- restore to temporary tables then controlled swap where supported;
- merge is **not** ordinary restore; it belongs to import/migration product semantics.

---

# URL/path migration

Migration restore can update known WordPress URL/path references only through serialization-safe, typed replacement logic.

Never raw string replace serialized PHP data blindly.

Targets include where appropriate:
- `home` / `siteurl`;
- network/site domain/path;
- uploads base URLs;
- known WPE definitions/data stores;
- serialized option/meta structures through safe parser;
- builder/plugin-specific data only through certified migration adapters where needed.

Unknown proprietary serialized/binary values remain unchanged and are reported if detectable.

---

# Multisite

Multisite restore needs dedicated modes:
- full network;
- single site/subsite logical restore;
- network tables;
- site-specific prefixed tables;
- uploads paths;
- domain/path mappings;
- network-active plugins/themes;
- Super Admin authorization.

Single-site → multisite or multisite → single-site is migration/conversion, not a simple restore and must not be marketed as such without dedicated support.

---

# Plugin/theme handling

Backup manifest records exact versions/hashes where practical.

Restore options candidate:
- restore plugin/theme files from backup;
- keep currently installed versions;
- selective choose.

Warnings:
- restored old plugin code may contain known vulnerabilities;
- current DB schema may not match kept newer code;
- restored DB may expect older WPE/Pro version.

Compatibility state machine must handle WPE Free/Pro version mismatch after restore without fatal boot.

No automatic downloading of historical paid/proprietary plugin versions from arbitrary sources.

---

# Core files

WordPress core can normally be reinstalled from trusted WordPress distributions; backing it up is optional/product-policy dependent.

If core restore is supported:
- verify expected WordPress version;
- never trust arbitrary executable core files merely because archive contains them;
- future “reinstall clean core then restore wp-content/DB” recovery mode may be safer and is worth separate design.

---

# Post-restore verification

Minimum checks:
- DB connection/required core tables;
- `home`/`siteurl`;
- current user/admin authentication path where possible;
- WPE Free/Pro compatibility state;
- WPE migration state;
- active plugin/theme load errors;
- uploads/readable files sample;
- manifest checksum samples/full verification according to tier;
- WP cron/job runner health;
- membership protected-access sanity sample if Membership data restored;
- permalink/rewrite state;
- Site Health integration summary.

Only after critical checks pass leave maintenance mode automatically. If critical checks fail, enter recovery/degraded mode with safe next actions.

---

# Rollback after failed restore

Strongest recovery:
- pre-restore Backup Set created/verified before restore.

If restore fails mid-way:
- do not claim automatic rollback unless implementation proves full rollback for that scope;
- preserve failure state/log/correlation ID;
- keep maintenance/recovery page;
- offer restore of pre-restore point where viable;
- avoid repeatedly auto-running failed restore loop.

DB transaction rollback alone cannot undo filesystem changes and is not a universal restore rollback.

---

# Restore status

Candidate states:
- `preflight`
- `waiting_confirmation`
- `downloading`
- `verifying`
- `pre_restore_snapshot`
- `maintenance`
- `restoring_database`
- `restoring_files`
- `transforming_environment`
- `post_restore_checks`
- `completed`
- `completed_with_warnings`
- `failed_recoverable`
- `failed_recovery_required`
- `cancelled_before_destructive_phase`

Cancellation may become unavailable after a critical commit/swap phase begins; UI must not show a fake safe Cancel button then.

---

# Restore observability

Log safe metadata:
- restore run ID;
- backup UUID;
- operator;
- scope;
- phases/durations;
- counts/bytes;
- warnings/errors;
- pre-restore point reference;
- final health state.

Do not log DB row contents, secrets, private files or archive-encryption keys.

---

# Backup verification tiers

## V0 — Generated
Artifacts created; not integrity verified.

## V1 — Local Verified
Local manifest/checksums verify.

## V2 — Remote Verified
At least one destination copy verified using provider-safe method.

## V3 — Restore Tested
Backup fixture has been restored successfully in automated/certification environment for that backup/provider/restore path.

User UI should distinguish these levels rather than a single generic “Success.”

---

# Reset Manager dependency

Reset Manager requiring a restore point should accept only a Backup Set meeting configured verification minimum—candidate default at least V1 locally or V2 remote depending destructive reset profile.

A backup job merely started before Reset is insufficient.

---

# Testing later

Required future fixtures include:
- small/large DB;
- large media set;
- changing files during backup;
- network interruption;
- corrupted archive/chunk;
- missing chunk;
- wrong encryption key;
- low disk space;
- DB permission failure;
- single table selective restore;
- plugin/theme mismatch;
- changed domain/path/prefix;
- serialized data URL replacement;
- multisite;
- failed post-restore plugin fatal;
- Membership access regression;
- source and target differing PHP/WP version;
- cancel before destructive phase;
- failure after DB but before files;
- recovery using pre-restore point.

---

# Development gate

This restore model is planning only. No archive creation/extraction, DB dump/import, maintenance mode, remote retrieval, restore runner or destructive operation is authorized before explicit owner development consent under ADR-0014.