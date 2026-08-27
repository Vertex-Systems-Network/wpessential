# ADR-0031 — Frontend Dashboard Route & Component Runtime

Status: **Accepted architecture / router-renderer implementation pending**  
Date: 2026-08-27

## Decision

Frontend Dashboard Builder follows:

**Dashboard Definition → Compiled Route/Component Descriptor → Server Route Resolution → Policy → Component Renderer**.

Client-side navigation is an enhancement only and never the authorization source of truth.

Routes have stable UUIDs, typed path parameters and independent server-side access Policy. Navigation visibility does not grant/revoke route access.

Third-party builder content is referenced through certified adapters; proprietary builder payloads are not canonical WPE Dashboard data.

## Why

This prevents:
- client-side-only authorization;
- arbitrary PHP routing;
- builder lock-in becoming platform data model;
- direct-route IDOR;
- unsafe shared caching across users.

## Consequences

- publish-time route compilation/validation required;
- direct URL requests and SPA-style navigation must yield same authorization result;
- private Dashboard output cache keys include principal/access/policy context;
- authenticated/private routes default noindex;
- only active route/component assets load.

## Evidence still required

After explicit consent:
- WordPress rewrite/router implementation comparison;
- permalink/multisite/path collision fixtures;
- direct-route IDOR tests;
- safe return/login redirects;
- component cache/asset isolation;
- builder adapter certification;
- mobile/accessibility/performance tests.

Supporting doc: `docs/ARCHITECTURE/FRONTEND-DASHBOARD-ROUTE-RUNTIME-MODEL.md`.