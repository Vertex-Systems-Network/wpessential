# WPEssential — Secrets Vault Cryptographic & Key-Hierarchy Profile

Status: **Phase 0 static security design / no implementation authorized**  
Related: ADR-0009, Secrets Vault Threat Model, ADR-0014.

## Goal

Provide DB-only breach resistance for persistent integration secrets without pretending an in-process WordPress plugin can protect secrets from full arbitrary PHP/server compromise.

## Accepted-profile candidate

### Primitive

Use PHP Sodium **XChaCha20-Poly1305 IETF AEAD** with:
- 256-bit keys;
- random 24-byte nonce per encryption/wrap;
- versioned deterministic AAD;
- authenticated failure = no plaintext output.

No unauthenticated encryption and no plaintext fallback.

## Key hierarchy

Instead of deriving every stored secret directly from one site key, use three levels.

### Level 1 — Vault Root Key (VRK)

- random 256-bit key generated once per Vault security domain/site/network scope;
- never stored plaintext in DB;
- used to wrap per-secret DEKs;
- stable across ordinary secret updates;
- can itself be rotated deliberately.

### Level 2 — Per-secret DEK

Each secret record/version gets a random 256-bit DEK.

Flow:
1. generate DEK;
2. encrypt secret under DEK using XChaCha20-Poly1305 + secret-specific AAD;
3. wrap DEK under current VRK with separate nonce/AAD;
4. store encrypted secret + wrapped DEK + metadata.

Updating a credential can generate a new DEK/version rather than reusing old ciphertext state.

### Level 3 — VRK Key Slots

The VRK is wrapped by one or more recovery/access key slots. This makes host-key rotation independent from per-secret encryption.

Supported architecture modes:

#### Slot A — external master wrapping key — preferred
- random 256-bit material supplied through environment/`wp-config.php`-style configuration;
- never in ordinary DB/options;
- wraps VRK with XChaCha20-Poly1305;
- key fingerprint/version only stored locally.

#### Slot B — WordPress-secret-derived convenience key
- derives a 256-bit wrapping key from approved WordPress secret material using a versioned HKDF-SHA256 profile and WPE/site context;
- exact input constants/`wp_salt()` composition and derivation byte format remain evidence-gated;
- DB-only attacker should not have the source secret material in the normal hardened deployment assumption;
- WordPress key/salt rotation can invalidate this slot unless another slot/recovery path exists.

This slot is convenience, not the strongest mode.

#### Slot C — independent recovery key — recommended for recoverability
- random 256-bit recovery wrapping key;
- exported/stored outside DB/server according to administrator policy;
- can unwrap VRK if external/WordPress-derived slot becomes unavailable;
- WPE escrow remains OFF by default.

#### Slot D — future KMS/HSM
Provider key service wraps VRK; provider credentials are themselves bootstrapped outside the protected secret dependency cycle.

## Why VRK + slots

Benefits:
- rotate external/WP-derived/recovery wrapping credentials by re-wrapping one VRK;
- rotate VRK by re-wrapping DEKs, without exposing secret plaintext unnecessarily;
- keep per-secret corruption/authentication isolated;
- allow multiple independent recovery paths without duplicating plaintext secrets;
- make clone/staging key behavior explicit.

## Logical Vault record

Candidate fields:
- secret UUID;
- scope/site/network ID;
- purpose/provider type;
- secret schema/version;
- ciphertext;
- secret nonce;
- wrapped DEK;
- DEK-wrap nonce;
- VRK generation ID;
- created/updated/rotated timestamps;
- safe metadata only.

No plaintext preview. Optional provider-safe identifier/last characters must be explicitly non-sensitive.

## VRK slot record

Candidate:
- slot UUID;
- VRK generation ID;
- slot type (`external`, `wp_derived`, `recovery`, `kms`);
- algorithm/profile version;
- key ID/fingerprint safe metadata;
- nonce;
- wrapped VRK;
- KDF salt/parameters only when required and non-secret;
- created/retired state.

A slot never stores its wrapping key plaintext.

## AAD profile

### Secret ciphertext AAD
Binds:
- profile version;
- secret UUID;
- site/network scope;
- purpose/provider type;
- secret version/generation.

### DEK wrap AAD
Binds:
- profile version;
- secret UUID;
- VRK generation;
- DEK generation.

### VRK slot AAD
Binds:
- profile version;
- Vault scope/install identity;
- VRK generation;
- slot UUID/type.

Exact byte canonicalization is a future interoperability fixture.

## External master key mode

Operational requirements:
- configuration value is high-entropy binary/base64url representation according to final profile;
- no user-chosen password accepted as “external master key” without a KDF design;
- diagnostics show configured key fingerprint/generation, never key;
- config export/backup does not copy it automatically;
- disaster-recovery documentation tells owner how to preserve it.

## WordPress-derived fallback

Use HKDF-SHA256 only to derive a wrapping key from sufficiently secret WordPress key material + stored random derivation salt + WPE context.

Rules:
- no raw direct truncation/hash of one salt string;
- derived key only wraps VRK, not every secret directly;
- UI labels mode as convenience/host-config-dependent;
- warn that rotating WordPress security keys before creating another valid VRK slot can make secrets inaccessible;
- staging clones should default integrations Disabled/Rebind-required unless environment policy explicitly allows production key use.

Exact source-material composition is not Accepted until compatibility tests prove stable behavior across supported WordPress/multisite configurations.

## Recovery key slot

Recovery key is not stored plaintext in DB.

Recommended UX:
- generate once;
- show/download/print recovery kit once with explicit warning;
- allow adding a replacement slot while old slot still valid;
- verify new recovery slot before retiring old one;
- no account-cloud escrow unless future opt-in ADR.

Exact human encoding/checksum/QR format remains open.

## Rotation classes

### Wrapping-key rotation
Re-wrap VRK into new slot; verify; retire old slot.

### VRK rotation
1. unlock old VRK;
2. generate new VRK;
3. unwrap each DEK under old VRK and re-wrap under new VRK in resumable batches;
4. create/verify new VRK slots;
5. mark new generation active;
6. retire old only after full verification.

Secret plaintext need not be re-encrypted just to rotate VRK.

### Secret rotation
Provider credential replacement creates new encrypted secret version/DEK; update references atomically; retire old according to provider/recovery policy.

## Runtime use

- decrypt only server-side at point of approved provider call;
- do not persist decrypted secret in object cache/transients/options;
- request-local memory caching only if necessary and bounded;
- no plaintext in React/REST/Abilities/AI/log/support bundle;
- test-connection Ability receives sanitized result only.

PHP cannot guarantee forensic memory erasure; do not overclaim secure memory handling.

## Clone/staging behavior

If DB clone lacks usable VRK key slot:
- preserve ciphertext;
- mark Vault `key_unavailable`;
- disable secret-dependent connection use;
- allow authorized rebind/recovery;
- never blank/overwrite ciphertext automatically.

If operator intentionally supplies same external/recovery key, decryption is possible; this is an explicit trust decision.

## Backup interaction

A normal DB backup contains encrypted Vault records and wrapped keys, not external/recovery wrapping key material by default.

Disaster recovery must preserve at least one independent VRK-unlocking path.

Do not package the only Vault recovery key next to ciphertext unless the Backup archive itself is separately encrypted under an independent key profile and the operator explicitly chooses that recovery design.

## Failure behavior

Fail closed on:
- no valid VRK slot;
- wrong key/AAD;
- authentication failure;
- unsupported profile;
- partial rotation state without valid active generation.

Preserve ciphertext for recovery; never replace with blank/plaintext fallback.

## Future executable evidence — NOT AUTHORIZED

- XChaCha20-Poly1305 vectors/tamper/AAD-swap;
- exact envelope serialization;
- external key parsing/fingerprints;
- WordPress-derived HKDF source stability;
- salt/security-key rotation;
- recovery-key slot restore;
- VRK/DEK batch rotation crash/resume;
- DB-only dump plaintext scan;
- multisite/network isolation;
- staging clone/rebind;
- backup/fresh-server recovery;
- REST/UI/log/support/AI redaction;
- hundreds/thousands secret performance.

No Vault encryption, keys, DB table or migration has been implemented.