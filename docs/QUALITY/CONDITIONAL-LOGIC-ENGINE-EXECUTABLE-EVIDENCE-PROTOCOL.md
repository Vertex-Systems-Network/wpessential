# WPEssential — Conditional Logic Engine Executable Evidence Protocol

Status: **Phase 0 evidence specification / EXECUTION NOT AUTHORIZED**  
Date: 2026-08-28  
Work package: `P0-M00-WP34`  
Related: ADR-0014, ADR-0035, ADR-0082, ADR-0131, ADR-0137, ADR-0143, ADR-0144, ADR-0145, ADR-0147, ADR-0149, Forms, Workflow, Admin Columns, Dashboard Widgets, Admin Menu, Notifications, Component Blueprint, Data Source Registry.

## 1. Purpose

Freeze the future executable evidence required for WPEssential's shared **Conditional Logic Engine** before any implementation is treated as platform-certified.

The protocol freezes **CLG-01…CLG-176**.

Current execution truth: **0/176 executed**.

No Conditional Logic Engine runtime certification exists.

Consumer protocols test condition behavior inside their own modules. This protocol verifies the shared engine itself: typed condition representation, operator semantics, value/context resolution, null/missing truth, authorization/privacy, dependency/cycle limits, cross-consumer parity, deterministic evaluation, cache/version behavior, failure normalization, Multisite and scale.

No PHP/JS engine implementation, WordPress runtime execution, query/provider call, benchmark or data mutation is authorized by this document.

---

## 2. Canonical truth boundaries

Keep distinct:

`Condition Definition ≠ published revision ≠ compiled predicate ≠ value resolver ≠ authorized context ≠ evaluation input ≠ evaluation result ≠ explanation trace ≠ consumer action`

Also:
- a visible field/value does not automatically make it condition-readable;
- condition result `true` does not authorize the consumer action;
- UI visibility condition is not server authorization;
- missing, null, empty, zero and false are not interchangeable;
- a Query returning rows is not equivalent to a boolean condition unless an explicit operator defines the conversion;
- dynamic value resolution is not permission to call arbitrary code/provider endpoints;
- cached condition output is not reusable across principals/scopes unless all dependencies permit it;
- current blog/request context is not durable target ownership;
- consumer-specific semantics do not silently fork shared operator meaning.

---

## 3. Canonical condition model

A shared condition descriptor records applicable fields:
- stable definition/revision identity;
- logical group/node type;
- typed left operand/source;
- typed operator;
- typed right operand/value/source where applicable;
- null/missing behavior;
- coercion policy, normally none beyond declared safe normalization;
- context requirements;
- dependency identities/generations;
- Policy/privacy classification;
- deterministic/non-deterministic marker;
- cacheability dependencies;
- version/schema metadata;
- consumer compatibility profile;
- safe explanation/diagnostic metadata.

Normal conditions never execute arbitrary PHP, JavaScript, shell, raw SQL or unrestricted callbacks.

---

## 4. Independent certification classes

- `CLG-D` — definition/schema/revision/compiler;
- `CLG-T` — type/operator/null semantics;
- `CLG-V` — value/context resolution;
- `CLG-P` — Policy/privacy/inference safety;
- `CLG-B` — boolean groups/order/determinism;
- `CLG-G` — dependency graph/cycle/budget safety;
- `CLG-C` — consumer parity and action separation;
- `CLG-X` — dynamic/time/remote/integration boundaries;
- `CLG-K` — cache/version/invalidation;
- `CLG-F` — failures/concurrency/observability;
- `CLG-O` — Multisite/scale/adversarial/release regression.

Passing one class never certifies another.

---

# 5. Fixed executable fixture matrix

## A. Definition, schema, revision and compiler — CLG-01…CLG-16

- **CLG-01** — valid Draft condition tree receives stable identity and schema version.
- **CLG-02** — publish produces immutable revision; live consumer pins published revision.
- **CLG-03** — editing Draft after publish does not mutate already referenced revision.
- **CLG-04** — new publish creates explicit consumer/dependency transition rather than silent mutation.
- **CLG-05** — unknown node type fails validation before evaluation.
- **CLG-06** — unknown required future schema fails safe/read-only rather than dropping semantics.
- **CLG-07** — duplicate node identity inside one revision is rejected when identity must be unique.
- **CLG-08** — condition compiler is deterministic for identical revision/context capability profile.
- **CLG-09** — compiler output records exact engine/profile/schema version.
- **CLG-10** — unsupported operator/source combination fails publish/compile explicitly.
- **CLG-11** — arbitrary callback/PHP/JS/raw-SQL node is rejected.
- **CLG-12** — extension operator requires registered namespaced descriptor and compatible version.
- **CLG-13** — third party cannot claim reserved first-party condition/operator namespace.
- **CLG-14** — disabled/deprecated operator follows version/deprecation policy, not silent remap.
- **CLG-15** — definition inspection is side-effect free and does not resolve remote/runtime values.
- **CLG-16** — large valid tree compile remains bounded and records compile cost without evaluation.

## B. Types, operators, null/missing/empty semantics — CLG-17…CLG-32

- **CLG-17** — boolean equality preserves canonical true/false without string truthiness.
- **CLG-18** — integer/decimal comparison uses canonical numeric semantics, not locale-formatted text.
- **CLG-19** — string equality and case behavior follow declared operator profile.
- **CLG-20** — date comparison stays date-only and is not shifted by timezone.
- **CLG-21** — datetime comparison uses declared instant/timezone semantics.
- **CLG-22** — enum choice compares canonical value, not display label.
- **CLG-23** — list contains/contains-all/contains-any semantics are distinct and typed.
- **CLG-24** — missing is distinguishable from explicit null.
- **CLG-25** — null is distinguishable from empty string.
- **CLG-26** — zero is distinguishable from false and empty.
- **CLG-27** — empty list/document has explicit semantics distinct from missing/null.
- **CLG-28** — incompatible operand types are rejected rather than loosely coerced.
- **CLG-29** — IN/NOT IN over typed sets preserves null/missing semantics.
- **CLG-30** — range/between validates endpoints/order/type.
- **CLG-31** — regex/pattern operator, if supported, has safe bounded engine/profile and rejects catastrophic patterns.
- **CLG-32** — unknown/NaN/non-finite or malformed typed value yields explicit invalid/unknown result, not accidental truth.

## C. Value sources and runtime context resolution — CLG-33…CLG-48

- **CLG-33** — static literal resolves without external side effect.
- **CLG-34** — current authenticated user source resolves server-side and cannot be caller-overridden.
- **CLG-35** — current entity source is bound to authorized target context, not arbitrary request ID.
- **CLG-36** — field source resolves through Field/Data Source contracts and exact published schema.
- **CLG-37** — relation-derived value resolves through Relations authorization and bounded traversal.
- **CLG-38** — Query-derived scalar/list/count uses exact Query revision and declared conversion operator.
- **CLG-39** — settings/site/network value resolves through explicit scope/inheritance contract.
- **CLG-40** — workflow/form runtime variable source is typed and bound to exact run/submission context.
- **CLG-41** — request/URL parameter source is allowed only when explicitly declared and validated.
- **CLG-42** — environment/runtime capability source cannot expose secrets/internal paths.
- **CLG-43** — missing source dependency yields explicit unresolved/degraded state.
- **CLG-44** — resolver exception is normalized; it does not silently evaluate as true.
- **CLG-45** — resolver timeout/unknown outcome remains unknown/retryable according source class.
- **CLG-46** — source marked non-deterministic/time-sensitive disables unsafe long-lived result caching.
- **CLG-47** — value resolver cannot mutate source data during normal read evaluation.
- **CLG-48** — repeated same-context resolution can be request-batched without changing semantic result.

## D. Authorization, privacy and inference safety — CLG-49…CLG-64

- **CLG-49** — unauthenticated actor cannot resolve protected operand merely because condition is attached to public UI.
- **CLG-50** — actor with consumer access but no operand-field access receives safe denied/unknown behavior.
- **CLG-51** — row/resource Policy applies before protected value comparison.
- **CLG-52** — field/projection Policy applies independently from row visibility.
- **CLG-53** — condition explanation does not reveal hidden operand value.
- **CLG-54** — hidden user/email/meta/security value cannot be inferred through boolean oracle beyond accepted Policy semantics.
- **CLG-55** — exact count/aggregate predicate does not reveal unauthorized cohort existence.
- **CLG-56** — wrong-site entity identifier cannot be tested as existence oracle.
- **CLG-57** — secret/Vault plaintext is never a generic condition operand.
- **CLG-58** — masked/reference metadata cannot be converted into secret-equality oracle.
- **CLG-59** — consumer UI may hide on denied condition, but direct server action still independently authorizes.
- **CLG-60** — condition `true` never bypasses capability/resource Policy of action it enables.
- **CLG-61** — privileged preview/explain path is separately capability/Policy gated.
- **CLG-62** — cached explanation/result cannot cross principal/access-generation boundary.
- **CLG-63** — privacy classification influences logs/cache/explanation but does not replace explicit Policy.
- **CLG-64** — authorization revocation invalidates stale condition results before protected disclosure/action.

## E. Boolean groups, short-circuit, order and determinism — CLG-65…CLG-80

- **CLG-65** — nested AND preserves exact boolean grouping/precedence.
- **CLG-66** — nested OR preserves exact grouping/precedence.
- **CLG-67** — NOT applies only to intended node/group.
- **CLG-68** — empty AND/OR group follows explicit validation policy and is not guessed.
- **CLG-69** — short-circuit does not resolve skipped side-effectful/expensive operand unnecessarily.
- **CLG-70** — short-circuit cannot skip required security/authorization checks for operands actually used.
- **CLG-71** — reordering commutative pure nodes for optimization preserves result and explanation contract.
- **CLG-72** — non-commutative/time-sensitive nodes are not reordered unsafely.
- **CLG-73** — identical deterministic inputs yield identical result.
- **CLG-74** — time-dependent condition records evaluation instant/timezone dependency.
- **CLG-75** — random/non-deterministic primitive is unavailable in normal canonical conditions unless separately approved.
- **CLG-76** — three-state unknown/denied/unresolved semantics are not collapsed into true accidentally.
- **CLG-77** — consumer chooses explicit policy for unknown where needed; engine does not invent one globally.
- **CLG-78** — condition result serialization preserves true/false/unknown/error distinctions.
- **CLG-79** — duplicate logically identical subexpression may be memoized only with identical context/dependencies.
- **CLG-80** — optimization does not change audit/explanation enough to hide material dependency or denial reason.

## F. Dependency graph, cycles, nesting and resource budgets — CLG-81…CLG-96

- **CLG-81** — condition dependencies on fields/queries/relations/settings are explicitly registered.
- **CLG-82** — missing hard dependency blocks/degrades affected condition revision.
- **CLG-83** — optional dependency uses explicit fallback, not silent false/true.
- **CLG-84** — direct condition-to-condition cycle is detected.
- **CLG-85** — longer dependency cycle across condition/query/computed field is detected with useful path.
- **CLG-86** — recursive component/condition visibility cycle is bounded.
- **CLG-87** — maximum tree depth prevents stack/resource exhaustion.
- **CLG-88** — maximum node count prevents oversized evaluation payload.
- **CLG-89** — large IN/list operand cardinality is bounded by context.
- **CLG-90** — expensive Query/relation operand consumes declared budget.
- **CLG-91** — public runtime budget may be stricter than admin diagnostic preview.
- **CLG-92** — Workflow/background evaluation has explicit budget and cannot become unbounded fan-out.
- **CLG-93** — repeated same source in list/form view batches resolution to avoid N+1 where semantics permit.
- **CLG-94** — budget exhaustion returns normalized bounded failure/unknown, not partial misleading truth.
- **CLG-95** — dependency generation change invalidates compiled/memoized condition state.
- **CLG-96** — scale evidence records resolver/query count, latency, memory and dependency fanout separately.

## G. Cross-consumer parity and action separation — CLG-97…CLG-112

- **CLG-97** — Forms field visibility uses shared operator semantics.
- **CLG-98** — Forms required/validation condition does not rely only on hidden client state.
- **CLG-99** — Workflow branch uses same typed truth semantics server-side.
- **CLG-100** — Notification recipient/channel condition uses same engine but independently authorizes delivery data.
- **CLG-101** — Admin Columns visibility/editability condition does not bypass list-table Policy.
- **CLG-102** — Dashboard Widget visibility uses condition result only as presentation decision.
- **CLG-103** — Admin Menu visibility condition never becomes authorization.
- **CLG-104** — Component Blueprint/Listings visibility condition reauthorizes protected binding/render context.
- **CLG-105** — Membership/access rules are not silently replaced by generic condition engine where specialized deny-wins semantics own authority.
- **CLG-106** — Status transition guard may consume condition result but transition service remains authoritative.
- **CLG-107** — REST/Ability request precondition uses same semantics without allowing client-supplied compiled predicate.
- **CLG-108** — CLI/AI channels cannot bypass server condition source/Policy boundaries.
- **CLG-109** — same published condition used by two consumers yields same semantic result for identical authorized context.
- **CLG-110** — consumer-specific unsupported operator is rejected at binding/publish time.
- **CLG-111** — consumer can add explicit post-condition business guards without mutating shared engine semantics.
- **CLG-112** — deleting/disabling a shared condition degrades dependents explicitly rather than silently defaulting to allow.

## H. Dynamic sources, time, locale and remote/integration boundaries — CLG-113…CLG-128

- **CLG-113** — site timezone date/time condition uses declared timezone source.
- **CLG-114** — DST spring-forward nonexistent local time follows explicit schedule/time rule.
- **CLG-115** — DST fall-back ambiguous local time follows explicit disambiguation.
- **CLG-116** — locale changes display formatting only, not canonical numeric/date comparison.
- **CLG-117** — translated label never changes stored enum comparison value.
- **CLG-118** — remote Data Source operand runs only through certified Query/Connection/Safe HTTP boundary.
- **CLG-119** — remote credentials never enter Condition Definition/log/explanation.
- **CLG-120** — remote timeout/rate limit/provider failure remains typed unresolved/error.
- **CLG-121** — remote response schema mismatch fails validation before boolean comparison.
- **CLG-122** — remote cached value is reauthorized locally before protected use.
- **CLG-123** — provider truth can be stale/unknown and is not presented as authoritative local state without profile.
- **CLG-124** — webhook/event facts used as context are pinned to event/run identity, not arbitrary current request.
- **CLG-125** — current-time operand marks cache expiry/dependency correctly.
- **CLG-126** — relative-time conditions preserve exact clock/calendar semantics and timezone.
- **CLG-127** — network/site setting inheritance operand records resolved scope/source.
- **CLG-128** — extension resolver/operator failure is isolated and cannot fatal unrelated platform conditions.

## I. Cache identity, invalidation and contract versioning — CLG-129…CLG-144

- **CLG-129** — compiled predicate cache keys include immutable condition revision and engine/profile version.
- **CLG-130** — result cache includes principal/access context whenever visibility differs.
- **CLG-131** — result cache includes site/network target scope.
- **CLG-132** — cache includes locale/timezone only when semantics depend on them.
- **CLG-133** — field/entity update invalidates dependent condition result.
- **CLG-134** — relation edge update invalidates dependent result.
- **CLG-135** — Query Definition/source generation change invalidates dependent result/compile state.
- **CLG-136** — Policy/role/Membership access generation revoke prevents stale allowed result.
- **CLG-137** — condition publish supersedes old revision only for consumers that move to it explicitly.
- **CLG-138** — deprecated operator revision remains interpretable only within declared compatibility window.
- **CLG-139** — unknown future condition schema fails safe/read-only under VER policy.
- **CLG-140** — engine/operator upgrade migration is deterministic and preserves historical revision truth.
- **CLG-141** — stale compiled cache from older operator semantics cannot execute after incompatible upgrade.
- **CLG-142** — rollback/downgrade does not interpret newer required semantics as older close-enough operator.
- **CLG-143** — import/export preserves schema/operator/source IDs and never trusts compiled runtime cache.
- **CLG-144** — cache corruption/missing entry rebuilds from canonical revision rather than mutating source definition.

## J. Failure, concurrency, explanation and observability — CLG-145…CLG-160

- **CLG-145** — invalid input/source returns stable ERR-compatible machine category.
- **CLG-146** — permission denial remains distinct from missing value and validation error.
- **CLG-147** — dependency unavailable remains distinct from condition false.
- **CLG-148** — timeout/rate-limit/provider unknown remains retry-class aware.
- **CLG-149** — internal exception hides stack/SQL/secret/private values in user-facing result.
- **CLG-150** — explanation trace is bounded and redacted according viewer authorization.
- **CLG-151** — explanation records which revision/operator/source classes contributed without exposing protected raw values.
- **CLG-152** — concurrent publish/evaluation pins one immutable revision and cannot mix nodes from two revisions.
- **CLG-153** — concurrent source mutation follows source consistency model; no false snapshot claim.
- **CLG-154** — stale consumer edit referencing old condition revision produces explicit conflict/dependency state.
- **CLG-155** — evaluator retry does not duplicate consumer side effect because evaluation itself remains side-effect free.
- **CLG-156** — audit/correlation can link sensitive evaluation to consumer operation without storing unnecessary operand values.
- **CLG-157** — high-volume repeated errors are rate/budget controlled without hiding first/root cause.
- **CLG-158** — diagnostics distinguish compile failure, source failure, authorization denial, budget exhaustion and false result.
- **CLG-159** — degraded mode never silently converts unresolved/denied to allow for security-sensitive consumer.
- **CLG-160** — recovery/rebuild of compiled caches does not require deleting canonical Condition Definitions.

## K. Multisite, scale, adversarial corpus and release regression — CLG-161…CLG-176

- **CLG-161** — site-owned condition resolves only site-owned dependencies by default.
- **CLG-162** — network-owned condition has explicit target/site aggregation semantics and Super Admin/network Policy.
- **CLG-163** — current blog switch cannot redirect durable condition ownership or cached result to wrong site.
- **CLG-164** — cross-site operand is rejected unless exact certified network/cross-site profile exists.
- **CLG-165** — site clone copies/remaps condition definitions/dependencies according lifecycle policy without stale authority.
- **CLG-166** — site deletion disables/removes owned runtime references without implying global-user/privacy/remote deletion.
- **CLG-167** — restore from older version reconciles condition schema/dependencies before normal writes/actions resume.
- **CLG-168** — 100/1k/10k-site registry/cache workload records network coordination and noisy-neighbor behavior.
- **CLG-169** — 100/1k/10k-node synthetic condition trees enforce depth/node budgets without crash.
- **CLG-170** — malicious nested boolean/list/pattern corpus cannot cause uncontrolled CPU/memory growth.
- **CLG-171** — malicious operand IDs cannot target arbitrary tables/options/users/sites outside registry contracts.
- **CLG-172** — XSS/HTML/JS payload remains data through compare/explanation path.
- **CLG-173** — SQL/control characters remain typed values and cannot become query identifiers/code.
- **CLG-174** — release regression runs representative Forms/Workflow/Menu/Listing consumers against same engine artifact/profile.
- **CLG-175** — certification report lists executed/skipped/inconclusive fixtures and never converts consumer green status into engine certification.
- **CLG-176** — production-readiness requires all mandatory classes, no security/scope stop-line issue, accepted performance baselines and explicit scoped owner authorization before execution/implementation.

---

## 6. Stop-the-line conditions

CLG cannot pass if any supported profile permits:
- arbitrary PHP/JS/raw-SQL/callback execution through normal conditions;
- condition `true` to bypass Capability or resource Policy;
- protected values to leak through explanation, boolean inference or cache reuse;
- wrong-site/network data to influence unauthorized evaluation;
- missing/denied/error/unknown to silently become allow for security-sensitive consumers;
- unbounded recursion/nesting/list/pattern/query fan-out;
- inconsistent operator truth across core consumers for identical typed context;
- stale revocation/version/schema state to preserve unauthorized allow;
- paper/static evidence to be promoted as runtime certification.

## 7. Future execution report

Authorized execution must record:
- CLG-01…CLG-176 result table;
- exact engine/operator/source adapter versions;
- WordPress/PHP/DB/Multisite profiles;
- condition revision/context/dependency fingerprints with sensitive values redacted;
- resolver/query counts, latency/memory/budget evidence where applicable;
- wrong-scope/unauthorized disclosure/action counts;
- consumer parity results;
- cache/version/migration results;
- failures/inconclusive/stop-line items;
- final certification class status.

## 8. Development gate

This protocol is documentation only. No engine code, evaluator/compiler, registry, resolver, cache, WordPress execution, provider call, benchmark, test run, migration or data mutation is authorized. Explicit owner consent under ADR-0014 remains required.