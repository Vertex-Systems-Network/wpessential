# Protector — Bank Entry Readiness V1

Surface: **27 / Protector**  
Planning issue: **#593**  
Supervisor wave: **#583**  
Exact-main claim anchor: `588079965734c56b2bda783778c9efa4dbf63530`

Status: **BANK ENTRY READY FOR RESEARCH/SEEDING — NOT BANK_REVIEWED — NO OPTION CONTRACT PROMOTION**

This document is planning evidence only. It does not seed/review the Master Options Bank, create schema-valid Atomic Option Contracts, block requests, change login/admin routes, emit security headers, mutate rate-limit state or authorize runtime implementation.

## Current machine truth

- Surface 27 `protector` is `UNSEEDED` with **0 normalized Master Options Bank records**.
- The atomic planning ledger marks Surface 27 `ATOMIC_INVENTORY_COMPLETE`; this is planning inventory only.
- Protector owns request/access hardening. Roles/Policy remain authorization owners, Redirect owns generic route redirection, XML-RPC Manager owns XML-RPC semantics and REST endpoints retain their own permission callbacks.
- Exact-main `frameworks/Modules` has no dedicated Protector runtime module.

The next valid gate is Bank seeding + native/market review, not request enforcement.

## Existing in-repo evidence

Primary sources:

- `docs/MODULES/PROTECTOR-EXHAUSTIVE-SPEC.md`;
- `docs/MODULES/OPTION-INVENTORY.md`;
- `docs/PRODUCT/56-SURFACE-COMPETITOR-PARITY-MATRIX.md`;
- `docs/MODULES/CANONICAL-OPTION-OWNERSHIP-INDEX-56-SURFACES.md`.

The exhaustive spec establishes critical principles: UI hiding is not authorization, login URL obfuscation is not authentication, IP rules are supplemental, Protector cannot grant access that canonical Policy/REST denies, and the module is not a full WAF or malware scanner.

## Candidate Bank families

1. Site Gate modes/scope/exclusions/recovery paths.
2. Shared-password gate/session/cookie/failure controls and one-way secret storage.
3. Protector Rule identity/scope/subject/conditions/effect/response/schedule.
4. Rule precedence/specificity/deny semantics and simulation/explainability.
5. wp-admin route/screen/action restrictions with AJAX/admin-post compatibility.
6. Login alias/rewrite compatibility and recovery requirements.
7. Login failure/rate/challenge/block policy.
8. Generic rate-limit policy targets/keys/windows/bursts/bypasses/storage health.
9. Client-IP/trusted-proxy parsing and spoof resistance.
10. Named IPv4/IPv6/CIDR groups and imports.
11. Structured security headers/CSP/HSTS presets with conflict detection.
12. REST/API outer deny/rate rules without granting missing endpoint permission.
13. XML-RPC delegated state/reference only.
14. Recovery architecture, lockout preflight and server-side safe-disable paths.
15. Privacy-aware security logs/retention/IP handling/redaction.
16. Permissions/Abilities with recovery/bypass/login/header separation.
17. Multisite/network-admin/site-gate/login behavior.
18. Performance rule compilation/cache/logging requirements.

## Native WordPress audit required before Bank review

Audit current WordPress request/login/admin/security primitives:

- authentication/login/lost-password/reset/logout URLs and hooks;
- current-user capabilities and network/Super Admin context;
- admin-ajax/admin-post and REST/Gutenberg dependencies;
- request/redirect helpers and canonical URL behavior;
- nonce/session/application-password semantics;
- HTTP/server/CDN header ownership;
- client IP/server variables and reverse-proxy deployment assumptions;
- multisite/network login/admin behavior;
- native login error/enumeration characteristics.

Native hooks/headers are primitives, not a complete Protector rule engine.

## Market audit required before Bank review

Compare current access-control/private-site/login-hardening/rate-limit/security-header products for:

- whole-site/private/maintenance gates;
- path/resource/role/capability restrictions;
- login alias and brute-force protection;
- trusted proxy/IP/CIDR handling;
- REST/admin compatibility;
- rate limiting;
- CSP/HSTS/security headers;
- recovery/anti-lockout;
- logs/diagnostics/multisite.

Reject patterns that treat obscurity as auth, trust arbitrary forwarded headers, permanently ban IPs by default, disable TLS/REST globally without impact analysis, or provide secret permanent query-string bypasses.

## Canonical ownership decisions

| Concern | Canonical owner |
|---|---|
| Site Gate, outer request deny/challenge/rate, login/admin hardening, recovery | **Surface 27 Protector** |
| Resource authorization / roles / capabilities | shared **Policy + Surface 30 Roles** |
| Generic redirects | **Surface 44 Redirect/Routing** |
| REST endpoint permission | each endpoint / **Surface 22 REST API** |
| XML-RPC method semantics | **Surface 29 XML-RPC** |
| Malware/security scanning | **Surface 52 Security Scanner** |
| Membership/entitlement truth | **Surface 15 Membership** |

## Rejected-unsafe / bounded candidates

Reject or tightly bound:

- UI/menu hiding as authorization;
- login alias as authentication;
- “Allow” rules that bypass core/WPE Policy;
- trusting `X-Forwarded-For`/Forwarded from untrusted proxies;
- permanent automatic IP bans by default;
- arbitrary PHP conditions/regex without accepted safe engine;
- blanket `/wp-admin`, REST, admin-ajax or admin-post blocking without impact-aware semantics;
- broad unauthenticated recovery bypass or permanent secret query string;
- auto-enabling HSTS preload or strict CSP;
- logging passwords/cookies/auth headers/query secrets/full bodies;
- remote service dependency inside request authorization path.

## Readiness decision

Surface 27 has sufficient semantics to begin disciplined Master Options Bank seeding/native/market review, but exact-main `UNSEEDED / 0` blocks option-contract, UX and runtime promotion.

Next gate: normalized Bank seed → native audit → market audit → ownership/unsafe/deferred resolution → Bank review with zero unresolved items → schema-valid Atomic Option Contracts → UX re-review → separately authorized enforcement slices.
