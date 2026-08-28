# ADR-0180 — Universal Foundations & Woo Adapter Technical Evidence Master Plan

Status: **Accepted evidence planning / execution pending**  
Date: 2026-08-29

## Context

ADR-0177 expanded WPEssential from the original 31 planned surfaces to 43 product-option surfaces by accepting 12 universal foundations and the Solution Blueprint architecture. The WooCommerce Commerce Domain Adapter was accepted as the first formal domain adapter.

Product-option Exhaustive status does not establish physical/runtime readiness.

ADR-0178/0179 separately accept the shared AI Prompt/MCP architecture and explicit AIP evidence fixtures.

## Decision

Accept:

`docs/QUALITY/UNIVERSAL-FOUNDATIONS-TECHNICAL-EVIDENCE-MASTER-PLAN.md`

The following fixed evidence namespaces are reserved:

- F01 Solution Blueprint: `SBP-001…SBP-176`;
- F02 Analytics/Journey: `ANL-001…ANL-176`;
- F03 Search/Index: `SRH-001…SRH-176`;
- F04 Decision/Formula: `DEC-001…DEC-176`;
- F05 Ledger: `LED-001…LED-176`;
- F06 Reservation: `RSV-001…RSV-176`;
- F07 Placement/Personalization: `PLC-001…PLC-176`;
- F08 Experiments/Rollout: `EXP-001…EXP-176`;
- F09 Documents/Records: `DOC-001…DOC-176`;
- F10 Sync/ETL: `SYN-001…SYN-176`;
- F11 Geo/Territory: `GEO-001…GEO-176`;
- F12 AI Prompt/MCP: `AIP-001…AIP-176`;
- WooCommerce Domain Adapter: `WCA-001…WCA-176`.

All are **0/176 executed**.

## Evidence structure

Each 176-fixture envelope is divided into 16 stable evidence groups of 11 fixtures covering, as applicable:
- definition/schema/lifecycle;
- data and identity;
- authorization/privacy;
- concurrency/idempotency;
- failure/reconciliation;
- cache/invalidation;
- version/provider drift;
- Multisite/site lifecycle;
- performance/scale;
- end-to-end golden/regression behavior.

The AIP protocol is already fully enumerated fixture-by-fixture under ADR-0179. Other namespaces may expand group fixtures before execution without changing evidence ownership or falsely promoting readiness.

## Preserved separation

- Product-option Exhaustive ≠ technical ready.
- Shared foundation evidence ≠ consumer module certification.
- Woo adapter evidence ≠ payment/tax/shipping/provider settlement certification.
- AI Prompt evidence ≠ underlying module business-runtime certification.
- static research ≠ runtime evidence.

## Development gate

ADR-0180 authorizes no executable evidence.

No database/schema build, analytics collection, search index, formula evaluation, ledger movement, reservation lock, placement render, experiment assignment, document render, sync, geocoder, AI call, MCP session or WooCommerce mutation has executed.

ADR-0014 explicit scoped owner development consent remains required.