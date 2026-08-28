# ADR-0191 — Admin Theme, Branding & Experience Manager

Status: **Accepted planning architecture / evidence pending / no development authorization**  
Date: 2026-08-29

## Context

The owner requested audits of Admin Color Schemes and Admin Color Schemer and asked for a competitive admin-theme module. Existing WPE Admin Menu, Dashboard Widgets and Settings surfaces do not own the visual token/branding/assignment lifecycle, so this capability is a genuinely missing reusable user-facing primitive.

WordPress admin theming is version-sensitive: native admin color schemes remain relevant, while newer WordPress versions expose broader Design System theming/token capabilities. WPE must therefore be capability-adaptive instead of hard-coding a legacy selector theme.

## Decision

Accept new **Surface 49 — Admin Theme, Branding & Experience Manager**.

Canonical product specification:
`docs/MODULES/ADMIN-THEME-BRANDING-EXHAUSTIVE-SPEC.md`

The module owns:
- theme definitions and revisions;
- semantic color/token mapping;
- native admin color-scheme compatibility output;
- WordPress Design System token integration where available;
- typography/geometry/density profiles where stable;
- accessibility/contrast validation;
- user/role/site/network/environment assignment resolution;
- production/staging/development identity cues;
- admin bar and login presentation branding;
- preview/compare;
- compatibility diagnostics;
- import/export and rollback.

## Boundaries

- Visual hiding never revokes access.
- Do not fork wp-admin templates as canonical implementation.
- Do not promise a token/control on WordPress versions that do not expose a certified compatible surface.
- Raw arbitrary admin JavaScript belongs outside this module.
- Network-enforced branding/theming requires network authority.

## Evidence

Reserve **ATM-001…ATM-176**, executed **0/176**.

## Development gate

No admin color scheme, CSS artifact, token override, assignment, login branding, network policy, preview runtime or compatibility test is authorized by this ADR.