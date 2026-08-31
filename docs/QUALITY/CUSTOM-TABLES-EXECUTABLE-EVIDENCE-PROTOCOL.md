# WPEssential — Custom Tables Executable Evidence Protocol

Status: **Phase 0 fixed paper evidence contract / execution NOT AUTHORIZED**  
Date: 2026-08-28  
Work package: `P0-M00-WP18`  
Related: ADR-0014, ADR-0023, ADR-0069, ADR-0071, ADR-0075, ADR-0088, ADR-0130, ADR-0131, ADR-0134.

## Purpose

Define the fixed future executable evidence required before WPEssential can certify any Custom Tables physical topology, DDL compiler, migration strategy, data browser/query-console path, lifecycle behavior or large-network operating profile.

This protocol creates no table, SQL, migration, fixture data or benchmark. All execution remains blocked by ADR-0014 explicit scoped owner consent.

## Canonical truth separation

The following are distinct and must never be collapsed:

1. **Table Definition revision** — desired logical/physical schema intent.
2. **Observed physical schema** — DB state discovered by trusted introspection.
3. **Migration Plan** — deterministic reviewed observed→desired change proposal.
4. **Migration Run** — durable execution/recovery state for one approved plan.
5. **Applied schema fingerprint/generation** — post-verification physical truth.
6. **Runtime rows** — application data governed by Data Source/Policy/Field Storage.
7. **Backup/restore evidence** — independent recovery truth.

Publishing a Definition never proves physical migration completion.

## Physical profiles under evidence

- **CT1 / PT-E** — per-site managed physical table; first baseline for ordinary site-owned data.
- **CT2 / PT-D** — shared physical table with explicit trusted site scope; mandatory comparison.
- **CT3** — genuinely network-owned table profile only; never a `site_id = 0` convenience shortcut.

Migration strategy profiles:
- **CM1** direct compatible alteration;
- **CM2** chunked backfill then constraint/index;
- **CM3** shadow/copy/verify/swap;
- **CM4** recovery-only destructive change.

## Environment record

Every future result records WordPress/PHP/DB family+version, storage engine, charset/collation, DB modes, object-cache state where relevant, single-site/Multisite, site count, table profile, migration compiler/profile revision, dataset seed/size, concurrency level and exact WPE artifact/version.

P-001 compatibility evidence controls eligible environments. Unsupported/unrecorded environments cannot produce certification.

# Fixed executable fixtures

## A. Definition, ownership and physical identity — CTB-01…CTB-12

- **CTB-01** — draft Table Definition changes do not mutate physical schema.
- **CTB-02** — publish desired schema without migration leaves explicit pending/degraded physical state.
- **CTB-03** — stable Table UUID survives logical label/key changes.
- **CTB-04** — generated physical name is deterministic, bounded and collision-safe.
- **CTB-05** — reserved/core WordPress table name is rejected.
- **CTB-06** — third-party plugin table cannot be silently claimed as WPE-owned.
- **CTB-07** — external-table inspect mode remains read-only by default.
- **CTB-08** — explicit adoption requires ownership review + baseline fingerprint + exit/recovery plan.
- **CTB-09** — CT1 physical mapping resolves only the target site/table definition.
- **CTB-10** — CT2 physical mapping includes trusted scope independent of request payload.
- **CTB-11** — CT3 requires explicit network-owned Definition and network authority.
- **CTB-12** — network activation/template reuse does not auto-promote CT1 to CT2/CT3.

## B. Identifier and SQL/DDL safety — CTB-13…CTB-24

- **CTB-13** — malicious table identifier input cannot alter generated SQL structure.
- **CTB-14** — malicious column identifier input cannot alter generated SQL structure.
- **CTB-15** — malicious index/constraint identifier input rejected/normalized safely.
- **CTB-16** — duplicate normalized identifier collision is detected.
- **CTB-17** — dynamic values remain prepared/bound where values exist.
- **CTB-18** — raw user SQL is absent from normal Table Definition.
- **CTB-19** — arbitrary PHP/eval transform is absent from migration language.
- **CTB-20** — stacked statement/DDL injection through safe query console is rejected.
- **CTB-21** — `SELECT ... INTO OUTFILE` or equivalent side-effect query is rejected.
- **CTB-22** — locking/destructive query-console operation is rejected.
- **CTB-23** — unsupported provider-specific DDL operation fails before mutation.
- **CTB-24** — diagnostics never expose credentials/secrets or unauthorized row data.

## C. Logical→physical types, defaults and collation — CTB-25…CTB-40

- **CTB-25** — integer logical type maps consistently within certified DB profile.
- **CTB-26** — bigint logical type and unsigned semantics preserve accepted range.
- **CTB-27** — decimal precision/scale mapping preserves exact-value semantics.
- **CTB-28** — boolean representation round-trips canonically.
- **CTB-29** — short-text length boundaries reject overflow rather than silently truncate.
- **CTB-30** — text/long-text mapping preserves UTF-8 content.
- **CTB-31** — date value mapping does not timezone-shift pure date semantics.
- **CTB-32** — time value mapping preserves declared precision.
- **CTB-33** — datetime/timestamp profile documents timezone/zero-date behavior.
- **CTB-34** — JSON/structured logical value behavior is explicit across MySQL/MariaDB profiles.
- **CTB-35** — binary advanced type remains bounded and non-text-assumed.
- **CTB-36** — nullable + default combinations validate before migration.
- **CTB-37** — explicit NULL vs empty scalar semantics round-trip where schema distinguishes them.
- **CTB-38** — charset/collation derives from accepted WordPress/DB profile unless reviewed override exists.
- **CTB-39** — collation change impact is detected and risk-classified.
- **CTB-40** — unsupported physical type/profile is rejected rather than approximated silently.

## D. Keys, indexes and constraints — CTB-41…CTB-56

- **CTB-41** — default numeric primary-key candidate behaves correctly at representative scale.
- **CTB-42** — alternate primary-key type requires provider-compatible identity proof.
- **CTB-43** — missing primary key generates explicit health/risk result.
- **CTB-44** — duplicate equivalent index is detected before creation.
- **CTB-45** — composite index column order is preserved exactly.
- **CTB-46** — index byte/prefix limit checked against certified DB profile.
- **CTB-47** — unique index preflight detects existing duplicates.
- **CTB-48** — concurrent duplicate writes cannot violate a claimed DB-unique invariant.
- **CTB-49** — site-local CT2 unique key includes scope where required.
- **CTB-50** — CT3 network uniqueness cannot be scoped accidentally to one site.
- **CTB-51** — index drop detects active Query/Field/Admin Column dependency.
- **CTB-52** — redundant/unused index evidence is surfaced rather than retained speculatively.
- **CTB-53** — physical FK remains unavailable unless exact certified profile supports lifecycle/upgrade order.
- **CTB-54** — logical Relations do not become implicit DB FK claims.
- **CTB-55** — constraint add fails safely when existing data violates it.
- **CTB-56** — primary-key replacement is classified high-impact with explicit recovery strategy.

## E. Data Source CRUD, Policy and concurrency — CTB-57…CTB-72

- **CTB-57** — authorized create validates schema and Policy server-side.
- **CTB-58** — unauthorized create mutates zero rows.
- **CTB-59** — authorized read returns only permitted row/field scope.
- **CTB-60** — wrong-site CT2 row-ID read returns no protected data.
- **CTB-61** — wrong-site CT2 update mutates zero rows.
- **CTB-62** — wrong-site CT2 delete mutates zero rows.
- **CTB-63** — CT3 network row cannot be mutated by site-only authority.
- **CTB-64** — typed update rejects invalid/overflow value.
- **CTB-65** — optimistic/versioned row write detects stale editor when advertised.
- **CTB-66** — concurrent updates satisfy advertised last-write/conflict contract without partial row corruption.
- **CTB-67** — bulk mutation remains scope/policy checked per effective target set.
- **CTB-68** — failed multi-field validation causes no partial success where operation promises atomic row write.
- **CTB-69** — deleted/disabled Table Definition produces deterministic degraded behavior.
- **CTB-70** — Pro/module disable preserves owned data unless explicit destructive operation is approved.
- **CTB-71** — runtime Data Source does not use arbitrary physical table names supplied by callers.
- **CTB-72** — row/cache state cannot disclose deleted/revoked protected data.

## F. Query P-009 integration — CTB-73…CTB-86

- **CTB-73** — QP2 provider resolves registered table/field schema only.
- **CTB-74** — unsupported AST operator fails before SQL execution.
- **CTB-75** — filter values remain bound/prepared.
- **CTB-76** — sort identifiers are allowlisted schema references.
- **CTB-77** — selected projection hides unauthorized fields independently of row visibility.
- **CTB-78** — deterministic ordering adds accepted tie-breaker for cursor pagination.
- **CTB-79** — crafted cursor cannot cross table/site/revision scope.
- **CTB-80** — deep offset vs cursor plans/rows examined captured.
- **CTB-81** — total count cannot leak hidden rows.
- **CTB-82** — aggregate cannot leak protected cohort information.
- **CTB-83** — indexed Q3/Q4 claim has actual supporting index/query-plan evidence.
- **CTB-84** — unindexed/expensive public query respects cost budget.
- **CTB-85** — cache identity includes table/schema/scope/policy generations where visibility differs.
- **CTB-86** — committed revoke/schema generation change cannot keep serving stale protected result.

## G. Desired schema, introspection and deterministic planning — CTB-87…CTB-102

- **CTB-87** — empty observed DB produces deterministic create plan.
- **CTB-88** — already-matching schema produces deterministic no-op plan.
- **CTB-89** — observed fingerprint includes material schema properties required by planner.
- **CTB-90** — same observed+desired inputs produce same operation ordering.
- **CTB-91** — plan references immutable target Definition revision/schema version.
- **CTB-92** — plan records risk R0–R4 accurately for representative operations.
- **CTB-93** — plan records dependency impact.
- **CTB-94** — plan records expected lock/availability class without false certainty.
- **CTB-95** — plan records required Backup/recovery tier.
- **CTB-96** — stale observed fingerprint blocks/replans before mutation.
- **CTB-97** — manually added harmless external column is classified, not blindly deleted.
- **CTB-98** — missing expected column/index produces drift/degraded state.
- **CTB-99** — changed type/default/collation drift is identified.
- **CTB-100** — missing/renamed physical table is not silently recreated over unknown data state.
- **CTB-101** — ownership conflict blocks automatic corrective migration.
- **CTB-102** — portable import generates target-local plan rather than importing source SQL.

## H. CM1 direct compatible migrations — CTB-103…CTB-114

- **CTB-103** — compatible table creation verifies post-introspection fingerprint.
- **CTB-104** — nullable column add verifies exact type/default/collation.
- **CTB-105** — compatible index add verifies planner/DB result.
- **CTB-106** — widening text/numeric change preserves all source values.
- **CTB-107** — default change preserves existing row values unless explicit semantics say otherwise.
- **CTB-108** — `dbDelta()` is used only when operation/profile evidence proves semantic fit.
- **CTB-109** — `dbDelta()` syntax-sensitive mismatch is detected rather than marked applied.
- **CTB-110** — DB algorithm/lock fallback is observed and reported truthfully.
- **CTB-111** — migration interruption produces explicit recoverable/unknown state, never false applied.
- **CTB-112** — retry/re-entry does not duplicate an already completed compatible operation.
- **CTB-113** — resulting applied generation updates only after verification.
- **CTB-114** — old code/newer incompatible schema path fails safe/degraded according compatibility policy.

## I. CM2 backfill + constraint/index — CTB-115…CTB-128

- **CTB-115** — NOT NULL migration blocks until null precondition/backfill plan exists.
- **CTB-116** — new unique constraint blocks until duplicate resolution is explicit.
- **CTB-117** — string→numeric reports invalid rows without silent coercion.
- **CTB-118** — signed→unsigned detects negative values.
- **CTB-119** — narrowing integer detects out-of-range rows.
- **CTB-120** — VARCHAR narrowing detects over-length rows.
- **CTB-121** — decimal precision/scale reduction reports rounding/loss.
- **CTB-122** — chunk ordering key is deterministic/stable.
- **CTB-123** — resume cursor after crash avoids skip/duplicate transformation.
- **CTB-124** — repeated chunk execution is idempotent under declared transform.
- **CTB-125** — concurrent writes during backfill follow explicit freeze/dual-path strategy.
- **CTB-126** — dual-write is rejected unless its correctness model is explicitly certified.
- **CTB-127** — validation after each chunk detects divergence before final cutover.
- **CTB-128** — final constraint/index occurs only after complete verified backfill.

## J. CM3 shadow/copy/swap — CTB-129…CTB-140

- **CTB-129** — shadow schema exactly matches reviewed target revision.
- **CTB-130** — copy preserves row count/identity under declared consistency model.
- **CTB-131** — copied data fingerprint/sample verification detects corruption/loss.
- **CTB-132** — write activity during copy follows explicit catch-up/freeze strategy.
- **CTB-133** — crash before swap leaves original authoritative and recoverable.
- **CTB-134** — crash during swap resolves to deterministic recoverable state.
- **CTB-135** — crash after swap but before metadata commit reconciles via introspection.
- **CTB-136** — old table/recovery copy retention is explicit and bounded.
- **CTB-137** — recovery-copy deletion cannot precede required verification/retention.
- **CTB-138** — physical identifiers cannot collide during shadow naming/swap.
- **CTB-139** — dependencies/query provider resolve only verified active mapping.
- **CTB-140** — “online/zero downtime” claim requires measured certified behavior, not DB hint presence.

## K. CM4 destructive/high-impact recovery — CTB-141…CTB-152

- **CTB-141** — drop column requires dependency + data-impact preview.
- **CTB-142** — drop table requires explicit destructive action; Definition deletion alone does not drop it.
- **CTB-143** — lossy transform reports exact loss/conflict class before commit.
- **CTB-144** — R3/R4 requires heightened capability/re-auth policy.
- **CTB-145** — required verified Backup exists before destructive commit.
- **CTB-146** — merely-started Backup job is not accepted as recovery proof.
- **CTB-147** — Backup tier/profile required by plan is verified, not assumed.
- **CTB-148** — recovery instructions identify irreversible boundary accurately.
- **CTB-149** — failed destructive migration enters failed_recoverable or failed_recovery_required truthfully.
- **CTB-150** — supported recovery restores schema+data to validated state.
- **CTB-151** — operation cannot claim transactional DDL rollback when DB cannot guarantee it.
- **CTB-152** — cancellation is unavailable/blocked across unsafe mutation boundary where required.

## L. Field Storage, Relations and schema-evolution integration — CTB-153…CTB-162

- **CTB-153** — FS2 field mapping blocks if owning table/schema dependency is missing.
- **CTB-154** — Field Definition publish cannot write against unmigrated incompatible column schema.
- **CTB-155** — field storage migration and table migration coordinate generations without double transform.
- **CTB-156** — relation endpoint/pivot dependency is included in table rename/drop impact.
- **CTB-157** — Query definition dependency is included in column/index rename/drop impact.
- **CTB-158** — Admin Column/REST/Data Source dependencies appear in impact plan.
- **CTB-159** — physical rename never changes portable Table/Field UUID identity.
- **CTB-160** — stale compiled schema/provider cache invalidates after applied generation change.
- **CTB-161** — secret fields remain Vault references; schema migration never logs plaintext secret values.
- **CTB-162** — relation/field derived projections are rebuildable after table restore/migration.

## M. Import/export, data browser, query console and privacy — CTB-163…CTB-172

- **CTB-163** — configuration export carries logical schema, not environment-specific SQL/prefix identity.
- **CTB-164** — CSV/JSON data import validates types and explicit upsert key.
- **CTB-165** — interrupted large import resumes without duplicate committed rows under declared idempotency.
- **CTB-166** — CSV export neutralizes spreadsheet formula injection where applicable.
- **CTB-167** — data browser enforces server pagination and per-row Policy.
- **CTB-168** — bulk data browser action cannot cross site/table scope.
- **CTB-169** — safe query console accepts one bounded SELECT/EXPLAIN only.
- **CTB-170** — safe query console result cap/budget is enforced server-side.
- **CTB-171** — privacy exporter finds owned PII in Custom Tables without leaking unrelated records.
- **CTB-172** — privacy erase/anonymize/retain policy is explicit, auditable and scope-safe.

## N. Multisite, lifecycle, Backup/Restore and scale — CTB-173…CTB-184

- **CTB-173** — CT1 site creation/provisioning maps correct physical table and applied schema version.
- **CTB-174** — CT1 nested/wrong blog context cannot operate on another site's managed table.
- **CTB-175** — CT1 site Backup/clone exports/restores only target table data/schema mapping.
- **CTB-176** — CT1 site delete follows retention/destructive plan rather than blind prefix drop.
- **CTB-177** — CT2 site Backup extracts only trusted-scope rows.
- **CTB-178** — CT2 site delete removes only owned rows and never drops shared table.
- **CTB-179** — CT2 clone/transfer remaps scope safely without raw ID substitution.
- **CTB-180** — CT3 network-owned data survives unrelated site deletion and requires network authority.
- **CTB-181** — 100/1k/10k-site CT1 provisioning+migration fan-out is measured/bounded.
- **CTB-182** — 100/1k/10k-site CT2 shared-index/noisy-neighbor behavior is measured/bounded.
- **CTB-183** — 10k/100k/1M-row query/write/index/migration workloads record query plans, locks, latency, memory, disk and temp amplification.
- **CTB-184** — fresh-server restore + schema reconciliation + independent DB/security/data-integrity review produces no cross-site loss/leak and records known limits.

# Required measurements

Where relevant record:
- query count and EXPLAIN/plan;
- p50/p95/p99 query and mutation latency;
- rows examined/affected;
- lock waits/deadlocks/timeouts/retries;
- DDL algorithm/lock behavior actually observed;
- migration/backfill throughput;
- table/index/data/temp-disk size;
- PHP memory/JobService overhead;
- schema fingerprint before/after;
- provisioning/migration fan-out at network scale;
- wrong-scope/unauthorized rows returned or mutated — required **0**.

# Acceptance hierarchy

1. scope/security/authorization correctness;
2. data integrity/type/constraint correctness;
3. migration/recovery truth;
4. compatibility and lifecycle operations;
5. Query/Field/Relation integration;
6. performance/storage/operational cost.

A faster candidate cannot win after a correctness/security/recovery failure.

# Stop-the-line gates

Custom Tables certification stops if any tested profile permits unchecked identifier/DDL injection; wrong-site CT2 access/mutation; site authority over CT3; silent truncation/lossy conversion; stale-fingerprint migration; false applied state after partial migration; unsafe `dbDelta()` overclaim; unverified destructive operation without required recovery point; one-site lifecycle operation damaging another site's data; schema publish presented as migration success; silent external drift overwrite; query/field/relation dependency break without impact plan; secret plaintext logging; or false online/rollback guarantees.

# Future decision output

An authorized completed Custom Tables evidence package must publish:
- selected CT profile(s) and exact scope eligibility rules;
- exact DB-family/version support matrix;
- exact physical types/lengths/charset/collation;
- PK/index/constraint profiles;
- supported migration operation→compiler strategy matrix;
- `dbDelta()` eligible operation subset;
- CM1/CM2/CM3/CM4 availability/locking/recovery truth;
- JobService/backfill concurrency policy;
- schema-drift/adoption policy;
- Query/Data Source/Field Storage/Relations integration contract;
- Backup/restore/site lifecycle procedures;
- known scale/site-count limits;
- rejected alternatives and evidence artifacts.

## Current evidence state

- CTB fixtures documented: **184**
- CTB fixtures executed: **0/184**
- Custom Tables runtime/DDL/migration certifications: **0**
- CT1 certified profiles: **0**
- CT2 certified profiles: **0**
- CT3 certified profiles: **0**
- CM1 certified operation profiles: **0**
- CM2 certified operation profiles: **0**
- CM3 certified operation profiles: **0**
- CM4 recovery profiles: **0**
- exact DDL/types/indexes/constraints: **OPEN / evidence-gated**
- independent DB/security/data-integrity review: **NOT EXECUTED**

## Development gate

No table, DDL, SQL, introspection, migration plan compiler, schema mutation, row mutation, backfill, shadow/copy/swap, query console execution, import/export job, privacy mutation, Backup/Restore, lifecycle mutation, fixture generator, benchmark or runtime test is authorized by this document.

ADR-0014 explicit scoped owner consent remains required before every executable Custom Tables action.