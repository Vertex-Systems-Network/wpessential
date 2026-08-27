# WPEssential — Backup Provider Certification Evidence Protocol

Status: **Planning only / NOT AUTHORIZED FOR EXECUTION**  
Extends: P-013 in `CONSENT-GATED-TECHNICAL-SPIKE-PROTOCOLS.md`  
Related: ADR-0053, ADR-0056, Backup Provider Certification Contract.

## 1. Purpose

Define the exact evidence categories required before a Backup provider/profile can move from Planned to C1/C2/C3/C4.

This file defines future tests only. It does not authorize credentials, API calls, provider accounts, SDK installation, uploads, restores or benchmarks.

---

## 2. Test profile identity

Every certification run records:
- WPE version/commit;
- adapter version;
- provider name;
- provider API/profile version;
- region/cloud variant;
- auth method/scopes;
- PHP/WP/DB test matrix identity;
- transport library versions;
- encryption on/off profile;
- object/file size classes tested;
- date/time;
- known provider limits at test date;
- test account/storage policy;
- fixture generator version.

A provider certification cannot be silently reused for a materially different API/library profile.

---

## 3. Fixture size classes

Exact byte values are chosen during implementation based on provider boundaries and minimum-host resources, but every applicable profile covers:
- empty/minimal artifact;
- small single-request artifact;
- just below multipart/resumable threshold;
- just above threshold;
- multi-part/chunk medium artifact;
- large artifact representing realistic site backup;
- boundary part/chunk sizes;
- provider maximum-limit behavior when practical/safe without creating unreasonable cost.

No universal giant test size is hardcoded in planning.

---

## 4. C0 — Detected / Connectable gate

Required pass evidence:
- adapter/profile identified;
- configuration schema validates;
- trusted endpoint/host policy evaluates correctly;
- credentials can be validated without destructive write;
- target bucket/container/folder/path resolves;
- safe provider/account identity shown;
- insufficient permissions detected;
- secrets absent from logs/errors;
- disconnect/revoke semantics documented.

Failures that block C0:
- certificate/host-key bypass required;
- secrets exposed;
- arbitrary redirect/host accepted;
- provider cannot be reliably identified/configured.

---

## 5. C1 — Upload Certified gate

Includes C0 plus:
- upload small WPE artifact;
- final provider identity captured;
- final remote byte size correct;
- object/file can be listed/head/read;
- WPE hash verifies after download/read-back;
- delete cleanup behaves according to profile;
- overwrite/conflict mode deterministic;
- Unicode/long safe names fixture;
- auth expiry/retry behavior for basic operation;
- quota/permission errors normalized.

C1 does not imply resumability or full-site restore.

---

## 6. C2 — Resumable & Integrity gate

Where provider supports resumable/multipart:
- interrupt after at least two non-final parts/chunks;
- resume in a new PHP request/process;
- local last-offset corruption does not cause duplicate/corrupt remote data because provider state is queried;
- part retry;
- out-of-order/parallel parts if profile claims it;
- session/upload ID expiry;
- orphan abort/cleanup;
- process crash before finalization;
- process crash during/after finalization producing `commit_unknown` then reconciliation;
- provider rate-limit response/backoff;
- provider 5xx/timeout;
- WPE stored-part hash;
- provider checksum interpretation when available;
- manifest-last invariant;
- corrupt remote bytes detected;
- missing part detected.

For a provider family without trustworthy resumability:
- certification label becomes **C2 Integrity Certified / Non-resumable**;
- safe restart behavior tested;
- UI/documentation exposes limitation;
- no hidden “resume” claim.

---

## 7. C3 — Restore Certified gate

Includes applicable C0–C2 evidence plus an actual restore-oriented fixture:
- produce a complete Backup Set;
- upload to provider;
- remove/ignore local source artifacts so restore truly reads remote copy;
- enumerate selected Remote Copy through final manifest identity;
- preflight all parts;
- download with interruption/retry;
- refresh credentials during long operation where applicable;
- verify every downloaded part before use;
- decrypt/authenticate encrypted profile where enabled;
- feed artifacts into certified restore path;
- restore selected/full scope according to test;
- run post-restore health checks;
- compare expected fixture truth;
- missing/corrupt remote part fails safely;
- restore cancellation/resume/recovery semantics tested;
- provider delete/retention behavior tested after fixture cleanup.

C3 is the minimum normal `Supported Backup Destination` gate under ADR-0053.

---

## 8. C4 — Disaster Restore Certified gate

Includes C3 plus fresh-environment recovery:
- original WordPress runtime state is unavailable to restore process except documented recovery material;
- clean/fresh supported WordPress/PHP/DB environment;
- provider connection is reconstructed according to documented disaster recovery procedure;
- Backup encryption recovery kit/passphrase/key slot available independently when encryption profile requires;
- remote Backup Set discovered/selected without relying on stale local runtime DB records unless recovery catalog export is an explicit documented prerequisite;
- full restore performed;
- domain/path/environment mapping exercised where in scope;
- WPE/Free/Pro compatibility safety evaluated;
- post-restore health and representative application behavior verified;
- recovery documentation followed by a fresh operator/automation fixture rather than relying on hidden tester knowledge.

C4 is the strongest provider recovery claim; it does not create a provider SLA/durability guarantee.

---

## 9. Authentication fault matrix

Applicable provider profiles test:
- wrong credential;
- revoked credential;
- expired access token;
- refresh token expired/revoked;
- insufficient OAuth scope;
- read-only credential for write operation;
- delete permission missing;
- list permission missing;
- provider account disabled;
- SSH host key changed;
- TLS certificate invalid;
- tenant/site/drive no longer accessible;
- credential rotated while upload in progress.

No test may disable certificate/host-key verification merely to pass.

---

## 10. Network/failure injection matrix

Applicable operations inject failure:
- DNS failure;
- connection timeout;
- read timeout;
- connection reset;
- partial request body;
- response lost after request may have succeeded;
- provider 429;
- provider 500/502/503/504;
- local PHP process terminated;
- Job claimed then worker dies;
- object storage session expires;
- local temp disk full;
- provider quota full.

Expected result is defined per operation as retry/reconcile/terminal—not generic retry everything.

---

## 11. Finalization ambiguity tests

For every provider with explicit commit:
1. commit request definitely not sent;
2. provider returns success;
3. provider returns terminal failure;
4. connection lost before response and commit may have succeeded;
5. duplicate finalize request;
6. final resource exists but local state not updated;
7. local state says committed but remote object absent/corrupt.

Adapter must reconcile using provider state before deciding final Remote Copy state.

---

## 12. Integrity fault matrix

Inject/detect:
- one bit/byte changed in remote artifact where feasible;
- wrong remote object size;
- missing part;
- duplicate/wrong part identity;
- stale manifest;
- manifest points to older object version;
- provider ETag semantics misunderstood fixture;
- encrypted stream truncated/reordered;
- wrong recovery key;
- wrong provider object returned under same display filename.

Integrity failure never downgrades to warning when required bytes cannot be trusted.

---

## 13. Retention/delete matrix

Test where provider supports:
- ordinary hard delete;
- trash/recycle behavior;
- versioning/delete markers;
- object lock/retention denial;
- async delete;
- delete outcome unknown;
- provider lifecycle removes object externally;
- pruning when newer backup unverified;
- pruning while only one healthy verified copy exists;
- pinned/protected restore point;
- concurrent restore using copy selected for prune.

Safety invariant: retention does not knowingly remove the last required verified recovery copy.

---

## 14. Security tests

- custom endpoint SSRF/private/link-local/cloud-metadata cases according destination policy;
- redirect to untrusted host;
- credential sent across redirect prohibited;
- signed/preauthenticated upload URL absent from logs;
- OAuth refresh token absent from logs/browser;
- SFTP host-key mismatch;
- path traversal/provider key manipulation;
- manifest/object identity tampering;
- cross-site/multisite destination access;
- lower-capability admin cannot read credentials or execute restore/delete;
- provider error body cannot inject wp-admin HTML/JS.

---

## 15. Performance/resource evidence

Record rather than assume:
- peak PHP memory;
- temp disk required;
- average/peak part buffer;
- request count;
- transfer throughput informational;
- job duration distribution;
- retries;
- API cost-call count where meaningful;
- restore throughput;
- CPU cost with client-side encryption.

Certification has safety ceilings derived from minimum supported environment; it does not promise a universal speed.

---

## 16. Certification artifact

A completed future certification emits a durable report containing:
- provider/profile identity;
- C-level achieved;
- capabilities certified/unsupported;
- test matrix/results;
- environment;
- evidence references/log IDs;
- known limitations;
- security findings;
- provider/API version;
- expiry/review trigger;
- approver/reviewer;
- recommended UI/marketing label.

No credential/secret appears in report.

---

## 17. Re-certification triggers

Re-run affected evidence after:
- major provider API change;
- auth mechanism change;
- SDK/client major upgrade;
- WPE transport/crypto/manifest change;
- provider changed upload/session/checksum behavior;
- serious provider regression/security incident;
- minimum PHP/WP platform change that affects adapter;
- long certification age according future maintenance policy.

## 18. Current execution state

**NOT EXECUTED. NOT AUTHORIZED.**

ADR-0014 requires explicit owner consent before any fixture, credential, SDK, upload, provider account or restore test is executed.
