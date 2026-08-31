# ADR-0203 — F09 Documents, Records & Templates Executable Evidence Protocol

Status: **Accepted**  
Date: **2026-08-29**

## Context

ADR-0180 reserved `DOC-001…DOC-176` as the fixed executable-evidence envelope for F09 — Documents, Records & Templates. WP71 required the group-level envelope to be expanded into exact fixture definitions before future implementation can claim runtime readiness.

Documents/records are high-risk because rendered output can be mistaken for legal, financial, identity or authorization truth; generated files can leak protected source data; renderer/asset inputs can introduce active-content/SSRF/resource-exhaustion risks; immutable records require correction without silent rewriting; and external signing/storage/timestamp providers cannot be treated as locally reversible facts.

## Decision

Accept `docs/QUALITY/DOCUMENTS-RECORDS-TEMPLATES-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical detailed executable-evidence contract for F09.

The fixed namespace remains **DOC-001…DOC-176**, organized as 16 groups × 11 fixtures:

1. document/template/version/schema;
2. renderer primitives/layout/pagination;
3. fonts/assets/images/SVG sanitization/licensing references;
4. dynamic values/Policy/redaction;
5. HTML/PDF/text/structured output accuracy;
6. private/protected file storage/delivery;
7. immutable record/version/amend/supersede semantics;
8. generation Job/idempotency/crash/partial output;
9. signing/hash/checksum/time provenance without false legal signature claims;
10. retention/export/erase/legal-hold metadata;
11. download/audit/share/access expiry;
12. malicious template/content/SSRF/resource limits;
13. Multisite/tenant/site lifecycle;
14. backup/restore/migration portability;
15. large/batch rendering memory/time benchmarks;
16. invoice/certificate/contract/report golden visual/data regression.

## Non-negotiable boundaries

- Generated document output is not automatically source business truth, payment/order/ledger truth, identity proof, authorization or a legally binding record.
- Hash/checksum alone is not an electronic signature; local application time is not a trusted timestamp authority token.
- Source data remains canonical at its owning domain and is Policy-projected/redacted at generation and protected delivery boundaries.
- Template-author permission does not imply permission to render all possible bound values.
- Immutable issued records are corrected through explicit amendment/supersession rather than silent replacement.
- Protected artifact delivery cannot be bypassed through a predictable/public-looking URL, CDN cache or storage path.
- HTML/SVG/fonts/images/remote assets are untrusted; arbitrary executable template code and unrestricted network/file access are prohibited.
- External signing/storage/timestamp outcomes can be unknown and require reconciliation before replay where duplicate side effects are possible.
- Backup/restore/clone cannot roll back external authorities and staging/clone cannot silently emit production provider actions.
- Multisite/tenant/site identity and storage/sequence namespaces remain server-resolved and isolated.
- AI/MCP-generated templates/content/issuance plans have no privileged bypass of Policy, sanitizer, approval, provenance or idempotency rules.

## Evidence truth

- `DOC-001…DOC-176` documented: **176/176**.
- executed: **0/176**.
- F09 runtime certification: **0**.
- product implementation authorization: **0/56 / NOT GRANTED**.

Documentation of fixtures is not execution evidence. No renderer, file write, protected-delivery flow, provider call, signing/timestamp action, AI/MCP session, test or benchmark occurred as part of this ADR.

## Consequences

WP71 — F09 Documents, Records & Templates detailed evidence is complete as a planning/evidence package.

The next safe planning work becomes **WP72 — F10 Data Sync & ETL detailed executable-evidence specification (`SYN-001…SYN-176`)**.

Production development remains blocked by ADR-0014 and the explicit development-consent gate.