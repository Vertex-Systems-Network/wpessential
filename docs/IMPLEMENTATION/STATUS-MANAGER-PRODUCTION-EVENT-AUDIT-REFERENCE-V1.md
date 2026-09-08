# Status Manager Production Event/Audit Reference V1

Issue: #376

This closure slice extends the existing real-WordPress Status reference application to prove the promoted transition Event/Audit composition against production bootstrap services and persistence.

The reference now certifies that:

- `platform.events` resolves to the canonical `EventBus` and `platform.audit` resolves to the production `PersistentAuditLogger` when the real WordPress MySQL/MariaDB persistence path is active;
- a real `draft -> review-ready` post transition emits exactly one `status.transition.completed` DomainEvent only after the new WordPress status is observable;
- Event evidence contains bounded server-derived transition and execution-context facts, preserves the correlation id and does not expose the authored transition reason;
- the same verified transition appends exactly one Surface 5 `status.transition` success record to the production Audit table;
- the Audit record preserves canonical actor/site/network/channel/correlation/resource context and the normalized bounded reason, while sanitized metadata contains only transition facts plus `reason_provided` and does not duplicate the reason;
- stale, undeclared, Core trash lifecycle, post-type applicability, object-authorization and site-scope denial paths create no additional Status transition Event or Audit evidence.

This reference does not introduce a second event bus, audit logger, workflow history, notification path or Status persistence owner. It changes no shared Platform implementation and makes no release, product-parity or runtime-certification claim.
