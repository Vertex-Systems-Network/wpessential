# WPEssential — Reset Execution Journal & Recovery Runtime

Status: **Phase 0 architecture / no destructive implementation authorized**  
Related: Reset Manager Exhaustive Spec, Backup Manager, ADR-0032, ADR-0014.

## Core principle

A WordPress/site reset is **not one atomic transaction**. It can span database rows/tables, options, users, files, plugin/theme activation state, caches and external/offloaded assets.

WPE therefore models reset as a staged, journaled destructive workflow with a verified restore point and explicit recovery boundaries.

## Reset domains

A Reset Plan has individually scoped domains:
- WPE definitions/runtime data;
- WordPress content/posts/comments/terms/media references;
- users/roles according to protected-admin rules;
- options/settings;
- plugin activation/configuration;
- theme state;
- uploads/media files;
- WPE custom tables;
- WPE caches/indexes/generated assets;
- external/offload/provider data only through certified adapters;
- whole-site reset preset composed from domains.

“Reset WordPress” is a generated plan, not an uninspectable hard-coded delete loop.

## Planning phase

Before execution WPE creates immutable Reset Plan revision containing:
- run UUID;
- requested scope;
- preserve rules;
- current site/network context;
- exact module/data-owner adapters involved;
- estimated objects/tables/files affected;
- recovery-principal impact;
- dependencies/order;
- operations classified reversible/restore-only/external;
- required Backup restore point;
- expected post-reset target state;
- plan fingerprint.

Execution must match the reviewed Plan fingerprint. Material site drift can require re-preflight.

## Mandatory restore point

Destructive reset levels require a Backup Set or equivalent verified recovery point according to risk.

Minimum checks before start:
- backup generation finished;
- required local/remote destinations reached required verification tier;
- restore metadata/key accessibility known;
- current site fingerprint matches snapshot expectation within allowed drift;
- operator acknowledges unrecoverable external actions.

For strongest destructive reset, prefer/require a restore point that can survive loss of the current DB/site.

## Recovery journal

A Reset Run owns a durable append/update journal with:
- run ID;
- plan revision/fingerprint;
- actor/request ID;
- current stage;
- stage started/completed timestamps;
- operation cursor/checkpoint;
- affected-object counters;
- preservation artifacts;
- restore-point reference;
- errors/retry state;
- recovery instructions/status;
- final health verification.

### Storage requirement

Journal must survive the data domains being reset.

Candidate dual-anchor model:
1. dedicated WPE recovery store/table deliberately excluded until successful finalization;
2. minimal filesystem recovery manifest outside ordinary uploads/reset scope when host permissions allow.

A whole-DB destructive reset that would erase its only journal before recovery is established is blocked.

Exact physical store requires later evidence.

## Execution stages

Candidate state machine:

1. `planned`
2. `preflight`
3. `restore_point_verified`
4. `maintenance_lock`
5. `preservation_snapshot`
6. `destructive_execution`
7. `minimum_wordpress_recovery`
8. `preserved_state_reapply`
9. `cache_rebuild`
10. `health_verification`
11. `unlock`
12. `completed`

Failure states:
- `failed_before_destructive`
- `failed_recoverable`
- `restore_required`
- `restoring`
- `recovered`
- `manual_intervention_required`.

## Destructive operation journal

Each adapter emits bounded operations/checkpoints such as:
- truncate/delete known table set;
- remove option namespace;
- delete selected content batch;
- delete WPE derivative directory batch;
- deactivate plugin set;
- switch/reset theme state;
- re-create minimum options/roles/admin recovery state.

The journal records semantic operations/counts, not sensitive row dumps.

## Database transaction limits

Use transactions only where the underlying tables/engine/operation genuinely supports a useful transaction boundary.

Do not claim a transaction can roll back:
- filesystem deletion;
- MySQL DDL that auto-commits under target DB behavior;
- external provider deletion;
- emails/webhooks already sent;
- plugin/theme side effects outside transaction.

Cross-domain recovery is Backup restore/compensating action, not fake ACID.

## Recovery principal invariant

ADR-0032 applies throughout reset.

If reset scope affects users/roles:
- preserve a verified current recovery administrator where requested/required; or
- establish and verify a replacement recovery principal before final removal of the old one;
- never intentionally leave zero legitimate administrative recovery path;
- multisite Super Admin/network scope handled separately.

Passwords are never written to journal/log.

## Plugin/theme files

Separate:
- installed files;
- activation state;
- WPE-owned configuration;
- third-party data.

Reset can choose deactivate/keep/delete files only where explicitly supported.

Deleting plugin/theme files is higher-risk than deactivation and must not happen before recovery dependencies are captured.

WPE does not delete another plugin's database tables merely because that plugin is deactivated unless a certified ownership adapter and explicit reset scope exists.

## Upload/media reset

Database media records and physical files are separate operations.

Policies:
- reference-only cleanup;
- WPE derivative/generated media cleanup;
- selected uploads deletion;
- whole uploads deletion.

Offloaded files require provider adapter and truthful rollback classification.

## External side effects

External deletes/revokes are not assumed reversible.

Reset Plan labels every external operation:
- local reversible;
- backup-restorable;
- compensatable;
- irreversible/unknown.

Irreversible external action requires heightened confirmation and may be excluded from default presets.

## Maintenance/locking

During destructive stages:
- block competing WPE mutations;
- avoid frontend writes where inconsistent state could occur;
- show maintenance/recovery-safe response;
- allow authorized recovery/health channel;
- background jobs targeting reset domains pause or reject according to Job Service policy.

A stuck lock has expiry/recovery semantics but must not auto-unlock into a half-reset state without verification.

## Minimum WordPress reconstruction

When scope resets core site data, WPE must know what constitutes a bootable recoverable WordPress state for the supported mode.

Examples to verify in future:
- required site options;
- administrator/recovery role state;
- permalink/cache refresh;
- active plugin/theme safety;
- multisite network/site essentials.

Do not hard-code these until compatibility matrix evidence exists.

## Post-reset verification

Run targeted checks:
- WordPress boot/admin login path;
- recovery principal exists;
- requested data domains match intended empty/preserved state;
- no unexpected source tables/files were removed;
- required plugin/theme state;
- WPE kernel/module registry health;
- DB schema health;
- cron/job queues consistent;
- uploads/storage adapter reachable;
- no maintenance lock left accidentally.

Only then mark Completed.

## Failure recovery

### Before destructive stage
Abort safely; clear temporary lock/artifacts.

### During destructive stage
Do not continue blindly after unknown failure.

Options:
- retry idempotent current operation;
- continue from verified checkpoint;
- execute compensating step when defined;
- restore mandatory Backup restore point;
- require manual intervention.

### Recovery mode
WPE reset recovery UI/CLI reads journal and offers only valid next actions. It does not invent successful state from missing journal data.

WordPress core Recovery Mode is designed for fatal plugin/theme errors; WPE may coexist with it but does not misuse it as the reset transaction journal.

## Reset report

Final report includes:
- plan/run IDs;
- requested/preserved scopes;
- counts by domain;
- restore point;
- duration/stages;
- warnings/errors;
- post-health checks;
- manual follow-up.

Optional browser screenshots can be UX evidence, but screenshots/video are **not** recovery truth and are never required instead of structured journal/backup evidence.

## Retention

Reset journals/reports retained according to operational/security policy; sensitive recovery secrets are not embedded.

Cleanup of recovery manifests only after completed/recovered state and configured retention.

## Future executable evidence — NOT AUTHORIZED

- partial failure after every stage;
- DB engine transaction/DDL behavior;
- user/role last-admin reset;
- full options/content/users resets;
- plugin/theme state;
- uploads/local/offload deletion;
- Job/cron races;
- multisite site vs network reset;
- restore-point integration;
- power/process crash resume;
- fresh login/health verification;
- filesystem journal unwritable case.

No reset operation, table deletion, file deletion or recovery store has been implemented.