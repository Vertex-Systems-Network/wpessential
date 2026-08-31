# WPEssential — Role & Capability Competitive Parity Exact Executable-Evidence Protocol

Status: **Planning-only exact protocol / NOT EXECUTED**  
Date: 2026-08-29  
Work package: **WP114**  
Namespace: **RPR-001…RPR-176**

## Contract

This protocol expands the fixed RPR groups into exact individual fixtures. It supplements, but never replaces, canonical WordPress capability/meta-cap behavior, WPE Policy, or the existing `RA` runtime evidence family.

Truth boundaries: role name/label ≠ authority; UI/menu hiding ≠ authorization; generic `edit_users` ≠ right to administer every target role; Super Admin ≠ ordinary role; rescue ≠ normal role edit; simulation ≠ impersonation; AI/MCP has no privilege path.

Execution status: **176/176 documented; 0/176 executed; runtime certification 0.**

## Group 1 — Role Administration Policy identity — RPR-001…011
- **RPR-001** — Role Administration Policy has stable key, immutable revision, owner/site scope and explicit subject selector.
- **RPR-002** — Policy target-role actions are independently represented for list, create-user-into, assign, remove, bulk, profile edit, individual caps, role edit, delete, clone and import/replace.
- **RPR-003** — `below-my-tier` preset resolves through declared hierarchy metadata and does not infer Administrator/Super Admin power from display names.
- **RPR-004** — Selected-target-role preset denies unlisted role IDs even if client submits them directly.
- **RPR-005** — Exclusion preset wins according to explicit precedence and explanation shows why target is denied.
- **RPR-006** — User-specific administration Policy and role-based Policy compose deterministically without accidental privilege union.
- **RPR-007** — Policy revision change invalidates target-role decision cache and stale forms cannot reuse old authority.
- **RPR-008** — Deleted/renamed role reference becomes unresolved Policy dependency, not retargeted by label similarity.
- **RPR-009** — Import/export preserves stable role identifiers/mapping intent and never imports active authorization without preview.
- **RPR-010** — Policy editor itself requires separate capability and cannot let an operator expand their own administer-target set when self-escalation is forbidden.
- **RPR-011** — Explain view distinguishes native capability, meta-cap result, Role Administration Policy and final mutation permission.

## Group 2 — List/assign/remove/edit target-role enforcement — RPR-012…022
- **RPR-012** — Users list may hide unauthorized target-role rows/counts only as presentation; direct user fetch/edit endpoint still enforces Policy.
- **RPR-013** — Assign-role request checks actor, target user, destination role and site at commit time.
- **RPR-014** — Remove-role request checks whether actor may administer the role being removed, including last-role/lockout implications.
- **RPR-015** — Replace-roles action evaluates both removed and added roles, never authorizing replacement only from destination check.
- **RPR-016** — Edit-profile permission is separate from ability to change target user roles/capabilities.
- **RPR-017** — Individual capability edit requires target-user administration right plus high-risk capability validation.
- **RPR-018** — Actor cannot administer a target user merely because they share one low-privilege role if target also holds protected role.
- **RPR-019** — Target-role policy is rechecked after concurrent role change before mutation commit.
- **RPR-020** — Unauthorized role ID tampered into form/REST payload returns typed denial without leaking hidden role details unnecessarily.
- **RPR-021** — Bulk role action authorizes every selected user/role pair independently and reports partial denied items truthfully.
- **RPR-022** — Audit records actor, target user, prior/new role set, Policy revision and reason without exposing unrelated profile data.

## Group 3 — Users/Add User/Edit User/bulk parity — RPR-023…033
- **RPR-023** — Add User screen lists only assignable roles but server rejects hidden roles submitted manually.
- **RPR-024** — New network user vs add-existing-user-to-site semantics are distinguished in Multisite.
- **RPR-025** — Edit User role selector cannot remove a protected role the actor is not allowed to administer.
- **RPR-026** — User with no role is visible/actionable only according to user-list Policy, not assumed harmless.
- **RPR-027** — Multiple-role user display preserves complete effective role set for authorized viewer and redacts where policy requires.
- **RPR-028** — Individual-capability override indicator does not expose sensitive capability names to unauthorized operators.
- **RPR-029** — Administrator-equivalent effective access detection uses capabilities/Policy evidence, not role-name match.
- **RPR-030** — Bulk add-role operation is idempotent for already-present role and does not duplicate side effects.
- **RPR-031** — Bulk clear-individual-capabilities requires high-risk preview and cannot erase unknown overrides silently.
- **RPR-032** — Default-role change impact is previewed separately from existing user role mutation.
- **RPR-033** — Users screen filters cannot be abused to enumerate protected roles/users beyond actor visibility scope.

## Group 4 — REST / Ability / Workflow parity — RPR-034…044
- **RPR-034** — REST role assignment endpoint enforces same Role Administration Policy as wp-admin UI.
- **RPR-035** — REST user update cannot smuggle role/capability changes through generic profile fields.
- **RPR-036** — Ability to assign/remove role requires typed target user/role/site and server-resolved Policy; request scope IDs are not authority.
- **RPR-037** — Workflow role action runs under attributed principal/service Policy and cannot inherit creator’s unlimited future authority.
- **RPR-038** — Import job role mutation uses same target-role checks per item; import permission alone is insufficient.
- **RPR-039** — MCP exposure of role-management Abilities is opt-in and high-risk mutation remains excluded/default-denied.
- **RPR-040** — AI-generated role diff is Draft/proposal and cannot invoke mutation merely because output contains valid role IDs.
- **RPR-041** — CSRF/nonces protect browser mutation but nonce possession alone does not satisfy Capability/Policy.
- **RPR-042** — REST application-password/service principal gets only its mapped capabilities/Policy and no browser-session rescue privilege.
- **RPR-043** — Workflow retry is idempotent and expected role version prevents duplicate/overwriting newer role state.
- **RPR-044** — Direct low-level Ability invocation produces same denial/explanation as UI path for forbidden target role.

## Group 5 — Rescue token eligibility/generation — RPR-045…055
- **RPR-045** — Rescue eligibility defaults to built-in Administrator recovery principal on single site and never arbitrary admin-like role by name.
- **RPR-046** — Multisite rescue eligibility distinguishes site Administrator from Super Admin and cannot mutate network super-admin status through site path.
- **RPR-047** — Custom recovery principal requires explicit configuration and separate privilege to manage that configuration.
- **RPR-048** — Rescue request response is generic whether account/email is eligible, absent or blocked.
- **RPR-049** — Token is cryptographically random, scoped to principal/site/recovery profile and stores only safe verifier/hash.
- **RPR-050** — Token expiry is short/configured and recorded with server time semantics; expired token cannot be revived by client time.
- **RPR-051** — Token delivery address is taken from canonical account identity, not request-supplied alternate email.
- **RPR-052** — Rescue request is rate-limited per account/IP/site with privacy-safe counters.
- **RPR-053** — Email transport accepted/queued does not mark rescue complete or token consumed.
- **RPR-054** — Generating a new token invalidates prior token according to profile and prevents parallel replay.
- **RPR-055** — Raw rescue token never appears in logs, audit, analytics, URLs beyond intended one-time delivery or export artifacts.

## Group 6 — Rescue replay/rate/enumeration/recovery — RPR-056…066
- **RPR-056** — Valid rescue token can be consumed once; second attempt is denied with generic response.
- **RPR-057** — Concurrent double-consumption is atomic: only one transition succeeds.
- **RPR-058** — Rescue restores only documented minimal safe recovery set/built-in role, not arbitrary requested role/caps.
- **RPR-059** — Recovery does not silently preserve dangerous individual denies/overrides that would make rescue ineffective; outcome is previewed/explained by profile.
- **RPR-060** — Recovery does not remove unrelated legitimate roles/caps unless explicit safe profile says so.
- **RPR-061** — Rescue request/consume endpoints resist account enumeration through status, timing/message detail within practical evidence bounds.
- **RPR-062** — Rate-limit exhaustion cannot be bypassed with casing/alias/site-ID manipulation.
- **RPR-063** — Token remains bound to original site/environment and fails after clone/environment mismatch unless explicitly reissued.
- **RPR-064** — Password reset and role rescue remain separate artifacts; one token cannot substitute for the other.
- **RPR-065** — Successful rescue revokes token, emits bounded audit/notification and prompts role-drift review without exposing token.
- **RPR-066** — AI/MCP has no default rescue-request or consume Ability and cannot ask system to reveal eligible recovery accounts.

## Group 7 — Capability provenance / orphan handling — RPR-067…077
- **RPR-067** — Capability Registry records key, primitive/meta/unknown classification and source/provider provenance when known.
- **RPR-068** — First/last observed timestamps are observational metadata, not proof provider still owns capability.
- **RPR-069** — Registry reports roles/users carrying capability without granting viewer unauthorized access to those users.
- **RPR-070** — CPT/tax/WPE/plugin references bind by registered source identities, not string similarity alone.
- **RPR-071** — Provider inactive/missing marks capability as orphan candidate, not safe-to-delete certainty.
- **RPR-072** — Unknown capability cannot be auto-deleted from roles/users merely because no current provider is detected.
- **RPR-073** — Remove-orphan Plan shows exact roles/users/effective-access changes and requires per-target authorization.
- **RPR-074** — Removing primitive capability does not assume corresponding meta-cap result without explain evaluation.
- **RPR-075** — Reappearing provider/capability reconciles provenance without silently restoring previously removed grants.
- **RPR-076** — Registry import/export excludes sensitive user data and preserves unknown classification truthfully.
- **RPR-077** — Capability-name collision across plugins/providers is represented as ambiguous provenance rather than invented ownership.

## Group 8 — Role diff/snapshot/rollback — RPR-078…088
- **RPR-078** — Role snapshot pins role key, capability set, explicit deny semantics if supported, source/site and revision fingerprint.
- **RPR-079** — Snapshot comparison distinguishes added/removed capabilities and role metadata changes.
- **RPR-080** — Effective-access diff does not equate raw capability diff with meta-cap/Policy outcome.
- **RPR-081** — Snapshot before destructive role edit is durable according to configured retention and excludes unrelated user secrets.
- **RPR-082** — Rollback preview checks current role drift; stale rollback cannot overwrite newer edits silently.
- **RPR-083** — Rollback of role definition does not claim rollback of actions users already performed while capability existed.
- **RPR-084** — Deleted role rollback handles key collision with explicit restore/rename/map choice.
- **RPR-085** — Default-role rollback is separated from restoring role capability definition.
- **RPR-086** — Network role-template rollback does not automatically mutate every site unless explicit target Plan is approved.
- **RPR-087** — Snapshot/export import from different WP/plugin profile reports unknown capabilities/lossiness before apply.
- **RPR-088** — Audit chain links change Plan, snapshot, applied revision and rollback outcome without implying immutable legal record.

## Group 9 — Admin/menu/widget/editor-feature delegation — RPR-089…099
- **RPR-089** — Admin Menu delegation stores presentation Policy link while destination screen Capability/Policy remains owner-enforced.
- **RPR-090** — Toolbar visibility delegation cannot grant/revoke destination action by itself.
- **RPR-091** — Dashboard Widget visibility uses owning widget Policy and hidden widget data is not embedded client-side for denied user.
- **RPR-092** — Meta box/editor panel visibility never substitutes for save/update capability enforcement.
- **RPR-093** — Profile field visibility/editability delegates to User Profile field Policy; Role Manager does not duplicate field ACL engine.
- **RPR-094** — Frontend navigation delegation is presentation-only and direct resource Policy remains authoritative.
- **RPR-095** — Form availability/fields delegate to Forms Policy and cannot infer submission authority from visibility.
- **RPR-096** — Plugin-management UI hiding does not weaken native plugin operation capability checks.
- **RPR-097** — WPE module screen/Ability delegation composes KPA/Policy and cannot create parallel authorization store.
- **RPR-098** — Deleting delegated visibility rule restores owner default rather than implicit allow/deny outside owner contract.
- **RPR-099** — Explain view labels presentation restriction vs actual operation authorization separately.

## Group 10 — Object-level Policy delegation — RPR-100…110
- **RPR-100** — Guided builder subject may be user/role but compiles to canonical Policy definition, not synthetic capability explosion.
- **RPR-101** — Post-type resource scope resolves registered type identity and rejects spoofed type keys.
- **RPR-102** — Explicit-record scope pins object IDs/site and reauthorizes object existence/visibility at evaluation.
- **RPR-103** — Author-based scope composes ownership relation and does not assume author can perform every action on owned object.
- **RPR-104** — Taxonomy-term scope maps through canonical resource adapter and handles deleted term as unresolved dependency.
- **RPR-105** — Ownership-relation scope uses registered Relation/Policy source, not arbitrary SQL.
- **RPR-106** — Read/edit/delete/publish actions remain distinct; allow one action cannot imply others.
- **RPR-107** — Explicit deny/allow precedence is deterministic and explainable across role/user/resource Policies.
- **RPR-108** — Bulk Policy generation previews affected resource classes and prevents accidental network-wide wildcard.
- **RPR-109** — Policy change invalidates relevant permission caches and stale editor session cannot reuse old decision indefinitely.
- **RPR-110** — AI may draft object Policy but cannot publish access broadening without governed Policy update approval.

## Group 11 — Plugin/form integration boundaries — RPR-111…121
- **RPR-111** — Plugin view/activate/deactivate granularity is exposed only where WordPress/provider adapter can enforce it safely.
- **RPR-112** — Install/update/delete plugin operations remain separately privileged even if actor may activate selected plugins.
- **RPR-113** — Network-active plugin operations require Network Admin/Super Admin Policy and cannot be delegated by site role alone.
- **RPR-114** — Plugin dependency relationship blocks/degrades unsafe deactivate/delete Plan according to WordPress capability profile.
- **RPR-115** — Unknown third-party plugin operation granularity is reported unsupported, not approximated through UI hiding.
- **RPR-116** — Forms role/capability conditions remain input/presentation logic unless submission/action Policy separately grants mutation.
- **RPR-117** — Workflow action using role checks resolves effective Policy at run time and records attributed principal.
- **RPR-118** — Builder/widget integration cannot expose protected capability data or bypass destination operation Policy.
- **RPR-119** — Membership role-sync remains side effect mapping; role change does not become canonical membership entitlement.
- **RPR-120** — Importer from competitor role plugin normalizes through WPE preview and never executes embedded callbacks/code.
- **RPR-121** — Integration unavailable/disabled yields explicit degraded state rather than silently allowing operation.

## Group 12 — Effective capability explain / meta-cap mapping — RPR-122…132
- **RPR-122** — “Can user do X?” evaluates current user/site/resource through canonical WordPress/meta-cap + Policy chain.
- **RPR-123** — “Who can do X?” query is access-controlled and bounded; it cannot enumerate protected users to unauthorized viewers.
- **RPR-124** — Explanation shows role-derived primitive capabilities separately from individual overrides.
- **RPR-125** — Meta-cap mapping identifies underlying primitive requirements/result without pretending raw role list is final authority.
- **RPR-126** — Super Admin short-circuit/WordPress network semantics are represented explicitly and not as ordinary role grant.
- **RPR-127** — WPE Policy deny/grant is shown as separate layer from native capability result.
- **RPR-128** — Object ownership/post status/context can alter meta-cap result and explanation includes those inputs without leaking protected object fields.
- **RPR-129** — Stale capability cache invalidates on role/user/Policy revision changes.
- **RPR-130** — Simulation against hypothetical role change is no-write and clearly labelled not live authority.
- **RPR-131** — “Which change would grant/remove access?” is recommendation only and cannot mutate roles automatically.
- **RPR-132** — Explain output is redacted according to viewer Policy and does not expose secret policies/user attributes unnecessarily.

## Group 13 — Import/export/migration — RPR-133…143
- **RPR-133** — Import Plan pins source format/version/hash, site targets and role mapping decisions.
- **RPR-134** — Create conflict action fails on existing role key unless explicit rename/map choice is made.
- **RPR-135** — Merge imports only declared capability/metadata semantics and does not silently delete destination-only grants.
- **RPR-136** — Replace shows destructive capability/user/default-role/Policy impact before apply.
- **RPR-137** — Rename preserves provenance and remaps selected user/Policy references only through reviewed Plan.
- **RPR-138** — Unknown capability names import as unknown provenance, not trusted safe capabilities.
- **RPR-139** — Administrator-equivalent imported role triggers high-risk review based on effective capabilities, not name.
- **RPR-140** — Competitor export containing unsupported data/callbacks is lossily mapped with unresolved items, never executed.
- **RPR-141** — Migration is idempotent by operation identity/source fingerprint and retry cannot duplicate role assignments.
- **RPR-142** — Partial migration records per-role/user outcome and supports reconcile/resume without pretending atomic all-success.
- **RPR-143** — Export contains configuration/evidence only and excludes password/session/tokens/secrets/unrelated PII.

## Group 14 — Multisite role template/sync/Super Admin — RPR-144…154
- **RPR-144** — Site-only role mutation is scoped to resolved site and cannot touch same role key on another site.
- **RPR-145** — Network role template is a definition/template, not automatically live on every site.
- **RPR-146** — Template instantiate/sync Plan lists exact target sites and per-site diff before apply.
- **RPR-147** — Linked/enforced network role mode, if supported, has explicit network authority and cannot be edited by ordinary site admin.
- **RPR-148** — Drift comparison distinguishes missing role, capability diff, local override and unsupported provider capability.
- **RPR-149** — New-site bootstrap uses current approved template revision and does not copy live user assignments by default.
- **RPR-150** — Site lifecycle deletion removes site role bindings per Core semantics without deleting global users incorrectly.
- **RPR-151** — Super Admin membership is never represented/imported/synchronized as ordinary role checkbox.
- **RPR-152** — Network sync cannot remove last recovery principal or lock all admins without stop-the-line anti-lockout check.
- **RPR-153** — Cross-site bulk role change authorizes actor per target site/network and preserves per-site outcome.
- **RPR-154** — Cache/idempotency keys include site/network namespace to prevent same role key collisions across sites.

## Group 15 — Anti-lockout / privilege escalation / concurrency — RPR-155…165
- **RPR-155** — Change that would remove actor’s last required management capability triggers preview/reauth/stop according to anti-lockout profile.
- **RPR-156** — Deleting last Administrator-equivalent recovery role/principal is blocked or requires documented rescue-safe procedure.
- **RPR-157** — Actor cannot assign role with capabilities beyond permitted target-role Policy via crafted REST/Ability/bulk/import path.
- **RPR-158** — Actor cannot edit target role definition to add forbidden capability then assign it indirectly.
- **RPR-159** — Capability escalation through individual user override is checked independently of role assignment permission.
- **RPR-160** — Concurrent role edits use revision/precondition and stale writer cannot overwrite newer capability set silently.
- **RPR-161** — Concurrent user role replacement vs deletion resolves with explicit conflict/partial state, not corrupted assignments.
- **RPR-162** — Default-role changes cannot silently promote future registrations to privileged role without high-risk validation.
- **RPR-163** — Forged role key/name/case/serialized payload cannot bypass validated registry identifiers.
- **RPR-164** — Emergency rescue remains separately audited and cannot be invoked as generic anti-lockout bypass by unauthorized user.
- **RPR-165** — Security regression covers CSRF, stale nonce, direct endpoint, race, replay, IDOR and role-name spoofing.

## Group 16 — Large user/role networks / regression — RPR-166…176
- **RPR-166** — Evidence profile for 100k users defines paginated user/role filters and explain-query budgets without preclaiming performance.
- **RPR-167** — Evidence profile for 1k roles/capability sets defines Role Administration Policy evaluation/cache budgets.
- **RPR-168** — Evidence profile for 1k-site network role drift scan defines per-site fairness, backpressure and resumable job state.
- **RPR-169** — Bulk 10k-user role mutation fixture defines batch size, expected-version checks, audit aggregation and partial failure truth.
- **RPR-170** — Role/capability plugin coexistence fixture verifies WPE does not overwrite competitor-managed roles merely because they are observed.
- **RPR-171** — WordPress version drift fixture revalidates native capability/meta-cap/Super Admin semantics before enabling certified behavior.
- **RPR-172** — Plugin disable/uninstall fixture preserves WordPress roles unless explicit owner cleanup Plan exists.
- **RPR-173** — Cache/object-cache outage falls back to authoritative checks rather than stale allow decisions.
- **RPR-174** — AI adversarial fixture asks to grant Administrator/Super Admin, bypass target-role Policy or expose rescue accounts; expected path is governed refusal/draft only.
- **RPR-175** — Golden end-to-end fixture covers operator role policy → Users UI → REST/Ability → target mutation → effective explain → audit with identical decision semantics.
- **RPR-176** — Golden recovery fixture covers enumeration-safe request → eligible token generation → one-time consume → minimal recovery → token revoke/audit, with no Super Admin or unrelated capability escalation.

## Completion truth

`RPR-001…RPR-176` are **176/176 documented and 0/176 executed**. This protocol does not certify canonical `RA` runtime, WordPress permission behavior, email transport, rescue delivery or any third-party role migration adapter.