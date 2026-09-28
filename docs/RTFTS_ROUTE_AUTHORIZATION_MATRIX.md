# RTFTS Route Authorization And URL-Tampering Matrix

Last reviewed: 2026-09-24

## Security Rule

Changing an ID, query parameter, barcode, case number, or path in the browser must not grant access beyond the signed-in user's business role. Numeric and sequential IDs are acceptable only when server-side authorization is enforced for every object operation.

Expected denial behavior:

- `401` or login redirect for guests.
- `403` for an authenticated user outside the permitted role or object ownership boundary.
- `404` where a nested object does not belong to its parent or the requested recipient is ineligible.
- A safe workflow redirect is currently used by department middleware; no protected data is returned before that redirect.

## Public And Authentication Routes

| Route(s) | Intended access | Tampering control | Status |
|---|---|---|---|
| `GET /`, `GET /lawyer/login`, `GET /lawyer/register` | Public | No object identifier; authenticated lawyers are redirected to their dashboard. | Verified |
| `GET /language/{locale}` | Public | Controller allow-list permits only `en` and `bn`. | Verified |
| `POST /lawyer/login` | Public guest | Validation, active lawyer check, password verification, throttling, and session regeneration. | Verified |
| `POST /lawyer/check-member` | Public | Input validation and throttling; returns SCBA lookup data required for registration. | Verified; external API trust remains operational dependency |
| `POST /lawyer/register` | Public | Validation, unique email/member number, throttling, and inactive-by-default account. | Verified |
| Staff login and card login | Public guest | Employee ID/password or card ID lookup, active account check, throttling, and session regeneration. | Verified; card is single-factor |
| Password reset and verification routes | Public/authenticated as appropriate | Laravel token, signed URL, CSRF, and throttle middleware. | Verified framework controls |

## Self-Service Routes

| Route(s) | Intended access | Tampering control | Status |
|---|---|---|---|
| `GET/PATCH/DELETE /profile` | Current authenticated account only | No user ID is accepted; controller always uses `$request->user()`. | Verified |
| `PUT /password` | Current authenticated account only | Current-password validation and CSRF protection. | Verified |
| `POST /logout` | Current session only | Auth and CSRF; session invalidated and token regenerated. | Verified |

## Lawyer Case Routes

| Route | Intended access | Tampering control | Status |
|---|---|---|---|
| `GET /lawyer/my-cases` | Current lawyer | Query is constrained by current lawyer ID. | Verified |
| `GET /lawyer/cases/{case}/summary` | Owning lawyer | `ensureCaseOwner()` rejects another lawyer's case ID. | Tested |
| `GET /lawyer/{case}/edit` | Owning lawyer and editable state | Ownership check precedes state check. | Tested |
| `PUT /lawyer/{case}` | Owning lawyer and editable state | Ownership check precedes validation/update. | Tested |
| `DELETE /lawyer/{case}` | Owning lawyer and draft state | Ownership and state checks. | Tested |
| `POST /lawyer/cases/{case}/resubmit` | Owning lawyer and returned state | Ownership and state checks. | Tested |
| `GET /lawyer/cases/{case}/top-sheet` | Owning lawyer | Ownership check occurs before PDF generation. | Tested |
| `GET /lawyer/cases/{case}/files/{file}` | Owning lawyer; file must belong to case | Case ownership plus nested `file.case_id` check and private storage. | Tested |
| Lawyer dashboard/settings/documents/messages | Current lawyer | No foreign user ID accepted; current authenticated relation is used. | Verified |

## System Administration Routes

| Route(s) | Intended access | Tampering control | Status |
|---|---|---|---|
| `/admin/home` | Admin account | `auth` and `checkUserType:admin`. | Verified |
| `/admin/users*` | Super Admin only | `role:Super Admin`; staff and ordinary Admin cannot type URLs to enter. | Tested |
| `PUT /admin/users/{user}` | Super Admin; staff/admin targets only | Lawyer targets are rejected before validation or mutation. | Tested |
| User activate/deactivate | Super Admin | Role middleware; self-deactivation and last-Super-Admin safeguards; records are retained. | Verified |
| `/admin/departments*` | Super Admin only | Role middleware; system/used departments have deletion safeguards. | Tested middleware; retention covered |
| `/admin/courts*` | Super Admin only | Role middleware; courts with history cannot be deleted or have code changed. | Tested middleware; retention covered |
| `PUT /admin/roles/{role}/display-name` | Super Admin only | Role middleware and display-name-only validation. | Verified |

## Internal Handover Routes

| Route | Intended access | Tampering control | Status |
|---|---|---|---|
| `GET /admin/tracking/send/{recipient}` | Staff/admin; eligible active recipient | Recipient is re-queried through `eligibleRecipientQuery`; self, lawyer, inactive, or unassigned IDs fail. | Verified |
| `GET .../send/{recipient}/validate` | Same as send screen; sender-owned cases only | Recipient eligibility and current-custody restriction. | Verified |
| `POST /admin/tracking/send/{recipient}` | Current holder to eligible recipient | Transaction, locked cases, custody check, and one-active-handover constraint. | Tested |
| `GET /admin/tracking/handovers/{batch}` | Sender, recipient, or Super Admin | Explicit participant check. | Tested |
| `POST .../handovers/{batch}/cancel` | Sender or Super Admin | Controller visibility check plus service-level actor check. | Tested |
| `POST .../handovers/{batch}/items/{item}/cancel` | Sender or Super Admin; item belongs to batch | Actor check and nested batch/item check. | Tested |
| Handover list query parameters | Current user's incoming/outgoing records; Super Admin may use all | Direction is allow-listed and ownership is applied in the database query. | Verified |

## Filing And Barcode Routes

| Route(s) | Intended access | Tampering control | Status |
|---|---|---|---|
| Filing intake, conversion, rejection, lawyer lookup, direct create, and case detail | Filing Section or Super Admin | `ensureDepartment:Filing Section`; Super Admin bypass is explicit. | Tested department boundary |
| Barcode print search/label/PDF/TSPL/direct print | Any authenticated staff/admin | Deliberate business rule: labels contain case reference/barcode and may be reprinted by staff. | Accepted scope; authentication required |
| Temporary barcode outside Filing | Not permitted | Movement and court workflows require permanent identifiers. | Tested |

## Section, Old Case, And Court Routes

| Route(s) | Intended access | Tampering control | Status |
|---|---|---|---|
| Section receive and identifier validation | Assigned staff/admin | Permanent identifier rules, current-holder rules, intended-recipient rules, rejection/court restrictions. | Tested |
| Old case receive | Assigned staff/admin; first valid registration only | Existing case is never reassigned by this endpoint; user must use normal Receive Files/handover. | Fixed and tested |
| Court dispatch and return | Office Assistant, Dealing Assistant, Assistant Registrar Office, or Super Admin | Department middleware plus custody/pending-handover checks. | Tested department and custody boundaries |
| `GET /court/batches/{batch}` and PDF | Creator or Admin/Super Admin | Explicit batch-view authorization in both HTML and PDF methods. | Tested |
| Court batch filters including `creator_id` | Own batches for staff; all for Admin/Super Admin | Creator filter is ignored unless view-all authority exists; base query scopes staff to creator ID. | Tested own/other visibility |

## Registrar, Timeline, And Reports

| Route(s) | Intended access | Tampering control | Status |
|---|---|---|---|
| Lookup, suggestion, timeline, override | Registrar/Assistant Registrar or Super Admin | Department middleware; override validates permanent case, destination, reason, and pending handover. | Tested department boundary |
| Register report HTML/PDF | Staff/admin | Non-admin report query is forcibly constrained to the user's section regardless of supplied `section` query value. | Tested URL query manipulation |
| Movement validation endpoint | Authenticated staff/admin | Returns operational case/barcode status only; mutation uses separate protected POST endpoint. | Verified |

## Findings Closed In This Review

| ID | Severity | Finding | Resolution |
|---|---|---|---|
| URL-001 | High | Crafted `PUT /admin/users/{lawyer}` bypassed the edit-screen restriction and could rewrite a lawyer account. | Controller now rejects lawyer targets before mutation; regression test added. |
| URL-002 | High | Old Case Receive could take an already-existing file from its current holder without a handover. | Existing cases are no longer reassigned; normal receipt/handover is required; regression test added. |
| URL-003 | High | Master-data/user routes trusted broad `user_type=admin` rather than the privileged role. | Users, departments, courts, card labels, and role-label routes now require `Super Admin`; menus match the server rule. |
| URL-004 | Medium | Pending timeline omitted the batch number needed to investigate a disputed handover. | Batch number is now displayed. |

## Residual Decisions

- Barcode label routes intentionally remain available to all authenticated staff/admin users. Change this only if the authority decides labels are Filing-only.
- Admin accounts may view all court batches and report sections; Staff accounts remain scoped. The Super Admin-only rule applies to system/user master data.
- Wrong-department requests currently redirect to the user's workspace instead of returning a plain `403`. This is secure but should remain consistent in future endpoints.
- Sequential database IDs are not treated as secrets. Authorization must remain mandatory even if UUIDs are introduced later.

## Regression Evidence

The focused tests are in:

- `tests/Feature/UrlTamperingAuthorizationTest.php`
- `tests/Feature/CaseFileSecurityTest.php`
- `tests/Feature/CourtBatchManagementTest.php`
- `tests/Feature/FileTransferServiceTest.php`
- `tests/Feature/PermanentBarcodeMovementTest.php`

Run the full suite and dependency audit against the exact release commit before deployment.
