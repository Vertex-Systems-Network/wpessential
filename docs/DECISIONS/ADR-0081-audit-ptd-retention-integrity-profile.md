# ADR-0081 — Audit PT-D Retention & Integrity Profile

Status: **Accepted paper architecture / exact DDL, retention and integrity evidence pending**  
Date: 2026-08-28

## Context

ADR-0071 places Audit in PT-D direction, but WPE still needs explicit boundaries between Audit, domain history, operational diagnostics and high-volume analytics, plus a truthful integrity claim that does not imply a local WordPress database is tamper-proof against privileged infrastructure access.

## Decision

WPE accepts **AU1 — PT-D shared scoped Audit store** as the favored future physical baseline.

Audit Event is a durable security/business-relevant action record. Structured domain histories remain in their owning domains; operational diagnostics are shorter-lived; detailed access analytics are not Audit by default.

Normal application semantics are append-only. Corrections become new linked events. Privacy redaction/anonymization, retention purge and controlled migration are explicit governed transformations.

## Integrity boundary

A local database Audit table is **not** claimed to be tamper-proof against a sufficiently privileged DB/server/root actor.

Baseline claims may include application append-only behavior, correlation/provenance, controlled retention and detectable integrity inconsistencies. Cryptographic chaining/signed or external checkpoints remain optional future evidence profiles and cannot be marketed as non-repudiation until key custody and attacker model are proven.

## Data-minimization invariants

- no passwords, reset tokens, Vault plaintext, Authorization/Cookie headers or reusable private URLs;
- no full arbitrary provider/webhook/request bodies by default;
- large Definition/content changes use revision IDs/fingerprints instead of duplicated full before/after payloads;
- Membership/Workflow/Event Inbox/Email domain history is linked rather than copied wholesale;
- IP/user-agent is purpose-bound, minimized and bounded, not automatically logged for every event.

## Retention

One universal retention period is rejected. Security, administrative, business-control, operational, short diagnostic and privacy-sensitive classes remain separately configurable/evidence-gated.

## Restore invariant

Restore cannot silently erase the fact that Restore occurred. Imported historical Audit preserves original event/time/provenance and does not overwrite current post-backup history.

## Evidence still required

After explicit owner consent:
- 1M/10M/100M row scale where practical;
- structured actor/resource/action/time queries;
- authorized network aggregation and wrong-site attacks;
- write contention and mutation/Audit failure boundary;
- retention purge and privacy redaction;
- site delete/transfer;
- Backup/Restore chronology;
- exact DDL/index/partition/retention evidence;
- optional tamper-evidence attacker-model experiments if later approved.

Executed Audit physical/integrity benchmarks: **0**.

## Development gate

This ADR authorizes no Audit table/migration/logger, integrity chain, external checkpoint, exporter/eraser, fixture or benchmark. ADR-0014 explicit owner consent remains required.