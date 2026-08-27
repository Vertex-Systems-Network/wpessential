# WPEssential — Backup Encryption Cryptographic Profile Candidate

Status: **Phase 0 static security design / no implementation authorized**  
Related: ADR-0021, ADR-0033, Backup Manifest/Chunk profile, ADR-0014.

## Goal

Define a portable, streaming, authenticated encryption profile for large WPEssential Backup Sets while preserving disaster recovery after the original WordPress server/database is lost.

## Preferred v1 data-encryption profile

### Backup Set DEK

Each encrypted Backup Set receives a cryptographically random **256-bit DEK**.

The DEK is never derived directly from WordPress salts, account password, site URL or provider credentials.

### Part encryption

Each logical backup part/file stream uses **libsodium `secretstream_xchacha20poly1305`**.

Reasons:
- designed for encrypted streams/files of arbitrary size;
- authenticated chunks;
- explicit final tag detects incomplete/truncated stream;
- transparent nonce management and rekey capability;
- native PHP Sodium support exists in PHP 8.

Each part starts a distinct secretstream with its own generated header while using the Backup Set DEK.

### Additional authenticated data

Every encrypted chunk is bound to non-secret structural context such as:

- profile version;
- backup-set UUID;
- part UUID;
- part type;
- chunk sequence;
- optional immutable manifest generation/fingerprint.

This prevents valid ciphertext chunks from being silently transplanted into a different backup/part/position.

Exact AAD byte serialization must be fixed by an interoperability fixture before implementation.

## Encrypted part framing

Candidate logical frame:

1. profile/magic/version;
2. secretstream header;
3. repeated framed ciphertext chunks with bounded length;
4. final chunk carrying `TAG_FINAL`;
5. no unauthenticated plaintext payload after final tag.

The outer Backup manifest records encrypted-part IDs/sizes/hashes and key-slot references; sensitive file paths/table names may remain in an encrypted inner manifest according to ADR-0033.

## Key slots / DEK wrapping

A Backup Set can have one or more independent **Key Slots**. Any valid key slot can recover the same DEK.

This enables recovery-key rotation/re-wrapping without re-encrypting multi-GB data.

### Slot A — independent recovery key

- random 256-bit recovery/wrapping key generated independently of the site DB;
- DEK wrapped using **XChaCha20-Poly1305 IETF AEAD** with random 192-bit nonce;
- slot AAD binds backup-set UUID, slot UUID/type and profile version;
- recovery key must be exportable outside the site in a recovery kit;
- customer escrow with WPE remains OFF by default.

Human-friendly recovery-key encoding/checksum/QR/export bundle remains an exact-format decision, not an excuse to weaken key entropy.

### Slot B — passphrase

Passphrase mode derives a 256-bit KEK using **Argon2id** with:
- unique random salt per slot;
- parameters stored with the slot;
- explicit profile version;
- no secret passphrase persisted after wrapping.

Current security floor candidate is based on RFC 9106's memory-constrained Argon2id recommendation (64 MiB, multiple passes) and must be benchmarked against the final minimum hosting environment.

If the host cannot safely meet the accepted KDF floor:
- passphrase mode is unavailable/degraded with an actionable diagnostic;
- WPE may recommend independent random recovery-key mode;
- it must **not silently lower KDF parameters to an unsafe value**.

### Slot C — future KMS/HSM

Future adapter wraps the Backup Set DEK using provider-native key service. The KMS key reference/metadata belongs to the slot, while provider credentials remain Vault secrets.

KMS is not required for first release.

## Native Sodium requirement

Encrypted Backup v1 requires **native `ext-sodium`** for:
- secretstream;
- XChaCha20-Poly1305;
- Argon2id password KDF.

No plaintext fallback.

`paragonie/sodium_compat` is not an acceptable replacement for Backup passphrase KDF because it explicitly does not polyfill Sodium password hashing/Argon2 at practical security/performance.

If native Sodium is unavailable, encrypted-backup creation/restore reports Unsupported and remains disabled rather than using weaker home-grown crypto.

## Recovery kit

A site-managed recovery kit candidate contains only what is necessary to recover the wrapping key/profile, for example:
- format version;
- recovery-key material or protected recovery-key representation;
- key ID/fingerprint;
- instructions identifying compatible WPE restore tooling.

It must not contain provider access tokens or the entire WordPress DB.

The UI must make clear:
- whoever holds the recovery key can decrypt applicable backups;
- losing every key slot may make the backup permanently unrecoverable;
- WPE cannot recover customer-only keys unless a future explicit escrow feature was enabled.

## Rotation

### Recovery key rotation

When old and new wrapping credentials are both available:
1. unwrap DEK with an existing valid slot;
2. create new slot under new key/passphrase/KMS;
3. verify new slot can unwrap same DEK;
4. only then retire/delete old slot if requested.

Large encrypted part data does not need re-encryption merely because a wrapping key changes.

### Data-key rotation

A new DEK means re-encryption of data and is used for new Backup Sets by default. Existing sets keep their DEK unless explicitly re-encrypted.

## Integrity layers

- secretstream authenticates encrypted chunk content/order/finality within a stream;
- AEAD authenticates DEK wraps;
- Backup manifest hashes verify stored part bytes/provider integrity;
- provider checksum alone is not encryption authenticity;
- V3 Restore Tested remains end-to-end recovery proof.

## Resume implications

Provider upload can resume at already completed encrypted part/chunk boundaries according to Backup container design.

Secretstream state cannot be reconstructed by pretending an arbitrary mid-stream ciphertext position is a fresh stream. Therefore resumability format must preserve authenticated stream boundaries/checkpoints deliberately rather than seek blindly into ciphertext.

Exact chunk/part sizing remains benchmark-driven.

## Failure rules

Fail closed on:
- bad authentication tag;
- wrong key/header/AAD;
- missing/reordered chunk;
- missing final tag;
- unsupported profile;
- damaged key slot;
- KDF resource failure;
- missing all valid recovery slots.

Never restore unauthenticated partial plaintext after an authentication failure.

## Future executable evidence — NOT AUTHORIZED

- PHP native Sodium secretstream multi-GB fixtures;
- interruption/resume at defined boundaries;
- chunk reorder/truncation/bit-flip;
- wrong AAD/backup/part binding;
- recovery-key wrapping and rotation;
- Argon2id cost/memory benchmarks on minimum hosts;
- fresh server with original WP DB/salts unavailable;
- provider multipart upload/download/corruption;
- cross-version restore profile compatibility;
- secure memory/error/log review.

No encryption code, keys or encrypted test archive has been created.