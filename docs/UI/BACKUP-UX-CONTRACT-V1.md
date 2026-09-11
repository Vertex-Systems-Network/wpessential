# Backup — Provisional UX Contract V1

Surface: **24 / Backup**  
Planning issue: **#590**  
Supervisor wave: **#583**  
Exact-main claim anchor: `dab9944bf7ad174ceffb3b48ddebffac4ea71bcc`

Lifecycle status: **PROVISIONAL PLANNING ONLY**. Surface 24 remains `UNSEEDED / 0` in the Master Options Bank. This document does not promote `UX_CONTRACT_COMPLETE` or authorize backup/restore side effects.

## Product model

The UI keeps these concepts separate:

- **Backup Plan** — reusable scope/schedule/destination/retention policy;
- **Backup Set** — one captured recovery artifact set;
- **Destination Copy** — one persisted copy at a provider/location;
- **Verification result** — actual V-level confidence/evidence;
- **Restore Run** — separately authorized recovery operation;
- **Restore Point** — protected recovery state used before destructive operations.

A green status must never collapse partial, unverified or restore-not-tested states into generic “Success”.

## Information architecture

1. Overview
2. Backup Sets
3. Backup Plans
4. Destinations
5. Restore Runs
6. Activity / Logs
7. Diagnostics / Recovery readiness

## Progressive disclosure

### Essential

Backup Plan editor exposes:

- name/status;
- scope preset;
- schedule/manual mode;
- destination refs;
- encryption on/off + recovery readiness summary;
- minimum verification target;
- retention summary;
- failure/partial notification policy;
- Validate / Save.

Backup Set creation shows exact capture scope, destination expectations and verification target before any future execution.

### Advanced

Adds:

- explicit DB/file include/exclude rules;
- archive/compression/split policy;
- required vs optional destination mirrors;
- retention/GFS/minimum verified copies;
- destination verification policy;
- missed-run/overlap behavior;
- cleanup/prune policy;
- local staging retention;
- preflight disk/resource estimates;
- dependency/usage summary.

### Expert

Adds bounded operational controls:

- provider-certified archive/storage features;
- chunk/concurrency ceilings;
- DB consistency strategy where supported;
- symlink/change-during-read policy;
- advanced environment mapping for restore;
- recovery-material provider/profile refs;
- restore compatibility/degraded diagnostics.

No Expert control exposes raw filesystem paths outside approved roots, credentials/recovery keys, weak encryption, TLS bypass or unbounded concurrency.

### System / Diagnostics

Read-only diagnostics show:

- Job runner health;
- local temp/storage capacity;
- destination/provider health;
- credential presence state;
- latest capture/upload/verification result;
- verification tier and method;
- encryption/recovery readiness;
- stale/missing/corrupt copy indicators;
- restore compatibility blockers;
- destructive-operation locks;
- site/network/environment identity.

## Overview

Cards must distinguish:

- last captured backup;
- last locally verified backup;
- last remotely verified backup;
- last restore-tested backup;
- latest failed/partial state;
- protected restore points;
- destination health;
- next run/runner health;
- recovery-key readiness;
- latest restore status.

## Backup Sets list

Columns include label/UUID, created time, trigger, scope, source environment, size, encryption, verification tier, destination copies, protected/restore-point state, retention expiry, status, duration and actor.

Row actions are Policy/state-aware: Details, Verify, Restore, manifest download, protected artifact download, copy, Protect/Unprotect, retry failed destination, eligible delete.

Deleting/copying is never enabled merely because a row exists; dependency/last-verified/protected rules apply.

## Create Backup wizard

Steps:

1. Identity
2. Scope preset
3. Database options
4. Files options
5. Archive/encryption
6. Destinations
7. Verification target
8. Notifications
9. Review / Preflight

Review screen shows included/excluded scope, estimates, destinations, encryption/recovery readiness, verification target, compatibility warnings, destructive locks and runner health.

No actual Start Backup action is authorized by this provisional contract.

## Destination UX

Destination editor shows non-secret provider settings, certification/health and credential presence only. Credentials are managed through Surface 23/Vault controls and never read back.

Tests should distinguish connection, upload, download and delete probe capabilities; destructive probe actions require an isolated provider test location and explicit support.

TLS verification is mandatory. Provider-specific immutable/object-lock/storage-class capabilities are shown only when actually supported.

## Verification UX

Use explicit tiers rather than one generic success:

- captured/unverified;
- local integrity verified;
- remote copy verified;
- restore-tested/certified where a real target exists.

Show method/timestamp/failure and actual verified target. Never infer restore confidence merely from successful upload.

## Retention UX

Retention editor shows:

- keep last N / duration;
- protected/manual restore-point exclusions;
- GFS when supported;
- minimum verified copies;
- partial/failed artifact prune;
- per-destination override.

Safety ordering is visible: create + verify replacement before pruning old recovery copy where policy requires.

## Restore wizard

Future restore flow requires:

1. source selection with verified-only default;
2. integrity/decryption/environment preflight;
3. restore scope;
4. target behavior and mapping;
5. plugin/theme strategy;
6. mandatory pre-restore point by default;
7. explicit impact/recovery confirmation;
8. execution only through separately authorized runtime.

Blocked conditions are visually separate from warnings. A restore cannot be made “available” by simply dismissing integrity/decryption/authorization blockers.

## Pre-restore point

Default requirement. Unsafe override, if ever supported, uses a dedicated high-risk ability, recent re-auth, explicit phrase/reason and audit. Ordinary backup administrators do not automatically receive it.

## Delete UX

Delete copy vs delete all copies/set metadata are separate actions. Protected/last-verified copies require stronger confirmation or are blocked by active safety policy.

Provider delete failure remains `delete_failed`; UI must not optimistically mark deleted.

## Accessibility

- keyboard-operable wizards/tables/actions;
- semantic phase/status/verification labels;
- no color-only success/partial/unverified states;
- linked preflight errors and focus management;
- accessible progress/status announcements without noise;
- responsive tables/wizards;
- destructive confirmations understandable without relying on icons;
- axe coverage for normal, partial, blocked restore and degraded provider states once implemented.

## Multisite

Future normalized contract must explicitly define network vs individual-site capture, tables/files/users/uploads scope, restore target rules, network/site URL mapping, provider credentials, retention and permissions. Site restore must not silently corrupt network/global tables; network restore requires separately reviewed semantics.

## Portability

Backup manifests/artifacts preserve source environment/provenance/schema/compatibility metadata. Importing a backup does not imply immediate restore; integrity/decryption/dependency preflight remains mandatory. Generic data/package import semantics remain Surface 26.

## Lifecycle decision

This is provisional interaction guidance only. Exact-main Master Options Bank remains `UNSEEDED / 0`; Bank review and schema-valid Atomic Option Contracts must precede UX lifecycle/runtime promotion.
