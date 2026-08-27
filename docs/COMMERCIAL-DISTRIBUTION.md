# WPEssential — Commercial & Distribution Architecture

Status: **Phase 0 product/engineering policy**

## 1. Non-negotiable WordPress.org constraint

WordPress.org Detailed Plugin Guidelines state that trialware is not permitted: a directory plugin may not include functionality that is locked for payment or disabled after a trial/quota. The guidelines recommend externally distributed add-on plugins when premium code should be excluded.

Reference: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/

Therefore the requested product is implemented as two packages.

## 2. Package model

### WPEssential Free — WordPress.org eligible target

Permanent functionality:
- platform/kernel required by Free
- Custom Post Types Builder
- Taxonomy Builder
- documentation/changelog/support/account connection surfaces where compliant

Free local features do not require a WPEssential account.

### WPEssential Pro — external premium add-on

Contains/registers premium modules and depends on a compatible Free platform version.

Pro can be distributed and updated through WPEssential-controlled infrastructure subject to WordPress/plugin licensing and security review.

## 3. 30-day trial

Trial is an entitlement for the externally distributed Pro add-on.

Recommended onboarding:

1. Activate WPEssential Free
2. Welcome
3. Choose **Continue Free** or **Connect account**
4. Connected account can see dynamically fetched plans/trial eligibility
5. User explicitly starts Pro trial
6. Pro add-on distribution/installation follows the approved distribution mechanism
7. Pro registers premium modules
8. Trial countdown/account state shown without disruptive global admin notices

No Free feature is disabled after 30 days.

## 4. Account API

External service owns:
- sign up
- login/token issuance
- forgot/reset flow
- account profile
- plans/catalog
- purchase handoff
- subscription status
- renewal
- trial eligibility
- license/site activation state
- support tickets

Plugin owns only the local UX/integration needed to communicate with those APIs.

### API resilience

- bounded timeouts
- retry only safe requests
- cache plan/catalog responses
- signed/authenticated entitlement response strategy
- clock-skew tolerance
- offline grace for transient license service outages
- never break wp-admin/frontend because licensing server is unreachable
- clear “could not verify” state distinct from “expired”

## 5. License state model

Suggested states:

- disconnected
- free
- trial-active
- pro-active
- grace
- expired
- suspended
- verification-unavailable
- incompatible-version

Do not collapse network outage into expired.

## 6. License expiry behavior

### Preserve
- definitions
- database data
- form entries
- custom tables
- fields/relations
- settings
- workflow history
- backups
- exported packages

### Restrict
- creating new premium definitions
- editing premium definitions
- manual premium operations that require an active entitlement
- premium cloud/service consumption as contract requires

### Runtime continuity
Existing safe public render output should continue by default. Breaking a client-facing site on billing expiry creates operational risk and weakens trust/renewal economics.

Automations that mutate data or consume external paid services can pause with a clear state. Security protections should retain a safe last-known configuration rather than silently switch off.

Any different runtime-lock policy requires an ADR because it affects customer sites, support burden and commercial trust.

## 7. Upgrade UI

Allowed/desired:
- module card showing Pro requirement
- contextual locked setting with reason
- trial/plan comparison screen
- read-only expired definitions with renew CTA

Avoid:
- notices on unrelated wp-admin pages
- full-screen takeovers after ordinary navigation
- fake WordPress errors
- repeated dismissible nags that reappear
- hiding meaningful failures behind upsell

## 8. Plans are dynamic

Plans/prices/features come from WPEssential API so package changes do not require plugin releases.

The plugin must still have local capability metadata to know what a module technically supports. Commercial plan mapping is remote configuration; technical module existence is code.

Remote plan payload cannot deliver executable PHP/JS.

## 9. Free ↔ Pro compatibility

Free exposes an explicit platform API version. Pro manifest declares supported Free ranges.

On mismatch:
- do not fatal
- premium modules remain unloaded
- show exact required version
- preserve all data

Updates must account for deployment order:
- Free-first compatible window
- Pro-first compatible window where practical
- migrations run only when required counterpart is compatible

## 10. Privacy / consent

No external account, analytics, telemetry, diagnostics or support data leaves the site without an appropriate explicit user action/consent and documented privacy behavior.

Free activation must not silently register the site with WPEssential servers.

## 11. Telemetry direction

If telemetry is ever introduced:
- opt-in
- explain exact fields
- no content/user/customer data by default
- no secrets
- local disable control
- deletion/retention policy
- separate product analytics from license checks

Telemetry is not required for Phase 1.

## 12. Update security

External Pro updates require a signed/trusted update architecture. Before implementation define:
- authenticated update metadata
- package integrity/signature mechanism
- TLS requirements
- rollback strategy
- key rotation
- compromised signing-key response
- version compatibility gating

Never accept executable update URLs or code snippets from unauthenticated API responses.

## 13. Support API

Ticket UI may list/create/read/reply/close/reopen tickets. Attachments and diagnostics require explicit upload action.

Support bundle flow:
1. generate locally
2. show included categories
3. redact secrets
4. user approves
5. upload
6. record ticket attachment/result

## 14. Commercial success principle

WPEssential should monetize **continued creation, advanced operations, updates/support and premium capabilities**, not hold already-built public sites hostage. This policy should reduce churn/support incidents while making the Pro value proposition clear.
