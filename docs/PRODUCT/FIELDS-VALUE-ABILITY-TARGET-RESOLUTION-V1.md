# Surface 3 — Field Value Ability & Target Resolution V1

Status: **IMPLEMENTED CANDIDATE / CI REQUIRED**  
Owner: **Surface 3 — Custom Fields**  
Shared contract touched: **Platform Ability authorization / WordPress Ability bridge**

## Purpose

This slice places the certified post-meta value persistence adapter behind typed WPEssential Abilities and a bounded, fail-closed post target resolver.

It does not auto-enable the Pro Fields module in the plugin bootstrap and does not auto-register published Field Groups at runtime. The canonical shared entitlement/module-enable owner remains unresolved and MUST NOT be bypassed by Surface 3.

## Ability contract

Two internal Abilities are registered when `FieldsModule` itself is registered by an authorized module lifecycle:

- `wpessential/fields/read-value`
- `wpessential/fields/write-value`

The WordPress Abilities API mapping is:

- `wpessential/fields-read-value`
- `wpessential/fields-write-value`

Both use typed object schemas. The write contract requires:

- `group_id` — stable Field Group UUID;
- `field_uuid` — stable top-level Field UUID;
- `post_id` — positive WordPress post ID;
- `expected_group_revision` — positive optimistic schema revision;
- `value` — the bounded scalar/flat-list value shape supported by registered post-meta persistence V1.

The response carries Field Group ID/revision, Field UUID/key, post ID/type, operation status, changed flag and canonical value.

## Input-aware authorization

The existing Ability registry previously authorized only descriptor channel/capability. That was insufficient for resource-scoped Field values because WordPress Ability `permission_callback` receives the same input as execution and a post mutation must authorize the exact `post_id`.

This slice adds the optional `InputAuthorizingAbilityHandlerInterface` shared contract.

Authorization order becomes:

1. ability exists;
2. execution channel is allowed;
3. baseline shared Policy capability passes;
4. if the handler opts into input authorization, authorize the exact request input/resource;
5. only then execute the handler.

Existing handlers that do not implement the optional interface retain their previous capability-only behavior.

The WordPress Ability bridge and AJAX Ability handler now pass normalized/request input into `AbilityRegistry::authorize()` so both transports use the same resource preflight.

## Field resource authorization

`FieldValueAbilityHandler` opts into input-aware authorization.

For every request it verifies:

- authenticated principal;
- principal actor type is `user`;
- ExecutionContext user equals the active WordPress user;
- ExecutionContext site equals the active WordPress site;
- when a network ID is present, it equals the active WordPress network;
- read uses exact `current_user_can( 'read_post', $postId )`;
- write uses exact `current_user_can( 'edit_post', $postId )`.

The handler repeats this check as defense in depth for direct callers that bypass `AbilityRegistry`.

Resource authorization runs before Field Group/Field target resolution so an unauthorized caller does not receive configuration-dependent target information.

## WordPress Ability error boundary

The WordPress Abilities API validates input, checks permissions and then invokes the execute callback. WPEssential handlers use exceptions internally for fail-closed contract violations.

The WordPress bridge therefore translates execution exceptions to a generic `WP_Error` with code `wpessential_ability_execution_failed` instead of allowing implementation exceptions to escape through the native Ability boundary.

Authorization denials occur earlier in the native permission callback and become WordPress `ability_invalid_permissions` failures without handler execution.

The error mapping deliberately does not expose internal exception messages.

## Certified target resolver V1

A value target resolves only when all of these are true:

- Field Group definition exists in canonical Surface 3;
- definition status is `published`;
- payload re-normalizes successfully against the current canonical Field Group contract;
- target post exists with a concrete post type and status;
- every configured location source is certified by this V1 resolver;
- at least one OR location group matches all of its AND rules;
- requested Field UUID belongs to a top-level Field in that published group.

Certified location sources:

- `post_type`;
- `post_status`;
- `entity_id`.

Supported operators reuse the canonical Field Group contract:

- `equals`;
- `not_equals`;
- `in`;
- `not_in`;
- plus the existing boolean `negate` flag.

`entity_id` string values must fit the current PHP platform integer range; overflow/saturation is rejected.

## Deliberate fail-closed location boundary

The broader Field Group schema already knows additional location sources such as page template, taxonomy, term, user role, user, media MIME, comment context, settings page, custom table and frontend context.

This value Ability V1 does **not** guess their runtime semantics. If any configured rule uses a source outside the certified post target set, the resolver rejects the target until that source has its own runtime evaluator and evidence.

This prevents partial OR/AND interpretation from accidentally exposing a Field on a resource that the complete location contract would not target.

## Top-level Field boundary

V1 resolves only top-level stable Field UUIDs.

Nested Group/Repeater subfields are container-owned values. They MUST NOT be exposed as independent post-meta keys merely because they have stable UUIDs. Their mutation remains owned by the future structured/container persistence adapter.

Relations-owned values remain outside ordinary Fields post-meta persistence.

## Schema revision CAS

A Field UUID is stable identity, but a published Field definition can still change validation/settings between client read and value write.

`write-value` therefore requires `expected_group_revision`.

After authorization and target resolution, but before any post-meta mutation, the handler compares the caller's expected revision to the currently resolved Field Group revision. A mismatch rejects the write with no metadata mutation.

Read responses return the current `group_revision` so a client can use it for a later write.

## Storage-key identity invariant

The current registered-meta V1 storage key is the canonical Field machine `key`. A future explicit migration workflow is required to rename that physical storage key safely.

Until such a migration exists, `FieldIdentityAssigner` rejects changing the machine key of an already persisted Field UUID. This applies recursively to nested stable identities as well.

The rule prevents one stable logical Field identity from silently switching to a different native post-meta key and orphaning/duplicating data.

Deleting an old Field and creating a genuinely new Field remains a distinct definition operation; it is not represented as an in-place rename migration.

## Native WordPress contract references

Current implementation is aligned to official WordPress documentation:

- Abilities API is available in WordPress 6.9+;
- abilities register on `wp_abilities_api_init`;
- ability categories register on `wp_abilities_api_categories_init`;
- input/output use WordPress JSON Schema;
- native `permission_callback` receives the same normalized input used for execution;
- `WP_Ability::execute()` performs input validation, permission checking, execution and output validation;
- REST JSON Schema supports ordered multi-type definitions, which is important because string-first value types avoid converting values such as `"1"` before the canonical Field normalizer interprets them.

References:

- https://developer.wordpress.org/apis/abilities-api/
- https://developer.wordpress.org/apis/abilities-api/php-reference/
- https://developer.wordpress.org/reference/functions/wp_register_ability/
- https://developer.wordpress.org/reference/classes/wp_ability/execute/
- https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/

## Real WordPress evidence target

`tests/Integration/wordpress-fields-value-ability.php` verifies the direct certified handler/resource path against real WordPress metadata and capabilities.

`tests/Integration/wordpress-fields-value-ability-core.php` additionally performs test-only `FieldsModule::register()` against a real WordPress service graph and native Abilities API. It verifies:

- WPEssential category registration;
- native read/write Ability registration;
- retained typed input schemas;
- required write schema revision CAS;
- readonly/mutating annotations;
- REST exposure metadata;
- administrator native permission + write/read execution;
- native registered post-meta persistence;
- subscriber read permission on a public published post;
- subscriber write denial in `WP_Ability::check_permissions()` before execution;
- no metadata mutation after permission denial;
- stale schema revision translated to a safe WPE `WP_Error`;
- post-status location mismatch translated to a safe WPE `WP_Error`.

The Platform Compatibility Matrix runs both fixtures across WordPress 6.9/7.1 × PHP 8.2–8.5 and MariaDB 10.11 baselines.

## Change impact

**Affected**

- Surface 3 Field value Ability services;
- Surface 3 post target resolution;
- Surface 3 stable Field storage-key invariant;
- optional shared Ability input-authorization contract;
- WordPress Ability permission input forwarding;
- AJAX Ability authorization input forwarding;
- WordPress Ability exception-to-`WP_Error` boundary;
- compatibility matrix Fields integration evidence.

**Unaffected**

- existing non-input-authorizing Ability handlers retain capability-only authorization;
- CPT/Taxonomy runtime registration ownership;
- Definition Repository persistence model;
- Relations storage;
- provider/custom-table Field storage;
- Pro entitlement/module-enable ownership;
- automatic Field Group boot registration;
- admin React UI.

## Risk and recovery

Primary risks:

- resource authorization bypass through a transport path;
- stale ExecutionContext reuse;
- partial location evaluation;
- Field UUID/storage-key drift;
- stale Field Group schema writes;
- WordPress Abilities schema incompatibility across supported versions.

Recovery:

- no DB schema migration is introduced;
- source rollback removes the new Ability/target behavior;
- write operations use the already-certified post-meta persistence adapter;
- unauthorized/stale/unsupported requests fail before mutation;
- multi-row replacement remains blocked by the previous persistence gate.

## Explicit non-certifications / MUST NOT

This slice does **not** certify Surface 3 as complete.

It MUST NOT be interpreted as certifying:

- automatic published Field Group → native registered-meta boot binding;
- page template/taxonomy/term/user/media/comment/settings/custom-table/frontend location evaluators;
- nested Group/Repeater independent value Abilities;
- multi-row post-meta replacement/recovery;
- meta-key rename/data migration execution;
- provider/custom-table/secret/Relations value adapters;
- import/export migration workflows;
- admin Field builder/renderers;
- shared Pro entitlement/module-enable/package separation;
- production deployment or live-site migration.

## Verification gate

Before merge, fresh exact-head applicable CI must pass. At minimum for this implementation diff:

- Architecture Guards;
- PHP Quality Toolchain;
- Platform Compatibility Matrix;
- Distributable Package.

Review classification remains **SELF REVIEW + AUTOMATED REVIEW** unless a separate independent reviewer is explicitly recorded.

## Next bounded slice after merge

After this slice is merged and checkpointed, the next safe Surface 3 target is **published Field Group → runtime post-meta registration binding V1** for the already-certified post target/location subset, while preserving the unresolved shared Pro activation boundary.

Broader location evaluators and multi-row recovery remain separate bounded tasks rather than being hidden inside that boot-binding slice.
