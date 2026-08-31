# ADR-0009 — Secrets Vault and Credential References

Status: **Proposed — Phase 0 blocker**  
Date: 2026-08-27

## Context

Backup providers, OAuth connections, webhooks, external APIs, SMTP/services, AI providers and support integrations require credentials. Treating API keys/tokens like ordinary WPEssential settings would expose them through REST bootstrap, exports, logs, backups, revisions, AI context or browser state.

WordPress hosting environments also make “encrypted at rest” claims tricky: if ciphertext and its decryption key are stored together in the same database with equivalent compromise boundaries, encryption can become security theater.

## Proposed decision

Create a central **Secrets Vault** service. Modules store only stable credential/connection references, never copy secret values into module definitions.

### Vault behavior

- secret values are write-only through normal admin APIs;
- UI shows masked/existence state, not retrievable plaintext;
- REST/list APIs return metadata only;
- definitions/revisions store Vault IDs;
- logs/audit redact secret fields;
- config exports exclude secrets by default;
- AI/MCP never receives secrets unless a narrowly scoped connector explicitly requires a secret for execution, and even then the model should not see the raw value;
- rotation/revocation and connection-test flows are centralized;
- OAuth refresh lifecycle is centralized per provider adapter.

## At-rest encryption proposal

Use authenticated encryption only when WPEssential can derive/access key material from a boundary separate enough from the database record to provide real value, for example host configuration/environment/WordPress salts via an explicitly designed key derivation strategy.

The exact cryptographic primitive/key-derivation format must be selected only after current PHP/WordPress support and operational recovery are reviewed.

Do not invent custom cryptography.

## Recovery/key-change problem

The accepted design must define what happens when:
- WordPress salts change;
- site migrates to another server;
- database is restored without old filesystem/env configuration;
- encryption key is rotated;
- Free/Pro version changes;
- multisite network/site scopes differ.

A design that silently makes credentials unrecoverable after normal migration is unacceptable.

## Security principle

If a host cannot provide a defensible separate key boundary, WPEssential must state the limitation rather than market reversible obfuscation as encryption. Access controls/redaction/least-privilege still apply.

## Provider scopes

Store the smallest provider permissions possible. Backup destination access should not automatically grant unrelated account resources. OAuth adapter documentation records required scopes.

## Export/import

Default WPEssential export contains connection placeholders/metadata but no credentials. Secret transfer, if ever supported, requires a separately encrypted export flow with explicit user action and password/key handling.

## Acceptance work

Before accepting:
1. threat-model DB-only vs filesystem/full-host compromise;
2. choose cryptographic primitive using maintained PHP facilities;
3. define key derivation/storage and rotation;
4. test salt/key/site migration behavior;
5. define multisite credential scope;
6. define recovery UX;
7. test no-secret leakage in REST/log/export/support bundles/browser state;
8. verify OAuth refresh/revoke flows with representative providers.
