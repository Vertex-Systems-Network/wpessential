# Membership — Runtime Gap Matrix V1

Surface: **15 / Membership**  
Issue: **#498**  
Runtime authorization: **false**.

| Family | Planning state | Runtime gap | Evidence required before runtime promotion |
|---|---|---|---|
| Plan identity | Seeded | No canonical Plan Definition/revision lifecycle | unit + integration + portability |
| Pricing model | Seeded | No typed price/interval representation adapter | unit + compatibility |
| State lifecycle | Seeded | No deterministic membership state machine/expiration service | unit + integration + recovery |
| Plan changes | Seeded | No preview/idempotent change planner | security + integration + recovery |
| Restrictions | Seeded | No canonical entitlement-to-Policy evaluator | security + WordPress runtime |
| Discounts | Seeded | No bounded discount definition/provider mapping | compatibility + unit |
| Transaction reference | Seeded | No redacted immutable provider reconciliation adapter | security + compatibility |
| Reporting | Seeded | No privacy-safe bounded aggregation/query layer | performance + security |

## Cross-cutting requirements

**Security:** server-authoritative entitlement/resource checks; provider secrets/tokens never enter UI/bootstrap/export data.  
**External side effects:** no live charge, refund, subscription mutation or money movement without a later separately authorized provider lane.  
**Multisite:** explicit site/network entitlement scope; no implicit cross-site grants.  
**Accessibility:** semantic forms/tables, keyboard support, visible focus and announced lifecycle/provider states.  
**Compatibility:** payment/provider adapters are allowlisted and optional; missing providers fail safely.  
**Performance:** bounded reporting, indexed state queries and no unbounded provider/transaction scans.  
**Portability:** portable plan definitions exclude credentials and environment-specific transaction data.

Runtime implementation, provider execution, destructive membership mutation, deployment/release and certification claims remain forbidden in this lane.