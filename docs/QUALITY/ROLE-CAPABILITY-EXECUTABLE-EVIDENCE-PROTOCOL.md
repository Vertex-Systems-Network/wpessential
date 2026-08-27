# WPEssential — Role & Capability Executable Security Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0032, ADR-0097, `docs/SECURITY/ROLE-CAPABILITY-RUNTIME-MUTATION-EVIDENCE-PROFILE.md`, User Profile, Policy, Audit, Multisite, ADR-0014.

## 1. Purpose

Define executable evidence required before Role & Capability Manager can claim safe role/user-capability mutation, anti-lockout, recovery, Multisite or cache-revocation behavior.

The authority invariant is fixed:

**WordPress remains the effective authorization authority. WPE may plan, simulate, guard, apply and audit native mutations, but it does not create a parallel authorization database or an anonymous recovery bypass.**

## 2. Runtime profile

Future certification records:
- WordPress/PHP/database versions;
- single-site/Multisite topology;
- RA1/RA2 adapter profile;
- native/third-party roles and capability owners relevant to fixtures;
- WPE Policy/cache-generation profile;
- Change Plan/snapshot schema versions;
- recent-auth/recovery profile;
- Audit profile;
- network/Super Admin context.

## 3. Fixture matrix

### RA-01 — Create WPE-managed custom role
Role is created through certified native WordPress semantics and effective capabilities match the reviewed Plan.

### RA-02 — Edit custom role capabilities
Minimal proposed diff is applied; unrelated capabilities remain unchanged.

### RA-03 — Delete unassigned custom role
Deletion succeeds only after authority, dependency and recovery analysis.

### RA-04 — Delete assigned role
Assigned users/default-role/dependency impact is reviewed and reassignment/removal behavior is explicit before mutation.

### RA-05 — Core role preservation
Ordinary WPE UI cannot destructively remove/replace core role semantics without a separately accepted high-risk operation.

### RA-06 — Third-party role preservation
Unknown/plugin-owned roles and caps survive unrelated WPE edits; deactivation does not authorize silent purge.

### RA-07 — Third-party ownership/version drift
Changed external role/cap state invalidates stale assumptions and triggers re-read/review.

### RA-08 — Add role to user
Authorized add preserves unrelated existing roles where operation semantics are additive.

### RA-09 — Remove one role from user
Only selected role is removed; residual effective capabilities are recalculated.

### RA-10 — Replace role set
Replace-all is treated high-risk and cannot silently drop unrelated plugin roles without explicit reviewed diff.

### RA-11 — Explicit user capability allow
Native user-specific override is applied only through declared high-risk action and appears in effective-capability simulation.

### RA-12 — Explicit user capability deny
Where WordPress semantics support explicit deny, effective result is simulated and verified; unsupported semantics are not invented.

### RA-13 — Remove explicit override
Removing override restores inherited role behavior rather than writing a copied grant/deny.

### RA-14 — Meta/object capability simulation
Effective simulation accounts for relevant mapped/meta capability context and does not pretend primitive role maps fully describe object authorization.

### RA-15 — Change Plan fingerprint
Plan pins target scope, actor, normalized diff and source authority fingerprint.

### RA-16 — Stale Change Plan
Role/user authority changed after review causes apply to block/re-plan rather than execute stale diff silently.

### RA-17 — Risk classification
Low/medium/high/critical classes are deterministic enough for policy while not pretending to be a universal privilege score.

### RA-18 — Affected-user impact
Plan reports bounded affected principals/counts according to privacy/privilege rules before broad mutation.

### RA-19 — Current operator impact
Plan identifies whether actor loses required effective administration access.

### RA-20 — Recovery-principal invariant
Ordinary UI blocks a mutation that would leave zero viable recovery principals in affected scope.

### RA-21 — Last admin-equivalent removal
Removing the final viable recovery principal fails even if role name itself is not literally `administrator`.

### RA-22 — Multiple-role residual recovery
A principal with another effective recovery path is evaluated by effective capabilities, not role-name heuristics.

### RA-23 — User override affects recovery
Individual grants/denies are included in recovery-principal analysis.

### RA-24 — Self-lockout attempt
Critical self-lockout requires recent auth, another viable recovery principal and immediate pre-apply revalidation.

### RA-25 — Recent-auth expiry/purpose
Expired or wrong-purpose assertion cannot authorize critical role/network mutation.

### RA-26 — Native mutation verification
After apply WPE re-reads WordPress authority and verifies expected effective diff before reporting success.

### RA-27 — Partial native mutation
Multi-step role deletion/reassignment failure is reported as partial/reconciliation-required, never clean success.

### RA-28 — WPE metadata failure after native mutation
If native authority changed but Plan/Audit metadata persistence fails, WPE re-reads native state and does not blindly retry destructive mutation.

### RA-29 — Duplicate apply request
Retry/idempotent submission cannot repeat replace/delete mutation after authoritative success.

### RA-30 — Pre-change snapshot
Critical operation stores only bounded authority metadata/fingerprint required for recovery; snapshot is not represented as full backup.

### RA-31 — Reverse-diff restore
Revert computes a new reverse diff against current state rather than blindly restoring stale full options/usermeta.

### RA-32 — Reverse-diff conflict
Newer administrator/plugin changes cause explicit conflict/review instead of silent overwrite.

### RA-33 — RR1 alternate authenticated principal
Another properly authorized principal can repair state without any special bypass.

### RA-34 — RR2 WP-CLI/native break-glass
Documented native privileged recovery can restore required role/cap state without WPE anonymous auth.

### RA-35 — RR3 WPE recovery mode
Recovery mode can disable WPE overlays/diagnostics only for already WordPress-authorized principal; it cannot mint admin authority.

### RA-36 — No public recovery URL
No anonymous token/URL bypass grants Administrator/Super Admin or disables core capability checks globally.

### RA-37 — Site role isolation
Mutating a user’s role on one site does not alter unrelated site role assignments in Multisite.

### RA-38 — Site admin cannot grant Super Admin
Site-level Role Manager cannot grant/remove network Super Admin authority.

### RA-39 — Network Super Admin adapter
When claimed, network mutation requires current core network authority, explicit action and separate recovery analysis.

### RA-40 — `switch_to_blog()` boundary
Changing site context never becomes evidence of network authority.

### RA-41 — Default-role implications
Changing/deleting a role that interacts with default-role configuration is previewed and resulting user/site behavior verified.

### RA-42 — Site removal/user removal boundary
Removing user from one site does not silently delete network/global user or unrelated role facts.

### RA-43 — Capability cache revocation
Committed capability revoke invalidates WPE Policy/cache generations so stale cached allow cannot survive accepted correctness window.

### RA-44 — User Profile integration
Profile mass-assignment/protected-meta path remains unable to bypass Role Manager authority.

### RA-45 — REST/Dashboard/Listings integration
Surfaces depending on capabilities observe revocation and do not serve privileged cached output after committed change.

### RA-46 — Audit correctness/redaction
Audit records actor/scope/target/diff/risk/recovery/result without credentials/session/Application Password secrets.

### RA-47 — Pro expiry/degraded runtime
Safe native authority remains intact under ADR-0007; WPE editing restrictions cannot lock administrator out of WordPress core recovery.

### RA-48 — Large-network/bulk performance
Reference large user/site/role workload meets bounded impact-scan/apply/cache-invalidation budgets with zero wrong-site/network grants.

## 4. Pass gates

Certification fails if:
- ordinary WPE UI can commit zero-recovery-principal state;
- site admin can grant/remove Super Admin;
- stale Change Plan applies silently;
- partial native mutation is reported as full success;
- metadata failure causes blind destructive retry;
- reverse restore overwrites newer unrelated authority state;
- public/anonymous recovery bypass exists;
- revoked capability remains effectively allowed due to WPE cache beyond declared correctness semantics;
- third-party roles/caps are silently destroyed by unrelated WPE operation.

## 5. Required future evidence report

Include:
- runtime/authority profile;
- RA-01…RA-48 pass/fail;
- effective-capability simulation cases;
- recovery-principal/self-lockout evidence;
- stale/partial/reconciliation cases;
- snapshot/reverse-diff conflict evidence;
- Multisite/Super Admin tests;
- cache revocation results;
- Audit redaction evidence;
- bulk/large-network measurements.

## 6. Current state

**RA fixtures executed: 0/48.**  
Zero-recovery-principal ordinary UI commits: **0 executed / 0 permitted by contract**.  
Unauthorized Super Admin/network grants: **0 executed / 0 permitted by contract**.

No role, capability, user-role, Super Admin, cache-generation or recovery mutation has been executed.

## 7. Development gate

Execution requires explicit owner consent under ADR-0014.