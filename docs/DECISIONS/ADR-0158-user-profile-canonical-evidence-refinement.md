# ADR-0158 — User Profile Canonical Executable Evidence Refinement

Status: **Accepted evidence refinement; execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP41`  
Execution mode: `PLANNER_ONLY`

## Decision

Refine the existing canonical User Profile executable-security protocol in place from `UP-01…UP-48` to **`UP-01…UP-176`**, preserving the original fixture intent while binding the surface to the newer shared platform contracts.

The canonical evidence file is:

- `docs/QUALITY/USER-PROFILE-EXECUTABLE-EVIDENCE-PROTOCOL.md`

## Preserved authority boundary

WordPress remains authoritative for native user identity/authentication/security state. WPEssential may provide typed, policy-guarded user-profile configuration and actions, but a generic Profile field or Data Source write must never become a route to mutate:

- password hashes or password-reset state;
- roles/capabilities or Super Admin state;
- session tokens/auth cookies;
- Application Password secret material;
- Membership entitlements/commercial state;
- Vault/provider/product-license secrets;
- protected third-party security metadata.

Dedicated security-sensitive actions retain their own current-authority, recent-auth, replay, audit and recovery semantics.

## Refinement scope

The fixed `UP-01…UP-176` matrix now covers, without automatically certifying dependencies:

- self/admin/object-level authorization and target spoofing;
- protected-field registry and mass-assignment resistance;
- native identity-field semantics including login/email/display/public identifiers;
- account-enumeration and public projection leakage controls;
- Field Storage and Data Source routing, typing, versioning and conflict handling;
- email-change confirmation, replay, supersession, race and delivery-failure recovery;
- password, session and Application Password action separation;
- current-user and administrator sensitive-action boundaries;
- avatar/media/private-asset handling where configured;
- third-party profile-hook/custom-meta coexistence and ownership drift;
- Dynamic Value/Listing/REST exposure and shared Cache/Invalidation revocation behavior;
- Privacy export/erase/retention boundaries;
- Definition/version/deprecation/module-disable/Pro-expiry behavior;
- Multisite global-user vs site-scoped profile data and Super Admin boundaries;
- large-user/network scale and cross-site isolation.

## Dependency truth

A User Profile fixture may depend on FST, DSR, KPA, RA, PDL, ERR, CAC, VER, MLC, MSI/LC, Asset/Media, REST/Listings or other domain contracts. Passing one shared dependency does not auto-pass a User Profile fixture, and passing User Profile does not promote the dependency.

## Current evidence state

- `UP-01…UP-176` documented.
- **UP executed: 0/176.**
- User Profile runtime certifications: **0**.
- No user/profile mutation, email confirmation, password/session/Application Password action, privacy operation, cache invalidation, Multisite user operation, browser/runtime test or benchmark has executed.

## Development gate

This ADR is planning/evidence documentation only. It does not grant development or executable-test consent. ADR-0014 and `DEVELOPMENT-CONSENT.md` remain authoritative.
