# ADR-0114 — Role & Capability Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Decision

Role & Capability Manager cannot claim safe mutation, anti-lockout, recovery, Multisite or cache-revocation support until a future implementation passes `docs/QUALITY/ROLE-CAPABILITY-EXECUTABLE-EVIDENCE-PROTOCOL.md` for its certified WordPress authority profile.

The protocol enforces:
- RA1 native WordPress authorization authority and RA2 third-party role compatibility;
- reviewed Change Plan + source fingerprint before high-risk mutation;
- effective-capability simulation rather than role-name privilege guesses;
- recovery-principal invariant, self-lockout prevention and recent-auth controls;
- core/third-party role preservation;
- additive/remove/replace role and user-cap override semantics;
- native mutation re-read/verification and reconciliation after ambiguous metadata failures;
- bounded pre-change snapshots and reverse-diff conflict handling;
- RR1/RR2/RR3 recovery without anonymous/public authorization bypass;
- explicit Site vs Network/Super Admin boundaries;
- capability-dependent Profile/REST/Dashboard/Listings cache-generation invalidation;
- redacted Audit and large-network/bulk evidence.

## Current state

RA-01…RA-48 documented. **0/48 executed.**

## Development gate

No role, capability, user-role, Super Admin, cache-generation or recovery mutation is authorized before explicit owner consent under ADR-0014.