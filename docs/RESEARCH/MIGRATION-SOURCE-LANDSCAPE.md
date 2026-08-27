# WPEssential — Migration Source Landscape

Status: **Phase 0 static research — no importer implementation authorized**  
Research date: 2026-08-27.

This note records current source-system behavior relevant to future WPEssential migration adapters. Source formats and plugin internals can change; refresh this research before implementing each adapter.

## Migration principle

A migration adapter must distinguish at least four different things:

1. **Definition/configuration** — field groups, CPT definitions, taxonomies, relations, plans, rules, queries, templates.
2. **Runtime data** — post meta, custom-table/CCT rows, relation links, members/enrollments, form entries.
3. **External billing state** — remote subscriptions, transactions, gateway references.
4. **Presentation artifacts** — builder templates/listings whose structure may be proprietary and not semantically portable.

A product saying “Export” does not imply all four layers are exported.

---

# ACF / ACF PRO

Official sources:
- https://www.advancedcustomfields.com/resources/local-json/
- https://www.advancedcustomfields.com/resources/register-fields-via-php/
- https://www.advancedcustomfields.com/resources/wp-acf-json-export/

## Current facts

ACF Local JSON can save/load configuration for:
- field groups;
- post types;
- taxonomies;
- UI Options Pages.

ACF can also generate PHP registration code and now exposes WP-CLI JSON import/export/sync/status operations. Field groups and fields have stable ACF `key` values that are meaningful references inside the schema.

## Migration implications

### Strong candidates for semantic conversion
- field groups;
- field definitions;
- location rules;
- CPT definitions;
- taxonomy definitions;
- options-page definitions.

### Runtime data is separate
ACF JSON is configuration, not a portable export of all actual post/user/term field values. WPEssential must migrate the field schema and runtime values as separate steps.

### Keys
Preserve the original ACF group/field key as `source_identity` metadata even when WPEssential creates its own UUID. Repeater/flexible/clone structures can reference nested keys; discarding them during mapping would make diagnostics/migration repair difficult.

### PHP-registered fields
PHP-registered field groups may exist without editable DB records. A live-source adapter should prefer public ACF APIs/local registry rather than assuming every group exists only as a database post.

---

# Secure Custom Fields (SCF)

Official sources:
- https://wordpress.org/plugins/secure-custom-fields/
- https://developer.wordpress.org/secure-custom-fields/

## Current facts

SCF retains broad ACF-compatible concepts/APIs and currently supports:
- field groups;
- post types;
- taxonomies;
- options pages;
- JSON Schemas for supported entities/fields;
- WordPress Abilities integration;
- `wp scf json` plus backward-compatible `wp acf json` commands for JSON import/export/sync/status.

SCF installation deliberately conflicts with/deactivates ACF-family plugins with matching APIs to avoid collisions.

## Migration implications

Treat SCF as its own source adapter even where schemas resemble ACF. Do not assume eternal byte-for-byte compatibility.

Preferred future strategy:
1. detect source product/version;
2. use source JSON schema/public APIs where available;
3. normalize into WPEssential's source-neutral intermediate representation;
4. preserve source product/version/key metadata;
5. convert runtime values separately.

SCF's current JSON schemas are particularly useful for versioned validation before conversion.

---

# Meta Box / MB Builder

Official sources:
- https://docs.metabox.io/tutorials/export-import-custom-fields-meta-box-builder/
- https://docs.metabox.io/custom-fields/
- https://docs.metabox.io/custom-post-types/

## Current facts

Meta Box Builder can export/import selected field groups. Its documentation explicitly states that this exports **field settings only**, not values stored in those fields.

Meta Box has 40+ field types and separate facilities/extensions for CPT/taxonomy and other structures.

## Migration implications

### Definition migration
Map field groups/types/settings/location behavior where semantics exist.

### Data migration
Read values through supported Meta Box/WordPress storage semantics rather than assuming the Builder export contains data.

### Compatibility classification required
Some Meta Box field types/extensions may have no exact WPEssential equivalent. Such mappings must be marked `convertible`, `lossy`, or `unsupported`, never silently coerced.

---

# JetEngine

Official sources:
- https://crocoblock.com/knowledge-base/jetengine/jetengine-how-to-import-and-export-custom-post-types-terms-and-meta-fields/
- https://crocoblock.com/knowledge-base/jetengine/how-to-import-and-export-custom-content-type/
- https://crocoblock.com/knowledge-base/articles/jetplugins-staging-to-live-checklist/

## Current facts

JetEngine Skins Manager can export/import structural configuration including combinations of:
- Custom Post Types;
- taxonomies;
- meta boxes/fields;
- relations;
- options pages;
- glossaries;
- Query Builder queries;
- Listing Items/templates;
- Custom Content Type structures.

Crocoblock's staging/live guidance explicitly distinguishes these **structures** from actual content. CCT items must be moved separately; CCT structure can be exported by JetEngine, while CCT records use separate CSV/import tooling. Custom-field post data also requires a separate content migration path.

## Migration implications

JetEngine needs one of the richest source adapters because it spans several WPEssential modules.

Candidate mapping:
- JetEngine CPT → WPE CPT;
- taxonomy → WPE Taxonomy;
- meta fields/meta boxes → WPE Fields;
- relations → WPE Relations;
- Query Builder query → WPE Query AST where semantically convertible;
- Listing Item → WPE Listing only for supported constructs, otherwise retain as external-builder reference/unsupported presentation artifact;
- CCT schema → WPE Custom Table/Data Source candidate;
- CCT items → separate runtime-data import;
- options page → WPE Settings Page candidate;
- glossary → reusable option/dictionary data-source candidate.

Never promise automatic 1:1 conversion of JetEngine query/listing/builder-specific rendering constructs without semantic verification.

---

# Custom Post Type UI (CPT UI)

Official product source:
- https://wordpress.org/plugins/custom-post-type-ui/
- https://github.com/WebDevStudios/custom-post-type-ui

## Current facts

CPT UI is focused on registering/managing CPTs and taxonomies and provides Tools/export-related functionality. Current product documentation and ecosystem references support migration of its registration configuration.

## Migration implications

The future adapter should support:
- CPT registration arguments/labels;
- taxonomy registration arguments/labels;
- CPT↔taxonomy attachment;
- source ownership metadata.

Exact serialized/export format must be inspected/version-gated during the authorized adapter spike rather than hard-coded from assumptions.

Runtime posts/terms are standard WordPress content and migrate through the runtime-data/content import path, not the CPT registration-definition importer.

---

# MemberPress

Official sources:
- https://memberpress.com/docs/memberpress-memberships-and-groups/
- https://memberpress.com/docs/one-time-payment-subscriptions-or-single-transactions/
- https://memberpress.com/docs/automatically-recurring-subscriptions/

## Current facts

MemberPress distinguishes memberships from subscriptions. Migration/import paths separately model transactions and recurring subscriptions.

For one-time membership access, transaction import associates existing WordPress users/memberships with existing processor transactions. Import does not create processor transactions or initiate charges.

Recurring subscription migration requires preserving remote gateway subscription identifiers/state appropriately.

## WPEssential migration implications

Map separately:
- MemberPress Membership → WPE Membership Plan;
- Group → WPE Plan Group/upgrade graph candidate;
- Rule → WPE Access Rule where semantics map;
- transaction/access interval → Enrollment source/history candidate;
- recurring subscription → external Billing Source reference, **not** a locally invented WPE subscription;
- users remain WordPress users;
- gateway IDs remain external references.

Never synthesize a remote recurring subscription merely because historical MemberPress data says one existed.

---

# Paid Memberships Pro (PMPro)

Official sources:
- https://www.paidmembershipspro.com/documentation/add-on-docs/import-members-from-csv-add-on/
- https://www.paidmembershipspro.com/documentation/add-on-docs/import-members-from-csv-add-on/import-csv-headings/
- https://www.paidmembershipspro.com/documentation/admin/members-list/exporting-members-csv/

## Current facts

PMPro's current import tooling can:
- match/create/update WordPress users;
- assign membership levels;
- import multiple memberships per user;
- preserve membership price-related values;
- migrate subscription references/fields;
- import custom user fields;
- handle parent/child relationship data in supported add-on paths.

Exports can include member/custom user-field data.

## WPEssential migration implications

Map:
- level → Membership Plan;
- active user-level interval → Enrollment;
- multiple levels → multiple Enrollment/Plan Group semantics after conflict analysis;
- custom user fields → User Profile/Field schema + user data;
- parent/child membership relation → Team/Seat or relation candidate only after semantic confirmation;
- subscription references → Billing Source records, not local card/payment creation.

A PMPro `membership_id=0`-style cancellation instruction is an **import command semantic**, not a Plan ID to preserve literally.

---

# WooCommerce Memberships

Official sources:
- https://woocommerce.com/document/woocommerce-memberships-import-and-export/
- https://woocommerce.com/document/woocommerce-memberships-cli-reference/

## Current facts

WooCommerce Memberships has native CSV member import/export. It can create/update user memberships and can create WordPress users where configured.

Its documentation explicitly states the Memberships CSV mechanism applies to **user memberships** and does **not** create/export recurring subscription/billing state. Recurring billing is separate, commonly via WooCommerce Subscriptions.

CLI operations also expose user-membership import/list behavior.

## WPEssential migration implications

Map:
- Membership Plan → WPE Plan;
- User Membership → Enrollment;
- start/end/status → enrollment interval/state after semantic mapping;
- plan content/product access rules → WPE Access Rules where exact;
- discounts/perks → WPE Benefits where exact;
- linked Woo Subscription → separate Billing Source adapter/reference.

Do not infer recurring billing merely from membership duration or membership CSV fields.

---

# Cross-source fidelity model

Every mapped object/field receives one migration-fidelity class:

## `exact`
Semantics and relevant constraints map without behavioral loss.

## `convertible`
A deterministic transformation preserves intended behavior but changes representation.

## `lossy`
WPEssential can preserve the main intent but one or more source behaviors/settings cannot be represented exactly. User approval is required before execution.

## `external-reference`
WPEssential preserves a reference to an external/provider-owned object rather than importing ownership—for example a remote billing subscription.

## `unsupported`
Safe semantic conversion is unavailable. The importer preserves the source artifact/report where legally/technically possible and does not fabricate a replacement.

## `conflict`
The target already contains an object/identity whose mapping is ambiguous or incompatible.

No unknown mapping defaults to `exact`.

---

# Source identity preservation

For every imported definition/runtime object where possible retain import metadata such as:
- source system;
- source plugin version;
- source object type;
- source stable ID/key/slug;
- source site identifier/fingerprint where privacy policy permits;
- import batch ID;
- converter version;
- mapping/fidelity class;
- original source artifact checksum;
- target UUID/ID.

This metadata supports re-import, rollback, reconciliation and support diagnostics. It must not contain passwords/secrets/card data.

---

# Prohibited migration shortcuts

Future adapters must not:
- assume an export file includes runtime values without evidence;
- directly copy opaque serialized plugin internals into WPEssential runtime tables and call that migration;
- fabricate remote subscriptions/charges;
- overwrite existing target definitions on slug collision without a conflict strategy;
- silently flatten repeaters/flexible layouts/relations;
- convert proprietary page-builder markup into WPE templates and claim 1:1 fidelity without a supported semantic mapper;
- require the source plugin to remain active after a completed migration unless the report explicitly classifies surviving external dependencies.

---

# Research gaps before adapters are implemented

Authorized implementation research still needs:
- exact versioned CPT UI export/storage contract;
- exact Meta Box extension-specific definition formats beyond core Builder export;
- ACF/SCF field-type-by-field-type storage normalization fixtures;
- JetEngine relation/query/listing/CCT schema fixtures by supported version;
- MemberPress rules/subscription gateway mapping fixtures;
- PMPro multi-level/group/subscription fixtures;
- Woo Memberships + Woo Subscriptions paired migration fixtures;
- large/resumable migration behavior;
- multisite source/target semantics.

No adapter code or executable fixture inspection has been performed in this planning phase.