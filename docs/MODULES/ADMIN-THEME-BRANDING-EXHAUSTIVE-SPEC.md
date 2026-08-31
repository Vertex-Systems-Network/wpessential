# WPEssential — Admin Theme, Branding & Experience Manager

Status: **Phase 0 exhaustive product specification / no development authorization**  
Edition: **Pro**  
Proposed current surface: **49**

## 1. Purpose

Provide a version-adaptive, accessible, role/site/network-aware visual theming and branding system for WordPress admin without forking wp-admin markup or confusing visual customization with authorization.

Market baseline includes Admin Color Schemes, Admin Color Schemer, forced/default color schemes and modern white-label/admin-style tools. WPE target is broader: native scheme compatibility + WordPress 7.1 design-token theming + environment identity + branding + safe inheritance/versioning.

## 2. Core architectural rule

Use the highest-level stable WordPress theming surface available:

1. WordPress 7.1+ semantic `wp-theme` design tokens where supported;
2. native admin color scheme registration through supported APIs such as `wp_admin_css_color()` where appropriate;
3. WPE-generated scoped compatibility CSS only for gaps not expressible through stable tokens/APIs;
4. never fork or copy wp-admin templates as canonical theme implementation.

Capability detection decides which controls are effective on the current WordPress version.

## 3. Screens

1. Overview
2. Themes
3. Theme Editor
4. Assignments
5. Branding
6. Environment Identity
7. Admin Bar
8. Login Experience
9. Compatibility
10. Preview / Compare
11. Revisions
12. Import / Export
13. Diagnostics
14. Settings

## 4. Theme definition

Fields:
- name;
- stable key/UUID;
- status Draft / Active / Archived;
- description;
- base theme/preset;
- variant type: light / dark / high contrast / custom;
- WordPress compatibility profile;
- scope: Site / Network Template / Network Enforced where supported;
- tags;
- author/source metadata;
- version/revision;
- fallback theme.

## 5. Color system

Token groups:
- canvas/background;
- raised surface;
- panel/card;
- border/subtle border;
- primary accent;
- secondary accent;
- text primary/secondary/muted;
- links normal/hover/focus;
- menu background/text/current/hover;
- toolbar/admin bar;
- buttons primary/secondary/destructive;
- inputs;
- selected/focus states;
- notices: info/success/warning/error;
- badges/status;
- disabled states;
- icon base/focus/current.

Each token can be inherited or explicitly overridden.

## 6. WordPress native admin color scheme bridge

Where native scheme registration remains supported:
- register WPE theme as native scheme;
- key/name;
- generated CSS URL/artifact;
- palette swatches;
- icon colors;
- RTL variant where required;
- user profile selection compatibility;
- default scheme handling;
- forced scheme handling only through explicit assignment policy.

Native registration is compatibility output, not WPE's only theme model.

## 7. WordPress 7.1+ Design System tokens

Where `wp-theme` is available, support semantic controls such as:
- color tokens;
- border radius / roundness;
- interactive pointer/cursor behavior where exposed;
- component-facing semantic tokens;
- future token groups only after version capability detection.

The editor must show:
- supported by current WP;
- fallback-generated;
- unsupported/ignored;
- Core-owned/no override.

## 8. Typography

Controls only through supported/scoped mechanisms:
- UI font family preset/registered font;
- base size scale;
- compact/comfortable density profile;
- heading/body/label weight profile;
- line height scale.

Do not globally inject arbitrary remote font URLs without Asset/Vault/Privacy policy.

## 9. Geometry and density

Options:
- radius scale;
- control height density;
- spacing density;
- panel elevation/shadow profile;
- menu item density;
- table row density;
- modal/drawer density.

Only controls with verified stable selectors/tokens are offered for the detected WordPress profile.

## 10. Accessibility

Theme editor continuously checks:
- foreground/background contrast;
- focus visibility;
- destructive/status color distinguishability;
- non-color state indicators;
- high-contrast compatibility;
- keyboard/focus not broken by styling;
- reduced motion preference respected.

Publish can warn/block based on policy when critical contrast fails.

## 11. Assignment engine

Assignment targets:
- current user;
- selected users;
- roles;
- capability/Policy segment;
- site;
- network default;
- environment;
- user preference.

Modes:
- user-selectable;
- default unless user chose another;
- role/site enforced;
- network enforced floor;
- environment override for warning identity only.

Precedence must be explicit and explainable.

Recommended precedence:
`environment safety cue > network enforced > site enforced > explicit user assignment > role default > site/network default > Core default`.

Environment cue should normally overlay identity without destroying user's accessibility preferences.

## 12. Environment Identity

Purpose: make production/staging/local visually unmistakable.

Options:
- environment class: production / staging / development / local / custom;
- admin bar color/accent;
- persistent label;
- icon;
- browser favicon candidate through supported site/admin mechanism;
- warning stripe/badge;
- production destructive-action warning enhancement;
- selected roles who see environment cue;
- never use environment color as the only signal.

Environment detection sources:
- WordPress environment type;
- config constant/profile;
- explicit WPE override with diagnostic warning on mismatch.

## 13. Branding

Options:
- organization/product name;
- admin logo/mark where supported;
- compact logo;
- footer text/link;
- help/support link;
- favicon/site icon integration;
- login logo/brand;
- brand colors mapped into theme;
- white-label mode limited to presentation, not removing required WordPress/license attribution illegally.

Brand asset references use Media/Asset Registry.

## 14. Admin Bar

Controls:
- theme/accent;
- environment label;
- logo/brand mark;
- visibility of selected presentation-only nodes through Admin Menu/Admin Bar adapter;
- role/site assignment;
- frontend admin bar theme compatibility;
- responsive/mobile states.

Hiding a toolbar node never revokes destination access.

## 15. Login Experience

Presentation only; WordPress authentication remains authoritative.

Options:
- logo;
- background color/image/gradient;
- panel surface;
- input/button tokens;
- heading/help text;
- safe footer links;
- privacy/terms links;
- hide default branding only where supported;
- light/dark variant;
- responsive/mobile preview.

MUST NOT:
- replace WordPress password/session/reset security;
- insert third-party login code without adapter;
- leak whether an account exists.

## 16. Preview / Compare

Preview contexts:
- Dashboard;
- Posts/list table;
- post editor shell;
- Users;
- Settings;
- Site Editor shell where current WP supports scheme reflection;
- Network Admin;
- login screen;
- mobile-width preview.

Compare:
- current Core;
- selected theme;
- theme revision A/B;
- role/user assignment result.

Preview never changes live user preference until Publish/Assign.

## 17. Compatibility diagnostics

Detect/report:
- current WP theming profile;
- `wp-theme` token availability;
- native admin color registration availability;
- custom plugin CSS collisions;
- selectors/tokens overridden with `!important` by third parties;
- iframe/editor isolation caveats;
- Site Editor/admin shell differences;
- RTL;
- high-contrast OS/browser mode;
- admin theme customizations from other plugins;
- stale generated theme artifact.

## 18. Import / Export

Package includes:
- theme definition;
- token values;
- assignments optional;
- branding references as portable asset refs;
- version/profile;
- fallback metadata.

No credentials/secrets.

Conflict actions:
- create new;
- merge compatible token groups;
- replace with diff;
- skip.

## 19. Revisions / rollback

Every publish records:
- before/after token diff;
- actor;
- affected assignments;
- WP profile;
- generated compatibility artifact fingerprint.

Rollback is theme-definition rollback, not WordPress version rollback.

## 20. Permissions

Candidate capabilities:
- `wpe_admin_theme_read`
- `wpe_admin_theme_create`
- `wpe_admin_theme_update`
- `wpe_admin_theme_publish`
- `wpe_admin_theme_assign`
- `wpe_admin_theme_network_manage`
- `wpe_admin_branding_manage`
- `wpe_login_branding_manage`
- `wpe_admin_theme_import_export`

Network enforcement requires Network Admin/Super Admin policy.

## 21. Abilities

- theme list/get/create/update/validate/publish/archive;
- preview;
- contrast audit;
- assignment plan/apply;
- compatibility inspect;
- import/export.

MCP/AI default: read, explain, draft, preview. Publish/network-enforce off by default.

## 22. Events

- admin_theme.published;
- admin_theme.assigned;
- admin_theme.assignment_removed;
- admin_theme.compatibility_degraded;
- admin_branding.updated;
- environment_identity.changed.

## 23. Multisite

- site themes site-owned by default;
- network theme library can provide templates;
- network enforced theme can set a floor while preserving approved accessibility/user variants if policy allows;
- Network Admin may have distinct theme;
- site admin cannot modify network-enforced definition;
- site clone can copy definitions but not user preference without explicit option;
- environment identity is installation/environment-aware and must not be copied blindly from staging to production.

## 24. AI Prompt

Examples:
- “Create a dark admin theme matching our brand.”
- “Make production red-accented and staging amber without changing user accessibility settings.”
- “Audit this theme for contrast problems.”
- “Create a role-specific compact scheme for editors.”

AI may draft themes and assignments. Network enforcement or broad reassignment requires approval.

## 25. MUST NOT

- no wp-admin template fork;
- no visual hiding as authorization;
- no hard-coded legacy CSS selectors without compatibility profile;
- no accessibility-critical state represented by color only;
- no network branding change from site-admin authority;
- no remote fonts/assets without approved asset/privacy policy;
- no arbitrary admin JavaScript injection inside this module.

## 26. Evidence

Reserved executable evidence: **ATM-001…ATM-176**, executed **0/176**.

Evidence classes cover Core-version profiles, native scheme registration, 7.1 token integration, fallback CSS, assignments, accessibility, branding, login/admin bar, Multisite, conflicts, lifecycle and performance.