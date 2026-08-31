# WPEssential — Universal Foundations Exhaustive Product Specification

Status: **Phase 0 expanded-scope exhaustive planning / no development authorization**  
Date: 2026-08-28

## 1. Purpose

This file takes the 12 proposed reusable foundations from `FOUNDATIONAL-MODULE-GAP-PLAN.md` from architecture-level candidates to screen/option-level product specifications.

These modules are proposed because many unrelated Solution Blueprints need the same semantics. They must reuse the current Definition/Policy/Ability/Event/Workflow/Job/Vault/Audit/Import-Export/Asset/Cache/Rate-Limit architecture and must not become private mini-platforms.

### Common lifecycle grammar

Unless a section overrides it, every definition supports:
- draft;
- published/active;
- paused/disabled where runtime semantics exist;
- archived;
- revisions;
- duplicate;
- preview/test/simulation where safe;
- export/import;
- dependency/Used-by view;
- audit history;
- capability/Policy controls;
- health/degraded state;
- module disable/data preservation;
- Pro expiry safe read-only/degraded behavior.

No option below is implemented or runtime-verified.

---

# F01 — Solution Blueprint & Application Composer

## Goal

Compose complete applications from canonical WPE definitions without copying runtime engines or silently overwriting an existing site.

## Admin navigation

`WPEssential → Solutions`
- Library
- My Blueprints
- Installed Solutions
- Install / Import
- Domain Packs
- Patterns
- Compatibility
- Settings

## Library screen

Columns:
- name;
- key;
- version;
- domain;
- primary pattern;
- source/curator;
- required modules;
- required foundations;
- required adapters;
- compatibility;
- installed state;
- update state;
- risk class;
- last reviewed.

Filters:
- domain;
- pattern;
- source;
- free/pro requirements;
- installed/not installed;
- compatibility;
- local/official/third-party;
- risk;
- actor model;
- experience profile.

Row actions:
- Preview;
- Inspect Manifest;
- Install;
- Duplicate/Fork;
- Export;
- Compare Version;
- View Dependencies;
- Archive local blueprint.

## Blueprint editor tabs

### Identity
- name;
- stable key;
- version;
- description;
- domain pack;
- primary/secondary patterns;
- tags;
- intended users;
- organization scale;
- icon/preview media;
- documentation links;
- source/provenance.

### Requirements
- WPE minimum/maximum compatible versions;
- required modules;
- optional modules;
- required foundation modules;
- required domain/provider adapters;
- minimum adapter certification levels;
- external authorities;
- WordPress/PHP compatibility constraints once certified;
- Multisite support class;
- incompatible modules/blueprints;
- environment requirements.

### Components
Selectable referenced definitions grouped by owner:
- CPTs/taxonomies;
- field groups;
- relations;
- statuses;
- custom tables;
- queries;
- listings/components;
- admin columns;
- settings pages;
- dashboards/routes;
- profiles;
- membership plans/policies;
- forms;
- workflows;
- cron schedules;
- notifications;
- emails;
- chats/conversation policies;
- REST endpoints;
- webhooks/connections metadata without secrets;
- imports/syncs;
- document/search/analytics/etc. definitions from expanded foundations.

For each component:
- required/optional;
- install as new / bind existing / choose during install;
- exposed install variables;
- upgrade ownership;
- deletion behavior;
- dependency links.

### Variables
- key;
- label;
- description;
- type;
- required;
- default;
- allowed values;
- secret yes/no (must bind Vault, never embed value);
- environment-bound yes/no;
- install-time only vs editable later;
- validation;
- dependency/visibility rules.

### Roles & Policies
- required roles;
- capability additions;
- resource policies;
- role mapping on existing site;
- create role vs bind role;
- anti-lockout impact;
- approval policies;
- guest/public surfaces.

### Navigation & Experience
- admin menus;
- frontend routes;
- dashboard shells;
- public pages/listings;
- placement slots;
- builder adapters;
- route collision policy;
- page creation vs bind-existing;
- responsive/accessibility requirements.

### Data & Migration
- initial schema operations;
- seed/sample data: none / demo-only / production-safe starter;
- mapping from existing post types/tables/roles;
- data import references;
- destructive migration class;
- backup requirement;
- rollback class;
- expected record volumes.

### Installation Plan
- dependency order;
- parallel-safe groups;
- definition create/update/bind operations;
- routes/pages;
- role/capability changes;
- migrations;
- indexes;
- adapters;
- health checks;
- activation order.

### Upgrade
- semver policy;
- migrations;
- added/removed components;
- permission changes;
- route changes;
- deprecated variables;
- drift conflict strategy;
- operator review-required changes.

### AI
- whether AI may draft variations;
- editable inference fields;
- fixed safety constraints;
- evidence/source requirements;
- generated blueprint evaluation checklist.

## Install wizard

1. Select Blueprint/version.
2. Compatibility scan.
3. Dependency/module/adaptor scan.
4. Existing-site inventory.
5. Collision/mapping screen.
6. Variable inputs.
7. Role/policy mapping.
8. Routes/pages/navigation mapping.
9. Data/migration preview.
10. Security/privacy impact.
11. Dry-run plan + fingerprint.
12. Backup/recovery requirement if applicable.
13. Install definitions as Draft by default.
14. Validation/health report.
15. Select components to Publish/Enable.
16. Completion report + exportable installation record.

## Installed Solution screen

- health;
- blueprint version;
- installed component count;
- customized/drifted count;
- missing dependencies;
- degraded components;
- upstream update;
- compare installed/upstream;
- upgrade dry run;
- detach/fork;
- disable runtime components;
- uninstall plan;
- export installation mapping.

## Conflict rules

Existing same stable definition ID:
- exact same revision → reuse;
- compatible known earlier revision → offer update;
- locally changed → show three-way diff / bind/fork/skip;
- unrelated same slug/key → never guess; require mapping/rename;
- third-party owned WordPress object → adapter/inspect semantics only.

## MUST NOT
- silently replace existing definitions;
- store credentials in blueprint packages;
- create hidden private fields/workflows;
- call “installed” if critical required component failed;
- treat partial install as rollback success unless reversal was verified.

---

# F02 — Analytics, Event Tracking & Journey Intelligence

## Goal

Provide durable behavioral/product/application analytics distinct from the operational Event Bus and Audit Log.

## Navigation

`Analytics`
- Overview
- Event Catalog
- Live/Recent Events
- Metrics
- Funnels
- Cohorts
- Journeys
- Attribution
- Dashboards
- Alerts
- Data Quality
- Tracking & Consent
- Retention

## Event Definition editor

### Identity
- event key;
- label;
- category;
- schema version;
- description;
- source owner;
- lifecycle status.

### Schema
- properties;
- type;
- required;
- enum/constraints;
- dimensions vs measure eligibility;
- entity references;
- PII class;
- sensitive/secret prohibited flag;
- client-settable vs server-only;
- max size/cardinality.

### Collection
- server event sources;
- client/browser collection allowed;
- REST/SDK source;
- imported historical source;
- sample rate;
- dedupe identity/window;
- event time vs received time;
- late event tolerance;
- bot/internal-user handling;
- consent category.

### Retention/privacy
- raw retention;
- aggregate retention;
- anonymous retention;
- user-linked retention;
- IP capture: off/default, truncated/hashed only if accepted use case;
- user-agent granularity;
- delete/anonymize linkage;
- export policy.

## Tracking settings

- first-party session cookie enabled;
- cookie name/prefix;
- anonymous ID rotation;
- session timeout;
- consent dependency;
- login identity-link behavior;
- logout behavior;
- cross-device link only after authoritative authentication;
- referrer;
- UTM fields;
- landing page;
- device class;
- locale/timezone;
- page/entity context;
- excluded roles/users/IPs;
- admin tracking off by default;
- debug mode;
- client payload max;
- endpoint rate limits.

## Metric editor

- name/key;
- unit;
- aggregation: count/sum/avg/min/max/distinct/rate/ratio/percentile where backend supports;
- source event/entity;
- value field;
- filters;
- groupable dimensions;
- numerator/denominator;
- time grain;
- timezone;
- window;
- late-data correction;
- freshness target;
- missing data policy;
- currency normalization profile;
- visibility Policy;
- caching/materialization;
- data completeness warning.

## Funnel editor

- ordered/unordered;
- steps;
- step filters;
- same-user/session/entity requirement;
- conversion window;
- entry mode;
- repeat occurrences;
- optional steps;
- exclusion/drop conditions;
- breakdown dimensions;
- segment filter;
- historical range;
- privacy minimum cohort threshold;
- attribution links.

## Cohort editor

- cohort event/condition;
- cohort date;
- return event;
- time bucket;
- retention windows;
- segment/dimension;
- inclusion/exclusion;
- static snapshot vs dynamic cohort.

## Journey/path exploration

- start/end events;
- max depth;
- max branching;
- event groups;
- include/exclude noise events;
- session/user scope;
- path timeout;
- sampling limits;
- protected dimension rules.

## Attribution

Initial planning profiles:
- first touch;
- last touch;
- last non-direct;
- linear;
- position-based;
- custom deterministic weights via F04;
- experiment-based incremental lift kept separate.

Options:
- attribution window;
- channel/source mapping;
- direct handling;
- cross-session authenticated linking;
- refund/cancellation adjustment where consumer supplies events;
- explain touch sequence.

Do not label modeled attribution as causal proof.

## Data Quality

- unknown event keys;
- schema violations;
- dropped events;
- duplicates;
- late events;
- high-cardinality dimensions;
- missing identity;
- consent rejected events;
- source drift;
- volume anomalies;
- freshness lag.

## Storage profiles

Physical backend remains evidence-gated. Product options expose capability, not vendor-specific assumptions:
- local small/medium profile;
- WPE custom-table analytics profile;
- external analytics warehouse adapter profile;
- retention/downsampling;
- aggregate materialization;
- export.

## MUST NOT
- reuse Audit Log as analytics warehouse;
- permit client events to create authority;
- collect secrets;
- claim causation from ordinary attribution/correlation;
- expose small sensitive cohorts contrary to privacy policy.

---

# F03 — Search & Indexing Engine

## Navigation

`Data → Search & Indexes`
- Indexes
- Synonyms
- Ranking Profiles
- Search Rules
- Logs & Zero Results
- Backends
- Health

## Index editor

### Identity/source
- name/key;
- source Data Source/Query;
- entity identity field;
- site/network scope;
- backend profile;
- index alias/version;
- status.

### Fields
Per field:
- source path;
- index name;
- text/keyword/numeric/date/bool/geo/vector-if-certified;
- searchable;
- retrievable;
- filterable;
- facetable;
- sortable;
- boost weight;
- stored/display snippet;
- security classification;
- language/analyzer;
- normalizer.

### Text analysis
- locale/language;
- tokenizer profile;
- lowercase/accent normalization;
- stemming;
- stopwords;
- synonym set;
- n-gram/prefix autocomplete;
- typo tolerance;
- fuzziness limits;
- exact phrase behavior.

### Relevance
- field weights;
- recency boost;
- popularity boost from approved metric;
- manual pins;
- bury rules;
- exact-match boost;
- F04 ranking formula profile;
- deterministic tie-break.

### Indexing
- full build;
- incremental event-triggered;
- scheduled reconcile;
- batch size;
- concurrency;
- retry;
- tombstone/delete handling;
- index generation swap;
- stale-source handling;
- pause;
- rebuild.

### Security
- source Policy projection;
- public-only index vs protected index;
- runtime reauthorization requirement;
- field redaction;
- tenant/site ownership;
- search cache identity dimensions.

## Search query profile

- fields;
- default operator;
- minimum characters;
- autosuggest;
- typo tolerance;
- filters;
- facets;
- sort modes;
- page size/max;
- cursor/pagination;
- highlight;
- empty query policy;
- zero-result fallback;
- pinned/redirect rules;
- protected count policy.

## Synonym sets
- one-way/two-way;
- locale;
- effective dates;
- import/export;
- conflict preview;
- usage analytics.

## Search rules
- query exact/pattern after bounded validation;
- audience/context;
- boost/bury/pin;
- redirect;
- schedule;
- priority;
- campaign binding;
- explain trace.

## Backends
Adapters declare:
- local capability;
- full-text features;
- facets;
- typo tolerance;
- geo;
- vector;
- pagination;
- consistency;
- index limit;
- auth/Vault;
- health;
- certification.

No generic promise that every backend supports every feature.

## MUST NOT
- let stale index leak data revoked at source;
- expose a result solely because document ID exists;
- accept arbitrary backend query language from public user input;
- treat vector similarity as factual correctness.

---

# F04 — Decision, Formula, Scoring & Ranking Studio

## Navigation

`Automation → Decisions`
- Formulas
- Scorecards
- Decision Tables
- Ranking Profiles
- Lookup Tables
- Simulations
- Evaluation Logs

## Formula editor

### Identity
- name/key;
- purpose;
- output type;
- output unit/currency;
- effective dates;
- version.

### Inputs
- key;
- typed source;
- constant/context/DVR/entity/query aggregate;
- required;
- default/null policy;
- min/max;
- unit;
- visibility in explanation.

### Expression
Only registered typed functions/operators:
- arithmetic;
- min/max/clamp;
- round/floor/ceil with mode;
- percentages;
- date/duration;
- bounded string classification helpers where safe;
- condition/if;
- lookup;
- aggregates passed as typed inputs;
- unit conversion via S05;
- no eval/PHP/JS.

### Numeric correctness
- decimal precision;
- rounding mode;
- intermediate precision;
- divide-by-zero policy;
- overflow bounds;
- currency mismatch handling;
- unit mismatch rejected.

## Scorecard
- factors;
- weight;
- factor input;
- normalization profile;
- missing factor policy;
- score min/max;
- thresholds/bands;
- confidence/data-completeness field;
- explain contribution;
- effective dates.

## Decision Table
- input columns;
- typed predicates;
- rows;
- priority;
- first-match/all-match/most-specific accepted profile;
- output columns;
- overlap/conflict diagnostics;
- unreachable row detection;
- default/no-match result.

## Ranking
- candidate Query;
- eligibility Policy/conditions;
- score formula;
- tie-breakers;
- diversity constraints if accepted;
- exclusions;
- caps;
- manual pins;
- explanation;
- deterministic/random experiment binding separately.

## Simulation
- sample manual inputs;
- saved fixtures;
- historical entity IDs read-only;
- batch sample query;
- compare versions;
- sensitivity ranges;
- edge/boundary cases;
- output distribution;
- no-write guarantee.

## Permissions
Separate:
- view definition;
- edit draft;
- publish financial/risk formula;
- simulate protected data;
- view evaluation logs;
- manual override consumer action.

## MUST NOT
- let score authorize without Policy;
- hide data completeness for AI/risk recommendations;
- evaluate arbitrary executable code;
- use binary floating point as canonical money semantics.

---

# F05 — Ledger, Balance & Movement Engine

## Navigation

`Data → Ledgers`
- Ledgers
- Accounts
- Transactions
- Holds/Reservations
- Reconciliation
- Statements
- Adjustments
- Health

## Ledger definition

### Identity
- name/key;
- domain: generic/value/inventory/loyalty/quota/time/commission/etc.;
- posting model: single-account delta / balanced multi-posting profile;
- unit/currency;
- precision;
- scope;
- status.

### Accounts
- account classes;
- allowed owner entity types;
- one-per-owner vs multiple;
- normal balance semantics if applicable;
- negative balance allowed;
- min/max balance;
- close/freeze policy;
- opening balance requires posting/import evidence.

### Transaction types
Per type:
- key/label;
- debit/credit/delta pattern;
- required source reference;
- allowed actor/Ability;
- input fields;
- idempotency scope;
- approval threshold;
- hold required;
- reversible yes/no;
- reversal type;
- expiry lot behavior;
- notification/event.

### Holds
- amount/quantity;
- account;
- reason/source;
- TTL/expires at;
- confirm/capture action;
- release action;
- partial capture;
- over-capture blocked;
- expired hold cleanup/reconcile.

### Expiry lots
For points/credits where required:
- FIFO/explicit lot allocation;
- earned date;
- expiry date;
- grace;
- consume order;
- reversal restoration semantics.

### Balance projection
- synchronous read model candidate;
- snapshot interval;
- rebuild from postings;
- cache;
- stale indicator;
- balance version;
- never primary writable truth.

## Manual adjustment
- account;
- transaction type;
- amount;
- reason mandatory;
- source ticket/reference;
- approval/re-auth threshold;
- preview resulting balance;
- compensating entry, never row edit.

## Reconciliation
- expected source selection;
- date window;
- ledger account;
- external/provider/reference totals;
- unmatched transactions;
- duplicate refs;
- balance mismatch;
- resolution classification;
- adjustment plan;
- approval;
- audit report.

## Statements
- date range;
- opening/closing balance;
- postings;
- source links;
- unit/currency;
- export;
- privacy/Policy.

## Concurrency
Product contract requires:
- atomic posting/idempotency;
- balance/version conflict handling;
- race-safe holds;
- immutable committed entries;
- unknown external outcome marked pending/reconciliation, not guessed.

## MUST NOT
- editable balance field as truth;
- hard-delete committed financial-like postings through ordinary UI;
- silently convert units/currencies;
- reuse one ledger across sites without explicit network ownership.

---

# F06 — Resource Scheduling, Availability & Reservation Engine

## Navigation

`Scheduling`
- Resources
- Resource Groups
- Calendars
- Availability Rules
- Reservations
- Holds
- Waitlists
- Capacity
- Calendar Integrations
- Settings/Health

## Resource editor
- name/key;
- type/category;
- owner/provider;
- location;
- timezone;
- capacity;
- exclusive vs shared;
- skill/capability tags;
- parent/group;
- bookable states;
- min/max duration;
- buffer before/after;
- lead time;
- booking horizon;
- price reference only, not own commerce pricing;
- metadata/fields;
- visibility/Policy.

## Availability rules
- weekly recurrence;
- date range;
- day/time intervals;
- timezone;
- priority;
- include/exclude;
- blackout;
- holiday/calendar source;
- capacity override;
- resource/group scope;
- actor/service context;
- effective dates.

## Reservation type
- resource requirements;
- single vs multiple resources;
- capacity units;
- duration fixed/variable;
- start interval/grid;
- attendee count;
- approval required;
- form binding;
- payment/commerce adapter binding;
- hold TTL;
- cancellation window;
- reschedule window/count;
- no-show policy;
- waitlist;
- reminders;
- check-in/out;
- recurrence allowed;
- buffer.

## Availability search
- date/time range;
- timezone;
- resource/query filter;
- location;
- required capacity;
- duration;
- combine resources;
- nearest alternatives;
- max result range;
- caching with reservation-version invalidation.

## Atomic hold flow
- requested slot/resources;
- capacity version;
- hold token/reference;
- TTL;
- principal/session binding;
- conversion to confirmed reservation;
- release;
- expiry;
- duplicate request/idempotency.

## Waitlist
- reservation type;
- date/range preference;
- resource preference;
- party size;
- priority policy;
- notification expiry;
- offer/hold window;
- auto-book only if separately policy-authorized.

## External calendars
- one-way busy import;
- two-way event sync only through F10-certified profile;
- event mapping;
- calendar selection;
- privacy fields;
- refresh cursor;
- duplicate/conflict;
- provider failure;
- external calendar does not automatically own WPE reservation truth.

## MUST NOT
- use WP-Cron alone as capacity lock;
- confirm beyond capacity due stale cache;
- promise exact external calendar synchronization without certification;
- expose private booking details through public availability responses.

---

# F07 — Experience Placement & Personalization Manager

## Navigation

`Experience → Placements`
- Slots
- Experiences
- Rules
- Frequency
- Preview
- Adapters
- Health

## Placement Slot
- key/name;
- surface: frontend/admin/portal/email-reference/other adapter;
- adapter owner;
- WordPress hook/block/template context;
- supported render modes;
- replacement allowed yes/no;
- allowed component types;
- data context schema;
- asset zone;
- default fallback;
- cache profile;
- accessibility notes.

## Experience editor
- name/key;
- component/listing/block/template binding;
- content/data inputs;
- design tokens;
- responsive settings;
- schedule;
- status;
- locale variants;
- audience/context rules;
- priority;
- conflict/stacking;
- fallback;
- experiment link;
- campaign/source link;
- frequency profile;
- dismissible;
- tracking event keys.

## Context conditions
- page/route;
- entity/post type/taxonomy;
- principal role/capability/segment;
- authenticated/guest;
- locale;
- device class;
- referrer/campaign only when F02 available;
- market/location only through S04/F11;
- cart/order only through domain adapter;
- schedule;
- custom registered context key.

## Frequency caps
- per session;
- per anonymous visitor if consent/storage permits;
- per user;
- per experience/campaign;
- N per duration;
- minimum gap;
- dismiss TTL/permanent;
- conversion suppression event;
- cross-device only authenticated.

## Conflict resolution
- slot capacity;
- exclusive vs stackable;
- priority;
- campaign override;
- experiment assignment;
- most-specific conditions;
- deterministic tie-break;
- diagnostics explaining winner/loser.

## Preview
- route/page;
- user/role/segment context;
- locale/device;
- time override;
- data entity;
- show matched/failed rules;
- asset preview;
- no production frequency mutation.

## MUST NOT
- visibility equal authorization;
- inject arbitrary raw PHP/JS;
- globally load assets for inactive experiences;
- mutate third-party builder private document structures without certified adapter.

---

# F08 — Experimentation & Feature Rollout Manager

## Navigation

`Experience → Experiments`
- Experiments
- Rollouts
- Metrics
- Mutual Exclusion
- Results
- Quality/Health

## Experiment editor

### Identity/hypothesis
- name/key;
- owner;
- hypothesis;
- description;
- status;
- start/end;
- review date.

### Subject/assignment
- unit: visitor/user/session/company/site/other stable subject;
- eligibility Query/conditions;
- exclusions;
- assignment key;
- deterministic hash namespace;
- sticky duration;
- re-randomization policy prohibited by default;
- bot/internal/test exclusions.

### Variants
- control + N variants;
- weights;
- component/definition override reference;
- allowed difference scope;
- fallback;
- compatibility validation;
- sum allocation validation.

### Metrics
- primary metric;
- secondary;
- guardrails;
- event/metric definitions from F02;
- attribution window;
- minimum practical duration;
- minimum sample guidance;
- segments/breakdowns;
- multiple-comparison methodology remains evidence/research scoped.

### Exposure
- exposure event;
- assignment vs actual exposure distinction;
- one exposure per configured unit/window;
- dedupe;
- cross-device linking policy.

### Rollout
- percentage;
- staged schedule;
- hold points;
- kill switch;
- health/error guardrail;
- segment restrictions;
- promote variant to normal definition flow.

## Result states
- collecting;
- insufficient data;
- directional signal;
- review required;
- stopped;
- completed;
- invalidated (instrumentation/assignment issue).

UI must not call every numeric difference a winner.

## Safety
Experiments cannot randomize:
- authentication requirements;
- core security checks;
- destructive confirmation;
- legally required consent;
- access policy in a way that exposes otherwise protected data.

## MUST NOT
- claim causal lift without valid randomized/exposure evidence;
- let cache mix variants across assignment units;
- permit overlapping experiments when mutual-exclusion rule prohibits it.

---

# F09 — Documents, Records & Template Generation

## Navigation

`Documents`
- Templates
- Generated Documents
- Numbering
- Renderers
- Delivery
- Protected Storage
- Settings

## Template editor

### Identity
- name/key;
- document class;
- version;
- locale;
- status;
- immutable-issued profile yes/no.

### Page/layout
- page size;
- orientation;
- margins;
- header/footer;
- page numbers;
- background;
- columns/sections;
- keep-together/page-break hints;
- print CSS profile for HTML renderer;
- renderer capability warnings.

### Components
- text;
- heading;
- image/logo;
- dynamic field/token;
- table/repeater;
- list;
- divider;
- spacer;
- address;
- barcode/QR through adapter if certified;
- signature reference block (not legal signature by itself);
- conditional block;
- page break;
- registered document component.

### Data binding
- primary entity;
- additional Data Sources/Queries;
- context variables;
- relation traversals;
- formatting;
- locale;
- null fallback;
- protected field Policy.

### Numbering
- sequence key;
- prefix/suffix;
- year/month tokens;
- padding;
- site/network scope;
- gap policy;
- reserve-on-draft vs issue-on-final;
- uniqueness/concurrency.

### Output
- HTML;
- PDF renderer adapter;
- image/other future certified renderer;
- filename template;
- MIME;
- compression;
- PDF metadata;
- font profile/licensing;
- accessibility/tagging capability declared per renderer.

### Storage/access
- media attachment public;
- Protected Asset private;
- external storage adapter;
- access Policy;
- expiry;
- download disposition;
- watermark via Media module;
- retention.

### Issuance
- draft render;
- approval required;
- issued immutable snapshot;
- supersede/revoke document state;
- regenerate policy;
- source revision fingerprint;
- checksum;
- audit.

### Delivery
- download;
- portal;
- email attachment/link;
- webhook/storage;
- external e-sign adapter;
- delivery attempts separate from document truth.

## MUST NOT
- claim WPE image/name block is legally binding e-signature;
- expose private documents via ordinary public uploads URL;
- silently regenerate an immutable issued record after source data changes.

---

# F10 — Data Sync, ETL & Integration Pipelines

## Navigation

`Integrations → Sync`
- Sync Definitions
- Mappings
- Runs
- Conflicts
- Dead Letters
- Cursors
- Schedules
- Health

## Sync editor

### Identity/direction
- name/key;
- source Connection/Data Source;
- destination Connection/Data Source;
- one-way/bidirectional;
- authoritative field owner matrix;
- environment;
- status.

### Source selection
- entity/resource;
- Query/filter;
- fields;
- changed-since capability;
- source cursor type;
- pagination;
- deleted/tombstone support;
- snapshot fallback.

### Destination
- operation: create/update/upsert/delete/archive;
- key/match fields;
- field allowlist;
- batch endpoint;
- idempotency;
- external operation status/reconcile capability.

### Mapping
Per field:
- source path;
- destination path;
- type;
- required;
- transform through allowlisted typed F04/DVR function;
- lookup map;
- default;
- null behavior;
- truncate/error;
- privacy classification.

### Cursors/checkpoints
- cursor/token;
- timestamp/version/high-water mark;
- per-page checkpoint;
- last completed;
- current run;
- reset cursor action with impact warning;
- replay window.

### Conflict policy
- source wins;
- destination wins;
- newest only where reliable comparable version exists;
- field-owner based;
- manual conflict;
- custom registered resolver;
- never silent generic last-write-wins default for bidirectional.

### Delete policy
- ignore source delete;
- soft archive;
- propagate delete only if both sides certified and explicit;
- quarantine for approval;
- protected records exclusions.

### Schedule/trigger
- manual;
- recurring Job;
- Event Bus trigger;
- inbound webhook;
- after import;
- concurrency limit;
- no overlapping runs / partitioning policy.

### Failure/retry
- item validation failure;
- rate limit;
- network;
- external unknown outcome;
- auth expired;
- mapping drift;
- destination conflict;
- dead letter;
- retry item/batch/run;
- reconciliation.

## Dry run
- sample rows;
- planned create/update/skip/delete counts;
- mapping errors;
- conflicts;
- permission failures;
- API estimate;
- no external writes.

## MUST NOT
- store secrets in sync mappings;
- reset cursor silently;
- retry non-idempotent unknown mutations blindly;
- claim bidirectional support without conflict ownership semantics.

---

# F11 — Geospatial, Location & Territory Engine

## Navigation

`Data → Locations`
- Locations
- Territories/Zones
- Geocoders
- Maps
- Queries
- Health/Quota

## Location schema
- address lines;
- locality/city;
- region/state;
- postal code;
- country;
- normalized address;
- latitude/longitude;
- precision/source;
- timezone;
- provider place ID;
- confidence;
- geocoded at;
- user-confirmed flag;
- privacy precision profile.

## Location field options
- manual address;
- autocomplete adapter;
- map pin;
- coordinates;
- require country;
- allowed countries;
- store normalized vs display address;
- geocode on save;
- manual override;
- reverse geocode;
- exact/coarse location;
- map display permission.

## Territory/zone editor
- name/key;
- type: country/region/postcode list/radius/polygon/multipolygon/imported geometry/custom resolver;
- priority;
- parent territory;
- inclusions/exclusions;
- effective dates;
- owner/team;
- data attachment;
- map preview;
- overlap diagnostics.

## Geo Query
- radius;
- units;
- nearest N;
- bounding box;
- within territory;
- intersects territory where supported;
- distance sort;
- location source field;
- max radius/result limits;
- Policy constraints.

## Provider adapter
- geocode;
- reverse geocode;
- autocomplete;
- map tiles/display;
- timezone;
- route/distance matrix kept separate capability;
- credentials Vault;
- allowed countries;
- rate/quota;
- cache TTL;
- provider terms/attribution requirements;
- data retention restrictions;
- certification.

## Privacy
- exact location;
- rounded coordinates;
- city/region only;
- public/private fields;
- role-specific precision;
- analytics geo aggregation threshold;
- history retention.

## MUST NOT
- expose exact home/sensitive locations because a map widget is public;
- infer authoritative postal/legal address from geocoder only;
- bypass SSRF/Connection policy for custom geocoder endpoints.

---

# F12 — AI Gateway, Knowledge & Copilot Studio

## Navigation

`AI`
- Overview
- Providers & Models
- AI Tasks
- Prompt/Instruction Templates
- Knowledge Sources
- Retrieval Profiles
- Copilots
- Evaluations
- Usage & Budgets
- Runs/Audit
- Privacy & Data Controls

## Provider Connection
- provider adapter;
- connection/Vault credentials;
- account/project region;
- endpoint fixed/provider-defined;
- enabled models fetched/declared;
- health/test;
- rate limits;
- data-retention provider profile;
- training-use provider profile;
- region/data residency informational;
- certification status;
- fallback eligibility.

## Model Profile
- stable local profile key;
- provider/model/version;
- modalities;
- context limits;
- structured output capability;
- tool/function capability;
- streaming;
- temperature/top-p only where supported;
- max output;
- timeout;
- cost metadata;
- allowed task classes;
- sensitivity ceiling;
- default fallback.

## AI Task Definition

### Identity
- name/key;
- purpose;
- owner module;
- task class: READ_ONLY_INSIGHT / STRUCTURED_DRAFT / APPROVAL_REQUIRED_ACTION / policy-preauthorized after separate acceptance;
- input schema;
- output JSON schema;
- version.

### Context
- principal context;
- allowed entity/Data Sources;
- Queries;
- fields allowlist;
- relation depth;
- max records/tokens;
- PII classes allowed;
- prohibited fields;
- secrets always prohibited unless narrowly scoped provider action never as prompt text;
- fresh vs cached data.

### Knowledge/retrieval
- F03 index;
- source documents/entities;
- chunk profile;
- filters/Policy;
- top K;
- rerank adapter if certified;
- citation/evidence required;
- no-answer threshold;
- stale source handling.

### Instructions
- system/product policy template;
- task instruction;
- user input slot;
- output schema;
- style rules;
- forbidden behavior;
- prompt version;
- injection defense strategy at application level;
- untrusted retrieved content isolation.

### Tools/Abilities
- allowed WordPress/WPE Ability keys;
- read vs write;
- input mapping;
- max calls;
- approval before call;
- dry-run required;
- destructive abilities excluded by default;
- tool output schema/size.

### Budget/rate
- max requests per user/role/site;
- token budget;
- monetary budget where provider supplies pricing metadata;
- daily/monthly cap;
- concurrency;
- timeout;
- fallback provider/model;
- stop-on-budget.

### Output validation
- JSON schema;
- source citation validation;
- referenced IDs existence;
- Policy recheck;
- hallucinated Ability/field rejected;
- prohibited content/data class filter where applicable;
- deterministic owning-module validation.

## Knowledge Source
- source type: posts/docs/custom tables/external synced data/etc.;
- Query/filter;
- fields;
- access Policy;
- indexing profile;
- update trigger;
- retention;
- citation metadata;
- deletion propagation.

## Copilot Definition
- target surfaces;
- persona/help scope;
- available tasks;
- command suggestions;
- context sources;
- conversation memory scope;
- session vs persistent history;
- user-controlled clear;
- action confirmation policy;
- response evidence UI.

## Evaluations
- dataset;
- case inputs;
- expected schema/criteria;
- deterministic checks;
- human rubric;
- safety cases;
- prompt/model comparison;
- regression threshold;
- cost/latency;
- versioned results.

## Run log
Privacy-safe metadata:
- task/version;
- provider/model profile;
- actor/site;
- timing;
- token/cost metrics if known;
- result status;
- evidence refs;
- validation failures;
- proposed Ability calls;
- approval/result;
- no raw prompt/PII retention by default unless explicit debugging profile and privacy policy.

## AI write flow

`Intent → Authenticate/Policy → Build Minimized Context → Retrieve Authorized Evidence → Model Structured Draft → Schema/Evidence Validate → Owning Module Validate → Simulation/Diff → Approval → Deterministic Ability → Audit Result`

## MUST NOT
- arbitrary PHP/SQL/shell as normal AI tool;
- bypass owner module validation;
- send Vault secret values as model context;
- grant model greater access than principal;
- silently auto-execute high-impact writes;
- let prompt text alter immutable platform security instructions or provider allowlist;
- present probabilistic recommendation as authoritative fact when evidence is incomplete.

---

# Common exhaustive requirements for F01–F12

Every module must additionally define before implementation:
- list/search/sort/filter/pagination behavior;
- capability keys and resource Policy;
- Abilities with JSON schemas;
- REST exposure defaults;
- module dependencies;
- Multisite ownership;
- exact Definition Repository integration;
- import/export package representation;
- Definition versions/migrations;
- cache dependencies/invalidation;
- Rate Limit consumers;
- Audit event classes;
- privacy exporter/eraser behavior;
- Backup/Restore behavior;
- disable/expiry/uninstall behavior;
- asset manifests;
- keyboard/accessibility;
- empty/loading/error/degraded states;
- provider adapter certification where applicable;
- scale evidence matrix;
- malicious/boundary/concurrency/recovery tests.

## Expanded-scope maturity truth

This specification makes the **product-option surface** for F01–F12 materially detailed, but it does not establish physical schemas, runtime compatibility, provider certification or implementation readiness.

Until canonical product governance is synchronized, report:
- original WPE surfaces: **31/31 Exhaustive**;
- universal foundation candidates: **12 option-detailed / runtime-unverified / unauthorized**;
- shared-service enhancements: **6 planned contracts**;
- curated Solution Blueprints: **160 planned**;
- raw validated Blueprint addressable space: **268,800 primary combinations before filtering**;
- development authorization: **not granted**.

## Development gate

No module code, DB schema, index, ledger, booking lock, experiment assignment, PDF renderer, sync run, geocoder request or AI provider call is authorized by this specification.