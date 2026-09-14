# WPEssential — P-006 Wave 1 Execution Boundary

Status: **AUTHORIZED / BOUNDED EXECUTION PREPARATION**  
Parent execution issue: **#939**  
Owner authorization evidence: **Issue #924 comment `5671331999`**  
Fresh-main authorization anchor: **`0424202c43a16585425b839f8234b9ae8f8b0e2b`**

## Authorized initial fixture subset

Only the following P-006 fixtures are initially executable under Wave 1:

- FP-01 — Free artifact metadata consistency;
- FP-02 — Pro compatibility metadata consistency;
- FP-05 — Free artifact contains no prohibited Pro implementation/assets;
- FP-07 — represented PHP/WordPress minimum requirements agree;
- FP-08 — deterministic SHA-256 identities pin the exact Free/Pro candidate pair.

All other P-006 fixtures remain `NOT EXECUTED` until a separate dependency-ready tranche authorizes them.

## Mandatory evidence sequence

1. record scoped owner consent in `docs/APPROVAL-LEDGER.md`;
2. activate deterministic Wave-1 queue slots from fresh main;
3. build immutable Free/Pro candidates with the repository deterministic builder;
4. record exact source SHA and artifact SHA-256 values before any FP result is promoted;
5. execute only the authorized fixture subset against those exact artifacts;
6. record expected/actual evidence and terminal result for every executed fixture;
7. stop immediately on any P-006 stop-the-line failure or artifact identity drift.

## Excluded authority

Wave 1 does not authorize production deploy/release, production credentials/data, live provider/billing/payment/license/allocation side effects, irreversible/destructive operations, runtime-source fixes without a separate defect issue, or promotion of adjacent evidence-domain certifications.

## Certification boundary

Passing this subset cannot certify P-006 or certify a Free/Pro pair. P-006 remains uncertified until all applicable mandatory fixtures and cross-gates pass.
