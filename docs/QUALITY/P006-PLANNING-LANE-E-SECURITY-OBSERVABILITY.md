# WPEssential — P-006 Planning Lane E: Security, Observability and Adversarial Recovery Evidence Map

Status: **PLANNING / EVIDENCE-DESIGN ONLY — EXECUTION NOT AUTHORIZED**  
Parent gate: **Issue #924**  
Worker lane: **Issue #929**  
Coordination: **Issue #930 / merged PR #931**  
Inputs: **Lane A / PR #932**, **Lane B / PR #933**, **Lane C / PR #934**, **Lane D / PR #935**  
Planning anchor: **current `main` after PR #935**  
Fixture scope: **FP-129…FP-144**  
P-006 truth: **144 documented / 0 executed / 0 passed / 0 failed / 0 certified pairs / 0 runtime certifications**

## 1. Purpose and hard boundary

This document plans the security/adversarial evidence required before a Free↔Pro compatibility profile can be accepted. It covers diagnostics authority, privacy/redaction, crafted metadata, public enumeration, observability truth separation, recovery safety, parser fuzzing, concurrency, reproducibility and independent adversarial review.

It does **not**:

- change security/runtime/admin code;
- execute fuzzing, concurrency or P-006 fixtures;
- call entitlement/provider/allocation services;
- mutate database/schema/users/roles/plugins;
- change repository branch protection;
- promote ADR-0010 or P-006 certification;
- deploy or release.

## 2. Existing non-certifying security/observability inputs

### 2.1 Runtime Observatory authority

`PlatformAdminController` registers the WPEssential diagnostics page with capability `manage_options` and re-checks `current_user_can('manage_options')` during render, returning 403 through `wp_die()` when unauthorized.

The page is explicitly read-only and exposes no mutation form/button.

### 2.2 Output escaping and JSON encoding

The controller renders diagnostic values through `esc_html()` and serializes the bootstrap JSON with `JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT`.

`RuntimeDiagnosticsSnapshot` additionally normalizes compatibility tokens and version/generation values before exposure.

### 2.3 Compatibility/entitlement truth separation

Runtime Observatory exposes compatibility and Product Entitlement as separate states and labels compatibility certification `adr_0010_not_certified`.

Unit evidence verifies the inventory consumes the canonical request-local compatibility result and provider-neutral entitlement state separately.

### 2.4 Read-only/non-secret source guard

`PlatformAdminCommercialInventorySourceTest` currently asserts:

- expected read-only inventory/compatibility labels;
- no `<form` or `<button` on the controller surface;
- no literal `license_key` or `license_token` source exposure.

This is a useful source assertion, not a runtime secret-leak test.

### 2.5 Compatibility parser fail-closed properties

`LocalCompatibilityPreflight` uses restricted marketing/API version syntax, integer schema generations and explicit min/max validation. Unknown/malformed metadata does not fall through to `compatible`.

### 2.6 Admin mismatch notice authority gap to test

The full Runtime Observatory page is capability-gated, but `wpessential-pro.php` currently registers compatibility error output on `admin_notices` without an explicit `current_user_can()` check at the notice callback itself.

A future FP-129 execution must determine whether the notice content is considered privileged compatibility diagnostics. If it is, the current behavior may require a separate implementation fix before P-006 can pass. Planning must not assume the Runtime Observatory capability automatically protects all admin notices.

### 2.7 Repository-admin residual

Issue #858 remains open: `main` branch protection / required status-check enforcement is still an admin-only repository setting action. This is separate from runtime P-006 evidence, but it remains a security/governance residual and must not be reported as fixed by source planning.

## 3. Evidence sensitivity classification

Future P-006 evidence should classify captured values before storage:

- **Public-safe**: plugin/version/API/schema identifiers, normalized compatibility state/reason/remediation, fixture ID, hash, WP/PHP versions.
- **Operational-restricted**: internal migration IDs, opaque site/network test identity, non-secret provider request correlation ID, exact failure class/code.
- **Secret/private — never store in ordinary fixture artifacts**: license tokens/keys, private signing material, raw signed entitlement artifacts where they embed private subject data, provider auth headers, unrestricted account payloads, database row contents, WordPress auth cookies/nonces, private user data.

Evidence collectors must prefer allowlists over post-hoc string redaction.

## 4. Fixture plan — FP-129…FP-144

Planning status labels (`STRONG EXISTING INPUT`, `PARTIAL EXISTING INPUT`, `GAP`, `CROSS-GATE`) do not mean executed/PASS.

| FP | Status | Existing input | Future authorized fixture / expected result | Stop / dependency |
| --- | --- | --- | --- | --- |
| FP-129 | PARTIAL EXISTING INPUT | Runtime Observatory is `manage_options` protected; generic Pro `admin_notices` mismatch message has no explicit capability check. | Run mismatch as administrator and lower-privilege authenticated admin-area users; privileged detailed diagnostics must require accepted capability, and any broader notice must contain only minimal safe recovery text. | Potential implementation gap. STOP if sensitive compatibility/commercial state is exposed to unauthorized role. |
| FP-130 | GAP/PARTIAL | Diagnostics record network/site context but admin controller uses site `manage_options`, not an explicit network-super-admin capability contract. | In Multisite network admin, attempt network-level compatibility/allocation diagnostics as super admin vs site admin; enforce explicit accepted network authority such as super-admin/`manage_network_options` equivalent. | Lane D Multisite diagnostics contract required. |
| FP-131 | STRONG EXISTING INPUT | Compatibility notice is admin-only hook; public controller path is not registered; diagnostics fields intentionally omit account/token objects. | Frontend + public REST requests under mismatch/expiry/outage states; inspect body/headers/logs for entitlement/account/allocation secrets and internal paths. | STOP on disclosure. |
| FP-132 | STRONG EXISTING INPUT | Strict compatibility parser + `safeCompatibility*` normalization + `esc_html()` + hex JSON encoding. | Inject bounded malicious strings through authorized synthetic metadata inputs (HTML/quotes/control-ish invalid forms); output must render safe normalized/invalid tokens, never executable markup/script. | Fuzz corpus coordinated with FP-139. |
| FP-133 | PARTIAL EXISTING INPUT | Strict API/version syntax rejects markup-like strings; UI escapes outputs. | Build disposable crafted plugin-header/metadata candidates containing delimiter/markup/JS payloads in fields WordPress will parse; preflight/diagnostics must reject/sanitize and never execute markup. | Artifact generator must stay test-only. |
| FP-134 | PARTIAL EXISTING INPUT | Runtime Observatory is admin-menu only; no compatibility/license diagnostics REST endpoint identified. | Unauthenticated/public endpoint enumeration of REST/AJAX/Abilities/admin-ajax routes; responses must not reveal private Product License/account/allocation state. | If future provider routes exist, include them. |
| FP-135 | STRONG EXISTING INPUT | Current mismatch notice contains only normalized `reason` and `remediation`; no provider response is consulted for compatibility. | Mismatch with separately simulated provider/account state; admin notice must remain local compatibility/recovery text and contain no raw provider payload/token. | STOP if remote commercial payload enters mismatch UI. |
| FP-136 | GAP/PARTIAL | Current local compatibility path has no entitlement artifact logging; future signed/provider flows do not yet exist. | Run signed entitlement/provider failure fixtures with log capture; raw artifact/token/auth header/private subject must never be logged. | Lane D crypto/provider implementation + explicit logging policy required. |
| FP-137 | PARTIAL EXISTING INPUT | Diagnostics expose compatibility and entitlement separately; module reason can show dependency state. No complete remote outage/migration-block classifier exists. | Construct mismatch, expired, verification-unavailable/outage and migration-block scenarios; Runtime Observatory/evidence must report distinct domains/reasons without relabeling one as another. | Lanes C/D must supply real states. |
| FP-138 | PARTIAL/GAP | Compatibility provides deterministic remediation codes; no accepted one-click irreversible recovery UI exists. | For each failure class, present exact safe action; any irreversible/destructive recovery must remain impossible without separately authorized explicit confirmation + backup/recovery preconditions. | Lane C recovery policy. |
| FP-139 | PARTIAL EXISTING INPUT | Fixed unit table covers many malformed/missing/contradictory values and parser is fail-closed; no generative fuzz run. | Property/generative fuzz semver/marketing-range/schema metadata with size/input limits; invariant: only explicitly valid in-range metadata can produce `compatible`, parser never fatals/hangs/permissively defaults. | Dedicated fuzz harness + resource limits. |
| FP-140 | CROSS-GATE | Request-local compatibility + migration gating exist; no three-way concurrent update/request/migration evidence. | Concurrently switch artifact pair while old/new requests and migration contender run; no request may obtain premium mutation/migration authority from mixed/unverified state; final data/schema integrity must hold. | Lanes B+C; stop-the-line on any mixed-state mutation/corruption. |
| FP-141 | CROSS-GATE | Diagnostics/site scope exists; no remote entitlement/allocation cache implementation accepted. | Concurrent site/network entitlement/allocation changes in disposable Multisite; cache/snapshot keys and revision checks must prevent cross-scope bleed. | Lane D identity/cache contract + provider sandbox. |
| FP-142 | PARTIAL EXISTING INPUT | Deterministic package SHA-256 manifests exist; Lanes A-D define required evidence dimensions. | Produce one immutable certification candidate record binding exact Free/Pro hashes, source commits, WP/PHP/DB, site mode, fixture IDs/outcomes and raw evidence references. | Record creation is evidence, not certification until required fixtures PASS. |
| FP-143 | PARTIAL EXISTING INPUT | Package builds are deterministic; compatibility evaluator is deterministic for same inputs. | Re-run full selected fixture subset for same immutable pair/environment from clean snapshot; outcomes/effective states must reproduce, with explained nondeterminism only for explicitly irrelevant IDs/timestamps. | Requires complete authorized harness; STOP on unexplained state drift. |
| FP-144 | GAP | Current planning/review is by the same Supervisor; no independent adversarial P-006 reviewer has approved evidence. | A different reviewer/agent with no authorship of the fixture evidence reviews code paths, raw evidence, stop conditions, gaps and attempted bypasses before ADR-0010 acceptance/certification. | Mandatory independence. Current chat cannot self-certify FP-144. |

## 5. Authority matrix for diagnostics

Future execution should test explicit surfaces rather than one generic `can_view` boolean:

| Surface | Candidate minimum authority | Public-safe fallback |
| --- | --- | --- |
| Runtime Observatory site diagnostics | `manage_options` (current) | none |
| Network-wide diagnostics | accepted super-admin/network capability | site-local minimal status only if contract permits |
| Compatibility admin notice | define whether detailed vs minimal; detailed should follow diagnostics authority | minimal non-secret “Pro inactive; contact/update” text if broader audience is allowed |
| CLI diagnostics | authenticated server/operator context according to accepted WP-CLI policy | no secrets |
| REST/AJAX diagnostics | no public route by default; explicit capability + nonce/auth if introduced | generic forbidden/not-found |
| Evidence artifacts | CI/reviewer access only as needed | sanitized summary |

The authority decision for the Pro mismatch `admin_notices` callback is an explicit pre-execution review item.

## 6. Fuzz strategy — planning only

### FZ1 — Compatibility metadata grammar

Generate bounded values for:

- Free/Pro marketing version fields;
- min/max Platform API fields;
- platform/Pro schema generations;
- absent/null/bool/int/array/object-like decoded values in pure evaluator inputs;
- whitespace and overlong strings;
- Unicode/control-like text where PHP string input permits it;
- HTML/script/quote sequences;
- numeric edge cases and contradictory ranges.

Core invariant: `compatible` iff all mandatory fields are valid and every dimension is explicitly in range.

### FZ2 — Output encoding

Feed every safe synthetic parser result into diagnostic normalization/rendering harnesses. Assert:

- HTML text nodes are escaped;
- JSON bootstrap cannot break out of its script container;
- invalid tokens become bounded safe placeholders;
- output size is bounded.

### FZ3 — Entitlement/provider artifacts

Only after Lane D accepts a cryptographic/provider contract:

- malformed canonical encoding;
- bad signature;
- unknown key/algorithm/version;
- wrong binding;
- replayed revision;
- extreme timestamp/skew;
- oversized fields.

No private production key may be used in fuzz fixtures.

## 7. Concurrency/adversarial schedule model

FP-140/141 need deterministic schedules, not best-effort race reproduction. The harness should coordinate processes with barriers around:

1. preflight read;
2. package replacement begin/end;
3. migration lock/admission;
4. schema mutation;
5. entitlement/allocation cache read/write;
6. final request commit/response.

Record process IDs/fixture actor IDs only as non-sensitive evidence. A schedule should be replayable from a named scenario rather than relying on random timing.

## 8. Runtime Observatory truth model

Formal evidence should validate a structured distinction similar to:

- `compatibility`: compatible / concrete mismatch reason;
- `entitlement`: pro_active / grace / expired / stale / unavailable / etc.;
- `provider`: not_called / reachable / outage / unknown-outcome;
- `membership_authorization`: allowed / denied / not_applicable;
- `migration`: not_required / pending / blocked / recovery_required / verified;
- `allocation`: not_applicable / unallocated / allocated / reconciliation_required;
- `certification`: not_certified / evidence_candidate / certified only after governance acceptance.

This document does not require these exact production fields; it requires the evidence to preserve the domain separation.

## 9. Recovery-message requirements

For each fail-closed state the future fixture should assert:

- one safe normalized reason;
- one deterministic remediation code/action class;
- no instruction to delete data as the default recovery;
- no unsupported automatic downgrade;
- no claim that license renewal fixes binary/schema incompatibility;
- no claim that plugin update fixes Membership permission;
- no irreversible action without separate confirmation/recovery proof;
- exact escalation path for `inconclusive`/provider-unknown states.

## 10. Certification record design

A future FP-142 record should be immutable and machine-readable. Minimum fields:

- evidence protocol revision;
- certification candidate ID;
- exact Free SHA-256 + source commit/version/API/schema;
- exact Pro SHA-256 + source commit/version/ranges/schema;
- environment: WordPress/PHP/DB/OS/container image as applicable;
- site mode + network/site scope;
- fixture IDs required for this profile;
- per-fixture status + raw evidence reference;
- stop-the-line defects linked;
- start/end timestamps;
- harness commit/hash;
- reviewer identities/roles;
- independent adversarial review status;
- final decision (`candidate`, `blocked`, `accepted`) separated from runtime compatibility.

No raw secret may be embedded in the certification record.

## 11. Independent adversarial review — FP-144

Independence means the final reviewer must not simply be the agent/person that authored the implementation and executed the evidence.

Reviewer tasks:

1. recompute artifact hashes;
2. inspect compatibility bootstrap ordering;
3. sample raw FP evidence against expected outcomes;
4. attempt truth-domain bypasses (entitlement→compatibility, provider→Membership, package presence→schema, etc.);
5. inspect mismatch/expiry/outage/recovery output for leakage/confusion;
6. review fuzz corpus and minimized failures;
7. review concurrency schedules and integrity checks;
8. verify failed/inconclusive fixtures were not hidden;
9. verify counters match actual executed records;
10. explicitly approve/block ADR-0010 acceptance/certification recommendation.

The current Supervisor planning work cannot satisfy this independence requirement itself.

## 12. Repository-process security residual

Issue #858 remains outside these runtime fixtures:

- protect `main`;
- require PRs;
- require applicable CI/status checks;
- block force pushes/deletion;
- govern bypass/review resolution.

The available GitHub connector in this session can read branch/ruleset state and mutate source/PR/issue resources, but it does not expose the required branch-protection/ruleset write operation. Therefore #858 must remain open until repository-admin evidence confirms the setting change.

This residual must not be “fixed” by adding source code that weakens or imitates repository governance.

## 13. Stop-the-line additions for Lane E

Stop security/adversarial execution immediately if any confirmed fixture shows:

1. unauthorized site/network user can access privileged compatibility/commercial diagnostics;
2. frontend/public REST/AJAX leaks entitlement/account/allocation/private migration state;
3. crafted metadata executes markup/script or breaks structured output;
4. malformed/fuzz input crashes/hangs parser or defaults to compatible;
5. raw license token/key/signed private artifact/provider auth header is logged/rendered/stored in ordinary evidence;
6. mismatch, expiry, outage or migration block are conflated in a way that permits unsafe remediation/mutation;
7. irreversible recovery can execute without accepted confirmation/backup gate;
8. concurrent update/request/migration yields mixed-state premium mutation or data corruption;
9. concurrent site/network commercial state bleeds through cache/scope identity;
10. evidence record does not bind the exact executed artifacts/environment;
11. same pair/environment produces materially different unexplained fixture outcomes;
12. independent reviewer finds an unresolved bypass or evidence-integrity defect.

## 14. Cross-gate dependencies

| Dependency | Fixtures | Requirement |
| --- | --- | --- |
| P-006 scoped ADR-0014 consent | FP-129…FP-144 | Mandatory before executable fixtures. |
| Lane A | FP-132/133/142/143 | Artifact identity, bootstrap and exact-pair evidence. |
| Lane B | FP-140/142/143 | Update/concurrency version graph. |
| Lane C | FP-137/138/140/142/143 | Migration/recovery state + integrity. |
| Lane D | FP-130/136/137/141/142/143 | Entitlement/provider/Multisite scope. |
| Crypto/provider implementation | FP-136 and relevant D-state adversarial cases | No fake token/signature claims. |
| Independent reviewer/agent | FP-144 | Different reviewer from evidence author/executor. |
| Repository admin | Issue #858 only | Branch protection/ruleset mutation unavailable in current source connector. |

## 15. Recommended future execution slices

1. **E1 — Authority/output hardening evidence**: FP-129…FP-135 with local synthetic states only.
2. **E2 — Provider/observability/recovery**: FP-136…FP-138 after Lanes C/D implementations exist.
3. **E3 — Parser fuzzing**: FP-139 in isolated bounded CI/fuzz worker.
4. **E4 — Concurrency**: FP-140/141 using deterministic barriers + disposable DB/Multisite/provider sandbox.
5. **E5 — Evidence integrity/reproducibility**: FP-142/143 after the selected profile fixture set executes.
6. **E6 — Independent adversarial review**: FP-144 last, before any acceptance/certification promotion.

## 16. Readiness conclusion

Lane E is **PLANNING COMPLETE / EXECUTION BLOCKED** when this document is accepted.

Current main has meaningful security foundations: strict fail-closed compatibility parsing, request-local truth, read-only capability-gated Runtime Observatory, escaped/hex-encoded diagnostics and separate compatibility/entitlement states. It also has clear gaps that cannot be hidden by planning: admin-notice authority needs explicit review, network-level authority is not yet formalized, provider/crypto log-redaction cannot be certified before those systems exist, fuzz/concurrency evidence has not run, and independent adversarial review is still outstanding.

No FP-129…FP-144 fixture is executed here. Issue #858 remains an admin-only repository-hardening residual. ADR-0010 and all P-006 counters remain unchanged.
