# WPEssential — Membership Competitive Landscape

Status: **Phase 0 research note**  
Research date: 2026-08-27

This note records the market capabilities that informed `docs/MODULES/MEMBERSHIP-SYSTEM.md`. It is not a claim that every competitor feature is desirable or that WPEssential has implemented any of them.

## Research rule

Prefer current official product documentation. Re-check these sources when the Membership implementation milestone begins because pricing, APIs, extension points and feature boundaries can change.

---

## 1. MemberPress

Official docs: https://memberpress.com/docs/

Observed market expectations include:
- membership products with one-time or recurring terms;
- pricing/access terms and registration controls;
- purchase permissions / eligibility restrictions;
- membership groups that support upgrade/downgrade style choices;
- content access Rules;
- coupons/promotions;
- member account and lifecycle management;
- integrations/add-ons for billing and automation.

### WPEssential implication
A serious Membership module needs more than post restriction. Plans, enrollment eligibility, upgrade paths, rules and lifecycle must be first-class.

### Opportunity
MemberPress is membership-centric. WPEssential can differentiate by letting the same Policy/Entitlement objects control Dashboards, Forms, Listings, REST/Abilities, protected files, Notifications and Workflows without separate rule engines.

---

## 2. Paid Memberships Pro

Official docs: https://www.paidmembershipspro.com/documentation/

Observed market expectations include:
- membership levels;
- level groups;
- groups that allow either one or multiple selections;
- free, one-time, recurring, trial and expiration arrangements;
- content restriction;
- user/member fields;
- member/account pages;
- extensive hooks/add-ons.

### WPEssential implication
The data model must support simultaneous memberships. A single `membership_level_id` on a user is too restrictive.

Plan Groups need explicit `exclusive` vs `multiple` behavior, and access evaluation must merge entitlements deterministically when several enrollments are active.

### Opportunity
Use the shared Field Schema and Query engine for member attributes/segmentation instead of a membership-only user-field/query implementation.

---

## 3. WooCommerce Memberships

Official docs: https://woocommerce.com/document/woocommerce-memberships/

Observed market expectations include:
- membership plans;
- restriction of content and products;
- content dripping/delayed access;
- member discounts;
- fixed/set-length membership periods;
- granting membership from WooCommerce purchases;
- recurring billing/lifecycle behavior when used with WooCommerce Subscriptions;
- switching/synchronization considerations between membership and subscription state.

### WPEssential implication
Membership access and billing subscription cannot be the same record. WooCommerce/Subscriptions must be an adapter/source, while local Membership Enrollment and Entitlements are normalized domain objects.

Discount calculation belongs to the commerce system, not WPEssential Membership. WPE may define a benefit and configure the adapter, but checkout/tax/proration remain provider responsibilities.

### Opportunity
WPEssential can support WooCommerce without making WooCommerce mandatory. Manual, workflow, SureCart, signed webhook and future provider grants can feed the same membership model.

---

## 4. SureMembers

Official docs: https://suremembers.com/docs/

Observed market expectations include:
- access groups/memberships;
- content protection from a centralized UI;
- exclusions;
- drip content;
- protected downloads/media-related access flows;
- unauthorized-user messages/redirect behavior;
- enrollment integrations;
- role synchronization-related workflows.

### WPEssential implication
Unauthorized behavior is a first-class UX contract: 403/message/teaser/login/plan CTA/internal redirect need deliberate precedence and loop protection.

Protected files need actual controlled file delivery. Merely hiding a post/page containing a Media Library URL is not sufficient protection.

### Opportunity
Make access decisions explainable: choose a member, resource and time, then show which rule/entitlement allowed or denied access and when drip unlocks.

---

## 5. Restrict Content Pro

Official product/developer documentation: https://restrictcontentpro.com/

Observed market expectations include:
- membership levels/subscription levels;
- role-related membership behavior;
- trial and renewal/expiration concepts;
- content restriction;
- extensibility.

### WPEssential implication
Role synchronization should exist for compatibility, but must remain a side effect. Using a WordPress role as the canonical membership record makes multiple memberships and billing reconciliation fragile.

---

# Cross-market baseline

A competitive WPEssential Membership release should plan for at least:

- free/manual and paid-source memberships;
- plans and plan groups/tiers;
- multiple simultaneous memberships where group rules permit;
- enrollment states/history;
- one-time/fixed/lifetime/source-controlled durations;
- trial and grace semantics;
- access/content rules;
- partial-content restriction;
- drip;
- protected downloads;
- member benefits/discount adapters;
- upgrades/downgrades;
- account/member portal;
- coupons/invitation/promotional access where ownership is clear;
- role synchronization compatibility;
- integrations/webhooks;
- lifecycle email/notifications;
- import/migration;
- audit and diagnostics.

Feature presence alone is insufficient. WPEssential should outperform through cross-module consistency, debugging, security, and failure recovery.

---

# WPEssential strategic differentiation

## 1. Entitlement-first architecture

Plans/enrollments grant normalized entitlements. Consumers depend on entitlements/Policy rather than specific payment plugins.

This enables the same grant to protect:
- WordPress content;
- partial blocks/components;
- files/downloads;
- frontend Dashboard routes;
- Forms;
- Listings;
- Settings/features;
- REST endpoints/Abilities where safe;
- Chat initiation;
- support tiers/quotas;
- third-party registered resources.

## 2. Explainable access

Every important access decision should support diagnostics such as:
- subject/user;
- resource/action;
- matched plans/enrollments;
- entitlements;
- allow/deny rule;
- precedence;
- drip/time condition;
- cached vs recomputed result;
- reason for denial.

This should reduce one of the hardest support problems in membership products: “Why can/can't this member see this?”

## 3. Shared automation

Membership does not create its own mini automation engine. Lifecycle events go to the WPE Event/Workflow system and can drive email, notification, webhook, CRUD, role sync, dashboard behavior and external integrations.

## 4. Billing independence

WPEssential is not a payment-card processor. Billing adapters normalize external lifecycle events into Enrollments while preserving raw external status/reference for diagnostics.

## 5. Safety under license/module failure

A WPEssential commercial entitlement problem must never accidentally expose protected membership content. Access enforcement needs a documented safe last-known behavior.

## 6. Application-platform integration

The Membership module becomes substantially more valuable when combined with Query, Relations, Profiles, Dashboards, Forms and Listings. This is the primary product-level advantage over treating membership as a standalone paywall plugin.

---

# Research-driven risks requiring spikes/ADRs

- allow/deny precedence across nested/global/resource-specific rules;
- protected download architecture across Nginx/Apache/CDNs/media offload;
- entitlement cache invalidation and revocation latency;
- external subscription event ordering/reconciliation;
- upgrade/downgrade proration ownership;
- simultaneous membership conflicts;
- seats/team concurrency;
- role-sync anti-lockout;
- privacy/retention for enrollment and billing-event history;
- migration fidelity from competing membership products;
- checkout/payment provider adapter contracts.

These are explicit blockers, not implementation details to guess later.
