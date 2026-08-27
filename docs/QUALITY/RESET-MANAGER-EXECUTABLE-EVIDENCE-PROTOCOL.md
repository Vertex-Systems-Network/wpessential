# WPEssential — Reset Manager Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0047, Reset Manager spec, Backup/Restore, Site Lifecycle, Role anti-lockout, JobService, ADR-0014.

## 1. Purpose

Define the evidence required before any WPEssential Reset operation can be enabled in production.

Reset is a destructive orchestration workflow, not a single database command and not a use case for pretending WordPress Recovery Mode provides transactional rollback.

## 2. Accepted execution architecture under test

`Reset Profile → observed-state fingerprint → impact Plan → recovery-principal validation → verified restore point → Level 3 confirmation/re-auth → destructive-operation lock → durable Reset Run journal → staged mutation → per-stage verification → post-reset health/reconciliation → release lock`

If a stage fails, state remains `failed_recoverable`/`recovery_required`; WPE never labels it rolled back unless restore/reversal was actually verified.

## 3. Durable Run/Journal candidate

Future Reset Run must record enough non-secret operational state to resume/reconcile safely:
- Run UUID;
- Profile Definition revision;
- target site/network scope;
- impact fingerprint;
- actor/re-auth confirmation reference;
- verified Backup/Restore Point UUID + verification tier;
- recovery-principal snapshot/fingerprint;
- operation plan version;
- current stage/state;
- committed stage checkpoints;
- destructive lock identity/lease;
- plugin/theme active-state snapshot refs;
- safe counts before/after;
- error/recovery classification;
- Job/correlation IDs;
- post-health results.

Journal does not store Backup encryption keys, passwords or user content values unnecessarily.

## 4. Reset scope profiles

### RS-S1 — WPE configuration only
Definition changes separate from runtime-data deletion and Vault credentials.

### RS-S2 — selected WPE module runtime data
Requires owner/module cleanup contract and dependency graph.

### RS-S3 — selected content
Post types/status/date/query selection with taxonomy/comment/media relationship semantics.

### RS-S4 — selected settings
Only registered/known options/settings; no arbitrary wildcard delete.

### RS-S5 — users advanced
Not normal preset. Requires explicit recovery-principal invariant and stronger policy.

### RS-S6 — full site baseline reset
Highest-risk; must preserve WordPress installation viability, recovery principal and target scope. Multisite semantics independently certified.

## 5. Fixture matrix

### RM-01 — WPE-config-only dry run
Counts/dependencies accurate; no runtime data mutation during preview.

### RM-02 — Runtime-only reset
Definitions retained according to profile; selected module runtime removed only in target scope.

### RM-03 — Selected CPT/content reset
Only selected records/status/date boundary affected.

### RM-04 — Taxonomy/comment/media dependency preview
Impact correctly reports linked entities before mutation.

### RM-05 — Settings registry selection
Known options only; arbitrary wildcard/unknown core option cannot be selected through normal UI.

### RM-06 — Users excluded normal preset
Default cannot delete users.

### RM-07 — Advanced user reset last recovery principal
Blocked.

### RM-08 — Super Admin/Multisite recovery invariant
Site reset cannot remove network recovery authority or grant new network authority.

### RM-09 — Restore point unavailable
Normal destructive Reset blocked.

### RM-10 — Restore point exists but unverified
Blocked under required verification policy.

### RM-11 — Restore point corrupt after selection
Pre-execution revalidation catches and blocks.

### RM-12 — Backup destination unavailable after local verification
Policy determines whether sufficient verified local/alternate copy exists; UI truthful.

### RM-13 — Impact state changes after preview
Fingerprint mismatch requires new preview/reconfirmation.

### RM-14 — Concurrent Reset attempt
Second destructive Run rejected/queued according to lock policy.

### RM-15 — Reset vs Restore race
Mutually incompatible destructive-operation locks prevent overlap.

### RM-16 — Reset vs Import migration race
Conflict detected before unsafe concurrent mutation.

### RM-17 — Current actor loses capability before start
Revalidation blocks.

### RM-18 — Current actor would self-lockout
Block unless accepted flow proves another viable recovery principal and high-risk confirmation.

### RM-19 — Level 3 confirmation/re-auth missing
Block.

### RM-20 — Plugin/theme keep-state default
Installed/active state preserved when profile says keep.

### RM-21 — Deactivate selected plugins
Required WPE/core/recovery components protected according to plan.

### RM-22 — Plugin deactivation fails
Journal records partial state and stops/reconciles; no false success.

### RM-23 — Theme switch/reset failure
Prior viable theme/recovery path maintained/recoverable.

### RM-24 — Failure before first destructive stage
Safe cancel; no recovery restore required beyond journal cleanup.

### RM-25 — Failure after first committed deletion
Run becomes recoverable/recovery-required; no claim of atomic rollback.

### RM-26 — Process killed mid-stage
Restart uses journal/checkpoint + observed state to reconcile, not blindly repeats destructive step.

### RM-27 — Job delivered twice
Same Reset logical stage is idempotent/reconciled; no second unrelated destructive pass.

### RM-28 — Lost Job backend row
Durable Reset Run truth allows reconciler to reschedule safe next work.

### RM-29 — Database outage mid-reset
Stops safely; journal/restore point preserved.

### RM-30 — Filesystem failure mid-reset
Same.

### RM-31 — Restore recovery from selected Backup
Recovery restores expected scope and verifies health before `recovered` state.

### RM-32 — Restore itself fails
Run remains recovery-required; surfaces manual recovery instructions/correlation, no false rollback success.

### RM-33 — Post-reset health failure
Reset not marked successful merely because deletion stages ended.

### RM-34 — Core login/admin viability
Required administrator can still authenticate/access native recovery surface after successful reset.

### RM-35 — WPE safe/recovery mode
Can bypass broken WPE overlays only; does not mint WordPress authority.

### RM-36 — WordPress Recovery Mode present
WPE correctly treats core Recovery Mode as fatal-error assistance, not Reset transaction state.

### RM-37 — Site A reset in Multisite
Site B runtime/config untouched.

### RM-38 — Network-scoped reset attempt from Site Admin
Denied.

### RM-39 — Site archived/deleted during Reset
Site Lifecycle Coordinator reconciles/drains; no wrong-site continuation.

### RM-40 — Restored database contains copied active Reset Run
Run does not blindly resume on restored timeline without revalidation.

### RM-41 — Pro expiry during active Reset
Already-started destructive safety/recovery handling follows explicit run policy; license state cannot abandon site half-mutated without recovery path.

### RM-42 — Reset definition edited during Run
Pinned Profile/Plan remains unchanged.

### RM-43 — Audit/log privacy
Counts/IDs safe; no passwords/Vault plaintext/content dump.

### RM-44 — Optional screenshot failure
Never blocks authoritative restore point/reset semantics; screenshot/video remains non-authoritative convenience only.

### RM-45 — Low memory/time environment
Bounded stages/checkpoints; no one-request full reset dependency.

### RM-46 — 100k/1M content records
Chunked deletion/cleanup/reconciliation evidence where scope permits.

### RM-47 — Relation/Field/Listing dependencies
Dependency invalidation/cleanup is explicit and no orphaned cross-domain access is silently left where owning contract requires cleanup.

### RM-48 — Vault credentials selected for deletion
Separate explicit destructive warning; no secret contents written to journal/backup metadata beyond accepted Vault backup profile.

## 6. Recovery classification

Every stage/operation declares:
- reversible locally;
- compensatable;
- recoverable only by verified Backup;
- irreversible after recovery window.

UI uses **Recovery**, not “Rollback,” when transactional rollback cannot be guaranteed.

## 7. Pass gates

Production Reset fails if:
- destructive operation can run without required verified restore point/policy;
- all viable recovery principals can be removed through ordinary flow;
- stale impact plan executes after material state drift;
- duplicate Job can repeat non-idempotent destructive stage;
- crash cannot determine/reconcile committed stage;
- cross-site data is deleted;
- failed recovery is labeled success;
- core WordPress Recovery Mode is relied upon as data rollback mechanism;
- restore point/key/secret safety is weakened to make reset convenient.

## 8. Required future evidence report

Include:
- Reset profile/scope;
- WordPress/DB/filesystem environment;
- Backup verification tier/profile;
- Job backend/profile;
- RM-01…RM-48 pass/fail;
- crash points tested;
- recovery/restore results;
- health checks;
- Multisite results;
- performance/throughput;
- unresolved destructive risks.

## 9. Current state

**RM fixtures executed: 0/48.**

No Reset Run, lock, journal, deletion, plugin/theme mutation, Backup/Restore or recovery action has executed.

## 10. Development gate

Execution requires explicit owner consent under ADR-0014.