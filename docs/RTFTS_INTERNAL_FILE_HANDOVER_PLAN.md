# RTFTS Internal File Handover Plan

## Objective

Replace unrestricted internal custody-taking with an accountable staff-to-staff
handover workflow:

1. The current holder selects an intended recipient.
2. The current holder scans one or more files and sends them as a batch.
3. Each file remains in a pending handover state until the selected recipient
   scans and receives it.
4. Confirmed custody changes only when the recipient receives the file.
5. RTFTS records the elapsed time between sending and receiving.

The established filing intake, old-case intake, affidavit rejection, and court
dispatch/return workflows must continue to work.

## Confirmed Rules

- Every staff workspace will begin with two large actions: Send Files and
  Receive Files.
- Only the current holder can send a file through the normal handover flow.
- A user cannot send a file to themselves.
- Only active admin/staff users are eligible recipients. Lawyers are excluded.
- A file can have only one active handover at a time.
- Only the selected recipient can accept a pending handover.
- A pending file cannot be sent again or received by another user.
- Internal movement requires a permanent RTFTS barcode or final case number.
- Rejected and returned-to-lawyer files cannot be sent internally.
- Files in court continue through the dedicated court workflow.
- The sender remains the last confirmed holder while the UI displays the file
  as "In Transit" to the intended recipient.
- Receiving completes custody transfer and creates the permanent movement
  history entry.
- Receipt delay is calculated from the item sent time to its received time.
- A sender may cancel an unreceived handover. Administrative cancellation must
  remain auditable and require a reason.

## Data Design

### `file_transfer_batches`

One batch represents a single sender, one intended recipient, and one or more
files scanned in the same operation.

Important fields:

- Unique batch number
- Sender and recipient user IDs
- Sender and recipient department IDs
- Snapshot names, employee IDs, and section names for historical reporting
- Compact indexed status code representing pending, partially received,
  completed, or cancelled
- Sent, completed, and cancelled timestamps
- Cancellation actor/reason and optional notes

### `file_transfer_items`

One item represents one case in a transfer batch. Item-level status permits the
recipient to receive only part of a multi-file batch and finish the remainder
later.

Important fields:

- Batch and case IDs
- `active_case_id`, unique while pending, to prevent concurrent duplicate
  handovers at database level
- Compact indexed status code representing pending, received, or cancelled
- Sent, received, and cancelled timestamps
- Receiving user and resulting movement IDs

When an item is received or cancelled, `active_case_id` becomes null. This
retains full history while allowing a later handover of the same case.

## Custody State

While pending:

- `cases.current_holder_user_id` remains the sender because that is the last
  confirmed custody event.
- The active transfer item identifies the expected recipient.
- Lookup and timeline will display "In Transit: Sender to Recipient".

When received in one transaction:

- Lock the case and active transfer item.
- Confirm the logged-in user is the intended recipient.
- Update the case holder, section, and holder timestamp.
- Mark the transfer item received and clear `active_case_id`.
- Update the batch status.
- Create a `file_movements` row of type `user_handover`.

## Delivery Phases

### Phase 1: Foundation

- [x] Save the approved workflow plan.
- [x] Add transfer batch and item migrations.
- [x] Add transfer models, constants, casts, and relationships.
- [x] Include transfer tables in the pre-live destructive reset seeder.
- [x] Apply and inspect the migration locally with InnoDB constraints.
- [ ] Run the automated foundation test when development dependencies are
  available.

### Phase 2: Transfer Domain Service

- [x] Add recipient eligibility rules.
- [x] Add collision-free batch number generation.
- [x] Add transactional send, receive, and cancel operations.
- [x] Use row locking for every custody decision.
- [x] Recalculate batch status after item receipt/cancellation.
- [ ] Run automated concurrency and authorization tests when development
  dependencies are available.

### Scale Preparation

- [x] Use bigint keys and InnoDB for row-level locking and foreign keys.
- [x] Use compact numeric transfer statuses to reduce large index size.
- [x] Enforce one active handover per case with a unique indexed key.
- [x] Add query-shaped indexes for incoming/outgoing batches and transfer-item
  status/time reporting.
- [x] Add a unique index for exact final case-number lookup.
- [x] Add focused case custody and movement date/type/section indexes for the
  projected multi-million-row workload.
- [ ] Benchmark dashboard and register-report queries with generated volume
  before production launch.
- [ ] Replace unbounded report retrieval with pagination and capped background
  export before importing historical cases.

### Phase 3: Staff Workspace and Send Flow

- [x] Add the two-action staff workspace.
- [x] Add searchable, paginated recipient selection ordered by department.
- [x] Add the familiar multi-file scan interface for sending.
- [x] Add outgoing pending summary and receipt-friendly confirmation.
- [x] Preserve existing filing and court navigation.

### Phase 4: Receive Integration

- [x] Allow only the intended recipient to receive a pending internal transfer.
- [x] Keep first-time old-case intake and court return exceptions working.
- [x] Block direct custody-taking from another holder without a handover.
- [x] Support partial receipt of a transfer batch.
- [x] Show incoming pending count and sender details.
- [x] Block court sending, rejection, court return, and Registrar Override while
  an internal handover is pending.
- [x] Require the court-sending operator to hold the file personally.

### Phase 5: Visibility and Reporting

- [x] Show pending and completed handovers in lookup and timeline.
- [x] Add sender, recipient, sent time, received time, and elapsed duration to
  reports.
- [x] Add sender and Super Admin cancellation tools with mandatory audit reasons.
- [x] Add indexed, paginated pending and completed transfer lists.
- [x] Show cancelled handovers and their reasons in the case timeline.

### Phase 6: Regression and Deployment

- [ ] Verify filing conversion and temporary barcode restrictions.
- [ ] Verify affidavit rejection restrictions.
- [ ] Verify court dispatch and court return rules.
- [ ] Verify old-case first intake.
- [ ] Verify dynamic departments and inactive users.
- [x] Run the full automated test suite.
- [ ] Test migration and rollback on a disposable database backup.
- [ ] Prepare cPanel migration and cache-clear commands.

## UI Constraint

Users have already trained on the current scanner interfaces. New screens must
reuse their visual language, scan queue, button placement, case-number display,
and validation behavior. The first workspace and recipient selection are new;
existing screens should receive only the minimum changes needed for the new
rules.
