# ADR-0016 — Membership Enrollment Lifecycle Semantics

Status: **Accepted product semantics**  
Date: 2026-08-27

## Context

Billing providers expose different status names and meanings. A membership system that stores those provider statuses directly as its access state becomes inconsistent across WooCommerce, SureCart, MemberPress migrations, manual grants and future adapters.

WPEssential therefore needs one canonical Enrollment lifecycle independent of billing provider terminology.

This ADR accepts lifecycle semantics only. Runtime tables/indexes, concurrency implementation and provider adapters remain unimplemented and blocked by ADR-0014.

## Decision

## 1. Canonical Enrollment states

WPEssential Membership uses these canonical states:
- `pending`
- `trialing`
- `active`
- `grace`
- `paused`
- `expired`
- `revoked`

### `pending`
Enrollment exists but access is not yet effective.

Default entitlements: none.

### `trialing`
Trial interval is effective.

Default entitlements: plan entitlements, unless the Plan explicitly defines a trial subset.

### `active`
Normal effective membership.

Default entitlements: normal plan entitlements.

### `grace`
Temporary bounded access retained after a billing/source recovery condition or configured period-end rule.

Default entitlements: normal access unless Plan defines a reduced grace subset.

### `paused`
Enrollment remains recorded but ordinary access is suspended.

Default entitlements: none unless explicit retained benefits are modeled.

### `expired`
Lifecycle interval ended normally.

Terminal for that Enrollment interval.

### `revoked`
Administrative/security/policy termination.

Terminal for that Enrollment interval and semantically stronger than ordinary expiry.

## 2. Provider/payment facts are not canonical Enrollment states

Do not add provider-specific lifecycle states such as:
- `past_due`
- `unpaid`
- `payment_failed`
- `incomplete`
- `on_hold`
- `refunded`
- `disputed`

These remain billing/source facts.

Adapters and Plan policy translate verified provider facts into canonical state transitions.

Example:
- renewal failure may map `active → grace` for one Plan;
- another Plan may map `active → paused` immediately.

The adapter does not invent business policy.

## 3. Cancellation is an intent, not a lifecycle state

`cancel_at_period_end` / “renewal cancelled” does not automatically remove current access.

Store separate intent/effective fields such as:
- cancellation requested at;
- requested by/source;
- scheduled end/effective timestamp;
- cancellation reason;
- source auto-renew state.

An already-paid member remains `active`/`trialing` until the effective end, then transitions to `expired`.

This semantics is also used when migrating products whose “cancelled” state still grants access through a paid-through date.

## 4. Allowed transition graph

### From `pending`
- → `trialing`
- → `active`
- → `expired`
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
- → `active`
- → `paused`
- → `expired`
- → `revoked`

### From `paused`
- → `active`
- → `expired`
- → `revoked`

### From terminal states
`expired` and `revoked` do not mutate back to active by default.

## 5. Reactivation after terminal state creates a new Enrollment

Default rule:
- later reactivation after `expired` or `revoked` creates a new Enrollment interval;
- optionally records predecessor/reason linkage.

Why:
- preserves historical audit facts;
- avoids rewriting disputes/commercial history;
- allows new plan/source/terms;
- makes analytics and migrations explainable.

A future provider-specific exception requires another explicit decision; adapters may not silently revive terminal history.

## 6. Time model

Persist authoritative timestamps in UTC and render using site/user timezone.

Conceptual timestamps include:
- created/processed time;
- effective start;
- trial start/end;
- current billing/source period start/end;
- grace end;
- pause time;
- scheduled end;
- ended/revoked time;
- provider event time;
- WPE received/processed time.

Provider event time and local processing time remain distinct.

## 7. Transition guards

Every state transition validates:
- expected current state;
- authorized actor/source;
- graph permits transition;
- timestamps are coherent;
- Plan/legacy snapshot exists;
- source event identity/idempotency;
- newer/conflicting source facts;
- Plan Group exclusivity/capacity where relevant;
- team/seat constraints where relevant.

Invalid transitions fail atomically with structured error.

## 8. Idempotency and out-of-order provider events

External lifecycle inputs must have a stable idempotency identity.

Rules:
- duplicate event does not duplicate transition/side effects;
- use provider sequence/version/timestamp where trustworthy;
- ambiguous ordering triggers source reconciliation rather than blind application;
- an old activation/payment event cannot resurrect a later terminal `revoked`/`expired` Enrollment;
- manual/security revocation outranks late ordinary billing events;
- changing a terminal revocation requires an authorized new Enrollment/recovery action, not ordinary webhook replay.

## 9. State commit precedes non-critical side effects

Conceptual ordering:
1. validate transition/idempotency;
2. commit canonical Enrollment/transition state atomically where possible;
3. update/materialize entitlement authorization version;
4. invalidate access cache;
5. commit;
6. emit typed domain event;
7. enqueue non-critical email/notification/webhook/analytics/sync work.

Failure to send email/webhook must not roll back already-valid authorization state.

## 10. Access eligibility defaults

| State | Normal entitlement eligibility |
|---|---|
| `pending` | No |
| `trialing` | Yes |
| `active` | Yes |
| `grace` | Yes by default, Plan configurable |
| `paused` | No by default |
| `expired` | No |
| `revoked` | No |

These semantics compose with ADR-0015 Access Policy.

## 11. Manual/free Enrollment uses the same lifecycle

Provider-free memberships do not get a second state machine.

Manual/admin enrollment may start:
- `pending`
- `trialing`
- `active`
- future-effective `pending`

Lifetime membership is an `active` Enrollment without normal expiry (or explicit lifetime duration semantic), not a special permanent state.

## 12. Refund/dispute behavior is policy, not universal lifecycle logic

A refund may mean:
- keep access through current term;
- expire at effective time;
- revoke for fraud/security dispute.

WPEssential will not hard-code one interpretation across all billing providers/plans.

## 13. Plan Group upgrade/cross-grade

If a group prohibits simultaneous eligible Enrollment:
- activating a new Plan cannot silently leave an incompatible old Enrollment eligible;
- effective transition order must be explicit;
- prevent unintended access gap/double grant;
- concurrency must be protected in runtime implementation.

Exact proration/billing math belongs to provider where possible. WPE controls access-effective timestamps.

## 14. Teams/seats derive from owner Enrollment

By default:
- owner `trialing`/`active`/eligible `grace` → seats eligible;
- owner `paused` → seat access pauses;
- owner `expired`/`revoked` → seat entitlements end.

Do not use WordPress role mutation as the sole representation of team membership/access.

## 15. Audit invariants

Every transition records at least:
- Enrollment stable ID;
- from/to state;
- effective and processing timestamps;
- actor/source type;
- provider/source event reference where applicable;
- reason code;
- safe note where appropriate;
- correlation/run ID;
- Plan/policy version snapshot reference;
- idempotency key;
- result.

Never persist card data/secrets/unnecessary raw provider payload in transition history.

## 16. Consequences

Positive:
- one lifecycle across manual grants and multiple billing adapters;
- cancellation no longer ambiguously means access loss;
- migration from systems with paid-through cancellation becomes faithful;
- historical intervals remain immutable/explainable;
- webhook ordering/reconciliation has a deterministic target.

Costs:
- adapters need explicit translation tables;
- additional source-fact fields/tables are needed;
- upgrade/group concurrency needs careful DB design;
- migration adapters must distinguish access end from renewal cancellation.

## 17. Remaining open technical/product decisions

This ADR does not finalize:
- physical Enrollment table/indexes;
- Plan benefit/version pinning vs follow-current behavior;
- exact grace durations;
- exact Plan Group upgrade transaction/locking;
- seat concurrency implementation;
- provider-specific reconciliation contracts;
- entitlement cache implementation.

These remain separately planned/tested.

## 18. Verification required after development consent

Tests must cover:
- every allowed/forbidden transition;
- duplicate and out-of-order source events;
- payment recovery during grace;
- grace timeout race;
- scheduled cancellation end;
- manual revoke vs late billing webhook;
- terminal reactivation creates new Enrollment;
- cache invalidation for all access-affecting transitions;
- group exclusivity concurrency;
- team seat cascade;
- post-commit side-effect failure;
- migration semantics for paid-through cancellation.
