# WPEssential — Module Lifecycle, Disable, Uninstall & Recovery Executable Evidence Protocol

Status: **Phase 0 evidence specification / EXECUTION NOT AUTHORIZED**  
Date: 2026-08-28  
Work package: `P0-M00-WP31`  
Related: ADR-0007, ADR-0010, ADR-0014, ADR-0069, ADR-0128, ADR-0143, ADR-0144, ADR-0145, ADR-0147, `docs/MODULE-LIFECYCLE-AND-UNINSTALL.md`, `docs/ARCHITECTURE/MODULE-DEPENDENCY-AND-DATA-OWNERSHIP.md`, Module Registry, Policy, JobService, Definition Repository, Audit, Privacy/Data Lifecycle, Multisite Site Lifecycle.

## 1. Purpose

Freeze the future executable evidence required before WPEssential may claim that module enable/disable/re-enable, dependency loss, plugin deactivation, Pro expiry, migration failure, uninstall, cleanup and recovery behavior are safe and predictable.

The protocol freezes **MLC-01…MLC-176**.

Current execution truth: **0/176 executed**.

No module lifecycle runtime certification exists.

This contract is intentionally cross-module. It does not replace domain-specific evidence for migrations, privacy erasure, backup/restore, Membership enforcement, provider cleanup, Definition deletion, Job execution or Multisite lifecycle. It verifies lifecycle orchestration and truth boundaries across those domains.

Nothing in this document authorizes plugin/module execution, WordPress activation/deactivation/uninstall hooks, database writes, file deletion, queue processing, migration, cache flush, provider calls or cleanup.

---

## 2. Canonical module states

Every module maps to one canonical platform state:

- `unavailable`
- `available_disabled`
- `enabled`
- `degraded`
- `read_only`
- `paused`
- `migration_required`
- `unhealthy`

A diagnostic reason may be more specific, but must not invent a contradictory hidden state.

State dimensions remain distinct:

`module availability ≠ user enable intent ≠ dependency health ≠ schema readiness ≠ entitlement/editing rights ≠ runtime enforcement ≠ job activity ≠ provider connection ≠ data retention ≠ cleanup state ≠ health certification`

A module being disabled does not mean its data is deleted. A Pro entitlement ending does not mean the module is absent. A missing optional integration does not mean the base module is disabled. Plugin deactivation is broader than a module toggle. Uninstall hook execution is not permission for surprise destructive cleanup.

---

## 3. Independent certification classes

Future evidence certifies independently:

- `MLC-S` — canonical state calculation and registry truth;
- `MLC-E` — enable/preflight/activation orchestration;
- `MLC-D` — disable and dependency degradation behavior;
- `MLC-R` — re-enable/reactivation/reconciliation;
- `MLC-P` — Pro expiry/plugin deactivation boundaries;
- `MLC-M` — migration-required/failure/recovery-mode behavior;
- `MLC-U` — uninstall and cleanup-level safety;
- `MLC-O` — data ownership/Definition/runtime cleanup orchestration;
- `MLC-J` — Jobs/assets/caches/Abilities/Events side-effect lifecycle;
- `MLC-X` — security/privacy/Multisite isolation;
- `MLC-Q` — concurrency/scale/observability/recovery quality.

Passing one class never certifies another.

---

## 4. Evidence ownership / anti-duplication

This protocol does **not** replace:

- `KPA-*` for kernel/registry/Policy/Ability/Event execution correctness;
- `FP-*` for exact Free↔Pro compatible/incompatible pair behavior;
- `VER-*` for version/migrator/deprecation semantics;
- `PDL-*` for personal-data export/erase/retention correctness;
- `DEF-*` for Definition persistence/revision/deletion mechanics;
- `JS-*` for Job claim/retry/idempotency execution;
- `BK-*` for backup/restore correctness;
- `MBR-*` / protected-file evidence for Membership enforcement;
- `MSI-*` / `LC-*` for Multisite scope and site lifecycle;
- provider-specific certification for remote cleanup/revocation.

MLC evidence references those results where applicable and never auto-promotes their certification.

---

# 5. Fixed executable fixture matrix

## A. Canonical states, manifest truth and registry calculation — MLC-01…MLC-16

### MLC-01 — Fresh module unavailable
Module absent from edition/build maps to `unavailable`, not merely disabled.

### MLC-02 — Available but disabled
Installed/available module with user enable intent off maps to `available_disabled` while definitions/data remain discoverable for authorized recovery/export.

### MLC-03 — Enabled healthy prerequisites
Available module with valid dependencies/schema/entitlement and enable intent maps to `enabled`.

### MLC-04 — Hard dependency missing
Enabled intent + missing hard dependency maps to `degraded` or another explicitly accepted safe state; no hidden partial normal operation.

### MLC-05 — Editing entitlement expired
Safe deployed runtime may map to `read_only`/`paused` as module contract requires without becoming `unavailable`.

### MLC-06 — Runtime mutations paused
Paused automations remain distinguishable from disabled module and preserve definitions/data.

### MLC-07 — Migration required
Schema version requiring migration maps to `migration_required` before incompatible writes execute.

### MLC-08 — Migration failed/unhealthy
Failed invariant/migration maps to `unhealthy` or explicit safe degraded state with reason.

### MLC-09 — One canonical state per evaluation
Same snapshot cannot emit contradictory `enabled` and `migration_required` authority to different callers.

### MLC-10 — Diagnostic reason separate
Specific reason (`dependency_too_old`, `license_read_only`, etc.) augments canonical state without replacing stable machine state.

### MLC-11 — State cache freshness
Change dependency/entitlement/schema state and ensure stale cached module state is invalidated or rejected.

### MLC-12 — State calculation side-effect free
Reading module health/state performs no destructive migration, provider call or data deletion.

### MLC-13 — Unknown module ID
Unknown/colliding module identifier fails safely and cannot alias another module state.

### MLC-14 — Manifest ownership
Free-owned shared module/kernel state is not shadowed by Pro/private duplicate registry entries.

### MLC-15 — State observability
Authorized diagnostics show availability, intent, dependency, schema, entitlement and health dimensions separately.

### MLC-16 — Registry scale
Large module/extension catalog state evaluation remains bounded and avoids repeated expensive dependency scans per request.

---

## B. Enable preflight and activation orchestration — MLC-17…MLC-32

### MLC-17 — Enable clean state
Enable from valid clean prerequisites transitions predictably and registers only owned runtime surfaces.

### MLC-18 — Edition/entitlement unavailable
Enable action refuses unavailable paid module without deleting existing user data or faking availability.

### MLC-19 — Platform API incompatible
Enable blocked/degraded before optional module calls unsupported shared interfaces.

### MLC-20 — Hard dependency absent
Enable preflight blocks activation before partial hooks/assets/jobs become authoritative.

### MLC-21 — Hard dependency version incompatible
Dependency exists but version/capability range fails; UI class existence alone does not bypass preflight.

### MLC-22 — Soft dependency absent
Base module can enable while optional adapter remains degraded/inactive.

### MLC-23 — Required third-party adapter unavailable
Module requiring external adapter does not activate mutation paths that assume it exists.

### MLC-24 — Migration required before enable
Enable transitions to `migration_required`; no destructive migration runs silently merely from toggle/request load.

### MLC-25 — High-impact enable preview
Security/access/destructive consequences are previewed before action where contract requires explicit acknowledgement.

### MLC-26 — Capability/CSRF protection
Unauthorized or CSRF enable request cannot change module intent/state.

### MLC-27 — Reauthentication high-risk enable
Where required by security policy, stale session cannot enable high-impact module without reauthentication.

### MLC-28 — Registration ordering
Capabilities/Policies/Definitions/runtime adapters register in dependency-safe order without exposing half-initialized mutation path.

### MLC-29 — Enable audit
Successful/failed enable records safe state transition and reason without secrets/private payloads.

### MLC-30 — Enable rollback on registration failure
Failure during activation leaves explicit prior/degraded state and cleans only newly created transient registrations, not user data.

### MLC-31 — Repeated enable idempotency
Duplicate/concurrent enable command does not duplicate jobs/hooks/options or corrupt state.

### MLC-32 — Enable request crash window
Crash between intent persistence and runtime registration reconciles deterministically on next boot rather than producing indefinite hidden partial state.

---

## C. Disable semantics and preservation — MLC-33…MLC-48

### MLC-33 — Default disable preserves definitions
Module toggle off leaves user-authored Definitions intact.

### MLC-34 — Default disable preserves runtime business data
Entries/history/rows owned by module are retained unless a separate explicit cleanup action applies.

### MLC-35 — Disable stops new ordinary mutations
New module-specific mutation endpoints/Abilities/jobs are blocked or safely paused as lifecycle contract requires.

### MLC-36 — Read-only/recovery surfaces remain
Authorized health/export/recovery UI may remain available without loading full optional runtime.

### MLC-37 — Assets/hooks scoped removal
Module-specific editor/runtime assets/hooks unregister where safe and do not leave duplicate callbacks on re-enable.

### MLC-38 — Ability lifecycle
Disabled module does not leave callable mutation Ability that bypasses disabled state; diagnostic/export Ability remains only if explicitly allowed.

### MLC-39 — Event consumer lifecycle
Disabled module stops new side-effect consumers without mutating historical events or stealing another module's registrations.

### MLC-40 — Job admission lifecycle
New module work is not admitted while disabled; existing queued work follows explicit pause/cancel/reconcile policy.

### MLC-41 — Cache behavior
Disable invalidates/marks stale only module-owned derived cache needed for truthful state; no unrelated global cache purge by default.

### MLC-42 — Dependent hard module degradation
Dependent module becomes explicit degraded/inert; its data remains preserved.

### MLC-43 — Soft integrations unaffected
Disabling one optional integration does not unnecessarily disable unrelated base module functions.

### MLC-44 — Disable security-sensitive module
Membership/Protector/protected-access module cannot accidentally expose protected content merely because editing/module UI is disabled.

### MLC-45 — Disable during active request/job
In-flight work rechecks lifecycle/state at safe commit/side-effect boundary and cannot blindly complete privileged mutation after disable.

### MLC-46 — Concurrent disable commands
Duplicate disable remains idempotent and does not double-unregister/delete data.

### MLC-47 — Disable failure
Partial failure enters observable degraded/unhealthy state; UI cannot report disabled-success if mutation paths remain unintentionally active.

### MLC-48 — Disable audit/report
Result lists stopped/retained/degraded surfaces without copying sensitive payloads.

---

## D. Re-enable, reactivation, Pro expiry and plugin deactivation — MLC-49…MLC-64

### MLC-49 — Re-enable same schema
Previously disabled module resumes registrations using retained data without duplicate definitions/jobs.

### MLC-50 — Re-enable with schema drift
Schema/version preflight occurs before normal writes; migration-required state blocks unsafe resume.

### MLC-51 — Re-enable stale jobs
Old queued jobs are revalidated by target state/revision/idempotency and are not blindly replayed.

### MLC-52 — Re-enable cache recompilation
Derived descriptor/cache generations refresh only where required and preserve protected-data isolation.

### MLC-53 — Re-enable provider disconnected
Base module resumes safely while provider-specific behavior remains explicit degraded/credential-required.

### MLC-54 — Pro entitlement valid→expired
Data persists; editing/new paid-only creation can lock while accepted deployed safe output/enforcement follows ADR-0007.

### MLC-55 — Pro expiry does not expose protected content
Access-enforcement rules required for existing protected output remain fail-safe according accepted Free/Pro boundary.

### MLC-56 — Pro expiry pauses cost/mutation automation
Paid/background operations that must pause do so explicitly without deleting schedules/history.

### MLC-57 — Pro expiry export/recovery
Authorized user retains permitted export/recovery access without granting edit/execute entitlement.

### MLC-58 — Pro entitlement restored
Module reconciles state and does not replay expired-period jobs or provider actions without validation.

### MLC-59 — Free plugin deactivation
WordPress plugin deactivation performs no destructive cleanup.

### MLC-60 — Pro plugin deactivation
Pro deactivation preserves data and does not delete Free-owned/shared state.

### MLC-61 — Security warning before plugin deactivation
Where loss of runtime enforcement is material, admin receives truthful warning; warning is not itself enforcement proof.

### MLC-62 — Plugin reactivation after safe deactivation
Registry/schema/dependencies reconcile before modules resume mutations.

### MLC-63 — Plugin deactivation with scheduled work
Scheduled/queued work cannot continue privileged side effects as if plugin remained healthy unless explicitly owned by safe shared Free layer.

### MLC-64 — FP/VER boundary reference
Exact Free/Pro pair and version-skew behavior remains FP/VER-owned; MLC certifies lifecycle transition orchestration only.

---

## E. Dependency/adapter loss, downgrade and recovery — MLC-65…MLC-80

### MLC-65 — Hard dependency deactivated
Dependent module becomes degraded/inert without fataling unrelated platform.

### MLC-66 — Hard dependency removed from filesystem
Missing classes/functions are detected through registry/capability boundary; no fatal include/call chain.

### MLC-67 — Hard dependency downgraded
Version/capability incompatibility surfaces before dependent mutation executes.

### MLC-68 — Hard dependency reinstalled
Dependent module re-evaluates compatibility and resumes only after schema/runtime reconciliation.

### MLC-69 — Soft adapter deactivated
Only adapter-specific functions degrade; base module remains healthy.

### MLC-70 — Builder adapter removed
Mapping/Blueprint definitions persist; missing builder does not delete canonical WPE definitions.

### MLC-71 — Provider adapter removed
Remote IDs/mappings persist as inert configuration; no automatic remote delete/revoke claim.

### MLC-72 — Provider adapter downgraded
Unsupported version enters uncertified/degraded state and cannot rely on private stale APIs.

### MLC-73 — Adapter returns after outage/removal
Reconciliation uses current provider/version/capability profile before side effects resume.

### MLC-74 — Dependency cycle appears
Cycle is detected before recursive enable/disable cascade or infinite boot loop.

### MLC-75 — Hard dependent disable ordering
Intentional dependency disable previews/handles affected dependents deterministically.

### MLC-76 — Soft dependency no cascade
Optional integration disable does not cascade base module or unrelated consumers.

### MLC-77 — Shared dependency ownership
One module cannot uninstall/delete shared service data/library owned by Free platform or another module.

### MLC-78 — Extension collision
Two versions/adapters registering same ID with incompatible ownership cannot make lifecycle state discovery-order dependent.

### MLC-79 — Dependency health cache invalidation
Dependency removal/version change invalidates stale healthy module-state decision.

### MLC-80 — KPA/VER boundary
Registry/version compatibility correctness remains KPA/VER-owned; MLC verifies resulting lifecycle transition only.

---

## F. Migration-required, failure and recovery mode — MLC-81…MLC-96

### MLC-81 — Migration-required boot
Affected module blocks incompatible writes while unrelated modules/admin remain usable where safe.

### MLC-82 — Migration does not retry every request
Known failed/pending destructive migration is not automatically rerun on each page load.

### MLC-83 — Migration failure preserves source data
Failure leaves previous/source data and explicit migration state; no success marker.

### MLC-84 — Partial migration failure
Partially migrated domain enters explicit degraded/recovery state rather than enabled-success.

### MLC-85 — Migration retry authorization
Only authorized deliberate retry/recovery path can restart migration.

### MLC-86 — Migration restore option
Irreversible/high-risk failure references verified recovery/backup class without falsely claiming rollback if unavailable.

### MLC-87 — Recovery UI minimal boot
Recovery surface loads without requiring every optional module editor bundle/consumer to initialize.

### MLC-88 — Recovery can disable problematic optional module
Authorized administrator can isolate failing optional module without editing DB/files manually where architecture permits.

### MLC-89 — Recovery diagnostics
Module health/dependency/schema/migration error visible with correlation ID and safe redaction.

### MLC-90 — Recovery export
Configuration export available where data/schema can be read safely; unknown future schema remains non-lossy/read-only.

### MLC-91 — Recovery cache reset bounded
Recovery may clear/rebuild selected derived module cache, not arbitrary user data.

### MLC-92 — Recovery cannot bypass Policy
Recovery mode is not anonymous/superuser backdoor; capability/reauth requirements remain.

### MLC-93 — Fatal optional module isolation
Optional module bootstrap exception does not necessarily fatal entire wp-admin/site if safe isolation is technically possible.

### MLC-94 — Repeated recovery action idempotency
Retry/disable/cache reset operations tolerate duplicate requests without corrupting state.

### MLC-95 — Recovered module health revalidation
Module returns `enabled` only after dependency/schema/registration invariants pass, not merely because error flag was cleared.

### MLC-96 — VER/domain migration boundary
Exact migrator/DDL/data correctness remains VER/module/CTB evidence; MLC owns safe lifecycle state around it.

---

## G. Plugin uninstall and cleanup levels — MLC-97…MLC-112

### MLC-97 — Default uninstall keep-everything
Without prior explicit destructive policy, uninstall preserves user-authored Definitions/runtime business data.

### MLC-98 — No surprise uninstall-hook deletion
Uninstall hook cannot infer destructive consent from plugin removal click alone.

### MLC-99 — Cleanup level 1 keep everything
Only plugin code disappears; owned retained data remains inventoried/recoverable after reinstall as applicable.

### MLC-100 — Cleanup level 2 transient/generated only
Caches/transients/generated disposable artifacts removed while user-authored Definitions/runtime business data remain.

### MLC-101 — Cleanup level 3 module configuration only
Selected configuration deletion requires dependency preview/export path and does not silently delete target WordPress content/runtime data outside scope.

### MLC-102 — Cleanup level 4 full WPE data
Full cleanup requires explicit high-impact flow, inventory, confirmation/re-auth, recovery guidance and truthful irreversibility.

### MLC-103 — Full cleanup excludes ordinary WP content by default
Posts/users/terms/media are not deleted merely because WPE metadata/relations/templates reference them.

### MLC-104 — Preselected destructive cleanup persistence
If uninstall hook relies on previously stored cleanup choice, choice is explicit, current, scoped and cannot be forged by low-privilege user.

### MLC-105 — Uninstall Free with Pro remnants
Free/shared authoritative data is not deleted in a way that strands active Pro-owned state without explicit whole-product cleanup plan.

### MLC-106 — Uninstall Pro only
Pro uninstall preserves shared Free platform data and retained Pro user configuration according cleanup policy.

### MLC-107 — Uninstall dependency ordering
Shared tables/options/files are deleted only by their owning cleanup plan after dependency/reference inventory.

### MLC-108 — Uninstall job limitation
Ordinary WordPress uninstall request cannot launch unbounded destructive work that depends on plugin code remaining loaded after deletion without safe staged design.

### MLC-109 — Interrupted cleanup
Large cleanup records durable progress/state and can resume/reconcile without double-deleting unrelated data.

### MLC-110 — Cleanup failure truth
Partial cleanup is reported as partial/failed with retained categories; never generic success.

### MLC-111 — Reinstall after default preserve
Compatible reinstall discovers retained data and performs version/schema reconciliation before writes.

### MLC-112 — Reinstall after full cleanup
Platform does not silently resurrect deleted WPE configuration from stale caches/options/jobs.

---

## H. Module-owned data, Definition deletion and dependency-safe cleanup — MLC-113…MLC-128

### MLC-113 — Ownership inventory
Cleanup preview identifies owned tables/options/files/Definitions/runtime rows and shared/external references separately.

### MLC-114 — Incoming dependency inventory
Hard-delete shows incoming dependents and blocks/handles them according owner policy.

### MLC-115 — Outgoing dependency inventory
Cleanup does not delete dependency-owned resources merely because selected module references them.

### MLC-116 — External data ownership
External provider/customer resources are not deleted automatically without explicit provider-specific policy/certification.

### MLC-117 — Draft Definition unused hard-delete
Unused Draft may delete through owner API with expected dependency/audit behavior.

### MLC-118 — Published Definition archive-first
Normal published removal archives/disables before hard-delete where module contract recommends it.

### MLC-119 — Referenced Definition hard-delete
Hard-delete requires dependency impact handling and cannot silently remap dependent object to another UUID/key.

### MLC-120 — Stable UUID non-reuse
Deleted semantic object's stable UUID cannot be silently reused for a new unrelated object.

### MLC-121 — Missing dependency surfacing
Dependent Definition becomes explicit missing/degraded state after authorized deletion; no stale successful compile.

### MLC-122 — Runtime row cleanup respects owner API
Cross-module cleanup asks owning service to delete/anonymize/unlink rather than direct arbitrary table deletion.

### MLC-123 — Mixed-class JSON/data
Cleanup honors field/row ownership/classification and does not assume one blob belongs wholly to caller module.

### MLC-124 — File cleanup path safety
Owned generated/private files are selected through canonical inventory; traversal/symlink/path confusion cannot delete outside allowed roots.

### MLC-125 — Shared media/content reference
WPE config cleanup does not delete media/content still referenced by WordPress/other modules unless explicitly owned/selected.

### MLC-126 — Audit retention boundary
Cleanup does not erase security/audit history contrary to accepted retention; sensitive content is not copied into cleanup logs.

### MLC-127 — Backup/export handoff
Pre-cleanup backup/export is optional/recommended or required per impact class but never falsely marked verified unless BK/export evidence succeeded.

### MLC-128 — DEF/PDL/BK boundary
Definition persistence, privacy erase and backup correctness remain their respective protocols; MLC certifies orchestration/ownership only.

---

## I. Jobs, caches, assets, hooks, Abilities and Events across lifecycle — MLC-129…MLC-144

### MLC-129 — Scheduled job pause on disable
Module-owned recurring work stops admission/execution as policy requires without deleting unrelated JobService rows.

### MLC-130 — In-flight job lifecycle precondition
Worker rechecks module/target/revision state before side-effect commit after module becomes disabled/unavailable.

### MLC-131 — Stale retry after re-enable
Retry from prior lifecycle generation cannot blindly apply to changed Definition/schema/provider state.

### MLC-132 — Lifecycle generation token
Where needed, side-effect/cache/job keys include lifecycle/config generation to reject stale work.

### MLC-133 — Recurring schedule re-enable
Valid schedules resume without duplicated recurrence registrations.

### MLC-134 — Asset registration disable/re-enable
Assets do not remain globally enqueued while module disabled and do not duplicate handles after re-enable.

### MLC-135 — Hook registration duplicate guard
Repeated lifecycle cycles do not multiply callbacks/listeners.

### MLC-136 — Ability catalog lifecycle
Mutation Abilities are unavailable/degraded consistently with module state; cached catalog cannot expose stale invokable path.

### MLC-137 — Event producer lifecycle
Disabled module stops producing new domain events from inactive mutations; historical events remain immutable.

### MLC-138 — Event consumer lifecycle
Re-enabled consumer does not automatically replay entire retained Event Inbox unless explicit checkpoint/replay policy says so.

### MLC-139 — Derived cache invalidation
Lifecycle change invalidates only relevant compiled/render/query/access cache generations and avoids unrelated global flush.

### MLC-140 — Persistent object cache
Module state/descriptor caches across processes/nodes converge after lifecycle transition.

### MLC-141 — Page/object cache protected output
Disabling/expiry/access lifecycle cannot leave previously protected output in public cache without invalidation/revalidation.

### MLC-142 — Asset/build absence after uninstall
Retained data does not cause PHP/markup to reference deleted plugin asset paths as if runtime were healthy; explicit degraded fallback applies.

### MLC-143 — Job/Ability/Event diagnostics
Lifecycle-related skipped/quarantined work is visible through safe machine-readable reason.

### MLC-144 — JS/KPA/CBP boundary
Execution, registry and renderer correctness remain JS/KPA/CBP-owned; MLC covers lifecycle admission/availability only.

---

## J. Security, privacy, access enforcement and Multisite — MLC-145…MLC-160

### MLC-145 — Disable cannot weaken access unexpectedly
Security/access module disable follows fail-safe decommission/last-known enforcement architecture and does not expose protected resource by default.

### MLC-146 — License outage distinct from expiry
Temporary remote/account outage does not become destructive disable/uninstall or immediate loss of accepted local enforcement.

### MLC-147 — Capability enforcement on lifecycle writes
Enable/disable/cleanup/recovery actions require stable capability + resource/site Policy.

### MLC-148 — High-impact reauthentication
Full cleanup/security decommission requires fresh reauthentication where accepted policy requires it.

### MLC-149 — CSRF/replay lifecycle mutation
Nonce/request/idempotency protections block forged/replayed destructive lifecycle actions.

### MLC-150 — Privacy export/erase still available where required
Disabling UI/module does not make legally/product-required personal-data owner cleanup impossible; safe owner handlers/recovery path remain as designed.

### MLC-151 — Module disable is not privacy erase
State change cannot report personal data deleted when it was merely retained/inert.

### MLC-152 — Uninstall cleanup is not remote deletion
Local cleanup result distinguishes remote provider/account/support data not deleted or deletion-requested separately.

### MLC-153 — Backup retention after cleanup
UI/docs do not promise historical backup archives were rewritten/erased by live module cleanup.

### MLC-154 — Multisite per-site module state
Site-scoped enable/disable affects only authorized target site where module policy supports site state.

### MLC-155 — Network-owned module state
Network-scoped state cannot be changed by child Site Admin through forged site coordinate.

### MLC-156 — Network floor vs child override
Network-required security/module floor cannot be weakened by child-site disable preference.

### MLC-157 — Site deletion lifecycle
Deleting a site invokes Site Lifecycle owner orchestration and does not equal global plugin/module uninstall across network.

### MLC-158 — Site clone lifecycle
Clone copies only allowed module intent/config state and re-resolves license/provider/secrets/jobs rather than blindly cloning authority.

### MLC-159 — Cross-site cache/job leakage
Lifecycle change on Site A cannot disable/activate/replay Site B module work through shared cache/job key collision.

### MLC-160 — MSI/LC/PDL boundary
Multisite/site-lifecycle/privacy correctness remains their protocols; MLC certifies module-state interaction with them.

---

## K. Concurrency, scale, observability, recovery and final regression — MLC-161…MLC-176

### MLC-161 — Enable vs disable race
Concurrent opposing commands resolve through version/precondition/last accepted intent policy without split authoritative state.

### MLC-162 — Disable vs cleanup race
Destructive cleanup cannot begin from stale precondition while module was re-enabled or ownership changed.

### MLC-163 — Cleanup vs restore/import race
Concurrent restore/import/cleanup operations serialize or reject unsafe overlap by resource/domain key.

### MLC-164 — Dependency removal during enable
Preflight success followed by dependency disappearance before activation commit is detected/reconciled safely.

### MLC-165 — Stale admin form
Lifecycle mutation submitted from stale module-state version gets conflict/refresh instead of overwriting newer state blindly.

### MLC-166 — Large data cleanup
Large module-owned cleanup is chunked/bounded and reports progress/failures without request timeout-dependent success.

### MLC-167 — Large network state inventory
100/1k/10k-site module health/cleanup preview uses pagination/batching and does not load all child state in one request.

### MLC-168 — No N+1 dependency health
Module status dashboard batches dependency/version/health lookups and avoids per-module/per-site uncontrolled queries/provider calls.

### MLC-169 — Audit correlation
Lifecycle change has actor/site/module/old/new state/correlation/outcome without secrets/private payloads.

### MLC-170 — Error taxonomy integration
Validation/auth/conflict/dependency/migration/internal cleanup failures map to stable ERR semantics and truthful retryability.

### MLC-171 — Partial cleanup report
Counts/categories for deleted/retained/failed/skipped are explicit; partial failure never reported as total success.

### MLC-172 — Crash after destructive chunk
Checkpoint/reconciliation can determine committed work and continue without deleting next/other-owner data twice.

### MLC-173 — Recovery principal invariant
Cleanup/deactivation cannot remove every legitimate administrative recovery path without explicit blocked safeguard.

### MLC-174 — Stop-line data-owner violation
Attempt to delete another module/WordPress-owned data outside approved scope is Critical failure.

### MLC-175 — Stop-line protected-resource exposure
Any lifecycle transition exposing previously protected/private content unexpectedly is Critical failure.

### MLC-176 — Final lifecycle regression matrix
Execute representative enable/disable/re-enable/expiry/dependency-loss/migration-failure/plugin-deactivation/uninstall/cleanup/recovery combinations across declared WP/PHP/Multisite profiles; report only observed support boundaries.

---

## 6. MUST NOT / stop-the-line rules

Future implementation/evidence MUST NOT:

- equate disable with delete;
- equate entitlement expiry with uninstall;
- run destructive migration merely because a module toggle is clicked;
- delete ordinary WordPress posts/users/terms/media simply because WPE metadata references them;
- let hard-dependency loss fatal unrelated platform where safe degradation exists;
- keep privileged mutation Abilities/jobs active after lifecycle state says they are unavailable;
- silently replay stale jobs on re-enable;
- let uninstall-hook execution stand in for prior explicit destructive cleanup consent;
- let one module delete shared/other-owner data directly;
- report local cleanup as remote/provider deletion;
- report live cleanup as backup erasure;
- weaken protected-resource enforcement accidentally through disable/license state;
- use current blog context as durable lifecycle ownership;
- promote MLC completion to KPA/FP/VER/PDL/DEF/JS/BK/MBR/MSI/LC/provider certification.

Stop the line on:
- unexpected protected-content exposure;
- unauthorized lifecycle or cleanup action;
- user data loss outside selected owned scope;
- cross-site/network lifecycle contamination;
- silent destructive uninstall;
- stale job/provider side effect after disable/delete when precondition should block it;
- partial cleanup reported as success;
- loss of all legitimate recovery principals;
- secret/private payload leakage in lifecycle/audit/error reports.

---

## 7. Required future evidence report

Every applicable fixture records:

- MLC fixture ID/name;
- exact Free/Pro/Platform/module/dependency/schema versions;
- WordPress/PHP/DB/Multisite profile;
- module/site/network scope;
- canonical old/new state and diagnostic reason;
- preconditions and actor/capability;
- registrations/jobs/assets/caches/provider/dependency state affected;
- data categories preserved/deleted/retained;
- domain protocol references (FP/VER/KPA/PDL/DEF/JS/BK/MBR/MSI/LC/etc.);
- observed result/evidence artifact refs;
- Pass/Fail/Blocked;
- security/privacy/recovery observations;
- known risk/deviation;
- retest state.

Overall report states independently:
- MLC certification classes passed/not verified;
- modules/profiles actually tested;
- cleanup levels actually verified;
- security-sensitive lifecycle profiles actually verified;
- Multisite scopes actually verified;
- unresolved risks and next safe action.

---

## 8. Current truth

- MLC fixtures documented: **176**.
- MLC fixtures executed: **0/176**.
- Module lifecycle runtime certifications: **0**.
- No module toggle, plugin activation/deactivation/uninstall, migration, cleanup, file deletion, DB mutation, Job execution, provider call, cache flush or Multisite lifecycle operation was executed by writing this protocol.

## Development-consent gate

**Do not execute any module/plugin enable/disable/reactivation/deactivation/uninstall hook, cleanup, migration, data/file deletion, queue/cache/provider action or recovery fixture until explicit owner consent under ADR-0014 and `/DEVELOPMENT-CONSENT.md`.**
