# AI Durable Last Checkpoint

## 2026-10-05 — minimal admin toolchain security migration active

### Exact terminal main truth

- Exact current main anchor: `25f098a170c42d30f829d196452189d9f5b71763`.
- Issue #1285 / PR #1286 — bounded Forms Set-Enabled Mutating Ability V1 — terminal PASS:
  - exact head `c45e623079824b7e75e4a05617f1439a6971ea2f`;
  - Governance `36932601887` PASS;
  - PHP Quality `36932601852` PASS;
  - Distributable `36932601809` PASS;
  - Architecture `36932601669` PASS;
  - Platform Compatibility `36932601737` PASS;
  - zero unresolved review blockers;
  - zero behind;
  - merge `25f098a170c42d30f829d196452189d9f5b71763`;
  - verdict `PASS_BOUNDED_FORMS_WORKFLOWS_SET_ENABLED_ABILITY_V1`.
- RB-0082 is terminal PASS.
- RB-0073 remains historical FAIL and is not rewritten.

### Repository security migration — #1289 / #1310 / PR #1311

Upstream `braces` remediation is no longer the only path.

Issue #1308 / PR #1309 performed research only and closed without merge after deterministic workflow run `37313892338` proved a maintained minimal admin dependency graph exists with:
- 62 package-lock entries;
- dev vulnerabilities: 0;
- high: 0;
- critical: 0;
- distributable vulnerabilities: 0;
- `braces`, `micromatch`, `fast-glob`, `stylelint`, `webpack-dev-server`, `@wordpress/scripts`: absent.

Issue #1310 / PR #1311 implements that migration.

Last product/toolchain head before shared-truth reconciliation:

`c1f9d81177031d997b490b2be64d47c863121cda`

Product/toolchain scope before shared truth:
1. `package.json`;
2. `package-lock.json`;
3. `biome.json`;
4. `tools/admin/admin-toolchain.mjs`;
5. `.github/workflows/security-lockfile-refresh.yml`;
6. `.github/workflows/architecture-guards.yml`;
7. `admin-ui/src/columns-runtime.ts` — two CI-proven ES2022 own-property compatibility edits only;
8. `admin-ui/src/taxonomy-visibility.ts` — one CI-proven expression-body callback compatibility edit only.

Exact minimal graph:
- `@biomejs/biome 2.5.15`;
- `esbuild 0.28.2`;
- `sass 1.105.1`;
- `typescript -> @typescript/typescript6 6.0.2`;
- no overrides;
- no runtime npm dependencies.

Security Lockfile Refresh `37315744695` — PASS:
- artifact `11346929180`;
- artifact digest `sha256:4210c97da60e55f848f2baf7ef9eacd3bd3043fdaab8c5d556283c893d46dfec`;
- committed and reproduced package-lock SHA256 both `88e943bde327ec4f88c034c75a877defca8e1367052c654791e21622dd2b0c0b`;
- reproducibility diff 0 bytes;
- dev/distributable vulnerabilities 0;
- affected legacy toolchain chain absent.

Architecture Guards `37315744449` — PASS:
- Node/package manifest validation;
- dev + distributable npm advisory gates;
- Biome JS/TS lint;
- SCSS validation;
- TypeScript strict check;
- deterministic five-entry admin build;
- repeated build byte identity;
- Composer audit;
- architecture/engineering contracts;
- PHP syntax;
- PHPCS;
- PHPStan;
- PHPUnit;
- diagnostic smoke;
- MySQL registration/persistence;
- real WordPress AJAX nonce policy;
- Action Scheduler coexistence;
- durable JobService persistence/lease;
- tracked source clean.

Distributable Package `37315744645` — PASS:
- five production admin asset triplets;
- optional query triplet fail-closed proof;
- independent Free + Pro builds;
- byte-for-byte rebuild determinism;
- compatibility bootstrap;
- ZIP integrity/package boundaries.

Browser E2E Accessibility `37315744670` remains a required merge gate and was still running when this shared-truth reconciliation was prepared.

RB-0087 is the security migration merge gate. It must not promote PASS until the final shared-truth head is green, zero behind, zero review blockers, and expected-head merged.

### Downstream prepared Dashboard chain

- #1287 / PR #1288 / RB-0083 — Input-Aware Authorization Contract V1: open and security/main-reconciliation blocked.
- #1291 / PR #1298 / RB-0084 — bounded Input-Aware Authorization implementation: PREPARED_NOT_MERGEABLE.
- #1296 / PR #1299 / RB-0085 — trusted form_action UI + confirmation contract: PREPARED_NOT_MERGEABLE.
- #1300 / PR #1301 / RB-0086 — trusted form_action UI + confirmation preflight implementation: PREPARED_NOT_MERGEABLE.
- #1297 final execution remains implementation-forbidden until predecessors are terminal.

After #1311 terminal merge:
1. close #1289;
2. update/rebase #1288 to fresh main;
3. rerun RB-0083;
4. reconcile/merge #1298 / RB-0084;
5. reconcile/merge #1299 / RB-0085;
6. reconcile/merge #1301 / RB-0086;
7. only then freeze/claim #1297 execution implementation.

### Recovery order

1. Read `.ai/state/CURRENT-STATE.yaml`.
2. Read this checkpoint.
3. Resolve exact current main and PR #1311.
4. Re-read #1289 and #1310.
5. Read `config/coordination/agent-work-queue.json`.
6. Read `config/coordination/runner-benchmark.json`.
7. Do not merge downstream Dashboard prepared PRs before security/main reconciliation.
8. Do not implement #1297 before RB-0083 through RB-0086 are terminal.

Repository/runtime evidence outranks compact state.
