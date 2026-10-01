# Ability Input Validation Contract V1

Status: FROZEN CANDIDATE — contract only, no runtime validation implementation

Exact source anchor: main@f7e24263b1429ffbacd3cba212684858feafbfe9
Committee authority: Issue #1275

## Purpose

This contract defines the smallest canonical server-side Ability input validation subset required before Dashboard Widgets may bind non-empty action input to a real Forms & Workflows mutating Ability.

Fresh exact-main evidence proves:
- AbilityDescriptor.inputSchema is metadata only.
- AbilityRegistry.authorize() and AbilityRegistry.execute() do not validate descriptor input schemas.
- no canonical generic Ability input validator exists.
- Surface 17 has no truthful zero-input business mutation.
- real Forms mutations require resource identity, revision/state identity, replay/idempotency and owner-domain semantics.

Therefore the prior plan to create a zero-input Forms mutation first is superseded.

## Ownership

The validator is a shared Platform primitive. It owns only deterministic validation of already-constructed PHP input against an already-registered Ability descriptor schema.

It does not own capability/policy authorization, resource authorization, business mutation semantics, Dashboard Widget authored bindings, Forms submission/run state, provider execution, secret resolution or audit/result mapping.

## Safe integration rule

The first implementation must be opt-in. A neutral AbilityInputValidator may validate descriptor.inputSchema plus runtime input, but AbilityRegistry behavior must not change globally in the initial validator tranche.

Reason: existing abilities already perform varying handler-level validation, so silently inserting new validation into every authorize/execute call would be a repository-wide behavioral change. Global Registry enforcement requires a separate compatibility/migration gate.

## V1 schema envelope

V1 is a deliberately small JSON-Schema-like subset. The root input schema MUST be type=object.

Supported keywords:
- type
- required
- properties
- additionalProperties
- items
- enum
- minLength
- maxLength
- minimum
- maximum
- minItems
- maxItems

Any other keyword fails schema validation. This includes $ref, $id, $schema, definitions, $defs, oneOf, anyOf, allOf, not, if/then/else, pattern, patternProperties, format, contentEncoding, contentMediaType, const, default, examples, dependencies/dependentRequired, uniqueItems, contains, propertyNames and unevaluated* keywords.

V1 is not a full JSON Schema implementation.

## Supported types

Allowed single type values:
- object
- array
- string
- integer
- number
- boolean
- null

V1 schemas use one string type only, not type unions. No coercion is allowed: numeric strings are not numbers, 0/1 are not booleans, empty string is not null, and shapes are never silently converted.

## Object rules

For type=object, supported companion keywords are properties, required and additionalProperties.

Rules:
- properties must be an associative map.
- property names must be non-empty strings.
- maximum 64 properties per object schema.
- required must be a list of unique strings that exist in properties.
- additionalProperties must be boolean; when omitted V1 defaults to false.
- unknown runtime keys fail closed when additionalProperties=false.
- later Dashboard Widgets/Forms action contracts MUST require additionalProperties=false.

## Array rules

For type=array, items is required and must be one valid V1 schema. minItems/maxItems are optional non-negative integers. Effective maxItems must not exceed 100. Sparse/non-list PHP arrays are invalid as arrays.

## String rules

For type=string, enum/minLength/maxLength are supported. Length is measured in bytes. Absolute V1 maximum string size is 4096 bytes. The validator performs no trimming, normalization, sanitization or case conversion.

## Integer and number rules

For integer/number, enum/minimum/maximum are supported. Integer requires a PHP integer. Number accepts integer or finite float. NaN/INF/-INF and numeric-string coercion are invalid.

## Boolean and null rules

Boolean accepts only PHP booleans. Null accepts only null.

## Enum rules

Enum must be a non-empty list of at most 64 scalar-or-null values compatible with the declared type. Comparison is strict/type-sensitive. Object/array enum values are unsupported.

## Global complexity limits

Hard V1 limits:
- root type object only
- schema depth <= 8
- total schema nodes <= 256
- properties per object <= 64
- required entries <= 64
- enum values <= 64
- runtime array items <= 100
- string value <= 4096 bytes
- canonical encoded runtime input envelope <= 32768 bytes

Owner contracts may impose stricter limits.

## Runtime input envelope

Runtime input must be a PHP associative array representing the root object. Unsupported PHP values such as resources, closures and arbitrary objects fail closed. Keys must be strings. Runtime nesting may not exceed depth 8. No normalization, coercion, default injection or key removal occurs.

## Schema validation vs input validation

The canonical primitive must expose two logically distinct operations equivalent to validateSchema(schema) and validateInput(schema,input).

Schema validation fails on unsupported keywords/types, malformed shapes, invalid bounds, invalid required/property relations, invalid enums, complexity excess and non-object roots.

Runtime validation fails on type mismatch, missing required keys, additional properties, enum mismatch, bounds, size/depth excess and unsupported PHP values. Runtime validation must not silently reinterpret an invalid schema.

## Result model

Ordinary invalid input/schema must produce a non-throwing typed result with machine-safe fields equivalent to valid, code and path. Raw runtime values must never be returned.

Required stable code families:
- schema_invalid
- unsupported_keyword
- unsupported_type
- complexity_exceeded
- input_type_mismatch
- required_missing
- additional_property
- enum_mismatch
- bound_violation
- input_too_large
- input_depth_exceeded
- unsupported_runtime_value

Paths may identify property/index location only, such as $, $.definition_id or $.items[3].value. Paths never include rejected values and are capped at 512 bytes. V1 may stop at the first failure.

## Auth and mutation boundary

Input validation is not authorization. Valid input does not imply authenticated actor, capability, resource ownership, site/network access, mutation permission, replay safety or business validity.

Canonical execution ordering remains:
1. descriptor exists
2. schema is valid
3. runtime input validates
4. policy/capability authorization
5. owner-specific resource/input authorization
6. prerequisite confirmation/audit gates
7. only then future owner execution

## Security invariants

The validator must never evaluate PHP/callbacks/closures, compile authored regex, fetch remote schema/ref, resolve providers/secrets, execute owner code, mutate input, inject defaults, coerce values, log raw input, expose secret values, call network/filesystem/database APIs or interpret HTML/shortcode/block/script content.

It is pure deterministic validation.

## Initial implementation boundary

The later implementation tranche is limited to a neutral shared Platform path such as frameworks/Platform/Abilities/InputValidation/** plus focused tests. Exact paths will be revalidated on fresh main.

Initial implementation explicitly does NOT authorize edits to AbilityRegistry.php, module source, Dashboard Widgets source or Forms & Workflows source.

## Later Dashboard Widgets input-binding gate

Only after the shared validator is terminally merged may Dashboard Widgets define authored action input. That later contract must bind only exact registered Ability schemas, prohibit callback/provider execution, compile bounded static/approved dynamic values, validate final server-side input with the canonical validator, preserve owner authorization and avoid logging raw input.

## Later Forms & Workflows mutation gate

Only after canonical non-empty input validation exists may a real Surface-17 mutating Ability be frozen. The owner contract must choose an actual domain operation with exact input schema, resource identity, capability, state/revision checks, replay/idempotency behavior, deterministic result shape and no Dashboard Widget-specific business logic.

A fake zero-input mutation is explicitly prohibited.

## This contract batch scope

This batch changes only:
1. this contract document
2. .ai/state/CURRENT-STATE.yaml
3. .ai/state/LAST-CHECKPOINT.md
4. README.md
5. config/coordination/agent-work-queue.json
6. config/coordination/runner-benchmark.json

No runtime/shared-Platform PHP, JS or test implementation is authorized.

## Promotion boundary

On terminal merge the only promotions are:
- CONTRACT_FROZEN_ABILITY_INPUT_VALIDATION_V1
- READY_FOR_BOUNDED_ABILITY_INPUT_VALIDATOR_V1

Not promoted:
- global AbilityRegistry validation
- Dashboard Widget action input
- Forms mutation Ability
- Dashboard Widget action execution
- full Surface 10/17 parity
- deployment, GA or release
