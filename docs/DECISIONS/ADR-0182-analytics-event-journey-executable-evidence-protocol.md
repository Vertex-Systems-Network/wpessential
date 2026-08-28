# ADR-0182 — Analytics, Event Tracking & Journey Intelligence Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-29

## Context

ADR-0177 accepted F02 — Analytics, Event Tracking & Journey Intelligence. ADR-0180 reserved `ANL-001…ANL-176` as its fixed evidence envelope.

A behavioral analytics system needs exact executable proof for schema, collection, consent, identity stitching, late data, metrics, funnels, cohorts, journeys, attribution, privacy, storage, Multisite and scale. It must remain distinct from operational Event Bus/Audit truth and must never turn observational events into authorization/business authority.

## Decision

Accept:

`docs/QUALITY/ANALYTICS-JOURNEY-EXECUTABLE-EVIDENCE-PROTOCOL.md`

Evidence namespace:
- **ANL-001…ANL-176**.

Current truth:
- documented: **176**;
- executed: **0/176**;
- F02 runtime certification: **0**;
- final storage/backend topology remains evidence-gated.

## Coverage

The protocol fixes evidence for:
- event catalog/schema/versioning;
- server/browser/import collection;
- anonymous/session/auth identity semantics;
- consent/privacy/retention/export/erase;
- dedupe/event-time/received-time/late data;
- typed metrics;
- funnels;
- cohorts/retention;
- journey/path exploration;
- attribution models and causal-language boundary;
- data quality/anomalies;
- raw store/materialization/cache/invalidation;
- authorization/tenant isolation;
- Multisite/site lifecycle;
- 100K/1M/10M/100M evidence profiles;
- full golden/regression scenarios.

## Preserved boundaries

- Event Bus ≠ analytics warehouse.
- Audit Log ≠ behavioral analytics store.
- client/browser event ≠ business authority.
- analytics observation ≠ authorization.
- modeled attribution ≠ causal proof.
- AI anomaly/root-cause output distinguishes evidence from hypothesis.
- privacy erase of raw/linkable data and retained aggregates follows explicit policy and cannot be overstated.
- storage/backend paper profile ≠ runtime certification.

## Development gate

No ANL fixture has executed. No event collection, cookie/session identifier, analytics table/store, warehouse adapter, metric/funnel/cohort query, attribution run, AI call, retention job, benchmark or runtime test is authorized.

ADR-0014 explicit scoped owner development consent remains required.