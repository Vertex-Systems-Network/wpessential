# WPEssential — Security Integrity, Malware & Vulnerability Scanner Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `SEC-001…SEC-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- Scanner finding ≠ confirmed compromise; confidence, provenance and verification remain explicit.
- Checksum mismatch ≠ malware; malware signature hit ≠ exploitability; vulnerability feed match ≠ confirmed vulnerable runtime path.
- Quarantine/repair is destructive/high-risk and requires recoverable evidence, Policy and explicit action; scan itself grants no mutation authority.
- Remote/blocklist/provider responses remain provider facts with freshness/version provenance and unknown/degraded states.
- Security hardening ownership stays with Protector/WordPress/provider owners; this scanner does not silently rewrite unrelated configuration.
- Multisite/network scanning preserves site ownership and protected-data boundaries.

## Exact fixtures

### Group 1 — baseline/provenance
- `SEC-001` Create scan profile with stable key, roots/scope, rule-pack versions and execution limits; normalization is deterministic.
- `SEC-002` Baseline inventory records WordPress/core/plugin/theme versions, file fingerprints and source provenance without claiming trust by presence alone.
- `SEC-003` Unknown component/source is classified unknown rather than trusted or malicious by default.
- `SEC-004` Baseline revision pins scanner/rule/provider versions so later comparisons are reproducible.
- `SEC-005` Stale baseline is detected after component update and is not used as current-clean proof.
- `SEC-006` Read-only scan profile cannot mutate/quarantine/repair even if a finding is high severity.
- `SEC-007` Site/root scope excludes paths outside declared scan boundary and reports excluded areas.
- `SEC-008` Symlink traversal respects configured boundary and cannot escape approved filesystem roots.
- `SEC-009` Baseline export redacts sensitive paths/secrets while preserving hashes/provenance needed for comparison.
- `SEC-010` AI/MCP can summarize baseline/findings only through same Policy and cannot mark system clean/compromised without evidence.
- `SEC-011` Unsupported baseline schema/rule version fails typed or migrates explicitly; silent reinterpretation is forbidden.

### Group 2 — Core checksums
- `SEC-012` Official WordPress Core checksum comparison for matching locale/version classifies exact matches as checksum-verified files only.
- `SEC-013` Modified Core file yields mismatch with expected/actual hash and path; mismatch is not automatically labelled malware.
- `SEC-014` Missing expected Core file is distinct from modified file.
- `SEC-015` Unexpected file under Core-owned path is distinct from checksum mismatch.
- `SEC-016` Locale/version without official checksum source returns unsupported/provider-unavailable state, not clean.
- `SEC-017` Core update between inventory and compare invalidates stale expected-version assumptions.
- `SEC-018` Permission/read error for Core file is reported unknown/unreadable and does not count as verified.
- `SEC-019` Large Core file hashing uses bounded streaming and records algorithm/version.
- `SEC-020` Repair suggestion references exact matching official version/package and never downloads/applies automatically in scan mode.
- `SEC-021` Multisite shared Core files are scanned once at installation scope while results map safely to network context.
- `SEC-022` Core checksum provider/network failure preserves prior evidence age and does not silently reuse it as current.

### Group 3 — plugin/theme integrity
- `SEC-023` WordPress.org plugin package/hash comparison pins plugin slug/version and source before integrity verdict.
- `SEC-024` WordPress.org theme package/hash comparison pins theme slug/version and source before integrity verdict.
- `SEC-025` Premium/custom component lacking trusted package source is classified unverifiable rather than compromised.
- `SEC-026` Modified plugin file reports exact diff/hash evidence without assuming malicious intent.
- `SEC-027` Added executable file inside plugin/theme is classified unexpected and sent to heuristic/signature analysis.
- `SEC-028` Removed vendor file is distinct from modified file and includes package provenance.
- `SEC-029` Component update during scan invalidates comparison and produces retry/reconcile requirement.
- `SEC-030` Inactive plugin/theme files remain scannable but inactive status is not vulnerability exploitability proof.
- `SEC-031` Custom build/vendor patch may be allowlisted/suppressed with reason while preserving original finding provenance.
- `SEC-032` Network-active plugin ownership/result is installation/network scoped, not duplicated as independent site mutation authority.
- `SEC-033` Provider package retrieval failure returns unknown/unverified, never clean.

### Group 4 — custom files/change classification
- `SEC-034` Custom upload/content file baseline records type, path class, hash and owner scope without reading excluded private content unnecessarily.
- `SEC-035` New executable PHP file in uploads is classified policy-relevant/high-risk candidate but not auto-malware without further evidence.
- `SEC-036` Expected generated cache/minified file class can be excluded/low-signal by explicit rule, never by hidden hardcode.
- `SEC-037` Unexpected `.htaccess`/server-config change is classified separately from application file change.
- `SEC-038` Unknown binary type is scanned only through safe bounded metadata/signature paths; no execution occurs.
- `SEC-039` Filename extension disagreement with detected MIME/content type is surfaced as anomaly.
- `SEC-040` Repeated benign file regeneration can be suppressed by scoped fingerprint/rule with expiry/review.
- `SEC-041` User-uploaded documents/images are not treated as code merely because bytes contain suspicious strings.
- `SEC-042` Path traversal/symlinked custom content cannot escape configured scan root.
- `SEC-043` Change timeline distinguishes first-observed/last-observed and baseline revision.
- `SEC-044` Privacy-sensitive file contents are not copied into findings/logs when hash/metadata evidence is sufficient.

### Group 5 — signature/heuristic scanning
- `SEC-045` Exact malware signature match records signature ID/version and matched byte/AST region without executing file.
- `SEC-046` Heuristic obfuscation score is labelled heuristic confidence, not confirmed malware.
- `SEC-047` Known benign encoded/minified library does not become confirmed malware solely from entropy/encoding heuristic.
- `SEC-048` Suspicious PHP functions are contextual findings; function presence alone is not compromise proof.
- `SEC-049` Polyglot/mixed-content file is parsed under bounded safe detector and never loaded by application runtime.
- `SEC-050` Rule-pack update creates new scan provenance; historical verdict is not silently rewritten.
- `SEC-051` Signature false-positive suppression requires exact rule/path/fingerprint scope and reason.
- `SEC-052` Archive scanning enforces nesting/decompression/size limits against zip bombs.
- `SEC-053` XML/entity/parser heuristics disable external entity expansion and bound recursion.
- `SEC-054` Scanner timeout/resource exhaustion yields incomplete scan state and names unscanned scope.
- `SEC-055` AI-generated malware explanation cannot upgrade heuristic confidence or trigger repair automatically.

### Group 6 — vulnerability-feed mapping
- `SEC-056` Component version maps to vulnerability feed identifier/version range with source/advisory provenance.
- `SEC-057` Version outside affected range is not marked vulnerable because slug/name merely matches.
- `SEC-058` Unknown/custom fork version yields uncertain mapping rather than vulnerable/not-vulnerable certainty.
- `SEC-059` Vulnerability feed entry includes published/modified/fetched timestamps and provider version.
- `SEC-060` Fixed version recommendation is shown only when advisory/source supports it.
- `SEC-061` Vulnerability severity uses provider scoring/provenance and does not silently convert between CVSS schemes.
- `SEC-062` Vulnerability match does not prove vulnerable code path reachable or exploited.
- `SEC-063` Multiple provider advisories are deduplicated by stable advisory/CVE identity while preserving differing metadata.
- `SEC-064` Withdrawn/disputed advisory changes current state without deleting historical evidence.
- `SEC-065` Feed outage leaves cached evidence clearly stale/aged and current status degraded.
- `SEC-066` Network/site report does not expose installed-component details to principals lacking applicable security visibility.

### Group 7 — remote malware/blocklist providers
- `SEC-067` Remote URL/site scan uses explicitly configured provider and records submitted hostname/URL/data classification.
- `SEC-068` Provider clean response is labelled provider-clean-at-time, not proof local filesystem is clean.
- `SEC-069` Provider malicious/blocklisted response records list/provider/category/freshness without automatic local quarantine.
- `SEC-070` Timeout/connection loss after submission is unknown, not clean or failed verdict.
- `SEC-071` Provider authentication/credential error is distinct from site-security finding.
- `SEC-072` Rate-limit response honors retry-after/quota policy and does not hammer provider.
- `SEC-073` Protected/private URL is not submitted externally unless privacy/provider policy explicitly permits it.
- `SEC-074` Redirect chain submitted to remote scanner cannot be used as SSRF path into private/metadata networks.
- `SEC-075` Provider response schema drift is detected and affected results quarantined from definitive verdict.
- `SEC-076` Provider disagreement is displayed as conflicting evidence rather than collapsed to one false certainty.
- `SEC-077` API key remains Vault-backed and never appears in finding/export/log payload.

### Group 8 — finding confidence/severity/suppression
- `SEC-078` Finding stores type, evidence, confidence, severity, affected resource and provenance as separate fields.
- `SEC-079` Confidence and severity are not conflated; high-confidence low-impact and low-confidence high-impact remain possible.
- `SEC-080` Duplicate observations collapse under stable finding identity while preserving occurrence timeline.
- `SEC-081` Suppression requires reason, scope, actor and optional expiry; underlying evidence remains recoverable.
- `SEC-082` Expired suppression reopens finding when evidence still matches current state.
- `SEC-083` Fingerprint-specific suppression does not hide a changed/new malicious-looking file at same path.
- `SEC-084` Bulk suppression enforces Policy and bounded scope preview.
- `SEC-085` Finding closed because component removed is distinct from repaired/false-positive/resolved-by-update.
- `SEC-086` Severity override records actor/reason and never rewrites provider/original severity provenance.
- `SEC-087` Unauthorized user cannot infer hidden finding/resource details through counts/export/API.
- `SEC-088` AI triage recommendation remains advisory and cannot suppress/close findings without governed mutation.

### Group 9 — quarantine/repair/recovery
- `SEC-089` Quarantine plan requires recoverable source evidence, target path, hash, backup/recovery reference and authorization.
- `SEC-090` Scan-only principal cannot invoke quarantine even if it can view findings.
- `SEC-091` Quarantine moves/copies file through bounded safe path handling and verifies resulting artifact/hash before declaring success.
- `SEC-092` Quarantine failure/partial filesystem outcome is explicit and preserves original evidence.
- `SEC-093` Core/plugin/theme repair uses exact trusted matching package/version and verifies repaired hash.
- `SEC-094` Custom file has no automatic trusted repair source; system offers quarantine/manual recovery instead of fabricating bytes.
- `SEC-095` Repair does not change DB/config/content beyond explicitly approved target operation.
- `SEC-096` Recovery restores quarantined artifact only after hash/provenance validation and destination conflict check.
- `SEC-097` Concurrent update of target file causes repair/quarantine conflict rather than overwriting newer content silently.
- `SEC-098` Quarantine/recovery audit records actor, operation, before/after hashes and recovery reference without secret contents.
- `SEC-099` AI/MCP may draft remediation plan but destructive quarantine/repair remains excluded by default and Policy-gated.

### Group 10 — post-hack workflows
- `SEC-100` Post-hack workflow starts from incident state and does not claim compromise contained merely because scan completed.
- `SEC-101` Credential-rotation checklist references owners/providers without exposing secret values.
- `SEC-102` Administrator/user/session review composes canonical identity/security owners and does not silently reset accounts.
- `SEC-103` File integrity rescan after remediation uses new baseline/revision and compares against prior incident evidence.
- `SEC-104` Database/content indicators are separate scan profile; filesystem clean does not imply DB/content clean.
- `SEC-105` Backdoor persistence check records unscanned/unverifiable surfaces explicitly.
- `SEC-106` Plugin/theme/core update plan preserves dependency/compatibility gates and does not auto-update from scanner result alone.
- `SEC-107` Incident timeline links findings/actions but audit log is not forensic proof beyond captured evidence.
- `SEC-108` External blocklist delisting is provider workflow and remains pending until provider confirms.
- `SEC-109` Recovery-point restore recommendation warns that restore can reintroduce vulnerability/malware and requires post-restore scan.
- `SEC-110` Incident closure requires explicit criteria; no single clean scan automatically closes incident.

### Group 11 — hardening-owner integration
- `SEC-111` Finding can recommend Protector/security-header/file-edit hardening through typed proposal, not direct hidden mutation.
- `SEC-112` Existing hardening owner reports current state before scanner recommends a duplicate rule.
- `SEC-113` Applying hardening proposal requires owning module capability/Policy and its own evidence path.
- `SEC-114` File-permission recommendation distinguishes host/filesystem limitations and does not claim enforceability where unavailable.
- `SEC-115` Disabling XML-RPC/REST/login features remains owned by their modules and is not automatic malware remediation.
- `SEC-116` Security-header recommendation cannot silently weaken CSP/other configured protections.
- `SEC-117` Auto-update recommendation does not override release/change-management policy.
- `SEC-118` WAF/CDN/provider hardening remains external provider fact with separate certification.
- `SEC-119` Duplicate hardening recommendations coalesce by owning capability and affected scope.
- `SEC-120` Removing obsolete hardening requires explicit review; scanner cannot assume external protection exists.
- `SEC-121` AI/MCP hardening plan cannot publish owning-module changes without that module's approval gate.

### Group 12 — privacy/provider data transfer
- `SEC-122` Scan profile classifies paths/metadata/content that may contain PII/secrets before external transfer.
- `SEC-123` Default local scan does not transmit file contents to remote providers.
- `SEC-124` Remote sample/hash submission follows provider privacy profile and explicit allowed fields.
- `SEC-125` Secrets/API keys/passwords detected in files are redacted from logs/export/UI beyond authorized secure handling.
- `SEC-126` Security finding export enforces caller scope and excludes hidden site/user data.
- `SEC-127` Data retention policy covers scan raw samples, hashes, findings and provider payload references separately.
- `SEC-128` Erasure request does not destroy incident/security records subject to authorized retention/legal hold; conflict is reported.
- `SEC-129` Region/residency restriction is checked before selecting remote scanner/provider.
- `SEC-130` Provider deletion request is not reported complete until provider capability/outcome is confirmed.
- `SEC-131` Telemetry/metrics avoid raw file content/path secrets by default.
- `SEC-132` AI/MCP context includes only redacted/authorized finding evidence and cannot exfiltrate raw secrets.

### Group 13 — Multisite/network ownership
- `SEC-133` Shared Core/plugin/theme filesystem scan is installation scoped while site-specific content findings map to owning site.
- `SEC-134` Site admin cannot view network/shared component findings beyond delegated Policy.
- `SEC-135` Super Admin/network capability is not represented as ordinary site role checkbox.
- `SEC-136` Same path-like upload name on two sites remains isolated by site/root ownership.
- `SEC-137` Network aggregate security counts do not expose raw cross-site paths/findings to unauthorized viewers.
- `SEC-138` Network scan job budgets fairly across sites and prevents one site from exhausting resources.
- `SEC-139` Site deletion retires/retains findings according lifecycle without deleting shared network evidence incorrectly.
- `SEC-140` Site clone does not copy incident/open-finding identity as if same environment/resource.
- `SEC-141` Staging clone findings are environment-distinct and cannot close production incidents.
- `SEC-142` Network provider credential can be shared only through explicit network Policy and does not imply shared finding visibility.
- `SEC-143` AI/MCP principal scoped to one site cannot query raw findings from another site by supplying site ID.

### Group 14 — scanner degradation/failure truth
- `SEC-144` Unreadable file produces unreadable/unknown item state and contributes to incomplete-scan summary.
- `SEC-145` Scanner process crash preserves checkpoint/progress and never reports completed/clean.
- `SEC-146` Rule-pack parse failure disables affected rules with explicit degraded state.
- `SEC-147` Database write failure for finding prevents success acknowledgement until durable state/reconciliation.
- `SEC-148` Remote provider timeout remains unknown; retry is bounded/idempotent where submission side effects matter.
- `SEC-149` Memory/time/resource limit stop records exact unfinished scope.
- `SEC-150` Files changing during scan are flagged race/changed-after-read and not given stable verdict without reconciliation.
- `SEC-151` Cache corruption causes rebuild from durable finding/baseline data rather than becoming source truth.
- `SEC-152` Permission loss mid-scan stops affected privileged operations without widening access.
- `SEC-153` Partial network scan reports per-site completion/degraded state rather than one false global success.
- `SEC-154` Recovery after failure resumes only from validated checkpoint/rule/baseline version.

### Group 15 — scale/resource budgets
- `SEC-155` 100K-file fixture later measures enumeration/hash throughput with declared filesystem/hardware profile.
- `SEC-156` Million-file/network fixture later proves checkpointing/backpressure and bounded memory.
- `SEC-157` Hash cache avoids rehashing unchanged files only when metadata/fingerprint validity contract holds.
- `SEC-158` Archive scanning enforces per-file/archive/decompression budgets under scale.
- `SEC-159` Remote-provider queue honors quotas/backoff across many sites fairly.
- `SEC-160` Vulnerability-feed refresh deduplicates component lookups and records provider version once per run.
- `SEC-161` Finding persistence/query remains paginated/indexed for large incident histories.
- `SEC-162` Quarantine store enforces capacity and refuses unsafe operation when recovery artifact cannot be stored.
- `SEC-163` Scan scheduling prevents overlapping identical scans from doubling destructive/resource pressure.
- `SEC-164` Metrics/log volume remains bounded and redacted at high finding counts.
- `SEC-165` Performance/resource claims remain `NOT EXECUTED` until reproducible environment/results are recorded.

### Group 16 — incident golden/regression scenarios
- `SEC-166` Golden clean Core scenario verifies official checksums without claiming whole site malware-free.
- `SEC-167` Golden modified Core scenario yields integrity mismatch and recoverable exact-version repair plan.
- `SEC-168` Golden benign custom-plugin patch scenario remains unverifiable/modified, not auto-malware.
- `SEC-169` Golden known signature scenario records signature provenance and quarantine remains separately authorized.
- `SEC-170` Golden vulnerability-feed scenario maps affected version and fixed-version evidence without claiming exploitation.
- `SEC-171` Golden remote-provider disagreement scenario preserves conflicting provider facts.
- `SEC-172` Golden privacy scenario prevents protected file/secret transfer to unapproved provider.
- `SEC-173` Golden quarantine/recovery scenario preserves before/after hashes and recovery artifact.
- `SEC-174` Golden Multisite scenario proves shared/network vs site-specific ownership and no cross-site leakage.
- `SEC-175` Golden scanner-crash scenario reports incomplete scope and resumes from validated checkpoint without false clean state.
- `SEC-176` Golden AI/MCP adversarial scenario cannot suppress findings, expose secrets or trigger quarantine/repair outside Policy.

## Execution gate

This document specifies evidence only. **SEC executed remains 0/176.** No scan, file read/hash, remote provider call, quarantine, repair, test, benchmark or runtime execution is authorized by this protocol.