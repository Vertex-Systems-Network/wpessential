# ADR-0101 — OAuth Account-Link Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Decision

Before WPEssential Account Linking can be called production-ready, the future implementation must pass the fixed OAuth evidence protocol in `docs/QUALITY/OAUTH-ACCOUNT-LINK-EXECUTABLE-EVIDENCE-PROTOCOL.md`.

The first profile under test remains:

`fixed WPE callback + one-time site-bound completion artifact + Authorization Code + PKCE S256 + short-lived access token + rotated refresh credential + signed entitlement retrieval`.

Accepted evidence requirements include:
- no reusable confidential client secret in distributed plugin;
- transaction-specific PKCE S256;
- state/transaction/site/issuer binding;
- replay-safe one-time completion artifact;
- no access/refresh tokens in browser URL/JS/logs;
- public-client refresh-token replay detection via rotation or a separately accepted sender-constrained profile;
- local capability revalidation during sensitive completion;
- open-redirect and wrong-issuer/mix-up resistance;
- clone/domain-change reconciliation;
- disconnect/outage truthfulness;
- Vault-backed refresh credential storage;
- explicit privacy/transmission evidence.

## Why

A browser login that succeeds does not prove secure Account Linking. The distributed WordPress client cannot keep a static confidential secret, and Account Linking affects Product License/site allocation state. Replay, mix-up, token theft, clone and unknown-outcome cases must be proven deliberately.

## Current state

OA-01…OA-32 documented. **0/32 executed.**

## Development gate

This ADR accepts the future evidence contract only. No OAuth client/server, redirect, token, refresh, revoke or device flow is authorized before explicit owner consent under ADR-0014.