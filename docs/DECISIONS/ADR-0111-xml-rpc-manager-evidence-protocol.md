# ADR-0111 — XML-RPC Manager Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Decision

XML-RPC Manager cannot claim endpoint disablement, Complete Deny, parser protection, compatibility or observability support until a future implementation passes `docs/QUALITY/XML-RPC-MANAGER-EXECUTABLE-EVIDENCE-PROTOCOL.md` for the recorded WordPress/plugin/host profile.

The protocol preserves layered semantics:
- host/CDN/WAF endpoint reachability is distinct from WPE policy;
- Protector outer request gate is distinct from method registry policy;
- `xmlrpc_enabled=false` means authenticated-method behavior, not universal endpoint disablement;
- effective method registry includes core and plugin-added methods;
- native WordPress method authentication/capability remains authoritative;
- Complete Deny must cover every discovered callable method in the certified profile;
- pingback policy remains independently testable;
- trusted-proxy-aware shared rate limiting is required for rate claims;
- `xmlrpc_element_limit` evidence is separated from host/PHP/request-size controls;
- Jetpack/remote publishing compatibility is version/profile certified, never guessed from static method lists;
- logging is redacted/minimized;
- Multisite network policy floors are explicit;
- method inventory drift invalidates stale support assumptions.

## Current state

XR-01…XR-48 documented. **0/48 executed.**

## Development gate

No XML-RPC request/filter, endpoint block, parser test, rate-limit mutation, integration call or compatibility execution is authorized before explicit owner consent under ADR-0014.