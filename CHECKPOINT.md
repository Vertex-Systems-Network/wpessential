# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-14 UTC**  
Canonical audited base anchor: **`main @ b549a65ce1ffa7400fe3a10b2f0b255ecfbba80d`**  
Project classification: **`ACTIVE_EXISTING_PROJECT`**  
Execution mode: **`IMPLEMENTATION_GATED`**  
Development approval: **`GOV-OWNER-CONSENT-001 ACTIVE / source scope 56/56`**

## Mandatory work-cycle order

Every `start`, `continue` and `resume` cycle must resolve exact current `main`, reconcile accepted OPEN Issues first, reconcile eligible OPEN PRs/MRs second, inspect deterministic claims + the coordination queue, and only then continue dependency-ready work. No force/reuse of deterministic claim branches. Exact-head applicable CI, latest-main reconciliation and clean review threads are required before promotion.

## Current commercial state

The commercial runtime/distribution foundation now has three accepted gates:

1. **Commercial matrix V1** — Issue #902 / merged PR #903: canonical **2 Free / 1 Platform Core / 53 Pro** surface split.
2. **Physical Package Boundary V1** — Issue #904 / merged PR #905, merge `4cc04111831895e1b07b3480ae2957d70d7d0f8d`: Free and Pro are deterministic physically separate artifacts; Free contains shared Platform/Kernel/Contracts/Bootstrap plus CPT + Taxonomy implementation, while premium implementation/assets are Pro-owned.
3. **Canonical Edition Metadata + Local Entitlement Domain V1** — Issue #908 / merged PR #909, merge **`b549a65ce1ffa7400fe3a10b2f0b255ecfbba80d`**: provider-neutral local entitlement states, entitlement-aware Pro activation policy, separate premium operation authority, and canonical `edition: 'pro'` metadata for the 12 currently implemented premium modules.

## Entitlement Domain V1 truth

Canonical local states are:

- `free`
- `trial_active`
- `pro_active`
- `grace`
- `expired`
- `suspended`
- `verification_stale`
- `verification_unavailable`
- `incompatible_version`

V1 semantics are intentionally split between module/read preservation and premium mutation authority:

- Free modules remain entitlement-independent.
- `trial_active`, `pro_active`, and `grace` admit current Pro modules and permit premium mutation in the V1 operation policy.
- `expired`, `suspended`, `verification_stale`, and `verification_unavailable` preserve read-safe Pro module activation but deny premium mutation.
- `free` and `incompatible_version` deny Pro module activation.
- `verification_stale` and `verification_unavailable` are distinct from `expired`; temporary verification failure is not expiry.
- Membership/user authentication is not product-entitlement truth.
- Pro bootstrap installs the entitlement-aware activation policy before premium module contribution.
- The default local state when no deterministic state is supplied is `verification_unavailable`, not `expired`.

This gate models authority for current and future integration but does **not** claim that every future premium mutation path is already universally wired through `PremiumOperationPolicy`. Current contributed premium modules are the bounded read-only `get`/`catalog` surfaces already accepted before this commercial gate.

## Exact-head evidence for PR #909

Exact PR #909 head **`37e5bb3c7a9186482306a458836575648614635e`** passed all five applicable workflows before merge:

1. Architecture Guards — PASS, including architecture manifests/contracts, PHP syntax, WPCS, PHPStan, PHPUnit, smoke, persistence, AJAX, Action Scheduler coexistence and JobService integration;
2. PHP Quality Toolchain — PASS;
3. Distributable Package — PASS, including deterministic Free/Pro artifacts, both package load orders and package-boundary checks;
4. Platform Compatibility Matrix — PASS across the applicable WordPress/PHP/database matrix;
5. Taxonomy Role Impact — PASS across WP 6.9/7.1 × PHP 8.2/8.5 separated Free+Pro fixtures.

The 12 currently implemented premium Module manifests now report `edition: 'pro'`: Roles & Capabilities, Admin Menu, Settings Pages, Frontend Dashboard, User Profiles, Membership, Builder Widgets, Forms & Workflows, Cron, Notifications, Emails and Chat.

## Commercial architecture authority

Accepted authority:

- `docs/DECISIONS/ADR-0001-free-pro-distribution.md` — Free and Pro are physically separate packages; Free must not contain Pro module source.
- `docs/DECISIONS/ADR-0007-license-expiry-runtime.md` — entitlement expiry must not destroy ownership/data; temporary verification failure is not equivalent to expiry.
- `docs/PRODUCT/FREE-PRO-ENTITLEMENT-MATRIX-V1.md` — canonical 56-surface commercial split and bounded implementation gates.
- `docs/IMPLEMENTATION/EDITION-ENTITLEMENT-DOMAIN-V1.md` — accepted V1 local entitlement/edition semantics and explicit non-goals.

Still separate / not certified:

- `docs/DECISIONS/ADR-0010-free-pro-compatibility.md` remains a later executable compatibility gate; certified Free/Pro version pairs remain unpromoted.
- WordPress admin Modules inventory remains unimplemented.
- multisite entitlement allocation, clone/restore and network/site semantics remain separately gated.
- remote license-server, billing/provider integration, secrets/credential persistence and live verification remain unauthorized in this gate.
- production deployment/release remains separately gated.

## Full-parity lifecycle truth remains unchanged

Commercial packaging and entitlement infrastructure are not lifecycle-certification promotions. Canonical machine truth remains:

- **17** surfaces at or beyond `OPTION_CONTRACT_COMPLETE`;
- **16** surfaces exactly at `UX_CONTRACT_COMPLETE`;
- **0** surfaces at full-parity `RUNTIME_CERTIFIED`;
- **0** surfaces at `PRODUCT_PARITY_CERTIFIED`.

Options Bank truth remains **22 seeded / 22 NATIVE_AUDITED / 22 MARKET_AUDITED / 22 BANK_REVIEWED / 2139 records**.

The complete 56-surface dashboard remains mandatory in `README.md`; machine-readable lifecycle files remain authoritative for counts.

## Existing bounded implementation states

The previously accepted bounded states remain unchanged:

- Surface 3 / Fields — **PASS FOR CERTIFIED NATIVE V1 SCOPE**.
- Surface 4 / Relations — **PASS FOR CERTIFIED NATIVE V1 BASELINE**.
- Surface 5 / Status — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Surface 6 / Query — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Surface 8 / Admin Columns — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Surface 9 / Listings — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Surface 2 / Taxonomy — **PASS FOR CERTIFIED BOUNDED ACCEPTED V1 OWNER-RUNTIME SCOPE**; full-parity lifecycle remains unpromoted.
- Surface 7 / Custom Tables — **ACTIVE / NOT PASS**, bounded runway **90%**, managed-table execution still blocked.
- Surfaces 11–21 — bounded read-only Runtime Foundation + Module/Ability exposure accepted and now Pro-owned; **NOT full-parity runtime/product certified**.

No bounded state above implies deployment/release readiness.

## Current queue and security residual

`config/coordination/agent-work-queue.json` v33 is the authoritative conflict-safe queue and is anchored to `main @ b549a65ce1ffa7400fe3a10b2f0b255ecfbba80d`.

After Entitlement Domain V1 there is **no dependency-ready source-development slot**. Completed branches must not be reused. Deterministic behavior is therefore `NO_VALID_WORK_SLOT` unless a separate accepted fresh-main issue explicitly opens another bounded gate.

Issue **#858** remains the repository-admin security residual: `main` branch protection/ruleset is still not enabled. This must be fixed through GitHub repository administration; no source-code workaround is authorized and the project must not claim it fixed without fresh branch/ruleset evidence.

## Later gates — not implicitly authorized

Possible later commercial gates remain independent unless a fresh accepted Issue explicitly authorizes one:

- WordPress admin Modules inventory: `Module | Edition | Package | Compatibility | Entitlement | Runtime state | Reason`, with no secret/token exposure;
- ADR-0010 executable Free/Pro compatibility preflight and certified version pairs;
- multisite entitlement allocation, site/network scope, clone/restore behavior;
- remote entitlement verification and provider/billing integration;
- explicit wiring of premium mutation policies into future mutating premium surfaces as those surfaces are authorized;
- deployment/release certification.

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
