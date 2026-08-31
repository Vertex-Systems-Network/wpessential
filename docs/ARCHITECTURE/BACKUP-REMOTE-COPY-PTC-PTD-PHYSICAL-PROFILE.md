# WPEssential — Backup Remote Copy PT-C/PT-D Physical Profile

Status: **Phase 0 paper architecture / P-013 benchmark profile only / no Backup execution authorized**  
Related: ADR-0021, ADR-0033, ADR-0043, ADR-0053, ADR-0056, ADR-0061, ADR-0064, ADR-0065, ADR-0069, ADR-0071, ADR-0075, Backup Remote Copy Lifecycle, P-013.

## Purpose

Define the first future physical comparison for Backup Set / Remote Copy operational truth without confusing transfer attempts, provider object visibility, commit, verification, deletion or restore certification.

## Ownership boundary

Backup configuration/destination definitions remain control-plane configuration and Vault references.

Backup runtime owns:
- Backup Set recovery-point identity/status;
- Backup Artifact/Part inventory metadata;
- Remote Copy lifecycle per destination;
- Remote Object References;
- transfer/finalize/verify/delete attempts;
- retention/re-verification state;
- restore-source evidence references.

Provider credentials remain in Vault. Provider-native storage is not the local source of truth for WPE lifecycle state.

---

## Physical comparison profiles

### BR1 — PT-D shared scoped Backup runtime metadata — first benchmark baseline

Use shared scoped runtime stores for Backup Sets, Artifact/Part metadata, Remote Copies, Remote Object References and operational attempts where needed.

Why first:
- Remote Object/part/attempt volume can be high;
- one network migration/diagnostic surface;
- Site Backup/Restore can row-scope shared metadata;
- avoids thousands of per-site runtime table families on large networks.

### BR2 — PT-C current Backup Set/Remote Copy control + PT-D part/object/attempt history — mandatory comparison

Keep compact current recovery-point/copy rows in PT-C while high-volume part/object/attempt records use PT-D.

Potential advantage: smaller hot current-copy indexes. Cost: cross-store lifecycle/transaction/migration complexity.

### BR3 — PT-E per-site Backup runtime — large-network physical-isolation comparison

Equivalent runtime stores per site. Required before a final large-network topology decision if BR1/BR2 noisy-neighbor or scoped-delete evidence is weak.

No profile is selected for production by this document.

---

## Scope identity

Every site-owned Backup Set, Artifact, Remote Copy, Object Reference and Attempt carries explicit:
- `network_id`;
- `site_id`;
- stable WPE UUID/internal identity.

Network Backup is a separate explicit scope/profile. A Site Backup cannot infer ownership from table prefix/provider path alone.

Destination/Connection identity is always paired with scope/Backup Set identity before provider object references are resolved.

Provider object key/file ID/version by itself is never a cross-site lookup authority.

---

## Backup Set physical invariants

Candidate fields/invariants:
- internal ID + stable Backup Set UUID;
- scope;
- logical recovery-point type/profile;
- source site/network identity snapshot;
- capture start/finish state/timestamps;
- manifest schema/profile version;
- encryption profile/key-slot reference metadata only;
- artifact counts/bytes summary;
- verification/recovery classification;
- retention/pin/protection class;
- parent Run/Job/Audit correlation;
- created/updated/terminal timestamps;
- state generation.

Hot index families:
- scope + created/recovery-point time;
- scope + state/verification class;
- scope + retention/pin + time;
- scope + Run/correlation;
- purge/retention eligibility.

Backup Set row does not store provider credentials or full file inventory.

---

## Artifact / Part invariants

Logical fields:
- scope + Backup Set;
- artifact/part stable identity;
- type (`manifest`, DB chunk, file/archive part, index/envelope, registered part);
- logical/stored size;
- stored-bytes cryptographic hash/fingerprint;
- encryption/compression/profile version;
- ordinal/range/chunk identity;
- local staging/reference state where applicable;
- retention/availability state.

Hot indexes:
- scope + Backup Set + artifact type/ordinal;
- scope + Backup Set + part UUID;
- hash lookup only where dedupe/integrity workflow proves need.

No broad filesystem path index or plaintext secret metadata.

---

## Remote Copy physical invariants

One Remote Copy represents one Backup Set at one Destination.

Candidate fields:
- Remote Copy UUID/internal ID;
- scope + Backup Set;
- Destination UUID/provider profile version;
- lifecycle state;
- verification level/state;
- provider commit identity safe reference;
- final manifest remote identity/version;
- expected/verified part counts;
- logical/stored bytes;
- active upload/finalize session reference safe metadata;
- started/committed/verified/reverified timestamps;
- last error category;
- retention policy snapshot/reference;
- delete state/timestamps;
- generation/version;
- current active attempt/reference.

Hot indexes:
- scope + Backup Set + Destination unique/logical copy identity;
- scope + state + updated/next-action time;
- scope + verification state + reverify due time;
- scope + Destination + state;
- retention/delete eligibility;
- provider commit/final manifest identity only together with Destination/profile/scope.

`remote_verified` remains stronger than `remote_committed`; object visibility alone never sets it.

---

## Remote Object Reference invariants

One row/reference per provider object/version needed to reconstruct/verify the copy where the provider model requires this detail.

Candidate fields:
- scope + Remote Copy + Artifact/Part;
- provider object stable ID/key/path safe representation;
- provider version/revision/ETag/checksum metadata where semantically valid;
- expected stored hash/size;
- observed size/checksum/time;
- staging/final role;
- object availability/state;
- storage/tier class safe metadata;
- retention/object-lock metadata where known;
- last verified timestamp.

Do not treat provider ETag generically as cryptographic integrity. Provider profile defines meaning.

Hot indexes:
- scope + Remote Copy + part;
- scope + Destination/provider object identity/version;
- scope + state + last verified;
- orphan/staging cleanup eligibility.

A broad prefix/path is never enough proof to delete an object.

---

## Operational Attempt invariants

Transfer/finalize/verify/delete/reconcile operations can require separate append-oriented Attempt records.

Candidate fields:
- Attempt UUID/internal ID;
- scope + Remote Copy;
- operation type;
- ordinal/idempotency identity;
- JobService Job/Attempt correlation;
- started/finished timestamps;
- provider request/operation reference safe metadata;
- result (`known_success`, `known_failure`, `unknown_outcome`, retryable, terminal);
- bytes/parts progress summary;
- error category/retry-after;
- adapter/provider profile version.

Hot indexes:
- scope + Remote Copy + operation + ordinal;
- scope + operation/result + time;
- scope + Job/correlation;
- unknown/reconciliation state + time.

Provider raw response bodies, credentials and pre-authenticated URLs are not retained as generic attempt metadata.

---

## Commit-unknown protocol

A network/process failure around provider Commit Point can yield `commit_unknown`.

Required future behavior:
1. preserve Remote Copy and Attempt identity;
2. do not mark committed/verified;
3. do not blindly create a second final object/upload if provider may have committed;
4. use certified provider status/list/object identity reconciliation where available;
5. if provider cannot prove outcome, retain explicit ambiguous state and offer safe retry/restart policy according to adapter contract;
6. publish/finalize manifest only according to manifest-last and provider profile semantics;
7. verification must still prove required object inventory/hash/manifest relationship before `remote_verified`.

Commit reconciliation and verification are distinct.

---

## Manifest-last physical implications

Final manifest/completion marker is published last under the certified provider profile.

Remote Copy stores the exact final manifest provider identity/version and verified expected object inventory.

Scanner/Restore must reject:
- staging objects without final marker;
- final marker whose required parts are missing/mismatched;
- manifest for another scope/Backup Set/Destination;
- unsupported encryption/schema/profile;
- copied provider folder that lacks valid WPE manifest ownership/integrity.

---

## Delete / prune truth

Delete Attempt result maps to explicit Remote Copy delete state:
- requested;
- moved-to-trash;
- retention-locked;
- delete-unverified;
- deleted-confirmed.

A generic provider success code does not necessarily mean physical erasure.

Before prune:
- pin/protection checked;
- minimum healthy-copy policy checked;
- current restore/reset dependency checked;
- newer backup verification checked;
- object lock/version/trash semantics evaluated;
- deletion Plan/audit correlation created.

Only known-owned provider objects are eligible; no broad prefix delete.

---

## Re-verification

Re-verification mode/depth is explicit:
- metadata presence/size;
- provider checksum where certified;
- sample read;
- full object/part hash;
- full restore certification separately.

UI/runtime stores evidence depth + time. Old V3 restore certification does not prove object still exists today.

---

## Site lifecycle

Site archive does not delete recovery points.

Site deletion uses the Site Lifecycle Coordinator and retention policy:
- identify protected/latest known-good recovery points;
- optionally create/verify final pre-delete recovery point when product policy requests and consented runtime supports it;
- stop new Backup producers/transfers;
- reconcile in-flight/commit-unknown operations;
- retain required Backup metadata/remote copies according to policy;
- clean site-owned PT-D/PT-C/PT-E rows only after remote retention/delete decisions are explicit;
- preserve network-owned Backup Sets/copies separately.

Site deletion never means provider copies were automatically erased.

---

## Backup of Backup metadata

Backup runtime metadata can be included in logical Backup manifest only to the extent needed for recovery, but circular/self-referential queue/attempt noise is minimized.

Disaster recovery needs:
- Backup Set identities;
- manifest/artifact inventory;
- Remote Copy/Destination safe references;
- encryption recovery metadata/key slots according to crypto profile;
- not provider credentials/Vault plaintext.

---

## Restore safety

Restore-source resolution prefers `remote_verified` copies whose current availability/key recovery is valid.

If only degraded/unverified copies exist, Recovery UI can expose explicit degraded recovery path but must not relabel them verified.

Restore validates:
- WPE manifest/profile;
- object inventory/versions;
- stored hashes;
- encryption authentication;
- target scope/remapping;
- semantic post-restore health.

Remote Copy status alone never bypasses actual restore validation.

---

## P-013 future evidence matrix — NOT AUTHORIZED

Topology:
- BR1 PT-D all runtime metadata;
- BR2 PT-C current Backup/Copy + PT-D parts/attempts;
- BR3 PT-E per-site.

Correctness/failure injection:
- upload session crash/restart;
- commit request timeout before/after provider commit;
- local commit after remote success fails;
- duplicate finalize request;
- final manifest publish failure;
- missing/mismatched part;
- checksum disagreement;
- provider object disappears;
- reverify while delete/prune races;
- delete success vs trash/version/delete marker/retention lock;
- only-one-known-good-copy pruning;
- provider credential expiry during long upload/restore;
- site delete with in-flight copy;
- restore from stale local DB but intact remote manifest;
- wrong-site provider object/reference collision.

Scale:
- 1k/10k Backup Sets;
- 100k/1M part/object records where practical;
- multi-destination copies;
- one large site + many small sites;
- retention/reverification sweeps;
- 100/1k/10k-site networks.

Measure:
- current Backup list/detail query latency;
- part/object inventory query plans;
- Attempt/commit reconciliation throughput;
- index/storage growth;
- retention/delete/reverify throughput;
- lock/deadlock/retry behavior;
- scoped Backup extraction/metadata restore cost;
- wrong-site/delete mistakes (must be zero).

Provider C0–C4 certification remains separate from local schema benchmark.

## Selection rule

BR1 is first benchmark baseline; BR2 is mandatory comparison; BR3 is required if physical site isolation/noisy-neighbor evidence warrants it. A profile is rejected if it can mislabel commit/verification/deletion, delete by ambiguous ownership, prune the only known-good copy, or restore another site's/provider object's data through reference collision.

## Development gate

No Backup table/migration, provider upload/download/delete, key use, Job execution, restore, prune, fixture or benchmark is authorized. ADR-0014 explicit owner consent remains required.