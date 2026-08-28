# WPEssential — Asset Registry & Scoped Loader Executable Evidence Protocol

Status: **Phase 0 evidence specification / EXECUTION NOT AUTHORIZED**  
Date: 2026-08-28  
Work package: `P0-M00-WP33`  
Related: ADR-0012, ADR-0014, ADR-0125, ADR-0126, ADR-0143, ADR-0146, ADR-0148, `docs/ARCHITECTURE.md`, UI, Build, Component Blueprint, Builder Widgets, Frontend Dashboard, Dynamic Listings, Module Registry, Multisite.

## 1. Purpose

Freeze the future executable evidence required for WPEssential's shared **Asset Registry and scoped loader**.

The protocol freezes **ASR-01…ASR-176**.

Current execution truth: **0/176 executed**.

No Asset Registry/scoped-loader runtime certification exists.

Existing UI, Build, Component Blueprint and builder-adapter protocols verify their own asset behavior. This protocol verifies the shared platform registry itself: asset identity/ownership, dependency graph, context/scope matching, WordPress handle coexistence, version/hash truth, deduplication/conflict behavior, dynamic/late discovery, loading strategy, lifecycle/degraded states, security, Multisite and performance.

No script/style registration, enqueue, build, browser load, WordPress hook execution or benchmark is authorized by this document.

---

## 2. Canonical boundaries

Keep distinct:

`source asset ≠ built file ≠ manifest entry ≠ registry descriptor ≠ WordPress handle ≠ dependency edge ≠ resolved load plan ≠ enqueued handle ≠ browser-fetched resource ≠ executed module ≠ certified asset behavior`

Also:
- asset declared ≠ asset required on every route;
- registered ≠ enqueued;
- enqueued ≠ successfully fetched/executed;
- same library name ≠ compatible version;
- same URL ≠ same semantic asset;
- shared chunk ≠ global admin asset;
- builder/editor asset ≠ frontend asset;
- component dependency ≠ permission to load arbitrary remote code;
- Pro expiry/module disable ≠ automatic deletion of deployed assets required for accepted safe output;
- current admin route/blog ≠ durable asset ownership.

---

## 3. Canonical descriptor

Every platform asset descriptor records applicable fields:
- stable namespaced asset key;
- WordPress handle where used;
- owner module/platform/adapter;
- kind: script/style/module/other approved type;
- admin/frontend/editor/both scope;
- route/screen/component/context match;
- dependency handles/asset keys;
- version/content hash/build fingerprint;
- loading strategy (`blocking`, `defer`, `async`, module, style strategy where supported);
- localization/runtime-data dependency contract;
- editor/frontend split;
- availability/edition/dependency requirements;
- compatibility with WordPress/core/third-party-provided handles;
- lifecycle/degraded behavior;
- integrity/origin policy for any approved external asset class;
- diagnostics/provenance metadata.

User definitions never become arbitrary executable URL/script/style declarations unless a separately approved security model explicitly exists.

---

## 4. Independent certification classes

- `ASR-R` — registry identity/ownership/descriptor validation;
- `ASR-D` — dependency graph/order/conflict resolution;
- `ASR-S` — scope/route/screen/component matching;
- `ASR-W` — WordPress handle/runtime coexistence;
- `ASR-B` — build-manifest/version/hash/provenance integration;
- `ASR-L` — enqueue/loading/late discovery/client behavior;
- `ASR-C` — cache/version/invalidation/CDN-origin semantics;
- `ASR-M` — module/Pro/lifecycle/degraded behavior;
- `ASR-X` — security/CSP/SRI/remote/extension boundaries;
- `ASR-O` — Multisite/observability/performance/regression.

Passing one class never certifies another.

---

# 5. Fixed executable fixture matrix

## A. Registry identity, ownership and descriptor validation — ASR-01…ASR-16

- **ASR-01** — valid first-party asset registers stable namespaced key + owner + kind.
- **ASR-02** — duplicate asset key with different content/owner is rejected; no silent last-write-wins.
- **ASR-03** — third party cannot claim reserved first-party asset namespace.
- **ASR-04** — repeated bootstrap registration is idempotent.
- **ASR-05** — descriptor requires supported kind and valid local/build-manifest reference.
- **ASR-06** — missing required descriptor field fails registration before enqueue.
- **ASR-07** — invalid dependency identifier fails validation, not browser-time surprise.
- **ASR-08** — asset owner module unavailable makes descriptor explicit unavailable/degraded.
- **ASR-09** — editor/frontend scope is explicit and not inferred from filename.
- **ASR-10** — route/screen match is typed/normalized, not arbitrary executable callback from user config.
- **ASR-11** — version/hash truth comes from build/runtime profile and is not mutable caller input.
- **ASR-12** — descriptor version incompatibility enters explicit degraded state.
- **ASR-13** — unknown future descriptor semantics fail safe rather than silently dropping constraints.
- **ASR-14** — registry read is side-effect free and does not enqueue/fetch assets merely to inspect metadata.
- **ASR-15** — authorized diagnostics expose key/owner/scope/deps/version without secret/private data.
- **ASR-16** — large registry lookup remains bounded and avoids filesystem hashing every asset on every request.

## B. Dependency graph, ordering, cycles and conflicts — ASR-17…ASR-32

- **ASR-17** — simple A→B dependency produces deterministic order.
- **ASR-18** — multi-level A→B→C order remains deterministic independent of discovery order.
- **ASR-19** — duplicate dependency edge dedupes without duplicate enqueue.
- **ASR-20** — direct cycle A→B→A is detected before enqueue.
- **ASR-21** — longer dependency cycle reports useful path and blocks affected plan.
- **ASR-22** — missing hard dependency blocks/degrades affected asset only where possible.
- **ASR-23** — optional dependency absence follows explicit fallback/profile without pretending dependency exists.
- **ASR-24** — incompatible dependency version is not accepted because handle name matches.
- **ASR-25** — two modules requiring same compatible shared asset resolve one shared handle/load.
- **ASR-26** — two modules requiring incompatible versions enter explicit conflict/degraded behavior; no arbitrary winner.
- **ASR-27** — dependency ownership does not allow child module to unregister shared platform dependency.
- **ASR-28** — dependency graph cache invalidates on manifest/module/version change.
- **ASR-29** — CSS dependency/order semantics do not rely on nondeterministic hook registration order.
- **ASR-30** — script module/classic-script incompatibility is detected where runtime semantics differ.
- **ASR-31** — editor-specific dependency cannot leak into unrelated frontend plan solely through shared child dependency.
- **ASR-32** — dependency resolution at scale remains bounded and avoids repeated full-graph traversal per handle.

## C. Scope, route, screen and component matching — ASR-33…ASR-48

- **ASR-33** — WPE shell screen loads only shell/shared assets required by route.
- **ASR-34** — Module A route loads Module A assets, not inactive Module B bundle.
- **ASR-35** — unrelated WordPress admin screen loads zero WPE module assets except explicitly justified tiny global bootstrap.
- **ASR-36** — route aliases/query params cannot trick broad substring matcher into loading wrong module bundle.
- **ASR-37** — native list-table/metabox/profile screen enhancement loads only declared surface assets.
- **ASR-38** — Gutenberg/editor context loads editor assets without forcing entire WPE admin shell.
- **ASR-39** — Elementor/Bricks/WPBakery/VC editor assets load only when relevant adapter/editor active.
- **ASR-40** — frontend page with no WPE component/listing loads no unrelated WPE frontend bundle.
- **ASR-41** — frontend page with one component loads required shared + component assets once.
- **ASR-42** — multiple components with overlapping dependencies dedupe shared assets.
- **ASR-43** — conditional component not rendered does not force its optional asset unless preloading policy explicitly says so.
- **ASR-44** — AJAX/REST request does not enqueue browser UI assets accidentally.
- **ASR-45** — CLI/cron/worker context does not bootstrap browser asset runtime unnecessarily.
- **ASR-46** — iframe/editor parent-child contexts get only intended side-specific assets.
- **ASR-47** — admin route capability denial does not leak protected data through localized asset payload even if shell asset is allowed.
- **ASR-48** — scope matcher cannot use current blog/route to change durable asset ownership or other-site registry state.

## D. WordPress handle/runtime coexistence — ASR-49…ASR-64

- **ASR-49** — WPE uses WordPress-provided React/ReactDOM where accepted; no second framework copy.
- **ASR-50** — existing compatible WordPress core handle is reused rather than re-registering competing copy.
- **ASR-51** — incompatible existing third-party handle collision is diagnosed, not silently overwritten.
- **ASR-52** — WPE handle namespace avoids generic names likely to collide.
- **ASR-53** — dependency on registered-but-not-yet-enqueued core handle resolves correctly.
- **ASR-54** — external plugin deregistering shared core handle cannot make WPE silently replace global runtime with unsafe private version.
- **ASR-55** — WPE does not deregister/replace unrelated third-party handles globally to “fix” conflicts.
- **ASR-56** — registered localization/config data attaches to exact intended handle and does not expose secrets.
- **ASR-57** — duplicate `wp_localize_script`/inline config generation is avoided across repeated route boot.
- **ASR-58** — module script strategy/defer/async respects dependency execution ordering.
- **ASR-59** — CSS enqueue order remains deterministic with WordPress/core/admin styles.
- **ASR-60** — dependency already printed too late triggers explicit late/degraded behavior rather than hidden duplicate tag.
- **ASR-61** — admin-footer/header timing is compatible with declared strategy.
- **ASR-62** — multiscreen navigation/reload does not accumulate duplicate JS listeners because registry handle is loaded once per page context.
- **ASR-63** — no global DOM/CSS reset is smuggled through shared asset handle.
- **ASR-64** — coexistence fixture with representative third-party admin assets does not materially break unrelated screens.

## E. Build manifest, version/hash and provenance integration — ASR-65…ASR-80

- **ASR-65** — registry resolves built file only from accepted build manifest/artifact, not guessed source path.
- **ASR-66** — missing built file referenced by manifest is detected before production-ready claim.
- **ASR-67** — content hash/version changes when built content changes.
- **ASR-68** — unchanged deterministic build yields same logical manifest/hash under reproducibility profile.
- **ASR-69** — source map/dev-only file is not enqueued in production artifact unexpectedly.
- **ASR-70** — test/dev dependency asset absent from production load plan.
- **ASR-71** — Free artifact references no Pro-only file path for Free-only routes.
- **ASR-72** — Pro asset depends on compatible Free shared handle rather than shipping duplicate kernel/UI runtime.
- **ASR-73** — manifest records chunk dependencies emitted by chosen build tool accurately.
- **ASR-74** — dynamic import chunk filename/hash maps to shipped file.
- **ASR-75** — stale old chunk request after deploy has bounded failure/reload strategy; no blank admin permanence.
- **ASR-76** — artifact inventory contains license/provenance for bundled third-party code/assets.
- **ASR-77** — restricted/unapproved source is absent from distributable artifact.
- **ASR-78** — release asset registry refers to exact promoted artifact, not rebuilt unverified copy.
- **ASR-79** — build/version mismatch between PHP manifest and JS/CSS artifact is detected/degraded.
- **ASR-80** — BT/CI remain authoritative for build/reproducibility/provenance; ASR verifies runtime mapping only.

## F. Enqueue, loading strategy, lazy/late discovery and client behavior — ASR-81…ASR-96

- **ASR-81** — normal required script/style enqueues once.
- **ASR-82** — conditional/lazy asset loads only after matching route/component discovery.
- **ASR-83** — dynamic component discovered during render can enqueue required frontend asset before output boundary supported by profile.
- **ASR-84** — asset discovered too late for safe stylesheet/script placement enters explicit fallback/degraded path.
- **ASR-85** — lazy JS chunk failure surfaces recoverable UI/error, not silent blank screen.
- **ASR-86** — retry/reload does not create duplicate script execution or duplicate module registration.
- **ASR-87** — async/defer/module strategy is applied only when dependency/order semantics permit.
- **ASR-88** — preload/prefetch used only for declared benefit and does not globalize private/rare bundles.
- **ASR-89** — stylesheet media/conditional loading behaves as declared.
- **ASR-90** — inline script/style is minimized, nonce/CSP-compatible where relevant and bound to trusted shipped code/config.
- **ASR-91** — user-provided definition cannot become arbitrary inline executable JS/CSS via generic asset descriptor.
- **ASR-92** — localized runtime config contains IDs/non-secret settings only; tokens/credentials absent.
- **ASR-93** — page navigation within SPA-like WPE shell lazy-loads route chunk once and handles unavailable module transition.
- **ASR-94** — browser cache reuses immutable hashed asset while HTML/PHP manifest points to current version.
- **ASR-95** — failed optional adapter asset degrades only affected adapter/component where possible.
- **ASR-96** — browser/network evidence records requested/loaded/executed assets distinctly; enqueue is not treated as successful execution proof.

## G. Cache, invalidation, URL/origin and CDN semantics — ASR-97…ASR-112

- **ASR-97** — immutable content-hashed asset may use long cache profile without serving stale mutable config.
- **ASR-98** — mutable non-hashed asset uses version/invalidation profile preventing indefinite stale code.
- **ASR-99** — deploy changes manifest pointer atomically enough to avoid mixed old/new dependency graph where possible.
- **ASR-100** — object/page cache containing generated asset tags invalidates when manifest generation changes.
- **ASR-101** — site URL/path change resolves new asset URL without rewriting canonical ownership incorrectly.
- **ASR-102** — HTTPS/admin SSL profile never downgrades asset to insecure HTTP unexpectedly.
- **ASR-103** — approved CDN/base URL cannot be caller-controlled SSRF/script-injection input.
- **ASR-104** — CDN outage has truthful degraded/local fallback only if explicitly supported; no hidden arbitrary-origin fallback.
- **ASR-105** — multisite subdirectory/subdomain path resolution points to correct shared plugin asset URL.
- **ASR-106** — reverse proxy/CDN origin differences do not change authorization or include private data in asset URL query strings.
- **ASR-107** — cache-busting query parameter is stable version/hash, not sensitive user/session value.
- **ASR-108** — service worker/browser cache, if present in future, cannot retain privileged admin data inside static asset cache.
- **ASR-109** — stale manifest cache detects missing file/version mismatch and refreshes safely.
- **ASR-110** — rollback to prior artifact restores matching manifest/assets as a unit where release profile supports rollback.
- **ASR-111** — partial deploy missing chunk fails release/health evidence.
- **ASR-112** — asset cache cleanup removes obsolete generated files only by owned manifest/inventory, never arbitrary paths.

## H. Module lifecycle, Pro expiry and degraded behavior — ASR-113…ASR-128

- **ASR-113** — module enable registers its descriptors exactly once.
- **ASR-114** — module disable stops editor/admin assets not required for retained safe output/recovery.
- **ASR-115** — module disable preserves shared platform assets still needed by other modules.
- **ASR-116** — re-enable does not duplicate handles/dependencies/listeners.
- **ASR-117** — hard dependency loss removes/degrades dependent asset plan before broken execution.
- **ASR-118** — optional adapter loss removes only adapter-specific assets.
- **ASR-119** — Pro expiry keeps assets required for accepted safe deployed frontend/access enforcement under ADR-0007.
- **ASR-120** — Pro expiry blocks paid editor/new-creation assets where product contract makes editing read-only without breaking deployed output.
- **ASR-121** — Pro plugin deactivation cannot deregister Free-owned shared assets required by Free routes.
- **ASR-122** — plugin deactivation/uninstall does not surprise-delete user-uploaded media merely because referenced by definitions.
- **ASR-123** — retained definitions referencing unavailable asset produce safe frontend/admin degraded diagnostics.
- **ASR-124** — migration/version change invalidates incompatible asset descriptors/chunks.
- **ASR-125** — stale queued Job/Workflow does not assume unavailable editor/browser asset as runtime dependency.
- **ASR-126** — recovery mode uses minimal safe asset set without loading every optional module bundle.
- **ASR-127** — cleanup deletes only registry/build-owned generated assets selected by MLC policy.
- **ASR-128** — MLC/VER/FP remain authoritative for lifecycle/version/entitlement; ASR verifies asset consequences only.

## I. Security, CSP/SRI, remote and extension boundaries — ASR-129…ASR-144

- **ASR-129** — generic registry rejects arbitrary user-supplied JavaScript URL.
- **ASR-130** — generic registry rejects arbitrary user-supplied executable inline JavaScript.
- **ASR-131** — generic registry rejects arbitrary CSS intended to escape scoped security model where no developer-mode contract exists.
- **ASR-132** — approved external asset class uses explicit allowlisted origin/trust profile.
- **ASR-133** — redirect from approved external asset to unapproved origin is not silently trusted where verifier/profile requires origin pinning.
- **ASR-134** — SRI/integrity metadata, if claimed, is verified against fetched external static asset and versioned deliberately.
- **ASR-135** — CSP nonce/hash integration, if host/profile supports it, does not require unsafe-inline blanket weakening.
- **ASR-136** — asset URL/path traversal cannot escape owned plugin/build directories.
- **ASR-137** — symlink/path confusion in generated cleanup cannot delete/read outside allowed roots.
- **ASR-138** — asset descriptor/localized config never contains Vault credentials, OAuth tokens, nonces intended as reusable secrets, passwords or private file tokens.
- **ASR-139** — SVG/icon asset follows content-safety rules and cannot inject arbitrary unsanitized script through generic icon registry.
- **ASR-140** — extension asset registration is namespaced and permissionless only for descriptor registration, not privileged execution/data access.
- **ASR-141** — malicious extension collision cannot replace first-party shared handle silently.
- **ASR-142** — extension failure/exception in asset descriptor does not fatal entire platform where isolation is feasible.
- **ASR-143** — source maps/debug metadata in production do not expose secrets/source files beyond accepted policy.
- **ASR-144** — static asset loading never becomes an authorization substitute for server-protected API/data operations.

## J. Multisite, network/site scope and cross-site isolation — ASR-145…ASR-160

- **ASR-145** — shared plugin file asset may be physically common while descriptor/module state remains correct per site/network.
- **ASR-146** — Site A module disabled does not remove shared handle needed by Site B.
- **ASR-147** — site-scoped admin route loads only target site's allowed module assets.
- **ASR-148** — network admin route uses network-specific asset scope and cannot be entered from child-site coordinate alone.
- **ASR-149** — localized runtime config for Site A never contains Site B private IDs/settings/data.
- **ASR-150** — shared persistent object-cache registry key includes network/install/version dimensions required to prevent cross-install collision.
- **ASR-151** — child-site option cannot override trusted global asset origin/path to executable arbitrary URL.
- **ASR-152** — network-required module asset remains available despite child-site preference where network floor requires feature.
- **ASR-153** — site lifecycle delete/uninitialize does not delete common plugin build files used by other sites.
- **ASR-154** — site clone preserves only portable asset references and re-resolves site-specific media/config.
- **ASR-155** — site URL/domain mapping change yields correct runtime URLs without changing content hash identity.
- **ASR-156** — network activation avoids redirect/enqueue behavior intended only for single-site activation flow.
- **ASR-157** — site switching during one request does not leak wrong-site localized config into later enqueued handle.
- **ASR-158** — network-wide diagnostics can aggregate asset health without exposing per-site protected runtime data.
- **ASR-159** — 100/1k/10k-site asset health inventory is paged/batched and does not initialize every site bundle on one request.
- **ASR-160** — MSI/LC remain authoritative for scope/lifecycle; ASR certifies scoped asset loading interaction only.

## K. Observability, performance and final regression — ASR-161…ASR-176

- **ASR-161** — diagnostics distinguish registered/resolved/enqueued/printed/fetched/failed where observable.
- **ASR-162** — duplicate-handle/dependency/conflict diagnostics include owner/version without dumping sensitive config.
- **ASR-163** — route asset report lists justified shell/shared/module chunks and flags unexpected modules.
- **ASR-164** — bundle/runtime report detects duplicate React/WordPress/common libraries.
- **ASR-165** — per-route JS/CSS transfer/parsed size recorded against declared budgets.
- **ASR-166** — frontend no-WPE page overhead is measured and unexpected WPE asset requests are zero unless explicitly justified.
- **ASR-167** — component/listing page query/render does not cause N duplicate asset registrations/enqueues proportional to item count.
- **ASR-168** — 1/10/100 component instances with shared deps keep unique asset count bounded.
- **ASR-169** — dependency resolution CPU/memory benchmark scales acceptably with large registry/graph; no accidental O(N²) hot path.
- **ASR-170** — cache hit/miss/manifest reload evidence is observable without filesystem/network storm.
- **ASR-171** — browser console/runtime has no duplicate-module-registration or missing-dependency errors in certified routes.
- **ASR-172** — accessibility-critical CSS/JS failure is classified as release blocker when it makes required operation inaccessible.
- **ASR-173** — stale/missing chunk/dependency failure maps to ERR stable failure semantics and recovery action.
- **ASR-174** — stop-line on unexpected WPE asset loaded on unrelated wp-admin/front page where scope contract says zero.
- **ASR-175** — stop-line on arbitrary executable remote/inline asset injection or secret-bearing localized payload.
- **ASR-176** — final regression matrix covers Free-only, Free+Pro, representative admin routes, native WP/editor, frontend components, builder adapters and Multisite profiles without generalizing beyond executed evidence.

---

## 6. MUST NOT / stop-the-line rules

Future implementation/evidence MUST NOT:
- globally enqueue optional module bundles;
- register competing React/ReactDOM/WordPress runtime copies when accepted core runtime should be reused;
- silently resolve incompatible same-handle versions by discovery order;
- allow user definitions to become arbitrary executable JS/CSS/remote URLs;
- attach secrets/private records to localized asset data;
- treat registration/enqueue as proof of successful browser execution;
- let module disable/uninstall remove shared assets used by other modules/sites;
- use current blog/route as durable registry ownership;
- delete ordinary uploaded media during generated-asset cleanup without explicit ownership;
- promote ASR success to UI/BT/CBP/BW/DL/MLC/VER/MSI provider/runtime certification.

Stop the line on:
- duplicate incompatible framework runtime;
- arbitrary executable asset injection;
- secret/private data in static/localized asset payload;
- cross-site localized-config leakage;
- material global CSS/JS leakage breaking unrelated WordPress screens;
- mixed/missing release asset graph reported healthy;
- protected deployed output becoming broken/exposed solely due entitlement/module lifecycle asset removal;
- unbounded duplicate asset loads/N+1 registration behavior.

---

## 7. Required future evidence report

Every applicable fixture records:
- ASR fixture ID/name;
- asset key/handle/owner/version/hash;
- build artifact/manifest commit/profile;
- WordPress/PHP/browser/Multisite profile;
- admin/frontend/editor/route/component context;
- resolved dependency/load plan;
- registered/enqueued/printed/fetched outcome as applicable;
- network requests + transfer/parsed size where relevant;
- duplicate/conflict/missing dependency observations;
- security/privacy/localized-data observations;
- domain refs (UI/BT/CI/CBP/BW/DL/KPA/MLC/VER/MSI/LC);
- Pass/Fail/Blocked;
- risk/deviation and retest.

Overall report states exact certified contexts/asset classes only.

---

## 8. Current truth

- ASR fixtures documented: **176**.
- ASR fixtures executed: **0/176**.
- Asset Registry/scoped-loader runtime certifications: **0**.
- No WordPress asset registration/enqueue, build, browser/network request, dynamic import, cache test, module lifecycle operation or Multisite asset fixture was executed by writing this protocol.

## Development-consent gate

**Do not execute asset registration/enqueue, build, browser/network load, dynamic import, CDN/external asset, cache, module lifecycle or Multisite fixtures until explicit owner consent under ADR-0014 and `/DEVELOPMENT-CONSENT.md`.**
