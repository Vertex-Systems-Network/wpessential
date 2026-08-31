# ADR-0167 — Builder Widgets Adapter Canonical Evidence Refinement

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP50`  
Development authorization: **NOT GRANTED**

## Decision

Accept the in-place refinement of `docs/QUALITY/BUILDER-WIDGETS-ADAPTER-CERTIFICATION-EVIDENCE-PROTOCOL.md` from BW-01…BW-50 to **BW-01…BW-176**, preserving all original fixtures and BC0…BC4 certification semantics.

The expanded evidence covers canonical Blueprint/version lifecycle, DSR/Query/DVR/Policy/action security, cache/assets/runtime isolation, deeper Gutenberg/Elementor/Bricks/WPBakery/Visual Composer behaviors, cross-builder semantic parity, migration/version drift, Multisite, accessibility and scale.

## Preserved invariants

- WPE Component Blueprint remains canonical; proprietary builder documents remain adapter-owned representations.
- Builder editor/control visibility never grants data/action authorization.
- User configuration cannot become arbitrary PHP/JS eval or uncontrolled executable code generation.
- Dynamic data resolves through WPE owner services/Policy.
- Missing/deactivated builder cannot fatal WPE globally or delete Blueprint definitions.
- Frontend must not unexpectedly require editor-only runtimes.
- Adapter support is version/edition/capability/BC scoped; unknown newer versions remain uncertified by default.
- Passing one builder/profile never promotes another.

## Evidence status

- BW fixtures documented: **176**
- BW fixtures executed: **0/176**
- Gutenberg/Elementor/Bricks/WPBakery/Visual Composer runtime certifications: **0**
- BC0…BC4 certified adapter profiles: **0**

No builder package installation, editor execution, block/widget/element registration, Node build, browser fixture, cache mutation or frontend render occurred.

## Consequence

`P0-M00-WP50` is planning-complete once canonical registries and Draft PR are synchronized. Implementation/executable evidence remains blocked by ADR-0014 and the Approval Ledger.
