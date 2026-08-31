# WPEssential — Universal System Catalog

Status: **Phase 0 planning / curated reference blueprints / no development authorization**  
Date: 2026-08-28  
Catalog size: **160 reference systems across 20 domains**.

## How to read this catalog

Each row is a **Solution Blueprint candidate**, not a new plugin/module. `Patterns` reference `REFERENCE-FLOW-AND-OPTION-PATTERNS.md`. The system inherits those detailed flows/options and adds the domain-specific objects/options shown here.

Abbreviations:
- `D` = current WPE data/schema/query/listing primitives;
- `F` = Forms/Workflow/Cron;
- `P` = Portals/Profile/Roles/Membership;
- `C` = Notifications/Email/Chat;
- `I` = REST/Webhooks/Connections/Import-Export;
- `X` = proposed reusable foundation(s) F01–F12 / S01–S06;
- external authorities remain adapters and never become inferred WPE truth.

---

# 1. CRM, Sales & Growth — S001–S008

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S001 | Lead CRM & Sales Pipeline | P02,P33,P34 | sales reps/managers; Lead, Account, Opportunity, Activity | D+F+P+C; F04/F02 optional | capture lead → qualify → assign → stage → win/loss → nurture | stages, owners, source, value, next action, stale rules, loss reasons, duplicate merge, dashboards |
| S002 | Account & Contact 360 | P01,P33,P29 | sales/support; Contact, Organization, Relationship, Activity | D+P+C; F02 optional | create/import account → relate contacts → timeline → task/action → segment/report | account types, contact roles, visibility, tags, custom fields, relationship graph, timeline retention |
| S003 | Follow-up & Sales Cadence Manager | P02,P34,P39 | reps/managers; Cadence, Step, Prospect, Attempt | D+F+C; F02 | enroll prospect → scheduled steps → call/email/task → reply/stop → outcome | cadence templates, delays, quiet hours, stop rules, ownership, retries, consent, performance |
| S004 | Partner / Channel CRM | P01,P02,P04,P33 | partner managers/partners; Partner, Deal Registration, Tier | D+F+P+C | onboard partner → approve → register deal → protect ownership → progress → reward/report | tiers, territories, deal expiry, conflict rules, approvals, documents, portal visibility |
| S005 | Customer Success / Renewal Hub | P03,P29,P34,P40 | CSMs/customers; Account Health, Renewal, Risk, Action Plan | D+F+P+C; F02/F04 | monitor usage/events → health score → risk queue → playbook → renewal/expansion | scorecards, health bands, owners, renewal dates, tasks, escalations, success plans |
| S006 | Sales Commission Tracker | P06,P02,P04 | sales/finance; Commission Plan, Earning, Adjustment, Payout Ref | D+F; F05/F04/S05 | qualifying sale → calculate earning → hold → approve → payout export → reversal | commission formulas, splits, tiers, clawbacks, caps, periods, approval, statements |
| S007 | Marketing Campaign Operations Hub | P09,P24,P29,P34 | marketers/approvers; Campaign, Asset, Task, Channel, Budget | D+F+C; F02/F07/F08 | brief → tasks/assets → approval → launch → track → review | campaign types, calendars, budgets, ownership, approvals, channels, assets, KPIs |
| S008 | Referral & Advocate Management | P02,P06,P29 | customers/marketing; Advocate, Referral, Reward, Touch | D+F+P+C; F02/F04/F05 | referral touch → validate conversion → anti-abuse → reward → reversal/report | codes/links, validation delay, limits, reward types, fraud signals, attribution windows |

---

# 2. HR, People & Workforce — S009–S016

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S009 | Employee Directory & HRIS-lite | P19,P01,P33 | HR/managers/employees; Employee, Department, Position | D+P+F | employee record → org relation → self-service updates → lifecycle changes | org structure, private/public fields, manager relation, employment state, documents, retention |
| S010 | Employee Onboarding & Offboarding | P19,P09,P04,P15 | HR/IT/manager/employee; Checklist, Access Request, Asset | D+F+P+C; F09 | hire → assign onboarding tasks/docs/access → confirm → active → offboard → revoke/return | templates, responsible teams, due offsets, required evidence, access/asset tasks, exit approvals |
| S011 | Leave & Absence Manager | P20,P04,P05,P06 | employee/manager/HR; Leave Type, Request, Balance | D+F+P+C; F05/F06 | accrue → request → overlap/balance validation → approval → deduct → cancel/reverse | accrual, carryover, holidays, half days, blackout, team calendar, attachments, delegation |
| S012 | Recruitment / ATS | P18,P02,P10,P05 | recruiters/hiring managers/candidates; Job, Candidate, Interview | D+F+P+C; F04/F06/F09 | publish → apply → screen → interview → evaluate → offer/reject → onboard | stages, sources, screening fields, scorecards, interview panels, retention, offer approvals |
| S013 | Performance Review System | P10,P04,P31 | employee/manager/HR; Review Cycle, Goal, Feedback, Rating | D+F+P+C; F04 | launch cycle → self review → manager/peer inputs → calibration → signoff → goals | cycles, rating scales, competencies, reviewers, anonymity, calibration, goal weights, appeals |
| S014 | Shift & Roster Planner | P05,P39,P33 | schedulers/staff; Shift, Role, Location, Availability | D+P+C; F06/F11 | collect availability → build roster → conflict check → publish → swap/request → attendance handoff | shift types, skills, locations, availability, max hours, breaks, swap approval, reminders |
| S015 | Timesheet & Worklog System | P21,P04,P09 | staff/managers/finance; Time Entry, Period, Project | D+F+P; F04 optional | timer/manual entry → submit period → approve → lock → export/report | rounding, billable flag, overtime types, edit lock, approvals, project/task, notes, exports |
| S016 | Employee Asset Custody | P37,P16 | HR/IT/employee; Asset, Assignment, Condition, Return | D+F+P; F09 optional | register asset → issue → acknowledge → transfer/inspect → return → damaged/lost case | asset classes, serials, accessories, due date, condition photos, signatures, replacement workflow |

---

# 3. Education, Training & LMS — S017–S024

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S017 | Course & LMS Portal | P12,P11,P33 | learners/instructors/admin; Course, Lesson, Enrollment, Progress | D+F+P+C; F04/F02/F09 optional | enroll → learn → complete → assess → gate progress → certificate | curriculum, prerequisites, drip, attempts, completion, cohorts, certificates, access plans |
| S018 | Student Admissions System | P10,P02,P04,P15 | applicants/admissions/reviewers; Application, Program, Decision | D+F+P+C; F04/F09 | apply/save → submit → screen → review → interview → decision → enrollment | deadlines, eligibility, documents, reviewer assignment, scoring, quotas, decision letters |
| S019 | Assignment & Submission Manager | P10,P12,P15 | students/instructors; Assignment, Submission, Rubric, Grade | D+F+P+C; F04 | publish assignment → submit → validate deadline → grade/rubric → feedback → resubmit/close | attempt limits, due dates, late policy, files, rubrics, feedback, plagiarism adapter refs |
| S020 | Quiz & Assessment Engine Blueprint | P10,P31,P12 | learners/instructors; Quiz, Question, Attempt, Result | D+F+P; F04 | launch attempt → randomized questions → answer → submit → score → pass/fail → remediation | question types, pools, timers, attempts, randomization, pass threshold, feedback timing, accommodations |
| S021 | Training Compliance Tracker | P12,P38,P34 | employees/compliance/admin; Requirement, Course, Completion, Expiry | D+F+P+C; F09 | assign requirement → enroll → complete → evidence/certificate → expiry reminder → renew | mandatory groups, due dates, renewal periods, exemptions, manager visibility, certificates |
| S022 | School Timetable & Class Scheduling | P05,P23 | admin/teachers/students; Class, Room, Teacher, Timetable | D+P+C; F06 | define constraints → allocate room/teacher → conflict check → publish → change/notify | periods, rooms, teacher availability, class capacity, recurring calendar, exceptions, substitutions |
| S023 | Parent / Guardian Portal | P33,P01,P34 | guardians/school staff; Student Link, Notices, Documents, Requests | D+P+C+F | guardian verification → linked students → view approved data → submit requests/messages | relationship proof, field visibility, notices, attendance/grades adapters, consent, documents |
| S024 | Alumni & Alumni Engagement CRM | P01,P02,P23,P29 | alumni office/alumni; Alumnus, Cohort, Event, Donation Ref | D+F+P+C; F02 | import alumni → profile update → segment → event/campaign → engagement/report | graduation data, privacy, mentoring interests, events, communications, donations external refs |

---

# 4. Healthcare & Clinic Administration — S025–S032

These are **administrative systems**, not diagnostic/clinical decision engines.

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S025 | Appointment & Clinic Scheduling | P05,P33,P34 | patients/front desk/providers; Appointment, Resource, Location | D+F+P+C; F06/F11 | search slot → hold → intake → confirm → remind → arrive/cancel/reschedule | provider schedules, visit types, duration, buffers, locations, waitlist, reminders, cancellation |
| S026 | Patient Intake & Consent Workflow | P10,P15,P33 | patient/staff; Intake, Consent, Attachment, Review | D+F+P+C; F09/S03 | secure form → identity/context → consent/documents → staff review → approved downstream handoff | form versions, signatures refs, consent dates, file privacy, required fields, expiry/reconsent |
| S027 | Referral Coordination Tracker | P03,P02,P15 | coordinators/providers/patients; Referral, Source, Destination, Status | D+F+P+C | referral intake → triage → assign → appointment/docs → response → close | priority, specialty, required docs, SLA, external provider contacts, status, reminders |
| S028 | Non-clinical Patient Service Case Center | P03,P33,P34 | patients/service agents; Case, Request, Resolution | D+F+P+C | request → classify → assign → communicate → approved admin action → close | categories, SLA, privacy, attachments, escalation, satisfaction, linked appointment/account |
| S029 | Clinic Queue & Check-in Manager | P39,P05,P34 | front desk/providers/patients; Visit Queue, Check-in, Status | D+F+P+C; F06 | arrival/check-in → queue → prioritize by explicit policy → call → service → complete | queue types, priority reasons, estimated wait display, locations, no-show, notifications |
| S030 | Medical Supply Inventory Admin | P07,P08,P16 | stock staff/procurement; Item, Location, Movement, PO | D+F; F05 | receive → store → issue/consume → count → reorder suggestion → PO | stock states, lots/expiry optional, location, reorder, supplier, count, quarantine, approvals |
| S031 | Provider Credential / Document Expiry Tracker | P38,P15,P34 | credentialing/admin/providers; Credential, Evidence, Expiry | D+F+P+C; F09 | collect credential → review → approve → active → expiry reminders → renew/archive | credential types, issuers, dates, documents, review roles, reminder offsets, restrictions |
| S032 | Clinic Facility / Equipment Maintenance | P17,P16,P37 | facilities/technicians; Asset, Work Order, Inspection | D+F+P+C; F06/F09 optional | preventive trigger/request → schedule → inspect/repair → parts → verify → close | asset schedules, checklists, downtime, parts, evidence, warranty, contractor assignment |

---

# 5. Legal, Compliance & Governance — S033–S040

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S033 | Legal Case / Matter Management | P03,P09,P15 | lawyers/staff/clients; Matter, Party, Deadline, Document | D+F+P+C; F09/F06 | open matter → parties/tasks/deadlines → documents/communications → review → close/archive | matter types, conflicts refs, permissions, deadlines, tasks, billing refs, documents, retention |
| S034 | Client Legal Intake Portal | P10,P33,P15 | prospects/clients/legal staff; Intake, Matter Candidate, Documents | D+F+P+C; F09 | intake/save → conflict/eligibility review → approve → create matter → notify | fields by matter type, identity docs, consent, attachments, assignment, rejection reasons |
| S035 | Contract Lifecycle Manager | P15,P04,P09 | legal/business/approvers; Contract, Version, Obligation, Renewal | D+F+P+C; F09 | request/draft → review → approve → sign externally → active obligations → renew/terminate | templates, clauses refs, counterparties, approvals, dates, obligations, signature adapter, renewal alerts |
| S036 | Policy Management & Attestation | P38,P24,P15 | compliance/employees; Policy, Version, Audience, Attestation | D+F+P+C; F09 | draft → review → publish version → target audience → acknowledge → remind → retire | owners, review cycles, effective dates, required readers, attestation, exceptions, version history |
| S037 | Risk & Control Register | P38,P31,P04 | risk owners/auditors; Risk, Control, Assessment, Treatment | D+F+C; F04 | identify risk → score → map controls → treatment approval → evidence/review → reassess | likelihood/impact, matrices, owners, controls, residual risk, due dates, treatment plans |
| S038 | Internal Audit & Finding Tracker | P16,P38,P03 | auditors/control owners; Audit, Test, Finding, Action | D+F+P+C; F04/F09 | plan audit → test controls → findings → owner response → remediation → verify → close | scope, samples, evidence, severity, due dates, repeat findings, reports, approvals |
| S039 | Compliance Evidence Repository | P01,P15,P38 | compliance/auditors; Requirement, Evidence, Owner, Expiry | D+F+P+C; F09/S03 | requirement → request evidence → upload/protect → review → approve → expire/renew | evidence classes, access, expiry, reminders, version, source refs, protected files, retention |
| S040 | Data Subject Request / Privacy Case Manager | P03,P04,P15 | privacy team/requester; Request, Verification, Search Task, Decision | D+F+P+C; F09 | receive request → verify → scope → collect/export/delete tasks → approve → respond → retain evidence | request types, deadlines, identity verification adapter, task owners, legal holds, export/delete evidence |

---

# 6. Real Estate & Property — S041–S048

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S041 | Property Listings Portal | P13,P30,P32,P33 | agents/buyers/admin; Property, Unit, Agent, Inquiry | D+P+F+C; F03/F11 | create/moderate property → index/map → search → inquire → lead handoff | property types, specs, media, location, price, status, agent, featured, search facets |
| S042 | Real Estate Lead CRM | P02,P05,P33 | agents/managers/leads; Lead, Property Interest, Viewing | D+F+P+C; F06/F02 | inquiry → qualify → match properties → schedule viewing → offer/follow-up → won/lost | sources, budget, areas, requirements, lead stages, property relations, viewing reminders |
| S043 | Property Viewing Scheduler | P05,P32,P34 | buyer/agent; Viewing, Property, Agent Availability | D+P+C; F06/F11 | property context → available agent/slot → hold → confirm → reminder → outcome | viewing duration, agent/resource rules, buffer, location, reschedule, no-show, notes |
| S044 | Property Management Portal | P01,P03,P15,P33 | landlords/managers/tenants; Property, Lease Ref, Request, Document | D+F+P+C; F09/S03 | tenant/property link → view docs/charges refs → request maintenance → communicate → close | portfolio hierarchy, tenant access, lease dates, documents, contacts, notices, maintenance |
| S045 | Maintenance Request & Contractor Dispatch | P17,P03,P39 | tenants/managers/contractors; Work Order, Asset, Contractor | D+F+P+C; F06/F11 | request → triage → assign/schedule → contractor visit → evidence → approval → close | emergency priority, trade, SLA, property/access info, quotes refs, photos, completion approval |
| S046 | Lease / Tenancy Document Workflow | P15,P04,P33 | landlord/tenant/legal; Lease, Version, Renewal | D+F+P+C; F09 | template/data → draft → approve → external sign → active → renewal/termination alerts | lease types, parties, dates, deposits refs, clauses, renewal periods, protected documents |
| S047 | Property Inspection System | P16,P32,P15 | inspectors/managers/tenants; Inspection, Room, Finding | D+F+P; F11/F09 | schedule → conduct checklist/media → findings → report → corrective tasks → verify | templates by property, condition ratings, photos, meter fields, signatures refs, reports |
| S048 | Real Estate Deal / Offer Room | P02,P04,P15,P33 | buyer/agent/seller/legal; Offer, Version, Counteroffer | D+F+P+C; F09 | create offer → submit → review/counter → approvals → accept/reject/expire → handoff | offer price/terms, deadlines, documents, parties, counter versions, approvals, audit |

---

# 7. Construction, Maintenance & Field Service — S049–S056

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S049 | Construction Project Hub | P09,P15,P34 | PMs/site teams/clients; Project, Phase, Task, RFI, Document | D+F+P+C; F09 | project setup → phases/tasks → field updates/docs → approvals → completion/handover | WBS, milestones, roles, client visibility, drawings/docs, dependencies, delays, reports |
| S050 | Daily Site Log | P10,P16,P32 | supervisors; Site Log, Weather Ref, Crew, Activity, Issue | D+F+P; F11/F09 | open daily log → crews/activities/materials/photos → incidents → submit/approve → report | project/site, date, workforce, equipment, deliveries, weather adapter, photos, signoff |
| S051 | RFI / Submittal Workflow | P03,P04,P15 | contractor/designer/client; RFI, Submittal, Revision, Response | D+F+P+C; F09 | submit question/document → route → review → response/revision → approve → close | categories, drawings refs, due dates, approvers, revisions, distribution, overdue escalation |
| S052 | Field Service Work Order OS | P17,P05,P39 | dispatchers/technicians/customers; Job, Asset, Visit | D+F+P+C; F06/F11 | request → triage → assign tech → schedule → perform/checklist/parts → signoff → close | skills, territories, SLA, time window, parts, checklists, customer approval, follow-up |
| S053 | Preventive Maintenance Planner | P17,P05,P07 | maintenance teams; Asset, Maintenance Plan, Work Order | D+F+C; F06/F05 | asset schedule/meter trigger → work order → assign → perform → parts/evidence → next due | recurrence, meter thresholds, checklist, downtime, priority, spare parts, warranty |
| S054 | Safety Inspection & Incident Tracker | P16,P03,P38 | safety staff/workers; Inspection, Incident, Finding, Action | D+F+P+C; F04/F09 | inspect/report → classify severity → contain → investigate → corrective action → verify | incident types, severity, evidence, witness refs, root cause, actions, report, notifications |
| S055 | Equipment & Tool Custody | P37,P07 | site teams/storekeepers; Tool, Custody, Location, Movement | D+F+P; F05 | register → issue/transfer → inspect → return → lost/damage exception | serial/quantity, project/location, assignee, due dates, condition, consumables vs assets |
| S056 | Change Order / Variation Manager | P04,P02,P15 | PM/client/commercial; Change Request, Cost/Time Impact, Approval | D+F+P+C; F04/F09 | raise change → assess impact → price/time → approval → issue revision → project update | categories, reason, cost/time formula, attachments, approvals, versions, acceptance, status |

---

# 8. Inventory, Procurement & Manufacturing — S057–S064

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S057 | Multi-location Inventory System | P07,P29 | warehouse/ops; Item, Location, Movement, Reservation | D+F; F05 | receive/reserve/move/consume/return → movement ledger → balance → reconcile | warehouses, bins, states, negative policy, lots/serials, reservations, reorder, counts |
| S058 | Purchase Order & Procurement System | P08,P04,P15 | buyers/managers/suppliers; Supplier, PO, Receipt | D+F+P+C; F05/F09 | demand/request → approval → PO → supplier ack → receipt → variance → close | MOQ, costs, currencies, terms, partial receipt, attachments, approvals, supplier portal |
| S059 | Supplier Portal & ASN Collaboration | P33,P08,P34 | suppliers/buyers; PO, Acknowledgement, Shipment Notice | D+F+P+C | supplier login → open POs → confirm/date/qty → ASN/docs → buyer exceptions | supplier users, PO visibility, promised dates, partials, documents, comments, notifications |
| S060 | Stock Count & Cycle Count | P16,P07,P04 | counters/supervisors; Count Session, Count Line, Variance | D+F+P; F05 | create blind count → capture → variance → recount/approval → movement adjustment → close | scopes, blind counts, tolerances, reasons, approvals, barcode refs, recount, freeze policy |
| S061 | Warehouse Transfer Workflow | P07,P04 | warehouse teams; Transfer, Pick, Shipment, Receipt | D+F+P; F05 | request → approve → pick → ship → in-transit → receive → variance → reconcile | source/destination, partials, bins, in-transit, loss/damage, approvals, documents |
| S062 | Manufacturing Work Order / BOM-lite | P09,P07,P31 | planners/operators; Work Order, BOM, Component, Output | D+F; F05/F04 | plan work → reserve components → issue → produce → record scrap/output → close | BOM versions, quantities, substitutions, work centers refs, scrap, batch/lot, approvals |
| S063 | Quality Control & Nonconformance | P16,P03,P38 | QA/production/suppliers; Inspection, NCR, Disposition | D+F+C; F04/F09 | inspect lot/output → finding → quarantine → investigate → disposition → CAPA → verify | sampling, specs, severity, evidence, disposition, supplier link, corrective actions, reports |
| S064 | Demand / Reorder Planning | P29,P31,P08 | planners/buyers; Forecast, Reorder Suggestion, Scenario | D+F; F02/F04/F05 | sales/usage + lead time + stock → forecast → scenario → suggestion → approval → PO | velocity windows, seasonality, safety stock, lead time, service level, overrides, confidence |

---

# 9. Finance & Business Administration — S065–S072

WPE records workflow/accounting-adjacent facts; regulated banking/payment/accounting authority may remain external.

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S065 | Expense Claim & Reimbursement | P22,P04,P15 | employees/managers/finance; Claim, Line, Receipt | D+F+P+C; F04/F09 | draft → receipts/policy → submit → approve → finance → external payment ref → close | categories, limits, currencies, mileage, projects, approvals, receipt requirements, exports |
| S066 | Purchase Requisition / Spend Approval | P04,P08 | requesters/managers/buyers; Requisition, Lines, Budget Ref | D+F+P+C; F04 | request → policy/threshold → approve → convert PO or external ERP handoff | spend categories, cost center, amount tiers, approvers, preferred suppliers, attachments |
| S067 | Budget Request & Allocation Tracker | P04,P06,P31 | department/finance; Budget, Request, Allocation, Consumption Ref | D+F; F05/F04 | propose budget → review/approve → allocate → track commitments/usage → revise/close | periods, cost centers, caps, transfers, approvals, variance, forecasts, locked periods |
| S068 | Invoice / Accounts Payable Register | P01,P04,P15 | finance/approvers; Invoice Record, Supplier, Approval, Payment Ref | D+F; F09 | capture/import invoice → validate/duplicate check → approve → external accounting/payment → reconcile | invoice fields, tax refs, PO match refs, due dates, approval, attachment, export, payment status ref |
| S069 | Accounts Receivable / Collection Tracker | P02,P03,P34 | finance/account managers; Receivable Ref, Contact, Promise | D+F+C; F02 | import external invoices → aging queue → contact cadence → promise/dispute → external settlement → close | aging buckets, owners, reminders, dispute reasons, notes, payment links adapter, escalation |
| S070 | Cash Advance / Petty Cash Request | P22,P06,P04 | employees/finance; Advance, Settlement, Receipt | D+F+P; F05 | request → approve → external disbursement → spend/receipts → settle/reimburse/recover | limits, purpose, currencies, due dates, approvers, settlement rules, outstanding balance |
| S071 | Pricing / Cost Estimator | P31,P15 | sales/estimators; Estimate, Formula, Inputs, Version | D+F; F04/S05/F09 | collect inputs → deterministic formula → scenario → approval → generate estimate → convert handoff | formulas, rates, units, rounding, margins, taxes external refs, versions, validity, approval |
| S072 | Financial Document Approval Vault | P15,P04,P38 | finance/auditors; Document, Version, Approval, Evidence | D+F; F09/S03 | upload/generate → classify → review/approve → protect → retention → audit/export | document types, access, signatures refs, retention, legal hold, numbering, immutable versions |

---

# 10. Membership, Community & Nonprofit — S073–S080

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S073 | Association Membership Portal | P11,P33,P23 | members/admin; Plan, Enrollment, Directory Profile, Event | D+F+P+C | join/grant → enrollment → portal/directory/events → renew/expire → history | plans, dues provider refs, member types, directory privacy, benefits, renewals, certificates |
| S074 | Club / Community Member Directory | P13,P11,P33 | members/moderators; Member Profile, Group, Interest | D+P+F+C; F03 optional | enroll → profile opt-in → directory search → connect/request → moderate | profile fields, visibility, categories, location coarse, messaging rules, search, reports |
| S075 | Volunteer Management | P19,P05,P23 | volunteers/coordinators; Volunteer, Skill, Shift, Assignment | D+F+P+C; F06 | onboard → skills/availability → assign shift/task → check-in → hours → recognition | skills, locations, availability, background-check refs, shifts, hour tracking, certificates |
| S076 | Donor CRM & Fundraising Pipeline | P02,P29,P34 | fundraisers/donors; Donor, Gift Ref, Campaign, Opportunity | D+F+P+C; F02 | donor/contact → campaign/touch → external donation fact → stewardship → next ask/report | donor types, sources, interests, giving history refs, campaigns, consent, major-gift pipeline |
| S077 | Grant Management | P10,P04,P15,P09 | applicants/grant team/reviewers; Grant, Application, Award, Report | D+F+P+C; F04/F09 | publish opportunity → apply → review/score → approve award ref → reporting milestones → close | eligibility, budgets, documents, reviewers, scoring, decisions, milestones, reports, amendments |
| S078 | Beneficiary / Program Case Management | P03,P10,P33 | caseworkers/beneficiaries; Case, Service Plan, Referral | D+F+P+C; F09 optional | intake → eligibility policy → case plan → service/referrals → review → close | household/subject links, sensitive fields, consent, tasks, service history, outcomes, privacy |
| S079 | Community Event & Attendance | P23,P11,P34 | members/organizers; Event, Registration, Attendance | D+F+P+C; F06 | event → member/public registration → capacity → reminders → attendance → follow-up | event types, membership eligibility, guest policy, capacity, waitlist, certificates |
| S080 | Mentoring / Buddy Matching Program | P01,P31,P39 | members/coordinators; Mentor, Mentee, Match, Goal | D+F+P+C; F04 | profiles/preferences → match candidates → coordinator approval → relationship → check-ins → close | skills, goals, availability, exclusions, scoring weights, manual override, privacy, duration |

---

# 11. Events, Hospitality & Services — S081–S088

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S081 | Event Registration Platform | P23,P05,P33 | attendees/organizers; Event, Ticket Ref, Session, Registration | D+F+P+C; F06 | publish → register/capacity → payment adapter optional → confirm → check-in → follow-up | ticket types, sessions, capacity, waitlist, forms, guests, QR adapter, reminders, refunds refs |
| S082 | Conference Program & Speaker Portal | P01,P10,P24,P33 | speakers/program team/attendees; Session, Speaker, Submission | D+F+P+C | call for papers → submission → review → schedule → speaker portal → publish program | tracks, rooms, reviewers, conflicts, speaker bios, materials, session states, program listings |
| S083 | Venue / Room Booking | P05,P33 | staff/customers; Venue, Room, Reservation | D+P+C; F06/F11 | availability → hold → request/payment → confirm → setup → complete | room capacity, layouts, buffers, resources, pricing refs, deposits, cancellation, blackout |
| S084 | Restaurant Table Reservation | P05,P39 | diners/hosts; Table, Reservation, Party, Waitlist | D+P+C; F06 | party/time → availability → reserve → remind → arrive/seated/no-show → release | table combinations, party size, duration, grace, waitlist, special notes, locations |
| S085 | Catering Inquiry & Order Workflow | P10,P02,P04,P15 | clients/sales/ops; Event Brief, Quote, Menu Ref, Order Ref | D+F+P+C; F09 | inquiry → qualify → quote/version → approve → external/order handoff → event checklist | event date, guests, menu options, dietary notes, venue, quote, deposit ref, approvals |
| S086 | Hotel / Property Reservation Coordination | P05,P33 | guests/reservations; Unit, Stay, Guest, Reservation Ref | D+P+C; F06 + external PMS/payment adapter if authoritative | availability adapter → hold/request → confirm → pre-arrival → stay → checkout/followup | unit types, occupancy, dates, rates refs, policies, add-ons, guest fields, cancellations |
| S087 | Service Appointment Marketplace | P05,P13,P14 | providers/customers; Service, Provider, Slot, Booking | D+F+P+C; F06/F11/F05 optional | find provider → choose service/slot → hold → pay external → perform → review/commission | provider onboarding, service areas, schedules, pricing refs, commissions, cancellation, reviews |
| S088 | Guest List / Invitation Manager | P23,P10,P34 | hosts/guests; Event, Invite, RSVP, Guest | D+F+C | import guests → invite → RSVP/plus-one → reminders → check-in → follow-up | invite groups, RSVP fields, plus-one limits, seating refs, privacy, QR/check-in, exports |

---

# 12. Government, Civic & Public Service — S089–S096

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S089 | Citizen Service Request / 311-like Portal | P03,P32,P33 | residents/service teams; Request, Location, Department | D+F+P+C; F11 | public intake → geo/category route → assign → work/update → verify → close | request categories, maps, photos, anonymity, SLA, departments, public/private status, notifications |
| S090 | Permit / License Application Workflow | P10,P04,P15,P16 | applicants/reviewers/inspectors; Application, Permit, Inspection | D+F+P+C; F09/F06 | apply → validate docs/fee authority → review → inspection → approve/deny → issue/expire | permit types, required docs, fees external, reviewers, inspections, conditions, renewals, certificate |
| S091 | Public Complaints & Ombudsman Cases | P03,P04,P15 | residents/case officers; Complaint, Agency, Finding | D+F+P+C; F09 | intake → jurisdiction → assign → investigate → response → review/appeal → close | confidentiality, categories, deadlines, evidence, agencies, escalation, final letter |
| S092 | Grant / Subsidy Application Portal | P10,P04,P31,P15 | applicants/program staff; Program, Application, Award Ref | D+F+P+C; F04/F09 | publish → apply → eligibility/score → panel approval → external payment/admin → reporting | eligibility, documents, scoring, quotas/budgets refs, reviewers, decisions, reporting |
| S093 | Inspection & Enforcement Tracker | P16,P03,P32 | inspectors/case officers; Inspection, Finding, Notice, Action | D+F+P+C; F11/F09 | schedule/trigger → inspect → finding → notice → corrective deadline → reinspection → close/escalate | inspection types, severity, geo, evidence, notices, deadlines, appeals refs, reports |
| S094 | Public Records / FOI Request Manager | P03,P15,P04 | requester/records team; Request, Search Task, Release Package | D+F+P+C; F09/S03 | submit → validate → task departments → collect → review/redact externally → approve → deliver | statutory deadlines, identity, exemptions refs, fee refs, documents, correspondence, appeal |
| S095 | Public Directory / Facility Finder | P13,P30,P32 | public/admin; Facility, Service, Location, Hours | D+UI; F03/F11 | maintain facilities → index/geo → public search/filter → view/contact | categories, accessibility, hours, service areas, languages, map, search, emergency notes |
| S096 | Council / Committee Agenda & Decisions | P24,P15,P04 | clerks/members/public; Meeting, Agenda Item, Decision, Document | D+F+C; F09 | collect agenda items → review → publish agenda → record decision → minutes → archive | meetings, committees, submissions, deadlines, public/private docs, decisions, minutes, search |

---

# 13. Media, Publishing & Content Operations — S097–S104

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S097 | Editorial Calendar & Workflow | P24,P09,P04 | editors/writers/designers; Story, Brief, Assignment, Revision | D+F+C | pitch → brief → assign → draft → edit/legal → schedule/publish → measure | content types, stages, owners, due dates, embargo, approvals, checklists, revisions |
| S098 | Contributor / Author Portal | P33,P10,P24 | contributors/editors; Submission, Author, Contract Ref | D+F+P+C | onboard author → submit pitch/content → review → revisions → accept/publish → payout ref | author profiles, submission types, rights refs, attachments, review states, messages, archives |
| S099 | Digital Asset / Media Approval Library | P01,P04,P15 | creative teams/approvers; Asset, Version, Usage, Approval | D+F+P; F09 optional | upload original → metadata → review/approve → publish/use → expire/archive | media types, rights/expiry, tags, projects, version, approval, watermark derivatives, download policy |
| S100 | Content Syndication / Feed Manager | P28,P24 | publishers/integrators; Feed, Mapping, Destination, Run | D+F+I; F10 | select content → map → schedule/event → publish external → reconcile/errors → update | destinations, formats, field mapping, filters, transforms, retries, deletions, canonical URL refs |
| S101 | Newsletter Content Production Hub | P24,P04,P34 | editors/marketers; Edition, Story Slot, Approval, Send Ref | D+F+C; F09 optional | plan edition → assign slots → draft/review → approve → email-provider handoff → archive | sections, deadlines, audience refs, templates, approvals, preview, send schedule, UTM options |
| S102 | Knowledge Base & Help Center | P25,P30,P33 | support/authors/customers; Article, Category, Feedback | D+P+C; F03/F12 optional | author/review → publish → index → search → feedback → update/retire | hierarchy, audiences, versions, review dates, related docs, helpfulness, redirects, AI citations |
| S103 | Review / UGC Moderation Center | P35,P39 | moderators/contributors; Submission, Media, Flag, Decision | D+F+C | ingest → spam/validation → moderation queue → approve/reject → appeal/report → analytics | queues, policy reasons, profanity adapters, media, reviewer roles, bulk actions, appeals |
| S104 | Content Localization Workflow | P24,P28,P04 | localization teams/translators; Source, Locale Version, Review | D+F+I | source change → create translation tasks → translate/import → review → publish → sync status | locales, translation provider refs, glossary, status, fallback, due dates, QA checklist, version links |

---

# 14. Developer, IT & DevOps — S105–S112

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S105 | Bug / Issue Tracker | P03,P09,P39 | developers/QA/users; Issue, Component, Release Ref | D+F+P+C | report → triage → assign → investigate/fix ref → verify → close/reopen | type, severity, priority, component, environment, assignee, reproduction, attachments, versions |
| S106 | IT Service Desk | P03,P34,P25 | users/IT agents; Ticket, Service, SLA, Knowledge Article | D+F+P+C; F03 optional | request/incident → classify → assign → resolve → KB link → verify/close | service catalog, priority, SLAs, queues, canned replies, approvals, related assets, knowledge |
| S107 | Release & Deployment Tracker | P09,P04,P15 | engineering/release managers; Release, Change, Environment, Gate | D+F+C; F09 optional | plan release → changes → approvals/gates → external deployment ref → verification → rollback/postmortem | versions, environments, change list, approvals, maintenance windows, checklists, evidence, status |
| S108 | Incident Response & Postmortem | P27,P34,P15 | on-call/engineering/stakeholders; Incident, Timeline, Action | D+F+C; F02/F09 | alert/report → severity → contain → communicate → recover → verify → RCA/actions | severity matrix, services, on-call refs, timeline, updates, runbooks, RCA, follow-ups |
| S109 | API Developer Portal | P26,P25,P33 | developers/platform admins; API, App, Scope, Subscription | D+I+P+C; F03 | publish API/docs → onboard developer → app request → credentials adapter → use/observe → rotate | versions, endpoints, schemas, scopes, rate limits, apps, test console, changelog, deprecation |
| S110 | Webhook Delivery Monitor | P03,P34,P28 | developers/ops; Endpoint, Delivery, Attempt, Dead Letter | I+F+C | event → delivery attempt → retry → unknown outcome → dead letter → replay/reconcile | endpoint health, signatures, retry policies, filters, payload redaction, replay permissions, metrics |
| S111 | Feature Request / Product Feedback | P03,P35,P29 | users/product teams; Request, Vote, Theme, Status | D+F+P+C; F02 | submit → dedupe/cluster → triage → vote/comment → roadmap status → notify | categories, public/private, votes, merge duplicates, statuses, product areas, requester updates |
| S112 | Test Case & QA Management | P16,P09,P15 | QA/developers; Test Case, Run, Result, Defect Ref | D+F+C; F09 optional | design cases → test plan → execute → evidence → defect link → rerun → signoff | suites, environments, steps, expected results, datasets, attachments, pass/fail/block, reports |

---

# 15. Agency & Professional Services — S113–S120

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S113 | Client Portal & Project Hub | P33,P09,P15 | agency/client teams; Client, Project, Deliverable, Approval | D+F+P+C; F09 | onboard client → project/tasks → deliverables → client review → approve → archive | branding, client users, projects, files, approvals, messages, invoices refs, dashboard widgets |
| S114 | Proposal / Quote Builder | P15,P02,P04 | sales/clients; Proposal, Version, Service Line, Approval | D+F+P+C; F04/F09 | opportunity → scope/pricing → internal approval → generate/send → client accept → project handoff | templates, services, quantities, formulas, validity, terms, versioning, signatures refs |
| S115 | Retainer / Service Request Queue | P03,P39,P33 | clients/account team; Request, Retainer Balance Ref, Task | D+F+P+C; F05 optional | client request → classify/estimate → approve priority → assign → deliver → consume allowance ref | request types, SLA, priorities, approvals, monthly quota, attachments, completion review |
| S116 | Creative Review & Approval | P04,P15,P35 | creatives/clients; Asset, Version, Annotation Ref, Decision | D+F+P+C | upload version → notify reviewers → feedback → revise → approve → final delivery | review rounds, approvers, deadlines, versions, comments, proofing adapter refs, final lock |
| S117 | Agency Resource & Capacity Planner | P09,P05,P29 | managers/staff; Resource, Skill, Allocation, Project | D+F; F06/F02 | project demand → staff availability → allocate → conflict/workload → adjust → report | skills, weekly capacity, allocations, holidays refs, utilization targets, tentative/confirmed |
| S118 | Client Onboarding Workflow | P10,P09,P15,P04 | clients/account/finance/ops; Intake, Checklist, Document | D+F+P+C; F09 | signed deal ref → intake → docs/access/tasks → approvals → kickoff → complete | onboarding templates, owner teams, required access, questionnaires, files, deadlines, kickoff |
| S119 | Deliverables & Milestone Acceptance | P09,P04,P15 | project/client; Milestone, Deliverable, Acceptance | D+F+P+C; F09 | submit deliverable → client review → accept/reject → revision → milestone close → invoice ref | acceptance criteria, due dates, versions, reviewers, change requests, evidence, signoff |
| S120 | Professional Services Knowledge / SOP Hub | P25,P24,P38 | consultants/staff; SOP, Template, Policy, Version | D+P+C; F03/F12 optional | author → peer review → publish → search/use → review due → revise | taxonomy, audience, version, approval, review dates, templates, linked processes, AI grounded Q&A |

---

# 16. Logistics, Fleet & Delivery — S121–S128

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S121 | Fleet Asset Register | P01,P17,P37,P32 | fleet managers/drivers; Vehicle, Driver, Assignment, Document | D+F+P; F11 | register vehicle → assign → documents/maintenance → transfer → retire | VIN/plate, type, location, odometer refs, custody, insurance/registration expiry, documents |
| S122 | Vehicle Maintenance Planner | P17,P05,P07 | fleet/technicians; Vehicle, Service Plan, Work Order, Part | D+F+C; F06/F05 | mileage/date trigger → schedule → perform → parts → evidence → next due | service intervals, odometer imports, vendors, downtime, parts, warranty, cost refs |
| S123 | Driver Compliance & Credential Tracker | P38,P15,P34 | drivers/fleet/compliance; License, Training, Expiry | D+F+P+C; F09 | collect → verify → active → expiry alert → renew/restrict | credential types, dates, documents, review, reminders, vehicle eligibility rules |
| S124 | Delivery Job Dispatch | P39,P05,P32 | dispatchers/drivers/customers; Delivery Job, Stop, Driver | D+F+P+C; F06/F11 | order/request → zone/skill → assign → schedule → pickup → deliver → proof → close | territories, windows, capacity, drivers, status, proof photos/signature refs, exceptions |
| S125 | Route / Stop Task Manager | P09,P32,P39 | dispatch/field staff; Route, Stop, Task | D+F+P; F11 | create/import stops → assign order/sequence → execute statuses → exceptions → complete | locations, time windows, priority, sequence manual/adapter, task checklist, proof, notes |
| S126 | Proof of Delivery Repository | P15,P16,P33 | drivers/customers/ops; Delivery Ref, Proof, Exception | D+F+P; F09/S03 | delivery → capture proof → validate → protect/store → customer/ops view → dispute case | photo/signature adapter refs, recipient, timestamp, location precision, failed proof, retention |
| S127 | Fuel / Expense Log | P22,P37,P29 | drivers/fleet/finance; Fuel Entry, Vehicle, Receipt | D+F+P; F04 | record/import fuel → validate → attach vehicle/driver → approve → analytics/anomaly | units, currency, liters/gallons, odometer, station, receipt, cost/km, duplicate detection |
| S128 | Transport Exception & Claims | P03,P16,P15 | ops/customers/carriers; Exception, Claim, Shipment Ref | D+F+P+C; F09 | delay/damage/loss event → case → evidence → carrier/customer communication → decision/ref external → close | reasons, severity, SLA, shipment relation, evidence, compensation refs, carrier integration |

---

# 17. Agriculture, Food & Production — S129–S136

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S129 | Farm / Field Register | P01,P32,P16 | farm managers/workers; Farm, Field, Crop Cycle, Observation | D+F+P; F11 | register fields → crop cycle → tasks/observations → harvest → history | polygons/locations, crops, seasons, acreage, soil refs, photos, notes, ownership |
| S130 | Crop Task & Activity Planner | P09,P05,P32 | farm managers/crews; Task, Field, Activity, Input | D+F+P+C; F06/F11 | seasonal plan → schedule tasks → assign crew → perform/evidence → complete | task templates, field, date window, crew, equipment, inputs, weather adapter refs, recurrence |
| S131 | Harvest & Yield Log | P10,P07,P29 | field/warehouse; Harvest, Lot, Quantity, Grade | D+F; F05/F02 | record harvest → lot/location → quality grade → stock movement → analytics | units, fields, crop, lot, grade, waste, destination, yield metrics |
| S132 | Agricultural Input Inventory | P07,P08 | stores/procurement; Seed/Fertilizer/Input, Stock, Supplier | D+F; F05 | receive → store → issue to field/job → count → reorder → PO | units, batches/expiry, locations, safety docs refs, consumption reason, reorder |
| S133 | Farm Equipment Maintenance | P17,P37 | operators/maintenance; Equipment, Service, Work Order | D+F+P; F06 | register → schedule service → issue work → parts/evidence → close → next due | meter/date intervals, operators, parts, downtime, condition, warranty |
| S134 | Food Batch / Lot Traceability-lite | P07,P16,P38 | production/QA; Lot, Input Lot, Process Batch, Output | D+F; F05 | receive lot → consume into batch → produce output lots → QA → release/quarantine → trace | lot IDs, expiry, supplier, transformations, quantities, QA status, recalls refs |
| S135 | Supplier Quality & Farm Procurement | P08,P16,P03 | buyers/QA/suppliers; Supplier, PO, Inspection, NCR | D+F+P+C; F05 | purchase → receive → quality inspect → accept/quarantine/reject → supplier corrective action | specs, grades, tolerances, prices refs, documents, NCR, supplier scorecards |
| S136 | Farm Sales / Customer Order Coordination | P02,P08,P29 | sales/farm ops; Customer, Demand, Allocation, Order Ref | D+F+C; A01/external commerce optional | demand/order fact → allocate harvest/stock → fulfillment planning → customer update → report | customer groups, product/grade, quantities, delivery dates, allocation priority, substitutions |

---

# 18. Marketplace, Directory & Local Service — S137–S144

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S137 | Business Directory | P13,P30,P32 | businesses/public/moderators; Listing, Category, Location | D+P+F; F03/F11 | submit/claim → moderate → publish/index → search/map → inquiry → renew | categories, fields, media, service areas, hours, claim, featured refs, reviews, expiry |
| S138 | Professional Expert Directory | P13,P30,P05 | experts/clients; Profile, Specialty, Credential Ref, Booking | D+P+C; F03/F06/F11 | expert onboard → profile verify → search → inquiry/booking → feedback | specialties, languages, locations, credentials, availability, consultation types, privacy |
| S139 | Job Board & Employer Portal | P13,P10,P02,P33 | employers/candidates/admin; Job, Company, Application | D+F+P+C; F03 | employer onboard → publish job → search/apply → ATS pipeline → close/expire | job fields, locations, salary display policy, company profiles, applications, featured, expiry |
| S140 | Freelancer / Service Marketplace | P14,P13,P05 | providers/clients/admin; Service, Request, Booking/Order Ref | D+F+P+C; F06/F05 + external payment | provider onboard → list → search → request/book → perform → commission/payment refs → review | categories, rates refs, availability, proposals, commissions, disputes, reviews, payouts refs |
| S141 | Vendor Marketplace Admin | P14,P13,P07 | vendors/buyers/admin; Vendor, Catalog, Order Allocation | D+F+P+C; A01/F05 | vendor approval → catalog → buyer order → split allocation → fulfillment → commission/reconcile | vendor roles, product approval, commissions, order visibility, fulfillment SLA, refunds, payouts refs |
| S142 | Local Service Request & Quote Marketplace | P10,P02,P39 | customers/providers; Request, Match, Quote | D+F+P+C; F04/F11 | request/location → qualify → match providers → quotes → customer select → external transaction/job | service categories, area, urgency, attachments, provider scoring, quote expiry, privacy |
| S143 | Rental Listing Marketplace | P13,P05,P14 | owners/renters; Asset Listing, Availability, Reservation | D+P+C; F06/F11 + external payment | owner listing → search → availability → reserve/hold → payment ref → pickup/return → dispute | asset specs, deposits refs, calendar, locations, pricing refs, condition, insurance refs |
| S144 | Community Classifieds | P13,P35,P33 | members/moderators; Listing, Message, Report | D+P+C; F03 optional | create listing → moderate → publish → search → contact → mark sold/expire → report abuse | categories, price, media, location privacy, expiry, messaging rules, moderation, spam |

---

# 19. Ecommerce & Retail Operations — S145–S152

These use the formal **A01 WooCommerce Commerce Domain Adapter** where WooCommerce is the commerce runtime.

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S145 | Ecommerce Wishlist & Intent Hub | P01,P29,P34 | shoppers/marketing; Wishlist, Item, Intent Event | D+F+P+C; A01/F02 | add/save/share → identity merge → product change → reminder/segment → cart/order conversion | multiple lists, privacy, guest merge, share, price/stock alerts, reminder caps |
| S146 | Ecommerce Returns / Exchanges Portal | P03,P04,P06,P33 | customers/support/warehouse; Return, Exchange, Refund Ref | D+F+P+C; A01/F05/S02 | order item → eligibility → request → approve → return/inspect → refund/exchange/store credit → stock | windows, reasons, media, labels adapter, fees, exchange pricing, dispositions, wallet |
| S147 | Loyalty / Wallet Program | P06,P11,P29 | customers/marketing/finance; Points, Tier, Wallet | D+F+P+C; A01/F05/F04 | order/review/referral → earning posting → tier → redeem → refund reversal/expiry | earn/redeem, tiers, expiry, exclusions, bonus, liability, manual adjustment approval |
| S148 | B2B Wholesale Portal | P33,P04,P02 | companies/buyers/sales; Company, Buyer, Quote, Price List | D+F+P+C; A01/F04 | company approve → catalog/pricing → saved cart/quote → approval → order/payment terms → fulfillment | roles, spend limits, shared carts, price books, min qty, quotes, credit terms refs |
| S149 | Product Configurator & Quote | P31,P02,P15 | shoppers/sales; Configuration, Component, Quote | D+F+P; A01/F04/S05/F09 | choices → compatibility/formula → live price → save/cart/quote → approval/order | steps, components, dependencies, formulas, substitutions, summary, expiry, quote doc |
| S150 | Promotion / Discount Rule Studio | P31,P04,P29 | marketers/finance; Promotion, Rule, Budget | D+F; A01/F04/S05/F02 | draft rule → simulate → margin/approval → activate → cart evaluation → budget/expiry → report | discount types, stacking, eligibility, caps, schedules, budgets, approval thresholds, experiments |
| S151 | Order Operations & Exception Center | P03,P39,P34 | ops/support; Order Ref, Operational Stage, Exception | D+F+C; A01 | order event → stage/SLA → detect exception → assign/remediate → verify → close | stages, owners, checklists, exceptions, bulk actions, customer notices, fulfillment refs |
| S152 | Product Search & Merchandising | P30,P29,P31 | shoppers/merchandisers; Search Index, Ranking, Rule | D+UI; A01/F03/F02/F04 | index catalog → search/facet → rank/boost → log zero result/conversion → tune | synonyms, typo, facets, pins, boosts, redirects, merchandising windows, analytics |

---

# 20. Personal Productivity, Small Business & General WordPress Apps — S153–S160

| ID | System | Patterns | Actors / core objects | Composition / extra foundation | Primary domain flow | Major option groups |
|---|---|---|---|---|---|---|
| S153 | Personal / Team Task Manager | P09,P34 | users/teams; Task, List, Project | D+F+P+C | create → prioritize/assign → work → remind → complete/archive | statuses, priorities, recurrence, due dates, tags, checklists, views, reminders |
| S154 | Personal Knowledge / Notes Database | P01,P25,P30 | users/teams; Note, Topic, Link, Attachment | D+P; F03 optional | capture → tag/link → search → revise → archive/share | note types, privacy, backlinks relations, tags, files, search, favorites, revisions |
| S155 | Contact / Relationship Manager | P01,P02,P34 | individual/small team; Contact, Relationship, Interaction | D+F+P+C | capture contact → categorize/relate → log interaction → follow-up reminder → archive | contact fields, groups, relationship types, birthdays, notes, reminders, imports |
| S156 | Simple Invoice / Quote Register | P15,P02 | freelancers/small business; Quote/Invoice Ref, Client, Line | D+F+P; F04/F09; external accounting/payment authority | client → quote/estimate → approve → generate doc → external payment/accounting ref → close | numbering, lines, units, taxes refs, due date, templates, status, payment reference |
| S157 | Appointment Booking for Professionals | P05,P33 | provider/customer; Service, Slot, Booking | D+P+C; F06 | service → availability → hold → form/payment ref → confirm → reminder → complete | services, duration, buffers, schedules, locations, cancellation, deposits refs, intake |
| S158 | Customer Request / Small Helpdesk | P03,P33 | customers/owner; Ticket, Message, Resolution | D+F+P+C | submit → assign → reply/action → resolve → feedback | categories, priority, SLA-lite, attachments, email/in-app notifications, portal |
| S159 | File / Document Request Portal | P10,P15,P33 | clients/staff; Request, Required Document, Upload | D+F+P+C; F09/S03 | staff requests docs → client portal upload → validate/review → approve/reject → complete | document types, due dates, secure files, reminders, version/replacement, reviewer notes |
| S160 | Custom Internal App Starter | P01,P09,P04,P33 | admins/staff; configurable Record, Task, Approval | D+F+P+C; F01 | choose blueprint variables → generate schemas/views/forms/workflows → review → publish | entity names, fields, relations, statuses, roles, list views, form layouts, workflows, dashboard |

---

# Catalog coverage summary

The 160 systems span:
1. CRM/Sales/Growth
2. HR/Workforce
3. Education/LMS
4. Healthcare administration
5. Legal/Compliance
6. Real Estate/Property
7. Construction/Field Service
8. Inventory/Procurement/Manufacturing
9. Finance/Admin
10. Membership/Community/Nonprofit
11. Events/Hospitality
12. Government/Civic
13. Media/Publishing
14. Developer/IT/DevOps
15. Agency/Professional Services
16. Logistics/Fleet
17. Agriculture/Food
18. Marketplace/Directory
19. Ecommerce/Retail
20. Personal/Small Business/General Apps

This is the curated reference catalog, not the ceiling of the platform.

## Catalog installation principle

A future user chooses a system, then the Solution Blueprint Composer resolves:

`Reference System → Patterns → Required Modules → Foundation Modules → Domain Adapters → Existing-site Mapping → Option Variables → Dry Run → Draft Definitions → Review → Activation`

Users can fork a blueprint after install. Forking must preserve provenance and make future upstream upgrade conflicts explicit.

## Development gate

All 160 entries are planned Solution Blueprints only. No entry means its runtime exists, and no entry authorizes generated source, schema migrations or third-party provider calls.