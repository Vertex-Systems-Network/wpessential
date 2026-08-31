# WPEssential — Implementation / Product Readiness Matrix

Status: **structural architecture complete; product parity re-baselined and not yet certified**  
Last synchronized: **2026-08-31**

## 1. Important correction

The earlier Phase 0 statement that planning was “complete” referred to **structural planning**: canonical ownership, option routing, navigation ownership, system-pattern reuse, dependencies, Ability/Event boundaries, data ownership and no-bypass rules.

It MUST NOT be interpreted as proof that every WPEssential module had already enumerated and planned every market-relevant user-facing option or reached JetEngine/ACF/SCF/Meta Box/CPT UI/Redux-class product depth.

That interpretation is superseded.

## 2. Structural architecture state

The prior architecture work remains valid as the composition/ownership baseline:
- 56/56 canonical surface owners;
- 56/56 canonical routes;
- exactly-once ownership constraints;
- reusable system patterns;
- dependency/cycle mapping;
- Capability/Ability/Event boundaries;
- storage/lifecycle ownership;
- multisite boundaries;
- no-bypass rules.

WP112–WP118 / ADR-0207 through ADR-0213 remain historical evidence for that structural architecture.

The former “known exact planning gap: 0/0” applies only to the accepted structural inventory from that phase. It is **not a product-parity completeness statement**.

## 3. Product-parity reset

WPEssential now has a separate mandatory product completeness contract:

- `docs/PRODUCT/COMPETITOR-PARITY-CHARTER.md`
- `docs/PRODUCT/56-SURFACE-COMPETITOR-PARITY-MATRIX.md`
- `docs/PRODUCT/COMPETITOR-BENCHMARK-REGISTER.md`
- `config/product/competitor-parity-surfaces.json`
- `docs/ARCHITECTURE/NATIVE-WORDPRESS-OPTIONS-ADMIN-UX-V2.md`

All **56/56** canonical surfaces now target `PARITY_OR_EXCEED`.

A surface is not `PRODUCT_PLANNED` merely because an owner, route, Definition type or CRUD screen exists. It must have:
1. native WordPress/platform option inventory where relevant;
2. competitor capability inventory;
3. detailed Option/Feature Contract;
4. data/storage model;
5. UX information architecture;
6. permissions/Policy;
7. REST/Ability behavior;
8. import/export/migration;
9. multisite behavior;
10. extension points;
11. performance/scaling plan;
12. accessibility plan;
13. runtime/product tests;
14. explicit WPE exceed items.

## 4. Product certification count

Current target registry: **56/56 surfaces registered for parity-or-exceed planning**.

Current `PRODUCT_PARITY_CERTIFIED`: **0/56**.

This is intentional and truthful. Existing certified runtime slices are foundations, not claims of full competitor parity.

## 5. Existing implementation evidence

Development has already progressed beyond the historical “no runtime verified” Phase 0 statement. Certified merged slices include the platform foundation and bounded CPT/Taxonomy runtime/admin/linking work.

Those slices prove architecture/runtime contracts such as:
- Definition ownership;
- canonical Abilities/Policy;
- validation/preflight;
- registration lifecycle;
- packaged browser/accessibility evidence;
- WordPress/PHP compatibility evidence.

They do **not** yet prove complete CPT/Taxonomy product parity because the full native/competitor option surface and Admin UX V2 remain to be implemented.

## 6. Required lifecycle from now on

Use these distinct states:

```text
OWNERSHIP_MAPPED
BENCHMARKING
CAPABILITY_INVENTORY_COMPLETE
OPTION_CONTRACT_COMPLETE
UX_CONTRACT_COMPLETE
PRODUCT_PLANNED
IMPLEMENTING
RUNTIME_CERTIFIED
PRODUCT_PARITY_CERTIFIED
```

`RUNTIME_CERTIFIED` is never automatically promoted to `PRODUCT_PARITY_CERTIFIED`.

## 7. Immediate roadmap rule

Before starting a new feature surface, development must read its section in the 56-surface competitor matrix and expand it into a machine-readable per-option contract before substantial UI/runtime implementation.

For CPT/Taxonomy, continue from the already adopted Native WordPress Options & Admin UX V2 contract.

For Fields and Settings Pages, parity planning must explicitly cover ACF/SCF/Meta Box/JetEngine/Redux-class field/control breadth rather than a minimal subset.

For every other surface, use the strongest specialist competitors listed in the benchmark register/matrix and update research at implementation time.

## 8. Completion claim rule

The words “complete”, “full”, “parity”, “production-ready” or equivalent MUST NOT be used for a module unless the statement names which gate is complete and evidence exists for that gate.

Examples:
- “CPT runtime foundation certified” — acceptable when runtime evidence exists.
- “CPT product complete” — prohibited until full parity inventory and certification pass.
