# WPEssential — WP Migrate Parity Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `MIG-001…MIG-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- Migration is controlled source→target state transfer; it is not backup, merge, or proof of semantic equivalence by itself.
- Export/import success does not prove target application correctness; verification is a separate required phase.
- Push/pull authentication authorizes a migration channel only; it does not grant arbitrary target data authority.
- Serialized search/replace must preserve data structure and lengths; raw string replacement is not acceptable where serialization semantics apply.
- A migration cursor/checkpoint is progress state, not proof every item was applied successfully.
- Clone/migration must not preserve production secrets, webhooks, scheduled side effects, environment identity, or provider authority blindly.
- Unknown remote write outcome is `unknown/reconcile_required`, not automatically failed.
- Multisite mapping is explicit; site/network identifiers are not inferred merely from matching numeric IDs or domains.

## Exact fixtures

### Group 1 — migration profile schema
- `MIG-001` Create a migration profile with stable ID, source, destination, direction, selected components, transform rules, verification profile and environment safety policy.
- `MIG-002` Reject a profile whose source and destination resolve to the same protected environment when self-migration is not explicitly allowed.
- `MIG-003` Reject unknown component selector rather than silently skipping it.
- `MIG-004` Version a migration profile update and preserve previous profile revision for audit/replay explanation.
- `MIG-005` Stale profile revision update fails with diff instead of overwriting a newer migration definition.
- `MIG-006` Export/import of profile excludes credentials/tokens and replaces them with Vault/provider references.
- `MIG-007` Environment class production/staging/local/custom is explicit and not derived solely from hostname string matching.
- `MIG-008` Read-only inspection profile can inventory impact without enabling write endpoints.
- `MIG-009` Capability/Policy denial blocks profile creation/edit/run even if UI entry is visible.
- `MIG-010` AI/MCP may draft a migration profile but cannot pair environments or run it without normal Policy/approval.
- `MIG-011` Unknown future profile version fails typed or migrates through explicit schema adapter; no silent reinterpretation.

### Group 2 — DB export/import
- `MIG-012` Export selected database tables with stable manifest, schema metadata, row counts/checksums where applicable and source revision/time metadata.
- `MIG-013` Import verifies package/manifest integrity before creating or replacing target database state.
- `MIG-014` Table selection excludes unselected tables and verifies they remain unchanged after import.
- `MIG-015` Prefix difference between source and target is mapped explicitly rather than assumed identical.
- `MIG-016` Character set/collation differences are detected and handled by declared compatibility policy before writes.
- `MIG-017` Large BLOB/text values are streamed/bounded rather than loaded unbounded into memory.
- `MIG-018` Import row failure records item/table error and prevents false whole-run success.
- `MIG-019` Duplicate-key conflict follows explicit replace/merge/skip/fail semantics; destructive replace is never labeled merge.
- `MIG-020` Database credentials remain Vault-owned and never enter export archives/logs.
- `MIG-021` Target schema incompatibility blocks affected import before destructive table swap where safe.
- `MIG-022` Post-import table counts/checksums are compared using the declared verification profile; transport success alone is insufficient.

### Group 3 — full-site export
- `MIG-023` Full-site package manifest explicitly enumerates database, uploads, themes, plugins and other selected wp-content paths.
- `MIG-024` Core WordPress files are excluded or included only under an explicit supported full-stack profile; default migration does not silently overwrite Core.
- `MIG-025` Package records source WordPress/PHP/plugin/theme versions needed for compatibility assessment.
- `MIG-026` Files outside approved roots are rejected from package to prevent arbitrary filesystem export.
- `MIG-027` Symlink handling is explicit and prevents traversal outside allowed roots.
- `MIG-028` Archive paths are normalized and reject `../`, absolute paths and device/special-file entries.
- `MIG-029` Very large media files use streamed chunks and checksum verification.
- `MIG-030` Package generation failure leaves no “complete” artifact and cleans or quarantines partial output.
- `MIG-031` Full-site export does not include runtime secrets/config values unless an explicitly approved secret-migration profile exists.
- `MIG-032` Export manifest distinguishes included, excluded, failed and unavailable components.
- `MIG-033` Restoring/importing a full-site package still requires destination environment and side-effect safety checks before activation.

### Group 4 — push/pull pairing/auth
- `MIG-034` Pair source and destination through an authenticated one-time/short-lived pairing flow and store long-lived credentials only in Vault if required.
- `MIG-035` Pairing response is enumeration-safe and does not reveal protected installation details before authentication.
- `MIG-036` Pairing token is single-use/expiring and replay after successful consume is rejected.
- `MIG-037` Destination identity fingerprint is recorded so a changed/replaced remote endpoint requires re-pairing or explicit trust update.
- `MIG-038` Pull operation authorizes both source read scope and local destination write scope independently.
- `MIG-039` Push operation authorizes local source read and remote destination write independently.
- `MIG-040` Request-supplied site/tenant identifiers do not grant remote scope; paired server-side connection owns the scope.
- `MIG-041` TLS/certificate/host verification failure stops connection unless an explicit test-only profile exists; production trust is not silently weakened.
- `MIG-042` Credential rotation/revocation invalidates future migration actions without corrupting historical run records.
- `MIG-043` Failed authentication is rate-limited and logged without storing secret/token material.
- `MIG-044` AI/MCP cannot reveal pairing secrets or silently establish a new migration trust relationship.

### Group 5 — serialized find/replace
- `MIG-045` Replace source URL with target URL inside PHP serialized strings while recalculating string lengths correctly.
- `MIG-046` Replace nested serialized arrays/objects without changing unrelated keys/values.
- `MIG-047` JSON payloads are parsed/re-encoded through JSON semantics where configured instead of raw blind replacement.
- `MIG-048` Plain text values use bounded replacement rules and respect case/regex mode explicitly.
- `MIG-049` Binary/BLOB fields are excluded by default unless a certified transformer exists.
- `MIG-050` Serialized object containing unavailable class is transformed without object instantiation/code execution.
- `MIG-051` Malformed serialized value is reported and left unchanged or quarantined according to policy; it is not silently corrupted.
- `MIG-052` Replacement preview shows counts/table/column examples using redacted values where data is sensitive.
- `MIG-053` Search/replace cannot rewrite secrets/hashed credentials unless an explicit typed rule targets them and policy allows it.
- `MIG-054` Replacement order is deterministic when multiple source strings overlap.
- `MIG-055` Post-transform parser validation confirms serialized/JSON structures remain valid before commit.

### Group 6 — table/post-type selection impact
- `MIG-056` Select specific tables and prove unselected tables are untouched.
- `MIG-057` Post-type filter includes required dependent postmeta/term relations according to explicit dependency plan.
- `MIG-058` Excluding a dependent table/object produces a warning/unresolved dependency rather than silently creating broken target references.
- `MIG-059` User/user-meta inclusion is explicit and defaults honor privacy/security policy.
- `MIG-060` Woo/order tables are included only through Woo-compatible profile and HPOS-aware APIs/manifest semantics where applicable.
- `MIG-061` Custom-table selection records owning module/schema/version and does not assume generic WordPress table semantics.
- `MIG-062` Site options vs network options are distinguished correctly in Multisite.
- `MIG-063` Post-type selection cannot expose or migrate protected records denied by source Policy unless authorized migration role explicitly grants them.
- `MIG-064` Row-count preview reflects selection filters and reports estimates separately from verified exported counts.
- `MIG-065` Selection change after checkpoint invalidates incompatible cursor/resume state.
- `MIG-066` Dry-run impact report lists destructive replacements/deletions separately from additive writes.

### Group 7 — media transfer/incremental sync
- `MIG-067` Transfer selected uploads with path, size, checksum and source attachment mapping recorded.
- `MIG-068` Existing target file with same checksum is reused/skipped according to policy without unnecessary overwrite.
- `MIG-069` Existing target file with same path but different checksum triggers conflict semantics rather than silent replacement.
- `MIG-070` Incremental media transfer uses source fingerprint/change evidence and does not infer unchanged solely from filename.
- `MIG-071` Deleted source media propagates deletion only when destructive mirror mode is explicitly enabled and target authority permits it.
- `MIG-072` Protected/private media retains classification and never becomes publicly exposed merely because destination upload path is public by default.
- `MIG-073` Offloaded media is handled through certified storage/CDN adapter and not blindly downloaded/reuploaded without policy.
- `MIG-074` Transfer resumes chunked file from verified checkpoint and checksum-validates final bytes.
- `MIG-075` Corrupt partial file is discarded/quarantined before retry; it is never published as complete.
- `MIG-076` Attachment metadata/reference mapping is updated only after corresponding binary transfer outcome is known.
- `MIG-077` Media transfer metrics distinguish planned, transferred, skipped, conflicted, failed and unknown items.

### Group 8 — theme/plugin/wp-content transfer
- `MIG-078` Transfer selected theme directories without modifying source files and verify target path ownership.
- `MIG-079` Transfer selected plugin directories with manifest/version checks but do not auto-activate plugins unless separately configured/authorized.
- `MIG-080` Network-active plugin state is treated as Network Admin scope and not reproduced from ordinary site migration blindly.
- `MIG-081` Must-use plugins/drop-ins receive separate high-risk compatibility handling and are not silently copied into activation-critical paths.
- `MIG-082` Theme active status is distinct from theme file transfer; import does not auto-switch active theme by default.
- `MIG-083` Child theme transfer verifies required parent dependency exists/is transferred before activation readiness.
- `MIG-084` File ownership/permissions are normalized to destination deployment policy rather than preserving unsafe source mode bits blindly.
- `MIG-085` Executable PHP files are transferred as repository/application assets only under migration scope, never executed during inspection/transform.
- `MIG-086` Package excludes cache/temp/log directories by default unless explicitly selected for a diagnostic profile.
- `MIG-087` Plugin/theme version incompatibility is surfaced before activation/cutover.
- `MIG-088` Transfer completion does not claim application compatibility until target verification runs.

### Group 9 — temporary-table/swap/recovery
- `MIG-089` Destructive database replacement stages imported data in temporary/staging structures before final swap where profile supports atomicity.
- `MIG-090` Temporary table names are collision-safe and scoped to migration run.
- `MIG-091` Validation failure before swap leaves current target tables active.
- `MIG-092` Swap uses bounded transactional/lock strategy where database engine supports it and records non-atomic caveats otherwise.
- `MIG-093` Crash during swap produces explicit recovery state rather than reporting success/failure from incomplete assumptions.
- `MIG-094` Pre-swap recovery point is created/verified when destructive profile requires it.
- `MIG-095` Recovery point availability is checked before destructive operation; “requested backup” is not enough.
- `MIG-096` Rollback restores only resources covered by the recovery plan and reports anything outside rollback scope.
- `MIG-097` External provider side effects cannot be rolled back by local DB swap and remain separately reconciled.
- `MIG-098` Cleanup of temporary tables runs only after successful verification/retention window or explicit failed-run cleanup policy.
- `MIG-099` Operator can inspect recovery instructions/state after interrupted run without needing the original browser session.

### Group 10 — resumable chunk/cursor/cancel
- `MIG-100` Large migration splits work into deterministic chunks with run/item identity.
- `MIG-101` Resume uses persisted checkpoint bound to profile revision/source fingerprint/destination identity.
- `MIG-102` Changed source selection/profile invalidates stale checkpoint rather than resuming under different semantics.
- `MIG-103` Replaying an already committed chunk is idempotent or reconciled before duplicate write.
- `MIG-104` Cancel request stops scheduling new chunks and records in-flight/unknown external outcomes explicitly.
- `MIG-105` Cancel does not claim rollback unless rollback has separately completed and verified.
- `MIG-106` Cursor cannot be reused against another site/tenant/destination even if numeric offset matches.
- `MIG-107` Expired remote session refreshes/re-authenticates safely without losing deterministic run identity.
- `MIG-108` Dead-letter/failed chunk contains bounded sanitized references, not raw credentials/private payload dumps.
- `MIG-109` Progress percentage is derived from known work units and labeled estimate when total is not fixed.
- `MIG-110` Resume after process restart produces same final logical target as uninterrupted run for an unchanged source/profile.

### Group 11 — compatibility mode/plugin allowlist
- `MIG-111` Preflight inventories active plugins/themes/drop-ins and records versions relevant to migration compatibility.
- `MIG-112` Compatibility mode can temporarily suppress only explicitly allowlisted side effects during migration context.
- `MIG-113` Unknown plugin is not disabled automatically merely because it is not on allowlist.
- `MIG-114` Migration-context suppression is scoped to migration request/job and cannot leak into ordinary frontend/admin traffic.
- `MIG-115` Plugin hooks that cause email/webhook/payment side effects remain blocked/quarantined according to environment policy during staging import.
- `MIG-116` Compatibility adapter cannot bypass capability/Policy merely to make migration succeed.
- `MIG-117` WP cron/Action Scheduler side effects are paused/remapped only through owning APIs and restored/verified after migration context.
- `MIG-118` Cache plugins are purged/rebuilt through adapter after verified import, not used as source truth during migration.
- `MIG-119` Security plugin conflict is reported and requires explicit compatible profile rather than globally disabling protection silently.
- `MIG-120` Compatibility-mode start/end is auditable and crash recovery prevents mode remaining accidentally active.
- `MIG-121` Target verification includes check that compatibility suppression is no longer active before cutover success.

### Group 12 — WP-CLI/Ability parity
- `MIG-122` CLI inventory/dry-run returns same normalized plan as admin/API path for identical inputs.
- `MIG-123` CLI run requires authenticated local principal/context and equivalent high-risk capability/approval checks.
- `MIG-124` Ability invocation exposes typed migration actions and cannot accept arbitrary shell/SQL payloads.
- `MIG-125` CLI output redacts secrets/tokens and protected values by default.
- `MIG-126` Non-interactive destructive run requires explicit flags/approval reference and cannot rely on hidden browser confirmation.
- `MIG-127` Exit status distinguishes validation failure, partial/unknown provider state, cancellation and verified success.
- `MIG-128` CLI resume requires exact run ID and rejects cross-environment checkpoint reuse.
- `MIG-129` Ability/MCP caller receives bounded status/progress without downloading protected export content unless separately authorized.
- `MIG-130` AI/MCP may explain/draft command or plan but cannot run migration under planning-only mode.
- `MIG-131` CLI/Ability and admin paths share the same audit/run provenance model rather than creating separate truth stores.
- `MIG-132` Deprecated command/Ability version fails with migration guidance instead of silently changing semantics.

### Group 13 — Multisite/subsite mapping
- `MIG-133` Full network migration maps network tables, site tables and upload paths through explicit topology plan.
- `MIG-134` Single-subsite export/import does not accidentally include unrelated sites’ posts/options/uploads.
- `MIG-135` Source blog/site ID can map to a different target ID explicitly; numeric equality is not required or assumed.
- `MIG-136` Network users are separated from per-site memberships/roles during mapping.
- `MIG-137` Super Admin status is never granted through ordinary site-user migration data.
- `MIG-138` Domain/path mapping validates target network mode and reports unsupported subdomain↔subdirectory assumptions.
- `MIG-139` Network-active themes/plugins are controlled by Network Admin policy and not inferred from source site-local state.
- `MIG-140` Cross-site references are mapped explicitly or retained unresolved; no numeric-ID guessing.
- `MIG-141` Site-specific secrets/provider connections are quarantined/remapped per destination site.
- `MIG-142` Network template migration does not copy live user/session/nonce/runtime cache state.
- `MIG-143` Verification compares per-site counts/routes/assets with source mapping and detects cross-site leakage.

### Group 14 — secrets/privacy/environment safety
- `MIG-144` Vault secrets are never written into migration archive or logs; portable references/placeholders are used.
- `MIG-145` Production API keys/webhook secrets copied in database are detected/quarantined/remapped under environment safety rules.
- `MIG-146` Staging target defaults to blocked/sandboxed email, payment and webhook side effects until explicit activation policy.
- `MIG-147` Auth/session/nonces/transient security tokens are excluded/reset according to profile rather than reused across environments.
- `MIG-148` Privacy classification determines whether personal data may be transferred to the chosen destination/region.
- `MIG-149` Migration package at rest uses configured encryption profile when protected data requires it.
- `MIG-150` Remote transfer logs avoid raw payloads and redact personal data/secrets.
- `MIG-151` Data subject deletion/retention state is not silently resurrected from an older migration source without policy/verification.
- `MIG-152` Environment identity after migration is regenerated for destination and not copied from source as authoritative.
- `MIG-153` Provider/webhook registrations are not cloned live; destination gets disabled/unmapped state until reconciled.
- `MIG-154` AI/MCP cannot request secret inclusion or environment-safety bypass through free-text migration instructions.

### Group 15 — large-site throughput/backpressure
- `MIG-155` 10K-row migration profile records throughput, chunk size, memory and retry metrics on declared environment.
- `MIG-156` 1M-row/table scenario uses bounded pagination/chunks and avoids loading full dataset into memory.
- `MIG-157` Large media library transfer respects bandwidth/concurrency limits and provider quotas.
- `MIG-158` Backpressure pauses producers when destination/write queue exceeds configured capacity.
- `MIG-159` Retry storm uses bounded backoff/jitter and does not overwhelm source/destination after outage.
- `MIG-160` Parallel chunks preserve ordering/dependency where required and do not race final swap.
- `MIG-161` Database lock duration is measured and remains within declared operational budget for chosen strategy.
- `MIG-162` Resume after network interruption avoids retransferring checksum-confirmed completed artifacts unnecessarily.
- `MIG-163` Multisite large-network profile enforces per-site fairness so one site cannot starve all migration workers.
- `MIG-164` Performance evidence records source/destination hardware/software/network/provider limits for reproducibility.
- `MIG-165` Static estimates are not accepted as throughput certification; only executed benchmark evidence can pass these fixtures.

### Group 16 — end-to-end source→target verification
- `MIG-166` Golden: migrate a simple site DB+uploads with URL transform and verify routes/content/media counts/checksums on target.
- `MIG-167` Golden: migrate serialized builder/settings data and prove post-transform structures parse/render without silent corruption.
- `MIG-168` Golden: partial post-type/table selection leaves excluded target data unchanged and reports unresolved dependencies.
- `MIG-169` Golden: push/pull migration interrupted mid-run resumes from bound checkpoint without duplicate rows/files.
- `MIG-170` Golden: staging clone receives sanitized/quarantined production email/payment/webhook credentials and new environment identity.
- `MIG-171` Golden: Multisite subsite maps to different target site ID without leaking another source site’s data.
- `MIG-172` Golden: target conflict forces explicit replace/skip/merge decision and destructive replacement is never described as merge.
- `MIG-173` Golden: crash during table swap enters recoverable state and verified rollback restores covered target state.
- `MIG-174` Golden: unsupported plugin/schema produces explicit blocked/degraded verification rather than “migration complete”.
- `MIG-175` Golden: target post-migration application checks fail when provider/live side effects remain unreconciled even if file/DB transfer succeeded.
- `MIG-176` Golden: AI/MCP adversarial request to migrate production to an untrusted endpoint or include Vault secrets is denied/draft-only with no runtime action.

## Runtime truth

This protocol is documentation-only. `MIG-001…MIG-176` are **176/176 documented, 0/176 executed**. No export/import, remote pairing, database/file mutation, migration, provider/API/AI/MCP call, test, benchmark or deployment occurred. Development authorization remains **NOT GRANTED / 0/56**.