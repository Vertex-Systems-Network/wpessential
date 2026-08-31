# WPEssential — P-010 Relations Executable Evidence Protocol

Status: **Refined fixed evidence contract / execution NOT AUTHORIZED**  
Date: 2026-08-28  
Work package: `P0-M00-WP16`  
Related: ADR-0074, ADR-0093, Relations PT-D/PT-E Physical Benchmark Profile, Relation Runtime Storage Alternatives, Query ADR-0086/0131, Definition ADR-0132, Site Lifecycle, Backup/Restore, Import/Export, ADR-0014.

## 1. Purpose

Define one canonical, fixed, adversarial P-010 evidence contract for selecting and certifying Relations runtime storage without allowing benchmark speed to weaken cardinality, direction, scope isolation, authorization, lifecycle, import/restore or recovery semantics.

This is an in-place refinement of the canonical protocol accepted by ADR-0093. `P010-RELATIONS-PHYSICAL-BENCHMARK-PROTOCOL.md` remains supplementary benchmark guidance; it is not a second certification authority.

No Relation table, SQL, fixture graph, mutation, Query execution, cache operation, migration, Backup/Restore or benchmark is authorized by this document.

## 2. Profiles under comparison

- **R1 — PT-D shared scoped universal edge table** — first benchmark baseline.
- **R2 — PT-E per-site universal edge table** — mandatory comparison.
- **R3 — per-relation physical table** — exceptional high-scale profile only when justified.
- **R4 — native/meta relation adapter** — interoperability/control baseline only where equivalent semantics exist.

Endpoint representation subprofiles:
- E1 normalized typed endpoint columns;
- E2 bounded canonical textual endpoint identity;
- E3 hybrid local materialized key + portable identity metadata.

Pivot subprofiles:
- PV1 compact versioned payload + normalized proven hot columns;
- PV2 generic typed pivot-value table;
- PV3 relation-specific native columns only with R3 evidence.

Isolate variables. Do not bundle several physical changes into one benchmark and attribute the result ambiguously.

## 3. Non-negotiable truth boundaries

1. Relation Definition/Revision ≠ runtime edge/link ≠ pivot metadata ≠ derived cache/history.
2. Definition Repository owns relation configuration; runtime Relations storage owns actual edges.
3. Cardinality is enforced below the UI under concurrency.
4. Duplicate pair prevention is deterministic and concurrency-safe.
5. Directional and reciprocal/symmetric semantics are explicit and cannot be inferred from endpoint order alone.
6. Endpoint identity always includes registered Data Source/type/scope semantics; raw integer IDs alone are never universally meaningful.
7. Site/network/cross-site scope is explicit product/security truth independent of R1/R2 physical layout.
8. Cross-site relations are **Off by default** and cannot appear merely because a scope predicate is omitted.
9. Attach/detach/pivot/reorder permissions are distinct from ability to edit an endpoint entity.
10. Relation list/count/existence must not leak inaccessible endpoint existence.
11. Pivot fields are typed/versioned; “stored” does not imply efficiently queryable/indexed.
12. Relation Definition changes that invalidate existing edges require an explicit migration/conflict plan before Publish.
13. Endpoint deletion cannot generic-cascade arbitrary third-party resources.
14. Missing/deactivated endpoint provider degrades the Relation; it does not purge links automatically.
15. Cache is derivative; a revoke/detach/definition change cannot preserve stale protected visibility.
16. Normal Query/List traversal cannot degrade into unbounded N+1.
17. Portable import/export remaps source-specific stable identities; local numeric IDs are never assumed portable.
18. Audit/events/cache invalidation follow durable edge commit, never precede it.
19. R1, R2 and any R3 profile must export/restore the same logical relation graph for equivalent supported semantics.
20. Performance/storage cannot override integrity, authorization, scope or recovery.

## 4. Evidence discipline

Every future fixture records at minimum:
- fixture ID;
- Relation Definition UUID + immutable revision/generation;
- physical R/E/PV profile revision;
- WordPress/PHP/DB environment;
- site/network scope;
- actor/principal class;
- endpoint source/type/identity classes;
- exact operation/query;
- transaction/lock/retry strategy where applicable;
- query plan/index/rows examined where available;
- DB/query count;
- p50/p95/p99 or operation duration as applicable;
- memory/storage/index metrics as applicable;
- cache generation/hit/invalidation evidence when applicable;
- cardinality/duplicate/orphan invariant result;
- wrong-scope/unauthorized row/count/existence leakage count;
- outcome/failure category;
- evidence artifact/reference.

Wrong-scope/unauthorized returned or mutated rows/count leakage must remain **0**.

## 5. Deterministic graph classes

Preserve fixed-seed graph classes:

- **RF-S** — 100k edges; 10 sites; mostly low-degree endpoints.
- **RF-M** — 1M edges; 100 sites; mixed degree distribution.
- **RF-L** — 10M edges when environment permits; 1k sites; one noisy/high-volume site.
- **RF-N** — synthetic 10k-site operational topology; lifecycle/Backup/migration/health focus.
- **RF-H** — 10k-related and 100k+-related high-degree endpoints where environment permits.

Endpoint classes include posts/CPTs, media, users, terms, comments where accepted, Custom Table rows, WPE runtime entities and registered external sources. Include numeric-ID collisions across sites and mixed typed identities.

Relation classes include one-to-one, one-to-many, many-to-one, many-to-many, directed self, reciprocal/symmetric self, ordered, independently ordered both sides, pivot/no-pivot and bounded custom max/min variants.

If the environment cannot execute a declared class, result is incomplete rather than silently downscaled and certified.

## 6. Fixed fixtures — REL-01…REL-160

### A. Definition identity / publish / runtime pinning — REL-01…REL-08

- **REL-01** — create Draft Relation Definition with stable UUID/key and typed From/To endpoints.
- **REL-02** — publish immutable Relation revision; runtime mutation pins/validates the published revision/generation.
- **REL-03** — edit Draft after publish; existing runtime semantics remain on published revision until explicit Publish.
- **REL-04** — disable Relation Definition blocks new mutations according to contract while preserving links for recovery/export.
- **REL-05** — archive Definition preserves runtime links/history; purge remains separate destructive cleanup.
- **REL-06** — missing/unpublished Relation Definition makes link mutation fail safe; stale cached compiled definition is not trusted.
- **REL-07** — unknown future Definition schema or missing migrator yields degraded/read-only behavior rather than destructive downgrade.
- **REL-08** — Pro/module disable preserves Definition + existing links; no automatic data purge.

### B. Endpoint identity / Data Source contracts — REL-09…REL-16

- **REL-09** — WordPress post endpoint identity is site-scoped and typed.
- **REL-10** — same numeric post ID on different sites remains distinct.
- **REL-11** — network-shared user identity plus site-owned relation scope remains unambiguous.
- **REL-12** — term/media/comment identities retain source/type semantics.
- **REL-13** — Custom Table endpoint validates declared primary-key type.
- **REL-14** — polymorphic endpoint rejects type/source confusion and arbitrary references.
- **REL-15** — missing/deactivated endpoint provider yields Degraded relation and retains links without exposing stale private data.
- **REL-16** — reactivated provider revalidates schema/identity before mutations resume.

### C. Direction / self / reciprocal semantics — REL-17…REL-24

- **REL-17** — directed From→To edge preserves orientation in forward and reverse traversal.
- **REL-18** — many-to-one UI normalization does not invert persisted semantic ownership unexpectedly.
- **REL-19** — reciprocal/symmetric A↔B normalizes pair identity so reverse duplicate cannot exist.
- **REL-20** — non-reciprocal self relation preserves A→B separately from B→A when definition requires direction.
- **REL-21** — symmetric relation with directional pivot schema is rejected or requires explicit non-reciprocal semantics.
- **REL-22** — endpoint labels/presentation do not alter canonical edge identity.
- **REL-23** — reciprocal exact-pair existence works identically regardless caller endpoint order.
- **REL-24** — direction/symmetry Definition change with existing links requires migration/conflict analysis before Publish.

### D. Cardinality / min-max / eligibility — REL-25…REL-32

- **REL-25** — one-to-one enforces maximum 1 on both sides below UI.
- **REL-26** — one-to-many enforces unique child/To-side parent where defined.
- **REL-27** — many-to-one normalized semantics enforce the intended constrained side.
- **REL-28** — many-to-many permits multiple distinct pairs but never exact duplicates.
- **REL-29** — custom max links per side is enforced transactionally under concurrency.
- **REL-30** — minimum-link rule is treated as declared business validation, not falsely represented as always DB-enforced.
- **REL-31** — Query/Condition eligibility is revalidated server-side during mutation; stale UI eligibility cannot authorize attach.
- **REL-32** — cardinality/max tightening with violating existing links blocks Publish until explicit conflict-resolution/migration plan exists.

### E. Attach / detach / replace / bulk idempotency — REL-33…REL-40

- **REL-33** — single attach validates Relation + both endpoints + Policy + cardinality before commit.
- **REL-34** — duplicate attach follows explicit idempotent-success/conflict mode without duplicate row.
- **REL-35** — duplicate attach with different pivot data follows explicit update-pivot/conflict/ignore mode; never silent overwrite.
- **REL-36** — bulk attach deduplicates input and enforces per-edge/cardinality Policy.
- **REL-37** — detach removes exactly intended edge and invalidates generation after commit.
- **REL-38** — bulk detach is bounded/idempotent and cannot cross scope through crafted IDs.
- **REL-39** — replace-set produces a diff, requires appropriate destructive authorization and preserves omitted/added semantics deterministically.
- **REL-40** — externally exposed bulk mutation idempotency key prevents duplicate replay while preserving genuine new requests.

### F. Pivot schema / values / evolution — REL-41…REL-48

- **REL-41** — typed pivot scalar validates through Field Schema contract.
- **REL-42** — invalid pivot value causes no partial edge/pivot success.
- **REL-43** — pivot field stable UUID/version survives Definition revisions.
- **REL-44** — queryable/sortable pivot field is advertised only when physical projection/index evidence exists.
- **REL-45** — non-queryable compact payload retrieval does not pretend efficient filtering.
- **REL-46** — pivot field add with compatible default/nullable semantics migrates safely.
- **REL-47** — pivot field remove/type change with existing values requires explicit migration/data-loss plan.
- **REL-48** — pivot schema mismatch after restore/import/provider downgrade yields degraded/conflict state, not guessed coercion.

### G. Ordering / reorder integrity — REL-49…REL-56

- **REL-49** — unordered relation does not incur/claim manual-order semantics.
- **REL-50** — ordered From side returns deterministic stable order.
- **REL-51** — ordered To side returns deterministic stable order.
- **REL-52** — independently ordered both sides preserves separate sequences.
- **REL-53** — append/prepend modes produce deterministic positions.
- **REL-54** — concurrent reorder resolves collisions deterministically and does not duplicate/drop links.
- **REL-55** — high-degree reorder avoids unbounded sibling rewrites or documents hard supported limit.
- **REL-56** — display-time sort by target field never silently rewrites stored manual order.

### H. Authorization / privacy / existence and count leakage — REL-57…REL-64

- **REL-57** — actor with edit permission on From endpoint but no relation.attach permission is denied.
- **REL-58** — actor cannot attach inaccessible/unauthorized target merely by knowing its ID.
- **REL-59** — view/list omits or denies inaccessible related target according to Definition policy.
- **REL-60** — exact existence does not reveal protected target existence to unauthorized caller.
- **REL-61** — relation count includes only rows actor may know exist, or denies count according to policy.
- **REL-62** — pivot protected field authorization is independent from edge visibility where configured.
- **REL-63** — bulk link management/repair/export requires distinct server-side capability.
- **REL-64** — diagnostics/admin browse does not expose private endpoint/pivot data beyond actor Policy.

### I. Original RQ1–RQ11 read workloads — REL-65…REL-75

- **REL-65** — RQ1 forward endpoint lookup.
- **REL-66** — RQ2 reverse endpoint lookup.
- **REL-67** — RQ3 exact pair existence.
- **REL-68** — RQ4 Policy-aware count/existence.
- **REL-69** — RQ5 ordered related list with stable pagination/tie behavior.
- **REL-70** — RQ6 pivot filter/order only on certified queryable fields.
- **REL-71** — RQ7 one/two-depth nested Relation Query through Query Service with bounded query count.
- **REL-72** — RQ8 endpoint orphan/cleanup lookup.
- **REL-73** — RQ9 Site Backup extraction of exact target-site logical relation graph.
- **REL-74** — RQ10 authorized bounded network diagnostics aggregate without site-by-site N+1.
- **REL-75** — RQ11 bounded cross-site relation query only under explicitly certified advanced mode; otherwise expected rejection.

### J. Additional Query / pagination / N+1 behavior — REL-76…REL-80

- **REL-76** — Query QP3 relation prefilter→provider query preserves Policy, ordering and bounds.
- **REL-77** — provider query→batched relation hydration avoids per-row N+1.
- **REL-78** — high-degree endpoint pagination uses stable deterministic tie-breaker/cursor semantics through Query contract.
- **REL-79** — aggregate over pivot/edge set cannot leak protected endpoint cohorts.
- **REL-80** — any exceptional post-processing fallback declares hard row cap and fails rather than silently becoming unbounded.

### K. Original RC1–RC8 concurrency races — REL-81…REL-88

- **REL-81** — RC1 duplicate many-to-many concurrent attach → one logical edge.
- **REL-82** — RC2 competing one-to-one attach → at most one valid winner.
- **REL-83** — RC3 concurrent one-to-many child reassignment → never two active parents where forbidden.
- **REL-84** — RC4 detach vs pivot update → detached edge cannot resurrect through stale update.
- **REL-85** — RC5 reorder vs detach → no ghost link/order corruption.
- **REL-86** — RC6 endpoint delete vs attach → no commit to endpoint already durably unavailable.
- **REL-87** — RC7 Relation Definition generation change vs attach → stale semantics cannot write incompatible edge/pivot/cardinality state.
- **REL-88** — RC8 site deletion vs mutation → no new edge commits after lifecycle drain boundary.

### L. Transaction / deadlock / event ordering — REL-89…REL-96

- **REL-89** — deadlock/timeout retry is bounded and preserves idempotency/cardinality.
- **REL-90** — transaction failure leaves no half edge/pivot/generation state.
- **REL-91** — relation generation increments only for durably committed logical mutation.
- **REL-92** — audit/domain event emits only after commit; failed transaction emits no success event.
- **REL-93** — cache invalidation/reconciliation is safe if post-commit event delivery is delayed/duplicated.
- **REL-94** — unrelated relations/sites are not globally serialized by one hot relation lock unless profile explicitly requires/bounds it.
- **REL-95** — 20+ concurrent independent attaches record contention without invariant weakening.
- **REL-96** — process/connection interruption around commit resolves by durable state reconciliation, not blind duplicate mutation.

### M. Endpoint deletion / restrict / orphan / repair — REL-97…REL-104

- **REL-97** — detach-on-endpoint-delete removes only edges, never generic-cascades connected entities.
- **REL-98** — restrict-delete blocks endpoint deletion while protected links exist when WPE owns/coordinates the action path.
- **REL-99** — custom delete policy requires registered trusted handler and explicit capability; arbitrary callback is forbidden.
- **REL-100** — third-party/core deletion outside WPE creates detectable orphan state without exposing deleted private data.
- **REL-101** — orphan scan is bounded/indexed and scope-safe.
- **REL-102** — orphan purge previews impact, is auditable and cannot delete wrong-site edges.
- **REL-103** — endpoint remap repair requires typed source identity and conflict/cardinality revalidation.
- **REL-104** — relation Definition purge cannot remove endpoint entities; only scoped relation runtime data per destructive plan.

### N. Definition revision changes with existing edges — REL-105…REL-112

- **REL-105** — endpoint source/type change with existing links is migration-class and ordinary Publish is blocked.
- **REL-106** — cardinality tightening evaluates all violating links before Publish.
- **REL-107** — reciprocal/direction change computes duplicate/inversion conflicts before Publish.
- **REL-108** — ordering-mode change preserves or explicitly migrates order data.
- **REL-109** — pivot schema change uses versioned migration and retains recoverable provenance.
- **REL-110** — delete-policy change affects future lifecycle behavior without retroactive unauthorized deletion.
- **REL-111** — permission-policy change invalidates protected relation caches immediately according to authorization generation.
- **REL-112** — Definition rollback to older revision refuses incompatible runtime-edge interpretation unless an explicit reverse migration/reconciliation exists.

### O. Cache / revocation / stale-state prevention — REL-113…REL-120

- **REL-113** — attach invalidates forward/reverse/count/existence caches for affected endpoints.
- **REL-114** — detach invalidates the same without stale edge visibility.
- **REL-115** — pivot update/reorder invalidates dependent filtered/sorted caches.
- **REL-116** — Relation Definition publish invalidates compiled relation/query cache by revision/generation.
- **REL-117** — endpoint visibility/role/Membership revoke cannot leave stale protected relation result/count.
- **REL-118** — Site A cache key/result cannot satisfy Site B even with identical numeric endpoint IDs.
- **REL-119** — site deletion makes scoped cache entries unreachable/invalid.
- **REL-120** — if authorization/invalidation dependency cannot be represented safely, persistent shared cache is disabled.

### P. Import / export / replay / clone / restore — REL-121…REL-128

- **REL-121** — runtime edge export uses portable source-specific identity mapping, not local numeric IDs.
- **REL-122** — import remaps endpoints + relation Definition UUID and detects missing mappings explicitly.
- **REL-123** — import duplicate pair is idempotent/conflict according declared mode; never duplicates silently.
- **REL-124** — import cardinality conflict is surfaced in Dry Run/Plan and cannot partially violate target graph.
- **REL-125** — import pivot type/version conflict is validated before commit.
- **REL-126** — package/import replay with same batch/idempotency identity does not duplicate edges.
- **REL-127** — site clone maps target site/entity identities without retaining source-site cache/scope references.
- **REL-128** — restore/import preserves order where target profile supports it and reports any unsupported/lossy condition before write.

### Q. Multisite / Site Lifecycle / transfer — REL-129…REL-136

- **REL-129** — default site relation cannot cross to another site by forged site/endpoint tuple.
- **REL-130** — network-scoped Relation requires network definition + network authorization.
- **REL-131** — advanced cross-site Relation requires both endpoint site coordinates and Policy on both resources.
- **REL-132** — R1 shared cleanup/extraction always includes trusted explicit scope; one-site lifecycle never drops shared table.
- **REL-133** — R2 correct site table context survives nested switching/restoration and detects wrong prefix/table context.
- **REL-134** — R2 missing/outdated per-site schema enters explicit degraded/migration-required state.
- **REL-135** — site transfer/remap is reviewed/typed and cannot bulk-substitute IDs across unrelated sites.
- **REL-136** — 100/1k/10k-site provisioning/migration/health/cleanup is bounded and reports noisy-neighbor/table-proliferation costs truthfully.

### R. Physical R/E/PV profile / migration evidence — REL-137…REL-144

- **REL-137** — R1 scope+relation+From index plan stays bounded at RF-S/RF-M and records RF-L behavior.
- **REL-138** — R1 reverse/exact-pair/cleanup indexes stay bounded under hot-site/high-degree skew.
- **REL-139** — R2 per-site table provisioning/index behavior stays operable across supported site-count profile.
- **REL-140** — E1/E2/E3 comparison preserves endpoint type/scope identity and import diagnostics.
- **REL-141** — PV1/PV2 comparison proves queryable pivot plans without arbitrary-payload indexing claims.
- **REL-142** — R3 is benchmarked only for a concrete exceptional relation and includes DDL lifecycle/table-count/migration/Backup costs.
- **REL-143** — schema/profile migration interruption resumes/reconciles without duplicate/lost/cross-scope edges.
- **REL-144** — old code/profile against newer incompatible relation schema fails/degrades safely instead of corrupting graph.

### S. Scale / high-degree / operational evidence — REL-145…REL-152

- **REL-145** — RF-S 100k edge workload.
- **REL-146** — RF-M 1M edge workload.
- **REL-147** — RF-L 10M edge target where infrastructure permits; inability remains NOT VERIFIED.
- **REL-148** — RF-H 10k-related high-degree endpoint lookup/count/order workload.
- **REL-149** — RF-H 100k+-related endpoint where environment permits; hard limits recorded truthfully.
- **REL-150** — bulk attach/detach 100/1k/10k throughput + memory + lock behavior.
- **REL-151** — one noisy site/relation does not make unrelated site operations unbounded for certified profile.
- **REL-152** — storage/index/write amplification compares R1/R2 and justified R3 only after correctness/security gates pass.

### T. Security / failure / final certification — REL-153…REL-160

- **REL-153** — wrong-site edge ID / forged scope / wrong blog-context attack corpus returns/mutates zero unauthorized rows.
- **REL-154** — malformed endpoint/reference/relation Definition mismatch cannot alter identifier/SQL/query structure.
- **REL-155** — source adapter unavailable/endpoint disappears after validation resolves as structured failure/reconciliation state.
- **REL-156** — Backup/Restore of R1 vs R2 yields the same logical portable relation graph for certified semantics.
- **REL-157** — post-restore relation generations/caches/orphan indexes are reconciled before trusted runtime use.
- **REL-158** — Site Backup/restore/delete/transfer cannot affect unrelated site relation data.
- **REL-159** — independent security/data-integrity review attacks scope, Policy, cardinality, import, cache, delete and migration boundaries.
- **REL-160** — final evidence audit records PASS/FAIL/NOT-APPLICABLE for each applicable fixture and bounds every R/E/PV support claim to the exact certified environment/profile.

## 7. Legacy ADR-0093 workload mapping preserved

Original read workloads:
- RQ1→REL-65
- RQ2→REL-66
- RQ3→REL-67
- RQ4→REL-68
- RQ5→REL-69
- RQ6→REL-70
- RQ7→REL-71
- RQ8→REL-72
- RQ9→REL-73
- RQ10→REL-74
- RQ11→REL-75

Original concurrency cases:
- RC1→REL-81
- RC2→REL-82
- RC3→REL-83
- RC4→REL-84
- RC5→REL-85
- RC6→REL-86
- RC7→REL-87
- RC8→REL-88

## 8. Required measurements

For applicable fixtures capture:
- selected index/query plan;
- rows examined/estimated;
- key width/selectivity;
- sort/temp-table behavior;
- DB/query count;
- p50/p95/p99 read/mutation latency;
- lock waits/deadlocks/retries;
- throughput for attach/detach/bulk;
- memory;
- table/index/storage footprint;
- cleanup/Backup extraction time;
- R2 provisioning/migration time;
- network health/admin cost;
- cache hit/miss/invalidation/revoke behavior.

Do not create benchmark-only indexes absent from the candidate profile.

## 9. Stop-the-line failures

P-010 cannot certify if any applicable fixture shows:
- wrong-site/network or unauthorized edge/pivot/list/count/existence visibility/mutation;
- duplicate/cardinality violation under declared concurrency;
- symmetric reverse duplicate or directional semantic corruption;
- stale Definition generation writes incompatible edge/pivot state;
- Definition cardinality/pivot/source change publishes over incompatible existing links without explicit migration plan;
- generic relation delete cascades arbitrary third-party entities;
- missing provider triggers automatic link purge;
- detach/update/reorder race resurrects ghost edge;
- normal traversal becomes unbounded N+1;
- protected cache/count survives authorization revoke/detach/site delete;
- import/clone/restore guesses endpoint identity or silently violates cardinality;
- R1/R2 equivalent export/restore produces different logical graph without explicit unsupported-semantics declaration;
- event/audit/cache success precedes durable transaction commit;
- migration interruption leaves unverifiable/ambiguous graph state;
- performance/storage advantage is used to waive security/integrity/recovery failure.

## 10. Selection hierarchy

1. scope/security/authorization correctness;
2. cardinality/direction/data integrity;
3. crash/retry/lifecycle/import/Backup recoverability;
4. Query integration/N+1/cache correctness;
5. compatibility/operations/maintainability;
6. performance/storage.

R2 can replace R1 default only with material total-system benefit. R3 can certify only for a bounded exceptional use case and cannot become default from one synthetic speed result.

## 11. Required future P-010 report

Future authorized execution must produce:
- selected R profile + E/PV representations;
- exact DDL/types/indexes;
- supported WP/PHP/DB/Multisite environment matrix;
- fixture IDs executed/skipped with reason;
- cardinality/locking/retry strategy;
- Relation Definition migration/change-impact policy;
- Query QP3 compilation/batch strategy;
- cache-generation/revocation contract;
- endpoint lifecycle/orphan/delete policy evidence;
- import/clone/Backup/Restore/Site Lifecycle evidence;
- scale/high-degree limits;
- independent security/data-integrity review;
- rejected alternatives and reasons;
- raw evidence artifacts;
- NOT VERIFIED items.

## 12. Current evidence state

- REL fixtures documented: **160**
- REL fixtures executed: **0/160**
- P-010 physical/runtime certifications: **0**
- selected final R1/R2/R3 physical profile: **OPEN / evidence-gated**
- selected E1/E2/E3 endpoint representation: **OPEN / evidence-gated**
- selected PV1/PV2/PV3 pivot representation: **OPEN / evidence-gated**
- exact DDL/types/indexes: **OPEN / evidence-gated**
- independent P-010 security/data-integrity review executed: **NO**

## 13. Development gate

No Relation table, DDL, SQL, fixture graph, migration, lock/concurrency test, Query execution, cache operation, Import/Export mutation, Backup/Restore, Site Lifecycle mutation or benchmark may run before explicit owner consent under ADR-0014.