# WPEssential — Builder Widgets Adapter Certification Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0035, ADR-0109, Component Blueprint, DSR, Query, DVR, CLG, ASR, CAC, VER, MLC, UI, Build, CI, Policy, ADR-0014.

## 1. Purpose

Define one evidence contract for certifying WPEssential Component Blueprint adapters across Gutenberg/WordPress Blocks, Elementor, Bricks, WPBakery Page Builder and Visual Composer Website Builder.

Canonical rule:

**WPE Component Blueprint is canonical. Builder documents, private serialized structures, editor manifests and proprietary runtime metadata are adapter-owned representations only.**

No builder plugin installation, editor run, block/widget/element registration, Node build, browser fixture or frontend render is authorized by this protocol.

## 2. Certification levels

- `BC0` Detected.
- `BC1` Registration/basic save-load.
- `BC2` Render Certified.
- `BC3` Advanced capability certified.
- `BC4` Upgrade/Regression Certified.

Support claims must name exact builder/version/edition/capability/BC level. Unknown newer versions are not automatically certified.

## 3. Non-negotiable invariants

1. Builder-private document format never becomes WPE canonical component storage.
2. Builder UI visibility/config never grants WPE data/action authorization.
3. User configuration cannot become arbitrary PHP/JS eval or uncontrolled generated executable code.
4. Dynamic data always resolves through WPE owner services/Policy.
5. Missing/deactivated builder cannot fatal WPE globally or delete Component Blueprints.
6. Frontend must not unexpectedly require editor-only runtime/packages.
7. Adapter assets remain scoped and use accepted ASR/build contracts.
8. Upgrade/downgrade/version drift must be explicit and recoverable/degraded, not silent corruption.
9. Passing one builder/version/BC class never certifies another.
10. Paper/static evidence never becomes runtime certification.

## 4. Fixed fixture matrix

### A. Original shared fixtures — preserved
- **BW-01** Product identity detection distinguishes similarly named builders.
- **BW-02** Exact version/edition profile and certified/uncertified state.
- **BW-03** Registration lifecycle enable/disable/reload.
- **BW-04** Canonical Blueprint independence from proprietary page serialization.
- **BW-05** Dynamic data authorization through WPE services.
- **BW-06** Asset isolation.
- **BW-07** No arbitrary runtime code generation/eval.
- **BW-08** Missing builder safe degradation.
- **BW-09** Stored content backward compatibility.
- **BW-10** Accessibility/common semantic output.

### B. Original Gutenberg fixtures — preserved
- **BW-11** Block registration metadata.
- **BW-12** Editor insertion/save/reload.
- **BW-13** Dynamic server render.
- **BW-14** Block Supports mapping.
- **BW-15** InnerBlocks/container class.
- **BW-16** Editor/frontend asset separation.
- **BW-17** Theme/block-style compatibility.
- **BW-18** WordPress major-version regression.

### C. Original Elementor fixtures — preserved
- **BW-19** Widget registration.
- **BW-20** Editor/frontend parity.
- **BW-21** Content/style control mapping.
- **BW-22** WPE dynamic data without native Dynamic Tags.
- **BW-23** Native Dynamic Tags edition/version gate.
- **BW-24** Responsive controls/assets.
- **BW-25** Elementor template reference degradation.
- **BW-26** Feature/version drift diagnostics.

### D. Original Bricks fixtures — preserved
- **BW-27** Bricks element registration.
- **BW-28** Editor/frontend parity.
- **BW-29** Control/style mapping.
- **BW-30** Dynamic data bridge.
- **BW-31** Nested element support gating.
- **BW-32** Theme/builder context isolation.
- **BW-33** Builder absent/changed API safe disable.
- **BW-34** Version regression.

### E. Original WPBakery fixtures — preserved
- **BW-35** Stable shortcode contract.
- **BW-36** `vc_map` editor mapping.
- **BW-37** Shortcode attribute validation.
- **BW-38** Frontend render without editor UI.
- **BW-39** Nested/container mapping.
- **BW-40** UI dependencies ≠ security conditions.
- **BW-41** Existing shortcode content regression.
- **BW-42** Explicit version-scoped certification.

### F. Original Visual Composer fixtures — preserved
- **BW-43** Separate product detection.
- **BW-44** Element manifest/settings mapping.
- **BW-45** Prebuilt adapter runtime; no customer build required.
- **BW-46** Trusted shipped editor glue; definitions remain data.
- **BW-47** Public render contract independent from editor runtime.
- **BW-48** Dynamic content bridge only after typed evidence.
- **BW-49** Nested element support separately certified.
- **BW-50** Version/toolchain regression refresh.

### G. Canonical Blueprint/version/dependency lifecycle
- **BW-51** Draft Blueprint never mutates published builder representation.
- **BW-52** Published render pins Blueprint revision.
- **BW-53** Concurrent Blueprint publish conflict is explicit.
- **BW-54** Component UUID survives title/label rename.
- **BW-55** Control UUID/key migration is explicit and versioned.
- **BW-56** Unknown future Blueprint schema degrades safely.
- **BW-57** Migrator chain preserves semantic component identity.
- **BW-58** Missing source/query dependency degrades locally.
- **BW-59** Missing style/asset dependency does not weaken authorization.
- **BW-60** Adapter disable preserves Blueprint/config references.
- **BW-61** Adapter re-enable revalidates builder/API/version compatibility.
- **BW-62** Builder plugin deactivation leaves stored builder document intact.
- **BW-63** Pro expiry preserves safe published frontend output per lifecycle contract.
- **BW-64** Free↔Pro version skew does not fork Blueprint semantics.
- **BW-65** Import/export carries Blueprint, not proprietary builder page document by default.
- **BW-66** Clone/transfer remaps site-owned dependencies and cache identities.

### H. Dynamic data, Policy, actions and security
- **BW-67** DSR readable does not imply writable action capability.
- **BW-68** Query binding uses typed AST/parameters only.
- **BW-69** Query protected counts/facets do not leak through builder UI/render.
- **BW-70** Relation binding reauthorizes endpoints/fields.
- **BW-71** DVR canonical value precedes builder formatting.
- **BW-72** HTML text escaping survives builder wrappers.
- **BW-73** Attribute escaping survives builder wrappers.
- **BW-74** URL value rejects unsafe scheme after builder transforms.
- **BW-75** JSON/editor bootstrap prevents script breakout.
- **BW-76** Trusted markup requires explicit trusted context/provider.
- **BW-77** Secret/Vault/provider credentials never reach controls/editor/bootstrap.
- **BW-78** Profile protected identity/security values remain unavailable to generic bindings.
- **BW-79** Membership protected data respects current access generation.
- **BW-80** Condition `true` never grants missing Policy.
- **BW-81** Builder control visibility never authorizes frontend output/action.
- **BW-82** Direct frontend render rechecks current principal/resource Policy.
- **BW-83** Registered action invokes typed Ability only.
- **BW-84** Forged builder attributes cannot choose arbitrary Ability/class/function.
- **BW-85** High-risk action requires owning reauth/confirmation contract.
- **BW-86** Error output uses safe shared envelope/redaction.

### I. Cache/assets/runtime isolation
- **BW-87** Render cache partitions by Blueprint revision.
- **BW-88** Render cache partitions by site/principal/access generation when needed.
- **BW-89** Source mutation invalidates dependent cache.
- **BW-90** Role/Membership revoke invalidates protected render cache.
- **BW-91** Cache backend outage does not become authorization fail-open.
- **BW-92** Builder preview cache cannot leak privileged frontend data.
- **BW-93** ASR handle ownership/dependencies are deterministic.
- **BW-94** Adapter/editor assets load only where needed.
- **BW-95** Frontend assets load only when component is rendered/discovered.
- **BW-96** Duplicate React/ReactDOM/JSX runtime is not bundled where WordPress runtime is canonical.
- **BW-97** Builder-provided dependency version conflict is detected/degraded.
- **BW-98** Lazy chunk failure degrades component locally.
- **BW-99** Stale manifest/hash mismatch is diagnosed rather than silently loading wrong bundle.
- **BW-100** Asset deregistration on adapter disable does not remove builder-owned handles.

### J. Gutenberg deeper certification
- **BW-101** Block attribute schema rejects unknown/mass-assigned properties.
- **BW-102** Server-rendered protected values are not persisted into public block comment attributes unnecessarily.
- **BW-103** Block validation/deprecation chain preserves older stored markup/config.
- **BW-104** Block editor undo/redo retains WPE semantic settings.
- **BW-105** Reusable/Synced Pattern duplication preserves component UUID/reference semantics as designed.
- **BW-106** Template lock/content-only editing cannot bypass protected WPE action settings.
- **BW-107** Block theme/global styles do not mutate WPE semantic authorization/data bindings.
- **BW-108** Interactivity/client hydration path reauthorizes protected async data.
- **BW-109** InnerBlocks child constraints are server-validated where relevant.
- **BW-110** WordPress major/minor deprecation is version-scoped in BC certification.

### K. Elementor deeper certification
- **BW-111** Widget control IDs remain stable across adapter upgrade/migration.
- **BW-112** Responsive device values map without losing canonical semantic fallback.
- **BW-113** Global Widget/template reuse preserves WPE component reference semantics.
- **BW-114** Elementor preview nonce/session does not substitute for WPE resource Policy.
- **BW-115** Dynamic Tag bridge handles missing Pro capability safely.
- **BW-116** Loop/Grid context binding cannot read unrelated protected entity.
- **BW-117** Popup/template contexts preserve frontend Policy.
- **BW-118** Elementor cache/regeneration cannot serve stale privileged WPE output.
- **BW-119** Feature experiment toggle drift changes certification state explicitly.
- **BW-120** Stored document corruption isolates WPE widget failure without arbitrary unserialize execution.

### L. Bricks deeper certification
- **BW-121** Query-loop context cannot bypass WPE Query/Policy semantics.
- **BW-122** Global element/template reuse preserves stable WPE reference.
- **BW-123** Bricks condition system remains presentation unless explicitly bridged to WPE CLG.
- **BW-124** Server-side render reauthorizes despite editor preview visibility.
- **BW-125** Nested child move/copy preserves allowed relationship constraints.
- **BW-126** Bricks cache/static render integration respects revocation-sensitive output.
- **BW-127** Custom element hooks cannot pass arbitrary executable callback from user Definition.
- **BW-128** Theme/style tokens do not introduce global leakage outside component scope.

### M. WPBakery deeper certification
- **BW-129** Shortcode parser handles malformed/nested attributes safely.
- **BW-130** Raw HTML/JS editor elements cannot be generated implicitly from WPE definitions.
- **BW-131** Template/row cloning preserves WPE semantic settings without UUID collision where uniqueness matters.
- **BW-132** Backend/frontend editor previews do not bypass data Policy.
- **BW-133** Shortcode cache/static-page cache respects protected output classification.
- **BW-134** Third-party WPBakery addon overriding shortcode tag is detected/conflicted.
- **BW-135** Deprecation/migration of shortcode attributes preserves prior content or explicit degraded state.
- **BW-136** Container recursion/depth is bounded.

### N. Visual Composer deeper certification
- **BW-137** Manifest control schema rejects unknown executable payloads.
- **BW-138** Editor message bridge validates origin/source/schema.
- **BW-139** Cloud/template sync cannot overwrite canonical WPE Blueprint silently.
- **BW-140** Dynamic source bridge reauthorizes server-side.
- **BW-141** Nested component depth/fanout is bounded.
- **BW-142** Editor bundle/runtime version mismatch becomes degraded/uncertified.
- **BW-143** Public cached render respects access-generation invalidation.
- **BW-144** Builder deactivation preserves page document references/data.

### O. Cross-builder semantic parity, lifecycle and migration
- **BW-145** Same Text Card preserves semantic text/link behavior across BC2 adapters.
- **BW-146** Same Entity Card preserves authorized entity/field result.
- **BW-147** Same Query List preserves filtered authorized result semantics.
- **BW-148** Same Conditional CTA preserves CLG result but not as authorization.
- **BW-149** Responsive Component preserves declared responsive intent within adapter capability limits.
- **BW-150** Asset Component declares equivalent required dependencies without global pollution.
- **BW-151** Empty/Error Component remains safe and localized.
- **BW-152** Repeater Component preserves typed item semantics where certified.
- **BW-153** Nested/Container Component preserves allowed parent-child semantics where certified.
- **BW-154** Unsupported capability is explicit degraded state, not silent approximation that changes meaning.
- **BW-155** Builder A document does not become Builder B migration authority.
- **BW-156** Cross-builder migration, if offered, uses Blueprint semantics plus explicit adapter mapping/conflicts.
- **BW-157** Adapter version downgrade never blindly writes newer unsupported builder structures.
- **BW-158** Unknown newer builder version defaults uncertified/degraded, not inherited BC4.

### P. Multisite, accessibility, performance and release evidence
- **BW-159** Same Blueprint UUID on different sites remains scope-safe.
- **BW-160** Site builder settings do not mutate network/global adapter state without authority.
- **BW-161** Site clone regenerates/remaps site-owned references/cache identities.
- **BW-162** Site deletion removes site-owned adapter runtime state only.
- **BW-163** Network activation/deactivation handles per-site builder presence/version differences.
- **BW-164** Multilingual/locale output preserves semantic data and cache partition.
- **BW-165** RTL editor/frontend semantics remain usable.
- **BW-166** Keyboard/focus/label semantics pass where WPE owns markup/controls.
- **BW-167** 1/10/100 WPE components on one page meet bounded render/query/asset budgets.
- **BW-168** 100/1k Blueprint definitions with small rendered subset avoid O(all) hot path where not required.
- **BW-169** Nested/repeater high-fanout profile remains bounded.
- **BW-170** Builder editor large-document load does not trigger unbounded WPE remote/query work.
- **BW-171** Frontend no-editor path excludes editor-only packages and secrets.
- **BW-172** Upgrade matrix records stored-content compatibility across declared builder versions.
- **BW-173** Build artifact/hash/provenance for shipped adapter is verified by BT/CI evidence separately.
- **BW-174** Adversarial XSS/IDOR/attribute/code-generation corpus yields zero unauthorized execution/disclosure.
- **BW-175** Missing/corrupt adapter component cannot fatal unrelated WPE/site output.
- **BW-176** Certification report pins exact builder/version/edition/WP/PHP/WPE/adapter/BC/capability profile; no generic builder support claim beyond tested evidence.

## 5. Independent certification dimensions

Future reports record per builder/version:
- BC level (`BC0…BC4`);
- registration/save-load;
- dynamic data/Policy;
- render/editor parity;
- advanced capabilities;
- asset/runtime isolation;
- accessibility;
- upgrade/regression;
- Multisite/lifecycle;
- performance.

No dimension auto-promotes another.

## 6. Stop-the-line gates

Certification fails if:
- proprietary builder serialization becomes WPE canonical format;
- protected data/action bypasses WPE Policy/Ability;
- arbitrary PHP/JS/runtime code generation is introduced from user data;
- frontend unexpectedly requires editor-only runtime;
- missing builder fatals WPE/site;
- adapter assets/runtime leak globally while inactive;
- unsupported version is marketed certified;
- upgrade/disable destroys stored references/content;
- cache/static rendering serves revoked privileged output;
- builder preview/editor visibility is treated as authorization.

## 7. Required future evidence report

Include exact builder/version/edition/profile, BW-01…BW-176 pass/fail/NA, BC level, reference Blueprint artifacts/configurations, dynamic data/Policy evidence, editor/frontend/browser results, asset/build observations, accessibility, upgrade/regression, Multisite/lifecycle, performance and unsupported/degraded capability map.

## 8. Current state

**BW fixtures documented: 176.**  
**BW fixtures executed: 0/176.**  
Gutenberg/Elementor/Bricks/WPBakery/Visual Composer runtime certifications: **0**.  
BC0…BC4 certified adapter profiles: **0**.

No builder package install, block/widget/element registration, editor run, Node build, browser fixture, cache mutation or frontend render has executed.

## 9. Development gate

Execution requires explicit owner consent under ADR-0014 and the Approval Ledger. `continue` remains planning-only.
