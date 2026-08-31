# ADR-0206 — WooCommerce Commerce Domain Adapter Executable Evidence Protocol

Date: **2026-08-29**  
Status: **Accepted — planning/evidence only**

## Context

ADR-0177 reserved the WooCommerce Commerce Domain Adapter as the commerce-specific adapter over the shared WPEssential foundations, and ADR-0180 fixed its technical evidence envelope as `WCA-001…WCA-176` across 16 groups. WP74 required that envelope to be expanded into deterministic executable-evidence fixtures before any runtime-readiness claim.

The adapter must integrate WooCommerce through supported Woo APIs, Data Stores and extension/provider contracts without creating a second source of truth for products, carts, checkout, orders, payments, refunds, tax, shipping, inventory or customer-commerce state. In particular, HPOS support must not rely on private-table assumptions, and unknown external payment/refund/shipping outcomes must remain reconcilable rather than being fabricated as success or failure.

## Decision

Accept `docs/QUALITY/WOOCOMMERCE-COMMERCE-DOMAIN-ADAPTER-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical detailed evidence protocol for A01 WooCommerce Commerce Domain Adapter.

The protocol fully enumerates:

- `WCA-001…011` Woo version/feature/capability detection and adapter bootstrap;
- `WCA-012…022` Product/Variation Data Source read/schema/Policy;
- `WCA-023…033` Customer identity/profile/adapters/privacy;
- `WCA-034…044` Cart/session/context/line-item identity/concurrency;
- `WCA-045…055` Checkout Blocks/classic contexts/field/placement compatibility;
- `WCA-056…066` HPOS Order Data Source/read/write abstraction/no private-table assumptions;
- `WCA-067…077` order item/refund/mutation idempotency/payment boundary;
- `WCA-078…088` stock/inventory reservation/ledger integration and third-party stock ownership;
- `WCA-089…099` coupon/discount/gift/pricing/tax-safe boundaries;
- `WCA-100…110` shipping method/rate eligibility/provider authority;
- `WCA-111…121` payment gateway eligibility/provider settlement authority;
- `WCA-122…132` My Account/portal/routes/download/protected access;
- `WCA-133…143` Woo events/Action Scheduler/webhook normalization/replay;
- `WCA-144…154` Multisite/store/site lifecycle/clone/import/restore;
- `WCA-155…165` high-product/order/cart concurrency/performance/HPOS query budget;
- `WCA-166…176` end-to-end product→cart→checkout→order→refund/fulfillment adapter golden suite.

## Non-negotiable boundaries accepted

- Adapter integration does not transfer WooCommerce commerce truth ownership to WPEssential.
- Product/variation read does not grant protected field/resource access.
- Product purchasability, stock, reservability and successful purchase are distinct facts.
- Cart is not order; checkout submission is not order success/payment authorization/capture/settlement.
- Woo order status is not bank/gateway settlement unless a certified gateway contract establishes that mapping.
- Woo refund request/object is not confirmed provider refund.
- Unknown payment/refund/shipping/provider outcome is not automatically failed; reconcile before unsafe replay.
- HPOS uses supported Woo APIs/Data Stores; private-table assumptions/direct writes are prohibited for certified adapter behavior.
- Duplicate/retried hooks, webhooks, Action Scheduler jobs and mutations preserve stable idempotency/business-operation identity.
- Stock quantity, hold/reservation, decrement and third-party inventory authority remain distinct; F05 Ledger does not become Woo stock/payment/order truth.
- Tax, shipping and payment facts remain with Woo/provider authorities and are not fabricated by generic WPE logic.
- Shipping rate/eligibility is not carrier acceptance/shipment/delivery guarantee.
- Payment-gateway eligibility is not authorization/capture/settlement.
- My Account/portal/download visibility is not protected-access authorization.
- Checkout Blocks and classic checkout retain separate compatibility evidence but identical server-side security/Policy boundaries.
- Multisite/store ownership and provider credentials remain site/tenant isolated and server-resolved.
- Clone/import/restore/staging cannot blindly activate production gateways, webhooks, scheduled jobs or external provider mappings.
- AI/MCP has no hidden privileged live-commerce mutation path.

## Evidence state

- WCA documented: **176/176**
- WCA executed: **0/176**
- A01 runtime certification: **0**
- Product implementation authorization: **0/56 / NOT GRANTED**

No WooCommerce product/cart/checkout/order/refund/stock/tax/shipping/payment/account/event/HPOS/provider runtime, AI/MCP session, test, benchmark or build occurred while creating or accepting this ADR.

## Consequences

WP74 is complete as a planning/evidence package. The reserved WP63…WP74 detailed universal-foundation/adapter sequence is now complete as documentation-only evidence design.

However, completion of WP74 does **not** mean the full project is ready for development approval. Repository governance summaries contain stale historical scope/work pointers, and many shared/supplemental evidence envelopes remain planning-only. The next safe planning package is **WP112 — P0 Final Pre-development Closure & Readiness Reconciliation Audit**. Its purpose is to reconcile current 56-surface scope, readiness/approval matrices, all remaining planning/evidence gaps and the exact conditions required before any lifecycle move to `AWAITING_DEVELOPMENT_APPROVAL`.

This ADR does not authorize implementation or runtime execution.