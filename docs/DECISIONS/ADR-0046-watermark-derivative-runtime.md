# ADR-0046 — Watermark Non-Destructive Derivative Runtime

Status: **Accepted architecture; image-editor/offload evidence pending**  
Date: 2026-08-27

## Decision

WPEssential Watermarker uses a non-destructive derivative model:

- original/current WordPress attachment source is never overwritten by standard watermark flow;
- derivative identity is derived from source fingerprint + published Rule revision + output profile + engine profile;
- derivatives live in WPE-owned storage namespace/adapter;
- private source produces private derivative;
- Rule/source changes mark derivatives stale and regenerate before old cleanup;
- consumer modules intentionally request WPE derivative rather than globally mutating attachment truth;
- animated/SVG/format support is capability-certified, not assumed.

## Why

This preserves recoverability, allows deterministic regeneration/cache invalidation, prevents irreversible media damage, and keeps media-offload/private-asset integrations explicit.

## WordPress compatibility principle

WordPress may retain an originally uploaded image separately from a processed/scaled attached file. WPE therefore resolves source mode explicitly and uses current WordPress media APIs rather than assuming one path equals every notion of “original.”

## Security/data boundaries

- no destructive overwrite of original;
- no public derivative from private source;
- no silent animation flattening;
- no arbitrary unreviewed SVG/XML transformation;
- no remote font/script fetch during watermark generation;
- WPE cleanup deletes only WPE-owned derivative objects.

## Remaining evidence

Exact registry table/indexes, GD/Imagick support, format/output matrix, EXIF/orientation, memory limits, concurrency, offload/CDN, multisite and stale-cleanup behavior require executable certification after owner consent.

See `docs/ARCHITECTURE/WATERMARK-DERIVATIVE-IDENTITY-STORAGE-RUNTIME.md`.

Development remains prohibited until explicit owner consent under ADR-0014.