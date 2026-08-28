# WPEssential — REST API Builder Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Work package refinement: `P0-M00-WP38`  
Related: ADR-0028, ADR-0094, ADR-0115, DSR, Query, Policy, Abilities, CLG, DVR, RLT, CAC, ERR, PDL, VER, MLC, JobService, Protector, Multisite, ADR-0014.

## 1. Purpose

Define executable evidence required before REST API Builder can claim route, authentication, authorization, schema, idempotency, rate-limit, cache, CORS, error, Data Source/Ability execution, versioning, privacy, Multisite or scale support.

The original REST-01…REST-52 semantics are preserved. This canonical refinement extends the fixed matrix to **REST-01…REST-176**.

Current execution truth: **0/176 executed**.

The request invariant remains:

**a published compiled descriptor resolves through WordPress REST, then authentication + trusted scope + Policy/operation guard precede Query/Data Source/Ability execution. Response projection, CORS, route visibility, rate limiting, cache and idempotency never substitute authorization.**

Passing shared DSR/CLG/DVR/RLT/CAC/KPA evidence never auto-certifies a REST endpoint.

## 2. Runtime certification profile

Every future report records:
- WordPress/PHP/database versions;
- single-site/Multisite topology;
- REST Definition/compiler/descriptor schema versions;
- RE1/RI1/RI2 and selected RLT/CAC backend/profile versions;
- authentication modes enabled;
- Endpoint/Query/Ability/Data Source/Policy/CLG/DVR versions;
- object/page/cache profile;
- trusted-proxy profile;
- CORS/origin/TLS/proxy configuration;
- Job/Workflow/provider profiles for async/integration endpoints;
- privacy/retention/log profile;
- load-test environment/capacity.

Certification is scoped to the recorded profile. Unknown newer provider/adapter/runtime versions are not silently certified.

# 3. Original route/descriptor/auth fixtures — REST-01…REST-10
- **REST-01** — Published Endpoint revision registers once at intended namespace/path/method and resolves pinned compiled descriptor.
- **REST-02** — Draft/editor Definition cannot become runtime route or fallback execution source.
- **REST-03** — Disabled/archived route fails closed and cannot execute stale descriptor.
- **REST-04** — Conflict with core/third-party/WPE route is detected/resolved deterministically before support claim.
- **REST-05** — Malformed path parameters fail typing before target access.
- **REST-06** — Cookie-auth browser mutation requires certified WP cookie/nonce semantics and still executes Policy.
- **REST-07** — Application Password mode uses native semantics without storing/exposing credential material.
- **REST-08** — Anonymous route works only through explicit publish configuration and still applies public/resource Policy.
- **REST-09** — Missing permission/auth configuration never silently becomes public.
- **REST-10** — Unknown/uncertified auth adapter fails/degrades closed.

# 4. Original scope/authorization/schema fixtures — REST-11…REST-22
- **REST-11** — Endpoint-level Policy deny prevents underlying operation.
- **REST-12** — Resource-level IDOR by changed ID/UUID is denied.
- **REST-13** — Request-provided site selector cannot alter trusted scope without explicit authorized network mode.
- **REST-14** — Cross-site mode reauthorizes each target and remains bounded.
- **REST-15** — Unknown mutation body fields are rejected/ignored; no mass assignment.
- **REST-16** — Protected password/role/secret/internal fields cannot be written through generic mapping.
- **REST-17** — Array/object/string/enum/range/format bounds enforced server-side.
- **REST-18** — Raw table/column/Ability/class/function identifiers cannot select executable primitives.
- **REST-19** — Order/filter/query identifiers validate against typed Query/Data Source contracts.
- **REST-20** — Response projection exposes only authorized declared fields.
- **REST-21** — 404 concealment does not create obvious protected-resource existence leak beyond accepted profile.
- **REST-22** — Malformed/deep/type-confused JSON fails safely without stack/SQL/deserialization execution.

# 5. Original idempotency fixtures — REST-23…REST-32
- **REST-23** — Required idempotency key missing/invalid is rejected before operation.
- **REST-24** — Same key + equivalent normalized request reuses same logical result without duplicate side effect.
- **REST-25** — Same key + materially different request returns conflict.
- **REST-26** — Concurrent same-key race admits one logical operation under certified RI backend.
- **REST-27** — Crash before target mutation permits safe retry according to operation state.
- **REST-28** — Crash after target commit before success record reconciles possible committed effect before retry.
- **REST-29** — External timeout with unknown outcome enters explicit reconciliation state.
- **REST-30** — Idempotency scope isolates endpoint/principal/site.
- **REST-31** — Retention expiry is explicit; old keys are not promised forever.
- **REST-32** — RI backend unavailable never silently removes required idempotency correctness.

# 6. Original rate-limit fixtures — REST-33…REST-38
- **REST-33** — Concurrent admission respects declared shared limiter window/burst behavior.
- **REST-34** — Window/burst boundary behavior is deterministic enough for advertised policy.
- **REST-35** — High-risk authenticated API is not protected solely by spoofable IP identity.
- **REST-36** — Untrusted forwarded headers cannot evade limiter/change trusted client identity.
- **REST-37** — Site namespace isolation prevents cross-site limiter collision unless explicitly network-shared.
- **REST-38** — Limiter unavailable follows explicit risk-based degraded/fail policy and diagnostics.

# 7. Original cache/CORS/error fixtures — REST-39…REST-48
- **REST-39** — Public read cache reuses representation only for equivalent public/authorized context.
- **REST-40** — Privileged response cannot be served to another principal/anonymous caller.
- **REST-41** — Same endpoint/params on different sites cannot cross-serve cache.
- **REST-42** — Endpoint/Query/Policy/source generation invalidates or versions stale cache.
- **REST-43** — Capability/Membership/resource revoke prevents stale privileged cache allow/response beyond accepted window.
- **REST-44** — Cursor/page/filter state participates in cache identity.
- **REST-45** — CORS uses exact approved allowlist; arbitrary Origin reflection is forbidden.
- **REST-46** — Credentialed wildcard CORS is rejected/not emitted.
- **REST-47** — Successful preflight is never authorization.
- **REST-48** — Errors redact stack/SQL/Vault/provider secrets/topology/cross-scope details.

# 8. Original Multisite/scale fixtures — REST-49…REST-52
- **REST-49** — Site-scoped endpoint/operational/cache/rate/idempotency state does not collide across sites.
- **REST-50** — Network endpoint requires network authority and avoids unbounded synchronous all-site mutation loops.
- **REST-51** — Site deletion/drain prevents unsafe new work and reconciles high-risk in-flight state.
- **REST-52** — Controlled 100k/1M-row Query workloads measure bounded latency/query/memory/error without weakening security correctness.

# 9. Definition, compilation, revision and lifecycle — REST-53…REST-68
- **REST-53** — publish compiles immutable runtime descriptor with Definition UUID/revision/schema checksum.
- **REST-54** — runtime never recompiles from mutable Draft on each request.
- **REST-55** — invalid compiled descriptor is rejected before route execution.
- **REST-56** — unknown future descriptor schema fails/read-only/degrades safely.
- **REST-57** — route method/path/namespace change creates explicit compatibility impact; old route is not silently repurposed.
- **REST-58** — revision rollback restores matching descriptor/schema/Policy references deterministically.
- **REST-59** — delete blocked when active dependency/consumer requires route unless supported migration exists.
- **REST-60** — module disable removes/degrades routes according to MLC without deleting Definition data.
- **REST-61** — Pro expiry preserves safe deployed behavior required by product contract but cannot expose paid editing/new unsafe operations.
- **REST-62** — dependency module/adapter loss yields explicit degraded route rather than PHP fatal.
- **REST-63** — endpoint clone receives new identity and does not copy live idempotency/cache/rate state.
- **REST-64** — import same Definition twice respects identity/conflict policy and cannot duplicate route unpredictably.
- **REST-65** — Definition schema migration pins/recompiles expected runtime generation; no silent reinterpretation.
- **REST-66** — capability/Policy rename/removal follows VER migration, not implicit permissive fallback.
- **REST-67** — deprecated endpoint emits documented compatibility/deprecation behavior without leaking internals.
- **REST-68** — removed endpoint cannot be executed through stale route/cache/descriptor registration.

# 10. Data Source, Ability and mutation semantics — REST-69…REST-88
- **REST-69** — read endpoint references registered DSR capability and cannot infer write capability from readable source.
- **REST-70** — create/update/delete operations require independently declared Data Source capabilities.
- **REST-71** — Data Source schema version mismatch degrades before mutation.
- **REST-72** — endpoint cannot override adapter-owned Policy with user-supplied flag.
- **REST-73** — create mapping uses explicit allowlisted fields and canonical types/default/null semantics.
- **REST-74** — update distinguishes absent/null/empty values according to target schema.
- **REST-75** — delete operation respects target restrict/detach/domain lifecycle behavior.
- **REST-76** — optimistic version/precondition conflict returns stable conflict semantics; no lost update.
- **REST-77** — `If-Match`/ETag precondition, where enabled, binds correct resource/version/principal scope.
- **REST-78** — transactional adapter behavior is used only when DSR certifies required transaction capability.
- **REST-79** — multi-resource write never claims atomicity when provider/adapter lacks it.
- **REST-80** — partial mutation result reports per-item/partial truth instead of all-success.
- **REST-81** — Ability endpoint resolves stable registered Ability, not arbitrary callable/string/class.
- **REST-82** — Ability input/output schemas are enforced independently of REST field schema.
- **REST-83** — Ability permission callback/resource Policy runs even if endpoint itself is allowed.
- **REST-84** — destructive Ability requires its specific high-risk capability/confirmation contract where applicable.
- **REST-85** — sync endpoint cannot invoke async-only Ability and fake immediate success.
- **REST-86** — async Ability returns durable operation/job reference only after accepted handoff.
- **REST-87** — arbitrary PHP/SQL/filesystem execution remains unreachable from generic endpoint configuration.
- **REST-88** — DSR/KPA certification never auto-certifies this REST mapping.

# 11. Query/filter/order/pagination/projection — REST-89…REST-104
- **REST-89** — endpoint binds caller parameters to declared typed Query placeholders only.
- **REST-90** — caller cannot replace Query Definition/source/provider by arbitrary identifier unless explicitly allowlisted.
- **REST-91** — filter operator allowed set comes from Query/source schema and rejects unsupported expensive/injection forms.
- **REST-92** — relation traversal reauthorizes target visibility and respects depth/fan-out budget.
- **REST-93** — order-by only on declared sortable fields/provider capability.
- **REST-94** — requested page size has server maximum independent of client value.
- **REST-95** — offset/cursor strategy follows provider profile and does not claim stable cursor where source lacks semantics.
- **REST-96** — cursor is integrity/scope/parameter bound; caller tamper cannot jump to another result context.
- **REST-97** — total count omitted/approximate when exact count is unauthorized/too expensive according to profile.
- **REST-98** — aggregate result cannot leak denied rows through counts/min/max/group buckets.
- **REST-99** — requested fields cannot bypass endpoint projection/field Policy by using aliases/include syntax.
- **REST-100** — sparse-field request only narrows allowed projection; it cannot broaden it.
- **REST-101** — expansion/embed of relations/resources has depth/count/cost limits and target Policy.
- **REST-102** — query timeout/resource-budget error is stable and does not return partial privileged data accidentally.
- **REST-103** — provider/query fallback does not silently switch to semantically weaker unauthorized source.
- **REST-104** — QRY/DSR/REL pass does not replace endpoint-level REST evidence.

# 12. Dynamic values, conditions and representation — REST-105…REST-120
- **REST-105** — DVR-sourced default/derived response value resolves only from registered typed sources.
- **REST-106** — generic resolver cannot expose Vault/password/reset/session/private-key material.
- **REST-107** — canonical value and JSON representation remain distinct; output encoding is deterministic.
- **REST-108** — URL/link fields use approved URL generation/validation and cannot emit unsafe scheme from untrusted value.
- **REST-109** — CLG condition can control declared endpoint behavior only after safe typed evaluation.
- **REST-110** — condition `true` never grants endpoint/resource authorization.
- **REST-111** — denied/unknown condition value follows explicit false/error policy; no favorable guess.
- **REST-112** — conditional field projection cannot leak protected field through alternate branch/error metadata.
- **REST-113** — locale-sensitive values respect requested/authorized locale contract without changing identity scope.
- **REST-114** — date/time output records timezone/format semantics and canonical source remains unambiguous.
- **REST-115** — content negotiation supports only declared media types; no arbitrary renderer execution.
- **REST-116** — JSON serialization handles numeric/string/boolean/null/array/object types without unsafe coercion.
- **REST-117** — large nested output is bounded by response depth/size budget.
- **REST-118** — HTML-rich field is represented only under explicit sanitized/trusted schema; generic text is not marked safe HTML.
- **REST-119** — hyperlinks/private attachment references are authorized at disclosure and use protected delivery when required.
- **REST-120** — DVR/CLG/CBP/renderer certification never auto-certifies REST representation.

# 13. Shared limiter and cache integration — REST-121…REST-136
- **REST-121** — endpoint references stable RLT policy identity; request cannot select bypass/alternate policy.
- **REST-122** — RLT allow continues through auth/Policy/schema; no rate-limit authorization shortcut.
- **REST-123** — idempotency and rate-limit ordering is explicit for high-risk mutation/replay semantics.
- **REST-124** — same idempotent replay cannot be abused as unlimited expensive read/write without declared rate policy.
- **REST-125** — limiter error maps to stable ERR code/Retry-After without exposing bucket key/backend.
- **REST-126** — endpoint cache opt-in requires explicit CAC-safe identity and invalidation dependencies.
- **REST-127** — cache hit still follows request-time Policy where endpoint profile requires it.
- **REST-128** — public→protected endpoint/Policy change invalidates public cached representation.
- **REST-129** — capability/Membership revoke invalidates principal-sensitive cache within certified correctness profile.
- **REST-130** — endpoint Definition/Query/Data Source revision generation invalidates incompatible result cache.
- **REST-131** — error/timeout/denied response negative caching is explicit and cannot become permanent “not found”.
- **REST-132** — stale-while-revalidate is disabled for unsafe authorization-sensitive representation unless separately proven.
- **REST-133** — page/CDN/browser cache headers never broaden private REST response scope.
- **REST-134** — ETag/304 handling reauthorizes request and does not disclose protected existence to unauthorized actor.
- **REST-135** — cache backend failure degrades to source/error according to endpoint profile without serving cross-principal stale data.
- **REST-136** — RLT/CAC certification counters remain separate from REST certification.

# 14. HTTP, proxy, origin and content-security behavior — REST-137…REST-152
- **REST-137** — HTTPS expectation/secure external auth is explicit; insecure transport is not represented as credential-safe.
- **REST-138** — Host/forwarded-host/proto headers affect URL/origin decisions only through trusted proxy profile.
- **REST-139** — absolute URLs generated from trusted canonical site configuration, not arbitrary Host injection.
- **REST-140** — request body Content-Type must match supported parser; unexpected multipart/form/xml does not bypass schema.
- **REST-141** — oversized body rejected before unbounded parse/memory use.
- **REST-142** — duplicate/conflicting headers have deterministic safe handling.
- **REST-143** — duplicate JSON keys/type-confused input follows deterministic parser/schema behavior.
- **REST-144** — method override headers/parameters disabled unless explicit certified profile.
- **REST-145** — OPTIONS/HEAD behavior does not execute mutation side effects.
- **REST-146** — CORS origin comparison normalizes exact scheme/host/port; suffix tricks cannot match allowlist.
- **REST-147** — `null`/file origins are denied unless explicitly justified profile.
- **REST-148** — CORS response does not expose credential/authorization headers unnecessarily.
- **REST-149** — security/cache headers compose safely with host/CDN/plugin and conflicts are observable.
- **REST-150** — redirect response cannot be open redirect from untrusted parameter.
- **REST-151** — response splitting/control characters in header-derived values are rejected/encoded.
- **REST-152** — Protector outer allow/deny never replaces endpoint semantic permission callback.

# 15. Async, provider and failure/reconciliation semantics — REST-153…REST-164
- **REST-153** — long-running operation uses Job/Workflow async handoff rather than unbounded request execution where profile requires it.
- **REST-154** — accepted async response distinguishes queued/accepted from completed.
- **REST-155** — Job enqueue failure means operation is not reported successfully accepted unless durable recovery state exists.
- **REST-156** — duplicate async request respects endpoint idempotency + downstream Job/Workflow idempotency independently.
- **REST-157** — provider call timeout with possible side effect remains `outcome_unknown`, not automatic retry/success.
- **REST-158** — retry policy considers operation idempotency/provider idempotency key and cannot blindly repeat unsafe side effect.
- **REST-159** — provider rate/quota error remains provider fact; shared RLT state is separate.
- **REST-160** — partial fan-out/batch async operation returns durable per-target/aggregate partial state truthfully.
- **REST-161** — cancellation request requires authority and follows cooperative operation semantics; cannot promise rollback of committed side effect.
- **REST-162** — site lifecycle drain/cancel/reconcile applies to in-flight async operations.
- **REST-163** — module disable/Pro expiry does not abandon unsafe in-flight work without MLC-defined reconciliation.
- **REST-164** — provider/Job/Workflow certification is not promoted by REST endpoint evidence.

# 16. Privacy, audit, versioning, Multisite and scale — REST-165…REST-176
- **REST-165** — sensitive endpoint audit records actor/action/target/result/correlation with input/output redaction.
- **REST-166** — privacy export/erase endpoint invokes owner-specific PDL behavior and does not dump/delete unrelated domains generically.
- **REST-167** — logs never retain Authorization header, cookie, password, reset/session token, Vault secret or full sensitive body by default.
- **REST-168** — endpoint API version and Definition/runtime schema versions remain separate compatibility dimensions.
- **REST-169** — deprecated API version follows VER stages and cannot silently alter response/mutation semantics incompatibly.
- **REST-170** — unsupported future client/schema version gets explicit stable compatibility error, not permissive fallback.
- **REST-171** — Site A route cannot operate Site B source/cache/rate/idempotency state through forged coordinates.
- **REST-172** — network endpoint reauthorizes each target and uses bounded/paged async fan-out for large networks.
- **REST-173** — site clone/restore/transfer assigns correct endpoint operational scope generation; stale tokens/keys/cache are not inherited as authority.
- **REST-174** — load profile measures authenticated/anonymous reads, writes, Query cost, limiter/cache/idempotency backend pressure separately.
- **REST-175** — performance optimization cannot weaken auth, Policy, schema, idempotency, RLT, CAC, PDL or scope isolation.
- **REST-176** — evidence report scopes support to exact runtime/auth/provider/backend profile and refuses generic REST-certification overclaim.

## 17. MUST NOT / stop-the-line gates

Certification fails for affected profile if:
- draft/missing permission route becomes public/executable;
- wrong-site selector changes trusted scope;
- mass assignment/protected field mutation succeeds;
- IDOR exposes/mutates unauthorized resource;
- required idempotency duplicate creates repeated side effect;
- proxy spoof bypasses limiter;
- privileged cache response reaches another principal/site;
- CORS/preflight/cache/rate limit is represented as authorization;
- generic endpoint exposes arbitrary PHP/SQL/filesystem/Vault secret primitive;
- stale 304/cache leaks protected resource after revoke;
- unknown future schema/version executes permissively;
- errors/logs leak secrets/SQL/stack/private payload;
- network endpoint performs unbounded unsafe fan-out;
- passing shared DSR/CLG/DVR/RLT/CAC/KPA evidence is used to claim REST certification.

## 18. Required future evidence report

Include exact runtime/adapter/backend profile; REST-01…REST-176 pass/fail/N/A; route/auth/Definition revision matrix; fuzz/mass-assignment/IDOR/wrong-scope evidence; DSR/Ability mutation/precondition tests; Query/projection/cursor tests; DVR/CLG representation tests; idempotency crash/race results; RLT concurrency/spoof results; CAC/revocation/CORS/HTTP results; async/provider reconciliation; privacy/audit/versioning/Multisite; load/query/memory/backend measurements; unsupported/degraded profiles; independent consumer/shared protocol results.

## 19. Current state

- REST fixtures documented: **176**.
- REST fixtures executed: **0/176**.
- REST runtime certification: **none**.
- RLT/CAC/DSR/KPA/provider certifications remain separate.

No REST route registration/request, auth flow, target mutation, idempotency/rate/cache write, CORS execution, provider call, Job/Workflow handoff, fuzz/load test or Multisite runtime operation has been executed.

## 20. Development gate

Execution requires explicit owner consent under ADR-0014 and the Approval Ledger.