# WPEssential — Forms Runtime & Submission Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Work package: `P0-M00-WP02`  
Project state: `PLANNED_EXISTING_PROJECT`  
Execution mode: `PLANNER_ONLY`  
Related: Forms & Workflow spec, `FORM-ENTRY-RUNTIME-STORAGE-CANDIDATE.md`, `FORMS-CHAT-PTD-PTE-TOPOLOGY-COMPARISON.md`, Field Storage, Policy, Query, Relations, Membership, Workflow, JobService, Protected Assets, Privacy, Multisite, ADR-0014.

## 1. Purpose

Define executable evidence required before WPEssential can claim production-ready Form rendering, access control, validation, submission, Entry storage, save/resume, capacity, spam protection, uploads, CRUD actions, user actions, workflow handoff, privacy, Multisite isolation or Forms runtime scale.

This protocol tests the accepted Forms architecture. It does not authorize or redesign runtime implementation.

## 2. Canonical invariants

A future certified implementation must preserve these boundaries:

1. **Form Definition is configuration; Form Entry is runtime submission truth.**
2. **Every schema load and submit is authorized server-side. UI visibility is not authorization.**
3. **Server validation is authoritative even when client validation succeeds.**
4. **Submitted Entry pins the applicable published Form revision.**
5. **Passwords, reset/security tokens and equivalent secrets are never stored in canonical Entry data.**
6. **Destination Data Source remains canonical for CRUD-created/updated entities; Entry is submission/audit truth only.**
7. **Mapped target fields are explicit; generic request mass assignment is forbidden.**
8. **Durable Entry acceptance precedes Workflow/external side effects unless an explicitly certified no-long-term-storage profile uses a temporary durable processing envelope.**
9. **Duplicate/retried submissions must not create repeated accepted Entries or repeated non-idempotent side effects under the certified idempotency contract.**
10. **Site scope is explicit; changing IDs/site selectors never grants cross-site access.**

## 3. Runtime certification profile

Every future evidence report records at minimum:
- WordPress/PHP/database versions;
- single-site or Multisite topology;
- Form Definition/compiler revision;
- Forms runtime storage profile (`FRT1/PT-D`, `FRT2/PT-E`, or later accepted profile);
- canonical-value/projection schema version;
- Field/Query/Relation/Data Source adapter versions;
- Policy/Capability/Entitlement versions;
- Workflow/JobService backend profile;
- private/public file storage adapter;
- spam/CAPTCHA adapter versions where used;
- object/page-cache layers relevant to Form rendering/submission;
- locale/timezone profile;
- retention/privacy configuration;
- frontend/browser profile used for E2E evidence.

Certification is scoped to the recorded profile. Unknown newer provider/adapter versions are not silently certified.

# 4. Definition, publish and revision fixtures

### FM-01 — Draft Form is not public-executable
A Draft Form cannot be submitted as a published Form merely by knowing its UUID/route.

### FM-02 — Publish minimum contract
Publish fails when required field schema or a valid submit outcome/action contract is absent.

### FM-03 — Published revision pinning
A started/finalized submission resolves against the intended pinned published revision, not an unrelated latest Draft.

### FM-04 — Form edited during active draft
Editing/publishing a new Form revision does not silently reinterpret an existing resumable draft.

### FM-05 — Explicit compatible draft migration
Draft migration to a newer Form revision occurs only through explicit compatible field/reference mapping and preserves failure visibility.

### FM-06 — Retained Entry historical render
A retained Entry can resolve its historical labels/schema using pinned revision/snapshot semantics without depending on mutable current labels.

### FM-07 — Required historical revision deletion guard
Deletion/retirement of a revision needed by retained Entries is blocked or replaced by sufficient immutable historical schema/display evidence.

### FM-08 — Corrupt/missing Form revision
Affected draft/Entry enters degraded/recovery behavior; values are never reinterpreted against an unrelated current Form.

# 5. Access, authorization and request-security fixtures

### FM-09 — Public mode explicit opt-in
New Form is not anonymously submittable until guest/public access is explicitly configured.

### FM-10 — Authenticated-only schema load
Anonymous principal cannot load protected Form schema/options through direct URL/API/Ajax access.

### FM-11 — Authenticated-only submit
Anonymous direct POST cannot submit a Form protected for authenticated principals.

### FM-12 — Policy/role/membership access
Server evaluates current resource/actor Policy/entitlement at schema load and final submit; stale client-visible Form does not preserve revoked access.

### FM-13 — Cross-user target IDOR
Changing an entity/user/subject ID in request cannot read/update/delete a target outside the actor's resource Policy.

### FM-14 — Cross-site Form/Entry IDOR
Site A actor cannot load/submit/read/update/delete Site B Form/Entry by changing UUID/numeric ID/site selector.

### FM-15 — CSRF/session request boundary
Authenticated state-changing submission/action uses the certified CSRF/nonce/session contract and rejects forged cross-origin state changes where applicable.

### FM-16 — Unauthorized success/result reference
A submit response cannot expose created entity URL/Entry/private file reference unless the actor is authorized to receive that reference.

# 6. Field validation, layout and conditional fixtures

### FM-17 — Server-side type/range validation
Client bypass of required/type/range/pattern/enum constraints fails server-side with safe field errors.

### FM-18 — Unknown field mass assignment
Forged unknown field keys are ignored/rejected and never reach Entry canonical values or target Data Source implicitly.

### FM-19 — Protected target field mapping
A mapped generic Form field cannot mutate password hashes, roles/capabilities, Vault secrets, protected internal metadata or other forbidden domain fields unless a dedicated certified action owns that operation.

### FM-20 — Multi-step partial client bypass
Submitting final endpoint without completing client step UI still triggers full authoritative Form validation.

### FM-21 — Hidden field tampering
A conditionally hidden field follows declared hidden-value policy; forged hidden input cannot overwrite protected/stale state.

### FM-22 — Hidden preserve vs unset semantics
Configured preserve/unset behavior maintains explicit missing/null/empty distinctions and is visible/testable.

### FM-23 — Dynamic defaults/options authorization
Query/token/URL-derived values are allowlisted, typed and revalidated server-side; privileged defaults cannot be forged by client state.

### FM-24 — Calculation engine safety
Calculations use bounded typed expression semantics; no PHP/JS eval, command execution or unbounded recursive expression path is reachable from user configuration.

# 7. Draft / save-resume fixtures

### FM-25 — Draft disabled default
Form does not persist resumable draft state unless save/resume is explicitly enabled.

### FM-26 — Guest resume token entropy/storage
Guest draft token is high entropy, expiry-bound and only a hash/reference is stored server-side after issuance.

### FM-27 — Resume token replay after expiry/revoke
Expired/revoked/consumed token cannot resume or mutate draft.

### FM-28 — Authenticated draft ownership
User cannot resume another user's draft by changing Entry UUID/token-independent identifiers.

### FM-29 — Guest draft claim
Claiming guest draft into authenticated identity requires valid resume proof + current Policy; login alone cannot claim arbitrary draft.

### FM-30 — Sensitive field draft exclusion
Fields classified non-persistable are absent from draft canonical storage, logs and generic cache.

### FM-31 — Draft file retention/cleanup
Expired draft file references are cleaned according to policy without deleting shared or submitted assets incorrectly.

### FM-32 — Draft concurrency
Two tabs/devices updating the same draft respect version/precondition semantics and do not silently lose newer state where optimistic concurrency is required.

# 8. Capacity, abuse and spam fixtures

### FM-33 — Schedule open/close boundary
Submission before opening/after closing is rejected server-side using defined timezone semantics; stale page state cannot bypass schedule.

### FM-34 — Global capacity race
Concurrent final submissions at the last available capacity cannot both exceed the declared global maximum.

### FM-35 — Per-user limit race
Concurrent submissions from one authenticated actor cannot exceed configured per-user maximum through race conditions.

### FM-36 — Privacy-aware IP limit
If per-IP control is enabled, trusted-proxy resolution and retention/privacy rules apply; spoofed forwarding headers do not trivially bypass it.

### FM-37 — Public rate limit
High-rate anonymous submission is bounded under the certified atomic/shared limiter profile without unbounded key growth.

### FM-38 — Honeypot behavior
Honeypot does not create an accessibility trap and detected spam follows configured quarantine/reject semantics rather than silently losing required audit evidence.

### FM-39 — CAPTCHA adapter failure
Provider outage/timeout/invalid response follows explicit fail-open/fail-closed/degraded policy appropriate to risk and does not hang indefinitely.

### FM-40 — Spam quarantine authorization
Quarantined Entries remain inaccessible to unauthorized users and retention/export behavior follows privacy policy.

# 9. File upload fixtures

### FM-41 — MIME + extension validation
Allowed upload requires certified MIME/extension/content validation; filename extension alone does not establish safety.

### FM-42 — Executable/script upload denial
Executable/script/polyglot payloads outside approved policy are rejected and cannot become web-executable public assets.

### FM-43 — SVG policy
SVG is rejected unless the certified sanitization profile is enabled; sanitized output cannot preserve script/event/external-dangerous content outside policy.

### FM-44 — Size/count/server limit
Per-file/count/Form limits are enforced before uncontrolled resource exhaustion and never claim a limit above unavoidable server constraints.

### FM-45 — Randomized/private storage identity
Private upload uses opaque/reference-based identity and cannot be fetched through a predictable direct public filesystem URL.

### FM-46 — Private file authorization
Every private file download reauthorizes the actor/resource; possession of attachment ID/path is not access.

### FM-47 — Required file finalize failure
Submission is not reported complete when required file finalize/commit failed; Entry reflects blocked/incomplete/recoverable state truthfully.

### FM-48 — Orphan/shared file cleanup
Deleting/anonymizing Entry does not blindly delete a Media/object asset that has legitimate shared references; true orphan cleanup remains bounded and auditable.

# 10. Entry persistence, idempotency and failure fixtures

### FM-49 — Canonical Entry write
Accepted submission persists normalized Entry identity + versioned canonical values + permitted metadata with exact missing/null/empty/type semantics.

### FM-50 — Secret/password omission
Password/reset/security token values do not appear in Entry document, projections, logs, workflow metadata, exports or caches; only safe action result/reference may persist.

### FM-51 — Sensitive projection default
Sensitive/complex fields are non-projected by default; only explicit permitted typed projections become filter/sort/search material.

### FM-52 — Duplicate submission same key
Retry with equivalent idempotency identity returns/reuses the same logical accepted submission rather than creating another Entry.

### FM-53 — Same key materially different payload
Idempotency key reuse with a different normalized submission produces conflict rather than overwriting/replaying first request silently.

### FM-54 — Concurrent same-key race
Two simultaneous equivalent submissions admit one logical accepted Entry/processing envelope under certified storage semantics.

### FM-55 — DB save failure before acceptance
No Workflow/external business side effects occur; retry remains safe and no false-success Entry is reported.

### FM-56 — Workflow enqueue failure after Entry commit
Entry remains authoritative accepted state such as `submitted/processing_pending`; retry/reconciliation can enqueue later without duplicating Entry.

### FM-57 — Crash after Entry commit before response
Client retry reconciles existing accepted Entry using idempotency identity instead of creating duplicate.

### FM-58 — Projection write/rebuild failure
Canonical Entry remains authoritative; derived projection failure is diagnosed/rebuildable and cannot corrupt canonical values.

# 11. CRUD, relation, user and membership action fixtures

### FM-59 — Explicit CRUD field mapping
Create/update/upsert sends only declared mapped target fields; arbitrary request keys never mass-assign destination entity.

### FM-60 — Update ownership/version precondition
Update reauthorizes target and uses supported stale-write/version semantics to avoid silent overwrite of newer authoritative data.

### FM-61 — Delete elevated boundary
Delete action requires the dedicated destructive capability/Policy/confirmation semantics and cannot be invoked merely because submitter can view/edit the Form.

### FM-62 — Upsert unique-match safety
Upsert operates only on explicit unique match contract; ambiguous/multiple matches fail rather than mutating an arbitrary record.

### FM-63 — Relation action cardinality/authorization
Relation creation/removal reuses Relation Engine constraints + endpoint Policy; forged IDs cannot attach resources across unauthorized scope.

### FM-64 — User creation privilege boundary
Generic public/user Form cannot create Administrator-equivalent/Super Admin authority through role field/request manipulation.

### FM-65 — User password action boundary
Password is passed directly to certified WordPress security action and never retained as generic Form/Entry value.

### FM-66 — Membership/entitlement action boundary
Grant/revoke/change action uses Membership/Entitlement authority and current Policy; raw submitted plan/provider status cannot directly become entitlement.

# 12. Workflow handoff and no-long-term-storage fixtures

### FM-67 — Workflow trigger after durable acceptance
Normal storage mode commits accepted Entry before Workflow dispatch; workflow failure cannot make the system falsely claim Entry never existed.

### FM-68 — Workflow duplicate/retry linkage
Entry→Workflow run linkage and idempotency prevent repeated non-idempotent actions when dispatch/job delivery is duplicated.

### FM-69 — Workflow revision binding
Submission triggers the intended published/pinned Workflow contract according to accepted binding semantics; Draft workflow edits do not silently alter an in-flight accepted submission.

### FM-70 — No-long-term-storage temporary envelope
If long-term Entry retention is disabled, any required durable processing envelope is minimized, retention-bounded and sufficient for idempotent recovery.

### FM-71 — No-storage failure truth
No-storage mode communicates reduced recovery/history guarantees and does not discard the only evidence needed to reconcile an uncertain external side effect prematurely.

### FM-72 — Queue/log secret hygiene
Short-lived envelope, Job payload, error log and retry metadata do not become an indefinite secret/submission-data store.

# 13. Success outcome, redirects and UX fixtures

### FM-73 — Inline success after authoritative acceptance
Success is shown only after the configured acceptance boundary succeeds; pending external processing is labelled accurately.

### FM-74 — Internal redirect
Internal success redirect is normalized to an allowed local destination and cannot be forged into an open redirect.

### FM-75 — External redirect
Advanced external redirect requires explicit validated allow/policy semantics; user input cannot choose arbitrary dangerous scheme/host unless intentionally permitted.

### FM-76 — Partial processing status
Entry accepted but downstream action failed/pending is presented as accepted-with-processing-state, not as total success or total rollback when neither is true.

# 14. Privacy, retention, admin and export fixtures

### FM-77 — Minimal request metadata
Raw IP/user-agent/headers are not retained by default without declared purpose/classification; any retained metadata follows privacy/retention policy.

### FM-78 — Retention execution
Drafts, spam, values, files and metadata follow their separate configured retention/anonymization/delete classes without one blanket destructive purge.

### FM-79 — Privacy export authorization
Exporter locates only authorized/in-scope personal Entries through declared identity mappings and excludes secrets/internal operational metadata.

### FM-80 — Privacy erase/anonymize
Field/Form policy removes/anonymizes eligible personal data while preserving required audit/aggregate/reference integrity with explicit retained-reason semantics.

### FM-81 — Entry admin list authorization
Admin list/read/export uses object/site Policy; a capability to manage one site/Form does not reveal another site's Entries.

### FM-82 — Non-projected filter behavior
Admin filtering does not generate unbounded joins/scans over arbitrary canonical fields; unsupported filter enters bounded job/export or projection-required flow.

### FM-83 — CSV/formula export safety
Export adapter escapes formula-injection-dangerous values and applies field/row authorization independently from Entry admin visibility.

### FM-84 — Pro expiry/degraded runtime
ADR-0007 semantics preserve safe deployed Forms behavior/data; management/editing restrictions cannot expose protected Form/Entry/file data or delete Entries.

# 15. Multisite, topology and scale fixtures

### FM-85 — FRT1/PT-D scope predicate isolation
Shared scoped runtime includes authoritative network/site identity in every relevant path; wrong-site Entry IDs never return/mutate data.

### FM-86 — FRT1 noisy-neighbor profile
One hot site workload is measured for index/IO/cleanup contention and JobService fairness without weakening scope correctness.

### FM-87 — FRT2/PT-E site provisioning/migration
Per-site runtime profile, if tested, proves provisioning/schema-version/site lifecycle behavior at representative network sizes.

### FM-88 — FRT2 network-admin fan-out
Network diagnostics/privacy/migration operation remains bounded and truthful across many per-site table families.

### FM-89 — Site backup/export isolation
Site-level Backup/export includes only target site's authorized Forms runtime/file references and excludes other sites sharing network users/storage infrastructure.

### FM-90 — Site delete/transfer lifecycle
Active submissions/drafts/jobs are drained/reconciled; retained data follows domain policy rather than generic table hard-delete; scope remap is reviewed during transfer.

### FM-91 — 10k/100k/1M Entry benchmark
Measure write/read/list/filter/projection/privacy/cleanup behavior across small/large/repeater payload profiles with p50/p95/p99, query count, memory and storage growth.

### FM-92 — 100/1k/10k-site topology benchmark
Compare migration/provisioning/table-count/network diagnostics and lifecycle cost for FRT1 vs FRT2 before final topology selection.

# 16. MUST NOT / stop-the-line gates

Forms runtime certification fails immediately if any fixture demonstrates:
- anonymous or unauthorized direct submission of a protected Form;
- cross-user or cross-site Entry/target/file IDOR;
- client-only authorization or validation protecting sensitive mutation;
- password/reset/security token persisted in canonical Entry/log/cache/export/Job payload;
- arbitrary request mass assignment into target Data Source;
- generic Form role input granting Administrator/Super Admin-equivalent authority;
- duplicate retry creating repeated accepted Entry or unsafe repeated side effect contrary to certified idempotency contract;
- workflow/external side effect occurring before required durable acceptance and becoming unreconcilable after failure;
- private upload reachable through unauthorized predictable/public URL;
- capacity limits exceeded due to accepted concurrency race;
- site selector/prefix/table choice treated as authorization;
- retention/privacy action deleting unrelated/shared asset/data;
- missing/corrupt Form revision causing reinterpretation against unrelated schema;
- FRT1/PT-D or cache path returning another site's data.

These are stop-the-line defects for the affected certification scope.

# 17. Performance evidence

Record at minimum:
- Form schema render/bootstrap time;
- final validation time;
- Entry commit time;
- query count;
- canonical payload size;
- projection write/rebuild cost;
- private file finalize cost;
- duplicate/idempotency contention;
- capacity counter/lock contention;
- Entry list/filter p50/p95/p99;
- cleanup/anonymization throughput;
- Job handoff latency;
- one-hot-site noisy-neighbor impact;
- 100/1k/10k-site migration/provisioning cost for topology comparison.

Performance optimization may not weaken Policy, idempotency, revision pinning, retention or scope isolation.

# 18. Required future evidence report

Include:
- exact runtime/profile/topology;
- FM-01…FM-92 pass/fail/NA with rationale;
- security/negative-requirement results;
- Entry/document/projection integrity evidence;
- idempotency/crash-window/concurrency results;
- file safety/private-delivery evidence;
- spam/rate/CAPTCHA profile evidence;
- CRUD/relation/user/membership action authorization evidence;
- Workflow handoff/recovery results;
- privacy/retention/export evidence;
- Multisite scope/Backup/lifecycle results;
- FRT1/FRT2 measurements and selection conclusion when topology certification is attempted;
- known unsupported/degraded profiles.

# 19. Current state

**FM fixtures documented: 92.**  
**FM fixtures executed: 0/92.**  
**Forms runtime/storage topology certified: none.**

FRT1/PT-D remains the **first future benchmark baseline**, not a final topology choice. FRT2/PT-E remains a mandatory comparison before final physical topology selection.

No Form route/render, submission, Entry row/table, upload, spam/CAPTCHA request, CRUD/user/relation/membership mutation, Workflow/Job dispatch, privacy operation, benchmark, migration or WordPress runtime test has been executed by this planning work.

# 20. Development gate

Execution requires explicit scoped owner consent recorded under ADR-0014 / `DEVELOPMENT-CONSENT.md` / `docs/APPROVAL-LEDGER.md`.

Until then this protocol remains planning evidence only: `NOT EXECUTED`.