# Forms & Workflows Set-Enabled Mutating Ability Owner Contract V1

Status: **FROZEN CANDIDATE — owner contract only, no mutation implementation**

Exact source anchor: `main@880db8ac6938f520fcc53d7f23eddd319b45e5fe`

Issue authority: **#1283**

## 1. Purpose

This contract freezes the first real Surface-17 mutating Ability after Dashboard Widgets gained bounded, schema-validated action-input binding.

The mutation is intentionally narrow:

**enable or disable one existing Forms & Workflows definition by transitioning only between Published and Disabled.**

It does not create submissions, entries, workflow runs, providers, payments, secrets or external side effects.

## 2. Why this mutation is first

Exact-main evidence shows:

- Surface 17 already owns canonical `form-workflow` definitions.
- Shared `DefinitionRepositoryInterface::save()` supports revisioned writes.
- `PersistentDefinitionRepository` enforces monotonic revision writes and optimistic `updateIfCurrentRevision` conflict protection.
- `DefinitionStatus` already defines `published` and `disabled`.
- Forms Atomic/UX truth owns form identity/lifecycle/revisions and classifies this family as reversible.
- submission/entry/run persistence is not implemented yet.

Therefore Published ↔ Disabled is the smallest real reversible owner mutation that does not invent missing persistence architecture.

## 3. Canonical Ability

Name:

`wpessential/forms-workflows/set-enabled`

Owner:

- Surface 17 / Forms & Workflows

Capability:

- `manage_options`

Mutation flag:

- `mutates=true`

Allowed execution channels:

- `Internal`
- `Ui`

Forbidden in V1:

- `Rest`

The WordPress Ability exposure must use `showInRest=false`.

No admin-post/AJAX/custom REST mutation endpoint is introduced by this tranche.

## 4. Input schema

The Ability descriptor input schema is exactly equivalent to:

```text
type: object
required:
  - definition_id
  - expected_revision
  - enabled
properties:
  definition_id:
    type: string
    minLength: 36
    maxLength: 36
  expected_revision:
    type: integer
    minimum: 1
  enabled:
    type: boolean
additionalProperties: false
```

### UUID validation correction

Canonical `AbilityInputValidator` V1 intentionally does not support `pattern` or `format`.

Therefore descriptor schema length validation is not sufficient to prove UUID identity.

The owner handler/service MUST additionally require `definition_id` to match the canonical lowercase RFC4122 UUID shape already used by `Definition`:

```text
^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$
```

No coercion is allowed.

## 5. Direct Ability input validation

Global `AbilityRegistry` does not automatically validate `AbilityDescriptor::inputSchema`.

Therefore this owner Ability MUST validate its own input before authorization/mutation.

The implementation must reuse canonical `AbilityInputValidator` for descriptor-shape/runtime type/bounds validation and then apply the explicit UUID-shape check.

This validation must occur in both resource authorization and execution-safe handling as needed so a direct WordPress Ability invocation cannot bypass schema validation.

No reliance on Dashboard Widgets validation alone is allowed.

## 6. Resource authorization

The handler must implement `InputAuthorizingAbilityHandlerInterface`.

After global capability authorization, `authorizeInput()` must fail closed unless:

1. input passes the canonical schema validator;
2. `definition_id` passes the owner UUID check;
3. the definition exists;
4. `FormWorkflowDefinition::assertOwned()` succeeds;
5. current status is exactly `Published` or `Disabled`;
6. current revision equals `expected_revision`.

Machine-safe deny reasons must be stable and bounded.

Recommended reason families:

- `forms_workflows_set_enabled_invalid_input`
- `forms_workflows_set_enabled_not_found`
- `forms_workflows_set_enabled_wrong_owner`
- `forms_workflows_set_enabled_invalid_state`
- `forms_workflows_set_enabled_revision_conflict`

No payload, raw storage value or internal exception text is returned in authorization decisions.

## 7. Execution re-check

Authorization is not a mutation lock.

`handle()` MUST re-read the definition immediately before deciding whether to write.

It must repeat:

- canonical input validation;
- UUID validation;
- existence;
- owner/type assertion;
- Published/Disabled state check;
- exact expected-revision check.

This prevents stale authorization from becoming execution authority.

## 8. Target mapping

Input:

`enabled=true`

means target status:

`DefinitionStatus::Published`

Input:

`enabled=false`

means target status:

`DefinitionStatus::Disabled`

No other lifecycle state transition belongs to this Ability.

Draft and Archived current definitions fail closed.

## 9. No-op semantics

If:

- current revision exactly equals `expected_revision`; and
- current status already equals the requested target;

then the operation succeeds as an explicit no-op.

Required behavior:

- do not call repository `save()`;
- do not increment revision;
- return `changed=false`;
- return `result_code=already_target_status`.

This makes repeated same-target requests deterministic only while the expected revision remains current.

## 10. Changed mutation semantics

If the target differs from current Published/Disabled status:

1. re-read and validate current definition;
2. construct a new immutable `Definition`;
3. preserve exactly:
   - id;
   - slug;
   - type;
   - schemaVersion;
   - ownerSurfaceId;
   - payload;
   - dependencies;
4. set target status;
5. set revision to `current.revision + 1`;
6. preserve the existing checksum value because `Definition::computedChecksum()` is payload-derived and this mutation does not change payload;
7. save only through canonical `DefinitionRepositoryInterface::save()`;
8. return `changed=true`.

No direct table gateway/database write is allowed.

## 11. Revision and replay safety

`expected_revision` is mandatory.

If current revision differs from `expected_revision` at authorization or execution re-check:

- fail with revision conflict;
- perform no write.

The repository's own optimistic revision guard remains the final concurrency boundary.

After one successful changed mutation:

- a replay carrying the old expected revision fails closed;
- no automatic retry is allowed;
- the client must re-read current definition truth before a new mutation attempt.

## 12. Output contract

Successful result is a bounded object with exactly:

- `definition_id`
- `previous_revision`
- `revision`
- `status`
- `enabled`
- `changed`
- `result_code`

Allowed result codes:

- `status_changed`
- `already_target_status`

For a changed mutation:

- `previous_revision` = input/current revision;
- `revision` = previous revision + 1.

For no-op:

- `previous_revision` = current revision;
- `revision` = current revision.

No payload, dependencies, secret data, provider output or raw exception text is returned.

## 13. Failure semantics

Expected failures include:

- invalid input;
- definition not found;
- wrong owner/type;
- Draft/Archived state;
- revision conflict;
- repository persistence conflict/failure.

No failure may partially mutate the definition.

Raw repository exception messages are not part of the public result contract.

No automatic retry is permitted after an ambiguous write attempt.

## 14. Dashboard Widgets compatibility

After implementation, Dashboard Widgets may bind only the Ability's declared input properties:

- `definition_id`
- `expected_revision`
- `enabled`

The current Action-Input Binder remains responsible for current-schema re-resolution and final validation before any later orchestration execution gate.

The later Dashboard action execution path must still independently require:

- authorization;
- explicit confirmation;
- mandatory pre-execution canonical audit;
- owner execution;
- terminal result/audit semantics.

This owner Ability contract does not itself authorize Dashboard Widget execution.

## 15. WordPress bridge boundary

The V1 mutating Ability may be exposed to the WordPress Abilities API only with:

`showInRest=false`

The descriptor itself must not allow `ExecutionChannel::Rest`.

Existing read-only Forms abilities may remain REST-exposed.

The current module helper hardcodes `showInRest=true`, so the later implementation must use a mutation-specific registration path or safely generalize the helper without changing existing read-only behavior.

## 16. Expected later implementation shape

After this contract terminally passes, fresh-main audit may authorize a bounded implementation such as:

1. `frameworks/Modules/FormsWorkflows/FormsWorkflowsSetEnabledAbilityHandler.php` — new
2. `frameworks/Modules/FormsWorkflows/FormsWorkflowsModule.php`
3. focused unit tests for handler/module behavior
4. shared truth files

A separate service class may be added only if exact-main implementation evidence proves it meaningfully narrows responsibilities; scope must be amended before widening.

## 17. Required implementation tests

At minimum:

Authorization/input:
- valid Published definition;
- valid Disabled definition;
- invalid UUID;
- malformed/missing/wrong input type;
- unknown input key;
- missing definition;
- wrong owner/type;
- Draft/Archived denial;
- stale expected revision.

Execution:
- Published -> Disabled increments revision once;
- Disabled -> Published increments revision once;
- same-target request is no-op without save/revision change;
- stale replay after changed mutation fails;
- execution re-read catches post-authorization revision drift;
- payload/dependencies/id/slug/type/schemaVersion/owner/checksum are preserved;
- repository failure does not fabricate success.

Module:
- descriptor owner=17;
- capability=`manage_options`;
- mutates=true;
- channels Internal/UI only;
- non-empty exact input schema;
- `showInRest=false`;
- read-only get/catalog behavior remains unchanged.

Security:
- no REST mutation;
- no entry/submission/run/provider/payment/secret side effect;
- no raw exception/payload leakage.

## 18. This contract tranche scope

This contract PR may change only:

1. `docs/PRODUCT/FORMS-WORKFLOWS-SET-ENABLED-ABILITY-CONTRACT-V1.md`
2. `.ai/state/CURRENT-STATE.yaml`
3. `.ai/state/LAST-CHECKPOINT.md`
4. `README.md`
5. `config/coordination/agent-work-queue.json`
6. `config/coordination/runner-benchmark.json`

No runtime/product PHP/JS/tests are authorized in this contract tranche.

## 19. Promotion boundary

On terminal merge promote only:

`CONTRACT_FROZEN_FORMS_WORKFLOWS_SET_ENABLED_ABILITY_V1`

and:

`READY_FOR_FORMS_WORKFLOWS_SET_ENABLED_ABILITY_V1`

Not promoted:

- Set-Enabled implementation;
- Dashboard Widget action execution;
- trusted `form_action` UI/orchestration;
- entry/submission/run mutation;
- REST mutation;
- full Surface 17 parity;
- deployment, GA or release.
