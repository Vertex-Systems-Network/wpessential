# ADR-0170 — OAuth Account-Link Canonical Evidence Refinement

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP53`  
Development authorization: **NOT GRANTED**

## Decision

Accept the in-place refinement of `docs/QUALITY/OAUTH-ACCOUNT-LINK-EXECUTABLE-EVIDENCE-PROTOCOL.md` from OA-01…OA-32 to **OA-01…OA-176**, preserving all original fixtures.

The profile remains a public OAuth client architecture with transaction-specific PKCE S256, fixed/exact redirect policy, issuer/environment binding, one-time site-bound completion artifact, short-lived access token, rotated Vault-backed refresh credential and optional separately certified Device Authorization fallback.

The expanded evidence covers authorization-server metadata, PKCE/state/issuer/mix-up defenses, redirect/callback/browser safety, completion-artifact redemption, token/Vault lifecycle, WordPress/Product License/site-allocation trust separation, Device Flow, Multisite/clone/lifecycle, privacy/abuse/observability and upgrade/regression.

## Preserved invariants

- WordPress principal, WPE Account, OAuth connection, Product entitlement, Site Allocation, Role/Capability, Membership and update authority are distinct truths.
- Account linking never grants WordPress administration or Membership access by itself.
- No reusable confidential client secret ships in the WordPress plugin.
- First profile requires PKCE S256 and exact redirect policy; wrong issuer/mix-up/open-redirect/replay fails.
- Access/refresh token, PKCE verifier and completion artifact never enter browser URLs/logs/support/export surfaces.
- Refresh credential remains Vault-owned and rotation/replay-aware.
- Clone/restore/domain changes require explicit reconciliation and cannot silently produce a usable production credential.

## Evidence status

- OA fixtures documented: **176**
- OA fixtures executed: **0/176**
- OAuth Account-Link runtime certifications: **0**

No OAuth endpoint, browser redirect, code/token exchange, refresh/revoke, Site Allocation mutation, Device Authorization poll, Vault operation or network request was executed.

## Consequence

`P0-M00-WP53` is planning-complete once canonical registries and Draft PR synchronize. Runtime implementation/evidence remains blocked by ADR-0014 and the Approval Ledger.
