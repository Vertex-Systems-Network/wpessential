# P-006 Wave 1T — FP-77 / FP-78 / FP-79 / FP-80 / FP-88 Schema-Migration Evidence

Issue: #1087
Runtime/evidence PR: #1089
Authorization: `GOV-P001-CF-TEMP-018`
Accepted runtime source head: `e3b2589ebd3234b4cecfb4c847713e17f4da0fa2`

Classification: **FORMAL BOUNDED P-006 EVIDENCE / NON-CERTIFYING**

## 1. Scope

Wave 1T formally executes exactly:

- **FP-77** — compatible code + matching schema boots without reapplying migrations;
- **FP-78** — Free schema-behind state executes only the authorized current Free migration path;
- **FP-79** — Pro schema-behind state executes Pro migrations only after Free/Pro compatibility passes;
- **FP-80** — local entitlement does not substitute for binary/API/schema compatibility;
- **FP-88** — Free/Pro migration ordering is explicit at runtime when both need changes.

No other P-006 fixture is executed by this wave.

The run uses only disposable WordPress/MySQL state. It performs no destructive or irreversible migration, provider/license/billing call, updater/TUF/signing flow, production/live mutation, backup/restore, multisite, deploy/release, schema-ahead recovery, interrupted-migration recovery, concurrency execution, pair certification, runtime certification, generic migration certification, permanent P-001/CF certification, or ADR-0010 promotion.

## 2. Accepted exact-head verification

Accepted Wave 1T workflow run:

- workflow: **P-006 Wave 1T Schema Migration Ordering**;
- run id: **35531995346**;
- exact source head: `e3b2589ebd3234b4cecfb4c847713e17f4da0fa2`;
- latest-change gate: **PASS**;
- deterministic candidate graph: **PASS**;
- minimum runtime cell: **PASS**;
- reference runtime cell: **PASS**;
- terminal aggregate: **PASS**.

Exact-head Governance Gate:

- run id: **35531995252**;
- result: **PASS**.

The accepted runtime diff contains only:

- `.github/workflows/p006-wave1t-schema-migration.yml`;
- `tools/p006/p006-wave1t-schema-migration.php`;
- `tools/p006/p006-wave1t-wordpress-setup.sh`.

Product runtime source is unchanged.

### Pre-acceptance harness diagnostics

Earlier Wave 1T attempts did **not** count as formal fixture results.

Run **35514296158** exposed an evidence-detector false positive in FP-77. The harness classified its own `SHOW CREATE TABLE` schema-inspection query as DDL because the detector matched `CREATE TABLE` anywhere in the SQL text.

Diagnostic rerun **35531851930** made the exact query visible:

`SHOW CREATE TABLE ...wpe_audit_events`

The detector was then corrected to match only actual DDL statements at the start of the normalized query. The accepted exact-head run **35531995346** re-executed both authorized cells from fresh disposable state and passed.

No product runtime behavior was changed to obtain the PASS.

## 3. Immutable candidate identity

| Node | ZIP SHA-256 | Payload-tree SHA-256 |
| --- | --- | --- |
| F0 | `2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80` | `0698d1a772704bcf44bea1eefae0c490212d6bb6ee214a4f6a5c33db1bf998b9` |
| F2 | `92e54db0220ea76323fcf2b0e3772ea31e379dac47ed62690b027edbccbff4dc` | `a19b300607420d77b7751334a9a4ea2ad1570ff9078aaa6938e5d32755cce42e` |
| P0 | `0b12ada8265f3032641e09a80ba09f9950d3aef8b526b63c2b3665663aca4178` | `08ef083162109a15899dba7b14d1e420f327323b20cd1434cf9e7cfa23af09a7` |

Formal pair identities:

- F0/P0 compatible pair id: `c564b2fdc079a02dbba57bee0d90dd64fd683605c0fdbca89ab48323b394f2a0`;
- F2/P0 incompatible control pair id: `72f260a4882bb04aaf05a8a4960aaaf7fa2bba0f83591aeb79f5ece4f303480c`;
- expected F2/P0 state: `free_version_too_new`.

Current fixed migration contract exercised by this wave:

1. `006.create-compiled-registration-atomic-store` — sequence 60;
2. `007.create-definition-persistence` — sequence 70;
3. `008.create-audit-ptd-store` — sequence 80;
4. `220.custom_tables_migration_runs_v1` — sequence 220;
5. `221.custom_tables_migration_execution_confirmations_v1` — sequence 221.

## 4. Runtime cells

Minimum:

- WordPress **6.9**;
- PHP **8.2.33**;
- MySQL **8.4.11**.

Reference:

- WordPress **7.1**;
- PHP **8.5.10**;
- MySQL **8.4.11**.

Each fixture uses an isolated WordPress table prefix inside its cell:

- FP-77: `wpep006t77_`;
- FP-78: `wpep006t78_`;
- FP-79: `wpep006t79_`;
- FP-80: `wpep006t80_`;
- FP-88: `wpep006t88_`.

A fixed sentinel `wave1t-preserve-v1` is required throughout each formal path. WordPress outbound HTTP is denied and recorded; accepted fixture evidence reports **0 attempts**.

## 5. Formal fixture results

### FP-77 — PASS

Compatible F0/P0 was first settled to the full current migration set. A later fresh matching-schema boot then proved:

- no migration marker was reinserted;
- normalized WPE-owned schema/migration state was unchanged;
- compatibility remained `compatible`;
- sentinel remained intact;
- outbound WordPress HTTP attempts remained zero.

Minimum normalized snapshot stayed:

`6e6c69077e1be4ac04c2869b93c3a86ff37e043ea986f5851cbc59dfc16d7147`

Reference normalized snapshot stayed:

`c9ee9a596979b8ff10304e3c6c16afc4c966514c8ffcf786ebb397d559449fdb`

The only actual DDL statement observed on the clean settled request was the generic state-store readiness check:

`CREATE TABLE IF NOT EXISTS ...wpe_migrations...`

It did not insert a migration marker and did not change the normalized schema hash. `SHOW CREATE TABLE` inspection queries are evidence reads, not DDL.

Terminal decision: **PASS**.

### FP-78 — PASS

Starting from an empty WPE migration/schema state with only Free active, the first fresh Free boot applied exactly:

1. `006.create-compiled-registration-atomic-store`;
2. `007.create-definition-persistence`;
3. `008.create-audit-ptd-store`.

No 220/221 migration or Pro Custom Tables store appeared.

Minimum before/after snapshot hashes:

- before: `1a5f33eb141c6b74ab8e4e50f373fe8be69556fb28c73f97fefae332f007e01e`;
- after: `e751b2b4f289ac125a8da26d3227ee82d1eb8a5c4a3c07b640d61d693adb807a`.

Reference before/after snapshot hashes:

- before: `1a5f33eb141c6b74ab8e4e50f373fe8be69556fb28c73f97fefae332f007e01e`;
- after: `03e147d6b81cf262e72c090ceb7b30766499cac8c47ae8a8fb9e2def53b4b175`.

Terminal decision: **PASS**.

### FP-79 — PASS

Free was first settled with 006/007/008 and Pro remained inactive. Pro activation itself produced no WPE schema/migration mutation. On the next fresh compatible request, the runtime compatibility preflight resolved `compatible` with `premium_migrations_allowed=true`, and exactly these migrations were added:

1. `220.custom_tables_migration_runs_v1`;
2. `221.custom_tables_migration_execution_confirmations_v1`.

The fixture used only test-local `pro_active` entitlement and no remote provider.

Minimum Free-settled / Pro-settled hashes:

- before: `11d6db9a4ede18692d5c6d94cf1c63525b7f86c11dba4e7e9618f236fad5793c`;
- after: `1efd4ed7cac785f976a44889bbc3af8b9065a99cbe03f189db6cffa1203e8f6f`.

Reference Free-settled / Pro-settled hashes:

- before: `f787fa14be05508ac9bafeeac7f4455b0e241445834234cc6adc7a99f0f75251`;
- after: `9c045a7ba8efca29deee4ab158331596bce6611ee8de6b64007865b058e9ac27`.

Terminal decision: **PASS**.

### FP-80 — PASS

The exact F2/P0 incompatible control was run with local `pro_active` requested.

Both cells resolved:

- compatibility state: `free_version_too_new`;
- dimension: `free_version`;
- reason: `free_version_above_supported_maximum`;
- `premium_boot_allowed=false`;
- `premium_migrations_allowed=false`.

Only Free 006/007/008 ran. Pro 220/221 remained absent, Pro Custom Tables stores remained absent, and the premium module set remained empty.

Minimum before/after hashes:

- before: `7f35539e1ebf3e97b97028e0c19788cbcd319fe4447cff32fa17322145ed9225`;
- after: `c1d9416914f43e1dd9f31662cd6fd8530107aedb65cf164710928c06fb78fb70`.

Reference before/after hashes:

- before: `7f35539e1ebf3e97b97028e0c19788cbcd319fe4447cff32fa17322145ed9225`;
- after: `143486a7b4e3ac61a6e63fe88c0ba1e8af8473b7d4caab7dcd82102e7a283bb4`.

This is bounded evidence that local entitlement does not substitute for compatibility admission.

Terminal decision: **PASS**.

### FP-88 — PASS

Starting from an empty WPE state with compatible F0/P0 active, runtime SQL evidence recorded this exact migration marker order in both cells:

1. `006.create-compiled-registration-atomic-store`;
2. `007.create-definition-persistence`;
3. `008.create-audit-ptd-store`;
4. `220.custom_tables_migration_runs_v1`;
5. `221.custom_tables_migration_execution_confirmations_v1`.

Therefore the current Free-before-Pro migration ordering is observed at runtime and is not inferred only from sequence constants.

Minimum before/after hashes:

- before: `0f3800b36b7dc5be900a5293b27341f20d768aac87b97fdb1ce4857ccdcf3586`;
- after: `def09246b75b453732ba8f9eb983940bd57fdce36715b64bec240468cfa3fb71`.

Reference before/after hashes:

- before: `0f3800b36b7dc5be900a5293b27341f20d768aac87b97fdb1ce4857ccdcf3586`;
- after: `61db7f2670a38ca0d8c42dd4eab71bec12738b9d50900c5fd83db3eafb252139`.

Terminal decision: **PASS**.

## 6. Immutable artifacts

Accepted run **35531995346** produced:

| Artifact | ID | Archive digest |
| --- | ---: | --- |
| candidate graph | **10612265052** | `sha256:5beeb0d9da79006a3c4f8e4f9a52dc6457beb069ec74be8dfd77fb7c47347106` |
| minimum runtime | **10611831278** | `sha256:f021372fa9f5cc79e827960273d744630b557bb29c823cac13ebbee7d3c31a6f` |
| reference runtime | **10611274191** | `sha256:d89ff1124e40c7638564e67cd007053052d453ab1ca43c385b52a85eb97a4fb6` |
| terminal aggregate | **10612295065** | `sha256:4665315bad7223597fc16a26306de4b04d3d080be6c9aea3d6270e16835c9987` |

All accepted runtime artifacts are bound to exact head `e3b2589ebd3234b4cecfb4c847713e17f4da0fa2`.

## 7. Terminal Wave 1T decision

Formal fixture results:

- FP-77: **PASS**;
- FP-78: **PASS**;
- FP-79: **PASS**;
- FP-80: **PASS**;
- FP-88: **PASS**.

If and only if this runtime/evidence PR merges and a separate Governance-only shared-truth closeout accepts the evidence, P-006 accounting becomes:

**144 documented / 57 executed / 57 PASS / 0 FAIL / 0 INCONCLUSIVE**.

Until that separate closeout lands, the repository shared-truth counters remain **144 / 52 / 52 / 0 / 0**.

`GOV-P001-CF-TEMP-018` remains active only for this one tranche until terminal shared-truth closeout consumes it.

No bounded PASS in this wave certifies a Free/Pro pair, generic migrations, the P-006 runtime, permanent P-001/CF, updater/TUF, production readiness, GA/release authority, or ADR-0010.
