# WPEssential — Agent Operating Contract

This file is mandatory guidance for every AI/coding/planning agent working in this repository, whether one agent works alone or many agents work in parallel.

The repository is a 56-surface modular WordPress platform. Do not treat modules as independent mini-products. Every business semantic has one canonical owner and every change must preserve the shared ownership, dependency, Policy, storage and execution contracts.

## 1. Authority and required preflight

Before planning, editing, generating code, building an Options Bank, deriving UI, or opening a PR, read the current repository evidence from the exact branch/base you will modify.

Mandatory preflight sources:

1. `AGENTS.md` — this contract.
2. `CONTRIBUTING.md` — engineering and release rules.
3. `config/product/competitor-parity-surfaces.json` — canonical 56-surface registry.
4. `config/product/options-bank-progress.json` — current machine lifecycle truth.
5. `docs/ARCHITECTURE/CROSS-MODULE-OPTION-OWNERSHIP-AND-NO-BYPASS-CONTRACT.md` — semantic ownership and no-bypass law.
6. `docs/ARCHITECTURE/CANONICAL-56-SURFACE-DEPENDENCY-RELATION-MATRIX.md` — dependency direction and forbidden coupling.
7. Relevant surface-specific architecture, product, Options Bank, audit and test files.

Repository evidence and accepted contracts override stale chat summaries, assumptions, previous agent memory, generated notes, and old branch state.

At task start, identify and record internally:

- exact base/main SHA;
- surface ID and key;
- current lifecycle status;
- canonical semantic/storage/execution ownership;
- allowed peer integrations and forbidden coupling;
- files this task is allowed to write;
- files that are shared/integrator-owned.

Do not restart completed work. Audit the existing state first and continue from the latest certified checkpoint.

## 2. Default autonomous module workflow

When asked to continue or start a module, do not require the user to repeatedly enumerate UI tabs, fields, toggles, Options Bank entries or ordinary module structure if repository evidence and research can determine them.

Default workflow:

`Understand → audit existing repository → research current native WordPress → research current market/providers → normalize Options Bank → resolve ownership/duplicates → native audit → market audit → Bank review → derive UX projection → architecture/implementation contract → implement when authorized → test → review → secure → verify → document → commit → checkpoint`

Ask for a product decision only when a genuine unresolved choice cannot be resolved from canonical repository evidence, current platform evidence, market evidence or existing owner contracts.

Never invent completion to avoid a question.

## 3. Options Bank and UI derivation law

The Options Bank is a normalized capability inventory, not a list of controls to render directly.

Required derivation:

`Master Options Bank → semantic/disposition review → UX projection → implementation contract`

A Bank record must be dispositioned before UI implementation. Typical outcomes include:

- user configurable → normal UI control;
- advanced configurable → advanced UI;
- provider configurable → provider/integration UI;
- derived/effective state → diagnostics/preview only, no duplicate authored control;
- runtime/internal → no user-facing control;
- diagnostic → diagnostics/health UI;
- compatibility/migration-only → compatibility/import/migration UI;
- deferred → no current UI/runtime claim;
- rejected unsafe → never expose as a supported capability.

Do not create hundreds of controls simply because hundreds of Bank records exist.

UI grouping, tabs, sections, labels, dependencies, visibility, validation and defaults should be derived from the certified Bank plus canonical Admin IA and ownership contracts. Do not create a second semantic engine merely because a local screen needs a contextual control.

## 4. Multi-agent parallel-work protocol

Parallel work is supported and encouraged, but only under this protocol.

### 4.1 One surface, one primary writer, one branch

- One primary agent owns one surface/task branch at a time.
- Do not let two primary agents write overlapping files for the same surface concurrently.
- A specialist/research sub-agent may assist, but should be read-only or assigned explicitly non-overlapping output files.
- Every worker branch must start from a known exact `main` SHA.

Recommended branch patterns:

- `planning/options-bank-<surface>-<stage>-vN`
- `planning/<surface>-<audit-or-review>-vN`
- `implementation/<surface>-<milestone>`
- `fix/<surface>-<issue>`

### 4.2 Module workers own module-local files

A parallel module worker should normally limit writes to its own surface-specific artifacts, for example:

- `config/product/options-bank/<surface>*.json`;
- `config/product/options-bank-audits/<surface>*.json`;
- surface-specific schemas only when they are not generic/shared;
- `tests/Smoke/<surface>-*.php` or equivalent surface-specific tests;
- surface-specific docs under `docs/PRODUCT/`, `docs/MODULES/`, `docs/ARCHITECTURE/`, or `docs/QUALITY/`;
- surface-specific implementation/runtime/test files when implementation is authorized.

### 4.3 Shared/global files are single-writer integrator territory

Unless the task explicitly designates the agent as the current integrator/coordinator, parallel module workers MUST NOT independently update shared truth/registration files such as:

- `README.md` module progress dashboard;
- `config/product/options-bank-progress.json`;
- global `STATUS.md` or cross-surface status summaries;
- `config/product/competitor-parity-surfaces.json`;
- canonical ownership/dependency registries;
- generic/shared JSON schemas used by multiple surfaces;
- `composer.json` shared smoke-script registration;
- generic smoke runners or global test aggregators;
- shared architecture contracts;
- shared package/release metadata;
- any other file currently being changed by another active agent.

If a module requires a shared-file change, the worker records an **Integration Requirement** in its PR/body or handoff, including the exact change needed. The integrator applies it after reconciling all active branches.

This single-writer rule prevents mechanical merge conflicts and, more importantly, contradictory global truth.

### 4.4 Canonical ownership beats local convenience

A module worker must not create a duplicate engine for a semantic owned elsewhere.

Examples:

- Status owns canonical state transition semantics; Workflow may request/observe transitions but must not privately set protected state.
- Relations owns persistent relation/cardinality/pivot semantics; Fields/Forms/Listings may select or consume relations but must not create hidden parallel relation storage.
- Query owns structured query semantics; Listings/Admin Columns consume query contracts rather than inventing private SQL engines.
- Roles owns role/capability definitions; modules consume Policy/capability contracts rather than inventing local role editors.
- Connections owns HTTP/OAuth/webhook transport policy; peer modules do not implement private transport/retry/signature stacks.

When two agents discover the same semantic, do not duplicate it in both Banks. Resolve the canonical owner from the ownership contract; the non-owner records only a typed reference, integration, compatibility mapping or explicit out-of-surface disposition.

### 4.5 No peer-private access

Parallel modules communicate through approved interfaces, Abilities, Events, Data Source contracts, registries or provider adapters.

Forbidden:

- direct writes into another module's private tables/options/meta as a shortcut;
- importing another module's private implementation classes when a public contract exists;
- hidden fallback engines that silently copy a disabled peer's behavior;
- adding a new peer hard dependency without documenting why a shared interface cannot express the dependency.

### 4.6 Parallel planning versus runtime implementation

Options Bank research, native audit, market audit and documentation can run highly parallel because ownership and dependencies are contract-driven.

Runtime implementation must additionally respect dependency readiness. A consumer may implement against a stable public contract/mock/fixture, but must not assume an uncertified peer's private implementation details.

## 5. Coordinator / integrator responsibilities

When multiple workers are active, one integration lane owns global reconciliation.

The integrator must:

1. track active surface branches and their exact base SHAs;
2. prevent overlapping write ownership;
3. reconcile cross-surface semantic collisions using canonical ownership;
4. apply requested shared-file changes once, not once per worker;
5. recompute Options Bank/global counts from repository truth;
6. update `options-bank-progress.json` and README/status only from verified merged/candidate state;
7. ensure a surface lifecycle promotion is supported by the required audit/review evidence;
8. serialize merges where shared truth or dependency changes could collide;
9. require stale branches to sync with latest main before final merge certification;
10. rerun all applicable exact-head CI after integration.

The integrator must not hide conflicts by force-merging or by deleting one worker's valid evidence without resolving ownership.

## 6. Lifecycle promotion rules

Canonical Options Bank lifecycle:

`UNSEEDED → BANK_SURFACE_SEEDED → NATIVE_AUDITED → MARKET_AUDITED → BANK_REVIEWED`

Rules:

- `BANK_SURFACE_SEEDED` means normalized discovery exists; it does not prove native/market completeness.
- `NATIVE_AUDITED` requires explicit current WordPress/platform dispositions and zero unresolved native items.
- `MARKET_AUDITED` requires evidence-backed provider/specialist dispositions and zero unresolved market items.
- `BANK_REVIEWED` requires native + market + WPE-future review, duplicate/semantic resolution and final consistency gates.
- `BANK_REVIEWED` means ready to feed implementation contracts; it does not mean runtime code is complete, released or production deployed.

A worker may prepare a promotion candidate, but must not claim the promoted status until the required exact-head executable gates pass and the shared machine truth is updated by the authorized integration lane.

## 7. Git, synchronization and merge protocol

### Before work

- branch from an explicit current `main` SHA;
- do not base new work on an old PR branch unless the task explicitly continues it;
- do not overwrite another agent's branch/ref.

### During work

- keep changes surface-scoped;
- create atomic commits/checkpoints;
- preserve existing working systems;
- do not delete or rewrite unrelated work to make your branch easier to merge;
- if upstream main changes a contract you depend on, re-audit your assumptions.

### Before PR/merge

PR/handoff should state:

- surface and lifecycle stage;
- exact original base SHA;
- current exact head SHA;
- files/semantics owned by this branch;
- new/changed Bank record counts, if applicable;
- native/market evidence used;
- unresolved items (must be explicit, never hidden);
- cross-surface dependencies/ownership decisions;
- Integration Requirements for shared files;
- applicable tests and CI results.

If `main` moved after the worker started:

1. sync/rebase/rebuild the candidate onto latest main;
2. resolve semantic as well as textual conflicts;
3. recompute counts/progress assumptions;
4. rerun surface-specific and global contracts;
5. use the new exact head as the only merge candidate.

Do not treat CI from an old head as evidence for a new head.

## 8. Exact-head CI and failure handling

Every applicable merge gate must be green on the exact candidate head.

Typical gates include architecture guards, platform compatibility matrix, PHP quality, distributable package, browser E2E/accessibility and relevant smoke/integration suites.

When a gate fails:

- stop promotion/merge;
- read the actual failing evidence;
- fix source/data/contract inconsistency when the test is correct;
- do not weaken, delete or bypass a valid gate merely to obtain green CI;
- only change a test when the test itself encodes stale/incorrect architecture, and document why.

A documentation statement is never a substitute for a failed executable gate.

## 9. Conflict-resolution hierarchy for parallel agents

When parallel findings conflict, resolve in this order:

1. canonical semantic ownership and no-bypass contract;
2. canonical dependency matrix;
3. current WordPress/native behavior evidence;
4. current provider/market evidence;
5. existing certified Bank/audit state;
6. WPE future/exceed design with explicit justification.

Do not resolve conflict by duplicating both interpretations into separate runtime engines.

If the conflict changes a canonical ownership or shared architecture contract, it is an integration/architecture decision, not a module-local edit.

## 10. Safety and truthfulness rules

- Never store plaintext secrets in code, Bank records, fixtures, logs, docs or prompts.
- Never expose arbitrary PHP/eval/raw SQL/shell execution as a convenience option.
- Never claim provider success, migration success, deployment success or lifecycle completion without evidence.
- Never silently perform destructive/live-provider operations under ordinary source-development authority.
- Keep Multisite/site/network identity context authoritative and non-spoofable.
- Preserve Policy/Ability authorization paths across UI, REST, CLI, Workflow and AI/MCP.

## 11. Definition of a clean parallel result

A parallel module result is acceptable only when:

- its surface ownership is unambiguous;
- it does not duplicate another surface's semantic engine;
- its writes do not collide with another active worker's owned files;
- shared-file changes are delegated/reconciled by the integrator;
- current main changes have been incorporated before merge;
- counts/status claims match repository truth;
- applicable exact-head CI passes;
- the merge leaves other active module branches able to rebase/reconcile without hidden architectural breakage.

If these conditions are not met, the task is not complete even if the branch compiles locally.
