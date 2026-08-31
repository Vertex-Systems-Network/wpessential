# WPEssential — URL Redirection & Routing Manager — Exhaustive Product Specification

Status: **Phase 0 planning / no development authorization**
Date: 2026-08-29

## 1. Purpose

Provide a first-class, policy-aware redirect/routing system for WordPress sites ranging from a few redirects to very large rule sets. It must cover SEO migrations, permalink changes, 404 remediation, conditional redirects, headers, imports/exports and deterministic testing without becoming an arbitrary request-code engine.

Market/source research basis includes the Redirection project/current plugin, whose mature feature set includes regex/query matching, permalink-change monitoring, conditional redirects, redirect/404 logging, HTTP headers, permissions, Apache/Nginx import/export, JSON/CSV and WP-CLI. WPE must compete by adding typed policies, simulation, chain/loop analysis, shared services, AI Prompt support and cross-module composition while preserving WordPress authority boundaries.

## 2. Module identity

Pro module candidate: **URL Redirection & Routing Manager**

Navigation:
`WPEssential → URL & Routing`
- Overview
- Redirects
- Groups
- 404 Monitor
- URL Health
- Permalink Migrations
- Headers
- Import / Export
- Simulator
- Reports
- Settings
- Diagnostics

Dependencies:
- Definition Repository
- Conditional Logic Engine
- Dynamic Value Resolver where explicitly safe
- Policy/Capability service
- JobService
- Audit/Observability
- Import/Export
- Shared Cache
- Rate Limit
- Local Privacy
- Error Taxonomy
- Multisite Scope/Lifecycle
- AI Prompt Runtime

Optional integrations:
- Link Health module
- Search/Replace module
- Analytics/Journey
- Geo/Territory
- Experiments (only for explicit experiment routing; never accidental SEO redirect testing)
- WooCommerce domain adapter

## 3. Redirect definition

### Identity
- name;
- stable key/UUID;
- group;
- enabled/disabled;
- priority/order;
- owner site/network scope;
- tags;
- description/notes;
- source/provenance;
- revision;
- effective start/end schedule;
- created/updated by/time.

### Source match
Match modes:
- exact path;
- prefix;
- suffix where safe;
- wildcard/glob with bounded grammar;
- regular expression with validation/complexity budget;
- WordPress object/permalink identity;
- old permalink structure migration;
- host/domain + path;
- page type such as 404 where supported.

Normalization options:
- case-sensitive / insensitive;
- trailing slash exact / ignore / normalize;
- URL-decoding profile;
- repeated slash normalization policy;
- query-string mode: exact / ignore / pass / discard / selected keys / mapped keys;
- fragment is never server-visible authority and cannot be source match input;
- scheme/host handling;
- percent-encoding normalization;
- locale/base-path profile.

### Conditions
Typed conditions only:
- logged-in/logged-out;
- WordPress capability;
- Role/Membership through Policy, not raw hidden state;
- referrer;
- cookie presence/value with privacy classification;
- HTTP header allowlisted key/value;
- IP/CIDR using trusted-proxy request identity;
- request host/server;
- device/browser class only as advisory/non-security condition;
- page/entity context;
- schedule;
- Geo/Territory only through certified F11 provider;
- experiment assignment only through F08;
- custom registered condition provider via SDK.

MUST NOT:
- treat browser/user-agent as authorization;
- expose arbitrary PHP callbacks in standard UI;
- permit client-controlled headers/cookies to grant protected access.

### Actions
- redirect to URL with 301/302/303/307/308 as appropriate;
- return 404/410/451 or registered bounded error response;
- pass/no-op;
- internal WordPress route mapping only through a dedicated certified profile;
- set/remove allowlisted response headers;
- invoke a registered typed routing Ability only when explicitly supported and safe.

Target types:
- absolute external URL;
- site-relative path;
- WordPress entity/permalink reference;
- typed dynamic target from bounded DVR sources;
- regex capture substitution;
- mapped query parameters.

Target validation:
- scheme allowlist;
- control-character/header-injection rejection;
- loop/chain analysis;
- unsafe scheme rejection;
- external host warning/policy;
- encoded-path validation;
- target existence advisory check where possible.

## 4. Groups

Fields:
- name/key;
- enabled;
- priority band;
- execution profile: WordPress / Apache export / Nginx export / external adapter;
- default source normalization;
- default query handling;
- default response code;
- logging profile;
- permissions;
- notes/tags.

Rules:
- group disable must not delete rules;
- unsupported rule semantics cannot silently export to Apache/Nginx;
- export must report lossiness/unsupported conditions/actions.

## 5. Permalink change monitoring

Options:
- monitor posts/pages/public CPTs;
- include/exclude post types;
- monitor taxonomy term permalink changes where supported;
- create redirect automatically as Draft / Active / Ask;
- prevent duplicate/cycle;
- merge/chain-collapse suggestion;
- old slug compatibility awareness;
- bulk permalink-structure migration wizard;
- preview affected URLs;
- rollback planning.

Flow:
`permalink change detected → canonical old/new URL resolve → duplicate/loop check → policy → Draft/Active redirect → audit`.

## 6. 404 Monitor

Capture options:
- disabled / aggregate-only / detailed;
- path;
- query classification;
- referrer optional;
- user-agent class optional;
- IP off/anonymized/truncated/full only with explicit policy;
- logged-in user optional and purpose-gated;
- count;
- first/last seen;
- source classification;
- bot/AI-crawler class where reliably identifiable, never identity proof.

Controls:
- retention;
- max rows/storage budget;
- dedupe bucket;
- ignore rules;
- known scanner/noise filters;
- role access;
- export;
- privacy exporter/eraser behavior.

Actions:
- create redirect;
- bulk map selected URLs;
- mark ignored/expected;
- send to Link Health investigation;
- propose nearest valid target using Search/AI as suggestion only;
- inspect referrers;
- group by path/prefix/referrer.

## 7. Redirect logs and analytics

Separate operational truth from behavioral analytics.

Operational log fields:
- redirect definition/revision;
- source normalized URL;
- target/result;
- HTTP code;
- matched condition profile;
- timestamp;
- request identity/privacy profile;
- latency;
- failure reason.

Options:
- off / aggregate / sampled / full bounded;
- retention;
- export;
- hit counters;
- first/last hit;
- filters/search;
- redirect effectiveness reports;
- chain/loop/error rate.

If F02 Analytics is enabled, only approved minimized events may be emitted. Redirect logs are not the analytics warehouse.

## 8. HTTP headers

Profiles:
- redirect-response headers;
- selected site-wide headers only if module owns a clearly documented scope;
- HSTS/CSP/security headers should integrate with Protector/Platform Security profiles rather than duplicate conflicting control planes.

Per header:
- name allowlist/validation;
- value validation;
- scope;
- append/replace/remove;
- schedule;
- route conditions;
- preview.

Reject CR/LF and forbidden hop-by-hop headers.

## 9. Simulator / debugger

Inputs:
- URL/path;
- host/scheme;
- query parameters;
- login/capability simulation identity where authorized;
- headers/cookies as simulated data;
- referrer;
- time;
- optional Geo/experiment context.

Output trace:
- normalized request;
- candidate rule count;
- skipped groups;
- ordered evaluations;
- condition results;
- selected rule/revision;
- target rendering;
- loop/chain analysis;
- final status/header result;
- cache identity;
- warnings.

Simulation is no-write by default and cannot grant real authorization.

## 10. Chain / loop / collision analyzer

Detect:
- direct self-loop;
- multi-rule cycle;
- external-return cycle where observable;
- long chains;
- overlapping exact/prefix/regex rules;
- shadowed/unreachable rules;
- conflicting priority;
- duplicate normalized sources;
- redirect-to-404/blocked target;
- host/scheme loops;
- query-preservation surprises.

Actions:
- explain;
- propose collapse;
- batch rewrite plan;
- never auto-collapse without reviewed Plan.

## 11. Import / Export

Formats:
- WPE package/JSON;
- CSV;
- Apache `.htaccess` supported subset;
- Nginx supported subset;
- simple text map;
- importer adapters for common redirect plugins where legally/technically feasible.

Import flow:
`parse → normalize → validate → capability/lossiness report → duplicate/collision analysis → dry run → reviewed Plan → apply → verify`.

Export must mark unsupported conditions/actions instead of silently degrading them.

## 12. REST / Abilities / MCP / CLI

Abilities:
- list/get/simulate redirects;
- create/update Draft redirect;
- publish/enable/disable with capability;
- analyze chain/collision;
- inspect aggregate 404s;
- create import Plan;
- export approved configuration.

MCP exposure opt-in only. AI may draft redirect plans, mappings and remediation suggestions; it cannot bypass Policy or publish destructive/bulk changes without required approval.

## 13. AI Prompt examples

- “Old `/shop/*` URLs ko `/store/*` par 301 karo, query params preserve karo.”
- “Last 30 days ke top 404s audit karo aur safe redirect suggestions do.”
- “Redirect loops aur 3+ hop chains identify karo.”
- “Old permalink structure se new structure ka dry-run plan banao.”

Prompt output always compiles to typed Redirect Definitions/Plan IR.

## 14. Performance architecture

- normalized source index;
- exact-match fast path;
- bounded prefix/regex candidate sets;
- compiled generation cache;
- invalidation on published revision/group change;
- no per-request N+1 DB reads;
- regex execution budget;
- large rulesets evidence-gated at 1k/10k/100k+ rules;
- logging asynchronously/batched where safe.

## 15. Multisite

- explicit site/network ownership;
- site rules cannot affect another site;
- network templates require per-site binding or explicit network route authority;
- domain mapping collisions visible;
- clone/transfer rewrites reviewed;
- network admin permissions separate;
- deletion/archival must respect Site Lifecycle.

## 16. Privacy / security

- minimize IP/referrer/user-agent storage;
- protect 404/log exports;
- rate-limit log pollution;
- SSRF protection for target existence checks through Safe HTTP;
- no open redirect from untrusted dynamic target data;
- no arbitrary headers/code;
- direct request evaluation never bypasses WordPress auth/Policy.

## 17. Failure / recovery

- malformed regex → definition invalid, never runtime fatal;
- unavailable storage/cache → risk-classified fail behavior;
- partial import → journal/checkpoint and truthful partial state;
- Apache/Nginx write failure → WordPress rule remains separate truth;
- stale compiled cache → generation mismatch must trigger safe refresh;
- DB migration issue → module degraded, not partially executing unknown schema.

## 18. Evidence namespace

Future fixed protocol: `RDR-001…RDR-176`, executed 0 until explicit development consent.

Primary evidence groups will cover matching, normalization, conditions, actions, loops/chains, logs/privacy, 404s, permalink monitoring, headers, import/export, REST/Abilities/MCP, performance, cache/concurrency, Multisite/lifecycle, upgrades and adversarial security.

## 19. MUST NOT

- arbitrary PHP/JS/shell routing code;
- unbounded catastrophic regex path;
- open redirects from user-controlled values;
- silently export unsupported semantics;
- treat visibility/condition as authorization;
- auto-publish AI suggestions without required approval;
- retain detailed request data indefinitely;
- claim server-level redirect active merely because WPE export succeeded.
