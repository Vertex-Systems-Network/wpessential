# P-006 B6b — FP-86 Fail-Once Migration Marker Prerequisite Evidence

Issue: #1095

Runtime/evidence PR: #1096

Authorization: `GOV-P006-B6B-FP86-FAIL-ONCE-MARKER-001`

Classification: **TEST-ONLY HARNESS PREREQUISITE / DISPOSABLE WORDPRESS+MYSQL / FP-86 NOT FORMALLY EXECUTED / NON-CERTIFYING**

Accepted prerequisite source head: `dcc15826df5989df23f3f1e588371af4038ad7d8`

## 1. Purpose and boundary

B6b validates only the fail-once marker-store harness prerequisite needed before any later formal FP-86 execution.

It does not formally execute FP-86. It does not certify generic migration interruption recovery. It does not alter product bootstrap, dependency injection, migration runtime source, or the accepted migration contract.

Formal P-006 accounting remains:

**144 documented / 57 executed / 57 PASS / 0 FAIL / 0 INCONCLUSIVE / 0 certified Free/Pro pairs / 0 runtime certifications**.

## 2. Exact production classes used unchanged

The accepted prerequisite composes the repository's real current classes:

- `MigrationRunner`;
- `MigrationRegistry`;
- `MigrationStateStoreInterface`;
- `WpdbMigrationStateStore`;
- `NativeWpdbAdapter`;
- `CreateMigrationRunStoreMigration`.

The only injected fault is a test-only `MigrationStateStoreInterface` decorator. It delegates `appliedIds()` to the real persistent state store and throws exactly once on:

`markApplied('220.custom_tables_migration_runs_v1')`

The failed first marker write is not delegated. No product DI seam was added.

## 3. Accepted exact-head verification

Accepted prerequisite workflow:

- workflow: **P-006 B6b FP-86 Fail-Once Marker Prerequisite**;
- run id: **35539744399**;
- source head: `dcc15826df5989df23f3f1e588371af4038ad7d8`;
- latest-change gate: **PASS**;
- disposable prerequisite cell: **PASS**.

Exact-head Governance Gate:

- run id: **35539744384**;
- result: **PASS**.

Evidence/shared-truth-only finalization:

- finalization head: `467c129b8589872554f4df58088f374def556c81`;
- Governance run: **35539959777 — PASS**;
- B6b change-gate run: **35539959790 — PASS**;
- disposable `Validate fail-once marker prerequisite` job: **SKIPPED**.

This proves evidence/shared-truth synchronization does not rerun or silently replace the accepted disposable prerequisite evidence.

Earlier run **35539597530** is diagnostic only and is not prerequisite evidence. Its scenario assertions had already passed far enough to reach the final network assertion; it failed because blocked WordPress installer URL probes were counted together with the migration-prerequisite phase. The harness was corrected to preserve those blocked installer attempts as explicit metadata, reset the probe log after installation, and require zero attempts during the actual migration-prerequisite phase.

No product behavior was changed to obtain the accepted PASS.

## 4. Disposable environment

Accepted cell:

- WordPress: **6.9**;
- PHP: **8.2.33**;
- MySQL: **8.4.11**;
- table prefix: `wpep006b6b_`.

This is one prerequisite-validation cell only, not the formal P-006 runtime matrix.

No product plugin is activated. WordPress core is installed only to provide a genuine `wpdb` object and database environment.

## 5. Exact migration subject

Target migration:

- ID: `220.custom_tables_migration_runs_v1`;
- sequence: **220**;
- current destructive flag: **false**;
- target table: `wpep006b6b_wpe_custom_table_migration_runs`;
- current implementation: idempotent `CREATE TABLE IF NOT EXISTS`.

The result is bounded to this exact current migration implementation. It is not evidence that arbitrary future migrations are idempotent or recoverable.

## 6. Fail-once prerequisite result

### Before first run

- target table present: **false**;
- applied migration IDs: `[]`;
- real state store initialized only `wpep006b6b_wpe_migrations`.

### First real runner

The real migration `apply()` completes and creates the target table.

The test-only marker decorator then throws:

`P006_B6B_FAIL_ONCE_MARKER_WRITE`

Immediately after that failure:

- target table present: **true**;
- applied migration IDs: `[]`;
- target schema SHA-256:
  `3d5c22d7013a46bbba3ddee33ec00aedafdd0b23b0e56eae3e75a58e6cb99318`.

This proves the harness can establish the exact "data/schema step committed, marker absent" prerequisite state.

### Second fresh runner

A newly constructed normal `WpdbMigrationStateStore`, new registry, and new runner execute against the same disposable DB.

Result:

- returned applied IDs: `["220.custom_tables_migration_runs_v1"]`;
- persistent migration IDs: `["220.custom_tables_migration_runs_v1"]`;
- marker row count for migration 220: **1**;
- target schema SHA-256:
  `3d5c22d7013a46bbba3ddee33ec00aedafdd0b23b0e56eae3e75a58e6cb99318`;
- schema unchanged from the first completed `apply()`: **true**.

### Third fresh runner

A third newly constructed real state store/registry/runner observes the settled state.

Result:

- returned applied IDs: `[]`;
- persistent migration IDs remain exactly migration 220;
- target schema SHA-256 remains:
  `3d5c22d7013a46bbba3ddee33ec00aedafdd0b23b0e56eae3e75a58e6cb99318`;
- schema unchanged from retry: **true**.

The only WPE-owned tables created by this prerequisite are:

- `wpep006b6b_wpe_migrations`;
- `wpep006b6b_wpe_custom_table_migration_runs`.

## 7. Network boundary

The MU probe blocks WordPress HTTP before transport.

During disposable WordPress installation, WordPress attempted two local canonical-post URL probes:

- `http://p006-b6b.test/2026/09/20/hello-world/`;
- `http://p006-b6b.test/index.php/2026/09/20/hello-world/`.

Both were intercepted by `pre_http_request`; no outbound transport occurred.

After installation, the network-attempt log was reset. During the actual fail-once migration prerequisite phase:

- WordPress HTTP attempts: **0**.

Evidence therefore distinguishes blocked installer probes from the migration-prerequisite phase instead of hiding them.

## 8. Immutable artifact

Accepted workflow artifact:

- artifact id: **10614580717**;
- artifact name: `p006-b6b-fp86-prerequisite-35539744399`;
- archive digest:
  `sha256:e17644327bd8ddd57c17d12205e3d8a0959ca3966b426391d8f2a1eeb4a6e6fc`.

The artifact contains the accepted `evidence.json` bound to source head `dcc15826df5989df23f3f1e588371af4038ad7d8`.

## 9. Security and scope invariants

The workflow uses:

- exact source-head checkout;
- pinned GitHub Action revisions;
- `permissions: contents: read`;
- no persisted repository credentials;
- one disposable MySQL service;
- one fixed, prefix-validated table namespace;
- no arbitrary SQL or user-supplied identifier input;
- no production/live DB;
- no provider/license/billing service;
- no updater/TUF/signing path;
- no product plugin activation;
- no product runtime source mutation;
- no destructive migration.

## 10. Terminal prerequisite decision

**B6b prerequisite: PASS / MERGE-READY.**

The repository now has a deterministic test harness that can construct the exact current marker-failure prerequisite state for a later separately authorized FP-86 formal fixture.

**FP-86 remains NOT FORMALLY EXECUTED.**

No counter, generic migration-recovery certification, Free/Pro pair certification, runtime certification, permanent P-001/CF state, ADR-0010 status, deployment or release authority changes.
