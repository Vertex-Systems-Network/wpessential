# WPEssential — Shared Rate Limit & Abuse Control Executable Evidence Protocol

Status: **Phase 0 evidence specification / EXECUTION NOT AUTHORIZED**  
Date: 2026-08-28  
Work package: `P0-M00-WP36`  
Related: ADR-0014, ADR-0045, Protector, REST API Builder, Forms, Webhooks/Connections/Event Inbox, JobService, Audit, Error Taxonomy, Privacy, Multisite.

## 1. Purpose

Freeze the future executable evidence required for WPEssential's shared **Rate Limit / Abuse Control service** without duplicating consumer-specific security policy.

This protocol freezes **RLT-01…RLT-176**.

Current execution truth: **0/176 executed**.

No shared limiter backend, algorithm profile, storage adapter, proxy parser or runtime certification exists.

Protector `PR-01…PR-44`, REST `REST-01…REST-52`, Forms `FM-01…FM-92`, Webhooks/Connections `WC-01…WC-156`, and other consumer protocols remain independently certified. Passing RLT never auto-certifies a consumer.

No counter/table/cache operation, request execution, load test, proxy simulation, WAF/provider call, cron cleanup or Multisite runtime operation is authorized by this document.

---

## 2. Canonical boundaries

Keep distinct:

`request ≠ direct peer ≠ trusted client-network identity ≠ authenticated principal ≠ rate key ≠ policy ≠ bucket state ≠ admission decision ≠ authorization decision ≠ consumer side effect ≠ audit record`

Also:
- forwarded header ≠ trusted client IP;
- rate-limit allow ≠ permission to execute;
- rate-limit deny ≠ authentication failure;
- idempotency key ≠ rate-limit bypass credential;
- CAPTCHA/spam score ≠ rate-limit state;
- application limiter ≠ edge/WAF/DDoS guarantee;
- admin capability ≠ automatic limiter bypass;
- cache presence ≠ authoritative atomic counter;
- site bucket ≠ network bucket;
- algorithm name ≠ certified observable behavior;
- backend success ≠ consumer operation success.

## 3. Shared policy descriptor

A future limiter policy records applicable fields:
- stable namespaced policy ID and revision;
- consumer/action/surface identity;
- trusted scope: installation/network/site/resource as applicable;
- principal/key strategy: trusted network identity, authenticated user, credential identity, target resource, composite, or approved anonymous class;
- algorithm profile and version;
- capacity/attempt limit;
- window/refill interval;
- burst semantics;
- cooldown/penalty/backoff semantics;
- bypass/exemption rules and authority;
- risk class and backend-unavailable policy;
- response metadata policy including truthful retry guidance;
- privacy classification/retention;
- backend/atomicity capability requirements;
- diagnostics/audit sampling profile;
- version/deprecation metadata.

The product contract is observable admission/backoff behavior. No exact fixed-window/token-bucket/sliding-window algorithm is final until executed evidence selects a certified profile.

## 4. Independent certification classes

- `RLT-I` — request/client/principal identity and trusted-proxy handling;
- `RLT-K` — key normalization, namespace and privacy;
- `RLT-A` — atomic state/concurrency correctness;
- `RLT-W` — window/refill/burst/backoff semantics;
- `RLT-P` — policy composition, response and bypass governance;
- `RLT-F` — backend failure/degraded/recovery behavior;
- `RLT-E` — evasion/abuse/security resistance;
- `RLT-D` — distributed/cache/cleanup/clock behavior;
- `RLT-M` — Multisite/network isolation and shared floors;
- `RLT-O` — observability/privacy/performance/consumer integration.

Passing one class never certifies another.

---

# 5. Fixed executable fixture matrix

## A. Trusted client and principal identity — RLT-01…RLT-16

- **RLT-01** — direct request with no trusted proxy uses normalized `REMOTE_ADDR` as network identity.
- **RLT-02** — untrusted `X-Forwarded-For` cannot change rate identity.
- **RLT-03** — configured trusted single proxy resolves intended client hop.
- **RLT-04** — multi-proxy chain follows one documented deterministic hop algorithm.
- **RLT-05** — untrusted hop inserted into trusted chain cannot spoof favorable identity.
- **RLT-06** — malformed/multiple forwarding headers fail safe/fallback deterministically.
- **RLT-07** — IPv4 and IPv6 canonical textual variants normalize to intended equivalent network identity.
- **RLT-08** — IPv4-mapped IPv6 and equivalent representations cannot create accidental parallel buckets.
- **RLT-09** — private/reserved-address handling follows proxy profile and never trusts arbitrary caller choice.
- **RLT-10** — excessive proxy chain length is rejected/bounded before key creation.
- **RLT-11** — authenticated user key uses stable WordPress/WPE principal identity, not mutable display/login text.
- **RLT-12** — credential-keyed API policy uses credential identity/reference without storing credential secret in key/log.
- **RLT-13** — anonymous-to-authenticated transition follows explicit key policy and cannot reset a user-targeted security limit accidentally.
- **RLT-14** — logout/login/session rotation does not bypass a principal-targeted cooldown where profile requires persistence.
- **RLT-15** — target-resource limiter distinguishes actor identity from protected target identity where both matter.
- **RLT-16** — client identity resolution never grants WordPress/WPE authorization.

## B. Key normalization, namespace and privacy — RLT-17…RLT-32

- **RLT-17** — stable policy ID participates in key namespace; unrelated policies do not collide.
- **RLT-18** — site/network/installation scope participates in key identity as declared.
- **RLT-19** — endpoint aliases map to canonical operation identity when policy intends one shared bucket.
- **RLT-20** — query-string ordering/irrelevant parameters cannot create unlimited alternate buckets.
- **RLT-21** — encoded path/dot-segment/case normalization follows consumer's canonical route identity before keying.
- **RLT-22** — attacker-controlled arbitrary high-cardinality string cannot become unbounded key namespace without explicit bounded strategy.
- **RLT-23** — composite user+IP key has canonical ordering/version and no ambiguous delimiter collision.
- **RLT-24** — target object IDs are typed/namespaced so unrelated resource classes cannot collide.
- **RLT-25** — user deletion/reassignment cannot make old bucket silently belong to a different principal.
- **RLT-26** — site clone does not inherit another site's live bucket ownership unless explicit network-shared policy says so.
- **RLT-27** — raw password/token/API credential is never part of limiter key.
- **RLT-28** — raw Authorization/cookie/reset token is absent from logs/diagnostics.
- **RLT-29** — IP/network identity storage follows privacy classification and approved pseudonymization/retention profile.
- **RLT-30** — pseudonymous/hash key design does not claim anonymity when linkability remains possible.
- **RLT-31** — key schema/version change has explicit compatibility/reset/migration semantics.
- **RLT-32** — unknown future key schema fails/degrades safely rather than silently merging namespaces.

## C. Atomic admission and concurrency — RLT-33…RLT-48

- **RLT-33** — simultaneous attempts against one bucket respect atomic admission under certified backend semantics.
- **RLT-34** — parallel PHP workers cannot all read old count and independently admit beyond accepted bound.
- **RLT-35** — atomic increment and TTL initialization cannot lose expiry through creation race.
- **RLT-36** — capacity-1 concurrent fixture admits exactly the behavior promised by selected algorithm profile.
- **RLT-37** — capacity-N high-concurrency fixture measures and bounds overshoot explicitly.
- **RLT-38** — rejected attempt cannot accidentally roll back another worker's accepted counter state.
- **RLT-39** — backend retry does not double-increment unless chosen semantics explicitly count attempts and report it.
- **RLT-40** — request cancellation/client disconnect cannot erase an already committed security admission state.
- **RLT-41** — process fatal/OOM after admission leaves bucket in truthful bounded state.
- **RLT-42** — lock/transaction timeout follows explicit retry/degraded behavior and does not silently allow.
- **RLT-43** — database deadlock retry preserves one logical admission decision.
- **RLT-44** — persistent-cache atomic operation semantics are proven for exact adapter/version before security use.
- **RLT-45** — ordinary WordPress transient semantics are not promoted to authoritative atomic security store without separate proof.
- **RLT-46** — edge/provider limiter and local limiter, when layered, do not assume shared atomic state unless adapter proves it.
- **RLT-47** — bucket mutation is idempotent only where an explicit operation/admission identity makes that meaningful.
- **RLT-48** — concurrency evidence records attempted/admitted/rejected/overshoot counts rather than reporting only HTTP status samples.

## D. Window, refill, burst and backoff semantics — RLT-49…RLT-64

- **RLT-49** — selected base algorithm produces documented capacity behavior within normal window.
- **RLT-50** — exact boundary rollover cannot create materially larger burst than accepted profile.
- **RLT-51** — token/refill profile, if used, refills at documented rate under concurrent requests.
- **RLT-52** — fixed-window profile, if used, exposes boundary behavior honestly and within accepted burst bound.
- **RLT-53** — sliding-window/profile, if used, remains computationally/storage bounded.
- **RLT-54** — burst allowance is distinct from sustained rate and both are measurable.
- **RLT-55** — progressive cooldown/backoff increases according to declared rule without integer/time overflow.
- **RLT-56** — successful legitimate event resets/decreases penalty only when policy explicitly permits it.
- **RLT-57** — penalty expiry does not delete unrelated base bucket state prematurely.
- **RLT-58** — multiple policy layers have deterministic composition: e.g. per-IP + per-user + endpoint/global floor.
- **RLT-59** — stricter applicable policy cannot be bypassed because another layer allows.
- **RLT-60** — disabled/expired policy stops future enforcement without corrupting other policy buckets.
- **RLT-61** — policy revision changes use explicit retain/reset/migrate semantics for existing state.
- **RLT-62** — duration math uses stable UTC/elapsed semantics and is not DST/calendar dependent.
- **RLT-63** — wall-clock jump backward/forward cannot create indefinite lockout or unlimited access under certified profile.
- **RLT-64** — extremely large configured limits/durations are bounded/rejected before overflow/resource abuse.

## E. Policy composition, response and bypass governance — RLT-65…RLT-80

- **RLT-65** — consumer must explicitly reference a published/registered limiter policy; hidden implicit defaults are diagnosable.
- **RLT-66** — rate allow continues to downstream authentication/Policy; it never grants operation authority.
- **RLT-67** — rate deny returns stable machine-readable category without masquerading as auth success/failure.
- **RLT-68** — `Retry-After`/retry metadata is derived from committed policy state and does not promise false precision.
- **RLT-69** — browser/API response behavior may differ by consumer while sharing same admission truth.
- **RLT-70** — administrator capability does not bypass limiter unless explicit privileged bypass policy exists.
- **RLT-71** — bypass creation/update requires dedicated authority and is auditable.
- **RLT-72** — bypass cannot be selected through request parameter/header/cookie controlled by ordinary caller.
- **RLT-73** — emergency recovery bypass disables only approved WPE overlay behavior and never authenticates/grants capability.
- **RLT-74** — allowlisted infrastructure IP/profile uses trusted client identity, not spoofable forwarding header.
- **RLT-75** — internal cron/loopback/webhook exception is scoped to registered operation identity and cannot become generic public bypass.
- **RLT-76** — CAPTCHA/spam verdict may influence consumer policy but never mutates shared limiter authority invisibly.
- **RLT-77** — idempotency replay does not automatically receive unlimited attempts; limiter/idempotency ordering is explicit per operation.
- **RLT-78** — health/diagnostic endpoint exemptions cannot expose privileged expensive operations.
- **RLT-79** — network-enforced minimum/floor cannot be weakened by Site Admin policy override.
- **RLT-80** — policy conflict diagnostics show effective layers without exposing sensitive principal/key material.

## F. Backend outage, degraded mode and recovery — RLT-81…RLT-96

- **RLT-81** — high-risk authentication/write policy has explicit fail-closed or conservative degraded behavior when state store is unavailable.
- **RLT-82** — low-risk public read may fail-open only when policy explicitly classifies that behavior safe.
- **RLT-83** — backend timeout does not silently fall through to unlimited high-risk admission.
- **RLT-84** — partial persistent-cache outage has explicit behavior; local per-worker fallback is not called globally atomic.
- **RLT-85** — DB unavailable path does not spin/retry indefinitely per request.
- **RLT-86** — cache→DB fallback, if supported, preserves key/version semantics and does not double-count unpredictably.
- **RLT-87** — DB→cache recovery, if supported, does not erase active high-risk cooldown without declared recovery rule.
- **RLT-88** — backend failover between nodes/adapters cannot merge unrelated key namespaces.
- **RLT-89** — stale replica/read path is never used as authoritative admission if it can undercount committed state.
- **RLT-90** — backend returns corrupt/invalid state: affected policy fails/degrades safely and emits diagnostic.
- **RLT-91** — storage schema mismatch/unknown version prevents unsafe writes until compatible path exists.
- **RLT-92** — cleanup backlog/outage cannot convert expired-row volume into unbounded request latency.
- **RLT-93** — recovery after outage has bounded reconciliation/reset semantics documented per adapter.
- **RLT-94** — operator/manual reset requires authority, target scope and audit reason.
- **RLT-95** — reset of one policy/principal/site cannot delete unrelated buckets.
- **RLT-96** — diagnostics distinguish limiter-store outage from consumer/provider/application failure.

## G. Evasion and abuse resistance — RLT-97…RLT-112

- **RLT-97** — spoofed forwarded header cannot rotate apparent client identity outside trusted proxy profile.
- **RLT-98** — alternate host/path encoding cannot create new bucket when consumer canonical operation is same.
- **RLT-99** — query-noise/random parameters cannot create unbounded equivalent endpoint buckets.
- **RLT-100** — cookie/session rotation cannot bypass a user/target-based security limit.
- **RLT-101** — IPv6 textual churn cannot create buckets for same canonical address.
- **RLT-102** — IPv6 privacy-address/prefix strategy is explicit; product does not overclaim one person per IP.
- **RLT-103** — large shared NAT fixture measures false-positive/fairness tradeoff and preserves recoverability.
- **RLT-104** — distributed botnet/IP rotation demonstrates application limiter limits; no DDoS/WAF overclaim.
- **RLT-105** — attacker cannot choose another user's ID/resource ID in rate key to lock victim unless target-based protection intentionally includes that target with safeguards.
- **RLT-106** — lockout amplification against named account/target has explicit anti-abuse/recovery strategy.
- **RLT-107** — forged credential identifier without valid authentication cannot poison authenticated-principal bucket unless policy intentionally counts target attempts.
- **RLT-108** — rate-key generation rejects control characters/oversized identifiers/serialization ambiguity.
- **RLT-109** — bypass-policy enumeration does not disclose private allowlist/network details to unauthorized caller.
- **RLT-110** — limiter response avoids revealing whether protected account/resource exists when concealment policy requires it.
- **RLT-111** — expensive key normalization/policy matching is bounded before attacker can weaponize limiter itself.
- **RLT-112** — malicious high-cardinality traffic has bounded storage/admission cleanup strategy and measured resource cost.

## H. Distributed state, cleanup and time behavior — RLT-113…RLT-128

- **RLT-113** — two application nodes sharing certified backend observe one logical bucket state.
- **RLT-114** — non-shared local memory adapter is never represented as network/global enforcement.
- **RLT-115** — persistent object-cache cluster failover semantics are measured for atomicity/TTL.
- **RLT-116** — cache eviction cannot silently reset a security-critical bucket without declared risk profile.
- **RLT-117** — TTL expiry and counter update race cannot resurrect stale penalty unexpectedly.
- **RLT-118** — cleanup deletes only expired owned records and cannot race-delete a newly renewed bucket.
- **RLT-119** — cleanup is paged/bounded; no unbounded full-table scan during request path.
- **RLT-120** — JobService cleanup retry is idempotent and not required for request-time correctness.
- **RLT-121** — delayed cleanup does not change logical expiry semantics.
- **RLT-122** — clock source/precision is consistent enough across nodes for selected algorithm profile.
- **RLT-123** — moderate node clock skew follows documented conservative behavior.
- **RLT-124** — deploy/restart does not reset durable buckets unless selected adapter/profile explicitly documents ephemeral semantics.
- **RLT-125** — rollback across limiter schema/policy version follows VER/MLC compatibility behavior.
- **RLT-126** — backup/restore does not blindly resurrect stale abuse penalties as current truth without explicit policy.
- **RLT-127** — disaster recovery cannot silently erase network-enforced high-risk floors without diagnostics/reinitialization contract.
- **RLT-128** — backend metrics distinguish logical bucket count from physical expired rows/cache entries.

## I. Multisite and network isolation — RLT-129…RLT-144

- **RLT-129** — same policy/principal on Site A and Site B does not collide for site-scoped policy.
- **RLT-130** — request-supplied blog/site ID cannot select another site's bucket namespace.
- **RLT-131** — current blog context is not durable bucket ownership.
- **RLT-132** — `switch_to_blog()` context changes do not leak prior site's rate key/state.
- **RLT-133** — Network Admin may define explicit network-shared floor only through network authority.
- **RLT-134** — Site Admin cannot weaken network-enforced minimum.
- **RLT-135** — network-shared policy intentionally aggregates across sites and reports that fact clearly.
- **RLT-136** — noisy Site A cannot exhaust Site B site-scoped buckets/storage quota through namespace collision.
- **RLT-137** — network aggregate anti-abuse state has bounded per-site/principal cardinality strategy.
- **RLT-138** — site creation initializes no accidental inherited live buckets from template/source site.
- **RLT-139** — clone duplicates policy configuration only as declared, not source site's live counters.
- **RLT-140** — site deletion removes/retains owned limiter records according to lifecycle/privacy policy without touching shared network state.
- **RLT-141** — site restore reauthorizes policy/scope and does not resurrect stale cross-site ownership.
- **RLT-142** — site transfer/network migration changes scope generation explicitly and prevents old-network collision.
- **RLT-143** — network-wide cleanup is bounded/fair and does not create one giant synchronous all-site loop.
- **RLT-144** — Multisite evidence records topology/backend and never promotes MS0 mapping to runtime certification.

## J. Consumer integration and semantic separation — RLT-145…RLT-160

- **RLT-145** — Protector uses shared admission result but retains its own route/rule/recovery semantics.
- **RLT-146** — REST uses shared limiter but still executes auth, Policy, schema and idempotency contracts.
- **RLT-147** — Forms public submission uses shared limiter but still owns capacity/spam/CAPTCHA/Entry semantics.
- **RLT-148** — Webhook ingress can use limiter without substituting signature verification/replay/Event Inbox correctness.
- **RLT-149** — login/password-gate limiter never replaces native WordPress authentication/password verification.
- **RLT-150** — XML-RPC outer limiting never replaces XML-RPC method policy/native auth.
- **RLT-151** — Workflow/Job retry throttling, if integrated, stays separate from JobService backoff/fairness/idempotency truth.
- **RLT-152** — Notification/email provider throttles/quotas are provider facts and not automatically the same as abuse limiter state.
- **RLT-153** — Connection provider quota/rate headers may inform adapter scheduling only through explicit integration semantics.
- **RLT-154** — consumer can choose stricter local policy while shared service preserves key/atomicity guarantees.
- **RLT-155** — consumer disable/module expiry follows MLC and cannot silently disable required retained security policy contrary to product contract.
- **RLT-156** — consumer policy revision/Definition revision pins the intended limiter policy generation where correctness requires it.
- **RLT-157** — shared limiter error maps through ERR taxonomy without leaking backend/key internals.
- **RLT-158** — Audit records normalized decision/reason/correlation while bucket store is not treated as Audit history.
- **RLT-159** — PDL privacy/retention applies to limiter identities/logs independently of operational TTL.
- **RLT-160** — passing RLT never changes PR/REST/FM/WC or any provider certification counter.

## K. Observability, privacy, performance and scale — RLT-161…RLT-176

- **RLT-161** — diagnostics expose backend/profile health, policy ID, scope class and aggregate admission metrics without secrets/raw sensitive keys.
- **RLT-162** — allowed/rejected/degraded/backend-error metrics are distinct.
- **RLT-163** — sampled Audit/telemetry cannot retain raw private payload/credential because limiter saw request context.
- **RLT-164** — privacy export/erase follows declared ownership; operational anti-abuse retention exceptions are explicit/legal-policy owned.
- **RLT-165** — logs redact/pseudonymize IP/principal identity according to classification while retaining useful correlation where approved.
- **RLT-166** — cardinality metric detects key-explosion attack without enumerating private key values to unauthorized diagnostics user.
- **RLT-167** — baseline request overhead measured with limiter disabled, one policy, layered policies and high-cardinality traffic.
- **RLT-168** — atomic backend latency/contention measured under controlled concurrency.
- **RLT-169** — cleanup latency/storage growth measured for representative expired/live bucket populations.
- **RLT-170** — 10k active bucket workload remains within declared query/memory/storage budget without weakening atomicity.
- **RLT-171** — 100k active/expired mixed workload measures request and cleanup behavior; no scale claim without numbers.
- **RLT-172** — distributed multi-worker workload measures admitted/rejected/overshoot/error counts and backend saturation.
- **RLT-173** — Multisite noisy-neighbor workload measures site isolation and network floor behavior.
- **RLT-174** — backend outage/recovery load does not create thundering-herd retry loop.
- **RLT-175** — performance optimization cannot trust spoofable identity, remove atomicity, leak scope or silently fail-open high-risk operations.
- **RLT-176** — evidence report records unsupported adapters/profiles and refuses generic “rate limiting certified” claim beyond tested environment.

---

## 6. MUST NOT / stop-the-line gates

Stop certification/release for affected profile if any of these occur:
- untrusted forwarding header controls security identity;
- concurrent workers materially bypass promised allowance due non-atomic state;
- high-risk limiter backend failure silently becomes unlimited access contrary to documented risk policy;
- Site A can consume/reset/collide with Site B site-scoped bucket;
- request-controlled identifier selects privileged bypass;
- raw credential/reset/session/Vault secret appears in limiter key/log;
- rate-limit allow is treated as authentication/authorization;
- cache/transient adapter is called atomic without exact runtime proof;
- admin capability silently bypasses security policy without explicit audited exemption;
- limiter is marketed as WAF/DDoS protection beyond application/edge evidence;
- shared RLT certification is used to claim PR/REST/FM/WC consumer certification.

## 7. Required future evidence report

Report at minimum:
- exact WordPress/PHP/database/cache/proxy/Multisite/runtime profile;
- selected algorithm profile/version and observable capacity/burst/backoff semantics;
- state adapter and atomic operations actually used;
- RLT-01…RLT-176 pass/fail/not-applicable;
- concurrency/admission/overshoot measurements;
- proxy spoof/identity normalization results;
- outage/fail-policy/recovery results;
- key-cardinality/privacy/cleanup results;
- Multisite isolation/network-floor results;
- performance/load measurements;
- independent certification classes earned;
- consumer protocols re-executed separately where integration claims are made;
- unsupported/degraded configurations.

## 8. Current truth

- RLT fixtures documented: **176**.
- RLT fixtures executed: **0/176**.
- Shared limiter runtime certification: **none**.
- Atomic state adapter certified: **none**.
- Algorithm profile selected/certified: **none**.
- Consumer certifications are unchanged.

## 9. Development gate

Execution requires explicit owner authorization under ADR-0014 and the Approval Ledger. `continue`, planning acceptance or ADR acceptance does not authorize executable evidence or production implementation.