# Custom Fields: competitor documentation and source audit — 2026-11-20

Scope: ACF, Secure Custom Fields (SCF), Meta Box, JetEngine and Redux Framework documentation, plus the
source of the wordpress.org packages of SCF, ACF, Meta Box, Redux, Pods, CMB2 and ACPT Lite (JetEngine is
closed source; its knowledge base was used). Compared with the Surface 3 Options Bank (618 records before).

## Field types found in source

| Plugin | Types (source directory) |
|---|---|
| SCF / ACF | 36 (`includes/fields/class-acf-field-*.php`); SCF adds nav-menu, flexible content, repeater, clone, gallery in the free package |
| Meta Box | 51 (`inc/fields/*.php`, incl. base classes input, choice, object-choice, multiple-values, input-list) |
| Redux | 34 (`redux-core/inc/fields/*`) |
| Pods | 24 (`classes/fields/*`) |
| CMB2 | 38 (`CMB2_Types`) |
| JetEngine | 20 (meta field types overview) |
| ACPT Lite | 5 |

Every user-facing type maps to an existing Bank record except: Redux `sorter` and `link_color`, Meta Box
`select_tree`, CMB2 `select_timezone` and hierarchical taxonomy displays.

## Capabilities checked

Covered already: conditional logic, repeater / group / flexible content / clone, location rules for posts,
terms, users, comments, attachments, menu items and widgets, blocks and block bindings, REST, Local JSON,
PHP registration, bidirectional relationships, custom tables, Abilities, schema.org, quick edit, revisions,
choice sources (query, glossary), bulk options, MIME limits, character limit, field width. Options pages are
owned by Settings Pages (Surface 12), not Fields.

Native WordPress: `register_meta()` (single, default, sanitize/auth callbacks, REST schema, revisions,
object subtype), term / user / comment meta, block bindings and quick edit were covered; protected meta keys
(`is_protected_meta()`) were not.

## Added (19 records, `fields--competitor-docs-source-audit-2026-11.json`)

Types: sorter, link colour, select tree, timezone select, hierarchical taxonomy display. Placement:
WooCommerce product data, variations, product categories. Front-end: post form, profile form (runtime owned by
Forms & Workflows and User Profiles). Multilingual: translation mode, wpml-config.xml generation (exceed),
Polylang. Developer: JS API. Settings framework: CSS output from values; Customizer panel (deferred). Admin:
bulk edit. Native: protected meta keys. Rejected: front-end field writes without nonce and capability.

Fields now has 637 reviewed records; the option contract projects 631 atomic options (546 parity, 79 exceeds,
2 deferred, 4 rejected).
