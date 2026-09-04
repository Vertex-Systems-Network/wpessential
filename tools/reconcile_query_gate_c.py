from __future__ import annotations

import json
from pathlib import Path

MAIN_ANCHOR = "c41158f6baf98912ca76108ec74bc685afe802f7"


def replace_once(text: str, old: str, new: str, label: str) -> str:
    count = text.count(old)
    if count != 1:
        raise SystemExit(f"{label}: expected exactly one match, found {count}")
    return text.replace(old, new, 1)


checkpoint_path = Path("CHECKPOINT.md")
checkpoint = checkpoint_path.read_text(encoding="utf-8")

checkpoint = replace_once(
    checkpoint,
    "Canonical repository reconciliation anchor: **`main @ b1ce9f08b2c69b3d7efcde75a856331eba70a25e`**  ",
    f"Canonical repository reconciliation anchor: **`main @ {MAIN_ANCHOR}`**  ",
    "checkpoint anchor",
)
checkpoint = replace_once(
    checkpoint,
    "Lifecycle decision: **Surface 3 Custom Fields Gate A — PASS for the certified native V1 scope; Surface 4 Relations Gate B — PASS for the certified native V1 baseline; Surface 6 Query Gate C — ACTIVE / bounded runtime and admin-scaffold slices promoted; NOT PASS**  ",
    "Lifecycle decision: **Surface 3 Custom Fields Gate A — PASS for the certified native V1 scope; Surface 4 Relations Gate B — PASS for the certified native V1 baseline; Surface 6 Query Gate C — PASS for the certified bounded V1 baseline; Surface 8 Admin Columns Gate D — ACTIVE**  ",
    "lifecycle decision",
)
checkpoint = replace_once(
    checkpoint,
    "Current dependency gate: **Surface 6 Query / Gate C active closure work**  ",
    "Current dependency gate: **Surface 8 Admin Columns / Gate D baseline implementation**  ",
    "dependency gate",
)
checkpoint = replace_once(
    checkpoint,
    "The later Atomic Option lifecycle is separate: `config/product/atomic-option-contract-progress.json` reports **56/56 atomic inventories, only Relations at OPTION_CONTRACT_COMPLETE, 0 UX_CONTRACT_COMPLETE, 0 full-parity RUNTIME_CERTIFIED and 0 PRODUCT_PARITY_CERTIFIED**.",
    "The later Atomic Option lifecycle is separate: `config/product/atomic-option-contract-progress.json` reports **56/56 atomic inventories, 2 OPTION_CONTRACT_COMPLETE-or-later surfaces (Relations and Admin Columns), 1 UX_CONTRACT_COMPLETE surface (Admin Columns), 0 full-parity RUNTIME_CERTIFIED and 0 PRODUCT_PARITY_CERTIFIED**.",
    "atomic option lifecycle truth",
)
checkpoint = replace_once(
    checkpoint,
    "Surface 6 Query is **BANK_REVIEWED / 169**. Query runtime has started in bounded certified slices: typed AST/validation and Policy-authorized planning, native `wordpress.posts` compilation/execution, bounded Relations predicate pre-resolution, real-WordPress execution evidence, fail-closed cache/diagnostics rules, and a non-runtime admin authoring scaffold are promoted on current main. Gate C remains **ACTIVE / NOT PASS** because Fields-owned predicate consumption, canonical admin route/bootstrap integration and final criterion-by-criterion closure evidence remain open.",
    "Surface 6 Query is **BANK_REVIEWED / 169** and Gate C is **PASS FOR THE CERTIFIED BOUNDED V1 BASELINE**. Current main contains typed AST/validation, Policy-authorized native `wordpress.posts` execution, bounded Relations predicates, bounded Fields-owned predicate resolution through the public Surface 3 consumer, deterministic scale/reference evidence, fail-closed cache/diagnostics rules, and the canonical packaged admin authoring route/bootstrap with execution still disabled. This Gate C pass is a bounded implementation baseline, not full Query Options Bank parity or `PRODUCT_PARITY_CERTIFIED`.",
    "surface 6 product truth",
)
checkpoint = replace_once(
    checkpoint,
    "- Phase 2 / Gate C / Surface 6 Query — **ACTIVE / NOT PASS** — bounded runtime slices are promoted; remaining closure work is explicitly tracked by #161, #162 and #163.\n- Gate D / Admin Columns — **BLOCKED BY QUERY RUNTIME**.\n- Gate E / Dynamic Listings — **BLOCKED BY QUERY PLUS SHARED RENDERER/DATA-SOURCE DEPENDENCIES**.",
    "- Phase 2 / Gate C / Surface 6 Query — **PASS FOR CERTIFIED BOUNDED V1 BASELINE** — all #66 Gate C baseline criteria are satisfied by merged owner-backed runtime/admin evidence through PR #189 and the final exact-main audit.\n- Gate D / Admin Columns — **ACTIVE** — the certified Atomic Option + UX contract may now enter dependency-ordered baseline implementation; runtime/product parity is not yet claimed.\n- Gate E / Dynamic Listings — **BLOCKED UNTIL GATE D AND ITS SHARED RENDERER/DATA-SOURCE DEPENDENCIES ARE READY**.",
    "implementation gate transition",
)
checkpoint = replace_once(
    checkpoint,
    "## Surface 6 — Query Gate C active certified slices\n\nGate C is **ACTIVE / NOT PASS**. Current main contains several bounded Query slices, but downstream Admin Columns, Dynamic Listings and Status runtime remain blocked until the Gate C baseline is actually closed.",
    f"## Surface 6 — Query Gate C certified bounded V1 baseline\n\nGate C is **PASS FOR CERTIFIED BOUNDED V1 BASELINE** at `main @ {MAIN_ANCHOR}`. Downstream Admin Columns Gate D may now begin under the repository dependency order; Dynamic Listings and Status remain blocked by their later gates.",
    "query section status",
)
checkpoint = replace_once(
    checkpoint,
    "- PR #160 — accessible Query-owned admin authoring scaffold with read-only AST preview and execution intentionally unavailable.\n\nRemaining explicit Gate C work at this checkpoint:\n- #161 — Fields-owned bounded public Query consumer contract V1; Query must not infer Field storage/meta ownership.\n- #162 — canonical Query admin route, validated Data Source bootstrap and production build/enqueue integration, with execution still disabled.\n- #163 — exact merged-evidence closure audit against every parent #66 Gate C criterion.\n- Query-side Field predicate runtime resolution remains dependency-blocked until #161 is accepted and must be implemented as a later bounded owner-contract consumer, not a direct storage shortcut.\n\nNo public/admin Query execution endpoint, total-count/aggregation runtime, or cache runtime enablement is implied by this checkpoint.",
    "- PR #160 — accessible Query-owned admin authoring scaffold with read-only AST preview and execution intentionally unavailable.\n- PR #166 — Fields-owned `FieldQueryConsumerInterface` V1 at exact head `92c2e9a7d2a60f25eb2c0d4da903e97d5099b090`; Architecture #911, Matrix #562, PHP Quality #291 and Package #465 all SUCCESS.\n- PR #184 — canonical packaged Query admin route/bootstrap/build integration at exact head `2cd888a6fe3bc4c1476daafadb44a629be9d9321`; Architecture #930, Matrix #581, PHP Quality #299, Package #483 and Browser E2E Accessibility #223 all SUCCESS.\n- PR #189 — bounded Fields predicate validation/resolution through the owner contract at exact head `7164e80fc0e3de0e4fd44323f5e2597d84d9110f`; Architecture #945, Matrix #596, PHP Quality #311 and Package #496 all SUCCESS, merged as `c41158f6baf98912ca76108ec74bc685afe802f7`.\n\nGate C closure facts:\n- #161 is completed by PR #166; Query does not infer Field storage/meta ownership.\n- #162 is completed by PR #184; the canonical admin route/bootstrap/build is packaged while execution remains disabled.\n- #177 is completed by PR #189; owner-backed bounded Field predicates are authorized only after canonical Data Source Policy and fail closed outside the certified native scalar contract.\n- the historical #163 audit remains preserved as the pre-closure evidence baseline and is superseded by the exact-main PASS reconciliation.\n\nNo public/admin Query execution endpoint, total-count/aggregation runtime, cache runtime enablement, arbitrary provider execution or Query product-parity claim is implied by this checkpoint.",
    "query closure evidence",
)

checkpoint_path.write_text(checkpoint, encoding="utf-8")


audit = f"""# Query Gate C Closure Audit V1

Status: **PASS FOR CERTIFIED BOUNDED V1 BASELINE**  
Parent tracker: GitHub Issue #66  
Final audit anchor: `main @ {MAIN_ANCHOR}`

This document re-audits Surface 6 Query after the previously blocked Fields-owner and canonical-admin lanes were promoted. It certifies only the bounded V1 baseline required by #66 Gate C. It does not claim full Query Options Bank parity, public Query execution, cache runtime enablement, aggregation/total-count support, arbitrary providers, production release or deployment.

## Decision

Gate C is **PASS FOR CERTIFIED BOUNDED V1 BASELINE** at this exact-main anchor.

The two blockers recorded by the earlier audit are now closed:

1. **Fields predicates — closed.** PR #166 established the Fields-owned bounded public consumer. PR #189 consumes only that interface, validates owner metadata, requires a finite `post.id` anchor, preserves Policy-before-owner ordering, narrows to an authorized subset, rewrites before native compilation and fails closed outside the certified native scalar contract.
2. **Canonical admin integration — closed.** PR #184 registers the Query admin route, server-owned Data Source bootstrap, production build entry and screen-scoped enqueue. Execution remains internal/disabled. Exact-head Browser E2E Accessibility and package evidence passed.

## Criterion matrix

| #66 Gate C criterion | Final status | Current-main evidence | Explicit boundary |
| --- | --- | --- | --- |
| Typed Query AST | **CERTIFIED V1** | `QueryDefinition`, source/predicate/order/pagination values and canonical AST validation are promoted. | Bounded V1 grammar; not full Bank parity. |
| Provider / Data Source adapter | **CERTIFIED V1** | Canonical `wordpress.posts` descriptor, compiler and executor are promoted through the native execution tranche. | Additional providers require separate certification. |
| Validation + Policy boundary | **CERTIFIED V1** | Canonical source revalidation and `PolicyEngine` authorization occur before provider compilation and before Relations/Fields owner resolution. | No public/admin execution endpoint is certified. |
| Sorting / filtering / search / pagination | **CERTIFIED BOUNDED V1** | Native posts path supports the certified comparison/set predicates, bounded search, field ordering and offset pagination; real-WordPress evidence enforces page-size 100 and blocks 101 before provider execution. | Cursor, arbitrary parameters/taxonomy/date/provider extensions remain fail-closed. |
| Relations predicates | **CERTIFIED BOUNDED V1** | Public `RelationQueryConsumerInterface` + bounded anchor resolver rewrite owner results before native compilation. | Richer traversal/provider semantics remain outside baseline. |
| Fields predicates | **CERTIFIED BOUNDED V1** | PR #166 public `FieldQueryConsumerInterface`; PR #189 Field-aware validation/resolution. Root AND, exactly one direct Field predicate, finite `post.id` anchor, max 100 candidates/results, positive unique subset enforcement, native scalar `storage_owner=native_post_meta` only. | No direct post-meta discovery, `meta_query`, raw SQL, complex/provider storage or unbounded scan. |
| Cache / diagnostics rules | **CERTIFIED RULES; RUNTIME DISABLED** | Query cache eligibility/key + safe diagnostic projection are fail-closed; current `wordpress.posts` remains non-cacheable. | No cache get/put/delete/invalidation or public diagnostics exposure. |
| Performance / no-unbounded-query safeguards | **CERTIFIED BOUNDED V1** | Query budgets, source page/batch bounds, owner anchor caps, provider allowlists and real-WordPress reference matrices are promoted. | Deterministic bounds, not a broad wall-clock performance claim. |
| Canonical admin UX | **CERTIFIED BOUNDED V1** | PR #160 authoring scaffold + PR #184 canonical route/bootstrap/build/enqueue; execution remains visibly unavailable. | Persistence/execution exposure is not added by Gate C. |
| Exact-head evidence | **CERTIFIED** | #166 head `92c2e9a7...`: Architecture #911, Matrix #562, PHP #291, Package #465 SUCCESS. #184 head `2cd888a6...`: Architecture #930, Matrix #581, PHP #299, Package #483, Browser #223 SUCCESS. #189 head `7164e80f...`: Architecture #945, Matrix #596, PHP #311, Package #496 SUCCESS. | New capabilities still require their own exact-head certification. |

## Owner-bound Fields execution contract

The final V1 path is deliberately narrow:

- Query consumes only `FieldQueryConsumerInterface` from `module.custom-fields.query-consumer`;
- accepted owner descriptors are limited to logical types `string`, `boolean`, `integer`, `number` and `storage_owner=native_post_meta`;
- execution requires one explicit finite positive unique `post.id eq/in` anchor and no more than 100 candidates;
- canonical Data Source Policy runs before owner resolution;
- the Fields owner performs definition/storage/target/value normalization and per-post read authorization;
- Query rejects malformed, duplicate, over-limit or foreign owner result IDs;
- successful resolution removes the Field predicate from provider input and narrows to canonical bounded post IDs;
- an empty owner result short-circuits before provider execution;
- when an earlier Relations resolver proves the root AND query empty, Query still validates/removes local Field syntax without unnecessary Fields-owner calls.

## Canonical admin integration contract

The final admin baseline provides:

- Query-owned WordPress admin route under the shared shell;
- server-projected canonical Data Source/bootstrap metadata;
- production Query asset build entry and screen-scoped enqueue;
- packaged deterministic distributable evidence and accessibility coverage;
- no REST/AJAX/admin Query execution endpoint.

## Deferred semantics after Gate C PASS

The following remain explicit non-goals and do not invalidate the declared bounded baseline:

- aggregation and total-count execution;
- cursor pagination and general parameter binding;
- arbitrary taxonomy/date/provider-extension execution;
- cache reads/writes/invalidation and public diagnostics;
- public REST/AJAX/CLI/workflow/AI Query execution;
- complex/provider-owned Field storage outside the certified owner contract;
- full Query Options Bank runtime parity or `PRODUCT_PARITY_CERTIFIED`.

## Downstream gate transition

Gate D / Admin Columns may now begin because the #66 Query dependency is satisfied and the Admin Columns Atomic Option + UX contract is already authoritative on main through PR #190. Gate D must preserve Query ownership of backend sort/filter/search, Fields/source-owner validation for mutations, and Policy as authorization; visibility remains presentation-only.

Gate E / Dynamic Listings and Status runtime remain blocked by their later dependency gates.
"""
Path("docs/IMPLEMENTATION/QUERY-GATE-C-CLOSURE-AUDIT-V1.md").write_text(audit, encoding="utf-8")


queue = {
    "schema_version": 1,
    "snapshot_date": "2026-09-04",
    "queue_anchor_main_sha": MAIN_ANCHOR,
    "authoritative_rule": (
        "Agents MUST refresh current main and repository evidence before selecting or claiming a slot. "
        "Query Gate C is PASS for the certified bounded V1 baseline at the queue anchor after PR #166, #184 and #189; "
        "Admin Columns Atomic Option + UX lifecycle is authoritative through PR #190. Gate D is therefore the active dependency gate. "
        "A queue anchor is evidence, not permission to work from stale history. Deterministic remote branch creation is the claim lock; "
        "completed/superseded claims must not be re-used or force-moved. Shared checkpoint/queue truth remains Supervisor-only."
    ),
    "auto_assignment": {
        "enabled": True,
        "selection_order": "lowest numeric priority first",
        "selection_rule": "Choose the highest-priority OPEN slot allowed for the agent role whose dependencies are satisfied and whose exclusive write boundary is not already claimed.",
        "claim_mechanism": "DETERMINISTIC_REMOTE_BRANCH_CREATE",
        "force_push_forbidden": True,
        "speculative_branch_creation_forbidden": True,
        "no_valid_slot_behavior": "Make no repository changes and report NO_VALID_WORK_SLOT.",
        "revalidation_before_work": [
            "Read AGENTS.md, CONTRIBUTING.md, CHECKPOINT.md and docs/ENGINEERING-EXECUTION-GOVERNANCE.md.",
            "Read current main SHA and the final Query Gate C audit; verify PR #166, #184, #189 and #190 remain authoritative.",
            "Inspect open/merged PRs, issues and deterministic claim branches before claiming a slot.",
            "Verify the selected slot is still needed, dependencies are satisfied and no other active slot owns the same files/semantic boundary.",
            "Preserve Query ownership of backend sort/filter/search, Fields/source-owner mutation semantics and Policy authorization.",
            "Only then create the exact deterministic claim branch from exact current main."
        ],
        "revalidation_before_merge": [
            "Absorb or reconcile latest main without force/history overwrite.",
            "Re-check dependency, ownership and no-bypass assumptions.",
            "Review exact diff; reject edits to another active slot's exclusive boundary or Supervisor-only shared truth.",
            "Require all applicable exact-head CI on the final candidate head and clean review/thread state.",
            "Never use stale CI or an earlier branch head as merge evidence."
        ],
        "claim_branch_rule": "For DETERMINISTIC_REMOTE_BRANCH_CREATE slots, create the exact claim_branch from exact current main without force. If creation fails because the branch exists, treat the slot as claimed and evaluate the next eligible slot."
    },
    "roles": {
        "SUPERVISOR": {
            "start_mode": "EXPLICIT",
            "description": "Exactly one Supervisor/Integrator owns shared truth, cross-slot integration and merge serialization.",
            "may_claim": ["SUPERVISOR_ONLY", "ANY"],
            "must_not": [
                "Pre-create worker claim branches before the worker/agent actually starts that slot.",
                "Merge a branch only because an agent reports completion.",
                "Allow dependency-blocked runtime work to start.",
                "Allow parallel workers to edit config/coordination/agent-work-queue.json, CHECKPOINT.md or other shared project-state truth."
            ]
        },
        "WORKER": {
            "start_mode": "AUTO",
            "description": "Workers autonomously inspect current repository truth and claim one eligible non-overlapping slot through deterministic branch creation.",
            "may_claim": ["ANY"],
            "must_not": [
                "Claim SUPERVISOR_ONLY slots.",
                "Modify shared/global truth unless the slot explicitly grants that scope.",
                "Create a different branch name when the deterministic branch is already occupied.",
                "Start dependency-blocked work or edit another active slot's exclusive files."
            ]
        }
    },
    "slots": [
        {
            "priority": 60,
            "slot_id": "columns-runtime-definition-foundation-v1",
            "title": "Establish the Admin Columns runtime definition and module foundation V1",
            "role": "ANY",
            "status": "OPEN",
            "work_mode": "RUNTIME_CONTRACT_WORK",
            "parallel_class": "COLUMNS_CORE_EXCLUSIVE",
            "claim_mechanism": "DETERMINISTIC_REMOTE_BRANCH_CREATE",
            "source": "Parent Issue #66 Gate D plus completed Admin Columns contract/UX Issues #48/#49",
            "dependencies": [
                "Query Gate C PASS is authoritative on current main",
                "Admin Columns option contract is UX_CONTRACT_COMPLETE with 214/214 Bank mapping and 41 Atomic Options"
            ],
            "runtime_allowed": True,
            "allowed_scope": [
                "new Admin Columns-owned PHP runtime/definition/module files under a dedicated frameworks/Modules/AdminColumns boundary",
                "canonical definition identity/lifecycle/normalization and typed runtime value objects required by the bounded baseline",
                "surface-local unit tests and one implementation document",
                "consume shared Data Source/Policy/Renderer contracts rather than creating private registries or authorization engines",
                "do not edit Query, Fields, Relations production code, admin-ui, queue, CHECKPOINT.md or shared Platform implementation",
                "do not implement inline/bulk mutation, export, performance adapters or product-parity claims in this foundation slot"
            ],
            "completion_condition": "A fail-closed Admin Columns core definition/module foundation is exact-head green and exposes stable typed seams for later Query read and source-owner mutation integration.",
            "notes": "May run in parallel with the admin-ui scaffold and docs-only Gate D baseline audit because those slots own disjoint paths.",
            "claim_branch": "agent/columns-runtime-definition-foundation-v1"
        },
        {
            "priority": 70,
            "slot_id": "columns-admin-authoring-scaffold-v1",
            "title": "Build the non-runtime Admin Columns authoring scaffold from the certified UX contract",
            "role": "ANY",
            "status": "OPEN",
            "work_mode": "ADMIN_UI_WORK",
            "parallel_class": "COLUMNS_ADMIN_UI_EXCLUSIVE",
            "claim_mechanism": "DETERMINISTIC_REMOTE_BRANCH_CREATE",
            "source": "Parent Issue #66 Gate D plus docs/PRODUCT/ADMIN-COLUMNS-UX-CONTRACT-V1.md",
            "dependencies": [
                "Query Gate C PASS is authoritative on current main",
                "Admin Columns UX contract is UX_CONTRACT_COMPLETE"
            ],
            "runtime_allowed": False,
            "allowed_scope": [
                "new Admin Columns admin-ui source/style files and surface-local browser/accessibility fixture/spec files only",
                "project the certified Column Sets/Views, sources/formats, sorting/filtering capability states, editing/export safety messaging and degraded states",
                "execution/mutation/save/provider calls remain disabled; use a future server-owned bootstrap contract rather than inventing backend semantics",
                "do not edit PHP runtime, Query/Fields/Relations, shared Platform, package/build registration, queue or CHECKPOINT.md",
                "emit explicit Integration Requirements for route/bootstrap/build wiring instead of racing shared files"
            ],
            "completion_condition": "Accessible non-runtime Admin Columns authoring UX is exact-head green and ready for serialized server/bootstrap integration without exposing runtime mutation or execution.",
            "notes": "Path-exclusive admin-ui lane; safe in parallel with the PHP core foundation.",
            "claim_branch": "agent/columns-admin-authoring-scaffold-v1"
        },
        {
            "priority": 80,
            "slot_id": "columns-gate-d-baseline-audit-v1",
            "title": "Audit Admin Columns Gate D baseline evidence and implementation gaps",
            "role": "ANY",
            "status": "OPEN",
            "work_mode": "EVIDENCE_WORK",
            "parallel_class": "DOCS_EVIDENCE_ONLY",
            "claim_mechanism": "DETERMINISTIC_REMOTE_BRANCH_CREATE",
            "source": "Parent Issue #66 Gate D",
            "dependencies": [
                "Query Gate C PASS is authoritative on current main",
                "Admin Columns Atomic Option + UX contracts are authoritative"
            ],
            "runtime_allowed": False,
            "allowed_scope": [
                "one new docs/IMPLEMENTATION/ADMIN-COLUMNS-GATE-D-BASELINE-AUDIT-V1.md evidence matrix only",
                "map each #66 Gate D requirement to existing contract evidence or a concrete implementation blocker",
                "identify Query/source-owner/Policy/visibility/export/performance/accessibility boundaries without inventing runtime completion",
                "do not edit production code, admin-ui, shared Platform, Query, Fields, Relations, queue or CHECKPOINT.md",
                "do not claim Gate D PASS"
            ],
            "completion_condition": "A current-main evidence matrix provides a precise dependency-safe work breakdown for Gate D while other two initial lanes proceed.",
            "notes": "Docs-only lane designed to run fully in parallel with both implementation writers.",
            "claim_branch": "agent/columns-gate-d-baseline-audit-v1"
        },
        {
            "priority": 90,
            "slot_id": "columns-query-source-integration-v1",
            "title": "Integrate Query-backed read/sort/filter/search adapters for Admin Columns",
            "role": "ANY",
            "status": "BLOCKED",
            "work_mode": "RUNTIME_INTEGRATION_WORK",
            "parallel_class": "COLUMNS_QUERY_ADAPTER_EXCLUSIVE",
            "claim_mechanism": "DETERMINISTIC_REMOTE_BRANCH_CREATE",
            "source": "Parent Issue #66 Gate D",
            "dependencies": [
                "columns-runtime-definition-foundation-v1 is promoted and exposes stable typed seams",
                "Query Gate C remains authoritative"
            ],
            "runtime_allowed": True,
            "allowed_scope": [
                "Admin Columns-owned Query adapter files only after the foundation is promoted",
                "consume Query public planner/execution contracts; do not duplicate backend query semantics",
                "do not edit Query production code or shared truth"
            ],
            "completion_condition": "Blocked until the core foundation is merged; then certify Query-owned backend semantics through an Admin Columns adapter without bypasses.",
            "notes": "Do not claim or branch while BLOCKED.",
            "claim_branch": "agent/columns-query-source-integration-v1"
        }
    ]
}
Path("config/coordination/agent-work-queue.json").write_text(json.dumps(queue, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")

# Final invariants for the workbench transformation.
assert f"main @ {MAIN_ANCHOR}" in checkpoint
assert "Gate C — PASS for the certified bounded V1 baseline" in checkpoint
assert "Gate D — ACTIVE" in checkpoint
assert "Status: **PASS FOR CERTIFIED BOUNDED V1 BASELINE**" in audit
assert queue["slots"][0]["status"] == "OPEN"
assert queue["slots"][1]["status"] == "OPEN"
assert queue["slots"][2]["status"] == "OPEN"
assert queue["slots"][3]["status"] == "BLOCKED"

print("Gate C reconciliation transformation complete.")
