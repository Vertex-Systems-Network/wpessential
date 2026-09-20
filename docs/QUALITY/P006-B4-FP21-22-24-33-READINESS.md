# P-006 B4 — FP-21 / FP-22 / FP-24 / FP-33 Readiness and Applicability Review

Issue: #1074

Authorization: `GOV-P006-B4-FP21-22-24-33-READINESS-001`

Exact review base: `5f783256fbe1228e1585df9de1dfd995cc9a25f8`

Classification: **NON-RUNTIME READINESS / APPLICABILITY REVIEW / NO FIXTURE EXECUTION / NON-CERTIFYING**

## 1. Purpose and accounting boundary

This review resolves the next dependency questions after Wave 1Q without executing any P-006 fixture.

Formal accounting remains unchanged:

**144 documented / 50 executed / 49 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified Free/Pro pairs / 0 runtime certifications**.

No P-001/CF runtime grant is created or reused. No WordPress runtime, MySQL service, package replacement, migration execution, provider call, production action, deploy or release occurs in this review.

## 2. Original fixture contracts

The protocol wording remains authoritative:

> **FP-21** — Free active with older Pro inside the supported overlap window boots the documented compatibility path.

> **FP-22** — older Free with newer Pro inside a declared overlap window boots only supported functionality.

> **FP-24** — a single incompatible premium adapter does not disable unrelated compatible premium modules when the contract permits per-module degradation.

> **FP-33** — duplicate activation hooks do not run destructive work before compatibility is known.

This review does not redefine those fixtures.

## 3. Current source and evidence truth

### 3.1 Compatibility remains pair-wide

`frameworks/Modules/Compatibility/LocalCompatibilityPreflight.php` evaluates one Free/Pro pair using:

- Free marketing version;
- Pro inclusive minimum/maximum Free version;
- Platform API range;
- Platform schema range;
- Pro schema/package completeness.

Its result publishes one request-local compatibility state and global admission booleans:

- `premium_boot_allowed`;
- `premium_migrations_allowed`.

There is no adapter/module identifier in that binary compatibility decision.

`wpessential-pro.php` reinforces the same boundary:

- only Compatibility source may autoload before a global `compatible` result;
- every other Pro module namespace stays inert until that global result is compatible;
- the full configured premium module set is registered only after the global preflight passes;
- an incompatible result returns before premium module registration.

Entitlement policy is a separate commercial/admission concern, not a module-level binary compatibility contract.

### 3.2 Module manifests do not define adapter-level binary compatibility

`frameworks/Platform/Modules/ModuleManifest.php` provides:

- module id/name/version;
- edition;
- module dependencies;
- minimum Platform version;
- minimum WordPress version;
- minimum PHP version.

It does **not** define a per-module Free-version range, Platform API maximum, capability requirement set, adapter ABI range, or per-module compatibility result.

`EntitlementAwareModuleActivationPolicy` permits/denies premium module activation from entitlement state only. It does not convert the pair-wide binary compatibility result into adapter-specific compatibility.

### 3.3 Deterministic overlap graph now exists

Lane A originally marked FP-21 and FP-22 CROSS-GATE because current product metadata was exact `0.1.0-dev` and no immutable overlap pair existed.

Later Wave 1K established a deterministic test-only overlap graph and reused it in later bounded P-006 work.

Exact accepted identities:

| Node | Marketing version | ZIP SHA-256 | Payload tree SHA-256 |
| --- | --- | --- | --- |
| F0 | `0.1.0-dev` | `2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80` | `0698d1a772704bcf44bea1eefae0c490212d6bb6ee214a4f6a5c33db1bf998b9` |
| F1 | `0.1.1-test-overlap` | `f9401f81b4a65d6f610b05cd445d3d8bdec75b2996296e0deaa13a8a2a265185` | `3e8e56d3887b1f6ed29a66beb0a489d44e547d8b5104c6819e41b91bb6518460` |
| P0 | `0.1.0-dev` | `0b12ada8265f3032641e09a80ba09f9950d3aef8b526b63c2b3665663aca4178` | `08ef083162109a15899dba7b14d1e420f327323b20cd1434cf9e7cfa23af09a7` |
| P1 | `0.1.1-test-overlap` | `96acfb8527e62f3ee52f8ef61b801f770315a3a633502ead845c5c4f145b30ac` | `d9a2d208871d684d50e4eabcdb7d4ffb0e2f51cff160ebb311e47d210c3d2a46` |

Accepted pair identities:

| Pair | Meaning | Expected compatibility | Pair id SHA-256 |
| --- | --- | --- | --- |
| F0/P0 | older baseline | compatible | `c564b2fdc079a02dbba57bee0d90dd64fd683605c0fdbca89ab48323b394f2a0` |
| F1/P0 | newer Free + older supported Pro | compatible | `f5157c46d4a29af6df4929cafeaad831fc50a8d38e469c2df73340766bb3faef` |
| F0/P1 | older supported Free + newer Pro | compatible | `532f93f984bb5ecee1793a4c325c904e84945e35f1f5f682e20b59cbdbc58b26` |

The overlap artifacts are test/evidence candidates. They do not change current production plugin headers, which remain exact development metadata on current main.

Wave 1K used these overlap pairs for FP-45/46/53/60. That prior execution is strong prerequisite/harness evidence but is **not retroactively counted as FP-21 or FP-22 execution**.

### 3.4 FP-33 lifecycle-owner truth

Wave 1H intentionally stopped FP-33 as INCONCLUSIVE after its package scan found migration/lifecycle owner type markers. The failed Wave 1H assertion was the overly broad expectation that no activation **or migration-owner** marker would exist.

Current source distinguishes those concepts.

Current first-party plugin entry files:

- `wpessential.php` contains normal bootstrap and a `plugins_loaded` Free boot callback at priority `-100`;
- `wpessential-pro.php` contains the local compatibility callback at priority `-200`;
- neither current entry file declares `register_activation_hook()`, `register_deactivation_hook()` or `register_uninstall_hook()`.

Current migration owner classes are real and expected. Their existence alone does not prove activation-hook work.

`frameworks/Bootstrap/Plugin.php` creates ordinary platform persistence services and registers baseline Free-owned migrations during normal Free boot. Pro-dependent Custom Tables migration registration is conditional:

1. `proCustomTablesRuntimeAvailable()` requires:
   - Pro package active;
   - request-local compatibility result array;
   - compatibility state `compatible`;
   - `premium_boot_allowed === true`;
   - required Pro runtime classes present.
2. `proCustomTablesMigrationsAllowed()` additionally requires:
   - `premium_migrations_allowed === true`.
3. Only then are Pro-dependent Custom Tables migration owners registered.

`MigrationRunner` separately refuses a destructive migration without an explicit recovery plan.

Therefore Wave 1H's lifecycle-owner discovery is not equivalent to proving a duplicate activation hook can run destructive Pro work before compatibility. A real activation/re-activation fixture is now definable and is the correct way to resolve FP-33.

## 4. FP-21 decision

Original Lane A blocker:

> no older-Pro overlap candidate exists.

That blocker no longer applies to P-006 evidence planning because the deterministic F1/P0 overlap pair exists and is already pinned by exact hashes and expected compatibility.

Decision:

**FP-21 — `READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION`**

This is readiness only. FP-21 remains **NOT EXECUTED**.

### Required future FP-21 fixture shape

A separately authorized disposable runtime tranche should:

1. use exact F1 + P0 candidate identities above;
2. run minimum and reference WordPress/PHP/MySQL cells already accepted for P-006;
3. install/activate Free F1 with older Pro P0;
4. set only a deterministic local test entitlement state if needed to distinguish binary compatibility from entitlement denial; no remote provider call;
5. assert request-local compatibility is `compatible`;
6. assert premium boot/migration admission is allowed by binary compatibility;
7. assert Free CPT/Taxonomy remain available;
8. assert the expected current premium module set can boot when entitlement permits;
9. assert zero outbound WordPress HTTP from compatibility evaluation;
10. record exact F1/P0 hashes/pair id in runtime evidence.

The fixture must not infer FP-21 PASS from Wave 1K's FP-45 execution.

## 5. FP-22 decision

Original Lane A blocker:

> no newer-Pro overlap candidate exists for an older supported Free.

That blocker no longer applies to P-006 evidence planning because exact F0/P1 is a deterministic compatible overlap pair.

Decision:

**FP-22 — `READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION`**

This is readiness only. FP-22 remains **NOT EXECUTED**.

### Required future FP-22 fixture shape

A separately authorized disposable runtime tranche should:

1. use exact F0 + P1 identities above;
2. run minimum and reference P-006 runtime cells;
3. install/activate older Free F0 with newer Pro P1;
4. assert request-local compatibility is `compatible`;
5. prove the runtime loads only functionality supported by the unchanged compatible Platform API/schema profile;
6. use deterministic local entitlement state only as needed to observe premium module activation without provider calls;
7. verify required Free owners remain available;
8. verify no unsupported/newer API shortcut is inferred from matching marketing versions;
9. record exact F0/P1 hashes/pair id and zero compatibility-network calls.

The current test P1 changes overlap metadata rather than introducing a new optional capability/adapter contract. FP-22 therefore tests declared pair overlap, not FP-24-style module degradation.

## 6. FP-24 decision

The fixture wording is conditional:

> when the contract permits per-module degradation.

Current accepted product compatibility does not provide that contract.

Evidence:

- binary compatibility is pair-wide in `LocalCompatibilityPreflight`;
- Pro module autoload/registration is globally gated by the pair result;
- `ModuleManifest` has no adapter/module binary compatibility range;
- no accepted Platform capability registry/optional capability negotiation exists for this purpose;
- entitlement policy is not binary compatibility;
- Lane A explicitly required an applicability decision before inventing an incompatible-adapter fixture.

Decision:

**FP-24 — `N_A_CURRENT_ACCEPTED_CONTRACT`**

This is an applicability decision, not PASS and not execution.

P-006 accounting does not change.

### Reactivation condition

FP-24 becomes applicable only if a separately accepted product contract introduces per-module/per-adapter binary compatibility or optional capability degradation semantics.

If that occurs, planning must define:

- the module/adapter compatibility declaration;
- dependency/capability ownership;
- how incompatible module admission is distinguished from entitlement denial;
- what unrelated compatible premium modules must remain available;
- deterministic candidate artifacts for one incompatible adapter.

P-006 must not invent those semantics by itself.

## 7. FP-33 decision

Wave 1H formal result remains:

**FP-33 — INCONCLUSIVE**

This review does not change that accounting result.

Current source removes the ambiguity that caused the original broad static assertion to stop:

- migration owner classes exist;
- WordPress activation/deactivation/uninstall registration is not present in current first-party entry files;
- Pro compatibility is decided before Free boot (`plugins_loaded -200` versus Free `-100`);
- incompatible Pro compatibility decisions deny premium boot and premium migrations;
- Pro-dependent Custom Tables migration registration in Free boot is compatibility-gated;
- destructive migration execution has an additional recovery-plan requirement.

Decision:

**FP-33 — `READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION`**

The fixture still requires real WordPress activation evidence before INCONCLUSIVE may be resolved.

### Required future FP-33 fixture shape

A separately authorized runtime tranche should combine a static package scan with real WordPress plugin activation/re-activation.

Minimum required scenarios:

1. compatible baseline activation to establish ordinary Free migration-state behavior;
2. incompatible newer-Free/older-Pro pair such as exact F2/P0;
3. incompatible older-Free/newer-breaking-Pro pair such as exact F1/P2;
4. activate Pro through WordPress's plugin activation API;
5. repeat activation/deactivation/reactivation lifecycle where WordPress permits it;
6. start fresh request processes after each lifecycle operation.

Required assertions:

- package scan still finds no first-party `register_activation_hook`, `register_deactivation_hook` or `register_uninstall_hook`;
- incompatible pair decision is established before premium contribution;
- premium boot and premium migrations remain denied;
- no Pro-dependent migration owner is registered/executed under incompatibility;
- ordinary Free-owned baseline migrations are identified separately and are not misreported as Pro lifecycle work;
- migration-state rows/ids do not gain Pro-only entries under incompatible activation/re-activation;
- no destructive migration/query attributable to Pro activation occurs;
- no premium module registers;
- Free remains usable according to the selected mismatch fixture;
- no outbound compatibility/provider network call occurs;
- repeated lifecycle actions do not create duplicate migration effects.

Only that separately authorized formal runtime evidence may change FP-33 from INCONCLUSIVE to PASS or FAIL.

## 8. Decision matrix

| Fixture | Pre-review state | Review decision | Counter change |
| --- | --- | --- | --- |
| FP-21 | NOT_EXECUTED / CROSS-GATE | **READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION** | none |
| FP-22 | NOT_EXECUTED / CROSS-GATE | **READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION** | none |
| FP-24 | NOT_EXECUTED / applicability undecided | **N_A_CURRENT_ACCEPTED_CONTRACT** | none |
| FP-33 | INCONCLUSIVE | **READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION; formal result remains INCONCLUSIVE** | none |

## 9. Timeout and execution boundary

This B4 review intentionally performs no runtime work.

No:

- WordPress install;
- MySQL service;
- candidate build;
- package replacement;
- activation execution;
- migration execution;
- artifact matrix;
- provider/network workflow;
- runtime workflow.

Only repository source/evidence reads and lightweight governance validation belong to this tranche.

## 10. Non-promotion boundary

This review does not establish:

- FP-21 PASS;
- FP-22 PASS;
- FP-24 PASS;
- FP-33 PASS or FAIL;
- any new executed-fixture count;
- permanent P-001/CF;
- a certified Free/Pro pair;
- a runtime certification;
- module-level compatibility semantics;
- updater/TUF trust;
- schema/migration certification;
- provider/license/billing behavior;
- multisite behavior;
- production deployment/release;
- ADR-0010 acceptance;
- Issue #947 authority.

Formal accounting remains:

**144 documented / 50 executed / 49 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

## 11. Terminal readiness conclusion

- **FP-21:** READY for separate formal runtime authorization using exact F1/P0.
- **FP-22:** READY for separate formal runtime authorization using exact F0/P1.
- **FP-24:** N/A under the current accepted pair-wide compatibility contract; reactivate only if module/adapter-level compatibility semantics are separately accepted.
- **FP-33:** READY for a separate real WordPress activation/re-activation fixture, but its existing formal result remains INCONCLUSIVE until that fixture executes.
