# Forms & Workflows — UX Contract V1

Surface: **17 / Forms & Workflows**  
Issue: **#500**  
Lifecycle: planning-only; no runtime promotion.

## Essential

- **Form identity** — stable form key, lifecycle/revision state and ownership.
- **Field configuration** — compose canonical registered Fields/Controls with requiredness, defaults and validation feedback.

## Advanced

- **Calculations** — bounded declarative calculations evaluated server-side; effective result and validation errors are visible.
- **Submission policy** — authentication/authorization, duplicate/idempotency and abuse-protection policy with explicit effective state.
- **Entry storage** — retention, privacy, deletion and access policy with clear site/network scope.

## Expert

- **Action mapping** — map workflow steps only to registered Abilities/providers and canonical target-owner references.
- **Run reliability** — idempotency key, retry/backoff, checkpoint/replay and terminal-failure policy with read-only run diagnostics.

## System / safety

- **Secret references** — Vault-backed references only; secret values are never displayed, logged or exported.

## Interaction contract

- Search/focus reaches every authored setting and validation error.
- Form preview distinguishes client hints from server-authoritative validation/calculation.
- Workflow actions expose provider availability, side-effect class and failure/retry implications before save.
- Entry and run states include empty, forbidden, failed, retrying and terminal outcomes.
- Keyboard operation, semantic labels/groups, error summaries, visible focus and announced async states are required.

## Security / multisite / compatibility / performance

- Server-side authorization, CSRF protection, validation/sanitization, rate limits and idempotency are authoritative.
- No arbitrary PHP/JS/code-expression execution or raw provider callback/class input.
- Site/network data scope is explicit; entry privacy must prevent cross-site leakage.
- Missing action providers fail safely and never trigger fallback side effects.
- Large entry/run collections are paginated and bounded; workflow evaluation avoids N+1 provider resolution.
- Optional assets load only on Forms & Workflows surfaces.

This contract does not authorize workflow execution, live external side effects, runtime implementation, deployment or certification.