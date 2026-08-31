# WPEssential — Commercial Positioning & Packaging Strategy

Status: Phase 0 planning. Pricing is not final. No development authorized.
Last researched: 2026-08-27.

## Product position
WPEssential should not compete as “another custom fields plugin” or “30 mini plugins in one ZIP.”

Position:
> **One modular WordPress application platform for data modeling, admin/frontend apps, automation, identity/access, integrations and operations.**

The commercial argument is reduced integration debt:
- one schema/field model;
- one relation/query system;
- one Policy/Entitlement model;
- one workflow/event/ability model;
- one definition/version/export model;
- one UX/diagnostics/support surface.

## Current competitor pricing anchors
Current official pricing reviewed in August 2026 shows roughly:
- ACF PRO: $49/year 1 site, $149/10 sites, $249/unlimited.
- JetEngine: $75/year 1 site.
- Crocoblock All-Inclusive: $199/year 1 site; Plus $249/year; lifetime $999 unlimited.
- Meta Box: Basic $149/year unlimited sites; Ultimate $229/year unlimited; lifetime $699.
- Gravity Forms: regular $59/$159/$259 annual tiers for 1/3/unlimited sites, with feature/add-on gating.
- MemberPress: regular Launch/Growth/Scale pricing around $399/$699/$999 annually before current promotional discounts; Launch also carries transaction-fee economics.

These are market anchors, not a formula for WPEssential price.

## Pricing principle
Do not win by being cheapest. A very low price for a broad platform creates support/maintenance economics that are incompatible with the promised scope.

Pricing should reflect:
- number of sites;
- support/service level;
- advanced operational/agency features if tiered;
- product maturity;
- value of replacing several specialist plugins.

Avoid arbitrary per-module microtransactions that recreate the fragmentation WPEssential is supposed to solve.

## Recommended licensing shape
### Free
Permanent:
- CPT Builder
- Taxonomy Builder
- required platform kernel
- import/export for those definitions
- docs/changelog/diagnostics basics

Purpose:
- prove code quality and UX;
- acquire developers/site builders;
- create a useful product, not a demo shell.

### Pro
Recommended default commercial model: **all currently released Pro modules included in a plan**, with site-count/support tiers.

Why:
- cross-module composition is the differentiator;
- users should not need to calculate whether Query + Relations + Forms + Membership require separate purchases;
- simplifies entitlement/support/documentation;
- prevents artificial feature walls inside workflows.

A future lower-cost focused edition may be considered only if research shows clear demand, but architecture should not require it.

## Candidate annual price bands — research hypothesis, not final price
These are willingness/value-testing ranges, not launch commitments:
- 1 site: approximately **$149–$199/year**
- 5–10 sites: approximately **$249–$349/year**
- agency/unlimited: approximately **$399–$599/year**

Reasoning:
- above a single-purpose ACF/JetEngine entry license;
- around/beyond broad bundles when WPEssential genuinely replaces multiple systems;
- still substantially below stacking several mature Pro products.

Do not finalize pricing before reference applications and beta-support cost data exist.

## Lifetime license
Do **not** make lifetime licensing a default launch assumption.

A platform with:
- WordPress compatibility maintenance;
- builder/provider adapters;
- backup destinations;
- security updates;
- external APIs;
- support
has ongoing cost.

If lifetime is ever offered:
- limited campaign/quantity;
- clear support/update economics;
- no promise that paid third-party service costs are included forever;
- financially modeled before sale.

## No WPEssential transaction fee by default
Membership should integrate billing sources rather than become a payment processor. WPEssential should not require a percentage of users' member/store revenue as the default business model.

This is a differentiation opportunity against membership products that use transaction-fee entry tiers.

## Trial strategy
Requested model: 30-day Pro trial.

Trial must:
- use separately distributed Pro add-on/entitlement;
- not violate WordPress.org trialware restrictions;
- expose real Pro workflows, not fake demos;
- preserve created data after trial;
- become read-only/paused safely on expiry according to ADR-0007;
- show which definitions depend on Pro;
- allow export.

### Trial activation funnel
1. Free installed.
2. User experiences CPT/Taxonomy quality.
3. Contextual need appears (“Add fields/relations/query/form/member access”).
4. Show Pro capability preview and real benefit.
5. Account connect/signup.
6. Trial entitlement.
7. Pro add-on install/activate.
8. Guided reference workflow/template.
9. Measure activation success, not merely trial start.

## Avoid aggressive upgrade UX
Do not put upgrade notices on every admin page.

Upgrade surfaces should be contextual:
- Modules catalog;
- attempted Pro quick action;
- relevant empty-state capability;
- trial/account center.

No full-screen admin hijacks or unrelated dashboard spam.

## Commercial release sequencing
Do not wait for all 30+ modules before selling anything, but do not sell architectural promises as finished features.

### Release A — Free Foundation
- CPT
- Taxonomy
- platform reliability/UX

Acquisition only.

### Release B — Data Application Pro
Minimum strong commercial package:
- Custom Fields
- Relations
- Query Builder
- Admin Columns
- Listings/Templates
- Statuses

This directly competes with the strongest ACF/Meta Box/JetEngine use case and proves WPEssential integration.

### Release C — Portal / App Builder
- Settings
- Roles/Policy
- Profiles
- Frontend Dashboard
- Admin Menu/Widgets
- Builder adapters

### Release D — Forms & Automation
- Forms
- Workflow
- Cron/Jobs
- Notifications
- Email
- Webhooks/Connections

### Release E — Membership
Release only after Policy, Profiles, Dashboard, Forms, Email/Notifications and Job infrastructure are mature enough to support it.

### Release F — Operations
- Import/Export
- Backup/Restore
- Reset
- Protector
- Watermark
- XML-RPC

### Release G — Chat/advanced integrations/AI UX
After authorization/jobs/notifications have scale evidence.

## Reference applications as sales proof
Each commercial milestone needs a complete reference application that uses ordinary public product features.

1. **Directory / real estate** — content model, fields, relations, filters, listing, admin columns.
2. **Client portal** — roles/policies, dashboard, profile, CRUD forms, notifications.
3. **Approval workflow** — form → approval → delayed action → email/webhook.
4. **Membership portal** — plan/access rules, protected content/file, member dashboard, team seat, Woo billing adapter.
5. **Data app** — custom table + relation + query + REST.
6. **Recovery drill** — backup → destructive fixture → verified restore.

Never use hand-coded demo-only shortcuts that customers cannot reproduce.

## Differentiators to communicate
### 1. One definition, many uses
Field/query/relation defined once and reusable across admin, forms, listings, REST, workflow and AI.

### 2. Explainability
“Used By”, Query Explain, Access Explain, workflow run history and diagnostics reduce black-box behavior.

### 3. Safe power
No arbitrary eval/destructive SQL as normal UX; dry run, impact preview, revisions and backups for high-risk changes.

### 4. Performance isolation
Disabled/unused modules do not pollute unrelated wp-admin/frontend assets.

### 5. AI-native but permission-native
AI composes typed Abilities under the same permissions, rather than getting a privileged backdoor.

### 6. Non-destructive licensing
License expiry does not erase data or unexpectedly break/protect/unprotect production content.

## Audience segments
### Primary
- WordPress freelancers/agencies building custom sites/apps;
- advanced Elementor/Bricks/Gutenberg implementers;
- site builders currently stacking ACF/JetEngine/Gravity Forms/Admin Columns/role/membership/automation plugins.

### Secondary
- internal business portals;
- directories/marketplaces;
- membership/community businesses;
- WooCommerce application workflows;
- developers wanting UI-defined structures with extension APIs.

### Not primary initially
- users wanting only a simple contact form;
- enterprise customers requiring SLA/compliance certifications before those programs exist;
- customers expecting WPEssential to replace ERP accounting/inventory/payroll without domain-specific modules.

## Messaging caution
Do not market WPEssential as a literal “ERP” merely because it can build application workflows. Better claim:
- application platform;
- structured data/workflow platform;
- WordPress operating toolkit.

ERP implies domain accounting/inventory/procurement functionality that the base platform does not inherently provide.

## Conversion metrics
Track:
- Free activation → first CPT/Taxonomy published;
- contextual Pro interest;
- trial started;
- first Pro reference workflow completed;
- time-to-value;
- trial → paid;
- paid activation failures;
- churn/renewal reason;
- module adoption;
- support burden by module;
- sites where multiple WPE modules replace third-party products;
- license-expiry recovery/renewal without site breakage.

Avoid optimizing only trial-start count.

## Pricing review gates
Final public pricing requires evidence from:
- beta interviews;
- support cost/time;
- reference-app completion time vs competitor stack;
- agency willingness-to-pay;
- infrastructure/service cost;
- refund/trial conversion;
- competitor pricing near launch date;
- actual released module scope.

## Commercial Definition of Ready
Before paid release:
- advertised modules are Verified, not merely planned;
- pricing page maps exactly to implemented entitlements;
- refund/trial/renewal behavior is explicit;
- no dark-pattern expiry behavior;
- updater/license service failure degrades safely;
- documentation/support available;
- reference applications reproducible;
- security/performance/compatibility gates passed;
- packaging contains no unreleased feature promises represented as current functionality.