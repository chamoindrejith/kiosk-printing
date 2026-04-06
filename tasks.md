# TASKS.md

## Project
Laravel + Blade self-service QR printing system for WiFi printers with LankaQR payment and page-resume support.

---

## Delivery Strategy
Build in phases so the team can validate the hardest risks early:
1. PDF flow and pricing
2. LankaQR payment flow
3. Direct printer communication over WiFi
4. Resume / recovery behavior
5. Admin operations and reporting

---

## Phase 0 — Discovery and Risk Validation

### T0.1 Confirm printer model and protocol capabilities
**Owner:** Printer Integration Agent  
**Goal:** Verify the actual WiFi printer supports the needed protocol and status reporting.

**Checklist**
- Identify exact printer model(s).
- Confirm supported protocols: IPP, IPPS, raw 9100, vendor API, SNMP.
- Confirm available job status and page progress signals.
- Confirm color, duplex, paper size, and orientation controls.
- Confirm whether page-level confirmation is truly available.

**Deliverable**
- Capability matrix per printer model.
- Go / no-go decision for direct WiFi printing.

**Blocking:** Yes

---

### T0.2 Define integration strategy
**Owner:** Laravel Architecture Agent + Printer Integration Agent  
**Goal:** Choose the printer communication pattern.

**Checklist**
- Choose the primary protocol.
- Choose the fallback strategy when page-level confirmation is weak.
- Decide whether to send page-by-page, chunked, or full-job based on capability.
- Define timeout, retry, and pause conditions.

**Deliverable**
- Printer integration technical design note.

**Blocking:** Yes

---

## Phase 1 — Laravel Foundation

### T1.1 Create Laravel project skeleton
**Owner:** Laravel Architecture Agent

**Checklist**
- Initialize Laravel project.
- Configure Blade layout structure.
- Configure auth-less guest routes for kiosk flow.
- Configure admin routes and middleware.
- Configure queue, cache, filesystem, and scheduler.

**Deliverable**
- Running app skeleton with guest and admin sections.

---

### T1.2 Define domain structure
**Owner:** Laravel Architecture Agent

**Checklist**
- Create domain folders for Printing, Pdf, Pricing, Payments.
- Add service interfaces for printer and payment gateways.
- Add action classes for kiosk and admin flows.

**Deliverable**
- Clean project structure ready for implementation.

---

### T1.3 Environment and operations setup
**Owner:** DevOps / Operations Agent

**Checklist**
- Define `.env` requirements.
- Configure storage disks for uploads and generated print assets.
- Configure queue worker and scheduler.
- Configure log channels for printing and payments.

**Deliverable**
- Environment setup guide.

---

## Phase 2 — Data Model and Persistence

### T2.1 Create database schema
**Owner:** Data / Persistence Agent

**Entities**
- printers
- printer_capabilities
- pricing_rules
- print_jobs
- print_job_pages
- payments
- payment_events
- print_job_events

**Checklist**
- Create migrations.
- Add indexes for job status, printer code, payment status, and retention cleanup.
- Add foreign keys and cascade rules carefully.

**Deliverable**
- Migration set with rollback support.

---

### T2.2 Create Eloquent models and relationships
**Owner:** Data / Persistence Agent

**Checklist**
- Build models and casts.
- Define relationships and query scopes.
- Add lifecycle helpers for status updates.

**Deliverable**
- Models ready for controllers and services.

---

### T2.3 Seed initial data
**Owner:** Data / Persistence Agent

**Checklist**
- Seed at least one printer.
- Seed example pricing rules for sizes, color modes, and duplex modes.
- Seed demo admin account if needed.

**Deliverable**
- Usable development seed data.

---

## Phase 3 — Kiosk Guest Flow

### T3.1 QR landing route
**Owner:** UI / Blade Agent

**Checklist**
- Route: `/kiosk/{printer_code}`
- Validate printer exists and is active.
- Show printer name/location and upload entry point.

**Deliverable**
- Mobile-friendly printer landing page.

---

### T3.2 PDF upload flow
**Owner:** PDF Processing Agent + UI / Blade Agent

**Checklist**
- Accept PDF only.
- Enforce size and page count limits.
- Validate corruption.
- Store original upload.
- Extract page count.

**Deliverable**
- Stable upload step with validation.

---

### T3.3 Print option form
**Owner:** UI / Blade Agent

**Fields**
- Color / black
- Simplex / duplex
- Copies
- Page range
- Paper size
- Orientation

**Checklist**
- Mobile-first form UX.
- Server-side validation.
- Preserve form state on error.

**Deliverable**
- Fully working options step.

---

### T3.4 Page range parsing and sheet calculation
**Owner:** PDF Processing Agent + Pricing Agent

**Checklist**
- Parse inputs like `1-3,5,7-9`.
- Reject invalid or out-of-range pages.
- Calculate effective pages.
- Convert effective pages to sheet quantity based on simplex/duplex and copies.

**Deliverable**
- Trusted page range and sheet calculation service.

---

### T3.5 Price preview
**Owner:** Pricing Agent + UI / Blade Agent

**Checklist**
- Show unit price.
- Show effective pages.
- Show sheet quantity.
- Show copies.
- Show total clearly.

**Deliverable**
- Price breakdown step before payment.

---

## Phase 4 — LankaQR Payment Flow

### T4.1 Create payment intent/order
**Owner:** Payment Agent

**Checklist**
- Generate payment reference linked to print job.
- Create payment request to LankaQR provider.
- Persist pending payment state.

**Deliverable**
- Payment initiation service.

---

### T4.2 Payment page and QR display
**Owner:** UI / Blade Agent + Payment Agent

**Checklist**
- Show amount, reference, expiration.
- Display LankaQR QR.
- Poll for payment status or use status refresh UI.

**Deliverable**
- Payment screen.

---

### T4.3 Webhook/callback handling
**Owner:** Payment Agent

**Checklist**
- Validate callback signature/authentication.
- Support idempotency.
- Update payment and job state safely.
- Record raw event payloads for audit.

**Deliverable**
- Secure webhook endpoint.

---

### T4.4 Confirm print gate
**Owner:** Product Agent + UI / Blade Agent + Payment Agent

**Checklist**
- Require payment success before confirm.
- Require explicit user confirmation after payment success.
- Prevent duplicate confirmation.

**Deliverable**
- Confirm-and-dispatch step.

---

## Phase 5 — PDF Processing and Print Preparation

### T5.1 Build print-ready asset pipeline
**Owner:** PDF Processing Agent

**Checklist**
- Normalize PDF handling.
- Generate per-page or per-chunk assets depending on printer strategy.
- Preserve mapping between logical pages and print order.

**Deliverable**
- Print-ready asset generation service.

---

### T5.2 Track per-page records
**Owner:** Data / Persistence Agent + PDF Processing Agent

**Checklist**
- Create `print_job_pages` rows for every page to be printed.
- Include copy number, logical page number, and sequence order.

**Deliverable**
- Page-level tracking data.

---

## Phase 6 — Printer Communication over WiFi

### T6.1 Implement printer gateway interface
**Owner:** Printer Integration Agent

**Checklist**
- Create `PrinterGatewayInterface`.
- Define methods for capability fetch, submit, status, cancel, and resume support.

**Deliverable**
- Stable printer abstraction.

---

### T6.2 Implement concrete WiFi printer adapter
**Owner:** Printer Integration Agent

**Checklist**
- Build adapter for the chosen printer protocol.
- Map Laravel print settings to device commands.
- Handle paper size and orientation correctly.

**Deliverable**
- Working printer adapter for the target device.

**Blocking:** Yes

---

### T6.3 Dispatch flow
**Owner:** Printer Integration Agent + Laravel Architecture Agent

**Checklist**
- Queue dispatch after confirm.
- Lock job to prevent duplicate dispatch.
- Move status through `queued → dispatching → printing`.

**Deliverable**
- Safe dispatch pipeline.

---

### T6.4 Progress polling and confirmation
**Owner:** Printer Integration Agent

**Checklist**
- Poll printer for job or page status.
- Update `print_job_pages` to `confirmed` only when truly confirmed.
- Update print job `last_confirmed_page`.

**Deliverable**
- Progress tracking service.

---

### T6.5 Pause, failure, and resume logic
**Owner:** Printer Integration Agent + QA / Reliability Agent

**Checklist**
- Detect stuck/offline conditions.
- Move job to `paused` or `failed`.
- Resume from last confirmed printed page.
- Record reason codes and timestamps.

**Deliverable**
- Resume-capable job handling.

---

## Phase 7 — Admin Panel

### T7.1 Admin layout and navigation
**Owner:** UI / Blade Agent

**Checklist**
- Dashboard shell.
- Navigation for printers, pricing, jobs, payments, reports.

**Deliverable**
- Admin UI foundation.

---

### T7.2 Printer management
**Owner:** UI / Blade Agent + Data / Persistence Agent

**Checklist**
- Add/edit printer.
- Manage printer code, name, location, connectivity settings, active state.
- Show current reachability and capability info.

**Deliverable**
- Printer admin screens.

---

### T7.3 Pricing management
**Owner:** Pricing Agent + UI / Blade Agent

**Checklist**
- CRUD for sheet-based pricing rules.
- Support paper size, color mode, duplex mode, and printer-specific overrides.

**Deliverable**
- Pricing admin screens.

---

### T7.4 Job monitoring and retry tools
**Owner:** UI / Blade Agent + Printer Integration Agent

**Checklist**
- Job list and detail view.
- Filter by printer, status, date, payment state.
- Manual retry/resume action where allowed.
- Show page-level progress timeline.

**Deliverable**
- Operational job console.

---

### T7.5 Payment monitoring
**Owner:** Payment Agent + UI / Blade Agent

**Checklist**
- Payment list and detail view.
- Reconciliation indicators.
- Link payment events to print jobs.

**Deliverable**
- Payment admin screens.

---

### T7.6 Reports
**Owner:** Product Agent + UI / Blade Agent + Data / Persistence Agent

**Checklist**
- Revenue by day/printer.
- Jobs by status.
- Sheets printed by size and mode.
- Failure and resume counts.

**Deliverable**
- Admin reports pages.

---

## Phase 8 — Retention, Cleanup, and Security

### T8.1 File retention policy
**Owner:** DevOps / Operations Agent + Data / Persistence Agent

**Checklist**
- Retain uploads for a few days.
- Delete originals and generated assets on schedule.
- Preserve job metadata even after file deletion.

**Deliverable**
- Scheduled cleanup command.

---

### T8.2 Security hardening
**Owner:** DevOps / Operations Agent + QA / Reliability Agent

**Checklist**
- Validate MIME and extension.
- Add rate limiting to guest routes.
- Guard admin routes.
- Log suspicious upload/payment activity.

**Deliverable**
- Basic hardening baseline.

---

## Phase 9 — Testing and Validation

### T9.1 Feature tests
**Owner:** QA / Reliability Agent

**Checklist**
- Upload and configure job.
- Price calculation.
- Payment success path.
- Confirm print path.
- Admin CRUD basics.

**Deliverable**
- Laravel feature tests.

---

### T9.2 Failure scenario testing
**Owner:** QA / Reliability Agent

**Checklist**
- Invalid PDF.
- Invalid page range.
- Payment callback replay.
- Printer offline before dispatch.
- Printer stuck during print.
- Resume from last confirmed page.

**Deliverable**
- Failure test matrix and results.

---

### T9.3 Mobile browser QA
**Owner:** UI / Blade Agent + QA / Reliability Agent

**Checklist**
- Test iPhone Safari.
- Test Android Chrome.
- Test low-bandwidth conditions.

**Deliverable**
- Mobile QA checklist.

---

## Immediate Next Tasks
1. Confirm the exact WiFi printer model.
2. Produce the printer capability matrix.
3. Choose the printer protocol and resume strategy.
4. Scaffold Laravel project and database schema.
5. Build kiosk upload/options/pricing flow.
6. Integrate LankaQR sandbox flow.
7. Implement one real printer adapter and validate end-to-end printing.

---

## Open Risks
- Some WiFi printers may not expose reliable page-level confirmation.
- Direct WiFi printer access from the Laravel host may depend on the deployment topology.
- LankaQR provider details may vary by bank/gateway implementation.
- Orientation and paper size support can differ by printer model and driver/protocol.

---

## Definition of MVP
MVP is complete when a guest user can:
- scan a printer QR
- upload a PDF
- choose color mode, simplex/duplex, copies, page range, paper size, and orientation
- see sheet-based pricing
- pay via LankaQR
- confirm the print
- start printing on the target WiFi printer
- and the system can pause/fail and resume from the last confirmed printed page where the printer supports confirmation

