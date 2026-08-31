# ADR-0042 — Product Entitlement Cryptographic Profile

Status: **Accepted cryptographic profile; implementation/interoperability evidence pending**  
Date: 2026-08-27

## Decision

WPEssential product-entitlement documents use a fixed v1 profile built around:

- **Ed25519** asymmetric signatures;
- **RFC 8785 JCS** canonical JSON for the signed semantic document;
- explicit WPE domain separation/versioning;
- stable `kid`-based trusted signer resolution;
- root-authorized entitlement signer keysets;
- monotonic entitlement sequence + signed freshness windows;
- strict site/activation/environment binding.

No v1 runtime algorithm negotiation and no HMAC/shared secret embedded in the distributed plugin.

Native PHP `ext-sodium` is the preferred verifier. A maintained Sodium compatibility library may only be accepted for signature verification after dependency/security evidence; no home-grown crypto fallback.

## Why

This keeps the service private signing key off customer sites, provides deterministic signed bytes, supports safe signer rotation without trusting normal API responses, and preserves ADR-0017's outage/expiry/anti-rollback semantics.

## Security boundaries

- product entitlement is not Membership access authority;
- entitlement keys are not Pro update/release keys;
- signature authenticity does not replace TLS or OAuth/service authorization;
- full offline site snapshot rollback cannot be perfectly defeated locally;
- unknown/malformed schema fails closed for paid management authorization but must not destroy Free/runtime data.

## Consequences

The signed envelope and keyset manifest become long-lived compatibility contracts. Exact field encoding, canonicalizer/library, key custody, TTL values and rotation runbooks require evidence before implementation.

## Required future evidence

See `docs/SECURITY/PRODUCT-ENTITLEMENT-CRYPTO-PROFILE-CANDIDATE.md`.

Must verify canonicalization, Ed25519 vectors, parser ambiguity, key rotation/revocation, site binding, rollback, clock/freshness and compatibility behavior.

All executable work remains prohibited until explicit owner consent under ADR-0014.