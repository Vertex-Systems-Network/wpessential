# WPEssential — WP Reset Parity Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `RSX-001…RSX-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- Reset is destructive operational tooling; it is not backup, migration, or guaranteed recovery.
- Snapshot existence is not recovery proof; recovery requires verified capture, integrity and restore evidence.
- Full/partial reset semantics are explicit and bounded; success cannot be reported while required cleanup/verification is unknown.
- WordPress user/admin/auth recovery must remain available according to reset profile; reset cannot silently lock out all authorized administrators.
- Filesystem/.htaccess/theme/plugin actions are separate from database reset and must be independently scoped/verified.
- Multisite reset must preserve network/site authority and cannot let a site admin reset network-global state.
- AI/MCP may draft/reset-plan/explain only; no destructive execution without explicit high-risk approval and re-auth.
- Unknown external/provider side effects cannot be “reset” by local DB operations and remain reconciled separately.

## Exact fixtures

### Group 1 — snapshot identity
- `RSX-001` Create snapshot definition with stable ID, scope, components, source environment identity, creation time, checksum manifest and retention policy.
- `RSX-002` Snapshot ID is unique within installation/site namespace and cannot collide by filename alone.
- `RSX-003` Snapshot records exact component coverage so omitted files/tables are never implied recoverable.
- `RSX-004` Snapshot metadata distinguishes requested, created, verified, degraded and failed states.
- `RSX-005` Snapshot created under one site/network scope cannot be restored elsewhere by guessed ID without explicit mapping.
- `RSX-006` Snapshot revision/manifest is immutable after verified completion; changes produce a new snapshot identity.
- `RSX-007` Partial/incomplete snapshot cannot be labeled verified recovery point.
- `RSX-008` Snapshot export excludes secrets unless an approved encrypted secret profile explicitly includes them.
- `RSX-009` Capability/Policy denial blocks snapshot create/delete/restore even if admin UI is visible.
- `RSX-010` AI/MCP may draft snapshot plan but cannot create/delete/restore runtime snapshot without same approval gate.
- `RSX-011` Unknown snapshot schema/version fails typed or migrates explicitly; no silent reinterpretation.

### Group 2 — DB snapshot capture
- `RSX-012` Capture selected WordPress tables with schema/row-count/checksum metadata under declared consistency profile.
- `RSX-013` Site-only DB snapshot excludes unrelated Multisite tables/sites.
- `RSX-014` Network snapshot explicitly includes network tables and selected site tables.
- `RSX-015` Large tables stream/batch data rather than load entirely into memory.
- `RSX-016` Capture failure on one required table marks snapshot degraded/failed and not restorable-complete.
- `RSX-017` Table prefix/schema metadata is recorded for restore mapping.
- `RSX-018` BLOB/binary data is preserved byte-accurately or explicitly unsupported; no text coercion.
- `RSX-019` Snapshot does not execute serialized objects/PHP while reading stored data.
- `RSX-020` DB credentials remain Vault-owned and absent from snapshot payload/logs.
- `RSX-021` Snapshot consistency caveat is recorded when source changes during non-transactional capture.
- `RSX-022` Verified capture compares manifest counts/checksums according to selected profile before declaring success.

### Group 3 — snapshot compare
- `RSX-023` Compare current DB schema/table inventory against snapshot and classify added/removed/changed tables.
- `RSX-024` Compare selected row counts/checksums without exposing raw protected values in summary.
- `RSX-025` Compare WordPress options/settings with redaction for secrets/tokens.
- `RSX-026` Compare content counts/status categories while distinguishing source truth from snapshot representation.
- `RSX-027` Compare filesystem component manifest separately from DB snapshot coverage.
- `RSX-028` Missing snapshot component is labeled not-captured, not “unchanged”.
- `RSX-029` Large diff is paginated/bounded and does not materialize full database in UI memory.
- `RSX-030` Compare result uses snapshot/current timestamps and does not imply causal explanation for differences.
- `RSX-031` Cross-environment compare identifies environment mismatch explicitly.
- `RSX-032` Viewer cannot inspect protected diff details without applicable Policy.
- `RSX-033` AI/MCP may summarize authorized compare evidence but cannot infer destructive reset recommendation as automatic action.

### Group 4 — snapshot restore/export
- `RSX-034` Restore plan validates snapshot integrity/version/coverage before any destructive write.
- `RSX-035` Restore creates or verifies a pre-restore recovery point when profile requires rollback safety.
- `RSX-036` Table restore maps source→target prefix/schema explicitly.
- `RSX-037` Restore of selected tables leaves unselected target tables unchanged.
- `RSX-038` Restore failure mid-run records partial/recovery-required state and never claims success.
- `RSX-039` Post-restore verification checks required table/schema/count/checksum invariants.
- `RSX-040` Snapshot export package protects traversal/path issues and includes manifest/integrity metadata.
- `RSX-041` Protected snapshot download is access-controlled and expiring where applicable.
- `RSX-042` Snapshot import remains inactive/unrestored until compatibility and destination checks pass.
- `RSX-043` External provider/webhook/payment state cannot be rolled back by DB snapshot and is reported separately.
- `RSX-044` Restore/rollback events are audited with actor/scope/outcome without dumping protected DB payload.

### Group 5 — full DB reset
- `RSX-045` Full DB reset plan explicitly enumerates tables/options/users/content/custom tables that will be removed/reinitialized.
- `RSX-046` High-risk confirmation requires exact scope/re-auth/approval according to policy and cannot be bypassed by UI parameter.
- `RSX-047` Required recovery admin/account strategy is validated before destructive reset begins.
- `RSX-048` Reset does not delete database/schema outside registered WordPress installation scope.
- `RSX-049` Multisite full-network reset requires network authority and cannot run from ordinary site admin.
- `RSX-050` Reset recreates only documented baseline/core-required data and does not invent plugin defaults as canonical unless owner adapter defines them.
- `RSX-051` Crash during reset enters recovery-required state and exposes verified recovery route.
- `RSX-052` Completed reset verifies expected fresh-state invariants before success status.
- `RSX-053` Reset does not claim external email/payment/search/provider data was erased.
- `RSX-054` Audit/log retains bounded reset evidence under retention policy even if ordinary site data is removed.
- `RSX-055` AI/MCP cannot execute full reset or reduce confirmation requirements under free-text instruction.

### Group 6 — partial content/settings reset
- `RSX-056` Reset posts/pages/CPT content only while preserving users/settings when selected profile says so.
- `RSX-057` Reset comments only without deleting posts or unrelated metadata.
- `RSX-058` Reset selected plugin/module settings through owning adapter and not arbitrary option-name wildcard.
- `RSX-059` Reset theme/plugin configuration distinguishes site options, network options and user meta.
- `RSX-060` Reset taxonomies/terms handles relationships explicitly and does not leave silent orphan links.
- `RSX-061` Reset selected WPE module data respects module lifecycle/uninstall ownership contracts.
- `RSX-062` Protected/legal-hold/retained data is excluded when Privacy/Policy says destructive reset is not allowed.
- `RSX-063` Partial reset preview reports affected counts/components and unknown provider-owned data.
- `RSX-064` Unsupported component selector is rejected instead of silently skipped.
- `RSX-065` Partial reset failure reports per-component outcome and leaves remaining untouched components explicit.
- `RSX-066` Post-reset verification confirms selected scope only and not whole-site freshness unless full reset profile ran.

### Group 7 — transients/uploads
- `RSX-067` Clear site transients only and preserve network transients when site-scoped profile is selected.
- `RSX-068` Clear network transients only with network authority.
- `RSX-069` Object-cache flush is separate from DB transient deletion and is adapter/provider scoped.
- `RSX-070` Uploads cleanup requires explicit path/attachment ownership and cannot delete arbitrary filesystem tree.
- `RSX-071` Orphaned upload candidate is not deleted solely because no current post reference was found.
- `RSX-072` Protected/private media follows Media/Privacy retention rules during cleanup.
- `RSX-073` Offloaded media deletion uses storage-provider owner and unknown outcome remains reconciliation-required.
- `RSX-074` Upload reset preview distinguishes DB attachment deletion from binary-file deletion.
- `RSX-075` Filesystem symlink/traversal protection keeps deletion inside approved uploads root.
- `RSX-076` Cache/CDN purge after media reset is separate provider outcome and not assumed successful from local deletion.
- `RSX-077` Reset completion reports local/remote media cleanup states separately.

### Group 8 — theme mods/themes/plugins
- `RSX-078` Reset theme mods for active theme only without deleting theme source files unless explicit separate action selected.
- `RSX-079` Switch to default/recovery theme is explicit activation action and requires applicable capability.
- `RSX-080` Plugin deactivate-all action is separate from uninstall/delete and preserves plugin files/data unless configured otherwise.
- `RSX-081` Network-active plugins require Network Admin scope and are not deactivated by site reset.
- `RSX-082` Must-use plugins/drop-ins are not removed through ordinary plugin reset profile.
- `RSX-083` Theme/plugin deletion verifies path ownership and cannot traverse arbitrary directories.
- `RSX-084` Dependency plugins/themes are analyzed before delete and blockers are reported.
- `RSX-085` Reset plugin options delegates to owner profiles where available and avoids blind wildcard option deletion.
- `RSX-086` Failed theme/plugin removal leaves exact component state and recovery action visible.
- `RSX-087` Recovery mode remains accessible when active theme/plugin reset causes runtime failure.
- `RSX-088` AI/MCP cannot turn presentation cleanup request into plugin/theme deletion automatically.

### Group 9 — custom tables
- `RSX-089` Inventory registered custom tables and owning modules before reset plan.
- `RSX-090` Reset selected custom table data only when owner/schema explicitly declares destructive reset semantics.
- `RSX-091` Unknown table is not dropped merely because it is non-Core/custom.
- `RSX-092` Drop table requires higher destructive scope than truncate/clear rows where applicable.
- `RSX-093` Foreign-key/dependency graph is checked before table deletion/truncation.
- `RSX-094` Shared/network custom table is not reset from site-only context unless owner supports scoped rows.
- `RSX-095` Custom table with legal-hold/retention data blocks incompatible destructive action.
- `RSX-096` Table reset uses typed registered identifiers and not raw admin-supplied SQL/table names.
- `RSX-097` Failed DDL/data reset yields explicit partial state and does not claim table removed.
- `RSX-098` Snapshot/restore coverage for custom table is verified before relying on it as recovery path.
- `RSX-099` Post-reset schema/data verification uses owner contract and reports unsupported verification honestly.

### Group 10 — filesystem/.htaccess actions
- `RSX-100` Filesystem cleanup target is restricted to registered WordPress roots/subpaths and rejects traversal/absolute foreign paths.
- `RSX-101` `.htaccess` reset uses declared WordPress baseline/profile and creates recovery copy when required.
- `RSX-102` nginx/server config is not modified through `.htaccess` reset path; unsupported server is reported.
- `RSX-103` wp-config.php is never rewritten by ordinary reset profile without a separate reviewed high-risk operation.
- `RSX-104` Cache/temp generated directories can be cleared only through allowlisted paths/owner adapters.
- `RSX-105` Symlink handling prevents deletion outside allowed root.
- `RSX-106` File permissions/ownership failure is reported and does not mark cleanup complete.
- `RSX-107` Immutable/read-only filesystem produces explicit unsupported/degraded state.
- `RSX-108` Deleting plugin/theme/upload files is distinct from corresponding DB row reset and each outcome is tracked.
- `RSX-109` Backup snapshot coverage includes any filesystem component required for claimed rollback.
- `RSX-110` Arbitrary shell command/file glob input is not accepted as reset operation.

### Group 11 — recovery admin/re-auth
- `RSX-111` Destructive reset requires re-auth according to security policy and cannot rely on stale long-lived admin page session alone.
- `RSX-112` Recovery admin identity is validated before full reset and remains able to log in afterward under declared profile.
- `RSX-113` Multisite recovery principal uses Super Admin/network recovery semantics, not ordinary site role checkbox.
- `RSX-114` Recovery credentials/tokens are never written into reset logs/snapshots.
- `RSX-115` Reset that would delete all administrators is blocked unless explicit supported recovery bootstrap is verified.
- `RSX-116` Lost-password/reset route remains available after reset unless auth owner explicitly says otherwise.
- `RSX-117` Account enumeration-safe behavior remains intact during recovery flow.
- `RSX-118` Recovery token/artifact is single-use/expiring if such profile is used.
- `RSX-119` Re-auth failure stops destructive action before writes.
- `RSX-120` AI/MCP cannot satisfy human re-auth/approval requirement by self-asserted identity.
- `RSX-121` Post-reset recovery verification confirms authorized admin login path without altering broader account policy.

### Group 12 — Backup integration
- `RSX-122` Pre-reset backup request delegates to Backup owner and reset waits for verified required recovery-point state, not “job queued”.
- `RSX-123` Backup failure/unknown provider outcome blocks destructive reset when recovery point is mandatory.
- `RSX-124` Reset records backup snapshot/manifest reference used for recovery planning.
- `RSX-125` Backup retention/delete remains Backup owner and is not coupled silently to reset completion.
- `RSX-126` Local DB snapshot ≠ full backup; UI/report states exact coverage.
- `RSX-127` External provider backup cannot be assumed restorable without provider certification.
- `RSX-128` Restore after reset follows Backup/RSX restore contract and does not infer external provider side-effect rollback.
- `RSX-129` Staging/Migration clone is not accepted as backup replacement unless explicit recovery evidence says so.
- `RSX-130` Encrypted backup requires key/recovery material availability verification before destructive reset.
- `RSX-131` Cross-site/network backup scope must match reset scope or reset is blocked/warned per policy.
- `RSX-132` AI/MCP cannot bypass required backup gate or delete recovery point to force reset.

### Group 13 — WP-CLI/Abilities
- `RSX-133` CLI reset plan/preview matches admin/API normalized scope for same inputs.
- `RSX-134` Destructive CLI reset requires explicit flags/approval/re-auth profile and cannot rely on hidden browser confirmation.
- `RSX-135` Ability endpoint exposes typed reset actions and rejects arbitrary SQL/shell/path payloads.
- `RSX-136` CLI output redacts secrets and protected snapshot data.
- `RSX-137` Exit code distinguishes validation block, partial/unknown, rollback-required and verified success.
- `RSX-138` CLI cannot target foreign site/network scope without server-resolved authorization.
- `RSX-139` Ability/MCP status read can be allowed while destructive run remains denied.
- `RSX-140` Deprecated command version fails with guidance and not altered semantics silently.
- `RSX-141` CLI retry uses reset run identity and does not repeat destructive steps already verified complete blindly.
- `RSX-142` Audit provenance records CLI/Ability principal separately from affected admin/recovery principal.
- `RSX-143` AI/MCP may draft CLI command but cannot execute destructive reset in planner-only mode.

### Group 14 — Multisite safety
- `RSX-144` Site reset removes only selected site tables/options/uploads and preserves other sites/network data.
- `RSX-145` Full network reset requires explicit Network Admin/Super Admin authority and broader approval.
- `RSX-146` Network users remain network-global; site reset removes site membership/content only according to profile.
- `RSX-147` Super Admin status is not removed through site reset.
- `RSX-148` Site ID/prefix is server-resolved and cannot target another site by request parameter.
- `RSX-149` Network-shared plugin/theme files are not deleted by site-only reset.
- `RSX-150` Site-specific uploads path cleanup cannot traverse into another site’s directory.
- `RSX-151` Site clone/snapshot identity does not authorize reset of source site.
- `RSX-152` Network aggregate reset preview reports affected sites/components without exposing protected site data beyond authority.
- `RSX-153` Site deletion vs reset are distinct lifecycle operations and not conflated in status/audit.
- `RSX-154` AI/MCP site context cannot escalate to network reset by specifying network scope in prompt.

### Group 15 — large DB/reset performance
- `RSX-155` 1M-row table snapshot/reset uses bounded batching/streaming and records memory/time/lock metrics.
- `RSX-156` Large Multisite reset processes sites fairly and does not load all site tables/data simultaneously.
- `RSX-157` Retention/custom-table dependency scan avoids unbounded metadata queries beyond declared budget.
- `RSX-158` Deletion/truncate strategy records lock/downtime characteristics for selected DB engine/profile.
- `RSX-159` Filesystem cleanup of large uploads uses bounded traversal and symlink-safe path checks.
- `RSX-160` Backup-before-reset performance budget is measured separately from reset operation itself.
- `RSX-161` Retry/recovery does not re-run verified destructive steps unnecessarily.
- `RSX-162` Progress status distinguishes estimated vs known work totals.
- `RSX-163` Job cancellation/restart uses persisted journal and avoids ambiguous repeated deletion.
- `RSX-164` Performance evidence records DB/filesystem/environment/scope for reproducibility.
- `RSX-165` Static estimates do not certify large-reset performance.

### Group 16 — destructive-run recovery regression
- `RSX-166` Golden: create verified DB snapshot, reset selected content only, verify untouched users/settings, then restore selected content successfully.
- `RSX-167` Golden: full reset preserves/creates declared recovery admin path and verifies post-reset login.
- `RSX-168` Golden: reset blocked when required backup is queued/failed/unknown rather than verified.
- `RSX-169` Golden: crash mid-reset resumes/reports from journal and does not claim success until verification.
- `RSX-170` Golden: uploads/offload cleanup reports local vs remote provider states separately and protects private media.
- `RSX-171` Golden: Multisite site reset leaves other sites/network/Super Admin state intact.
- `RSX-172` Golden: custom table with unknown owner is preserved/blocker instead of dropped automatically.
- `RSX-173` Golden: `.htaccess` reset creates/verifies recovery copy and never touches nginx/wp-config paths.
- `RSX-174` Golden: plugin/theme reset failure falls back to recovery path without deleting unrelated shared source.
- `RSX-175` Golden: rollback restores only covered local state and explicitly reports external provider side effects unreverted.
- `RSX-176` Golden: AI/MCP adversarial request for full/network reset, backup bypass, secret deletion or arbitrary shell/path action is denied/draft-only.

## Runtime truth

This protocol is documentation-only. `RSX-001…RSX-176` are **176/176 documented, 0/176 executed**. No snapshot, reset, DB/filesystem/theme/plugin mutation, backup/restore, login recovery, CLI/Ability or AI/MCP runtime action occurred. Development authorization remains **NOT GRANTED / 0/56**.