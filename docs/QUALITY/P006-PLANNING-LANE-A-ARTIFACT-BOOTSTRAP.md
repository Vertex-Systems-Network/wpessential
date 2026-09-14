# WPEssential — P-006 Planning Lane A: Artifact, Package, Bootstrap and Preflight Evidence Map

Status: **PLANNING / EVIDENCE-DESIGN ONLY — EXECUTION NOT AUTHORIZED**  
Parent gate: **Issue #924**  
Worker lane: **Issue #925**  
Coordination gate: **Issue #930 / merged PR #931**  
Planning anchor: **`main @ 00a685442d961f0604467d7a1039de12638a06cf`**  
Fixture scope: **FP-01…FP-44**  
P-006 truth at this planning anchor: **144 documented / 0 executed / 0 passed / 0 failed / 0 certified pairs / 0 runtime certifications**

## 1. Purpose and hard boundary

This document converts P-006 fixtures FP-01…FP-44 into an executable-ready evidence plan without executing them and without changing runtime behavior.

It does **not** authorize or perform:

- Free/Pro source, bootstrap, header, package, Composer/npm or CI behavior changes;
- plugin install/activation/deactivation;
- WordPress requests for P-006 evidence;
- candidate package builds for P-006 certification;
- database/schema/migration mutation;
- entitlement, Membership, allocation, licensing/provider or updater execution;
- certification-counter changes;
- ADR-0010 acceptance;
- deployment or release.

Executable P-006 remains blocked until the prerequisites in `docs/QUALITY/P006-FREE-PRO-COMPATIBILITY-EXECUTABLE-EVIDENCE-PROTOCOL.md` are satisfied, including explicit P-006-scoped ADR-0014 owner consent.

## 2. Truth domains kept separate

Lane A plans evidence only for local artifact/package/bootstrap compatibility. It must not manufacture truth in adjacent domains.

| Truth domain | Lane A treatment |
| --- | --- |
| Package/artifact identity | In scope for FP-01…FP-12. |
| Binary/load-order compatibility | In scope for FP-13…FP-44. |
| Platform API compatibility | In scope only as local preflight input/result; deeper range/deprecation execution is Lane B. |
| Schema compatibility | Observe declared generations only; migration execution is Lane C. |
| Product entitlement | Never used to make a compatibility decision. Lane D owns entitlement execution planning. |
| Membership authorization | Never inferred from product compatibility. Lane D owns its separation evidence. |
| Remote account/allocation | Out of scope; Lane D. |
| Updater/package trust | Artifact identity may be recorded here; updater/TUF trust is a separate gate. |
| Runtime/product certification | Not promoted by this planning document. |

## 3. Existing non-certifying evidence inventory

The following repository evidence is useful input but is **not** P-006 certification evidence:

1. `wpessential.php`
   - Free plugin header declares version `0.1.0-dev`, WordPress `6.9`, PHP `8.2`.
   - publishes `WPE_VERSION`, `WPE_PLATFORM_API_VERSION=0.1.0`, `WPE_PLATFORM_SCHEMA_GENERATION=1` before normal runtime boot;
   - publishes `WPE_FREE_BOOTSTRAP_READY` only after the Free Composer autoloader resolves;
   - registers ordinary Free boot on `plugins_loaded` priority `-100`.
2. `wpessential-pro.php`
   - Pro plugin header declares version `0.1.0-dev`, WordPress `6.9`, PHP `8.2`, and `Requires Plugins: wpessential`;
   - publishes bounded supported Free version, Platform API and platform-schema ranges plus Pro schema generation;
   - exposes only `WPEssential\Modules\Compatibility\*` through the Pro autoloader before canonical compatibility PASS;
   - evaluates local compatibility before resolving required Free Platform runtime classes or registering premium modules;
   - keeps compatibility in request-local `$GLOBALS['wpe_pro_compatibility_result']`; constants are observability mirrors only.
3. `frameworks/Modules/Compatibility/LocalCompatibilityPreflight.php`
   - local-only strict evaluator;
   - no network, entitlement or Membership dependency;
   - fails closed for missing Free, incomplete bootstrap/package, malformed or contradictory Pro metadata, missing/malformed/too-old/too-new Free version, Platform API and platform schema;
   - only `compatible` enables `premium_boot_allowed` and compatibility-layer `premium_migrations_allowed`.
4. `tests/Unit/Modules/Compatibility/LocalCompatibilityPreflightTest.php`
   - exact-boundary compatible case;
   - fail-closed table covering Free missing/incomplete, Pro package incomplete, contradictory ranges, missing/malformed/too-old/too-new Free version/API/schema.
5. `tools/release/build-free-pro-distributables.php`
   - deterministic normalized Free and Pro ZIP construction;
   - SHA-256 artifact identity and JSON package manifests;
   - Free/Pro physical source and asset boundary validation;
   - project metadata validation for current header/readme baseline.
6. `.github/workflows/distributable-package.yml`
   - repeated byte-for-byte deterministic Free/Pro ZIP build checks;
   - SHA-256 checks;
   - Free package boundary validation;
   - Pro package boundary validation;
   - packaged bootstrap verification for Free-only, compatible Free-first, compatible Pro-first, incompatible Free-first and incompatible Pro-first.
7. `tools/release/verify-free-pro-bootstrap.php`
   - proves current packaged Free-only boot has CPT + Taxonomy and no implemented Pro modules;
   - proves compatible packaged Free+Pro registers the current 12 implemented Pro modules;
   - proves a deliberately incompatible Platform API prevents Pro module and Custom Tables migration-class loading;
   - exercises both plugin-file include orders;
   - is a purpose-built CLI harness, **not** a real WordPress activation/request-context P-006 fixture.
8. `frameworks/Bootstrap/Plugin.php`
   - Free owns Platform bootstrap + CPT + Taxonomy;
   - optional Pro Custom Tables runtime/migrations require canonical request-local compatibility `compatible` plus explicit boot/migration admission and required class availability;
   - Free persistence/migrations remain independently owned.
9. `frameworks/Platform/Admin/RuntimeDiagnosticsSnapshot.php`
   - reads canonical request-local Pro compatibility state;
   - keeps compatibility and entitlement separate;
   - labels compatibility certification `adr_0010_not_certified`;
   - normalizes compatibility tokens/versions/generations before read-only exposure.
10. `.github/workflows/platform-compatibility.yml`
    - existing non-P-006 WordPress/PHP regression matrix: WordPress `6.9` and `7.1` × PHP `8.2`, `8.3`, `8.4`, `8.5`;
    - this matrix is reusable infrastructure, but it is not by itself the accepted P-001/P-006 certification matrix.

Historical PR #921 exact-head regression evidence and packaged bootstrap checks may be cited as **prior implementation evidence only**. They must not be relabeled as FP execution.

## 4. Future evidence record shape for every FP fixture

When execution is separately authorized, every FP-01…FP-44 result must record at minimum:

- fixture ID and protocol revision;
- exact source commit and immutable Free/Pro artifact SHA-256 values;
- parsed Free plugin version, Platform API and platform schema;
- parsed Pro version, supported Free/API/schema ranges and Pro schema;
- WordPress/PHP/database/site mode;
- installed directory names and activation/load order where applicable;
- request context (`admin`, `frontend`, `REST`, `cron`, `WP-CLI`, recovery mode, or harness-only where protocol permits);
- expected compatibility state/dimension/reason/remediation;
- actual compatibility state/dimension/reason/remediation;
- premium module registration result;
- premium migration-admission result, without executing a migration unless Lane C is separately authorized;
- Free CPT/Taxonomy availability result where applicable;
- no-network assertion where applicable;
- log/UI redaction assertion;
- data-integrity assertion where the fixture can affect durable state;
- recovery observation;
- `PASS`, `FAIL`, `INCONCLUSIVE`, or `NOT EXECUTED`;
- linked raw evidence artifact and linked defect when failing.

A prior unit test or CI run can be linked as supporting context, but the FP record must be generated by the authorized P-006 fixture run itself.

## 5. Candidate disposable environment profile — not yet accepted

Execution must not begin until P-001 compatibility-floor evidence is accepted or a temporary P-001 scope is explicitly authorized. The current repository suggests the following **candidate**, not certified, profile:

- minimum candidate: WordPress `6.9`, PHP `8.2`, MySQL `8.4`;
- reference/current candidate: WordPress `7.1`, PHP `8.5`, MySQL `8.4`;
- isolated single-site WordPress fixtures for baseline Lane A cases;
- separate Multisite/recovery/concurrency profiles only for the fixtures that require them;
- immutable extracted Free and Pro candidate ZIPs identified by SHA-256;
- disposable DB with snapshot/restore capability even where a Lane A fixture is intended to remain read-only;
- outbound network deny/record instrumentation for FP-44 and mismatch/no-network assertions;
- separate PHP process/request for artifact-switch/cache-invalidity cases so request-local state cannot contaminate the next observation.

This profile must not be treated as the accepted P-006 matrix until the relevant gate records it.

## 6. Fixture-by-fixture evidence plan — FP-01…FP-12

Evidence status labels below describe **planning readiness only**:

- `STRONG EXISTING INPUT` — current repository already has useful non-certifying implementation/regression evidence.
- `PARTIAL EXISTING INPUT` — some required behavior exists but a formal fixture needs additional environment/assertions.
- `GAP` — no adequate current evidence was identified; future harness work is required after authorization.
- `CROSS-GATE` — execution depends on another separately governed evidence domain.

| FP | Planning status | Existing input | Future authorized fixture / expected result | Stop / dependency |
| --- | --- | --- | --- | --- |
| FP-01 | STRONG EXISTING INPUT | Free header + `WPE_VERSION`, `WPE_PLATFORM_API_VERSION`, `WPE_PLATFORM_SCHEMA_GENERATION`; package manifest SHA. | Unpack exact Free ZIP; machine-parse header/constants and manifest; assert internally consistent version/API/schema/minimum-environment data and exact artifact hash. | FAIL on missing/contradictory metadata. P-001 floor must define accepted environment semantics. |
| FP-02 | STRONG EXISTING INPUT | Pro header + bounded Free/API/schema constants + Pro schema generation. | Unpack exact Pro ZIP; parse all compatibility declarations before boot; assert coherent ranges and exact artifact identity. | FAIL on missing/contradictory metadata. |
| FP-03 | PARTIAL EXISTING INPUT | Marketing version and Platform API are distinct inputs in `LocalCompatibilityPreflight`. | Use synthetic/candidate metadata pairs where marketing version changes inside a declared supported range while Platform API is unchanged; compatibility must remain based on declared ranges/API/schema, not version equality. | Requires candidate overlap metadata; no production package mutation during fixture preparation without authorization. |
| FP-04 | GAP | Runtime evaluator rejects out-of-range API, but no dedicated release-evidence rule currently proves that an API change without matching compatibility metadata is blocked. | Future release-validation fixture changes only disposable candidate metadata and proves package/release validation rejects undeclared Platform API compatibility change. | Crosses Lane B/release-validation design; stop if release path can emit contradictory metadata. |
| FP-05 | STRONG EXISTING INPUT | Build script + package workflow reject Pro modules/assets/bootstrap in Free ZIP. | Inspect exact Free candidate file list after build and independently scan prohibited Pro namespaces/assets/bootstrap. | STOP on any Pro implementation leak. |
| FP-06 | STRONG EXISTING INPUT | `Requires Plugins: wpessential` plus independent runtime preflight. | Parse Pro header dependency; boot Pro candidate with/without Free and prove header dependency is not treated as sole runtime guard. | STOP if dependency metadata bypasses local preflight. |
| FP-07 | PARTIAL EXISTING INPUT | Free/Pro headers and readme pin WP `6.9` / PHP `8.2`; Composer requires PHP `>=8.2`; packaging asserts current header/readme literals. | Machine-compare all represented PHP/WP minima across Free header, Pro header, readme, Composer and generated package metadata. | GAP: current package manifest does not carry environment/compatibility metadata; do not infer agreement from absent fields. |
| FP-08 | STRONG EXISTING INPUT | Deterministic ZIP builder writes SHA-256 into package manifests; workflow recomputes hashes and byte-compares repeated builds. | Record exact Free/Pro SHA-256 values as immutable P-006 pair identity before any boot. | STOP if repeated candidate build hashes differ or evidence references a different hash. |
| FP-09 | GAP | Current preflight is constant/metadata based, not directory-name based, but no duplicate-directory installation fixture exists. | Install identical immutable candidates under approved alternate/copied plugin directory names in disposable WordPress; prove identity/compatibility result cannot be bypassed by directory naming. | Needs isolated WordPress install semantics and explicit duplicate-plugin handling design. |
| FP-10 | STRONG EXISTING INPUT | Unit matrix fails closed on missing/malformed Free/API/schema and contradictory Pro ranges. | Execute packaged candidates with controlled malformed/missing metadata variants; expect non-fatal fail-closed result + actionable remediation, no premium load. | STOP on permissive boot or fatal. |
| FP-11 | PARTIAL EXISTING INPUT | Evaluator reads a fixed known key set, so unrelated extra array fields are naturally ignored; no metadata-schema versioning fixture exists. | Add unknown future metadata fields to disposable candidate metadata while preserving known valid fields; behavior must follow explicit versioning rule, never become permissive due parse ambiguity. | Requires metadata-versioning rule if future fields can change semantics. |
| FP-12 | PARTIAL EXISTING INPUT | Package manifests expose only artifact metadata; Runtime Diagnostics sanitizes compatibility tokens/versions and remains read-only. | Capture package/compatibility diagnostics under authorized admin context and assert only safe metadata is exposed; scan output/logs for token/secret/account patterns. | Security authority/redaction execution is coordinated with Lane E; STOP on any sensitive value. |

## 7. Fixture-by-fixture evidence plan — FP-13…FP-28

| FP | Planning status | Existing input | Future authorized fixture / expected result | Stop / dependency |
| --- | --- | --- | --- | --- |
| FP-13 | PARTIAL EXISTING INPUT | Packaged CLI verifier proves Free-only CPT + Taxonomy boot; platform matrix has real WP minimum version infrastructure. | Fresh disposable WordPress at accepted minimum; install exact Free ZIP, activate Free, perform admin/frontend/REST/cron/CLI smoke; expect non-fatal boot and Free owner modules available. | Requires accepted P-001 minimum environment and P-006 execution authorization. |
| FP-14 | PARTIAL EXISTING INPUT | Same source evidence; existing matrix includes reference WP/PHP candidates. | Repeat Free-only clean activation on accepted reference/current environment. | Same prerequisites as FP-13. |
| FP-15 | STRONG EXISTING INPUT | Packaged verifier proves compatible Free-first include order and premium registration after preflight. | Real WordPress: activate Free then compatible Pro; capture ordering; premium modules must contribute only after canonical compatibility PASS. | STOP if any premium module/service appears before PASS. |
| FP-16 | STRONG EXISTING INPUT | Packaged verifier proves Pro-first file include order is non-fatal and reaches same compatible result after Free callback ordering. | Real disposable WordPress activation/load-order fixture for Pro-first condition permitted by WordPress dependency rules; expect non-fatal inert/deferred Pro until Free is ready, then same decision. | If WordPress prevents this activation topology, record exact platform behavior; do not fake it with only CLI include ordering. |
| FP-17 | GAP | No dedicated installed-but-inactive Pro WordPress fixture. | Install Pro ZIP without activating it while Free is active; compare hooks/modules/classes/DB/network activity to Free-only baseline; expect no premium runtime side effects. | STOP on premium hook/module/migration/provider side effect. |
| FP-18 | PARTIAL EXISTING INPUT | Evaluator has `free_missing`; Pro bootstrap is intended to fail closed before premium load. | Pro-active/Free-missing disposable fixtures across admin/frontend/REST/cron/WP-CLI where WordPress allows the state; expect non-fatal degraded behavior and zero premium handlers/migrations. | WordPress `Requires Plugins` behavior must be recorded, not bypassed silently. |
| FP-19 | PARTIAL EXISTING INPUT | Unit test covers `free_version_too_old`. | Boot exact disposable pair with Free below declared supported minimum; expect fail-closed before premium registration in all selected contexts. | Requires immutable older candidate artifact or explicitly authorized synthetic candidate. |
| FP-20 | PARTIAL EXISTING INPUT | Unit test covers `free_version_too_new`; packaged mismatch verifier covers Platform API too-new. | Boot exact pair with Free marketing version above supported max; expect fail-closed before premium registration. | Requires immutable candidate pair. |
| FP-21 | CROSS-GATE | Current Pro range is exact `0.1.0-dev`; no older-Pro overlap candidate exists. | Select two immutable candidate releases with documented overlap; newer Free + older supported Pro must boot documented supported path. | Lane B owns update/range matrix; mark `NOT EXECUTED` until real overlap artifacts exist. |
| FP-22 | CROSS-GATE | Same limitation as FP-21. | Older supported Free + newer Pro inside declared overlap must boot only supported functionality. | Lane B / accepted compatibility matrix required. |
| FP-23 | STRONG EXISTING INPUT | Packaged incompatible verifier always checks CPT + Taxonomy before mismatch-specific assertions. | Real mismatch request fixture must prove CPT/Taxonomy remain available while premium remains inert. | STOP if mismatch disables unrelated Free owner functionality. |
| FP-24 | CROSS-GATE | Current local pair preflight is pair-wide; no per-adapter binary compatibility contract identified. | First record applicability decision. If future contract permits per-module compatibility degradation, construct one incompatible adapter and prove unrelated compatible premium modules remain available; otherwise mark protocol fixture N/A with accepted rationale. | Requires explicit module-level compatibility contract; do not invent one in P-006. |
| FP-25 | PARTIAL EXISTING INPUT | Pro mismatch registers escaped `admin_notices` message containing reason + remediation. | Real wp-admin request with mismatch; assert HTTP/non-fatal boot, authorized notice, exact safe reason/remediation, no global admin hijack. | Security rendering assertions coordinate with Lane E. |
| FP-26 | PARTIAL EXISTING INPUT | Mismatch notice is admin-only; premium bootstrap returns early. | Real public frontend request during mismatch; expect successful Free rendering, no fatal, no internal path/secret/account/license leakage. | STOP on fatal or private/internal disclosure. |
| FP-27 | PARTIAL EXISTING INPUT | Preflight prevents premium module/class contribution after mismatch. | Real REST + WordPress Abilities request under mismatch; enumerate registered handlers before/after; incompatible premium handlers must be absent while Free handlers remain safe. | Needs real WP request harness; STOP on premium handler registration. |
| FP-28 | PARTIAL EXISTING INPUT | Packaged CLI verifier is non-fatal but does not cover real cron/CLI WordPress bootstrap. | Run disposable cron and WP-CLI bootstrap under mismatch; expect non-fatal Free operation and no incompatible premium handlers/migrations. | Requires authorized WP-CLI/cron fixture environment. |

## 8. Fixture-by-fixture evidence plan — FP-29…FP-44

| FP | Planning status | Existing input | Future authorized fixture / expected result | Stop / dependency |
| --- | --- | --- | --- | --- |
| FP-29 | STRONG EXISTING INPUT | Pro autoloader permits only Compatibility namespace before canonical PASS; preflight is evaluated before required Free runtime classes and module registration; packaged Pro-first mismatch is non-fatal. | Instrument class-load sequence in disposable packaged pair; prove no unavailable Free symbol is referenced before compatibility result is established. | STOP on preflight-order fatal or premium class load before decision. |
| FP-30 | STRONG EXISTING INPUT | Free package physically excludes Pro source; Free-only package verifier rejects Pro modules/classes/package-active state. | Instrument Free-only autoload requests; assert no Pro-only file/symbol is required to boot Free CPT/Taxonomy. | STOP on eager Pro dependency. |
| FP-31 | STRONG EXISTING INPUT | `LocalCompatibilityPreflight` is standalone local metadata logic; Pro autoloader exposes it before premium service/module loading. | Class-load/dependency trace proves compatibility evaluator executes without initializing premium service container/modules or requiring entitlement/Membership. | STOP if preflight needs premium service initialization. |
| FP-32 | GAP | No opcache/mixed-filesystem replacement fixture identified. | Use disposable PHP runtime with opcache/config matching supported deployment model; replace one candidate with another between isolated requests, reset/invalidate according to deployment contract, and assert no mixed-version fatal/premium mutation. | Needs Lane B deployment/update model. Do not claim universal host/opcache coverage. |
| FP-33 | PARTIAL EXISTING INPUT | No `register_activation_hook` usage was identified by repository search; migrations are ordinary runtime-controlled and Pro-owned migration registration is compatibility-gated. | Static scan + real activation fixture prove no duplicate activation path can start destructive work before compatibility. | If activation hooks are introduced later, fixture must expand. Lane C owns migration execution. |
| FP-34 | STRONG EXISTING INPUT | Packaged verifier runs compatible and incompatible Free-first + Pro-first orders and expects the same compatibility outcome. | Repeat as formal fixture using immutable P-006 candidate hashes; compare complete state/dimension/reason/remediation/module set across orders. | STOP on decision drift by load order. |
| FP-35 | CROSS-GATE | Current Lane A source is request/site oriented; protocol requires declared Multisite behavior. | Network-activate Free, site-activate Pro in disposable Multisite; assert behavior matches the accepted Multisite compatibility contract. | Lane D / Multisite allocation semantics and explicit scope contract required. |
| FP-36 | CROSS-GATE | No accepted evidence for inverse network/site activation topology. | Site-activated Free + network-activated Pro must fail/degrade explicitly if unsupported, never infer current-blog safety. | Lane D / Multisite contract required. |
| FP-37 | GAP | No MU-plugin/host bootstrap ordering fixture identified. | Disposable MU-plugin loads/observes boot at deliberately early phases; incomplete Free bootstrap must not be treated as compatible merely because partial constants/classes exist. | STOP on false-compatible incomplete boot. |
| FP-38 | GAP | No WordPress recovery-mode fixture identified. | Trigger/enter disposable WordPress Recovery Mode with known Free/Pro mismatch; mismatch diagnosis must work without requiring incompatible premium code to boot. | Needs safe recovery-mode harness and no production email/account usage. |
| FP-39 | CROSS-GATE | Fail-closed mismatch path itself performs no deletion, but no durable recovery-mode data-integrity fixture exists. | Snapshot DB/files before recovery-mode mismatch; inspect after diagnostic/recovery flow; Pro data must remain unchanged. | Lane C provides durable-state integrity/restore evidence. STOP on mutation/deletion. |
| FP-40 | PARTIAL EXISTING INPUT | Canonical compatibility state is request-local global state; evaluator does not persist a compatibility cache. | Execute separate requests before/after immutable artifact version replacement; state must be recomputed from new metadata and old result must not survive. | Lane B owns replacement/update mechanics. |
| FP-41 | PARTIAL EXISTING INPUT | Current compatibility evaluator does not read WordPress object cache/transients; request-local result is rebuilt during bootstrap. | Install instrumented external object-cache fixture seeded with stale compatibility-shaped data; prove it cannot authorize premium boot after artifact mismatch. | STOP if object cache/transient can become compatibility authority. |
| FP-42 | STRONG EXISTING INPUT | Evaluator contains no time/clock dependency. | Run identical metadata evaluation under controlled different process timezone/clock settings; compatibility result must be identical. | Any clock-sensitive binary result is a defect. |
| FP-43 | CROSS-GATE | Canonical gating exists, but no concurrent file-switch fixture. | Concurrent requests span an authorized package-switch window; no request seeing unverified/mixed pair may gain `premium_migrations_allowed`; record process/artifact identity per request. | Lane B owns update window; Lane C owns migration-safety assertion. STOP on migration admission from mixed state. |
| FP-44 | PARTIAL EXISTING INPUT | `LocalCompatibilityPreflight` is local-only and contains no network calls; no measured boot budget is recorded. | Instrument wall/CPU time across representative requests and install outbound-network spy/deny layer; preflight must remain within an accepted budget and make zero remote calls. | Performance budget must be declared before PASS; remote call is stop-the-line. |

## 9. Planned harness topology after authorization

No harness is created by this document. The future executable gate should prefer reusable layers rather than 44 ad-hoc scripts:

### H1 — Artifact inspector

Responsibilities:

- unzip immutable Free/Pro candidates;
- calculate and record SHA-256;
- parse plugin headers and known compatibility constants without booting WordPress;
- inspect deterministic package manifests/file lists;
- assert Free/Pro physical package boundaries;
- emit redacted machine-readable evidence for FP-01…FP-12.

### H2 — Pure local preflight table

Responsibilities:

- drive `LocalCompatibilityPreflight::evaluate()` from explicitly enumerated metadata fixtures;
- cover missing, malformed, contradictory, boundary, older/newer and unknown-field cases;
- record full state/dimension/reason/remediation/admission result;
- remain network- and database-free.

This can reuse existing unit-test patterns but must emit P-006 evidence only when separately authorized.

### H3 — Packaged bootstrap harness

Responsibilities:

- consume immutable extracted candidates, never repository working-tree source as a substitute;
- execute Free-only, Free→Pro, Pro→Free, mismatch and missing-package permutations;
- trace class/module registration order;
- assert CPT/Taxonomy continuity;
- assert Pro module and migration-class absence before/after failed preflight.

`tools/release/verify-free-pro-bootstrap.php` is a strong prototype but formal P-006 execution must pin hashes and record the protocol-required result dimensions.

### H4 — Real WordPress request-context harness

Responsibilities:

- clean isolated WordPress install from the accepted environment matrix;
- install exact ZIP candidates;
- drive activation/deactivation states allowed by WordPress;
- issue admin, frontend, REST, cron and WP-CLI requests;
- capture HTTP/exit status, registered modules/handlers, notices, logs and redaction checks;
- reset from a clean snapshot between topology-changing cases.

### H5 — Adversarial boot/recovery instrumentation

Responsibilities:

- alternate directory names;
- object-cache/transient poisoning attempts;
- MU-plugin/early-bootstrap observation;
- Recovery Mode;
- opcache/artifact replacement model;
- clock variation;
- concurrency/update-window tracing;
- network-call denial/recording;
- measured preflight overhead.

H5 must be decomposed further by Lane B/C/D/E when it crosses update, migration, entitlement, Multisite or security authority.

## 10. Stop-the-line policy for Lane A execution

In addition to the global P-006 stop-the-line rules, Lane A execution must stop immediately if any authorized fixture confirms:

1. a known incompatible/missing/incomplete pair fatals a supported request context that should degrade safely;
2. any premium module/service/class requiring a verified pair registers before canonical compatibility PASS;
3. Pro migration admission becomes true before canonical compatibility PASS;
4. missing/malformed/contradictory metadata defaults to compatible;
5. Free-only boot requires Pro implementation or loses CPT/Taxonomy because Pro is absent/mismatched;
6. Free and Pro file/load order produces conflicting compatibility truth for the same immutable pair;
7. a stale persistent cache becomes compatibility authority after artifact replacement;
8. a directory name, local clock or entitlement-like state can manufacture package compatibility;
9. compatibility preflight performs an undeclared remote call;
10. UI/log/evidence output exposes secret/private commercial material;
11. exact artifact hash recorded by the fixture does not match the artifact actually executed.

After a stop-the-line event, remaining fixtures stay `NOT EXECUTED` until the defect is repaired under a separately authorized implementation gate and a fresh exact pair is selected.

## 11. Cross-gate dependency map

| Dependency | Lane A fixtures affected | Required state before execution |
| --- | --- | --- |
| ADR-0014 P-006 scoped owner consent | FP-01…FP-44 | Mandatory for any executable P-006 action. |
| P-001 compatibility floor / temporary scoped matrix | FP-13, FP-14 and all real WP environment claims | Accepted floor or explicitly scoped temporary evidence. |
| ADR-0010 decision state | Certification conclusion, not basic planning | Do not infer acceptance from implementation. Any acceptance must satisfy its own criteria. |
| Lane B — update/API ranges | FP-03, FP-04, FP-21, FP-22, FP-32, FP-40, FP-43 | Real overlap/update/replacement semantics defined. |
| Lane C — schema/migrations | FP-39, FP-43 and migration side of FP-29/33 | DB snapshot, migration safety and recovery gate authorized. |
| Lane D — entitlement/Multisite | FP-35, FP-36 and any commercial state assertions | Explicit site/network/allocation boundaries; no entitlement inference. |
| Lane E — security/observability | FP-12, FP-25, FP-26, FP-37, FP-38, FP-41, FP-44 | Authority/privacy/redaction/adversarial assertions coordinated. |
| Updater/TUF | FP-32/40/43 only if updater mechanics are used | Separate updater authorization; Lane A must not silently expand into it. |

## 12. Authorization-ready execution slices

After all prerequisites are met, execution should be opened in small bounded slices rather than authorizing all FP-01…FP-44 at once:

1. **A1 — Artifact identity / metadata**: FP-01…FP-12, no WordPress activation, no DB mutation.
2. **A2 — Baseline single-site boot**: FP-13…FP-20, FP-23, FP-25…FP-31, FP-34, only immutable candidate install/activation in disposable single-site fixtures.
3. **A3 — Overlap and specialized host topology**: FP-21, FP-22, FP-32, FP-37, FP-38, FP-40…FP-44 after Lane B/C/E prerequisites.
4. **A4 — Multisite boot topology**: FP-35, FP-36 only after Lane D contract/authorization.
5. **A5 — Conditional per-module compatibility**: FP-24 only if an accepted contract declares the fixture applicable.

Each slice needs its own exact main anchor, immutable artifact hashes, environment selection, allowed side effects and stop condition.

## 13. Readiness conclusion

Lane A is **PLANNING COMPLETE / EXECUTION BLOCKED** when this document is accepted.

Current implementation provides strong non-certifying inputs for deterministic packaging, physical Free/Pro separation, local fail-closed metadata evaluation, compatible/incompatible both-order packaged bootstrap and request-local compatibility gating. The largest formal P-006 evidence gaps are real WordPress install/activation/request-context coverage, version-overlap candidate pairs, duplicate-directory/recovery/opcache/concurrency fixtures, explicit performance budget measurement, and cross-gate Multisite/update/migration/security evidence.

No FP fixture has been executed by this planning work. P-006 counters remain unchanged, ADR-0010 remains Proposed, and no certified Free↔Pro artifact pair exists.
