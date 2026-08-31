# ADR-0120 — Notification System Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP04`

## Context

Notification System already has accepted Phase 0 product and persistence/delivery semantics:
- Rule configuration, logical Notification occurrence, per-recipient/in-app state and per-channel Delivery Attempt are separate domains;
- Notification creation, provider acceptance, confirmed delivery and user read state are distinct facts;
- Notification routing consumes Events/Workflow but does not become a second Workflow engine;
- recipient resolution timing is explicit;
- preferences/classification, quiet hours, digests and frequency caps are first-class policy;
- protected targets reauthorize access when opened/acted upon;
- large audiences use bounded JobService fan-out;
- Email Builder owns email rendering/provider truth and Connections owns webhook/provider security;
- NE1/PT-D is the first future physical benchmark baseline and NE2/PT-E is mandatory before final topology selection.

The remaining gap was a bounded executable evidence contract covering rule revisioning, event dedupe, fan-out, eligibility, preferences, quiet hours, digests, inbox/read state, channel truth, retry ambiguity, privacy, lifecycle, Multisite and scale.

## Decision

Notification production-readiness claims require the applicable fixtures in:

`docs/QUALITY/NOTIFICATION-SYSTEM-EXECUTABLE-EVIDENCE-PROTOCOL.md`

The protocol fixes **NT-01…NT-142** evidence covering:
- Rule Draft/publish/revision pinning and trigger safety;
- Occurrence durability, dedupe and concurrent trigger races;
- users/roles/capabilities/Query/relations/Membership/team/external recipient resolution;
- snapshot-vs-delivery-time recipient semantics and eligibility revalidation;
- preference classes, opt-out, frequency caps and misuse-resistant required/security policy;
- quiet hours, timezone/DST, delays and expiry;
- digest grouping, caps, collapse, eligibility and retry;
- in-app inbox authorization, read/unread/dismiss/revoke state and unread-count/cache consistency;
- safe token rendering, protected-content/action authorization, localization and renderer ownership;
- independent channels, ordered fallback, delivery truth, provider events and unknown-outcome reconciliation;
- JobService fan-out, worker crash, backpressure and large audiences;
- permissions, privacy/export/erase, secret/log redaction;
- Pro/dependency degradation, site lifecycle, clone/restore and Multisite isolation;
- NE1/PT-D vs NE2/PT-E physical, history, cache and 100/1k/10k-site scale evidence;
- explicit MUST-NOT/stop-the-line gates.

## Negative requirements locked

A certified Notification runtime MUST NOT:
- use Notification possession/action URL as authorization to protected content;
- allow cross-user/site inbox, preference, recipient or Delivery IDOR;
- label `wp_mail()`/SMTP/API handoff as confirmed Delivered without applicable certified evidence;
- infer inbox placement, human visibility/read or Notification read state from provider acceptance/open/click facts;
- repeat protected channel sends on duplicate event/Job contrary to configured dedupe/idempotency semantics;
- blindly retry/fallback after ambiguous provider outcome where duplicate delivery is plausible;
- let ordinary optional/marketing messages bypass preference/quiet-hours/frequency policy by self-labeling critical/system;
- execute unbounded synchronous fan-out or arbitrary PHP/JS/class/function logic from Notification configuration;
- leak provider credentials, raw secrets or sensitive object/meta dumps into Notification data/logs;
- bind provider events to another site by unscoped provider reference;
- blindly resend historical/ambiguous deliveries after restore/clone;
- expose another principal/site's unread state through cache/count errors.

## Physical topology and channel truth

This ADR does **not** finalize NE1 vs NE2 and does not upgrade any Email/Connection provider certification.

- `NE1/PT-D` remains first future benchmark baseline.
- `NE2/PT-E` remains mandatory comparison.
- Email delivery claims remain bounded by ADR-0058 and ET certification.
- Connection/webhook claims remain bounded by the relevant adapter/provider certification.

## Current state

NT fixtures documented: **142**.  
NT executed: **0/142**.  
Notification runtime certification: **none**.  
Final Notification physical topology: **OPEN / evidence-gated**.

No Notification table/row, trigger, fan-out, inbox mutation, digest, Job, email/webhook/provider call, migration, benchmark or runtime test was executed.

## Development gate

This is planning-only acceptance. Execution remains blocked until explicit scoped owner consent under ADR-0014 / `DEVELOPMENT-CONSENT.md` / `docs/APPROVAL-LEDGER.md`, plus applicable platform/Job/Workflow/channel prerequisites.