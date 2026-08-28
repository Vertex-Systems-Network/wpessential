# WPEssential — Admin Columns Executable Evidence Protocol

Status: **Accepted planning protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP19`  
Execution mode: `PLANNER_ONLY`  
Development authorization: **NOT GRANTED**

Related: `docs/ARCHITECTURE/ADMIN-COLUMNS-OPERATIONAL-PROFILE.md`, `docs/MODULES/CUSTOM-TABLES-ADMIN-COLUMNS-EXHAUSTIVE-SPEC.md`, ADR-0098, ADR-0131, ADR-0133, ADR-0134, ADR-0135, ADR-0014.

## 1. Purpose

Freeze the future executable evidence required before WPEssential Admin Columns may claim production-safe support for WordPress/WPE list-table targets, batched data hydration, sorting, filtering, editing, bulk operations, exports, third-party adapters, WooCommerce adapters, DataViews-era compatibility, Multisite isolation or performance.

This protocol does **not** authorize WordPress hooks, REST endpoints, SQL, data writes, exports, jobs, browser execution, package installation, benchmarks or runtime testing.

## 2. Architectural baseline under test

ADR-0098 keeps **AC1 — compiled whole-request Column Execution Plan with batch hydration** as the first operational baseline.

The baseline must prove all of the following before certification:

- row identities are resolved before WPE hydration;
- source adapters expose truthful batch/read/sort/filter/write capability metadata;
- expensive per-row Query/Relation/remote/shortcode loops are rejected or converted to bounded execution;
- sorting/filtering/search semantics execute in the authoritative backend before pagination;
- Policy applies before protected values are fetched or returned;
- inline/bulk writes use owning Field/Data Source APIs and concurrency controls;
- current-page selection and all-filtered selection are different execution contracts;
- export authorization is independent from screen visibility;
- scope/site/actor context is part of hydration and cache identity;
- unsupported targets degrade explicitly rather than pretending feature support.

## 3. Evidence truth model

The following are separate truths:

`Column Set Definition ≠ Compiled Column Plan ≠ target adapter capability ≠ hydrated request data ≠ displayed cell ≠ writable source ≠ export schema ≠ certified runtime behavior`

A displayed value does not prove:
- server-side sortability;
- server-side filterability;
- searchability;
- editability;
- bulk mutability;
- export permission;
- Multisite safety;
- acceptable query cost.

## 4. Certification classes

Per target adapter, capabilities are certified independently:

- `AC-R` — read/render
- `AC-S` — server-side sort before pagination
- `AC-F` — server-side filter before pagination
- `AC-Q` — search/query integration
- `AC-E` — inline edit
- `AC-B` — bulk edit/delete
- `AC-X` — export
- `AC-M` — Multisite/scope safety
- `AC-P` — performance/batching budget

A target may be certified for one class and unsupported for another.

Current certifications: **0**.

## 5. Fixed fixture matrix — AC-01…AC-176

### A. Target registry, definitions and capability honesty — AC-01…AC-16

- **AC-01** Posts list target discovery and row identity.
- **AC-02** Pages/CPT target discovery.
- **AC-03** Users target discovery.
- **AC-04** Media target discovery.
- **AC-05** Comments target discovery.
- **AC-06** Terms/taxonomy target discovery.
- **AC-07** WPE Custom Tables/Data Browser target discovery.
- **AC-08** WPE module list-adapter discovery.
- **AC-09** registered third-party target discovery.
- **AC-10** WooCommerce product target capability inspection.
- **AC-11** WooCommerce order target capability inspection, including current storage mode.
- **AC-12** missing/removed target dependency degrades safely.
- **AC-13** target adapter refuses unsupported sort/filter/edit capability.
- **AC-14** immutable/stable Column Set identity and revision reference behavior.
- **AC-15** shared vs personal view ownership is explicit.
- **AC-16** invalid primary-column configuration resolves to safe deterministic fallback.

### B. Whole-request planning and batch hydration — AC-17…AC-40

- **AC-17** native row property already hydrated, no redundant I/O.
- **AC-18** native post metadata batch preload.
- **AC-19** user metadata batch preload.
- **AC-20** term metadata batch preload.
- **AC-21** taxonomy term batch hydration.
- **AC-22** WPE Field Storage FS1 batch read.
- **AC-23** alternate certified Field Storage adapter batch read.
- **AC-24** Relations count batch for visible row set.
- **AC-25** Relations first-N targets + total count batch.
- **AC-26** Relation pivot projection batch.
- **AC-27** saved Query aggregate bound to visible row IDs as set operation.
- **AC-28** Query source that cannot batch is rejected/degraded.
- **AC-29** media attachment metadata/thumbnails are cache-aware and bounded.
- **AC-30** protected media source obeys Policy.
- **AC-31** deterministic computed/token source with declared dependencies.
- **AC-32** unknown arbitrary callback/eval source rejected.
- **AC-33** allowlisted shortcode side-effect-free list mode.
- **AC-34** unknown shortcode renderer blocked or unsupported.
- **AC-35** registered server-rendered block list mode.
- **AC-36** deliberately N+1 adapter is detected and refused/flagged.
- **AC-37** duplicate source dependencies are coalesced per request.
- **AC-38** adapter chunking scales by bounded batches, not rows.
- **AC-39** failed batch source degrades only affected column where safe.
- **AC-40** unauthorized hidden column is not prefetched.

### C. Sorting, filtering, search and pagination truth — AC-41…AC-64

- **AC-41** lexical sort before pagination.
- **AC-42** numeric sort before pagination.
- **AC-43** date/time sort before pagination.
- **AC-44** boolean sort before pagination.
- **AC-45** null/empty ordering semantics are explicit.
- **AC-46** stable tie-breaker prevents page drift.
- **AC-47** Field Storage sort through owning query adapter.
- **AC-48** Relation-count sort through certified backend path.
- **AC-49** Query-derived sort unsupported path is not advertised.
- **AC-50** filter equals/not-equals typed semantics.
- **AC-51** text contains/prefix/suffix provider capability truth.
- **AC-52** numeric/date range filter.
- **AC-53** taxonomy any/all/none semantics.
- **AC-54** relation-related-to filter.
- **AC-55** relation-count comparison filter.
- **AC-56** dynamic filter option source remains bounded and Policy-aware.
- **AC-57** invalid filter operator/input is rejected before query execution.
- **AC-58** client-side row hiding is never counted as real filter.
- **AC-59** searchable native source.
- **AC-60** searchable Field/Relation source only when backend supports it.
- **AC-61** search-every-rendered-cell fallback is rejected.
- **AC-62** pagination total/count remains consistent with filters.
- **AC-63** saved segment URL does not leak protected/private values.
- **AC-64** sort/filter/search state cannot bypass target authorization.

### D. Views, presentation and WordPress list-table behavior — AC-65…AC-80

- **AC-65** multiple shared views per target.
- **AC-66** personal view does not mutate shared definition.
- **AC-67** role/capability audience chooses presentation after server Policy.
- **AC-68** temporary per-user column visibility preference.
- **AC-69** temporary sort/filter preference isolation.
- **AC-70** valid WordPress primary column retained.
- **AC-71** responsive row actions remain accessible.
- **AC-72** width/min-width behavior scoped to target table only.
- **AC-73** sticky header/column does not corrupt core admin layout.
- **AC-74** conditional formatting uses approved tokens only.
- **AC-75** conditional formatting is not color-only meaning.
- **AC-76** unauthorized edit/view links are omitted.
- **AC-77** broken entity/media link has safe fallback.
- **AC-78** formatted display value remains distinct from canonical value.
- **AC-79** optional assets load only on matching admin screen.
- **AC-80** no global CSS/JS side effects on unrelated wp-admin screens.

### E. Inline edit, authorization and concurrency — AC-81…AC-104

- **AC-81** inline edit disabled when source lacks write capability.
- **AC-82** valid inline edit through owning Field/Data Source API.
- **AC-83** invalid value rejected by canonical validator.
- **AC-84** sanitizer/type conversion parity with normal editor.
- **AC-85** per-row read allowed but write denied.
- **AC-86** protected/derived/internal field cannot be made writable by column config.
- **AC-87** stale version/fingerprint causes conflict, not silent overwrite.
- **AC-88** two concurrent inline edits on same row.
- **AC-89** row deleted between editor open and save.
- **AC-90** source/definition revision changes while editor is open.
- **AC-91** relation edit uses Relations-owned mutation semantics.
- **AC-92** taxonomy edit uses authoritative taxonomy API.
- **AC-93** status edit uses Status-owned transition semantics where applicable.
- **AC-94** custom-table row edit respects row versioning.
- **AC-95** cross-site numeric ID collision cannot redirect write.
- **AC-96** forged row ID/target ID fails authorization.
- **AC-97** CSRF/nonce failure rejects mutation.
- **AC-98** edit response does not echo newly unauthorized protected value.
- **AC-99** dependent cache/projection invalidation occurs or is explicitly queued.
- **AC-100** audit record captures mutation according to risk class.
- **AC-101** safe retry does not duplicate non-idempotent side effects.
- **AC-102** undo unavailable when source cannot guarantee safe restore.
- **AC-103** risky edit confirmation/reauth boundary where required.
- **AC-104** unsupported third-party inline edit degrades without raw meta/SQL fallback.

### F. Bulk selection and mutations — AC-105…AC-128

- **AC-105** checked-current-page bulk selection.
- **AC-106** all-matching-filtered-query selection stores canonical selection query, not browser ID explosion.
- **AC-107** selection query is reauthorized at execution time.
- **AC-108** affected-count preview matches authoritative query.
- **AC-109** set/replace operation.
- **AC-110** clear/null operation preserves null semantics.
- **AC-111** numeric increase/decrease operation.
- **AC-112** numeric percentage transform with typed rounding semantics.
- **AC-113** taxonomy add/remove operation.
- **AC-114** relation bulk operation only if Relations adapter certifies it.
- **AC-115** status bulk operation obeys per-row transition Policy.
- **AC-116** mixed authorized/unauthorized rows report partial failure truthfully.
- **AC-117** one invalid row does not falsely report whole-batch success.
- **AC-118** concurrent source mutation during bulk run.
- **AC-119** large selection routes through JobService after future consent.
- **AC-120** cancel semantics distinguish not-started vs already-applied rows.
- **AC-121** retry does not repeat completed non-idempotent row mutation.
- **AC-122** bulk delete exact count/impact preview.
- **AC-123** bulk delete dependency/Relation restriction.
- **AC-124** bulk delete reauth boundary for destructive severity.
- **AC-125** target removed/adapter disabled while bulk job is pending.
- **AC-126** site archived/deleted while bulk job is pending.
- **AC-127** bulk failure log redacts protected values.
- **AC-128** JobService/backend failure cannot be presented as completed mutation.

### G. Export and spreadsheet safety — AC-129…AC-144

- **AC-129** current-page export.
- **AC-130** selected-row export.
- **AC-131** all-matching-filtered/sorted export uses authoritative query.
- **AC-132** export Policy evaluated independently from on-screen visibility.
- **AC-133** unauthorized hidden field excluded.
- **AC-134** secret/Vault-backed value excluded.
- **AC-135** raw canonical export schema.
- **AC-136** formatted display export schema.
- **AC-137** relation ID vs label export is explicit.
- **AC-138** date/time timezone export semantics are explicit.
- **AC-139** CSV values beginning `=`, `+`, `-`, `@` are formula-safe.
- **AC-140** delimiter/quote/newline encoding safety.
- **AC-141** HTML intended for wp-admin is not copied blindly into CSV.
- **AC-142** large export uses bounded streaming/temp artifact strategy after future evidence.
- **AC-143** export interruption/partial artifact cannot be advertised as complete.
- **AC-144** expired/unauthorized export artifact cannot be downloaded.

### H. Lazy/remote sources, compatibility and degradation — AC-145…AC-160

- **AC-145** eager source within measured budget.
- **AC-146** batched preload source.
- **AC-147** async visible-page source with bounded request fan-out.
- **AC-148** one remote call per row is rejected when no certified exception exists.
- **AC-149** remote timeout produces safe cell/column error state.
- **AC-150** stale cached remote value truth is labeled where used.
- **AC-151** lazy value cannot claim sort/filter when backend cannot query it.
- **AC-152** Woo product adapter current storage compatibility.
- **AC-153** Woo order adapter HPOS/current storage compatibility.
- **AC-154** Woo storage-mode transition invalidates stale adapter assumptions.
- **AC-155** third-party list adapter hook/API missing after plugin update.
- **AC-156** WordPress core list-table behavior at accepted compatibility floor.
- **AC-157** DataViews-era/core-admin screen change compatibility probe.
- **AC-158** missing extension renderer does not fatal list page.
- **AC-159** unsupported adapter status is visible in diagnostics.
- **AC-160** no raw SQL/meta fallback when declared adapter capability is absent.

### I. Multisite, cache isolation and scale — AC-161…AC-176

- **AC-161** Site A/Site B same numeric row IDs remain isolated.
- **AC-162** cache key includes site/scope identity.
- **AC-163** actor/access-sensitive hydration cannot reuse unsafe global cache.
- **AC-164** network-admin target requires explicit network-capable adapter.
- **AC-165** `switch_to_blog()` never substitutes for authorization.
- **AC-166** network screen hydration remains bounded across sites.
- **AC-167** site clone does not inherit invalid personal/shared view ownership.
- **AC-168** site delete/archive invalidates pending unsafe references.
- **AC-169** restore/clone reconciles missing source UUID dependencies as degraded, not remapped by accident.
- **AC-170** 20 rows × 5 columns baseline.
- **AC-171** 100 rows × 20 columns workload.
- **AC-172** 500 rows × 50 columns stress workload only where target permits.
- **AC-173** high-degree Relation column first-N + count workload.
- **AC-174** query count/batch count remains bounded by source/chunk class rather than row count.
- **AC-175** p50/p95 render time, memory, hydrated values and remote-call metrics recorded.
- **AC-176** deliberately pathological configuration triggers warning/block/degradation rather than silent unbounded execution.

## 6. Required measurements

For applicable fixtures record:

- WordPress/PHP/DB versions and target adapter/version;
- target screen and storage mode;
- visible row count/page size;
- active column count and source classes;
- base SQL count;
- additional SQL count;
- adapter batch count/chunk sizes;
- remote-call count;
- cache hit/miss counts where relevant;
- hydrated row/value count;
- p50/p95 request/render duration;
- peak memory;
- Job count/duration for bulk/export cases;
- authorization decisions and denial path;
- final result and artifacts/log references.

Exact performance thresholds remain evidence-gated. No threshold is certified from paper design alone.

## 7. MUST NOT / negative requirements

Admin Columns MUST NOT:

- run one expensive Query/Relation/remote renderer per row as the ordinary default;
- claim sorting/filtering/search when only current HTML rows are manipulated;
- sort formatted strings when canonical typed ordering differs;
- fetch protected values merely to hide them later in CSS/UI;
- bypass owning Field/Data Source/Relation/Status APIs for mutation convenience;
- treat hidden columns as authorization;
- export secrets or unauthorized values;
- execute arbitrary PHP/eval/raw SQL from a column definition;
- silently fall back to raw meta/SQL writes when adapter capability is absent;
- claim Woo/core/third-party compatibility without version/storage-specific evidence;
- let cross-site numeric identity or shared caches leak values;
- report partial bulk/export completion as full success.

## 8. Stop-the-line conditions

Immediately stop executable certification if future evidence shows:

- cross-user, cross-role or cross-site protected-data leakage;
- mutation of a row/site other than the authorized target;
- unsanitized code/script execution from configured source/formatter;
- CSV formula injection not neutralized;
- silent destructive bulk mutation outside the selected authoritative query;
- N+1 or remote fan-out materially proportional to rows where contract required batching;
- client-only sort/filter presented as backend truth;
- unsafe stale-cache reuse across actor/site/policy context;
- undocumented fallback to raw SQL/meta writes;
- fatal admin-screen breakage from missing adapter/dependency.

## 9. Evidence report format

Every future execution batch must report:

`Status / Changed / Why / Research / Tests / Security / Data-Migration / Affected / VCS / Docs-Memory / Known Issues / Not Verified / Next Safe Action`

Additionally record:

- fixture IDs executed;
- pass/fail/blocked per fixture;
- target adapter/storage versions;
- capability classes certified/rejected;
- measurements and artifacts;
- deviations from ADR-0098/AC1;
- remaining unsupported targets/features.

## 10. Current evidence state

- Documented fixtures: **176**.
- Executed fixtures: **0/176**.
- Target adapters runtime-certified: **0**.
- `AC-R/AC-S/AC-F/AC-Q/AC-E/AC-B/AC-X/AC-M/AC-P` certifications: **0**.
- Core list-table hook compatibility: **not runtime verified**.
- DataViews compatibility: **not runtime verified**.
- WooCommerce storage adapters: **not runtime verified**.
- performance thresholds: **OPEN**.

## 11. Development gate

Execution of AC-01…AC-176 requires explicit scoped owner development/executable-evidence authorization under ADR-0014 and the Approval Ledger.

Planning acceptance of this file is not execution consent.