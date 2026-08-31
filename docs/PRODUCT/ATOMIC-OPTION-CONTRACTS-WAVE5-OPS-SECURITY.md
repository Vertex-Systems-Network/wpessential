# WPEssential — Atomic Option Contracts Wave 5: Platform Operations & Security

Status: **atomic option inventory**  
Snapshot: **2026-08-31**  
Surfaces: **24 Backup, 25 Reset, 27 Protector, 29 XML-RPC, 30 Roles, 31 Platform, 47 Link Health, 48 DB Maintenance, 50 Safe Script, 52 Security Scanner**.

---

# Surface 24 — Backup

## Backup definition
- UUID;
- name/key;
- enabled;
- scope: files/database/full/incremental;
- schedule;
- retention;
- destination providers;
- encryption/provider;
- status;
- notes.

## File scope
- wp-content;
- uploads;
- themes;
- plugins;
- mu-plugins;
- custom directories;
- WordPress core optional/provider;
- include/exclude glob patterns;
- symlink policy;
- max file size;
- unreadable-file behavior.

## Database scope
- all site tables;
- selected tables;
- network tables;
- custom tables;
- include/exclude;
- routines/views provider where supported;
- chunk size;
- transaction/snapshot capability detection;
- compression;
- anonymization provider.

## Schedule/retention
- manual;
- hourly/daily/weekly/monthly presets;
- custom schedule through Cron surface;
- files vs DB separate schedules;
- keep last N;
- keep by age;
- grandfather retention provider;
- minimum restore-point safeguard;
- low-storage cleanup.

## Storage providers
- local;
- S3-compatible;
- Google Drive;
- Dropbox;
- OneDrive;
- SFTP;
- WebDAV;
- registered provider;
- multi-destination;
- provider path/bucket;
- region;
- multipart/chunk upload;
- retry/resume;
- remote retention;
- connection/Vault credentials.

## Integrity/security
- manifest;
- checksums;
- archive encryption;
- DB encryption;
- encryption key strategy;
- no key stored uselessly beside ciphertext;
- upload completeness;
- restore-point verification;
- malware scan provider optional;
- secret redaction in logs.

## Restore
- files/database/full/selective;
- table selection;
- file selection/provider;
- dry-run/preflight;
- compatibility/version check;
- URL migration option;
- maintenance mode;
- backup current state before restore;
- overwrite policy;
- restore progress;
- resume/retry;
- rollback/recovery;
- post-restore permalink/cache actions;
- verification checklist.

## Migration
- clone to new URL;
- search/replace serialized-safe;
- database prefix mapping;
- path mapping;
- multisite/subsite provider;
- selected-site migration;
- staging integration.

---

# Surface 25 — Reset

## Reset scopes
- WPE module configuration only;
- one Definition;
- one module;
- WPE all configuration;
- transients/cache;
- module operational data;
- WordPress content reset provider;
- full site reset provider only if explicitly approved.

## Options
- dry-run mandatory;
- affected tables/options/definitions/files counts;
- dependency impact;
- backup requirement;
- confirmation phrase;
- capability;
- multisite scope;
- preserve users;
- preserve media;
- preserve selected modules;
- preserve licenses/connections metadata while secrets policy remains separate;
- preserve audit log;
- background job threshold.

## Recovery
- generated restore point;
- reset run ID;
- logs;
- rollback when feasible;
- restore route;
- forward-repair path;
- final verification.

Reset actions MUST be individually classified; there is no single blanket destructive reset permission.

---

# Surface 27 — Protector

## Protection definition
- UUID;
- name/key;
- enabled;
- scope;
- priority;
- Decision rules;
- response behavior;
- status;
- revisions.

## Targets
- whole site;
- posts/CPTs;
- individual content;
- taxonomies/terms;
- archives;
- URL/path patterns;
- frontend dashboard routes;
- documents/downloads;
- REST routes;
- feeds;
- search results;
- registered provider target.

## Access rules
- login required;
- role;
- capability;
- membership;
- specific user;
- shared password group;
- IP allow/deny provider;
- date/time;
- geo provider;
- custom Decision rule;
- AND/OR combinations.

## Response
- redirect to login;
- redirect custom URL;
- 401;
- 403;
- 404 masking;
- protected message/template;
- teaser/excerpt;
- password form;
- cache-control behavior;
- robots/noindex behavior.

## Shared password security
- hashed storage;
- expiration;
- attempt limit;
- lockout duration;
- rate limit;
- cookie/session duration;
- secure cookie flags;
- audit failures/success under retention policy.

## Preview/diagnostics
- test as user/role;
- URL simulator;
- conflicting-rule diagnostics;
- cache incompatibility warning;
- REST/search/feed exposure preview;
- no security-by-menu-hiding.

---

# Surface 29 — XML-RPC

## Global policy
- WordPress default;
- fully disable XML-RPC application endpoints where feasible;
- allow selected methods;
- deny selected methods;
- authentication method restrictions;
- multisite scope.

## Method groups
- pingback methods;
- post/page methods;
- media methods;
- comment methods;
- user/blogger/metaWeblog methods;
- registered plugin methods.

Per group/method:
- allow/deny;
- role/capability policy;
- rate limits;
- audit/logging;
- compatibility dependency warnings.

## Pingbacks
- enable/disable;
- `pingback.ping`;
- `pingback.extensions.getPingbacks`;
- outbound ping behavior separate from XML-RPC inbound;
- SSRF/security rationale;
- trackback compatibility warning.

## Diagnostics
- currently registered methods;
- source/plugin owner;
- authentication requirement;
- last request metrics optional;
- blocked request count;
- Site Health warning for unsafe exposure.

---

# Surface 30 — Roles

## Role definition
- slug/key;
- display name;
- clone source;
- status/managed classification;
- site/network scope;
- description;
- custom vs WordPress/plugin-owned.

## Capability matrix
- core capabilities;
- post-type capabilities;
- taxonomy capabilities;
- WPE module capabilities;
- plugin/provider capabilities;
- orphan/unknown capabilities;
- grouped search/filter;
- grant/revoke;
- select all group;
- effective capability preview.

## Role operations
- create;
- clone;
- rename display name;
- key migration only under guarded workflow;
- delete;
- replacement role for users;
- restore WordPress defaults;
- presets/templates;
- compare roles;
- diff;
- import/export.

## User assignment
- individual assignment;
- multiple roles where WordPress/plugin semantics allow;
- bulk assignment;
- remove role;
- replace roles;
- filter users;
- affected-user count;
- audit.

## Safety
- protect administrator recovery path;
- current-admin lockout prevention;
- Super Admin cannot be simulated by ordinary role grants;
- network boundaries;
- plugin-owned capability warning;
- dependency impact before deleting role/capability.

---

# Surface 31 — Platform

## Environment/compatibility
- WPE version;
- edition Free/Pro;
- WordPress version;
- PHP version;
- DB engine/version;
- multisite;
- memory limits;
- filesystem mode;
- cron health;
- REST health;
- loopback health;
- required PHP extensions;
- optional integrations;
- compatibility warnings.

## Module registry
Per module:
- installed/available;
- edition;
- version;
- enabled;
- dependencies;
- conflicts;
- degraded reason;
- capabilities;
- admin route;
- migrations;
- health checks;
- scheduled jobs;
- import/export types;
- uninstall policy.

## Global platform settings
- telemetry/diagnostics opt-in if ever provided;
- update channel stable/beta/provider;
- debug diagnostics mode;
- log retention;
- support-bundle retention;
- default UI density;
- default theme mode;
- first-run/wizard state;
- uninstall preserve/delete-data choice with explicit warning;
- feature flags only registered/owned.

## Site Health/support
- health tests;
- diagnostics;
- support bundle preview;
- redaction;
- export support bundle;
- configuration fingerprint;
- module status;
- DB schema versions;
- background job health;
- audit/log health;
- asset diagnostics;
- no secrets.

---

# Surface 47 — Link Health

## Scope
- posts/CPT content;
- comments optional;
- terms;
- widgets/blocks/provider;
- menus;
- settings fields;
- listings/components;
- documents/templates;
- external/internal links;
- media URLs.

## Crawl/check policy
- enabled;
- schedule;
- batch size;
- concurrency;
- timeout;
- retry;
- user agent;
- HEAD vs GET fallback;
- redirect follow limit;
- max response size;
- respect robots provider optional;
- domain rate limit.

## Status classification
- healthy;
- redirect;
- redirect chain;
- broken 4xx;
- server 5xx;
- timeout;
- DNS/TLS failure;
- blocked/auth-required;
- soft-404 provider;
- invalid URL;
- ignored.

## Actions
- open source location;
- replace URL;
- ignore once/permanently;
- create Redirect rule;
- bulk update through Transform/approved mutation;
- recheck;
- export report.

## History/analytics
- first seen;
- last checked;
- status history;
- source count;
- destination count;
- domain summary;
- scheduled reports/notifications.

External URL checking must enforce SSRF/private-address policy.

---

# Surface 48 — Database Maintenance

## Cleanup candidates
- post revisions;
- auto drafts;
- trashed posts;
- spam comments;
- trashed comments;
- expired transients;
- all transients optional;
- orphan post meta;
- orphan term meta;
- orphan user meta;
- orphan comment meta;
- orphan relationships/provider;
- WPE stale job/log data under retention;
- session/transient provider data where owned.

## Table analysis
- table size;
- index size;
- row estimates;
- overhead;
- engine/collation;
- autoloaded option size/count;
- largest options;
- duplicate indexes/provider;
- missing index diagnostics from WPE queries.

## Optimization operations
- optimize table;
- analyze table;
- repair provider where supported;
- cleanup selected candidates;
- autoload recommendation;
- retention cleanup;
- schedule.

## Safety
- dry-run counts/size;
- backup requirement threshold;
- site/network scope;
- chunk size;
- max runtime;
- lock impact warning;
- destructive confirmation;
- skip unknown/plugin-owned data unless provider explicitly claims ownership;
- logs/rollback/restore guidance.

---

# Surface 50 — Safe Script

## Snippet definition
- UUID;
- name/key;
- type: PHP/JS/CSS/HTML/registered language provider;
- enabled;
- scope admin/frontend/login/REST/provider;
- priority;
- revisions;
- tags/category;
- notes;
- clone/import/export.

## Execution/insertion locations
PHP:
- approved WordPress hook;
- action/filter;
- priority;
- callback args;
- shortcode/provider;
- no arbitrary request-time eval path beyond controlled snippet runtime.

JS/CSS/HTML:
- header/footer;
- frontend/admin route scope;
- Placement surface;
- shortcode/block provider;
- specific content conditions.

## Conditions
- post type/content;
- URL/path;
- user/role/capability;
- logged-in/out;
- device presentation;
- date/time;
- Decision definition;
- site/network.

## Safety
PHP:
- syntax parse before activation;
- isolated validation process/provider where available;
- fatal recovery/safe mode;
- automatic disable on fatal attributable to snippet;
- restricted capability;
- audit;
- revision rollback.

JS/CSS/HTML:
- syntax lint where available;
- XSS/sanitization classification;
- CSP compatibility diagnostics;
- no secrets in frontend code;
- source maps/debug only non-production.

## Operations
- preview where applicable;
- run/test only for explicit registered test context;
- enable/disable;
- schedule enable/disable;
- duplicate;
- revision diff/restore;
- export with privacy classification.

AI/MCP cannot receive a blanket ability to create/enable executable snippets.

---

# Surface 52 — Security Scanner

## Scan profiles
- quick;
- standard;
- deep;
- custom;
- scheduled;
- on-demand;
- site/network scope.

## Core integrity
- WordPress core checksums;
- missing files;
- modified files;
- unknown core files;
- version/end-of-support warning;
- repair core file through verified source/provider.

## Plugin/theme integrity
- official repository checksum/source where available;
- changed files;
- unknown files;
- vulnerable version intelligence provider;
- abandoned/outdated software heuristic;
- unsigned/private plugin limitations explicitly shown.

## Malware/suspicious-code providers
- signatures;
- obfuscated code heuristic;
- suspicious eval/base64/gzinflate patterns as signals, not automatic malware verdict;
- webshell signatures;
- malicious redirect patterns;
- injected admin/user/provider checks;
- external threat intelligence provider.

## Configuration/security checks
- debug display;
- file editor state;
- weak permissions;
- exposed backups/config files;
- directory listing;
- XML-RPC exposure;
- REST/user enumeration context;
- default admin names/provider;
- weak salts diagnostic without exposing salts;
- HTTPS/admin cookie diagnostics;
- dangerous cron/snippet providers;
- public sensitive files.

## Findings
- ID;
- severity;
- confidence;
- category;
- file/resource;
- evidence summary;
- first/last seen;
- remediation;
- ignore/suppress with reason/expiry;
- status;
- audit.

## Remediation
- view diff;
- download clean reference metadata;
- repair only verified files;
- quarantine provider;
- delete only explicit confirmation;
- disable vulnerable plugin/theme provider;
- backup before destructive remediation;
- rollback/recovery;
- re-scan.

## Notifications/reports
- new critical/high;
- scan failed;
- vulnerability update;
- scheduled digest;
- export report;
- Site Health integration;
- retention/privacy.

Scanner findings are evidence-based signals; WPE must not overclaim malware certainty when only heuristic evidence exists.
