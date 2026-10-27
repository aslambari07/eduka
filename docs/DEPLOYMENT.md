# EDUKA deployment guide

This application is prepared for a Laravel 13-compatible VPS or cPanel account and now includes a Vercel serverless entrypoint. A protected Vercel Preview has been built successfully, but Production has not been promoted because the TiDB application schema, rotated credential, and durable object storage are not yet configured.

## Local XAMPP database migration

The intended local target is MySQL/MariaDB database `eduka_v1`. The existing
`eduka_rebuild` database on system MySQL 3306 is preserved and is not the target
for this migration. Before changing the local `.env`, verify the XAMPP process,
binary/datadir, host, port, authentication, and target schema. Do not assume a
different MariaDB listener is XAMPP.

The verified source backup is
`/home/aslam/Desktop/eduka_rebuild_backup_20261027_2148.sql`. The reviewed
non-destructive SQL exports and phpMyAdmin procedure are in
[`database/exports/README.md`](../database/exports/README.md). The exports were
not imported automatically because XAMPP is currently stopped and its default
port 3306 conflicts with the active system MySQL. Start or reconfigure XAMPP
through its normal privileged control, then verify the target before import.
Never use `migrate:fresh`, `db:wipe`, `DROP DATABASE`, `TRUNCATE TABLE`, or a
destructive import for this data.

## Vercel + TiDB Cloud architecture

Vercel does not provide Laravel as a first-party runtime. The repository uses the community `vercel-php@0.7.4` runtime, which targets PHP 8.3, with `api/index.php` forwarding requests to Laravel’s `public/index.php`. The static Vite bundle is routed from `public/build`; all dynamic routes go through the PHP function. Vercel’s function filesystem is read-only except for temporary `/tmp`, so it is not a suitable permanent location for sessions, cache, logs, or assignment files.

The Vercel project `eduka-learning-portal` exists in the authenticated account. The CLI project inspection currently reports `gitRepository: null` and `productionBranch: null`, so the repository association and Production Branch still require confirmation in Project Settings; no Git/Production setting was changed by this audit. The latest Preview deployment of local commit `533dc1b` is `dpl_ECpZR1DZnS6zaNaPev64h6qZkGq1`, status `READY`, at `https://eduka-learning-portal-ntstmc3ty-aslambari07s-projects.vercel.app`. Preview deployment protection remains enabled, so anonymous HTTP requests may be redirected to Vercel SSO; authenticated CLI checks were used for runtime verification.

The first Preview build failed because Vercel inferred the Vite default output directory `dist`; setting `outputDirectory` to `public` fixed the build. A later runtime failure caused by stale development `bootstrap/cache` and missing production service discovery was fixed by retaining a verified production-only service manifest. The remote project now reports Node `22.x` and build command `npm run build`, matching `vercel.json` and `package.json`. On the latest Preview, authenticated CLI checks reached `/up` successfully and read `/build/manifest.json`; authenticated requests to `/`, `/kelas`, and `/masuk` returned `500` because no TiDB connection variables are configured.

The current Preview log audit shows that those failing requests reach Laravel's HTTP kernel and terminate in the application request path. This is consistent with the source: `/` and `/kelas` execute database queries for published courses/categories, while database-backed sessions are enabled for `/masuk`. The Vercel environment list currently contains only `APP_DEBUG`, `APP_URL`, and `APP_KEY`; it contains no `DB_*`, TLS CA, or private-storage variables. The serverless config now pins `DB_CONNECTION=mysql` so it cannot silently fall back to a local SQLite file, but a verified TiDB host, non-system schema, rotated user/password, and TLS CA are still required before a new Preview deployment can pass.

Required Vercel environment variables (values belong in Project Settings, never in `vercel.json` or Git):

- `APP_KEY`, `APP_URL`, `APP_ENV=production`, `APP_DEBUG=false`.
- `DB_CONNECTION=mysql`, verified TiDB application `DB_HOST`, `DB_PORT=4000`, `DB_DATABASE`, rotated `DB_USERNAME`, and rotated `DB_PASSWORD`.
- `DB_SSL_CA` or `MYSQL_ATTR_SSL_CA` pointing to the verified TiDB CA file, with `MYSQL_ATTR_SSL_VERIFY_SERVER_CERT=true`. Do not disable TLS verification.
- `SESSION_DRIVER=database`, `CACHE_STORE=database`, and `QUEUE_CONNECTION=sync` unless a real external queue worker is provisioned.
- `EDUKA_PRIVATE_DISK=s3`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, and optional `AWS_ENDPOINT`/`AWS_URL` for a private S3-compatible bucket.
- Production SMTP variables if password reset mail is required.

The currently missing minimum Preview inputs are `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, and either `DB_SSL_CA` or `MYSQL_ATTR_SSL_CA`; `MYSQL_ATTR_SSL_VERIFY_SERVER_CERT` must remain `true`. For the current serverless defaults, `SESSION_DRIVER=database`, `CACHE_STORE=database`, and `QUEUE_CONNECTION=sync` also require the corresponding schema tables after an approved migration. Secret values must be entered in Vercel Project Settings or another secret manager, never sent in chat or committed to Git.

The local MCP configuration currently points at the TiDB system schema `sys` and contains a database credential. That schema is not accepted as the EDUKA application database, and the credential must be rotated before any cloud connection or deployment. The MCP server is ignored by Git and was not used to migrate data in this audit.

Deployment sequence after the target and credentials are supplied:

1. Create or select the exact Vercel project and confirm the intended repository/Production Branch in Project Settings. **Project exists; CLI currently reports the Git repository and Production Branch as unset/unconfirmed.**
2. Create or verify an empty, non-system TiDB application schema and restricted database user; take a backup if the schema is not empty.
3. Add the production environment variables in Vercel Preview first. Do not add demo credentials or run `DemoCourseSeeder`.
4. Deploy a Preview, inspect build/function logs, then run `php artisan migrate --force` against the verified schema only after confirming the migration list, backup, and explicit owner approval. **Build and runtime health are partially verified; DB-dependent requests are not.**
5. Smoke-test HTTPS, assets, session login, all roles, enrollment, quiz, assignment submission/grading, certificate PDF, and private S3 download.
6. Promote to Production only after Preview passes and the owner explicitly approves the promotion.

The Preview URL and build/runtime logs are real evidence of a successful Preview build, not Production readiness. The production domain currently returns Vercel `404 DEPLOYMENT_NOT_FOUND`; Vercel history contains an earlier failed Production deployment (`dpl_AV3M4g8EBTgByfcYj2DRumWARFYa`) and no successful Production promotion is being claimed. A successful Production deployment/alias, commit linkage, TiDB application connection, migration result, private-storage test, and full feature smoke test are still required before marking deployment successful.

## Hosting requirements

- PHP 8.3 or newer with `pdo_mysql`, `dom`, `mbstring`, `openssl`, `fileinfo`, `ctype`, `json`, `tokenizer`, and XML-related extensions required by the hosting’s Laravel build. Dompdf uses pure PHP; `gd` is optional because the certificate template has no raster images.
- Assignment uploads are capped by the application at **5120 KB (5 MiB)**. PHP must allow at least `upload_max_filesize=8M` and `post_max_size=10M`; `post_max_size` covers the complete multipart request, not just the file.
- MySQL 8 or a compatible MariaDB version.
- Composer 2.x and Node 20/npm for build-time setup, unless `vendor/` and `public/build/` are prepared locally and uploaded.
- A web server whose document root can point to the project’s `public/` directory.
- For Vercel, PHP 8.3 is provided by the pinned community runtime; Node 22.x is selected by `package.json` for the runtime build. The S3 Flysystem adapter is included for persistent private assignment storage.

## VPS deployment

1. Create a restricted deploy user, an application directory, a MySQL database, and a database user with access only to that database.
2. Upload the repository without `.env`, `storage/logs`, or local test databases.
3. Run `composer install --no-dev --optimize-autoloader` and `npm ci && npm run build` during the release build. The PDF endpoint does not require Node.js, Chrome, wkhtmltopdf, or another OS binary at request time.
4. Copy `.env.example` to `.env` and set `APP_ENV=production`, `APP_DEBUG=false`, the HTTPS `APP_URL`, a generated `APP_KEY`, MySQL credentials, SMTP settings, and production session/cache values.
5. Run `php artisan migrate --force`. Do not run `migrate:fresh` or `db:seed` against production.
6. Run `php artisan storage:link` only if public assets need a symbolic link. Assignment files stay on the private local disk and must not be exposed through `public/storage`.
7. Ensure `storage/` and `bootstrap/cache/` are writable by the web process, but keep `.env` outside the public document root. On Vercel, do not rely on either directory for persistent writes; use database-backed session/cache and S3 for assignments.
8. Run `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache` after environment values are correct.
9. Configure PHP-FPM/Apache/Nginx to serve `public/index.php`, force HTTPS, and use secure cookies. Set a process supervisor only if queue work is introduced later; the current core workflows do not require a worker.

## cPanel deployment

Prefer a host that supports PHP 8.3 and lets the domain document root point to `EDUKA/public`. If the host cannot change the document root, keep the application one directory above `public_html`, copy only the contents of `public/` into `public_html`, and update the front controller paths carefully. Do not upload `.env`, `vendor`, or the whole application source into a publicly browsable directory.

When SSH/Composer is unavailable, run `composer install --no-dev --optimize-autoloader` and `npm ci && npm run build` locally with the same PHP/runtime requirements, then upload the prepared `vendor/` and `public/build/` directories. Configure the cPanel PHP version/extensions, create the MySQL database/user, add `.env` through the file manager outside the public root when possible, and run migrations through SSH or a one-time protected deployment command. Remove any temporary deployment helper immediately afterward.

### PDF certificate smoke check

After deployment, log in as the certificate owner and request `GET /sertifikat/{code}/unduh`. Confirm `Content-Type: application/pdf`, a filename ending in `.pdf`, a readable one-page A4 landscape document, and that the response is denied for another participant. If the PDF is blank or corrupt, check that `dom` and `mbstring` are enabled, `storage/` and `bootstrap/cache/` are writable, Composer dependencies are complete, and the PHP memory limit is sufficient. The renderer uses only the local DejaVu fonts bundled with Dompdf and does not fetch remote CSS/images.

## Environment and email

Set `APP_DEBUG=false` in production. Generate the key once with `php artisan key:generate` before the first deployment, then preserve it across releases. Configure `MAIL_MAILER=smtp`, host, port, username, password, encryption, `MAIL_FROM_ADDRESS`, and `MAIL_FROM_NAME` through environment variables. Password reset notifications use Laravel’s configured mail transport; the local default is the log driver only.

Use file sessions/cache only when the host provides reliable writable storage. For multiple web nodes, use a shared database or Redis session/cache backend. The repository’s three demo credentials are development-only; do not use them in production, and do not place production credentials in Blade, seeders, JavaScript, or Git.

## Assignment upload limits

The application keeps the upload limit in `config/eduka.php` and exposes it as `EDUKA_ASSIGNMENT_MAX_FILE_KB` (default `5120`). The participant form and instructor authoring form use this same limit. Submission validation checks the stored MIME/content type and the filename extension for the assignment’s allowed `pdf`, `zip`, or `docx` formats, then stores accepted files on the configured private disk (`EDUKA_PRIVATE_DISK`) with a generated storage name. Local development defaults to `local`; Vercel production must use a private S3-compatible bucket. Download authorization is unchanged.

The PHP limits must be larger than the application limit:

```ini
upload_max_filesize = 8M
post_max_size = 10M
```

`post_max_size` must be greater than or equal to `upload_max_filesize` and include room for multipart boundaries and text fields. Laravel cannot repair a request after PHP rejects it; `PostTooLargeException` is handled only to return a friendly assignment-form error, not to process the rejected file.

For the local CLI used by `php artisan serve`, this workspace reports the active file `/etc/php/8.3/cli/php.ini`. It currently belongs to `root`, so it was not changed from this user session. Edit it deliberately with `sudoedit`, update the two values above, stop the old `artisan serve` process, start a new one, and verify:

```bash
php --ini
php -r 'echo ini_get("upload_max_filesize"), PHP_EOL, ini_get("post_max_size"), PHP_EOL;'
php artisan serve
```

If root access is unavailable, start the local server with the limits explicitly applied to that process:

```bash
php -d upload_max_filesize=8M -d post_max_size=10M artisan serve
```

For cPanel, use MultiPHP INI Editor or a host-supported `.user.ini` in the web application directory. Example:

```ini
upload_max_filesize=8M
post_max_size=10M
```

The host may impose lower hard limits or require PHP-FPM/Apache reload time; confirm the effective web value with a temporary protected `phpinfo()` check or the host panel, then remove any diagnostic file. Do not assume local `php.ini`, `.htaccess`, or CLI settings apply to cPanel web requests.

### 413 troubleshooting and manual upload check

1. Confirm the active web/CLI values and ensure `post_max_size` is not below the multipart request size.
2. Keep the selected file at or below 5 MiB and use the assignment’s displayed format.
3. Log in as an enrolled participant, open the assignment, submit a small valid PDF/ZIP, and confirm the success message plus a new `submitted` record.
4. Submit a file above 5 MiB and confirm a friendly validation error with no new submission.
5. Submit an invalid extension/MIME and confirm rejection.
6. Confirm the instructor can grade the valid submission and that the participant can download only their own private file.

Restart `php artisan serve` after changing CLI ini values. On cPanel, use the panel’s PHP-FPM/restart or configuration propagation mechanism; do not assume an application restart changes host PHP settings.

## Backups and rollback

- Schedule encrypted, tested MySQL backups and retain more than one restore point.
- Back up `.env` through the host’s secret-management or encrypted backup process, never in the repository.
- Deploy additive migrations first, then application code. Take a backup before schema changes.
- To roll back application code, switch the release symlink or restore the prior release and run only the documented down migration when it is known to be safe. Do not reset the database.
- Verify uploaded assignment files are included in the private storage backup if they are business-critical.

## Production smoke test

After each release, verify:

1. `/` and `/kelas` render over HTTPS.
2. Registration, login, logout, and password reset notification work with the configured mail transport.
3. A participant can enroll, open a published lesson, complete it, submit a quiz, and see progress.
4. An instructor can view only owned courses/submissions and grade a submission.
5. An admin can view reports and manage categories without changing their own account status.
6. Assignment file downloads are denied to another participant.
7. `storage/logs/laravel.log` contains no new critical errors.

Record the host, PHP version, database version, migration output, and smoke-test date in the release notes. No live deployment is claimed by this repository.
