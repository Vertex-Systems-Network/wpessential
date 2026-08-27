# ADR-0030 — User/Profile Identity Security Boundaries

Status: **Accepted security architecture / runtime evidence pending**  
Date: 2026-08-27

## Decision

User Profile Builder separates:

1. ordinary profile data;
2. sensitive identity changes;
3. authentication credentials/session actions;
4. authorization state;
5. opaque WordPress/plugin security internals.

Generic Custom Fields may never directly mutate password/reset/session/Application Password/role/capability/Membership authority or protected security meta.

Email and password changes use dedicated security workflows. Editing another user uses object-aware WordPress capability checks plus field/action policy.

## Why

A generic user-meta editor creates mass-assignment and privilege-escalation risk. WordPress itself treats user email confirmation, session tokens and Application Passwords as special security workflows/data.

## Consequences

- protected user-meta registry is required;
- roles/capabilities remain Role Manager operations;
- Membership remains Membership state-machine operation;
- Application Passwords use dedicated WordPress API/component;
- public profile has explicit allowlist and hides personal/security data by default;
- account deletion is a separate destructive workflow.

## Evidence still required

After explicit consent:
- protected meta fixture matrix;
- email change confirmation/replay tests;
- password/session behavior;
- Application Password secrecy;
- multisite/Super Admin edit-user cases;
- public-profile IDOR/privacy tests.

Supporting security doc: `docs/SECURITY/USER-PROFILE-IDENTITY-CHANGE-SECURITY.md`.