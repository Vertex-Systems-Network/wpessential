# ADR-0020 — Membership Teams, Seats & WordPress Role Sync

Status: **Accepted product/security semantics**  
Date: 2026-08-27

## Decision

Teams/seats are Membership-domain data. WordPress roles are optional compatibility side effects and never the source of Membership truth.

Accepted rules:

1. **Separate concepts**
   - Team = business/membership container;
   - Team internal role = owner/manager/member;
   - Seat = capacity-limited assignment;
   - WordPress role = site authorization primitive;
   - Entitlement = access grant.

2. **One canonical Team owner at a time.**
   Ownership transfer is audited/high-impact and does not silently transfer external billing ownership.

3. **Seat policy is explicit.**
   - owner counts as seat by default candidate;
   - pending non-reserved invite does not consume a seat by default;
   - optional reserved invitation consumes capacity until accept/expiry/revoke;
   - acceptance always rechecks capacity transactionally.

4. **No seat overbooking by race.**
   Concurrent final-seat acceptance must be serialized/locked in runtime implementation.

5. **Invitation token is single-use, high-entropy, hashed at rest and expiring.**

6. **Team member removal revokes only seat-derived access**, not WordPress user or unrelated Membership Enrollment.

7. **Owner Enrollment controls Team eligibility by default.**
   - trialing/active/eligible grace → Team seats eligible;
   - paused → seat-derived access pauses;
   - expired/revoked → seat-derived access ends.

8. **Seat-limit reduction below current usage creates `over_capacity`, not arbitrary eviction.**

9. **Role sync is OFF by default.**
   Membership must function fully without it.

10. **Role sync uses a WPE provenance/claim ledger.**
    Multiple WPE sources may claim one WordPress role.

11. **Conservative role removal.**
    WPE auto-removes a mapped role only when it can determine the role was not pre-existing and no active WPE claim remains. Ambiguous provenance defaults to retain + reconciliation warning.

12. **Pre-existing roles are never claimed as exclusively WPE-owned.**

13. **External/manual role mutation does not reconstruct or revoke paid Membership truth.**

14. **Administrator/Super-Admin-equivalent role mappings are blocked by default.**
    Any future override needs separate high-risk capability, re-authentication and anti-lockout safeguards.

15. **Role-sync failure is a compatibility failure, not Membership state failure.**
    Enrollment/Entitlements remain authoritative; role sync retries/reconciles separately.

## Consequences

- Teams can scale beyond WordPress role mechanics;
- billing/access remains stable even when third-party plugins alter roles;
- role cleanup is conservative rather than destructive;
- capacity/invitation implementation needs real concurrency controls;
- role-claim storage and reconciliation are required if role sync ships.

## Remaining implementation blockers

- team/seat physical schema/indexes;
- exact lock primitive;
- invitation identity/email verification flow;
- owner-transfer provider integration;
- role-claim reconciliation implementation;
- privacy/retention defaults;
- concurrency tests.

All executable work remains blocked by ADR-0014.