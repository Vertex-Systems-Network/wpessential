# WPEssential — Resource Scheduling & Reservation Executable Evidence Protocol

Status: **Accepted evidence design candidate / execution pending / no development authorization**  
Date: **2026-08-29**

Namespace: **RSV-001…RSV-176**  
Documented: **176/176**  
Executed: **0/176**  
Runtime certification: **0**

## 1. Purpose

This protocol fixes the executable evidence required before F06 — Resource Scheduling, Availability & Reservation Engine can be called runtime-ready.

F06 owns resource/calendar definitions, availability derivation and atomic reservation lifecycle for explicitly configured scheduling profiles. Availability is advisory derived state until a reservation/hold transition succeeds atomically. A reservation is not a payment settlement, order, entitlement, ledger hold, external-calendar fact or authorization merely because related systems are connected.

No fixture below has executed. No resource table, availability evaluator, recurrence engine, hold, booking transaction, provider/calendar sync, payment call, benchmark, AI/MCP call or runtime mutation is authorized by this protocol.

## 2. Non-negotiable truth boundaries

- `Availability result ≠ Reservation`.
- `Hold ≠ Confirmed booking`.
- `Reservation ≠ Payment / Order / Entitlement / F05 ledger hold / Policy`.
- `External calendar busy/free ≠ WPE reservation truth` unless explicitly imported/linked under a certified source contract.
- `Waitlist position ≠ Reservation`.
- `Availability cache ≠ booking authority`; final mutation revalidates current resource/capacity state.
- `Payment success ≠ reservation success`, and reservation success does not prove payment settlement.
- `Unknown external/payment/calendar outcome ≠ failed`; reconciliation precedes replay where duplicate effects are possible.
- Resource capacity is scheduling capacity, not a financial/quantity ledger balance.
- Canonical instants are unambiguous; local-wall-clock recurrence semantics require explicit timezone/DST behavior.
- Cross-site/tenant ownership is server-resolved and durable; caller-supplied site IDs never grant authority.
- AI/MCP may draft schedules, explain conflicts and suggest alternatives only through normal Policy and atomic-booking gates.

## 3. Certification classes

- `RSV-SCH` — resource/calendar/capacity schema and lifecycle.
- `RSV-TIM` — timezone, DST, recurrence, blackout and holiday semantics.
- `RSV-AVL` — availability derivation, duration and buffers.
- `RSV-RES` — hold, confirm, release and expiry atomicity.
- `RSV-CAP` — capacity pools and multi-resource semantics.
- `RSV-LIF` — reschedule, cancel, no-show and extension lifecycle.
- `RSV-EXT` — payment/approval prerequisites and external outcomes.
- `RSV-CON` — concurrency, crash safety and overbooking defense.
- `RSV-WAI` — waitlist, alternatives and promotion policy.
- `RSV-SEC` — permissions, private calendars and privacy.
- `RSV-SYN` — external calendar/provider connector synchronization.
- `RSV-CCH` — cache, invalidation and stale-availability defense.
- `RSV-MSI` — Multisite/location/tenant isolation.
- `RSV-REC` — backup, restore, clone and site lifecycle continuity.
- `RSV-PERF` — scale, hot-resource concurrency and backpressure.
- `RSV-DET` — end-to-end deterministic golden/regression suites.

Passing one class never implies another. Runtime readiness requires every class applicable to the enabled scheduling profile and its integrations.

# Group 1 — Resource/calendar/capacity schema — RSV-001…RSV-011

- **RSV-001** Valid Resource Definition publishes with stable key, immutable revision identity, explicit site/network ownership and scheduling profile.
- **RSV-002** Duplicate stable resource key with incompatible ownership/schema requires explicit fork/migration and is never silently replaced.
- **RSV-003** Resource type/category and supported booking mode are enum/schema validated before activation.
- **RSV-004** Scheduling timezone must be a valid explicit IANA timezone where local schedules are used; server/PHP timezone is not implicit authority.
- **RSV-005** Capacity must satisfy the resource profile's numeric domain; zero/negative capacity is rejected unless a specifically modeled unavailable state uses separate semantics.
- **RSV-006** Fractional capacity is prohibited unless the resource profile explicitly supports typed fractional units and precision rules.
- **RSV-007** Location, owner/provider and related entity references are typed/provenanced and do not grant booking authority merely because a caller supplies their IDs.
- **RSV-008** Booking horizon, minimum notice, minimum/maximum duration and interval/granularity constraints are validated for internal consistency.
- **RSV-009** Active/paused/maintenance/archived resource lifecycle states expose deterministic effects on new availability and existing reservations.
- **RSV-010** Export/import preserves resource IDs, revisions, ownership and schedule definitions without provider credentials or unrelated private booking data unless explicitly included.
- **RSV-011** Archive/delete performs dependency and future-reservation review and never silently deletes reservation history or external mappings.

# Group 2 — Timezone/DST/recurrence/blackout/holiday — RSV-012…RSV-022

- **RSV-012** Weekly recurring availability generates the expected local-wall-clock intervals in the resource timezone.
- **RSV-013** Recurrence generation is bounded by configured horizon/occurrence count and rejects unbounded pathological rules.
- **RSV-014** Resource timezone/rule revision changes future occurrence semantics according to explicit effective-date policy without rewriting historical reservation instants.
- **RSV-015** DST spring-forward nonexistent local times follow an explicit reject/skip/shift profile and are never mapped unpredictably.
- **RSV-016** DST fall-back ambiguous local times use a deterministic fold/offset profile so repeated execution selects the same canonical instant.
- **RSV-017** Overnight availability crossing local midnight resolves start/end dates and DST boundaries correctly.
- **RSV-018** Availability rule effective-from/effective-to boundaries use declared inclusive/exclusive semantics at exact instants.
- **RSV-019** Blackout/maintenance intervals suppress matching recurring availability using an explicit, testable precedence rule.
- **RSV-020** Holiday calendars carry timezone, source/provenance and revision/effective dates; an unknown/stale holiday source cannot silently alter current availability.
- **RSV-021** Overlapping availability/exception rules resolve by explicit priority/merge semantics and produce conflict diagnostics where ambiguous.
- **RSV-022** Recurrence, timezone, blackout or holiday revision changes fingerprint/invalidate all affected derived availability and cache state.

# Group 3 — Availability computation/duration/buffers — RSV-023…RSV-033

- **RSV-023** Basic availability query returns only intervals permitted by the active resource calendar/rules for the requested canonical range.
- **RSV-024** Requested duration must fit one continuous eligible interval; fragmented gaps cannot be combined unless the profile explicitly supports split booking.
- **RSV-025** Pre-buffer consumes resource availability/capacity for conflict purposes while remaining distinguishable from customer-visible service time.
- **RSV-026** Post-buffer consumes resource availability/capacity for conflict purposes and is applied once at exact interval boundaries.
- **RSV-027** Minimum-notice constraint is enforced from the current canonical instant, not browser clock or display timezone.
- **RSV-028** Maximum booking horizon rejects slots beyond the declared future boundary using deterministic timezone semantics.
- **RSV-029** Existing confirmed reservations subtract the configured capacity across service plus applicable buffer intervals.
- **RSV-030** Active unexpired reservation holds subtract capacity according to hold profile without being reported as confirmed bookings.
- **RSV-031** Canceled/released/expired states restore capacity exactly according to their state and effective transition, not merely UI status text.
- **RSV-032** Query/display timezone changes presentation only and does not change canonical slot identity or create duplicate availability.
- **RSV-033** Availability response carries enough rule/resource/freshness identity to identify it as advisory derived state; it cannot be used as proof that later confirmation must succeed.

# Group 4 — Atomic hold/confirm/release/expiry concurrency — RSV-034…RSV-044

- **RSV-034** Hold creation atomically claims provisional capacity and returns stable hold identity, expiry and resource/slot fingerprint.
- **RSV-035** Replaying the same normalized hold request/idempotency identity returns the same hold outcome rather than consuming capacity twice.
- **RSV-036** Simultaneous holds for the last unit of capacity cannot collectively exceed resource capacity.
- **RSV-037** Hold confirmation verifies the exact hold is active, owned/authorized, unexpired and compatible with the confirmation request.
- **RSV-038** Confirmation retry is idempotent and cannot create duplicate confirmed reservations from one logical hold.
- **RSV-039** Hold release retry is idempotent and cannot restore capacity more than once.
- **RSV-040** Expiry transition releases provisional capacity exactly once even if multiple expiry workers/jobs observe the same hold.
- **RSV-041** Confirm-vs-expire race resolves atomically to one permitted terminal outcome with no confirmed booking plus released duplicate capacity.
- **RSV-042** Release-vs-confirm race resolves atomically under declared state-transition rules and never yields two contradictory successes.
- **RSV-043** A stale availability/search result cannot bypass current capacity and rule validation at the final hold/confirm boundary.
- **RSV-044** Mutation resolves actor, site/tenant, resource and reservation ownership server-side; client-supplied identity dimensions cannot widen authority.

# Group 5 — Capacity >1/shared pools/multi-resource requirements — RSV-045…RSV-055

- **RSV-045** Capacity-greater-than-one resource atomically consumes the requested number of units for a reservation.
- **RSV-046** Request exceeding remaining eligible capacity is rejected at mutation time even if an earlier availability search showed enough capacity.
- **RSV-047** Fractional requested capacity is accepted only by an explicitly fractional resource profile and obeys its precision/unit constraints.
- **RSV-048** Shared capacity pool across multiple resources enforces one aggregate pool limit in addition to per-resource limits where configured.
- **RSV-049** A reservation consuming multiple units from the same shared pool applies one atomic aggregate allocation and releases it exactly once.
- **RSV-050** Multi-resource all-required booking either secures every required resource under certified atomic semantics or enters an explicit safe compensating/not-confirmed state.
- **RSV-051** If any mandatory resource becomes unavailable during multi-resource booking, the system cannot report the resource set as fully confirmed.
- **RSV-052** Alternative/substitutable resources may replace a required resource only when compatibility rules explicitly permit the substitution.
- **RSV-053** Simultaneous person+room+equipment or equivalent grouped requirements enforce every per-resource and shared-pool constraint.
- **RSV-054** Concurrent allocations against one shared capacity pool cannot oversubscribe aggregate capacity under hot contention.
- **RSV-055** Capacity/rule revision affects future availability according to effective-date policy and does not retroactively rewrite confirmed historical allocation truth.

# Group 6 — Reschedule/cancel/no-show/extension — RSV-056…RSV-066

- **RSV-056** Reschedule validates/claims the new slot before relinquishing the old reservation, or uses an explicitly safe compensating transition; it does not blindly cancel-then-hope.
- **RSV-057** Successful atomic reschedule releases old capacity exactly once and confirms new capacity exactly once with linked history.
- **RSV-058** Failed reschedule preserves the old valid reservation when the selected policy claims atomic rescheduling.
- **RSV-059** Cancellation follows a valid lifecycle transition, preserves history and releases capacity exactly once at the configured point.
- **RSV-060** Cancellation retry is idempotent and cannot trigger repeated release/provider/payment effects.
- **RSV-061** Cancellation cutoff, fee/penalty or refund metadata remains a consumer-commerce/Policy concern unless explicitly owned; F06 does not invent or silently charge money.
- **RSV-062** No-show state preserves reservation history and applies capacity/lifecycle semantics appropriate to an elapsed booking rather than pretending prior capacity never existed.
- **RSV-063** Extension checks future interval/resource/shared-pool capacity atomically before extending a confirmed reservation.
- **RSV-064** Failed/partial extension leaves the original reservation interval valid and does not leak partially claimed future capacity.
- **RSV-065** Check-in/check-out or fulfillment markers may advance lifecycle but cannot retroactively create overlapping availability for already elapsed reserved time.
- **RSV-066** Every reschedule/cancel/no-show/extension transition retains prior state, actor/source, reason, timestamps and linked Audit evidence without silently rewriting reservation identity.

# Group 7 — Payment/approval external prerequisite reconciliation — RSV-067…RSV-077

- **RSV-067** Reservation profile requiring approval cannot reach confirmed state before the required authorized approval is valid.
- **RSV-068** Approval binds to the exact reservation/hold identity, resource/time fingerprint and validity window so approval cannot be replayed to another slot.
- **RSV-069** Successful payment authorization does not force confirmation if the slot/hold has expired or current capacity can no longer be legally confirmed.
- **RSV-070** Reservation confirmation does not imply provider capture/settlement/order completion; external/payment state remains separately authoritative.
- **RSV-071** Payment/provider timeout after a possible mutation yields explicit pending/unknown reconciliation state rather than automatic failure/replay.
- **RSV-072** Retry after an unknown prerequisite outcome first reconciles provider/idempotency/source identity before sending a potentially duplicating request.
- **RSV-073** Confirmed payment failure may release a hold only according to declared policy and releases it no more than once.
- **RSV-074** Approval revoked/expired before reservation confirmation blocks confirmation unless a separately governed exception applies.
- **RSV-075** External prerequisite callback/webhook must authenticate/validate connector source and cannot supply actor/site authority by payload alone.
- **RSV-076** Duplicate and out-of-order prerequisite events reconcile through stable source event/version identity rather than arrival order alone.
- **RSV-077** Reservation/prerequisite logs redact secrets, payment credentials and prohibited provider payload fields while retaining safe reconciliation references.

# Group 8 — Overbooking prevention/crash/job delay — RSV-078…RSV-088

- **RSV-078** Multiple concurrent contenders for the final unit of capacity produce no more confirmed capacity than allowed.
- **RSV-079** Database/transaction deadlock or serialization conflict uses bounded retry while preserving one logical reservation idempotency identity.
- **RSV-080** Crash after durable capacity/reservation commit but before client response resolves through operation identity and cannot duplicate the booking on retry.
- **RSV-081** Crash before durable commit cannot leave a phantom reservation labelled confirmed or permanently consumed capacity.
- **RSV-082** Delayed hold-expiry jobs do not let already-expired holds block capacity indefinitely when authoritative expiry is checked during current allocation.
- **RSV-083** Delayed provider/calendar sync exposes lag/freshness and does not claim a stale remote view is current authoritative availability.
- **RSV-084** Host clock skew/PHP timezone changes do not alter already-recorded canonical hold expiry/booking instants.
- **RSV-085** Duplicate reservation/expiry/waitlist jobs are idempotent and cannot double release capacity or promote the same entitlement twice.
- **RSV-086** Lock loss/timeout/transaction-unknown state fails safe and is reconciled before reporting available/confirmed in a way that could overbook.
- **RSV-087** Hot-resource contention uses bounded retries/fairness policy and does not claim starvation-free behavior until evidence demonstrates it.
- **RSV-088** Health distinguishes availability-cache staleness, provider-sync lag, expiry backlog, contention and mutation failure from ordinary no-availability results.

# Group 9 — Waitlist/alternatives/priority policy — RSV-089…RSV-099

- **RSV-089** Waitlist entry is a separate state/entity and never represents a confirmed reservation.
- **RSV-090** Repeated equivalent waitlist enrollment obeys idempotency/duplicate policy and does not create unintended multiple priority positions.
- **RSV-091** Waitlist priority profile is deterministic, versioned and bounded to approved typed factors/Policy.
- **RSV-092** Equal priority resolves using a stable documented tie-break rather than non-deterministic database order.
- **RSV-093** Promotion re-evaluates current eligibility, Policy, resource state and capacity instead of trusting the conditions that existed when waitlisted.
- **RSV-094** Promotion normally creates a bounded offer/hold under its declared profile and does not silently create an unauthorized confirmed booking.
- **RSV-095** Expired/declined promotion offer releases capacity exactly once and safely advances to the next eligible candidate where policy allows.
- **RSV-096** Canceled/removed/ineligible waitlist entries cannot later be promoted by delayed jobs.
- **RSV-097** Alternative-slot suggestions are read-only/advisory and are recomputed/revalidated before a mutation.
- **RSV-098** Alternative-resource suggestion returns only compatible resources within the caller's authorized resource/query scope.
- **RSV-099** Waitlist position/count/status views follow privacy Policy and do not reveal private participant identities or protected resource cardinality.

# Group 10 — Resource permissions/private calendars/data minimization — RSV-100…RSV-110

- **RSV-100** View-resource, view-availability, view-private-event, create-hold, confirm, reschedule, cancel and administer permissions are independently enforceable.
- **RSV-101** Private calendar can expose a busy/unavailable block without leaking event title, customer, notes or unrelated private metadata.
- **RSV-102** Unauthorized callers cannot enumerate hidden resources or protected exact availability/cardinality beyond the configured disclosure policy.
- **RSV-103** Resource owner/provider relationship does not bypass site/tenant/resource Policy when acting through public/admin/API surfaces.
- **RSV-104** Reservation stores only participant/customer PII required by the configured booking workflow and uses references where another domain owns identity data.
- **RSV-105** Internal notes, provider metadata and staff-only fields are redacted from public availability/reservation representations.
- **RSV-106** REST, Ability, Workflow and MCP booking paths apply the same server-side Policy as the primary UI and have no privileged hidden mutation route.
- **RSV-107** Staff/delegated booking records acting principal separately from customer/subject so impersonation is not inferred from booking ownership.
- **RSV-108** Authority to create a reservation does not automatically grant authority to cancel/reschedule another actor's reservation.
- **RSV-109** Calendar feed/export/share access is scoped, revocable and does not expose a permanent bearer capability wider than configured Policy.
- **RSV-110** Audit/log/diagnostic surfaces redact sensitive customer/provider data while retaining safe actor/resource/reservation correlation identifiers.

# Group 11 — Calendar/provider connector conflict/sync — RSV-111…RSV-121

- **RSV-111** Calendar connector declares certified read/write, recurrence, busy/free, webhook/poll, version/ETag and rate-limit capabilities; unsupported semantics are not assumed.
- **RSV-112** External busy/event record stores stable provider connection/calendar/event identity and provider revision/version where available.
- **RSV-113** Imported external busy block affects WPE availability under its source policy without pretending it is a native confirmed WPE reservation.
- **RSV-114** Pushing a WPE reservation to a provider uses stable source/idempotency mapping so retries cannot create duplicate remote events where the provider permits prevention.
- **RSV-115** Remote version/ETag conflict yields explicit conflict/reconciliation instead of blind last-write overwrite.
- **RSV-116** Remote deletion/modification conflicting with an active local reservation follows configured source-authority/conflict policy and surfaces unresolved truth.
- **RSV-117** Duplicate webhook and overlapping poll observation of the same provider revision are deduplicated.
- **RSV-118** Out-of-order provider updates compare source version/change token/timestamps under connector semantics rather than blindly applying arrival order.
- **RSV-119** Origin/source markers prevent local→remote→local echo loops and repeated no-op writes.
- **RSV-120** Provider outage, quota or rate limit uses bounded backoff and exposes synchronization lag; local UI cannot claim remote calendar freshness it does not have.
- **RSV-121** Connector credential rotation uses Vault references without logging secret material and revalidates connector scope/capability before resumed writes.

# Group 12 — Cache/invalidation/availability stale defense — RSV-122…RSV-132

- **RSV-122** Availability cache key includes all required resource/rule/site/time-range/timezone/duration/capacity dimensions that can change eligible output.
- **RSV-123** Confirmed reservation mutation invalidates or namespaces all affected resource/pool availability intervals.
- **RSV-124** Hold create, release, confirm and expiry invalidate affected availability/capacity state correctly.
- **RSV-125** Resource capacity, recurrence, blackout, buffer, holiday or effective-rule revision invalidates dependent cached availability.
- **RSV-126** External busy/free provider update invalidates affected cached intervals according to source mapping and freshness policy.
- **RSV-127** Protected availability cache includes all required actor/Policy/tenant disclosure dimensions to prevent cross-user leakage.
- **RSV-128** Cache hit is never sufficient authority for confirmation; final hold/confirm transaction revalidates current rules, ownership and capacity.
- **RSV-129** Stale-while-revalidate response, if supported, is explicitly labelled with freshness/version and cannot be represented as guaranteed bookable.
- **RSV-130** Cache backend outage may degrade performance but cannot widen permissions or skip atomic availability checks.
- **RSV-131** Negative/no-availability cache uses bounded TTL/invalidation so newly released capacity is not hidden indefinitely.
- **RSV-132** Site/resource-specific cache clear/invalidation cannot flush or expose sibling-tenant protected entries unless explicitly network scoped.

# Group 13 — Multisite/location/tenant isolation — RSV-133…RSV-143

- **RSV-133** Site-scoped resource and reservation retain durable site ownership independent of current WordPress blog context.
- **RSV-134** `switch_to_blog()` or stale request context cannot read/mutate a sibling site's protected reservations through reused cache/resource identity.
- **RSV-135** Site administrator cannot book/query another site's protected resource merely by supplying a site/resource ID.
- **RSV-136** Network-shared resource requires explicit network ownership/profile and does not emerge accidentally from duplicate site definitions.
- **RSV-137** Network availability/query resolves the authorized site/tenant/location set server-side before aggregating results.
- **RSV-138** Same resource key/ID-like value on different sites cannot collide because composite ownership identity is explicit.
- **RSV-139** Cross-site shared-capacity pool, if supported, has explicit network authority, atomic aggregate semantics and per-site disclosure Policy.
- **RSV-140** New-site provisioning from a network template copies definitions/rules, not another site's live reservations, waitlists or customer data.
- **RSV-141** Site archive/delete/quarantine applies a governed future-booking/external-sync lifecycle without affecting sibling resources/reservations.
- **RSV-142** Site clone creates new ownership/reservation/resource identities and does not inherit live provider event mappings or writable production connections implicitly.
- **RSV-143** Noisy/high-volume site or location is subject to configured network resource/query/job quotas so it cannot starve every sibling tenant.

# Group 14 — Backup/restore/clone/site lifecycle — RSV-144…RSV-154

- **RSV-144** Backup profile includes selected resource definitions, rules, reservation state and provider mapping metadata with versioned manifest/checksums.
- **RSV-145** Same-environment restore preserves intended historical reservation identity/state and validates resource references.
- **RSV-146** Restoring an older backup never claims to roll back external calendar/payment/approval providers; external state remains separately authoritative.
- **RSV-147** Post-restore startup requires reconciliation of external mappings/change cursors and quarantines ambiguous provider state before unsafe writes.
- **RSV-148** Clone/staging environment marks production calendar/payment/provider mappings detached, read-only or quarantined by default according to environment policy.
- **RSV-149** Clone cannot push duplicate external events/reservations until an explicit safe rebind and environment authority check succeeds.
- **RSV-150** Restored holds are evaluated against current canonical time before they can continue blocking capacity; already-expired holds cannot be resurrected blindly.
- **RSV-151** Restore verifies capacity invariants, duplicate reservation identity and interval conflicts before declaring scheduling health.
- **RSV-152** Missing/corrupt/partial restored scheduling data produces degraded/not-ready state instead of serving potentially overbookable availability as healthy.
- **RSV-153** Site/domain/environment move preserves canonical resource/reservation identity unless an explicit mapping/migration plan changes ownership.
- **RSV-154** Module/site disable/uninstall follows preservation/export policy, stops new mutations safely and does not silently delete unresolved future bookings/provider links.

# Group 15 — High-volume slot/resource concurrency benchmarks — RSV-155…RSV-165

- **RSV-155** 10K-resource availability search workload records query count, latency, memory and correctness against a fixed rule/reservation dataset.
- **RSV-156** 100K-resource filtered/location/category availability workload remains within declared bounds or is truthfully classified unsupported/degraded.
- **RSV-157** 1M-reservation indexed time-range/conflict lookup demonstrates bounded query plans and exact result correctness for declared backend profile.
- **RSV-158** Hot single-resource benchmark with at least 100 concurrent last-slot hold attempts proves confirmed/held capacity never exceeds the configured limit.
- **RSV-159** Shared-pool high-contention benchmark proves aggregate pool capacity cannot be oversubscribed across member resources.
- **RSV-160** Multi-resource reservation fanout measures lock/transaction cost and proves no false all-confirmed state under partial contention.
- **RSV-161** Hold-expiry storm benchmark proves bounded throughput/backlog behavior and exactly-once capacity release semantics.
- **RSV-162** Waitlist promotion backlog benchmark proves bounded job behavior, deterministic ordering and no duplicate offers/confirmations.
- **RSV-163** Provider sync backlog/rate-limit benchmark demonstrates bounded backpressure/retry and truthful freshness lag without uncontrolled queue growth.
- **RSV-164** Cold/warm availability cache benchmarks compare latency while producing equivalent authorized availability truth for the same versioned state.
- **RSV-165** Declared workload profile demonstrates no unbounded N+1/resource-per-slot query explosion; if limits are exceeded, health/UX exposes them rather than hiding degradation.

# Group 16 — Booking/rental/delivery golden/regression suite — RSV-166…RSV-176

- **RSV-166** Golden fixed appointment: one resource, one timezone, notice/buffer rules, hold→confirm→cancel lifecycle yields the exact expected intervals/capacity/history.
- **RSV-167** Golden rental/day-boundary profile validates overnight/multi-day duration, buffers, local date boundaries and exact release semantics.
- **RSV-168** Golden capacity class/session with 20 seats and concurrent clients proves exact remaining capacity, final-slot contention and no oversubscription.
- **RSV-169** Golden multi-resource service requiring staff+room+equipment proves atomic all-required booking, safe failure and deterministic explanation.
- **RSV-170** Golden recurring availability across DST spring-forward and fall-back produces the documented local-wall-clock/canonical-instant outcomes on repeated runtimes.
- **RSV-171** Golden blackout/holiday/maintenance precedence suppresses only intended occurrences and invalidates affected caches deterministically.
- **RSV-172** Golden payment-authorization timeout/unknown-outcome flow proves no blind provider retry, no false confirmation and correct reconciliation-driven continuation/release.
- **RSV-173** Golden external-calendar import/push/conflict flow proves source mapping, dedupe, version conflict, lag reporting and echo-loop prevention.
- **RSV-174** Golden cancel→reschedule→waitlist-promotion scenario proves capacity is released/claimed once, priority is stable and offers do not imply confirmation.
- **RSV-175** Golden Multisite scenario proves site-private vs explicit network-shared resources, capacity pools, provider mappings and caches do not leak across tenants.
- **RSV-176** AI/MCP/adversarial regression proves a hallucinated slot, forged site/resource ID, replayed provider callback, stale cache result or suggested override cannot bypass Policy, current capacity, provider reconciliation or atomic confirmation semantics; repeated valid inputs remain deterministic.

## 4. Stop-the-line failures

Any applicable fixture failure in the following classes blocks runtime certification for the affected scheduling profile:

- overbooking or capacity oversubscription;
- cross-user/site/tenant reservation or private-calendar leakage;
- stale cache/search result accepted as final booking authority;
- payment/provider result represented as reservation truth without the reservation transition, or vice versa;
- blind replay after an unknown external outcome;
- DST/recurrence ambiguity producing non-deterministic canonical instants;
- multi-resource booking reported confirmed while a required allocation failed;
- duplicate release/expiry/promotion causing extra capacity or duplicate booking;
- restore/clone writing into production external calendars/providers without explicit rebind;
- AI/MCP bypass of Policy, atomic capacity checks or approval/prerequisite gates.

## 5. Evidence artifact requirements

Future execution evidence must retain, as applicable:

- fixture ID and scheduling profile/version;
- resource/rule/reservation/hold revision fingerprints;
- canonical instants plus display timezone/DST-fold inputs;
- capacity/pool before/after state and transaction identity;
- idempotency/source/provider mapping identifiers with secret redaction;
- Policy/actor/site/tenant decision metadata;
- cache/freshness/provider-sync revision state;
- concurrency worker/result traces sufficient to prove no overbooking;
- expected vs actual result and deterministic diff;
- benchmark dataset/workload/profile and latency/query/memory/backlog metrics;
- retained logs/artifacts/checksums required to reproduce the claim.

A planning document, UI screenshot or manually asserted result is not executed evidence.

## 6. Current truth

- RSV fixture text: **176/176 documented**.
- RSV executed evidence: **0/176**.
- F06 runtime certification: **0**.
- Product scope remains **56/56 planned**.
- Implementation authorization remains **0/56**.

No resource/reservation runtime, provider/calendar call, payment call, benchmark, AI/MCP call, database mutation, plugin code or test execution occurred while creating this protocol.
