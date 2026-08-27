# WPEssential Membership — Enrollment State Machine

Status: **Phase 0 semantic specification / candidate for acceptance**  
No runtime implementation exists.

The purpose of this document is to prevent billing-provider status names, WordPress roles and Membership access state from becoming one ambiguous field.

## 1. Enrollment definition

An **Enrollment** is one user's membership relationship to one Membership Plan for one lifecycle interval/source.

It records:
- user/subject;
- plan;
- plan group, if any;
- source type/reference;
- lifecycle state;
- start/effective timestamps;
- trial/grace/period timestamps;
- scheduled end/cancel intent;
- source/billing references;
- transition/audit history.

An external billing subscription is a source/reference, not the Enrollment itself.

---

# 2. Canonical states

## `pending`

Enrollment exists but access is not yet active.

Examples:
- waiting for manual approval;
- checkout/payment source not confirmed;
- invitation accepted but activation workflow not complete;
- future-dated enrollment.

Default entitlement behavior: **none**.

## `trialing`

Trial interval is currently effective.

Default entitlement behavior: **grant plan trial entitlements**.

## `active`

Normal effective membership interval.

Default entitlement behavior: **grant normal plan entitlements**.

## `grace`

Normal billing/source condition failed or period ended, but policy intentionally keeps temporary access.

Default entitlement behavior: **grant configured grace entitlements**; candidate default is normal access during bounded grace.

## `paused`

Enrollment is preserved but normal access is suspended.

Default entitlement behavior: **none**, except explicitly modeled retained benefits.

Examples:
- payment recovery policy after grace;
- owner/member pause where provider supports it;
- compliance/manual hold.

## `expired`

Enrollment interval ended normally and no longer grants access.

Terminal for that interval.

## `revoked`

Enrollment was intentionally terminated for administrative/security/policy reasons.

Terminal for that interval.

Examples:
- fraud/security response;
- manual revocation;
- access abuse;
- source dispute policy where explicit revocation is desired.

`revoked` is stronger than ordinary cancellation/expiry and should remain visible in history.

---

# 3. States intentionally NOT canonical

The following should not become Membership lifecycle states by default:

- `past_due`
- `unpaid`
- `payment_failed`
- `incomplete`
- `incomplete_expired`
- `on_hold`
- `cancel_at_period_end`
- `refunded`
- `disputed`

These are billing/source/provider facts or intents.

Adapters translate them into WPEssential enrollment transitions according to Plan/Connection policy.

Example:

Stripe/Woo source reports `past_due` → WPEssential plan may transition `active → grace` for 7 days, then `grace → paused` if unrecovered.

Another plan could transition immediately `active → paused`.

---

# 4. Cancellation is an intent, not necessarily a state

A member can cancel renewal while retaining access until the already-paid period ends.

Therefore model cancellation separately with fields such as:
- cancellation requested timestamp;
- cancellation source/actor;
- effective end timestamp;
- cancellation reason;
- auto-renew/source intent.

During the remaining paid period the Enrollment stays `active` (or `trialing`) and transitions to `expired` when the effective interval ends.

This avoids the common ambiguity where `canceled` sometimes means “no renewal” and sometimes means “no access now.”

---

# 5. Canonical transition graph

Allowed candidate transitions:

### From `pending`
- → `trialing`
- → `active`
- → `expired` — future/approval window ended or source never completed
- → `revoked`

### From `trialing`
- → `active`
- → `grace`
- → `paused`
- → `expired`
- → `revoked`

### From `active`
- → `grace`
- → `paused`
- → `expired`
- → `revoked`

### From `grace`
- → `active` — source recovered
- → `paused`
- → `expired`
- → `revoked`

### From `paused`
- → `active`
- → `expired`
- → `revoked`

### From `expired`
- no mutation back to active by default

### From `revoked`
- no mutation back to active by default

## Reactivation rule

Candidate rule: reactivation after a terminal `expired`/`revoked` interval creates a **new Enrollment** referencing the previous Enrollment where useful.

Why:
- preserves clean historical intervals;
- avoids rewriting terminal audit facts;
- supports changed plan/source/price terms;
- makes analytics and disputes easier to reason about.

An exception could later be accepted for provider semantics, but must not silently mutate terminal history.

---

# 6. Transition guards

Every transition must validate:
- current state matches expected source state;
- actor/source is authorized;
- transition is allowed by graph;
- effective timestamps are valid;
- plan exists/published or legacy snapshot permits continuation;
- group exclusivity/capacity rules;
- source event idempotency;
- no conflicting newer source event exists;
- seat/team constraints where applicable.

Invalid transitions return a structured error and do not partially apply side effects.

---

# 7. Time model

Store normalized timestamps in UTC, render in site/user timezone.

Relevant fields may include:
- `created_at`
- `effective_from`
- `trial_starts_at`
- `trial_ends_at`
- `current_period_starts_at`
- `current_period_ends_at`
- `grace_ends_at`
- `paused_at`
- `scheduled_end_at`
- `ended_at`
- `revoked_at`

Rules:
- use immutable historical timestamps where possible;
- source/provider timestamps and WPEssential received/processed timestamps are distinct;
- late events are evaluated against event time and current state, not blindly applied in arrival order.

---

# 8. Idempotency and event ordering

Every external lifecycle event must carry or derive an idempotency identity.

Persist enough information to reject duplicate/replayed processing safely.

For out-of-order events:
- compare provider/source event timestamps/version/order where provider supports it;
- run reconciliation against source-of-truth billing API when ambiguity exists;
- never allow an old `active` event to resurrect a later `revoked`/expired interval automatically;
- manual/security revocation must have explicit precedence over late ordinary billing events unless an authorized administrator reverses it through a new enrollment/recovery flow.

---

# 9. Side-effect ordering

Candidate transition transaction flow:

1. validate transition/idempotency;
2. write Enrollment state and transition record atomically where DB semantics permit;
3. update/materialize Entitlements or increment authorization version;
4. invalidate access caches;
5. commit state;
6. emit typed domain event;
7. enqueue non-critical asynchronous side effects:
   - email;
   - notification;
   - webhook;
   - analytics;
   - external sync.

Critical authorization state must not depend on email/webhook delivery succeeding.

If job enqueue fails after committed state, state remains authoritative and diagnostics/recovery must surface the side-effect failure.

---

# 10. Transition audit record

Every transition records at least:
- Enrollment ID;
- from state;
- to state;
- effective timestamp;
- processing timestamp;
- actor/source type;
- source/provider event ID if applicable;
- reason code;
- safe human reason/note where appropriate;
- correlation/run ID;
- policy/plan version snapshot reference;
- idempotency key;
- result.

Do not store raw payment-card data, secrets or unnecessary webhook payloads in transition history.

---

# 11. Billing translation examples

These are examples, not final provider contracts.

## Payment succeeds during grace

`grace → active`

Actions:
- refresh period timestamps;
- clear grace end;
- rebuild/invalidate entitlements;
- emit `membership.enrollment.activated` or `recovered` semantic event.

## Renewal fails

Policy A:
`active → grace`

Policy B:
`active → paused`

Adapter does not invent which; plan/source policy determines it.

## Member cancels at period end

State remains `active`.

Set:
- cancellation intent;
- scheduled end.

At period end:
`active → expired`

## Immediate administrative revoke

`trialing|active|grace|paused → revoked`

Required:
- immediate entitlement invalidation;
- strong cache invalidation;
- audit reason;
- optional notification according to policy.

## Refund

A refund does not universally mean immediate revoke.

Provider adapter/policy must decide:
- keep access through paid term;
- expire immediately;
- revoke for fraud/dispute.

No hard-coded universal refund behavior.

---

# 12. Manual enrollments

Manual/admin grants use the same state machine.

Possible creation:
- pending approval;
- active immediately;
- trialing;
- future-effective pending.

Lifetime membership is not a special forever state; it is an active enrollment with no normal expiry (or explicit lifetime duration semantic) until revoked/changed.

---

# 13. Plan edits during active Enrollment

Need distinguish:
- plan definition current version;
- Enrollment's commercial/lifecycle snapshot;
- Entitlement rules that may intentionally update for existing members.

Candidate principle:
- price/billing history never rewrites retroactively;
- access benefits can be configured as `follow current plan` or future version-pinning semantics after separate decision;
- major destructive plan changes require impact preview showing affected active enrollments.

Exact snapshot/version strategy remains open.

---

# 14. Group exclusivity transitions

If Plan Group allows only one active/trial/grace enrollment:

New enrollment cannot simply activate while conflicting enrollment remains eligible.

Upgrade/cross-grade workflow must explicitly decide order:
- validate new source;
- schedule/end old enrollment;
- activate new enrollment at effective timestamp;
- avoid a gap or accidental double grant according to configured transition policy.

Concurrency requires DB-level/transactional protection or equivalent locking strategy after implementation is authorized.

---

# 15. Seat/team relationship

A team owner Enrollment can grant derived seat/member access.

If owner Enrollment becomes:
- `active/trialing/grace`: seats eligible according to policy;
- `paused`: seat entitlements pause by default;
- `expired/revoked`: seat entitlements terminate/expire accordingly.

Do not mutate every member's WordPress role as the sole representation of seat access.

---

# 16. State-to-access truth table

| State | Enrollment exists | Entitlements eligible by default | Renewable/source action possible | Terminal |
|---|---:|---:|---:|---:|
| pending | Yes | No | Yes | No |
| trialing | Yes | Yes | Yes | No |
| active | Yes | Yes | Yes | No |
| grace | Yes | Yes* | Yes | No |
| paused | Yes | No* | Yes | No |
| expired | Yes/history | No | new enrollment | Yes |
| revoked | Yes/history | No | authorized new enrollment only | Yes |

`*` Plan policy can explicitly define a limited entitlement subset; defaults remain as stated.

---

# 17. Required future tests

After explicit development consent:
- every allowed transition;
- every forbidden transition;
- duplicate provider event;
- out-of-order old activation after revoke;
- payment recovery during grace;
- grace timeout race;
- scheduled cancellation expiry;
- concurrent upgrade/group exclusivity;
- manual revoke while billing webhook arrives;
- cache invalidation on every access-affecting transition;
- job/email failure after state commit;
- terminal reactivation creates new enrollment;
- multisite/site-scope isolation if supported.

---

# 18. Acceptance status

This is a **candidate state model**.

Before Accepted:
1. compare with the planned billing adapters;
2. validate trial/grace/pause semantics against product UX;
3. finalize plan snapshot/version rules;
4. finalize group upgrade/cross-grade transaction semantics;
5. finalize reconciliation precedence;
6. record final model in ADR-0013 or a dedicated ADR.

No executable state machine or database table has been created.
