# ADR-0169 — Pro Updater TUF Canonical Evidence Refinement

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP52`  
Development authorization: **NOT GRANTED**

## Decision

Accept the in-place refinement of `docs/QUALITY/PRO-UPDATE-TUF-EXECUTABLE-EVIDENCE-PROTOCOL.md` from TU-01…TU-44 to **TU-01…TU-176**, preserving every original fixture.

Future certification must pin the exact TUF specification version/patch and verifier implementation tested. The architectural commitment remains TUF 1.0-compatible Root/Targets/Snapshot/Timestamp semantics, not a timeless claim that any future patch/verifier is automatically certified.

The expanded matrix covers verifier/parser behavior, trusted Root bootstrap, sequential Root rotation, role thresholds/key custody, metadata freshness/rollback/mix-and-match, delegations/channel/product target resolution, Product entitlement separation, target download/archive safety, Free↔Pro/schema compatibility, replacement/migration/recovery, build/CI artifact provenance, repository operations, Multisite and adversarial regression.

## Preserved invariants

- Account identity, Product entitlement and download authorization never substitute for TUF authenticity.
- Ordinary API/CDN responses cannot add Root trust.
- Target bytes execute only after trusted metadata graph + hash/length + archive/compatibility gates.
- Expired/rolled-back/mix-and-match metadata fails closed.
- Production signing/root keys cannot be exposed to untrusted PR/web-request paths.
- Package rollback and database/schema rollback are distinct recovery truths.
- Free remains platform authority but does not become a weaker external Pro update trust root.

## Evidence status

- TU fixtures documented: **176**
- TU fixtures executed: **0/176**
- TUF verifier/repository/package/runtime certifications: **0**

No metadata repository, signing key, target ZIP, updater client, package download/staging/replacement, migration or health test was created/executed.

## Consequence

`P0-M00-WP52` is planning-complete once canonical registries and Draft PR synchronize. If no production-grade verifier can meet the protocol, automated Pro updates remain blocked rather than falling back to weaker signed-JSON semantics.
