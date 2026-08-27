# WPEssential Membership — Privacy, Retention, Export & Erasure Defaults

Status: **Phase 0 product/security planning / no runtime implementation authorized**  
Related: ADR-0013/0015/0016/0019/0020, Membership Runtime candidate, Privacy/Data Classification contract.

This is technical/product planning, **not jurisdiction-specific legal advice**. Sites may need stricter/longer retention based on law, contracts, accounting, disputes or regulated-industry requirements.

## Goals

1. Retain enough structured history to explain access and reconcile billing without hoarding unnecessary personal data.
2. Make high-volume/security logs opt-in or short-lived by default.
3. Separate current authorization state from historical audit and external provider records.
4. Integrate with WordPress personal-data exporter/eraser mechanisms where applicable.
5. Never store raw payment-card credentials.

---

# Data categories

## M1 — Plan/access definitions
Examples:
- Plan definitions;
- Access Rules;
- benefit/drip definitions;
- Plan Groups.

Classification: P0/P1 configuration depending publication.

Retention:
- versioned Definition history according to platform revision policy;
- archived definitions retained while referenced by historical Enrollment/audit records;
- purge only through explicit cleanup with dependency check.

Personal-data exporter/eraser: generally not user personal data by themselves.

## M2 — Current Enrollment state
Examples:
- user/subject ID;
- Plan reference;
- lifecycle state;
- effective/trial/period/grace/end timestamps;
- source type/reference.

Classification: P2.

Default retention:
- retain while Enrollment exists;
- terminal Enrollment history retained by default for operational/audit continuity;
- site can configure anonymization/retention policy for old terminal history.

Do not delete terminal history automatically just because access ended.

## M3 — Enrollment transition history
Examples:
- from/to state;
- timestamps;
- source/event reference;
- actor;
- reason code;
- correlation/idempotency reference.

Classification: P2, potentially P4 depending business notes.

Default:
- retain structured history indefinitely unless site config selects a bounded policy;
- free-text admin notes discouraged/minimized;
- secrets/raw billing payloads excluded.

Reason: this history explains why access changed and supports reconciliation/disputes.

## M4 — Materialized Entitlements
Classification: P2 derived operational data.

Retention:
- only current/necessary active derived rows plus bounded terminal/rebuild state;
- stale materialized grants are removed/rebuilt when source no longer valid;
- no need to retain indefinite duplicate entitlement history if Enrollment/transition history already explains it.

Default candidate:
- current materialized state only;
- historical entitlement changes represented through Enrollment/Plan revision/audit records.

## M5 — Manual force allow/deny overrides
Classification: P2/P4 security/access data.

Default:
- active override retained while effective;
- expired/revoked override history retained for audit;
- UI strongly encourages expiry on temporary overrides;
- cleanup can anonymize actor notes but preserve decision/time/resource trace where operationally required.

## M6 — Billing/provider references
Examples:
- provider customer ID;
- subscription/order/purchase reference;
- connection reference;
- normalized status facts.

Classification: P2.

Default:
- retain while needed to reconcile current/historical Enrollment;
- never store card PAN/CVC/raw payment credentials;
- provider customer/subscription IDs are treated as personal/account-linked data even when opaque.

Deletion/anonymization:
- external references may be retained or pseudonymized when necessary to preserve billing/access history;
- exact deletion behavior can depend on external provider and site policy.

## M7 — Raw/verified provider event inbox
Preferred owner: shared Webhooks & Connections service.

Classification: P2/P3/P4 depending payload.

Default candidate:
- store **minimal normalized receipt metadata**, not full raw payload, whenever possible;
- signature/replay/idempotency identifiers retained only as long as needed for replay protection/reconciliation;
- full raw payload retention is off by default unless a provider adapter explicitly needs a bounded diagnostic window.

If raw payload retention enabled:
- sensitive fields redacted/minimized;
- configurable short retention;
- not included in generic exports/support bundles.

## M8 — Membership teams/seats
Data:
- team owner/member user IDs;
- internal team role;
- seat state;
- joined/removed timestamps.

Classification: P2.

Default:
- active team membership retained while team exists;
- removed-member history retained for bounded operational audit according to site policy;
- no WordPress role copy used as sole history.

## M9 — Invitations
Data:
- recipient email or normalized matching value;
- token hash;
- team/role/seat context;
- inviter;
- expiry/status.

Classification: P2/P3-like token security metadata.

Defaults:
- raw redeem token never stored after issue;
- expired/revoked pending invitations eligible for automatic cleanup;
- candidate default cleanup: **30 days after terminal invitation state**, configurable;
- accepted invitation can retain minimal audit reference while email can be minimized/anonymized when no longer needed.

30 days is a product default candidate, not a legal retention mandate.

## M10 — Protected download/access logs
Classification: P2/P4.

Default: **off** for detailed per-download logging unless site enables it for security/compliance/analytics purpose.

When enabled:
- store user/resource/time/outcome, not file contents;
- IP off by default or separately enabled with retention warning;
- configurable retention;
- high-volume logs sampled/aggregated where appropriate.

Security deny events can be recorded in Audit/Protector according to separate retention policy without turning Membership into a surveillance log.

## M11 — Membership notifications/email history
Owned by Notification/Email modules.

Membership stores only safe source/event references where needed, not duplicate email bodies forever.

## M12 — Audit records
Owned by shared Audit service.

Membership audit metadata:
- actor;
- action;
- resource/Enrollment/Plan reference;
- outcome;
- reason code;
- before/after safe summary.

No secrets/full protected content.

Retention follows Audit policy; Membership-specific security events can require stronger minimum retention than ordinary debug logs.

---

# Retention policy model

Each category supports only meaningful modes:

- `retain_indefinitely`
- `retain_for_duration`
- `retain_until_terminal_plus_duration`
- `anonymize_after_duration`
- `derived_current_state_only`
- `disabled/no_collection`

Not every mode appears for every category.

Site/global defaults can be overridden per category only by users with appropriate privacy/operations capability.

## Candidate product defaults

| Category | Candidate default |
|---|---|
| Plan/Rule definitions | retain versioned history while referenced |
| Current Enrollment | retain |
| Terminal Enrollment/transition history | retain indefinitely until site sets policy |
| Materialized Entitlements | current derived state only |
| Overrides | retain history |
| Billing references | retain while needed for reconciliation/history |
| Raw provider payload | disabled/minimal normalized metadata |
| Team membership history | retain; site-configurable archival cleanup |
| Invitations | cleanup 30 days after terminal state |
| Detailed protected-download log | disabled |
| IP/device logging | disabled unless explicit purpose |

These defaults prioritize access explainability and minimization; they are not compliance guarantees.

---

# WordPress Personal Data Export

Membership exporter should return human-understandable groups such as:
- Membership Enrollments;
- Plan/access names applicable to user;
- lifecycle/effective dates;
- team membership;
- active/manual access grants where appropriate;
- invitation history attributable to user/email where appropriate;
- billing provider references only when useful and safe;
- selected membership access/security history according to privacy policy.

Do not export:
- internal secrets;
- webhook signatures;
- other members' PII;
- private admin notes not appropriate for data-subject export;
- protected resource content merely because user had access.

Export is paginated/batched according to WordPress privacy tool patterns.

---

# Personal Data Erasure / Anonymization

Eraser evaluates each category and returns:
- erased;
- anonymized;
- retained with safe reason;
- external action required/unsupported.

## Candidate behavior

### Active Membership
Do not automatically destroy an active account/access relationship merely because generic privacy erasure is requested without clear site policy. Surface that active operational relationship requires explicit business/admin resolution.

### Terminal Enrollment history
Prefer anonymization/pseudonymization where preserving lifecycle/audit history is operationally necessary:
- replace direct user linkage with anonymized subject reference where feasible;
- minimize free-text notes;
- retain Plan/state/timestamps/reason codes.

### Team membership
Remove/anonymize direct member identity after policy allows while preserving aggregate/history when needed.

### Invitations
Delete expired invitation PII/token metadata after retention window.

### Download/access logs
Delete/anonymize attributable records according to configured retention and audit/security exception policy.

### Billing references
Do not invent deletion at external provider. WPE can remove/pseudonymize local references if no longer operationally needed and report external provider as separate processor/source.

---

# User deletion

When WordPress user is deleted, WPE must not leave broken foreign references silently.

Candidate choices depending module/site policy:
- block user deletion until Membership impact resolved;
- transfer team ownership;
- terminate/revoke current Enrollment;
- anonymize historical Enrollment subject;
- preserve provider reference only when needed;
- remove derived current Entitlements;
- release team seat;
- revoke invitations authored by deleted user only when policy requires.

No default cascade that destroys historical billing/access evidence without preview.

---

# Plan deletion/archive interaction

Published Plan with current/history Enrollment is archived, not physically erased from history by default.

Historical rows keep stable Plan UUID/revision snapshot reference even if Plan no longer available for new signup.

Privacy erasure of one user does not require deleting shared Plan definitions.

---

# Backup implications

Backups can retain deleted/anonymized P2/P4 data until backup retention expires.

Privacy UI/docs must explain:
- live erase does not rewrite historical backup archives;
- restoring an older backup may reintroduce previously erased data;
- post-restore reconciliation/privacy cleanup may be required;
- backup encryption/key policy applies independently.

---

# Import/migration implications

Imported membership records immediately inherit target retention/privacy classification.

Migration dry run reports:
- imported PII categories;
- billing references;
- invitation/access log history;
- source data that will be dropped/anonymized;
- retention policy that will apply after import.

Do not keep entire source-plugin payload indefinitely as a hidden migration archive.

---

# Support/diagnostics

Membership diagnostics include counts/IDs/status summaries, not member content/PII by default.

Support bundle default excludes:
- member lists;
- emails;
- provider customer IDs unless explicitly selected and redacted;
- webhook payloads;
- protected download logs;
- Plan-restricted content.

---

# Admin UI controls

Membership → Settings → Privacy & Retention:

## General
- show data categories and current classifications;
- default retention policy summary;
- link to WordPress privacy tools;
- privacy policy helper content/version.

## Enrollment history
- terminal history mode;
- duration when bounded;
- anonymize vs delete where allowed;
- show impact warning.

## Provider event metadata
- normalized receipt retention;
- raw diagnostic payload retention toggle (off default);
- raw payload duration if enabled;
- redaction status.

## Invitations
- cleanup after terminal duration — candidate default 30 days;
- cleanup job health.

## Access/download logging
- enabled off by default;
- log successful downloads;
- log denied attempts;
- include IP separate toggle off;
- retention duration;
- sampling/aggregation if supported.

## Team history
- removed seat/member retention mode.

## Export/erase
- show registered exporter/eraser health;
- preview category behavior;
- last privacy cleanup job status.

Changes are audited.

---

# Retention job requirements

Future cleanup jobs must be:
- chunked;
- idempotent;
- resumable;
- dependency-aware;
- race-safe against record reactivation;
- dry-run/report capable for destructive categories;
- observable by counts, not deleted contents.

Cleanup cannot make authorization stale; removing terminal history and removing active Entitlements are different operations.

---

# Future acceptance tests — NOT AUTHORIZED

After development consent:
- exporter pagination on large user history;
- eraser/anonymizer preserves active authorization safely;
- invitation 30-day cleanup boundary;
- raw-provider retention off by default;
- access logs off by default;
- no secrets/card data in export/logs;
- team member deletion/ownership transfer;
- WordPress user deletion impact flow;
- restored backup reintroduces erased data warning/reconciliation;
- retention job race with Enrollment reactivation;
- provider references retained/removed according to policy;
- multisite isolation.

No retention job/exporter/eraser/runtime table has been implemented or run.