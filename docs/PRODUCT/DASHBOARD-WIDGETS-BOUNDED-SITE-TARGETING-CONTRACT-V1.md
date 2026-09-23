# Dashboard Widgets — Bounded Site Targeting Contract V1

Status: **implementation-ready contract / direct targeting source gated until merge**
Surface: **10 — Dashboard Widgets**
Issue: **#1195**
Exact base: `main@d57c9439dfd8f6105c3f5a0b90f8cef68e2ac5f4`

## 1. Purpose

Freeze the fail-closed targeting semantics for the canonical P0 family:

- `dashboard-widgets.target.scope`;
- `dashboard-widgets.target.site_ids`;
- existing `dashboard-widgets.target.network_dashboard`;
- normalized atomic owner `dashboard-widgets.targeting.policy`.

This contract does not authorize runtime/product PHP. It defines the later bounded source/test tranche only.

## 2. Canonical payload

The later registration compiler may accept an optional `target` object with exactly:

```text
target.scope            = "all_sites" | "site_ids"
target.site_ids         = list<positive-integer>
target.network_dashboard = boolean
```

Unknown targeting keys remain compiler errors.

## 3. Backwards-compatible defaults

When `target` is absent, or when both `scope` and `site_ids` are absent and `network_dashboard` is false/absent, preserve the already-accepted site Dashboard behavior:

- site Dashboard eligible on the current positive WordPress site id;
- network Dashboard ineligible.

Existing `network_dashboard: true` definitions preserve the accepted network Dashboard path and do not silently become site-targeted definitions.

No default may broaden a network definition onto site Dashboards.

## 4. Scope semantics

`scope = "all_sites"` means the Definition is eligible on any current positive WordPress site id in the active installation/network context.

`scope = "site_ids"` means eligibility requires the current positive WordPress site id to be present in the normalized `site_ids` set.

No other scope value is accepted.

A present `site_ids` list requires `scope = "site_ids"`. `scope = "site_ids"` requires a non-empty `site_ids` list.

`scope = "all_sites"` forbids a non-empty `site_ids` list.

## 5. Site-id validation and normalization

Each authored site id must be an integer value greater than zero. Numeric strings, floats, booleans, nulls, arrays and objects are rejected rather than coerced.

The normalized list:

- contains at most **100** entries;
- is deduplicated by exact integer identity;
- is sorted ascending before descriptor construction;
- remains immutable data after compilation.

More than 100 authored entries fails closed. This bound limits pathological payload and collision-planning cost; it is not a claim about a WordPress installation's maximum site count.

## 6. Site/network mutual exclusion

`network_dashboard = true` is an independent network-admin target.

For V1, a network Dashboard Definition MUST NOT also carry site targeting intent:

- `network_dashboard = true` with `scope` present => reject;
- `network_dashboard = true` with non-empty `site_ids` => reject.

A site-targeted Definition has `network_dashboard = false` after normalization.

No Definition may register into both collision domains from one compiled descriptor.

## 7. Fail-closed malformed combinations

Reject the entire Definition at compile time for:

- unknown target keys;
- unsupported scope;
- `site_ids` without `scope = "site_ids"`;
- empty `site_ids` with `scope = "site_ids"`;
- non-list `site_ids`;
- non-integer, zero or negative ids;
- more than 100 ids;
- `scope = "all_sites"` plus non-empty ids;
- network target combined with site scope/ids.

Compiler rejection remains per-Definition; unexpected compiler/dependency failure retains the existing whole-target zero-side-effect planning abort.

## 8. Current-site eligibility ordering

Site Dashboard planning MUST resolve eligibility before WordPress widget-id collision grouping:

1. compile the Definition through the canonical registration compiler;
2. reject network descriptors from the site target;
3. evaluate the descriptor against the exact current positive site id;
4. discard ineligible descriptors;
5. only then group remaining eligible descriptors by canonical WordPress widget id;
6. suppress every member of an eligible same-id collision group;
7. perform native registration only after the complete eligible plan is normalized.

Therefore two Definitions with the same widget key but disjoint site-id sets do not suppress each other on a site where only one is eligible.

## 9. Network collision domain

The network Dashboard remains independent:

- only `network_dashboard = true` descriptors participate;
- site scope/site ids are absent by contract;
- collision grouping remains isolated from the site Dashboard;
- current site targeting MUST NOT affect network collision decisions.

## 10. Environment dependency

The already-accepted module-local WordPress environment seam remains authoritative for the current site id.

Eligibility evaluation requires a positive current site id. Missing, non-integer, zero/negative, or throwing current-site evidence aborts the site target with zero native registration side effects.

No provider, remote API or persistent preference lookup is introduced.

## 11. Descriptor ownership

The normalized targeting policy belongs to the Dashboard Widgets registration descriptor/compiler boundary. The WordPress adapter consumes only the compiled descriptor; it must not reinterpret raw Definition payload.

A later source tranche may add bounded immutable targeting fields/accessors to the existing descriptor. It must not create a second targeting policy engine.

## 12. Acceptance matrix

Later implementation tests MUST prove at least:

| Scenario | Required behavior |
|---|---|
| target absent | current-site eligible; existing behavior preserved |
| all_sites | eligible on any valid current site |
| site_ids contains current site | eligible |
| site_ids excludes current site | omitted before collision grouping |
| duplicate site ids | normalized to one sorted integer |
| non-positive/non-integer id | compiler rejects Definition |
| >100 site ids | compiler rejects Definition |
| site_ids without site_ids scope | compiler rejects Definition |
| all_sites plus ids | compiler rejects Definition |
| network target plus site scope/ids | compiler rejects Definition |
| same key, disjoint site targets | no false collision on site with one eligible Definition |
| same key, both eligible on same site | all colliders suppressed |
| site and network same key | independent collision domains |
| current-site lookup invalid/throws | zero site-target registration side effects |

## 13. Later authorized source/test scope

After this contract merges and a separately governed source Issue is created, the bounded implementation may touch only the minimum Dashboard Widgets module-local compiler/descriptor/adapter source plus focused unit tests required to implement this contract.

The exact source paths MUST be enumerated by that later Issue before mutation. This contract itself does not pre-authorize shared Platform changes.

## 14. Explicit denials

Not authorized by this contract:

- provider/query/source execution;
- Safe HTTP/remote/iframe execution;
- shortcode/block/action execution;
- asset enqueue/register side effects;
- Dashboard control/settings mutation;
- Definition or user-preference mutation;
- caching/refresh;
- shared Platform source changes;
- full-parity certification;
- deploy/release.

## 15. Promotion verdict

When this contract merges with exact-head Governance Gate and Architecture Guards green, zero unresolved review threads and zero behind, the next transition audit may create the exact bounded source Issue.

Promotion string:

`READY_FOR_BOUNDED_SITE_TARGETING_SOURCE_V1`
