# WPEssential — OAuth Account-Link Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0014, ADR-0034, Remote Service API Contract, OAuth Threat Model, RFC 9700.

## 1. Purpose

Predefine the exact evidence required before WPEssential can claim Account Linking is secure/production-ready.

The accepted first architecture remains:

`Authorized WP admin → local one-time transaction → WPE browser authorization → fixed WPE callback → one-time site-bound completion artifact → local redemption with PKCE S256 → short-lived access token + rotated refresh credential → signed entitlement fetch`

No client/service implementation is authorized by this document.

## 2. Public-client invariant

WPE WordPress plugin is a public OAuth client.

Future implementation must prove:
- no reusable confidential client secret is shipped/required;
- PKCE is transaction-specific;
- challenge method is S256;
- verifier never appears in authorization URL/log/browser JS;
- authorization code/completion artifact cannot be redeemed without matching local transaction/verifier;
- refresh-token replay is detected via rotation or an accepted sender-constrained profile.

## 3. Local transaction record candidate

Each pending link transaction conceptually stores:
- random transaction UUID;
- high-entropy state handle/value or protected hash according to implementation;
- PKCE verifier secret;
- PKCE challenge + method;
- expected issuer/environment;
- fixed WPE callback profile ID;
- exact bound site-return URI/origin/path;
- local installation UUID;
- target network/site scope;
- initiating WP user ID;
- initiating capability/policy generation/fingerprint where useful;
- created/expires;
- consumed/failed/expired state;
- safe correlation ID.

Transaction storage must be private, bounded and destroyed after terminal state/retention policy.

## 4. Completion artifact candidate

Browser receives only a one-time short-lived completion artifact after the fixed WPE callback.

Artifact/service record binds:
- WPE authorization transaction;
- original local transaction identifier;
- installation/site target;
- exact bound return destination;
- PKCE challenge or transaction proof reference;
- authorized WPE Account identity server-side;
- issued/expires;
- one-time redemption state;
- issuer/environment.

It is **not** an access token, refresh token or signed Product Entitlement.

## 5. Token lifecycle profile to prove

### Access token
- short-lived;
- least scopes;
- server-to-server only;
- not persisted longer than required;
- never in URL/browser storage/log/support bundle.

### Refresh credential
- Vault P3 secret;
- refresh-token rotation preferred first evidence profile;
- previous token invalid after rotation according to server contract;
- reuse/replay detection revokes or quarantines the token family according to accepted policy;
- scoped/bound to Account + site connection;
- disconnect removes local usable secret even if remote revocation is pending.

Exact lifetimes remain service-policy evidence, not hardcoded here.

## 6. Scope profile

Minimum candidate scopes:
- `account:read`;
- `entitlements:read`;
- `sites:manage-self`;
- `support:read`;
- `support:write`.

Tests must prove a token lacking a scope cannot use the corresponding API, and plans/docs/public status do not demand broad Account scope unnecessarily.

## 7. OAuth fixture classes

### OA-01 — Happy path
Authorized admin links correct Account/site. State/verifier/artifact consumed exactly once.

### OA-02 — State replay
Replay old local state/transaction after success. Must fail.

### OA-03 — Completion artifact replay
Redeem same completion artifact twice. Second must fail without duplicate Site Allocation/account link.

### OA-04 — Stolen authorization/completion artifact without verifier
Must fail token/site-link redemption.

### OA-05 — Wrong PKCE verifier
Must fail.

### OA-06 — PKCE downgrade
Attempt `plain`/missing challenge against S256-required client profile. Must fail.

### OA-07 — Constant/reused PKCE challenge
Two transactions accidentally/maliciously reuse challenge. Diagnostics/service policy must detect/reject according to implementation contract.

### OA-08 — Open redirect
Modify return URI/host/path at final browser step. Must fail; no redirect to attacker.

### OA-09 — Scheme downgrade
HTTPS production return changed to HTTP. Must fail except explicitly separated local-development policy.

### OA-10 — Wrong issuer/environment
Production transaction completed using staging/attacker issuer metadata. Must fail.

### OA-11 — OAuth mix-up
Response from wrong configured authorization server/environment. Must fail before tokens used.

### OA-12 — Lower-privilege initiation
User without WPE Account-management capability tries to start link. Must fail.

### OA-13 — Capability revoked mid-flow
Authorized admin initiates, loses capability before completion. Completion must not silently link without revalidation.

### OA-14 — Different admin browser/session completes transaction
Policy decides explicitly whether same initiating actor/browser is required; test must prove chosen behavior and no CSRF/account-link swap.

### OA-15 — Two simultaneous transactions same site/user
Each verifier/state isolated; completion cannot cross-consume transaction.

### OA-16 — Two simultaneous admins
No Account/site binding swap; concurrency/idempotency result deterministic.

### OA-17 — WPE service unavailable before authorization
Local transaction expires safely; Free remains usable.

### OA-18 — Service unavailable after approval but before local redemption
Retry/reconcile same transaction without generating duplicate allocation.

### OA-19 — Local persistence failure after remote success
Use operation/activation identity to reconcile rather than issue fresh logical connection.

### OA-20 — Access-token expiry
Refresh path works without exposing token and without reusing expired access token indefinitely.

### OA-21 — Refresh-token rotation
Successful refresh returns new credential; old token cannot continue as valid current token.

### OA-22 — Refresh-token replay
Use rotated old token from second actor/process. Service detects replay/token-family compromise according to policy.

### OA-23 — Refresh response lost
Client reconciles/handles rotated-token ambiguity without repeatedly using a possibly invalidated prior token blindly.

### OA-24 — Disconnect online
Remote revoke + local secret deletion + account-link state update; Free/config/data remain.

### OA-25 — Disconnect during service outage
Local usable secret removed; remote revoke queued/retry state visible; no secret retained merely to make UI look connected.

### OA-26 — DB-only theft
Database copy without external Vault key cannot reveal refresh credential under accepted Vault profile.

### OA-27 — Browser URL/referrer/history
Access/refresh tokens never appear; one-time artifact removed from browser URL after processing using safe redirect.

### OA-28 — Log/support/diagnostics scan
No verifier, access token, refresh token, authorization code or completion artifact leaked into generic logs/support bundle.

### OA-29 — Site domain/host change
Existing link follows Product License reconciliation; domain equality is not authentication.

### OA-30 — Staging clone
Copied DB cannot silently reuse production OAuth refresh credential/site commercial binding without clone policy/revalidation.

### OA-31 — Reverse proxy callback canonicalization
Trusted proxy/site URL configuration produces exact safe return; Host/X-Forwarded-* spoof cannot redirect completion externally.

### OA-32 — Device Authorization fallback
If enabled: user-code expiry, poll interval, `slow_down`, cancellation, Account/site binding and phishing-resistant UI semantics proven.

## 8. HTTP/browser evidence to capture

For each relevant fixture:
- authorization URL safe parameter list;
- redirect chain hosts;
- callback status/Location;
- token endpoint request field names with secrets redacted;
- issuer/environment identifiers;
- cookies/referrer-policy/browser history checks;
- HTTP error/problem code;
- local transaction state before/after;
- remote site-connection/allocation state before/after;
- Vault secret reference existence only, never plaintext in report.

## 9. Security pass gates

Production profile fails if any occurs:
- static client secret required;
- reusable access/refresh token in browser URL/JS/log;
- completion without matching PKCE transaction;
- replay creates a second valid connection/allocation;
- arbitrary return/open redirect;
- wrong issuer accepted;
- lower-privilege link completion;
- stale capability accepted without chosen revalidation rule;
- refresh-token replay undetectable under public-client profile;
- cloned/stolen DB automatically grants usable production service credential contrary to clone policy.

## 10. Reliability pass gates

- service outage ≠ Product entitlement expiry;
- unknown outcome reconciles;
- retry uses same logical operation identity;
- Free remains fully usable;
- pending transactions expire/clean up;
- simultaneous transactions do not corrupt each other;
- disconnect state is truthful when remote revoke pending.

## 11. Privacy pass gates

- connection disclosure shown before first transmission;
- only documented site/account fields transmitted;
- no hidden telemetry coupled to account linking;
- logs/support redact token/transaction secrets;
- disconnect vs remote deletion distinction preserved.

## 12. Required evidence report

Future execution report must include:
- client/service build/version;
- OAuth authorization server profile/version;
- exact token lifetimes/rotation semantics;
- fixture pass/fail matrix OA-01…OA-32;
- residual risks;
- browser/proxy environments tested;
- Vault profile used;
- privacy field capture;
- unresolved security review findings.

## 13. Current state

**OA fixtures executed: 0/32.**

No OAuth endpoint, transaction, browser redirect, token exchange, refresh, revoke or Device Authorization poll has run.

## 14. Development gate

Execution requires explicit owner consent under ADR-0014. This protocol alone does not authorize a mock server, OAuth client, service endpoint, token generation or network request.