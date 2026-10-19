# ADR-0227 — Custom Tables R1 Internal Migration Abilities

Status: **ACCEPTED**
Date: **2026-10-18**
Approval: `GOV-OWNER-CONSENT-002` (full autonomy; decision recorded instead of asked)
Builds on: ADR-0224 (coordinator), ADR-0225 (preparation and review snapshot), ADR-0226 (lifecycle and request resolution)

## Context

After ADR-0226, a production R1 run can be prepared, reviewed, approved, resolved and executed through internal services. None of them is reachable through the platform's ability system, which is how the plugin exposes actions. The `AbilityRegistry` already enforces the execution channel and the descriptor capability through `PolicyEngine` before any handler runs.

This would be the first entry point for an operation that changes the database schema. Exposing it to REST, the admin UI or the WordPress Abilities API is a larger step than registering it.

## Decision

1. **Module.** A `CustomTablesModule` registers the abilities. It is registered only when the `custom-tables.runtime-composition.factory` service exists, which already requires the Pro Custom Tables runtime and a native `wpdb`. Without the factory, nothing is registered.
2. **Abilities.** All use owner surface 7, capability `manage_options`, `mutates: true` and **`channels: [ Internal ]` only**:
   - `wpessential/custom-tables/prepare-migration`, input `definition_id`: loads the current Definition and prepares a run with its review snapshot.
   - `wpessential/custom-tables/submit-migration`, `approve-migration`, `revalidate-migration` and `cancel-migration`, input `run_id` and `expected_state_revision`.
   - `wpessential/custom-tables/confirm-migration`, input `run_id` and `expected_state_revision`: the plan fingerprint and readiness revision are read from the stored run, never taken from input.
   - `wpessential/custom-tables/execute-migration`, input `run_id` and `backup_id`: resolves the request (ADR-0226) and runs the coordinator (ADR-0224).
3. **Input.** Handlers accept identifiers and expected revisions only. They validate shape and fail with exceptions, following the existing ability handlers. Every trust decision stays in the services, which keep their own authorization checks. Registry and service checks both apply.
4. **Output.** Plain snake_case arrays carrying the run id, state, state revision and, for prepare and execute, the outcome reasons. Execute also reports whether the change was applied and whether a mutation was attempted.
5. **No external exposure.** No `WordPressAbilityBridge::expose()`, no AJAX route, no REST channel. A REST, CLI or UI context is refused by the registry with `channel_not_exposed`. Exposure is a later ADR.

## Consequences

- Plugin code can drive a full R1 migration through the ability registry, with central channel and capability enforcement.
- Nothing becomes reachable from HTTP, the admin UI or WP-CLI.
- The handlers stay thin. Behaviour remains covered by the service tests and the real-database end-to-end test from ADR-0226.
