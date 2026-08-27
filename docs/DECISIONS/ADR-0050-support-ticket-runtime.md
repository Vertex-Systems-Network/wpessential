# ADR-0050 — Support Ticket Runtime, Attachment & Privacy Model

Status: **Accepted platform architecture / service implementation pending**  
Date: 2026-08-27

## Decision

WPEssential support tickets are remote-service-owned resources accessed securely from wp-admin:

- WPE service is authoritative for Ticket/Message/Attachment state;
- local WordPress stores only minimal cache/draft/idempotency metadata by default;
- account-link OAuth + dedicated support scopes/capabilities enforce access;
- replies use idempotency keys;
- attachments are private, bounded, scanned/redacted according to service policy;
- diagnostic bundles require preview + explicit user consent before upload;
- closing is distinct from deletion;
- sent-ticket deletion is truthful (`delete` or `deletion requested`) according to actual remote retention policy;
- full remote support conversation is not silently mirrored into local logs/database;
- Support service outage never disables Free/local WPE functions.

## Why

A remote support system needs one authority, strong account/site isolation, private attachments, retry safety and truthful retention semantics. Mirroring a second ticket database into each WordPress site would add stale state and unnecessary privacy risk.

## Security/privacy boundaries

- ticket UUID alone never authorizes access;
- diagnostic bundles exclude secrets/recovery keys/raw unrestricted DB dumps;
- attachments are not public uploads by default;
- unknown send outcome reconciles by idempotency rather than duplicate reply;
- AI may draft/read under permission but does not autonomously send/close/delete/upload by default.

## Remaining evidence

Remote API schemas, OAuth scope behavior, attachment scanner/storage, rate limits, privacy/export/delete flows, local cache and service reconciliation require implementation evidence after explicit owner consent.

See `docs/PLATFORM/SUPPORT-TICKET-RUNTIME-PRIVACY-MODEL.md`.

No service endpoint or local ticket implementation has been created.