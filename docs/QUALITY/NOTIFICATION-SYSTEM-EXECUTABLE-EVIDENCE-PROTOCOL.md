# WPEssential — Notification System Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Work package: `P0-M00-WP04`  
Project state: `PLANNED_EXISTING_PROJECT`  
Execution mode: `PLANNER_ONLY`  
Governs: ADR-0026, ADR-0058, ADR-0079, Notification System contracts, Email delivery truth, JobService/P-003, Workflow/P-011, Event Bus/Event Inbox, Renderer/Conditions, Policy/Abilities, Membership, Query, Connections, Multisite/Site Lifecycle and ADR-0014.

## 1. Purpose

Define bounded executable evidence required before WPEssential can claim production-ready Notification rule triggering, recipient fan-out, in-app inbox/read state, preferences, quiet hours, digests, channel dispatch, dedupe, retries, provider delivery status, privacy, Multisite isolation or Notification scale.

This protocol tests the accepted Notification architecture. It does not turn Notification Rules into a second Workflow engine, Email into a Notification-owned renderer/provider stack, or transport/provider status into guessed delivery truth.

## 2. Canonical invariants

A future certified implementation must preserve:

1. **Notification Rule, logical Occurrence, per-recipient state and per-channel Delivery Attempt are separate domains.**
2. **Notification creation, queueing, provider acceptance, confirmed delivery, in-app read and human engagement are distinct facts.**
3. **`wp_mail()` or transport handoff is never called Delivered without certified provider evidence.**
4. **Notification routing does not reimplement arbitrary Workflow branching.**
5. **A notification/link never grants access to a protected target; target access is reauthorized when rendered/opened/acted upon where required.**
6. **Recipient resolution timing is explicit: trigger snapshot or delivery-time resolution.**
7. **User preferences, quiet hours, digests, frequency caps and required/security classification are policy decisions, not priority shortcuts.**
8. **Large audiences are fan-out/batched through JobService with bounded admission/backpressure.**
9. **Duplicate events/Jobs do not create duplicate logical notification or protected channel send contrary to the configured dedupe/idempotency contract.**
10. **Unknown provider outcome remains unknown until reconciliation/evidence resolves it; blind resend is prohibited where duplication is plausible.**
11. **Email Builder owns email-safe rendering and email provider truth; Connections owns webhook/provider security; Notification owns communication intent/routing/aggregation.**
12. **Site/network scope is explicit and cannot be selected by untrusted IDs, provider refs or recipient identifiers alone.**
13. **Secrets, raw provider payloads and unnecessary sensitive content are minimized/redacted in Notification state/logs.**
14. **Restore/clone never automatically resends terminal or ambiguous historical deliveries merely because operational rows were restored.**

## 3. Future certification profile

Every future evidence run records:
- WordPress/PHP/database versions accepted by P-001;
- single-site/Multisite topology;
- Notification Rule/Occurrence/Recipient/Delivery schema/profile version;
- physical topology under test (`NE1/PT-D`, `NE2/PT-E`, or later accepted profile);
- JobService/JS certification profile;
- Workflow/WF integration profile where used;
- Event Bus/Event Inbox profile;
- Email transport/provider ET certification profile for email fixtures;
- Connection/channel adapter certification profile;
- Policy/Ability/Query/Membership versions used for recipient resolution;
- site/user timezone and locale profiles;
- cache profile relevant to inbox/unread counts;
- retention/privacy configuration;
- reference audience/throughput workload.

No runtime execution occurs until explicit owner consent and applicable prerequisite evidence floors exist.

# 4. Rule lifecycle, revision and trigger fixtures

### NT-01 — Draft Rule does not fire
Knowing a Draft Rule UUID/key cannot make it process a real event.

### NT-02 — Publish validation
Publish rejects missing/unknown event versions, invalid recipient resolvers, unsafe channel configuration, missing required templates and unresolved dependencies.

### NT-03 — Stable Rule identity
Rule key/UUID remains stable across revisions while published revisions remain immutable historical references.

### NT-04 — Occurrence pins Rule revision
Triggered Occurrence stores the Rule revision that decided event filters, recipients/routing and dedupe semantics.

### NT-05 — Rule edited after trigger
Later Draft/publish does not silently reinterpret an already-created Occurrence.

### NT-06 — Content revision policy
Pinned-template vs explicitly configured latest-template-at-send behavior is deterministic and auditable.

### NT-07 — Rule disabled
No new triggers fire; already-created transactional Occurrences follow the configured keep/cancel policy.

### NT-08 — Rule archived/deleted
History remains interpretable and queued-impact behavior is explicit; destructive deletion cannot orphan required evidence silently.

### NT-09 — Typed WPE event trigger
Only registered event type/version/schema can trigger the Rule; arbitrary PHP hook string does not become executable subscription.

### NT-10 — Workflow action trigger
Workflow invokes a registered Notification action/Ability and cannot inject arbitrary provider callbacks or bypass Notification policy.

### NT-11 — Manual/admin send authorization
Manual send/test requires current server-side Ability/Policy and cannot be authorized by visible UI alone.

### NT-12 — Trigger filter evaluation
Conditions execute server-side with typed allowlisted data; raw PHP/JS expressions are rejected.

### NT-13 — Duplicate source event
Same logical source event + Rule/dedupe identity creates one logical Occurrence or explicit suppressed duplicate result.

### NT-14 — Same event ID, materially different payload
Conflict/anomaly is recorded; prior Occurrence is not silently overwritten or reinterpreted.

### NT-15 — Recursion/cycle guard
Notification-emitted events cannot recursively retrigger the same cycle without bounded explicit policy.

### NT-16 — Missing trigger dependency
Rule enters visible degraded/blocked state instead of silently losing events or binding an unrelated event.

# 5. Occurrence creation and dedupe fixtures

### NT-17 — Occurrence durability before fan-out
Occurrence identity/revision/source/correlation/dedupe state commits before downstream recipient/channel Jobs become authoritative.

### NT-18 — Concurrent duplicate occurrence race
Two workers handling the same logical trigger admit one Occurrence under certified idempotency semantics.

### NT-19 — Dedupe by event ID
Configured event-ID dedupe suppresses only the intended logical duplicate within declared scope/window.

### NT-20 — Custom dedupe key
Typed/allowlisted key mapping is deterministic and cannot access arbitrary secret/raw object data.

### NT-21 — Recipient-scoped dedupe
Recipient+Rule+entity/window dedupe cannot suppress another recipient's independent Notification.

### NT-22 — Channel-scoped dedupe
Channel-specific dedupe does not incorrectly erase an independent in-app/other-channel intent.

### NT-23 — Dedupe window expiry
A genuinely new occurrence after the configured window is not permanently suppressed by stale keys.

### NT-24 — Security notification broad-key guard
Security/access-revocation notification is not suppressed by an overly broad unrelated marketing/service dedupe key.

### NT-25 — Duplicate update-existing behavior
When configured, duplicate can update an existing in-app representation safely without rewriting immutable delivery history.

### NT-26 — Duplicate increment-counter behavior
Counter aggregation is atomic/concurrency-safe and cannot overflow/unboundedly grow a serialized record.

# 6. Recipient resolution and eligibility fixtures

### NT-27 — Specific user recipient
Resolved user identity is site/scope-authorized and deduplicated.

### NT-28 — Event actor/subject/owner recipient
Actor/subject/owner mapping uses canonical event identities, not attacker-controlled display fields.

### NT-29 — Role recipient resolution
Large role audience is paged/batched and respects current site membership/scope.

### NT-30 — Capability recipient resolution
Effective capability is evaluated through WordPress/WPE authority; stale cached role labels do not grant recipient eligibility.

### NT-31 — Query Builder recipients
Query is authorization/scoping-aware, bounded and does not expose arbitrary cross-site users.

### NT-32 — Relation-derived recipients
Relation cardinality/scope and endpoint authorization are respected during resolution.

### NT-33 — Membership/entitlement recipients
Authoritative Enrollment/Entitlement state controls eligibility; raw billing/provider status is insufficient.

### NT-34 — Team recipients
Team owner/manager/member resolution respects current Membership/team boundaries and revoke state.

### NT-35 — Registered custom recipient provider
Only registered typed provider contracts can produce recipients; arbitrary class/function names from Rule data cannot execute.

### NT-36 — External address recipient
External email/phone is allowed only for compatible channel policy with strict validation and no implied WordPress-user identity.

### NT-37 — Include/exclude actor
Actor inclusion/exclusion is deterministic after recipient dedupe.

### NT-38 — Max recipients/run guard
Audience expansion beyond configured/safety maximum stops/degrades safely instead of creating unbounded synchronous work.

### NT-39 — Resolve-at-trigger snapshot
Recipient set remains the event-time snapshot when Rule explicitly selects snapshot semantics.

### NT-40 — Resolve-at-delivery current state
Delayed delivery re-resolves mutable broad audience when Rule selects delivery-time semantics.

### NT-41 — Membership/access revoked before delivery
Access-dependent delayed Notification is suppressed/minimized when recipient is no longer eligible.

### NT-42 — User deleted before delivery
Pending user-scoped deliveries are suppressed/reconciled; no orphan address is guessed/reused.

### NT-43 — Missing/unverified channel address
Recipient/channel enters explicit suppressed/failed eligibility state; another user's/address's data is never substituted.

### NT-44 — Recipient resolution partial failure
Cursor/checkpoint/idempotency allows safe retry without duplicating already-materialized recipient records.

# 7. Preference, classification and frequency fixtures

### NT-45 — Required system/security class
Required classification follows explicit site/product policy and cannot be selected by ordinary marketing rule to bypass controls.

### NT-46 — Transactional/service class
Transactional rule is limited to the declared service/account purpose and records preference decision reason.

### NT-47 — Optional subscription opt-out
Optional category/channel opt-out suppresses delivery deterministically.

### NT-48 — Preference change before delayed send
Delivery-time policy uses the intended current/snapshotted preference semantics and records the decision reason.

### NT-49 — Preferred channel
Preferred-channel choice affects routing without altering the logical Occurrence truth.

### NT-50 — Missing preference
Documented site/default preference applies predictably; absence does not silently become mandatory consent.

### NT-51 — Frequency cap below threshold
Eligible message is admitted and counted in the correct recipient/category/channel window.

### NT-52 — Frequency cap exceeded
Configured suppress/digest/defer outcome occurs atomically under concurrent sends.

### NT-53 — Required security vs optional cap
Required/security policy is evaluated separately and cannot be accidentally dropped by optional marketing cap.

### NT-54 — Preference privacy isolation
User can read/update only own permitted preferences; admin-on-behalf requires separate high-privacy capability.

# 8. Quiet hours, scheduling and expiry fixtures

### NT-55 — Quiet-hours defer
Deferrable Notification computes next allowed instant from recipient timezone without busy polling.

### NT-56 — Quiet-hours overnight window
Window crossing midnight resolves correctly across local calendar dates.

### NT-57 — Recipient timezone missing
Documented site-timezone fallback is used and surfaced; arbitrary browser timezone is not trusted as canonical.

### NT-58 — DST spring-forward quiet-hour boundary
Nonexistent local time follows deterministic calendar policy without duplicate/looping defer.

### NT-59 — DST fall-back quiet-hour boundary
Repeated local time does not accidentally send twice.

### NT-60 — Critical bypass
Bypass happens only when Rule/classification policy explicitly permits and records reason.

### NT-61 — Expiry during quiet hours
Expired notification is not sent late unless product contract explicitly allows; historical suppression/expiry truth remains.

### NT-62 — Fixed delay
Occurrence remains durable while JobService schedules future eligibility; request thread never sleeps.

### NT-63 — Send-at timestamp
Stored due instant/timezone semantics remain unambiguous and compatible with JS/Cron evidence.

### NT-64 — Job lost/enqueue failure
Durable eligible/deferred state remains discoverable and can recreate execution opportunity without duplicate logical delivery.

# 9. Digest fixtures

### NT-65 — Digest disabled baseline
Notification remains individual and is not accidentally captured by global digest defaults.

### NT-66 — Hourly/daily/weekly grouping
Eligible items group by recipient/channel/window according to Rule/user policy.

### NT-67 — Recipient-local digest time
Digest scheduling uses intended timezone/DST semantics and does not duplicate a window.

### NT-68 — Max items + overflow
Digest respects item cap and produces bounded overflow summary/link without dropping underlying in-app history silently.

### NT-69 — Empty digest suppression
No empty channel send/job is created when no eligible items remain.

### NT-70 — Duplicate collapse in digest
Configured duplicate collapse preserves distinct logical/history evidence while rendering bounded summary.

### NT-71 — Read/revoked item before digest send
Eligibility is rechecked as configured; protected/revoked content is not leaked in delayed digest.

### NT-72 — Urgent/security bypass digest
Bypass only occurs under explicit classification/rule policy; ordinary priority flag cannot bypass digest/preferences.

### NT-73 — Digest send partial channel failure
Individual item read/access semantics remain independent from digest transport status.

### NT-74 — Digest retry
Retry does not regenerate a second logical digest/container or duplicate already-confirmed channel attempts contrary to idempotency evidence.

# 10. In-app inbox and read-state fixtures

### NT-75 — Recipient inbox authorization
User cannot list/read another user's Notification through forged recipient/notification IDs.

### NT-76 — Unread listing
Unread filter uses recipient state/indexes and does not scan message bodies/delivery attempt history.

### NT-77 — Mark read
Authorized recipient transition is idempotent and records read timestamp according to product contract.

### NT-78 — Mark unread
Allowed transition updates only current recipient's state without rewriting delivery/provider history.

### NT-79 — Mark all read
Bounded/batched server operation handles large inbox without N browser requests or cross-user leakage.

### NT-80 — Dismiss/archive
Dismiss changes inbox presentation state but does not falsely delete required audit/delivery evidence.

### NT-81 — Revoked/withdrawn Notification
Sensitive content can be hidden/revoked according to policy while retaining minimal safe historical evidence where required.

### NT-82 — Expired in-app Notification
Expired record visibility follows policy; expiry does not rewrite prior delivery/read facts.

### NT-83 — Unread count concurrency
Simultaneous create/read/dismiss operations produce bounded consistent badge/count semantics without cross-user cache contamination.

### NT-84 — Cached inbox isolation
Cache key includes principal/site/access-relevant dimensions; another user's unread/list content cannot be reused.

# 11. Content, rendering, localization and action fixtures

### NT-85 — Escaped in-app template tokens
Untrusted event/user/entity values cannot inject script/HTML outside the channel renderer's declared safe schema.

### NT-86 — Sensitive token denylist
Raw user meta/object dumps, credentials, reset tokens and protected fields cannot be selected through generic token syntax.

### NT-87 — Protected content at delivery/render time
Resource-sensitive content is reauthorized/minimized according to Rule timing policy before materialization.

### NT-88 — Action target local route
Local route/descriptor is validated and reauthorizes target access on open/action.

### NT-89 — Trusted external URL
Only allowed/validated external target policy is accepted; javascript/data/open-redirect payloads are rejected.

### NT-90 — Email rendering ownership
Email channel delegates to Email Builder/Email IR and does not render arbitrary Elementor/browser HTML as canonical mail.

### NT-91 — Webhook rendering ownership
Webhook channel uses typed Connections payload mapping/signing/auth; Notification does not expose raw secret/header injection.

### NT-92 — Locale selection
Recipient/site locale selection and fallback are deterministic and missing variant does not silently drop delivery.

### NT-93 — RTL/accessibility output
Supported in-app/email surface preserves semantic status/actions and usable RTL/keyboard/focus behavior where applicable.

# 12. Channel dispatch, fallback and delivery-truth fixtures

### NT-94 — Independent channels
Each configured independent channel has its own Delivery identity/status; one failure does not rewrite another channel's success.

### NT-95 — Ordered fallback on definitive failure
Fallback proceeds only when prior result is definitively eligible-for-fallback according to adapter semantics.

### NT-96 — No fallback on ambiguous async outcome
Unknown/accepted-but-unconfirmed status does not automatically trigger duplicate-prone fallback.

### NT-97 — `wp_mail()` success truth
Successful `wp_mail()` call is recorded as local handoff/accepted-level evidence only, never `delivered_confirmed`.

### NT-98 — Provider acceptance truth
API/SMTP acceptance maps only to the certified provider/transport fact; it does not imply inbox placement/read.

### NT-99 — Verified provider delivery event
`delivered_confirmed` is set only from applicable authenticated/idempotently processed certified provider evidence.

### NT-100 — Bounce/complaint/suppression after acceptance
Later facts are appended/derived without erasing earlier acceptance history or pretending a single monotonic boolean.

### NT-101 — Duplicate/out-of-order provider event
Event Inbox verification/dedupe/order-independent projection prevents state corruption or duplicate Notification event loops.

### NT-102 — Wrong-site provider/reference collision
Provider message/reference alone cannot bind an event to another site's Recipient Delivery.

### NT-103 — Provider timeout before request send
Retry is safe only when evidence proves no external side effect occurred.

### NT-104 — Provider timeout after possible acceptance
Delivery enters unknown/reconciliation policy; no blind resend where duplication is plausible.

### NT-105 — Stable provider idempotency key
Certified adapter reuses appropriate logical attempt identity across safe retries and reconciles provider result.

### NT-106 — 429/5xx/Retry-After
Backoff/rate policy is bounded, respects certified provider semantics and does not block unrelated Notification work indefinitely.

### NT-107 — Permanent invalid destination
Terminal invalid recipient/address does not retry indefinitely and can feed suppression policy safely.

### NT-108 — Manual delivery retry
Authorized operator retry creates an auditable new attempt against current eligibility/preconditions; backend/provider row is not edited directly.

# 13. Fan-out, JobService and backpressure fixtures

### NT-109 — 1-recipient control
Small Notification follows same durable Occurrence/Recipient/Delivery semantics without unnecessary fan-out ambiguity.

### NT-110 — 100-recipient batch
Bounded batch materialization and dispatch avoid one unbounded synchronous request loop.

### NT-111 — 10k-recipient fan-out
Cursor/checkpoint/idempotency keeps memory/query/Job count bounded and retry-safe.

### NT-112 — 100k-recipient fan-out
Producer backpressure/high-water policy prevents queue/table explosion while progress remains observable.

### NT-113 — Duplicate fan-out Job
Repeated batch Job does not duplicate Recipient rows or protected channel Delivery identities.

### NT-114 — Worker crash mid-recipient batch
Checkpoint/reconciliation resumes from durable boundary without missing or double-creating recipients.

### NT-115 — Worker crash after provider side effect before local commit
Adapter/domain unknown-outcome handling prevents blind duplicate send.

### NT-116 — JobService paused/backpressured
Occurrence/recipient state truthfully remains pending/deferred; system does not claim failed/completed merely because worker is unavailable.

### NT-117 — Mixed priority pressure
Security/urgent work follows configured JobService policy while normal/optional work cannot starve indefinitely under documented healthy capacity.

### NT-118 — Noisy recipient Query
Slow/large Query is bounded/observable and cannot monopolize request/worker/database resources indefinitely.

# 14. Security, privacy and audit fixtures

### NT-119 — Rule management authorization
Create/update/publish/enable/delete permissions are server-side and separated where specified.

### NT-120 — Test-send authorization
Test recipient/content send requires explicit capability and remains clearly separate from real event state.

### NT-121 — Delivery-log privacy
Ordinary log viewer cannot see unmasked sensitive recipient details or full private message bodies beyond policy.

### NT-122 — Provider Connection separation
Notification manager cannot obtain/change provider credentials without Connections/Vault authority.

### NT-123 — Secret/log redaction
Credentials, Authorization headers, reset/security tokens, full raw webhook payloads and unrelated private fields are absent from generic Notification logs.

### NT-124 — Privacy export
User export returns appropriate own Notification/read/preference history without other recipients' data.

### NT-125 — Privacy erase/anonymize
Configured erasable data is removed/anonymized while legally/operationally required minimal security/audit evidence can remain according to policy.

### NT-126 — Marketing classification misuse visibility
System makes classification explicit and auditable enough to detect misuse; it does not claim jurisdiction-specific legal compliance automatically.

# 15. Lifecycle, restore and Multisite fixtures

### NT-127 — Rule/Connection dependency disabled
Affected channel/rule enters visible degraded state; other safe channels may continue according to policy.

### NT-128 — Pro expiry
Editing/Pro-only authoring follows ADR-0007 while already-deployed necessary security/runtime behavior fails safe rather than leaking or silently changing classification.

### NT-129 — Site archived/deleted with queued work
Lifecycle drain/reconciliation blocks future delivery from mutating/sending under a deleted/wrong site identity.

### NT-130 — Site clone
Definitions may be intentionally copied/remapped, but historical recipient/delivery Jobs/provider refs are not blindly replayed.

### NT-131 — Backup/restore terminal delivery
Restoring previously accepted/confirmed/failed terminal records does not automatically resend them.

### NT-132 — Backup/restore ambiguous delivery
Unknown-outcome restored attempt re-enters reconciliation/manual policy before any retry.

### NT-133 — Per-site recipient isolation
Same user ID/email/provider ref in different sites cannot cross-resolve recipient/read/delivery state.

### NT-134 — Network-owned Notification policy
Any network-scoped Notification requires explicit network storage/authority/recipient semantics and cannot be simulated by arbitrary site switching.

# 16. Physical topology, cache and scale fixtures

### NT-135 — NE1/PT-D baseline
Measure shared scoped Occurrence/Recipient/Delivery stores, indexes, writes, unread queries and wrong-site predicates.

### NT-136 — NE2/PT-E mandatory comparison
Measure per-site table provisioning/migration/version skew/lifecycle/diagnostics fan-out and hot-site isolation.

### NT-137 — 100k/1M Recipient Notification history
Measure inbox/read/unread/retention/cleanup/query plans/storage growth without scanning bodies/attempt history for counts.

### NT-138 — Large delivery-attempt history
Measure status/log pagination, retry lookup, provider correlation and retention cleanup with bounded indexes.

### NT-139 — 100/1k/10k-site topology
Measure scope isolation, noisy-neighbor behavior, migrations, Backup/lifecycle and diagnostics for NE1 vs NE2.

### NT-140 — Cache invalidation scale
Bulk create/read/dismiss/preference changes keep unread/list cache correctness without cache stampede or cross-principal leakage.

### NT-141 — Mixed channel stress
In-app + email + webhook/Connection workload measures Job fairness, provider rate limits, retry amplification and partial-completion truth.

### NT-142 — Rule/event storm protection
High event rate with dedupe/frequency caps prevents uncontrolled Notification→event→Notification feedback and queue amplification.

# 17. MUST NOT / stop-the-line gates

Notification certification fails if any fixture demonstrates:
- notification possession or target URL acting as authorization to protected content/action;
- cross-user/site inbox, preference, recipient or Delivery IDOR;
- `wp_mail()`/SMTP/API handoff being labeled confirmed Delivered without applicable evidence;
- provider acceptance being represented as inbox placement or human read;
- read state being inferred from channel delivery/open/click facts;
- duplicate event/Job creating duplicate protected sends contrary to configured idempotency/dedupe semantics;
- blind retry/fallback after an ambiguous provider outcome where duplicate delivery is plausible;
- optional/marketing content bypassing preference/quiet-hours/frequency policy by self-labeling critical/system;
- unbounded synchronous recipient Query/fan-out or per-recipient request loop;
- Notification Rule becoming arbitrary Workflow/PHP/JS/class/function execution;
- arbitrary remote HTML/script or sensitive raw object/meta dump entering in-app/email content;
- provider credentials/raw secrets stored in Notification Rule/Occurrence/Recipient/Delivery/log data;
- provider event binding another site's delivery by unscoped reference;
- restore/clone blindly resending historical or ambiguous deliveries;
- site lifecycle allowing queued work to send/mutate after authoritative site deletion;
- unread counts/cache exposing another principal/site's state.

These are stop-the-line defects for the affected certification scope.

# 18. Performance evidence

Capture at minimum:
- trigger-to-Occurrence latency;
- dedupe/idempotency contention;
- recipient resolution throughput;
- recipients materialized per batch/Job;
- fan-out Job count and retry amplification;
- queue wait/oldest age by priority/channel/site;
- in-app inbox list latency;
- unread count latency and cache hit/invalidation behavior;
- mark-read/mark-all contention;
- quiet-hours/digest scheduling latency;
- provider attempt throughput and rate-limit behavior;
- Delivery status/event projection latency;
- DB query count/lock/index behavior;
- memory/CPU under 1/100/10k/100k recipients;
- 100k/1M history storage/cleanup throughput;
- one-hot-site noisy-neighbor effect;
- NE1/NE2 migration/provisioning/diagnostics cost.

Performance optimization must not weaken authorization, recipient correctness, preference/classification policy, idempotency, delivery truth, privacy or site isolation.

# 19. Required future Notification report

Include:
- exact environment/runtime/topology profile;
- NT-01…NT-142 pass/fail/NA with rationale;
- Rule/revision/trigger evidence;
- Occurrence/dedupe/concurrency evidence;
- recipient resolution/eligibility evidence;
- preference/classification/frequency/quiet-hours/digest evidence;
- inbox/read/cache evidence;
- renderer/action authorization evidence;
- channel/fallback/retry truth evidence;
- Email ET/Connection adapter evidence references;
- fan-out/JobService/backpressure evidence;
- security/privacy/redaction evidence;
- restore/clone/site-lifecycle evidence;
- NE1/NE2 physical/Multisite/scale measurements;
- unsupported/degraded profiles;
- final Notification physical/runtime recommendation.

## 20. Current state

**NT fixtures documented: 142.**  
**NT fixtures executed: 0/142.**  
**Notification runtime certified: none.**  
**Final Notification physical topology: open / evidence-gated.**

NE1/PT-D remains the first future benchmark baseline. NE2/PT-E remains a mandatory comparison before final topology selection.

Email/channel status claims remain bounded by their own ET/Connection/provider certification; Notification certification cannot upgrade an uncertified channel's evidence level.

No Notification table/row, Rule trigger, recipient fan-out, inbox mutation, Job execution, digest, email/webhook/provider call, migration, benchmark or runtime test has been executed.

## 21. Development gate

Execution requires explicit scoped owner consent under ADR-0014 / `DEVELOPMENT-CONSENT.md` / `docs/APPROVAL-LEDGER.md`, plus applicable P-001/JS/WF/channel prerequisites.

Until then this protocol remains planning evidence only: `NOT EXECUTED`.