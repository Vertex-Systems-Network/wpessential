# WPEssential — Membership Billing Adapter Certification Contract

Status: **Phase 0 planning only / no billing implementation authorized**  
Related: ADR-0013, ADR-0016, ADR-0019, ADR-0040, ADR-0055, Membership System spec.

## 1. Purpose

WPEssential Membership does not become a payment processor. Billing providers are **sources of verified commercial facts** that can create/update/revoke WPE Enrollment/Entitlement state according to published Membership policy.

A billing provider status is never copied blindly into WPE Enrollment state.

Canonical flow:

`Billing Source → Verified Source Fact/Event → Billing Adapter → Reconciliation → Membership Policy → Enrollment transition → Entitlement materialization/invalidation`

---

## 2. Domain separation

### Billing Source owns
- order/purchase/subscription identity;
- payment/renewal facts;
- billing period;
- provider cancellation intent/status;
- refund/dispute facts;
- provider customer identity;
- billing currency/amount;
- payment method/provider internals.

### WPE Membership owns
- Plan/benefit definition;
- Enrollment state;
- access start/end/grace policy;
- Entitlement grants/denials;
- team/seat state;
- protected-resource access;
- grandfathering/follow-current behavior;
- complimentary/admin/manual access.

### Provider does not own
- WordPress Role as business truth;
- direct WPE access-policy precedence;
- local protected-file authorization;
- WPE Plan revision semantics.

---

## 3. Initial adapter priority

1. **Manual / Free Enrollment** — internal reference source, no payment provider.
2. **WooCommerce Core one-time purchases**.
3. **WooCommerce Subscriptions**.
4. **SureCart**.
5. Additional billing providers after demand/security/API evidence.

Direct card processing/Stripe billing engine is not a v1 Membership requirement. WPE should integrate with commerce/billing systems rather than expand PCI/payment scope unnecessarily.

---

## 4. Billing Source Reference

Each provider-linked Enrollment stores immutable/opaque source references such as:
- provider adapter/profile;
- provider account/store context;
- source customer ID;
- order/purchase ID;
- subscription ID where applicable;
- product/price/variation IDs;
- source environment live/test;
- source created/updated timestamp where useful;
- last reconciled source version/time;
- source uniqueness key.

Provider display name/email is not the stable source identity when an ID exists.

Source references are not authorization secrets.

---

## 5. Source Fact model

Billing adapter normalizes provider data into typed facts rather than directly mutating access.

Candidate facts:
- purchase.created;
- purchase.paid;
- purchase.refunded;
- purchase.partially_refunded;
- subscription.created;
- subscription.trialing;
- subscription.active;
- subscription.renewed;
- subscription.payment_failed;
- subscription.on_hold/paused source fact;
- subscription.cancel_at_period_end_set;
- subscription.cancel_at_period_end_removed;
- subscription.cancelled_terminal;
- subscription.expired/completed;
- subscription.refunded;
- dispute/chargeback opened/resolved where provider exposes trustworthy facts;
- source.deleted/invalidated;
- source.reconciled snapshot.

Fact names are WPE normalized vocabulary; provider-specific raw event names remain source metadata.

---

## 6. Cancellation rule

**Cancellation intent is not automatically terminal access state.**

Examples:
- WooCommerce Subscriptions can move a customer-initiated cancellation to `pending-cancel` until the end of a prepaid term.
- SureCart exposes a `subscription.set_to_cancel` event distinct from final `subscription.canceled`.

WPE therefore records:
- `cancel_at_period_end=true/false` source intent;
- paid-through/access-through source timestamp where reliable;
- final provider cancellation fact separately.

Membership Plan policy determines whether access remains active until paid-through date, enters grace, or ends according to accepted semantics.

---

## 7. Payment failure rule

Provider `failed`, `on-hold`, `past_due` or equivalent is a **billing source fact**.

WPE policy translates that fact into:
- remain active during provider retry window;
- enter WPE `grace`;
- enter `paused`;
- revoke/expire only when defined conditions are met.

Do not immediately revoke access from one transient payment-failed webhook unless Plan/source policy explicitly requires it.

---

## 8. One-time WooCommerce purchase adapter

Source truth candidate:
- Woo order/purchase line item;
- paid/payment-complete state through supported Woo APIs;
- refund quantity/amount/order state;
- product/variation mapping to WPE Plan/benefit.

Adapter must distinguish:
- order created;
- payment complete;
- processing/completed according product type;
- on-hold/manual payment;
- failed/cancelled;
- partial/full refund.

A pending/unpaid Woo order does not grant paid Membership merely because checkout created the order.

Mapping options:
- product → Plan;
- variation → Plan;
- product → fixed-duration/lifetime access policy;
- quantity → seat quantity only where explicitly configured;
- coupon/discount does not alter entitlement benefits by accident.

---

## 9. WooCommerce Subscriptions adapter

Certified source data uses supported `WC_Subscription` APIs/hooks rather than direct private DB assumptions.

Source statuses include provider semantics such as:
- pending;
- active;
- on-hold;
- pending-cancel;
- cancelled;
- expired;
- custom/unknown status handled conservatively.

Important mappings:
- created pending before payment is not access proof;
- active is positive subscription source fact;
- pending-cancel records cancellation intent + paid-through behavior, not immediate terminal revoke by default;
- on-hold requires Plan/provider policy translation;
- cancelled/expired are terminal source facts subject to already-signed/complementary WPE overrides;
- renewal success updates source period/reconciliation;
- failed renewal enters failure/retry policy, not simplistic permanent revoke.

Role changes performed by Woo Subscriptions are **not WPE Membership authority**. WPE role sync remains independent/off by default per ADR-0020.

---

## 10. SureCart adapter

SureCart source of truth may involve its cloud/API plus WordPress integration events.

Current documented webhook classes relevant to Membership include:
- `subscription.created`;
- `subscription.made_trialing`;
- `subscription.made_active`;
- `subscription.renewed`;
- `subscription.set_to_cancel`;
- `subscription.canceled`;
- `subscription.completed`;
- `subscription.updated`;
- purchase/refund events where mapping requires them.

Security/reliability requirements:
- verify `x-webhook-signature` according current SureCart HMAC profile;
- validate timestamp/replay window;
- store/dedupe event ID;
- expect duplicates;
- do not assume event ordering;
- fetch current provider object for reconciliation when event order/partial payload makes state uncertain;
- acknowledge webhook quickly and process durable work asynchronously where appropriate.

Provider may add new event types; unknown types are ignored/logged safely, not fatal to endpoint.

---

## 11. Reconciliation is mandatory

Webhooks/hooks provide freshness, not perfect source completeness.

Reconciliation modes:
- on initial link/purchase;
- after ambiguous event;
- scheduled bounded reconciliation;
- manual Repair/Reconcile action;
- after webhook outage/re-enable;
- after site restore/clone where source refs exist;
- migration/import verification.

Reconciliation fetches authoritative current source object/facts and computes expected WPE Enrollment state via the same transition engine.

It never directly edits source provider billing unless an explicit certified management action exists.

---

## 12. Event Inbox

External provider webhooks use ADR-0040 Event Inbox.

Record:
- provider/profile;
- provider event/delivery ID;
- raw type;
- source object ID;
- verified signature state;
- received/occurred timestamp;
- normalized fact(s);
- processing/reconciliation state;
- dedupe key;
- safe error;
- raw payload retention ref only according privacy policy.

Duplicate delivery returns safe success/idempotent handling without repeating business transition.

---

## 13. Idempotency / uniqueness

Enrollment source uniqueness candidate:

`provider_profile + provider_account/store + source_subscription_or_purchase_id + WPE membership mapping identity`

Same provider event/source cannot create duplicate active Enrollment merely because webhook was retried.

Transition engine is idempotent for repeated equivalent facts.

---

## 14. Mapping definitions

Billing Mapping Definition fields:
- name/key/status;
- provider adapter/profile;
- source product/price/variation selector;
- target Membership Plan;
- enrollment mode;
- access duration source: provider-controlled/fixed/lifetime;
- trial handling;
- cancellation policy mapping;
- failure/grace policy;
- refund policy;
- quantity→seats optional;
- environment live/test;
- priority/conflict behavior;
- effective from/to;
- migration/source-match behavior.

Published mapping revision is pinned/recorded when Enrollment created so later mapping edits are explainable.

---

## 15. Mapping conflicts

Potential conflicts:
- same source product maps to multiple mutually exclusive Plans;
- one provider subscription already linked to different Enrollment;
- user/customer identity unresolved;
- multiple valid purchases intentionally grant union benefits;
- refund event for source with manual WPE override;
- imported Enrollment already exists.

No silent destructive choice.

Resolution states:
- auto-resolved by deterministic accepted rule;
- needs_review;
- quarantined_source;
- manual mapping selected;
- ignored_with_reason.

---

## 16. Customer → WordPress user resolution

Do not grant access to arbitrary user solely by matching mutable email when stronger source linkage exists.

Resolution order candidate:
1. existing explicit provider customer ↔ WP user link;
2. source order/customer owner WordPress user ID through certified local integration;
3. verified email match only where policy explicitly allows and ambiguity checks pass;
4. create/invite user only if Membership workflow is configured;
5. unresolved queue for admin review.

Account takeover/user-email-change scenarios require dedicated tests.

---

## 17. Refund policy

Refund facts can map to:
- no access change;
- revoke immediately;
- revoke at period/access end;
- proportional/seat adjustment only if product semantics explicitly support;
- review required for partial refund.

Default cannot be one global refund behavior for every Plan/provider.

Chargeback/dispute behavior likewise requires provider capability and Plan policy.

---

## 18. Upgrade/downgrade

Billing provider owns billing/proration mathematics.

WPE receives source facts for changed product/price/subscription and applies Membership Plan revision/change semantics.

Default candidate for paid downgrade: effective at provider period boundary where source fact indicates this, unless provider/product explicitly performs immediate change.

WPE does not independently charge/refund the difference.

---

## 19. Trial semantics

Source trial is a billing fact.

Mapping defines:
- source trial grants WPE Enrollment `trialing`;
- trial benefits same/different Plan benefits only via Membership policy;
- trial end successful payment → active;
- trial ends without payment → grace/expired/paused according source + Plan policy;
- manual/complementary trial independent of provider is separate source type.

---

## 20. Test/live environment isolation

Provider sandbox/test purchases cannot grant production Membership accidentally.

Every mapping/source reference includes environment.

UI clearly labels test Enrollment/facts.

Migration from test→live is not automatic identity reuse.

---

## 21. Billing adapter certification levels

### MB0 — Detected / Mapping Configurable
Plugin/provider present or connection configured; mapping schema validates. No grant claim.

### MB1 — Source Read Certified
Can identify customer/order/purchase/subscription and read normalized current source facts securely.

### MB2 — Grant Lifecycle Certified
Initial paid/trial/active source facts create correct Enrollment/Entitlements idempotently; user resolution and duplicate prevention certified.

### MB3 — Renewal / Failure / Cancellation Certified
Renewal, temporary payment failure/on-hold, cancel-at-period-end, final cancellation/expiration and reactivation/recovery paths pass.

### MB4 — Refund / Change / Reconciliation Certified
Full/partial refunds, product/price changes, upgrade/downgrade, webhook loss/out-of-order, scheduled reconciliation and manual repair pass.

### MB5 — Production Billing Profile Certified
All advertised provider capabilities for declared version/profile, migration/restore behavior, concurrency, security/privacy and operational recovery certified.

Normal public claim that a billing provider fully supports Membership lifecycle requires the specific capability profile to reach the release-defined level; current recommendation is **MB4 minimum for recurring subscription lifecycle**, with MB5 required for “Production Certified” label.

---

## 22. Security

- WPE never stores card numbers/CVC.
- provider API/webhook credentials in Vault.
- webhook signature verified before source fact dispatch.
- raw billing payload minimized/retained per privacy policy.
- order/customer IDs are not secrets but are access-controlled PII/business data.
- source fetch uses least privileges/scopes.
- admin mapping changes require dedicated capability/audit.
- frontend cannot forge provider source IDs/paid status.
- user cannot submit hidden form field to create paid Enrollment.

---

## 23. Privacy

Store only billing fields required for Membership explanation/reconciliation.

Avoid duplicating full invoices/payment method details into WPE tables.

Enrollment transition history records normalized fact/reference, not unnecessary raw provider payload.

Exporter/eraser implications respect legal/business record requirements and provider-owned data boundaries; WPE does not claim deletion from external commerce provider when local data is erased.

---

## 24. Restore/clone behavior

After site restore/clone:
- source references remain but environment/site identity is checked;
- outbound provider actions/webhooks may be disabled on staging policy;
- reconcile before treating stale local event state as fresh where necessary;
- webhook endpoint subscriptions are re-evaluated;
- duplicate production processing on staging is prevented.

---

## 25. Future evidence — NOT AUTHORIZED

For each adapter/profile:
- initial paid purchase;
- unpaid/pending purchase;
- duplicate hook/webhook;
- renewal success;
- renewal failure/retry/recovery;
- pending-cancel/set-to-cancel;
- final cancellation;
- expiration/completion;
- full/partial refund;
- chargeback/dispute where supported;
- upgrade/downgrade/product change;
- trial start/end/convert;
- out-of-order events;
- webhook outage + reconciliation;
- provider API unavailable;
- user identity ambiguity;
- duplicate source uniqueness race;
- concurrent seat quantity changes;
- restore/clone/staging;
- migration from source plugin;
- privacy/export/delete behavior.

No billing adapter, provider hook, webhook, API call or fixture has been executed.
