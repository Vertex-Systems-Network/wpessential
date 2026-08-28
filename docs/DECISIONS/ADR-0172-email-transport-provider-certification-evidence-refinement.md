# ADR-0172 — Email Transport / Provider Certification Executable Evidence Refinement

Status: **Accepted evidence protocol / execution pending**  
Date: **2026-08-28**  
Work package: **P0-M00-WP55**

## Context

ADR-0058 established the email-delivery truth boundary and ET0–ET5 certification ladder. ADR-0063 established provider source-truth profiles for WordPress `wp_mail()`, generic SMTP, Amazon SES, Twilio SendGrid, Mailgun and Postmark. ADR-0067 established provider/version-scoped certification identity. ADR-0139 separately refined Email Builder rendering/composition evidence as EBR-01…EBR-176.

The existing Email Transport certification protocol already defined strong ET0–ET5 semantics, but it predated the mature shared contracts for Vault, JobService, Event Inbox/Webhooks, Notification, local/remote privacy, Error Taxonomy, Contract Versioning, Rate Limit, Multisite and Site Lifecycle. It also lacked one canonical exhaustive fixture family comparable to the newer 176-fixture refinement protocols.

Static provider research currently covers six EE3 profiles. No runtime provider profile has been certified.

## Decision

WPEssential accepts the in-place refinement of `docs/QUALITY/EMAIL-TRANSPORT-CERTIFICATION-EVIDENCE-PROTOCOL.md` with the following rules.

### 1. ET0–ET5 remain the certification maturity ladder

The existing ET0–ET5 meanings are preserved. They are not renamed, renumbered or replaced by fixture IDs.

- ET0 — Configured / Connectable
- ET1 — Submission Certified
- ET2 — Resilient Submission Certified
- ET3 — Delivery Truth Certified
- ET4 — Feedback / Suppression / Reconciliation Certified
- ET5 — Production Email Profile Certified

A profile may claim only the highest level for which all required lower-level evidence is also satisfied for the same pinned profile.

### 2. ET-F001…ET-F176 become the canonical executable evidence matrix

The protocol now defines **ET-F001…ET-F176** across sixteen evidence groups covering:

- profile identity/version/provenance;
- Vault credentials/authentication/sender prerequisites;
- sender/recipient/header safety;
- renderer handoff/MIME/attachments;
- submission attempts/provider identifiers;
- DNS/TLS/SMTP/API failure normalization;
- unknown outcomes/idempotency/retries/JobService crash windows;
- fairness/throttling/batching/backpressure;
- provider-event authenticity and scope;
- event correlation/dedupe/order/late evidence;
- delivery/bounce/drop/complaint/suppression truth;
- unsubscribe/preferences/cross-channel policy;
- engagement/tracking truth;
- reconciliation/outage/retention/API drift;
- Multisite/lifecycle/restore/clone/Free↔Pro degradation;
- privacy retention/erasure/audit/diagnostics/scale/production behavior.

These fixtures are evidence inputs to ET-level certification. They are not themselves certification levels.

### 3. Static EE3 never becomes ET0

Static provider documentation/profile review remains distinct from executable runtime certification. The current six provider profiles remain:

- **6 EE3 static profiles**;
- **0 ET-certified runtime profiles**.

No provider gains ET0 or above from documentation, implementation intent, code presence or a provider marketing claim.

### 4. Delivery truth remains deliberately narrow

The following state boundaries are canonical:

`Rendered Message → Transport Attempt → submission accepted/unknown/rejected → provider queued/accepted → receiving-server accepted → deferred/bounced/dropped/complained/suppressed → engagement signal`

Therefore:

- renderer success does not prove submission;
- local transport success does not prove provider acceptance;
- provider acceptance does not prove mailbox/inbox delivery;
- `Delivered` requires the certified receiving-server-acceptance evidence defined by the exact provider profile;
- open/click does not mean read, human viewed or human intent;
- complaints, suppressions and unsubscribes preserve their own source facts and never rewrite historical delivery facts.

### 5. Certification is exact-profile scoped

Certification identity includes WPE/adapter/provider/API or event-schema version, auth/security mode, region/account scope where relevant, WordPress/PHP environment, JobService profile, Vault profile, Event Inbox/Webhook profile, Multisite scope and environment class.

A materially different profile requires separate evidence or an explicitly justified portability decision. Unknown/unsupported capabilities stay visible as unknown/unsupported.

### 6. Shared-contract boundaries are mandatory

Email Transport certification cannot bypass or redefine shared contracts:

- Vault owns provider secret material and rotation boundaries;
- JobService supplies at-least-once execution opportunities, not exactly-once email truth;
- Notification occurrence/recipient/channel state remains distinct from transport attempts;
- Webhook Gateway/Event Inbox owns authenticated provider-event ingress/source facts;
- PDL/remote-service privacy govern retention/export/erasure/transmission boundaries;
- ERR governs normalized failure truth;
- VER governs compatibility/retest impact;
- RLT/backpressure/resource policy cannot become authorization;
- Multisite/Site Lifecycle owns durable scope, clone/restore/site-delete behavior.

### 7. Stop-the-line negative requirements are certification gates

A profile cannot be certified if evidence shows secret disclosure, cross-site correlation, unauthenticated/tampered event acceptance where authenticity is required, uncontrolled duplicate sending, false inbox/read claims, unsafe restored/staging sends, hidden unsupported capabilities or any bypass of lower-level evidence.

## Current evidence truth

At acceptance time:

- ET-F fixtures documented: **176**;
- ET-F fixtures executed: **0/176**;
- static provider profiles: **6 EE3**;
- ET-certified runtime provider profiles: **0**;
- ET0/ET1/ET2/ET3/ET4/ET5 certified profiles: **0 each**;
- EBR rendering/composition remains separately **0/176**;
- no SMTP connection, DNS mutation, provider API call, email send, webhook runtime, queue execution, benchmark, build, migration or runtime test was performed by WP55.

## Consequences

### Positive

- Email delivery claims now have an exhaustive, reproducible evidence matrix.
- Existing ET0–ET5 semantics and historical provider-profile terminology remain backward compatible.
- Provider acceptance, receiving-server delivery, complaint/suppression and engagement cannot collapse into one misleading status.
- Job crashes, ambiguous acceptance, restore/clone and Multisite failure modes are first-class certification evidence.
- Future provider support can advertise only the capability subset actually proven for the exact profile.

### Costs / open evidence

- ET runtime certification remains entirely open until explicitly authorized execution occurs.
- Provider/version/profile combinations may require multiple independent certification reports.
- Some providers will legitimately remain below ET5 or expose unsupported capabilities.
- Exact operational limits and provider-specific quirks remain evidence-derived, not assumed from paper architecture.

## Consent and execution gate

This ADR accepts documentation/evidence semantics only. It grants **no development or executable-spike authorization**.

Under ADR-0014, `DEVELOPMENT-CONSENT.md` and the Approval Ledger, ET-F execution, provider credentials, SMTP/API sends, webhook setup, DNS changes, queues, tests, benchmarks or runtime mutation require explicit scoped owner consent.

Current owner authorization remains **0/31**.