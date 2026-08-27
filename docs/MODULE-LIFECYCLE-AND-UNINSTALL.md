# WPEssential — Module Lifecycle, Disable, Uninstall & Recovery Contract

Status: Phase 0 planning. No runtime implementation authorized.

## Goal
Make module state transitions predictable and prevent data loss, fatal boot loops, silent security weakening, or license-state breakage.

## Module states
Canonical platform states:
- `unavailable`
- `available_disabled`
- `enabled`
- `degraded`
- `read_only`
- `paused`
- `migration_required`
- `unhealthy`

A module may expose a more specific diagnostic reason, but must map to one canonical platform state.

## Enable flow
Before enabling:
1. verify edition/entitlement availability;
2. verify required platform API version;
3. verify hard dependencies;
4. verify required third-party integration version if adapter is mandatory;
5. determine migration requirement;
6. show any high-impact activation consequences;
7. activate registrations/hooks/assets/jobs only after prerequisites pass;
8. record audit/module-health state.

Enabling does not implicitly import legacy data or run destructive migrations without an explicit migration plan.

## Disable flow
Default Disable:
- stops module UI registrations except recovery/read-only surfaces as needed;
- stops new module runtime mutations/jobs;
- unregisters module-specific assets/hooks/abilities where safe;
- preserves definitions/runtime data;
- preserves enough enforcement to avoid security exposure when the module owns access/protection state;
- marks dependent definitions degraded/read-only;
- does not delete tables/options/files;
- does not cascade-disable soft integrations unnecessarily.

### Special security modules
Membership/Protector/protected-file rules cannot simply disappear on accidental disable/license failure if that would expose resources.

Design must support a safe last-known enforcement layer or explicit controlled decommissioning process.

## Re-enable
- validate schema/version before normal runtime resumes;
- resume only module-owned jobs that were paused by module state and are still valid;
- do not replay stale jobs blindly;
- recompile/invalidate caches as required;
- surface migrations/reconciliation before risky runtime activation.

## Pro expiry
Follow ADR-0007:
- preserve data;
- preserve safe deployed output/access enforcement;
- creation/editing can become read-only;
- cost/data-mutating automations may pause;
- export remains available;
- no silent delete/unprotect.

## Plugin deactivation vs module disable
WordPress plugin deactivation is broader than a module toggle.

Free plugin deactivation must not attempt destructive cleanup.
Pro deactivation must not delete data.
Where Pro owns security-sensitive runtime behavior, the product must warn before deactivation and document resulting enforcement behavior. Free/Pro compatibility protocol decides which last-known rules can remain enforced by Free platform code.

## Plugin uninstall
Default uninstall policy should be **preserve user data unless the administrator explicitly opted into destructive cleanup beforehand or uses a dedicated cleanup flow**.

Do not present uninstall-hook deletion as a surprise.

### Cleanup levels
1. **Keep everything** — safest default.
2. **Remove transient/cache/generated artifacts** — no user-authored definitions/runtime business data.
3. **Remove module configuration only** — with dependency preview/export.
4. **Full WPEssential data removal** — destructive, explicit, backup/export recommended, dependency/reference inventory shown.

Full cleanup may include WPE-owned tables/options/generated files but must never delete ordinary WordPress content merely because it had WPE fields/relations/templates associated with it unless explicitly selected and separately confirmed.

## Module data deletion
Before deletion:
- identify owned tables/options/files/definitions/runtime rows;
- identify incoming/outgoing dependencies;
- classify orphaned external data;
- offer export/backup;
- require correct capability;
- re-auth for high-impact modules;
- show irreversible vs recoverable elements;
- execute in bounded/chunked jobs when large;
- record result/failures.

## Definition deletion
Recommended lifecycle:
- Draft can delete if unused.
- Published definition normally archive first.
- Hard-delete requires dependency check and explicit action.
- Stable UUID is never silently reused for another semantic object.
- Referenced deleted definitions surface as missing dependency, not silently remapped.

## Runtime retention
Each module must define retention separately for:
- audit logs;
- workflow/job history;
- provider/webhook events;
- form entries;
- chat messages;
- membership history;
- support/diagnostic records;
- backup catalog.

Retention must account for privacy/legal/business requirements and operational debugging. No universal retention number is assumed in Phase 0.

## Migration failure
On migration failure:
- stop affected module from unsafe writes;
- preserve previous data;
- mark `unhealthy`/`migration_required` with reason;
- do not retry destructively on every request;
- provide diagnostic/retry/restore path;
- avoid fataling entire wp-admin/site when safe degraded boot is possible;
- record migration version and failure metadata.

## Dependency removal
If a hard dependency disappears:
- dependent module becomes degraded/inert;
- no new mutations requiring dependency;
- definitions preserved;
- recovery guidance shown.

If a soft integration disappears:
- only integration-specific behavior degrades;
- base module remains available.

## Third-party plugin deactivation
Adapters must tolerate providers/builders being removed or downgraded:
- unregister adapter safely;
- do not fatal on missing classes/functions;
- preserve mapping definitions;
- mark them inactive/degraded;
- do not delete external-provider identifiers automatically.

## Recovery mode
WPEssential should have a minimal recovery surface that can operate even if an optional module UI fails, allowing authorized administrators to:
- see module health;
- disable problematic optional module;
- inspect migration/dependency error;
- export configuration where possible;
- restore known-safe module state/cache;
- access documentation/support diagnostics.

Recovery surface must avoid loading all optional module bundles.

## Acceptance tests per module
- enable from clean state;
- enable with missing hard dependency;
- disable/re-enable preserves data;
- soft integration missing;
- Pro expiry behavior;
- failed migration safe state;
- plugin deactivation/reactivation;
- uninstall default preserves data;
- explicit cleanup deletes only owned selected data;
- dependent definition degradation;
- security/access module does not accidentally expose protected resource;
- large cleanup interruption/retry where applicable.