# WPEssential — Module Benchmark Matrix

Research snapshot: **2026-08-27**  
Purpose: ensure no WPEssential module is designed from assumptions alone. This is a minimum competitive bar, not a claim that a module is implemented.

Before implementing a module, refresh the relevant official sources and add module-specific research when needed.

| WPEssential area | Current benchmark / reference systems | Minimum bar WPEssential must meet | WPEssential differentiation target |
|---|---|---|---|
| Custom Post Types Builder | CPT UI, SCF, JetEngine | complete labels/registration args, supports, REST, rewrite, capabilities, taxonomies, import/export | dependency graph, revisions, cross-module creation, Ability/API contract, safe impact preview |
| Taxonomy Builder | CPT UI, SCF, JetEngine | hierarchical/flat, labels, object types, REST/rewrite/admin controls | same version/dependency/Ability model as CPT; first-class cross-module links |
| Custom Fields Builder | ACF, SCF, Meta Box, JetEngine | broad field set, location rules, repeaters/groups/flexible content, relationship/reference fields, options/user/term/post locations | portable typed Field Schema reused by forms/settings/tables/APIs; storage adapter + privacy/policy metadata |
| Relations Builder | JetEngine Relations, Meta Box Relationships | 1:1/1:N/N:N, posts/users/terms/custom objects, relation management/querying | first-class Relation Engine, pivot metadata, scale-aware storage, delete integrity, workflow/forms/API reuse |
| Status Manager | PublishPress Statuses | custom editorial statuses, workflow ordering, post-type/role rules, icons/labels | distinguish WP post statuses from generic domain state machines; guarded transitions + audit/workflow events |
| Custom Query Builder | JetEngine Query Builder | posts/terms/users/comments/custom tables/REST, nested filters, ordering, pagination, SQL/custom sources | provider-neutral query AST, prepared compilers, relation traversal, diagnostics/cost warnings, Ability reuse |
| Custom Tables Builder | JetEngine CCT, CustomTables, Meta Box custom tables | create custom data structures/tables, fields, CRUD/listing/import | real schema/migration ownership, indexes, relation/query integration, safe SELECT/EXPLAIN console rather than unrestricted phpMyAdmin clone |
| Admin Columns Builder | Admin Columns Pro, CPT UI Pro | drag/drop columns, sorting, filtering, inline/bulk edit, export, views | any Data Source/Query/Relation value, performance diagnostics/lazy values, policy-aware editing, dependency graph |
| Dynamic Listings / Template Builder | JetEngine Listings/Tables, CPT UI Pro display blocks | query-backed dynamic grids/lists/tables, filters/pagination, templates | one renderer across Gutenberg/shortcode/builders; typed dynamic values; SEO/server-render-first where practical |
| Dashboard Widgets Manager | Ultimate Dashboard, Adminify, WordPress Dashboard API | create/control/reorder/hide widgets and custom admin content | query/listing/form-backed widgets, role policies, schedules, audit/versioning, scoped assets |
| Custom Admin Menu Builder | Admin Menu Editor | reorder/rename/icons/hide/show, custom items, per-role views | visibility explicitly separated from authorization; reusable policy engine; recovery/export/versioning |
| Settings Page Builder | ACF Options Pages, Meta Box Settings Page | admin/submenu pages, tabs/sections, field groups, global options | shared Field Schema, secret Vault fields, revision/import/export, typed APIs/Abilities, network scope |
| Dashboard Builder | JetEngine Profile Builder, Frontend Dashboard | frontend account pages, menus/subpages, role targeting, CRUD/profile content | application shell using listings/forms/notifications/chat; server-side route/resource policy; builder-neutral content adapters |
| User Profile Builder | JetEngine Profile Builder, Frontend Dashboard | public/private profile/account pages, custom user fields, edit flows | per-role profile definitions, privacy classifications, relation/query content, shared Field Schema and policy engine |
| Builder Widgets Builder | Gutenberg block API, Elementor Widgets API, WPBakery params API | user-defined controls/rendering registered in target builders | neutral Component Blueprint compiled to adapters; explicit capability-gap matrix per builder; shortcode fallback |
| Forms & Workflow Builder | Gravity Forms, Fluent Forms, JetFormBuilder, AutomatorWP | mature fields, conditional logic, multi-step, files, notifications, entries, CRUD/integrations | form schema + workflow runtime share Field/Condition/Event/Action engines; persisted run state, retries, idempotency, approval |
| Cron Job Builder | WP Crontrol + WordPress Cron API | list/run/edit/pause/delete schedules, custom intervals, diagnostics | separate schedule from durable jobs; workflow/ability actions; runner health/system-cron guidance; no arbitrary PHP eval |
| Notification System | Better Notifications for WP + automation products | event-triggered recipient targeting, templates, delivery paths | channel-agnostic in-app/frontend/email/webhook, preferences/digests/retries/deduplication + shared workflow engine |
| Emails Builder | Better Notifications for WP, transactional email builders | customize core/new event emails, tokens, recipients, previews/test sends | dedicated email-safe component renderer + plaintext; dynamic WPEssential data; do not assume Elementor/browser HTML is email-safe |
| Message & Chat System | Better Messages | 1:1/group chat, unread/read, attachments, search, reply/edit/delete, moderation, realtime option | resource-level policies, shared-hosting-safe polling fallback, optional realtime adapter, workflows/notifications integration |
| REST API Builder | miniOrange Custom API for WP + WordPress REST API | no-code endpoints, CRUD/read data, external auth/integration options | schema-first Data Source/Ability binding, safe auth defaults, rate/CORS/idempotency, no raw SQL as default endpoint engine |
| Webhooks & Connections Manager | automation/webhook integrations in form/automation suites | inbound/outbound webhooks, auth, reusable connections | centralized Vault-backed connections, signing/replay controls, SSRF protection, retries/logs, shared use by every module |
| Backup Manager | WPvivid, UpdraftPlus-class backup tools | database/files, schedules, cloud destinations, retention, restore | protocol/provider adapters, checksummed manifest, resumable/chunked work, verified restore, provider certification tests, Reset integration |
| Reset Manager | WP Reset | full/partial reset, confirmations, snapshots/restore, plugin/theme handling | verified restore point, environment inventory, WPEssential export, typed destructive tiers, post-reset health check/audit |
| Import / Export | WP All Import/Export, JetEngine/CCT import/export | CSV/XML/JSON/spreadsheet-like mapping, update matching, large imports | two systems: config packages + data migration; UUID dependency mapping, dry-run, resumable jobs, relations/custom tables |
| Protector | Password Protected, WPS Hide Login, security hardening tools | site/path/admin protection, login URL options, access rules | authorization-centric policies, safe recovery, trusted proxy/IP rules, rate/abuse controls; URL hiding labelled as obfuscation only |
| Watermarker / Media Rules | Image Watermark, Ultimate Watermark | image/text watermark, position/opacity/scale, bulk apply/remove, min-size rules | original-preserving derivative contract, background regeneration, rule engine, preview, media/source integrity checks |
| XML-RPC Manager | Disable XML-RPC variants + WordPress XML-RPC hooks | enable/disable/method control, pingback considerations, diagnostics | accurate distinction between auth-method filter and full method surface, granular allow/deny, compatibility warnings, rate/log controls |
| Role & Capability Manager | Members, PublishPress Capabilities, User Role Editor | create/clone/edit roles/caps, multiple roles, import/export | anti-lockout, privilege-diff preview, recovery snapshot, WPEssential capability groups, multisite/Super Admin safety |
| Support / Docs / Changelog / Account | mature premium plugin account/help centers | docs, changelog, license/account, support tickets | support bundle preview/redaction/consent, remote API resilience, no forced account for Free local functionality |

## Notable research findings that changed the original assumptions

### CPT/T taxonomy market is deeper than simple registration

Current CPT UI Pro now also advertises frontend display blocks, admin list controls, filters, multisite and developer APIs. WPEssential Free cannot stop at a bare `register_post_type()` form if it expects adoption.

Reference: https://wordpress.org/plugins/custom-post-type-ui/

### Custom-table/data modeling already exists in JetEngine

JetEngine Custom Content Types create separate database tables for large structured datasets and integrate fields, admin columns, relations, forms, listings and REST. WPEssential Custom Tables must therefore compete on schema integrity/migrations/shared engines—not merely “create a SQL table from UI.”

References:
- https://crocoblock.com/knowledge-base/jetengine/how-to-create-custom-content-type/
- https://crocoblock.com/knowledge-base/features/custom-content-type/

### Settings-page builders are already visual

ACF Options Pages and Meta Box Settings Page already provide UI-created admin settings/options pages with field groups and tabs/styles. WPEssential's advantage must be shared schema, policies, Vault, revisions and cross-module data—not just no-code settings pages.

References:
- https://www.advancedcustomfields.com/resources/options-page/
- https://docs.metabox.io/extensions/mb-settings-page/

### Status tools already include role-aware workflows

PublishPress Statuses includes custom editorial/visibility/revision status concepts, workflow ordering, post-type targeting, role controls and deeper capability integration. WPEssential should add value through generic state-machine reuse and automation, not only colored status labels.

Reference: https://publishpress.com/statuses/

### Frontend profile/dashboard ecosystems already support CRUD flows

JetEngine Profile Builder and Frontend Dashboard provide account pages/subpages, role menus and editable user/content flows. WPEssential should be application-oriented: one Dashboard Shell composed from Forms, Listings, Profiles, Notifications and Policies.

References:
- https://crocoblock.com/knowledge-base/features/jetengine-user-profile-builder-overview/
- https://wordpress.org/plugins/frontend-dashboard/

### WP Crontrol establishes a high Cron UX baseline

WP Crontrol already views scheduled events with arguments/callbacks/next-run data and can edit/delete/pause/resume/run events plus manage custom schedules and warnings.

Reference: https://wordpress.org/plugins/wp-crontrol/

WPEssential must add durable job/workflow integration and runner reliability rather than merely reproducing an event list.

### No-code REST plugins expose very powerful/raw operations

Custom API for WP advertises no-code CRUD/external API integrations and even custom SQL-backed endpoints. WPEssential should not compete by making raw SQL easier; it should compete by making schema/policy/data-source/ability based endpoints safer and more reusable.

Reference: https://wordpress.org/plugins/custom-api-for-wp/

### Reset expectations include snapshots/fail-safes

WP Reset uses confirmations, snapshots/restore and partial reset tools; it explicitly advises snapshot/backup before destructive operations.

Reference: https://wordpress.org/plugins/wp-reset/

WPEssential raises this to a restore-point contract that can include Backup Manager, environment inventory and health verification.

### Watermark competitors mutate then restore from backups

Current Image Watermark/Ultimate Watermark products support image/text watermarking, bulk operations, sizing/positioning/opacity and backup/restore. WPEssential's stricter target is to preserve the original as the original and generate/rebuild derivatives rather than relying on destructive replacement as the default.

References:
- https://wordpress.org/plugins/image-watermark/
- https://wordpress.org/plugins/ultimate-watermark/

### Protector must avoid security theater

WPS Hide Login itself describes URL interception and recovery behavior; password-protection plugins cover site/content gates. These are useful controls but do not replace capability/session/rate-limit security.

References:
- https://wordpress.org/plugins/wps-hide-login/
- https://wordpress.org/plugins/password-protected/

### XML-RPC products often simplify a nuanced core surface

Current XML-RPC control plugins show demand for method controls, rate limits and compatibility behavior, while WordPress core documents that `xmlrpc_enabled` does not cover every XML-RPC method. WPEssential must model the actual method surface rather than advertise a misleading one-switch abstraction.

References:
- https://developer.wordpress.org/reference/classes/wp_xmlrpc_server/
- https://developer.wordpress.org/reference/hooks/xmlrpc_methods/

## Benchmark completion rule

A module can be labelled **Competitive Baseline Verified** only when:

1. current benchmark features have been re-researched at implementation time;
2. required baseline behaviors are mapped to acceptance tests;
3. the WPEssential differentiator is implemented, not only documented;
4. performance/security/accessibility gates pass;
5. at least one realistic reference application uses the module end-to-end.

No marketing claim such as “beats JetEngine/ACF/Gravity Forms” is justified by feature-count planning alone.
