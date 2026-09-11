# Backup — Runtime Gap Matrix V1

Surface: **24 / Backup**  
Planning issue: **#590**  
Supervisor wave: **#583**  
Exact-main claim anchor: `dab9944bf7ad174ceffb3b48ddebffac4ea71bcc`

Status: **PLANNING GAP MATRIX / RUNTIME NOT AUTHORIZED**

Surface 24 remains `UNSEEDED / 0` in the Master Options Bank and exact main has no dedicated Backup runtime module. This matrix records prerequisites and owner boundaries only.

## Gap matrix

| Area | Exact-main state | Future requirement | Boundary / gate |
|---|---|---|---|
| Master Options Bank | **BLOCKED — UNSEEDED / 0** | Native/market/provider reviewed normalized records. | Product gate before option contracts/runtime. |
| Atomic Option Contracts | **PLANNING INVENTORY ONLY** | Schema-valid contracts after Bank review. | No lifecycle promotion from inventory alone. |
| Backup definitions/catalog | **ABSENT** | Plans/Sets/Destination refs, revision/CAS/lifecycle. | Surface 24. |
| Capture engine | **ABSENT** | DB/files/config scopes with manifest/checksum/provenance. | Runtime authorization required later. |
| DB consistency/chunking | **ABSENT** | certified strategies and warnings for non-transactional/privilege/object limits. | DB adapter evidence. |
| File capture/excludes | **ABSENT** | approved roots, symlink/path safety, changed/unreadable handling. | No arbitrary outside-root traversal. |
| Archive/encryption | **ABSENT** | certified format/split/compression + mandatory manifest/checksum + reviewed encryption/recovery. | No weak pseudo-encryption. |
| Destination adapters | **DEPENDENCY** | required/optional mirrors, upload/resume/remote verify. | Surface 23 handles connection/credentials/Safe HTTP. |
| Verification | **ABSENT** | distinct capture/local/remote/restore-tested evidence tiers. | No generic success collapse. |
| Scheduling/Jobs | **DEPENDENCY** | durable overlap/missed-run/concurrency semantics. | Surface 18/shared Job Service. |
| Retention | **ABSENT** | protected points/min verified copies/GFS/prune ordering. | Never delete last required verified recovery point. |
| Restore engine | **ABSENT / HIGH RISK** | source integrity/decryption/environment preflight, scope/mapping, pre-restore point, journal/recovery. | Separate explicit runtime authorization. |
| Pre-restore point | **DEPENDENCY** | verified backup before destructive restore by default. | Surface 24 self-protection; Reset consumes same seam. |
| Delete/copy | **ABSENT / DESTRUCTIVE** | state-aware copy/delete with provider verification and safeguards. | Dedicated abilities/re-auth. |
| Activity/audit | **ABSENT** | phase/result/evidence logs without secrets/recovery keys. | Shared Audit + Surface 24 evidence. |
| Notifications | **DEPENDENCY** | partial/failure/verification/provider health alerts. | Surface 19. |
| Multisite | **UNSPECIFIED** | network/site capture and restore semantics. | Contract before implementation. |
| Portability/import | **ABSENT** | source provenance and imported artifact preflight. | Import does not imply restore. |
| Accessibility | **NO SURFACE UI** | wizard/table/status/progress/destructive UX. | Browser/axe evidence later. |
| Compatibility | **NO SURFACE RUNTIME** | WP/PHP/DB/filesystem/archive/provider/hosting matrix. | Exact-head evidence later. |
| Reliability/performance | **NO SURFACE RUNTIME** | streaming/chunking/resume/jobs/locks/resource budgets/cancel/recovery. | Deterministic tests later. |

## Safety hard gates

Future implementation must reject or fail closed on:

- restore without integrity/decryption/environment preflight;
- restore without current Policy and destructive lock;
- ordinary bypass of required pre-restore point;
- weak ZIP-password mode presented as secure encryption;
- missing manifest/checksums;
- following symlinks outside approved roots in normal mode;
- deleting the only verified/protected recovery copy against policy;
- provider/TLS/credential bypass;
- secrets/recovery keys in logs/export/AI context;
- optimistic provider-delete success without confirmation where API supports verification;
- unbounded backup/restore in a PHP request;
- concurrent destructive runs without lock/journal/recovery semantics;
- generic database merge semantics during restore.

## Required Policy / Ability separation

Separate abilities for read/list, Plan create/update/delete, Destination metadata manage, credential manage (delegated), run/cancel, verify, artifact download, copy/delete, restore, and unsafe restore override. Restore/credential/recovery-key actions require elevated capability and may require recent re-auth.

## Accessibility evidence required later

- keyboard backup/restore wizards;
- focus and error summaries across preflight phases;
- non-color-only partial/unverified/degraded states;
- accessible progress/status updates;
- destructive confirmation semantics;
- responsive backup/destination tables;
- axe coverage for capture preview, partial backup, restore blocker and provider failure states.

## Multisite evidence required later

- network vs individual-site DB table selection;
- shared users/uploads/plugins/themes scope;
- site/network URL/path mapping;
- site-only restore safety around global tables;
- network admin permissions;
- destination/Vault scope;
- retention and backup catalog visibility.

## Reliability/performance evidence required later

1. manifests/checksums are mandatory and deterministic;
2. large DB/files are streamed/chunked within memory/time budgets;
3. changed/unreadable files produce explicit partial/fail state;
4. required destination failure cannot become green success;
5. remote verification is distinct from upload success;
6. encrypted backups verify recovery material before relying on them;
7. retention creates/verifies replacement before pruning required old copy;
8. restore preflight blocks incompatible/corrupt/decryption-failed sources;
9. pre-restore point is created/verified before destructive execution where required;
10. interrupted restore has journal/recovery path;
11. concurrent backup/restore/destructive operations obey locks;
12. provider retries/resume are idempotent/bounded;
13. logs never expose credentials/recovery keys;
14. cancel/cleanup leaves catalog/artifacts in explainable recoverable state.

## Future implementation order

Only after Bank review + schema-valid option contracts:

1. Plan/Set catalog + manifest/verification model;
2. read-only preflight/estimation and destination health;
3. DB/files capture + archive/checksum;
4. destination upload + remote verification;
5. retention/scheduling/concurrency/evidence;
6. encryption/recovery integration;
7. separately authorized restore preflight/journal/pre-restore point;
8. restore execution and recovery testing;
9. multisite/portability/degraded closure;
10. exact-head security/accessibility/compatibility/performance certification audit.

Restore execution must remain separately gated from basic backup capture.

## Exit decision

Exact main has a mature exhaustive Backup specification and companion restore/encryption planning, but the Master Options Bank is still `UNSEEDED / 0` and no runtime module exists. `runtime_allowed=false` remains correct.

The next valid action is Bank seeding/native/market/provider review, then schema-valid option contracts and UX re-review — not backup/restore execution from this planning lane.
