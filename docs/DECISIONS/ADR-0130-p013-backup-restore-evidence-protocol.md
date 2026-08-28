# ADR-0130 — P-013 Backup / Restore Artifact / Provider / Recovery Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP13`

## Context

WPEssential Backup architecture is already restore-oriented on paper:

- Backup Set is a logical restore point distinct from artifacts and Destination Copies;
- canonical bundle is manifest-first, multipart and independently verifiable rather than a mandatory single ZIP;
- completion and restore readiness are separate states;
- per-backup DEK + independently recoverable wrapping is required for encrypted backups;
- Sodium secretstream/XChaCha20 and Argon2id are paper crypto candidates under the accepted profile;
- Remote Copy commit/verify/retention/delete/restore state is durable truth distinct from provider API responses;
- provider support is exact family/provider/profile/adapter/environment scoped and uses C0–C4 restore-first certification;
- static SE0–SE3 evidence never grants runtime certification;
- V0 Generated, V1 Local Verified, V2 Remote Verified and V3 Restore Tested are distinct evidence levels;
- H-B1 SHA-256 stored-byte integrity remains distinct from AEAD authentication;
- CMP0 no-compression is fallback, CMP1 gzip first comparison, and ZIP is convenience only.

The generic P-013 spike and provider matrices did not provide one fixed adversarial contract spanning capture consistency, bundle finalization, parser security, crypto recovery, Remote Copy unknown outcomes, provider certification, destructive restore phases, cross-domain reconciliation, Multisite and disaster recovery.

## Decision

Accept `docs/QUALITY/P013-BACKUP-RESTORE-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the fixed P-013 executable evidence contract.

It defines **BK-01…BK-180** covering:

- Backup Set/header/manifest/part identity and finalization truth;
- DB/files capture consistency and volatility boundaries;
- streaming/chunking/compression/large-object behavior;
- hostile parser/archive/path/symlink/bomb/malicious-content handling;
- stored-byte hashes, AEAD, per-backup DEK, passphrase/site-managed recovery and key rotation;
- Remote Copy durable states, retries, reconciliation, retention and delete unknown outcomes;
- provider SE vs C0–C4 certification and exact provider-profile scoping;
- local/browser/FTP/FTPS/SFTP/WebDAV/S3/GCS/Azure/Drive/Graph/Dropbox/Swift/native-family boundaries;
- restore preflight, destructive commit point, maintenance and phase journaling;
- database/files/selective restore and domain/path/prefix migration;
- Free↔Pro/schema/Vault/Membership/protected-file/job reconciliation after restore;
- Multisite/site/network scope, Site Lifecycle, clone and transfer;
- retention/pruning, failed-restore recovery and verified restore-point requirements for Reset/migrations/destructive operations;
- JobService concurrency/backpressure and large-scale profiles;
- privacy/redaction, fresh-server disaster restore and independent final recovery/security review.

## Preserved invariants

1. Generated/uploaded does not mean restore-ready.
2. V2 Remote Verified does not mean V3 Restore Tested.
3. Required capture failures/missing required parts cannot be presented as a fully verified Backup.
4. Provider success responses/checksums do not replace WPE manifest/integrity/restore truth.
5. SE static evidence does not grant C certification.
6. Provider certification is exact provider/profile/adapter/environment scoped.
7. The only recovery key is never stored solely beside/inside the encrypted archive it unlocks.
8. WordPress salts are not the sole preferred long-term disaster-recovery root.
9. Integrity/authentication failure aborts before untrusted plaintext/destructive restore.
10. Restore parser treats bundle as hostile until validated and bounded.
11. Remote delete unknown outcome is not completed deletion.
12. Restore/clone does not silently reactivate production Vault/provider/commercial state.
13. Restore cannot resurrect revoked/expired Membership through stale cache/derived state.
14. Reset/migration/destructive operations cannot claim a restore point merely because a Backup Job started.
15. DB transaction rollback is not universal rollback for combined DB/files restore.

## Evidence state

- BK fixtures documented: **180**
- BK fixtures executed: **0/180**
- P-013 Backup/Restore runtime certifications: **0**
- V3 Restore Tested production-profile certifications: **0**
- planned provider targets: **34**
- provider C-certified: **0**
- provider C3 Supported: **0**
- independent disaster-recovery/security review executed: **NO**

ADR-0084 remains a physical benchmark baseline, ADR-0100 remains an artifact/container profile, and provider static research remains paper evidence only. Final runtime profiles remain evidence-gated.

## Stop-the-line examples

P-013 cannot certify if restore reports success despite silent corruption/missing required data; checksum/AEAD failure is ignored; hostile archive escapes target scope; the only recovery key is colocated with ciphertext; wrong/missing key falls back insecurely; cross-site restore succeeds; clone/restore activates production provider state without revalidation; stale Membership access is resurrected; provider certification is inflated; remote deletion unknown outcome is marked complete; required verified copy is pruned; or a destructive operation proceeds without its required verified restore point.

## Development gate

This ADR authorizes no Backup schema/runtime, archive creation/extraction, DB dump/import, compression/hash/crypto/key operation, provider transfer/delete, Vault/recovery-kit operation, maintenance mode, restore, Site Lifecycle mutation, provider certification, benchmark or destructive test.

ADR-0014 explicit scoped owner consent remains required for every executable P-013 action.