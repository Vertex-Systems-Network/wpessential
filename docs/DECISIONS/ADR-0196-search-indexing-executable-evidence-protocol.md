# ADR-0196 — Search & Indexing Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: **2026-08-29**

## Context

ADR-0177 accepted F03 — Search & Indexing Engine. ADR-0180 reserved `SRH-001…SRH-176` as its fixed evidence envelope. WP65 was reserved to turn that group-level envelope into exact executable fixture specifications.

Search is a derived index/retrieval system and therefore has unusually high risk of stale authorization, protected-count leakage, backend-semantic drift, raw query-language injection, cache leakage and false readiness claims based only on successful indexing.

## Decision

Accept:

`docs/QUALITY/SEARCH-INDEXING-EXECUTABLE-EVIDENCE-PROTOCOL.md`

Evidence namespace:
- **SRH-001…SRH-176**.

Current truth:
- documented: **176**;
- executed: **0/176**;
- F03 runtime certification: **0**;
- final backend/topology/performance certification remains evidence-gated.

## Coverage

The protocol fixes evidence for:
- index definition/schema/backend capability discovery;
- field analysis/tokenization/normalization/locale;
- exact/prefix/fuzzy/typo/phrase/synonym semantics;
- numeric/date/bool/filter/facet/sort behavior;
- relevance weights/recency/popularity/manual pins/ties;
- full/incremental/rebuild/generation swap/tombstones;
- source change/delete/revocation/freshness;
- Policy projection/protected counts/field redaction;
- pagination/cursors/autosuggest/zero-result/redirect rules;
- remote backend failure/retry/unknown outcome;
- query injection/resource-abuse controls;
- cache/authorization/tenant isolation;
- Multisite ownership/network aggregate search;
- backend migration/version compatibility;
- 100K/1M/10M scale and latency profiles;
- golden relevance/security/regression scenarios.

## Preserved boundaries

- Search index ≠ source business truth.
- Indexed document existence ≠ authorization.
- Facet/count/suggestion/highlight/explanation must not bypass Policy.
- Runtime reauthorization remains available/required for protected profiles where asynchronous index freshness cannot be trusted as the only gate.
- Raw public user input never becomes backend query DSL.
- Backend capability declarations are versioned/certified; unsupported capabilities are not silently promised.
- Vector/fuzzy/relevance similarity ≠ factual correctness.
- Search cache cannot cross actor/site/tenant authorization dimensions.
- Backend outage/timeout ≠ zero results.
- Partial or unknown indexing outcome ≠ successful completion.

## Work coordination

WP65 — F03 Search & Indexing detailed executable-evidence specification is now **DONE** as a planning work package.

The next previously reserved work package becomes current:
- **WP66 — F04 Decision, Formula, Scoring & Ranking detailed executable-evidence specification (`DEC-001…DEC-176`)**.

WP67…WP74 retain their existing reserved meanings.

## Development gate

No SRH fixture has executed. No search index, database index/table, remote search backend, ingestion job, analyzer, cache, query, benchmark, provider/API/AI/MCP call or WordPress runtime test was created or run.

ADR-0014 explicit scoped owner development consent remains required.