# ADR-0189 — Membership Competitive Parity Expansion

Status: **Accepted planning architecture / evidence pending / no development authorization**  
Date: 2026-08-29

## Context

Owner requested a deep audit of Members and WP-Members against WPE Membership and required the WPE module to be fully featured and market-competitive.

Current WPE Membership already owns a broader Plan/Enrollment/Entitlement/Policy architecture than these role/login-focused plugins, so creating another membership runtime would violate the platform reuse rule.

## Decision

Accept `docs/MODULES/MEMBERSHIP-COMPETITIVE-PARITY-EXPANSION.md` as an authoritative addendum to Surface 15.

Add product behavior for:
- Site Access / Private Site profile;
- registration/onboarding studio;
- verification and admin approval flows;
- login/register/profile components;
- restriction defaults and per-resource override;
- teaser/excerpt profiles;
- navigation visibility adapters;
- messages/dialog/email presets;
- member directory/login compositions;
- Members/WP-Members/role-based migration assistants;
- typed SDK/Ability/Event extension parity.

## Domain boundary

Do not collapse:
`WordPress User → Role/Capability → Membership Plan → Enrollment → Entitlement → Access Policy`.

Role and billing source remain non-canonical Membership authorities.

## Evidence

Reserve **MPR-001…MPR-176**, executed **0/176**.

Existing MBR/MB-F/PC-F evidence remains separate and unexecuted.

## Development gate

No registration, user creation, private-site gate, migration, Enrollment, role-sync, email verification or runtime test is authorized by this ADR.