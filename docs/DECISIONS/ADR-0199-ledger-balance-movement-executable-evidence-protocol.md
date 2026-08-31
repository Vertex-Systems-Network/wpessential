# ADR-0199 — F05 Ledger, Balance & Movement Detailed Executable Evidence Protocol

Status: **Accepted — planning/evidence only**  
Date: **2026-08-29**  
Work package: **WP67**  
Supersedes: none

## Context

ADR-0177 accepted F05 — Ledger, Balance & Movement Engine as a reusable universal foundation. The universal technical evidence master plan reserved `LED-001…LED-176` as 16 groups × 11 fixtures, but those IDs remained group-level envelopes.

WP67 must freeze the fixture-level evidence before implementation so append-only history, balance correctness, idempotency, concurrency, reversals, reconciliation and restore continuity cannot later be claimed from UI completeness or happy-path tests.

## Decision

Accept `docs/QUALITY/LEDGER-BALANCE-MOVEMENT-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical detailed evidence protocol for F05.

The protocol fully enumerates **LED-001…LED-176** across:

1. ledger/account/movement schemas;
2. append-only identity, idempotency and source references;
3. debit/credit or quantity semantics and balance derivation;
4. holds/reservations/release/expiration;
5. reversal, compensation, refund and void truth;
6. concurrent postings, locking and isolation;
7. crash, partial failure, unknown external outcomes and reconciliation;
8. rebuild, snapshots and checkpoints;
9. currency/unit/decimal precision and rounding;
10. Policy, approval, manual adjustment and re-auth;
11. import, migration, duplicate-source handling and replay;
12. Audit-vs-ledger truth, privacy, retention and integrity;
13. Multisite/tenant/site lifecycle isolation;
14. backup, restore, clone and environment continuity;
15. 10K/100K/1M movement scale and throughput evidence;
16. wallet, loyalty, inventory, commission, provider and adversarial golden regressions.

## Architecture invariants

- Ledger movement/balance is canonical only for the explicitly owned ledger profile; it is not payment-provider settlement, bank truth, order truth, entitlement, reservation or authorization.
- Posted movements are immutable. Corrections use explicit reversal/compensation/superseding movements rather than silent history edits.
- Idempotency/source identity protects duplicate processing but never grants authority.
- Holds are distinct from final postings; confirm/release/expiry races must resolve atomically.
- Constrained balances must be enforced atomically under concurrency; UI pre-checks are insufficient.
- Unknown external provider outcomes remain unknown until reconciliation; blind replay is prohibited.
- Audit Log is operational evidence around ledger actions, not a second movement-history authority.
- Snapshots/materialized balances are derived acceleration/recovery structures, not replacements for canonical history unless a separately certified compaction profile proves equivalent invariants.
- Canonical money arithmetic uses decimal semantics. Currency conversion requires explicit rate/source/effective-time/provenance; no rate is invented.
- F04 formulas/scores and Workflows may provide typed inputs/plans but cannot bypass Ledger Policy or become posting truth.
- Manual/high-risk adjustments may require re-auth and maker-checker approval bound to the exact posting-plan fingerprint.
- Multisite/tenant ownership is durable and server-resolved; cross-site transfers require an explicit network/bridge profile rather than caller-supplied scope.
- Backup/restore cannot roll back external providers. A restored ledger must reconcile facts that may have occurred after the recovery point.
- Staging/cloned ledgers must be environment-isolated so they cannot emit duplicate production postings/provider actions.
- AI/MCP-generated posting/reversal/reconciliation plans receive exactly the same Policy, approval, numeric, idempotency, site and immutable-history gates as human-authored plans.

## Evidence truth

At acceptance:

- LED documented: **176/176**;
- LED executed: **0/176**;
- F05 runtime certification: **0**;
- implementation authorization: **not granted**;
- current product denominator remains **56/56 planned, 0/56 authorized**.

No ledger table, posting engine, balance materialization, hold, lock, transaction, provider call, reconciliation, import, restore, benchmark, AI/MCP runtime or database mutation was executed by this ADR.

## Consequences

WP67 is complete as a detailed planning/evidence package. Future implementation cannot claim F05 runtime readiness until the applicable LED fixtures are executed with retained evidence under separate development authorization.

The universal evidence sequence may advance to **WP68 — F06 Resource Scheduling & Reservation (`RSV-001…RSV-176`)** without changing the reserved meanings of WP68…WP74.

## Next safe planning action

Start WP68 by expanding the fixed RSV group envelope into a fixture-level executable-evidence protocol. This remains documentation/specification only until explicit scoped development consent is recorded.
