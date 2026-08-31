# WPEssential — Backup Remote Copy Lifecycle

Status: **Phase 0 planning only / no backup execution authorized**  
Related: ADR-0033, ADR-0043, ADR-0053, Backup Provider Certification Contract.

## 1. Purpose

A Backup Set may exist in multiple destinations. Each destination copy has an independent lifecycle and verification state.

WPE must not equate:
- upload started with backup stored;
- all parts written with backup committed;
- provider object visible with integrity verified;
- provider deletion request with data physically gone;
- one valid remote copy with the entire Backup Set healthy.

---

## 2. Entities

### Backup Set
Logical recovery point owned by WPE Backup.

### Backup Artifact/Part
One manifest/database/files/index/envelope part of the Backup Set.

### Destination
Configured provider/profile and namespace.

### Remote Copy
The representation of one Backup Set at one Destination.

### Remote Object Reference
Provider-specific immutable/stable identity where possible:
- object key + version ID;
- Drive file ID;
- Graph DriveItem ID;
- Dropbox path/revision/ID;
- SFTP/WebDAV path + verified metadata;
- provider-native object ID.

Paths/names alone are not treated as strong identity where provider exposes a better stable ID/version.

---

## 3. Remote Copy states

Candidate durable state machine:

### Preparation
- `planned`
- `auth_pending`
- `staging`

### Transfer
- `uploading`
- `upload_paused`
- `upload_retrying`
- `uploaded_uncommitted`

### Commit
- `finalizing`
- `commit_unknown`
- `remote_committed`

### Verification
- `verifying`
- `remote_verified`
- `integrity_failed`

### Degraded/loss
- `credentials_required`
- `provider_unavailable`
- `remote_missing`
- `session_expired`
- `quota_exceeded`
- `retention_locked`
- `deletion_requested`
- `moved_to_trash`
- `delete_unverified`
- `deleted_confirmed`
- `failed_terminal`
- `cancelled`

`remote_verified` is required for V2 status for that copy.

---

## 4. Staging namespace

Where provider permits, remote upload uses a staging/non-final namespace that cannot be mistaken for a completed Backup Set.

Conceptual layout:

- staging prefix/directory/session;
- uploaded parts;
- draft/private manifest metadata;
- final completion marker/manifest published last.

Provider-specific adapters can use native upload sessions instead of visible staging objects.

A scanner/list operation must ignore incomplete staging state as restorable Backup Set unless explicit recovery tooling is inspecting orphaned uploads.

---

## 5. Commit point

Each provider profile declares one exact Commit Point.

Examples:
- multipart completion;
- resumable session final resource;
- block-list commit;
- upload-session finish;
- atomic rename/move from temporary path where proven;
- complete PUT followed by final resource check if no stronger primitive exists.

Commit may be non-idempotent or have unknown outcome. If network failure occurs around commit, state becomes `commit_unknown` until provider state is queried.

Never blindly restart an upload if provider may already have committed the final object.

---

## 6. Manifest-last invariant

Default logical ordering:

1. prepare destination/staging;
2. upload all data parts;
3. verify remote sizes/checksums/identities as capability permits;
4. write/finalize encrypted envelope/key-slot metadata as required;
5. publish final manifest/completion marker last;
6. read back final manifest/marker;
7. verify manifest references every required remote part/version;
8. transition Remote Copy to `remote_verified`.

This prevents a visible final manifest from referencing not-yet-uploaded data under normal flow.

If provider lacks an atomic final publish primitive, capability profile records the weaker window and certification must prove scanner/restore does not accept partial state.

---

## 7. Integrity layers

### Layer A — WPE stored-part integrity
WPE hash over exactly the bytes stored remotely, typically ciphertext when client-side encryption is enabled.

### Layer B — provider transport/object checksum
Provider-native checksum when trustworthy/documented.

### Layer C — manifest relationship integrity
Manifest expects the exact part IDs/names/sizes/digests/profile.

### Layer D — cryptographic decryption/authentication
Encrypted part must authenticate/decrypt under ADR-0043.

### Layer E — restore semantic verification
Restored DB/files/config must pass recovery validation/health checks.

Passing A/B alone is not V3 restore proof.

---

## 8. Remote Copy record

Logical fields:
- remote_copy_uuid;
- backup_set_uuid;
- destination_uuid/profile version;
- state;
- verification level;
- provider commit identity;
- final manifest identity/version;
- expected/verified part counts;
- logical/stored bytes;
- encryption profile;
- upload session refs where active;
- started/committed/verified timestamps;
- last error category;
- last provider request correlation ID safe metadata;
- retention policy snapshot;
- delete state;
- last re-verification timestamp.

Exact physical schema requires later evidence.

---

## 9. Re-verification

Remote copy can be re-verified without restoring it fully.

Modes:
- metadata-only presence/size check;
- provider checksum check;
- sampled range/read candidate;
- full part read/hash;
- full restore test through scheduled/manual V3 process.

UI must label verification depth and time.

A V3 test from months ago is not represented as proof that the remote object still exists now without a current presence/integrity state.

---

## 10. Retention policy snapshot

When Backup Set is created, destination records the retention policy version/snapshot that applied.

Policy concepts:
- keep last N;
- keep days/weeks/months;
- GFS-like daily/weekly/monthly generations;
- pinned/manual protected backup;
- legal/retention lock informational;
- provider lifecycle policy detected/declared;
- minimum number of healthy remote copies before pruning.

Retention engine operates on Backup Set/copy truth, not raw provider folder age alone.

---

## 11. Prune safety

Before pruning a copy:
- ensure it is not pinned/protected;
- evaluate minimum healthy-copy requirement;
- consider latest known successful restore point;
- consider in-progress Reset/Restore dependency;
- consider provider object lock/versioning;
- calculate expected delete semantics;
- create auditable prune plan.

Do not delete the only known-good recovery point merely because a newer backup exists but is unverified.

---

## 12. Delete semantics

Provider profile maps delete outcome to truthful WPE state.

Possible provider semantics:
- hard delete confirmed;
- move to trash/recycle bin;
- create delete marker/version remains;
- object retention lock refuses delete;
- asynchronous deletion accepted;
- unknown due network failure.

WPE UI states:
- deletion_requested;
- moved_to_trash;
- retention_locked;
- delete_unverified;
- deleted_confirmed.

Never show `Deleted` simply because delete request returned a generic success if versioned/retained data still exists according to provider semantics.

WPE cannot guarantee forensic erasure of provider replicas/backups outside provider contract.

---

## 13. Provider lifecycle interference

Provider-side lifecycle rules can delete/archive/tier objects independently of WPE.

Destination profile/config can record known provider lifecycle policy where inspectable, but WPE must not assume it controls it.

Health checks warn when:
- object disappeared earlier than WPE retention expected;
- storage class requires restore/rehydration before read;
- retention lock prevents WPE pruning;
- provider versioning leaves unexpected historical versions;
- quota/lifecycle configuration conflicts with plan.

---

## 14. Restore Source resolution

Restore never accepts arbitrary provider folder/file as trusted Backup Set without WPE manifest validation.

Resolution:
1. select Backup Set;
2. enumerate known healthy Remote Copies;
3. choose Destination/source;
4. retrieve final manifest by stored remote identity;
5. verify manifest/profile compatibility;
6. verify required part inventory;
7. verify encryption recovery availability;
8. estimate staging/disk/network requirements;
9. obtain current provider read authorization;
10. begin restore journal.

If one copy is degraded, UI can offer another verified destination for same Backup Set.

---

## 15. Restore reads

Provider profile declares:
- full download;
- range/resumable download;
- object version selection;
- archived-object rehydration;
- signed/preauthenticated download URL handling;
- auth refresh during long read;
- checksum headers;
- throttling/rate limit.

Preauthenticated URLs remain secret and never become persistent backup metadata/logs.

Downloaded parts re-verify WPE manifest hash before use.

---

## 16. Multi-destination quorum concepts

Optional future policy can require:
- at least one local + one remote verified copy;
- at least N remote providers;
- geographic/provider diversity labels;
- at least one C4-certified destination.

This is a policy/health concept, not distributed-consensus quorum.

WPE does not claim durability percentages without provider/SLA evidence.

---

## 17. Copy repair / replication

Future copy-repair can create a new destination copy from:
- verified local staging artifact;
- another verified remote copy;
- regenerated Backup Set only if source site still matches intended recovery point semantics.

Copy repair produces a new Remote Copy record and runs full destination verification.

Do not mark missing provider object healthy merely by updating DB metadata.

---

## 18. Orphan cleanup

Orphans include:
- abandoned multipart uploads;
- expired Drive/Graph/Dropbox sessions;
- staging files without final manifest;
- temporary SFTP/WebDAV objects;
- final parts from a failed Backup Set.

Cleanup is conservative and identity-bound.

Rules:
- never delete by broad prefix without ownership proof;
- age threshold + Backup Run/Copy reference;
- provider list/abort semantics;
- dry-run preview for manual cleanup;
- audit deletions;
- provider costs of abandoned multipart/uploads surfaced where relevant.

---

## 19. Failure states and recovery

Examples:
- auth revoked → preserve copy metadata, request reauthorization;
- session expired before commit → restart affected staged part safely;
- manifest upload failed → data parts remain staging/orphan until resume/cleanup;
- commit outcome unknown → query provider identity/state;
- checksum mismatch → quarantine/integrity_failed, never mark verified;
- provider lost object → remote_missing, do not rewrite history;
- retention lock → retain and communicate provider-enforced condition;
- deleted local metadata but provider copy exists → recovery/discovery tool may import only after manifest ownership/integrity verification.

---

## 20. Future evidence — NOT AUTHORIZED

After explicit development consent:
- provider commit crash windows;
- manifest-last failure injection;
- remote-copy state persistence;
- checksum/read-back;
- delete/trash/versioning/object-lock profiles;
- retention pruning with only-one-good-copy invariant;
- expired upload sessions/orphans;
- long restore credential refresh;
- encrypted remote restore;
- alternate-copy failover;
- archived/tiered storage restore;
- multisite copy isolation.

No Backup transfer, restore, prune or provider API has been executed.
