# WPEssential — Search & Indexing Executable Evidence Protocol

Status: **Accepted evidence design candidate / execution pending / no development authorization**  
Date: **2026-08-29**

Namespace: **SRH-001…SRH-176**  
Executed: **0/176**  
Runtime certification: **0**

## 1. Purpose

This protocol fixes the executable evidence required before F03 — Search & Indexing Engine can be called runtime-ready.

Search is a derived retrieval/indexing system. The source Data Source/Policy remains authoritative. An indexed document, cached result, facet count, vector similarity or backend hit never becomes authorization or factual/business authority merely because the search backend returned it.

Backend-specific capabilities must be discovered/certified. WPE must not promise equivalent semantics across local WordPress/MySQL search, database full-text implementations or remote search/vector backends when their analyzers, consistency, filtering, ranking or pagination differ.

---

# Group 1 — Index definition/schema/backend capability discovery — SRH-001…011

- **SRH-001** valid Index Definition publishes with stable key, revision and source identity.
- **SRH-002** duplicate stable index key with incompatible schema requires explicit revision/migration rather than silent replacement.
- **SRH-003** source Data Source/Query must resolve before index activation; missing source produces degraded/not-ready state.
- **SRH-004** entity identity field must be stable, non-empty and unique within the configured source scope.
- **SRH-005** backend adapter declares supported field types, filtering, faceting, sorting, highlighting, typo/fuzzy, geo, vector and pagination capabilities.
- **SRH-006** unsupported requested capability blocks publish or is explicitly disabled; WPE never silently emulates incompatible semantics without a certified fallback.
- **SRH-007** backend version/capability drift invalidates stale certification and raises compatibility state.
- **SRH-008** index alias/generation identity cannot point to a schema-incompatible generation without migration/swap validation.
- **SRH-009** site/network ownership is embedded in definition and cannot be changed by request context alone.
- **SRH-010** export/import preserves index definition/version/backend profile references without credentials or raw protected data.
- **SRH-011** deleting/archiving an Index Definition follows dependency review and does not delete source business data.

# Group 2 — Field analysis/tokenization/normalization/locale — SRH-012…022

- **SRH-012** text field indexes and retrieves expected normalized tokens for the configured analyzer.
- **SRH-013** keyword field preserves exact-value semantics and is not silently stemmed/tokenized like text.
- **SRH-014** lowercase normalization produces declared case-insensitive behavior without rewriting canonical source value.
- **SRH-015** accent/diacritic normalization follows configured locale/profile and remains explainable.
- **SRH-016** stemming applies only to certified language/analyzer combinations and does not silently use an unrelated language stemmer.
- **SRH-017** stopword profile changes require index generation/rebuild semantics when backend analysis occurs at index time.
- **SRH-018** tokenizer handles punctuation/apostrophes/hyphens according to declared profile with golden fixtures.
- **SRH-019** multilingual field/analyzer routing selects the correct analyzer from explicit locale/source facts.
- **SRH-020** unknown/unsupported locale follows configured neutral/reject/fallback behavior and is not guessed from user identity.
- **SRH-021** n-gram/prefix analyzer respects configured min/max bounds and cannot explode unbounded token cardinality.
- **SRH-022** analyzer/normalizer revision changes are fingerprinted so stale documents cannot be reported as fully current.

# Group 3 — Exact/prefix/fuzzy/typo/phrase/synonym semantics — SRH-023…033

- **SRH-023** exact-match query prefers/returns exact eligible term according to ranking profile.
- **SRH-024** prefix/autocomplete returns only terms within configured min-character and prefix semantics.
- **SRH-025** empty or below-minimum-character query follows explicit empty-query policy without full-index accidental dump.
- **SRH-026** fuzzy matching respects maximum edit-distance/length thresholds and execution budget.
- **SRH-027** typo tolerance is disabled or bounded for fields such as IDs/SKUs/emails where fuzzy semantics are unsafe unless explicitly enabled.
- **SRH-028** quoted/exact phrase behavior preserves token order/proximity according to the certified backend profile.
- **SRH-029** one-way synonym expands only the intended direction.
- **SRH-030** two-way synonym set produces symmetric declared matches without recursive expansion loop.
- **SRH-031** synonym conflicts/duplicates are detected before publish and effective-date/version is recorded.
- **SRH-032** synonym update reindexes or query-expands according to backend capability and reports which semantic is active.
- **SRH-033** malicious/oversized synonym import is bounded, validated and cannot inject backend query syntax.

# Group 4 — Numeric/date/bool/filter/facet/sort behavior — SRH-034…044

- **SRH-034** numeric field preserves numeric ordering rather than lexical ordering.
- **SRH-035** integer/decimal range filters honor inclusive/exclusive bounds and null policy.
- **SRH-036** date/datetime values normalize to unambiguous canonical time with configured source timezone handling.
- **SRH-037** DST boundary date filter returns the same canonical set across repeated execution.
- **SRH-038** boolean tri-state/null semantics distinguish true, false and missing where source supports them.
- **SRH-039** filter composition with nested AND/OR uses typed AST/registered operators, not raw backend-language injection.
- **SRH-040** facet counts match the eligible filtered result universe under declared self-filter semantics.
- **SRH-041** protected/low-count facet values follow count privacy Policy and do not leak hidden entities.
- **SRH-042** multi-value facet/filter semantics distinguish ANY/ALL explicitly.
- **SRH-043** sort by field requires sortable capability/type and deterministic secondary tie-break.
- **SRH-044** missing-value sort placement is explicit and reproducible across supported backends.

# Group 5 — Ranking weights/recency/popularity/manual pins/ties — SRH-045…055

- **SRH-045** field weights affect relevance in the declared direction with a golden query fixture.
- **SRH-046** exact-match boost combines deterministically with text relevance and does not bypass eligibility Policy.
- **SRH-047** recency boost uses a versioned decay/profile with canonical timestamps.
- **SRH-048** popularity boost accepts only approved metric/input and records freshness/provenance.
- **SRH-049** missing popularity metric follows explicit neutral/default behavior rather than arbitrary penalty.
- **SRH-050** manual pin places an eligible result at the configured position without resurrecting deleted/restricted source objects.
- **SRH-051** bury rule lowers eligible ranking without acting as authorization deny.
- **SRH-052** multiple pins/campaign rules resolve by explicit priority and conflict diagnostics.
- **SRH-053** F04 ranking formula integration consumes a published/certified formula revision and refuses incompatible output.
- **SRH-054** equal scores use a deterministic stable tie-break to prevent pagination/result-order flapping.
- **SRH-055** ranking explanation identifies applied weights/rules/metrics without exposing protected internal fields to unauthorized actors.

# Group 6 — Full/incremental/rebuild/generation swap/tombstones — SRH-056…066

- **SRH-056** full index build enumerates the complete authorized source scope once under a recorded source snapshot/profile.
- **SRH-057** repeated full build is idempotent for unchanged source and does not duplicate documents.
- **SRH-058** incremental create event indexes the new source entity once.
- **SRH-059** incremental update replaces/updates the correct document identity without leaving stale searchable field values.
- **SRH-060** delete event removes or tombstones the corresponding indexed document according to backend profile.
- **SRH-061** missed incremental event is repaired by scheduled reconcile.
- **SRH-062** rebuild uses a new generation where atomic swap semantics are required and does not expose half-built mixed schema.
- **SRH-063** generation swap activates only after validation/count/checksum/sample gates pass.
- **SRH-064** failed new generation leaves the prior healthy generation serving and marks rebuild failed/degraded.
- **SRH-065** pause stops new indexing work without corrupting the last known generation and exposes staleness state.
- **SRH-066** resumed/retried build continues from certified checkpoint or restarts explicitly; it never claims continuity that was not verified.

# Group 7 — Source change/delete/revocation/invalidation freshness — SRH-067…077

- **SRH-067** source content update propagates within declared freshness target and exposes lag while pending.
- **SRH-068** source visibility/publication-state change removes or reclassifies result eligibility promptly.
- **SRH-069** source access-policy revocation prevents future unauthorized result/detail exposure even before asynchronous index deletion when runtime reauthorization is required.
- **SRH-070** role/entitlement/policy change invalidates affected protected search caches or causes correct runtime reauthorization.
- **SRH-071** source entity hard delete cannot remain retrievable through stale index ID after reconciliation boundary.
- **SRH-072** source restore/untrash creates a fresh eligible document only after current Policy evaluation.
- **SRH-073** field-level redaction change invalidates stored/snippet/highlight representation.
- **SRH-074** source schema/field removal moves incompatible indexed field to migration/degraded state rather than returning stale values indefinitely.
- **SRH-075** delayed queue/job exposes index freshness/lag health and does not label stale generation current.
- **SRH-076** reconcile detects orphan index documents and missing source documents with bounded repair plan.
- **SRH-077** source-of-truth unavailable state is distinguished from confirmed source deletion; WPE does not mass-delete on an ambiguous outage.

# Group 8 — Policy projection/protected counts/field redaction — SRH-078…088

- **SRH-078** public-only index contains only entities certified public under its source Policy profile.
- **SRH-079** protected index query requires actor/resource Policy before returning protected result details.
- **SRH-080** document existence in backend never grants access when source Policy denies.
- **SRH-081** runtime reauthorization blocks a stale indexed result after access revocation.
- **SRH-082** stored/retrievable fields exclude secret/security-token classes regardless of searchability request.
- **SRH-083** field redaction removes protected value from result, highlight, explanation and export surfaces.
- **SRH-084** total hit count follows protected-count policy and does not reveal hidden object cardinality.
- **SRH-085** facet/autosuggest terms derived only from hidden data are suppressed/bucketed according to Policy.
- **SRH-086** direct document-ID lookup passes the same Policy as normal search.
- **SRH-087** Search REST/Ability/MCP exposure applies actor/site/tenant scope and cannot accept client-supplied broader tenant authority.
- **SRH-088** cache identity includes every authorization dimension required by the protected result profile.

# Group 9 — Pagination/cursor/autosuggest/zero-result/redirect rules — SRH-089…099

- **SRH-089** page-number pagination returns stable non-overlapping pages under unchanged generation/order.
- **SRH-090** cursor pagination binds to required generation/query/sort context and rejects incompatible cursor reuse.
- **SRH-091** max page size/result window is enforced before expensive backend work.
- **SRH-092** generation change during cursor traversal follows explicit restart/stale-cursor semantics.
- **SRH-093** autosuggest obeys minimum characters, rate limits and Policy/redaction rules.
- **SRH-094** autosuggest returns normalized display labels without exposing protected payload fields.
- **SRH-095** zero-result fallback executes only the configured safe fallback and records that it is a fallback.
- **SRH-096** pinned query rule applies only to matching bounded query/context and effective schedule.
- **SRH-097** search redirect rule accepts validated local/allowlisted target and prevents open redirect.
- **SRH-098** multiple matching search rules resolve by explicit priority/specificity with explain trace.
- **SRH-099** empty-query browse mode, when enabled, still applies explicit sort/Policy/pagination budgets and is distinguishable from text search.

# Group 10 — Remote/index backend failure/retry/unknown health — SRH-100…110

- **SRH-100** backend connection/auth failure reports unavailable/degraded without exposing credential material.
- **SRH-101** request timeout is distinguished from confirmed zero results.
- **SRH-102** backend 429/rate-limit follows bounded retry/backoff and surfaces Retry-After/provider state where available.
- **SRH-103** backend 5xx retry does not duplicate indexing writes beyond adapter idempotency semantics.
- **SRH-104** unknown outcome after indexing request is reconciled by document/version identity before blind replay.
- **SRH-105** partial bulk indexing response records per-document success/failure and does not mark batch complete.
- **SRH-106** backend read outage can use only an explicitly certified fallback profile; absence of fallback returns truthful unavailable state.
- **SRH-107** health probe success does not imply index generation freshness; both are reported separately.
- **SRH-108** credential rotation swaps Vault reference without logging old/new secret and revalidates capability.
- **SRH-109** backend index missing/deleted unexpectedly triggers recovery/rebuild plan rather than silent recreation with unknown schema.
- **SRH-110** adapter/version downgrade or incompatible feature loss blocks unsupported query profiles with actionable diagnostics.

# Group 11 — Query injection/bounded backend query language/DoS — SRH-111…121

- **SRH-111** user query string is treated as data and cannot inject raw backend query DSL/operators outside typed parser policy.
- **SRH-112** malformed UTF-8/encoding input is rejected or normalized deterministically before backend execution.
- **SRH-113** pathological wildcard/regex-like public input cannot trigger unbounded backend expression execution.
- **SRH-114** advanced pattern matching, where supported, is validator/budget bound and unavailable to ordinary public input by default.
- **SRH-115** maximum query length is enforced.
- **SRH-116** maximum filter/facet clause count/depth is enforced.
- **SRH-117** expensive high-cardinality facet request is rejected/throttled according to query budget.
- **SRH-118** deep pagination/result-window abuse is bounded by cursor/max-window policy.
- **SRH-119** public search endpoint applies rate limit/abuse controls independently of backend provider limits.
- **SRH-120** highlight/snippet generation caps fragments/length and sanitizes returned markup safely.
- **SRH-121** search logs redact configured PII/secrets/query values and cannot become a raw credential/request-body sink.

# Group 12 — Cache/authorization/tenant isolation — SRH-122…132

- **SRH-122** public search cache key includes query, filters, sort, page/cursor generation and relevant locale dimensions.
- **SRH-123** protected search cache includes actor/Policy-equivalent scope required to prevent cross-user leakage.
- **SRH-124** site/tenant ID is server-resolved and part of every site-scoped cache/index key where needed.
- **SRH-125** cache hit cannot bypass runtime reauthorization for profiles that require it.
- **SRH-126** source/index generation revision invalidates or namespaces stale cached results.
- **SRH-127** ranking/synonym/search-rule revision invalidates affected cache semantics.
- **SRH-128** stampede protection coalesces equivalent work without sharing a protected result across authorization scopes.
- **SRH-129** negative/zero-result cache has bounded TTL so new content is not hidden indefinitely.
- **SRH-130** cache backend outage degrades performance without changing search authorization/result truth.
- **SRH-131** cache clear for one site/profile does not flush unrelated tenant data unless intentionally network-scoped.
- **SRH-132** cached facet counts/highlights follow the same redaction and protected-count policy as uncached execution.

# Group 13 — Multisite index ownership/cross-site aggregate policy — SRH-133…143

- **SRH-133** site-scoped index records durable site ownership independent of current blog context.
- **SRH-134** `switch_to_blog()`/context switch cannot mutate another site's index through stale cached ownership.
- **SRH-135** site administrator cannot query another site's protected index by supplying arbitrary site ID.
- **SRH-136** network aggregate search explicitly resolves authorized target-site set.
- **SRH-137** network aggregate result preserves source site identity/permalink/domain mapping.
- **SRH-138** duplicate entity IDs/slugs across sites do not collide because composite identity is explicit.
- **SRH-139** network facet/count aggregation follows site-level Policy and protected-count rules.
- **SRH-140** new-site bootstrap applies only configured network index templates, not another site's live documents.
- **SRH-141** site deletion/archive removes or quarantines its indexed documents through lifecycle coordinator without affecting sibling sites.
- **SRH-142** site clone creates new ownership identity and does not share protected search cache/index aliases accidentally.
- **SRH-143** noisy/high-volume site cannot exhaust the entire network indexing/query budget without configured network resource policy.

# Group 14 — Backend migration/version compatibility — SRH-144…154

- **SRH-144** backend adapter upgrade runs capability/schema compatibility preflight before serving changed semantics.
- **SRH-145** analyzer/version change that requires reindex creates a new generation and preserves old serving generation until validation.
- **SRH-146** migration local→remote preserves document identity/count plus representative searchable/filter/facet/rank semantics within declared compatibility.
- **SRH-147** migration remote→local rejects features the local backend cannot faithfully support rather than silently dropping them.
- **SRH-148** backend-to-backend migration verifies field mappings/types/normalizers explicitly.
- **SRH-149** synonym/ranking/search-rule definitions are migrated independently from credentials and provider-internal IDs where portable mapping exists.
- **SRH-150** index export/import never assumes raw provider index files are portable across engine versions.
- **SRH-151** rollback after failed backend migration restores previous adapter/generation routing without source-data mutation.
- **SRH-152** dual-read/shadow comparison, if used, does not expose shadow results to end users before acceptance.
- **SRH-153** backend migration comparison reports known semantic differences rather than claiming byte-for-byte ranking equivalence.
- **SRH-154** credentials/endpoints/environment bindings require re-resolution after clone/import and are not embedded in portable definitions.

# Group 15 — 100K/1M/10M document scale and latency budgets — SRH-155…165

- **SRH-155** 100K-document full-build benchmark records throughput, peak memory, query load impact and resulting index size.
- **SRH-156** 1M-document full-build profile remains resumable/bounded and records checkpoint/retry behavior.
- **SRH-157** 10M-document profile selects only a topology certified for that scale and refuses unsupported local promise.
- **SRH-158** incremental sustained update workload measures indexing lag and does not starve transactional WordPress workload.
- **SRH-159** concurrent public query workload records p50/p95/p99 latency under defined dataset/cache/backend profile.
- **SRH-160** expensive facet/filter workload stays within configured CPU/time/result budgets.
- **SRH-161** autocomplete workload uses bounded payload and rate control with defined latency target.
- **SRH-162** rebuild/new-generation process respects JobService fairness/backpressure and configured concurrency.
- **SRH-163** source enumeration avoids obvious N+1 field/relation loading under certified source adapters.
- **SRH-164** index/cache storage growth and retention of old generations obey cleanup/recovery policy.
- **SRH-165** performance certification records hardware/backend/version/dataset/query mix so results are not presented as universal constants.

# Group 16 — Relevance regression/golden query/security leak suite — SRH-166…176

- **SRH-166** content-site golden query set returns expected exact/prefix/phrase/synonym ordering within declared relevance tolerances.
- **SRH-167** product/catalog golden set validates SKU/exact term, taxonomy facets, price/date sort and Woo adapter eligibility without cart/order authority confusion.
- **SRH-168** directory/member golden set validates public-profile search while suppressing private profile fields/users.
- **SRH-169** multilingual golden set validates analyzer/locale routing, accent handling and per-language synonyms.
- **SRH-170** access-revocation scenario proves a previously indexed protected document becomes inaccessible immediately according to runtime Policy profile and is later reconciled from index.
- **SRH-171** deleted/unpublished content scenario proves tombstone/reconcile/cache invalidation and no stale direct-ID leak.
- **SRH-172** backend outage/recovery scenario distinguishes timeout/unavailable from zero results and reconciles unknown indexing outcomes without duplicates.
- **SRH-173** Multisite aggregate scenario returns only authorized target sites, preserves site identity and prevents raw cross-site leak.
- **SRH-174** malicious query/filter/facet suite proves DSL injection, wildcard/regex abuse, deep pagination and oversized highlighting are bounded.
- **SRH-175** AI Prompt drafts an Index/Search Profile using an unsupported backend feature; deterministic validation rejects unsupported capability and no invalid definition publishes.
- **SRH-176** end-to-end source→index build→search→facet→ranking→Policy→incremental update→rebuild generation→backend failure/recovery→export/import golden lifecycle passes with evidence and no unauthorized result/count/field exposure.

## Stop-the-line conditions

Certification stops on any of the following:
- result, document existence, count, facet, suggestion, highlight or explanation leaks data denied by source Policy;
- stale index remains an authorization bypass after source revocation;
- raw public input reaches backend query language without bounded typed validation;
- site/tenant ownership can be widened by client-supplied identifier;
- partial/unknown backend write is reported as fully successful;
- zero results are reported when backend state is actually timeout/unavailable;
- new generation becomes serving before its declared validation gates pass;
- backend migration silently drops required semantics;
- cache returns protected data across authorization/site scope;
- destructive source mutation is used as a search-index repair shortcut;
- vector/fuzzy/relevance similarity is represented as factual correctness or authorization.

## Current truth

- SRH documented: **176**.
- SRH executed: **0/176**.
- F03 runtime certification: **0**.
- Final backend/topology certification remains evidence-gated by capability, scale and deployment profile.
- No index/table/provider, analyzer, search request, document ingestion, cache, benchmark, AI call, MCP call or WordPress runtime test occurred.