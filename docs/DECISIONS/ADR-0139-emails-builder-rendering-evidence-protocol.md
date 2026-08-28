# ADR-0139 — Emails Builder Rendering & Composition Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP22`

## Decision

Accept `docs/QUALITY/EMAILS-BUILDER-RENDERING-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical future executable-evidence contract for Emails Builder template compilation, rendering/composition and normalized Rendered Message handoff.

The protocol freezes **EBR-01…EBR-176**.

It explicitly does **not** replace or duplicate `EMAIL-TRANSPORT-CERTIFICATION-EVIDENCE-PROTOCOL.md`; ET0–ET5 transport/provider truth remains separately certified.

## Accepted truth boundary

The following remain separate:

`Email Definition ≠ Published Template Revision ≠ Layout Revision ≠ Compiled Email Descriptor ≠ authorized render context ≠ Email IR ≠ HTML output ≠ plaintext output ≠ recipient/sender envelope ≠ immutable Rendered Message snapshot ≠ Transport Attempt ≠ provider/delivery truth ≠ certified runtime behavior`

A correct preview or render does not certify transport acceptance, receiving-server delivery, inbox placement or human read/open.

## Fixed evidence coverage

- Definition/revision/dependencies — EBR-01…EBR-16;
- typed context/tokens/conditions/Policy — EBR-17…EBR-32;
- Email IR/HTML blocks/escaping/sanitizer — EBR-33…EBR-48;
- CSS/style/responsive/accessibility — EBR-49…EBR-64;
- plaintext/subject/preheader/locale/formatting — EBR-65…EBR-80;
- sender/recipient/reply-to/header composition — EBR-81…EBR-96;
- links/unsubscribe/preferences/images/remote assets — EBR-97…EBR-112;
- attachments/private-file exposure — EBR-113…EBR-128;
- preview/test/production snapshot/handoff — EBR-129…EBR-144;
- personalization/fan-out/cache/Notification-Workflow integration — EBR-145…EBR-160;
- Multisite/import-export/compatibility/failure/scale — EBR-161…EBR-176.

## Certification classes

Certify independently:

- `EBR-D` Definition/revision/dependencies;
- `EBR-C` compiled descriptor/context/token authorization;
- `EBR-H` Email IR/HTML rendering;
- `EBR-T` plaintext/subject/preheader/localization;
- `EBR-E` envelope/header/sender/recipient composition;
- `EBR-A` links/assets/images/attachments;
- `EBR-P` privacy/personalization/fan-out/cache safety;
- `EBR-I` preview/test/Notification/Workflow/transport handoff;
- `EBR-M` Multisite/import/export/clone/restore scope;
- `EBR-O` compatibility/failure/performance/observability.

## Accepted invariants

1. Draft templates/layouts are never production render inputs.
2. production rendering is deterministic for exact revisions + authorized context + renderer profile.
3. tokens are typed, privacy-classified, Policy-aware and destination-escaped.
4. secrets/credentials/protected internals are not generic renderable tokens.
5. browser/page-builder markup is not canonical email markup.
6. HTML and plaintext are separate outputs from the same authorized composition model.
7. To/CC/BCC/From/Reply-To are validated envelope data, not arbitrary text/header injection surfaces.
8. private assets/attachments require recipient-specific authorized delivery semantics.
9. preview, test send and production render are separate modes; test cannot mutate business workflow state.
10. normalized immutable Rendered Message is the renderer/transport boundary.
11. deterministic retries reuse the frozen Rendered Message unless an explicit versioned re-render policy creates a new render generation.
12. renderer never promotes its own success into ET submission/delivery/inbox/read truth.
13. network/shared templates never imply shared sender credentials or recipient datasets.
14. personalized/protected rendered output never shares a public cache key.

## Relationship to ET certification

EBR ends at normalized Rendered Message handoff.

ET remains independently responsible for:
- ET0 connect/configure;
- ET1 submission;
- ET2 resilient submission;
- ET3 delivery truth;
- ET4 feedback/suppression/reconciliation;
- ET5 production provider profile.

No EBR pass may promote ET state and no ET pass substitutes for EBR render/token/privacy evidence.

## Current evidence state

- EBR documented: **176**.
- EBR executed: **0/176**.
- all `EBR-*` certification classes: **0**.
- exact renderer/CSS inliner dependency: **OPEN**.
- exact email-client compatibility matrix: **OPEN**.
- exact size/attachment/render budgets: **OPEN**.
- WordPress core email override adapter certifications: **0**.
- third-party email override adapter certifications: **0**.
- existing provider/transport state remains **6 EE3 / 0 ET-certified**.

## Rejected shortcuts

- Draft render in production;
- arbitrary browser/page-builder markup as canonical email;
- arbitrary PHP/JS/template language/callback execution;
- secret/protected token exposure;
- header injection or unvalidated dynamic URL placement;
- hidden template recipients by default;
- private attachment exposure based only on template-editor access;
- unbounded Query/repeater/fan-out rendering;
- personalized output under public shared cache;
- test-send business side effects;
- silent queued-message re-render on retry;
- renderer-created delivery/read claims;
- global `wp_mail()` interception as substitute for semantic override adapters.

## Development gate

No renderer/compiler, email-client test, private-file resolution, WordPress override, Notification handoff execution, test send, SMTP/provider call or benchmark is authorized by this ADR.

ADR-0014 and the Approval Ledger still require explicit scoped owner consent before executable evidence or implementation.

Current execution count remains **0/176**.