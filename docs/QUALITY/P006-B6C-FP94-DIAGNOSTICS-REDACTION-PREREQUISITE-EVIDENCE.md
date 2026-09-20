# P-006 B6c — FP-94 Migration Diagnostics / Redaction Prerequisite Evidence

Issue: #1097

Runtime/evidence PR: #1098

Authorization: `GOV-P006-B6C-FP94-DIAGNOSTICS-REDACTION-001`

Classification: **TEST-ONLY DIAGNOSTICS/REDACTION PREREQUISITE / FP-94 NOT FORMALLY EXECUTED / NON-CERTIFYING**

Accepted prerequisite source head: `46ab31e6ab6169d05c8a1c936970c2213aca1146`

## 1. Purpose and boundary

B6c validates only the deterministic capture/redaction prerequisite required before a later separately authorized FP-94 formal fixture.

It does not formally execute FP-94. It adds no product logger, migration hook, provider integration, WordPress/MySQL runtime, or product source behavior.

Formal P-006 accounting remains:

**144 documented / 57 executed / 57 PASS / 0 FAIL / 0 INCONCLUSIVE / 0 certified Free/Pro pairs / 0 runtime certifications**.

## 2. Current migration-adjacent output surface

Exact-source audit scanned **65 PHP files** under:

- `frameworks/Platform/Database/Migrations`;
- `frameworks/Modules/CustomTables/Migration`.

Sorted source-file-list SHA-256:

`4a20256d7c47ad60e661d2a46f6b1b5103ed77ec01b330ccc61b55a4955cdc3f`

The prerequisite asserts these dedicated logging/hook patterns are absent from that current migration surface:

- `error_log(`;
- `trigger_error(`;
- `do_action(`;
- `apply_filters(`.

Therefore the finite prerequisite capture surface is bounded to:

1. real `MigrationRunner::runPending()` returned IDs;
2. the current target-migration exception message;
3. the current persistent marker-store exception message;
4. PHP error-log capture;
5. process stderr capture;
6. `SensitiveValue` JSON/debug representations;
7. generated prerequisite evidence JSON.

No product observability surface was invented.

## 3. Exact-head verification

Accepted workflow:

- workflow: **P-006 B6c FP-94 Diagnostics Redaction Prerequisite**;
- run id: **35540536959**;
- source head: `46ab31e6ab6169d05c8a1c936970c2213aca1146`;
- change gate: **PASS**;
- deterministic capture executed twice byte-identically: **PASS**.

Exact-head Governance Gate:

- run id: **35540536932**;
- result: **PASS**.

Evidence/shared-truth-only finalization:

- finalization head: `284e4f412257d561fbbb228342a2a758c5a56987`;
- Governance run: **35540781136 — PASS**;
- B6c change-gate run: **35540781182 — PASS**;
- heavy `Validate FP-94 diagnostics/redaction capture` job: **SKIPPED**.

This proves evidence/shared-truth synchronization does not rerun or replace the accepted immutable prerequisite evidence.

The accepted implementation diff modifies only prerequisite workflow/tooling and the serialized queue claim. Product runtime source is unchanged.

## 4. Deterministic canary design

Three raw in-memory canaries are used:

- license/provider-like token;
- Vault plaintext;
- unrelated user/private data.

Their raw values are not persisted into evidence. The artifact contains only these SHA-256 fingerprints:

- license-like: `47c3ebee8cc4eefb980499223b55d3545cc6f5ca4b0268e179ef54d56433f5eb`;
- Vault plaintext: `fc2c5857908bba6ecd649df96305dd0a153399967585ba06c275383f5ae751f2`;
- private user data: `5934b6c49a0f2737d3929e3f4718dc9b8b4c433b7b8e7e03ac0cc4d0f902f0b9`.

An independent scan of the downloaded immutable artifact found **NO RAW CANARIES** in any artifact file.

## 5. Captured product outputs

### Successful runner status

Real `MigrationRunner` + `MigrationRegistry` + `InMemoryMigrationStateStore` returned exactly:

`990.p006_b6c_status_probe`

Captured runner-output surface:

- bytes: **29**;
- SHA-256: `52c900dec8ec1a9837450849b119891084047c48dd378808a9672cf4d227913d`;
- raw canaries present: **false**.

### Existing target migration error

The current `CreateMigrationRunStoreMigration` error path produced exactly:

`Unable to initialize Custom Tables Migration Run store.`

Surface SHA-256:

`e441d6b813dff436162e4f551feb5b042a2da87a9b95f7af956dc0fdcac35cd2`

Raw canaries present: **false**.

### Existing marker-store error

The current `WpdbMigrationStateStore` verification failure produced exactly:

`Migration state could not be persisted.`

Surface SHA-256:

`3cc81fa587fcbfe2dccf1f1207a62571d9f2c8b0114318caf10632b205e7acb0`

Raw canaries present: **false**.

## 6. SensitiveValue redaction

`SensitiveValue` is instantiated with the Vault plaintext canary.

Observed JSON representation:

`[REDACTED]`

Debug representation:

- contains `[REDACTED]`: **true**;
- Vault plaintext absent: **true**;
- debug SHA-256:
  `1a80ac03d59b56cd31b1635689d8568b36d84fcfbadb4902cfc7e503cad6f68f`.

Sensitive JSON surface SHA-256:

`fca92358a25a3418b48b418da3d16a5a170136fefcafc47495b432a98cd19093`

## 7. Error-log and stderr capture

Accepted PHP runtime:

- PHP **8.2.33**.

Captured PHP error log:

- bytes: **0**;
- SHA-256:
  `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855`;
- raw canaries present: **false**.

Captured process stderr:

- bytes: **0**;
- SHA-256:
  `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855`;
- raw canaries present: **false**.

The prerequisite harness is executed twice from the same exact source. `evidence.json`, stdout capture, stderr capture, and PHP error-log capture compare byte-identically.

## 8. Immutable artifact

Accepted artifact:

- artifact id: **10614821331**;
- artifact name: `p006-b6c-fp94-prerequisite-35540536959`;
- archive digest:
  `sha256:0bdf200c404e9db5d91fb60c937603ef7a1d07945fec79811074e4a8dceb822a`.

Artifact contents:

- `evidence.json`;
- `stdout.json`;
- `stderr.log`;
- `php-error.log`.

Downloaded-artifact raw-canary scan: **PASS / no raw canaries found**.

## 9. Security and scope invariants

The prerequisite proves:

- raw canaries absent from every captured migration-adjacent surface;
- raw canaries absent from final evidence JSON;
- only canary fingerprints are persisted;
- product logging added/changed: **false**;
- product runtime source modified: **false**;
- WordPress/MySQL runtime used: **false**;
- provider/network runtime used: **false**;
- runtime grant created: **false**;
- formal fixture executed: **false**;
- fixture accounting changed: **false**.

Workflow uses pinned Action revisions, `contents: read`, exact-head checkout, no repository credential persistence, and immutable artifact publication.

## 10. Terminal prerequisite decision

**B6c prerequisite: PASS / MERGE-READY.**

The repository now has the bounded diagnostics/redaction capture prerequisite required for later separately authorized FP-94 formal execution.

**FP-94 remains NOT FORMALLY EXECUTED.**

No P-006 counter, product logging contract, pair/runtime/migration certification, permanent P-001/CF status, ADR-0010 state, deployment or release authority changes.
