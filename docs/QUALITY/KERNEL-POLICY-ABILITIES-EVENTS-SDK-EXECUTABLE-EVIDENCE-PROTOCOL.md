# WPEssential — Kernel / Module Registry / Capability-Policy / Abilities / Events / Extension SDK Executable Evidence Protocol

Status: **Phase 0 evidence specification / NOT AUTHORIZED FOR EXECUTION**  
Date: 2026-08-28  
Work package: `P0-M00-WP26`  
Related: ADR-0001, ADR-0003, ADR-0004, ADR-0007, ADR-0010, ADR-0014, ADR-0069, ADR-0128, ADR-0141, `docs/ARCHITECTURE.md`, `docs/ARCHITECTURE/MODULE-DEPENDENCY-AND-DATA-OWNERSHIP.md`, `docs/ARCHITECTURE/EVENT-AND-ABILITY-CATALOG.md`, `docs/ARCHITECTURE/PER-MODULE-CAPABILITY-ABILITY-EVENT-REGISTRY.md`, `docs/ARCHITECTURE/EXTENSION-SDK-AND-ADAPTER-CONTRACT.md`, `docs/SECURITY/CAPABILITY-POLICY-MATRIX.md`.

## 1. Purpose

This is the canonical future executable-evidence contract for the shared WPEssential platform chain:

`Bootstrap/Kernel → Module Manifest/Registry → Dependency & Availability Resolution → Capability → Resource Policy → Ability/Event contracts → Extension registries/SDK`

It verifies that WPEssential operates as one shared platform instead of independent modules with duplicate registries, inconsistent authorization or ad-hoc callbacks.

The protocol freezes **KPA-01…KPA-176**.

**Executed: 0/176.**

No kernel, registry, capability, Policy, Ability, Event Bus, SDK, extension, plugin activation, build, WordPress runtime, Multisite operation, benchmark or provider execution is authorized by this document.

---

## 2. Truth boundaries

The following remain separate:

`Plugin loaded ≠ Kernel healthy ≠ Module registered ≠ Module available ≠ Module enabled ≠ dependency satisfied ≠ product entitled ≠ principal authenticated ≠ capability granted ≠ resource Policy allowed ≠ Ability registered ≠ Ability exposed to a channel ≠ Ability executed ≠ Event emitted ≠ Event consumed ≠ extension registered ≠ extension certified`

Also:

- menu/route visibility ≠ authorization;
- Super Admin identity ≠ automatic WPE high-risk bypass;
- Ability registration ≠ REST/CLI/Workflow/AI exposure;
- Event delivery ≠ exactly-once execution;
- extension registration ≠ security certification;
- Pro presence ≠ right to fork shared platform services;
- module disable ≠ data deletion;
- current blog context ≠ durable target ownership.

---

## 3. Canonical architecture constraints

1. Free `wpessential` owns bootstrap/kernel and shared platform registries/services required by Free + Pro.
2. `wpessential-pro` registers premium modules into the same compatible Module Registry; it does not create a second kernel/admin app/React/runtime/service registry.
3. Module manifests are declarative contracts for identity, version, edition, dependencies, compatibility, capabilities, routes, assets, migrations, Abilities, health checks, jobs, import/export and uninstall policy.
4. Dependency cycles are prohibited; shared behavior moves to platform services/contracts.
5. Missing/incompatible dependencies produce explicit degraded/unavailable state where safe, not fatal undefined behavior.
6. WordPress/WPE capability answers operation class; Policy answers target resource/context.
7. Security evaluation order remains explicit: authentication/context → site/network boundary → capability → module availability/health → entitlement where relevant → resource Policy → validation/business guards → execution → Audit.
8. Every channel uses the same semantic authorization contract; no UI/REST/CLI/Workflow/AI bypass path.
9. Abilities are typed/versioned operations with schemas, ownership, risk/idempotency/async/privacy metadata.
10. Events are typed past-tense facts with stable IDs, versioned envelopes and at-least-once consumer assumptions.
11. Generic Event payloads exclude plaintext secrets/password-equivalent/card data/unnecessary private content.
12. Extensions register typed descriptors through public registries; they do not mutate private global arrays or use UI-provided arbitrary PHP/JS.
13. Third-party namespaces are vendor-prefixed; `wpessential/*` remains reserved for first-party contracts.
14. Broken optional extensions should fail/degrade locally where possible rather than fatal the platform.

---

## 4. Certification classes

Certify independently:

- `KPA-K` — bootstrap/kernel/service-container lifecycle;
- `KPA-M` — Module Registry/manifests/dependency/data-ownership lifecycle;
- `KPA-P` — capability + resource Policy authorization;
- `KPA-A` — Ability definition/registration/execution/channel parity;
- `KPA-E` — Event catalog/envelope/delivery/consumer safety;
- `KPA-X` — extension SDK/registry/adapter lifecycle;
- `KPA-F` — Free↔Pro shared-platform ownership/version/degraded behavior;
- `KPA-S` — Multisite scope/network-site authority;
- `KPA-O` — failure/concurrency/cache/observability/performance.

Passing one class does not imply another.

---

# 5. Fixed executable fixture matrix

## A. Bootstrap, kernel and service-container lifecycle — KPA-01…KPA-16

### KPA-01 — Minimum supported environment boot
Supported WordPress/PHP/DB profile reaches one healthy WPE kernel without duplicate bootstrap.

### KPA-02 — Unsupported environment boot
Unsupported platform fails/degrades with fatal-safe notice and no partial module mutation.

### KPA-03 — Bootstrap idempotency
Repeated bootstrap entry in one request/process does not create duplicate kernel/service instances or hooks.

### KPA-04 — Free-only kernel
Free plugin alone owns and boots shared kernel/services required by CPT/Taxonomy.

### KPA-05 — Free+Pro matched kernel
Matched Pro attaches to the existing Free kernel; no parallel container/registry/admin app.

### KPA-06 — Pro loaded before expected hook/order edge
Load-order variation resolves through supported bootstrap contract or explicit degraded state; no hidden global-order assumption.

### KPA-07 — Missing autoload/dependency failure
Autoload/bootstrap dependency failure produces controlled diagnostics rather than partially registered runtime.

### KPA-08 — Service registration identity
Each platform service has one canonical contract/identifier and deterministic ownership.

### KPA-09 — Duplicate service registration
Second conflicting registration is rejected/resolved explicitly; no silent last-write-wins for security-critical services.

### KPA-10 — Optional service absence
Module declaring optional service dependency degrades only affected feature and remains truthful.

### KPA-11 — Kernel health state
Healthy/degraded/blocked state is inspectable through diagnostics without exposing secrets.

### KPA-12 — Activation lifecycle routing
Plugin activation routes through intended platform lifecycle and does not implicitly enable every premium module/site.

### KPA-13 — Deactivation lifecycle routing
Deactivation stops runtime registration safely without deleting owned data.

### KPA-14 — Fatal-safe admin recovery
One broken optional module/adapter does not make all wp-admin recovery impossible where architecture permits isolation.

### KPA-15 — Request isolation
Kernel/service mutable request state does not leak between requests or reused workers.

### KPA-16 — Bootstrap observability
Boot diagnostics identify component/version/failure class without dumping environment secrets or sensitive paths beyond policy.

---

## B. Module manifest, registry, dependency DAG and ownership — KPA-17…KPA-32

### KPA-17 — Valid manifest registration
Valid first-party manifest registers stable module ID/version/edition/dependencies/features.

### KPA-18 — Duplicate module ID
Conflicting module ID registration is rejected; no silent overwrite.

### KPA-19 — Reserved namespace ownership
Third-party module cannot claim reserved official module/Ability/Event namespace.

### KPA-20 — Missing hard dependency
Module becomes explicit unavailable/degraded and does not boot dependent mutable behavior.

### KPA-21 — Missing soft dependency
Base module remains usable while optional integration is unavailable and explained.

### KPA-22 — Dependency order
Acyclic dependency graph boots in deterministic topological order independent of discovery order.

### KPA-23 — Direct dependency cycle
A→B→A cycle is detected before runtime boot; no recursion/fatal loop.

### KPA-24 — Longer dependency cycle
A→B→C→A is reported with useful cycle path and affected modules remain blocked/degraded.

### KPA-25 — Version constraint satisfied
Compatible minimum/maximum Platform API/dependency version allows module availability.

### KPA-26 — Version constraint unsatisfied
Incompatible module/dependency version is blocked/degraded without class/schema corruption.

### KPA-27 — Enable transition
Authorized enable registers intended routes/assets/Abilities/jobs only once and updates truthful module state.

### KPA-28 — Disable transition
Disable unregisters owned runtime surfaces/stops mutable operations while preserving owned data by default.

### KPA-29 — Disable with dependents
Dependency impact is surfaced; dependents degrade/block according declared graph rather than cascading data deletion.

### KPA-30 — Delete-data separation
Delete module data is a separate high-impact operation with ownership inventory/dependency preview/recovery/authorization.

### KPA-31 — Authoritative data ownership
Consumer module references another domain by stable contract/ID and cannot directly mutate private owned tables/classes through supported path.

### KPA-32 — Manifest revision/cache invalidation
Manifest/version/availability changes invalidate cached registry results deterministically.

---

## C. Capability and resource Policy — KPA-33…KPA-48

### KPA-33 — Unauthenticated denial
Protected operation requiring identity denies before capability/Policy execution and leaks no protected resource detail.

### KPA-34 — Missing capability denial
Authenticated principal with visible UI route but missing required capability is denied server-side.

### KPA-35 — Capability granted + Policy denied
Operation class grant cannot override target-resource Policy denial.

### KPA-36 — Capability + Policy allowed
Authorized principal on allowed resource reaches validation/execution path.

### KPA-37 — Wrong-resource IDOR
Actor authorized for Resource A changes ID to Resource B and is denied before read/mutation.

### KPA-38 — UI hiding is not authorization
Hidden menu/button route remains denied on direct REST/Ability/request invocation.

### KPA-39 — Current creator not permanent owner
Creator ID alone cannot preserve edit/delete authority after capability/Policy revocation.

### KPA-40 — Module unavailable gate
Capability/Policy allow does not execute operation when required module/service is unavailable/degraded.

### KPA-41 — Product entitlement ordering
Where product entitlement is relevant, expiry/outage semantics follow product contract and never substitute for Membership/user authorization.

### KPA-42 — High-risk re-auth
Configured high-impact restore/reset/secret/admin-equivalent operation enforces recent-auth/re-auth without becoming sole authorization control.

### KPA-43 — Explicit audited bypass
Any supported exceptional bypass is separately capability-gated, narrow, re-authorized and audited; no generic bypass switch.

### KPA-44 — Stale capability change
Role/capability revocation becomes effective within defined cache-generation contract; stale allow cannot persist indefinitely.

### KPA-45 — Policy cache key isolation
Policy allow/deny cache includes principal/resource/site/network/generation dimensions required to prevent cross-context reuse.

### KPA-46 — Policy exception/failure
Policy evaluator exception/timeouts fail safely according operation class; no default allow.

### KPA-47 — Policy explanation privacy
Access-explain output reports safe decision categories without revealing protected resource payload/rules beyond viewer authorization.

### KPA-48 — Audit decision correlation
Sensitive authorization decision records safe capability/Policy outcome and correlation ID through Audit contract without secret/private payloads.

---

## D. Ability definition, registration and schemas — KPA-49…KPA-64

### KPA-49 — Stable Ability ID
Namespaced Ability ID resolves to one owning module/versioned contract.

### KPA-50 — Duplicate Ability ID collision
Conflicting registration is rejected; first/last discovery order cannot silently change behavior.

### KPA-51 — Reserved Ability namespace
Third party cannot register unauthorized `wpessential/*` first-party Ability ID.

### KPA-52 — Required Ability metadata
Definition exposes owner, input/output schemas, capability/Policy, risk class, idempotency, sync/async, privacy, availability and version/deprecation metadata.

### KPA-53 — Input schema valid
Valid typed input is normalized and reaches authorization/business validation.

### KPA-54 — Input schema invalid
Unknown/wrong type/oversized/invalid enum input fails before side effects.

### KPA-55 — Output schema conformance
Successful output conforms to declared schema and redaction policy.

### KPA-56 — Sensitive output annotation
Protected fields are not exposed through generic Ability output/channel without explicit policy.

### KPA-57 — Read vs write classification
Read/explain Ability cannot mutate durable state through supported path.

### KPA-58 — Destructive classification
Destructive Ability carries impact/recovery/idempotency/precondition metadata and channel policies.

### KPA-59 — Dry-run support truth
Ability declaring dry-run produces no durable mutation/provider side effect and reports deterministic impact where supported.

### KPA-60 — Async metadata
Async Ability returns/links typed Job/Run identity without pretending completion.

### KPA-61 — Availability dependency
Ability from disabled/degraded module cannot remain callable as a dangling privileged callback.

### KPA-62 — Ability version compatibility
Additive compatible schema evolution works; breaking change requires version/new contract/deprecation path.

### KPA-63 — Ability removal/deprecation
Deprecated ID follows published window/migration behavior; consumers get explicit unavailable/version error rather than another Ability.

### KPA-64 — Ability registry cache invalidation
Enable/disable/version change cannot leave stale callable descriptor beyond defined generation boundary.

---

## E. Ability invocation and channel parity — KPA-65…KPA-80

### KPA-65 — Admin UI invocation
UI invokes semantic Ability/application contract rather than bypassing server authorization with client-only logic.

### KPA-66 — REST invocation parity
REST channel applies same authentication/capability/Policy/validation/business guard semantics.

### KPA-67 — WP-CLI invocation parity
CLI administrative context does not bypass resource/site/network Policy merely because process is local.

### KPA-68 — Workflow invocation parity
Workflow service principal is explicit and reauthorized at execution; creator permissions are not frozen forever.

### KPA-69 — AI/MCP invocation parity
AI-exposed Ability uses same principal and Policy; model arguments are untrusted input.

### KPA-70 — AI exposure allowlist
Registered Ability is not automatically AI-exposed; read/explain/preview-first allowlist behavior is verified.

### KPA-71 — Destructive AI default denial
Destructive Ability remains unavailable to AI unless explicit approved exposure policy exists; exposure never grants authorization itself.

### KPA-72 — Channel-specific presentation only
Channel adapter may change transport/format but not semantic authorization or operation meaning.

### KPA-73 — Idempotent duplicate invocation
Same logical operation/idempotency key replay does not duplicate destructive/business side effect.

### KPA-74 — Missing idempotency for unsafe retry
Non-idempotent/unknown operation cannot be blindly auto-retried as safe.

### KPA-75 — Optimistic precondition
Version/ETag/state precondition detects stale mutation and returns explicit conflict rather than lost update.

### KPA-76 — Timeout/unknown outcome
Timeout after possible side effect becomes unknown/reconciliation state; no false failure/success assumption.

### KPA-77 — Async cancellation
Cancel request is cooperative/domain-aware and does not claim rollback of already committed external effects.

### KPA-78 — Error normalization
Exceptions normalize to safe stable error taxonomy without stack/secret leakage in public channels.

### KPA-79 — Correlation propagation
One invocation carries request/operation/correlation identity into Job/Workflow/Event/Audit boundaries without using correlation as authorization.

### KPA-80 — Direct callback bypass regression
Known internal callback/service route cannot be invoked through supported public channel to skip Ability/Policy contract.

---

## F. Event catalog, envelope, emission and consumption — KPA-81…KPA-96

### KPA-81 — Stable Event type
Namespaced past-tense Event type resolves to one owner/schema version.

### KPA-82 — Duplicate Event type collision
Conflicting registration is rejected rather than discovery-order overwrite.

### KPA-83 — Event envelope completeness
Event ID/type/schema version/occurred time/scope/actor/correlation/entity/source/privacy metadata validate.

### KPA-84 — Secret payload rejection/redaction
Password/token/Vault/card/reusable private URL or forbidden content cannot enter generic Event payload.

### KPA-85 — Scope identity
Site/network ownership is explicit and independent of current request/blog context.

### KPA-86 — Primary commit before noncritical event
Domain mutation commits according owning transaction semantics before asynchronous/noncritical side effects consume event.

### KPA-87 — Event emission failure after commit
Failure to publish noncritical event does not roll back already valid mutation unless explicitly transactional; reconciliation truth is visible.

### KPA-88 — At-least-once duplicate delivery
Consumer receives same event more than once and remains idempotent using event/business keys.

### KPA-89 — Out-of-order events
Consumer uses revision/state/version/source sequence where order matters; arrival order alone cannot corrupt state.

### KPA-90 — Event replay
Authorized replay preserves original event identity/provenance and does not fabricate newly occurred source fact.

### KPA-91 — Event schema additive evolution
Old consumer tolerates compatible unknown additive fields according version contract.

### KPA-92 — Event breaking change
Breaking schema requires explicit version/migration/consumer compatibility behavior.

### KPA-93 — Diagnostic/audit-only event
High-volume/sensitive diagnostic class is not automatically exposed as general Workflow trigger.

### KPA-94 — Consumer disabled
Disabled/unavailable consumer cannot cause producer failure unless declared hard transactional dependency.

### KPA-95 — Consumer crash/retry
Crash after possible side effect uses consumer idempotency/reconciliation; no exactly-once claim.

### KPA-96 — Event observability
Event delivery/consumer status is diagnosable through safe IDs/metrics without turning Event Bus into canonical domain history.

---

## G. Extension SDK and public registries — KPA-97…KPA-112

### KPA-97 — Vendor namespace registration
Valid third-party vendor-prefixed extension ID registers into documented public registry.

### KPA-98 — Namespace collision
Duplicate extension ID or official namespace collision is rejected explicitly.

### KPA-99 — Descriptor validation
Extension descriptor validates version, Platform API range, capabilities, config schema, dependencies, privacy and health metadata.

### KPA-100 — Incompatible Platform API
Unsupported extension version remains unavailable/degraded and cannot invoke private internals.

### KPA-101 — Missing extension dependency
Extension degrades locally with actionable health state; core kernel remains available.

### KPA-102 — Public interface boundary
Extension uses documented interface/registry rather than direct private table/class mutation in supported flow.

### KPA-103 — No arbitrary UI code execution
Admin-configured values cannot become arbitrary PHP/JS/eval execution under SDK.

### KPA-104 — Adapter capability honesty
Read-only provider declares read-only; unsupported write/sort/filter/transaction capability is not emulated silently.

### KPA-105 — Sensitive configuration
Extension secret fields store/use Vault references according policy and are not rendered/exported plaintext.

### KPA-106 — Extension asset scoping
Assets load only where needed, avoid duplicate host React/runtime, respect RTL/localization/accessibility and do not globally pollute CSS/JS.

### KPA-107 — Extension health check failure
Broken optional adapter produces scoped degraded status and normalized error, not platform fatal.

### KPA-108 — Extension disable
Owned hooks/Abilities/events/jobs/assets unregister; owned data preserved by default; shared data remains owner-controlled.

### KPA-109 — Extension uninstall
Uninstall follows declared ownership/retention/export policy and cannot delete platform/shared/other-extension data silently.

### KPA-110 — Extension deprecation
Deprecated public hook/registry/adapter contract follows documented compatibility window and replacement path.

### KPA-111 — Certification truth
Installed/registered extension is distinguishable from official/certified/supported status; registration never creates certification claim.

### KPA-112 — Extension security regression
IDOR/SSRF/SQL injection/path traversal/webhook replay/secret handling checks apply to extension boundary relevant to adapter type.

---

## H. Free↔Pro shared ownership, compatibility and module availability — KPA-113…KPA-128

### KPA-113 — Free owns shared registry
Free package exposes one shared Module/Ability/Event/Integration registry contract usable by compatible Pro.

### KPA-114 — Pro does not fork kernel
Pro activation does not instantiate parallel kernel/container/registry/policy/event/React/admin runtime.

### KPA-115 — Matched Free+Pro module merge
Premium manifests merge deterministically into shared registry without replacing Free module ownership.

### KPA-116 — Pro absent
Free CPT/Taxonomy remain functional within certified profile and no Pro class reference fatal occurs.

### KPA-117 — Free older than Pro requirement
Incompatible pair fails/degrades before premium modules boot or mutate schema/runtime.

### KPA-118 — Pro older than Free compatibility
Unsupported pair remains safe/degraded according FP contract; no partial registry corruption.

### KPA-119 — Pro expiry
Commercial management lock changes availability according ADR-0007 while preserving data/safe deployed runtime and not weakening Membership protection.

### KPA-120 — Product service outage
Service unreachable is not interpreted as immediate expiry/revocation; cached signed entitlement rules remain separate from module registry mechanics.

### KPA-121 — Premium module disabled
Shared service remains owned by Free/kernel; disabling Pro feature does not unregister platform service required by Free/other modules.

### KPA-122 — Shared schema/API ownership
One Platform API/schema version authority prevents Free/Pro both trying to migrate same shared resource independently.

### KPA-123 — Duplicate asset/runtime prevention
Free+Pro do not ship/load competing React/ReactDOM or duplicate platform runtime where host/shared contract owns it.

### KPA-124 — Upgrade order Free first
Staged compatible upgrade preserves registry/API handshake and degraded state if second artifact not yet updated.

### KPA-125 — Upgrade order Pro first
Opposite staged order remains fatal-safe/read-only/degraded as contract dictates.

### KPA-126 — Rollback one artifact
Mixed rollback state is detected and blocked/degraded rather than silently running unsupported API pair.

### KPA-127 — Uninstall Pro
Premium data retention policy applies; shared Free kernel/registries/free definitions remain intact.

### KPA-128 — Uninstall Free while Pro present
Unsupported dependency state is detected safely; Pro cannot become a standalone shadow kernel accidentally.

---

## I. Multisite site/network authority and registry scope — KPA-129…KPA-144

### KPA-129 — Network-active registration
Network activation creates one installation/kernel contract while site availability remains explicit per module/product policy.

### KPA-130 — Per-site activation profile
Where supported, target site module state is explicit and does not alter sibling site state.

### KPA-131 — Site Admin cannot manage network registry
Direct request from Site Admin to network module/config registry action is denied server-side.

### KPA-132 — Super Admin still evaluated
High-risk network operation still passes dedicated WPE capability/Policy/confirmation/Audit requirements.

### KPA-133 — Same resource ID across sites
Policy/Ability/Event registry consumers use explicit scope; numeric ID collision cannot authorize sibling resource.

### KPA-134 — `switch_to_blog()` context
Switching site context does not grant target-site capability/Policy or rewrite durable ownership.

### KPA-135 — Nested site switching failure
Exception during nested context restores original blog and cannot leak target site into next operation.

### KPA-136 — Worker reuse across sites
Reused long-lived worker clears site/principal/Policy/registry request-local state between jobs.

### KPA-137 — Network default module policy
Network defaults/inheritance/locks show provenance; site override exists only where permitted.

### KPA-138 — Network module disable with site dependents
Impact is bounded/explicit; child data is preserved and affected site modules become truthful degraded states.

### KPA-139 — New site provisioning
Site Lifecycle initializes applicable module registry state exactly once; no automatic paid allocation beyond product policy.

### KPA-140 — Site archive/delete
Delayed Ability/Job/Event consumer rechecks lifecycle/target authority before side effects.

### KPA-141 — Clone/staging
Copied registry/entitlement/extension state is revalidated and cannot silently become second production authority.

### KPA-142 — Network-to-network transfer
Module/extension references and scope identities remap explicitly; network-owned secrets/contracts are not blindly copied.

### KPA-143 — Network aggregate operation
Authorized fan-out enumerates/paginates explicit sites with bounded JobService work, checkpointing and per-site Policy.

### KPA-144 — 100/1k/10k-site registry scale
Measure registry/policy/cache/fan-out cost; no “large network supported” claim without executed profile evidence.

---

## J. Registry/cache concurrency, failure and observability — KPA-145…KPA-160

### KPA-145 — Concurrent module enable
Two enable requests resolve one logical transition; no duplicate hooks/jobs/migrations/Abilities.

### KPA-146 — Concurrent disable vs invoke
Ability invocation overlapping module disable rechecks availability/preconditions before mutable side effect.

### KPA-147 — Concurrent extension register collision
Two conflicting registrations produce deterministic rejection, never nondeterministic winner.

### KPA-148 — Registry cache cold start
Cold cache builds deterministic registry from canonical manifests/descriptors.

### KPA-149 — Registry cache hit
Cached result preserves same authorization/availability semantics and is version/generation scoped.

### KPA-150 — Stale registry generation
Manifest/module/version change invalidates old generation; stale callable security contract cannot persist indefinitely.

### KPA-151 — Persistent object cache failure
Cache outage degrades to correct source resolution or explicit unavailable state; no stale security default allow.

### KPA-152 — Partial registry build crash
Crash during rebuild cannot publish half-built authoritative registry snapshot.

### KPA-153 — Recovery after bad extension
Disabling/quarantining broken optional extension restores healthy shared registry without manual DB surgery where supported.

### KPA-154 — Dependency state drift
Peer module/version disappears after initial boot; next operation detects drift and blocks/degrades safely.

### KPA-155 — Policy service unavailable
Protected operation fails safely; menu/UI availability cannot substitute.

### KPA-156 — Event Bus unavailable
Primary mutation follows declared transactional/noncritical event policy and exposes reconciliation requirement truthfully.

### KPA-157 — Ability registry unavailable
Channel returns safe unavailable/degraded error; no direct callback fallback that bypasses Policy.

### KPA-158 — Extension registry unavailable
Optional adapters fail isolated; core first-party operation remains available where dependency is optional.

### KPA-159 — Diagnostics snapshot
System Status reports safe kernel/module/dependency/registry generation/incompatible-extension state without exposing secrets/private configuration.

### KPA-160 — Audit correlation
Enable/disable/dependency conflict/high-risk Ability/extension security events correlate to AUD contract without duplicating raw payloads.

---

## K. Performance, compatibility and composite security — KPA-161…KPA-176

### KPA-161 — 31-surface manifest inventory
Load all planned first-party manifests in supported profile; verify deterministic unique IDs/dependency graph and bounded boot work.

### KPA-162 — Large Ability catalog
Measure registration/resolution memory/time for full first-party catalog; no repeated schema compilation on every lookup if avoidable.

### KPA-163 — Large Event catalog
Measure event descriptor registration/dispatch lookup overhead independently from consumer execution.

### KPA-164 — Large extension inventory
Synthetic compatible extension descriptors measure registry build/cache/health-check cost with explicit limits.

### KPA-165 — Bootstrap query budget
Measure DB/cache queries on normal admin/public requests and prevent per-module N+1 option/table probing.

### KPA-166 — Public request minimal boot
Frontend request that needs no WPE module UI avoids loading unnecessary admin/extension assets and heavy registries beyond required runtime contracts.

### KPA-167 — Admin route lazy loading
Opening one module screen loads only required module/adapters/assets while shared registry metadata remains available.

### KPA-168 — Compatibility matrix execution
Run only after P-001/BT/CI authorization across supported WordPress/PHP/DB/build profiles; record exact versions.

### KPA-169 — Free↔Pro matrix execution
Run only under FP-certified artifact pairs; KPA cannot promote FP certification itself.

### KPA-170 — Multisite matrix execution
Run MSI/LC-required site/network profiles; KPA pass cannot promote MS1+/SL certification independently.

### KPA-171 — Unauthorized arbitrary PHP/eval attempt
Admin/REST/Ability/extension configuration cannot inject executable PHP/JS/eval through supported UI contracts.

### KPA-172 — Unauthorized raw SQL escape attempt
Normal Ability/Query/extension input cannot become unrestricted destructive raw SQL execution.

### KPA-173 — Forged principal/scope metadata
Caller-supplied actor/site/network/correlation fields cannot override trusted authentication/target resolution.

### KPA-174 — Hidden capability grant regression
Enabling module/extension/menu preset cannot silently grant unrelated high-risk capabilities or network authority.

### KPA-175 — Channel escalation composite
Principal denied in UI directly invokes same semantic operation through REST/CLI/Workflow/AI; every channel must deny consistently according actual authority context.

### KPA-176 — Stop-the-line composite
Inject incompatible Pro + duplicate Ability/Event/extension IDs + wrong-site target + stale Policy cache during a destructive Ability. Any authorization bypass, wrong-site mutation, duplicate kernel/registry, secret exposure, arbitrary code/SQL execution, silent contract overwrite or fabricated success is a critical failure.

---

## 6. Required evidence artifact per future run

Each executed fixture records:
- KPA fixture ID;
- WPE commit/version;
- Free/Pro artifact pair where applicable;
- WordPress/PHP/DB/build versions;
- single-site/Multisite topology;
- object-cache state;
- module/extension manifests and Platform API ranges;
- actor/authentication/capability/Policy target scope;
- registry generation/cache state;
- Ability/Event/extension IDs + schema versions where relevant;
- starting dependency/module availability state;
- expected authorization/availability/result;
- actual result/error category;
- side-effect/idempotency/retry assertions;
- privacy/secret assertions;
- Audit/correlation identifiers;
- query/time/memory metrics where relevant;
- final module/registry/domain state;
- pass/fail/skipped/not-executed;
- known limitations.

Evidence is version/profile scoped and may expire after incompatible Platform API, WordPress, Free/Pro, registry, Policy, Ability/Event schema or extension changes.

---

## 7. MUST NOT / stop-the-line rules

Stop the line on any of the following:
- second/parallel kernel or security-critical registry silently becomes authoritative;
- duplicate module/Ability/Event/extension IDs resolve by nondeterministic or silent last-write-wins;
- menu/route visibility is treated as authorization;
- capability allow bypasses required resource Policy;
- Site Admin gains Network authority or wrong-site resource access through request coordinates/current blog;
- Super Admin silently bypasses explicitly required high-risk WPE Policy/confirmation/Audit;
- REST/CLI/Workflow/AI path bypasses semantic Ability/Policy contract;
- registered Ability is automatically exposed to AI/destructive automation without explicit exposure policy;
- arbitrary PHP/JS/eval or unrestricted raw SQL becomes a normal UI/Ability extension mechanism;
- Event payload contains passwords/tokens/Vault plaintext/card secrets/reusable private URLs;
- event consumer assumes exactly-once and duplicates external/destructive side effects;
- extension registration is presented as security/support certification;
- module disable cascades deletion or silently weakens Membership/security protections;
- Pro forks/duplicates Free-owned shared kernel/registry/service/React runtime;
- stale registry/Policy cache preserves revoked access beyond declared security boundary;
- broken optional extension fatals unrelated core platform where isolation contract says optional;
- fabricated success after unknown/partial execution.

---

## 8. Current evidence state

- Protocol documented: **KPA-01…KPA-176**.
- Executed: **0/176**.
- `KPA-K/M/P/A/E/X/F/S/O` certifications: **0**.
- canonical concrete Kernel/service-container implementation: **NOT SELECTED / NOT IMPLEMENTED**.
- canonical Module Registry persistence/cache implementation: **NOT SELECTED / NOT IMPLEMENTED**.
- exact Policy evaluator/cache implementation: **NOT SELECTED / NOT IMPLEMENTED**.
- exact WordPress Abilities API bridge/profile: **OPEN / evidence-gated**.
- exact Event Bus backend/delivery implementation: **OPEN / evidence-gated**.
- exact extension registry lifecycle/cache implementation: **OPEN / evidence-gated**.
- extension certification harness: **NOT IMPLEMENTED**.
- runtime/Multisite/performance certification: **0**.

## 9. Development gate

This protocol authorizes **no executable work**.

Do not create kernel/container/registry/Policy/Ability/Event/SDK runtime code, register WordPress hooks, install packages, activate plugins, mutate roles/capabilities, execute Abilities/events/extensions, create Multisite fixtures or run benchmarks until explicit owner development/executable-evidence consent is recorded under ADR-0014 and the Approval Ledger.
