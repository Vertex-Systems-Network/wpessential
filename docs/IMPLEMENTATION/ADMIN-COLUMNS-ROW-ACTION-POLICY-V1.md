# Admin Columns Primary / Row-Action Policy V1

Status: **IMPLEMENTED FOUNDATION / GATE D STILL ACTIVE**  
Issue: #243  
Parent: #66

## Scope

This tranche adds a side-effect-free effective-state policy for the Admin Columns primary column and row actions.

The policy consumes:

- finite canonical column metadata (`key`, `enabled`, `primary`);
- an explicit target-adapter capability descriptor containing primary-eligible column keys and advertised row-action keys;
- the finite list of row actions requested by a later integration layer.

## Primary resolution

V1 never invents a primary column. A primary column is effective only when:

1. exactly one column is explicitly authored as primary;
2. that column is enabled;
3. the target adapter explicitly advertises that column as primary-eligible.

Missing, disabled or ineligible primary state returns a deterministic unavailable reason. Duplicate primary declarations are malformed authored state and fail closed.

## Row actions

A requested row action is effective only when the primary state is available and the target adapter explicitly advertises that action key. No implicit WordPress fallback is used.

The output is **effective capability state only**. `available=true` is not an authorization grant. Any later executable action must still pass canonical Policy and target/source-owner authorization.

## Safety boundaries

This service performs no persistence, WordPress row-action execution, Query work, source-owner mutation, UI rendering, nonce work or authorization.

Malformed/duplicate/over-budget machine keys fail closed.

## V1 budgets

- columns: <= 100;
- target/requested row actions: <= 20 each.

## Non-scope

- controller/list-table integration;
- action URLs/nonces;
- Policy grants;
- edit/delete/trash execution;
- target/provider discovery;
- Gate D PASS, Gate E start, Status runtime, product parity or deployment.
