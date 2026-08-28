# WPEssential — Experimentation & Rollout Executable Evidence Protocol

Status: **Accepted evidence design candidate / execution pending / no development authorization**  
Date: **2026-08-29**

Namespace: **EXP-001…EXP-176**  
Documented: **176/176**  
Executed: **0/176**  
Runtime certification: **0**

## 1. Purpose

This protocol fixes the executable evidence required before F08 — Experimentation & Rollout can be called runtime-ready.

F08 owns controlled experiment definitions, deterministic assignment, exposure semantics, metric interpretation contracts, staged rollout and kill-switch behavior for explicitly declared experiment/rollout profiles. It does not become authorization, consent authority, source-data truth, feature business truth, causal proof, analytics warehouse authority, placement authority or privileged mutation authority merely because it selects a variant or rollout cohort.

No fixture below has executed. No assignment engine, exposure collector, analytics query, cache mutation, feature flag runtime, browser render, provider call, AI/MCP call, benchmark, build, test or production mutation is authorized by this protocol.

## 2. Non-negotiable truth boundaries

- `Experiment assignment ≠ authorization`.
- `Experiment assignment ≠ consent`.
- `Assignment ≠ exposure`; a subject may be assigned without actually seeing/receiving the treatment.
- `Exposure ≠ conversion`.
- `Observed association ≠ automatic causal proof`; the selected statistical profile, design assumptions, data quality and interference risks must be disclosed.
- `Rollout cohort ≠ role/capability/membership/entitlement`.
- F08 feature rollout can gate delivery of an already-authorized capability, but cannot grant an otherwise-denied capability.
- F07 Placement may consume a typed experiment assignment, but does not own F08 allocation/statistical truth.
- F02 Analytics owns event/metric collection and data-quality truth; F08 consumes declared metric contracts and cannot silently redefine event facts.
- Primary metric, guardrails and stopping rules are versioned experiment configuration; results cannot silently switch metrics after observation.
- Deterministic assignment is scoped by declared experiment revision, subject identity, site/tenant and namespace; request-supplied identity/site/tenant values cannot create privilege or cross-tenant contamination.
- Anonymous→authenticated stitching follows an explicit policy and cannot silently rewrite historical assignments/exposures in a way that fabricates treatment history.
- A kill switch is an operational safety control, not statistical evidence that a variant lost.
- A feature flag/rollout may exist without being an experiment; non-experiment rollout must not fabricate A/B statistics.
- Cache state must include all dimensions required to prevent cross-variant, cross-user, cross-consent, cross-site or cross-tenant leakage.
- Sensitive segmentation requires Policy/consent/data-minimization rules and must not be inferred from prohibited attributes merely for targeting.
- AI/MCP may draft experiment plans, hypotheses, metric candidates, analyses and rollout recommendations only through normal Policy/approval gates; it cannot publish, allocate, stop, roll out or expose privileged variants through a hidden path.

## 3. Certification classes

- `EXP-SCH` — experiment/variant/hypothesis/metric schema and lifecycle.
- `EXP-ELG` — eligibility, exclusions and audience boundary correctness.
- `EXP-ASG` — deterministic assignment, hashing, identity and stickiness.
- `EXP-ALC` — allocation, rebalance and variant-set evolution.
- `EXP-EXP` — exposure event identity, dedupe and contamination semantics.
- `EXP-MET` — primary/guardrail metric contracts and interpretation.
- `EXP-STA` — statistical profile, sample, intervals, multiplicity and caveats.
- `EXP-RLS` — schedule, pause/stop, rollout and kill-switch safety.
- `EXP-INT` — cache/personalization/anonymous-login integration.
- `EXP-VER` — experiment versioning and concurrent configuration change safety.
- `EXP-PRV` — consent, privacy and sensitive segmentation.
- `EXP-FLG` — feature-rollout safety and non-experiment flag semantics.
- `EXP-MSI` — Multisite/site/tenant assignment isolation.
- `EXP-DQ` — analytics data quality, late events, refunds and corrections.
- `EXP-PERF` — high-traffic assignment/exposure performance.
- `EXP-DET` — deterministic end-to-end A/B, multivariate and rollout regression.

Passing one class never implies another. Runtime readiness requires every class applicable to the enabled experiment/rollout profile.

# Group 1 — Experiment/variant/hypothesis/metric schema — EXP-001…EXP-011

- **EXP-001** Valid Experiment Definition publishes only with stable experiment key, immutable revision identity, lifecycle state and explicit experiment type/profile.
- **EXP-002** Experiment requires a declared hypothesis/question, population intent, treatment/control model and owner; missing hypothesis cannot be silently replaced with generic optimization text.
- **EXP-003** Variant schema requires stable variant key, label, treatment reference/configuration and status, with one unambiguous control/baseline when the selected profile requires it.
- **EXP-004** Duplicate variant keys, experiment keys or ambiguous control designations are rejected before publish.
- **EXP-005** Experiment metric bindings reference versioned F02 metric contracts or explicitly accepted derived metric definitions rather than ad-hoc mutable labels.
- **EXP-006** Primary metric, guardrail metrics and diagnostic/secondary metrics remain typed/distinct; a secondary metric cannot silently become primary after data is observed.
- **EXP-007** Experiment schema records unit of assignment, unit of analysis and eligible exposure unit, and rejects unsupported mismatches unless a certified clustered/aggregate profile is selected.
- **EXP-008** Unknown experiment/variant/schema version is rejected as incompatible rather than silently coerced.
- **EXP-009** Draft duplicate/fork creates a new experiment/revision identity and does not reuse production assignment namespace unless explicitly declared compatible through versioning rules.
- **EXP-010** Archive/delete workflow performs dependency/active-traffic review and preserves historical assignment/exposure/result references required for analysis and audit.
- **EXP-011** Export/import preserves stable definition IDs, versions, variant keys, metric references and provenance without importing live subject assignments unless an explicit migration profile separately allows it.

# Group 2 — Eligibility/audience exclusions — EXP-012…EXP-022

- **EXP-012** Eligibility is evaluated server-side from declared Policy-safe context and cannot rely solely on client/UI visibility.
- **EXP-013** Inclusion rules and exclusion rules have deterministic precedence; explicit exclusion wins over broad inclusion unless the profile documents another governed rule.
- **EXP-014** A subject outside the eligible population receives no experimental treatment assignment merely because a variant URL/component is directly requested.
- **EXP-015** Existing role/capability/membership/entitlement Policy remains authoritative; experiment eligibility cannot grant access to a protected capability or resource.
- **EXP-016** Mutually exclusive audience/experiment groups honor declared namespace rules so a subject cannot enter conflicting experiments where exclusion is required.
- **EXP-017** Eligibility based on changing attributes records the evaluation revision/time semantics so later analysis can explain why the subject was eligible at assignment/exposure time.
- **EXP-018** Missing eligibility inputs follow explicit fail-open/fail-closed rules; protected/high-risk experiments default to no assignment when required inputs are unknown.
- **EXP-019** Bot/internal/test-user/excluded-role handling follows configured filters and does not contaminate production assignment or result populations.
- **EXP-020** Geographic/device/referrer/behavioral audience predicates use declared trusted/derived sources and bounded values rather than arbitrary client claims when correctness matters.
- **EXP-021** Eligibility preview/simulation is read-only and must not create durable assignments, exposures or feature-rollout state.
- **EXP-022** AI-generated audience suggestions remain Draft until normal privacy, Policy and approval checks pass; AI cannot silently activate sensitive segmentation.

# Group 3 — Deterministic assignment/hash/stickiness — EXP-023…EXP-033

- **EXP-023** Same eligible subject, experiment revision and assignment namespace deterministically resolves to the same variant across repeated evaluations under a sticky profile.
- **EXP-024** Assignment hash input includes required experiment revision/site/tenant/subject namespace dimensions so unrelated experiments or tenants cannot collide.
- **EXP-025** Hash/assignment implementation is deterministic across supported runtimes and does not use process-randomized language hash functions.
- **EXP-026** Allocation bucket boundaries are canonical and stable; exact boundary fixtures map deterministically without off-by-one gaps/overlap.
- **EXP-027** Anonymous subject identity is explicit, scoped and rotation-aware; cookie/session IDs do not become authenticated identity authority.
- **EXP-028** Login transition follows the configured anonymous→authenticated policy: keep anonymous assignment, reassign, or reconcile, with no silent double exposure.
- **EXP-029** Logout/session rotation behavior is explicit and cannot leak a prior authenticated subject’s sticky assignment to a different user sharing the browser.
- **EXP-030** Subject identity collision/invalid identity fails safely and cannot merge unrelated users into one experiment history.
- **EXP-031** Assignment persistence/idempotency guarantees one canonical durable assignment when persistence is enabled, even under concurrent first requests.
- **EXP-032** Request replay with the same assignment identity returns the existing assignment rather than incrementing allocation/exposure counts twice.
- **EXP-033** Assignment explanation can report experiment revision, eligibility result, bucket and selected variant to authorized operators without exposing another subject’s protected identity.

# Group 4 — Allocation percentages/rebalance/new variant — EXP-034…EXP-044

- **EXP-034** Variant allocation percentages/weights validate to the selected profile’s exact total/range with deterministic rounding/bucket partitioning.
- **EXP-035** Zero-allocation variant receives no new assignments while preserving historical assignments/exposures for analysis.
- **EXP-036** Allocation change creates a new configuration revision/effective boundary rather than silently rewriting the configuration that produced prior assignments.
- **EXP-037** Rebalancing policy explicitly states whether existing sticky assignments remain fixed or can migrate; no silent reassignment is permitted.
- **EXP-038** Adding a new variant mid-experiment creates an explicit new revision/analysis caveat and cannot pretend the new variant was eligible during earlier traffic.
- **EXP-039** Removing/retiring a variant stops new assignments according to effective-time rules while preserving historical data and a safe treatment fallback for already-assigned subjects.
- **EXP-040** Concurrent configuration publishes resolve by optimistic version/fingerprint or approval rules so two editors cannot silently overwrite allocation plans.
- **EXP-041** Allocation preview over the full bucket space proves no gaps, overlaps or unreachable active variants for the selected algorithm.
- **EXP-042** Small percentage allocations retain deterministic exact bucket semantics and do not drift due to binary floating-point rounding.
- **EXP-043** Manual force-variant/debug override is isolated from production analysis by explicit test/operator provenance and cannot be used as an unlogged privileged treatment path.
- **EXP-044** AI/MCP-proposed allocation/rebalance remains a Draft recommendation until explicit publish/approval; model confidence cannot itself mutate live traffic.

# Group 5 — Exposure event dedupe/first exposure/contamination — EXP-045…EXP-055

- **EXP-045** Assignment alone does not emit an exposure unless the selected experiment profile explicitly defines assignment-as-exposure and documents the caveat.
- **EXP-046** First actual treatment delivery/render/action records an exposure with stable experiment revision, variant, subject/unit, time and exposure source.
- **EXP-047** Repeated renders/retries of the same logical exposure are deduplicated according to configured exposure identity/window rather than counted blindly.
- **EXP-048** Multiple legitimate exposures can be retained for repeat-exposure analysis while first-exposure remains separately derivable.
- **EXP-049** Exposure event with variant different from canonical assignment is flagged as contamination/integrity violation rather than silently accepted.
- **EXP-050** Exposure received before a known assignment can be reconciled only through explicit rules and cannot fabricate an assignment history without provenance.
- **EXP-051** Exposure after experiment stop/variant retirement is classified by effective-time/config revision and surfaced as stale-treatment leakage.
- **EXP-052** Cross-device authenticated exposure linkage occurs only after authoritative identity linkage and never joins anonymous users by weak fingerprinting.
- **EXP-053** Client-generated exposure payload cannot override server-known experiment revision, tenant or privileged treatment identity without validation.
- **EXP-054** Exposure queue retry/redelivery is idempotent and cannot inflate sample size through duplicate transport events.
- **EXP-055** Exposure diagnostics distinguish assigned-not-exposed, exposed, contaminated, duplicate, late and invalid states for analysis/data-quality review.

# Group 6 — Primary/guardrail metric semantics — EXP-056…EXP-066

- **EXP-056** Primary metric binding pins the exact metric definition/version, time window and aggregation semantics used for the declared decision question.
- **EXP-057** Guardrail metric definitions are independent of the primary metric and can block rollout recommendations without being silently optimized as the primary objective.
- **EXP-058** Metric numerator/denominator, unit of analysis, attribution window and exposure anchoring are explicit and versioned.
- **EXP-059** Pre-exposure events are excluded from post-treatment outcome metrics unless the metric profile explicitly uses them as covariates/baseline data.
- **EXP-060** Subjects assigned but never exposed are handled according to the declared estimand/profile; treatment-on-treated and intention-to-treat interpretations are not silently mixed.
- **EXP-061** Conversion events are deduplicated according to F02 metric/event identity semantics before F08 consumes them.
- **EXP-062** Revenue/currency metric uses canonical decimal/currency conversion provenance from owning systems; F08 does not invent exchange rates or financial truth.
- **EXP-063** Refund/cancel/reversal-adjusted metrics state their correction window and source event semantics rather than permanently counting gross conversions as final truth.
- **EXP-064** Missing/late metric data produces a data-completeness warning and cannot be silently interpreted as zero.
- **EXP-065** Metric definition change after experiment start creates a new analysis/config revision or invalidates comparability; historical results retain the originally pinned metric contract.
- **EXP-066** Dashboard/result API identifies primary, guardrail and exploratory metrics distinctly so exploratory wins cannot be presented as the predeclared primary result.

# Group 7 — Statistical profile/sample/interval/error caveats — EXP-067…EXP-077

- **EXP-067** Experiment selects an explicit supported statistical profile/method; the UI/API does not imply one universal significance model for all designs.
- **EXP-068** Sample counts distinguish assigned, exposed, eligible, analyzed and converted units rather than presenting one ambiguous N.
- **EXP-069** Confidence/credible intervals, point estimates and uncertainty are computed using the declared profile and display method/version provenance.
- **EXP-070** Sequential peeking/continuous monitoring follows an accepted sequential design or surfaces that nominal fixed-horizon p-values/intervals are not valid for repeated optional stopping.
- **EXP-071** Minimum detectable effect/power/sample planning is treated as planning evidence, not a guarantee of observed significance or business value.
- **EXP-072** Multiple variants/metrics/comparisons invoke the configured multiplicity strategy or display an explicit unadjusted exploratory warning.
- **EXP-073** Tiny sample, zero-event, extreme imbalance or degenerate variance cases return bounded/typed diagnostics rather than NaN/Infinity presented as a business conclusion.
- **EXP-074** Sample-ratio mismatch is detected against expected allocation with configured tolerance/test and blocks blind interpretation until investigated.
- **EXP-075** Interference/network effects, novelty effects, carryover/crossover and non-independent units are surfaced as design caveats where applicable rather than hidden.
- **EXP-076** Statistical result does not automatically issue a causal claim when eligibility, exposure, instrumentation, contamination or design assumptions are materially violated.
- **EXP-077** AI-generated result summaries must include the same uncertainty, data-quality and design caveats and cannot state a winner beyond the certified statistical/evidence profile.

# Group 8 — Schedule/stop/pause/rollout/kill switch — EXP-078…EXP-088

- **EXP-078** Experiment schedule stores explicit start/end instants and display timezone; activation follows server-resolved canonical time.
- **EXP-079** Future-scheduled experiment cannot assign production subjects before its start boundary even if cached/preview configuration exists.
- **EXP-080** Pause stops new treatment assignment/delivery according to the selected profile while retaining historical assignment/exposure/result data.
- **EXP-081** Resume preserves or explicitly revisions assignment semantics; it cannot silently reset the population and double-count returning subjects.
- **EXP-082** Stop/end transition records reason, actor/source, effective time and config revision; stopping does not rewrite prior exposure history.
- **EXP-083** Winner/rollout action is a separate governed rollout decision and cannot be inferred solely from a dashboard color or statistical threshold.
- **EXP-084** Kill switch can immediately disable a treatment/feature path within the declared operational guarantee and records safety provenance independently of experiment-result interpretation.
- **EXP-085** Kill switch failure/degraded state surfaces explicitly; system must not claim a treatment is off if stale caches/edge/provider paths can still deliver it.
- **EXP-086** Scheduled rollout percentage changes execute idempotently and use version/effective-time guards so delayed jobs cannot replay older allocation state over newer state.
- **EXP-087** Rollback from a feature rollout follows explicit safe fallback semantics and does not assume external side effects can be undone by hiding the feature.
- **EXP-088** High-risk rollout/stop/kill operations can require re-auth/maker-checker approval bound to exact normalized rollout plan fingerprint.

# Group 9 — Cache/personalization/anonymous-login stitch — EXP-089…EXP-099

- **EXP-089** Experiment-aware cache keys include every required experiment/variant/site/tenant/consent/identity class dimension needed to prevent treatment leakage.
- **EXP-090** Shared/public cache never stores a personalized treatment response unless the response is explicitly safe for the shared cohort key.
- **EXP-091** Variant-specific assets/content fragments cannot be served from a stale cache to a subject assigned a different variant after configuration change.
- **EXP-092** Cache purge/invalidation on experiment revision/stop/kill reaches all declared cache layers or reports degraded safety state rather than assuming success.
- **EXP-093** F07 placement personalization consumes typed F08 assignment and exposure hooks without creating a second independent experiment assignment engine.
- **EXP-094** Anonymous subject cache/assignment state is isolated from authenticated user state according to the configured login-stitch policy.
- **EXP-095** Login does not expose an earlier anonymous user’s experiment treatment to a different account on a shared browser after identity transition cleanup rules apply.
- **EXP-096** Cross-request concurrent anonymous→authenticated transition cannot create two active conflicting assignments when the profile claims canonical sticky assignment.
- **EXP-097** Consent change invalidates affected targeting/cache/treatment dimensions and prevents further consent-dependent assignment/delivery where required.
- **EXP-098** CDN/edge cache or third-party personalization adapter must declare experiment-aware key/purge capabilities before being certified for protected personalized experiments.
- **EXP-099** Cache-bypass/debug mode is permissioned, marked non-production/test in analysis and cannot be used to force undocumented treatment for ordinary users.

# Group 10 — Experiment versioning/concurrent config changes — EXP-100…EXP-110

- **EXP-100** Every published experiment configuration has immutable revision identity and effective-time boundary linking assignments/exposures/results to the exact configuration.
- **EXP-101** Editing a live experiment creates a Draft/new revision; historical published revision remains readable and cannot be silently overwritten.
- **EXP-102** Concurrent editors publishing from the same base revision trigger conflict/merge review rather than last-write-wins loss of hypothesis/metric/allocation changes.
- **EXP-103** Assignment algorithm/hash namespace change requires explicit compatibility/migration semantics and cannot silently reassign existing sticky subjects.
- **EXP-104** Variant treatment content revision is pinned or version-linked so analysis can distinguish subjects exposed to materially different treatment payloads under one variant label.
- **EXP-105** Metric contract revision during a live experiment creates explicit analysis boundary and does not backfill old results under new semantics without a governed recomputation record.
- **EXP-106** Eligibility rule revision records which population rule applied to each assignment/exposure and surfaces pre/post-revision comparability caveat.
- **EXP-107** Experiment duplication/fork generates a separate assignment namespace even when variants and metrics are copied.
- **EXP-108** Reopening a stopped experiment requires explicit new revision/restart policy; old subject history is not accidentally counted as a fresh independent sample.
- **EXP-109** Import of a definition with colliding stable experiment identity uses bind/fork/conflict workflow; it never silently overwrites active production configuration.
- **EXP-110** Version diff/approval view shows hypothesis, eligibility, variants, allocation, metrics, statistical profile, schedule and rollout-safety changes before publish.

# Group 11 — Privacy/consent/sensitive segmentation — EXP-111…EXP-121

- **EXP-111** Consent-dependent experiment assignment/delivery checks current consent category server-side or through the certified consent authority before treatment where required.
- **EXP-112** Consent refusal/withdrawal prevents new consent-dependent exposure collection and targeting according to the declared privacy profile.
- **EXP-113** Sensitive/protected attributes cannot be used for segmentation unless explicitly lawful/approved within product Policy; F08 does not infer them from proxies to evade restrictions.
- **EXP-114** Experiment result access is Policy-projected; unauthorized users cannot query subject-level assignments, sensitive segments or small-cohort breakdowns.
- **EXP-115** Minimum cohort/privacy threshold suppresses or aggregates small sensitive result cells according to the configured policy without falsifying global totals.
- **EXP-116** Subject identifiers in experiment data use the minimum necessary stable pseudonymous/reference form; secrets and unrelated profile data are not copied into exposure records.
- **EXP-117** Data export includes only authorized experiment/assignment/exposure fields and preserves consent/retention classifications.
- **EXP-118** Erasure/anonymization workflow follows F02/privacy ownership rules and records how experiment analytical integrity is preserved or degraded without claiming impossible full deletion from required aggregates.
- **EXP-119** Retention policies distinguish raw subject-level assignment/exposure data from aggregate experiment results and enforce expiry independently.
- **EXP-120** Dark-pattern experiment categories that manipulate consent, forced choice or protected user rights are rejected/blocked by policy rather than treated as ordinary optimization targets.
- **EXP-121** AI/MCP cannot request or derive prohibited sensitive segmentation through hidden prompts/tools; generated targeting plans remain subject to the same privacy review as human-authored plans.

# Group 12 — Feature rollout safety and non-experiment flags — EXP-122…EXP-132

- **EXP-122** Feature flag/rollout definition declares whether it is `experiment`, `operational rollout`, `release gate` or another supported profile; non-experiment flags do not generate fake causal statistics.
- **EXP-123** Rollout gate only affects feature delivery after underlying capability/Policy authorization; enabled flag cannot grant protected access.
- **EXP-124** Boolean flag has explicit default/fallback behavior for evaluator unavailable, malformed config or unknown subject context.
- **EXP-125** Percentage rollout uses deterministic cohort assignment where stickiness is claimed and cannot randomly flap on every request.
- **EXP-126** Targeted override priority between emergency off, explicit allow/deny cohort, percentage rollout and default is deterministic and documented.
- **EXP-127** Emergency kill/off rule has highest configured safety precedence and cannot be overridden by stale experiment assignment or lower-priority user targeting.
- **EXP-128** Dependency between flags is explicitly modeled/cycle-checked; enabling child flag cannot bypass disabled/required parent invariant.
- **EXP-129** Flag evaluation failure returns the configured safe fallback and records degraded observability without exposing internal configuration to the subject.
- **EXP-130** Server and client flag evaluation semantics are compatible/certified; privileged server-only feature authorization is never delegated to a manipulable client flag.
- **EXP-131** Rollout audit records who/what changed targeting, percentage, schedule or kill state, while Audit remains operational history rather than feature business truth.
- **EXP-132** Migration/clone/staging environment resets or quarantines production rollout/provider identifiers so staging cannot accidentally control live production cohorts.

# Group 13 — Multisite/tenant assignment isolation — EXP-133…EXP-143

- **EXP-133** Experiment/rollout definition ownership records network/site/tenant scope server-side; request-supplied site/tenant IDs do not grant cross-scope access.
- **EXP-134** Same experiment key on two sites/tenants remains isolated unless a deliberate network-shared experiment profile with global identity is configured.
- **EXP-135** Assignment hash includes required site/tenant/network namespace so identical user IDs across isolated tenants do not share variants accidentally.
- **EXP-136** Network-template experiment rollout to child sites records template revision plus site override/drift state and never silently overwrites local protected configuration.
- **EXP-137** Site-specific consent/Policy/eligibility rules remain effective even when the experiment definition originates from a network template.
- **EXP-138** Cross-site aggregate results are available only under explicit network Policy and cannot expose site-level sensitive small cohorts through drilldown.
- **EXP-139** Moving/cloning a site creates new environment/site experiment namespace unless the selected migration profile explicitly preserves safe historical analysis identity.
- **EXP-140** Site deletion/archive stops new assignments/exposures for that site while preserving/deleting historical data according to retention policy.
- **EXP-141** Shared network feature flag cannot accidentally override a child-site emergency kill/deny rule when precedence says local safety override wins.
- **EXP-142** Cross-tenant cache, exposure queue, assignment storage and result materialization keys are collision-tested under identical experiment/subject keys.
- **EXP-143** AI/MCP operating in one site/tenant cannot enumerate or modify another tenant’s experiment definitions/results without explicit cross-scope capability.

# Group 14 — Analytics data quality/late events/refunds — EXP-144…EXP-154

- **EXP-144** F08 result computation consumes F02 data-quality state and exposes dropped/duplicate/late/invalid event counts relevant to the experiment window.
- **EXP-145** Late exposure arriving after outcome is handled through explicit event-time rules and cannot silently reverse causal ordering without a warning/exclusion policy.
- **EXP-146** Late conversion within the accepted attribution/correction window updates materialized results idempotently and records freshness timestamp.
- **EXP-147** Conversion after the closed correction window is classified separately or ignored according to declared profile; dashboards show finalization boundary.
- **EXP-148** Refund/cancellation/reversal events adjust configured outcome metrics exactly once and retain gross/net distinction where relevant.
- **EXP-149** Duplicate conversion/refund transport events are deduplicated by F02/source identity before changing experiment aggregates.
- **EXP-150** Assignment/exposure metric join with missing identity is surfaced as unmatched data, not automatically assigned to control or discarded invisibly.
- **EXP-151** Instrumentation schema drift or unknown event version marks affected experiment data degraded and prevents blind winner recommendation.
- **EXP-152** Data outage/gap distinguishes `no events observed` from `collection unavailable`; zero conversions are not fabricated during telemetry failure.
- **EXP-153** Recompute from canonical assignment/exposure/metric events reproduces materialized experiment aggregates within declared deterministic/statistical tolerance.
- **EXP-154** Result export includes data-freshness, correction-window, sample, contamination and quality metadata required to interpret the numbers responsibly.

# Group 15 — High-traffic assignment/exposure performance — EXP-155…EXP-165

- **EXP-155** Assignment benchmark profile defines workload, hardware/backend/runtime, experiment count, variant count, subject identity profile and cache state before any latency claim is accepted.
- **EXP-156** 10K-subject deterministic assignment corpus completes within the future declared budget with zero assignment mismatches across repeat runs.
- **EXP-157** 100K-subject assignment corpus measures throughput, p50/p95/p99 latency and memory without replacing correctness assertions with aggregate speed only.
- **EXP-158** 1M-subject assignment/materialization candidate workload is measured only when the target architecture supports it; unsupported scale remains uncertified rather than extrapolated.
- **EXP-159** Hot experiment with many concurrent first-time assignments preserves deterministic stickiness/idempotency without lock amplification beyond the declared design.
- **EXP-160** Exposure ingestion under burst/redelivery load maintains dedupe correctness and records backpressure/drop/dead-letter behavior instead of silently losing events.
- **EXP-161** Large active experiment catalog evaluates only relevant eligible definitions/indexes and measures per-request overhead as catalog size grows.
- **EXP-162** Cache cold-start/revision invalidation benchmark measures assignment/placement impact and proves no cross-variant leakage under concurrency.
- **EXP-163** Results aggregation/recompute benchmark for large exposure/conversion volumes reports freshness lag and resource use; paper estimates do not count as certification.
- **EXP-164** Kill-switch/rollout propagation benchmark measures worst-case declared propagation across local/cache/edge layers and verifies stale treatment detection.
- **EXP-165** Performance certification is profile-specific; a local benchmark cannot be generalized to remote analytics/edge/provider adapters without separate evidence.

# Group 16 — Golden A/B/multivariate/rollout regression — EXP-166…EXP-176

- **EXP-166** Golden 50/50 A/B corpus maps fixed subject identities to deterministic expected control/treatment buckets identically across supported runtimes.
- **EXP-167** Golden unequal-allocation A/B corpus validates exact bucket boundaries, expected proportions and sticky assignments before/after restart.
- **EXP-168** Golden multivariate experiment validates stable assignment across multiple variants and correct handling when a variant is added under a new revision.
- **EXP-169** Golden eligibility/exclusion scenario proves excluded subject receives no experimental treatment and cannot bypass through direct placement/feature request.
- **EXP-170** Golden anonymous→login scenario validates declared stitching policy with no double exposure, identity leakage or conflicting persistent assignments.
- **EXP-171** Golden exposure/metric scenario proves assignment≠exposure, dedupe, contamination detection, conversion attribution and late-event correction semantics.
- **EXP-172** Golden rollout/kill-switch scenario proves percentage rollout stickiness, emergency-off precedence, stale-cache invalidation and safe fallback behavior.
- **EXP-173** Golden privacy/Multisite scenario proves consent withdrawal, sensitive cohort suppression and identical subject IDs across tenants remain isolated.
- **EXP-174** Golden data-quality/statistics scenario injects sample-ratio mismatch, telemetry gap, duplicate events and tiny sample, requiring caveated/no-winner output rather than false certainty.
- **EXP-175** Golden AI/MCP adversarial scenario attempts hidden sensitive targeting, metric swapping, forced winner, bypassed approval and privileged rollout; all prohibited paths are rejected while safe Draft recommendations remain possible.
- **EXP-176** Full cross-runtime deterministic regression covers experiment definition → eligibility → assignment → treatment/exposure → analytics metric → result interpretation → rollout/kill/stop → audit/export with identical canonical identities and no cross-user/site/tenant leakage.

## 4. Execution and certification rules

1. **Documented is not executed.** Current execution counter remains `0/176`.
2. A fixture passes only when its future executable harness records input corpus, environment/runtime, config revision, expected result, actual result and retained evidence artifact.
3. Statistical fixtures must additionally record the exact method/profile/library/runtime version and assumptions; screenshot-only dashboard evidence is insufficient.
4. Performance fixtures require actual measured workloads. Estimates, design calculations and paper benchmarks are not runtime certification.
5. Security/privacy/Multisite fixtures must be negative-tested for leakage/bypass, not inferred from normal-path success.
6. Rollout/kill-switch certification must include stale cache/edge/delayed job scenarios where the enabled architecture exposes those layers.
7. AI/MCP evidence must exercise the same Policy/approval/privacy boundaries as human-authored operations; no hidden privileged path may exist.
8. Any profile not covered by applicable passing fixtures remains uncertified even if another experiment profile passes.

## 5. Current evidence truth

- Namespace reserved: **EXP-001…EXP-176**.
- Detailed fixture text: **176/176 documented**.
- Executed: **0/176**.
- Runtime certification: **0**.
- Production implementation authorization: **NOT GRANTED / 0/56**.

This protocol is planning/evidence specification only and authorizes no runtime, implementation, dependency, analytics, provider, AI/MCP, build, test or deployment action.