# WPEssential — Role & Capability Executable Security Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Work package refinement: `P0-M00-WP40`  
Related: ADR-0032, ADR-0097, ADR-0114, `docs/SECURITY/ROLE-CAPABILITY-RUNTIME-MUTATION-EVIDENCE-PROFILE.md`, KPA, CAC, VER, MLC, PDL, ERR, Audit, User Profile, Membership, REST, Multisite, ADR-0014.

## 1. Purpose

Define executable evidence required before Role & Capability Manager can claim safe role/user-capability mutation, effective-capability simulation, anti-lockout, recovery, cache revocation, third-party coexistence, Multisite/Super Admin handling or scale support.

The original **RA-01…RA-48** semantics remain preserved. This canonical refinement extends the fixed matrix to **RA-01…RA-176**.

Current execution truth: **0/176 executed**.

Authority invariant:

**WordPress remains effective authorization authority. WPE may discover, plan, simulate, guard, apply, verify and audit native mutations, but it does not create a parallel authorization database or anonymous recovery bypass.**

Passing KPA/CAC/VER/MLC/PDL/ERR evidence never auto-certifies Role Manager runtime behavior.

## 2. Certification profile

Record exact WordPress/PHP/database versions; single-site/Multisite topology; RA1/RA2 profile; role/capability owners and versions; Policy/CAC generations; Change Plan/snapshot schema; recent-auth/recovery profile; Audit profile; network/Super Admin context; third-party role plugins involved; scale fixture.

Independent certification classes:
- `RA-N` native authority/role semantics;
- `RA-U` user role/override semantics;
- `RA-P` Change Plan/impact/preconditions;
- `RA-L` anti-lockout/recovery-principal safety;
- `RA-X` mutation verification/reconciliation/revert;
- `RA-M` Multisite/Super Admin;
- `RA-C` capability cache/consumer revocation;
- `RA-E` external/third-party coexistence;
- `RA-S` security/privacy/audit;
- `RA-O` observability/performance/scale.

# 3. Original core role/user fixtures — RA-01…RA-14
- **RA-01** — create WPE-managed custom role through certified native WordPress semantics; effective caps match Plan.
- **RA-02** — edit custom role applies minimal diff; unrelated capabilities preserved.
- **RA-03** — delete unassigned custom role only after authority/dependency/recovery analysis.
- **RA-04** — delete assigned role reviews users/default-role/dependencies/reassignment first.
- **RA-05** — ordinary WPE UI cannot destructively replace/remove core role semantics without separate accepted high-risk operation.
- **RA-06** — unknown/plugin-owned roles/caps survive unrelated WPE edits/deactivation.
- **RA-07** — third-party role/cap drift invalidates stale assumptions and requires re-read/review.
- **RA-08** — additive role assignment preserves unrelated existing roles.
- **RA-09** — remove selected role only; residual effective capabilities recomputed.
- **RA-10** — replace-all role set is high-risk and cannot silently drop unrelated plugin roles.
- **RA-11** — explicit user capability allow uses declared high-risk action and appears in simulation.
- **RA-12** — explicit deny follows actual WordPress semantics; unsupported deny model is not invented.
- **RA-13** — removing user override restores inherited behavior rather than copying role state.
- **RA-14** — mapped/meta/object capability simulation does not pretend primitive role map fully defines authorization.

# 4. Original Change Plan / anti-lockout fixtures — RA-15…RA-25
- **RA-15** — Change Plan fingerprints target scope, actor, normalized diff and source authority.
- **RA-16** — stale authority after review blocks/re-plans instead of silently applying.
- **RA-17** — risk classes are deterministic guards, not fictional universal privilege score.
- **RA-18** — affected-user impact is bounded/privacy-aware before broad mutation.
- **RA-19** — Plan identifies current operator access impact.
- **RA-20** — ordinary UI blocks mutation leaving zero viable recovery principals.
- **RA-21** — final admin-equivalent removal fails even when role name is not `administrator`.
- **RA-22** — multiple-role residual recovery uses effective capabilities, not role-name heuristics.
- **RA-23** — individual grants/denies participate in recovery-principal analysis.
- **RA-24** — critical self-lockout requires recent auth, another recovery principal and immediate pre-apply revalidation.
- **RA-25** — expired/wrong-purpose recent-auth assertion cannot authorize critical mutation.

# 5. Original mutation/recovery fixtures — RA-26…RA-36
- **RA-26** — after apply WPE re-reads native authority and verifies expected effective diff.
- **RA-27** — partial multi-step native mutation is partial/reconciliation-required, never full success.
- **RA-28** — metadata failure after native authority change triggers re-read/reconciliation, never blind destructive retry.
- **RA-29** — duplicate apply cannot repeat already authoritative replace/delete mutation.
- **RA-30** — bounded pre-change snapshot is recovery metadata, not full backup.
- **RA-31** — revert computes reverse diff against current state instead of restoring stale full options/usermeta.
- **RA-32** — newer changes create reverse-diff conflict/review, not silent overwrite.
- **RA-33** — another properly authorized principal can repair via ordinary native authority.
- **RA-34** — WP-CLI/native privileged break-glass works without WPE anonymous auth.
- **RA-35** — WPE recovery mode only disables WPE overlays/diagnostics for already-authorized principal; cannot mint authority.
- **RA-36** — no public token/URL bypass grants Administrator/Super Admin or disables core checks globally.

# 6. Original Multisite/cache/integration fixtures — RA-37…RA-48
- **RA-37** — role mutation on one site does not alter unrelated site assignments.
- **RA-38** — Site Admin cannot grant/remove Super Admin through site Role Manager.
- **RA-39** — claimed network Super Admin adapter requires current core network authority and separate recovery analysis.
- **RA-40** — `switch_to_blog()` never becomes evidence of network authority.
- **RA-41** — role/default-role implications are previewed and resulting behavior verified.
- **RA-42** — removing user from one site does not delete global user or unrelated role facts.
- **RA-43** — capability revoke invalidates WPE Policy/cache generations within accepted correctness window.
- **RA-44** — User Profile mass-assignment/protected-meta path cannot bypass Role Manager authority.
- **RA-45** — REST/Dashboard/Listings dependent surfaces observe committed revocation.
- **RA-46** — Audit records actor/scope/target/diff/risk/recovery/result without credentials/session/Application Password secrets.
- **RA-47** — Pro expiry preserves safe native authority/core recovery under ADR-0007.
- **RA-48** — large-network/bulk fixture measures bounded impact/apply/cache invalidation with zero wrong-site/network grants.

# 7. Native WordPress authority semantics — RA-49…RA-64
- **RA-49** — role slug/name normalization follows native constraints and cannot create reserved/confusable duplicate role unexpectedly.
- **RA-50** — adding existing role is idempotent/explicit update according to WordPress semantics; no hidden replacement.
- **RA-51** — removing nonexistent role returns truthful no-op/not-found state without corrupting defaults/users.
- **RA-52** — role object/capability map is re-read from native authority after another plugin modifies it in same request lifecycle.
- **RA-53** — capability keys are treated as exact stable identifiers; label/case normalization never grants another capability.
- **RA-54** — unknown custom capability remains preserved unless Plan explicitly removes it.
- **RA-55** — primitive capability false/deny semantics are interpreted exactly as WordPress effective-cap behavior.
- **RA-56** — role with zero capabilities is not automatically classified harmless if assignments/dependencies exist.
- **RA-57** — default role configured to deleted/missing role is detected/repaired only through reviewed Plan.
- **RA-58** — core role recreation/version differences are detected rather than assumed identical across WP versions.
- **RA-59** — multisite role storage/site-prefix semantics are read through native APIs, not fabricated table names.
- **RA-60** — custom user-table/prefix environment does not break authority discovery through hardcoded defaults.
- **RA-61** — mapped capabilities (`edit_post`, `delete_user`, etc.) use correct object/context where simulation claims support.
- **RA-62** — custom `map_meta_cap`/`user_has_cap` filters can alter effective result; WPE reports observed effective authority rather than static role-map certainty.
- **RA-63** — plugin-defined dynamic capability filters are not persisted into native role map merely because simulation saw them.
- **RA-64** — authority discovery itself is side-effect free and never “repairs” roles during read/preview.

# 8. User roles, overrides and identity boundaries — RA-65…RA-80
- **RA-65** — user ID target is resolved in trusted site/network context; request coordinate cannot substitute another user/site silently.
- **RA-66** — username/email/display name is never mutable identity authority when user ID/reference exists.
- **RA-67** — user deletion/reassignment race between Plan/apply invalidates stale Plan.
- **RA-68** — adding role to nonexistent/deleted user fails before creating stray metadata.
- **RA-69** — role assignment to user not member of target site follows explicit membership/add-user policy, not silent privilege creation.
- **RA-70** — remove-role from user with no residual role follows documented WordPress/site behavior and recovery analysis.
- **RA-71** — replacing role set preserves explicit individual overrides unless Plan explicitly changes them.
- **RA-72** — explicit user override diff is minimal and does not rewrite unrelated user capabilities.
- **RA-73** — role/cap source provenance distinguishes role grant from individual override for impact/revert.
- **RA-74** — Membership role-sync provenance cannot remove manually/admin/third-party assigned role it does not own.
- **RA-75** — Membership revoke cannot grant fallback WordPress role through unsafe default assumption.
- **RA-76** — imported user/role mapping reauthorizes target identity and does not trust source numeric ID.
- **RA-77** — User Profile field updates never interpret arbitrary meta as role/cap assignment.
- **RA-78** — Application Password/session credential management is separate from Role Manager authority.
- **RA-79** — password-reset/account-security state never becomes role/capability override.
- **RA-80** — anonymization/privacy operation cannot silently transfer privileged role to replacement identity.

# 9. Change Plan fingerprint, diff and concurrency — RA-81…RA-96
- **RA-81** — Plan schema/version is pinned and unknown future Plan schema cannot execute.
- **RA-82** — Plan includes target role/user/native authority fingerprint sufficient to detect relevant drift.
- **RA-83** — unrelated harmless state change does not force destructive broad rewrite; diff remains minimal.
- **RA-84** — two admins create competing Plans; second apply after first mutation detects stale precondition.
- **RA-85** — concurrent add/remove same role/user cannot lose update through stale full-map write.
- **RA-86** — concurrent role capability edits preserve independent additions/removals or conflict explicitly.
- **RA-87** — bulk user-role Plan pins bounded target set/query fingerprint; target drift is surfaced.
- **RA-88** — “all filtered users” bulk action revalidates filter/query/authorization and cannot silently expand after review.
- **RA-89** — dynamic group/Query target cannot be treated as immutable target list without explicit snapshot semantics.
- **RA-90** — Plan summary derives from machine diff, not localized text parser.
- **RA-91** — critical impact count truncation/pagination cannot hide last-recovery-principal risk.
- **RA-92** — Plan cannot be tampered client-side to remove risk/recovery requirement.
- **RA-93** — replay of expired Plan/recent-auth assertion is rejected.
- **RA-94** — Plan actor identity is bound and cannot be transferred to another operator silently.
- **RA-95** — Plan target site/network identity is bound and cannot execute after site transfer without re-plan.
- **RA-96** — policy/version change affecting mutation authorization invalidates/revalidates existing Plan.

# 10. Anti-lockout and recovery-principal depth — RA-97…RA-112
- **RA-97** — recovery-principal classifier uses effective required capabilities/actions, not one hardcoded `manage_options` check only.
- **RA-98** — WPE platform management capability and core WordPress administrator recovery are evaluated separately where needed.
- **RA-99** — a principal denied by current WPE Policy overlay is not counted as sole WPE recovery route if overlay would remain active.
- **RA-100** — WPE recovery mode availability is verified before relying on it as recovery layer.
- **RA-101** — locked/disabled/deleted user is not counted as viable recovery principal merely due stored role.
- **RA-102** — expired session does not make principal nonviable if native login recovery remains legitimately available; viability criteria are explicit.
- **RA-103** — network Super Admin may satisfy site recovery only according to actual core authority and operational reach.
- **RA-104** — site-specific recovery analysis cannot assume a nonmember ordinary administrator from another site has authority.
- **RA-105** — plugin-dependent admin capability is not sole recovery basis if dependency is currently unavailable/degraded.
- **RA-106** — scheduled/batch mutations revalidate recovery before each critical commit, not only at initial Plan time.
- **RA-107** — multiple planned removals evaluated as aggregate final state, preventing sequential last-admin loss.
- **RA-108** — rollback/reverse diff also enforces recovery-principal invariant; revert cannot lock site out.
- **RA-109** — emergency recovery action is audited and cannot suppress native auth logs/history.
- **RA-110** — recovery documentation names native WP-CLI/database-safe repair boundaries without embedding static secret/backdoor.
- **RA-111** — `DISALLOW_FILE_MODS`/host restrictions do not falsely count unavailable recovery channels as guaranteed.
- **RA-112** — anti-lockout false-positive/false-negative fixtures are documented; product does not claim infallible privilege ranking.

# 11. Mutation transaction, partial failure and reconciliation — RA-113…RA-128
- **RA-113** — high-risk apply reauthorizes actor immediately before first native mutation.
- **RA-114** — recent-auth assertion is purpose/scope/actor/time bound.
- **RA-115** — role delete + user reassignment step ordering has explicit partial-failure journal.
- **RA-116** — failure after some user reassignments before role delete reports exact committed subset.
- **RA-117** — failure after role delete before all metadata/audit persistence triggers native re-read/reconciliation.
- **RA-118** — native API returns success but effective state differs due filter/plugin; verification blocks clean success claim.
- **RA-119** — native API returns failure after possible partial external/plugin side effects; result remains reconciliation-required where applicable.
- **RA-120** — duplicate request token/idempotency protects logical operation without assuming SQL transaction around all WP option/usermeta writes.
- **RA-121** — retry after unknown metadata outcome checks native state before any mutation repeat.
- **RA-122** — reverse diff revalidates target identity/role existence/current source fingerprint.
- **RA-123** — snapshot schema/version mismatch blocks blind restore.
- **RA-124** — snapshot excludes credentials/session secrets and stores only minimum authority metadata.
- **RA-125** — corrupt/missing snapshot cannot become reason to bypass current native checks.
- **RA-126** — post-mutation event emits only after authoritative state verification according to event contract.
- **RA-127** — event subscriber failure does not roll back native authority magically; failure remains observable/reconcilable.
- **RA-128** — Audit failure policy for critical authority mutation is explicit; no false “not changed” if native state changed.

# 12. Multisite and Super Admin — RA-129…RA-144
- **RA-129** — network/site IDs are trusted explicit scope identifiers; request-supplied blog ID is reauthorized.
- **RA-130** — Site A admin cannot edit Site B role/user assignments by forged coordinates.
- **RA-131** — site role set and network Super Admin list remain separate native authorities.
- **RA-132** — grant Super Admin action, if exposed, requires exact current core Super Admin authority and dedicated capability/action class.
- **RA-133** — remove Super Admin verifies network recovery principals independently of site roles.
- **RA-134** — Super Admin mutation cannot be performed through generic “add role” path.
- **RA-135** — network activation/deactivation of WPE does not rewrite existing native site roles broadly.
- **RA-136** — new site provisioning uses explicit WPE-owned capabilities/defaults without copying arbitrary source-site user roles.
- **RA-137** — site clone does not copy source site's users/roles as live authority unless clone policy explicitly includes and reauthorizes them.
- **RA-138** — site transfer/network move revalidates users/roles/network authority and does not preserve stale network assumptions.
- **RA-139** — site deletion does not delete global user/Super Admin authority automatically.
- **RA-140** — archived/spam/deleted site state affects mutability/access according to Site Lifecycle, not arbitrary current-blog switch.
- **RA-141** — 100/1k/10k-site bulk impact scan is bounded and does not call unbounded `switch_to_blog()` loops synchronously.
- **RA-142** — network-wide role change, if supported, is explicit paged/Job-driven operation with per-site results.
- **RA-143** — noisy site cannot poison shared network role/cap cache keys for another site.
- **RA-144** — RA Multisite evidence never upgrades MSI/LC certification automatically.

# 13. Cache, consumers and revocation correctness — RA-145…RA-160
- **RA-145** — successful native revoke advances relevant Policy/CAC generation only after authoritative verification.
- **RA-146** — request-local memoized allow is not reused after same-request critical revoke where operation chain requires immediate denial.
- **RA-147** — persistent WPE capability/Policy cache cannot serve stale allow after generation change.
- **RA-148** — object-cache outage cannot preserve stale privileged allow as canonical authority.
- **RA-149** — admin shell/nav visibility refreshes after revoke but hidden menu remains non-authoritative.
- **RA-150** — REST authorization rechecks native/Policy authority after revoke; cached response cannot leak protected data.
- **RA-151** — Dashboard/Listing/Blueprint caches include authorization generation and reauthorize protected data.
- **RA-152** — Membership role-sync change invalidates both Membership-derived and native-capability-dependent access caches as appropriate.
- **RA-153** — Forms/Workflow/Notification consumer action cannot use stale cached role allow after revoke.
- **RA-154** — async Job/Workflow reauthorizes high-risk action at execution time where policy requires current authority.
- **RA-155** — cached deny after legitimate grant follows bounded invalidation and cannot create indefinite lockout.
- **RA-156** — public page/CDN cache cannot contain privileged role-dependent output due app cache mistake.
- **RA-157** — cache key never contains password/session token or other credential.
- **RA-158** — manual cache purge/reset requires authority and does not mutate native role truth.
- **RA-159** — CAC pass does not imply Role Manager revocation semantics pass, and RA pass does not certify generic CAC backend.
- **RA-160** — consumer integration evidence records exact dependent protocol versions/generations.

# 14. Third-party coexistence, versioning and lifecycle — RA-161…RA-168
- **RA-161** — third-party plugin adds/removes caps between WPE versions; VER compatibility/drift is explicit and unrelated caps preserved.
- **RA-162** — plugin deactivation leaves native role/cap metadata according to plugin/WP semantics; WPE does not assume ownership.
- **RA-163** — module disable/Pro expiry stops WPE editing/overlays according to MLC without deleting core/native authority.
- **RA-164** — WPE uninstall cleanup removes only WPE-owned metadata/config and does not purge user roles/caps by default.
- **RA-165** — import/export role configuration never treats source role name/cap map as authority without reviewed target diff/recovery analysis.
- **RA-166** — unknown future role-plan/schema version fails safe and does not execute with dropped constraints.
- **RA-167** — renamed/deprecated WPE capability follows VER migration and least-privilege mapping; old cap is not broad-mapped silently.
- **RA-168** — downgrade/rollback does not silently resurrect deprecated high privilege from stale WPE config/cache.

# 15. Security, privacy, observability and scale — RA-169…RA-176
- **RA-169** — CSRF protection applies to browser mutation; nonce alone never replaces capability/Policy/recent-auth.
- **RA-170** — IDOR attempt against role/user/site/network target is denied with disclosure-safe error.
- **RA-171** — bulk mutation has RLT/Job fairness/backpressure where appropriate without turning limiter allow into authorization.
- **RA-172** — logs/Audit/diagnostics redact passwords, cookies, nonces, reset/session/Application Password secrets and unrelated private user data.
- **RA-173** — privacy export/erase does not remove authorization/Audit facts needed by explicit security/legal retention without owner policy.
- **RA-174** — 100k-user role-impact scan measures queries/memory/time and uses bounded batching without weakening anti-lockout.
- **RA-175** — concurrent/bulk large-network mutation measures conflicts/partial failures/cache invalidation and proves zero wrong-site/Super-Admin grants.
- **RA-176** — final evidence report scopes certification to exact WP/third-party/topology/profile and refuses generic “Role Manager secure/certified” overclaim.

## 16. MUST NOT / stop-the-line gates

Stop affected certification if:
- ordinary WPE UI can commit zero-recovery-principal state;
- Site Admin can grant/remove Super Admin through site role path;
- stale/tampered Change Plan applies silently;
- partial native mutation is reported as full success;
- metadata/event/audit failure causes blind destructive retry;
- reverse restore overwrites newer unrelated native authority state;
- public/anonymous recovery bypass exists;
- revoked capability remains effectively allowed through WPE cache beyond certified correctness profile;
- third-party/core roles/caps are silently destroyed by unrelated WPE operation;
- generic Profile/REST/Import path can mass-assign privileged role/capability;
- WPE parallel authorization database overrides native WordPress truth;
- KPA/CAC/VER/MLC/shared protocol pass is used to claim RA runtime certification.

## 17. Required future evidence report

Include runtime/native/third-party/topology profile; RA-01…RA-176 pass/fail/N/A; effective/meta-cap simulation; Change Plan/concurrency; recovery-principal/self-lockout; native mutation partial/reconciliation/revert; Super Admin/Multisite; cache/consumer revoke; third-party/version/lifecycle; CSRF/IDOR/privacy/Audit; large-user/network performance; certification classes earned; unsupported/degraded profiles.

## 18. Current state

- RA fixtures documented: **176**.
- RA fixtures executed: **0/176**.
- RA runtime certifications: **0**.
- zero-recovery-principal ordinary UI commits permitted by contract: **0**.
- unauthorized Super Admin/network grants permitted by contract: **0**.

No role, capability, user-role, Super Admin, cache-generation, recovery, bulk, Job or Multisite mutation has been executed.

## 19. Development gate

Execution requires explicit owner consent under ADR-0014 and the Approval Ledger.