# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-02**  
Canonical repository reconciliation anchor: **`main @ 045b941b2ec17174837fa8997087a7753705fe1b`**  
Planning authority: `planning/master-architecture` through ADR-0213  
Implementation decisions: through **ADR-0222** plus certified bounded Surface 3 and Surface 4 implementation contracts  
Project classification: **`ACTIVE_EXISTING_PROJECT`**  
Execution mode: **`IMPLEMENTATION_GATED`**  
Lifecycle decision: **Surface 3 Custom Fields Gate A — PASS for the certified native V1 scope; Surface 4 Relations Gate B — IN PROGRESS**  
Current dependency gate: **Surface 4 Relations / Gate B**  
Development approval: **GOV-OWNER-CONSENT-001 ACTIVE / source scope 56/56**

## Approval boundary

Authorized sequence remains:

`Implementation Baseline / Adoption Gate → Machine-enforced architecture guards → Milestone 1 Platform Foundation → dependency-gated module development`.

Phase 2 dependency order is authoritative:

`Fields → Relations → Query → Admin Columns → Dynamic Listings → Status`.

Source implementation, development/test tooling, CI and milestone-scoped schemas/tests are authorized. Production deployment/release, destructive live-site/customer-data operations, chargeable or irreversible provider side effects and separately privileged release operations remain excluded unless explicitly authorized.

## Product/planning truth

Accepted structural scope remains **56/56 Exhaustive**, Multisite **56/56**, AI Prompt **56/56**, with no known structural planning or semantic-owner gap after WP118 / ADR-0213.

Current Master Options Bank machine truth from `config/product/options-bank-progress.json` is **8 surfaces started / 6 BANK_REVIEWED / 1,571 records**. The reviewed surfaces are Fields 618, Relations 144, Status 129, Custom Tables 165, Admin Columns 214 and Dashboard Widgets 123. CPT remains `BANK_SURFACE_SEEDED / 107`; Taxonomy remains `BANK_SURFACE_SEEDED / 71`; Query and Listings remain `UNSEEDED / 0` in shared progress until their reconciliation work is accepted and integrated.

The later Atomic Option lifecycle is separate: `config/product/atomic-option-contract-progress.json` reports **56/56 atomic inventories, only Relations at OPTION_CONTRACT_COMPLETE, 0 UX_CONTRACT_COMPLETE, 0 full-parity RUNTIME_CERTIFIED and 0 PRODUCT_PARITY_CERTIFIED**.

Surface 3 Fields Options Bank remains **BANK_REVIEWED at 618 records**. Gate A PASS does **not** mean all 618 Bank records are shipped, runtime implemented or `PRODUCT_PARITY_CERTIFIED`. Runtime certification is deliberately narrower and applies only to the supported native V1 contracts listed below. Provider-owned, Relations-owned and other uncertified storage semantics remain fail-closed.

## Implementation gates

- WP119 / ADR-0214 — **DONE / PASS** — greenfield Implementation Baseline / Adoption Gate.
- WP120 / ADR-0215 — **DONE / PASS** — machine-enforced architecture guards.
- WP121 — **DONE / PASS FOR MODULE HANDOFF** — shared Platform foundation readiness closed by WP121.1 through WP121.4.
- Phase 2 / Gate A / Surface 3 Custom Fields — **PASS FOR CERTIFIED NATIVE V1 SCOPE** — all #66 Gate A exit criteria are satisfied by merged exact-head evidence summarized below.
- Phase 2 / Gate B / Surface 4 Relations — **IN PROGRESS** — canonical Definition lifecycle, durable edge persistence and transactional connect/disconnect foundations are merged; Gate B closure remains open.
- Gates C–E / Query → Admin Columns → Dynamic Listings — **BLOCKED BY DEPENDENCY ORDER** at runtime.
- Status Manager runtime — **BLOCKED UNTIL GATES A–E ARE COMPLETE**.

## Surface 3 — Custom Fields certified runtime scope

### Foundation slices 1–7 — PASS

The earlier checkpointed slices remain accepted:

1. PR #35 — canonical Field Type / repeatability foundation.
2. PR #36 — canonical `field_group` Definition lifecycle and module-local Ability/AJAX wiring.
3. PR #38 — machine-readable canonical Field Catalog API.
4. PR #39 — typed server-side Field Value normalization.
5. PR #41 — stable RFC4122 per-Field identity and collision protection.
6. PR #42 — WordPress registered post-meta storage V1 with explicit auth, sanitization, REST schema and subtype support guards.
7. PR #44 — native post-meta value persistence V1 with typed reads, idempotent verified writes, deletion semantics and fail-closed unsupported multi-row mutation at that stage.

The source checkpoint after slice 7 was `f867eb318c3b529b3bf12b8f37873801c15f2b5b`. All blocker language tied to that historical point is superseded by the certified closure slices below.

### Closure slice 8 — Field value Ability + target/resource authorization — PASS

Promotion PR #57 certified source `5b56f632d8466104c0eefb1c54b6848236ec2997`.

Established:
- typed Field read/write Abilities;
- persisted Field Group + stable Field UUID target resolution;
- actual post subtype/status evaluation;
- V1 location matching for supported target predicates;
- resource-level WordPress authorization through the shared Policy/ExecutionContext boundary;
- optimistic group revision protection on value mutation;
- existing `PostMetaValueStore` as the bounded mutation path;
- draft/disabled, unknown Field, target mismatch and unsupported owner/storage cases fail closed.

### Closure slice 9 — runtime storage projection — PASS

Promotion PR #58 certified source `d454956e7768de6bbcf8434753c54d96af020b65`.

Established explicit runtime projection for Field Group native-post-meta mode, REST exposure and revision policy. Omitted/legacy storage does not silently become native storage.

### Closure slice 10 — finite post-type target compiler — PASS

Promotion PR #59 certified source `d293db195ac0f03eb701ff5ed737dfb1083d77ac`.

Established deterministic finite post-subtype compilation from supported OR-of-AND location rules. Negative-only, unbounded, malformed or unsupported targeting fails closed rather than widening registration scope.

### Closure slice 11 — registered-meta ownership guard — PASS

Promotion PR #60 certified source `b1a31ff514237cf5b356f6e7a92c45aec53a26d7`.

Established fail-closed ownership preflight for global/subtype registered-meta collisions. A foreign same-key registration cannot be silently replaced. Structurally identical WPE registration with the same stable Field identity remains idempotent.

### Closure slice 12 — Published Field Group post-meta binder — PASS

Promotion PR #64 certified source `1508e3722eb55d7a43011067a1a15a7e17f7610`.

Established canonical Published Field Group → finite target → registered post-meta binding using the certified storage projection/compiler/ownership guard. Draft/unpublished groups cannot bind through this contract.

### Closure slice 13 — `single=false` multi-row replacement/recovery — PASS

Promotion PR #65 certified source `a9e713198d55d77cafda61bf143cbf9fffbaef25`.

Established:
- exact row order and duplicate preservation;
- bounded snapshot before destructive replacement;
- public WordPress Metadata API mutation only;
- post-write verification;
- compensating restore after partial failure;
- post-restore verification;
- explicit uncertain-state failure if compensation cannot be proven.

This closes the destructive-write/recovery hole that was explicitly open in the seven-slice checkpoint.

### Closure slice 14 — explicit storage-key migration/rollback — PASS

Issue #68 promoted by PR #70, certified source `1b65e1aa236b125e946391da9eda8f459623d291`.

Established an explicit migration workflow separate from ordinary Field editing:
- stable Field UUID retained while storage key changes;
- finite target compilation;
- source/destination ownership and collision preflight;
- scalar/list/multi-row value preservation;
- destination verification before source retirement;
- concurrent-source drift protection;
- ownership-guarded registration retirement;
- verified compensating rollback;
- explicit uncertain-state failure when rollback cannot be verified.

Ordinary Field save continues to reject silent persisted storage-key rename.

### Closure slice 15 — shared Pro module activation/admission gate — PASS

Issue #71 promoted by PR #73, certified source `9834284d6f9237734a195cb05fd644b368a70786`.

Established a Free-owned neutral pre-registry activation contract:
- default policy admits Free modules and denies Pro modules;
- denied Pro modules never enter the registry and cannot execute `register()` / `boot()`;
- explicit pre-boot `ModuleActivationPolicyInterface` injection can authorize externally supplied modules;
- explicit pre-boot `Plugin::registerModule()` is the neutral contribution seam;
- Free bootstrap contains no concrete `FieldsModule` / `Modules\Fields` source dependency;
- dependencies on denied modules use the existing degraded path.

This is the canonical shared module/edition admission boundary for current Gate A. It is not a billing/licensing/checkout implementation, and `ModuleManifest::edition` remains classification rather than entitlement by itself.

### Closure slice 16 — catalog-driven Field Group admin builder/rendering — PASS

Issue #74 promoted by PR #76, certified source `08549390abfa76a906557d53c762b6df6fc6c539`.

Established:
- module-owned Field Group admin builder under the shared WPEssential admin shell;
- canonical catalog projection rather than duplicated hard-coded field semantics;
- existing Ability/AJAX/nonce/capability boundaries for reads/writes;
- stable Field UUID preservation;
- persisted storage keys read-only in ordinary editing;
- supported bounded field types editable through canonical semantics;
- unsupported provider/complex/container types preserved read-only / fail-closed;
- existing payload sections preserved during ordinary edits.

Exact-head evidence included Architecture #750, PHP Quality #183, Platform Compatibility #433, Browser E2E Accessibility #189 and Distributable Package #352 — all SUCCESS.

### Closure slice 17 — provider/complex owner-boundary certification — PASS

Issue #77 promoted by PR #79, certified source `f8c2f515151f28e757ec6580b15cc44ab4b6ed65`.

Certified that `relationship`, `taxonomy`, `user`, `page_link`, `nav_menu`, `sidebar`, `group`, `repeater`, `flexible_content`, `clone`, `accordion` and `tab` do not silently fall into native post-meta storage when their canonical owner/storage contract is absent. Real WordPress registered-meta evidence proves the same owner-boundary behavior. `post_object` and `posts` remain positive controls for the certified native integer-reference tranche.

Fail-closed unsupported ownership is a deliberate safety boundary, not an unresolved destructive-write hole.

### Closure slice 18 — definition portability / import-export compatibility V1 — PASS

Issue #80 promoted by PR #82, certified source `9e7d07a3acc1b5e289fbe4792db5d64e39284631`.

Exact-head CI: Architecture #770, PHP Quality #195, Platform Compatibility #448 and Distributable #364 — all SUCCESS.

Certified definition-only create-safe portability:
- versioned Surface 3 Field Group envelope;
- deterministic export;
- stable Definition UUID and Field UUID preservation;
- payload checksum verification;
- strict known format/schema/type/owner/key boundary;
- absent definition creates; identical same-ID re-import is idempotent;
- divergent identity/slug/group-key/Field UUID collisions fail closed;
- no silent remap or storage-key migration bypass;
- source revision is provenance only; imported local revision starts at 1.

Values/data import, destructive merge/overwrite, provider/Relations storage and dependency remapping remain explicit non-goals.

### Closure slice 19 — deterministic performance/scale envelope V1 — PASS

Issue #83 promoted by PR #85, certified source `820be13284e684809df1012ef9b9afec915e8a57`.

Exact-head CI: Architecture #782, PHP Quality #201, Platform Compatibility #456 and Distributable #372 — all SUCCESS.

Certified deterministic scale behavior without unstable wall-clock assertions:
- a 512-Field selected group resolves with one Definition `get()`, zero repository-wide `byType()` scans, one post-type lookup and one post-status lookup;
- batch registration preserves complete preflight plus live per-tuple ownership/support revalidation;
- for `N` registration tuples and `P` unique post types, ownership-map reads are bounded at `2N + P + 1` rather than the prior `4N` model;
- a callback cannot introduce foreign ownership for a later tuple and then be silently overwritten;
- scalar value IO has a fixed metadata-call budget;
- multi-row replacement scales linearly with desired rows plus bounded verification.

No arbitrary product caps, direct SQL or cross-request registration cache were introduced.

### Closure slice 20 — automatic runtime binding + composed real-WordPress reference — PASS

Issue #86 promoted by PR #88, certified source `a29c9ba573923203e209bcf967c74f9366c2580d`; merged to `main` as `b7b882422f616ee135441c6b52674ff5522a839c`.

Exact-head CI:
- Architecture Guards #793 — SUCCESS;
- PHP Quality Toolchain #205 — SUCCESS;
- Platform Compatibility Matrix #466 — SUCCESS;
- Distributable Package #378 — SUCCESS.

Established the final runtime composition required by Gate A:
- an admitted `FieldsModule` owns a runtime coordinator;
- compiled CPT runtime registers at WordPress `init` priority 20;
- Fields runtime binding executes at `init` priority 30;
- only canonical Surface 3 Published Field Groups are selected;
- all Published groups compile into one registrar-owned batch;
- cross-group duplicate `(post_type, meta_key)` fails before the first Field registration mutation;
- runtime failure is inspectable and non-fatal, with no silent overwrite/remap;
- denied Pro Fields cannot install the runtime hook because module lifecycle never executes.

Platform Compatibility #466 runs the composed fresh-request reference on:
- WordPress 6.9 / 7.1 × PHP 8.2 / 8.3 / 8.4 / 8.5 with MySQL 8.4;
- WordPress 6.9 / 7.1 × PHP 8.4 with MariaDB 10.11.

The composed workflow proves the public pre-boot module admission seam, persistent CPT/Field Group Ability creation, actual `Plugin::boot` lifecycle, automatic native registered-meta binding, REST-visible metadata shape, native/internal Field value Ability IO, non-target rejection, Draft/foreign-owner exclusion and all-groups collision fail-before-mutation behavior.

## Surface 3 Gate A exit audit — PASS

Parent tracker: #66. Closure checkpoint tracker: #89.

### Criterion 1 — no known destructive-write/recovery hole for certified native storage paths — PASS

Evidence:
- verified single-value persistence from PR #44;
- `single=false` snapshot/replacement/compensating recovery from #65;
- explicit storage-key migration with destination verification, source retirement and verified rollback from #70;
- registration ownership preflight/live revalidation from #60/#85;
- cross-group combined-plan fail-before-first-mutation from #88.

Unsupported storage owners do not fall through to native post meta; they fail closed.

### Criterion 2 — runtime activation governed by shared module/edition contract — PASS

Evidence:
- shared activation gate #73 owns admission before registry insertion;
- default Pro is denied;
- neutral pre-boot policy + module contribution seam is shared infrastructure;
- concrete `FieldsModule` activation through that seam is exercised by #88 real-WordPress reference evidence;
- Free bootstrap remains concrete-Fields-source free;
- no Fields-private license or activation bypass exists.

Commercial entitlement/billing/provider implementation under ADR-0010/P-006 remains a separate non-goal and is not represented as completed.

### Criterion 3 — admin UX can create/edit/publish the supported canonical Field Group model without duplicate semantics — PASS

Evidence:
- #76 catalog-driven builder/rendering consumes the canonical Fields catalog and existing Ability/AJAX contracts;
- stable UUID and storage-key protections are preserved;
- unsupported owner-bound types are read-only/fail-closed rather than reimplemented locally;
- Browser E2E Accessibility #189 passed on the certified admin-builder source.

### Criterion 4 — import/export + migration/rollback certified for supported definitions/storage changes — PASS

Evidence:
- #82 definition portability/import-export V1;
- #70 explicit native post-meta storage-key migration/rollback V1;
- ordinary save cannot bypass explicit migration;
- create-only portability does not claim destructive merge/overwrite or Field value/data import.

### Criterion 5 — real WordPress compatibility, security, accessibility and performance evidence green — PASS

Evidence includes:
- repeated WordPress 6.9/7.1 × PHP 8.2–8.5 compatibility matrices and MariaDB 10.11 baselines across registered-meta, persistence, migration, value Ability and owner-boundary slices;
- #88 Platform Compatibility #466 composed lifecycle/reference workflow across MySQL and MariaDB baselines;
- shared Policy/ExecutionContext + object/resource authorization on Field value operations;
- registered-meta object-level authorization and ownership protection;
- #76 Browser E2E Accessibility #189 for the Field Group admin builder;
- #85 deterministic target/binding/value-IO scale certification.

### Criterion 6 — checkpoint truth accurately reflects current main and explicit non-goals — PASS on promotion of the Gate A checkpoint

The Gate A checkpoint replaced the obsolete seven-slice blocker list and anchored that historical audit to `main @ b7b882422f616ee135441c6b52674ff5522a839c`. This 2026-09-02 reconciliation preserves that evidence while advancing the current repository anchor and lifecycle to Relations Gate B.

## Explicit Surface 3 non-goals after Gate A

Gate A PASS is intentionally scoped. The following are **not** claimed complete:
- all 618 Fields Bank records as runtime/shipped features;
- `PRODUCT_PARITY_CERTIFIED` for Surface 3;
- provider-owned entity/storage adapters without their canonical owner contracts;
- Relations-owned relationship/cardinality/edge storage;
- arbitrary provider callbacks or executable PHP/JavaScript configuration;
- generic custom-table/provider storage for unsupported Field types;
- destructive definition import merge/overwrite;
- Field values/data import/export;
- automatic remapping of conflicting Definition IDs, slugs, group keys, Field UUIDs or storage keys;
- billing, checkout, licensing or production entitlement-provider implementation;
- production deployment, stable release or live-site migration;
- any Query, Admin Columns, Dynamic Listings or Status runtime implementation.

Unsupported provider/Relations/container types remain deliberately fail-closed until their owning surfaces/adapters are certified. This is part of the safety contract.

## Surface 4 — Relations Gate B current merged evidence

Relations is now the active runtime gate. Surface 4 owns relation/cardinality/direction/persistent-edge semantics. Fields retains relationship selector/control-schema ownership; Query retains structured query semantics; consumers must use typed public contracts instead of peer-private storage/runtime access.

### Gate B prerequisite — Atomic Option Contract V1 — PASS

PR #100 promoted exact certified source `e3591526d1e2f35c1fbda912b19aa198be03cad8`.

- Relations Bank remains `BANK_REVIEWED / 144`.
- Exact source projection is 144/144 Bank IDs across seven validated shards.
- The surface projects to 18 canonical Relations-owned authored Atomic Options.
- Missing/unclassified is 0/0.
- Only Surface 4 is currently `OPTION_CONTRACT_COMPLETE` in shared atomic progress.

This planning/product-contract certification did not itself claim runtime implementation.

### Gate B runtime slice 1 — canonical Relation Definition lifecycle — PASS

PR #103 promoted exact certified source `55aa6dfd65c310af8f9a9934a2a658f1e860a136`.

Established:
- canonical Surface 4 `relation` Definition lifecycle;
- immutable relation key and optimistic revision/checksum persistence;
- cardinality/direction/endpoints/bounds/unique-edge normalization;
- publish-time native WordPress endpoint validation;
- uncertified custom-table/provider endpoints fail closed;
- shared Definition/Ability/AJAX infrastructure only;
- Pro module remains outside default Free bootstrap.

### Gate B runtime slice 2 — durable edge persistence / recovery — PASS

PR #112 promoted exact source `67dae2a4a07d990df79ac7e44223205f843a68d1` into merge `b06c3999d91979f76909352a8fd8a52729524637`.

Established:
- Surface 4-owned scoped InnoDB edge and mutation-state persistence;
- explicit network/site scope on keys and reads/writes;
- durable unique logical edge tuple for the current certified storage contract;
- shared MigrationCoordinator contribution instead of request-time lazy DDL;
- per-relation transaction serialization with `FOR UPDATE` state locking;
- revision compare-and-swap completion;
- rollback and explicit uncertain-recovery failure behavior;
- deterministic source/target reads and malformed-row fail-closed hydration.

Exact-head evidence: Architecture Guards #828, PHP Quality #221, Platform Compatibility #492, Distributable #401 and Relations Edge Persistence #2 across MySQL 8.4/PHP 8.2 + 8.5 and MariaDB 10.11/PHP 8.4 — all SUCCESS.

### Gate B runtime slice 3 — transactional connect/disconnect foundation — PASS

PR #114 promoted exact source `4420f6a00d69dd7b01c8afc7576adc187435cae2` into merge `69ed7416d6e5090ca6c14d2b6779266e5613c847`.

Established:
- canonical Published Relation loading/normalization before mutation;
- relation-scoped transactional connect/disconnect over the durable edge gateway;
- cardinality maximum enforcement for one-to-one, one-to-many, many-to-one and many-to-many;
- disconnect minimum-bound protection;
- deterministic unique-edge idempotency;
- fail-closed native endpoint existence + meta-capability authorization for post/media/term/user/comment;
- typed connect/disconnect Ability handlers and AJAX routes only when durable persistence is available;
- rollback on mutation failure.

Exact source `4420f6a00d69dd7b01c8afc7576adc187435cae2` passed Architecture Guards #835, PHP Quality #224, Platform Compatibility #497, Distributable Package #404 and Relations Edge Persistence #7 — all SUCCESS.

The current durable schema permits one logical `(relation_definition_id, from_object_id, to_object_id)` tuple per scope. Therefore `unique_edge=false` definitions deliberately fail closed in this mutation tranche until a later storage contract can represent non-unique tuples safely.

### Gate B remains OPEN

The merged foundations do **not** close Relations Gate B. Remaining bounded work includes, where canonical ownership/contracts require it:
- broader endpoint/object/provider adapters;
- Query/Data Source integration without moving Query ownership into Relations;
- Relations admin editing UX;
- import/export and diagnostics;
- pivot metadata, ordering, cascade and non-unique tuple semantics only after explicit owner/storage/recovery contracts;
- reference workflow / real-WordPress integration evidence for completed Gate B behavior;
- deterministic performance/scale evidence;
- final Gate B exit audit against parent #66.

Query, Admin Columns, Listings and Status runtime remain blocked until their dependency gates open.

## Shared WP121 foundation remains accepted

WP121 remains **PASS FOR MODULE HANDOFF**. Accepted shared foundation includes:
- Bootstrap / Kernel / Service Registry / Module lifecycle;
- Definition / ExecutionContext / Policy / Ability / Event core;
- Audit, Vault, Assets and Integrations foundations;
- WordPress Capability + Abilities API bridge;
- atomic compiled-registration persistence/recovery;
- Definition + Audit MySQL persistence + migration ledger;
- WordPress.org-facing source/package guards;
- real WordPress AJAX/nonce/Policy integration;
- Action Scheduler coexistence profile;
- durable Job persistence/revision/attempt/lease/checkpoint primitives;
- Platform admin shell / Runtime Observatory;
- locked Composer and Node quality toolchains;
- deterministic distributable package and real-browser accessibility baselines;
- Multisite runtime isolation evidence.

This remains a source-development/module-handoff decision, not a stable-release or production-deployment approval.

## AUTO multi-agent coordination state

`AUTO-AGENT.md` and `config/coordination/agent-work-queue.json` are active on current `main`.

Claim branches observed during this reconciliation:
- Supervisor shared-state lane — `agent/shared-state-reconciliation-v1`;
- Relations Gate B continuation — `agent/relations-gate-b-closure-v1`;
- Taxonomy Bank reconciliation — `agent/taxonomy-bank-reconciliation-v1`.

All three claims were created from exact `main @ 045b941b2ec17174837fa8997087a7753705fe1b`. The supervisor owns the serialized `CHECKPOINT.md` / project-state reconciliation. The Relations worker owns its bounded Surface 4 runtime scope. The Taxonomy worker is planning-only and must surface shared progress changes as Integration Requirements rather than racing integrator-owned shared files.

Historical open PRs are not automatically current AUTO submissions or merge-ready merely because they remain open. Any candidate must be reconciled against then-current `main`, ownership/dependency rules, Integration Requirements and exact-head applicable CI before integration. Shared/global writes remain serialized; force update/force merge is not a conflict-resolution strategy.

## Current next action

1. Promote this supervisor shared-state reconciliation only after its exact diff is confirmed to stay within the assigned shared-state scope and applicable exact-head checks are green or truthfully recorded as not path-applicable.
2. Continue Surface 4 Relations Gate B on the claimed Relations worker lane using bounded slices; do not touch supervisor-owned shared truth from that worker branch.
3. Continue Taxonomy planning/Bank reconciliation on its claimed surface-local lane; any shared lifecycle/progress promotion remains integrator-owned after accepted evidence.
4. Keep Query, Admin Columns, Dynamic Listings and Status **runtime blocked**. Planning-only work may proceed only under an explicitly valid queue claim and cannot be reported as runtime progress.
5. After any accepted worker merge, re-read current `main`, active claims, open submissions and machine progress files before the next shared write or merge-order decision.

Repository evidence overrides conversational memory.
