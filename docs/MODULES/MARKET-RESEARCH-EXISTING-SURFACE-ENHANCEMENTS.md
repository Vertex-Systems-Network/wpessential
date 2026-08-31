# WPEssential — Market-Driven Existing-Surface Enhancement Plan

Status: **Phase 0 product planning / no development authorization**
Date: 2026-08-29
Source decision: ADR-0188 + `../RESEARCH/MARKET-GAP-AUDIT-2026-08.md`

## Purpose

Deep market research identified high-demand WordPress capabilities that WPE should support but **should not** create as independent new module runtimes because an existing WPE owner already fits the responsibility.

This document assigns those capabilities to canonical owners and specifies product options/flows deeply enough to prevent later ad-hoc invention.

---

# E01 — Deep Developer Diagnostics — extend Surface 31 Platform Diagnostics + Audit/Observability

Market pattern: Query Monitor-style request diagnostics.

## Navigation

`WPEssential → System → Diagnostics → Request Inspector`
- Current Request
- Database Queries
- PHP Errors
- Hooks & Actions
- HTTP Requests
- REST / Ajax
- Assets
- Templates / Blocks
- Abilities / Policy
- Jobs / Workflows
- Cache
- Environment
- Saved Diagnostic Sessions

## Request session

Fields:
- correlation ID;
- site/network scope;
- request type: frontend/admin/REST/Ajax/CLI/cron/job/MCP/Ability;
- route/action;
- actor ID only when permitted;
- start/end/duration;
- peak memory;
- DB query count/time;
- HTTP calls;
- cache stats where adapter supports;
- PHP warnings/notices/errors;
- hook/action counts;
- template/block/component trace;
- WPE definitions/revisions involved;
- Ability invocations;
- Policy decisions with redacted reason trace;
- Job/Workflow correlation;
- AI/MCP initiator metadata when applicable.

## Query diagnostics

- SQL fingerprint, not sensitive values by default;
- caller/component;
- duration;
- duplicate query grouping;
- slow threshold;
- error state;
- row count where known;
- query source owner;
- site scope;
- EXPLAIN advisory only for authorized developer profile and compatible DB;
- no arbitrary query execution control.

## Hook/action trace

- hook name;
- callbacks/provider where discoverable;
- priority;
- duration only when instrumentation enabled;
- WPE Ability/Event relation;
- excessive invocation detection;
- source plugin/theme/module.

## REST/Ajax/HTTP

- route/host;
- status;
- duration;
- response-size bucket;
- retry/correlation;
- Safe HTTP policy result;
- no auth header/token logging.

## Assets/template/block

- script/style handle;
- dependencies;
- duplicate/missing handle;
- route loading scope;
- template hierarchy result;
- block/component tree summary;
- builder adapter identity;
- React/runtime duplication warning.

## Data lifecycle

Diagnostics is ephemeral by default:
- live/current request;
- bounded recent buffer;
- explicit Saved Session;
- sanitized Support Package export.

Audit Log remains durable security/business activity truth. Diagnostics must not become a second audit warehouse.

## AI Prompt

- “Is admin screen ka slow part explain karo.”
- “Duplicate queries aur N+1 candidates identify karo.”
- “Policy deny trace explain karo without exposing hidden data.”

AI only receives explicitly permitted sanitized diagnostic fields.

## Evidence impact

Expand PLT/AUD/UI/BT/CI evidence during later refinement; no new module namespace required unless implementation reveals a genuinely separate runtime.

---

# E02 — Troubleshooting Session Mode — extend Platform Diagnostics shared service

Market pattern: Health Check / Troubleshooting Mode.

## Goal

Let an authorized operator reproduce plugin/theme conflicts for **their own troubleshooting session** without globally disabling site components for normal visitors/users, where technically supportable.

## Session wizard

1. Capture baseline environment/health.
2. Choose target site.
3. Select temporary plugin enable/disable profile.
4. Select temporary theme/profile where technically safe.
5. Detect persistent/page/object/CDN cache layers that may defeat isolation.
6. Show unsupported/conflicting components.
7. Start isolated operator session.
8. Reproduce issue.
9. Capture diagnostics.
10. Add plugins/components incrementally.
11. Compare baseline/current.
12. Exit and verify normal environment unchanged.

## Options

- duration/expiry;
- operator user;
- session token rotation;
- default active set;
- always-required plugins;
- MU-plugin visibility;
- network-activated plugin handling;
- theme override;
- object-cache warning;
- page-cache/CDN bypass token/profile;
- REST/Ajax propagation;
- background-job behavior: excluded by default unless isolation is proven;
- cookie/session namespace;
- support ticket link;
- saved troubleshooting profile.

## Safety

- cannot promise isolation for every cache/host/plugin before evidence;
- session expiry restores normal environment;
- no anonymous troubleshooting token;
- no global `active_plugins` mutation disguised as local mode;
- Network Admin controls network plugin exceptions;
- diagnostic session does not authorize code execution.

## Evidence impact

Future dedicated troubleshooting evidence may be created if architecture proves sufficiently complex; until then it is a Platform Diagnostics shared-service enhancement.

---

# E03 — Controlled Support Impersonation — extend User Profile + Role/Capability + Platform Support

Market pattern: User Switching.

## Goal

Allow authorized support/admin staff to reproduce a user's WPE/WordPress experience without requesting that user's password.

## Actions

- Switch to user;
- Switch off to anonymous preview where supported;
- Switch back to original operator;
- open target user's frontend/dashboard context in controlled session;
- attach reason/ticket reference;
- end all active impersonation sessions.

## Policy controls

- capability required;
- roles/users allowed as target;
- deny protected principals/Super Admin by default;
- same-site vs network target;
- reauthentication threshold;
- reason required;
- support ticket ID required optional;
- max duration;
- one/multiple concurrent support sessions;
- IP/device continuity advisory;
- MFA/recovery-sensitive screens blocked or separately privileged;
- destructive actions optionally disabled during impersonation;
- Woo/payment/security/profile credential operations stricter/blocked.

## Provenance

Every audit event must retain:
- effective user;
- original operator;
- impersonation session ID;
- target site;
- reason/ticket;
- start/end;
- actions performed.

Application/business events must never erase the original operator provenance.

## AI/MCP

Not MCP-public by default. AI can explain/setup policy, but cannot start impersonation autonomously.

---

# E04 — Native WordPress Cron Inspector — extend Cron Job Builder / JobService

Market pattern: WP Crontrol.

## Native event inventory

Columns:
- hook;
- next run;
- schedule/recurrence;
- interval;
- args fingerprint/redacted preview;
- source/provider where known;
- late/overdue status;
- site/network scope;
- JobService mapping if WPE-owned;
- action count/history link.

Actions:
- inspect;
- run now with authorization;
- reschedule;
- delete registered event;
- pause WPE-owned schedule;
- find provider/source;
- copy diagnostic data;
- search/filter by hook/provider/due state.

## Schedule registry

- standard WP schedules;
- custom schedule provider;
- interval;
- provider/plugin;
- registration health;
- collision/duplicate schedule names;
- missing provider warning.

## Diagnostics

- DISABLE_WP_CRON state;
- loopback/spawn health;
- system cron recommendation only as documentation, not automatic OS change;
- overdue event threshold;
- long-running hook observations where instrumentation exists;
- duplicate scheduling;
- orphan event provider;
- Action Scheduler adapter state separately.

## Rejected market behavior

Do **not** copy arbitrary PHP event creation into ordinary WPE UI. Registered typed Abilities/Job handlers remain the execution boundary under ADR-0004.

---

# E05 — Human-readable Activity History — extend Audit/Observability

Market pattern: Simple History/activity log products.

## Views

- Activity Feed;
- Security Activity;
- Configuration Changes;
- Content/User activity;
- WPE Definition changes;
- AI/MCP/Ability actions;
- Plugin/Theme/Core lifecycle;
- Filters;
- Reports/Export.

## Human-readable event card

- what happened;
- actor/effective actor;
- impersonating operator if applicable;
- resource;
- site;
- time;
- source/channel;
- safe before/after diff;
- correlation/Request/Job/Workflow/Prompt ID;
- result/error;
- reason/approval reference where applicable.

## Filters

- actor;
- event family;
- resource type;
- module;
- site/network;
- channel UI/REST/CLI/Workflow/Job/AI/MCP;
- success/failure;
- date;
- security risk;
- correlation ID.

This is a presentation/query layer over canonical Audit/domain histories, not a second activity datastore by default.

---

# E06 — Replace Media Source & Regenerate Derivatives — extend Watermarker / Media Rules

Market patterns: Enable Media Replace + Regenerate Thumbnails.

## Replace source wizard

1. Select attachment.
2. Inspect current source checksum/metadata/usage.
3. Upload replacement.
4. Validate MIME/extension/image security.
5. Choose mode:
   - replace source while preserve attachment ID;
   - create new attachment and migrate references through reviewed Plan;
   - replace source and rename file only with compatibility preview.
6. Compare dimensions/aspect/file size.
7. Preview derivative impact.
8. Backup/recovery requirement.
9. Apply through Media owner.
10. Regenerate selected sizes.
11. Purge/invalidate CDN/object cache where certified.
12. Verify references/derivatives.

## Derivative regeneration

Options:
- selected attachments;
- media query;
- selected registered image sizes;
- all current registered sizes;
- create missing only;
- force regenerate;
- delete obsolete derivatives after reviewed inventory;
- Watermarker rule revision;
- format/quality profile;
- offload/object-storage handling;
- JobService batch/concurrency;
- progress/errors/retry.

## Safety

- ordinary Watermarker still never mutates canonical source; explicit **Replace Source** is a separately privileged Media action;
- preserve attachment identity only if semantics allow;
- never delete unknown original/unregistered files by filename guess;
- offload provider truth requires adapter certification;
- backup/restore route shown before destructive replacement.

---

# Rejected market pattern — Generic Code Snippets

WPE does not add a generic arbitrary PHP/JS executable snippet module to ordinary no-code runtime.

Use instead:
- typed SDK extensions;
- registered Abilities/Event handlers;
- declarative Conditions/Transforms/Templates;
- theme/design CSS options in appropriate surface;
- conventional audited plugin/theme source for developer-owned executable code.

This preserves ADR-0004 and prevents AI/MCP from acquiring an arbitrary execution primitive.

## Development gate

All enhancements above are planning-only. No diagnostic instrumentation, troubleshooting-mode hook, impersonation session, cron mutation, audit collection expansion, media replacement or derivative generation is authorized.
