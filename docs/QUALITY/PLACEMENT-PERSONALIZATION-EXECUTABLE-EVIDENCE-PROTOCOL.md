# WPEssential — Placement & Personalization Executable Evidence Protocol

Status: **Accepted evidence design candidate / execution pending / no development authorization**  
Date: **2026-08-29**

Namespace: **PLC-001…PLC-176**  
Documented: **176/176**  
Executed: **0/176**  
Runtime certification: **0**

## 1. Purpose

This protocol fixes the executable evidence required before F07 — Experience Placement & Personalization Manager can be called runtime-ready.

F07 owns **where**, **when** and **for which eligible presentation context** an approved Component Blueprint may be considered for rendering. It does not become authorization, entitlement, source-data truth, consent authority, experiment statistics authority, commerce/order truth, payment truth or arbitrary browser-code execution merely because a component is selected for a slot.

No fixture below has executed. No placement registry, personalization evaluator, browser render, asset enqueue, cache mutation, experiment exposure, builder/theme/WooCommerce adapter call, provider call, AI/MCP call, benchmark, build or runtime mutation is authorized by this protocol.

## 2. Non-negotiable truth boundaries

- `Placement decision ≠ authorization`.
- `Audience match ≠ role/capability/membership entitlement`.
- `Hidden/not selected component ≠ denied underlying capability`; authorization is enforced by the owning Policy at the resource/action boundary.
- `Selected component ≠ successfully rendered/exposed component`.
- `Experiment assignment ≠ exposure`; exposure requires the configured render/view semantics and is owned with F08/F02 analytics contracts.
- `Experiment assignment ≠ consent`.
- `Personalization context ≠ permission to collect/use protected attributes`; consent, privacy and Policy remain authoritative.
- `Frequency-cap identity ≠ authentication identity authority`; anonymous/session/user stitching follows approved identity rules.
- `Cached personalized markup ≠ universally reusable markup`; every personalization/Policy/tenant dimension must be represented or shared caching must be disabled.
- `Slot adapter ≠ arbitrary DOM/code injection`; adapters expose bounded, certified placement contracts.
- `Component Blueprint rendering ≠ source-data authorization`; referenced data is reauthorized by the owning Query/Policy/resource layer.
- `Priority/score/rank ≠ authorization`; F04 may supply derived ranking inputs only.
- `Schedule active ≠ eligible`; audience, Policy, consent, slot compatibility and conflict rules still apply.
- `Dismissal/preference ≠ permanent account consent` unless the owning preference/consent profile explicitly says so.
- Multisite/site/tenant ownership is server-resolved; request-supplied site IDs do not grant placement or audience access.
- AI/MCP may draft, explain and validate placement definitions only through the same Policy/revision/approval boundaries; it cannot create a hidden privileged placement path.

## 3. Certification classes

- `PLC-SLT` — placement/slot registry and adapter discovery.
- `PLC-AUD` — audience/context resolution and eligibility.
- `PLC-PRI` — priority/conflict/stacking/fallback.
- `PLC-FRQ` — frequency caps and identity scope.
- `PLC-SCH` — scheduling/timezone/campaign lifecycle.
- `PLC-CMP` — Component Blueprint rendering and data Policy.
- `PLC-AST` — asset loading/scoped chunks/performance.
- `PLC-CAC` — cache identity/invalidation/leak defense.
- `PLC-UXA` — accessibility/responsive/dismissal/preferences.
- `PLC-PRV` — consent/dark-pattern/privacy/PII boundaries.
- `PLC-EXP` — experiment binding and exposure logging.
- `PLC-ADP` — theme/builder/Woo/domain adapter conflict semantics.
- `PLC-MSI` — Multisite/template/site override isolation.
- `PLC-LCY` — lifecycle/expiry/disabled-component behavior.
- `PLC-PERF` — many-placement/high-traffic performance.
- `PLC-DET` — deterministic end-to-end golden/regression scenarios.

Passing one class never implies another. Runtime readiness requires every class applicable to the enabled placement profile and adapter combination.

# Group 1 — Placement/slot registry and adapter discovery — PLC-001…PLC-011

- **PLC-001** Valid slot definition publishes with stable slot key, owning adapter, surface type, lifecycle state and immutable revision identity.
- **PLC-002** Duplicate slot stable key within the same certified ownership scope is rejected or explicitly version-migrated rather than silently shadowed.
- **PLC-003** Slot schema declares supported component families, multiplicity, ordering model, allowed rendering modes and context contract.
- **PLC-004** Unknown slot/adapter schema version is treated as incompatible and never coerced to a generic unsafe placement.
- **PLC-005** Adapter discovery distinguishes available, inactive, unsupported-version, degraded and uncertified adapters with actionable diagnostics.
- **PLC-006** Missing required theme/builder/domain adapter prevents activation of dependent placement definitions without deleting their configuration.
- **PLC-007** Slot aliases/deprecations preserve stable migration provenance and never silently repoint to an unrelated surface.
- **PLC-008** Dynamic slot discovery is bounded to registered adapter contracts and cannot accept arbitrary selector/code payloads as a new trusted slot.
- **PLC-009** Slot export/import preserves stable IDs, adapter requirements and revision metadata without inventing availability on the destination environment.
- **PLC-010** Deleting/archiving a slot performs dependency review and leaves dependent placements disabled/degraded rather than silently relocating them.
- **PLC-011** Slot capability matrix rejects unsupported stacking, interactivity, render mode or asset behavior before publish.

# Group 2 — Audience/context resolution and eligibility — PLC-012…PLC-022

- **PLC-012** Audience definition declares explicit typed inputs such as route, entity, locale, device class, authenticated state and approved segment references.
- **PLC-013** Eligibility evaluation distinguishes `eligible`, `ineligible`, `unknown/unavailable` and `denied` where protected context cannot be read.
- **PLC-014** Anonymous visitor rules cannot infer authenticated user capabilities, membership or entitlements from client-supplied claims.
- **PLC-015** Authenticated audience evaluation resolves user/site/tenant context server-side and rejects forged actor or tenant identifiers.
- **PLC-016** Role/capability/membership references are treated as inputs from their canonical owners; audience match never becomes authorization.
- **PLC-017** Query/entity attributes used for personalization are Policy-filtered before evaluation and protected values are not leaked in explanation traces.
- **PLC-018** Missing optional audience attribute follows its configured missing-value policy instead of being silently coerced to false/zero/empty.
- **PLC-019** Locale/device/referrer/campaign inputs are normalized through registered typed vocabularies and bounded lengths.
- **PLC-020** Segment/audience revision pinning produces deterministic evaluation for a published placement until an explicit new revision is published.
- **PLC-021** Contradictory include/exclude rules surface diagnostics and resolve through declared precedence rather than rule-order accident.
- **PLC-022** AI-generated audience suggestions remain Draft and cannot introduce protected/sensitive targeting attributes outside configured governance.

# Group 3 — Priority/conflict/stacking/fallback — PLC-023…PLC-033

- **PLC-023** Competing eligible placements in a single-winner slot resolve through explicit deterministic priority and tie-break rules.
- **PLC-024** Equal priority without a configured deterministic tie-break fails publish or uses the registered stable fallback; database row order is never authority.
- **PLC-025** Multi-item/stacking slot applies declared max-item count and ordering without rendering extra eligible components beyond the cap.
- **PLC-026** Exclusive placement prevents mutually exclusive peers from rendering in the same decision scope.
- **PLC-027** F04 score/rank may order already eligible candidates but cannot make an otherwise unauthorized/ineligible component eligible.
- **PLC-028** Fallback component is evaluated against its own Policy, consent, lifecycle and compatibility requirements rather than bypassing failed primary checks.
- **PLC-029** Fallback chain has cycle detection and bounded depth so A→B→A cannot create recursion or repeated render attempts.
- **PLC-030** Manual pin/override requires a separate governed capability and cannot override protected-resource Policy.
- **PLC-031** Conflict trace explains candidate rejection/selection without exposing protected audience facts to unauthorized viewers.
- **PLC-032** Multiple placements targeting overlapping nested slots do not duplicate the same single-instance component unless explicitly allowed.
- **PLC-033** Deterministic golden candidate set produces the same selected/ordered placements across certified runtimes and cache states.

# Group 4 — Frequency caps/session/user identity — PLC-034…PLC-044

- **PLC-034** Frequency cap declares scope (`request`, `page`, `session`, `anonymous-id`, `authenticated-user`, `campaign`) and rolling/calendar window semantics.
- **PLC-035** Session cap counts only events defined by the selected cap mode (attempt, render, view/exposure or dismissal) and does not mix event types.
- **PLC-036** Anonymous identifier rotation follows approved privacy/session policy and does not create a durable cross-device identity by default.
- **PLC-037** Login identity stitching merges or separates anonymous frequency history according to explicit policy without double-counting the same exposure.
- **PLC-038** Logout prevents protected user-scoped cap/personalization state from leaking into the next anonymous or different-user session.
- **PLC-039** Concurrent requests cannot exceed a strict user/session cap when the profile claims atomic cap enforcement.
- **PLC-040** Cap storage outage distinguishes `cap state unavailable` from `cap count = 0` and follows configured fail-open/fail-closed presentation policy.
- **PLC-041** Time-window boundary uses canonical time/timezone semantics and handles clock skew within the declared tolerance.
- **PLC-042** Dismissal-based suppression has explicit duration/scope and preserves the user’s deliberate choice across eligible pages as configured.
- **PLC-043** Manual/admin reset of frequency state requires an authorized action and produces audit evidence without rewriting analytics history.
- **PLC-044** Frequency-cap data export/erase follows privacy rules while preserving only the minimum non-identifying aggregate evidence allowed by policy.

# Group 5 — Schedule/timezone/campaign lifecycle — PLC-045…PLC-055

- **PLC-045** Placement schedule stores explicit timezone, start/end instants and inclusive/exclusive boundary semantics.
- **PLC-046** Local-time schedule across DST gap/fold resolves deterministically and never silently shifts to a different unintended hour.
- **PLC-047** Start-before-end, valid recurrence and bounded schedule horizon are validated before publish.
- **PLC-048** Campaign pause stops new eligible selections at the declared effective boundary while preserving historical exposure records.
- **PLC-049** Scheduled placement remains ineligible before start and after expiry even if cached markup exists.
- **PLC-050** Overlapping campaign schedules resolve with the same registered conflict/priority semantics as unscheduled candidates.
- **PLC-051** Holiday/business-calendar reference, when supported, is revisioned/provenanced and `calendar unavailable` is not treated as an empty holiday set.
- **PLC-052** Recurring schedule expansion is bounded and resists pathological recurrence definitions/CPU amplification.
- **PLC-053** Schedule edit creates a new revision/effective plan and does not retroactively rewrite historical exposure timestamps.
- **PLC-054** Site timezone change does not reinterpret previously stored canonical instants or already-recorded exposure history.
- **PLC-055** Import to a different timezone requires explicit review of local-time schedules rather than silent destination-site reinterpretation.

# Group 6 — Component Blueprint rendering/data Policy — PLC-056…PLC-066

- **PLC-056** Placement references an immutable/published Component Blueprint revision or an explicitly declared compatible revision range.
- **PLC-057** Missing/archived/incompatible component revision degrades the placement safely and invokes only governed fallback behavior.
- **PLC-058** Dynamic component data is fetched through canonical Query/Data Source/Policy owners and is reauthorized at render time.
- **PLC-059** Personalization tokens are typed/escaped by output context and cannot inject arbitrary HTML/script/URL protocols where not explicitly supported.
- **PLC-060** Protected field denied by Policy is omitted/redacted/placeholdered according to component contract and never exposed by debug markup.
- **PLC-061** Component rendering distinguishes empty valid data, denied data, source unavailable and render error so fallback behavior is accurate.
- **PLC-062** Component action controls/forms/links enforce their own server-side authorization; visual placement never grants an action capability.
- **PLC-063** Component nested-placement depth is bounded with cycle detection to prevent recursive slot/component loops.
- **PLC-064** Server-rendered and client-hydrated variants preserve equivalent Policy/data-redaction semantics for the same fixture.
- **PLC-065** Preview/test mode clearly labels non-production context and does not create production exposure/frequency events unless explicitly requested and authorized.
- **PLC-066** AI-generated component personalization copy/data bindings cannot introduce unregistered data sources, secret references or privileged actions.

# Group 7 — Asset loading/scoped chunks/performance — PLC-067…PLC-077

- **PLC-067** Placement/component asset manifest declares CSS/JS/media dependencies with stable handles, versions and loading conditions.
- **PLC-068** Assets for an ineligible/unselected component are not loaded when the profile claims conditional loading.
- **PLC-069** Shared dependency is de-duplicated across multiple selected components without changing execution order guarantees.
- **PLC-070** Asset handle/version collision across components/adapters is detected rather than silently substituting unrelated code.
- **PLC-071** Scoped CSS contract prevents one placement’s styles from unintentionally overriding unrelated site/admin surfaces within declared isolation limits.
- **PLC-072** Client bundle/chunk failure leaves the page usable and records degraded render state without falsely logging a completed interactive exposure.
- **PLC-073** Lazy-loaded media/assets preserve accessibility dimensions/alternatives and avoid layout shift beyond declared budgets.
- **PLC-074** Third-party asset origin, CSP/integrity requirements and consent category are validated before load where applicable.
- **PLC-075** Asset cache bust/versioning follows component revision so a new published component cannot execute stale incompatible client code indefinitely.
- **PLC-076** Placement asset pipeline does not accept arbitrary PHP/server executable code; server logic remains Extension SDK/VCS/release territory.
- **PLC-077** Performance evidence records placement decision, render and asset cost separately so network/render bottlenecks are not misattributed.

# Group 8 — Cache key/invalidation/personalized leakage — PLC-078…PLC-088

- **PLC-078** Cache key includes every approved dimension that can materially change placement selection or rendered protected content.
- **PLC-079** User-specific/protected placement output is never served from a shared public cache unless the profile proves an equivalent safe segmentation model.
- **PLC-080** Anonymous audience cache segmentation cannot be promoted to authenticated-user output without re-evaluation/re-authorization.
- **PLC-081** Login/logout invalidates or bypasses stale personalized cache entries so one user’s component/data cannot appear to another.
- **PLC-082** Consent change invalidates affected personalized/third-party placements before subsequent eligible render.
- **PLC-083** Placement, audience, component, Policy, schedule and adapter revision changes invalidate dependent cache generations deterministically.
- **PLC-084** Site/tenant/network identity is part of cache ownership; identical slugs across sites cannot share personalized output accidentally.
- **PLC-085** Cache stampede protection does not turn an expired/stale protected entry into an indefinitely reusable authorization result.
- **PLC-086** Stale-while-revalidate, if supported, is disabled for contexts whose current Policy/consent cannot safely tolerate stale output.
- **PLC-087** Cache backend outage follows an explicit no-cache/degraded policy and never treats missing cache state as universal eligibility.
- **PLC-088** Adversarial cache-poison fixture with forged audience/context headers cannot persist protected personalized output for other users.

# Group 9 — Accessibility/responsive/dismissal/preferences — PLC-089…PLC-099

- **PLC-089** Modal/banner/popup placement satisfies declared keyboard navigation, focus order and focus-restoration requirements.
- **PLC-090** Dismissible overlay exposes an accessible close control and does not trap focus after dismissal.
- **PLC-091** Placement respects reduced-motion/system accessibility preferences where animation is optional.
- **PLC-092** Responsive eligibility/layout rules use registered breakpoints/context and do not remove critical server-authorized functionality solely by CSS hiding.
- **PLC-093** Reflow/zoom fixtures preserve actionable controls and readable content at certified accessibility zoom/viewport profiles.
- **PLC-094** Screen-reader semantics identify dialog/banner/region purpose and avoid duplicate landmark/label collisions in stacked placements.
- **PLC-095** Dismissal persists at the configured scope/duration and does not reappear on every navigation due to client/server state divergence.
- **PLC-096** User preference such as `hide promotions` or `compact notices` is resolved through its canonical preference owner and cannot be overwritten by campaign priority.
- **PLC-097** Required legal/security/system notice class, if exempt from ordinary dismissal, is explicitly governed and cannot be mislabeled as marketing to bypass preferences.
- **PLC-098** Personalization does not create a dark-pattern interaction that makes reject/dismiss materially harder than accept when consent rules require symmetry.
- **PLC-099** Accessibility validation failure can block publish for configured high-impact placement types without deleting the draft definition.

# Group 10 — Consent/dark-pattern/privacy/PII boundaries — PLC-100…PLC-110

- **PLC-100** Placement declares the consent/privacy categories required for audience evaluation, third-party assets and tracking separately.
- **PLC-101** Missing/revoked consent prevents the affected targeting/third-party behavior rather than being interpreted as implicit approval.
- **PLC-102** Necessary/non-optional placement classification requires explicit governance rationale and cannot be chosen merely to bypass consent.
- **PLC-103** Sensitive/protected attributes are prohibited from targeting unless an explicitly accepted lawful/Policy profile permits the exact use case.
- **PLC-104** Audience explanation/debug output redacts protected attributes and inferred sensitive segments from unauthorized operators.
- **PLC-105** Client-side context cannot smuggle unrestricted raw PII into placement rules, analytics properties or cache keys.
- **PLC-106** Privacy erase/anonymize operation removes eligible frequency/personalization linkage according to retention policy without falsifying aggregate historical counts.
- **PLC-107** Consent withdrawal while a page is open stops future affected loads/events according to the certified client lifecycle and does not continue third-party initialization blindly.
- **PLC-108** Geo/age/device/referrer targeting, where used, records source/provenance/precision and respects data-minimization settings.
- **PLC-109** A/B personalization UI cannot use deceptive preselection, forced continuity or misleading urgency patterns prohibited by the configured UX governance profile.
- **PLC-110** AI/MCP personalization cannot infer or target prohibited sensitive traits from unrelated data even when technically derivable.

# Group 11 — Experiment binding/exposure logging — PLC-111…PLC-121

- **PLC-111** Placement may bind to a published F08 experiment/rollout revision but does not copy/private-fork F08 assignment logic.
- **PLC-112** Deterministic assignment result is consumed as typed input and does not bypass placement audience/Policy/consent checks.
- **PLC-113** Assignment without actual qualifying render/view is not recorded as exposure when the experiment profile defines exposure-at-render/view.
- **PLC-114** Repeated render of the same placement respects experiment exposure dedupe semantics and frequency-cap event definitions independently.
- **PLC-115** Anonymous-to-authenticated identity transition follows F08/F02 stitching rules and cannot create silent cross-user experiment contamination.
- **PLC-116** Experiment pause/kill switch prevents new experimental variant selection while declared fallback/control behavior remains deterministic.
- **PLC-117** Variant component missing/degraded is recorded as delivery failure/fallback rather than a successful exposure to the unavailable variant.
- **PLC-118** Exposure event contains experiment/variant/placement/component revision identity needed for later attribution without embedding secrets/protected payloads.
- **PLC-119** Analytics/event pipeline outage distinguishes `exposure logging unavailable` from `not exposed`; retry/dedupe follows F02/F08 contracts.
- **PLC-120** Placement preview/test traffic is excluded from production experiment metrics unless explicitly configured as test traffic.
- **PLC-121** Placement cannot declare experiment winner/causal result; statistical interpretation remains owned by F08/analytics evidence.

# Group 12 — Theme/builder/Woo/domain adapter conflicts — PLC-122…PLC-132

- **PLC-122** Theme adapter exposes named certified slots with version/capability metadata instead of unrestricted arbitrary selectors.
- **PLC-123** Builder adapter detects template/slot disappearance or rename and marks dependent placements degraded rather than injecting into the nearest DOM match.
- **PLC-124** Multiple adapters claiming the same effective surface resolve through explicit adapter ownership/priority and diagnostics.
- **PLC-125** WooCommerce/domain adapter placement checks required domain context (product/cart/checkout/account etc.) before candidate evaluation.
- **PLC-126** Commerce placement never mutates cart/order/payment/inventory merely by rendering; actions delegate to canonical commerce owners with server-side Policy.
- **PLC-127** Checkout/account protected context is not exposed to a placement/component unless the adapter contract and source Policy allow the exact fields.
- **PLC-128** Theme/builder upgrade compatibility mismatch disables unsafe placement execution until recertified or explicitly mapped.
- **PLC-129** Duplicate hook firing by theme/plugin integration is deduplicated according to slot instance identity so single-instance placements do not render twice.
- **PLC-130** Nested builder templates preserve stable slot instance scope and do not leak parent-page audience/cache context into unrelated embedded sites/tenants.
- **PLC-131** Adapter error/exception is isolated from page-wide failure where the certified integration contract claims graceful degradation.
- **PLC-132** Safe Script/Tag remains a separate browser-side owner; F07 placement configuration cannot become an arbitrary script/PHP injection backdoor.

# Group 13 — Multisite/template/site override — PLC-133…PLC-143

- **PLC-133** Placement/slot ownership stores explicit network/template/site scope with server-resolved site identity.
- **PLC-134** Network template placement may be inherited only by sites allowed by its rollout/Policy profile.
- **PLC-135** Site override can replace, extend or disable inherited placement only according to declared override semantics.
- **PLC-136** Site user with local placement rights cannot edit network-enforced definition outside delegated capability scope.
- **PLC-137** Network administrator cannot infer protected site audience values merely because the placement definition is network-managed.
- **PLC-138** Cross-site shared component still reauthorizes its data source in the destination site/tenant context.
- **PLC-139** Same user account across sites does not imply shared frequency/personalization state unless an explicit network identity profile permits it.
- **PLC-140** Cache, dismissal, experiment and exposure identifiers include required site/network scope to prevent cross-site contamination.
- **PLC-141** New-site provisioning applies template placements as Draft/active according to rollout policy without silently overwriting site-owned definitions.
- **PLC-142** Site archive/delete lifecycle disables placement execution and preserves/exports required configuration/evidence per retention policy.
- **PLC-143** Site domain/path change does not change stable placement identity or reinterpret historical route/exposure evidence incorrectly.

# Group 14 — Lifecycle/expiry/disabled-component behavior — PLC-144…PLC-154

- **PLC-144** Draft placement never participates in production selection.
- **PLC-145** Published→paused transition stops new selection while preserving definition/revision/exposure history.
- **PLC-146** Archived placement remains inspectable/exportable subject to Policy but cannot execute.
- **PLC-147** Component disabled after placement publication causes deterministic degraded/fallback behavior rather than rendering stale unsafe markup indefinitely.
- **PLC-148** Audience/Policy dependency disabled or unavailable yields configured `unknown/degraded` behavior and never universal eligibility by default.
- **PLC-149** Pro/module expiry follows the documented safe read-only/degraded contract and does not silently delete placement definitions or expose protected fallback content.
- **PLC-150** Duplicate placement creates a new stable identity in Draft and does not copy historical exposure/frequency identity as if it were the original.
- **PLC-151** Revision rollback republishes an explicit prior-compatible revision while preserving later revision history and current dependency validation.
- **PLC-152** Import/update conflict with locally modified placement uses bind/fork/skip/diff semantics and never overwrites silently.
- **PLC-153** Uninstall/disable plan identifies dependent slots/components/experiments/assets and the resulting fallback/degraded behavior before action.
- **PLC-154** Restored historical placement configuration is revalidated against current Policy/adapters/consent before reactivation.

# Group 15 — Many-placement/high-traffic performance — PLC-155…PLC-165

- **PLC-155** 10K placement-definition catalog lookup remains within the future declared admin/runtime discovery budget using measured evidence.
- **PLC-156** 100K placement-definition catalog filtering/pagination does not require loading every definition into process memory.
- **PLC-157** 1M historical placement/exposure-related records remain queryable through bounded indexed/paginated profiles where F07 owns the read model.
- **PLC-158** Hot page with 100 eligible candidate placements evaluates within the future declared decision CPU/latency budget.
- **PLC-159** High-traffic anonymous page cache profile demonstrates no cross-segment/consent/site leakage under concurrency.
- **PLC-160** High-traffic authenticated personalized page demonstrates bounded cache cardinality or safe cache bypass without unbounded per-user object growth.
- **PLC-161** Concurrent strict frequency-cap updates meet the declared contention/error budget without systematic over-delivery.
- **PLC-162** Conditional asset loading with many placements avoids N+1 manifest/data-source resolution beyond declared budgets.
- **PLC-163** Large audience/segment dependency graph has bounded evaluation depth and detects cycles/excessive fan-out.
- **PLC-164** Placement logging/analytics backpressure cannot block critical page rendering indefinitely; degraded telemetry behavior is explicit.
- **PLC-165** Performance certification records dataset, hardware/runtime, cache state, concurrency, percentile latency, memory, error/leak checks and cannot be satisfied by documentation alone.

# Group 16 — End-to-end popup/banner/portal/cart placement regression — PLC-166…PLC-176

- **PLC-166** Public announcement banner golden profile: route/locale audience, schedule, dismissal, accessibility, cache and analytics semantics remain deterministic.
- **PLC-167** Authenticated portal notice golden profile: Policy-filtered data and per-user cache/frequency state never leak to another account.
- **PLC-168** Marketing popup golden profile: consent, frequency cap, dismissal, mobile accessibility and campaign expiry all agree across server/client lifecycle.
- **PLC-169** WooCommerce product/cart placement golden profile renders eligible recommendations/notice without becoming cart/order/payment/inventory mutation authority.
- **PLC-170** Experiment-bound hero/banner golden profile distinguishes assignment, selected component, qualifying exposure and analytics delivery state.
- **PLC-171** Builder/theme slot regression survives certified adapter template revision or fails closed/degraded with actionable slot-mapping diagnostics.
- **PLC-172** Multisite network-template placement golden profile preserves site overrides, tenant data Policy, cache scope and local frequency identity.
- **PLC-173** Consent withdrawal + login/logout + cache adversarial sequence produces no protected personalized markup or third-party asset leakage.
- **PLC-174** Concurrent cap/priority/cache adversarial run never exceeds strict cap, renders an ineligible protected component or cross-pollinates site/user output.
- **PLC-175** Backup/restore/clone golden scenario preserves stable definitions but revalidates environment-specific adapters, caches, identity/frequency stores and experiment bindings before activation.
- **PLC-176** AI/MCP adversarial regression rejects instructions to target prohibited sensitive traits, bypass consent/Policy, inject arbitrary code, falsify exposure, force hidden privileged placement or call runtime/provider actions without separate authorization.

## 4. Mandatory evidence artifact per future execution

Every executed PLC fixture must record, as applicable:

- fixture ID and protocol revision;
- implementation/runtime version and exact commit;
- WordPress/PHP/database/browser/adapter versions relevant to the fixture;
- site/network/tenant and actor class using non-secret identifiers;
- slot/placement/audience/component/Policy/experiment revision IDs;
- normalized eligibility inputs with protected values redacted;
- selected/rejected candidates and deterministic reason codes;
- cache/frequency/dismissal/consent state before and after;
- render/asset/exposure outcome distinction;
- concurrency/timing/fault-injection parameters where applicable;
- measured latency/memory/query/cache/error metrics for performance fixtures;
- expected vs actual result;
- logs/checksums/traces/screenshots only where safe and necessary;
- PASS/FAIL/NOT-APPLICABLE with reviewer and timestamp.

A fixture is not `executed` merely because its expected result is written in documentation.

## 5. Completion truth

This document freezes the detailed **PLC-001…PLC-176** evidence contract for WP69.

Current state after this planning document:

- documented: **176/176**;
- executed: **0/176**;
- runtime certification: **0**;
- F07 implementation authorization: **not granted**;
- global product implementation authorization: **0/56**.

The next governance step is an ADR accepting this protocol as the canonical detailed F07 evidence specification. That ADR remains a planning decision and does not authorize development.