# ADR-0204 — Data Sync & ETL Executable Evidence Protocol

Status: **Accepted**  
Date: **2026-08-29**  
Scope: **Planning/evidence only — no implementation authorization**

## Context

ADR-0180 reserved `SYN-001…SYN-176` as the fixed evidence namespace for F10 — Data Sync & ETL. WP72 required the reserved group envelope to be expanded into exact executable-evidence fixtures before implementation so synchronization cannot later be called production-ready from option screens, static architecture or paper claims alone.

F10 crosses remote systems and therefore requires explicit truth boundaries around business authority, idempotency, unknown provider outcomes, bidirectional conflict ownership, delete/tombstone semantics, cursor honesty, privacy propagation, Multisite isolation, restore/clone safety and provider quotas.

## Decision

Accept `docs/QUALITY/DATA-SYNC-ETL-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical detailed evidence specification for F10.

The protocol fixes **SYN-001…SYN-176** across the previously reserved 16 evidence domains:

1. pipeline/source/destination/connection schema;
2. mapping/type transformation/validation;
3. initial full sync/checkpoint/cursor;
4. incremental change capture/poll/webhook source;
5. idempotency/deduplication/replay;
6. create/update/delete/tombstone semantics;
7. bidirectional conflict/ownership/field authority;
8. unknown remote outcome/reconciliation;
9. retry/backoff/dead-letter/manual replay;
10. secret/Vault/SSRF/rate-limit/provider quotas;
11. schema/version/provider drift/migration;
12. privacy/PII/data minimization/export/erase propagation;
13. Multisite/network/shared connection isolation;
14. restore/clone/environment cursor safety;
15. million-record/throughput/backpressure performance;
16. CRM/ERP/catalog/warehouse golden reconciliation suite.

## Frozen architecture boundaries

- Synchronized copy is not source truth unless an explicit entity/field authority contract assigns that authority.
- Transport success is not business acceptance.
- Timeout/unknown provider outcome is not failure; reconcile before unsafe replay.
- Cursor/checkpoint progress is not proof that every item succeeded; unresolved/dead-lettered/unknown items remain visible.
- Replay preserves logical operation identity and idempotency semantics.
- Bidirectional sync requires explicit field/entity authority and conflict policy; implicit universal last-write-wins is prohibited.
- Delete/archive/tombstone/privacy erasure/immutable-record revoke are distinct semantics.
- Credentials remain Vault-owned; remote targets remain adapter-bounded and SSRF governed.
- Provider quota/Retry-After behavior, schema drift and API-version drift must be surfaced and evidenced.
- Privacy/data-residency/export/erase propagation remains Policy/data-governance controlled.
- Site/tenant/environment ownership is server-resolved; cross-tenant sync authority cannot come from request parameters.
- Restore/clone/staging cannot blindly reuse production cursor/webhook/lease/provider-write state.
- F10 does not replace Backup, Staging/Migration, F05 Ledger, F06 Scheduling, F09 immutable Records or commerce/payment/order authorities.
- AI/MCP may draft mappings/reconciliation suggestions only through normal Policy/approval/adapter contracts and receives no privileged connector path.

## Evidence truth

At acceptance:
- `SYN` documented: **176/176**;
- `SYN` executed: **0/176**;
- F10 runtime certification: **0**;
- implementation authorization: **NOT GRANTED / 0/56**.

No connector, provider request, webhook, polling job, cursor mutation, database mutation, replay, benchmark, AI/MCP call, build or test was executed by accepting this ADR.

## Work-package effect

- **WP72 — F10 Data Sync & ETL detailed evidence: DONE as a planning/evidence package.**
- Next safe planning action: **WP73 — F11 Geospatial & Territory detailed executable-evidence specification (`GEO-001…GEO-176`)**.
- WP74 remains reserved for the WooCommerce Commerce Domain Adapter (`WCA`).

## Consequences

A future F10 implementation cannot claim readiness merely because mappings, connectors or UI exist. Required SYN fixtures must be executed against the exact implementation/provider/backend profile and accepted later as runtime evidence. Historical planning denominators and all development-consent gates remain unchanged.