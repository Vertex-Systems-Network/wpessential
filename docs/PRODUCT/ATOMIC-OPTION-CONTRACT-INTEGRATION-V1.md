# WPEssential — Atomic Option Contract Integration V1

Status: **INTEGRATION_WORK / SHARED_INFRASTRUCTURE_CANDIDATE**  
Coordinator: **Issue #49**  
First consumer: **Issue #48 — Surface 8 Admin Columns (`columns`)**  
Authoritative base: **`d21e524cd873d13b1439af80582a22175069724e`**

## 1. Purpose

This contract establishes the first repository convention for schema-valid per-surface Atomic Option instances without starting runtime implementation.

Canonical downstream lifecycle is:

`ATOMIC_INVENTORY_COMPLETE → OPTION_CONTRACT_COMPLETE → UX_CONTRACT_COMPLETE → RUNTIME_CERTIFIED → PRODUCT_PARITY_CERTIFIED`.

`CAPABILITY_INVENTORY_COMPLETE` remains a valid earlier planning state where older capability-family evidence still needs to be represented. Runtime and product-parity states require later executable evidence and cannot be reached by planning documents alone.

## 2. Canonical artifact convention

- machine option instance: `config/product/option-contracts/<surface-key>.json`;
- canonical schema: `config/product/option-contract.schema.json`;
- shared progress: `config/product/atomic-option-contract-progress.json`;
- surface UX contract: `docs/PRODUCT/<SURFACE-NAME>-UX-CONTRACT-V1.md`;
- generic smoke validator: `tests/Smoke/option-contracts-contract.php`;
- surface validator: `tests/Smoke/option-contract-<surface-key>-contract.php`.

Do not create a second Atomic Option progress registry or a parallel option schema.

## 3. Certified-source projection

For surfaces with a certified Master Options Bank, the machine option instance may include `source_projection` and must include it before claiming `OPTION_CONTRACT_COMPLETE` when the Bank is already `BANK_REVIEWED`.

The projection exists because Bank records are discovery/research semantics, not a requirement for one persisted setting per record. Each certified source record must be accounted for exactly once as one of:

- authored Atomic option;
- user-preference Atomic option;
- integration/provider Atomic option;
- effective or diagnostic state;
- native/runtime implementation evidence;
- out-of-surface typed reference;
- compatibility/provider mapping;
- deferred;
- rejected unsafe;
- WPE-exceed/future guarantee.

`source_projection.atomic_ids` links source records to emitted Atomic options. Non-authored dispositions may intentionally have no Atomic IDs. Peer-owned semantics must name the owner surface rather than duplicate its engine.

For a `BANK_REVIEWED` source, `source_record_count` must equal canonical Options Bank progress truth.

## 4. Shared validator responsibilities

The generic validator is dependency-free PHP, matching the existing repository smoke-contract pattern. It validates:

- canonical 56-surface registry identity;
- exact 56-row Atomic Option progress registry;
- derived progress truth counters;
- instance filename and surface identity;
- canonical lifecycle values and monotonic progress;
- benchmark source structure;
- unique feature-group and Atomic option IDs;
- required option contract objects/types;
- `validation.server_authoritative === true`;
- required test evidence;
- coverage counts;
- rejected-unsafe and WPE-exceed structural invariants;
- source-projection source kinds, dispositions and referential integrity;
- Bank-reviewed projection count consistency;
- progress never outrunning machine instance evidence;
- `missing = 0` and `unclassified = 0` before `OPTION_CONTRACT_COMPLETE` or later.

The shared validator does not encode Surface 8-specific ownership rules or hard-code its 214 source IDs.

## 5. Bank → Atomic translation boundary

The discovery Bank and implementation-grade Atomic schema are intentionally not 1:1. The first-instance program must preserve these shared rules:

- `REJECTED_UNSAFE` remains `REJECTED_UNSAFE` and must be structurally prohibited;
- reviewed deferred/LATER semantics become `DEFERRED_WITH_REASON`;
- WPE-exceed semantics become `EXCEEDS` with explicit reason/dependencies;
- current market parity may become `PARITY` when the contract preserves the accepted behavior;
- native MUST_HAVE is `PARITY` only when native behavior is fully preserved, otherwise `PLANNED_BASELINE`;
- provider/competitive/expert semantics require evidence-based classification rather than mechanical promotion;
- runtime/effective/diagnostic/out-of-surface evidence belongs in source projection and must not be manufactured into fake authored controls;
- discovery `unknown` values must be resolved, explicitly deferred/rejected, or classified as non-authored evidence before contract completion;
- Bank `CONDITIONAL` requiredness must be represented by an implementation-grade dependency/effective requirement rather than silently flattened.

## 6. Surface-local validator boundary

Each surface-local validator owns semantic assertions that cannot be generalized safely, including:

- exact source-record projection completeness;
- canonical ownership / no-bypass constraints;
- non-authored disposition correctness;
- peer-reference boundaries;
- domain-specific safety invariants;
- UX dependency requirements.

Surface 8 specifically must preserve Query, Fields, Relations, Policy, Data Source, Renderer and Content Order ownership boundaries and must not turn visibility into authorization.

## 7. Promotion rules

### OPTION_CONTRACT_COMPLETE

Requires all of:

- schema-valid machine instance;
- unique Atomic IDs;
- source projection complete when a certified Bank exists;
- zero missing and zero unclassified semantics;
- surface-local validator green;
- shared validator green;
- ownership/no-bypass review clean;
- exact-head CI green before shared progress promotion is treated as certified.

A machine instance may be prepared ahead of shared progress. Shared progress may never outrun the instance.

### UX_CONTRACT_COMPLETE

Requires `OPTION_CONTRACT_COMPLETE` first, plus reviewed information architecture, authored/effective state separation, degraded states, accessibility, security, performance, portability and multisite behavior.

## 8. Parallelism and branch integration

Issue #49 is the single-writer lane for shared schema/progress/Composer/CI files. Issue #48 owns Surface 8-local contract/UX/test artifacts.

The first consumer may stack its surface branch on the exact certified Issue #49 infrastructure head. If concurrent work already touched shared files on the surface branch, preserve history and reconcile through an ordinary two-parent integration commit; do not force-move or rewrite another writer's history. The resulting tree must retain the designated integrator's shared truth plus only the surface-local additions.

Before any shared progress promotion, re-read current `main`, current surface head and current integrator head; then rerun applicable exact-head CI.

This lane does not authorize Admin Columns runtime code, release/deployment, or merge of a future PR without separate merge authorization.
