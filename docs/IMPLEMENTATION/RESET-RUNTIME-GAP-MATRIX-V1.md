# Reset — Runtime Gap Matrix V1

Surface: **25 / Reset**  
Planning issue: **#591**  
Supervisor wave: **#583**  
Exact-main claim anchor: `97f9c29ffad14e1124a7565e8dd4e027e9ef7d10`

Status: **PLANNING GAP MATRIX / RUNTIME NOT AUTHORIZED**

Surface 25 remains `UNSEEDED / 0` in the Master Options Bank and exact main has no dedicated Reset runtime module. This matrix records prerequisites and owner boundaries only.

## Gap matrix

| Area | Exact-main state | Future requirement | Boundary / gate |
|---|---|---|---|
| Master Options Bank | **BLOCKED — UNSEEDED / 0** | Native/market reviewed normalized records. | Product gate before option contracts/runtime. |
| Atomic Option Contracts | **PLANNING INVENTORY ONLY** | Schema-valid contracts after Bank review. | No lifecycle promotion from inventory alone. |
| Reset Profile definitions | **ABSENT** | stable profile identity/revision/lifecycle. | Surface 25. |
| Scope compiler | **ABSENT** | owner-aware content/settings/module/runtime selections. | No generic wipe. |
| Impact Preview | **ABSENT** | exact counts/dependencies/preservation/restore-point state + fingerprint. | Must precede destructive execution. |
| Drift validation | **ABSENT** | safety-critical re-evaluation immediately before execution. | Material drift re-preview/block. |
| Restore-point integration | **DEPENDENCY** | verified Backup ref/tier/age/protection. | Surface 24 Backup owns artifact truth. |
| Re-auth/confirmation | **ABSENT** | impact-tiered server-side controls. | Dedicated high-risk abilities. |
| Preservation engine | **ABSENT** | mandatory operator/recovery-admin/Vault/audit/backup protections. | Cannot be casually overridden. |
| Module/runtime deletion | **ABSENT** | owner-registered delete contracts. | No generic table wipe. |
| Content/taxonomy/comments/settings | **ABSENT** | WordPress-owner-aware bounded deletion/reset semantics. | No broad SQL shortcuts. |
| User reset | **BLOCKED/ADVANCED** | operator/recovery-admin/Super Admin/reassignment/privacy safety. | Separate explicit authorization. |
| Schema/table reset | **BLOCKED/ADVANCED** | only owning-contract truncate/drop. | Raw DB destructive actions forbidden. |
| Locks/journal | **ABSENT** | exclusive destructive locks + phase/recovery journal. | Reset/Restore/Import/migration coordination. |
| Post-reset verification | **ABSENT** | boot/health/dependency checks and explicit result. | No false rollback/success claims. |
| Recovery | **ABSENT** | recoverable/recovery-required state and Backup-driven actions. | Separate ability/re-auth. |
| Audit/history | **ABSENT** | safe IDs/counts/scope/reason/outcomes, no deleted payload dump. | Shared Audit. |
| Multisite | **UNSPECIFIED** | certified network/subsite behavior or hard block. | No best-effort destructive mode. |
| Accessibility | **NO SURFACE UI** | preview/confirmation/progress/recovery accessibility. | Browser/axe evidence later. |
| Compatibility | **NO SURFACE RUNTIME** | WP/PHP/DB/filesystem/module owner matrix. | Exact-head evidence later. |
| Reliability/performance | **NO SURFACE RUNTIME** | bounded batches/jobs, idempotent phases where possible, recovery. | Deterministic tests later. |

## Destructive safety hard gates

Future implementation must reject or fail closed on:

- run without current server-authoritative impact preview;
- required restore point missing/stale/unverified;
- current operator/last recovery admin/Super Admin deletion;
- raw SQL/table/option/path wildcard destructive actions;
- generic module table wipe without owner delete contract;
- concurrent Reset/Restore/destructive Import/schema migration conflicts;
- silent Backup requirement downgrade;
- arbitrary PHP/shell post-reset actions;
- unsupported multisite destructive scope;
- claiming rollback/recovery completed without verification.

## Required Policy / Ability separation

Separate read/profile-edit/preview/run/status/history/recovery abilities from high-risk unsafe-override, user-reset and schema-reset abilities. Run/recovery mutations require server-side Policy; high-impact scopes require recent re-auth and explicit confirmation. AI defaults remain read/preview/explain only.

## Accessibility evidence required later

- keyboard preview/confirmation/history/recovery flows;
- focus after validation/drift/re-auth failures;
- non-color-only severity/recovery states;
- accessible exact-count summaries and preservation lists;
- phase progress live regions without noise;
- axe coverage for Level 1/2/3 confirmation and recovery-required states.

## Multisite evidence required later

- subsite vs network ownership;
- network users/options/Super Admin protection;
- network-active plugins/themes;
- site tables vs global tables;
- Backup restore-point scope matching Reset scope;
- unsupported combinations blocked before mutation.

## Reliability/performance evidence required later

1. preview counts and owner dependencies are reproducible;
2. material drift invalidates critical confirmation;
3. required restore point remains protected through failure/recovery;
4. last recovery admin cannot be removed under race;
5. owner-specific deletion boundaries are enforced;
6. phases/journal are idempotency/recovery aware where possible;
7. concurrent destructive operations cannot overlap conflicting stores;
8. midway failure enters explicit recoverable/recovery-required state;
9. post-reset health checks distinguish warnings/failure;
10. large content deletion is batched/job-backed, not one unbounded request;
11. audit stores safe summary, not deleted private payloads;
12. re-auth/confirmation cannot be bypassed by direct request.

## Future implementation order

Only after Bank review + schema-valid option contracts:

1. Profile definitions + read-only impact/preflight;
2. preservation + Backup restore-point validation;
3. low-risk cache/WPE config resets through owner contracts;
4. bounded content/settings/module cleanup;
5. locks/journal/post-reset health/recovery;
6. separately authorized high-impact/user/schema scopes;
7. multisite certification/degraded-state closure;
8. exact-head security/accessibility/compatibility/performance certification audit.

## Exit decision

Exact main has a mature exhaustive Reset specification and clear Backup/owner boundaries, but the Master Options Bank is still `UNSEEDED / 0` and no Reset runtime exists. `runtime_allowed=false` remains correct.

The next valid action is Bank seeding/native/market review, then schema-valid option contracts and UX re-review — not destructive execution from this planning lane.
