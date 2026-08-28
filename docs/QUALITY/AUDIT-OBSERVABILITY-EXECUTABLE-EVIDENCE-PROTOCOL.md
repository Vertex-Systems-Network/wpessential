# WPEssential — Audit & Observability Executable Evidence Protocol

Status: **Phase 0 evidence specification / NOT AUTHORIZED FOR EXECUTION**  
Date: 2026-08-28  
Work package: `P0-M00-WP25`  
Related: ADR-0014, ADR-0060, ADR-0069, ADR-0071, ADR-0075, ADR-0081, `docs/ARCHITECTURE/AUDIT-PTD-RETENTION-INDEX-INTEGRITY-PROFILE.md`, Multisite, Site Lifecycle, Policy/Abilities, JobService, Workflow, Event Inbox, Backup/Restore, Platform Diagnostics.

## 1. Purpose

This is the canonical future executable-evidence contract for WPEssential Audit and its observability boundary.

It verifies that meaningful administrative/security actions can be explained without turning Audit into:
- an unbounded debug log;
- a copy of every domain history table;
- a generic provider payload dump;
- an authorization bypass;
- a cross-site information leak;
- a false “tamper-proof” claim;
- a permanent personal-data warehouse.

The protocol freezes **AUD-01…AUD-176**.

**Executed: 0/176.**

No Audit table, logger, migration, hash chain, external checkpoint, privacy action, site lifecycle action, provider call, queue execution, benchmark or runtime test is authorized by this document.

---

## 2. Truth boundaries

The following are separate truths:

`Audit Event ≠ Domain History ≠ Operational Diagnostic ≠ Security Alert ≠ Request Trace ≠ Job Attempt ≠ Workflow Run ≠ Event Inbox record ≠ Provider log ≠ analytics event ≠ immutable external evidence`

Audit may correlate/link those domains. It must not silently become their canonical storage.

A locally stored Audit row does **not** prove cryptographic non-repudiation or resistance to a privileged DB/server attacker.

---

## 3. First physical baseline

`AU1 / PT-D` remains the favored first future benchmark profile under ADR-0081:
- shared scoped append-oriented Audit store;
- explicit network/site ownership;
- structured query/index families;
- retention-class-aware purge eligibility;
- bounded metadata document;
- correlation/causation links.

AU1 is a **paper baseline only**.

Still OPEN/evidence-gated:
- exact DDL and data types;
- exact indexes/order/cardinality;
- retention durations;
- per-Ability mandatory/fail-closed Audit policy;
- metadata size limits;
- export limits;
- purge batch limits;
- optional tamper-evidence profile;
- external checkpoint/immutability service;
- performance thresholds.

---

## 4. Certification classes

Certify independently:

- `AUD-M` — event model, identity, scope and classification;
- `AUD-W` — write/commit/failure/idempotency semantics;
- `AUD-A` — read authorization, filtering and export authorization;
- `AUD-P` — privacy, redaction, minimization and secret exclusion;
- `AUD-C` — correlation/causation and domain-boundary correctness;
- `AUD-R` — retention, purge, erasure, hold and lifecycle policy;
- `AUD-I` — integrity/provenance/restore/migration truth;
- `AUD-S` — Multisite/site-lifecycle isolation;
- `AUD-Q` — query/index/performance/scale behavior;
- `AUD-O` — diagnostics/incident/operational observability boundary.

Passing one class never implies another.

---

# 5. Fixed executable fixture matrix

## A. Event model, identity, scope & classification — AUD-01…AUD-16

### AUD-01 — Stable Audit event identity
Create one logical event and verify stable UUID/internal identity, no collision and deterministic references from related domains.

### AUD-02 — Explicit site scope
Site-owned event persists explicit network/site ownership independent of current blog context.

### AUD-03 — Explicit network scope
Network-owned event does not invent site ownership or silently inherit current site.

### AUD-04 — Invalid scope rejection
Missing, impossible or mismatched network/site coordinates fail safely; no fallback to current site.

### AUD-05 — Actor class: user
Authorized user action records safe user reference and relevant authority context without credential/session leakage.

### AUD-06 — Actor class: system
System-triggered action is distinguishable from a human actor.

### AUD-07 — Actor class: Job/worker
Job-originated event links Job/Attempt identity without copying full Job payload.

### AUD-08 — Actor class: remote service
Remote-service-originated administrative/reconciliation event records trusted service identity/profile, not arbitrary supplied actor text.

### AUD-09 — Actor class: CLI/Ability
CLI/AI/Ability execution records actual authenticated authority path; no “AI” superuser bypass identity.

### AUD-10 — Action key stability
Semantic action/Ability key is stable enough for filtering and policy; UI label changes do not rewrite historical meaning.

### AUD-11 — Target resource identity
Target type + stable resource reference are recorded without relying only on mutable label/title.

### AUD-12 — Result taxonomy
`success`, `denied`, `failed`, `partial`, and `unknown/reconciliation` remain distinct where applicable.

### AUD-13 — Severity/classification
Security/admin/business/operational/privacy classes are explicit and independently filterable.

### AUD-14 — Source version
Event can identify source module/component/profile/schema version needed to interpret it.

### AUD-15 — Metadata schema version
Structured metadata has a versioned schema and bounded unknown-field behavior.

### AUD-16 — Timestamp truth
Occurred-at and recorded-at are UTC-normalized and remain distinguishable when source/event timing differs.

---

## B. Write, commit, failure & idempotency semantics — AUD-17…AUD-32

### AUD-17 — Successful local mutation
Committed high-value mutation produces exactly the required success Audit event.

### AUD-18 — Validation rejection
Input rejected before mutation does not create a false success event; denial/failure recording follows event policy.

### AUD-19 — Authorization denial
Denied protected action records only the safe denial evidence allowed by policy.

### AUD-20 — Transaction rollback
Business transaction rollback must not leave an Audit event claiming committed success.

### AUD-21 — Audit write failure before mutation
Mandatory-Audit high-risk action fails safely if policy requires durable Audit and Audit cannot be written.

### AUD-22 — Audit write failure after mutation boundary
Injected failure around commit boundary yields truthful partial/unknown/reconciliation state rather than fabricated atomicity.

### AUD-23 — Outbox/journal profile
Where a transaction/outbox strategy is used, replay produces one logical event and preserves causation.

### AUD-24 — Duplicate event submission
Same event operation/idempotency identity replay does not create misleading duplicate logical actions.

### AUD-25 — Concurrent same-resource mutations
Concurrent authorized mutations produce independently ordered/correlated events without lost or merged history.

### AUD-26 — Process crash before commit
Crash before durable mutation cannot produce false committed success.

### AUD-27 — Process crash after commit/before acknowledgement
Retry/reconciliation preserves exactly one logical committed action explanation even if transport/request result was unknown.

### AUD-28 — External side-effect unknown outcome
Provider timeout after possible external success records unknown/reconciliation truth; later reconciliation appends a new linked event.

### AUD-29 — Correction semantics
Operator correction creates a new event referencing prior event; original committed Audit row is not silently edited.

### AUD-30 — Bulk operation parent/child correlation
Bulk action records bounded parent/child correlation without one giant metadata document.

### AUD-31 — High-volume event admission
Audit cannot exhaust request memory by buffering an unbounded event set; batching/backpressure policy is observable.

### AUD-32 — Degraded Audit subsystem
Degraded Audit state is itself diagnosable and does not disable authorization or fabricate healthy logging.

---

## C. Read authorization, filters & exports — AUD-33…AUD-48

### AUD-33 — Site admin own-site read
Authorized Site A admin sees only allowed Site A event classes.

### AUD-34 — Wrong-site UUID IDOR
Site A admin directly requests Site B event UUID; deny without unsafe existence leakage.

### AUD-35 — Network Admin authorized aggregate
Authorized network operator can query permitted network/site scope with explicit target rules.

### AUD-36 — Site admin network-route access
Known Network Admin URL/API route remains server-side denied.

### AUD-37 — Super Admin classification filtering
Super Admin authority does not automatically expose secret/private metadata classified beyond viewer policy.

### AUD-38 — Actor filter authorization
Filtering by actor cannot reveal actors/events outside target scope.

### AUD-39 — Resource filter authorization
Filtering by resource reference cannot cross site/network policy.

### AUD-40 — Correlation search authorization
Correlation ID search reauthorizes every returned event; correlation itself is not an access token.

### AUD-41 — Date range bounds
Interactive date-range query applies bounded defaults/max window or pagination strategy.

### AUD-42 — Structured filter composition
Action/result/severity/module/resource filters return correct intersection without scope bypass.

### AUD-43 — Empty/invalid filter behavior
Malformed filters fail predictably and never fall back to an expensive unbounded network scan.

### AUD-44 — Pagination determinism
Stable ordering/cursor semantics avoid skip/duplicate anomalies under concurrent inserts.

### AUD-45 — Small authorized export
Export applies the same scope and field-level redaction rules as UI/API reads.

### AUD-46 — Large export
Large export moves to bounded JobService-backed workflow and remains scope-pinned to request authorization.

### AUD-47 — CSV formula/injection safety
Spreadsheet-compatible exports neutralize formula injection and unsafe field encoding.

### AUD-48 — Export provenance
Export records requestor, target scope, filter fingerprint/count/time and output identity without recursively dumping exported content into Audit.

---

## D. Privacy, redaction, minimization & secret exclusion — AUD-49…AUD-64

### AUD-49 — Password exclusion
Passwords never enter Audit payload/metadata before or after redaction.

### AUD-50 — Reset/auth token exclusion
Password reset, nonce, bearer, refresh, API and equivalent reusable security tokens never persist in Audit.

### AUD-51 — Cookie/Authorization header exclusion
Request headers are not generically serialized; sensitive headers are absent.

### AUD-52 — Vault secret rotation
Audit stores secret identity/version/action/result only, never plaintext or recoverable secret material.

### AUD-53 — Provider payload minimization
Raw webhook/provider body is not copied by default; Event Inbox/domain reference + safe fields are used.

### AUD-54 — Payment data minimization
Card/payment secrets or full payment payloads never enter Audit; safe provider/reference/state only.

### AUD-55 — Private signed URL exclusion
Reusable/private signed download URLs are not persisted.

### AUD-56 — Definition/config change minimization
Large old/new payloads are represented with field names, revisions, fingerprints, safe enums/counts rather than complete dumps.

### AUD-57 — Membership change minimization
Membership event links Transition/Enrollment/override identities rather than copying provider/customer payload.

### AUD-58 — IP purpose gating
IP is absent unless an explicit security/operational purpose/classification allows it.

### AUD-59 — Trusted proxy client-IP handling
When IP evidence is required, client address uses trusted-proxy-aware resolution, not arbitrary forwarded header trust.

### AUD-60 — User-agent minimization
User-agent storage is purpose-bound and bounded; no silent default collection.

### AUD-61 — Error redaction
Exceptions/provider errors are normalized so secrets, SQL credentials, filesystem secrets and private payloads do not leak.

### AUD-62 — Viewer-specific redaction
Privileged and less-privileged viewers receive policy-appropriate field sets server-side, not CSS hiding.

### AUD-63 — Diagnostic bundle redaction
When Audit excerpts enter support/diagnostic bundles, a second destination-specific redaction/minimization step applies.

### AUD-64 — No telemetry implication
Local Audit enablement does not transmit events remotely or opt the site into telemetry.

---

## E. Correlation, causation & observability boundaries — AUD-65…AUD-80

### AUD-65 — Request correlation
One request can link multiple domain/Audit events without collapsing them into one record.

### AUD-66 — Causation chain
Parent action → child Job/Workflow/side effect retains causation identifiers and target scope.

### AUD-67 — Workflow boundary
Workflow Run/Step remains canonical workflow history; Audit links meaningful admin/security milestones only.

### AUD-68 — JobService boundary
Job/Attempt logs remain JobService truth; Audit records selected meaningful actions/cancellations/retries/reconciliations.

### AUD-69 — Event Inbox boundary
Verified ingress body/state remains Event Inbox truth; Audit links security/admin/reconciliation events.

### AUD-70 — Notification boundary
Occurrence/recipient/read/delivery attempt histories are not duplicated wholesale into Audit.

### AUD-71 — Email transport boundary
SMTP/provider attempt/delivery/engagement truth stays ET domain; Audit records configuration/admin/reconciliation actions only.

### AUD-72 — Backup boundary
Backup/Restore manifest/run/provider copy truth remains Backup domain; Audit links operator/destructive/recovery milestones.

### AUD-73 — Membership domain-history boundary
Enrollment/Transition history remains Membership canonical state reconstruction source.

### AUD-74 — Definition revision boundary
Definition Revision remains content/config history; Audit records publish/disable/migrate/ownership actions and revision references.

### AUD-75 — Operational diagnostic boundary
Short-lived stack traces/latency/worker debug data remain Diagnostics and do not become long-retention Audit by default.

### AUD-76 — Security alert boundary
Alerting/incident notification state is separate from underlying Audit/Security Event evidence.

### AUD-77 — Analytics boundary
Page views/clicks/download analytics are not automatically Audit events.

### AUD-78 — Trace sampling boundary
Request tracing/sampling configuration cannot silently reduce mandatory security/admin Audit coverage.

### AUD-79 — Cross-domain correlation lookup
Authorized operator can pivot between safe linked identifiers without copying private source payloads.

### AUD-80 — Correlation collision/forgery
Externally supplied correlation IDs cannot override trusted internal causation or merge unrelated tenant/site histories.

---

## F. Retention, purge, erasure & hold — AUD-81…AUD-96

### AUD-81 — Retention class assignment
AR-S/AR-A/AR-B/AR-O/AR-D/AR-P classification resolves to explicit effective policy.

### AUD-82 — Multiple-class stricter policy
Event with multiple classifications receives the stricter applicable retention/privacy rule.

### AUD-83 — Retention eligibility calculation
Purge eligibility is deterministic and timezone-independent.

### AUD-84 — Bounded purge
Large purge runs in bounded batches with checkpointing/backpressure and avoids giant blocking delete.

### AUD-85 — Purge crash/retry
Crash mid-purge resumes idempotently and never expands target scope.

### AUD-86 — Site-scoped purge
Site A retention purge cannot remove Site B/network-owned events.

### AUD-87 — Privacy anonymization
When policy permits/needs anonymization, identity fields are transformed without rewriting event meaning/result.

### AUD-88 — Privacy erasure request
Erasure is policy/category-specific; it is not equivalent to deleting all related Audit history blindly.

### AUD-89 — Retained security necessity
Required security/legal evidence can remain according to documented lawful/product policy while unnecessary PII is minimized/redacted.

### AUD-90 — Legal/security hold candidate
If hold support exists, hold state is explicit, authorized, audited and not a hidden indefinite-retention default.

### AUD-91 — Hold release
Release resumes normal eligibility calculation without silent immediate over-delete.

### AUD-92 — Deactivated module retention
Module disable/deactivation does not silently erase Audit; retention remains governed centrally.

### AUD-93 — Plugin uninstall policy
Uninstall behavior follows explicit data-retention/export policy and Multisite scope; no accidental network-wide purge.

### AUD-94 — Remote support copy retention
Remote support/service copy, if any, has its own disclosed retention and cannot inherit local Audit duration by assumption.

### AUD-95 — Retention configuration authorization
Only appropriate site/network authority can change policy; lower scope cannot weaken locked network security policy.

### AUD-96 — Retention policy change audit
Changing retention/hold policy creates its own safe event with old/new policy identifiers rather than silently rewriting history.

---

## G. Integrity, provenance, migration & restore truth — AUD-97…AUD-112

### AUD-97 — Application append-only mutation denial
Ordinary admin/API path cannot edit a committed event in place.

### AUD-98 — Correction-by-new-event
Correction/reclassification allowed by policy produces linked new event and preserves prior provenance.

### AUD-99 — Schema migration provenance
Audit schema migration preserves event UUID, original occurred time, source schema/profile and migration provenance.

### AUD-100 — Imported historical event
Imported event retains original origin and is not presented as newly occurred local action.

### AUD-101 — Duplicate import detection
Same historical event imported twice is deduped/reconciled according stable origin identity.

### AUD-102 — Restore chronology
Restoring an old backup cannot erase current evidence that Restore/reconciliation occurred.

### AUD-103 — Restore merge
Historical rows from backup merge without overwriting post-backup current history.

### AUD-104 — Backup rollback signal
If an older Audit store is restored, generation/provenance checks surface rollback/reconciliation rather than silently asserting continuity.

### AUD-105 — Row/content hash experiment
If optional row hashing is enabled in future, modified content detection is measured and claim scope stated accurately.

### AUD-106 — Hash-chain experiment
If chaining is tested, insert/delete/reorder/rollback behavior and checkpoint dependency are explicitly evaluated.

### AUD-107 — Same-DB attacker limitation
Hash/checkpoint stored only beside editable rows is not presented as strong defense against privileged DB/server attacker.

### AUD-108 — Signed checkpoint experiment
If signing exists, verify key custody, rotation, verification failure and backup rollback behavior.

### AUD-109 — External checkpoint experiment
If remote/customer immutable anchoring exists, verify trust boundary, outage behavior, privacy and reconciliation separately.

### AUD-110 — Integrity failure UX
Detected inconsistency is surfaced as integrity/reconciliation problem; system does not silently rewrite rows to “fix” history.

### AUD-111 — Integrity evidence export
Authorized evidence export includes necessary provenance/profile/checkpoint identifiers without exposing keys/secrets.

### AUD-112 — Claim wording gate
No UI/docs/support claim may say tamper-proof/non-repudiable/immutable beyond the actually certified attacker model/profile.

---

## H. Multisite scope & Site Lifecycle — AUD-113…AUD-128

### AUD-113 — Same resource IDs on two sites
Identical numeric/local resource IDs remain isolated by explicit scope.

### AUD-114 — Current-blog independence
Creating/querying Audit while switched across blogs preserves explicit target ownership and restores context.

### AUD-115 — Worker site switch reuse
Sequential site Jobs in one process cannot leak prior site scope into Audit.

### AUD-116 — Network event visibility
Network-owned event appears only to authorized network viewers and does not duplicate as independent site-owned rows.

### AUD-117 — Network fan-out parent/children
Network operation over many sites records bounded network parent + site child evidence with truthful partial failures.

### AUD-118 — Noisy site isolation
One site's heavy Audit volume cannot cause another site's scope/query results to mix; resource fairness is measurable.

### AUD-119 — Site archive
Archive/suspend retains Audit according policy; nonessential writes may change behavior but history stays readable to authorized recovery/admin actors.

### AUD-120 — Site reactivation
Reactivation does not reset Audit identity/history or duplicate prior events.

### AUD-121 — Site deletion plan
Lifecycle plan enumerates site-owned Audit classes/retention actions before destructive cleanup.

### AUD-122 — Site deletion wrong-target guard
Deleted Site A cleanup cannot touch Site B rows even with colliding numeric IDs/resources.

### AUD-123 — Minimal network tombstone/event
Where policy requires, network-level deletion/transfer evidence can remain without retaining unnecessary deleted-site PII.

### AUD-124 — Clone to staging
Cloned Audit history is marked with environment/provenance expectations and cannot masquerade as fresh production events.

### AUD-125 — Unknown production clone
Duplicate production identity/history conflict is diagnosable; no silent merged authority.

### AUD-126 — Site transfer between networks
Historical provenance distinguishes original network/site identity from new target ownership and does not mass-rewrite origin.

### AUD-127 — Disaster restore
Restored network/site Audit reconciles scope, chronology, allocation/environment and current post-restore events safely.

### AUD-128 — Deleted/recreated WordPress site ID
Reused numeric blog/site ID cannot inherit historical Audit authority solely by matching the number.

---

## I. Query, index, performance & scale — AUD-129…AUD-144

### AUD-129 — Scope + time query
Benchmark primary site/network recent-events query on AU1 candidate indexes.

### AUD-130 — Scope + action + time query
Benchmark structured action filtering.

### AUD-131 — Scope + actor + time query
Benchmark actor investigation path.

### AUD-132 — Scope + resource + time query
Benchmark resource history path.

### AUD-133 — Scope + result/severity + time query
Benchmark incident/failure investigation path.

### AUD-134 — Correlation lookup
Benchmark exact correlation/causation lookup with scope predicate.

### AUD-135 — Retention eligibility query
Benchmark purge candidate selection without full-table scan where evidence supports index.

### AUD-136 — Metadata non-index default
Verify arbitrary metadata keys do not silently create uncontrolled indexes/query expectations.

### AUD-137 — Free-text search guard
No unbounded hot-table wildcard/FULLTEXT path becomes default without benchmark and explicit product requirement.

### AUD-138 — 1M row workload
Measure write/query/export/purge behavior and query plans.

### AUD-139 — 10M row workload
Same evidence; thresholds remain measured, not promised.

### AUD-140 — 100M row staged workload
Where environment permits, document practical limits/partition/archive needs; inability to run is recorded, not guessed.

### AUD-141 — Sustained write burst
Measure p50/p95/p99 write latency/contention and impact on business transaction path.

### AUD-142 — Concurrent read/write
Interactive admin query remains bounded under ongoing inserts.

### AUD-143 — Large network aggregation
Measure authorized 100/1k/10k-site aggregation strategy; no unbounded `switch_to_blog()` loop in one request.

### AUD-144 — Index/storage budget
Measure row/index bytes, retention growth and write amplification; exact index set remains evidence-selected.

---

## J. Diagnostics, incidents & operational observability — AUD-145…AUD-160

### AUD-145 — Audit health diagnostic
System Status can report Audit availability/schema/queue/purge health without exposing event payloads.

### AUD-146 — Audit write failure alerting
Repeated mandatory write failure creates actionable diagnostics/incident signal without log recursion storm.

### AUD-147 — Storage pressure
Approaching storage/retention limits is observable; system does not silently drop mandatory events.

### AUD-148 — Purge backlog
Backlog size/oldest eligible age/failure state is visible safely.

### AUD-149 — Query degradation
Slow/erroring Audit queries expose safe diagnostics and do not fall back to an unsafe unbounded query.

### AUD-150 — Correlation across incident
Incident responder can connect safe Audit, Job, Workflow, Event Inbox, Backup and Diagnostics IDs while each domain keeps canonical truth.

### AUD-151 — Support bundle generation
Support bundle includes explicit Audit summary ranges/counts/configuration only as authorized; private events require opt-in/authorization.

### AUD-152 — Support bundle preview
User can preview categories/sensitivity before remote submission where product contract requires.

### AUD-153 — Remote support upload
Upload failure/unknown outcome is truthful; local Audit does not claim remote receipt without service evidence.

### AUD-154 — Diagnostic logging recursion
Failure while writing/logging an Audit diagnostic cannot create an infinite recursive logging loop.

### AUD-155 — Rate-limited security attack
High-volume denied requests produce bounded security evidence/aggregation without attacker-controlled storage exhaustion.

### AUD-156 — Incident mode retention override
Temporary incident collection, if supported, is explicit, scoped, time-bounded and does not become permanent silent telemetry.

### AUD-157 — Clock skew
Source/remote clock skew is detectable; recorded server time and source occurred time are not conflated.

### AUD-158 — Timezone presentation
UI can present local/user/site timezone while durable event time remains UTC and sortable correctly across DST.

### AUD-159 — Error taxonomy stability
Operational error categories are structured/versioned enough for diagnostics without exposing raw exception bodies as canonical API.

### AUD-160 — Recovery verification event
Post-recovery verification records actual checks/results; “recovered” is not emitted solely because a retry command ran.

---

## K. Compatibility, failure injection & operational envelopes — AUD-161…AUD-176

### AUD-161 — Free-only profile
Audit platform behavior degrades safely with Free-only supported artifact set and does not require Pro class loading accidentally.

### AUD-162 — Free+Pro matched profile
Matched pair preserves one Audit contract and shared schema ownership according Platform API rules.

### AUD-163 — Free↔Pro version skew
Incompatible version pair fails/degrades according FP protocol without corrupting Audit or losing mandatory security evidence silently.

### AUD-164 — WordPress/PHP/DB compatibility floor
Run only after P-001 authorizes environment; record exact platform versions/profile.

### AUD-165 — Object cache off/on
Scope/authorization truth does not depend on unsafe shared cache keying.

### AUD-166 — Persistent object cache stale authorization
Permission/redaction/retention changes invalidate relevant cached views according generation/policy.

### AUD-167 — DB deadlock/lock timeout
Retry policy avoids duplicate logical events and reports unresolved failure truthfully.

### AUD-168 — DB connection loss
Mandatory-Audit action follows declared fail policy; no fabricated success.

### AUD-169 — Disk/storage exhaustion
Audit and diagnostic behavior remains bounded and surfaces critical state; no secret dump fallback.

### AUD-170 — Schema version mismatch
Reader/writer mismatch follows compatible migration/degraded-state rules, not silent malformed writes.

### AUD-171 — Extension event registration
Third-party module can register typed safe Audit event schema only through supported registry/policy; arbitrary secret payload logging rejected.

### AUD-172 — Extension removal
Historical events remain interpretable via stored semantic key/schema/version even if originating extension disappears.

### AUD-173 — Import/Export package
Audit configuration/schema mappings are explicit; historical event import follows provenance/dedupe/privacy rules.

### AUD-174 — Backup/Restore package
Audit backup/restore preserves scope/provenance and current chronology according AUD-102/103.

### AUD-175 — Observability overhead budget
Measure CPU/DB/write amplification for representative low/medium/high event rates; no fixed production claim before evidence.

### AUD-176 — Stop-the-line composite
Inject cross-site target tampering + secret-bearing error + Audit write failure during a high-risk mutation. Any wrong-site mutation, secret persistence, fabricated success, silent mandatory-Audit bypass or misleading integrity claim is a critical failure.

---

## 6. Required evidence artifact per future run

Every executed fixture records:
- fixture ID;
- WPE commit/version;
- Free/Pro artifact versions where applicable;
- WordPress/PHP/DB versions;
- single-site/Multisite topology;
- object-cache state;
- actor + target network/site scope;
- relevant module/provider/profile versions;
- starting domain/Audit state;
- action/Ability/correlation identifiers;
- expected event classification/result;
- actual event identity/scope/result;
- redaction/privacy assertions;
- domain-history/diagnostic boundary assertions;
- DB/query-plan/index observations where relevant;
- retry/crash/failure-injection details;
- final domain state;
- final Audit state;
- pass/fail/skipped/not-executed;
- known limitations.

Evidence is version/profile scoped and may expire after incompatible schema, runtime, storage, policy or provider changes.

---

## 7. MUST NOT / stop-the-line rules

Stop the line on any of the following:
- wrong-site/network Audit read, write, purge, export or lifecycle mutation;
- password, token, cookie, Authorization header, Vault plaintext, card secret or reusable private URL persisted in Audit;
- Audit event claims a business mutation succeeded when it rolled back or remained unknown;
- unauthorized actor can weaken retention/redaction or read protected metadata;
- restore/import rewrites old events as newly occurred or erases evidence of Restore itself;
- current-blog context silently becomes durable ownership;
- Audit failure disables Policy/authorization;
- a local hash/hash-chain is marketed as tamper-proof/non-repudiable without matching certified attacker model;
- mandatory security Audit is silently dropped under load/storage failure;
- generic remote telemetry/export occurs merely because local Audit is enabled.

---

## 8. Current evidence state

- Protocol documented: **AUD-01…AUD-176**.
- Executed: **0/176**.
- `AUD-M/W/A/P/C/R/I/S/Q/O` certifications: **0**.
- AU1/PT-D: **favored first future baseline only**.
- Exact DDL/index set: **OPEN**.
- Exact retention durations: **OPEN**.
- Exact fail-closed Ability classes: **OPEN**.
- Optional tamper-evidence mechanism: **NONE SELECTED / OPEN**.
- External immutable checkpoint profile: **NONE SELECTED / OPEN**.
- Runtime/scale/Multisite certification: **0**.

## 9. Development gate

This protocol authorizes **no executable work**.

Do not create Audit tables/migrations, logger code, integrity chains, checkpoints, queues, runtime fixtures, WordPress sites, privacy operations, exports, provider/service calls or benchmarks until explicit owner development/executable-evidence consent is recorded under ADR-0014 and the Approval Ledger.
