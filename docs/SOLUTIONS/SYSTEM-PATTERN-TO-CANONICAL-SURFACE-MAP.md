# WPEssential — System Pattern → Canonical Surface Map

Status: **Canonical post-P0 system composition map / planning-only / no development authorization**  
Date: **2026-08-29**

## 1. Purpose

The 160 curated reference systems reuse 40 system patterns. This map binds each pattern to the accepted **56 canonical WPEssential surfaces**, shared services and domain adapters.

Therefore every current curated system resolves transitively as:

`System Sxxx → Pattern(s) Pxx → Canonical Surface IDs → Shared Services/Adapters → Owner Definitions/Abilities`

No system may create a private schema/query/workflow/role/HTTP/search/ledger/reservation/document/AI runtime merely because it has a business name such as CRM, ERP, LMS or Helpdesk.

## 2. Composition symbols

- `R` = required canonical surface(s) for the pattern's defining semantics;
- `C` = common composition surfaces normally needed for a complete application experience;
- `O` = optional surface selected by blueprint options;
- `Sxx` = accepted shared service, not numbered product surface;
- `A01` = WooCommerce Commerce Domain Adapter when Woo is commerce runtime;
- `EXT` = external authority/provider explicitly remains external.

A Blueprint can omit a common/optional UI composition only if its own scope genuinely does not need it. It cannot replace the canonical owner with a private implementation.

## 3. Pattern map

### P01 — Master Data Registry / CRUD Directory

- **R:** `3 Fields`, `4 Relations`, `5 Status`, `6 Query` and a canonical writable Data Source: `1/2/7` or registered source.
- **C:** `8 Admin Columns`, `9 Listings`, `26 Import/Export`, `30 Roles/Policy`.
- **O:** `13 Portal`, `17 Forms`, `34 Search`, `42 Geo`, `51 Order`, `54 User Stores`.
- Rule: business records stay with selected Data Source; P01 is not a new generic EAV entity store.

### P02 — Pipeline / CRM Opportunity

- **R:** `3`, `4`, `5`, `6`, `17`.
- **C:** `9`, `13`, `19`, `20`, `30`.
- **O:** `33 Analytics`, `35 Decision/Scoring`, `37 Scheduling`, `40 Documents`, `51 Sequence` for explicit board/manual sequence only.
- Rule: stage mutation belongs to Status; workflow reacts/orchestrates after or around authorized transition.

### P03 — Case / Ticket / SLA Management

- **R:** `3`, `4`, `5`, `6`, `17`.
- **C:** `9`, `13`, `19`, `20`, `21 Chat`, `30`.
- **O:** `33`, `35`, `37`, `40`, `42`.
- Rule: messages can be case comments or Chat links by explicit schema; do not duplicate Chat store implicitly.

### P04 — Approval / Authorization Workflow

- **R:** `17 Workflow`, `30 Roles/Policy`, shared `S06 Approval Policy`, `5 Status` when a governed object state changes.
- **C:** `19`, `20`, shared Audit.
- **O:** `40 Documents`, `43 AI` for draft/explanation only.
- Rule: approval != authorization bypass; final action still invokes canonical owner Ability/Policy.

### P05 — Scheduling / Booking / Reservation

- **R:** `37 Reservations`.
- **C:** `17 Forms/Workflow`, `13 Portal`, `19`, `20`, `30`.
- **O:** `42 Geo`, `40 Documents`, `A01/EXT` payment, external calendar via `23 Connections`.
- Rule: Cron does not own resource locks; payment does not confirm reservation until owner transition/reconciliation.

### P06 — Ledger / Credits / Balance

- **R:** `36 Ledger`, shared `S05 Money/Decimal/Unit`.
- **C:** `6 Query`, `9`, `17`, `30`, Audit.
- **O:** `33 Analytics`, `35 Decision`, `40 Statements/Documents`.
- Rule: balance is derived from movements; no Custom Table direct balance overwrite.

### P07 — Inventory / Movement / Warehouse

- **R:** `36 Ledger` for quantity movement plus canonical Item/Location Data Sources using `3/4/5/6`.
- **C:** `17`, `9`, `30`.
- **O:** `37 Reservations`, `42 Geo`, `35 Reorder decisions`, `33 Analytics`, `40 Documents`.
- Rule: all quantity mutation resolves to ledger movement profile.

### P08 — Procurement / Purchase Order

- **R:** P01 master data + P04 approvals + P07 receipt/movement composition.
- **C:** `17`, `13`, `19`, `20`, `40 Documents`.
- **O:** `35 formulas`, `41 Sync`, `A01/EXT accounting`.
- Rule: PO document != stock receipt; receipt posts through Ledger owner.

### P09 — Project / Task / Work Management

- **R:** `3`, `4`, `5`, `6`, `17`.
- **C:** `9`, `13`, `19`, `21`, `30`.
- **O:** `37 capacity/calendar`, `33 analytics`, `40 documents`, `51 task sequence`.
- Rule: dependency graph uses Relations/typed task semantics, not generic hidden workflow edges.

### P10 — Form Intake / Application / Admissions

- **R:** `17 Forms/Workflow`, shared Field Schema/`3` for reusable fields, `5 Status`, `30 Policy`.
- **C:** `13`, `19`, `20`.
- **O:** `35 scoring`, `40 generated decisions/docs`, `37 interviews`, `42 location`, `52 file-security evidence adapter`.
- Rule: form submission is not automatically downstream record approval/authorization.

### P11 — Membership / Enrollment / Entitlement

- **R:** `15 Membership`, `30 Roles/Policy`.
- **C:** `13 Portal`, `14 Profiles`, `17`, `19`, `20`, `22 REST` as needed.
- **O:** billing/provider through `23` + certified adapter; `40` certificates/docs; `33` analytics.
- Rule: provider billing fact != entitlement; role != membership.

### P12 — Course / Learning Program

- **R:** canonical course/lesson Data Sources using `1/3/4/5/6`, `15 Membership/Enrollment` when gated, `17 Forms/Workflow`.
- **C:** `9`, `13`, `19`, `20`.
- **O:** `35 assessment scoring`, `33 learning analytics`, `40 certificates`, `51 curriculum order`, `54 favorites`.
- Rule: certificate issue consumes verified completion state; profile value cannot fabricate completion.

### P13 — Directory / Marketplace Listing

- **R:** `3`, `4`, `6`, `9`, canonical entity source, `5` moderation/publish state where used.
- **C:** `17 inquiry`, `13 owner portal`, `19`.
- **O:** `34 Search`, `42 Geo`, `38 Placement`, `39 experiments`, `54 user stores`, `51 manual order`.
- Rule: featured placement does not alter source authorization/ownership.

### P14 — Marketplace / Multi-Party Transaction Coordination

- **R:** P13 + `17 Workflow`, `30 Policy`; `A01` or `EXT` owns commerce/order/payment when applicable.
- **C:** `13`, `19`, `20`.
- **O:** `36 Commission Ledger`, `35 allocation/formulas`, `37 booking`, `40 docs`.
- Rule: WPE commission ledger is not provider/bank payout truth.

### P15 — Document / Record Lifecycle

- **R:** `40 Documents`, `30 Policy`, shared `S03 Protected Asset` for private outputs.
- **C:** `17 Workflow`, `19`, `20`.
- **O:** `53 Fonts`, `28 Media`, external signature/timestamp provider via `23`, `36` only if separately posting a ledger fact.
- Rule: rendered doc != legal signature/payment/order truth.

### P16 — Inspection / Checklist / Audit

- **R:** `17 Forms/Workflow`, `3`, `4`, `5`, `30`.
- **C:** `13`, `28 Media`, `19`.
- **O:** `35 scoring`, `40 report`, `37 scheduling`, `42 location`.
- Rule: application inspection record is not shared Audit/Observability log; both may coexist with distinct ownership.

### P17 — Maintenance / Work Order / Field Service

- **R:** P03 Case/Work Item + P05 Scheduling + P16 Inspection; inventory parts use P07.
- **C:** `13`, `19`, `20`, `30`.
- **O:** `42 territory`, `40 work report`, `33 service analytics`, `36 cost/parts ledger`.
- Rule: recurring maintenance schedule != reservation; parts use Ledger movements.

### P18 — Recruitment / Candidate Tracking

- **R:** P02 Pipeline + P10 Intake + P05 Interview Scheduling + P04 Approval.
- **C:** `13`, `19`, `20`, `30`.
- **O:** `35 scoring`, `40 offer`, `33 funnel analytics`.
- Rule: AI/candidate score is advisory unless separately lawful policy accepts deterministic use; no AI authorization.

### P19 — HR Employee Lifecycle

- **R:** P01 + P04 + P09 + `30 Roles/Policy`.
- **C:** `13`, `14`, `17`, `19`, `20`, `40`.
- **O:** `37`, `36`, `42`, `54`.
- Rule: WordPress user identity != employment status; offboarding follows explicit role/access/provider actions.

### P20 — Leave / Absence / Balance

- **R:** `36 Ledger` for balance/accrual, `37` date/resource overlap where applicable, `17`, `30`.
- **C:** `13`, `19`, `20`, `6`.
- **O:** `35 policy calculations`, `33 analytics`, external HR sync through `41`.
- Rule: approved request posts/reverses ledger entries; balance is not a mutable form field.

### P21 — Time Tracking / Timesheet

- **R:** canonical Time Entry Data Source using `3/5/6`, `17` approval/workflow, `30`.
- **C:** `9`, `13`.
- **O:** `35 calculations`, `33 analytics`, `36` only for explicit time-credit ledger, external payroll/accounting via `41/23`.
- Rule: tracked/approved time != payroll/payment truth.

### P22 — Expense / Reimbursement

- **R:** `17`, `30`, canonical Expense Data Source, `40` receipts/approval artifact where needed.
- **C:** `35` policy/calculation, `19`, `20`.
- **O:** `36` internal advance/credit ledger; external accounting/payment via `23/41/EXT`.
- Rule: approved expense != payment settlement.

### P23 — Event Registration / Attendance

- **R:** `37` capacity/reservation when attendance is capacity-controlled, `17`, `13`.
- **C:** `19`, `20`, `30`.
- **O:** `40 ticket/certificate`, `42 venue`, `A01/EXT` payment, `33 analytics`.
- Rule: registration != paid/attended; attendance is a separate state/event.

### P24 — Content Editorial / Publishing Workflow

- **R:** `1/3` content schema, `5` editorial states, `4` assignments/dependencies, `17` workflow, `30`.
- **C:** `9`, `18` scheduling, `19`.
- **O:** `51` persistent editorial order, `33`, `40`.
- Rule: scheduling publication does not create a separate Cron-owned content state.

### P25 — Knowledge Base / Documentation Portal

- **R:** content Data Source `1/2/3`, `4`, `5`, `9`, `13`.
- **C:** `34 Search`, `30`.
- **O:** `43 grounded assistant`, `44 Redirects`, `33 analytics`, `54 favorites`.
- Rule: AI answers reference approved knowledge/search evidence and cannot alter source docs without owner Ability.

### P26 — API / Developer Portal

- **R:** `22 REST API`, `23 Connections` for external connection/OAuth lifecycle where used, `30 Policy`.
- **C:** `13`, `14`, documentation/listings through `9`.
- **O:** `33 usage analytics`, `40 docs`, `43 AI docs assistant`.
- Rule: documentation visibility != endpoint authorization; secrets remain Vault-owned.

### P27 — Incident / Problem / Change Management

- **R:** P03 + P04/Workflow + `30`.
- **C:** `19`, `20`, `21`.
- **O:** `33 telemetry/analytics`, `40 postmortem`, `37 maintenance window`, `52` security findings refs.
- Rule: application incident timeline != immutable shared Audit log, though audit facts may be referenced.

### P28 — Data Import / Sync / ETL

- **R:** `41 Sync` for recurring sync; `26 Import/Export` for one-time package/data runs; `23 Connections` for transport/credentials.
- **C:** `18` schedule trigger, `17` governed orchestration, Audit.
- **O:** `45 Transform`, `24 Backup`, `55 staging migration`.
- Rule: one-time import and recurring sync remain distinct lifecycles; timeout may be unknown outcome.

### P29 — Analytics / Funnel / Cohort

- **R:** `33 Analytics`.
- **C:** `6 Query`, `13 dashboard/listing presentation`, `19 alerting`.
- **O:** `35 decision thresholds`, `43 narrative summaries`.
- Rule: Analytics does not replace Audit or source entities.

### P30 — Search / Discovery / Matching

- **R:** `34 Search`.
- **C:** `6 structured source/query`, `9` presentation.
- **O:** `33 search analytics`, `35 matching/ranking decisions`, `42 geo`, `44 search redirect`, `38 merchandising placement`.
- Rule: source Policy reauthorization occurs at result delivery.

### P31 — Rules / Scoring / Eligibility

- **R:** `35 Decision`, shared Conditional Logic/DVR/`S05` types.
- **C:** `30 Policy` for any protected action.
- **O:** `33 analytics`, `43 explanation/draft`, `17 workflow`.
- Rule: score/eligibility output != authority; protected mutation always goes through owner Policy/Ability.

### P32 — Geospatial / Territory / Service Area

- **R:** `42 Geo`.
- **C:** `3` location field references, `6` structured query/filter.
- **O:** `34 geo search`, `37 scheduling/location`, `23 provider connection`, `35 territory decision`, `38 placement`.
- Rule: geocoder confidence/location match != verified identity or guaranteed serviceability.

### P33 — Portal / Self-Service Application Shell

- **R:** `13 Dashboard`.
- **C:** `14 Profile`, `9 Listings`, `16 Components`, `17 Forms`, `30 Policy`, `19/21` communications.
- **O:** `15 Membership`, `54 user stores`, `38 placement`, `39 experiments`.
- Rule: Portal is presentation/composition; it never owns underlying records/permissions.

### P34 — Notification / Action Center

- **R:** `19 Notifications`.
- **C:** `13` action-center UI, `17 Workflow`, `30 Policy`.
- **O:** `20 Email`, `23 Webhook/channel transport`, `21 Chat`, `33 analytics`.
- Rule: webhook delivery transport delegates to Connections; email render delegates to Emails.

### P35 — Review / Moderation / UGC

- **R:** `17 Forms/Workflow`, `3`, `5`, `30`.
- **C:** `28 Media`, `9`, `19`.
- **O:** `35 moderation scoring`, `52 scanner evidence`, `33 analytics`, `54 helpful/favorite state when applicable`.
- Rule: automated score/finding never silently publishes/rejects protected content without accepted policy.

### P36 — Survey / Feedback / NPS-like Program

- **R:** `17 Forms`, `33 Analytics` for program metrics.
- **C:** `19`, `20`, `30`.
- **O:** `35 scoring/segments`, `13 respondent portal`, `43 summaries`.
- Rule: response storage and behavioral aggregate ownership remain distinct.

### P37 — Asset Assignment / Custody

- **R:** P01 asset data + `4 Relations`, `5 Status`, `17 Workflow`, `30`.
- **C:** `13` self-service/manager portal.
- **O:** `36` quantity/value movement, `40` acknowledgement docs, `42` location.
- Rule: relation/custody assignment != stock quantity movement unless Ledger posting also exists.

### P38 — Compliance / Policy Register

- **R:** `3`, `4`, `5`, `17`, `30` plus P04/P16/P15 compositions as required.
- **C:** `19`, `40` evidence/records.
- **O:** `35 risk scoring`, `33 analytics`, `52 scanner refs`, `43 drafts/summaries`.
- Rule: product "policy" records are business content and do not replace WPE Authorization Policy Engine.

### P39 — Queue / Dispatch / Assignment

- **R:** `6 Query`, `5 Status`, `17 Workflow`, `30`.
- **C:** `9` queue UI.
- **O:** `35 prioritization`, `37 scheduling/capacity`, `42 territory`, `19 alerts`, `51 explicit manual queue order`.
- Rule: scoring proposes priority; Workflow/Status/Policy executes assignment/state mutation.

### P40 — Subscription / Recurring Lifecycle Orchestration

- **R:** `15 Membership` for WPE entitlement/lifecycle when access is involved, `23 Connections` and certified external billing adapter/`A01` for provider truth, `17 Workflow`.
- **C:** `19`, `20`, `30`.
- **O:** `41 reconciliation sync`, `36 internal credits only where separate product semantics exist`, `33 analytics`.
- **EXT:** billing/subscription/payment authority.
- Rule: provider webhook/status is normalized/reconciled; it does not directly grant authorization.

## 4. System-level no-bypass rule

For every S001…S160 system:

1. system-specific objects are declared against canonical Data Sources;
2. system-specific fields use shared Field Schema/Surface 3;
3. relationships use Surface 4;
4. lifecycle state uses Surface 5 when a state machine is required;
5. structured data retrieval uses Surface 6; discovery/search uses Surface 34;
6. rendering uses Surface 9/13/16 as applicable;
7. forms/workflows use Surface 17;
8. HTTP/provider/webhooks use Surface 23;
9. authorization uses WordPress capability + shared Policy; role definitions use Surface 30;
10. external truth remains adapter/provider-owned;
11. no system may invent a private table simply because its name contains CRM/ERP/LMS/ATS/helpdesk unless that table is created through a canonical source owner and its semantics are not already owned by a foundation;
12. no Blueprint-owned shadow definitions.

## 5. 160-system coverage rule

`UNIVERSAL-SYSTEM-CATALOG.md` currently references only P01…P40 plus explicit optional foundations/adapters. Therefore this map provides a complete semantic routing layer for the current **160/160 curated systems**.

A catalog validation task must fail if a future system references:
- an unknown Pattern ID;
- an unknown surface ID;
- an option that cannot be assigned to an owner;
- a private runtime not registered in the canonical 56 ownership registry;
- an external authority without an adapter/reconciliation boundary.

## 6. Blueprint compiler output requirement

A compiled Solution Blueprint must materialize a **composition manifest** containing, at minimum:

- system key/version;
- Pattern IDs;
- required/optional Surface IDs;
- definition types + owner Surface IDs;
- cross-definition dependency edges;
- shared-service dependencies;
- domain/provider adapters;
- external-authority declarations;
- option variables and target owner paths;
- role/capability/Policy bindings;
- routes/screens and rendering owners;
- events/workflows/notifications;
- migration/import/seed plan;
- install order;
- degraded behavior;
- uninstall/detach behavior.

If the compiler cannot produce this manifest without inventing a new semantic owner, installation planning stops and opens a capability-gap review.

## 7. User flow containment

Every user flow in every system MUST reduce to:

`Entry Surface → Context/Principal → canonical read owners → Policy → canonical decision/state owner → canonical mutation Ability → side-effect owners → presentation owner → audit/observe`

There is no valid path of the form:

`System custom screen → private system logic/table/provider → side effect`

unless a separately accepted future surface/adapter explicitly establishes that ownership.
