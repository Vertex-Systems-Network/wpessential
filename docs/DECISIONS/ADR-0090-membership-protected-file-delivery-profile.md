# ADR-0090 — Membership Protected File Delivery Profile

Status: **Accepted paper security/delivery profile / executable evidence pending**  
Date: 2026-08-28

## Context

Membership access rules are insufficient if the protected file bytes remain reachable through a public origin URL. WPE needs explicit storage/delivery profiles and evidence levels before marketing a file as protected.

## Decision

Accept these delivery profiles:
- **PD1 — private local storage + PHP streaming** as the universal correctness baseline;
- **PD2 — server-accelerated private local delivery** after server-specific health/capability evidence;
- **PD3 — private object storage + short-lived signed delivery** with explicit bearer-URL expiry/revocation limitations;
- **PD4 — private CDN/tokenized stronger-revocation profile** only as future provider-specific evidence.

Accept certification levels **PC0–PC4**, from configured/unverified through origin isolation, authorization, transfer semantics and lifecycle/recovery proof.

## Core invariant

A supported protected-asset profile must have no unauthenticated bypass path to origin bytes within the certified deployment configuration.

Protecting only a page/button/shortcode does not satisfy this invariant.

## Authorization rule

Every new download initiation reauthorizes current outer WordPress/WPE security + Membership Policy unless a separately accepted short-lived token represents bounded authorization.

Revoked/expired/force-denied principals cannot start a new download under the accepted cache-generation model.

## Signed delivery truth

PD3 does not claim instant revocation of already-issued provider bearer URLs or immediate termination of in-flight bytes. Product wording and TTL policy must match actual provider semantics.

## Migration rule

Converting an existing public attachment to protected delivery requires a reviewed copy/move/origin-rule migration and post-cutover direct-origin verification. Metadata change alone is not proof that public bytes are inaccessible.

## Evidence still required

After explicit owner consent:
- PC1–PC4 direct-origin and authorization fixtures;
- PD1/PD2/PD3 transfer/cache/Range/header/resource evidence;
- signed URL expiry/leak/reconnect semantics;
- wrong-site/path traversal/cache bypass attacks;
- public→private migration;
- Backup/Restore/clone/lifecycle;
- concurrency/resource behavior.

Executed PC1+ certifications: **0**.

## Development gate

This ADR authorizes no file move, server configuration, download endpoint, signed token/URL, storage API call, Range implementation or test download. ADR-0014 explicit owner consent remains required.