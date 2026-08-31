# WPEssential — Decision, Formula, Scoring & Ranking Executable Evidence Protocol

Status: **Accepted evidence design candidate / execution pending / no development authorization**  
Date: **2026-08-29**

Namespace: **DEC-001…DEC-176**  
Executed: **0/176**  
Runtime certification: **0**

## 1. Purpose

This protocol fixes the executable evidence required before F04 — Decision, Formula, Scoring & Ranking Studio can be called runtime-ready.

F04 evaluates typed facts and produces typed derived outputs. A formula result, score, band, decision-table match or rank is **derived decision data**. It does not become authorization, a ledger posting, a reservation, a payment, an entitlement, a factual external rate, or another domain's canonical business truth merely because F04 produced it.

The expression language is a registered typed grammar/AST. It is not PHP, JavaScript, SQL, shell, template code or an arbitrary provider-call language. Published high-risk definitions are immutable revisions with explicit approval/effective-date semantics.

No fixture below has executed. No parser, evaluator, cache, lookup, score, decision table, ranking engine, simulation, benchmark, provider call, AI call, database write or runtime integration is authorized by this protocol.

## 2. Non-negotiable truth boundaries

- `Decision/Score/Rank ≠ Authorization/Policy`.
- `Formula output ≠ Ledger/Payment/Inventory/Reservation/Order mutation`.
- `Lookup value ≠ externally verified fact unless the input/provenance owner says so`.
- `Simulation ≠ production mutation`.
- `Explanation/Audit ≠ permission to expose protected inputs`.
- `Published revision ≠ latest draft`.
- `Currency conversion ≠ invented exchange rate`; rate source, effective time and provenance are required.
- `Unit conversion ≠ arbitrary numeric multiplication`; dimensions and conversion profile must be compatible.
- `No match`, `missing`, `null`, `denied`, `invalid`, `unavailable` and numeric zero are distinct states where the schema permits them.
- Binary floating point is not canonical money arithmetic.
- AI/MCP may draft/explain/validate definitions only through the same grammar/Policy gates; no privileged arbitrary-code path exists.

## 3. Certification classes

- `DEC-AST` — grammar, parser, typed AST, schema/version compatibility.
- `DEC-NUM` — numeric/decimal/rounding/overflow correctness.
- `DEC-TYP` — currency/unit/date/duration/input typing.
- `DEC-LKP` — lookup/effective-date/version semantics.
- `DEC-SCR` — scorecard/normalization/contribution correctness.
- `DEC-TBL` — decision-table hit/conflict semantics.
- `DEC-RNK` — candidate eligibility/ranking/tie correctness.
- `DEC-SIM` — simulation/no-write/version comparison.
- `DEC-GOV` — publish/approval/high-risk governance.
- `DEC-INT` — consumer integration and dependency boundaries.
- `DEC-SEC` — expression safety, execution budgets and isolation.
- `DEC-OBS` — cache, invalidation, audit and explanations.
- `DEC-MSI` — Multisite ownership/template/isolation.
- `DEC-PERF` — batch scale, resource budgets and performance.
- `DEC-DET` — deterministic golden/cross-runtime regression.

Passing one class never implies another. Runtime-ready requires the exact classes needed by the consuming profile plus the applicable security, Policy, Multisite and deterministic-regression classes.

# Group 1 — Typed formula AST/parser/versioning — DEC-001…DEC-011

- **DEC-001** Valid formula definition compiles from the registered grammar into a typed AST with stable definition key and revision.
- **DEC-002** Parser rejects syntactically invalid input with bounded, location-aware diagnostics and no partial executable artifact.
- **DEC-003** AST serialization round-trips without semantic drift and preserves schema/compiler version.
- **DEC-004** Unknown AST node type is rejected as incompatible rather than ignored or coerced.
- **DEC-005** Operator precedence and associativity match the published grammar for nested arithmetic, comparisons and conditionals.
- **DEC-006** Parentheses override precedence deterministically and survive export/import canonicalization.
- **DEC-007** Expression depth/node-count limits reject pathological trees before evaluation.
- **DEC-008** Output type declared by the formula must match inferred AST type before publish.
- **DEC-009** Definition revision change yields a new immutable evaluation identity; historical evaluations remain pinned to the old revision.
- **DEC-010** Import of an older compatible AST version follows explicit migration and produces a reviewable diff/fingerprint.
- **DEC-011** Delete/archive of a formula respects dependency review and never deletes consumer business data.

# Group 2 — Numeric precision/decimal/rounding/overflow/divide-zero — DEC-012…DEC-022

- **DEC-012** Decimal arithmetic preserves configured canonical precision for exact decimal operands without binary-float money drift.
- **DEC-013** Intermediate precision is explicit and high enough to avoid premature rounding before the configured boundary.
- **DEC-014** Rounding mode is explicit and reproducible for positive and negative halfway cases.
- **DEC-015** round/floor/ceil behavior is type-safe and produces the declared scale.
- **DEC-016** Division by zero follows configured error/null/fallback policy and never silently returns infinity/zero.
- **DEC-017** Integer overflow or decimal magnitude beyond configured bounds produces typed evaluation failure, not wraparound.
- **DEC-018** Very small decimals below supported scale follow explicit rounding/underflow semantics.
- **DEC-019** Percentage input distinguishes 10 percent from numeric 10 and prevents accidental factor-of-100 ambiguity.
- **DEC-020** Mixed integer/decimal arithmetic promotes according to the documented type lattice without precision loss.
- **DEC-021** NaN/infinity-like external values are rejected or normalized to typed invalid state before formula evaluation.
- **DEC-022** Cross-runtime golden arithmetic vectors produce identical canonical decimal results and error states.

# Group 3 — Currency/unit/date/duration type correctness — DEC-023…DEC-033

- **DEC-023** Money value carries currency code and cannot be added/subtracted with a different currency without explicit conversion.
- **DEC-024** Currency conversion requires a registered conversion profile/rate source, effective timestamp and provenance.
- **DEC-025** Missing/stale exchange rate returns explicit unavailable/unknown according to policy; no rate is invented.
- **DEC-026** Unit-bearing quantities with compatible dimensions convert through a registered unit profile and preserve precision.
- **DEC-027** Incompatible dimensions such as length plus mass are rejected at compile/type-check time where statically knowable.
- **DEC-028** Temperature/offset-style conversions use correct affine conversion semantics rather than naive multiplication.
- **DEC-029** Date and datetime values retain explicit timezone/UTC semantics and do not inherit implicit PHP/server timezone.
- **DEC-030** Duration arithmetic distinguishes fixed elapsed duration from calendar period semantics.
- **DEC-031** DST spring-forward/fall-back boundary calculations are deterministic under the selected timezone/calendar policy.
- **DEC-032** Locale affects presentation/parsing only when explicitly configured and never changes canonical stored numeric/date meaning.
- **DEC-033** Unknown currency/unit/timezone identifier is rejected with typed compatibility error before publish/evaluation.

# Group 4 — Input source/null/default/range validation — DEC-034…DEC-044

- **DEC-034** Required constant input missing at evaluation returns declared missing-input failure, not implicit zero/empty string.
- **DEC-035** Optional input with explicit default applies the default only to true missing state, not to valid falsy values.
- **DEC-036** Null, missing, empty string, zero and false remain distinguishable according to input schema.
- **DEC-037** Input source from DVR/entity/context resolves through typed registered resolver and carries source/provenance identity.
- **DEC-038** Query aggregate input must be predeclared/typed and cannot embed arbitrary Query/SQL in formula text.
- **DEC-039** Input min/max validation applies before evaluation and reports out-of-range value without mutating source data.
- **DEC-040** Enum/set-constrained input rejects unknown values rather than treating them as labels or code.
- **DEC-041** String-to-number/date coercion is prohibited by default or follows an explicit locale-independent conversion profile.
- **DEC-042** Protected input denied by Policy is represented as denied/unavailable and cannot fall through to a permissive default silently.
- **DEC-043** Default value itself is type/range/unit validated at definition publish time.
- **DEC-044** Evaluation context rejects client-supplied actor/site/tenant identity that conflicts with server-resolved authority.

# Group 5 — Lookup tables/effective dates/version pinning — DEC-045…DEC-055

- **DEC-045** Lookup table key/value schemas are typed and invalid rows cannot publish.
- **DEC-046** Exact-key lookup returns the value from the pinned published lookup-table revision.
- **DEC-047** Range/band lookup defines inclusive/exclusive boundaries and rejects overlaps where hit policy requires uniqueness.
- **DEC-048** Lookup miss follows explicit no-match/null/default/error behavior.
- **DEC-049** Effective-from/effective-to windows select the correct revision at boundary timestamps.
- **DEC-050** Overlapping effective revisions are rejected or resolved only by explicit priority policy.
- **DEC-051** Historical replay pins the lookup revision/effective facts used originally rather than silently using current values.
- **DEC-052** Lookup import/export preserves stable row identity, types, dates and provenance without hidden executable content.
- **DEC-053** Large lookup cardinality is budgeted/indexed and cannot cause unbounded linear evaluation in public paths.
- **DEC-054** Lookup-table change invalidates dependent cached formula/score/ranking results via dependency graph.
- **DEC-055** Cross-site/network lookup template use preserves site ownership and cannot read another site's private rows.

# Group 6 — Scorecards/weights/normalization/missing factors — DEC-056…DEC-066

- **DEC-056** Scorecard factor definitions declare typed input, weight, normalization profile and contribution visibility.
- **DEC-057** Weight sign/range rules are explicit; forbidden negative or excessive weights block publish.
- **DEC-058** Weight normalization to a target total is deterministic and reports original plus normalized weights.
- **DEC-059** All-zero weight set is rejected or follows explicit zero-score policy; it is never divided by zero.
- **DEC-060** Linear min-max normalization handles exact min/max boundaries and out-of-range clamp/reject policy.
- **DEC-061** Z-score/percentile or other advanced normalization is unavailable unless its statistical source/profile is explicitly certified.
- **DEC-062** Missing factor policy distinguishes ignore-and-renormalize, neutral/default, fail and explicit penalty.
- **DEC-063** Score min/max clipping occurs only at the configured stage and is visible in explanation.
- **DEC-064** Threshold/band boundaries are non-ambiguous and map exact edge values deterministically.
- **DEC-065** Confidence/data-completeness output reflects missing/estimated inputs separately from the numeric score.
- **DEC-066** Contribution explanation sums/reconciles to final score within declared rounding tolerance.

# Group 7 — Decision table overlap/priority/no-match/unreachable rows — DEC-067…DEC-077

- **DEC-067** Decision-table input columns and output columns use typed schemas and registered predicates only.
- **DEC-068** Unique-hit policy rejects overlapping rows capable of matching the same valid input.
- **DEC-069** First-hit policy follows explicit row order/priority and records which earlier rows were evaluated.
- **DEC-070** Priority-hit policy resolves multiple matches using typed priority values with deterministic tie handling.
- **DEC-071** All/collect hit policy returns bounded typed collections and does not duplicate the same logical row.
- **DEC-072** Most-specific profile, if enabled, uses a documented specificity rule and deterministic fallback.
- **DEC-073** No-match state is distinct from false/zero output and follows explicit default/no-result policy.
- **DEC-074** Unreachable row analysis detects rows shadowed by higher-priority predicates when provable.
- **DEC-075** Contradictory predicate ranges are diagnosed before publish.
- **DEC-076** Row reorder/revision changes evaluation fingerprint and cannot alter already-pinned historical decisions.
- **DEC-077** Decision-table explanation reveals matched rule/inputs only to the visibility level allowed by Policy.

# Group 8 — Ranking candidate/eligibility/ties/diversity/manual pins — DEC-078…DEC-088

- **DEC-078** Ranking profile consumes candidates only from its declared Data Source/Query and preserves candidate stable identity.
- **DEC-079** Eligibility Policy/condition is evaluated before scoring; ineligible candidate cannot be resurrected by high score or pin.
- **DEC-080** Score formula output must be compatible numeric ranking type and published revision.
- **DEC-081** Equal scores resolve with declared stable tie-breaker so unchanged input order does not flap.
- **DEC-082** Multiple tie-breakers apply in declared order with explicit missing-value placement.
- **DEC-083** Manual pin changes position only for an otherwise eligible candidate and obeys configured conflict priority.
- **DEC-084** Exclusion rule removes candidate from ranking without mutating source business data.
- **DEC-085** Top-K/cap processing is deterministic after eligibility, score and tie rules.
- **DEC-086** Diversity constraint, when enabled, has a bounded typed algorithm/profile and reports displaced candidates.
- **DEC-087** Pagination/cursor over unchanged candidate snapshot returns stable non-overlapping order.
- **DEC-088** Ranking explanation separates eligibility, score contribution, pin/diversity adjustment and final tie-break.

# Group 9 — Simulation/version compare/sensitivity/no-write — DEC-089…DEC-099

- **DEC-089** Manual-input simulation evaluates a draft/published revision without writing consumer/domain state.
- **DEC-090** Saved fixture simulation records exact inputs, definition revisions and expected typed output.
- **DEC-091** Historical entity simulation reads only authorized snapshot/current facts and remains read-only.
- **DEC-092** Batch sample simulation enforces query/result/evaluation budgets before execution.
- **DEC-093** Compare two formula/scorecard/decision/ranking revisions on identical inputs and surface semantic output diffs.
- **DEC-094** Sensitivity analysis varies only explicitly selected numeric inputs within bounded ranges/steps.
- **DEC-095** Boundary-case generator covers exact min/max/threshold/lookup edges without inventing out-of-schema values.
- **DEC-096** Simulation output distribution labels sample population, missing/failure counts and definition revision.
- **DEC-097** Simulation involving protected data requires separate Policy and redacts unauthorized input/output details.
- **DEC-098** Cancel/timeout of simulation yields partial/aborted state and never reports complete results.
- **DEC-099** Simulation cannot invoke side-effecting Ability, Workflow action, provider mutation or arbitrary code through any function.

# Group 10 — Publish/approval/high-risk financial/risk policy — DEC-100…DEC-110

- **DEC-100** Draft edit is separate from publish/activation authority.
- **DEC-101** Publish requires successful parser/type/dependency validation for the exact revision fingerprint.
- **DEC-102** High-risk financial formula can require explicit approver capability/re-auth per Policy.
- **DEC-103** Risk/eligibility/recommendation score can require maker-checker approval distinct from author.
- **DEC-104** Approval binds to exact revision hash; any post-approval edit invalidates approval.
- **DEC-105** Effective-date activation cannot silently backdate high-risk logic without explicit policy/approval.
- **DEC-106** Emergency disable pauses future consumption while preserving historical revision/evaluation evidence.
- **DEC-107** Rollback/reactivate uses an already-published compatible revision and records operator/audit reason.
- **DEC-108** Manual consumer override is a consumer-domain action, not silent mutation of formula result, and requires its own authority.
- **DEC-109** AI-generated formula/table remains Draft and cannot self-approve or bypass high-risk publish gates.
- **DEC-110** Publish/disable/rollback Audit records safe definition/revision/actor/result metadata without protected input leakage.

# Group 11 — Consumer integration with Query/Workflow/Placement/etc. — DEC-111…DEC-121

- **DEC-111** Query consumer references stable published formula/ranking revision and validates output type before use.
- **DEC-112** Workflow branch consumes decision result as data; Workflow authorization remains independently enforced.
- **DEC-113** Placement/personalization consumes rank/score only after its own audience/Policy eligibility.
- **DEC-114** Analytics metric/attribution custom weights pin formula revision and do not reinterpret result as causal proof.
- **DEC-115** Search ranking integration cannot bypass Search source Policy or revive revoked indexed documents.
- **DEC-116** Forms/field computed value integration validates context and never grants capability from score alone.
- **DEC-117** Membership/access consumer cannot treat score/decision as authorization unless an explicit Policy rule independently defines that semantic.
- **DEC-118** REST/Ability output schema exposes only authorized typed result/explanation fields.
- **DEC-119** Ledger/reservation/commerce consumer validates currency/unit and owns actual mutation/transaction semantics.
- **DEC-120** Consumer missing/incompatible revision enters degraded/not-ready state rather than silently choosing latest.
- **DEC-121** Dependency/Used-by graph lists all consumers needed for safe publish/archive/invalidation impact analysis.

# Group 12 — Malicious expression/no eval/execution budget — DEC-122…DEC-132

- **DEC-122** Formula text containing PHP/code delimiters cannot escape registered grammar into PHP evaluation.
- **DEC-123** JavaScript/eval/function-constructor style payload is parsed as invalid data, never executable code.
- **DEC-124** SQL fragments/subqueries cannot be executed from expression/lookup text.
- **DEC-125** Shell/path/command injection strings have no execution primitive in the expression language.
- **DEC-126** Unknown function name is rejected; functions are registry-based, typed and capability-versioned.
- **DEC-127** Recursive/self-referential formula dependency is detected before evaluation.
- **DEC-128** Mutual dependency cycle across formulas/scorecards/lookups is detected with bounded cycle trace.
- **DEC-129** Excessive AST depth/node count/function calls is rejected according to execution budget.
- **DEC-130** Pathological regex/pattern/string helper input is unavailable or strictly budgeted; ordinary formula grammar provides no unbounded regex execution.
- **DEC-131** External/network/provider call is not permitted as an implicit pure expression function; mutable external facts must arrive as typed inputs with provenance.
- **DEC-132** Malicious AI-generated expression receives the same parser/type/budget validation and cannot gain a privileged execution path.

# Group 13 — Cache/version/invalidation/audit explanations — DEC-133…DEC-143

- **DEC-133** Cache key includes exact definition revision and canonical typed input/context fingerprint.
- **DEC-134** Changing formula/scorecard/decision/lookup/ranking revision namespaces or invalidates affected cache entries.
- **DEC-135** Source input dependency change invalidates cached result where freshness semantics require it.
- **DEC-136** Policy/role/entitlement/site-scope change prevents protected cached result from leaking across authorization contexts.
- **DEC-137** Cache hit returns identical typed result/explanation semantics as uncached evaluation.
- **DEC-138** Cache backend outage degrades performance only and does not change decision/authorization truth.
- **DEC-139** Evaluation log stores definition/revision/result class/timing/provenance needed for diagnosis without becoming canonical business history.
- **DEC-140** Explanation identifies input provenance, rules/functions/contributions and final output at an authorized level.
- **DEC-141** Sensitive/secret input is redacted or omitted from explanation, Audit and diagnostics.
- **DEC-142** Explanation failure/unavailability never changes the evaluated numeric/decision result and is reported separately.
- **DEC-143** Historical replay can compare stored/pinned inputs and revisions to reproduced result while clearly flagging unavailable external facts.

# Group 14 — Multisite templates/site data isolation — DEC-144…DEC-154

- **DEC-144** Site-scoped formula definition has durable site ownership independent of current blog context.
- **DEC-145** Site admin cannot read/edit/evaluate another site's protected definition by supplying site ID/key.
- **DEC-146** Network template can be inherited only according to explicit template/override policy.
- **DEC-147** Site override creates explicit derived revision/link and does not mutate the network template.
- **DEC-148** Network-enforced definition cannot be silently overridden by site-level editor lacking network authority.
- **DEC-149** Network batch evaluation resolves authorized target-site set server-side and preserves per-site context.
- **DEC-150** Same definition key/entity ID on different sites cannot collide because scope is part of identity/cache key.
- **DEC-151** `switch_to_blog()` or equivalent context switching cannot retain stale input/cache/Policy context from previous site.
- **DEC-152** Site clone receives new site ownership and no protected evaluation cache/history identity collision.
- **DEC-153** Site deletion/archive lifecycle disables/quarantines site-owned definitions/evaluation artifacts without affecting siblings.
- **DEC-154** Network aggregate score/rank exposes only data/results allowed by each target site's Policy and protected-count rules.

# Group 15 — Large batch evaluation/performance — DEC-155…DEC-165

- **DEC-155** Single small formula evaluation records baseline parser/compile/evaluate timing separately.
- **DEC-156** Compiled published revision may be reused safely without reparsing for each identical schema evaluation.
- **DEC-157** 10K batch evaluation completes under candidate budget profile without unbounded memory growth.
- **DEC-158** 100K batch evaluation uses bounded chunks/backpressure and reports throughput/partial progress truthfully.
- **DEC-159** 1M batch evaluation is benchmark-only until explicitly certified; system must reject/queue when interactive budget is exceeded.
- **DEC-160** Scorecard with many factors enforces maximum factors/dependency depth before expensive work.
- **DEC-161** Decision table with large row count uses a bounded/certified evaluation strategy and exposes unsupported scale honestly.
- **DEC-162** Ranking large candidate set avoids materializing unbounded protected payloads and enforces top-K/query budgets.
- **DEC-163** Cache stampede/coalescing for equivalent safe evaluations cannot mix actor/site protected contexts.
- **DEC-164** Cancellation/timeout/resource exhaustion produces aborted/partial status and never fabricated complete result.
- **DEC-165** Performance optimization/caching produces byte/semantic-equivalent canonical results to the unoptimized golden implementation.

# Group 16 — Deterministic golden-vector/cross-runtime regression — DEC-166…DEC-176

- **DEC-166** Golden arithmetic vector covers decimal precision, negative values, halfway rounding and divide-zero.
- **DEC-167** Golden money vector covers same-currency operations, mismatch rejection and pinned exchange-rate conversion provenance.
- **DEC-168** Golden unit/date vector covers compatible conversion, incompatible dimensions, timezone and DST boundary cases.
- **DEC-169** Golden null/input vector covers missing/null/zero/false/default/denied distinctions.
- **DEC-170** Golden lookup vector covers exact/range/no-match/effective-date/version-pinning boundaries.
- **DEC-171** Golden scorecard vector reconciles normalized weights, missing-factor policy, bands and contribution explanation.
- **DEC-172** Golden decision-table vector covers unique/first/priority/all/no-match/overlap/unreachable behavior.
- **DEC-173** Golden ranking vector covers eligibility, equal-score ties, pins, exclusions, diversity and stable pagination.
- **DEC-174** Golden simulation/publish vector proves no-write, revision compare, approval invalidation and rollback semantics.
- **DEC-175** Cross-runtime/environment evaluation of the same canonical AST/input/revision produces identical typed result/fingerprint or a declared incompatibility.
- **DEC-176** AI-drafted and hand-authored adversarial regression suite must pass parser/type/Policy/redaction/determinism gates before any DEC runtime certification.

## 4. Execution and certification rules

- Fixture IDs are stable. Refinement may clarify expected artifacts but must not silently change the evidence domain.
- Every execution records build/runtime version, definition revision, fixture data fingerprint, site/network scope, adapter versions and result artifacts needed for reproducibility.
- Expected failure/deny/unknown cases are first-class passing outcomes only when the observed typed state exactly matches the fixture.
- Paper review, static documentation and unit-test-looking examples do not count as executed evidence.
- Performance fixtures require measured artifacts from the target runtime/profile; planning budgets are not performance claims.
- Cross-runtime determinism is certified only for explicitly supported runtimes/versions and canonical decimal/date/unit libraries.
- High-risk financial/risk profiles cannot inherit certification from low-risk examples when their approval, precision or provenance requirements differ.
- Any security leak, authorization bypass, arbitrary-code execution path, cross-site cache bleed or nondeterministic canonical result blocks runtime certification for the affected profile.
- AI-generated fixtures/definitions receive no weaker validation than hand-authored definitions.
- Execution of these fixtures requires separate development/runtime authorization under ADR-0014 and the approval ledger.

## 5. Current evidence truth

- Documented: **176/176**.
- Executed: **0/176**.
- Runtime certification: **0**.
- Production development authorization: **NOT GRANTED**.
- This protocol completes **WP66 planning only** and does not authorize implementation.
