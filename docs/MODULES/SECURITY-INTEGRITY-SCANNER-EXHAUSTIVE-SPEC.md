# WPEssential — Security Integrity, Malware & Vulnerability Scanner

Status: **Phase 0 exhaustive planning / no development authorization**  
Edition: **Pro**  
Surface: **52**

## 1. Purpose

Provide evidence-backed integrity monitoring, malware/vulnerability scanning and post-compromise diagnostics without pretending to be an upstream network WAF or DDoS service.

This surface complements Protector. Protector owns request/access hardening; Security Integrity owns scanning, evidence and remediation planning.

## 2. Screens

- Security Overview
- Integrity Baselines
- Scans
- Findings
- Vulnerabilities
- Malware / Suspicious Files
- Blocklist / Reputation
- Hardening Assessment
- Remediation Plans
- Post-Hack Checklist
- Schedules
- Providers / Feeds
- Quarantine
- Reports
- Diagnostics
- Settings

## 3. Integrity baseline

Sources:
- WordPress Core checksums where official checksums exist;
- plugin/theme package checksum/provenance when trusted source metadata exists;
- local first-known-good baseline for custom files;
- selected custom directories;
- configuration fingerprints for non-secret security-relevant settings.

Record:
- path;
- hash algorithm/version;
- size/mtime as secondary evidence;
- source package/version;
- expected provenance;
- baseline time/actor;
- site/environment.

Never call a changed custom file malware merely because it differs from a baseline.

## 4. Scan types

- Core integrity;
- plugin/theme integrity;
- custom-file integrity;
- suspicious code/signature scan;
- heuristic scan;
- vulnerable component inventory;
- exposed dangerous file/config scan;
- database-content IOC scan through bounded registered patterns;
- remote public-site malware scanner adapter;
- blocklist/reputation monitor;
- security headers/hardening assessment delegated/read from owning modules where applicable.

## 5. Finding model

Fields:
- finding ID;
- class;
- severity;
- confidence;
- evidence;
- affected asset/component/version;
- first/last seen;
- source scanner/feed;
- known/unknown state;
- exploitability/context caveat;
- remediation options;
- false-positive/suppression state;
- verification state;
- audit trail.

Severity and confidence are separate.

## 6. Vulnerability intelligence

Provider adapters may ingest:
- WordPress Core advisories;
- plugin/theme vulnerability feeds;
- CVE/GHSA references where mappings exist;
- hosting/vendor advisories.

Requirements:
- source + published/modified time;
- affected-version ranges;
- installed-version comparison;
- fixed-version metadata where known;
- stale-feed warning;
- no claim of safety merely because no feed match exists.

## 7. Malware/signature scanning

- versioned signature sets;
- exact/hash indicators;
- token/AST-like heuristics only where language parser is certified;
- suspicious obfuscation patterns;
- unexpected executable files in writable upload paths;
- webshell indicators;
- injected iframe/script indicators;
- database option/content indicators through bounded patterns.

Scanning must be resource-bounded and resumable via JobService.

## 8. Quarantine

Quarantine is high risk and not default automatic remediation.

Flow:
`finding → impact/dependency preview → backup/recovery point → approval → quarantine action → site health verification → reversible restore`.

Options:
- move file outside executable path if filesystem semantics permit;
- disable affected plugin through WordPress ownership-aware action;
- isolate generated suspicious artifact;
- mark only / no mutation.

Core/plugin replacement from trusted package is a separate repair plan.

## 9. Hardening assessment

Read and explain posture across owners:
- Protector login/rate-limit/site gate state;
- XML-RPC policy;
- REST exposure;
- file editing/config flags;
- auto-update/update health;
- security headers;
- backup recovery readiness;
- admin-equivalent accounts/capability drift;
- stale plugins/themes;
- exposed debug/log files;
- directory listing/config exposure where detectable.

Assessment does not duplicate the settings authority.

## 10. Post-hack mode

Guided plan:
- preserve evidence;
- create recovery point where safe;
- rotate credentials/secrets through owning systems;
- enumerate admin/session/API credentials;
- invalidate sessions/application passwords as approved;
- verify Core/plugin/theme integrity;
- scan files/database;
- review recent privileged changes;
- review scheduled tasks/new users/options;
- restore/repair from trusted source;
- verify externally;
- document incident timeline.

No one-click destructive cleanup without evidence/rollback.

## 11. Remote providers / WAF boundary

WPE may integrate external:
- malware scanners;
- blocklist/reputation services;
- vulnerability feeds;
- CDN/WAF status/providers.

WPE does not claim local plugin code can provide upstream volumetric DDoS mitigation. WAF configuration remains an adapter to the real provider.

## 12. Scheduling / performance

- manual / daily / weekly / event-triggered;
- incremental changed-file scan;
- full periodic baseline verification;
- I/O rate budget;
- CPU/memory budget;
- exclusion profiles;
- pause/resume/checkpoint;
- maintenance window;
- high-load backoff.

## 13. Notifications

- critical new finding;
- vulnerable component with known fix;
- unexpected privileged file change;
- blocklist status change;
- scanner/feed degraded;
- scheduled scan missed;
- remediation verification failed.

Use Notification/Email definitions; no secrets in alerts.

## 14. Permissions

Candidate capabilities:
- `wpe_security_scan_read`
- `wpe_security_scan_run`
- `wpe_security_findings_manage`
- `wpe_security_baseline_manage`
- `wpe_security_quarantine`
- `wpe_security_repair_plan`
- `wpe_security_provider_manage`
- `wpe_security_report_export`

Quarantine/repair/credential/session actions require separate owning capabilities and recent re-auth where policy requires.

## 15. AI / MCP

AI may summarize findings, correlate evidence, prioritize remediation and draft incident reports. It must distinguish observed evidence from hypothesis.

AI/MCP cannot auto-delete suspicious files, disable security controls, rotate secrets, revoke users or apply remediation without deterministic validation + approval.

## 16. Multisite

- per-site visibility and findings;
- network-owned plugin/theme/core integrity inventory;
- network scans may fan out with bounded jobs;
- site admin cannot remediate network-active components;
- cross-site aggregation hides protected path/user data appropriately.

## 17. MUST NOT

- no false WAF/DDoS claims;
- no automatic deletion based on low-confidence signature;
- no secrets/request bodies in scan logs;
- no remote file upload to scanner without explicit privacy/provider policy;
- no disabled TLS verification;
- no vulnerability-free claim from absence of feed data;
- no remediation that bypasses Backup/Policy/Audit boundaries.

## 18. Evidence

Reserved namespace: **SEC-001…SEC-176**, executed **0/176**.

Evidence groups cover baseline integrity, package provenance, scan engines, signatures, vulnerability feeds, remote providers, blocklists, quarantine/repair, hardening integration, post-hack workflows, privacy, Multisite, performance and end-to-end incident regressions.