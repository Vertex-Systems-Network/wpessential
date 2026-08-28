# ADR-0183 — URL Redirection & Routing Manager

Status: **Accepted planning architecture / evidence pending / no development authorization**
Date: 2026-08-29

## Context

The owner requested a deep audit of the WordPress Redirection plugin/source and asked WPEssential to plan a market-competitive equivalent within its shared architecture.

Current market evidence demonstrates durable demand for redirect management, regex/query matching, permalink-change redirects, conditions, 404 monitoring, logging, headers, server exports and import/export. WPE has related primitives but no canonical redirect/routing owner.

## Decision

Accept a new user-facing Pro module surface:

**URL Redirection & Routing Manager**.

Canonical product specification:
`docs/MODULES/URL-REDIRECTION-ROUTING-EXHAUSTIVE-SPEC.md`

## Architecture

The module owns Redirect Definitions, Groups, request match/action compilation, 404/redirect operational records and routing simulation. It reuses shared Policy, Conditional Logic, JobService, Audit, Import/Export, Cache, Rate Limit, Privacy and Multisite services.

It must not become an arbitrary request-code engine.

## Key accepted semantics

- exact/prefix/bounded regex matching;
- explicit URL normalization/query policies;
- typed conditions/actions;
- safe HTTP response codes/targets/headers;
- permalink-change monitoring;
- 404 monitoring with privacy controls;
- chain/loop/collision analysis;
- simulator/dry trace;
- lossiness-aware Apache/Nginx export;
- REST/Abilities/MCP/AI through normal Policy boundaries;
- compiled fast path and bounded regex candidate sets;
- explicit site/network ownership.

## Evidence

Future evidence namespace: **RDR-001…RDR-176**, currently **0/176 executed**.

## Scope impact

This is one new module/platform surface beyond ADR-0177’s 43-surface scope. Expanded denominator is finalized after the whole market-expansion batch.

## Safety

No open redirect from untrusted values, arbitrary PHP/JS/shell routing callback, catastrophic unbounded regex, unsupported server export masquerading as success, or AI-published routing change without required approval.

## Development gate

Planning only. No redirect runtime, DB schema, request hook, log collection, import/export execution or server-file write is authorized.
