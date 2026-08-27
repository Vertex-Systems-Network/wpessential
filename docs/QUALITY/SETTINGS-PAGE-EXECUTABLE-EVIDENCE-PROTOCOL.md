# WPEssential — Settings Page Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0036, ADR-0089, `docs/ARCHITECTURE/SETTINGS-PAGE-STORAGE-SCOPE-RUNTIME.md`, Vault, REST, Multisite, ADR-0014.

## 1. Purpose

Define evidence required before WPEssential can claim Settings Page Builder runtime support for site/network scoped value documents, inheritance, validation, secrets, external setting adapters, REST exposure, cache behavior and Multisite lifecycle.

The storage invariant is fixed:

**Settings Page Definition is not the runtime value document, secret plaintext never belongs in ordinary Settings storage, and site/network scope is resolved server-side rather than trusted from the browser.**

## 2. Runtime profile

Future certification records:
- WordPress/PHP/database versions;
- single-site/Multisite topology;
- ST1/ST2/ST3 storage profile used;
- Options/Network Options behavior relevant to version;
- Definition/Field/Vault/Policy versions;
- cache/object-cache profile;
- REST exposure profile;
- external setting adapters/versions;
- autoload policy and measured size budget.

## 3. Fixture matrix

### ST-01 — Site value document
One Settings Page stores/retrieves site-scoped typed values without colliding with another page/site.

### ST-02 — Network value document
Network-scoped values require network authority and remain distinct from site values.

### ST-03 — Network default + site override
Resolution is `site override → network default → definition default` with explicit provenance in UI/runtime.

### ST-04 — Inherited value is not materialized automatically
Reading an inherited default does not silently create a site override.

### ST-05 — Explicit empty/null semantics
Permitted explicit empty/null remains distinguishable from missing/inherited state.

### ST-06 — Reset to inherited/default
Reset removes explicit override rather than copying current default into storage.

### ST-07 — Label rename
Presentation label change preserves field value identity.

### ST-08 — Field key/identity migration
Published identity change requires mapping/migration; old values do not become orphaned or duplicated silently.

### ST-09 — Unknown field rejection
Forged/unmapped submitted fields are rejected/ignored according to explicit schema; no mass assignment.

### ST-10 — Typed validation
Invalid type/range/enum/format is rejected server-side independently from client controls.

### ST-11 — Sensitive/high-risk field Policy
Field-level Policy can deny a field even when page-level access is allowed.

### ST-12 — Browser CSRF boundary
Certified admin/frontend save path rejects missing/invalid CSRF protection where applicable.

### ST-13 — Concurrent stale edit
Two editors cannot silently overwrite newer document version when stale-conflict semantics are required.

### ST-14 — Partial validation failure
Invalid field cannot produce ambiguous half-saved document unless the product explicitly supports fieldwise transactional semantics.

### ST-15 — Conditional hidden preserve
Default hidden-field behavior preserves prior value and rejects tampered hidden submission that lacks permission.

### ST-16 — Conditional hidden clear
Opt-in clear-on-hidden performs explicit warned removal only under server-validated condition.

### ST-17 — Non-autoload default
Ordinary/admin-only page does not become globally autoloaded merely because it is a setting.

### ST-18 — Autoload opt-in
Tiny request-critical setting can be explicitly profiled/benchmarked; support claim records actual WordPress-version behavior.

### ST-19 — Oversized value document
Configured size budget warns/blocks unsuitable large datasets rather than turning Settings into runtime log/table storage.

### ST-20 — Media/file reference
Large media/file value is stored as typed reference, not giant inline bytes.

### ST-21 — Secret write
Secret field stores only Vault reference in Settings document; plaintext does not remain in option/value document.

### ST-22 — Secret read/UI
Existing secret plaintext is never returned to ordinary edit input/bootstrap; UI exposes configured/replace/revoke state.

### ST-23 — Secret REST/export exclusion
REST, export, logs and history contain no secret plaintext.

### ST-24 — Vault unavailable
Non-secret settings remain usable while secret-dependent field/feature degrades safely.

### ST-25 — External setting read-only baseline
Unknown/uncertified external setting adapter is inspect/read-only by default.

### ST-26 — Certified external setting write
Write adapter proves scope/schema/sanitization/capability/version behavior before mutation support is claimed.

### ST-27 — Registered WordPress setting compatibility
Certified mapping uses registered metadata without assuming metadata `type` alone validates arbitrary form submissions.

### ST-28 — REST off by default
Publishing a Settings Page does not expose a REST write/read surface unless explicitly configured.

### ST-29 — REST read projection
Only allowlisted fields are returned, with server-side site/network scope and field Policy.

### ST-30 — REST write projection
Only declared writable fields mutate; secrets/internal fields stay excluded and unknown fields cannot mass-assign.

### ST-31 — Network REST authorization
Site administrator cannot select/request network scope and mutate network document without network authority.

### ST-32 — Frontend consumer binding
Component/List/Form/Workflow reads through typed Settings service and correct scope, not arbitrary raw option names.

### ST-33 — Site cache isolation
Resolved value cache identity prevents same page/key on two sites from crossing.

### ST-34 — Inheritance cache invalidation
Network default update invalidates/versions affected inheriting site resolutions while preserving explicit overrides.

### ST-35 — Secret cache hygiene
Secret plaintext is not copied into generic persistent cache/log/debug payload.

### ST-36 — Value audit/history
Configured change history records safe diffs/metadata separately from Definition revisions.

### ST-37 — High-risk recent-auth
Credential/security/destructive/billing mapping changes can require recent auth without blocking unrelated low-risk save unnecessarily.

### ST-38 — High-risk impact/confirmation
Configured high-risk field produces impact/confirmation/audit behavior before change.

### ST-39 — Definition export/import
Definition package carries field/storage/scope semantics without runtime secrets.

### ST-40 — Value export/import
Values are separately opt-in, scoped and typed; inherited values remain semantic when configured rather than blindly materialized.

### ST-41 — Environment/site-specific remap
URLs/IDs/connection references are classified and conflict/remap is explicit during import.

### ST-42 — Scope conflict on import
Network value cannot silently land as site value or vice versa.

### ST-43 — Corrupted value document
Corrupt payload fails safe with diagnostics; no arbitrary coercion/destructive rewrite.

### ST-44 — Legacy invalid value
Invalid legacy value yields typed degraded/fallback state and migration path rather than silent mutation.

### ST-45 — Site deletion lifecycle
Site-scoped Settings values follow explicit site lifecycle retention/removal policy; network values/secrets remain unaffected.

### ST-46 — Subsite export/privacy
One site export cannot include unrelated network secrets or another site’s values.

### ST-47 — Pro expiry
Safe deployed runtime values remain readable according to ADR-0007 while editing/advanced actions can restrict without data loss.

### ST-48 — Performance/scale profile
Reference pages/fields/sites meet bounded read/write/query/autoload/cache budgets without turning one save into unbounded network-wide work.

## 4. Pass gates

Certification fails if:
- site admin can mutate network value;
- secret plaintext appears in Settings option/document, REST, export, history or generic cache;
- unknown fields mass-assign values;
- inherited and explicit values become indistinguishable;
- autoload claims are made without version/profile evidence;
- stale edit silently overwrites newer high-risk value contrary to declared semantics;
- one site receives another site/network value due to cache/storage collision;
- external arbitrary `option_name` editing is exposed as safe generic behavior.

## 5. Required future evidence report

Include:
- runtime/storage profile;
- ST-01…ST-48 pass/fail;
- option/autoload sizes and query counts;
- stale-write/concurrency evidence;
- Vault secret scans;
- REST projection tests;
- import/export scope tests;
- Multisite lifecycle/isolation evidence;
- unsupported external adapters.

## 6. Current state

**ST fixtures executed: 0/48.**

No option/network-option write, Vault secret operation, REST route, cache mutation, import/export or WordPress runtime test has occurred.

## 7. Development gate

Execution requires explicit owner consent under ADR-0014.