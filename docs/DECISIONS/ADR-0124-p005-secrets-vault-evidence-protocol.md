# ADR-0124 — P-005 Secrets Vault Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Context

ADR-0048 accepted the WPEssential Vault cryptographic/key hierarchy: random Vault Root Key (VRK), random per-secret DEKs, XChaCha20-Poly1305 IETF AEAD, versioned deterministic AAD and multiple independent VRK key slots for external, WordPress-derived, recovery and future KMS/HSM wrapping profiles. ADR-0085 accepted V1/PT-C as the favored first physical Vault baseline with V2/PT-E plus a separate network Vault as mandatory Multisite comparison.

Those decisions deliberately left executable crypto, exact envelope serialization, derivation details, physical constraints, rotation/recovery, redaction and runtime security unverified. The older generic P-005 spike listed representative cases but did not provide fixed fixture IDs or a sufficiently complete evidence boundary for concurrency, network-secret grants, clone/staging safety, provider unknown outcomes, V1/V2 migration and release security review.

## Decision

Accept `docs/QUALITY/P005-SECRETS-VAULT-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical bounded future P-005 executable evidence contract.

It defines **VT-01…VT-128** covering:

- crypto primitive availability, randomness, nonces and interoperability;
- AAD tamper and anti-swap evidence across Secret/version/purpose/site/network/key generations;
- immutable Secret Version lifecycle and current-pointer concurrency;
- external, WordPress-derived, recovery and future KMS/HSM VRK key-slot behavior;
- wrapping-key and VRK rotation with crash/resume/duplicate-Job/concurrent-write cases;
- server-side authorization, explicit network Use Grants and grant-revoke races;
- browser/REST/Abilities/AI/Job/Workflow no-plaintext boundaries;
- DB/filesystem/log/Audit/support/diagnostic redaction scans;
- Backup/Restore, disaster recovery and lost-key truth;
- clone/staging/transfer/environment safety;
- deletion, retention and provider-vs-local outcome truth;
- Connections/provider integration and unknown outcomes;
- Multisite isolation and network delegation;
- V1/PT-C vs V2/PT-E schema/migration/restore evidence;
- scale, cache, concurrency and operational-health behavior;
- database-only theft/full-server threat-claim validation, fuzzing and independent security review.

## Preserved architecture

This ADR does **not** redesign ADR-0048 or ADR-0085.

Preserved invariants include:

- no plaintext/weak fallback;
- per-secret DEKs wrapped by a scoped VRK;
- VRK key slots separate wrapping-key custody from DB ciphertext;
- WordPress-derived material wraps VRK only;
- authenticated failure yields no plaintext;
- AAD binds identity/version/scope/purpose/generations to prevent row/ciphertext transplantation;
- immutable encrypted Secret Versions and concurrency-safe current-pointer advancement;
- explicit Use Grant plus current target-site Policy/Connection authorization for network-secret delegation;
- no generic plaintext reveal through UI/REST/Abilities/AI;
- Backup does not include the only independent recovery/wrapping key plaintext by default;
- clone/staging fails closed or requires deliberate rebind rather than silently using production credentials;
- full arbitrary PHP/server compromise remains outside the standard Vault secrecy claim.

## Evidence state

At acceptance:

- VT fixtures documented: **128**;
- VT fixtures executed: **0/128**;
- Vault runtime certifications: **0**;
- Vault crypto interoperability certifications: **0**;
- independent security review executed: **NO**;
- final Vault physical topology: **OPEN / evidence-gated**;
- V1/PT-C: favored first future baseline only;
- V2/PT-E + separate network Vault: mandatory comparison.

P-005 remains technically blocked until future authorized execution produces reproducible evidence.

## Selection rule

No topology or implementation may be accepted merely because it is faster or simpler. It fails P-005 if it permits plaintext leakage, weak fallback, successful cross-context ciphertext transplantation, ungranted/cross-site secret use, destructive key rotation, unsafe clone/staging activation, misleading recovery behavior or overclaimed full-server-compromise protection.

Final V1/V2 selection and any exact byte/KDF/DDL decisions requiring executable evidence must be recorded explicitly after results exist; they cannot be inferred from this planning ADR.

## Development gate

This ADR authorizes **no** Vault source code, table/migration, crypto/KDF execution, key/recovery generation, rotation Job, provider call, package/dependency setup, fixture, benchmark, attack simulation or security test.

Explicit owner development/executable-spike consent under ADR-0014 is still required.
