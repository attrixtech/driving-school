# thrumble.driving-school — Development Progress

## Overview
**thrumble.driving-school** is a production-ready, commercial SaaS operations management platform built for driving schools, motor training institutes, and commercial driver academies.

The software is designed in **Light Mode with a compact, high-density view and small typography** for maximum operational speed and screen efficiency.

---

## Completed Phases & Modules Summary

### 1. Foundation & Multi-Tenancy Architecture (Phase 1)
- Multi-tenant architecture with shared database schema isolating tenant records via `BelongsToTenant` Eloquent global scope and lifecycle hooks.
- Multi-branch operational management supporting headquarters, subsidiary branches, branch switching in topbar, and user-branch permission mapping.
- Configurable Role-Based Access Control (RBAC):
  - Platform Super Admin
  - Business Owner
  - Branch Manager
  - Admission Executive
  - Driving Instructor
  - Student
- System activity audit logging (`activity_logs`) recording IP addresses, JSON changes, and actor credentials.

### 2. CRM & Enquiry Management (Phase 2)
- Prospective student lead capture across sources: Walk-in, Website, Phone, Referrals, Social Media, Justdial.
- **Comprehensive Call Logs & Interaction History Viewer:**
  - Real-time inline expandable accordion rows beneath each prospect row displaying chronological communication threads.
  - Dedicated **Call Logs & Communication History Modal** with full thread history, click-to-call (`tel:`) and click-to-WhatsApp (`https://wa.me/`) quick actions, and embedded inline follow-up recording.
  - Multi-channel tracking badges with visual icons: Phone Call (blue), WhatsApp (emerald), In-Person Visit (purple), Email (amber), SMS (slate).
  - Outcome categorizations: *Connected & Discussed, Callback Requested, No Answer / Busy, Agreed to Enroll, Not Interested*.
  - Staff attribution, conversation notes, and next follow-up reminder dates.
  - CRM Telemetry: Total Inquiries, Follow-ups Due Today, Overdue Follow-ups, and Total Call Logs counter.
  - JSON API endpoint: `GET /crm/leads/{lead}/logs`.
- 1-click lead conversion into full student admission with pre-populated course and package data.

### 3. Student Admission & Registration Workflow (Phase 2 & 7)
- Dual admission and student record creation inside transactional boundaries.
- Unique code generation: `STU-YYYY-XXXXX` and `ADM-YYYY-XXXXX`.
- Automated financial invoice generation with Indian GST calculation (18% GST: 9% CGST + 9% SGST).
- Initial installment collection with instant receipt generation (`RCP-YYYY-XXXXX`).
- Immediate `StudentProgress` tracking initialization.
- Printable official Student Admission Voucher with terms, declarations, and authorized signatory seals.

### 4. Fleet & Vehicle Registry (Phase 4)
- Dual-control vehicle tracking (Manual, Automatic, Petrol, Diesel, CNG, EV).
- Statutory document renewal tracking with visual operational alerts:
  - Pollution Under Control (PUC) expiry warning
  - Insurance policy expiry warning
  - Fitness certificate & permit tracking
- Vehicle fuel logging with liter quantity, rate/L, total cost, and odometer tracking (automatically integrated into business expenses).
- Vehicle periodic maintenance, brake overhauls, clutch replacements, and workshop invoice tracking.

### 5. Instructor Management & Availability (Phase 3)
- Instructor roster with certified vehicle classes (LMV, LMV-A, MCWG).
- Commercial driving license number and expiration alerts.
- Weekly availability shift configuration (Mon–Sat 07:00 to 18:00).
- Performance ratings and completed lesson session counters.

### 6. Courses, Curriculums & Packages (Phase 3)
- Vehicle category management: LMV (Four Wheeler), MCWG (Two Wheeler), LMV-A (Automatic Car).
- Configurable base courses with duration (days), practical hours (1-on-1), and theory hours.
- Flexible commercial packages (Standard Beginner, VIP Doorstep with home pickup, Refresher).

### 7. Smart Training Scheduler & Collision Engine (Phase 5)
- Automated triple-conflict detection engine:
  1. Instructor conflict detection (prevents overlapping bookings for the same trainer).
  2. Vehicle conflict detection (prevents double booking of cars/bikes and warns if in maintenance).
  3. Student conflict detection (prevents student slot clashes).
- Interactive 7-Day Master Calendar view (weekly roster matrix with day navigation).
- Lesson completion and attendance workflow:
  - Actual start and end time logging
  - Start odometer and end odometer recording (auto-calculates kilometers driven and updates vehicle odometer)
  - 1-5 star student evaluation ratings and instructor observations.

### 8. Student Progress, Mock Tests & Certificates (Phase 6 & 10)
- Real-time percentage progress calculation based on completed practical hours (weighted 70%) and theory attendance (weighted 30%).
- RTO Driving License readiness badge when practical and theory requirements are satisfied.
- RTO Mock Test module testing road signs, mandatory regulations, and traffic penalties.
- Government-compliant Certificate of Driving Competency issuance with verifiable QR security codes.
- Public online verification portal (`/certificates/verify/{code}`) enabling anyone or RTO officials to verify certificate authenticity.

### 9. Finance, GST Billing & Accounting (Phase 8)
- Indian GST compliant Tax Invoices (SAC Code 999293 - Driving tuition).
- Fee collection and partial installment payments with instant receipts.
- Branch operating expenses recording (fuel, maintenance, rent, Torrent Power electricity, salaries).
- Real-time financial metrics: Month collection, Today collection, Invoiced, Receivables, Net profit.

### 10. Public Portal & Online Enquiries (Phase 10 & 11)
- Public-facing driving school website (`/portal`).
- Course catalog with pricing and package highlights.
- Online callback request and enquiry submission form.

---

## Database Architecture

### 11. SaaS Platform Super-Admin & Multi-Tenant Management (Phase 11)
- Platform-wide telemetry covering registered academies, active paid plans, trial accounts, active students, managed fleet, and gross platform invoicing.
- Provisioning modal for new driving academy accounts with customizable subscription tiers:
  - Starter (Single Branch)
  - Professional (Multi-Branch)
  - Enterprise (High Volume)
  - Multi-Branch Fleet (Unlimited)
- Account resource caps configuration (max branches, max students, max vehicles).
- Status lifecycle toggle (Active, Trial, Expired, Suspended).

### 12. Student Self-Service Portal (Phase 9)
- Personalized learner dashboard displaying enrolled course & package, instructor, and branch.
- Live progress meters for practical driving hours completed vs required, and theory attendance.
- Confirmed next upcoming driving lesson with trainer name, phone, dual-control car registration number and time slot.
- Session-by-session practical driving history with odometer coverage (km).
- Fee payment vouchers & GST invoice download links.
- Interactive RTO Exam preparation guide with essential Motor Vehicles Act rules (3-point turn, parallel parking, gradient hill-start, 8/H tracks) and high-contrast traffic signs cheat sheet (Mandatory, Cautionary, Informatory).

### 13. Communications & Notification Gateway Hub (Phase 11)
- Omnichannel delivery log for WhatsApp Business API, TRAI DLT-compliant SMS, and SMTP Email.
- Real-time simulation and dispatch modal for:
  - Practical Lesson Reminders
  - Course Fee Balance Notices
  - Learner License Expiry Alerts
  - Form 5 Completion Certificate Ready Alerts

### 14. Data Export & Interoperability
- Dynamic streaming CSV export of student directory with licensing and enrollment metrics (`/students/export/csv`).
- Financial ledger CSV export with GST tax breakdowns (CGST, SGST, Subtotal, Paid, Due) (`/reports/export/financial-csv`).

---

## Database Architecture

### Migrated Tables:
1. `tenants`
2. `companies`
3. `branches`
4. `users` & `user_branches`
5. `roles`, `permissions`, `permission_role`, `role_user`
6. `vehicle_categories`
7. `courses` & `course_packages`
8. `instructors` & `instructor_availabilities`
9. `vehicles`, `vehicle_documents`, `vehicle_fuel_logs`, `vehicle_maintenances`
10. `leads` & `lead_followups`
11. `students`, `admissions`, `student_documents`
12. `lesson_schedules`, `lesson_attendances`, `practical_assessments`
13. `theory_classes`, `theory_attendances`, `mock_tests`, `student_progress`
14. `invoices`, `payments`, `expenses`
15. `certificates`, `activity_logs`, `notifications`

---

## Automated Test Coverage
- **20 Feature & Unit Tests** executed with **70 Assertions** (100% Passing):
  - `AuthenticationTest`: Login rendering, authentication, 1-click persona quick-login, unauthenticated redirection.
  - `AdmissionsWorkflowTest`: Multi-model admission creation, invoice generation with 18% GST, student progress initialization.
  - `LessonSchedulingConflictTest`: Triple collision prevention for instructor overlapping slots and vehicle double-bookings.
  - `BillingAndProgressTest`: Payment application to invoice/admission balances, mock test pass/fail evaluation, certificate generation and public QR verification.
  - `SaasAndSelfServiceTest`: Platform Super-Admin tenant provisioning and plan upgrades, student self-service portal rendering, simulated notification dispatch across WhatsApp/SMS, and CSV streaming exports.
  - `ExampleTest`: Public portal rendering and brand integrity.

---

## Credits
- **Developed by:** [Attrix Technologies](https://www.attrixtech.com)
- **Marketing by:** [Infusionn](https://www.infusionn.in)
