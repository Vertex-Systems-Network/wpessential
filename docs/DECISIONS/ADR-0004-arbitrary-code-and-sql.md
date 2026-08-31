# ADR-0004 — Arbitrary Runtime Code and SQL

Status: **Accepted**  
Date: 2026-08-27

## Context

Early product assumptions included allowing administrators to place custom PHP/HTML/JavaScript in cron jobs and to operate Custom Tables with phpMyAdmin-like functions and queries.

Those concepts appear powerful but create fundamentally different security and reliability properties from normal WordPress configuration:

- user-entered PHP executed through `eval()` is arbitrary code execution;
- server-side HTML/JavaScript is not a meaningful cron execution model unless it is merely generated output passed to another action;
- unrestricted SQL permits data loss, privilege changes, schema destruction and bypasses WPEssential data/permission contracts;
- fatal loops/code errors can take down requests or background workers;
- AI-generated code would amplify those risks if treated as executable configuration.

## Decision

### Cron / Workflows
Standard WPEssential automation executes only registered actions, abilities, supported WordPress hooks with validated arguments, approved HTTP/webhook operations and other typed extension points.

No general user-entered PHP `eval()` executor is shipped as a standard Cron/Form/Workflow action.

### Custom Tables / Query
The canonical Query Builder stores a typed query AST and compiles it through supported adapters with prepared parameters.

The Custom Tables console defaults to safe data/schema tools and read-oriented SQL inspection such as bounded `SELECT` and `EXPLAIN` where raw SQL is exposed.

Unrestricted destructive multi-statement SQL is not a normal product primitive.

## Future developer escape hatch

A future explicitly unsafe Developer Mode may be researched, but only through a new ADR and threat model covering:

- who can enable/use it;
- WordPress.org/distribution implications;
- separate capability and re-authentication;
- backup/restore point requirement;
- audit/revisions;
- SQL/code classification;
- transaction/rollback boundaries;
- fatal/infinite execution recovery;
- multisite scope;
- AI exclusion by default;
- support implications.

Until such an ADR is Accepted, implementation must not introduce this capability indirectly.

## Consequences

### Positive
- dramatically smaller RCE/data-loss surface;
- automations remain testable and auditable;
- shared action/ability model remains enforceable;
- AI cannot turn natural-language output into unreviewed runtime code;
- easier WordPress.org/security review.

### Cost
Power developers cannot use WPEssential as an unrestricted embedded code/DB shell. They can extend it through documented PHP hooks/SDK code in normal plugins, where version control and deployment controls are available.

## Rejected alternative

“Only Administrators can run it” is not sufficient mitigation. Administrator accounts are compromised, mistakes occur, delegated admin models exist, and unrestricted execution still undermines recovery/testing guarantees.
