# WPEssential — Competitive & Platform Research

Research date: **2026-08-27**. Prefer official WordPress/vendor documentation over comparison blogs. Refresh this research before implementing each module.

## WordPress platform findings

- **WordPress 7.1 is current** (released 2026-08-19): https://wordpress.org/documentation/wordpress-version/version-7-1/
- **Abilities API** exists in WordPress 6.9+ and provides typed, discoverable functionality with schemas and permission callbacks: https://developer.wordpress.org/apis/abilities-api/
- Official WordPress AI architecture now combines Abilities, the provider-agnostic AI Client and MCP Adapter: https://developer.wordpress.org/news/2026/07/build-your-first-ai-powered-wordpress-plugin/
- Same-site REST uses cookie authentication + REST nonce; external baseline can use Application Passwords over HTTPS: https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/
- WP-Cron runs when WordPress receives traffic and therefore cannot promise exact wall-clock execution: https://developer.wordpress.org/plugins/cron/
- Action Scheduler is a serious candidate for traceable background queues and is designed for distribution in plugins: https://actionscheduler.org/

### Architecture consequences

WPEssential actions should be typed Abilities where practical; AI/MCP is an adapter over those actions, not a privileged backdoor. Cron scheduling and job execution are separate concerns. External API authentication must reuse secure WordPress primitives rather than inventing a default username/password API.

## WordPress.org commercial constraint

WordPress.org guideline 5 prohibits a directory plugin from containing functionality that is locked behind payment or disabled after a trial/quota. It recommends premium add-ons hosted outside WordPress.org when premium code should be excluded.

Source: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/

**Decision:** WPEssential Free permanently contains CPT + Taxonomy. WPEssential Pro is a separate add-on. The requested 30-day full trial applies to Pro entitlement/add-on, not hidden premium code inside the WordPress.org package.

## Legacy WPEssential repository audit

Reference: `wpessential/wpessential-dashboard-builder`.

Useful prior experiments found include REST code, a migration layer, Reset Manager, Cron Jobs Builder, Backup Manager adapters (including S3-compatible/email/MEGA), Elementor integration, forms assets and chat assets. This is evidence/reference material, not trusted production code.

Drift identified:

1. plugin header requires PHP 8.0 while Composer requires PHP 8.1;
2. a PSR-4 Database mapping points to `inc/daabase/`;
3. scripts use Laravel Mix while Vite packages are also present;
4. main plugin file logs wp-admin execution duration unconditionally;
5. recent commit messages such as `late work`, `improvements`, and `error fixing` do not meet the new engineering-history standard.

Before implementing a module, search the legacy repo for relevant work, review it, then explicitly classify each useful part as **reuse / adapt / replace / reject**.

## Content model / query market

### Secure Custom Fields
SCF covers fields, CPTs, taxonomies, Local JSON, developer APIs, advanced field types and 2026 Abilities/schema work.

Sources:
- https://developer.wordpress.org/secure-custom-fields/features/
- https://wordpress.org/plugins/secure-custom-fields/

### Meta Box
Meta Box demonstrates custom-table storage for field data and broad field extensibility.

Source: https://docs.metabox.io/extensions/mb-custom-table/

### JetEngine
JetEngine Query Builder spans CPT/CCT, SQL tables, users, terms/comments, WooCommerce and REST data, and the product also provides relations/listings/tables.

Sources:
- https://crocoblock.com/plugins/jetengine/
- https://crocoblock.com/knowledge-base/jetengine/jetengine-query-builder-rest-api-query-type/

**WPEssential differentiator:** one Data Source Registry + Field Schema + Relation Engine + Query AST shared by Columns, Forms, Listings, Dashboards, REST, Workflows and AI.

## Admin Columns market

Admin Columns Pro already provides sorting, smart filtering, inline/bulk editing, conditional formatting, CSV export and multiple column sets.

Sources:
- https://www.admincolumns.com/features/
- https://docs.admincolumns.com/article/25-basics-how-to-use-admin-columns-pro

**WPEssential bar:** meet those productivity features, then add native relation/query/aggregate columns, expensive-column diagnostics/lazy loading, source-aware editing, dependency tracking and safe export.

## Admin/menu/dashboard market

Admin Menu Editor supports drag/drop ordering, hide/show, rename/icon/URL changes and permissions. Ultimate Dashboard/Adminify cover custom dashboard widgets/admin experiences.

Sources:
- https://wordpress.org/plugins/admin-menu-editor/
- https://wordpress.org/plugins/ultimate-dashboard/
- https://wordpress.org/plugins/adminify/

**WPEssential rule:** menu visibility never substitutes for destination authorization.

## Roles/capabilities market

Members, PublishPress Capabilities and User Role Editor show that mature role tools require import/export, fine capability management, multisite awareness and lockout protections.

Sources:
- https://wordpress.org/plugins/members/
- https://wordpress.org/plugins/capability-manager-enhanced/
- https://wordpress.org/plugins/user-role-editor/

**WPEssential requirement:** privilege-diff preview, anti-lockout, recovery, audit and separate capabilities for dangerous actions.

## Forms & workflow market

Gravity Forms provides conditional logic, multi-page forms, save/continue, file uploads, notifications, REST APIs and an add-on framework. Fluent Forms adds extensive fields, calculations, conversational/multi-step forms, post creation and many integrations. AutomatorWP establishes the trigger/action/filter/log baseline for automation.

Sources:
- https://www.gravityforms.com/features/
- https://docs.gravityforms.com/category/developers/
- https://fluentforms.com/features/
- https://automatorwp.com/docs/getting-started/automations/
- https://automatorwp.com/docs/getting-started/logs/

**WPEssential differentiator:** Forms, Cron and automations share one Event/Condition/Action/Workflow runtime with persisted run state, retries, idempotency and recovery.

## Backup market

WPvivid supports multiple cloud destinations such as Google Drive, Dropbox, pCloud, OneDrive, S3/S3-compatible, Backblaze, WebDAV, FTP/SFTP and Wasabi.

Sources:
- https://wpvivid.com/features/cloud-storage
- https://docs.wpvivid.com/wpvivid-backup-pro-cloud-storage-overview.html

**WPEssential differentiator:** protocol/provider adapter registry, checksummed manifests, chunk/resume, retention, verifiable restore and automated integration tests per advertised destination. Email/Gmail is treated as notification/small-artifact delivery, not a primary large-backup store.

## Import/export market

WP All Import/Export supports CSV/XML/spreadsheets, custom fields, taxonomies, images, WooCommerce and updating existing data.

Sources:
- https://www.wpallimport.com/
- https://www.wpallimport.com/documentation/theme-plugin-fields/

**WPEssential differentiator:** separate configuration packages from content-data migration, include dependency/UUID remapping, dry-run, resumable jobs and relations/custom tables.

## Email/notification market

Better Notifications for WP customizes core/new WordPress email notifications and provides dynamic shortcodes/recipient rules.

Sources:
- https://betternotificationsforwp.com/features/
- https://betternotificationsforwp.com/documentation/notifications/shortcodes/

**WPEssential direction:** Notification System is channel-agnostic; Email Builder uses a dedicated email-safe renderer. Arbitrary browser-builder HTML is not assumed email-compatible.

## Chat market

Better Messages demonstrates the expected bar: private/group conversations, AJAX/WebSocket options, unread state, search, attachments, edit/delete/reply, moderation and role access.

Source: https://wordpress.org/plugins/bp-better-messages/

**WPEssential requirement:** object-level authorization, shared-hosting fallback transport, optional realtime adapters and workflow/notification integration.

## Builder integration research

WordPress recommends block registration through `block.json` and server registration. Elementor officially exposes widget/control registration. WPBakery exposes parameter/shortcode element APIs.

Sources:
- https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/
- https://developers.elementor.com/docs/widgets/
- https://kb.wpbakery.com/devs/element-development/params-array/

**Decision:** neutral Component Blueprint + per-builder adapter. No assumption that builders have identical feature sets.

## UI research

Untitled UI React currently uses React 19.2, TypeScript 5.9, Tailwind CSS 4.3 and React Aria. Components explicitly marked open source are MIT licensed, while paid assets have separate redistribution restrictions. Lucide is ISC licensed. WordPress provides `@wordpress/components` and `@wordpress/dataviews`; `@wordpress/ui` is currently documented as experimental.

Sources:
- https://www.untitledui.com/react/docs/introduction
- https://www.untitledui.com/license
- https://lucide.dev/license
- https://developer.wordpress.org/block-editor/reference-guides/packages/packages-components/
- https://developer.wordpress.org/block-editor/reference-guides/packages/packages-dataviews/

**Decision:** wrapper-based hybrid UI. Use only clearly MIT Untitled UI code unless separately licensed/reviewed; Lucide is the default icon set; use stable WordPress-native components when they reduce compatibility/accessibility risk.

## XML-RPC research

WordPress documents that `xmlrpc_enabled` does not fully disable all XML-RPC functionality; granular control uses `xmlrpc_methods` and related hooks.

Sources:
- https://developer.wordpress.org/reference/classes/wp_xmlrpc_server/
- https://developer.wordpress.org/reference/hooks/xmlrpc_methods/

The XML-RPC Manager must communicate this nuance rather than provide a misleading single switch.

## Media/watermark research

WordPress exposes `wp_get_image_editor()` and image sub-size infrastructure suitable for creating derived images.

Source: https://developer.wordpress.org/reference/functions/wp_get_image_editor/

**Decision:** preserve original uploads and watermark generated derivatives/renditions.

## Market conclusion

WPEssential cannot win by shipping shallow copies of mature specialist plugins. Its defensible advantage is integration depth:

1. shared typed definitions;
2. first-class relations;
3. one query/data-source model;
4. one event/workflow/action model;
5. typed Abilities shared by UI/REST/CLI/workflows/AI;
6. revisions/dependency graphs/import-export across builders;
7. safe dry-run/impact/rollback/audit for dangerous operations;
8. strict per-module asset loading;
9. site-safe entitlement behavior;
10. documented extension SDK.

## Research refresh protocol

Before coding any module:

1. re-check its primary competitor/current release docs;
2. re-check relevant WordPress APIs for supported versions;
3. inspect legacy WPEssential code for that feature;
4. update this research or create a module-specific note;
5. create/update an ADR if the result changes architecture, dependency, security or compatibility.
