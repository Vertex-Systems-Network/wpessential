# WPEssential — P-005 Secrets Vault Executable Evidence Protocol

Status: **Phase 0 planning only / EXECUTION NOT AUTHORIZED**  
Work package: `P0-M00-WP08`  
Related: ADR-0009, ADR-0048, ADR-0085, ADR-0014, Secrets Vault Threat Model, Cryptographic & Key-Hierarchy Profile, PT-C Physical Envelope Profile, Backup/Restore, Connections, Audit, Multisite.

## 1. Purpose

Freeze a bounded, reproducible and adversarial future evidence contract for the WPEssential Secrets Vault before any Vault implementation is treated as production-ready.

This document authorizes **nothing executable**. It defines what must later be proven after explicit owner consent.

The accepted architecture remains:

`Vault Security Domain → VRK Generation → VRK Key Slot(s) → per-secret DEK → immutable Secret Version ciphertext`

with explicit Secret Use Grants for approved network-secret delegation.

Primary target claim is limited to **database-only disclosure resistance when the relevant external/config/recovery wrapping material is not compromised**. WPEssential does not claim secrecy from arbitrary malicious PHP, full server compromise or privileged process-memory inspection.

## 2. Hard invariants

P-005 MUST NOT pass unless all applicable evidence proves:

- no plaintext secret or wrapping key is stored in Vault DB rows;
- no plaintext/blank/weak-crypto fallback exists;
- authenticated decryption failure yields no plaintext;
- per-secret random DEKs and random VRKs are generated through approved secure randomness;
- XChaCha20-Poly1305 IETF AEAD and versioned deterministic AAD are used according to ADR-0048 unless a superseding ADR exists;
- ciphertext, wrapped DEKs and wrapped VRKs cannot be transplanted across secret identity, version, purpose, scope or key generation without authentication failure;
- secret replacement is versioned and cannot destroy the last valid ciphertext on partial failure;
- external/recovery/KMS wrapping-key plaintext never enters ordinary Vault DB/options/logs/export/support data;
- WordPress-derived material wraps a VRK only and cannot silently become direct secret encryption;
- missing/wrong/retired key material fails closed while preserving ciphertext;
- network-secret use requires active explicit Use Grant plus current target-site Policy/Connection authorization;
- generic REST/Abilities/AI/browser surfaces cannot reveal stored plaintext;
- Jobs/Workflow/Definitions carry Vault references, not persisted plaintext credentials;
- clone/staging/restore does not silently activate production provider access;
- normal Backup does not place the only independent recovery/wrapping secret beside ciphertext;
- rotation is resumable and cannot retire the last working recovery path before replacement verification;
- V1/PT-C convenience or performance never overrides scope isolation, anti-swap, recovery or redaction safety.

## 3. Evidence state

Fixtures defined: **VT-01…VT-128**  
Fixtures executed: **0/128**  
Vault runtime certification: **none**  
Vault crypto interoperability certification: **none**  
Final Vault physical topology: **OPEN / evidence-gated**  
V1/PT-C: favored first future baseline only  
V2/PT-E + separate network Vault: mandatory Multisite security/operations comparison

## 4. Required environment record

Every future execution records:

- WPEssential commit/build/artifact hash;
- WordPress/PHP/DB versions and Multisite mode;
- Sodium extension/version/capability evidence;
- Vault crypto/envelope profile version;
- V1 or V2 physical profile;
- key-slot modes enabled;
- object cache / cron / Job runner profile where relevant;
- exact test fixture identities and secret classifications;
- commands/tests executed;
- pass/fail/inconclusive result;
- logs/artifacts with redaction proof;
- cleanup/key-destruction status for synthetic fixtures.

Real production credentials MUST NOT be used for cryptographic evidence fixtures.

---

# 5. Fixed executable fixtures

## Group A — Crypto primitive, randomness and envelope interoperability — VT-01…VT-08

### VT-01 — Sodium capability / exact primitive
Verify required XChaCha20-Poly1305 IETF API exists on every supported P-001 cell selected for Vault support. Missing primitive must fail preflight; no plaintext or unauthenticated compatibility fallback.

### VT-02 — known deterministic interoperability fixture
Using fixed synthetic keys/nonces/plaintext/AAD only in the test fixture, verify stable encryption/decryption outputs and cross-run/profile reproducibility where deterministic test vectors intentionally fix randomness.

### VT-03 — secure random VRK generation
Generate statistically independent synthetic VRKs through approved CSPRNG path; prove key length/profile and that production API cannot accept predictable default material.

### VT-04 — secure random DEK generation
Each new Secret Version receives a fresh DEK; prove two versions/identical plaintext do not share DEK/ciphertext by construction.

### VT-05 — nonce uniqueness path
Verify independent 24-byte nonce generation for secret encryption, DEK wrapping and VRK slot wrapping; force collision-detection/test doubles where applicable without inventing deterministic production nonces.

### VT-06 — unsupported crypto/profile version
Unknown/future envelope or algorithm profile must fail safe/read-only/recovery diagnostics rather than attempting guessed decryption.

### VT-07 — crypto initialization/randomness failure
Injected randomness/crypto-service failure must block save/rotation and preserve prior valid state; no zero key, blank secret or weak fallback.

### VT-08 — binary/Unicode/structured secret round-trip
Verify representative binary, UTF-8 and structured provider credential payloads round-trip exactly without normalization/truncation while metadata remains bounded.

## Group B — AAD anti-swap and tamper resistance — VT-09…VT-16

### VT-09 — ciphertext bit tamper
One-bit ciphertext modification must cause authenticated failure and emit no plaintext.

### VT-10 — authentication tag/ciphertext truncation
Truncation/malformed envelope must fail closed with safe diagnostic classification.

### VT-11 — Secret UUID swap
Copy an otherwise valid encrypted envelope to another Secret Identity; decryption must fail because AAD identity binding differs.

### VT-12 — Secret Version swap
Move ciphertext/wrapped DEK between versions of the same Secret; version/generation AAD mismatch must fail.

### VT-13 — site-scope swap
Copy a site-scoped encrypted version to another site scope; authentication/use must fail before provider action.

### VT-14 — site↔network scope swap
Attempt to reinterpret site secret as network secret and vice versa; authentication/use must fail.

### VT-15 — purpose/provider swap
Reclassify a secret to another provider/purpose while retaining envelope; AAD mismatch must fail.

### VT-16 — VRK/DEK generation metadata swap
Alter generation references or wrapped-DEK association; unwrap/authentication must fail without silently searching unrelated keys.

## Group C — Secret identity/version lifecycle and concurrency — VT-17…VT-24

### VT-17 — create first Secret Version
Create synthetic secret with immutable encrypted version, safe metadata and valid current pointer; DB scan shows no plaintext.

### VT-18 — replace secret creates immutable version
Replacement creates a new DEK/version; old ciphertext remains unchanged according to retention policy until deliberately retired.

### VT-19 — crash before new version commit
Injected failure before complete envelope commit must leave previous current version usable and no half-current state.

### VT-20 — crash after version commit but before pointer advance
New immutable version may exist as non-current/reconcilable; previous current remains authoritative.

### VT-21 — crash after current-pointer compare/update
Recovery must determine exact current version without double-advancing or losing the prior version.

### VT-22 — concurrent replacements
Two authorized concurrent replacement attempts must resolve through optimistic generation/CAS or equivalent deterministic conflict semantics; last-write ambiguity cannot silently discard a valid secret.

### VT-23 — stale admin edit
A page loaded before another secret replacement cannot overwrite current secret metadata/version blindly.

### VT-24 — corrupted current with valid prior version
Runtime must fail closed and surface recovery state; it must not silently downgrade to prior secret for provider mutation unless an explicit authorized recovery procedure selects it.

## Group D — VRK key slots and bootstrap modes — VT-25…VT-32

### VT-25 — external high-entropy slot
Unlock VRK using approved external key representation; only fingerprint/version metadata is stored locally.

### VT-26 — malformed/low-entropy external key input
Invalid encoding/length must be rejected; normal UI must not accept human password material as raw external master key.

### VT-27 — WordPress-derived slot stability
On an unchanged supported environment, exact versioned HKDF profile derives the same wrapping key needed to unlock the intended VRK.

### VT-28 — WordPress secret/salt change
After changing source key material, old derived slot becomes unavailable as expected; another valid slot permits deliberate recovery/rewrap. No guessed fallback.

### VT-29 — independent recovery slot
Synthetic recovery key unlocks VRK on recovery fixture without DB-stored plaintext recovery material.

### VT-30 — add-and-verify new slot before retire
Normal flow cannot retire the only working slot until replacement slot is cryptographically verified.

### VT-31 — wrong slot key / wrong fingerprint
Wrong external/recovery material fails closed and cannot damage stored slot/ciphertext state.

### VT-32 — KMS/HSM bootstrap-cycle guard
If future KMS slot is enabled, prove KMS credentials/bootstrap do not recursively depend on the same unavailable Vault secret; outage/error state is explicit.

## Group E — wrapping-key and VRK rotation — VT-33…VT-40

### VT-33 — wrapping-key rotation only
Re-wrap one VRK under a new external/recovery slot without re-encrypting secret plaintext or changing per-secret ciphertext.

### VT-34 — interrupted slot rotation
Crash between new-slot creation and verification leaves old valid slot usable and new slot safely resumable/removable.

### VT-35 — VRK rotation happy path
Generate new VRK, create verified slot(s), batch re-wrap DEKs, verify required versions, atomically activate new generation, retain old generation during recovery window.

### VT-36 — VRK rotation crash per checkpoint
Inject failure before generation creation, after slot creation, during batches, before activation and after activation; each state must resume/reconcile safely.

### VT-37 — duplicate rotation Job
At-least-once duplicate Job execution must not re-wrap inconsistently, create duplicate active generations or prematurely retire old keys.

### VT-38 — concurrent secret write during VRK rotation
New/replaced Secret Version must bind to a deterministic valid generation and be included/reconciled before old generation retirement.

### VT-39 — rotation verification mismatch
If expected/verified wrapped-version counts differ, activation/retirement gate fails and old recovery path remains.

### VT-40 — retire old VRK generation
Retirement only after full required verification/recovery window; retained historical Secret Versions have explicit decryptability/retention semantics.

## Group F — authorization, Use Grants and server-side use — VT-41…VT-48

### VT-41 — authorized site secret use
Approved Connection/Ability resolves secret reference server-side only after current scope/Policy checks and returns sanitized provider result.

### VT-42 — unauthorized secret UUID use
Possession/guessing of Secret UUID is insufficient; lower-privilege caller gets no plaintext or provider-use capability.

### VT-43 — network secret without Use Grant
Child site cannot use a network-owned secret without active explicit grant.

### VT-44 — network secret with grant but Policy denied
Use Grant cannot bypass target-site capability/Policy/Connection authorization.

### VT-45 — Use Grant revoke race
Revoke while a Job/provider action is queued/starting; current authorization/grant generation must be revalidated at execution boundary so stale authorization does not silently permit new use.

### VT-46 — grant expiry/lifecycle disable
Expired/revoked/suspended-site grant fails closed without decrypting for provider call.

### VT-47 — cross-site enumeration
Site-scoped admin/list/REST surfaces cannot enumerate another site's secret identities/safe labels merely because V1 uses shared tables.

### VT-48 — no generic plaintext Ability
Registry scan proves no ordinary `get_secret_plaintext`-style Ability/API exists; approved provider adapters consume scoped secret handles/server-side resolution instead.

## Group G — browser, REST, AI, Job and Workflow leakage — VT-49…VT-56

### VT-49 — admin write-only field
After save, UI returns `Configured`/safe metadata only and never refills decrypted value into HTML/React state.

### VT-50 — REST read projection
REST/API representations expose no secret ciphertext where unnecessary and never plaintext/wrapping keys; safe metadata is scope-authorized.

### VT-51 — Abilities response projection
Secret-management/test-connection Abilities return sanitized status/error facts only.

### VT-52 — AI/MCP boundary
AI-facing action schemas cannot enumerate/decrypt Vault plaintext; provider credential remains server-side during allowlisted action execution.

### VT-53 — Job payload persistence
Queue/backend/WPE Job rows contain secret references/operation data only; serialized payload/logs contain no plaintext secret.

### VT-54 — Workflow durable state
Run/Step/Wait/Approval records contain secret refs/typed action inputs, not persisted decrypted provider credentials.

### VT-55 — object cache/transient/options scan
After representative secret use, persistent cache/transient/options contain no plaintext credential.

### VT-56 — request-local memory claim truth
Document/verify that implementation minimizes plaintext lifetime but does not falsely claim guaranteed forensic memory erasure in PHP.

## Group H — logging, diagnostics, support and audit redaction — VT-57…VT-64

### VT-57 — normal success logging
Provider success path logs safe IDs/status/correlation only, not Authorization/token/password/body secret.

### VT-58 — exception/error logging
Synthetic provider/client exception containing a secret is redacted before application/debug/support output under supported logging path.

### VT-59 — HTTP header/query redaction
Authorization headers, signed URLs, query tokens and webhook secrets are removed/redacted from diagnostics.

### VT-60 — Audit event
Audit records secret identity/action/actor/scope/result metadata without plaintext/ciphertext disclosure beyond justified safe identifiers.

### VT-61 — support bundle
Generated support/diagnostic package contains no plaintext secrets, external/recovery key material or reusable signed URLs.

### VT-62 — database plaintext scan
After create/use/rotate/error fixtures, search relevant DB tables/options/jobs/log tables for exact synthetic secret values; supported plaintext occurrences must be zero.

### VT-63 — filesystem/log plaintext scan
Search generated app logs/cache/export/support fixture artifacts for exact synthetic secret values; supported plaintext occurrences must be zero.

### VT-64 — redaction collision safety
Redactor must not corrupt unrelated system behavior while still redacting known sensitive fields/values; false-positive policy is documented.

## Group I — Backup, restore, disaster recovery and key loss — VT-65…VT-72

### VT-65 — normal Backup contents
Backup includes encrypted Vault envelopes/wrapped slots according to policy but excludes external/recovery wrapping-key plaintext by default.

### VT-66 — restore with valid independent slot
Fresh recovery environment with intended key material can verify/unlock restored Vault and preserves identity/version/scope provenance.

### VT-67 — restore without usable key
Restored ciphertext remains intact and Vault enters `key_unavailable`/rebind-required; no blank/plaintext fallback or destructive overwrite.

### VT-68 — wrong recovery key
Authentication failure cannot mark Vault healthy/current or mutate ciphertext.

### VT-69 — WordPress-derived slot after salt/config change
Restore where WP-derived slot no longer works remains recoverable only through explicit alternate slot/re-entry path; behavior is truthfully diagnosed.

### VT-70 — lost all VRK-unlocking slots
System states secrets may be cryptographically unrecoverable, preserves encrypted records for deliberate recovery decision and does not expose backdoor key.

### VT-71 — recovery kit replacement
New recovery slot is verified before prior recovery path is retired; one-time recovery material is not retained server-side beyond accepted design.

### VT-72 — Backup/Vault key separation review
Prove selected disaster-recovery procedure does not package the only Vault-unlocking secret alongside ciphertext unless a separately approved independent Backup encryption profile protects that combination.

## Group J — clone, staging, transfer and environment safety — VT-73…VT-80

### VT-73 — DB clone without external key
Ciphertext copies, becomes unavailable/rebind-required and production integrations do not run.

### VT-74 — DB clone with intentionally shared key
Decryption capability is recognized as explicit operator trust decision; staging outbound-use policy still applies independently.

### VT-75 — copied Use Grant on staging
Copied network grant alone cannot activate network secret in a new installation/environment without current installation/scope/lifecycle authorization.

### VT-76 — domain URL change
Changing domain alone neither destroys ciphertext nor silently rebinds security-domain identity; exact accepted scope/install AAD behavior is verified.

### VT-77 — site clone inside Multisite
New site cannot inherit source site's site-owned secret merely by copying rows; explicit clone/rebind policy controls any intended copy.

### VT-78 — site transfer between networks/installations
Transfer cannot reinterpret old Vault security-domain identity. Recovery/rebind is explicit and auditable.

### VT-79 — staging outbound default
Environment policy can block secret-dependent outbound calls while preserving configuration/ciphertext; disabled state does not imply key failure.

### VT-80 — production promotion/rebind
Moving approved config from staging to production requires deliberate credential binding/use-grant state; no hidden copied plaintext.

## Group K — deletion, retention and dependency truth — VT-81…VT-88

### VT-81 — delete referenced secret
Deletion is blocked or requires explicit dependency-resolution plan when active Connection/Definition references exist.

### VT-82 — logical retirement/tombstone
Retired/tombstoned Secret cannot be newly used; history/Audit retains only policy-approved metadata/ciphertext.

### VT-83 — physical purge
Retention purge removes selected encrypted rows according to policy without claiming forensic secure erase from DB pages/backups.

### VT-84 — provider credential revoke vs local delete
External provider revocation and local Vault deletion are separate outcomes; unknown provider result does not cause false local certainty.

### VT-85 — old secret after successful provider rotation
Previous encrypted version retention/retirement follows provider/recovery policy and cannot accidentally become active without explicit recovery action.

### VT-86 — site deletion
Site-owned grants/secrets/dependencies are planned safely; network-owned secret is not deleted merely because one child site used it.

### VT-87 — network secret deletion with active grants
Destructive operation is blocked or requires explicit dependency plan resolving grants/consumers.

### VT-88 — Free/Pro deactivation/expiry
Encrypted Vault data/config is preserved safely; entitlement/deactivation can lock editing/unsafe operations without deleting or exposing secrets.

## Group L — provider/Connection integration and unknown outcomes — VT-89…VT-96

### VT-89 — Test Connection
Server decrypts only after authorization, performs declared safe provider check and returns sanitized result; no secret in response/log.

### VT-90 — OAuth token refresh secret replacement
Refresh/access-token update creates correct Vault version semantics without placing token in Job/Event Inbox/logging state.

### VT-91 — provider key rotation happy path
New provider credential and local Secret Version coordinate through explicit order/reconciliation; old valid local secret is not destroyed prematurely.

### VT-92 — provider mutation timeout after send
Unknown external outcome is recorded/reconciled; Vault does not automatically retry a non-idempotent credential-rotation action as known failure.

### VT-93 — provider rejects new credential
Local current pointer remains/reverts according to explicit safe workflow while previous known-valid encrypted version is preserved.

### VT-94 — Connection disabled/revoked
Disabled Connection cannot use its secret even if Vault itself can decrypt it.

### VT-95 — webhook signing secret use
Inbound verification accesses the correct scoped signing secret server-side; raw webhook/security diagnostics do not expose it.

### VT-96 — Backup destination credential use
Backup adapter uses Vault handle under its own provider certification; generic Connection certification does not imply Backup provider certification.

## Group M — Multisite isolation and network delegation — VT-97…VT-104

### VT-97 — V1 explicit site-scope query
Shared PT-C store returns only authorized site secret metadata/use under current site context.

### VT-98 — wrong-blog context switch
Switching blog/context without target-site authorization cannot access target Vault; current blog ID alone is not security authority.

### VT-99 — Super Admin/network secret management
Network-owned secrets require explicit network authority and remain network-scoped rather than copied into child site by default.

### VT-100 — same UUID/provider IDs across sites
Scope coordinates/AAD prevent collision or cross-site resolution even with deliberately colliding external identifiers.

### VT-101 — network secret shared to multiple sites
Each child site requires independent active Use Grant/current Policy; one site's revoke does not affect unrelated granted site incorrectly.

### VT-102 — noisy/compromised child site
Child site cannot enumerate/use network secrets outside its grants or another site's secrets, including via crafted UUIDs and direct DB-backed API requests.

### VT-103 — archived/suspended site
Secret-dependent provider actions for suspended scope obey lifecycle policy and do not bypass via queued Job/grant.

### VT-104 — 100/1k/10k-site isolation suite
Run deterministic wrong-site/grant/query/lifecycle tests across target network sizes; wrong-site secret-use/plaintext disclosure count must be zero.

## Group N — V1/V2 physical profile, DDL and migration evidence — VT-105…VT-112

### VT-105 — V1 schema constraints/indexes
Prove Secret UUID/scope uniqueness, version linkage, VRK generation linkage, slot/grant constraints and bounded hot queries without indexing ciphertext/plaintext.

### VT-106 — V2 per-site + network Vault provisioning
Measure table provisioning/migration/lifecycle behavior and verify physical isolation does not weaken network-secret delegation/recovery semantics.

### VT-107 — V1→V2 migration
Migration preserves ciphertext bytes, identities, AAD-valid scope provenance, current pointers, generations, slots and grants without plaintext export.

### VT-108 — V2→V1 migration/recovery comparison
Where evaluated, preserve same invariants and reject ambiguous scope mappings.

### VT-109 — schema migration crash
Interrupted Vault schema migration has Expand→Migrate/Backfill→Verify→Contract recovery path; encrypted data is not destructively rewritten without verification.

### VT-110 — unknown future envelope/schema read
Older code fails safe/read-only/degraded on unsupported newer profile rather than corrupting ciphertext.

### VT-111 — restore across schema versions
Backup restore preserves encrypted envelopes and uses explicit migration/verification before declaring secrets usable.

### VT-112 — migration rollback truth
If ciphertext/AAD/schema transform is irreversible, recovery class is declared honestly; no fake rollback claim.

## Group O — scale, concurrency, performance and operational health — VT-113…VT-120

### VT-113 — 100 / 1k / 100k Secret metadata workload
Measure storage, point-read metadata lookup, current-version resolution and admin-list query plans where practical.

### VT-114 — decrypt/use latency
Measure server-side secret-resolution/decrypt overhead under representative provider-use pattern; do not encourage decrypting in generic hot loops.

### VT-115 — rotation throughput
Measure DEK re-wrap throughput, Job batches, DB locks/retries and resumability for representative retained-version counts.

### VT-116 — concurrent provider-use during secret replacement
Reads either resolve a fully valid old or new version according to transaction/generation semantics; never half-written envelope.

### VT-117 — concurrent grant revoke/use
Stress grant-generation/current-Policy check boundary; new provider action after effective revoke must not pass from stale cache.

### VT-118 — object cache present/absent
Authorization/scope correctness is identical; cache never stores plaintext and cache key isolation prevents metadata leakage.

### VT-119 — key service/KMS degradation
If KMS profile exists, timeout/rate-limit/outage causes bounded fail-closed/degraded behavior and does not cascade plaintext fallback.

### VT-120 — diagnostics health model
Health reports distinguish key unavailable, corrupt envelope, slot unavailable, rotation incomplete, permission denied and provider failure without revealing sensitive material.

## Group P — attack simulation, release gate and independent review — VT-121…VT-128

### VT-121 — database-only theft simulation
With DB dump only and external/recovery/config material absent, synthetic secrets are not recoverable as plaintext through documented Vault paths.

### VT-122 — config/filesystem-only theft simulation
Record exact exposure boundary when config wrapping material exists but DB ciphertext does not; do not overclaim protection.

### VT-123 — DB + config/full-server compromise truth
Demonstrate/document that standard Vault cannot claim secrecy from arbitrary PHP/full server compromise; marketing/security docs must state limitation.

### VT-124 — lower-privilege hostile admin/API corpus
Attempt enumeration, direct IDs, forged scopes, bulk endpoints, export/support routes and malformed payloads; no plaintext/cross-scope use.

### VT-125 — XSS/browser exfiltration surface review
Saved plaintext is absent from normal browser bootstrap/DOM/API payloads, reducing browser theft surface after initial authorized entry.

### VT-126 — fuzz/malformed envelope corpus
Truncated/oversized/invalid encodings, nonces, key IDs, AAD metadata and state combinations fail boundedly without warning leakage or destructive mutation.

### VT-127 — independent security review
A reviewer independent from the implementing author/team reviews crypto construction usage, AAD/serialization, key hierarchy, rotation/recovery, redaction and threat claims. Findings are tracked to resolution or accepted risk.

### VT-128 — production-readiness gate
Release claim is blocked unless all mandatory fixture classes pass on supported P-001 environments, no stop-the-line finding remains, security review is complete, recovery runbook is validated and final V1/V2 selection is explicitly recorded by ADR.

---

## 6. Stop-the-line conditions

Immediately classify as **FAIL / STOP-THE-LINE** if any applicable fixture finds:

- plaintext credential/wrapping key in DB, logs, support/export, generic API, browser bootstrap, Job/Workflow state or AI context;
- unauthenticated/weak/plaintext fallback;
- ciphertext/DEK/VRK-slot swapping that still decrypts under wrong identity/scope/purpose;
- missing/wrong key resulting in blank/default secret use rather than fail closed;
- cross-site or ungranted network-secret use;
- lower-privilege caller obtaining plaintext;
- rotation deleting/retiring the last valid recovery path before verification;
- clone/staging automatically calling production provider because copied ciphertext/grant was usable;
- destructive migration/restore corrupting the last valid encrypted version without recovery path;
- key/provider unknown outcome being falsely reported as definite success/failure where reconciliation is required;
- security documentation claiming protection against full arbitrary PHP/server compromise under the standard in-process Vault.

Performance success cannot override any stop-the-line security failure.

## 7. Required future evidence report

After authorized execution, P-005 report must include:

- exact implementation/build/profile versions;
- selected environment matrix and topology;
- VT-01…VT-128 result table (`PASS` / `FAIL` / `INCONCLUSIVE` / `NOT_APPLICABLE` with justification);
- crypto/interoperability fixture artifacts containing synthetic data only;
- DB/log/export/support leakage scan results;
- rotation crash/recovery evidence;
- Backup/Restore/lost-key evidence;
- Multisite wrong-scope/grant evidence;
- V1 vs V2 DDL/query/storage/lifecycle comparison;
- provider/Connection integration evidence where applicable;
- independent security-review provenance/findings;
- known limitations and threat-claim wording;
- final recommendation: accept V1, choose V2, revise architecture, or remain inconclusive.

## 8. Development gate

No Vault table/migration, key generation, encryption/decryption, KDF, recovery kit, key slot, rotation Job, secret provider action, synthetic fixture, benchmark, package installation, runtime test or security attack execution is authorized by this document.

Explicit owner development/executable-spike consent under ADR-0014 remains mandatory.
