# P-006 Lane B3 Fault-Injection / Manual-Upgrade Harness Readiness Review

Review authority: `GOV-P006-B3-HARNESS-REVIEW-001`
Source issue: #1040
Exact review base: `cee1e4ba2a441218a26c615b358377bcbc54f33d`
Review type: **NON-RUNTIME / NO FP EXECUTION**

## 1. Scope and non-promotion boundary

This review decides whether the remaining Lane B3 fixtures are dependency-ready:

- FP-49 — interrupted Free package replacement;
- FP-50 — interrupted Pro package replacement;
- FP-51 — partial/missing/truncated Pro package file;
- FP-52 — partial/missing/truncated Free Platform/bootstrap file;
- FP-58 — WordPress manual ZIP upload/replacement order.

No WordPress runtime, fault injection, `Plugin_Upgrader`, database mutation, provider call or production action is executed here.

P-006 accounting therefore remains:

- documented: **144**;
- executed: **45**;
- PASS: **44**;
- FAIL: **0**;
- INCONCLUSIVE: **1**;
- certified Free/Pro pairs: **0**;
- runtime certifications: **0**.

Permanent P-001/CF remains **NOT_CERTIFIED** and ADR-0010 remains **Proposed**.

## 2. Source facts that control the review

### 2.1 Free bootstrap guard

`wpessential.php` publishes Free marketing/API/schema constants before checking the Composer autoloader. It then:

1. verifies `vendor/autoload.php` with `is_readable()`;
2. returns with an admin notice when the autoloader is missing/unreadable;
3. otherwise executes `require_once $autoload`;
4. only after that defines `WPE_FREE_BOOTSTRAP_READY=true`;
5. schedules `WPEssential\Bootstrap\Plugin::boot()` at `plugins_loaded` priority **-100**.

Important consequence:

- **missing/unreadable autoload** is a guarded fail-closed state;
- **readable but syntactically truncated/corrupt autoload** is not checked for integrity before `require_once` and may fail at PHP parse/include time.

### 2.2 Pro compatibility ordering

`wpessential-pro.php` schedules its compatibility/bootstrap callback at `plugins_loaded` priority **-200**, before Free's priority -100 boot callback.

Pro behavior includes:

1. load only Compatibility namespace classes before compatibility passes;
2. if `LocalCompatibilityPreflight` cannot be loaded, publish `pro_package_incomplete`, deny premium boot/migrations and return;
3. check the fixed premium module class list with `is_readable()` on their module files;
4. pass `$packageComplete` into `LocalCompatibilityPreflight::evaluateRuntime()`;
5. if incompatible, publish the fail-closed result and return;
6. when metadata is otherwise compatible, verify a fixed list of required Free runtime classes with `class_exists()`;
7. if one of those required Free classes is unavailable, publish `free_bootstrap_incomplete`, deny premium boot/migrations and return;
8. only after these checks publish compatible state and register premium modules.

Important consequence:

- several **missing-file** Pro and Free-profile states can be detected before premium module registration;
- `is_readable()` is existence/readability evidence only, not content-integrity evidence.

### 2.3 Local preflight package states

`LocalCompatibilityPreflight` exposes explicit package-level fail-closed states:

- `free_bootstrap_incomplete`;
- `pro_package_incomplete`.

`evaluateRuntime()` derives Free bootstrap completeness from `WPE_FREE_BOOTSTRAP_READY` and receives Pro package completeness from the caller.

There is no checksum/manifest/content-integrity validation in this local preflight.

### 2.4 Existing Wave 1K/1L transport is not interruption evidence

Wave 1K/1L use quiescent atomic symlink-target replacement over already extracted complete ZIP payloads.

That transport proves complete-generation switching only. It intentionally does **not** prove:

- in-place file-copy interruption;
- partial directory replacement;
- readable-but-corrupt/truncated PHP handling;
- WordPress manual upload/overwrite semantics.

It must not be reused as a claim for FP-49…52/58.

## 3. Critical safety distinction

The remaining B3 fixtures must separate two fundamentally different filesystem states.

### A. Missing or unreadable file

Examples:

- `vendor/autoload.php` absent;
- Pro preflight file absent;
- a required Pro module file absent;
- a selected required Free Platform class file absent.

These may be caught by current `is_readable()` / `class_exists()` guards and can be candidates for future bounded execution.

### B. Readable but truncated/corrupt PHP file

Examples:

- zero/partial bytes of `vendor/autoload.php` but file remains readable;
- partially copied `LocalCompatibilityPreflight.php`;
- partially copied premium module class;
- partially copied required Free Platform class.

Current product bootstrap has **no pre-include content-integrity manifest/checksum gate** for these files.

If a readable corrupt PHP file is included/autoloaded, PHP can fail during parsing before application-level compatibility code can convert the state into `free_bootstrap_incomplete` or `pro_package_incomplete`.

Therefore:

> **Full arbitrary interruption/truncation coverage is not dependency-ready.**

A future harness may not silently redefine "interrupted replacement" to mean only cleanly missing files.

## 4. Fixed proposed fault-coverage set

This is the minimum deterministic coverage set for a future authorized B3 execution. It is a design target only; it is not executed here.

### 4.1 Free package cut-points

**F-CUT-1 — entry metadata exists, autoloader missing**

State:

- new `wpessential.php` present;
- `vendor/autoload.php` missing/unreadable;
- Pro package remains complete.

Expected current behavior:

- Free publishes metadata constants;
- Free returns before `WPE_FREE_BOOTSTRAP_READY`;
- Pro preflight sees incomplete Free bootstrap;
- premium boot/migrations remain false.

Readiness: **candidate safe missing-file cut-point**.

**F-CUT-2 — autoloader path readable but corrupt/truncated**

State:

- new `wpessential.php` present;
- readable partial/corrupt `vendor/autoload.php`.

Expected risk:

- `require_once` can parser/include-fail before `WPE_FREE_BOOTSTRAP_READY`.

Readiness: **BLOCKED** until integrity is established before include, or the packaging/replacement transport guarantees unreadable/absent-before-atomic-publish semantics.

**F-CUT-3 — Free bootstrap ready, selected Pro-required Free Platform class missing**

Required future fixed class candidate:

- one class from Pro's declared required Free runtime profile that Free's ordinary base path can tolerate being absent long enough for Pro's -200 preflight to reject the pair.

The exact class must be selected by a separate implementation review and proven not to create an unrelated Free fatal after Pro returns.

Expected Pro behavior:

- metadata preflight may pass;
- `class_exists()` fails for the selected required Free class;
- Pro publishes `free_bootstrap_incomplete`;
- premium boot/migrations remain false.

Readiness: **PARTIAL**. Candidate exists conceptually, but safe class selection must be proven before execution.

**F-CUT-4 — selected Free Platform/bootstrap PHP file readable but truncated**

Expected risk:

- autoload/include can parser-fail.

Readiness: **BLOCKED** without pre-include integrity protection or transport-level atomic publish semantics.

### 4.2 Pro package cut-points

**P-CUT-1 — Pro entry present, compatibility preflight missing**

State:

- `wpessential-pro.php` present;
- `frameworks/Modules/Compatibility/LocalCompatibilityPreflight.php` absent/unreadable.

Expected current behavior:

- compatibility class cannot resolve;
- Pro publishes `pro_package_incomplete`;
- premium boot/migrations remain false;
- Free remains independently bootable.

Readiness: **candidate safe missing-file cut-point**.

**P-CUT-2 — compatibility preflight readable but truncated**

Expected risk:

- compatibility autoload may parser-fail.

Readiness: **BLOCKED** without pre-include integrity protection or atomic publication.

**P-CUT-3 — compatibility preflight complete, one required Pro module file missing**

Fixed future category coverage must include at least one file from each declared premium bootstrap category actually traversed by the module list, not one convenient sample.

At minimum the future harness must prove:

- one first-listed module file missing;
- one middle-listed module file missing;
- one last-listed module file missing.

Expected current behavior:

- `$packageComplete=false`;
- local preflight returns `pro_package_incomplete`;
- premium boot/migrations remain false;
- no premium module is registered;
- Free remains usable.

Readiness: **candidate safe missing-file cut-point**, pending exact immutable file list pinning.

**P-CUT-4 — required Pro module file readable but truncated**

Expected risk:

- `is_readable()` reports complete;
- compatibility may pass;
- later `class_exists()` can autoload the corrupt module and parser-fail.

Readiness: **BLOCKED** without package content-integrity validation before compatible state is treated as authoritative.

**P-CUT-5 — Pro entry file itself partially copied**

Expected risk:

- WordPress/PHP loads the plugin entry before any Pro-owned package guard can run.

Readiness: **BLOCKED** for arbitrary in-place truncation. The future replacement transport must either publish the entry atomically or detect package integrity outside the plugin process before activation/request boot.

## 5. Per-fixture readiness decision

| Fixture | Review result | What is ready | Blocking prerequisite |
| --- | --- | --- | --- |
| FP-49 | **BLOCKED** | Missing Free autoload cut-point can be modeled fail-closed. | Full interrupted replacement includes readable partial/corrupt states; no integrity gate exists before Free includes the autoloader. |
| FP-50 | **PARTIAL / NOT EXECUTION-READY AS FULL FIXTURE** | Missing Pro preflight and missing required module-file states have explicit fail-closed paths. | Arbitrary readable partial Pro entry/preflight/module files can parser-fail before safe degradation. |
| FP-51 | **BLOCKED AS WRITTEN** | Fixed missing-file matrix is design-ready. | Fixture explicitly covers remove/truncate behavior; readable truncated Pro PHP is not detected by current readability checks before include/autoload. |
| FP-52 | **BLOCKED AS WRITTEN** | Missing autoload is fail-closed; a selected missing required Free runtime class may be testable after safe-class proof. | Readable truncated Free bootstrap/Platform PHP can parser-fail; exact safe class coverage is not yet pinned. |
| FP-58 | **BLOCKED** | Complete immutable ZIP graph and disposable WordPress matrix already exist from Waves 1K/1L. | No accepted WordPress manual upload/overwrite harness exists; symlink transport is explicitly not equivalent to manual replacement. |

No remaining B3 fixture is authorized for execution by this review.

## 6. Root architectural gap for FP-49…52

The current runtime compatibility layer is good at rejecting **semantic incompatibility** and several **missing-file package states**, but it is not a general package-integrity verifier.

A future design must choose one of these explicit models before full interruption fixtures can execute:

### Model A — atomic publication contract

Replacement transport writes/extracts into a non-live staging directory, validates the complete artifact, then atomically switches the live plugin directory/generation.

Under this model, active requests see either:

- the old complete generation;
- or the new complete generation;

but never a readable partially copied PHP file.

This model can make arbitrary byte-level truncation in the live directory **not applicable to supported deployment semantics**, but that applicability decision must be accepted explicitly before P-006 fixture expectations are changed.

### Model B — pre-load package integrity contract

A trusted local manifest maps required runtime PHP files to expected hashes/sizes and is validated before application code includes/autoloads those files.

The verifier itself must live in an entry path that can be published/read atomically enough to perform the validation.

Only after this verifier succeeds may the package advertise bootstrap-ready/complete state.

This is materially new product/package behavior and requires separate architecture/security authorization. It is not authorized by #1040.

### Model C — external updater/installer integrity authority

An installer/updater validates and atomically installs a package before WordPress can execute it.

This may reduce runtime responsibility but enters separate updater/TUF / installer trust boundaries. It cannot be inferred from local compatibility evidence.

## 7. Required future harness architecture

When product/deployment semantics are explicitly selected, the future disposable harness should contain these separate layers.

### H1 — immutable artifact graph

Reuse deterministic complete ZIP generation principles from Waves 1K/1L.

Every node must record:

- ZIP SHA-256;
- payload-tree SHA-256;
- plugin entry SHA-256;
- marketing version;
- Platform API;
- schema generation/ranges.

Partial-state derivations must also record a **state-manifest hash** describing exactly which files are:

- old;
- new;
- missing;
- unreadable;
- intentionally corrupt/truncated.

### H2 — deterministic materializer

Never mutate canonical ZIPs.

For each fault cell:

1. create a fresh disposable plugin directory;
2. materialize files according to the exact state manifest;
3. verify every non-corrupt file hash;
4. verify the intended missing/corrupt set;
5. only then expose the state to the disposable WordPress request.

The materializer itself owns the fault; product code must not be modified merely to force PASS.

### H3 — process isolation

Each observation must start a fresh PHP process so constants/classes from a prior complete generation cannot contaminate a partial-state result.

No fault scenario may be evaluated in the same PHP process that previously loaded a different plugin generation.

### H4 — network deny

Retain the existing P-006 outbound WordPress HTTP deny/log probe.

Expected attempt count remains zero.

### H5 — database boundary

FP-49…52 are package-integrity/boot fixtures, not migration certification.

Allowed:

- disposable WordPress installation state;
- baseline activation state;
- DB snapshot/hash comparison needed to prove no new Pro migration occurred.

Forbidden without Lane C authorization:

- intentional schema advancement;
- rollback/downgrade migration;
- DB restoration claims that imply migration certification.

A future fixture should capture relevant migration/schema markers before and after the partial-package request and prove no Pro migration is newly admitted.

### H6 — recovery

Every partial-state cell must define recovery before execution:

1. terminate the request/process;
2. remove the entire partial live package directory;
3. restore a complete immutable artifact generation;
4. restart with a fresh PHP process;
5. re-evaluate compatibility;
6. verify the intended Free CPT/Taxonomy baseline where the recovered Free package is complete.

Do not "repair" a partial directory incrementally and then use the repaired directory as evidence for a different cell.

## 8. FP-58 manual replacement harness requirement

FP-58 must exercise a **WordPress-owned plugin upload/overwrite path** or another explicitly accepted representation of that path.

The Wave 1K/1L symlink switch is not sufficient because it bypasses WordPress's package extraction/replacement lifecycle.

Before FP-58 execution, a separate harness review/implementation must pin:

- exact WordPress core versions for the two accepted cells;
- exact core source/API path used to represent manual plugin upload/overwrite;
- filesystem method used in CI;
- destination cleanup/overwrite semantics;
- activation state before and after replacement;
- behavior when replacing Free first and Pro first;
- exact package hashes before/after;
- whether WordPress temporarily deactivates/reactivates the plugin;
- how failures are surfaced;
- what DB/plugin state may change as a side effect of WordPress's own upgrade controller.

The harness must not call WPEssential automatic updater/TUF logic.

FP-58 is therefore **BLOCKED** until this WordPress-owned transport is implemented and reviewed independently.

## 9. Timeout-safe future CI shape

Do not build one monolithic B3 job.

Recommended future structure after prerequisites are met:

1. **artifact/state-manifest build** — <=15 minutes;
2. **FP-49 Free interruption cells** — independent jobs, <=12 minutes each;
3. **FP-50 Pro interruption cells** — independent jobs, <=12 minutes each;
4. **FP-51 fixed Pro missing/corrupt cells** — matrix, <=12 minutes each, `fail-fast:false`;
5. **FP-52 fixed Free missing/corrupt cells** — matrix, <=12 minutes each, `fail-fast:false`;
6. **FP-58 manual replacement** — separate minimum/reference jobs, <=18 minutes each.

Required:

- stale-run cancellation;
- independent per-cell artifacts;
- exact-head checkout;
- no retry that mutates expected semantics;
- failed cells rerunnable independently.

## 10. Stop conditions for future implementation

Stop rather than force PASS if:

1. a readable corrupt PHP file can reach `require_once`/autoload before integrity rejection;
2. a partial state registers any premium module;
3. premium migration admission becomes true;
4. a partial Pro package breaks an otherwise complete Free request;
5. a future selected FP-52 missing Free file causes an unrelated Free fatal instead of the intended bounded package-incomplete behavior;
6. a fault cell cannot prove its exact file-state manifest;
7. manual replacement is represented only by the Wave 1K/1L symlink harness;
8. DB/schema state changes are needed to manufacture a compatibility PASS;
9. provider/network/production resources are required;
10. product source would need an unreviewed behavior change merely to satisfy the fixture.

## 11. Required next prerequisites

### Before FP-49/51/52 full interruption execution

One explicit architecture decision is required:

- accept atomic-publication semantics and narrow applicable interruption states accordingly; **or**
- add a separately authorized pre-load package-integrity contract; **or**
- establish an external installer/updater integrity authority under its own trust gate.

Until then, readable-truncation coverage remains blocked.

### Before FP-50 execution

A smaller missing-file-only sub-slice could be technically designed, but it would not satisfy the full FP-50 interruption claim unless the supported replacement semantics explicitly exclude readable partial PHP publication.

Therefore no FP-50 execution tranche is opened by this review.

### Before FP-58

Implement and review a disposable WordPress-owned upload/overwrite transport harness. Do not reuse symlink switching as a substitute.

## 12. Terminal readiness conclusion

The required B3 harness review is complete.

Result:

- **FP-49: BLOCKED**
- **FP-50: PARTIAL / NOT FULL-FIXTURE READY**
- **FP-51: BLOCKED AS WRITTEN**
- **FP-52: BLOCKED AS WRITTEN**
- **FP-58: BLOCKED**

The primary blocker for FP-49…52 is not the local compatibility evaluator; it is the absence of a declared integrity/publication contract that prevents a readable partially copied PHP file from being parsed before fail-closed package state can be published.

The primary blocker for FP-58 is the absence of an accepted WordPress-owned manual replacement harness.

Accordingly, **no new P-006 execution wave is dependency-ready from Lane B3 after this review**.

The next repository-changing action must come from a separately authorized architecture/harness prerequisite, not from directly executing FP-49…52/58.
