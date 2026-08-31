# WPEssential — WooCommerce Commerce Domain Adapter Exhaustive Specification

Status: **Phase 0 planning / adapter contract / no WooCommerce runtime implementation authorized**  
Date: 2026-08-28  
Adapter key candidate: `wpe.adapter.woocommerce`

## 1. Purpose

The WooCommerce Commerce Domain Adapter (`A01`) is the supported bridge between generic WPEssential platform primitives and WooCommerce commerce semantics.

It exists so Solution Blueprints can use products, carts, checkout, orders, stock, shipping, payment eligibility and My Account contexts without:
- assuming legacy order-post storage;
- reaching into private WooCommerce internals casually;
- reimplementing WooCommerce calculations;
- letting generic Fields/Workflow/AI bypass transactional rules.

The adapter is not a replacement for WooCommerce. WooCommerce and exact payment/shipping/tax/subscription providers remain authoritative for the operations they own.

---

# 2. Compatibility targets

Certification matrix must separately cover:
- WooCommerce core version;
- WordPress version;
- PHP version;
- HPOS enabled/disabled only where Woo supports both and WPE explicitly certifies both;
- Cart/Checkout Blocks version/profile;
- classic templates where supported;
- Store API surface used;
- REST API surface used;
- selected public extension points;
- payment/shipping extension families;
- Multisite mode;
- object cache profile;
- known checkout/cart caching conflicts;
- supported subscription/provider adapters separately.

No version range is marked Supported until executable evidence exists.

---

# 3. Admin navigation

`WPEssential → Integrations → WooCommerce`

Tabs:
1. Overview
2. Compatibility
3. Data Sources
4. Events
5. Abilities / Actions
6. Cart & Checkout
7. Orders & Fulfillment
8. Inventory
9. Shipping
10. Payments
11. Customer / My Account
12. Placement Slots
13. Diagnostics
14. Feature Certification
15. Settings

The adapter should auto-detect WooCommerce where possible; it must not expose mutation features merely because Woo is active.

---

# 4. Overview

Display:
- WooCommerce installed/active/version;
- HPOS state;
- Woo feature compatibility declarations;
- Blocks/cart/checkout state;
- active theme template mode hints;
- active payment gateways count;
- active shipping methods/zones count;
- product/order/customer counts only through bounded supported APIs/query where safe;
- WPE adapter version;
- certification profile;
- degraded/unsupported features;
- last compatibility check;
- high-risk third-party conflicts detected;
- links to Commerce Solution Blueprints.

No “fully compatible” badge unless exact tested profile satisfies certification matrix.

---

# 5. Canonical commerce identity and ownership

Adapter exposes canonical WPE Data Source identities that map to Woo-owned objects.

Candidate entity keys:
- `woo.product`
- `woo.variation`
- `woo.product_category`
- `woo.product_tag`
- `woo.customer`
- `woo.cart_context`
- `woo.cart_line`
- `woo.checkout_context`
- `woo.order`
- `woo.order_item`
- `woo.refund`
- `woo.coupon`
- `woo.shipping_zone`
- `woo.shipping_method_instance`
- `woo.payment_gateway_profile`
- `woo.stock_item_view`
- `woo.download_permission_view`

WPE does not claim ownership of these objects.

WPE definitions may attach metadata/relations only through supported extension mechanisms and their own canonical storage contracts.

---

# 6. Product & variation Data Source

## Read fields
Subject to Woo public/supported API availability:
- ID;
- type;
- status;
- name;
- slug;
- SKU;
- parent ID;
- price values through Woo price APIs/context, not raw guessed meta;
- regular/sale display/value where semantically safe;
- taxable/tax class refs;
- stock management status;
- stock status;
- quantity view where Woo supports;
- backorder state;
- sold individually;
- catalog visibility;
- categories/tags;
- attributes;
- dimensions/weight;
- downloadable/virtual flags;
- downloads metadata subject to protection;
- images/gallery references;
- date created/modified;
- variation attributes;
- permalink/public URL where appropriate;
- extension-provided fields only through registered adapter extensions.

## Query capabilities
- product/variation ID;
- SKU;
- status;
- type;
- category/taxonomy;
- stock status;
- price ranges where supported;
- attributes;
- dates;
- search;
- pagination;
- ordering;
- custom WPE fields/relations through WPE Query composition.

Search & Discovery Studio should use F03 for indexed relevance; Woo product Data Source remains origin truth.

## Write abilities
Separate risk-classified abilities, not generic unrestricted CRUD:
- create product draft;
- update approved product properties;
- change publication status subject to Policy;
- update catalog visibility;
- update selected stock settings through inventory contract;
- update price fields only through explicit commerce pricing Ability;
- attach WPE metadata/relations;
- media associations through safe API.

Each ability declares unsupported product types/extensions rather than guessing.

---

# 7. Customer Data Source

Read:
- WordPress user/customer identity binding;
- Woo customer ID where applicable;
- billing/shipping addresses through Woo APIs;
- order summary aggregates only through bounded query/analytics contracts;
- account creation dates;
- marketing consent only through authoritative consent source/adapters;
- WPE custom profile fields/relations.

Sensitive data Policy:
- role/capability/resource Policy;
- address/phone/email not public dynamic tokens by default;
- guest order customer facts are order-scoped, not automatically global customer profiles;
- account identity remains WordPress authority.

Write abilities:
- update own billing/shipping fields via validated self-service profile;
- admin update through explicit capability;
- never generic password/security-token mutation.

---

# 8. Cart Context adapter

## Cart Context read model
Candidate fields:
- cart/session identity opaque reference;
- principal/customer context;
- line count;
- quantity count;
- line items;
- subtotal/totals display views;
- coupons applied;
- shipping packages/options view;
- selected shipping methods;
- fees view;
- tax display;
- currency;
- requires shipping;
- cart weight/dimensions aggregate if Woo exposes reliably;
- notices/errors;
- checkout readiness signals.

These are runtime context views, not durable WPE business truth.

## Cart line read
- line key opaque;
- product/variation IDs;
- quantity;
- variation attributes;
- line price/tax/totals view;
- custom cart item data only through registered schema/adapter;
- bundled/configured component references through certified extension adapter.

## Cart mutation abilities
Each re-runs Woo validation/calculation:
- add product/variation;
- update quantity;
- remove item;
- restore removed item where Woo semantics support;
- apply/remove coupon;
- add configured WPE cart metadata through declared namespace;
- select shipping method where supported;
- empty cart only high-impact confirmed action.

Ability output includes:
- result;
- recalculated cart summary;
- notices/reason codes;
- cart version/fingerprint candidate for stale-client detection.

## Idempotency
External/API/workflow add-to-cart operations require explicit idempotency semantics if retryable. Browser ordinary UI click semantics are separate.

## MUST NOT
- alter raw cart session storage directly;
- trust client price/totals;
- use stale cached eligibility after cart changed;
- let recommendation/AI directly modify cart without typed Ability and applicable approval/user intent.

---

# 9. Checkout Context adapter

Expose typed context:
- cart summary;
- customer/principal;
- billing/shipping country/state/postcode/city only as allowed;
- shipping requirement;
- available shipping rates/methods through Woo;
- selected shipping;
- available payment gateways through Woo after native/provider filtering;
- checkout fields registered;
- order notes capability;
- totals;
- coupons;
- fees;
- tax display;
- blocks/classic channel;
- checkout validation errors.

WPE rules may contribute constraints/filter decisions only through certified extension points.

---

# 10. Checkout Field integration

WPE Checkout Field Blueprint must distinguish:
- Woo core required field;
- Woo editable optional field;
- WPE custom checkout field;
- third-party owned field;
- display-only component.

Per field:
- key/namespace;
- channel support: Blocks/classic;
- type;
- required;
- location/step;
- validation;
- conditional display;
- persistence destination;
- admin/order/customer display;
- privacy class;
- edit policy;
- API exposure;
- migration/uninstall behavior.

Core tax/payment/shipping-critical fields cannot be disabled if it would create unsupported checkout semantics.

---

# 11. Checkout layout / placement contract

A01 registers available Commerce Placement Slots for F07.

Candidate slots only where reliable supported APIs exist:
- product summary areas;
- before/after add-to-cart areas;
- archive product card regions where adapter supports;
- mini-cart/cart drawer WPE-owned shell slots;
- cart page before/after items/totals/coupon/checkout CTA;
- checkout contact/address/shipping/payment/order-summary adjunct slots;
- thank-you/order-received slots;
- My Account dashboard/order/download/address adjunct slots;
- email placements only through Email Builder, not frontend HTML reuse.

For each slot:
- Blocks support;
- classic support;
- hook/block API identity;
- allowed components;
- mutable/replace capability;
- context schema;
- cache/asset notes;
- accessibility requirements;
- certification state.

No private block document mutation.

---

# 12. Discount / promotion contribution contract

WooCommerce remains authoritative calculator/order total runtime.

WPE F04/Rules may define promotion intent but A01 compiles allowed profiles into Woo-compatible deterministic adjustments.

Planned promotion families:
- automatic percentage/fixed product/cart discounts where supported;
- BOGO / X→Y profiles;
- quantity/tiered rules;
- category/product conditions;
- customer/company/segment context;
- shipping discount/surcharge only through appropriate hook family;
- coupon-code mapping where using Woo coupon object is appropriate;
- campaign activation/effective date;
- usage/budget maintained by owning WPE definition where exact atomic semantics exist.

Must define:
- tax handling;
- rounding;
- stacking/combinability;
- priority;
- exclusions;
- max discount;
- per-order/per-user/global limits;
- refunds/order edits implications;
- reporting attribution.

Do not implement generic raw negative fees as universal discount shortcut without semantic evidence.

---

# 13. Shipping adapter

## Read model
- zones;
- locations;
- method instances;
- active status;
- method IDs/titles;
- package/rate results from Woo context;
- selected methods;
- shipping classes refs;
- address/cart requirements.

## Rule contribution
WPE can:
- hide/show eligible rates/methods through supported filters;
- add explanatory reason codes outside sensitive details;
- apply certified surcharge/discount profile;
- select preferred method only when user/business policy permits;
- connect F11 service zones and F06 delivery slots as additional constraints.

## External carrier integrations
Labels, live carrier quotes, tracking, address validation remain provider adapter capabilities under Connections. A01 normalizes references; it does not invent carrier success.

## Failure semantics
- no rates;
- provider timeout;
- unsupported address;
- stale cart;
- split packages;
- method disappeared after recalculation;
- user explanation.

---

# 14. Payment adapter

## Gateway read
- gateway ID;
- title/description safe presentation;
- enabled state;
- availability result for current checkout;
- supports metadata where Woo provides public feature flags;
- selected gateway;
- provider extension identity.

## WPE rule contribution
- show/hide via deterministic Policy/conditions using supported filter contract;
- surcharges/discounts only through certified calculation profile;
- COD amount/segment rules;
- company invoice terms visibility;
- risk gate from F04 may be an input but merchant policy owns final rule.

## Hard boundaries
- WPE does not store card data;
- gateway provider owns payment authorization/capture/settlement;
- gateway hidden ≠ payment failed;
- AI cannot autonomously extend credit;
- payment result webhook/event must reconcile to authoritative order/provider state.

---

# 15. Order Data Source / HPOS contract

A01 must use WooCommerce supported order APIs/query abstractions compatible with HPOS rather than direct `wp_posts/wp_postmeta` assumptions.

## Read
- order ID/number;
- status;
- dates;
- customer relation;
- billing/shipping data under Policy;
- currency;
- totals/tax/shipping/discount/refund views;
- payment method refs;
- transaction IDs only under strict visibility;
- line items;
- shipping items;
- fee/coupon items;
- notes selected safe classes;
- metadata via registered schema;
- download permission view;
- refunds;
- fulfillment/shipment references from WPE/extension adapters.

## Query
- ID/number;
- status;
- customer;
- date;
- product/item relation only through supported Woo query strategy;
- totals range where supported;
- payment/shipping method refs;
- custom WPE operational data via WPE tables/relations, not by inventing order storage joins.

## Order events
Normalize supported Woo hooks into versioned WPE events without assuming hook fire = final external payment/fulfillment truth.

Candidate events:
- order created;
- order status changed;
- order paid indicator;
- order completed;
- order cancelled;
- refund created;
- order note/event selected classes;
- order item changes through adapter;
- checkout/order creation failure diagnostics.

Duplicate/out-of-order event consumers remain idempotent.

---

# 16. Order mutation abilities

Every mutation has separate input/output/permission/idempotency/recovery contract.

Candidate abilities:

### `woo.order.add_note`
- customer/private note classes explicit;
- sanitized content;
- notification behavior explicit.

### `woo.order.update_address`
- allowed fields;
- order-state window;
- tax/shipping/payment downstream implications surfaced;
- self-service vs staff policies separate.

### `woo.order.add_item/remove_item/update_quantity`
High-risk:
- only supported order states/gateway contexts;
- recalculate totals/taxes;
- inventory effects;
- payment difference plan;
- refund/additional payment requirement;
- fulfillment lock;
- idempotency;
- audit.

### `woo.order.change_shipping_method`
- rate availability/recalculation;
- fulfillment stage gate;
- payment delta.

### `woo.order.refund`
Very high risk:
- amount/items/tax/shipping inputs;
- refund reason;
- restock option;
- payment gateway API capability separate;
- manual vs gateway refund truth;
- unknown external outcome;
- duplicate prevention;
- re-auth/approval threshold;
- immutable audit.

### `woo.order.cancel`
- state eligibility;
- payment/stock implications;
- provider cancellation not assumed.

Self-Service Order Editing Solution uses a Saga/transaction plan composed from these abilities; it must not perform direct arbitrary order edits.

---

# 17. Fulfillment, shipment & package extension contract

Woo core Order remains commercial order. WPE Solution Blueprints may own operational Shipment/Package objects in WPE tables/entities.

A01 provides:
- order/item identity linkage;
- item quantities eligible for allocation;
- fulfillment lock/state mappings;
- customer order timeline placement;
- supported third-party shipment/tracking adapter hooks;
- order status effects only through explicit workflow rules.

Shipment objects must not masquerade as native Woo objects if Woo does not own them.

---

# 18. Inventory / stock contract

Woo stock remains authoritative for Woo purchasability unless a certified WPE Multi-location Inventory profile explicitly becomes the stock source through an accepted adapter architecture.

## Baseline read
- manages stock;
- stock quantity view;
- stock status;
- backorders;
- low-stock threshold where supported;
- product/variation identity.

## Stock mutation
Typed abilities only:
- increase/decrease/set through Woo-supported stock functions where allowed;
- source reason/reference mandatory for WPE workflows;
- idempotency for retries;
- audit;
- order/return/receipt integration avoids double adjustment.

## F05 multi-location profile
If WPE Inventory Ledger is enabled:
- WPE ledger owns location balances/reservations;
- an explicit aggregation/synchronization adapter maps sellable available stock to Woo;
- Woo order events generate idempotent WPE movements;
- cancellation/refund/return semantics explicit;
- reconciliation compares Woo aggregate vs WPE ledger;
- source-of-truth mode cannot be ambiguous.

No dual uncontrolled stock writers.

---

# 19. Coupon adapter

Read:
- coupon identity/code under permissions;
- type;
- amount;
- usage restrictions/limits through public Woo APIs;
- expiry;
- individual use/free shipping where available.

Actions:
- create campaign-generated coupon with explicit namespace/source metadata;
- update/pause/expire only WPE-owned/generated coupons by default;
- inspect third-party/manual coupons;
- delete generated coupon only after dependency/usage impact.

Campaigns should prefer WPE promotion definitions when automatic behavior is desired rather than creating huge coupon inventories without reason.

---

# 20. Product/customer/order events taxonomy

A01 Event Adapter defines stable WPE event schemas mapping Woo hooks.

Every event records:
- WPE event key/version;
- site scope;
- Woo object ID/type;
- relevant before/after state when reliably available;
- occurred/observed timestamps;
- source hook/profile;
- correlation/request/order/cart identity where safe;
- actor/principal class where known;
- payload data minimization;
- dedupe/idempotency hint;
- whether event is fact/observation/transition request.

Event examples:
- `commerce.product.created`
- `commerce.product.updated`
- `commerce.product.stock_changed`
- `commerce.cart.item_added`
- `commerce.cart.updated`
- `commerce.checkout.started` only with explicit instrumentation, not inferred from page load blindly;
- `commerce.order.created`
- `commerce.order.status_changed`
- `commerce.order.paid_observed`
- `commerce.order.refund_created`
- `commerce.customer.created`

Behavioral events such as product viewed/search impression belong to F02 instrumentation, not ordinary Woo hooks alone.

---

# 21. My Account / Customer Portal adapter

Expose supported Woo customer components/data to F01/F07/Frontend Dashboard:
- orders list/detail;
- downloads;
- addresses;
- payment method management only via Woo/provider-supported component, never copied card UI;
- account details through secure WordPress/Woo flow;
- logout;
- subscriptions/bookings/quotes only through certified extension adapters;
- custom WPE portal routes.

A WPE Customer Portal may replace/complement navigation where safe while preserving authorization and provider-required flows.

---

# 22. Admin integration

WPE may provide:
- Admin Columns on supported product/order screens through adapters;
- dashboard stats via bounded Query;
- order operations views using Woo Data Source;
- quick actions bound to typed abilities;
- links to WPE workflow/exception/audit traces.

It must not inject conflicting React/runtime assets globally into Woo admin screens.

---

# 23. Blocks compatibility

For each WPE checkout/cart extension declare:
- supported Cart block integration point;
- supported Checkout block integration point;
- Store API extension schema if used;
- client data store interaction through public API;
- server validation;
- persistence;
- compatibility with classic channel;
- block version/profile;
- asset scope;
- hydration/render behavior;
- checkout error presentation;
- accessibility.

No assumption that PHP classic hooks automatically affect Blocks.

---

# 24. Classic template compatibility

Where reasonable:
- use documented Woo hooks/filters/template extension points;
- avoid wholesale template copies;
- if template override unavoidable for a Solution component, version compatibility and fallback must be explicit;
- theme override conflicts surface in Diagnostics.

---

# 25. REST / Store API / external API exposure

Generic WPE REST endpoints over Woo Data Sources still apply:
- explicit auth;
- Policy;
- field allowlist;
- pagination;
- rate limit;
- idempotency for writes;
- CORS safe defaults;
- sensitive fields excluded.

Do not simply proxy all Woo REST capabilities through a public no-code endpoint.

Store API usage is channel-specific and must respect Woo authentication/nonces/session model.

---

# 26. Security / privacy

Required negative tests/plans:
- order/customer IDOR;
- guest order enumeration;
- cart/session fixation/replay;
- checkout CSRF/state boundaries;
- coupon abuse;
- unauthorized refund/order edit;
- role/capability bypass;
- protected downloads;
- exposed transaction/provider IDs;
- webhook spoofing;
- cross-site order/product ID collision;
- malicious custom field rendering;
- unsafe redirect;
- rate abuse;
- cache leak across customers/carts.

---

# 27. Transaction / Saga semantics

High-impact cross-domain operations use S02:

Example self-service order edit:
1. load order + current version/context;
2. authorize actor/resource/action;
3. verify editable state/fulfillment locks;
4. create immutable edit plan;
5. simulate totals/tax/stock/payment effects where Woo/provider permits;
6. approval/payment delta stage if required;
7. execute bounded Woo abilities;
8. record unknown provider outcomes;
9. reconcile;
10. publish final audit/customer notification.

Do not claim distributed atomicity across payment gateway, inventory, carrier and Woo database.

---

# 28. Cache / invalidation

Cache keys must include relevant:
- site;
- principal/segment where personalized;
- cart version;
- currency/market;
- product/order version;
- rule generation;
- policy generation.

Invalidate on:
- product/variation relevant changes;
- stock change;
- cart change;
- coupon change;
- customer context change;
- published WPE rule/config changes;
- order mutations;
- provider availability change where detected.

Never use cached gateway/shipping availability as authority after checkout context changed.

---

# 29. Multisite

Every adapter call carries explicit site scope.

Rules:
- same Woo object numeric ID on another site is a different resource;
- network admin cannot imply business-record access to every site without Policy;
- shared Solution Blueprint ≠ shared Woo catalog/orders;
- network-owned analytics/search can only ingest explicitly authorized site data;
- clone does not clone live payment/webhook credentials/subscriptions blindly.

---

# 30. Diagnostics / “Why?” traces

Commerce Inspector should be able to answer, where evidence exists:
- why product unavailable;
- why add-to-cart rejected;
- why coupon failed;
- why shipping method hidden;
- why payment gateway hidden;
- why promotion did/didn't match;
- why checkout field shown/required;
- why order edit blocked;
- why stock differs between source views;
- what WPE rules and adapter decisions contributed.

Trace separates:
- Woo native/provider decision;
- WPE contributed rule;
- third-party hook observation;
- unknown/unobservable cause.

Do not falsely claim full causal trace of arbitrary plugins.

---

# 31. Feature certification matrix

Each capability gets independent states:
- `NOT_AVAILABLE`
- `PAPER_SUPPORTED`
- `RUNTIME_UNVERIFIED`
- `CERTIFIED`
- `DEGRADED`
- `UNSUPPORTED`
- `OUTDATED_PROFILE`

Dimensions:
- product read/query/write;
- cart read/add/update/remove/coupon;
- Blocks cart placement;
- classic cart placement;
- checkout fields Blocks/classic;
- checkout layout slots;
- shipping visibility;
- shipping fee contribution;
- payment visibility;
- payment fee contribution;
- promotion profiles;
- order read/query;
- each order mutation Ability;
- refund gateway profile;
- stock read/write;
- My Account slots;
- admin columns/order UI;
- events;
- Multisite;
- known third-party extensions.

One certified capability never implies all WooCommerce integration is certified.

---

# 32. Module disable / Pro expiry

If A01 management/adaptor functionality becomes unavailable:
- no new WPE commerce mutations requiring Pro;
- existing safe public rendering may continue where platform entitlement policy permits;
- security/access enforcement remains safe;
- WPE-owned metadata preserved;
- Woo products/orders/carts remain owned by Woo and must keep functioning independently;
- generated coupons/rules are not silently deleted;
- active critical WPE promotion/stock integration behavior follows documented safe-degrade plan;
- diagnostics identify paused/degraded Solution components.

---

# 33. Uninstall

Uninstall must never delete Woo products/orders/customers/payments merely because WPE adapter is removed.

Separate destructive cleanup may remove:
- WPE definitions;
- WPE metadata known to be WPE-owned;
- WPE operational tables/analytics according explicit plan;
- generated temporary artifacts where safe.

Historical commerce records remain intact.

---

# 34. AI integration

F12 may consume A01 read-only Data Sources under Policy and propose structured drafts.

Allowed examples:
- summarize product performance;
- draft promotion rules;
- recommend merchandising candidates;
- explain gateway/shipping decision trace;
- draft customer support response;
- prepare an order-edit plan.

High-impact writes still route through deterministic abilities and required approval.

AI does not:
- choose payment settlement result;
- invent stock;
- invent tax;
- alter raw order storage;
- issue refunds without explicit typed Ability and approval/policy.

---

# 35. Acceptance / evidence plan

Before any Commerce Solution Blueprint is called production-capable, evidence must cover applicable:
- HPOS CRUD/query behavior;
- Cart/Checkout Blocks;
- classic fallback;
- product types;
- cart mutation/recalculation;
- coupons;
- checkout custom fields;
- shipping/payment availability;
- order reads/mutations;
- refunds including unknown gateway outcome;
- stock concurrency/idempotency;
- customer privacy/IDOR;
- caching;
- Multisite;
- third-party conflict diagnostics;
- performance under realistic catalogs/orders;
- module disable/expiry;
- Backup/Restore/clone;
- upgrades across Woo versions.

Exact executable fixture IDs require a future consent-gated evidence protocol.

---

# 36. Development gate

This specification authorizes **no** WooCommerce hook registration, package installation, Store API call, checkout/cart mutation, order/refund mutation, HPOS query, stock write, payment/shipping provider call or compatibility test. Explicit owner development consent remains required.