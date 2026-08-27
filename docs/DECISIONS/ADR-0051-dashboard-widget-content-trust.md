# ADR-0051 — Dashboard Widget Content Trust & Runtime Model

Status: **Accepted security architecture / WordPress adapter evidence pending**  
Date: 2026-08-27

## Decision

WPEssential Dashboard Widgets use compiled server-side descriptors with server-evaluated visibility Policy and trusted content classes.

Accepted boundaries:
- shared Component Blueprint is preferred flexible renderer;
- structured Banner/Announcement is first-class;
- remote responses are data fetched through Safe HTTP, not trusted wp-admin HTML/JS;
- arbitrary iframe is OFF; iframe support requires registered trusted embed profile + restrictive sandbox/origin policy;
- arbitrary PHP/JavaScript is not a no-code widget source;
- shortcodes/server blocks are adapter-controlled and do not gain capabilities from Dashboard placement;
- cache varies by authorization/user context;
- optional module assets load only where widget/screen requires them;
- widget failure cannot take down whole Dashboard.

## Why

wp-admin runs in a privileged origin. Treating remote HTML, scripts or arbitrary iframe URLs as normal widget content turns content-builder convenience into administrator XSS/supply-chain risk.

## Remaining evidence

WordPress Dashboard/meta-box registration, user layout/dismiss behavior, admin-XSS tests, iframe CSP/sandbox behavior, shortcode/block contexts, cache isolation, multisite and asset performance require executable certification after owner consent.

See `docs/SECURITY/DASHBOARD-WIDGETS-CONTENT-TRUST-RUNTIME.md`.

No Dashboard widget implementation has been created.