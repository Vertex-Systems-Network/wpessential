# WPEssential — Custom Fields Builder Exhaustive Field & Option Specification

Status: **Phase 0 planning only — no development consent**  
Module: **Custom Fields Builder — Pro**

This document deepens `CONTENT-MODEL-SPECS.md` and `OPTION-INVENTORY.md`. It specifies the field-schema vocabulary at option level before implementation.

## Current market/WordPress research baseline

Research verified current behavior from:

- WordPress `register_meta()` — typed metadata, subtype, label, description, default, single/multiple, sanitize/auth callbacks, REST schema, revisions;
- ACF field settings, conditional logic, Repeater and Flexible Content;
- Meta Box 40+ field types, common/advanced settings and cloneable/repeatable behavior;
- JetEngine current meta-field types, quick-edit, revision support, dynamic choice sources and conditional logic.

WPEssential does not need to copy the internal data format of any competitor. The goal is a portable typed schema that can render in post/term/user/settings/custom-table/forms contexts and be queried consistently.

---

# 1. Schema principle

A **Field Definition** has four separate concerns:

1. **Logical type** — what the value means (`string`, `integer`, entity reference, media reference, date, structured rows, etc.).
2. **Editor control** — how a human edits the value (text box, picker, map, media modal, repeater grid, etc.).
3. **Storage adapter** — where/how canonical data is persisted (post meta, user meta, term meta, options, custom table, relation/pivot, registered provider).
4. **Presentation/return format** — how consumers render/serialize the stored value.

These must not be conflated. Example: Image stores an attachment reference; returning an URL or object is a presentation decision, not a different canonical image field storage type.

---

# 2. Field Group screens

## 2.1 Groups list

Columns:

- Group name
- Key
- Status
- Locations summary
- Fields count
- Storage summary
- REST exposure summary
- Used by
- Health
- Updated
- Actions

Filters:

- Draft / Published / Disabled / Archived
- location target
- storage adapter
- REST exposed / private
- healthy / warning / degraded

Actions:

- Edit
- Duplicate
- Preview
- Publish/Disable
- Dependencies
- Revisions
- Export
- Delete definition

## 2.2 Group editor sections

1. Fields
2. Locations
3. Presentation
4. Permissions
5. Storage
6. REST & Abilities
7. Validation policy
8. Dependencies
9. Diagnostics
10. Revisions
11. Export

## 2.3 Group common options

### Identity

- title — required
- stable UUID — system-generated, immutable
- machine key — generated, user-editable until externally referenced; unique within WPEssential field groups
- description — optional
- status — Draft by default

### Presentation

- panel style: Standard / Seamless / Sectioned adapter where target supports it
- label placement: Top / Left
- instruction placement: Below label / Below input
- field order: explicit sortable order
- collapsible panel: target-dependent
- default collapsed state: false

### Location logic

Groups of AND clauses OR-ed together.

Each matcher has:

- source/target type
- operator
- operand schema
- negate support where valid
- runtime availability

Initial matcher catalog:

- post type
- post status
- page template
- page/post ID
- taxonomy term
- taxonomy
- user role
- user ID/segment
- media MIME family
- comment type/context where supported
- settings page
- custom table/entity
- frontend form/profile context
- registered extension matcher

No arbitrary PHP condition.

### Group permissions

Separate:

- view field UI
- edit value
- view value through renderer
- REST read
- REST write
- Ability read/write

Permission can use role/capability plus shared resource Policy. UI hiding is never the sole authorization boundary.

---

# 3. Common field options

Every data field inherits this contract unless the field explicitly declares an option irrelevant.

## 3.1 Identity

### label

- text
- optional for intentionally label-less layout, but recommended

### machine name/key

- required for persisted fields
- unique within group unless a documented namespace/container strategy says otherwise
- stable after external references exist
- key change is migration/reference-impacting

### stable field UUID

- always system generated
- canonical dependency/import identity
- not exposed as editable slug

### field type

- required
- changing type opens migration compatibility analysis

### description/instructions

- label description
- input instructions
- contextual help/docs link optional

### placeholder

Only for controls where semantically meaningful; never persisted as a value.

## 3.2 Required/null/empty semantics

Options:

- required: false default
- nullable: type/storage dependent
- allow empty string: explicit for string fields
- trim whitespace: default true for ordinary text/email/url; configurable when whitespace is meaningful
- default value: null/no explicit default by default

Required is server-side validation, not only HTML `required`.

## 3.3 Cardinality / repeatability

Common schema property for compatible fields:

- cardinality: Single / Multiple / Repeatable
- minimum items: 0 default
- maximum items: product-defined safety limit / explicit lower limit
- sortable: false unless multi-value order is meaningful
- unique items: optional
- empty start: type-dependent
- add item button label: generated, override optional

`Multiple` means one logical multi-value field such as multi-select/gallery/entity references.

`Repeatable` means repeated instances of a control or container with stable item IDs where ordering/diffs matter.

Do not automatically serialize all repeated values into one meta row; storage adapter chooses an explicit strategy and reports queryability.

## 3.4 Default/dynamic value

Value source:

- Static
- Context token
- Query result
- Registered resolver

Dynamic defaults run only when value is genuinely unset unless a separate overwrite rule is explicitly selected.

Context tokens are allowlisted (current user ID, current entity property, date/time, query parameter only when security policy permits, etc.).

## 3.5 Conditional visibility

- disabled by default
- AND/OR condition groups
- compare other fields in same safe context
- typed operators based on source field type
- cycle detection
- unknown/missing dependency => deterministic false or configured fallback, never PHP warning-driven behavior

Conditional visibility does **not** automatically erase a hidden existing value.

Separate optional setting: `clear_when_hidden`, default false, with data-loss warning.

## 3.6 Read-only / disabled

- read-only: display current value but prohibit edit
- disabled editor control: visual state; server policy still authoritative
- computed fields can be read-only by definition

## 3.7 Layout

- width: responsive design token/grid fraction, not arbitrary inline CSS percentage only
- new row/break before: optional
- CSS class: advanced sanitized token list, scoped target only
- DOM ID: advanced unique sanitized value where relevant

No arbitrary unsanitized `before`/`after` HTML setting in normal product mode. Use UI-only Message/HTML field with sanitization/policy instead.

## 3.8 Help/accessibility

- help text
- ARIA description generated from help/instructions
- required indicator semantics
- invalid message override
- empty-state text where picker/list needs it

## 3.9 Value visibility/privacy classification

Classification:

- Public
- Internal
- Personal data
- Sensitive
- Secret reference

This influences default REST/export/log/support-bundle behavior but does not replace explicit Policy.

## 3.10 Quick edit / admin-column integration

Option: `quick_edit_available` appears only if field type/storage adapter supports safe inline editing.

It delegates to Admin Columns/WordPress integration contract; Custom Fields does not build a duplicate list-table engine.

## 3.11 Revision support

For post meta-compatible fields:

- inherit group default
- enabled
- disabled

WordPress registered metadata revisions are used where compatible. Other storage adapters declare their own revision support.

## 3.12 REST registration

Controls:

- expose in REST: false default unless group/product preset explicitly enables
- read policy
- write policy
- schema visibility
- custom REST field name optional

When backed by WordPress registered meta, map to current `register_meta()` concepts:

- `object_type`
- `object_subtype`
- `type`
- `label`
- `description`
- `default`
- `single`
- sanitize policy
- auth policy
- `show_in_rest` schema
- `revisions_enabled` where supported

Custom callback fields are selected from registered policy/sanitizer adapters; no arbitrary PHP callback entry.

## 3.13 Abilities exposure

Fields themselves do not automatically create privileged generic mutation abilities.

Data Source/Entity abilities expose fields according to schema + policy.

## 3.14 Export

Per field:

- include definition: yes
- include values: separate Import/Export data job, not field definition export
- secret values: never exported in ordinary configuration package

---

# 4. Validation and normalization options

All validation executes server-side. Client validation mirrors it for UX.

Common rules:

- required
- min/max scalar
- min/max length
- item count
- regex/pattern with safety limits
- allowed values
- unique within current record
- unique within data source when adapter can enforce safely
- custom registered validator
- cross-field rule via Conditions/Validation Registry

Validation has:

- rule
- error message
- severity (blocking/warning only when semantics allow)
- normalization timing

Sanitization/normalization is separate from validation.

---

# 5. Text & content field types

## 5.1 Text

Canonical: string.

Specific options:

- min length
- max length
- pattern
- trim
- case normalization: none/lower/upper only when explicitly required
- autocomplete hint from approved HTML autocomplete vocabulary
- prefix/suffix presentation
- input mask adapter optional; stored canonical value must be defined separately

## 5.2 Textarea

- rows/editor height
- min/max length
- newline normalization
- plain text default
- no HTML unless field is Rich Text/HTML-safe type

## 5.3 Email

- canonical string
- WordPress/PHP-valid email validation
- optional lowercase domain normalization
- multiple addresses is cardinality, not comma-separated blob
- allow internationalized email only if chosen validation stack supports it predictably

## 5.4 URL

- allowed schemes default `http`, `https`
- relative URL allowed: false default
- protocol normalization option
- no automatic network fetch as validation

## 5.5 Telephone

- canonical string
- optional country/default-region metadata
- formatting display separated from storage
- leading `+` preserved
- no numeric storage

## 5.6 Password input

Two modes are deliberately separate:

1. **Credential/secret field** → Secrets Vault reference; reveal off by default.
2. **User password action** → never generic meta; handled by WordPress account/password APIs in Forms/Profile modules.

No reusable plain-text password meta field preset.

## 5.7 Rich Text / WYSIWYG

Options:

- editor mode: Visual+Text / Visual / Text where supported
- toolbar preset
- media upload permission
- minimum height
- auto paragraph behavior only if renderer contract requires
- allowed HTML governed by actor capability + sanitizer policy

Never grants `unfiltered_html` by UI toggle.

## 5.8 Block Content

Structured WordPress block content field for use cases where a nested block editor is appropriate.

Options:

- allowed blocks
- template
- template lock
- max content size safety limit

Canonical value uses a documented block-content representation. This is not automatically the main post editor content.

## 5.9 Code

Stored text only.

Options:

- language/highlighting mode
- line numbers
- wrap
- lint adapter
- max bytes

Never executed by Custom Fields.

## 5.10 JSON

Canonical JSON-compatible value.

Options:

- raw text editor / structured editor
- JSON Schema reference
- pretty display
- max depth/size

Validation must reject malformed JSON before save.

## 5.11 HTML / Message content field

Two distinct types:

- `Message` — UI-only sanitized rich instructions; no storage
- `HTML data` — stored markup for explicitly approved use; sanitized by capability/policy, not arbitrary script/style execution

---

# 6. Numeric field types

## 6.1 Integer

- min
- max
- step default 1
- signed/unsigned semantic
- thousands formatting display only

Canonical integer.

## 6.2 Decimal / Number

- min
- max
- step
- decimal precision policy
- rounding mode only when materializing a transformed value

Canonical numeric representation must avoid locale-formatted commas in storage.

## 6.3 Range / Slider

Same numeric constraints plus:

- visual min/max required for slider
- step
- show current value
- marks/ticks optional
- orientation only if accessible implementation supports it

## 6.4 Currency value

Logical decimal amount with optional currency source:

- fixed currency code
- companion field
- site setting

Do not store formatted symbol text as canonical amount.

## 6.5 Percentage

Logical number; display suffix `%`; min/max presets optional. Store number, not `50%` string.

## 6.6 Rating

Logical decimal/integer number with visual control.

Options:

- scale max (5/10/custom safe)
- step
- icon renderer

Accessible numeric input remains available.

---

# 7. Boolean & choice fields

## 7.1 True/False / Switch

Canonical boolean.

Options:

- on label
- off label
- default
- UI style checkbox/switch

Meaning of true must be clear in label/help text.

## 7.2 Checkbox list

Canonical list of allowed scalar values.

Options:

- source
- layout horizontal/vertical/grid
- min/max selected
- allow custom: false default
- select all UI option
- toggle all UI option
- return format affects renderer only

## 7.3 Radio

Single allowed value.

- option source
- layout
- allow null when field optional
- optional “other” value only when explicitly enabled and validated

## 7.4 Select

- single/multiple
- searchable threshold
- placeholder
- allow null
- source
- lazy search for large dynamic source
- max options fetched/rendered

## 7.5 Button Group / Segmented control

Same value semantics as radio; presentation suited to small option counts.

## 7.6 Autocomplete

- static/dynamic source
- minimum characters
- debounce UX value
- result limit
- store selected value/reference, not visible label unless that is schema
- free text allowed: false default

## 7.7 Image Choice

Choice option has:

- value
- label
- image/media reference
- alt text

Canonical selected value(s), not serialized HTML/image data.

## 7.8 Icon Choice

Choice option uses registered icon reference. Lucide can be one UI icon source; stored canonical value uses namespaced icon key.

---

# 8. Choice option sources

Every choice field uses a common Option Source contract.

## 8.1 Manual

Each row:

- value required
- label required/generated
- description optional
- disabled false
- group optional
- icon/image optional where field supports

Duplicate canonical values blocked.

## 8.2 Bulk manual

Parser supports documented formats only, with preview before replacing existing choices.

## 8.3 Query Builder

- query UUID
- value field
- label field
- optional group field
- result limit
- cache policy
- missing-record behavior

## 8.4 Taxonomy / Posts / Users / Data Source shorthand

These are convenience source builders that compile to the shared Data Source/Query contract.

## 8.5 Remote connection

Only via approved Connection/Query adapter with timeout/cache/failure states. No arbitrary unauthenticated URL fetch field hidden inside Select.

## 8.6 Allow custom option

Off by default.

If on:

- validation rule for custom value
- whether custom values are local to record or added to shared option source
- shared-source mutation requires separate capability

---

# 9. Date & time fields

## 9.1 Date

Canonical logical date `YYYY-MM-DD`.

Options:

- display format
- min/max date
- disabled weekdays/dates through typed rules
- first day of week inherits site locale by default
- dynamic default

Never timezone-shift a pure date.

## 9.2 Time

Canonical logical local time with defined seconds precision.

- 12/24-hour display
- min/max
- step minutes/seconds

## 9.3 Datetime

Canonical instant + timezone semantics.

Default strategy: normalize instant to UTC for comparison while retaining source/site timezone context where business logic needs it.

Options:

- display timezone
- entry timezone policy
- min/max
- minute/second step

## 9.4 Date Range

Canonical start/end dates.

Options:

- inclusive endpoints
- allow same day
- min/max duration
- open-ended start/end flags if use case allows

## 9.5 Datetime Range / Advanced Date

Same contract with timezone-aware instants, recurrence kept separate from ordinary field unless a dedicated recurrence schema is selected.

---

# 10. Color, icon & visual-value fields

## 10.1 Color

Options:

- formats: HEX/RGB/HSL/alpha according to approved parser
- alpha allowed
- palette presets
- custom color allowed

Canonical color object/string form must be documented; reject arbitrary CSS injection.

## 10.2 Icon

Canonical namespaced icon reference.

Sources:

- Lucide/WPE icon registry
- Dashicons where useful
- extension icon pack
- sanitized custom SVG media/reference if policy permits

No raw scriptable SVG markup stored as an unchecked icon value.

## 10.3 Background

Structured value, not CSS text blob:

- color
- image reference
- position
- size
- repeat
- attachment behavior where renderer supports

Renderer converts schema to safe CSS.

---

# 11. Media fields

Shared media constraints:

- local WordPress attachment reference canonical by default
- allowed MIME families
- max bytes cannot exceed server/provider limits
- image dimensions/aspect constraints
- min/max items
- source: Media Library / Upload / approved remote-ingest workflow
- upload capability policy
- attachment privacy/access policy

Removing a reference does **not** delete Media Library file by default.

`force delete` style behavior is not a casual field toggle; actual media deletion is destructive and may affect other records.

## 11.1 Single Image

- one attachment ID
- preview size
- crop/focal-point data optional separate structure
- min/max dimensions
- aspect ratio rule

## 11.2 Gallery

- ordered attachment IDs
- min/max images
- sortable true default
- preview size
- new item placement end default

## 11.3 Single File

- attachment ID
- allowed MIME/extensions
- max size

## 11.4 Multi-file

- ordered/list attachment refs
- max count
- sortable optional

## 11.5 Audio

File/media field constrained to audio MIME; optional duration validation after metadata extraction.

## 11.6 Video

Media reference constrained to video MIME; avoid expensive synchronous transcoding in field save path.

## 11.7 External File URL

URL field with explicit remote-resource semantics. It is not a trusted protected asset and does not become a Media Library attachment automatically.

## 11.8 oEmbed

Canonical source URL. Render/preview through WordPress oEmbed APIs with network/cache/failure protections. Stored value is source URL, not third-party iframe blob.

---

# 12. Entity/reference fields

Use canonical Entity Reference:

- source ID
- entity type/subtype
- entity ID/key

No arbitrary PHP object serialization.

## 12.1 Post/Content reference

- allowed post types
- status visibility
- query filter
- single/multiple
- search fields
- create-new shortcut only if actor can create target

## 12.2 User reference

- allowed roles/segments
- single/multiple
- search by approved user fields
- privacy-safe result display

## 12.3 Term reference

- taxonomy selector
- include/exclude terms
- hierarchy UI mode
- allow create term only with capability

## 12.4 Media reference

Can reuse Media field with source restrictions.

## 12.5 Generic Entity reference

Advanced Data Source adapter; source declares lookup/search/authorization semantics.

## 12.6 Relationship field

A convenience editor for Relations Engine links.

It does not store a second independent bidirectional relation copy in ordinary meta.

---

# 13. Location fields

## 13.1 Coordinates

Canonical:

- latitude decimal
- longitude decimal

Validation ranges enforced.

## 13.2 Address

Structured optional components:

- formatted address
- line 1/2
- locality
- region
- postal code
- country code

## 13.3 Map

Combines coordinates/address with provider-backed editor.

Provider modes:

- provider-free coordinate/manual map where available
- OpenStreetMap adapter
- Google Maps adapter
- registered provider

API keys live in Secrets/Connections, never field definition.

Provider response blob is not canonical storage.

---

# 14. Link field

Structured value:

- URL
- link text/title
- target `_self/_blank`
- rel flags (noopener/noreferrer/nofollow/sponsored/ugc according to renderer use)

URL validation applies.

---

# 15. Key/Value field

Structured list of key/value rows.

Options:

- key type plain string
- value subtype plain text/JSON only according to selected mode
- unique keys default true
- sortable
- min/max rows

Not a generic PHP array editor.

---

# 16. Group

Structured namespace with subfields.

Options:

- subfields
- layout block/table/row where appropriate
- collapsible
- repeatability/cardinality
- min/max rows if repeated
- stable row UUIDs
- title/collapsed field selector

Storage strategy declares:

- normalized custom table columns/JSON
- flattened meta namespace
- structured meta object

Serialized storage must show Query Builder limitations.

---

# 17. Repeater

Conceptually a repeatable Group optimized for row editing.

Options:

- subfields
- min rows 0
- max rows safe limit
- layout table/block/row
- collapsed summary field
- add row label
- sortable true default
- pagination/virtualization threshold for admin editor
- stable row UUID

No “unlimited” DOM rendering; blank max means platform safety ceiling, configurable at platform policy level.

Nested repeater depth has hard schema/runtime limit to prevent pathological editor/query payloads.

---

# 18. Flexible Layouts

Container of named layout schemas.

Per layout:

- stable UUID
- name/key
- label
- description
- icon/preview optional
- subfields
- min/max instances of this layout
- display mode

Container options:

- min/max total layouts
- add layout button label
- allowed layouts by condition/context
- sortable
- duplicate row permission

Switching layout type cannot silently drop incompatible stored data; user gets impact preview.

---

# 19. Table/Grid structured field

Optional structured field for truly tabular small data; not a replacement for Custom Tables Builder.

Schema:

- fixed column definitions
- row min/max
- per-column simple field types
- sortable rows optional

Large/relational datasets should use Custom Tables instead.

---

# 20. UI-only layout fields

These persist no data:

- Heading
- Divider
- Tab
- Accordion
- Endpoint/section close marker if builder model needs it
- Message
- Spacer/layout break

Each has accessibility-safe semantics.

Tabs/accordions are presentation grouping, not data hierarchy unless actual Group field is used.

---

# 21. Hidden, read-only and computed

## 21.1 Hidden

Value still server-authorized/validated. Hidden HTML input is not trusted input.

Source:

- static
- context resolver
- workflow/form-provided value only through policy

## 21.2 Read-only display

Shows value from field/data source; cannot mutate via that UI.

## 21.3 Computed

Expression from allowlisted engine.

Options:

- expression/AST
- dependencies
- output logical type
- calculate at read time / save time / background materialization
- persist false default
- cache policy

Cycles blocked.

---

# 22. Sidebar/reference-list field

A Sidebar selector can exist for WordPress compatibility use cases.

Canonical sidebar/widget-area ID; discovered choices read-only from WordPress registry. Missing sidebar => degraded warning.

---

# 23. Custom registered field type SDK contract

An extension field type declares:

- namespaced type key
- logical JSON/schema type
- editor component
- server normalizer
- validator
- sanitizer
- renderer
- storage compatibility
- query compatibility
- cardinality support
- REST schema support
- assets manifest
- migration hooks
- accessibility contract
- test fixtures

No custom field type can be “React-only”; server must understand/validate persisted data.

---

# 24. Storage adapters

## 24.1 WordPress post meta

Uses registered-meta concepts wherever useful.

Declares:

- single/multiple
- scalar/array/object type
- queryability
- revisions support
- default behavior

## 24.2 Term meta

Same typed contract subject to WordPress term-meta constraints.

## 24.3 User meta

Sensitive-field defaults stricter; generic exports/REST off by default.

## 24.4 Comment meta

Only enabled where target supports safe management UI.

## 24.5 Options

- autoload policy explicit; do not autoload large arbitrary field-group data by default
- site vs network option scope

## 24.6 Custom table

Maps logical fields to explicit columns/JSON according to Custom Tables schema.

Index/unique constraints may be first-class.

## 24.7 External provider

Provider contract owns persistence and failure semantics; WPEssential must surface unavailable/stale/degraded state.

---

# 25. Queryability classification

Every field displays one of:

- Native indexed
- Native unindexed
- Queryable with cost
- Structured/limited query
- Renderer-only/not queryable

Examples:

- scalar custom-table indexed column → Native indexed
- post meta scalar → queryable but potentially costly at scale
- serialized/flexible nested structure → limited
- Message UI field → not queryable

This classification is visible to Query Builder and Diagnostics.

---

# 26. Type/storage migration matrix

Before changing field type or storage:

1. identify records with values;
2. sample values;
3. classify transformation:
   - lossless
   - coercible with warning
   - lossy
   - incompatible
4. estimate records/time;
5. decide synchronous/background future execution;
6. define rollback snapshot;
7. update dependencies/query/listing/form mappings.

Examples:

- integer → decimal: normally lossless
- text numeric → integer: coercible only when all/selected values validate
- gallery → single image: lossy
- post meta → custom table: migration-class
- repeater schema change: row/subfield migration required

No physical migration is authorized yet.

---

# 27. Deletion behavior

Deleting field definition defaults to **retain stored values**.

Options in future destructive cleanup flow, separately authorized:

- retain orphaned values
- archive field definition
- purge values after impact preview/backup/job

Media attachments referenced by a deleted field are not deleted automatically.

---

# 28. Diagnostics

Field-level diagnostics:

- duplicate machine key
- invalid default
- conditional cycle
- computed cycle
- incompatible storage adapter
- REST schema mismatch
- missing dynamic option query
- query returns too many options
- missing referenced entity provider
- sensitive field exposed publicly
- unindexed field used by expensive filter/sort
- nested depth/row count risk
- media MIME/dimension policy conflict
- missing map provider connection
- unknown custom field type
- migration pending/failed

---

# 29. Acceptance-test inventory

Every field type must inherit common tests plus type-specific tests.

## Common

- create/edit/duplicate/delete definition
- required/optional
- null/empty/default
- unauthorized view/edit
- conditional show/hide
- hidden field tampering
- repeatability min/max/order
- import/export UUID mapping
- REST read/write denial/allow
- revision behavior
- storage adapter round-trip
- disable group retains values
- delete definition retains values
- asset scoping

## Text/value

- Unicode
- boundary length
- invalid email/URL
- locale numeric input to canonical numeric storage
- regex safety

## Choice

- duplicate option value
- missing dynamic source
- stale selected value
- large source lazy search
- malicious option label/value

## Date/time

- timezone boundary/DST
- invalid range
- date without timezone shift

## Media

- MIME spoof
- oversized upload
- dimension failure
- unauthorized media selection/upload
- shared attachment reference not force-deleted

## Entity

- target becomes unavailable
- actor can see source field but not referenced private entity
- deleted referenced entity

## Repeater/Flexible

- max rows
- nested depth
- concurrent reorder/update conflict
- schema migration with existing rows

## Computed

- dependency cycle
- invalid expression
- stale materialized value invalidation

---

# 30. Product differentiators to preserve

1. **One schema, many surfaces** — same field powers admin, Forms, Profiles, Settings, Tables, REST, Query Builder, Listings and Abilities.
2. **Logical type separated from UI/storage/return format**.
3. **Repeatability as schema capability**, not only one proprietary Repeater representation.
4. **Queryability/cost visible at design time**.
5. **Typed dynamic option sources through Query Builder**.
6. **Server-first permissions/validation**.
7. **Secrets are Vault references, never ordinary meta**.
8. **Relation fields delegate to Relations Engine**.
9. **Migration impact preview before schema changes**.
10. **Original values retained by default when definitions are removed**.

---

# 31. Open decisions before implementation

Still unresolved/blocked:

- user development consent;
- accepted compatibility/build/UI/Definition Repository/CI ADRs;
- exact WordPress registered-meta mapping for each storage adapter;
- maximum nesting/row defaults based on UX/performance evidence;
- object/array REST schema strategy;
- field value revision strategy outside post meta;
- uniqueness enforcement in post meta vs custom-table adapters;
- final HTML/rich-content sanitization policy by actor capability;
- field-level encryption beyond Secrets Vault, if any;
- map provider adapter order;
- custom field migration transaction/rollback mechanism;
- exact DataViews/form component mapping after UI evidence spike.

**Development authorization remains NO.**
