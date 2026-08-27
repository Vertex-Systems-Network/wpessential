# WPEssential — P-004 Definition Repository Executable Evidence Protocol

Status: **Phase 0 paper evidence contract / execution NOT AUTHORIZED**  
Date: 2026-08-28  
Related: ADR-0073, Definition Repository PT-C DDL & Index Alternatives, ADR-0014.

## Purpose

Define the exact future fixture shapes, operations, measurements and rejection criteria for selecting final Definition Repository physical DDL from D1/D2/D3/D4.

This document contains no SQL, migration or benchmark execution.

## Profiles under comparison

- **D1** maintainability baseline: textual UUID, explicit scope coordinates, bounded identifier keys, text payload, minimal indexes, application integrity.
- **D2** compact identity: D1 semantics with binary UUID representation.
- **D3** native JSON: D1 identity with native JSON payload where supported.
- **D4** constraint-enhanced: D1/D2 plus validated foreign-key/check-constraint profile where compatible.

Variants may differ in payload-hash representation H1/H2 only in isolated subtests; do not combine unrelated optimizations and attribute the result incorrectly.

## Engine matrix

Future execution uses the accepted P-001 database matrix only.

Each result records:
- WordPress version;
- PHP version;
- DB family/version;
- storage engine;
- charset/collation;
- row/page format where observable;
- object-cache state where relevant;
- single-site vs Multisite topology.

No profile can be accepted on an unrecorded/unsupported DB environment.

## Deterministic fixture generator contract

Fixtures are generated from a fixed seed and documented distribution so D1–D4 compare identical logical data.

Definition types include representative:
- CPT/Taxonomy/Field/Relation/Query/Table;
- Settings/Dashboard/Listing;
- Membership/Form/Workflow/Notification/Email;
- REST/Connection/Backup-style definitions where schema size differs.

Payload sizes:
- small ~1–4 KiB canonical document;
- medium ~8–32 KiB;
- large bounded stress documents ~64–256 KiB where product limits permit.

Dependency distribution:
- zero dependency;
- typical 2–10 dependencies;
- dense 50+ dependency stress case;
- unresolved import target subset;
- mixed required/optional edges.

Revision distribution:
- mostly 1–5 revisions;
- selected definitions with 50/100+ history for stress.

## Dataset classes

### DF-S
- 10k Definitions;
- single site and 10-site variants.

### DF-M
- 100k Definitions;
- 100-site skewed distribution.

### DF-L
- target 1M Definitions when environment capacity permits;
- 1k-site topology.

### DF-N
- synthetic 10k-site network metadata/operations profile;
- mostly small sites plus one noisy/high-definition site.

If environment cannot execute DF-L/DF-N fully, result is incomplete rather than silently downscaled and certified.

## Required query/operation cases

### Q1 — canonical UUID lookup
Expected result exactly one Definition or not-found within authorized scope rules.

### Q2 — scoped machine-key lookup
`scope + definition_type + machine_key` uniqueness/lookup.

### Q3 — site/type/state list
Cursor pagination; no deep OFFSET benchmark as primary large-list strategy.

### Q4 — network aggregate list
Explicit network authorization with bounded filters/pagination.

### Q5 — revision history
Latest-first and forward traversal for one Definition.

### Q6 — compile dependencies
Load source revision + outgoing dependencies without N+1 target lookup pattern.

### Q7 — reverse Used-by
Resolved target ID and unresolved target UUID variants; current/published/historical distinction preserved.

### Q8 — archive/tombstone list
Lifecycle filtering and pagination.

### Q9 — Site Backup extraction
Target-site Definitions + retained revisions + dependency edges only.

### Q10 — site delete/retention scan
Enumerate exactly target-site owned rows without other-site contamination.

## Publish transaction/concurrency fixtures

### C1 — expected-generation publish
One writer updates published pointer/generation successfully.

### C2 — stale publish
Two editors start from same generation; first commits; second must receive conflict and must not overwrite.

### C3 — current save vs publish race
Draft/current update and published-pointer mutation interleave; resulting pointers must each reference same Definition and satisfy declared semantics.

### C4 — same-definition pointer integrity
Attempt to point Definition A to Revision B from Definition B must fail at DB/application invariant layer selected by profile.

### C5 — dependency validation race
Required target becomes archived/deleted between validation and publish; accepted transaction/locking profile must produce deterministic safe result.

### C6 — duplicate machine-key race
Concurrent creation under same scope/type/key must produce at most one valid identity.

### C7 — site deletion vs publish
Lifecycle drain/lock ordering prevents publishing into a scope being destructively removed according to Site Lifecycle contract.

## Locking observations

Record for each C-case:
- transaction isolation/default DB mode;
- rows/index ranges locked where observable;
- waits/deadlocks/timeouts;
- retry count;
- final invariant validation;
- effect on unrelated Definition/site writes.

No profile is accepted merely because deadlocks are rare; retry semantics and invariant preservation must be explicit.

## Index/query-plan evidence

For Q1–Q10 capture where supported:
- selected index;
- rows examined/estimated;
- filesort/temp-table flags;
- key length;
- scan type;
- query count;
- p50/p95/p99 latency after warm-up;
- cold/warm distinction.

Reject unexplained full-table scans on required hot lookups at representative scale.

Do not create ad-hoc benchmark-only indexes that are absent from the candidate profile.

## Storage evidence

Measure after each dataset:
- table data bytes;
- total index bytes;
- bytes/Definition;
- bytes/revision;
- bytes/dependency;
- index amplification;
- D1 vs D2 UUID effect;
- D1 vs D3 payload effect.

Storage savings alone cannot override operational/debug/compatibility cost.

## Migration evidence

Future P-004 migration fixtures:
- empty create;
- no-op desired schema check;
- additive nullable column/index;
- bounded key-length change where safe;
- D1↔D2 identity representation migration candidate only if a real upgrade path is proposed;
- D1↔D3 payload storage candidate only if a real upgrade path is proposed;
- D4 constraint add/remove;
- interrupted migration and re-entry;
- pre/post schema fingerprint verification.

No destructive profile transition is certified without recovery proof.

## Backup/Restore/Multisite evidence

Required:
- Site Backup excludes other sites;
- Site Restore remaps target site scope according to restore contract;
- Network Backup preserves all scope coordinates;
- restored current/published pointers remain same-definition valid;
- unresolved dependency remap remains repairable;
- 100/1k/10k-site extraction/provision/lifecycle operations are bounded.

## Security/attack fixtures

- wrong-site Definition numeric ID lookup;
- guessed UUID from another site;
- machine-key collision across sites;
- crafted definition type/key normalization collision;
- reverse-dependency leakage across scope;
- site delete with wrong supplied site ID;
- payload content does not alter identifier/SQL structure.

Unauthorized/wrong-scope returned or mutated rows required: **0**.

## Acceptance hierarchy

Correctness/security gates first, then compatibility/operations, then performance/storage.

A profile is rejected regardless of speed if it fails:
- same-definition pointer integrity;
- scope isolation;
- machine-key uniqueness semantics;
- immutable revision invariant;
- stale-write conflict semantics;
- Site Backup scope correctness;
- migration recoverability for supported upgrade path.

## Decision output after future execution

P-004 result must produce:
- selected profile + exact DDL/types/lengths/collations/indexes;
- supported engine matrix;
- query-plan evidence summary;
- concurrency/locking policy;
- migration strategy;
- known scale limits;
- rejected alternatives and reason;
- raw fixture/result artifacts retained according to quality policy.

Executed P-004 cases: **0**.

## Development gate

No fixture generator, DB table, SQL, EXPLAIN, migration, lock test or benchmark is authorized before explicit owner consent under ADR-0014.