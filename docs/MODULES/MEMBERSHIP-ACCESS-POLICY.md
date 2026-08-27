# WPEssential Membership — Access Policy Semantics

Status: **Phase 0 semantic specification / candidate for acceptance**  
No runtime implementation exists.

This document removes ambiguity around how multiple memberships, grants, denies, manual overrides and inherited rules combine.

## 1. Separation of concerns

Membership access evaluation does not replace WordPress authorization.

A request must pass two different questions:

1. **Can this principal perform the operation at all?** — WordPress capability/resource policy/security layer.
2. **Does this principal have the required membership entitlement for this protected resource/benefit?** — Membership Access Policy.

Membership can further restrict or grant access inside a permitted product surface, but it must never turn a WordPress-forbidden operation into an authorized one.

Example: a membership entitlement to edit premium project records does not bypass a missing WordPress/WPEssential edit capability.

---

# 2. Resource protection modes

Every resource/rule resolves to one of these conceptual modes:

- **Public** — membership engine imposes no restriction.
- **Protected** — requires one or more entitlement conditions.
- **Explicitly denied** — membership policy denies even if another lower-precedence membership rule would grant.
- **Partial** — resource shell may be public while selected regions/actions/downloads are protected.

A resource with no applicable Membership rule remains **Public** unless another module/policy protects it.

Membership is not a global default-deny security firewall unless the site owner explicitly creates such a policy scope.

---

# 3. Rule scopes and specificity

Candidate specificity order from most to least specific:

1. exact resource/object ID;
2. exact child/partial region/action/download;
3. relation-derived resource rule;
4. taxonomy/term or collection rule;
5. post type/entity type;
6. route/path pattern or listing/query surface;
7. module/domain scope;
8. site-wide Membership default.

A more specific applicable rule set is evaluated before less-specific inherited rules.

Inheritance must be visible in UI: administrators should be able to inspect **Why is this protected?** and see the rule chain.

---

# 4. Policy layers

Evaluation order is deliberately separated into layers.

## Layer 0 — non-membership security boundary

Examples:
- WordPress object capability failure;
- private/post-password/core visibility where applicable;
- Protector hard block;
- disabled endpoint;
- invalid authentication/session;
- CSRF/nonce failure for state-changing operations.

**Result:** deny. Membership cannot override this layer.

## Layer 1 — emergency/explicit Membership override

Manual overrides are exceptional, audited policy objects, not ordinary plan membership.

Types:
- `force_deny`
- `force_allow`

Candidate behavior:
- `force_deny` wins over `force_allow` at the same exact subject/resource scope;
- `force_allow` can bypass ordinary Membership plan/rule requirements but **cannot** bypass Layer 0 security;
- overrides require a dedicated high-privilege capability and reason/audit metadata;
- optional expiry is strongly encouraged;
- bulk/global force-allow requires heightened confirmation.

## Layer 2 — applicable resource rules

Rules define requirements, not direct payment logic.

Examples:
- requires Plan A OR Plan B;
- requires entitlement `courses.advanced`;
- requires active team seat;
- requires Plan A AND certification entitlement;
- deny Plan C members from a special upgrade page;
- allow public teaser but protect download action.

Rules are evaluated by specificity.

## Layer 3 — entitlement resolution

The engine resolves the user's current valid entitlements from:
- active/trial/grace enrollments according to plan policy;
- manual complimentary grants;
- team/seat grants;
- approved promotional/access grants;
- external billing sources only after they have been normalized into valid Enrollment/Entitlement state.

Raw payment-provider events never directly authorize a request.

## Layer 4 — default

If no Membership rule applies: **membership-allow / public**.

If a rule applies but its requirement is not satisfied: **membership-deny** with a safe unauthorized behavior response.

---

# 5. Allow/deny conflict semantics

## Same specificity

Candidate default: **deny wins**.

Reason:
- safer and easier to reason about;
- prevents an accidental broad allow from neutralizing an explicit exclusion;
- can be explained in diagnostics.

## Different specificity

The most specific applicable rule set wins over inherited lower-specificity rules.

Example:
- Post Type `courses` requires Pro.
- Exact Course `free-introduction` is Public.
- The exact-resource rule wins, making that course public.

Another example:
- Post Type `downloads` allows Gold.
- Exact Download `internal-contract.pdf` explicitly denies Gold and requires Staff entitlement.
- Exact-resource rule wins.

## Multiple memberships

Memberships do not overwrite one another.

Entitlements are unioned unless an applicable resource rule expressly creates an exclusion/deny condition.

Example:
- Plan A grants `reports.basic`.
- Plan B grants `reports.advanced`.
- User with both has both valid grants.

A Plan Group may prohibit simultaneous enrollments, but that is an enrollment rule, not access-evaluation magic.

---

# 6. Requirement operators

A rule can require:

- **ANY** — at least one selected plan/entitlement condition;
- **ALL** — every selected condition;
- **NONE** — exclusion condition;
- nested condition groups through the shared Condition Engine after complexity limits are defined.

Avoid exposing an unlimited boolean-programming language in v1. Rules should remain explainable.

---

# 7. Enrollment states and access eligibility

Candidate eligibility defaults:

| Enrollment state | Grants normal entitlements? | Notes |
|---|---:|---|
| `pending` | No | awaiting approval/source confirmation |
| `trialing` | Yes | unless plan explicitly limits trial benefits |
| `active` | Yes | normal state |
| `grace` | Yes by default | configurable per plan; grace duration bounded |
| `paused` | No by default | optional limited benefits may be modeled explicitly, not assumed |
| `expired` | No | terminal interval ended |
| `revoked` | No | administrative/security termination |

Payment state such as `past_due` is not automatically a Membership state. The billing adapter translates it into the configured Enrollment behavior (`grace`, `paused`, etc.).

---

# 8. Trial entitlement policy

Per plan:
- trial grants full normal entitlements; or
- trial grants a selected subset.

Default candidate: full plan entitlements during trial unless the administrator intentionally defines trial-only exclusions.

Do not create hidden differences between trial and paid access.

---

# 9. Grace-period policy

Plan defines:
- grace enabled;
- duration;
- entitlement behavior during grace;
- notification/workflow hooks.

Default candidate:
- access remains active during a bounded grace period;
- no grace for manually revoked/security-revoked membership;
- grace expiration deterministically transitions to paused/expired according to enrollment source policy.

---

# 10. Admin/management bypass

Do not hard-code `administrator = always membership allowed`.

Candidate model:
- dedicated capability such as `wpe_membership_bypass_access`;
- administrators receive it by default through WPEssential role setup, subject to anti-lockout policy;
- bypass can be disabled in testing/simulation contexts;
- bypass is visible in access diagnostics;
- bypass cannot override non-membership Layer 0 security failures.

This lets administrators inspect restricted content without making Membership logic depend on role names.

---

# 11. Unauthorized behavior resolution

An access deny produces a structured result, not an immediate redirect/string.

Result should include safe internal fields such as:
- decision: allow/deny;
- policy/rule identifier;
- matched scope;
- missing entitlement keys;
- enrollment state reason;
- recommended public behavior key;
- correlation/audit ID where needed.

Public rendering chooses configured behavior:
- login prompt;
- upgrade/plan selector;
- custom message/template;
- redirect to approved local URL;
- 403;
- hide action/component;
- teaser/partial content;
- protected download denial.

Do not expose sensitive plan/rule internals to anonymous visitors unless explicitly intended.

---

# 12. Access-decision truth table

| Security layer | Manual override | Resource rule | Entitlement satisfied | Result |
|---|---|---|---:|---|
| deny | any | any | any | **DENY** |
| allow | force_deny | any | any | **DENY** |
| allow | force_allow | any | any | **ALLOW** |
| allow | none | none | n/a | **ALLOW** |
| allow | none | protected/allow requirement | yes | **ALLOW** |
| allow | none | protected/allow requirement | no | **DENY** |
| allow | none | explicit deny condition matched | any | **DENY** |

Within ordinary rule evaluation, the most specific rule set is selected; at the same specificity, deny wins.

---

# 13. Example conflict cases

## Case A — broad protection, exact public exception

- all `course` posts require Pro;
- exact `course:welcome` marked Public.

Result: welcome course is public because exact resource is more specific.

## Case B — user has two memberships

- Silver grants library;
- Workshop grants one workshop;
- user holds both.

Result: both entitlement sets are available unless a specific deny rule excludes one.

## Case C — revoked user with stale cache

- user was active;
- enrollment becomes revoked.

Required behavior: entitlement/access cache invalidation is synchronous enough that revocation cannot rely on a long TTL to take effect. Stale authorization after revocation is a security bug.

## Case D — admin force allow

- resource requires Gold;
- support administrator has explicit Membership bypass capability.

Result: allow via audited bypass, provided WordPress/non-membership authorization also allows the operation.

## Case E — provider webhook says paid but reconciliation not complete

Result: raw webhook receipt alone is not authorization. Adapter must validate/idempotently update Enrollment; only resulting valid entitlements are used.

---

# 14. Caching constraints

Access caching is an optimization only.

Cache key must eventually include or derive from versions of:
- principal/user;
- relevant enrollment/entitlement state;
- policy/rule definition;
- resource identity/scope;
- team/seat state where relevant.

Invalidation events include:
- enrollment activation/expiry/revocation/pause/grace change;
- plan entitlement change;
- access rule publish/update/delete;
- manual override;
- team seat/invitation acceptance/removal;
- role/bypass capability change where bypass participates.

Revocation and hard deny changes require immediate/strong invalidation semantics.

Exact cache implementation remains open under M-003.

---

# 15. Explainability requirement

Admin diagnostics must be able to answer:

**Why can/can't user X access resource Y?**

The trace should show:
- base security result;
- bypass/override result;
- matched rule scopes ordered by specificity;
- winning rule;
- entitlement requirements;
- entitlements found/missing;
- final decision;
- cache status/version metadata where safe.

This trace is a core differentiator and a required support/security tool.

---

# 16. Security tests required after development consent

- lower-privilege user cannot use admin bypass;
- Membership allow cannot bypass WordPress object permission;
- same-specificity deny wins;
- exact public exception overrides broader membership requirement only where intentionally configured;
- expired/revoked enrollment grants nothing;
- trial/grace behavior follows plan configuration;
- stale cache cannot preserve revoked access;
- multiple memberships union valid entitlements correctly;
- force override is audited and permission-protected;
- anonymous error output does not reveal sensitive policy internals;
- protected-file endpoint uses the same policy result as page/action access.

# 17. Acceptance status

The semantics in this document are a **candidate policy contract**, not an implemented or verified behavior.

Before marking the access-precedence decision Accepted:

1. review the truth table against every Membership use case;
2. verify it composes with Protector, Roles/Capabilities, Dashboard routes, Forms actions and protected downloads;
3. decide exact manual-override/bypass capabilities;
4. record final precedence in an ADR or update ADR-0013;
5. later, after explicit owner development consent, protect it with automated authorization tests.
