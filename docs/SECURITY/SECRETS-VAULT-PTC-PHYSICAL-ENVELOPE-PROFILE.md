# WPEssential — Secrets Vault PT-C Physical Envelope Profile

Status: **Phase 0 paper architecture / P-005 physical profile only / no crypto or DDL authorized**  
Related: ADR-0009, ADR-0048, ADR-0069, ADR-0071, ADR-0075, Secrets Vault Threat Model, Secrets Vault Cryptographic & Key-Hierarchy Profile, P-005.

## Purpose

Map the accepted Vault hierarchy — Vault Root Key (VRK), per-secret DEKs and VRK key slots — onto a future physical control-plane profile without weakening scope isolation, recovery, rotation or write-only secret semantics.

This document does not execute or finally select cryptographic byte formats.

## Security claim boundary

Target threat: database-only disclosure without the external/config/recovery wrapping key.

Vault does not claim secrecy against:
- arbitrary malicious PHP in the WordPress process;
- full server/filesystem/config compromise;
- process-memory inspection by a privileged attacker.

No physical schema can change that in-process WordPress trust boundary.

---

## Physical topology

### V1 — PT-C shared scoped Vault control-plane tables — first/favored benchmark baseline

Use one network/global WPE table family with explicit scope for:
- Secret Identity/current metadata;
- immutable Secret Versions/ciphertext envelopes;
- Vault Root Key Generations metadata;
- VRK Key Slots;
- explicit Secret Use Grants/Bindings for network-secret delegation where enabled.

Why V1 first:
- Vault volume is normally control-plane scale;
- network-scoped secrets and explicit child-site delegation fit one scoped registry;
- one migration/diagnostic path;
- avoids multiplying sensitive table families across large networks;
- encrypted material remains cryptographically separated by scope/VRK/AAD rather than relying only on table isolation.

### V2 — PT-E per-site Vault tables + separate network Vault — mandatory security/operations comparison

Site secrets physically separated into per-site tables while network secrets require a distinct network store.

Potential benefit: stronger accidental-query isolation. Costs: table/migration proliferation, network-secret delegation complexity, site transfer/restore complexity and multiple Vault schema states.

V1 is favored paper topology but V2 remains a required P-005 comparison before final physical selection for Multisite.

---

## Vault security domain

Each VRK belongs to one explicit Vault Security Domain:
- one site scope; or
- explicit network scope.

A site-scoped Secret Version can only reference the active/valid VRK generation for the same site security domain.

A network-scoped secret remains network-owned even when a child site is granted use. Child-site use does not copy/decrypt/re-encrypt the secret into site scope automatically.

Current blog ID alone is never the security-domain key.

---

## Secret Identity store

Purpose: stable write-only secret reference and safe lifecycle/current-version metadata.

Candidate fields/invariants:
- internal numeric ID + stable Secret UUID;
- network/site scope;
- purpose/provider/schema key;
- safe admin label/identifier metadata only;
- lifecycle state (`active`, `rotation_pending`, `disabled`, `key_unavailable`, `retired`, `tombstoned` as finalized by evidence);
- current Secret Version ID;
- generation/version for optimistic concurrency;
- created/updated/rotated timestamps;
- creator/updater actor safe refs where useful;
- retention/classification metadata.

Never stored here:
- plaintext secret;
- decrypted preview;
- wrapping key;
- provider credential payload;
- recoverable token fragment unless provider/product proves it non-sensitive.

Candidate indexes:
- scope + purpose/provider + lifecycle state;
- scope + Secret UUID unique;
- scope + safe connection/reference lookup only where needed;
- lifecycle/retention eligibility.

Do not index ciphertext.

---

## Secret Version store

Each credential replacement/rotation creates a new immutable encrypted version rather than mutating historical ciphertext in place.

Candidate fields:
- internal ID + Secret Version UUID;
- Secret Identity ID + version ordinal;
- scope;
- envelope/profile version;
- secret ciphertext;
- secret nonce;
- wrapped per-secret DEK;
- DEK-wrap nonce;
- VRK generation ID;
- DEK generation/version;
- AAD profile version/fingerprint metadata;
- ciphertext/envelope fingerprint for corruption diagnostics, not authorization;
- created/retired timestamps;
- state (`current`, `retiring`, `retired`, `rotation_failed` etc. evidence-gated).

Invariants:
- immutable ciphertext/wrapped-DEK envelope once committed;
- AAD binds Secret UUID/version/scope/purpose/VRK generation;
- Secret Identity current pointer changes under concurrency-safe update only after new version authenticates/commits;
- failed replacement never blanks or overwrites last valid encrypted version.

Candidate indexes:
- Secret ID + version ordinal unique;
- scope + Secret ID + state/time;
- VRK generation + state for rotation planning.

Ciphertext body stays out of indexes and logs.

---

## VRK Generation store

Stores metadata about a Vault Root Key generation, never plaintext VRK.

Candidate fields:
- VRK generation UUID/internal ID;
- Vault Security Domain scope;
- crypto/profile version;
- state (`preparing`, `active`, `rotation_in_progress`, `retiring`, `retired`, `recovery_required` etc. evidence-gated);
- generation ordinal;
- created/activated/retired timestamps;
- expected/verified Secret Version wrap counts;
- rotation Run/Job/Audit correlation;
- safe integrity/diagnostic metadata.

A scope has exactly one normal active generation after completed rotation, while controlled rotation may temporarily retain old/new valid generations.

No VRK plaintext is stored in this table.

---

## VRK Key Slot store

One VRK generation can have multiple independent slots:
- external master wrapping key;
- WordPress-secret-derived convenience key;
- independent recovery key;
- future KMS/HSM.

Candidate fields:
- Slot UUID/internal ID;
- VRK generation ID + scope;
- slot type;
- algorithm/profile version;
- external key ID/fingerprint safe metadata;
- wrapped VRK ciphertext;
- nonce;
- KDF salt/parameters when required and non-secret;
- state (`pending_verify`, `active`, `retiring`, `retired`, `unavailable`);
- created/verified/retired timestamps;
- Audit correlation.

Never stored:
- external/recovery/KMS wrapping key plaintext;
- raw WordPress salts/secret material.

A new slot is verified before the only working old slot can be retired through normal UI flow.

---

## Secret Use Grant / Binding

Network-scoped secrets need explicit child-site use semantics rather than implicit cross-site lookup.

Candidate grant fields:
- Grant UUID/internal ID;
- network Secret UUID;
- target site ID;
- allowed connection/module/purpose key or registered policy reference;
- state/generation;
- created/revoked timestamps;
- actor/reason safe metadata;
- optional expiry.

Use grant does **not** reveal plaintext and does not itself authorize a user/API call.

Secret use requires:
1. current target-site scope;
2. current Connection/Ability/Policy authorization;
3. active Secret/Version/VRK state;
4. active explicit grant when secret is network-owned and target is a child site;
5. server-side provider action.

Knowing Secret UUID or Grant UUID never grants decryption/use.

Candidate indexes:
- network Secret + target site + state;
- target site + state for lifecycle cleanup;
- target site + module/connection purpose where needed.

---

## AAD and physical anti-swap invariants

Future cryptographic serialization must bind at minimum the accepted stable context:
- envelope profile version;
- Secret UUID;
- Secret Version/generation;
- Vault Security Domain scope;
- purpose/provider type;
- VRK/DEK generation as applicable.

Copying ciphertext/wrapped DEK/slot rows into another Secret/site/network context must fail authentication, not silently decrypt under the wrong identity.

Exact canonical byte encoding remains executable interoperability evidence.

---

## Rotation transaction/journal model

### Secret replacement
Candidate safe flow:
1. authorize replace;
2. create new Secret Version + random DEK;
3. encrypt/wrap under active VRK generation;
4. verify envelope/authentication in bounded local operation;
5. atomically advance Secret Identity current version/generation;
6. retire old provider credential according to provider semantics separately;
7. Audit safe metadata only.

Provider credential rotation and local secret-version commit can be cross-system non-atomic; unknown provider outcomes require provider-specific reconciliation rather than deleting last working local ciphertext prematurely.

### VRK rotation
Requires durable resumable rotation state:
1. unlock old active VRK;
2. generate new VRK generation;
3. create/verify at least one valid new key slot;
4. batch unwrap DEKs with old VRK and re-wrap under new VRK without exposing secret plaintext unnecessarily;
5. mark each version migrated/idempotently checkpointed;
6. verify all required current/retained versions;
7. atomically make new generation active;
8. retain old generation/slots until rollback/recovery window closes;
9. retire old generation deliberately.

Crash at any batch point must resume without mixed-generation data loss.

Exact JobService/rotation journal implementation remains P-005/P-003 evidence.

---

## Runtime decryption/use

Approved provider action resolves Secret reference server-side after current scope/Policy/grant checks.

Rules:
- decrypt only at point of approved use;
- no decrypted value in persistent object cache/transient/options;
- no decrypted value in REST/React/AI/support/Audit/logs;
- request-local memory only as needed and bounded;
- no generic `get_secret_plaintext` Ability for ordinary callers;
- provider action returns sanitized result.

Avoid write amplification: `last_used_at` does not need a DB write for every decryption; bounded operational aggregation/Audit can be used where a real purpose exists.

---

## Clone / staging / transfer

Cloned ciphertext is preserved.

If required VRK slot/key is unavailable or AAD/scope identity no longer matches:
- state becomes `key_unavailable`/rebind-required;
- connection use fails closed;
- ciphertext is not blanked/re-encrypted with guessed material;
- staging outbound providers remain disabled/rebind-controlled according to Environment Policy.

Moving a site/network to a new installation cannot silently change Vault Security Domain identity. Explicit transfer/recovery rebinds scope/key-slot context according to a future certified procedure.

A DB clone must not accidentally give staging use of a network secret merely because it copied a Grant row; installation/environment/lifecycle authorization still applies.

---

## Site lifecycle

Archive/suspend can disable selected secret-dependent outbound use without deleting encrypted material.

Site deletion:
- revoke/delete site-owned Use Grants first;
- identify site-owned Secrets and external dependencies;
- preserve encrypted material for recovery/retention where policy requires;
- avoid deleting network-owned secret because one child site used it;
- purge site-owned encrypted rows only after dependency/retention/recovery Plan;
- Audit only safe secret IDs/operations.

Network secret deletion is blocked while active Use Grants/dependencies remain unless an explicit destructive Plan resolves them.

---

## Backup / Restore

Normal DB Backup can include encrypted Secret Versions, wrapped DEKs and wrapped VRK slots.

It does not include external/recovery wrapping key plaintext by default.

Recovery requires at least one independent valid VRK-unlocking path.

Restore rules:
- preserve Secret/Version/VRK/Slot identities and scope provenance;
- verify envelope/AAD before marking usable;
- missing external key => `key_unavailable`, not plaintext fallback;
- WordPress-derived slot may become unavailable after salt/config change;
- restored Use Grants reauthorize against current site/network lifecycle and Policy;
- current Secret pointer cannot be advanced to an unauthenticated/corrupt version merely because Backup row says current.

Portable config export excludes secret ciphertext by default unless a separately designed secure secret-export profile is explicitly selected later.

---

## Query/index philosophy

Vault is metadata lookup + cryptographic point-read, not analytics.

Optimize:
- Secret UUID/scope lookup;
- purpose/provider/state admin list;
- current version pointer;
- VRK generation rotation scan;
- slot state/generation;
- Use Grants by target site/secret;
- retention/lifecycle dependency checks.

Do not add:
- ciphertext search;
- secret prefix indexes;
- decrypted-value fingerprint used as identity;
- general full-text metadata over sensitive fields.

---

## P-005 future evidence matrix — NOT AUTHORIZED

Cryptographic/interoperability:
- XChaCha20-Poly1305 vectors;
- tamper/AAD row swap/wrong scope/wrong key;
- exact envelope serialization;
- external-key parsing/fingerprint;
- WordPress-derived HKDF stability and salt rotation;
- recovery slot unlock;
- KMS profile if added.

Physical/concurrency:
- Secret replace concurrent edits;
- crash before/after current-pointer advance;
- VRK rotation at each batch checkpoint;
- slot add/verify/retire races;
- corrupted current version with valid prior version;
- Use Grant revoke while provider action starts;
- 100/1k/100k Secrets where practical;
- 100/1k/10k-site network grant/isolation tests;
- V1 vs V2 migration/provisioning/Backup/restore.

Leakage/security:
- DB dump plaintext scan;
- REST/React/log/Audit/support/AI redaction;
- lower-privilege user enumeration;
- wrong-site Secret UUID;
- network secret without grant;
- clone/staging missing/wrong key;
- Backup without external key;
- lost-key fail-closed.

Measure point-read/use latency, rotation throughput, index/storage overhead, lock/retry behavior and any plaintext disclosure (must be zero in supported DB-only/redaction fixtures).

Independent security review is required before production-ready claim.

## Selection rule

V1/PT-C is the favored first physical profile; V2 remains mandatory Multisite security/operations comparison. A profile is rejected regardless of convenience if ciphertext can be swapped across scope/identity, plaintext appears in DB/log/API, missing key falls back insecurely, rotation can strand valid secrets, or network-secret grants bypass target-site Policy/lifecycle.

## Development gate

No Vault table/migration, key generation, encryption/decryption, KDF, rotation Job, provider action, recovery kit, fixture or benchmark is authorized. ADR-0014 explicit owner consent remains required.