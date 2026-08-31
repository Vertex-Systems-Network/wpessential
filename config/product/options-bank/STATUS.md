# Bank Status

Canonical Master Options Bank discovery currently has four seeded surfaces: CPT, Taxonomy, Fields / Field Groups, and Relations. The remaining canonical surfaces are unseeded unless `../options-bank-progress.json` says otherwise.

Surface 3 — Fields / Field Groups — is `BANK_REVIEWED` at 618 records. Its review remains a surface-local certification and is intentionally independent of later global Bank growth.

Surface 4 — Relations — is a `NATIVE_AUDITED` candidate at 142 records. Relations owns persistent edge definitions, endpoint/cardinality/direction semantics, native object-term and hierarchical post-parent adapters, storage/indexes, pivot metadata, lifecycle, permissions, relation queries/APIs and integrity. Relations Native Audit V1 contains 44 explicit current-WordPress dispositions with zero unresolved items and adds seven evidence-backed native records for adapter identity, dependencies, validation and deletion semantics. Fields continues to own relationship selector/control configuration and typed pivot field definitions.

Relations is **not** `MARKET_AUDITED` or `BANK_REVIEWED` yet. Provider-by-provider market coverage remains the next lifecycle gate, followed by semantic/final whole-surface review if the market pass discovers overlaps or gaps.

`BANK_SURFACE_SEEDED` is discovery truth only. `NATIVE_AUDITED` certifies relevant native WordPress/platform coverage. `MARKET_AUDITED` additionally certifies current competitor/specialist capability coverage. `BANK_REVIEWED` means those layers plus WPE-future semantics, duplicate resolution, rejection/defer policies, and ownership are reviewed enough to feed downstream Atomic Option Contracts.

No Bank lifecycle status by itself means runtime implementation, migration readiness, production certification, or shipped feature parity.

Use `../options-bank-progress.json` as the canonical machine-readable global count and lifecycle-status source. Audit evidence lives in `../options-bank-audits/`; final surface-review certificates live in `../options-bank-reviews/`.
