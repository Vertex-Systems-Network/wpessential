# ADR-0138 — Free CPT & Taxonomy Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP21`

## Decision

Accept `docs/QUALITY/FREE-CPT-TAXONOMY-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical future executable-evidence contract for the Free Custom Post Types Builder and Taxonomy Builder runtime-registration surfaces.

The protocol freezes **CPTX-01…CPTX-176**.

## Accepted truth boundary

The following remain separate:

`Draft Definition ≠ Published Revision ≠ validated registration descriptor ≠ WordPress registered object ≠ rewrite/query state ≠ REST/editor state ≠ persisted posts/terms ≠ migration state ≠ certified runtime behavior`

Also:

`WPE ownership ≠ runtime key collision ≠ external registration discovery ≠ safe takeover/import-to-ownership`.

A successful native registration call alone does not certify rewrite/permalink behavior, REST/editor compatibility, capabilities, data preservation, Multisite behavior or coexistence.

## Fixed evidence coverage

- identity/validation/publish boundary — CPTX-01…CPTX-16;
- CPT visibility/admin registration — CPTX-17…CPTX-32;
- CPT supports/capabilities/taxonomy associations — CPTX-33…CPTX-48;
- CPT REST/archive/rewrite/query/lifecycle — CPTX-49…CPTX-64;
- Taxonomy registration/UI/object types — CPTX-65…CPTX-80;
- Taxonomy REST/editing/capabilities/rewrite/advanced semantics — CPTX-81…CPTX-96;
- collision/discovery/external ownership/coexistence — CPTX-97…CPTX-112;
- revisions/high-risk migration/data preservation — CPTX-113…CPTX-128;
- activation/update/rewrite-flush/rollback lifecycle — CPTX-129…CPTX-144;
- Multisite/import/export/clone/restore — CPTX-145…CPTX-160;
- compatibility/security/diagnostics/scale — CPTX-161…CPTX-176.

## Certification classes

Certify independently:

- `CPTX-CPT` CPT registration/admin/editor;
- `CPTX-TAX` Taxonomy registration/term UI;
- `CPTX-RW` rewrite/archive/query-var/permalink lifecycle;
- `CPTX-REST` REST/block-editor/controller compatibility;
- `CPTX-CAP` capabilities/meta-cap/anti-lockout;
- `CPTX-OWN` collision/discovery/coexistence/ownership;
- `CPTX-LC` Definition/activation/update/disable/delete/rollback lifecycle;
- `CPTX-MIG` key/high-risk migration;
- `CPTX-MS` Multisite rollout/scope/site lifecycle;
- `CPTX-COMP` compatibility/diagnostics.

## Accepted invariants

1. Draft definitions do not alter WordPress registration.
2. WPE owns only WPE-created/imported definitions; external/core objects are read-only absent a separately certified takeover path.
3. published CPT/taxonomy keys are migration-class identities, not ordinary rename fields.
4. rewrite changes use a dirty marker + controlled safe flush; never an every-request flush.
5. Definition disable/delete preserves posts, terms, relationships and metadata by default.
6. capability changes require impact preview and anti-lockout protection.
7. controller/callback extension points accept registered adapters only; arbitrary executable input is rejected.
8. CPT↔taxonomy attachment is represented consistently on both WordPress registration surfaces.
9. Multisite registration is target-site behavior; network templates do not share site posts/terms by implication.
10. runtime health reflects effective WordPress registration, not merely stored Definition state.
11. unsupported/version-dependent args degrade according to compatibility evidence.
12. known collision checks are required, but WPE must not claim exhaustive rewrite collision detection where WordPress/plugin/theme interactions cannot prove it.

## Current evidence state

- CPTX documented: **176**.
- CPTX executed: **0/176**.
- all `CPTX-*` certification classes: **0**.
- accepted WordPress compatibility floor: not runtime certified.
- exact reserved-name/query-var registry update strategy: **OPEN**.
- exact rewrite collision-detection completeness: **OPEN**.
- external import-to-ownership/takeover runtime certification: **0 / unsupported by default**.
- key migrations: **not implemented / not executed**.

## Rejected shortcuts

- registering Draft definitions;
- every-request rewrite flush;
- Definition delete/disable deleting content or terms;
- post-type/taxonomy key change as ordinary rename;
- generic takeover by hook-priority race;
- arbitrary PHP/callback/class/function configuration;
- client/admin visibility used as authorization;
- import key collision treated as identity;
- network template treated as shared site data;
- health reporting stored intent while effective runtime registration disagrees.

## Development gate

No PHP registration hook, rewrite flush, REST route/controller, capability mutation, key migration, content/term mutation, Multisite rollout, browser test or benchmark is authorized by this ADR.

ADR-0014 and the Approval Ledger still require explicit scoped owner consent before executable evidence or implementation.

Current execution count remains **0/176**.