# ADR-0165 — Dashboard Widgets Canonical Evidence Refinement

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP48`  
Development authorization: **NOT GRANTED**

## Decision

Accept the in-place refinement of `docs/QUALITY/DASHBOARD-WIDGETS-EXECUTABLE-EVIDENCE-PROTOCOL.md` from the original DW-01…DW-36 matrix to **DW-01…DW-176**. Original fixtures remain preserved and traceable.

The refinement adds bounded evidence for Definition/revision lifecycle, WordPress Site/Network/User Admin adapters, current principal/Policy, DSR/Query/Relations/DVR/Settings/Profile/Membership sources, Component Blueprint rendering, output escaping, remote structured data, iframe trust, CAC cache/refresh concurrency, Job snapshots, ASR assets, accessibility/RTL/mobile, privacy/audit/error handling, Multisite lifecycle and large-dashboard/network scale.

## Preserved invariants

- Widget visibility/layout/dismissal is presentation, never authorization.
- Policy-denied data is not fetched just to be hidden later.
- Remote HTML/JS is not trusted admin-origin content.
- Provider/Vault secrets never enter browser/bootstrap/URL/log/support/cache surfaces.
- Site/Network/User Admin contexts remain distinct.
- Cache/refresh success does not upgrade source/provider certification.
- One widget failure cannot fatal the complete Dashboard.
- Pro expiry/module disable/deactivation preserve accepted lifecycle and recovery semantics.

## Independent certification classes

Future evidence reports `DW-A`, `DW-P`, `DW-S`, `DW-R`, `DW-X`, `DW-U`, `DW-M` and `DW-Q` independently. Passing one class does not certify the others.

## Evidence status

- DW fixtures documented: **176**
- DW fixtures executed: **0/176**
- Dashboard Widget runtime certifications: **0**
- Source/refresh/Multisite/performance certifications: **0**

No Dashboard hook/render, WordPress runtime, provider request, iframe, cache mutation, Job, browser test, asset enqueue or benchmark was executed.

## Consequence

`P0-M00-WP48` is planning-complete once source-of-truth registries and Draft PR are synchronized. Executable evidence or implementation remains blocked by ADR-0014 and the Approval Ledger.
