# ADR-0066 — Membership Billing Provider Version & Evidence Registry

Status: **Accepted compatibility/evidence architecture / executable certification pending**  
Date: 2026-08-28

## Context

ADR-0057 and ADR-0062 define source-truth and reconciliation semantics for Manual, WooCommerce orders, WooCommerce Subscriptions and SureCart. Those semantics still need an explicit compatibility rule: a provider/plugin name alone is not a stable API contract, and an MB certification result cannot be silently inherited by arbitrary future versions.

The current 2026-08-28 static snapshot also makes the problem concrete:
- WooCommerce stable is 11.0.1;
- WooCommerce Subscriptions current product version is 9.1.0 and a current security advisory directs affected sites to update to that line;
- SureCart WordPress plugin current version is 4.7.0;
- Woo HPOS is a first-class storage/runtime compatibility dimension;
- SureCart's WordPress plugin version and hosted API/webhook behavior are separate dimensions.

## Decision

The authoritative planning contract is `docs/ARCHITECTURE/MEMBERSHIP-BILLING-PROVIDER-VERSION-REGISTRY.md`.

Each future billing certification is scoped conceptually by:

`provider_key + provider_profile_version + source_plugin_version + source_api_schema/version + adapter_version + environment`

WPE records compatibility states explicitly, including:
- below supported floor;
- candidate static reviewed;
- certified exact/range;
- newer unverified;
- known incompatible;
- security blocked;
- degraded read only;
- reconciliation required.

### Newer version rule

A numerically newer provider/plugin/API version is **not automatically certified**.

Patch/minor/major provider changes are reviewed according to risk, and a future major release defaults to `newer_unverified` until a new profile/evidence campaign establishes compatibility.

### Security rule

WPE does not recommend pinning a known vulnerable source version simply because that older version previously worked.

A provider release affected by a security advisory can become `security_blocked`; compatibility work targets a secure version.

### WooCommerce rule

Woo profiles use supported public WooCommerce APIs/CRUD/data abstractions rather than direct order-post storage assumptions or `Automattic\WooCommerce\Internal` implementation details.

HPOS is a mandatory future certification dimension. Legacy posts storage/compatibility mode is supported only if separately included in the certified matrix.

### WooCommerce Subscriptions rule

The first future evidence campaign is anchored to the current secure 9.1.x line with WooCommerce 11.0.x, plus the immediately previous documented Woo compatibility line only if it remains current/security-valid when execution is authorized.

### SureCart rule

SureCart compatibility records both:
- WordPress plugin version; and
- separately versioned/reviewed hosted API/event profile.

Certifying only the local SureCart plugin while ignoring hosted source/event behavior is insufficient.

### Manual provider rule

Manual/Free Membership grants have no external provider version, but they are still versioned by WPE Platform API + Membership schema + profile/adapter version and still require MB runtime evidence.

## Current static snapshot

Paper reference only:
- `billing.manual` — WPE-owned; BE3; MB-certified 0;
- `billing.woocommerce-order` — WooCommerce 11.0.1 current snapshot; BE3; MB-certified 0;
- `billing.woocommerce-subscriptions` — WCS 9.1.0 / Woo 11.0 current snapshot; BE3; MB-certified 0;
- `billing.surecart` — SureCart WP 4.7.0 + separately tracked hosted API profile; BE3; MB-certified 0.

No current version number in this ADR grants runtime certification.

## Consequences

Positive:
- provider upgrades cannot silently invalidate authorization semantics;
- support can explain exact tested/certified ranges;
- security advisories can override stale compatibility claims;
- Woo HPOS and SureCart hosted API drift remain visible;
- version evidence is durable and auditable.

Cost:
- major provider releases require profile review/certification work;
- some newly released versions can temporarily show `newer_unverified` rather than Supported;
- support matrix maintenance becomes a continuing engineering responsibility.

## Evidence still required

After explicit owner development consent:
- exact provider/plugin version installs in isolated fixtures;
- Woo HPOS/legacy mode matrix where intended;
- source object/API/hook behavior;
- upgrade/downgrade and security-advisory behavior;
- reconciliation and duplicate/out-of-order events;
- cancel/fail/recover/refund/change/switch;
- clone/restore/test-live isolation;
- customer→WP identity mapping;
- Membership revoke-to-deny latency;
- MB0–MB5 certification.

No provider installation, commerce record, API/webhook call, HPOS switch or Membership authorization fixture was executed to accept this ADR.
