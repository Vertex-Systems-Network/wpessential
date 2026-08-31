# WPEssential — WordPress Multisite Scope, Ownership & Isolation Model

Status: **Phase 0 planning / logical architecture accepted candidate / no runtime implementation authorized**  
Review date: **2026-08-28**  
Related: ADR-0014, ADR-0032, ADR-0036, ADR-0048, ADR-0049, ADR-0059, P-001, P-003, P-004, P-012, P-013.

## 1. Purpose

WPEssential is a platform, not a single-site-only feature plugin. Multisite therefore cannot be treated as a later compatibility patch.

WordPress Multisite has important split ownership:
- sites share one WordPress installation and a global user table;
- individual sites retain separate content/options tables;
- user membership/roles/capabilities are evaluated for a specific site;
- Super Admin/Network Admin authority exists above individual site administration;
- plugins can be activated per-site or network-wide;
- switching blog context changes DB/cache context, not authorization by itself.

WPE must make that scope explicit in every definition, runtime record, authorization decision, cache entry, background job, secret, entitlement, audit event and destructive operation.

The central rule is:

> **Code being network-active does not make WPE data, definitions, roles, Memberships, secrets or permissions automatically network-global.**

---

# 2. WordPress facts used by this model

Current official WordPress documentation establishes:

1. A Multisite network contains separate sites managed by one installation. Site content uses separate database tables while users are shared network-wide.
2. WordPress distinguishes blog/site tables from global and multisite-global tables.
3. Site administrators have reduced authority compared with Network Admin/Super Admin.
4. Plugins can be active on one site or network-active across all sites.
5. `switch_to_blog()` switches site database/cache context; plugin/theme PHP code itself does not magically reload for the target site.
6. Every `switch_to_blog()` must be paired with `restore_current_blog()` to keep the switching stack/context correct.
7. `current_user_can_for_site()` / `user_can_for_site()` are the explicit site-targeted capability APIs in modern WordPress.
8. `get_site_option()` is a network-wide option API in Multisite despite its historical name.
9. `get_sites()` / `WP_Site_Query` is the supported site enumeration/query path.

References reviewed:
- https://developer.wordpress.org/advanced-administration/multisite/
- https://developer.wordpress.org/advanced-administration/multisite/administration/
- https://developer.wordpress.org/reference/functions/switch_to_blog/
- https://developer.wordpress.org/reference/functions/restore_current_blog/
- https://developer.wordpress.org/reference/functions/current_user_can_for_site/
- https://developer.wordpress.org/reference/functions/user_can_for_site/
- https://developer.wordpress.org/reference/functions/get_site_option/
- https://developer.wordpress.org/reference/functions/get_sites/
- https://developer.wordpress.org/reference/classes/wpdb/tables/

---

# 3. Canonical WPE scope primitives

Every scope-aware WPE resource has an explicit logical scope.

## 3.1 Scope types

Initial canonical values:
- `site`
- `network`

Future values such as tenant/external-workspace are **not** added until a separate architecture decision requires them.

## 3.2 Scope coordinates

Conceptual scope object:

```text
scope_type
network_id
site_id|null
```

Rules:
- `site` requires `network_id + site_id` in Multisite;
- `network` requires `network_id` and `site_id = null`;
- single-site WordPress can normalize to a site-scoped logical context without pretending a Multisite network exists;
- current request/blog context may provide a default UX context, but storage/authorization records persist explicit scope coordinates;
- a bare object UUID is never assumed unique enough for authorization unless its resource repository guarantees global UUID identity and scope is resolved alongside it.

## 3.3 Scope must not be inferred from table name

Logical scope and physical storage are separate decisions.

A site-scoped record may physically live in a global WPE table with a `site_id` discriminator, or in a per-site table, depending on future P-004/runtime evidence.

Therefore:

> **`scope_type=site` is a product/security contract. Physical table topology is an implementation/evidence contract.**

This prevents premature coupling of product semantics to one database strategy.

---

# 4. Default scope policy

Unless a module's architecture explicitly says otherwise, **user-created WPE application definitions are site-scoped by default**.

Examples:
- CPT definitions;
- taxonomy definitions;
- field groups;
- relation definitions;
- queries;
- custom table definitions;
- admin columns;
- statuses;
- listings/templates;
- dashboard routes/pages;
- dashboard widgets;
- settings pages;
- profile layouts;
- Membership plans/rules;
- forms/workflows;
- cron/schedule definitions;
- notification rules;
- email templates;
- chat/workspace definitions;
- REST endpoint definitions;
- connections/webhook definitions;
- import/export mappings;
- Backup plans;
- Protector policies;
- Watermark rules.

Network scope is **opt-in and privileged**, not inferred from network activation.

---

# 5. Network-scoped WPE resources

Network scope is appropriate only where one network authority intentionally owns the resource.

Candidate network-scoped resource classes:
- WPE network module policy;
- network defaults/templates/blueprints;
- network-enforced settings/policies;
- network diagnostics/health summary;
- network-level audit aggregation;
- network-level Backup/Restore definition;
- explicitly shared provider connection/profile;
- explicitly shared Secrets Vault slot/key policy;
- network-level maintenance/Job coordinator;
- network-wide import/export package;
- network-level role/capability policy where WordPress semantics permit it;
- product/license activation metadata representing the installation/network;
- reusable network template that can instantiate site-scoped definitions.

Important distinction:

A network template/blueprint does **not** automatically mean one mutable live definition directly controls every site forever.

WPE must choose explicit semantics such as:
- copy/instantiate into site;
- inherit read-only default;
- linked/pinned revision;
- network enforced policy;
- scheduled propagation.

The UI must show which model applies.

---

# 6. User identity, site membership and authorization

## 6.1 Global user identity is not global site authority

Normal WordPress Multisite shares user/user-meta identity at network level, but user membership/roles/capabilities remain site-aware.

Therefore:
- a WordPress user existing in the network does not automatically gain WPE access to every site;
- a user's role on Site A does not imply the same role/capabilities on Site B;
- a WPE Membership on Site A does not grant Site B entitlements by default;
- a WPE Profile view/edit policy must evaluate target site where site-scoped;
- network-level actions require separate network authority.

## 6.2 Site-targeted authorization

Cross-site action checks use explicit target-site capability APIs such as `current_user_can_for_site()` / `user_can_for_site()` plus WPE Policy.

Do not use this unsafe pattern conceptually:

`switch_to_blog(target); current_user_can(...); assume current context == authorization model`

Instead authorization input is explicit:

```text
actor
ability/capability
resource UUID
resource scope
network_id
site_id
object/resource arguments
```

WordPress capability check and WPE Policy both receive the intended target scope.

## 6.3 Super Admin / Network Admin

Super Admin has elevated network authority, but WPE still applies:
- explicit WPE capabilities/Policy;
- destructive-operation confirmation;
- audit;
- recovery-principal rules;
- secrets/PII boundaries;
- module-specific restrictions.

`Super Admin` is not interpreted as “skip every WPE policy.”

## 6.4 Site admin

A site administrator cannot perform network-wide WPE mutations solely because WPE is network-active.

Examples blocked without network authority:
- change network module policy;
- read another site's Vault secret;
- run Network Reset;
- export all sites/users/configuration;
- modify network-wide provider connection;
- grant cross-site Membership;
- run destructive job fan-out across sites.

---

# 7. Site switching contract

`switch_to_blog()` is a context tool, not an ownership/auth primitive.

## 7.1 Allowed use

Use it only when a WordPress API/storage operation genuinely needs target-site context.

## 7.2 Pairing invariant

Every successful switch must be paired with `restore_current_blog()` even on exceptions/errors/early returns.

Implementation direction after consent:
- use a small scope/context guard abstraction;
- pair restoration in a `finally`-equivalent control path;
- assert no dirty switching stack at Job/request boundary in development/diagnostic mode;
- never rely on nested ad-hoc switches scattered across modules.

## 7.3 What switching does not do

Switching site context does not:
- reload plugin/theme PHP for another site;
- grant actor access to that site;
- transform site-scoped UUID into network scope;
- permit reading network/shared secrets;
- guarantee caches created before switch are safe for target site;
- imply network-global transactionality.

---

# 8. Definition Repository scope

Definitions carry explicit logical scope.

Candidate logical fields:
- definition UUID;
- module/type;
- `scope_type`;
- `network_id`;
- `site_id|null`;
- current/published revision pointers;
- owner/provenance;
- state/tombstone.

Dependency edges carry scope-aware source/target identities.

Cross-scope dependency rules:
- site definition → same-site definition: normal;
- site definition → approved network template/default/resource: allowed according to resource policy;
- site definition → other-site private definition: denied by default;
- network definition → one site resource: explicit target binding only;
- network definition → all sites: requires explicit propagation/inheritance semantics.

Exact global/per-site Definition Repository DDL remains P-004 and is **not decided here**.

---

# 9. Settings scope

ADR-0036 remains authoritative.

Multisite composition:

`network default → optional network enforced/locked policy → site override where allowed`

Rules:
- site value and network value are stored/identified separately;
- `get_site_option()`/network option semantics are treated as network-wide, not a site value despite historical naming;
- site admin can see inherited/locked indicators;
- locked network value cannot be overridden by site admin;
- changing network default does not silently overwrite an existing site override unless policy explicitly says so;
- network secret settings remain Vault references, not plain options.

---

# 10. Secrets Vault scope

Every secret has ownership/scope metadata separate from encrypted envelope material.

Examples:
- site-scoped SMTP credential;
- network-shared Backup S3 credential;
- network WPE account OAuth token;
- site-specific third-party API key;
- provider signing/webhook secret.

Rules:
- a site-scoped module cannot read a network-scoped secret unless Policy explicitly grants *use* of that secret/profile;
- “can use connection” does not mean “can reveal credential”;
- network-shared credential usage is audited per target site/action;
- browser/admin UI never receives raw Vault secret because actor can use a shared connection;
- copying a site does not copy/rebind live network secrets implicitly;
- site export excludes secret plaintext and records credential placeholders/references according to package policy.

Exact Vault physical storage remains P-005.

---

# 11. Module activation and policy

WordPress network activation means plugin code is active for all sites. WPE module/product state is separate.

Candidate network policy per module:
- `allowed`;
- `default_enabled`;
- `forced_enabled`;
- `forced_disabled`;
- optional site allow/deny selection.

Per-site module state:
- enabled;
- disabled;
- inherited/locked by network policy.

Rules:
- network-active WPE does not automatically enable every WPE module on every site;
- a site admin cannot override forced network state;
- disabling a module on one site does not delete network or other-site definitions/data;
- network policy changes use dependency impact analysis and audit;
- Pro entitlement/site-count policy composes separately from module activation.

---

# 12. Jobs and cron in Multisite

Every Job/Schedule has explicit scope.

Candidate Job scope fields:
- `scope_type`;
- `network_id`;
- `site_id|null`;
- source definition/job type;
- actor/system authority;
- resource/concurrency key.

## 12.1 Site job

Runs for exactly one target site.

Before any site-context operation:
- target site is re-resolved/validated;
- archived/deleted/spam/deactivated state is considered according to Job type;
- authorization/system authority is checked;
- site context is entered safely;
- context is always restored.

## 12.2 Network fan-out job

Do not run one giant request/worker loop over the entire network.

Preferred logical pattern:

`Network Coordinator Job → paginated site enumeration → bounded Site Child Jobs → aggregate result`

Rules:
- use `get_sites()`/`WP_Site_Query` with bounded pagination/IDs;
- coordinator checkpoints progress;
- child jobs carry explicit site ID;
- one site failure does not corrupt coordinator/network state;
- retry is per site/job;
- concurrency/backpressure uses ADR-0059;
- no business dependency on Action Scheduler queue order;
- JobService scope remains WPE-owned even if Action Scheduler backend is selected.

## 12.3 Multisite Action Scheduler warning

ADR-0068 remains authoritative: Action Scheduler does not become WPE's network orchestration model merely because its tables/runtime exist.

P-003 must prove site/network isolation and runner behavior.

---

# 13. Events, Abilities and API scope

Every WPE Event envelope includes:
- event ID/type/schema;
- network ID;
- site ID or network scope;
- resource identity;
- actor/system authority source;
- occurred/received timestamps.

Ability invocation includes explicit target scope.

REST/API rules:
- site endpoint cannot mutate another site by passing a raw site ID unless the Ability explicitly supports cross-site scope and Policy authorizes it;
- network endpoints live in separately authorized namespace/resource class;
- object-level Policy always uses target scope;
- API cache keys include scope;
- signed/webhook source facts are normalized into the correct scope before business dispatch.

---

# 14. Cache isolation

Every cache key for site-owned state includes site/network scope or is resolved through a cache group whose isolation semantics are explicitly proven.

Critical examples:
- Membership access decisions;
- protected file authorization;
- Query/Listings output;
- Settings inheritance;
- Definition compiled descriptors;
- REST output;
- Dashboard render;
- user profile permissions;
- Notification recipient resolution.

Rules:
- Site A's cached allow decision must never satisfy Site B;
- network-level cache is allowed only for truly network-scoped immutable/shared resource;
- access-generation/invalidation counters are scoped;
- switching blogs does not by itself make already-held in-memory WPE cache objects safe;
- cross-site cache leakage is a **critical security failure**.

---

# 15. Membership in Multisite

Default Membership scope is **site**, despite network-global WordPress user IDs.

Candidate uniqueness identity includes:

`network_id + site_id + user_id + plan/source identity`

Rules:
- Plan on Site A is not Plan on Site B;
- Enrollment/Entitlement from Site A does not authorize Site B;
- billing source mapping is site-scoped unless an explicit network Membership product exists;
- site role sync changes only the intended site's roles;
- deleting/resetting a site does not delete the global WordPress user by default;
- protected files/listings/dashboard routes include site scope in authorization/cache;
- team seats are site-scoped by default.

## 15.1 Network-wide Membership

Not implied by Multisite.

If later required, it becomes an explicit feature/profile with:
- network Plan;
- site coverage selection;
- per-site entitlement materialization or network policy evaluation;
- billing/seat implications;
- migration/privacy/export semantics;
- separate UI and acceptance tests.

No network Membership implementation is accepted by implication here.

---

# 16. User Profile / identity changes

Global identity-sensitive fields remain protected because changes can affect the shared WordPress account across the network.

Examples:
- user email;
- password;
- sessions;
- Application Passwords;
- Super Admin/network authority.

Site Profile Builder cannot treat those as ordinary site custom fields.

Site-specific profile fields/layouts can remain site-scoped.

When a site UI initiates a global identity change, WPE must clearly label the network-wide impact and use the protected identity action class from ADR-0030.

---

# 17. Role & Capability Manager

Roles are treated as site-scoped WordPress authorization configuration unless the operation explicitly concerns network/Super Admin authority.

Rules:
- role edit on Site A does not silently edit Site B role definitions;
- recovery-principal invariant from ADR-0032 is evaluated at target scope;
- Super Admin changes are a separate high-risk network action;
- site admin cannot grant itself network authority;
- cross-site role cloning/import is an explicit mapping operation;
- site deletion never equates to deleting a user from the network.

---

# 18. Custom Tables and runtime data

Logical ownership comes first.

## Default

A user-created Custom Table definition/data source is site-scoped unless created through an explicit network-level advanced workflow.

## Physical alternatives remain open

Possible implementations for WPE platform services may include:
- one network/global WPE table with `network_id/site_id` discriminators;
- per-site WPE tables;
- hybrid based on data class and query patterns.

This ADR intentionally does **not** choose between them.

Decision criteria remain:
- query/index performance;
- site deletion/restore;
- network scale;
- migrations;
- transaction/concurrency;
- backup/restore boundaries;
- privacy/export;
- table-count overhead;
- global/shared data needs.

P-004 and module-specific physical evidence own this decision.

---

# 19. Backup in Multisite

Backup scope is explicit:
- `site_backup`;
- `network_backup`.

## 19.1 Site Backup

A site backup includes only the selected site's owned state plus explicit references needed for restoration.

It must **not** silently overwrite shared/global users, network settings or other sites during restore.

Candidate content:
- target site's blog tables/content/options/meta;
- site uploads/media topology;
- site-scoped WPE definitions/runtime data;
- references to shared users/identities where needed without treating user table as site-owned;
- site-scoped secrets as placeholders or encrypted recoverable material according to Backup/Vault policy;
- theme/plugin compatibility metadata.

## 19.2 Network Backup

Higher-risk scope that may include:
- network/global WordPress tables;
- shared users/usermeta;
- each selected site's blog tables;
- network + site WPE data;
- uploads topology;
- network settings/policies;
- product/service activation references according to restore policy.

Network Backup/Restore has a separate disaster certification profile from ordinary site restore.

## 19.3 Restore rules

- site restore cannot corrupt other sites/shared users by default;
- site clone uses explicit identity/site-ID/domain/path remap;
- network restore requires Super Admin/recovery principal + higher confirmation + verified Backup + durable journal;
- external provider Remote Copy remains scope-aware;
- C3/C4 provider certification does not automatically certify every Multisite restore mode; P-013 needs explicit Multisite fixtures.

---

# 20. Reset in Multisite

Reset has distinct action classes:
- Site Reset;
- Network Reset.

## Site Reset

Can only mutate the target site's owned state unless user explicitly selects a network-shared object through an authorized separate action.

Defaults:
- keep global users;
- do not mutate other sites;
- preserve network module policy;
- preserve unrelated network connections/secrets;
- take scope-correct restore point first.

## Network Reset

Extremely high risk:
- Super Admin/network WPE capability required;
- recovery principal invariant;
- verified Network Backup/restore point;
- durable journal;
- impact preview showing every site/global table/resource;
- no one-click accidental reuse of Site Reset UI.

---

# 21. Import / Export in Multisite

Portable package manifest includes source scope:
- network identity/fingerprint;
- source site identity where applicable;
- resource scope per object;
- dependencies;
- stable UUIDs;
- site/network remap requirements.

Rules:
- importing site package into another site remaps site-scoped references;
- site package cannot overwrite network policy/resources without explicit network privilege and compatible package type;
- network package is a separate high-risk import mode;
- source local DB IDs are never treated as portable identity;
- user mapping is explicit because user IDs may differ between source/target networks;
- secrets remain placeholders/encrypted package artifacts according to export policy.

---

# 22. Remote Service and commercial entitlement

WPE remote-service product activation and site-count licensing need two identities:
- installation/network activation identity;
- site activation identity where commercial plan counts individual sites.

Rules:
- use pseudonymous stable identifiers, not domain/user data as primary secret identity;
- network account connection does not opt every site into telemetry;
- public/no-identifier privacy rules from ADR-0060 remain;
- site clone must not silently duplicate a live commercial site activation identity;
- site transfer/network clone is a future evidence scenario;
- Pro product entitlement never becomes website Membership authorization;
- disconnected site/network semantics are explicit.

Exact service schema remains S-001…S-006 evidence-gated.

---

# 23. Network Admin UX

WPE can expose a dedicated Network Admin surface only in Multisite and only to appropriate authority.

Candidate information architecture:
- Network Overview;
- Sites / WPE Status;
- Module Policy;
- Network Defaults / Enforced Settings;
- Shared Connections;
- Network Jobs;
- Network Backup;
- Network Audit;
- Diagnostics;
- Product/License site allocation.

Site-admin UI shows:
- inherited value badges;
- locked network policy;
- source of shared connection/default;
- site-specific module state;
- no inaccessible network secret/details.

Network UI and site UI are separate authorization surfaces, not the same screen with hidden buttons.

---

# 24. Site lifecycle

WPE must react deliberately to WordPress site lifecycle events.

Candidate lifecycle states include:
- initialized/created;
- active;
- archived/deactivated;
- spam;
- deleted/uninitialized.

Planning requirements:
- new site can receive network defaults/module policy without copying unsafe secrets automatically;
- deletion/uninitialization cleans or tombstones site-owned WPE runtime according to retention/recovery policy;
- network/shared resources remain untouched unless explicitly owned by the deleted site;
- pending jobs for deleted site are cancelled/terminalized safely;
- deleted site IDs are not immediately treated as reusable WPE scope identities;
- audit history preserves deletion provenance according to retention;
- Backup/restore can recreate/remap site scope according to explicit workflow.

Exact lifecycle hook wiring remains implementation evidence.

---

# 25. Large-network behavior

WPE must assume networks can contain many sites.

Rules:
- no unbounded `get_sites()` all-sites array for network-wide heavy operations;
- paginate site IDs;
- use coordinator + bounded child Jobs;
- network admin list screens paginate/filter;
- counts/aggregates can be asynchronously materialized where needed;
- network-wide migration/Backup/Reset shows progress/checkpoints;
- one slow/broken site does not block all network work indefinitely;
- concurrency is controlled by JobService resource keys/backpressure;
- cache invalidation is scoped rather than global flush whenever possible.

Exact large-network budgets require runtime benchmarks.

---

# 26. Audit model

Every relevant audit event contains:
- network ID;
- site ID or network scope;
- actor user/system identity;
- actor authority source (site capability/network/Super Admin/system);
- originating admin/site context;
- target scope;
- ability/action;
- resource identity;
- result;
- request/job correlation;
- safe metadata.

Network fan-out destructive operation:
- one parent network audit event;
- child per-site events;
- aggregate completion/partial failure record.

This allows investigation without guessing which site was affected.

---

# 27. Privacy/export/erasure

Shared user identity creates special privacy boundaries.

Rules:
- site erasure/export must distinguish site-owned personal data from global account data;
- Membership/Form/Chat/Profile data exports include only authorized target scope;
- deleting Site A's WPE data does not erase Site B data for the same global user;
- global user/account erasure must consider every WPE site/network data source according to WordPress privacy workflows and WPE retention rules;
- Support/remote service data remains separately governed by ADR-0060.

---

# 28. Failure/recovery invariants

Critical invariants:
1. Site A data cannot leak to Site B through cache/query/ability/job context.
2. Site admin cannot gain network authority because WPE is network-active.
3. `switch_to_blog()` never substitutes for Policy/capability check.
4. Every switch is restored.
5. Network job fan-out is bounded/checkpointed.
6. Site reset/restore cannot silently mutate shared/global user/network data.
7. Deleting a site does not automatically delete the shared WordPress user.
8. Site Membership never grants another site by default.
9. Network shared secret usage never reveals plaintext credential to site admin.
10. Network template/default propagation is explicit and revisioned.

Violation of 1–3 or cross-site authorization/cache isolation is classified **Critical**.

---

# 29. Future executable Multisite evidence — NOT AUTHORIZED

After explicit owner development consent, minimum fixtures include:

## Topology/platform
- single-site WordPress regression;
- Multisite subdirectory;
- Multisite subdomain/domain-mapped profile where relevant;
- network activation vs per-site activation;
- main site vs subsite;
- custom users/usermeta table compatibility if supported.

## Authorization
- Super Admin;
- network admin capability path;
- Site A Administrator;
- user member of A but not B;
- user with different roles A/B;
- explicit `current_user_can_for_site()` meta-capability checks;
- cross-site Ability attack attempts.

## Scope/data/cache
- Definition same UUID-like labels across A/B;
- site/network Settings inheritance/locks;
- Vault site/network credential usage;
- Query/Listings cache isolation;
- Membership revoke/cache isolation;
- REST/API site-ID tampering;
- Chat/private asset cross-site access;
- Custom Tables/data source isolation.

## Context switching
- nested site switch;
- exception after switch;
- dirty-stack detection;
- cache context restoration;
- deleted/archived target site.

## Jobs
- coordinator pagination;
- hundreds/thousands synthetic site records as permitted by test env;
- one site failure;
- worker crash;
- retry;
- duplicate coordinator;
- cancellation;
- Action Scheduler backend coexistence under P-003.

## Lifecycle
- new site initialization;
- archive/spam/deactivate/reactivate;
- site delete/uninitialize;
- pending WPE jobs/data cleanup/tombstone;
- restore/recreate/remap.

## Backup/Reset
- site-only Backup/Restore without overwriting global users/network settings;
- site clone/remap;
- network Backup/Restore;
- shared uploads/user references;
- remote provider C3/C4 Multisite-specific restore profile;
- Site Reset vs Network Reset safety.

## Commercial/service
- one network + multiple licensed/unlicensed sites;
- site clone activation identity;
- network clone;
- disconnect one site vs whole network;
- remote privacy no-hidden-identifier behavior per site/network.

No fixture has been executed.

---

# 30. Implementation gate

This document fixes **logical scope and ownership semantics only**.

Still evidence-gated:
- exact Definition/global/per-site table DDL;
- JobService physical schema;
- Vault storage;
- Membership schema/cache;
- Backup physical bundle/restore mechanics;
- site lifecycle hooks;
- network admin React screens;
- performance limits;
- Action Scheduler Multisite behavior;
- service/license API schema.

**No Multisite runtime source, table, site, Job, Backup, permission test or migration has been created/executed. Explicit owner development consent under ADR-0014 is still required.**
