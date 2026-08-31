# WPEssential — Market Intelligence Radar Executable Evidence Protocol

Status: **Exact planning evidence / NOT EXECUTED / no development authorization**  
Date: 2026-08-29  
Work package: **WP113**  
Namespace: **MIR-001…MIR-176**  
Documented: **176/176**  
Executed: **0/176**  
Runtime certification: **0**

## Purpose

Freeze exact future evidence for S08 Market Intelligence & Capability Radar, preserving the 16 groups fixed by the market-expansion evidence master plan and `AI/MARKET-INTELLIGENCE-CAPABILITY-RADAR.md`.

## Truth boundaries

- Market signal/trend score ≠ product decision or development approval.
- Popularity/install/review movement ≠ proof of architecture quality, demand causality or correctness.
- Secondary commentary/reviews are discovery/pain signals and require primary verification before canonical factual planning claims.
- Daily schedule means scheduled cadence with platform delay semantics, not exact 24-hour guarantee.
- Radar may create Draft research/planning artifacts only under configured repository permissions; it cannot auto-merge, modify runtime/source or grant consent.
- AI summaries/capability extraction are non-authoritative until source/provenance validation.
- Source failures/missing data remain unknown, never fabricated as zero/no-change.

---

## Group 1 — WordPress.org API popular/new/recommended paging — MIR-001…011

- **MIR-001** — Popular-plugin query records endpoint/query parameters/page/per-page/retrieval time and returns only source-provided results.
- **MIR-002** — New-plugin query uses explicit ordering/filter contract and cannot infer “new” from local first-seen time when source exposes different semantics.
- **MIR-003** — Recommended/beta/category/tag query records exact source mode so results from different feeds are not merged as one ranking silently.
- **MIR-004** — Multi-page retrieval follows source paging metadata until configured page/result budget; missing later pages are not treated as complete catalog.
- **MIR-005** — Duplicate plugin slug across pages is deduped by stable source identity while preserving first/latest observed metadata provenance.
- **MIR-006** — API returns partial/empty page unexpectedly; scan records source anomaly and does not infer marketplace has no plugins.
- **MIR-007** — Rate-limit/HTTP failure during paging preserves successfully fetched pages plus incomplete state and retry cursor.
- **MIR-008** — Active-install/review/rating fields absent from API remain unknown and cannot be synthesized from ranking position.
- **MIR-009** — Search/tag/category terms are bounded/encoded and cannot become arbitrary endpoint/SSRF target.
- **MIR-010** — Cached API page is labeled with fetched-at/freshness and is not presented as live when cache is reused.
- **MIR-011** — API schema field change/unknown field does not break scan silently; parser captures compatible fields and raises version/drift diagnostic.

## Group 2 — plugin information, version and changelog snapshots — MIR-012…022

- **MIR-012** — Plugin information snapshot pins slug, version, last-updated, tested/requires fields and retrieval time from official source.
- **MIR-013** — Version string change creates a new snapshot and does not overwrite historical version metadata.
- **MIR-014** — Changelog extraction links entries to source version/date where available and labels unstructured/ambiguous entries accordingly.
- **MIR-015** — Meaningful feature/security/compatibility change is separated from formatting/readme text change by explicit classifier evidence.
- **MIR-016** — Missing changelog does not become “no changes”; status remains unavailable/unknown.
- **MIR-017** — Plugin marked closed/removed/unavailable is recorded as source state, not automatically security-malicious or product-obsolete.
- **MIR-018** — Tested/requires WordPress/PHP metadata movement triggers compatibility signal without assuming runtime compatibility beyond declared source data.
- **MIR-019** — Version rollback/decrease or malformed version creates anomaly instead of being accepted as ordinary newer release.
- **MIR-020** — Snapshot hash covers normalized relevant metadata so non-material presentation changes can be distinguished from semantic source changes.
- **MIR-021** — Cached plugin-info snapshot expires/refreshes under freshness policy before any “current/latest” report.
- **MIR-022** — Conflicting version between WordPress.org and linked official repository is preserved as conflict with both provenance sources, not silently reconciled.

## Group 3 — SVN, Trac and GitHub official source resolution — MIR-023…033

- **MIR-023** — Official repository link from trusted plugin metadata/vendor page is recorded as candidate source with provenance before fetching.
- **MIR-024** — WordPress.org SVN plugin path is resolved using exact plugin slug and only public repository scope.
- **MIR-025** — SVN tag/trunk revision used for observation is pinned so later commits cannot rewrite historical extraction.
- **MIR-026** — Trac ticket/change source is classified issue/development evidence, not automatically released product behavior.
- **MIR-027** — GitHub repository is accepted as official only when linked/validated through authoritative source or explicit configured watchlist; name similarity alone is insufficient.
- **MIR-028** — GitHub release/tag observation pins repository/ref/date and differentiates release notes from actual WordPress.org deployed version.
- **MIR-029** — Fork/mirror/unofficial GitHub repository remains secondary/unverified unless governance explicitly accepts it as evidence source.
- **MIR-030** — Private repository is never scanned without explicit connected authorization; public radar cannot infer access.
- **MIR-031** — Source code inspection extracts architecture/API behavior only at allowed abstraction and does not copy proprietary/incompatible implementation.
- **MIR-032** — Repository/SVN/Trac unavailable or rate-limited yields source-unavailable status and does not fabricate architecture/version facts.
- **MIR-033** — Official-source relationship changes over time are versioned; stale repository mapping cannot silently remain authoritative forever.

## Group 4 — WordPress core, dev-note and standards feeds — MIR-034…044

- **MIR-034** — Core Developer Blog/Make/Core/dev-note item is stored with publication/event/version context and official source provenance.
- **MIR-035** — Core API introduction is mapped to exact target WordPress version/status (proposed/merged/released) rather than “available now” by default.
- **MIR-036** — Deprecation/removal notice records affected API/version/timeline and creates compatibility signal without assuming WPE currently uses it.
- **MIR-037** — Developer.WordPress.org documentation update is distinguished from actual core code/release change where evidence differs.
- **MIR-038** — Standards/RFC/vendor protocol update records normative source/version and relevance before candidate extraction.
- **MIR-039** — Security/core release advisory receives priority-review signal regardless of ordinary market score when relevance may affect WPE.
- **MIR-040** — Beta/RC proposal is labeled prerelease and cannot be treated as guaranteed stable API until released/accepted.
- **MIR-041** — Core capability that duplicates a planned WPE primitive triggers dedupe/reassessment candidate, not automatic removal/rewrite of accepted plan.
- **MIR-042** — Core feature ownership/version differences across supported WP floor are recorded so fallback/coexistence planning can be requested.
- **MIR-043** — Feed/source publication failure is explicit and daily report marks coverage incomplete.
- **MIR-044** — Multiple official notes on same feature are deduped/linked by capability/version while retaining each source provenance.

## Group 5 — support and review signal classification — MIR-045…055

- **MIR-045** — Public support thread is classified anecdotal pain signal with plugin/version/date context where available, not factual proof of universal defect.
- **MIR-046** — Review complaint/praise remains user opinion and cannot directly modify capability score without configured evidence weighting.
- **MIR-047** — Duplicate support topics describing same symptom are clustered while preserving unique source count and avoiding artificial trend inflation.
- **MIR-048** — One highly active/noisy thread cannot dominate trend score without weighting/corroboration policy.
- **MIR-049** — Support issue resolved/fixed in newer release is marked historical/resolved where source evidence supports it.
- **MIR-050** — Security-sensitive support report is escalated for primary advisory/code verification and not republished as confirmed vulnerability prematurely.
- **MIR-051** — Spam/irrelevant/off-topic review/support content is classified noise and excluded from capability/pain extraction.
- **MIR-052** — Language/locale variation can be clustered semantically but source text meaning/confidence remains explicit.
- **MIR-053** — Personal/private data in support/review source is minimized/redacted from internal artifacts beyond necessary public citation metadata.
- **MIR-054** — Deleted/inaccessible support source remains historical unavailable signal and does not become stronger because it cannot be verified.
- **MIR-055** — Pain-theme summary distinguishes occurrence volume, recency and evidence quality; it never claims causality or market-wide prevalence without data.

## Group 6 — change detection, hash and dedupe — MIR-056…066

- **MIR-056** — First-seen candidate stores source identity/snapshot hash and produces `new_candidate` only once under stable dedupe key.
- **MIR-057** — Semantically unchanged snapshot with formatting/order noise does not generate repeated change event.
- **MIR-058** — Version/changelog/API field change creates deterministic change event linked to previous/new snapshot hashes.
- **MIR-059** — Same source change observed through two ingestion channels dedupes to one capability-change identity while preserving channel provenance.
- **MIR-060** — Plugin rename/slug change is not automatically merged with old identity unless authoritative relationship evidence exists.
- **MIR-061** — Reverted source change records new event/history rather than deleting prior observation.
- **MIR-062** — Hash algorithm/version is recorded; hash equality means normalized snapshot equality only, not semantic product equivalence beyond normalization contract.
- **MIR-063** — Cache stale response does not create false “no change” when freshness policy requires live validation before report.
- **MIR-064** — Concurrent scans processing same snapshot use idempotency key/unique identity and do not emit duplicate issues/drafts.
- **MIR-065** — Clock/order skew between sources uses retrieval/source version metadata instead of naïve latest-timestamp overwrite.
- **MIR-066** — Corrupt/partial snapshot is quarantined and cannot replace last known good snapshot as canonical current evidence.

## Group 7 — capability extraction — MIR-067…077

- **MIR-067** — New changelog feature is extracted as candidate capability with exact source claim/citation and confidence.
- **MIR-068** — Marketing phrase without concrete behavior remains weak/unresolved capability and does not produce precise invented option set.
- **MIR-069** — API/CLI/integration capability is extracted only when official source documents or code observation supports it.
- **MIR-070** — Multisite indication is recorded as supported/unsupported/unknown based on source evidence and not assumed.
- **MIR-071** — Security/privacy implication is extracted separately from user feature and can raise negative burden/priority-review flags.
- **MIR-072** — Free/Pro/business-model fact is separated from functional capability so paywall differences do not become architecture requirements automatically.
- **MIR-073** — Removed/deprecated competitor feature produces capability-retirement signal and does not remain “new opportunity” indefinitely.
- **MIR-074** — Multiple source claims for same capability are clustered with provenance and contradictory semantics remain unresolved.
- **MIR-075** — AI extraction output is validated against source snippets/claim schema and unsupported precise claims are rejected.
- **MIR-076** — Proprietary implementation detail is not copied into capability artifact; behavior/interface abstraction only.
- **MIR-077** — Capability extraction failure is logged with source and can be reprocessed later; missing extraction is not treated as no capability.

## Group 8 — WPE overlap and gap mapping — MIR-078…088

- **MIR-078** — Candidate capability maps against current 56-surface/option catalog by exact owner semantics before new-module recommendation.
- **MIR-079** — Existing direct match is classified `existing exact match` with owning spec/evidence reference and lowers duplicate gap score.
- **MIR-080** — Small missing behavior under existing owner maps `option enhancement`, not standalone module.
- **MIR-081** — Cross-cutting reusable capability maps `shared-service enhancement` when multiple consumers exist.
- **MIR-082** — External source-truth integration maps adapter/provider profile rather than new local domain engine.
- **MIR-083** — Composition of existing modules/foundations can satisfy candidate and is recorded as Blueprint/composition opportunity.
- **MIR-084** — Security-rejected primitive maps reject/watch with rationale and cannot be promoted by market popularity score.
- **MIR-085** — Planned-but-unimplemented capability still counts as planned overlap; runtime absence does not make it a product-planning gap automatically.
- **MIR-086** — Historical rejected/duplicate decision is consulted so Radar does not repeatedly re-open identical candidate without new evidence.
- **MIR-087** — Uncertain mapping remains `requires S07 audit/unknown`, not forced into module/option class.
- **MIR-088** — WPE catalog revision is pinned for overlap result; material catalog changes trigger remap before current report.

## Group 9 — scoring, thresholds and watchlist — MIR-089…099

- **MIR-089** — Score computes from declared weights/normalized inputs and records every component contribution for explainability.
- **MIR-090** — Missing demand/install/review input is treated as unknown according to scoring policy, not zero evidence by default unless explicitly defined.
- **MIR-091** — Negative security/privacy/maintenance/external-authority burden reduces/qualifies score without automatically rejecting strategically necessary candidate.
- **MIR-092** — 80–100 threshold triggers S07 Draft audit only; it does not accept product scope or authorize implementation.
- **MIR-093** — 60–79 produces candidate report/lightweight map only under default policy.
- **MIR-094** — 40–59 enters watchlist and preserves refresh cadence/source history.
- **MIR-095** — Below-40 archive/no-action preserves provenance and can be reconsidered on meaningful new evidence.
- **MIR-096** — Severe security/core compatibility signal can bypass ordinary score threshold for priority review but still not bypass governance.
- **MIR-097** — Configured weight/threshold revision changes cause re-score with policy version recorded; historical score remains preserved.
- **MIR-098** — One noisy popularity/review metric is capped/normalized so it cannot dominate all architecture/safety dimensions unless policy explicitly chooses that.
- **MIR-099** — Score is labeled triage heuristic; report/UI cannot state “approved/recommended build” solely from numeric threshold.

## Group 10 — S07 handoff and full Draft audit — MIR-100…110

- **MIR-100** — High-priority candidate handoff creates S07 request with source manifest, capability claims, overlap map, score and exact Radar snapshot references.
- **MIR-101** — Duplicate active S07 audit for same candidate/change identity is reused/updated rather than creating second work item.
- **MIR-102** — S07 independently revalidates repository current state and cannot rely on stale Radar overlap map as canonical truth.
- **MIR-103** — S07 research can broaden/contradict Radar evidence; final Draft audit preserves discrepancy/provenance rather than forcing Radar conclusion.
- **MIR-104** — Radar candidate does not enter module denominator until S07/governance ADR acceptance.
- **MIR-105** — Candidate mapped to existing option produces expansion Draft, not artificial new-module proposal.
- **MIR-106** — Candidate needing provider/external authority produces adapter/certification planning, not fake local fact engine.
- **MIR-107** — Security-rejected candidate handoff can document rejection/safe alternative and does not create implementation work.
- **MIR-108** — S07 full audit failure/incomplete research leaves candidate pending/inconclusive and Radar does not mark planning complete.
- **MIR-109** — Repeated new market evidence updates existing S07 candidate provenance/diff and may reopen review without erasing prior decision.
- **MIR-110** — S07 acceptance/completion state is consumed as planning metadata only; it cannot become development consent for Radar/S07/runtime.

## Group 11 — daily report, artifacts and issues — MIR-111…121

- **MIR-111** — Daily report lists new high-priority candidates with source/score/provenance and distinguishes zero results from source failure.
- **MIR-112** — Changed watchlist entries include previous/current snapshot/change reason rather than full noisy raw source dump.
- **MIR-113** — WordPress core/platform/security/compatibility section prioritizes event relevance and exact version/source.
- **MIR-114** — User-pain section clearly labels anecdotal themes and occurrence/source quality.
- **MIR-115** — Promoted/demoted candidate section explains score/policy/evidence delta instead of just number change.
- **MIR-116** — Duplicate/rejected findings remain visible enough to prevent rework while not flooding daily report with unchanged archive.
- **MIR-117** — Planning drafts created section links only Draft research/S07 artifacts and never claims runtime code changes.
- **MIR-118** — Stale-source section identifies sources requiring refresh and cannot present stale cached facts as live.
- **MIR-119** — Scan-failure section records source/network/rate/parser failures and coverage gaps; report completion remains partial when material sources failed.
- **MIR-120** — Markdown/JSON artifact contains no connector secrets/private credentials/full unnecessary source payloads and is access-scoped appropriately.
- **MIR-121** — Daily/weekly issue update is idempotent for the reporting period/candidate identity and does not create one duplicate issue per retry.

## Group 12 — optional Draft planning PR permissions — MIR-122…132

- **MIR-122** — Default Radar automation cannot open/write a Draft PR unless repository planning-write profile is explicitly enabled.
- **MIR-123** — Enabled Draft PR writes only allowed planning/research paths such as `docs/RESEARCH/CANDIDATES/**` under configured branch.
- **MIR-124** — Radar cannot modify PHP/JS/runtime/source/build/deploy directories through its Draft PR capability.
- **MIR-125** — Draft PR remains draft and cannot be marked ready/merged automatically under default Radar policy.
- **MIR-126** — Existing candidate Draft PR is updated/idempotently reused rather than opening duplicate PR for each daily scan.
- **MIR-127** — Repository file write uses current blob/base revision and detects concurrent human/planner edits instead of overwriting.
- **MIR-128** — Branch protection/ruleset state unavailable to connector remains unknown; Radar cannot weaken or bypass protections.
- **MIR-129** — PR body/artifacts preserve source citations and explicitly state no development authorization/runtime code.
- **MIR-130** — Private repository/source evidence is not copied into public PR unless sharing policy explicitly permits it.
- **MIR-131** — Write/PR failure is reported and local/report artifact remains source of attempted Draft state; failure cannot be announced as successful sync.
- **MIR-132** — Disabling Draft-PR option immediately prevents future repository writes while read-only Radar reports may continue under authorization.

## Group 13 — cron schedule, default branch and idempotency — MIR-133…143

- **MIR-133** — Planned daily schedule uses documented non-round minute/cadence and is explicitly marked not installed before development consent.
- **MIR-134** — GitHub scheduled workflow execution occurs from default-branch workflow definition; non-default planning branch design is not mistaken for active schedule.
- **MIR-135** — Platform scheduler delay records actual started/completed time; “daily” is cadence intent, not exact-to-second guarantee.
- **MIR-136** — Missed/disabled scheduled run is visible in next report/health and cannot be synthesized as successful no-change scan.
- **MIR-137** — Manual rerun for same logical period uses run/idempotency identity to avoid duplicate candidate/report/issues.
- **MIR-138** — Overlapping daily runs are serialized/deduped or partitioned safely so same source snapshot does not race duplicate updates.
- **MIR-139** — Workflow update/version is recorded with Run provenance so behavior changes are traceable.
- **MIR-140** — Default branch renamed/moved requires schedule/workflow reconciliation and cannot silently stop Radar while status remains healthy.
- **MIR-141** — Scheduled job has bounded timeout/source/result budgets and cannot run indefinitely due one source.
- **MIR-142** — Job cancellation/timeout leaves partial scan/report state and preserves resumable/retry identifiers where supported.
- **MIR-143** — Enabling executable schedule itself is an implementation/runtime action blocked by ADR-0014 until explicit scoped consent.

## Group 14 — network, rate-limit, cache and source failures — MIR-144…154

- **MIR-144** — Per-source/domain/API rate limit uses configured budget/backoff and honors Retry-After where available.
- **MIR-145** — Global network concurrency budget prevents Radar from overwhelming external sources or shared runner resources.
- **MIR-146** — Cached source metadata stores freshness/ETag/Last-Modified where supported and validates stale/current state honestly.
- **MIR-147** — HTTP 304/no-change is tied to correct cached snapshot identity and cannot be interpreted without prior valid body.
- **MIR-148** — Timeout/DNS/TLS/5xx failure remains source failure/unknown and does not produce false candidate removal/no-change.
- **MIR-149** — 401/403/private access failure is explicit and cannot trigger credential scraping/bypass attempts.
- **MIR-150** — Parser/schema drift quarantines malformed source response and preserves last known good snapshot separately.
- **MIR-151** — Partial multi-source failure produces degraded report with exact failed sources/coverage rather than all-or-nothing fabricated completeness.
- **MIR-152** — Safe HTTP blocks loopback/private/link-local/metadata endpoints and revalidates redirects where Radar fetches arbitrary source links.
- **MIR-153** — Cache poisoning/cross-source key collision is prevented by normalized source/profile/version identity; one source cannot overwrite another snapshot.
- **MIR-154** — Retry storm is bounded with jitter/backoff/dead-letter/review semantics and cannot multiply Draft issues/PR writes.

## Group 15 — AI summaries, provenance and secret isolation — MIR-155…165

- **MIR-155** — AI changelog summary receives only authorized source text and output links every substantive claim to source snapshot/provenance.
- **MIR-156** — AI clustering merges similar capability themes only as analysis; original source claims remain independently traceable.
- **MIR-157** — AI cannot invent install/review/version/date data absent from source; deterministic validator leaves unknown fields unknown.
- **MIR-158** — Prompt injection in plugin README/changelog/support issue is untrusted data and cannot alter Radar tools/repository permissions.
- **MIR-159** — AI provider/API credentials remain Connector/Vault-owned and are never included in prompt artifacts, reports, issues or PRs.
- **MIR-160** — Private connector/source data is excluded from external model context unless explicit data-flow policy authorizes it.
- **MIR-161** — AI model/provider/version metadata is recorded where relevant to evaluation/drift; summary is not treated as source evidence itself.
- **MIR-162** — Invalid/low-confidence AI extraction routes to review/unknown rather than being auto-scored as precise capability.
- **MIR-163** — AI cannot accept/reject module, merge PR, modify runtime code or grant development consent through Radar tool chain.
- **MIR-164** — AI outage preserves deterministic source collection/change detection/reporting subset where supported and marks AI-derived sections unavailable.
- **MIR-165** — AI/MCP attribution is audit metadata only and never substitutes for repository/owner authorization.

## Group 16 — false trend, noise, security, VCS, recovery and regression — MIR-166…176

- **MIR-166** — Temporary install/review spike from one plugin/source cannot automatically become category trend without configured corroboration/time window.
- **MIR-167** — Bot/spam/review manipulation/noisy source pattern is downweighted/flagged and cannot dominate scoring as trusted demand evidence.
- **MIR-168** — Category/tag taxonomy change/reclassification does not create false “new category” trend without historical normalization.
- **MIR-169** — Security advisory candidate requires source/relevance/version mapping and does not label unaffected current versions vulnerable automatically.
- **MIR-170** — Malicious source URL/content cannot SSRF/exfiltrate secrets/execute code or expand repository write permissions.
- **MIR-171** — VCS concurrent edit/write conflict leaves Draft artifact unsynced/conflicted and cannot overwrite newer human planning work.
- **MIR-172** — Crash after artifact generation but before issue/PR update uses run/report identity to reconcile before replay and avoid duplicates.
- **MIR-173** — Restore/clone of Radar state changes environment identity and quarantines live schedule/credentials/issues/PR idempotency mappings until reconciled.
- **MIR-174** — Regression suite validates no auto-module inflation, no auto-merge/runtime write, no fabricated trend/install fact and no source/provenance loss.
- **MIR-175** — Historical score/source snapshots remain auditable after policy/model/source changes; current recomputation does not rewrite old decision evidence.
- **MIR-176** — Golden daily scan fixture covers source paging → snapshots/change detection → capability extraction → WPE dedupe/score → S07 Draft handoff → report/issue/optional Draft PR and stops before scope acceptance/development.

## Stop-the-line conditions

Certification stops on fabricated trend/version/install facts, private/secret source leakage, unsafe source fetch, auto-addition of module scope, production/runtime writes, auto-merge, duplicate issue/PR storms, AI prompt-injection privilege bypass or false complete-report claim after source failure.

## Execution gate

All 176 fixtures are documented only. No WordPress.org/API/web scan, GitHub scheduled workflow, repository automation, AI/provider/MCP call, test, benchmark, build or deployment has executed under this protocol. ADR-0014 remains mandatory.