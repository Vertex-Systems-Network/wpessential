# WPEssential Backup — Archive Encryption & Key Recovery Model

Status: **Phase 0 planning / no encryption implementation authorized**  
Date: 2026-08-27

## 1. Purpose

A full WordPress backup can contain passwords/tokens in application tables, private uploads, form/chat data, membership records and business data. Transport TLS or provider-side encryption does not replace application-level archive encryption.

This design separates:
- archive encryption;
- destination/server-side encryption;
- Secrets Vault storage;
- WPE configuration export behavior.

## 2. Non-negotiable principles

- use reviewed authenticated-encryption primitives; no ad-hoc cipher/container;
- encryption key and encrypted backup data must not be inseparably stored together in the same backup;
- lost encryption key can make restore impossible; UI must state this before enabling encryption;
- encryption is stream/chunk capable for large backups;
- integrity/authentication failure aborts restore before plaintext is trusted;
- key recovery is designed before encrypted backups are marketed;
- no raw key is logged, uploaded to support or included in normal configuration export;
- provider SSE may be used as defense-in-depth but is not equivalent to client-side archive encryption.

## 3. Candidate cryptographic primitive

Preferred paper candidate for encrypted artifact streams:
- libsodium `secretstream_xchacha20poly1305` family;
- authenticated chunked stream;
- detects corruption/reordering/truncation/modification according to secretstream semantics;
- suitable for arbitrarily large files without loading entire archive into memory.

Exact PHP wrapper/API/version support remains implementation evidence work.

Alternative interoperable container/library can supersede this candidate if it provides stronger portability and a maintained PHP implementation. Do not invent custom cryptography merely to avoid a dependency.

## 4. Envelope model

Each Backup Set may use a random **Data Encryption Key (DEK)** generated specifically for that backup set/artifact family.

The DEK encrypts archive chunks/streams.

The DEK itself is wrapped/protected using one selected recovery mode.

Benefits:
- rotating a master/recovery key can re-wrap DEKs without re-encrypting terabytes of backup content where format supports it;
- different backups do not share one direct data-encryption key;
- compromise can be scoped/recovered more cleanly.

## 5. Encryption modes

### Mode A — No WPE archive encryption
- explicit opt-out state;
- UI explains reliance on destination/transport controls;
- may be acceptable for local protected infrastructure but should not be silently implied secure at rest.

### Mode B — User recovery passphrase
- generate random backup DEK;
- derive wrapping key from passphrase using a reviewed memory-hard KDF candidate such as Argon2id through PHP Sodium;
- random salt and versioned KDF parameters stored in public envelope;
- wrapping key encrypts/wraps DEK;
- passphrase itself is never stored.

Pros:
- portable disaster recovery independent from original site.

Risk:
- lost passphrase means lost backup;
- weak passphrase can be attacked offline, therefore UX and KDF parameters matter.

### Mode C — Site-managed backup recovery key
- independent random backup master/recovery key;
- per-backup DEKs wrapped by that key;
- recovery key stored outside ordinary DB when possible and protected via Secrets/Vault/operator configuration;
- administrator is required/encouraged to export a separate recovery kit.

Critical rule: do **not** store the only recovery key solely inside the same WordPress database/archive it is needed to decrypt.

### Mode D — External KMS/HSM/provider key adapter — future
- DEK wrapping performed by approved external key-management service;
- provider availability/credentials/recovery become explicit dependencies;
- implementation only after demand/security review.

## 6. WordPress salts are not the preferred backup recovery root

Deriving the only archive-recovery key from WordPress salts would make disaster recovery fragile when `wp-config.php` is lost/rotated or when migrating to a new environment.

Therefore:
- salts may participate in local Vault convenience modes elsewhere;
- long-term backup recovery should use an independently recoverable key/passphrase/KMS policy;
- rotating WordPress salts must not silently make old backups undecryptable.

## 7. Public envelope vs encrypted manifest

An encrypted backup still needs minimal metadata to identify/decrypt it.

### Public envelope may contain only:
- format/version;
- backup UUID;
- encryption mode/algorithm/KDF identifiers;
- salt/KDF parameters where applicable;
- secretstream/header/non-secret crypto parameters;
- wrapped DEK;
- ciphertext artifact/chunk identifiers/sizes/checksums;
- key/recovery profile ID;
- creation timestamp/general compatibility marker where necessary.

### Encrypt sensitive inner manifest
Prefer keeping details such as:
- site URLs/paths;
- plugin/theme inventory;
- table/file lists;
- user/business metadata;
inside the encrypted manifest where feasible.

Do not expose unnecessary backup contents merely so a remote storage catalog can list the archive.

## 8. Key wrapping

Exact key-wrap construction remains an implementation decision.

Requirements:
- authenticated encryption;
- explicit algorithm/version metadata;
- random nonce/salt where required;
- associated data binds wrapped DEK to backup UUID/profile to prevent accidental cross-use;
- wrong key/passphrase fails authentication cleanly;
- no silent fallback to plaintext.

## 9. Recovery kit

For site-managed encryption, WPE should offer a **separate recovery kit** concept.

Candidate contents:
- recovery-key material or protected representation;
- key/profile ID;
- backup encryption format versions supported;
- human recovery instructions;
- checksum/fingerprint;
- date/site/account label that is useful but avoids unnecessary secrets.

Rules:
- generated only after high-risk capability/re-auth;
- never emailed/uploaded automatically;
- user is warned to store offline/separately;
- support staff cannot reconstruct a missing customer-controlled key unless a deliberately designed escrow/KMS service exists.

## 10. Key escrow

Default candidate: **no WPE cloud escrow** for customer-controlled backup keys.

Reason:
- escrow increases WPE breach/liability scope;
- weakens the customer's exclusive control expectation;
- requires its own security/compliance service.

If managed escrow is later offered:
- opt-in separate product/security ADR;
- strong key-wrapping/HSM/KMS design;
- recovery identity verification;
- audit and deletion/retention policy;
- clear disclosure that WPE can participate in decryption/recovery.

## 11. Key rotation

### Site/recovery key rotation
Preferred behavior:
- generate/select new KEK/recovery key;
- re-wrap stored DEKs for retained backup sets where possible;
- verify every re-wrap before old key retirement;
- maintain rollback/recovery until migration confirmed;
- do not re-encrypt full archive unless required by format/security event.

### Compromised DEK
If one backup DEK is compromised, that Backup Set should be re-encrypted/recreated or destroyed according to policy.

### Compromised master/recovery key
All backups whose DEKs are wrapped by that key are affected; re-wrap only protects future access if attacker already copied old wrapped DEKs+ciphertext. Incident response may require new backups and pruning old compromised material.

## 12. Restore UX

Restore preflight must identify encryption state before destructive actions.

States:
- key/passphrase available;
- key required;
- wrong key;
- unsupported encryption format;
- corrupted/authentication failure;
- KMS unavailable;
- recovery key permanently unavailable.

No destructive restore begins until the artifact can be authenticated/decrypted enough to validate the trusted inner manifest.

## 13. Password/passphrase UX

If passphrase mode ships:
- allow paste/password-manager usage;
- do not impose arbitrary composition rules that encourage predictable patterns;
- strength guidance/long passphrase recommendation;
- confirmation at creation;
- one-time recovery warning;
- never store passphrase in options, logs, job payloads or browser persistent storage;
- retry/rate UX must avoid leaking sensitive diagnostics while acknowledging offline password guessing is controlled primarily by KDF strength.

Exact KDF parameters must be benchmarked on accepted minimum hosts after owner consent.

## 14. Job/queue handling

Background backup jobs must not serialize raw DEKs/passphrases into ordinary job payload tables.

Candidate patterns:
- worker resolves short-lived Vault/key-handle reference;
- in-memory key only for active encryption/decryption operation;
- key material zeroization best-effort according to PHP/libsodium limits;
- resumable encrypted streams require safe state/chunk strategy that does not persist plaintext keys in generic logs/metadata.

## 15. Destination copies

Encryption is performed before upload where client-side mode is enabled.

All destinations receive ciphertext only.

Provider metadata/tags must not contain secrets.

Remote verification can validate ciphertext digest/size without decryption; deeper restore verification requires key access in controlled certification environment.

## 16. Backup verification tiers and encryption

- V0 Generated: ciphertext created, not verified.
- V1 Local Verified: ciphertext/chunk integrity + decrypt/authentication sample/full policy + manifest verification.
- V2 Remote Verified: remote ciphertext matches expected digest/size and is retrievable.
- V3 Restore Tested: encrypted backup successfully decrypted/restored in certification fixture.

A remote provider saying upload succeeded is not proof that encryption/recovery works.

## 17. Export/import interaction

WPE portable configuration export remains a separate product.

Normal configuration export excludes secrets. A full encrypted backup may contain encrypted Secrets Vault records but must not automatically include the only external Vault/backup recovery key in the same archive.

## 18. Disaster recovery

Recovery documentation must cover:
- fresh server/site with no original DB;
- obtaining WPE restore tooling/version compatible with archive;
- supplying recovery passphrase/key/KMS access;
- verifying outer envelope and encrypted manifest;
- restoring data;
- separately restoring/re-provisioning runtime Secrets Vault keys/integration credentials where appropriate.

A backup is not disaster-recoverable merely because it can be decrypted on the original live site.

## 19. Privacy/security impact

Encrypted backups can still leak metadata from:
- filename/object key;
- sizes;
- timestamps;
- destination account/bucket/folder;
- public envelope.

Use opaque backup IDs/object names by default where practical and minimize public envelope data.

## 20. Evidence required before acceptance of exact crypto profile

After explicit development consent:
- Sodium/secretstream support across accepted PHP matrix;
- multi-GB streaming memory behavior;
- corruption/truncation/reordering detection fixtures;
- wrong password/key behavior;
- Argon2id host-cost benchmark;
- key-wrap/re-wrap fixtures;
- recovery kit restore on fresh environment;
- lost-key UX;
- interrupted/resumed encrypted backup;
- remote provider roundtrip;
- V3 restore test.

No encryption/decryption code or keys were created by this planning document.

## Research basis
- libsodium secretstream provides authenticated streaming/file-encryption properties suitable for large files;
- OWASP key-management guidance emphasizes separation of keys/data, lifecycle/rotation and recoverability for long-lived encrypted data.
