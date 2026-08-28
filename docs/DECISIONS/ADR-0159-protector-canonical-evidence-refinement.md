# ADR-0159 — Protector Canonical Executable Evidence Refinement

Status: **Accepted evidence refinement; execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP42`  
Execution mode: `PLANNER_ONLY`

## Decision

Refine the canonical Protector evidence protocol in place from `PR-01…PR-44` to **`PR-01…PR-176`**, preserving the original fixture intent and binding the surface to current shared RLT/CAC/KPA/ERR/VER/MLC, integration and Multisite contracts.

Canonical protocol:
- `docs/QUALITY/PROTECTOR-EXECUTABLE-EVIDENCE-PROTOCOL.md`

## Preserved security boundaries

- Protector remains application-layer hardening, not a complete edge WAF/DDoS product.
- `REMOTE_ADDR`/trusted-proxy policy determines security client identity; untrusted forwarded headers never gain authority.
- Protector allow, gate-password success, IP match, RLT allow, cache state or login alias never grants WordPress capability/resource authorization.
- Shared Rate Limit remains an independent service; passing RLT does not auto-certify Protector and vice versa.
- Recovery can disable WPE overlays only; it cannot authenticate, mint privilege, bypass WordPress auth/nonces or expose Membership/private resources.
- REST/XML-RPC/Webhook endpoint semantics remain owned by their respective engines after Protector outer gating.

## Refinement scope

`PR-01…PR-176` now fixes evidence for:
- canonical scheme/host/path/method/request context;
- trusted proxy/IP/CIDR/IPv6 behavior;
- rule Draft/publish/version/dependency/precedence lifecycle;
- shared RLT identity/atomicity/failure/exemption/network-floor integration;
- gate password/session/login alias/recovery behavior;
- redirect and security-header correctness/conflict handling;
- CAC/page-cache/CDN limitations and invalidation;
- wp-admin/AJAX/REST/XML-RPC/Webhooks/cron/loopback/core flows;
- Multisite site/network ownership, lifecycle, clone/restore/import/versioning;
- privacy, Audit, ERR diagnostics and support-bundle redaction;
- rule-match, regex, proxy-chain, deny-flood, RLT contention and large-network scale.

## Current evidence state

- `PR-01…PR-176` documented.
- **PR executed: 0/176.**
- Protector runtime certifications: **0**.
- Shared RLT: **0/176**; CAC: **0/176**.
- No Protector hook/request gate, proxy parse, rule publication, limiter counter, gate session, login alias, redirect, header, cache/CDN integration, Multisite operation, runtime test or benchmark has executed.

## Development gate

This ADR is planning/evidence documentation only. It grants no development or executable-test authorization. ADR-0014 and `DEVELOPMENT-CONSENT.md` remain authoritative.
