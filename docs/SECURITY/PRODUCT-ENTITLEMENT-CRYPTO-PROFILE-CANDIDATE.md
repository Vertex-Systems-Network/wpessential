# WPEssential — Product Entitlement Cryptographic Profile Candidate

Status: **Phase 0 static security design / no implementation authorized**  
Related: ADR-0017, ADR-0014, Product Entitlement Signing & Offline Grace.

## Goal

Turn the already accepted entitlement authority/freshness model into a narrow, interoperable signing profile without creating a local `is_pro` trust flag or depending only on TLS.

## Current preferred profile

### Signature algorithm

**Ed25519 detached signatures through a maintained Sodium-compatible implementation.**

Reasons:
- asymmetric verification keeps private signing keys off WordPress sites;
- PHP exposes Ed25519 verification through Sodium;
- small public keys/signatures;
- deterministic signing behavior;
- no RSA padding/profile complexity;
- no shared HMAC secret shipped inside a distributed plugin.

No algorithm agility in v1 ordinary runtime. `alg` is fixed by schema. An unexpected algorithm is rejected rather than negotiated.

### Canonical payload

Use **RFC 8785 JSON Canonicalization Scheme (JCS)** for the signed object.

The signed bytes are domain-separated:

`WPE\x00PRODUCT-ENTITLEMENT\x00V1\x00` + JCS(document)

This prevents the same signature from being interpreted as another WPE document class.

The exact byte-level interoperability fixture remains consent-gated.

### Envelope

Candidate transport/storage envelope:

```json
{
  "schema": "wpe.product-entitlement-envelope.v1",
  "kid": "ent-live-2026-q3-01",
  "payload": { "...": "canonical entitlement document" },
  "signature": "base64url-without-padding"
}
```

The `schema` and `kid` are themselves inside the object covered by the signature, or equivalently are included in a fixed signed protected section. The final serialization choice must have exactly one signed semantic interpretation.

## Required entitlement payload fields

- schema/version;
- issuer/environment;
- activation UUID;
- install UUID or accepted activation-binding identity;
- normalized site origin/domain binding or hash according to privacy policy;
- account/customer reference in non-secret opaque form;
- plan/product identifier;
- enabled product capabilities/modules;
- commercial state;
- entitlement sequence number;
- issued-at;
- not-before;
- refresh-after;
- expires-at;
- optional service-authorized grace-until;
- Free↔Pro Platform API compatibility range where relevant;
- optional reason/status code safe for local UX.

No payment card data, account password, refresh token or Membership-site-user entitlement belongs in this document.

## Time semantics

The verifier distinguishes:

- `fresh`;
- `refresh_due`;
- `verification_unavailable_but_valid`;
- `signed_grace`;
- `expired`;
- `explicitly_suspended/revoked`;
- `invalid_signature/schema/binding`.

A network error never mutates `expired` into active or creates grace locally.

Exact production TTLs and clock-skew tolerance remain service-policy/evidence items. They are not hard-coded in this paper profile.

## Anti-rollback

Persist highest trusted entitlement sequence per activation plus latest trusted issuance metadata.

Rules:
- lower sequence is rejected as stale/rollback;
- same sequence with different signed bytes is a conflict/security event;
- newer signed explicit suspension/revocation supersedes older cached active state;
- full offline filesystem/DB snapshot rollback cannot be perfectly defeated locally and is not falsely claimed to be solved.

Short signed validity windows bound snapshot-replay exposure.

## Key trust and rotation

### Online entitlement signing key

- used only for WPE commercial entitlement documents;
- never reused for Pro executable releases, OAuth, webhooks or backups;
- identified by `kid`;
- can be rotated frequently enough to limit operational exposure.

### Entitlement root trust

WordPress ships one or more long-lived entitlement-root public keys or a threshold root set.

A root-signed **Entitlement Keyset Manifest** authorizes currently valid online signer public keys and their validity windows.

Candidate keyset fields:
- schema/version;
- keyset sequence;
- signer `kid`;
- Ed25519 public key;
- not-before/expires-at;
- status active/retired/revoked;
- environment;
- root signatures.

A normal licensing API response cannot self-authorize a new trusted signing key.

Exact root threshold/custody is a separate operations decision; reuse of Pro-update root keys is prohibited.

## Parser/security requirements

Before using payload semantics:
1. enforce maximum envelope/payload size;
2. decode strict UTF-8 JSON;
3. reject malformed/duplicate-key or non-canonicalizable structures according to accepted parser profile;
4. validate schema and fixed algorithm/profile;
5. resolve trusted `kid` only from accepted key trust;
6. verify signature over exact domain-separated canonical bytes;
7. validate issuer/environment;
8. validate site/activation binding;
9. validate time/freshness;
10. enforce anti-rollback sequence;
11. only then expose normalized entitlement state.

Unknown fields may be preserved/ignored only according to schema-forward-compatibility rules; security-critical unknown semantics cannot silently broaden access.

## PHP/Sodium dependency direction

Native `ext-sodium` is preferred.

For **signature verification only**, `paragonie/sodium_compat` remains a possible compatibility fallback after dependency/security review because it supports Ed25519 APIs. It is not automatically accepted as a global cryptographic fallback.

No pure-PHP home-grown Ed25519 implementation.

## Logging

Log only:
- key ID;
- sequence;
- normalized entitlement state;
- verification result code;
- issuance/expiry metadata as operationally necessary.

Do not log raw service tokens, account secrets or unnecessary signed payload PII.

## Future executable evidence — NOT AUTHORIZED

- RFC 8785 canonicalization cross-language fixtures;
- Ed25519 service↔PHP vectors;
- malformed JSON/duplicate key tests;
- wrong `kid`/algorithm/schema;
- signature bit-flips;
- site-binding mismatch;
- sequence rollback/conflict;
- clock skew/freshness;
- root/keyset rotation and compromise;
- native Sodium vs approved fallback interoperability;
- Free↔Pro old-client handling.

No signing/verifier code, key generation or service endpoint has been implemented.