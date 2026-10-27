# EDUKA Production Readiness

Tanggal pemeriksaan: 27 Oktober 2026  
Status: **siap untuk staging/local verification; belum diklaim siap hosting produksi**.

## Local MySQL/XAMPP migration checkpoint

The requested target `eduka_v1` is not yet active because XAMPP's MySQL/MariaDB
service is stopped and its configured default port 3306 is occupied by the
separate system MySQL instance. The active Laravel `.env` therefore remains on
the preserved `eduka_rebuild` source. This is intentional: no target database
was created on the wrong instance and no existing data was overwritten.

Before switching `.env` to `eduka_v1`, an operator must start XAMPP, confirm its
actual port and root/database credentials, create only `eduka_v1`, verify that
it is empty or back it up if it is not, and import the reviewed files in
[`database/exports/README.md`](../database/exports/README.md). The source backup
is `/home/aslam/Desktop/eduka_rebuild_backup_20261027_2148.sql`; it was verified
non-empty before export generation. This workspace cannot start the privileged
XAMPP service from the current user session, so target connection and import
remain **BELUM DIVERIFIKASI**.

## Bukti yang sudah tersedia

- Laravel 13 pada PHP 8.3.14.
- PDF certificate: `barryvdh/laravel-dompdf` 3.1.2 dengan Dompdf 3.1.6; pure PHP, tanpa Chrome/wkhtmltopdf/runtime Node.
- Migration lengkap dan `php artisan migrate:status --no-ansi` menunjukkan seluruh 20 migration `Ran` pada database development.
- Test memakai database MySQL terpisah sesuai `phpunit.xml`, bukan database development/production.
- `php artisan test --compact --no-ansi`, `composer test`, and `vendor/bin/phpunit`: **103 passed, 720 assertions** each.
- `php artisan view:cache`: **passed**.
- `npm run build`: **passed**; asset Vite production terbentuk.
- `php artisan route:list --no-ansi`: **69 routes**; pemeriksaan nama route tidak menemukan duplikasi.
- PHP lint: **84 file PHP, 0 failure**.
- Demo course memakai seeder terpisah dan guard environment; tidak ada deployment atau seeding produksi yang dilakukan.
- Assignment upload memakai disk privat terkonfigurasi (`local` untuk development, S3-compatible untuk Vercel) dan endpoint download memeriksa enrollment/ownership peserta.
- Sertifikat memakai unique constraint `(course_id, user_id)` dan service transaksional yang idempotent.
- PDF certificate owner-only menghasilkan `application/pdf`, filename aman `.pdf`, signature `%PDF-`, satu halaman A4 landscape, dan cache private/no-store. Dompdf remote/PHP/JavaScript execution dimatikan.
- Assignment upload policy is centralized at 5120 KB (5 MiB), with `pdf`, `zip`, and `docx` authoring choices, MIME/content validation, extension validation, private storage, and friendly oversized-request handling.
- Development participant acceptance completed through real HTTP routes on `eduka_rebuild`: four enrollments completed, four server-scored quizzes passed, all required assignments graded by the authorized instructor, four certificates persisted exactly once, and four owner-only PDF downloads verified.
- Vercel Preview: project `eduka-learning-portal` exists, Node 22.x and `npm run build` are configured, and the deployment of local commit `533dc1b`, `dpl_ECpZR1DZnS6zaNaPev64h6qZkGq1`, is `READY` at `https://eduka-learning-portal-ntstmc3ty-aslambari07s-projects.vercel.app`. `vercel project inspect --json` reports `gitRepository: null` and `productionBranch: null`, so repository/branch linkage remains unconfirmed in the Dashboard. Authenticated CLI checks reached `/up` and read `/build/manifest.json`; authenticated `/`, `/kelas`, and `/masuk` requests returned `500`. Preview currently contains only `APP_DEBUG`, `APP_URL`, and `APP_KEY`, with no `DB_*`, TLS CA, or private-storage variables, so DB-dependent routes and feature tests cannot run. No TiDB migration or successful Production promotion was performed; the Production domain still returns `404 DEPLOYMENT_NOT_FOUND` and Vercel history contains one earlier failed Production deployment.
- `storage/logs/laravel.log` masih menyimpan error historis `is_required` pada 05:07–05:10 sebelum migration diterapkan; log tidak dihapus. Audit ini juga sempat mencatat satu kegagalan CLI `Command "test" is not defined` sebelum package-discovery cache diregenerasi. Setelah cache diperbaiki, test dan smoke HTTP berikutnya lulus; seluruh riwayat error tetap dipertahankan.
- Browser smoke lokal dengan Chrome/CDP: login dan landing Admin/Pengajar/Peserta berhasil; screenshot dashboard role dan halaman publik utama diperiksa. Dashboard Peserta pada viewport 390px tidak menunjukkan horizontal overflow.

## Functional readiness

| Area | Status | Bukti |
|---|---|---|
| Login tiga role dan redirect dashboard | Lulus automated test | `AuthenticationTest` |
| Admin user/category/course/report | Lulus automated test | `AdminManagementTest`, workflow tests |
| Pengajar course/content/question/grading | Lulus automated test | `InstructorAuthoringTest`, `InstructorAdminWorkflowTest` |
| Peserta enrollment/lesson/quiz/assignment/note | Lulus automated test | `ParticipantWorkflowTest` |
| Required/optional completion | Lulus automated test | `CompletionSchemaTest`, `CourseCompletionServiceTest` |
| Auto certificate lesson/quiz/grading | Lulus automated test | participant/instructor workflow tests |
| Failure/revision/unauthorized/retry | Lulus automated test | completion, certificate, authoring tests |
| Browser login/dashboard smoke | Lulus terbatas | Chrome/CDP: tiga role berhasil login dan landing; screenshot dashboard diperiksa |
| Pixel comparison Superdesign dan seluruh browser CRUD | Belum diverifikasi | Belum ada pixel comparison atau browser automation penuh untuk semua form/upload/modal |

## Hosting checklist yang masih harus dilakukan

Sebelum hosting nyata, operator harus mengisi dan membuktikan hal berikut pada staging yang menyerupai production. Item yang membutuhkan akses cPanel/host tetap `BELUM DIVERIFIKASI` dari workspace ini:

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` dibuat dan disimpan sebagai secret.
- `APP_URL` HTTPS dan secure cookie/session settings.
- PHP 8.3+ dengan `pdo_mysql`, `dom`, `mbstring`, `openssl`, `fileinfo`, `ctype`, `json`, `tokenizer`, serta extension XML yang dibutuhkan dependency. `gd` bersifat opsional untuk Dompdf karena certificate tidak memakai raster image.
- MySQL 8 atau MariaDB yang kompatibel, user database terbatas pada database aplikasi.
- Web root mengarah ke `public/`, bukan root repository; `.env` tidak dapat diakses web.
- `storage/` dan `bootstrap/cache/` writable hanya oleh process aplikasi.
- `composer install --no-dev --optimize-autoloader`, `npm ci`, dan `npm run build` berhasil pada release build.
- Dependency PDF tersedia melalui `composer install --no-dev --optimize-autoloader`; tidak membutuhkan Node.js, Chrome, wkhtmltopdf, atau binary OS saat request download.
- `php artisan migrate --force`, `config:cache`, `route:cache`, dan `view:cache` berhasil setelah environment production terpasang.
- SMTP/password reset diuji dengan provider nyata; local `MAIL_MAILER=log` tidak cukup untuk production.
- Assignment files tetap private dan masuk backup terenkripsi; jangan membuat `storage` upload menjadi public secara tidak sengaja.
- Backup database dan file diuji restore, termasuk rollback aplikasi tanpa `git reset` atau reset database.
- HTTPS, rate limiting, log monitoring, disk quota, dan alert error disiapkan oleh host.

Status lokal yang terukur: PHP **8.3.14**, `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `ctype`, `tokenizer`, `xml`, `zip`, dan ekstensi runtime lain yang dibutuhkan tersedia. `.env` tidak terlacak Git dan di-ignore; `APP_KEY` lokal terisi (nilainya tidak dicatat); request lokal ke `/.env` menghasilkan **404**. `.env.example` masih berisi nilai local/dev sehingga wajib dioverride pada production. `.env` lokal saat pemeriksaan memakai `APP_DEBUG=true`, `APP_ENV=local`, `APP_URL=http://localhost:8000`, dan `MAIL_MAILER=log`; ini bukan konfigurasi production.

Status PHP upload lokal: file aktif CLI adalah `/etc/php/8.3/cli/php.ini`, dengan nilai terukur `upload_max_filesize=2M` dan `post_max_size=8M`. File tersebut root-owned dan tidak dapat diubah dari sesi ini; karena itu nilai aktif default `php artisan serve` **belum diklaim diperbaiki**. Jalankan dengan `php -d upload_max_filesize=8M -d post_max_size=10M artisan serve` atau ubah file tersebut secara eksplisit dengan hak root, lalu verifikasi ulang.

Status `BELUM DIVERIFIKASI`: PHP/ext dan versi database di cPanel target, effective `upload_max_filesize`/`post_max_size` web, document root aktual, permission user web, HTTPS/certificate, SMTP provider, cron/queue bila diperlukan, backup/restore, storage quota, dan rollback live. Assignment files disimpan pada `storage/app/private` melalui disk `local` untuk development; production serverless harus memakai bucket privat S3-compatible. Probe HTTP lokal ke `/storage/app/private` menghasilkan **403** dan tidak ada route public direct, tetapi pembuktian permission dan backup pada host tetap wajib.

## Blocker dan risiko tersisa

1. **Blocker hosting:** belum ada target server, credentials, DNS, web server/PHP-FPM, SMTP, atau bukti restore; deployment produksi belum dilakukan.
2. **Blocker Vercel/TiDB:** Vercel project dan GitHub repository sudah terhubung, tetapi Preview application routes `/`, `/kelas`, dan `/masuk` masih `500` karena environment DB belum diisi. `DB_CONNECTION=mysql` sudah dipatok pada konfigurasi serverless, tetapi `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, serta `DB_SSL_CA` atau `MYSQL_ATTR_SSL_CA` belum tersedia. Schema aplikasi non-`sys`, user/password yang sudah dirotasi, CA TLS, dan persetujuan migrasi cloud belum tersedia. TiDB MCP menunjuk schema sistem `sys`, bukan schema aplikasi yang terverifikasi, dan credential lokal harus dirotasi sebelum digunakan.
3. **Blocker private storage:** object storage S3-compatible dan credentials belum disediakan. Tanpa `EDUKA_PRIVATE_DISK=s3` serta bucket privat, assignment files tidak boleh dianggap durable di Vercel.
4. **Verifikasi visual penuh:** smoke Chrome lokal sudah dilakukan, tetapi belum ada pixel parity Superdesign atau browser CRUD/upload/modal end-to-end.
5. **Sertifikat:** PDF dibuat on-demand dan tidak disimpan sebagai file publik. PDF sudah diverifikasi lokal; font/aset dan memory limit tetap perlu diuji pada target Vercel/TiDB dengan nama/course terpanjang.
6. **Operasional:** proses completion masih synchronous. Ini cukup untuk alur sekarang; queue/worker baru diperlukan jika email, PDF, atau proses berat ditambahkan.
7. **Perubahan authoring pasca-sertifikat:** sertifikat yang sudah terbit bersifat histori dan tidak dicabut otomatis. Kebijakan perubahan requirement setelah penerbitan perlu diputuskan untuk operasi nyata.
8. **Demo data:** `DemoCourseSeeder` hanya boleh dipanggil eksplisit pada local/testing. `php artisan db:seed` juga tidak boleh dijalankan pada production; prosedur deployment resmi menggunakan migration saja.
9. **Upload runtime:** default CLI PHP yang terukur masih berada di bawah batas aplikasi 5 MiB. Sampai ini diubah/di-override pada process server, upload file nyata pada `php artisan serve` belum dapat dinyatakan selesai diverifikasi.

## Kesimpulan

Tidak ada test failure pada alur lokal yang tercakup suite 103 test. Acceptance keempat kelas demo hingga certificate request-to-database, PDF signature, satu halaman A4, unduhan owner-only, serta validasi upload tugas berhasil pada database testing, dan akun peserta development telah diselesaikan pada database lokal. Namun kriteria “siap hosting produksi” **belum terpenuhi** karena schema/user TiDB, rotasi credential, object storage privat, migration cloud, HTTPS, SMTP, backup/restore, dan smoke test fitur pada URL Preview belum dibuktikan.
