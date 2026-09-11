# Emails — Provisional UX Contract V1

Surface: **20 / Emails**  
Planning issue: **#586**  
Supervisor wave: **#583**  
Exact-main claim anchor: `40a56e1da1ae59c4441176eda59d43eabe0cd8bc`

Lifecycle status: **PROVISIONAL PLANNING ONLY**. Surface 20 remains `UNSEEDED / 0` in the Master Options Bank. This document guides later review; it does not promote `UX_CONTRACT_COMPLETE` or authorize mail delivery.

## Product boundary

Email Builder owns **email-safe template/rendering semantics**. It is not a generic webpage builder and it does not own Notification recipient/event policy, transport credentials or provider delivery truth.

The UI must never imply that arbitrary frontend HTML/CSS/JS can be pasted into a template and rendered reliably in all clients.

## Information architecture

Primary screens:

1. Templates
2. Create/Edit Template
3. Layouts / Headers & Footers
4. WordPress Email Overrides
5. Branding
6. Preview / Test
7. Sender Profiles — dependency shortcut/read-only health
8. Diagnostics

Delivery logs link to the owning Notification/transport evidence surface rather than creating a second delivery ledger.

## Progressive disclosure

### Essential

Default template authoring exposes:

- Name / stable key;
- Draft / Published / Archived;
- purpose/category;
- default locale;
- optional base layout;
- subject;
- preheader;
- email-safe canvas;
- plaintext preview/state;
- Validate / Preview / Save.

The block chooser contains only approved email-safe blocks. Unsupported browser widgets, scripts, forms, iframes and arbitrary frontend components are absent rather than merely warned.

### Advanced

Adds:

- locale variants and fallback;
- reusable layout revision behavior;
- brand preset;
- conditional blocks;
- token formatting/fallbacks;
- bounded Query/repeater data;
- responsive/mobile safe controls;
- attachments as authorized references;
- optional tracking policy;
- preferences/unsubscribe block;
- dependency/usage summary;
- import/export preview when later contracted.

### Expert

Adds high-impact but still declarative controls:

- registered custom email block providers;
- registered token/data providers;
- sender profile reference;
- WordPress/third-party override adapter bindings;
- strict sanitizer/renderer profile;
- source/generated HTML **read-only developer view** behind capability;
- client-compatibility warnings;
- renderer/template health details;
- revision pin/follow-current policy.

No Expert field accepts raw executable PHP/JS/callback/class input or transport secrets.

### System / Diagnostics

Read-only system diagnostics show:

- template/layout revision;
- renderer/sanitizer version;
- missing token/layout/brand dependencies;
- sender profile health without credentials;
- linked rules/events count;
- unsupported/degraded override adapters;
- HTML/plaintext size;
- bounded repeater/query warnings;
- attachment totals;
- accessibility warnings;
- generated output/source only where Policy allows.

## Templates list

Columns:

- Name;
- Key;
- Status;
- Purpose/category;
- linked events/rules count;
- Base layout;
- locale variants;
- Updated;
- Revision;
- Health;
- Actions.

Filters: lifecycle, default-override/custom, category, locale, linked/unlinked, needs-attention.

Actions: Edit, Preview, Test, Duplicate, Publish/Unpublish, Usage, Revisions, Export, Archive/Delete subject to later reviewed contracts.

## Canvas contract

Approved core blocks:

- Section/container;
- Columns;
- Text;
- Heading;
- Button;
- Image;
- Divider;
- Spacer;
- List;
- bounded data/key-value table;
- bounded repeater/list;
- logo/brand block;
- social links;
- legal/preferences footer;
- dynamic token/text;
- conditional block;
- registered email-safe custom block.

The editor may look visual, but its source of truth is a structured email schema, not arbitrary browser DOM.

## Block editing rules

Every control must reflect renderer/client support. If a property is unreliable, hide it or mark its compatibility rather than promising universal output.

Examples:

- width/padding/border/radius use bounded email-safe values;
- columns use supported table/email rendering and bounded mobile stacking;
- text uses allowlisted semantic formatting;
- URLs validate schemes and dynamic URL providers;
- image alt text is required unless explicitly decorative;
- repeaters/tables have hard row limits;
- responsive options are limited to renderer-supported behavior.

## Tokens

Token browser groups approved providers such as Site, Recipient, Event, Entity, Form Entry, Membership, Query and Date/Time.

Each token displays:

- type;
- source;
- privacy classification;
- escaping mode;
- nullable fallback;
- available safe formatters.

Never expose password hashes, app passwords, OAuth/API credentials, secrets, raw user meta or arbitrary object dumps.

Preview distinguishes missing, redacted, unauthorized and resolved tokens.

## Conditional content

Conditional blocks use shared declarative conditions. No raw PHP/template language.

Conditions may reference only schema-approved recipient/event/entity/membership/query/locale facts and must render deterministically from the chosen template revision/context.

## Metadata and sender boundary

Template controls subject/preheader and may **reference** an allowed sender profile. Sender profile management, transport credentials and provider verification remain with the connection/transport owner.

Safe sender display may include From name/address/domain, Reply-To and provider health. Credentials never appear.

SPF/DKIM/DMARC status is shown only when a real diagnostic provider can verify it; configuration alone is not “verified”.

## Layouts and branding

Reusable layout supports outer shell, header, body slot and footer. Template can follow current published layout revision or explicitly pin a revision where deterministic requirements justify it.

Changing a published layout shows impacted templates before publish.

Branding controls are structured tokens (logo/colors/type/contact/social), not remote HTML/CSS imports.

## Plaintext

Every send-ready template exposes a plaintext preview.

Modes:

- auto-generate from structured blocks;
- custom plaintext override.

Auto-generation removes decorative-only content and converts buttons/images/tables/lists into readable text/link forms without raw HTML remnants.

## WordPress email overrides

Overrides screen lists **registered stable event adapters**, not all `wp_mail()` traffic.

Per adapter:

- event name/owner/version;
- Default / WPE Override state;
- template selection;
- supported subject override;
- recipient-ownership explanation;
- sample/test context;
- restore default;
- health state: supported / partial / unavailable.

If WordPress lacks a stable semantic override, mark it Partial/Unsupported. Do not intercept mail by fragile string matching.

## Third-party adapters

Only certified adapters appear. Each shows supported plugin versions, event schema, override scope and fallback-to-original behavior.

Missing/outdated adapters degrade safely instead of breaking the plugin's native mail path.

## Attachments

Attachment slots reference approved static media, generated documents or event/form files under Policy.

Show count, total size, MIME restrictions and private-file exposure warnings. Prefer secure expiring links for large/private files where the owning resource supports them.

No attachment resolution bypasses file/resource authorization.

## Tracking and preferences

Open/click tracking is privacy-sensitive and defaults to off at planning level unless a later reviewed policy says otherwise.

When enabled, show provider capability and privacy implication; never claim perfect accuracy because image blocking/privacy proxies distort signals.

Optional/subscription templates can include unsubscribe/manage-preferences blocks. Required transactional/security messages remain separately classified by Notifications policy.

## Preview and test

Preview modes:

- Desktop width;
- Mobile width;
- Plaintext;
- token/context inspector;
- generated HTML/source developer view where capability permits.

Preview is approximate client rendering, not proof of Gmail/Outlook parity.

Test-send preflight surfaces:

- missing/unauthorized tokens;
- invalid URLs;
- unsupported blocks;
- sender/connection health;
- HTML/plaintext size;
- attachment size;
- alt-text/accessibility warnings.

A test send is clearly labelled in evidence and must not mutate real business workflow state.

## Revisions

Published revisions are immutable. A delivery/notification occurrence must be able to identify which template/layout revision rendered it.

Editing a draft does not silently change deterministic already-queued output where the Rule pinned a revision.

## Accessibility

The editor must provide:

- semantic block labels and keyboard reordering/actions;
- focus management for block add/delete/move dialogs;
- error summary linked to block/control;
- heading-hierarchy guidance;
- alt/decorative image control;
- contrast/font-size guidance without pretending all client CSS is controllable;
- no color/image-only essential meaning;
- accessible preview modes;
- no drag-only required interaction.

Output renderer should preserve meaningful headings, alt text, link/button purpose and readable plaintext fallback within email-client limitations.

## Multisite

A later normalized contract must define:

- site-local vs network templates/layouts/brands;
- inheritance/override rules;
- sender profile scope;
- core email override scope;
- network user/site locale behavior;
- import/export dependency mapping;
- who may view/test sensitive templates across sites.

The UX must display scope explicitly and never infer cross-site access from presentation alone.

## Portability

Exports include structured template schema, layout/brand/token dependencies, locale variants and revision policy, **never sender credentials**.

Import preflight reports missing providers/layouts/connections, version incompatibilities and conflicts. Generic package orchestration remains Surface 26.

## Lifecycle decision

This is a provisional interaction contract only. Because Surface 20 is still `UNSEEDED / 0`, it must be re-reviewed after Bank review and schema-valid Atomic Option Contracts. No lifecycle or runtime certification follows from this document.
