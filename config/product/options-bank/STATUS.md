# Bank Status

Canonical Master Options Bank discovery currently has three seeded surfaces: CPT, Taxonomy, and Fields / Field Groups. The remaining canonical surfaces are unseeded unless `../options-bank-progress.json` says otherwise.

Surface 3 — Fields / Field Groups — is certified through the native WordPress audit and has a market-audit candidate covering current provider ecosystems. `MARKET_AUDITED` is valid only when the exact source head passes all applicable CI gates.

`BANK_SURFACE_SEEDED` is discovery truth only. `NATIVE_AUDITED` certifies relevant native WordPress/platform coverage. `MARKET_AUDITED` additionally certifies the current competitor/specialist capability matrix. Neither status implies implementation readiness, runtime certification, shipped product parity, or final whole-surface Bank review.

Use `../options-bank-progress.json` as the canonical machine-readable count and lifecycle-status source, and `../options-bank-audits/` for lifecycle audit evidence.
