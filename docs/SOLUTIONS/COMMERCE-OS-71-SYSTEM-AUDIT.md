# WPEssential — WooCommerce AI-Native Commerce OS 71-System Capability Audit

Status: **Phase 0 planning / source audit / no implementation authorized**  
Date: 2026-08-28  
Source reviewed: `WooCommerce_AI_Native_Commerce_OS_Product_Plan(1).docx`, 32 pages, planning edition 28 August 2026.

## 1. Audit conclusion

The source document contains **71 numbered systems**, not 70.

WPEssential should **not** create 71 new modules. The majority are application-level Solution Blueprints that compose existing WPE primitives plus a small number of reusable missing foundations and a formal WooCommerce domain adapter.

Primary classification of the 71 source systems:

- `DIRECT` from existing generic module contracts: **3**;
- `COMPOSABLE_WITH_ADAPTER`: **20**;
- `NEEDS_FOUNDATION`: **46**;
- `EXTERNAL_AUTHORITY_REQUIRED` as the primary blocker: **2**.

The 46 foundation-dependent systems do **not** imply 46 new modules. They converge on the 12 reusable candidates in `FOUNDATIONAL-MODULE-GAP-PLAN.md`.

## 2. Legend

### Existing WPE primitives

- `DATA` = CPT/Taxonomy/Fields/Relations/Custom Tables
- `STATE` = Status Manager
- `QUERY` = Query Builder
- `UI` = Listings/Builder Widgets/Dashboard Widgets/Admin Columns
- `PORTAL` = Frontend Dashboard/User Profile/Membership/Roles
- `FLOW` = Forms & Workflow/Cron
- `COMMS` = Notification/Email/Chat
- `API` = REST/Webhooks/Connections
- `OPS` = Import/Export/Backup/Protector/Media/Platform Diagnostics
- `SHARED` = Policy/Abilities/Event Bus/Jobs/Vault/Audit/Conditional Logic/DVR/Cache/Rate Limit/etc.

### New reusable foundations

- `F02` Analytics/Event/Session/Journey
- `F03` Search & Indexing
- `F04` Decision/Formula/Scoring/Ranking
- `F05` Ledger/Balance/Movement
- `F06` Resource Scheduling/Reservation
- `F07` Experience Placement/Personalization
- `F08` Experimentation/Rollout
- `F09` Documents/Records/Generation
- `F10` Data Sync/ETL
- `F11` Geospatial/Location/Territory
- `F12` AI Gateway/Copilot
- `S01` Simulation/Historical Replay
- `S02` Transaction/Saga contract
- `S04` Context Resolver
- `S05` Money/Decimal/Unit library
- `A01` WooCommerce Commerce Domain Adapter Pack

`F01 Solution Blueprint Composer` packages the resulting systems but is not repeated in every row.

---

# 3. 71-system mapping

| # | Source system | Primary classification | Current WPE composition | Additional required foundation/adapter | Canonical primary flow |
|---:|---|---|---|---|---|
| 1 | Wishlist, Favorites & Intent Lists | NEEDS_FOUNDATION | DATA + QUERY + UI + PORTAL + FLOW + COMMS | F02 + A01 | identify visitor/user → add/remove list item → persist/share → merge identity → notify/segment/cart |
| 2 | Product Compare | COMPOSABLE_WITH_ADAPTER | DATA + REL + QUERY + UI | A01 | select products → hydrate comparison schema → show differences → share/add-to-cart |
| 3 | Recently Viewed & Continue Shopping | NEEDS_FOUNDATION | QUERY + UI + PORTAL | F02 + A01 | product view → session/journey event → retain under privacy policy → rank/resume items |
| 4 | Saved Cart & Shopping Lists | COMPOSABLE_WITH_ADAPTER | DATA + REL + PORTAL + FLOW + COMMS | A01 | snapshot cart → save/share → re-open → revalidate price/stock → restore selected/all |
| 5 | Product Q&A | COMPOSABLE_WITH_ADAPTER | DATA + REL + STATE + FORMS + UI + COMMS + MEDIA | A01 for verified-purchase context | question → moderation → answer/vote → notify watchers → promote FAQ |
| 6 | Search & Discovery Studio | NEEDS_FOUNDATION | QUERY + UI + FLOW | F03 + F02 + A01 | query → analyze/tokenize → secure index search → rank/facet → log outcome/zero result |
| 7 | Smart Collections & Dynamic Merchandising | COMPOSABLE_WITH_ADAPTER | QUERY + CONDITIONAL + STATE + CRON + UI | A01 | collection rule → evaluate products → pin/exclude/order → publish → incremental recalc |
| 8 | Product Recommendation Studio | NEEDS_FOUNDATION | QUERY + REL + UI + FLOW | F02 + F04 + A01 | candidate set → policy/filter → score/rank → placement → impression/click/order feedback |
| 9 | Product Badge & Merchandising Label Engine | NEEDS_FOUNDATION | DATA + CONDITIONAL + UI | F07 + A01 | product/context change → evaluate badge rules → resolve priority/stacking → render placement |
| 10 | Product Lifecycle Manager | COMPOSABLE_WITH_ADAPTER | STATE + FLOW + CRON + CONDITIONAL + COMMS | A01 | lifecycle state → timed/manual guarded transition → visibility/purchase effects → audit |
| 11 | Product Watch / Stock & Price Alerts | COMPOSABLE_WITH_ADAPTER | DATA + FORMS + FLOW + COMMS + PORTAL | A01 | opt-in/watch → observe product event → revalidate eligibility/consent → notify → suppress/frequency |
| 12 | Product Compatibility Finder | COMPOSABLE_WITH_ADAPTER | FIELDS + REL + QUERY + IMPORT + UI | A01 | select owned model/asset → traverse compatibility relations → exclude conflicts → recommend/checkout warn |
| 13 | Size & Fit Recommendation | NEEDS_FOUNDATION | FIELDS + REL + FORMS + QUERY + UI | F04 + F02 + A01 | measurements/profile + product fit data → score/confidence → recommendation → feedback/returns calibration |
| 14 | Shop-the-Look & Visual Bundling | COMPOSABLE_WITH_ADAPTER | MEDIA + FIELDS + REL + UI + QUERY | A01 | scene/hotspots → resolve tagged products → select variants → availability fallback → add selected/all |
| 15 | Bundles, Mix & Match & Build-a-Box | NEEDS_FOUNDATION | DATA + REL + QUERY + FLOW | F04 + S05 + A01 | choose bundle components → validate constraints → price → reserve/check stock → cart/order mapping |
| 16 | Advanced Product Configurator | NEEDS_FOUNDATION | DATA + REL + UI + FLOW + QUERY | F04 + S05 + A01 | step choice → evaluate dependency/formula → update price/compatibility → summarize → cart/quote/save |
| 17 | Fly Cart / Cart Drawer OS | NEEDS_FOUNDATION | UI + QUERY + FLOW + COMMS | F07 + A01 | cart event → render contextual drawer → edit cart → recompute incentives → checkout |
| 18 | Cart Reward Ladder | NEEDS_FOUNDATION | FLOW + CONDITIONAL + UI + COMMS | F04 + F05 when points/credit + A01 | cart context → evaluate threshold → grant/revoke reward state → show progress → audit economics |
| 19 | Checkout Field Manager | COMPOSABLE_WITH_ADAPTER | FIELDS + CONDITIONAL + FORMS + DATA | A01 | checkout schema → context rules → render/validate → persist allowed data → downstream actions |
| 20 | Visual Checkout Layout Builder | NEEDS_FOUNDATION | UI + FIELDS + CONDITIONAL + QUERY | F07 + A01; F08 optional | choose checkout variant → compose steps/components → validate step → payment completion → measure |
| 21 | Upsell, Cross-sell & Order Bump Engine | NEEDS_FOUNDATION | QUERY + UI + FLOW | F02 + F04 + F07 + A01 | context → candidates → eligibility → rank → render offer → accept/reject → mutate cart/order safely |
| 22 | Discount & Promotion Engine | NEEDS_FOUNDATION | CONDITIONAL + FLOW + STATE + AUDIT | F04 + S05 + A01 | cart/customer/product context → deterministic rule resolution → stacking/budget → tax-safe adjustment → trace |
| 23 | Gift Engine | NEEDS_FOUNDATION | CONDITIONAL + FLOW + UI | F04 + A01; F05 where reward balance applies | eligibility → gift pool → choose/auto-select → stock revalidation → add/remove qualification-linked gift |
| 24 | Promotion Scheduler & Launch Engine | COMPOSABLE_WITH_ADAPTER | CRON + FLOW + STATE + AUDIT | A01 | draft launch plan → conflict/simulation → approval → scheduled activation → connected changes → rollback |
| 25 | Verified Countdown & Urgency | NEEDS_FOUNDATION | CRON + STATE + UI | F07 + A01 | authoritative deadline → server state → render timer → expiry transition → linked offer/state closes |
| 26 | Shipping Rules Builder | NEEDS_FOUNDATION | CONDITIONAL + FLOW + AUDIT | F04 + S05 + A01; F11 for advanced zones | cart/location → rule evaluation → method/fee result → explain hidden/available → checkout |
| 27 | Payment Rules Builder | NEEDS_FOUNDATION | CONDITIONAL + FLOW + AUDIT | F04 + S05 + A01 | checkout context → rule evaluation/risk policy → gateway visibility/surcharge → explain → payment provider |
| 28 | Thank-you & Post-Purchase Builder | NEEDS_FOUNDATION | UI + QUERY + FLOW + COMMS | F07 + A01 | paid order → select experience → render contextual blocks/offers → optional safe order action → follow-up |
| 29 | Self-Service Order Editing | NEEDS_FOUNDATION | PORTAL + FORMS + FLOW + AUDIT | A01 + S02 + S05 | request edit → state/lock eligibility → impact preview → payment/tax/stock delta → transactional apply/reconcile |
| 30 | Campaign Studio | NEEDS_FOUNDATION | DATA + REL + STATE + FLOW + COMMS + UI | F02 + F04 + F07 + F08 | brief → audience/offer/content/placement plan → simulation/approval → activate → attribute/measure → improve |
| 31 | Dynamic Customer Segments | NEEDS_FOUNDATION | QUERY + CONDITIONAL + CRON + DATA | F02 + F04 for scored segments | define audience → query behavior/profile/order data → materialize/cache membership → enter/leave events |
| 32 | Visual Automation Flow | COMPOSABLE_WITH_ADAPTER | FORMS/WORKFLOW + CRON + COMMS + API + SHARED | A01 for commerce actions/events | trigger → condition → branch/wait → typed action → retry/reconcile → run history |
| 33 | Abandoned Journey Recovery | NEEDS_FOUNDATION | FLOW + COMMS + QUERY | F02 + F04 + A01 | journey activity → inactivity window → classify intent → sequence → stop on conversion → attribute recovery |
| 34 | Review & UGC Engine | COMPOSABLE_WITH_ADAPTER | DATA + FORMS + MEDIA + STATE + COMMS + UI | A01 for verified purchase/delivery | eligible purchase → request → submit media/review → moderate → publish → reward/support follow-up |
| 35 | Verified Social Proof & Live Activity | NEEDS_FOUNDATION | UI + CONDITIONAL | F02 + F07 + A01 | verified event aggregates → privacy/anonymization → eligibility/frequency → render truthful proof |
| 36 | Popup & On-site Message Builder | NEEDS_FOUNDATION | UI + FORMS + CONDITIONAL + FLOW | F07 + F08 | context/trigger → frequency/policy → render experience → form/action → attribution/experiment |
| 37 | Commerce Forms | COMPOSABLE_WITH_ADAPTER | FORMS + DATA + FLOW + COMMS + PORTAL | A01 only for commerce context/actions | render → validate/consent/spam → save typed entry → CRUD/segment/workflow/notification |
| 38 | Loyalty & Rewards | NEEDS_FOUNDATION | DATA + FLOW + PORTAL + COMMS | F05 + F04 + A01 | qualifying event → earn/revoke posting → tier evaluate → expose balance/benefits → redeem/reconcile |
| 39 | Referral Program | NEEDS_FOUNDATION | DATA + REL + FLOW + COMMS | F02 + F05 + F04 + A01 | referral identity/touch → signup/order validation → anti-abuse → delayed reward posting → refund reversal |
| 40 | Store Credit & Wallet Ledger | NEEDS_FOUNDATION | PORTAL + FLOW + AUDIT | F05 + S05 + A01 | credit/debit request → policy/idempotency → append ledger entry → balance → spend/refund/expiry/reconcile |
| 41 | Replenishment & Reorder Intelligence | NEEDS_FOUNDATION | QUERY + FLOW + CRON + COMMS | F02 + F04 + A01 | order history → estimate interval/confidence → schedule due → revalidate stock/preferences → remind/reorder |
| 42 | Gift Registry & Occasion Lists | COMPOSABLE_WITH_ADAPTER | DATA + REL + PORTAL + FLOW + COMMS | A01 | create registry → add/share items → buyer purchase/reservation → update quantities/state → event reminders |
| 43 | Creator / Affiliate Commerce | NEEDS_FOUNDATION | DATA + REL + FLOW + PORTAL | F02 + F05 + F04 + A01 | tracking touch/code → attribution → order/refund → commission ledger → approval/export/payout authority |
| 44 | Customer Portal Builder | COMPOSABLE_WITH_ADAPTER | PORTAL + UI + QUERY + FORMS + COMMS | A01 for commerce widgets/actions | resolve principal/role → route/policy → render widgets → execute typed self-service actions |
| 45 | B2B Company Accounts & Approvals | COMPOSABLE_WITH_ADAPTER | DATA + REL + ROLES + PORTAL + FLOW + MEMBERSHIP | A01 | company/users/roles → cart/order threshold → approval chain → approved payment/fulfillment → audit |
| 46 | Quotation & Deal Room | NEEDS_FOUNDATION | DATA + REL + STATE + FORMS + FLOW + PORTAL + COMMS | A01 + S02; F09 optional | quote request → version/price/approval negotiation → send → accept/expire → safe order/payment conversion |
| 47 | Customer Service Center | COMPOSABLE_WITH_ADAPTER | FORMS + CHAT + STATE + FLOW + PORTAL + COMMS | A01 for order/refund/credit actions | issue → ticket/context → assign/SLA → converse → approved commerce action → resolution |
| 48 | Commerce Inbox / Live Chat | COMPOSABLE_WITH_ADAPTER | CHAT + PORTAL + COMMS + FLOW | A01 | conversation → resolve visitor/customer/cart/order context → agent/AI assist → typed action/handoff → close |
| 49 | Order Operations / Fulfillment Board | COMPOSABLE_WITH_ADAPTER | STATE + QUERY + UI + ADMIN COLUMNS + FLOW + COMMS | A01 | order event → operational stage/owner/SLA → board work → checklist/action → customer update/exception |
| 50 | Split Orders, Shipments & Packages | NEEDS_FOUNDATION | DATA + REL + STATE + FLOW + PORTAL | A01 + S02; F05 for inventory allocation | allocate lines → create shipment/package records → reserve/consume stock → track partial fulfillment → reconcile parent |
| 51 | Order Exception Manager | COMPOSABLE_WITH_ADAPTER | DATA + STATE + QUERY + UI + FLOW + COMMS | A01 | detect rule/anomaly → create exception → assign/severity/SLA → remediation → verify normalized → close |
| 52 | Returns, Exchanges & Warranty OS | NEEDS_FOUNDATION | FORMS + DATA + REL + STATE + FLOW + MEDIA + PORTAL | A01 + S02 + F05 | eligibility → case/request → approval/label → inspection → refund/exchange/credit → inventory disposition |
| 53 | Delivery, Pickup & Slot Operations | NEEDS_FOUNDATION | PORTAL + FLOW + COMMS + CONDITIONAL | F06 + F11 + A01 | cart/location → available slots/capacity → atomic hold → checkout confirm → fulfillment/reschedule |
| 54 | Booking & Rental Resource OS | NEEDS_FOUNDATION | DATA + PORTAL + FORMS + FLOW + COMMS | F06 + F04 + A01/payment adapter | search availability → hold resource → configure/deposit → confirm → check-in/out → extend/damage/close |
| 55 | Subscription Lifecycle Manager | EXTERNAL_AUTHORITY_REQUIRED | PORTAL + STATE + FLOW + COMMS + QUERY | A01 + billing/subscription provider adapter + S02 | provider contract fact → normalized subscription state → self-service change → renewal/dunning → reconcile provider truth |
| 56 | Inventory Ledger & Multi-Location Stock | NEEDS_FOUNDATION | DATA + REL + STATE + QUERY + FLOW | F05 + A01 | stock source event → append movement/reservation → recompute balances → allocate/release → reconcile/count |
| 57 | Purchase Orders & Procurement | NEEDS_FOUNDATION | DATA + REL + STATE + FORMS + FLOW + PORTAL | F05; F09 optional | demand/request → PO draft → approval → send → partial receipt → inventory/cost postings → close/variance |
| 58 | Supplier Portal | DIRECT | DATA + REL + PORTAL + FORMS + STATE + FLOW + COMMS + ROLES | F09 optional documents | supplier login → authorized PO/list → confirm/date/docs/comment → procurement workflow/exception |
| 59 | Demand Planning & Reorder Recommendations | NEEDS_FOUNDATION | QUERY + CRON + FLOW + UI | F02 + F04 + F05 + A01 | sales/stock/lead time → forecast/scenario → stockout score → suggested order → approval → PO |
| 60 | Stock Counts, Transfers & Reconciliation | NEEDS_FOUNDATION | FORMS + STATE + FLOW + PORTAL + AUDIT | F05 + A01 | create count/transfer → capture physical/process events → variance → approval → ledger postings → reconcile |
| 61 | Markets & Localization Console | EXTERNAL_AUTHORITY_REQUIRED | SETTINGS + CONDITIONAL + QUERY + UI + API | S04 + F11 + A01 + currency/translation/tax/duties adapters | resolve market → select catalog/pricing/content/payment/shipping context → authoritative tax/localization integrations |
| 62 | Price Lists & Contract Pricing | NEEDS_FOUNDATION | DATA + REL + CONDITIONAL + FLOW + IMPORT | F04 + S05 + A01 | resolve customer/company/market → choose price book → quantity/effective-date formula → stacking policy → cart/order |
| 63 | Unified Commerce Analytics | NEEDS_FOUNDATION | QUERY + UI + DASHBOARDS + COMMS | F02 + A01 | collect normalized events → aggregate metrics → dashboard/cohort/attribution → anomaly → drilldown |
| 64 | Funnel & Journey Analytics | NEEDS_FOUNDATION | QUERY + UI | F02 + F08 | session/journey events → funnel definition → step windows/cohorts → dropoff/path analysis → alert/experiment link |
| 65 | Search & Merchandising Analytics | NEEDS_FOUNDATION | QUERY + UI | F02 + F03 + A01 | search/compare/wishlist events → demand metrics → zero-result/findability analysis → merchandising task |
| 66 | Profit & Promotion Guardrails | NEEDS_FOUNDATION | CONDITIONAL + FLOW + AUDIT + APPROVAL | F04 + F05/F02 + S05 + A01 | cost/revenue inputs → scenario formula → margin/budget threshold → allow/block/approval → trace |
| 67 | Commerce Inspector & Conflict Center | NEEDS_FOUNDATION | PLATFORM DIAGNOSTICS + AUDIT + QUERY + POLICY | S01 + A01 | capture context/snapshot → trace rules/adapters/errors → rank causal chain → propose safe test → verify |
| 68 | Rule Simulator & Why? Explainer | NEEDS_FOUNDATION | CONDITIONAL + DEFINITION + WORKFLOW + AUDIT | S01 + F04 | construct/historical context → run no-write evaluation → condition/action trace → compare versions → explain |
| 69 | Admin Notifications & Action Center | DIRECT | NOTIFICATION + DASHBOARD + STATUS + QUERY + FLOW + ROLES | none | module alert → route/priority/owner → snooze/ack/escalate → linked action → resolve/audit |
| 70 | AI Commerce Copilot | NEEDS_FOUNDATION | ABILITIES + POLICY + AUDIT + all modules | F12 + F03 for knowledge retrieval + S01 | intent → authorized context/evidence → structured draft → validate/simulate → approve → deterministic Ability |
| 71 | Integration & Webhook Hub | DIRECT | WEBHOOKS/CONNECTIONS + REST + VAULT + JOBS + AUDIT + RATE LIMIT | none; provider certifications remain separate | configure connection → verify/auth → receive/send → retry/dedupe/reconcile → health/log |

---

# 4. High-leverage findings

## 4.1 Existing modules already cover whole application classes

The following source systems do not justify new dedicated runtimes:
- Product Q&A;
- Smart Collections;
- Product Lifecycle;
- Commerce Forms;
- Gift Registry;
- Customer Portal;
- B2B Company Accounts;
- Customer Service Center;
- Commerce Inbox/Chat;
- Order Operations Board;
- Order Exception Manager;
- Supplier Portal;
- Action Center;
- Integration Hub.

Their value comes from **prebuilt Solution Blueprints, Woo adapters and UX**, not duplicated data/workflow engines.

## 4.2 The biggest true gaps are not commerce-specific

Most blockers collapse into:
- behavioral analytics + session/journey;
- advanced search/index;
- typed decision/formula/scoring;
- ledger/movements;
- reservations/availability;
- placement/personalization;
- experimentation;
- documents;
- sync/ETL;
- geospatial;
- AI gateway;
- whole-solution composition.

These primitives also unlock non-commerce systems.

## 4.3 WooCommerce adapter is mandatory

WPE modules already reference WooCommerce “via adapter” in Query/Admin Columns/Workflow concepts. The source document raises that assumption into a first-class requirement.

A01 must expose supported Woo APIs/contracts for products, HPOS orders, carts, checkout blocks, gateways, shipping, stock and account placements without private storage assumptions.

## 4.4 Transactional truth cannot be generated by AI

The source document correctly requires deterministic engines for money, stock, taxes, shipping/payment eligibility and order state. WPE architecture should preserve that separation.

AI can:
- draft configuration;
- rank/recommend;
- summarize/explain;
- forecast;
- classify;
- generate content.

The owning deterministic module/adapters remain authority for state-changing operations.

---

# 5. Source-document architectural gaps found by WPE audit

The source plan is strong, but the following concepts need explicit platform ownership before implementation:

1. distinction between Event Bus and durable Analytics Event Store;
2. anonymous visitor/session/journey identity and consent lifecycle;
3. exact search-index security/invalidation model;
4. deterministic formula/money/scoring semantics;
5. immutable ledger model for wallet/loyalty/inventory/commissions;
6. resource reservation locking vs scheduled jobs;
7. placement registry separate from page-builder document ownership;
8. experiment assignment/exposure/statistical truth;
9. protected/private generated document handling;
10. incremental/bidirectional data sync semantics;
11. location/territory/geospatial semantics;
12. AI provider/model/knowledge/budget/evaluation control plane;
13. transaction/saga/reconciliation contract for multi-system mutations;
14. formal WooCommerce adapter profile and version certification;
15. whole-system Blueprint manifest/install/upgrade/drift lifecycle.

These gaps are planned in `FOUNDATIONAL-MODULE-GAP-PLAN.md` rather than duplicated inside individual Commerce OS systems.

---

# 6. Development gate

This audit changes planning scope only. It authorizes no WooCommerce hooks, checkout/cart mutations, payment/shipping rules, DB schemas, event tracking, indexes, ledger writes, reservations, AI provider calls or other executable work.