# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-14 UTC**  
Canonical audited base anchor: **`main @ 4cc04111831895e1b07b3480ae2957d70d7d0f8d`**  
Project classification: **`ACTIVE_EXISTING_PROJECT`**  
Execution mode: **`IMPLEMENTATION_GATED`**  
Development approval: **`GOV-OWNER-CONSENT-001 ACTIVE / source scope 56/56`**

## Mandatory work-cycle order

Every `start`, `continue` and `resume` cycle must resolve exact current `main`, reconcile accepted OPEN Issues first, reconcile eligible OPEN PRs/MRs second, inspect deterministic claims + the coordination queue, and only then continue dependency-ready work. No force/reuse of deterministic claim branches. Exact-head applicable CI, latest-main reconciliation and clean review threads are required before promotion.

## Current post-merge state

Issue **#904** is completed through merged PR **#905**, squash merge **`4cc04111831895e1b07b3480ae2957d70d7d0f8d`**.

The accepted commercial/distribution truth is now:

- canonical product split: **2 Free / 1 Platform Core / 53 Pro**;
- Free distribution physically contains shared Platform/Kernel/Contracts/Bootstrap infrastructure plus **CPT Builder** and **Taxonomy Builder** implementation and Free-owned admin assets;
- Pro is a separately identifiable add-on package containing premium module implementation/assets without duplicating Free Platform/Kernel/Contracts/Bootstrap or CPT/Taxonomy implementation;
- Free bootstrap no longer contributes product-Pro modules;
- optional Custom Tables composition is fail-closed when Pro implementation is absent;
- Free-only Taxonomy role-impact diagnostics remain usable and truthfully report `unavailable` when the Pro Surface 30 Roles service is absent;
- Free+Pro composition restores canonical Surface 30-backed role-impact truth through the shared neutral contract.

Exact PR #905 head **`79fa4173208b3dcae00b303395831f9e2b6c7634`** passed **9/9 applicable workflows** before merge:

1. Architecture Guards — PASS;
2. Browser E2E Accessibility — PASS;
3. CPT Runtime — PASS;
4. Distributable Package — PASS;
5. PHP Quality Toolchain — PASS;
6. Platform Compatibility Matrix — PASS;
7. Status Reference Application — PASS;
8. Taxonomy Role Impact — PASS, including WP 6.9/7.1 × PHP 8.2/8.5 separated Free+Pro fixtures;
9. Taxonomy Runtime — PASS.

This evidence proves the bounded physical package boundary and regression health only. It does **not** promote full product parity, release readiness, a certified Free/Pro compatibility pair, or paid entitlement enforcement.

## Commercial architecture authority

Accepted authority:

- `docs/DECISIONS/ADR-0001-free-pro-distribution.md` — Free and Pro are physically separate packages; Free must not contain Pro module source.
- `docs/DECISIONS/ADR-0007-license-expiry-runtime.md` — entitlement expiry must not destroy ownership/data; temporary verification failure is not equivalent to expiry.
- `docs/PRODUCT/FREE-PRO-ENTITLEMENT-MATRIX-V1.md` — canonical 56-surface commercial split and implementation gates.

Not yet certified:

- `docs/DECISIONS/ADR-0010-free-pro-compatibility.md` remains a later executable compatibility gate;
- certified Free/Pro version pairs remain unpromoted;
- live billing/provider/license-server integration remains unauthorized in the current gate.

## Full-parity lifecycle truth remains unchanged

Physical packaging is a distribution boundary, not a lifecycle-certification promotion. Canonical machine truth remains:

- **17** surfaces at or beyond `OPTION_CONTRACT_COMPLETE`;
- **16** surfaces exactly at `UX_CONTRACT_COMPLETE`;
- **0** surfaces at full-parity `RUNTIME_CERTIFIED`;
- **0** surfaces at `PRODUCT_PARITY_CERTIFIED`.

Options Bank truth remains **22 seeded / 22 NATIVE_AUDITED / 22 MARKET_AUDITED / 22 BANK_REVIEWED / 2139 records**.

The complete 56-surface dashboard remains in `README.md`; machine-readable lifecycle files remain authoritative for counts.

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
- Surfaces 11–21 — bounded read-only Runtime Foundation + Module/Ability exposure accepted and activated; **NOT full-parity runtime/product certified**.

No bounded state above implies deployment/release readiness.

## Current queue and security residual

`config/coordination/agent-work-queue.json` is the authoritative conflict-safe queue.

After the package-boundary closeout there is **no dependency-ready source-development slot**. Completed prior worker slots are historical and must not be reused. Deterministic behavior is therefore `NO_VALID_WORK_SLOT` unless a separate accepted fresh-main issue explicitly opens the next bounded gate.

Issue **#858** remains the only repository-admin security residual: `main` branch protection/ruleset is still not enabled. This must be fixed through GitHub repository administration; no source-code workaround is authorized and the project must not claim it fixed without fresh branch/ruleset evidence.

## Next implementation gate — not yet implicitly authorized

The next logical product gate is **Canonical Edition Metadata + Local Entitlement Domain V1**, but it requires its own accepted fresh-main Issue before source implementation.

Required sequencing for that future gate:

1. introduce a product-entitlement domain that distinguishes at minimum Free, Trial Active, Pro Active, Grace, Expired, Suspended, Verification Stale/Unavailable and Incompatible Version states;
2. install a replacement server-authoritative module activation policy through the existing `Plugin::setModuleActivationPolicy()` seam;
3. preserve ADR-0007 non-destructive expiry behavior and the rule that verification outage is not expiry;
4. keep product entitlement separate from Membership/user authentication and authorization;
5. only **after** the replacement activation policy is proven, normalize implemented premium module manifests from `edition: 'free'` to `edition: 'pro'`;
6. add exact unit/integration evidence for Free allowed, active Pro allowed, expired operation restrictions, and fail-closed-but-non-destructive unavailable/stale verification behavior;
7. do not add live billing/provider/network calls, license secrets, deployment or release in that gate unless separately authorized.

Simply flipping premium manifests first is forbidden because `DefaultModuleActivationPolicy` currently admits only `edition === 'free'` and would silently suppress premium module activation.

## Later separate gates

After edition/entitlement policy, keep these independent unless a later accepted plan explicitly combines them:

- WordPress admin Modules inventory: `Module | Edition | Package | Compatibility | Entitlement | Runtime state | Reason`;
- ADR-0010 executable Free/Pro compatibility preflight and certified version pairs;
- multisite entitlement/allocation semantics, clone/restore behavior and site/network scope;
- remote entitlement provider/billing integration;
- release/deployment certification.

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
