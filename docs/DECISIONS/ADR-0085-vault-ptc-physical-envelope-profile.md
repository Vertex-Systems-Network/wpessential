# ADR-0085 — Vault PT-C Physical Envelope Profile

Status: **Accepted paper physical/security profile / P-005 crypto/runtime evidence pending**  
Date: 2026-08-28

## Context

ADR-0048 accepts the Vault hierarchy of random VRK → per-secret DEKs → external/WordPress-derived/recovery/KMS key slots with no plaintext fallback. The remaining paper gap is the scoped physical mapping for Secret identities/versions, VRK generations, slots and explicit network-secret delegation.

## Decision

Future first/favored physical profile is:
- **V1 — PT-C shared scoped Vault control-plane tables**.

Mandatory Multisite security/operations comparison:
- **V2 — PT-E per-site Vault tables plus a separate network Vault**.

V1 logical stores are:
- Secret Identity/current metadata;
- immutable Secret Versions/ciphertext envelopes;
- VRK Generations metadata;
- VRK Key Slots;
- explicit Secret Use Grants/Bindings for network-secret delegation where enabled.

This ADR accepts physical boundaries and invariants only; it does not authorize final DDL, cryptographic byte formats or key execution.

## Invariants

- each VRK belongs to one explicit site or network Vault Security Domain;
- no plaintext secret/wrapping key is stored in database rows;
- Secret replacements create immutable encrypted versions and only advance the current pointer after valid commit/verification;
- AAD binds secret identity/version/scope/purpose and key generations so row/ciphertext swapping across context fails authentication;
- external/recovery/KMS wrapping key plaintext is never stored in the Vault DB tables;
- network secret use by a child site requires explicit Use Grant plus current target-site Policy/Connection authorization;
- knowing Secret/Grant UUID never grants plaintext/use;
- missing/wrong key fails closed and preserves ciphertext; no plaintext/blank fallback;
- clone/staging does not silently activate production integrations merely because ciphertext/grants were copied;
- normal Backup contains encrypted envelopes/wrapped slots but not the only external/recovery wrapping key plaintext;
- full PHP/server compromise remains outside the standard DB-only secrecy claim.

## Selection gate

A profile is rejected regardless of convenience/performance if plaintext appears in DB/log/API, ciphertext can be swapped across scope/identity, missing key falls back insecurely, rotation can strand the last valid secret without recovery, or network delegation bypasses target-site Policy/lifecycle.

## Evidence still required

After explicit owner consent P-005 must cover:
- XChaCha20-Poly1305/AAD/tamper/wrong-key/wrong-scope vectors;
- exact envelope serialization;
- external key and WordPress-derived HKDF stability/rotation;
- recovery slot and future KMS interoperability;
- Secret replace/current-pointer concurrency;
- resumable VRK rotation crash/recovery;
- Use Grant revoke races;
- DB/API/log/support/AI plaintext scans;
- clone/staging/lost-key/Backup/Restore;
- V1 vs V2 on 100/1k/10k-site networks;
- independent security review.

Executed Vault crypto/physical benchmarks: **0**.

## Development gate

This ADR authorizes no Vault table/migration, key generation, encryption/decryption, KDF, rotation Job, provider action, recovery kit, fixture or benchmark. ADR-0014 explicit owner consent remains required.