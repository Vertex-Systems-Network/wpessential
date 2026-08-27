# ADR-0005 — Admin UI / Design System Strategy

Status: **Proposed — Phase 0 blocker**  
Date: 2026-08-27  
Static research refreshed: 2026-08-27

## Context

WPEssential needs a large, consistent admin application across many modules. Product direction asks for React, Untitled UI and Lucide where appropriate. WordPress 7.1 now also ships a stronger public admin Design System foundation, including semantic tokens and `ThemeProvider`, while DataViews/DataForm continue to mature for data-heavy plugin interfaces.

A major compatibility fact changes the earlier recommendation: WordPress 7.1 continues to use **React 18.3**, while current Untitled UI React documentation targets **React 19.2**. WordPress Core temporarily tested React 19 and reverted/punted it after mixed React/JSX runtime incompatibilities caused plugin crashes.

Therefore current Untitled UI React cannot be treated as a drop-in canonical runtime dependency inside WordPress 7.1.

Licensing also matters: Untitled UI explicitly distinguishes MIT open-source components from PRO/commercial source. PRO source has redistribution restrictions and is not approved for bundling in a distributed WPEssential builder/plugin without a separate written licensing review.

Static evidence lives in `docs/RESEARCH/COMPATIBILITY-UI-TOOLCHAIN-STATIC-RESEARCH.md`.

## Updated proposed decision

### Application/runtime stack

- React + TypeScript;
- use the **WordPress-provided React runtime** rather than shipping a second incompatible React copy;
- one WordPress-aware build path selected by ADR-0012;
- route/module-level code splitting where supported by the accepted toolchain;
- all domain screens depend on WPEssential wrapper primitives, not vendor components directly.

### Component priority

Use this order of preference:

1. **WPEssential wrapper components** — canonical product-facing API.
2. Stable public WordPress 7.1 Design System primitives/tokens via `@wordpress/theme` where appropriate.
3. Stable `@wordpress/components` and `@wordpress/dataviews` / DataForm for WordPress-native admin interactions, lists, filters, forms, actions and accessibility.
4. WPEssential-owned layout/composition components to create the premium visual language and cross-module consistency.
5. Clearly MIT-licensed Untitled UI pieces only when:
   - their license status is verified;
   - they are adapted/tested against the supported WordPress React runtime;
   - they do not force a second React runtime;
   - they materially improve UX over an existing WPEssential/WordPress primitive.
6. Experimental WordPress UI/pages/widgets APIs are not foundational contracts until their stability changes.

### Untitled UI role

Untitled UI remains a **design/interaction reference**, not the mandatory runtime framework.

WPEssential should preserve the desired Untitled-style qualities:
- clean spacing and hierarchy;
- premium modern controls;
- restrained visual density;
- excellent empty/loading/error states;
- consistent cards/tables/forms;
- clear destructive/permission states;
- polished responsive behavior.

But these qualities should be implemented through WPEssential-owned wrappers over compatible public primitives rather than binding the product to React 19.2-only assumptions.

### Licensing rule

- MIT-marked Untitled UI open-source components are candidate inputs, subject to attribution/compliance and compatibility review.
- Untitled UI PRO source/components/pages are **not approved for distribution** in WPEssential by default.
- Any PRO usage requires a separate license review specifically covering a downloadable/commercial WordPress builder/plugin.

### Icon strategy

Lucide remains the preferred visual icon vocabulary, but domain code should not scatter raw `lucide-react` imports.

Use a `WPEssentialIcon`/icon-registry abstraction so implementation can:
- use Lucide-compatible SVG definitions;
- integrate WordPress 7.1 SVG Icon API where useful;
- avoid library/runtime lock-in;
- provide accessible names/labels;
- change rendering strategy without rewriting modules.

Exact package/rendering choice remains blocked on the UI/build spike.

### Styling

- use WordPress semantic design tokens where they improve admin consistency;
- WPEssential owns a small semantic token layer mapped to public WordPress tokens where practical;
- no global wp-admin resets;
- styles scoped to WPEssential roots/components;
- third-party adapter assets load only on their own screens;
- RTL is a release requirement;
- do not rely on color alone for state.

### UX grammar

All builder modules follow:

**List → Create/Edit → Configure → Preview/Test → Publish/Enable → Observe → Version/Export**

Common list/editor/delete/revision/secret states are defined in `docs/MODULES/COMMON-OPTION-CONTRACTS.md`.

## Accessibility requirements

Target WCAG 2.2 AA-oriented behavior for new/updated WPEssential interfaces where applicable:

- full keyboard operation;
- visible focus;
- semantic labels/descriptions;
- keyboard alternative for drag/drop;
- screen-reader announcements for meaningful async states;
- no color-only meaning;
- correct dialog focus management;
- accessible validation/errors;
- loading/empty/error/success/disabled states;
- reduced-motion consideration for non-essential animation.

## Why wrappers are mandatory

Without wrappers, module code would couple to React version quirks, WordPress component API changes, Untitled licensing/runtime changes and future design-system migrations.

Wrappers provide:
- stable WPEssential props/events;
- access-control-aware patterns;
- consistent validation/errors;
- centralized accessibility fixes;
- asset and styling control;
- migration isolation.

## Open questions blocking acceptance

1. Which build tool is canonical after ADR-0012 evidence?
2. Which WordPress Design System packages/entrypoints are stable enough for the minimum WP version?
3. What wrapper surface is small enough to maintain but broad enough for 30+ modules?
4. Which exact Untitled MIT components, if any, are worth adapting?
5. How is Tailwind avoided/isolated if an imported MIT component assumes it?
6. How are DataViews/DataForm wrapped without fighting their native interaction model?
7. What is the exact icon registry/rendering choice?
8. How are RTL, localization and browser support verified?
9. Does WPEssential need product-level light/dark mode, or should admin follow host/system/WordPress behavior initially?

## Acceptance evidence

A future executable spike must compare a representative admin experience using only approved runtime assumptions:

- WPEssential admin shell;
- DataView/list screen;
- form/editor screen;
- modal/toast/confirmation;
- filter/search/bulk action;
- icon rendering;
- keyboard/focus behavior;
- RTL build;
- scoped CSS against unrelated wp-admin;
- production bundle measurement;
- no duplicate React runtime;
- supported WordPress minimum/current versions.

No such executable spike may be performed without explicit owner consent under ADR-0014.

## Current recommendation

**WPEssential wrappers + WordPress 7.1 public Design System/DataViews runtime primitives, with Untitled UI as visual reference and selectively adapted MIT source only when compatible.**

This ADR remains Proposed until executable evidence confirms the wrapper/build/runtime combination.
