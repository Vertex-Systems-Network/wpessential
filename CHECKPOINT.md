# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-27**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before runtime/source/build/migration/test implementation, dependency/package setup, crypto key generation, executable benchmark or provider/API integration.

`continue`, `proceed`, planning approval, ADR acceptance, technical readiness or Phase 0 completion do **not** authorize development.

Source of truth:
- `/DEVELOPMENT-CONSENT.md`
- `docs/DECISIONS/ADR-0014-development-consent-gate.md`

No production PHP/React source, plugin bootstrap, DB migration/table, package scaffold, implementation test, benchmark, provider integration or cryptographic implementation has been created/run.

---

# Product specification milestone

- **31/31** surfaces have screen/option inventory.
- **31/31** have behavioral specification.
- **31/31** are at **Exhaustive product-option maturity**.
- **0/31** are Authorized for development.

Primary planning sources:
- `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
- `docs/MODULES/README.md`
- `docs/IMPLEMENTATION-READINESS-MATRIX.md`
- `docs/OPEN-DECISIONS-REGISTER.md`
- `docs/DECISIONS/README.md`

Exhaustive specification does not imply physical schema, provider, performance or security verification.

---

# Accepted ADR state

Accepted decisions now extend through **ADR-0047**.

Latest accepted additions:
- ADR-0042 — Product entitlement Ed25519 + RFC 8785 JCS + domain-separated/root-authorized signer profile.
- ADR-0043 — Backup Sodium secretstream/XChaCha20/Argon2id crypto profile; native ext-sodium required for encrypted Backup v1.
- ADR-0044 — Pro automated updates target TUF 1.0-compatible Root/Targets/Snapshot/Timestamp semantics; current PHP-TUF not production-selected.
- ADR-0045 — Protector trusted-proxy request identity + shared atomic Rate Limit service + non-authenticating recovery mode.
- ADR-0046 — Watermark non-destructive derivative identity/storage architecture.
- ADR-0047 — Reset reviewed Plan + verified restore point + durable execution journal + recovery-principal invariant.

ADRs 0035–0041 previously accepted Component Blueprint, Settings, Admin Menu, Status, Listings, Connections/Webhooks and Import runtime architecture.

Earlier ADRs preserve Free/Pro distribution, Abilities, unsafe-code/SQL boundaries, license-expiry behavior, Membership semantics, Field/Table/Form/Notification/Chat/REST/Email/Dashboard/Profile/Role/Backup/OAuth architecture.

---

# Platform blockers still requiring executable evidence

- ADR-0002 compatibility floor — P-001.
- ADR-0005 UI/design system — P-002.
- ADR-0006 Job Service adapter — P-003.
- ADR-0008 Definition Repository exact DDL/indexes — P-004.
- ADR-0009 Secrets Vault exact crypto/key/recovery — P-005.
- ADR-0010 Free↔Pro executable compatibility — P-006.
- ADR-0011 CI implementation — P-007.
- ADR-0012 build toolchain/externalization — P-008.
- Query/Relations/Workflow/Membership/Backup evidence — P-009…P-013.

All protocols live under `docs/QUALITY/CONSENT-GATED-TECHNICAL-SPIKE-PROTOCOLS.md`; **none has been executed**.

---

# Current shared architecture snapshot

## Definitions/data/runtime
- Definition Repository: stable identity + immutable revisions + current/published pointers + dependencies.
- Query AST: typed/provider-neutral; no raw SQL/PHP node.
- Relations: universal typed edge model paper preference; reverse lookup/cardinality first-class.
- Workflow: runs pin published revision; Job Service handles execution; at-least-once/idempotency semantics.
- Fields: native WP meta/options where natural; Custom Tables for scale/constraints; Relations for relationships; Vault for secrets.
- Custom Tables: desired vs observed schema + typed Migration Plan.
- Form Entry/Notification/Chat/REST/Email runtime domains are separated and versioned.

## Presentation/admin
- Component Blueprint is shared across Builder Widgets, Dashboard and Listings; builders are adapters.
- Settings have explicit site/network inheritance and separate runtime values.
- Admin Menu is runtime-discovered/transformed; hide ≠ authorization; safe mode restores native navigation.
- Status separates WordPress Post Status from generic domain state machine.
- Listings use authorization-aware Query results + SSR Component Blueprint + optional enhancement.
- Dashboard routes authorize server-side on direct access.
- User/Profile identity changes and credentials are separate from generic field mutations.
- Role mutations preserve a legitimate recovery-principal invariant.

## Integrations/import
- Connections centralizes Safe HTTP, verified webhook ingress and normalized Event Inbox.
- Import uses reviewed Plan/Dry Run fingerprint, durable checkpoint, identity map, change journal and truthful rollback levels.

---

# Remote service / distribution security

## Product entitlement — ADR-0042
Accepted:
- Ed25519;
- RFC 8785 JCS;
- WPE domain separation;
- signer `kid` + root-authorized keyset;
- monotonic sequence/freshness/site binding.

Open executable evidence:
- canonicalizer/library choice;
- exact envelope/keyset byte schema;
- root custody/rotation;
- TTL/skew values;
- service↔PHP interoperability/malformed/replay fixtures.

## Pro updater — ADR-0044
Accepted:
- TUF 1.0-compatible Root/Targets/Snapshot/Timestamp semantics;
- Root 2-of-3 candidate;
- stable Targets 2-of-3 candidate;
- short-lived Timestamp/Snapshot direction;
- consistent snapshots direction;
- signed target length/hash/product/channel/compatibility metadata.

Current PHP-TUF is not production-selected due its upstream pre-release/non-production warning.

Open: production verifier/client strategy, exact metadata/runbooks/expiry values, conformance/key-compromise/freeze/rollback/update-order fixtures.

## OAuth — ADR-0034
Fixed WPE callback + one-time site-bound completion artifact + PKCE S256; executable service/token lifecycle still pending.

---

# Backup / recovery / protection

## Backup crypto — ADR-0043
Accepted:
- random 256-bit Backup Set DEK;
- secretstream XChaCha20-Poly1305 for encrypted parts;
- XChaCha20-Poly1305 DEK wrapping;
- Argon2id passphrase KEK;
- independent recovery-key slots;
- native ext-sodium required; no plaintext/weak fallback.

Open: exact framing/AAD, KDF performance floor, recovery-kit encoding, part/chunk sizing/resume, provider/cross-server restore proof.

## Protector — ADR-0045
Trusted proxy headers only from configured peers; security rate limiter requires atomic adapter; password gate is not WP login; recovery mode disables WPE overlay without authenticating anyone.

## Watermark — ADR-0046
Original/current attachment source remains intact; WPE stores versioned derivatives keyed by source fingerprint + Rule revision + output/engine profile. Private source stays private.

## Reset — ADR-0047
Reset is staged/journaled, not fake global ACID. High-risk reset requires verified restore point. Journal must survive reset scope. Last recovery principal cannot be knowingly removed without replacement.

## XML-RPC
Product options exhaustive; exact runtime complete-deny/compatibility proof still open.

---

# Membership state

Accepted semantics/defaults remain unchanged: Role ≠ Membership ≠ Billing ≠ Entitlement; outer deny cannot be bypassed; deterministic access precedence/lifecycle; Plan revision semantics; separate team roles; privacy-minimized defaults.

Remaining evidence: physical schema/indexes, revoke-to-deny cache, protected files, seat concurrency, Woo/Woo Subscriptions/SureCart reconciliation, migration/provider/privacy fixtures.

---

# Verification

## Verified
- planning branch remains isolated from `main`;
- 31/31 Exhaustive; 0/31 Authorized;
- ADR index synchronized through ADR-0047;
- Open Decisions synchronized through latest crypto/operations profiles;
- Readiness synchronized through ADR-0047;
- static primary-source research recorded where used;
- no implementation/build/test success claimed.

## Not performed / intentionally blocked
- Composer/npm install;
- production PHP/React source;
- plugin activation/bootstrap;
- DB migrations/tables;
- cryptographic key generation/encryption/signing code;
- PHPUnit/Playwright;
- P-001…P-013 spikes;
- provider/API implementation;
- performance benchmarks;
- Backup/Restore/Reset execution;
- release packaging/deployment.

Reason: explicit owner development/executable-spike consent has not been granted.

---

# Next allowed planning-only priorities

1. Secrets Vault exact envelope/key hierarchy/rotation profile;
2. Definition Repository physical schema alternatives narrowed against compatibility constraints;
3. Support Ticket runtime/storage/attachment/privacy model;
4. Dashboard Widgets remote-content/iframe trust model;
5. XML-RPC complete-deny/compatibility paper matrix;
6. Backup provider certification contract per protocol family;
7. synchronize Draft PR after each milestone.

Before **any executable work**, obtain explicit owner consent.

## Resume order
1. `/DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
5. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
6. `docs/OPEN-DECISIONS-REGISTER.md`
7. `docs/DECISIONS/README.md`
8. relevant architecture/security/module docs

Repository evidence overrides conversational memory.