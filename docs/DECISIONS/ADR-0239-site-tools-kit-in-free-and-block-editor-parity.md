# ADR-0239 — Site Tools Kit moves to Free; block editor parity rules

Status: **ACCEPTED**
Date: **2026-11-26**
Approval: `GOV-OWNER-CONSENT-002`
Supersedes: ADR-0232 decision 1 (Site Kit edition `pro`)

## Context
Owner's 15-point request (2026-11-26): Site Kit must be a Free module named "Site Tools Kit"; kit settings are the single default source for blocks, Elementor and Customizer; blocks need Content/Style/Advanced tabs, responsive device dropdown, units, reset, previews, editor = frontend, A11y/W3C.

## Decision
- Site Kit → Free module display name "Site Tools Kit" (stable runtime ID `site-kit`) (surface 49 `admin-theme` unchanged; option keys unchanged). Pro adds premium extras through filters as planned; current Free Elementor sync registration remains a boundary reconciliation item (not yet certified).
- Rules and work packages: `docs/IMPLEMENTATION/BLOCK-EDITOR-PARITY-AND-SITE-TOOLS-KIT-V1.md`.

## Consequences
Free grows; Pro loses Site Kit as a paid item, keeps extras. No data migration because keys stay the same.
