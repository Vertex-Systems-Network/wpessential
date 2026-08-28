# ADR-0125 — P-002 UI / Design System Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Context

ADR-0005 proposes WPEssential-owned UI wrappers over the WordPress-provided React runtime and stable WordPress admin primitives, with Untitled UI as a visual reference/selective MIT input rather than a mandatory runtime framework.

The current compatibility candidate remains WordPress 6.9, while WordPress 7.1 introduces a richer stable `wp-theme`/ThemeProvider foundation. Therefore the UI contract must prove a minimum-floor-compatible path and capability-gated later-version enhancement rather than silently raising the WordPress minimum.

The generic P-002 spike was directionally correct but lacked fixed coverage for cross-version capability gating, React/JSX duplicate detection, experimental package exclusion, wrapper substitution, portal/iframe styling, assistive technology, localization expansion, route asset isolation and release regression.

## Decision

Accept `docs/QUALITY/P002-UI-DESIGN-SYSTEM-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical bounded future P-002 evidence contract.

It defines **UI-01…UI-104** covering:
- minimum/current WordPress UI capability matrix;
- WordPress 6.9-compatible theme/token fallback and WordPress 7.1+ theme enhancement;
- exclusion of current experimental `@wordpress/ui` from foundational dependency;
- single WordPress-provided React/ReactDOM/JSX runtime;
- WPE wrapper contracts and underlying primitive substitution;
- DataViews/DataForm list/search/filter/sort/pagination/bulk/edit behavior;
- semantic tokens, CSS isolation, portals/iframes and third-party adapter style boundaries;
- keyboard/focus/dialog/menu/drag alternatives;
- screen-reader/async/error/reduced-motion/larger-text/high-contrast behavior;
- RTL/localization/string expansion/mixed-direction content;
- narrow/responsive wp-admin behavior;
- loading/empty/degraded/permission/stale/offline/destructive states;
- icon abstraction/content safety/license provenance;
- exact-route assets, lazy failures, bundle/style budgets and duplicate packages;
- minimum/current regression and production-readiness gates.

## Preserved architectural direction

This ADR does not accept ADR-0005 itself and does not select a concrete component package implementation.

Preserved rules:
- domain modules depend on WPE wrappers, not vendor primitives directly;
- WPE uses WordPress-provided React rather than a second framework runtime;
- no 7.1-only Design System capability can be a hard dependency while 6.9 is the minimum candidate;
- stable 7.1+ theme capability may be used through a wrapper adapter when present;
- current experimental `@wordpress/ui` and experimental build page/route/widget facilities are not foundational contracts;
- Untitled remains visual reference/selective MIT candidate only; PRO source is not approved by default;
- no global wp-admin CSS reset;
- accessibility, RTL, localization and exact-route asset scoping are release requirements.

## Evidence state

At acceptance:
- UI fixtures documented: **104**;
- UI fixtures executed: **0/104**;
- P-002 runtime certification: **0**;
- ADR-0005: **Proposed**;
- canonical build tool: **not selected**;
- P-008 evidence: pending separate fixed protocol.

## Selection rule

P-002 cannot pass merely because the UI looks polished. Runtime duplication, minimum-version fatal behavior, inaccessible required workflows, protected-data leakage, global CSS leakage or an unapproved experimental hard dependency are stop-the-line failures regardless of visual quality.

## Development gate

This ADR authorizes no UI/runtime source, package manifest, dependency installation, React render, browser/accessibility execution, build or visual-regression fixture. Explicit owner development/executable-spike consent under ADR-0014 remains required.
