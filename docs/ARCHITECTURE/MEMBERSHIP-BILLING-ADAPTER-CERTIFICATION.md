# WPEssential — Membership Billing Adapter Certification Contract

Status: **Phase 0 planning only / no billing implementation authorized**  
Governs: ADR-0013, ADR-0016, ADR-0019, ADR-0040, ADR-0055, ADR-0057, ADR-0062, ADR-0066, ADR-0129 and ADR-0173.

## 1. Purpose

WPEssential Membership does not become a payment processor. Billing providers are **sources of verified commercial facts** that can influence WPE Enrollment and Membership Entitlement state only through reconciliation and published WPE Membership policy.

A billing-provider status is never copied blindly into WPE Enrollment state.

Canonical flow:

`Billing Source → Verified Source Fact/Event → Billing Adapter → Reconciliation → Membership Policy → Enrollment transition → Membership Entitlement materialization/invalidation`

This protocol defines the runtime certification ladder **MB0–MB5** and the canonical executable evidence matrix **MB-F001…MB-F176**.

Current truth:

- four provider/source profiles have static **BE3** evidence;
- **0 provider profiles are MB-certified**;
- **MB-F execution is 0/176**;
- no provider plugin mutation, payment/order/subscription creation, webhook/API call, Job execution, Membership transition, migration or runtime test is authorized by this document.

---

## 2. Non-negotiable authority boundaries

### Billing Source owns commercial source facts

Examples:
- order/purchase/subscription identity;
- payment/renewal facts;
- billing period and paid-through timestamps where reliable;
- provider cancellation intent/status;
- refund/dispute/chargeback facts;
- provider customer identity;
- billing currency/amount;
- provider product/price/variation references;
- payment-method/provider internals.

### WPE Membership owns Membership business authority

Examples:
- Plan and published Plan revision;
- Enrollment state;
- access start/end/grace/paused policy;
- Membership Entitlement grants/denials;
- team/seat state;
- protected-resource access;
- grandfathering/follow-current policy;
- complimentary/admin/manual access;
- mapping revision and reconciliation policy.

### Product Entitlement is a separate authority domain

WPE Product/Pro entitlement, licensing and site allocation are not Membership Entitlements and are not billing-provider statuses.

A paid Membership purchase must not manufacture Product License authority. A valid Product entitlement must not imply a Membership Enrollment.

### Provider never directly owns

- WordPress Role as Membership truth;
- WPE Membership Policy precedence;
- local protected-file authorization;
- WPE Plan revision semantics;
- WPE Product entitlement;
- durable WordPress user/site ownership merely from provider customer/email/store identifiers.

Canonical separation:

`Commercial fact ≠ Enrollment ≠ Membership Entitlement ≠ Product Entitlement ≠ WordPress Role`

---

## 3. Initial provider/source profiles

1. **Manual / Free Enrollment** — internal reference source, no payment provider.
2. **WooCommerce Core one-time purchases**.
3. **WooCommerce Subscriptions**.
4. **SureCart**.
5. Additional providers only after demand, security, API/version and executable evidence.

Direct card processing/Stripe billing-engine scope is not a v1 Membership requirement. WPE should integrate with established commerce/billing systems rather than expand PCI/payment scope unnecessarily.

Static evidence maturity remains separate:

- BE0 — unreviewed/insufficient;
- BE1 — source/current-state semantics reviewed;
- BE2 — lifecycle reviewed;
- BE3 — refund/change/reconciliation/security semantics reviewed.

**BE0–BE3 never grants MB0–MB5.** Current provider paper state is **4 BE3 / 0 MB-certified**.

---

## 4. Stable certification identity

Future runtime certification is scoped to an exact profile including:

- provider key/profile;
- WPE version/commit;
- Membership schema/transition-engine version;
- Billing Adapter version;
- source plugin version where applicable;
- provider API/event-schema profile/version;
- WooCommerce storage mode/HPOS profile where applicable;
- provider account/store/site/tenant context;
- live/test environment;
- authentication/webhook-security profile;
- Vault profile;
- Event Inbox/Webhook Gateway profile;
- JobService backend/runner profile;
- Mapping Definition revision;
- WordPress/PHP/DB environment;
- Multisite/network/site scope;
- certification date and evidence provenance.

A result is not automatically portable to a materially different provider/plugin/API/storage/account/site/security/adapter profile.

`newer` is not automatically `supported`; materially changed or unreviewed versions become `newer_unverified` until evidence establishes compatibility.

---

## 5. Billing Source Reference

Each provider-linked Enrollment stores immutable/opaque source references as applicable:

- provider adapter/profile;
- provider account/store context;
- source customer ID;
- order/purchase ID;
- subscription ID;
- line-item/product/price/variation IDs;
- source environment live/test;
- source created/updated timestamp/version when useful;
- last reconciled source version/time;
- source uniqueness key;
- WPE Mapping revision used to interpret the source.

Provider display name/email is not stable source identity when a stronger identifier exists. Source references are business/PII data where applicable but are not authorization secrets.

---

## 6. Normalized Source Fact model

Billing adapters emit typed normalized facts rather than mutating Membership directly.

Candidate facts include:

### Purchase
- `purchase.created`;
- `purchase.paid`;
- `purchase.unpaid`;
- `purchase.refund_pending`;
- `purchase.refund_partial`;
- `purchase.refund_full`;
- `purchase.revoked`;
- `purchase.reactivated`;
- `purchase.product_or_quantity_changed`.

### Subscription
- `subscription.created`;
- `subscription.trialing`;
- `subscription.active`;
- `subscription.renewed`;
- `subscription.payment_failed`;
- `subscription.on_hold`;
- `subscription.cancel_at_period_end_set`;
- `subscription.cancel_at_period_end_removed`;
- `subscription.cancelled_terminal`;
- `subscription.expired_or_completed`;
- `subscription.reactivated`;
- `subscription.product_or_quantity_changed`.

### Dispute/other
- `dispute.opened`;
- `dispute.won`;
- `dispute.lost`;
- `source.deleted_or_invalidated`;
- `source.reconciled_snapshot`.

Provider-specific raw names remain source metadata. Unknown provider values remain unknown; they are not guessed into positive access facts.

---

## 7. Lifecycle interpretation rules

### Cancellation

Cancellation intent is not automatically terminal access state.

Examples:
- WooCommerce Subscriptions `pending-cancel` can preserve a prepaid term until its end;
- SureCart `subscription.set_to_cancel` is distinct from terminal `subscription.canceled`.

WPE records cancellation intent and paid/access-through source timestamps separately. Membership policy determines continued access, grace or final transition.

### Payment failure

Provider `failed`, `on-hold`, `past_due` or equivalent is a billing source fact. WPE policy can choose active-during-retry, grace, paused or terminal behavior according to accepted Plan/provider rules.

One transient payment-failed event must not become permanent revocation unless explicit policy requires it.

### Refund/dispute

Refund/dispute source facts do not have one universal access consequence. Policy can choose no change, immediate revoke, end-of-period revoke, seat adjustment, temporary hold or review depending on provider evidence and Plan semantics.

### Trial

Source trial is a billing fact. Membership policy owns whether trialing creates a trial Enrollment, which benefits apply, and what happens at conversion/failure/end.

### Upgrade/downgrade

Billing provider owns billing/proration mathematics. WPE receives source changes and applies Plan revision/change semantics. WPE does not independently charge/refund a price difference unless a separately certified management action is explicitly in scope.

---

## 8. Provider-specific source rules

### Manual / Free

Uses WPE-owned source UUID, actor/reason, target user/team, Plan/revision, effective/expiry times and optional external reference. It does not manufacture fake payment/subscription objects.

### WooCommerce Core one-time purchase

Use supported WooCommerce order/line-item APIs. Do not hard-code `Completed == paid`; use supported paid/payment semantics and refund records. Enrollment source identity includes line-item/mapping identity, not only order ID.

A pending/on-hold/unpaid order does not grant paid Membership merely because checkout created it.

### WooCommerce Subscriptions

Use supported `WC_Subscription`/Woo APIs and documented hooks/actions rather than direct private DB assumptions. `pending`, `active`, `on-hold`, `pending-cancel`, `cancelled`, `expired` and custom states are provider source facts, not WPE Enrollment states.

Failed renewal/retry behavior and `pending-cancel` require policy/reconciliation; Woo-created WordPress Role changes are not WPE Membership authority.

### SureCart

Cloud/API and local WordPress integration are separate dimensions. Purchase, Subscription, Refund and related stable UUIDs are source objects. Webhook ordering is not assumed; duplicates are expected; signature/timestamp/replay checks precede source-fact dispatch. Test/live mode is first-class.

Product switch may revoke one Purchase and create another; WPE reconciles the correlated source set rather than treating a single `purchase.revoked` as account-wide access revoke.

---

## 9. Reconciliation is mandatory

Hooks/webhooks provide freshness, not guaranteed complete truth.

Reconciliation modes include:

- initial link/purchase;
- after ambiguous/unknown event;
- scheduled bounded reconciliation;
- manual Repair/Reconcile action;
- webhook outage/re-enable;
- site restore/clone;
- provider/plugin upgrade;
- migration/import verification;
- source conflict/identity review.

Reconciliation fetches authoritative current source data where supported, persists/normalizes source facts and runs the same Membership transition engine. It does not directly edit provider billing unless a separately certified provider-management action exists.

If the current provider state cannot be established, WPE must preserve `unknown`/`needs_review`/equivalent rather than guess a positive or terminal access result.

---

## 10. Event Inbox and JobService

External provider webhooks use the accepted Webhook Gateway/Event Inbox contract.

Event Inbox records at least the provider/profile, trusted target scope, provider event/delivery ID, raw type, source object ID, authenticity state, received/occurred times, normalized facts, dedupe identity, processing/reconciliation state and minimized raw payload reference where policy permits.

Duplicate delivery is idempotent and does not repeat Membership transition effects.

JobService supplies at-least-once processing opportunities. It does not make provider operations or Membership side effects exactly once. Every retry/recovery path must re-check current source identity, Mapping revision, Enrollment state, policy and idempotency preconditions.

---

## 11. Identity, mapping and uniqueness

Enrollment source uniqueness candidate:

`provider_profile + provider_account/store + source_purchase_or_subscription_or_manual_id + mapping_identity + site_scope`

Billing Mapping Definition includes:

- stable name/key/status;
- provider adapter/profile;
- source product/price/variation selector;
- target Membership Plan;
- enrollment mode;
- provider-controlled/fixed/lifetime duration;
- trial policy;
- cancellation/failure/grace/refund policy;
- optional quantity→seat mapping;
- environment live/test;
- priority/conflict behavior;
- effective range;
- migration/source-match behavior.

Published Mapping revision is pinned/recorded when interpreted so later edits remain explainable.

No silent destructive conflict resolution. Conflicts become deterministic accepted resolution, `needs_review`, `quarantined_source`, manual mapping or ignored-with-reason.

---

## 12. Customer → WordPress user resolution

Do not grant access to an arbitrary user solely by matching mutable email when stronger linkage exists.

Candidate resolution order:

1. explicit provider-customer ↔ WP-user link;
2. certified local source owner/user reference;
3. verified email match only when policy explicitly allows and ambiguity checks pass;
4. create/invite only through configured Membership workflow;
5. unresolved review queue.

Provider customer ID/email is not durable site/user authorization by itself. Account takeover, email-change, merged-customer and deleted/recreated-user cases require evidence.

---

## 13. Test/live, Multisite and lifecycle

- Sandbox/test commercial objects cannot grant live Membership.
- Every source and mapping carries environment.
- Site/network ownership is explicit; current-blog context is not durable authority.
- Provider account/store credentials and source IDs cannot cross tenant/site scope without explicit network-scoped policy.
- Clone/staging does not inherit production-mutating provider behavior automatically.
- Restore must not blindly replay hooks/events/Jobs or duplicate provider actions/access grants.
- Site deletion/uninitialization, module disable, Pro expiry, uninstall and privacy erasure are distinct lifecycle events.
- Local erase cannot claim provider-side deletion or legal-record erasure.

---

# 14. Billing adapter certification levels

## MB0 — Detected / Mapping Configurable

Plugin/provider present or connection configured; exact compatibility profile recognized; mapping schema validates. No source-read, grant or lifecycle claim.

## MB1 — Source Read Certified

Can securely identify customer/order/purchase/subscription/manual source and read/normalize current source facts for the exact profile.

## MB2 — Grant Lifecycle Certified

Initial paid/trial/active/manual positive source facts create correct Enrollment and Membership Entitlements idempotently; user resolution and duplicate prevention certified.

## MB3 — Renewal / Failure / Cancellation Certified

Renewal, temporary payment failure/on-hold, cancel-at-period-end, final cancellation/expiration and reactivation/recovery paths certified.

## MB4 — Refund / Change / Reconciliation Certified

Full/partial refunds, disputes where advertised, product/price changes, upgrade/downgrade, webhook loss/out-of-order, scheduled reconciliation and manual repair certified.

## MB5 — Production Billing Profile Certified

All advertised provider capabilities for the exact declared version/profile, plus migration/restore, concurrency, security/privacy, Multisite/lifecycle, version drift and operational recovery are certified.

Normal public claim that a recurring billing provider fully supports Membership lifecycle requires at least the release-defined MB level; current planning recommendation remains **MB4 minimum for recurring lifecycle support**, with **MB5 required for a “Production Certified” label**.

Certification is cumulative. An MB5 claim cannot skip failed/not-executed required MB0–MB4 evidence.

---

# 15. Canonical executable evidence matrix — MB-F001…MB-F176

All fixtures are predefined but **NOT EXECUTED**. Every future run records exact certification identity, preconditions, expected outcome, observed source facts, normalized facts, Enrollment/Entitlement result, security/privacy notes and pass/fail/not-applicable/unsupported.

## Group A — Profile identity, compatibility and static/runtime evidence — MB-F001…MB-F011

- **MB-F001** pin provider/profile, WPE, adapter, Membership schema and environment versions.
- **MB-F002** pin provider API/event schema and source-plugin version where applicable.
- **MB-F003** pin Woo HPOS/legacy-storage mode where applicable.
- **MB-F004** pin provider account/store, live/test and site/network scope.
- **MB-F005** prove BE3 static review cannot produce MB0.
- **MB-F006** detect below-floor source profile and block unsafe mutation.
- **MB-F007** detect newer-unverified profile without falsely calling it Supported or incompatible.
- **MB-F008** known-incompatible/security-blocked profile fails closed for risky automation.
- **MB-F009** adapter/profile version change triggers targeted recertification.
- **MB-F010** exact/range certification does not leak to materially different provider profile.
- **MB-F011** certification report preserves evidence provenance, unsupported capabilities and retest triggers.

## Group B — Vault, credentials, connection and provider scope — MB-F012…MB-F022

- **MB-F012** valid Vault-backed provider/API/webhook credentials load only in authorized server context.
- **MB-F013** invalid/revoked credential yields truthful failure without secret disclosure.
- **MB-F014** browser/UI/API responses never return API/webhook/provider secrets.
- **MB-F015** logs/Audit/diagnostics redact billing credentials and sensitive payload fields.
- **MB-F016** least-privilege provider scope is sufficient for advertised source reads/reconciliation.
- **MB-F017** insufficient provider scope degrades/blocks capability truthfully.
- **MB-F018** credential rotation preserves source history and does not create plaintext fallback.
- **MB-F019** concurrent credential rotation/reconciliation does not mix tenant/provider identity unsafely.
- **MB-F020** provider account/store mismatch is detected before source facts can affect Membership.
- **MB-F021** local provider plugin detected but external account/API disconnected is represented distinctly.
- **MB-F022** network/site credential inheritance follows explicit Vault + Multisite policy.

## Group C — Provider customer/user/site identity and source uniqueness — MB-F023…MB-F033

- **MB-F023** explicit provider-customer ↔ WP-user link resolves deterministically.
- **MB-F024** certified local order/customer owner resolves without mutable-email authority escalation.
- **MB-F025** email fallback with one unambiguous verified match follows explicit policy.
- **MB-F026** duplicate/ambiguous email match enters review instead of granting access.
- **MB-F027** user email change does not transfer provider-owned Enrollment to another user silently.
- **MB-F028** provider customer email change does not create duplicate Membership identity.
- **MB-F029** deleted/recreated WP user cannot inherit provider linkage solely through recycled email.
- **MB-F030** one provider source cannot create duplicate active Enrollments under concurrent processing.
- **MB-F031** one order with multiple mapped line items preserves line-item/mapping uniqueness.
- **MB-F032** provider source identifiers are scoped by provider account/store/site/environment.
- **MB-F033** cross-site/customer/source-ID collision cannot attach commercial facts to the wrong tenant/user.

## Group D — Mapping Definition, Plan revision and product/price semantics — MB-F034…MB-F044

- **MB-F034** valid published mapping resolves provider product/price/variation to exact Membership Plan.
- **MB-F035** Draft mapping edit has no effect on existing live interpretation until publication policy permits.
- **MB-F036** Enrollment records the Mapping/Plan revision provenance used for derivation.
- **MB-F037** unmapped commercial source remains unmapped/reviewable rather than granting default access.
- **MB-F038** conflicting mappings use deterministic priority or review, never silent arbitrary choice.
- **MB-F039** product/variation change after purchase does not silently transfer Membership authority.
- **MB-F040** quantity→seat mapping occurs only when explicitly configured and bounded.
- **MB-F041** coupon/discount/zero-total handling does not accidentally change benefit semantics.
- **MB-F042** fixed-duration/lifetime/provider-controlled duration modes produce distinct accepted time semantics.
- **MB-F043** expired/effective mapping boundaries use the source occurred/effective rules consistently.
- **MB-F044** Membership mapping cannot create WPE Product/Pro entitlement authority.

## Group E — Initial source creation, unpaid/pending/paid/trial/manual grant — MB-F045…MB-F055

- **MB-F045** Manual/Free explicit authorized grant creates the correct source fact without fake payment object.
- **MB-F046** unauthorized Manual grant is rejected and audited.
- **MB-F047** Woo pending-payment order does not create paid Membership.
- **MB-F048** Woo on-hold/manual-payment state does not become paid access without supported positive evidence/policy.
- **MB-F049** Woo supported paid/payment-complete semantics produce normalized paid fact for mapped line item.
- **MB-F050** Woo Completed is not hard-coded as the sole paid criterion.
- **MB-F051** Woo zero-total/free order follows explicit mapping policy without fabricating payment.
- **MB-F052** Woo Subscription created/pending before successful initial payment does not prove active paid access.
- **MB-F053** certified Woo Subscription active/payment-complete source snapshot produces positive source facts idempotently.
- **MB-F054** SureCart test/live Purchase/Subscription creation remains environment isolated.
- **MB-F055** provider/manual trial source fact creates only the Membership trial state/benefits allowed by Plan policy.

## Group F — Webhook/hook authenticity, Event Inbox and raw-source truth — MB-F056…MB-F066

- **MB-F056** valid external webhook signature/auth profile is accepted before source-fact dispatch.
- **MB-F057** tampered webhook body/signature is rejected and cannot alter Membership.
- **MB-F058** stale/replayed webhook outside certified replay policy is rejected/deduped.
- **MB-F059** wrong provider/profile endpoint cannot inject facts for another adapter/site.
- **MB-F060** trusted endpoint/routing state determines target site/network scope rather than payload alone.
- **MB-F061** malformed/oversized provider payload fails safely without poisoning Event Inbox.
- **MB-F062** valid event persists source identity/authenticity/dedupe metadata before asynchronous business work.
- **MB-F063** raw billing payload retention is minimized and policy-bound.
- **MB-F064** unknown provider event type is preserved/ignored safely and does not imply access transition.
- **MB-F065** local Woo hook is treated as freshness signal and reconciled through supported source APIs where ambiguity exists.
- **MB-F066** webhook acknowledgement/retry behavior does not claim downstream Membership transition succeeded prematurely.

## Group G — Event dedupe, ordering, races and late events — MB-F067…MB-F077

- **MB-F067** duplicate provider event is idempotent for source facts and Enrollment transitions.
- **MB-F068** duplicate local hook does not duplicate Enrollment/Entitlements.
- **MB-F069** out-of-order active then older failed event resolves from authoritative source/reconciliation rather than arrival order.
- **MB-F070** out-of-order cancellation intent/final cancellation resolves deterministically.
- **MB-F071** late renewal event after later cancellation cannot revive access without current-source proof.
- **MB-F072** concurrent provider events for same source serialize/precondition safely.
- **MB-F073** concurrent initial purchase processing cannot create duplicate source linkage.
- **MB-F074** provider event with unknown source object is quarantined/reconciled, not attached by guess.
- **MB-F075** same event ID under different trusted provider/account scope cannot cross-dedupe incorrectly.
- **MB-F076** restore/replay of already-ingested event does not repeat Membership side effects.
- **MB-F077** raw fact/history preserves conflicting/late provider evidence for audit without rewriting historical facts silently.

## Group H — Renewal, temporary failure, hold, grace and recovery — MB-F078…MB-F088

- **MB-F078** successful renewal updates source period/paid-through facts and Enrollment idempotently.
- **MB-F079** duplicate renewal-success signal does not extend access twice.
- **MB-F080** transient renewal/payment failure is normalized separately from terminal cancellation.
- **MB-F081** provider retry window can preserve active/grace access only according to explicit policy.
- **MB-F082** Woo Subscription `on-hold` is interpreted by source cause/policy rather than one universal revoke rule.
- **MB-F083** stalled provider scheduler/overdue renewal is not automatically payment failure.
- **MB-F084** later payment recovery exits grace/paused according to current source and Plan policy.
- **MB-F085** repeated failed-renewal events remain idempotent and bounded.
- **MB-F086** grace expiration uses authoritative/current time source and accepted Membership semantics.
- **MB-F087** source API unavailable during failure keeps uncertain state explicit and schedules bounded reconciliation.
- **MB-F088** manual Repair/Reconcile after outage converges to the same state as normal event flow.

## Group I — Cancellation intent, terminal cancellation, expiry, pause and reactivation — MB-F089…MB-F099

- **MB-F089** Woo `pending-cancel` records cancellation intent without default immediate terminal revoke.
- **MB-F090** SureCart `subscription.set_to_cancel` records intent separately from final cancellation.
- **MB-F091** cancel-at-period-end removal/reversal updates intent idempotently.
- **MB-F092** paid-through/current-period end remains separate from cancellation-request timestamp.
- **MB-F093** terminal provider cancellation produces the policy-defined Membership transition only when current.
- **MB-F094** provider expiration/completion remains distinct from cancellation.
- **MB-F095** manual pause/resume or provider hold maps only through explicit Plan/provider semantics.
- **MB-F096** provider reactivation after terminal/paused source follows current Mapping/Plan policy and does not revive unrelated revoked access.
- **MB-F097** cancellation then immediate re-subscribe with a new source identity creates explainable separate provenance.
- **MB-F098** old terminal event arriving after new subscription cannot revoke the new Enrollment through weak user matching.
- **MB-F099** manual/admin Membership override remains independent from provider terminal source facts according to precedence policy.

## Group J — Refund, partial refund, dispute and chargeback — MB-F100…MB-F110

- **MB-F100** Woo full refund source evidence is distinguished from order cancellation.
- **MB-F101** Woo partial refund reads refund records/line quantities/amounts rather than only final order status.
- **MB-F102** provider refund initiated/pending is distinct from refund succeeded where provider exposes both.
- **MB-F103** full refund applies the Mapping/Plan refund policy idempotently.
- **MB-F104** partial refund follows explicit policy/seat semantics or review; no global guessed revoke.
- **MB-F105** refund of one line item cannot revoke Membership from unrelated order lines.
- **MB-F106** dispute/chargeback opened is a source fact, not an automatic final access decision unless policy says so.
- **MB-F107** dispute resolved won/lost updates Membership only through accepted policy/current source evidence.
- **MB-F108** refund event after manual complimentary override does not destroy unrelated Membership authority.
- **MB-F109** duplicated/refetched refund records do not repeat seat/access reductions.
- **MB-F110** external processor settlement is not claimed merely from a local/manual commerce refund record when provider cannot prove it.

## Group K — Product/price/quantity change, upgrade/downgrade and seat semantics — MB-F111…MB-F121

- **MB-F111** provider product/price switch correlates old/new source objects before Membership transition.
- **MB-F112** SureCart old Purchase revoke during plan switch does not become account-wide revoke.
- **MB-F113** immediate provider upgrade maps benefits at the provider-effective boundary defined by policy.
- **MB-F114** period-end downgrade remains pending until authoritative effective boundary where provider semantics require.
- **MB-F115** WPE never independently calculates/charges/refunds provider proration unless separately certified scope exists.
- **MB-F116** quantity increase produces seat change only for mappings explicitly configured for quantity→seats.
- **MB-F117** quantity decrease cannot evict/alter team members without Membership seat-policy handling.
- **MB-F118** concurrent quantity changes reconcile to current source and do not apply arithmetic twice.
- **MB-F119** product switch plus cancellation/refund race converges deterministically from current source facts.
- **MB-F120** Mapping revision change does not reinterpret historical source silently without migration policy.
- **MB-F121** product/price identifiers remain provider-scoped and cannot be reused across site/account/environment by coincidence.

## Group L — Unknown outcomes, reconciliation, JobService, retries and manual repair — MB-F122…MB-F132

- **MB-F122** provider source read timeout before response remains unknown/transient, not positive or terminal fact.
- **MB-F123** provider response after ambiguous transport outcome is reconciled before any risky management retry.
- **MB-F124** missed webhook is recovered by bounded scheduled reconciliation where provider supports source reads.
- **MB-F125** provider with insufficient reconciliation API keeps unresolved state visible rather than fabricated.
- **MB-F126** Job crash before reconciliation safely retries without duplicate Enrollment mutation.
- **MB-F127** Job crash after source fact persisted but before transition safely resumes through idempotent preconditions.
- **MB-F128** Job crash after Enrollment transition but before success bookkeeping does not apply transition twice.
- **MB-F129** stale lease/concurrent worker cannot create duplicate source facts or access grants.
- **MB-F130** provider rate limit/429 honors bounded retry/backoff without becoming authorization result.
- **MB-F131** manual Repair/Reconcile is capability/Policy protected, audited and uses the same transition engine.
- **MB-F132** reconciliation conflict produces deterministic state/review and preserves evidence rather than silently overwriting admin/provider facts.

## Group M — Enrollment, Membership Entitlement, Product Entitlement and Role separation — MB-F133…MB-F143

- **MB-F133** positive commercial fact creates/updates Enrollment only through published Membership policy.
- **MB-F134** Enrollment transition materializes/invalidate Membership Entitlements through accepted Membership engine.
- **MB-F135** provider webhook cannot directly write Membership Entitlement rows as authoritative shortcut.
- **MB-F136** provider Role changes (including Woo Subscriptions roles) do not become Membership truth.
- **MB-F137** optional WPE Role sync remains separate/provenance-aware and cannot grant Membership by circular inference.
- **MB-F138** Product/Pro entitlement remains independent from Membership billing source.
- **MB-F139** Product entitlement loss/expiry does not fabricate provider cancellation/refund facts.
- **MB-F140** Membership Enrollment expiry/revoke does not revoke unrelated Product entitlement.
- **MB-F141** manual/complementary Membership authority can coexist with provider-owned source under explicit precedence/provenance.
- **MB-F142** access check consumes current authorized Membership Entitlements, not raw provider status/event payload.
- **MB-F143** revoked/expired source reaches deny-safe Membership result within accepted reconciliation/transition latency without stale positive cache authority.

## Group N — Privacy, retention, audit, export and external-data boundaries — MB-F144…MB-F154

- **MB-F144** only Membership-relevant billing data is copied locally; card/CVC/payment credentials are never stored by WPE.
- **MB-F145** raw provider payload retention follows PDL minimization and Event Inbox policy.
- **MB-F146** source IDs/customer/order/subscription business data is access-controlled and not exposed cross-tenant.
- **MB-F147** privacy export includes authorized explainable source/Enrollment data without provider/Vault secrets.
- **MB-F148** local privacy erase distinguishes local live data from external commerce-provider records.
- **MB-F149** WPE never claims provider deletion when only local records were erased.
- **MB-F150** legal/business retention exceptions remain explicit instead of silently defeating erasure UX.
- **MB-F151** remote-service transmission, if any WPE-controlled service is involved, follows RS purpose/minimization/consent rules independently from billing-provider transmission.
- **MB-F152** Audit records mapping/reconcile/manual grant/revoke/security-relevant changes without raw secret/payment data.
- **MB-F153** support diagnostics redact provider customer PII, raw payloads and credentials by default.
- **MB-F154** backup retention/restore of billing-derived data remains distinct from live privacy erasure and provider retention.

## Group O — Multisite, clone/restore, module/site lifecycle and environment isolation — MB-F155…MB-F165

- **MB-F155** site-scoped billing profile cannot read/process another site's provider sources or credentials.
- **MB-F156** explicit network-scoped provider profile requires target-site authorization and stable ownership identifiers.
- **MB-F157** `switch_to_blog()`/current-blog context cannot become durable source ownership authority.
- **MB-F158** staging/clone starts provider-mutating automation safe/disabled unless explicit environment policy permits it.
- **MB-F159** clone cannot turn copied live source IDs into live Membership authority for the cloned site automatically.
- **MB-F160** restore does not replay historical webhook/Job transitions blindly.
- **MB-F161** restore with current provider state differing from backup reconciles before granting/revoking from stale snapshot.
- **MB-F162** webhook endpoint subscriptions/secrets/site routing are re-evaluated after URL/site identity changes.
- **MB-F163** site deletion/uninitialization stops future processing and applies retention policy without pretending external billing was cancelled/deleted.
- **MB-F164** module disable/Pro expiry preserves safe historical/access semantics according to lifecycle contract and does not mutate provider billing.
- **MB-F165** re-enable/upgrade revalidates source profile/version/mapping/current provider state before resuming automation.

## Group P — Provider upgrades, HPOS, security, scale and production operations — MB-F166…MB-F176

- **MB-F166** Woo HPOS authoritative mode passes supported public CRUD/API source reads and lifecycle flows.
- **MB-F167** legacy Woo storage/compatibility mode is supported only if intentionally included and separately evidenced.
- **MB-F168** HPOS migration/switch after synchronization does not duplicate/lose source identity or Enrollment mapping.
- **MB-F169** provider/plugin patch/minor upgrade inside claimed range reruns required regression/compatibility evidence.
- **MB-F170** provider major/newer-unverified version blocks or degrades risky automation until recertified.
- **MB-F171** known security-advisory range becomes security-blocked without recommending unsafe downgrade solely for compatibility.
- **MB-F172** SureCart local plugin and hosted API/webhook schema drift are evaluated as separate compatibility dimensions.
- **MB-F173** high-volume source/event/reconciliation workload respects Job/RLT/resource/backpressure budgets without cross-site starvation.
- **MB-F174** provider outage/degradation/quota/rate-limit produces truthful operational health and bounded recovery behavior.
- **MB-F175** certification report lists failed/not-executed/unsupported capabilities and exact version range, not only passes.
- **MB-F176** full exact-profile regression proves source→reconciliation→Enrollment→Membership Entitlement authority boundaries and negative requirements before MB5 claim.

---

## 16. Fixture-to-level certification rule

Each certification report maps MB-F fixtures to MB0–MB5 and marks each `required`, `not_applicable`, `unsupported`, `pass`, `fail` or `not_executed` with rationale.

Minimum cumulative intent:

- **MB0** requires applicable identity/version/detection/mapping configuration and safety prerequisites.
- **MB1** requires MB0 plus secure source identity/read/normalization evidence.
- **MB2** requires MB1 plus initial grant/trial/manual positive lifecycle, identity, uniqueness and Membership derivation evidence.
- **MB3** requires MB2 plus renewal/failure/hold/grace/cancellation/expiry/reactivation evidence.
- **MB4** requires MB3 plus refund/dispute/change/reconciliation/outage/order/idempotency evidence for advertised capabilities.
- **MB5** requires MB4 plus Multisite/lifecycle/restore/privacy/version/HPOS/security/scale/operational recovery evidence.

A provider capability can be `not_applicable` only with exact-profile evidence. Unsupported capability remains visibly unsupported and cannot be waived into a pass.

---

## 17. Provider-specific minimum suites

### Manual / Free

Must prove actor authorization, source UUID/idempotency, Plan revision, start/end, revoke/restore, conflict/precedence, import/restore/clone and Audit provenance. No fake payment status is created.

### WooCommerce Core

Must prove supported order/line-item APIs, paid semantics, pending/on-hold/unpaid behavior, zero-total policy, refunds, order edits, product/variation mapping, HPOS and identity/reconciliation. Direct private order-storage assumptions are prohibited in the normal adapter path.

### WooCommerce Subscriptions

Must prove initial/renewal source reads, active/on-hold/pending-cancel/cancelled/expired semantics, renewal retry/recovery, dates/paid-through behavior, product/quantity changes, Woo role non-authority, HPOS and source-plugin version profile.

### SureCart

Must prove stable Purchase/Subscription/Refund objects, `live_mode`, Purchase revoke/invoke, trial/active/renewed/set-to-cancel/cancelled/completed/change semantics, HMAC/timestamp/replay profile, duplicate/out-of-order events, cloud reconciliation, local-plugin + hosted-API version dimensions and plan-switch correlation.

Provider-specific suites add requirements; they never weaken shared MB-F evidence.

---

## 18. MUST NOT / stop-the-line gates

Stop certification and mark the profile failed/blocked if evidence shows any of the following:

- commercial/provider state copied directly into Membership Enrollment/Entitlement without accepted reconciliation/policy;
- provider customer/email/order/subscription ID treated as WordPress user/site authorization by itself;
- Membership billing creates/revokes Product/Pro entitlement authority;
- provider-managed WordPress Role treated as Membership truth;
- unauthenticated/tampered webhook source fact accepted where authenticity is required;
- duplicate/out-of-order/replayed events cause repeated grants/revokes/seat changes;
- ambiguous/unknown provider state guessed as paid, cancelled, refunded or active;
- sandbox/test source grants production Membership;
- cross-site/network provider/source/credential leakage;
- Vault credentials/card/CVC/signing secrets exposed to browser/log/export/Audit/diagnostics;
- restored/staging/cloned state processes live billing sources unsafely;
- JobService at-least-once processing treated as exactly-once Membership/provider behavior;
- RLT/cache/hook delivery/CORS/idempotency used as authorization;
- BE3/static docs/code presence promoted to MB runtime certification;
- upper MB level certified while required lower-level evidence failed/unknown/not executed;
- unsupported/unknown capability hidden from certification report;
- direct Woo private storage assumptions used where public APIs are required for the certified profile;
- local privacy erase represented as deletion from external billing provider;
- source-plugin/API/security version outside certified profile allowed to mutate access without accepted compatibility policy.

Cross-tenant leakage, authorization bypass, secret/payment-data exposure, uncontrolled duplicate grants/revokes or destructive privacy/data corruption triggers repository stop-the-line incident governance.

---

## 19. Evidence artifact

Every future provider certification produces a report containing:

- exact profile identity/version/environment/site scope;
- MB level sought and prerequisite levels;
- provider/source capabilities advertised and unsupported;
- MB-F fixture IDs with expected vs observed outcomes;
- raw source evidence stored only in protected/minimized test artifacts;
- normalized source facts;
- Event Inbox/reconciliation evidence;
- Enrollment transition and Membership Entitlement evidence;
- proof that Product Entitlement/WordPress Role authority did not cross domains;
- security/privacy/Multisite/lifecycle observations;
- known provider quirks/version limits;
- pass/fail/not-executed/not-applicable/unsupported per fixture;
- certification range/expiry/retest triggers;
- negative-requirement/stop-the-line results.

Retest triggers include provider/plugin/API/event-schema/security advisory changes, Woo HPOS/storage changes, Billing Adapter changes, Membership transition/Mapping changes, Vault/Event Inbox/JobService changes, privacy/lifecycle/Multisite changes or material WPE schema/compatibility changes.

---

## 20. Current evidence truth

- MB-F fixtures documented: **176**.
- MB-F fixtures executed: **0/176**.
- static provider/source profiles: **4 BE3**.
- runtime provider profiles certified: **0 MB-certified**.
- MB0: **0 certified profiles**.
- MB1: **0 certified profiles**.
- MB2: **0 certified profiles**.
- MB3: **0 certified profiles**.
- MB4: **0 certified profiles**.
- MB5: **0 certified profiles**.
- Membership core runtime MBR remains separately **0/160**.
- protected-file delivery certification remains separately **0 PC1+**.
- no billing adapter/provider plugin mutation, Woo HPOS mutation, commerce object creation, webhook/API call, reconciliation execution, Membership transition, Job execution, migration, build, benchmark or runtime test occurred in this planning work package.

## Development gate

**No item in this protocol may execute until explicit scoped owner consent is granted and recorded under ADR-0014, `DEVELOPMENT-CONSENT.md` and the Approval Ledger.**