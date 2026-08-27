# ADR-0034 — WPE Account-Link OAuth Profile

Status: **Accepted security profile / service implementation pending**  
Date: 2026-08-27

## Decision

WPEssential account linking uses this preferred architecture:

**WordPress initiates transaction + PKCE S256 → WPE Authorization Code flow returns to fixed WPE-owned callback → WPE issues a short-lived one-time site-completion artifact bound to the original installation/transaction/return target → browser returns to the exact original site → WordPress redeems completion artifact server-to-server with local transaction + PKCE proof → site connection credentials/activation are established.**

The distributed WordPress plugin is a **public OAuth client** and does not ship a reusable confidential client secret.

Device Authorization remains a fallback/troubleshooting profile when browser callback/return behavior cannot operate reliably.

## Required security properties

- PKCE with S256;
- transaction-specific state/binding;
- exact trusted WPE issuer/environment;
- fixed OAuth redirect at WPE service;
- site return URL fixed/bound when transaction is initiated, not accepted from final callback input;
- one-time short-lived completion artifact;
- no access/refresh token in browser return URL;
- no reusable account credential/password handled by local WP plugin;
- initiating WordPress principal/capability bound to transaction;
- replay, wrong issuer/environment and concurrent transaction handling;
- safe local post-link redirect after consuming artifact;
- refresh credential stored only through Secrets Vault.

## Why this over direct dynamic OAuth redirect registration

A fixed WPE-owned OAuth callback avoids making arbitrary customer WordPress URLs first-class OAuth redirect registrations while still allowing each site to complete a cryptographically/transaction-bound return.

The custom site-return step remains security-sensitive, but its one-time artifact can be designed to carry no reusable bearer credential and can be strictly bound to the originating installation/transaction.

## Why not a static client secret

Anything distributed in WordPress plugin source must be assumed recoverable by attackers and cannot provide confidential-client secrecy across installations.

## Device Authorization fallback

Device Authorization may be offered when:
- WordPress callback/return is blocked;
- reverse proxy/security middleware breaks browser return;
- site is not directly browser-reachable in an expected way.

It remains secondary because browser-capable wp-admin can normally offer a smoother Authorization Code UX.

## Consequences

- WPE service must own an exact trusted callback and a short-lived transaction store;
- WordPress stores pending state/PKCE verifier privately and destroys it on completion/expiry;
- site URL/domain migration may require relink/activation-transfer semantics;
- no arbitrary `return_to` redirects;
- WPE Free remains functional if linking fails/service is offline;
- exact token lifetimes, service endpoint schemas, rotation/revocation and callback implementation still require executable integration evidence.

## Standards basis

OAuth 2.0 Security Best Current Practice (RFC 9700) requires public clients using Authorization Code to use PKCE and identifies S256 as the method that does not expose the verifier in the authorization request. It also requires protections against CSRF/code injection/mix-up/open redirect classes.

## Evidence still required

After explicit owner development consent:
- end-to-end service + local prototype;
- code/completion-artifact replay;
- code theft without PKCE verifier;
- arbitrary return URL/open redirect;
- wrong issuer/environment;
- simultaneous link transactions;
- lower-privilege callback completion;
- token rotation/revocation;
- reverse proxy/site URL migration cases;
- service outage/disconnect;
- Device Authorization fallback behavior.

Supporting threat model: `docs/SECURITY/OAUTH-ACCOUNT-LINK-THREAT-MODEL-ALTERNATIVES.md`.