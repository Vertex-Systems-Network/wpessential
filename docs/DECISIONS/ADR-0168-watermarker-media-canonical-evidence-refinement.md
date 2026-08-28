# ADR-0168 — Watermarker / Media Rules Canonical Evidence Refinement

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP51`  
Development authorization: **NOT GRANTED**

## Decision

Accept the in-place refinement of `docs/QUALITY/WATERMARKER-MEDIA-EXECUTABLE-EVIDENCE-PROTOCOL.md` from WM-01…WM-48 to **WM-01…WM-176**, preserving all original fixtures.

The expanded matrix adds Rule/version lifecycle, source/parser/file safety, geometry/typography/composition, DSR/DVR/media authorization, Job/concurrency/commit reconciliation, offload/CDN/CAC behavior, privacy/Backup/lifecycle, Multisite, performance, visual-quality and upgrade-regression evidence.

## Preserved invariants

- Standard WPE processing never modifies original source bytes/checksum.
- File extension never proves decoder/encoder support.
- Derivative currentness requires complete verified local/remote commit.
- Source fingerprint + Rule revision + renderer profile participate in derivative identity.
- Private/protected media cannot become public merely because a derivative exists.
- Cache/CDN state is projection, not canonical derivative truth.
- Provider/offload credentials remain server-side/Vault-owned.
- Duplicate/retry/stale workers cannot publish an obsolete derivative as current.
- Malicious/corrupt media cannot gain arbitrary file/network/code access.

## Evidence status

- WM fixtures documented: **176**
- WM fixtures executed: **0/176**
- Format/editor/offload/runtime certifications: **0**

No image decode/render/save, watermark/derivative generation, Job, offload request, cache/CDN mutation, media deletion, Multisite action or benchmark executed.

## Consequence

`P0-M00-WP51` is planning-complete once source-of-truth files and Draft PR synchronize. Implementation/executable evidence remains blocked by ADR-0014 and the Approval Ledger.
