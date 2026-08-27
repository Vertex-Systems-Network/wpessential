# ADR-0043 — Backup Encryption Cryptographic Profile

Status: **Accepted cryptographic profile; implementation/performance evidence pending**  
Date: 2026-08-27

## Decision

WPEssential encrypted Backup v1 uses:

- one random 256-bit Backup Set DEK;
- libsodium **`secretstream_xchacha20poly1305`** for authenticated streaming encryption of backup parts;
- XChaCha20-Poly1305 IETF AEAD for wrapping the Backup Set DEK under independent recovery-key slots;
- **Argon2id** for passphrase-derived key-encryption keys;
- explicit versioned AAD binding backup/part/slot identities;
- independently exportable recovery-key semantics;
- no WordPress-salt-only recovery root;
- no plaintext or weak-crypto fallback.

Encrypted Backup requires native `ext-sodium` in v1. If unavailable, encrypted Backup is Unsupported rather than downgraded silently.

## Why

Secretstream is designed for authenticated file/stream encryption, provides authenticated chunk processing/finality and avoids building custom nonce/counter logic. Argon2id is the current standards-based password KDF direction. Independent key slots let recovery credentials rotate by re-wrapping the small DEK instead of re-encrypting every large backup artifact.

## Security boundaries

- provider TLS/SSE is additional protection, not replacement for client-side authenticated encryption;
- possession of a recovery key grants decryption authority for backups tied to it;
- key loss can be permanent and is surfaced honestly;
- customer-key escrow by WPE remains OFF unless separately designed/approved;
- exact KDF resource parameters cannot be silently weakened because a host is small.

## Implementation-profile items still pending

- exact binary framing and AAD canonical bytes;
- recovery-kit human encoding/checksum/QR representation;
- accepted Argon2id memory/time floor after host benchmark;
- chunk/part sizing and resume boundaries;
- secure key handling/logging;
- cross-version restore compatibility;
- optional KMS adapter.

See `docs/SECURITY/BACKUP-ENCRYPTION-CRYPTO-PROFILE-CANDIDATE.md`.

All executable crypto/performance work remains prohibited until explicit owner consent under ADR-0014.