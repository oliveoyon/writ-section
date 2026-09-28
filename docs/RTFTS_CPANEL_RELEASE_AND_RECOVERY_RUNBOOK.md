# RTFTS cPanel Release and Recovery Runbook

## Purpose

Use this checklist for the first live release and every later production update.
Run commands from the Laravel project root. Never run benchmark generation or
the concurrency audit on production.

## 1. One-Time Hosting Setup

- PHP 8.2 or newer with `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`,
  `intl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, and `xml` enabled.
- MySQL tables must use InnoDB. Confirm the server supports FULLTEXT indexes.
- Point the domain document root to the application's `public` directory.
- Keep `.env` outside Git. Confirm it is not web-accessible and back it up
  separately before each release.
- Make `storage` and `bootstrap/cache` writable by the PHP/cPanel account.
- Keep HTTPS enabled for the whole domain, not only the login page.
- Configure a cron entry to run `php artisan schedule:run` every minute if a
  scheduled task is added. The application currently has no required scheduler
  job.
- If asynchronous jobs are introduced, configure a supervised queue worker.
  Do not rely on a terminal process left open in cPanel.

## 2. Production Environment

Use production-specific secrets and values. Do not copy the local `.env`.

```dotenv
APP_NAME="RTFTS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://writ.a2jbd.org
APP_LOCALE=en
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=database
SESSION_LIFETIME=120
AUTO_LOGOUT_MINUTES=10
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_PATH=/
SESSION_DOMAIN=null

CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

SCBA_MEMBER_LIST_URL=https://api.scba.org.bd/api/esl/memberlist
SCBA_SSL_VERIFY=true
```

Generate `APP_KEY` once with `php artisan key:generate`. Preserve that key in
every future deployment and backup. Changing it invalidates sessions and makes
previously encrypted application values unreadable.

## 3. Pre-Release Gate

1. Record the release commit with `git rev-parse HEAD`.
2. Put the application in maintenance mode with `php artisan down --retry=60`.
3. Export a timestamped MySQL backup and verify that its file size is not zero.
4. Back up `.env` and `storage/app`, including private case attachments.
5. Confirm available disk space is comfortably larger than the database plus
   the new backup.
6. On local or staging, run:

   ```bash
   php artisan test
   php artisan tracking:concurrency-audit
   ```

The concurrency command creates isolated records and removes them afterward.
It is intentionally disabled when `APP_ENV=production`.

## 4. Deploy

```bash
git pull origin master
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan optimize:clear
php artisan migrate --force
php artisan storage:link
php artisan tracking:health-check
php artisan tracking:audit-users
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`storage:link` may report that the link already exists; that is acceptable when
the existing link points to this release's `storage/app/public` directory.

### Frontend Assets

`public/build` is excluded from Git. Use exactly one of these release methods:

```bash
npm ci
npm run build
```

Or build locally from the same commit and upload the complete `public/build`
directory as a deployment artifact. Never leave `public/hot` on production.

## 5. Smoke Test Before Opening

- `php artisan about` shows `Environment: production`, `Debug: OFF`, and
  `Timezone: Asia/Dhaka`.
- `php artisan migrate:status` shows every migration as `Ran`.
- `php artisan tracking:health-check` passes.
- `https://writ.a2jbd.org/up` returns HTTP 200.
- Manual employee ID/password login works.
- A test staff card performs tap login without exposing the card value.
- Lawyer registration/member lookup returns a controlled result.
- A test file can be sent, appears for the recipient, and is received once.
- An unauthorized user cannot receive that pending file by changing a URL or
  scanning its barcode.
- Lookup, timeline, filing print, report HTML, and report PDF open correctly.
- Case attachments are not directly enumerable from a public directory.
- Browser cookies show `Secure` and `HttpOnly`; no Laravel exception details
  appear in an error response.

Then reopen the application with `php artisan up`.

Monitor `storage/logs/laravel.log`, HTTP 500 responses, database load, and disk
usage during the first working session.

## 6. Rollback

Do not start with `migrate:rollback` on a populated production database.

1. Run `php artisan down --retry=60`.
2. Preserve the failed release log and record its commit hash.
3. Restore the previous tested application commit/artifact.
4. If the failed release changed schema or data, restore the matching database
   backup. A code rollback without a compatible database may be unsafe.
5. Restore `storage/app` only when the release changed or removed attachments.
6. Run `php artisan optimize:clear`, then cache configuration/routes/views.
7. Repeat the smoke test and run `php artisan up`.

## 7. Backup and Restore Drill

At least once before launch, restore the latest backup into a separate staging
database and storage directory. Verify login, one case timeline, one attachment,
one handover, and one PDF. A backup is accepted only after a successful restore
test.

Recommended retention:

- Daily encrypted database and attachment backup: 30 days.
- Weekly backup: 12 weeks.
- Monthly backup: 12 months or the Supreme Court retention requirement.
- Keep at least one encrypted copy outside the cPanel account.
- Restrict backup access and never place SQL dumps under `public`.

## 8. Launch Evidence

Keep these records with the release:

- Git commit hash and deployment time.
- Operator name.
- Database and attachment backup names/checksums.
- Migration and health-check output.
- Test-suite result from the same commit.
- Smoke-test sign-off.
- Rollback decision and incident notes, if applicable.
