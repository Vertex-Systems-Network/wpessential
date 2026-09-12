# Notifications — UX Contract V1

Surface: **19 / Notifications**  
Machine source: `config/product/option-contracts/notifications.json`  
Lifecycle: **UX certification candidate**. Machine truth is `OPTION_CONTRACT_COMPLETE`; this UX contract does not dispatch notifications or promote runtime/product state.

## Lifecycle preconditions

- Machine status must remain `OPTION_CONTRACT_COMPLETE` or later with `missing=0` and `unclassified=0`.
- All 16 current Atomic Option IDs are mapped exactly once below.
- Eligibility, recipient resolution, scheduling, delivery evidence and preferences remain server-authoritative.

## Canonical route and information architecture

Canonical admin location: **WPEssential → Automation → Notifications**.

The IA separates **Rule Identity**, **Trigger & Conditions**, **Recipients**, **Eligibility**, **Priority & Channels**, **Content**, **Scheduling**, **Dedupe & Digest**, **Instance & Delivery**, **Preferences & Reliability**, and **Safety**. Authored rule definition is visually distinct from provider routing and delivery observations.

## UX state classes

### Authored definition
Rule identity, typed triggers, recipient/eligibility policy, priority, content tokens, dedupe/digest, inbox instance and preference/reliability policy are revisioned Notifications-owned definitions.

### Effective/runtime state
Resolved recipients, eligibility, schedule due state, generated instances, delivery attempts and preference application are effective/read-only context until separately authorized runtime operations exist.

### Diagnostic/provider state
Condition engine, Query/Role/Membership recipient sources, channel transports and Cron scheduling are canonical-owner/provider references with explicit availability/degraded state.

### Deferred / prohibited state
Raw hooks/executable callbacks are prohibited. Provider acceptance or transport success never becomes an assertion that the user received or read a notification.

## Atomic Option → UX map

- `notifications.rule.identity` — Rule Identity → rule name/category/lifecycle definition.
- `notifications.trigger.binding` — Trigger & Conditions → typed event/registered action binding; raw hooks are not authored.
- `notifications.condition.reference` — Trigger & Conditions → canonical condition-engine provider reference.
- `notifications.recipient.policy` — Recipients → bounded recipient resolution policy with authorization-aware preview.
- `notifications.recipient.references` — Recipients → Query/Role/Membership source references and dependency diagnostics.
- `notifications.eligibility.policy` — Eligibility → authorization/preference/quiet-hours policy and effective preview.
- `notifications.priority.class` — Priority & Channels → Expert priority/requiredness classification.
- `notifications.channel.routing` — Priority & Channels → delegated in-app/email/webhook transport mapping and health.
- `notifications.content.tokens` — Content → safe in-app content and allowlisted typed-token schema.
- `notifications.schedule.policy` — Scheduling → Cron-owned immediate/delay/date-time/timezone reference; timing guarantees remain explicit.
- `notifications.dedupe.frequency` — Dedupe & Digest → dedupe/frequency-cap policy with diagnostic key preview.
- `notifications.digest.policy` — Dedupe & Digest → grouping/digest/overflow policy.
- `notifications.instance.state` — Instance & Delivery → durable inbox state/expiry definition and effective instance state.
- `notifications.delivery.reliability` — Instance & Delivery → attempt/retry/idempotency evidence; delivery acceptance is not receipt proof.
- `notifications.preference.reliability` — Preferences & Reliability → user preference/retention/health/bounded fan-out policy.
- `notifications.safety.raw-hook` — Safety → prohibited arbitrary hook/executable callback evidence; never an authored control.

## Interaction and persistence

Rule editing uses draft → validate → save revision. Recipient preview is bounded, privacy-aware and clearly labeled as preview. Channel/schedule changes validate referenced providers before commit. No editor preview dispatches externally. Delivery rows are observations and are not edited into success.

## Loading, empty, validation, conflict and recovery

Required states include no rules, trigger loading/unavailable, invalid condition, no eligible recipients, channel provider unavailable, Cron degraded, schedule unknown, dedupe suppression, digest overflow, delivery retry/failed/unknown, preference-blocked, stale revision, saved and recovery. Unknown receipt remains unknown.

## Security and ownership

Roles/Policy and recipient owners retain authorization/source truth. Cron owns scheduling semantics. Email/Webhook/channel providers own transport execution. Notifications owns rule, in-app instance and reliability policy. Raw user-authored hooks/callbacks are prohibited. Delivery evidence is not user receipt evidence, and presentation visibility never grants access.

## Accessibility

Rule/recipient/channel editors require labels, keyboard access, visible focus and linked validation errors. Recipient counts and delivery states include text, not color only. Async preview/provider status uses live announcements. High-volume tables have semantic headers and keyboard-operable filtering.

## Multisite and scope

Rule, recipient-source and channel scope are explicit. Network context is server-derived. Imports cannot silently expand site-scoped recipients or channels to network/global scope.

## Portability and reference remapping

Exports are definition-only and secret-free. Imports validate condition, Query/Role/Membership, Cron and channel references and show unresolved mappings before save. Delivery attempts, user receipt state and provider credentials are not portable authored configuration.

## Performance and scale

Recipient preview/fan-out is bounded. Delivery/instance tables paginate and filter server-side. Dedupe/digest preview uses sampled/limited diagnostics. Provider health is cached conservatively without converting stale health into success truth.

## Degraded/provider states

Missing condition, recipient-source, Cron, email or webhook providers produce explicit unavailable/degraded states. The surface never fabricates eligible recipients, schedule execution, delivery, receipt or read state. Recovery links to the canonical owner/provider.

## UX lifecycle exit criteria

Certification requires complete 16-ID mapping, zero missing/unclassified machine semantics, raw-hook rejection, truthful delivery semantics, reviewed owner/provider/accessibility/portability/performance/degraded states and exact-head validator CI. Shared lifecycle promotion remains Supervisor-only.

## Non-certifications

Runtime implementation is not certified by this document. Product parity is not certified. Production deployment or release is not verified. No external notification/email/webhook dispatch, recipient mutation or provider execution is authorized by this UX contract.
