# ADR-0161 — Reset Manager Canonical Executable Evidence Refinement

Status: **Accepted evidence refinement; execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP44`  
Execution mode: `PLANNER_ONLY`

## Decision

Refine the canonical Reset Manager executable evidence protocol in place from `RM-01…RM-48` to **`RM-01…RM-176`**, preserving original fixture intent and binding destructive orchestration to current Backup/Restore, RA/UP, JobService, DSR/FST/REL/CTB, ERR/PDL/VER/MLC and MSI/LC contracts.

Canonical protocol:
- `docs/QUALITY/RESET-MANAGER-EXECUTABLE-EVIDENCE-PROTOCOL.md`

## Preserved destructive-safety boundaries

- Reset is staged destructive orchestration, not one database command or universal SQL transaction.
- A verified recovery/restore point is distinct from a Run journal and is required according to Reset risk profile.
- WordPress Recovery Mode is fatal-error assistance, not transactional data rollback.
- WPE never reports rollback/recovery/success unless reversal/restore/post-health evidence proves it.
- Recovery-principal viability remains mandatory; generic user/role/security-secret deletion is prohibited.
- Duplicate Jobs, expired leases and unknown outcomes require fencing/reconciliation before destructive retry.
- Site/network/global ownership is explicit; current blog context never authorizes another site's deletion.

## Refinement scope

`RM-01…RM-176` now fixes evidence for:
- Profile Draft/publish/version/scope/impact fingerprints and dependency graph;
- DSR/FST/REL/CTB owner-specific reset/delete capability boundaries;
- Backup verification tier, freshness, completeness, encryption-key recoverability and pinned restore identity;
- dedicated capabilities, recent-auth, confirmation binding and anti-lockout;
- destructive locks/fencing/Job crash windows and idempotent reconciliation;
- staged DB/filesystem/provider mutation truth and partial outcomes;
- plugin/theme/options/media/users/roles/Membership/Vault boundaries;
- Restore/recovery and mandatory post-health evidence;
- Multisite/global tables/site lifecycle/clone/import/migration/privacy coordination;
- ERR/Audit/PDL retention/redaction and support diagnostics;
- 10k/100k/1M entities, large relation/media/user and 100/1k/10k-site scale/fault injection.

## Current evidence state

- `RM-01…RM-176` documented.
- **RM executed: 0/176.**
- Reset runtime/recovery certifications: **0**.
- No Reset Run, destructive lock, journal, deletion, option/user/role/Vault mutation, plugin/theme change, Backup/Restore, recovery action, Multisite operation or benchmark has executed.

## Development gate

This ADR is planning/evidence documentation only. It grants no implementation, destructive action or executable-test authorization. ADR-0014 and `DEVELOPMENT-CONSENT.md` remain authoritative.
