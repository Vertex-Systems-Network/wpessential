# ADR-0112 — Settings Page Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Decision

Settings Page Builder cannot claim runtime-ready site/network storage, inheritance, secret, REST, cache, external-setting or Multisite behavior until a future implementation passes `docs/QUALITY/SETTINGS-PAGE-EXECUTABLE-EVIDENCE-PROTOCOL.md` for the certified runtime/storage profile.

The protocol enforces:
- Settings Definition separate from scoped runtime value document;
- ST1 site, ST2 network and ST3 network-default + site-override semantics;
- inherited/missing/explicit-empty value distinction;
- typed server validation, unknown-field rejection and stale-edit handling;
- bounded non-autoload-by-default storage;
- Vault references only for secrets, with plaintext excluded from Settings storage/REST/export/history/cache;
- external/native settings read-only until write adapter semantics are certified;
- REST off by default with field/scope Policy;
- site/network cache isolation and inheritance invalidation;
- high-risk recent-auth/impact controls;
- import/export scope/remap semantics;
- Multisite lifecycle/isolation and scale evidence.

## Current state

ST-01…ST-48 documented. **0/48 executed.**

## Development gate

No option/network-option write, Vault operation, REST route, cache mutation, import/export or WordPress runtime execution is authorized before explicit owner consent under ADR-0014.