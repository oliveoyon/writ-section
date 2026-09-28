# RTFTS Security Assurance Plan

Last reviewed: 2026-09-24

## Purpose

This plan defines the security evidence required before RTFTS is approved for production and handed over to the Supreme Court of Bangladesh. It complements the production and performance audit plan.

The target is a documented, risk-based review aligned with OWASP ASVS 5.0 Level 2. This is a target until every applicable control has been tested; this document does not claim certification.

## Release Gates

Production approval requires all of the following:

- No unresolved Critical or High vulnerability unless the authorized owner records a time-limited written acceptance and compensating control.
- No known authentication, authorization, custody, or cross-user data-access bypass.
- `composer audit --locked --no-dev` reports no known production dependency advisories.
- Automated tests cover critical authentication and file-custody transitions and pass against the release commit.
- Production configuration is reviewed without exposing secret values.
- HTTPS, secure cookies, trusted host handling, `APP_DEBUG=false`, and non-public application directories are verified on the real server.
- Database and uploaded-file backup and restore are tested, not merely configured.
- Migration and rollback procedures are rehearsed from a production-like backup.
- Audit logs retain who performed each custody-changing action and when.
- A separate pre-launch penetration test or independent security review is completed. A developer self-review is necessary but is not an independent assessment.

## Security Test Areas

### Authentication And Sessions

- Staff password login uses Employee ID only and rejects inactive or lawyer accounts.
- Lawyer login rejects inactive accounts, is throttled, and regenerates the session ID.
- Card login accepts only active staff/admin users, is throttled, and does not create a persistent remember-me session.
- Logout invalidates the session and regenerates the CSRF token.
- Idle timeout remains active after `php artisan config:cache`.
- Password reset tokens, remember tokens, and concurrent sessions are reviewed.
- Default/shared passwords and card-only authentication are prohibited at final production acceptance unless explicitly accepted as temporary risks.

### Authorization

- Every route has an explicit public, lawyer, staff, department, or Super Admin access rule.
- Object access is checked for case IDs, attachments, handover batches/items, court batches, reports, users, departments, and courts.
- URL parameter changes cannot reveal or modify another lawyer's cases or another user's handovers.
- Super Admin overrides are logged and cannot silently rewrite history.
- Renaming a display label does not change authorization behavior.

### File Custody And Integrity

- Only the current holder can send a file.
- Only the intended recipient can receive a pending handover.
- A case cannot have two active handovers.
- Court dispatch/return, rejection, filing conversion, legacy intake, and Registrar Override cannot bypass pending custody.
- All custody changes use transactions, row locks where required, and append an attributable movement.
- Duplicate scans, browser retries, double clicks, and concurrent users are tested.

### Input, Output, And Files

- All state-changing browser requests use CSRF-protected non-GET methods.
- Identifiers, filters, notes, names, and dynamic sorting are validated and safely encoded.
- Upload type, MIME, size, filename, storage location, and authorization are tested.
- Case documents remain private and are downloaded only through an authorized controller action.
- PDF and barcode output cannot include unescaped attacker-controlled markup.

### Infrastructure And Operations

- The web root points to Laravel's `public` directory only.
- `.env`, logs, storage-private files, backups, source control, and database exports are not web accessible.
- `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL` is appropriate, and secrets are unique.
- `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, and an appropriate SameSite mode are verified under HTTPS.
- Host-header restrictions and security response headers are verified.
- cPanel cron, queue workers if used, log rotation, disk monitoring, and restore ownership are documented.
- Production database accounts use least privilege and remote database access is restricted.

## Evidence Register

For each control, retain:

- Control identifier and requirement.
- Applicable route, controller, service, table, or server setting.
- Test method and test data.
- Pass/fail result with date and release commit.
- Fix reference or written risk acceptance.
- Responsible owner and retest date.

Do not store passwords, application keys, API secrets, session data, or full personal records in the evidence register.

## Initial Baseline Findings

| ID | Severity | Finding | Status |
|---|---|---|---|
| SEC-001 | High | Locked production dependencies contained 21 advisories across Guzzle, PSR-7, and CommonMark. | Fixed locally by compatible updates; `composer audit --locked --no-dev` is clean as of 2026-09-24. |
| SEC-002 | High | Lawyer login did not check active status or regenerate the session ID and had no route throttle. | Fixed locally with active-account regression coverage. |
| SEC-003 | High | Case attachments were written to the public disk and linked directly. | New uploads moved to private storage with owner-authorized download and cross-lawyer access tests; removal of old fake public files is still required before launch. |
| SEC-004 | High | SCBA TLS certificate verification defaulted off and Filing disabled it unconditionally. | Fixed locally to default on and use configuration in both paths; production connectivity must be tested with verification enabled. |
| SEC-005 | Medium | Card login created a persistent remember-me session. | Fixed locally; card cloning/single-factor risk remains an authority decision. |
| SEC-006 | Medium | Idle timeout read `.env` directly and could be disabled by production config caching. | Fixed locally by reading cached application configuration. |
| SEC-007 | Medium | An unused logout-all action changed session state through `GET`. | Removed locally. |
| INT-001 | High | Initial temporary barcode generation used second-level time and could collide under concurrency. | Fixed locally by using the existing uniqueness-checked generator; concurrency test required. |
| SEC-010 | High | A crafted staff-update URL could target a lawyer account even though the edit screen blocked it. | Fixed locally with a controller-side target guard and regression coverage. |
| SEC-011 | High | Old Case Receive could claim an existing file from another holder by submitting its case reference. | Fixed locally; existing files must now follow the normal receive/handover workflow. |
| SEC-012 | High | User and master-data routes relied on broad Admin account type rather than the Super Admin role. | Fixed locally with server-side `role:Super Admin` middleware and matching navigation visibility. |
| SEC-008 | Pending | Shared/default staff passwords and card-only login materially weaken accountability. | Must be eliminated before final acceptance or documented as a temporary accepted risk with a retirement date. |
| SEC-009 | Pending | Host restrictions, response headers, cPanel document root, HTTPS cookies, backup restore, and server permissions require environment verification. | Open. |

## Current Verification

As of 2026-09-24:

- Production dependency audit: no known advisories.
- Laravel test suite: 88 tests passed with 399 assertions.
- Route and URL-tampering review: documented in `docs/RTFTS_ROUTE_AUTHORIZATION_MATRIX.md`; focused authorization regression tests passed.
- Route discovery, configuration cache, and Blade cache: passed.
- PHP syntax checks for changed application files: passed.
- Whitespace/error-marker check with `git diff --check`: passed.
- Full style check still reports pre-existing formatting differences in several legacy controllers and routes; these should be handled separately to avoid a noisy pre-launch rewrite.

## MySQL Concurrency Verification

Run `php artisan tracking:concurrency-audit` on local or staging before release.
It starts overlapping PHP processes and verifies that double send, double
receive, and receive-versus-cancel attempts preserve one coherent custody
state. The command is blocked in production and removes its isolated audit data
after completion.

Use `docs/RTFTS_CPANEL_RELEASE_AND_RECOVERY_RUNBOOK.md` for deployment, backup,
smoke-test, rollback, and restore evidence.

## Required Handover Pack

- Release commit and dependency lock file.
- Environment checklist with secret values redacted.
- Route and permission matrix.
- Findings register with closure evidence.
- Automated test and dependency-audit output.
- Migration, deployment, rollback, backup, and restore runbooks.
- Data retention and audit-log policy.
- Independent review report and signed residual-risk acceptance.

Security testing reduces risk and supplies due-diligence evidence. It cannot guarantee that an application will never be compromised or remove contractual/legal responsibility; those boundaries must also be covered by formal acceptance, operational ownership, and independent review.
