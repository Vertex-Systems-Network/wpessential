# WPEssential — Dummy Data, Synthetic Dataset & Fixture Generator — Exhaustive Product Specification

Status: **Phase 0 planning / no development authorization**
Date: 2026-08-29

## 1. Purpose

Generate deterministic, realistic, privacy-safe synthetic WordPress and WPEssential data for development, demos, QA, load tests, support reproduction and Solution Blueprint evaluation.

Market research shows strong developer demand for generators covering posts/CPTs, meta, featured images, users, terms, comments and attachments, with modern REST/batching support. WPE must go further: every WPE Data Source, field/relation/status/custom table, Membership/Forms/Workflow-compatible data, adapter-owned domain data, edge/adversarial fixtures, deterministic seeds, cleanup ownership and large-scale profiles must be first-class.

## 2. Module identity

Pro/developer module candidate: **Dummy Data & Fixture Studio**

Navigation:
`WPEssential → Developer Tools → Fixture Studio`
- Overview
- Datasets
- Generators
- Templates
- Scenario Builder
- Volume Profiles
- Generated Runs
- Cleanup
- REST / CLI
- Settings
- Diagnostics

Dependencies:
- Data Source Registry
- Field Storage
- Relations
- Status Manager
- Custom Tables
- Definition Repository
- JobService
- Import/Export
- Media service
- Policy/Capability
- Audit
- Privacy
- Multisite
- AI Prompt Runtime

Optional:
- Solution Blueprint Composer
- WooCommerce/domain adapters
- Analytics for synthetic event streams
- Search indexing benchmark
- Ledger/Reservation foundations

## 3. Core objects

### Dataset Definition
- name/key/version;
- purpose: demo / QA / regression / edge / performance / support reproduction;
- locale(s);
- deterministic seed;
- target site/network;
- required modules/adapters;
- scenario graph;
- cleanup policy;
- privacy class;
- max generated entities/bytes;
- lifecycle/revision.

### Generator Definition
- owner Data Source/entity type;
- field schema;
- value providers;
- constraints;
- relation rules;
- status/time distribution;
- invalid/adversarial mode;
- dependency order.

### Generation Run
- Dataset revision;
- seed;
- target scope;
- planned counts;
- actual counts;
- generated object identities;
- job/checkpoint state;
- errors/skips;
- cleanup ownership marker;
- verification summary.

## 4. Supported entity classes

WordPress native:
- posts/pages/public and private CPTs;
- taxonomies/terms;
- users;
- comments/custom comment types where registered;
- attachments/media;
- options/settings only through owner-safe generator profiles;
- navigation/menu items through registered adapters;
- block content/pattern fixtures where safe.

WPE:
- custom fields/meta;
- relations/pivots;
- statuses/state histories where generator profile supports;
- custom table rows;
- query/listing source data;
- form entries/drafts;
- notification/message fixtures without sending;
- memberships/plans/enrollments as non-billing synthetic facts through owning APIs;
- dashboard/profile/settings data;
- documents/search/analytics/ledger/reservation foundation fixtures after their adapters are certified.

Domain adapters:
- Woo products/variations/customers/orders only through certified WCA fixture abilities;
- no real payment capture/refund/email/SMS/provider call;
- external systems use local mock/stub profiles by default.

## 5. Value provider library

General:
- null/empty;
- bool;
- integers/decimal;
- bounded random number;
- sequence;
- UUID;
- date/time/duration/timezone;
- text/word/sentence/paragraph;
- HTML with allowlisted tags;
- URL/domain/path;
- email using reserved/example domains;
- phone synthetic/non-dialable profile;
- names/persona;
- company/organization;
- address;
- geo coordinates;
- locale/language;
- currency/money through canonical decimal type;
- SKU/reference codes;
- IP from documentation/private ranges only;
- user agent samples;
- regex-generated bounded strings;
- enum/weighted enum;
- file/image placeholder;
- color;
- slug;
- JSON object/array;
- serialized structure for compatibility testing;
- block markup;
- shortcode fixture.

Security:
- never generate real secrets/API keys/payment card credentials;
- emails default to reserved `example.*` domains;
- phone numbers use non-routable/test profiles where jurisdictional rules permit;
- no real personal data source required.

## 6. Field generation

Per field:
- provider;
- fixed vs random;
- required probability;
- null probability;
- invalid probability in explicit negative-test dataset only;
- min/max length/value;
- pattern;
- uniqueness;
- distribution;
- dependencies;
- conditional visibility;
- correlation with other fields;
- locale;
- deterministic sequence;
- reference to previously generated entity;
- computed via F04 deterministic formula where certified.

Respect owning field validators by default. Negative-test mode must be isolated and clearly marked.

## 7. Relation graph generation

Supports:
- one-to-one;
- one-to-many;
- many-to-many;
- pivot fields;
- reciprocal edges;
- minimum/maximum cardinality;
- weighted degree;
- graph depth;
- connected/disconnected clusters;
- orphan fixtures only in explicit negative-test mode;
- cycles where schema permits;
- deterministic graph seed.

Never bypass Relation owning API merely to insert edges.

## 8. Status / lifecycle distributions

- fixed state;
- weighted state distribution;
- state transition history;
- time-in-state distribution;
- overdue/stale records;
- scheduled/future records;
- cancelled/expired states;
- impossible states only in corruption/negative fixture mode and never on production.

## 9. Scenario Builder

Scenario examples:
- “Small blog”;
- “Large editorial site”;
- “CRM with 10k leads”;
- “Membership site with active/grace/expired users”;
- “Woo store with 5k products and synthetic orders”;
- “LMS with students/courses/enrollments”;
- “Property portal”;
- “Broken-link/redirect stress dataset”;
- “Search relevance benchmark”;
- “Reservation concurrency fixture”;
- “Multisite network with 100 sites”.

Scenario graph:
`definitions/dependencies → seed entities → dependent entities → relations → histories → media → derived projections → verification`.

## 10. Volume profiles

Built-ins:
- XS: 10–100 entities;
- S: 1k;
- M: 10k;
- L: 100k;
- XL: 1M;
- custom bounded profile.

Per entity type counts can override profile.

Large runs use JobService with checkpoints, resource keys and backpressure. Estimates show projected rows/files/bytes/jobs before execution.

## 11. Determinism

Every Run stores:
- seed;
- generator revisions;
- locale pack versions;
- provider profile versions;
- input variables;
- target schema fingerprint.

Same compatible definitions + seed should reproduce logically equivalent values/order unless provider declares nondeterminism.

External image/random services are not deterministic and are off by default for reproducibility.

## 12. Media generation

Profiles:
- local generated placeholder image;
- solid/gradient/pattern image;
- SVG only if media security profile allows;
- local bundled sample assets;
- remote placeholder provider opt-in through Safe HTTP with attribution/terms metadata;
- derived sizes through WordPress media pipeline.

Options:
- dimensions/aspect ratio;
- format;
- filesize range;
- alt text/caption/description;
- parent relation;
- featured image probability;
- broken/missing media only in negative-test mode.

## 13. Synthetic PII profiles

- generic anonymous;
- locale-realistic but fictional;
- business contacts;
- international names/addresses;
- accessibility/Unicode edge cases;
- RTL;
- long/short names;
- duplicate-name distinct identities.

Never import scraped people as dummy data.

## 14. Edge/adversarial datasets

Explicit test packs:
- empty strings/nulls;
- max lengths;
- Unicode/emoji/RTL/combining characters;
- HTML/script-looking text stored as data;
- unusual slugs/URLs;
- invalid dates/timezone edges;
- DST transitions;
- serialized/JSON nesting;
- duplicate candidates;
- cardinality boundaries;
- huge text;
- orphan references;
- permission-denied actors;
- Multisite cross-scope attempted references;
- import/version mismatch fixtures.

These are never mixed into ordinary demo datasets without explicit selection.

## 15. Cleanup ownership

Every generated record/file must be traceable to Generation Run using owner-safe metadata/registry/journal.

Cleanup modes:
- preview;
- generated-by-run only;
- generated-by-dataset family;
- age-based generated data;
- detach/retain selected records;
- full verified cleanup.

Cleanup must:
- use owning APIs;
- respect dependencies;
- show external/provider artifacts separately;
- never delete matching real data merely because values look synthetic;
- verify residual generated identities.

## 16. Demo reset / regeneration

Flow:
`identify owned generated data → cleanup dry run → optional backup → cleanup → regenerate with chosen seed/version → verify`.

This is not Reset Manager and cannot wipe unrelated site data.

## 17. AI Prompt

Examples:
- “Real estate CRM ke liye 500 properties, 100 agents aur 5k leads realistic Turkish data ke saath banao.”
- “Search benchmark ke liye 100k posts generate karo, 10% duplicate-like titles aur multilingual content ke saath.”
- “Membership QA dataset banao jisme active, grace, expired aur revoked cases hon—koi email/payment call na ho.”

AI compiles Draft Dataset/Scenario definitions only. It cannot generate or delete data without typed Ability authorization.

## 18. REST / Abilities / MCP / CLI

Abilities:
- list generator capabilities;
- create Dataset Draft;
- estimate Run;
- validate dependencies;
- start authorized Run;
- pause/resume/cancel;
- verify;
- preview cleanup;
- cleanup generated run;
- export Dataset definition.

REST/CLI batching supported where certified. MCP opt-in.

## 19. Multisite

- per-site Dataset ownership;
- network template can instantiate per-site datasets;
- network user generation has explicit global-vs-site role semantics;
- no cross-site relation unless owning schema supports it;
- site deletion interrupts/fences active jobs;
- clone can regenerate identities or preserve fixture seed per explicit profile;
- 10/100/1k/10k-site evidence profiles.

## 20. Failure / recovery

- partial Run → durable checkpoint + owned generated identities;
- plugin/module disabled → pause/degraded, no untracked generation;
- unique collision → deterministic retry or recorded failure;
- missing dependency → block dependent generator;
- media provider unavailable → local fallback only if profile permits;
- cleanup failure → partial truthful state + retry plan;
- schema drift → halt before unsafe continuation.

## 21. Security / privacy

- non-production warning/guard profiles;
- optionally disallow production environments unless explicit privileged override;
- generated admin users do not get known/default passwords; authentication test users use controlled fixture credentials in Vault/test environment only;
- no secrets;
- no real payment/provider calls;
- no public unauthenticated generation endpoint;
- quotas/rate limits;
- audit all Run/cleanup actions.

## 22. Evidence namespace

Future protocol: `DMY-001…DMY-176`, executed 0 until development consent.

Evidence groups cover native entities, custom fields/relations/tables, deterministic values, localization/PII safety, lifecycle/status, media, scenarios, volume/load, JobService/checkpoints, cleanup ownership, REST/CLI/Abilities/MCP, Multisite, adapters, failures/recovery and adversarial security.

## 23. MUST NOT

- delete non-generated data during cleanup;
- call real payment/email/SMS/provider operations by default;
- create real secrets;
- bypass owning module validators/APIs;
- generate known insecure admin credentials;
- scrape real people for fixture data;
- claim deterministic reproduction when external nondeterministic providers are involved;
- allow AI to execute data generation/deletion without authorization.
