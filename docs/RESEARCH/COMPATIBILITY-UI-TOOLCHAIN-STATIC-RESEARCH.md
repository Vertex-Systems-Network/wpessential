# WPEssential — Compatibility, UI and Toolchain Static Research

Status: **Phase 0 static research — no executable spike performed**  
Research date: 2026-08-27

This note updates ADR-0002, ADR-0005 and ADR-0012 using current official/primary documentation. It does **not** constitute runtime verification and does not authorize development.

## Sources reviewed

Primary/current sources:

- WordPress Requirements — https://wordpress.org/about/requirements/
- WordPress PHP compatibility matrix — https://make.wordpress.org/core/handbook/references/php-compatibility-and-wordpress-versions/
- WordPress PHP support clarification (May 2026) — https://make.wordpress.org/core/2026/05/22/php-support-clarification-2026/
- PHP supported versions — https://www.php.net/supported-versions.php
- WordPress Abilities API — https://developer.wordpress.org/apis/abilities-api/
- WordPress 7.1 release/developer notes — https://developer.wordpress.org/news/2026/08/whats-new-for-developers-august-2026/
- WordPress 7.1 React 19 punt — https://make.wordpress.org/core/2026/07/24/react-19-punted-beyond-wordpress-7-1-experiment-in-gutenberg/
- WordPress React 19 temporary revert background — https://make.wordpress.org/core/2026/06/05/react-19-upgrade-temporarily-reverted-in-gutenberg/
- `@wordpress/build` — https://developer.wordpress.org/block-editor/reference-guides/packages/packages-wp-build/
- `@wordpress/scripts` — https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/
- dependency extraction plugin — https://developer.wordpress.org/block-editor/reference-guides/packages/packages-dependency-extraction-webpack-plugin/
- `@wordpress/theme` — https://developer.wordpress.org/block-editor/reference-guides/packages/packages-theme/
- `@wordpress/dataviews` — https://developer.wordpress.org/block-editor/reference-guides/packages/packages-dataviews/
- WordPress Design System package guidance — https://developer.wordpress.org/block-editor/contributors/design/design-system-packages/
- Untitled UI React introduction — https://www.untitledui.com/react/docs/introduction
- Untitled UI license — https://www.untitledui.com/license
- Untitled UI FAQ — https://www.untitledui.com/faqs

---

# 1. Compatibility findings

## WordPress minimum

The Abilities API is only available in WordPress 6.9+. WPEssential has already chosen Abilities as a first-class action/AI contract.

Therefore **WordPress 6.9 remains the strongest minimum candidate** unless WPEssential deliberately chooses to maintain a compatibility implementation for older WordPress versions.

WordPress 7.1 is the current primary development/reference target for planning.

### Static conclusion

- Recommended minimum candidate: **WordPress 6.9**.
- Primary current target: **WordPress 7.1**.
- Do not accept the ADR until install/runtime/CI evidence exists.

## PHP minimum

Current official WordPress requirements recommend **PHP 8.3+**.

WordPress 7.0/7.1 still technically supports PHP 7.4+, but WPEssential is not obligated to inherit WordPress's broad legacy runtime floor.

PHP lifecycle on 2026-08-27:

- PHP 8.2: security support ends **2026-12-31**;
- PHP 8.3: security support ends **2027-12-31**;
- PHP 8.4: active support until **2026-12-31**, security until **2028-12-31**;
- PHP 8.5: active support until **2027-12-31**, security until **2029-12-31**.

### Changed recommendation

The earlier PHP 8.2 development/beta preference is now weak for a new long-lived platform because it reaches end of security support only months after this planning date.

**New static recommendation: PHP 8.3 minimum from the first production codebase**, subject to executable compatibility and market/install-base validation.

Reasons:

- aligns with WordPress's current recommended PHP baseline;
- avoids designing a new platform around a runtime reaching security EOL in December 2026;
- gives a materially longer support runway than PHP 8.2;
- reduces the chance that WPEssential must raise its PHP minimum during early adoption.

PHP 8.4/8.5 should be CI targets even if the minimum remains 8.3.

### Still not Accepted

Need executable proof against:

- WordPress 6.9/current;
- PHP 8.3/8.4/8.5;
- selected Composer dependencies;
- integrations/build/release tooling.

---

# 2. React compatibility finding

## WordPress 7.1 runtime

WordPress 7.1 continues to use **React 18.3**. The planned React 19 Core upgrade was punted beyond 7.1.

Core's temporary React 19 rollout exposed real compatibility failures when old/new React runtimes and JSX runtimes were mixed. This is directly relevant to WPEssential because it is a large wp-admin React application.

## Untitled UI current runtime

Untitled UI's current React documentation lists:

- React 19.2;
- TypeScript 5.9;
- Tailwind CSS 4.3;
- React Aria 1.20.

Therefore current Untitled UI React cannot be assumed to be a drop-in runtime foundation inside WordPress 7.1.

### Static conclusion

Do **not** make current Untitled UI React itself the canonical runtime/component dependency for WPEssential 1.0 while the supported WordPress runtime is React 18.3.

WPEssential can still use:

- Untitled UI as visual/UX reference;
- clearly MIT open-source pieces only after compatibility review/adaptation;
- layouts/interactions recreated through WPEssential-owned wrappers;
- no PRO source redistribution without a separate licensing decision.

This preserves the desired visual quality without introducing a second incompatible React runtime.

---

# 3. Untitled UI licensing finding

Untitled UI has separate open-source and commercial material.

Official license currently states that:

- explicitly open-source components are MIT and excluded from the commercial license restrictions;
- PRO/commercial files/source have redistribution restrictions;
- raw PRO React source must not be exposed/distributed;
- using Untitled UI assets to create a competing UI kit/library/builder is restricted under commercial terms.

WPEssential is itself a builder/platform distributed to third parties. Therefore a blanket assumption that paid Untitled UI source can be bundled in WPEssential is unsafe.

### Static licensing rule

- **Allowed candidate:** explicitly MIT-marked open-source components/code, subject to notice/compliance and React compatibility.
- **Not approved:** PRO component/page source in distributed WPEssential artifacts.
- Any future PRO usage needs written licensing review specific to a distributed WordPress builder/plugin.

---

# 4. WordPress 7.1 Design System finding

WordPress 7.1 ships foundational admin Design System support:

- semantic design tokens via `@wordpress/theme` / `wp-theme`;
- `ThemeProvider` for application-scoped token overrides;
- public WordPress design-system packages;
- improved admin component sizing/tooltips/accessibility;
- DataViews/DataForm/View Config maturation.

`@wordpress/dataviews` provides:

- table/grid/list layouts;
- search/filter/sort;
- pagination;
- bulk actions;
- field/action APIs;
- DataForm editing integration;
- composition modes.

These overlap substantially with the repeated list/editor patterns WPEssential needs across 30+ modules.

### Static recommendation

Canonical WPEssential admin UI should be:

1. **WPEssential-owned wrapper API** as the only domain-facing component contract;
2. WordPress 7.1 public Design System tokens/theme as the base admin runtime where supported;
3. stable `@wordpress/components`, `@wordpress/dataviews`, `@wordpress/theme` behind wrappers for lists/forms/admin primitives;
4. WPEssential visual tokens/layout conventions layered on top to achieve the desired premium/Untitled-style visual language;
5. MIT Untitled components only when compatibility-reviewed and materially better than a WordPress/WPEssential primitive;
6. avoid experimental WordPress UI/pages/widgets APIs as foundational contracts until stable.

This is a stronger architecture than importing one external UI library across every module.

---

# 5. Build-tool findings

## `@wordpress/scripts`

Still a mature WordPress-tailored toolset with:

- production build/watch;
- linting/formatting;
- unit/E2E helpers;
- plugin ZIP;
- WordPress dependency externalization through its webpack setup;
- `block.json` workflows.

It remains a viable fallback/reference baseline.

## `@wordpress/build`

WordPress now documents `@wordpress/build` as an **opinionated build system designed for WordPress plugins**.

Current documented capabilities include:

- TypeScript/JSX transpilation;
- CommonJS and ESM outputs;
- esbuild;
- SCSS/CSS Modules;
- LTR/RTL styles;
- browser-ready bundles;
- PHP registration generation;
- watch mode;
- third-party plugin namespace support;
- automatic externalization of `@wordpress/*`, React, lodash, jQuery, moment;
- `.asset.php` generation.

Its admin page/routes and widgets facilities are currently marked experimental; those experimental features must **not** be required by WPEssential's architecture.

### Changed recommendation

The previous assumption that Vite should be the primary candidate is no longer strongest.

**New evaluation order:**

1. `@wordpress/build` for canonical plugin build/externalization, using only stable build capabilities;
2. `@wordpress/scripts` as mature WordPress-native fallback/comparison;
3. custom Vite only if WPEssential's proven requirements cannot be met cleanly by WordPress-native tooling.

Why:

- WordPress runtime/package externalization is a first-order requirement, not generic SPA ergonomics;
- `@wordpress/build` already solves the exact problem of externalizing WordPress and React globals and generating asset metadata;
- using Vite would require WPEssential to own/verify equivalent WordPress-aware integration;
- React mixed-runtime failures make correct externalization especially important.

### Still needs executable spike

Before ADR-0012 becomes Accepted, compare:

- incremental build speed;
- production chunking/lazy imports;
- stable multi-entry output;
- asset registry integration;
- CSS Modules/scoping;
- translations;
- plugin ZIP/release path;
- bundle size;
- source maps;
- test integration;
- React/WordPress dependency duplication;
- compatibility with the selected minimum WordPress version.

No spike has been executed because owner development/executable-spike consent has not been granted.

---

# 6. Icon direction

WordPress 7.1 adds a public SVG Icon API. WPEssential can keep Lucide as the visual icon vocabulary while insulating actual rendering behind a `WPEssentialIcon` abstraction.

Planning direction:

- use Lucide icon designs where licensing/compatibility permits;
- prefer a WPEssential icon registry rather than scattering `lucide-react` imports throughout modules;
- use WordPress SVG Icon API where it improves admin/editor interoperability;
- preserve accessible labels and never rely on icon/color alone for state.

Exact implementation remains pending the UI/build spike.

---

# 7. Updated static recommendations

| Decision | Previous recommendation | Updated static recommendation | Accepted? |
|---|---|---|---|
| WP minimum | 6.9 | **6.9** | No — executable matrix pending |
| PHP minimum | 8.2 beta / review 8.3 | **8.3 from production codebase** | No — executable/market matrix pending |
| React | generic React | **WordPress-provided React runtime; 7.1 = React 18.3** | No — build spike pending |
| UI base | hybrid Untitled/WP | **WPEssential wrappers + WP Design System/DataViews; Untitled visual/MIT selective** | No — UI spike pending |
| Untitled PRO source | license review | **not approved for distribution by default** | Planning rule |
| Build | Vite preferred | **`@wordpress/build` first candidate; wp-scripts fallback; Vite evidence-based fallback** | No — build spike pending |
| Icons | Lucide React | **Lucide visual vocabulary behind WPEssential/WP icon abstraction** | No — UI spike pending |

# 8. No-development statement

No package was installed, no build was created, no source code was written, and no runtime test was executed as part of this research.

The next step for these ADRs is either:

- accept only those semantics that can be decided from static evidence; or
- prepare executable spike protocols and request explicit owner consent before running them.
