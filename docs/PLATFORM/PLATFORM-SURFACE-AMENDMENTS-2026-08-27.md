# WPEssential — Platform Surface Amendments — 2026-08-27

Status: **Phase 0 planning — authoritative amendment**

This file explicitly supersedes conflicting account/authentication and Pro-acquisition statements in `docs/MODULES/PLATFORM-SURFACES-SPEC.md`.

Detailed authoritative service contract:
- `docs/PLATFORM/REMOTE-SERVICE-API-CONTRACT.md`

Distribution authority:
- `docs/COMMERCIAL-DISTRIBUTION.md`

## Amendment A — Account authentication

Old concept: WordPress wizard directly presents WPE account email/password sign-in/sign-up forms.

**Superseded.**

Current preferred behavior:
- local wizard provides **Continue Free**, **Connect WPEssential Account**, Create Account and Recover Account actions;
- authentication, sign-up, MFA and password recovery occur on the trusted WPE account/service domain;
- preferred account-link candidate is browser-based OAuth Authorization Code + PKCE (`S256`), with Device Authorization as a fallback candidate if callback registration proves unsuitable;
- a distributed WordPress plugin does not ship a reusable confidential OAuth client secret;
- WordPress does not persist/log WPE account passwords;
- long-lived account credentials use the Secrets Vault contract;
- exact account-link callback/token profile remains a Phase 0 blocker.

## Amendment B — Pro acquisition

Old concept: Free wizard may receive Pro package metadata and install/activate the external Pro package from inside wp-admin.

**Superseded.**

Current WordPress.org-target behavior:
1. user explicitly selects Start Trial / Buy / Upgrade;
2. Free opens a trusted WPE service/customer-account page/session;
3. checkout/trial activation happens on WPE service domain;
4. customer receives Pro package/download instructions through that service/account;
5. customer installs WPEssential Pro through WordPress's normal plugin upload/manual administrator flow;
6. activated Pro verifies compatible Free + entitlement.

WPEssential Free must **not** act as an external Pro ZIP installer/updater from non-WordPress.org servers.

A future WPE management service that pushes software from its own domain requires a separate ADR/compliance/security architecture.

## Amendment C — Plans

Remote plan/catalog responses are structured data only. They may include trusted checkout/manage-plan links but never executable PHP/JS/CSS or arbitrary admin HTML.

Plan API outage cannot impair Free CPT/Taxonomy.

## Amendment D — Account disconnect

Disconnect:
- revokes remote credential where reachable;
- deletes local token/secret material;
- keeps Free settings/content;
- does not delete Pro definitions/data;
- clearly explains resulting entitlement-management state.

## Amendment E — Platform service precedence

Where service/account/package behavior in the older platform-surface spec conflicts with this amendment or `REMOTE-SERVICE-API-CONTRACT.md`, the newer specific contract wins.

All other non-conflicting platform-surface requirements—including Home, Modules, Docs, Changelog, Support, Diagnostics, redaction, accessibility and failure-state behavior—remain valid.

## Development gate

This amendment is planning only. No OAuth client, account API, updater, package installation, support API or remote service implementation is authorized without explicit owner development consent under ADR-0014.