# WPEssential — Security Model

Status: **Phase 0 mandatory engineering policy**

Security is a design constraint for every module, not a final review task.

## 1. Core security principles

1. Server-side authorization is mandatory even when UI hides an action.
2. WordPress capabilities/policies decide access; menu visibility never grants access.
3. Validate input against an expected schema, sanitize at ingestion where appropriate, and escape at the final output context.
4. Nonces defend against CSRF; they do not replace authorization.
5. Never trust object IDs, slugs, request ownership claims or frontend state as authorization.
6. Least privilege for users, API credentials, provider scopes and internal abilities.
7. No secrets in source control, browser-localized bootstrap data, logs, support bundles or exports by default.
8. Destructive actions get impact preview, explicit capability, confirmation and audit; high-risk actions may require re-authentication/restore point.
9. External dependencies and remote services are assumed to fail or return malicious/malformed data.
10. AI/MCP has exactly the permissions of its authenticated principal and exposed allowlist — never more.

## 2. Threat actors

Design against:

- unauthenticated visitors
- authenticated low-privilege users
- compromised legitimate accounts
- malicious administrators on multisite sub-sites
- CSRF from third-party origins
- malicious uploaded files
- hostile remote API responses
- compromised OAuth/access tokens
- malicious import/backup archives
- extension/plugin conflicts
- accidental administrators causing destructive changes
- AI agents proposing or invoking unsafe operations

A WordPress Administrator is powerful, but WPEssential still protects against accidental self-destruction, cross-site privilege mistakes and unreviewed code/data execution.

## 3. Authentication

### wp-admin / same-site React
Use WordPress logged-in sessions and REST nonces for REST requests.

### External REST
Baseline: WordPress Application Passwords over HTTPS. Optional OAuth/JWT integrations require separate adapter security review.

### WPEssential cloud/account
- OAuth/session/token model defined by the external service
- local plugin stores the minimum token material needed
- refresh/revocation supported
- external connection is optional for Free local functionality
- license validation cannot become the sole substantive “SaaS” justification for WordPress.org

## 4. Authorization

Use dedicated capabilities by module and risk. Examples are illustrative:

- manage WPEssential platform
- manage CPT/taxonomy
- manage queries
- manage roles/capabilities
- manage secrets/connections
- run backups
- restore backups
- reset site
- run developer SQL

Read, edit, execute and destructive actions can require different capabilities.

### Resource-level policy
Object-level checks must validate that the actor can access the specific entity/conversation/form entry/backup/definition, not merely the route.

### Multisite
Super Admin/network boundaries must be explicit. A site administrator must not be able to escalate to network-level capabilities or modify another site through shared tables/configuration.

## 5. CSRF

All state-changing wp-admin form/AJAX/REST actions require proper nonce handling where WordPress cookie authentication is used.

Rules:
- verify nonce
- then verify capability/resource policy
- never treat a valid nonce as permission
- no mutation through GET requests

## 6. XSS and output safety

Render according to explicit context:

- HTML text
- trusted limited HTML through `wp_kses` policy
- HTML attribute
- URL
- JSON
- JavaScript data
- email HTML
- plain text

Shortcodes, block rendering, custom templates, remote data, custom field values, filenames and provider errors are all untrusted until encoded for the destination.

## 7. SQL injection / database safety

- Query Builder canonical format is typed AST.
- Custom-table SQL uses prepared values and allowlisted identifiers/operators.
- Table/column identifiers cannot be passed through value placeholders; validate against schema registry.
- Admin sorting/filtering must not concatenate request parameters into SQL.
- Raw SQL developer console is not a normal feature.
- Public REST queries are bounded and policy checked.

## 8. Arbitrary code execution

WPEssential does **not** implement user-entered PHP through `eval()` as a standard feature.

Cron, Forms, Workflows, Columns and Builder Widgets use registered actions/abilities/hooks/templates.

Any future developer code runner requires its own ADR/threat model and must answer:
- distribution legality
- who can execute it
- how code is stored
- audit/revisions
- recovery from fatal/infinite code
- filesystem/network access
- multisite impact
- whether it is excluded from WordPress.org builds

Default position: **do not ship it**.

## 9. SSRF

User-configured outbound HTTP/webhooks, remote dashboard widgets, backup endpoints and imports are SSRF surfaces.

Threat-model requirements:
- allow `https` by default; other schemes explicit
- reject credential-in-URL patterns where not needed
- resolve and evaluate destination addresses
- block loopback/link-local/private/internal targets by default unless an explicitly privileged local-integration mode is designed
- re-check redirects
- bounded timeout/response size
- DNS rebinding considerations
- no access to cloud instance metadata endpoints
- sanitize/log destination without leaking credentials

Use WordPress safe HTTP functions where their behavior matches requirements.

## 10. File uploads

Forms, support tickets, imports, backup restores, watermark assets and chat attachments must use module-specific allowlists.

Rules:
- validate extension + MIME + content where practical
- randomize/sanitize names
- prevent executable uploads
- keep sensitive/private uploads outside public exposure where architecture permits
- enforce size/count quotas
- authorize every download of private files
- scan/integrate malware checking through optional provider where needed; do not claim scanning if absent

## 11. Import archive safety

Before extraction:
- validate archive type/size
- reject path traversal (`../`), absolute paths and symlink abuse
- enforce extracted-size/file-count limits (zip-bomb defense)
- validate manifest/checksums
- do not overwrite arbitrary filesystem paths
- definitions are schema validated before applying
- secrets excluded unless using a deliberate encrypted secret-transfer flow

## 12. Backup security

Backup archives may contain the entire site and are highly sensitive.

Requirements:
- destination credentials kept in Secrets Vault
- encrypted archives available for remote/untrusted storage
- integrity checksums/manifests
- signed/validated restore metadata where feasible
- temporary archives protected and removed after upload according to policy
- download/restore endpoints capability checked and nonce protected
- remote backup URLs never exposed to unauthorized users
- restore refuses untrusted path traversal or unexpected executable payload placement

## 13. Secrets Vault

Never render stored secret values back to normal UI after save. Show masked state + replace/revoke actions.

Never include in:
- REST listing responses
- localized React state
- logs
- screenshots/support bundles
- configuration export
- AI context

At-rest encryption must have a meaningful key-separation strategy. Do not market reversible obfuscation as encryption.

## 14. Role & Capability Manager

High-risk because it can create privilege escalation or lockout.

Must include:
- protected Administrator/Super Admin handling
- current-user anti-lockout checks
- privilege diff before save
- backup/restore of role state
- audit trail
- separate capability for role management
- prevent a lower-privilege delegated role manager from granting capabilities above its authority

## 15. Custom Admin Menu / Dashboard Builder

Hiding links is visual policy only. Destination endpoints still check capabilities.

Frontend dashboards must check route + resource permissions server-side; React route guards are UX, not authorization.

## 16. Custom Tables / Query Builder

Custom Tables schema changes are high risk.

- destructive schema edits show affected columns/indexes/dependent definitions
- large table operations warn and run through safe migration strategy
- read-only SQL console by default
- public queries enforce row/timeout limits
- expose only selected fields to REST/listings
- protect tenant/site boundaries on multisite

## 17. REST API Builder

Every endpoint requires explicit authentication/policy state.

Defaults:
- read endpoints are authenticated unless user deliberately selects public and the data source supports it
- write/delete endpoints cannot be anonymous in normal UI
- `permission_callback` equivalent mandatory
- input schema mandatory
- bounded pagination
- safe CORS defaults (no wildcard credentials)
- rate/abuse controls for public endpoints
- idempotency for suitable mutations
- secrets/private fields excluded from generic field selector

## 18. Forms & Workflow

Forms are untrusted public input surfaces.

Controls:
- CSRF where session-bound actions require it
- bot/rate controls
- validation on server
- file rules
- role/user creation restrictions
- no user-selected role escalation
- CRUD actions enforce the workflow/service account/user policy
- webhook responses treated as untrusted
- idempotency prevents duplicate payments/records/actions where relevant
- retries distinguish safe-to-repeat actions from non-idempotent actions

## 19. Cron / background jobs

- scheduled arguments validated when created and when executed
- job callbacks are registered code, not arbitrary serialized callable strings
- locks prevent duplicate concurrent work where needed
- max execution/progress limits
- retry budget
- poison/failed job state instead of infinite retries
- secrets redacted from job args/logs

## 20. Email / notifications

- sanitize dynamic HTML
- protect email header fields from injection
- validate recipient addresses
- distinguish transactional vs subscription/marketing semantics
- unsubscribe/preferences where applicable
- no sensitive token in public notification bodies unless explicitly designed
- reset/auth links use WordPress secure token mechanisms, not homemade passwords

## 21. Chat

Every operation checks conversation membership/policy.

Must address:
- IDOR prevention
- attachment access
- spam/rate limits
- block/report/moderation
- edit/delete authorization + history policy
- message retention/privacy
- XSS in message rendering
- no WebSocket token broader than the user’s allowed conversations

## 22. Protector

Protector cannot weaken WordPress security while attempting to harden it.

- changing login/admin paths is optional obfuscation only
- emergency recovery path documented
- trusted-proxy handling prevents spoofed IP allowlists
- password-protected site gate uses strong hashing/session handling via WordPress primitives
- security rules must not block REST/admin-ajax/cron unexpectedly without compatibility preview
- license expiry must not silently disable active security protections

## 23. Watermarker

- source original remains intact
- SVG watermark sanitized; external references/scripts disallowed
- image decompression/size limits to reduce resource exhaustion
- background batch queue rate limited
- paths generated only within approved upload locations

## 24. XML-RPC Manager

Do not claim full disablement from `xmlrpc_enabled` alone. Method filtering and pingback behavior use supported WordPress hooks. Changes show compatibility warnings for consumers such as mobile/remote publishing integrations.

## 25. AI / MCP security

AI is a caller, not an administrator by definition.

Controls:
- opt-in connections
- explicit ability allowlist
- input/output schemas
- permission callbacks
- read-only connection preset
- destructive annotations
- optional human confirmation policy
- dry-run/diff before supported mutations
- audit actor/provider/model/session correlation where available without storing sensitive prompts unnecessarily
- prompt/model output always treated as untrusted data
- no ability that returns all secrets/raw DB/filesystem/code editor by default

## 26. Dependency and supply chain

Before adding a package:
- confirm existing equivalent
- maintenance/release activity
- supported runtime versions
- license
- known vulnerabilities/security history
- transitive dependency cost
- browser/PHP bundle footprint
- update ownership

Automated dependency review/vulnerability scanning is part of CI, but alerts require human assessment rather than blind updates.

## 27. Logging and privacy

Security/audit logs record enough to investigate without collecting unnecessary personal data.

Never log:
- passwords
- API tokens
- OAuth refresh/access tokens
- authorization headers
- backup encryption keys
- full payment credentials
- WordPress auth cookies/nonces

Define retention and purge policies per log class.

## 28. Destructive-action tiers

### Tier 1 — normal mutation
Example: rename a WPEssential definition. Capability + nonce + validation + audit.

### Tier 2 — significant mutation
Example: delete a field used by a form. Add dependency impact + strong confirmation.

### Tier 3 — site/data/security destructive
Examples: restore backup, reset site, destructive table migration, privilege changes. Require dedicated capability, re-auth/typed confirmation where appropriate, restore point/backup when feasible, maintenance strategy and detailed audit.

## 29. Security testing minimums

Per applicable feature:
- unauthenticated access
- insufficient capability
- CSRF
- IDOR/resource ownership
- stored/reflected XSS
- SQL injection
- SSRF
- malicious upload
- path traversal/archive extraction
- privilege escalation
- replay/idempotency
- rate-limit/abuse
- secret leakage in responses/logs
- multisite boundary
- expired license behavior
- dependency failure

Use automated tests permanently for critical regressions.

## 30. Vulnerability response

Before public release, document:
- private security reporting channel
- triage/severity process
- supported release branches
- coordinated disclosure policy
- patch/release procedure
- customer notification criteria
- CVE handling where applicable

Security fixes must not be delayed for marketing release cadence.

## 31. Security Definition of Done

A module is not security-reviewed until:

- assets/endpoints/hooks are enumerated
- capabilities/resource policies are defined
- input/output schemas are documented
- secrets/PII handling is known
- top abuse cases are tested
- destructive actions have recovery/impact behavior
- external calls have timeout/failure/SSRF plan
- logs are redacted
- migration/uninstall effects are understood
- reviewer records remaining risks
