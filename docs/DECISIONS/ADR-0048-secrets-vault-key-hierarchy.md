# ADR-0048 — Secrets Vault Key Hierarchy & Cryptographic Profile

Status: **Accepted security architecture / exact derivation & storage evidence pending**  
Date: 2026-08-27

## Decision

WPEssential Secrets Vault uses a versioned envelope hierarchy:

- random **Vault Root Key (VRK)** per Vault security domain;
- random per-secret **DEK**;
- XChaCha20-Poly1305 IETF AEAD for secret encryption, DEK wrapping and VRK key-slot wrapping;
- VRK key slots for:
  - external high-entropy master wrapping key — preferred;
  - WordPress-secret-derived convenience wrapping key through a versioned HKDF-SHA256 profile;
  - independent recovery key;
  - future KMS/HSM;
- deterministic versioned AAD binding secret/scope/purpose/key generations;
- server-side/write-only secret handling;
- no plaintext/weak fallback.

The WordPress-derived slot wraps the VRK only; secrets are not encrypted directly from WordPress salts. A separate recovery/external slot is recommended before rotating WordPress security keys.

## Why

This preserves DB-only compromise resistance while making host-key/recovery-key rotation cheap and recoverable. Per-secret DEKs isolate records; VRK/key-slot separation avoids re-encrypting every secret just because an environment key changes.

## Security boundaries

- Vault does not claim secrecy against full arbitrary PHP/server compromise;
- lower-privilege UI/REST/AI cannot reveal saved plaintext;
- provider credentials stay server-side;
- DB/config exports exclude secret material by default;
- lost all VRK-unlocking slots can make secrets unrecoverable;
- no cloud escrow unless separately opt-in designed.

## Supersession

This ADR narrows/supersedes the cryptographic/key-hierarchy candidate portion of ADR-0009. ADR-0009 remains relevant as the Phase-0 physical/runtime verification blocker until storage/rotation/redaction/compatibility tests exist.

## Remaining evidence

- exact envelope/AAD byte serialization;
- exact WordPress secret input + HKDF derivation profile;
- external/recovery key encoding;
- VRK/DEK physical schema and indexes;
- key rotation crash/resume;
- DB-only leak test;
- multisite/staging/backup recovery;
- redaction and provider-use tests.

See `docs/SECURITY/SECRETS-VAULT-CRYPTO-PROFILE-CANDIDATE.md`.

All executable crypto/storage work remains prohibited until explicit owner consent under ADR-0014.