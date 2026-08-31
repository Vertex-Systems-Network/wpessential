# ADR-0078 — Membership PT-D/PT-E Benchmark Baseline

Status: **Accepted paper benchmark profile / P-012 physical evidence pending**  
Date: 2026-08-28

## Context

Membership logical runtime semantics are already defined: Enrollment is authoritative lifecycle state, materialized Entitlements accelerate access evaluation, transition history is immutable domain history, overrides are exceptional, and team/invitation concurrency must be explicit. ADR-0071 identified PT-D as the current topology candidate but did not define a comparable physical benchmark profile.

## Decision

Future P-012 first benchmark profile is:
- **M1 — PT-D shared scoped Membership runtime**.

Mandatory comparison:
- **M2 — PT-E per-site Membership runtime**.

M1 includes the logical store family for Enrollments, materialized Entitlements, transitions, overrides, a small principal-access-generation store, Teams, Team Members and Invitations where those features are enabled.

This accepts benchmark order and physical invariants only. It does not approve final table names, SQL types, index orders, cache implementation or lock primitives.

## M1 invariants

- every site-owned row has explicit network/site scope;
- WordPress user identity is not Membership scope;
- Enrollment remains authoritative current lifecycle state;
- Entitlements remain derived/rebuildable current grants;
- access-affecting mutations update source state, affected grants and principal access generation under a concurrency-safe write boundary;
- timestamp-based expiry is enforced at request time even if jobs are late;
- ordinary access checks make no provider API call;
- optional caches cannot be required for authorization correctness;
- transition history does not duplicate raw provider webhook payloads;
- invitation tokens are stored only as hashes;
- concurrent Plan Group/seat operations cannot silently violate exclusivity/capacity;
- site lifecycle and Backup/Restore are scope/retention-aware.

## Security selection gate

A profile is rejected regardless of speed if it permits wrong-site access, stale allow after committed revoke, late-job expiry bypass, duplicate effective Enrollment from retries, Plan Group exclusivity failure, seat overbooking, or stale Restore resurrection without reconciliation.

## Evidence still required

After explicit owner consent, P-012 must compare M1 and M2 using:
- large Enrollment/Entitlement datasets;
- access hot-path latency/query count;
- provider/source reconciliation;
- mass expiry/revoke/rebuild;
- Plan Group and team-seat concurrency;
- wrong-site and cache-collision attacks;
- site archive/delete/restore;
- Backup extraction;
- noisy-neighbor behavior;
- 100/1k/10k-site provisioning/migration cost;
- MySQL/MariaDB query plans, locking and storage/index evidence.

Executed Membership physical benchmarks: **0**.  
MB-certified billing profiles: **0**.

## Development gate

This ADR authorizes no Membership table, migration, cache, lock, fixture, provider call, queue execution or benchmark. ADR-0014 explicit owner consent remains required.