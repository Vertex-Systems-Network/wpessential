# WPEssential — Secrets Vault Threat Model and Candidate Design

Status: **Phase 0 security design / Proposed / no cryptographic storage implemented**  
Related: ADR-0009, Connections, Backup, Membership billing adapters, Email/provider APIs

## 1. Goal

Store operational secrets used by WPEssential integrations without:
- plaintext database storage;
- exposing credentials to React/browser bootstrap;
- returning secrets through generic REST/Abilities;
- copying secrets into logs, exports or support bundles;
- pretending encryption protects against a fully compromised WordPress server.

Examples:
- API keys;
- OAuth refresh/access tokens;
- S3 secret keys;
- webhook signing secrets;
- SMTP/provider credentials;
- backup destination passwords;
- billing integration secrets.

Passwords for WordPress users are not Vault data; WordPress owns user authentication.

---

# 2. Threat model

## Threat A — database-only disclosure

Attacker obtains database dump but not `wp-config.php`/environment secrets.

**Vault should materially protect plaintext secrets.**

## Threat B — backup/export disclosure

Attacker obtains WPEssential config export or ordinary site-data export.

**Secrets excluded by default.** Encrypted secret blobs alone should not become portable plaintext.

## Threat C — lower-privilege WordPress user

User can access some WPEssential admin screen/API but lacks secret-management permission.

**Must not read/reveal secrets.**

## Threat D — browser XSS / frontend source inspection

A compromised/hostile browser can inspect scripts/network requests.

**Secrets never sent to browser except explicit one-time inputs entered by authorized admin; saved values are write-only thereafter by default.**

## Threat E — malicious/compromised plugin with arbitrary PHP execution

Another plugin already executes arbitrary PHP under WordPress process.

Vault cannot guarantee secrecy if attacker can call decryption code/read process configuration. Defense is limited to permission/API minimization and auditing.

## Threat F — full server/filesystem compromise

Attacker reads database, code, `wp-config.php`, environment and process memory.

**Vault does not claim protection.** This is outside realistic in-process WordPress encryption isolation.

## Threat G — accidental logging/support disclosure

Debug logs, exceptions, support bundles or webhook logs leak token values.

**Redaction/write-only handling required.**

## Threat H — site clone/staging

Production DB copied to staging without intended production credentials/key.

Secrets must fail closed or require explicit rebind rather than silently exposing/using production integrations.

---

# 3. Security claims WPEssential may make

Allowed claim after verification:

> WPEssential encrypts stored integration secrets at rest and supports keeping the encryption key outside the WordPress database.

Do **not** claim:
- protection from full server compromise;
- HSM-grade isolation without HSM/KMS integration;
- end-to-end encryption when server decrypts credentials to call providers;
- portable encrypted backup safety without a separate export-key design.

---

# 4. Key-mode tiers

## Mode A — Hardened external master key — preferred

Administrator/host provides a high-entropy Vault master key outside DB, e.g. environment or `wp-config.php` constant.

Candidate concept name:
- `WPESSENTIAL_VAULT_KEY`

Exact name/format waits for ADR acceptance.

Properties:
- 256-bit random material;
- not generated/stored in `wp_options`;
- not exposed to browser;
- not included in normal backups/config exports;
- key fingerprint/version can be stored, never raw key.

This is the strongest standard mode for DB-only compromise resistance.

## Mode B — WordPress-key-derived fallback — convenience mode

If explicit key absent, WPEssential may derive Vault key material from WordPress secret keys/salts plus WPEssential/site context.

Benefits:
- still separates key material from ordinary DB-only secret blobs in typical setup;
- zero extra host configuration.

Risks:
- WordPress security keys/salts can be rotated;
- rotating them without Vault re-key flow can make secrets undecryptable;
- `wp_salt()` also incorporates WordPress-managed key material, so exact derivation must be reviewed carefully;
- not as operationally explicit/recoverable as a dedicated key.

UI must label this mode accurately and warn before salt/key rotation/migration operations where detectable.

## Mode C — no usable key/crypto

Do **not** store integration secrets plaintext as fallback.

Options:
- block saving secret-dependent connection;
- require external key configuration;
- allow only integrations that do not require persistent secrets.

---

# 5. Candidate cryptographic primitive

PHP Sodium provides XChaCha20-Poly1305 authenticated encryption and documents it as the preferred provided AEAD mode.

Candidate primitive:
- `sodium_crypto_aead_xchacha20poly1305_ietf_encrypt()` / decrypt;
- random 24-byte nonce per encryption;
- 256-bit keys;
- authenticated additional data (AAD) binding ciphertext to WPEssential context.

Why AEAD:
- confidentiality + integrity/authentication;
- tampered ciphertext fails verification;
- AAD can prevent blob swapping across secret IDs/purposes.

Do not invent custom crypto construction.

Fallback to OpenSSL or another primitive requires a separate compatibility/security decision if Sodium availability becomes a blocker.

---

# 6. Candidate envelope model

For easier master-key rotation, prefer envelope-like storage rather than encrypting every long-lived secret directly with one master key forever.

## Master Key / KEK

Key Encryption Key derived/provided from external Vault key mode.

## Per-secret DEK

Each secret record receives a random Data Encryption Key.

Flow:
1. generate random DEK;
2. encrypt secret plaintext with DEK using AEAD + secret-specific nonce/AAD;
3. wrap/encrypt DEK using current KEK + separate nonce/AAD;
4. store ciphertext + wrapped DEK + nonces + algorithm/key-version metadata;
5. erase plaintext/intermediate key variables where practical for PHP lifecycle.

Benefit:
- master key rotation can re-wrap DEKs without re-encrypting large secret values/provider payloads;
- versioned keys can coexist during controlled rotation.

Whether this complexity is justified must be proven in security prototype/review before Accepted.

---

# 7. AAD binding

Candidate authenticated context includes non-secret stable identifiers such as:
- Vault record UUID;
- site/blog/network scope;
- secret purpose/provider type;
- key version;
- algorithm/version marker.

Goal: copying a ciphertext/wrapped key into another record/context should fail authentication rather than decrypt as a valid different secret.

Exact canonical AAD serialization must be versioned and deterministic.

---

# 8. Candidate Vault record

Non-DDL logical fields:

| Field | Purpose |
|---|---|
| `uuid` | stable Vault secret reference |
| `label` | safe admin label |
| `purpose` | connection/provider purpose |
| `algorithm_version` | crypto format version |
| `key_version` | master KEK generation |
| `secret_nonce` | AEAD nonce for ciphertext |
| `ciphertext` | encrypted secret |
| `dek_nonce` | nonce for wrapped DEK |
| `wrapped_dek` | encrypted per-secret DEK |
| `created_at` | UTC |
| `updated_at` | UTC |
| `rotated_at` | optional |
| `created_by` | actor where applicable |
| `last_used_at` | optional non-secret operational metadata |

Do not store plaintext preview/prefix unless provider/product explicitly needs a safe identifier (e.g. last 4 characters) and it is classified non-sensitive.

---

# 9. Secret references

Definitions store only Vault references, e.g. secret UUID, not plaintext.

Examples:
- S3 connection Definition → access-key secret ref + secret-key ref;
- webhook connection → signing-secret ref;
- SMTP provider → password/token ref.

Import/export can preserve the reference identity while excluding secret material.

---

# 10. UI behavior

Saved secret field is **write-only by default**.

Display:
- `Configured`;
- last updated;
- optional safe provider identity;
- Replace/Rotate/Delete buttons.

Do not refill HTML input with decrypted value.

Reveal feature:
- not available by default;
- if ever added, requires dedicated capability + re-auth + audit + short-lived response and still increases browser/XSS risk.

Recommended v1: **no reveal after save**.

---

# 11. Permissions

Separate capabilities conceptually:
- manage connection configuration;
- replace secret;
- delete secret;
- test/use connection;
- rotate Vault keys;
- inspect Vault diagnostics.

Most operators should never need plaintext read ability because normal use happens server-side.

A `Test connection` action decrypts on server, calls provider and returns sanitized result only.

---

# 12. REST / Abilities / AI

Generic APIs never return secret values.

Allowed operations:
- secret exists/configured? boolean;
- metadata safe fields;
- replace via sensitive write input;
- delete/reference check;
- test connection through typed Ability;
- rotate under high privilege.

AI/MCP:
- cannot enumerate/decrypt secret values;
- may invoke allowlisted connection test/action where its principal has permission;
- model does not receive provider tokens unless an explicitly designed external call mechanism absolutely requires server-side use, in which case token remains server-side.

---

# 13. Logging/redaction

Central redaction contract must cover:
- known secret values during request lifetime where feasible;
- field names (`password`, `secret`, `token`, `authorization`, etc.);
- Authorization headers;
- signed URLs/query tokens;
- provider webhook secrets;
- OAuth refresh/access tokens.

Provider raw request/response logging defaults to metadata, status and correlation IDs—not full bodies/headers.

Errors shown to users never include decrypted credentials.

---

# 14. Backup/export

## Normal WPEssential configuration export

Secrets excluded by default.

Package stores:
- connection definition;
- secret reference placeholder;
- `requires_rebind: true` where secret not included.

## Full site backup

Encrypted Vault database blobs may naturally be part of DB backup.

Restore works only if the corresponding external/WordPress-derived Vault key material is available.

Backup Manager must warn if a hardened external Vault key is not part of disaster-recovery procedure.

Do not automatically place the Vault master key inside the same backup archive as encrypted secrets; that destroys DB/archive separation unless the archive itself has a separately protected encryption model.

## Portable secret export

Deferred. If added later, use a separate user-provided export encryption key/passphrase/KDF design and dedicated threat model.

---

# 15. Site clone/staging behavior

On cloned DB:

### Same external Vault key intentionally provided
Secrets can decrypt; this is an operator decision.

### External key absent/different
Vault records become `key_unavailable` / connection needs rebind.

Do not:
- delete ciphertext;
- overwrite secret with blank;
- fallback to plaintext;
- spam production providers with failed attempts.

Environment mode can optionally disable outbound connections by default on staging after separate Environment Policy design.

---

# 16. Key rotation

Candidate hardened flow:

1. add new master key version;
2. verify old + new key access;
3. re-wrap each DEK under new KEK in bounded background chunks;
4. update key version atomically per record;
5. verify sample/full integrity according to plan;
6. mark old key retire-ready;
7. administrator removes old key only after completion/backup validation.

During rotation:
- both authorized key versions may be needed;
- rotation is resumable;
- audit progress/failures;
- no plaintext export.

WordPress-salt fallback rotation requires special migration because old derived key may disappear when config keys are changed.

---

# 17. Lost key behavior

Cryptographically correct encryption means a lost key may make ciphertext unrecoverable.

WPEssential must state this clearly.

If key is lost:
- mark Vault unavailable;
- preserve encrypted records;
- integrations fail closed;
- administrator re-enters provider credentials into new records/rotation path;
- no fake backdoor/recovery key stored next to ciphertext.

Disaster recovery documentation must include external key backup/storage responsibility.

---

# 18. Secret deletion

Before deleting Vault record:
- show `Used by` connections/definitions;
- block or require dependency resolution;
- audit action;
- remove/overwrite application reference logically.

Secure physical erasure from database/storage pages cannot be absolutely guaranteed by SQL delete due DB/filesystem backups/copies; documentation should not claim forensic secure erase.

---

# 19. Multisite

Default: site-scoped Vault records/permissions unless network-level connection explicitly exists.

Network secrets require:
- Super Admin/network capability;
- explicit sharing to child sites;
- no accidental cross-blog lookup;
- AAD binding includes scope.

Exact schema remains future decision.

---

# 20. Required future prototype/review — NOT AUTHORIZED

After explicit owner consent:
- verify Sodium availability on supported PHP matrix;
- implement isolated non-production encrypt/decrypt format fixture;
- tamper test;
- AAD swap test;
- wrong-key test;
- key rotation/re-wrap test;
- WordPress-salt fallback rotation scenario;
- site clone missing-key behavior;
- DB dump demonstrates no plaintext;
- REST/admin/diagnostic redaction tests;
- backup/restore key availability test;
- performance for hundreds/thousands of secrets (not hot-loop decrypt per request).

Independent security review is strongly recommended before declaring Vault production-ready.

ADR-0014 prohibits executing this prototype before owner consent.

---

# 21. Current recommendation

Preferred architecture candidate:
- external 256-bit master key outside DB;
- WordPress-key-derived convenience fallback with explicit recovery warning;
- no plaintext fallback;
- Sodium XChaCha20-Poly1305 AEAD candidate;
- versioned envelope/per-secret DEK model;
- write-only secret UI;
- server-side use only;
- secrets excluded from ordinary exports/support/AI;
- explicit key rotation and fail-closed lost-key behavior.

This remains **Proposed** until prototype + security review evidence exists.
