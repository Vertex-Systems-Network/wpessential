# WPEssential — Reference Flow & Option Patterns

Status: **Phase 0 planning / reusable Solution Blueprint grammar / no development authorization**  
Date: 2026-08-28

## Purpose

Thousands of business systems repeat a much smaller number of trustworthy application patterns. This document defines those patterns once so curated Solution Blueprints can reuse them without duplicating or drifting from platform semantics.

Each pattern specifies its minimum objects, flow, screens/options, WPE composition and common failure/security rules.

---

# P01 — Master Data Registry / CRUD Directory

Use for contacts, vendors, properties, assets, products, facilities, alumni, equipment, policies and other governed records.

**Objects:** Record, Category/Taxonomy, Field Schema, Relation, Attachment, Revision.

**Flow:** `List/Search → Create Draft → Validate → Policy → Save → Relate → Publish/Activate → Observe → Update/Archive`.

**Screens/options:** list columns, saved filters, search, sort, pagination, bulk actions, create/edit tabs, custom fields, relationships, status, ownership, tags, attachments, duplicate, revision, import/export, audit, archive/delete policy.

**Composition:** DATA + STATE + QUERY + UI + ROLES + AUDIT; PORTAL optional.

**Failure/security:** IDOR checks, field-level policies, duplicate key handling, stale edits, referential delete policy, privacy/retention.

---

# P02 — Pipeline / CRM Opportunity

Use for leads, sales opportunities, admissions, recruiting candidates, fundraising opportunities and partner deals.

**Objects:** Person/Organization, Opportunity, Stage, Activity, Note, Task, Source, Value/Score.

**Flow:** `Capture → Qualify → Assign → Stage Progression → Activity/Follow-up → Proposal/Decision → Won/Lost/Closed → Nurture/Report`.

**Screens/options:** Kanban/list, stage definitions, stage probabilities, owners, source, value, next action, due date, custom fields, required stage fields, stale threshold, reassignment, loss reason, duplicate merge, activity timeline, conversion rules, dashboards.

**Composition:** DATA + REL + STATE + QUERY + UI + FORMS + FLOW + COMMS + PORTAL/ROLES.

**Optional foundations:** F04 scoring, F02 analytics, F09 documents.

**Failure/security:** no AI autonomous credit/eligibility decisions; protected notes; stage transition permissions; owner/team visibility.

---

# P03 — Case / Ticket / SLA Management

Use for helpdesk, complaints, legal cases, service requests, incidents, returns, claims and quality issues.

**Objects:** Case, Requester, Assignee/Team, Priority, SLA, Message/Note, Attachment, Resolution, Related Resource.

**Flow:** `Intake → Classify → Prioritize → Assign → Investigate/Communicate → Action/Approval → Resolve → Verify → Close/Reopen`.

**Screens/options:** queues, status, severity, SLA clocks, assignment, escalation, categories, reasons, tags, internal/public notes, attachments, watchers, canned replies, linked entities, resolution codes, reopen policy, satisfaction survey.

**Composition:** DATA + STATE + QUERY + FORMS + FLOW + COMMS + CHAT + UI + PORTAL + ROLES.

**Optional foundations:** F02 SLA analytics, F09 documents.

**Failure/security:** requester cannot see another case; SLA pause semantics explicit; attachment policy; no silent auto-denial from AI risk flags.

---

# P04 — Approval / Authorization Workflow

Use for expenses, leave, purchase orders, quotes, content, contracts, access requests and high-risk changes.

**Objects:** Request, Approval Policy, Approval Step, Decision, Approver, Delegation, Evidence.

**Flow:** `Draft → Submit → Resolve Approval Policy → Sequential/Parallel Review → Approve/Reject/Return → Execute Allowed Action → Audit`.

**Screens/options:** thresholds, approver by role/relation/query, quorum, sequential/parallel, conditional routes, delegation, absence backup, due dates, reminders, escalation, re-auth, comments, attachments, withdrawal, resubmit, immutable decision history.

**Composition:** FORMS + WORKFLOW + ROLES/POLICY + STATE + COMMS + AUDIT.

**Foundation:** S06 Approval Policy Profile.

**Failure/security:** requester cannot approve own request unless policy explicitly permits; race-safe quorum; revocation/delegation provenance; decision history immutable.

---

# P05 — Scheduling / Booking / Reservation

Use for appointments, rooms, equipment, rentals, staff, classes, delivery slots and interviews.

**Objects:** Resource, Calendar, Availability Rule, Slot, Reservation, Hold, Attendee, Location.

**Flow:** `Context → Availability Query → Select Resource/Slot → Atomic Hold → Collect Form/Payment/Approval → Confirm → Remind → Check-in/Use → Complete/Cancel/Reschedule`.

**Screens/options:** resource types, capacities, schedules, timezone, recurrence, exceptions, blackout, buffers, lead time, booking horizon, duration, capacity pools, waitlist, approval, deposits, cancellation window, reschedule limit, reminders, external calendar sync.

**Composition:** F06 + DATA + FORMS + FLOW + COMMS + PORTAL; F11 location optional.

**Failure/security:** no double-booking; hold expiry; payment unknown outcome reconciliation; timezone/DST; private attendee data.

---

# P06 — Ledger / Credits / Balance

Use for wallet, loyalty, leave balance, inventory, commission, quota, prepaid credits and points.

**Objects:** Ledger, Account, Transaction, Posting, Hold, Balance Snapshot, Reconciliation.

**Flow:** `Posting Request → Policy → Idempotency → Validate Unit/Accounts → Reserve/Commit → Append Entry → Balance Projection → Event/Audit → Reconcile`.

**Screens/options:** units/currency, precision, account types, transaction reasons, negative policy, expiry, holds, reversal, manual adjustment approval, statements, filters, reconciliation, export.

**Composition:** F05 + S05 + FLOW + AUDIT + QUERY + UI + ROLES.

**Failure/security:** never direct-overwrite balance; immutable source references; duplicate posting prevention; cached balance non-authoritative.

---

# P07 — Inventory / Movement / Warehouse

Use for stock, spare parts, tools, library copies, medical supplies, food inventory and internal assets.

**Objects:** Item, Location/Bin, Stock Account, Movement, Reservation, Count, Transfer, Lot/Serial optional.

**Flow:** `Receive/Adjust/Reserve/Move/Consume/Return → Append Movement → Recalculate Available → Exception/Reconcile → Report`.

**Screens/options:** locations, bins, on-hand/reserved/available/incoming/damaged/quarantine, transfers, count sessions, lot/serial, reorder point, negative policy, adjustment reasons, approvals, barcode identifiers.

**Composition:** F05 + DATA + REL + STATE + QUERY + FORMS + FLOW + UI.

**Failure/security:** all quantity changes are movements; concurrency-safe reservations; count variance approval; no silent negative stock.

---

# P08 — Procurement / Purchase Order

Use for supplier purchasing, internal procurement, replenishment and project purchasing.

**Objects:** Supplier, Supplier Item, Purchase Request, PO, PO Line, Receipt, Cost, Invoice Reference.

**Flow:** `Need/Suggestion → Purchase Request → Approval → PO Draft → Send → Supplier Acknowledge → Partial/Full Receive → Variance → Close`.

**Screens/options:** supplier SKU, MOQ, lead time, currency, terms, cost, approval thresholds, attachments, expected date, partial receipts, backorders, landed cost, variance reason, cancel/close.

**Composition:** P01 + P04 + P07 + PORTAL + COMMS; F09 for generated POs optional.

**Failure/security:** duplicate PO send/idempotency; receipt never updates stock without ledger movement; supplier access restricted to owned POs.

---

# P09 — Project / Task / Work Management

Use for agency work, engineering projects, implementation plans, construction tasks and team operations.

**Objects:** Project, Milestone, Task, Dependency, Assignee, Checklist, Time Entry optional, File, Comment.

**Flow:** `Create Project → Plan Milestones/Tasks → Assign → Work/Update → Dependency/Approval → Complete → Review/Archive`.

**Screens/options:** boards/lists/calendar, priority, status, owner, due date, dependencies, recurrence, checklist, labels, watchers, attachments, comments, task templates, workload, blocked reason, completion criteria.

**Composition:** DATA + REL + STATE + QUERY + UI + FORMS + FLOW + COMMS + PORTAL.

**Optional foundations:** F06 for capacity/calendar, F02 analytics.

**Failure/security:** dependency cycles; visibility by project/team/client; archived project mutation policy.

---

# P10 — Form Intake / Application / Admissions

Use for applications, registrations, onboarding, claims, surveys, wholesale requests and intake.

**Objects:** Form Definition, Submission, Applicant/Subject, Review, Attachment, Decision.

**Flow:** `Open Form → Validate Eligibility → Save/Resume → Submit → Screen → Review/Approval → Decision → Notify → Downstream Record Creation`.

**Screens/options:** multi-step, conditional fields, files, consent, save/resume, deadlines, entry caps, applicant portal, reviewer notes, scoring, status, duplicate detection, export.

**Composition:** FORMS + DATA + STATE + FLOW + PORTAL + COMMS + ROLES.

**Optional foundations:** F04 scoring, F09 generated decision documents.

**Failure/security:** private drafts; secure resume tokens; malicious uploads; reviewer/applicant field separation; deadline/timezone truth.

---

# P11 — Membership / Enrollment / Entitlement

Use for clubs, courses, paid content, partner programs, licenses and role-independent access plans.

**Objects:** Plan, Enrollment, Entitlement, Policy, Benefit, External Billing Reference optional.

**Flow:** `Eligibility/Commercial Fact → Enrollment State → Derive Entitlement → Policy Check → Benefit/Resource Access → Renew/Pause/Revoke/Expire → Reconcile`.

**Screens/options:** plans, durations, grace, trials, benefits, access rules, manual grants, team/seat rules, lifecycle, billing adapters, portal, protected resources, notifications.

**Composition:** MEMBERSHIP + PORTAL + FLOW + COMMS + API + ROLES/POLICY.

**Failure/security:** billing provider status never directly authorizes; role != membership; revocation cache generation; expiry preserves safe historical data.

---

# P12 — Course / Learning Program

Use for training, LMS-lite, certification, internal academies and onboarding curricula.

**Objects:** Course, Module, Lesson, Enrollment, Progress, Assessment, Submission, Certificate.

**Flow:** `Browse/Assign → Enroll → Consume Lesson → Complete Activity → Assess → Progress Gate → Complete → Certificate/Next Course`.

**Screens/options:** curriculum ordering, prerequisites, drip dates, progress rules, required lessons, quiz/assignment types, attempts, pass threshold, grading, instructor, cohorts, certificates, discussions, completion expiry.

**Composition:** DATA + REL + PORTAL + MEMBERSHIP + STATE + FORMS + FLOW + QUERY + UI + COMMS.

**Optional foundations:** F04 scoring, F09 certificate, F02 learning analytics.

**Failure/security:** attempts/grading permissions; protected content; certificate reflects verified completion, not editable profile values.

---

# P13 — Directory / Marketplace Listing

Use for businesses, professionals, vendors, properties, jobs, services, members and resources.

**Objects:** Listing, Owner, Category, Location, Media, Availability/Offer optional, Review/Inquiry.

**Flow:** `Create/Claim → Validate/Moderate → Publish → Search/Filter → View → Inquiry/Action → Update/Renew/Expire`.

**Screens/options:** listing schema, ownership, categories, attributes, media, location, search/filter, featured/pin, moderation, expiry, claim flow, contact form, privacy, review integration, import.

**Composition:** DATA + REL + QUERY + LISTINGS + FORMS + PORTAL + STATE + COMMS.

**Optional foundations:** F03 advanced search, F11 geo, F07 placement.

**Failure/security:** claim ownership proof; public field allowlist; spam; expired listing cache invalidation.

---

# P14 — Marketplace / Multi-Party Transaction Coordination

Use for vendor marketplaces, service providers, commissions and multi-party fulfillment where Woo/external commerce is involved.

**Objects:** Vendor, Offer/Listing, Order/Request, Allocation, Commission, Payout Reference, Dispute.

**Flow:** `Vendor Onboard → Publish Offer → Buyer Action → Allocate → Provider Fulfill → Commission Posting → Payout Export/Authority → Refund/Dispute Reconcile`.

**Composition:** P13 + P06 + FLOW + PORTAL + A01/external commerce adapter.

**Options:** vendor approval, commission formula, catalog ownership, order visibility, fulfillment SLA, payout schedule/export, refunds, disputes, tax-doc refs, product moderation.

**Failure/security:** WPE ledger != bank payout; provider payout authority external; cross-vendor order privacy.

---

# P15 — Document / Record Lifecycle

Use for proposals, contracts, certificates, letters, reports, invoices, permits and compliance records.

**Objects:** Template, Document, Version, Approval, Attachment, Signature Reference, Retention Policy.

**Flow:** `Create/Generate Draft → Validate Data → Review → Approve → Render Immutable Version → Deliver/Sign Externally if needed → Archive/Expire`.

**Screens/options:** template, numbering, dynamic fields, sections/tables, locale, PDF settings, access, approval, signature adapter, versioning, retention, watermark, delivery, regeneration policy.

**Composition:** F09 + DATA + FLOW + ROLES + PROTECTED ASSET + COMMS.

**Failure/security:** generated != legally signed; immutable issued version; private document origin protected.

---

# P16 — Inspection / Checklist / Audit

Use for QA, property inspections, safety, compliance, maintenance, warehouse counts and field audits.

**Objects:** Inspection Template, Inspection Run, Section, Check, Finding, Evidence, Corrective Action.

**Flow:** `Schedule/Trigger → Assign → Conduct Checks → Capture Evidence → Score/Findings → Corrective Actions → Verify → Close/Report`.

**Screens/options:** checklist types, yes/no/score/text/media, required evidence, conditional sections, location, offline strategy if future adapter, signatures, severity, due date, repeat findings, corrective workflow, report.

**Composition:** FORMS + DATA + REL + STATE + FLOW + MEDIA + PORTAL + COMMS.

**Optional foundations:** F04 scoring, F09 report, F06 scheduling, F11 location.

---

# P17 — Maintenance / Work Order / Field Service

Use for equipment maintenance, facilities, fleet, property and service organizations.

**Objects:** Asset, Work Order, Job Type, Technician, Parts, Checklist, Visit, Finding, Cost.

**Flow:** `Request/Preventive Trigger → Triage → Schedule/Assign → Perform → Consume Parts/Record Work → QA/Customer Signoff → Close → Next Service`.

**Screens/options:** asset link, priority, SLA, skill/team, appointment, checklist, parts, labor/time, attachments, customer/site, recurring maintenance, warranty, cost, completion proof.

**Composition:** P03 + P05 + P07 + P16 + PORTAL.

**Failure/security:** technician only sees assigned/customer-authorized data; parts consumption through ledger; recurring schedule vs reservation distinction.

---

# P18 — Recruitment / Candidate Tracking

Use for ATS, volunteer recruitment, admissions interviews and talent pools.

**Objects:** Position, Candidate, Application, Stage, Interview, Evaluation, Offer Document.

**Flow:** `Publish/Open → Apply → Screen → Stage → Interview → Evaluate → Approve → Offer/Reject → Onboard/Archive`.

**Screens/options:** job/position schema, application form, source, stage, owner, interview schedule, scorecard, consent, duplicate candidates, retention, offer approval, talent pool.

**Composition:** P02 + P10 + P05 + P04 + P15.

**Failure/security:** sensitive candidate data; retention/erase; AI ranking advisory unless explicit lawful policy; interview conflict prevention.

---

# P19 — HR Employee Lifecycle

Use for employee master records, onboarding, changes and offboarding.

**Objects:** Employee, Position, Department, Manager Relation, Employment State, Checklist, Document, Asset Assignment.

**Flow:** `Hire/Create → Onboarding Tasks/Documents/Access → Active Changes → Reviews/Requests → Offboarding Approval → Revoke/Return Assets → Archive/Retention`.

**Screens/options:** employee fields, org structure, manager, employment dates/status, emergency/private fields, onboarding templates, documents, role/account provisioning hooks, offboarding checklist, retention.

**Composition:** P01 + P04 + P09 + P15 + ROLES + PORTAL + FLOW.

**Failure/security:** HR-private field policy; WordPress account != employment truth; offboarding cannot silently delete records needed for retention.

---

# P20 — Leave / Absence / Balance

Use for vacation, sick leave, study leave, volunteer allocation and time-off banks.

**Objects:** Leave Type, Balance Account, Request, Approval, Calendar Entry, Accrual Posting.

**Flow:** `Accrue/Grant → Request Dates → Validate Balance/Overlap → Approval → Reserve/Deduct → Calendar/Notify → Cancel/Reverse`.

**Screens/options:** leave types, accrual rules, carryover, expiry, negative limits, half days, holidays, approval, blackout, team calendar, attachments, reasons/private visibility.

**Composition:** P04 + P05 + P06 + PORTAL.

**Failure/security:** balance through ledger; timezone/calendar overlap; sensitive reason visibility.

---

# P21 — Time Tracking / Timesheet

Use for projects, service delivery, payroll inputs, volunteers and billing support.

**Objects:** Time Entry, Timer Session, Project/Task, User, Approval Period, Rate Reference.

**Flow:** `Start/Manual Entry → Validate Context → Stop/Submit → Review/Approve → Lock Period → Export/Report/Invoice Reference`.

**Screens/options:** timer/manual, rounding, min/max, billable, project/task, notes, attachments, approval periods, edit lock, overtime classification, rate visibility, exports.

**Composition:** DATA + REL + FORMS + FLOW + PORTAL + QUERY + UI + P04.

**Optional foundation:** F04 calculations, F02 analytics.

---

# P22 — Expense / Reimbursement

Use for staff expenses, project costs, travel claims and petty cash requests.

**Objects:** Expense, Line, Receipt, Category, Approval, Payment/Reimbursement Reference.

**Flow:** `Draft Claim → Add Lines/Receipts → Validate Policy → Submit → Approval → Finance Review → External Reimbursement → Reconcile/Close`.

**Screens/options:** currency, date, category, tax fields, project/cost center, receipt, mileage/unit formulas, policy limits, approval thresholds, status, export.

**Composition:** FORMS + DATA + P04 + F09 optional; external finance/payment authority.

**Failure/security:** approval != actual payment; sensitive receipts; immutable approved snapshot.

---

# P23 — Event Registration / Attendance

Use for conferences, classes, community events, workshops and internal sessions.

**Objects:** Event, Session, Venue/Resource, Registration, Attendee, Ticket/Pass, Attendance.

**Flow:** `Publish Event → Register → Eligibility/Capacity → Confirm/Payment if needed → Remind → Check-in → Attendance → Follow-up/Certificate`.

**Screens/options:** dates/timezones, capacity, waitlist, ticket types, fields, guests, QR/code adapter, cancellation, session choices, reminders, attendance, certificates.

**Composition:** DATA + F06 + FORMS + PORTAL + FLOW + COMMS + F09 optional.

---

# P24 — Content Editorial / Publishing Workflow

Use for magazines, blogs, knowledge teams, policy publishing and agency content operations.

**Objects:** Content Item, Brief, Assignment, Review, Revision, Publication Schedule, Campaign Link.

**Flow:** `Idea/Brief → Assign → Draft → Editorial/Legal Review → Approve → Schedule/Publish → Measure → Update/Archive`.

**Screens/options:** content types, stages, owners, due dates, review roles, revision comparisons, SEO/meta fields, attachments, embargo, schedule, checklist, campaign.

**Composition:** CPT/FIELDS + STATE + REL + P04 + CRON + FLOW + UI.

---

# P25 — Knowledge Base / Documentation Portal

Use for help centers, internal docs, SOPs, product docs and developer documentation.

**Objects:** Article, Category, Version, Audience, Feedback, Search Document.

**Flow:** `Author → Review/Publish → Index → Search/Browse → Feedback → Update/Version/Retire`.

**Screens/options:** hierarchy, version, audience/policy, related docs, feedback, helpfulness, redirects, expiry/review date, attachments, changelog.

**Composition:** DATA + UI + PORTAL + P24; F03 for advanced search, F12 for grounded assistant optional.

---

# P26 — API / Developer Portal

Use for API catalogs, integration docs, keys/app registrations and developer onboarding.

**Objects:** API Definition, Endpoint, App/Client, Credential Reference, Scope, Subscription, Usage Metric.

**Flow:** `Publish API Docs → Developer Onboard → Request/Create App → Approve/Issue Credential Through Adapter → Call → Observe Quota/Errors → Rotate/Revoke`.

**Screens/options:** API versions, endpoints/schemas, auth method, scopes, rate plans, app registration, test console, webhook subscriptions, changelog, deprecation, usage dashboard.

**Composition:** REST + CONNECTIONS + VAULT + RATE LIMIT + DOC PORTAL + PORTAL + AUDIT.

**Failure/security:** never reveal stored secrets; app credential authority explicit; API policy separate from documentation visibility.

---

# P27 — Incident / Problem / Change Management

Use for IT incidents, production operations, security operations and service management.

**Objects:** Incident, Problem, Change, Service, Severity, Timeline, Action, Postmortem.

**Flow:** `Detect/Report → Triage → Assign → Contain → Resolve/Recover → Verify → Postmortem → Preventive Actions`.

**Screens/options:** severity, service, owner, status, timeline, communication, runbook, impacted users, linked changes, RCA, corrective actions, review date.

**Composition:** P03 + P09 + P25 + NOTIFICATIONS + AUDIT.

**Optional foundations:** F02 analytics/telemetry, F09 postmortem report.

---

# P28 — Data Import / Sync / ETL

Use for external directories, ERP, CRM, product feeds and recurring data exchange.

**Objects:** Sync Definition, Connector, Mapping, Cursor, Run, Conflict, Dead Letter.

**Flow:** `Connect → Map → Dry Run → Initial Sync → Persist Cursor → Incremental Runs → Conflict/Reconcile → Monitor`.

**Screens/options:** one/two-way, keys, field ownership, transformations, schedule/event, batch, cursor, create/update/delete policy, conflict mode, retry, rate, privacy, dead letters.

**Composition:** F10 + IMPORT/EXPORT + CONNECTIONS + CRON + FLOW + AUDIT.

---

# P29 — Analytics / Funnel / Cohort

Use for commerce, SaaS-like behavior, learning, portals, marketing and operations.

**Objects:** Event, Session, Metric, Funnel, Cohort, Dashboard, Alert.

**Flow:** `Collect → Consent/Schema Validate → Store → Identity Link → Aggregate → Query/Funnel → Compare → Alert/Explain`.

**Screens/options:** event schema, retention, sampling, metrics, dimensions, funnels, conversion windows, cohorts, attribution, date compare, alert threshold, scheduled reports.

**Composition:** F02 + QUERY + DASHBOARDS + COMMS.

---

# P30 — Search / Discovery / Matching

Use for catalogs, jobs, candidates, properties, docs, directories and knowledge retrieval.

**Objects:** Search Index, Document, Analyzer, Synonym Set, Ranking Profile, Search Rule.

**Flow:** `Index Source → Normalize/Analyze → Secure Query → Filter/Facet → Score/Rank → Render → Log Feedback → Reindex`.

**Screens/options:** fields/weights, language, synonyms, typo, facets, filters, boosts, pins, redirects, autosuggest, security filters, incremental index, backend adapter, zero-result analytics.

**Composition:** F03 + QUERY + UI + F02 optional.

---

# P31 — Rules / Scoring / Eligibility

Use for lead scoring, admissions, routing, shipping, pricing, risk, grades and recommendations.

**Objects:** Formula, Scorecard, Decision Table, Threshold, Evaluation Trace.

**Flow:** `Resolve Inputs → Type/Policy Validate → Evaluate → Explain Trace → Threshold/Rank → Consumer Policy/Action`.

**Screens/options:** variables, weights, formula functions, lookup tables, null policy, rounding, effective dates, simulation, thresholds, priorities, conflict policy.

**Composition:** F04 + CONDITIONAL + DVR + S05.

---

# P32 — Geospatial / Territory / Service Area

Use for real estate, delivery, field service, stores, healthcare locations and sales territories.

**Objects:** Address, Location, Territory, Zone, Geometry, Provider Geocode Ref.

**Flow:** `Capture Address/Coordinate → Normalize/Geocode → Policy/Precision → Match Radius/Zone/Territory → Use in Query/Assignment → Cache/Refresh`.

**Screens/options:** provider, address schema, coordinates, radius, units, polygon, hierarchy, timezone, privacy precision, quotas, caching, import/export.

**Composition:** F11 + FIELDS + QUERY + CONNECTIONS.

---

# P33 — Portal / Self-Service Application Shell

Use for customers, employees, students, suppliers, members, vendors and partners.

**Objects:** Dashboard Definition, Route, Navigation, Widget/Component, Principal Context.

**Flow:** `Authenticate/Resolve Context → Route Policy → Render Role-Specific Navigation/Widgets → Execute Typed Actions → Notify/Audit`.

**Screens/options:** routes, nested menu, role/capability/segment visibility, widgets, forms, listings, profile, notifications, chat, breadcrumbs, responsive shell, denied/empty/error states.

**Composition:** FRONTEND DASHBOARD + PROFILE + LISTINGS + BUILDER WIDGETS + FORMS + ROLES/POLICY + COMMS.

---

# P34 — Notification / Action Center

Use for operational alerts, approvals, marketing notifications, inventory, support and system health.

**Objects:** Notification Occurrence, Recipient State, Action Item, Escalation, Delivery Attempt.

**Flow:** `Event → Normalize Alert → Resolve Recipients/Priority → Deliver/In-App → Acknowledge/Snooze/Act → Escalate → Resolve`.

**Screens/options:** categories, severity, owner, due date, channels, preferences, quiet hours, digest, snooze, escalation, bulk resolve, linked object/action.

**Composition:** NOTIFICATION + DASHBOARD + FLOW + ROLES + EMAIL/WEBHOOK.

---

# P35 — Review / Moderation / UGC

Use for product reviews, directory reviews, testimonials, community submissions and media moderation.

**Objects:** Review/Submission, Subject, Author, Rating, Media, Moderation Decision, Vote/Helpful State.

**Flow:** `Eligibility → Submit → Spam/Validation → Moderate → Publish/Reject → Reply/Vote → Flag/Appeal → Analyze`.

**Screens/options:** rating schema, verified context, text/media, anonymous policy, moderation, profanity/spam adapters, incentives, voting, replies, report abuse, retention/export.

**Composition:** FORMS + DATA + MEDIA + STATE + FLOW + COMMS + UI.

---

# P36 — Survey / Feedback / NPS-like Program

Use for satisfaction, employee surveys, course feedback, customer research and inspections.

**Objects:** Survey, Question, Response, Respondent, Campaign/Distribution, Score/Segment.

**Flow:** `Define → Target/Invite → Respond → Validate/Anonymize → Aggregate → Analyze → Follow-up/Case`.

**Screens/options:** question types, branching, anonymity, consent, quotas, schedule, invite channels, reminders, scoring, exports, text analysis, follow-up thresholds.

**Composition:** FORMS + FLOW + COMMS + QUERY + F02; F04 for scoring.

---

# P37 — Asset Assignment / Custody

Use for laptops, tools, uniforms, vehicles, keys, documents and equipment custody.

**Objects:** Asset, Custodian, Assignment, Transfer, Condition, Checkout/Return, Evidence.

**Flow:** `Register Asset → Assign/Issue → Custody Period → Transfer/Inspect → Return → Condition/Exception → Archive/Dispose`.

**Screens/options:** asset IDs, serials, category, condition, location, owner, assignee, dates, expected return, accessories, photos, acknowledgements, lost/damaged, maintenance links.

**Composition:** P01 + REL + STATE + FORMS + FLOW + PORTAL; F05 optional for quantity assets.

---

# P38 — Compliance / Policy Register

Use for policies, controls, risks, evidence, certifications and regulatory obligations.

**Objects:** Requirement, Policy, Control, Risk, Evidence, Review, Finding, Remediation.

**Flow:** `Register Requirement → Map Policy/Control → Assign Owner → Collect Evidence → Review/Test → Finding → Remediate → Reassess/Expire`.

**Screens/options:** frameworks, categories, owners, review frequency, control status, risk rating, evidence files, attestation, exceptions, due dates, audit history.

**Composition:** DATA + REL + STATE + P04 + P16 + P15 + COMMS.

---

# P39 — Queue / Dispatch / Assignment

Use for service jobs, leads, inspections, support, deliveries and moderation workloads.

**Objects:** Work Item, Queue, Assignee, Skill/Rule, Priority, SLA, Assignment History.

**Flow:** `Work Arrives → Qualify/Priority → Select Queue → Assignment Rule/Manual Claim → Work → Requeue/Escalate → Complete`.

**Screens/options:** queue conditions, skills, capacity, priority score, round-robin/manual, ownership lock, claim timeout, escalation, workload limits, reassign reason.

**Composition:** DATA + QUERY + FLOW + ROLES + UI + F04 optional scoring.

---

# P40 — Subscription / Recurring Lifecycle Orchestration

Use where an external billing or recurring authority exists.

**Objects:** External Contract Ref, Normalized Subscription State, Renewal Attempt, Change Request, Dunning Flow.

**Flow:** `Provider Fact → Verify/Reconcile → Normalize State → Entitlement/Service Effects → Self-Service Change → Provider Action → Unknown Outcome/Reconcile → Renewal/Cancel`.

**Screens/options:** pause/skip/change/cancel allowed actions, effective timing, grace, failed payment, dunning, retention reason, portal, provider profile.

**Composition:** MEMBERSHIP/STATE + FLOW + PORTAL + CONNECTIONS + external billing adapter.

**Failure/security:** provider is commercial source of truth; no exactly-once external claim; entitlement derived, not provider webhook direct authorization.

---

## Pattern composition rule

A Solution Blueprint may combine multiple patterns. Example:

**Property Management System** = P01 Property Registry + P02 Lead Pipeline + P05 Viewing Reservation + P03 Maintenance Cases + P15 Lease Documents + P33 Tenant Portal + P29 Analytics.

The Blueprint must still define exact entity ownership, option overrides, actor policies and inter-pattern transitions.

## Development gate

Patterns are documentation primitives only. They do not create runtime modules, tables, routes or provider calls.