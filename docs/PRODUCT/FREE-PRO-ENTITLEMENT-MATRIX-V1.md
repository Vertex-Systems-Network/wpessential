# WPEssential — 56-Surface Free / Pro Entitlement Matrix V1

Status: **Supervisor audit / product-distribution reconciliation**  
Issue: **#902**  
Audit base: **`main @ 75eab57225b09bd51171c9dc76ef01de83e756a2`**  
Date: **2026-09-14**

## 1. Purpose

Reconcile the current 56-surface architecture with the already accepted WPEssential commercial/distribution decisions before any runtime license enforcement or manifest edition migration is attempted.

This document is a classification and architecture audit. It does **not**:
- change a runtime module manifest;
- add billing/provider calls;
- add license secrets;
- ship or install a Pro package;
- authorize deployment/release;
- promote any surface to runtime/product-parity certification.

## 2. Existing decisions that control this audit

The product model is already decided; this audit does not invent a new one.

1. **ADR-0001 — Accepted Free / Pro Distribution**
   - WPEssential Free is the WordPress.org-target platform plugin.
   - CPT Builder and Taxonomy Builder are permanently Free.
   - WPEssential Pro is a separately distributed premium add-on.
   - The Free artifact must contain **no Pro module source**.
2. **COMMERCIAL-DISTRIBUTION.md**
   - Free local functionality is the platform/kernel required by Free, CPT, Taxonomy, and local platform/onboarding/diagnostic/documentation surfaces.
   - Pro contains/registers premium modules and depends on a compatible Free Platform API.
3. **COMMERCIAL-POSITIONING-AND-PACKAGING.md**
   - Free: CPT + Taxonomy + required platform kernel/basic support surfaces.
   - Pro: all currently released Pro modules are included in the commercial plan by default rather than sold as per-module microtransactions.
4. **ADR-0007 — Accepted license-expiry behavior**
   - entitlement controls premium creation/editing/operations;
   - user data and safe already-deployed output are preserved;
   - verification outage is not equivalent to expiry.
5. **ADR-0010 — Proposed Free ↔ Pro compatibility protocol**
   - Free platform compatibility, Pro binary compatibility, schema compatibility, entitlement, commercial license/account state, membership authorization, and updater trust are separate truth domains.

No superseding ADR is required merely to implement this already accepted package split. A new/superseding ADR is required if the distribution model itself changes.

## 3. Canonical commercial classes

- `FREE` — permanently available in the WordPress.org-target Free product.
- `PRO` — premium user-facing module; source belongs to the separately distributed Pro add-on, not the Free artifact.
- `PLATFORM_CORE` — shared Free platform/kernel/account/docs/diagnostic shell; not sold as a standalone module.

Evidence strength is recorded separately because some later surface specifications say `Edition: Pro`, some say `Pro module candidate`, and some inherit the accepted closed Free allowlist plus Pro-expiry contract. The commercial class below follows the accepted distribution boundary: the only permanently Free product modules are CPT and Taxonomy; Surface 31 is the platform shell.

## 4. 56 / 56 canonical surface matrix

| # | Canonical surface | Commercial class | Classification evidence |
|---:|---|---|---|
| 1 | CPT Builder | `FREE` | ADR-0001 + Module Catalog explicit Free |
| 2 | Taxonomy Builder | `FREE` | ADR-0001 + Module Catalog explicit Free |
| 3 | Fields | `PRO` | Module Catalog explicit Pro |
| 4 | Relations | `PRO` | Module Catalog explicit Pro |
| 5 | Status | `PRO` | Module Catalog explicit Pro |
| 6 | Query | `PRO` | Module Catalog explicit Pro |
| 7 | Custom Tables | `PRO` | Module Catalog explicit Pro |
| 8 | Admin Columns | `PRO` | Module Catalog explicit Pro |
| 9 | Listings | `PRO` | Module Catalog explicit Pro |
| 10 | Dashboard Widgets | `PRO` | Module Catalog explicit Pro |
| 11 | Admin Menu | `PRO` | Module Catalog explicit Pro |
| 12 | Settings Pages | `PRO` | Module Catalog explicit Pro |
| 13 | Frontend Dashboard | `PRO` | Module Catalog explicit Pro |
| 14 | User Profile | `PRO` | Module Catalog explicit Pro |
| 15 | Membership | `PRO` | Membership spec explicit `Classification: Pro` |
| 16 | Builder Widgets | `PRO` | Module Catalog explicit Pro (pre-Membership numbering) |
| 17 | Forms & Workflows | `PRO` | Module Catalog explicit Pro (pre-Membership numbering) |
| 18 | Cron | `PRO` | Module Catalog explicit Pro (pre-Membership numbering) |
| 19 | Notifications | `PRO` | Module Catalog explicit Pro (pre-Membership numbering) |
| 20 | Emails | `PRO` | Module Catalog explicit Pro (pre-Membership numbering) |
| 21 | Chat | `PRO` | Module Catalog explicit Pro (pre-Membership numbering) |
| 22 | REST API | `PRO` | Module Catalog explicit Pro (pre-Membership numbering) |
| 23 | Connections/Webhooks | `PRO` | Module Catalog explicit Pro (pre-Membership numbering) |
| 24 | Backup | `PRO` | Module Catalog explicit Pro (pre-Membership numbering) |
| 25 | Reset | `PRO` | Module Catalog explicit Pro (pre-Membership numbering) |
| 26 | Import/Export | `PRO` | Module Catalog explicit Pro (pre-Membership numbering) |
| 27 | Protector | `PRO` | Module Catalog explicit Pro (pre-Membership numbering) |
| 28 | Media Operations | `PRO` | Module Catalog explicit Pro (pre-Membership numbering) |
| 29 | XML-RPC | `PRO` | Module Catalog explicit Pro (pre-Membership numbering) |
| 30 | Roles & Capabilities | `PRO` | Module Catalog explicit Pro (pre-Membership numbering) |
| 31 | Platform | `PLATFORM_CORE` | Module Catalog says platform surface / not sold; ADR-0001 makes Free the platform/kernel |
| 32 | Solution Blueprint Composer | `PRO` | Accepted Free allowlist excludes it + Universal Foundations Pro-expiry contract |
| 33 | Analytics & Journeys | `PRO` | Accepted Free allowlist excludes it + Universal Foundations Pro-expiry contract |
| 34 | Search & Indexing | `PRO` | Accepted Free allowlist excludes it + Universal Foundations Pro-expiry contract |
| 35 | Decision/Formula/Scoring | `PRO` | Accepted Free allowlist excludes it + Universal Foundations Pro-expiry contract |
| 36 | Ledger | `PRO` | Accepted Free allowlist excludes it + Universal Foundations Pro-expiry contract |
| 37 | Reservations | `PRO` | Accepted Free allowlist excludes it + Universal Foundations Pro-expiry contract |
| 38 | Placement/Personalization | `PRO` | Accepted Free allowlist excludes it + Universal Foundations Pro-expiry contract |
| 39 | Experiments/Rollout | `PRO` | Accepted Free allowlist excludes it + Universal Foundations Pro-expiry contract |
| 40 | Documents/Records | `PRO` | Accepted Free allowlist excludes it + Universal Foundations Pro-expiry contract |
| 41 | Sync/ETL | `PRO` | Accepted Free allowlist excludes it + Universal Foundations Pro-expiry contract |
| 42 | Geo/Territory | `PRO` | Accepted Free allowlist excludes it + Universal Foundations Pro-expiry contract |
| 43 | AI Gateway/Copilot | `PRO` | Accepted Free allowlist excludes it + Universal Foundations Pro-expiry contract |
| 44 | Redirect/Routing | `PRO` | Spec says Pro module candidate + accepted Free allowlist excludes it |
| 45 | Search/Replace/Transform | `PRO` | Spec says Pro module candidate + accepted Free allowlist excludes it |
| 46 | Dummy Data/Fixtures | `PRO` | Spec says Pro/developer module candidate + accepted Free allowlist excludes it |
| 47 | Link Health | `PRO` | Spec says Pro module candidate + accepted Free allowlist excludes it |
| 48 | DB Maintenance | `PRO` | Spec says Pro module candidate + accepted Free allowlist excludes it |
| 49 | Admin Theme | `PRO` | Surface spec explicit `Edition: Pro` |
| 50 | Safe Script/Tag | `PRO` | Surface spec explicit `Edition: Pro` |
| 51 | Content Order/Sequence | `PRO` | Surface spec explicit `Edition: Pro` |
| 52 | Security Scanner | `PRO` | Surface spec explicit `Edition: Pro` |
| 53 | Fonts/Typography | `PRO` | Surface spec explicit `Edition: Pro` |
| 54 | User Stores | `PRO` | Surface spec explicit `Edition: Pro` |
| 55 | Staging/Clone/Migration | `PRO` | Surface spec explicit `Edition: Pro` |
| 56 | Theme Workspace | `PRO` | Surface spec explicit `Edition: Pro` |

### Count reconciliation

- `FREE`: **2 / 56** — Surfaces 1–2.
- `PLATFORM_CORE`: **1 / 56** — Surface 31.
- `PRO`: **53 / 56** — every remaining user-facing canonical surface.
- Per-module micro-purchase: **not the current recommended model**; released Pro modules belong to the Pro plan/site-count/support tier model.

## 5. Critical current-state mismatches

### M-01 — Product edition truth and runtime manifest truth are not aligned

`ModuleManifest` supports `free|pro`, and `DefaultModuleActivationPolicy` currently permits only manifests whose edition is `free`.

However, bounded read-only runtime work has used `edition: 'free'` for at least product-Pro modules. Two directly verified examples are:
- Surface 15 Membership: product spec says **Pro**, current `MembershipModule` manifest says `free`;
- Surface 30 Roles & Capabilities: Module Catalog says **Pro**, current `RolesModule` manifest says `free`.

Therefore the current manifest edition values are **not yet trustworthy commercial entitlement truth**. They must not be used to claim that a product-Pro module is commercially Free.

### M-02 — Current bootstrap contributes product-Pro modules from the base plugin

Current `wpessential.php` contributes Roles plus Admin Menu, Settings, Dashboard, Profiles, Membership, Builder Widgets, Forms/Workflows, Cron, Notifications, Emails and Chat into the base plugin bootstrap.

Those surfaces are classified `PRO` by the accepted product model. This is acceptable only as an implementation-repository intermediate state; it is not the final Free distribution boundary.

### M-03 — Current distributable packager copies the complete `frameworks/` tree

`tools/release/build-distributable.php` currently copies the entire `frameworks` directory into `artifacts/wpessential.zip`.

ADR-0001 requires the WordPress.org-target **Free artifact to contain no Pro module source**. The current single-artifact packager therefore does **not yet prove the accepted Free/Pro physical package boundary**. A future release must have separately verifiable Free and Pro artifact manifests/allowlists.

This is a release/distribution blocker, not a reason to hide Pro code only through UI or an entitlement check inside the Free ZIP.

## 6. Required target architecture

### 6.1 Physical package boundary first

The final packaging model must produce two explicit artifacts:

**WPEssential Free**
- shared platform/kernel contracts required by Free;
- Surface 1 CPT;
- Surface 2 Taxonomy;
- Surface 31 platform/admin shell needed by Free;
- no Pro module implementation source.

**WPEssential Pro**
- separately distributed premium plugin/add-on;
- product-Pro module source;
- lightweight bootstrap that preflights Free/Platform API compatibility before registering Pro services/modules;
- no duplicate private platform/kernel engine.

A Free package test must fail if a Pro module namespace/path is present.

### 6.2 Local technical edition metadata

`ModuleManifest::edition` remains local technical metadata and should match the canonical product class for user-facing modules:
- Free module → `free`;
- Pro module → `pro`.

`PLATFORM_CORE` is not a sellable module; platform services should not be forced into a fake commercial module classification merely to pass a boolean gate.

### 6.3 Entitlement is not merely `edition`

Edition answers **what package/class a module belongs to**. Entitlement answers **whether the current site/allocation may perform premium management/mutation now**.

Required separate states include at least:
- disconnected;
- free;
- trial-active;
- pro-active;
- grace;
- expired;
- suspended;
- verification-stale;
- verification-unavailable;
- incompatible-version.

A network outage must not become `expired`.

### 6.4 Server-side activation/operation policy

The existing `ModuleActivationPolicyInterface` seam is the correct composition point, but the final policy must not be UI-only.

Required behavior:
- Free modules load without remote licensing.
- Pro bootstrap first proves binary/Platform API compatibility.
- Pro management/mutation requires the applicable verified entitlement state.
- confirmed expiry follows ADR-0007: preserve data and safe deployed output; restrict creation/editing/premium manual operations; pause mutating jobs where appropriate.
- verification unavailable uses signed/cache/grace semantics rather than hard failure.
- Membership authorization is never interchangeable with WPE product entitlement.

### 6.5 Admin Modules inventory

Surface 31 should expose a read-only module inventory with at least:

| Field | Meaning |
|---|---|
| Module | canonical module/surface name |
| Edition | Free / Pro / Platform |
| Package | Free installed / Pro installed / missing |
| Compatibility | compatible / incompatible / unknown |
| Entitlement | active / trial / grace / expired / stale / unavailable / not-applicable |
| Runtime state | active / read-only / paused / degraded / unavailable |
| Reason | safe diagnostic code/message |

Do not expose license tokens, account secrets, signed raw entitlement documents, provider credentials or private updater URLs in generic diagnostics.

### 6.6 Multisite/allocation boundary

Package activation and commercial allocation are distinct.
- Network activation does not silently entitle every child site.
- Site/network allocation semantics must be explicit.
- clone/restore cannot manufacture another paid allocation.
- blog ID/domain alone is not entitlement authority.

## 7. Implementation gates after this audit

Runtime implementation should be split into reviewable milestones rather than one giant licensing diff.

1. **Package Boundary Gate** — deterministic Free and Pro artifact manifests; Free proves zero Pro module source.
2. **Canonical Edition Metadata Gate** — normalize manifests/registries to the 56-surface matrix without making Free load Pro source.
3. **Compatibility Preflight Gate** — implement/execute the ADR-0010/P-006 profile before Pro service/module registration.
4. **Entitlement Domain Gate** — typed states, signed/cache/grace boundary, secret-safe persistence, no billing-provider coupling in kernel.
5. **Activation/Operation Policy Gate** — server-authoritative module/operation policy with ADR-0007 expiry behavior.
6. **Modules Admin UX Gate** — inventory/locked/degraded/read-only states; UI reflects policy but does not enforce it alone.
7. **Multisite/Clone/Restore Gate** — allocation semantics and recovery evidence.
8. **Release Gate** — only after exact Free/Pro package tests, compatibility matrix, WordPress.org Plugin Check for Free, security review and separate release authorization.

## 8. Tests required before claiming Paid/Pro support

At minimum:
- Free artifact contains CPT, Taxonomy and required Platform only; no Pro module implementation source.
- Pro artifact without compatible Free fails closed without fatal error or migrations.
- Free remains functional with Pro absent, expired or incompatible.
- product-Pro manifests cannot register as commercially Free by mistake.
- active entitlement permits only the intended Pro management/operations.
- confirmed expiry preserves definitions/data and safe deployed output according to ADR-0007.
- verification outage/stale cache is distinct from expiry.
- entitlement cannot bypass WordPress capability/Policy checks.
- membership entitlement cannot satisfy product-license entitlement and vice versa.
- Multisite child/site scope cannot borrow another allocation implicitly.
- clone/restore cannot duplicate an allocation merely from copied local state.
- diagnostics and support bundles redact secrets/tokens/signed entitlement payloads.
- exact supported Free×Pro version combinations execute the ADR-0010/P-006 compatibility evidence.

## 9. Audit conclusion

The product split is not actually ambiguous:
- **2 permanently Free product modules** — CPT and Taxonomy;
- **1 shared Platform Core surface** — not sold as a module;
- **53 Pro canonical surfaces** — delivered through the separate Pro add-on as they become released/product-ready.

What is incomplete is the **runtime/distribution implementation of that already accepted commercial architecture**.

The most important blockers are:
1. product-Pro modules are currently represented by `free` manifests in bounded runtime work;
2. base bootstrap currently contributes product-Pro modules;
3. the current distributable copies the whole `frameworks/` tree rather than proving a Free-vs-Pro physical source boundary;
4. ADR-0010 compatibility evidence remains unexecuted before a real Pro artifact can be certified.

Do not solve these by hiding shipped Pro code behind UI or by embedding paid source into the WordPress.org Free artifact. The implementation must preserve the accepted two-package architecture.