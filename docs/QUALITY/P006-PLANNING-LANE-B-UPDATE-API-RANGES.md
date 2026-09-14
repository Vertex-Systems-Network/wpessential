# WPEssential — P-006 Planning Lane B: Update Order and Platform API Range Evidence Map

Status: **PLANNING / EVIDENCE-DESIGN ONLY — EXECUTION NOT AUTHORIZED**  
Parent gate: **Issue #924**  
Worker lane: **Issue #926**  
Coordination gate: **Issue #930 / merged PR #931**  
Dependency input: **Lane A / Issue #925 / merged PR #932**  
Planning anchor: **current `main` after PR #932**  
Fixture scope: **FP-45…FP-76**  
P-006 truth: **144 documented / 0 executed / 0 passed / 0 failed / 0 certified pairs / 0 runtime certifications**

## 1. Purpose and boundary

This document plans executable evidence for independent Free/Pro update order, interrupted replacement, rollback, stale-request safety, Platform API range semantics and deprecation windows.

It does **not**:

- run a WordPress update, manual replacement, rollback or restore;
- implement or exercise an updater/TUF client;
- build P-006 candidate artifacts;
- modify Free/Pro compatibility metadata or runtime source;
- mutate a database or execute migrations;
- execute any FP fixture;
- change ADR-0010 status or P-006 counters;
- deploy or release.

All future execution remains subject to the explicit P-006-scoped ADR-0014 owner authorization gate and any additional updater/migration/recovery authorization required by the selected fixture.

## 2. Existing non-certifying inputs

Current repository evidence provides a useful starting point but does not execute FP-45…FP-76:

1. `docs/DECISIONS/ADR-0010-free-pro-compatibility.md`
   - independent Free/Pro updates are explicitly non-atomic;
   - overlap windows are the preferred release policy;
   - breaking Platform API change requires deprecation overlap;
   - partial/interrupted replacement, stale requests and rollback must fail safely;
   - updater/package trust remains separate from runtime compatibility.
2. `wpessential.php` / `wpessential-pro.php`
   - Free marketing version, Platform API and platform-schema generation are separate values;
   - Pro publishes inclusive minimum/maximum Free, Platform API and platform-schema ranges;
   - current development profile uses an exact single-point range (`0.1.0-dev`, API `0.1.0`, schema `1`), so it does **not** yet provide a real multi-version overlap pair.
3. `frameworks/Modules/Compatibility/LocalCompatibilityPreflight.php`
   - strict local range parsing;
   - fail-closed below/above Free version, Platform API and platform schema;
   - contradictory ranges fail as invalid Pro metadata;
   - compatibility is request-local and has no remote call or persistent compatibility cache.
4. `tests/Unit/Modules/Compatibility/LocalCompatibilityPreflightTest.php`
   - exact compatible boundary;
   - too-old/too-new Free/API/schema;
   - contradictory Free/API/schema ranges;
   - does not yet constitute packaged multi-version update/deprecation evidence.
5. deterministic Free/Pro packaging and SHA-256 package manifests;
6. packaged bootstrap verifier exercises matching-head compatible and deliberately incompatible load orders, but not actual update transactions or rollback;
7. current Platform Compatibility Matrix supplies reusable disposable WP/PHP infrastructure but is not a P-006 multi-version artifact matrix.

## 3. Core update-evidence invariant

Every update fixture must model Free and Pro as **independently replaceable immutable artifacts**. A fixture must never assume both packages change atomically.

For every step in a future sequence record:

- Free artifact SHA-256 + marketing version + Platform API + platform schema;
- Pro artifact SHA-256 + marketing version + supported Free/API/schema ranges + Pro schema;
- which package changed in that step;
- package filesystem state (`complete`, `partial`, `missing`, `old`, `new`);
- request/process started before or after replacement;
- canonical compatibility result;
- premium boot and migration-admission booleans;
- Free CPT/Taxonomy availability;
- durable schema generation before/after when relevant;
- whether updater trust was evaluated separately;
- result and recovery action.

Compatibility PASS may never be inferred from update success, package signature/trust, entitlement or equal marketing versions.

## 4. Candidate multi-version graph — design only

Formal FP-45…FP-76 execution needs a deliberately constructed immutable artifact graph. The exact versions are selected by a later authorized gate; conceptual roles are:

- **F0** — older Free; API generation A; schema generation S0;
- **F1** — overlap Free; API generation A or compatible A+minor; schema supported by both P0/P1;
- **F2** — breaking/new Free; API generation B or schema beyond P0 support;
- **P0** — older Pro supporting F0/F1 overlap;
- **P1** — transition Pro supporting F1 and the new API/deprecation window;
- **P2** — later Pro for F2 after old contract removal.

Each node must be a real immutable ZIP with recorded SHA-256. Synthetic metadata-only fixtures may validate parser behavior, but they cannot substitute for packaged update/rollback fixtures.

## 5. Fixture plan — FP-45…FP-60 independent update/interruption

Planning status labels:

- `STRONG EXISTING INPUT` — useful current behavior exists, but still no P-006 execution.
- `PARTIAL EXISTING INPUT` — current implementation supports an assertion but lacks real update evidence.
- `GAP` — future executable harness/candidates are required.
- `CROSS-GATE` — another governed evidence domain is mandatory.

| FP | Status | Existing input | Future authorized fixture / expected result | Stop / dependency |
| --- | --- | --- | --- | --- |
| FP-45 | GAP | ADR requires overlap; current range is single-point only. | Start F0+P0, replace Free with F1 while P0 remains; requests before/after replacement stay non-fatal and end compatible inside overlap. | Requires real F0/F1/P0 immutable overlap artifacts. |
| FP-46 | GAP | Same. | Start F0+P0, replace Pro with P1 while F0 remains inside P1 overlap; compatible path remains available. | Requires P1 declaring real overlap. |
| FP-47 | PARTIAL EXISTING INPUT | Local preflight fails closed on too-new Free/API; mismatch packaged boot preserves Free. | Replace F1→F2 while old P0 remains incompatible; premium becomes inert, Free CPT/Taxonomy stays functional, no fatal/migration. | STOP on premium registration, fatal, or destructive action. |
| FP-48 | PARTIAL EXISTING INPUT | Local preflight fails closed on too-old Free/API. | Replace P0→P2 first while F0/F1 is below P2 support; Pro degrades safely until Free catches up. | STOP on fatal or mutation before compatible pair. |
| FP-49 | GAP | Free publishes bootstrap-ready only after autoloader, and Pro can detect missing/incomplete Free metadata, but no interrupted Free replacement fixture exists. | Fault-inject Free replacement at defined file-copy cut points; every resulting state must be detectable/non-runnable without Pro migration. | Needs filesystem fault injector and disposable install. |
| FP-50 | PARTIAL EXISTING INPUT | Pro checks preflight file and required module-file completeness; Free owns its own runtime. | Fault-inject Pro replacement; Free remains usable, premium disabled with safe package-incomplete result. | STOP if partial Pro breaks Free. |
| FP-51 | PARTIAL EXISTING INPUT | Pro package-completeness loop and preflight-missing branch fail closed. | Remove/truncate one Pro file at a time from disposable candidate classes representing bootstrap/preflight/module/runtime categories; preserve data and Free operation. | Coverage set must be fixed before execution; do not sample only one easy file. |
| FP-52 | PARTIAL EXISTING INPUT | Free autoloader/bootstrap-ready guard + Pro free-incomplete states. | Remove/truncate representative Free Platform/bootstrap file during disposable replacement; Pro must never mutate/load premium against partial Free. | STOP on premium boot/migration or fatal public/admin path that should degrade. |
| FP-53 | STRONG EXISTING INPUT | Compatibility evaluator is pure/request-local and does not write compatibility state. | Retry same exact artifact replacement and repeat preflight; effective compatibility decision must be identical and no compatibility-layer duplicate mutation occurs. | Updater/package layer may have separate idempotency, not inferred here. |
| FP-54 | CROSS-GATE | Compatibility can return to supported state after artifact change, but DB downgrade safety is not proven. | F1+P1 → F2+P1 mismatch → rollback Free to exact F1; compatibility restores without automatic destructive DB downgrade. | Lane C owns schema/migration/rollback evidence. |
| FP-55 | CROSS-GATE | Same principle for Pro. | Roll back Pro to prior supported package only where persisted schema remains supported; compatibility restores, data preserved. | Lane C schema support required. |
| FP-56 | CROSS-GATE | ADR states older code vs newer unsupported schema must block mutation. | Prepare newer durable schema through separately authorized Lane C fixture, then run older code; expect degraded/read-only recovery state, no downgrade writes. | Lane C + backup/restore gate mandatory. |
| FP-57 | GAP | ADR explicitly says independent updates are non-atomic; no automatic updater execution is authorized/implemented for P-006. | Future sandbox updater scheduler intentionally selects each possible package order and interruption point; compatibility must not assume transactionality. | Separate updater/TUF authorization required if product updater is used. |
| FP-58 | GAP | Packaged include-order behavior exists, not manual upload/replacement evidence. | Use WordPress manual ZIP replacement/file replacement in disposable site for same version graph; effective compatibility guarantees must match approved automated path. | Needs disposable filesystem + WordPress upgrade harness. |
| FP-59 | CROSS-GATE | Compatibility result is request-local, reducing stale cross-request authority; no concurrent stale-browser/update fixture. | Hold an old request/process while filesystem switches to new pair; stale request must not authorize migration using pre-replacement result. | Lane C migration gate + concurrency harness; STOP on mixed-state mutation. |
| FP-60 | GAP | Runtime diagnostics expose version/API/schema state, while package manifests hold SHA-256; no post-update health record combines exact artifact identities. | After each successful/failed update step, evidence collector records exact Free/Pro hashes + metadata + canonical state without making diagnostics a trust authority. | Do not require production UI to expose hashes; evidence harness may own them. |

## 6. Fixture plan — FP-61…FP-76 Platform API ranges/deprecation

| FP | Status | Existing input | Future authorized fixture / expected result | Stop / dependency |
| --- | --- | --- | --- | --- |
| FP-61 | STRONG EXISTING INPUT | Exact API `0.1.0` against exact min/max is accepted by unit evidence. | Pure metadata + packaged pair exact match; expect `compatible` when all other dimensions pass. | P-006 run still required. |
| FP-62 | PARTIAL EXISTING INPUT | Range comparison is inclusive; current min=max does not independently demonstrate a lower boundary in a wider range. | Use declared `[A,B]` range with installed API exactly A; expect accepted. | Real range candidate or authorized synthetic parser fixture. |
| FP-63 | PARTIAL EXISTING INPUT | Same. | Installed API exactly B; expect accepted. | Same. |
| FP-64 | STRONG EXISTING INPUT | Unit evidence covers API below minimum. | Package/parser fixture below A; fail `platform_api_too_old`, premium blocked. | STOP on permissive boot. |
| FP-65 | STRONG EXISTING INPUT | Unit evidence covers API above maximum; packaged mismatch prototype covers a too-new API. | Package/parser fixture above B; fail `platform_api_too_new`, premium blocked. | STOP on permissive boot. |
| FP-66 | PARTIAL EXISTING INPUT | Evaluator uses strict numeric API syntax and invalid Pro metadata collapses fail-closed; current unit table emphasizes contradictory range, not every malformed syntax variant. | Table malformed min/max strings, empty/null/non-string/pre-release forms; every invalid declaration fails closed. | Lane E may fuzz beyond fixed cases. |
| FP-67 | STRONG EXISTING INPUT | Unknown/new major above current max resolves too-new. | Use next unknown major; expect fail closed with update-Pro remediation, never wildcard acceptance. | If capability negotiation later supports majors differently, contract must explicitly change first. |
| FP-68 | GAP | Platform API is separate from marketing version, but current Pro also pins exact marketing version range. | Real overlap profile changes marketing version while API remains compatible; verify compatibility follows declared ranges/capabilities rather than equality shortcut. | Requires deliberate Free marketing-range policy in candidate graph. |
| FP-69 | GAP | ADR allows optional premium adapter/module degradation, but local pair preflight currently has no Platform capability registry/negotiation. | First define accepted optional capability contract; remove one optional capability and prove only dependent module/adapter degrades. | Do not invent capability semantics inside P-006. May become N/A for V1 if no optional capability contract. |
| FP-70 | GAP | Same capability gap. | Missing required capability must block only dependent premium bootstrap path before mutation, according to accepted dependency contract. | Requires module/capability ownership semantics. |
| FP-71 | GAP | ADR documents deprecation-window policy; no real old+new API overlap artifacts exist. | F1 retains deprecated API while introducing replacement; P0 remains functional through promised window. | Requires versioned artifacts and accepted deprecation ledger. |
| FP-72 | GAP | No dedicated deprecation-use telemetry contract identified. | Execute P0 against overlap Free; record safe bounded deprecation-use evidence without token/account/user-data leakage. | Lane E owns observability/privacy assertions. |
| FP-73 | GAP | No release gate currently ties deprecated API removal to a machine-readable promised window. | Attempt disposable release candidate that removes API before window; release evidence must fail. | Requires release/deprecation contract and separate release-validation implementation gate. |
| FP-74 | GAP | No transition artifact graph exists. | Pro P1 adopts replacement API while Free still supports both; prove P1 works before old API removal. | Needs F1/P1 immutable candidates. |
| FP-75 | GAP | Current preflight negotiates version ranges, not capability presence. | Once capability contract exists, run admin/frontend/REST/CLI contexts and compare effective negotiation result; it must be deterministic. | May be N/A until capability negotiation is accepted. |
| FP-76 | STRONG EXISTING INPUT | ADR-0010 + readiness contract define strict numeric inclusive ranges; `LocalCompatibilityPreflight::evaluate()` is local and independent of remote licensing. | Formal parser/evaluator fixtures execute without WordPress remote/provider services and record identical range semantics. | Remote service use is a failure for this fixture. |

## 7. Future harness architecture — no implementation in this lane

### U1 — Immutable compatibility graph registry

A machine-readable test-only registry selected by the future execution issue should list each candidate artifact:

- artifact ID (`F0`, `F1`, `F2`, `P0`, `P1`, `P2`);
- SHA-256;
- plugin version;
- Platform API;
- platform/Pro schema generation;
- supported counterpart ranges;
- deprecated/provided capability set if an accepted capability contract exists;
- exact source commit and build evidence.

The registry is evidence metadata, not a product updater feed.

### U2 — Update sequencer

A disposable WordPress harness should:

1. install a selected baseline pair;
2. snapshot filesystem + DB;
3. replace exactly one package;
4. start selected request contexts;
5. record compatibility and mutation admission;
6. optionally replace the second package;
7. collect health evidence;
8. restore clean state before the next scenario.

It must support Free-first and Pro-first sequences without assuming one transaction.

### U3 — Interruption/fault injector

For FP-49…FP-52 and FP-59, replacement must be stoppable at predefined boundaries such as:

- bootstrap copied but vendor/autoload absent;
- metadata/bootstrap copied but Platform files incomplete;
- Pro bootstrap copied but preflight absent;
- preflight present but one required Pro module absent;
- old request alive while replacement advances.

Fault injection must operate only in disposable fixtures.

### U4 — Pure API range matrix

Reuse the local evaluator as a no-network parser target with a generated finite matrix covering:

- exact/min/max;
- below/above;
- malformed/empty/null/non-string;
- contradictory ranges;
- unknown major;
- marketing-version change independent of API change.

Pure parser evidence is necessary but cannot replace packaged multi-version fixtures where the protocol requires actual artifacts.

### U5 — Deprecation-window ledger and validator

Before FP-71…FP-74 can execute, an accepted machine-readable/testable deprecation contract should define:

- API/capability identifier;
- introduced replacement;
- first deprecated release/API generation;
- minimum promised overlap endpoint;
- consuming Pro transition release;
- removal eligibility condition.

Release-validation evidence should reject early removal. This does not mean P-006 planning itself is authorized to implement a release system.

## 8. Request/concurrency model

Update evidence must distinguish at least:

- request starts and ends entirely before replacement;
- request starts after complete replacement;
- old process/request spans replacement;
- two concurrent requests observe different physically complete artifact generations;
- one request sees intentionally incomplete filesystem state during fault injection.

A request-local compatibility result is authoritative only for the package state that request actually evaluated. It must never be reused as proof that a later request or a different artifact pair is compatible.

Any migration/mutation action observed during a mixed or unverified state is a stop-the-line defect.

## 9. Recovery assertions

Every update/interruption fixture must define recovery **before** execution:

- complete the interrupted replacement with the intended immutable artifact;
- or restore exact previous package artifact;
- restore DB snapshot only where the separate migration/recovery contract permits it;
- clear/restart process/opcache according to the supported deployment model;
- re-evaluate compatibility from local authoritative metadata;
- verify Free CPT/Taxonomy continuity;
- verify Pro data/configuration was not deleted merely because the pair mismatched.

Rollback never implies an automatic database downgrade.

## 10. Stop-the-line additions for Lane B

Stop P-006 update/range execution immediately if any confirmed case shows:

1. either package update order is treated as an atomic transaction requirement;
2. partial Free or Pro replacement enables premium boot/migration;
3. a breaking update fatals instead of entering an expected fail-closed state where safe degradation is possible;
4. rollback writes against unsupported newer schema;
5. a stale request authorizes mutation from a compatibility decision created before artifact replacement;
6. unknown/malformed API range becomes unrestricted compatibility;
7. marketing-version equality overrides incompatible Platform API/schema;
8. marketing-version difference alone rejects a pair whose accepted range/API/schema contract says it is supported;
9. deprecated API is removed before the accepted overlap promise;
10. update health evidence cannot identify the exact artifacts actually executed.

## 11. Cross-gate dependencies

| Dependency | Affected fixtures | Requirement |
| --- | --- | --- |
| P-006 scoped ADR-0014 consent | FP-45…FP-76 | Mandatory before any executable fixture. |
| P-001 accepted compatibility floor | All packaged WordPress update fixtures | Exact supported WP/PHP/database matrix. |
| Lane A artifact identity/boot | All | Immutable candidate hashes + baseline safe boot. |
| Lane C schema/migrations | FP-54…FP-56, FP-59 | No rollback/migration certification without schema-state evidence. |
| Lane E security/observability | FP-60, FP-72 and concurrency logging | Redacted evidence/telemetry. |
| Updater/TUF gate | FP-57 and any fixture using product updater | Separate authorization and trust evidence. Manual disposable replacement can test runtime compatibility without certifying updater trust. |
| Release/deprecation governance | FP-71…FP-74 | Machine-testable overlap promise and removal rule. |
| Optional capability contract | FP-69, FP-70, FP-75 | Explicit applicability; otherwise mark N/A rather than invent semantics. |

## 12. Recommended future execution slices

After prerequisites are separately authorized:

1. **B1 — Pure range semantics**: FP-61…FP-68 and FP-76; no package replacement or DB mutation.
2. **B2 — Compatible independent update order**: FP-45, FP-46, FP-53, FP-60 with immutable overlap pair.
3. **B3 — Breaking/interrupted replacement**: FP-47…FP-52, FP-58 after filesystem fault-injection harness review.
4. **B4 — Rollback/stale concurrency**: FP-54…FP-56, FP-59 only with Lane C recovery evidence.
5. **B5 — Automatic updater order**: FP-57 only under separate updater/TUF authorization.
6. **B6 — Deprecation/capabilities**: FP-69…FP-75 only after accepted capability/deprecation contracts and real transition artifacts exist.

## 13. Readiness conclusion

Lane B is **PLANNING COMPLETE / EXECUTION BLOCKED** when this document is accepted.

The current code already has a useful strict inclusive range evaluator, request-local fail-closed compatibility state and deterministic artifacts, but it does not yet provide the multi-version immutable artifact graph required to certify independent update order or deprecation windows. The largest gaps are real overlap candidates, interrupted replacement/fault injection, rollback + schema safety, stale-request concurrency, post-update exact-artifact health evidence, machine-enforced deprecation promises and an explicit optional Platform capability contract.

No FP-45…FP-76 fixture is executed by this document. ADR-0010 remains Proposed and all P-006 counters remain unchanged.
