# ADR-0021 — Backup Encryption & Recovery Architecture

Status: **Accepted security architecture; exact crypto profile pending**  
Date: 2026-08-27

## Decision

WPEssential Backup supports optional client-side authenticated archive encryption with independent disaster-recovery key semantics. Encryption must remain recoverable when the original WordPress database/server is lost.

Accepted invariants:

1. **Archive encryption is separate from TLS and provider SSE.**
2. **Use reviewed authenticated encryption; no custom cipher.**
3. **Large backups require streaming/chunk-capable encryption.**
4. **Each Backup Set/artifact family uses a random DEK rather than one long-lived direct data key.**
5. **DEK is wrapped by an explicit recovery mode:**
   - user passphrase-derived key;
   - independent site/customer recovery key;
   - future external KMS/HSM adapter.
6. **The only recovery key must not live solely inside the same DB/archive it protects.**
7. **WordPress salts are not the preferred long-term backup recovery root.** Salt loss/rotation must not silently destroy disaster-recovery ability.
8. **Encrypted backup uses a minimal public envelope and encrypted sensitive inner manifest where feasible.**
9. **No plaintext fallback after encryption/key error.**
10. **Lost key is an explicit unrecoverable state unless another approved recovery/escrow mechanism exists.**
11. **Customer-controlled key escrow with WPE is OFF by default.** Future escrow requires separate ADR/service/security model.
12. **Site-managed mode needs a separately exportable recovery kit.**
13. **Key rotation should re-wrap per-backup DEKs where the accepted format permits, rather than unnecessarily re-encrypt all large archive data.**
14. **Remote provider receives ciphertext when client-side encryption is enabled.**
15. **V3 Restore Tested is the strongest proof that encrypted backup + key recovery actually works.**

## Current cryptographic candidate

For stream encryption:
- libsodium `secretstream_xchacha20poly1305` candidate.

For passphrase-derived wrapping key:
- Argon2id through a maintained Sodium/PHP profile candidate.

Exact container, key-wrap format, KDF parameters and PHP compatibility are not yet Accepted.

## Why

Authenticated streaming encryption is suitable for multi-GB archives and can detect corruption/truncation/reordering. Key-management guidance also requires lifecycle/rotation and recoverability for long-lived encrypted data; a backup that loses its only key is not a recovery system.

## Consequences

- encrypted backups can remain portable off-site;
- disaster recovery no longer assumes original WP salts/database survive;
- UX must teach administrators about key loss/recovery kit;
- encrypted backup format becomes a long-lived compatibility contract;
- support cannot promise recovery of customer-only keys.

## Follow-up blockers

Before implementation:
- exact container/envelope specification;
- Sodium support matrix;
- Argon2id cost benchmark on minimum hosts;
- key wrapping/re-wrapping profile;
- recovery-kit format;
- large-stream interruption/resume semantics;
- fresh-server disaster restore fixture;
- KMS adapter contract if ever included.

All executable work remains blocked by ADR-0014.