# WPEssential — P-006 Planning Lane C: Schema and Custom Tables Migration Evidence Map

Status: **PLANNING / EVIDENCE-DESIGN ONLY — EXECUTION NOT AUTHORIZED**  
Parent gate: **Issue #924**  
Worker lane: **Issue #927**  
Coordination: **Issue #930 / merged PR #931**  
Inputs: **Lane A / PR #932**, **Lane B / PR #933**  
Planning anchor: **current `main` after PR #933**  
Fixture scope: **FP-77…FP-94**  
P-006 truth: **144 documented / 0 executed / 0 passed / 0 failed / 0 certified pairs / 0 runtime certifications**

## 1. Purpose and hard boundary

This document plans the evidence required to prove Free/Pro schema compatibility, migration admission, interruption/retry/concurrency safety, marker/schema reconciliation, rollback/restore behavior and diagnostics privacy.

It does **not**:

- execute or register new migrations;
- mutate a database or schema;
- create backups/restores;
- alter Custom Tables runtime or readiness behavior;
- change migration IDs, sequence numbers or recovery contracts;
- execute FP fixtures;
- promote compatibility/runtime/product certification;
- deploy or release.

Any future schema-changing fixture requires explicit P-006 authorization plus the fixture-specific destructive/migration/recovery gate.

## 2. Critical truth distinction: declared generation is not observed durable state

Current compatibility metadata publishes:

- Free code declaration `WPE_PLATFORM_SCHEMA_GENERATION`;
- Pro supported Free schema-generation min/max;
- Pro code declaration `WPE_PRO_SCHEMA_GENERATION`.

Those values identify the **schema contract expected by the binaries**. They do not, by themselves, prove that the live database has actually reached that generation or that its structure/data agree with a migration marker.

Formal P-006 migration evidence must therefore record at least three separate truths:

1. **binary-declared schema contract** — values published by the exact Free/Pro artifacts;
2. **migration-state markers** — applied migration IDs/revisions stored durably;
3. **observed schema/data facts** — table/column/index/version/fingerprint facts required by the migration contract.

A marker cannot silently substitute for observed schema/data, and a binary constant cannot silently substitute for either.

## 3. Existing non-certifying migration architecture

### 3.1 Compatibility admission

`LocalCompatibilityPreflight` blocks premium boot and compatibility-layer migration admission when Free plugin/API/platform-schema metadata is missing, malformed, too old or too new.

`frameworks/Bootstrap/Plugin.php` registers the current Pro Custom Tables infrastructure migrations only when `proCustomTablesMigrationsAllowed()` is true, which requires:

- Pro package present;
- canonical request-local compatibility state `compatible`;
- `premium_boot_allowed=true`;
- required Pro classes available;
- `premium_migrations_allowed=true`.

This is useful admission evidence, but it is not migration correctness/certification.

### 3.2 Generic migration layer

`MigrationRegistry`:

- requires stable IDs and positive unique sequence numbers;
- produces deterministic numeric sequence ordering.

`MigrationRunner`:

- reads already-applied IDs;
- skips marked migrations;
- requires a recovery plan only when a migration declares itself destructive;
- calls `apply()` and only afterwards marks the migration applied.

`WpdbMigrationStateStore`:

- stores migration IDs in the network-prefixed `wpe_migrations` table;
- validates canonical marker IDs;
- uses an idempotent primary-key + `INSERT IGNORE` marker write.

Important limitation for P-006 planning: this generic layer does not itself expose a lock, transaction coordinator, migration checksum, schema fingerprint, applied-marker reconciliation or automatic backup primitive.

### 3.3 Current Free/Pro ordering input

Free Platform migrations are registered before optional Pro Custom Tables migrations in `Plugin::createPersistenceServices()`. Current Pro Custom Tables infrastructure uses high deterministic sequences:

- `220.custom_tables_migration_runs_v1`;
- `221.custom_tables_migration_execution_confirmations_v1`.

The inspected Pro migrations are non-destructive `CREATE TABLE IF NOT EXISTS` operations. That makes these specific table-creation operations naturally retry-tolerant in some failure windows, but it must not be generalized to all future migrations.

### 3.4 Custom Tables migration domain

The Pro Custom Tables source already contains planning/readiness/recovery/run concepts, including migration plans, provider capability profiles, preconditions, execution confirmation storage, recovery components and run persistence. These are useful architecture inputs. Surface 7 remains separately governed and is not promoted by P-006 planning.

## 4. Required future migration evidence record

Every authorized FP-77…FP-94 fixture must record:

- exact Free/Pro artifact SHA-256 + compatibility metadata;
- database engine/version and WordPress site/network scope;
- pre-fixture DB snapshot identifier;
- binary-declared platform/Pro schema generations;
- applied migration marker set before/after;
- observed schema fingerprints/facts before/after;
- selected migration IDs and deterministic sequence order;
- canonical compatibility result before migration admission;
- migration-specific precondition result;
- destructive/non-destructive declaration and recovery plan;
- lock/concurrency identity if relevant;
- interruption point if relevant;
- exact DB error class/code only where safe;
- retry/reconcile action;
- post-fixture data-integrity assertions;
- restore verification where required;
- no-secret/no-row-content diagnostics assertion;
- PASS/FAIL/INCONCLUSIVE/NOT EXECUTED.

Evidence must not contain raw private table contents merely to prove schema state.

## 5. Fixture plan — FP-77…FP-94

Status labels: `STRONG EXISTING INPUT`, `PARTIAL EXISTING INPUT`, `GAP`, `CROSS-GATE` describe planning readiness, not certification.

| FP | Status | Existing input | Future authorized fixture / expected result | Stop / dependency |
| --- | --- | --- | --- | --- |
| FP-77 | PARTIAL EXISTING INPUT | Current exact Free/Pro declarations both use platform schema generation `1`; compatibility admits only supported range. | Install immutable matching pair against DB proven to match required observed schema; expect normal premium boot/migration admission, no unnecessary migration. | Requires real observed-schema probe, not constants alone. |
| FP-78 | STRONG EXISTING INPUT for declared contract | Preflight rejects Free platform schema below Pro minimum. | Pair Pro with Free whose declared/observed platform schema is behind; expect block before any Pro migration registration/execution. | STOP if Pro migration runs. |
| FP-79 | STRONG EXISTING INPUT for declared contract | Preflight rejects Free platform schema above Pro maximum. | Pair older Pro with newer unsupported Free schema generation; expect fail-closed, no writes. | STOP on mutation. |
| FP-80 | PARTIAL EXISTING INPUT | Compatible pair can admit Pro migrations; current Pro table-creation migrations are ordered/idempotent DDL. | Start compatible binaries with observed Pro-owned durable schema behind code-required generation; run only the separately authorized, applicable Pro migrations after compatibility + preconditions pass. | Requires a real persisted Pro schema-generation/fingerprint contract; code constant alone is insufficient. |
| FP-81 | GAP | `WPE_PRO_SCHEMA_GENERATION` declares code expectation but current local preflight does not compare it to an independently observed persisted Pro schema state. | Prepare database with Pro-owned schema ahead of current Pro binary support; binary must enter read-only/degraded recovery state and block unsafe mutation. | Needs persisted Pro schema observation contract before execution. |
| FP-82 | PARTIAL EXISTING INPUT | Registry sequence ordering exists; Free migrations are registered before optional Pro Custom Tables migrations, whose current sequences are 220/221. | Fresh disposable DB: trace exact ordered migration list and prove all required Free-owned prerequisites complete before dependent Pro migration begins. | Must define dependency edges, not rely only on coincidental sequence numbers. |
| FP-83 | GAP/PARTIAL | Runner marks after `apply()`, so an interruption can leave applied DDL without marker; current `CREATE TABLE IF NOT EXISTS` migrations may tolerate retry. No general resume protocol exists. | Fault-inject at before-apply, mid-apply where DB permits, after-apply-before-marker and after-marker points; retry must either deterministically reconcile/resume or block with recovery state. | Requires per-migration idempotency/recovery contract and DB snapshot. |
| FP-84 | GAP | Generic runner trusts an existing applied marker and does not introspect schema/data to verify it. | Seed marker as applied while intentionally withholding/corrupting required schema in disposable DB; system must detect mismatch and block mutation with recovery action. | Likely requires future reconciliation/probe implementation before fixture can pass. |
| FP-85 | PARTIAL/GAP | Missing marker causes rerun; current table-creation DDL is `IF NOT EXISTS`, but generic layer has no explicit reconcile evidence. | Create expected schema/data state without marker; run reconciliation path; result must be idempotent and converge to truthful marker without destructive duplicate work. | Must not simply mark arbitrary schema as valid without fingerprint/preconditions. |
| FP-86 | PARTIAL EXISTING INPUT | Applied markers skip completed IDs; current Pro table-creation DDL is idempotent at SQL table-create level. | Run same exact migration twice under clean sequential conditions and compare schema/data/fingerprints; second execution must produce no harmful change. | Per-migration evidence required; cannot certify future migrations generically. |
| FP-87 | GAP | Generic runner has no explicit cross-request migration lock/lease. Marker PK prevents duplicate marker rows but does not serialize two `apply()` calls that race before marking. | Launch concurrent migration attempts against same site/network and migration ID; expect single executor or deterministic fail-closed contender, never unsafe double mutation. | Requires lock/lease/DB advisory strategy or proof specific DDL is concurrency-safe. Stop on concurrent destructive apply. |
| FP-88 | PARTIAL EXISTING INPUT | Deterministic registry sequencing + Free-first registration + Pro 220/221 sequences. | Trace dependency graph and runtime order on fresh DB; Free prerequisite IDs/facts must be complete before dependent Pro migration starts. | Explicit dependency assertion required; numeric order alone is not sufficient long-term. |
| FP-89 | GAP/PARTIAL | Runtime exceptions are bounded messages; no formal migration-failure diagnostics fixture identified. | Force safe disposable DB failure; admin/CLI diagnostics must state migration ID/state/recovery code without dumping SQL secrets/private row contents; runtime must remain recoverable. | Lane E privacy/observability assertions. |
| FP-90 | GAP | Generic runner only requires non-empty `recoveryPlan()` for migrations declaring `isDestructive()`. It does not enforce snapshot/backup proof. | For irreversible/destructive fixture, execution gate must require verified pre-migration backup/snapshot or explicit accepted equivalent before apply. | Separate destructive-operation authorization; STOP if destructive migration can start without required recovery evidence. |
| FP-91 | GAP | No P-006 backup/restore retry evidence exists. | Snapshot → authorized migration/failure → restore → verify data/schema fingerprint → retry or remain safely blocked according to contract. | Requires disposable restore infrastructure and Lane C-specific approval. |
| FP-92 | GAP | ADR-0010 says code rollback against newer unsupported schema must block unsafe mutation; no full recovery path proven. | Run older binary against newer observed schema; expect read-only/degraded diagnostics with explicit forward-update/restore recovery path, no implicit downgrade. | Requires real version graph from Lane B + persisted schema observation. |
| FP-93 | PARTIAL EXISTING INPUT | Current migration classes use generic error text and validated identifiers; they do not intentionally serialize row contents. | Capture all migration failure/readiness/recovery diagnostics; assert table contents, credentials, tokens, private definition payloads and unrestricted raw SQL are absent. | Lane E security review coordinates redaction standard. |
| FP-94 | GAP | Canonical compatibility result is request-local and evaluates binary-declared schema metadata before migrations; it is not automatically re-evaluated from observed DB schema after a migration. | After migration changes durable schema state, start a fresh compatibility/reconciliation evaluation from authoritative observed schema/markers; stale pre-migration state must not authorize further mutation. | Likely requires explicit observed-schema bridge; STOP if cached/pre-migration truth remains mutation authority. |

## 6. Migration state model required before execution

Formal P-006 should not treat migration as a boolean `applied/not applied`. The future harness should classify at least:

- `not_required`;
- `pending_compatible`;
- `blocked_binary_incompatible`;
- `blocked_schema_precondition`;
- `running`;
- `applied_verified`;
- `applied_marker_schema_mismatch`;
- `schema_present_marker_missing`;
- `interrupted_recoverable`;
- `failed_recovery_required`;
- `unsupported_schema_ahead`;
- `restore_verified`.

These can be harness/evidence states without becoming production enum names unless a separate implementation gate accepts them.

## 7. Future harness topology — no implementation here

### M1 — Schema fact collector

Read-only collector for:

- table existence;
- required columns/types/nullability/defaults;
- required indexes/keys;
- migration marker IDs;
- contract-specific schema version/fingerprint;
- row counts only where explicitly necessary and non-sensitive.

It must not dump row contents.

### M2 — Disposable migration executor

Owns:

- exact candidate pair + DB snapshot;
- migration registry/order trace;
- precondition and compatibility admission trace;
- single authorized migration application;
- postcondition/fingerprint verification.

### M3 — Interruption injector

Must support deterministic interruption points:

1. before migration `apply()`;
2. after first durable DB effect where the DB/migration supports simulation;
3. after `apply()` returns but before marker write;
4. after marker write before request completion.

A migration whose DB engine makes a particular mid-statement interruption impossible should record that limitation rather than fabricate evidence.

### M4 — Marker/schema reconciliation harness

Creates only disposable inconsistent states for FP-84/85:

- marker present / schema absent or wrong;
- schema present / marker absent;
- marker and schema fingerprint disagree.

Expected result must be an explicit reconcile or safe block, not silent trust.

### M5 — Concurrency harness

Starts two or more isolated PHP/WordPress processes against the same disposable DB and records:

- lock/lease acquisition if implemented;
- migration start/end timestamps;
- SQL/transaction outcome codes;
- marker/fingerprint final state;
- which executor, if any, was authoritative.

### M6 — Backup/restore verifier

Before any authorized destructive/irreversible fixture:

- create snapshot using approved disposable infrastructure;
- verify snapshot is readable/restorable;
- record snapshot ID/hash without sensitive contents;
- restore to a second or reset environment;
- compare schema/data integrity fingerprints.

## 8. Ordering contract

A future evidence gate should make migration dependencies explicit rather than relying solely on numeric sequence coincidence.

At minimum the evidence graph must show:

1. migration state store availability;
2. Free Platform durable prerequisites;
3. Free definitions/registrations/audit schema as applicable;
4. canonical Free/Pro compatibility PASS;
5. Pro schema-specific preconditions;
6. Pro infrastructure migrations (current 220/221 where applicable);
7. higher-level Custom Tables migration execution only after readiness/confirmation/recovery contract gates.

If a Pro migration has no dependency on a Free migration, evidence should say so explicitly rather than forcing unnecessary coupling.

## 9. Irreversibility and backup policy

Before FP-90/91 or any future destructive migration fixture can run, the implementation/evidence gate must classify the migration:

- fully transactional + rollback-capable;
- idempotent/retry-safe but not transactionally reversible;
- reversible by explicit compensating migration;
- irreversible without snapshot restore.

For the last category, verified snapshot/backup is a hard precondition. A textual `recoveryPlan()` alone is not sufficient evidence that recovery is possible.

## 10. Stop-the-line additions for Lane C

Stop migration evidence immediately if any confirmed fixture shows:

1. Pro migration executes before canonical compatibility PASS;
2. unsupported Free/Pro schema state permits writes;
3. marker says applied while required schema/data facts disagree and runtime continues mutating;
4. two concurrent runners perform an unsafe double mutation;
5. interrupted migration cannot be deterministically classified/recovered;
6. retry duplicates/destructively corrupts data;
7. older code writes against unsupported newer schema;
8. destructive/irreversible migration starts without required recovery proof;
9. restore does not reproduce the expected pre-migration integrity fingerprint;
10. diagnostics leak private row contents, credentials, entitlement artifacts or sensitive SQL values;
11. stale pre-migration compatibility/schema state remains mutation authority after durable schema changes.

## 11. Cross-gate dependencies

| Dependency | Fixtures | Requirement |
| --- | --- | --- |
| P-006 scoped ADR-0014 consent | FP-77…FP-94 | Mandatory before execution. |
| Lane A artifact/boot evidence | All | Immutable binaries + fail-closed bootstrap. |
| Lane B version/update graph | FP-80, FP-81, FP-92, FP-94 | Real older/newer binary/schema combinations. |
| Surface 7 Custom Tables execution governance | Custom Tables-specific migration fixtures | Must not bypass separate runtime/destructive gates. |
| Lane E observability/security | FP-89, FP-93 | Redacted diagnostics and safe authority. |
| Backup/destructive-op authorization | FP-90, FP-91 and any destructive fixture | Verified disposable recovery path before apply. |
| Multisite scope contract | Any network/site migration fixture | Lane D scope semantics first. |

## 12. Recommended future execution slices

1. **C1 — Read-only schema/marker observation**: FP-77…FP-79, no migration apply.
2. **C2 — Fresh install and sequential idempotency**: FP-80, FP-82, FP-86, FP-88 using non-destructive selected migrations.
3. **C3 — Marker reconciliation / interruption**: FP-83…FP-85 after M3/M4 review.
4. **C4 — Concurrency**: FP-87 only after a locking/fail-closed strategy exists or a migration-specific concurrency proof is accepted.
5. **C5 — Failure diagnostics**: FP-89, FP-93 with Lane E review.
6. **C6 — Backup/restore/downgrade**: FP-90…FP-92 under explicit destructive/recovery authorization.
7. **C7 — Post-migration truth refresh**: FP-94 after authoritative observed-schema integration is defined.

## 13. Readiness conclusion

Lane C is **PLANNING COMPLETE / EXECUTION BLOCKED** when this document is accepted.

Current main has strong building blocks: deterministic migration IDs/order, persistent applied markers, post-apply marker writes, recovery-plan requirement for migrations that declare themselves destructive, compatibility gating for current Pro Custom Tables infrastructure migrations, and idempotent `CREATE TABLE IF NOT EXISTS` in the inspected 220/221 migrations. Those facts are valuable but do not close P-006.

The most important gaps are authoritative observed-schema vs declared-generation truth, marker/schema reconciliation, generic concurrency serialization, interruption recovery, verified backup enforcement, unsupported-schema downgrade behavior and post-migration compatibility re-evaluation.

No FP-77…FP-94 fixture is executed by this planning work. ADR-0010 and all P-006 counters remain unchanged.
