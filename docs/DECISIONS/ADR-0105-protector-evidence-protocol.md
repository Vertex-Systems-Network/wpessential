# ADR-0105 — Protector Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Decision

Protector cannot be called production-ready until the future implementation passes `docs/QUALITY/PROTECTOR-EXECUTABLE-EVIDENCE-PROTOCOL.md`.

The protocol fixes evidence for:
- trusted-proxy chain resolution and spoof resistance;
- atomic rate-limit concurrency/window behavior;
- login/password-gate/XML-RPC/REST throttling;
- path/resource normalization and rule precedence;
- redirect loop/open-redirect protection;
- login alias compatibility/recovery;
- application security-header conflict handling;
- recovery mode that never bypasses WordPress authentication;
- Multisite/network floor isolation;
- privacy/log retention;
- explicit limitation that Protector is not a full WAF/DDoS layer.

## Current state

PR-01…PR-44 documented. **0/44 executed.**

## Development gate

No Protector hook, rate-limit store, proxy parsing, login alias, header mutation or request gate is authorized before explicit owner consent under ADR-0014.