# WPEssential — Market Expansion Executable Evidence Master Plan

Status: **Planning protocol / NOT EXECUTED**
Date: 2026-08-29

## Purpose

Fix the future evidence namespaces and coverage envelopes for the August 2026 market-expansion modules and planning automation before any implementation begins.

All counters below are **0 executed**. This document authorizes no runtime work.

## Fixed protocols

### RDR — URL Redirection & Routing
`RDR-001…RDR-176` — **0/176**

16 evidence groups × 11 fixtures:
1. RDR-001…011 identity/revisions/groups/priority.
2. 012…022 exact/prefix/wildcard/regex matching and budgets.
3. 023…033 URL normalization/case/trailing/query/encoding.
4. 034…044 conditions/login/capability/referrer/cookie/header/IP/time.
5. 045…055 actions/codes/errors/targets/capture substitution.
6. 056…066 open-redirect/header-injection/unsafe-scheme/adversarial URL safety.
7. 067…077 loops/chains/collisions/shadowing/priority.
8. 078…088 permalink monitoring/migration/duplicate prevention.
9. 089…099 404/logging/privacy/retention/log-pollution.
10. 100…110 headers/server profiles/Apache/Nginx lossiness.
11. 111…121 import/export/CSV/JSON/plugin adapters/WP-CLI.
12. 122…132 Simulator/diagnostic trace/cache-generation truth.
13. 133…143 REST/Abilities/MCP/AI Prompt authorization.
14. 144…154 concurrency/cache/performance 1k–100k rules.
15. 155…165 Multisite/domain mapping/site lifecycle.
16. 166…176 upgrade/failure/recovery/coexistence/security regression.

### SRT — Search, Replace & Data Transformation
`SRT-001…SRT-176` — **0/176**

Groups:
1. literal/case/URL search;
2. regex/bounded pattern safety;
3. table/schema/column scope validation;
4. PHP serialized data/no object instantiation;
5. JSON/block/HTML/shortcode structured transforms;
6. typed transforms/URL mapping/output validation;
7. Dry Run fingerprint/count/sample truth;
8. exact diff/privacy/redaction/CSV safety;
9. backup/rollback classes/journals;
10. JobService/checkpoints/pause/resume/crash;
11. concurrency/schema drift/unique collision;
12. URL migration/home/siteurl/GUID/permalink/cache flow;
13. DSR/Field/Relation/Custom Table/adapter-owned writes;
14. REST/Abilities/MCP/CLI/AI approval;
15. Multisite/global tables/site lifecycle;
16. multibyte charset/performance/recovery/adversarial security.

### DMY — Dummy Data / Fixture Studio
`DMY-001…DMY-176` — **0/176**

Groups:
1. posts/pages/CPT;
2. terms/taxonomies;
3. users/comments;
4. fields/meta/value providers;
5. relations/graph/cardinality;
6. status/lifecycle/time distributions;
7. media/attachments;
8. localization/Unicode/RTL/synthetic PII safety;
9. deterministic seeds/reproduction/provider versions;
10. scenarios/Solution Blueprints/domain adapters;
11. volume profiles/jobs/checkpoints/backpressure;
12. generated-data identity/cleanup/regen;
13. negative/adversarial datasets;
14. REST/Abilities/MCP/CLI/AI;
15. Multisite/global-user/site lifecycle;
16. production guards/no-real-provider-effects/performance/recovery.

### LNK — Link Health / Crawl Intelligence
`LNK-001…LNK-176` — **0/176**

Groups:
1. source discovery/content/field/block adapters;
2. link extraction and normalization;
3. internal WordPress route/entity resolution;
4. Safe HTTP/SSRF/DNS/TLS/redirect policy;
5. status classification/inconclusive handling;
6. fragments/anchors;
7. redirect chain/loop analysis;
8. broken media/srcset/embed;
9. internal graph/orphans/crawl depth;
10. scan schedule/JobService/host backpressure;
11. issue lifecycle/dedupe/history;
12. Fix Plan integration with Redirect/SearchReplace/Media;
13. privacy/query redaction/protected sources;
14. REST/Abilities/MCP/AI;
15. Multisite/domain mapping/site lifecycle;
16. 1k–1M URLs/10M occurrences/failures/recovery.

### DBM — Database Maintenance / Cleanup
`DBM-001…DBM-176` — **0/176**

Groups:
1. owner/provider registry;
2. revisions/autodrafts/trash/comments;
3. transients/cache-like cleanup;
4. metadata/relation orphan certainty;
5. WPE Jobs/Workflow/Notification/Analytics/Audit retention providers;
6. module uninstall/orphan retained-data providers;
7. autoload health/owner-aware changes;
8. table size/index/schema/fragmentation health;
9. Dry Run/Plan/fingerprint/estimates;
10. backup/rollback/reauth;
11. jobs/batches/concurrency/precondition recheck;
12. run journal/post-verification;
13. third-party/Woo/Action Scheduler adapter boundaries;
14. REST/Abilities/MCP/CLI/AI;
15. Multisite/global tables/site lifecycle;
16. large DB/resource budgets/failure/recovery/security.

### PDO — Product Discovery & Planning Orchestrator
`PDO-001…PDO-176` — **0/176**

Groups:
1. intent recognition;
2. repository source-of-truth audit;
3. user-source ingestion;
4. web/market research strategy;
5. provenance/freshness/citations;
6. competitor capability extraction;
7. WPE capability/dedupe map;
8. module/service/adapter/Blueprint classification;
9. exhaustive option/flow generation;
10. security/privacy/Multisite/lifecycle generation;
11. evidence/ADR/work-package proposal;
12. canonical sync-plan generation;
13. owner review/acceptance controls;
14. AI/MCP/repo connector authorization;
15. VCS/concurrency/replay/idempotency;
16. hallucination/copyright/secrets/failure/regression.

### MIR — Market Intelligence Radar
`MIR-001…MIR-176` — **0/176**

Groups:
1. WordPress.org API popular/new/recommended paging;
2. plugin information/version/changelog snapshots;
3. SVN/Trac/GitHub official source resolution;
4. WordPress core/dev-note/standard feeds;
5. support/review signal classification;
6. change detection/hash/dedupe;
7. capability extraction;
8. WPE overlap/gap mapping;
9. scoring/thresholds/watchlist;
10. S07 handoff/full Draft audit;
11. daily report/artifacts/issues;
12. optional Draft planning PR permissions;
13. cron schedule/default-branch/idempotency;
14. network/rate-limit/cache/source failures;
15. AI summaries/provenance/secret isolation;
16. false trend/noise/security/VCS/recovery/regression.

## Cross-cutting gates

Every protocol additionally inherits:
- Compatibility CF;
- UI/Build/CI;
- KPA Policy/Abilities;
- PDL privacy;
- ERR errors;
- VER versioning;
- MLC lifecycle;
- MSI/LC Multisite;
- AUD observability;
- AI Prompt AIP where applicable.

No paper feature comparison produces runtime certification.

## Development gate

All seven protocols are documentation-only. Executing any fixture, installing competitor code, creating DB test state, starting HTTP crawls, enabling GitHub scheduled workflow scripts, or running AI/provider research automation remains blocked until explicit scoped development consent.
