# ADR-0037 — Admin Menu Transformation & Safe Mode

Status: **Accepted architecture / hook compatibility evidence pending**  
Date: 2026-08-27

## Decision

Custom Admin Menu Builder stores **stable transformation rules over a runtime-discovered WordPress admin-menu registry**, not a cloned raw `$menu` array as canonical configuration.

Menu visibility/order/label is separate from screen registration and authorization. Hiding a menu item never means its screen is disabled.

On invalid/corrupt menu rules, WPE fails back to original WordPress/plugin navigation. A safe/recovery mode can bypass WPE menu transformations but never bypass WordPress authentication/capabilities.

## Why

WordPress/plugin menus are registered dynamically and may change across plugin versions. Raw positions/indexes are brittle, and menu hiding is only presentation—not security.

## Consequences

- stable matching prefers page/menu slug + parent/context;
- ordering composes with WordPress custom menu ordering semantics;
- ambiguous external targets do not get guessed;
- WPE parent/recovery navigation has anti-lockout safeguards;
- external page capability changes remain Role/Policy/certified adapter responsibility;
- native WP sidebar is top-level + submenu; deeper app nav belongs inside WPE pages/Dashboards.

## Evidence still required

After explicit consent: admin hook/priority fixtures, common plugin conflicts, site/network admin, safe mode, recovery invariants, direct URL authorization and per-request performance.

Supporting doc: `docs/ARCHITECTURE/ADMIN-MENU-TRANSFORMATION-CONFLICT-SAFE-MODE.md`.