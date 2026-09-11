# Protector — Provisional UX Contract V1

Surface: **27 / Protector**  
Planning issue: **#593**  
Supervisor wave: **#583**  
Exact-main claim anchor: `588079965734c56b2bda783778c9efa4dbf63530`

Lifecycle status: **PROVISIONAL PLANNING ONLY**. Surface 27 remains `UNSEEDED / 0` in the Master Options Bank. This document does not promote `UX_CONTRACT_COMPLETE` or authorize request enforcement.

## Product rule

Protector is an access-control/hardening orchestrator, not a full WAF or malware scanner. UI hiding is not authorization, login obfuscation is not authentication, IP rules are supplemental, and a Protector Allow can never override an underlying WordPress/WPE denial.

## Information architecture

1. Overview
2. Rules
3. Site Gate
4. Login / Admin Protection
5. Rate Limits
6. Security Headers
7. Logs / Blocks
8. Recovery
9. Diagnostics

## Progressive disclosure

### Essential

- Rule name/status/priority;
- scope/resource;
- subject policy;
- effect (deny, require auth, Site Gate, conceal, local redirect, challenge/rate ref);
- schedule;
- safe response;
- Validate / Simulate / Save.

Site Gate offers Off, shared password, authentication, role/capability, Membership/entitlement and combined policy modes with recovery-safe exclusions shown explicitly.

### Advanced

- exact/prefix/bounded-pattern scopes;
- selected admin/login/REST/resource targets;
- safe condition facts;
- named IP/CIDR groups;
- trusted proxy mode;
- rate profiles;
- login alias compatibility;
- security-header presets;
- logging/retention;
- dependency/usage summary.

### Expert

- registered resource/challenge providers;
- high-risk login alias change flow;
- structured CSP/HSTS advanced controls;
- trusted proxy hop strategy;
- high-impact Site Gate/admin policies;
- recovery diagnostics/bypass management;
- compatibility/degraded-state detail.

No Expert control accepts arbitrary PHP, unrestricted raw headers, untrusted proxy forwarding, permanent recovery query secrets or blanket core-route shutdown shortcuts.

### System / Diagnostics

Read-only diagnostics show:

- compiled rule version/health;
- request/resource normalization;
- matched rule chain/winner;
- outer WordPress/module Policy result where available;
- trusted proxy/client-IP resolution;
- rate-limit backend health;
- login/recovery compatibility;
- header conflicts/upstream ownership;
- XML-RPC delegated state;
- REST/admin dependency impact;
- current site/network scope.

## Rule simulation

Before publish, simulation explains normalized target, subject facts, matching rules, precedence, final Protector decision and underlying authorization result where available.

Simulation must expose lockout/redirect-loop/dependency risks without weakening real runtime checks.

## Recovery / anti-lockout UX

High-risk publish is blocked if it would remove all known recovery paths, block the current operator's route without an approved recovery path, break login/lost-password/reset flows, or create redirect loops.

Recovery architecture is configured before high-risk enforcement and uses dedicated capabilities/re-auth. No permanent secret query-string bypass.

## Login/Admin UX

Login alias is labelled noise reduction, not security by itself. UI tests current login, reset, logout, multisite and account flows before activation where possible.

Admin restrictions distinguish presentation from authorization and warn about admin-ajax/admin-post/REST/Gutenberg dependencies.

## Trusted proxy/IP UX

Default client IP is direct remote address. Forwarded headers are used only when the request came through explicitly trusted proxy CIDRs. Diagnostics show the resolved chain and ignore spoofed untrusted headers.

Named network groups validate IPv4/IPv6/CIDR overlap/duplicates and usage.

## Rate-limit UX

Profiles show target, key strategy, request/window/burst/block semantics, trusted-network bypass and backend health. UI does not claim strict distributed guarantees unless the backend actually provides them.

## Security headers UX

Structured presets expose `nosniff`, Referrer-Policy, Permissions-Policy, frame protection, HSTS and CSP with compatibility/conflict warnings.

HSTS preload and strict CSP are never auto-enabled. Upstream server/CDN ownership is detected/reported where possible.

## REST/XML-RPC boundary

REST controls are outer deny/rate/logging only; endpoint permission callbacks remain authoritative. XML-RPC configuration deep-links/delegates to Surface 29 rather than duplicating truth.

## Logs/privacy

Logs store safe request/resource/rule/result/principal/correlation metadata according retention policy. Passwords, cookies, Authorization headers, query secrets and full request bodies are never logged.

IP retention/anonymization is explicit and privacy-aware.

## Accessibility

- keyboard-operable Rule/Site Gate/rate/header/recovery flows;
- focus after simulation/validation/save failures;
- no color-only allow/deny/rate/recovery health;
- accessible rule-matching explanation;
- clear high-risk warnings/confirmations;
- responsive logs/rules tables;
- axe coverage for lockout-risk, proxy-invalid, degraded-rate and header-conflict states once implemented.

## Multisite

Future normalized contract must define site/network rule scope, network admin/login behavior, Super Admin/recovery semantics, shared vs site-specific proxy/network groups, REST/admin restrictions and logs. Unsupported network behavior is blocked rather than inferred.

## Lifecycle decision

This is provisional interaction guidance only. Exact-main Master Options Bank remains `UNSEEDED / 0`; Bank review and schema-valid Atomic Option Contracts must precede UX lifecycle/runtime promotion.
