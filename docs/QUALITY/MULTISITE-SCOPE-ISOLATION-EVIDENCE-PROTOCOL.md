# WPEssential — Multisite Scope & Isolation Executable Evidence Protocol

Status: **Phase 0 fixed evidence contract / execution NOT AUTHORIZED**  
Date: 2026-08-28  
Refinement work package: `P0-M00-WP24`  
Related: ADR-0014, ADR-0069, ADR-0071, ADR-0075, Multisite scope/ownership architecture, `MULTISITE-SCOPE-OPTION-MATRIX.md`, Site Lifecycle protocol, P-001/P-003/P-004/P-005/P-006/P-009/P-010/P-012/P-013.

## 1. Goal

Define fixed executable evidence before WPEssential can claim WordPress Multisite support for any module/platform surface.

Passing network activation, rendering Network Admin, or storing `site_id` is not certification.

The canonical fixture set is now **MSI-01…MSI-160**. Legacy named fixture families (`MS-SCOPE-*`, `MS-AUTH-*`, `MS-SWITCH-*`, `MS-SET-*`, etc.) are preserved semantically inside the fixed ranges below and are superseded as execution identifiers by MSI IDs.

No fixture has been executed.

## 2. Evidence levels retained

- **MS0 — Static Compatible:** explicit scope/ownership and no known architectural contradiction; no runtime claim.
- **MS1 — Activation & Site Isolation:** activation/routes/ordinary site data/definitions isolated; basic cross-site IDOR denied.
- **MS2 — Scope Runtime Certified:** switching, caches, jobs, inheritance, lifecycle, shared-user/site-role semantics proven.
- **MS3 — Cross-Site / Network Operations Certified:** only for intentional network operations; bounded fan-out, network templates, shared resources, partial-failure recovery proven.
- **MS4 — Large-Network & Disaster Certified:** scale, version skew, disaster/restore, long-running fan-out and operational recovery proven.

A module that intentionally has no network-operation feature may be fully supported at its documented MS2 scope. Certification remains per-surface/profile/version.

## 3. Standard future topology

After explicit authorization only:

- single-site control;
- Multisite subdirectory network with asymmetric users/roles and at least four child sites;
- Multisite subdomain network where lab DNS permits;
- object cache off/on where supported;
- accepted P-001 WP/PHP/DB profiles;
- Free-only, matched Free+Pro and version-skew profiles;
- staged synthetic 100 / 1,000 / 10,000-site profiles only where environment supports them.

Actors include Super Admin, network-capable WPE admin, Site A admin, Site B admin, same global user with different site roles, network user with no target-site membership, one-site member/subscriber and anonymous visitor.

## 4. Result model

Each MSI fixture reports `PASS`, `FAIL`, `BLOCKED`, `NOT_EXECUTED` or `INCONCLUSIVE` with pinned environment/scope/dependency evidence. Uncertified dependent modules produce BLOCKED, not optimistic success.

---

# 5. Fixed matrix — MSI-01…MSI-160

## A. Scope identity, topology and activation — MSI-01…MSI-16

- **MSI-01** Same logical definition key/label on Site A and Site B retains distinct explicit ownership and CRUD isolation.
- **MSI-02** Network-owned resource has one network owner; site-visible inherited/use state never masquerades as local ownership.
- **MSI-03** Durable target scope is independent from current request/blog context when created/read from Network Admin or another site.
- **MSI-04** Missing/invalid network-site coordinates fail closed; no fallback to current site.
- **MSI-05** Stable WPE site identity is not hostname-only and survives supported domain/path metadata change.
- **MSI-06** Deleted/recreated WordPress numeric blog ID does not inherit old WPE identity/state automatically.
- **MSI-07** Network ID, site/blog ID, installation ID, WPE site UUID and Product Allocation ID remain distinct identifiers.
- **MSI-08** Per-site activation cannot silently become network activation; network activation cannot silently enable every child surface.
- **MSI-09** Network-active Free+Pro package boot respects child-site entitlement/module state independently.
- **MSI-10** Per-site plugin activation/deactivation profile behaves according to supported installation mode without cross-site data mutation.
- **MSI-11** Subdirectory topology resolves route/site URLs to exact target site without path bleed.
- **MSI-12** Subdomain topology resolves route/site URLs and cookies without sibling-domain authority leakage.
- **MSI-13** Mapped/custom domain metadata cannot change durable ownership or authorize cross-site access.
- **MSI-14** Archived/spam/deleted site coordinates are recognized as lifecycle state, not silently treated as an active target.
- **MSI-15** Network resource selected-site applicability cannot target a site outside its network by forged coordinates.
- **MSI-16** Single-site control proves Multisite abstractions do not introduce broken scope assumptions in normal WordPress.

## B. Authorization, IDOR and blog-context restoration — MSI-17…MSI-32

- **MSI-17** Site A admin direct-reads Site B WPE UUID: denied without unsafe existence leakage.
- **MSI-18** Site A admin direct-updates Site B resource by forged site/network ID: denied before mutation.
- **MSI-19** Site A admin direct-deletes Site B resource: denied before impact plan/journal creation.
- **MSI-20** Ordinary site admin accesses Network Admin WPE route by known URL: denied server-side.
- **MSI-21** Network-capable WPE admin lacking a specific high-risk ability cannot rely on broad Network Admin access as bypass.
- **MSI-22** Super Admin still passes WPE Policy, high-risk confirmation and audit contracts where defined.
- **MSI-23** Same global WP user with different A/B roles receives target-site effective capabilities only.
- **MSI-24** User belonging to no target site cannot acquire site authority merely because user account is global to network.
- **MSI-25** REST/Ability `site_id` tampering cannot escalate target scope.
- **MSI-26** Nonce/capability valid on Site A cannot authorize Site B mutation without target-site evaluation.
- **MSI-27** Balanced `switch_to_blog(A→B→restore)` restores original blog/cache context.
- **MSI-28** Nested A→B→C switch stack restores in correct order.
- **MSI-29** Exception/fatal-style controlled failure within target operation restores context or records dirty-context failure without proceeding elsewhere.
- **MSI-30** Target site missing a theme/plugin integration does not assume `switch_to_blog()` loads code that is unavailable.
- **MSI-31** Reused worker executing sequential jobs for different sites clears site-specific context between attempts.
- **MSI-32** Long request that changes current site/capability midway re-resolves authorization at the sensitive operation boundary.

## C. Settings, definitions, inheritance and caches — MSI-33…MSI-48

- **MSI-33** Network default with no site override resolves effective value with network provenance.
- **MSI-34** Allowed site override affects only target site and records override provenance.
- **MSI-35** Network-enforced/locked value cannot be changed by site UI, REST, Ability or owning supported action.
- **MSI-36** Removing site override returns to inheritance without copying network value as site ownership.
- **MSI-37** Network default update changes only inheriting sites; explicit overrides remain.
- **MSI-38** Network template Draft never changes live child sites.
- **MSI-39** Network published template instantiates/links only selected eligible sites under declared propagation mode.
- **MSI-40** Linked/follow-current vs pinned/copied revision semantics remain distinct and deterministic.
- **MSI-41** One target-site definition/key/slug conflict is isolated and reported; other target sites can follow declared partial-failure policy.
- **MSI-42** Network template deletion/unlink follows keep-copy/revert/block policy and never erases site runtime data by implication.
- **MSI-43** Cache of Site A authorization/value cannot serve Site B equivalent resource.
- **MSI-44** Object-cache enabled repeated site switching preserves correct Definition/Settings/Query/Policy state.
- **MSI-45** Network policy/default generation invalidates only affected inherited site caches as declared.
- **MSI-46** Site-specific mutation invalidates its site cache without flushing unrelated sites unnecessarily.
- **MSI-47** Membership/access revoke on A invalidates A authorization cache within certified bound while B remains independent.
- **MSI-48** Restored/stale cache cannot override newer network/site generations or resurrect removed authority.

## D. Jobs, schedules, workflows, events and fan-out — MSI-49…MSI-64

- **MSI-49** Site A Job enqueued from Network Admin/current Site B records A as durable target.
- **MSI-50** Site archived/deleted before Job executes causes typed skip/cancel/fail/reconcile; never wrong-site fallback.
- **MSI-51** Worker claims Site A job then current context changes: execution still validates durable A scope.
- **MSI-52** Worker crash during site switch/attempt cannot leak context into next site's job after recovery.
- **MSI-53** Network coordinator enumerates sites in bounded pages and creates bounded child work rather than one unbounded request.
- **MSI-54** One repeatedly failing target site does not prevent unrelated site children completing.
- **MSI-55** Aggregate network operation truthfully reports partial/failed/unknown children.
- **MSI-56** Retry only repeats eligible child operation identities; no duplicate external/site side effects.
- **MSI-57** Mixed urgency across sites honors JobService fairness and avoids sustained noisy-neighbor starvation.
- **MSI-58** Per-site concurrency/resource key prevents two unsafe mutations on same site while allowing safe work elsewhere.
- **MSI-59** Network Cron coordinator excludes archived/spam/deleted sites according to policy.
- **MSI-60** Site-local calendar/timezone schedule is evaluated in target site's accepted timezone semantics, not coordinator/current-site timezone.
- **MSI-61** Waiting Workflow wakes after target site restriction/deletion and re-checks lifecycle/Policy before side effect.
- **MSI-62** Provider/Webhook event correlated to Site A cannot mutate Site B similarly keyed resource.
- **MSI-63** Duplicate network event/fan-out request reuses logical operation identity and does not duplicate child side effects.
- **MSI-64** Permission/site-membership changes while a long coordinator runs are re-evaluated where required before each sensitive child action.

## E. Global users, site roles, Profiles and Membership — MSI-65…MSI-80

- **MSI-65** Site-specific custom profile value change affects only target site's owned value/layout.
- **MSI-66** Global email/password/session action is explicitly labeled network-account impact and uses protected WordPress identity flow.
- **MSI-67** Site A role add/remove leaves Site B role/capability membership unchanged.
- **MSI-68** Role edit cannot remove last recovery-capable authority without anti-lockout process.
- **MSI-69** Site removal from user is distinct from deleting global WP user.
- **MSI-70** Network/Super Admin role-like mutations use separate high-risk authority from ordinary site roles.
- **MSI-71** Active Membership Plan on Site A grants no Site B protected resource by default.
- **MSI-72** Site A Membership role-sync changes only A and preserves unrelated manually assigned B roles.
- **MSI-73** Shared billing/connection provider fact maps to exact target site Enrollment/Plan, not network-wide grant.
- **MSI-74** Site archive/removal revokes/blocks local Membership access per lifecycle while preserving global user unless separately deleted.
- **MSI-75** Membership protected asset token/link created for A cannot access analogous B asset.
- **MSI-76** Site B user preference/notification state does not control A unless explicit network preference contract exists.
- **MSI-77** User privacy export distinguishes site-owned profile/membership data from global identity/network-owned records.
- **MSI-78** User erasure on one site cannot silently delete network/global identity or another site's retained business records.
- **MSI-79** Future network Membership profile remains unavailable/uncertified unless separately implemented and P-012 certified.
- **MSI-80** Network clone/transfer does not infer Membership entitlement from shared numeric user ID alone.

## F. Vault, Connections, providers and private assets — MSI-81…MSI-96

- **MSI-81** Site A private Vault secret reference from B is denied before plaintext resolution.
- **MSI-82** Network-shared connection can delegate use-right to A/B without revealing plaintext secret.
- **MSI-83** Revoke B use-right leaves A and network credential intact.
- **MSI-84** Site export/clone carries placeholders/reference semantics, never network secret plaintext.
- **MSI-85** Child-site admin cannot inspect Network Account/OAuth/Vault credentials from platform/diagnostics UI.
- **MSI-86** Shared connection operation records target site correlation for audit/rate/usage policy.
- **MSI-87** Per-site provider quota/rate limit is isolated where provider profile supports delegated limits.
- **MSI-88** Site deletion removes/revokes use-right without deleting shared credential or breaking siblings.
- **MSI-89** Inbound Event Inbox resolves trusted target site before business action; payload-supplied arbitrary site ID is not authority.
- **MSI-90** Outbound email/webhook from A uses A-authorized sender/connection/template context and cannot borrow B by name collision.
- **MSI-91** Private Support/Chat/Media asset for A cannot download through B context.
- **MSI-92** Signed/private asset URL is scoped/short-lived according to owning profile and not logged as reusable network secret.
- **MSI-93** Provider unknown outcome remains scoped to exact site operation and reconciliation cannot mutate sibling operation.
- **MSI-94** Network connection rotation invalidates/reconciles authorized children without exposing new secret.
- **MSI-95** Site-specific connection override and inherited network connection show provenance and separate revocation semantics.
- **MSI-96** Staging clone does not blindly keep production OAuth/provider/webhook credentials active.

## G. Query, Relations, Listings, REST, Abilities and Import — MSI-97…MSI-112

- **MSI-97** Normal site Query cannot select arbitrary other-site source.
- **MSI-98** Explicit network aggregate Query authorizes each target site before inclusion.
- **MSI-99** Network aggregate max-site/result/cost budget prevents unbounded fan-out.
- **MSI-100** Network aggregate merge/sort/page/count semantics do not leak unauthorized rows/site counts.
- **MSI-101** Ordinary Relation cannot create cross-site edge by default.
- **MSI-102** Any future cross-site Relation requires explicit coordinates, both endpoint Policies and lifecycle/orphan certification.
- **MSI-103** Listing/template shared from network evaluates target-site Query/Policy and site-scoped cache at render time.
- **MSI-104** Same Listing cache inputs on A/B cannot share protected result artifact unless resource is genuinely network/public by contract.
- **MSI-105** Site REST endpoint cannot turn into network endpoint through `site_id` request parameter.
- **MSI-106** Network REST endpoint is separate privileged route/Ability with bounded target fan-out.
- **MSI-107** AI/CLI invoking cross-site Ability receives no privileged bypass beyond normal target-scope Policy.
- **MSI-108** Site import package cannot overwrite network-owned Definition/Settings/Vault resource without explicit network plan/authority.
- **MSI-109** Network import dry-run enumerates target-site remaps/conflicts before mutation.
- **MSI-110** Imported UUID/site mapping collision is explicit; no numeric-site-ID blind remap.
- **MSI-111** Site clone/import never assumes network-shared dependency exists in destination network.
- **MSI-112** Export of site config records network dependencies by safe reference/provenance and excludes secrets.

## H. Backup, Reset, operations and protection — MSI-113…MSI-128

- **MSI-113** Site Backup manifest contains exactly target-site artifacts plus declared shared references.
- **MSI-114** Selected-sites Backup pins explicit site identities and reports partial child failures.
- **MSI-115** Network Backup explicitly includes selected network/global resources and per-site artifacts according to profile.
- **MSI-116** Single-site provider C3/C4 evidence does not automatically certify network restore.
- **MSI-117** Same-site restore does not corrupt sibling data/cache/jobs/settings.
- **MSI-118** Site-to-new-site restore remaps site/domain/UUID/dependency identities explicitly.
- **MSI-119** Full-network disaster restore requires separate MS4/P-013 profile and preserves source/target mapping truth.
- **MSI-120** Site Reset cannot affect shared users/network options/sibling sites by default.
- **MSI-121** Network Reset is separate extreme-risk action with Super Admin/WPE Policy, inventory, journal and verified recovery point.
- **MSI-122** Protector network security floor cannot be weakened by site override when policy is enforced.
- **MSI-123** XML-RPC/network-wide security policy UI does not promise site-local isolation impossible at installation-level endpoint.
- **MSI-124** Watermark/media site derivative ownership stays site-scoped despite shared filesystem/object storage.
- **MSI-125** Dashboard/Admin Menu site rules cannot hide/rearrange Network Admin by implication.
- **MSI-126** Network Dashboard Widget cannot expose aggregate/private data to unauthorized site admin.
- **MSI-127** Per-site deactivate vs network deactivate vs uninstall remain distinct and non-destructive by default.
- **MSI-128** Uninstall cleanup plan scopes each storage class and never bulk-deletes sibling/network resources without explicit ownership evidence.

## I. Site lifecycle, clone, migration, transfer and disaster — MSI-129…MSI-144

- **MSI-129** New-site initialization applies network defaults/templates according to new-site policy exactly once.
- **MSI-130** Duplicate/replayed site-created event is idempotent across Definitions/Jobs/License/Connections.
- **MSI-131** Partial provisioning failure records truthful Partial/Blocked state and supports scoped retry.
- **MSI-132** Archive/spam/restricted transition pauses/blocks domain operations according to registered lifecycle policy.
- **MSI-133** Reactivation revalidates module policy, entitlement, Jobs, cache, Membership and provider bindings before resume.
- **MSI-134** Site deletion that bypasses ideal WPE preflight creates degraded/orphan reconciliation state, never false orderly success.
- **MSI-135** Active Job/Workflow/queued side effect overlapping deletion re-checks lifecycle before mutation/send.
- **MSI-136** Site deletion does not cancel external billing subscription merely by implication.
- **MSI-137** Site deletion/release unknown remote allocation outcome stays pending/ambiguous with idempotent reconciliation.
- **MSI-138** Staging clone is detected/classified without silently duplicating production allocation or production side effects.
- **MSI-139** Unknown production clone enters review/revalidation and preserves safe deployed output rules.
- **MSI-140** Temporary migration overlap has one explicit side-effect/commercial authority and deterministic completion.
- **MSI-141** Network-to-network transfer remaps data, roles, network dependencies, Vault/Connections and allocation explicitly.
- **MSI-142** Disaster restore with stale entitlement/jobs/provider/cache/lifecycle state revalidates each authority before resuming.
- **MSI-143** Site ID/domain/path changes preserve stable WPE identity only where continuity is verified.
- **MSI-144** Lifecycle/privacy erasure overlap respects domain retention rather than treating site deletion as universal erasure.

## J. Scale, failure injection, observability and certification truth — MSI-145…MSI-160

- **MSI-145** 100-site coordinator measures pagination/enqueue/query/memory budgets and never uses one unbounded interactive loop.
- **MSI-146** 1,000-site synthetic profile records queue growth, fairness, cache invalidation and admin listing performance.
- **MSI-147** 10,000-site profile, if executed, produces environment-specific measured limits rather than generic marketing claim.
- **MSI-148** One child DB failure remains isolated and aggregate state is truthful.
- **MSI-149** Object-cache outage does not cause cross-site scope fallback or stale authorization reuse.
- **MSI-150** Network option write conflict/stale ETag-like mutation surfaces conflict instead of silent overwrite.
- **MSI-151** Vault locked/unavailable produces scoped failure and cannot fall back to plaintext/shared unsafe credential.
- **MSI-152** Free/Pro version mismatch follows FP degraded-safe boot instead of fatal or destructive lifecycle action.
- **MSI-153** Network template rollout conflict isolates failed target and records enough diagnostics to resume/review.
- **MSI-154** Restore interruption resumes/checkpoints without sibling-site corruption or duplicated remote side effects.
- **MSI-155** Audit record includes actor, originating context, target network/site and operation correlation without secret payload.
- **MSI-156** Diagnostics distinguish unsupported, not configured, unavailable, failed and uncertified Multisite capability.
- **MSI-157** Public support label names MS level and surface/profile/version; MS0 never presented as runtime Multisite certification.
- **MSI-158** Protocol dependency failures remain BLOCKED/NOT CERTIFIED and are not converted to MSI pass by mocks lacking owning semantics.
- **MSI-159** Retest triggers include material WordPress Multisite API, storage topology, cache, Job, Policy, Free/Pro or provider changes.
- **MSI-160** Final report enumerates unsupported network operations and measured limits; no `supports all Multisite` blanket claim from partial evidence.

---

## 6. Evidence artifact per run

Record:
- fixture ID;
- WPE commit/build and Free/Pro pair;
- WordPress/PHP/DB versions;
- topology/subdomain-subdirectory/object-cache profile;
- actor/origin/target network-site identities using safe references;
- module/provider versions;
- prerequisite evidence/certification;
- starting state;
- expected/observed behavior;
- affected resource counts and cache/job/audit correlations;
- final state;
- security assertions;
- performance metrics where applicable;
- `PASS/FAIL/BLOCKED/NOT_EXECUTED/INCONCLUSIVE`;
- resulting MS0–MS4 class per claimed surface.

Certification is profile/version scoped and can expire/downgrade.

## 7. Critical failure / stop-the-line

Stop certification on:
- wrong-site data read/write/delete;
- target-scope authorization bypass;
- context leakage into another site's worker/request;
- stale cross-site authorization cache;
- shared secret exposure/deletion harming unrelated site;
- duplicate production allocation/side effect from retry/clone;
- external billing cancellation from implicit site deletion;
- protected Membership content becoming public because site/product state changed;
- network import/reset/restore mutating unselected site/network data;
- lifecycle run reporting success with required unknown/failed child;
- irrecoverable destructive operation where verified recovery was required.

## 8. Current state

- fixed MSI fixtures documented: **160**;
- executed: **0/160**;
- **31/31 product/platform surfaces have static Multisite scope mapping**;
- **0 surfaces runtime-certified at MS1+**;
- MS0–MS4 runtime certification remains evidence-gated;
- Site Lifecycle LC protocol is independent but required where lifecycle behavior is claimed;
- development authorization remains **NOT GRANTED**.

## 9. Development gate

Do not create networks, install/activate packages, execute site creation/deletion, switch-context runtime tests, run queues/workflows, mutate schemas/data, call Product License/providers, perform Backup/Restore/Reset/Import, or run scale benchmarks until explicit owner consent under ADR-0014 is granted and recorded.