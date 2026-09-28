# RTFTS Database Integrity And Scale Audit

Date: 2026-09-28

## Scope

This pass reviewed the current-state case record, append-only movement history, filing conversion, normal receipt, old-case intake, internal handover, court dispatch/return, register reporting, exact case lookup, foreign keys, and indexes. Existing users were preserved. Case and movement data remain disposable during development.

## Required Invariants

1. One case has no more than one current holder.
2. One case has no more than one active internal handover.
3. Custody changes update `cases` and append `file_movements` in the same transaction.
4. A custody decision is repeated after locking the case row; browser validation is advisory only.
5. Movement history is append-only.
6. Permanent barcode, final case number, and registration year/serial are unique.
7. A user department must reference a real department.
8. Court and internal batches are bounded and cannot leave empty operational records.

## Findings And Actions

| ID | Severity | Finding | Action |
|---|---|---|---|
| DB-001 | High | `users.department` stored an ID in a text column without a foreign key. | Existing values were validated, the MySQL column was converted to unsigned bigint, and a restricted department foreign key was added. |
| DB-002 | High | Normal section receipt checked custody before its transaction but did not repeat the decision under a row lock. | Receipt now locks and revalidates status, court state, holder, and pending handover before changing custody. |
| DB-003 | High | Temporary filing conversion and return-to-lawyer could act on stale case state during simultaneous requests. | Both paths now lock the case and repeat permanent/temporary/lawyer checks inside retryable transactions. |
| DB-004 | High | The normal court-return helper changed custody without first locking and revalidating the case. | Court state, permission, pending handover, and latest dispatch are now checked under a case row lock. |
| DB-005 | High | Simultaneous first scans of an unknown old case could collide and return a server error. | Unique-key races now resolve to the existing case and preserve the winning desk's custody. |
| DB-006 | High | Register HTML loaded every matching movement; PDF was unbounded. | HTML now paginates at 100 rows. PDF rejects filters above 5,000 rows with a clear validation message. |
| DB-007 | Medium | Report section options loaded all movement rows into PHP before deduplication. | Section values are now selected with database-level `DISTINCT`. |
| DB-008 | Medium | Full case identifiers entered the same broad `%term%` search used for names and descriptions. | Valid barcode and `WRPET serial/year` input now uses the unique permanent-barcode index directly. Flexible partial suggestions remain available. |
| DB-009 | Medium | Court scan requests were effectively unbounded and invalid scans left empty batches. | Court and section requests are capped at 200 files; court batches with no successful item are removed in the transaction. |
| DB-010 | Medium | Four-digit random court batch suffixes were too small for sustained daily volume. | Batch suffixes now use 40 bits of secure randomness while retaining readable type/date prefixes. |
| DB-011 | Medium | Movement immutability was convention only. | Eloquent now rejects updates and deletes to `FileMovement`; development truncation remains available to the explicit seeder. |
| DB-012 | Medium | Court history and status/date queries lacked confirmed compound indexes, while some new indexes overlapped automatic FK indexes. | Targeted compound indexes were added and three redundant MySQL indexes were removed. |
| DB-013 | Medium | Dashboard totals repeated the same date-range scans for each movement type and used a non-index-friendly case date filter. | Case and movement totals now use conditional aggregates, today's cases use a timestamp range, and monthly grouping is covered on both MySQL and SQLite. |
| DB-014 | High | Broad party/lawyer lookup performed a full case-table scan and averaged about 334 ms at only 25,000 cases. | Search is centralized and uses capped MySQL FULLTEXT branches joined by case ID; the difficult benchmark dropped to about 121 ms. |
| DB-015 | Medium | Current-holder queue required a filesort with the existing holder/status index. | Replaced it with a holder/status/time/ID index; measured average improved from about 2.86 ms to below 1 ms. |

## Schema Changes

- `2026_09_28_000001_harden_tracking_integrity_and_indexes.php`
  - validates and constrains `users.department`
  - adds `cases(status, created_at)`
  - adds court batch type/date and creator indexes
  - adds court batch item case/batch lookup index
- `2026_09_28_000002_remove_redundant_tracking_indexes.php`
  - removes standalone indexes already covered by compound leading columns
- `2026_09_28_000003_optimize_report_and_holder_queue_indexes.php`
  - adds the measured holder queue ordering index
- `2026_09_28_000004_add_tracking_fulltext_search_indexes.php`
  - adds case, party, respondent, and lawyer FULLTEXT indexes for production MySQL

Both migrations were applied successfully to the local MySQL database. Laravel schema inspection confirmed InnoDB tables, the department foreign key, and the intended indexes.

## Verification

- Full Laravel suite: 88 tests passed, 399 assertions.
- Added 5,001-row PDF limit regression coverage.
- Added 101-row HTML pagination regression coverage.
- Added court batch limit and empty-batch coverage.
- Added exact case-reference lookup coverage.
- Added append-only movement history coverage.
- Added dashboard aggregate regression coverage and removed its prior MySQL-only test blind spot.
- Added benchmark generate/probe/cleanup lifecycle coverage and broad party-search regression coverage.
- PHP syntax checks and migration execution passed.

## Remaining Scale Work

1. Repeat the documented benchmark at a larger scale on dedicated staging and capture cPanel staging results; do not put benchmark volume in the normal seeder.
2. Reassess a denormalized search document or dedicated search service if FULLTEXT lookup exceeds 500 ms at one million representative cases.
3. Confirm the production MySQL/MariaDB FULLTEXT tokenizer and collation with representative Bangla and English names.
4. Replace expensive dashboard live aggregates with measured short-lived caching or summary tables. Never cache live custody location.
5. Move PDF/export work above the current synchronous limit to queued exports with authorization and expiry.
6. Extend true concurrent MySQL coverage to filing conversion, court dispatch, and court return. Double send, double handover receive, and receive-versus-cancel pass against local MySQL/InnoDB through `php artisan tracking:concurrency-audit`.
7. Execute the cPanel environment and restore drill in `docs/RTFTS_CPANEL_RELEASE_AND_RECOVERY_RUNBOOK.md`, including attachment backup, HTTPS cookies, permissions, and operational evidence.

## Deployment Note

After pulling this version, run:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan test
```

Take a database backup first. The department migration deliberately stops without altering the column if any user points to a missing or nonnumeric department.
