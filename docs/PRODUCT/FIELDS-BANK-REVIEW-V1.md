# Fields / Field Groups — Formal Bank Review V1

Snapshot: 2026-09-01

Surface: 3 — Fields / Field Groups / Control Registry

Decision: **BLOCKED FOR STATUS PROMOTION**

Lifecycle status remains: `BANK_SURFACE_SEEDED`

This review follows the 596-record Fields discovery Bank merged through PRs #17 and #18. Its purpose is to decide whether the surface can honestly advance to `NATIVE_AUDITED`, `MARKET_AUDITED`, or `BANK_REVIEWED` under the binding competitor-parity charter. It is not an implementation or runtime-certification artifact.

## 1. Machine integrity result

The exact-head CI used by PR #18 ran the repository's Options Bank contracts successfully before merge.

`tests/Smoke/options-bank-contract.php` enforces, across every shard in a surface:

- unique record IDs;
- unique `option_path` values;
- valid surface id/key ownership;
- valid classifications, hard/soft mode, horizon, adoption, and priority enums;
- valid cross-shard `parent_id` references;
- declared record/unreviewed/classified counts matching actual contents.

`tests/Smoke/options-bank-progress-contract.php` additionally enforces:

- exactly 56 canonical progress rows;
- declared per-surface record counts equal actual shard records;
- `UNSEEDED` surfaces cannot contain records;
- lifecycle truth counters match actual statuses and records.

Therefore this review treats **exact duplicate IDs/paths and count drift as closed machine-integrity concerns for the certified head**. The remaining review risk is semantic duplication, native completeness, market completeness, and unresolved ownership/mapping.

## 2. Semantic consolidation blockers

The current Bank intentionally grew through multiple discovery waves. Machine-unique paths do not guarantee unique product meaning. The following pairs/families require an explicit canonicalization decision before `BANK_REVIEWED` is possible.

| Candidate | Related record | Review finding | Required decision |
| --- | --- | --- | --- |
| `fields.field.required` / `field.required` | `fields.behavior.validation_required` / `validation.required` | Same user-facing required constraint is represented at two abstraction levels. | Choose one canonical Option Contract and treat the other as a mapping/alias, or document a provable scope distinction. |
| `fields.field.cloneable` / `field.cloneable` | `fields.behavior.repeat_clone` / `behavior.clone.enabled` | Both describe enabling multiple/clone values. | Prefer one canonical repeatability primitive; richer clone behavior can hang from that primitive. |
| `fields.field.sortable` / `field.sortable` | `fields.behavior.repeat_sort` / `behavior.clone.sortable` | Both describe sorting repeated/clone values. | Consolidate or define a strict scope distinction. |
| `fields.field.rest_schema` / `field.rest_schema` | `fields.behavior.rest_schema` / `rest.schema` | Both describe a field/meta REST schema. | Decide whether one is authored configuration and the other is resolved/effective schema; otherwise consolidate. |
| `fields.field.escape_html` / `field.escape_html` | `fields.behavior.value_escape` / `value.escape` | Both describe output escaping. | Split configuration policy from resolved pipeline stage, or consolidate. |
| `fields.field.format_value` / `field.format_value` | `fields.behavior.value_formatted` / `value.formatted` | Toggle/policy vs resolved-value semantics are not currently explicit enough. | Clarify descriptor semantics and prevent two UI controls for one behavior. |

These are **semantic review blockers**, not proof that records should be deleted blindly. No production contract should consume both sides independently until the canonical meaning is resolved.

## 3. Layered relationships that should remain distinct

The following are close in language but represent different architectural layers. They should be cross-referenced rather than collapsed:

- `group.revision_policy` → group-level product policy;
- `storage.revisions` → storage behavior/capability;
- `native_meta.revisions_enabled` → concrete WordPress metadata primitive.

Likewise:

- `block_binding.enabled` is field-level eligibility, while `block_binding.source.*` describes source registration/editor contracts;
- `storage.custom_table` and `group.storage.custom_table` choose storage, while `custom_table.*` describes physical table/data-object semantics;
- `value_scope.*` describes generic global/local value behavior, while `network_scope.*` is multisite/network-specific;
- `performance.field_index_policy` is a per-field policy, while `custom_table.index_keys` is physical schema/index definition.

Implementation planning must preserve these layers without presenting duplicate controls for the same effective value.

## 4. Native WordPress completeness review

The binding charter requires every relevant public/supported native argument, lifecycle state, permission, REST behavior, storage behavior, and integration point to be classified; silent omission is not allowed.

### `register_meta()` mapping

Current WordPress documentation exposes these material arguments/behaviors: `object_subtype`, `type`, `label`, `description`, `single`, `default`, `sanitize_callback`, `auth_callback`, `show_in_rest` (including schema/prepare callback), and `revisions_enabled`.

The Bank now captures several native primitives directly, including object subtype, REST array-item schema, prepare-value provider, revisions, and the CPT `custom-fields` support dependency. Other native arguments appear to map onto broader WPE records (`field.type`, `field.label`, `field.default`, `storage.single`/`storage.multiple`, sanitization, REST exposure), but that mapping is not yet an explicit audited matrix.

Two areas remain especially ambiguous:

1. WordPress meta `description` is API/registered-meta metadata and is not necessarily identical to editor `field.instructions`.
2. WordPress `auth_callback` covers metadata capability checks; the current `rest.auth` / field access records do not yet prove one canonical provider-safe mapping for that native behavior.

**Required before `NATIVE_AUDITED`:** publish a one-row-per-native-argument disposition matrix (`canonical Bank path`, `provider mapping`, `not applicable`, or `rejected/replaced`) so no native argument is silently inferred.

Primary reference: https://developer.wordpress.org/reference/functions/register_meta/

### Block Bindings mapping

The current Bank captures server registration, get-value provider, `uses_context`, editor registration, `getFieldsList`, get/set values, edit authorization, attribute/value compatibility, `core/post-meta`, protected-key restrictions, and REST-registration requirements.

Current WordPress Block Bindings source registration also has an explicit namespaced source identity and label. Those are currently implicit inside the broad `server_registration` / `editor_registration` records rather than atomic Bank paths. The review also needs an ownership decision for supported-attribute filters and built-in non-field sources (`core/post-data`, term data): Fields integration vs Block/Platform surface.

**Required before `NATIVE_AUDITED`:** resolve source identity/label atomicity and classify adjacent native Block Bindings hooks/sources as Fields-owned or explicitly out-of-surface.

Primary references:

- https://developer.wordpress.org/reference/functions/register_block_bindings_source/
- https://developer.wordpress.org/block-editor/reference-guides/block-api/block-bindings/
- https://developer.wordpress.org/block-editor/reference-guides/packages/packages-blocks/

## 5. Market completeness review

The 14-shard Fields Bank now has broad evidence from ACF/SCF, Meta Box, JetEngine, Redux, Pods, CMB2, Carbon Fields, Fieldmanager, Toolset/ACPT-style ecosystems, specialist field builders, WPGraphQL for ACF, custom-table/data-object systems, and long-tail editor/media/relationship patterns.

That breadth is useful discovery evidence, but `MARKET_AUDITED` means the relevant competitor/specialist possibilities were exhaustively audited and classified with evidence. The current evidence is mostly shard/source-oriented, not a provider-by-provider capability coverage matrix proving that each accepted benchmark family has zero unexplained gaps.

WPGraphQL for ACF mutations are correctly retained as `DEFERRED` / `WPE_FUTURE`: current provider documentation states GraphQL mutations are not yet supported. This is an example of the evidence standard the rest of the market audit should follow.

Primary reference: https://acf.wpgraphql.com/

**Required before `MARKET_AUDITED`:** create a benchmark coverage matrix that maps each material capability family from each accepted benchmark to an existing Bank record, a deliberate rejection/replacement, a deferred item with reason, or a documented not-applicable disposition.

## 6. Status decision

| Gate | Result | Reason |
| --- | --- | --- |
| Machine Bank integrity | PASS | Certified exact-head CI enforces IDs, paths, parents, enums, per-shard counts and progress truth. |
| Semantic deduplication | BLOCKED | At least six cross-wave record families need canonical meaning/alias decisions. |
| Native inventory completeness | BLOCKED | `register_meta()` disposition mapping is incomplete; Block Bindings source identity/ownership edges remain implicit. |
| Market inventory completeness | BLOCKED | Broad evidence exists, but no exhaustive provider capability-to-record matrix yet proves zero unexplained gaps. |
| WPE exceed inventory | SEEDED | Strong diagnostics, governance, safe providers, migration, performance and network exceed ideas exist, but final review depends on the canonicalized base. |
| `NATIVE_AUDITED` | **NO** | Native silent-omission gate not yet closed. |
| `MARKET_AUDITED` | **NO** | Market evidence matrix not yet exhaustive. |
| `BANK_REVIEWED` | **NO** | Semantic and completeness blockers remain. |

Surface 3 must remain `BANK_SURFACE_SEEDED`.

## 7. Corrective order

1. Resolve the six semantic consolidation families and record canonical/alias semantics.
2. Build the explicit `register_meta()` native-argument disposition matrix.
3. Resolve Block Bindings source identity/label atomicity and adjacent-source ownership.
4. Build the provider-by-provider market capability coverage matrix.
5. Add only genuinely missing records discovered by those matrices; do not inflate the Bank with aliases.
6. Re-run exact-head Options Bank contracts and the full applicable CI matrix.
7. Re-run this formal gate; promote lifecycle status only when evidence, not record count, supports it.

## 8. Non-claims

- 596 records is not a completeness certificate.
- Green CI is not a native/market audit certificate.
- No runtime behavior is changed by this review.
- No semantic candidate is deleted until its canonical contract is defined.
- No competitor implementation source is copied.
