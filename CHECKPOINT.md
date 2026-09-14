# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-14 UTC**  
Canonical audited base anchor: **`main @ 647ba6c14c4e485406be68a145ae940369200087`**  
Project classification: **`ACTIVE_EXISTING_PROJECT`**  
Execution mode: **`IMPLEMENTATION_GATED`**  
Development approval: **`GOV-OWNER-CONSENT-001 ACTIVE / source scope 56/56`**

## Mandatory work-cycle order

Every `start`, `continue` and `resume` cycle must resolve exact current `main`, reconcile accepted OPEN Issues first, reconcile eligible OPEN PRs/MRs second, inspect deterministic claims + the coordination queue, and only then continue dependency-ready work. No force/reuse of deterministic claim branches. Exact-head applicable CI, latest-main reconciliation and clean review threads are required before promotion.

## Current commercial state

The commercial runtime/distribution foundation now has six accepted bounded gates:

1. **Commercial matrix V1** — Issue #902 / merged PR #903: canonical **2 Free / 1 Platform Core / 53 Pro** surface split.
2. **Physical Package Boundary V1** — Issue #904 / merged PR #905, merge `4cc04111831895e1b07b3480ae2957d70d7d0f8d`: deterministic physically separate Free and Pro artifacts.
3. **Canonical Edition Metadata + Local Entitlement Domain V1** — Issue #908 / merged PR #909, merge `b549a65ce1ffa7400fe3a10b2f0b255ecfbba80d`: provider-neutral local entitlement states, entitlement-aware Pro activation policy, premium-operation authority separation and canonical Pro edition metadata for the 12 currently implemented premium modules.
4. **Read-only Modules Commercial Inventory V1** — Issue #912 / merged PR #913, merge `f06bdfb20d17d1ce313560a774e9f3b8655961cc`: Runtime Observatory exposes canonical read-only module commercial/runtime diagnostics.
5. **ADR-0010 Compatibility Preflight Execution Readiness V1** — Issue #916 / merged PR #917, merge `2c9c32665557eb9855c31406dad20c7f72d0ff50`: docs-only contract for local compatibility metadata, fail-closed sequencing, CustomTables gating and diagnostics single-truth ownership.
6. **Local Free/Pro Compatibility Preflight V1 — Gates A–C** — Issue #920 / merged PR #921, merge **`647ba6c14c4e485406be68a145ae940369200087`**: bootstrap-safe Free/Pro metadata, strict local fail-closed pair evaluation, premium boot/runtime/migration gating and canonical request-local diagnostics truth are implemented. This is implementation evidence only; it does not accept ADR-0010 or execute/certify P-006.

## Entitlement Domain V1 truth

Canonical local states remain:

- `free`
- `trial_active`
- `pro_active`
- `grace`
- `expired`
- `suspended`
- `verification_stale`
- `verification_unavailable`
- `incompatible_version`

V1 semantics remain intentionally separate from compatibility:

- Free modules remain entitlement-independent.
- `trial_active`, `pro_active`, and `grace` admit current Pro modules and permit premium mutation in the V1 operation policy.
- `expired`, `suspended`, `verification_stale`, and `verification_unavailable` preserve read-safe Pro module activation but deny premium mutation.
- `free` and `incompatible_version` deny Pro module activation.
- temporary verification failure is not expiry and does not delete owned definitions/data/history.
- Membership/user authentication is not product-entitlement truth.
- compatibility PASS cannot manufacture entitlement authority, and entitlement cannot override compatibility failure.

## Modules Commercial Inventory V1 truth

The existing Platform Runtime Observatory remains read-only and exposes:

`Module | Edition | Package | Compatibility | Entitlement | Runtime state | Reason`

After PR #921:

- Free rows still report package `wpessential` and entitlement `not_applicable`;
- Pro rows use canonical edition/runtime/entitlement truth;
- Pro pair compatibility is no longer independently inferred from marketing version or module metadata alone;
- the diagnostics snapshot consumes the same canonical request-local compatibility result used by premium loading and optional Pro runtime gating;
- pair state, dimension, reason, recovery and certification status are rendered as escaped read-only diagnostics;
- compatibility and entitlement remain separate columns/truth domains;
- no activation/deactivation controls, license keys, tokens, secrets, billing/provider calls or mutation controls are exposed.

## Local Free/Pro Compatibility Preflight V1 truth

Issue #920 / merged PR #921 implements only readiness Gates A–C.

### Bootstrap-safe metadata

Free now publishes bootstrap-safe local metadata before ordinary runtime use:

- existing `WPE_VERSION` marketing/plugin version;
- dedicated `WPE_PLATFORM_API_VERSION`;
- `WPE_PLATFORM_SCHEMA_GENERATION`;
- bootstrap-ready state after the Free autoloader resolves.

Pro publishes its own version plus bounded supported Free version, Platform API and platform-schema ranges and Pro schema generation.

### Canonical local evaluation

The Pro-owned `LocalCompatibilityPreflight` evaluator:

- is local-only and performs no network/provider/billing work;
- does not depend on Free Platform/Kernel/Contracts or entitlement/Membership services to parse pair metadata;
- fails closed on missing, malformed, contradictory, too-old or too-new Free/API/schema metadata;
- distinguishes package presence, binary version compatibility, Platform API compatibility and schema compatibility from entitlement, Membership and updater/package trust;
- admits premium boot only for canonical `compatible` state;
- preserves Free operation and data when Pro is incompatible.

The authoritative request-local pair result is published in request-local runtime state and consumed by Pro premium autoload/contribution, Free optional Pro CustomTables runtime/migration gating and Runtime Observatory diagnostics. Public compatibility constants are mirrors only and are not the compatibility authority.

### CustomTables fail-closed boundary

Optional Pro CustomTables runtime now requires canonical compatibility PASS plus premium-boot admission and required class availability. Pro-owned CustomTables migration registration additionally requires migration admission. Package presence, class existence or entitlement alone is insufficient.

### Certification boundary

ADR-0010 remains **Proposed**. PR #921 implementation tests and packaged compatible/incompatible boot checks are regression/implementation evidence, not formal P-006 fixture execution or certified-pair evidence.

P-006 truth remains exactly:

- FP documented: **144**;
- FP executed: **0/144**;
- FP passed: **0**;
- FP failed: **0**;
- certified Free↔Pro artifact pairs: **0**;
- P-006 runtime certifications: **0**.

## Exact-head evidence for PR #921

Exact PR #921 head **`7cd4fe63ab0eaf63b4c971fd67c6f30b01b53a9c`** passed all **nine** applicable workflows before merge:

1. Architecture Guards — PASS;
2. PHP Quality Toolchain — PASS, including PHPCS, PHPStan and PHPUnit;
3. Distributable Package — PASS, including deterministic Free/Pro ZIPs, package boundaries, Free-only boot, compatible Free→Pro and Pro→Free boot, and deliberately incompatible fail-closed boot in both plugin-file load orders;
4. Platform Compatibility Matrix — PASS;
5. Browser E2E Accessibility — PASS;
6. CPT Runtime — PASS;
7. Taxonomy Runtime — PASS;
8. Taxonomy Role Impact — PASS;
9. Status Reference Application — PASS.

No review or review-thread blocker existed and `main` had zero drift from the PR base before squash merge.

## Commercial architecture authority

Accepted authority:

- `docs/DECISIONS/ADR-0001-free-pro-distribution.md` — physical Free/Pro separation.
- `docs/DECISIONS/ADR-0007-license-expiry-runtime.md` — non-destructive expiry/degraded semantics.
- `docs/PRODUCT/FREE-PRO-ENTITLEMENT-MATRIX-V1.md` — canonical 56-surface commercial split.
- `docs/IMPLEMENTATION/EDITION-ENTITLEMENT-DOMAIN-V1.md` — local entitlement/edition semantics.
- Issue #912 / merged PR #913 — read-only Modules commercial/runtime diagnostics.
- `docs/PRODUCT/ADR-0010-COMPATIBILITY-PREFLIGHT-READINESS-V1.md` / #916 / #917 — readiness contract.
- Issue #920 / merged PR #921 — bounded local fail-closed compatibility preflight implementation Gates A–C.

Still separate / not certified:

- `docs/DECISIONS/ADR-0010-free-pro-compatibility.md` remains **Proposed**;
- P-006 FP-01…FP-144 formal execution and certified pair evidence remain unexecuted;
- multisite entitlement allocation, clone/restore and network/site semantics remain separately gated;
- remote license-server, billing/provider integration, secrets/credential persistence and live verification remain unauthorized;
- broad commercial activation/deactivation controls and universal premium mutation enforcement remain separately gated;
- updater/TUF and production deployment/release remain separately gated.

## Full-parity lifecycle truth remains unchanged

Commercial packaging, entitlement infrastructure, read-only inventory, compatibility readiness and the bounded local preflight do not promote product-surface lifecycle certification. Canonical machine truth remains:

- **17** surfaces at or beyond `OPTION_CONTRACT_COMPLETE`;
- **16** surfaces exactly at `UX_CONTRACT_COMPLETE`;
- **0** surfaces at full-parity `RUNTIME_CERTIFIED`;
- **0** surfaces at `PRODUCT_PARITY_CERTIFIED`.

Options Bank truth remains **22 seeded / 22 NATIVE_AUDITED / 22 MARKET_AUDITED / 22 BANK_REVIEWED / 2139 records**.

The complete 56-surface dashboard remains mandatory in `README.md`. The #922 closeout audit reconfirmed canonical rows **1 through 56** are present; no module lifecycle row or percentage is promoted by #920/#921.

## Existing bounded implementation states

Previously accepted bounded states remain unchanged:

- Surface 3 / Fields — **PASS FOR CERTIFIED NATIVE V1 SCOPE**.
- Surface 4 / Relations — **PASS FOR CERTIFIED NATIVE V1 BASELINE**.
- Surface 5 / Status — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Surface 6 / Query — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Surface 8 / Admin Columns — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Surface 9 / Listings — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Surface 2 / Taxonomy — **PASS FOR CERTIFIED BOUNDED ACCEPTED V1 OWNER-RUNTIME SCOPE**; full-parity lifecycle remains unpromoted.
- Surface 7 / Custom Tables — **ACTIVE / NOT PASS**, bounded runway **90%**, managed-table execution still blocked.
- Surfaces 11–21 — bounded read-only Runtime Foundation + Module/Ability exposure accepted and Pro-owned; **NOT full-parity runtime/product certified**.

No bounded state above implies deployment/release readiness.

## Current queue and security residual

`config/coordination/agent-work-queue.json` v36 is the authoritative conflict-safe queue and is anchored to `main @ 647ba6c14c4e485406be68a145ae940369200087`.

After Local Free/Pro Compatibility Preflight V1 there is **no dependency-ready source-development slot**. Deterministic behavior is `NO_VALID_WORK_SLOT` unless a separate accepted fresh-main issue explicitly opens another bounded gate and satisfies its decision/evidence prerequisites.

Issue **#858** remains the repository-admin security residual: `main` branch protection/ruleset is still not enabled. Current branch evidence reports `protected=false` and required-check enforcement off. This must be fixed through GitHub repository administration; no source-code workaround is authorized and the project must not claim it fixed without fresh branch/ruleset evidence.

## Later gates — not implicitly authorized

Possible later gates remain independent unless a fresh accepted Issue explicitly authorizes one:

- ADR-0010 decision acceptance, where its acceptance criteria are actually satisfied;
- P-006 executable FP-01…FP-144 compatibility fixtures and certified Free/Pro artifact pairs;
- multisite entitlement allocation, site/network scope and clone/restore behavior;
- remote entitlement verification and provider/billing integration;
- module activation/deactivation commercial controls;
- broader premium mutation/runtime enforcement;
- updater/TUF work;
- deployment/release certification.

The merged local preflight does not itself authorize any of these later gates.

## Closeout rule

Before reporting any meaningful repository-changing cycle final:

1. refresh exact `main`;
2. resolve accepted OPEN Issues first;
3. reconcile eligible OPEN PRs/MRs second;
4. verify exact-head applicable CI and review threads;
5. reconcile the complete 56/56 README dashboard and coordination queue;
6. keep certification counters unchanged unless machine evidence explicitly promotes them;
7. report admin-only residuals such as #858 separately.

Repository evidence, accepted ADRs and machine-readable lifecycle files override stale conversational summaries and historical checkpoint text.
