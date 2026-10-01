# Dashboard Widgets Action-Input Binding Contract V1

Status: **FROZEN CANDIDATE — contract only, no action execution**

Exact source anchor: `main@57165b1571ecf45430ad34a7ed3e53823e08750d`

Issue authority: **#1279**

## 1. Purpose

This contract freezes the bounded authored input model required before Dashboard Widgets may reference a real non-empty Forms & Workflows mutating Ability.

Terminal prerequisites already exist:

- canonical Forms Ability reference validation;
- capability/policy authorization evaluation;
- bounded confirmation metadata;
- result/audit contract;
- opt-in canonical `AbilityInputValidator`.

Dashboard Widgets still has no `AbilityRegistry::execute()` path.

## 2. Exact dependency correction

Current Dashboard Widget action compilation admits only owner-17 mutating UI abilities with exactly empty `input_schema`.

That is no longer sufficient because a truthful Forms mutation requires resource/input identity.

Therefore:

- fake zero-input Forms mutations remain prohibited;
- action-input binding must be defined before the real Forms owner mutation contract;
- the current zero-input restriction may be relaxed only by a later bounded implementation that obeys this contract.

## 3. Authored shape

Optional only when `widget.action.ability_id` exists:

```text
widget.action.input.<property>
```

Each top-level property binding is one exact envelope.

Literal:

```text
source: literal
value: <bounded JSON-like value>
```

Dynamic:

```text
source: dynamic
source_ref: <registered Dynamic Value source>
value_ref: <bounded semantic reference>
resource: site | user | network
```

No Query, template, interpolation, callback, shortcode, block, PHP or provider-execution source is valid in V1.

## 4. Ability schema requirements

When `action.input` is authored, the referenced Ability must:

- resolve through canonical Ability Registry truth;
- have exact matching name;
- be owned by Surface 17;
- have `mutates=true`;
- have `ui_allowed=true`;
- have a non-empty `input_schema`;
- have a schema valid under canonical `AbilityInputValidator`;
- use root `type=object`;
- have effective `additionalProperties=false`.

Binding keys:

- must be top-level property names from the current Ability schema;
- must include every required schema property;
- may omit optional schema properties;
- must not include unknown names;
- maximum 32 authored bindings.

A zero-input Ability continues to require no authored input.

## 5. Binding key and envelope bounds

Binding keys:

- length 1..128 bytes;
- stable semantic property identifiers only;
- no nested authored paths such as `user.email`, `items[0]` or JSON Pointer syntax.

Encoded authored `action.input` envelope:

- <= 8192 bytes.

Unknown envelope keys fail closed.

## 6. Literal binding

Literal binding exact keys:

- `source`;
- `value`.

Rules:

- `source=literal`;
- value must be JSON-encodable;
- no value coercion, trimming, default injection or normalization;
- compiler validates the literal against the exact property schema by wrapping that property in a synthetic one-property root object and using the canonical validator;
- invalid schema/value fails closed;
- raw literal values are never written to audit metadata.

Nested literal objects/arrays are permitted only when the exact Ability property schema permits them and the canonical validator accepts them.

## 7. Dynamic binding

Dynamic binding exact keys:

- `source`;
- `source_ref`;
- `value_ref`;
- `resource`.

Rules:

- `source=dynamic`;
- references use existing bounded semantic-reference rules;
- resource is exactly `site`, `user` or `network`;
- resource id is never authored;
- site derives `ExecutionContext.siteId`;
- user derives authenticated `ExecutionContext.principal.userId`;
- network derives `ExecutionContext.networkId`;
- missing context fails closed;
- resolution uses only canonical `DynamicValueResolverInterface`;
- unknown source, resolver exception, unresolved result, null result or unsafe value fails closed.

Dynamic V1 may target only property schemas of:

- string;
- integer;
- number;
- boolean;
- array of string/integer/number/boolean.

Dynamic object/null bindings are unsupported.

Final assembled value must still pass the canonical Ability input validator.

## 8. Secret-bearing property guard

Until Ability descriptors expose explicit sensitivity metadata, Dashboard Widget action-input V1 refuses clearly credential-bearing top-level property names.

Normalize the property name by lowercasing and removing separators/non-alphanumerics, then reject names containing any of:

- password
- passwd
- secret
- token
- apikey
- privatekey
- authorization
- cookie
- credential
- cardnumber
- cvv
- cvc

This restriction is Surface-10-specific and does not redefine shared Ability schemas.

## 9. Compile-time result

A later compiler must produce a typed descriptor containing only:

- validated literal bindings;
- bounded dynamic binding metadata;
- ability id;
- definition/revision identity needed for safe correlation.

It must not contain:

- resolver instances;
- callbacks;
- provider objects;
- secrets;
- raw audit payloads;
- an executable closure;
- a stale schema snapshot that can override current Ability truth.

## 10. Runtime assembly

A later runtime binder must:

1. receive the compiled descriptor and exact authenticated `ExecutionContext`;
2. start from compiled literal bindings;
3. resolve each dynamic binding through canonical `DynamicValueResolverInterface`;
4. derive resource identity from the same context;
5. assemble one top-level input object;
6. re-resolve the current Ability descriptor;
7. require owner 17, `mutates=true`, `ui_allowed=true`;
8. validate the current descriptor schema through canonical `AbilityInputValidator`;
9. validate final assembled input against that exact current schema;
10. fail closed on schema drift or any mismatch;
11. return bounded typed input only on success.

No action execution is performed by the binder.

## 11. Schema drift

Current Ability descriptor truth is authoritative at runtime.

If schema changes after widget compilation:

- stale compiled bindings do not override it;
- final validation uses the current schema;
- incompatible bindings fail closed;
- no automatic migration/coercion/default injection;
- user must update/recompile the Dashboard Widget definition.

## 12. Audit/result boundary

Per the frozen Action Result + Audit contract:

- raw input values are never logged;
- raw Dynamic resolver output is never logged;
- only `input_present=true|false` may enter bounded audit metadata;
- validation notices never include rejected values;
- no provider/exception body is surfaced.

## 13. Exact later implementation direction

Fresh exact-main verification is required, but the expected bounded implementation surface is:

1. `frameworks/Modules/DashboardWidgets/DashboardWidgetActionInputDescriptor.php` — new
2. `frameworks/Modules/DashboardWidgets/DashboardWidgetActionInputCompiler.php` — new
3. `frameworks/Modules/DashboardWidgets/DashboardWidgetActionInputBinder.php` — new
4. `frameworks/Modules/DashboardWidgets/DashboardWidgetRegistrationCompiler.php`
5. `frameworks/Modules/DashboardWidgets/DashboardWidgetRegistrationDescriptor.php`
6. focused tests for compiler/binder/registration integration
7. five shared-truth files

No `AbilityRegistry.php` modification is expected.

No Dashboard Widget action execution or Forms source change is allowed in that implementation tranche.

## 14. Permanent V1 security invariants

The binding path must never:

- execute PHP/callbacks/classes;
- interpolate template strings;
- resolve Query/Data Source values in V1;
- accept authored resource ids;
- execute owner Ability code;
- mutate Forms state;
- bypass capability/policy authorization;
- write raw input to audit;
- expose raw resolver/provider errors;
- log secrets;
- silently coerce input;
- auto-migrate schema drift;
- widen REST mutation APIs.

## 15. This contract batch scope

This contract PR changes only:

1. this contract document;
2. `.ai/state/CURRENT-STATE.yaml`;
3. `.ai/state/LAST-CHECKPOINT.md`;
4. `README.md`;
5. `config/coordination/agent-work-queue.json`;
6. `config/coordination/runner-benchmark.json`.

No runtime/product PHP, JS or tests are authorized here.

## 16. Promotion boundary

On terminal merge promote only:

`CONTRACT_FROZEN_DASHBOARD_ACTION_INPUT_BINDING_V1`

and:

`READY_FOR_BOUNDED_DASHBOARD_ACTION_INPUT_BINDING_V1`

Not promoted:

- action-input implementation;
- Forms mutation Ability;
- trusted `form_action` UI/orchestration;
- `AbilityRegistry::execute()`;
- full Surface 10/17 parity;
- deployment, GA or release.
