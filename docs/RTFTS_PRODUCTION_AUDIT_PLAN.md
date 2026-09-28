# RTFTS Production Readiness Audit Plan

This document preserves the agreed plan for making RTFTS ready for long-term file-tracking production use.

## Objective

Prepare RTFTS for safe growth toward:

- 3 million or more writ cases
- 10 million or more file movement records
- Five years of continuous operation
- Concurrent barcode scan, receive, dispatch, search, and report workflows
- Secure document attachment handling
- Reliable dashboard and register reporting

## Working Rules

- Read repository instructions, README, project structure, routes, migrations, models, controllers, services, views, commands, and tests before changing code.
- Check Git status before edits and preserve unrelated user changes.
- Do not change business workflow unless a confirmed defect requires it.
- Do not remove existing features.
- Do not expose `.env` secrets, passwords, keys, or tokens.
- Do not run destructive database commands during audit.
- Use forward-only Laravel migrations for schema/index fixes.
- Add indexes only for confirmed query patterns.
- Keep changes focused, reviewable, and backward compatible where possible.
- Stop and explain first if a fix requires destructive data migration, major architecture change, or business-rule decision.

## Phase 1: Workflow-To-Code Map

Map each business step to actual code:

- Lawyer filing
- Temporary barcode generation
- Filing Section verification
- Permanent RTFTS barcode and case number generation
- Affidavit processing
- Internal section receiving
- Internal user-to-user handover and pending receipt
- Movement between desks/departments
- Send to court
- Receipt from court
- Old case receiving
- Final status and Record Room transfer
- Search, lookup, timeline, dashboard, and reports
- Attachment upload/list/download
- User, department, court, role, and permission management

For each step identify:

- Route
- Controller method
- Request validation
- Service/helper class
- Model relationships
- Tables touched
- Views/API endpoints

## Phase 2: Database And Schema Audit

Review these high-risk tables first:

- `cases`
- `file_movements`
- `users`
- `departments`
- `courts`
- `court_dispatch_batches`
- `court_dispatch_batch_items`
- `case_files`
- `case_petitioners`
- `case_respondents`
- `lawyers`
- `case_registration_sequences`
- `file_transfer_batches`
- `file_transfer_items`

Check:

- Primary keys and foreign keys
- Unique constraints
- Index coverage and index order
- Nullable fields
- Oversized string columns
- Repeated text where reference IDs may be better
- Barcode uniqueness
- Case number uniqueness
- Attachment metadata design
- Current state vs movement history separation

## Phase 3: Current State And Movement History

Confirm this principle:

- `cases` keeps fast current state: current section, current holder, status, last custody time.
- `file_movements` keeps complete append-only history.

For every barcode operation:

- Validate barcode/case number.
- Lock or safely load the case where concurrency matters.
- Insert movement record.
- Update current case state.
- Keep both actions in one transaction.
- Prevent duplicate or repeated scan events.

## Phase 4: Query Review

Review actual Eloquent/Query Builder queries in:

- Dashboard
- Lookup/search
- Register report
- Timeline
- Section receive
- Court dispatch/return
- Court batches
- Filing pages
- User and lawyer management
- Attachment handling

Look for:

- N+1 queries
- Unbounded `get()` or `all()`
- Collection filtering after large loads
- Missing eager loading
- Missing selected columns
- Leading wildcard searches
- Large `OR` conditions
- Date filtering with inefficient indexes
- Report queries scanning full movement history
- Synchronous large PDF/export generation

Where possible, verify important queries with safe `EXPLAIN`.

## Phase 5: Index Plan

Prepare targeted indexes only for confirmed query patterns, such as:

- Permanent barcode lookup
- Temporary barcode lookup
- Final case year and serial
- Current section and status
- Current holder
- Movement history by case and date
- Movement report by received date, section, and type
- Court dispatch batch lookup
- User search by employee ID/card/email
- Department/court filtering

Avoid overlapping indexes that slow inserts into `file_movements`.

## Phase 6: Laravel Code Audit

Review:

- Controller responsibilities
- Service-layer use
- Duplicate code
- Model accessors and relationships
- Middleware and authorization
- Validation
- Blade loops
- Session usage
- Logging
- Jobs, queues, and scheduled commands

Possible safe improvements:

- Eager loading
- Explicit column selection
- Chunking/lazy iteration for exports
- Cursor/keyset pagination where appropriate
- Cached reference data for departments/courts
- Queue-based heavy reports
- Reduced repeated aggregate queries

## Phase 7: Barcode Reliability

Audit critical scan flows for:

- Duplicate scan
- Double-click submission
- Browser retry
- Network retry
- Two users scanning the same file
- Dispatching a file already in court
- Receiving a file in the wrong department
- Invalid barcode
- Temporary barcode used outside Filing Section
- Incorrect current custody

Preferred protections:

- Transactions
- Row locking for custody-changing operations
- Server-side status validation
- Idempotency where needed
- Clear user-facing errors

## Phase 8: Attachments

Confirm:

- Files are outside MySQL.
- MySQL stores metadata and secure storage path only.
- Laravel storage abstraction is used.
- Filename collisions are prevented.
- File extension, MIME, and size are validated.
- Downloads are authorized.
- Internal storage paths are not exposed.
- Future NAS/S3-compatible storage remains possible.

## Phase 9: Dashboard And Reporting

Review whether each report/dashboard should use:

- Direct indexed query
- Short-lived cache
- Daily/monthly summary table
- Queue-based PDF/export generation

Do not cache live custody in a way that can show stale file location.

## Phase 10: Security And Integrity

Check:

- Authorization at query level
- Department/court access
- Mass assignment
- Raw SQL and dynamic sorting
- Report filter limits
- Rate limiting
- Sensitive logging
- Session/cache configuration

## Phase 11: Tests And Benchmarking

Add or improve tests for:

- Barcode lookup
- Temporary-to-permanent transition
- Section receive
- Court dispatch
- Court return
- Duplicate scan prevention
- Current custody accuracy
- Movement history
- Unauthorized access
- Search/report filters
- Attachment metadata

Create scalable performance seed/command strategy without committing huge data.

Target validation goals:

- Indexed barcode lookup below 100 ms at database level
- Common app request below 500 ms
- Paginated movement history below 500 ms
- Dashboard below 2 seconds or served from summary/cache
- Barcode scan response fast enough for kiosk use

## Phase 12: Production Configuration Review

Review without exposing secrets:

- `.env.example`
- DB config
- Cache config
- Queue config
- Session config
- Logging config
- Scheduler/worker expectations
- Nginx/PHP-FPM assumptions
- Docker files if present
- Backup and attachment backup needs

Confirm support for:

- `APP_DEBUG=false`
- config caching
- Redis cache/queues
- queue workers
- scheduled jobs
- log rotation
- health checks
- graceful worker restart
- database backup
- attachment backup

## Required Deliverables When Audit Is Performed

- Executive summary
- Workflow-to-code map
- Findings table by severity
- Database and index plan
- Query review with evidence
- Implemented changes list
- Test and benchmark results
- Remaining work list
- Server-sizing input checklist

## Recommended Immediate Order

1. Freeze a pre-audit baseline and capture schema, index, and query evidence.
2. Rebuild the workflow-to-code and authorization matrix, including internal handovers.
3. Audit custody invariants, row locking, idempotency, and alternate movement paths.
4. Audit schema, foreign keys, uniqueness rules, and index overlap.
5. Measure lookup, search, dashboard, timeline, and report queries with `EXPLAIN`.
6. Implement only confirmed Critical/High integrity and security fixes first.
7. Paginate large HTML reports and define capped or queued PDF/export behavior.
8. Add production-volume data generation and concurrent scan benchmarks.
9. Review authentication, authorization, attachments, logging, backup, and restore.
10. Prepare the cPanel deployment runbook, rollback procedure, and launch checklist.

## Current Baseline Completed

As of 2026-09-28:

- Permanent barcode and human case-reference rules are centralized.
- Internal user-to-user handover has pending, partial, received, and cancelled states.
- One case cannot have more than one active handover.
- Send and receive operations use transactions and case-first row locking.
- Send to Court requires personal custody and is blocked by a pending handover.
- Rejection, court return, and Registrar Override cannot bypass a pending handover.
- Handover lists use indexed pagination; lookup, timeline, reports, and PDF expose audit details.
- Focused scale indexes have been added for custody and movement history.
- User departments are enforced by a real foreign key after validating existing users.
- Filing conversion, normal receipt, and court return repeat custody checks under row locks.
- Movement history is append-only through Eloquent.
- Register HTML is paginated and synchronous PDF output is capped at 5,000 rows.
- Exact RTFTS barcode/case-reference searches use the permanent-barcode index directly.
- Court/section scan requests are capped at 200 files and empty court batches are not retained.
- Dashboard movement metrics use consolidated conditional aggregates instead of repeated range scans.
- Repeatable local-only benchmark generation, cleanup, timing, and execution-plan commands are available.
- Broad lookup uses centralized MySQL FULLTEXT search while exact RTFTS references remain uniquely indexed.
- The full test suite passes with 88 tests and 399 assertions.

These completed items must be regression-tested during the audit; they should not be redesigned unless evidence shows a defect.
