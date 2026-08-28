# WPEssential — Component Blueprint Core Runtime Executable Evidence Protocol

Status: **Phase 0 evidence specification / NOT AUTHORIZED FOR EXECUTION**  
Date: 2026-08-28  
Work package: `P0-M00-WP29`  
Related: ADR-0014, ADR-0035, ADR-0039, ADR-0099, ADR-0109, ADR-0137, `docs/ARCHITECTURE/COMPONENT-BLUEPRINT-RUNTIME-MODEL.md`, Definition Repository, Query, Field Storage, Relations, Policy/Abilities, Asset Registry, UI/Build, Dynamic Listings, Frontend Dashboard, Builder Widgets.

## 1. Purpose

This is the canonical future executable-evidence contract for the **shared Component Blueprint core**, independent of any specific third-party builder adapter.

Canonical pipeline:

`Component Blueprint Definition → Published Compiled Blueprint → Component Instance Settings/Bindings → Authorized Render Context → Shared Renderer → Target Adapter/Markup/Assets`

The protocol freezes **CBP-01…CBP-176**.

**Executed: 0/176.**

It does not replace Builder adapter certification `BW-01…BW-50 / BC0…BC4`; a correct core renderer does not certify Elementor, Gutenberg, Bricks, WPBakery or Visual Composer adapters, and adapter certification does not replace core security/runtime evidence.

No renderer/compiler, block/widget registration, browser render, Query execution, builder package, asset build, cache benchmark or runtime fixture is authorized by this document.

---

## 2. Truth boundaries

The following remain separate:

`Blueprint Definition ≠ Definition Revision ≠ Published Compiled Blueprint ≠ Component Instance ≠ Binding Descriptor ≠ Authorized Render Context ≠ Resolved Value ≠ Render Tree ≠ Escaped Markup ≠ Asset Graph ≠ Builder Adapter Representation ≠ cached output ≠ certified runtime behavior`

Also:
- builder document/private serialization is not canonical WPE Blueprint data;
- editor preview is not proof of frontend authorization/render correctness;
- control visibility is not server validation;
- condition visibility is not authorization;
- sanitized rich text is content, not executable code;
- asset declaration is not permission to load arbitrary remote code;
- renderer success is not Query/provider/domain-state success certification;
- public cacheability is not implied by visual equality;
- a Blueprint being published is not AI/adapter publication authority.

---

## 3. Certification classes

Certify independently:

- `CBP-D` — Definition/revision/publish/compile lifecycle;
- `CBP-C` — control schema and component-instance validation;
- `CBP-B` — dynamic binding/context/Policy correctness;
- `CBP-R` — render primitives/escaping/sanitization/output semantics;
- `CBP-S` — slots/nesting/composition/styles/responsive behavior;
- `CBP-A` — assets/dependencies/scoped loading;
- `CBP-K` — cache/personalization/invalidation;
- `CBP-X` — portability/versioning/adapters/AI boundaries;
- `CBP-U` — accessibility/degraded/error UX;
- `CBP-O` — concurrency/performance/Multisite/operational behavior.

Passing one class never implies another.

---

# 4. Fixed executable fixture matrix

## A. Definition, revision, publish & compile — CBP-01…CBP-16

### CBP-01 — Draft creation
Create valid Blueprint draft with stable UUID/key/type/control schema/primitive tree and no runtime side effect.

### CBP-02 — Immutable published revision
Publishing produces a pinned immutable revision/descriptor identity; later draft edits do not mutate deployed revision.

### CBP-03 — Invalid draft cannot publish
Schema/control/primitive/slot validation failure blocks publication with normalized ERR contract.

### CBP-04 — Unknown component type
Unknown leaf/container/collection type fails safely; no fallback that changes semantics silently.

### CBP-05 — Duplicate Blueprint key
Collision follows Definition conflict rules and never discovery-order overwrite.

### CBP-06 — Dependency fingerprint
Compiled descriptor records exact dependencies needed for cache/invalidation/compatibility.

### CBP-07 — Deterministic compilation
Same normalized revision + compiler profile yields semantically identical descriptor/fingerprint.

### CBP-08 — Compiler version capture
Descriptor records compiler/schema profile so runtime can detect incompatible stale descriptor.

### CBP-09 — Unknown future schema
Older runtime does not lossy-downgrade unknown future Blueprint schema; inspect/degraded behavior is explicit.

### CBP-10 — Additive schema evolution
Supported optional fields compile without breaking older compatible instances/adapters.

### CBP-11 — Breaking schema evolution
Required semantic change uses version/migrator/deprecation path rather than silent reinterpretation.

### CBP-12 — Dependency unavailable at publish
Hard missing dependency blocks publish; optional dependency yields explicit degraded capability only when contract allows.

### CBP-13 — Dependency unavailable after publish
Existing safe deployment follows declared degraded/fallback behavior and does not fatal globally.

### CBP-14 — Publish authorization
Only caller with required capability + resource Policy can publish; draft edit permission alone is insufficient if separated.

### CBP-15 — Publish audit
Publish records safe Definition/revision/action/correlation metadata without serializing private render context.

### CBP-16 — Publish concurrency
Two publishers from stale revisions produce deterministic conflict/version result and no lost update.

---

## B. Control schema, instance values & validation — CBP-17…CBP-32

### CBP-17 — Scalar control
Text/number/boolean/enum control normalizes valid value and rejects wrong type server-side.

### CBP-18 — Responsive control
Responsive base/breakpoint values validate against same property/type constraints.

### CBP-19 — Dynamic-binding allowed flag
Control disallowing dynamic binding rejects forged binding descriptor even if editor UI hides option.

### CBP-20 — Required/default value
Missing optional control receives deterministic safe default; required control fails clearly.

### CBP-21 — Unknown control key
Unexpected instance field is rejected/ignored only according schema policy; no mass-assignment behavior.

### CBP-22 — Control visibility dependency
Editor visibility condition does not remove server validation for submitted hidden value.

### CBP-23 — Privacy classification
Control marked P2/P4/P3-sensitive follows storage/export/log/render rules; secret controls use references, not plaintext generic values.

### CBP-24 — URL control
URL scheme/host/target handling rejects javascript/data/unsafe scheme according destination policy.

### CBP-25 — Rich-text control
Allowed markup sanitizes deterministically and cannot inject script/event-handler/unsafe URL execution.

### CBP-26 — Media reference
Media control uses typed attachment/private-asset reference and rechecks access when source is protected.

### CBP-27 — Style value control
CSS-like property value is typed/allowlisted and cannot break out into arbitrary selector/rule/script.

### CBP-28 — Adapter extension namespace
Adapter-specific instance metadata stays namespaced and cannot override canonical control/Policy fields.

### CBP-29 — Instance fingerprint
Normalized static instance settings produce stable fingerprint used for cache/diff/diagnostics.

### CBP-30 — Instance migration
Older instance schema migrates deterministically without overwriting source builder document unexpectedly.

### CBP-31 — Unknown future instance data
Unsupported newer instance remains safely degraded/read-only and is not silently stripped on save.

### CBP-32 — Oversized instance
Deep/large control payload is bounded before expensive render/compile work.

---

## C. Dynamic bindings, context & Policy — CBP-33…CBP-48

### CBP-33 — Current entity field binding
Authorized public field resolves typed value from declared entity context.

### CBP-34 — WPE Custom Field binding
Field Storage value resolves through owner API/Policy and respects field privacy.

### CBP-35 — Site setting binding
Site/network setting uses explicit effective scope/inheritance and does not expose Vault-backed secret value.

### CBP-36 — User/profile binding
Safe profile field resolves only for authorized principal/subject/context; protected identity/security data is unavailable.

### CBP-37 — Relation binding
Related entities resolve through Relation/Query Policy and never arbitrary object lookup by forged ID.

### CBP-38 — Query aggregate binding
Query result/aggregate is bounded and authorized; renderer does not create raw SQL escape hatch.

### CBP-39 — Membership safe binding
Only safe entitlement flags/labels needed for presentation are exposed; product entitlement/provider/payment secrets remain separate.

### CBP-40 — Route/event context binding
Only registered typed context values are available; raw global request/server/session objects are not generic bindings.

### CBP-41 — Registered resolver binding
Extension resolver declares output type/context/Policy/cache sensitivity and cannot bypass KPA registry authorization.

### CBP-42 — Denied binding
Denied value resolves to declared deny/fallback behavior without leaking protected value/existence.

### CBP-43 — Missing binding source
Missing module/entity/field/query uses explicit null/fallback/error semantics; no stale cached value reuse.

### CBP-44 — Type mismatch after resolution
Resolved value incompatible with expected output/control type is rejected/normalized without unsafe coercion.

### CBP-45 — Formatter boundary
Formatter uses allowlisted typed transformations and cannot invoke arbitrary PHP/eval/template code.

### CBP-46 — Batch hydration
Multiple bindings across collection items are hydrated/batched through owner APIs to avoid obvious per-item N+1.

### CBP-47 — Principal change
Personalized binding output reauthorizes under current principal/access generation; cached prior user's value cannot leak.

### CBP-48 — Site/network context isolation
Same Blueprint rendered on Sites A/B uses explicit target scope; current blog switching cannot leak data across sites.

---

## D. Render primitives, escaping & output semantics — CBP-49…CBP-64

### CBP-49 — Text primitive
Plain text is escaped for HTML text context and cannot inject markup.

### CBP-50 — Heading primitive
Heading level/semantic element obeys allowed schema and cannot inject arbitrary tag/attributes.

### CBP-51 — Link/button primitive
Label/URL/target/rel attributes are independently validated/escaped; unsafe schemes rejected.

### CBP-52 — Image/media primitive
Source/alt/decorative semantics render safely; private media cannot become public URL through component render.

### CBP-53 — Icon primitive
Registered/safe icon source renders without arbitrary SVG/script injection.

### CBP-54 — List primitive
Repeated values escape each item; list count is bounded.

### CBP-55 — Data-table primitive
Headers/cells use semantic markup and safe escaping with bounded rows/columns.

### CBP-56 — Conditional primitive
Condition controls output only; it never substitutes for resource authorization of bound data.

### CBP-57 — Loop/repeater primitive
Loop is bounded by schema/query/runtime limit; user input cannot create unbounded recursion/execution.

### CBP-58 — Child-slot primitive
Child render reuses authorized context with explicit child scope and depth budget.

### CBP-59 — Partial/component reference
Referenced Blueprint revision resolves through Definition dependency graph and cycle detection.

### CBP-60 — Semantic wrapper
Allowed element/role/attributes are schema constrained; arbitrary inline handlers/scripts are prohibited.

### CBP-61 — SDK primitive
Registered primitive passes KPA extension/namespace/security contract; unknown primitive is explicit unsupported/degraded.

### CBP-62 — Sanitized rich HTML
Typed rich-content value is sanitized by defined profile; renderer does not trust prior editor preview sanitization alone.

### CBP-63 — Attribute/class generation
User/adapter values cannot inject quotes, event handlers, selectors or unsafe arbitrary class behavior outside allowed contract.

### CBP-64 — Render determinism
Given exact descriptor/instance/context/data generation/profile, semantic output is deterministic aside from explicitly declared nondeterministic values.

---

## E. Slots, nesting, composition, conditions & recursion — CBP-65…CBP-80

### CBP-65 — Valid child slot
Allowed child component type/category persists and renders in configured order.

### CBP-66 — Invalid child type
Disallowed child in slot is rejected at validation/render boundary; no silent arbitrary nesting.

### CBP-67 — Min children
Container below required minimum cannot publish/save valid instance.

### CBP-68 — Max children
Oversized child count is bounded/rejected before expensive recursion.

### CBP-69 — Direct self-cycle
Blueprint referencing itself through partial/slot dependency is detected before infinite render.

### CBP-70 — Indirect cycle
A→B→C→A cycle is detected with safe diagnostic path.

### CBP-71 — Depth limit
Deep valid acyclic composition hits configured maximum safely with degraded/error result rather than stack exhaustion.

### CBP-72 — Duplicate child identity
Host/instance child identity collision resolves explicitly and does not merge unrelated state.

### CBP-73 — Parent context narrowing
Child receives only declared context subset; parent secret/private internal object is not implicitly global.

### CBP-74 — Collection item context
Each item receives correct entity/item/index context and cannot reuse prior item authorization/value.

### CBP-75 — Empty collection
Empty-state component renders according declared contract without issuing hidden fallback query.

### CBP-76 — Loading state
Client-enhanced loading state does not expose private placeholder data or claim result before authorized server response.

### CBP-77 — Error state
Error component receives safe normalized ERR metadata only, not raw exception/private payload.

### CBP-78 — Visibility condition
Instance condition uses typed safe values; hidden component is not treated as access-control enforcement for underlying resource.

### CBP-79 — Conditional child dependency
Conditional branch does not load/resolve protected child bindings/assets when branch is definitively not selected, where optimization is safe.

### CBP-80 — Composition fingerprint
Dependency/child/condition changes update descriptor/render/cache fingerprint deterministically.

---

## F. Styles, design tokens & responsive rendering — CBP-81…CBP-96

### CBP-81 — Named style target
Style applies only to declared target token and cannot inject arbitrary global selector.

### CBP-82 — Allowed property
Permitted spacing/typography/color/layout property renders through typed mapping.

### CBP-83 — Disallowed property
Unsupported/high-risk property or raw CSS rule is rejected unless separately approved developer-mode contract exists.

### CBP-84 — Numeric/unit validation
Length/value uses approved units/ranges and rejects malformed CSS expression/injection.

### CBP-85 — Color token/value
Design token or validated color maps deterministically and does not permit CSS code breakout.

### CBP-86 — Typography token
Font family/size/weight/line-height follow registered design-system/profile and avoid arbitrary remote font URL injection.

### CBP-87 — Responsive breakpoint mapping
Base/tablet/mobile/custom supported breakpoint values produce deterministic ordered output under selected profile.

### CBP-88 — Missing responsive value
Fallback/inheritance behavior is explicit and not dependent on editor implementation quirks.

### CBP-89 — Reduced-motion
Animation/motion settings honor accessibility reduced-motion policy where WPE owns output.

### CBP-90 — RTL logical properties
Spacing/alignment/direction behavior remains correct for RTL and does not hardcode LTR-only assumptions unnecessarily.

### CBP-91 — Theme inheritance
Blueprint scoped styles do not globally overwrite unrelated theme/admin/builder selectors.

### CBP-92 — Style collision isolation
Two instances/Blueprints with similarly named targets remain scoped and deterministic.

### CBP-93 — Token change invalidation
Design-token revision invalidates affected compiled/render/cache artifacts according dependency generation.

### CBP-94 — Unsupported adapter style capability
Adapter reports adapted/degraded/unsupported rather than silently dropping security/accessibility-critical style semantics.

### CBP-95 — User custom CSS boundary
Normal Blueprint control cannot become arbitrary CSS textarea/eval; any future developer CSS mode is separately threat-modeled.

### CBP-96 — Style output size budget
Large responsive/style combinations remain bounded and deduplicated enough to avoid pathological per-instance CSS growth.

---

## G. Asset graph, registration & scoped loading — CBP-97…CBP-112

### CBP-97 — Registered style handle
Declared known stylesheet loads only when component/route requires it.

### CBP-98 — Registered script/module handle
Known frontend script loads through Asset Registry with dependency/version metadata.

### CBP-99 — Unknown asset handle
Unknown descriptor does not become arbitrary URL/file include; component degrades safely.

### CBP-100 — Remote arbitrary CDN rejection
User Blueprint data cannot point directly to executable remote JS/CSS as ordinary asset definition.

### CBP-101 — Dependency ordering
Asset dependencies enqueue once in deterministic order without duplicate copies.

### CBP-102 — WordPress-host dependency reuse
Compatible WordPress-provided runtime/library is reused rather than bundling competing duplicate React/runtime.

### CBP-103 — Builder-provided dependency reuse
Adapter capability/profile may reuse compatible builder dependency without making builder globally required.

### CBP-104 — Editor-only asset isolation
Editor asset does not load on public frontend unless explicitly required by frontend contract.

### CBP-105 — Frontend-only isolation
Public enhancement asset does not pollute unrelated wp-admin pages.

### CBP-106 — Conditional asset
Asset tied to selected primitive/feature is omitted when feature is not rendered.

### CBP-107 — Duplicate component instances
Ten instances enqueue shared asset once while instance-specific data remains isolated.

### CBP-108 — Asset fingerprint/version change
Changed asset version/fingerprint invalidates appropriate browser/cache assumptions without stale descriptor mismatch.

### CBP-109 — Missing asset
Render remains safe with explicit degraded/health diagnostic; no arbitrary fallback URL.

### CBP-110 — Pro expiry deployed asset
Existing safe deployed Pro component retains required render assets according ADR-0007 while editing/unsafe management can lock.

### CBP-111 — CSP/inline script boundary
Renderer does not require user-generated inline executable script; any trusted shipped initialization uses defined CSP-compatible strategy.

### CBP-112 — Asset budget evidence
Measure requested handles/bytes/duplicate dependencies on representative pages; performance claim remains evidence-based.

---

## H. Cache, personalization, invalidation & render generations — CBP-113…CBP-128

### CBP-113 — Public static cache
Truly public descriptor+instance+context can use shared cache with explicit generation/fingerprint.

### CBP-114 — Personalized cache
User-specific/profile/membership output includes principal/access generation and cannot use public shared cache key.

### CBP-115 — Protected resource cache
Resource Policy generation/visibility is included or output remains uncached according safety profile.

### CBP-116 — Query result generation
Query/data generation change invalidates collection output without stale protected data.

### CBP-117 — Entity revision
Current entity update invalidates dependent render output.

### CBP-118 — Setting revision
Bound site/network setting change invalidates affected components according dependency tracking.

### CBP-119 — Blueprint publish
New published revision invalidates follow-current instances; pinned instances stay on pinned revision.

### CBP-120 — Instance setting change
Instance fingerprint change cannot reuse old output cache.

### CBP-121 — Locale cache key
Localized text/formatting output is isolated by locale/profile.

### CBP-122 — Site/network cache scope
Same Blueprint/instance identifiers across Sites A/B cannot share data/render cache incorrectly.

### CBP-123 — Membership revoke
Access-generation revoke invalidates protected/personalized render within certified boundary.

### CBP-124 — User logout/login switch
Authenticated personalized fragment cannot survive into next principal/session through shared page/object cache.

### CBP-125 — Stale cache backend failure
Cache outage/failure resolves to safe source render or explicit degraded state; stale authorization default allow is prohibited.

### CBP-126 — Cache stampede
Concurrent cold renders use bounded locking/coalescing or measured safe behavior; no unbounded duplicate expensive Query hydration.

### CBP-127 — Negative/error cache
Denied/missing/error output is cached only when key/lifetime cannot leak/persist wrong authority after state change.

### CBP-128 — Cache observability
Diagnostics exposes safe hit/miss/generation/dependency metrics without private rendered body.

---

## I. Accessibility, degradation, error UX & safe runtime preservation — CBP-129…CBP-144

### CBP-129 — Semantic root
Declared semantic root/role appears correctly and does not produce invalid nested interaction semantics.

### CBP-130 — Accessible name
Interactive control has valid accessible name derived from authorized/escaped content.

### CBP-131 — Keyboard activation
WPE-owned button/link/disclosure behavior is keyboard operable when enhancement exists.

### CBP-132 — Focus management
Interactive component/dialog/state transitions preserve sensible focus without trapping unexpectedly.

### CBP-133 — Live/state announcement
Loading/error/success/expanded/current state is announced only when semantically required and not noisy.

### CBP-134 — Image alt/decorative behavior
Meaningful/decorative media honors declared semantics; missing alt does not default to filename leakage blindly.

### CBP-135 — Heading hierarchy
Heading primitive/level configuration avoids invalid forced structure where Blueprint owns semantics and flags unsafe misuse.

### CBP-136 — Reduced motion
Motion enhancement respects user preference and does not hide essential content when disabled.

### CBP-137 — Missing Blueprint frontend
Public output uses safe configured fallback/empty/placeholder semantics and never raw exception/definition JSON.

### CBP-138 — Missing binding frontend
Fallback/error state follows descriptor without leaking denied/missing distinction improperly.

### CBP-139 — Authorized admin diagnostic
Admin can inspect safe missing dependency/binding/asset/error correlation according Policy.

### CBP-140 — Unsupported adapter
Core server-render fallback may be used only where contract says equivalent; otherwise explicit unsupported/degraded state.

### CBP-141 — Pro editing unavailable
Expired/revoked product entitlement locks management according policy but cannot expose protected data or destroy existing Blueprint definition.

### CBP-142 — Module disabled
Dependent component degrades without deleting Definition/instance/builder document automatically.

### CBP-143 — Error taxonomy parity
Validation/render/dependency/policy/cache errors use normalized ERR code/category and accessible persistent UX when needed.

### CBP-144 — Accessibility regression gate
Core component cannot claim certified render if essential semantic/keyboard/name/focus behavior fails even when pixels match.

---

## J. Portability, contract versioning, adapters & AI boundaries — CBP-145…CBP-160

### CBP-145 — Definition package export
Blueprint definition/revision/control/render/style/dependencies export through portable package without local numeric IDs/secrets/runtime data.

### CBP-146 — Instance portability
Portable WPE instance values/bindings retain stable references while adapter-private data stays namespaced/reportable.

### CBP-147 — Missing target adapter
Package imports canonical Blueprint even when source builder adapter absent, with explicit adapter data deferred/degraded.

### CBP-148 — Unsupported adapter metadata
Importer reports nonportable adapter extension data rather than silently deleting it during unrelated save.

### CBP-149 — Older Blueprint schema import
Supported migrator produces deterministic current in-memory/new revision semantics.

### CBP-150 — Future Blueprint schema import
Unknown future schema is inspectable/deferred and never lossy downgraded.

### CBP-151 — Clone/new identity
Cloning Blueprint rewrites internal child/dependency references intentionally while external references remain explicit.

### CBP-152 — Same UUID modified import
Semantic diff/revision/conflict flow applies; no silent overwrite.

### CBP-153 — BW adapter boundary
Core CBP certification does not grant BC certification; each builder version/edition still runs BW fixtures.

### CBP-154 — Builder canonicality regression
Builder serialization cannot become source of truth for exported Blueprint or shared renderer semantics.

### CBP-155 — AI inspect
AI Ability may inspect/explain authorized Blueprint schema/descriptor without receiving hidden P3/private values.

### CBP-156 — AI draft generation
AI may create/edit typed draft through KPA Ability/schema under authorization; arbitrary PHP/JS/CSS injection remains impossible.

### CBP-157 — AI preview/diff
Preview/diff operates on draft and cannot publish or change live deployment implicitly.

### CBP-158 — AI publish authority
Publish remains explicit capability/resource Policy/impact action; AI exposure is separate and off unless configured.

### CBP-159 — Deprecated control/primitive
Deprecated contract loads/migrates according version policy and cannot silently change rendering/security meaning.

### CBP-160 — SDK component/primitive
Third-party Blueprint/primitive registration is namespaced/versioned and registration is not certification/support truth.

---

## K. Concurrency, performance, Multisite & composite security — CBP-161…CBP-176

### CBP-161 — Concurrent render + publish
Render pins one published descriptor generation while another revision publishes; no mixed descriptor tree.

### CBP-162 — Concurrent render + instance edit
One request uses coherent instance fingerprint/version; next request sees new version after commit.

### CBP-163 — Concurrent cache invalidation
Publish/data/Policy changes racing cached render cannot indefinitely serve stale protected output.

### CBP-164 — 100 component page
Measure server time/query count/memory/assets for 100 representative components and detect N+1 binding patterns.

### CBP-165 — 1k nested/list items
Bounded collection/nesting workload records practical limits and degradation; no unlimited default.

### CBP-166 — Deep recursion attack
Malicious cyclic/deep instance fails before stack/memory exhaustion.

### CBP-167 — Large control payload attack
Oversized nested controls/style arrays are rejected/bounded before expensive compilation/render.

### CBP-168 — Repeated Query binding
Batch hydration/coalescing prevents one query per field/item when owner API supports batching.

### CBP-169 — Asset-heavy page
Measure duplicate handle elimination and frontend/editor separation across many instances.

### CBP-170 — Object cache off/on
Core semantic render/authorization is identical; cache only changes performance within defined stale-generation contract.

### CBP-171 — Multisite same IDs
Same Blueprint/instance/entity numeric IDs across sites remain isolated by explicit site/network scope and cache keys.

### CBP-172 — Network template/pinned revision
Network-shared template inheritance/pinning follows MSI/Definition Policy and does not expose shared private context.

### CBP-173 — Site lifecycle
Archive/delete/clone/transfer triggers dependency/cache/asset/runtime handling without deleting unrelated network/sibling Blueprint data.

### CBP-174 — Free↔Pro skew
Existing safe component render follows FP/ADR-0007 degraded behavior; incompatible pair cannot run unsafe newer descriptor blindly.

### CBP-175 — Error/privacy composite
Denied P4 binding + missing asset + unsupported adapter returns safe degraded output/ERR metadata and never caches/leaks protected value.

### CBP-176 — Stop-the-line composite
Inject forged binding target + recursive child + unsafe style/URL + stale public cache during wrong-site render. Any Policy bypass, P2/P4 leak, script/style injection, cross-site cache leak, unbounded recursion, arbitrary code execution or false certified-adapter claim is Critical.

---

## 5. Required evidence artifact per future run

Each executed fixture records:
- CBP fixture ID;
- WPE commit/version + Free/Pro pair;
- WordPress/PHP/DB/build versions;
- Blueprint/Revision/compiled descriptor UUID + schema/compiler versions;
- instance fingerprint/revision policy;
- actor/principal/site/network/entity/render context;
- bindings and source owner profiles using synthetic data;
- Policy/access generation;
- selected renderer/adapter profile;
- cache generation/key class;
- declared/enqueued assets;
- semantic output assertions;
- escaping/sanitization/privacy assertions;
- accessibility assertions where relevant;
- query/time/memory/asset metrics where relevant;
- ERR/Audit/correlation identifiers;
- pass/fail/blocked/skipped/not-executed;
- known degraded/unsupported behavior.

No real customer/private/secret content is used in certification fixtures.

---

## 6. MUST NOT / stop-the-line rules

Stop the line on any of the following:
- builder private document/serialization becomes canonical WPE Blueprint state;
- forged control/binding bypasses server schema or resource Policy;
- P2/P4 protected value enters public/shared cache or unauthorized output;
- P3 secret becomes generic control/binding/context/rendered value;
- arbitrary PHP/JS/eval/raw executable code becomes normal Blueprint primitive/control;
- unsafe HTML/URL/CSS/SVG injection reaches output;
- current blog or builder/editor context becomes authorization;
- recursion/repeater/Query hydration is unbounded enough for trivial resource exhaustion;
- adapter registration/visual preview is presented as BC2+ certification without BW evidence;
- asset declaration loads arbitrary executable remote URL or duplicate competing React/runtime;
- missing/disabled Pro/module/adapter deletes Blueprint/instance content or weakens Membership/security;
- cache invalidation failure keeps revoked protected output available beyond defined security boundary;
- accessibility-critical semantics are lost while core renderer is still called certified.

---

## 7. Current evidence state

- Protocol documented: **CBP-01…CBP-176**.
- Executed: **0/176**.
- `CBP-D/C/B/R/S/A/K/X/U/O` certifications: **0**.
- concrete compiled descriptor schema: **NOT IMPLEMENTED / exact representation OPEN**.
- concrete shared renderer implementation: **NOT IMPLEMENTED**.
- concrete Asset Registry/manifest integration: **NOT IMPLEMENTED**.
- exact cache backend/key/locking profile: **OPEN / evidence-gated**.
- core runtime/performance/accessibility/Multisite certification: **0**.
- Builder adapters remain **BW 0/50; BC runtime certifications 0**.

## 8. Development gate

This protocol authorizes **no executable work**.

Do not implement/execute Blueprint compiler/renderer/control schemas/binding resolvers/assets/caches, register blocks/widgets/builders, run Query/provider calls, create Multisite fixtures, run browser/accessibility tests or benchmarks until explicit owner development/executable-evidence consent is recorded under ADR-0014 and the Approval Ledger.
