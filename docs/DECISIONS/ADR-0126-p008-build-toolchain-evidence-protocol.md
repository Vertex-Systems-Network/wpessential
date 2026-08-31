# ADR-0126 — P-008 Build Toolchain Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Context

ADR-0012 proposes a single WordPress-aware build toolchain, evaluating stable `@wordpress/build` capabilities first, `@wordpress/scripts` second and Vite only for a documented unmet requirement. Current authoritative repository branches do not contain an active root package/build manifest, so earlier Mix/Vite references are historical/unverified rather than current implementation truth.

The generic P-008 spike did not fully freeze repository-baseline integrity, minimum/current WordPress package mapping, React/JSX duplicate scans, asset metadata, route isolation, production ZIP safety, reproducibility, cross-platform failures or a disciplined conditional Vite admission gate.

## Decision

Accept `docs/QUALITY/P008-BUILD-TOOLCHAIN-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical bounded future P-008 evidence contract.

It defines **BT-01…BT-112** covering:
- execution-time official version refresh and repository baseline inventory;
- identical controlled fixture/candidate versioning and experimental-feature inventory;
- package-manager/lockfile/dependency/license governance;
- React/ReactDOM/JSX and `@wordpress/*` externalization;
- generated asset/dependency/PHP registration metadata;
- multiple entries/shared/lazy chunks;
- exact-route enqueue and unrelated-screen asset absence;
- scoped CSS, LTR/RTL and style output;
- localization extraction/runtime registration;
- PHP/Composer/plugin-header/P-001 metadata consistency;
- production ZIP contents, Node-free runtime and asset completeness;
- build reproducibility/source-map/checksum policy;
- cold/warm build metrics, configuration surface and maintenance burden;
- cross-platform/adversarial failure behavior;
- `@wordpress/build` vs `@wordpress/scripts` scorecards;
- Vite evaluation only after an explicit unmet-requirement register;
- one canonical build-tool decision and P-007 CI handoff.

## Preserved architectural direction

This ADR does not accept ADR-0012 or select a tool.

Preserved rules:
- stable `@wordpress/build` capabilities are evaluated first;
- `@wordpress/scripts` receives the same fixture comparison;
- Vite requires a documented material unmet requirement before evaluation;
- Laravel Mix is not reopened without new authoritative evidence/requirement;
- current experimental `@wordpress/build` pages/routes/widgets are not WPE foundational dependencies;
- WPE must use WordPress-provided React/runtime without a competing bundled copy;
- package imports/build output must remain compatible with the eventually accepted WordPress minimum;
- PHP never guesses hashed filenames; generated registration/asset metadata is authoritative where applicable;
- module assets load only in required contexts;
- RTL/localization and actual built-ZIP verification are release requirements;
- end-user WordPress installations require no Node/npm runtime;
- only one canonical production frontend build system is selected unless a separate ADR explicitly justifies otherwise.

## Evidence state

At acceptance:
- BT fixtures documented: **112**;
- BT fixtures executed: **0/112**;
- P-008 toolchain certification: **0**;
- ADR-0012: **Proposed**;
- canonical build tool: **not selected**;
- P-002/UI fixtures: **0/104**;
- P-007 CI execution: **0**.

## Selection rule

Tool preference, build speed or developer familiarity cannot override WordPress runtime correctness. A competing React runtime, hidden newer-WP requirement, unverified release artifact, global asset loading, runtime Node requirement, secret-bearing ZIP or dependency on experimental routing/widgets is a stop-the-line failure.

Vite cannot win by comparison unless a material unmet requirement is first documented against both WordPress-native candidates.

## Development gate

This ADR authorizes no `package.json`, lockfile, dependency installation, scaffold, compile/watch/lint/typecheck/test, Composer install, bundle analysis or ZIP generation. Explicit owner development/executable-spike consent under ADR-0014 remains required.
