# ADR-0102 — Pro Updater TUF Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Decision

Automated WPEssential Pro updates remain blocked until a future updater implementation passes the fixed TUF evidence protocol in `docs/QUALITY/PRO-UPDATE-TUF-EXECUTABLE-EVIDENCE-PROTOCOL.md`.

The first evaluation profile uses TUF Root/Targets/Snapshot/Timestamp semantics with consistent snapshots and explicitly tests:
- trusted Root bootstrap and sequential Root rotation;
- role-specific threshold signatures;
- rollback/freeze/mix-and-match resistance;
- metadata expiry and trusted-version persistence;
- target SHA-256 + length verification;
- channel/product/Platform API/WordPress/PHP compatibility metadata;
- CDN/API compromise separation from TUF authenticity;
- package archive safety/staging/recovery;
- key-compromise and lost-key runbooks.

A TK1 paper custody baseline evaluates 2-of-3 offline Root and 2-of-3 controlled Targets signing with narrowly scoped online Snapshot/Timestamp roles. Exact production key count/custody remains operational-security evidence and may require a superseding ADR.

## Why

A working external updater is a software supply-chain authority. HTTPS, account authentication or one custom package signature do not provide the same rollback/freeze/mix-and-match/key-rotation properties as the accepted TUF architecture.

## Current state

TU-01…TU-44 documented. **0/44 executed.**

No TUF metadata, signing key, repository, verifier, package download or updater transaction exists.

## Development gate

This ADR accepts the future evidence contract only. If no production-grade verifier can meet it, automated Pro updates stay blocked rather than falling back to weak custom signed JSON. Explicit owner consent under ADR-0014 is required before any execution.