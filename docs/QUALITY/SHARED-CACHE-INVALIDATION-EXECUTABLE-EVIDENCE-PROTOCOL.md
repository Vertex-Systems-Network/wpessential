# WPEssential — Shared Cache & Invalidation Executable Evidence Protocol

Status: **Phase 0 evidence specification / EXECUTION NOT AUTHORIZED**  
Date: 2026-08-28  
Work package: `P0-M00-WP37`  
Related: ADR-0014, platform architecture §27, Definition, Query, Relations, Data Source Registry, Dynamic Listings, Component Blueprint, Dynamic Value Resolver, REST, Policy, Privacy, Multisite, Module Lifecycle, Contract Versioning.

## 1. Purpose

Freeze future executable evidence for WPEssential's shared cache/invalidation contract without replacing consumer-specific cache correctness.

This protocol freezes **CAC-01…CAC-176**.

Current execution truth: **0/176 executed**.

No shared cache backend/profile or runtime certification exists.

Query, Listings, REST, Component Blueprint, DVR, Data Source adapters and other consumers retain their own cache fixtures. Passing CAC does not auto-certify any consumer.

No object-cache call, transient write, DB cache table, page-cache purge, CDN request, browser cache operation, benchmark or Multisite runtime operation is authorized by this document.

## 2. Canonical boundaries

Keep distinct:

`source truth ≠ source generation ≠ cache descriptor ≠ cache key ≠ cached value ≠ cache metadata ≠ hit ≠ freshness ≠ authorization ≠ invalidation event ≠ purge attempt ≠ purge confirmation`

Also:
- cache hit ≠ permission to disclose;
- TTL ≠ complete invalidation strategy;
- deletion request ≠ confirmed deletion;
- object cache ≠ page cache ≠ CDN cache ≠ browser cache;
- cache backend availability ≠ source availability;
- stale value ≠ canonical value;
- current site ≠ durable cache ownership;
- same query/string ≠ equivalent authorization context;
- cache optimization ≠ correctness authority.

## 3. Shared cache descriptor

A cacheable operation declares applicable fields:
- stable namespaced cache profile ID/version;
- owner module/service;
- source/dependency identities and generations;
- scope: installation/network/site/resource/principal/public class;
- canonical key inputs and schema version;
- value schema/serialization version;
- authorization sensitivity/publicness class;
- locale/timezone/language/context dependencies where relevant;
- TTL/max-stale/stale-while-revalidate profile where supported;
- invalidation dependencies/events;
- negative-cache policy;
- stampede/single-flight policy;
- backend capability requirements;
- lifecycle/version/degraded behavior;
- privacy/retention classification;
- diagnostics/provenance metadata.

Caching is opt-in. A consumer that cannot describe safe identity/invalidation does not become cacheable by default.

## 4. Independent certification classes

- `CAC-K` — canonical identity/key/schema;
- `CAC-A` — authorization/principal/public isolation;
- `CAC-G` — generation/dependency invalidation;
- `CAC-T` — TTL/freshness/stale semantics;
- `CAC-C` — concurrency/stampede/atomic mutation;
- `CAC-B` — backend/coexistence/failure behavior;
- `CAC-L` — lifecycle/version/migration/rollback;
- `CAC-P` — privacy/security/data minimization;
- `CAC-M` — Multisite/network isolation;
- `CAC-O` — observability/performance/consumer integration.

Passing one class never certifies another.

# 5. Fixed executable fixture matrix

## A. Cache identity and key schema — CAC-01…CAC-16
- **CAC-01** — stable namespaced cache profile creates deterministic key for equivalent canonical input.
- **CAC-02** — different consumer/profile namespaces cannot collide accidentally.
- **CAC-03** — cache key includes schema/profile version when representation compatibility requires it.
- **CAC-04** — definition/revision identity participates where output depends on pinned revision.
- **CAC-05** — Data Source/Query/provider identity participates where source semantics differ.
- **CAC-06** — ordering/filter/pagination parameters normalize deterministically.
- **CAC-07** — irrelevant query parameter noise cannot create unbounded equivalent keys.
- **CAC-08** — locale/language participates only when output actually varies by locale.
- **CAC-09** — timezone participates for timezone-sensitive output.
- **CAC-10** — render context participates when HTML/plaintext/JSON representations differ.
- **CAC-11** — site/network/install scope participates explicitly.
- **CAC-12** — public/principal/resource authorization class participates as required.
- **CAC-13** — oversized/high-cardinality attacker input is bounded/hashed through approved canonicalization.
- **CAC-14** — secrets/raw credentials/tokens never appear in key text/logging.
- **CAC-15** — unknown future key schema fails/degrades safely rather than colliding with known keys.
- **CAC-16** — diagnostics can explain logical key inputs without exposing private key material.

## B. Authorization and disclosure isolation — CAC-17…CAC-32
- **CAC-17** — anonymous public representation cannot reuse privileged principal cache entry.
- **CAC-18** — User A protected result cannot be served to User B without proven equivalent authorization class.
- **CAC-19** — capability change invalidates/versions authorization-sensitive cache as required.
- **CAC-20** — Membership revoke prevents stale privileged disclosure beyond certified correctness window.
- **CAC-21** — Role/capability removal invalidates cached allow/output where dependency exists.
- **CAC-22** — resource-level Policy change invalidates affected protected representation.
- **CAC-23** — possession of cache key/URL/reference never grants authorization.
- **CAC-24** — negative authorization result cache, if used, cannot survive legitimate grant beyond declared window/generation.
- **CAC-25** — publicness classification change public→protected invalidates public cached output before exposure.
- **CAC-26** — protected→public change does not reuse principal-private representation blindly.
- **CAC-27** — row/count/aggregate cache cannot leak existence of denied resources.
- **CAC-28** — cursor/pagination cache cannot cross principal authorization boundary.
- **CAC-29** — fragment cache with mixed public/private children derives safest required identity.
- **CAC-30** — admin preview cache cannot become frontend public cache source.
- **CAC-31** — AI/REST/CLI consumer cache still reauthorizes according to owning contract.
- **CAC-32** — cache hit never bypasses target Policy when Policy requires request-time check.

## C. Generations and dependency invalidation — CAC-33…CAC-48
- **CAC-33** — source generation change invalidates/version-bumps dependent cache.
- **CAC-34** — Definition publish invalidates compiled/output caches tied to previous revision as declared.
- **CAC-35** — draft edit does not invalidate published runtime cache unless declared dependency requires it.
- **CAC-36** — Relation mutation invalidates dependent relation/query/listing cache.
- **CAC-37** — Field value mutation invalidates dependent projections/render output.
- **CAC-38** — Query Definition publish invalidates compiled plan/result identities appropriately.
- **CAC-39** — Policy/capability generation invalidates authorization-sensitive caches.
- **CAC-40** — module/adapter version change invalidates incompatible representation.
- **CAC-41** — provider/source schema generation change invalidates incompatible cached result.
- **CAC-42** — explicit dependency graph invalidates transitive dependents without unrelated global purge where possible.
- **CAC-43** — multiple dependency events coalesce without losing required invalidation.
- **CAC-44** — missed event has reconciliation/generation fallback where correctness requires it.
- **CAC-45** — out-of-order duplicate invalidation event remains idempotent.
- **CAC-46** — cache generation cannot move backward silently after concurrent writes.
- **CAC-47** — invalidation of one tenant/resource cannot wildcard-delete unrelated namespace through malformed identifier.
- **CAC-48** — dependency-cycle metadata is bounded and cannot create recursive invalidation loop.

## D. TTL, freshness, stale and negative caching — CAC-49…CAC-64
- **CAC-49** — TTL expiry makes entry non-fresh according to declared semantics.
- **CAC-50** — TTL zero/off disables caching rather than creating permanent entry accidentally.
- **CAC-51** — very large/invalid TTL is bounded/rejected before overflow.
- **CAC-52** — stale-while-revalidate, if enabled, has explicit max-stale and authorization-safe class.
- **CAC-53** — security-sensitive denial/revocation output does not use unsafe stale window.
- **CAC-54** — expired entry cannot be served indefinitely because refresh repeatedly fails unless explicit safe degraded policy says so.
- **CAC-55** — source outage and stale-cache serving are distinct states surfaced truthfully.
- **CAC-56** — negative cache for missing resource has bounded lifetime/invalidation on resource creation.
- **CAC-57** — transient provider error is not cached as durable “not found” unless explicit semantics allow it.
- **CAC-58** — empty list and backend error remain distinct cacheable results.
- **CAC-59** — null/missing/denied/error representations use distinct typed cache state.
- **CAC-60** — clock/DST changes do not corrupt TTL correctness under certified backend semantics.
- **CAC-61** — expiration cleanup lag does not make logically expired entry fresh.
- **CAC-62** — touch/refresh extends freshness only through explicit policy.
- **CAC-63** — client/browser `max-age` never exceeds server authorization-safe freshness unless representation is immutable/public.
- **CAC-64** — TTL is not represented as substitute for event/generation invalidation where correctness demands immediate revoke.

## E. Concurrency, stampede and atomic updates — CAC-65…CAC-80
- **CAC-65** — concurrent miss burst has bounded duplicate computation under selected profile.
- **CAC-66** — single-flight/lock, if used, cannot create permanent deadlock after worker crash.
- **CAC-67** — lock expiry does not allow stale old worker to overwrite newer generation result.
- **CAC-68** — compare/version write prevents older computation replacing newer cache generation.
- **CAC-69** — concurrent invalidation and fill resolves to entry matching current generation.
- **CAC-70** — invalidation during expensive render/query cannot publish stale result after commit without generation check.
- **CAC-71** — multiple workers may compute redundantly only within documented bounded profile; correctness remains intact.
- **CAC-72** — cache add/set semantics are certified per exact backend before atomic claim.
- **CAC-73** — object-cache eviction under load causes misses, not cross-key corruption.
- **CAC-74** — failure to write cache never rolls back canonical successful source mutation.
- **CAC-75** — cache write before canonical commit is prevented or invalidated on transaction rollback.
- **CAC-76** — post-commit invalidation failure is visible/reconciled for security-sensitive data.
- **CAC-77** — Job-based refresh remains idempotent and generation-aware.
- **CAC-78** — refresh/backfill jobs obey JobService fairness/backpressure and cannot flood one site/provider.
- **CAC-79** — stampede protection itself cannot become authorization lock/bypass.
- **CAC-80** — concurrency evidence records source calls, hits, misses, stale serves, duplicate computes and generation conflicts.

## F. Backend adapters and cache-layer coexistence — CAC-81…CAC-96
- **CAC-81** — no persistent object cache profile behaves correctly with request-local/default WP cache semantics.
- **CAC-82** — certified Redis/Memcached/object-cache adapter records exact capabilities/version before support claim.
- **CAC-83** — ordinary WordPress transients are treated according to actual DB/object-cache semantics, not assumed persistent/atomic universally.
- **CAC-84** — cache backend outage degrades to source where safe rather than reporting stale hit as fresh.
- **CAC-85** — source unavailable + cache unavailable yields explicit error/degraded state.
- **CAC-86** — backend serialization failure cannot expose raw PHP object injection/untrusted deserialization path.
- **CAC-87** — cache prefix/group coexistence avoids collision with core/third-party keys.
- **CAC-88** — external plugin flushing object cache creates misses only, not canonical data loss.
- **CAC-89** — page cache does not bypass private dynamic authorization just because application fragment is safe.
- **CAC-90** — CDN cache receives only representations explicitly classified safe for that shared scope.
- **CAC-91** — browser cache headers do not cache sensitive admin/API responses publicly.
- **CAC-92** — ETag/Last-Modified semantics do not turn conditional request into authorization bypass.
- **CAC-93** — reverse proxy cache varies on necessary auth/site/locale dimensions or is disabled for protected responses.
- **CAC-94** — application purge request distinguishes local cache invalidation from upstream CDN/page-cache confirmation.
- **CAC-95** — unsupported external cache layer is documented as UNKNOWN rather than assumed coherent.
- **CAC-96** — backend adapter certification never automatically certifies consumer cache safety.

## G. Lifecycle, versioning, deploy and rollback — CAC-97…CAC-112
- **CAC-97** — module disable stops new module cache use while preserving canonical data.
- **CAC-98** — re-enable does not trust incompatible old cache generation blindly.
- **CAC-99** — Pro expiry cannot leak paid/protected cached output after access/edit policy changes.
- **CAC-100** — plugin deactivation cache disappearance does not delete canonical user data.
- **CAC-101** — uninstall cleanup removes only owned cache metadata/entries and never arbitrary shared cache groups.
- **CAC-102** — product/platform version upgrade versions incompatible cache representations.
- **CAC-103** — rolling deploy with old/new app versions cannot share incompatible cache schema silently.
- **CAC-104** — unknown future cache value schema is ignored/degraded safely.
- **CAC-105** — rollback to older version does not deserialize/use unsupported newer representation unsafely.
- **CAC-106** — build/deploy artifact change invalidates compiled/render caches tied to artifact hash where relevant.
- **CAC-107** — module migration does not use stale pre-migration cache as canonical migrated data.
- **CAC-108** — migration completion invalidates/versions impacted cache deterministically.
- **CAC-109** — backup excludes ephemeral cache by default unless specific metadata is explicitly required.
- **CAC-110** — restore reconstructs/reconciles cache from restored canonical truth instead of trusting backup-time cache freshness.
- **CAC-111** — clone/transfer gives new scope generation rather than sharing source site's tenant-private cache.
- **CAC-112** — lifecycle/version evidence remains subordinate to VER/MLC domain truth.

## H. Privacy, security and data minimization — CAC-113…CAC-128
- **CAC-113** — Vault plaintext secrets are never stored in generic cache.
- **CAC-114** — passwords/reset/session/API secrets are excluded from generic cached values/keys.
- **CAC-115** — PII/sensitive cache values require explicit classification and shortest justified retention/freshness.
- **CAC-116** — privacy erase invalidates/removes derived cache owned by affected live data where required.
- **CAC-117** — privacy export does not dump internal cache blobs as canonical user data by default.
- **CAC-118** — cache logs/metrics omit sensitive full key/value payload.
- **CAC-119** — unauthorized diagnostics user cannot inspect private cached values.
- **CAC-120** — cache poisoning via untrusted request parameters is prevented by canonical validation and scope identity.
- **CAC-121** — attacker cannot choose serialized cache key that collides with privileged object through delimiter/type confusion.
- **CAC-122** — cached HTML/markup still follows renderer escaping/trusted-markup contract; cache does not sanitize unsafe source magically.
- **CAC-123** — poisoned/invalid cached representation fails schema/integrity check where profile supports it and falls back safely.
- **CAC-124** — protected error/exception details are not cached into public response.
- **CAC-125** — denial/count/timing cache does not amplify resource-existence leakage beyond accepted Policy profile.
- **CAC-126** — remote source cached payload does not bypass provider/source reauthorization requirements at refresh/use boundaries.
- **CAC-127** — operational retention is distinct from privacy/legal retention and documented by owner.
- **CAC-128** — security optimization never accepts stale privileged authorization merely to improve hit rate.

## I. Multisite/network scope — CAC-129…CAC-144
- **CAC-129** — identical logical key on Site A/B cannot collide for site-owned cache.
- **CAC-130** — request-provided site ID cannot select another site's cache namespace.
- **CAC-131** — current blog context is not durable cache ownership.
- **CAC-132** — `switch_to_blog()` restores prior cache context correctly and cannot leak previous site's entry.
- **CAC-133** — network-owned cache uses explicit network identity, not arbitrary current site.
- **CAC-134** — Site Admin cannot inspect/purge another site's private cache through forged target.
- **CAC-135** — Network Admin bulk purge is bounded/paged and authorized.
- **CAC-136** — site-specific invalidation does not wildcard-flush all network tenants unless explicit emergency operation.
- **CAC-137** — noisy Site A key cardinality does not evict/corrupt Site B logical correctness beyond backend capacity limitations disclosed.
- **CAC-138** — site clone creates new tenant cache generation; source private cache not copied as live truth.
- **CAC-139** — site delete cleans owned cache namespace without deleting network/shared assets incorrectly.
- **CAC-140** — site restore invalidates/rebuilds against restored canonical data and current authorization.
- **CAC-141** — site transfer/network move changes cache scope identity explicitly.
- **CAC-142** — network-shared public cache is used only where data is genuinely network-equivalent/public.
- **CAC-143** — multisite cache cleanup/metrics are bounded and tenant-attributable where feasible.
- **CAC-144** — cache evidence never upgrades MSI/LC certification automatically.

## J. Consumer integration and semantic separation — CAC-145…CAC-160
- **CAC-145** — Definition registry cache keeps immutable revision/source truth separate.
- **CAC-146** — Query plan/result cache preserves Query AST/Policy/provider certification boundaries.
- **CAC-147** — Relation lookup cache invalidates on edge/pivot/lifecycle mutation.
- **CAC-148** — Admin Columns batch-value cache does not bypass row Policy or make fake backend sort/filter possible.
- **CAC-149** — Dynamic Listings fragment/result cache preserves visible-result/count/cursor authorization semantics.
- **CAC-150** — Component Blueprint cache preserves binding/Policy/render-context/asset dependencies.
- **CAC-151** — DVR cache stores only context-safe canonical/formatted representation according to resolver contract.
- **CAC-152** — REST response cache preserves auth/Policy/CORS/idempotency/rate boundaries.
- **CAC-153** — Forms schema/options cache cannot preserve revoked Form/resource access.
- **CAC-154** — Notification/email rendering cache never promotes delivery/provider truth.
- **CAC-155** — remote Data Source cache preserves provider/version/error/freshness semantics.
- **CAC-156** — Membership entitlement/access cache invalidates on revoke with accepted correctness guarantee.
- **CAC-157** — Asset/build/browser immutable caching remains ASR/BT-owned and is not auto-certified by CAC.
- **CAC-158** — RLT security bucket state is not treated as generic cache merely because backend is object cache.
- **CAC-159** — Audit log is never replaced by ephemeral cache.
- **CAC-160** — passing CAC changes no consumer protocol/runtime certification counter.

## K. Observability, performance and scale — CAC-161…CAC-176
- **CAC-161** — metrics distinguish hit/miss/stale/error/bypass/refresh/invalidate/purge-request states.
- **CAC-162** — cache hit ratio is not presented as correctness or authorization proof.
- **CAC-163** — diagnostics identify profile/backend/generation safely without exposing value/secrets.
- **CAC-164** — invalidation lag and failed purge are observable for security-sensitive dependencies.
- **CAC-165** — baseline no-cache vs cache-enabled request/query/render cost is measured.
- **CAC-166** — cold-cache and warm-cache latency measured separately.
- **CAC-167** — 10k-key workload measures lookup/write/invalidation/storage behavior.
- **CAC-168** — 100k-key workload measures cardinality/cleanup/backend pressure without unsupported scale claim.
- **CAC-169** — high-concurrency miss workload measures stampede/source-call amplification.
- **CAC-170** — high invalidation-rate workload measures event/coalescing/recompute behavior.
- **CAC-171** — authorization revoke workload measures stale privileged exposure window explicitly.
- **CAC-172** — Multisite noisy-neighbor workload measures isolation and backend pressure.
- **CAC-173** — backend outage/recovery workload avoids refresh retry storm.
- **CAC-174** — cache memory/storage budget and eviction behavior recorded for tested backend.
- **CAC-175** — performance optimization cannot broaden cache identity, remove Policy dependency or skip required invalidation.
- **CAC-176** — evidence report scopes support to exact backend/profile/environment and refuses generic cache-certification overclaim.

## 6. MUST NOT / stop-the-line gates

Stop affected certification if:
- privileged cache output reaches unauthorized principal/site;
- public→protected or revoke change remains exposed beyond accepted correctness profile;
- Site A can read/purge/collide with Site B private cache;
- secret/password/token appears in generic cache/key/log;
- cache hit bypasses required request-time Policy;
- stale computation overwrites a newer generation after invalidation;
- upstream page/CDN/browser cache is assumed safe without evidence;
- cache is treated as canonical business/audit/security-counter truth;
- passing CAC is used to auto-certify Query/Listings/REST/CBP/DVR/other consumers.

## 7. Required future evidence report

Record exact WordPress/PHP/database/object-cache/page-cache/CDN/browser/Multisite profile; backend versions/capabilities; CAC-01…CAC-176 results; key/generation schemas; authorization/revocation tests; concurrency/stampede measurements; invalidation and stale windows; privacy/security results; Multisite isolation; performance/load numbers; certification classes earned; unsupported/degraded cache layers; consumer protocols separately executed for integration claims.

## 8. Current truth

- CAC fixtures documented: **176**.
- CAC fixtures executed: **0/176**.
- shared cache certifications: **0**.
- cache backends certified: **0**.
- consumer certifications unchanged.

## 9. Development gate

Execution requires explicit owner authorization under ADR-0014 and the Approval Ledger. `continue`, planning acceptance or ADR acceptance does not authorize executable evidence or production implementation.