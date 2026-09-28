# RTFTS Workflow Code Map And Risk Checklist

Last reviewed: 2026-09-24

Purpose: keep one practical working reference for the RTFTS production-preparation work. The site is still pre-live and current data is fake, but users have already been trained on the UI, so visual/layout changes should be conservative.

## Current Business Flow

1. Lawyer filing
   - Lawyer creates a draft case and receives a temporary barcode.
   - Temporary barcode is only for filing-stage work.
   - Main code: `LawyerCaseController`, filing routes in `routes/web.php`.

2. Filing Section
   - Filing Section scans a temporary barcode, verifies/edits required case data, and converts it to a permanent RTFTS case.
   - Filing direct-create can create a permanent case without prior lawyer draft.
   - Permanent barcode format is `13YYYYNNNNNN`.
   - Human reference is `WRPET Serial/Year`.
   - Main code: `Admin\FilingController`, `App\Services\RtftsCaseReference`.

3. Normal section receive
   - Any staff/admin section can receive a valid permanent case barcode or final case number.
   - Temporary barcode is rejected outside Filing Section.
   - If an old valid RTFTS identifier is scanned and not found, the system can create an old case record and receive it into the scanner's custody.
   - Same holder cannot receive the same file again.
   - Rejected/returned-to-lawyer cases are blocked from normal receive.
   - Main code: `Admin\SectionReceiveController`.

4. Court movement
   - Office Assistant, Dealing Assistant, Assistant Registrar Office, and Super Admin can access Send to Court/return routes.
   - Send to Court requires the file to be in the operator's personal custody.
   - Sending moves a case to `Court`, clears current holder, creates a batch/item, and writes a `dispatch_to_court` movement.
   - Receive from Court can be done through the normal Receive File screen for Super Admin, Office Assistant, or Dealing Assistant.
   - Main code: `Admin\CourtDispatchController`, `Admin\SectionReceiveController`.

5. Internal file handover
   - A current holder selects another active staff user, scans one or more files, and creates a pending handover.
   - Custody remains with the sender until the intended recipient scans and receives each file.
   - Sender or Super Admin may cancel pending files with a mandatory audit reason.
   - Pending handovers block Send to Court, rejection, court return, and Registrar Override.
   - Main code: `Admin\FileTransferController`, `App\Services\FileTransferService`.

6. Registrar lookup and override
   - Assistant Registrar Office, Registrar, and Super Admin can search lookup/timeline and perform override receive.
   - Override changes current section with an audit movement and reason.
   - Main code: `Admin\RegistrarTrackingController`.

7. Reports and timeline
   - Register report is based on `file_movements`.
   - Timeline is based on a case's movement history.
   - Main code: `Admin\RegistrarTrackingController`.

## Required Master Data At Install

Keep these from the beginning:

- Departments: from `Department::CANONICAL_NAMES`.
- Roles: `Super Admin`, `Admin`, `Staff`.
- One Super Admin user:
  - Email: `super.admin@writ.local`
  - Employee ID: `0000`
  - Password: `password`
  - No Card ID by default
- Courts: required before court dispatch can be used.
- App settings/env: `APP_KEY`, database connection, `APP_TIMEZONE=Asia/Dhaka` or `config/app.php` timezone `Asia/Dhaka`.

Important warning: `DatabaseSeeder` is destructive by design. It truncates users, lawyers, cases, movements, sessions, and registration sequences, then recreates master data and the Super Admin. It is suitable before live only, not after live data starts.

## Data That Should Not Be Deleted After Live

After live launch, do not physically delete:

- Users who created/received/returned/dispatched any file.
- Lawyers who submitted any case.
- Departments used in cases or movements.
- Courts used in dispatch/return movements.
- Cases once permanent barcode is generated.
- File movements.
- Court dispatch batches and items.
- Uploaded case attachments.
- Registration sequence rows.

Preferred live behavior:

- Deactivate users instead of deleting.
- Lock or hide departments/courts once used.
- Keep movements append-only except a controlled Super Admin correction flow.
- Use backup plus dry-run verification before any cleanup command.

## Access And Routing Notes

- Manual admin/staff login uses `employee_id` and password.
- Tap/card login uses `login_id` only.
- Lawyer login is separate under `/lawyer/login`.
- Department-based routing is still name-based in middleware and login redirect logic.
- Custom departments can use the generic Receive File screen by default.
- Special sections are recognized by section-name text:
  - Filing Section
  - Office Assistant
  - Dealing Assistant
  - Assistant Registrar Office
  - Registrar
  - Affidavit, Requisite, Put-Up, Typing, Compare, Superintendent, Ready Table, Record Room, Court

## Immediate Risk Checklist

High risk before live:

1. Court sending and internal handover integrity
   - Personal custody, pending-handover blocking, and case-first row locking are implemented.
   - Audit task: regression-test concurrent Send to Court, receive, cancel, reject, and override attempts.

2. Large register report and PDF
   - `register-report` and `register-report/pdf` use `get()` for all matching movements.
   - This can become slow or memory-heavy with 300,000 cases and long movement history.
   - Recommended fix: paginate HTML, cap PDF range/export size, and add a queued export later if needed.

3. Search performance
   - Lookup uses broad `%keyword%` searches across cases, parties, and lawyers.
   - Good for user flexibility, but expensive at large scale.
   - Recommended fix: keep exact barcode/case-number path fast, then add indexes/full-text strategy for broad search.

4. Race conditions in remaining custody paths
   - Internal handover, Send to Court, rejection, court return, and override now use case-first locking where they conflict.
   - Audit task: inspect filing conversion, old-case intake, and every remaining current-custody update for the same lock order.

5. Department names as workflow rules
   - Workflow routing and permissions depend on department names.
   - Renaming special departments can affect routing/access.
   - Recommended fix: add stable department codes or capability flags later, while keeping existing UI labels unchanged.

Medium risk:

1. Dashboard aggregation
   - Dashboard executes several count/group queries on cases and movements.
   - Fine now, but should be checked with realistic volume.

2. Court batch search
   - Batches are not preloaded unless searched, which is good.
   - Searching by case number/barcode inside batch items can still be heavy at scale.

3. Physical delete behavior
   - `CourtCase` model blocks deleting non-draft cases, but seeders/direct query truncation bypass model events.
   - Keep destructive commands limited to pre-live/dev workflows only.

4. Attachment storage
   - Cleanup before live can remove fake uploads.
   - After live, attachments must be backed up with database IDs because movement/case context depends on them.

## Recommended Starting Order

1. Complete the workflow/authorization matrix and remaining row-lock audit.
2. Check current MySQL query plans and index overlap using production-scale assumptions.
3. Change register report HTML to pagination and protect PDF from huge exports.
4. Add production-volume seed tooling and concurrent scan benchmarks.
5. Add or run tests for:
   - temporary barcode rejected outside Filing Section
   - old valid barcode creates/receives once
   - same user cannot receive again
   - court receive only by allowed users
   - court dispatch only from valid custody
6. Keep UI stable except small text/validation changes needed by the workflow.
