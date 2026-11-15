# Project cleanup requests

When the user asks to "clean" or "clean data" in the context of resetting this application's data, use `php artisan app:clean-data` to preview, then `php artisan app:clean-data --force` to execute the requested cleanup. This means deleting all cases, parties, attachments, tracking/transfer/dispatch history, courts, registration sequences, lawyer profiles AND lawyer login accounts, sessions, reset tokens, queued jobs, and database cache.

Preserve all non-lawyer user accounts (including staff/admin), their photos, department assignments, departments, roles, permissions, access assignments, migrations, source code, environment configuration, and backups. Do not use migrate:fresh or truncate users. This instruction does not apply to requests to clean code or formatting.

Use the environment named in the request. Do not infer a production reset from a local cleanup request. For cPanel use Terminal/SSH in the deployed Laravel root, preview the configured database, stop writers, enter maintenance mode, execute cleanup, verify success, then restore the previous maintenance state. See docs/DATA_CLEANUP.md. If remote access is unavailable, provide the terminal commands and clearly state that remote cleanup has not been performed.
