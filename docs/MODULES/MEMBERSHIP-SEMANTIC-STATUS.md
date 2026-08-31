# Membership Semantic Status

Status: **Phase 0 source-of-truth clarification**  
Date: 2026-08-27

The detailed files `MEMBERSHIP-ACCESS-POLICY.md` and `MEMBERSHIP-ENROLLMENT-STATE-MACHINE.md` were originally written as candidate specifications.

Their product semantics are now accepted through dedicated ADRs:

- **ADR-0015 — Membership Access Precedence & Explainability**
- **ADR-0016 — Membership Enrollment Lifecycle Semantics**

Where wording in the earlier candidate documents conflicts with these ADRs, the ADRs take precedence.

Important accepted refinement to the earlier flat specificity wording:
- specificity is two-dimensional: resource scope + action/subresource scope;
- an exact action/download/partial-region rule within an exact resource is more specific for that action than the whole-resource rule.

Still unresolved/implementation-gated:
- entitlement physical schema/indexes;
- cache implementation/invalidation mechanism;
- Plan version-pinning/follow-current details;
- exact grace durations;
- Plan Group cross-grade concurrency;
- team/seat concurrency;
- provider-specific reconciliation;
- protected-file transport implementation.

No runtime code or tests exist yet.