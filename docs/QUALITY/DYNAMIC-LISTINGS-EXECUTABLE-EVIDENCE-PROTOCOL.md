# WPEssential — Dynamic Listings Executable Evidence Protocol

Status: **Accepted planning protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP20`  
Execution mode: `PLANNER_ONLY`  
Development authorization: **NOT GRANTED**

Related: `docs/ARCHITECTURE/DYNAMIC-LISTING-RENDER-CACHE-RUNTIME.md`, `docs/ARCHITECTURE/DYNAMIC-LISTINGS-SSR-PAGINATION-CACHE-PROFILE.md`, `docs/MODULES/DYNAMIC-LISTINGS-EXHAUSTIVE-SPEC.md`, ADR-0039, ADR-0099, ADR-0131, ADR-0133, ADR-0134, ADR-0014.

## 1. Purpose

Freeze the future executable evidence required before WPEssential Dynamic Listings / Template Builder may claim production-safe support for secure server rendering, authorization-aware pagination, filters/facets, cache isolation, nested listings, progressive enhancement, builder embeds, SEO/accessibility behavior, Multisite scope or scale.

This protocol does **not** authorize renderer code, Query execution, REST/Interactivity endpoints, cache writes, builder integration, browser tests, benchmarks or any runtime execution.

## 2. Operational baseline under test

ADR-0099 keeps **DL1 — authorization-aware Query + batched hydration + Component Blueprint SSR** as the first operational baseline.

Canonical path:

`Published Listing Descriptor → resolve Scope/Principal → authorized Query plan → truthful visible-result/count/cursor semantics → batched dependency hydration → Component Blueprint SSR → optional progressive enhancement`

## 3. Truth separation

The following are separate truths:

`Listing Definition ≠ Published Listing Revision ≠ Compiled Listing Descriptor ≠ Query capability ≠ candidate result set ≠ authorized visible result set ≠ count/cursor metadata ≠ rendered HTML ≠ cache artifact ≠ client transition state ≠ certified runtime behavior`

A page that visually renders correctly does not prove secure pagination, truthful totals, cache isolation, no-JS accessibility, action authorization or builder compatibility.

## 4. Authorization strategies under test

- `DL-A1` — authorization pushed into authoritative source query; preferred.
- `DL-A2` — bounded candidate query + server-side authorization filtering/refill with truthful count/cursor behavior; evidence required.
- `DL-A3` — secure pageable behavior unsupported; publishing for that context must be blocked/degraded.

No strategy is runtime-certified today.

## 5. Capability certifications

Certify independently per provider/integration profile:

- `DL-R` — SSR/render correctness
- `DL-A` — authorization/visibility correctness
- `DL-P` — pagination/count/cursor correctness
- `DL-F` — filters/search/facets
- `DL-H` — batched hydration/nesting
- `DL-C` — cache safety/invalidation
- `DL-I` — progressive interaction/history/no-JS parity
- `DL-B` — block/shortcode/builder embeds
- `DL-S` — SEO/accessibility semantics
- `DL-M` — Multisite/scope safety
- `DL-O` — performance/observability

Current certifications: **0**.

## 6. Fixed fixture matrix — DL-01…DL-176

### A. Definition, publish and dependency truth — DL-01…DL-16

- **DL-01** new Listing View starts Draft and is non-public.
- **DL-02** published Listing resolves immutable Listing UUID/revision.
- **DL-03** published Query UUID/revision is pinned/resolved truthfully.
- **DL-04** direct simple Data Source mode compiles to bounded Query descriptor rather than alternate raw query language.
- **DL-05** Item Template/Component Blueprint revision resolves successfully.
- **DL-06** Query result schema compatible with Item Template context.
- **DL-07** incompatible result schema blocks publish or marks explicit degraded state.
- **DL-08** missing Query dependency degrades without raw fallback.
- **DL-09** missing Item Template/Partial dependency degrades safely.
- **DL-10** missing Field/Relation binding is diagnosed without nearest-name remapping.
- **DL-11** partial include cycle detected.
- **DL-12** nested Listing cycle detected.
- **DL-13** published descriptor records parameter/filter/sort allowlists.
- **DL-14** URL/pagination schema revision is treated as integration-impacting change.
- **DL-15** archive/disable behavior preserves definition history but prevents new public use as specified.
- **DL-16** draft builder configuration is never executed as privileged runtime code.

### B. SSR rendering, bindings and output security — DL-17…DL-40

- **DL-17** public post/CPT Listing SSR baseline.
- **DL-18** user result Listing SSR baseline.
- **DL-19** term result Listing SSR baseline.
- **DL-20** custom-table row Listing SSR baseline.
- **DL-21** relation-row/result Listing SSR baseline.
- **DL-22** registered provider result-schema SSR baseline.
- **DL-23** text/heading escaping against HTML/script payload.
- **DL-24** safe rich-content sanitizer contract.
- **DL-25** dynamic URL protocol/escaping validation.
- **DL-26** external target-blank link receives safe rel behavior.
- **DL-27** image/media responsive rendition and safe URL output.
- **DL-28** image alt explicit/dynamic/decorative semantics.
- **DL-29** missing value follows configured fallback without raw exception.
- **DL-30** computed/token binding uses allowlisted resolver only.
- **DL-31** arbitrary PHP/JS/eval binding is rejected.
- **DL-32** unknown raw SQL/query fragment source is rejected.
- **DL-33** semantic heading level remains independent from visual style.
- **DL-34** table layout emits semantic table only for tabular data.
- **DL-35** conditional private component decision is enforced server-side.
- **DL-36** client-only condition cannot expose protected source data.
- **DL-37** malformed one-item render follows configured isolated safe fallback.
- **DL-38** systemic Query/provider failure selects Listing error state.
- **DL-39** frontend errors redact SQL/stack/provider secrets.
- **DL-40** rendered action visibility never substitutes for invocation authorization.

### C. Query parameters, filters, search and URL state — DL-41…DL-64

- **DL-41** static parameter mapping.
- **DL-42** current-entity typed parameter mapping.
- **DL-43** current-user safe-context parameter mapping.
- **DL-44** route/context parameter mapping.
- **DL-45** shortcode/block/builder attribute mapping only to declared public parameter.
- **DL-46** unknown public parameter is rejected/ignored by explicit schema policy.
- **DL-47** type/range validation rejects malformed value before Query execution.
- **DL-48** arbitrary `orderby`, meta key or Data Source field from request is rejected.
- **DL-49** namespaced filter keys prevent two-listing URL collision.
- **DL-50** canonical URL serialization deterministic for same typed state.
- **DL-51** sensitive/internal parameter never serialized into public URL.
- **DL-52** text-search min/max length and allowed-mode rules.
- **DL-53** select/radio/boolean filter allowlist semantics.
- **DL-54** multi-select/repeated-value canonical encoding.
- **DL-55** range/date-range typed normalization and timezone semantics.
- **DL-56** taxonomy/hierarchy filter binding.
- **DL-57** entity/relation selector binding.
- **DL-58** invalid filter cannot silently widen result set.
- **DL-59** active-filter chips derive from canonical typed state.
- **DL-60** reset removes only Listing-owned state and preserves unrelated approved query parameters.
- **DL-61** declared sort preset only; arbitrary field sort rejected.
- **DL-62** random sort disabled/incompatible with cursor unless deterministic provider strategy is certified.
- **DL-63** dependent filters remain bounded and cannot cause unbounded query cascade.
- **DL-64** filter/search state cannot bypass Policy or request hidden projection fields.

### D. Authorization, visible-result and count truth — DL-65…DL-88

- **DL-65** DL-A1 source-pushed Policy produces authorized page.
- **DL-66** DL-A1 exact authorized total/count.
- **DL-67** DL-A2 bounded candidate filtering/refill produces requested visible page where allowed.
- **DL-68** DL-A2 refill reaches configured bound without unbounded scan.
- **DL-69** DL-A2 inability to produce truthful result transitions to unsupported/degraded semantics.
- **DL-70** DL-A3 blocks secure public pageable publish/use.
- **DL-71** unauthorized result IDs never appear in HTML/transition payload.
- **DL-72** unauthorized rows never leak through source-wide exact total.
- **DL-73** exact authorized total mode.
- **DL-74** bounded/estimated safe total is labeled as such.
- **DL-75** no-total mode exposes no inferred source total.
- **DL-76** `More results available` mode does not imply hidden exact total.
- **DL-77** page count does not leak protected source cardinality.
- **DL-78** cursor progression cannot expose disallowed record identity.
- **DL-79** facet counts obey same authorization/scope as results.
- **DL-80** error differences do not become protected-existence oracle beyond accepted policy.
- **DL-81** Membership/access revocation changes principal/access generation.
- **DL-82** revocation invalidates/versions affected cached allow/result path.
- **DL-83** item field/component authorization can be stricter than collection authorization.
- **DL-84** item action reauthorizes at invocation.
- **DL-85** forged entity/action input fails typed authorization.
- **DL-86** destructive action uses required confirmation/reauth/idempotency contract.
- **DL-87** private/authenticated listing defaults to non-public indexing assumptions.
- **DL-88** conceal-vs-deny semantics do not reveal hidden record counts accidentally.

### E. Pagination, cursors and mutation-between-pages — DL-89…DL-112

- **DL-89** bounded first-results/no-pagination mode.
- **DL-90** standard numbered page navigation.
- **DL-91** prev/next navigation.
- **DL-92** provider-supported cursor/keyset navigation.
- **DL-93** page-size override cannot exceed Query/provider/listing bound.
- **DL-94** invalid page number fails safely.
- **DL-95** invalid cursor fails safely.
- **DL-96** stale cursor after Listing revision change fails/restarts explicitly.
- **DL-97** cursor binds Query revision + normalized filters/sort + scope.
- **DL-98** protected cursor includes relevant access/principal generation.
- **DL-99** stable deterministic order includes unique tie-breaker.
- **DL-100** duplicate sort keys do not duplicate/skip rows across cursor pages.
- **DL-101** insert before next offset page records expected drift behavior.
- **DL-102** delete between numbered pages records expected drift behavior.
- **DL-103** cursor behavior under insert/delete remains provider-contract truthful.
- **DL-104** page parameter collision avoided for two Listings on same page.
- **DL-105** filters/sort preserved across page navigation.
- **DL-106** canonical pagination URL is deterministic.
- **DL-107** public runtime has no unbounded `all` mode.
- **DL-108** load-more response obeys same authorization/query contract as SSR.
- **DL-109** load-more done state is truthful.
- **DL-110** infinite scroll has manual accessible continuation fallback.
- **DL-111** max automatic pages/footer reachability behavior.
- **DL-112** browser back/forward restoration matches advertised state semantics.

### F. Batched hydration, nesting and dependency budgets — DL-113…DL-132

- **DL-113** visible field dependencies batch by Field Storage adapter.
- **DL-114** media metadata hydration remains cache-aware/bounded.
- **DL-115** relation dependencies batch for visible parent IDs.
- **DL-116** computed resolver dependencies are declared/bounded.
- **DL-117** duplicate dependencies coalesce per request.
- **DL-118** nested Listing level 1 with typed parent parameter mapping.
- **DL-119** nested Listing level 2 within configured budget.
- **DL-120** configured maximum nesting depth blocks deeper recursion.
- **DL-121** child result-count budget enforced.
- **DL-122** nested high-degree relation displays bounded first-N/aggregate where configured.
- **DL-123** deliberate query-per-parent N+1 is detected/rejected/degraded.
- **DL-124** remote provider dependency is not called once per rendered item unless explicit certified bounded exception.
- **DL-125** provider timeout degrades affected Listing/item according to typed error policy.
- **DL-126** missing adapter during hydration cannot fatal entire page.
- **DL-127** one malformed result shape is isolated where safe.
- **DL-128** result-schema mismatch after provider upgrade marks descriptor degraded.
- **DL-129** dependency diagnostics identify revision/provider cause without protected data.
- **DL-130** hydration metrics record rows/values/batches/remote calls.
- **DL-131** page-size × nested-fanout calculation remains within budget.
- **DL-132** pathological nested configuration is blocked/warned rather than silently unbounded.

### G. Cache classes, keys and invalidation — DL-133…DL-152

- **DL-133** LC0 cache-off personalized baseline.
- **DL-134** LC1 request-local memoization.
- **DL-135** LC2 public shared persistent only for truly public deterministic result/output.
- **DL-136** LC3 authenticated persistent key includes principal/audience/access generation where needed.
- **DL-137** LC4 stale-while-revalidate allowed only where stale visibility is acceptable.
- **DL-138** access revocation blocks unsafe stale protected cache immediately.
- **DL-139** Query result cache and rendered item cache are distinct artifacts.
- **DL-140** full fragment cache requires all dependencies to share compatible cache safety.
- **DL-141** most-restrictive dependency classification wins over manual cache toggle.
- **DL-142** cache key includes Listing + Query published revisions.
- **DL-143** cache key includes normalized params/filter/sort/page/cursor.
- **DL-144** cache key includes site/network scope and locale where relevant.
- **DL-145** Blueprint/item revision participates when rendered HTML is cached.
- **DL-146** Field/entity update invalidates/version-tags affected cache.
- **DL-147** Relation attach/detach/pivot/order change invalidates/version-tags affected cache.
- **DL-148** Listing/Query/Blueprint publish invalidates old generation use.
- **DL-149** Membership/Policy change invalidates protected audience generation.
- **DL-150** public and authenticated caches cannot collide under same unsafe key.
- **DL-151** unknown-safe invalidation disables persistent cache or uses explicitly accepted bounded semantics.
- **DL-152** cache diagnostics expose classification/hit/miss without leaking protected key material.

### H. Progressive enhancement, embeds, SEO and accessibility — DL-153…DL-168

- **DL-153** initial meaningful content SSR without JS.
- **DL-154** enhanced filter transition returns same semantic result as server submit.
- **DL-155** enhanced pagination transition matches server navigation contract.
- **DL-156** transition failure leaves/reveals usable fallback where mode claims progressive enhancement.
- **DL-157** loading state exposes appropriate busy/status semantics.
- **DL-158** async update manages focus/announcement without unexpected focus theft.
- **DL-159** reduced-motion behavior respected for offered animation/scroll behavior.
- **DL-160** Gutenberg dynamic block passes Listing UUID + approved overrides only.
- **DL-161** shortcode accepts declared parameters only and uses canonical renderer.
- **DL-162** Elementor/Bricks/WPBakery adapter stores canonical reference rather than duplicate Listing schema where supported.
- **DL-163** missing/outdated builder adapter degrades explicitly and scopes assets to actual use.
- **DL-164** SEO-indexable public Listing exposes meaningful semantic links/page URLs.
- **DL-165** filtered URL index/canonical/noindex integration metadata remains explicit, not hardcoded to one SEO plugin.
- **DL-166** load-more/infinite-scroll is not sole discovery path where indexability is promised.
- **DL-167** structured data appears only through validated registered schema component.
- **DL-168** RTL/table/header associations/keyboard controls/image-alt/heading semantics acceptance set.

### I. Multisite, lifecycle and scale — DL-169…DL-176

- **DL-169** Site A/Site B same numeric entity IDs remain isolated in results/cache/links.
- **DL-170** caller-supplied `site_id` cannot widen scope; route/definition/server context is authoritative.
- **DL-171** network aggregate Listing requires explicit network-capable Query/Data Source and Policy.
- **DL-172** clone/restore reconciles Listing/Query/Blueprint UUID dependencies without accidental cross-site remap.
- **DL-173** site archive/delete invalidates/degrades unsafe cached/pending references.
- **DL-174** public 10k-row workload records query/batch/render/cache metrics.
- **DL-175** public 100k-row and protected mixed-visibility workload records p50/p95, memory, rows examined, refill work and cache isolation.
- **DL-176** scale/pathological configuration triggers evidence-based warning/block/degradation instead of silently exceeding budgets.

## 7. Required measurements

For applicable fixtures record:

- WordPress/PHP/DB versions;
- Query/Data Source provider + version;
- Listing/Query/Blueprint revisions;
- authorization strategy (`DL-A1/A2/A3`);
- scope/site/network/principal classification;
- page size and candidate/visible row counts;
- exact/estimated/no-total mode;
- DB query count and rows examined where available;
- field/relation/media hydration batch counts;
- remote-call counts;
- authorization refill work;
- cache class/key-dimension classification and hit/miss;
- p50/p95 SSR and transition latency;
- peak memory;
- rendered HTML/asset size where relevant;
- accessibility/browser/history observations;
- builder/theme/plugin versions for adapter evidence;
- correlation/artifact references.

Exact budgets and thresholds remain executable evidence, not paper certification.

## 8. MUST NOT / negative requirements

Dynamic Listings MUST NOT:

- render Draft Listing/Query/Template definitions publicly;
- store raw SQL, arbitrary PHP or arbitrary JS as canonical Listing execution logic;
- accept unrestricted request fields, sort keys, projections or AST;
- fetch a protected page, hide unauthorized rows and still expose source totals/page counts as actor-visible truth;
- allow post-query authorization to become an unbounded scan/refill loop;
- share personalized/member/private results or HTML under a public cache key;
- use stale-while-revalidate where revoked access must fail closed;
- run ordinary query/relation/remote N+1 loops per item when a batch path is required;
- let client-side conditions/filtering substitute for authorization;
- expose sensitive parameters in URL/cache/diagnostic logs;
- let builder documents become competing canonical Listing definitions;
- claim enhanced navigation, history restoration, SEO or accessibility behavior without corresponding evidence;
- trust numeric site/entity IDs without explicit scope identity;
- silently reinterpret unsupported provider pagination/filter/sort behavior.

## 9. Stop-the-line conditions

Stop future executable certification immediately for:

- protected record/data/count/facet/cursor leak;
- cross-user or cross-site cache leakage;
- action authorization bypass;
- arbitrary script/PHP/SQL execution from Listing configuration;
- unbounded authorization refill or nested-list N+1 fan-out;
- public shared cache containing personalized/member/private content;
- stale cache exposing access after revocation;
- forged request widening site/network scope;
- builder/client path returning data that SSR Policy path would deny;
- unsafe XSS/dynamic URL output;
- fatal frontend/admin breakage caused by a missing dependency that should degrade safely.

## 10. Evidence report format

Every future executable batch reports:

`Status / Changed / Why / Research / Tests / Security / Data-Migration / Affected / VCS / Docs-Memory / Known Issues / Not Verified / Next Safe Action`

Additionally record fixture IDs, pass/fail/blocked, provider/integration versions, certified/rejected capability classes, measurements, artifacts, deviations from DL1, and unsupported contexts.

## 11. Current evidence state

- Documented fixtures: **176**.
- Executed fixtures: **0/176**.
- `DL-A1/DL-A2/DL-A3` runtime strategy certifications: **0**.
- `DL-R/DL-A/DL-P/DL-F/DL-H/DL-C/DL-I/DL-B/DL-S/DL-M/DL-O` certifications: **0**.
- WordPress Interactivity API selection/certification: **OPEN**.
- builder adapter runtime certifications: **0**.
- exact cache store/TTL/invalidation thresholds: **OPEN**.
- exact nesting/refill/performance budgets: **OPEN**.

## 12. Development gate

Execution of DL-01…DL-176 requires explicit scoped owner authorization under ADR-0014 and the Approval Ledger.

Planning acceptance of this protocol is not implementation or runtime consent.