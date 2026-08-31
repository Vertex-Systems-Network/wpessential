# WPEssential — Theme Workspace, Child Theme & Theme Customization Manager

Status: **Phase 0 exhaustive product specification / no development authorization**  
Edition: **Pro**  
Surface: **56**

## 1. Purpose

Provide a safe, versioned theme-development workspace for inspecting a parent theme, creating and managing child themes, editing declarative theme assets, tracking parent/child drift and producing portable child-theme packages without confusing theme-source customization with wp-admin branding or browser snippet injection.

Competitive baseline: Child Theme Configurator.

## 2. Ownership boundaries

Surface 56 owns:
- installed theme analysis;
- child-theme definition/lifecycle;
- stylesheet/theme.json/template asset workspace;
- parent-child dependency analysis;
- selector/property inspection;
- theme file copy/override planning;
- child-theme package export/import;
- parent update drift diagnostics.

Surface 49 owns **WordPress admin/login branding**.

Surface 50 owns **browser-side snippets/tags/code placement**.

Extension SDK/VCS owns **PHP/server-code source development**.

## 3. Hard security rule

WPE does **not** provide a textarea that writes arbitrary PHP into `functions.php` and immediately executes it on the site.

Server-side PHP changes follow:
`requirement → extension/theme source plan → reviewed generated source → VCS/CI/release → deploy`.

Theme Workspace may inspect PHP file metadata and copy a parent template into a governed source workspace when allowed, but executable PHP editing/publishing remains outside normal wp-admin runtime tooling.

## 4. Screens

1. Theme Workspace Overview
2. Installed Themes
3. Analyze Theme
4. Child Themes
5. Create Child Theme
6. Stylesheets
7. Selector / Property Explorer
8. Global Styles / theme.json
9. Templates & Parts
10. Assets & Fonts
11. Parent Update / Drift
12. Preview / Compare
13. Revisions
14. Package / Export / Import
15. Multisite
16. Diagnostics
17. Settings

## 5. Theme identity model

Fields:
- theme stylesheet key;
- name/version;
- parent stylesheet key if child;
- theme URI/author/source metadata;
- text domain;
- WordPress compatibility metadata;
- block/classic/hybrid classification;
- active/inactive/network-enabled state;
- installed path as protected system metadata;
- source provenance;
- package checksum/fingerprint where available;
- WPE workspace state;
- last analyzed parent version;
- last generated child revision.

WPE does not claim ownership of third-party theme source simply because it can inspect it.

## 6. Analyzer

Analyze without mutation:
- parent/child relationship;
- `style.css` headers;
- stylesheet enqueue graph;
- `@import` usage;
- hard-coded stylesheet link candidates;
- theme.json presence/schema/version;
- templates/template parts/patterns;
- block vs classic template hierarchy;
- CSS files and media queries;
- font declarations and external font origins;
- script/style handles where statically discoverable;
- asset paths;
- child-theme compatibility risks;
- writable filesystem state;
- current active theme and recovery theme availability.

Analyzer findings are evidence/warnings, not automatic edits.

## 7. Create Child Theme wizard

1. Select parent theme.
2. Run analysis.
3. Choose child name/slug/text domain.
4. Choose style strategy.
5. Choose stylesheet enqueue profile.
6. Choose theme.json inheritance/override profile.
7. Select safe files/assets to copy.
8. Configure screenshot/metadata.
9. Review parent compatibility/drift risks.
10. Generate package/workspace as Draft.
11. Validate generated files.
12. Preview.
13. Optional install/network-enable/activate only under separate capability and recovery preflight.

## 8. Style loading strategies

Candidate profiles, capability-detected:
- child stylesheet only when parent already loads correctly;
- enqueue parent then child;
- enqueue selected parent styles then child;
- block-theme/theme.json-first profile;
- custom registered adapter for unusual theme architecture.

Do not blindly use CSS `@import` as default.

Show resolved order and handles before publish/activation.

## 9. Stylesheet workspace

Supported declarative operations:
- create/manage child stylesheet files;
- CSS syntax parse/validate;
- selector/property browser;
- media-query browser;
- property-value search;
- copy selected rule from parent into child override;
- edit override;
- CSS custom properties;
- source file attribution;
- revision/diff;
- preview;
- duplicate rule detection;
- invalid/dead selector advisory where bounded evidence exists.

Advanced raw CSS editing is a privileged source-editing action with validation/revisions; it is not equivalent to arbitrary PHP execution.

## 10. Selector / Property Explorer

Indexes statically parsed CSS:
- selector;
- specificity estimate;
- source file/line reference where parser retains it;
- media/support/layer context;
- properties/values;
- custom property definitions/uses;
- vendor-prefixed variants;
- child override status.

Search by:
- selector;
- property;
- value;
- file;
- media condition;
- custom property.

Generated override preview must show cascade caveats rather than claiming runtime certainty from static analysis alone.

## 11. theme.json / Global Styles workspace

Where supported:
- schema/version validation;
- settings vs styles separation;
- colors/gradients/duotone;
- typography/fonts references;
- spacing/layout;
- borders/shadows where Core schema supports;
- blocks-specific settings/styles;
- custom settings;
- template/part relationships;
- parent vs child JSON merge preview;
- unsupported/newer keys preserved safely;
- import/export;
- revision diff.

Surface 53 remains canonical Font Library; Theme Workspace references its font definitions.

## 12. Templates & template parts

For block-theme declarative files:
- inventory templates/parts;
- copy parent template into child override;
- compare parent vs child;
- detect removed/renamed upstream template;
- validate block markup structurally where possible;
- track user-edited Site Editor entities separately from theme files;
- export selected override.

Classic PHP template source:
- inspect/list/copy-plan metadata;
- file diff after parent updates;
- no arbitrary live PHP editor in standard UI.

## 13. Assets & fonts

Inventory:
- images/icons;
- CSS;
- JS references;
- fonts;
- source maps advisory;
- duplicate assets;
- external origins.

Font files/registering delegate to Surface 53.

Script execution/injection delegates to Asset Registry/Safe Script where applicable.

## 14. Parent update / drift intelligence

On parent version change:
- old parent fingerprint vs new parent;
- child override files affected;
- parent file removed/renamed;
- copied selector changed upstream;
- template/theme.json drift;
- hook/handle change where statically detectable;
- severity/confidence;
- manual review queue.

Never overwrite child customizations automatically because the parent changed.

## 15. Preview / Compare

Preview contexts:
- frontend representative routes;
- selected post/archive/template;
- responsive viewport presets;
- child revision A vs B;
- parent current vs child;
- current live theme vs Draft child package.

Preview sandbox availability is environment/profile dependent. If isolation cannot be guaranteed, WPE must say so and use staging/preview URL integration through Surface 55.

## 16. Child theme lifecycle

States:
- Draft workspace;
- Generated;
- Installed inactive;
- Network enabled where applicable;
- Active;
- Archived workspace.

Actions:
- create;
- duplicate;
- rename pre-install where safe;
- regenerate metadata;
- validate;
- preview;
- package/export;
- install;
- activate with recovery preflight;
- deactivate/switch through WordPress theme APIs;
- archive workspace.

Deletion of installed theme files follows WordPress package/theme rules and dependency checks.

## 17. Package / export / import

Export ZIP may include:
- `style.css` with valid theme header;
- selected stylesheets;
- `theme.json`;
- templates/template parts/patterns/assets;
- screenshot;
- manifest with parent requirement/version assumptions;
- WPE provenance metadata outside executable source where appropriate.

No credentials/secrets.

Import:
- inspect manifest;
- scan paths/zip traversal;
- validate theme headers;
- parent dependency;
- collision strategy;
- source trust/provenance;
- keep Draft until approved.

## 18. Multisite

- Network Admin controls installation/network-enable;
- site admins may activate only permitted installed themes;
- child definition can be network template;
- network-enabling generated theme requires explicit authority;
- parent dependency must exist network-wide as required;
- site-specific customization should prefer Site Editor/Global Styles when that is canonical rather than fork child theme unnecessarily;
- no Super Admin authority inferred from theme role names.

## 19. Integrations

- Surface 53 Font Library;
- Surface 55 Staging/Clone/Migration for isolated preview/deployment;
- Backup Manager for pre-activation recovery point where policy requires;
- Audit & Observability;
- Asset Registry;
- Admin Theme only for environment identity, not frontend theme source;
- Solution Blueprint for packaging/reference;
- Extension SDK/VCS for PHP source changes;
- Import/Export.

## 20. Permissions

Candidate capabilities:
- `wpe_theme_workspace_read`
- `wpe_theme_workspace_create`
- `wpe_theme_workspace_edit_css`
- `wpe_theme_workspace_edit_theme_json`
- `wpe_theme_workspace_copy_template`
- `wpe_theme_workspace_package`
- `wpe_theme_workspace_install`
- `wpe_theme_workspace_activate`
- `wpe_theme_workspace_network_manage`
- `wpe_theme_workspace_import_export`

Install/activate/network actions are high-risk and may require re-auth/backup/recovery readiness.

## 21. Abilities / AI / MCP

Abilities:
- theme inventory/get/analyze;
- child plan/create Draft;
- style query/diff;
- theme.json validate/diff;
- drift report;
- package plan/export;
- preview plan;
- activation preflight.

AI/MCP default:
- read/analyze/explain/draft declarative CSS/theme.json changes;
- package/install/activate/network-enable off by default;
- PHP source generation routes to Extension SDK/VCS workflow and remains development-consent gated.

## 22. Evidence

Reserved namespace: **THM-001…THM-176**, executed **0/176**.

Evidence groups cover:
- theme identity/parent dependency;
- analyzer;
- child creation;
- stylesheet enqueue;
- CSS parser/selector explorer;
- theme.json;
- templates/parts;
- assets/fonts;
- parent drift;
- preview;
- package import/export security;
- permissions/source safety;
- activation/recovery;
- Multisite;
- performance/large stylesheets;
- end-to-end regression.

## 23. MUST NOT

- no arbitrary PHP live editor/execution;
- no silent parent-theme modification;
- no automatic child overwrite on parent update;
- no theme activation without authority/recovery preflight when required;
- no unvalidated ZIP/path writes;
- no claim static CSS analysis perfectly predicts browser runtime cascade;
- no duplicate Font Library or Safe Script engine inside Theme Workspace;
- no network enable from site-admin authority.