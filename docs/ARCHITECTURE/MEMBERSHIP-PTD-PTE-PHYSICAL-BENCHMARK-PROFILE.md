# WPEssential Membership — PT-D/PT-E Physical Benchmark Profile

Status: **Phase 0 paper architecture / benchmark profile only / no DDL authorized**  
Related: ADR-0013, ADR-0015, ADR-0016, ADR-0019, ADR-0020, ADR-0024, ADR-0057, ADR-0062, ADR-0066, ADR-0069, ADR-0071, Membership Runtime Data Candidate, P-012.

## Purpose

Narrow Membership runtime storage into a comparable future physical benchmark without changing accepted Membership semantics or pretending that a benchmark baseline is a final schema.

Membership configuration remains in the Definition Repository. This document covers transactional Membership runtime only.

## Benchmark profiles

### M1 — PT-D shared scoped Membership runtime — first benchmark baseline

One network/global WPE table family with explicit logical scope coordinates on every site-owned row.

Candidate store family:
- Enrollments;
- Materialized Entitlements;
- Enrollment Transitions;
- Membership Overrides;
- Principal Access Generations;
- Teams;
- Team Members;
- Invitations.

### M2 — PT-E per-site Membership runtime — mandatory comparison

Equivalent logical stores provisioned per site using the same semantic contract.

M2 is not assumed safer or faster merely because tables are physically separated. It must be measured against migration fan-out, provisioning, large-network operations, Backup/Restore, supportability and noisy-neighbor behavior.

The benchmark may later prove a mixed topology is superior for a particular subdomain, but no mixed split is accepted without evidence and a superseding/clarifying ADR.

---

## Scope identity

For M1, every site-owned row carries explicit:
- `network_id`;
- `site_id`;
- domain runtime identity/UUID.

WordPress `user_id` is a network/global user identity, not Membership scope. A user having an Enrollment on Site A never implies an Enrollment, Entitlement, Team seat or override on Site B.

Network-owned Membership resources require an explicit future product contract; network activation or Super Admin status does not silently transform site-owned Membership runtime into network-global rights.

All hot queries must bind scope before principal/resource predicates.

---

## Enrollment physical invariants

Enrollment remains authoritative current lifecycle state for one subject + Plan interval.

Candidate physical invariants:
- numeric internal primary key plus stable runtime UUID;
- explicit site/network scope;
- principal/user reference;
- stable Plan UUID and optional Plan Group UUID;
- lifecycle state from the accepted state machine;
- provider/source identity fields are bounded normalized references, not raw provider objects;
- all access-affecting timestamps are stored in UTC-compatible form;
- monotonic state/concurrency generation;
- entitlement/access generation trace;
- created/updated timestamps.

Candidate hot index families to benchmark:
- scope + principal + eligible state/time;
- scope + Plan + state;
- scope + source type/connection/subscription reference;
- scope + state + expiry/grace/scheduled timestamps;
- scope + Plan Group + principal for exclusivity checks.

Exact columns, order, prefix lengths and null handling remain P-012 evidence.

---

## Entitlement physical invariants

Materialized Entitlements are derived current grants; they are not commercial billing records and not the sole reconstructable source of truth.

Candidate invariants:
- scope + principal;
- stable normalized entitlement key;
- entitlement resource/scope tuple where applicable;
- source type + source UUID;
- Plan trace where useful;
- validity interval;
- state/generation;
- no secret/provider payload.

Hot index families to benchmark:
- scope + principal + active state;
- scope + principal + entitlement key + resource scope;
- scope + source UUID for rebuild/revoke;
- scope + validity/state for cleanup.

The ordinary authorization path must be able to obtain required active grants without N queries per rule.

---

## Principal Access Generation

M1 first benchmark includes a dedicated small current-generation store keyed by scope + principal.

Purpose:
- make access-affecting mutations produce one monotonic token;
- let stale authorization caches become unreachable immediately after generation change;
- support diagnostics showing the generation used for a decision.

This store does not grant access itself.

Candidate mutation rule:
1. lock/read current Membership source state;
2. validate transition/idempotency;
3. write Enrollment/team/override mutation;
4. diff/rebuild affected Entitlements;
5. increment principal access generation;
6. commit;
7. invalidate optional caches;
8. emit events/jobs after commit.

If optional cache invalidation fails after commit, request-time authorization must still be correct by using current database state/generation. Cache availability is never an authorization dependency.

Exact generation locking/storage remains evidence-gated.

---

## Transition history

Enrollment Transition history is immutable domain history, separate from generic Audit.

Benchmark index families:
- scope + Enrollment + transition sequence/time;
- scope + processed/effective time;
- scope + external event/reference for reconciliation where safe;
- correlation/idempotency identity.

Raw webhook payloads remain owned by verified Event Inbox/Connections retention policy, not duplicated indefinitely into Membership transitions.

---

## Overrides

Membership force allow/deny overrides remain exceptional, high-privilege and audited.

Physical requirements:
- explicit scope + principal + resource identity;
- decision;
- active/revoked/expiry state;
- actor/reason safe metadata;
- no override can bypass outer WordPress/WPE security denial.

Hot lookup must not require scanning all historical overrides.

---

## Teams and invitations

Team and seat runtime remains site-owned by default.

Concurrency invariants:
- one active seat per team/principal according to product semantics;
- capacity rechecked under a concurrency-safe write boundary;
- owner Enrollment eligibility rechecked;
- invitation token stored as hash only;
- invitation acceptance consumes/reconciles idempotently;
- two concurrent last-seat acceptances cannot overbook.

Candidate indexes:
- scope + team + active seat state;
- scope + principal + team state;
- scope + owner Enrollment;
- scope + invitation token hash/status/expiry;
- scope + normalized invitation identity hash where privacy policy allows.

Exact email/PII representation remains privacy evidence.

---

## Authorization correctness gates

A physical profile fails even if it is fast when any of these fail:
- wrong-site Enrollment/Entitlement/override visible or usable;
- expired/grace-ended access remains allowed because a job is late;
- provider outage blocks ordinary local access checks;
- stale cache permits access after committed revoke;
- duplicate provider event creates duplicate effective Enrollment;
- concurrent Plan Group switch leaves mutually exclusive active grants;
- concurrent team seat acceptance overbooks capacity;
- site archive/delete lifecycle leaves unintended live access;
- restored stale backup resurrects access without reconciliation rules.

Security/correctness is a pass/fail gate before performance comparison.

---

## Retention and site lifecycle

Do not cascade-delete all Membership data merely because a site changes status.

Domain categories:
- current authorization state;
- transition/reconciliation history;
- team/invitation state;
- provider reference metadata;
- privacy-export/erase obligations;
- generic Audit links.

Site archive/spam/deleted-state transition should make live access evaluation follow accepted lifecycle policy immediately, without requiring physical purge.

Physical site deletion later uses the Site Lifecycle Coordinator to:
- block/revoke live access;
- preserve required recovery/retention state;
- export/Backup according to policy;
- clean PT-D rows or PT-E tables only through domain-aware cleanup;
- avoid deleting network-owned/shared resources by implication.

---

## Backup/Restore

M1 Site Backup extracts only rows owned by the selected site plus required references/manifests. M2 extracts the selected site's physical tables.

Restore must not simply trust stale materialized Entitlements. It must reconcile:
- Enrollment timestamps/state;
- provider/source identity where applicable;
- principal access generations;
- Product License/site lifecycle context separately;
- current Plan/Rule semantics according to accepted follow-current/pinned rules.

Backup does not create new commercial truth.

---

## P-012 future benchmark matrix — NOT AUTHORIZED

Datasets:
- 10k / 100k / 1M principals where practical;
- 20k / 200k / 1M+ Enrollment history rows;
- 100k / 1M / multi-million Entitlements;
- single and multiple Enrollments per principal;
- high-volume Plan update/rebuild;
- teams with low and high seat counts;
- 100 / 1k / 10k-site network profiles.

Read workloads:
- one principal access snapshot;
- exact entitlement lookup;
- admin member list by Plan/state/date;
- provider/source reconciliation lookup;
- team member list;
- invitation lookup;
- transition history.

Write/concurrency workloads:
- duplicate event/idempotency;
- active→grace→expired;
- immediate revoke;
- concurrent Plan Group switch;
- concurrent last-seat acceptance;
- override add/revoke;
- bulk Plan benefit change/rebuild;
- site archive/delete/restore.

Security fixtures:
- wrong-site IDOR for every runtime identity;
- cache key site collision;
- stale generation after revoke;
- user shared across sites with different access;
- network admin without target-site authority;
- restored stale data;
- provider reference collision across sites/connections.

Measure:
- p50/p95/p99 authorization/read latency;
- queries per access decision;
- write/rebuild throughput;
- lock/deadlock/retry behavior;
- index/table size;
- noisy-neighbor impact;
- Backup extraction/Restore cost;
- 100/1k/10k-site provisioning/migration cost.

Capture query plans/rows examined and correctness results, not latency alone.

---

## Decision rule

M1 is the first benchmark baseline, not the winner by declaration.

M1 may be selected only if it passes all scope/revoke/concurrency/restore invariants and has acceptable workload evidence. M2 remains mandatory comparison. A marginal speed difference does not justify materially worse isolation, migration safety or operational complexity.

## Development gate

No Membership table, migration, fixture, cache, lock primitive, provider event, queue job or benchmark is authorized by this document. ADR-0014 explicit owner consent remains required.