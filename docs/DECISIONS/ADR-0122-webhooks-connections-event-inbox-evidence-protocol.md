# ADR-0122 — Webhooks, Connections & Event Inbox Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP06`

## Context

Webhooks & Connections already has accepted Phase 0 architecture:
- centralized Safe HTTP owns outbound SSRF/redirect/TLS/size/timeout controls;
- Connection credentials are Vault-backed and adapters cannot bypass Vault, Policy, error taxonomy, Audit/redaction or JobService where durable async semantics are required;
- provider support is certified per adapter + provider + capability + API/profile version through I0–I5;
- a successful `Test Connection` or OAuth result does not prove read/write/event/production capability;
- inbound webhooks resolve a trusted endpoint/provider profile, verify authenticity before business dispatch, normalize accepted facts into durable Event Inbox and tolerate duplicate/out-of-order delivery;
- trusted endpoint/Connection mapping determines site/network scope; attacker-controlled payload fields cannot choose scope;
- Event Inbox dedupe is necessary but not sufficient; owning domains preserve their own durable idempotency/reconciliation;
- Event Inbox is operational ingress/source-fact truth, not owning Membership/Email/Workflow/business state;
- outbound mutations distinguish known success/failure from unknown outcome and cannot blindly retry an ambiguous non-idempotent effect;
- EI1/PT-D is the first future Event Inbox benchmark baseline and EI2/PT-E is mandatory before final topology selection.

The remaining gap was a single bounded executable protocol covering Connection lifecycle, auth/OAuth, I0–I5 certification, Safe HTTP, webhook signatures/replay, Event Inbox durability, consumer reconciliation, outbound retry/unknown outcomes, privacy and Multisite/lifecycle/scale.

## Decision

Webhooks/Connections/Event Inbox production-readiness claims require the applicable fixtures in:

`docs/QUALITY/WEBHOOKS-CONNECTIONS-EVENT-INBOX-EXECUTABLE-EVIDENCE-PROTOCOL.md`

The protocol fixes **WC-01…WC-156** evidence covering:
- Connection Definition/publish/revision/dependency behavior;
- site/network ownership and trusted scope derivation;
- Vault credential lifecycle and OAuth state/PKCE/issuer/refresh/revoke races;
- granular I0–I5 provider capability certification;
- centralized Safe HTTP SSRF, DNS rebinding, redirect, credential-forwarding, TLS, proxy, request/response and decompression bounds;
- inbound route identity, exact raw-body verification, HMAC/signature/key rotation, timestamp/skew/nonce/event-ID replay and unsigned-provider alternative profiles;
- Event Inbox accepted-fact durability, typed normalization, dedupe, conflicting payload, out-of-order/schema drift, claim/crash/replay/reconciliation behavior;
- consumer-specific idempotency across Workflow, Membership, Email, Notification and other owning domains;
- outbound typed payload/signing/idempotency/retry/Retry-After/unknown-outcome/dead-letter behavior;
- pagination, rate limits, Protected Asset transfer, observability, privacy and raw-payload retention;
- site archive/delete/clone/restore behavior;
- EI1/PT-D vs EI2/PT-E physical/Multisite/scale comparison;
- explicit MUST-NOT/stop-the-line gates.

## Negative requirements locked

A certified integration runtime MUST NOT:
- store or expose plaintext credentials/tokens/webhook secrets through generic Definitions, Jobs, Event Inbox, logs, exports or ordinary UI reads;
- let provider adapters bypass Safe HTTP/Vault/Policy boundaries;
- permit generic external requests to loopback/private/link-local/cloud-metadata targets or bypass those controls by DNS/redirect/URL parsing tricks;
- mutate business state before required inbound authenticity verification;
- trust provider payload fields to choose WPE site/network scope before trusted endpoint/Connection mapping;
- turn invalid/replayed requests into normal processable Event Inbox facts;
- repeat protected business transitions merely because provider/Job delivery is duplicated;
- make Event Inbox operational rows the owning business-domain truth;
- blindly apply provider arrival order as causal domain order;
- map unknown event/schema data to arbitrary callbacks/functions/PHP/actions;
- treat an outbound timeout as definite failure where remote side effect may have committed;
- blindly retry ambiguous non-idempotent mutations;
- claim unsupported capabilities merely because a Connection is authenticated/Connected;
- cross site/network boundaries through provider IDs, event IDs, Connection IDs, caches, Jobs or routing;
- blindly reactivate cloned/restored credentials, subscriptions, Event Inbox work or pending remote effects.

## Certification truth

This ADR preserves granular provider certification:
- **I0 — Detected / Configurable**
- **I1 — Authentication Certified**
- **I2 — Read Certified**
- **I3 — Write / Action Certified**
- **I4 — Event / Reconciliation Certified**
- **I5 — Production Profile Certified**

Certification is always scoped to exact adapter/provider/capability/API-profile/environment evidence. One capability may be certified while another remains unsupported or experimental.

## Event Inbox physical topology

This ADR does **not** finalize EI1 vs EI2.

- `EI1/PT-D` remains first future benchmark baseline.
- `EI2/PT-E` remains mandatory comparison.
- Final selection requires executed authenticity, scope isolation, dedupe/idempotency, noisy-neighbor, migration, Backup/lifecycle, retention and scale evidence.

## Current state

WC fixtures documented: **156**.  
WC executed: **0/156**.  
Connection provider I4/I5 certifications: **0**.  
Event Inbox runtime certifications: **0**.  
Safe HTTP runtime certification: **none**.  
Final Event Inbox physical topology: **OPEN / evidence-gated**.

No credential exchange, OAuth flow, network request, webhook request/subscription, provider/API call, Event Inbox table/row, Job, Workflow dispatch, outbound webhook, migration, benchmark or runtime test was executed.

## Development gate

This is planning-only acceptance. Execution remains blocked until explicit scoped owner consent under ADR-0014 / `DEVELOPMENT-CONSENT.md` / `docs/APPROVAL-LEDGER.md` and applicable technical prerequisites.