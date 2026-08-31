# ADR-0178 — WordPress-Native AI Prompt, Requirement Compiler & MCP Architecture

Status: **Accepted planning architecture / executable evidence pending**  
Date: 2026-08-29

## Context

The owner requires every WPEssential module to provide an AI prompt experience. A user should be able to describe a requirement in natural language and have WPEssential build the requested module configuration or complete Solution from canonical WPE primitives. If a required option/system is not available, WPE must offer a structured request flow for the missing capability.

WordPress now provides first-party AI building blocks that make a parallel WPE AI/provider/agent protocol stack unnecessary:
- Abilities API in WordPress 6.9+;
- AI Client and Connectors integration in WordPress 7.0+;
- Abilities/MCP/client schema improvements in WordPress 7.1;
- official WordPress MCP Adapter for exposing Abilities as MCP tools/resources/prompts.

## Decision 1 — One shared Prompt Runtime across all WPE surfaces

Accept the architecture in:
- `docs/AI/AI-PROMPT-REQUIREMENT-COMPILER-ARCHITECTURE.md`;
- `docs/AI/MODULE-AI-PROMPT-OPTION-STANDARD.md`.

All 31 original surfaces and 12 universal foundations use the shared Prompt & Requirement Compiler. Modules must not implement isolated AI chat/provider/key systems.

Combined planned AI-prompt coverage target: **43/43 surfaces**.

## Decision 2 — F12 remains AI product owner

ADR-0177 F12 **AI Gateway, Knowledge & Copilot Studio** remains the user-facing owner of provider/model policy, AI tasks, prompt templates, knowledge/retrieval, copilots, evaluations, usage and budgets.

The module Prompt Runtime is shared platform infrastructure consumed by F12 and all other modules.

No 13th universal foundation is created merely for the prompt button.

## Decision 3 — WordPress AI Client / Connectors are preferred provider substrate

For supported WordPress profiles:
- WPE uses `wp_ai_client_prompt()`/WordPress AI Client rather than a WPE-specific vendor client for ordinary AI tasks;
- provider credentials remain in WordPress Connectors/provider integrations;
- WPE modules do not create duplicate OpenAI/Anthropic/Google/etc. API-key settings;
- provider/model requirements are expressed by capability and policy, not vendor lock-in;
- AI absence degrades to normal deterministic builders.

Exact compatibility remains executable-evidence gated.

## Decision 4 — Structured compilation, not free-form execution

Canonical flow:

`Prompt → Requirement IR → capability resolution → gap report → Plan IR → deterministic validation/simulation → approval → typed Ability execution → verification/audit`

Machine-consumed AI output must validate against versioned schemas.

Free-form model prose is never directly executable.

## Decision 5 — Capability gaps are first-class

Accept:

`docs/AI/CAPABILITY-GAP-REQUEST-SYSTEM.md`

Unsupported requirements must be classified and shown. The user can:
- alter the requirement;
- use an explicitly limited workaround;
- continue with an acknowledged supported subset;
- create a local Request New Option/System draft;
- optionally submit the request remotely after exact payload preview and consent.

No account connection is required merely to keep a local gap request.

## Decision 6 — Use WordPress Abilities as the execution boundary

WPE AI applies changes only through registered typed Abilities and existing module runtimes.

Every invocation remains subject to:
- authenticated principal;
- Capability;
- target resource Policy;
- module/entitlement/lifecycle state;
- site/network scope;
- approval/re-auth when required;
- risk/rate/budget controls;
- audit.

AI does not receive a generic PHP, SQL, JavaScript or shell execution Ability.

## Decision 7 — MCP is an optional adapter, not WPE's core runtime

Accept:

`docs/AI/MCP-ABILITIES-PROMPTS-RESOURCES-CONTRACT.md`

WPE should use the official WordPress MCP Adapter where compatible rather than implementing a competing MCP transport by default.

WPE may register explicit custom MCP server profiles such as:
- `wpe-builder`;
- `wpe-operator`;
- `wpe-developer`;
- `wpe-support-readonly`.

MCP is optional. WPE must function without it.

## Decision 8 — Explicit external exposure

Do not expose every WPE Ability externally.

WordPress 7.1 `meta.public` may establish a general external exposure default. WPE therefore chooses internal/REST/MCP/general exposure deliberately and honors channel-specific opt-outs.

Exposure/discoverability is not authorization.

## Decision 9 — MCP tools/resources/prompts

WPE may expose:
- **tools** for typed read/draft/validate/simulate/apply/request operations;
- **resources** for permissioned schema/catalog/manifest/sanitized context;
- **prompts** for WPE-guided workflows such as Build Solution, Modify Safely, Explain, Audit, Troubleshoot, Find Missing Capability and Draft Capability Request.

Prompt availability never grants tool authority.

## Decision 10 — Draft-first and stale-safe

AI-generated plans bind source revision fingerprints.

Before apply:
- re-check revision/current state;
- reject/rebase stale plans;
- show exact diff;
- bind approval to exact plan fingerprint for applicable risk class.

AI must not overwrite newer human/automation changes silently.

## Decision 11 — Privacy and prompt injection boundaries

- least context by default;
- schema-only mode available;
- no Vault/password/session/reset/Application Password secrets in prompts;
- PII/resource values require actor Policy and AI data policy;
- retrieved/site content is untrusted data, never privileged instruction;
- task tool allowlists are fixed outside untrusted content;
- remote diagnostics/request submission is separately previewed and consented.

## Decision 12 — Current WordPress research facts

Architecture was researched against official WordPress material current in August 2026, including:
- Abilities API handbook;
- WordPress 7.0 AI Client developer note;
- WordPress 7.1 unified Ability public exposure flag;
- WordPress 7.1 Ability schema/lifecycle/filter improvements;
- WordPress MCP Adapter documentation;
- WordPress Agent Skills project notes.

Agent Skills may inform developer/AI guidance and repo expertise, but they do not replace WPE runtime definitions, permissions or evidence.

## Preserved boundaries

- AI assists/drafts; deterministic engines own business state.
- `AI output valid JSON` ≠ valid WPE plan.
- `Ability discoverable` ≠ Ability authorized.
- `MCP server authenticated` ≠ target resource authorized.
- `MCP prompt available` ≠ tool permission.
- `Capability gap request submitted` ≠ feature accepted/planned/released.
- `Feature released` ≠ existing site auto-mutated.
- external provider response ≠ authoritative business fact unless provider contract defines it.

## Development gate

This ADR authorizes documentation only.

No AI Client request, provider Connector change, API key, Prompt Session runtime, MCP Adapter install/server, Application Password, Ability exposure, remote gap submission, module definition mutation or executable test has occurred or is authorized.

ADR-0014 explicit scoped owner development consent remains required.