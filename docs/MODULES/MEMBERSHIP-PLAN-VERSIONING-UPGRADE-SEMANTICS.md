# WPEssential Membership — Plan Versioning, Grandfathering & Upgrade Semantics

Status: **Phase 0 product semantics / no implementation authorized**  
Date: 2026-08-27

## 1. Problem

Membership Plans change over time. Price, billing source, access benefits, trial policy, seat count and restrictions may not all change with the same semantics.

WPEssential must not:
- rewrite historical purchase facts when a Plan is edited;
- silently remove access from thousands of existing members;
- make billing proration calculations it does not own;
- let two exclusive Plans remain active accidentally during upgrade/downgrade races.

## 2. Separate Plan identity, Plan revision and Enrollment history

### Stable Plan identity
One durable Plan UUID represents the product concept.

### Immutable published Plan revision
Every publish creates an immutable revision for:
- access benefits/entitlement grants;
- trial/grace/access policy;
- seat/team limits;
- group/exclusivity rules;
- display/UX configuration relevant to access.

### Enrollment historical snapshot/reference
Enrollment records:
- Plan UUID;
- Plan revision/effective benefit revision reference where needed;
- source product/subscription/order references;
- effective dates;
- lifecycle state/history.

Do not retroactively change historical external order/subscription price or transaction facts.

## 3. Draft vs published changes

Editing a Plan creates/updates draft revision.

No member access changes until the revision is explicitly published/effective.

Publish preflight shows:
- active/trial/grace Enrollment count affected;
- entitlements added;
- entitlements removed;
- access rules/seat limits changed;
- team capacity impacts;
- downstream definitions using changed entitlement keys;
- workflows/notifications triggered by effective change;
- migration/rebuild workload estimate.

Access-removing changes require higher-impact confirmation than cosmetic label edits.

## 4. Benefit application strategy

Every access-affecting Plan publication chooses an explicit application mode.

### Mode A — Follow current Plan — default candidate
Published benefit revision applies to all eligible existing + future Enrollments at effective time.

Use case:
- service benefit improvements;
- correcting access configuration;
- central membership package intended to evolve.

### Mode B — New Enrollments only / grandfather existing
Existing eligible Enrollments retain their currently assigned benefit revision.
New Enrollments use the new published revision.

Use case:
- legacy package promises;
- reducing benefits for new customers only;
- grandfathered seat count/access.

### Mode C — Scheduled global change
New revision becomes effective for selected/current members at a future UTC timestamp.

Use case:
- announced policy/benefit change.

Exact scheduler/materialization implementation remains later work.

## 5. Benefit revision assignment

Conceptually each Enrollment resolves access benefits from either:
- `follow_current_plan`; or
- a pinned/grandfathered `benefit_revision_uuid`.

The access engine must be able to explain which revision supplied an entitlement.

Changing this assignment for existing members is an audited bulk operation with impact preview.

## 6. Entitlement key rename/removal

Entitlement keys are stable contracts.

Before rename/remove:
- dependency graph lists Access Rules, Dashboards, Forms, REST, Templates, Workflows and integrations using key;
- provide alias/migration mapping where appropriate;
- impact existing/pinned revisions;
- do not silently convert unknown custom integration references.

A display-label rename is not an entitlement-key rename.

## 7. Commercial pricing/billing facts

WPE Membership does not rewrite provider/order price history.

When Plan display price changes:
- WPE catalog/mapping may show new commercial configuration;
- existing subscription price behavior is provider-owned unless an explicit provider action changes it;
- local Enrollment keeps source references and access-effective state;
- WPE must not claim provider proration/charge success until provider confirms.

## 8. Upgrade/downgrade object

Treat a cross-Plan change as an explicit **Membership Change** workflow/record rather than two unrelated button clicks.

Candidate fields:
- source Enrollment/Plan;
- target Plan;
- change type: upgrade/downgrade/cross-grade;
- requested by/source;
- requested/effective timestamp;
- effective mode;
- source billing change reference;
- status;
- idempotency/correlation ID;
- failure/recovery reason.

## 9. Effective modes

### Immediate
Access changes after required provider/manual confirmation.

### End of current period — recommended default for paid downgrade
Old Enrollment remains eligible through paid period; target activates at boundary.

### Scheduled date
For manual/admin/provider workflows that support future effective date.

Do not expose an effective mode that the selected billing adapter cannot honor safely.

## 10. Billing ownership

Provider/commerce system owns where applicable:
- money collection;
- tax;
- invoice;
- refund;
- credit;
- proration amount;
- payment retry.

WPE controls:
- local access-effective timestamps;
- Enrollment transitions after verified provider outcome;
- entitlement change;
- member-facing access status.

If provider returns ambiguous/partial result, transition remains pending/reconciliation-required rather than guessing payment success.

## 11. Immediate upgrade candidate transaction

For exclusive Plan Group:
1. validate target eligibility/capacity;
2. obtain/verify required billing provider change outcome;
3. lock subject + Plan Group transition;
4. re-read old/new state;
5. create/prepare target Enrollment;
6. end/schedule source Enrollment at same effective boundary;
7. activate target;
8. recompute entitlements/generation atomically where possible;
9. commit;
10. emit events/notifications after commit.

Goal: no accidental gap and no unintended double entitlement beyond explicit transition policy.

## 12. Downgrade default

For externally paid recurring membership, candidate default is **period-end effective** unless administrator/provider configuration explicitly chooses immediate.

Reason:
- avoids removing already-paid access unexpectedly;
- maps naturally to cancellation-at-period-end semantics.

Immediate downgrade remains valid where business policy/provider supports it and impact is explicit.

## 13. Trial carry-over

Default candidate:
- upgrading/cross-grading inside the same exclusive Plan Group does **not** automatically grant a fresh full trial if the user already consumed a trial in that group;
- Plan/Promotion policy may explicitly allow trial reset/credit.

Track trial eligibility/history separately from current state so repeated cancel/rejoin cannot unintentionally loop free trials.

## 14. Grace during change

A source Enrollment in `grace` can change Plan only if target/provider policy permits.

Do not silently carry an old grace window into a new paid Plan unless explicit policy says so.

If provider change is pending while old access remains valid, state/UX explains the pending transition.

## 15. Scheduled change cancellation

A future upgrade/downgrade can be cancelled only while its irreversible provider/effective step has not occurred.

Cancellation:
- verifies provider cancellation where necessary;
- preserves source Enrollment schedule correctly;
- removes target pending change;
- is idempotent/audited.

## 16. Failed transition recovery

Examples:
- provider charged but local transition failed;
- local transition committed but notification failed;
- provider API timed out with unknown outcome;
- webhook arrives before synchronous response;
- source subscription changed outside WPE.

Rules:
- external idempotency key/source reference;
- ambiguous provider outcome → reconciliation-required, never retry charge blindly;
- local access state can be rebuilt from verified provider + Enrollment history;
- non-critical notification failure does not reverse valid access;
- diagnostics clearly separates payment/source and local access status.

## 17. Plan deletion/archive

Published Plan with historical/active Enrollments is normally **archived**, not hard-deleted.

Archive:
- blocks new enrollment unless explicitly restored;
- preserves historical identity/revisions;
- existing members follow configured continuation policy;
- dependency graph remains resolvable.

Hard deletion requires no retained dependencies/history or a deliberate migration/reassignment process.

## 18. Plan cloning

Clone creates new Plan UUID.

It can copy current configuration but does not copy:
- existing Enrollments;
- billing provider subscriptions;
- transaction history;
- invitation/team ownership;
- analytics history.

Provider product mapping must be explicitly selected/created.

## 19. Seat limit changes

Increasing seat limit can apply immediately according to benefit revision mode.

Reducing below currently occupied/reserved seats must not evict arbitrary members silently.

Candidate result:
- Plan/team becomes `over_capacity` for management;
- existing assigned seats remain according to configured policy;
- new invitations/seat additions blocked;
- admin must resolve/remediate or schedule future enforcement.

Immediate forced removals require explicit high-impact operation.

## 20. Required future tests

After development consent:
- draft change does not alter access;
- follow-current benefit addition/removal;
- grandfathered Enrollment retains old benefits;
- scheduled revision activation;
- entitlement rename dependency handling;
- immediate upgrade no gap/double grant;
- period-end downgrade;
- provider timeout/reconciliation;
- duplicate change request;
- concurrent upgrade requests;
- trial reuse protection;
- seat-limit reduction over-capacity;
- archived Plan continuation;
- rollback/rebuild explainability.

No runtime code or billing calls exist in this planning document.