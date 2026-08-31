# ADR-0095 — Import Runtime Physical & Recovery Profile

Status: **Accepted paper physical/recovery profile / executable evidence pending**  
Date: 2026-08-28

## Context

Import architecture already separates Source Adapter, reviewed Plan/Dry Run, Import Run, Checkpoints, Identity Map, Change Journal and verification. The remaining ambiguity was physical topology and the recovery contract for crashes between target mutation and durable checkpoint state.

## Decision

Accept:
- **IR1 — PT-D shared scoped Import Runtime** as the first physical benchmark for Runs, Checkpoints, Identity Maps and Change Journals;
- **IR2 — PT-E per-site Import Runtime** as a mandatory comparison for physical isolation/large private imports;
- source/archive bytes remain in protected bounded temporary artifact storage, not giant Import DB rows;
- JobService provides execution opportunity only and never becomes Import source of truth.

## Crash-reconciliation invariant

A crash after target mutation but before Identity Map/Checkpoint commit must reconcile by deterministic source identity and target fingerprint/ownership before any repeat create/update.

Job redelivery or lease expiry never proves the target mutation did not happen.

## Identity invariant

Source numeric IDs are never assumed to equal target IDs. Re-import/update uses the certified source→target Identity Map and explicit match policy.

Concurrent same-source imports must not silently create duplicate target ownership.

## Rollback truth

Rollback remains R0–R3 and must respect current target fingerprints. Full rollback cannot be claimed when irreversible, external or newer conflicting edits remain.

A Change Journal does not replace a verified Backup for broad/high-risk recovery.

## Scope invariant

Site is default scope. IR1 shared rows carry trusted scope in every ownership/query/mutation path. Network import is explicit and cannot be obtained by omitting a site coordinate.

## Restore invariant

Restoring copied Import/Job history does not blindly resume work. Active/nonterminal Runs require reconciliation of source artifact, Plan, target fingerprints, scope and Job continuation state before resume.

## Evidence still required

After explicit owner consent:
- IR1 vs IR2 exact DDL/index/storage;
- 100k/1M record imports;
- crash windows before/after target/Map/Checkpoint/enqueue commits;
- duplicate Job/concurrent same-source imports;
- target-edit/rollback conflicts;
- temp archive path/size/MIME/SSRF security;
- 100/1k/10k-site lifecycle/noisy-neighbor/table-version behavior;
- Backup/Restore and retention cleanup.

Required correctness results include zero duplicate targets after valid crash/retry fixtures, zero wrong-site mutations/reads and zero falsely reported full rollbacks.

Executed Import physical/recovery fixtures: **0**.

## Development gate

This ADR authorizes no import, source fetch, archive extraction, DB table, Job, target mutation, rollback, Backup/Restore or benchmark. ADR-0014 explicit owner consent remains required.