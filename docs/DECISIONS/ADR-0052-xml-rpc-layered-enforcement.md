# ADR-0052 — XML-RPC Layered Enforcement & Compatibility Model

Status: **Accepted security/compatibility architecture; runtime certification pending**  
Date: 2026-08-27

## Decision

WPEssential XML-RPC Manager treats XML-RPC as layered exposure rather than one boolean:

1. host/CDN/WAF endpoint reachability;
2. Protector outer request gating/rate limiting;
3. effective `xmlrpc_methods` registry policy;
4. authenticated-method state represented by `xmlrpc_enabled` semantics;
5. WordPress/plugin method-native authentication and capability checks.

`xmlrpc_enabled=false` is never labeled complete XML-RPC disable because WordPress documents that it controls methods requiring authentication and does not itself disable pingbacks or custom unauthenticated methods.

## Complete Deny

WPE Complete Deny means:
- deny/remove every effective registered XML-RPC method, including pingback/custom methods controllable through the registry;
- optionally pair with Protector endpoint-level denial;
- dynamically deny newly registered methods under the published rule;
- verify effective method behavior before claiming runtime certification;
- preview integration impact before apply.

## Compatibility

Profiles are certified adapters, not guessed static method lists.

Jetpack is a first-class compatibility warning because Jetpack currently documents XML-RPC as required for its WordPress.com connection. WordPress mobile/legacy publishing support is similarly certification-driven.

## Parser/abuse

- XML-RPC uses ADR-0045 shared atomic Rate Limit service;
- `xmlrpc_element_limit` may be exposed after version behavior is certified;
- no invented parser/body-size WordPress hook;
- logging is metadata-minimized and never stores passwords/raw request bodies by default.

## Why

The layered model avoids false security claims, preserves legitimate integrations, correctly handles plugin-added methods, and separates endpoint firewall policy from WordPress method authorization.

## Remaining evidence

Core/plugin method inventories, filter priority, Complete Deny, pingbacks, parser limits, Jetpack/mobile compatibility, rate limiting, host/WAF detection and multisite require executable certification after explicit owner consent.

See `docs/SECURITY/XML-RPC-LAYERED-ENFORCEMENT-COMPATIBILITY.md`.

No XML-RPC enforcement code has been implemented.