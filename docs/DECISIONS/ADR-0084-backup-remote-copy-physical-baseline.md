# ADR-0084 — Backup Remote Copy Physical Benchmark Baseline

Status: **Accepted paper benchmark profile / P-013 and provider evidence pending**  
Date: 2026-08-28

## Context

Backup Remote Copy lifecycle, manifest-last semantics, verification depth and truthful deletion states are already accepted. The remaining paper gap is the local physical mapping for Backup Set/Artifact/Remote Copy/Object/Attempt metadata across PT-C/PT-D/PT-E.

## Decision

Future physical comparisons are:
- **BR1 — PT-D shared scoped Backup runtime metadata**, first benchmark baseline;
- **BR2 — PT-C current Backup Set/Remote Copy control + PT-D part/object/attempt history**, mandatory comparison;
- **BR3 — PT-E per-site Backup runtime**, large-network physical-isolation comparison where needed.

This ADR accepts benchmark order and invariants only; it does not approve final DDL, provider adapters, transfer code or restore implementation.

## Invariants

- every site-owned Backup/Copy/Object/Attempt row has explicit scope;
- Remote Copy commit, verification and deletion remain distinct truth states;
- `commit_unknown` is reconciled, never blindly treated as failure/success;
- final manifest/completion marker remains last under provider profile;
- provider object identity/path alone never bypasses WPE manifest/scope/integrity validation;
- provider ETag is not generically treated as cryptographic hash;
- broad prefix/path delete is prohibited without ownership proof;
- prune cannot remove the only known-good recovery point because a newer unverified backup exists;
- provider delete success is mapped to truthful trash/version/lock/delete-confirmed semantics;
- site deletion does not imply provider copies were erased;
- Restore validates manifest, object inventory, hashes, encryption and target scope rather than trusting local Remote Copy status alone.

## Evidence still required

After explicit owner consent P-013 must compare BR1/BR2/BR3 and provider profiles using commit crash windows, manifest-last failures, checksum/inventory mismatches, delete/trash/version/object-lock behavior, retention/prune safety, re-verification, long credential refresh, site lifecycle, wrong-site reference collisions, large part/object inventories and 100/1k/10k-site topology evidence.

Executed Backup physical/provider runtime benchmarks: **0**.  
Backup provider C-certifications: **0/34**.

## Development gate

This ADR authorizes no Backup table/migration, provider transfer/delete, key use, Job execution, Restore, prune, fixture or benchmark. ADR-0014 explicit owner consent remains required.