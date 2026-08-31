# WPEssential Membership — Teams, Seats & WordPress Role Sync Semantics

Status: **Phase 0 product/security semantics / no implementation authorized**  
Date: 2026-08-27

## 1. Separation of concepts

A Membership Team is not a WordPress role and a WordPress role is not a seat.

Separate domains:
- Team = membership/business container;
- Team role = `owner` / `manager` / `member` inside that Team;
- Seat = capacity-limited membership assignment;
- WordPress role = site authorization primitive;
- Entitlement = access/benefit grant.

Membership authorization is driven by canonical Enrollment/Entitlement state, never solely by WordPress role mutation.

## 2. Team ownership

Each Team has one active owner identity and is associated with an owner Enrollment/Plan context.

Candidate invariants:
- one canonical owner at a time;
- owner must remain a valid WordPress user/subject;
- team lifecycle is bounded by owner Enrollment unless product policy explicitly transfers ownership/billing context;
- owner transfer is audited and high-impact;
- transferring Team ownership does **not** silently transfer an external billing subscription unless provider explicitly supports and confirms it.

## 3. Team internal roles

### Owner
Can manage Team within Membership policy:
- view Team;
- invite/remove members;
- assign Team managers;
- view seat usage;
- initiate allowed transfer;
- manage Team-level metadata.

Billing/account operations remain separately capability/provider controlled.

### Manager
Can perform delegated Team management selected by Plan/site policy.

Default candidate:
- invite members;
- revoke pending invites;
- remove ordinary members;
- view seat usage.

Cannot:
- transfer ownership;
- increase purchased seat limit;
- manipulate billing source;
- grant WPE high-risk capabilities.

### Member
Receives seat-derived entitlements and normal member self-service only.

## 4. Seat counting

Plan defines seat policy explicitly.

Candidate options:
- owner counts as seat — **default candidate: yes**;
- managers count as seats — default yes because they are also members unless policy defines admin-only manager;
- reserved invitation consumes seat — configurable;
- pending invitation without reservation consumes no seat by default;
- suspended/paused seat consumes capacity — configurable business rule, default candidate yes until removed to prevent free overbooking through pause toggles.

UI shows:
- purchased/configured seat limit;
- occupied seats;
- reserved seats;
- available seats;
- over-capacity state.

## 5. Invitation reservation modes

### Non-reserved invite — default
- invitation does not consume seat until acceptance;
- acceptance transaction rechecks capacity;
- if full, invite remains/returns capacity error according to expiry/policy.

Pros: no capacity stranded by unused invitations.

### Reserved invite
- invitation consumes a reserved seat immediately;
- reservation expires/revokes with invitation;
- acceptance converts reservation to occupied seat atomically.

Use case: customer must guarantee invited employee a seat.

Both modes require concurrency-safe counting.

## 6. Invitation tokens

- high-entropy one-time token;
- persist token hash, never reusable raw token after issuance;
- bounded expiry;
- single Team/role/email or user target context;
- rate-limited validation/acceptance;
- accepted/revoked/expired state;
- acceptance binds to authenticated/verified identity according to invite policy;
- email comparison/normalization rules cannot allow invitation theft through case/alias assumptions.

Changing invite recipient invalidates old token and issues a new invitation identity.

## 7. Invitation acceptance transaction

Candidate atomic flow:
1. validate token hash, expiry and state;
2. resolve authenticated/eligible user;
3. lock Team/capacity scope;
4. verify owner Enrollment and Team remain eligible;
5. recalculate occupied + reserved seats;
6. enforce duplicate-user/team rule;
7. enforce Plan/group/eligibility constraints;
8. consume reservation or capacity slot;
9. create active Team membership;
10. materialize/update derived entitlements/access generation;
11. mark invitation accepted;
12. commit;
13. emit events/notifications after commit.

Two simultaneous accepts cannot both take the final seat.

## 8. Duplicate membership / multiple Teams

A user may belong to multiple Teams where Plan/site policy permits.

Entitlements from eligible Team seats union with other valid Membership entitlements under ADR-0015.

Within one Team:
- one active canonical Team membership per user;
- duplicate pending invitations should be detected/merged/rejected according to invite rules.

## 9. Member removal

Removing a Team member:
- requires Team/policy permission;
- ends seat membership at effective timestamp;
- revokes seat-derived entitlements immediately/strongly;
- invalidates authorization cache generation;
- does not delete the WordPress user;
- does not remove unrelated direct Membership Enrollment;
- role-sync cleanup only affects WPE-owned role claims.

Optional scheduled removal can be a later product feature; immediate is baseline.

## 10. Seat transfer

A seat can effectively be reassigned by removing old member and assigning/inviting new member.

Do not keep entitlements active for both old and new users merely because UI calls it a transfer.

High-level transaction:
- validate destination eligibility;
- revoke old seat entitlement at effective boundary;
- assign/reserve new seat;
- avoid capacity double-count;
- audit both identities.

## 11. Owner Enrollment lifecycle impact

Default behavior:
- owner `trialing`/`active` → Team seats eligible;
- owner eligible `grace` → seats remain eligible according to Plan grace policy;
- owner `paused` → seat-derived entitlements pause;
- owner `expired` → seat access expires;
- owner `revoked` → seat access revoked/disabled immediately.

The Team/history remains stored even when access is inactive.

## 12. Over-capacity state

A Plan benefit/seat-limit reduction can result in occupied seats > new limit.

Default safety behavior:
- mark Team `over_capacity`;
- do not choose arbitrary users to evict automatically;
- block new seats/invites/reservations;
- show exact overage and affected Plan revision;
- administrator/Team owner resolves according to explicit policy.

A future enforcement date/priority-removal feature requires separate semantics.

## 13. Ownership transfer

Preconditions:
- target user is eligible and active/accepted Team member or is promoted atomically;
- current owner authorized/re-authenticated where policy requires;
- no billing-source promise is made unless provider confirms transfer;
- capacity remains valid;
- current owner's future Team role after transfer chosen explicitly.

If billing subscription remains with old owner/account, UI must not imply commercial ownership moved.

## 14. Team deletion/archive

A Team with history should normally be archived/closed, not hard-deleted.

Closure:
- stops new invites;
- revokes seat-derived entitlements;
- preserves audit/history;
- does not delete users;
- preserves source/billing references needed for reconciliation.

Hard deletion follows privacy/retention + dependency policy.

---

# WordPress Role Sync

## 15. Default is OFF

Membership access works without role sync.

Role sync exists only for compatibility with third-party plugins/themes that consume WordPress roles.

## 16. Role mapping

Mappings may be configured from:
- Plan + eligible Enrollment state;
- selected Team role/seat state;
- entitlement key where use case is explicit.

Do not map arbitrary protected-resource rules into role churn.

## 17. Role provenance / claims

WordPress's user role list does not record which plugin assigned a role. WPE therefore needs its own role-sync claim ledger if role sync ships.

Conceptual claim:
- user ID;
- WordPress role slug;
- WPE source type/UUID (Enrollment/Team/etc.);
- claim active/inactive;
- whether role was already present before first WPE claim;
- timestamps/version/audit reference.

Multiple WPE sources can claim the same role.

## 18. Conservative removal rule

WPE removes a WordPress role only when it can safely determine that:
- the role was not pre-existing before WPE began managing it; and
- no active WPE claim still requires it; and
- removal will not violate anti-lockout/security protections.

If provenance is ambiguous, default to **do not remove automatically**; flag reconciliation to administrator.

This prevents WPE from deleting a role that was independently/manual assigned.

## 19. Pre-existing roles

If user already has mapped role when first WPE claim begins:
- record `preexisting=true`;
- WPE considers mapping satisfied;
- later Enrollment expiry does not remove that pre-existing role automatically.

## 20. Multiple WPE claims

If Plan A and Team B both require `customer_pro`:
- keep one WordPress role assignment;
- maintain two WPE claims;
- remove role only when last WPE-owned claim ends and no pre-existing/external ownership protection applies.

## 21. External/manual role changes

Because WordPress has no universal role-provenance API, an external plugin/admin can alter roles.

Role reconciliation:
- detects desired vs actual;
- distinguishes safe auto-repair from ambiguous destructive removal;
- logs conflict;
- never uses role state to reconstruct paid Membership truth automatically.

External removal of a role does not revoke Membership entitlements. Depending mapping policy WPE may re-add mapped role or mark conflict.

## 22. Administrator-equivalent roles

Mapping Membership Plans/Teams to `administrator`, Super Admin semantics or administrator-equivalent custom roles is blocked by default.

If ever permitted:
- dedicated high-risk capability;
- privilege escalation impact analysis;
- re-authentication;
- anti-lockout/recovery;
- explicit warning that Membership billing events could otherwise alter administrative privileges.

Strong recommendation: Membership role sync should target low-privilege compatibility roles only.

## 23. Role sync state mapping

Candidate defaults:
- `trialing` → mapped roles yes if Plan mapping applies;
- `active` → yes;
- eligible `grace` → yes by default;
- `paused` → remove/end WPE role claim according to conservative ownership rules;
- `expired` → end claim;
- `revoked` → end claim immediately.

Role assignment/removal follows canonical Enrollment state, never raw provider status.

## 24. Failure behavior

If role sync fails after Membership state commits:
- Membership entitlements remain authoritative;
- record sync unhealthy/conflict;
- retry through idempotent job/reconciliation;
- do not roll back valid Enrollment because third-party role mutation failed.

## 25. Import/migration

Imported source roles are evidence only when the migration adapter explicitly defines their source semantics.

Never infer “user currently has premium role = paid active member” generically.

Migration can optionally establish WPE role-sync claims only when provenance is trustworthy; otherwise preserve roles as pre-existing compatibility state.

## 26. Required future tests

After development consent:
- final-seat concurrent invitation accepts;
- reserved/non-reserved invitation capacity;
- invite expiry frees reservation;
- duplicate invitation/member;
- owner Enrollment pause/expire/revoke cascades;
- over-capacity Plan reduction;
- owner transfer;
- remove member immediate entitlement revoke;
- multiple Teams entitlement union;
- pre-existing role never auto-removed;
- multiple WPE claims same role;
- external/manual role conflict;
- role-sync failure does not alter Membership truth;
- administrator-equivalent mapping blocked;
- stale authorization cache cannot preserve removed seat access.

No Team, seat, invitation or role-sync runtime implementation exists yet.