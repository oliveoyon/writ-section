# RTFTS Performance Baseline

Date: 2026-09-28  
Database: Local MySQL / InnoDB  
Sample: 25,000 generated cases, 250,000 movements, 25,000 petitioners, and 25,000 respondents  
PHP memory limit: 128 MB

## Benchmark Commands

These commands are separate from `DatabaseSeeder` and are blocked outside `local`, `testing`, and `staging` environments.

```bash
# Example development sample
php artisan tracking:benchmark-generate --cases=25000 --movements=10 --chunk=500 --with-parties

# Read-only timing and EXPLAIN probe
php artisan tracking:benchmark-probe --iterations=10 --days=90

# Machine-readable evidence
php artisan tracking:benchmark-probe --iterations=10 --days=90 --json

# Remove only benchmark records
php artisan tracking:benchmark-clean --chunk=5000
```

Generated cases use `entry_source=benchmark`. Cleanup was exercised after both a successful run and an interrupted run. It preserved users, departments, courts, roles, and non-benchmark cases.

## Measured Results

The values below are ten-run averages. They are a development-machine baseline, not a guarantee for cPanel hardware.

| Query | Average | Plan evidence |
|---|---:|---|
| Exact permanent barcode | 0.24-0.41 ms | Unique `cases_permanent_barcode_unique`, one row |
| Case timeline page | 0.29-0.54 ms | `file_movements_case_id_received_at_index`, backward index scan |
| Section register page | 1.5-2.1 ms | Received-date range, 100-row page |
| Current-holder queue | 0.8-1.0 ms | `cases_holder_status_time_id_index`, no filesort |
| Incoming handover queue | 0.4-0.7 ms | Indexed pending status and batch primary key |
| Court history by case | 0.2-0.4 ms | `court_batch_items_case_batch_index` |
| Broad party text search | about 121 ms | FULLTEXT indexes, capped source unions, primary-key case join |
| Dashboard movement aggregate, 90 days | about 27 ms | `file_movements_received_at_index` range |

## Evidence-Based Changes

1. Added `cases(current_holder_user_id, status, current_holder_at, id)` and removed the shorter overlapping holder/status index.
2. Added MySQL FULLTEXT indexes for case text, petitioners, respondents, and lawyers.
3. Centralized exact and broad lookup in `App\Services\CourtCaseSearch` so lookup and barcode-print suggestions use the same behavior.
4. Exact RTFTS identifiers continue to use the permanent-barcode unique index and do not enter full-text search.
5. Broad text sources are capped before joining to cases, preventing unbounded suggestion results.
6. A proposed union rewrite for From/To movement reports was rejected: it increased the measured page query from about 1.6 ms to about 5.9 ms.
7. The generator was reduced to 500-case default chunks and 500-movement insert batches after the initial 1,000-case/5,000-movement batches exceeded the 128 MB PHP limit. The corrected 25,000/250,000 run completed successfully.

## Interpretation

- Barcode, timeline, custody, handover, court history, report-page, and 90-day dashboard queries are comfortably below the current targets on this sample.
- Broad text search improved from about 334 ms with a full case scan to about 121 ms on an intentionally difficult sample where every generated party shares common words. This remains the first query to retest at larger scale.
- A ten-year dashboard aggregate was about 153 ms over 250,000 movements, while the real maximum dashboard window of 90 days was about 27 ms.
- FULLTEXT indexes support scalable word/prefix search, but they do not provide arbitrary middle-of-word substring matching. Exact case references and barcodes remain fully supported.

## Production Notes

- The FULLTEXT migration took about 10 seconds with 25,000 cases and 50,000 party rows on the development machine. Run it before launch while operational tables are empty or during maintenance if applied later.
- Repeat the probe against cPanel staging because CPU, storage, MySQL/MariaDB version, buffer pool, and shared-host contention affect results.
- Do not generate benchmark data on the live application. The command enforces this through the Laravel environment, but production credentials should never be copied into a local `.env` merely to bypass that rule.
- At one million or more representative cases, reconsider a dedicated denormalized search document or search service if broad search exceeds 500 ms.
