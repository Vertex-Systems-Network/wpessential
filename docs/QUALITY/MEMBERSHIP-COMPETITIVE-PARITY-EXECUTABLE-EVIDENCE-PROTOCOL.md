# WPEssential — Membership Competitive Parity Exact Executable-Evidence Protocol

Status: **Planning-only exact protocol / NOT EXECUTED**  
Date: 2026-08-29  
Work package: **WP114**  
Namespace: **MPR-001…MPR-176**

## Contract

This protocol expands the fixed MPR groups from `ACCESS-ADMIN-MEDIA-CODE-MARKET-EVIDENCE-MASTER-PLAN.md` into exact individual fixtures. It supplements, but does not replace, the canonical Membership runtime (`MBR`), billing (`MB-F`) or protected-file (`PC-F`) evidence families.

Truth boundaries: User ≠ Role/Capability ≠ Plan ≠ Enrollment ≠ Entitlement ≠ Policy; registration/account creation ≠ verification/approval/enrollment/payment; UI/navigation hiding ≠ authorization; billing/provider state remains external/provider-owned; legacy labels/roles never silently become Membership truth.

Execution status: **176/176 documented; 0/176 executed; runtime certification 0.**

## Group 1 — Site lockdown / public-route exclusions — MPR-001…011
- **MPR-001** — Authenticated-site profile protects ordinary frontend routes while leaving the configured login route reachable; evidence must show route classification plus final Policy decision; stop on redirect loop or login denial.
- **MPR-002** — Lost-password and reset routes remain reachable under lockdown without exposing account existence; evidence must show generic response semantics; stop on enumeration leak.
- **MPR-003** — Registration route is public only when the active Registration Flow permits guest registration; evidence must bind route access to flow revision; stop on accidental open registration.
- **MPR-004** — Email-verification/activation route is reachable only with its own bounded token semantics; evidence must show token scope and no Membership grant before qualification; stop on direct entitlement creation.
- **MPR-005** — Privacy/legal pages explicitly exempted from lockdown remain public while non-exempt sibling pages remain protected; evidence must show resource IDs, not URL-name heuristics.
- **MPR-006** — Health/provider verification endpoints required by declared integrations are exempted only through registered route identities; stop on wildcard/system-route bypass.
- **MPR-007** — Public REST routes remain explicit and protected REST routes still enforce their own Capability/Policy; UI lockdown must not become REST authorization.
- **MPR-008** — Webhook receiver routes bypass site-login redirect only to reach their own authenticated webhook contract; stop if lockdown exemption makes webhook unauthenticated.
- **MPR-009** — Static assets required by login/registration render under lockdown without exposing protected media; evidence must distinguish public asset from protected-file delivery.
- **MPR-010** — Denied-mode selection (401/login, 403/access page, safe redirect, CTA, concealment) produces the configured status/target without open redirect or loop.
- **MPR-011** — Lockdown preflight detects a configuration that would lock every recovery/admin path and blocks publish with a deterministic explanation.

## Group 2 — Registration Flow identity/schema — MPR-012…022
- **MPR-012** — Registration Flow has stable key, immutable revision identity and explicit site scope; duplicate key/import collision is resolved without silent overwrite.
- **MPR-013** — Flow status Draft/Active/Archived controls usability without deleting prior registration evidence.
- **MPR-014** — Identity-field schema rejects password/reset artifacts from generic Form Entry storage and analytics mappings.
- **MPR-015** — Approved custom profile fields bind to registered Field definitions with type/validation metadata; unknown field keys fail closed.
- **MPR-016** — Username strategy (explicit, generated, email-presentation) produces deterministic validation without changing WordPress login authority.
- **MPR-017** — Email-domain allow/deny policy normalizes case/IDN safely and produces a typed rejection reason without storing unnecessary raw inputs.
- **MPR-018** — Terms/privacy acknowledgement stores policy/version/time evidence rather than treating checkbox presence as perpetual legal consent.
- **MPR-019** — Invite-only flow requires a scoped valid invitation artifact; knowing the registration URL alone is insufficient.
- **MPR-020** — Admin-created-only flow rejects unauthenticated self-registration even if a frontend form is rendered by stale cache.
- **MPR-021** — Existing-user enrollment behavior is explicit (reject/link/continue qualification) and cannot create a duplicate WordPress account.
- **MPR-022** — Registration Flow export/import preserves schema semantics without tokens, passwords, provider secrets or live approval artifacts.

## Group 3 — Account creation / native auth boundary — MPR-023…033
- **MPR-023** — WordPress account creation uses native user APIs and records the resulting user identity; direct table insert is not an accepted path.
- **MPR-024** — Duplicate email/username race resolves atomically to one account outcome; second request gets typed conflict, not duplicate identity.
- **MPR-025** — Account creation success does not automatically create Enrollment unless the next qualification step explicitly passes.
- **MPR-026** — Auto-login is permitted only after the configured verified/approved state and native authentication/session creation succeeds.
- **MPR-027** — Rejected/pending registration cannot authenticate merely because a WordPress user row exists in a disabled/pending profile.
- **MPR-028** — Native lost-password/reset behavior remains authoritative; Membership messages may present it but cannot mint reset credentials.
- **MPR-029** — Account-disable/suspend presentation does not substitute for actual authentication/access Policy enforcement.
- **MPR-030** — Role-sync side effect occurs only after account/enrollment transition according to configured mapping and never becomes canonical Membership state.
- **MPR-031** — Crash after WordPress account creation but before registration journal commit is reconciled by user identity/idempotency before retry; no duplicate account.
- **MPR-032** — User deletion during pending registration is detected and registration state becomes explicit invalid/cancelled rather than silently recreating identity.
- **MPR-033** — Registration diagnostics redact password/reset/session data and show only bounded account-creation provenance.

## Group 4 — Email verification — MPR-034…044
- **MPR-034** — Verification artifact is cryptographically random, scoped to registration/user/site/flow revision and stores no reusable plaintext secret.
- **MPR-035** — Verification token expires at configured time and expired use produces generic safe response without account enumeration.
- **MPR-036** — Verification token is one-time or replay-safe; second successful-use attempt cannot repeat downstream Enrollment/Role actions.
- **MPR-037** — Verification link host/scheme is generated from trusted site configuration and cannot be changed by Host-header/open-redirect input.
- **MPR-038** — Resend rotates/invalidates prior token according to profile and is rate-limited per account/IP without disclosing eligibility.
- **MPR-039** — Email delivery accepted/queued is not treated as user verified; verification state changes only on valid token consumption.
- **MPR-040** — Email transport failure leaves registration in truthful verification-pending/delivery-error state and allows bounded retry.
- **MPR-041** — Verification of one site/flow cannot verify another site’s scoped registration in Multisite.
- **MPR-042** — Changing the email address invalidates or rebinds verification according to explicit rule; old-address token cannot verify new identity.
- **MPR-043** — Admin manual verification requires separate capability/reason and produces audit evidence; it is not equivalent to proving mailbox possession.
- **MPR-044** — Verification completion alone does not grant paid entitlement/admin approval when those gates are separately configured.

## Group 5 — Admin approval — MPR-045…055
- **MPR-045** — Approval queue lists only registrations the actor is authorized to review; cross-site/private-field leakage is blocked.
- **MPR-046** — Approve action requires expected registration revision/state to prevent stale double-approval.
- **MPR-047** — Reject action requires configured reason semantics and cannot silently erase the registration evidence unless retention policy permits.
- **MPR-048** — Approver cannot approve their own restricted high-risk registration where separation-of-duties profile forbids it.
- **MPR-049** — Approval transition advances only the next allowed step; it cannot skip required email verification or billing qualification.
- **MPR-050** — Auto-expiry transitions stale approval-pending records without deleting audit/retention evidence.
- **MPR-051** — SLA/reminder scheduling is idempotent and reminder delivery does not change approval state.
- **MPR-052** — Concurrent approve vs reject resolves to one accepted transition with conflict evidence for the loser.
- **MPR-053** — Bulk approval evaluates Policy and expected state per registration; one unauthorized item cannot inherit another item’s authorization.
- **MPR-054** — AI/MCP may summarize queue or draft reasons but cannot invoke approval/rejection without the same explicit governed Ability.
- **MPR-055** — Approval audit records actor, registration identity, prior/new state, reason/reference and flow revision without sensitive field dump.

## Group 6 — Plan selection / enrollment qualification — MPR-056…066
- **MPR-056** — Selectable Plan list is Policy/flow-scoped and excludes archived/ineligible Plans even if client submits their IDs.
- **MPR-057** — Default Plan is a proposal input, not an automatic Enrollment if other qualification gates fail.
- **MPR-058** — Existing user selecting a Plan follows explicit duplicate/parallel/upgrade policy and cannot create unintended concurrent Enrollment.
- **MPR-059** — Free Plan qualification can create Enrollment only after configured verification/approval gates pass.
- **MPR-060** — Paid Plan selection creates billing handoff/pending state, not paid entitlement before provider-confirmed accepted semantics.
- **MPR-061** — Provider checkout success page/query parameter cannot grant Membership without reconciled provider event/state.
- **MPR-062** — Billing timeout/unknown outcome remains pending/unknown and is reconciled before replaying a non-idempotent purchase action.
- **MPR-063** — Enrollment creation pins Plan revision/version according to canonical Membership versioning rules.
- **MPR-064** — Eligibility based on role/domain/invite/custom Policy is evaluated server-side; client-hidden Plan options are not enforcement.
- **MPR-065** — Role-sync mapping runs as a side effect after Enrollment state and cannot back-create Enrollment from arbitrary role assignment unless explicit mapping profile permits.
- **MPR-066** — Qualification explanation distinguishes account, verification, approval, Plan, billing, Enrollment and Entitlement facts in provenance chain.

## Group 7 — Login/register/profile rendering adapters — MPR-067…077
- **MPR-067** — Login block/shortcode/component submits through native auth boundary and does not log passwords in renderer/form telemetry.
- **MPR-068** — Registration adapters render the same canonical Flow revision across block/shortcode/builder outputs or report adapter limitation explicitly.
- **MPR-069** — Lost-password/reset adapter delegates to native reset flow and preserves enumeration-safe responses.
- **MPR-070** — Account/Profile component authorizes field read/write per field Policy; rendering a field does not grant edit authority.
- **MPR-071** — Membership status card reflects canonical Enrollment/Entitlement data and labels provider-pending/unknown states accurately.
- **MPR-072** — Upgrade CTA is presentation only and cannot mutate Enrollment by URL navigation alone.
- **MPR-073** — Logout component uses native nonce/session semantics and safe redirect allowlist.
- **MPR-074** — Theme/builder adapter inherits design tokens where supported and does not hard-code insecure auth markup.
- **MPR-075** — Cached guest login/register output cannot expose a logged-in user’s private membership/profile details.
- **MPR-076** — Disabled adapter/plugin reports degraded rendering without weakening underlying access Policy.
- **MPR-077** — Accessibility evidence covers labels, errors, focus, keyboard flow and non-color state communication for auth/membership components.

## Group 8 — Default restrictions / resource overrides — MPR-078…088
- **MPR-078** — Resource-class default `public` applies only when no explicit override/direct Access Rule supersedes it.
- **MPR-079** — Resource-class default `restricted` compiles to Policy semantics; client-side hiding alone fails evidence.
- **MPR-080** — `concealed` mode returns configured non-disclosing result while authorized principals still resolve the resource.
- **MPR-081** — Per-resource `inherit/public/restricted/concealed` override is stored as explicit state with revision provenance.
- **MPR-082** — Direct Access Rule precedence remains authoritative over convenience defaults and is explainable.
- **MPR-083** — Bulk restriction change previews exact resources and requires Policy per target; stale selection cannot silently broaden scope.
- **MPR-084** — Clearing override restores inherited behavior rather than persisting an accidental public allow.
- **MPR-085** — Post type/taxonomy deletion or rename leaves detectable orphan default bindings rather than applying rules to wrong resources.
- **MPR-086** — Draft/private WordPress publication status composes with Membership Policy and is not made public by membership allow.
- **MPR-087** — REST/feed/search/listing access to restricted resources uses source Policy and cannot leak through alternative presentation surfaces.
- **MPR-088** — Cached restriction decisions include policy/resource/site/version identity and invalidate on relevant Membership/Policy change.

## Group 9 — Teaser / excerpt safety — MPR-089…099
- **MPR-089** — `no teaser` returns no protected body fragment for denied users.
- **MPR-090** — Manual excerpt is a separately authorized/public-safe field or is suppressed when classified protected.
- **MPR-091** — More-marker teaser parser stops before protected dynamic block/token evaluation.
- **MPR-092** — Generated excerpt operates only on approved source projection and bounded length; it cannot render then truncate protected output.
- **MPR-093** — Replacement message/CTA contains no protected values/tokens from denied resource.
- **MPR-094** — Featured image/title/meta exposure obeys explicit teaser profile and protected media/resource Policy.
- **MPR-095** — Safe formatting sanitizer preserves allowed markup and blocks scripts/event handlers/unsafe URLs.
- **MPR-096** — Dynamic Value Resolver denies protected fields before teaser rendering and records denied/redacted state distinct from empty.
- **MPR-097** — Search/index/listing teaser cache cannot retain a previously authorized full excerpt for unauthorized viewers.
- **MPR-098** — AI-generated teaser suggestion is Draft content and cannot infer/reveal protected source fields unavailable to the actor.
- **MPR-099** — Regression fixture verifies teaser behavior through direct URL, archive, search, feed and API adapters without disclosure.

## Group 10 — Navigation visibility vs direct authorization — MPR-100…110
- **MPR-100** — Logged-in/out navigation condition changes visibility only; direct destination access still uses Policy.
- **MPR-101** — Plan/Group/Entitlement navigation condition reads canonical membership facts and never grants them.
- **MPR-102** — Supplemental Role/Capability menu condition cannot override a Membership deny at destination.
- **MPR-103** — Custom Policy navigation condition and destination Policy may differ; diagnostics explain both outcomes without conflating them.
- **MPR-104** — Cached menu output is segmented/invalidation-safe so one user’s membership-visible item does not leak to another.
- **MPR-105** — WordPress Navigation adapter removes/retains nodes without corrupting menu structure or exposing private labels/URLs unexpectedly.
- **MPR-106** — Dashboard navigation adapter obeys same presentation rule and cannot make hidden admin route unauthorized by itself.
- **MPR-107** — Child menu item whose parent is hidden follows explicit promote/hide policy without leaking parent protected context.
- **MPR-108** — Direct crafted request to hidden route/resource is denied by underlying Capability/Policy when appropriate.
- **MPR-109** — AI/MCP menu-recommendation drafts cannot alter access Policy unless a separate governed Policy change is explicitly requested/approved.
- **MPR-110** — Explain view clearly labels `visible`, `hidden`, `authorized`, `denied` as separate facts.

## Group 11 — Messages / dialogs / email composition — MPR-111…121
- **MPR-111** — Login-required message uses renderer tokens from approved context and cannot expose account/member existence.
- **MPR-112** — Registration-success message reflects submitted state, not verified/approved/enrolled state unless those transitions actually occurred.
- **MPR-113** — Verification-sent/expired copy does not reveal whether an arbitrary email belongs to an account in enumeration-sensitive contexts.
- **MPR-114** — Approval pending/approved/rejected messages bind to canonical registration state and revision.
- **MPR-115** — Upgrade-required message/CTA does not claim payment due/paid without canonical Plan/provider facts.
- **MPR-116** — Expired/grace membership message distinguishes Enrollment state from provider billing status.
- **MPR-117** — Password-reset guidance contains no reset token except through native intended delivery path.
- **MPR-118** — Email template rendering uses Email/DVR privacy policy and redacts unavailable protected fields.
- **MPR-119** — Message localization preserves semantic state and cannot change legal/entitlement meaning through fallback.
- **MPR-120** — Failed notification/email delivery does not roll back or fabricate the underlying Membership transition.
- **MPR-121** — Message copy edits are versioned presentation changes and cannot directly modify access/business logic.

## Group 12 — Legacy Members / WP-Members detection and mapping — MPR-122…132
- **MPR-122** — Detector identifies supported legacy plugin/config signature and records version/provenance without executing legacy code.
- **MPR-123** — Missing/inactive legacy plugin with retained data is reported as detected data, not assumed active behavior.
- **MPR-124** — Legacy role restrictions map to explicit candidate Policies/Plans only after human-reviewable mapping; no silent role→Plan truth conversion.
- **MPR-125** — Legacy restricted post/CPT metadata maps to Access Rule/resource override candidates with unresolved values preserved.
- **MPR-126** — Legacy default blocked/unblocked settings map to Restriction Default candidates and show semantic differences/lossiness.
- **MPR-127** — Legacy registration fields map only to compatible User Profile/Field types; unsupported validation becomes unresolved.
- **MPR-128** — Legacy registration form/config maps to Draft Form/Flow and excludes passwords/reset artifacts from generic import.
- **MPR-129** — Legacy email/dialog copy imports as presentation definitions, not executable logic.
- **MPR-130** — Unknown/custom hooks/PHP behavior is reported unsupported/manual-review; it is not converted to executable WPE code.
- **MPR-131** — Detection does not expose secrets/license keys/provider credentials from legacy options into reports/exports.
- **MPR-132** — Coexistence mode identifies overlapping enforcement and warns before both systems can produce redirect/access loops.

## Group 13 — Migration dry-run / replay / recovery — MPR-133…143
- **MPR-133** — Migration Plan pins source fingerprint, mapping revision, destination schema and site scope.
- **MPR-134** — Dry run reports exact/estimated users/resources/rules/fields affected plus unresolved/lossy mappings without mutation.
- **MPR-135** — Duplicate destination Plan/Policy key has explicit create/merge/map/skip conflict action; no silent overwrite.
- **MPR-136** — Applying same migration operation identity is idempotent and does not duplicate Enrollments/Rules.
- **MPR-137** — Same idempotency key with changed mapping fingerprint conflicts instead of replaying different semantics.
- **MPR-138** — Partial batch failure records per-item success/failure/unknown and supports bounded resume.
- **MPR-139** — Crash after destination commit reconciles by source/destination identity before retry.
- **MPR-140** — Rollback capability is labelled accurately: configuration rollback does not promise reversal of external billing/email side effects.
- **MPR-141** — Legacy plugin disable/removal remains a separate reviewed step after verification, never part of implicit import success.
- **MPR-142** — Post-migration verification compares effective access on representative principals/resources, not only row counts.
- **MPR-143** — Migration report preserves provenance and unresolved items without storing unnecessary protected source payloads.

## Group 14 — Abuse / privacy / retention — MPR-144…154
- **MPR-144** — Registration endpoint has configured rate limit keyed without unbounded PII storage and returns non-enumerating errors.
- **MPR-145** — CAPTCHA/spam adapter outcome is advisory/gate input per profile and cannot become identity proof unless explicitly contracted.
- **MPR-146** — Pending registration field collection follows minimization/classification and rejects unneeded protected fields.
- **MPR-147** — Rejected/expired registration retention follows configured purpose/period and scheduled deletion is owner-aware/idempotent.
- **MPR-148** — Privacy export returns only authorized subject data and distinguishes membership facts from provider-held billing data.
- **MPR-149** — Erasure request respects legal/retention holds and does not claim provider deletion until confirmed by owning provider.
- **MPR-150** — Audit/logs redact verification/rescue/reset tokens, passwords, secrets and unnecessary field payloads.
- **MPR-151** — Registration diagnostics/export use CSV/formula safety and do not expose hidden protected columns by default.
- **MPR-152** — Brute-force verification/resend attempts trigger bounded throttling without revealing eligible account state.
- **MPR-153** — Cached denial/registration pages do not contain another user’s email/name/membership state.
- **MPR-154** — Security regression pack covers CSRF, forged flow/Plan IDs, stale nonce, replay and direct endpoint bypass.

## Group 15 — Multisite / network / user-identity boundaries — MPR-155…165
- **MPR-155** — Registration Flow is site-owned by default and request-supplied site ID cannot switch owner scope.
- **MPR-156** — Network-global WordPress user identity is distinguished from site Membership Enrollment.
- **MPR-157** — Existing network user joining another site follows explicit add-to-site/enrollment semantics without duplicate global user.
- **MPR-158** — Network template instantiation creates site-scoped Flow identities/revisions and does not share live pending registrations.
- **MPR-159** — Site Access Profile cannot lock Network Admin/Super Admin recovery routes through ordinary site authority.
- **MPR-160** — Super Admin status is never created/removed by Membership role-sync or registration flow.
- **MPR-161** — Network Membership profile, if configured, requires explicit certified scope; site Membership is not promoted automatically.
- **MPR-162** — Cross-site navigation/directory queries reauthorize each destination/resource and do not leak membership/private profiles.
- **MPR-163** — Site clone copies definitions only according to profile and quarantines verification/invite/live Enrollment artifacts by default.
- **MPR-164** — Site deletion suspends/archives site-owned pending registrations/enrollments per lifecycle without deleting network user identity incorrectly.
- **MPR-165** — Cache/idempotency/token keys include site/network namespace so identical IDs across sites cannot collide.

## Group 16 — Scale / regression / coexistence — MPR-166…176
- **MPR-166** — Evidence profile for 10k pending registrations measures queue/list/filter behavior without claiming runtime result before execution.
- **MPR-167** — Evidence profile for 100k users and membership-filtered directory defines query/index/memory budgets and privacy-safe pagination.
- **MPR-168** — Concurrent registration burst fixture covers duplicate email, rate limit, verification token generation and idempotent account creation.
- **MPR-169** — Large restriction-default change fixture defines batch preview, Policy recheck, cache invalidation and rollback class.
- **MPR-170** — Coexistence with legacy Members-style role restriction detects double-deny/double-redirect and reports owner of each decision.
- **MPR-171** — Coexistence with WP-Members-style registration/restriction detects duplicate forms/hooks and blocks unsafe simultaneous canonical ownership.
- **MPR-172** — Theme/cache/CDN regression fixture verifies login/register/access-denied pages remain functional and non-leaking under full-page cache profiles.
- **MPR-173** — WordPress/plugin version drift fixture requires compatibility detection before enabling legacy migration/presentation adapters.
- **MPR-174** — Failure of Email/Workflow/Policy dependency yields explicit degraded state without defaulting resource access open.
- **MPR-175** — AI adversarial fixture requests self-approval, admin Plan/role grant or migration execution; expected result is governed refusal/draft-only path.
- **MPR-176** — Golden end-to-end planning fixture covers register → verify → approve → qualify → enroll → direct resource Policy → navigation/teaser presentation, with each truth transition separately evidenced.

## Completion truth

`MPR-001…MPR-176` are **176/176 documented and 0/176 executed**. This is an exact planning protocol only. It does not certify Membership runtime, billing providers, protected-file delivery, email transport, WordPress authentication or any migration adapter.