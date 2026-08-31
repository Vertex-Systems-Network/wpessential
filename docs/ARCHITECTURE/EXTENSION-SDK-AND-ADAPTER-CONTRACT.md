# WPEssential — Extension SDK & Adapter Contract

Status: Phase 0 planning. No runtime implementation authorized.

## Goal
Allow third parties and WPEssential add-ons to extend platform capabilities without modifying core or bypassing shared security/data contracts.

## Extension categories
- Data Source adapters
- Field types
- Query providers/operators
- Relation providers
- Renderers/components
- Builder adapters
- Workflow triggers/actions
- Notification channels
- Email transport/provider adapters
- Backup storage providers
- Billing/membership source adapters
- Authentication/REST adapters
- Import source/export destination adapters
- Protected-file delivery adapters
- Diagnostics/health checks

## Registration principle
Extensions register typed descriptors through public registries. They do not mutate private global arrays or load order assumptions.

A descriptor includes where applicable:
- stable extension ID namespace;
- display name/vendor/version;
- minimum WPE Platform API range;
- capabilities/features supported;
- configuration schema;
- runtime service/callback factory;
- health check;
- sensitive fields annotations;
- dependency requirements;
- asset entry points;
- import/export behavior;
- privacy classification;
- deprecation metadata.

## Stable namespaces
Third parties use vendor-prefixed IDs, e.g.:
- `acme/crm-data-source`
- `vendor/sms-channel`

Reserved `wpessential/*` namespace belongs to official platform/modules.

## No arbitrary code from UI
The SDK is a developer-code extension mechanism distributed as installed PHP/JS packages/plugins, not a UI textbox that executes arbitrary PHP/JS.

## Data Source adapter contract
Must declare:
- entity types;
- field schema discovery;
- read/list/query capabilities;
- create/update/delete support flags;
- pagination/cursor model;
- filter/sort operators;
- transaction/idempotency characteristics;
- authorization mapping;
- rate/complexity constraints;
- caching/invalidation hints;
- error normalization.

A provider that cannot update safely must advertise read-only rather than emulate writes.

## Field type contract
- schema/data type;
- editor/view renderer;
- sanitize/validate/normalize;
- serialization/storage constraints;
- conditional-logic operators;
- query compatibility;
- REST/Ability schema;
- accessibility metadata;
- asset loading.

## Builder adapter contract
Adapters translate WPE Component Blueprint/listing/template definitions into supported public builder APIs.

Must declare a capability matrix, e.g.:
- dynamic text/media
- responsive controls
- repeaters
- query/listing embedding
- conditions
- style controls

Unsupported capabilities are surfaced, not silently dropped.

No direct dependence on undocumented proprietary internal document format unless explicitly accepted/version-certified.

## Workflow action contract
- input/output schema;
- permission/service-principal requirements;
- idempotency;
- sync/async;
- retry class;
- timeout;
- compensation/rollback hints;
- secret references;
- audit/privacy classification.

## Billing/Membership adapter contract
Provider adapters may report/normalize:
- purchase/subscription identifiers;
- product/price mapping;
- lifecycle facts;
- period start/end;
- cancellation intent;
- payment/recovery facts;
- refund/dispute facts;
- webhook event ID/timestamp;
- reconciliation snapshot.

They do not directly decide access. Membership policy maps normalized provider facts to Enrollment/Entitlement state.

Requirements:
- signed webhook verification where provider supports it;
- idempotency/deduplication;
- out-of-order tolerance;
- reconciliation path;
- provider API version tracking;
- explicit source-of-truth boundaries;
- no raw card storage.

## Backup provider contract
Must support a declared subset:
- test connection;
- put/get/delete/list;
- stream/multipart/resume;
- checksum/metadata;
- server-side encryption options;
- retention/delete semantics;
- auth rotation.

Provider marketing status:
- Experimental
- Beta
- Supported
- Deprecated

“Supported” requires certification tests, not merely successful upload once.

## Notification channel contract
- recipient address/schema;
- payload/template constraints;
- size limits;
- delivery API result semantics;
- retry/rate limits;
- unsubscribe/preferences behavior;
- delivery status capability.

Do not call provider API acceptance “delivered” unless provider semantics justify it.

## Service container/public contracts
Public SDK should depend on interfaces/contracts, not core concrete internals.

Internal classes remain non-API unless explicitly documented.

Semantic versioning applies to Platform API/SDK contracts separately from product version.

## Hooks/filters
WordPress actions/filters may complement registries, but public hooks require:
- documented timing;
- inputs/outputs;
- mutable vs notification semantics;
- security implications;
- deprecation rules.

Avoid hundreds of arbitrary filters that bypass validation. Prefer explicit registry/service extension points.

## Assets
Extension assets:
- declare handles/entry points;
- load only in extension context;
- avoid bundling duplicate WordPress React when host provides it;
- no global CSS pollution;
- respect RTL/localization/accessibility.

## Security review
Certification evaluates:
- capability/policy integration;
- validation/escaping;
- SSRF for network adapters;
- secret handling;
- upload/path traversal;
- SQL injection;
- webhook signatures/replay;
- IDOR;
- CORS/auth;
- dependency vulnerabilities.

WPEssential cannot guarantee third-party code is safe merely because it uses the SDK; UI should distinguish official/certified/community integrations if a marketplace emerges.

## Compatibility certification matrix
Each adapter records tested:
- WPE Platform API version;
- WordPress versions;
- PHP versions where PHP code involved;
- provider/plugin versions;
- multisite if applicable;
- relevant builder/browser versions.

## Provider drift monitoring
External APIs/plugins change. Adapter documentation requires:
- upstream version/API reference;
- known breaking changes;
- deprecation handling;
- health diagnostic;
- last certification date/version.

## Failure isolation
A broken optional adapter should degrade only that integration where possible, not fatal WPEssential core.

Adapter callbacks crossing a public boundary normalize errors into WPE error taxonomy.

## Marketplace/package policy (future)
Do not build a marketplace before:
- signing/distribution/update model;
- license policy;
- security review process;
- namespace ownership;
- compatibility metadata;
- incident/revocation process
are formally designed.

## Implementation gate
Before SDK code begins, accept:
- Platform API versioning ADR;
- registry lifecycle;
- Free/Pro ownership boundary;
- error/event/ability contracts;
- package/build policy;
- certification test harness design.