# ADR-0065 — Local, Browser, FTP, FTPS & SFTP Backup Static Profiles

Status: **Accepted planning/security architecture / runtime certification pending**  
Date: 2026-08-28

## Context

ADR-0053/0061 define Backup provider/protocol-family certification, and ADR-0064 allows auditable static-evidence overlays. The remaining non-cloud-provider targets needed explicit product/security semantics before implementation:

- local filesystem;
- browser/manual export;
- FTP;
- FTPS;
- SSH File Transfer Protocol (SFTP).

Without a specific decision, a future implementation could incorrectly treat FTP restart as integrity, FTPS control-channel TLS as proof that the data channel is protected, SFTP as a single final-RFC capability set, browser download as durable managed storage, or a local same-host copy as off-site disaster recovery.

## Decision

The authoritative paper contract is `docs/ARCHITECTURE/BACKUP-LOCAL-FTP-SFTP-STATIC-PROFILES.md`.

### Local filesystem

`local-server` remains `bf.local-filesystem`.

- staging/non-final part names are required;
- WPE hash/size verification remains authoritative;
- manifest/completion publication is last;
- same-filesystem rename is only a candidate Commit Point until runtime evidence;
- cross-filesystem/mounted-storage durability/rename semantics are never assumed;
- local storage must warn that it can share the WordPress host's failure domain.

Static maturity: **SE2**, C-certified: **0**.

### Browser/manual export

`browser-export` remains `bf.browser-export`.

It is an authenticated **delivery/export mechanism**, not a WPE-managed remote retention destination after handoff.

It cannot be counted as a durable remote Backup copy merely because the HTTP response/download completed.

Static product semantics: **SE3**. It does not become a normal provider C3 destination because WPE no longer controls/verifies the user's local copy after handoff.

### FTP

`ftp-generic` remains `bf.ftp` and is a **Legacy / Insecure transport** compatibility profile.

RFC 3659 `REST`/`SIZE` semantics permit conditional restart, but restart is not integrity or transaction proof.

Rules:
- explicit admin acknowledgement is required if future product support exists;
- no encryption-in-transit claim;
- WPE client-side Backup encryption does not cure FTP credential/control-channel exposure;
- resumed uploads require remote-state verification and final WPE read-back/hash evidence;
- RNFR/RNTO is not assumed universally atomic/durable.

Static maturity: **SE2**, C-certified: **0**.

### FTPS

`ftps-generic` remains `bf.ftps` and follows RFC 4217 semantics.

Rules:
- TLS 1.2+ production baseline, reflecting RFC 8996/BCP 195;
- certificate/hostname validation required;
- file data connection must use the certified protected-data-channel profile, not only a secured control connection;
- implicit FTPS is not silently equivalent to the RFC 4217 AUTH TLS profile and requires a separately versioned runtime profile if supported;
- FTP restart/rename semantics remain separately evidence-gated.

Static maturity: **SE3**, C-certified: **0**.

### SSH File Transfer Protocol (SFTP)

`sftp-generic` remains `bf.sftp`.

The commonly deployed SSH File Transfer Protocol is not the historic RFC 913 “Simple File Transfer Protocol.” Its secsh file-transfer specification did not become a final IETF RFC; therefore actual client-library/server/version behavior is part of certification.

Rules:
- SSH host-key verification mandatory;
- unknown/new and changed host keys use explicit trust/re-authorization flows;
- no ordinary `accept any host key` mode;
- offset resume is candidate capability only;
- temporary remote names + rename are candidate finalization;
- requested atomic rename must not be reported as atomic if server/client cannot support it;
- no shell-command fallback is disguised as SFTP.

Static maturity: **SE2**, C-certified: **0**.

## Security consequence

Protocol encryption and Backup payload encryption are separate layers.

- FTP can carry WPE-encrypted Backup bytes while still exposing credentials/control metadata and lacking transport authentication/confidentiality.
- FTPS protects FTP transport only when the selected control/data-channel profile is correctly negotiated and certified.
- SFTP relies on SSH host authentication; disabling host-key verification destroys an important trust property.

## Certification invariant

This ADR does not certify any target.

After acceptance:
- Backup target count remains **34**;
- runtime C0–C4 certified provider/profile count remains **0**;
- normal Supported Backup Destination count remains **0** under the C3 gate.

Static protocol maturity cannot create C0–C4 labels.

## Future evidence required

After explicit development consent, P-013 must prove:
- local disk-full/process-crash/finalization/mounted-path/restore behavior;
- FTP restart/server variance/read-back/finalization/NAT-data-channel behavior;
- FTPS certificate/TLS-version/data-channel/reconnect/restore behavior;
- SFTP host-key/auth/offset-resume/atomic-rename/path/symlink/server-version/restore behavior.

No connection, credential, upload, rename, delete or restore fixture was executed to accept this ADR.
