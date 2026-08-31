# ADR-0045 — Protector Request Gate, Trusted Proxy & Rate-Limit Architecture

Status: **Accepted security architecture; hook/storage evidence pending**  
Date: 2026-08-27

## Decision

WPEssential Protector uses:

- explicit request-surface classification rather than one global block switch;
- `REMOTE_ADDR` as client-network authority unless the immediate peer matches a configured trusted proxy profile;
- forwarding headers only through trusted proxy configuration;
- a shared Rate Limit service with an **atomic** state adapter;
- hashed site/path gate passwords and server-verifiable gate sessions;
- WordPress authentication/capabilities as the real application authorization layer;
- a server-side WPE recovery mode that disables WPE overlays without authenticating/granting privileges;
- separate delegation to REST/XML-RPC semantic engines.

Ordinary transients are not assumed to be authoritative atomic security counters.

## Why

This avoids spoofable IP trust, race-prone brute-force counters, accidental authorization-by-menu/path hiding, and recovery backdoors that would weaken WordPress security.

## Security boundaries

- Protector allow ≠ WordPress capability;
- login alias ≠ brute-force security;
- password gate ≠ user login;
- recovery mode ≠ authentication bypass;
- security-header configuration must handle conflicts rather than blindly duplicate policies.

## Remaining evidence

Hook ordering, atomic rate-limit adapters, proxy-chain fixtures, gate-session implementation, multisite, login/reset/logout compatibility, header conflicts and recovery behavior require executable tests after owner consent.

See `docs/SECURITY/PROTECTOR-REQUEST-GATE-RATE-LIMIT-RUNTIME.md`.

Development remains prohibited until explicit owner consent under ADR-0014.