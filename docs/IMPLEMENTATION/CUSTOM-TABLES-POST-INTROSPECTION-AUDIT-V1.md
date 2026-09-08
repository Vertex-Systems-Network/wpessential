# Custom Tables Post-Introspection Next-Lane Audit V1

Exact main audited: `9e9d5028d08321dbfbc741ae4a87ae9c4bb006c6`  
Audit owner: Issue #392  
Entry condition: Issue #389 / merged PR #391 trusted CT1 physical identity + strictly read-only schema introspection is promoted exact-head green.

## Verdict

**Custom Tables remains ACTIVE / NOT PASS. The next safe serialized tranche is a server-selected provider capability profile plus a pure provider DDL compilation preview. Physical DDL execution remains blocked.**

Surface 7 now has the prerequisite truth planes needed to compile a provider operation representation without mutating the database:

1. canonical desired `TableSchemaDescriptor`;
2. trusted CT1/PT-E physical identity;
3. real read-only MySQL/MariaDB observed schema and observed fingerprint;
4. deterministic desired-to-observed `MigrationPlan` with R0-R4 risk/blocking/recovery semantics.

The missing seam before any executor can be audited is the provider-specific statement/capability representation. That representation can be certified as a pure compiler with zero database side effects.

## Why provider compilation is next

A migration executor must not invent SQL, identifier quoting, provider capability assumptions, online/locking promises or operation ordering at execution time.

The next lane therefore separates **compilation** from **execution**:

- input is already-reviewed logical desired/observed/plan state plus trusted physical identity;
- server provider/version capability facts are trusted runtime inputs, never request-selectable product inputs;
- output is immutable typed statement preview only;
- unsupported or blocked operations fail closed before an executor exists;
- statement fingerprinting gives later run-state/revalidation layers a stable generation identity;
- no database mutation is possible in the compiler path.

Migration-state persistence by itself would record intent before a certified physical operation representation exists. Precondition scans/backfills are operation-specific and should remain deferred until the provider plan language is stable.

## Next authorized tranche

### Issue
`#393 — Custom Tables V1 — provider capability profile + pure DDL compiler preview`

### Claim
`agent/custom-tables-provider-ddl-compiler-v1`

### Required contract

- immutable trusted provider capability profile for supported MySQL/MariaDB baselines;
- provider/version facts originate only from connected server evidence or injected trusted test fixtures;
- no request/public API can choose provider, version, algorithm, database/schema, raw physical name or arbitrary SQL;
- pure compiler accepts promoted `MigrationPlan`, target `TableSchemaDescriptor`, trusted `Ct1ManagedTableIdentity` and trusted capability profile;
- deterministic statement ordering, canonical statement metadata and fingerprint;
- trusted identifiers come only from canonical descriptors/identity and are quoted by one bounded compiler path;
- blocked/manual-drift plans do not yield executable statement previews;
- R3/R4 remain blocked from executable compilation in this tranche;
- compile only explicitly supported safe operation families and fail closed for unknown/unsupported operations;
- provider algorithm/lock/instant/in-place/copy capability is represented conservatively; no zero-downtime claim from syntax alone;
- no statement execution method exists.

## Candidate compile families

The compiler may produce previews only for safe/unblocked plan operations that have deterministic provider semantics in the bounded V1 vocabulary, such as:

- missing-table `create_table` preview;
- safe additive `add_column`;
- safe `alter_column_default`;
- supported nullability changes only where the plan is unblocked and provider shape is certified;
- supported widening `alter_column_type` only where plan risk/capability permits;
- `add_index` and precondition-safe `add_unique_constraint` representation.

Drop column/index/unique, primary-key replacement, lossy conversions, manual drift and other R3/R4 operations remain non-executable/blocked previews in this tranche.

## Physical mutation remains blocked

This audit does **not** authorize:

- dispatching generated CREATE/ALTER/DROP/RENAME statements;
- `dbDelta()` execution;
- generic `$wpdb->query()` DDL execution;
- migration run/applied-generation persistence or lease/retry state;
- Backup/restore execution;
- precondition data scans, backfill/dedup/shadow-copy/swap;
- row CRUD/Data Source/Query/admin/REST/Ability runtime;
- CT2/PT-D or CT3 runtime/topology conversion;
- external adoption;
- product parity, deployment or release.

## Acceptance for Issue #393

Exact-head evidence must prove:

- deterministic provider profile and statement fingerprints;
- trusted identifier quoting and no caller-controlled physical-name/provider widening;
- equivalent inputs compile identically;
- blocked/manual-drift/R3-R4 plans cannot become executable statements;
- unsupported provider/version/operation semantics fail closed;
- MySQL/MariaDB differences are explicit where actually certified rather than guessed;
- compiler source contains no database mutation dependency or execution primitive;
- no shared Platform DB interface change is introduced.

Only after Issue #393 promotes should another exact-main Supervisor audit decide whether migration-run persistence, precondition scanning/recovery integration, a narrowly bounded executor, or another prerequisite is next.