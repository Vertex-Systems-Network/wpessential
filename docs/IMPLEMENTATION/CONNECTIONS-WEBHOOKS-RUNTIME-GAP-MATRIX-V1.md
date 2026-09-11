# Connections/Webhooks — Runtime Gap Matrix V1

Surface: **23 / Connections/Webhooks**  
Planning issue: **#589**  
Supervisor wave: **#583**  
Exact-main claim anchor: `6e0c29aeec9c7553ebb2d22decbc5f9437edecc1`

Status: **PLANNING GAP MATRIX / RUNTIME NOT AUTHORIZED**

Surface 23 remains `UNSEEDED / 0` in the Master Options Bank and exact main has no dedicated Connections/Webhooks runtime. This matrix records prerequisites and owner boundaries only.

## Gap matrix

| Area | Exact-main state | Future requirement | Boundary / gate |
|---|---|---|---|
| Master Options Bank | **BLOCKED — UNSEEDED / 0** | Native/market reviewed normalized records. | Product gate before option contracts/runtime. |
| Atomic Option Contracts | **PLANNING INVENTORY ONLY** | Schema-valid contracts after Bank review. | No lifecycle promotion from inventory alone. |
| Connection Definition runtime | **ABSENT** | Stable definitions, provider profiles, revision/CAS/lifecycle. | Surface 23. |
| Vault secret references | **NO SURFACE CONTRACT** | store refs only; never return secret values. | Shared Vault owner. |
| Safe HTTP / SSRF engine | **ABSENT** | scheme/host/IP/DNS/redirect/TLS enforcement incl IPv4/IPv6. | Security prerequisite before outbound calls. |
| OAuth lifecycle | **ABSENT** | state/PKCE/scopes/refresh/revoke/reauth through certified adapters. | Provider-specific review. |
| Outbound request profiles | **ABSENT** | typed mappings, body/response caps, retries/idempotency/redaction. | Surface 23. |
| Outbound execution | **ABSENT** | safe request run with Policy and async Job where needed. | No arbitrary network action from definitions. |
| Retry/circuit health | **ABSENT** | bounded retry/Retry-After/idempotency/degraded cooldown. | No endless/non-idempotent retries. |
| Delivery logs | **ABSENT** | metadata/redacted attempt evidence. | No auth/secrets. |
| Inbound webhook definitions | **ABSENT** | stable route, auth/signature/schema/rate/event mapping. | Surface 23. |
| Raw-body signature verification | **ABSENT** | verify raw bytes before parse, constant-time approved algorithm. | Must precede business processing. |
| Replay/idempotency | **ABSENT** | bounded provider event identity/retention/duplicate handling. | Duplicate must not rerun mutations. |
| Inbound acknowledgment | **ABSENT** | prompt adapter-defined response + async heavy work. | Business processing decoupled. |
| Receipt logs | **ABSENT** | verification/processing metadata, bounded sensitive raw retention. | Privacy policy. |
| Secret rotation | **ABSENT** | replace/overlap/retire semantics without revealing secret. | Vault/provider contract. |
| Dependency graph | **ABSENT** | callers/usage + disable/delete impact. | Consumers keep domain semantics. |
| Portability | **ABSENT** | non-secret export + credential-required import state. | Generic package orchestration Surface 26. |
| Multisite | **UNSPECIFIED** | site/network connection/secret/route/log scope. | Contract before implementation. |
| Accessibility | **NO SURFACE UI** | keyboard/focus/status/secret-state/test accessibility. | Browser/axe evidence later. |
| Compatibility | **NO SURFACE RUNTIME** | WP/PHP/HTTP/proxy/provider version/degraded behavior. | Exact-head evidence later. |
| Performance/reliability | **NO SURFACE RUNTIME** | no unrelated-request calls, bounded bytes/time/retries/logs/jobs. | Deterministic tests later. |

## Security hard gates

Future implementation must reject or fail closed on:

- loopback/link-local/private/cloud-metadata/reserved destinations by default;
- DNS/redirect bypass of original host policy;
- unsupported schemes and ordinary production HTTP plaintext;
- disabled TLS/hostname verification;
- cross-host forwarding of Authorization/secret headers without certified adapter rules;
- plaintext secrets in definitions, revisions, logs, exports or AI context;
- arbitrary dynamic hosts by default;
- retrying non-idempotent requests without an idempotency contract;
- processing inbound payload before signature/replay verification;
- reserialized JSON for providers that sign raw bytes;
- weak arbitrary signature algorithms;
- replayed event IDs rerunning business mutations;
- indefinite raw webhook payload retention.

## Required Policy / Ability separation

Separate abilities should cover connection metadata read, create/update/enable/disable, safe test, credential replace/clear/rotate, OAuth reconnect, outbound profile execution, webhook definition management, delivery/receipt read/retry and sensitive diagnostics.

Credential operations and private-network widening require elevated capability/re-auth and are not default AI-exposed actions.

## Accessibility evidence required later

- keyboard connection/webhook/profile editors;
- focus after test/OAuth/rotate/save;
- linked URL/SSRF/signature validation errors;
- accessible masked-secret presence state;
- no color-only health/replay/signature indicators;
- responsive delivery/receipt tables;
- axe coverage across connected/auth-expired/blocked-SSRF/signature-failed/degraded states.

## Multisite evidence required later

- site/network definitions and Vault refs;
- OAuth callback/site identity;
- inbound route collisions;
- cross-site consumer access;
- delivery/receipt privacy;
- network admin credential permissions;
- export/import credential rebinding.

No cross-site access or secret reuse is implied by Super Admin status alone.

## Reliability/performance evidence required later

1. SSRF tests cover IPv4/IPv6, redirect and metadata addresses;
2. every redirect target is revalidated;
3. auth headers are stripped across unauthorized host changes;
4. TLS verification cannot be silently disabled;
5. OAuth state/PKCE/refresh/revoke behavior is deterministic;
6. secrets are never returned/logged/exported;
7. retry respects idempotency and Retry-After;
8. inbound signatures verify exact raw body bytes;
9. timestamp/replay and duplicate provider events are blocked from duplicate processing;
10. oversized/deep payloads reject before expensive processing;
11. heavy webhook work is queued after acknowledgment;
12. logs/receipts paginate and cleanup asynchronously;
13. provider/Vault outages degrade safely;
14. no external call occurs on unrelated admin/frontend requests.

## Future implementation order

Only after Bank review + schema-valid option contracts:

1. Connection Definition + Vault-reference contract + Policy;
2. Safe HTTP/SSRF/TLS engine + read-only tests;
3. provider/auth/OAuth adapter registry;
4. outbound request profiles + bounded execution/evidence;
5. inbound webhook definitions + raw-signature/replay receipt pipeline;
6. retry/circuit/health/logging;
7. rotation/dependency/portability/multisite;
8. exact-head security/accessibility/compatibility/performance certification audit.

Private-network connections remain a separately gated high-risk capability, not baseline behavior.

## Exit decision

Exact main has a mature exhaustive Connections/Webhooks specification and clear owner boundaries, but the Master Options Bank remains `UNSEEDED / 0` and no runtime module exists. `runtime_allowed=false` remains correct.

The next valid action is Bank seeding/native/market review, then schema-valid option contracts and UX re-review — not network/OAuth/webhook execution from this planning lane.
