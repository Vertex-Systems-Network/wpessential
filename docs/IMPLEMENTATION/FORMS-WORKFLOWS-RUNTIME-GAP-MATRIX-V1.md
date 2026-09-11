# Forms & Workflows — Runtime Gap Matrix V1

Surface: **17 / Forms & Workflows**  
Issue: **#500**  
Runtime authorization: **false**.

| Family | Planning state | Runtime gap | Evidence required before runtime promotion |
|---|---|---|---|
| Form identity | Seeded | No canonical Form Definition/revision lifecycle | unit + integration + portability |
| Fields | Seeded | No canonical Fields/Control Registry composition adapter | compatibility + browser |
| Calculations | Seeded | No bounded declarative server-side calculation evaluator | unit + security + performance |
| Submission policy | Seeded | No authorization/CSRF/abuse/idempotency submission service | security + integration + REST |
| Entry storage | Seeded | No privacy/retention/access persistence contract | security + multisite + migration |
| Action mapping | Seeded | No allowlisted Ability/provider action registry | security + compatibility |
| Run reliability | Seeded | No idempotent retry/checkpoint/replay state machine | unit + integration + recovery |
| Secret references | Seeded | No Vault-reference adapter/redacted diagnostics | security + portability |

## Cross-cutting requirements

**Security:** server-authoritative authorization, CSRF protection, validation/sanitization, rate limits and idempotency; reject arbitrary PHP/JS/code expressions and raw callbacks/classes.  
**External side effects:** no live email/SMS/payment/webhook/provider mutation without a later explicitly authorized execution lane.  
**Multisite/privacy:** explicit site/network scope, retention/deletion ownership and no cross-site entry disclosure.  
**Accessibility:** semantic form controls, error summaries, keyboard access, visible focus and announced workflow states.  
**Compatibility:** action/field providers are allowlisted and optional; provider loss fails safely.  
**Performance:** bounded calculations, paginated entries/runs and no N+1 provider resolution.  
**Portability:** versioned definitions with secrets excluded and environment/provider references explicitly remapped.

Workflow execution, destructive entry mutation, deployment/release and certification claims remain forbidden in this lane.