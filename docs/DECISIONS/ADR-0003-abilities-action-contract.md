# ADR-0003 — WordPress Abilities as the Typed Action Contract

Status: **Accepted**  
Date: 2026-08-27

## Context

WPEssential has many callers for the same operations:
- wp-admin React UI
- native WordPress screens
- frontend dashboards/forms
- workflows
- scheduled jobs
- REST APIs
- WP-CLI
- future AI/MCP integrations

If each caller implements its own action layer, authorization, validation and behavior will drift.

WordPress 6.9 introduced the Abilities API for standardized, discoverable functionality with input/output schemas and permission callbacks. WordPress also provides an MCP Adapter capable of exposing approved abilities to MCP clients.

References:
- https://developer.wordpress.org/apis/abilities-api/
- https://developer.wordpress.org/news/2025/11/introducing-the-wordpress-abilities-api/
- https://developer.wordpress.org/news/2026/02/from-abilities-to-ai-agents-introducing-the-wordpress-mcp-adapter/

## Decision

WPEssential will define important executable operations through an internal typed ability descriptor that maps to the WordPress Abilities API on supported WordPress versions.

Each applicable ability defines:
- stable ID/name;
- human description;
- input JSON schema;
- output JSON schema;
- permission callback/policy;
- read/write/destructive classification;
- idempotency expectations;
- dry-run/preview support where meaningful;
- audit metadata;
- implementation callback/service.

The same domain service/ability is reused by UI, REST, workflows, jobs, CLI and optional AI adapters rather than reimplementing mutations in each presentation layer.

## Security constraints

- Registration does not imply public exposure.
- REST, MCP and external integrations expose only explicit allowlists.
- Permission callbacks are mandatory for protected actions.
- AI/MCP never bypasses the authenticated principal’s permissions.
- Destructive actions follow WPEssential impact/confirmation/audit policy.
- Ability inputs are untrusted regardless of caller.
- No raw secret dump, arbitrary PHP evaluation, unrestricted filesystem shell or arbitrary database shell ability is part of the standard platform.

## Consequences

### Positive
- one authorization/validation contract across callers;
- AI-native architecture uses WordPress-native primitives;
- easier discoverability/documentation/testing;
- workflows can compose the same safe actions as human UI;
- lower risk of REST/UI behavior drift.

### Costs
- reinforces WordPress 6.9 as the preferred minimum;
- not every internal low-level helper should become an ability, so boundaries require discipline;
- ability versioning/deprecation becomes part of public platform compatibility.

## Rejected alternative

Create a separate privileged “AI API” with direct access to WPEssential database/services. Rejected because it duplicates security logic and creates an unnecessary high-privilege attack surface.

## Implementation note

Abilities orchestrate application services; business logic must not live solely inside registration callbacks.
