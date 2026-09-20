# P-006 B6d — FP-90 Disposable DB Snapshot Prerequisite Evidence

Issue: #1099

Prerequisite PR: #1100

Authorization: `GOV-P006-B6D-FP90-DISPOSABLE-DB-SNAPSHOT-001`

Classification: **TEST-ONLY DISPOSABLE SQL SNAPSHOT/RESTORE PREREQUISITE / FP-90 NOT FORMALLY EXECUTED / NON-CERTIFYING**

Accepted prerequisite source head: `6e7cef451494a5403991755b599ece560177675c`

## 1. Purpose and boundary

B6d proves that one exact older **disposable** WPE database state can be snapshotted, destroyed, restored and reproduced deterministically before any later separately authorized formal FP-90 execution.

It does **not**:

- execute FP-90 formally;
- invoke pending Pro migrations 220/221 after restore;
- implement or certify product Backup/Restore;
- touch production/live data or credentials;
- execute a destructive/irreversible WPE migration;
- create a P-001/CF runtime grant;
- mutate product runtime source;
- promote pair/runtime/migration certification, ADR-0010, deployment or release authority.

Formal P-006 accounting remains:

**144 documented / 57 executed / 57 PASS / 0 FAIL / 0 INCONCLUSIVE / 0 certified Free/Pro pairs / 0 runtime certifications**.

## 2. Accepted exact-head verification

Accepted dedicated workflow:

- workflow: **P-006 B6d FP-90 Disposable DB Snapshot Prerequisite**;
- run id: **35542007281**;
- source head: `6e7cef451494a5403991755b599ece560177675c`;
- latest-change gate: **PASS**;
- disposable snapshot prerequisite: **PASS**.

Exact-head Governance Gate:

- run id: **35542007218**;
- result: **PASS**.

Accepted environment:

- WordPress **6.9**;
- PHP **8.2.33**;
- MySQL **8.4.11**;
- disposable database: `wpessential_b6d`;
- table prefix: `wpep006b6d_`.

## 3. Older accepted WPE state

The prerequisite establishes exactly these settled Free migration markers:

1. `006.create-compiled-registration-atomic-store`;
2. `007.create-definition-persistence`;
3. `008.create-audit-ptd-store`.

The following remain absent throughout the prerequisite:

- `220.custom_tables_migration_runs_v1`;
- `221.custom_tables_migration_execution_confirmations_v1`;
- `wpe_custom_table_migration_runs`;
- `wpe_custom_table_migration_confirmations`.

WPE-owned tables in the accepted older state:

- `wpep006b6d_wpe_audit_events`;
- `wpep006b6d_wpe_definition_dependencies`;
- `wpep006b6d_wpe_definitions`;
- `wpep006b6d_wpe_migrations`.

Fixed non-secret sentinel:

- key: `wpe_p006_b6d_sentinel`;
- value: `fp90-older-db-preserve-v1`.

Normalized WPE schema SHA-256:

`b5dce97185091736adb5e7400216f8501b2394d083daca98edd400a5570a1da7`

Normalized WPE state SHA-256:

`6db1c72036e77ff1bece365eb2abf7dd55018a066739899b597e247f6bce8600`

Per-table normalized schema SHA-256:

| Table | SHA-256 |
| --- | --- |
| `wpep006b6d_wpe_audit_events` | `c52c6b0774b3b5c469183c0bda652832cdc75ab299dcbae8effc52bbb2b35cdb` |
| `wpep006b6d_wpe_definition_dependencies` | `2682eda85e9da744c74f00944d45c54ba5012d4c0a776f64fc418fc8df39bea9` |
| `wpep006b6d_wpe_definitions` | `8aca46ddc21536ebc26e00970ceca15ada74afb811dc2bb59798da54fc1f74d7` |
| `wpep006b6d_wpe_migrations` | `e86e4735f463322b290fa1d78758f9afcd0845594572fb485effb36f6c24739e` |

## 4. MySQL snapshot fixed point

The raw first `mysqldump` replay makes MySQL's implicit column charset representation explicit in `SHOW CREATE TABLE` output. The prerequisite does not rewrite SQL text to hide this.

Instead it proves a MySQL-owned fixed point:

1. establish the logical older state;
2. export a seed SQL snapshot;
3. drop/recreate only `wpessential_b6d`;
4. import the seed;
5. prove normalized schema/state identity is unchanged;
6. run the canonical package compatibility preflight;
7. export the accepted canonical SQL snapshot;
8. drop/recreate/import that accepted snapshot;
9. prove semantic state identity again;
10. export again and prove the accepted snapshot is byte-identical.

Seed SQL snapshot:

- SHA-256: `53d27f6a111009d4ccc5ac59eb838be5e67d54a06c30e57a07e46595a17eb618`;
- bytes: **51,673**.

Accepted fixed-point SQL snapshot:

- SHA-256: `24178a1289fb5929eda850f488d932c94bbbddc5936a82cc26cb83979823b058`;
- bytes: **112,681**.

Second fixed-point export:

- SHA-256: `24178a1289fb5929eda850f488d932c94bbbddc5936a82cc26cb83979823b058`;
- byte-identical to accepted snapshot: **true**.

The seed and accepted dump are intentionally not claimed byte-identical. The accepted snapshot is the stable MySQL replay fixed point.

The workflow also refuses snapshots containing:

- `CREATE DATABASE` / `USE` statements;
- Pro 220/221 migration IDs;
- Pro migration table names.

## 5. Semantic restore identity

After both the seed replay and accepted-snapshot replay, the prerequisite proves:

- migration IDs equal the original 006/007/008 set;
- WPE table set is unchanged;
- normalized per-table schema identities are unchanged;
- normalized aggregate schema SHA is unchanged;
- normalized state SHA is unchanged;
- sentinel key/value are unchanged;
- Pro migration IDs/tables remain absent.

One narrow normalization is used for `SHOW CREATE TABLE` comparison: redundant column-level `CHARACTER SET utf8mb4` is removed only when it immediately precedes the same `COLLATE utf8mb4_unicode_520_ci`. The collation, table default charset/collation, types, nullability, keys and all other DDL semantics remain part of the compared state.

## 6. Compatibility before pending migrations

The prerequisite uses the real `LocalCompatibilityPreflight::evaluate()` against the exact canonical package pair before any pending Pro migration execution.

Canonical F0:

- ZIP SHA-256: `2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80`;
- payload-tree SHA-256: `0698d1a772704bcf44bea1eefae0c490212d6bb6ee214a4f6a5c33db1bf998b9`;
- entry count: **239**;
- main-entry SHA-256: `b2a32e91d7f7bbc3cbe22e37094274e5d1ccdbc0d31c79e385f02e399e679e61`.

Canonical P0:

- ZIP SHA-256: `bd74077f22c7268765519ac2d4c64dd785ba59c9eee68f5a5f1b4d8fd8de0467`;
- payload-tree SHA-256: `fbaa10957eeb482bd248ef2833b021202068ce7f420fe2d78f35b0e522b0bfd0`;
- entry count: **287**;
- main-entry SHA-256: `aabf93d673c4dae5cf511d540079a2db1aef869069e047c3ac5c22c0bfe5b368`.

Pair ID:

`28e96f5207d3dc195bbf1b1a883ea7dcea5a314580661de5400e4e299dbd5211`

Observed preflight:

- state: `compatible`;
- dimension: `pair`;
- reason: `compatible_local_pair`;
- premium boot allowed: **true**;
- premium migrations allowed: **true**;
- evaluated before pending migration execution: **true**;
- pending Pro migrations invoked: **false**.

The prerequisite terminates before 220/221 are invoked.

## 7. Network and safety boundary

During prerequisite state construction after WordPress installation:

- prerequisite-phase outbound WordPress HTTP attempts: **0**.

The WordPress installer itself produced **2** blocked HTTP attempts under the dedicated network blocker; those installer attempts are recorded separately and do not participate in the migration prerequisite phase.

The accepted evidence also records:

- product Backup/Restore implemented/certified: **false**;
- production/live data used: **false**;
- product runtime source modified: **false**;
- destructive/irreversible WPE migration executed: **false**;
- provider/remote runtime used: **false**;
- runtime grant created: **false**;
- formal fixture executed: **false**;
- fixture accounting changed: **false**.

## 8. Immutable artifact

Accepted run **35542007281** produced:

- artifact id: **10615316516**;
- artifact name: `p006-b6d-fp90-prerequisite-35542007281`;
- archive digest: `sha256:7e2c19c0ec29e81cbfef865bac2a404c315189d7852b1af662ee3187417fd8a3`.

The artifact contains the accepted evidence JSON, before/restored/compatibility evidence, the seed dump and the accepted fixed-point SQL snapshot.

## 9. Pre-acceptance diagnostics

Failed development runs do **not** count as formal fixture or prerequisite PASS:

- run **35541352389** — workflow assertion typo in the canonical Pro hash;
- run **35541485786** — exposed raw `SHOW CREATE TABLE` representation drift after dump/import;
- run **35541593704** — added field-level drift diagnostics;
- run **35541716384** — proved the exact non-semantic drift was redundant column-level `CHARACTER SET utf8mb4`;
- run **35541870992** — semantic restore and compatibility passed; raw first dump was not yet a fixed point.

The accepted run **35542007281** then proved both semantic restore identity and a byte-stable MySQL snapshot fixed point.

## 10. Terminal prerequisite decision

**B6d prerequisite: PASS / EVIDENCE ACCEPTED.**

FP-90 remains **NOT FORMALLY EXECUTED**.

All four B6 prerequisite tracks now have accepted prerequisite evidence:

- FP-89 — B6a isolated Platform API candidate;
- FP-86 — B6b fail-once marker-store path;
- FP-94 — B6c diagnostics/redaction capture;
- FP-90 — B6d disposable older-DB SQL snapshot identity.

A later formal runtime tranche still requires separate owner-authoritative execution scope and a new non-reusable P-001/CF grant. No formal fixture execution or certification follows from B6d.
