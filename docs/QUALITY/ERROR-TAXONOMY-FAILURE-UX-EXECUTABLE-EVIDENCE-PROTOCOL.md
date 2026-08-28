# WPEssential — Error Taxonomy & Failure UX Executable Evidence Protocol

Status: **Phase 0 evidence specification / NOT AUTHORIZED FOR EXECUTION**  
Date: 2026-08-28  
Work package: `P0-M00-WP28`  
Related: ADR-0014, `docs/ARCHITECTURE/ERROR-TAXONOMY-AND-FAILURE-UX.md`, KPA/Policy/Abilities, JobService, Workflow, REST, UI, Audit, Privacy, Multisite, Backup/Restore, Import/Export, provider integrations.

## 1. Purpose

This is the canonical future executable-evidence contract for WPEssential error semantics and failure UX across UI, REST, Abilities, Jobs, Workflow, CLI/AI adapters and integrations.

The protocol freezes **ERR-01…ERR-176**.

**Executed: 0/176.**

The goal is not prettier error messages. The goal is stable, safe, actionable and truthful failure semantics across every channel without leaking protected data, misclassifying unknown outcomes, causing unsafe retries or turning partial failure into false success.

No runtime error mapper, UI component, REST response, Ability execution, Job retry, provider failure, browser test, accessibility test or benchmark is authorized by this document.

---

## 2. Canonical error categories

Initial semantic categories:
- `validation`;
- `authorization`;
- `conflict`;
- `dependency`;
- `integration_auth`;
- `rate_capacity`;
- `timeout_network`;
- `data_integrity`;
- `migration_compatibility`;
- `internal_bug`.

A domain may add typed subcodes while preserving these broad semantics.

## 3. Truth boundaries

The following remain separate:

`Error category ≠ severity ≠ retryability ≠ HTTP status ≠ UI presentation ≠ provider code ≠ Job terminal state ≠ Workflow state ≠ Audit event ≠ incident severity`

Also:
- error text ≠ machine authority;
- timeout ≠ confirmed failure;
- provider acceptance ≠ final delivery;
- denied policy result ≠ internal error;
- partial completion ≠ success;
- retry requested ≠ rollback;
- critical-looking UI color ≠ actual critical severity;
- correlation ID ≠ authorization token;
- raw exception ≠ public error envelope.

---

## 4. Canonical normalized envelope

Where applicable, semantic error data includes:
- stable namespaced `code`;
- category;
- localized safe message;
- severity;
- retryability classification;
- optional validation field/path details;
- correlation/operation ID;
- optional safe recovery action;
- optional safe metadata;
- optional current/version/precondition hints for conflicts;
- optional retry-after/backoff hint;
- partial-failure summary where relevant.

Transport/channel adapters may serialize differently while preserving semantic meaning.

---

## 5. Certification classes

Certify independently:

- `ERR-T` — taxonomy/code/envelope correctness;
- `ERR-V` — validation and field failure UX;
- `ERR-A` — authorization/privacy-safe denial;
- `ERR-C` — conflict/concurrency/precondition semantics;
- `ERR-R` — retry/rate/timeout/unknown-outcome semantics;
- `ERR-P` — partial/batch/workflow/provider failure truth;
- `ERR-X` — cross-channel normalization/parity;
- `ERR-U` — UI/accessibility/localization/recovery UX;
- `ERR-S` — security/redaction/Multisite scope;
- `ERR-O` — observability/incidents/performance/compatibility.

Passing one class never implies another.

---

# 6. Fixed executable fixture matrix

## A. Taxonomy, stable codes & normalized envelopes — ERR-01…ERR-16

### ERR-01 — Stable code identity
One known validation failure returns a documented namespaced machine code independent of translated message.

### ERR-02 — Code uniqueness
Two semantically different first-party errors cannot share one ambiguous code unless intentionally represented by the same contract.

### ERR-03 — Namespaced extension code
Third-party extension uses vendor namespace and cannot claim reserved first-party error code silently.

### ERR-04 — Validation category
Invalid user-correctable input classifies as `validation`, not internal/server failure.

### ERR-05 — Authorization category
Capability/Policy deny classifies as authorization/policy result without leaking target content.

### ERR-06 — Conflict category
Stale revision/version/duplicate-key/state-race failure classifies as `conflict` with safe precondition hint.

### ERR-07 — Dependency category
Missing/incompatible module/provider/builder dependency reports dependency class + recovery requirement.

### ERR-08 — Integration auth category
Expired/revoked/misconfigured external credentials are distinguishable from transient outage.

### ERR-09 — Rate/capacity category
429/capacity/backpressure maps to rate/capacity with retry/user-action semantics where known.

### ERR-10 — Timeout/network category
Network timeout has explicit unknown/transient semantics and does not claim side effect definitely failed.

### ERR-11 — Data-integrity category
Checksum/corrupt archive/missing invariant relation/malformed durable state stops unsafe continuation.

### ERR-12 — Migration/compatibility category
Free↔Pro/schema/platform incompatibility maps to migration/compatibility, not generic fatal message.

### ERR-13 — Internal bug category
Unexpected exception becomes stable internal error + correlation ID; raw stack is not public output.

### ERR-14 — Severity independence
Category and severity remain independently assigned; a validation issue is not `critical` merely because request failed.

### ERR-15 — Retryability independence
Retryability derives from semantic operation/outcome, not category string alone.

### ERR-16 — Envelope schema version
Machine-consumed envelope/version evolution is explicit and additive-compatible where intended.

---

## B. Validation & field-level failure semantics — ERR-17…ERR-32

### ERR-17 — Required field
Missing required field returns path/control mapping and no mutation.

### ERR-18 — Type mismatch
Wrong scalar/object/list type fails before owner-domain side effect.

### ERR-19 — Enum mismatch
Unsupported enum returns stable code + safe allowed-values detail where disclosure is safe.

### ERR-20 — Length/size constraint
Oversized text/file/payload returns specific bounded error without echoing full content.

### ERR-21 — Slug/key format
Invalid identifier maps to exact field path and does not mutate registry/rewrite state.

### ERR-22 — Cross-field constraint
Mutually invalid option combination reports form-level + involved fields rather than misleading one-field error.

### ERR-23 — Nested repeater path
Nested validation identifies deterministic item/index/key path without leaking other protected rows.

### ERR-24 — File validation
MIME/extension/size/count failure distinguishes validation from storage/provider failure and removes unsafe temporary artifact according owner policy.

### ERR-25 — Validation ordering
Authorization/scope checks occur before validation details when validating target would leak protected resource structure.

### ERR-26 — Unknown fields
Unexpected fields are rejected/ignored only according schema contract; mass-assignment cannot silently accept privileged fields.

### ERR-27 — Multiple validation errors
Bounded list reports all safe actionable field errors without unbounded attacker-controlled output.

### ERR-28 — Server/client parity
Client hints may improve UX but server returns authoritative validation result independently.

### ERR-29 — No retry until corrected
Validation error is marked non-retryable by automatic retry machinery.

### ERR-30 — Localized field message
Message localization does not change machine code/path semantics.

### ERR-31 — Accessibility field association
Rendered field error associates with control/help text and is announced appropriately without color-only signaling.

### ERR-32 — Validation telemetry/privacy
Invalid secret/private value is never echoed into Audit/log/diagnostic/error metadata.

---

## C. Authorization, policy denial & privacy-safe failures — ERR-33…ERR-48

### ERR-33 — Unauthenticated protected action
Returns authentication-required/denied behavior appropriate to channel without protected resource detail.

### ERR-34 — Missing capability
Authenticated caller lacking operation-class capability is denied server-side with stable code.

### ERR-35 — Resource Policy deny
Capability grant + target Policy deny stays denied; error can distinguish only as much as viewer may know.

### ERR-36 — Wrong-site IDOR
Site A actor targets Site B resource and receives safe denial, not validation/existence information.

### ERR-37 — Network-route deny
Site Admin directly invokes network route/Ability and receives stable authorization denial.

### ERR-38 — Super Admin high-risk policy
Where WPE requires explicit high-risk Policy/confirmation/re-auth, Super Admin failure maps truthfully instead of implicit bypass.

### ERR-39 — Membership denial as policy result
Protected-content denial can map to login/plan/drip/forbidden UX without being classified internal failure.

### ERR-40 — Secret-field read denial
Unauthorized secret read does not reveal whether secret is set/value/provider beyond safe configured-state policy.

### ERR-41 — Existence oracle resistance
403/404 choice and metadata are consistent with resource privacy policy, avoiding avoidable existence probing.

### ERR-42 — Rate-limit auth attack
Repeated denials can trigger security/rate response without changing authorization semantics.

### ERR-43 — Revocation during long operation
Job/Workflow reauthorization failure after revoke stops future side effects with explicit denied/cancelled/partial state.

### ERR-44 — Authorization error is non-retryable
Automatic retry does not repeatedly hammer an operation that is denied unless authority/context changes explicitly.

### ERR-45 — Recovery action privacy
Suggested recovery does not reveal protected resource owner/email/site details to unauthorized caller.

### ERR-46 — Audit denial safely
Audit captures safe actor/action/scope/result identifiers according AUD without copying private request body.

### ERR-47 — UI direct-link parity
Hidden button/menu still yields same server denial on manually constructed URL/API request.

### ERR-48 — AI/CLI parity
AI/CLI cannot translate authorization deny into alternate privileged fallback execution.

---

## D. Conflict, concurrency & precondition failures — ERR-49…ERR-64

### ERR-49 — Stale definition revision
Edit based on old revision returns conflict with current revision identifier and no lost update.

### ERR-50 — ETag/version conflict
Resource mutation with stale precondition returns deterministic conflict category/code.

### ERR-51 — Duplicate unique key
Concurrent/create duplicate slug/key produces conflict rather than generic DB exception.

### ERR-52 — Membership seat race
Capacity/seat assignment race returns capacity/conflict truth and no over-allocation.

### ERR-53 — Role last-admin/anti-lockout conflict
Unsafe role mutation returns explicit safety/conflict result and preserves recovery principal.

### ERR-54 — Relation cardinality race
Concurrent edge creation violating one-to-one/limits becomes conflict with no duplicate corrupt state.

### ERR-55 — Module enable/disable race
Registry state conflict reports current generation/state; no half-enabled success.

### ERR-56 — Retention policy stale plan
Cleanup/privacy operation detects changed policy revision and pauses/replans rather than continuing stale destructive plan.

### ERR-57 — Import plan fingerprint conflict
Mutation after dry-run invalidates stale Plan/fingerprint and requires review/replan.

### ERR-58 — Reset/restore plan conflict
Target/recovery-point drift blocks destructive execution with conflict/integrity category.

### ERR-59 — Backup remote-copy generation conflict
Retry against changed copy/state uses version/idempotency semantics and preserves truthful state.

### ERR-60 — Provider resource version conflict
409/412 maps to semantic conflict with provider/source reference but no raw sensitive body.

### ERR-61 — Conflict recovery UX
User sees refresh/compare/replan/merge/retry action only when actually safe and supported.

### ERR-62 — Conflict is not auto-retry default
Stale write does not silently retry with newest version and overwrite another actor's change.

### ERR-63 — Batch mixed conflicts
Per-item conflict results remain distinct from successes/validation failures in summary.

### ERR-64 — Conflict correlation
Conflict event references operation/resource/version safely for support/Audit without dumping payload diffs.

---

## E. Retry, rate, timeout, network & unknown outcome — ERR-65…ERR-80

### ERR-65 — Read transient retry
Safe idempotent read retries with bounded attempts/backoff/jitter according policy.

### ERR-66 — Write retry requires idempotency
Write is retried automatically only when idempotency/preconditions make duplication safe.

### ERR-67 — Provider 429 with Retry-After
Retry honors safe bounded `Retry-After`/backoff and avoids synchronized retry storm.

### ERR-68 — Provider 429 missing hint
Client uses bounded fallback backoff and terminal/deferred state after budget.

### ERR-69 — Network connection failure before send
Where outcome is provably no-send, failure may be retryable according operation semantics.

### ERR-70 — Timeout after possible send
State becomes unknown/reconciliation-needed when side effect may have occurred.

### ERR-71 — HTTP 5xx idempotent request
Retries are bounded and never promote intermediate provider errors to final business truth.

### ERR-72 — Malformed provider response
Unparseable response produces integration/protocol error, preserves safe raw evidence only per privacy policy, and does not assume success.

### ERR-73 — DNS/TLS failure
Failure category distinguishes network/trust/configuration where evidence allows and never disables certificate verification as recovery shortcut.

### ERR-74 — OAuth expired credential
Re-auth requirement is distinct from provider outage and repeated refresh loop is bounded.

### ERR-75 — Job retry budget
Attempt count/backoff/next-run/terminal state remain visible and consistent with JobService.

### ERR-76 — Retry after crash
Duplicate Job/Event/Workflow delivery is safe through idempotency/reconciliation; no exactly-once claim.

### ERR-77 — User manual retry
UI indicates what may have already happened and uses same operation/idempotency identity where required.

### ERR-78 — Cancel during retry wait
Cancellation prevents future attempts but does not claim rollback of possible prior side effect.

### ERR-79 — Retry storm backpressure
Large transient outage cannot create unbounded queue/request loop; backpressure state is explicit.

### ERR-80 — Retry exhaustion
Terminal/deferred/manual-intervention state is truthful and retains recovery context/correlation.

---

## F. Partial, batch, workflow, import & provider failure truth — ERR-81…ERR-96

### ERR-81 — Batch success/failure counts
Mixed result reports total/succeeded/failed/skipped/unknown correctly.

### ERR-82 — Per-item safe error
Each failed item has stable safe code without leaking inaccessible item content.

### ERR-83 — Retryable subset
Only safe retryable subset is offered/enqueued; successes are not repeated blindly.

### ERR-84 — Atomic batch rollback
If operation is truly atomic, rollback state is verified and summary says rolled back rather than partial success.

### ERR-85 — Non-atomic batch
Completed items remain committed and summary cannot say all-or-nothing rollback.

### ERR-86 — Compensation distinction
Compensating action is recorded separately from rollback; compensation failure remains visible.

### ERR-87 — Workflow branch partial
One failed branch/join produces Workflow-owned state + safe error references, not generic success because other branches completed.

### ERR-88 — Form action partial
Entry accepted but downstream action failed is represented separately from form validation/submission persistence.

### ERR-89 — Notification fan-out partial
Per-recipient/channel outcomes stay separate; one delivery success is not global notification success.

### ERR-90 — Email render vs transport failure
Renderer failure and transport submission/delivery failure keep separate codes/domains.

### ERR-91 — Webhook/Event Inbox consumer failure
Verified ingress can remain accepted while consumer processing fails/retries; response/history reflects correct boundary.

### ERR-92 — Import partial
Import summary identifies created/updated/skipped/failed/unknown and rollback class; no fabricated full success.

### ERR-93 — Backup partial
Archive/local copy/remote copy/verify/retention failures remain stage-specific.

### ERR-94 — Restore partial
Preflight/extract/DB/files/reconciliation/post-health stages report exact failed/partial state and recovery action.

### ERR-95 — Network fan-out partial
One site failure does not mark 100-site coordinator fully failed or fully successful; aggregate truth preserves child results.

### ERR-96 — Partial-result export
Downloadable error report obeys authorization/privacy/CSV-injection/private-file retention rules.

---

## G. Cross-channel normalization & parity — ERR-97…ERR-112

### ERR-97 — Admin UI parity
Known semantic failure renders from normalized code/category rather than bespoke contradictory interpretation.

### ERR-98 — REST parity
REST status/Problem Details mapping preserves semantic code/category/retry/conflict metadata.

### ERR-99 — Ability parity
Ability result/error object preserves same semantic code and safe metadata.

### ERR-100 — WP-CLI parity
CLI exit/status text preserves semantic classification without stack dump/secrets in normal output.

### ERR-101 — Workflow parity
Workflow step references same normalized error category/code while Workflow owns retry/step state.

### ERR-102 — Job parity
Job Attempt stores normalized safe error reference and retryability without turning error mapper into Job truth.

### ERR-103 — AI parity
AI tool receives stable machine-safe error/recovery affordance but no hidden privileged diagnostic/private payload.

### ERR-104 — Webhook/provider adapter parity
Adapter translates provider-specific error into WPE semantic error while retaining safe provider code/reference for diagnostics.

### ERR-105 — Translation independence
Changing locale modifies message, never code/category/retryability/business logic.

### ERR-106 — HTTP-status independence
Same semantic error may map to channel-specific status, but clients do not infer domain result solely from HTTP numeric code.

### ERR-107 — Correlation propagation
Correlation/operation ID remains stable through channel adapter → Ability → Job/provider/Audit without becoming a secret/auth token.

### ERR-108 — Recovery-action normalization
Recovery action is typed/stable enough for UI adapter but remains capability/Policy checked when invoked.

### ERR-109 — Deprecated error code
Old code has documented compatibility/translation period or explicit breaking version; never silently reused for new meaning.

### ERR-110 — Unknown extension error
Unrecognized vendor code remains namespaced/safe and maps to bounded fallback category without crash.

### ERR-111 — Nested cause chain
Internal diagnostic may retain safe cause references, but public envelope remains bounded and non-recursive.

### ERR-112 — No text parsing
Automated consumers operate on code/category/typed metadata, never regex translated human message.

---

## H. UI failure UX, accessibility & localization — ERR-113…ERR-128

### ERR-113 — Inline validation state
Field error displays beside/associated with field and focus behavior is usable.

### ERR-114 — Page/banner degraded state
Dependency/migration/platform degraded problem persists visibly and is not lost as transient toast.

### ERR-115 — Toast appropriateness
Toast is used only for transient/nonblocking feedback and is not sole carrier of critical/destructive failure.

### ERR-116 — Destructive retry modal
Retry/restore/recovery decision clearly states impact, possible partial completion and required confirmation.

### ERR-117 — Diagnostics link authorization
“View details” resolves correlation-safe diagnostic page only for authorized viewer.

### ERR-118 — Screen-reader announcement
New error summary/field issue is announced through supported semantics without repeated noise loop.

### ERR-119 — Focus management
Submit failure moves focus to summary/first actionable invalid control according UX contract.

### ERR-120 — Color independence
Severity/invalid state includes text/icon/semantic markup; color alone is not signal.

### ERR-121 — Keyboard recovery
Retry/cancel/details/close actions are keyboard reachable and focus returns sensibly.

### ERR-122 — RTL layout
Error icon/text/field associations remain correct in RTL without clipping/overlap.

### ERR-123 — Long translation
Expanded localized message/recovery labels wrap without hiding controls or sensitive raw data fallback.

### ERR-124 — Plural/count localization
Batch totals/errors use localization-aware pluralization; counts remain machine truth.

### ERR-125 — Timestamp/timezone
Retry-after/conflict/availability date presentation uses intended site/user timezone while durable times remain normalized.

### ERR-126 — Offline/degraded UX
Offline cached state is labeled and does not imply current remote/entitlement/provider truth.

### ERR-127 — Error persistence across navigation
Critical unresolved operation state remains recoverable via canonical domain history/status, not ephemeral client memory only.

### ERR-128 — Success-after-error clearing
Resolved error UI clears stale state without erasing historical Audit/domain evidence or hiding remaining partial failures.

---

## I. Security, redaction, privacy & Multisite isolation — ERR-129…ERR-144

### ERR-129 — Secret in exception message
Injected secret-bearing exception is redacted before UI/REST/Ability/Job/Audit/diagnostic presentation.

### ERR-130 — SQL error redaction
DB exception cannot expose credentials/raw SQL with private values/schema internals beyond authorized diagnostic policy.

### ERR-131 — Filesystem path redaction
Production public/admin-safe error avoids unnecessary absolute private server paths.

### ERR-132 — Authorization header/cookie redaction
Headers/cookies/tokens never enter normalized safe metadata.

### ERR-133 — Private form/chat content redaction
Error generated while processing P4 content uses resource ID/safe summary, not body/content dump.

### ERR-134 — Provider body redaction
Raw provider response with token/PII is not copied into public envelope/Audit/support bundle.

### ERR-135 — Stack trace production default
Production UI/API does not expose raw stack by default; authorized dev/test diagnostics remain separate.

### ERR-136 — Cross-site error leakage
Site A failure cannot expose Site B domain, user, resource label, row count, stack context or provider binding.

### ERR-137 — Network aggregate privacy
Network coordinator error summary gives authorized per-site status only; lower-scope viewer cannot inspect sibling failure details.

### ERR-138 — Current-blog isolation
Error generated inside switched site restores original context and stores explicit target scope, not accidental current-blog leakage.

### ERR-139 — Correlation enumeration
Correlation ID lookup is Policy-authorized and high entropy/opaque enough not to expose other events by guessing.

### ERR-140 — Support bundle safe copy
Error detail copied into support diagnostics undergoes destination-specific second redaction/preview/consent.

### ERR-141 — Error cache isolation
Cached error/degraded result cannot be reused across user/site/network/entitlement/policy scopes incorrectly.

### ERR-142 — Error response injection safety
Provider/user-controlled error text is escaped for HTML/attribute/JSON/log/CLI destinations and cannot inject markup/control sequences.

### ERR-143 — Error redirect safety
Recovery/reauth/upgrade redirect uses allowlisted/safe target and cannot become open redirect from attacker metadata.

### ERR-144 — Error-generated action authorization
“Retry”, “Reconnect”, “Repair”, “Restore”, “Upgrade” button invokes independently authorized action; possession of error object/correlation ID is not authority.

---

## J. Observability, Audit, incident & support boundaries — ERR-145…ERR-160

### ERR-145 — Structured operational log
Safe code/category/severity/module/action/correlation/attempt/resource refs are logged according environment/retention policy.

### ERR-146 — Audit vs diagnostic separation
Meaningful admin/security failure may create Audit event; raw stack/debug trace remains Diagnostics, not duplicated forever.

### ERR-147 — Repeated identical error aggregation
High-frequency identical errors can be sampled/aggregated operationally without dropping mandatory security Audit events.

### ERR-148 — Error-rate health signal
System Status can report bounded counts/rates/last occurrence category without exposing payload.

### ERR-149 — Critical stop-the-line signal
Data-loss/security/integrity errors can trigger stop-the-line/incident state according governance rather than ordinary retry loop.

### ERR-150 — Incident correlation
Responder can pivot correlation IDs across Audit/Job/Workflow/Event Inbox/Backup/Diagnostics while each remains canonical owner.

### ERR-151 — Baseline failure distinction
Pre-existing failing environment is labeled BASELINE FAILURE and not attributed to current change without evidence.

### ERR-152 — Flaky test distinction
Flaky execution remains flagged/reproducible per quality policy; rerun-until-green cannot erase error evidence.

### ERR-153 — Known limitation state
Unsupported environment/provider feature returns documented unsupported/dependency state, not generic bug or silent fallback.

### ERR-154 — Service outage health
Remote outage is distinguishable from local module bug, product expiry and invalid credentials.

### ERR-155 — Queue backlog health
Backpressure/deferred state is reported separately from per-job execution failures.

### ERR-156 — Migration failure health
Schema/migration block is persistent/actionable and cannot be hidden by one successful page load.

### ERR-157 — Integrity failure recovery reference
Checksum/corrupt-state error links only to verified recovery path/backup state, never “repair” that discards evidence blindly.

### ERR-158 — Support copy correlation
User can provide correlation ID/error code instead of secrets/full stack; support lookup still authorizes account/site scope.

### ERR-159 — Error retention
Operational error logs have bounded retention/classification under PDL; Audit/domain histories keep independent lifecycle.

### ERR-160 — Recovery verification
After repair/retry/restore, health verification must actually pass before error is marked resolved/recovered.

---

## K. Compatibility, performance, failure injection & composite truth — ERR-161…ERR-176

### ERR-161 — Free-only error stack
Free supported surface renders/serializes errors without Pro classes/runtime dependency.

### ERR-162 — Matched Free+Pro
One shared error taxonomy/registry exists; Pro does not fork contradictory error-code system.

### ERR-163 — Free↔Pro skew
Incompatible pair returns safe platform compatibility/degraded error before premium mutation.

### ERR-164 — WordPress/PHP/DB matrix
Run only after P-001 authorization; normalized semantics remain stable across supported profiles.

### ERR-165 — UI/build matrix
Run only after UI/BT authorization; translated/RTL/accessibility/error components work with selected toolchain.

### ERR-166 — Large validation set
Hundreds/thousands of validation issues are bounded/truncated/paginated safely with total count and no memory blow-up.

### ERR-167 — Large batch error set
1k/10k+ child failures use stored/report artifacts/pagination rather than huge REST/UI payload.

### ERR-168 — High error rate
Sustained invalid/attack/provider failure load does not cause recursive log/Audit/error amplification.

### ERR-169 — Error mapper failure
Unexpected failure inside formatter/localizer falls back to safe internal error without leaking original private context or infinite recursion.

### ERR-170 — Localization missing key
Missing translation falls back safely while stable machine code remains unchanged.

### ERR-171 — Unknown HTTP/provider code
Unknown external code maps to bounded integration/internal fallback with raw code safely retained, not arbitrary retry classification.

### ERR-172 — Clock skew retry metadata
Malformed/past/future Retry-After and provider clock skew are bounded; no absurd indefinite/immediate retry storm.

### ERR-173 — Browser stale error state
Out-of-order client responses cannot overwrite newer success/current revision with stale error banner that implies wrong state.

### ERR-174 — Cancellation race
Success/commit racing cancel request produces truthful final domain state; UI cannot always claim “cancelled.”

### ERR-175 — Restore unknown-outcome composite
Restore crashes after DB mutation before health result; system reports recovery/verification unknown/partial and never normal success.

### ERR-176 — Stop-the-line composite
Inject wrong-site authorization attempt + stale revision + secret-bearing provider timeout during destructive async operation. Any cross-site leak/mutation, secret exposure, unsafe retry/duplicate side effect, false success/rollback, or misleading recoverability is Critical.

---

## 7. Required evidence artifact per future run

Each executed fixture records:
- ERR fixture ID;
- WPE commit/version + Free/Pro pair;
- WordPress/PHP/DB/build/UI versions where relevant;
- channel (UI/REST/Ability/CLI/Workflow/Job/AI/provider);
- actor + target site/network/resource scope;
- starting domain/version/idempotency state;
- injected failure/fault condition;
- expected machine code/category/severity/retryability;
- actual transport/status/envelope/UI presentation;
- side-effect/rollback/unknown-outcome assertions;
- redaction/privacy assertions;
- accessibility/localization assertions where relevant;
- Job/Workflow/Audit/correlation IDs;
- retry/backoff/attempt timing where relevant;
- final canonical domain state;
- pass/fail/blocked/skipped/not-executed;
- known limitations.

Use only synthetic secrets/private data in failure-injection fixtures.

---

## 8. MUST NOT / stop-the-line rules

Stop the line on any of the following:
- error/diagnostic response exposes password/token/cookie/Auth header/Vault plaintext/card/private content across unauthorized boundary;
- wrong-site/network error leaks protected existence/data or performs mutation;
- translated human text is parsed as machine/business authority;
- automatic retry duplicates non-idempotent/destructive/provider side effect;
- timeout is represented as definitely failed when outcome may be unknown;
- partial/batch/workflow/import/backup/restore operation is reported full success incorrectly;
- conflict is silently retried by overwriting newer state;
- UI/menu hiding substitutes for authorization;
- error/recovery action itself bypasses capability/Policy/re-auth/impact gates;
- provider raw error enables HTML/log/CLI/header/redirect injection;
- error formatting causes recursion/storage exhaustion under attack;
- critical data-integrity/security failure is hidden as ordinary toast/retry;
- recovery is marked complete before post-recovery verification succeeds.

---

## 9. Current evidence state

- Protocol documented: **ERR-01…ERR-176**.
- Executed: **0/176**.
- `ERR-T/V/A/C/R/P/X/U/S/O` certifications: **0**.
- canonical concrete error registry/mapper implementation: **NOT IMPLEMENTED**.
- exact machine-code catalog finalization: **OPEN / module-implementation dependent**.
- exact REST status/Problem Details mapping: **OPEN / evidence-gated**.
- exact UI error component implementation: **NOT IMPLEMENTED**.
- exact retry budgets/backoff profiles: **OPEN by operation/provider**.
- runtime/accessibility/Multisite/performance certification: **0**.

## 10. Development gate

This protocol authorizes **no executable work**.

Do not implement or execute error registries/mappers/UI components, REST responses, Ability/CLI/AI adapters, Job retries, Workflow/provider failure injection, browser/accessibility tests, Multisite fixtures or benchmarks until explicit owner development/executable-evidence consent is recorded under ADR-0014 and the Approval Ledger.
