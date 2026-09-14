# WPEssential — P-006 Planning Lane D: Entitlement, Membership and Multisite Separation Evidence Map

Status: **PLANNING / EVIDENCE-DESIGN ONLY — EXECUTION NOT AUTHORIZED**  
Parent gate: **Issue #924**  
Worker lane: **Issue #928**  
Coordination: **Issue #930 / merged PR #931**  
Inputs: **Lane A / PR #932**, **Lane B / PR #933**, **Lane C / PR #934**  
Planning anchor: **current `main` after PR #934**  
Fixture scope: **FP-95…FP-128**  
P-006 truth: **144 documented / 0 executed / 0 passed / 0 failed / 0 certified pairs / 0 runtime certifications**

## 1. Purpose and hard boundary

This document plans evidence for the separation between:

- local Free↔Pro binary/API/schema compatibility;
- signed Product Entitlement;
- remote Product License/account/allocation state;
- Membership/user authorization;
- expiry, grace, stale and unavailable verification states;
- Multisite network/site activation and commercial allocation;
- clone/staging/domain-change/restore identity reconciliation.

It does **not**:

- call a live or sandbox license/provider service;
- generate/sign/verify a real entitlement artifact;
- create/modify account or allocation records;
- mutate WordPress users/roles/capabilities;
- activate/deactivate plugins in Multisite;
- change entitlement or Membership implementation;
- execute FP fixtures;
- deploy or release.

Provider/allocation execution remains separately gated even if P-006 fixture execution is later authorized.

## 2. Truth-boundary model

P-006 must preserve these independent authorities:

| Truth | Authority | Cannot imply |
| --- | --- | --- |
| Package presence | local bootstrap/artifact identity | binary compatibility, entitlement, Membership |
| Binary/API/schema compatibility | canonical local compatibility preflight + schema evidence | entitlement, allocation, Membership |
| Product Entitlement | signed/provider-neutral entitlement verifier/domain | binary compatibility, Membership permission |
| Remote Product License/account | provider/account service | signed local entitlement unless contract proves it |
| Site/network allocation | allocation service + bound scope identity | binary compatibility, Membership permission |
| Membership authorization | WordPress/Membership auth policy | product license/entitlement |
| Updater trust | updater/TUF/release trust | any of the above runtime/commercial states |

No test is valid if it collapses these rows into a single `licensed/unlicensed` boolean.

## 3. Existing non-certifying entitlement evidence

### 3.1 Provider-neutral state model

`ProductEntitlementState` currently distinguishes:

- `free`;
- `trial_active`;
- `pro_active`;
- `grace`;
- `expired`;
- `suspended`;
- `verification_stale`;
- `verification_unavailable`;
- `incompatible_version`.

This gives formal planning vocabulary for outage/expiry separation, but the current local provider is intentionally not a remote cryptographic license implementation.

### 3.2 Read/mutation separation

`ProductEntitlementSnapshot` currently:

- allows premium module activation/read for all states except `free` and `incompatible_version`;
- allows premium mutation only for `trial_active`, `pro_active`, `grace`.

Therefore `expired`, `suspended`, `verification_stale` and `verification_unavailable` are currently non-mutating but can preserve read-safe premium state. This is useful architecture input for non-destructive expiry/outage evidence.

### 3.3 Module activation policy

`EntitlementAwareModuleActivationPolicy` always allows Free modules and uses Product Entitlement only for Pro module activation. It does not implement Membership authorization.

### 3.4 Compatibility precedes entitlement setup

`wpessential-pro.php` performs local compatibility preflight and required Free API-profile checks before constructing the local entitlement provider/operation policy or contributing premium modules.

Thus an active entitlement-like state cannot currently force an incompatible binary pair through the preflight. This is strong source input for FP-97/108, not P-006 execution.

### 3.5 Runtime diagnostics separation

`RuntimeDiagnosticsSnapshot` exposes Pro compatibility and entitlement as separate fields. It labels compatibility certification separately and includes site/network context without intentionally exposing account/token material.

### 3.6 Major missing production authorities

No P-006 evidence should pretend the following already exist merely because the domain model has placeholders:

- cryptographic signed entitlement verification;
- issuer/key rotation contract;
- site/network binding cryptography;
- replay/rollback protection for older artifacts;
- clock-skew/timestamp policy;
- remote account/license allocation service;
- per-site Multisite allocation reconciliation;
- clone/staging/production identity service;
- allocation cache-key contract.

Fixtures requiring those authorities are planning gaps until a separate implementation/security gate accepts them.

## 4. Future entitlement evidence record

Every authorized entitlement fixture must record, without secret material:

- exact Free/Pro artifact hashes + canonical compatibility result;
- entitlement state and safe reason code;
- entitlement artifact identifier/fingerprint only if allowed, never raw signed secret/private key/token;
- issuer/key ID and verification result if a signed artifact contract exists;
- bound site/network/environment identity fingerprint, not private account payload;
- verification timestamp/expiry/grace metadata under the accepted clock policy;
- remote service state separately (`not_called`, `reachable`, `unavailable`, etc.);
- Product License/account/allocation state separately;
- Membership principal/capability decision separately;
- premium read/mutation decision;
- local data-preservation assertion;
- network/site scope;
- PASS/FAIL/INCONCLUSIVE/NOT EXECUTED.

A provider HTTP 200/JSON response is not signed entitlement proof unless the accepted contract explicitly makes it so.

## 5. Fixture plan — FP-95…FP-112 entitlement/expiry/outage separation

Status labels: `STRONG EXISTING INPUT`, `PARTIAL EXISTING INPUT`, `GAP`, `CROSS-GATE` are planning-readiness labels only.

| FP | Status | Existing input | Future authorized fixture / expected result | Stop / dependency |
| --- | --- | --- | --- | --- |
| FP-95 | PARTIAL EXISTING INPUT | Compatible pair can set local `pro_active`/`trial_active`; operation policy permits read+mutation. | Compatible immutable pair + cryptographically valid active entitlement for correct scope; expect supported premium operation only after compatibility + authorization checks. | Requires accepted signed-entitlement implementation; current local provider alone cannot certify cryptographic validity. |
| FP-96 | STRONG EXISTING INPUT for local policy | `expired` keeps premium read/module activation but denies mutation. | Transition valid entitlement to confirmed expired state; premium mutation blocked, owned local data/config/history preserved, safe reads/output follow accepted product contract. | Data-preservation fixture + product expiry contract required. |
| FP-97 | STRONG EXISTING INPUT | Compatibility preflight runs before entitlement/provider setup and blocks premium contribution. | Supply active valid entitlement to an incompatible pair; canonical compatibility must still block premium boot/migrations/mutations. | STOP if entitlement overrides binary/API/schema incompatibility. |
| FP-98 | PARTIAL EXISTING INPUT | `verification_unavailable` is distinct from `expired`; read-safe state is possible. | Provider outage with still-valid accepted offline entitlement/cache; compatibility unchanged, continuity follows offline contract, no forced expiry. | Requires signed offline-cache/TTL policy; provider sandbox execution separately authorized. |
| FP-99 | PARTIAL EXISTING INPUT | `verification_unavailable` state exists and denies mutation while preserving read-safe activation. | Provider unavailable and no valid offline proof; result must be unavailable/verification-degraded, not `expired`, with fail-closed premium mutation. | Requires verifier/provider integration. |
| FP-100 | PARTIAL EXISTING INPUT | `verification_stale` is a distinct state and denies mutation. | Valid prior proof passes freshness threshold into stale window; state becomes stale according to explicit policy, not expired/unavailable conflation. | Needs timestamp/freshness contract. |
| FP-101 | GAP | No accepted cryptographic entitlement verifier identified in current local provider path. | Tamper signature/payload/key ID on disposable artifact; verifier must reject and never grant premium mutation. | Security/crypto implementation gate + Lane E adversarial review. |
| FP-102 | GAP | No accepted site/network binding verifier. | Present validly signed artifact bound to different site/network/environment identity; reject for this scope. | Identity/binding contract required. |
| FP-103 | GAP | No accepted entitlement anti-replay/monotonic revision mechanism. | Replay older but otherwise valid artifact after newer revision accepted; older proof cannot roll authority backward. | Requires revision/issued-at/nonce policy and durable anti-rollback state. |
| FP-104 | GAP | No clock-skew policy. | Verify entitlement just inside accepted clock tolerance; remain valid according to documented boundary. | Clock source/tolerance must be fixed before execution. |
| FP-105 | GAP | No clock-skew policy. | Extreme future/past clock outside tolerance must block mutation safely without rewriting binary compatibility. | STOP if skew creates active entitlement. |
| FP-106 | PARTIAL EXISTING INPUT | `grace` currently permits mutation; `expired` does not. | Execute deterministic grace start/end boundaries under accepted time policy; at expiry boundary mutation changes exactly once to blocked while data remains. | Needs transition/clock contract; current enum alone is insufficient. |
| FP-107 | GAP/PARTIAL | Stale/unavailable states are distinct; no remote recovery implementation. | Restore provider service; revalidate entitlement/account state; binary compatibility result must remain unchanged unless artifacts changed. | Provider sandbox + signed verifier required. |
| FP-108 | STRONG EXISTING INPUT | Local compatibility evaluation is explicitly independent of licensing and precedes entitlement setup. | Feed arbitrary successful provider/account response while binary pair is incompatible; premium remains blocked. | STOP if remote response becomes compatibility authority. |
| FP-109 | PARTIAL EXISTING INPUT | Binary preflight + module registry dependency/state machinery exist; Product Entitlement only governs Pro activation. | Valid entitlement with one missing required third-party/platform dependency; only dependent path may activate/degrade according to accepted dependency contract, never bypass dependency due license. | Requires explicit dependency applicability per module. |
| FP-110 | PARTIAL EXISTING INPUT | Expired/stale/unavailable states deny mutation without automatically disabling read-safe module activation; mismatch paths contain no deletion behavior. | Persist representative premium definitions/history/data, expire entitlement, compare durable fingerprints; no deletion or silent destructive cleanup. | Lane C data-integrity snapshot; STOP on destructive expiry. |
| FP-111 | PARTIAL EXISTING INPUT | Product Entitlement policy is separate from WordPress/Membership auth; it does not grant user capabilities. | Active product entitlement + principal lacking Membership/WordPress permission attempts protected premium mutation; authorization must deny despite product license. | Membership/authorization harness required; no user mutation in planning. |
| FP-112 | PARTIAL EXISTING INPUT | Product expiry denies premium mutation regardless of other local policy layers. | Principal with valid Membership permission but product entitlement expired attempts premium mutation; product operation policy still blocks commercial mutation. | Requires composed auth+entitlement integration fixture. |

## 6. Fixture plan — FP-113…FP-128 Multisite/allocation/clone/restore

| FP | Status | Existing input | Future authorized fixture / expected result | Stop / dependency |
| --- | --- | --- | --- | --- |
| FP-113 | GAP/PARTIAL | Runtime diagnostics/persistence know `network_id` and `site_id`; package compatibility constants are request-local/global and not commercial allocations. | Network-activate Free+Pro in disposable Multisite; define whether pair compatibility is evaluated once per request/network package pair while site-specific prerequisites remain explicit. | Multisite activation contract required; compatibility must not manufacture site allocations. |
| FP-114 | GAP | No P-006 network-Free/site-Pro activation evidence. | Network Free + Pro activated only on one site; that site's runtime may evaluate same installed pair, other sites must not gain premium allocation/authority implicitly. | WordPress activation topology + allocation contract. |
| FP-115 | GAP | No accepted inverse scope behavior. | Site Free + network Pro topology: explicitly support or safely reject/degrade; no fatal/current-blog guessing. | Contract decision required before execution. |
| FP-116 | GAP | No remote network-account allocation service implemented/accepted for P-006. | Link network account without allocating child sites; prove zero automatic production allocations until explicit policy/action. | Provider/allocation sandbox gate. |
| FP-117 | GAP | No per-site allocation identity/reconciliation contract. | Allocate one child site; repeat same request; effective one allocation with deterministic idempotent result. | Requires durable allocation key/idempotency contract. |
| FP-118 | GAP | Same. | Another child site remains unallocated and cannot gain mutation authority from sibling allocation/account link. | STOP on cross-site bleed. |
| FP-119 | GAP | No staging/production identity binding contract. | Clone production DB to staging identity; staging must not consume/create second production allocation merely because copied local state says allocated. | Requires environment identity + reconciliation state. |
| FP-120 | GAP | Same. | Promote staging to production; explicit reconciliation/transfer/new-allocation flow according to accepted commercial contract, never automatic duplicate. | Provider/allocation workflow gate. |
| FP-121 | GAP | No canonical domain-change allocation identity contract. | Change site domain while stable site identity remains; reconcile existing allocation rather than duplicate blindly. | Domain is evidence input, not sole allocation primary key. |
| FP-122 | GAP | No network-domain-change contract. | Network primary domain change preserves/revalidates relationship according to stable identity; child allocation map remains coherent. | Requires signed/provider reconciliation. |
| FP-123 | GAP | No restore-to-different-identity safety contract. | Restore DB to a different site/network identity; copied entitlement/allocation becomes reconciliation-required/non-mutating until rebound. | STOP if copied DB manufactures active production authority. |
| FP-124 | GAP/PARTIAL | Local entitlement snapshot can preserve safe state, but no remote freshness reconciliation. | Restore backup to same stable site identity; recover local entitlement proof if still valid, but stale remote/account/allocation state is revalidated before mutation according to contract. | Provider/verifier integration. |
| FP-125 | GAP | No durable blog-ID generation/reuse protection. | Delete/recreate site so a numeric blog ID could be reused; stale allocation cannot attach solely by blog ID. | Requires stable scoped identity stronger than numeric blog ID alone. |
| FP-126 | GAP | No remote allocation idempotency/reconciliation implementation. | Two concurrent allocation requests for same scope; provider/local reconciliation yields one effective allocation or safe conflict requiring reconciliation. | Concurrency + provider sandbox; STOP on duplicate billable/production allocation. |
| FP-127 | GAP | No accepted remote entitlement/allocation cache key implementation. | Seed cross-site/network stale cache entry; lookup key must include canonical scope identity/environment/revision so one site's authority cannot bleed to another. | Security/cache design + Lane E adversarial tests. |
| FP-128 | PARTIAL EXISTING INPUT | Runtime diagnostics currently expose site/network IDs, compatibility state and entitlement state, not tokens/account IDs. | Multisite admin/network diagnostics fixture captures output/logs for allocated/unallocated/mismatch states; assert no license token, signed artifact, account secret/private allocation ID leakage. | Lane E authority/redaction review. |

## 7. Signed entitlement contract prerequisites

FP-95…FP-107 cannot be certified from a local enum/constant alone. Before those fixtures execute, a separate accepted design/implementation must specify at minimum:

- signature algorithm and canonical serialization;
- issuer/key IDs and rotation/revocation strategy;
- verification key distribution/trust root;
- entitlement subject/product/edition;
- stable site/network/environment binding fields;
- issued-at/not-before/expiry/grace fields;
- monotonic revision or anti-replay mechanism;
- offline-cache validity and stale thresholds;
- maximum accepted clock skew;
- safe reason codes and privacy rules;
- fail-closed behavior on malformed/unknown algorithm/key/version.

Private signing keys must never enter repository fixtures.

## 8. Membership separation harness

Future FP-111/112 evidence should use a Cartesian decision table rather than one happy path:

| Binary compatible | Product entitlement permits mutation | Membership/user permission | Expected mutation |
| --- | --- | --- | --- |
| no | yes | yes | denied by compatibility |
| yes | no | yes | denied by product entitlement |
| yes | yes | no | denied by Membership/auth |
| yes | yes | yes | allowed only if module/dependency/schema policy also allows |

The harness must record **which authority denied** without leaking commercial/private details. A denial in one layer must not be rewritten as a false state in another.

## 9. Multisite identity model prerequisites

Before allocation fixtures can execute, the accepted design must define canonical stable identity. It must not rely solely on:

- domain name;
- `blog_id`;
- copied database UUID/state;
- current network ID without environment binding.

A future scope identity likely needs an opaque installation/network/site identity plus environment classification and provider-side allocation ID, but this planning document does not select the production format.

Required properties:

- stable across ordinary domain change;
- distinguish clone/staging from production;
- prevent blog-ID reuse inheritance;
- support restore-to-same vs restore-to-different identity;
- support network + per-site allocation without cross-site cache bleed;
- remain privacy-safe in diagnostics.

## 10. Allocation operation contract prerequisites

Any future allocation mutation fixture must use:

- idempotency key derived from safe operation/scope identity;
- optimistic revision or equivalent conflict detection;
- explicit desired vs observed allocation state;
- retry semantics after timeout/unknown provider result;
- reconciliation read before duplicate creation;
- audit record with safe identifiers;
- separate network account-link state and child-site allocation state.

A timeout cannot be interpreted automatically as `allocation failed` or `allocation succeeded` without reconciliation.

## 11. Stop-the-line additions for Lane D

Stop entitlement/Multisite evidence immediately if any confirmed fixture shows:

1. entitlement/provider/account response authorizes an incompatible binary pair;
2. binary compatibility manufactures product entitlement;
3. provider outage or stale verification is silently rewritten as `expired`;
4. expiry/stale/unavailable state deletes owned local data/config/history;
5. forged/wrong-scope/replayed entitlement grants premium authority;
6. Product License/account state grants Membership/user permission;
7. Membership permission bypasses expired/non-mutating product entitlement;
8. linking a network account silently allocates all child sites;
9. clone/restore/domain/blog-ID reuse manufactures or duplicates production allocation;
10. concurrent allocation requests create duplicate effective allocations;
11. entitlement/allocation cache or diagnostics leak authority across sites/networks;
12. tokens, signing material, private account payloads or secrets appear in evidence/log/UI.

## 12. Cross-gate dependencies

| Dependency | Fixtures | Requirement |
| --- | --- | --- |
| P-006 scoped ADR-0014 consent | FP-95…FP-128 | Mandatory before executable P-006. |
| Lane A compatibility | All | Exact immutable pair + canonical local compatibility result. |
| Lane C data/schema integrity | FP-110, FP-123/124 where restore affects DB | Durable-state snapshots/recovery. |
| Signed entitlement implementation/security gate | FP-95…FP-107 | Crypto/binding/replay/clock contract. |
| Membership/auth integration | FP-111, FP-112 | Explicit protected operation and principal/capability setup. |
| Remote provider/account/allocation sandbox authorization | FP-98/99/107, FP-116…FP-127 | No live production provider calls. |
| Multisite scope/identity contract | FP-113…FP-128 | Stable network/site/environment identity. |
| Lane E security/observability | FP-101…105, FP-127, FP-128 | Adversarial crypto/cache/privacy review. |

## 13. Recommended future execution slices

1. **D1 — Local policy truth table**: selected FP-96, FP-97, FP-108, FP-110…FP-112 using local provider only, explicitly not claiming crypto/provider certification.
2. **D2 — Signed entitlement verifier**: FP-95, FP-98…FP-107 after crypto contract + sandbox verifier is accepted.
3. **D3 — Multisite activation scope only**: FP-113…FP-115 with no remote allocation mutation.
4. **D4 — Allocation semantics**: FP-116…FP-118 under sandbox provider gate.
5. **D5 — Clone/staging/domain/restore identity**: FP-119…FP-125 with disposable Multisite clones/backups.
6. **D6 — Allocation concurrency/cache/privacy**: FP-126…FP-128 with Lane E review.

## 14. Readiness conclusion

Lane D is **PLANNING COMPLETE / EXECUTION BLOCKED** when this document is accepted.

Current main already separates local compatibility from a provider-neutral Product Entitlement state model and distinguishes active/grace/expired/stale/unavailable states with separate read/mutation policy. That is meaningful architecture, but it is not evidence of cryptographic entitlement validity or remote commercial allocation correctness.

The largest gaps are signed-verifier trust, scope binding, replay protection, clock policy, provider recovery, Membership composition evidence, stable Multisite identity and per-site allocation/reconciliation semantics.

No FP-95…FP-128 fixture is executed by this planning work. No provider call, allocation mutation, user/role change or certification promotion occurs. ADR-0010 and all P-006 counters remain unchanged.
