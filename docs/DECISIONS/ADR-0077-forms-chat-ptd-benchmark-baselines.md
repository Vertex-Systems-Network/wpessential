# ADR-0077 — Forms & Chat PT-D Benchmark Baselines

Status: **Accepted paper benchmark profiles / physical evidence pending**  
Date: 2026-08-28

## Context

ADR-0071 left Forms and Chat as PT-D vs PT-E evidence-gated high-volume runtime domains. Their logical data models are already defined, but future implementation needs explicit first benchmark profiles so evidence is comparable.

## Decision

For **Forms Entries**, future first benchmark profile is:
- **FRT1 — PT-D shared scoped Forms runtime**.

Mandatory comparison:
- **FRT2 — PT-E per-site Forms runtime**.

For **Chat**, future first benchmark profile is:
- **CRT1 — PT-D shared scoped Chat runtime**.

Mandatory comparison:
- **CRT2 — PT-E per-site Chat runtime**.

The decision selects benchmark order only; it does not approve final tables.

## Why PT-D first

PT-D gives one schema/migration/repository path, avoids per-site table proliferation, supports centralized diagnostics/retention and composes with WPE's scope-aware runtime architecture. These benefits must be weighed against noisy-neighbor, index growth and scope-leak blast radius.

PT-E remains mandatory because it may materially improve physical site isolation and noisy-neighbor behavior, especially for high-volume private data.

## Forms invariants

Topology cannot change:
- normalized Entry core;
- versioned canonical value document;
- selected query projections only;
- protected asset references;
- pinned Form revision;
- Workflow runtime separate;
- no password/reset token storage.

## Chat invariants

Topology cannot change:
- dedicated Conversations/Participants/Messages/Moderation stores;
- transport-independent canonical state;
- cursor/deterministic ordering;
- private Protected Assets;
- search candidates reauthorized;
- Membership/team revoke checked at request time;
- no WordPress comments as universal chat store.

## Evidence still required

After explicit owner consent:
- FRT1 vs FRT2 Entry volume/filter/privacy/retention/Backup/site-lifecycle/large-network tests;
- CRT1 vs CRT2 message volume/pagination/unread/concurrency/revocation/search/retention/Backup/site-lifecycle/large-network tests;
- wrong-site security attacks;
- noisy-neighbor benchmarks;
- 100/1k/10k-site migration/provisioning cost;
- exact DDL/index/storage profiles.

Executed Forms/Chat physical benchmarks: **0**.

## Development gate

This ADR authorizes no table, migration, Form runtime, Chat runtime, search index, transport, fixture or benchmark. ADR-0014 explicit owner consent remains required.
