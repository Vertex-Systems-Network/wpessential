# Membership — Options Bank Seed Plan V1

Surface: **15 / Membership**
Issue: **#498**
Current Bank: **UNSEEDED / 0 records**
Atomic inventory: **ATOMIC_INVENTORY_COMPLETE**
Runtime implementation: **not authorized**

## Candidate families

- plan identity/lifecycle and visibility;
- pricing, billing interval, trials, signup fees and tax/provider references;
- membership states, expiration, grace periods and role/capability mapping references;
- upgrade/downgrade/change scheduling and provider boundaries;
- content/restriction rules and drip windows;
- coupons/discounts and usage constraints;
- checkout/account endpoints and consent fields;
- transaction/reconciliation records and idempotency requirements;
- notification/reporting hooks and privacy-safe diagnostics.

## Evidence pass

Benchmark established membership/subscription products and relevant WordPress/WooCommerce primitives using official documentation. Separate payment-provider execution from Membership-owned plan/state/restriction semantics.

## Ownership boundaries

Roles owns permission grants. Forms owns generic workflow/form execution. Emails/Notifications own delivery. Documents owns invoice/document generation. Payment provider adapters remain registered integrations; Membership must not store raw payment credentials.

## Promotion path

Seed normalized Bank records → native/platform audit → market audit → resolve ownership/unsafe/deferred records → `BANK_REVIEWED` only at zero unresolved → machine option contract → UX/gap matrix. No runtime or certification promotion occurs here.
