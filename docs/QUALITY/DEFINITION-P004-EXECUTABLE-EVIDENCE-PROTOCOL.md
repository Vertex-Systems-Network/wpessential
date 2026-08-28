# WPEssential — P-004 Definition Repository Executable Evidence Protocol

Status: **Refined fixed evidence contract / execution NOT AUTHORIZED**  
Date: 2026-08-28  
Work package: `P0-M00-WP15`  
Related: ADR-0049, ADR-0069, ADR-0071, ADR-0073, ADR-0092, Contract Versioning & Deprecation, Portable Configuration Package Format, Site Lifecycle, Backup/Restore, ADR-0014.

## 1. Purpose

Define one canonical, fixed, adversarial P-004 evidence contract for selecting and certifying the Definition Repository physical/runtime persistence profile without allowing benchmark speed to override correctness, scope isolation, revision integrity, migration safety, portability or recoverability.

This is an in-place refinement of the protocol accepted by ADR-0092. It replaces no accepted semantic invariant and creates no second Definition Repository protocol.

No SQL, migration, fixture DB, cache operation or benchmark is authorized by this document.

## 2. Canonical architecture under test

Definition Repository stores versioned **configuration definitions**, not arbitrary runtime/business data.

Canonical logical domains remain:
- Definition identity/lifecycle;
- immutable Definition Revision;
- revision-aware Dependency edges;
- current/published pointers;
- module/type schema registry + migrator chain;
- derivative compiled/runtime cache;
- portable UUID/logical references for package/import/export;
- explicit site/network scope coordinates independent of physical table name.

First physical benchmark baseline remains **D1 / PT-C**:
- numeric physical IDs;
- transparent textual portable UUID baseline;
- explicit network/site scope;
- bounded type/machine keys;
- text revision payload baseline;
- SHA-256 payload fingerprint representation benchmarked;
- minimal workload-driven indexes;
- application-enforced same-definition pointer integrity;
- WordPress-derived charset/collation.

Comparisons remain:
- **D2** compact/binary UUID representation;
- **D3** native JSON payload;
- **D4** stronger FK/check-constraint profile where DB compatibility proves safe.

## 3. Non-negotiable truth boundaries

1. Definition identity ≠ immutable Revision ≠ Dependency edge ≠ compiled cache artifact.
2. Draft/current revision can differ from published revision without changing production behavior.
3. Historical revisions are immutable; migration/load does not silently rewrite history.
4. current/published pointers must reference revisions owned by the same Definition.
5. portable identity is UUID/logical reference, never local numeric DB ID.
6. scope is explicit product/security truth even when physical storage is shared PT-C.
7. site-owned and network-owned Definitions are distinct; client-supplied scope never becomes authority.
8. module/type payload validation/migration is owned by registered module/type contracts, not generic Repository guesswork.
9. unknown future schema is degraded/read-only, never lossy downgraded by dropping unknown fields.
10. compiled/runtime caches are derivative; Repository revision remains source of truth.
11. module disable or Pro entitlement loss preserves safe readable configuration and cannot delete user Definitions.
12. import replay/conflict/remap is explicit; key collision never proves logical identity.
13. archive/tombstone ≠ purge. Purge is separate destructive work with dependency/recovery checks.
14. Backup/restore/clone/transfer must preserve/remap explicit scope and portable identity according to recovery contracts.
15. performance/storage savings cannot override correctness/security/operability.

## 4. Environment and evidence record

Future execution uses only the accepted P-001 compatibility matrix and records:
- WordPress/PHP/DB family/version;
- storage engine/row format;
- charset/collation;
- object-cache state where relevant;
- single-site/Multisite topology and site count;
- exact D1/D2/D3/D4 profile revision;
- migration/schema version;
- fixture seed/generator version;
- source commit/artifact;
- exact commands;
- query plans/locks/deadlocks/retries;
- latency/query-count/memory/storage metrics;
- wrong-scope/unauthorized row count;
- recovery outcome.

Unauthorized/wrong-scope returned or mutated rows must remain **0**.

## 5. Fixed fixtures — DEF-01…DEF-144

### A. Identity / scope / lifecycle — DEF-01…DEF-08

- **DEF-01** — create site-scoped Definition with stable UUID, type, module and machine key.
- **DEF-02** — create network-scoped Definition with same machine key as site Definition without identity collision.
- **DEF-03** — duplicate machine key within same scope/type races to at most one valid identity.
- **DEF-04** — same machine key across different sites is valid and isolated.
- **DEF-05** — UUID uniqueness is global within Repository contract.
- **DEF-06** — Draft→Published→Disabled→Archived lifecycle preserves history/pointers according to semantics.
- **DEF-07** — archive/tombstone does not silently purge Revision/Dependency history.
- **DEF-08** — hard purge is unavailable without explicit destructive plan/dependency/recovery prerequisites.

### B. Revision immutability / pointers / editor concurrency — DEF-09…DEF-16

- **DEF-09** — initial Revision immutable after commit.
- **DEF-10** — save new Draft Revision advances current pointer only.
- **DEF-11** — publish moves published pointer to validated current Revision without mutating older Revision.
- **DEF-12** — current and published pointers may intentionally diverge while runtime remains pinned to published.
- **DEF-13** — pointer from Definition A to Revision owned by Definition B is rejected/diagnosed.
- **DEF-14** — stale editor save returns conflict; no silent last-write-wins overwrite.
- **DEF-15** — stale publish after another publish returns conflict and preserves committed pointer.
- **DEF-16** — same-request/runtime consumer resolves one immutable published Revision/fingerprint consistently.

### C. Dependency graph / Used-by / publish validation — DEF-17…DEF-24

- **DEF-17** — revision save extracts deterministic outgoing Dependency edges.
- **DEF-18** — published Used-by only reflects dependencies of published source revisions when requested.
- **DEF-19** — draft impact view distinguishes current/draft edges from published edges.
- **DEF-20** — hard missing dependency blocks publish.
- **DEF-21** — optional/soft missing dependency yields explicit degraded semantics without becoming hard blocker.
- **DEF-22** — unresolved imported target UUID remains queryable/remappable without payload full-scan.
- **DEF-23** — target archive/delete race between validation and publish resolves deterministically/safely.
- **DEF-24** — dependency cycle policy is enforced per owning module/type; generic Repository does not invent unsupported cycle semantics.

### D. Definition type registry / schema versions / migrator chain — DEF-25…DEF-32

- **DEF-25** — registered definition type validates current schema payload before save/publish.
- **DEF-26** — unsupported/unregistered definition type loads as explicit unavailable/deferred state, not arbitrary execution.
- **DEF-27** — older supported schema runs deterministic ordered migrator chain in memory.
- **DEF-28** — loading old schema does not rewrite historical Revision in place.
- **DEF-29** — explicit persisted migration creates new Revision with provenance/version.
- **DEF-30** — missing intermediate migrator yields degraded/read-only state.
- **DEF-31** — unknown future schema version fails safe/read-only; no lossy field drop.
- **DEF-32** — migrator replay/idempotency for same input/version is deterministic.

### E. Portable package / import / conflict / replay — DEF-33…DEF-40

- **DEF-33** — package UUID absent locally creates same portable Definition UUID without importing local numeric IDs.
- **DEF-34** — same UUID + same semantic payload resolves no-change/idempotent replay.
- **DEF-35** — same UUID + changed payload creates explicit conflict/update Revision; no silent overwrite.
- **DEF-36** — different UUID + colliding machine key never infers identity automatically.
- **DEF-37** — clone/new-identity import rewrites internal Definition references to new UUID map deterministically.
- **DEF-38** — missing hard dependency blocks activation/import completion while optional dependency can remain explicit unresolved.
- **DEF-39** — unavailable Pro/module object remains inspectable/deferred without faking active functionality.
- **DEF-40** — import provenance/package UUID/checksum/source→target mapping supports safe re-import and rollback analysis.

### F. Runtime compiled cache / module disable / entitlement boundary — DEF-41…DEF-48

- **DEF-41** — published Revision compiles to immutable runtime artifact keyed by Definition UUID + Revision/fingerprint.
- **DEF-42** — Draft save does not invalidate/replace published compiled runtime artifact unnecessarily.
- **DEF-43** — Publish invalidates/supersedes affected compiled cache deterministically.
- **DEF-44** — schema/module migrator change invalidates incompatible compiled artifact.
- **DEF-45** — broken checksum/schema/dependency prevents compiled artifact execution; stale artifact is not trusted blindly.
- **DEF-46** — module disable preserves Definitions/Revisions/Dependencies and removes module runtime behavior per lifecycle contract.
- **DEF-47** — Pro entitlement expiry/lock preserves readable/exportable safe deployed configuration; it does not delete Repository rows.
- **DEF-48** — re-enable/reactivation validates compatibility/health before resuming from preserved published Revision.

### G. Revision retention / pruning / purge safety — DEF-49…DEF-56

- **DEF-49** — retention always protects current Revision.
- **DEF-50** — retention always protects published Revision.
- **DEF-51** — pinned/migration/restore Revision is not pruned.
- **DEF-52** — dependency/rollback references needed by policy prevent unsafe prune.
- **DEF-53** — background prune selects only eligible old unpinned history and remains scope-bound.
- **DEF-54** — prune interrupted mid-run is resumable/idempotent and does not damage pointers.
- **DEF-55** — purge preview reports incoming hard dependencies and recovery/backup requirement.
- **DEF-56** — purge cannot remove another site's/network's Definition through crafted scope/ID input.

### H. D1/D2/D3/D4 physical integrity — DEF-57…DEF-64

- **DEF-57** — D1 textual UUID uniqueness/index behavior.
- **DEF-58** — D2 compact UUID comparison preserves portable identity semantics and migration recoverability.
- **DEF-59** — D3 native JSON comparison preserves canonical payload validation/checksum semantics across supported DBs.
- **DEF-60** — D4 FK/check constraints do not break WordPress upgrade/site-lifecycle/migration compatibility on certified matrix.
- **DEF-61** — payload SHA-256 H1/H2 representation detects corruption without becoming business authorization.
- **DEF-62** — machine key/type/scope normalization and collation do not create hidden identity collisions.
- **DEF-63** — WordPress-derived charset/collation preserves supported Unicode/RTL identifiers/titles/payload.
- **DEF-64** — no candidate profile stores configuration as generic runtime EAV or adds speculative payload-property indexes.

### I. Canonical lookup/list/index workloads — DEF-65…DEF-76

- **DEF-65** — Q1 canonical UUID lookup.
- **DEF-66** — Q2 scope + type + machine-key lookup.
- **DEF-67** — Q3 site/type/lifecycle list with cursor pagination.
- **DEF-68** — Q4 bounded authorized network aggregate list.
- **DEF-69** — Q5 revision history recent/latest and forward traversal.
- **DEF-70** — Q6 compile dependencies without N+1 target lookups.
- **DEF-71** — Q7 reverse Used-by resolved target ID and unresolved UUID forms.
- **DEF-72** — Q8 archive/tombstone maintenance list.
- **DEF-73** — Q9 Site Backup extraction returns exact target-site Definition/Revision/Dependency closure.
- **DEF-74** — Q10 Site delete/retention inventory enumerates only target-site rows.
- **DEF-75** — published-pointer hot read resolves identity + Revision within bounded query plan.
- **DEF-76** — import UUID/key conflict lookup remains indexed at representative scale.

### J. Concurrency / locking / transactional invariants — DEF-77…DEF-88

- **DEF-77** — legacy C1 expected-generation publish success.
- **DEF-78** — legacy C2 stale concurrent publish conflict.
- **DEF-79** — legacy C3 current-save vs publish race preserves pointer semantics.
- **DEF-80** — legacy C4 cross-definition pointer corruption rejected.
- **DEF-81** — legacy C5 dependency target archive/delete race.
- **DEF-82** — legacy C6 duplicate machine-key creation race.
- **DEF-83** — legacy C7 Site Lifecycle deletion vs publish race.
- **DEF-84** — 20+ independent Definition creates do not serialize unrelated sites unnecessarily.
- **DEF-85** — reverse Used-by read during publish sees declared consistent state.
- **DEF-86** — Site Backup extraction during writes follows documented consistency model; no false atomic snapshot claim.
- **DEF-87** — deadlock/timeout retry preserves idempotency/invariants and does not duplicate Revisions/edges.
- **DEF-88** — failed transaction emits no published event/cache invalidation as if commit succeeded.

### K. Physical schema migration / version state — DEF-89…DEF-100

- **DEF-89** — empty/fresh schema create.
- **DEF-90** — no-op desired-schema verification.
- **DEF-91** — additive nullable column migration.
- **DEF-92** — additive workload-justified index migration.
- **DEF-93** — backfill derived/normalized field with checkpoint/re-entry semantics.
- **DEF-94** — bounded identifier/type change only through explicit compatible migration plan.
- **DEF-95** — D1↔D2 UUID representation experiment preserves all portable/dependency references or is rejected.
- **DEF-96** — D1↔D3 payload representation experiment preserves checksums/semantics or is rejected.
- **DEF-97** — D4 constraint add/remove path is compatible/recoverable where claimed.
- **DEF-98** — interrupted migration resumes/reconciles from durable domain migration state.
- **DEF-99** — old code against newer incompatible Repository schema enters safe degraded state rather than corrupting data.
- **DEF-100** — pre/post schema fingerprint and data-integrity audit proves no cross-scope/pointer/dependency damage.

### L. Backup / restore / clone / Site Lifecycle — DEF-101…DEF-112

- **DEF-101** — Site Backup excludes all other site/network-owned rows except explicit dependencies represented by contract.
- **DEF-102** — Network Backup preserves explicit network/site scope coordinates.
- **DEF-103** — Site Restore remaps target site scope without UUID/key collision leakage.
- **DEF-104** — restored current/published pointers remain same-definition valid.
- **DEF-105** — unresolved external/Definition dependency remains repairable after restore.
- **DEF-106** — restore to environment missing owning module yields deferred/read-only state, not payload destruction.
- **DEF-107** — restore with older/newer definition schema uses accepted migrator/degraded behavior.
- **DEF-108** — Site clone creates correct target scope identity while preserving portable logical identity policy.
- **DEF-109** — Site transfer/move does not leave source-scope cache/index leakage.
- **DEF-110** — site uninitialize/delete drains or inventories Repository rows according to Site Lifecycle contract.
- **DEF-111** — 100/1k/10k-site lifecycle/extraction operations are bounded; no accidental all-network full payload scan.
- **DEF-112** — post-restore cache/dependency indexes are invalidated/reconciled before runtime trusts them.

### M. Authorization / Multisite / attack corpus — DEF-113…DEF-124

- **DEF-113** — guessed numeric Definition ID from Site B is inaccessible/mutation-denied from Site A.
- **DEF-114** — guessed UUID from Site B is inaccessible/mutation-denied from Site A.
- **DEF-115** — client-supplied site/network coordinate cannot widen actor authority.
- **DEF-116** — network Definition operation requires network-level authorization; site admin cannot mutate it by ID.
- **DEF-117** — reverse Used-by cannot leak existence/title/type from unauthorized scope.
- **DEF-118** — machine-key/type normalization attack corpus cannot create ambiguous identity.
- **DEF-119** — malicious payload strings cannot alter SQL identifiers/query structure.
- **DEF-120** — oversized identifier/payload is rejected by explicit product/storage bounds.
- **DEF-121** — corrupted JSON/payload checksum fails safe; raw bytes preserved for recovery diagnostics where appropriate.
- **DEF-122** — unauthorized import/export/publish/archive/purge action is denied server-side.
- **DEF-123** — support/diagnostic output does not expose protected payload/secrets merely because Repository stores them.
- **DEF-124** — cross-network/installation identity/caches cannot be replayed into another installation scope.

### N. Scale / query-plan / storage evidence — DEF-125…DEF-136

- **DEF-125** — DF-S 10k Definitions single-site workload.
- **DEF-126** — DF-S 10-site skew variant.
- **DEF-127** — DF-M 100k Definitions / 100-site workload.
- **DEF-128** — DF-L 1M target where infrastructure permits; inability is reported incomplete, not silently certified.
- **DEF-129** — DF-N 1k-site control-plane workload.
- **DEF-130** — synthetic 10k-site control-plane workload where infrastructure permits.
- **DEF-131** — revision-heavy 50/100+ history lookup/prune workload.
- **DEF-132** — dense 50+ dependency fanout + reverse Used-by workload.
- **DEF-133** — cold vs warm/object-cache state recorded separately.
- **DEF-134** — every accepted index maps to a real workload/query and planner evidence.
- **DEF-135** — unused/redundant indexes are rejected despite intuitive appeal.
- **DEF-136** — storage/index/write amplification comparison D1–D4 is secondary to correctness/security/operability.

### O. Failure / recovery / final certification audit — DEF-137…DEF-144

- **DEF-137** — DB connection interruption mid-transaction leaves no half-published logical state.
- **DEF-138** — disk-full/storage failure during migration/save has explicit recoverable state.
- **DEF-139** — missing current Revision pointer is diagnosed; system never silently substitutes arbitrary Revision.
- **DEF-140** — missing published Revision fails safe; current Draft is never silently promoted.
- **DEF-141** — orphan/corrupt Dependency edge/pointer injected in lab is detectable and repair workflow does not cross scope.
- **DEF-142** — Definition event/cache invalidation happens only after durable commit and is replay/reconciliation safe.
- **DEF-143** — independent data-integrity/security review verifies scope, pointer, migration, import and purge invariants before physical-profile certification.
- **DEF-144** — final evidence audit records PASS/FAIL/NOT-APPLICABLE for every applicable fixture and bounds selected DDL/profile support to exact certified environment.

## 6. Legacy protocol mapping preserved

The original ADR-0092 protocol identifiers remain traceable:

- Q1→DEF-65
- Q2→DEF-66
- Q3→DEF-67
- Q4→DEF-68
- Q5→DEF-69
- Q6→DEF-70
- Q7→DEF-71
- Q8→DEF-72
- Q9→DEF-73
- Q10→DEF-74
- C1→DEF-77
- C2→DEF-78
- C3→DEF-79
- C4→DEF-80
- C5→DEF-81
- C6→DEF-82
- C7→DEF-83

`P004-DEFINITION-REPOSITORY-BENCHMARK-PROTOCOL.md` remains supplementary benchmark guidance. This file is the canonical fixed executable-evidence protocol accepted by ADR-0092 and refined by the next ADR.

## 7. Dataset classes

Preserve deterministic fixed-seed classes:
- **DF-S** — 10k Definitions, single-site + 10-site variants;
- **DF-M** — 100k Definitions, 100-site skew;
- **DF-L** — target 1M Definitions where infrastructure permits;
- **DF-N** — 1k/10k-site control-plane/network profile.

Definition types must cover representative small/medium/large payloads across Content, Data/Query, Admin, Membership, Forms/Workflow, Notification/Email, REST/Connections and Backup-style configuration.

## 8. Measurements

For applicable fixtures capture:
- query count;
- selected index/query plan;
- rows examined/estimated;
- filesort/temp-table flags;
- p50/p95/p99 latency after warm-up;
- cold/warm distinction;
- transaction/lock waits/deadlocks/retries;
- PHP memory;
- table/index/storage bytes;
- bytes per Definition/Revision/Dependency;
- write/storage amplification;
- migration duration/temp disk/lock impact;
- cache hit/miss/invalidation behavior where derivative cache is involved.

Do not add benchmark-only indexes absent from candidate profile.

## 9. Stop-the-line failures

P-004 cannot certify if any fixture shows:
- wrong-site/network read or mutation;
- immutable Revision mutation;
- current/published pointer to another Definition;
- stale write/publish silently overwriting committed work;
- machine-key uniqueness ambiguity within declared scope/type;
- unsupported future schema destructively downgraded;
- historical Revision silently rewritten during load/migration;
- import key collision treated as identity without explicit mapping;
- module disable/Pro expiry deleting user configuration;
- unsafe Revision prune/purge removing protected history/dependencies;
- Site Backup/restore/clone scope contamination;
- migration interruption leaving unverifiable/corrupt schema state;
- compiled cache trusted after incompatible publish/schema/restore change;
- event/cache invalidation emitted as success before DB commit;
- performance/storage benefit used to waive correctness/security/recovery failure.

## 10. Selection hierarchy

Correctness/security/integrity → compatibility/operations/recovery → maintainability/diagnostics → performance/storage.

D2/D3/D4 can replace D1 only if evidence proves material benefit worth added migration/compatibility/operational complexity.

## 11. Required future P-004 report

Future authorized execution must produce:
- selected physical profile + exact DDL/types/lengths/collations/indexes;
- supported WP/PHP/DB/environment matrix;
- fixture IDs executed/skipped with reason;
- schema/profile/migrator versions;
- query-plan/index evidence;
- concurrency/locking/retry policy;
- migration/re-entry/recovery strategy;
- import/package/versioning behavior evidence;
- Backup/Site Lifecycle/Multisite evidence;
- cache/event reconciliation evidence;
- security review result;
- known scale/operational limits;
- rejected alternatives and rationale;
- raw evidence artifacts retained under quality policy;
- NOT VERIFIED items.

## 12. Current evidence state

- DEF fixtures documented: **144**
- DEF fixtures executed: **0/144**
- P-004 physical/runtime certifications: **0**
- selected final D1/D2/D3/D4 physical profile: **OPEN / evidence-gated**
- exact DDL/indexes/types/collations: **OPEN / evidence-gated**
- independent P-004 data-integrity/security review executed: **NO**

## 13. Development gate

No fixture generator, Definition table, SQL, EXPLAIN, migration, cache operation, package import, Site Lifecycle mutation, Backup/restore operation, lock/concurrency test or benchmark is authorized before explicit owner consent under ADR-0014.