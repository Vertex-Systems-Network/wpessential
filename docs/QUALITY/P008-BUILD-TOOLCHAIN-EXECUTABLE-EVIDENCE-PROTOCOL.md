# WPEssential — P-008 Build Toolchain Executable Evidence Protocol

Status: **Phase 0 planning only / EXECUTION NOT AUTHORIZED**  
Work package: `P0-M00-WP09`  
Related: ADR-0002, ADR-0005, ADR-0011, ADR-0012, ADR-0014, P-001/CF, P-002/UI, P-007 CI.

## 1. Purpose

Freeze a bounded future comparison for WPEssential's canonical build/externalization/release-artifact toolchain before ADR-0012 can be accepted.

No package manifest, dependency install, scaffold, compile, lint, test or ZIP is authorized by this document.

## 2. Candidate order

1. `@wordpress/build` using stable build capabilities only.
2. `@wordpress/scripts` on the identical controlled fixture.
3. Document material unmet requirements.
4. Evaluate Vite only if such a requirement survives both WordPress-native candidates.
5. Laravel Mix is not a current candidate unless new authoritative repository evidence justifies reopening it.

No candidate receives credit from historical familiarity alone.

## 3. Current repository baseline

Current authoritative accessible branches contain no active root frontend package/build manifest. Historical Mix/Vite references are unverified and must not be treated as an installed/current toolchain.

The future evidence fixture is therefore a controlled technical spike, not a migration of assumed current runtime code.

## 4. Evidence state

Fixtures defined: **BT-01…BT-112**  
Fixtures executed: **0/112**  
P-008 toolchain certification: **none**  
ADR-0012: **Proposed**

## 5. Common fixture contract

Every candidate is measured on the same representative fixture containing:
- WordPress-provided React/TypeScript admin shell;
- multiple module entries;
- shared chunk and lazy chunk;
- WPE UI wrapper;
- minimum-version-compatible WP component/DataViews usage;
- capability-gated later-version theme path;
- icon abstraction;
- scoped CSS/CSS Module candidate;
- LTR/RTL outputs;
- localization extraction/runtime registration;
- generated dependency/asset metadata;
- PHP enqueue/registration adapter;
- exact-route asset loading;
- production package/ZIP;
- test/lint/typecheck integration hooks.

---

# 6. Fixed fixtures

## Group A — Research/version/baseline integrity — BT-01…BT-08

### BT-01 — execution-time official refresh
Record current supported WordPress, Node/package tooling and candidate docs/versions before any comparison.

### BT-02 — repository baseline inventory
Record actual manifests/configs on target branch; distinguish current, historical, absent and unknown rather than assuming legacy build files.

### BT-03 — identical source fixture fingerprint
All compared candidates consume semantically identical source fixture; differences are tool adapters/config only.

### BT-04 — candidate version pinning
Record exact candidate/package versions and lifecycle/stability classification.

### BT-05 — experimental-feature inventory
Identify experimental candidate features; canonical fixture must not require `@wordpress/build` experimental page/route/widget facilities.

### BT-06 — WordPress minimum/current package mapping
Record package/runtime versions compatible with the P-001 minimum and current/reference profiles.

### BT-07 — Vite admission gate
Do not evaluate Vite unless a concrete unmet requirement from BT evidence is recorded first.

### BT-08 — legacy Mix rejection truth
No Mix path is created merely because historical text mentioned it; reopening requires repository evidence + explicit requirement.

## Group B — Package manager, lockfile and dependency governance — BT-09…BT-16

### BT-09 — one package-manager policy
Fixture uses one documented package manager and one authoritative lockfile format.

### BT-10 — clean locked install
Fresh authorized environment installs exactly from lock state without hidden global dependencies.

### BT-11 — lock drift detection
Manifest change without lock update and lock change without explainable manifest/tool impact are detectable.

### BT-12 — development-only Node boundary
Built plugin starts without Node/npm/package manager on end-user WordPress host.

### BT-13 — dependency/license inventory
Generate/record direct and material transitive dependency/license inventory for distributable artifact.

### BT-14 — unsupported dependency engine
Wrong Node/tool engine fails clearly before misleading partial build.

### BT-15 — package integrity/cache cold run
Cold clean install/build does not depend on undeclared local cache/global package state.

### BT-16 — dependency update reproducibility
Allowed dependency update produces reviewable lock/artifact changes and reruns relevant externalization/regression gates.

## Group C — React and WordPress externalization — BT-17…BT-24

### BT-17 — React externalized
Production bundle contains no competing React implementation on normal wp-admin route.

### BT-18 — ReactDOM externalized
No competing ReactDOM/client runtime is bundled.

### BT-19 — JSX runtime audit
Generated output does not embed incompatible duplicate JSX framework runtime.

### BT-20 — @wordpress package dependency mapping
Imported WordPress packages map to correct runtime dependencies/handles/modules according to selected supported profile.

### BT-21 — shared vendor duplication scan
Multiple module entries do not each embed the same external/shared WordPress vendor copy.

### BT-22 — minimum WordPress runtime
Build produced for accepted/proposed minimum does not import package/runtime feature unavailable there.

### BT-23 — current WordPress runtime
Same source/wrapper contract works on current/reference profile and may capability-enable later stable features.

### BT-24 — deliberate duplicate-runtime failure
Injected misconfiguration that bundles React is detected by automated artifact/bundle gate.

## Group D — Asset metadata and PHP registration — BT-25…BT-32

### BT-25 — machine-generated dependency metadata
Entry dependency/version metadata is generated rather than manually guessed.

### BT-26 — hashed filename resolution
PHP never hardcodes/guesses content-hashed output paths.

### BT-27 — `.asset.php`/registration compatibility
Where native generated metadata is used, PHP registration resolves exact dependencies/version/file safely.

### BT-28 — missing asset metadata
Missing/corrupt metadata fails build/package verification rather than yielding a silent blank admin screen.

### BT-29 — unique handles/namespaces
WPE entry/chunk/style handles avoid collision with core/third-party plugins.

### BT-30 — dependency ordering
Runtime dependencies are registered/enqueued before dependent WPE entry.

### BT-31 — version/cache busting
Artifact version/content hash changes predictably when relevant source changes and avoids stale chunk mismatch.

### BT-32 — metadata artifact audit
Release verification checks metadata points only to packaged files and contains no dev filesystem path/secret.

## Group E — entries, shared chunks and lazy loading — BT-33…BT-40

### BT-33 — multiple module entries
Independent WPE modules build/register without one monolithic forced entry.

### BT-34 — shared chunk dedupe
Common WPE code is emitted/loaded once according to accepted strategy.

### BT-35 — lazy chunk success
Dynamic import resolves correctly through production artifact URLs/dependencies.

### BT-36 — lazy chunk failure behavior
Deleted/corrupt chunk produces diagnosable UI/recovery path rather than unexplained permanent blank state.

### BT-37 — chunk name/hash stability contract
PHP/runtime never assumes unstable internal chunk filename directly.

### BT-38 — circular dependency detection
Problematic entry/chunk dependency cycle is detectable or produces deterministic build failure.

### BT-39 — module disabled/absent
Disabled module does not force its unique entry/chunks into unrelated runtime.

### BT-40 — shared runtime across navigation
Moving between WPE routes does not duplicate shared bundle/runtime registration.

## Group F — exact-route enqueue and asset isolation — BT-41…BT-48

### BT-41 — target WPE route assets
Required shell/module assets load on exact target route/context.

### BT-42 — unrelated wp-admin absence
Unrelated wp-admin route has no module-specific WPE JS/CSS.

### BT-43 — frontend absence/default
Public frontend receives no admin assets unless a specific frontend WPE surface requires them.

### BT-44 — editor/builder context
Editor/third-party builder adapter loads only declared integration assets.

### BT-45 — multisite network admin
Network-admin assets are distinct/scoped from site-admin routes where product contract differs.

### BT-46 — duplicate enqueue
Multiple registration hooks do not execute same entry/style twice.

### BT-47 — dependency-only global bootstrap budget
Any justified global bootstrap is tiny, measured and contains no full module implementation.

### BT-48 — route isolation regression
Adding a new module entry does not increase unrelated-route payload except intentional shared-chunk change.

## Group G — CSS, LTR/RTL and styling outputs — BT-49…BT-56

### BT-49 — scoped CSS build
Representative WPE CSS Modules/scoped styles compile and retain root isolation.

### BT-50 — no global reset output
Build does not inject broad reset/preflight rules that alter unrelated wp-admin.

### BT-51 — LTR production output
Canonical LTR style registration works on target route.

### BT-52 — RTL production output
Required RTL stylesheet/output exists and is registered correctly.

### BT-53 — CSS asset dependency/order
Theme/base/module style order is deterministic and does not rely on incidental filesystem ordering.

### BT-54 — CSS minification/source mapping
Production output respects selected source-map/privacy policy and preserves functional selectors/custom properties.

### BT-55 — third-party style isolation
Adapter-specific style is separate/conditional where required.

### BT-56 — CSS duplicate analysis
Shared component styles are not unnecessarily copied into every module bundle.

## Group H — Localization and WordPress runtime strings — BT-57…BT-64

### BT-57 — JS string extraction
Representative translated strings are discovered by chosen localization pipeline.

### BT-58 — text-domain consistency
Built/runtime text domain matches plugin contract and generated catalogs.

### BT-59 — lazy chunk translations
Dynamically loaded module strings receive correct translation registration/load behavior.

### BT-60 — PHP + JS catalog coexistence
Packaging includes/addresses both PHP and JS translation artifacts without duplicate/conflicting domains.

### BT-61 — plural/context preservation
Extraction/build does not erase plural/context metadata used by source.

### BT-62 — RTL locale package path
RTL + translation outputs coexist without one overwriting the other.

### BT-63 — missing translation artifact
Missing optional locale falls back safely; missing mandatory generated registration is detected.

### BT-64 — release localization audit
ZIP contains intended localization artifacts and excludes raw tooling-only localization cache.

## Group I — PHP/Composer and platform metadata consistency — BT-65…BT-72

### BT-65 — PSR-4 autoload fixture
Production artifact resolves representative `WPEssential\` PHP class through selected Composer/autoload packaging contract.

### BT-66 — dev dependency exclusion
Composer/Node dev-only dependencies are absent from runtime ZIP unless explicitly required.

### BT-67 — plugin header vs P-001 floor
`Requires at least`/`Requires PHP` metadata matches accepted support floor when eventually selected.

### BT-68 — Composer platform consistency
Composer PHP constraint matches plugin header/CI support policy.

### BT-69 — package version consistency
Plugin/version/build metadata uses one release version source or verified consistency mechanism.

### BT-70 — stale generated PHP registration
Source asset change without regenerated registration metadata is detected.

### BT-71 — Free/Pro shared build boundary
Future Free/Pro artifacts cannot accidentally package premium-only source/assets into Free merely due shared build entry.

### BT-72 — no environment-specific path
Generated PHP/metadata contains no developer absolute path or machine-specific separator assumption.

## Group J — Production ZIP/artifact contents and safety — BT-73…BT-80

### BT-73 — clean ZIP from clean checkout
Production package can be generated from declared source/lock state with no undeclared local files.

### BT-74 — runtime allowlist/denylist
ZIP includes required PHP/assets/translations/license/readme and excludes node_modules/tests/cache/dev configs/source secrets as policy dictates.

### BT-75 — no source credential leakage
Scan packaged artifact for synthetic env/token/private-key fixtures; zero unintended secrets.

### BT-76 — no Node runtime requirement
Install/activate built ZIP on target WordPress profile without Node files/tooling.

### BT-77 — asset completeness
Every generated registration/manifest reference resolves to a file inside ZIP.

### BT-78 — case/path portability
Packaged paths work under representative case-sensitive/case-insensitive environment expectations supported by P-001.

### BT-79 — archive path safety
Packaging does not create absolute/traversal/symlink surprises that violate release policy.

### BT-80 — source-tree vs artifact verification
Runtime smoke/evidence is executed against the built ZIP artifact, not only development source tree.

## Group K — Reproducibility, source maps and build determinism — BT-81…BT-88

### BT-81 — repeated clean build comparison
Two clean builds from same commit/lock/tool versions are byte-identical where feasible or documented nondeterministic fields are isolated/explained.

### BT-82 — source map production policy
Release explicitly includes/excludes/maps source maps; no accidental absolute paths/private source disclosure.

### BT-83 — timestamp/build-id normalization
Incidental timestamps/random IDs do not cause unexplained artifact drift where avoidable.

### BT-84 — deterministic dependency metadata
Repeated build emits equivalent dependencies/versions for unchanged inputs.

### BT-85 — deterministic RTL/localization outputs
Repeated build preserves same semantic artifacts.

### BT-86 — clean workspace enforcement
Untracked/generated stale files cannot silently enter release package.

### BT-87 — artifact checksum record
Release artifact receives reproducible hash/identity recorded by later release process.

### BT-88 — diff explainability
Every artifact diff between two declared builds maps to source/dependency/tool/config/version change or is classified as a failure.

## Group L — Build/watch performance and developer ergonomics — BT-89…BT-96

### BT-89 — cold production build time
Measure same hardware/environment for each admitted candidate.

### BT-90 — warm incremental rebuild
Measure representative TSX/CSS/module edit rebuild latency.

### BT-91 — watch correctness
Watch rebuild updates correct entry/chunk/metadata without stale duplicates.

### BT-92 — output size
Compare shell/module/shared CSS+JS sizes on identical fixture.

### BT-93 — configuration surface
Record custom config/plugin/glue lines/files required for WordPress externalization and asset registration.

### BT-94 — failure diagnostics
Syntax/type/config/import failure produces actionable deterministic error.

### BT-95 — developer command simplicity
Declared install/watch/typecheck/lint/build/package commands are minimal/coherent; hidden secondary toolchain not required.

### BT-96 — maintenance burden score
Record WordPress-specific custom integration WPE must own; lower custom compatibility glue is favored when requirements are equally met.

## Group M — Cross-platform and failure/adversarial behavior — BT-97…BT-104

### BT-97 — supported OS path handling
Clean build on representative supported development/CI OS does not depend on shell-specific path syntax without documented wrapper.

### BT-98 — case-sensitive import failure
Wrong-case imports are detected before release rather than working only on case-insensitive workstation.

### BT-99 — missing dependency
Deleted package/lock mismatch fails clearly.

### BT-100 — corrupted cache/build directory
Clean rebuild recovers without relying on manually preserved stale artifacts.

### BT-101 — interrupted build
Partial output cannot be mistaken for verified release artifact; packaging requires successful complete build marker/gate.

### BT-102 — invalid asset reference
Broken import/dynamic chunk fails build or artifact verification rather than silently producing unusable route.

### BT-103 — minimum/current matrix regression
Same candidate artifact/source configuration passes declared minimum/current P-001 profiles or documents profile-specific output explicitly.

### BT-104 — experimental feature removal
Removing candidate's experimental pages/routes/widgets has no effect on canonical WPE routing/build contract.

## Group N — Candidate decision, CI handoff and release gate — BT-105…BT-112

### BT-105 — @wordpress/build scorecard
Record all mandatory results/metrics using stable capabilities only.

### BT-106 — @wordpress/scripts scorecard
Record same mandatory results/metrics on identical fixture.

### BT-107 — unmet requirement register
List any requirement neither WordPress-native candidate meets; distinguish MUST from preference/optimization.

### BT-108 — conditional Vite scorecard
Only if BT-107 contains material unmet need, evaluate Vite and exact custom WordPress glue/regression burden.

### BT-109 — single canonical tool decision
Select at most one canonical production frontend build system unless a separate ADR justifies split architecture.

### BT-110 — P-007 CI handoff
Define deterministic commands/artifacts/matrix outputs that CI can consume; do not mark CI certified merely because local build passes.

### BT-111 — migration/recovery plan
Record how generated assets/build config can be regenerated/replaced and how a failed tool upgrade is rolled back/forward-fixed without domain-module rewrite.

### BT-112 — P-008 production-readiness gate
ADR-0012 remains Proposed until mandatory fixtures pass, P-002 UI/runtime compatibility is preserved, artifact verification succeeds, no stop-line externalization/security issue remains and the candidate decision is explicitly recorded.

---

## 7. Stop-the-line conditions

P-008 cannot pass if any supported configuration:
- bundles a competing/incompatible React/ReactDOM/JSX runtime;
- silently requires a WordPress package/API newer than declared minimum;
- requires experimental candidate routes/widgets as canonical architecture;
- globally enqueues module assets on unrelated admin/frontend screens without approved need;
- produces registration metadata pointing outside/missing from release ZIP;
- requires Node/npm on end-user site;
- packages secrets/dev-only material into production artifact;
- fails RTL/localization requirements without explicit supported-scope decision;
- cannot reproduce/verify actual release artifact from declared source/lock state;
- requires two competing canonical production build systems without separate ADR.

## 8. Future report

Authorized P-008 report includes:
- BT-01…BT-112 result table;
- exact candidate/package/Node/WordPress matrix;
- identical fixture hash;
- externalization/duplicate analysis;
- route/chunk/style/l10n/RTL evidence;
- artifact contents/checksums/reproducibility results;
- build/watch/size/config metrics;
- candidate scorecards and unmet-requirement register;
- CI handoff contract;
- final recommendation: accept one candidate, revise ADR-0012, or remain inconclusive.

## 9. Development gate

No package manifest, lockfile, dependency installation, scaffold, source fixture, compile, watch server, lint, typecheck, test, bundle analysis, Composer install or ZIP/package execution is authorized. Explicit owner consent under ADR-0014 remains required.
