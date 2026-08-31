# Bank Status

Canonical Master Options Bank discovery currently has four seeded surfaces: CPT, Taxonomy, Fields / Field Groups, and Relations. The remaining canonical surfaces are unseeded unless `../options-bank-progress.json` says otherwise.

Surface 3 — Fields / Field Groups — is `BANK_REVIEWED` at 618 records. Its review remains a surface-local certification and is intentionally independent of later global Bank growth.

Surface 4 — Relations — is `MARKET_AUDITED` at 144 records. Relations owns persistent edge definitions, endpoint/cardinality/direction semantics, native object-term and hierarchical post-parent adapters, storage/indexes, pivot metadata, lifecycle, permissions, relation queries/APIs and integrity. Relations Native Audit V1 remains certified with 44 current-WordPress dispositions and zero unresolved items. Relations Market Audit V1 dispositions six primary relation providers plus JetFormBuilder across eight capability families and closes with zero unresolved market items.

The market audit adds two evidence-backed current-market records only:
- `relations.marketaudit.admin_filter` — relation-specific Admin List filtering by related item;
- `relations.marketaudit.rest_read_policy` — relation-level REST read/public-access authorization separated from write permission.

Fields continues to own relationship selector/control configuration and typed pivot field definitions. Admin Columns owns column presentation semantics; Relations only owns the relation integration capability.

Relations is **not** `BANK_REVIEWED` yet. Formal whole-surface review remains the next lifecycle gate before Relations can feed final downstream Atomic Option Contracts as a fully reviewed surface.

`BANK_SURFACE_SEEDED` is discovery truth only. `NATIVE_AUDITED` certifies relevant native WordPress/platform coverage. `MARKET_AUDITED` additionally certifies current competitor/specialist capability coverage. `BANK_REVIEWED` means those layers plus WPE-future semantics, duplicate resolution, rejection/defer policies, and ownership are reviewed enough to feed downstream Atomic Option Contracts.

No Bank lifecycle status by itself means runtime implementation, migration readiness, production certification, or shipped feature parity.

Use `../options-bank-progress.json` as the canonical machine-readable global count and lifecycle-status source. Audit evidence lives in `../options-bank-audits/`; final surface-review certificates live in `../options-bank-reviews/`.
