# WPEssential — 56-Surface Competitor-Parity Capability Matrix

Status: **product re-baseline / planning contract**  
Date: 2026-08-31  
Base architecture: 56 canonical surfaces in `config/architecture/surfaces.json`.

This document raises the product target from architectural ownership to **market-credible product depth**. The named competitors are benchmarks, not dependencies or visual-cloning targets.

## Research baseline

The current benchmark review includes official/current documentation from:
- JetEngine/Crocoblock: CPTs, meta fields, Query Builder, Relations, Listings, Maps, REST, Options Pages, Profile Builder and Data Stores;
- ACF PRO: field groups, Repeater, Flexible Content, Clone, Gallery, Options Pages, Blocks, Local JSON and bidirectional fields;
- Secure Custom Fields: CPT/taxonomy/field-group and field-type behavior;
- Meta Box AIO: 50+ fields, CPT/taxonomy, relationships, custom tables, settings pages, profiles, frontend submission, views, blocks, REST, revisions and admin columns;
- CPT UI/CPTUI Extended: post types/taxonomies, network-wide registration, import/export, extension hooks and display helpers;
- Redux Framework: broad options/control types, typography, spacing, backgrounds, media, repeaters and generated output;
- specialist products including Admin Columns Pro, SearchWP, Paid Memberships Pro, WP All Import/Export, UpdraftPlus, Wordfence, WP STAGING, Redirection, WP Crontrol, WPCode and WP-Optimize.

The matrix is a **minimum planning floor**, not a ceiling.

---

# Suite A — Content Schema

## Surface 1 — Custom Post Types (`cpt`)

**Primary benchmarks:** JetEngine, Meta Box, SCF/ACF, CPT UI, Pods, Toolset.

### Required option families

**Identity**
- immutable Definition UUID;
- post type key with WordPress length/character/reserved-key validation;
- plural name, singular name, description;
- active/draft/disabled/archive lifecycle;
- duplicate/clone definition;
- safe key migration workflow rather than casual destructive rename.

**Complete labels customization**
- name;
- singular_name;
- add_new;
- add_new_item;
- edit_item;
- new_item;
- view_item;
- view_items;
- search_items;
- not_found;
- not_found_in_trash;
- parent_item_colon;
- all_items;
- archives;
- attributes;
- insert_into_item;
- uploaded_to_this_item;
- featured_image;
- set_featured_image;
- remove_featured_image;
- use_featured_image;
- menu_name;
- filter_items_list;
- filter_by_date;
- items_list_navigation;
- items_list;
- item_published;
- item_published_privately;
- item_reverted_to_draft;
- item_scheduled;
- item_updated;
- item_link;
- item_link_description;
- generated labels from plural/singular with per-label override/reset.

**Visibility/query**
- public;
- hierarchical;
- exclude_from_search;
- publicly_queryable;
- show_ui;
- show_in_menu boolean or controlled parent menu;
- show_in_nav_menus;
- show_in_admin_bar;
- inheritance/default visualization.

**Admin presentation**
- menu_position;
- menu_icon: Dashicon, media/icon provider, validated SVG where safe;
- menu parent selection;
- admin visibility by capability/role where WPE policy permits;
- list-screen integration with Admin Columns surface.

**Editor/content capabilities**
- supports: title, editor, author, thumbnail, excerpt, trackbacks, custom-fields, comments, revisions, page-attributes, post-formats;
- supported structured `supports` arguments where WordPress allows them;
- block template;
- template lock;
- editor/block compatibility preview;
- featured-image behavior;
- revisions integration.

**Taxonomy relationships**
- associate core/external/WPE taxonomies;
- read-only backlink from taxonomy-owned associations where ownership requires it;
- missing/degraded association diagnostics;
- bulk association tools;
- impact preview before unlinking.

**URLs/query variables**
- has_archive bool/custom slug;
- rewrite enabled/disabled;
- rewrite slug;
- with_front;
- feeds;
- pages;
- endpoint mask through safe enum/constant mapping;
- query_var bool/custom name;
- before/after URL preview;
- reserved/rewrite collision diagnostics;
- controlled rewrite flush lifecycle.

**Capabilities**
- capability_type singular/plural;
- map_meta_cap;
- complete supported capabilities map;
- standard vs custom capability mode;
- effective capability preview;
- lockout diagnostics;
- import/export of capability config.

**REST API**
- show_in_rest;
- rest_base;
- rest_namespace;
- rest_controller_class via allowlisted/provider mechanism;
- autosave REST controller provider;
- revisions REST controller provider;
- late_route_registration;
- effective endpoint preview;
- REST collision diagnostics.

**Lifecycle/export**
- can_export;
- delete_with_user;
- activation/deactivation runtime behavior;
- Definition revisions;
- import/export/local-file portability;
- migration from CPT UI/ACF/Meta Box/JetEngine formats where feasible.

**Controlled extension point**
- register_meta_box_cb through registered provider, never arbitrary executable text.

### UX parity/exceed
- Essential mode: plural, singular, key, public, hierarchical, common supports, taxonomies, active;
- Advanced tabs: Labels, Visibility, Admin UI, Content & Editor, Relationships, URLs, Permissions, REST;
- Expert: provider/controller/migration details;
- setting search by friendly label and native arg;
- effective `register_post_type()` args preview;
- validate-before-save;
- explicit inherited/default badges;
- dependency impact graph before delete/rename.

---

## Surface 2 — Taxonomies (`taxonomy`)

**Primary benchmarks:** JetEngine, Meta Box, SCF/ACF, CPT UI, Pods, Toolset.

### Required options

**Identity/association**
- taxonomy key;
- plural/singular names;
- description;
- object type associations across core/external/WPE CPTs;
- association preservation for temporarily missing object types;
- immutable-key migration workflow.

**Complete labels**
- name;
- singular_name;
- search_items;
- popular_items;
- all_items;
- parent_item;
- parent_item_colon;
- name_field_description;
- slug_field_description;
- parent_field_description;
- desc_field_description;
- edit_item;
- view_item;
- update_item;
- add_new_item;
- new_item_name;
- separate_items_with_commas;
- add_or_remove_items;
- choose_from_most_used;
- not_found;
- no_terms;
- filter_by_item;
- items_list_navigation;
- items_list;
- most_used;
- back_to_items;
- item_link;
- item_link_description;
- generated defaults + individual overrides.

**Behavior/visibility**
- public;
- publicly_queryable;
- hierarchical;
- show_ui;
- show_in_menu;
- show_in_nav_menus;
- show_tagcloud;
- show_in_quick_edit;
- show_admin_column.

**URLs/query**
- rewrite bool/structured;
- slug;
- with_front;
- hierarchical rewrite;
- endpoint mask where applicable;
- query_var bool/custom;
- URL preview/collision diagnostics.

**Permissions**
- manage_terms;
- edit_terms;
- delete_terms;
- assign_terms;
- effective capability preview;
- lockout validation.

**REST**
- show_in_rest;
- rest_base;
- rest_namespace;
- controller provider/allowlist;
- route preview.

**Term/default/query behavior**
- default_term name/slug/description;
- sort;
- bounded declarative args where WordPress supports them;
- term ordering integrations;
- term meta/field-group relationships.

**Controlled providers**
- meta_box_cb;
- meta_box_sanitize_cb;
- update_count_callback;
- registered provider only.

### UX exceed
- known object types shown as searchable checklist/token selector;
- external keys preserved separately;
- association health panel;
- effective `register_taxonomy()` args preview;
- default/inherited controls;
- labels auto-generation;
- dependency impact before disable/delete.

---

## Surface 3 — Fields / Field Groups (`fields`)

**Primary benchmarks:** ACF PRO, SCF, Meta Box AIO, JetEngine, Pods, Toolset, Redux for settings/control breadth.

This surface must not ship as a small “text/select/image” field set. It is one of the core competitive pillars.

### Field-group options
- title, immutable UUID, machine key;
- active/draft/disabled;
- menu/order priority;
- location-rule groups with AND/OR semantics;
- locations: post type, page, page template, post status, post format, taxonomy/term, user/user role, comment, attachment/media, options/settings page, nav menu/menu item, block, widget/legacy surfaces where supported, custom table/CCT, relation side, registered provider locations;
- group display position/context;
- style: standard/seamless/native variants;
- label placement;
- instruction placement;
- wrapper width/layout;
- hide-on-screen controls;
- description/documentation;
- show in REST/API;
- revision support;
- import/export/local JSON equivalent;
- clone/duplicate group;
- field-group dependency graph;
- permission/role visibility;
- multisite scope.

### Common field options
Every compatible field type should classify:
- label;
- name/key;
- instructions;
- required/nullability;
- default value;
- placeholder;
- wrapper width/class/id;
- conditional logic;
- validation rules;
- sanitization;
- return format;
- storage format;
- REST exposure;
- revision support;
- quick edit;
- admin column integration;
- privacy classification;
- translation/localization behavior;
- capability visibility/editability;
- dynamic default/value provider;
- uniqueness/index hint where meaningful;
- frontend display/form behavior;
- import/export behavior.

### Basic/data field types
- text;
- textarea;
- number;
- range/slider;
- email;
- URL;
- password;
- hidden;
- true/false;
- switch/toggle;
- checkbox;
- checkbox list;
- radio;
- button group;
- select;
- multi-select;
- multi-text/tag input;
- key/value;
- JSON/object editor in safe structured form.

### Date/time fields
- date;
- time;
- date-time;
- month/year where useful;
- timezone semantics;
- storage/display/return format separation;
- min/max/disabled-date constraints.

### Content/editor fields
- WYSIWYG/editor;
- code editor with language/mode and no automatic execution;
- HTML/message/info;
- oEmbed;
- link;
- page link;
- shortcode/token preview where safe.

### Media fields
- image;
- gallery;
- file;
- files/multi-file;
- audio;
- video;
- generic media;
- image crop/focal metadata where provider supports it;
- library restriction;
- MIME/size/dimension constraints;
- return ID/URL/object/structured metadata.

### Object/relationship selectors
- post object;
- posts/multi-post;
- relationship;
- taxonomy term;
- taxonomy selector with load/save term relationship behavior;
- user;
- comment;
- nav menu;
- registered custom-table/entity source;
- remote/query-backed select through Data Source/Query provider.

### Layout/composition
- group;
- repeater;
- flexible content/layout builder;
- clone/reuse field/group;
- tab;
- accordion;
- separator/divider;
- collapsible groups;
- nested repeaters/groups subject to depth/performance guard;
- repeater min/max, collapsed state, title field, row/table/block layouts, sorting.

### Geo/specialized
- map/location;
- address autocomplete;
- color;
- color alpha;
- gradient;
- icon selector;
- star/rating where semantically generic;
- phone with validation strategy;
- country/state/language/timezone data providers.

### Redux-class settings controls shared through control registry
- typography: family, source, weight/style, size, line-height, letter spacing, word spacing, text transform, alignment, decoration, color, preview, units;
- spacing/margin/padding;
- dimensions;
- border;
- border radius;
- box shadow;
- background color/image/position/repeat/size/attachment;
- gradient;
- palette;
- image select;
- sortable/sorter;
- spinner;
- slides/repeatable media rows;
- button set;
- social profile rows;
- CSS output binding only through validated theme/control providers.

### Choice/source options
- manual choices;
- bulk manual input;
- glossary/dictionary;
- query result;
- taxonomy/post/user/data-source result;
- remote connection through approved provider;
- allow/save custom values where type permits;
- AJAX/load-on-demand;
- search threshold;
- multiple/min/max choices;
- layout/appearance;
- return value/label/object.

### Validation options
- min/max numeric;
- min/max length;
- regex/pattern with safety limits;
- email/URL;
- unique;
- required;
- custom registered validator;
- cross-field condition;
- file MIME/size/dimensions;
- relational count;
- server authoritative validation.

### Storage targets
- post meta;
- term meta;
- user meta;
- comment meta;
- options/settings;
- custom table column;
- custom content type table;
- relation pivot metadata;
- module-owned entity storage.

### WPE exceed
- one portable Field Schema Registry across modules;
- field control != storage type separation;
- schema-derived REST/Ability contracts;
- native revision support where viable;
- indexed/custom-table storage choice;
- dependency graph;
- field usage search;
- migration preview when changing storage/type;
- AI-readable schema without exposing private values.

---

## Surface 4 — Relations (`relations`)

**Primary benchmarks:** JetEngine Relations, Meta Box Relationships, ACF bidirectional relationships, Pods/Toolset relationships.

### Required options
- relation name/key/UUID;
- one-to-one, one-to-many, many-to-many;
- post/CPT, term, user, comment, media, custom-table/CCT and registered entity adapters;
- same-type reciprocal relations;
- directional from/to labels;
- optional bidirectional traversal;
- min/max cardinality per side;
- required relationship constraints;
- ordered related items;
- relation metadata/pivot fields using Field Schema Registry;
- connection UI context/position/field settings;
- selection query/filter/search;
- prevent duplicate links;
- bulk connect/disconnect;
- delete behavior: restrict, detach, controlled cascade;
- orphan diagnostics;
- relation counts;
- inverse/backlink display;
- query integration;
- listings/dynamic value integration;
- forms/frontend editing;
- admin columns;
- REST/Ability exposure;
- import/export;
- indexes and high-volume relation storage;
- permissions per relation and side;
- audit/history where configured.

### WPE exceed
- first-class relation table/index strategy rather than serialized meta;
- impact analysis before deletion;
- typed pivot schema;
- relation-aware Query AST;
- no ambiguous double-writer association model.

---

## Surface 5 — Status (`status`)

**Primary benchmarks:** PublishPress custom statuses, JetEngine status workflows, WordPress native post statuses.

### Required options
- custom status key/name;
- complete labels;
- public/private/protected/internal classification;
- show in admin all/status lists;
- post count behavior;
- icon/color presentation;
- allowed post types;
- default status per context where safe;
- allowed transitions from/to statuses;
- transition capability requirements;
- role/user restrictions;
- bulk/quick edit exposure;
- scheduling/future behavior;
- notifications/workflow triggers;
- REST exposure;
- query integration;
- import/export;
- audit trail;
- status retirement/migration impact.

---

# Suite B — Data Intelligence

## Surface 6 — Query Builder (`query`)

**Primary benchmarks:** JetEngine Query Builder, Toolset Views, Meta Box Views/query integrations, WP Grid Builder/FacetWP query integrations.

### Query types/providers
- posts/WP_Query;
- terms/WP_Term_Query;
- users/WP_User_Query;
- comments/WP_Comment_Query;
- media;
- WooCommerce products/orders via optional adapter;
- custom tables/CCT;
- relations;
- repeater/structured field data where safe;
- SQL advanced provider;
- REST/remote source;
- search engine source;
- geo query source.

### Query options
- selected fields/projection;
- source/entity;
- status/type filters;
- keyword/search;
- post/page IDs include/exclude;
- author/user filters;
- parent/child filters;
- taxonomy clauses nested AND/OR;
- meta clauses nested AND/OR;
- date clauses;
- relation clauses;
- dynamic values/macros/context parameters;
- compare operators including IN/NOT IN/BETWEEN/LIKE/EXISTS as provider supports;
- type casting;
- multiple sort clauses;
- random with explicit cost warning;
- offset/page/per-page;
- keyset/cursor pagination where provider supports it;
- distinct;
- grouping;
- aggregates/count/sum/avg/min/max;
- HAVING-like aggregate filters;
- joins through registered adapters;
- computed fields;
- query parameters/placeholders;
- fallback/empty behavior;
- cache TTL/tags/invalidation;
- result count;
- preview rows;
- execution time/query count;
- explain/cost diagnostics where possible;
- max-row safety limits;
- REST endpoint publication through Surface 22;
- import/export;
- permissions.

### WPE exceed
- canonical safe Query AST;
- prepared SQL provider instead of raw concatenation;
- portable query definitions across listings/columns/forms/API;
- performance-class warnings;
- dependency tracking for referenced fields/relations.

---

## Surface 7 — Custom Tables / Content Tables (`tables`)

**Primary benchmarks:** Meta Box Custom Table, JetEngine Custom Content Types, Pods Advanced Content Types, WP Data Access-class table builders.

### Required options
- table/entity name and DB-safe identifier;
- site/network/global scope;
- primary key strategy;
- auto increment/UUID options;
- column builder using Field Schema Registry;
- SQL types: integer variants, decimal, float, bool, varchar/text/longtext, date/time/datetime/timestamp, JSON where supported, binary/blob only where justified;
- length/precision/scale;
- nullable/default;
- unique indexes;
- normal/composite indexes;
- full-text index where DB capability permits;
- generated/computed column provider only when compatible;
- timestamps created/updated;
- soft delete;
- optimistic revision/version column;
- relation references;
- optional foreign keys only after hosting compatibility checks;
- admin CRUD screen;
- forms integration;
- listing/query integration;
- REST/Ability CRUD;
- bulk edit/export/import;
- migration diff/preview;
- add/drop/rename column strategy;
- chunked data migration;
- schema rollback/recovery;
- retention/uninstall policy;
- row-level Policy;
- audit;
- large-table pagination and indexes.

### WPE exceed
- migration engine and schema diff instead of opaque table creation;
- query/index evidence;
- portable schema Definitions;
- dependency graph before destructive schema changes.

---

## Surface 8 — Admin Columns (`columns`)

**Primary benchmark:** Admin Columns Pro; also Meta Box Admin Columns and JetEngine columns.

### Required options
- screen/list table target: posts/CPTs, users, media, comments, terms, custom entities;
- multiple named column sets/views;
- assign column set by user/role;
- column label/key/order/width;
- source: native property, meta/field, taxonomy, relation, user, media, query aggregate, computed value, custom provider;
- display format: text, number, date, image, link, boolean, status, taxonomy chips, relation list/count, progress/rating where generic;
- sortable when backend can sort before pagination;
- smart filters by data type;
- saved filters;
- inline edit;
- bulk edit;
- quick edit integration;
- export CSV/structured format;
- conditional visibility;
- role/capability permissions;
- custom empty/fallback display;
- search integration;
- per-user preference;
- clone column set;
- import/export;
- Woo/ACF/Meta Box/JetEngine-style adapter architecture;
- no N+1 rendering;
- batched hydration and query budget diagnostics.

### WPE exceed
- explicit backend truth for sort/filter;
- batch-read capability contract;
- performance class surfaced before publishing an expensive column.

---

## Surface 9 — Listings (`listings`)

**Primary benchmarks:** JetEngine Listings, Meta Box Views, Toolset Views, CPTUI Extended layouts, builder dynamic-list systems.

### Required options
- source: query definition, entity/data source, relation, REST endpoint;
- display modes: grid, list, table, cards, compact rows, map, calendar/timeline adapters;
- template/blueprint definition;
- dynamic field/text/image/link/media/taxonomy/relation output;
- repeater/flexible content rendering;
- dynamic URL/actions;
- conditional visibility;
- fallback/empty state;
- loading/skeleton state;
- pagination numbered/prev-next;
- load more;
- infinite scroll;
- cursor pagination provider;
- sorting controls;
- filter integration;
- search integration;
- result count;
- responsive columns/gaps/alignment;
- accessibility semantics;
- builder adapters: Gutenberg, Elementor, Bricks priority;
- server-rendered fallback/shortcode/block;
- caching with permission/context-aware keys;
- lazy media;
- SEO/schema structured-data provider;
- query performance diagnostics;
- import/export/clone.

---

## Surface 33 — Analytics (`analytics`)

**Primary benchmarks:** independent WordPress analytics/reporting products, form/search/plugin analytics, GA4-compatible integrations.

### Required options
- first-party event registry;
- page/content views;
- listing impressions/clicks;
- search terms and zero-result tracking;
- form starts/submissions/errors;
- workflow outcomes;
- membership/signup/conversion events via adapters;
- booking events;
- custom events/properties;
- goals/conversions;
- funnels;
- cohorts/retention where viable;
- dimensions/segments;
- UTM/referrer attribution;
- date ranges/comparison;
- dashboards/widgets;
- scheduled reports;
- export;
- retention controls;
- consent/privacy mode;
- anonymization;
- sampling/aggregation for scale;
- external analytics connections;
- role permissions;
- multisite rollups with explicit scope.

---

## Surface 34 — Search (`search`)

**Primary benchmarks:** SearchWP, Relevanssi, FacetWP/WP Grid Builder search/filter behavior.

### Required options
- multiple search engines;
- selectable sources/post types/entities;
- source attributes: title/content/excerpt/slug/custom fields/taxonomies/comments/media metadata/custom table fields;
- per-attribute relevance weights;
- include/exclude rules;
- stemming;
- fuzzy/partial matching;
- phrase handling;
- synonyms;
- stopwords;
- keyword minimums;
- exact-match boosts;
- custom ranking rules;
- search forms;
- AJAX/live search;
- result templates/listings;
- highlights/excerpts;
- filters/facets integration;
- indexing queue/status;
- incremental index updates;
- document/PDF extraction via optional adapter;
- multilingual adapter;
- WooCommerce adapter;
- query logs;
- popular/no-result analytics;
- privacy/retention;
- REST/API search;
- performance diagnostics.

---

## Surface 35 — Decision / Rules (`decision`)

**Primary benchmarks:** JetEngine Dynamic Visibility/conditions, form conditional logic, automation rule engines.

### Required options
- reusable named decision definitions;
- typed input variables;
- AND/OR/nested condition groups;
- comparisons across string/number/date/bool/list/entity values;
- existence/empty operators;
- membership/role/capability predicates;
- field/meta predicates;
- query/relation predicates through bounded providers;
- time/date/schedule predicates;
- geo predicates where provider exists;
- branches/outcomes;
- scoring/weighted rules;
- priorities;
- fallback/default outcome;
- simulation/test cases;
- versioning;
- audit of evaluations for sensitive workflows where enabled;
- reusable in forms, visibility, menus, workflows, notifications, placements and memberships;
- no arbitrary PHP condition.

---

## Surface 36 — Ledger / Activity Audit (`ledger`)

**Primary benchmarks:** WP Activity Log, Simple History, security/audit logs.

### Required options
- actor/principal;
- action/event;
- object/resource;
- before/after structured diff with redaction;
- timestamp/site/network;
- correlation ID;
- request/channel/source;
- severity/category;
- search/filter;
- object/user drilldown;
- export;
- retention;
- archive/legal-hold provider where required;
- privacy/redaction;
- alert rules;
- tamper-evidence/hash chaining optional advanced mode;
- multisite aggregation;
- REST/CLI access under capability;
- no secrets in logs.

---

## Surface 42 — Geo (`geo`)

**Primary benchmarks:** JetEngine Maps, GeoDirectory, Meta Box Geolocation, ACF map fields.

### Required options
- provider abstraction: Google/Mapbox/OpenStreetMap-compatible adapters;
- API key through Secrets Vault;
- geocoding/reverse geocoding;
- address autocomplete;
- latitude/longitude fields;
- canonical address components;
- map picker;
- marker icon/data;
- clustering;
- radius filters;
- distance sorting;
- bounding box;
- polygon/geofence provider;
- user/current-location input with consent;
- dynamic map listings;
- query integration;
- relation/listing/filter integration;
- caching/geocode quotas;
- fallback provider behavior;
- privacy precision controls.

---

## Surface 54 — User Stores (`user-stores`)

**Primary benchmark:** JetEngine Data Stores; favorites/bookmark/compare/recently-viewed plugins.

### Required options
- store type: favorites, bookmarks, likes, dislikes, compare, recently viewed, custom named store;
- supported object types: posts/CPTs, terms, users, custom entities where safe;
- user vs guest storage;
- guest cookie/session migration to account;
- unique/multiple entries;
- max items;
- ordering and timestamps;
- counts/aggregate popularity;
- privacy visibility;
- frontend add/remove/toggle button blueprint;
- login-required behavior;
- list/query source;
- notifications/actions on change;
- REST/Ability API;
- import/export for user-owned data subject to privacy policy;
- cleanup/retention;
- anti-abuse/rate limits for public interactions.

---

# Suite C — Experience & Presentation

## Surface 10 — Dashboard Widgets (`dashboard-widgets`)

**Benchmarks:** Ultimate Dashboard, WP Adminify, White Label CMS, native WP dashboard.

### Required options
- widget title/id;
- dashboard target/site/network;
- content type: rich text, stats, chart, listing/query, recent activity, quick links, form/action, health status, custom provider;
- position/context/priority;
- width/layout;
- role/capability/user visibility;
- conditional visibility;
- refresh interval/manual refresh;
- cached query data;
- dismissible/non-dismissible;
- empty/error states;
- links/actions through Abilities;
- drag/order preferences where supported;
- clone/import/export;
- default WordPress widget hide/show management through admin-theme/menu boundaries.

---

## Surface 11 — Admin Menu (`admin-menu`)

**Benchmarks:** Admin Menu Editor Pro, WP Adminify, Ultimate Dashboard.

### Required options
- rename menu/submenu;
- icon;
- order/priority;
- parent reassignment;
- hide/show by role/capability/user;
- conditional visibility;
- custom menu item linking internal admin route/external URL with safety rules;
- separators/groups;
- duplicate conflict detection;
- original menu restore;
- per-role menu profiles;
- multisite/network menu profiles;
- admin-bar item management where owned;
- import/export;
- conflict-safe transformation after all plugins register menus;
- no removal of actual capabilities when merely hiding UI.

---

## Surface 12 — Settings / Options Pages (`settings`)

**Primary benchmarks:** Redux, ACF Options Pages, Meta Box Settings Page, JetEngine Options Pages, Kirki-class settings frameworks.

### Page architecture
- page title/menu title/slug;
- top-level/submenu parent;
- icon/position;
- capability;
- site/network/user scope;
- tabs/sections/panels/subsections;
- page layout/columns;
- save/reset/import/export;
- revisions/history optional;
- REST exposure;
- autoload strategy;
- customizer/site-editor adapter where relevant;
- clone/duplicate pages;
- conditional page/section visibility.

### Field/control breadth
Must consume the full Field/Control Registry including Redux-class controls:
- text, textarea, editor, code;
- choice controls;
- media/gallery;
- repeater/group/flexible structures;
- typography;
- spacing/dimensions;
- background;
- border/radius/shadow;
- gradients/colors/palettes;
- sortable/image select;
- social profiles;
- icon/font controls;
- date/time;
- links/entities;
- registered custom controls.

### Output/developer options
- get value API/token resolver;
- generated CSS only through validated output mappings;
- field dependencies;
- validation/sanitization;
- defaults/inheritance;
- reset field/section/page;
- versioned export/local config.

---

## Surface 13 — Frontend Dashboards (`dashboard`)

**Benchmarks:** JetEngine Profile Builder, Meta Box frontend dashboard, membership/profile plugins.

### Required options
- dashboard definition/routes/endpoints;
- navigation groups/items/icons;
- role/membership/capability visibility;
- account overview;
- profile edit;
- user-created content list/add/edit/delete;
- custom table/CCT item management;
- favorites/data stores;
- bookings/orders/subscriptions via adapters;
- notifications/messages;
- forms;
- query/listing widgets;
- tab/page templates;
- pagination/search/filter;
- empty states;
- public vs private profile routes;
- login/register/forgot-password integration;
- redirects after login/logout/action;
- frontend-only restriction from wp-admin where configured;
- responsive/accessibility;
- route collision/security validation.

---

## Surface 14 — User Profiles (`profiles`)

**Benchmarks:** JetEngine Profile Builder, Meta Box User Profile, Ultimate Member, Profile Builder.

### Required options
- registration forms;
- login/logout;
- password reset/change;
- email/account verification providers;
- profile edit;
- avatar/profile image;
- cover image optional;
- custom user fields;
- field visibility/privacy;
- role assignment with strict allowlist;
- default role;
- moderation/approval;
- account status;
- public profile templates;
- user directory/listing;
- search/filter users;
- social/profile links;
- account deletion/export privacy hooks;
- admin approval;
- redirect rules;
- spam/abuse integration;
- REST/API policy;
- multisite user behavior.

---

## Surface 16 — Builder Widgets / Dynamic Components (`builder-widgets`)

**Benchmarks:** JetEngine dynamic widgets/blocks/elements, ACF Blocks, Meta Box Blocks, dynamic-content add-ons.

### Required options
- neutral Component Blueprint;
- controls schema;
- dynamic data bindings;
- query/listing binding;
- field/relation/user/term/options data;
- dynamic visibility conditions;
- link/action binding;
- style controls;
- responsive controls;
- state/empty/error variants;
- repeater/list rendering;
- accessibility metadata;
- caching;
- asset dependencies;
- server render/client render classification;
- Gutenberg block adapter;
- shortcode fallback;
- Elementor adapter;
- Bricks adapter;
- Divi/WPBakery/other adapters by demand;
- import/export of blueprint;
- no builder-specific business rule fork.

---

## Surface 28 — Media (`media`)

**Benchmarks:** Real Media Library/HappyFiles, Enable Media Replace, Media Cleaner, ACF/Meta Box media fields.

### Required options
- media folders/collections without breaking attachment IDs/URLs;
- taxonomy-like organization;
- search/filter/sort;
- attachment metadata editor;
- replace file safely;
- preserve URL vs new URL modes;
- regenerate metadata/thumbnails;
- usage/reference detection;
- orphan/unused diagnostics with conservative false-positive policy;
- duplicate detection by checksum/name/dimensions;
- image focal point/crop metadata;
- MIME/file restrictions;
- custom attachment fields through Fields surface;
- bulk actions;
- offload/CDN adapter boundaries;
- image format/optimization adapter boundaries;
- import/export of organization metadata;
- privacy/security for private media provider;
- builder integration.

---

## Surface 38 — Placement (`placement`)

**Benchmarks:** theme hook/block placement systems, WPCode auto-insert locations, dynamic visibility products.

### Required options
- content/component/snippet/listing source;
- insertion target: header/footer/body, before/after content, before/after post, theme hook, block/template region, shortcode/block placement, registered selector/provider;
- hook name/provider;
- priority;
- before/after/replace modes where safe;
- page/post type/taxonomy/archive/search/404 conditions;
- user/role/membership conditions;
- device/responsive conditions where presentation-only;
- date/time schedule;
- query/string/referrer conditions where safe;
- repeat limits;
- conflict/duplicate detection;
- preview;
- builder-specific placement adapters;
- cache awareness;
- audit/versioning;
- no arbitrary DOM monkey patch as default.

---

## Surface 39 — Experiments (`experiments`)

**Benchmarks:** Nelio A/B Testing, Thrive Optimize-class experimentation.

### Required options
- experiment name/type/status;
- variants;
- traffic allocation;
- target/placement;
- audience segment conditions;
- start/end schedule;
- goals/conversion events;
- primary/secondary metrics;
- sample size/minimum duration guard;
- statistical method configuration with sane locked defaults;
- preview/test traffic exclusion;
- admin/user exclusion;
- cookie/user assignment persistence;
- cross-device identity only where privacy permits;
- results dashboard;
- confidence/credible interval display;
- automatic/manual winner;
- rollback;
- experiment history;
- analytics integration;
- privacy/consent.

---

## Surface 49 — Admin Theme (`admin-theme`)

**Benchmarks:** WP Adminify, Ultimate Dashboard, White Label CMS.

### Required options
- brand/logo/favicon;
- login page styling;
- admin colors;
- typography;
- spacing/density;
- menu/top-bar styling;
- dashboard branding;
- footer/version text;
- hide/rename selected native branding elements where safe;
- dark/light/system theme;
- per-role/user theme profiles;
- custom login background/form settings;
- responsive behavior;
- accessibility contrast checks;
- custom CSS only through Safe Script/validated style provider;
- presets/import/export;
- reset to WordPress default.

---

## Surface 53 — Fonts (`fonts`)

**Benchmarks:** custom-font/self-hosted Google font plugins, Redux typography, page-builder custom fonts.

### Required options
- font family definitions;
- upload WOFF/WOFF2 and permitted formats;
- multiple weights/styles;
- variable font axes metadata;
- local/system font stacks;
- Google-font catalog/provider;
- self-host/download Google fonts with privacy mode;
- Adobe/remote provider through Connections;
- subsets/languages;
- font-display;
- preload selected files;
- fallback stack;
- CSS family name validation;
- duplicate/unused font diagnostics;
- usage references from Theme Workspace/settings/components;
- import/export metadata excluding licensed binaries where prohibited;
- performance warning for excessive variants.

---

## Surface 56 — Theme Workspace (`theme-workspace`)

**Benchmarks:** Redux/Kirki theme option frameworks, WordPress Site Editor/global styles, builder global settings.

### Required options
- design tokens;
- global color palette;
- gradients;
- typography scale/families;
- spacing scale;
- container widths;
- breakpoints where theme/provider supports them;
- buttons/forms/link styles;
- body/headings/content styles;
- layout/sidebar defaults;
- header/footer template/provider bindings;
- template part/global style integration;
- theme mods/options adapter;
- Customizer compatibility provider;
- Site Editor/global styles provider;
- responsive preview;
- CSS variable output;
- validated selector/output mapping;
- presets/style variations;
- child-theme-safe behavior;
- import/export/version control;
- rollback/history;
- builder adapters;
- safe custom CSS delegated to Safe Script;
- performance/font dependency diagnostics.

---

# Suite D — Identity & Access

## Surface 15 — Membership (`membership`)

**Benchmarks:** MemberPress, Paid Memberships Pro, Ultimate Member membership extensions.

### Required options
- membership level/plan definitions;
- level groups and multiple concurrent memberships;
- free/one-time/recurring pricing;
- initial payment;
- billing amount/cycle;
- trial period/pricing;
- expiration;
- signup enabled/hidden plans;
- upgrade/downgrade/change rules;
- proration provider;
- grace periods;
- content restriction by page/post/CPT/taxonomy/category/tag/term/URL/block/shortcode/component;
- excerpt/archive behavior;
- drip/delay access;
- per-user overrides;
- coupons: code/start/end/usage/per-user/plan applicability/price override;
- checkout fields;
- taxes/VAT provider;
- payment gateway adapters;
- subscription state sync;
- orders/transactions/refunds;
- member account/billing/cancel/confirmation pages;
- member admin management;
- bulk import/export;
- reminder emails;
- cancellation/expiration notifications;
- reports: revenue, signups, cancellations, active members, visits/login where tracked;
- capability/role mapping;
- REST/API;
- audit;
- multisite policy.

### WPE exceed
- same shared Conditions/Policy engine for restrictions;
- dependency impact when deleting a plan;
- one Data Source/Query layer for member dashboards/reports;
- portable plan/rule definitions while secrets/payment tokens remain external.

---

## Surface 27 — Protector (`protector`)

**Benchmarks:** content-control/private-site/password-protection products; membership rules.

### Required options
- whole-site private mode;
- per-content protection rules;
- path/URL pattern rules;
- password protection groups;
- login-required rules;
- role/capability/membership access;
- IP allow/deny provider;
- schedule/time-window access;
- REST/feed/search/archive handling;
- protected-content message/template;
- redirect vs 401/403/404 response;
- admin/editor bypass rules;
- crawler/robots behavior;
- cache compatibility;
- brute-force protection for shared passwords;
- access attempt audit;
- preview/test-as-user capability;
- no security-by-menu-hiding.

---

## Surface 30 — Roles (`roles`)

**Benchmarks:** User Role Editor, Members, PublishPress Capabilities.

### Required options
- create/clone/rename/delete roles;
- capability matrix grouped by core/module/plugin source;
- grant/revoke;
- custom capabilities;
- user assignment/bulk assignment;
- role hierarchy/presentation only without inventing unsafe privilege inheritance;
- per-site vs network roles;
- restore WordPress defaults;
- compare roles/diff;
- detect orphan capabilities;
- module capability registration;
- role templates/presets;
- content-type capability integration;
- import/export;
- audit;
- prevent current-admin lockout;
- super-admin boundaries.

---

# Suite E — Automation & Communication

## Surface 17 — Forms & Workflows (`forms-workflows`)

**Benchmarks:** Gravity Forms, Fluent Forms, Formidable, JetFormBuilder, automation products.

### Form builder
- all relevant Field Registry controls;
- layout columns/sections/pages;
- multi-step forms;
- progress indicator;
- repeaters/groups;
- conditional fields/pages;
- calculations/formulas;
- dynamic defaults;
- prefill from entity/user/query;
- create/update posts/CPTs/users/terms/custom-table rows;
- relation connect/update;
- file/media uploads;
- save draft/partial submission;
- confirmation message/page/redirect;
- AJAX submission;
- spam honeypot/rate limit/CAPTCHA provider;
- nonce/CSRF protection;
- logged-in/guest restrictions;
- scheduling/open-close/max entries;
- signatures provider;
- payment fields/providers;
- entry storage/retention;
- entry search/filter/export;
- edit/resubmit;
- frontend management;
- REST/API.

### Actions/workflows
- send email;
- notification;
- create/update/delete entity under Policy;
- webhook/API request;
- connection/provider action;
- relation update;
- user/role/membership action;
- payment/subscription action;
- redirect;
- scheduled/delayed action;
- conditional branch;
- loop over bounded collection;
- approvals/manual tasks;
- retries/backoff;
- idempotency;
- workflow run log;
- resume/checkpoints;
- failure path;
- secrets redaction.

---

## Surface 18 — Cron / Schedules (`cron`)

**Benchmark:** WP Crontrol, Advanced Cron Manager, Action Scheduler operational UIs.

### Required options
- inspect existing WP-Cron events;
- next run/recurrence/hook/arguments;
- add/edit/delete WPE-owned schedules;
- custom intervals;
- one-time schedules;
- recurring schedules;
- run-now;
- missed-event diagnostics;
- timezone correctness diagnostics;
- event ownership/source;
- lock/concurrency info for WPE jobs;
- Action Scheduler queue view when used;
- retries/backoff;
- pause/disable;
- system-cron/CLI runner guidance/config state;
- logs/history;
- capability restrictions;
- no false promise of exact WP-Cron wall-clock timing.

---

## Surface 19 — Notifications (`notifications`)

**Benchmarks:** Better Notifications for WP, automation notification systems, membership/form notifications.

### Required options
- triggers from typed Events;
- conditions;
- recipient rules: user, role, email, relation, field, admin, dynamic query;
- channels: in-app, email, webhook, browser/push adapter, SMS/messaging provider;
- subject/title/body;
- token/dynamic values;
- HTML/plain variants;
- localization;
- priority;
- immediate/delayed/scheduled/digest;
- throttling/deduplication;
- retries;
- delivery state/log;
- user notification preferences;
- read/unread in-app state;
- retention;
- unsubscribe/consent where applicable;
- preview/test send;
- templates reusable through Email surface.

---

## Surface 20 — Emails (`emails`)

**Benchmarks:** transactional email template/customizer and SMTP/provider products.

### Required options
- reusable templates;
- subject/preheader;
- HTML body component schema;
- plaintext fallback;
- dynamic tokens;
- responsive/email-safe rendering;
- global header/footer/branding;
- sender name/address;
- reply-to;
- recipient/CC/BCC rules with security limits;
- attachments from safe sources;
- test send;
- desktop/mobile preview;
- localization;
- provider selection through Connections;
- wp_mail/SMTP/API adapters;
- queue/retry;
- delivery/error log without sensitive body retention by default;
- rate limits;
- template versions;
- import/export;
- unsubscribe headers/preferences where appropriate.

---

## Surface 21 — Chat (`chat`)

**Benchmarks:** WordPress messaging/support/community chat plugins and SaaS chat integrations.

### Required options
- conversation/channel types: direct, group, support/thread provider;
- participants;
- roles/permissions;
- message text;
- attachments;
- reply/thread reference;
- edit/delete policy;
- unread/read receipts;
- notifications;
- search;
- moderation/report/block;
- conversation status/assignment for support mode;
- presence/typing provider optional;
- real-time transport adapter with polling fallback;
- retention/export/privacy;
- rate limits/anti-spam;
- frontend dashboard integration;
- REST/Ability API;
- audit for moderator actions.

---

## Surface 37 — Reservations (`reservations`)

**Benchmarks:** Amelia, Bookly, JetAppointment, JetBooking.

### Required options
- services/products;
- staff/providers;
- resources/rooms/assets;
- locations;
- availability schedules;
- working hours;
- breaks;
- holidays/days off;
- slot duration;
- service duration;
- buffer before/after;
- capacity/group booking;
- min/max advance booking;
- recurring appointments/bookings;
- multi-day/date-range bookings;
- timezone handling;
- customer/user fields;
- guest booking;
- approval/pending/confirmed/cancelled/completed/no-show statuses;
- reschedule/cancel windows;
- waitlist;
- pricing/variable pricing;
- deposits;
- coupons;
- taxes provider;
- payment provider;
- notifications/reminders;
- calendar view;
- Google/Outlook calendar sync adapters;
- frontend booking form;
- staff/customer dashboards;
- WooCommerce adapter;
- reports;
- REST/webhooks;
- overbooking/concurrency locks;
- audit.

---

## Surface 40 — Documents (`documents`)

**Benchmarks:** Gravity PDF/E2Pdf/PDF generator and invoice/certificate plugins.

### Required options
- document template definitions;
- HTML/email-like component schema;
- PDF renderer provider;
- merge fields/tokens;
- dynamic tables/repeaters;
- images/barcodes/QR providers;
- headers/footers/page numbers;
- page size/orientation/margins;
- fonts;
- locale/date/currency;
- invoice/order/member/booking/form-entry adapters;
- numbering sequences;
- certificates;
- generated filename/path;
- private/public access;
- download link expiration;
- email attachment;
- generation trigger;
- background generation;
- regeneration/versioning;
- signature provider;
- retention;
- audit;
- import/export template configuration.

---

# Suite F — Integrations & Data Transfer

## Surface 22 — REST API Builder (`rest-api`)

**Benchmarks:** JetEngine REST API, WPGetAPI, ACF/Meta Box REST integrations, WP Webhooks-class endpoint tooling.

### Required options
- endpoint name/namespace/version/path;
- HTTP methods;
- route parameters;
- query parameters;
- request body schema;
- headers;
- authentication: cookie/nonce internal, Application Passwords, OAuth/JWT/provider adapters only after review;
- permission Policy/Ability;
- public read vs authenticated mutation safeguards;
- data source/query binding;
- CRUD entity abilities;
- response field mapping;
- pagination;
- sorting/filter params;
- validation/sanitization;
- response status/error mapping;
- caching;
- rate limiting;
- CORS policy;
- idempotency for mutations;
- webhook callback integration;
- OpenAPI/schema generation;
- request/response test console with secret redaction;
- logs/metrics;
- versioning/deprecation;
- import/export.

---

## Surface 23 — Connections (`connections`)

**Benchmarks:** automation/webhook/API connector platforms.

### Required options
- provider registry;
- connection instance name;
- API key/basic/bearer/OAuth2/custom provider auth;
- Secrets Vault storage;
- scopes;
- environment/test/live distinction;
- OAuth callback;
- token refresh;
- connection test;
- health status;
- base URL/region;
- proxy/TLS settings only when safe;
- timeout;
- rate-limit metadata;
- retry/backoff defaults;
- webhooks/subscriptions provider;
- secret rotation/revocation;
- usage references/dependency graph;
- export without secrets;
- multisite/site ownership.

---

## Surface 26 — Import / Export (`import-export`)

**Primary benchmark:** WP All Import/Export; also ACF Local JSON, CPT UI import/export, Meta Box/JetEngine migrations.

### Required options
- formats: WPE Definition Package, JSON, CSV, XML, spreadsheet adapters where feasible;
- local upload;
- copy/paste;
- remote URL/provider;
- Google Sheets/remote source through Connection adapter;
- source preview;
- record/path selector;
- drag/drop or explicit field mapping;
- transformations;
- conditions;
- loops/repeating source nodes;
- create new;
- update existing;
- matching by UUID/key/ID/custom unique field;
- granular “which fields update” controls;
- missing-record behavior: keep/archive/delete only with explicit high-risk confirmation;
- taxonomies/hierarchies;
- custom fields;
- repeaters/flexible structures;
- relationships;
- users;
- media download/import with dedupe;
- custom tables/CCT;
- plugin adapters for ACF/Meta Box/JetEngine/Woo etc. where licensed/APIs permit;
- chunk size;
- background jobs;
- resume/checkpoint;
- scheduled recurring import;
- large-file streaming;
- dry-run/preflight;
- conflict report;
- checksum/integrity;
- rollback/recovery strategy;
- export column/layout builder;
- filtered/query-based export;
- CSV/XML/JSON/custom feed generation;
- logs;
- CLI;
- secrets excluded;
- multisite scope.

---

## Surface 41 — Sync (`sync`)

**Benchmarks:** recurring import/sync tools, WP Webhooks, WP Fusion-class connectors, migration sync systems.

### Required options
- source/destination connection/data source;
- one-way/two-way;
- entity mapping;
- field mapping;
- transforms;
- filters;
- full vs incremental/delta sync;
- cursor/checkpoint;
- schedule or webhook trigger;
- conflict policy: source wins, destination wins, newest, manual;
- create/update/delete/tombstone rules;
- ID mapping table;
- idempotency;
- retries/backoff;
- batching;
- rate limits;
- dry-run;
- per-record log/error;
- pause/resume;
- re-sync selected records;
- privacy/field exclusion;
- secret handling;
- multisite scope.

---

## Surface 44 — Redirects (`redirects`)

**Primary benchmark:** Redirection; also Rank Math/Yoast redirect managers.

### Required options
- source URL/path;
- target URL/action;
- 301, 302, 303 where appropriate, 307, 308;
- regex;
- wildcard/provider patterns;
- query parameter handling;
- ignore/pass-through/error modes where safe;
- groups/categories;
- enabled/disabled;
- priority/order;
- hit count/last hit;
- referrer/user agent/IP logging with privacy controls;
- 404 monitor;
- auto-redirect on slug/permalink changes;
- conditional redirects by login/role/capability/device/referrer/cookie/header only through safe Condition providers;
- domain/site migration rules;
- redirect chains/loops diagnostics;
- bulk edit;
- import/export CSV/JSON/.htaccess/_redirects/provider formats;
- preview import;
- CLI;
- log retention;
- multisite.

---

## Surface 45 — Transform (`transform`)

**Benchmarks:** WP All Import transformation/mapping, automation data mappers.

### Required options
- named transform pipeline;
- string operations;
- numeric operations;
- date/time parse/format/timezone;
- boolean normalization;
- list split/join/filter/map/sort/unique;
- object get/set/rename/remove;
- JSON parse/stringify;
- XML/CSV source helpers;
- regex with safety limits;
- replace/map lookup tables;
- conditional expressions;
- formulas/calculations;
- template interpolation;
- entity/field lookup provider;
- taxonomy/user lookups;
- media URL normalization;
- null/default/fallback;
- validation step;
- error policy;
- preview with sample input/output;
- test cases;
- versioning;
- reusable in import/export, sync, forms, REST and workflows;
- custom transform only through registered safe provider.

---

## Surface 55 — Staging (`staging`)

**Primary benchmarks:** WP STAGING, WP Migrate, Duplicator migration workflows.

### Required options
- create local/remote staging clone;
- target path/domain/database;
- files include/exclude;
- DB tables include/exclude;
- search/replace serialized-safe;
- user/content anonymization options;
- disable outgoing email;
- disable/adjust cron;
- discourage indexing/robots;
- password/protector integration;
- clone progress/resume;
- staging list/status;
- refresh staging from production;
- selective push to production: files, tables, selected groups;
- backup production before push;
- changed-file/data diagnostics where feasible;
- conflict strategy;
- URL/domain replacements;
- multisite/subsite support plan;
- remote Connection credentials;
- rollback;
- audit;
- explicit high-risk confirmation.

---

# Suite G — Operations & Security

## Surface 24 — Backup (`backup`)

**Primary benchmarks:** UpdraftPlus, Duplicator, BackWPup, WP STAGING backup.

### Required options
- database/files/full backup;
- include/exclude plugins/themes/uploads/wp-content/custom paths;
- extra DB tables/external DB provider;
- manual/scheduled;
- separate file/database schedules;
- incremental backups;
- retention count/time;
- compression/chunk size;
- encryption for sensitive archives;
- anonymized backup option;
- destinations: local, email only where size-safe, FTP/SFTP/FTPS/SCP, S3-compatible, Google Drive, Dropbox, OneDrive, Azure, WebDAV, Google Cloud, Backblaze and registered providers;
- multiple destinations;
- remote subfolder;
- transfer retry/resume;
- manifest/checksums;
- verify backup;
- restore selectable components;
- search/replace migration;
- direct site-to-site migration provider;
- selective migration;
- multisite/subsite cases;
- pre-restore backup;
- restore dry-run/compatibility diagnostics where feasible;
- progress/logs;
- notifications;
- CLI;
- secrets in Vault.

---

## Surface 25 — Reset (`reset`)

**Primary benchmark:** WP Reset.

### Required options
- reset site database to fresh state;
- delete posts/pages/CPT content;
- delete terms;
- delete comments;
- delete users with admin safety;
- delete transients;
- delete uploads/files selected scope;
- delete themes/plugins selected scope;
- delete custom tables/options by ownership;
- module-specific reset;
- deactivate/delete plugins options;
- snapshots before reset;
- restore snapshot where supported;
- dry-run impact count;
- typed confirmation phrase;
- capability/super-admin boundary;
- multisite site-only vs network reset;
- preserve current user option;
- preserve WPE configuration option;
- CLI guarded commands;
- audit.

---

## Surface 29 — XML-RPC (`xml-rpc`)

**Benchmarks:** WordPress security/hardening products.

### Required options
- current XML-RPC state diagnostic;
- disable all;
- allow authenticated methods only policy;
- disable pingbacks;
- method allowlist/denylist through filter provider;
- IP/network provider restrictions;
- rate-limit/brute-force integration;
- audit attempts/failures with retention;
- compatibility warnings for Jetpack/mobile/remote publishing integrations;
- multisite behavior;
- REST/Application Password alternative guidance.

---

## Surface 47 — Link Health (`link-health`)

**Benchmarks:** Broken Link Checker, SEO link monitors.

### Required options
- crawl internal content sources;
- external links;
- image/media links;
- redirects;
- HTTP status;
- timeout/DNS/TLS failure classes;
- anchors/fragments optional bounded check;
- source object/field/context;
- redirect-chain detection;
- broken internal permalink detection;
- scheduled scans;
- chunk/rate limits;
- external host politeness;
- retries/backoff;
- ignore/whitelist;
- mark fixed/dismiss;
- edit source deep link;
- export/report;
- notifications;
- history/last checked;
- privacy/no credential leakage;
- multisite.

---

## Surface 48 — Database Maintenance (`db-maintenance`)

**Primary benchmarks:** WP-Optimize, Advanced Database Cleaner.

### Required options
- post revisions cleanup;
- auto drafts;
- trashed posts/comments;
- spam/unapproved comments;
- expired/all transients;
- orphan post/term/user/comment meta;
- orphan relationships/term relationships where safely detectable;
- orphan options by known owner only;
- autoload option analysis/size;
- session/cache tables through adapters;
- table overhead/optimize;
- ANALYZE/repair provider with hosting compatibility;
- scheduled maintenance;
- retention age;
- table/owner exclusions;
- dry-run counts/size estimate;
- backup-before-destructive option;
- large-table chunking;
- logs;
- multisite;
- never claim unknown plugin data is orphaned without evidence.

---

## Surface 52 — Security Scanner (`security-scanner`)

**Primary benchmarks:** Wordfence, Patchstack, Solid Security, Sucuri.

### Required options
- WordPress core integrity scan;
- plugin/theme file integrity where checksums available;
- malware/signature scan provider;
- suspicious PHP/JS patterns;
- modified core files;
- vulnerable plugin/theme/core intelligence provider;
- abandoned/outdated extension warnings;
- weak admin/user security checks;
- exposed secrets/config backup files;
- writable file/permission diagnostics;
- dangerous PHP settings diagnostics;
- public debug/log exposure;
- scheduled scans;
- scan intensity/resource limits;
- include/exclude paths with warnings;
- quarantine provider;
- repair from trusted source where checksum/source allows;
- ignore/resolve findings;
- severity/CVSS/provider metadata;
- notifications;
- reports/history;
- multisite;
- firewall/rate-limit controls only through a clearly owned protection sub-engine, not duplicated invisibly;
- login security/2FA integration boundary;
- no secret/file-content leakage in reports.

---

# Suite H — Platform, Solutions & Developer/AI

## Surface 31 — Platform (`platform`)

**Benchmarks:** mature modular suites such as JetEngine/Meta Box extension management plus WordPress Site Health.

### Required options
- module enable/disable/degraded state;
- dependency/conflict display;
- Free/Pro entitlement state;
- version compatibility;
- WordPress/PHP/DB environment;
- migration/schema status;
- jobs/queue status;
- cron health;
- REST/Ability status;
- storage usage;
- caches;
- module health checks;
- Site Health integration;
- diagnostics bundle with redaction;
- logs;
- audit viewer entry points;
- feature flags/beta features;
- developer mode;
- telemetry opt-in and privacy controls;
- update channel stable/beta where supported;
- license/update provider for Pro;
- onboarding/first-run state;
- recovery mode information;
- import/export platform config excluding secrets;
- multisite/network controls.

---

## Surface 32 — Solutions (`solutions`)

**Benchmark:** Crocoblock solution/template ecosystems and starter-site/configuration packages.

### Required options
- packaged solution manifest;
- use cases: directory, real estate, jobs, membership, booking, events, marketplace, CRM-like records, knowledge base and extensible recipes;
- required/optional modules;
- dependency versions;
- included Definitions;
- sample/demo data optional and separately consented;
- assets/templates;
- builder compatibility;
- install preview;
- conflict/preflight;
- create-only/update behavior;
- environment variables/secrets placeholders;
- rollback/uninstall impact;
- detach/fork solution for customization;
- solution updates with diff, never silent overwrite;
- export user-created solution package;
- marketplace/source signing provider future option.

---

## Surface 43 — AI Gateway (`ai`)

**Benchmarks:** WordPress AI Engine-class products plus modern provider APIs.

### Required options
- provider connections via Vault;
- model catalog/capabilities;
- text/chat/vision/embedding capability classification;
- prompt templates;
- system/instruction/user message structure;
- variables/tokens from WPE data;
- structured output schema;
- tool/Ability allowlist;
- read-only default AI permissions;
- human confirmation for sensitive mutation policies;
- content/field generation;
- summarization/classification/extraction;
- query/transform assistance with preview;
- embeddings/vector provider;
- RAG over allowed site data;
- model fallback/routing;
- temperature/max tokens/provider options;
- streaming;
- moderation/safety provider;
- usage/cost accounting;
- quotas per user/role/module;
- prompt/result audit with privacy redaction;
- retention controls;
- PII/private-data classification;
- no secret exposure;
- AI-generated Definition changes require normal validation/Policy.

---

## Surface 46 — Fixtures (`fixtures`)

**Primary benchmark:** FakerPress and development seeding tools.

### Required options
- deterministic random seed;
- locale;
- count/range;
- posts/pages/CPTs;
- terms/taxonomies/hierarchy;
- users/roles;
- comments;
- media placeholders/providers;
- custom fields by schema/type;
- repeaters/groups;
- relations;
- custom-table/CCT rows;
- membership/booking/form-entry fixtures through adapters;
- date ranges/status distributions;
- author distributions;
- dependency-aware generation order;
- named fixture sets;
- cleanup only generated fixture UUID/tag;
- preview counts;
- background jobs for scale;
- CLI;
- no production enablement by default.

---

## Surface 50 — Safe Script (`safe-script`)

**Primary benchmarks:** WPCode, Code Snippets, Scripts Organizer-class products.

### Required options
- snippet types: PHP, JavaScript, CSS, HTML, shortcode/provider;
- title/tags/notes;
- active/draft;
- code editor/syntax highlighting;
- syntax validation;
- PHP activation sandbox/error detection;
- automatic disable/recovery on fatal risk;
- safe mode/recovery URL;
- insertion: site-wide header/body/footer, frontend/admin/login, before/after content/post, hook/action/filter provider, shortcode/manual;
- priority;
- run once/admin-only/provider modes;
- Smart Conditional Logic using shared Conditions;
- page/post/CPT/archive/user/device/schedule conditions;
- revisions/version history;
- clone;
- import/export;
- staging/test mode;
- execution logs/errors;
- capability restrictions;
- multisite/network snippets;
- code-signing/approved-provider future option;
- explicit warning that arbitrary code is privileged and never exposed to ordinary editor roles/AI by default.

---

## Surface 51 — Content Order (`content-order`)

**Benchmarks:** Post Types Order, Simple Custom Post Order, taxonomy ordering products.

### Required options
- enable per post type/taxonomy/entity;
- drag/drop order;
- hierarchical ordering;
- term ordering;
- manual numeric order editor;
- default sort integration;
- admin-only vs frontend query application;
- query-specific opt-in/opt-out;
- reset order;
- bulk move;
- permissions;
- role restrictions;
- REST/Ability update;
- import/export;
- large-list pagination/order strategy;
- concurrent update/revision protection;
- relation/listing/query integration.

---

# Cross-surface competitive requirements

The following are mandatory platform-wide and are not allowed to be forgotten inside individual modules.

## A. Import/export and migration
Every builder/configuration surface must classify clone, import, export, version-control/local-file and competitor-migration behavior.

## B. REST/Abilities
Every surface must classify read, create, update, destructive and operation abilities separately. Public REST exposure is explicit.

## C. Conditions
All conditional logic must reuse the shared typed Condition/Decision engine rather than each module inventing incompatible operators.

## D. Dynamic values
Fields, listings, forms, emails, documents, placements, dashboards and builder widgets use the same safe dynamic-value resolver.

## E. Querying
Queryable modules expose adapters to the Query engine rather than hand-building unrelated query languages.

## F. Relationships
Cross-object links use the Relation engine unless WordPress core has a specific canonical relationship that has a separate owner (for example taxonomy-object registration).

## G. Admin UX
Every complex surface follows Admin UX V2: Essential / Advanced / Expert, domain navigation, search, defaults/inheritance, validate, sticky command state, diagnostics, responsive and accessible behavior.

## H. Multisite
Every surface must state site/network/global data scope, network activation behavior and Super Admin boundaries.

## I. Performance
Every published feature that claims sorting/filtering/querying/background operations must have a realistic large-data strategy and evidence gate.

## J. Extensibility
Hooks/providers/adapters are planned deliberately. Normal settings pages never accept arbitrary executable PHP merely to match a competitor checkbox.

## K. Parity certification
A surface cannot be called “complete” until its accepted competitor inventory has zero unexplained missing capabilities and its implemented behavior passes runtime + UX + portability + accessibility + compatibility evidence.
