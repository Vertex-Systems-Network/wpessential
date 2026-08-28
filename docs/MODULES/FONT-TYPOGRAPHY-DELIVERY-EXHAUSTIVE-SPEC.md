# WPEssential — Font Library, Typography & Delivery Manager

Status: **Phase 0 exhaustive planning / no development authorization**  
Edition: **Pro**  
Surface: **53**

## 1. Purpose

Provide a first-class frontend/admin-safe font and typography infrastructure that can meet and exceed Custom Fonts and Fonts Plugin style functionality while respecting privacy, licensing, performance and builder/theme ownership.

Admin Theme remains the owner of wp-admin visual theming; this surface owns reusable font assets and frontend typography delivery.

## 2. Screens

- Font Library
- Add / Import Font
- Provider Catalogs
- Families & Variants
- Typography Profiles
- Assignments
- External Font Audit
- Local Hosting / Privacy
- Loading & Performance
- Builder / Theme Integrations
- Licensing / Provenance
- Revisions
- Import / Export
- Diagnostics
- Settings

## 3. Font sources

- local uploaded font files;
- Media/Asset Registry font asset;
- Google Fonts metadata/provider adapter;
- Adobe Fonts/Typekit adapter where user is authorized;
- other registered provider adapter;
- system font stack;
- theme-provided local family discovered read-only;
- plugin/builder-provided family discovered read-only.

Remote provider terms, API limitations and licensing are explicit. WPE never republishes restricted font files merely because a CSS URL can be fetched.

## 4. Formats

Plan support for:
- WOFF2 preferred;
- WOFF;
- TTF/OTF import where permitted;
- variable fonts;
- collection/other formats only after browser/toolchain certification.

Validation:
- MIME/signature;
- family/style/weight metadata;
- malformed file detection;
- size limits;
- duplicate fingerprint;
- license/provenance fields.

## 5. Family / variant model

Family:
- name;
- stable key;
- source/provider;
- fallback stack;
- classification;
- license/source URL/reference;
- status/revision.

Variant:
- weight 1–1000 or standard named weight;
- normal/italic/oblique;
- width/stretch;
- variable axes;
- Unicode subset/range;
- file/asset;
- display strategy;
- preload eligibility.

## 6. Typography profiles

Profiles can define:
- family;
- fallback;
- size scale;
- line height;
- weight;
- style;
- letter spacing;
- word spacing;
- transform;
- text decoration;
- responsive values;
- fluid type clamp profile after validation;
- language/script override;
- variable-font axes.

Typography profile is reusable design data, not raw CSS pasted into every page.

## 7. Assignments

Targets:
- body;
- headings H1–H6;
- buttons;
- forms;
- navigation;
- captions/meta;
- Woo/domain adapter roles;
- registered design-system token;
- selected theme typography token;
- selected builder global typography token;
- explicit component scope;
- advanced CSS selector only under high-risk compatibility profile.

Precedence and ownership must be explainable.

## 8. Live preview

Preview:
- real site sample page where safe;
- representative headings/body/buttons/forms;
- desktop/tablet/mobile widths;
- locale/script samples;
- fallback simulation;
- font-load disabled simulation;
- current vs proposed profile.

Preview never publishes assignments.

## 9. Local hosting and privacy

Features:
- import permitted remote provider font into local Asset Registry;
- generate local `@font-face` definitions;
- retain provenance/license metadata;
- detect remaining external font requests;
- optional rewrite plan for recognized Google Fonts CSS/resources;
- external provider allow/deny policy;
- consent/privacy warning for remote font calls;
- environment-aware asset URLs.

Do not claim legal compliance solely because a font is local.

## 10. Loading / performance

Controls:
- `font-display` profile;
- preload only selected critical variants;
- subset/Unicode range;
- remove unused weights/styles;
- variable-font vs static tradeoff report;
- preconnect only when remote provider is intentionally used;
- cache headers via hosting/CDN ownership diagnostics;
- FOIT/FOUT/LCP advisory;
- duplicate family/source detection;
- CSS payload estimate.

Never preload every font file.

## 11. External font audit

Detect where possible:
- Google Fonts stylesheet requests;
- Adobe/Typekit requests;
- theme/plugin/builder external font CSS;
- duplicate families;
- unused variants;
- HTTP/mixed content;
- blocked/CSP failures;
- source owner.

Actions compile a migration/localization plan rather than blindly regex-rewriting all CSS.

## 12. Builder/theme integrations

Adapters for:
- Gutenberg/theme.json Global Styles;
- Elementor global fonts;
- Bricks;
- WPBakery/Visual Composer where supported;
- Divi where documented;
- Woo/domain components;
- Admin Theme consumption of registered local font family.

No direct mutation of proprietary internal documents without a certified adapter.

## 13. Import / Export

Portable package:
- family metadata;
- profile definitions;
- assignments;
- asset references;
- provider references;
- license/provenance metadata.

Font binary redistribution is included only when license/source policy permits it.

## 14. Permissions

Candidate:
- `wpe_fonts_read`
- `wpe_fonts_upload`
- `wpe_fonts_provider_import`
- `wpe_fonts_profile_manage`
- `wpe_fonts_assign`
- `wpe_fonts_privacy_manage`
- `wpe_fonts_import_export`

## 15. AI

AI may recommend pairings, inspect weight usage, draft typography scales and flag likely performance/privacy problems. It must not invent licensing rights or silently download/redistribute restricted assets.

## 16. Multisite

- site font library by default;
- network shared approved family library;
- site may reference network asset without duplicating file where topology permits;
- network-enforced privacy floor can forbid external providers;
- site clone revalidates URLs/assets/provider auth;
- per-site assignments remain isolated unless network template/enforcement is explicit.

## 17. MUST NOT

- no unlicensed redistribution;
- no automatic remote font download without allowed source/profile;
- no all-weights preload;
- no arbitrary global selector injection in ordinary mode;
- no direct proprietary builder-document mutation without adapter;
- no privacy-compliance guarantee from one technical setting.

## 18. Evidence

Reserved namespace: **FNT-001…FNT-176**, executed **0/176**.

Evidence covers formats/metadata, variable fonts, local uploads, provider adapters, licensing, assignment precedence, theme.json/builders, local hosting, external-font detection, performance, CSP/privacy, Multisite and visual/performance regression profiles.