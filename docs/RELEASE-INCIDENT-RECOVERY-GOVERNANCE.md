# WPEssential — Release, Incident & Recovery Governance

Status: **Active governance / no production release authorized**  
Last reviewed: 2026-08-28

## 1. Release state machine

Use these states distinctly:

- `BUILT` — artifact/build completed successfully in the recorded environment.
- `DEPLOYED` — revision/artifact was applied to a target environment.
- `RELEASED` — intended release process/version became available to its target audience/environment.
- `PRODUCTION_VERIFIED` — production behavior was explicitly checked after release and critical health/acceptance checks passed.

Rules:
- `BUILT` does not imply deployed.
- `DEPLOYED` does not imply released successfully.
- `RELEASED` does not imply production verified.
- a command returning success is not sufficient production evidence.

## 2. High-risk release record

Before a high-risk release know and record:
- exact revision/version/artifact;
- dependency changes;
- schema/migrations/backfills;
- configuration/feature flags;
- security status;
- tests/evidence executed;
- known baseline/flaky failures;
- deployment ordering;
- rollback/recovery route;
- backup/restore prerequisite where relevant;
- post-deployment verification;
- stop-the-line criteria.

## 3. Recovery classification

Every high-risk change/release is classified as one of:

- `SIMPLE_ROLLBACK` — prior artifact can be restored without data/schema compatibility complexity.
- `ROLLBACK_WITH_COMPATIBILITY` — rollback requires compatible schema/config/data transition or staged reversal.
- `FORWARD_FIX_PREFERRED` — reverting application code is more dangerous than applying a bounded corrective change due to data/state evolution.
- `IRREVERSIBLE` — operation cannot be reliably undone; recovery depends on restore/reconstruction/compensating action.

`IRREVERSIBLE` requires stronger review, recovery evidence and explicit authorization.

## 4. Expand → Migrate → Contract

For risky schema/data evolution, prefer where practical:

`EXPAND → MIGRATE/BACKFILL → VERIFY → CONTRACT`

Purpose:
- permit old/new application coexistence during rollout;
- reduce one-shot destructive migrations;
- enable verification before removal of old schema/path.

Do not use this pattern mechanically where it adds risk/complexity without benefit.

## 5. Incident mode

When production safety is threatened, switch execution mode to:

`INCIDENT`

Priority becomes:

`STABILIZE → CONTAIN → PRESERVE EVIDENCE → DIAGNOSE → RECOVER → VERIFY → ROOT CAUSE → PREVENT RECURRENCE`

During active containment:
- pause unrelated feature work;
- minimize broad refactoring;
- preserve logs/state/diffs needed for diagnosis;
- make the smallest safe recovery change;
- keep operator/user communication factual and evidence-based.

## 6. Stop-the-line triggers

Immediately stop affected work and preserve evidence for:
- unexpected data loss;
- cross-user/cross-site/cross-tenant protected data leakage;
- credential/token/private-key exposure;
- critical authorization/authentication bypass;
- destructive unknown/unreviewed command;
- migration/schema corruption;
- archive/path operation escaping intended scope;
- unexplained massive diff or generated change outside approved scope;
- repository state that cannot be safely understood;
- release/deployment acting on the wrong environment/site/tenant;
- backup/restore verification failure before a dependent destructive action;
- security control unexpectedly failing open.

Do not “push through” simply to finish a milestone.

## 7. Stop-the-line response

When triggered:
1. stop new affected mutations/jobs/deployments where safely possible;
2. preserve logs, revisions, error output and environment identity;
3. classify data/security blast radius;
4. verify current authoritative state;
5. enter `RECOVERY` project/work state if required;
6. select rollback/forward-fix/restore/containment route;
7. execute only authorized recovery actions;
8. verify recovery;
9. document root cause and permanent prevention work.

## 8. Production verification

Production verification should check as applicable:
- deployed revision/version;
- app/plugin health;
- migrations/schema state;
- authentication/authorization critical paths;
- critical user workflows;
- jobs/queues/cron;
- external integrations;
- logs/error rate;
- performance regressions;
- cache behavior;
- security headers/gates;
- backup/restore readiness after schema-changing releases.

Only then may the release enter `PRODUCTION_VERIFIED`.

## 9. Incident evidence and root cause

Record:
- incident ID;
- first detected time;
- detecting signal/person/system;
- affected version/revision/environment;
- impact/blast radius;
- containment actions;
- recovery actions;
- data/security implications;
- verification evidence;
- root cause;
- contributing factors;
- prevention tasks/tests/monitoring;
- unresolved risk.

Do not alter historical evidence to make the incident appear cleaner.

## 10. Recovery authority

Recovery urgency does not remove authorization/security boundaries.

Emergency actions still require the strongest available legitimate authority for the affected environment. WPEssential must not create an anonymous/public break-glass path that bypasses WordPress/native authorization.

## 11. Current WPEssential state

Release state: **none**  
Production deployment: **none**  
Production verification: **none**  
Current execution mode: `PLANNER_ONLY`  
Current project state: `PLANNED_EXISTING_PROJECT`

These rules are predeclared for future implementation/release work and do not imply that any release/incident operation has occurred.