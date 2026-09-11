# Connections/Webhooks — Bank Entry Readiness V1

Surface: **23 / Connections/Webhooks**  
Planning issue: **#589**  
Supervisor wave: **#583**  
Exact-main claim anchor: `6e0c29aeec9c7553ebb2d22decbc5f9437edecc1`

Status: **BANK ENTRY READY FOR RESEARCH/SEEDING — NOT BANK_REVIEWED — NO OPTION CONTRACT PROMOTION**

This document is planning evidence only. It does not seed/review the Master Options Bank, store or rotate credentials, perform OAuth, make outbound requests, register inbound webhook routes or authorize runtime implementation.

## Current machine truth

- Surface 23 `connections` is `UNSEEDED` with **0 normalized Master Options Bank records**.
- The atomic planning ledger marks Surface 23 `ATOMIC_INVENTORY_COMPLETE`; this is planning inventory only.
- The canonical ownership map assigns reusable connection/transport lifecycle to Surface 23 while Workflow, Notifications, Backup, REST, Sync and other consumers retain their domain semantics.
- Exact-main `frameworks/Modules` has no dedicated Connections/Webhooks runtime module.

The next valid gate is Bank seeding + native/market review, not HTTP/OAuth/webhook execution.

## Existing in-repo evidence

Primary sources:

- `docs/MODULES/WEBHOOKS-CONNECTIONS-EXHAUSTIVE-SPEC.md`;
- `docs/MODULES/OPTION-INVENTORY.md`;
- `docs/PRODUCT/56-SURFACE-COMPETITOR-PARITY-MATRIX.md`;
- `docs/MODULES/CANONICAL-OPTION-OWNERSHIP-INDEX-56-SURFACES.md`.

The exhaustive spec already establishes the core split: Connection Definition, Vault Secret, Outbound Request Profile, Inbound Webhook Endpoint, and Delivery/Receipt are separate concepts. Consumers must not each invent credentials, OAuth, retries or HTTP security.

## Candidate Bank families

1. Connection identity/provider/environment/lifecycle.
2. Generic HTTP base URL/path/method/header/query/content/timeouts/response caps.
3. Auth profile: none/API key/Bearer/Basic/OAuth/HMAC/provider adapter with Vault refs only.
4. Secret lifecycle: add/replace/clear/rotate/presence/last-changed/Vault health.
5. OAuth provider/scopes/state/PKCE/refresh/revoke/reauth lifecycle.
6. Safe connection tests and health states.
7. SSRF policy: schemes, loopback/private/link-local/metadata/reserved ranges, DNS/IPv6/redirect validation.
8. Private-network exception policy as high-risk explicit allowlist only.
9. Redirect/TLS policy and auth-header forwarding restrictions.
10. Outbound request profiles: path/method/mappings/schema/statuses/timeout/retry/idempotency/redaction.
11. Request body/response parsing/size/schema/storage bounds.
12. Retry/backoff/Retry-After/idempotency and circuit/degraded states.
13. Outbound delivery evidence/log retention/redaction.
14. Inbound webhook identity/path/lifecycle/provider.
15. Signature/auth policy, raw-body verification ordering, timestamp/replay windows.
16. Replay/idempotency receipt identity and duplicate handling.
17. Inbound schema/event mapping/acknowledgment/async handoff.
18. Rate/body/depth/source controls.
19. Receipt states/logs/raw-body retention/privacy.
20. Credential/signing-secret rotation and overlap semantics.
21. Dependency/usage graph, disable/delete impact.
22. Secret-free portability/import degraded credential-required state.
23. Permissions/Abilities, multisite scope, diagnostics and performance.

## Native WordPress audit required before Bank review

Audit current WordPress HTTP/OAuth-adjacent capabilities explicitly:

- WP HTTP API request/redirect/TLS behavior;
- `wp_safe_remote_*` limitations and DNS/redirect implications;
- nonce/admin callback patterns for OAuth state handling;
- cron/job primitives for retries/health checks only where canonical Job service allows;
- site/network option and credential-reference storage constraints;
- REST/AJAX route primitives for inbound webhook endpoints;
- proxy constants/hosting environment behavior;
- multisite/site/network scope and secret isolation.

Native WordPress HTTP helpers are primitives, not a complete SSRF/OAuth/secret-management contract.

## Market audit required before Bank review

Compare current integration/webhook/automation products and provider SDK patterns for:

- reusable provider connections;
- OAuth lifecycle/scopes;
- credential masking/rotation;
- SSRF/redirect/TLS controls;
- outbound request mapping/retries/idempotency;
- inbound signature/replay verification;
- delivery/receipt logs and redaction;
- dependency/health diagnostics;
- import/export without secrets;
- provider/version compatibility.

Do not copy weak patterns such as plaintext secrets, arbitrary HTTP destinations, disabled TLS verification, broad raw payload logging or unsigned inbound hooks.

## Canonical ownership decisions

| Concern | Canonical owner |
|---|---|
| Connection/provider/auth profile, Safe HTTP, OAuth lifecycle, webhook transport, delivery/receipt evidence | **Surface 23 Connections/Webhooks** |
| Secret values | shared **Vault**; Surface 23 stores references only |
| Business orchestration | **Surface 17 Forms & Workflows** |
| Notification routing | **Surface 19 Notifications** |
| Backup artifact semantics | **Surface 24 Backup** |
| REST endpoint public contract | **Surface 22 REST API** |
| Recurring sync cursor/conflict | **Surface 41 Sync** |
| Generic data/package movement | **Surface 26 Import/Export** |

## Rejected-unsafe / bounded candidates

Reject or tightly bound:

- plaintext secrets in definitions/revisions/logs/exports/AI context;
- HTTP plaintext in ordinary production configuration;
- “ignore SSL errors” toggles;
- loopback/private/link-local/cloud metadata/reserved destinations by default;
- arbitrary redirect chains or cross-host auth-header forwarding;
- dynamic hosts by default;
- visible static Authorization secrets;
- unbounded response/body/log storage;
- retrying non-idempotent writes without idempotency support;
- unsigned/unverified webhook business processing where provider supports signatures;
- weak user-selected signature algorithms;
- JSON reserialization before raw-body signature verification;
- provider-event replay rerunning mutations;
- raw webhook body retention indefinitely by default.

## Readiness decision

Surface 23 has enough semantic material to begin disciplined Bank seeding/native/market review, but exact-main `UNSEEDED / 0` blocks option-contract, UX and runtime promotion.

Next gate: seed normalized Bank records, complete native + market audit, resolve duplicates/ownership/unsafe/deferred items, promote Bank with zero unresolved items, derive schema-valid Atomic Option Contracts, then re-review UX before any transport runtime work.
