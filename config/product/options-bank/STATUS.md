# Bank Status

Canonical Master Options Bank discovery currently has four seeded surfaces: CPT, Taxonomy, Fields / Field Groups, and Relations. The remaining canonical surfaces are unseeded unless `../options-bank-progress.json` says otherwise.

Surface 3 — Fields / Field Groups — is `BANK_REVIEWED` at 618 records after exact-head semantic, native, market, WPE-future policy, compatibility, packaging, and browser/accessibility gates. This is planning readiness for downstream Atomic Option Contracts, not a claim that all records are implemented or shipped.

Surface 4 — Relations — is `BANK_SURFACE_SEEDED` at 135 classified discovery records. Relations owns persistent edge definitions, endpoint/cardinality/direction semantics, storage and indexes, pivot metadata, lifecycle, permissions, relation queries/APIs and integrity. Fields continues to own relationship selector/control configuration; the Relations seed references the Fields Schema Registry rather than duplicating field definitions. Native and provider-by-provider market audits remain open.

`BANK_SURFACE_SEEDED` is discovery truth only. `NATIVE_AUDITED` certifies relevant native WordPress/platform coverage. `MARKET_AUDITED` additionally certifies current competitor/specialist capability coverage. `BANK_REVIEWED` means those layers plus WPE-future semantics, duplicate resolution, rejection/defer policies, and ownership are reviewed enough to feed downstream Atomic Option Contracts.

No Bank lifecycle status by itself means runtime implementation, migration readiness, production certification, or shipped feature parity.

Use `../options-bank-progress.json` as the canonical machine-readable count and lifecycle-status source. Audit evidence lives in `../options-bank-audits/`; final surface-review certificates live in `../options-bank-reviews/`.
