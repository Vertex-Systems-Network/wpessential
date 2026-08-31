# ADR-0017 — Product Entitlement Verification Architecture

Status: **Accepted architecture; cryptographic profile implementation pending**  
Date: 2026-08-27

## Decision

WPEssential commercial product entitlement is a **signed, site/activation-bound, freshness-aware document**, not a mutable local `is_pro=true` flag and not the Membership System's site-user entitlement store.

Accepted invariants:

1. **Product license and Membership access are separate authorities.**
   - WPE Pro product entitlement controls creation/editing/management rights for licensed WPE features.
   - Membership entitlements control site-user/member access to protected resources.
   - WPE licensing-service outage/expiry must never expose protected member content/files.

2. **Cached product entitlement is asymmetrically verifiable.**
   - signing private keys remain only in WPE service infrastructure;
   - WordPress ships only public verification trust;
   - TLS is required but not the sole authenticity property for cached state.

3. **Signed document is bound to the intended site activation/installation context.**

4. **Signed document has explicit freshness windows.**
   - `issued_at` / `not_before`;
   - refresh-due boundary;
   - validity expiry;
   - optional outage-grace boundary.

5. **Outage grace is service-signed, never invented/extended locally.**

6. **Network outage is not commercial expiry.**
   - `verification_unavailable`/stale and `expired`/`suspended` are distinct states.

7. **A freshly verified explicit expiry/suspension supersedes older cached active state.**

8. **Grace exhaustion restricts management, not data ownership or safe deployed runtime.**
   - Free remains functional;
   - Pro definitions/data remain;
   - editing/creation/paid remote operations may become read-only/unavailable;
   - security/access enforcement remains safe according to ADR-0007.

9. **Anti-rollback metadata is maintained locally**, while acknowledging full snapshot/database rollback cannot be perfectly defeated offline; short signed validity bounds replay exposure.

10. **Key rotation uses an existing trust chain.** A normal API response cannot establish a new trusted signing key by itself.

11. **Entitlement signing keys are separate from executable Pro update/release keys.**

12. **Refresh credentials are secrets and live in Secrets Vault; signed entitlement documents are integrity-protected state, not secret credentials.**

## Cryptographic profile intentionally not yet Accepted

Current candidate:
- RFC 8785 JCS canonical JSON;
- Ed25519 asymmetric signature using a maintained/verified PHP strategy;
- explicit schema/algorithm/key ID.

A standards-based JWS EdDSA profile remains a valid alternative.

Exact format, library/dependency, canonicalizer and PHP Sodium support require owner-authorized executable interoperability/security evidence before implementation.

## Consequences

- service outage cannot be confused with billing expiry;
- local option tampering does not create a valid signed license state;
- Membership protection remains independent from WPE SaaS availability;
- key rotation/compromise response is possible;
- commercial grace policy can change server-side only through signed documents without trusting arbitrary local clocks/flags.

## Follow-up blockers

- exact cryptographic/signature profile;
- key manifest/root rotation format;
- token/document lifetime values;
- exact clock-skew thresholds;
- service-side schema/OpenAPI;
- executable malformed/replay/rotation fixtures.

These do not reopen the accepted authority/freshness separation above.
