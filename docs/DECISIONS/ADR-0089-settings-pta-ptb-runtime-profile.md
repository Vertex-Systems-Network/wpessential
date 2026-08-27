# ADR-0089 — Settings PT-A/PT-B Runtime Profile

Status: **Accepted paper runtime profile / executable evidence pending**  
Date: 2026-08-28

## Context

Settings Page definitions are already separate from runtime values. The remaining ambiguity was how WPE-owned site/network values, inheritance, autoload and concurrent edits should map onto WordPress-native storage.

## Decision

Accept:
- **ST1 — PT-A grouped site Settings value document** as the first site baseline;
- **ST2 — PT-B grouped network Settings value document** as the first network baseline;
- **ST3 — explicit network-default + site-override inheritance** using independent ST2/ST1 documents;
- **ST4 — per-field option rows** only as bounded comparison/native-adapter case, not the universal builder model.

Ordinary WPE Settings default to non-autoload.

## Inheritance rule

Resolution is:
`explicit site override → network default → Definition default`.

Reset-to-inherited removes the explicit site value. It does not materialize the current parent/default value.

## Concurrency rule

Settings value documents carry a version/generation. Future mutation must detect stale edits for high-risk/page-document semantics instead of blindly relying on last-write-wins.

Exact Options API locking/CAS mechanism remains evidence-gated.

## Cache rule

Resolved cache identity includes the relevant site override, network default and Definition generations. A network default mutation must invalidate/version-bypass dependent site resolutions without requiring synchronous writes to every subsite.

## Security rule

- site authority cannot mutate ST2 network values;
- REST is Off by default and field-allowlisted when enabled;
- arbitrary `option_name` editing is not accepted;
- secret plaintext never belongs in Settings storage/history/cache/REST; only Vault references do.

## Evidence still required

After explicit owner consent:
- ST1 vs ST4 performance/atomicity;
- autoload behavior on supported WordPress versions;
- stale-write conflict implementation;
- ST2/ST3 inheritance/cache at 100/1k/10k sites;
- REST/site-network attack cases;
- site delete/clone/export and Vault-degraded behavior.

Executed Settings fixtures: **0**.

## Development gate

This ADR authorizes no option write, setting registration, cache layer, REST route, concurrency lock, migration or benchmark. ADR-0014 explicit owner consent remains required.