# ADR-0001 — Free / Pro Distribution

Status: **Accepted**  
Date: 2026-08-27

## Context

Product requirements call for two permanently free modules (Custom Post Types Builder and Taxonomy Builder), premium modules, and an initial 30-day experience of the premium feature set.

WPEssential also intends to distribute the Free product through WordPress.org.

WordPress.org Detailed Plugin Guidelines prohibit directory plugins from containing functionality that is locked for payment or disabled after a trial/quota. The guidelines point developers toward externally hosted add-on plugins for premium code.

Source: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/

## Decision

Use two packages:

1. **WPEssential Free** — WordPress.org-eligible platform plugin. CPT Builder and Taxonomy Builder remain permanently functional. Local Free functionality does not require an account.
2. **WPEssential Pro** — separately distributed premium add-on that depends on a compatible Free platform version and registers premium modules into the same Module Registry/admin shell.

The requested 30-day trial applies to **Pro entitlement/add-on access**, not to premium source code embedded and locked inside the WordPress.org Free artifact.

## Consequences

### Positive
- aligns product model with WordPress.org trialware policy;
- Free remains genuinely useful and independently functional;
- Pro source is not shipped in the Free package;
- one platform/kernel can still power both editions;
- entitlement logic is separated from local Free capability.

### Costs
- release engineering must build/test two compatible artifacts;
- update order and platform API compatibility need explicit handling;
- Pro distribution/update security is a first-party responsibility;
- onboarding must offer **Continue Free** rather than forcing login/signup.

## Rejected alternatives

### Single WordPress.org package with premium modules hidden until purchase/trial
Rejected because it conflicts with the WordPress.org trialware/functionality-lock rules.

### Make all features remote SaaS solely to justify licensing
Rejected. A remote service must provide substantive functionality; licensing alone is not a sound architecture for turning local plugin code into SaaS.

## Implementation constraints

- Free artifact contains no Pro module source.
- Account/network calls are explicit and privacy-documented.
- Free/Pro compatibility protocol must fail safely without fatal errors.
- No entitlement event deletes user data.
- Any change to this distribution model requires legal/policy re-review and a superseding ADR.
