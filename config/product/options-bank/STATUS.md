# Bank Status

Canonical Master Options Bank discovery currently has three seeded surfaces: CPT, Taxonomy, and Fields / Field Groups. The remaining canonical surfaces are unseeded unless `../options-bank-progress.json` says otherwise.

Surface 3 — Fields / Field Groups — has a WordPress 7.1-era native disposition audit candidate that promotes it to `NATIVE_AUDITED` only when the exact source head is CI-certified. Market audit and final Bank review remain separate gates.

`BANK_SURFACE_SEEDED` is discovery truth only. `NATIVE_AUDITED` certifies the relevant native WordPress/platform inventory only; it does not imply market audit, Bank review, implementation readiness, runtime certification, or shipped product parity.

Use `../options-bank-progress.json` as the canonical machine-readable count and lifecycle-status source, and `../options-bank-audits/` for lifecycle audit evidence.
