# WPEssential — Webhooks, Connections & Event Inbox Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Work package: `P0-M00-WP06`  
Project state: `PLANNED_EXISTING_PROJECT`  
Execution mode: `PLANNER_ONLY`  
Governs: ADR-0040, ADR-0055, ADR-0080, Connection Adapter Certification Contract, Event Inbox physical profile, Webhooks & Connections specifications, Vault, Safe HTTP, Policy/Abilities, JobService, Workflow, Notification/Email, Membership, Backup/Import, Multisite/Site Lifecycle, ADR-0014.

## 1. Purpose

Define bounded executable evidence required before WPEssential can claim production-ready Connections, provider authentication, Safe HTTP, inbound/outbound webhooks, signature/replay protection, durable Event Inbox processing, provider reconciliation, provider capability certification or Multisite event routing.

This protocol tests accepted integration architecture. It does not authorize network access, provider configuration, webhook subscription creation, credential exchange, Event Inbox DDL, Jobs, business mutations, provider calls or benchmarks.

## 2. Canonical invariants

A future certified implementation must preserve:

1. **Connection state and credentials are separate.** Secrets remain Vault references; plaintext credentials do not become generic Definition, Job, Event Inbox, log or export data.
2. **Connected does not mean fully certified.** Certification is scoped to adapter + provider + capability + API/profile version + environment.
3. **All outbound HTTP uses centralized Safe HTTP policy.** Provider adapters do not bypass SSRF, redirect, TLS, host, size, timeout or response guards.
4. **Inbound authenticity is verified before business dispatch.** Exact raw bytes are used where provider signature semantics require them.
5. **Trusted endpoint/Connection binding determines scope.** Attacker-controlled payload fields never choose site/network scope directly.
6. **Replay and duplicate delivery are expected conditions.** Stable provider event identity, timestamp/nonce profile and dedupe are explicit where supported.
7. **Event Inbox stores accepted verified ingress facts, not arbitrary unauthenticated request bodies.**
8. **Event Inbox is not owning business-domain truth.** Membership, Email, Workflow and other consumers keep their own durable idempotency/state/reconciliation.
9. **Out-of-order provider events are expected.** Arrival order does not automatically become domain chronology.
10. **Unknown schema/event type fails safely.** It cannot become arbitrary action execution or implicit business mutation.
11. **Outbound mutation is at-least-once/unknown-outcome aware.** Timeout after send is not assumed failure; unsafe blind retry is forbidden.
12. **Provider capability support is granular.** Authentication/read/write/event/reconciliation support are independently certified through I0–I5.
13. **Multisite scope is explicit in Connections, Event Inbox, Jobs, logs and provider routing.** Same provider IDs across sites cannot collide.
14. **Restore/clone/site lifecycle cannot blindly reactivate copied credentials, subscriptions, events or pending outbound effects.**

## 3. Future certification profile

Every future evidence run records:
- WordPress/PHP/database versions and P-001 status;
- single-site/Multisite topology;
- Connection Definition and adapter versions;
- provider/API/product/region/profile version;
- certified capability keys and intended I0–I5 level;
- Vault/credential profile;
- Safe HTTP policy version;
- Webhook verification/signature profile;
- Event Inbox physical profile (`EI1/PT-D`, `EI2/PT-E`, or later accepted profile);
- JobService/Workflow versions and certification states;
- rate-limit/backpressure settings;
- provider sandbox/test/live environment class;
- retention/privacy configuration;
- representative event/request workloads.

# 4. Connection Definition, publish and dependency fixtures

### WC-01 — Draft Connection Definition inactive
Draft/unpublished Connection cannot be used by production Ability, Workflow, Notification or webhook dispatch merely because its UUID is known.

### WC-02 — Publish validation
Publish rejects missing adapter, invalid auth profile, unresolved Vault refs, unsupported provider/API profile, unsafe endpoint policy or missing required capability declarations.

### WC-03 — Published revision identity
Runtime operation records the intended Connection/profile revision where historical/audit/retry correctness requires it.

### WC-04 — Definition edited during in-flight operation
Draft/new publish does not silently reinterpret already-admitted outbound/inbound operation in a way that changes auth, scope or idempotency semantics.

### WC-05 — Adapter dependency missing
Missing/disabled adapter enters explicit degraded state; Connection does not fall back to arbitrary generic HTTP behavior.

### WC-06 — Unsupported API version
Unsupported/deprecated provider API version fails closed or explicit degraded mode; no optimistic capability claim.

### WC-07 — Capability removed after publish
Operations requiring removed capability are blocked/reconciled rather than silently attempted.

### WC-08 — Provider profile drift
Detected material provider/version drift marks certification outdated and does not preserve an unqualified Production Certified claim.

### WC-09 — Route key collision
Two inbound definitions cannot ambiguously own the same effective route/profile without explicit deterministic routing rule.

### WC-10 — Connection disabled
No new ordinary outbound operations or business dispatch use disabled Connection; inbound provider retry acknowledgement follows explicit safe lifecycle policy.

### WC-11 — Connection archived/deleted with dependencies
Dependency impact is surfaced; referenced Workflow/Notification/etc. does not silently bind another Connection.

### WC-12 — Import/export Definition
Portable configuration excludes secrets and environment-bound credentials/subscriptions unless a separately accepted secure remap contract applies.

# 5. Scope ownership and Multisite identity fixtures

### WC-13 — Site-owned default
New Connection defaults to explicit site ownership and cannot be used by another site by identifier manipulation.

### WC-14 — Network-owned Connection authority
Creating/updating a network-shared Connection requires explicit network authority and network-owned storage semantics.

### WC-15 — Wrong-site Connection IDOR
Direct API/Ability use of another site's Connection UUID is denied even when provider/account identifiers match.

### WC-16 — Network-shared delegated site use
Delegated site use follows explicit allowlist/policy and cannot be inferred from site switching alone.

### WC-17 — Payload-supplied site ID ignored as authority
Inbound payload field such as `site_id`, tenant ID or object ID cannot choose WPE target scope before trusted mapping.

### WC-18 — Trusted endpoint scope binding
Dedicated endpoint/Connection configuration deterministically establishes allowed site/network scope before business payload mapping.

### WC-19 — Network-shared provider routing
Provider tenant/resource mapping routes to site only after certified trusted identity mapping.

### WC-20 — Ambiguous network-shared routing
Ambiguous target enters quarantine/reconciliation; system never guesses from matching numeric IDs/slugs/domains.

### WC-21 — Same provider event ID across sites
Dedupe identity includes authoritative scope/Connection/profile so one site's event cannot suppress or overwrite another's.

### WC-22 — Scope change of existing Connection
Moving site-owned to network-owned or vice versa requires explicit migration/rebind plan; no silent credential/subscription reuse.

### WC-23 — Network activation
Network activation does not automatically share site credentials or create provider subscriptions across all sites.

### WC-24 — Site switch in admin
Switching current admin site invalidates stale selected Connection/provider context and reauthorizes target scope.

# 6. Vault, credential and OAuth fixtures

### WC-25 — Secret stored only as Vault reference
API key/client secret/token/password is absent from Connection Definition payload, generic options, Job args and normal logs.

### WC-26 — Write-only secret UI
Saved credential cannot be revealed through ordinary get/edit API or UI response.

### WC-27 — Invalid credential
Authentication failure maps safely without leaking credential/provider raw sensitive response.

### WC-28 — Revoked credential
Revocation detected at next certified boundary causes explicit reauthorization/degraded state; no endless unsafe retry.

### WC-29 — Credential rotation
New credential version becomes active according to declared cutover semantics; in-flight attempts preserve enough identity to reconcile safely.

### WC-30 — Deleted Vault reference
Connection fails closed and does not use cached plaintext fallback.

### WC-31 — Least-scope denial
Missing provider scope blocks only affected capability and does not claim full Connected/Production support.

### WC-32 — Account/tenant identity verification
Authentication binds expected provider account/tenant identity; valid token for wrong tenant is not silently accepted where profile requires identity.

### WC-33 — OAuth state validation
Authorization callback rejects missing/mismatched/replayed state.

### WC-34 — OAuth PKCE
Public-client profile requires verified PKCE S256 where applicable; downgrade is rejected.

### WC-35 — OAuth redirect/callback binding
Callback/redirect target is fixed/allowlisted according to profile and cannot be attacker-swapped.

### WC-36 — OAuth issuer/authorization-origin mix-up
Unexpected issuer/token/authorization origin is rejected according to certified provider profile.

### WC-37 — Refresh token rotation
Rotating refresh token semantics preserve newest valid token and detect replay/stale update races where provider supports rotation.

### WC-38 — Concurrent refresh race
Two workers refreshing the same credential cannot overwrite a newer token with stale result.

### WC-39 — OAuth revoke/disconnect
Disconnect revokes/removes local usable credential refs and marks dependent capability state accurately; remote revoke result truth is not overstated.

### WC-40 — OAuth secret/log redaction
Authorization code, access token, refresh token, client secret and sensitive callback parameters never appear in ordinary logs/errors/history.

# 7. Provider capability and I0–I5 certification fixtures

### WC-41 — I0 adapter load
Adapter schema/version loads without provider connectivity claim.

### WC-42 — I1 valid authentication
Certified profile proves valid authentication and expected identity only for declared auth method.

### WC-43 — I1 invalid/expired/revoked authentication
Failure modes are explicitly handled and redacted.

### WC-44 — I2 read/list capability
Certified read operation validates mapping, authorization/scope, pagination and rate-limit behavior.

### WC-45 — I2 unsupported read capability
Unsupported query/search/read endpoint remains unavailable despite Connection being authenticated.

### WC-46 — I3 write action input/authority
Each certified write/action validates typed input, provider scope and WPE authorization separately.

### WC-47 — I3 action idempotency
Mutation uses provider idempotency/status/reconciliation profile where available and records unknown outcome honestly.

### WC-48 — I3 high-risk action granularity
Delete/refund/revoke/etc. is separately certified and not inherited from lower-risk write action.

### WC-49 — I4 event authenticity
Provider event capability proves signature/authenticity, replay, duplicate and source mapping for exact provider/API profile.

### WC-50 — I4 reconciliation
Provider source-of-truth/list-events/status reconciliation works for declared capability and handles out-of-order/unknown outcomes.

### WC-51 — I5 advertised capability closure
Production profile certifies every capability publicly claimed for that exact profile; unsupported capabilities remain explicit.

### WC-52 — Certification downgrade/deprecation
Version drift, provider deprecation or failed regression can downgrade one capability without falsely preserving I5/global support label.

# 8. Safe HTTP SSRF, redirect and response fixtures

### WC-53 — HTTPS default
External provider request requires HTTPS unless an explicitly accepted specialized profile permits otherwise.

### WC-54 — Loopback block
`127.0.0.0/8`, `::1`, localhost aliases and equivalent loopback targets are blocked by default.

### WC-55 — RFC1918/private-network block
Private IPv4 destinations are blocked by generic external profile.

### WC-56 — IPv6 private/link-local block
ULA/link-local/private IPv6 targets are blocked by generic external profile.

### WC-57 — Cloud metadata block
Known metadata/link-local targets such as `169.254.169.254` and equivalent routes are denied.

### WC-58 — Alternate numeric IP forms
Decimal/octal/hex/mixed/IPv4-mapped tricks cannot bypass destination classification.

### WC-59 — Userinfo/authority parsing
Crafted URL userinfo/host ambiguity cannot redirect credentials or host validation to attacker target.

### WC-60 — Host allowlist
Fixed provider profile rejects non-approved host even if TLS is valid.

### WC-61 — Port policy
Unexpected/private/admin ports are rejected unless explicitly allowed by provider profile.

### WC-62 — DNS rebinding
Resolution and connection policy prevent hostname initially resolving public then private from bypassing SSRF guard.

### WC-63 — Redirect revalidation
Every redirect target is independently revalidated for scheme/host/IP/port policy.

### WC-64 — Credential-bearing redirect
Authorization/bearer credential is not forwarded to untrusted redirect host.

### WC-65 — Redirect loop/limit
Redirect count is bounded and cannot consume unbounded request resources.

### WC-66 — TLS verification
Certificate/hostname verification cannot be disabled by ordinary Connection configuration.

### WC-67 — Proxy policy
Proxy use is explicit and cannot silently weaken destination/credential trust boundaries.

### WC-68 — Method allowlist
Unsafe/unexpected HTTP methods are blocked unless declared by certified capability.

### WC-69 — Request byte limit
Headers/body/multipart payload respect bounded request-size policy.

### WC-70 — Response byte/time limit
Slow/oversized response is terminated safely and does not exhaust PHP/worker memory indefinitely.

### WC-71 — Decompression bomb
Compressed response expansion is bounded independently of compressed byte length.

### WC-72 — Response content/schema validation
Unexpected content type, malformed JSON/XML or schema mismatch fails safely before business mapping.

# 9. Inbound webhook authenticity, signature and replay fixtures

### WC-73 — Unknown endpoint reference
Unknown/random route key does not expose provider/profile metadata or dispatch business logic.

### WC-74 — Disabled endpoint
Disabled endpoint does not create processable Event Inbox facts; response follows provider-safe lifecycle policy.

### WC-75 — Request content-type limit
Unsupported content type is rejected before expensive parsing/business logic.

### WC-76 — Raw request byte limit
Oversized webhook body is rejected before unbounded buffering/parsing.

### WC-77 — Exact raw-body signature
Provider requiring exact raw bytes verifies against unmodified body before JSON/form normalization.

### WC-78 — Valid HMAC signature
Certified HMAC profile accepts known-good signature with declared algorithm/version/key reference.

### WC-79 — Invalid HMAC signature
Bad signature creates no normal processable Event Inbox event/business mutation.

### WC-80 — Constant-time comparison
MAC/signature comparison path is timing-safe according to accepted primitive/profile.

### WC-81 — Signature algorithm/version mismatch
Unexpected algorithm/key version is rejected rather than silently downgraded.

### WC-82 — Key rotation overlap
Current/previous signing key overlap works only inside explicit bounded rotation policy.

### WC-83 — Expired signing key
Expired/revoked webhook key no longer authenticates requests.

### WC-84 — Timestamp fresh
Request inside accepted provider replay/skew window is accepted when other checks pass.

### WC-85 — Timestamp stale
Stale signed request is rejected when certified profile provides timestamp semantics.

### WC-86 — Future timestamp/skew abuse
Excessive future skew is rejected according to profile.

### WC-87 — Nonce replay
Reused authenticated nonce is rejected/deduped where provider protocol offers nonce semantics.

### WC-88 — Provider event-ID replay
Repeated provider event/delivery ID resolves to existing logical ingress identity, not a new business event.

### WC-89 — Same ID conflicting payload
Materially conflicting payload for same trusted event ID enters conflict/reconciliation diagnostics; existing event is not silently overwritten.

### WC-90 — No stable event ID
Certified fallback dedupe uses authenticated semantic/fingerprint strategy and bounded window; arbitrary raw hash is not assumed sufficient.

### WC-91 — Unsigned provider explicit profile
Provider without cryptographic signatures requires separately certified alternative authenticity profile; unsigned is never silently `verified`.

### WC-92 — Bearer webhook secret
Bearer/token endpoint secret is verified and redacted; query-string leakage is rejected by default unless provider profile explicitly requires it.

### WC-93 — Malformed signed body
Signature may be valid but malformed/unsupported payload is quarantined/schema-failed before business dispatch.

### WC-94 — Invalid authenticity diagnostics
Rejected attack can emit bounded Security/Audit metadata without storing full attacker-controlled sensitive body as normal Event Inbox record.

# 10. Event Inbox durability, normalization, dedupe and processing fixtures

### WC-95 — Verified ingress durability
Accepted verified event envelope is durably stored before asynchronous business processing becomes authoritative.

### WC-96 — Scope from trusted binding
Envelope scope records endpoint/Connection-derived site/network identity, never raw payload authority.

### WC-97 — Provider/profile identity pinning
Envelope records adapter/provider/profile version needed for deterministic normalization/replay.

### WC-98 — Typed normalized payload
Provider raw JSON is validated/mapped to versioned normalized event schema before consumer dispatch.

### WC-99 — Raw payload minimization
Exact raw payload is retained only when declared verification/recovery policy requires it and via protected bounded retention.

### WC-100 — Duplicate same ID/same payload
Duplicate resolves to existing Event Inbox envelope and cannot create second protected business transition.

### WC-101 — Duplicate observation metadata
Optional delivery count/last-seen update does not mutate canonical provider fact or consumer state incorrectly.

### WC-102 — Concurrent duplicate receipt
Two simultaneous deliveries of same identity admit one logical Event Inbox envelope.

### WC-103 — Out-of-order provider events
Older-later arrival is retained as source fact but cannot blindly regress newer owning-domain state.

### WC-104 — Provider sequence/version token
Certified ordering token is preserved and available to consumer/reconciliation policy.

### WC-105 — Unknown event type
Unknown provider event is safely ignored/quarantined/dead-lettered according to profile; no arbitrary callback/action selection.

### WC-106 — Schema drift
Known event type with incompatible new shape enters `quarantined_schema` or explicit unsupported state.

### WC-107 — Poison event retry bound
Deterministically invalid consumer payload cannot retry forever and starve healthy events.

### WC-108 — Processing claim race
Two workers cannot concurrently commit the same non-idempotent consumer transition for one event identity.

### WC-109 — Worker crash before consumer mutation
Retry resumes safely from Event Inbox accepted state.

### WC-110 — Worker crash after consumer mutation before processed mark
Owning domain idempotency/reconciliation prevents duplicate business mutation.

### WC-111 — Abandoned processing claim
Expired/abandoned claim can recover without assuming prior side effect did not occur.

### WC-112 — Manual replay
Replay reprocesses same logical ingress identity and is separately authorized/audited; it does not mint a new external fact.

### WC-113 — Reconciliation-required state
Ambiguous provider/domain result enters explicit reconciliation state with bounded retry/manual path.

### WC-114 — Processed state truth
`processed` means registered consumer handling completed, not that provider/business object can never later change/reverse.

### WC-115 — Terminal dedupe tombstone
Minimal dedupe/reconciliation identity may outlive payload body where policy requires, without retaining unnecessary PII.

### WC-116 — Event Inbox not domain truth
Deleting/pruning operational Event Inbox detail does not erase owning Membership/Email/Workflow/etc. durable state/history needed for truth.

# 11. Consumer idempotency, Workflow and Job integration fixtures

### WC-117 — Consumer-specific idempotency
Each owning domain binds Event Inbox identity/provider source reference to its own transition idempotency.

### WC-118 — Workflow trigger dedupe
One logical verified external event starts at most one intended Workflow Run under its trigger binding.

### WC-119 — Membership/billing event
Commercial source event is normalized/reconciled before Enrollment/Entitlement mutation; provider raw status is not direct authority.

### WC-120 — Email provider event
Verified event becomes Email delivery evidence only according to Email ET-certified mapping; Event Inbox does not infer inbox/read truth.

### WC-121 — Notification event
Notification occurrence/delivery semantics remain separate and cannot be upgraded by generic Event Inbox acceptance.

### WC-122 — Import/Backup child event
External progress/completion event updates owning Run only through typed owning service/idempotent state precondition.

### WC-123 — Job enqueue failure after event acceptance
Accepted Event Inbox row remains recoverable/discoverable and is not lost because async enqueue failed.

### WC-124 — Duplicate Job execution
At-least-once Job delivery does not repeat protected consumer mutation.

### WC-125 — Backpressure
Large event burst applies bounded admission/processing/backpressure without dropping verified accepted facts silently.

### WC-126 — Consumer disabled/dependency missing
Event remains explicit pending/degraded/quarantined according to retention policy; system does not dispatch to arbitrary substitute consumer.

# 12. Outbound webhook/request, retry and unknown-outcome fixtures

### WC-127 — Outbound authorization
Manual/Workflow/Notification outbound action requires current WPE Ability/Policy plus certified Connection capability.

### WC-128 — Typed outbound payload mapping
Only declared fields/tokens are mapped; arbitrary object dump/secret/token access is denied.

### WC-129 — Header secret handling
Auth/signing headers are produced from Vault/provider adapter and absent from stored generic payload/logs.

### WC-130 — Outbound signature profile
If WPE signs payload, algorithm/key/canonicalization/version is explicit and testable; no ad-hoc crypto fallback.

### WC-131 — Stable outbound idempotency key
Logical action/attempt identity is reused according to provider certified idempotency semantics.

### WC-132 — 2xx response truth
Receiver 2xx means accepted HTTP response per profile, not proof downstream business completion.

### WC-133 — Known retryable failure
429/selected 5xx/transport errors follow bounded retry/backoff and `Retry-After` where certified.

### WC-134 — Known terminal failure
Validation/auth/permanent destination error becomes terminal/degraded without infinite retry.

### WC-135 — Timeout before send proven
Retry is allowed when evidence proves no remote side effect could have occurred.

### WC-136 — Timeout after send ambiguous
Operation enters `outcome_unknown`/reconciliation rather than blind retry when remote side effect may have committed.

### WC-137 — Provider status reconciliation
Certified status/query API can resolve unknown outcome and prevent duplicate remote mutation.

### WC-138 — Dead-letter/manual retry
Terminal/dead-letter replay is separately authorized/audited and rechecks current Connection/capability/business preconditions.

# 13. Pagination, file transfer, observability and privacy fixtures

### WC-139 — Pagination cursor handling
Adapter follows certified pagination model with bounded pages/items and rejects attacker/provider loops or repeated cursors.

### WC-140 — Rate-limit quota headers
Certified adapter parses only documented provider rate-limit semantics and does not trust arbitrary headers for global policy.

### WC-141 — Protected file download
Inbound/provider file retrieval uses Safe HTTP + size/MIME policy and produces Protected Asset/reference according to owning module contract.

### WC-142 — Protected file upload
Outbound file upload uses authorized Protected Asset reference and cannot read arbitrary server path/public-unscoped file by user input.

### WC-143 — Log redaction
Authorization headers, cookies, API keys, OAuth tokens, signed URLs, webhook secrets and sensitive full bodies are absent from ordinary logs.

### WC-144 — Error redaction
User-facing/provider errors omit secret values, raw authorization data, private paths and unrelated site/account identifiers.

### WC-145 — Privacy export/erase
Locally retained personal event/Connection data follows adapter/domain retention policy without exposing other sites/tenants/subjects.

### WC-146 — Raw payload retention expiry
Raw verification payload/reference expires independently from minimal dedupe/reconciliation metadata and is removed from protected storage/indexes as required.

# 14. Site lifecycle, clone and restore fixtures

### WC-147 — Site archive/suspend during inbound receipt
Endpoint may acknowledge provider safely, but protected business mutation pauses/fails according to lifecycle policy.

### WC-148 — Site delete with provider subscription
Deletion plan accounts for webhook subscription revoke, Connection delegation, pending outbound operations and dedupe tombstone risk.

### WC-149 — Site clone
Definitions may clone as configured; credentials, OAuth grants, provider subscriptions, Event Inbox identities and pending remote effects are not blindly activated.

### WC-150 — Backup/restore processed events
Historical processed events do not automatically replay after restore.

### WC-151 — Restore in-flight/unknown operations
Failed-retryable/reconciliation/outcome-unknown events and outbound attempts undergo explicit provider/domain reconciliation before retry.

# 15. EI topology and scale fixtures

### WC-152 — EI1/PT-D baseline
Measure shared scoped Event Inbox inserts, dedupe, claims, reconciliation queries, retention and wrong-site predicates under representative workloads.

### WC-153 — EI2/PT-E mandatory comparison
Measure per-site provisioning/migration/version skew, network-shared Connection routing, Backup/lifecycle and hot-site isolation.

### WC-154 — 100k/1M/10M retained events
Measure insert/dedupe/query/index/storage/purge/reconciliation behavior where practical without weakening trust boundaries.

### WC-155 — Burst/hot provider workload
Measure burst ingestion, duplicate storms, one noisy Connection/site, Job backlog, fairness and bounded acknowledgement behavior.

### WC-156 — 100/1k/10k-site topology
Compare EI1/EI2 scope isolation, noisy-neighbor behavior, migrations, diagnostics, provider routing, retention and operational cost.

# 16. MUST NOT / stop-the-line gates

Certification fails if any fixture demonstrates:
- plaintext credential/token/webhook secret in generic Definition, Job, Event Inbox, log, export or UI readback;
- provider adapter bypassing centralized Safe HTTP controls;
- loopback/private/link-local/cloud-metadata/DNS-rebinding/redirect SSRF bypass in generic external profile;
- inbound business mutation before required authenticity/signature verification;
- payload-controlled site/network selection before trusted endpoint/Connection mapping;
- invalid/replayed webhook becoming a normal processable Event Inbox event;
- same trusted event identity producing repeated protected domain mutation contrary to idempotency contract;
- Event Inbox row treated as owning Membership/Email/Workflow/business truth;
- arrival order blindly regressing newer domain state;
- unknown schema/event selecting arbitrary function/callback/PHP/action;
- network timeout treated as definite outbound failure when side effect may have occurred;
- unsafe blind retry of ambiguous non-idempotent provider mutation;
- `Connected` or lower I-level presented as unsupported read/write/event/Production certification;
- cross-site provider/event/Connection ID collision leaking or mutating another site;
- restore/clone blindly reactivating credentials/subscriptions/events/pending remote effects;
- provider support marketed beyond exact certified capability/profile.

These are stop-the-line defects for the affected certification scope.

# 17. Performance evidence

Capture at minimum:
- Connection test/auth latency by declared check;
- Safe HTTP DNS/connect/TLS/TTFB/total latency;
- inbound signature verification latency;
- receipt/dedupe p50/p95/p99;
- sustainable verified inserts/sec;
- duplicate suppression rate and duplicate business application count;
- Event Inbox Job claim/process throughput;
- reconciliation backlog/age;
- outbound attempt/retry amplification;
- rate-limit/backpressure behavior;
- DB rows examined/locks/deadlocks/retries;
- Event Inbox index/storage growth;
- raw payload vs normalized retention cost;
- purge throughput;
- one-hot-provider/site noisy-neighbor impact;
- EI1/EI2 migration/provisioning/Backup/diagnostics cost.

Performance optimization must not weaken authenticity, SSRF defenses, scope derivation, dedupe, consumer idempotency, unknown-outcome truth or site isolation.

# 18. Required future WP06 report

Include:
- exact environment/adapter/provider/API/profile versions;
- requested and achieved I0–I5 capability levels;
- WC-01…WC-156 pass/fail/NA with rationale;
- Vault/auth/OAuth evidence;
- Safe HTTP SSRF/redirect/TLS/size evidence;
- webhook raw-body/signature/replay/key-rotation evidence;
- Event Inbox normalization/dedupe/concurrency/crash/replay evidence;
- consumer idempotency/reconciliation evidence;
- outbound retry/unknown-outcome evidence;
- provider rate/pagination/file-transfer evidence;
- privacy/log-redaction/retention evidence;
- Site Lifecycle/clone/restore evidence;
- EI1/EI2 physical and large-scale measurements;
- unsupported/degraded capabilities;
- final provider/profile certification and Event Inbox topology recommendation.

## 19. Current state

**WC fixtures documented: 156.**  
**WC fixtures executed: 0/156.**  
**Connection provider I4/I5 certifications: 0.**  
**Event Inbox runtime certifications: 0.**  
**Safe HTTP runtime certification: none.**  
**Final Event Inbox physical topology: open / evidence-gated.**

EI1/PT-D remains the first future benchmark baseline. EI2/PT-E remains a mandatory comparison before final topology selection.

No credential exchange, OAuth flow, DNS/HTTP request, webhook endpoint request, provider subscription, provider/API call, Event Inbox table/row, Job, Workflow dispatch, outbound webhook, migration, benchmark or runtime test has been executed.

## 20. Development gate

Execution requires explicit scoped owner consent under ADR-0014 / `DEVELOPMENT-CONSENT.md` / `docs/APPROVAL-LEDGER.md`, plus applicable P-001/P-003/P-005 environment, Job and Vault prerequisites.

Until then this protocol remains planning evidence only: `NOT EXECUTED`.