# WPEssential — Product License OpenAPI Component Schema Profile

Status: **Phase 0 paper component contract / no OpenAPI artifact, server, client or API call authorized**  
Date: 2026-08-28  
Related: ADR-0070, ADR-0072, ADR-0076, Product License Remote Resource Model, Product License OpenAPI Candidate Contract.

## Purpose

Remove remaining field-level ambiguity from the future Product License API without creating executable OpenAPI YAML/JSON.

This document defines component shapes, required/optional semantics, identifiers, enums and concurrency/idempotency expectations at paper level.

## Common scalar conventions

### OpaqueId
String, server-generated or client-local opaque identity as specified by resource. It is not a bearer credential.

Rules:
- never parse business meaning from it;
- max length remains future OpenAPI detail;
- case sensitivity/encoding remain explicit in final schema.

### LocalUuid
Client-generated stable UUID used only for local installation/site continuity mapping. Exact UUID textual profile remains future implementation detail.

### ResourceVersion
Opaque string exposed through strong ETag semantics where resource is mutable.

The client does not increment or interpret it.

### Timestamp
RFC 3339/ISO-8601 UTC timestamp in future OpenAPI contract.

### SafeUrlMetadata
Optional normalized URL/domain metadata used for reconciliation/display. It is mutable metadata, never sole identity or authentication evidence.

## Enumerations

### EnvironmentClass
- `production`
- `staging`
- `development`
- `migration`
- `disaster_recovery`
- `unknown_review_required`

Exact commercial allowance is Product Contract policy, not the enum itself.

### ContractState
- `trialing`
- `active`
- `grace`
- `expired`
- `suspended`
- `revoked`
- `terminated`

### ActivationState
- `unlinked`
- `linked_unallocated`
- `active`
- `offline_cached`
- `revalidation_required`
- `transfer_pending`
- `clone_review`
- `service_unavailable`
- `expired`
- `revoked`
- `disconnected`

### SiteAllocationState
- `unallocated`
- `reserved`
- `active`
- `staging_approved`
- `development_approved`
- `migration_source`
- `migration_target`
- `dr_temporary`
- `release_pending`
- `released`
- `conflict`
- `site_missing`
- `transfer_pending`
- `revoked`

### TransferState
- `requested`
- `awaiting_source`
- `awaiting_target`
- `in_progress`
- `completed`
- `cancelled`
- `expired`
- `conflict`
- `failed`

### ReviewState
- `pending`
- `approved`
- `rejected`
- `expired`
- `cancelled`

### ConflictCode
Initial closed vocabulary:
- `allocation_limit_exceeded`
- `allocation_already_bound_elsewhere`
- `possible_production_clone`
- `duplicate_installation_identity`
- `stale_restored_entitlement`
- `site_id_reuse_detected`
- `domain_changed_revalidation_required`
- `old_host_still_active`
- `transfer_source_not_released`
- `transfer_target_not_authorized`
- `network_binding_changed`
- `account_ownership_changed`
- `contract_no_longer_covers_environment`
- `signed_entitlement_binding_mismatch`
- `entitlement_rollback_detected`
- `remote_allocation_missing`
- `local_allocation_missing`
- `service_state_ambiguous`

Unknown future values require compatible enum-evolution policy; clients must not convert unknown conflict into `active`.

## Component: AccountSummary

Required:
- `id: OpaqueId`
- `status: string/closed profile later`

Optional:
- `display_label: string`
- `organization_summary: object` limited to client-useful safe fields
- `locale: string`
- `support_profile: object`

Excluded by default:
- site inventory;
- billing/card details;
- credentials/tokens.

Mutable response carries ETag when Account mutations are exposed.

## Component: ProductContract

Required:
- `id: OpaqueId`
- `product_key: string`
- `tier_key: string`
- `state: ContractState`
- `allocation_policy_id: OpaqueId`
- `allocation_policy_version: string`
- `environment_allowances: object`
- `support_rights: object`
- `update_rights: object`
- `feature_entitlement_profile_ref: OpaqueId|string`

Optional:
- `trial_starts_at: Timestamp`
- `trial_ends_at: Timestamp`
- `starts_at: Timestamp`
- `ends_at: Timestamp`
- `grace_ends_at: Timestamp`
- `capacity_summary: CapacitySummary`

`capacity_summary` is advisory UX and never grants rights.

## Component: CapacitySummary

Required:
- `policy_version: string`

Optional numeric fields where policy exposes them:
- `production_limit`
- `production_used`
- `production_available`
- `network_limit`
- `network_used`

Values are non-negative bounded integers. Missing means not applicable/undisclosed, not unlimited unless policy explicitly says so.

## Component: InstallationActivation

Required:
- `id: OpaqueId`
- `account_id: OpaqueId`
- `contract_id: OpaqueId`
- `local_installation_uuid: LocalUuid`
- `environment_class: EnvironmentClass`
- `state: ActivationState`
- `created_at: Timestamp`
- `updated_at: Timestamp`

Optional:
- `canonical_url: SafeUrlMetadata`
- `last_reconciled_at: Timestamp`
- `conflict_code: ConflictCode`

Mutable resource uses strong ETag/If-Match.

### InstallationCreateRequest

Required:
- `local_installation_uuid`
- `environment_class`
- `contract_id` when account has more than one valid candidate or contract selection is explicit.

Optional:
- `canonical_url`
- minimal disclosed WordPress/platform compatibility profile only when required by policy.

Explicitly excluded:
- content inventory;
- complete plugin/theme list;
- WordPress credentials.

Create requires Idempotency-Key.

## Component: NetworkActivation

Required:
- `id`
- `installation_id`
- `state: ActivationState`
- `allocation_mode: string`
- `policy_version: string`
- `created_at`
- `updated_at`

Optional:
- `local_network_ref: string` safe metadata only;
- `conflict_code`.

Mutable resource uses ETag/If-Match.

### NetworkActivationCreateRequest

Required:
- `installation_id` through route/binding;
- `allocation_mode` from allowed contract policy.

Optional:
- local network coordinate metadata.

Create requires Idempotency-Key.

## Component: SiteAllocation

Required:
- `id`
- `contract_id`
- `installation_id`
- `environment_class: EnvironmentClass`
- `state: SiteAllocationState`
- `production_counting: boolean`
- `created_at`
- `updated_at`

Conditionally required:
- `network_activation_id` for network-scoped Multisite allocation;
- `local_site_uuid` when client supports stable site identity mapping.

Optional:
- `local_site_numeric_id` mutable metadata;
- `current_url` safe metadata;
- `lineage_source_allocation_id`;
- `conflict_code`;
- `last_reconciled_at`;
- `release_requested_at`;
- `released_at`.

Mutable resource uses ETag/If-Match.

### SiteAllocationCreateRequest

Required:
- target contract/network/installation binding implied by route or explicit schema;
- `local_site_uuid` for Multisite/scope-aware identity where available;
- `environment_class`.

Optional:
- `local_site_numeric_id`;
- `current_url`;
- lineage/reason metadata only through defined fields.

Create requires Idempotency-Key.

### SiteAllocationPatchRequest

Allowed mutable fields are explicit; no arbitrary merge-patch over server-owned state.

Candidate fields:
- `environment_class` when policy permits reclassification;
- `current_url` safe metadata;
- selected local continuity metadata.

Client cannot directly set:
- `production_counting`;
- arbitrary `state`;
- `contract_id`;
- another account owner;
- signed entitlement fields.

Requires If-Match and Idempotency-Key where mutation can have commercial side effects.

## Component: AllocationReleaseRequest

Required request body may be empty if target resource + If-Match + Idempotency-Key fully identify intent.

Optional:
- `reason_code` from bounded safe enum;
- client correlation reference.

Release response returns updated SiteAllocation or an Operation/Reconciliation reference when outcome is asynchronous/unknown.

Local client must not free capacity solely because request was sent.

## Component: ReconciliationObservation

Minimal client-observed facts only.

Required:
- `local_installation_uuid`
- declared current `environment_class`

Optional:
- `local_site_uuid`
- `local_site_numeric_id`
- `current_url`
- previously persisted remote resource IDs;
- last verified entitlement identity/version reference;
- local continuity/clone reason code.

Excluded:
- content fingerprints by default;
- user/member/customer data;
- arbitrary diagnostics.

## Component: ReconciliationResult

Required:
- `status` from future closed reconciliation state enum;
- `action_required: boolean`

Optional:
- authoritative `installation: InstallationActivation` summary;
- authoritative `site_allocation: SiteAllocation` summary;
- `review: SiteAllocationReview`;
- `conflict_code`;
- safe `next_action_code`;
- entitlement refresh reference where permitted.

It never reveals another customer's identifying allocation details during conflict handling.

## Component: SiteAllocationReview

Required:
- `id`
- `reason` enum:
  - `staging_clone`
  - `development_clone`
  - `migration`
  - `disaster_recovery`
  - `possible_production_clone`
- `state: ReviewState`
- `created_at`
- `updated_at`

Optional:
- source/target allocation references visible to current authorized Account;
- `expires_at`;
- safe conflict summary;
- resulting environment/allocation decision.

Mutable review actions use ETag/If-Match and Idempotency-Key where retryable.

## Component: Transfer

Required:
- `id`
- `type` closed enum future profile;
- `state: TransferState`
- source resource reference(s) permitted to current Account;
- target Account/Installation/Network reference as required by transfer type;
- `created_at`
- `updated_at`

Optional:
- `expires_at`
- `completed_at`
- `cancelled_at`
- `conflict_code`
- overlap policy/window metadata.

Transfer never carries Vault secret/plaintext/site content.

Create/complete/cancel mutations require Idempotency-Key and existing-resource mutations require If-Match.

## Component: SignedEntitlementEnvelope

Required:
- `artifact: string|structured canonical artifact representation` exact wire shape pending ADR-0042 fixtures;
- `schema_version: string`
- `signing_profile: string`
- `keyset_id: OpaqueId`
- safe verification metadata sufficient for client routing.

The API response does not convert entitlement claims into trusted state by transport alone. Client verifies signature/canonicalization/binding/freshness independently.

No private signing material is exposed.

## Component: EntitlementKeysetSummary

Required:
- `id`
- `profile_version`
- root-authorized public verification material/profile reference according to ADR-0042.

Optional:
- validity/rotation metadata;
- superseded-by reference.

Exact trust-bootstrap wire format remains evidence-gated.

## Component: ProblemDetails

RFC 9457-compatible required fields by problem type:
- `type`
- `title`
- `status`

Optional standard fields:
- `detail`
- `instance`

WPE extensions:
- `code: string` stable machine code;
- `retryable: boolean` when meaningful;
- `field_errors: array<object>` for schema validation;
- `current_resource_version: ResourceVersion` only when safe;
- `support_correlation_id: string` opaque safe identifier.

Security rules:
- 403/404 choice may hide resource existence;
- no stack/database/service topology;
- no other-account IDs/site/domain details in conflict detail;
- no OAuth tokens or signed private material.

## Component: FieldError

Required:
- `field: string` schema path/property name;
- `code: string` stable validation code.

Optional:
- `detail: string` safe localized/human-readable text.

Never echoes secret request values.

## Component: CursorPage

Collection envelope candidate:
- `items: array<T>` required;
- `next_cursor: string|null` required;
- `has_more: boolean` required;
- optional `limit` actually applied.

No total count is required unless a resource explicitly supports it cheaply/safely.

Cursor is opaque and cannot encode reusable authorization credentials.

## Header contract

### Idempotency-Key
Required on retryable create/commercial mutations selected by route profile.

Rules:
- opaque client operation ID;
- same authenticated Account + route/resource operation scope;
- same key + materially different normalized request → stable conflict Problem;
- unknown outcome retry reuses same key;
- retention window is explicit in future service profile;
- not an auth credential.

### If-Match
Required for concurrency-sensitive mutation of existing mutable resources.

Stale value returns precondition/resource-version problem. Client refetches/reconciles before changing intent.

### ETag
Strong opaque version on mutable resource responses.

### Retry-After
Used with 429/eligible temporary conditions when server can provide useful retry timing.

### Correlation ID
Optional safe client/server correlation header, distinct from Idempotency-Key.

## Client persistence minima

Local WordPress persists only:
- local installation/site UUIDs;
- remote installation/network/allocation IDs;
- last resource version/ETag where needed for safe continuation;
- outstanding idempotency operation IDs for unknown outcomes;
- last verified signed entitlement + anti-rollback/freshness metadata;
- safe reconciliation/conflict state.

OAuth credentials remain Vault. No remote account password/card/site inventory cache.

## Compatibility rules

Future OpenAPI v1 may add optional fields/enums only under an explicit compatibility policy.

Client behavior:
- unknown optional field ignored safely;
- unknown state/conflict enum becomes `unsupported/newer-service-state` style degraded handling, never optimistic `active`;
- missing newly required semantic field implies version incompatibility rather than guessed default;
- breaking schema/state semantics require major API/profile change.

## Future actual OpenAPI evidence — NOT AUTHORIZED

After explicit consent:
- encode these components into OpenAPI;
- lint/schema validate;
- request/response examples;
- OAuth scope mapping;
- idempotency body canonicalization/retention;
- ETag/precondition contract tests;
- enum compatibility fixtures;
- pagination cursor behavior;
- RFC 9457 conformance;
- last-seat/release/transfer races;
- conflict privacy/resource enumeration;
- remote-success/local-persist-failure;
- signed entitlement verification integration.

Executed Product License API fixtures: **0**.

## Development gate

This document creates no OpenAPI YAML/JSON, route, service DB, OAuth client, mock server, SDK or API call. ADR-0014 explicit owner consent remains required.