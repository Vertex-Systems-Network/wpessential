# WPEssential — Atomic Option Contracts Wave 2: Content Experience & Presentation

Status: **atomic option inventory**  
Snapshot: **2026-08-31**  
Surfaces: **5, 8, 9, 10, 11, 13, 14, 16, 28, 38, 39, 49, 53, 56**.

This is an implementation-grade option inventory. It is not a runtime/parity certification.

---

# Surface 5 — Custom Status

## Identity
- immutable UUID;
- status key;
- display label;
- singular/plural labels where UI needs both;
- description;
- icon;
- color;
- lifecycle: draft/published/disabled/archived definition.

## WordPress status behavior
- public;
- private;
- protected/internal classification;
- `publicly_queryable` where applicable;
- `exclude_from_search` behavior;
- show in admin status/all lists;
- show in admin dropdown;
- show in bulk edit;
- show in quick edit;
- post count callback/provider;
- allowed post types;
- default status by post type/context;
- status query integration;
- REST exposure;
- import/export.

## Transition workflow
- allowed From statuses;
- allowed To statuses;
- transition matrix;
- transition capability per edge;
- role restrictions;
- user restrictions;
- conditional transition rules;
- require comment/reason;
- validation before transition;
- notification triggers;
- workflow event triggers;
- audit transition;
- scheduled/future status semantics;
- transition bulk operation policy.

## Retirement/migration
- replacement status;
- affected content count;
- dry-run;
- chunk size;
- dependency impact;
- archive/disable vs delete;
- rollback/recovery.

---

# Surface 8 — Admin Columns

## Column-set identity
- UUID;
- name;
- target list screen;
- posts/CPTs;
- taxonomies/terms;
- users;
- media;
- comments;
- custom-table entities;
- status;
- clone;
- per-role assignment;
- per-user assignment;
- default/fallback set.

## Column definition
- UUID/key;
- label;
- order;
- width;
- minimum/maximum width;
- alignment;
- source type;
- source field/meta key;
- native property;
- taxonomy;
- relation;
- user property;
- media metadata;
- query aggregate;
- computed provider;
- custom registered provider;
- fallback/empty value.

## Display formats
- text;
- number;
- currency/provider;
- date;
- datetime;
- relative time;
- boolean;
- image;
- icon;
- link;
- email;
- taxonomy chips;
- status badge;
- relation item list;
- relation count;
- progress;
- rating;
- truncated text;
- custom template/provider.

## Sorting
- sortable enabled;
- sort source;
- numeric/string/date mode;
- ASC/DESC default;
- backend-before-pagination requirement;
- unavailable sorting explanation;
- expensive sort warning;
- custom provider sort contract.

## Filtering
- filterable;
- data-aware operator list;
- equals/not equal;
- contains/not contains;
- starts/ends;
- greater/less/between;
- empty/not empty;
- date ranges;
- taxonomy/entity filters;
- saved filters;
- filter presets;
- user-specific saved filter state;
- smart filter indexing/provider.

## Editing
- inline edit enabled;
- editor control;
- validation;
- capability;
- bulk edit;
- bulk value transform;
- quick edit;
- audit;
- optimistic/revision conflict handling.

## Export
- CSV export;
- selected columns;
- current filters;
- current sort;
- page/all rows;
- export capability;
- background export threshold;
- encoding/delimiter;
- privacy redaction.

## Performance
- batch hydration;
- no N+1 rule;
- query budget;
- expensive column diagnostic;
- cached computed values;
- cache invalidation;
- large list-table performance evidence.

---

# Surface 9 — Listings

## Definition/source
- UUID;
- name/key;
- source type;
- Query definition;
- Entity/Data Source;
- Relation traversal;
- REST source;
- status;
- clone;
- revisions.

## Layout modes
- grid;
- list;
- cards;
- compact rows;
- table;
- map adapter;
- calendar adapter;
- timeline adapter;
- custom registered renderer.

## Item blueprint
- container element;
- semantic role;
- dynamic text;
- dynamic field;
- image/media;
- link;
- taxonomy terms;
- relation values;
- repeaters/flexible values;
- icon;
- badge/status;
- date/time;
- buttons/actions;
- nested component;
- dynamic classes through validated token provider;
- conditional visibility;
- fallback values.

## Grid/layout controls
- columns desktop/tablet/mobile;
- gap row/column;
- alignment;
- equal height;
- masonry provider;
- item min/max width;
- container width;
- responsive breakpoints from Theme Workspace;
- table columns and responsive collapse.

## Results behavior
- empty state;
- error state;
- loading state;
- skeleton;
- result count;
- sort controls;
- filter integration;
- search integration;
- numbered pagination;
- prev/next;
- load more;
- infinite scroll;
- cursor pagination;
- current-page state;
- browser history/query-string sync;
- scroll/focus after pagination.

## Rendering/integration
- server-rendered fallback;
- block;
- shortcode;
- Elementor adapter;
- Bricks adapter;
- native Gutenberg adapter;
- template part/provider;
- dynamic value/token resolver;
- action Ability binding;
- permission-aware output.

## Performance/SEO/accessibility
- permission/context-aware cache;
- cache TTL/invalidation;
- lazy media;
- image sizes/srcset;
- query performance diagnostic;
- maximum render items;
- schema/structured-data provider;
- semantic list/table/grid roles;
- keyboard action controls;
- focus management;
- reduced-motion behavior.

---

# Surface 10 — Dashboard Widgets

## Definition
- UUID;
- title;
- key;
- status;
- site/network target;
- widget type;
- capability/role/user visibility;
- conditional visibility;
- clone/import/export.

## Widget types
- rich text/info;
- KPI/stat;
- chart;
- listing/query;
- activity/ledger;
- quick links;
- form/action;
- Site Health/diagnostic;
- announcements;
- registered provider.

## Presentation
- context/area;
- priority/order;
- width;
- height/min-height;
- collapsible;
- dismissible;
- default collapsed;
- icon;
- help link;
- empty/loading/error states;
- compact/comfortable density.

## Data/refresh
- query/source;
- refresh interval;
- manual refresh;
- background refresh;
- cache TTL;
- cache scope;
- stale state;
- last updated;
- retry/error behavior.

## User preference
- drag/reorder;
- hide/dismiss;
- reset layout;
- per-user state;
- role defaults;
- network defaults.

## Actions
- Ability-backed buttons;
- capability check;
- confirmation;
- dry run where relevant;
- result notice;
- audit.

---

# Surface 11 — Admin Menu

## Transformation rule
- target original menu/submenu;
- rename;
- icon;
- order;
- parent reassignment;
- hide/show;
- role visibility;
- capability visibility;
- current-user visibility;
- Super Admin/network visibility;
- condition/Decision rule;
- restore original.

## Custom menu items
- title;
- icon;
- internal WPE route;
- safe wp-admin URL;
- external URL;
- target behavior;
- capability;
- role/user visibility;
- conditional visibility;
- separator/group headers;
- submenu items.

## Profiles
- named menu profile;
- role assignment;
- user assignment;
- site/network assignment;
- fallback profile;
- clone;
- import/export;
- preview as role/user.

## Admin bar
- hide/show item;
- rename;
- order/group;
- custom item;
- role/capability/user rules;
- network rules;
- restore original.

## Safety
- hiding menu does not revoke capability;
- required WPE recovery/menu route remains available to authorized admin;
- duplicate slug conflict detection;
- transformations applied after plugins register menus;
- missing target shown as degraded, not silently lost;
- no arbitrary unsafe URL schemes.

---

# Surface 13 — Frontend Dashboards

## Dashboard identity
- UUID;
- name/key;
- base route/slug;
- page/template host;
- status;
- access policy;
- login requirement;
- role/membership/capability rules;
- unauthorized behavior;
- redirect behavior;
- theme/layout selection.

## Navigation
- groups;
- endpoint/item;
- title;
- icon;
- route slug;
- order;
- parent/child;
- visibility policy;
- badges/count providers;
- external safe links;
- active state;
- mobile navigation behavior.

## Endpoint types
- account overview;
- profile edit;
- password/security;
- user content listing;
- add content;
- edit content;
- custom-table/CCT CRUD;
- favorites/data stores;
- bookings;
- memberships/subscriptions;
- notifications;
- chat/messages;
- forms;
- listings/query;
- documents/downloads;
- registered custom component.

## User-content CRUD
- source post type/entity;
- ownership field/policy;
- add/edit/delete permissions;
- field group/form;
- default status;
- moderation status;
- max items;
- media rules;
- relation editing;
- validation;
- success/error redirects;
- audit.

## Presentation
- header/sidebar/top navigation;
- responsive breakpoint;
- breadcrumbs;
- empty/loading/error;
- notices;
- account avatar/name;
- theme tokens;
- builder integration;
- accessible landmarks/focus.

---

# Surface 14 — User Profiles

## Profile schema
- field groups;
- native WP user fields;
- custom user fields;
- avatar;
- cover image/provider;
- biography;
- social links;
- address/contact;
- relation fields;
- privacy class per field;
- user-editable vs admin-only;
- validation;
- REST exposure.

## Public profile
- enabled;
- profile slug;
- slug source;
- collision behavior;
- visibility: public/members/roles/private;
- template/listing;
- sections/tabs;
- field visibility;
- relation/content sections;
- SEO title/meta provider;
- canonical URL;
- directory integration.

## Registration
- enabled;
- registration form;
- default role;
- role allowlist;
- username/email policy;
- password policy;
- email verification;
- admin approval;
- auto-login;
- redirect;
- anti-spam;
- terms/privacy consent;
- workflow/notification triggers.

## Login/reset
- login form;
- remember me;
- redirect by role;
- failed-login behavior;
- password reset form;
- reset token handling through WordPress;
- password policy;
- logout redirect;
- login security integration.

## Directory
- query/source;
- card/list template;
- filters;
- search;
- sort;
- pagination;
- role inclusion/exclusion;
- privacy controls;
- map/geo integration.

---

# Surface 16 — Builder Widgets / Dynamic Components

## Component Blueprint
- UUID;
- name/key;
- category;
- icon;
- description;
- status;
- version;
- supported adapters;
- dependencies;
- clone/import/export.

## Controls
- control schema from Fields/Control Registry;
- content controls;
- style controls;
- advanced controls;
- responsive values;
- dynamic value support;
- conditional controls;
- repeater/group controls;
- defaults;
- validation.

## Rendering
- server render;
- client render provider;
- dynamic token bindings;
- query/listing binding;
- relation binding;
- action/Ability binding;
- escaping context;
- empty/error/loading states;
- caching;
- asset dependencies.

## Style
- typography;
- spacing;
- dimensions;
- colors/background;
- border/radius/shadow;
- alignment/flex/grid provider;
- responsive breakpoints;
- state styles: hover/focus/active;
- theme tokens;
- CSS output through validated bindings only.

## Adapters
- Gutenberg/block;
- shortcode fallback;
- Elementor;
- Bricks;
- WPBakery;
- Visual Composer;
- registered third-party adapter.

Each adapter declares parity gaps rather than silently ignoring blueprint controls.

---

# Surface 28 — Media

## Organization
- folders/collections;
- nested folders;
- virtual vs physical organization;
- user/role visibility;
- drag/move;
- bulk organize;
- saved media filters;
- tags/provider.

## Attachment metadata
- native title/caption/alt/description;
- custom field groups;
- taxonomy terms;
- privacy classification;
- usage references;
- checksum/hash;
- file dimensions/duration;
- focal point/crop metadata;
- external/offload provider metadata.

## Upload policy
- allowed MIME;
- extension;
- max size;
- image min/max dimensions;
- SVG policy/provider;
- user/role upload limits;
- filename sanitization;
- duplicate filename behavior;
- duplicate-content detection;
- malware/scanner adapter;
- private media policy.

## Operations
- replace file;
- retain attachment ID/URLs where safe;
- regenerate metadata;
- regenerate image sizes;
- bulk edit;
- bulk delete;
- unused/orphan detection;
- usage impact before delete;
- restore/recovery where supported;
- import/export metadata.

## Optimization/offload adapters
- compression provider;
- WebP/AVIF provider;
- resize policy;
- lazy loading metadata;
- object storage/offload;
- CDN URL mapping;
- local-copy retention;
- sync status;
- provider health.

---

# Surface 38 — Placement

## Placement identity
- UUID;
- name/key;
- status;
- placement type;
- priority;
- content/component source;
- clone/import/export.

## WordPress locations/providers
- before/after content;
- before/after title;
- post/page templates via documented hooks;
- header/footer provider;
- sidebar/widget area;
- archive/listing positions;
- WooCommerce provider locations;
- block hooks/provider;
- shortcode/manual token;
- registered custom hook provider.

## Targeting
- post types;
- individual posts/pages;
- taxonomies/terms;
- templates;
- archives;
- user role;
- membership;
- logged-in/out;
- device/responsive;
- date/time schedule;
- geo/provider;
- Decision rule;
- include/exclude groups.

## Rendering
- component/listing/template;
- priority/order;
- wrapper semantics;
- CSS classes through safe tokens;
- fallback;
- cache scope;
- experiment integration;
- analytics impression/click events.

---

# Surface 39 — Experiments

## Experiment identity
- UUID;
- name/key;
- status: draft/running/paused/completed/archived;
- hypothesis/description;
- owner;
- start/end;
- clone.

## Variants
- control;
- variants;
- component/content source;
- traffic percentage;
- sum validation;
- deterministic assignment key;
- sticky assignment;
- anonymous/user identity policy;
- exclusion rules.

## Audience
- all traffic;
- logged-in/out;
- role;
- membership;
- device;
- referrer/UTM;
- geo provider;
- Decision rule;
- new/returning visitor provider;
- percentage rollout.

## Goals
- page view;
- click;
- form submit;
- registration;
- purchase/provider;
- booking;
- membership signup;
- custom Analytics event;
- conversion window;
- primary/secondary goals.

## Statistics/governance
- impressions;
- conversions;
- conversion rate;
- uplift;
- confidence/significance provider;
- minimum sample warning;
- minimum duration;
- multiple testing warning;
- manual winner;
- auto winner only if accepted methodology/provider;
- stop/rollback;
- history;
- privacy/consent;
- bot exclusion provider.

---

# Surface 49 — Admin Theme

## Branding
- admin logo;
- login logo;
- logo link/title;
- favicon/admin icon provider;
- brand colors;
- footer text;
- admin bar branding;
- white-label WPE visibility policy;
- per-site/network brand profile.

## Theme tokens
- light/dark/system mode;
- primary/accent;
- success/warning/error/info;
- text/background/surface/border;
- focus color;
- radius;
- shadow;
- spacing density;
- font family;
- font size scale.

## Admin layout
- menu width;
- collapsed menu behavior;
- top bar behavior;
- content max width;
- compact/comfortable density;
- dashboard clean-up integration;
- Screen Options policy;
- responsive rules.

## Login screen
- background color/image;
- form placement;
- form width;
- logo;
- button style;
- custom message;
- privacy/help links;
- safe custom CSS only via Theme Workspace output provider.

## Scope
- global default;
- site override;
- network profile;
- role/user profile where appropriate;
- reset/preview;
- import/export.

Accessibility contrast/focus constraints cannot be disabled by theme styling.

---

# Surface 53 — Fonts

## Font source
- system stack;
- uploaded local font;
- registered theme font;
- Google Fonts provider if explicitly enabled;
- Adobe/remote provider adapter;
- variable font;
- icon font provider;
- fallback stack.

## Font family definition
- UUID/key;
- family name;
- source;
- license/attribution metadata;
- status;
- preload flag;
- display strategy;
- unicode subset;
- fallback.

## Faces
- weight;
- style;
- stretch;
- file format;
- WOFF2 priority;
- WOFF/provider compatibility;
- variable axes;
- source URL/file;
- checksum;
- MIME validation.

## Performance/privacy
- self-host preference;
- external request disclosure;
- preload only used critical face;
- duplicate family detection;
- unused font diagnostics;
- file size;
- subset provider;
- caching headers diagnostic;
- GDPR/privacy warning for remote providers.

## Integration
- Theme Workspace;
- typography controls;
- Admin Theme;
- builder adapters;
- editor styles;
- frontend enqueue only when used;
- import/export excluding licensed binaries unless explicitly portable/licensed.

---

# Surface 56 — Theme Workspace

## Workspace identity
- UUID;
- name/key;
- active theme context;
- site/network scope;
- status;
- revisions;
- clone/import/export.

## Global colors
- palette groups;
- token name/key;
- color value;
- semantic token mapping;
- light/dark variants;
- gradients;
- aliases;
- deprecated token migration;
- contrast diagnostics.

## Typography
- font family token;
- size scale;
- weight;
- style;
- line height;
- letter spacing;
- text transform;
- responsive overrides;
- heading/body/button/form roles;
- variable font axes provider.

## Spacing/layout
- spacing scale;
- container widths;
- content width;
- wide width;
- breakpoints;
- grid columns;
- gutters;
- radius scale;
- shadow scale;
- z-index tokens where safely modeled.

## Component tokens
- button;
- input;
- card;
- table;
- badge;
- alert;
- modal;
- navigation;
- listing;
- form;
- dashboard;
- custom registered component token set.

## Output adapters
- CSS variables;
- theme.json adapter;
- Site Editor adapter;
- Gutenberg editor styles;
- Elementor global colors/fonts adapter;
- Bricks variables adapter;
- WPE component blueprint adapter;
- custom registered provider.

## Preview/versioning
- desktop/tablet/mobile preview;
- light/dark preview;
- changed-token diff;
- usage/dependency graph;
- rollback revision;
- staged publish;
- invalid token diagnostics;
- provider sync status;
- export/import mapping.

Raw arbitrary PHP is prohibited. Custom CSS, if supported through Safe Script/validated CSS provider, remains a separate privileged surface.

---

# Shared Wave 2 UX requirements

- common WPE list-screen grammar;
- Essential/Advanced/Expert progressive disclosure;
- setting search;
- live preview where meaningful;
- explicit runtime/effective state;
- no frontend or admin operation based only on hidden UI permission;
- query/list rendering must remain server-authoritative for sort/filter/pagination;
- all visual configuration must retain WCAG focus/contrast safeguards;
- expensive dynamic widgets/listings/columns expose performance diagnostics;
- destructive media/menu/theme migrations use preview and recovery;
- responsive and accessibility E2E required before runtime certification.
