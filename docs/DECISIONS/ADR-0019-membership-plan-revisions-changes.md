# ADR-0019 — Membership Plan Revisions, Grandfathering & Plan Changes

Status: **Accepted product semantics**  
Date: 2026-08-27

## Decision

Membership Plan changes use versioned published access-benefit revisions and explicit effective-change semantics. Historical billing/purchase facts are not rewritten by editing a Plan.

Accepted rules:

1. **Stable Plan identity, immutable published revisions.**
   - Plan UUID remains stable;
   - access-affecting publish creates immutable revision;
   - draft edits do not affect member access.

2. **Enrollment preserves historical source/commercial references.**
   - original Plan/revision/source/order/subscription references remain auditable;
   - WPE does not rewrite provider price/payment history.

3. **Every access-affecting publication chooses application mode:**
   - `follow_current_plan` — applies to existing + future eligible Enrollment; default candidate;
   - `new_enrollments_only` — grandfather existing members on their assigned benefit revision;
   - `scheduled_global_change` — future effective timestamp.

4. **Publish preflight is mandatory for material changes.**
   Show member count, benefits added/removed, entitlement-key dependencies, seat-limit impact and estimated rebuild workload.

5. **Entitlement keys are stable contracts.**
   Display-label change is not key rename. Key rename/removal requires dependency/migration mapping.

6. **Paid billing math remains provider-owned.**
   WPE controls local access-effective time only after verified provider outcome.

7. **Plan cross-change is an explicit change workflow**, not two unrelated Enrollment edits.
   Change type can be upgrade/downgrade/cross-grade with requested/effective state and idempotency/reference data.

8. **Effective modes:**
   - immediate after required confirmation;
   - period-end;
   - scheduled date where supported.

9. **Paid recurring downgrade defaults to period-end** unless explicit policy/provider supports immediate change.

10. **Exclusive Plan Group transitions must avoid unintended access gaps/double grants.**
    Runtime implementation must lock/recheck group state transactionally.

11. **Trial reuse is not automatically reset by cross-grade/rejoin.**
    Default: no fresh trial within same exclusive group unless explicit trial/promotion policy permits it.

12. **Ambiguous provider outcome goes to reconciliation-required**, not guessed success and not blind charge retry.

13. **Published Plan with active/history is archived rather than hard-deleted by default.**

14. **Reducing seat capacity below current usage does not randomly evict users.**
    Team becomes over-capacity and blocks new seat allocation until explicit remediation.

## Consequences

- benefit changes are deliberate and explainable;
- grandfathering is first-class rather than accidental copied Plans;
- provider billing remains source of monetary truth;
- high-scale access removals need strong cache/materialization invalidation;
- Plan revisions become dependencies for Enrollment/migration history.

## Remaining implementation blockers

- physical revision/assignment fields;
- bulk entitlement rebuild strategy;
- scheduled-effective job/authorization mechanics;
- Plan Group locking;
- provider-specific change contracts;
- exact trial eligibility history schema.

All executable work remains blocked by ADR-0014.