# P-006 Wave 1U — FP-86 / FP-89 / FP-90 / FP-94 Minimum-Cell Evidence

Issue: #1425 (child execution tranche of #1102)
Owner direction / authorization: `GOV-P001-CF-TEMP-019` (one-time, this tranche only)
Workflow: `.github/workflows/p006-wave1u-minimum-cell.yml` — **P-006 Wave 1U FP-86/89/90/94 Minimum Cell**

Classification: **FORMAL BOUNDED P-006 EVIDENCE / NON-CERTIFYING**

Status: **FP-86 / FP-89 / FP-94 PASS (run 39218761905); FP-90 NOT EXECUTED** — the accepted B6d snapshot artifact has expired. Accounting: **144 documented / 60 executed / 60 PASS / 0 FAIL / 0 INCONCLUSIVE / 0 certified Free/Pro pairs / 0 runtime certifications**.

## 1. Scope

Wave 1U formally executes exactly, on the minimum disposable cell only (WordPress **6.9** / PHP **8.2** / MySQL **8.4**):

- **FP-86** — current idempotent migration `220.custom_tables_migration_runs_v1` with a fail-once marker write: the apply completes, the marker write fails once, a fresh runner re-applies 220 idempotently (schema unchanged, marker persisted exactly once) and a settled third run applies nothing;
- **FP-89** — the isolated API-incompatible Pro candidate (`P_API`) paired with canonical `F0` is rejected (`platform_api_too_old`, dimension `platform_api`) before premium boot and before any Pro migration, with no premium module registered;
- **FP-90** — the exact accepted older-state snapshot `sha256:24178a1289fb5929eda850f488d932c94bbbddc5936a82cc26cb83979823b058` is restored, its semantic identity re-proved, canonical F0/P0 compatibility is proved **before** pending migrations, and only then are the current non-destructive 220/221 migrations applied (additive `CREATE TABLE IF NOT EXISTS` only, pre-existing schema, migration rows and sentinel preserved, settled rerun applies nothing);
- **FP-94** — raw license-like, Vault-plaintext and private-user-data canaries seeded into the disposable state (plus the accepted B6c redaction capture) are absent from every surface captured during the formal FP-86/89 scenarios in the same `minimum` job (evidence JSON, stdout/stderr, PHP error log, network and SQL logs).

No reference cell, other fixture, or other runtime cell is executed.

## 2. Composed accepted prerequisites

| Prerequisite | Accepted source head | Accepted run | Reused as |
| --- | --- | --- | --- |
| B6a FP-89 Platform API candidate | `bf7f5dc9dd73d13143dcc19d5c5743f1f5034ef8` | 35539119381 | `tools/p006/p006-b6a-fp89-platform-api-candidate.php` derives F0/P0/P_API twice byte-identically; the canonical pair is built from the accepted B6a source head |
| B6b FP-86 fail-once marker | `dcc15826df5989df23f3f1e588371af4038ad7d8` | 35539744399 | `tools/p006/p006-b6b-*` executed in the runtime cell; Wave 1U binds its evidence as FP-86 |
| B6c FP-94 diagnostics/redaction | `46ab31e6ab6169d05c8a1c936970c2213aca1146` | 35540536959 | `tools/p006/p006-b6c-fp94-diagnostics-redaction.php` executed in the runtime cell; combined with the formal-scenario canary scan |
| B6d FP-90 disposable DB snapshot | `6e7cef451494a5403991755b599ece560177675c` | 35542007281 | accepted artifact `p006-b6d-fp90-prerequisite-35542007281` (older-state SQL + `before.json`) is downloaded, hash-pinned and restored; B6d `verify-restored` and `compatibility` modes run before 220/221 |

## 3. Immutable candidate identity (asserted, not re-derived)

| Node | ZIP SHA-256 | Payload-tree SHA-256 |
| --- | --- | --- |
| F0 | `2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80` | `0698d1a772704bcf44bea1eefae0c490212d6bb6ee214a4f6a5c33db1bf998b9` |
| P0 | `bd74077f22c7268765519ac2d4c64dd785ba59c9eee68f5a5f1b4d8fd8de0467` | `fbaa10957eeb482bd248ef2833b021202068ce7f420fe2d78f35b0e522b0bfd0` |
| P_API | `5a2634a0d3c72ff7ede89545837c3fe2773aefad90a3fed4b060485c55645af9` | `8985a20a68c7aafeee2d758845b26bddfe34ba83b5141ea335fad174acd52a69` |

Pair identities:

- canonical F0/P0: `28e96f5207d3dc195bbf1b1a883ea7dcea5a314580661de5400e4e299dbd5211`;
- isolated F0/P_API: `9447c78174b7702879f318339c60942ebc20be9a477df78f9dbd2b734705d841`.

Any drift in these values fails the run; it is never re-baselined inside this tranche.

## 4. Workflow shape and budgets

| Job | Budget | Purpose |
| --- | --- | --- |
| `change-gate` | ≤ 3 min | latest-change gate on PR `synchronize` |
| `build-candidates` | ≤ 15 min | build canonical pair twice from accepted B6a head, derive B6a graph twice, assert accepted identities, upload `p006-wave1u-candidates-<run_id>` |
| `minimum` | ≤ 18 min | disposable WP 6.9 / PHP 8.2 / MySQL 8.4 execution of FP-86, FP-89, FP-94 (FP-94 scans the surfaces captured in this job) and aggregate over those three (`WPE_P006_AGGREGATE_FIXTURES="FP-86 FP-89 FP-94"`); no B6d download; upload `p006-wave1u-minimum-<run_id>` |
| `fp90` | ≤ 18 min | separate disposable WP 6.9 / PHP 8.2 / MySQL 8.4 cell; downloads the accepted B6d artifact, keeps the exact-hash requirement `sha256:24178a12…b058` unchanged, runs FP-90 and a FP-90-only aggregate; upload `p006-wave1u-fp90-<run_id>`. If the download fails the job fails and FP-90 stays not-executed |
| `terminal` | ≤ 5 min | terminal marker `p006-wave1u-terminal-<run_id>` recording per-fixture outcomes, partial result (`fp86_fp89_fp94_job_result`, `fp90_job_result`) and observed accounting; status PASS only if every job succeeds; the job still fails if any job failed |

The workflow is manual-dispatch only (terminal-workflow contract), matching Wave 1T and the B6 workflows. Actions are pinned to the same commit SHAs as Wave 1T.

Evidence tool: `tools/p006/p006-wave1u-minimum-cell.php` (modes `fp89-install`, `fp89-activate`, `fp89`, `fp86`, `fp90`, `fp94`, `aggregate`). Disposable WordPress setup: `tools/p006/p006-wave1u-wordpress-setup.sh`.

## 5. Accounting

Before execution: **144 documented / 57 executed / 57 PASS / 0 FAIL / 0 INCONCLUSIVE / 0 certified Free/Pro pairs / 0 runtime certifications**.

Observed accounting is computed per job, never inferred: executed = 57, + 3 if the `minimum` job (FP-86/89/94) passed, + 1 if the `fp90` job passed.

- `minimum` PASS only: **144 documented / 60 executed / 60 PASS / 0 FAIL / 0 INCONCLUSIVE**, FP-90 not-executed;
- both PASS (terminal PASS): **144 documented / 61 executed / 61 PASS / 0 FAIL / 0 INCONCLUSIVE**.

Until an exact-head run records it, accounting is unchanged.

## 6. Boundaries

This tranche performs no product runtime or DI change, no provider/license/billing/remote entitlement call, no live or production data mutation, no destructive or irreversible migration, no other fixture or cell, no deploy/release, no pair/runtime/migration certification, no permanent P-001/CF certification, and no ADR-0010 promotion (remains **Proposed**). `GOV-P001-CF-TEMP-019` is consumed by this tranche and is not reusable.

## 7. Run history

### Run 39183971075 (first dispatch) — 0 executed

- failed at `Download accepted B6d older-state snapshot`: the accepted B6d artifact `p006-b6d-fp90-prerequisite-35542007281` from run 35542007281 had expired;
- every scenario step was skipped; FP-86, FP-89, FP-90 and FP-94 produced **no evidence** (0 executed, 0 PASS, 0 FAIL); accounting stays 144 / 57 / 57;
- corrective decision: split the runtime so FP-86/89/94 no longer depend on the FP-90 prerequisite (`minimum` job without B6d download, `fp90` job keeping the exact-hash snapshot requirement unchanged). The snapshot hash is not re-baselined; FP-90 remains not-executed until the exact accepted snapshot is available.

### Run 39216892132 — 0 executed

- `minimum` job stopped before any fixture: Wave 1U pointed the B6b/B6d setups at `/tmp/wordpress-b6b` / `/tmp/wordpress-b6d`, but both extract WordPress to `/tmp/wordpress`, so their `wp-load.php` check failed silently;
- fixed by PR #1441 (both jobs use `/tmp/wordpress`; failed captured steps print their name). Reproduced locally; the corrected B6b harness returns PASS.

## 8. Accepted run — 39218761905

- workflow run: [39218761905](https://github.com/Vertex-Systems-Network/wpessential/actions/runs/39218761905) (`workflow_dispatch` on `main`);
- exact source head: `7b0013fa590c7d8007eb2e3fcf20cb1bae3fc5c6`;
- candidate build: success (accepted B6a canonical F0/P0/P-API graph reproduced twice, byte-identical);
- `minimum` job (FP-86/89/94, WP 6.9 / PHP 8.2 / MySQL 8.4): **success**; aggregate `PASS`, raw-canary scan clean; artifact `p006-wave1u-minimum-39218761905`;
- `fp90` job: **failure before execution** (`Artifact not found for name: p006-b6d-fp90-prerequisite-35542007281`); artifact `p006-wave1u-fp90-39218761905`;
- terminal marker `p006-wave1u-terminal-39218761905`: `fixture_outcomes` FP-86 PASS, FP-89 PASS, FP-94 PASS, FP-90 NOT_EXECUTED; `accounting_observed` 144 / 60 / 60 / 0 / 0; terminal status FAIL solely because FP-90 did not run.

### Result

| Fixture | Outcome |
|---|---|
| FP-86 | PASS |
| FP-89 | PASS |
| FP-94 | PASS |
| FP-90 | NOT EXECUTED |

P-006 accounting: **144 documented / 60 executed / 60 PASS / 0 FAIL / 0 INCONCLUSIVE / 0 certified Free/Pro pairs / 0 runtime certifications**.

FP-90 needs a fresh B6d prerequisite run with a new accepted snapshot identity; the original hash cannot be regenerated because the dump embeds install timestamps. No pair, runtime, migration or Backup/Restore certification follows; ADR-0010 remains Proposed.
