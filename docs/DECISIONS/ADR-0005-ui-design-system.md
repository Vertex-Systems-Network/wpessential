# ADR-0005 — Admin UI / Design System Strategy

Status: **Proposed — Phase 0 blocker**  
Date: 2026-08-27

## Context

WPEssential needs a large, consistent admin application across many modules. Product direction asks for React, Untitled UI and Lucide where appropriate. WordPress also provides stable admin-oriented packages such as `@wordpress/components` and `@wordpress/dataviews`.

Untitled UI's current React system is based on modern React/TypeScript/Tailwind/React Aria. Its documentation states that components explicitly marked open source are MIT licensed, while paid assets/components have commercial redistribution restrictions.

Lucide is ISC licensed and tree-shakable.

WordPress's newer `@wordpress/ui` package is currently documented as experimental and should not become a foundational dependency until its stability status changes.

References:
- https://www.untitledui.com/react/docs/introduction
- https://www.untitledui.com/license
- https://lucide.dev/license
- https://developer.wordpress.org/block-editor/reference-guides/packages/packages-components/
- https://developer.wordpress.org/block-editor/reference-guides/packages/packages-dataviews/
- https://developer.wordpress.org/block-editor/reference-guides/packages/packages-ui/

## Proposed decision

### Application stack
- React + TypeScript
- one build system selected by a separate/tooling decision; no mixed Laravel Mix/Vite legacy
- route/module-level code splitting
- shared WPEssential component wrappers

### Component sources
Use a hybrid strategy:

1. **WPEssential wrapper components** are the product-facing API.
2. Use **clearly MIT-licensed Untitled UI React components** where they provide strong modern UX.
3. Use **Lucide** as the default icon system.
4. Use stable `@wordpress/components` / `@wordpress/dataviews` where native WordPress behavior, accessibility or integration makes them superior.
5. Do not build core UX directly on experimental `@wordpress/ui` until stability is reassessed.
6. Do not redistribute paid Untitled UI assets/code unless a separate license review explicitly approves the distribution model.

### Styling
- WPEssential styling is scoped to its application/root/components.
- No global wp-admin reset or broad selectors that restyle third-party screens.
- Third-party builder integration screens load only their adapter assets.
- CSS variables/design tokens are centralized behind WPEssential theme primitives.

### UX grammar
All builder modules follow:

**List → Create/Edit → Configure → Preview/Test → Publish/Enable → Observe → Version/Export**

## Accessibility requirements

Target WordPress ecosystem expectation of WCAG 2.2 AA for new/updated interfaces where applicable.

- keyboard operation
- visible focus
- semantic labels/descriptions
- drag/drop keyboard alternative
- screen-reader announcements for important async states
- no color-only semantics
- loading/empty/error/success/disabled states

## Why wrappers matter

If screens import Untitled UI/WordPress components directly everywhere, a future library/version change becomes a repository-wide rewrite. WPEssential wrapper primitives isolate external UI libraries from domain screens and enforce consistent behavior.

## Open questions blocking acceptance

1. Which bundler and WordPress package externalization strategy is canonical?
2. Does Tailwind 4 fit the generated/distributed plugin architecture without style collision or build complexity?
3. Which exact Untitled UI components are MIT and acceptable for redistribution?
4. How will DataViews/DataForm be wrapped vs native WPEssential tables/forms?
5. What browser support matrix follows from chosen dependencies?
6. How are RTL and WordPress locale considerations tested?

## Acceptance evidence

Build a small non-production UI spike with:
- admin shell
- DataView/list screen
- form/editor screen
- modal/toast
- keyboard navigation
- dark/light handling only if product requires it
- scoped CSS verification against unrelated wp-admin
- production bundle measurement

Then accept/supersede this ADR before Phase 1 UI implementation.
