# WPEssential — Local, Browser, FTP, FTPS & SFTP Static Backup Profiles

Status: **Phase 0 planning / static standards research only / no runtime certification authorized**  
Date reviewed: **2026-08-28**  
Related: ADR-0053, ADR-0056, ADR-0061, ADR-0064, P-013.

## 1. Purpose

This document closes the paper-level semantics for the five Backup targets that are not ordinary cloud-provider API profiles:

- `local-server` → `bf.local-filesystem`;
- `browser-export` → `bf.browser-export`;
- `ftp-generic` → `bf.ftp`;
- `ftps-generic` → `bf.ftps`;
- `sftp-generic` → `bf.sftp`.

These are protocol/host profiles, not provider brands. Static documentation maturity does **not** create C0–C4 certification.

Current runtime-certified count for all five remains **0**.

---

## 2. Evidence scale

This document uses the ADR-0061 SE scale:

- SE0 — insufficient current evidence;
- SE1 — protocol/platform baseline reviewed;
- SE2 — transfer/finalization/security semantics reviewed;
- SE3 — transfer + integrity/lifecycle/deviation semantics reviewed.

SE3 still means **no credential, transfer, corruption, crash, delete or restore fixture has run**.

---

# 3. `local-server` — `bf.local-filesystem`

## Static maturity

**SE2 paper maturity / C0–C4 = none.**

## Product meaning

Local server storage means a WPE-controlled private filesystem destination on the WordPress host or an explicitly configured mounted path.

It is a real Backup destination only when the destination survives independently enough for the site's recovery model. A path under a disposable temp directory is not equivalent to durable local Backup storage.

## Required paper contract

Candidate write lifecycle:

`planned → private staging file → streamed write → WPE hash/size verification → candidate finalization → final manifest last → local_verified`

Rules:
- data parts are written to non-final/staging names first;
- final Backup manifest/completion marker is published last;
- free-space and write permissions are preflight concerns;
- WPE own cryptographic hashes remain integrity authority;
- local file metadata alone is not sufficient integrity evidence;
- cross-filesystem rename is never assumed atomic;
- mounted/NFS-like storage is a separate runtime profile because rename/durability/locking semantics can differ;
- a successful PHP file write does not by itself prove durable persistence after process/host crash;
- retention must protect the final known-good recovery point;
- cleanup must distinguish staging/orphan parts from committed Backup parts.

## Finalization truth

A same-filesystem temp→final rename is the preferred candidate finalization mechanism where the runtime proves its semantics, but ADR-0056 remains authoritative: no finalization operation becomes a Commit Point until runtime evidence exists for the selected filesystem/host profile.

## Failure classes to preserve

- disk full before/during write;
- permission/ownership failure;
- destination disappears/unmounts;
- partial write;
- process death before finalization;
- finalization succeeds but later verification fails;
- external deletion/modification;
- retention cleanup collision;
- original site and local Backup lost in the same host failure.

## UI warning

Local-server storage must display that it may share the same failure domain as the WordPress site. It is useful for fast restore/staging but is not automatically an off-site disaster copy.

---

# 4. `browser-export` — `bf.browser-export`

## Static maturity

**SE3 product semantics / intentionally not a normal C3 storage destination.**

## Product meaning

Browser/manual download is a delivery/export mechanism. It transfers a generated Backup package to an authenticated operator/browser.

It is **not** a remotely managed WPE retention destination after the response has left the server.

## Rules

- requires WordPress capability + nonce/session authorization;
- download generation/streaming has explicit size/time/resource limits;
- any temporary server-side artifact follows short-lived cleanup policy;
- Content-Disposition filename is sanitized and deterministic;
- secrets are never placed in URL query parameters;
- browser download completion cannot prove the user's local disk retained the file;
- WPE cannot prune, inventory, verify or restore from the user's downloaded copy unless the user later supplies it;
- browser export cannot satisfy a scheduled remote-copy retention requirement;
- browser export cannot be counted in “remote Backup copies available” after handoff.

## Support-label rule

The UI may label this `Manual Download / Export`, not `Remote Backup Destination`.

Its usefulness is independent of C3 provider certification because there is no WPE-managed remote provider to certify.

---

# 5. `ftp-generic` — `bf.ftp`

## Static maturity

**SE2 standards maturity / C0–C4 = none.**

## Standards facts

RFC 959 defines FTP and rename/restart commands. RFC 3659 standardizes/updates machine-readable metadata and STREAM-mode restart semantics including `SIZE`, `MDTM`, `MLST/MLSD` and `REST`.

Important interpretation:
- `REST` enables transfer restart at a byte position for supported STREAM transfers;
- `SIZE` can provide exact transfer size;
- restart is not an integrity proof;
- FTP itself does not provide transport confidentiality or server authentication equivalent to TLS/SSH.

## WPE security position

Plain FTP is **legacy/insecure** and is not the recommended default transport for credentials or Backup data.

If product support is retained for compatibility:
- UI must mark it `Legacy / Insecure transport`;
- credential entry remains Vault-backed;
- enabling it requires explicit administrator acknowledgement;
- no UI language may imply encryption in transit;
- stronger SFTP/FTPS/provider API alternatives are recommended.

Client-side WPE Backup encryption can protect Backup contents at rest/in transit from passive file disclosure, but it does not fix FTP credential/control-channel exposure or active transport trust problems.

## Resume contract

FTP resume is conditional, never assumed.

Before resuming an interrupted upload:
1. reconnect/authenticate;
2. query exact remote partial size/state using certified commands;
3. confirm the partial object belongs to the expected WPE Backup part using WPE-side identity/journal metadata;
4. use `REST` only where the selected client/server profile proves correct STREAM upload restart semantics;
5. restart from zero if state cannot be trusted;
6. verify final WPE size/hash through read-back according to certification policy.

A remote byte count matching expected length is insufficient by itself.

## Finalization

Preferred planning strategy:
- upload to a staging filename;
- verify remote bytes/read-back according to profile;
- rename with `RNFR`/`RNTO` where supported;
- publish final WPE manifest last.

RFC 959 rename support does **not** give WPE a universal atomic/durable rename guarantee. The server/client profile must prove finalization behavior.

## Data-channel concerns

FTP uses separate control/data connection behavior. NAT/firewall/passive/active mode differences are runtime certification concerns, not paper-inferred capabilities.

---

# 6. `ftps-generic` — `bf.ftps`

## Static maturity

**SE3 standards/security maturity / C0–C4 = none.**

## Protocol meaning

FTPS here means FTP secured according to RFC 4217's TLS negotiation model, not SFTP.

RFC 4217 makes control-channel TLS negotiation and data-channel protection explicit. A secured control connection alone is not enough to silently infer protected file data.

## TLS baseline

RFC 8996 / BCP 195 deprecates TLS 1.0 and TLS 1.1 and updates RFC 4217 accordingly.

WPE production planning therefore requires:
- TLS 1.2 or newer;
- normal certificate validation;
- hostname validation according to chosen TLS/client stack;
- no ordinary “trust all certificates” mode;
- protected data connection (`PROT P`-equivalent certified behavior) for Backup transfer;
- explicit UI failure for servers that only offer obsolete TLS.

## Explicit vs implicit FTPS

The RFC 4217 `AUTH TLS` style is the standards baseline.

“Implicit FTPS” conventions must not be silently merged into the same profile. If future demand requires implicit FTPS, it needs a separately versioned endpoint/client profile and runtime fixtures.

## Resume/finalization

FTP `SIZE`/`REST`/rename semantics from the FTP profile may be reused only after the FTPS client/server profile proves:
- TLS reconnect/session behavior does not break restart handling;
- both control and data channels use required protection;
- certificate checks survive reconnects;
- partial-size/restart/finalization behavior is correct.

FTPS encryption does not itself make FTP restart or rename transactional.

---

# 7. `sftp-generic` — `bf.sftp`

## Static maturity

**SE2 standards/draft maturity / C0–C4 = none.**

## Naming warning

This profile means the **SSH File Transfer Protocol** used over SSH, not the unrelated historical “Simple File Transfer Protocol” RFC 913.

The commonly implemented SSH File Transfer Protocol did not become a final IETF RFC. The latest secsh working-group file-transfer specification remains expired `draft-ietf-secsh-filexfer-13`.

Therefore WPE must certify the actual selected client library + negotiated protocol/server implementation rather than claim a universal final-RFC feature set.

## SSH transport security

RFC 4253 SSH transport provides encryption, integrity protection and server host authentication primitives.

WPE requirements:
- SSH host-key verification is mandatory;
- unknown/new host key requires explicit trust workflow;
- changed host key is a high-severity failure unless explicitly re-authorized;
- `StrictHostKeyChecking=no`-equivalent behavior is not an ordinary production default;
- password/private-key/passphrase credentials remain Vault references;
- private key material is never returned to browser/log/support diagnostics.

## Resume candidate

The SSH File Transfer draft defines byte-offset writes. Therefore offset resume is a valid candidate capability, but it is **not certified by static draft existence**.

Runtime profile must prove:
- selected library exposes safe offset writes;
- server protocol/version supports expected semantics;
- remote partial size/identity can be checked;
- local checkpoint and remote state agree;
- process restart can reconstruct state without secret leakage;
- mismatch forces safe restart instead of blind append.

## Finalization candidate

The draft defines rename operations and flags including an atomic-rename request, while allowing a server to return unsupported when it cannot meet requested semantics.

Therefore:
- upload uses a temporary remote name;
- atomic rename is requested only when client/server profile supports it;
- unsupported atomic rename cannot be silently treated as atomic;
- fallback finalization must be explicitly certified and must retain WPE manifest-last semantics;
- remote read-back/hash verification remains independent of rename success.

## Path/security rules

- target base directory is explicit;
- canonical/real-path resolution is used where available to prevent accidental escape;
- user-supplied path traversal is rejected by WPE path policy;
- symlink behavior must be included in certification;
- no shell-command fallback is treated as SFTP.

---

# 8. Static profile summary

| Profile | Family | Static maturity | Resume claim | Transport security | Normal C3 support now? |
|---|---|---:|---|---|---|
| `local-server` | `bf.local-filesystem` | SE2 | runtime-dependent | local host/filesystem trust | No |
| `browser-export` | `bf.browser-export` | SE3 product semantics | N/A | HTTPS/admin-session delivery profile | Not a managed remote provider |
| `ftp-generic` | `bf.ftp` | SE2 | conditional REST; not integrity | insecure plaintext protocol baseline | No |
| `ftps-generic` | `bf.ftps` | SE3 | conditional REST after TLS profile proof | TLS 1.2+ required | No |
| `sftp-generic` | `bf.sftp` | SE2 | conditional offset resume | SSH host authentication/encryption/integrity | No |

---

# 9. Future certification fixtures — NOT AUTHORIZED

After explicit owner development consent, P-013 must include at minimum:

## Local filesystem
- disk full mid-part;
- process kill mid-write;
- same-filesystem and cross-filesystem finalization;
- mounted-volume disappearance;
- permission/ownership changes;
- external part corruption/deletion;
- full restore from final local copy.

## FTP
- passive/active/NAT profiles;
- interrupted STOR + REST resume;
- server with REST unsupported/incorrect;
- exact SIZE/partial mismatch;
- RNFR/RNTO behavior;
- plaintext-transport warning UX;
- corrupt read-back and restore.

## FTPS
- expired/wrong-host/untrusted certificate;
- TLS 1.0/1.1-only server rejection;
- protected control + data channels;
- reconnect/resume;
- certificate rotation;
- firewall/passive data-channel cases;
- restore.

## SFTP
- known/unknown/changed host key;
- password and key-based auth;
- offset resume after process kill;
- remote partial mismatch;
- atomic rename supported/unsupported;
- symlink/path escape attempts;
- host/server protocol variants;
- restore.

No fixture above has been executed.

---

# 10. Static references

Reviewed standards/reference material:
- RFC 959 — File Transfer Protocol;
- RFC 3659 — Extensions to FTP (`SIZE`, `MDTM`, `MLST/MLSD`, STREAM `REST`);
- RFC 4217 — Securing FTP with TLS;
- RFC 8996 / BCP 195 — TLS 1.0/1.1 deprecated; updates RFC 4217;
- RFC 4253 — SSH Transport Layer Protocol;
- `draft-ietf-secsh-filexfer-13` — SSH File Transfer Protocol, expired Internet-Draft; offset writes and rename capability model.

## Development gate

**No filesystem destructive fixture, FTP/FTPS/SFTP connection, credential, upload, resume, rename, delete, certificate/host-key probe or restore is authorized until explicit owner development consent under ADR-0014.**
