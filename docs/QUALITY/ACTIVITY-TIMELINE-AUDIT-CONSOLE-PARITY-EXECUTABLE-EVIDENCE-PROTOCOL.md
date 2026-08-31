# WPEssential — Activity Timeline & Audit Console Parity Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `ALX-001…ALX-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- Audit/activity records describe observed events; they are not identity, authorization, entitlement, payment, order, or business-state authority.
- Actor attribution is evidence with provenance/confidence; request/user IDs alone do not prove the real human/entity behind an action.
- Before/after diffs are bounded representations and may omit/redact protected data; “no diff” is not proof nothing changed outside observed scope.
- External sink delivery success does not prove durable downstream ingestion unless provider acknowledgment/verification says so.
- Integrity/checkpoint/tamper-evidence supports detection; it is not an absolute immutability or legal non-repudiation claim.
- Privacy/retention/export/erase policies apply to audit data independently from source business records.
- Network views require explicit cross-site authority; same account across sites does not grant all audit access.
- AI/MCP may summarize/explain allowed records but cannot treat audit data as permission to mutate source systems.

## Exact fixtures

### Group 1 — activity-console event rendering
- `ALX-001` Render a normalized event with stable event ID, timestamp, category, action, object reference, actor evidence and outcome.
- `ALX-002` Event with unknown category renders as typed unknown without dropping core provenance fields.
- `ALX-003` Event timestamp preserves source timezone/clock provenance and canonical stored instant separately from display locale.
- `ALX-004` Duplicate event ID in same namespace is idempotently rejected/returned rather than rendered twice as distinct truth.
- `ALX-005` Large event payload renders bounded summary and lazy detail instead of unbounded raw JSON.
- `ALX-006` Protected fields are redacted before render and cannot leak through hidden HTML/data attributes.
- `ALX-007` Event renderer escapes user-controlled labels/URLs and blocks script/HTML injection.
- `ALX-008` Deleted source object still renders historical event with tombstoned reference rather than broken unsafe lookup.
- `ALX-009` Event link to source resource reauthorizes access at click/view time.
- `ALX-010` Console can show event provenance/source adapter without claiming WPE authored third-party event.
- `ALX-011` AI/MCP read/summarize path receives only Policy-authorized/redacted event representation.

### Group 2 — actor/principal/source attribution
- `ALX-012` Logged-in WordPress user event stores canonical user reference plus request/session provenance without equating user ID to real-world identity.
- `ALX-013` System job event records service/job principal separately from initiating user where applicable.
- `ALX-014` API token/Ability call records credential/application principal and optional delegated user context distinctly.
- `ALX-015` Missing actor remains `unknown/system-unattributed` rather than invented from object owner.
- `ALX-016` Spoofed request actor field is ignored when server-resolved principal disagrees.
- `ALX-017` Impersonation/support session records both acting operator and impersonated subject where authorized.
- `ALX-018` AI/MCP event records model/tool invocation principal as supplemental attribution, not authorization source.
- `ALX-019` Imported legacy event retains original source/provider provenance and is not relabeled as native WPE event.
- `ALX-020` User deletion/anonymization follows retention/privacy policy while event actor reference remains semantically explainable.
- `ALX-021` Cross-site same user account records site context separately to avoid false global action attribution.
- `ALX-022` Attribution confidence/evidence level is explicit when source channel cannot prove a strong actor identity.

### Group 3 — wp-admin/frontend/REST channels
- `ALX-023` wp-admin mutation event records admin route/surface class without storing sensitive form payload by default.
- `ALX-024` frontend authenticated mutation records route/context and reuses same normalized event taxonomy.
- `ALX-025` REST mutation records endpoint/method/status/request correlation while redacting auth headers/body secrets.
- `ALX-026` AJAX/admin-post channel is classified correctly and not mislabeled as generic admin page view.
- `ALX-027` Read-only page views are captured only when the configured audit profile explicitly includes them.
- `ALX-028` Failed/denied mutation can generate security/audit event without exposing protected target data.
- `ALX-029` Redirect response is not misclassified as successful business mutation when final source action failed/never ran.
- `ALX-030` Batched REST request emits per-item outcome where underlying operation has item-level success/failure.
- `ALX-031` Full request body is not retained merely for debugging if privacy policy forbids it.
- `ALX-032` Channel-specific correlation IDs are bounded and cannot be attacker-controlled HTML/script output.
- `ALX-033` Same logical action through admin/REST/Ability maps to comparable canonical action taxonomy.

### Group 4 — WP-CLI/Cron/XML-RPC/Abilities/AI attribution
- `ALX-034` WP-CLI mutation records command family, local principal context and outcome without shell environment secrets.
- `ALX-035` Cron/Job Service event records scheduled job definition/run identity separately from any original creator.
- `ALX-036` XML-RPC event records method/channel and authenticated principal according to XML-RPC owner evidence.
- `ALX-037` Ability invocation records Ability version/action and principal while excluding protected arguments.
- `ALX-038` MCP invocation records connected client/tool context only when available and labels unknown fields honestly.
- `ALX-039` AI-generated plan followed by human-approved action records plan provenance and actual executing principal separately.
- `ALX-040` Failed scheduled task retry reuses run/operation identity and does not create misleading duplicate “successful actions”.
- `ALX-041` CLI dry-run event is labeled simulation and never counted as actual mutation.
- `ALX-042` XML-RPC disabled/blocked attempt records denial without implying authentication succeeded.
- `ALX-043` Ability call with delegated principal preserves delegation chain and Policy decision reference.
- `ALX-044` AI/MCP attribution cannot be used to grant future privileges or bypass source capability checks.

### Group 5 — before/after diff adapters
- `ALX-045` Post title/status update records bounded before/after values for allowed fields.
- `ALX-046` Secret/password/token change records changed/not-changed marker only, never raw before/after secret.
- `ALX-047` Large rich-text content uses fingerprint/summary or bounded diff according to audit profile.
- `ALX-048` Structured JSON/array diff uses semantic keys and does not depend on nondeterministic serialization order.
- `ALX-049` Unknown field adapter stores typed opaque-change evidence without attempting unsafe unserialize/eval.
- `ALX-050` Deleted object records known prior summary/provenance without reconstructing protected data beyond retention rules.
- `ALX-051` Created object uses null→value semantics and distinguishes defaults applied by source owner.
- `ALX-052` Bulk action stores per-object diff/outcome or summary with drill-down, not one false monolithic success.
- `ALX-053` External provider status change stores provider reference/status evidence and not fabricated internal before/after truth.
- `ALX-054` Diff display escapes content and redacts fields based on viewer Policy at read time.
- `ALX-055` Missing before snapshot is labeled unavailable, not assumed equal to null/default.

### Group 6 — security/login/role/session context
- `ALX-056` Successful login event records auth method/channel without raw credentials.
- `ALX-057` Failed login records bounded username/email candidate only according to privacy/enumeration policy.
- `ALX-058` Password reset request/complete events never store reset token.
- `ALX-059` Role assignment/removal records target user, role/capability change and authorized actor without implying role label alone equals access.
- `ALX-060` Individual capability override change records diff and anti-lockout context.
- `ALX-061` Session revoke/logout records affected session scope without exposing session token.
- `ALX-062` Super Admin grant/revoke is distinguished from ordinary role mutation and only recorded from network authority path.
- `ALX-063` Account recovery/rescue event records recovery profile and outcome without reusable recovery artifact.
- `ALX-064` MFA/social-login provider event retains provider status/provenance and no provider secret.
- `ALX-065` Security alert source is labeled finding/evidence, not automatic compromise certainty.
- `ALX-066` Viewer lacking security-audit capability cannot see protected login/session/role event details.

### Group 7 — filters/saved/shared views
- `ALX-067` Filter by date/category/action/object/actor/site uses typed query parameters and deterministic pagination.
- `ALX-068` Invalid/unbounded date range is rejected/clamped according to policy rather than scanning entire history accidentally.
- `ALX-069` Full-text search does not expose redacted fields through index snippets.
- `ALX-070` Saved view stores filter definition and owner scope, not result snapshot as canonical truth.
- `ALX-071` Shared view grants only view definition access and still reauthorizes each result row.
- `ALX-072` View owned by one site/tenant cannot be opened in another by guessing ID.
- `ALX-073` Role-based shared view does not override source audit-data Policy.
- `ALX-074` Archived/deleted saved view does not delete underlying events.
- `ALX-075` Filter URL/query values are safely encoded and cannot inject admin UI scripts.
- `ALX-076` Export from a saved view reevaluates current authorization/redaction rather than using stale cached unrestricted rows.
- `ALX-077` AI/MCP query requests are constrained to same filter/row/retention limits as normal API.

### Group 8 — dashboard/history-column UX
- `ALX-078` Dashboard widget shows bounded recent event summary and links to authorized console view.
- `ALX-079` Widget cache is site/user-policy scoped and cannot leak another administrator’s broader audit visibility.
- `ALX-080` History column on post/user/object list shows last relevant event without turning audit timestamp into source modified truth.
- `ALX-081` History column missing event displays “no recorded event” rather than “never changed”.
- `ALX-082` High-volume list uses batched query and avoids N+1 event lookup per row beyond declared budget.
- `ALX-083` Keyboard/screen-reader interaction can open event detail and return focus correctly.
- `ALX-084` Severity/status colors include text/icon semantics.
- `ALX-085` Empty/loading/error/degraded states are explicit and do not show stale cached data as live without label.
- `ALX-086` Viewer cannot infer hidden event category/count from inaccessible dashboard badges if privacy policy forbids it.
- `ALX-087` Dismissed dashboard alert affects presentation only and not event retention/source state.
- `ALX-088` Network dashboard aggregate uses authorized counts and avoids raw cross-site detail by default.

### Group 9 — alerts/reports/digests
- `ALX-089` Alert rule matches explicit event taxonomy/severity and does not treat every audit event as incident.
- `ALX-090` Duplicate event redelivery does not send duplicate alert when event identity already processed.
- `ALX-091` Digest groups events by configured window/site/category and labels counts accurately.
- `ALX-092` Alert recipient selection is Policy/config driven and does not expose event data to unauthorized email recipient.
- `ALX-093` Email/notification delivery success remains transport truth only, not proof recipient read alert.
- `ALX-094` Report generation pins query/filter/as-of window and records redaction profile.
- `ALX-095` Failed report generation does not advance “last delivered” state.
- `ALX-096` External SIEM alert handoff unknown outcome remains unknown/reconcile-required where duplicate side effects matter.
- `ALX-097` Suppressed/ignored finding event can remain in audit history with suppression reason rather than deletion.
- `ALX-098` Rate limits/backpressure prevent alert storm from overwhelming notification system.
- `ALX-099` AI-generated incident summary cites allowed event evidence and does not assert unsupported causal certainty.

### Group 10 — CSV/JSON exports
- `ALX-100` CSV export uses authorized filtered rows and safe spreadsheet escaping for formula-like values.
- `ALX-101` JSON export preserves typed fields/timestamps/object refs and documented schema version.
- `ALX-102` Export omits/redacts protected fields per current viewer/export capability.
- `ALX-103` Large export streams/chunks and respects row/time/resource limits.
- `ALX-104` Export file is stored/delivered through protected artifact path when data classification requires it.
- `ALX-105` Signed/private download URL is scoped/expiring and cannot be converted into public permanent link by parameter change.
- `ALX-106` Export manifest records filter/time range/count/schema/redaction profile without secrets.
- `ALX-107` Retention-deleted events do not reappear from stale export cache after invalidation.
- `ALX-108` Export cancellation/partial file never receives successful-complete status.
- `ALX-109` Imported/exported event schema version mismatch is typed and never silently reinterpreted.
- `ALX-110` Exporting audit data does not grant ability to mutate referenced source objects.

### Group 11 — privacy/export/erase/retention
- `ALX-111` Event field classification distinguishes operational metadata, personal data, protected/security data and secrets.
- `ALX-112` Retention rule deletes/anonymizes only eligible event data after configured start/period/hold checks.
- `ALX-113` Legal/operational hold prevents destructive retention only for authorized scope and is recorded as product workflow metadata.
- `ALX-114` User privacy export returns authorized subject-linked audit data according to privacy policy, not all events mentioning similar text.
- `ALX-115` Erasure can anonymize actor details while preserving required operational event chain where policy requires retention.
- `ALX-116` Secret fields are never retained simply because audit retention period is long.
- `ALX-117` Privacy erase does not silently rewrite canonical business records referenced by events.
- `ALX-118` Retention job is idempotent and records removed/anonymized/skipped/held counts.
- `ALX-119` External sink retention limitations are reported; local delete does not claim remote deletion until confirmed.
- `ALX-120` Site deletion applies site audit lifecycle without deleting network-required events outside owned scope.
- `ALX-121` AI/MCP cannot request unredacted audit export outside principal’s privacy/security Policy.

### Group 12 — external DB/Syslog/SIEM sinks
- `ALX-122` Configure external sink with typed provider endpoint/credentials stored via Vault and explicit event selection.
- `ALX-123` Sink payload contains only approved fields and redaction profile.
- `ALX-124` Syslog severity/facility mapping is explicit and does not change source event severity semantics.
- `ALX-125` External DB write uses parameterized typed adapter and no arbitrary SQL from admin config.
- `ALX-126` SIEM HTTP endpoint uses Safe HTTP/SSRF controls and blocks private/metadata targets unless certified profile permits.
- `ALX-127` Sink write timeout after possible acceptance is unknown and reconciled/deduped where provider supports identity.
- `ALX-128` Permanent auth/schema error is not retried forever.
- `ALX-129` Backpressure/spool is bounded and preserves event identity/order requirements.
- `ALX-130` Sink outage does not block source business mutation unless an explicit strict audit durability profile says so and failure semantics are defined.
- `ALX-131` Sink credential rotation does not alter historical event provenance.
- `ALX-132` Disabling sink stops future delivery but does not delete local events or claim remote copies removed.

### Group 13 — integrity/checkpoint/tamper-evidence truth
- `ALX-133` Event checksum/hash binds declared canonical event bytes/fields and algorithm/version.
- `ALX-134` Hash mismatch detects changed protected representation but does not identify who changed it.
- `ALX-135` Chained checkpoint records previous checkpoint reference and bounded event range without claiming blockchain/legal immutability.
- `ALX-136` Missing event/checkpoint is surfaced as integrity gap rather than silently resequenced.
- `ALX-137` Rebuild/index operation preserves original event IDs/checksums and does not rewrite history silently.
- `ALX-138` Storage admin with write access is not claimed cryptographically unable to modify events unless stronger external controls are actually certified.
- `ALX-139` External timestamp/signature evidence is labeled according to actual provider profile; local server time is not trusted TSA proof.
- `ALX-140` Key rotation retains historical verification metadata for prior hashes/signatures.
- `ALX-141` Restored backup compares checkpoint/hash inventory and reports divergence explicitly.
- `ALX-142` Tamper-evidence failure triggers alert/degraded state but does not itself prove malicious compromise.
- `ALX-143` UI wording avoids absolute “immutable/legally non-repudiable” claims absent certified evidence.

### Group 14 — Multisite/network views
- `ALX-144` Site admin queries only site-owned audit events by default.
- `ALX-145` Network admin aggregate view requires network capability and still applies protected-field redaction.
- `ALX-146` Same event/object numeric ID on two sites remains separated by site/network namespace.
- `ALX-147` Network view can filter per-site without exposing another site’s raw data to delegated site operator.
- `ALX-148` Cross-site user timeline distinguishes network user identity from site membership/action context.
- `ALX-149` Site deletion archives/deletes events according to network retention policy and explicit ownership.
- `ALX-150` Site clone does not copy historical audit events as if they occurred in clone; imported provenance is labeled if copied for diagnostics.
- `ALX-151` Network-shared external sink route includes site namespace so events cannot collide across sites.
- `ALX-152` Site operator cannot disable network-mandated audit categories/sinks beyond delegated controls.
- `ALX-153` Network dashboard cache keys include permission/site scope and never bleed cross-site event counts/details.
- `ALX-154` AI/MCP network summarization requires network principal and cannot infer access because account exists on each site.

### Group 15 — high-volume storage/query performance
- `ALX-155` 1M-event dataset query by time/category/site uses declared indexes and bounded pagination.
- `ALX-156` High-cardinality actor/object filters avoid unbounded table scans beyond declared performance budget.
- `ALX-157` Concurrent event writes preserve unique event identity and do not serialize all traffic on one global lock unnecessarily.
- `ALX-158` Diff payload size limits prevent one event from exhausting storage/memory.
- `ALX-159` Retention purge runs in bounded batches and avoids long blocking locks under declared profile.
- `ALX-160` Export/report jobs use backpressure and do not starve interactive audit queries.
- `ALX-161` External sink outage spool remains bounded with drop/degrade policy explicitly recorded when capacity is exceeded.
- `ALX-162` Multisite fairness prevents one noisy site from monopolizing shared audit workers/storage budget.
- `ALX-163` Query cache invalidation does not expose stale rows after permission/retention changes.
- `ALX-164` Performance fixture records DB engine/schema/index/environment for reproducibility.
- `ALX-165` Static estimates are not accepted as runtime performance certification.

### Group 16 — incident reconstruction regression
- `ALX-166` Golden: reconstruct login→role change→protected action timeline with separate actor/source/channel evidence and no privilege inference from audit alone.
- `ALX-167` Golden: bulk REST mutation shows per-item before/after/outcome and one denied item without false all-success.
- `ALX-168` Golden: scheduled job + retry + external sink unknown outcome retains one logical operation and explicit reconciliation state.
- `ALX-169` Golden: privacy erase anonymizes eligible actor data while retained security events remain explainable under policy.
- `ALX-170` Golden: Multisite network timeline shows two sites isolated and same user account actions attributed with site context.
- `ALX-171` Golden: tampered stored event produces integrity alert/gap without false claim identifying attacker.
- `ALX-172` Golden: external SIEM outage/backpressure preserves bounded local events and accurate delivery status.
- `ALX-173` Golden: CSV/JSON exports contain only authorized/redacted fields and protected download expires correctly.
- `ALX-174` Golden: imported legacy audit events retain provider provenance and do not become WPE identity authority.
- `ALX-175` Golden: dashboard/history-column remains responsive on large dataset and does not leak hidden event counts/details.
- `ALX-176` Golden: AI/MCP adversarial request to use audit actor/event as authorization or exfiltrate redacted security data is denied.

## Runtime truth

This protocol is documentation-only. `ALX-001…ALX-176` are **176/176 documented, 0/176 executed**. No audit events were captured, exported, erased, hashed, delivered to external sinks, queried at scale or processed by AI/MCP. Development authorization remains **NOT GRANTED / 0/56**.