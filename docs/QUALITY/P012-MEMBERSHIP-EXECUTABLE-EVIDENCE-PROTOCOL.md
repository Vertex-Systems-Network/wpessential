# WPEssential — P-012 Membership Runtime / Access / Protected Files / Provider Executable Evidence Protocol

Status: **Phase 0 fixed executable-evidence contract / execution NOT AUTHORIZED**  
Work package: `P0-M00-WP12`  
Related: ADR-0013, ADR-0015, ADR-0016, ADR-0019, ADR-0020, ADR-0024, ADR-0057, ADR-0062, ADR-0066, ADR-0078, ADR-0090, ADR-0014, P-003/JS, P-005/VT, P-007/CI, P-013 Backup, Chat CH protocol.

## 1. Purpose

Prove that WPEssential Membership can make deterministic, revocation-safe access decisions across Plans, Enrollments, Entitlements, teams, files and external billing sources without collapsing WordPress roles, billing provider state or WPE Product License into Membership authority.

This is a future evidence contract only. It creates no Membership tables, Enrollment records, entitlements, team seats, protected files, provider hooks, webhooks, cache entries, Jobs or runtime tests.

## 2. Non-negotiable domain separation

These concepts remain distinct:

1. WordPress User — identity.
2. WordPress Role/Capability — WordPress authorization primitive.
3. Membership Plan + immutable published revision — access product definition.
4. Membership Enrollment — authoritative local lifecycle interval.
5. Billing Subscription/Purchase source — external commercial fact.
6. Entitlement — normalized derived/current benefit grant.
7. Membership Access Rule/Policy — resource/action requirements and exclusions.
8. Team/Seat — Membership capacity/access delegation.
9. WPE Product Entitlement — WPEssential's own commercial license, never member access.

Provider events never directly authorize protected resources. Product licensing never becomes Membership authorization. WordPress role mutation never creates/revokes paid Membership truth unless a separately explicit workflow is configured.

## 3. Preserved access semantics

- outer WordPress/WPE security denial cannot be overridden by Membership;
- Membership protection is opt-in unless explicit site-wide default deny exists;
- resource + subresource/action specificity is deterministic;
- same-specificity explicit deny wins;
- multiple valid memberships union valid entitlements subject to exclusions;
- manual `force_allow` only bypasses Membership requirements, never outer security;
- `force_deny` outranks same-scope allow;
- access cache is never authority;
- committed revoke/hard deny must not survive stale cache;
- ordinary access hot path performs no provider API call;
- timestamp expiry is enforced even if Cron/Job execution is late;
- diagnostics are explainable but safely redacted.

## 4. Preserved Enrollment lifecycle

Canonical states:
`pending`, `trialing`, `active`, `grace`, `paused`, `expired`, `revoked`.

`expired` and `revoked` are terminal by default; later access creates a new Enrollment interval. Cancellation is intent/effective-time data, not an Enrollment state. Provider-specific statuses remain source facts.

## 5. Physical/delivery baselines

Membership storage:
- **M1 — PT-D shared scoped runtime** first benchmark baseline;
- **M2 — PT-E per-site runtime** mandatory comparison.

Protected assets:
- **PD1** private local + PHP streaming correctness baseline;
- **PD2** server-accelerated private local delivery after capability evidence;
- **PD3** private object storage + bounded signed delivery;
- **PD4** future provider-specific stronger-revocation profile only with separate evidence.

Protected-file certification remains **PC0–PC4**. A page/button restriction is not file protection if origin bytes remain publicly reachable.

## 6. Execution prerequisites

Before any MBR fixture runs:

- explicit P-012 execution consent under ADR-0014;
- disposable test sites/networks and recoverable snapshots;
- accepted/explicitly scoped compatibility and build/runtime test environment;
- exact Membership schema/profile revision recorded;
- provider fixtures use sandbox/test accounts only where separately authorized;
- Vault/protected-storage prerequisites are available for provider/file tests;
- no production member, billing, file, credential or account data is used.

Unavailable prerequisites yield **NOT EXECUTED** or **INCONCLUSIVE**, not simulated PASS.

# 7. Fixed evidence matrix — MBR-01…MBR-160

## A. Plan identity, publication and revision behavior — MBR-01…MBR-12

- **MBR-01** Draft Plan creation has no live access effect.
- **MBR-02** access-affecting publish creates immutable revision under stable Plan identity.
- **MBR-03** label-only edit does not silently rename entitlement key.
- **MBR-04** entitlement-key removal/rename requires dependency/migration handling.
- **MBR-05** `follow_current_plan` updates eligible existing + future Enrollment behavior as declared.
- **MBR-06** `new_enrollments_only` preserves grandfathered assigned revision.
- **MBR-07** `scheduled_global_change` is ineffective before timestamp and effective at/after timestamp even when Job runs late.
- **MBR-08** material publish preflight identifies affected members, removed benefits, seat impact and rebuild scope.
- **MBR-09** concurrent Plan publish attempts resolve deterministically; one stale revision cannot silently overwrite another.
- **MBR-10** archived Plan with history remains referentially valid and cannot be hard-deleted through ordinary flow.
- **MBR-11** imported/migrated Plan revision preserves explicit source/effective semantics rather than rewriting historical billing facts.
- **MBR-12** access diagnostics identify the effective Plan/revision without exposing unrelated member/provider data.

## B. Enrollment lifecycle, transitions and time — MBR-13…MBR-32

- **MBR-13** valid `pending→trialing` transition.
- **MBR-14** valid `pending→active` transition.
- **MBR-15** valid `trialing→active` transition.
- **MBR-16** valid `active→grace` transition.
- **MBR-17** valid `active→paused` transition.
- **MBR-18** valid eligible-state→expired transition.
- **MBR-19** valid eligible-state→revoked transition.
- **MBR-20** forbidden transition is atomically rejected with no side effect.
- **MBR-21** `expired` does not mutate back to active; authorized rejoin creates new Enrollment linked to predecessor.
- **MBR-22** `revoked` is not resurrected by later ordinary billing webhook/event.
- **MBR-23** duplicate source event does not duplicate Enrollment transition or side effects.
- **MBR-24** out-of-order source facts enter reconciliation rather than blind older transition.
- **MBR-25** cancellation-at-period-end preserves access until effective end according to policy.
- **MBR-26** late Cron/Job does not extend expired access beyond authoritative timestamp.
- **MBR-27** grace expiry denies at request time even before cleanup Job.
- **MBR-28** payment recovery during grace returns to active only through valid current-source reconciliation.
- **MBR-29** manual/free Enrollment uses same canonical lifecycle and audit model.
- **MBR-30** concurrent same-subject/Plan creation obeys uniqueness/group rules and cannot duplicate effective Enrollment.
- **MBR-31** state commit remains authoritative when non-critical notification/webhook side effect fails afterward.
- **MBR-32** UTC authoritative timestamps + site/user display timezone do not alter eligibility at DST boundaries.

## C. Entitlement derivation, access precedence and explainability — MBR-33…MBR-52

- **MBR-33** pending Enrollment grants no normal entitlement.
- **MBR-34** trialing grants declared trial/full benefits only.
- **MBR-35** active grants normal Plan benefits.
- **MBR-36** grace grants configured grace benefit set only.
- **MBR-37** paused grants no ordinary benefits unless explicit retained benefit exists.
- **MBR-38** expired/revoked grant no Enrollment-derived entitlement.
- **MBR-39** multiple compatible memberships union valid entitlements.
- **MBR-40** outer WordPress capability/security deny cannot be bypassed by Membership allow.
- **MBR-41** exact-resource rule beats lower-specificity entity/site rule.
- **MBR-42** exact subresource/download/action rule beats whole-resource default.
- **MBR-43** same-effective-specificity explicit deny wins.
- **MBR-44** `ANY` requirement evaluates only defined group semantics.
- **MBR-45** `ALL` requirement evaluates only defined group semantics.
- **MBR-46** `NONE` requirement denies when excluded entitlement is present.
- **MBR-47** manual `force_allow` requires high-risk capability/audit and cannot bypass outer denial.
- **MBR-48** manual `force_deny` immediately defeats same-scope allow.
- **MBR-49** administrator role name alone confers no Membership bypass.
- **MBR-50** dedicated bypass capability is explicit, auditable and still subject to outer security.
- **MBR-51** no applicable Membership rule means Membership itself does not impose a hidden deny.
- **MBR-52** explainability trace identifies winning rule/requirements/reason safely without exposing hidden policy/provider secrets to unauthorized callers.

## D. Cache, access generation, revoke latency and hot path — MBR-53…MBR-64

- **MBR-53** active→revoked committed transition invalidates prior allow before next protected access decision.
- **MBR-54** force-deny invalidates prior allow immediately under accepted write boundary.
- **MBR-55** Plan revision removing benefit invalidates affected principals without relying on stale TTL expiry.
- **MBR-56** team-seat revoke invalidates seat-derived access.
- **MBR-57** persistent object-cache unavailable/degraded still produces correct authorization from durable truth.
- **MBR-58** stale object-cache entry with old access generation cannot authorize after revoke.
- **MBR-59** cache key includes site/network/principal/relevant policy/access generation to prevent collision.
- **MBR-60** ordinary access check makes no billing/provider API call.
- **MBR-61** timestamp expiration remains correct when cache contains prior eligible result.
- **MBR-62** large entitlement rebuild has resumable/reconcilable progress without temporary permissive default.
- **MBR-63** cache flush/rebuild failure fails safely rather than making protected resources public.
- **MBR-64** measured hot-path DB/query/cache cost is bounded for representative multiple-membership/rule fixtures.

## E. Plan Groups, team seats, invitations and ownership — MBR-65…MBR-80

- **MBR-65** exclusive Plan Group blocks incompatible simultaneous eligible Enrollment.
- **MBR-66** concurrent final exclusive-group transition cannot produce double effective grant.
- **MBR-67** upgrade/cross-grade avoids unintended access gap/double grant at effective boundary.
- **MBR-68** trial reuse policy is preserved across rejoin/cross-grade rather than silently resetting.
- **MBR-69** one canonical Team owner exists at a time.
- **MBR-70** owner transfer is authorized/audited and does not silently transfer provider billing ownership.
- **MBR-71** invitation token is high-entropy, single-use, expiring and stored hashed at rest.
- **MBR-72** expired/revoked invitation cannot be accepted.
- **MBR-73** final-seat concurrent acceptance cannot overbook capacity.
- **MBR-74** reserved invitation consumes/releases capacity exactly according to configured policy.
- **MBR-75** lowering seat limit below use creates over-capacity without arbitrary eviction.
- **MBR-76** owner active/trial/eligible-grace state enables seat-derived eligibility.
- **MBR-77** owner pause suspends seat-derived access without deleting unrelated member state.
- **MBR-78** owner expire/revoke terminates seat-derived access with revoke-safe invalidation.
- **MBR-79** removing Team member revokes only team/seat-derived grants, not unrelated Membership or WP account.
- **MBR-80** wrong-team/wrong-site member/seat/invite identifiers cannot cross authorization boundaries.

## F. WordPress role synchronization and provenance — MBR-81…MBR-92

- **MBR-81** Membership functions fully with role sync disabled.
- **MBR-82** enabling role sync creates explicit provenance/claim records for WPE-added role claims.
- **MBR-83** multiple WPE sources can share one role claim without premature removal.
- **MBR-84** pre-existing/manual role is not claimed exclusively by WPE.
- **MBR-85** WPE removes a mapped role only when provenance proves no active claim and it was not pre-existing.
- **MBR-86** ambiguous provenance retains role + reconciliation warning rather than destructive removal.
- **MBR-87** manual/external WP role mutation does not create a Membership Enrollment.
- **MBR-88** manual/external role removal does not silently revoke paid Membership truth.
- **MBR-89** role-sync failure does not roll back valid Enrollment/Entitlement transition.
- **MBR-90** administrator/Super-Admin-equivalent mappings are blocked by default.
- **MBR-91** any future high-risk privileged mapping path requires reauth + anti-lockout + explicit authority.
- **MBR-92** site/network role-sync claims do not cross Multisite scope or cause privilege escalation.

## G. Billing source facts, providers and reconciliation — MBR-93…MBR-112

- **MBR-93** Manual/Free source creates canonical local Enrollment without fake payment object.
- **MBR-94** duplicate manual grant identity does not duplicate effective Enrollment.
- **MBR-95** WooCommerce one-time grant uses supported paid/payment-complete semantics, not `Completed` string alone.
- **MBR-96** Woo pending/on-hold creation without paid truth does not grant paid entitlement.
- **MBR-97** Woo partial refund maps through explicit policy and does not assume full revocation.
- **MBR-98** Woo order storage mode differences use supported APIs and are version/capability certified.
- **MBR-99** Woo Subscriptions `pending-cancel` records cancellation intent/paid-through truth, not immediate revoke.
- **MBR-100** Woo renewal failure/on-hold maps through configured grace/pause policy rather than permanent direct revoke.
- **MBR-101** Woo successful renewal reconciliation updates period/source truth idempotently.
- **MBR-102** Woo `cancelled` vs `expired` remain distinct provider facts before WPE policy mapping.
- **MBR-103** scheduler stall/late provider task is not misclassified as payment failure.
- **MBR-104** SureCart signature/timestamp/replay validation occurs before source-fact dispatch.
- **MBR-105** duplicate SureCart webhook is deduped.
- **MBR-106** out-of-order SureCart events trigger current-object reconciliation.
- **MBR-107** SureCart `set_to_cancel`/`cancel_at_period_end` does not equal terminal canceled access.
- **MBR-108** SureCart test/live identities and mappings cannot cross.
- **MBR-109** refund/purchase-switch facts do not account-wide revoke unrelated current Enrollment without policy basis.
- **MBR-110** provider→WP-user identity ambiguity fails to reconciliation/manual resolution rather than assigning the wrong user.
- **MBR-111** provider outage/webhook loss reconciliation feeds the same canonical transition engine, never a second authorization path.
- **MBR-112** BE3 static provider documentation does not claim MB0–MB5; public support level is bounded by executed provider certification evidence.

## H. Protected assets / files — MBR-113…MBR-132

- **MBR-113** PD1 protected file bytes are absent from unauthenticated public-origin path.
- **MBR-114** page/button/shortcode hiding alone fails certification if origin URL remains reachable.
- **MBR-115** every new protected-download initiation reauthorizes current outer security + Membership Policy.
- **MBR-116** revoked member cannot start a new download after committed revoke.
- **MBR-117** expired member cannot start a new download even if expiry Job is late.
- **MBR-118** force-denied member cannot start a new download.
- **MBR-119** wrong-site asset/reference/path is denied.
- **MBR-120** path traversal/symlink/alternate-extension/original-file bypass attempts cannot reach protected bytes.
- **MBR-121** Range requests preserve authorization and correct bounded transfer semantics.
- **MBR-122** content-disposition/type/cache headers do not expose private file through shared public cache.
- **MBR-123** interrupted PD1 transfer does not change authorization truth or corrupt source.
- **MBR-124** PD2 acceleration is enabled only after server capability/config/origin isolation evidence.
- **MBR-125** PD2 internal redirect cannot be forged directly by unauthenticated client.
- **MBR-126** PD3 signed URL has bounded TTL and is issued only after current authorization.
- **MBR-127** product wording/test acknowledges already-issued PD3 bearer URL cannot promise instant revoke beyond provider semantics.
- **MBR-128** signed URL/token does not reveal Vault/storage credentials.
- **MBR-129** public→private migration copies/moves bytes and verifies old public origin no longer bypasses protection.
- **MBR-130** Backup/Restore preserves or re-establishes private origin controls before asset is marked protected/available.
- **MBR-131** clone/staging does not silently expose production private origin or issue production signed URLs without current policy.
- **MBR-132** PC0–PC4 certification records exact storage/delivery/provider/server profile; one profile's PC level does not certify another.

## I. Privacy, user deletion, lifecycle, restore and audit — MBR-133…MBR-144

- **MBR-133** current derived Entitlements are rebuildable current-state data rather than indefinite duplicate history.
- **MBR-134** raw provider webhook/payload retention is minimized/off by default according to accepted profile.
- **MBR-135** successful protected-download detailed logging is off by default.
- **MBR-136** IP/device logging is off by default unless explicitly enabled for stated purpose.
- **MBR-137** terminal invitations become cleanup-eligible without deleting active/reconcilable access evidence.
- **MBR-138** WordPress privacy exporter returns authorized/bounded Membership categories and no secrets/card data.
- **MBR-139** eraser supports delete/anonymize/retain-with-reason semantics without blindly destroying active authorization/business relationship.
- **MBR-140** user deletion runs impact resolution for Enrollment/team ownership/seats/current grants before destructive action.
- **MBR-141** site archive/suspend behavior preserves data while access/provider tasks follow lifecycle policy.
- **MBR-142** site delete does not erase network-owned/shared truth or leak another site's Membership data.
- **MBR-143** Restore that reintroduces older member/PII/access state runs post-restore authorization/privacy reconciliation before normal operation.
- **MBR-144** Audit records transitions/overrides/team/provider reconciliation using safe references and no card/Vault/plaintext provider secrets.

## J. Physical topology, Multisite, concurrency and scale — MBR-145…MBR-160

- **MBR-145** M1/PT-D wrong-site Enrollment lookup is impossible under scoped query/authorization path.
- **MBR-146** M1/PT-D cache/access-generation keys cannot collide across sites.
- **MBR-147** M1/PT-D noisy-neighbor workload does not create cross-site authorization failure.
- **MBR-148** M2/PT-E provisioning creates correct per-site runtime without missing security invariants.
- **MBR-149** M2/PT-E site deletion/restore handles table lifecycle safely.
- **MBR-150** M1↔M2 migration preserves Enrollment IDs/history/effective grants/team references and denies during ambiguous partial cutover.
- **MBR-151** 100k-user / 200k+ Enrollment / 1M Entitlement representative workload measures hot-path query/latency/memory.
- **MBR-152** multiple memberships per principal keep access query count bounded.
- **MBR-153** 10k rule/resource evaluation remains bounded or cost-guarded.
- **MBR-154** 50k-member Plan benefit removal/rebuild cannot leave revoked benefit available from stale generation.
- **MBR-155** mass expiry/revoke workload remains correct under Job lag/backpressure.
- **MBR-156** final-seat and exclusive-group high-concurrency tests show zero overbooking/double-effective grants.
- **MBR-157** MySQL and MariaDB candidate query plans/indexes for Enrollment/Entitlement/access-generation/team lookups are captured.
- **MBR-158** 100/1k/10k-site provisioning/migration/lifecycle behavior compares M1 vs M2.
- **MBR-159** Backup extraction/restore for M1 and M2 preserves scope and requires post-restore reconciliation before stale access resurrection.
- **MBR-160** independent final adversarial review confirms no known path allows stale revoked access, direct provider authorization, wrong-site/team access, protected-origin bypass, seat overbooking or role-sync privilege escalation.

## 8. Required result record

Every executed fixture records:
- MBR ID;
- exact code/artifact revision;
- WordPress/PHP/DB/object-cache/site-mode environment;
- Membership storage profile M1/M2;
- Plan/Enrollment/policy revision IDs as safe fixture references;
- protected-delivery profile/PC level where applicable;
- provider adapter/profile/version/BE+MB state where applicable;
- expected vs actual authorization/state/side effect;
- query/cache/latency evidence where relevant;
- security/privacy/redaction result;
- recovery/reconciliation result;
- PASS / FAIL / INCONCLUSIVE / NOT EXECUTED;
- linked defect/evidence artifact.

## 9. Stop-the-line failures

Stop P-012 certification on any confirmed case of:

- committed revoke/expiry/force-deny still allows a new protected action because of stale cache;
- provider webhook/status directly authorizes or revokes protected resource without canonical reconciliation/policy transition;
- outer security denial is bypassed by Membership;
- wrong-site/team/user IDOR produces access;
- exclusive-group concurrency creates simultaneous prohibited effective memberships;
- seat acceptance race overbooks capacity;
- role sync creates Administrator/Super-Admin-equivalent escalation or removes ambiguous pre-existing role;
- direct/public origin path bypasses a claimed protected-file profile;
- file path traversal/private cache leak exposes bytes;
- Restore/clone resurrects stale access or production private provider use without reconciliation;
- product license state becomes member authorization;
- card data/Vault plaintext/provider secrets appear in Membership persistence/log/export/diagnostics;
- unknown provider/event/order state defaults to permissive access.

## 10. Certification boundaries

A passing P-012 does not automatically certify:
- any provider at MB0–MB5;
- protected delivery at PC1–PC4 beyond the exact tested profile;
- Vault;
- JobService;
- Notification/Email;
- Chat;
- Backup;
- WordPress Role Manager;
- Product License;
- P-001 compatibility or build/CI.

Provider and protected-file certifications remain profile/version/environment scoped.

## 11. Current evidence state

- MBR fixtures documented: **160**
- MBR fixtures executed: **0/160**
- Membership runtime certifications: **0**
- M1/M2 physical benchmarks executed: **0**
- Membership billing provider profiles: **4 BE3 paper profiles / 0 MB-certified**
- Protected-file certifications: **0 PC1+**
- independent P-012 security review executed: **NO**

## 12. Development gate

This protocol authorizes **no** Membership table/schema/migration, Plan/Enrollment/Entitlement write, cache, Job, role mutation, invitation/token, protected-file move/download, storage/provider call, WooCommerce/SureCart install/hook/webhook/API operation, privacy erasure/export execution, benchmark or test.

ADR-0014 explicit scoped owner consent remains mandatory.