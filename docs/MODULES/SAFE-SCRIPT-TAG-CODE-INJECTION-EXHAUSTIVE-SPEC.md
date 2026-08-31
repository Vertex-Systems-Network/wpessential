# WPEssential — Safe Script, Tag & Code Injection Manager

Status: **Phase 0 exhaustive product specification / no development authorization**  
Edition: **Pro**  
Proposed current surface: **50**

## 1. Purpose

Provide a controlled replacement for common header/footer script and browser-code injection plugins while explicitly refusing to become an arbitrary PHP/eval execution console.

The module is intended for:
- analytics tags;
- consent-aware marketing scripts;
- verification meta tags;
- external widgets/chat snippets;
- JSON-LD;
- CSS;
- bounded JavaScript;
- safe HTML/text placements;
- external script/link resources.

Server-side business logic remains an Extension SDK / reviewed plugin responsibility.

## 2. Security invariant

**No arbitrary PHP execution. No `eval()`. No user-entered server-side code runtime.**

ADR-0004 remains controlling.

If the user needs PHP/server-side customization, WPE should generate or document a typed Extension SDK plan, not store executable PHP in WordPress content/options and evaluate it.

## 3. Screens

1. Overview
2. Snippets / Tags
3. Create Snippet
4. Placements
5. Conditions
6. Consent & Privacy
7. External Origins
8. CSP / Security
9. Dependencies & Ordering
10. Environments
11. Test / Preview
12. Revisions
13. Import / Export
14. Activity / Audit
15. Diagnostics
16. Settings

## 4. Snippet types

Supported declarative/browser types:
- External JavaScript URL;
- Inline JavaScript;
- CSS;
- HTML fragment;
- Plain text;
- Meta tag;
- Link tag;
- JSON-LD structured data;
- iframe/widget definition through explicit safe iframe profile;
- reusable content/ad snippet through renderer profile.

Not supported:
- PHP;
- shell;
- SQL;
- arbitrary server template evaluation;
- executable shortcode/PHP callback entered in UI.

## 5. Snippet identity

Fields:
- name;
- UUID/key;
- status Draft / Active / Paused / Archived;
- type;
- description;
- owner/team metadata;
- tags/category;
- risk class;
- revision;
- environment scope;
- created/updated actor/time.

## 6. Placement locations

Frontend standard locations:
- document head early;
- document head normal/late;
- after opening body hook where theme/Core supports;
- before closing body/footer;
- content before;
- content after;
- selected component/slot through Placement/Component adapter;
- shortcode/block/manual placement token;
- selected theme hook only through registered Hook Adapter.

Advanced separately privileged locations:
- login head/footer;
- wp-admin head/footer;
- Site Editor/editor shell only through certified adapter.

No arbitrary hook-name textbox that calls unknown PHP actions as code execution.

## 7. Priority / ordering

Each snippet:
- priority integer;
- dependencies;
- run/load before/after selected snippet;
- mutual exclusion group;
- once-per-request;
- once-per-page-render;
- manual-only.

Dependency graph must detect:
- cycles;
- missing dependency;
- contradictory placement;
- external script duplicate URL;
- duplicate tag IDs/global variables where detectable.

## 8. Conditional logic

Uses shared Conditional Logic Engine.

Context may include:
- entire site;
- homepage/front page;
- post/page/CPT type;
- specific resources;
- taxonomy/term;
- archive/search/404;
- template/route;
- logged-in/logged-out;
- role/capability for presentation only;
- Membership/Entitlement only where non-sensitive delivery semantics are acceptable;
- language/locale;
- environment;
- device class only through coarse approved client/server signal;
- query parameter only from allowlisted key/value comparison;
- referrer/origin only when privacy/security profile permits;
- WooCommerce context via formal domain adapter;
- Frontend Dashboard route;
- experiment/placement audience via shared foundation.

Condition success never authorizes protected data.

## 9. External script definition

Fields:
- HTTPS URL required by default;
- origin allowlist/profile;
- script type classic/module;
- async;
- defer/loading strategy where applicable;
- crossorigin;
- referrerpolicy;
- integrity/SRI;
- fetch priority where browser supports and evidence justifies;
- nonce mode through CSP adapter;
- data attributes;
- fallback behavior;
- failure reporting.

HTTP, data:, javascript:, file: and other dangerous schemes blocked by default.

## 10. Inline JavaScript

Allowed only under privileged capability and risk warning.

Controls:
- syntax validation;
- max size;
- no PHP interpolation;
- safe typed token interpolation only through JSON/JS-context escaping;
- CSP nonce/hash compatibility;
- environment restriction;
- minify off by default unless deterministic build service later certified;
- sourceURL diagnostic label candidate;
- test sandbox where feasible.

WPE does not claim JavaScript can be made harmless merely by sanitization. It is an explicit browser-code privilege.

## 11. CSS

Controls:
- syntax validation;
- scoped/global mode;
- frontend/admin/login scope;
- selected route/template scope where generated scoping is reliable;
- custom properties;
- max size;
- asset URL validation;
- no `expression()`/unsafe legacy constructs;
- external URL/privacy warnings;
- revision/preview.

Admin visual theming should prefer Surface 49 tokens instead of generic CSS.

## 12. HTML fragments

Allowed elements/attributes depend on risk profile and operator capability.

Profiles:
- sanitized content HTML;
- trusted marketing/embed HTML;
- restricted iframe widget;
- raw browser markup advanced.

Scripts inside HTML are extracted/rejected unless snippet is explicitly a JavaScript/script type.

No PHP tags.

## 13. Meta / Link tag builder

Typed fields instead of raw markup for common cases:
- verification meta;
- robots/meta names where not owned by SEO integration;
- preconnect/dns-prefetch;
- preload/modulepreload with validation;
- canonical/alternate only through SEO-aware conflict checks;
- theme-color;
- custom allowed meta/link attributes.

Conflict detection prevents duplicate canonical/viewport/charset-like critical tags where WPE should not compete with Core/SEO owner.

## 14. JSON-LD

Options:
- raw JSON with JSON parser/schema validation;
- typed Schema.org template profiles optional;
- dynamic tokens through JSON-safe encoding;
- per-resource/route conditions;
- preview generated object;
- conflict warning with SEO plugin structured data.

No JavaScript expressions inside JSON-LD.

## 15. Consent / privacy

Consent categories:
- strictly necessary;
- functional;
- preferences;
- analytics;
- marketing;
- custom legal/profile category.

For non-essential snippets:
- blocked until required consent signal;
- consent adapter source shown;
- fallback/placeholder optional;
- withdrawal stops future execution where technically possible;
- no cookies/storage written by WPE before allowed category;
- diagnostic can report observed third-party behavior but cannot guarantee vendor compliance.

Consent engine integration is provider/region evidence-gated; WPE does not invent legal advice.

## 16. CSP / security headers

Integrate with Protector/Security Header profile:
- nonce injection;
- hash generation candidate;
- allowed origin/source preview;
- report-only CSP compatibility;
- violation diagnostics where data is available;
- block publish when snippet contradicts enforced CSP and no safe profile exists.

Snippet Manager must not silently weaken CSP to make a snippet work.

## 17. Environment controls

Per snippet:
- all environments;
- production only;
- staging only;
- development/local only;
- selected custom environment profile.

Common use:
- analytics production only;
- debug tools non-production only;
- staging verification tags.

Environment mismatch appears in diagnostics.

## 18. Presets / Tag recipes

Optional audited templates for:
- Google tag/analytics loader;
- Meta Pixel loader;
- generic tag manager;
- search-console/site verification meta;
- chat/widget loader;
- JSON-LD starter;
- custom CSS;
- external module script.

Presets store parameter schema, not copied proprietary scripts when vendor terms/source ownership disallow it. Current official vendor snippets are fetched/reviewed only through approved adapter/version profiles.

## 19. Test / Preview

Before publish:
- parse/syntax result;
- resolved placement;
- condition evaluation for selected URL/context;
- consent state simulation;
- CSP compatibility;
- duplicate/conflict check;
- dependency order;
- external origin list;
- dynamic token values using safe sample/redacted values;
- expected output preview.

No production script execution is required for static preview.

## 20. Diagnostics

Report:
- snippet active/inactive and reason;
- page placement encountered;
- condition result;
- consent blocked/allowed;
- CSP blocked/conflict;
- external resource load error where browser telemetry explicitly enabled;
- duplicate library detection;
- jQuery/version conflict candidate;
- document order;
- cache/full-page cache caveat;
- minifier/CDN rewrite caveat;
- theme hook missing;
- stale manual shortcode usage.

## 21. Revisions / rollback

Every publish creates:
- code/tag content fingerprint;
- placement/condition diff;
- risk-class diff;
- actor/reason;
- environment/consent/CSP changes.

Rollback restores previous snippet revision after validation.

Emergency kill switch:
- pause one snippet;
- pause group;
- safe-mode disable all non-essential injected browser code;
- network/site scope explicit.

## 22. Import / Export

Export includes:
- definition;
- content;
- conditions;
- placements;
- consent category;
- security settings;
- environment;
- dependencies;
- version.

Secrets/credentials must be Vault references/placeholders, never plaintext package values.

Import conflict:
- create;
- merge metadata only;
- replace with diff;
- skip.

Imported code stays Draft until explicitly approved when risk policy requires.

## 23. Permissions

Candidate capabilities:
- `wpe_script_read`
- `wpe_script_create`
- `wpe_script_update`
- `wpe_script_publish`
- `wpe_script_publish_high_risk`
- `wpe_script_manage_admin_scope`
- `wpe_script_manage_login_scope`
- `wpe_script_manage_consent`
- `wpe_script_manage_csp`
- `wpe_script_import_export`
- `wpe_script_emergency_pause`

High-risk publish can require re-auth and reason.

## 24. Abilities / MCP

Abilities:
- list/get/create/update/validate;
- preview;
- conflict audit;
- publish/pause;
- dependency plan;
- export/import plan;
- diagnostics.

AI/MCP defaults:
- read/explain/draft/validate allowed by policy;
- publish browser code disabled by default;
- admin/login scope and emergency/security changes excluded by default.

## 25. AI Prompt

Examples:
- “Add this analytics script only after analytics consent on production.”
- “Create JSON-LD for this CPT without duplicating Yoast output.”
- “Audit which snippets violate our CSP.”
- “Move this external script to the footer with defer if safe.”
- “Generate a Meta Pixel draft for product pages only.”

AI output is always a Draft snippet until applicable validation/approval.

## 26. Multisite

- snippets site-scoped by default;
- network library can provide templates;
- network enforced security floor can prohibit inline JS or non-allowlisted origins;
- shared network snippet rollout uses explicit target sites and dry-run;
- site admin cannot edit network-enforced snippet;
- no shared secret reveal;
- clone/import changes production-only environment bindings to review-required state;
- network emergency pause available only to Network Admin/Super Admin Policy.

## 27. Coexistence

Detect/report likely output from:
- WPCode/Insert Headers & Footers;
- theme header/footer fields;
- analytics/tag-manager plugins;
- SEO structured-data plugins;
- cache/minification plugins;
- consent plugins;
- CSP/security plugins.

Migration assistant may import simple head/body/footer scripts after preview, but never executes/migrates PHP snippets into WPE.

## 28. PHP/custom server code path

When user asks for PHP:
1. classify requirement;
2. determine if an existing WPE Ability/Condition/Workflow/Hook Adapter solves it declaratively;
3. otherwise offer **Create Extension Plan**;
4. generate SDK/plugin scaffold specification only until development is separately authorized;
5. reviewed source code goes through normal VCS/CI/release flow.

No “unsafe mode” re-enables PHP eval in this module.

## 29. MUST NOT

- no PHP/eval;
- no arbitrary SQL/shell;
- no role/capability bypass because snippet editor page is accessible;
- no CSP weakening automatically;
- no secret interpolation into frontend JavaScript;
- no unvalidated external schemes/origins;
- no consent bypass;
- no AI auto-publish of high-risk code;
- no false promise that third-party script is privacy compliant;
- no duplicate critical meta/SEO tags without conflict acknowledgement.

## 30. Evidence

Reserved evidence namespace: **STM-001…STM-176**, executed **0/176**.

Evidence classes include parser/types, placement, ordering, conditions, consent, CSP, browser code security, external origins, environment, revisions, imports, migration, Multisite, AI/MCP and failure recovery.