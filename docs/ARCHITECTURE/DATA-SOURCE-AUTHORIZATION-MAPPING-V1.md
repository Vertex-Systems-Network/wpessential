# Shared Data Source Authorization Mapping V1

Status: bounded shared Platform contract required before Query provider execution.

## Purpose

A Data Source descriptor already requires canonical Policy authorization in principle. This contract adds the missing explicit mapping that tells an execution consumer which canonical WPEssential Ability name, WordPress capability key, and optional semantic resource type must be supplied to the shared Policy boundary.

The mapping is metadata only. It never grants access, executes Policy, resolves a principal, or makes a source executable by itself.

## Contract

`DataSourceAuthorizationMapping` contains only:

- `ability` — canonical `wpessential/<domain>/<action>` name;
- `capability` — stable WordPress capability key;
- optional `resourceType` — stable semantic resource identifier.

No callback, SQL fragment, table/column name, endpoint, credential, principal, role, user ID, capability resolver, or authorization result is carried by the mapping.

`DataSourceDescriptor::$authorization` is optional for backward compatibility. Existing descriptors remain valid for registration, inspection, capability discovery, planning, and other non-executing consumers.

Execution-capable consumers MUST call `requireAuthorizationMapping()` (or equivalently fail closed when `hasAuthorizationMapping()` is false) before constructing a canonical `AuthorizationRequest`. An unmapped legacy descriptor is therefore inspectable but cannot silently become executable.

## Validation

The shared value object validates the same canonical naming families already used by Platform Ability/Policy contracts:

- ability: `wpessential/<domain>/<action>`;
- capability: lowercase WordPress capability key using letters, digits, and underscore;
- resource type, when present: lowercase semantic identifier using letters, digits, dot, underscore, and hyphen.

Malformed mappings fail at construction time.

## Ownership boundary

This shared contract owns only Data Source → Policy metadata. It does not:

- create Query-local authorization rules;
- register or grant WordPress capabilities;
- weaken `PolicyEngine` or `AuthorizationRequest` checks;
- authorize a source because a mapping exists;
- execute `WP_Query` or any provider;
- expose REST/admin execution;
- cache authorization decisions;
- access provider-private storage.

The future Query execution tranche must resolve the source through the canonical Data Source Registry, require this mapping, build the canonical Policy request from the caller `ExecutionContext`, deny on any failed Policy decision, and only then invoke a separately certified provider execution adapter.

## Backward compatibility and recovery

Adding the nullable mapping at the end of `DataSourceDescriptor` constructor preserves existing positional/named constructor call sites. Legacy descriptors require no migration. If a mapping is removed or unavailable, execution must stop fail-closed while non-executing inspection remains available.

## Verification

Unit and smoke coverage verifies:

- legacy descriptors remain constructible without a mapping;
- execution mapping demand fails closed for an unmapped descriptor;
- mapped descriptors expose the exact validated mapping;
- malformed ability/capability/resource identifiers are rejected;
- existing Data Source registration/degraded/cache/raw-escape-hatch protections remain intact.
