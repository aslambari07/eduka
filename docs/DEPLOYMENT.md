# EDUKA deployment guide

This application is prepared for a Laravel 13-compatible VPS or cPanel account. It has not been deployed to an external host from this workspace; follow the smoke checks below after deployment.

## Hosting requirements

- PHP 8.3 or newer with `pdo_mysql`, `dom`, `mbstring`, `openssl`, `fileinfo`, `ctype`, `json`, `tokenizer`, and XML-related extensions required by the hosting’s Laravel build. Dompdf uses pure PHP; `gd` is optional because the certificate template has no raster images.
- Assignment uploads are capped by the application at **5120 KB (5 MiB)**. PHP must allow at least `upload_max_filesize=8M` and `post_max_size=10M`; `post_max_size` covers the complete multipart request, not just the file.
- MySQL 8 or a compatible MariaDB version.
- Composer 2.x and Node 20/npm for build-time setup, unless `vendor/` and `public/build/` are prepared locally and uploaded.
- A web server whose document root can point to the project’s `public/` directory.

## VPS deployment

1. Create a restricted deploy user, an application directory, a MySQL database, and a database user with access only to that database.
2. Upload the repository without `.env`, `storage/logs`, or local test databases.
3. Run `composer install --no-dev --optimize-autoloader` and `npm ci && npm run build` during the release build. The PDF endpoint does not require Node.js, Chrome, wkhtmltopdf, or another OS binary at request time.
4. Copy `.env.example` to `.env` and set `APP_ENV=production`, `APP_DEBUG=false`, the HTTPS `APP_URL`, a generated `APP_KEY`, MySQL credentials, SMTP settings, and production session/cache values.
5. Run `php artisan migrate --force`. Do not run `migrate:fresh` or `db:seed` against production.
6. Run `php artisan storage:link` only if public assets need a symbolic link. Assignment files stay on the private local disk and must not be exposed through `public/storage`.
7. Ensure `storage/` and `bootstrap/cache/` are writable by the web process, but keep `.env` outside the public document root.
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

The application keeps the upload limit in `config/eduka.php` and exposes it as `EDUKA_ASSIGNMENT_MAX_FILE_KB` (default `5120`). The participant form and instructor authoring form use this same limit. Submission validation checks the stored MIME/content type and the filename extension for the assignment’s allowed `pdf`, `zip`, or `docx` formats, then stores accepted files on Laravel’s private `local` disk with a generated storage name. Download authorization is unchanged.

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
