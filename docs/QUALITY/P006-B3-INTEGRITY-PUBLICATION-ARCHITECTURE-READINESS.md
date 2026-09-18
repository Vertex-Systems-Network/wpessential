# P-006 B3 Package Integrity / Publication Architecture Readiness

Issue: #1049

Authorization: `GOV-P006-B3-INTEGRITY-PUBLICATION-REVIEW-001`

Classification: **NON-RUNTIME ARCHITECTURE REVIEW / NO FIXTURE EXECUTION**

## 1. Purpose

This review resolves the architecture question that blocks full FP-49…52 interruption/partial-replacement evidence.

It does not execute FP-49, FP-50, FP-51 or FP-52. It does not change product runtime source, implement an updater, implement a hash manifest, promote ADR-0010, or change P-006 accounting.

Formal accounting remains:

**144 documented / 46 executed / 45 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified Free/Pro pairs / 0 runtime certifications**.

## 2. Repository source facts

### 2.1 Free entry/bootstrap

Current `wpessential.php`:

1. publishes metadata before Composer;
2. checks `vendor/autoload.php` only with `is_readable()`;
3. returns safely when it is missing/unreadable;
4. executes `require_once $autoload` when readable;
5. only after that publishes `WPE_FREE_BOOTSTRAP_READY=true`;
6. schedules the Free kernel on `plugins_loaded` priority -100.

Therefore:

- missing/unreadable Free autoload is application-level detectable;
- readable corrupt/truncated autoload can reach PHP parsing before application fail-closed state exists;
- a corrupt/truncated `wpessential.php` is even earlier: plugin-internal PHP cannot verify its own entry file before PHP parses that entry file.

### 2.2 Pro entry/bootstrap

Current `wpessential-pro.php`:

- owns a narrow Compatibility namespace loader;
- may `require_once` a readable compatibility file;
- checks the compatibility-preflight class before premium admission;
- checks required premium module files with `is_readable()`;
- verifies required Free runtime classes with `class_exists()`;
- denies premium boot/migrations for several missing-file states.

Therefore:

- missing preflight/module files can fail closed;
- readable corrupt/truncated compatibility/module PHP can still reach parsing/autoload;
- a corrupt/truncated `wpessential-pro.php` cannot be protected by code inside that same entry file.

### 2.3 Build/package state

The current distribution build produces deterministic complete ZIP artifacts and P-006 evidence pins ZIP/payload-tree identities.

Repository search finds no accepted runtime package-integrity manifest that is verified before Composer/module autoload.

No accepted publication contract currently says that every supported Free/Pro replacement publishes a whole live directory atomically.

## 3. WordPress-core publication facts

Official WordPress core semantics must not be overstated.

`Plugin_Upgrader::install(... overwrite_package=true)` enters `WP_Upgrader::install_package()` with destination clearing enabled.

WordPress `move_dir($from, $to, $overwrite)` currently:

1. deletes an existing destination when overwrite is enabled;
2. tries the active WordPress Filesystem implementation's `move()`;
3. when that move fails, creates the destination and falls back to recursive `copy_dir()`.

WordPress update flows also expose temporary-backup/restore machinery, but this does not transform every install/replacement/filesystem path into a universal atomic directory switch.

Architecture consequence:

> **Generic WordPress plugin replacement is not accepted as a universal atomic-publication guarantee.**

A successful direct/same-filesystem move in one environment may be effectively generation-like, but the architecture cannot infer that property for all supported WordPress Filesystem methods or fallback paths.

## 4. Architecture option decision

### Option A — transport-only atomic publication

**Not accepted as a current universal contract.**

A dedicated publisher could stage and atomically switch generations under controlled filesystem assumptions, but current generic WordPress replacement semantics can fall back to recursive copy.

This model may be valid for a future explicitly constrained publisher, not as a statement about all current WordPress installs.

### Option B — plugin-internal pre-load integrity only

**Rejected as a sufficient solution.**

A generated manifest could protect non-entry runtime PHP before Composer/module autoload only after a trusted verifier is already executing.

It cannot protect an already readable-corrupt `wpessential.php` or `wpessential-pro.php`, because PHP must parse the entry file before plugin-owned verification can run.

A sidecar manifest is also not authoritative unless the entry/verifier establishes the package generation and manifest identity it is trusting.

### Option C — combined external publication boundary + internal manifest

**Technically complete direction, but not implementation-ready until publication ownership is accepted.**

A viable combined design would require:

1. an external publication authority to guarantee that the plugin entry file and its embedded/anchored manifest identity are either complete/trusted or not executable;
2. a generated manifest, anchored by that trusted entry/generation, covering runtime-critical non-entry PHP by path + size + SHA-256 + package identity;
3. verification before Free Composer include and before Pro compatibility/module autoload;
4. fail-closed behavior before premium admission/migrations;
5. exact post-publication destination verification before execution resumes.

This can tolerate non-entry partial/corrupt files, but only after the entrypoint trust problem is solved externally.

### Option D — external installer/publication authority only

**Potentially sufficient for update-time integrity, but no accepted current owner provides the required universal guarantee.**

If an external controller can:

- stage a complete artifact outside the live tree;
- verify its exact artifact/payload identity;
- exclude every relevant execution channel while a live tree can be absent/partial;
- publish/copy the destination;
- verify the exact live tree before re-enabling execution;
- restore the old complete generation or remain fail-closed on error/crash;

then arbitrary partial live trees do not need to become application-observable states.

However, current generic WordPress replacement is not accepted as proving all of those properties across Filesystem methods. Automatic updater/TUF/signature trust is a separate gate.

## 5. Selected architecture conclusion

The review selects the following **architecture ordering**, not a runtime implementation:

### Primary requirement — external publication/execution boundary

Full FP-49…52 cannot be certified solely by code inside WPEssential.

An external publication owner must first define and prove the entrypoint safety boundary.

That contract must identify, separately for Free and Pro:

- who owns package staging and destination publication;
- which WordPress Filesystem methods are supported;
- what happens when `move()` falls back to recursive copy;
- how web, cron, CLI and other executable requests are excluded while the live generation can be incomplete;
- how the exact staged and final trees are verified;
- how old-complete/new-complete/failed-publication recovery works.

### Secondary requirement — internal manifest is defense-in-depth, not the first prerequisite

A runtime-critical-file manifest may still be valuable after the external entrypoint boundary exists, particularly for detecting non-entry corruption before Composer/module autoload.

It must not be implemented first and must never be described as protecting a corrupt plugin entrypoint.

### Immediate next prerequisite

The next repository-changing B3 prerequisite is:

> **a bounded publication-owner contract and executable harness design for Free and Pro that proves entrypoint execution exclusion + complete staged/final tree verification across the intended WordPress Filesystem profile, including the recursive-copy fallback case or an explicit fail-closed exclusion of that case.**

Until that owner/contract exists, no full FP-49…52 execution tranche is dependency-ready.

This prerequisite is narrower than automatic updater/TUF: it is local publication/integrity semantics. If implementation requires remote update metadata, signing or WPE-controlled package delivery, that portion must stop at the separate updater/TUF trust gate.

## 6. Cut-point readiness matrix

| Cut point | Current classification | Reason / next dependency |
| --- | --- | --- |
| F-CUT-1 — Free entry metadata present, autoload missing/unreadable | **BOUNDED SUBCASE TECHNICALLY READY / NOT FULL FP-49** | Current Free entry fails closed before Composer. Does not resolve entrypoint corruption or full interrupted-replacement semantics. |
| F-CUT-2 — Free autoload readable but corrupt/truncated | **BLOCKED** | Current entry only checks readability. Requires trusted entrypoint plus pre-include integrity or external execution exclusion/final-tree verification. |
| F-CUT-3 — Free bootstrap ready, selected Pro-required Free class missing | **PARTIAL** | Pro can publish `free_bootstrap_incomplete`; exact safe immutable class coverage still must be pinned. |
| F-CUT-4 — selected Free Platform/bootstrap PHP readable but truncated | **BLOCKED** | Can parser-fail during autoload. Requires external publication boundary and/or trusted manifest anchored by a safe entrypoint. |
| P-CUT-1 — Pro entry present, preflight missing/unreadable | **BOUNDED SUBCASE TECHNICALLY READY / NOT FULL FP-50/51** | Current Pro path publishes `pro_package_incomplete` and leaves Free usable. |
| P-CUT-2 — Pro preflight readable but truncated | **BLOCKED** | Compatibility autoload can parse-fail before safe state. |
| P-CUT-3 — Pro preflight complete, required module missing | **BOUNDED SUBCASE READY AFTER FIXED FILE-LIST PIN** | Current package completeness path can deny premium runtime before module registration. |
| P-CUT-4 — required Pro module readable but truncated | **BLOCKED** | Readability can pass and later class autoload can parse-fail. |
| P-CUT-5 — Pro entry itself partially copied | **EXTERNALLY OWNED / PLUGIN-INTERNAL UNPROVABLE** | PHP parses the entry before Pro-owned code can verify it. Requires publication/execution authority outside that entry file. |

Additional entrypoint truth:

- an equivalently partial/corrupt Free `wpessential.php` state is also **externally owned / plugin-internal unprovable**;
- this state must be covered by the publication-owner contract even though the #1040 cut-point list focused Free detail on the autoloader and Platform files.

## 7. Effect on formal fixtures

### FP-49 — interrupted Free replacement

**Still BLOCKED as a full fixture.**

The missing-autoload subcase is safe, but arbitrary replacement interruption can include a corrupt entry/autoload/non-entry file. The publication owner must first define whether those states are excluded from execution or detected before include.

### FP-50 — interrupted Pro replacement

**Still PARTIAL / NOT FULL-FIXTURE READY.**

Missing preflight/module states are fail-closed. Corrupt entry/preflight/module states remain unresolved until the publication owner + integrity boundary exists.

### FP-51 — partial filesystem replacement/missing Pro file

**Still BLOCKED AS WRITTEN for full remove/truncate coverage.**

A missing-file matrix can be executed later, but truncation coverage requires the publication/integrity prerequisite.

### FP-52 — partial filesystem replacement/missing Free platform file

**Still BLOCKED AS WRITTEN.**

Missing autoload is safe and one missing required-Free-class path may be testable after exact class pinning, but corrupt/truncated Free entry/bootstrap/Platform files remain unresolved.

No formal P-006 counter changes follow from this review.

## 8. Recovery invariant for the future publication owner

A future accepted publication owner must make one of these states externally provable:

1. **OLD_COMPLETE_EXECUTABLE** — old exact package/generation remains live;
2. **EXECUTION_EXCLUDED** — live destination may be absent/partial, but no supported execution channel can parse it;
3. **NEW_COMPLETE_VERIFIED** — final live tree exactly matches the intended package generation and may become executable;
4. **RECOVERED_OLD_COMPLETE** — failed publication restored the exact previous complete generation;
5. **STOP_REVIEW** — exact recovery cannot be proven; execution remains excluded and the fixture cannot claim PASS.

It must not declare success while the live tree is a mixed/partial generation.

It must not repair selected files in place and then reuse that mutated tree as another evidence cell.

## 9. Manifest requirements if separately authorized later

If a local runtime manifest is later approved, minimum requirements are:

- generated only from the deterministic final package payload;
- package/generation identity;
- normalized relative path;
- byte size;
- SHA-256;
- fixed runtime-critical coverage, not opportunistic samples;
- verification before Free Composer include / Pro compatibility or premium-module autoload;
- no remote lookup on ordinary boot;
- no entitlement/license state as integrity evidence;
- deterministic error state and remediation;
- measured bounded boot-cost evidence;
- explicit manifest trust anchor tied to the externally safe entry/generation.

The manifest implementation must not include signing/TUF unless separately authorized.

## 10. Future harness shape

Do not use one monolithic job.

### Publication-owner prerequisite harness

- deterministic complete artifact graph build: <=15m;
- publication mechanism probe per Filesystem profile: <=12m;
- interrupted publication cells: <=12m each;
- fresh PHP process for every observation;
- fail-fast false;
- stale-run cancellation;
- per-cell immutable state manifest and evidence artifact.

Every state manifest must identify:

- staged artifact hash;
- intended package generation;
- live-tree state;
- entrypoint state;
- old/new/missing/unreadable/corrupt files;
- execution-exclusion state;
- final tree hash;
- recovery state.

### Later FP-49…52 execution

Only after the publication-owner prerequisite is accepted. The future execution must preserve the existing timeout-safe matrix shape and must not weaken fixture expectations merely to manufacture PASS.

## 11. Stop conditions

Stop rather than force an architecture PASS if:

1. a readable-corrupt plugin entrypoint can be parsed by a supported execution channel during publication;
2. recursive-copy fallback is supported but there is no proven execution exclusion until final-tree verification;
3. an internal manifest is claimed to protect its own already-corrupt entry file;
4. the manifest can be independently replaced without a trusted generation anchor;
5. a partial/mixed live tree can authorize premium boot or migrations;
6. recovery depends on an unverified in-place repair;
7. implementation crosses into updater/TUF/signature/provider/release scope without separate authorization;
8. fixture applicability is silently changed instead of explicitly reviewed.

## 12. Trust-boundary separation

This review concerns local executable package integrity/publication only.

It does not establish:

- remote update metadata authenticity;
- TUF/signature/key-rotation trust;
- Product License or entitlement;
- Membership authorization;
- schema/migration recovery;
- rollback/downgrade certification;
- production deployment/release authority.

Those remain separate gates.

## 13. Terminal readiness decision

The repository now has a precise reason not to execute full FP-49…52 yet.

- Generic WordPress replacement is **not accepted as universally atomic**.
- Plugin-internal integrity alone is **not sufficient** because the entrypoint is parsed first.
- An external publication/execution boundary is the **mandatory first prerequisite**.
- A runtime-critical-file manifest is a possible **secondary defense**, not the first unblocker.
- Missing-file-only subcases remain useful later but cannot be promoted to full FP-49…52 results.

Therefore the next B3 implementation/review tranche, if authorized, should define and validate the **publication owner contract/harness**, not execute FP-49…52 and not add a standalone manifest first.
