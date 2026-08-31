# WPEssential — Atomic Option Contracts Wave 4: Integrations, Data Movement & Deployment

Status: **atomic option inventory**  
Snapshot: **2026-08-31**  
Surfaces: **22 REST API, 23 Connections, 26 Import/Export, 41 Sync, 44 Redirects, 45 Transform, 55 Staging**.

---

# Surface 22 — REST API Builder

## Endpoint identity
- UUID;
- name/key;
- namespace;
- version;
- route/path;
- enabled/status;
- methods;
- description;
- tags/group;
- clone/revisions/import/export.

## Methods
Per method GET/POST/PUT/PATCH/DELETE/custom registered method:
- enabled;
- request schema;
- response schema;
- permission policy;
- rate limit;
- cache policy;
- idempotency;
- audit class;
- timeout for provider-backed handler.

## Request inputs
- path params;
- query params;
- headers;
- JSON body;
- form body;
- file upload provider;
- type;
- required;
- default;
- enum;
- min/max;
- pattern;
- sanitize;
- validation;
- sensitive/redaction flag.

## Authentication/authorization
- logged-in cookie+REST nonce;
- Application Passwords;
- OAuth/provider adapter;
- bearer/provider adapter;
- signed webhook/provider;
- public read only when explicitly allowed;
- unauthenticated mutation prohibited by default;
- capability;
- resource Policy;
- ownership check;
- multisite/site scope;
- CORS policy;
- CSRF classification.

## Handler types
- approved Ability;
- Query definition;
- entity CRUD adapter;
- workflow trigger;
- registered provider;
- proxy/connection request with SSRF guard;
- no arbitrary PHP.

## Response
- status code mapping;
- response fields;
- pagination schema;
- envelope/raw mode;
- error schema;
- headers allowlist;
- caching headers;
- ETag/provider;
- field privacy shaping;
- relationship embedding bounds.

## Pagination/filter/sort
- page/per_page;
- cursor;
- maximum page size;
- filters mapped to Query AST;
- sort allowlist;
- search;
- include/exclude fields;
- total count policy.

## Abuse/performance
- per-IP/user/token rate limits;
- burst;
- payload size;
- upload size;
- execution timeout;
- concurrency;
- expensive Query warning;
- caching;
- idempotency key;
- replay window;
- request/response log redaction.

## Developer UX
- endpoint preview;
- request builder/test console;
- sample curl/provider;
- OpenAPI generation;
- schema diff/versioning;
- route collision detection;
- permission simulation;
- audit/health.

---

# Surface 23 — Connections

## Connection identity
- UUID;
- provider type;
- name;
- environment: production/sandbox/custom;
- base URL/region;
- enabled;
- owner/scope;
- capability;
- tags;
- health status.

## Authentication modes
- no auth;
- API key header/query provider;
- Basic auth through Vault;
- bearer token;
- OAuth2 authorization code;
- OAuth2 client credentials;
- OAuth refresh token;
- application password provider;
- custom registered auth provider;
- mTLS/provider;
- signed request provider.

Secrets:
- secret fields live in Vault;
- never returned to frontend after save;
- masked state;
- rotate;
- revoke;
- reconnect;
- last rotated;
- scopes;
- expiry;
- refresh status.

## HTTP/provider config
- base URL;
- region;
- default headers allowlist;
- timeout;
- connect timeout;
- TLS verification always on unless accepted provider exception;
- redirects policy;
- proxy provider;
- user agent;
- request size;
- response size;
- rate-limit metadata;
- retry policy;
- backoff;
- retryable status codes;
- circuit breaker provider.

## SSRF/security
- HTTPS requirement where provider supports;
- private/reserved IP deny by default;
- DNS rebinding protections;
- host allowlist/provider;
- redirect revalidation;
- credential forwarding restrictions;
- response content-type validation;
- logs redact auth/secrets.

## Operations
- test connection;
- fetch provider profile/capabilities;
- health check;
- reconnect;
- rotate credentials;
- disable;
- dependency list;
- webhook subscription management;
- import/export excludes secrets;
- clone without secrets.

---

# Surface 26 — Import / Export

## Package/export definition
- source definition types;
- selected definitions;
- dependency inclusion mode;
- include/exclude references;
- environment-sensitive values;
- secrets always excluded;
- package schema version;
- manifest;
- checksums;
- source site metadata minimal/non-sensitive;
- created timestamp;
- WPE version compatibility.

## Export formats/providers
- canonical WPE JSON package;
- ZIP bundle provider when approved;
- CSV;
- XML;
- JSON records;
- Excel provider;
- Google Sheets/remote provider;
- filtered definition export;
- operational data export per module provider.

## Import sources
- local upload;
- pasted JSON for bounded config;
- remote Connection provider;
- scheduled remote import;
- URL source only through Connection/SSRF-safe provider;
- Google Sheets provider;
- SFTP/storage provider.

## Mapping
- source preview;
- delimiter/encoding;
- record root/path;
- field mapping;
- nested data mapping;
- type conversion;
- date/time parsing;
- choice mapping;
- taxonomy hierarchy;
- media URL/file mapping;
- relation mapping;
- user mapping;
- custom table mapping;
- repeater/flexible mapping;
- default values;
- skip/ignore fields.

## Matching/update
- create only;
- update existing;
- create or update;
- match by UUID;
- match by native key;
- match by custom field(s);
- composite match;
- exact vs normalized;
- update only selected fields;
- preserve unspecified fields;
- delete missing records optional/destructive;
- duplicate policy;
- revision CAS for definitions.

## Transformations
- trim/case;
- replace;
- concatenate;
- split/join;
- number/date conversion;
- lookup map;
- conditional transform;
- registered Transform definition;
- no arbitrary eval/PHP.

## Media
- download remote media;
- existing media match;
- filename/URL/hash match;
- alt/title/caption mapping;
- MIME/size guard;
- retry;
- failed-media behavior;
- attachment ownership.

## Runtime
- dry-run mandatory for destructive/conflicting imports;
- preflight;
- conflict report;
- estimated records;
- batch size;
- background job;
- progress;
- checkpoint/resume;
- idempotency;
- retry;
- failure row report;
- partial success policy;
- transactional chunk where supported;
- cancellation;
- rollback/restore strategy.

## Scheduling
- manual;
- one-time;
- recurring;
- source change/provider trigger;
- webhook trigger;
- concurrency lock;
- stale source detection;
- notification on success/failure.

## Competitor migration adapters
- CPT UI;
- ACF/SCF;
- Meta Box;
- JetEngine;
- Redux settings;
- other registered provider;
- adapter version;
- source compatibility;
- unsupported feature report.

---

# Surface 41 — Sync

## Sync definition
- UUID;
- name/key;
- source Connection/Data Source;
- destination Data Source;
- direction: one-way/two-way;
- enabled;
- schedule/trigger;
- scope/filter;
- status;
- revisions.

## Mapping
- entity type;
- source fields;
- destination fields;
- type conversion;
- Transform definition;
- relation mapping;
- taxonomy mapping;
- media mapping;
- default values;
- ignored fields;
- immutable fields;
- secret fields prohibited unless provider explicitly owns them.

## Identity/matching
- provider remote ID;
- local ID;
- UUID;
- custom match field;
- composite key;
- ID map table;
- first-sync behavior;
- duplicate resolution.

## Delta/change detection
- modified timestamp;
- cursor/token;
- webhook change;
- checksum/hash;
- full scan;
- provider change feed;
- checkpoint.

## Conflict policy
- source wins;
- destination wins;
- newest wins only with reliable clocks;
- field-level policy;
- manual review;
- conflict queue;
- merge provider;
- protected fields;
- delete conflict behavior.

## Deletes/tombstones
- ignore remote delete;
- soft delete;
- detach relation;
- hard delete only explicit;
- tombstone retention;
- resurrection policy;
- deletion impact preview.

## Execution
- batch size;
- parallelism;
- rate limit;
- retry/backoff;
- idempotency;
- checkpoint/resume;
- timeout;
- pause/cancel;
- dry-run;
- per-record status;
- error report;
- run history;
- metrics.

---

# Surface 44 — Redirects

## Redirect rule
- UUID;
- source URL/path;
- match mode exact/regex/provider;
- destination URL;
- HTTP code 301/302/303/307/308 where appropriate;
- enabled;
- group;
- priority;
- notes;
- start/end schedule;
- hit count;
- last hit.

## Conditions
- query parameters;
- referrer;
- user/login;
- role;
- capability;
- device provider;
- locale;
- HTTP method restrictions;
- host/domain;
- site in multisite;
- AND/OR rules.

## Destination
- internal URL;
- external HTTPS/HTTP;
- relative path;
- dynamic capture groups for regex;
- safe substitution validation;
- query-string preserve/drop/replace;
- fragment policy.

## Diagnostics
- redirect chain detection;
- redirect loop detection;
- target 404 check;
- regex tester;
- preview resolved redirect;
- duplicate source detection;
- conflicting priority.

## 404/logging
- 404 logging enabled;
- request URL;
- referrer;
- hits;
- last seen;
- IP anonymization/privacy;
- retention;
- create redirect from 404;
- ignore rule;
- bulk operations.

## Automatic rules
- post slug change;
- taxonomy slug change;
- CPT/taxonomy key migration;
- old permalink capture;
- disable automatic per type;
- confirmation for broad rule.

## Import/export
- WPE package;
- CSV;
- Redirection-compatible adapter;
- Apache/Nginx export provider optional;
- dry-run conflicts.

---

# Surface 45 — Transform

## Transform definition
- UUID;
- name/key;
- input schema;
- output schema;
- status;
- reusable pipeline;
- revisions;
- clone/import/export.

## Operations
- trim;
- lower/upper/title case;
- substring;
- regex replace with limits;
- string replace;
- concatenate;
- split;
- join;
- number parse/format;
- rounding;
- currency conversion provider;
- date parse;
- date format;
- timezone conversion;
- boolean mapping;
- lookup/dictionary;
- array map/filter;
- object pick/rename;
- JSON path provider;
- URL normalize;
- HTML strip/sanitize;
- slugify;
- conditional branch;
- fallback/default;
- registered safe provider.

## Pipeline behavior
- ordered steps;
- step condition;
- step input path;
- step output path;
- error policy: fail/skip/default;
- null behavior;
- type enforcement;
- max input size;
- max collection size;
- deterministic requirement;
- side effects prohibited by default.

## Testing
- sample input;
- expected output;
- test cases;
- edge cases;
- preview each step;
- type errors;
- performance warning;
- no eval/arbitrary code.

---

# Surface 55 — Staging

## Staging site definition
- UUID;
- name;
- source site;
- target path/domain/provider;
- status;
- created/updated;
- owner;
- notes.

## Clone scope
- full site;
- files only;
- database only;
- selected tables;
- selected uploads/themes/plugins;
- exclude caches/logs/backups;
- custom excludes;
- multisite full network/subsite provider;
- size estimate;
- disk-space check.

## Environment changes
- staging URL replacement;
- serialized data safe replacement;
- disable search indexing;
- disable outgoing email by default;
- disable payment/live webhooks provider;
- environment flag/constant provider;
- staging banner;
- admin notice;
- robots policy.

## Refresh staging
- source snapshot;
- preserve staging-only selected tables/files;
- dry-run;
- backup staging;
- replacement plan;
- conflict list.

## Push to live
- backup live required;
- files selected;
- DB tables selected;
- granular table rows provider;
- exclude live users/orders/comments where configured;
- URL replacement;
- maintenance window;
- migration diff;
- destructive confirmation;
- restore point;
- rollback path;
- post-push cache/rewrite actions;
- verification checklist.

## Operations
- clone/create;
- pause;
- update;
- push;
- delete staging;
- background progress;
- resume;
- retry;
- logs;
- storage cleanup;
- permissions;
- production guardrails.

Production push is always separately privileged and cannot be inferred from ordinary source-code development approval.
