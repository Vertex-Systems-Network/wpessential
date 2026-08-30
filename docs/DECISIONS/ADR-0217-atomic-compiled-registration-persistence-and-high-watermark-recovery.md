# ADR-0217 — Atomic Compiled Registration Persistence and High-Watermark Recovery

Status: **Accepted**  
Date: **2026-08-29**  
Work package: **WP121 — Milestone 1 Platform Foundation**  
Approval: `GOV-OWNER-CONSENT-001` ACTIVE

## Context

WPEssential dynamically manages WordPress registration definitions such as post types, taxonomies, metaboxes and settings-page structures. The owner contract requires these definitions to be compiled when their canonical source changes instead of scanning and reconstructing arbitrarily large definition populations on ordinary requests.

The prior WP121 tranche established the compile-on-write model and an in-memory active-generation reference store. That adapter was intentionally not production persistence. This ADR accepts the next bounded implementation: an atomic, scope-isolated persistence model for compiled registration generations, including corruption handling and last-known-good recovery.

A first implementation pass exposed an important sequencing defect during executable CI evidence: after a corrupt active generation rolled back to a previous generation, deriving the next generation from the active pointer could reuse the quarantined generation number. Reuse would violate immutable evidence/history. The implementation was corrected before acceptance.

## Decision

WPEssential SHALL persist compiled registration runtime projections using two separate concepts:

1. **immutable compiled generation history**; and
2. **mutable scope state pointer** identifying the current active and fallback generations.

The active pointer is **not** generation-sequence authority.

### Scope identity

Every generation and pointer is bound to an explicit `(network_id, site_id)` scope.

- Site scope uses the actual site identifier.
- Network scope is represented distinctly and does not alias any site scope.
- Request-supplied site/network identifiers never grant authority by themselves; canonical execution context and Policy remain responsible for authorization.

Cross-site or site/network aliasing is prohibited.

### Immutable generation high-watermark

Generation numbers are monotonically allocated from the historical scope high-watermark:

`next generation = MAX(all persisted generation IDs for the scope) + 1`

This includes corrupt or quarantined generations.

Therefore:

- recovery may move the **active pointer backward**;
- the immutable generation high-watermark never moves backward;
- corrupt/quarantined generation IDs remain permanently consumed;
- a generation number may never be silently reused;
- failed transactions that fully roll back without a persisted generation do not consume a generation number.

### Atomic publication

Publication requires one transaction/CAS boundary for the scope:

1. acquire/lock the scope state row;
2. verify the expected active pointer;
3. verify the historical next-generation number;
4. persist the complete immutable manifest generation;
5. update the active pointer and previous-active fallback pointer;
6. commit.

A staged generation is not active until the pointer update commits. Partial publication must roll back and must not become request-visible.

Concurrent stale writers fail closed and must recompile from current canonical state rather than silently overwriting another writer.

### Integrity and corruption

Each compiled manifest carries a deterministic SHA-256 checksum over the canonicalized generation + entries representation.

A missing, malformed or checksum-invalid active generation is explicit corruption. It is not silently treated as valid or as an empty manifest.

The corrupted generation is quarantined/recorded, and recovery may atomically move the pointer to a verified last-known-good fallback. If both active and fallback are invalid, runtime fails closed.

Recovery does not make the fallback the latest canonical user intent. Future mutation/compile work must recompile from current canonical definition sources; it must not derive new configuration by treating the fallback projection as source truth.

### Runtime boundary

The compiled manifest is a **derived runtime projection**, not source business/configuration truth.

Ordinary runtime registration loading reads only the active compiled manifest for the resolved scope. It must not scan historical definition rows or process the full definition population on every request.

### Persistence implementation

The accepted implementation introduces:

- `DatabaseAdapterInterface`;
- `NativeWpdbAdapter`;
- `CompiledRegistrationScope`;
- `CompiledRegistrationPointer`;
- `CompiledRegistrationManifestIntegrity`;
- `CompiledRegistrationGenerationSequenceInterface`;
- `CompiledRegistrationPersistenceGatewayInterface`;
- `AtomicCompiledRegistrationStore`;
- `WpdbCompiledRegistrationPersistenceGateway`;
- `CreateCompiledRegistrationTablesMigration`;
- in-memory reference persistence gateway for deterministic negative testing.

The WordPress database topology uses network-prefixed InnoDB tables:

- `<base_prefix>wpessential_registration_generations`
- `<base_prefix>wpessential_registration_state`

Generation history and active/fallback state remain separate.

## Executable evidence

The corrected implementation commit is:

`de2bf6ea0299ce3900a0d6dff2d4646066137497`

GitHub Actions run **33261866811** / run **#89** completed **SUCCESS** on an actual GitHub-hosted Ubuntu runner.

The run executed and passed:

- Composer metadata validation;
- canonical architecture validation;
- owner engineering-contract validation;
- PHP syntax under PHP 8.2;
- **9/9 smoke suites**;
- real **MySQL 8.4** compiled-registration transactional integration evidence.

The MySQL integration verifies, among other boundaries:

- active/fallback pointer persistence;
- site isolation and network-vs-site isolation;
- checksum-corrupt active generation recovery;
- immutable corrupt-generation high-watermark retention;
- post-recovery allocation of the next new generation rather than ID reuse;
- stale compare-and-swap writer rejection;
- invalid JSON payload quarantine/recovery;
- preservation of immutable historical generations.

The preceding run **33261668224 / #87** correctly failed the smoke gate and stopped the MySQL integration before acceptance. That failure exposed the generation-reuse edge case; the implementation was fixed rather than waived.

## Non-certifications

This ADR does **not** claim:

- 10K/100K compiled-registration performance certification;
- full WordPress runtime certification of `NativeWpdbAdapter`;
- end-to-end WordPress AJAX/nonce/Policy integration certification;
- production deployment or live production DB migration;
- persistent Definition repository WordPress/MySQL certification;
- persistent/tamper-evident Audit storage certification;
- Action Scheduler coexistence/backend/Multisite certification;
- business-module readiness by itself.

The MySQL run certifies the accepted transactional storage/recovery behavior in the CI MySQL environment. It does not substitute for later WordPress integration or scale evidence.

## Consequences

### Positive

- request-time registration does not require rebuilding a large dynamic-definition population;
- partial generations cannot become active under the accepted transaction boundary;
- stale concurrent writers are rejected;
- site/network isolation is part of storage identity;
- corruption is explicit and recoverable without rewriting immutable history;
- active rollback does not permit generation-number reuse;
- future performance evidence can benchmark a stable persistence contract.

### Trade-offs

- two tables and transactional state management are required;
- corrupt generations remain retained/quarantined until a future retention policy explicitly governs cleanup;
- recovery requires recompile-from-source on the next mutation rather than treating a fallback projection as canonical source truth.

## Stop-the-line conditions

WP121 must stop rather than promote this storage layer if later evidence shows any of the following:

- cross-site/network projection leakage;
- partial generation made active;
- generation ID reuse;
- stale writer overwriting current state;
- corrupt manifest silently accepted;
- recovery bypassing checksum verification;
- ordinary runtime scanning the historical definition population instead of loading the active compiled manifest;
- static documentation being promoted as runtime/performance certification.

## Next WP121 work

After this ADR, the next bounded shared-foundation sequence is:

1. production Definition/Audit persistence adapters + bounded migrations;
2. real WordPress AJAX/nonce/Policy integration fixtures;
3. Action Scheduler coexistence/backend evidence;
4. durable Job attempt/lease/checkpoint contracts after backend evidence;
5. minimal Platform admin shell + Runtime Observatory diagnostics UI;
6. executable 10K/100K compiled-registration scale evidence;
7. shared-foundation readiness gate before the first business-module tranche.

Production deployment and separately privileged destructive/live-provider operations remain outside this ADR.
