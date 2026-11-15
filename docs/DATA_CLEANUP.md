# Application data cleanup

Preview from the Laravel root (the directory containing `artisan`):

```bash
php artisan app:clean-data
```

Execute locally:

```bash
php artisan app:clean-data --force
```

The command deletes cases, parties, files, transfers, movements, dispatches, courts, registration sequences, lawyer profiles **and lawyer user accounts**, sessions, password reset tokens, queued jobs, and database cache. It removes uploaded case/lawyer files, lawyer account photos that are not shared by retained users, and generated labels/PDF temporary files. Non-lawyer users, their photos, departments, roles, permissions, migrations, source, logs, and SQL backups remain. Numeric IDs are not reset; yearly registration sequences are cleared.

Deletion is permanent. Database deletion uses a transaction with foreign keys enabled. File deletion happens after commit; inspect any reported failure before reopening the site. Known upload directories are scanned again on a retry. A failed deletion of an individual lawyer user photo may require manually deleting the exact path reported by the command, because its user record has already been removed.

## cPanel Terminal

Deploy `app/Console/Commands/CleanApplicationData.php` to the same path in the hosted Laravel project. Laravel discovers the command automatically; no migration or web endpoint is needed. Keep the hosting `.env` unchanged.

Open cPanel Terminal and change to your Laravel root:

```bash
cd /home/YOUR_CPANEL_USER/PATH_TO_LARAVEL
php artisan app:clean-data
```

Check the database name and counts in the preview. Pause any queue workers, scheduled jobs, and imports that write to this database, and let active requests finish. Then run:

```bash
php artisan down --retry=60
php artisan app:clean-data --force
```

Only after the command reports successful verification, reopen the site if it was previously online:

```bash
php artisan up
```

Resume paused workers/jobs. Existing sessions are removed, so users must sign in again. If cPanel's default `php` is older than PHP 8.2, use your host's PHP 8.2+ CLI executable in each command. Never put this destructive command in cron.

Saying "clean" to the assistant in this project refers to this scope. Saying "clean cPanel" targets the hosted copy; it requires a connected terminal or execution of these commands in cPanel. A local command does not clean the hosted database.
