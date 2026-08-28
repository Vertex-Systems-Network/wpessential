# WPEssential — Analytics, Event Tracking & Journey Intelligence Executable Evidence Protocol

Status: **Accepted evidence design / execution pending / no development authorization**  
Date: 2026-08-29

Namespace: **ANL-001…ANL-176**  
Executed: **0/176**  
Runtime certification: **0**

## 1. Purpose

This protocol certifies F02 as a durable behavioral/application analytics system distinct from the operational Event Bus and Audit Log.

Analytics events are observational facts/claims subject to schema, consent and source quality. They never become authorization/business authority merely because they were recorded.

# Group 1 — Event catalog/schema/versioning — ANL-001…011
- **ANL-001** valid versioned event definition publishes with stable key.
- **ANL-002** duplicate stable event key with incompatible schema requires revision/migration.
- **ANL-003** required/optional typed properties validate correctly.
- **ANL-004** enum/range/length constraints reject invalid payload.
- **ANL-005** unknown event key follows configured reject/quarantine policy.
- **ANL-006** future unknown schema version does not silently coerce to current.
- **ANL-007** source owner and provenance recorded.
- **ANL-008** dimension/measure eligibility follows schema.
- **ANL-009** prohibited secret-class property cannot be enabled for collection.
- **ANL-010** high-cardinality property warning/limit activates.
- **ANL-011** event schema export/import preserves version identity without secrets.

# Group 2 — Server/browser/import collection — ANL-012…022
- **ANL-012** trusted server event validates and stores once.
- **ANL-013** browser event accepts only client-settable fields.
- **ANL-014** client attempts to set server-only authority field and is rejected/ignored explicitly.
- **ANL-015** REST/SDK source authenticates/rate-limits according to profile.
- **ANL-016** historical import preserves event_time separately from received_time.
- **ANL-017** oversized event payload rejected before expensive processing.
- **ANL-018** malformed JSON/encoding fails typed validation.
- **ANL-019** bot/internal role exclusion policy works.
- **ANL-020** sampling profile records sampling metadata.
- **ANL-021** collection endpoint outage/retry does not duplicate accepted event.
- **ANL-022** unknown collector/source is not trusted as business authority.

# Group 3 — Anonymous/session/auth identity — ANL-023…033
- **ANL-023** new anonymous session receives scoped pseudonymous identifier.
- **ANL-024** session timeout starts new session according to configured window.
- **ANL-025** anonymous ID rotation policy works without impossible cross-device claim.
- **ANL-026** authenticated login links permitted anonymous history according to policy.
- **ANL-027** cross-device identity links only through authoritative authentication/accepted identifier mapping.
- **ANL-028** logout ends authenticated association for future events according to policy.
- **ANL-029** two users on shared browser do not merge protected profiles incorrectly.
- **ANL-030** guest→user merge handles duplicate same event/session deterministically.
- **ANL-031** identity deletion/anonymization breaks retained link as configured.
- **ANL-032** imported external identity map cannot grant WordPress authorization.
- **ANL-033** site/network identity scope is explicit in Multisite.

# Group 4 — Consent/privacy/retention — ANL-034…044
- **ANL-034** required consent absent prevents configured behavioral event collection.
- **ANL-035** consent granted after page load affects only allowed subsequent/replayed events per policy.
- **ANL-036** consent withdrawal stops future collection and initiates configured linkage/retention action.
- **ANL-037** PII-class property requires explicit permitted profile.
- **ANL-038** Vault/password/session/reset/security tokens never enter analytics payload.
- **ANL-039** IP handling follows off/truncated/hashed accepted profile rather than raw-by-default assumption.
- **ANL-040** raw-event retention expiry deletes/aggregates eligible data.
- **ANL-041** aggregate retention can outlive raw events only under documented privacy semantics.
- **ANL-042** user data export includes eligible analytics linkage/data without cross-user leak.
- **ANL-043** user erase/anonymize handles raw and derived linkage truthfully.
- **ANL-044** small sensitive cohort privacy threshold prevents unsafe drill-down.

# Group 5 — Deduplication/time/late data — ANL-045…055
- **ANL-045** duplicate event ID inside dedupe window stored once.
- **ANL-046** same business action with different event IDs follows source idempotency/dedupe profile.
- **ANL-047** event_time earlier than received_time handled as valid late event within tolerance.
- **ANL-048** event beyond late tolerance follows reject/quarantine/backfill policy.
- **ANL-049** future-dated event beyond clock-skew allowance rejected/quarantined.
- **ANL-050** timezone conversion leaves canonical event time unambiguous.
- **ANL-051** DST transition does not duplicate/miss canonical UTC ordering.
- **ANL-052** source replay after outage does not double-count deduped event.
- **ANL-053** duplicate browser retry and server confirmation reconcile per event identity profile.
- **ANL-054** correction/backfill updates materialized metrics according to versioned correction rules.
- **ANL-055** event ordering uncertainty is not falsely presented as causal sequence.

# Group 6 — Metrics — ANL-056…066
- **ANL-056** count metric returns correct bounded result.
- **ANL-057** sum/average over typed numeric field handles null policy.
- **ANL-058** distinct count follows canonical identity field.
- **ANL-059** rate metric numerator/denominator filters are independently validated.
- **ANL-060** ratio divide-by-zero follows explicit policy.
- **ANL-061** percentile metric only available on backend/profile that certifies it.
- **ANL-062** currency metric refuses silent cross-currency sum without normalization profile.
- **ANL-063** timezone/time-grain boundaries are reproducible.
- **ANL-064** late data updates metric freshness/correction metadata.
- **ANL-065** permissioned metric hides protected dimensions/results.
- **ANL-066** metric definition revision does not silently rewrite historical interpretation without version metadata.

# Group 7 — Funnels — ANL-067…077
- **ANL-067** ordered two-step funnel conversion computed correctly.
- **ANL-068** multi-step funnel enforces conversion window.
- **ANL-069** optional step does not block conversion.
- **ANL-070** exclusion event removes/ends path according to definition.
- **ANL-071** same-session requirement differs correctly from same-user requirement.
- **ANL-072** repeat event handling respects first/last/every occurrence profile.
- **ANL-073** segment filter applies before funnel aggregation without protected-count leak.
- **ANL-074** anonymous→authenticated stitch does not double-count converted actor.
- **ANL-075** refund/cancellation adjustment uses explicit downstream event, not inferred deletion.
- **ANL-076** late/backfilled step updates funnel with freshness notice.
- **ANL-077** funnel drop-off is observational and not labeled proven cause.

# Group 8 — Cohorts/retention — ANL-078…088
- **ANL-078** acquisition/event cohort assignment uses correct cohort date.
- **ANL-079** weekly/monthly retention buckets respect timezone definition.
- **ANL-080** static cohort remains snapshot despite later profile changes.
- **ANL-081** dynamic cohort updates membership as conditions change.
- **ANL-082** return event counted once per defined bucket/profile.
- **ANL-083** reactivation profile distinguishes return after lapse.
- **ANL-084** cohort comparison enforces minimum privacy threshold.
- **ANL-085** deleted/anonymized identity no longer appears in prohibited linked cohort drill-down.
- **ANL-086** late events can adjust historical cohort metric with correction metadata.
- **ANL-087** cross-site cohort aggregation requires explicit network authorization/profile.
- **ANL-088** cohort retention output identifies definition/version/freshness.

# Group 9 — Journeys/path exploration — ANL-089…099
- **ANL-089** start-event path follows bounded max depth.
- **ANL-090** end-event path terminates correctly.
- **ANL-091** noisy event groups can be collapsed without changing underlying event truth.
- **ANL-092** max branching caps high-cardinality path explosion.
- **ANL-093** path timeout separates unrelated long-gap activity.
- **ANL-094** session-scope path differs from authenticated-user path.
- **ANL-095** sampling is visible in path metadata.
- **ANL-096** protected event/property cannot appear in unauthorized path drill-down.
- **ANL-097** same timestamp ties use deterministic/explicit ordering uncertainty.
- **ANL-098** imported historical path preserves source provenance.
- **ANL-099** AI journey summary cites observed evidence and does not invent unseen steps.

# Group 10 — Attribution — ANL-100…110
- **ANL-100** first-touch attribution assigns eligible first touch.
- **ANL-101** last-touch assigns eligible final touch before conversion.
- **ANL-102** last-non-direct skips direct according to explicit channel mapping.
- **ANL-103** linear attribution distributes deterministic weights.
- **ANL-104** position-based profile weights sum correctly.
- **ANL-105** custom deterministic weights come from certified F04 definition.
- **ANL-106** attribution window excludes out-of-window touches.
- **ANL-107** authenticated cross-session linking follows identity policy.
- **ANL-108** refund/cancel adjustment follows configured business event.
- **ANL-109** missing channel/source is classified unknown rather than fabricated.
- **ANL-110** attribution output is labeled modeled attribution, not causal proof; experiment lift remains separate.

# Group 11 — Data quality/anomalies — ANL-111…121
- **ANL-111** unknown event key count visible.
- **ANL-112** schema violation rate visible by source/version.
- **ANL-113** dropped event count/reason observable.
- **ANL-114** duplicate rate visible.
- **ANL-115** late event rate/freshness lag visible.
- **ANL-116** high-cardinality dimension warning triggers.
- **ANL-117** missing identity/session quality warning triggers.
- **ANL-118** consent-rejected event attempts counted only in privacy-safe aggregate if configured.
- **ANL-119** source schema drift detected.
- **ANL-120** abnormal volume spike/drop creates bounded anomaly/alert candidate.
- **ANL-121** AI anomaly explanation distinguishes evidence from hypothesis.

# Group 12 — Storage/materialization/cache — ANL-122…132
- **ANL-122** raw store and materialized aggregate are separate data products.
- **ANL-123** aggregate rebuild from canonical retained source matches expected fixture.
- **ANL-124** downsampling preserves declared metric semantics.
- **ANL-125** cache key includes metric definition/version/timezone/filter/Policy dimensions.
- **ANL-126** invalidation after corrected late data updates relevant cached result.
- **ANL-127** stampede protection does not serve cross-tenant protected result.
- **ANL-128** unavailable analytics backend degrades reporting without blocking core site business operations.
- **ANL-129** backend write partial failure reconciles accepted vs dropped events truthfully.
- **ANL-130** migration between storage profiles preserves counts/checksums/sample comparisons.
- **ANL-131** retention prune and materialization race does not corrupt aggregate truth.
- **ANL-132** Audit Log/Event Bus is not silently reused as the analytics warehouse.

# Group 13 — Authorization/tenant isolation — ANL-133…143
- **ANL-133** dashboard/report read requires capability and resource Policy.
- **ANL-134** actor can view aggregate but not restricted row/event details.
- **ANL-135** protected dimension is removed or bucketed per policy.
- **ANL-136** query/filter parameter cannot escalate tenant/site scope.
- **ANL-137** export applies same Policy as interactive report.
- **ANL-138** scheduled report resolves recipients and data under correct scope.
- **ANL-139** shareable dashboard link cannot become anonymous protected analytics access.
- **ANL-140** cache hit rechecks or keys authorization context appropriately.
- **ANL-141** AI Prompt context uses only analytics summaries allowed to actor/task.
- **ANL-142** REST/MCP analytics Ability exposure remains explicit and permissioned.
- **ANL-143** role downgrade/revoke removes future protected analytics access promptly.

# Group 14 — Multisite/site lifecycle — ANL-144…154
- **ANL-144** site-scoped event includes durable site ownership.
- **ANL-145** current-blog switch cannot rewrite event ownership.
- **ANL-146** network aggregate explicitly authorizes target site set.
- **ANL-147** site admin cannot query another site's raw analytics by arbitrary site ID.
- **ANL-148** network metric merges site results with explicit semantic/currency/timezone rules.
- **ANL-149** new-site tracking defaults follow network template/consent policy.
- **ANL-150** site deletion retention/anonymization follows lifecycle policy.
- **ANL-151** site clone defaults do not duplicate historical visitor identity blindly.
- **ANL-152** shared network event schema can be inherited while values remain site-owned.
- **ANL-153** network backfill fan-out uses bounded Jobs/per-site failure reporting.
- **ANL-154** noisy site cannot exhaust entire network ingestion/query budget without configured policy.

# Group 15 — Scale/performance — ANL-155…165
- **ANL-155** 100K-event ingestion profile baseline.
- **ANL-156** 1M-event ingestion/profile benchmark candidate.
- **ANL-157** 10M-event local/external topology benchmark candidate.
- **ANL-158** 100M-event external-warehouse profile remains separate certification class.
- **ANL-159** high-cardinality dimension query budget enforced.
- **ANL-160** funnel over large window uses bounded plan/materialization where required.
- **ANL-161** path exploration max-depth/branch budget prevents memory explosion.
- **ANL-162** scheduled aggregates/backfills obey Job backpressure/fairness.
- **ANL-163** dashboard does not issue N+1 per-widget raw scans.
- **ANL-164** ingestion endpoint rate/backpressure protects transactional WordPress workload.
- **ANL-165** retention/prune jobs remain resumable and bounded.

# Group 16 — Golden/regression scenarios — ANL-166…176
- **ANL-166** anonymous product-view→signup→purchase journey stitches under consent and yields correct funnel.
- **ANL-167** CRM lead lifecycle events produce approved metric/cohort without becoming lead authority.
- **ANL-168** LMS enrollment/lesson/completion events produce retention/funnel with protected learner data policy.
- **ANL-169** booking search→hold→confirm/cancel journey handles duplicate/retry events.
- **ANL-170** Woo adapter browse→cart→checkout→order/refund events produce attribution with HPOS-safe source semantics.
- **ANL-171** privacy withdrawal/erase updates raw/linkable data and derived reporting according to policy.
- **ANL-172** late/backfilled historical batch corrects metrics/funnels with visible freshness/change metadata.
- **ANL-173** provider/collector outage recovers without duplicate inflation.
- **ANL-174** Multisite network dashboard aggregates authorized sites without cross-site raw leak.
- **ANL-175** AI Prompt generates a metric/funnel definition, deterministic validation catches unsupported field, and no invalid definition publishes.
- **ANL-176** end-to-end event catalog→collection→identity→metric→funnel→cohort→journey→attribution→quality→privacy/export golden lifecycle.

## Stop-the-line

Certification stops on:
- cross-user/site/tenant analytics leakage;
- secret/security-token collection;
- client event treated as authorization/business authority;
- consent bypass;
- raw/derived erase falsely reported complete;
- duplicate/retry inflation inconsistent with declared semantics;
- cache returning unauthorized protected aggregate/detail;
- attribution/correlation presented as proven causation;
- partial ingestion/backend failure reported as complete success.

## Current truth

- ANL documented: **176**.
- ANL executed: **0/176**.
- F02 runtime certification: **0**.
- Final analytics storage topology/backend certification remains evidence-gated.
- No event collection, cookie/session creation, analytics DB/table, warehouse, dashboard query, attribution run, AI call, benchmark or runtime test occurred.