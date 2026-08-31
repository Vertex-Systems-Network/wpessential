# WPEssential — Module Catalog Expansion through ADR-0194

Status: **Phase 0 product catalog addendum / no development authorization**

This addendum supplements `docs/MODULE-CATALOG.md` and records the current 50-surface catalog expansion without rewriting historical catalog numbering.

## Existing surfaces expanded

### 15. Membership System — Pro
Additional competitive capabilities accepted by ADR-0189:
- Site Access / Private Site profile;
- registration/onboarding studio;
- email verification and admin approval;
- login/register/profile components;
- restriction defaults + per-resource overrides;
- teaser/excerpt profiles;
- navigation visibility adapters;
- messages/dialog/email presets;
- member-directory/login compositions;
- Members/WP-Members/role-based migration assistants;
- MPR evidence 0/176.

### 28. Watermarker / Media Rules — Pro
Additional media-performance capabilities accepted by ADR-0192:
- Core-aware LCP/fetchpriority/lazy/eager decisions;
- privacy-aware field metrics;
- responsive sizes/Picture diagnostics;
- AVIF/WebP/fallback derivative policy;
- placeholders and CLS/dimension diagnostics;
- CDN/offload delivery intelligence;
- MDP evidence 0/176.

### 30. Role & Capability Manager — Pro
Additional competitive capabilities accepted by ADR-0190:
- Assignable Role Policy / target-role hierarchy;
- Administrator Recovery/Rescue;
- capability provenance/orphan diagnostics;
- linked surface-specific restriction Policies;
- network role templates/sync dry run;
- effective-access explainability;
- RPR evidence 0/176.

## 49. Admin Theme, Branding & Experience Manager — Pro

Goal: version-adaptive, accessible, role/site/network-aware wp-admin visual theming and branding.

Major capabilities:
- native admin color-scheme bridge;
- WordPress 7.1+ Design System token/ThemeProvider compatibility profile;
- semantic color/token theme editor;
- typography/roundness/density where certified;
- contrast/accessibility validation;
- per-user/role/site/network/environment assignments;
- staging/production identity cues;
- admin-bar and login branding;
- preview/revisions/import/export/compatibility diagnostics;
- ATM evidence 0/176.

Boundary: visual hiding never becomes authorization and wp-admin templates are not forked as the canonical implementation.

## 50. Safe Script, Tag & Code Injection Manager — Pro

Goal: safe replacement for common header/footer/tag/snippet use cases without arbitrary server-code execution.

Supported product classes:
- external JavaScript;
- privileged inline JavaScript;
- CSS;
- HTML/text;
- typed meta/link tags;
- JSON-LD;
- safe iframe/widget profiles;
- typed head/body/footer/component placements;
- Conditions, consent categories, CSP/SRI/origin controls;
- environment scoping;
- dependency ordering;
- preview/conflict diagnostics;
- revisions/rollback/emergency pause;
- import/export/migration of non-PHP snippets;
- STM evidence 0/176.

Hard boundary: **no PHP, no eval, no arbitrary SQL/shell/server-code runtime**. Server-side customization is routed to Extension SDK/plugin planning and normal VCS/CI/review after development authorization.

## Current catalog milestone

Current planned module/platform surfaces: **50**.  
Product maturity: **50/50 Exhaustive**.  
Logical Multisite product mapping: **50/50**.  
AI Prompt product mapping: **50/50**.  
Implementation authorization: **0/50**.