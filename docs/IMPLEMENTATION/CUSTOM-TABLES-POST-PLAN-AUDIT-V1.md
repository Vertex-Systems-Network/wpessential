# Custom Tables Post-Plan Next-Lane Audit V1

Exact main audited: `29c8a22b04f8fee64848f60161fc2e4eccab5b14`  
Audit owner: Issue #388  
Entry condition: Issue #385 / merged PR #387 execution-free observed schema + pure Migration Plan is promoted exact-head green.

## Verdict

**Custom Tables remains ACTIVE / NOT PASS. The next safe serialized tranche is CT1 trusted physical identity + strictly read-only real database schema introspection. Physical DDL generation/execution remains blocked.**

The promoted planner now correctly separates authored desired state, normalized observed state and a pure typed Migration Plan. It still intentionally receives server-resolved metadata rather than touching the database. The next prerequisite must therefore prove how a CT1/PT-E managed table obtains a trusted environment-specific physical identity and how real MySQL/MariaDB metadata is read into the promoted normalizer without opening arbitrary database access.

A provider DDL compiler, migration executor or applied-state writer before trusted real observation/revalidation would invert the canonical safety dependency.

## Exact-main evidence

PR #387 promoted:

- deterministic `ObservedTableSchema` normalization/fingerprinting;
- explicit missing-table state;
- desired-to-observed typed Migration Plan preview;
- deterministic plan ordering/id/fingerprint;
- R0–R4 risk, blocking and recovery classification;
- explicit unsupported/manual drift findings;
- fail-closed decimal capacity-loss classification;
- tests proving the planning path contains zero provider DDL/database mutation primitives.

Applicable exact-head CI was green before merge: PHP Quality Toolchain, Architecture Guards, Platform Compatibility Matrix and Distributable Package.

## Why read-only introspection is next

The canonical PT-D/PT-E profile states:

1. CT1/PT-E is the first baseline for ordinary site-owned managed tables;
2. each managed table has an environment-specific physical identity/mapping distinct from its portable Definition identity;
3. a migration run must introspect observed schema, generate/revalidate the plan, and introspect again after future mutation;
4. Definition publication changes desired state only.

Current Platform database services expose generic database reads and the network base prefix, but Surface 7 has no promoted canonical per-site CT1 physical-identity resolver or real schema introspector. That missing seam is a prerequisite for trustworthy source fingerprints and later pre-mutation revalidation.

## Next authorized tranche

### Issue
`#389 — Custom Tables V1 — trusted CT1 identity + read-only schema introspection`

### Claim
`agent/custom-tables-readonly-introspection-v1`

### Objective
Implement the smallest real-observation seam needed before any DDL compiler/executor exists.

### Required contract

- immutable Surface-7-owned CT1 physical identity bound to a canonical managed `TableSchemaDescriptor`;
- physical table identity derived only from trusted WordPress current-site context/prefix plus a fixed WPE-managed namespace and canonical logical table key;
- no request/public API may submit a raw physical table name, table prefix, database/schema name or provider selector;
- enforce identifier length/character bounds before metadata access;
- strictly read-only existence/schema metadata inspection;
- observe only bounded V1 facts required by `ObservedTableSchemaNormalizer`: columns, primary key, secondary/unique indexes and charset/collation;
- MySQL/MariaDB/provider/version facts may be detected from the connected server for diagnostics/capability classification, never selected by public input;
- feed raw server metadata through the already-promoted normalizer rather than inventing a second schema semantic model;
- missing table returns explicit `exists=false`;
- unsupported type/default/collation/index shapes remain findings/blockers, never guessed into supported semantics;
- deterministic normalized `ObservedTableSchema` and fingerprint for repeated equivalent physical metadata.

## Read-only SQL boundary

The worker may use narrowly bounded metadata queries only. Any concrete reader must prove it does not issue or expose:

- `CREATE`, `ALTER`, `DROP`, `RENAME`, `TRUNCATE`;
- `INSERT`, `UPDATE`, `DELETE`, `REPLACE`;
- raw arbitrary SQL supplied by callers;
- public identifier interpolation;
- cross-site table selection not derived from trusted CT1 context.

`SHOW`, `DESCRIBE`, `INFORMATION_SCHEMA` or equivalent read-only metadata mechanisms may be used only when identifiers are server-derived/validated and the implementation remains portable across the supported MySQL/MariaDB matrix.

## Shared Platform boundary

The current shared `DatabaseAdapterInterface` MUST NOT be widened in this worker lane merely for convenience.

If a missing neutral Platform primitive is genuinely unavoidable, stop that portion and return it to a separate Supervisor-owned shared-service audit. Surface 7 must not smuggle a generic DBA/introspection console into Platform.

## Multisite boundary

This tranche certifies CT1/PT-E only:

- current site owns the logical table;
- physical namespace carries site isolation;
- another site's physical table cannot be selected by request input or logical row identifier;
- CT2/PT-D shared scoped storage and CT3 network-owned storage remain deferred;
- no topology conversion/fan-out migration is authorized.

Tests must include distinct trusted site prefixes/contexts proving physical identities do not collide.

## Explicitly blocked after this audit

Even after Issue #389 promotes, these remain separately serialized:

- provider SQL/DDL compiler;
- CREATE/ALTER/DROP/rename/`dbDelta()` execution;
- migration run persistence/state machine/lease/applied-generation truth;
- precondition data scans such as duplicates, NULL/range/max-length checks;
- backfill/dedup/shadow-copy/swap;
- verified Backup/restore execution;
- row CRUD/Data Source and Query provider runtime;
- admin/REST/Ability mutation surface;
- external-table adoption;
- CT2/PT-D and CT3 runtime/topology conversion;
- product parity, deployment and release.

## Acceptance for Issue #389

Exact-head tests/evidence must prove:

- deterministic trusted CT1 physical identity derivation;
- per-site namespace isolation;
- no caller-controlled physical table/prefix/provider widening channel;
- real read-only supported MySQL/MariaDB metadata maps to the promoted observed vocabulary;
- missing table remains distinct from empty table;
- unsupported metadata fails closed into findings/blockers;
- repeated equivalent observations yield the same normalized fingerprint;
- the introspection path contains and executes zero DDL or data-mutation statements;
- no shared Platform DB interface changes are introduced.

Only after Issue #389 is promoted should another exact-main Supervisor audit decide whether provider capability/DDL compilation, migration-state persistence, precondition scanning, or another prerequisite is the next safe lane.