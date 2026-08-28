# ADR-0134 — Field Storage / Custom Fields Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP17`

## Context

WPEssential Custom Fields use a plural storage architecture. Field Definition, editor control, canonical runtime value, storage adapter and presentation are separate concerns. ADR-0087 already accepts the routing families:

- FS1 — native WordPress object/meta/options storage for natural WordPress-owned values;
- FS2 — WPE typed Custom Table columns for scale, strong schema/index/query/constraint requirements;
- FS3 — first-class child rows for genuinely queryable structured/repeater data;
- FS4 — Relations Engine for relationship/cardinality/reverse/pivot semantics;
- FS5 — Vault references for persisted secrets;
- FS6 — rebuildable derived/search/materialized projections.

The existing architecture and benchmark profile did not provide one fixed adversarial executable-evidence contract covering runtime type fidelity, null/default semantics, adapter capability truth, uniqueness races, revisions, structured values, Relations/Vault boundaries, REST/privacy exposure, migration cutover/recovery, import/export, Multisite, cache invalidation and scale.

## Decision

Accept `docs/QUALITY/FIELD-STORAGE-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the fixed future Field Storage / Custom Fields evidence contract.

It defines **FST-01…FST-176** covering:

- Field Definition/revision and adapter capability contracts;
- null/missing/empty/default/inherited value semantics;
- server-side validation, normalization and malicious-input handling;
- scalar, numeric, date/time, JSON and multi-value type fidelity;
- dynamic/manual/remote option-source boundaries;
- FS1 post/user/term/comment/meta/options behavior and truthful native capabilities;
- FS2 typed Custom Table mapping, indexes, concurrency and privacy responsibilities;
- FS3 structured/repeater child identity, ordering and queryability;
- FS4 typed references and Relations routing boundaries;
- FS5 Vault-only secret persistence/reveal/export/cache rules;
- FS6 computed/materialized/search projection rebuildability and invalidation;
- Q0–Q4 queryability evidence and downgrade behavior;
- hard uniqueness/concurrency/write atomicity;
- runtime value history vs Definition revision semantics;
- REST, Abilities, Quick Edit/Admin Columns, privacy/export/erase exposure;
- type and storage-adapter migration planning, crash/resume, cutover and rollback;
- import/export/package portability and idempotency;
- Multisite/site/network/clone/transfer/restore isolation;
- cache/invalidation/revocation safety;
- 10k/100k/1M workloads and independent security/data-integrity/privacy review.

## Preserved invariants

1. Field Definition ≠ runtime value ≠ editor control ≠ presentation format.
2. Publishing a Field Definition does not mean a runtime value migration completed.
3. No adapter may claim a higher Q0–Q4 queryability class than its physical/index evidence proves.
4. Missing, null, empty and default/inherited values are not collapsed silently.
5. Required validation and authorization are server-side.
6. Hard concurrent uniqueness requires a proven transactional/DB guarantee; UI validation alone is insufficient.
7. Many-to-many/reverse/pivot/cardinality semantics remain owned by Relations, not generic serialized field values.
8. Persisted secrets are Vault references only; generic Field Storage never stores plaintext secret values.
9. Derived/search/materialized projections remain rebuildable and are not source-of-truth replacements.
10. Storage does not automatically grant REST, Ability, export, log or cache visibility.
11. Runtime type/storage changes use an explicit migration plan with fidelity, checkpoint, verification and recovery semantics.
12. Native WordPress ownership/scope semantics remain intact; a network-scoped Field Definition does not silently globalize site runtime values.
13. Import/export operates on logical schemas and canonical typed values, not raw physical DB representations.
14. Performance cannot waive security, fidelity, portability, privacy or recovery requirements.

## Evidence state

- FST fixtures documented: **176**
- FST fixtures executed: **0/176**
- Field Storage runtime certifications: **0**
- FS1 certified adapter/profiles: **0**
- FS2 certified adapter/profiles: **0**
- FS3 certified adapter/profiles: **0**
- FS4 remains under Relations P-010 certification: **0**
- FS5 remains under Vault P-005 certification: **0**
- FS6 certified projection profiles: **0**
- final routing thresholds: **OPEN / evidence-gated**
- exact custom storage/index profiles: **OPEN / evidence-gated**
- independent Field Storage security/data-integrity/privacy review executed: **NO**

ADR-0087 remains the accepted paper routing profile. This ADR accepts only the executable evidence contract; it does not select one universal physical storage format or certify any runtime adapter.

## Stop-the-line examples

Field Storage cannot certify if secret plaintext reaches generic persistence/log/export/cache; wrong-site/network or unauthorized values are disclosed/mutated; protected WordPress security keys become generic editable fields; Definition publish is presented as completed value migration; null/default states are silently collapsed; unsupported queryability/uniqueness/revision capability is claimed; hard uniqueness fails under the advertised concurrency contract; generic fields become a shadow Relations engine; Q3/Q4 is claimed without index/query-plan evidence; REST bypasses Policy; future schema is destructively downgraded; migration interruption loses/duplicates/crosses scope; protected cache survives committed revoke; or a projection becomes unrecoverable source truth.

## Development gate

This ADR authorizes no `register_meta()`, metadata/option write, Custom Table/child-row storage, Relations mutation, Vault secret operation, projection, Query execution, REST exposure, privacy mutation, migration/backfill, cache operation, fixture generation, benchmark or runtime test.

ADR-0014 explicit scoped owner consent remains required before every executable Field Storage action.