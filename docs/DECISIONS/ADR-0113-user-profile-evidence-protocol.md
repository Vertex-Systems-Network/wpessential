# ADR-0113 — User Profile Security Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Decision

User Profile Builder cannot claim secure profile/identity/auth runtime support until a future implementation passes `docs/QUALITY/USER-PROFILE-EXECUTABLE-EVIDENCE-PROTOCOL.md` for its certified WordPress/Field Storage/Multisite profile.

The protocol preserves:
- UP1 native WordPress identity/auth authority;
- UP2 Field Storage for ordinary custom profile data;
- UP3 minimal security-action state only where core primitives are insufficient;
- protected binding denial for password/reset, role/capability, session, Application Password, Membership, Vault and product-license internals;
- self-vs-admin target authority and mass-assignment resistance;
- allowlisted public/REST/listing projections;
- confirmation/replay/race-safe email changes;
- recent-auth purpose binding;
- typed password/session/Application Password actions;
- site removal vs global account deletion and Super Admin boundaries;
- privacy exporter/eraser integration and security audit redaction;
- native WordPress recovery availability when WPE Profile is degraded.

## Current state

UP-01…UP-48 documented. **0/48 executed.**

## Development gate

No user mutation, email/password/session/Application Password action, privacy operation or runtime test is authorized before explicit owner consent under ADR-0014.