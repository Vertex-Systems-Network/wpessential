# WPEssential — Entity / Data Source Registry Executable Evidence Protocol

Status: **Phase 0 evidence specification / EXECUTION NOT AUTHORIZED**  
Date: 2026-08-28  
Work package: `P0-M00-WP32`  
Related: ADR-0014, ADR-0022, ADR-0086, ADR-0094, ADR-0131, ADR-0134, ADR-0135, ADR-0141, ADR-0143, ADR-0144, ADR-0145, ADR-0147, ADR-0148, `docs/ARCHITECTURE.md`, Query, Field Storage, Relations, Custom Tables, REST, Import/Export, Component Blueprint, Policy, Multisite.

## 1. Purpose

Freeze the future executable evidence required for the shared WPEssential **Entity / Data Source Registry** before modules, Query, Fields, REST, Workflows, AI or UI rely on it as a universal data-access boundary.

The protocol freezes **DSR-01…DSR-176**.

Current execution truth: **0/176 executed**.

No Data Source runtime/provider certification exists.

The canonical rule is:

> A source being discoverable or readable never implies that it is writable, queryable, sortable, filterable, transactional, remotely accessible, publicly exposable or authorized for the current principal.

Every adapter declares its exact capability/schema/scope/authorization semantics. Consumers use only declared capabilities and must fail safely when semantics are unsupported.

Nothing in this protocol authorizes WordPress/runtime execution, entity reads/writes, custom-table queries, remote HTTP, WooCommerce calls, database mutation, provider registration, benchmarks or migrations.

---

## 2. Canonical boundaries

Keep these truths distinct:

`entity type ≠ Data Source adapter ≠ registered source instance ≠ schema descriptor ≠ capability descriptor ≠ source scope ≠ principal authorization ≠ resource Policy ≠ queryability ≠ mutability ≠ transaction guarantee ≠ runtime entity record ≠ serialized representation ≠ cached projection ≠ provider certification`

Also:
- read capability ≠ write capability;
- list/query ≠ get-one;
- create ≠ update ≠ delete;
- field visible ≠ field writable;
- schema presence ≠ authorization;
- provider supports filter ≠ exact WPE Query semantic equivalence;
- remote response ≠ locally authorized result;
- WordPress numeric ID ≠ portable global identity;
- current blog ≠ durable site ownership;
- transaction support ≠ cross-provider distributed transaction;
- adapter registration ≠ provider certification;
- schema discovery ≠ permission to expose protected/system fields.

---

## 3. Canonical adapter capability contract

Each registered Data Source declares machine-readable support for applicable operations/semantics:

- source ID, owner, adapter/version;
- entity identity type and portable identity strategy;
- site/network/global scope rules;
- `get_one`;
- `list/query`;
- `create`;
- `update`;
- `delete`;
- bulk read/write where supported;
- schema/field descriptors;
- searchable/sortable/filterable fields/operators;
- projection support;
- pagination/cursor/count semantics;
- optimistic concurrency/version tokens where supported;
- transaction class/limitations;
- batch hydration capability;
- relation hooks/references where applicable;
- cache/invalidation generations;
- privacy classification metadata;
- authorization/Policy resolver;
- event/audit integration;
- import/export identity mapping;
- lifecycle/degraded-state metadata.

Unsupported semantics are rejected, not silently emulated as “close enough” unless an explicitly bounded, equivalent fallback is certified.

---

## 4. Independent certification classes

- `DSR-R` — registry identity/ownership/discovery;
- `DSR-S` — schema/type/field capability truth;
- `DSR-A` — authorization, Policy and scope isolation;
- `DSR-Q` — read/list/query/pagination/count semantics;
- `DSR-W` — create/update/delete/bulk mutation semantics;
- `DSR-C` — concurrency/transactions/idempotency;
- `DSR-P` — first-party WordPress provider adapters;
- `DSR-T` — Custom Table/module-owned entity adapters;
- `DSR-X` — remote/ecosystem/extension adapters;
- `DSR-I` — identity/relations/import-export/lifecycle;
- `DSR-O` — cache/events/privacy/observability/performance/Multisite.

Passing one class never certifies another.

---

# 5. Fixed executable fixture matrix

## A. Registry identity, ownership and discovery — DSR-01…DSR-16

- **DSR-01** — valid first-party source registers stable namespaced source ID + owner + adapter version.
- **DSR-02** — duplicate source ID with different owner/semantics is rejected; no discovery-order overwrite.
- **DSR-03** — third party cannot claim reserved first-party source namespace.
- **DSR-04** — source registration is idempotent within repeated bootstrap.
- **DSR-05** — source discovery order does not change authoritative registry result.
- **DSR-06** — disabled/unavailable owner module makes source explicit unavailable/degraded rather than dangling callable adapter.
- **DSR-07** — adapter dependency missing produces local degraded state without fataling unrelated sources.
- **DSR-08** — adapter version incompatibility is explicit and does not reuse stale descriptor.
- **DSR-09** — source descriptor records site/network/global ownership class explicitly.
- **DSR-10** — source instance identity is separate from entity-record identity.
- **DSR-11** — registry discovery performs no entity/provider mutation.
- **DSR-12** — registry cache invalidates on module/adapter/version/availability change.
- **DSR-13** — malformed source descriptor fails registration before consumer use.
- **DSR-14** — unsupported future descriptor version fails safe/degraded.
- **DSR-15** — authorized diagnostics list source/capability/version without leaking protected records/secrets.
- **DSR-16** — large registry lookup remains bounded and avoids provider network calls during ordinary discovery.

## B. Schema, field descriptors and capability truth — DSR-17…DSR-32

- **DSR-17** — schema declares stable field keys/types/nullability/cardinality independently from UI labels.
- **DSR-18** — protected/internal field can exist in provider storage while remaining excluded from public generic schema.
- **DSR-19** — read-visible and write-allowed field sets remain distinct.
- **DSR-20** — source-level read capability does not imply every field readable.
- **DSR-21** — source-level write capability does not imply every field writable.
- **DSR-22** — searchable capability is declared per field/operator where semantics differ.
- **DSR-23** — sortable capability is declared per field with null/collation semantics where relevant.
- **DSR-24** — filter capability rejects unsupported operator/type pair rather than coercing silently.
- **DSR-25** — projection capability exposes only registered safe fields.
- **DSR-26** — schema version/generation change invalidates stale Query/Field/renderer descriptors.
- **DSR-27** — required/default/generated/read-only/immutable field semantics are machine-readable.
- **DSR-28** — secret/credential fields are Vault references or excluded; generic source schema never exposes plaintext secret contract.
- **DSR-29** — entity reference field declares target source/type and does not encode display label as canonical identity.
- **DSR-30** — unknown future field descriptor is rejected or preserved safely according version contract; no lossy save.
- **DSR-31** — schema introspection is bounded and does not scan all runtime records.
- **DSR-32** — UI/AI schema discovery cannot infer create/update/delete authorization from visible controls/schema alone.

## C. Authorization, Policy and scope isolation — DSR-33…DSR-48

- **DSR-33** — unauthenticated protected source read denies before record disclosure.
- **DSR-34** — authenticated actor lacking operation capability cannot read/write despite knowing source/entity ID.
- **DSR-35** — capability granted but target resource Policy denied blocks disclosure/mutation.
- **DSR-36** — get-one IDOR across resources returns no protected existence/data oracle beyond safe error contract.
- **DSR-37** — list/query result is row/resource Policy filtered/denied according provider contract.
- **DSR-38** — field-level Policy prevents protected projection despite row visibility.
- **DSR-39** — create authorization is checked independently from list/read access.
- **DSR-40** — update authorization is checked on current target and requested field changes.
- **DSR-41** — delete authorization is separate high-impact operation; read/update grant does not imply delete.
- **DSR-42** — bulk operation reauthorizes each applicable target or uses proven equivalent scoped policy plan.
- **DSR-43** — caller-supplied site/network coordinate cannot expand source ownership/authorization.
- **DSR-44** — current blog context is not durable scope authority for source/entity identity.
- **DSR-45** — network source cannot be mutated by Site Admin merely through child-site context.
- **DSR-46** — public REST/AI exposure is opt-in and separate from internal source registration.
- **DSR-47** — authorization cache includes principal/resource/site/network/generation dimensions required for safe reuse.
- **DSR-48** — provider/Policy failure defaults safe for protected operations and normalizes through ERR contract.

## D. Get-one, list/query, projection and pagination — DSR-49…DSR-64

- **DSR-49** — get-one returns canonical entity descriptor/value shape for authorized existing entity.
- **DSR-50** — missing entity is distinct from unauthorized entity only where disclosure policy permits.
- **DSR-51** — list has bounded default/max page size.
- **DSR-52** — deterministic pagination uses stable ordering/tie-breaker where required.
- **DSR-53** — offset pagination truth is explicit; no snapshot claim under concurrent writes unless provider supports it.
- **DSR-54** — keyset/cursor token is opaque, tamper-resistant/validated and bound to source/query/scope/order context.
- **DSR-55** — actor loses authorization between pages; next page reauthorizes.
- **DSR-56** — exact/approximate/unknown count semantics remain distinct.
- **DSR-57** — counts cannot reveal hidden unauthorized cohorts.
- **DSR-58** — selected projection contains no undeclared provider fields or accidental `*` leakage.
- **DSR-59** — unknown requested field/operator fails validation before provider execution.
- **DSR-60** — source advertises no sort/filter support and consumer rejects request rather than client-side unbounded emulation.
- **DSR-61** — bounded equivalent post-filter fallback, if supported, records truncation/semantic proof and never overclaims provider support.
- **DSR-62** — list/read result canonicalization preserves null/missing/empty/type semantics.
- **DSR-63** — batch get hydrates many IDs without per-entity N+1 when provider advertises batch capability.
- **DSR-64** — request diagnostics capture provider calls/query count/rows/timing without private payload leakage.

## E. Create, update, delete and bulk mutations — DSR-65…DSR-80

- **DSR-65** — create validates source writable/create capability before mutation.
- **DSR-66** — create rejects unknown/protected/read-only/generated fields.
- **DSR-67** — create server-validates schema/types/business guards independent of UI.
- **DSR-68** — update uses canonical entity identity and cannot retarget another source/type through forged payload.
- **DSR-69** — update rejects immutable/system/security fields not exposed by adapter.
- **DSR-70** — partial patch vs full replace semantics are explicit; omitted field is not accidentally nulled.
- **DSR-71** — delete is rejected for read/update-only source.
- **DSR-72** — delete behavior distinguishes soft/archive/trash/hard delete according owning provider semantics.
- **DSR-73** — provider delete does not imply relation/file/external cascade unless explicitly declared and authorized.
- **DSR-74** — bulk create validates each record and reports partial/atomic semantics truthfully.
- **DSR-75** — bulk update cannot widen field/source/site authority through one privileged batch envelope.
- **DSR-76** — bulk delete requires explicit impact/authorization and does not accept unbounded “all records” from low-trust input.
- **DSR-77** — mutation emits stable canonical result/identity rather than raw provider internals.
- **DSR-78** — failed mutation cannot be reported success because adapter returned truthy/HTTP 2xx with invalid semantic result.
- **DSR-79** — unsupported operation returns stable capability error and never invokes fallback private DB path.
- **DSR-80** — mutation Audit/Event emission occurs according owner commit semantics without exposing sensitive before/after payloads by default.

## F. Concurrency, version tokens, transactions and idempotency — DSR-81…DSR-96

- **DSR-81** — optimistic update with current version/ETag succeeds where advertised.
- **DSR-82** — stale version/ETag yields explicit conflict, not lost update.
- **DSR-83** — source that lacks optimistic concurrency does not advertise strong lost-update protection.
- **DSR-84** — create idempotency key prevents duplicate logical create where adapter claims idempotent create.
- **DSR-85** — delete retry semantics distinguish already-deleted from unknown provider outcome safely.
- **DSR-86** — transaction capability states scope: none / single record / provider transaction / batch, not generic boolean overclaim.
- **DSR-87** — single-provider transaction rolls back/commits according declared atomic boundary.
- **DSR-88** — two sources in one workflow are not labeled distributed transaction merely because both individually support transactions.
- **DSR-89** — crash after provider commit before WPE response becomes reconciliation/unknown-outcome when result cannot be proven.
- **DSR-90** — duplicate event/job mutation rechecks business/idempotency precondition.
- **DSR-91** — concurrent uniqueness is only claimed where owning storage/provider proves it.
- **DSR-92** — row/entity lock scope does not globally serialize unrelated sources/sites without evidence.
- **DSR-93** — long transaction/resource exhaustion is bounded by provider/context policy.
- **DSR-94** — stale adapter/schema generation cannot commit using superseded write mapping.
- **DSR-95** — concurrent lifecycle disable/removal invalidates mutation before unsafe side-effect commit where precondition applies.
- **DSR-96** — transaction/concurrency errors map to stable ERR conflict/integrity/unknown-outcome semantics.

## G. WordPress-native first-party providers — DSR-97…DSR-112

- **DSR-97** — Posts/CPT provider uses supported WordPress APIs and preserves post type/status/capability semantics.
- **DSR-98** — Post provider does not expose protected internal post/meta fields through generic schema.
- **DSR-99** — Terms provider preserves taxonomy ownership/hierarchy and uses supported term APIs.
- **DSR-100** — Users provider separates profile data from roles/capabilities/password/session/application-password security internals.
- **DSR-101** — Comments provider preserves comment status/object/user ownership and native capability semantics.
- **DSR-102** — Media provider treats attachment metadata/files as governed resources; public URL does not imply delete/write permission.
- **DSR-103** — Options/settings provider is configuration-oriented and does not accept arbitrary option-key access through generic caller input.
- **DSR-104** — network option source requires explicit network scope and authorization.
- **DSR-105** — post/user/term/comment meta access uses registered/allowlisted schema rather than arbitrary meta-key dump/edit.
- **DSR-106** — WordPress trash/restore lifecycle is represented truthfully where entity type supports it.
- **DSR-107** — WordPress revisions do not get generalized to users/terms/comments/entities that lack equivalent semantics.
- **DSR-108** — WP Query APIs used for supported list/query semantics; global main query is not mutated unexpectedly.
- **DSR-109** — native hooks/events triggered by mutations remain compatible and do not cause duplicate WPE mutation loop.
- **DSR-110** — multisite user identity remains global while site membership/roles/source scope stay site-aware.
- **DSR-111** — WP provider downgrade/core-version capability change enters explicit compatible/degraded state.
- **DSR-112** — 10k/100k/1M representative WP source workloads record query count/latency/memory without security or semantic relaxation.

## H. Custom Tables and module-owned entity providers — DSR-113…DSR-128

- **DSR-113** — Custom Table source registers only WPE-managed logical table/schema identifiers, never caller-supplied arbitrary table names.
- **DSR-114** — physical table/column names resolve from registered schema and remain outside public payload authority.
- **DSR-115** — typed row identity and schema generation are explicit.
- **DSR-116** — Custom Table source capabilities reflect actual indexed/query/constraint evidence rather than configuration intent.
- **DSR-117** — module-owned Form Entry source exposes only Entry owner-approved fields/actions.
- **DSR-118** — Membership entity source does not expose billing/provider/security state as generic writable fields.
- **DSR-119** — Workflow/Job/Audit historical rows are not generically mutable merely because represented as entities.
- **DSR-120** — Notification/Chat private entities enforce participant/recipient Policy per operation.
- **DSR-121** — module source disable preserves data while adapter availability becomes degraded per MLC.
- **DSR-122** — schema migration-required state blocks stale reads/writes when semantic mapping is unsafe.
- **DSR-123** — custom-table source delete respects relation/dependency/retention rules; no arbitrary cascade.
- **DSR-124** — high-volume batch hydration/query stays bounded and avoids N+1 consumer behavior.
- **DSR-125** — generated/materialized/search projection is labeled derivative, not canonical entity truth.
- **DSR-126** — restore/rebuild invalidates/reconciles derivative source before trusted reads.
- **DSR-127** — shared/network custom-table source ownership is explicit and cannot be treated as child-site local by current blog.
- **DSR-128** — CTB/FST/domain protocol evidence remains authoritative for physical schema/value correctness; DSR certifies adapter contract only.

## I. Remote/ecosystem/extension providers — DSR-129…DSR-144

- **DSR-129** — remote source registers through approved Connection/Safe HTTP adapter, not arbitrary URL callback.
- **DSR-130** — credentials are Vault-owned references and absent from Data Source Definition/query payload.
- **DSR-131** — remote source capability map names exact supported provider/API/profile version.
- **DSR-132** — remote filter/sort/pagination advertised only when provider semantics match declared WPE semantics.
- **DSR-133** — provider returns schema drift; response validation fails/degrades before protected consumer use.
- **DSR-134** — timeout/429/outage normalizes safely and does not fabricate empty-success/cache truth.
- **DSR-135** — unknown remote mutation outcome becomes reconciliation state, not automatic retry if unsafe.
- **DSR-136** — remote cached records are locally reauthorized before disclosure.
- **DSR-137** — SSRF/redirect/DNS/private-network controls remain Safe HTTP responsibility and cannot be bypassed by source config.
- **DSR-138** — WooCommerce source uses certified public integration APIs/capabilities; private table assumptions are not universal contract.
- **DSR-139** — removed ecosystem plugin makes source degraded without fataling registry or deleting mappings.
- **DSR-140** — adapter upgrade/downgrade respects SDK/VER compatibility and does not silently reinterpret stored config.
- **DSR-141** — third-party source cannot register arbitrary PHP/SQL/eval execution as generic query/write primitive.
- **DSR-142** — third-party schema cannot claim reserved protected field IDs or first-party namespace.
- **DSR-143** — malicious adapter exception/failure is isolated where possible; registry remains diagnosable.
- **DSR-144** — provider certification remains separate; DSR registration success is never marketed as provider-supported/certified.

## J. Identity, Relations, import/export and lifecycle — DSR-145…DSR-160

- **DSR-145** — canonical record identity includes source/type/scope dimensions required to avoid cross-source numeric-ID collision.
- **DSR-146** — portable identity/reference never assumes local numeric DB ID survives export/import.
- **DSR-147** — relation endpoint reference resolves exact registered Data Source/type and current Policy.
- **DSR-148** — missing/deleted relation target becomes typed unavailable/orphan state without stale private snapshot leakage.
- **DSR-149** — import maps source identity through explicit identity map; does not guess by display label alone.
- **DSR-150** — export includes only allowed fields/records and excludes secret/provider internals by default.
- **DSR-151** — source/module disable makes consumer Definitions explicit degraded/read-only while preserving stable references.
- **DSR-152** — source re-enable validates schema/adapter version before consumers resume writes.
- **DSR-153** — source hard-delete/cleanup checks incoming Definitions/Relations/Queries before removal.
- **DSR-154** — source UUID/key is never silently reused for semantically unrelated provider after deletion.
- **DSR-155** — source rename/alias migration preserves stable identity and rejects collision.
- **DSR-156** — unknown future source descriptor imported is inspectable/deferred where possible, not activated by lossy downgrade.
- **DSR-157** — Pro expiry preserves deployed safe read/enforcement behavior according domain contract without granting paid edit/write.
- **DSR-158** — plugin uninstall default preserve does not destroy source-backed business data without explicit MLC cleanup scope.
- **DSR-159** — site clone/restore re-resolves source scope/connections/secrets and cannot clone remote authority blindly.
- **DSR-160** — REL/IM/VER/MLC evidence remains separate; DSR certifies source identity/adapter lifecycle interaction only.

## K. Cache, events, privacy, observability, performance and Multisite — DSR-161…DSR-176

- **DSR-161** — cache key includes source/adapter/schema/site/scope/query/parameter/principal generation dimensions required by visibility semantics.
- **DSR-162** — mutation invalidates source/entity/list/count caches through explicit generation/dependency contract.
- **DSR-163** — privileged source cache cannot be reused by anonymous/lower-privilege/cross-site caller.
- **DSR-164** — entity mutation emits typed event only after owning commit semantics permit it; event is not source of authorization.
- **DSR-165** — duplicate/out-of-order events cannot cause cache/index/source projection corruption without idempotency/state checks.
- **DSR-166** — Audit records source/action/entity safe ID/outcome/correlation, not full sensitive records by default.
- **DSR-167** — P2/P4 data retains classification through generic source reads, exports, caches and diagnostics.
- **DSR-168** — P3 secret fields never appear in generic read/list/query/cache/event/audit/export paths.
- **DSR-169** — privacy eraser/exporter calls owning source/domain API and respects retention/integrity; direct generic delete is not assumed.
- **DSR-170** — Site A source/entity/cache cannot be read or mutated from Site B through shared registry/key collision.
- **DSR-171** — network aggregation source requires explicit network authority and does not become ordinary child-site list/query.
- **DSR-172** — source registry at 100/1k/10k sites avoids eager per-site provider initialization on every request.
- **DSR-173** — representative source list/batch/query workloads record provider calls, DB queries, p50/p95/p99, memory and N+1 count.
- **DSR-174** — source capability downgrade invalidates dependent Query/Field/Listing/REST/Blueprint compiled artifacts before incompatible execution.
- **DSR-175** — stop-line on unauthorized/wrong-site record or protected-field disclosure/mutation; count must remain zero.
- **DSR-176** — final cross-provider regression matrix covers certified WordPress-native, Custom Table/module-owned and approved remote/ecosystem profiles without generalizing beyond executed evidence.

---

## 6. MUST NOT / stop-the-line rules

Future implementation/evidence MUST NOT:
- infer write/delete capability from read/schema discovery;
- expose arbitrary post/user/term/comment meta or option/table/column names through generic caller input;
- let UI/AI/REST infer authorization from source visibility;
- silently emulate unsupported filter/sort/query semantics and call them equivalent;
- trust current blog as durable source/entity ownership;
- use remote provider response as local authorization;
- treat transaction support as cross-provider distributed transaction;
- expose P3 secrets through generic source interfaces;
- reuse privileged cache across lower-privilege/site contexts;
- let one source adapter mutate another domain's private storage via supported generic path;
- promote Data Source registration to provider certification;
- promote DSR success to QRY/FST/CTB/REL/KPA/PDL/VER/MLC/provider certification.

Stop the line on:
- unauthorized or wrong-site record disclosure/mutation;
- protected-field leakage;
- secret plaintext in source/cache/event/audit/export paths;
- arbitrary table/column/meta/option/provider URL access;
- semantic query mismatch presented as correct result;
- lost update where strong concurrency was advertised;
- cross-site source/cache contamination;
- destructive lifecycle cleanup outside owning scope;
- provider unknown outcome reported as definite safe retry/success.

---

## 7. Required future evidence report

For every applicable fixture record:
- DSR ID/name;
- source ID/entity type/owner/adapter + exact version;
- schema/capability descriptor version;
- WordPress/PHP/DB/Multisite/provider profile;
- site/network/source scope;
- principal + Capability/Policy context;
- operation and requested fields/query/mutation class;
- expected capability/authorization/transaction semantics;
- observed result;
- provider call/query count, timing/memory where relevant;
- wrong-scope/unauthorized record/field count;
- cache/event/audit/privacy observations;
- domain evidence refs (QRY/FST/CTB/REL/KPA/PDL/VER/MLC/WC/etc.);
- Pass/Fail/Blocked;
- known risk/deviation;
- retest state.

Overall report states independently which source/adapter/operation classes are Verified, Not Verified, Unsupported or Degraded.

---

## 8. Current truth

- DSR fixtures documented: **176**.
- DSR fixtures executed: **0/176**.
- Data Source runtime/provider certifications: **0**.
- No source registration, WordPress entity operation, database/custom-table query, remote HTTP/provider call, mutation, cache benchmark, import/export, Multisite operation or runtime test was executed by writing this protocol.

## Development-consent gate

**Do not execute any Data Source/provider registration, entity read/write/delete, Query, database/custom-table operation, remote HTTP/provider call, cache/event mutation, benchmark, import/export or Multisite fixture until explicit owner consent under ADR-0014 and `/DEVELOPMENT-CONSENT.md`.**
