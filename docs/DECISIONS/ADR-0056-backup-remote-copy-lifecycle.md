# ADR-0056 — Backup Remote Copy Commit, Retention and Restore Lifecycle

Status: **Accepted architecture / executable provider evidence pending**  
Date: 2026-08-27

## Context

Remote backup storage has multiple partial states. An upload can be incomplete, remotely committed but unverified, moved to trash rather than deleted, versioned despite deletion, or visible while some required parts are missing. A correct restore system must represent these states explicitly.

## Decision

Each Backup Set destination has an independent durable **Remote Copy** lifecycle.

Canonical state progression:

`planned → staging → uploading → uploaded_uncommitted → finalizing → remote_committed → verifying → remote_verified`

Explicit degraded/error states include commit-unknown, integrity-failed, remote-missing, credentials-required, quota/session failures, retention-locked and truthful deletion states.

### Manifest-last invariant

WPE publishes the final manifest/completion marker only after required data parts are uploaded and checked according to provider capability. The final manifest is then read back/verified before the copy becomes `remote_verified`.

Incomplete staging data cannot be enumerated as a normal restorable Backup Set.

### Provider commit point

Every certified provider profile defines an exact commit/finalization point. Network loss around that point becomes `commit_unknown`; WPE queries provider state before blindly repeating finalization.

### Integrity layers

WPE distinguishes:
1. WPE stored-part hash;
2. provider checksum/metadata where reliable;
3. manifest-to-part relationship integrity;
4. encrypted part authentication/decryption;
5. actual restore semantic/health verification.

Passing an upload checksum alone is not V3 restore proof.

### Retention/delete truth

Retention operates on Backup Set/Remote Copy health, not raw remote file age.

WPE does not prune the only known-good recovery point merely because a newer backup exists but is unverified.

Delete states distinguish hard delete, trash/recycle, version/delete-marker retention, provider lock, asynchronous request and unknown outcome. UI cannot claim `Deleted` more strongly than provider semantics justify.

### Restore-source identity

Restore selects a known Remote Copy, retrieves its final WPE manifest by stored provider identity, validates required parts/encryption/recovery material and then begins a restore journal. Arbitrary provider files are not treated as trusted WPE backups solely by filename/location.

## Consequences

- incomplete remote uploads are not mistaken for recovery points;
- provider commit ambiguity is recoverable;
- retention cannot silently erase the last verified copy;
- deletion/versioning semantics stay truthful;
- one Backup Set can fail over to another verified provider copy;
- V0–V3 verification labels remain meaningful.

## Evidence still required

After explicit owner development consent:
- Remote Copy physical schema/indexes;
- provider commit/finalization crash fixtures;
- manifest-last failure injection;
- checksum/read-back tests;
- retention/last-good-copy invariants;
- delete/trash/versioning/object-lock tests;
- orphan cleanup;
- long restore auth refresh;
- encrypted remote restore;
- alternate-copy failover;
- multisite isolation.

No transfer, deletion, retention or restore has been executed.
