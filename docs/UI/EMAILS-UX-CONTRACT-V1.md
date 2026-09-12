# Emails — UX Contract V1

Surface: **20 / Emails**  
Machine source: `config/product/option-contracts/emails.json`  
Lifecycle: **UX certification candidate**. Machine truth is `OPTION_CONTRACT_COMPLETE`; preview/configuration does not authorize external email dispatch.

## Lifecycle preconditions

- Machine status must remain `OPTION_CONTRACT_COMPLETE` or later with `missing=0` and `unclassified=0`.
- All 16 current Atomic Option IDs are mapped exactly once below.
- Template validation, sender/provider state, attachment authorization and token resolution remain server-authoritative.

## Canonical route and information architecture

Canonical admin location: **WPEssential → Communication → Emails**.

The IA separates **Template Identity**, **Metadata & Layout**, **Branding**, **Email-Safe Blocks**, **Dynamic & Localization**, **Overrides**, **Sender & Attachments**, **Preview & Diagnostics**, **Portability & Performance**, and **Safety**. Definition/editing is distinct from provider transport and non-dispatching preview.

## UX state classes

### Authored definition
Template identity, metadata/layout, branding, safe blocks, typed tokens, locale/plaintext and portability/cache policy are revisioned Emails-owned definitions.

### Effective/runtime state
Rendered preview, resolved tokens, locale fallback, plaintext result and compile/preflight diagnostics are effective/read-only context. Preview is not dispatch.

### Diagnostic/provider state
WordPress event adapters, certified third-party adapters, sender profiles and attachment references are provider/canonical-owner references with explicit health/certification state.

### Deferred / prohibited state
Raw PHP, script and untrusted executable HTML are prohibited. Sender credentials remain Vault/provider-owned and are never exposed or exported.

## Atomic Option → UX map

- `emails.template.identity` — Template Identity → template category/key/lifecycle/revision definition.
- `emails.metadata.message` — Metadata & Layout → subject/preheader/locale/HTML-plaintext mode editor.
- `emails.layout.shell` — Metadata & Layout → reusable email-safe shell/header/footer composition.
- `emails.brand.tokens` — Branding → logo/color/typography/contact reference policy.
- `emails.block.structure` — Email-Safe Blocks → section/columns/text/heading structure.
- `emails.block.actions-media` — Email-Safe Blocks → button/image/divider/spacer controls with safe URL/media validation.
- `emails.block.dynamic` — Email-Safe Blocks → bounded repeater/legal/dynamic/conditional blocks using registered providers/tokens only.
- `emails.token.schema` — Dynamic & Localization → typed allowlisted token schema and deterministic null fallback.
- `emails.locale.plaintext` — Dynamic & Localization → locale fallback plus deterministic plaintext generation/override.
- `emails.override.wordpress` — Overrides → reviewed WordPress email-event adapter reference.
- `emails.override.third-party` — Overrides → separately certified third-party compatibility-provider mapping and health state.
- `emails.sender.profile` — Sender & Attachments → sender metadata/profile reference; credentials/verification remain provider/Vault-owned.
- `emails.attachment.policy` — Sender & Attachments → Media/File-owned authorized attachment references plus count/size/MIME policy.
- `emails.preview.diagnostics` — Preview & Diagnostics → non-dispatching preview/test/preflight diagnostics.
- `emails.portability.performance` — Portability & Performance → secret-free import/export, revision conflict and bounded compile-cache policy.
- `emails.safety.raw-executable` — Safety → prohibited raw PHP/script/untrusted executable markup evidence; never an authored control.

## Interaction and persistence

Template editing uses draft → validate → preview → save revision. Preview shows exact locale/token fallbacks and validation diagnostics but does not send email. Adapter/sender changes require provider validation before save. Attachment selections are authorization-checked references; inaccessible items remain blocked.

## Loading, empty, validation, conflict and recovery

Required states include no templates, preview loading, missing token/provider, invalid safe URL, unsupported block, adapter unavailable/uncertified, sender unverified/unknown, attachment denied/too large/unsupported MIME, locale fallback, stale revision, saved and recovery. Test-send is not inferred from preview and requires a separate authorized runtime gate.

## Security and ownership

Vault/provider owns sender credentials and verification. Media/File owners authorize protected attachments. Event/third-party adapters own integration execution. Emails owns template/rendering definitions only. Raw PHP/script/untrusted executable markup is prohibited. Preview/preflight is non-dispatching, and UI state never fabricates provider verification or successful delivery.

## Accessibility

Template editor controls require labels, keyboard operation and visible focus. Block reordering has keyboard alternatives. Preview exposes text/plaintext alternatives. Validation summaries link to offending controls. Provider/attachment status is textual and not color-only.

## Multisite and scope

Template/sender/adapter scope is explicit. Network/global context is server-derived. Imports cannot silently bind a site template to a network sender or unrelated third-party adapter.

## Portability and reference remapping

Exports are definition-only and secret-free. Imports validate sender, adapter, Media/File and token-provider references, report unresolved mappings and never include credentials. Preview/runtime observations are not imported as authored state.

## Performance and scale

Large template libraries paginate/filter. Dynamic preview is bounded and avoids N+1 token/provider resolution. Compile caches are revision-addressed and invalidated deterministically. Admin assets load only on relevant Email screens.

## Degraded/provider states

Missing/uncertified adapter, sender provider, token source or attachment owner produces explicit unavailable/degraded state. The surface never fabricates sender verification, attachment authorization, dispatch, provider acceptance, delivery or receipt.

## UX lifecycle exit criteria

Certification requires complete 16-ID mapping, zero missing/unclassified machine semantics, raw-executable rejection, non-dispatching preview truth, provider/attachment ownership review, accessibility/portability/performance/degraded states and exact-head validator CI. Shared lifecycle promotion remains Supervisor-only.

## Non-certifications

Runtime implementation is not certified by this document. Product parity is not certified. Production deployment or release is not verified. No external email dispatch, sender credential operation, provider execution or protected-file mutation is authorized by this UX contract.
