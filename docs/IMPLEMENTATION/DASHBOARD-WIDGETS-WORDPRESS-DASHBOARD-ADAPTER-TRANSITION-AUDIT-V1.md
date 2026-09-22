# Dashboard Widgets WordPress Dashboard Adapter Transition Audit V1

Status: `READY_FOR_BOUNDED_WORDPRESS_DASHBOARD_ADAPTER_CONTRACT_V1`

Exact audited main: `cfd2003aaf238f7453c9f8fa429f5103e86fce05`

Source milestone: Issue #1182 / PR #1184

## Terminal source evidence

PR #1184 exact head `9a6b0650a706b14e5ef7bef92da2ac00f5536dcb`:

- Governance Gate `35786157023` — PASS
- Architecture Guards `35786157058` — PASS
- PHP Quality Toolchain `35786157049` — PASS
- Platform Compatibility Matrix `35786157012` — PASS
- Distributable Package `35786157060` — PASS
- Composer advisory audit — PASS
- npm high/critical development and distributable advisory gates — PASS
- zero unresolved review threads
- zero commits behind main
- exactly six authorized source/test files
- merged as `cfd2003aaf238f7453c9f8fa429f5103e86fce05`

## Exact-main capability now present

Surface 10 now has the ordered runtime chain:

`Definition → registration compile → visibility compile/evaluate → trusted render-source compile → shared renderer → six-state module-local runtime result`

The merged implementation proves:

- missing Definition short-circuits;
- compiler rejection fails closed;
- visibility denial prevents renderer invocation;
- unexpected runtime exceptions are absorbed without raw detail leakage;
- shared renderer failures preserve only bounded `RenderFailureCode`;
- exact incoming `ExecutionContext` is forwarded unchanged;
- asset handles are returned as data only;
- no WordPress Dashboard hook, provider/source execution, mutation or asset side effect was added.

The trusted component renderer escapes authored scalar text with `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')` and accepts only bounded relative or HTTPS links.

## Remaining WordPress-specific gap

Directly calling `wp_add_dashboard_widget` is still unsafe without a frozen adapter policy.

The adapter boundary must settle:

1. normal vs network Dashboard target isolation;
2. deterministic Definition catalog ordering before registration;
3. catalog-level duplicate widget-key / WordPress-id collision handling before any registration side effect;
4. a fixed WPEssential WordPress widget-id namespace;
5. a fresh current-request `ExecutionContext` using `ExecutionChannel::Ui`;
6. six-state callback result mapping;
7. trusted rendered HTML as the only callback body;
8. ignored/data-only asset handles in V1;
9. hook/API/dependency exception containment;
10. zero control callback, mutation, provider/source execution, remote content, caching/refresh or shared Platform widening.

### ExecutionContext finding

The existing `WordPressExecutionContextFactory` is Ability-oriented. Ordinary non-REST/non-CLI requests are classified as `ExecutionChannel::Internal`.

A Dashboard callback is a UI execution boundary. The later adapter implementation must therefore create an exact current-user/current-site/network `ExecutionContext` with `ExecutionChannel::Ui`; it must not silently reuse the Ability factory's ordinary-request `Internal` classification.

### Collision finding

The existing registration compiler validates one Definition at a time. It does not prove uniqueness across the whole Dashboard Widget catalog.

A WordPress adapter that registers as it iterates can create first-wins/last-wins collisions. V1 must instead perform a two-phase plan: compile and normalize the complete target catalog, detect colliding WordPress-facing ids, then perform registration only for a collision-free plan.

## Verdict

- `READY_FOR_BOUNDED_WORDPRESS_DASHBOARD_ADAPTER_CONTRACT_V1`
- `BLOCKED_FOR_DIRECT_WORDPRESS_DASHBOARD_REGISTRATION_IMPLEMENTATION`
- `BLOCKED_FOR_WP_ADD_DASHBOARD_WIDGET` until the adapter contract merges
- provider/query/source/remote/iframe/shortcode/block/action execution remains blocked
- asset enqueue/register side effects remain blocked
- Definition/user-preference mutation and caching/refresh remain blocked
- full-parity certification/deploy/release remain blocked

## Next contract tranche

Issue #1186 is the only next bounded Surface 10 slot.

It must freeze deterministic pre-registration planning/collision handling, normal/network target isolation, WordPress-facing id derivation, UI execution context semantics, six-state callback mapping, exception containment and zero-asset/zero-mutation behavior before any runtime hook implementation may be claimed.
