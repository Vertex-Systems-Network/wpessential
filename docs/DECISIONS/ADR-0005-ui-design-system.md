# ADR-0005 — Admin UI / Design System Strategy

Status: **Proposed — Phase 0 blocker**  
Date: 2026-08-27  
Static research refreshed: 2026-08-28

## Context

WPEssential needs a large, consistent admin application across many modules. Product direction asks for React, an Untitled-inspired premium visual language and Lucide where appropriate.

The current compatibility candidate in ADR-0002 is **WordPress 6.9 minimum**, while the planning reference/current WordPress release is **7.1**. This creates an important capability boundary: WordPress 7.1 provides the newer public `wp-theme` semantic token/ThemeProvider foundation, but WPEssential cannot make a 7.1-only Design System capability a hard dependency while claiming a 6.9 minimum.

WordPress 7.1 also continues to use **React 18.3**. Core's attempted React 19 transition was punted beyond 7.1 after mixed React/JSX-runtime compatibility problems. Current Untitled UI React documentation targets React 19.x, so current Untitled UI React cannot be treated as a drop-in canonical runtime dependency inside the supported WordPress admin environment.

WordPress's newer `@wordpress/ui` package remains experimental at this research date. It is therefore explicitly excluded from WPEssential's foundational 1.0 runtime contract unless a later stability review/ADR changes that decision.

Licensing also matters: Untitled UI distinguishes MIT open-source material from PRO/commercial source. PRO source is not approved for redistribution in a downloadable WPEssential builder/plugin without separate written licensing review.

Static evidence lives in `docs/RESEARCH/COMPATIBILITY-UI-TOOLCHAIN-STATIC-RESEARCH.md`.

## Updated proposed decision

### Application/runtime stack

- React + TypeScript;
- use the **WordPress-provided React runtime** rather than shipping a second competing React copy;
- one WordPress-aware build path selected by ADR-0012;
- route/module-level code splitting where supported by the accepted toolchain;
- all domain screens depend on WPEssential wrapper primitives, not vendor components directly;
- runtime feature/package capability is detected against the supported WordPress version rather than inferred from the latest npm documentation.

### Minimum-floor capability rule

WPEssential must remain functional on the eventually accepted minimum WordPress version.

While the candidate floor is WordPress 6.9:

- no WPEssential module may require `wp-theme`/ThemeProvider merely to boot or render core administration;
- WPEssential owns a small semantic token contract with a minimum-floor compatible implementation/fallback;
- on WordPress 7.1+ the wrapper/theme layer may map/enhance that contract using stable `wp-theme`/ThemeProvider capabilities;
- component/package imports must be selected/versioned so they are compatible with the actual WordPress floor under P-001/P-002/P-008 evidence;
- absence of a newer optional Design System capability must degrade predictably, not fatal or silently restyle unrelated wp-admin;
- UI convenience must never silently raise the WordPress minimum. Raising the minimum requires ADR-0002 evidence and an explicit decision.

### Component priority

Use this order of preference:

1. **WPEssential wrapper components** — canonical product-facing API.
2. Stable public WordPress primitives available on the accepted minimum version.
3. Stable `@wordpress/components` and compatible `@wordpress/dataviews` / DataForm capabilities for WordPress-native admin interactions, lists, filters, forms, actions and accessibility.
4. WordPress 7.1+ stable `wp-theme` semantic tokens/ThemeProvider through the WPE wrapper/theme adapter where capability is available.
5. WPEssential-owned layout/composition components to create the premium visual language and cross-module consistency.
6. Clearly MIT-licensed Untitled UI pieces only when license and supported-React compatibility are verified and they materially improve UX.
7. Experimental WordPress packages/APIs — including current `@wordpress/ui` and experimental page/route/widget facilities — are not foundational contracts until separately accepted.

### Untitled UI role

Untitled UI remains a **design/interaction reference**, not the mandatory runtime framework.

Preserve desirable qualities through WPE-owned wrappers:
- clean spacing and hierarchy;
- premium modern controls;
- restrained visual density;
- excellent empty/loading/error/degraded states;
- consistent cards/tables/forms;
- clear destructive/permission states;
- polished responsive behavior.

Do not bind WPEssential to React-19-only assumptions or redistribute restricted PRO source.

### Licensing rule

- MIT-marked Untitled UI open-source components are candidate inputs, subject to attribution/compliance and compatibility review.
- Untitled UI PRO source/components/pages are **not approved for distribution** in WPEssential by default.
- Any future PRO usage requires separate written license review covering a downloadable/commercial WordPress builder/plugin.

### Icon strategy

Lucide remains the preferred visual icon vocabulary, but domain code must not scatter raw `lucide-react` imports.

Use a `WPEssentialIcon`/icon-registry abstraction so implementation can:
- render reviewed Lucide-compatible SVG definitions without introducing a second React dependency;
- integrate WordPress SVG Icon capabilities where useful and compatible;
- avoid library/runtime lock-in;
- provide accessible names/labels;
- change rendering strategy without rewriting modules.

Exact package/rendering choice remains blocked on the UI/build evidence.

### Styling and theme behavior

- WPEssential owns semantic design tokens and component-level styles;
- map to stable WordPress tokens only where the supported runtime exposes them;
- no global wp-admin reset or broad selector overrides;
- styles are scoped to WPE roots/components;
- portals/dialogs/tooltips/popovers must remain correctly scoped even when rendered outside the immediate root;
- third-party adapter assets load only on their own required screens/contexts;
- RTL is a release requirement;
- localization/string expansion must not break layout;
- WordPress/admin color schemes, high-contrast/user preferences and larger text must not produce unreadable states;
- do not rely on color alone for state;
- product-specific dark mode is not a 1.0 architectural requirement unless separately accepted; the initial contract prioritizes compatibility with WordPress/host behavior.

### UX grammar

All builder modules follow:

**List → Create/Edit → Configure → Preview/Test → Publish/Enable → Observe → Version/Export**

Common list/editor/delete/revision/secret states are defined in `docs/MODULES/COMMON-OPTION-CONTRACTS.md`.

## Accessibility requirements

Target WCAG 2.2 AA-oriented behavior for new/updated WPEssential interfaces where applicable:

- full keyboard operation;
- visible focus;
- semantic labels/descriptions;
- keyboard alternative for drag/reorder;
- screen-reader announcements for meaningful async states;
- no color-only meaning;
- correct dialog/popover focus management and restoration;
- accessible validation/errors;
- loading/empty/error/success/disabled/permission-denied states;
- reduced-motion consideration for non-essential animation;
- usable narrow/mobile wp-admin layouts and zoom/text expansion.

## Why wrappers are mandatory

Without wrappers, module code would couple to React version quirks, WordPress package churn, version-specific Design System capabilities, Untitled licensing/runtime changes and future migrations.

Wrappers provide:
- stable WPEssential props/events;
- capability/version adapters for WP 6.9 vs later features;
- access-control-aware patterns;
- consistent validation/errors;
- centralized accessibility fixes;
- asset and styling control;
- migration isolation.

## Open questions blocking acceptance

1. Which build tool is canonical after ADR-0012 evidence?
2. Which exact WordPress package versions/entrypoints are safe on the eventual minimum WordPress version?
3. What is the exact minimum-floor token/theme fallback and the 7.1+ `wp-theme` adapter behavior?
4. What wrapper surface is small enough to maintain but broad enough for 30+ modules?
5. Which exact Untitled MIT components, if any, are worth adapting?
6. How is Tailwind avoided/isolated if an imported MIT component assumes it?
7. How are DataViews/DataForm wrapped without fighting their native interaction model?
8. What is the exact icon registry/rendering choice?
9. How are portals/iframes/editor contexts, RTL, localization, browser support and accessibility verified?
10. Which future WordPress Design System packages can be adopted without introducing an experimental hard dependency?

## Acceptance evidence

A future executable P-002 protocol must test representative WPE admin surfaces on minimum/current WordPress profiles and prove at least:

- WPE admin shell;
- DataView/list/search/filter/sort/pagination/bulk actions;
- form/editor/validation;
- modal/popover/toast/confirmation;
- loading/empty/error/degraded/permission/destructive states;
- icon rendering;
- keyboard/focus/screen-reader behavior;
- drag/reorder keyboard alternative;
- RTL/localization/text expansion;
- responsive/narrow admin;
- scoped CSS against unrelated wp-admin;
- capability-gated minimum-WP token fallback and 7.1+ theme path;
- no hard dependency on experimental `@wordpress/ui`;
- production bundle/route-asset measurement;
- no duplicate React/JSX runtime;
- supported WordPress minimum/current visual and interaction regression.

No executable spike may be performed without explicit owner consent under ADR-0014.

## Current recommendation

**WPEssential wrappers + WordPress-provided React + stable minimum-version-compatible WordPress primitives; enhance through stable WordPress 7.1+ `wp-theme` capabilities when present. Untitled remains visual reference/selective MIT input. Experimental `@wordpress/ui` is not a 1.0 foundation.**

This ADR remains **Proposed** until executable P-002/P-008 evidence confirms the wrapper/build/runtime combination.
