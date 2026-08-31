# ADR-0171 — Remote Service Privacy & Retention Canonical Evidence Refinement

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP54`  
Development authorization: **NOT GRANTED**

## Decision

Accept the in-place refinement of `docs/QUALITY/REMOTE-SERVICE-PRIVACY-RETENTION-EVIDENCE-PROTOCOL.md` from RS-01…RS-30 to **RS-01…RS-176**, preserving the original privacy fixtures.

The expanded evidence covers remote resource/data classification, purpose/consent provenance, request/response minimization, OAuth/Account/Product License separation, Support tickets/messages/attachments, diagnostics bundles, logs/metrics/analytics, RR0–RR6 retention jobs, deletion/tombstones, export/erasure orchestration, backup/restore, clone/environment, Multisite/tenant isolation and failure/privacy regression.

## Preserved invariants

- Free activation/local-only use does not contact WPE-controlled remote services solely because WPE is installed or active.
- Account linking is not analytics/telemetry consent.
- Local disconnect, local privacy erase, remote Account deletion, provider deletion and backup expiry are separate operations/truths.
- OAuth/Vault/provider secrets are excluded from browser/log/export/support/diagnostics surfaces.
- Diagnostics require separate preview/explicit approval.
- Signed Product entitlement and TUF update trust remain separate from ordinary remote REST assertions.
- Clones/restores cannot silently impersonate the original production installation/account/site allocation.
- Remote retention/deletion states cannot be reported more strongly than actual service/provider evidence.

## Evidence status

- RS fixtures documented: **176**
- RS fixtures executed: **0/176**
- Remote privacy/retention runtime certifications: **0**

No network capture, OAuth/Account operation, service API call, diagnostics upload, Support attachment action, cleanup/deletion/export/erasure, provider call, backup restore, clone or Multisite runtime fixture was executed.

## Consequence

`P0-M00-WP54` is planning-complete once canonical registries and Draft PR synchronize. Executable evidence/implementation remains blocked by ADR-0014 and the Approval Ledger.
