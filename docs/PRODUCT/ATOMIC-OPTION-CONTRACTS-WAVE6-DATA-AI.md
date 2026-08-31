# WPEssential — Atomic Option Contracts Wave 6: Data Intelligence, AI & Solution Systems

Status: **atomic option inventory**  
Snapshot: **2026-08-31**  
Surfaces: **32 Solutions, 33 Analytics, 34 Search, 35 Decision, 36 Ledger, 42 Geo, 43 AI, 46 Fixtures, 51 Content Order, 54 User Stores**.

---

# Surface 32 — Solutions

## Solution package identity
- UUID;
- name/key;
- category;
- description;
- version;
- compatible WPE version range;
- edition requirement;
- required modules;
- optional modules;
- required integrations/providers;
- conflicts;
- status;
- screenshots/docs metadata;
- license/source metadata.

## Package content
- CPT definitions;
- taxonomies;
- field groups;
- relations;
- queries;
- listings;
- settings pages;
- dashboards/profiles;
- forms/workflows;
- notifications/emails;
- theme tokens/components;
- sample/fixture data separately classified;
- migration/adapters;
- no secrets.

## Installation
- dependency preflight;
- provider availability;
- conflicts;
- required capabilities;
- estimated definitions/data;
- create vs update existing;
- UUID/key collision handling;
- dry-run;
- selected components;
- install progress;
- rollback/remove package changes where attributable;
- preserve user-modified definitions policy.

## Update
- current/new version;
- package diff;
- local modifications;
- merge strategy;
- overwrite protection;
- migration scripts/providers;
- backup/recovery;
- staged update;
- rollback.

## Preset solution families
- directory;
- real estate;
- jobs;
- events;
- booking;
- membership;
- knowledge base;
- portfolio;
- marketplace/provider;
- custom first-party package.

A Solution is composition of canonical modules, never a hidden duplicate runtime engine.

---

# Surface 33 — Analytics

## Event definition
- UUID/key;
- event name;
- category;
- source module;
- properties schema;
- privacy class;
- enabled;
- retention;
- sampling/aggregation policy.

## Built-in event families
- page/content view;
- listing impression;
- listing click;
- search;
- zero-result search;
- form view/start/submit/error;
- workflow outcome;
- registration/login;
- membership signup/cancel/renew;
- booking create/cancel/complete;
- document download;
- user-store add/remove;
- experiment exposure/conversion;
- custom registered event.

## Collection
- server events;
- client events;
- anonymous/session ID policy;
- logged-in user ID policy;
- consent mode;
- IP anonymization/no storage by default;
- user agent/device categorization provider;
- referrer;
- UTM source/medium/campaign/content/term;
- timestamp/timezone;
- deduplication/event ID;
- bot filtering provider.

## Goals/conversions
- goal definition;
- triggering events;
- property conditions;
- conversion value;
- currency;
- attribution window;
- first/last/custom attribution provider;
- funnel step;
- primary/secondary;
- experiment integration.

## Funnels/cohorts
- ordered steps;
- optional steps;
- time window;
- segmentation;
- drop-off;
- cohort entry condition;
- retention period;
- returning behavior;
- privacy aggregation minimum/provider.

## Reporting
- date range;
- comparison;
- dimensions;
- metrics;
- filters;
- segments;
- breakdowns;
- table/chart;
- dashboard widget;
- scheduled report;
- CSV/export;
- multisite rollup with explicit permission/scope.

## External analytics
- GA4/provider;
- Matomo/provider;
- server-side endpoint/provider;
- outbound event mapping;
- consent gating;
- retry;
- no provider credentials outside Vault.

---

# Surface 34 — Search

## Engine identity
- UUID;
- name/key;
- status;
- sources;
- default engine;
- locale/language;
- revisions;
- clone/import/export.

## Sources
- posts/CPTs;
- terms;
- users;
- comments;
- media metadata;
- custom tables;
- registered data source;
- WooCommerce provider;
- document/PDF text provider.

## Indexed attributes
- title;
- content;
- excerpt;
- slug;
- custom fields;
- taxonomy terms;
- comments;
- author;
- media metadata;
- custom table fields;
- relation-derived field/provider.

Per attribute:
- enabled;
- weight;
- exact boost;
- phrase boost;
- tokenization provider;
- data type;
- normalization.

## Inclusion/exclusion rules
- post type/source;
- status;
- taxonomy;
- field/meta;
- author;
- date;
- specific IDs;
- role/capability visibility;
- membership/access Policy;
- Decision rules;
- no indexing content user cannot later access without permission-aware result shaping.

## Relevance
- exact match;
- phrase;
- token match;
- stemming;
- fuzzy matching;
- partial/prefix;
- minimum word length;
- stopwords;
- synonyms;
- keyword minimum;
- custom boosts;
- recency boost;
- popularity/Analytics boost provider;
- custom ranking provider.

## Indexing
- enabled;
- background queue;
- batch size;
- incremental updates;
- full rebuild;
- status/progress;
- index size;
- source counts;
- last indexed;
- failed records;
- pause/resume;
- retention/cleanup;
- multilingual/provider indexes.

## Query/search UX
- keyword;
- live/AJAX search;
- minimum characters;
- debounce;
- suggestions/provider;
- spell correction provider;
- result excerpt;
- highlight;
- result template/listing;
- filters/facets;
- sort;
- pagination;
- no-results suggestions;
- analytics logging privacy.

## Faceting integration
- checkbox;
- radio;
- dropdown;
- multi-select;
- range;
- date range;
- taxonomy;
- hierarchy;
- search facet;
- geo/map;
- result counts;
- contextual counts;
- indexed facet data;
- URL/history sync;
- reset filters.

---

# Surface 35 — Decision / Rules Engine

## Decision definition
- UUID;
- name/key;
- description;
- input schema;
- status;
- version/revisions;
- priority;
- fallback outcome;
- clone/import/export.

## Condition groups
- nested AND;
- nested OR;
- NOT group;
- short-circuit behavior;
- group label;
- group priority where relevant.

## Value types
- string;
- number;
- boolean;
- date/time;
- duration;
- list/set;
- entity reference;
- field value;
- user;
- role/capability;
- membership;
- query result;
- relation count/value;
- geo value;
- request/context parameter;
- registered provider.

## Operators
- equals/not equal;
- > >= < <=;
- contains/not contains;
- starts/ends;
- in/not in;
- between/not between;
- exists/not exists;
- empty/not empty;
- before/after;
- within duration;
- matches registered safe pattern;
- has role/capability;
- relation exists/count;
- query count/result;
- geo radius/provider.

## Outcomes
- boolean;
- named outcome;
- score;
- weighted score;
- value selection;
- branch/action reference;
- fallback.

## Simulation/testing
- sample inputs;
- expected outcome;
- test cases;
- explain evaluation path;
- condition timing/provider;
- missing input behavior;
- type mismatch;
- provider unavailable;
- sensitive evaluation audit optional.

No arbitrary PHP expression condition.

---

# Surface 36 — Ledger / Activity Audit

## Event record schema
- event ID;
- timestamp;
- site/network;
- actor principal;
- impersonation/provider state;
- channel: UI/REST/CLI/job/AI/provider;
- action/event;
- category;
- severity;
- resource type;
- resource ID;
- before structured state;
- after structured state;
- structured diff;
- correlation ID;
- request/run ID;
- source module;
- result success/failure;
- redacted metadata.

## Audit policy
- event categories enabled;
- minimum severity;
- module filters;
- retention days;
- storage scope;
- archive provider;
- legal hold provider;
- user/privacy redaction;
- IP storage/anonymization policy;
- secret-field redaction;
- body/payload size limits.

## Viewer
- date range;
- actor;
- action;
- module;
- resource;
- severity;
- site;
- channel;
- correlation ID;
- search;
- object/user timeline;
- diff viewer;
- export;
- saved filters.

## Alerts
- rule/Decision;
- severity;
- notification target;
- throttle;
- deduplicate;
- new admin/role changes;
- security scanner events;
- destructive operation;
- repeated failures.

## Integrity
- append-only logical policy;
- edit prohibited for ordinary admins;
- purge through retention/destructive capability only;
- optional hash chaining/tamper evidence;
- verify chain;
- archive/restore provider;
- no secrets.

---

# Surface 42 — Geo

## Provider configuration
- map provider;
- geocoding provider;
- autocomplete provider;
- routing/distance provider;
- provider connection/secret;
- region/language;
- quota/rate limit;
- fallback provider;
- privacy precision.

## Location field/value
- formatted address;
- latitude;
- longitude;
- place/provider ID;
- street number;
- route;
- locality/city;
- region/state;
- postal code;
- country;
- timezone provider;
- precision;
- manually adjusted pin;
- geocode status.

## Map control
- center;
- zoom;
- min/max zoom;
- map type/provider;
- draggable marker;
- multiple markers;
- marker icon;
- popup template;
- cluster enabled;
- cluster settings;
- bounds fit;
- fullscreen/control visibility;
- accessibility fallback/list.

## Query/filter
- radius center;
- radius value/unit;
- distance sorting;
- bounding box;
- polygon/geofence provider;
- current user location with consent;
- location field source;
- max result radius;
- zero/missing coordinate behavior;
- Query Builder integration;
- Search/facet integration;
- Listings/map sync.

## Geocoding runtime
- on save;
- manual;
- background bulk geocode;
- retry;
- cache;
- provider status;
- ambiguous result selection;
- reverse geocode;
- quota diagnostics;
- privacy rounding/truncation.

---

# Surface 43 — AI Gateway

## Provider/model
- provider Connection;
- model;
- task capability classification;
- enabled;
- allowed modules;
- temperature/top-p only where model supports;
- max output tokens;
- timeout;
- retry;
- fallback model/provider;
- cost metadata;
- region/privacy provider.

## Prompt/template
- UUID;
- name/key;
- system instruction;
- user template;
- variables schema;
- output schema;
- version;
- test cases;
- status;
- privacy classification;
- no secrets embedded.

## Structured output
- JSON schema;
- strict mode provider;
- validation;
- retry/repair policy;
- max attempts;
- fallback/error;
- model output always untrusted until schema/Policy validation.

## Context/RAG
- approved data sources;
- Query definitions;
- document indexes/provider;
- embeddings provider;
- chunk size/overlap;
- metadata filters;
- maximum retrieved items/tokens;
- permission-aware retrieval;
- PII/private field exclusions;
- source citation metadata.

## Ability/MCP exposure
- allowlisted Ability;
- read/write/destructive classification;
- principal authorization;
- human confirmation requirement;
- dry-run;
- maximum scope/batch;
- audit;
- input redaction;
- output validation;
- AI cannot bypass Policy.

## Quotas/cost
- per-user;
- per-role;
- per-site;
- per-feature;
- daily/monthly tokens;
- currency budget provider;
- warning threshold;
- hard stop;
- usage reporting;
- estimated/actual cost where provider reports.

## Safety/privacy
- sensitive data classifications allowed/denied;
- provider data-retention mode metadata;
- user consent where required;
- no arbitrary DB/file shell;
- no raw PHP execution;
- no secret dump;
- prompt injection treated as untrusted content;
- tool result output escaping.

---

# Surface 46 — Fixtures

## Fixture set
- UUID;
- name/key;
- purpose: development/demo/test/migration verification;
- target modules/entities;
- status;
- version;
- seed value;
- deterministic flag;
- import/export.

## Data generators
- posts/CPTs;
- terms;
- users test accounts;
- field data;
- relations;
- custom table rows;
- form entries test-only;
- reservations test-only;
- analytics synthetic events;
- media placeholder/provider;
- registered entity generator.

## Generation options
- record count;
- locale;
- random seed;
- date range;
- null/edge-case percentage;
- relationship cardinality;
- unique constraints;
- distribution profiles;
- nested/repeater depth;
- invalid fixture mode only for tests;
- chunk size;
- cleanup ownership tags.

## Safety
- production environment disabled by default;
- explicit privileged override/provider;
- generated records tagged/owned;
- cleanup only owned fixture data;
- preview counts;
- dry-run;
- no real customer/personal data copying by default.

---

# Surface 51 — Content Order

## Order definition
- UUID;
- name/key;
- target source/post type/taxonomy/entity;
- enabled;
- scope;
- status;
- default sort integration;
- capability.

## Ordering modes
- manual global;
- manual per parent;
- manual per taxonomy term;
- manual per relation;
- alphabetical fallback;
- date fallback;
- menu_order/native mapping;
- dedicated indexed order storage;
- registered provider.

## Admin UX
- drag/drop;
- keyboard reorder;
- move top/bottom;
- numeric order edit;
- search/filter before ordering;
- parent/term context;
- pagination/virtualization for large lists;
- unsaved changes;
- bulk reset;
- restore default.

## Runtime/query
- Query Builder order source;
- WP_Query integration;
- terms query integration;
- Listings;
- REST/Ability reorder;
- optimistic revision/CAS;
- concurrent edit conflict;
- missing/deleted item repair;
- performance/index diagnostics.

---

# Surface 54 — User Stores

## Store definition
- UUID;
- name/key;
- store type preset: favorites/bookmarks/likes/dislikes/compare/recently-viewed/custom;
- target object types;
- status;
- user/guest modes;
- visibility/privacy;
- max items;
- ordering;
- duplicate policy;
- retention;
- revisions of definition.

## User/guest identity
- logged-in user storage;
- guest cookie/session;
- anonymous token;
- cookie expiry;
- secure/same-site flags;
- guest→user migration on login;
- merge policy: union/replace/provider;
- cross-device only authenticated;
- privacy export/delete.

## Item record
- object type;
- object ID/ref;
- created timestamp;
- updated/accessed timestamp;
- order;
- metadata/pivot fields optional;
- count aggregate;
- uniqueness;
- source action.

## Behavior
- add;
- remove;
- toggle;
- clear;
- reorder;
- max-items behavior reject/evict oldest/provider;
- recently-viewed auto-add;
- duplicate view update timestamp;
- compare max columns/items;
- login-required policy;
- public aggregate counts;
- anti-abuse/rate limit.

## Frontend/UI
- add/remove/toggle button blueprint;
- active/inactive labels/icons;
- login prompt;
- success/error messages;
- count display;
- user list/listing source;
- empty state;
- compare table provider;
- dashboard integration.

## Query/API
- Query Builder source;
- filter by user/store;
- aggregate popularity;
- REST read/write;
- Ability API;
- permission/ownership;
- import/export under privacy policy;
- Notifications/Analytics events on change.

---

# Shared Wave 6 rules

- analytics/search/AI are permission-aware; indexes/caches must not create unauthorized disclosure;
- AI is an adapter over approved schemas/Abilities, never a privileged bypass;
- fixtures cannot silently seed production;
- rules/transform/query remain declarative and typed rather than arbitrary executable code;
- geo provider secrets stay in Vault and current-location features require privacy/consent handling;
- ordering and user-store mutations are revision/concurrency safe;
- audit retention and redaction are explicit;
- all expensive data operations expose performance/scale diagnostics.
