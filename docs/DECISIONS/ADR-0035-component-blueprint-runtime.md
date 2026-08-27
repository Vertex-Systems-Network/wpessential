# ADR-0035 — Shared Component Blueprint Runtime

Status: **Accepted architecture / renderer-adapter evidence pending**  
Date: 2026-08-27

## Decision

Builder Widgets, Frontend Dashboard and Dynamic Listings/Templates share one canonical Component Blueprint model:

**Blueprint Definition → Published Compiled Blueprint → Instance Settings/Bindings → Authorized Render Context → Shared Renderer → Target Adapter/Markup/Assets**.

Elementor, Gutenberg, Bricks, WPBakery, Visual Composer and future builders are adapters, not canonical WPE data/runtime models.

## Why

- avoids duplicate render/security/data-binding logic per builder;
- preserves portability;
- keeps server validation and authorization authoritative;
- enables the same component in Dashboard/Listings/builders/shortcodes;
- gives AI a typed composition contract instead of arbitrary code generation.

## Consequences

- controls, bindings, slots, styles and assets are schema-driven;
- default renderer is WPE server-side;
- builder-native rendering requires certification/fidelity declaration;
- personalized output caching includes access/principal context;
- arbitrary PHP/JS/global CSS is excluded from standard Blueprint.

## Evidence still required

After explicit consent: renderer prototype, nesting/binding/cache/asset/accessibility tests and builder adapter certification.

Supporting doc: `docs/ARCHITECTURE/COMPONENT-BLUEPRINT-RUNTIME-MODEL.md`.