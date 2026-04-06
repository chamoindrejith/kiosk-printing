# AGENTS.md

## Project
Self-service QR-based PDF printing kiosk built with **Laravel + Blade**.

Users scan a QR code attached to a WiFi printer, open a web page, upload a PDF, choose print settings, review sheet-based pricing, pay via LankaQR, confirm printing, and the system sends the job directly to the WiFi printer. The system includes admin tools for printers, pricing, jobs, payments, retries, and reporting.

---

## Product Goals
- Guest-only, browser-based print flow.
- PDF-only for phase 1.
- Direct printer communication over WiFi.
- LankaQR payment flow.
- Printing begins only after **payment success** and explicit **Confirm Print**.
- Resume from the **last confirmed printed page**.
- Admin control for pricing, printers, jobs, payments, and failures.
- Uploaded files retained for a few days, then purged automatically.

---

## Core Assumptions
- Each printer has a unique QR URL such as `/kiosk/{printer_code}`.
- Each WiFi printer is reachable from the Laravel app environment over the network.
- Printer capabilities vary by model, so printer integration must be abstracted behind a service layer.
- Exact page confirmation depends on printer support. If a printer cannot confirm page completion reliably, the system must surface that limitation in admin diagnostics and use a safe fallback strategy.
- Phase 1 is web-only; there is no local kiosk app and no human operator.

---

## Suggested Team / Agent Roles

### 1) Product Agent
Owns user flow, scope boundaries, acceptance criteria, and feature sequencing.

**Responsibilities**
- Define MVP and phase-based scope.
- Keep flows aligned with guest-only usage.
- Ensure payment-before-print and confirm-before-dispatch behavior.
- Review admin needs and operational edge cases.

**Primary outputs**
- Refined requirements
- Acceptance criteria
- Flow diagrams
- Prioritized backlog

---

### 2) Laravel Architecture Agent
Owns project structure, domain boundaries, service contracts, queue design, and resilience patterns.

**Responsibilities**
- Define modules, directories, and service abstractions.
- Design job lifecycle and queueing approach.
- Separate controller, action, domain, integration, and admin concerns.
- Define cleanup, retries, and resumability strategy.

**Primary outputs**
- Folder structure
- Domain model definitions
- Service interfaces
- Queue and scheduler plan

---

### 3) UI / Blade Agent
Owns all Blade views, guest print flow screens, validation feedback, and admin dashboards.

**Responsibilities**
- Build mobile-first kiosk pages.
- Build printer landing, upload, options, checkout, payment, and status pages.
- Build admin pages for printers, pricing, jobs, payments, and reports.
- Keep the UX simple and clear for walk-up users.

**Primary outputs**
- Blade templates
- Components / partials
- Validation and status UI
- Admin navigation layout

---

### 4) Printer Integration Agent
Owns direct printer communication over WiFi, capability detection, job dispatch, progress tracking, and resume support.

**Responsibilities**
- Implement printer gateway abstraction.
- Evaluate and integrate the selected protocol for the target printer model.
- Support printer capability lookup: paper sizes, duplex, color, orientation, job status.
- Track the last confirmed printed page.
- Handle stuck, offline, timeout, and retry cases.

**Primary outputs**
- Printer service contracts
- Concrete printer drivers / adapters
- Progress polling logic
- Retry/resume logic

---

### 5) Payment Agent
Owns LankaQR order creation, callback/webhook validation, payment reconciliation, and payment-to-job linking.

**Responsibilities**
- Integrate LankaQR provider workflow.
- Validate callback authenticity.
- Prevent duplicate payment application.
- Mark jobs as payable, paid, expired, failed, or refunded if applicable.

**Primary outputs**
- Payment service
- Webhook handlers
- Reconciliation rules
- Payment state machine

---

### 6) PDF Processing Agent
Owns PDF validation, metadata extraction, page counting, page range expansion, and print-ready chunking.

**Responsibilities**
- Accept PDF-only uploads.
- Validate size, page count, and corruption.
- Count pages accurately.
- Resolve page ranges into explicit page lists.
- Prepare split-page or chunk assets when needed for resume support.

**Primary outputs**
- PDF validation rules
- Page count service
- Page range parser
- Split/chunk generation pipeline

---

### 7) Pricing Agent
Owns sheet-based price calculation, printer-specific pricing, size-based pricing, color/duplex logic, and preview totals.

**Responsibilities**
- Calculate sheet quantity from page count, copies, duplex mode, and page range.
- Support choosable paper sizes.
- Support per-printer or global pricing configuration.
- Return transparent pricing breakdowns.

**Primary outputs**
- Pricing rules
- Calculation service
- Admin pricing screens
- Price preview logic

---

### 8) Data / Persistence Agent
Owns migrations, Eloquent models, indexes, retention policy, and auditable state transitions.

**Responsibilities**
- Design schema for printers, jobs, pages, payments, pricing, and events.
- Capture progress at the page level where possible.
- Support retention and purge strategy.
- Preserve audit trail for failures and retries.

**Primary outputs**
- Migrations
- Models and relationships
- Seeders
- Retention jobs

---

### 9) QA / Reliability Agent
Owns scenario testing, failure injection, printer-offline cases, stuck job recovery, and payment edge-case coverage.

**Responsibilities**
- Test guest flow on mobile browsers.
- Verify pricing accuracy for page range, orientation, paper size, simplex/duplex, and copies.
- Simulate webhook retries, printer disconnects, and timeouts.
- Verify resume from the last confirmed printed page.

**Primary outputs**
- Test plans
- Manual QA checklist
- Feature tests
- Failure scenario matrix

---

### 10) DevOps / Operations Agent
Owns deployment, storage, queue workers, scheduler, network configuration, logging, and monitoring.

**Responsibilities**
- Configure Laravel app, queue, scheduler, storage, and SSL.
- Ensure app network can reach WiFi printers.
- Manage logs for print and payment events.
- Set up cleanup of uploaded files after a few days.

**Primary outputs**
- Deployment checklist
- Environment variables list
- Monitoring / alerting guidance
- Backup and cleanup tasks

---

## Collaboration Rules
- All printer communication must go through a dedicated printer service abstraction; controllers must not speak directly to printer protocols.
- All payment changes must be idempotent.
- Print dispatch must occur only when both conditions are true:
  1. payment status is successful
  2. user has explicitly confirmed the print action
- Job lifecycle changes must be recorded with timestamps.
- Resume logic must rely on the last confirmed printed page, not last attempted page.
- Do not couple pricing logic to Blade templates; use dedicated services/actions.
- PDF uploads must be validated before pricing or payment.
- Guest flow must avoid unnecessary friction; no account creation.

---

## Recommended Laravel Modules
- `app/Actions/Kiosk/*`
- `app/Actions/Admin/*`
- `app/Domain/Printing/*`
- `app/Domain/Payments/*`
- `app/Domain/Pricing/*`
- `app/Domain/Pdf/*`
- `app/Services/Printers/*`
- `app/Services/Payments/*`
- `app/Jobs/*`
- `app/Console/Commands/*`
- `resources/views/kiosk/*`
- `resources/views/admin/*`

---

## State Models

### Print Job Status
- `draft`
- `configured`
- `awaiting_payment`
- `payment_pending`
- `payment_success`
- `awaiting_confirmation`
- `queued`
- `dispatching`
- `printing`
- `paused`
- `completed`
- `failed`
- `cancelled`
- `expired`

### Payment Status
- `initiated`
- `pending`
- `successful`
- `failed`
- `expired`
- `refunded`

### Page Status
- `pending`
- `sent`
- `confirmed`
- `failed`
- `skipped`

---

## Non-Functional Priorities
1. Reliability of payment and print job state.
2. Safe printer dispatch and resume.
3. Mobile usability.
4. Clear operational visibility for admins.
5. Secure short-term storage and scheduled deletion.

---

## Definition of Done
A feature is done when:
- It is implemented in Laravel + Blade.
- Validation and error states are handled.
- Admin visibility exists where relevant.
- Logging/auditing is adequate.
- Tests or a QA checklist cover the main path and key failures.
- Documentation in `TASKS.md` is updated if scope or order changes.

