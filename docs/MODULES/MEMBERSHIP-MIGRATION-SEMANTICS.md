# WPEssential — Membership Migration Semantics

Status: **Phase 0 planning — no importer/provider implementation authorized**

This document defines candidate source-state translations for future migration adapters. Source statuses are facts from another product; they are never trusted as WPEssential authorization decisions without normalization.

Canonical WPE Enrollment states remain:
- `pending`
- `trialing`
- `active`
- `grace`
- `paused`
- `expired`
- `revoked`

Separate state metadata includes cancellation intent, source billing state, source status, start/end dates and external references.

---

# General rules

1. Import source status/history first; derive WPE Enrollment state second.
2. Preserve `source_status` and source-version metadata.
3. Access is recomputed from WPE Plan + Enrollment + Access Rules after migration.
4. Cancellation intent is not automatically an inactive state.
5. Remote billing/subscription state is stored as an external Billing Source reference.
6. Import never charges/refunds/cancels a remote subscription unless a separately approved migration operation explicitly does so; ordinary migration is read/normalize only.
7. Source custom statuses force a mapping review unless an adapter-certified rule exists.
8. If source status and dates conflict, mark `conflict`/manual review; do not choose the most permissive interpretation.
9. If a source membership has access despite a nominally inactive billing state, preserve that as an access-equivalence warning and require a rule mapping.
10. Migration verification samples protected resources before source deactivation readiness is claimed.

---

# WooCommerce Memberships

Official source states include:
- Active
- Free Trial
- Complimentary
- Delayed
- Pending Cancellation
- Paused
- Expired
- Cancelled

WooCommerce Memberships also allows custom statuses through filters, so unknown statuses are possible.

## Candidate mapping

| Woo Membership status | WPE Enrollment | Additional metadata | Fidelity |
|---|---|---|---|
| `active` | `active` | preserve source order/product/subscription refs | exact candidate |
| `free_trial` | `trialing` | preserve trial dates/content-after-trial behavior separately | convertible |
| `complimentary` | `active` | `grant_source=manual/complimentary`, no billing entitlement implied | convertible |
| `delayed` | `pending` | preserve scheduled `starts_at` | convertible |
| `pending` / Pending Cancellation | `active` until effective end | `cancel_at_period_end=true`, preserve source end date | convertible |
| `paused` | `paused` | preserve pause date/reason/source subscription | exact candidate |
| `expired` | `expired` | preserve natural end date | exact candidate |
| `cancelled` | `revoked` or `expired` after reason review | preserve cancelled date/reason | convertible |
| custom/unknown | unresolved | source status + access-capability evidence | conflict |

## Why cancelled is not blindly `expired`
Woo distinguishes cancellation from natural expiration. WPE retains that distinction through reason/source metadata even if both remove access. A manually/refund-triggered cancellation may semantically fit `revoked`; end-of-term natural completion fits `expired`.

## Subscription linkage
Woo Memberships CSV/import can carry a `subscription_id`, but membership import/export does not itself migrate recurring billing. A linked Woo Subscription is imported as a Billing Source reference only after the Woo Subscriptions adapter verifies it.

## Subscription statuses
Woo Subscriptions currently includes:
- pending
- active
- on-hold
- pending-cancel
- cancelled
- expired

Candidate billing-source translation is separate from Enrollment:
- `active` → billing healthy;
- `on-hold` → billing interrupted/suspended fact; Plan policy decides grace vs pause;
- `pending-cancel` → cancellation intent, not immediate access loss;
- `cancelled` → billing ended;
- `expired` → billing term ended;
- `pending` → billing not yet active/confirmed.

Do not let these values directly authorize requests.

---

# Paid Memberships Pro

Current PMPro history/status records include:
- `active`
- `admin_cancelled`
- `admin_changed`
- `cancelled`
- `changed`
- `expired`
- `inactive` (legacy/general inactive)

PMPro can support multiple membership levels per user. Membership billing, access expiration and gateway cancellation can have related but distinct dates/actions.

## Candidate mapping

| PMPro status | WPE Enrollment | Additional metadata | Fidelity |
|---|---|---|---|
| `active` | `active` | preserve start/end and billing ref | exact candidate |
| `expired` | `expired` | natural expiry | exact candidate |
| `cancelled` | `revoked` or scheduled-active depending end date/history | `cancellation_actor=user` | convertible |
| `admin_cancelled` | `revoked` or scheduled-active depending end date/history | `cancellation_actor=admin` | convertible |
| `changed` | terminal historical Enrollment + linked successor if identifiable | `transition_reason=member_changed_level` | convertible |
| `admin_changed` | terminal historical Enrollment + successor | `transition_reason=admin_changed_level` | convertible |
| `inactive` | unresolved historical inactive | inspect end/modified/successor history | lossy/conflict |

## Multiple levels
Each active PMPro user-level membership becomes a separate Enrollment candidate. Level Group rules are mapped into WPE Plan Groups/exclusivity only when source group semantics are known.

If multiple source memberships would violate the chosen WPE Plan Group exclusivity, migration must flag conflict rather than arbitrarily retaining one.

## Cancellation at end of paid term
PMPro can cancel recurring payment while allowing access through a specified expiration date. Such a source record maps to:
- WPE Enrollment = `active` until `ends_at`;
- `cancel_at_period_end=true`;
- Billing Source = cancelled/stopped externally;
- transition to `expired` at effective end unless separately revoked.

---

# MemberPress

MemberPress presents member access at a high level as Active / Inactive / None, while subscriptions have separate rebill states such as Enabled / Pending / Paused / Stopped. Transactions and recurring subscriptions are migrated separately.

## Access candidate mapping

| MemberPress access view | WPE | Notes |
|---|---|---|
| Active | `active` or `trialing` after product/transaction inspection | High-level status alone is insufficient for trial/billing details. |
| Inactive | `expired` or `revoked` after transaction/subscription/history inspection | Do not infer reason from UI label. |
| None | no Enrollment | User exists but no membership history/grant. |

## Subscription rebill state mapping

These remain Billing Source facts:
- `enabled` → recurring billing configured active;
- `pending` → gateway/registration state unresolved;
- `paused` → recurring billing paused/suspended;
- `stopped` → local rebill flag stopped, but MemberPress documentation warns this alone does not necessarily cancel the customer at the gateway.

Therefore `stopped` must never be interpreted as verified remote cancellation without gateway/provider evidence.

## One-time transactions
A one-time MemberPress transaction can create an Enrollment interval where the transaction/product semantics grant access. Missing expiration can represent lifetime access in MemberPress imports; WPE migration must make that explicit instead of defaulting silently.

## Recurring subscriptions
Preserve processor subscription identifiers. Do not create a new charge or remote subscription during migration. A recurring source must reconcile against a supported provider/billing adapter before the target can label the external billing link verified.

---

# Cross-source state reason model

Enrollment state alone is intentionally insufficient. Candidate reason metadata:

- `natural_expiration`
- `manual_grant`
- `complimentary_grant`
- `trial_started`
- `trial_completed`
- `scheduled_start`
- `payment_failure`
- `billing_on_hold`
- `user_cancelled`
- `admin_cancelled`
- `refund`
- `chargeback_dispute`
- `plan_changed`
- `admin_plan_changed`
- `source_deleted`
- `manual_revoke`
- `migration_normalized`
- `unknown_source_reason`

Source-specific raw reason is also retained where safe.

---

# Dates

Migration must separately normalize:
- membership `starts_at`;
- membership `ends_at`;
- trial start/end;
- cancellation requested-at;
- effective cancellation/end;
- paused-at/resumed-at;
- billing period start/end where provider makes it available;
- source timezone/UTC interpretation.

A single `expiration_date` must not be overloaded to represent all of these.

---

# Access-equivalence verification

Before claiming a membership migration successful, fixture or sampled verification must answer:

1. Did every expected active source member receive target access?
2. Did every source non-member/inactive member remain denied where expected?
3. Do trial members have the same trial-specific access/drip restrictions?
4. Do delayed/scheduled memberships remain denied before start?
5. Do pending-cancellation users keep access only for the correct remaining term?
6. Do paused members lose access if source semantics say they should?
7. Are complimentary/manual memberships independent of billing?
8. Are multi-membership entitlement unions equivalent?
9. Are product discounts/benefits equivalent or explicitly marked lossy?
10. Are remote billing references present without accidental new billing actions?

Any unexplained access difference blocks source-deactivation readiness.

---

# Historical migration

Where source history is available, WPE should preserve prior intervals as immutable historical Enrollments or normalized history records rather than rewriting all old states into one current Enrollment.

Current authorization derives only from the current applicable Enrollment/Entitlement state, not from historical records.

---

# Custom statuses

Woo Memberships and other products/extensions can introduce custom states. General rule:

- adapter recognizes certified custom status → versioned mapping;
- unknown status with source-reported active-access evidence → manual mapping review, not automatic allow;
- unknown status without access evidence → fail closed for target access until resolved;
- original status retained in migration report/history.

---

# Development gate

This document is a semantic plan. No MemberPress, PMPro, Woo Memberships or Woo Subscriptions migration code, database reads, gateway calls or executable fixtures are authorized until explicit owner consent under ADR-0014.