# ADR-0193 — Safe Script, Tag & Code Injection Manager

Status: **Accepted planning architecture / evidence pending / no development authorization**  
Date: 2026-08-29

## Context

The owner requested an audit of Insert Headers and Footers / WPCode-style custom-code functionality and asked that WPE custom code become advanced.

Market demand is clear for header/body/footer tags, JavaScript, CSS, HTML, JSON-LD, verification tags, widgets, conditional placement and versioning. However, browser-managed arbitrary PHP/eval materially expands the privilege and remote-code-execution attack surface and conflicts with ADR-0004.

Existing Asset Registry owns application assets/dependencies, not user-configured third-party tags, consent-aware placements, CSP policy, environment scoping and revisioned browser-code snippets. A dedicated safe user-facing surface is therefore justified.

## Decision

Accept new **Surface 50 — Safe Script, Tag & Code Injection Manager**.

Canonical specification:
`docs/MODULES/SAFE-SCRIPT-TAG-CODE-INJECTION-EXHAUSTIVE-SPEC.md`

Supported declarative/browser types include:
- external JavaScript;
- inline JavaScript under privileged policy;
- CSS;
- HTML/text;
- typed meta/link tags;
- JSON-LD;
- safe iframe/widget profiles;
- registered frontend/admin/login placements under separate risk classes.

The surface includes:
- typed placements;
- dependency/order graph;
- shared Conditional Logic;
- external-origin/SRI/crossorigin/referrerpolicy controls;
- consent categories and blocking;
- CSP nonce/hash/security integration;
- environment profiles;
- preview/validation/conflict diagnostics;
- revisions/rollback/emergency pause;
- import/export;
- migration from simple header/footer tools;
- AI/MCP draft/explain/validate with publish disabled by default.

## Hard boundary

**No PHP. No `eval()`. No arbitrary SQL/shell/server-code runtime.**

If a requirement genuinely needs server-side code, WPE routes it to a typed Extension SDK/plugin plan and the normal development/VCS/CI/review process after explicit development authorization.

## Evidence

Reserve **STM-001…STM-176**, executed **0/176**.

## Development gate

No script/tag output, JavaScript/CSS/HTML injection, consent/CSP mutation, external request, admin/login injection, import, emergency pause, PHP execution or runtime test is authorized by this ADR.