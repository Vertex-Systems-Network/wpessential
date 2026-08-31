# WPEssential — Dynamic Value / Token Resolver Executable Evidence Protocol

Status: **Phase 0 evidence specification / EXECUTION NOT AUTHORIZED**  
Date: 2026-08-28  
Work package: `P0-M00-WP35`  
Related: ADR-0014, ADR-0022, ADR-0029, ADR-0035, ADR-0039, ADR-0131, ADR-0134, ADR-0137, ADR-0139, ADR-0143, ADR-0144, ADR-0145, ADR-0146, ADR-0147, ADR-0149, ADR-0151, Data Source Registry, Field Storage, Relations, Query, Conditional Logic, Component Blueprint, Emails, Notifications, Forms.

## 1. Purpose

Freeze the future executable evidence required for WPEssential's shared **Dynamic Value / Token Resolver**.

The protocol freezes **DVR-01…DVR-176**.

Current execution truth: **0/176 executed**.

No Dynamic Value / Token Resolver runtime certification exists.

WPEssential architecture defines one shared resolver supplying dynamic values to listings, dashboards, columns, emails, notifications, forms, builder adapters and other presentation/runtime consumers. Existing owner and consumer protocols verify their own data access/render behavior. This protocol verifies the shared resolver itself: typed source descriptors, safe source resolution, authorization/privacy before disclosure, formatting vs escaping, output-context safety, dependency/cycle/budget behavior, batching, cache/invalidation, versioning, cross-consumer parity, Multisite and scale.

No resolver implementation, render execution, WordPress runtime, provider call, benchmark or data mutation is authorized by this document.

---

## 2. Canonical truth boundaries

Keep distinct:

`source definition ≠ source value ≠ resolved canonical value ≠ formatted value ≠ escaped value ≠ trusted markup ≠ rendered consumer output ≠ cached representation`

Also:
- value readable by one principal/context does not make it globally readable;
- a token resolving successfully does not authorize the consuming action;
- missing, null, empty, zero, false, denied, unresolved and error are not interchangeable;
- display formatting is not canonical storage normalization;
- output escaping is context-specific and occurs after authorized value resolution;
- an HTML-looking string is data unless a separately trusted renderer contract marks a structured safe representation;
- a secret/Vault value is never generic dynamic content;
- current request/blog context is not durable source ownership;
- resolving a Query/Relation/Data Source value does not bypass the owning service's Policy/capability semantics;
- cacheability is derived from all source/access/version/context dependencies, not only token text.

---

## 3. Canonical descriptor

A dynamic-value descriptor records applicable fields:
- stable namespaced token/value-provider key;
- owning module/provider;
- provider/version/schema compatibility;
- typed source identity and source revision where applicable;
- requested field/property/aggregate/value shape;
- expected canonical output type/cardinality;
- authorized target context requirements;
- null/missing/error behavior;
- formatting profile separate from storage semantics;
- allowed output/render contexts;
- privacy/sensitivity classification;
- dependency identities/generations;
- deterministic/time-sensitive/remote marker;
- cacheability dependencies and TTL constraints;
- Multisite scope rules;
- safe diagnostics/explanation metadata.

Normal dynamic-value providers never execute arbitrary PHP, JavaScript, shell, raw SQL or unrestricted callbacks.

---

## 4. Independent certification classes

- `DVR-D` — descriptor/provider registration/versioning;
- `DVR-S` — source resolution and source-owner boundaries;
- `DVR-T` — canonical type/cardinality/null semantics;
- `DVR-P` — Policy/privacy/secret/inference safety;
- `DVR-E` — formatting/escaping/output-context safety;
- `DVR-G` — dependency graph/cycles/budgets/batching;
- `DVR-K` — cache identity/invalidation/version behavior;
- `DVR-C` — cross-consumer parity;
- `DVR-X` — remote/time/locale/media/integration boundaries;
- `DVR-F` — failure/concurrency/observability;
- `DVR-O` — Multisite/scale/adversarial/release regression.

Passing one class never certifies another.

---

# 5. Fixed executable fixture matrix

## A. Descriptor, provider registration and version identity — DVR-01…DVR-16

- **DVR-01** — valid first-party provider registers stable namespaced key, owner, version and output schema.
- **DVR-02** — duplicate provider key with conflicting semantics is rejected; no silent last-write-wins.
- **DVR-03** — third party cannot claim reserved first-party resolver namespace.
- **DVR-04** — repeated bootstrap registration is idempotent.
- **DVR-05** — descriptor declares output type/cardinality and required context explicitly.
- **DVR-06** — unknown provider key yields typed unavailable state, not empty string fallback.
- **DVR-07** — incompatible provider version yields explicit degraded/incompatible state.
- **DVR-08** — unknown future descriptor semantics fail safe/read-only rather than silently dropping constraints.
- **DVR-09** — provider capability/version metadata is inspectable without resolving protected values.
- **DVR-10** — user definitions may reference registered provider keys but cannot register arbitrary executable callbacks.
- **DVR-11** — provider deprecation follows VER stages and emits explicit compatibility/deprecation state.
- **DVR-12** — provider removal does not remap old token key to semantically different provider.
- **DVR-13** — provider registry cache invalidates on module/version/lifecycle change.
- **DVR-14** — provider descriptor inspection is side-effect free.
- **DVR-15** — consumer binding declares supported provider/type/context compatibility before publish.
- **DVR-16** — large provider registry lookup remains bounded and avoids arbitrary filesystem/provider probing.

## B. Source resolution and owner-service boundaries — DVR-17…DVR-32

- **DVR-17** — static literal source resolves exact typed value without side effect.
- **DVR-18** — current authenticated user source resolves server-side and cannot be caller-overridden.
- **DVR-19** — current entity resolves from authorized durable target context, not arbitrary request ID.
- **DVR-20** — Data Source field/property resolves only through DSR-declared read/schema capability.
- **DVR-21** — Field Storage value resolves against exact field definition/schema revision.
- **DVR-22** — Relation-derived target/value resolves through Relations authorization and bounded traversal.
- **DVR-23** — Query-derived scalar/list/row result uses exact Query revision/provider contract.
- **DVR-24** — settings/site/network value uses explicit Settings scope/inheritance contract.
- **DVR-25** — form/workflow runtime value binds to exact submission/run context and type.
- **DVR-26** — membership/user-access-derived label/value does not bypass Membership authority or reveal protected entitlement internals.
- **DVR-27** — media source uses canonical attachment/private-file access contract rather than raw filesystem path.
- **DVR-28** — remote source runs only through certified DSR/Query/Connection/Safe HTTP boundary.
- **DVR-29** — source owner unavailable yields typed unavailable/degraded state rather than stale guessed value.
- **DVR-30** — source resolver cannot mutate source data during normal resolution.
- **DVR-31** — source service exception is normalized and does not convert into empty/allowed output silently.
- **DVR-32** — same source requested repeatedly in one context may batch/memoize only through owner-safe semantics.

## C. Canonical type, cardinality and null/missing semantics — DVR-33…DVR-48

- **DVR-33** — string canonical value remains distinct from rendered HTML/text formatting.
- **DVR-34** — integer preserves numeric identity and does not become localized string prematurely.
- **DVR-35** — decimal/currency/percentage preserve canonical numeric value before formatting.
- **DVR-36** — boolean preserves true/false and never relies on truthy strings.
- **DVR-37** — date remains calendar date without timezone shift.
- **DVR-38** — datetime preserves declared instant/timezone semantics.
- **DVR-39** — enum/choice resolves canonical stored value separately from display label.
- **DVR-40** — entity reference resolves stable identity separately from title/URL/representation.
- **DVR-41** — list preserves typed element cardinality/order semantics.
- **DVR-42** — object/document output requires declared safe schema/shape and bounded depth.
- **DVR-43** — missing is distinguishable from explicit null.
- **DVR-44** — null is distinguishable from empty string.
- **DVR-45** — zero is distinguishable from false and empty.
- **DVR-46** — empty list/object is distinguishable from missing/null.
- **DVR-47** — type mismatch between provider and consumer fails binding/resolution explicitly rather than loose coercion.
- **DVR-48** — unsupported/non-finite/malformed value yields typed invalid/error state, not misleading formatted output.

## D. Authorization, privacy, secrets and inference safety — DVR-49…DVR-64

- **DVR-49** — unauthenticated actor cannot resolve protected token merely because consumer template is public.
- **DVR-50** — authenticated actor lacking row/resource Policy receives denied value state without protected payload.
- **DVR-51** — field/projection Policy applies independently from entity visibility.
- **DVR-52** — source visible to admin preview is not automatically public/frontend/email visible.
- **DVR-53** — token name/diagnostic does not reveal protected source existence beyond allowed semantics.
- **DVR-54** — protected count/aggregate/token cannot infer hidden cohort existence.
- **DVR-55** — wrong-site entity/source ID returns no unauthorized value/existence oracle.
- **DVR-56** — Vault secret plaintext cannot be registered/resolved through generic resolver.
- **DVR-57** — masked secret/reference metadata cannot become equality/reveal oracle.
- **DVR-58** — passwords, reset tokens, session tokens, private keys and card/security data are hard-denied generic dynamic values.
- **DVR-59** — personal/sensitive classification drives default diagnostics/cache/export behavior but never substitutes for Policy.
- **DVR-60** — actor loses access between cache creation and consumption; stale protected value is not served.
- **DVR-61** — condition/token formatting success never authorizes a mutation/action.
- **DVR-62** — privileged preview/explain/reveal mode requires separate capability/Policy and redacts unavailable fields.
- **DVR-63** — renderer cannot request arbitrary internal property by crafted token path beyond provider schema.
- **DVR-64** — audit/correlation records provider/key/result class without unnecessary protected raw value.

## E. Formatting, escaping and output-context safety — DVR-65…DVR-80

- **DVR-65** — plain text context returns formatted text with no HTML trust implication.
- **DVR-66** — HTML text context escapes untrusted markup characters correctly.
- **DVR-67** — HTML attribute context applies attribute-safe escaping and rejects unsafe structural injection.
- **DVR-68** — URL context validates/normalizes allowed URL semantics separately from HTML escaping.
- **DVR-69** — JSON context serializes typed data without pre-HTML escaping or executable string concatenation.
- **DVR-70** — email HTML context uses email renderer sanitization/escaping policy, not browser-markup assumptions.
- **DVR-71** — email plaintext context strips/normalizes presentation without leaking hidden HTML/source artifacts.
- **DVR-72** — JavaScript data, if any approved context exists, uses safe structured encoding and never code interpolation/eval.
- **DVR-73** — CSS/style context is not a generic raw string sink; only typed allowed style-token/value contract may be used.
- **DVR-74** — trusted markup can originate only from separately trusted structured renderer/provider contract, never ordinary string flag from user data.
- **DVR-75** — double-escaping regression is detected when value moves through nested consumers.
- **DVR-76** — under-escaping/XSS payload corpus remains inert across every approved context.
- **DVR-77** — URL scheme/path traversal/data/javascript scheme corpus is rejected/normalized according context policy.
- **DVR-78** — locale number/date formatting occurs after canonical typed value resolution and does not change storage/query semantics.
- **DVR-79** — truncation/format templates cannot expose hidden raw value in tooltip/title/metadata accidentally.
- **DVR-80** — consumer cannot override declared safe output context with arbitrary `raw` mode unless separately trusted and authorized.

## F. Dependency graph, cycles, budgets and batching — DVR-81…DVR-96

- **DVR-81** — token dependency on source/revision/provider is explicitly registered.
- **DVR-82** — nested computed/dynamic token dependency is explicit and versioned.
- **DVR-83** — missing hard dependency yields typed degraded state.
- **DVR-84** — optional dependency uses explicit fallback contract, not silent stale value.
- **DVR-85** — direct token-to-token cycle is detected before infinite recursion.
- **DVR-86** — longer cycle through computed field/condition/query/token is detected with useful path.
- **DVR-87** — maximum nested token depth prevents stack/resource exhaustion.
- **DVR-88** — maximum token count per render/evaluation context is bounded.
- **DVR-89** — relation/query fan-out consumes declared resource budget.
- **DVR-90** — remote provider fan-out/round trips are bounded by context.
- **DVR-91** — public/frontend/email budgets may be stricter than admin preview/diagnostic budgets.
- **DVR-92** — repeated source resolution across collection rows uses batch hydration where owner service supports it.
- **DVR-93** — normal listing/column render does not degrade into unbounded N+1 token requests.
- **DVR-94** — batching preserves row/principal/site associations and cannot cross-authorize values.
- **DVR-95** — budget exhaustion returns bounded error/placeholder policy without partial protected leakage.
- **DVR-96** — evidence records provider call/query count, fan-out, latency and memory separately from render time.

## G. Cache identity, invalidation, expiry and versioning — DVR-97…DVR-112

- **DVR-97** — request-local memoization never crosses request/principal boundary.
- **DVR-98** — persistent cache key includes provider/token descriptor and source revision/generation.
- **DVR-99** — cache key includes site/network target scope.
- **DVR-100** — cache key includes principal/access generation whenever visibility differs.
- **DVR-101** — locale/timezone included only when formatted/resolved semantics depend on them.
- **DVR-102** — time-sensitive/current-time value has bounded expiry and cannot be cached indefinitely.
- **DVR-103** — entity/field/source update invalidates dependent token cache.
- **DVR-104** — relation edge change invalidates dependent token cache.
- **DVR-105** — Query/provider schema/version change invalidates incompatible token result/compile metadata.
- **DVR-106** — Policy/role/Membership revoke invalidates stale allowed value before disclosure.
- **DVR-107** — module/provider disable removes or degrades cached provider availability safely.
- **DVR-108** — provider deprecation/version migration follows VER and never silently changes token semantics.
- **DVR-109** — unknown future token/provider schema fails safe/read-only rather than old-engine interpretation.
- **DVR-110** — downgrade cannot reinterpret newer required semantics as older compatible output.
- **DVR-111** — cache corruption/miss rebuilds from canonical provider/source, never mutates source truth.
- **DVR-112** — import/export transports canonical descriptors/config only, not trusted runtime token-result cache.

## H. Cross-consumer semantic parity — DVR-113…DVR-128

- **DVR-113** — Dynamic Listing uses same source value semantics as resolver-independent Query/Field contracts.
- **DVR-114** — Admin Columns token/value display preserves identical typed source semantics.
- **DVR-115** — Dashboard Widget resolves same token consistently under same authorized context.
- **DVR-116** — Component Blueprint binding receives canonical value + explicit render context rather than pretrusted HTML string.
- **DVR-117** — builder adapter consumes Blueprint/resolver output without private serialization redefining source authority.
- **DVR-118** — Email template token uses same canonical value but email-specific formatting/escaping context.
- **DVR-119** — Notification template token follows same value/privacy semantics while channel delivery remains separate.
- **DVR-120** — Form default/display token does not grant write authority or bypass form validation.
- **DVR-121** — Workflow template/action argument resolution remains typed and separately authorizes action execution.
- **DVR-122** — REST/Ability response value resolver cannot expose fields outside endpoint/output Policy.
- **DVR-123** — Admin Menu/title/badge token cannot become authorization or arbitrary HTML sink.
- **DVR-124** — shortcode/block/frontend consumer receives same canonical semantics with context-specific escaping.
- **DVR-125** — identical token/provider/context produces semantically identical canonical value across consumers.
- **DVR-126** — consumer-specific formatting may differ but must not alter underlying canonical value or access authority.
- **DVR-127** — unsupported provider/output type for a consumer fails bind/publish explicitly.
- **DVR-128** — consumer success never promotes shared resolver certification and resolver success never promotes consumer certification.

## I. Remote, time, locale, media and integration boundaries — DVR-129…DVR-144

- **DVR-129** — remote Data Source value uses certified connection/provider profile only.
- **DVR-130** — remote credentials/tokens never enter dynamic-value descriptor/log/cache/consumer markup.
- **DVR-131** — remote timeout/rate-limit/provider outage yields typed unavailable/unknown state.
- **DVR-132** — remote response schema mismatch fails validation before formatting/render.
- **DVR-133** — remote cached value is locally reauthorized before protected disclosure.
- **DVR-134** — current date/time provider records timezone/clock dependency explicitly.
- **DVR-135** — DST spring-forward/fall-back behavior follows declared temporal semantics rather than string formatting accidents.
- **DVR-136** — locale change modifies presentation only when canonical value semantics are locale-independent.
- **DVR-137** — translated labels never replace canonical enum/source identity.
- **DVR-138** — private media URL/download token reauthorizes access and does not expose permanent public URL.
- **DVR-139** — image/file metadata provider does not leak local filesystem path or protected attachment metadata.
- **DVR-140** — external URL/media source remains subject to Safe HTTP/origin/security policy where retrieval occurs.
- **DVR-141** — third-party provider plugin removal/downgrade degrades only its registered resolver sources without fatal.
- **DVR-142** — extension provider cannot access unrelated private platform services merely by being a resolver plugin.
- **DVR-143** — event/webhook source fact used as token is bound to event/run identity and privacy scope.
- **DVR-144** — provider output marked stale/approximate/unknown preserves that truth in diagnostics where material.

## J. Failure, concurrency, diagnostics and observability — DVR-145…DVR-160

- **DVR-145** — unknown token/provider returns stable ERR-compatible code/category.
- **DVR-146** — permission denial remains distinct from missing/null source.
- **DVR-147** — dependency unavailable remains distinct from empty value.
- **DVR-148** — type/schema mismatch remains distinct from formatting error.
- **DVR-149** — output-context escaping failure fails closed for unsafe sink rather than returning raw value.
- **DVR-150** — remote timeout/rate limit retains retryability truth without fabricating cached success.
- **DVR-151** — internal exception hides stack/SQL/path/secret/private payload in public output.
- **DVR-152** — safe diagnostics identify provider/source/type/context/cache state without protected raw values.
- **DVR-153** — concurrent source update follows owner consistency semantics and does not claim snapshot if none exists.
- **DVR-154** — concurrent token/provider version update pins one compatible descriptor/revision per resolution.
- **DVR-155** — stale render after access revocation cannot publish protected cached value.
- **DVR-156** — resolver retry is side-effect free and cannot duplicate downstream consumer action.
- **DVR-157** — correlation propagates into Query/Relation/remote/provider calls without becoming authorization key.
- **DVR-158** — repeated failing token is rate/budget controlled without hiding root failure classification.
- **DVR-159** — degraded provider can return explicit placeholder only if consumer contract allows it; security-sensitive values never default-open.
- **DVR-160** — recovery/rebuild clears derived caches and restores descriptors without deleting canonical source definitions/data.

## K. Multisite, scale, adversarial corpus and release regression — DVR-161…DVR-176

- **DVR-161** — site-owned token resolves only site-owned sources by default.
- **DVR-162** — network-owned resolver source requires explicit network ownership and Policy.
- **DVR-163** — `switch_to_blog()` changes execution context only and cannot rewrite durable token/source ownership.
- **DVR-164** — cross-site source is rejected unless exact certified cross-site/network profile exists.
- **DVR-165** — cache key/generation prevents same token text from leaking values between sites.
- **DVR-166** — site clone remaps/copies descriptors/dependencies without stale access/commercial/provider authority.
- **DVR-167** — site deletion makes owned values unavailable without implying global-user/privacy/remote deletion.
- **DVR-168** — restore from older version reconciles resolver/provider/schema generations before trusted output resumes.
- **DVR-169** — 10k/100k/1M-row collection render evidence proves bounded batching/query count for representative token sets.
- **DVR-170** — 100/1k/10k-site registry/cache workload measures network coordination/noisy-neighbor behavior.
- **DVR-171** — malicious nested token/path/source-reference corpus cannot escape registered provider schema.
- **DVR-172** — XSS/HTML/JS/CSS/URL payload corpus remains inert under every approved output context.
- **DVR-173** — SQL/control/path traversal payload remains data and never becomes identifier/code/filesystem path.
- **DVR-174** — large token/dependency graph obeys depth/count/fan-out/memory budgets without fatal.
- **DVR-175** — release regression runs representative Listings/Columns/Blueprint/Email/Notification/Form consumers against same resolver artifact/profile and reports each certification separately.
- **DVR-176** — production-readiness requires all mandatory DVR classes, no security/scope/escaping stop-line issue, accepted scale baselines and explicit scoped owner authorization before execution/implementation.

---

## 6. Stop-the-line conditions

DVR cannot pass if any supported profile permits:
- arbitrary PHP/JS/raw-SQL/shell/callback execution through normal dynamic values;
- generic secret/password/token/private-key resolution;
- a protected value to bypass source-owner Policy or leak across principal/site/cache boundaries;
- unescaped attacker-controlled value to reach an incompatible HTML/attribute/URL/JSON/JS/CSS/email sink;
- trusted-markup claims from ordinary user strings without a separately trusted renderer contract;
- unbounded resolver/query/relation/remote fan-out or recursive token cycles;
- consumer-specific behavior to silently redefine canonical source value semantics;
- module/provider/version downgrade to reinterpret unknown required semantics as safe;
- paper/static evidence to be promoted as runtime certification.

## 7. Future execution report

Authorized execution must record:
- DVR-01…DVR-176 result table;
- exact resolver/provider/source-owner/consumer versions;
- WordPress/PHP/DB/Multisite profiles;
- token/provider/source/revision/context fingerprints with sensitive values redacted;
- authorization/privacy decisions;
- formatting/escaping context results;
- query/provider call counts, latency/memory/fan-out/batching evidence;
- cache key/invalidation/version evidence;
- wrong-scope/unauthorized/secret/XSS leakage counts;
- cross-consumer parity results;
- failures/inconclusive/stop-line items;
- final DVR certification-class state.

## 8. Development gate

This protocol is documentation only. No resolver/provider implementation, registry, renderer, cache, WordPress runtime, test, benchmark, provider call, migration or data mutation is authorized. Explicit owner consent under ADR-0014 remains required.