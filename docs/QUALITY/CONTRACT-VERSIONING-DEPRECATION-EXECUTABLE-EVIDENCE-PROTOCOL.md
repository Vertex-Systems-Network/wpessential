# WPEssential — Contract Versioning & Deprecation Executable Evidence Protocol

Status: **Phase 0 evidence specification / NOT AUTHORIZED FOR EXECUTION**  
Date: 2026-08-28  
Work package: `P0-M00-WP30`  
Related: ADR-0010, ADR-0014, ADR-0049, ADR-0059, ADR-0069, ADR-0092, ADR-0095, ADR-0128, ADR-0132, ADR-0143, ADR-0146, `docs/ARCHITECTURE/CONTRACT-VERSIONING-AND-DEPRECATION.md`, Free↔Pro, Definition Repository, Migration plans, Abilities, Events, Extension SDK, Import/Export, Component Blueprint, module manifests, Multisite.

## 1. Purpose

This document freezes the shared future executable-evidence contract for **cross-version compatibility, migration, deprecation and removal semantics** across WPEssential.

The protocol freezes **VER-01…VER-176**.

**Executed: 0/176.**

Nothing here certifies a specific Free↔Pro pair, Definition migration, database migration, adapter, package importer, Ability, Event, Component Blueprint or module runtime. Those domains keep their own protocols. This protocol proves only the shared versioning/deprecation invariants that span them.

No plugin downgrade/upgrade, package install, database migration, schema mutation, stored-definition rewrite, WordPress runtime, CLI command, browser flow or fixture execution is authorized by this document.

---

## 2. Version families kept separate

WPEssential must not collapse all compatibility into one product version number.

Independent version families remain:

- Product Version;
- Platform API Version;
- Module Version;
- Definition Schema Version;
- Runtime Data Schema / domain migration version;
- Ability contract/version;
- Event schema version;
- Integration/adapter API version;
- Extension SDK version;
- package format version;
- provider/source adapter version where relevant;
- compiler/descriptor version where a compiled runtime artifact exists.

A higher Product Version does not prove every inner contract is newer or compatible. A matching Product Version does not prove Platform API, data schema, Ability/Event or adapter compatibility.

---

## 3. Truth boundaries

The following remain distinct:

`Product version ≠ Platform API version ≠ module version ≠ Definition schema version ≠ runtime DB migration version ≠ Ability/Event schema version ≠ adapter/SDK compatibility range ≠ package format version ≠ certified cross-version behavior`

Also:
- successful boot ≠ successful migration;
- successful migration ≠ safe downgrade;
- a deprecated contract still working ≠ compatibility guaranteed forever;
- deprecation warning ≠ removal authorization;
- migration code existing ≠ migration evidence;
- unknown future schema ≠ old schema;
- package parse success ≠ semantic import compatibility;
- a compatibility shim ≠ permission to weaken authorization/security semantics;
- restored old data ≠ safe execution under newer runtime until reconciliation/migration completes.

---

## 4. Evidence ownership / anti-duplication rule

This protocol does **not** replace:
- `FP-*` for Free↔Pro package/API/entitlement boot behavior;
- `DEF-*` for Definition Repository persistence/revision mechanics;
- module-specific schema/migration evidence;
- `IM-*` for package inspection/conflict/import execution;
- `KPA-*` for registry/Policy/Ability/Event invocation semantics;
- `CBP-*` for Component Blueprint compiler/renderer runtime;
- `CI-*` for release-pipeline enforcement.

Where a VER fixture depends on one of those protocols, the final report must reference the domain evidence rather than duplicate it.

---

## 5. Certification classes

Certify independently:

- `VER-I` — version identity/catalog/manifest truth;
- `VER-P` — Platform API / Free↔Pro/module compatibility ranges;
- `VER-D` — Definition schema evolution/migrator chains;
- `VER-R` — runtime-data/database migration state and recovery;
- `VER-A` — Ability contract evolution;
- `VER-E` — Event schema evolution/replay;
- `VER-X` — extension/adapter/SDK/module dependency compatibility;
- `VER-G` — package/import/export compatibility boundary;
- `VER-L` — deprecation/removal lifecycle;
- `VER-O` — release/rollback/Multisite/operational version coordination;
- `VER-S` — security, observability and performance of version transitions.

Passing one class never implies another.

---

# 6. Fixed executable fixture matrix

## A. Version identity, manifests and compatibility catalog — VER-01…VER-16

### VER-01 — Product version identity
Runtime reports exact Free/Pro product versions without treating them as Platform API or schema versions.

### VER-02 — Platform API identity
Shared kernel exposes one explicit Platform API version/range independent from product marketing version.

### VER-03 — Module version identity
Each module manifest exposes module version independently from enable/disable state.

### VER-04 — Definition schema identity
Persisted and compiled Definition artifacts carry exact owning type/schema version.

### VER-05 — Runtime-domain migration identity
Each WPE-owned runtime domain records migration/schema version independently from global product version.

### VER-06 — Ability version identity
Ability catalog reports stable ID plus contract/schema/version semantics required by consumers.

### VER-07 — Event schema identity
Event envelope exposes immutable event type plus schema version used by consumers/replay.

### VER-08 — SDK/adapter identity
Extension/adapter registration declares supported SDK/Platform/API range rather than only installed plugin version.

### VER-09 — Package format identity
Portable package declares package format version separately from WPE Product and inner Definition schema versions.

### VER-10 — Compiled descriptor identity
Compiled runtime descriptors record compiler/descriptor version where compatibility depends on generated representation.

### VER-11 — No implicit version inference
Remove one explicit inner version from fixture; runtime does not infer it solely from Product Version when semantics require independent truth.

### VER-12 — Version metadata consistency
Manifest/runtime/catalog copies of the same version family agree or enter explicit mismatch/degraded diagnostic state.

### VER-13 — Unknown version metadata
Unknown/malformed version token fails safely instead of lexical/string comparison that incorrectly promotes compatibility.

### VER-14 — Pre-release version handling
Alpha/beta/RC/nightly identifiers follow defined comparison/profile rules and are never silently promoted into stable support.

### VER-15 — Environment/report capture
Evidence report records exact WordPress/PHP/DB/Free/Pro/Platform/module/schema/adapter versions used.

### VER-16 — Version catalog performance
Reading version/compatibility metadata does not require scanning every Definition/runtime row on normal request boot.

---

## B. Platform API, Free↔Pro and module version skew — VER-17…VER-32

### VER-17 — Compatible Platform range
Pro/module declaring supported Platform API range boots through the shared kernel when current Platform API is inside range.

### VER-18 — Free too old
Consumer requiring newer Platform API enters explicit degraded/block state without fataling unrelated Free functionality.

### VER-19 — Free too new / unsupported
Older Pro/module against unsupported newer Platform API fails safely; no blind call into changed interfaces.

### VER-20 — Missing Free kernel
Pro/shared consumer detects missing authoritative kernel and does not instantiate duplicate private registry/runtime as fallback.

### VER-21 — Module dependency compatible range
Module dependency satisfied within declared semantic range registers normally.

### VER-22 — Module dependency too old
Dependent module stays degraded/blocked with actionable machine-readable reason and preserved data.

### VER-23 — Module dependency too new
Unsupported newer dependency is not assumed compatible merely because symbol/class exists.

### VER-24 — Optional dependency absent
Optional integration enters explicit capability/degraded state without poisoning owning module boot.

### VER-25 — Dependency cycle after version change
Version/manifest change introducing dependency cycle is detected deterministically before partial module activation.

### VER-26 — Mixed Free/Pro rollback
Rollback one package while the other remains newer; boot state follows declared compatibility range and never silently rewrites data to force compatibility.

### VER-27 — Stale compatibility cache
Upgrade version metadata while cache is stale; registry invalidates/refuses stale compatibility decision.

### VER-28 — Concurrent package transition observation
Two requests during package/version transition do not construct contradictory authoritative registries from mixed old/new manifests.

### VER-29 — Feature detection over version sniffing
Consumer uses declared capability/feature detection where supported rather than version-only assumptions.

### VER-30 — Unsupported feature flag on compatible version
Platform version is compatible but required capability unavailable; consumer degrades explicitly instead of relying only on version number.

### VER-31 — Deactivated dependency with stored data
Dependency deactivation preserves owned/shared data and causes explicit degraded runtime rather than destructive cleanup.

### VER-32 — FP boundary reference
Free↔Pro artifact-pair certification remains owned by FP protocol; VER report references FP evidence and does not promote itself to FP-certified.

---

## C. Definition schema evolution and migrator chains — VER-33…VER-48

### VER-33 — Current Definition schema
Current schema loads/validates without migration and preserves revision identity.

### VER-34 — Single-step migration
Schema N→N+1 migrator produces deterministic supported in-memory representation.

### VER-35 — Multi-step migration chain
N→N+1→N+2 chain executes in strict registered order with no skipped semantic step.

### VER-36 — Missing migrator gap
If N→N+1 migrator missing, runtime stops with explicit unsupported/migration-required state rather than jumping to later migrator.

### VER-37 — Migrator determinism
Same source revision + migration profile yields semantically identical output/fingerprint across repeated runs.

### VER-38 — Migrator idempotency
Re-evaluating already migrated in-memory form does not double-apply destructive/default transformations.

### VER-39 — Historical revision immutability
Loading old Definition under newer runtime does not silently overwrite historical persisted revision.

### VER-40 — Explicit upgraded revision persistence
When product chooses to persist upgrade, it creates new revision/auditable transition rather than mutating prior history.

### VER-41 — Unknown future Definition schema
Older runtime enters inspect/read-only/unsupported state; it does not drop unknown fields and save lossy downgrade.

### VER-42 — Additive optional field
Compatible additive optional field is preserved/ignored safely according schema rules without breaking older compatible consumer.

### VER-43 — Removed/renamed required field
Breaking field change requires explicit schema version/migrator; no alias magic that obscures semantic break.

### VER-44 — Security-semantic change
Authorization/privacy meaning change is treated as breaking even if JSON shape is unchanged.

### VER-45 — Definition dependency version skew
Parent Definition on newer schema referencing older dependent Definition resolves only through supported compatibility/migration path.

### VER-46 — Published compiled descriptor stale
Definition schema/compiler upgrade invalidates or explicitly recompiles stale descriptor before execution.

### VER-47 — Downgrade with newer Definition
Older code seeing newer Definition does not save/normalize away unsupported fields.

### VER-48 — DEF boundary reference
Definition storage/revision/locking persistence evidence remains `DEF-*`; VER records only cross-version migration semantics.

---

## D. Runtime data schema / database migrations / recovery — VER-49…VER-64

### VER-49 — Per-domain migration version
Membership/Workflow/Chat/etc. runtime domains track their own migration state rather than one global db_version.

### VER-50 — Ordered forward migration
Domain migration N→N+1 runs only after required predecessor state and records completion atomically enough for crash recovery.

### VER-51 — Resumable chunked migration
Large migration can resume from durable checkpoint without reprocessing committed chunk incorrectly.

### VER-52 — Crash before commit
Worker crash before chunk commit leaves retryable previous state, not false migrated marker.

### VER-53 — Crash after data write before state marker
Ambiguous crash window is detected/reconciled idempotently instead of duplicating transformation.

### VER-54 — Migration failure state
Failure records explicit failed/degraded state and prevents incompatible new code from blindly operating on partial schema.

### VER-55 — Read compatibility during expansion
Expand phase allows old/new code only where the documented compatibility window proves shared read semantics.

### VER-56 — Dual-write/backfill window
If migration requires dual-write, source-of-truth and reconciliation rules are explicit and duplicate/out-of-order writes remain safe.

### VER-57 — Contract phase after verification
Old column/index/shape removal occurs only after backfill/verification and rollback window rules allow it.

### VER-58 — Irreversible migration classification
Irreversible/data-loss-risk migration declares recovery class and verified backup/restore boundary before execution.

### VER-59 — Unsupported downgrade
Older code refuses incompatible newer runtime schema unless an explicit downgrade migration exists.

### VER-60 — Restore older backup into newer code
Restore completes only into reconciliation/migration-required state until domain migrations and privacy/access reconciliations finish.

### VER-61 — Restore newer backup into older code
Older code blocks unsupported newer schema rather than destructive best-effort downgrade.

### VER-62 — Multisite mixed site migration states
Network can identify Site A migrated / Site B pending / Site C failed without current-blog ambiguity or global false completion.

### VER-63 — Network-owned schema coordination
Network-scoped table/domain migration serializes/coordinates once and does not run independently per child site accidentally.

### VER-64 — Domain protocol boundary
Exact DDL/data transformation correctness remains owned by module/CTB/etc. protocols; VER certifies orchestration/version state only.

---

## E. Ability contract evolution — VER-65…VER-80

### VER-65 — Add optional Ability input
Optional additive input with safe default remains backward compatible for old callers.

### VER-66 — Add optional Ability output
Old consumer ignores unknown optional output field where contract allows.

### VER-67 — Add required Ability input
Required input change uses new versioned contract or explicit transition; existing callers cannot silently break.

### VER-68 — Remove/rename Ability field
Breaking input/output field removal requires version/deprecation path.

### VER-69 — Permission strengthening
More restrictive permission may be security-required but is surfaced as contract/security change with migration/UX impact.

### VER-70 — Permission weakening
Reduced authorization requirement is always treated as security-sensitive breaking change even if schemas are identical.

### VER-71 — Side-effect change
Read→write, write scope expansion or destructive semantics require versioned contract/impact review.

### VER-72 — Idempotency semantic change
Changing idempotency/retry guarantee is breaking to Workflow/REST/AI/CLI consumers and cannot be hidden behind same contract.

### VER-73 — Sync→async change
Synchronous result changing to queued/asynchronous execution requires explicit contract version/transition.

### VER-74 — Error contract evolution
New stable error code can be additive; changing category/retry semantics for existing code is reviewed as compatibility change.

### VER-75 — Deprecated Ability listing
Catalog marks deprecated contract and replacement without deleting discoverability needed by existing consumers.

### VER-76 — Compatibility-only Ability
Old Ability remains invokable for existing authorized definitions while new creation UI/catalog defaults to replacement.

### VER-77 — Removed Ability with stored Workflow
Stored Workflow referencing removed Ability enters explicit migration/degraded state rather than silently substituting different action.

### VER-78 — AI catalog preference
AI-facing catalog prefers active replacement while still explaining deprecated stored contract; AI cannot silently rewrite/publish.

### VER-79 — Cross-channel same version
UI/REST/CLI/Workflow/AI invoke the same selected Ability contract version and cannot route to privilege-weaker legacy variant.

### VER-80 — KPA boundary reference
Ability registration/authorization execution correctness remains KPA-owned; VER covers contract evolution only.

---

## F. Event schema evolution and historical replay — VER-81…VER-96

### VER-81 — Add optional Event field
Consumer accepting current schema tolerates unknown additive optional field where declared compatible.

### VER-82 — Remove/rename Event field
Breaking removal uses new event schema version; consumer does not reinterpret old field meaning.

### VER-83 — Change field meaning
Semantic change requires new schema even if type/name remains identical.

### VER-84 — Historical replay old schema
Replayed old event retains original schema version and passes through registered historical decoder/migrator if supported.

### VER-85 — Unknown future Event schema
Old consumer rejects/quarantines unsupported future event rather than partially consuming guessed fields.

### VER-86 — Event type stable identity
Schema version evolution does not create duplicate logical event IDs or mutate original event identity.

### VER-87 — Privacy classification evolution
Newly sensitive field gets correct redaction/export/log policy; old replay path cannot expose it under stale classification.

### VER-88 — Authorization context evolution
Event payload never becomes authority solely because older schema carried broader data; current consumer Policy still applies.

### VER-89 — Consumer version lag
Producer emits schema supported by declared consumer compatibility contract or routes through explicit compatibility/degraded path.

### VER-90 — Mixed consumer fleet
Two consumer versions process same event safely according their declared schema support without one mutating shared event body.

### VER-91 — Event deprecation warning
Deprecated event type remains identifiable for consumers and observability without silently remapping to replacement.

### VER-92 — Removed event producer
Producer stops emitting removed type only after dependent consumers/definitions are migrated or explicit breaking release policy permits.

### VER-93 — Duplicate/retry across schema upgrade
At-least-once duplicate delivered before/after upgrade retains event identity/dedupe semantics across decoder versions.

### VER-94 — Event persistence migration
If stored Event Inbox representation changes, historical source payload/schema provenance remains auditable.

### VER-95 — Workflow wait/event binding version
Stored Workflow waiting on old event schema either continues through supported compatibility or enters explicit migration state.

### VER-96 — KPA/WC boundary reference
Event transport, signature and consumer execution remain KPA/WC-owned; VER covers schema evolution/replay compatibility.

---

## G. Extension SDK, adapters, module dependencies and public interfaces — VER-97…VER-112

### VER-97 — SDK supported range
Extension declares compatible SDK/Platform range and registers when current runtime is inside it.

### VER-98 — SDK too old
Runtime refuses unsupported ancient extension contract safely without fataling platform.

### VER-99 — SDK too new
Older runtime recognizes incompatible future extension and degrades rather than guessing public interface compatibility.

### VER-100 — Feature detection
Extension can query supported feature/capability without reaching into internal classes/version sniff only.

### VER-101 — Public vs internal class boundary
Internal class change does not break compliant extension because public wrapper/interface remains stable.

### VER-102 — Leaked internal type detection
If public API accidentally exposes internal/vendor type, compatibility test flags it before release.

### VER-103 — Adapter API additive change
New optional adapter capability does not force every old adapter to implement it; capability negotiation remains explicit.

### VER-104 — Adapter breaking change
Required method/semantic change increments adapter API/version and marks old adapter incompatible/deprecated.

### VER-105 — Provider API version dimension
Provider integration certification remains scoped to explicit provider/API version rather than extension version alone.

### VER-106 — Module dependency version pin/range
Consumer cannot use unbounded `>=` style compatibility when known major breaking changes exist without upper/tested range policy.

### VER-107 — Extension namespace rename
Public namespace/ID rename has alias/migration/deprecation plan and collision analysis.

### VER-108 — Extension ID collision across versions
Two extension versions cannot register same public IDs with inconsistent semantics based on discovery order.

### VER-109 — Extension downgrade with stored config
Newer extension config remains intact/read-only/degraded when old adapter cannot understand it; no lossy save.

### VER-110 — Extension uninstall/reinstall across versions
Uninstall/disable does not silently delete shared user configuration needed for later compatible reinstall.

### VER-111 — Deprecation notice quality
Developer-facing notice names contract, current status, replacement and compatibility/removal boundary without secret/private data.

### VER-112 — KPA/provider boundary
Registry/failure isolation/provider behavior remains domain-owned; VER covers public compatibility range/evolution only.

---

## H. Package format / import-export compatibility — VER-113…VER-128

### VER-113 — Current package format
Importer recognizes supported current package format and reads inner version manifest before writes.

### VER-114 — Older supported package
Older format migrates through explicit format adapter/migrator before semantic import plan.

### VER-115 — Unknown future package format
Importer allows safe inspect/report where possible but performs no destructive import by guessing unknown format.

### VER-116 — Package format vs Definition schema
Current package can contain older Definition schemas; importer evaluates each independent version family correctly.

### VER-117 — Missing module version requirement
Package dependency requirement is surfaced before import; unavailable module does not cause silent object loss.

### VER-118 — Pro object on Free-only target
Package remains inspectable/deferred according IM policy; versioning layer does not fake Pro capability.

### VER-119 — Same UUID older schema
Conflict analysis runs after supported migration normalization and does not compare incompatible raw payloads as equivalent.

### VER-120 — Same UUID future schema
Future unknown object cannot overwrite current target Definition via lossy downgrade.

### VER-121 — Clone/new identity across schema versions
Reference rewrite occurs after schema migration/normalization so identity mapping does not corrupt versioned dependency keys.

### VER-122 — Deferred object later module upgrade
Previously deferred object can be re-evaluated when required module/version becomes available without reparsing as trusted executable code.

### VER-123 — Package checksum/signature and migration
Content integrity/authenticity verification covers original package bytes; migration output has separate derived evidence/fingerprint.

### VER-124 — Package source Platform API newer
Target reports incompatible/degraded requirements and does not infer compatibility from matching module names.

### VER-125 — Package source Platform API older
Supported definitions migrate independently; old source Platform API is context, not automatic rejection.

### VER-126 — Imported deprecated contract
Importer flags deprecated Ability/Event/Definition feature and offers migration/deferred behavior rather than silently substituting.

### VER-127 — Re-export after migration
Exported new package declares target/current schema versions while preserving relevant origin/migration metadata without leaking site secrets.

### VER-128 — IM boundary reference
Archive parsing, conflict resolution, write/rollback behavior remains `IM-*`; VER covers version interpretation only.

---

## I. Deprecation lifecycle, compatibility-only state and removal — VER-129…VER-144

### VER-129 — Active contract
Active contract is discoverable/recommended and has no deprecation warning.

### VER-130 — Enter Deprecated
Contract still works but developer/admin diagnostics identify replacement and migration path.

### VER-131 — Deprecated does not auto-migrate silently
Existing stored definitions are not rewritten/published solely because contract becomes deprecated.

### VER-132 — New creation preference
Normal UI/AI/catalog defaults stop recommending deprecated contract when replacement exists.

### VER-133 — Compatibility-only state
Existing stored references continue where safe; new creation is hidden/blocked by default.

### VER-134 — Removal eligibility preconditions
Removal requires documented window/release policy, migration path, dependency inventory and impact evidence.

### VER-135 — Removal with unresolved dependency
Contract cannot be removed as routine cleanup while active stored definitions/extensions still require it unless release explicitly accepts break and exposes migration/degraded path.

### VER-136 — Removed contract failure mode
Runtime returns stable unsupported/migration-required state; no undefined method/fatal or silent substitution.

### VER-137 — Accelerated security deprecation
Critical security issue may shorten normal deprecation window but still records rationale, safe replacement/disable path and user-impact/recovery guidance.

### VER-138 — Capability deprecation
Split/rename/removal of capability applies least-privilege migration; old broad grant cannot silently map to multiple sensitive new grants.

### VER-139 — Event deprecation
Producer/consumer transition preserves replay truth and does not rewrite historical event schema.

### VER-140 — Ability deprecation
Workflow/REST/AI catalogs surface replacement while old authorized consumer remains explicit until removed.

### VER-141 — Adapter deprecation
Deprecated builder/provider adapter is not advertised as current certified support after supported range ends.

### VER-142 — Definition field deprecation
Deprecated field is preserved in stored historical revision and migrated only through explicit schema evolution.

### VER-143 — Deprecation telemetry privacy
If usage evidence is ever used to judge removal, it must come from privacy-approved telemetry/support evidence; hidden telemetry is prohibited.

### VER-144 — Documentation parity
Docs/changelog/release compatibility report match actual deprecation/removal state for tested release.

---

## J. Release transitions, rollback and Multisite mixed states — VER-145…VER-160

### VER-145 — Upgrade from immediately prior supported release
Public contract/version inventory before/after matches declared release compatibility report.

### VER-146 — Upgrade across multiple supported releases
Required migrator/deprecation chain remains complete when skipping intermediate product versions.

### VER-147 — Unsupported large version jump
Runtime blocks or requires staged migration when chain not supported; no best-effort silent execution.

### VER-148 — Rollback before schema change
Code rollback without incompatible data migration follows declared supported path.

### VER-149 — Rollback after irreversible schema change
System blocks unsafe downgrade or requires restore/forward-fix per recovery class.

### VER-150 — Release artifact/version manifest mismatch
Package metadata and embedded runtime manifest mismatch blocks production-ready claim.

### VER-151 — Partial deployment / mixed files
Mixed old/new code file state is detected/degraded where feasible and never treated as clean completed upgrade.

### VER-152 — Concurrent web requests during migration
Requests do not execute new incompatible write semantics before migration gate allows them.

### VER-153 — Background Job during upgrade
Queued Job carrying old payload/schema is decoded through supported version contract or explicitly quarantined/reconciled.

### VER-154 — Workflow run spanning upgrade
Pinned Workflow/Step/Ability/Event versions remain stable or enter explicit upgrade boundary; in-flight run is not silently reinterpreted.

### VER-155 — Multisite staged child-site migration
Network records per-site progress and isolates failed child site without falsely marking entire network migrated.

### VER-156 — Multisite network-owned migration first
Network-scoped shared storage follows coordinator ordering before child-site consumers use new schema.

### VER-157 — New child site created during migration window
Provisioning uses a consistent target schema/version profile and does not create hybrid old/new state.

### VER-158 — Site clone during version skew
Clone records source version/migration state and requires reconciliation; it cannot bypass pending migration by copying markers blindly.

### VER-159 — Backup/restore during version transition
Backup manifest records relevant WPE/domain versions; restore path evaluates compatibility before execution.

### VER-160 — Release compatibility report
Every public-contract-affecting release outputs Added/Deprecated/Breaking/migrations/Free-Pro/SDK/Ability/Event/rollback notes based on actual manifest diff/evidence.

---

## K. Security, observability, performance and stop-the-line behavior — VER-161…VER-176

### VER-161 — Version comparison injection
Malformed manifest/version strings cannot become shell/SQL/path/code execution through comparison/migration selection.

### VER-162 — Unauthorized migration trigger
Low-privilege caller cannot invoke privileged schema migration/deprecation-removal operation by forging version state.

### VER-163 — Migration CSRF/replay
Admin-triggered migration/upgrade acknowledgement uses appropriate nonce/capability/idempotency protections where manual surface exists.

### VER-164 — Security downgrade prevention
Rollback cannot silently re-enable known-vulnerable deprecated contract where release/security policy explicitly blocks it.

### VER-165 — Secret-safe migration logs
Version/migration/deprecation logs contain IDs/state/timings/errors, not Vault secrets/private payload bodies.

### VER-166 — Correlation and audit
Migration/deprecation/removal actions emit safe Audit/diagnostic correlation without duplicating domain data.

### VER-167 — Unknown-version fail-safe UX
Admin sees actionable current/required/supported range and next safe action without raw stack/internal secret exposure.

### VER-168 — Large manifest catalog
Thousands of modules/definitions/adapters/version records remain bounded and avoid O(N²) dependency/version comparison on ordinary boot.

### VER-169 — Large Multisite migration inventory
100/1k/10k-site planning/coordination uses pagination/chunking and does not load every site/domain migration detail into one request.

### VER-170 — Migration lock contention
Concurrent migrators use explicit lease/lock/state semantics and do not corrupt version markers under contention.

### VER-171 — Stale worker after migration lease expiry
Old worker cannot commit incompatible transformation after ownership/lease transferred without precondition check.

### VER-172 — Tampered migration marker
Marker says migrated but schema/data invariant fails; system detects mismatch and stops rather than trusting marker alone.

### VER-173 — Tampered package/manifest version metadata
Version declaration cannot override actual signed/checksummed/package contents or domain schema validation.

### VER-174 — Stop-the-line on authorization weakening
Any version transition that unexpectedly broadens effective authorization blocks release/migration evidence immediately.

### VER-175 — Stop-the-line on lossy unknown schema
Any fixture that silently drops unknown future fields/content and persists result is Critical failure.

### VER-176 — Final cross-version regression matrix
Run representative supported upgrade/downgrade/deprecation/migration combinations across declared WordPress/PHP/Multisite profiles; record exact supported/unsupported boundaries and do not generalize beyond executed evidence.

---

## 7. MUST NOT / stop-the-line rules

Future implementation/evidence MUST NOT:
- treat Product Version as universal schema/API compatibility truth;
- jump a missing migrator step;
- silently mutate historical immutable Definition revisions;
- save unknown future schema after dropping unrecognized fields;
- let compatibility shims weaken current authorization/Policy requirements;
- map removed broad capabilities into multiple sensitive grants automatically;
- claim downgrade support merely because plugin files can be copied backward;
- mark network migration complete when child/network domains are partially failed;
- use hidden telemetry to justify deprecation/removal;
- promote VER completion to FP/DEF/IM/KPA/CBP/module certification automatically.

Stop the line on:
- data loss/corruption;
- unauthorized privilege expansion;
- unknown-future-schema lossy persistence;
- migration marker/schema mismatch;
- rollback that reactivates explicitly blocked vulnerable contract;
- cross-site migration/scope contamination;
- secrets/private content in migration/version logs;
- unresolved partial migration reported as success.

---

## 8. Required future evidence report

For every applicable fixture record:
- fixture ID/name;
- source and target versions for every relevant version family;
- exact environment (WP/PHP/DB/Multisite/Free/Pro/modules/adapters);
- pre-state;
- operation/transition;
- expected compatibility/deprecation state;
- observed state;
- domain protocol references (FP/DEF/KPA/IM/CBP/etc.);
- persistence/migration markers;
- rollback/recovery class where applicable;
- security/privacy observations;
- evidence artifact refs;
- Pass/Fail/Blocked;
- known risk/deviation;
- retest result.

Overall report must state independently:
- which Product/Platform/module/schema/Ability/Event/SDK/package version ranges are **Verified**;
- which are **Not Verified**;
- which are explicitly **Unsupported**;
- which are **Deprecated / Compatibility-only / Removal eligible / Removed**;
- exact next safe action.

---

## 9. Current truth

- VER fixtures documented: **176**.
- VER fixtures executed: **0/176**.
- Cross-version runtime certifications: **0**.
- No upgrade/downgrade/migration/deprecation/removal runtime has been executed by creating this protocol.
- FP/DEF/KPA/IM/CBP and module-specific execution counters remain unchanged.

## Development-consent gate

**Do not execute any package switch, upgrade/downgrade, WordPress runtime, Definition migration, database migration, schema write, stored-object rewrite, CLI migration, import, benchmark or release fixture until explicit owner consent under ADR-0014 and `/DEVELOPMENT-CONSENT.md`.**
