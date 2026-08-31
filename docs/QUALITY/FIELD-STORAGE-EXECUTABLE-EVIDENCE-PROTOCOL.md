# WPEssential — Field Storage / Custom Fields Executable Evidence Protocol

Status: **Accepted planning candidate / execution pending / no executable authorization**  
Date: 2026-08-28  
Work package: `P0-M00-WP17`  
Related: ADR-0022, ADR-0087, Field Storage Architecture Alternatives, Field Storage Physical Routing & Migration Benchmark Profile, Custom Fields Exhaustive Spec, Query ADR-0131, Relations ADR-0133, Vault ADR-0124, Custom Tables ADR-0088, Import/Export, Privacy, Multisite, ADR-0014.

## 1. Purpose

Freeze one bounded adversarial evidence contract for Custom Fields runtime value storage before any storage adapter is implemented or certified.

The product uses a **plural storage architecture** rather than one universal EAV/JSON/custom-table format:

- **FS1** — native WordPress object/meta/options storage where ownership/workload are natural;
- **FS2** — WPE typed Custom Table column for scale, constraints, Q3/Q4 queryability or application entities;
- **FS3** — first-class child rows for genuinely queryable structured/repeater data;
- **FS4** — Relations Engine for relationship/cardinality/reverse/pivot semantics;
- **FS5** — Vault reference for secrets;
- **FS6** — rebuildable derived/search/materialized projection.

This protocol does not select one universal adapter, custom-table topology, cache backend, migration chunk size or numeric performance threshold.

## 2. Non-negotiable truth boundaries

1. Field Definition ≠ runtime value ≠ editor control ≠ presentation/return format.
2. Logical type, canonical value, storage adapter and renderer representation remain separate.
3. Field Definition publish does not imply runtime value migration completed.
4. No adapter may advertise a higher Q0–Q4 queryability class than its physical/index evidence supports.
5. Null, missing, empty and default/inherited states are explicit and cannot be collapsed accidentally.
6. Required validation is server-side; HTML/editor state is never the sole enforcement boundary.
7. Hard concurrent uniqueness requires a proven transactional/DB guarantee; FS1 meta does not get such a claim by UI validation alone.
8. Relationship semantics needing reverse lookup/pivot/cardinality belong to FS4 Relations, not serialized IDs.
9. Secret plaintext is not Field Storage; persisted secret value is FS5 Vault reference + safe metadata only.
10. Derived/materialized/search values are FS6 projections and remain rebuildable derivatives, not source truth.
11. Storage does not automatically imply REST/Ability/export exposure.
12. Storage/type changes use explicit Migration Plans; incompatible/lossy changes require conflict policy and recovery.
13. Native WordPress ownership/site semantics are preserved; network Definitions do not silently globalize site runtime values.
14. Import/export uses stable field/schema identities and typed values, not arbitrary raw DB representation.
15. Private/personal/sensitive/secret classifications affect default export/log/support/cache behavior but never replace explicit Policy.
16. Performance may justify routing escalation, never semantic weakening.

## 3. Evidence discipline

Every future fixture records at minimum:
- fixture ID;
- Field Definition UUID + immutable revision/schema version;
- logical type/cardinality;
- FS adapter/profile + adapter version;
- target entity/Data Source + site/network scope;
- actor/principal class;
- input state and normalized canonical value fingerprint with sensitive values redacted;
- storage operation/read path;
- queryability/uniqueness/revision/privacy capability class claimed;
- query count/query plan/index evidence where applicable;
- p50/p95/p99 or operation duration as applicable;
- memory/storage/write amplification where applicable;
- migration/checkpoint/cutover state where applicable;
- wrong-scope/unauthorized returned/mutated rows;
- secret/plaintext leakage count;
- outcome/failure category;
- evidence artifact/reference.

Wrong-scope/unauthorized mutations or disclosures must remain **0**. Secret plaintext leakage must remain **0**.

## 4. Fixed fixtures — FST-01…FST-176

### A. Field Definition / adapter contract — FST-01…FST-08

- **FST-01** — create Draft Field Definition separating logical type/editor/storage/presentation.
- **FST-02** — publish immutable schema revision; runtime reads/writes resolve exact published Field schema.
- **FST-03** — Draft storage/type change does not alter live runtime values before migration/publish plan.
- **FST-04** — adapter declares target/logical-type/cardinality/queryability/uniqueness/revision/privacy capabilities machine-readably.
- **FST-05** — unsupported field/adapter combination fails Publish validation instead of degrading silently.
- **FST-06** — adapter version incompatibility yields explicit degraded/migration-required state.
- **FST-07** — module/Pro disable preserves Field Definitions/runtime values according ownership/lifecycle; no silent purge.
- **FST-08** — re-enable validates schema/adapter compatibility before writes resume.

### B. Null / missing / empty / default semantics — FST-09…FST-16

- **FST-09** — no stored value remains distinguishable from explicit null where schema permits.
- **FST-10** — empty string remains distinct from missing/default when allowed.
- **FST-11** — empty list/document remains distinct from null/missing.
- **FST-12** — static default applies only when value is genuinely unset according to schema.
- **FST-13** — dynamic/context default applies only under declared creation/unset rule and does not overwrite committed value.
- **FST-14** — inherited/default value is not persisted as explicit local override unless operation requests materialization.
- **FST-15** — migration preserves null/missing/empty/default distinctions or reports explicit lossy class.
- **FST-16** — REST/Ability/export serialization preserves documented null/empty semantics consistently.

### C. Validation / normalization / security — FST-17…FST-24

- **FST-17** — required rule enforced server-side on direct/API write.
- **FST-18** — min/max scalar/length/item-count rules apply after documented normalization order.
- **FST-19** — safe regex/pattern limits prevent pathological evaluation/resource abuse.
- **FST-20** — allowed-value enforcement uses canonical values, not presentation labels.
- **FST-21** — registered validator may run only through typed trusted registry; no arbitrary PHP callback/eval.
- **FST-22** — sanitization/normalization is separate from validation; invalid input is not silently coerced into misleading valid value.
- **FST-23** — malicious string/JSON/HTML/code corpus remains data and cannot become executable PHP/JS/SQL.
- **FST-24** — validation error diagnostics redact sensitive/secret values.

### D. Core scalar type fidelity — FST-25…FST-32

- **FST-25** — string/text newline/trim/case rules preserve declared semantics.
- **FST-26** — integer range/signedness/step and canonical integer storage.
- **FST-27** — decimal/currency/percentage store locale-independent canonical numeric value.
- **FST-28** — boolean stores canonical boolean, not ambiguous display labels.
- **FST-29** — date remains pure calendar date without timezone shift.
- **FST-30** — time remains declared local logical time/precision.
- **FST-31** — datetime stores/compares instant + timezone semantics according to profile.
- **FST-32** — JSON canonical value validates depth/size/schema limits before save.

### E. Choice / multi-value / cardinality semantics — FST-33…FST-40

- **FST-33** — single choice stores canonical value independent of label/icon/image.
- **FST-34** — multiple choice stores typed ordered/unordered logical list according to schema, never comma-concatenated ambiguity.
- **FST-35** — min/max selected enforced server-side.
- **FST-36** — unique-items rule rejects/normalizes duplicates deterministically.
- **FST-37** — option-source duplicate canonical values are rejected.
- **FST-38** — dynamic Query option source cannot expand write authorization merely because an option is displayed.
- **FST-39** — remote option source only uses certified Connection/Query boundary and stores no credentials in Field Definition/value.
- **FST-40** — custom free option/shared-source mutation requires separate capability and validation.

### F. FS1 WordPress native post/meta behavior — FST-41…FST-48

- **FST-41** — post/CPT scalar uses registered post meta when profile supports it.
- **FST-42** — object subtype scoping prevents field from attaching to unintended post subtype.
- **FST-43** — single vs multiple registered-meta semantics match logical cardinality.
- **FST-44** — sanitize/auth/read/write behavior is server-side and not bypassed by direct REST/meta path.
- **FST-45** — post-meta revision support is claimed only on certified WP/meta profile and preserves value revision semantics.
- **FST-46** — meta query Q1/Q2 behavior is truthful; slow technical query does not become Q3/Q4 claim.
- **FST-47** — native post deletion/revision/restore lifecycle preserves or removes field values according to WordPress contract.
- **FST-48** — 10k/100k/1M post/meta query workloads capture joins/rows examined/index/storage/latency without semantic relaxation.

### G. FS1 user/term/comment/options behavior — FST-49…FST-56

- **FST-49** — user meta field cannot target protected role/capability/password/session/core security keys.
- **FST-50** — user personal-data exporter/eraser mapping follows classification/retention policy.
- **FST-51** — term meta field preserves taxonomy/term ownership and no post-revision claim.
- **FST-52** — comment meta field is limited to actual comment-domain ownership.
- **FST-53** — site option is bounded configuration, not unbounded row-per-business-record storage.
- **FST-54** — network option requires explicit network scope/Super Admin policy; site value never silently promotes.
- **FST-55** — autoload policy remains explicit; large/sensitive settings are not auto-autoloaded by convenience default.
- **FST-56** — site/network option cache keys/invalidation do not cross scope.

### H. FS2 typed Custom Table columns — FST-57…FST-64

- **FST-57** — FS2 field can map only to a valid owning Custom Table/schema dependency.
- **FST-58** — logical type→physical column mapping preserves null/default/range/precision semantics.
- **FST-59** — Q3/Q4 claim requires actual index/query-plan evidence.
- **FST-60** — hard uniqueness uses proven DB/transaction guarantee and survives concurrent writes.
- **FST-61** — custom-table row version/concurrency prevents silent lost update where advertised.
- **FST-62** — schema migration generation mismatch prevents stale write through old Field adapter.
- **FST-63** — Custom Table field privacy/export/erase is implemented despite being outside core WP tables.
- **FST-64** — FS2 10k/100k/1M workloads capture type/index/aggregate/storage/write amplification.

### I. FS3 structured / repeater / flexible child rows — FST-65…FST-72

- **FST-65** — bounded non-queryable group may store one structured canonical document with explicit size/depth limits.
- **FST-66** — simple repeated scalar may use native multi-row only when semantics fit exactly.
- **FST-67** — queryable repeater uses stable child identity/order under FS3 rather than opaque serialized blob.
- **FST-68** — child create/update/delete/reorder preserves parent/field/scope identity transactionally.
- **FST-69** — child-level Q3/Q4 filter/sort claim requires indexes/query plans.
- **FST-70** — nested/flexible structure recursion/item/depth limits prevent resource exhaustion.
- **FST-71** — concurrent child reorder/update does not duplicate/drop/resurrect rows.
- **FST-72** — structured blob vs child-row benchmark compares equivalent semantics, not easier reduced functionality.

### J. FS4 entity references / Relations boundary — FST-73…FST-80

- **FST-73** — trivial single local entity reference stores typed canonical identity, not label/URL.
- **FST-74** — many-to-many/reverse/pivot/cardinality requirement routes to Relations rather than serialized IDs.
- **FST-75** — multi-reference field routed to Relations inherits REL authorization/cardinality/scope semantics.
- **FST-76** — display return format object/label/URL never changes canonical stored reference.
- **FST-77** — missing/deleted referenced entity becomes typed unavailable/orphan state; no stale private data leakage.
- **FST-78** — import/export remaps references through stable source identity, never guesses numeric IDs.
- **FST-79** — cross-site reference is rejected unless explicit certified cross-site profile exists.
- **FST-80** — Field Definition cannot independently create a shadow relationship engine parallel to Relations.

### K. FS5 secret / credential fields — FST-81…FST-88

- **FST-81** — persisted secret field stores only opaque Vault reference + safe metadata.
- **FST-82** — secret plaintext never enters post/user/term/comment meta, options, custom-table field, generic JSON payload or Relation pivot.
- **FST-83** — editor reveal requires Vault-specific authorization/re-auth and is not generic Field read.
- **FST-84** — REST/Ability/export returns masked/reference semantics only unless a separately authorized Vault operation explicitly permits resolution.
- **FST-85** — generic logs/audit/support bundles do not contain secret plaintext.
- **FST-86** — generic object/page/query caches never persist secret plaintext.
- **FST-87** — storage migration to/from secret type cannot copy plaintext through ordinary migration staging/logs.
- **FST-88** — missing/locked Vault keeps Field Definition/value reference recoverable without plaintext fallback.

### L. FS6 computed / materialized / search projections — FST-89…FST-96

- **FST-89** — runtime computed field persists no source-of-truth value.
- **FST-90** — materialized computed field records source/version/generation dependencies.
- **FST-91** — source change invalidates/recomputes materialized value according to declared contract.
- **FST-92** — projection failure does not overwrite canonical source truth with stale/partial derivative.
- **FST-93** — search/facet/reporting projection is rebuildable from canonical source.
- **FST-94** — projection cache/index cannot bypass current field/entity authorization.
- **FST-95** — DB generated column is only advertised under certified Custom Table capability/profile.
- **FST-96** — rebuild/reconciliation after restore/schema change restores derived correctness before trusted use.

### M. Queryability Q0–Q4 truth — FST-97…FST-104

- **FST-97** — Q0 field is rejected for runtime filter/sort/aggregate requests.
- **FST-98** — Q1 equality/existence works with documented null/missing semantics.
- **FST-99** — Q2 range/order/filter uses typed comparison and correct collation/time semantics.
- **FST-100** — Q3 indexed-high-volume claim requires accepted plan/latency/index evidence at scale.
- **FST-101** — Q4 aggregate/join claim requires accepted provider/compiler/index evidence.
- **FST-102** — Query Builder rejects unsupported field predicate instead of slow silent fallback.
- **FST-103** — protected field query/count cannot infer unauthorized values/cohorts.
- **FST-104** — adapter capability downgrade invalidates compiled Query/List caches that depended on higher Q class.

### N. Uniqueness / concurrency / writes — FST-105…FST-112

- **FST-105** — validation-only uniqueness is labeled best-effort and not marketed as hard guarantee.
- **FST-106** — application-lock uniqueness race produces one valid winner or deterministic conflict.
- **FST-107** — DB unique constraint race produces one valid canonical value and normalized conflict.
- **FST-108** — bulk write validates all relevant Field policies/types/scope before each committed mutation.
- **FST-109** — concurrent stale entity/field write uses owner adapter's version/concurrency semantics and never silently loses protected update where guarantee is claimed.
- **FST-110** — interrupted write cannot leave half structured/child/projection mutation presented as success.
- **FST-111** — post-commit event/cache invalidation occurs after durable value write.
- **FST-112** — unrelated fields/sites are not globally serialized by uniqueness coordination without explicit bounded reason.

### O. Revision / history semantics — FST-113…FST-120

- **FST-113** — Definition revision and runtime value revision/history remain distinct.
- **FST-114** — native post-meta revisions restore exact certified fields only.
- **FST-115** — user/term/comment fields do not inherit post revision claims automatically.
- **FST-116** — WPE value-history profile, if enabled, is separately authorized/retained from canonical current value.
- **FST-117** — no-value-history adapter truthfully reports inability to restore older runtime values.
- **FST-118** — Field Definition rollback does not reinterpret incompatible current values without migration/reconciliation.
- **FST-119** — revision/history purge follows retention/security policy and cannot remove canonical current value accidentally.
- **FST-120** — Backup/restore preserves advertised value-history semantics or reports unsupported history explicitly.

### P. REST / Abilities / admin / privacy exposure — FST-121…FST-128

- **FST-121** — storage location alone never enables REST exposure.
- **FST-122** — REST read/write Policy enforced server-side independently from editor UI visibility.
- **FST-123** — custom REST field name collision/schema mismatch is detected before Publish.
- **FST-124** — generic Data Source/Ability field access respects schema + field + entity Policy; no auto-created privileged generic mutation.
- **FST-125** — Quick Edit/Admin Columns integration is offered only when owning adapter supports safe batched editing; no duplicate private list-table engine.
- **FST-126** — Public/Internal/Personal/Sensitive/Secret classification applies expected default export/log/support behavior.
- **FST-127** — privacy erase/anonymize/retain action is type/adapter/module aware and auditable.
- **FST-128** — unauthorized field existence/schema metadata is not leaked through REST/Ability/diagnostics where Policy hides it.

### Q. Type change / adapter migration planning — FST-129…FST-136

- **FST-129** — text→integer/date migration reports invalid values before cutover.
- **FST-130** — single→multiple wraps values with exact fidelity when safe.
- **FST-131** — multiple→single requires explicit conflict selection and never discards values silently.
- **FST-132** — FS1 meta→FS2 Custom Table migration preserves identities/null/default/value counts and indexes.
- **FST-133** — FS2→FS1 is allowed only where native target can represent semantics/queryability/constraints without hidden loss.
- **FST-134** — structured blob→FS3 child rows preserves stable item identity/order and invalid-row reporting.
- **FST-135** — FS3 child rows→blob runs only when full fidelity/size semantics are defined; otherwise rejected/lossy class.
- **FST-136** — reference→Relations extraction does not duplicate edges and validates cardinality/pivot semantics.

### R. Migration execution / crash / cutover / rollback — FST-137…FST-144

- **FST-137** — Migration Plan records source/target adapters/schema versions/scope/count/fidelity/dependencies/recovery class.
- **FST-138** — small offline migration freezes writes only for declared bounded window.
- **FST-139** — chunked large migration has durable cursor/checkpoint and idempotent replay.
- **FST-140** — process/DB interruption resumes without double-transform/missing rows.
- **FST-141** — temporary dual-read/dual-write, if used, has formal conflict/source-of-truth rule and cannot become indefinite accidental state.
- **FST-142** — cutover occurs only after source→target counts/fingerprints/invalid-case verification.
- **FST-143** — destructive/lossy migration requires configured verified Backup/recovery boundary before commit.
- **FST-144** — rollback/recovery retains old source for declared window/class and never blindly reverses irreversible lossy transformation.

### S. Import / export / package / portability — FST-145…FST-152

- **FST-145** — Field Definition package stores logical schema/UUID/adapter requirements, not local physical IDs.
- **FST-146** — ordinary configuration export excludes runtime values and secret plaintext.
- **FST-147** — runtime data export serializes normalized typed canonical values independent of physical FS1/FS2/FS3 representation.
- **FST-148** — import into different safe adapter uses registered conversion/migration rather than raw DB shape replay.
- **FST-149** — missing target adapter/module preserves deferred/reportable definition/value package state where safe.
- **FST-150** — future unknown Field schema is inspect/read-only and not lossy downgraded.
- **FST-151** — media/entity references remap through stable identities and do not store source URLs/numeric IDs blindly.
- **FST-152** — re-import/idempotency/conflict handling does not duplicate repeaters/child rows/Relations/projections.

### T. Multisite / scope / clone / restore — FST-153…FST-160

- **FST-153** — post/term/comment native values remain site-owned; identical object IDs across sites do not collide.
- **FST-154** — network-shared user data vs site-owned profile/application field semantics are explicit and Policy-safe.
- **FST-155** — network Field Definition does not automatically make every site's runtime values network-global.
- **FST-156** — FS2/FS3 values inherit owning table's PT-D/PT-E scope and cannot be queried/migrated across site accidentally.
- **FST-157** — single-site export/migration/Backup excludes other-site values.
- **FST-158** — site clone/transfer remaps scope/entity/reference identities and invalidates source-scope caches/projections.
- **FST-159** — 100/1k/10k-site custom-storage migration/health/rebuild operation is bounded and records topology cost.
- **FST-160** — wrong-site entity/value ID/adapter-context attack returns/mutates zero unauthorized values.

### U. Cache / invalidation / dependency changes — FST-161…FST-168

- **FST-161** — canonical read cache key includes entity/value/schema/scope context required by adapter.
- **FST-162** — committed field value update invalidates affected Field/Query/List/renderer caches.
- **FST-163** — Field Definition publish invalidates compiled schema/queryability/runtime adapter artifacts.
- **FST-164** — Role/Membership/Policy revoke cannot keep serving cached protected field value.
- **FST-165** — adapter/schema migration invalidates old physical-routing cache before cutover use.
- **FST-166** — site deletion makes site-scoped field caches/projections unreachable.
- **FST-167** — secret plaintext never enters generic cache during read/reveal.
- **FST-168** — if authorization/invalidation dependency cannot be represented safely, shared persistent caching is disabled.

### V. Scale / adversarial / final certification — FST-169…FST-176

- **FST-169** — 10k representative values across FS1/FS2/FS3 applicable workloads.
- **FST-170** — 100k representative values + filter/sort/bulk/privacy workload.
- **FST-171** — 1M value/row target where infrastructure permits; inability remains NOT VERIFIED.
- **FST-172** — meta OR/range/sort vs equivalent FS2 indexed workload comparison records total semantic/operational cost, not just latency.
- **FST-173** — uniqueness concurrency + high write rate workload preserves invariant.
- **FST-174** — large structured/repeater migration/rebuild memory/chunk/storage behavior remains bounded.
- **FST-175** — independent security/data-integrity/privacy review attacks scope, protected core keys, secrets, REST, migration and cache boundaries.
- **FST-176** — final evidence audit records PASS/FAIL/NOT-APPLICABLE for every applicable fixture and bounds each FS/Q/revision/uniqueness/privacy support claim to exact certified adapter/environment.

## 5. Stop-the-line failures

Field Storage certification stops immediately if:
- secret plaintext is persisted/logged/exported/cached by generic Field Storage;
- wrong-site/network or unauthorized value is returned/mutated;
- protected WordPress user/security keys are generically editable;
- Field Definition publish is presented as completed value migration when migration is pending/failed;
- null/missing/empty/default values are silently collapsed with semantic loss;
- unsupported adapter/queryability/uniqueness/revision capability is silently claimed;
- hard uniqueness is violated under the advertised concurrency contract;
- many-to-many/pivot/reverse relationship is encoded as an opaque generic field instead of certified Relations semantics;
- Q3/Q4 is claimed without physical/index evidence;
- REST/storage exposure bypasses server-side Policy;
- future schema is destructively downgraded;
- migration interruption causes duplicate/lost/wrong-scope values;
- lossy migration executes without explicit conflict/recovery classification;
- cached protected value survives committed authorization revoke;
- projection becomes unrecoverable source truth;
- benchmark speed is used to waive security, fidelity, portability or recovery failure.

## 6. Adapter/profile acceptance

An FS adapter/profile can be runtime-certified only when:
1. its logical type/cardinality/target capabilities are versioned;
2. null/default/validation semantics pass;
3. Policy/privacy/Multisite scope fixtures pass;
4. claimed Q0–Q4/uniqueness/revision capabilities have exact evidence;
5. migration/import/export/recovery semantics pass for the claims exposed;
6. cache/invalidation/revocation dependencies are correct;
7. applicable 10k/100k/1M workloads meet accepted future budgets or publish hard limits;
8. independent security/data-integrity/privacy review passes for the claimed certification scope.

Passing FS1 post meta does not certify user/term/comment/options profiles. Passing one FS2 Custom Table schema/profile does not certify another. Passing Field Storage does not certify Relations, Vault or Custom Tables themselves; those retain their own evidence boundaries.

## 7. Required future evidence report

When owner later authorizes executable work, report:
- exact code/artifact commit;
- WP/PHP/DB environment;
- adapter/profile/schema versions;
- FST fixtures executed/skipped + reason;
- source/target storage DDL/API/profile;
- query plans/indexes/queryability evidence;
- uniqueness/concurrency outcomes;
- null/default/type-fidelity outcomes;
- migration/checkpoint/cutover/rollback evidence;
- REST/Ability/privacy/export/erase outcomes;
- secret-leak checks;
- Multisite/clone/restore outcomes;
- cache/revocation evidence;
- scale/storage/write-amplification metrics;
- independent review result;
- NOT VERIFIED items;
- final support claims bounded exactly to evidence.

## 8. Current evidence state

- FST fixtures documented: **176**
- FST fixtures executed: **0/176**
- Field Storage runtime certifications: **0**
- FS1 certified adapter/profiles: **0**
- FS2 certified adapter/profiles: **0**
- FS3 certified adapter/profiles: **0**
- FS4 certification remains under Relations P-010: **0**
- FS5 certification remains under Vault P-005: **0**
- FS6 certified projection profiles: **0**
- final adapter routing thresholds: **OPEN / evidence-gated**
- exact custom storage/index profiles: **OPEN / evidence-gated**
- independent Field Storage security/data-integrity/privacy review executed: **NO**

## 9. Development gate

This protocol is planning/documentation only.

No `register_meta()`, option/meta write, Custom Table, child-row table, Relation mutation, Vault secret, projection, Query execution, migration/backfill, REST exposure, privacy mutation, cache operation, fixture generation or benchmark is authorized by this document.

ADR-0014 explicit scoped owner consent remains required before every executable Field Storage action.