# WPEssential — Atomic Option Contracts Wave 3: Identity, Automation & Communication

Status: **atomic option inventory**  
Snapshot: **2026-08-31**  
Surfaces: **15 Membership, 17 Forms/Workflows, 18 Cron, 19 Notifications, 20 Emails, 21 Chat, 37 Reservations, 40 Documents**.

---

# Surface 15 — Membership

## Plan identity
- UUID;
- name/key;
- description;
- group/category;
- status;
- public/hidden/internal visibility;
- sort/order;
- clone;
- revisions;
- import/export.

## Pricing
- free;
- one-time;
- recurring;
- currency;
- initial payment;
- recurring amount;
- billing interval unit;
- billing interval count;
- billing cycle limit;
- trial enabled;
- trial duration;
- trial amount/free trial;
- signup fee;
- taxes enabled;
- tax class/provider;
- price display formatting;
- compare-at/list price display;
- gateway availability per plan.

## Expiration/state
- never expires;
- fixed duration from signup;
- fixed calendar expiration;
- grace period;
- pending/active/past-due/cancelled/expired/refunded state mapping;
- gateway subscription state mapping;
- automatic role/capability mapping by state;
- manual override;
- override expiration;
- audit override.

## Upgrade/downgrade/change
- allowed target plans;
- immediate vs next-cycle change;
- proration provider;
- credit balance provider;
- trial eligibility on upgrade;
- downgrade access behavior;
- schedule change;
- cancel scheduled change;
- dependency impact preview.

## Restriction rules
- content types;
- individual content;
- taxonomy/terms;
- archives;
- URL/path patterns;
- blocks/components;
- shortcode/token/provider;
- listings/dashboard endpoints;
- download/document access;
- REST endpoints via Policy integration;
- allow/deny effect;
- AND/OR Decision groups;
- plan inclusion/exclusion;
- user override;
- role/capability conditions;
- login state;
- drip delay;
- expiration after access;
- date/time window;
- teaser/excerpt behavior;
- restricted message/template;
- redirect target;
- HTTP response behavior where relevant;
- cache compatibility.

## Coupons/discounts
- code;
- description;
- active;
- start/end;
- usage total;
- usage per user;
- applicable plans;
- first-payment discount;
- recurring discount;
- percent/fixed;
- override trial/signup fee;
- minimum/maximum amount;
- stacking policy;
- auto-apply provider;
- audit uses.

## Checkout/account
- checkout fields;
- field groups;
- required/optional;
- terms/privacy consent;
- login/register behavior;
- account page endpoints;
- billing details;
- payment-method management;
- subscription cancel/pause provider;
- invoices/documents;
- confirmation page;
- cancellation page;
- renewal page;
- redirects.

## Transactions
- order ID;
- member;
- plan;
- amount/currency;
- tax;
- gateway/provider;
- provider transaction ID;
- status;
- refund amount/status;
- notes;
- timestamps;
- reconciliation;
- idempotency;
- export;
- audit.

## Notifications/reports
- signup;
- payment success/failure;
- renewal;
- trial ending;
- expiration;
- cancellation;
- refund;
- reminder schedule;
- active members;
- signups;
- cancellations;
- MRR/revenue provider logic;
- churn;
- plan distribution;
- date comparison/export.

---

# Surface 17 — Forms & Workflows

## Form definition
- UUID;
- name/key;
- status;
- form type;
- description;
- submit label;
- AJAX;
- layout columns/grid;
- sections;
- pages/steps;
- progress indicator type;
- previous/next labels;
- save/resume enabled;
- revisions;
- clone/import/export.

## Field configuration
All Field Registry types plus form-specific options:
- input name;
- label;
- help;
- required;
- default;
- dynamic default;
- placeholder;
- autocomplete;
- conditional visibility;
- conditional requiredness;
- validation;
- sanitization;
- CSS/layout width;
- page/section location;
- readonly/disabled;
- logged-in prefills;
- entity prefills;
- query prefills;
- URL parameter prefills;
- signed/validated hidden values;
- repeaters/groups;
- calculations;
- file upload rules;
- privacy classification.

## Calculations
- formula builder;
- field references;
- arithmetic;
- min/max/rounding;
- conditional formula;
- currency precision;
- date/duration calculation;
- server calculation as authority;
- client preview;
- divide-by-zero/error behavior.

## Submission policy
- logged-in only;
- guest allowed;
- role/capability rules;
- membership rules;
- open date/time;
- close date/time;
- maximum total entries;
- maximum per user/IP/provider;
- duplicate detection;
- nonce/CSRF;
- honeypot;
- CAPTCHA provider;
- rate limits;
- abuse score provider;
- confirmation message/page/redirect.

## Entry storage
- store entries yes/no;
- retention duration;
- field encryption/provider for approved sensitive classes;
- searchable fields;
- entry status;
- notes;
- assignment;
- entry edit;
- resubmit;
- admin list columns;
- export;
- anonymization;
- delete/export privacy integration.

## Entity actions
- create post/CPT;
- update post/CPT;
- create/update user;
- create/update term;
- create/update custom table row;
- relation connect/disconnect;
- media attach;
- status assignment;
- ownership assignment;
- field mapping;
- dynamic target ID;
- permission policy;
- update only mapped fields;
- conflict behavior;
- transaction/compensation strategy.

## Workflow actions
- send notification;
- send email;
- webhook;
- external connection action;
- entity CRUD;
- relation mutation;
- membership action;
- role action;
- reservation action;
- document generation;
- payment action/provider;
- redirect;
- delay;
- schedule;
- Decision branch;
- approval task;
- bounded loop;
- custom registered Ability.

Per action:
- enabled;
- order;
- condition;
- inputs/mapping;
- timeout;
- idempotency key;
- retry count;
- retry backoff;
- failure behavior: stop/continue/branch;
- compensation action where supported;
- secret references via Vault only;
- audit/log redaction.

## Workflow runtime
- run ID;
- status;
- current step;
- started/finished;
- checkpoint;
- retry;
- resume;
- cancel;
- manual intervention;
- logs;
- correlation ID;
- replay protection;
- duplicate submission protection.

## Payment fields/providers
- amount source;
- currency;
- one-time/recurring provider mode;
- customer mapping;
- product/plan mapping;
- success/failure states;
- webhook verification;
- idempotency;
- refund provider action;
- no raw card storage.

---

# Surface 18 — Cron / Schedules

## Existing event inspector
- hook;
- next run;
- recurrence;
- arguments;
- source/owner;
- site/network scope;
- overdue/missed state;
- duration/history where observable;
- timezone display;
- UTC/local display;
- filter/search.

## WPE schedule definition
- UUID;
- name/key;
- target Ability/job;
- enabled/paused;
- one-time/recurring;
- interval preset;
- custom interval;
- cron-expression provider only where scheduler supports;
- start time;
- timezone;
- end date;
- occurrence limit;
- arguments/schema;
- idempotency key strategy;
- concurrency policy;
- lock timeout;
- execution timeout;
- retry count;
- exponential/fixed backoff;
- failure notification;
- catch-up policy after missed schedule;
- overlap policy: skip/queue/replace;
- import/export.

## Operations
- run now;
- pause/resume;
- reschedule;
- cancel queued occurrence;
- retry failed;
- inspect attempts;
- logs;
- duration;
- next occurrences preview;
- system-cron/CLI runner status;
- Action Scheduler backend status;
- queue health;
- stale claim diagnostics.

WP-Cron must never be represented as exact guaranteed wall-clock execution.

---

# Surface 19 — Notifications

## Definition
- UUID;
- name/key;
- trigger Event;
- enabled;
- priority;
- conditions;
- channels;
- revisions;
- clone/import/export.

## Recipient resolution
- explicit user;
- current user;
- role;
- capability;
- administrator;
- field/meta email;
- relation endpoint;
- query result;
- membership members;
- reservation customer/staff;
- arbitrary email only under validated policy;
- deduplicate recipients;
- maximum recipient bound.

## Channel configuration
In-app:
- title;
- body;
- icon;
- action URL/Ability;
- read/unread;
- dismissible;
- expiry.

Email:
- Email template;
- subject override;
- sender/reply-to override policy.

Webhook:
- Connection;
- endpoint/path;
- payload mapping;
- signing provider;
- timeout/retry.

Push/SMS/messaging:
- provider;
- destination field;
- template;
- opt-in requirement;
- rate/cost limits.

## Delivery
- immediate;
- delay;
- exact schedule;
- digest;
- digest grouping key;
- quiet hours/user preference;
- deduplication window;
- throttle per recipient;
- throttle global;
- retry;
- expiry;
- delivery status;
- provider message ID;
- error code/message;
- logs/retention.

## User preferences
- channel opt-in/out;
- notification category;
- digest frequency;
- quiet hours;
- language;
- unsubscribe token/provider;
- mandatory/system notification classification.

---

# Surface 20 — Emails

## Template identity
- UUID;
- name/key;
- status;
- locale/translation variants;
- subject;
- preheader;
- HTML schema;
- plaintext fallback;
- global layout;
- revisions;
- clone/import/export.

## Components
- text;
- heading;
- image;
- button;
- divider;
- spacer;
- columns;
- table;
- dynamic field/token;
- listing/repeater with bounded rows;
- footer;
- social links;
- legal/unsubscribe component.

## Style
- email-safe width;
- background;
- typography;
- colors;
- spacing;
- button style;
- border/radius where client-compatible;
- responsive stacking;
- dark-mode metadata/provider;
- inline CSS compilation;
- unsupported CSS warning.

## Sending identity
- sender name;
- sender email;
- reply-to;
- provider/connection;
- wp_mail fallback;
- SMTP/API provider;
- provider From verification status;
- test address;
- CC/BCC policy;
- attachments;
- max attachment size;
- attachment source permission.

## Delivery runtime
- queue;
- batch size;
- retry;
- backoff;
- provider rate limit;
- bounce/complaint webhook provider;
- delivery status;
- provider ID;
- error diagnostic;
- privacy-safe logs;
- retention;
- no secret/provider token exposure.

## Preview/testing
- desktop;
- mobile;
- plaintext;
- test token context;
- test send;
- missing token warnings;
- invalid URL/image warnings;
- accessibility basics;
- spam/link checker provider optional.

---

# Surface 21 — Chat

## Conversation definition/runtime
- conversation ID;
- type: direct/group/support;
- title;
- participants;
- owner/assignee;
- status;
- priority;
- tags;
- created/updated/closed;
- retention.

## Participant policy
- user;
- role/membership eligibility;
- guest provider/token mode;
- invite/add/remove;
- moderator/admin;
- mute;
- block;
- leave conversation;
- participant limit.

## Message
- message ID;
- author;
- text;
- rich text subset;
- attachment/media;
- reply-to;
- thread/provider;
- created/edited;
- edit window;
- delete policy;
- moderation state;
- reactions provider optional;
- mentions;
- read state.

## Delivery/realtime
- polling interval fallback;
- WebSocket/SSE provider;
- presence optional;
- typing indicator optional;
- reconnect;
- missed-message sync;
- unread counts;
- notification trigger;
- pagination/cursor;
- rate limit;
- anti-spam.

## Support mode
- queue/inbox;
- assignment;
- status open/pending/resolved/closed;
- SLA provider;
- internal note;
- canned reply;
- tags;
- customer metadata;
- escalation;
- transcript/export.

## Moderation/privacy
- report;
- block;
- delete/redact;
- attachment scanning provider;
- retention;
- user export/delete;
- moderator audit;
- sensitive-log redaction.

---

# Surface 37 — Reservations

## Service
- UUID;
- name;
- description;
- category;
- status;
- duration;
- price;
- currency;
- tax class/provider;
- buffer before;
- buffer after;
- min/max participants;
- locations;
- staff/providers;
- resources;
- image;
- booking window;
- cancellation/reschedule rules.

## Staff/provider
- user/employee identity;
- services;
- locations;
- working hours;
- breaks;
- days off;
- holidays;
- capacity override;
- price override;
- timezone;
- calendar connection;
- booking notification preferences.

## Resource/location
- name;
- type;
- capacity;
- location/address;
- availability;
- blackout dates;
- shareable/exclusive;
- service assignment.

## Availability
- weekly schedule;
- date-specific override;
- breaks;
- holidays;
- blackout;
- slot interval;
- service duration;
- buffers;
- lead time;
- max future window;
- timezone;
- DST behavior;
- overbooking prohibition/provider.

## Booking
- appointment vs date-range;
- service;
- customer;
- staff;
- location;
- resource;
- start/end;
- timezone;
- participants;
- status;
- notes;
- custom Fields;
- price breakdown;
- coupon;
- tax;
- deposit;
- payment state;
- recurring series;
- parent/child recurrence;
- created source;
- audit.

## Recurrence
- frequency;
- interval;
- weekdays;
- monthly rule;
- occurrence count;
- end date;
- skip conflict behavior;
- reschedule one/all/future;
- cancel one/all/future;
- price/payment treatment.

## Status/lifecycle
- pending;
- approved/confirmed;
- cancelled;
- completed;
- no-show;
- rejected;
- custom status provider;
- allowed transitions;
- staff/customer cancellation windows;
- approval policy;
- waitlist;
- waitlist promotion.

## Payments
- pay later;
- full payment;
- deposit fixed/percent;
- gateway provider;
- WooCommerce provider;
- payment due deadline;
- refund policy/provider;
- webhook verification;
- idempotency;
- no raw payment credentials.

## Calendars/notifications
- Google Calendar provider;
- Outlook/Microsoft provider;
- two-way sync policy;
- conflict checking;
- sync window;
- reminder schedule;
- confirmation;
- cancellation;
- reschedule;
- staff reminder;
- customer reminder;
- ICS attachment/link.

## Reporting
- bookings by status/service/staff/location;
- utilization;
- revenue;
- no-shows;
- cancellations;
- occupancy/capacity;
- date comparison;
- export.

---

# Surface 40 — Documents

## Template identity
- UUID;
- name/key;
- document type;
- status;
- page size;
- orientation;
- margins;
- locale;
- revisions;
- clone/import/export.

## Document types/presets
- PDF report;
- invoice;
- receipt;
- certificate;
- booking confirmation;
- membership card/provider;
- custom document.

## Components
- text;
- rich text subset;
- heading;
- image/logo;
- table;
- dynamic data;
- listing/repeater;
- totals;
- signature image/provider;
- QR code;
- barcode provider;
- divider;
- header;
- footer;
- page number;
- page break.

## Dynamic context
- entity;
- user;
- transaction;
- membership;
- reservation;
- form entry;
- query/listing;
- relation;
- current date/time;
- custom token provider.

## Rendering
- PDF engine provider;
- HTML preview;
- font embedding;
- image resolution;
- remote image policy;
- page breaks;
- repeating table headers;
- orphan/widow handling provider;
- RTL/localization;
- metadata title/author/subject;
- password/encryption provider;
- digital signature provider optional.

## Generation/runtime
- immediate vs background;
- maximum rows/pages guard;
- cache/reuse generated document;
- regenerate;
- version generated output;
- private storage;
- signed download URL/provider;
- expiry;
- attach to email;
- user dashboard access;
- audit downloads;
- retention/delete policy.

---

# Shared Wave 3 product rules

- money uses explicit currency/precision and provider-aware state;
- payment tokens/secrets never enter ordinary definitions/logs;
- retries require idempotency classification;
- workflow/form mutations pass normal Policy/Ability validation;
- notifications respect user preference/consent where applicable;
- booking availability is server-authoritative and concurrency-safe;
- scheduled jobs never promise exact WP-Cron timing;
- documents/email have separate browser/email/PDF rendering contracts;
- all user-generated/private data receives retention/privacy classification;
- frontend forms/chat/booking endpoints receive rate-limit/abuse review;
- runtime parity requires real multi-request persistence, not one-request mocks.
