# Bank Status

Canonical Master Options Bank discovery currently has four seeded surfaces: CPT, Taxonomy, Fields / Field Groups, and Relations. The remaining canonical surfaces are unseeded unless `../options-bank-progress.json` says otherwise.

Surface 3 — Fields / Field Groups — is `BANK_REVIEWED` at 618 records. Its review remains a surface-local certification and is intentionally independent of later global Bank growth.

Surface 4 — Relations — is `BANK_REVIEWED` at 144 records. Relations Native Audit V1 remains certified with 44 current-WordPress dispositions and zero unresolved items. Relations Market Audit V1 covers six primary relation providers plus JetFormBuilder across eight capability families with zero unresolved market items. Relations Bank Review V1 confirms zero Relations semantic aliases/effective derivations, zero unreviewed records, consistent rejection/defer/WPE-exceed policy, canonical ownership closure, and no count-only records after the market audit.

Relations owns persistent edge definitions, endpoint/cardinality/direction semantics, native object-term and hierarchical post-parent adapters, storage/indexes, pivot metadata, lifecycle, permissions, relation queries/APIs and integrity. Fields continues to own relationship selector/control configuration and typed pivot field definitions. Admin Columns owns generic column/filter presentation; Relations owns only relation-specific integration semantics such as the related-item predicate.

`BANK_REVIEWED` means the surface Bank is ready to feed downstream Atomic Option Contracts. It does **not** mean runtime implementation, migration readiness, production certification, or shipped feature parity.

`BANK_SURFACE_SEEDED` is discovery truth only. `NATIVE_AUDITED` certifies relevant native WordPress/platform coverage. `MARKET_AUDITED` additionally certifies current competitor/specialist capability coverage. `BANK_REVIEWED` means those layers plus WPE-future semantics, duplicate resolution, rejection/defer policies, and ownership are reviewed enough to feed downstream Atomic Option Contracts.

Use `../options-bank-progress.json` as the canonical machine-readable global count and lifecycle-status source. Audit evidence lives in `../options-bank-audits/`; final surface-review certificates live in `../options-bank-reviews/`.
