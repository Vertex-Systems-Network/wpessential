# WPEssential — Canonical Option Ownership Index — 56 Surfaces

Status: **Current option-map authority / planning-only / no development authorization**  
Date: **2026-08-29**

## 1. Purpose

`OPTION-INVENTORY.md` is a useful detailed historical inventory for the original module set, but it predates later accepted surfaces. This index supplies the **current 56-surface option ownership map** without copying thousands of already-planned option rows into a second source of truth.

For each surface this index declares:
- primary option families;
- canonical semantic/storage/execution owner;
- cross-module delegates;
- authoritative detailed specification/evidence source.

Rule: detailed option values/defaults/validation remain in the linked exhaustive spec + exact evidence protocol. This index decides **where an option belongs**.

## 2. Canonical option index

| # | Surface | Canonical option families | Canonical owner / delegates | Authoritative detail |
|---:|---|---|---|---|
| 1 | CPT Builder | identity/labels, registration visibility, supports, admin menu, archive/rewrite, REST, capabilities, taxonomy bindings, dependency/revisions/export | Surface 1 owns CPT definition; terms/content/fields remain peer/WP owned | `MODULE-CATALOG.md`, `MODULES/OPTION-INVENTORY.md`, CPT evidence/ADR set |
| 2 | Taxonomy Builder | identity/labels, hierarchy, object assignment, visibility/admin, rewrite/query, REST, term capabilities, default-term configuration | Surface 2 taxonomy definitions; terms WP-owned; term order -> 51 | `MODULE-CATALOG.md`, `OPTION-INVENTORY.md`, Free CPT/Tax evidence |
| 3 | Fields | group identity/location/presentation, type settings, validation, defaults, choices, storage mapping, permissions, REST/Ability, repeaters/groups, media, relation refs, secret/computed | Surface 3 authors Field Schema definitions; values stored by selected Data Source; bidirectional relation -> 4; secret -> Vault; formula -> 35 when non-trivial | `MODULES/CUSTOM-FIELDS-EXHAUSTIVE-SPEC.md`, `OPTION-INVENTORY.md`, field evidence |
| 4 | Relations | identity, endpoints, cardinality, direction, pivot schema, order, delete policy, admin management, integrity, permissions | Surface 4 owns relation semantics/edge storage; pivot field schema reuses Field Schema | `MODULES/RELATIONS-STATUS-EXHAUSTIVE-SPEC.md`, `OPTION-INVENTORY.md`, relations evidence |
| 5 | Status | WP status registration options, domain state identity, transitions, guards, required fields, permissions, history/display | Surface 5 owns transition/state semantics; Workflow17 owns surrounding side effects | `MODULES/RELATIONS-STATUS-EXHAUSTIVE-SPEC.md`, `OPTION-INVENTORY.md`, status evidence |
| 6 | Query | source, typed filters, relation traversal, projection, aggregate/group, computed refs, sort, pagination/cursor, parameters, cache, preview/explain | Surface 6 structured Query AST; search relevance ->34; persistent manual order ->51; formula ->35 | `MODULES/QUERY-BUILDER-EXHAUSTIVE-SPEC.md`, `OPTION-INVENTORY.md`, query evidence |
| 7 | Custom Tables | table identity, columns/types/defaults, indexes, schema migration, row browser/edit, safe query console, import/export, privacy | Surface 7 owns WPE table schemas/rows; relations ->4; ledger semantics ->36 | `MODULES/CUSTOM-TABLES-ADMIN-COLUMNS-EXHAUSTIVE-SPEC.md`, `OPTION-INVENTORY.md` |
| 8 | Admin Columns | target/view, column identity/source/format, sorting/filtering, inline/bulk edit, export, conditions, performance/cache | Surface 8 presentation/view definitions only; source mutation via owning Data Source | `MODULES/CUSTOM-TABLES-ADMIN-COLUMNS-EXHAUSTIVE-SPEC.md`, `OPTION-INVENTORY.md` |
| 9 | Listings | source/query binding, layout, item template, dynamic values, controls, pagination, empty/error/loading, responsive, SSR/cache/SEO | Surface 9 rendering definition; data Query6/Search34; placement38 where globally injected | `MODULES/DYNAMIC-LISTINGS-EXHAUSTIVE-SPEC.md`, `OPTION-INVENTORY.md` |
| 10 | Dashboard Widgets | existing-widget visibility/order, preset assignment, custom widget type/content, schedule/expiry, dismissal, refresh/cache, remote/iframe policy | Surface10 dashboard widget definitions; remote transport ->23; schedule ->18/Job; authorization remains Policy | `MODULE-CATALOG.md`, `OPTION-INVENTORY.md`, dashboard-widget evidence |
| 11 | Admin Menu | profiles, tree/order, labels/icons, parent/position, visibility conditions, custom links, destination redirect refs, recovery | Surface11 presentation; generic redirect execution ->44; access ->30/Policy/27 | `MODULE-CATALOG.md`, `OPTION-INVENTORY.md`, admin-menu evidence |
| 12 | Settings Pages | page identity/menu placement, tabs/sections/layout, field bindings, storage scope/autoload, permissions, frontend exposure, revisions/import/export | Surface12 settings app definitions; field semantics -> shared Fields; secrets ->Vault; font ->53; typed CSS RDX profile is declarative | `MODULES/ADMIN-DASHBOARD-MENU-SETTINGS-EXHAUSTIVE-SPEC.md`, `OPTION-INVENTORY.md`, RDX overlay |
| 13 | Frontend Dashboard | dashboard identity, route/navigation tree, visibility/access bindings, component/page content, breadcrumbs, responsive shell, account/form/CRUD embeds, states | Surface13 portal composition; records/actions stay owning surfaces | `MODULE-CATALOG.md`, `OPTION-INVENTORY.md`, frontend-dashboard evidence |
| 14 | User Profile | template assignment, sections/tabs, field refs, view/edit policy, avatar/media, account actions, public SEO, directory/listing composition | Surface14 presentation; user values WP/Data Source; password/email/session -> native secure identity; membership ->15 | `MODULES/DASHBOARD-PROFILE-ROLES-EXHAUSTIVE-SPEC.md`, PBX overlay/evidence |
| 15 | Membership | plan identity/version, lifecycle/duration/trial/grace, enrollment, benefits, entitlement derivation, access rules/overrides, teams/seats/invites, billing links/reconciliation, registration/private-site presets | Surface15 owns membership transactional semantics; users WP-owned; roles30; billing external adapter; protected files S03 | `MODULES/MEMBERSHIP-SYSTEM.md` + membership companion specs + MPR evidence |
| 16 | Builder Widgets | component identity/category, control schema/defaults, dynamic bindings, render primitives, style/responsive controls, dependencies/assets, per-builder compatibility | Surface16 Component Blueprint/adapter registration; data/render owners remain Query/Renderer/Fields | `MODULE-CATALOG.md`, `OPTION-INVENTORY.md`, builder adapter evidence |
| 17 | Forms & Workflows | form layout/fields/steps, validation, files, spam, entries/save-resume, CRUD action bindings; workflow trigger/conditions/actions/waits/branches/approval/retry/idempotency/compensation/runs | Surface17 form submission + orchestration; fields shared; state ->5; HTTP ->23; notification ->19; schedule ->18; approval profile S06 | `MODULES/FORMS-WORKFLOW-EXHAUSTIVE-SPEC.md`, workflow evidence |
| 18 | Cron | event inventory, schedule identity, recurrence/timezone, pause/resume/delete, run-now, runner health, third-party ownership warnings | Surface18 schedule semantics only; Job Service executes; domain Ability defines work | `MODULES/CRON-JOB-BUILDER-EXHAUSTIVE-SPEC.md`, `OPTION-INVENTORY.md` |
| 19 | Notifications | event/trigger ref, recipients, template/token refs, channels, conditions, priority, schedule/digest, preferences/opt-out, retry/dedupe/escalation, delivery evidence | Surface19 notification occurrence/routing; Email20 renderer; Webhook23 transport | `MODULES/NOTIFICATION-SYSTEM-EXHAUSTIVE-SPEC.md`, `OPTION-INVENTORY.md` |
| 20 | Emails | template identity/event binding, email-safe components, branding/header/footer, dynamic data, subject/preheader, locale, preview/test, revisions, provider evidence | Surface20 email render/template; delivery transport/provider may be connection/email adapter; notifications19 chooses recipients/channel | `MODULES/EMAILS-BUILDER-EXHAUSTIVE-SPEC.md`, email provider evidence |
| 21 | Chat | conversation types/initiation, participants, messages/edit/delete, attachments, read receipts, unread, moderation/block/report, retention, realtime/polling profile | Surface21 conversation/message store; files S03/Media as appropriate; notifications19 alerts | `MODULES/MESSAGE-CHAT-EXHAUSTIVE-SPEC.md`, `OPTION-INVENTORY.md` |
| 22 | REST API | endpoint identity/version/path/method, input/output schema, Query/Data Source/Ability binding, auth/Policy, pagination, cache/rate, logs, OpenAPI preview | Surface22 endpoint definition; action/query execution remains bound canonical owner | `MODULES/REST-API-BUILDER-EXHAUSTIVE-SPEC.md`, REST evidence |
| 23 | Connections/Webhooks | connection/provider/profile, Vault credential ref, OAuth lifecycle, Safe HTTP URL/host/redirect/timeouts, inbound signing/replay/rate, outbound mapping/retry/delivery/reconcile | Surface23 owns transport/connection lifecycle; Sync41 owns cursor/conflict; Workflow17 business orchestration | `MODULES/WEBHOOKS-CONNECTIONS-EXHAUSTIVE-SPEC.md`, connections evidence |
| 24 | Backup | plan/scope, include/exclude, destination, schedule ref, archive/compression/encryption profile, incremental chain, retention, verify, restore/selective restore, recovery | Surface24 backup artifact/restore truth; provider adapter via23; staging55 separate | `MODULES/BACKUP-MANAGER-EXHAUSTIVE-SPEC.md`, backup evidence/BKX |
| 25 | Reset | profile/scope, impact, restore-point requirement, exclusions, re-auth/confirmation, journal/run, health/recovery, partial/full/dev presets | Surface25 reset semantics; restore point ->24; DB maintenance48 separate | `MODULES/RESET-MANAGER-EXHAUSTIVE-SPEC.md`, reset/RSX evidence |
| 26 | Import/Export | configuration package selection, dependency manifest, data source/format, mappings, identity conflict, create/update/delete, dry-run, chunk/resume, run/error, export scope | Surface26 one-time package/data movement; transform45; recurring sync41; migration env55 | `MODULES/IMPORT-EXPORT-EXHAUSTIVE-SPEC.md`, import evidence |
| 27 | Protector | access/private/maintenance profiles, path/admin/login protection, rate/lockout refs, IP/proxy, login alias, security headers, emergency recovery, logs | Surface27 request/access hardening; roles30/Policy authorization; redirect44 route execution; scanner52 separate | `MODULES/PROTECTOR-EXHAUSTIVE-SPEC.md`, protector evidence |
| 28 | Media Operations | watermark rule/derivative; responsive/lazy/LCP/fetchpriority/format/CDN/placeholder; attachment replacement/supersede/restore/reference graph/cache regeneration | Surface28 media operations; reference mutation ->45; font53 separate | `MODULES/WATERMARKER-MEDIA-RULES-EXHAUSTIVE-SPEC.md`, media expansion, MDP/MRL evidence |
| 29 | XML-RPC | current state/method inventory, method-group allow/deny, pingback/auth method policy, rate/request limits, compatibility presets, logs | Surface29 XML-RPC semantics; Protector may impose security floor | `MODULES/XML-RPC-MANAGER-EXHAUSTIVE-SPEC.md`, XML-RPC evidence |
| 30 | Roles & Capabilities | role identity/clone/delete, capability matrix/provenance, assignments, compare, CPT/tax helpers, network/Super Admin, target-role policy, rescue/anti-lockout | Surface30 WordPress role/capability management; resource Policy shared; Membership15 separate | `MODULES/DASHBOARD-PROFILE-ROLES-EXHAUSTIVE-SPEC.md`, RPR evidence |
| 31 | Platform | Home/module catalog, enable/disable/dependency state, diagnostics/status, docs/changelog/support/account/license, compatibility/update health | Platform surface only; module business options stay owners | `MODULE-CATALOG.md` platform section, platform-surface evidence |
| 32 | Solution Blueprint Composer | blueprint identity/version/domain, dependencies, required/optional surfaces/adapters, variables, mapping/collision policy, install/dry-run, installed solution/drift/upgrade/detach | Surface32 composition/provenance; definitions persist in owner surfaces | `SOLUTIONS/UNIVERSAL-FOUNDATIONS-EXHAUSTIVE-SPEC.md`, F01/Blueprint evidence |
| 33 | Analytics & Journeys | event schema/source/PII/consent/sampling/dedupe/retention, tracking/session/identity link, metric definitions, funnels/cohorts/attribution, data quality/backfill | Surface33 behavioral analytics; Audit shared separately | Universal Foundations spec F02, Analytics evidence |
| 34 | Search & Indexing | index/source/document schema, analyzers/language, synonyms, typo/prefix, weights/ranking, filters/facets, pins/boosts, incremental/full indexing, backend, logs/analytics | Surface34 search/index; source Policy/Data Source6; redirect44 | Universal Foundations F03, Search evidence |
| 35 | Decision/Formula/Scoring | formula variables/types/functions, lookups/tiers, scorecards/weights, decision tables, ranking/thresholds, rounding/null policy, effective version, simulation, explain trace | Surface35 deterministic decision; S05 numeric/unit; Policy remains authority | Universal Foundations F04, Decision evidence |
| 36 | Ledger | ledger/account/type/unit/precision, posting types, negative policy, holds/expiry, source/idempotency, reversal/compensation, snapshots/reconcile, statements/retention | Surface36 append movement semantics; external settlement separate | Universal Foundations F05, Ledger evidence |
| 37 | Reservations | resource/group/calendar, weekly/exceptions, slot duration/buffers, horizon/lead time, capacity/pools, hold/expiry, waitlist, approval, cancellation/reschedule, external calendar | Surface37 scheduling/reservation; Cron18 not lock; payment external | Universal Foundations F06, Reservation evidence |
| 38 | Placement/Personalization | slot/surface/adapter, component binding, priority, schedule, audience/context/device/market, frequency/dismiss, fallback, experiment binding, SSR/client, assets/accessibility | Surface38 placement; renderer/listing owner referenced; Experiment39 optional | Universal Foundations F07, Placement evidence |
| 39 | Experiments/Rollout | hypothesis, target, variants/allocation, assignment/sticky key, duration, metric links, exclusions, mutual groups, rollout percentage, guardrails/kill, result decision record | Surface39 experimentation; Analytics33 metrics; Placement38 exposure; no authorization | Universal Foundations F08, Experiment evidence |
| 40 | Documents/Records | template/profile/page/layout, bindings/tables/sections, numbering/locale/font/media refs, PDF/HTML profile, filename/storage/access, issue/final/amend/supersede, hash/signature/time provenance, retention/share/download | Surface40 record/artifact lifecycle; source records remain owners; S03 protected delivery | Universal Foundations F09, Documents evidence |
| 41 | Sync/ETL | source/destination connection refs, selection, field/key mapping, transforms, direction, cursor/change capture/webhook, conflict/field authority, delete/tombstone, batch/rate/retry/dead-letter/reconcile | Surface41 recurring sync semantics; connection23; transform45; import26 distinct | Universal Foundations F10, Sync evidence |
| 42 | Geo/Territory | address/location schema, coordinate precision/CRS, geocoder/reverse, map provider, distance/radius/box, polygon/zone/territory hierarchy, timezone, privacy precision, provider quota/cache | Surface42 geo semantics; provider via23; Query/Search consumers | Universal Foundations F11, Geo evidence |
| 43 | AI Gateway/Copilot | provider/model, task/prompt/version, knowledge/retrieval/citations, allowed data, policy/task class, structured output, tool/Ability allowlist, budgets/rates, evals, retention/fallback/approval | Surface43 AI governance; Vault secret; Search34 retrieval; same Ability/Policy | AI/Gateway/Prompt docs, AIP evidence |
| 44 | Redirect/Routing | rule identity/source/target/status, match variants, conditions, HTTP status, priority/order, chain/loop, query preservation, schedule, import, simulation/logs | Surface44 generic redirect semantics; access policy stays27/30 | `MODULES/URL-REDIRECTION-ROUTING-EXHAUSTIVE-SPEC.md`, RDR evidence |
| 45 | Search/Replace/Transform | plan/scope, source types, match mode, typed transformations, serialization safety, dry-run/diff, batch/lock, backup/recovery, rollback, logs | Surface45 transform semantics; consumers 55/28/26/48 | `MODULES/SEARCH-REPLACE-DATA-TRANSFORMATION-EXHAUSTIVE-SPEC.md`, SRT evidence |
| 46 | Dummy Data/Fixtures | fixture project/profile, target sources/schemas, field generators, relations/graphs, deterministic seed, locale/volume, dependencies/order, media strategy, preview/package/cleanup | Surface46 synthetic fixtures only; target writes via Data Source owners | `MODULES/DUMMY-DATA-FIXTURE-GENERATOR-EXHAUSTIVE-SPEC.md`, DMY evidence |
| 47 | Link Health | scan profile/local-remote-hybrid, source occurrence discovery, URL normalization, result status/provenance, ignore/snooze, saved views, notifications, recheck, Fix Plans | Surface47 detection; fixes execute 44/45/28; Safe HTTP23 | `MODULES/LINK-HEALTH-BROKEN-LINK-CRAWLER-EXHAUSTIVE-SPEC.md`, LNK/LHX evidence |
| 48 | DB Maintenance | health/candidate profile, revision/transient/orphan/session/table/index cleanup classes, owner hook, dry-run/impact, schedule/job, retention, optimize/repair profile, history | Surface48 physical maintenance only; domain lifecycle owner authorizes deletion; reset25 separate | `MODULES/DATABASE-MAINTENANCE-CLEANUP-EXHAUSTIVE-SPEC.md`, DBM evidence |
| 49 | Admin Theme | theme profile/token/color/typography ref/roundness/density, admin/login branding, assignment precedence user/role/site/network/environment, accessibility, revisions/import, compatibility | Surface49 wp-admin visual system; font53; menu11; no auth effect | `MODULES/ADMIN-THEME-BRANDING-EXHAUSTIVE-SPEC.md`, ATM evidence |
| 50 | Safe Script/Tag | snippet type/source, browser placement, URL/origin, inline privilege, conditions, consent category, CSP/SRI/nonce/hash, environment, dependencies/order, preview/conflict, revision/pause/import | Surface50 browser runtime only; no PHP/server; redirects44 separate | `MODULES/SAFE-SCRIPT-TAG-CODE-INJECTION-EXHAUSTIVE-SPEC.md`, STM/HFC evidence |
| 51 | Content Order/Sequence | sequence definition/context/target, native menu_order vs independent store, hierarchy/sibling mode, term order, drag/keyboard, missing membership behavior, query/listing binding, concurrency/revisions | Surface51 persistent sequence; duplicate record `DUP` uses source owner; query sort6 | `MODULES/CONTENT-ORDER-SEQUENCE-EXHAUSTIVE-SPEC.md`, ORD evidence |
| 52 | Security Scanner | baseline/source provenance, checksums, core/plugin/theme/custom file classification, signatures/heuristics, vulnerability feeds, reputation, confidence/severity/suppression, quarantine/repair plan, post-hack workflow, hardening report | Surface52 integrity evidence; Protector27 request hardening; repair high-risk | `MODULES/SECURITY-INTEGRITY-SCANNER-EXHAUSTIVE-SPEC.md`, SEC evidence |
| 53 | Fonts/Typography | family/variant/face/axis metadata, upload/provider, format/conversion, licensing/provenance, typography assignments, theme/builder refs, local/external detection, preload/display/subset/fallback/locale/cache | Surface53 font registry/delivery; legal authority external; theme56/admin49 consume refs | `MODULES/FONT-TYPOGRAPHY-DELIVERY-EXHAUSTIVE-SPEC.md`, FNT/UAF evidence |
| 54 | User Stores | store type/identity, user/guest ownership, add/remove/toggle/idempotency, ordering/meta, limits/expiry, merge-on-login, sharing/team, query/listing/REST, privacy | Surface54 favorites/etc.; Woo cart/order/stock remains A01/Woo | `MODULES/USER-DATA-STORES-FAVORITES-COLLECTIONS-EXHAUSTIVE-SPEC.md`, UDS evidence |
| 55 | Staging/Clone/Migration | environment topology/identity, create clone, side-effect quarantine, DB/files selection, URL/path/serialized mapping, transfer/checkpoint, push/pull, drift/conflict, cutover/verify/recovery, Multisite conversion | Surface55 environment lifecycle; Backup24 artifacts; Transform45; Connections23; Import26 | `MODULES/STAGING-CLONE-MIGRATION-EXHAUSTIVE-SPEC.md`, STG/MIG evidence |
| 56 | Theme Workspace | theme identity/parent, analyzer, child create/metadata/enqueue, CSS selector workspace, theme.json, template/part overrides, asset/font refs, parent drift, preview/compare, ZIP import/export, activation/recovery/Multisite | Surface56 frontend theme source/declarative workspace; font53/media28; Safe Script50; no live PHP editor | `MODULES/THEME-WORKSPACE-CHILD-THEME-CUSTOMIZATION-EXHAUSTIVE-SPEC.md`, THM evidence |

## 3. Shared-service option owners

These options appear across many screens but remain shared semantics:

- Scope/context -> `S04 Context Resolver` + Multisite model.
- Capability/Policy -> shared Policy; roles authored in Surface30.
- Conditions -> Conditional Logic Engine.
- Dynamic values/tokens -> DVR/Renderer.
- Secret references -> Vault.
- Job retry/lock/progress/cancel -> Job Service.
- Cache -> shared Cache contract.
- Rate/abuse -> shared Rate Limit/Abuse contract.
- Assets -> Asset Registry.
- Approval definitions -> `S06` + Workflow execution.
- Simulation/replay -> `S01`.
- Multi-step external/local saga/reconcile coordination -> `S02`.
- Protected/private asset delivery -> `S03`.
- Money/decimal/unit -> `S05`.

Local modules can render these controls through shared owner components but cannot create incompatible local semantics.

## 4. Option duplication test

An apparent duplicate is acceptable only if one of these is true:

1. same semantic owner component rendered in different UI context;
2. one surface stores a **reference** to another owner's definition;
3. local option is presentation-only and does not claim shared domain semantics;
4. adapter-specific option is genuinely provider-specific and still maps to a canonical owner contract.

It is a defect if:
- two surfaces store independent copies of the same rule with undocumented precedence;
- two engines can execute the same mutation independently;
- one consumer can bypass the owner Ability/Policy;
- an import/parity overlay persists external executable semantics rather than normalizing them;
- disabling one module leaves a hidden clone engine running in another.

## 5. Implementation-time generated option manifest

For every implementation milestone, generate a machine-readable option manifest from this index + detailed specs containing:

`surface_id, option_key, group, semantic_owner, storage_owner, execution_owner, scope, ui_routes, control_type, default, validation, policy_gate, side_effects, dependencies, consumers, import_export_owner, evidence_fixture_ids`.

No production option may ship without a manifest row.

## 6. Historical option inventory rule

`docs/MODULES/OPTION-INVENTORY.md` remains valid for its detailed original-module content, but this file is the current **56-surface routing/index authority**. A future consolidation may mechanically move/normalize old detailed rows into generated per-surface manifests; implementation must not infer current ownership solely from historical numbering in the old inventory.
