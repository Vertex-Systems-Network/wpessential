# WPEssential — Product Entitlement Signing & Offline Grace Model

Status: **Phase 0 planning / no implementation authorized**  
Date: 2026-08-27

## 1. Scope and non-confusion rule

This document is about the **WPEssential commercial product license/entitlement** for WPEssential Pro management capabilities.

It is **not** the Membership System's member/customer entitlement engine.

These must never share authority:
- WPE product entitlement answers whether this WordPress installation may create/edit/use licensed WPE Pro management capabilities;
- Membership entitlement answers whether a site user/member may access a protected site resource/benefit;
- Membership authorization is site-local and must never require the WPE licensing service to be reachable;
- expiry/outage of a WPE product license must never silently expose member-protected content/files.

## 2. Goals

The commercial entitlement client must:
- verify entitlement authenticity without trusting mutable local flags;
- distinguish service outage from commercial expiry/suspension;
- work without a remote call on every admin/frontend request;
- tolerate bounded temporary outage;
- preserve Free functionality;
- preserve safe deployed Pro runtime according to ADR-0007;
- make local tampering/replay materially harder;
- support signing-key rotation and compromise response;
- expose understandable freshness state to administrators.

It is not intended to provide DRM against a fully compromised PHP/server administrator. A site owner with arbitrary code execution controls the runtime. The goal is a reliable, tamper-evident commercial protocol, not impossible client-side secrecy.

## 3. Threat model

Consider:
- locally changing an option from expired to active;
- forging module grants;
- replaying an older still-valid entitlement after downgrade;
- replaying an old entitlement restored from a database backup;
- service/API response manipulation;
- stale/frozen service metadata;
- system clock rollback;
- cloning an entitlement to another activation/site;
- signing-key compromise;
- refresh-token compromise;
- license-service outage;
- account disconnection;
- Free/Pro version incompatibility.

TLS is required but is not the only authenticity mechanism for cached entitlement state.

## 4. Signed entitlement document

Candidate payload v1 contains only information required for local product authorization and explainability:

- `schema_version`
- `document_id`
- monotonic `sequence`
- `issuer`
- `environment`
- `audience`
- `site_activation_id`
- local `installation_uuid` binding where service model supports it
- opaque account/customer reference when necessary
- product ID
- plan ID
- granted module/capability groups
- commercial state (`trial_active`, `pro_active`, `expired`, `suspended`, etc.)
- trial/subscription effective facts needed for display
- `issued_at`
- `not_before`
- `refresh_after`
- `expires_at`
- optional **signed** `outage_grace_until`
- minimum/maximum compatible Platform API where useful
- key ID (`kid`)

Do not copy unnecessary invoices, addresses, payment methods, card information or provider payloads into this document.

## 5. Candidate signature format

Preferred paper design:
- payload serialized using **RFC 8785 JSON Canonicalization Scheme (JCS)**;
- asymmetric **Ed25519** signature;
- detached/base64url signature envelope with explicit algorithm/version/key ID;
- verification public keys embedded in trusted WPE code/root-key metadata;
- private signing keys exist only in WPE service/release infrastructure.

Why canonicalization matters: signing arbitrary JSON serialization creates ambiguity because whitespace/property order/number representation can differ. RFC 8785 provides an invariant representation specifically for cryptographic use.

Why Ed25519 is attractive: modern deterministic signatures, compact keys/signatures and PHP Sodium support. It remains a **candidate** until an authorized interoperability/crypto spike verifies the exact PHP/server support and implementation contract.

A standards-based JWS EdDSA profile remains an alternative if it provides a safer maintained implementation without unnecessary dependency cost.

## 6. Verification order

Before accepting a cached/new document:
1. parse under strict size/depth/schema limits;
2. reject unknown critical schema/algorithm versions;
3. resolve `kid` only from an already trusted key set;
4. canonicalize the unsigned payload deterministically;
5. verify signature;
6. verify issuer/audience/environment;
7. verify site activation/installation binding;
8. verify `not_before`/expiry with bounded clock-skew policy;
9. enforce compatibility claims;
10. enforce anti-rollback sequence rules;
11. persist the signed document + safe verification metadata atomically;
12. compute local effective product state.

A remote API cannot simply send an arbitrary new public key and ask the plugin to trust it. New keys require an existing trust chain/root update.

## 7. Anti-rollback / replay

Store safe local metadata such as:
- highest accepted entitlement `sequence` for the active site activation;
- latest accepted document ID;
- latest issued-at/verified-at timestamps;
- active signing key ID.

While local state is intact, reject a lower sequence unless a separately trusted recovery/rebind operation explicitly authorizes it.

Limitation: restoring an old full database backup can restore old anti-rollback metadata too. Therefore anti-rollback cannot rely only on local counters. Short entitlement validity + remote refresh bounds the useful replay window after a restore. Do not overclaim perfect rollback prevention on a fully offline/fully restored client.

## 8. Clock rollback handling

Because commercial validity uses time:
- store `last_verified_server_time`/issued time and local observation metadata;
- tolerate a small documented skew;
- a large backwards local clock jump must not extend an entitlement indefinitely;
- suspicious rollback changes product-management state to `verification_stale`/needs verification rather than silently creating more valid time;
- public runtime/security enforcement still follows ADR-0007 and is not torn down due to clock suspicion.

Exact skew thresholds require implementation evidence.

## 9. Freshness phases

Signed timestamps define distinct phases.

### Fresh
`now < refresh_after`
- normal licensed management behavior;
- no unnecessary service request.

### Refresh due
`refresh_after <= now < expires_at`
- current signed entitlement still valid;
- background/administrative refresh is due;
- failed refresh does not equal expired.

### Service-outage grace
`expires_at <= now < outage_grace_until`, **only if the signed document explicitly contains such a window**
- state shown as `grace` / verification stale;
- WPE may temporarily preserve management operations according to commercial policy;
- warnings are contextual, not global nag spam;
- retries use backoff.

The client does **not invent or extend** grace locally. The grace boundary is signed by the service.

### Grace exhausted / unverifiable
`now >= outage_grace_until`
- creation/editing and paid remote-service operations become unavailable/read-only;
- Free remains functional;
- existing safe deployed output remains available where technically possible;
- security/access enforcement remains at the safe last-known state;
- mutating premium automations follow ADR-0007 pause/read-only rules;
- reconnect/verify action is shown.

## 10. Explicit commercial expiry/suspension vs outage

A freshly verified, signed server state saying `expired` or `suspended` is **not** treated as a network outage.

Rules:
- explicit effective commercial state takes precedence over an older locally cached active state;
- the client must not apply outage grace to intentionally override a newer signed expiry/suspension unless the signed document itself defines an effective transition/grace;
- runtime continuity still follows ADR-0007: do not delete definitions/data or expose protected resources.

## 11. Trial behavior

A trial expiry is a commercial fact, not a connectivity error.

Any outage tolerance around trial verification must be encoded by the signed service document. The plugin must not locally turn a 30-day trial into an open-ended trial because the service cannot be reached.

## 12. Disconnect behavior

Explicit account disconnect:
- attempts remote token revocation when reachable;
- removes refresh/access credentials locally;
- keeps Free configuration;
- keeps Pro definitions/data;
- immediately disables future product-entitlement refresh;
- Pro management becomes read-only/unavailable according to disconnect policy rather than silently relying forever on cached activation;
- safe deployed runtime remains according to ADR-0007.

Reconnect creates a new trusted linking/activation verification flow.

## 13. Key hierarchy and rotation

Do not use one key forever for everything.

Candidate separation:
- product-entitlement signing key(s);
- Pro software-update/release signing key(s);
- account/OAuth credentials/tokens;
- support/webhook keys.

Entitlement keyset:
- public key IDs and validity windows;
- introduce new public key before cutover through trusted root/package update;
- overlap old/new verification for bounded period;
- stop issuing with old key;
- retire old key after all legitimately cached documents expire;
- emergency compromise procedure can revoke key through a trusted root/update path.

Compromise of an entitlement signing key must not automatically permit signing executable Pro update packages.

## 14. Storage

The signed entitlement blob is integrity-protected, not secret. It may live in local configuration/cache with strict size/schema handling.

Secrets such as refresh credentials remain in Secrets Vault.

Do not expose the full blob/signature as a generic frontend dynamic token. Diagnostics can show safe fields:
- product state;
- plan;
- last verified;
- refresh/expiry/grace times;
- key ID;
- document/sequence summary;
- verification error category.

## 15. Cache invalidation

Refresh product entitlement when relevant events occur:
- explicit Refresh action;
- successful account link/reconnect;
- purchase/upgrade/renew handoff completion followed by verified service response;
- nearing `refresh_after`;
- Free/Pro compatibility change that requires updated entitlement applicability;
- service signals a valid authenticated invalidation opportunity.

Never poll on every page request.

## 16. Failure states

Normalize at least:
- `signature_invalid`
- `unknown_signing_key`
- `schema_unsupported`
- `site_binding_mismatch`
- `document_rollback_detected`
- `not_yet_valid`
- `clock_suspicious`
- `refresh_due`
- `verification_unavailable`
- `grace_active`
- `grace_exhausted`
- `commercial_expired`
- `commercial_suspended`
- `platform_incompatible`

These map to WPE error taxonomy and safe admin UX.

## 17. Security properties and limits

Provides:
- tamper-evident cached entitlement;
- asymmetric verification with no signing secret in WordPress;
- bounded offline behavior;
- explicit distinction between outage and expiry;
- key rotation support;
- local anti-rollback when state is intact.

Does not provide:
- protection from a site owner who modifies WPE PHP/runtime code;
- instant revocation for a site that is completely offline;
- perfect rollback protection after full server/database snapshot rollback;
- payment/card security (not in scope).

## 18. Evidence required before acceptance/implementation

Static decisions can be accepted now conceptually, but exact cryptographic profile remains Proposed until an owner-authorized spike verifies:
- RFC 8785 implementation/interoperability in selected PHP dependency strategy;
- Sodium/Ed25519 support across accepted PHP matrix;
- signature/key rotation fixtures;
- malformed/canonicalization test vectors;
- clock/sequence behavior;
- Free↔Pro mismatch behavior;
- offline freshness transition tests.

No code, keys, tokens or entitlement service were created by this planning document.

## Sources
- RFC 8785 — JSON Canonicalization Scheme.
- TUF security model — rollback, freeze and signing-key compromise threats inform the update/metadata threat model.
