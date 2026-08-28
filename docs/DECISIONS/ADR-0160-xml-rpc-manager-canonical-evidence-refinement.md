# ADR-0160 — XML-RPC Manager Canonical Executable Evidence Refinement

Status: **Accepted evidence refinement; execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP43`  
Execution mode: `PLANNER_ONLY`

## Decision

Refine the canonical XML-RPC Manager executable evidence protocol in place from `XR-01…XR-48` to **`XR-01…XR-176`**, preserving the original fixture semantics while binding the surface to current Protector/RLT/CAC/KPA/ERR/VER/MLC, Safe HTTP and Multisite contracts.

Canonical protocol:
- `docs/QUALITY/XML-RPC-MANAGER-EXECUTABLE-EVIDENCE-PROTOCOL.md`

## Preserved terminology and authority boundaries

- `xmlrpc_enabled=false` is not equivalent to disabling the XML-RPC endpoint.
- Host/WAF/Protector endpoint reachability, parser behavior, method registry, WPE method policy and native WordPress authentication/object authorization remain separate layers.
- WPE method allow never grants native authentication/capability/object permission.
- Protector/RLT evidence never auto-certifies XML-RPC behavior.
- Pingback/outbound safety uses certified Safe HTTP semantics where WPE owns outbound processing; WPE does not claim host/edge controls it did not execute.

## Refinement scope

`XR-01…XR-176` now fixes evidence for:
- endpoint/HTTP/request-surface canonicalization and first-denial-layer truth;
- effective core/plugin/custom method inventory and late-registration drift;
- allowlist/denylist/Complete Deny policy lifecycle/versioning/import/export;
- username/password/Application Password/native capability/object authorization;
- shared RLT, endpoint/method/multicall abuse controls and parser/resource bounds;
- pingback SSRF/redirect/TLS/size/timeout/privacy behavior;
- Jetpack/mobile/custom-plugin versioned integration compatibility;
- Protector/host/core/plugin version drift;
- Multisite network floors/site overrides/lifecycle/restore/cache isolation;
- ERR/Audit/PDL redaction/recovery/support diagnostics;
- inventory, request, multicall, malformed-input and large-network scale evidence.

## Current evidence state

- `XR-01…XR-176` documented.
- **XR executed: 0/176.**
- XML-RPC runtime/integration certifications: **0**.
- Protector PR: **0/176**; RLT: **0/176**; CAC: **0/176**.
- No XML-RPC request/filter/endpoint block, parser test, limiter mutation, pingback/outbound call, integration workflow, Multisite operation, cache mutation or benchmark has executed.

## Development gate

This ADR is planning/evidence documentation only and grants no implementation or executable-test authorization. ADR-0014 and `DEVELOPMENT-CONSENT.md` remain authoritative.
