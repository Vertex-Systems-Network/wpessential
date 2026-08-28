# ADR-0201 — Placement & Personalization Executable Evidence Protocol

Status: **Accepted**  
Date: **2026-08-29**  
Decision class: **Phase 0 planning/evidence governance — no development authorization**

## Context

ADR-0177 accepted F07 — Experience Placement & Personalization Manager as a reusable WPEssential foundation. ADR-0180 reserved the stable `PLC-001…PLC-176` technical-evidence namespace as 16 groups × 11 fixtures, but the group envelope by itself was not sufficient to support future runtime certification.

WP69 therefore expanded the reserved evidence envelope into exact fixture-level requirements in:

`docs/QUALITY/PLACEMENT-PERSONALIZATION-EXECUTABLE-EVIDENCE-PROTOCOL.md`

The resulting protocol documents all 176 reserved PLC fixtures without executing any of them.

## Decision

Accept `PLACEMENT-PERSONALIZATION-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical detailed executable-evidence specification for F07.

Current PLC truth:

- namespace: `PLC-001…PLC-176`;
- documented: **176/176**;
- executed: **0/176**;
- runtime certification: **0**;
- production implementation authorization: **not granted**.

WP69 is complete as a **planning/evidence package only**.

The next reserved detailed-evidence work package becomes:

**WP70 — F08 Experimentation & Rollout (`EXP-001…EXP-176`)**.

## Accepted evidence coverage

The protocol freezes fixture-level evidence for:

1. placement/slot registry and adapter discovery;
2. audience/context resolution and eligibility;
3. priority/conflict/stacking/fallback;
4. frequency caps/session/user identity;
5. schedule/timezone/campaign lifecycle;
6. Component Blueprint rendering/data Policy;
7. asset loading/scoped chunks/performance;
8. cache key/invalidation/personalized leakage;
9. accessibility/responsive/dismissal/preferences;
10. consent/dark-pattern/privacy/PII boundaries;
11. experiment binding/exposure logging;
12. theme/builder/Woo/domain adapter conflicts;
13. Multisite/template/site override;
14. lifecycle/expiry/disabled component behavior;
15. many-placement/high-traffic performance;
16. end-to-end popup/banner/portal/cart placement golden/regression scenarios.

## Architecture boundaries made explicit

- Placement/personalization decides presentation eligibility, not authorization.
- Audience match is not role/capability/membership entitlement.
- Hidden/not-selected UI does not deny or grant the underlying action; canonical Policy remains authoritative.
- Selected component is not equivalent to successful render or qualifying exposure.
- F08 experiment assignment is not consent and is not necessarily an exposure.
- F04 score/rank may order eligible candidates but cannot authorize an ineligible/protected component.
- Component data is reauthorized through canonical Query/Data Source/Policy owners at render time.
- Personalized/protected cache output must never leak across users, sessions, sites, tenants or consent states.
- Slot/theme/builder/Woo adapters expose bounded certified placement contracts and do not create arbitrary DOM/PHP/script injection authority.
- Safe Script/Tag remains a separate browser-side owner; server executable code remains Extension SDK/VCS/release territory.
- Multisite placement ownership, cache, frequency and audience scope are server-resolved and isolated.
- AI/MCP may draft/explain/validate placement definitions only through ordinary Policy/revision/approval boundaries and cannot bypass consent or runtime authorization.

## Evidence truth

Writing an expected outcome is not execution. Runtime certification requires future measured evidence against the exact relevant PLC fixtures, including implementation commit/runtime, adapter versions, inputs, candidate selection reasons, Policy/consent/cache/frequency state, render/exposure distinction, fault/concurrency parameters and performance measurements where applicable.

No static/paper evidence is promoted to runtime certification by this ADR.

## Consequences

### Positive

- F07 now has the same fixture-level evidence precision as Search, Decision, Ledger and Resource Scheduling.
- Presentation and authorization boundaries are testable rather than implicit.
- Personalized cache leakage, identity stitching, consent and experiment-exposure failure classes are reserved before implementation.
- Builder/theme/Woo integration behavior is constrained to certified slot contracts before runtime code exists.

### Cost

Future F07 implementation cannot be declared ready from visual success alone; applicable PLC security, privacy, concurrency, cache, accessibility, Multisite, adapter and performance fixtures must execute and pass.

## Explicit non-authorization

This ADR does **not** authorize:

- production source/runtime implementation;
- a placement registry/evaluator;
- WordPress hooks or browser rendering;
- cache/frequency state mutation;
- theme/builder/Woo adapter execution;
- experiment assignment/exposure runtime;
- provider/API/AI/MCP calls;
- package/dependency setup;
- tests, benchmarks, builds, migrations or deployment.

Development remains governed by ADR-0014, `DEVELOPMENT-CONSENT.md`, `AGENTS.md` and `docs/APPROVAL-LEDGER.md`.

Global implementation authorization remains **0/56**.