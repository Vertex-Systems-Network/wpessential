# ADR-0015 — Membership Access Precedence & Explainability

Status: **Accepted product semantics**  
Date: 2026-08-27

## Context

Membership access can be affected by WordPress authorization, WPE resource policies, multiple Membership Plans, exact-resource exceptions, partial-content/download rules, manual overrides, team seats, trial/grace states and administrator support access.

Without a deterministic precedence contract, two modules can reach different answers for the same user/resource and authorization bugs become difficult to diagnose.

This ADR accepts semantic behavior only. Runtime schema, cache/index design and executable security tests remain blocked by ADR-0014 and related technical decisions.

## Decision

### 1. Membership never overrides an outer security denial

Evaluation starts with non-membership security/authorization:
- authenticated context where required;
- WordPress/site/network boundary;
- object/capability policy;
- Protector/security hard blocks;
- endpoint/session/CSRF requirements for state-changing operations.

If this layer denies, Membership cannot turn the operation into allowed.

### 2. Membership is opt-in protection, not implicit global deny

If no Membership rule applies to a resource/action, Membership imposes no restriction. Other WordPress/WPE policies may still restrict the request.

A site-wide Membership default-deny can exist only as an explicit configured rule.

### 3. Manual overrides are exceptional Membership-layer controls

Supported semantic overrides:
- `force_deny`
- `force_allow`

Rules:
- require dedicated high-risk capability;
- audited with actor/reason and optional expiry;
- `force_allow` bypasses ordinary Membership requirements only, not outer security;
- at identical subject/resource/action scope, `force_deny` wins.

### 4. Specificity is two-dimensional

Do not use one ambiguous flat list only.

#### Resource specificity, high to low
1. exact resource/object;
2. relation-derived specific resource set;
3. taxonomy/term/collection;
4. entity/post type;
5. route/listing/query surface;
6. module/domain scope;
7. site-wide Membership scope.

#### Subresource/action specificity
Within the same resource scope:
1. exact action/download/field/partial region/component;
2. whole resource/default action set.

Therefore an exact protected-download rule for `post:123` is more specific for that download action than the whole-page rule for `post:123`.

### 5. More-specific applicable policy beats inherited lower-specificity policy

Examples:
- all `course` posts require Pro, exact `course:welcome` is Public → welcome is public;
- a course page is allowed to Gold, but exact `download:certificate` requires Certification entitlement → download uses the more-specific action rule.

### 6. Same-specificity deny wins

When applicable rules remain at the same effective specificity and conflict, explicit deny/exclusion wins over allow.

This is the safer and more explainable default.

### 7. Rule requirements use explicit operators

Supported semantic operators:
- `ANY`
- `ALL`
- `NONE`
- bounded nested condition groups through the shared Condition Engine.

Multiple rules must not be accidentally unioned as OR merely because they exist at the same scope. The owning rule group defines its boolean semantics.

### 8. Multiple memberships union valid entitlements

A user may hold multiple valid enrollments where Plan Group policy permits it.

Their valid entitlement sets are unioned. They do not overwrite one another.

An explicit applicable exclusion/deny rule can still restrict access.

Plan Group exclusivity is an Enrollment constraint, not hidden access-evaluation behavior.

### 9. Only normalized local Membership state authorizes

Entitlements can derive from:
- valid active/trial/grace enrollments according to plan policy;
- manual complimentary grants;
- team/seat grants;
- approved access promotions.

Raw billing-provider webhook/payment status never directly authorizes a request. Provider facts must first pass adapter validation/reconciliation and change canonical Enrollment/Entitlement state.

### 10. Enrollment eligibility defaults

- `pending` → no normal entitlements;
- `trialing` → eligible, full plan entitlements by default unless explicit trial subset;
- `active` → eligible;
- `grace` → eligible by default for configured bounded grace, plan may define subset;
- `paused` → no normal entitlements by default;
- `expired` → none;
- `revoked` → none.

### 11. Administrator/support access uses a capability, not a role name

Membership bypass is represented by dedicated capability such as `wpe_membership_bypass_access`.

It:
- is visible in diagnostics;
- is policy/audit controlled;
- can be disabled in simulation/testing contexts;
- cannot override outer security denial.

Do not hard-code `administrator` as always allowed.

### 12. Deny returns a structured decision

Membership evaluation returns a structured internal result, not immediate redirect/HTML.

It includes, where safe:
- allow/deny;
- winning policy/rule ID;
- matched resource/action scope;
- required/missing entitlement keys;
- Enrollment eligibility reason;
- bypass/override reason;
- policy/cache version metadata;
- public unauthorized behavior key.

Frontend/admin/API layers render that decision appropriately without leaking sensitive rule internals to unauthorized users.

### 13. Explainability is mandatory

Admin diagnostics must answer:

**Why can/can't subject X perform/access Y?**

Trace includes:
- outer security result;
- override/bypass;
- applicable rules ordered by effective specificity;
- winning rule/group;
- requirement operators;
- entitlements found/missing;
- final result;
- cache/version context where safe.

Explainability is part of the product contract, not optional debug tooling.

### 14. Authorization cache is never authority

Caching may optimize evaluation but cannot weaken semantics.

Revocation, force-deny and access-rule changes require strong/immediate invalidation behavior. Serving access from stale cache after a revoke/hard deny is a security defect.

Exact cache model remains M-003 and requires executable evidence later.

## Consequences

Positive:
- deterministic behavior across pages, downloads, dashboards, forms, REST and builder components;
- multiple memberships compose cleanly;
- exact exceptions remain possible;
- deny behavior is security-biased;
- support can explain decisions.

Costs:
- Policy Engine needs explicit specificity metadata;
- partial/action rules cannot be reduced to only post IDs;
- cache keys/invalidation become version-aware;
- migration adapters must translate source restriction semantics explicitly.

## Rejected alternatives

### “Any allow always wins”
Rejected because a broad grant could neutralize explicit exclusions/security-sensitive resource rules.

### “Administrator role bypasses Membership”
Rejected because role names are not a stable authorization contract.

### “Billing webhook says paid, therefore allow immediately”
Rejected because provider events can be duplicate, out-of-order, forged/unverified or semantically incomplete.

### “Most recent rule wins”
Rejected because edit time is not a defensible policy precedence model.

## Remaining technical blockers

This ADR does **not** accept:
- entitlement physical schema/indexes;
- cache implementation;
- object-cache strategy;
- exact runtime performance thresholds;
- protected-file transport implementation.

Those require owner-authorized implementation/testing later.

## Verification required after development consent

Automated tests must permanently cover:
- outer deny cannot be bypassed;
- exact/subresource specificity;
- same-specificity deny wins;
- public exact exception over broad protection;
- multiple Enrollment union;
- trial/grace/paused/expired/revoked eligibility;
- force override capability/audit;
- administrator-role name provides no magic access;
- stale cache revoke regression;
- page/download/form/REST policy parity;
- safe explainability output.
