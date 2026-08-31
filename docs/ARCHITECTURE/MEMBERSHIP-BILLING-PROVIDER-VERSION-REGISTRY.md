# WPEssential — Membership Billing Provider Version & Evidence Registry

Status: **Phase 0 planning / static official-doc evidence only / no billing adapter runtime authorized**  
Review date: **2026-08-28**  
Related: ADR-0057, ADR-0062, P-012, `MEMBERSHIP-BILLING-PROVIDER-PROFILES.md`.

## 1. Purpose

A provider name is not a stable API contract.

WPEssential must know **which exact provider/plugin/API version profile was reviewed and which version range was actually certified** before claiming a Membership billing integration is supported.

This registry separates:
- current static documentation snapshot;
- supported/candidate version range;
- future runtime-certified range;
- provider API/schema version;
- WordPress plugin version where applicable;
- compatibility features such as HPOS;
- security advisories;
- out-of-range behavior.

Static version research is **not MB0–MB5 certification**.

Current MB-certified count remains **0**.

---

# 2. Stable identity

A future runtime billing profile is identified conceptually by:

`provider_key + provider_profile_version + source_plugin_version + source_api_schema/version + adapter_version + environment`

Examples:

`billing.woocommerce-order + 2026-08-profile + woo-11.0.1 + public-wc-crud + adapter-v1 + live`

`billing.surecart + 2026-08-profile + surecart-wp-4.7.0 + surecart-api-current-profile + adapter-v1 + test`

`provider_profile_version` is WPE's interpretation/certification record. It is not replaced by the provider's marketing version.

---

# 3. Compatibility-state vocabulary

A detected source profile can be:

- `not_installed`;
- `detected_unreviewed`;
- `below_supported_floor`;
- `candidate_static_reviewed`;
- `certified_exact`;
- `certified_range`;
- `newer_unverified`;
- `known_incompatible`;
- `security_blocked`;
- `degraded_read_only`;
- `reconciliation_required`.

The UI must not convert `newer_unverified` into `Supported` merely because semantic version comparison says it is newer.

---

# 4. Version-policy principles

1. **Certification is version-scoped.** An MB5 result for one provider/plugin/API profile does not certify every future major version.
2. **Newer is not automatically safer/compatible.** Future provider versions can change hooks, storage, status semantics or security behavior.
3. **Known vulnerable versions can be security-blocked** even if they were previously functionally compatible.
4. **Public APIs only.** WPE does not depend on WooCommerce `Internal` namespace or source-plugin private implementation details when a public API exists.
5. **Storage abstraction matters.** Woo order/subscription reads use Woo CRUD/data APIs so HPOS and legacy posts storage can be supported intentionally rather than through direct `wp_posts` assumptions.
6. **API and plugin versions are separate.** SureCart's local WordPress plugin version and hosted SureCart API/event contract are separate compatibility dimensions.
7. **Test/live are separate environments.** Certification never allows test objects to authorize live Membership access.
8. **Exact source facts survive version translation.** Provider status strings never directly become WPE Enrollment states.

---

# 5. Current official snapshot — 2026-08-28

These facts are static research inputs only.

## 5.1 WooCommerce Core

Current official release snapshot reviewed:
- WooCommerce **11.0.1**, released 2026-08-10;
- WordPress.org currently lists WooCommerce 11.0.1;
- minimum WordPress listed: 6.9;
- minimum PHP listed: 7.4;
- Woo documentation recommends PHP 8.3 or newer despite the lower compatibility floor.

Important compatibility facts:
- HPOS is the default for new stores since WooCommerce 8.2;
- extension developers should use WooCommerce CRUD/data APIs rather than direct WordPress order-post assumptions;
- `Automattic\WooCommerce\Internal` namespace is not a public backward-compatibility contract;
- Woo requires/encourages extensions to test and declare compatibility with current platform features such as HPOS.

### WPE paper baseline

For the first future `billing.woocommerce-order` certification campaign:
- reference current line: WooCommerce **11.0.x**;
- compatibility floor must not be lower than the intersection of WPE platform requirements and the exact integration evidence;
- HPOS enabled is a mandatory test mode;
- legacy posts storage/compatibility mode is a separate compatibility fixture if WPE intends to support it;
- direct SQL/postmeta order reads are prohibited from the billing adapter normal path.

No Woo version is MB-certified yet.

## 5.2 WooCommerce Subscriptions

Current official product snapshot reviewed:
- WooCommerce Subscriptions **9.1.0** current product version;
- security advisory dated 2026-08-05 instructs affected sites to update to 9.1.0;
- product page: requires WordPress 6.9+, WooCommerce 10.9+, PHP 7.4+;
- tested with WordPress 7.0 / WooCommerce 11.0 in the reviewed product metadata;
- current documentation says latest Subscriptions generally supports the most recent WooCommerce version and the previous minor line;
- HPOS compatibility is explicitly documented.

### WPE paper baseline

First future `billing.woocommerce-subscriptions` certification campaign should start with:
- Woo Subscriptions **9.1.x** current secured line;
- WooCommerce **11.0.x** reference line;
- WooCommerce **10.9.x** as the immediately previous documented compatibility line where still security-supported at execution time;
- HPOS enabled mandatory;
- source object APIs/hooks documented by Subscriptions rather than direct storage assumptions.

A future newer Woo/Subscriptions release becomes `newer_unverified` until compatibility impact is reviewed/certified.

Versions subject to a known unpatched security advisory become `security_blocked`, not merely `old but supported`.

No Subscriptions version is MB-certified yet.

## 5.3 SureCart

Current WordPress.org snapshot reviewed:
- SureCart **4.7.0**, dated 2026-08-25;
- WordPress.org minimum WordPress: 6.8;
- tested up to WordPress 7.1;
- minimum PHP: 7.4.

Relevant current changelog evidence:
- 4.7.0 includes an account-creation hardening fix;
- 4.6.3 also documented access-control/import security hardening;
- 4.6.5/4.6.6 included WordPress 7.1 compatibility fixes.

SureCart provider architecture differs materially from Woo:
- WordPress plugin version is one dimension;
- SureCart hosted account/API/webhook schema is another dimension;
- source facts include Purchase, Subscription, Refund and related provider objects;
- webhook events are asynchronous and can be duplicate/out of order;
- test/live state is first-class.

### WPE paper baseline

First future `billing.surecart` certification campaign should start from:
- SureCart WordPress plugin **4.7.x current line** at execution time;
- exact current SureCart API/webhook profile snapshot recorded separately;
- verified source-object reads + webhook ingress + reconciliation together;
- WordPress user/customer resolution tested independently from commerce-object correctness.

WPE cannot certify only the local plugin version while ignoring hosted API/event behavior.

No SureCart version/API profile is MB-certified yet.

## 5.4 Manual/Free provider

`billing.manual` has no external plugin dependency.

Its compatibility identity is WPE Platform API + Membership schema version + adapter/profile version.

Manual does not mean untracked. Future runtime certification must still prove:
- grant source identity;
- actor authorization;
- start/end dates;
- revoke/expire behavior;
- idempotency;
- audit history;
- import/restore/clone behavior.

No Manual provider runtime is MB-certified yet because implementation is not authorized.

---

# 6. Current paper registry

| Provider key | Source plugin/API snapshot | Static evidence | First future certification reference | Current MB state |
|---|---|---:|---|---|
| `billing.manual` | WPE-owned | BE3 | WPE Membership runtime version to be implemented | Not certified |
| `billing.woocommerce-order` | WooCommerce 11.0.1 current snapshot | BE3 | Woo 11.0.x + HPOS mandatory; prior line only if explicitly tested | Not certified |
| `billing.woocommerce-subscriptions` | Subscriptions 9.1.0; Woo 11.0 current / 10.9 documented floor | BE3 | WCS 9.1.x + Woo 11.0.x; 10.9.x only if current/security-valid at execution | Not certified |
| `billing.surecart` | WP plugin 4.7.0 + current hosted API/webhook profile | BE3 | SureCart 4.7.x + separately recorded API/event profile | Not certified |

Current MB-certified profiles: **0**.

---

# 7. HPOS contract

Woo-based profiles must treat HPOS as a first-class compatibility dimension.

Required future matrix:
- HPOS authoritative;
- legacy posts storage only if still intentionally supported;
- Woo compatibility/sync mode where relevant;
- switch after full synchronization;
- refunds and subscription objects under chosen storage;
- upgrade from supported prior Woo line;
- no direct post/postmeta assumptions.

WPE must declare Woo HPOS compatibility only after the relevant executable matrix passes.

---

# 8. Provider upgrade behavior

When a source plugin/API changes:

## Patch release inside certified range

Can remain Supported only if policy allows and automated regression evidence exists for the range.

Otherwise state becomes `newer_unverified` until a smoke/regression certification update.

## Minor release

Requires at least:
- changelog/security review;
- source API/hook diff review;
- lifecycle mapping regression;
- reconciliation fixtures;
- HPOS/storage check for Woo profiles;
- webhook schema/event review for SureCart.

## Major release

Defaults to `newer_unverified` and requires a new provider-profile version/certification campaign.

## Security advisory

If provider recommends an urgent security update:
- old affected version can become `security_blocked`;
- WPE must not recommend pinning an insecure version merely to preserve compatibility;
- compatibility work targets the secure provider version.

---

# 9. Unsupported/out-of-range UX

WPE must distinguish:

### Below floor
- explain minimum supported/certified source version;
- do not activate mutating billing automation;
- allow safe diagnostic/read-only migration tooling only when separately proven.

### Newer unverified
- do not falsely say incompatible;
- show `Newer version not yet certified`;
- default high-risk source-to-entitlement automation to safe policy defined by certification status;
- preserve existing data/access according to Membership safety rules;
- provide version information needed for support.

### Known incompatible
- identify exact incompatible range/reason;
- block unsafe writes/reconciliation path;
- never suggest downgrading to a known vulnerable release.

---

# 10. Future MB certification matrix — NOT AUTHORIZED

For each exact/range profile after explicit owner consent:
- activate source plugin/version;
- verify dependencies/platform versions;
- inspect HPOS/storage mode where applicable;
- create test source records in isolated environment;
- verify source identity/reconciliation;
- duplicate/out-of-order event handling;
- paid/cancel/fail/recover/expire/refund/change/switch flows;
- restore/clone/test-live isolation;
- upgrade within certified range;
- upgrade outside range;
- downgrade behavior;
- security advisory handling;
- customer→WP identity mapping;
- Membership revoke-to-deny latency;
- audit/privacy/export behavior.

No fixture has been executed.

---

# 11. Static references reviewed

- WooCommerce official Releases: current stable 11.0.1 on 2026-08-10.
- WordPress.org WooCommerce plugin metadata: 11.0.1, WP 6.9+, PHP 7.4+.
- WooCommerce extension compatibility/interoperability and HPOS developer documentation.
- WooCommerce extension development guidance: public APIs vs `Automattic\WooCommerce\Internal`.
- WooCommerce Subscriptions current product metadata: version 9.1.0; minimum WP 6.9; minimum Woo 10.9; tested Woo 11.0.
- WooCommerce Subscriptions requirements/HPOS documentation.
- WooCommerce 2026-08-05 security advisory recommending Subscriptions 9.1.0.
- WordPress.org SureCart: current 4.7.0 on 2026-08-25; WP 6.8+, PHP 7.4+, tested WP 7.1.
- SureCart developer webhook/source-object documentation already captured in ADR-0062 provider profile research.

## Development gate

**No provider plugin installation/update/downgrade, Woo HPOS change, commerce object creation, webhook/API call, reconciliation run or Membership authorization test is authorized until explicit owner development consent under ADR-0014.**
