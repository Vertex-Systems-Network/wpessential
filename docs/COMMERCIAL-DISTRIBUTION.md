# WPEssential — Commercial & Distribution Architecture

Status: **Phase 0 product/engineering policy**  
Last compliance review: 2026-08-27.

## 1. Non-negotiable WordPress.org constraints

Current WordPress.org Detailed Plugin Guidelines establish several boundaries material to WPEssential:

- **Trialware is not permitted** inside the directory plugin. Functionality shipped in the directory artifact cannot be locked/unlocked by payment or disabled after a trial/quota.
- Externally distributed premium add-on plugins are the recommended pattern when premium code is not part of the Free artifact.
- Documented serviceware is permitted, but the Free plugin cannot be merely a license validator/storefront with all substantive paid functionality hidden locally.
- External data transmission/tracking requires appropriate disclosure/consent.
- Directory plugins may not use third-party systems to send executable code into the plugin, including serving/installing/updating premium plugins/add-ons from non-WordPress.org servers inside wp-admin.
- Management services that push software can be a different permitted model when the interaction is handled on the management service's own domain; that is not assumed for initial WPEssential Free.

Reference:
- https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
- https://developer.wordpress.org/plugins/wordpress-org/common-issues/

Therefore WPEssential uses separate Free and Pro packages and keeps executable-package delivery outside the WordPress.org Free plugin.

---

## 2. Package model

### WPEssential Free — WordPress.org target

Permanent local functionality:
- platform/kernel required by Free;
- Custom Post Types Builder;
- Taxonomy Builder;
- local onboarding/home/modules/diagnostics/docs/changelog surfaces;
- optional account/support/service connection surfaces where compliant and explicitly initiated.

Free local features do **not** require a WPEssential account or licensing server.

Free must remain substantively useful on its own; it is not a storefront wrapper.

### WPEssential Pro — external premium add-on

Contains/registers premium modules and depends on a compatible WPEssential Free Platform API range.

WPEssential Pro is distributed outside WordPress.org through WPE-controlled customer/download infrastructure. Its own external update mechanism, if used, is a Pro-package concern and requires a separately reviewed trusted/signed updater architecture.

**WPEssential Free does not fetch/install/update the external Pro executable package.**

---

## 3. 30-day trial

The requested trial is an entitlement for the **externally distributed Pro add-on**.

Recommended compliant onboarding:

1. Activate WPEssential Free.
2. Welcome / Continue Free.
3. User may explicitly choose **Connect WPEssential Account**.
4. Account authentication occurs through the trusted WPE service-domain account-link flow.
5. Connected account can see dynamic plans/trial eligibility as data.
6. User selects Start Trial / Buy / Upgrade and is handed off to the trusted WPE service/customer account.
7. Service activates the trial/commercial entitlement and makes the Pro package available to the customer.
8. Customer installs WPEssential Pro through WordPress's normal administrator plugin-upload/manual installation flow.
9. Pro activates, verifies Free compatibility, and reads verified entitlement state through the approved account connection.
10. Trial/account state is displayed contextually without disruptive global admin notices.

No Free feature is disabled after 30 days.

### No hidden one-click external installer in Free

The WordPress.org Free artifact must not call an external WPE package URL through `Plugin_Upgrader` or equivalent to install/update the Pro add-on from inside wp-admin.

A future WPE management service capable of pushing software from its own domain is a separate product/compliance/security architecture and requires a new ADR before adoption.

---

## 4. Account API / service model

External WPE service may own:
- account sign-up/sign-in/recovery on the WPE domain;
- account/profile management;
- OAuth/account-link token issuance;
- plans/catalog metadata;
- purchase/trial/renewal handoff;
- entitlement/site-activation state;
- support tickets;
- docs search/link resolution;
- remote release/changelog metadata;
- service-health metadata.

Detailed client/service contract:
- `docs/PLATFORM/REMOTE-SERVICE-API-CONTRACT.md`

### Authentication direction

Current preferred candidate is browser-based **OAuth Authorization Code + PKCE (S256)** because a distributed WordPress plugin cannot safely keep a reusable confidential OAuth client secret.

Local WordPress UI should not be the default password-collection proxy for WPE account credentials. Sign-in/signup/recovery happens on the WPE service domain; WordPress stores only approved tokens/connection references under the Secrets Vault contract.

Exact callback-registration/token profile remains a Phase 0 blocker and requires an authorized security/executable spike before implementation.

### API resilience

- bounded timeouts;
- retries only for safe/idempotent requests;
- schema/body-size validation;
- cache plan/docs/release metadata appropriately;
- signed/verified entitlement document strategy;
- clock-skew handling;
- bounded offline/stale policy;
- never break wp-admin/frontend because licensing server is unreachable;
- clear `verification_unavailable` distinct from `expired`;
- no remote executable PHP/JS/CSS injection.

---

## 5. Entitlement state model

Suggested local states:
- disconnected;
- free;
- trial-active;
- pro-active;
- grace;
- expired;
- suspended;
- verification-stale;
- verification-unavailable;
- incompatible-version.

Do not collapse a network/service outage into `expired`.

### Signed entitlement candidate

Entitlement cache should be independently verifiable using an asymmetric signature/trust chain so ordinary runtime does not need a licensing HTTP request on every page load.

The exact signed format/algorithm, key rotation and grace policy remain separate implementation decisions. Service responses cannot dynamically replace their own trusted verification keys without an existing trust chain.

---

## 6. License expiry behavior

### Preserve
- definitions;
- database/application data;
- form entries;
- custom tables;
- fields/relations;
- settings;
- workflow history;
- membership enrollment/history;
- backups;
- exported packages.

### Restrict
- creating new premium definitions;
- editing premium definitions;
- manual premium operations requiring active entitlement;
- premium WPE-hosted service consumption as contract requires.

### Runtime continuity
Existing safe public render output should continue by default. Breaking a client-facing production site because of a billing event creates operational risk and weakens trust.

Mutating automations that could keep creating data/cost may pause with an explicit state. Security/access protections retain a safe last-known configuration rather than silently switching off and exposing resources.

Any different runtime-lock policy requires a new ADR.

---

## 7. Upgrade UI

Allowed/desired:
- module card showing Pro requirement;
- contextual locked setting with reason;
- optional account/trial/plan comparison screen;
- read-only expired definition with Renew CTA;
- trusted service-domain Buy/Trial/Manage Plan handoff.

Avoid:
- notices on unrelated wp-admin pages;
- full-screen takeovers after ordinary navigation;
- fake WordPress errors;
- repeated nags that reappear after dismissal;
- hiding meaningful failures behind upsell;
- presenting a remote package installer inside Free as an upgrade convenience.

---

## 8. Plans are dynamic data, not code

Plans/prices/features may come from WPE service so catalog changes do not require plugin releases.

The plugin still has local technical module metadata. Commercial plan mapping is remote data; module existence/behavior is local code.

Remote plan payload may contain only allowlisted structured data/links. It cannot deliver executable PHP/JS/CSS or arbitrary admin HTML.

Plan/catalog failure does not impair Free CPT/Taxonomy.

---

## 9. Free ↔ Pro compatibility

Free exposes an explicit Platform API version. Pro manifest declares supported ranges.

On mismatch:
- do not fatal WordPress;
- premium modules remain unloaded/degraded;
- show exact required version;
- preserve all data;
- do not run migrations when counterpart compatibility is unknown.

Updates/deployments must account for:
- Free-first window;
- Pro-first window where practical;
- migration ordering;
- old/new Platform API contracts.

See:
- `docs/ARCHITECTURE/FREE-PRO-COMPATIBILITY-STATE-MACHINE.md`
- `docs/ARCHITECTURE/CONTRACT-VERSIONING-AND-DEPRECATION.md`

---

## 10. Privacy / consent

No external account, analytics, telemetry, diagnostics, support payload or site registration leaves WordPress without the appropriate explicit user action/consent and documented privacy behavior.

Free activation must not silently register the site with WPE servers.

Before account linking, disclose the service, purpose, categories transmitted and Terms/Privacy links.

---

## 11. Telemetry direction

If telemetry is ever introduced:
- separate ADR;
- opt-in;
- exact fields disclosed;
- no content/user/customer data by default;
- no secrets;
- local disable control;
- retention/deletion policy;
- analytics separated from required account/license requests.

Telemetry is not required for initial product phases.

---

## 12. Pro update security

Externally distributed WPEssential Pro may eventually use a WPE-controlled updater **from the Pro package**, subject to current rules at implementation time.

Before implementation define:
- trusted update metadata origin;
- authenticated/authorized entitlement where needed;
- package checksum;
- asymmetric package signature/trust chain;
- TLS requirements;
- rollback/reference strategy;
- signing-key rotation;
- compromised-key response;
- Platform API/version compatibility gating;
- no executable code snippets in update metadata.

Free must not become the external Pro updater.

---

## 13. Support API

Ticket UI may list/create/read/reply/close/reopen tickets after explicit account/service connection. Attachments and diagnostics require explicit upload action.

Support bundle flow:
1. generate locally;
2. show included categories;
3. redact secrets/private content;
4. user approves;
5. upload through authenticated service API;
6. record safe result/reference.

The service is source of truth for submitted ticket retention/state. Permanent ticket deletion is not automatically promised by local UI.

---

## 14. Documentation and external-service disclosure

WordPress.org submission/readme must clearly document any optional external services, why/when data is transmitted, and link to service Terms/Privacy as required by current directory guidance.

Local quick-start/version-critical documentation remains available without account/service access.

---

## 15. Commercial success principle

WPEssential monetizes continued creation, advanced operations, maintained integrations/updates/support and premium capabilities—not by holding already-built public sites or security protections hostage.

This policy should reduce churn/support incidents while preserving a strong Pro value proposition.

---

## 16. Implementation blockers

Before any account/distribution/update code:
- exact OAuth/PKCE callback-registration profile;
- token lifetimes/rotation/revocation;
- signed entitlement format/key rotation/offline policy;
- Pro package signing/update channel;
- current WordPress.org compliance review immediately before submission;
- service Terms/Privacy/disclosure text;
- account/support API schemas;
- abuse/rate-limit model;
- Free↔Pro mismatch executable tests.

All executable work remains blocked by ADR-0014 until explicit owner development consent.