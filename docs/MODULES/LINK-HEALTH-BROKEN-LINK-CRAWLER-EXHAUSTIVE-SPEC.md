# WPEssential — Link Health, Broken Link & Crawl Intelligence — Exhaustive Product Specification

Status: **Phase 0 planning / no development authorization**
Date: 2026-08-29

## 1. Purpose

Continuously or on-demand discover internal/external broken links, broken images/media, redirect chains/loops, orphaned content and URL-health regressions across WordPress/WPE content while respecting Safe HTTP, robots/server load, privacy and source ownership.

Market research shows large installed demand for broken-link checking and remediation. WPE should integrate the capability with Redirect Manager, Search/Replace, Analytics, JobService and content Data Sources instead of creating an isolated crawler.

## 2. Module identity

Pro module candidate: **Link Health & Crawl Intelligence**

Navigation:
`WPEssential → URL & Routing → Link Health`
- Overview
- Scans
- Issues
- Link Inventory
- Internal Graph
- Orphan Content
- Redirect Chains
- Broken Media
- Schedules
- Fix Plans
- Settings
- Diagnostics

Dependencies:
- JobService
- Safe HTTP / Connections
- Data Source Registry
- Query
- Policy/Capability
- Audit
- Rate Limit
- Cache
- Privacy
- Error Taxonomy
- Multisite
- AI Prompt Runtime

Optional:
- URL Redirect Manager
- Search/Replace
- Search Indexing
- Analytics/Journey
- WooCommerce adapter

## 3. Scan sources

Source discovery profiles:
- stored post/page/CPT content;
- excerpts;
- custom fields through Field Storage descriptors;
- comments where enabled;
- term descriptions;
- navigation/menu entities;
- widgets/settings through registered adapters;
- block attributes;
- shortcode output only through safe parser/registered adapter;
- rendered frontend crawl profile;
- XML sitemaps;
- Query/Listing routes;
- Woo product/category/account routes through adapter;
- manually supplied URL list.

Each source records entity identity, field/path, site scope and source revision/fingerprint.

## 4. Link extraction

Types:
- anchor `href`;
- image/source `src`, `srcset`;
- video/audio/embed URLs;
- canonical/hreflang references where adapter supplies;
- stylesheet/script references only in developer/asset scan profile;
- form actions where safe;
- redirect targets from URL Routing module;
- plain-text URLs only in optional detection profile.

Normalize without changing content.

## 5. URL classification

- same-site internal;
- same-network other-site;
- external;
- mailto/tel/sms;
- fragment/anchor;
- protocol-relative;
- media/file;
- API endpoint;
- unsupported/unsafe scheme;
- dynamic/template URL;
- generated/signed URL that should not be crawled.

## 6. Check engine

Safe HTTP profiles:
- HEAD first where reliable;
- GET fallback with bounded body;
- configurable timeout;
- redirect follow max;
- per-host concurrency;
- host backoff;
- retry policy;
- TLS verification mandatory;
- DNS/private-network SSRF protections;
- user-agent identification;
- robots/polite-crawl policy for rendered crawl;
- no credential leakage across redirects.

Status truth:
- healthy 2xx;
- redirect 3xx;
- broken 404/410;
- restricted 401/403;
- rate-limited 429;
- server error 5xx;
- timeout;
- DNS failure;
- TLS/certificate failure;
- connection failure;
- blocked by policy/robots;
- inconclusive/bot protection;
- unsupported scheme;
- not checked.

Do not call every 403/timeout “broken”.

## 7. Internal WordPress checks

For internal URLs WPE may resolve directly through WordPress route/entity knowledge before network request:
- post/term exists;
- published/private status;
- permalink changed;
- expected 404/410;
- redirect rule applies;
- route collision;
- target permission visibility.

Protected/private URL health must not leak existence to unauthorized viewers.

## 8. Anchor/fragment checking

Optional HTML-aware profile:
- fetch/render bounded page;
- collect element IDs/named anchors;
- case/encoding rules;
- report missing fragment separately from broken URL;
- JS-created anchors unsupported unless browser adapter explicitly certified.

## 9. Redirect chain intelligence

Detect:
- 1-hop healthy redirect;
- multi-hop chain;
- cycle;
- protocol flip-flop;
- cross-domain chain;
- redirect to broken target;
- canonicalization loop;
- mixed query behavior;
- excessive hop threshold.

Integration action:
- propose direct target rewrite via Search/Replace;
- propose redirect collapse via URL Routing;
- never mutate content/rules directly from scan result without Plan.

## 10. Orphan / internal link graph

Graph nodes:
- public content/routes;
- links between entities;
- sitemap entry;
- navigation membership;
- inbound count;
- outbound count;
- crawl depth from configured roots.

Orphan definition is configurable and must not imply SEO harm automatically.

Reports:
- zero internal inbound links;
- low-link pages;
- isolated clusters;
- unreachable from root/navigation;
- duplicate destination patterns.

## 11. Broken media

Detect:
- missing attachment/file;
- attachment DB record without file;
- file without expected derivative advisory;
- external image 404/timeout;
- invalid srcset target;
- mixed-content URL;
- redirected media;
- private media inaccessible through correct delivery profile.

Fix integrations:
- Media Rules/derivative regeneration;
- Search/Replace URL mapping;
- Redirect Manager;
- manual replacement.

## 12. Scan definition

Fields:
- name/key;
- source scope;
- site/network scope;
- link types;
- internal/external settings;
- max URLs;
- max depth;
- per-host concurrency;
- schedule;
- timeout/retry;
- ignore patterns;
- authentication profile where explicitly certified;
- retention;
- notifications;
- owner/permissions;
- revision.

## 13. Scheduling

- manual;
- daily/weekly/monthly/custom cron through JobService;
- after deployment/migration;
- after Search/Replace Run;
- after permalink migration;
- after large content import;
- incremental changed-content scan.

Use durable checkpoints and host/resource keys.

## 14. Issue model

Issue fields:
- stable issue key;
- link URL/normalized identity;
- source entity/field;
- site;
- issue type;
- first/last seen;
- latest check;
- occurrence count;
- status history;
- evidence;
- severity;
- owner;
- ignored/snoozed state;
- fix Plan reference;
- verified fixed time.

States:
`new → confirmed → triaged → fix planned → fixed pending verification → verified → ignored/snoozed`.

## 15. Fix Plan

Supported safe fixes:
- replace target URL;
- unlink;
- change to final redirect destination;
- create redirect;
- restore/regenerate media;
- mark expected/restricted;
- update navigation item;
- bulk map matching URLs.

Flow:
`select issues → ownership/capability resolution → proposed changes → dry run/diff → backup if required → approval → owning Ability execution → re-scan verification`.

## 16. AI Prompt

- “Broken internal links scan karo aur top impact issues group karo.”
- “3+ hop redirect chains identify kar ke direct-target fix plan banao.”
- “Broken images ko source page ke saath list karo; abhi changes mat karo.”

AI may classify/prioritize/suggest. It cannot infer business-intended destination as authoritative without evidence/approval.

## 17. REST / Abilities / MCP

Abilities:
- create Scan Draft;
- estimate scan;
- run/stop authorized scan;
- list issues with Policy;
- recheck URL/issue;
- create Fix Plan;
- ignore/snooze issue;
- export report.

MCP exposure read-oriented by default; mutation goes through owning modules and approvals.

## 18. Privacy

- URLs may contain PII/secrets; redact query values by default;
- never store Authorization headers/cookies;
- referrer collection not required for crawler results;
- external request logs minimized;
- retention configurable;
- protected/private source URLs access controlled.

## 19. Multisite

- site-owned scan definitions by default;
- network scan explicitly targets sites;
- domain mapping awareness;
- cross-site internal links classified separately;
- per-site issue visibility;
- host rate limit shared appropriately;
- site deletion cleans/archives related issue state through lifecycle coordinator.

## 20. Performance

Evidence profiles:
- 1k links;
- 100k links;
- 1M links;
- 10M occurrences;
- 100/1k-site networks.

Controls:
- dedupe URL checks;
- cache freshness;
- incremental recheck;
- per-host concurrency;
- bounded HTTP body;
- queue backpressure;
- crawl budget.

## 21. Evidence namespace

Future protocol: `LNK-001…LNK-176`, executed 0 until development consent.

Groups cover extraction, URL normalization, internal resolution, Safe HTTP, status classification, anchors, redirect chains, media, graph/orphans, scheduling/jobs, issue lifecycle, Fix Plans, privacy/security, Multisite, scale and integration regressions.

## 22. MUST NOT

- send secrets/cookies to arbitrary external URLs;
- access private networks through SSRF;
- call inconclusive responses broken with certainty;
- mutate content from scan results without Plan;
- bypass source entity Policy;
- overload external hosts;
- treat orphan status as automatic SEO defect;
- use AI suggestion as authoritative replacement target.
