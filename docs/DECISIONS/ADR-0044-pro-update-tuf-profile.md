# ADR-0044 — WPEssential Pro Update TUF Profile

Status: **Accepted protocol profile; production client/library evidence pending**  
Date: 2026-08-27

## Decision

WPEssential Pro automated updates target **TUF 1.0-compatible repository semantics** rather than a generic signed JSON manifest.

Required profile properties:

- Root, Targets, Snapshot and Timestamp top-level roles;
- Root candidate threshold 2-of-3 offline/hardware-backed keys;
- stable Targets candidate threshold 2-of-3 controlled release keys;
- narrowly scoped online Snapshot and Timestamp keys;
- consistent snapshots required direction;
- signed target length/hash + WPE product/channel/Platform-API compatibility metadata;
- persisted trusted metadata versions;
- rollback/freeze/mix-and-match/target-integrity defenses;
- sequential Root rotation through existing trust;
- entitlement/download authorization separate from artifact authenticity;
- explicit initial-install trust limitation;
- archive/staging verification after TUF authenticity and before update activation.

The current PHP-TUF implementation is **not selected for production** because its own current project documentation states that it is pre-release and not considered a complete secure production implementation.

## Why

TUF defines the threat model and metadata roles needed to handle more than simple package-signature verification, especially freeze, rollback, mix-and-match and key compromise/rotation.

## Operational direction

- Timestamp/Snapshot metadata stays short-lived;
- Root/Targets metadata lasts longer but remains bounded and renewed before expiry;
- Root/Targets private keys remain outside ordinary web/CDN runtime;
- online freshness-key compromise alone cannot authorize arbitrary executable content;
- release signing keys are separate from entitlement/OAuth/webhook keys.

Exact expiry values, signer hardware/process and client implementation remain evidence/runbook items.

## Production implementation gate

Before automated Pro updates can ship, WPE requires:
- a production-worthy TUF client/verifier strategy;
- conformance/security fixtures;
- key-custody and compromise runbooks;
- Free↔Pro compatibility/update-order tests;
- archive/staging rollback evidence.

If no acceptable PHP implementation path exists, automated updates remain blocked rather than degrading to a weaker custom updater.

See `docs/SECURITY/PRO-UPDATE-TUF-PROFILE-CANDIDATE.md`.

All executable work remains prohibited until explicit owner consent under ADR-0014.