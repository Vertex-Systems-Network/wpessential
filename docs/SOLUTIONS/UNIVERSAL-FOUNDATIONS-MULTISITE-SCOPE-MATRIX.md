# WPEssential — Universal Foundations Multisite Scope Matrix

Status: **Phase 0 planning / 12 expanded foundations / no Multisite implementation authorized**  
Date: 2026-08-28  
Complements: `docs/MODULES/MULTISITE-SCOPE-OPTION-MATRIX.md` for the original 31 surfaces.

## 1. Shared rules

All expanded foundations inherit the canonical Multisite ownership rule:
- current-blog context is not durable ownership;
- every Definition/runtime record carries trusted site/network scope where applicable;
- Site Admin cannot create implicit network authority;
- network templates and shared runtime are distinct;
- cross-site reads/actions require explicit network-capable Data Source + Policy;
- network fan-out uses bounded Jobs and per-site authorization;
- site clone/transfer/delete never silently copies/changes credentials, external subscriptions, analytics identity, protected assets or other site's data;
- Backup/Restore scope is explicit.

## 2. Matrix

| Foundation | Default logical scope | Network modes | Core Multisite rule |
|---|---|---|---|
| F01 Solution Blueprint Composer | Blueprint library may be install/network available; installation Site by default | Network template/library + selected-site rollout | a shared Blueprint does not imply shared runtime data; each installed binding records target scope |
| F02 Analytics & Journey | Site | Network aggregate/warehouse only explicit profile | events carry source site; cross-site identity/journey merge is off by default and separately policy-governed |
| F03 Search & Indexing | Site index | Network index explicit | site documents/results stay site-owned; network index requires per-document scope and runtime Policy filtering |
| F04 Formula/Decision/Scoring | Site Definition | Network template/shared Definition | formula may be shared; input data/consumer authorization resolves in target site |
| F05 Ledger/Balance/Movement | Site ledger | Network-owned ledger only explicit advanced profile | balances/postings never mix sites accidentally; account scope is part of idempotency/uniqueness |
| F06 Scheduling/Reservation | Site | Network resource pool explicit | site resources/reservations isolate by default; shared resource pool has network ownership and site-use Policy |
| F07 Placement/Personalization | Site | Network template/experience library | network Experience may deploy to sites but target context, frequency state and authorization remain site-aware |
| F08 Experimentation/Rollout | Site | Network rollout coordinator | assignment namespace includes scope; one site's exposure/metrics cannot contaminate another without explicit aggregate experiment |
| F09 Documents/Generation | Site | Network template library | template can be shared; generated document/protected asset belongs to target resource/site unless explicitly network-owned |
| F10 Data Sync/ETL | Site | Network coordinator/shared Connection delegation | each sync target/source site explicit; payload field cannot choose site authority; cursors are scope-bound |
| F11 Geospatial/Location/Territory | Site | Network territory library/runtime explicit | shared territory Definition does not make location records cross-site; privacy precision resolved per target Policy |
| F12 AI Gateway/Copilot | Provider connection install/network capable; task/site usage Site by default | Network provider delegation + network task library | shared model credential use does not reveal secret; AI context cannot cross sites without explicit network Policy |

---

# 3. F01 Solution Blueprint Composer

## Blueprint ownership
- official/local Blueprint can be network-library visible;
- site-local Blueprint stays site-owned;
- network template can be selectable by sites if delegated;
- Site Admin cannot edit network canonical Blueprint unless authorized.

## Installation
Per target site record:
- `installed_solution_uuid`;
- Blueprint key/version;
- site ID;
- component binding map;
- install variables excluding network secrets;
- drift state;
- health;
- upgrade state.

Network rollout options:
- selected sites;
- site query/filter;
- install Draft only default;
- concurrency batch size;
- stop-on-failure threshold;
- per-site mapping/collision review;
- future-site default off/template suggestion;
- no automatic production activation across all sites by default.

## Clone
Cloned site may copy Definition mappings but must review:
- external Connections;
- domain/route values;
- analytics identity;
- schedules;
- emails/senders;
- AI provider usage;
- protected storage;
- commerce credentials.

---

# 4. F02 Analytics & Journey

Every event includes trusted `site_scope` from collection endpoint/runtime.

Site mode:
- anonymous visitor identity site-bound by default;
- user ID shared by WP network does not merge behavioral histories automatically;
- session cookie domain/profile configurable but cross-site linking requires explicit network analytics profile and consent.

Network aggregate mode options:
- source site allowlist;
- event allowlist;
- field projection/minimization;
- common metric definitions;
- site dimension mandatory;
- cross-site distinct-user semantics explicit;
- network retention;
- site opt-out/mandatory policy;
- aggregate minimum cohort privacy;
- Network Admin access.

Site deletion:
- raw/aggregate retention profile;
- detach/anonymize/delete according policy;
- historical network aggregate truth documented.

---

# 5. F03 Search & Indexing

Site index default key namespace includes site.

Network index requires:
- source sites;
- source Data Source per site;
- document scope field;
- stable compound identity;
- per-site Policy projection;
- site filter mandatory unless network principal authorized;
- deletion/tombstone on site delete;
- index rebuild partitioning;
- cache site dimension;
- network query cost bounds.

A public site search never sees another site's documents because they share an index backend.

---

# 6. F04 Formula / Decision

Definition scope:
- site formula;
- network template copied/linked;
- network shared formula allowed for network-owned consumer.

Runtime inputs always include target scope. A network-shared formula cannot fetch site data by itself; its registered input source must be authorized for the target.

Site override options:
- constants/thresholds overridable;
- formula body locked/linked;
- selected lookup tables site-local;
- effective dates per site if template permits.

---

# 7. F05 Ledger

Default ledger/account/entry site-owned.

Compound identity/idempotency includes scope.

Network ledger only for genuinely network-owned value/resource, with:
- Network Admin ownership;
- explicit participant sites;
- account owner site/resource;
- cross-site transfer transaction type separately authorized;
- site closure handling;
- network reconciliation;
- Backup/Restore at network scope;
- no site-admin arbitrary adjustment to another site's account.

Site clone default:
- ledger history **not cloned as live balance**;
- test/staging clone requires explicit anonymized/non-financial profile;
- production fork requires opening-balance migration plan, not copied postings.

---

# 8. F06 Scheduling & Reservation

Site resources default.

Network shared resource pool examples:
- one central facility used by sites;
- shared staff/equipment;
- network event capacity.

Required fields:
- resource ownership scope;
- use-right sites;
- reservation owner site;
- principal Policy;
- network capacity version;
- site-specific booking types;
- conflict/priority policy;
- network-wide atomic capacity contract.

One site cannot exceed shared capacity due per-site caches.

Site delete:
- future reservations cannot disappear without impact workflow;
- shared resource remains if owned elsewhere/network;
- participant/customer notification plan.

---

# 9. F07 Placement & Personalization

Placement slot is registered in target site's runtime/theme/builder context.

Network library can provide:
- Experience template;
- design tokens;
- component binding template;
- rules template;
- campaign template.

Site-specific:
- route/entity bindings;
- target pages;
- audience segments;
- frequency state;
- dismiss state;
- analytics events;
- locale/content overrides;
- active schedule unless network enforced.

Network enforced security notices are separate from marketing experiences and require dedicated policy.

---

# 10. F08 Experimentation

Assignment identity includes experiment scope.

Site experiment:
- users on site A assigned independently from same-key site B.

Network experiment options:
- target sites;
- site-stratified allocation;
- shared subject across sites only with explicit identity/consent model;
- site as analysis dimension;
- exposure event site scope;
- rollout per-site percentage;
- kill switch all/selected sites;
- no site can edit network canonical hypothesis/metric unless delegated.

A network template copied into sites is not one experiment; copied experiments have independent result populations.

---

# 11. F09 Documents

Template can be network library.

Generated document ownership follows primary source resource:
- site order/application/case → site document;
- network-owned policy/contract → network document.

Protected Asset scope required.

Numbering sequence scope options:
- per-site;
- network-global only explicit;
- per-document-class/site;
- external numbering adapter.

Network-global numbering requires concurrency evidence and cannot be simulated with per-site options.

---

# 12. F10 Sync / ETL

Sync Definition default site-owned.

Network coordinator can:
- use a network Connection without secret reveal;
- fan out child site syncs;
- aggregate explicit site data into network destination;
- distribute network master data into selected sites under mapping/ownership rules.

Every cursor/checkpoint is keyed by:
- sync definition;
- source scope;
- destination scope;
- provider/profile;
- partition if applicable.

Inbound payload `site_id` is data only until trusted Connection/routing mapping authorizes target.

Clone:
- sync paused by default;
- cursors not reused blindly;
- external destination identity remap required.

---

# 13. F11 Geo / Territory

Site locations site-owned.

Network territory Definition use cases:
- shared sales regions;
- service zones;
- country/region taxonomy;
- central store network.

Options:
- network territory read/use rights;
- site local subterritories;
- site override of labels/data only if allowed;
- network geometry revision;
- dependent site count;
- coordinate/privacy policy remains resource/site-specific.

Network shared geocoder credential delegated through Connection/Vault without secret visibility.

---

# 14. F12 AI Gateway

Provider credential scope:
- site-owned provider Connection;
- network-owned provider Connection delegated to sites;
- WPE-controlled remote provider only through explicit account/service architecture.

Delegation options:
- allowed sites;
- allowed model profiles;
- allowed task classes;
- per-site token/cost budgets;
- per-site rate limits;
- sensitivity ceiling;
- provider region;
- fallback permissions.

AI Task Definition:
- site task;
- network template;
- network task only for network-owned data/action.

Context isolation:
- target site mandatory;
- network model connection does not grant network data access;
- Knowledge retrieval uses same site/network Policy;
- cached model/retrieval results include scope;
- conversation history scope-bound.

Usage reporting can aggregate network costs without exposing site prompt/data content.

---

# 15. Expanded scope mapping truth

- Original module/platform surfaces: **31/31 Multisite behavior mapped** in the existing canonical matrix.
- Expanded universal foundation candidates: **12/12 Multisite behavior mapped** here.
- Combined planned surface mapping: **43/43 logical scope behavior mapped**, if F01–F12 are accepted into canonical product scope.
- Exact physical topology/runtime Multisite certification remains **0** for new foundations.
- No code/runtime is authorized.

## Development gate

This matrix authorizes no cross-site queries, shared ledgers, network bookings, analytics aggregation, AI provider delegation, Blueprint rollout or other executable Multisite behavior.