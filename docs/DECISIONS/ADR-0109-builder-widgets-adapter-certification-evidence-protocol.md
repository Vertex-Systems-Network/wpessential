# ADR-0109 — Builder Widgets Adapter Certification Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Decision

Builder Widgets support is capability- and version-certified per builder adapter. Gutenberg/WordPress Blocks, Elementor, Bricks, WPBakery Page Builder and Visual Composer Website Builder remain distinct integrations over the shared WPE Component Blueprint.

A future adapter cannot claim meaningful support until it passes `docs/QUALITY/BUILDER-WIDGETS-ADAPTER-CERTIFICATION-EVIDENCE-PROTOCOL.md` at the stated BC level.

The protocol fixes:
- BC0 Detected, BC1 Registration, BC2 Render Certified, BC3 Advanced and BC4 Upgrade/Regression Certified levels;
- exact builder product/version/edition identity;
- canonical Component Blueprint independence from proprietary builder page/document storage;
- authorized WPE dynamic data/Query/Policy rendering;
- scoped assets and safe missing-builder degradation;
- no arbitrary per-user runtime PHP/JS generation;
- shared semantic reference blueprints across builders;
- Gutenberg, Elementor, Bricks, WPBakery and Visual Composer specific fixture families;
- native dynamic-tag/data bridges only where separately certified;
- nested/container/repeater behavior as explicit advanced capability;
- stored-content compatibility across adapter/version changes;
- no Node/npm/Webpack build requirement on customer production sites for ordinary published components under the preferred profile.

## Current state

BW-01…BW-50 documented. **0/50 executed.**  
Builder runtime certifications: **0**.

## Development gate

No builder package install, block/widget/element registration, editor/browser run, Node build or frontend adapter execution is authorized before explicit owner consent under ADR-0014.