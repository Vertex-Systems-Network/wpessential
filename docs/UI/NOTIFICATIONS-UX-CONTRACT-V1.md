# Notifications — Provisional UX Contract V1

Surface: **19 / Notifications**  
Planning issue: **#585**  
Supervisor wave: **#583**  
Exact-main claim anchor: `3390739c85f45787743778372c616ef5c9f5d5df`

Lifecycle status: **PROVISIONAL PLANNING ONLY**. Surface 19 is `UNSEEDED / 0` in the Master Options Bank. This interaction contract must be re-reviewed after Bank review and schema-valid Atomic Option Contracts; it does not promote `UX_CONTRACT_COMPLETE` and does not authorize runtime delivery.

## Product model visible to users

The UI must keep these objects distinct:

- **Rule** — when, who and through which channel a notification should be created/routed;
- **Notification** — one logical recipient occurrence;
- **Delivery** — one channel/provider attempt;
- **Preference** — recipient-controlled behavior where policy permits;
- **Digest** — a bounded batch of notification references/snapshots.

A Rule is not a mail provider configuration, webhook credential store, Workflow engine or Cron implementation.

## Information architecture

Primary screens:

1. **Rules**
2. **Create/Edit Rule**
3. **Inbox / In-app Preview**
4. **Deliveries**
5. **Digests**
6. **Preference Defaults**
7. **Channels & Connections** — read-only/reference shortcut into the owning connection surfaces
8. **Diagnostics**

Every screen must make ownership boundaries legible. Missing Email templates, Connection profiles, Job health or typed Events are dependencies/degraded states, not fields that Notifications silently reimplements.

## Progressive disclosure

### Essential

The default Rule editor exposes the minimum safe authoring path:

- Rule name and stable generated key/identity;
- lifecycle: Draft / Enabled / Disabled / Archived;
- category;
- typed trigger/event selector;
- recipient source summary;
- preference class: Required system/security / Transactional/service / Optional subscription;
- priority: Low / Normal / High / Critical-system, with policy restrictions;
- one or more channels;
- content/template reference per channel;
- immediate vs delayed/digest choice;
- Validate, Preview and Save.

Essential never accepts arbitrary PHP hooks, callback/class names, raw JS/PHP conditions, provider secrets or unbounded recipient expressions.

### Advanced

Advanced adds:

- event/source/object scope filters;
- bounded Condition Engine rules;
- recipient includes/excludes, actor inclusion and max-recipient guard;
- recipient resolution timing;
- channel fallback mode;
- locale/fallback locale and recipient-locale source;
- quiet hours;
- digest cadence/grouping/overflow;
- dedupe policy/window;
- frequency caps;
- content snapshot policy for delayed delivery;
- expiration/dismiss/read behavior for in-app notifications;
- dependency and usage summary;
- revision history/usage where the eventual contract requires it.

### Expert

Expert is for high-impact operational policy, not executable code:

- registered recipient-provider selector;
- registered token-provider selector;
- bounded retry/backoff profile;
- idempotency strategy;
- provider/connection references;
- channel health/degraded behavior;
- delivery retention/log-redaction policy references;
- simple escalation event emission;
- batch/fan-out limits within platform ceilings;
- environment/portability diagnostics.

Provider-specific low-level credentials, OAuth tokens, webhook signing secrets, arbitrary headers and Safe HTTP policy stay in Connections/Webhooks or the channel adapter owner.

### System / Diagnostics

A dedicated read-only diagnostics tier shows:

- effective Rule revision;
- trigger adapter health/version;
- recipient-source dependencies;
- Email template/channel dependencies;
- Connection/provider health;
- Job/scheduler dependency health;
- preference-class constraints;
- effective retry/fan-out/platform ceilings;
- delivery/inbox storage health when implementation exists;
- redacted dependency identifiers;
- validation warnings/errors grouped by field and severity.

No System panel control bypasses Policy/Ability checks.

## Rules list

Columns:

- Name;
- Key;
- Status;
- Trigger/event;
- Recipient summary;
- Channels;
- Priority;
- Digest/batching summary;
- Last fired;
- Last delivery result;
- Failure/health summary;
- Updated;
- Actions.

Filters:

- status;
- trigger family;
- channel;
- priority;
- category;
- has failures;
- updated date.

Actions are permission-aware and include Edit, Enable/Disable, Preview/Test, Duplicate, Deliveries, Usage, Revisions/Export where later contracts support them, and Archive/Delete with dependency impact.

## Trigger UX

Trigger selector accepts only registered typed Event/Ability adapters.

Show:

- friendly event name;
- canonical event ID/version;
- owning module;
- adapter health/compatibility;
- available safe payload fields;
- dependency/degraded status.

Never expose a generic “WordPress hook name + callback” executable configuration path. If native hooks become supported, a registered adapter owns their mapping and payload schema.

## Conditions UX

Conditions use the shared declarative Condition Engine where available.

Allowed sources can include:

- typed event fields;
- current entity fields permitted by Policy;
- Query result/count references;
- recipient/user attributes permitted by schema;
- Membership/role/resource facts from owning surfaces.

The UI must label inaccessible/missing dependencies. Raw PHP/JS/expression execution is prohibited.

## Recipients UX

Recipient source selector may include:

- specific users;
- event actor;
- subject/resource owner;
- roles/capabilities;
- Query-defined users;
- relation-derived users;
- Membership/entitlement members;
- team owner/managers/members;
- registered recipient provider;
- explicit external address only when a selected channel supports and validates it.

Show a **dry-run count/preview** without exposing protected identities beyond current Policy.

Controls:

- include actor;
- excludes;
- deduplicate recipients;
- max recipients/run;
- resolve at trigger vs delivery time;
- batch/Job handoff warning for large audiences.

Recipient preview is not authorization for delivery. Protected resource content is re-authorized at render/delivery time.

## Priority and preference class

Priority and preference class are separate fields.

The UI must prevent ordinary optional/promotional rules from casually selecting a critical/system bypass. Required/system classification displays a strong explanatory warning and remains Policy-controlled.

Optional/subscription classes expose unsubscribe/preference implications. WPE does not claim jurisdiction-specific legal compliance; the site owner remains responsible for lawful configuration.

## Channels UX

Core channel cards:

- In-app/Admin;
- Frontend dashboard in-app;
- Email — references Surface 20 Email template/rendering;
- Webhook — references Surface 23 Connection/transport.

Future registered adapters may include push, Slack, Teams, SMS or WhatsApp/business providers only through reviewed adapters.

Per channel show:

- enabled state;
- template/content reference;
- provider/connection reference where applicable;
- delay/digest eligibility;
- fallback role;
- bounded retry profile;
- dependency health.

Email-safe markup is authored by Email Builder. Notification Rule does not accept arbitrary browser/Elementor HTML as an email template.

## Channel fallback

Modes:

- independent;
- ordered fallback;
- recipient preferred channel then fallback.

The UI distinguishes:

- queued;
- attempting;
- accepted by provider;
- confirmed delivered only with reliable provider evidence;
- failed/retrying/failed;
- suppressed/deferred states.

Provider acceptance must never be labelled as end-user delivery by default.

## In-app content

Fields:

- title;
- short body;
- type/icon from allowlisted presentation choices;
- primary action label/target;
- optional secondary action;
- media reference where access-safe;
- expiry;
- dismissible;
- mark-read-on-open.

Action targets use validated WPE/admin routes, local frontend routes, protected resource routes or allowlisted trusted external URLs. Possession of a notification never substitutes for action authorization.

## Tokens and conditional content

Token browser shows only schema-approved fields with source and sensitivity labels.

No generic `user.meta.*`, raw object dump, arbitrary shortcode execution or secret-bearing token source.

Preview highlights:

- resolved token;
- missing token;
- redacted/sensitive value;
- permission failure;
- locale fallback.

Bounded conditional blocks use the shared Renderer/Condition Engine and contain no executable template code.

## Scheduling and delay

Modes:

- immediate;
- delay duration;
- send at date/time;
- recipient-local time when a reliable timezone source exists.

The editor exposes trigger-time snapshot vs delivery-time current-state policy for approved fields. Durable waiting is delegated to Job/Cron ownership; no PHP request sleeps.

## Quiet hours

Controls:

- enabled;
- start/end local time;
- timezone source;
- days;
- defer/digest/skip behavior;
- policy-controlled priority bypass.

Unknown timezone uses an explicit documented fallback. Critical bypass cannot be silently enabled by an ordinary Rule.

## Digest UX

Modes: none, hourly, daily, weekly, approved custom interval.

Controls:

- recipient-local delivery time;
- max items;
- overflow link/behavior;
- grouping key;
- sort;
- duplicate collapse;
- suppress empty digest.

Digest preview shows representative safe data and never loads an unbounded object dump.

## Dedupe and frequency

Dedupe modes:

- none;
- event ID;
- custom safe key mapping;
- recipient + rule + entity within bounded window.

Duplicate handling can suppress or update supported in-app counters/state. Security/access-revocation notifications warn against over-broad dedupe.

Frequency controls are per Rule/category/channel and clearly distinguish optional marketing caps from required security/service behavior.

## Recipient inbox

Self-service inbox includes:

- unread count;
- paginated list;
- All / Unread / Category filters;
- mark read/unread;
- bounded mark-all-read;
- dismiss/archive;
- action link;
- empty/loading/error states.

Keyboard support, focus management and non-noisy live-region announcements are required. A user cannot enumerate another user's notifications without an explicitly elevated privacy capability.

## Deliveries screen

Columns:

- delivery ID;
- Rule/notification;
- masked recipient summary;
- channel;
- provider/connection;
- normalized status;
- attempt;
- queued/sent/confirmed timestamps;
- error category;
- correlation ID.

Detail view redacts credentials, secret headers, private webhook payload fields and full sensitive content by default.

Retry action is shown only where adapter state marks the failure retryable and current Ability/Policy allows it.

## Preferences UX

Self-service preference screen may expose, where policy permits:

- category/channel enablement;
- preferred channel;
- quiet hours;
- digest mode/time;
- language.

Required categories appear locked/explained rather than silently ignoring user input. Preference mutation is owner-authorized, privacy-safe and auditable at an appropriate level.

Admin default preferences are separate from an individual user's values.

## Preview and test

Preview modes:

- render with synthetic/sample authorized context;
- dry-run recipient count;
- explicitly labelled test recipient/channel output;
- dependency/provider expectation preview.

A test must never alter the real event state or be visually confused with a live production broadcast.

Bulk/live send remains separately gated and is not authorized by this planning contract.

## Disable/archive/delete behavior

Disable stops new triggers but requires an explicit queued-instance policy: keep vs cancel where cancellation is semantically safe.

Archive is preferred when delivery history/dependencies exist.

Delete requires dependency and queued-instance impact. Historical delivery evidence follows retention policy and is not silently erased by deleting a Rule.

## Accessibility requirements

- semantic headings/fieldsets/status labels;
- keyboard-operable Rule builder, inbox and delivery actions;
- no color-only priority/status;
- error summary linked to invalid controls;
- restrained `aria-live` for unread/status updates;
- deterministic focus after modal/save/mark/dismiss actions;
- no disappearing toast before accessible reading/action opportunity;
- touch targets and responsive tables/cards suitable for narrow admin layouts.

## Privacy and security UX requirements

- recipient previews obey Policy and minimize identity exposure;
- sensitive token sources are denylisted/redacted;
- provider secrets live outside Notifications;
- protected-resource content is re-authorized at delivery/render and action time;
- log screens are permission-separated from Rule editing;
- high-privacy “view another user's inbox” is a separate capability;
- no arbitrary executable input;
- no unbounded fan-out/retry controls;
- no misleading “delivered” status without reliable evidence.

## Multisite requirements

The eventual Bank/contract must explicitly choose and display scope for:

- site-local Rules and instances;
- network-managed defaults if authorized;
- network users vs site membership recipient resolution;
- provider/Connection scope;
- preference scope;
- delivery/log visibility.

The UX must not infer cross-site recipient/provider access from Super Admin status alone; canonical Policy owns authorization.

## Portability requirements

Rule export/import, when later contracted, should contain references to canonical Event/template/Connection/Query/etc. identities plus dependency metadata and no secrets. Import must provide environment conflict/missing-dependency preview before mutation.

## Lifecycle decision

This document is implementation-ready **interaction guidance**, but exact-main Master Options Bank status remains `UNSEEDED / 0`. It therefore cannot promote Surface 19 to `UX_CONTRACT_COMPLETE`.

After Bank review and schema-valid Atomic Option Contracts, re-review this UX against the normalized option inventory. Only then may the lifecycle ledger be considered for promotion.
