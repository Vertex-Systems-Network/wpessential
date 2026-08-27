# ADR-0108 — Frontend Dashboard Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Decision

Frontend Dashboard Builder cannot be considered runtime-ready until a future implementation passes `docs/QUALITY/FRONTEND-DASHBOARD-EXECUTABLE-EVIDENCE-PROTOCOL.md` for its certified WordPress/permalink/cache/Multisite/builder environment.

The protocol enforces:
- server-side route resolution + Policy for every protected direct request;
- route/menu visibility never acting as authorization;
- typed route/path normalization and IDOR resistance;
- safe intended-return/login redirects;
- authorization-aware navigation labels/counts/breadcrumbs;
- Component Blueprint/Listing/Form/CRUD/Profile action boundaries;
- server/client navigation parity;
- principal/site/revision/access-generation cache isolation;
- private noindex/sitemap safety;
- asset/component/builder dependency scoping;
- plain/pretty permalink and collision behavior;
- accessibility/mobile/RTL baseline;
- Multisite/network-policy isolation;
- large route/navigation graph budgets.

## Current state

FD-01…FD-48 documented. **0/48 executed.**

## Development gate

No rewrite rule, route hook, frontend render, browser test, cache mutation, asset build or WordPress runtime operation is authorized before explicit owner consent under ADR-0014.