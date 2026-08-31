# WPEssential — P-009 Query Compiler / Cost / Cache / Security Executable Evidence Protocol

Status: **Accepted planning candidate / execution pending / no executable authorization**  
Date: 2026-08-28  
Work package: `P0-M00-WP14`  
Related: ADR-0086, Query AST v1 Candidate Schema, Query P-009 Compiler/Cost/Cache Benchmark Profile, Data & Query Detailed Specifications, Relations P-010, Multisite scope architecture, Connections/Safe HTTP, ADR-0014.

## 1. Purpose

Freeze one bounded adversarial P-009 evidence contract before any Query compiler/runtime/cache implementation exists.

The Query AST is the product contract. Providers compile only the subset they truthfully support:

- **QP1** — WordPress-native provider compilation;
- **QP2** — WPE Custom Table compiler;
- **QP3** — Relations-assisted/two-phase compiler;
- **QP4** — remote Data Source adapter, separately governed by Connections/Safe HTTP/provider evidence.

This protocol does **not** select a universal SQL engine, cache backend, numeric cost threshold, cursor encoding or final physical plan.

## 2. Non-negotiable truth boundaries

1. Query Definition/Revision ≠ runtime invocation ≠ compiled provider request ≠ result/cache entry.
2. Draft edits do not mutate a published revision already referenced by a consumer.
3. No raw SQL node, arbitrary PHP callback/eval or user-controlled identifier escape hatch exists in normal Query AST.
4. Parameter values are typed/untrusted values; identifiers come only from registered schema/provider references.
5. Unsupported semantics fail validation; they are never silently dropped, approximated or mapped to a “close enough” operator.
6. Authentication is not authorization. Row/resource, field/projection and cross-site/network authorization remain server-side.
7. A Query safe for admin preview is not automatically safe for public/API/Workflow use.
8. A fast query that leaks wrong-site/unauthorized data fails P-009.
9. Persistent cache is forbidden when its authorization/invalidation dependencies cannot be represented safely.
10. QP4 remote execution never bypasses local Policy merely because the remote provider returned data.
11. Normal relation/list execution cannot degrade into unbounded per-result N+1 queries.
12. Exact count/aggregate metadata is protected data when it can reveal otherwise hidden rows or cohorts.

## 3. Evidence discipline

Every future execution records at minimum:
- fixture ID;
- Query Definition UUID + immutable revision;
- AST version;
- provider/profile/compiler version;
- source/scope identity;
- actor/principal class;
- execution-context budget class;
- normalized typed parameter fingerprint with sensitive values redacted;
- compiled provider operation safe representation;
- query/request count;
- wall/runtime duration;
- memory;
- rows examined/read where available;
- returned row count;
- wrong-scope/unauthorized row/field/count leakage count;
- cache hit/miss/key dependency/invalidation evidence when applicable;
- SQL/remote request template only in safe redacted form;
- outcome and failure category;
- evidence artifact/reference.

Wrong-scope/unauthorized result count must remain **zero**.

## 4. Fixed fixtures — QRY-01…QRY-168

### A. Definition / revision / consumer identity — QRY-01…QRY-08

- **QRY-01** — create Draft Query with stable UUID/key and typed source.
- **QRY-02** — publish immutable revision; consumer pins published revision.
- **QRY-03** — edit Draft after publish; live consumer remains on prior revision.
- **QRY-04** — publish new revision; dependency/consumer transition is explicit and auditable.
- **QRY-05** — disable/archive Query; existing consumer behavior follows declared state rather than silently executing stale draft.
- **QRY-06** — deleted/missing source dependency produces typed degraded/error state.
- **QRY-07** — provider capability version changes; incompatible published Query becomes explicit incompatible/read-only/degraded state.
- **QRY-08** — unknown future AST version/required semantic feature fails safe before execution.

### B. AST schema / semantic validation — QRY-09…QRY-16

- **QRY-09** — valid nested AND/OR groups preserve intended boolean precedence.
- **QRY-10** — empty/invalid semantic group is rejected on publish/compile.
- **QRY-11** — excessive nesting/depth is rejected by context budget.
- **QRY-12** — unsupported operator for field/provider fails validation.
- **QRY-13** — type-incompatible operator/cast is rejected.
- **QRY-14** — unknown required node is rejected, not ignored.
- **QRY-15** — namespaced provider-extension node requires exact advertised capability/version.
- **QRY-16** — crafted AST attempting raw SQL/callback/arbitrary expression path is rejected.

### C. Parameters / runtime context — QRY-17…QRY-24

- **QRY-17** — typed literal parameter binds correctly.
- **QRY-18** — missing required parameter fails before provider execution.
- **QRY-19** — enum/min/max/length/array-cardinality validation enforced server-side.
- **QRY-20** — URL/route parameter is accepted only when definition explicitly allows the source and validates it.
- **QRY-21** — current-user/current-entity context resolves server-side and cannot be overridden by caller input.
- **QRY-22** — Workflow/Form/Ability parameter mapping preserves declared type and scope.
- **QRY-23** — malicious value corpus remains data, never SQL/identifier/control syntax.
- **QRY-24** — sensitive parameter value is absent from logs/cache diagnostics/support output.

### D. Authorization / scope / projection — QRY-25…QRY-32

- **QRY-25** — unauthorized actor cannot execute protected Query even when definition UUID is known.
- **QRY-26** — Query Preview enforces row/resource Policy; preview capability is not a data bypass.
- **QRY-27** — field/projection Policy hides or rejects sensitive field independently from row visibility.
- **QRY-28** — crafted projection alias/reference cannot expose protected/internal field.
- **QRY-29** — server resolves site/network scope; client-supplied site ID cannot expand authority.
- **QRY-30** — wrong-site entity IDs return zero unauthorized results and no existence oracle beyond allowed error semantics.
- **QRY-31** — public/admin/API/Workflow execution-context budgets are distinct and enforced.
- **QRY-32** — privileged definition or provider diagnostics do not reveal unauthorized row data/secrets.

### E. QP1 WordPress-native providers — QRY-33…QRY-48

- **QRY-33** — posts basic typed filter/projection/order through native WordPress query API.
- **QRY-34** — posts meta filter with typed compare/cast and malicious value corpus.
- **QRY-35** — taxonomy nested predicate preserves IN/NOT IN/AND/EXISTS semantics where supported.
- **QRY-36** — date/time predicate preserves source timezone/storage semantics.
- **QRY-37** — post status/author/include/exclude semantics respect Policy and source capability.
- **QRY-38** — media/attachment preset remains scoped WordPress post behavior.
- **QRY-39** — users role vs role-in semantics remain distinct.
- **QRY-40** — user search/projection cannot expose protected email/login/meta without Policy.
- **QRY-41** — users Multisite blog/site scope is server-enforced.
- **QRY-42** — terms taxonomy/parent/hierarchy/hide-empty semantics match advertised WordPress capability.
- **QRY-43** — term meta predicate remains typed and bounded.
- **QRY-44** — comments status/user/post/meta/date semantics match advertised provider capability.
- **QRY-45** — unsupported aggregate/group/join on QP1 is rejected rather than emulated inaccurately.
- **QRY-46** — current WordPress Query provider is read-only and does not unexpectedly mutate global main query.
- **QRY-47** — WooCommerce or other registered WordPress ecosystem adapter uses its supported API contract; private storage assumptions are not smuggled into QP1.
- **QRY-48** — QP1 10k/100k/1M representative workloads capture query plan/count/latency/memory without correctness relaxation.

### F. QP2 WPE Custom Table compiler — QRY-49…QRY-64

- **QRY-49** — registered logical table/column references compile to exact owned physical schema.
- **QRY-50** — arbitrary table name input is rejected.
- **QRY-51** — arbitrary column/order identifier input is rejected.
- **QRY-52** — value SQL-injection corpus remains bound/prepared data.
- **QRY-53** — identifier quoting/allowlist handles reserved/special-but-supported registered identifiers safely.
- **QRY-54** — typed NULL vs empty vs missing semantics are preserved.
- **QRY-55** — numeric/decimal/date/datetime/boolean/string casts preserve declared logical semantics.
- **QRY-56** — collation/case-sensitivity behavior is explicit and provider-profile correct.
- **QRY-57** — INNER/LEFT join only across registered safe schemas preserves cardinality semantics.
- **QRY-58** — crafted join cannot target WordPress/plugin/arbitrary tables outside owned/registered contract.
- **QRY-59** — aggregate/group/having type validation and null behavior are correct.
- **QRY-60** — explicit projection prevents accidental `SELECT *` leakage on public/API path.
- **QRY-61** — indexed predicate plan is recorded and stays within accepted budget after calibration.
- **QRY-62** — unindexed/high-scan predicate is warned/blocked according to context budget, not silently allowed universally.
- **QRY-63** — schema/migration generation change invalidates incompatible compile/cache artifacts.
- **QRY-64** — QP2 10k/100k/1M row workloads compare offset/keyset, indexes, compiler overhead and memory.

### G. QP3 Relations-assisted compilation — QRY-65…QRY-80

- **QRY-65** — parent→child traversal returns authorized targets only.
- **QRY-66** — child→parent traversal preserves direction semantics.
- **QRY-67** — symmetric relation semantics do not duplicate/invert incorrectly.
- **QRY-68** — relation existence predicate compiles/batches without per-row N+1.
- **QRY-69** — nested related-target predicate preserves Policy on both source and target resources.
- **QRY-70** — pivot/meta filter uses registered typed pivot fields only.
- **QRY-71** — relation count/aggregate does not leak hidden target existence.
- **QRY-72** — one-to-one/one-to-many/many-to-many cardinality assumptions do not duplicate base rows incorrectly.
- **QRY-73** — bounded ID-prefilter → provider-query two-phase plan preserves deterministic result semantics.
- **QRY-74** — provider-query → batched relation hydration plan stays bounded and preserves ordering.
- **QRY-75** — certified direct join path is used only when owning schemas/capability permit it.
- **QRY-76** — relation traversal depth/cycle limit is enforced.
- **QRY-77** — high-degree relation fanout hits budget/rejection rather than memory explosion.
- **QRY-78** — relation edge add/remove invalidates dependent persistent Query cache.
- **QRY-79** — endpoint deletion/orphan/reconciliation state cannot leak stale relation result.
- **QRY-80** — 100k/1M-edge workloads prove bounded query count; normal list N+1 is zero-tolerance failure.

### H. QP4 remote Data Source — QRY-81…QRY-92

- **QRY-81** — remote adapter executes only through registered Connection/Safe HTTP boundary.
- **QRY-82** — Query definition contains no raw credential/secret material.
- **QRY-83** — local AST filter/sort/pagination is accepted remotely only when exact provider semantics are advertised.
- **QRY-84** — unsupported remote operator is rejected; local post-filter fallback occurs only when explicitly bounded/correct and never hides limitation.
- **QRY-85** — remote cursor/page token is treated as opaque/untrusted and bound to expected source/query context.
- **QRY-86** — remote total-count semantics distinguish exact/approximate/unknown.
- **QRY-87** — timeout/rate-limit/provider-unavailable error is normalized without leaking credentials/request secrets.
- **QRY-88** — credentials refresh/re-authentication occurs through Connection/Vault contract, not Query payload.
- **QRY-89** — remote response schema mismatch fails validation/degrades safely.
- **QRY-90** — remote cached result is reauthorized locally before protected disclosure.
- **QRY-91** — SSRF/custom endpoint trust rules remain enforced for remote source.
- **QRY-92** — unknown/ambiguous provider outcome cannot fabricate a complete local result/cache state.

### I. Cost model / execution budgets — QRY-93…QRY-104

- **QRY-93** — predicate-count budget boundary.
- **QRY-94** — boolean nesting/OR explosion budget boundary.
- **QRY-95** — meta-join multiplication cost signal.
- **QRY-96** — leading wildcard/free-text expensive search boundary.
- **QRY-97** — regex disabled/rejected for public runtime unless exact safe capability/budget permits.
- **QRY-98** — relation traversal/fanout cost boundary.
- **QRY-99** — aggregate/group/having cost boundary.
- **QRY-100** — exact-total-count cost boundary.
- **QRY-101** — deep-offset cost boundary.
- **QRY-102** — maximum page-size/row budget boundary.
- **QRY-103** — remote round-trip/fanout budget boundary.
- **QRY-104** — admin preview may diagnose a query that public/API runtime correctly blocks; no automatic budget inheritance.

### J. Ordering / pagination / cursor integrity — QRY-105…QRY-116

- **QRY-105** — deterministic unique order requires stable tie-breaker.
- **QRY-106** — non-unique sort without tie-breaker is rejected/warned for cursor mode.
- **QRY-107** — ascending keyset cursor next-page correctness.
- **QRY-108** — descending/multi-column cursor correctness.
- **QRY-109** — NULL/collation ordering semantics match provider profile.
- **QRY-110** — cursor tamper fails validation without scope/data disclosure.
- **QRY-111** — cursor is bound to Query revision/provider/scope/order/parameter semantics.
- **QRY-112** — actor loses authorization between pages; next page reauthorizes and does not disclose cached continuation.
- **QRY-113** — source mutation between pages follows documented consistency model; no false snapshot claim.
- **QRY-114** — offset vs keyset benchmark records duplication/skip/latency characteristics under concurrent writes.
- **QRY-115** — random ordering is blocked/incompatible with stable cursor/cache unless explicit deterministic seed profile exists.
- **QRY-116** — public caller cannot request unbounded/no-limit or exceed hard page limit.

### K. Counts / aggregates / inference leakage — QRY-117…QRY-124

- **QRY-117** — exact count only includes rows actor is authorized to know exist.
- **QRY-118** — approximate/provider count is labeled, not presented as exact.
- **QRY-119** — hidden-row cohort cannot be inferred through aggregate count.
- **QRY-120** — SUM/AVG/MIN/MAX over protected field/resource is denied or scoped correctly.
- **QRY-121** — group-by key projection obeys field Policy.
- **QRY-122** — HAVING cannot become authorization bypass after aggregation.
- **QRY-123** — empty vs zero vs unknown count semantics remain distinct.
- **QRY-124** — count cache carries same authorization/scope/invalidation dependencies as result cache.

### L. Cache identity / invalidation / revocation — QRY-125…QRY-140

- **QRY-125** — request-local cache does not cross request/principal boundary.
- **QRY-126** — persistent key includes Query immutable revision.
- **QRY-127** — key includes normalized typed parameters.
- **QRY-128** — key includes provider/compiler profile version.
- **QRY-129** — key includes site/network/aggregation scope.
- **QRY-130** — principal/access context varies key when visibility differs.
- **QRY-131** — locale/timezone factor included when query semantics depend on it.
- **QRY-132** — source entity/meta/tax update invalidates dependent cache.
- **QRY-133** — relation edge generation invalidates dependent cache.
- **QRY-134** — Membership/Role/Policy revoke changes authorization generation and stale allow is not served.
- **QRY-135** — definition publish invalidates/supersedes prior revision cache correctly.
- **QRY-136** — custom-table schema generation change invalidates compiled/cache artifacts.
- **QRY-137** — user/site deletion removes or makes dependent protected cache unreachable.
- **QRY-138** — stale-while-revalidate is rejected for definitions whose correctness/security cannot tolerate stale data.
- **QRY-139** — privileged cache entry cannot be reused by anonymous/lower-privilege caller.
- **QRY-140** — when safe authorization dependency cannot be represented, persistent shared caching is disabled rather than guessed.

### M. Multisite / network aggregation — QRY-141…QRY-150

- **QRY-141** — default Query executes only in current site scope.
- **QRY-142** — caller-supplied site/network identifier cannot widen scope.
- **QRY-143** — explicit network Query requires network authorization and provider capability.
- **QRY-144** — site-owned data in network aggregation rechecks target-site Policy.
- **QRY-145** — bounded explicit site set aggregation returns no data from unauthorized site.
- **QRY-146** — unbounded synchronous loop across every subsite is rejected.
- **QRY-147** — deleted/uninitialized site cannot remain reachable through stale query/cache index.
- **QRY-148** — site clone/transfer creates new scope identity; old-site cache/cursor cannot be replayed into target.
- **QRY-149** — 100/1k/10k-site benchmark measures bounded aggregation strategy and noisy-neighbor behavior.
- **QRY-150** — network cache key/invalidation includes site-set/scope generation and cannot cross networks/installations.

### N. Explain / diagnostics / error truth — QRY-151…QRY-156

- **QRY-151** — Explain shows normalized AST/provider/cost/index hints without secret interpolation.
- **QRY-152** — prepared SQL diagnostics separate template from redacted parameters and remain capability-gated.
- **QRY-153** — remote headers/tokens/credentials are absent from diagnostics/logs.
- **QRY-154** — unsupported/degraded node is visible as explicit error/warning, never hidden.
- **QRY-155** — cost/block reason is observable enough for admin remediation without exposing sensitive data.
- **QRY-156** — provider/runtime error normalization does not reveal cross-site existence, SQL internals or secrets to unauthorized callers.

### O. Concurrency / consumers / final scale — QRY-157…QRY-168

- **QRY-157** — concurrent Query publish/edit uses revision/concurrency control; no lost update silently changes live semantics.
- **QRY-158** — consumer references immutable revision/fingerprint during one request/run.
- **QRY-159** — Dynamic Listing cannot privately mutate referenced Query AST; parameter map only.
- **QRY-160** — Admin Columns Query source uses batch execution plan and rejects per-row N+1.
- **QRY-161** — REST endpoint parameter mapping cannot bypass Query parameter schema/Policy/budget.
- **QRY-162** — Forms/Dashboard/Workflow consumer context does not inherit a more privileged execution budget accidentally.
- **QRY-163** — merged-query application strategy exposes global sort/pagination limitations truthfully.
- **QRY-164** — provider-native merge/UNION is allowed only for compatible registered schemas/capabilities; no arbitrary SQL union.
- **QRY-165** — source/provider unavailable yields typed degraded state; stale privileged data is not served as generic fallback.
- **QRY-166** — 10k/100k/1M workload report compares QP1/QP2/QP3 only on workloads they actually own; no misleading universal-engine winner.
- **QRY-167** — independent security review attacks AST, parameters, identifiers, cursors, scope, count and cache leakage before P-009 certification.
- **QRY-168** — final evidence audit proves every accepted provider/profile/context has explicit PASS/FAIL/NOT-APPLICABLE evidence and no paper assumption is promoted to runtime truth.

## 5. Stop-the-line failures

Any of the following blocks P-009 certification immediately:
- raw user SQL/arbitrary callback/eval becomes reachable through normal Query definition;
- SQL/identifier/order/projection injection succeeds;
- unsupported semantic node/operator is silently ignored or approximated;
- wrong-site/network data is returned;
- row/resource/field authorization is bypassed;
- protected existence leaks through count/aggregate/facet metadata;
- privileged cache result is served to lower-privilege/anonymous actor;
- committed authorization revoke still receives stale cached allow/result;
- cursor tamper or cross-revision/scope replay exposes data;
- public/API query can escape hard budget/page/depth limits;
- normal relation/list execution produces unbounded N+1;
- remote provider result bypasses current local Policy;
- remote/provider secrets appear in AST, logs, diagnostics or cache payload;
- cross-site aggregation uses an unbounded synchronous all-sites loop;
- benchmark speed is used to waive a correctness/security failure.

## 6. Provider-profile acceptance

A Query provider/profile can be called runtime-supported only when:
1. its advertised AST/operator/source capability set is versioned;
2. all applicable security/scope fixtures pass;
3. unsupported semantics fail before execution;
4. its pagination/count/cache semantics are truthful;
5. cost budgets are calibrated for each supported execution context;
6. applicable 10k/100k/1M workloads meet accepted budgets or publish explicit limits;
7. Multisite scope behavior is certified where claimed;
8. diagnostics/redaction evidence passes;
9. independent security review is complete for certification level claimed.

Passing QP1 does not certify QP2/QP3/QP4. Passing one provider version does not certify another.

## 7. Required future evidence report

When owner later authorizes executable P-009 work, the report must include:
- exact code/artifact commit;
- WP/PHP/DB environment profile;
- provider/compiler/cache backend versions;
- fixture IDs executed/skipped with reason;
- FAST/FULL classification;
- baseline failures and flaky evidence;
- security/scope/count/cache leakage results;
- query plans/indexes/rows examined where available;
- latency/query-count/memory/compiler-overhead metrics;
- offset-vs-cursor results;
- cache hit/miss/invalidation/revocation timings;
- Multisite scale results;
- NOT VERIFIED items;
- final provider/profile/context support claims strictly bounded to evidence.

## 8. Current evidence state

- QRY fixtures documented: **168**
- QRY fixtures executed: **0/168**
- P-009 runtime certifications: **0**
- QP1 certified providers/profiles: **0**
- QP2 certified providers/profiles: **0**
- QP3 certified providers/profiles: **0**
- QP4 certified providers/profiles: **0**
- independent P-009 security review executed: **NO**
- final numeric cost thresholds: **OPEN / evidence-gated**
- final persistent cache backend/default: **OPEN / evidence-gated**
- final cursor encoding/profile: **OPEN / evidence-gated**

## 9. Development gate

This protocol is documentation/evidence planning only.

No compiler, SQL generation, DB fixture, data mutation, query execution, cache operation, remote request, WordPress runtime, benchmark, provider certification or security test is authorized by this document.

ADR-0014 explicit scoped owner consent remains required before every executable P-009 action.