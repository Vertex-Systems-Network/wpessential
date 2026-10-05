# AI Durable Last Checkpoint

## 2026-10-05 — shared AssetRegistry prerequisite reconciliation active

### Exact terminal repository truth

- Exact current main: `25f098a170c42d30f829d196452189d9f5b71763`.
- Issue #1285 / PR #1286 — bounded Forms Set-Enabled Mutating Ability V1 — terminal PASS.
- Exact #1286 head: `c45e623079824b7e75e4a05617f1439a6971ea2f`.
- Governance `36932601887` PASS.
- PHP Quality `36932601852` PASS.
- Distributable `36932601809` PASS.
- Architecture `36932601669` PASS.
- Platform Compatibility `36932601737` PASS.
- RB-0082 terminal PASS.
- RB-0073 remains historical FAIL.

### Repository-wide security blocker

Issue #1289 remains `BLOCKED_UPSTREAM`.

Fresh 2026-10-05 public evidence still reports:
- `braces` latest published version: 3.0.3;
- affected: <=3.0.3;
- patched versions: none;
- upstream fix PR remains open/unmerged.

Architecture high-severity advisory policy is not waived or lowered.

### Prepared Dashboard stack

#### #1287 / PR #1288 / RB-0083
Input-Aware Authorization contract:
- contract prepared;
- feature Governance PASS;
- Architecture blocked by #1289;
- no merge/PASS promotion.

#### #1291 / PR #1298 / RB-0084
Input-Aware Authorization implementation:
- exact prepared head `0111346896b5092abd65a16939bf2fdb15d20cc9`;
- Governance/PHP Quality/Distributable PASS;
- Platform Compatibility 10/10 PASS;
- Architecture fails only at #1289;
- 0 review threads;
- PREPARED_NOT_MERGEABLE.

#### #1296 / PR #1299 / RB-0085
Trusted form_action UI + confirmation contract:
- exact six-file stacked contract;
- Governance PASS;
- Architecture blocked only by #1289;
- PREPARED_NOT_MERGEABLE.

#### #1300 / PR #1301 / RB-0086
Trusted form_action presentation + confirmation preflight:
- exact prepared head `eef275ceedd0ca5241a489d188d350c459640771`;
- 21/21 authorized files;
- 0 behind stacked base;
- 0 unresolved review threads;
- Governance `37306315812` PASS;
- Distributable `37306315904` PASS;
- Platform Compatibility `37306315830` PASS 10/10;
- Browser E2E Accessibility `37306315836` PASS;
- Architecture `37306315833` fails only at #1289 after JS lint/Stylelint/TypeScript/admin build PASS;
- zero AbilityRegistry execute/direct owner handle/Forms mutation in the preflight path.

### Plan-drift finding — shared assets

Older #1292/#1294 planning was still open and remains architecturally relevant.

ADR-0150 says the Platform architecture requires one shared Asset Registry / scoped loader for:
- asset identity and ownership;
- dependency resolution;
- route/screen scoping;
- trusted build-manifest mapping.

#1292 selected:

`trusted build manifest → AssetRegistry ownership → scoped WordPress loader`.

Prepared #1301 currently directly resolves `AdminAssetManifest('main')` inside `NativeWordPressDashboardWidgetEnvironment` and enqueues the bundle through a Dashboard-module environment seam.

That is safe as a prototype but bypasses the selected shared AssetRegistry path.

Therefore #1301 is not terminally merge-ready even after #1289 clears until asset-runtime reconciliation is complete.

### #1294 / PR #1305 — active shared asset contract

Status: **PREPARED_NOT_MERGEABLE / contract only**.

Exact main base:
`25f098a170c42d30f829d196452189d9f5b71763`.

V1 design:
- preserve existing `AssetDescriptor` semantics;
- add a Platform-owned trusted logical-handle → build-entry mapping registry;
- reuse `AdminAssetManifest` as trusted generated-file resolver;
- add one shared scoped WordPress loader;
- publish stable services:
  - `platform.assets`;
  - `platform.assets.build-entries`;
  - `platform.assets.manifest`;
  - `platform.assets.wordpress`.
- first consumer handle: `wpe-dashboard-form-action`;
- owner Surface 10;
- exact site/network Dashboard routes only;
- trusted `main` script entry;
- no style entry in V1 unless separately proved necessary;
- no Definition-selected path/URL;
- no remote fallback;
- no global wp-admin loading;
- no action execution.

RB-0088 is the contract gate.

### #1297 / PR #1304 / RB-0087

Final bounded form_action execution contract is prepared but remains downstream.

It freezes:
- Set-Enabled-only owner allowlist;
- exact pre-execution audit order;
- exactly one later `AbilityRegistry::execute()`;
- no direct owner handle;
- exact seven-field result adapter;
- ambiguity/no-retry semantics.

No execution runtime is authorized.

### Corrected dependency order

1. resolve #1289 without weakening Architecture;
2. #1288 / RB-0083;
3. #1298 / RB-0084;
4. #1305 / RB-0088 shared AssetRegistry contract;
5. bounded shared AssetRegistry runtime implementation;
6. reconcile #1301 to consume shared loader;
7. #1299/#1301 terminal contract/preflight gates as appropriate after reconciliation;
8. #1304 / RB-0087 final execution contract;
9. separately authorized bounded execution implementation.

### Recovery order

1. read `.ai/state/CURRENT-STATE.yaml`;
2. read this checkpoint;
3. resolve exact main and open PRs #1288, #1298, #1299, #1301, #1304, #1305;
4. read #1289;
5. read #1292 and #1294 asset prerequisite truth;
6. read queue + runner benchmark;
7. do not merge any affected branch while Architecture is red;
8. do not terminally merge #1301 with module-local asset enqueue;
9. do not implement final Dashboard execution runtime before all predecessor gates are terminal.
