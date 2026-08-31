# WPEssential — Emails Builder Exhaustive Option Specification

Status: **Phase 0 exhaustive product specification / no implementation authorized**

## 1. Product boundary

Email Builder creates **email-safe transactional/event templates**.

It is not a general webpage builder and does not promise that arbitrary Elementor/WPBakery/Visual Composer/Gutenberg frontend markup will render correctly across email clients.

Notification System owns recipient/event delivery policy. Email Builder owns:
- email template structure;
- email-safe rendering;
- branding/layouts;
- subject/preheader/plaintext variants;
- email-specific dynamic tokens/conditional content;
- preview/test-send;
- WordPress email event overrides where supported.

---

# 2. Screens

- Templates
- Create/Edit Template
- Layouts / Headers & Footers
- WordPress Email Overrides
- Sender Profiles shortcut
- Test / Preview
- Delivery Logs shortcut to Notification/Email delivery data
- Settings
- Diagnostics

---

# 3. Templates list

Columns:
- Name
- Key
- Status
- Purpose/category
- Linked events/rules count
- Base layout
- Locale variants
- Updated
- Revision
- Health
- Actions

Filters:
- Draft/Published/Archived
- WordPress default override/custom
- category
- locale
- linked/unlinked
- needs attention

Actions:
- Edit
- Preview
- Test send
- Duplicate
- Publish/Unpublish
- Usage
- Revisions
- Export
- Archive/Delete

---

# 4. Create template — identity

Fields:
- Name required
- Key stable generated
- Internal description
- Category
- Status Draft default
- Base Layout: None / selected reusable layout
- Default locale
- Additional locale variants
- Linked Notification/Event optional quick-link

Templates can exist independently from a Notification Rule.

---

# 5. Email metadata

Per locale/template:
- Subject — required for send-ready template
- Preheader — optional
- From profile — inherit Notification/sender profile or explicit allowed profile
- Reply-To profile/address policy
- email purpose/category label
- HTML enabled — default yes
- Plaintext enabled — default yes/auto-generated with editable override

CC/BCC are Notification/send-policy concerns, not hidden template-level recipients by default.

---

# 6. Email canvas structure

Approved email blocks:
- Section/container
- Columns
- Text
- Heading
- Button
- Image
- Divider
- Spacer
- List
- Data/key-value table
- Repeater/list of records with limits
- Logo/brand block
- Social links
- Footer/legal/preferences block
- Dynamic token/text
- Conditional block
- HTML-safe registered custom email block — SDK controlled, not arbitrary untrusted HTML by default

No iframe, video autoplay, browser form, JS, script, canvas or arbitrary frontend widget.

---

# 7. Section/container options

- content width token/pixels within safe min/max
- full-width outer background
- inner background
- padding top/right/bottom/left
- border width/style/color limited email-safe subset
- border radius with compatibility warning
- vertical alignment for columns
- stack columns on mobile
- hide on desktop/mobile only if renderer/client compatibility is documented; avoid hidden critical content

---

# 8. Text block

Options:
- rich email-safe text editor
- font family from safe/brand stack
- size
- line height
- weight
- alignment
- text color
- link color
- paragraph spacing
- dynamic tokens
- conditional inline content where supported

Sanitization:
- allowlisted semantic tags
- no scripts/event handlers/forms
- URL protocol validation

---

# 9. Heading block

- level semantic H1–H6 where renderer can preserve
- text/token
- font settings
- alignment
- margin/padding
- color

Template should normally contain coherent heading hierarchy; accessibility warning for skipped/duplicated H1 patterns, not hard block where email content semantics differ.

---

# 10. Button block

- label/token
- URL/action target
- button alignment
- width auto/full/fixed safe bounds
- background/text/border
- radius compatibility warning
- padding
- fallback link text candidate for plaintext

Target URL:
- validated absolute/local URL
- trusted dynamic URL provider
- signed single-purpose link provider for account actions where module owns it

Never expose secrets/session tokens directly through generic token editor.

---

# 11. Image block

Source:
- media attachment
- trusted absolute URL
- dynamic media token

Options:
- alt text required or decorative toggle
- width/max width
- alignment
- link
- height auto default
- image rendition/size

Rules:
- no base64 giant image by default
- no private/protected media URL unless recipient authorization/delivery design actually permits email exposure
- remote tracking implications documented for externally hosted images.

---

# 12. Columns

Options:
- 1–4 columns candidate safe maximum
- ratios presets
- gutters
- mobile stack order
- vertical alignment
- column-specific background/padding

Renderer must generate email-compatible structure; CSS Grid/Flexbox cannot be assumed across clients.

---

# 13. Divider/spacer

Divider:
- thickness
- style limited safe subset
- color
- width
- alignment
- vertical spacing

Spacer:
- height safe bounds

Use spacing controls instead of empty text blocks.

---

# 14. Data table / key-value block

Modes:
- key/value rows
- tabular rows from Query/event collection

Options:
- columns/labels
- field/token mapping
- max rows
- sort inherited from data source
- empty behavior
- header show/hide
- borders/padding/type styles
- mobile fallback behavior

Do not render unbounded query results into email.

---

# 15. Repeater/list block

Source:
- event collection
- Query Builder bounded results
- registered data provider

Options:
- item template blocks
- max item count
- truncation message/link
- separator
- empty state

Query executes under delivery principal/policy and performance budget.

---

# 16. Conditional block

Uses Condition Engine.

Conditions can reference:
- recipient safe profile fields
- event data
- entity fields under policy
- Membership safe state/entitlement labels
- Query result
- locale

No raw PHP/template language.

Email rendering must remain deterministic from chosen template revision + approved context.

---

# 17. Dynamic tokens

Token browser groups:
- Site
- Recipient
- Event
- Entity
- Form Entry safe fields
- Membership safe fields
- Query
- Date/time
- Custom registered provider

Each token defines:
- type
- formatters allowed
- privacy classification
- HTML/text escaping mode
- nullable fallback

Never expose:
- password hashes
- application passwords
- OAuth/API tokens
- secrets
- raw protected user meta
- full arbitrary object dumps

---

# 18. Token formatting

Safe formatters:
- text
- uppercase/lowercase/title case locale-aware candidate
- number
- currency with explicit currency
- date/time/timezone
- URL
- list join
- boolean label
- truncate
- fallback/default

No arbitrary PHP callback typed by admin.

---

# 19. Global template styles

- outer background
- content background
- content width
- default font stack
- base font size/line height
- text color
- heading styles H1–H6
- link color/decoration
- button default style
- divider default
- border/radius tokens
- spacing scale

Styles are compiled/inlined/embedded according to accepted email renderer; CSS support matrix determines what controls are available.

---

# 20. Reusable Layouts

Layout can include:
- outer shell
- header
- body slot
- footer

Layout fields:
- Name
- Key
- status
- brand logo
- colors/type defaults
- header blocks
- footer blocks
- legal/company address tokens
- preference/unsubscribe block where applicable

Template can:
- follow current published layout revision — default candidate
- pin layout revision for deterministic compliance-sensitive template if explicitly chosen

Changing layout shows affected templates.

---

# 21. Branding presets

Candidate organization:
- Brand name
- logo light/dark variants only if real email-client strategy supports
- default colors
- company/contact/address fields
- social links
- website

Do not fetch arbitrary remote brand HTML/CSS.

---

# 22. Plaintext variant

Modes:
- auto-generate from email-safe structure
- custom plaintext override

Plaintext preview required.

Auto generation rules:
- headings become text
- buttons become label + URL
- images use alt/link where relevant
- remove purely decorative blocks
- tables/lists become readable lines
- no raw HTML remnants

---

# 23. Responsive/mobile behavior

Controls only where email renderer can support them:
- column stacking
- mobile padding
- mobile text size limited overrides
- button full width
- image fluid width

Do not promise pixel-identical rendering across all clients.

Preview dimensions are approximate developer previews, not proof of Gmail/Outlook rendering.

---

# 24. Dark mode

Email-client dark mode behavior is inconsistent.

Product options may include:
- default light design
- dark-mode-aware safe color hints where supported
- alternate logo only when renderer/testing supports

Do not promise exact brand color preservation in every client.

---

# 25. Accessibility

- meaningful subject
- plaintext alternative
- semantic text hierarchy where possible
- alt text/decorative image control
- readable contrast guidance
- link/button purpose
- font size warnings
- no essential info conveyed only by image/color
- tables marked/layouted appropriately by renderer

Email-client limitations are documented.

---

# 26. WordPress default email overrides

Screen inventories supported WordPress/core email events through an adapter registry rather than fragile mail-content string matching.

Potential events:
- new user/admin notification
- password reset
- email change
- comment moderation/notification
- site/admin events where WordPress exposes stable hooks/filters

Per event:
- current status Default/WPE Override
- recipients ownership shown
- template selection
- subject selection/override
- enable/disable override
- test context
- restore default

If a core event cannot be safely/fully overridden through stable APIs, mark Partial/Unsupported rather than intercept every `wp_mail()` globally.

---

# 27. Third-party email overrides

Only certified adapters.

Examples may later include WooCommerce transactional emails.

Adapter certification defines:
- supported plugin versions
- event data schema
- recipient ownership
- template override scope
- fallback to original plugin email

No generic “replace every email from plugin X” interception without semantic adapter.

---

# 28. Sender profiles

Owned by Connections/Email transport integration.

Template selects profile only when allowed.

Profile safe display:
- From name
- From email/domain
- Reply-To
- provider/transport
- verified/health state

Credentials never shown.

DMARC/SPF/DKIM status may be displayed only if provider/domain diagnostics can actually verify it; do not infer delivery authentication from configured From address.

---

# 29. Attachments

Email rule/template can declare attachment slots, but actual file resolution is Notification/action context.

Options:
- static approved media/file
- generated document reference
- event/form upload only with policy
- max count/total size
- MIME restrictions
- protected/private file exposure warning

Default preference for large/private files: secure expiring download link rather than attaching sensitive giant file.

---

# 30. Tracking

Tracking is privacy-sensitive and **off by default candidate** unless site owner explicitly enables through delivery provider/product policy.

Potential options:
- open tracking
- click tracking
- provider analytics

Requirements:
- disclosure/privacy policy
- per-category setting
- no hidden WPE telemetry
- provider capability detection
- do not claim accuracy due image blocking/privacy proxies.

---

# 31. Unsubscribe/preferences block

For optional subscription categories:
- unsubscribe current category
- manage preferences

Tokens/links are single-purpose, signed/expiring or durable preference tokens according to security design.

Required transactional/security messages are classified separately and must not use “optional” unsubscribe behavior incorrectly.

WPE does not make jurisdiction-specific marketing-law compliance claims automatically.

---

# 32. Test send

Fields:
- test recipient
- template locale
- sample context source
- sender profile
- include tracking off/on per test

Preflight:
- missing tokens
- invalid URLs
- unsupported blocks
- sender connection unhealthy
- estimated size
- missing plaintext
- alt text warnings

Test email is marked as test in log; test should not mutate real business workflow state.

---

# 33. Preview

Modes:
- Desktop width
- Mobile width
- Plaintext
- Source/generated HTML developer view where capability permits
- token/context inspector

Optional external screenshot/email-client testing provider can be future adapter; not core guarantee.

---

# 34. Email size diagnostics

Warnings:
- HTML size
- total inline image/attachment size
- excessive DOM/table nesting
- too many query/repeater items

Large-email thresholds need evidence; avoid fake universal deliverability score.

---

# 35. Revision behavior

Published Template/Layout revisions immutable.

Notification instance/delivery should know which template/layout revision rendered it.

Editing draft does not mutate already queued deterministic notification if rule policy pins triggered revision.

---

# 36. Import/export

Exports:
- template schema
- referenced layout UUID/revision policy
- brand dependencies
- token dependencies
- locale variants
- no sender credentials

Import:
- semantic preview
- missing token providers
- missing layouts/connections
- conflict mapping
- draft/deferred if dependency missing

---

# 37. Delivery logging boundary

Email Builder records template/render metadata; Notification/transport layer records delivery attempts.

Do not claim inbox delivery unless provider returns meaningful delivery event.

Safe detail:
- template/revision
- subject maybe privacy-classified
- recipient masked
- provider state
- timestamps
- normalized error

Do not store full rendered sensitive email body forever by default.

---

# 38. Settings

- default layout
- default brand
- default sender profile
- default plaintext auto-generation
- default tracking off/on — off candidate
- max query/repeater rows
- max attachment bytes
- template revision retention
- test recipient default optional current admin
- HTML sanitizer/render strictness
- image proxy/hosting adapter future
- log retention delegated to Notification/transport settings

---

# 39. Permissions

- read templates
- create/update/delete
- publish
- manage layouts/branding
- override WordPress email events
- view source/generated HTML developer capability
- test send
- export/import
- manage sender profiles delegated to Connections

Templates containing sensitive tokens may require additional resource policy.

---

# 40. Abilities

- template list/get/create/update/validate/publish/archive
- preview/render with authorized sample context
- test-send
- layout list/get/create/update/publish
- override list/configure/restore-default

AI exposure default:
- read/explain/preview
- draft template generation opt-in
- publish/test-send/override mutation not exposed by default.

---

# 41. Events

- template/layout published/updated/archived
- override enabled/disabled
- render failed
- test send requested/result

Actual email delivery events owned by Notification/transport.

---

# 42. Error/degraded states

- missing layout
- missing token provider
- missing sender connection
- unsupported event adapter
- render validation error
- provider transport unhealthy
- imported template uses unsupported block version
- Pro expired → editing read-only, already deployed transactional behavior follows safe runtime policy

---

# 43. Performance/assets

Performance:
- compile/cache published template representation by revision
- no Query N+1
- bounded repeater data
- render server-side without frontend builder runtime
- async large sends through Notification/Job Service

Assets:
- email editor assets only Email screens
- no frontend CSS/JS for email templates
- WordPress editor/media packages only when used

---

# 44. Future tests

After consent:
- token escaping/XSS
- missing sensitive token denied
- responsive column output
- plaintext generation
- malformed imported block
- WordPress override fallback
- third-party adapter missing
- sender From injection/header safety
- attachment authorization
- tracking preference
- unsubscribe token security
- deterministic revision rendering
- query row limits
- email HTML sanitization
- test send vs real workflow isolation
- delivery status truthfulness
- Pro/dependency degradation

## Maturity

Emails Builder is now **Exhaustive option spec** at Phase 0 product level. Exact email renderer/CSS inliner/dependency and provider compatibility require later technical research/evidence under the development consent gate.