# WPEssential — Atomic Option Contracts Wave 1: Core Schema & Data

Status: **implementation-grade product planning / Wave 1**  
Snapshot: **2026-08-31**  
Applies to surfaces: **1 CPT, 2 Taxonomy, 3 Fields, 4 Relations, 6 Query, 7 Tables, 12 Settings**.

This document expands the capability-family matrix into atomic user-facing and runtime option inventories. It is subordinate to `config/product/option-contract.schema.json`: before implementation of each surface, these options must be materialized into schema-valid machine contracts and verified against current WordPress/competitor evidence.

A named feature is not complete merely because its heading exists. Sub-options, dependencies, defaults, validation, storage, migration, security, UX tier and runtime effect must be implemented and tested.

---

# Surface 1 — Custom Post Types

## 1. Identity & lifecycle

Required/primary:
- immutable Definition UUID;
- post type key;
- plural label source;
- singular label source;
- description;
- definition status: draft / published / disabled / archived;
- active runtime registration state;
- clone/duplicate definition;
- definition revisions/history;
- created/updated metadata;
- dependency count/usage summary.

Post type key contract:
- lowercase letters, numbers, `_`, `-`;
- maximum WordPress length;
- sanitize preview;
- reserved/core key detection;
- external registered key collision detection;
- WPE definition collision detection;
- immutable after creation by default;
- explicit key migration wizard;
- migration impact preview;
- reference rewrite plan;
- rollback/recovery plan;
- rewrite/REST impact preview.

## 2. Complete labels

Auto-generate all unset labels from plural/singular, then permit independent override/reset:
- `name`;
- `singular_name`;
- `add_new`;
- `add_new_item`;
- `edit_item`;
- `new_item`;
- `view_item`;
- `view_items`;
- `search_items`;
- `not_found`;
- `not_found_in_trash`;
- `parent_item_colon`;
- `all_items`;
- `archives`;
- `attributes`;
- `insert_into_item`;
- `uploaded_to_this_item`;
- `featured_image`;
- `set_featured_image`;
- `remove_featured_image`;
- `use_featured_image`;
- `menu_name`;
- `filter_items_list`;
- `filter_by_date`;
- `items_list_navigation`;
- `items_list`;
- `item_published`;
- `item_published_privately`;
- `item_reverted_to_draft`;
- `item_scheduled`;
- `item_updated`;
- `item_link`;
- `item_link_description`.

UX requirements:
- labels collapsed in Essential mode;
- `Customize labels` reveal;
- generated/default/override badge per label;
- reset one label;
- reset all labels;
- live admin-label preview.

## 3. Visibility/query options

Atomic options:
- `public`;
- `hierarchical`;
- `exclude_from_search`;
- `publicly_queryable`;
- `show_ui`;
- `show_in_menu` as true / false / parent-menu reference;
- `show_in_nav_menus`;
- `show_in_admin_bar`.

Inheritance contract:
- show inherited value separately from explicit override;
- reset override to WordPress inheritance;
- show effective value;
- disabling a parent does not silently discard dormant child overrides;
- dormant override count appears in diagnostics.

## 4. Admin presentation

Atomic options:
- `menu_position`;
- `menu_icon` mode: inherited/default / Dashicon / safe media URL / validated SVG provider / none;
- Dashicon search;
- safe SVG sanitization/provider requirement;
- parent menu selector for `show_in_menu` string mode;
- WPE UI visibility policy by capability/role/user;
- list-screen Admin Columns integration;
- Screen Options integration;
- compact/comfortable list density preference.

UI hiding MUST NOT replace authorization.

## 5. Content/editor supports

Each support is individually configurable:
- title;
- editor;
- author;
- thumbnail;
- excerpt;
- trackbacks;
- custom-fields;
- comments;
- revisions;
- page-attributes;
- post-formats.

For supports that accept structured arguments:
- enabled state;
- supported argument object;
- native compatibility validation;
- provider-defined custom support with explicit registration.

Additional editor options:
- block template;
- ordered template blocks;
- initial block attributes;
- nested template blocks;
- template lock: false / all / insert / content-only where supported;
- block-editor compatibility preview;
- classic-editor compatibility warning;
- featured-image dependency diagnostics;
- revision dependency diagnostics.

## 6. Taxonomy relationships

Atomic options/capabilities:
- show core taxonomies;
- show WPE taxonomies;
- show external taxonomies;
- searchable multi-select;
- association status: healthy / missing / external / disabled;
- read-only backlink to taxonomy-owned `object_types`;
- bulk link workflow delegated to Taxonomy owner;
- bulk unlink workflow delegated to Taxonomy owner;
- missing association preservation;
- dependency impact preview;
- no dual writer between CPT and Taxonomy definitions.

## 7. Archives, rewrite & query vars

Atomic options:
- `has_archive` disabled / default archive / custom archive slug;
- `rewrite` enabled/disabled;
- `rewrite.slug`;
- `rewrite.with_front`;
- `rewrite.feeds`;
- `rewrite.pages`;
- `rewrite.ep_mask` through safe constant mapping;
- `query_var` disabled / default / custom;
- single URL preview;
- archive URL preview;
- feed URL preview;
- paginated archive preview;
- query-var preview;
- reserved slug detection;
- rewrite collision detection;
- REST/rewrite overlap diagnostic;
- controlled rewrite flush strategy.

A save MUST NOT flush rewrites on every ordinary request.

## 8. Capabilities & permissions

Atomic options:
- standard/custom capability mode;
- `capability_type` singular;
- `capability_type` plural;
- `map_meta_cap`;
- explicit `capabilities` map;
- read_post;
- read_private_posts;
- edit_post;
- edit_posts;
- edit_others_posts;
- edit_private_posts;
- edit_published_posts;
- publish_posts;
- delete_post;
- delete_posts;
- delete_private_posts;
- delete_published_posts;
- delete_others_posts;
- create_posts where resolved;
- effective generated capability map;
- role impact preview;
- current-admin lockout check;
- capability collision/invalid-name validation;
- reset to standard WordPress capability behavior.

## 9. REST API

Atomic options:
- `show_in_rest`;
- `rest_base`;
- `rest_namespace`;
- controller mode: WordPress default / registered provider;
- `rest_controller_class` provider;
- autosave controller enabled/default/disabled/provider;
- revisions controller enabled/default/disabled/provider;
- `late_route_registration`;
- endpoint preview;
- autosave endpoint preview;
- revisions endpoint preview;
- route collision validation;
- block-editor dependency warning;
- REST permissions remain WordPress/controller authoritative.

Arbitrary class names entered as executable code are not accepted; providers are allowlisted/registered.

## 10. Lifecycle / export

Atomic options:
- `can_export`;
- `delete_with_user` tri-state;
- publish/disable/archive actions;
- definition revisions;
- revision diff;
- rollback configuration revision;
- export definition;
- import definition;
- create-only import;
- update-existing import with revision CAS;
- competitor migration adapter selection;
- migration dry-run;
- migration conflict report.

## 11. Controlled developer extensions

`register_meta_box_cb` use case:
- WordPress default;
- no callback;
- registered provider ID;
- provider compatibility status;
- provider permission requirements;
- provider health diagnostics.

Never store arbitrary PHP callback text.

## 12. WPE UX exceed requirements

- Essential / Advanced / Expert modes;
- Find Setting search by friendly label and native argument;
- sticky Save + Validate commands;
- effective `register_post_type()` args preview;
- explicit overrides-only diff;
- reset field/section/all to WordPress defaults;
- dependency graph;
- collision diagnostics;
- URL/REST previews;
- unsaved-change guard;
- keyboard/ARIA compliant tabs and validation navigation.

---

# Surface 2 — Taxonomies

## 1. Identity & associations

Atomic options:
- immutable Definition UUID;
- taxonomy key;
- plural name;
- singular name;
- description;
- definition lifecycle status;
- object type associations;
- core post types;
- WPE CPTs;
- external post types;
- preserved missing/external object-type keys;
- association health;
- safe taxonomy key migration;
- clone/duplicate definition.

Taxonomy key migration must preview term URLs, object associations, REST routes, query vars and dependent definitions.

## 2. Complete taxonomy labels

Individually overrideable:
- `name`;
- `singular_name`;
- `search_items`;
- `popular_items`;
- `all_items`;
- `parent_item`;
- `parent_item_colon`;
- `name_field_description`;
- `slug_field_description`;
- `parent_field_description`;
- `desc_field_description`;
- `edit_item`;
- `view_item`;
- `update_item`;
- `add_new_item`;
- `new_item_name`;
- `separate_items_with_commas`;
- `add_or_remove_items`;
- `choose_from_most_used`;
- `not_found`;
- `no_terms`;
- `filter_by_item`;
- `items_list_navigation`;
- `items_list`;
- `most_used`;
- `back_to_items`;
- `item_link`;
- `item_link_description`.

Generated tag-like/category-like defaults must adapt to hierarchical mode while preserving explicit overrides.

## 3. Behavior & visibility

Atomic native options:
- `public`;
- `publicly_queryable`;
- `hierarchical`;
- `show_ui`;
- `show_in_menu`;
- `show_in_nav_menus`;
- `show_tagcloud`;
- `show_in_quick_edit`;
- `show_admin_column`.

Same inherited/default/explicit/dormant semantics as CPT.

## 4. Rewrite/query

Atomic options:
- rewrite disabled/default/structured;
- slug;
- with_front;
- hierarchical rewrite;
- endpoint mask;
- query_var disabled/default/custom;
- URL preview;
- parent-term URL preview for hierarchical taxonomies;
- collision detection;
- controlled rewrite flush.

## 5. Permissions

Atomic capability options:
- `manage_terms`;
- `edit_terms`;
- `delete_terms`;
- `assign_terms`;
- effective map preview;
- role impact;
- lockout diagnostics;
- reset to WordPress defaults.

## 6. REST

Atomic options:
- `show_in_rest`;
- `rest_base`;
- `rest_namespace`;
- default REST terms controller;
- registered controller provider;
- endpoint preview;
- route collision diagnostics;
- block editor dependency warning.

## 7. Default term

Atomic options:
- default term enabled;
- name;
- slug;
- description;
- create-if-missing behavior through WordPress semantics;
- existing default-term collision/selection diagnostic.

## 8. Term ordering / default query args

Atomic options:
- `sort`;
- bounded structured `args` passed to object-term queries;
- allowlisted supported argument keys;
- invalid/expensive query argument warnings;
- term-order integration with Surface 51.

## 9. Controlled providers

For each:
- WordPress default;
- disabled where WordPress allows;
- registered provider selection;
- provider health/version compatibility;
- no raw callback text.

Providers:
- `meta_box_cb`;
- `meta_box_sanitize_cb`;
- `update_count_callback`.

## 10. UX exceed

- searchable object-type selector;
- missing/external key preservation field;
- association health panel;
- tag-like/category-like starter presets;
- effective `register_taxonomy()` args;
- inherited/default badges;
- dependency impact before disable/archive/key migration.

---

# Surface 3 — Fields / Field Groups

This is a flagship competitive surface. It must not be treated as a small meta-field helper.

## A. Field Group atomic options

Identity/lifecycle:
- immutable UUID;
- group title;
- machine key;
- draft/published/disabled/archived;
- group order/priority;
- clone/duplicate;
- revisions/history;
- revision diff/restore;
- description/documentation.

Location rules:
- OR groups;
- AND rules inside group;
- post type;
- specific post/page;
- page parent;
- page template;
- post status;
- post format;
- taxonomy;
- taxonomy term;
- attachment/media;
- comment;
- user;
- user role;
- nav menu;
- nav menu item;
- options/settings page;
- block;
- widget/legacy provider;
- custom table entity;
- relation side;
- registered custom location provider;
- negate/equality operators where location supports them;
- location simulation/test current object.

Presentation:
- position/context;
- style: standard / seamless / native supported variant;
- label placement;
- instruction placement;
- group wrapper/layout;
- hide-on-screen options individually;
- collapsed/default-open behavior where supported;
- field-group role/capability visibility;
- site/network scope;
- show in REST;
- revisions enabled;
- import/export;
- Local-JSON-equivalent sync semantics.

## B. Common options for every compatible field

Each field type must classify whether these are supported, inapplicable or specialized:
- label;
- name/key;
- immutable field UUID/key identity;
- instructions;
- required;
- allow null;
- default value;
- placeholder;
- prepend;
- append;
- wrapper width;
- wrapper class;
- wrapper id;
- conditional logic;
- validation rules;
- sanitization strategy;
- return format;
- storage format;
- REST exposure;
- revision support;
- quick edit;
- admin column;
- privacy class;
- localization/translation mode;
- view permission policy;
- edit permission policy;
- dynamic default provider;
- dynamic choice provider;
- uniqueness;
- index/query hint;
- frontend form behavior;
- import/export behavior;
- migration behavior when type/storage changes;
- usage/dependency count.

## C. Required field type registry

Basic/data:
- Text;
- Textarea;
- Number;
- Range/Slider;
- Email;
- URL;
- Password;
- Hidden;
- True/False;
- Switch;
- Checkbox;
- Checkbox List;
- Radio;
- Button Group;
- Select;
- Multi Select;
- Tags/Multi Text;
- Key/Value;
- structured JSON/Object editor.

Date/time:
- Date;
- Time;
- DateTime;
- Month/Year;
- timezone selector/provider.

Content:
- WYSIWYG;
- Code Editor;
- Message/HTML information;
- oEmbed;
- Link;
- Page Link.

Media:
- Image;
- Gallery;
- File;
- Multi-file;
- Audio;
- Video;
- generic Media.

Object/entity:
- Post Object;
- Posts Multi-select;
- Relationship;
- Taxonomy Term;
- Taxonomy selector with load/save terms semantics;
- User;
- Comment;
- Nav Menu;
- custom-table/entity selector;
- remote/query-backed selector.

Layout/composition:
- Group;
- Repeater;
- Flexible Content;
- Clone;
- Tab;
- Accordion;
- Divider/Separator;
- collapsible section/provider.

Specialized/generic:
- Map/location;
- Address autocomplete;
- Color;
- Color Alpha;
- Gradient;
- Icon selector;
- Rating;
- Phone;
- Country;
- State/Province;
- Language;
- Timezone.

## D. Text/Textarea atomic options

- default;
- placeholder;
- prepend;
- append;
- maxlength;
- minlength;
- pattern;
- rows for textarea;
- newline handling;
- character counter;
- autocomplete attribute through safe enum;
- input type variants where applicable.

## E. Number/Range atomic options

- default;
- min;
- max;
- step;
- prepend;
- append;
- decimal precision policy;
- localized display vs canonical storage;
- range orientation/provider where supported.

## F. Choice field atomic options

Shared across select/checkbox/radio/button group/multi-select where applicable:
- manual choices;
- bulk manual choices;
- value::label parsing;
- glossary/dictionary source;
- Query Builder source;
- Data Source source;
- remote Connection source;
- allow custom choice;
- save custom choice;
- default choice(s);
- single/multiple;
- minimum selections;
- maximum selections;
- vertical/horizontal layout;
- stylized/native appearance;
- searchable;
- AJAX/load on demand;
- search threshold;
- placeholder;
- allow null;
- return value / label / structured object;
- save as scalar/array where compatible;
- quick edit compatibility.

## G. Date/time atomic options

- display format;
- storage format;
- return format;
- timezone semantics: site / user / UTC / fixed;
- minimum date/time;
- maximum date/time;
- disabled weekdays;
- disabled explicit dates;
- minute step;
- 12/24-hour presentation;
- today/now shortcut;
- date-range provider mode where applicable.

## H. Media atomic options

Shared:
- library scope;
- allowed MIME types;
- min/max file size;
- min/max image width;
- min/max image height;
- min/max image dimensions;
- upload vs library selection permissions;
- return ID / URL / object / structured metadata;
- preview size;
- multiple ordering;
- required minimum/maximum items;
- image crop/focal metadata provider;
- gallery reorder;
- gallery edit captions/alt integration;
- orphan/usage diagnostics integration.

## I. Relationship selector atomic options

- target source/entity;
- allowed post types/taxonomies/roles etc.;
- query/filter definition;
- search;
- AJAX;
- minimum selection;
- maximum selection;
- ordering;
- return ID / object / structured reference;
- bidirectional synchronization mode where legitimate;
- canonical owner rules;
- load terms/save terms separately for taxonomy fields;
- create-new-related-item provider where safely supported.

## J. Repeater atomic options

- subfields;
- minimum rows;
- maximum rows;
- layout: table / block / row;
- collapsed/title field;
- add-row label;
- remove-row confirmation;
- duplicate row;
- drag sorting;
- row numbering;
- admin pagination/rows per page;
- nested repeater support;
- nesting depth guard;
- per-row conditional logic;
- per-row validation;
- frontend form behavior;
- query/storage performance diagnostics.

## K. Flexible Content atomic options

- layout collection;
- layout UUID/key;
- layout label/name;
- layout subfields;
- layout display mode;
- per-layout minimum;
- per-layout maximum;
- overall minimum layouts;
- overall maximum layouts;
- add-layout button label;
- duplicate layout row;
- reorder layouts;
- collapse layout;
- layout preview/thumbnail provider;
- conditional availability of layouts;
- nesting/depth performance guard.

## L. Clone atomic options

- clone fields;
- clone field groups;
- multiple source selection;
- display: seamless / group;
- group layout: block / table / row;
- prefix labels;
- prefix names;
- circular clone dependency detection;
- nested clone support with cycle guard;
- source-change propagation preview.

## M. Validation

Built-ins/providers:
- required;
- allow-null consistency;
- min/max numeric;
- min/max length;
- regex/pattern with catastrophic-pattern safety limits;
- email;
- URL;
- phone provider;
- unique;
- custom registered validator;
- cross-field validation;
- file MIME;
- file size;
- image dimensions;
- relation count;
- date range;
- server-authoritative validation;
- field-level error text;
- focus/error navigation.

## N. Storage targets

- post meta;
- term meta;
- user meta;
- comment meta;
- options/settings;
- custom table column;
- CCT/entity table;
- relation pivot metadata;
- module-owned entity storage.

Storage options:
- canonical data type;
- serialization mode;
- single/multiple;
- null semantics;
- default omission;
- indexing;
- unique constraint;
- revision support;
- migration preview;
- backfill strategy;
- rollback/recovery.

## O. Redux-class control registry

Controls must exist as reusable controls, not settings-only one-offs:
- Typography;
- Font Family;
- Font Source;
- Font Weight;
- Font Style;
- Font Size;
- Line Height;
- Letter Spacing;
- Word Spacing;
- Text Transform;
- Text Decoration;
- Text Align;
- Color;
- Spacing;
- Margin;
- Padding;
- Dimensions;
- Border;
- Border Radius;
- Box Shadow;
- Background color/image/repeat/position/size/attachment;
- Gradient;
- Palette;
- Image Select;
- Sortable/Sorter;
- Spinner;
- Slides;
- Button Set;
- Social Profiles;
- Icon;
- Font picker.

Generated CSS/output is allowed only via validated Theme Workspace/output bindings.

## P. WPE exceed

- one Field Schema Registry across modules;
- field control separated from storage type;
- usage/dependency search;
- schema-derived REST/Ability data contracts;
- privacy metadata;
- field migrations;
- query/index diagnostics;
- storage conversion preview;
- AI-readable schema without private values;
- option search and effective storage/runtime previews.

---

# Surface 4 — Relations

## Identity & endpoints
- immutable relation UUID;
- name;
- machine key;
- status;
- relation type: 1:1 / 1:N / N:N;
- From entity adapter;
- To entity adapter;
- post/CPT adapter;
- taxonomy term adapter;
- user adapter;
- comment adapter;
- media adapter;
- custom table/CCT adapter;
- registered custom entity adapter;
- same-type self relation;
- directional From label;
- directional To label;
- bidirectional traversal flag;
- clone relation;
- key migration workflow.

## Cardinality
- From minimum;
- From maximum;
- To minimum;
- To maximum;
- relation required flag;
- prevent duplicates;
- ordered related items;
- sortable order scope;
- max-selection UI enforcement;
- server cardinality enforcement.

## Pivot metadata
- pivot metadata enabled;
- pivot field schema;
- pivot field validation;
- pivot field permissions;
- pivot indexes;
- pivot unique constraints;
- pivot revision/history;
- pivot query exposure;
- pivot REST exposure.

## Connection/editor UX
- editor context;
- editor position;
- From-side UI;
- To-side UI;
- searchable selector;
- selection query;
- selection filters;
- result template;
- minimum/maximum selection;
- bulk connect;
- bulk disconnect;
- inverse/backlink display;
- related count display;
- frontend editing;
- form integration;
- Admin Columns integration.

## Delete/lifecycle behavior
- restrict;
- detach;
- controlled cascade;
- orphan detection;
- repair/rebuild counts;
- dependency impact preview;
- current relation usage;
- archive/disable behavior;
- migration of endpoint adapter/key;
- audit mutations.

## Permissions/API
- view relation policy;
- connect policy;
- disconnect policy;
- edit pivot policy;
- bulk-operation policy;
- REST read;
- REST mutation;
- Ability exposure;
- import/export;
- rate/bulk limits.

## Query/runtime
- relation count query;
- From→To traversal;
- To→From traversal;
- relation-aware Query AST;
- relation sorting;
- relation meta filtering;
- high-volume indexed storage;
- query/performance diagnostics.

---

# Surface 6 — Query Builder

## Query provider types
Every provider has its own option schema and declares unsupported features explicitly:
- Posts / WP_Query;
- Terms / WP_Term_Query;
- Users / WP_User_Query;
- Comments / WP_Comment_Query;
- Media;
- Custom Tables/CCT;
- Relations;
- structured/repeater data provider;
- remote REST source;
- Search engine source;
- Geo source;
- WooCommerce provider;
- advanced prepared-SQL provider.

Raw SQL is not the canonical query format.

## Definition identity
- immutable UUID;
- query name;
- machine key;
- provider/source;
- status;
- description;
- clone;
- revisions;
- permission policy;
- import/export.

## Projection
- select all/default;
- selected fields;
- aliases;
- computed fields;
- distinct;
- entity hydration mode;
- scalar projection;
- ID-only projection;
- aggregate-only projection.

## Filters
- keyword/search;
- include IDs;
- exclude IDs;
- status filters;
- type filters;
- author/user filters;
- parent/child;
- taxonomy clauses;
- meta clauses;
- date clauses;
- relation clauses;
- geo clauses;
- dynamic value/macros;
- named parameters;
- nested AND/OR groups;
- NOT groups where provider supports;
- equals/not equals;
- greater/less;
- >= / <=;
- IN / NOT IN;
- BETWEEN / NOT BETWEEN;
- LIKE / NOT LIKE;
- EXISTS / NOT EXISTS;
- regex only through providers with safety rules;
- data type casting;
- null/empty semantics.

## Ordering
- multiple order clauses;
- ASC/DESC;
- numeric/string/date casting;
- relevance order provider;
- relation count order;
- aggregate order;
- random ordering with cost warning;
- stable deterministic tie-breaker option.

## Pagination
- per page/limit;
- page;
- offset;
- total count mode;
- numbered pagination;
- cursor/keyset pagination where provider supports;
- cursor field/order requirements;
- maximum row safety limit;
- preview row limit.

## Grouping/aggregation
- group by fields;
- count;
- count distinct;
- sum;
- average;
- min;
- max;
- aggregate aliases;
- HAVING-like filters;
- registered joins;
- join type;
- join keys;
- join cardinality warning.

## Dynamic parameters
- current post;
- current user;
- queried object;
- URL/query argument;
- shortcode/block argument;
- form value;
- relation endpoint;
- date/time;
- registered token provider;
- parameter type;
- required/default value;
- validation;
- permission sensitivity.

## Cache
- enabled;
- TTL;
- cache key inputs;
- cache tags;
- invalidation events;
- per-user/per-role context;
- permission-sensitive cache prohibition;
- manual invalidate;
- cache hit/miss preview.

## Preview/diagnostics
- sample rows;
- total count;
- execution time;
- DB query count;
- memory where observable;
- cache state;
- generated WP_Query/query-object preview;
- prepared SQL preview for advanced provider;
- EXPLAIN/cost provider;
- missing index warning;
- N+1 warning;
- full scan warning;
- large OFFSET warning;
- random order warning;
- performance class.

## Publication/integration
- Listings;
- Admin Columns;
- choice fields;
- Relations selectors;
- Forms;
- REST API Builder;
- dashboards;
- analytics;
- AI approved read abilities;
- import/export;
- dependency graph for referenced fields/relations/tables.

---

# Surface 7 — Custom Tables / Content Tables

## Identity/scope
- immutable schema UUID;
- entity display name;
- table identifier;
- DB prefix strategy;
- site/network/global scope;
- primary key mode: auto increment / UUID / safe custom;
- timestamps created/updated;
- soft delete;
- optimistic revision/version column;
- description;
- status;
- clone schema.

## Column builder
Each column:
- immutable column UUID;
- DB column name;
- display label;
- data type;
- unsigned where supported;
- length;
- precision;
- scale;
- nullable;
- default;
- auto increment eligibility;
- generated/computed provider;
- collation/charset policy where supported;
- field/control binding;
- relation reference;
- privacy class;
- validation;
- REST exposure;
- Admin Columns integration.

Required SQL/data types, compatibility gated by DB:
- tiny/small/medium/int/big integer;
- boolean;
- decimal;
- float/double where justified;
- varchar;
- text/mediumtext/longtext;
- date;
- time;
- datetime;
- timestamp;
- JSON where supported;
- binary/blob only Expert/provider use.

## Indexes/constraints
- primary key;
- unique index;
- normal index;
- composite index;
- prefix length where DB requires;
- fulltext index where available;
- index order;
- generated index name;
- custom safe index name;
- foreign key optional/provider gated;
- uniqueness collision preview;
- index-size compatibility check;
- query evidence / usage rationale;
- redundant-index warning.

## CRUD/admin
- admin CRUD enabled;
- menu parent;
- capability;
- list columns;
- search fields;
- filters;
- default sort;
- pagination;
- create form;
- edit form;
- delete confirmation;
- soft-delete restore;
- bulk edit;
- bulk delete;
- import/export;
- row-level Policy;
- audit.

## Integrations
- Field Schema Registry;
- Query Builder;
- Listings;
- Forms;
- Relations;
- Admin Columns;
- REST/Abilities;
- analytics;
- import/export;
- user ownership field/provider.

## Schema migration
Every schema mutation has a diff:
- add column;
- rename column;
- change type;
- change length;
- change precision/scale;
- nullable change;
- default change;
- add/drop index;
- add/drop unique;
- add/drop foreign key where enabled;
- drop column;
- rename table;
- scope change.

Migration controls:
- row count/size estimate;
- affected rows;
- compatibility check;
- online/chunked strategy where possible;
- maintenance-mode requirement detection;
- backup/recovery requirement;
- destructive confirmation;
- dependency graph;
- migration job progress;
- checkpoint/resume;
- rollback if safe;
- forward-fix/restore plan when rollback is not safe.

## Performance
- row count;
- table size;
- index size;
- slow query diagnostics;
- unindexed filter warning;
- pagination strategy;
- max bulk operation size;
- chunk sizing;
- retention/archive provider.

---

# Surface 12 — Settings / Options Pages

## Page identity/navigation
- immutable UUID;
- page title;
- menu title;
- slug;
- top-level/submenu;
- parent menu;
- icon;
- position;
- required capability;
- site/network/user scope;
- status;
- clone/duplicate;
- description/help.

## Page structure
- tabs;
- sections;
- panels;
- subsections;
- columns/layout;
- section icons;
- conditional page visibility;
- conditional tab visibility;
- conditional section visibility;
- role/capability visibility;
- sticky action bar;
- reset controls;
- import/export controls;
- revisions/history.

## Storage
- option namespace/name;
- individual option vs grouped storage;
- autoload strategy;
- site option vs network option vs user meta;
- default values;
- inheritance;
- environment-specific value classification;
- secret values prohibited from ordinary settings storage and redirected to Vault;
- revision support;
- reset field;
- reset section;
- reset page;
- migration/versioning.

## Control registry
Settings pages consume the Fields/Control Registry, including:
- Text;
- Textarea;
- WYSIWYG;
- Code;
- Checkbox;
- Radio;
- Select;
- Multi-select;
- Button Group;
- Media;
- Gallery;
- File;
- Repeater;
- Group;
- Flexible Content;
- Color;
- Color Alpha;
- Gradient;
- Typography;
- Font;
- Spacing;
- Margin;
- Padding;
- Dimensions;
- Border;
- Border Radius;
- Box Shadow;
- Background;
- Palette;
- Sortable;
- Image Select;
- Spinner;
- Slides;
- Social Profiles;
- Icon;
- Date;
- Time;
- Link;
- Entity select;
- registered custom controls.

## Typography atomic controls
- source: system / uploaded / registered provider;
- family;
- fallback stack;
- weight;
- style;
- size;
- size unit;
- line height;
- line-height unit;
- letter spacing;
- word spacing;
- text transform;
- decoration;
- alignment;
- color;
- responsive values;
- preview text;
- inherit/reset.

## Background atomic controls
- color;
- image/media;
- gradient;
- repeat;
- position;
- size;
- attachment;
- overlay;
- responsive source where supported;
- focal position;
- preview.

## Border/shadow atomic controls
- border style per/all side;
- border width per/all side;
- border color;
- radius per/all corner;
- shadow X;
- shadow Y;
- blur;
- spread;
- shadow color;
- inset;
- multiple shadows only if output provider supports.

## Dependencies/validation
- show when;
- hide when;
- enable when;
- require when;
- nested AND/OR;
- typed comparisons;
- server authoritative validation;
- field sanitizer;
- registered validator;
- validation error navigation.

## Output/developer integration
- get-value API;
- token/value resolver;
- typed schema API;
- REST exposure;
- Ability exposure;
- Site Editor/Customizer adapter;
- Theme Workspace output binding;
- CSS variable binding;
- CSS property binding through validated provider;
- no arbitrary unsanitized CSS/PHP execution;
- effective value preview;
- explicit override diff;
- versioned export/import.

---

# Shared Wave 1 UX contract

All seven surfaces must use the same builder grammar:

## List screens
- title + Add New;
- search;
- status filters;
- pagination;
- configurable columns;
- bulk actions where safe;
- row actions;
- relationship/dependency counts;
- validation health;
- Screen Options;
- compact/comfortable density;
- empty state;
- sortable headers only when sorting is backend-correct.

## Editors
- clear page title/status;
- sticky Save/Validate command area;
- Essential default view;
- Advanced configuration toggle;
- Expert section;
- logical settings tabs;
- Find Setting search;
- contextual help;
- native argument name discoverable;
- inherited/default/override badges;
- reset control;
- dirty state;
- unsaved navigation warning;
- validation issue summary;
- focus/jump to invalid option;
- dependency diagnostics;
- effective runtime preview;
- no giant all-options-at-once form.

## Validation/security
- UI dependencies never replace server validation;
- capabilities/policies always server authoritative;
- executable callback/class providers are registered/allowlisted;
- raw arbitrary PHP is prohibited;
- collisions are validated at save/publish;
- destructive/migration operations require impact preview and explicit flow;
- imports use preflight/conflict reporting/revision safety.

## Accessibility
- complete keyboard operation;
- semantic labels;
- correct tab/accordion ARIA;
- visible focus;
- status not color-only;
- validation announcements;
- no inaccessible custom selects;
- packaged browser + axe evidence before runtime certification.

---

# Wave 1 planning completion rules

A Wave 1 surface can advance from this inventory to `OPTION_CONTRACT_COMPLETE` only when:

1. each item above exists in a schema-valid machine option contract;
2. each WordPress-native option is checked against current core API/source;
3. each parity feature has current official competitor evidence;
4. each option has UX tier/group/control;
5. defaults/inheritance are explicit;
6. dependencies are explicit;
7. validation/sanitization is explicit;
8. storage owner/mode is explicit;
9. mutation/migration class is explicit;
10. security capability/policy is explicit;
11. import/export and multisite semantics are classified;
12. required tests are enumerated;
13. `missing = 0` and `unclassified = 0`;
14. UX contract is reviewed separately before implementation;
15. runtime implementation proves round-trip behavior before `RUNTIME_CERTIFIED`;
16. competitor test scenarios prove the intended parity before `PRODUCT_PARITY_CERTIFIED`.

Until those gates are met, this document is an implementation-grade inventory, not a claim that the runtime product already has these features.
