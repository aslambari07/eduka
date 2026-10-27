# EDUKA

EDUKA is a Laravel 13 learning portal built from the approved Superdesign project `da5e3497-9e37-4e76-ad20-e0f61058dc06`. The app uses Blade/Vite, MySQL or MariaDB, session authentication, and exactly three primary roles: `admin`, `pengajar`, and `peserta`.

## Implemented scope

- Public homepage, searchable/paginated course catalog, course detail, public lesson preview, login, registration, password reset, profile/security, error and empty states.
- Participant enrollment with prerequisites, protected lesson player, progress tracking, next/previous lesson navigation, server-scored quizzes with attempt limits, deadline-aware text/file assignments, private notes, certificates, owner-authorized server-side PDF download, HTML fallback, verification, and print output.
- Instructor dashboard, owned course drafts, material/quiz/question/assignment authoring, quiz builder, submission inbox, grading, and feedback.
- Admin dashboard, user role/status management, category CRUD, course publication moderation, enrollment progress reports, and pagination.
- Private assignment files are stored on Laravel’s non-public `local` disk and downloaded only by the submitting participant.

Design artifacts and screen mapping live in [`docs/DESIGN-SYSTEM.md`](docs/DESIGN-SYSTEM.md), [`docs/PROJECT-STATUS.md`](docs/PROJECT-STATUS.md), and [`.superdesign/implementation-map.md`](.superdesign/implementation-map.md).

## Requirements

- PHP 8.3+
- Composer 2.x
- MySQL 8/MariaDB compatible with Laravel 13
- Node.js 20+ and npm
- PHP extensions used by Laravel plus `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `ctype`, `json`, and `tokenizer`
- PDF generation also requires the PHP `dom` extension; Dompdf uses bundled DejaVu fonts and does not require Chrome, wkhtmltopdf, or Node.js at request time.
- Assignment uploads are limited to 5120 KB (5 MiB) by the application. The PHP process serving the app must use at least `upload_max_filesize=8M` and `post_max_size=10M`.

## Local setup

```bash
cp .env.example .env
composer install
php artisan key:generate
```

Create a MySQL database and user, then set `DB_*` in `.env`. For a local development database:

```bash
php artisan migrate --seed
php artisan db:seed --class=DemoCourseSeeder
npm install
npm run build
php artisan serve
```

The application will be available at `http://127.0.0.1:8000`. If the active CLI `php.ini` still has lower upload limits, start the local server with `php -d upload_max_filesize=8M -d post_max_size=10M artisan serve`. The account seeder creates clearly marked development accounts only when those email addresses are absent; it does not reset existing credentials:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@eduka.test` | `AdminEDUKA2026!` |
| Pengajar | `pengajar@eduka.test` | `PengajarEDUKA2026!` |
| Peserta | `peserta@eduka.test` | `PesertaEDUKA2026!` |

These passwords are for local development only and must be changed or removed before production.

For local development with automatic asset rebuilding, use `npm run dev` in a second terminal.

## Tests and verification

```bash
php artisan test
php artisan route:list
npm run build
```

The test suite uses the separate MySQL database configured in `phpunit.xml` (`eduka_rebuild_testing` by default). Do not point it at a production database. This workspace does not have the SQLite PDO extension, so tests intentionally use MySQL rather than silently falling back to SQLite.

## Important environment settings

Production should use:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
DB_CONNECTION=mysql
SESSION_DRIVER=file
CACHE_STORE=file
MAIL_MAILER=smtp
FILESYSTEM_DISK=local
```

Never commit `.env`, production credentials, `APP_KEY`, SMTP passwords, or cloud storage keys. Configure SMTP values through the hosting environment. See [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) for cPanel/VPS procedures.

## Development data

`php artisan db:seed` is intended for development only and creates only the three named development accounts when they do not already exist; it does not reset existing credentials or seed course content. For the complete four-course learning content, run the separate guarded seeder explicitly on local/testing:

```bash
php artisan db:seed --class=DemoCourseSeeder
```

`DemoCourseSeeder` creates Dasar Pemrograman Web, Laravel untuk Pemula, UI/UX Design Dasar, and Database dan SQL with five lessons and a quiz per course. Kelas Dasar Pemrograman Web memiliki empat tugas bertahap (HTML, CSS, JavaScript, dan proyek akhir); tiga course lain memiliki satu tugas proyek. Seeder ini idempotent, tidak menghapus data tidak terkait, tidak mereset password `pengajar@eduka.test`, dan menolak environment selain local/testing. Jangan jalankan seeder demo pada production. `migrate:fresh` sengaja tidak dipakai pada setup karena destruktif dan hanya boleh dijalankan pada database test/local disposable.

The completion/certificate rules and the actual verification evidence are documented in [`docs/FUNCTIONAL-AUDIT.md`](docs/FUNCTIONAL-AUDIT.md) and [`docs/PRODUCTION-READINESS.md`](docs/PRODUCTION-READINESS.md).

## License

This project is application code for EDUKA. Laravel remains licensed under the [MIT License](https://opensource.org/licenses/MIT).

| Role | Email | Password |
|---|---|---|
| Admin | `admin@eduka.test` | `AdminEDUKA2026!` |
| Pengajar | `pengajar@eduka.test` | `PengajarEDUKA2026!` |
| Peserta | `peserta@eduka.test` | `PesertaEDUKA2026!` |
