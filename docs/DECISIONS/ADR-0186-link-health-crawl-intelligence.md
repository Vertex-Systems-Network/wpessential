# ADR-0186 — Link Health, Broken Link & Crawl Intelligence

Status: **Accepted planning architecture / evidence pending / no development authorization**
Date: 2026-08-29

## Context

Market research shows substantial demand for broken-link checking and remediation across internal/external links, images and rendered content. This capability is not equivalent to request-time redirection: it requires content/source extraction, URL inventory, Safe HTTP checks, scheduling, graph/orphan semantics and Fix Plans.

## Decision

Accept new Pro module surface:

**Link Health, Broken Link & Crawl Intelligence**.

Canonical spec:
`docs/MODULES/LINK-HEALTH-BROKEN-LINK-CRAWLER-EXHAUSTIVE-SPEC.md`

## Architecture

The module owns Scan Definitions, link inventory/occurrences, issue lifecycle and crawl/verification results. It uses JobService, Safe HTTP, DSR, Rate Limit, Cache, Policy, Privacy and Multisite services.

Fixes are delegated to owning modules such as Redirect Manager, Search/Replace, Media or content editors via reviewed Fix Plans.

## Accepted semantics

- stored-content and rendered-route source profiles;
- internal/external/media/link extraction;
- direct WordPress internal resolution where possible;
- Safe HTTP with SSRF/TLS/DNS/redirect controls;
- truthful status classes including inconclusive/restricted/rate-limited;
- fragment checks where certified;
- redirect chain/loop intelligence;
- broken media checks;
- internal graph/orphan analysis;
- incremental/scheduled scans;
- issue state/history;
- Fix Plans and verification;
- privacy/minimized URL logging;
- Multisite/domain mapping;
- REST/Abilities/MCP/AI.

## Evidence

Future namespace: **LNK-001…LNK-176**, executed **0/176**.

## Safety

No SSRF/private-network crawling, no credential forwarding, no automatic content mutation from scan output, no definitive “broken” label for inconclusive responses, no uncontrolled host load and no protected-source existence leakage.

## Development gate

No crawler, HTTP scan, URL fetch, content rewrite or scheduled task is authorized.
