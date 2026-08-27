# ADR-0103 — Dashboard Widgets Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Decision

Dashboard Widgets cannot be called production-ready until a future implementation passes `docs/QUALITY/DASHBOARD-WIDGETS-EXECUTABLE-EVIDENCE-PROTOCOL.md`.

The protocol preserves the accepted architecture:

`Widget Definition → compiled descriptor → server visibility Policy → trusted renderer → WordPress Dashboard adapter`.

Required evidence covers:
- Site vs Network Dashboard registration behavior;
- core/third-party widget coexistence;
- user layout/dismiss state;
- Component/Query/Listing/block/shortcode source trust;
- remote structured-data normalization through Safe HTTP;
- XSS/SSRF/token leakage;
- cache/user/site/access-generation isolation;
- async/Job refresh truthfulness;
- iframe origin/sandbox/CSP constraints;
- asset scoping and failure isolation;
- Multisite network aggregate behavior.

## Current state

DW-01…DW-36 documented. **0/36 executed.**

## Development gate

No WordPress Dashboard hook, widget registration, renderer, iframe, remote fetch, cache, async endpoint or benchmark is authorized before explicit owner consent under ADR-0014.