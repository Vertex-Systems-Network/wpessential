# Fields Value Normalization V1

Status: **bounded implementation foundation**  
Surface: **3 — Custom Fields**  
Options Bank authority: **BANK_REVIEWED / 618 records**

## Purpose

This slice adds a server-side canonical value normalization boundary after field-definition normalization. It is intentionally independent of React controls and storage adapters so the same validation contract can be reused by admin UI, Forms, REST/Abilities and future import/migration paths.

## Implemented in V1

- server-authoritative required/null handling;
- common Meta Box-style clone/repeatable list normalization with min/max enforcement;
- primitive string/email/URL/number/boolean normalization;
- single/multiple choice validation against explicit choices;
- Date / Week / Month canonical formats;
- Time validation;
- DateTime normalization to UTC with explicit input timezone requirement;
- HEX/HEXA/RGB/RGBA/HSL/HSLA color syntax/range validation;
- WordPress media/post/term/user ID references where the field type is explicitly WordPress-owned;
- Group recursive subfield normalization;
- Repeater recursive row normalization and row-count bounds;
- unknown subfield rejection;
- UI-only fields reject persisted values;
- Relationship field values fail closed and remain owned by the Relations Engine;
- unsupported complex/provider-owned types fail closed rather than silently serializing arbitrary input.

## Deliberate non-goals

This is not yet a storage adapter, registered-meta compiler, renderer or full 618-option runtime implementation. It does not claim completion for Flexible Content, Clone schema references, advanced date ranges/recurrence, JSON/key-value/visual structured objects, custom registered fields, remote entities, relation edges, secret vault references or provider-specific values.

Those types require their canonical schema/provider/owner contracts and will be enabled through later bounded slices instead of accepting arbitrary arrays/blobs now.

## Storage boundary

The normalizer returns canonical PHP scalar/list/map values only. It does not call `update_post_meta()`, `register_meta()` or another persistence API. The next storage slice must explicitly declare:

- supported object type/subtype;
- WordPress registered-meta schema mapping;
- single/multiple storage semantics;
- authorization and sanitization policy;
- revisions support;
- multisite scope;
- queryability/index implications;
- migration/rollback behavior.

## Security negative requirements

- no arbitrary PHP/JavaScript execution from Code fields;
- no trusting hidden/UI-only values as authorization;
- no arbitrary object deserialization;
- no relation edge storage through ordinary field meta;
- no provider-owned complex value accepted before its adapter is registered;
- no frontend-only validation claim.

## Verification

`tests/Unit/Modules/Fields/FieldValueNormalizerTest.php` covers primitive normalization, invalid email/URL, choice membership, repeatability bounds, nested Group/Repeater values, media/color/datetime normalization, UI-only rejection and Relations ownership rejection.
