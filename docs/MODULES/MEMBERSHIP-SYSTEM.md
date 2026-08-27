# WPEssential — Membership System

Status: **Phase 0 — detailed product specification**  
Classification: **Pro**  
Slug: `membership`

## 1. Purpose

Provide a no-code membership/access platform for WordPress that can model free and paid memberships, multiple simultaneous memberships, tiers, add-ons, content/file/product access, drip schedules, benefits, seats, member lifecycle and entitlement-driven integrations without coupling access logic to one payment provider.

This module must integrate with WPEssential Fields, Relations, Query, Forms/Workflow, Profile, Dashboard, Roles, Notifications, Email, REST, Webhooks, Import/Export and Audit services.

## 2. Critical domain separation

WPEssential must keep these concepts separate:

- **WordPress User** — identity/account.
- **WordPress Role/Capability** — WordPress authorization primitives.
- **Membership Plan** — configured access/product definition.
- **Membership Enrollment** — a user's membership instance in a plan.
- **Subscription/Billing Contract** — external or integrated recurring-payment lifecycle.
- **Entitlement** — normalized grant such as `content.view`, `download.access`, `discount`, `dashboard.route`, `ability.execute`.
- **Access Rule** — policy matching a resource/context to entitlement requirements.
- **Benefit** — non-content privilege such as discount, quota, download, priority, badge, support tier.

Do not make role assignment the canonical membership engine. Optional role synchronization is an adapter/side effect.

## 3. Market bar researched

Current official product documentation shows the minimum expected market behavior:

- MemberPress: one-time/recurring terms, purchase permissions, membership groups/upgrades, rules, coupons and registration controls.
- Paid Memberships Pro: level groups, single/multiple level selection, free/one-time/recurring/trial/lifetime structures, expiration, user fields and content controls.
- WooCommerce Memberships: content/product restriction, fixed/set-length memberships, drip access, member discounts and recurring behavior through WooCommerce Subscriptions.
- SureMembers: centralized membership access groups, exclusions, drip, protected downloads, unauthorized redirects/messages and enrollment integrations.

WPEssential differentiates by making memberships consume the same typed Policy, Conditions, Relations, Workflow, Renderer and Abilities platform as the rest of WPEssential instead of creating a separate rules engine.

## 4. Module-owned data

Purpose-built tables are preferred for high-churn membership runtime data; configuration definitions use the shared Definition Repository.

### Definitions
- Membership Plan
- Plan Group
- Access Rule
- Benefit Rule
- Drip Rule
- Upgrade/Downgrade Path
- Enrollment Policy
- Seat Policy
- Membership Page/Message configuration
- Membership integration mapping

### Runtime records
- Enrollment
- Enrollment status history
- Entitlement snapshot/cache
- Grant/revoke history
- Seat allocation
- external billing/source references
- usage counters where benefits have quotas

Payment card data is never owned/stored by WPEssential.

## 5. Membership statuses

Canonical enrollment states:

- `pending`
- `trialing`
- `active`
- `grace`
- `paused`
- `pending_cancel`
- `cancelled`
- `expired`
- `revoked`
- `suspended`
- `error`

Adapters may expose external statuses, but they must map to a normalized state without discarding the raw external status/reference.

Every status change records actor/source, reason, timestamp, previous state, new state and correlation/run ID.

## 6. Admin information architecture

Parent: **WPEssential → Membership**

Sub-screens:

1. Overview
2. Plans
3. Plan Groups
4. Members / Enrollments
5. Access Rules
6. Benefits
7. Drip & Expiration
8. Upgrades / Downgrades
9. Coupons / Promotions (adapter-backed where billing owns discounting)
10. Seats / Teams
11. Pages & Messages
12. Integrations
13. Settings
14. Activity / Audit
15. Diagnostics
16. Import / Export

## 7. Overview screen

### Summary cards
- active memberships
- trialing
- grace/past-due equivalent normalized states
- expiring soon
- paused
- cancelled in period
- new enrollments in period
- manual/complimentary enrollments
- paid-source enrollments where source reports this
- seat utilization
- failed synchronization count

### Period control
- today
- last 7 days
- last 30 days
- current month
- previous month
- custom date range

Default: last 30 days.

### Charts / tables
Do not invent revenue analytics unless billing source provides trustworthy amount/currency/refund data. Access metrics and billing metrics must be visually distinguished.

### Alerts
- disconnected integration
- webhook failures
- orphan external subscription
- enrollment without valid plan
- plan with no access rules/benefits
- access rule matching deleted resource
- stuck pending/grace records
- entitlement rebuild backlog

## 8. Plans list

### Columns
- Name
- Slug
- Group
- Status
- Access model
- Billing source
- Enrollment count
- Active count
- Duration
- Rules count
- Updated

### Filters
- active/draft/archived
- group
- free/manual/external billing source
- duration type
- has/no access rules
- has/no integrations

### Row actions
- Edit
- Duplicate
- Preview access
- View members
- View dependencies
- Export
- Archive
- Delete (only when dependency/enrollment policy allows)

## 9. Plan editor — General

### `name`
- string; required; trim whitespace;
- recommended max 120 characters;
- public/internal display behavior separately configurable.

### `slug`
- stable machine key;
- lowercase sanitized slug;
- unique;
- generated from name by default;
- changing after external integrations exist requires impact warning.

### `status`
- Draft / Active / Archived;
- Active rules may affect frontend immediately;
- Archived plan cannot accept new enrollments but existing enrollments follow configured archive behavior.

### `description`
- rich but sanitized content;
- intended for plan cards/checkout/portal surfaces;
- renderer may expose a separate short description.

### `internal_notes`
- admin-only;
- never exposed through generic frontend tokens/API.

### `group_id`
- optional stable Plan Group reference.

### `priority`
- integer used only for defined conflict resolution;
- must not silently override explicit deny rules unless policy says so.

### `badge/icon`
- optional presentation metadata;
- never used as access semantics.

## 10. Plan editor — enrollment model

### `enrollment_mode`
Options:
- manual only
- free/self-enroll
- external purchase/subscription
- workflow/API only
- mixed

### `allow_new_enrollments`
Boolean; default true for Active plans.

### `who_can_enroll`
Condition builder using shared Conditions engine:
- guests
- authenticated users
- users with/without specified memberships
- roles/capabilities
- query/segment
- email/domain rules where explicitly enabled
- relation-based conditions

### `multiple_active_enrollments_same_plan`
Default false. If true, runtime semantics must explain whether entitlements merge and how duplicate external subscriptions are represented.

### `user_can_self_cancel`
Default depends on source. For external billing, cancellation action delegates to provider when supported instead of changing local state only.

### `approval_required`
If enabled:
- enrollment enters `pending`;
- approval ability/capability required;
- approval/rejection reason audit;
- optional workflow/notification.

### enrollment capacity
- unlimited by default;
- optional max active enrollments;
- optional waitlist integration planned separately;
- concurrency-safe capacity checks required.

## 11. Plan duration / lifecycle

### duration mode
- lifetime/unlimited
- relative duration from enrollment activation
- fixed start/end dates
- source-controlled (external subscription)

### relative duration
- quantity: positive integer
- unit: day/week/month/year
- timezone: site timezone for calendar semantics

### start behavior
- immediately on qualifying event
- specific date
- after payment/source confirmation
- after manual approval
- workflow-determined

### end behavior
- expire immediately at end timestamp
- move to grace period
- source-controlled

### grace period
- disabled by default
- quantity + unit
- configurable access during grace: full / selected entitlements / none
- warnings/notifications via workflow

### trial
Trial is an enrollment/access concept only when the billing source can represent it consistently.
- enabled
- duration
- trial access set: full or selected benefit/rule subset
- trial-to-active transition source
- trial expiration behavior
- prevent repeated trial abuse policy using user/history/source data

## 12. Plan Groups

Purpose: represent tiers or add-on families.

### group options
- Name
- Slug
- Description
- Selection mode:
  - exclusive: one active plan from group
  - multiple: multiple active plans allowed
- Sort order
- Public visibility
- Upgrade/downgrade enabled
- Default plan optional
- Comparison/pricing-table renderer settings delegated to Listings/Renderer

### conflict behavior
When an exclusive-group enrollment activates while another is active:
- block activation;
- replace immediately;
- schedule replacement at term end;
- delegate to billing switch adapter.

No implicit behavior; must be configured.

## 13. Access Rules

Access Rules are Policy definitions, not ad-hoc condition code.

### Resource types
- entire site (with exclusions)
- individual posts/pages/CPT records
- post type
- taxonomy term / taxonomy scope
- archive/query surface
- media/download
- menu/navigation item visibility
- block/component/shortcode region
- frontend Dashboard route
- WPEssential Listing
- Form
- REST endpoint/Ability where explicitly safe
- custom registered resource adapter

### Rule effect
- allow
- deny
- public override/exclusion

Deny/allow precedence must be explicit. Recommended policy: explicit resource-specific deny > explicit resource-specific allow > inherited rules, unless an Accepted ADR chooses another model.

### Subject conditions
- any active membership
- selected plans
- selected plan groups
- enrollment status
- active for minimum duration
- membership start/end windows
- member/user
- role/capability only as supplemental conditions
- relation/query segment
- benefit/entitlement present

### Time conditions
- immediately
- X time after enrollment activation
- fixed date/time
- between dates
- after trial
- recurring time window only if clear semantics exist

### Resource matching
Rules must preview estimated affected resources and show inherited vs direct matches.

## 14. Unauthorized behavior

Configurable globally, per plan/group and optionally per resource rule with clear precedence.

Options:
- return normal 403-style restricted page
- show message
- show teaser/excerpt
- hide protected body only
- redirect to login
- redirect to plan/pricing page
- redirect to custom validated internal URL
- return 404-like concealment only when intentionally selected

### Message options
- heading
- body
- login CTA
- signup/upgrade CTA
- button label
- target plan/listing
- renderer/template

### Redirect safety
- prevent loops;
- validate URL;
- external redirects disabled by default;
- preserve safe return URL only when appropriate.

## 15. Partial content restriction

Adapters:
- Gutenberg container block
- WPEssential shortcode
- WPEssential renderer condition
- Elementor/Bricks/WPBakery adapters where supported

Options:
- required plans/groups/entitlements
- inverse/non-member content
- delay/drip condition
- fallback content
- editor preview mode

Server-side enforcement is required; frontend-only hiding is never sufficient for sensitive content.

## 16. Protected files / downloads

Media Library URLs are public by default on typical WordPress hosting, so protected downloads require controlled delivery/storage semantics.

Options:
- source attachment/file
- allowed plans/entitlements
- expiration/signed URL strategy where adapter supports it
- max downloads per period
- max total downloads
- disposition inline/download
- filename override
- audit downloads toggle
- cache/CDN compatibility mode
- offload-storage adapter

Never claim a plain media attachment is protected merely because its page is hidden.

## 17. Benefits

Benefit types:
- content access
- protected download
- WooCommerce product viewing/purchasing permission adapter
- product/category discount adapter
- dashboard route access
- form access
- support priority/tier token
- quota/usage allowance
- badge/presentation
- custom entitlement registered by SDK

### Benefit fields
- Name
- Key
- Type
- Plan(s)
- Conditions
- Start delay
- End/expiry override
- quantity/value
- reset period for quotas
- stack/combine rule
- external adapter
- active flag

## 18. Product discounts / commerce benefits

WPEssential should not reimplement a commerce engine.

Adapter contract supports:
- percentage discount
- fixed discount where commerce system defines currency semantics
- selected products/categories
- minimum/maximum conditions
- member-only purchase/view restriction
- stacking/priority compatibility

The commerce adapter remains source of truth for checkout calculation and tax behavior.

## 19. Drip & expiration

### drip anchor
- enrollment activation date
- fixed calendar date
- trial completion
- external subscription start
- custom workflow timestamp

### unlock specification
- duration quantity/unit
- exact time optional
- resource/benefit selector
- notification on unlock

### expiry/revoke specification
- optional separate expiration from enrollment
- revoke at fixed date or relative duration
- notify before revoke

### preview
Admin can choose a member/enrollment/date and see:
- accessible now
- locked now
- unlock date/reason
- denial rule/reason

This is a major debugging feature and should reuse Policy explainability.

## 20. Upgrade / downgrade / cross-grade

Membership access transition and billing switch are separate but coordinated.

### transition path fields
- from plan/group
- to plan
- direction label: upgrade/downgrade/cross-grade/custom
- who may initiate
- availability conditions
- immediate vs term-end
- entitlement overlap behavior
- billing adapter action
- proration: provider-controlled / none / configured if provider supports it
- trial carryover policy
- enrollment history preservation
- workflow before/after

Never calculate proration locally when the external billing source is authoritative unless the adapter explicitly owns that calculation.

## 21. Billing / grant-source integrations

Core WPEssential Membership does not store/process card details.

Initial adapter targets:
- manual/complimentary enrollment
- WooCommerce order/product grant
- WooCommerce Subscriptions lifecycle
- SureCart purchase/subscription
- Forms/Workflow grant/revoke
- generic signed inbound webhook
- REST/Ability grant with strict permissions
- migration/import source

Future adapters may include external SaaS billing systems after demand/security review.

### integration mapping fields
- connection/source
- external product/price/plan identifier
- local membership plan
- qualifying external statuses/events
- activation mapping
- pause/grace mapping
- cancel/expire mapping
- refund mapping
- duplicate event handling
- reconciliation behavior

## 22. External event safety

Every payment/billing webhook adapter must define:
- signature verification
- replay protection
- event ID idempotency
- out-of-order event handling
- retry safety
- source timestamp
- local receipt timestamp
- raw payload retention/redaction policy
- reconciliation job
- manual replay only with privileged capability and audit

## 23. Members / Enrollments list

### columns
- User
- Plan
- Group
- Status
- Source
- Started
- Trial end
- Current term/end
- Grace end
- External reference
- Seats used where applicable
- Updated

### filters
- plan
- group
- status
- source
- expiring in X days
- trialing
- grace
- no external reference
- sync error
- date range

### row actions
- View
- Edit dates where local/manual ownership allows
- Change plan
- Pause/resume where allowed
- Cancel
- Revoke
- Grant benefit
- Rebuild entitlements
- Reconcile source
- View audit

External-source-controlled fields are read-only unless adapter supports an upstream mutation.

## 24. Enrollment detail

Tabs:
- Summary
- Entitlements
- Billing/source
- History
- Seats
- Usage
- Notes
- Audit/diagnostics

### manual override
Any manual override requires:
- specific capability;
- reason;
- optional expiry;
- clear precedence against source synchronization;
- audit event.

## 25. Seats / teams / corporate memberships

Designed as optional advanced membership capability, not assumed for every plan.

### seat policy
- enabled
- total seats
- owner/admin seats included or separate
- invite permission
- allowed email domains optional
- invite expiry
- role/profile defaults for sub-members
- whether sub-members receive full or selected entitlements
- transfer seat behavior
- remove member behavior

### team records
- membership enrollment owner
- allocated seats
- invites
- accepted members
- status

Concurrency-safe seat allocation is mandatory.

## 26. Coupons / promotions

When an external commerce/billing system owns checkout pricing, WPEssential should reference or surface its coupon objects rather than duplicate payment calculations.

WPEssential-native promotions may control **access behavior** only, for example:
- complimentary enrollment code
- time-limited invitation code
- plan-specific invitation
- max redemptions
- per-user redemption limit
- start/end date
- eligibility condition
- upgrade/downgrade allowed

Financial discount coupons belong to billing adapters.

## 27. Member-facing pages

Reusable route/page definitions:
- membership plans/pricing
- registration/enrollment
- membership account summary
- memberships list
- membership detail
- benefits
- downloads
- billing link/embedded provider adapter
- change plan
- cancellation
- team/seats

Pages use WPEssential Dashboard/Profile/Forms/Renderer rather than a separate page builder.

## 28. Global settings

### access defaults
- default unauthorized behavior
- default restricted message
- default login URL
- default plans/pricing page
- allow excerpt/teaser default
- search/archive visibility policy

### membership defaults
- default enrollment approval
- default grace behavior
- default plan archive behavior
- timezone basis

### role sync
- disabled by default
- plan → role mapping
- add role / replace role behavior
- removal behavior
- conflict behavior when multiple memberships map roles
- recovery/anti-lockout protection

Role sync is a side effect, never the source of truth.

### cleanup
- keep runtime history on module disable: always yes
- retention periods for audit/source payloads
- uninstall data deletion only through explicit uninstall policy

## 29. Cross-module integrations

### Custom Fields
Membership-specific user/enrollment fields use shared Field Schema.

### Relations
Membership/enrollment/user/team relationships are exposed as typed relations without duplicating relation definitions.

### Query
Queries can filter users/enrollments by plan/status/entitlement/expiry/source/usage.

### Forms & Workflow
Triggers:
- enrollment requested/created/approved/activated
- trial ending/ended
- status changed
- plan changed
- benefit granted/revoked
- drip unlocked
- seat invited/accepted/removed
- expiration approaching/expired
- reconciliation failed

Actions:
- grant/revoke membership
- approve/reject
- change plan
- pause/resume local/manual enrollment
- grant/revoke override entitlement
- invite/remove seat member
- rebuild/reconcile

### Notifications / Email
Lifecycle messages use centralized Notification + Email Builder.

### Dashboard / Profile
Access routes and membership account UI reuse these modules.

### Role Manager
Optional role sync and policy inspection.

### REST / Abilities
Typed membership operations subject to object-level policy.

### Import / Export
Definitions export separately from runtime member data. Secrets/external tokens never travel in ordinary config packages.

## 30. Abilities — initial contract candidates

Read:
- `wpessential/membership/list-plans`
- `wpessential/membership/get-plan`
- `wpessential/membership/explain-access`
- `wpessential/membership/list-enrollments`
- `wpessential/membership/get-enrollment`

Mutating:
- `wpessential/membership/create-plan`
- `wpessential/membership/update-plan`
- `wpessential/membership/grant-enrollment`
- `wpessential/membership/change-enrollment-status`
- `wpessential/membership/change-plan`
- `wpessential/membership/grant-entitlement-override`
- `wpessential/membership/revoke-entitlement-override`
- `wpessential/membership/reconcile-enrollment`

Dangerous operations require explicit confirmation metadata and cannot be exposed to AI merely because the Ability exists.

## 31. Capabilities — initial candidates

- `wpe_membership_read`
- `wpe_membership_manage_plans`
- `wpe_membership_manage_rules`
- `wpe_membership_read_members`
- `wpe_membership_manage_members`
- `wpe_membership_manage_integrations`
- `wpe_membership_reconcile`
- `wpe_membership_export`
- `wpe_membership_import`
- `wpe_membership_diagnostics`
- `wpe_membership_dangerous`

Final capability naming/grouping requires the platform authorization ADR.

## 32. Asset loading

Membership admin React/JS/CSS loads only on:
- WPEssential Membership screens;
- explicit WPEssential shared modal mounted from supported content editor integration;
- frontend membership components when actually rendered.

No membership bundle is enqueued globally across wp-admin or frontend.

## 33. Performance requirements

- members list default page size: 25; selectable 25/50/100;
- all large lists paginated server-side;
- access checks must avoid per-plan N+1 queries;
- resolved entitlement cache allowed only with deterministic invalidation;
- bulk grant/revoke/import runs in background batches;
- entitlement rebuild is resumable/idempotent;
- access rule preview is bounded;
- external reconciliation is queued and rate-aware.

Exact budgets require implementation benchmarks before acceptance.

## 34. Security requirements

Threats to test:
- IDOR reading/changing another user's enrollment;
- privilege escalation by granting high-value entitlement;
- bypass through direct REST/Ability calls;
- stale entitlement cache after revoke;
- unauthorized protected-file direct URL;
- forged/replayed webhook;
- open redirects in unauthorized behavior;
- seat over-allocation race;
- role-sync administrator escalation;
- export leaking PII/external IDs/secrets;
- bulk action CSRF;
- malicious renderer/template content;
- mass assignment of status/source/external reference fields.

## 35. Audit events

Initial event names:
- `membership.plan.created`
- `membership.plan.updated`
- `membership.plan.archived`
- `membership.rule.changed`
- `membership.enrollment.created`
- `membership.enrollment.status_changed`
- `membership.enrollment.plan_changed`
- `membership.entitlement.granted`
- `membership.entitlement.revoked`
- `membership.override.created`
- `membership.integration.event_received`
- `membership.integration.event_rejected`
- `membership.reconciliation.completed`
- `membership.reconciliation.failed`
- `membership.seat.invited`
- `membership.seat.changed`

No payment-card details, secrets or full sensitive webhook payloads in audit logs.

## 36. Import / migration

### Configuration import
- plans/groups/rules/benefits/drip/paths;
- dependency preview;
- UUID remapping;
- resource mapping;
- no credentials.

### Member data import
- user matching by explicit key;
- plan mapping;
- status/source mapping;
- start/end dates;
- external IDs only if source semantics are understood;
- dry run;
- duplicate handling;
- chunking;
- validation error CSV/report;
- optional post-import entitlement rebuild.

Migration adapters for MemberPress/PMPro/Woo Memberships/SureMembers are candidates, but must be researched against their public/stable APIs and data ownership before implementation.

## 37. Disable / Pro expiry behavior

### Module disabled by admin
- no new enrollment automation;
- existing data retained;
- access enforcement behavior requires explicit safe mode rather than accidentally making protected content public;
- recommended default: continue minimal access enforcement using stored definitions while management UI is disabled, with clear warning.

### Pro entitlement expired
- definitions/data remain;
- existing access enforcement continues;
- existing safe member-facing pages continue;
- creation/editing/import and high-risk automated mutations may become read-only/paused according to global commercial contract;
- never expose previously restricted content merely because the license expired.

This is a security-critical extension of ADR-0007.

## 38. Minimum acceptance tests

- create free lifetime plan;
- create fixed-duration manual plan;
- multiple plans in exclusive and multi-select groups;
- activate/cancel/expire/grace transitions;
- overlapping allow/deny/public-override rules;
- fixed and relative drip;
- partial-content restriction;
- direct protected-download denial;
- admin/manual grant with audit;
- duplicate external webhook idempotency;
- out-of-order external status events;
- upgrade/downgrade adapter failure;
- multiple simultaneous memberships and entitlement union/conflict;
- expired plan with cached access;
- module disable does not expose content;
- Pro expiry does not expose content;
- seat allocation concurrent race;
- role sync cannot remove current administrator recovery access;
- bulk import rollback/error reporting;
- object-level REST/Ability authorization;
- accessibility of member/admin management screens.

## 39. Open ADRs / spikes before implementation

1. exact allow/deny precedence and explainability model;
2. membership runtime table schema/indexes;
3. entitlement cache strategy/invalidation;
4. protected-file delivery strategy across Apache/Nginx/CDN/offload;
5. initial billing adapter scope and contracts;
6. role synchronization conflict semantics;
7. module-disable enforcement behavior;
8. privacy/retention defaults for enrollment history and integration payloads;
9. migration scope from competing membership plugins;
10. multi-currency financial metadata representation without becoming payment source of truth.

## 40. Research references

- MemberPress membership creation/options/rules/coupons: https://memberpress.com/docs/
- Paid Memberships Pro levels/groups/content/user fields: https://www.paidmembershipspro.com/documentation/
- WooCommerce Memberships plans/restriction/subscriptions integration: https://woocommerce.com/document/woocommerce-memberships/
- SureMembers memberships/content protection/drip: https://suremembers.com/docs/

These references establish competitive expectations; they are not implementation contracts and must be rechecked when the relevant adapter/module enters development.
