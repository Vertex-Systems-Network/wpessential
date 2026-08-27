# ADR-0107 — Watermarker / Media Rules Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Decision

Watermarker/Media Rules cannot advertise a host/editor/offload profile as supported until a future implementation passes `docs/QUALITY/WATERMARKER-MEDIA-EXECUTABLE-EVIDENCE-PROTOCOL.md`.

The protocol enforces:
- original-source checksum immutability;
- runtime `WP_Image_Editor`/MIME capability profiling rather than extension-based assumptions;
- JPEG/PNG/WebP/AVIF/GIF/HEIC-family behavior only where actually supported;
- EXIF/orientation/transparency/quality semantics;
- malicious SVG/font/large-image safety;
- deterministic derivative identity from source fingerprint + Rule revision + renderer/output profile;
- Job pause/resume/duplicate/concurrency behavior;
- offload/CDN commit and stale-cache semantics;
- protected/private derivative access inheritance;
- Multisite attachment-scope isolation.

## Current state

WM-01…WM-48 documented. **0/48 executed.**

## Development gate

No image decode/render/save, watermark, derivative, Job, offload fetch/upload or media mutation is authorized before explicit owner consent under ADR-0014.