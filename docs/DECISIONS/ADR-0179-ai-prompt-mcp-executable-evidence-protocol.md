# ADR-0179 — AI Prompt / Requirement Compiler / MCP Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-29

## Context

ADR-0178 accepts the WordPress-native AI Prompt, Requirement Compiler and optional MCP architecture. The architecture must not be called runtime-ready merely because prompt templates, Abilities or MCP metadata can be registered on paper.

## Decision

Accept the fixed future evidence protocol:

`docs/QUALITY/AI-PROMPT-MCP-EXECUTABLE-EVIDENCE-PROTOCOL.md`

Evidence namespace:
- **AIP-001…AIP-176**.

Current execution truth:
- documented: **176**;
- executed: **0/176**;
- AIC runtime certifications: **0**;
- MCP runtime certifications: **0**;
- WPE Prompt Runtime provider/model certifications: **0**.

## Certification separation

AI Client/provider certification, structured Requirement IR, module draft generation, typed apply, MCP discovery/auth/session/cache behavior and each underlying module's business runtime are separate evidence domains.

Passing one does not auto-certify another.

## Required proof classes

The protocol covers:
- WordPress 6.9/7.0/7.1 capability detection;
- WordPress AI Client/Connectors integration;
- provider/model capability and failure states;
- structured JSON schema output;
- context Policy/PII/secret redaction;
- Prompt templates/sessions;
- Requirement IR/capability resolver;
- Capability Gap Request flow;
- Plan IR/diff/validation/simulation;
- approval fingerprints and typed Ability apply;
- concurrency/idempotency/partial failure;
- Abilities/MCP discovery and exposure;
- MCP authentication/session/cache isolation;
- prompt injection/tool misuse;
- rate/budget/observability;
- Multisite/tenant isolation;
- provider/model/WordPress/MCP version regression and scale.

## Stop-the-line conditions

Any future evidence run stops certification on:
- cross-user/site data leakage;
- secret exposure;
- Capability/Policy bypass;
- arbitrary code execution path;
- MCP exposure beyond declared allowlist;
- stale/changed plan executed under invalid approval;
- destructive/high-impact auto-approval contrary to policy;
- prompt injection changing privileged instruction/tool authority;
- partial failure reported as complete success;
- unsupported requirement silently omitted while system claims complete build.

## Preserved truth

- AIP documentation is not runtime evidence.
- Static official WordPress/MCP docs are not WPE compatibility certification.
- AI model/provider availability is environment-specific.
- AI Client success does not certify module business behavior.
- MCP Adapter availability does not authorize WPE MCP exposure.
- Module/provider evidence protocols remain independently required.

## Development gate

No AIP fixture has been executed. No AI Client call, provider call, MCP session, Ability execution, remote capability request or runtime test is authorized by this ADR.

ADR-0014 remains the hard consent gate.