# ADR-0141 — Multisite Scope / Isolation & Site Lifecycle Evidence Refinement

Status: **Accepted evidence refinement / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP24`

## Decision

Refine the two existing canonical Multisite evidence protocols in place rather than creating duplicate protocols:

- `docs/QUALITY/MULTISITE-SCOPE-ISOLATION-EVIDENCE-PROTOCOL.md` → fixed **MSI-01…MSI-160** matrix;
- `docs/QUALITY/MULTISITE-SITE-LIFECYCLE-EVIDENCE-PROTOCOL.md` → fixed **LC-01…LC-96** matrix.

The existing Multisite certification levels **MS0–MS4** and Site Lifecycle levels **SL0–SL4** remain valid and are not redefined by this ADR.

Legacy named Multisite fixture families and LC-001…LC-040 semantics are preserved inside the refined matrices but are superseded as future execution identifiers by the fixed MSI/LC IDs.

## Why refine rather than duplicate

The prior protocols already contained the correct architectural direction but had inconsistent fixture granularity:

- Scope/Isolation used many semantic prefixes without one fixed execution count;
- Site Lifecycle had only 40 fixtures while lifecycle responsibilities now span Definition, Jobs, Membership, Vault, Product License, Backup, provider side effects, clone/transfer/disaster and large-network recovery.

Refining the canonical files preserves source-of-truth continuity while making future evidence repeatable and reportable.

## Fixed MSI coverage

- scope identity/topology/activation — MSI-01…MSI-16;
- authorization/IDOR/context restoration — MSI-17…MSI-32;
- settings/definitions/inheritance/cache — MSI-33…MSI-48;
- Jobs/Cron/Workflow/events/fan-out — MSI-49…MSI-64;
- users/roles/Profile/Membership — MSI-65…MSI-80;
- Vault/Connections/providers/private assets — MSI-81…MSI-96;
- Query/Relations/Listings/REST/Abilities/Import — MSI-97…MSI-112;
- Backup/Reset/operations/protection — MSI-113…MSI-128;
- lifecycle/clone/migration/transfer/disaster — MSI-129…MSI-144;
- scale/failure/observability/certification truth — MSI-145…MSI-160.

## Fixed LC coverage

- provisioning/initialization/idempotency — LC-01…LC-16;
- archive/spam/restrict/domain/reactivation — LC-17…LC-32;
- uninitialization/deletion/storage cleanup/recovery gates — LC-33…LC-48;
- runtime overlap/Jobs/Workflows/Membership/remote reconciliation — LC-49…LC-64;
- crashes/clone/migration/transfer/disaster restore — LC-65…LC-80;
- authorization/scale/deactivation/uninstall/privacy/certification truth — LC-81…LC-96.

## Accepted invariants

1. site/network ownership is explicit and cannot fall back to current blog context;
2. WordPress numeric site IDs, WPE site UUIDs, installation/network identities and commercial Allocation IDs remain separate;
3. Site Admin authority never implicitly becomes Network/Super Admin authority;
4. `switch_to_blog()` is context management, not authorization or code-loading authority;
5. site/network caches, jobs, workflows, provider operations and rendered data remain scope-bound;
6. normal site Query/Relation/REST/Ability operations cannot become arbitrary cross-site operations through request parameters;
7. shared network secrets can be delegated for use without plaintext disclosure or ownership transfer;
8. site Membership/roles/access do not propagate to siblings by shared global user identity alone;
9. site lifecycle transitions revalidate authority before queued/delayed side effects;
10. site deletion never implies global-user deletion, billing cancellation, shared-secret deletion or universal privacy erasure;
11. remote unknown outcomes remain pending/reconcilable rather than optimistic success;
12. clone/restore cannot silently resurrect production allocation, OAuth/provider authority or stale access state;
13. destructive cleanup is storage-class/domain-aware and requires verified recovery when policy demands it;
14. MS0/SL0 static mapping is not runtime certification;
15. large-network support claims require executed measured profiles, not architecture alone.

## Current evidence state

- MSI documented: **160**.
- MSI executed: **0/160**.
- runtime-certified surfaces at MS1+: **0**.
- LC documented: **96**.
- LC executed: **0/96**.
- SL0–SL4 runtime certifications: **0**.
- 31/31 product/platform surfaces retain static Multisite scope mapping.
- no Multisite runtime/network/lifecycle implementation or certification exists.

## Rejected shortcuts

- network activation as proof of Multisite support;
- current-blog context as durable ownership;
- arbitrary `site_id` as authority;
- Super Admin as automatic bypass of WPE high-risk Policy/recovery rules;
- cross-site cache reuse for protected output;
- unbounded network fan-out in one request;
- copied clone identifiers as proof of valid second production allocation;
- queue cancellation represented as rollback;
- site delete represented as billing cancel/global-user delete/privacy erasure;
- single-site Backup restore evidence promoted to network restore support;
- 10k-site support claim without executed measured environment;
- unexecuted dependency evidence converted into an MSI/LC pass.

## Development gate

No Multisite network creation, plugin activation, site creation/archive/delete, context-switch runtime test, job/workflow/provider execution, schema/data mutation, Product License call, Backup/Restore/Reset/Import, clone/transfer or scale benchmark is authorized by this ADR.

ADR-0014 and the Approval Ledger still require explicit scoped owner consent before executable evidence or implementation.

Current counts remain **MSI 0/160; LC 0/96**.