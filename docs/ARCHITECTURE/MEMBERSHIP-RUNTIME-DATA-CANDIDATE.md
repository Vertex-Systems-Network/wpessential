# WPEssential Membership — Runtime Data Candidate Model

Status: **Phase 0 paper design / Proposed / no tables or migrations created**  
Related: ADR-0013, Membership specs, M-001/M-003/M-008

This document separates **configuration definitions** from **transactional membership runtime data**.

## 1. Storage boundary

### Definition Repository owns configuration
Examples:
- Membership Plan;
- Plan Group;
- access rule/policy definition;
- benefit definition;
- drip rule;
- upgrade/downgrade policy;
- member page/template configuration;
- billing source mapping definition where it is reusable configuration.

### Membership runtime owns transactional state
Examples:
- user Enrollment;
- current materialized Entitlement grants;
- Enrollment transition history;
- user/resource force allow/deny overrides;
- team/seat membership;
- invitation lifecycle;
- selected reconciliation/runtime state.

Do not store 100k user enrollments as Definition Repository JSON definitions.

---

# 2. Candidate runtime tables

Exact names/types/indexes require benchmark after explicit owner consent.

## A. `wpe_membership_enrollments`

Purpose: current lifecycle row for one user/subject + Plan interval.

Candidate logical fields:

| Field | Purpose |
|---|---|
| `id` | local numeric primary key |
| `uuid` | stable runtime identity for APIs/audit |
| `user_id` | WordPress user subject for v1 |
| `plan_uuid` | stable Plan definition reference |
| `plan_group_uuid` | optional group snapshot/reference |
| `state` | pending/trialing/active/grace/paused/expired/revoked |
| `source_type` | manual/free/woocommerce/surecart/etc. adapter key |
| `source_connection_uuid` | reusable connection reference where applicable |
| `source_customer_ref` | provider customer identifier, non-secret |
| `source_subscription_ref` | provider subscription/reference, non-secret |
| `source_order_ref` | provider order/purchase reference where applicable |
| `effective_from` | UTC |
| `trial_ends_at` | nullable UTC |
| `current_period_starts_at` | nullable UTC |
| `current_period_ends_at` | nullable UTC |
| `grace_ends_at` | nullable UTC |
| `scheduled_end_at` | cancellation/future end intent |
| `ended_at` | terminal timestamp |
| `revoked_at` | terminal revoke timestamp |
| `cancellation_requested_at` | nullable |
| `cancellation_reason_code` | safe normalized code |
| `previous_enrollment_uuid` | optional terminal interval lineage |
| `state_version` | monotonic optimistic-concurrency/version counter |
| `entitlement_version` | generation/version used for cache invalidation |
| `created_at` | UTC |
| `updated_at` | UTC |

### What does not belong here
- card details;
- raw provider webhook payload;
- WordPress role as membership source of truth;
- complete Plan payload;
- arbitrary serialized provider objects.

---

## B. `wpe_membership_entitlements`

Purpose: materialized current grants used for fast access evaluation and explainability.

Candidate fields:

| Field | Purpose |
|---|---|
| `id` | local primary key |
| `uuid` | grant identity |
| `user_id` | subject |
| `entitlement_key` | normalized grant key, e.g. `reports.advanced` |
| `scope_type` | global/module/resource/type/etc. if entitlement itself is scoped |
| `scope_ref` | stable scoped reference when needed |
| `source_type` | enrollment/manual/team/promo/etc. |
| `source_uuid` | Enrollment/team/grant source identity |
| `plan_uuid` | optional Plan trace |
| `valid_from` | UTC |
| `valid_until` | nullable UTC |
| `state` | active/suspended/revoked/expired or simplified current-state marker |
| `generation` | source entitlement generation/version |
| `created_at` | UTC |
| `updated_at` | UTC |

### Candidate strategy

Do not recompute all Plan benefits from Definition JSON on every page request.

When access-affecting state changes:
1. transactionally update Enrollment/runtime source;
2. rebuild/diff affected materialized entitlements;
3. increment entitlement/access version;
4. invalidate affected cache keys;
5. emit event after commit.

Materialization is derived state; Enrollment + Plan/Rule history remains authoritative for rebuild/reconciliation.

---

## C. `wpe_membership_transitions`

Purpose: immutable lifecycle history independent of generic Audit Log.

Candidate fields:
- transition ID/UUID;
- Enrollment UUID/ID;
- from state;
- to state;
- effective timestamp;
- processed timestamp;
- actor/source type and safe reference;
- external event/reference ID if applicable;
- reason code;
- correlation/idempotency key;
- plan/source version metadata;
- safe result metadata.

Why keep dedicated transition history if Audit exists:
- state reconstruction/analytics/reconciliation requires structured domain history;
- generic Audit can link to transition rather than store all lifecycle semantics itself.

Do not duplicate full raw webhook bodies.

---

## D. `wpe_membership_overrides`

Purpose: exceptional per-subject/resource Membership `force_allow` / `force_deny` decisions.

Candidate fields:
- UUID;
- user/subject ID;
- resource scope type/ref;
- decision (`force_allow`, `force_deny`);
- optional entitlement/rule target;
- reason code/note;
- effective from;
- expires at;
- created by;
- created/updated timestamp;
- status/revoked timestamp.

Rules:
- dedicated high privilege;
- audit every change;
- expiry strongly encouraged;
- never bypass outer WordPress/security Layer 0.

---

## E. `wpe_membership_teams`

Purpose: organization/team seat container backed by an owner Enrollment or Plan.

Candidate fields:
- UUID;
- owner user ID;
- owner Enrollment UUID;
- Plan UUID;
- name;
- status;
- seat limit snapshot/current policy ref;
- created/updated timestamps.

Exact commercial ownership semantics remain open.

---

## F. `wpe_membership_team_members`

Purpose: active/reserved team seat assignment.

Candidate fields:
- team ID/UUID;
- user ID;
- membership role inside team (`owner`, `manager`, `member`) separate from WordPress role;
- seat status;
- joined/effective timestamps;
- removed timestamp;
- invitation/source trace;
- generation/version.

Candidate uniqueness:
- one active seat per user/team;
- transactions/locks enforce seat capacity because MySQL partial unique constraints are limited.

---

## G. `wpe_membership_invitations`

Purpose: seat/team/member invitation lifecycle.

Candidate fields:
- UUID;
- team/Plan context;
- normalized email hash/index + encrypted/PII-safe storage decision pending privacy review;
- invited by;
- role/seat type;
- token **hash** only, never raw redeem token after issue;
- created/expires/accepted/revoked timestamps;
- accepted user ID;
- attempt/rate metadata where needed.

Invitation acceptance requires concurrency-safe capacity recheck; reservation semantics must be explicit.

---

# 3. External billing events

Do not automatically create a Membership-only raw webhook table if Webhooks & Connections Manager owns a shared verified event inbox/replay/idempotency service.

Preferred boundary:
- Connections/Webhooks verifies signature, replay window, connection and stores safe normalized event receipt metadata;
- Membership adapter consumes normalized event ID/data;
- Membership transition stores only the provider/source reference needed for audit/reconciliation.

If shared event inbox is not available in the first Membership billing release, a temporary module-owned inbox would require an ADR/migration path rather than becoming accidental permanent architecture.

---

# 4. Plan snapshots vs current Plan

Need separate concepts:

## Commercial/source snapshot
Enrollment preserves facts needed to explain what was purchased/granted:
- source product/reference;
- original Plan UUID;
- relevant term timestamps;
- optional Plan revision UUID/version.

## Current access definition
Administrators may intentionally update benefits for all current members.

Candidate future Plan option:
- `benefits_follow_current_plan` — default candidate;
- future `pin_access_to_plan_revision` only if real use case demands it.

Do not retroactively rewrite billing prices/order history.

---

# 5. Entitlement key model

Entitlement keys should be stable developer-readable identifiers, e.g.:
- `content.courses.pro`
- `downloads.assets.gold`
- `dashboard.partner`
- `discount.wholesale`

Rules:
- lowercase namespace grammar;
- changing/removing key requires usage/dependency impact view;
- display label separate from key;
- aliases/migration map may be needed for rename;
- Access Rules reference entitlement stable key/UUID as appropriate.

Avoid using Plan IDs directly as the only entitlement model because benefits can be shared across Plans.

---

# 6. Hot access-check read path

Candidate logical path:

1. outer WordPress/WPEssential resource authorization;
2. resolve resource Membership rule set from compiled Definitions/cache;
3. obtain request-local principal entitlement snapshot/version;
4. check exceptional override;
5. evaluate rule requirements against active materialized Entitlements;
6. return structured decision/explanation key;
7. memoize request-local decision;
8. optional persistent cache only with versioned invalidation.

No provider API call during ordinary access request.

No Plan JSON full scan for every content check.

---

# 7. Cache version candidate

Each user/subject has an access/entitlement generation that changes on any access-affecting mutation.

Could be:
- highest/combined Enrollment entitlement version;
- dedicated per-user access-version record/cache key;
- event-driven version token.

Cache key concept:

`principal + access_generation + policy_generation + resource/scope`

This allows old cached values to become unreachable after generation increment.

However revocation safety requires transaction/invalidation semantics; a generation model must be benchmarked and tested, not assumed correct.

---

# 8. Enrollment indexes needed

Exact DDL pending benchmark.

Hot queries:
- active eligibility by `user_id` + state/time;
- Plan members by `plan_uuid` + state;
- source subscription lookup by provider/source reference;
- scheduled grace/expiry jobs by timestamp/state;
- team owner Enrollment lookup;
- reconciliation by connection/source.

Potential indexes must account for high write/update volume on state transitions without excessive index cost.

---

# 9. Entitlement indexes needed

Hot queries:
- active entitlement keys for one user;
- one user + one entitlement key/scope;
- source Enrollment cleanup/rebuild;
- validity expiry scans;
- diagnostics source trace.

Access hot path should avoid N queries per required entitlement.

Candidate: fetch/set all active subject entitlements once per request or batch exact required keys, depending benchmark.

---

# 10. Time-based expiry

Do not depend solely on a background job firing at the exact expiry second.

Access checks must respect `valid_until` / Enrollment timestamps even if expiry cleanup job is late.

Background jobs:
- materialize terminal transition;
- cleanup/rebuild entitlements;
- notifications;
- analytics.

But authorization cannot remain allowed just because WP-Cron did not run on time.

This is critical on low-traffic sites.

---

# 11. State-transition transaction candidate

Access-affecting mutation should aim for:

1. lock/read current Enrollment version;
2. validate transition/idempotency;
3. write new Enrollment state/version/timestamps;
4. write transition record;
5. update/remove/materialize Entitlements;
6. increment access generation;
7. commit;
8. invalidate object/application caches;
9. emit domain event/job side effects.

If DB/cache invalidation failure occurs after commit, generation-based keys should reduce stale authorization risk; exact mechanism needs executable concurrency testing.

---

# 12. Group exclusivity / upgrade concurrency

MySQL cannot conveniently express all "only one eligible Enrollment in this Plan Group" rules as a simple portable partial unique index.

Candidate solution:
- transaction + advisory/application lock keyed by user + Plan Group;
- recheck eligible Enrollment rows inside lock;
- perform old/new effective transition atomically where possible.

Exact lock primitive depends on accepted DB/Job architecture and benchmark.

Do not rely on UI validation only.

---

# 13. Team seat concurrency

Invitation acceptance flow candidate:

1. verify token hash + expiry + invite status;
2. begin transaction/lock team;
3. re-read seat limit and occupied/reserved count;
4. verify owner Enrollment still eligible;
5. ensure user has no duplicate active seat;
6. consume invitation;
7. insert/activate team membership;
8. materialize derived Entitlements/update generation;
9. commit;
10. emit notification/event.

If full, return deterministic capacity result; never overbook because two requests saw the same stale count.

---

# 14. Privacy classification

Potential personal data:
- Enrollment/user association;
- team membership;
- invitation email;
- billing customer/order reference;
- transition actor/source;
- download/access logs if enabled.

Need future privacy ADR/spec for:
- WordPress exporter/eraser integration;
- retention;
- anonymization vs legal/accounting/security history;
- invitation expiry cleanup;
- external provider reference handling.

Do not add high-volume access logs by default merely for analytics.

---

# 15. Import/migration

Competitor migration must map into:
- Plan definitions;
- current/history Enrollments;
- entitlement/materialization rebuild;
- source/provider references where safe;
- role sync only if explicitly configured.

Never treat imported role as proof of a paid/valid Membership unless migration mapping explicitly declares that semantic.

Dry-run reports:
- users matched/unmatched;
- duplicate enrollments;
- plan mappings;
- unsupported states;
- billing source limitations;
- expected access changes.

---

# 16. Failure behavior

## Plan missing
Enrollment preserved; mark unhealthy/orphaned, deny/last-known policy according to accepted security rule—not silently reassign another Plan.

## Provider connection missing
Existing local Enrollment/Entitlements do not disappear. Reconciliation unhealthy; warn admin.

## Job runner late
Timestamp-based access still expires correctly.

## Entitlement materialization inconsistency
Diagnostics compare/rebuild from Enrollment + Plan source of truth; fail safe for uncertain protected access.

## Cache service unavailable
Correct uncached DB/policy path remains available; cache cannot be required for authorization correctness.

---

# 17. Benchmark plan — NOT AUTHORIZED

Future synthetic test candidates:
- 100k users;
- 200k/500k Enrollment history rows;
- 1M materialized Entitlements;
- 10k access rules/resources;
- multiple memberships/user;
- plan update affecting 50k users;
- mass expiry/revoke;
- team seat concurrency.

Measure:
- single user access-check query count/latency;
- bulk member admin list;
- entitlement rebuild throughput;
- revoke-to-deny behavior;
- cache invalidation;
- storage/index size;
- group/seat concurrency.

No table/fixture/benchmark may be executed before explicit owner consent under ADR-0014.

---

# 18. Current paper recommendation

Configuration:
- Plans/Groups/Rules/Benefits in Definition Repository.

Runtime:
- Enrollment table;
- materialized Entitlement table;
- structured Enrollment transition history;
- exceptional access override table;
- teams/team members/invitations tables only when team feature enters accepted scope;
- shared Webhook/Connections inbox for provider events where available.

Correctness:
- timestamps enforce expiry even if jobs are late;
- no provider call on access hot path;
- no WordPress role as Membership source of truth;
- generation/version-based cache invalidation candidate;
- transaction/lock protections for group/seat races;
- no raw secrets/card data/provider objects.

This remains Proposed until schema/index/cache benchmark evidence is authorized and completed.
