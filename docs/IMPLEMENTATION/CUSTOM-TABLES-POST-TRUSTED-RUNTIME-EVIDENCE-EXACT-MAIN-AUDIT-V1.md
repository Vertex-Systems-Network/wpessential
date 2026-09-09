# Custom Tables — Post-Trusted Runtime Evidence Exact-Main Audit V1

Audit anchor: `main @ 62240fc03b8e6b91e720f6845bbdaee584151f9f`  
Triggered by: Issue #463 / merged PR #464  
Audit issue: #465  
Status: **PASS FOR TRUST-BOUNDARY HARDENING EVIDENCE / MANAGED-TABLE EXECUTION STILL BLOCKED**

## Scope reviewed

This audit re-evaluates the promoted Trusted Runtime Evidence Sources V1 implementation on exact current `main` after PR #464 merged with all seven applicable exact-head workflows green:

- Architecture Guards;
- PHP Quality Toolchain;
- Platform Compatibility Matrix;
- Distributable Package;
- CPT Runtime;
- Taxonomy Runtime;
- Status Reference Application.

Review threads and submitted reviews were clean at promotion.

## Accepted evidence

The promoted implementation materially improves the production trust boundary:

1. Runtime Composition no longer accepts caller-supplied `MetadataPreconditionFacts`; metadata facts are obtained through a typed provider port.
2. Production metadata facts are derived through the existing CT1 identity + `WordPressCt1SchemaIntrospector` path, which is bounded to `INFORMATION_SCHEMA` metadata reads and does not read managed-table row payloads or row aggregates.
3. Runtime Composition binds the supplied table descriptor to the persisted Migration Run definition id, revision, table key and target schema version before metadata lookup.
4. Source failures for run persistence, metadata, recovery verification and confirmation lookup are converted to deterministic fail-closed reasons instead of becoming implicit authorization.
5. An internal WPE confirmation metadata table is registered through canonical Platform migration infrastructure; it is site-scoped and separate from managed Custom Tables target storage.
6. Runtime confirmation lookup is read-only and revision/actor bound.
7. Canonical `CustomTablesRuntimeCompositionFactory::create()` accepts zero arguments and the constructor is private, so callers cannot inject providers at service creation time.
8. Runtime composition packages remain immutable with `execution_allowed=false`.
9. Expanded attack coverage proves rejection for missing/stale/actor-mismatched confirmation, capability denial, descriptor/run mismatch, metadata-source failure, site/network mismatch, missing recovery evidence, R3, generation mismatch and row-scan preconditions.

## Remaining blockers

The audit does **not** authorize an R1/R2 execution coordinator. Three trust/operability gaps remain:

1. `CustomTablesRuntimeCompositionFactory::production(...)` is still a public construction method that accepts trusted-provider interface instances. The canonical Plugin bootstrap owns the registered production instance, but the factory API itself does not yet intrinsically prevent alternate code from constructing another factory with a custom implementation of those marker interfaces. This must be tightened before an execution boundary relies on the factory as an exclusive trust root.
2. Production recovery verification is currently wired to `FailClosedRecoveryVerificationProvider`. This is safe, but it means no real already-existing recovery artifact can currently satisfy production readiness. A server-owned allowlisted real verification source remains separately gated.
3. The internal confirmation store has a read-only runtime provider, but this lane intentionally added no trusted confirmation issuance/write workflow. Therefore production cannot rely on this store as a complete approval lifecycle yet.

These gaps are reasons to keep physical managed-table execution blocked; they are not reasons to weaken the fail-closed behavior.

## Custom Tables decision

Surface 7 remains **ACTIVE / NOT PASS** at the repository-defined bounded runway indicator of **90%**. PR #464 advances trust evidence but does not define a new percentage milestone.

Still forbidden:

- generated managed-table `CREATE`, `ALTER`, `DROP`, `RENAME`, `TRUNCATE` or `dbDelta()` dispatch;
- R1/R2 managed-table statement execution;
- R3/R4 execution;
- live row-count/null/duplicate/range/max-length scans or row payload reads;
- Backup creation/restore side effects;
- migration jobs, leases, retries or Action Scheduler execution;
- row CRUD/Data Source runtime, backfill, deduplication, shadow-copy or swap;
- CT2/PT-D/CT3 runtime conversion or external-table adoption;
- public admin/REST/Ability mutation for managed-table execution;
- deployment/release or product-parity certification.

Custom Tables execution work is therefore safe-paused after this audit until a later explicit trust-activation/hardening lane is promoted.

## Surface 1 / Surface 2 full-final eligibility assessment

The owner requested that CPT Builder and Taxonomy Builder be taken to full-final completion. The existing runtime baselines are non-trivial and must be preserved rather than replaced: both modules already include definition projection, validation, admin, Ability and registration/runtime integration, and their dedicated CI workflows are green.

However, full-final means the repository's `PARITY_OR_EXCEED` lifecycle, not merely a green runtime regression workflow.

### Surface 1 — CPT Builder

Machine product truth currently reports:

- Atomic inventory: `ATOMIC_INVENTORY_COMPLETE`;
- Options Bank: `BANK_SURFACE_SEEDED`, 107 records;
- missing prerequisite: final native WordPress cross-check, market audit and Bank Review;
- therefore a schema-valid option contract and UX contract must not be promoted yet.

**Next authorized CPT lane:** `CPT Builder Options Bank Audit Closure V1`.

Goal: close native + market + semantic/policy review for the 107-record Bank with zero unresolved items, without claiming runtime parity. Once promoted, a separate CPT Atomic Option Contract + UX Closure lane may open.

### Surface 2 — Taxonomy Builder

Machine product truth currently reports:

- Atomic inventory: `ATOMIC_INVENTORY_COMPLETE`;
- Options Bank: `BANK_REVIEWED`, 71 records;
- Bank review has zero unresolved items and is explicitly ready to feed downstream implementation contracts;
- no schema-valid Taxonomy option-contract file exists yet.

**Next authorized Taxonomy lane:** `Taxonomy Builder Atomic Option Contract + UX Closure V1`.

Goal: project all 71 reviewed Bank records and Wave-1 atomic inventory into schema-valid machine contracts, close missing/unclassified counts to zero, define the reviewed information architecture/interaction contract, and produce a deterministic runtime gap matrix against the existing Taxonomy baseline. This planning lane does not yet claim product parity.

## Parallelism decision

The CPT Bank-closure lane and Taxonomy Option-Contract/UX lane are dependency-safe and non-overlapping. They may run in parallel because:

- they own different product surfaces and files;
- neither mutates Custom Tables runtime ownership;
- neither authorizes destructive/live-provider work;
- they are planning/contract closure lanes before broader runtime implementation.

After each planning lane promotes, the Supervisor must re-run Issues → PRs → queue preflight before opening that surface's implementation/parity lane.

## Full-final definition for CPT and Taxonomy

Neither surface may be called fully final until all of the following are explicitly promoted for that surface:

1. reviewed Options Bank with zero unresolved native/market/semantic items;
2. schema-valid Atomic Option Contract with `missing=0` and `unclassified=0`;
3. reviewed UX contract and information architecture;
4. exhaustive gap matrix against the existing runtime implementation;
5. missing runtime behavior implemented through canonical Definition/Policy/Ability/registration owners;
6. Multisite, import/export/migration, security, REST/Ability, accessibility and compatibility evidence as applicable;
7. exact-head CI/runtime/browser/security/parity evidence required by the option contracts;
8. machine lifecycle promotion to `RUNTIME_CERTIFIED`;
9. competitor-parity acceptance promotion to `PRODUCT_PARITY_CERTIFIED`.

A green CPT Runtime or Taxonomy Runtime workflow alone is regression evidence, not full-final certification.

## Next actions

1. Reconcile queue priority 960 to DONE via Issue #463 / merged PR #464.
2. Record this audit as the serialized post-trust gate.
3. Open CPT Builder Options Bank Audit Closure V1 as a non-overlapping planning lane.
4. Open Taxonomy Builder Atomic Option Contract + UX Closure V1 as a non-overlapping planning lane.
5. Keep Custom Tables managed-table execution blocked.
6. Preserve the complete README 56/56 dashboard and machine lifecycle distinctions.

Repository evidence overrides conversational memory.