# ADR-0154 — Shared Cache & Invalidation Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP37`

## Context

The platform architecture states that caching is opt-in by evidence, requires explicit invalidation, and must include relevant definition/version/permission context. Query, Listings, REST, Component Blueprint, Dynamic Value Resolver and other consumers each test parts of cache behavior, but no dedicated shared executable contract previously covered canonical key identity, dependency generations, authorization isolation, backend coexistence and Multisite invalidation as one platform service boundary.

## Decision

Accept `docs/QUALITY/SHARED-CACHE-INVALIDATION-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical shared cache/invalidation evidence contract.

It freezes **CAC-01…CAC-176** covering:
- canonical key/profile/schema identity;
- principal/public/resource authorization isolation;
- generation/dependency invalidation;
- TTL/fresh/stale/negative-cache semantics;
- concurrency/stampede/atomic update behavior;
- object/page/CDN/browser cache coexistence and backend failures;
- lifecycle/version/deploy/rollback behavior;
- privacy/security/data minimization;
- Multisite/network isolation;
- consumer integration boundaries;
- observability/performance/scale.

Independent certification classes remain separate: `CAC-K/A/G/T/C/B/L/P/M/O`.

## Preserved boundaries

- cache hit is not authorization;
- TTL is not a complete invalidation strategy;
- cache data is not canonical business truth, Audit history or Rate Limit security state;
- object cache, page cache, CDN cache and browser cache are distinct layers;
- current blog context does not establish durable cache ownership;
- consumer-specific Query/Listings/REST/CBP/DVR/etc. cache certification remains separate and is never promoted by CAC evidence.

## Evidence-gated decisions

This ADR does **not** select or certify:
- a canonical object-cache backend;
- a persistent cache requirement;
- exact TTL/stale windows;
- a single-flight/locking implementation;
- page-cache/CDN/browser integration support;
- exact storage/memory/cardinality budgets;
- performance or scale claims.

These remain profile- and evidence-gated.

## Current execution truth

- CAC fixtures documented: **176**.
- CAC fixtures executed: **0/176**.
- shared cache certifications: **0**.
- cache backends certified: **0**.
- consumer certifications unchanged.

## Development gate

No cache write/read/purge, object-cache adapter, page-cache/CDN/browser operation, benchmark or Multisite runtime action has been executed by accepting this ADR.

Execution and implementation remain prohibited until explicit scoped owner consent under ADR-0014 and the Approval Ledger.