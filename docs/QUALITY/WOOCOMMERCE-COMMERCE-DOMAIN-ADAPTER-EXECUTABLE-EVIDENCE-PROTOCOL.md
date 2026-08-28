# WPEssential — WooCommerce Commerce Domain Adapter Executable Evidence Protocol

Status: **Accepted evidence design candidate / execution pending / no development authorization**  
Date: **2026-08-29**

Namespace: **WCA-001…WCA-176**  
Documented: **176/176**  
Executed: **0/176**  
Runtime certification: **0**

## 1. Purpose

This protocol fixes the executable evidence required before A01 — WooCommerce Commerce Domain Adapter can be called runtime-ready.

The adapter exists to integrate WPEssential capabilities with WooCommerce through supported WooCommerce APIs, Data Stores, extension points and declared provider contracts. It does **not** become a second commerce engine or silently replace WooCommerce as the canonical owner of product, variation, cart, checkout, order, tax, shipping, payment, refund, inventory or customer-commerce truth.

No fixture below has executed. No WooCommerce runtime call, product/cart/order/customer mutation, checkout submission, HPOS query/write, stock reservation, tax/shipping/payment provider request, Action Scheduler job, webhook delivery, AI/MCP call, benchmark, build or test is authorized by this protocol.

## 2. Non-negotiable truth boundaries

- `Adapter integration ≠ commerce truth ownership`.
- `Product/variation read ≠ authorization`; field/resource Policy still applies.
- `Product purchasable ≠ in stock ≠ reservable ≠ successfully purchasable`.
- `Variation selection ≠ inventory reservation`.
- `Cart ≠ order`; cart line/session state is not durable order/payment truth.
- `Checkout submission ≠ order success ≠ payment authorization ≠ capture ≠ settlement`.
- `Order status ≠ bank/gateway settlement` unless the declared gateway contract explicitly establishes the represented fact.
- `Refund request / Woo refund object ≠ confirmed provider refund`.
- Unknown payment/refund/shipping/provider outcome is not automatically failed; reconcile before unsafe replay.
- WooCommerce HPOS is accessed through supported Woo APIs/Data Stores; no private-table schema assumptions or direct private-table writes.
- Stable mutation/event identity and idempotency are required across hooks, retries, webhooks, queues and Action Scheduler.
- Stock quantity, reservation/hold, committed decrement and third-party inventory authority are distinct.
- F05 Ledger does not become Woo order/payment/stock truth and Woo stock does not become generic financial ledger truth.
- Coupons, discounts, gifts and pricing adjustments must compose Woo pricing/tax authority through certified extension contracts.
- Tax calculation is not invented by generic formula logic where Woo/tax-provider authority applies.
- Shipping eligibility/rate quote is not carrier acceptance, shipment creation, serviceability or delivery guarantee.
- Payment gateway eligibility is not payment authorization/capture/settlement.
- My Account/portal/download visibility is not protected-access authorization.
- Checkout Blocks and classic checkout are separate compatibility surfaces; client UI cannot bypass server-side validation/Policy.
- Woo events, webhooks and Action Scheduler jobs can duplicate, retry, reorder or arrive late; business effects must converge safely.
- Multisite/store/site/customer/order/product ownership and provider credentials remain site/tenant isolated and server-resolved.
- Clone/import/restore/staging cannot blindly reactivate production gateways, webhooks, scheduled jobs, live provider IDs or external write authority.
- AI/MCP may draft configuration, mappings or explanations only through normal Policy/approval gates; no hidden privileged live-commerce mutation path exists.

## 3. Canonical evidence groups

The 16 groups preserve the exact ownership fixed by `docs/QUALITY/UNIVERSAL-FOUNDATIONS-TECHNICAL-EVIDENCE-MASTER-PLAN.md`.

---

## 4. Executable evidence fixtures

### Group 1 — WCA-001…WCA-011 — Woo version/feature/capability detection and adapter bootstrap

- **WCA-001** — Detect WooCommerce presence/version through supported capability checks and return explicit unavailable state when absent.
- **WCA-002** — Detect required feature/API/Data Store capability separately from plugin version string; version alone cannot imply support.
- **WCA-003** — Detect HPOS enabled/disabled/compatibility state through Woo-supported feature APIs rather than table inspection.
- **WCA-004** — Detect Checkout Blocks/classic checkout availability and active context without assuming one universal checkout surface.
- **WCA-005** — Validate declared minimum/maximum tested Woo and WordPress compatibility profiles; unsupported combinations fail safely.
- **WCA-006** — Prove adapter bootstrap is idempotent and does not register duplicate hooks/services when loaded more than once.
- **WCA-007** — Validate missing optional Woo subsystem/provider yields explicit capability degradation rather than fatal boot or fabricated support.
- **WCA-008** — Prove site/network activation state is resolved server-side per store and cannot be overridden by request parameters.
- **WCA-009** — Validate adapter feature flags gate only supported integration paths and do not grant underlying Woo capabilities/permissions.
- **WCA-010** — Prove bootstrap logs/version diagnostics redact secrets and do not expose private provider configuration.
- **WCA-011** — Golden bootstrap matrix covers Woo absent, supported, unsupported, HPOS on/off, Blocks/classic and optional-provider combinations.

### Group 2 — WCA-012…WCA-022 — Product/Variation Data Source read/schema/Policy

- **WCA-012** — Read product identity/type/status through supported Woo APIs/Data Stores and preserve canonical Woo IDs.
- **WCA-013** — Read variation parent/attribute relationships without flattening variation identity into the parent product.
- **WCA-014** — Validate product/variation fields are exposed through a typed adapter schema with null/unknown semantics preserved.
- **WCA-015** — Prove private/protected product fields are filtered by canonical Policy/capability before Data Source output.
- **WCA-016** — Validate draft/private/trash product visibility follows Woo ownership/capability rules rather than query possession of an ID.
- **WCA-017** — Prove price reads distinguish regular, sale, current display/context values and do not fabricate tax-inclusive/exclusive meaning.
- **WCA-018** — Validate stock-status/quantity/backorder/manage-stock fields retain Woo semantics and third-party stock ownership provenance where applicable.
- **WCA-019** — Validate downloadable/virtual/subscription-like or extension-defined types preserve adapter capability state instead of coercing to simple-product semantics.
- **WCA-020** — Prove product meta exposure uses an allowlisted/registered mapping; arbitrary private meta is not exported automatically.
- **WCA-021** — Validate product cache invalidation keys include site/store, product/variation revision and material visibility/Policy context.
- **WCA-022** — Golden product/variation suite covers simple, variable, hidden/private, out-of-stock/backorder and extension-defined product profiles.

### Group 3 — WCA-023…WCA-033 — Customer identity/profile/adapters/privacy

- **WCA-023** — Distinguish WordPress user identity, Woo customer identity, guest checkout identity and session identity explicitly.
- **WCA-024** — Prove customer ID/email/order ownership cannot be claimed from request payload without authenticated/authorized resolution.
- **WCA-025** — Validate guest-to-authenticated transition does not silently merge unrelated customer/session histories.
- **WCA-026** — Read billing/shipping/profile fields through typed, Policy-projected adapters with field-level redaction.
- **WCA-027** — Validate customer email/phone/address remain privacy-governed and are absent from unrestricted logs/cache/debug payloads.
- **WCA-028** — Prove customer role/membership/entitlement remains distinct from Woo customer record existence.
- **WCA-029** — Validate duplicate-email/customer reconciliation follows explicit identity rules rather than automatic destructive merge.
- **WCA-030** — Validate customer data export includes only authorized subject/store data and records Woo/WPE provenance.
- **WCA-031** — Validate erasure/anonymization respects Woo retention/legal/accounting constraints and does not promise deletion where retention applies.
- **WCA-032** — Prove Multisite same-user-account scenarios do not imply cross-store customer/order access.
- **WCA-033** — Golden customer suite covers authenticated, guest, guest→login, duplicate identity, privacy export/erase and cross-store adversarial cases.

### Group 4 — WCA-034…WCA-044 — Cart/session/context/line-item identity/concurrency

- **WCA-034** — Validate cart identity is scoped to the active Woo session/context and is not treated as durable order identity.
- **WCA-035** — Prove cart line key/item identity remains distinct from product/variation/order-item identity.
- **WCA-036** — Validate add-to-cart uses Woo validation hooks/contracts and cannot bypass purchasability, quantity or extension validation.
- **WCA-037** — Validate variation attributes are normalized/validated against the selected variation before cart insertion.
- **WCA-038** — Prove duplicate add/retry semantics are explicit: merge quantity only where Woo/profile rules say so; otherwise preserve distinct line identity.
- **WCA-039** — Validate cart mutation rejects stale/invalid cart line references and surfaces conflict rather than mutating a different line.
- **WCA-040** — Prove concurrent cart updates preserve Woo session locking/concurrency semantics and do not silently lose quantities/fees/coupons.
- **WCA-041** — Validate cart totals are recomputed through Woo canonical calculation rather than trusted from client-supplied totals.
- **WCA-042** — Validate cart restore/persistent-cart merge follows explicit identity and freshness semantics; stale cart does not overwrite newer state silently.
- **WCA-043** — Prove cart/session caches are isolated by user/session/site/store and cannot leak personalized pricing or line items.
- **WCA-044** — Golden cart suite covers add/update/remove, variation lines, duplicate retry, concurrent mutation, guest/login transition and stale-cart conflict.

### Group 5 — WCA-045…WCA-055 — Checkout Blocks/classic contexts/field/placement compatibility

- **WCA-045** — Detect whether checkout interaction occurs through Blocks, classic shortcode/template or supported API context and select the certified adapter path.
- **WCA-046** — Validate checkout field registration uses supported extension points for each surface; unsupported DOM injection is rejected.
- **WCA-047** — Prove client-side field visibility/validation never replaces required server-side validation and authorization.
- **WCA-048** — Validate custom checkout field schema, required/optional rules, sanitization and persistence are versioned and context-aware.
- **WCA-049** — Validate Blocks Store API payload extensions use registered namespaces/schema and reject arbitrary unregistered fields.
- **WCA-050** — Prove classic-checkout posted values are nonce/session/context validated before business use.
- **WCA-051** — Validate checkout errors are mapped without leaking secrets/provider internals or converting unknown provider state into a false validation error.
- **WCA-052** — Validate placement/personalization from F07 cannot bypass Woo checkout eligibility or server-side field requirements.
- **WCA-053** — Prove checkout recalculation after address/shipping/payment changes uses Woo totals/tax/shipping authority.
- **WCA-054** — Validate unsupported theme/builder/Blocks version yields explicit compatibility degradation instead of silently missing required controls.
- **WCA-055** — Golden checkout-surface suite proves equivalent protected semantics across certified Blocks/classic contexts and adversarial client bypass attempts.

### Group 6 — WCA-056…WCA-066 — HPOS Order Data Source/read/write abstraction/no private-table assumptions

- **WCA-056** — Read order through Woo order APIs/Data Stores and preserve order ID/type/status/store provenance.
- **WCA-057** — Prove HPOS enabled/disabled storage difference is invisible behind supported Woo abstraction for certified adapter operations.
- **WCA-058** — Reject direct assumptions about `wp_posts`, `wp_postmeta` or HPOS private table layouts for order truth.
- **WCA-059** — Validate order reads distinguish customer-visible fields, operational fields and protected/private metadata under Policy.
- **WCA-060** — Validate order write/mutation uses Woo CRUD/data-store APIs and declared extension hooks, not direct private-table writes.
- **WCA-061** — Prove optimistic/stale mutation handling detects material order revision/state conflict before destructive overwrite where supported.
- **WCA-062** — Validate extension-defined order types/statuses remain typed unknown/extension states rather than coerced into core statuses.
- **WCA-063** — Prove HPOS compatibility declaration/evidence is version-specific and cannot be claimed from code comments/static declaration alone.
- **WCA-064** — Validate order queries use supported Woo query APIs with bounded fields/status/date/customer filters and Policy projection.
- **WCA-065** — Validate order cache/index invalidation follows canonical Woo mutations/events and cannot expose revoked customer/order data.
- **WCA-066** — Golden HPOS parity suite compares certified reads/writes/queries with HPOS on/off without private-table assumptions.

### Group 7 — WCA-067…WCA-077 — order item/refund/mutation idempotency/payment boundary

- **WCA-067** — Validate order item identity/type/product/variation/quantity/totals are read through Woo item APIs and remain distinct from cart lines.
- **WCA-068** — Prove creation/update/cancel/refund adapter operations require stable logical operation/idempotency identity.
- **WCA-069** — Validate duplicate hook/webhook/retry for the same logical order mutation converges without duplicate item/refund/business effect.
- **WCA-070** — Distinguish order creation from payment authorization/capture/settlement; no successful order object implies settled payment.
- **WCA-071** — Distinguish Woo refund object/request from gateway/provider refund confirmation and preserve both states/provenance.
- **WCA-072** — Validate unknown refund/payment provider outcome enters reconciliation state before unsafe replay.
- **WCA-073** — Prove partial refund amount/item/tax/shipping semantics use Woo APIs and canonical decimal/currency handling.
- **WCA-074** — Validate order status transition request is checked against Woo/domain rules and does not fabricate provider settlement.
- **WCA-075** — Prove cancellation/failed order does not imply external authorization/capture/refund reversal unless provider evidence confirms it.
- **WCA-076** — Validate mutation audit records actor, source event, operation identity, before/after semantic state and external references without storing secrets.
- **WCA-077** — Golden order/refund suite covers paid/unpaid, partial/full refund, duplicate retry, provider timeout and status/provider disagreement.

### Group 8 — WCA-078…WCA-088 — stock/inventory reservation/ledger integration and third-party stock ownership

- **WCA-078** — Read Woo stock management/quantity/status/backorder semantics without converting status into exact quantity when quantity is unknown.
- **WCA-079** — Distinguish available/display quantity, reserved/held quantity, committed decrement and external inventory authority.
- **WCA-080** — Validate stock reservation/hold uses supported Woo/extension mechanisms and does not reuse F05 financial ledger hold semantics.
- **WCA-081** — Prove cart presence alone is not guaranteed inventory reservation unless the active Woo/profile mechanism explicitly establishes a hold.
- **WCA-082** — Validate concurrent checkout/stock decrement prevents certified oversell only to the extent guaranteed by declared Woo/inventory provider profile.
- **WCA-083** — Prove third-party inventory plugin/provider remains authoritative when configured; WCA mirrors/adapts instead of becoming competing stock truth.
- **WCA-084** — Validate restock on cancellation/refund follows Woo/provider rules and is idempotent under duplicate events.
- **WCA-085** — Validate variable product stock inheritance/managed-at-parent vs variation semantics explicitly.
- **WCA-086** — Prove low-stock/out-of-stock notifications are operational signals, not transactional proof of available units.
- **WCA-087** — Validate F05 ledger may record owned quantity/value movements only under an explicit integration contract and cannot silently override Woo stock.
- **WCA-088** — Golden inventory suite covers simple/variation stock, backorders, concurrent checkout, provider-owned stock, cancellation/restock and duplicate events.

### Group 9 — WCA-089…WCA-099 — coupon/discount/gift/pricing/tax-safe boundaries

- **WCA-089** — Validate coupon existence/validity/application through Woo APIs/rules and never trust client-calculated discount values.
- **WCA-090** — Validate coupon usage limits/customer restrictions/product/category constraints against current authoritative Woo state.
- **WCA-091** — Prove duplicate apply/remove coupon requests converge without duplicate discounts or usage-count corruption.
- **WCA-092** — Validate custom discount/gift rules compose through certified Woo pricing/fee/coupon extension contracts and retain provenance.
- **WCA-093** — Prove F04 formula/score output may propose a bounded price/discount input but does not bypass Woo pricing/tax validation.
- **WCA-094** — Distinguish list/regular/sale/cart-adjusted/order-recorded prices and preserve currency/decimal semantics.
- **WCA-095** — Validate tax class/status/location inputs are passed to Woo/tax authority; generic WPE formulas do not fabricate tax owed.
- **WCA-096** — Prove inclusive/exclusive tax display values are not conflated with canonical tax amounts recorded on the order.
- **WCA-097** — Validate external tax-provider timeout/unknown state is surfaced/reconciled and not silently replaced by zero tax unless explicit fallback policy allows it.
- **WCA-098** — Validate rounding/distribution of discounts/tax across items uses Woo-certified semantics and deterministic currency precision.
- **WCA-099** — Golden pricing suite covers coupon eligibility, stacked rules, gift/fee, tax inclusive/exclusive, provider timeout and rounding edge cases.

### Group 10 — WCA-100…WCA-110 — shipping method/rate eligibility/provider authority

- **WCA-100** — Read shipping packages/methods/rates through Woo APIs and preserve package/method/rate identity and provider provenance.
- **WCA-101** — Validate rate eligibility against current address, cart contents, zones/classes and provider/profile requirements.
- **WCA-102** — Prove displayed/selected rate is not carrier acceptance, label creation, shipment booking or delivery guarantee.
- **WCA-103** — Validate shipping-zone/geospatial predicates may consume F11 facts but territory match alone does not authorize a shipping service.
- **WCA-104** — Validate provider rate timeout/unknown outcome is not treated as definitive no-service without declared fallback/retry semantics.
- **WCA-105** — Prove client-supplied method/rate/cost is revalidated server-side at checkout/order creation.
- **WCA-106** — Validate rate cache keys include material cart/address/provider/profile/store dimensions and expiry/freshness rules.
- **WCA-107** — Validate free-shipping/coupon/threshold rules compose with Woo totals and cannot be fabricated from stale client totals.
- **WCA-108** — Distinguish shipping rate, shipment/fulfillment entity, tracking reference and delivery event as separate facts.
- **WCA-109** — Validate third-party shipping plugin/provider authority remains external and unknown create/label outcomes reconcile before replay.
- **WCA-110** — Golden shipping suite covers zones, multiple packages, stale rate, provider timeout, free shipping, revalidation and unknown label/create outcome.

### Group 11 — WCA-111…WCA-121 — payment gateway eligibility/provider settlement authority

- **WCA-111** — Discover gateway availability/eligibility through Woo/gateway APIs for the current checkout context rather than static configured list.
- **WCA-112** — Prove gateway eligibility does not imply successful authorization, capture, settlement or refund capability.
- **WCA-113** — Validate checkout payment request uses gateway-supported flow and never logs/exposes raw secrets or prohibited card data.
- **WCA-114** — Distinguish gateway intent/session/reference, authorization, capture, settlement, failure, cancellation and refund states explicitly.
- **WCA-115** — Validate redirect/async/webhook payment flows bind provider callbacks to the correct site/store/order/operation identity.
- **WCA-116** — Prove timeout/connection loss after payment submission enters unknown/reconciliation state rather than immediate unsafe retry.
- **WCA-117** — Validate duplicate gateway webhook/callback is deduped by provider event/operation identity and cannot duplicate order/payment effects.
- **WCA-118** — Prove Woo order status alone cannot be used as bank settlement evidence unless the certified gateway contract explicitly maps it.
- **WCA-119** — Validate amount/currency/order-reference mismatches on provider callbacks fail closed/quarantine rather than updating the order.
- **WCA-120** — Validate manual admin payment-status mutation does not fabricate provider settlement and retains explicit manual provenance.
- **WCA-121** — Golden payment suite covers synchronous, redirect/async, duplicate callback, timeout unknown, mismatch, capture/refund and manual-status cases.

### Group 12 — WCA-122…WCA-132 — My Account/portal/routes/download/protected access

- **WCA-122** — Validate My Account route/endpoint registration through supported Woo rewrite/endpoint APIs with deterministic collision handling.
- **WCA-123** — Prove route/menu visibility does not authorize underlying customer/order/download data.
- **WCA-124** — Validate current customer can access only orders/resources owned or explicitly shared under canonical Woo/Policy rules.
- **WCA-125** — Validate guessed order/download/resource IDs fail closed and do not reveal existence through differential metadata where Policy forbids it.
- **WCA-126** — Validate downloadable-product access uses Woo permission/download APIs and current expiry/download-count/ownership state.
- **WCA-127** — Prove signed/private download URL/token scope, expiry and revocation cannot be bypassed by cached/public media URLs.
- **WCA-128** — Validate guest order lookup/authentication uses Woo-supported verification semantics and rate-limit/privacy controls.
- **WCA-129** — Validate account address/profile edits reauthorize fields and do not trust hidden UI or client ownership claims.
- **WCA-130** — Prove personalized My Account caches are isolated by user/site/store/Policy and invalidated on ownership/permission change.
- **WCA-131** — Validate AI/MCP customer/order explanation receives only Policy-projected data and cannot enumerate protected accounts/orders.
- **WCA-132** — Golden account suite covers IDOR attempts, download expiry, guest lookup, cache leakage, profile edit and cross-store ownership.

### Group 13 — WCA-133…WCA-143 — Woo events/Action Scheduler/webhook normalization/replay

- **WCA-133** — Normalize Woo hooks/events into typed adapter events with source, site/store, entity ID, revision/time and stable event identity where available.
- **WCA-134** — Prove hook invocation count/order is not assumed to be exactly once or globally ordered.
- **WCA-135** — Validate duplicate hook/event converges through event/business-operation idempotency without duplicate effects.
- **WCA-136** — Validate out-of-order events use current entity/provider state or sequence contract rather than blindly regressing state.
- **WCA-137** — Validate Action Scheduler task definition includes stable purpose/entity/operation identity and safe retry semantics.
- **WCA-138** — Prove failed/timeout scheduled task distinguishes retryable, terminal and unknown-external-outcome states.
- **WCA-139** — Validate webhook signature/authentication/site/store binding before payload is trusted.
- **WCA-140** — Validate webhook replay/duplicate provider delivery dedupes by provider/event identity and records attempt provenance.
- **WCA-141** — Prove dead-letter/manual replay preserves original logical operation identity and does not become a fresh business operation silently.
- **WCA-142** — Validate scheduler/webhook payloads/logs redact credentials, payment secrets and protected customer data.
- **WCA-143** — Golden async suite covers duplicate/reordered hooks, scheduled retry, timeout unknown, webhook replay, dead-letter and manual replay.

### Group 14 — WCA-144…WCA-154 — Multisite/store/site lifecycle/clone/import/restore

- **WCA-144** — Validate product/customer/cart/order/provider identities include/respect server-resolved store/site ownership.
- **WCA-145** — Prove same numeric Woo entity ID on different sites cannot collide in caches, idempotency keys or external mappings.
- **WCA-146** — Validate network-shared configuration separates reusable template/defaults from site-local credentials, IDs and commerce data.
- **WCA-147** — Prove same WordPress network user does not automatically gain cross-store customer/order/admin access.
- **WCA-148** — Validate site creation/clone starts live gateway/shipping/tax/webhook/provider write authority disabled/quarantined unless explicitly approved.
- **WCA-149** — Validate cloned/imported orders/products/customers preserve source provenance but receive safe destination identity/mapping semantics where required.
- **WCA-150** — Prove restore/rollback cannot roll back external payment/shipping/tax/provider facts and requires reconciliation before new writes.
- **WCA-151** — Validate production webhook endpoints/secrets/provider IDs are not blindly copied into staging/clone environment.
- **WCA-152** — Validate Action Scheduler jobs from another environment are disabled/quarantined/deduped before activation.
- **WCA-153** — Validate site archive/delete follows retention/export/provider cleanup policy without deleting shared/network configuration accidentally.
- **WCA-154** — Golden Multisite/environment suite covers ID collision, same-user isolation, clone quarantine, restore reconciliation and stale external mappings.

### Group 15 — WCA-155…WCA-165 — high-product/order/cart concurrency/performance/HPOS query budget

All fixtures in this group are **reserved executable benchmarks**. No performance claim is certified until executed on declared Woo/WordPress/PHP/DB versions, HPOS state, plugins, hardware and dataset.

- **WCA-155** — Benchmark product/variation Data Source reads across 10K/100K/1M catalog profiles with declared cache/query budgets.
- **WCA-156** — Benchmark HPOS order queries across 100K/1M order profiles with representative customer/status/date filters.
- **WCA-157** — Benchmark cart calculation/update concurrency at declared line-item counts and parallel session mutation profiles.
- **WCA-158** — Benchmark checkout totals/tax/shipping recalculation at representative cart/address/provider complexity.
- **WCA-159** — Benchmark concurrent order creation/idempotency contention with declared payment-provider simulator/profile.
- **WCA-160** — Benchmark stock decrement/reservation contention for hot products/variations under declared inventory owner profile.
- **WCA-161** — Benchmark webhook/event normalization and dedupe burst processing with duplicate/reordered delivery distributions.
- **WCA-162** — Benchmark Action Scheduler backlog/retry throughput and recovery without exceeding provider quotas or duplicating business effects.
- **WCA-163** — Benchmark My Account/order/download queries at large customer histories with Policy filtering and no N+1 regressions.
- **WCA-164** — Benchmark Multisite aggregate/admin operational views only for declared authorized site sets and query budgets.
- **WCA-165** — Record p50/p95/p99 latency, DB queries, memory, external calls, failures/retries and hardware/profile for every certified benchmark; paper estimates do not count.

### Group 16 — WCA-166…WCA-176 — end-to-end product→cart→checkout→order→refund/fulfillment adapter golden suite

- **WCA-166** — Golden happy path: authorized product/variation read → cart add → totals → checkout → order creation while keeping payment state distinct.
- **WCA-167** — Golden variable-product path validates attributes, stock ownership, cart identity and order-item identity through conversion.
- **WCA-168** — Golden guest→authenticated checkout path preserves correct customer/session/order ownership without cross-account merge leakage.
- **WCA-169** — Golden Checkout Blocks and classic checkout paths produce equivalent server-side validation/Policy guarantees for certified fields.
- **WCA-170** — Golden async payment path covers provider redirect/callback, duplicate webhook, unknown timeout reconciliation and final order/payment provenance.
- **WCA-171** — Golden tax/shipping path covers provider/Woo authority, stale rate revalidation, multi-package shipping and no fabricated provider facts.
- **WCA-172** — Golden stock-concurrency path covers two competing checkouts, reservation/decrement semantics and declared oversell guarantees.
- **WCA-173** — Golden partial/full refund path distinguishes Woo refund record from provider refund and proves idempotent duplicate replay.
- **WCA-174** — Golden My Account/download path proves ownership, expiry/revocation, guest lookup and IDOR resistance.
- **WCA-175** — Golden Multisite clone/restore path proves site isolation, provider quarantine, job/webhook safety and external-fact reconciliation.
- **WCA-176** — Full adversarial regression combines stale client totals, forged IDs, duplicate/out-of-order hooks, provider timeout, HPOS on/off, privacy/Policy and AI/MCP attempts without bypass or fabricated commerce truth.

---

## 5. Required evidence artifact for every executed fixture

A later authorized execution must record at minimum:

- fixture ID and protocol revision;
- WooCommerce, WordPress, PHP, DB and relevant extension/provider versions;
- HPOS and Checkout Blocks/classic feature states;
- site/store/Multisite topology;
- exact input/preconditions and Policy/identity context;
- stable operation/event/idempotency identities where relevant;
- actual observed output/state transition;
- Woo/provider API/Data Store/hook path used;
- external provider outcome classification (`known-success`, `known-failure`, `unknown/reconcile`);
- DB/query/memory/time/concurrency measurements where relevant;
- privacy/security redactions applied to stored evidence;
- pass/fail/degraded/blocked result and reproducible artifact reference.

Static documentation, mocks, screenshots without runtime provenance or paper estimates cannot mark a fixture executed.

## 6. Stop-the-line conditions

Stop certification immediately if evidence shows:

- direct private HPOS/order table dependency or write where supported Woo APIs/Data Stores should be used;
- cross-user/site/store customer/order/cart/download leakage;
- client/UI visibility used as authorization;
- duplicate order/refund/payment/stock/shipping side effect from replay/retry;
- unknown payment/refund/shipping/provider outcome treated as known failure/success without reconciliation;
- Woo order status presented as provider/bank settlement without certified contract;
- fabricated tax/shipping/payment/stock-provider fact;
- stale/unvalidated cart or checkout values accepted as authoritative;
- stock authority conflict creating silent competing inventory truth;
- clone/restore activating production gateways/webhooks/jobs/provider mappings unexpectedly;
- secret/payment/private customer data leakage in logs/evidence;
- static evidence promoted to runtime certification.

## 7. Current evidence truth

- WCA documented: **176/176**
- WCA executed: **0/176**
- A01 runtime certification: **0**
- Product implementation authorization: **0/56 / NOT GRANTED**

No WooCommerce runtime operation occurred while creating this protocol.