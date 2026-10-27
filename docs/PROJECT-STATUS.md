# EDUKA project status

Last verified: 2026-10-27, local workspace `/home/aslam/Desktop/EDUKA`.

## Foundation

- Laravel 13 application scaffolded and running on PHP 8.3.14.
- Blade/Vite frontend with Tailwind CSS 4, Archivo, and Manrope.
- MySQL/MariaDB-compatible migrations and separate local test database configuration.
- Existing Superdesign project `da5e3497-9e37-4e76-ad20-e0f61058dc06` inspected through the authenticated CLI; all 13 drafts and 5 reusable components were fetched and mapped in [SUPERDESIGN-UI-AUDIT.md](SUPERDESIGN-UI-AUDIT.md).

## Implemented

- Public homepage, catalog search/filter/sort/pagination, course detail, prerequisite display, public lesson preview, authentication, password reset flow, profile/security, responsive navigation, error pages, and empty states.
- Exactly three application roles with server-side middleware and ownership checks: `admin`, `pengajar`, `peserta`.
- Normalized schema for categories, courses, prerequisites, enrollments, lessons/progress, quizzes/questions/options/attempts/answers, assignments/submissions, notes, and certificates.
- Participant enrollment, protected learning, progress, next/previous navigation, server-side quiz scoring and attempt limit, private file/text assignments, deadlines, private notes, certificate eligibility/verification/PDF download/HTML fallback/printing, and certificate history.
- Instructor course drafts, lesson/quiz/question/assignment authoring, quiz workspace, private submission inbox, grading, feedback, and database-derived dashboard metrics.
- Centralized completion evaluation with required/optional content flags, automatic transactional/idempotent certificates, owner-only certificate pages, and a separate minimal public verification page.
- Full safe instructor CRUD for courses, lessons, quizzes, quiz questions, and assignments. Content with learner history is archived rather than destructively deleted.
- Explicit guarded `DemoCourseSeeder` with four complete courses, five lessons per course, quiz questions/options, four stable assignments on the web demo course, and an evaluable assignment on each other demo course.
- Admin dashboard, user role/status controls, category CRUD, class publication moderation, and filtered/paginated enrollment progress reporting.
- Full dark-theme foundation plus alternating section audit across public, authentication, participant, instructor, and admin surfaces: dark hero/workspace tokens, light `#FFFFFF`/`#F8FAFC` content sections, correct dark/light heading and copy tokens, green actions, readable feedback states, visible focus states, complete authenticated mobile navigation, responsive empty states, same-origin flow prefetch, and real-action checks.
- Final Admin/Pengajar/Peserta alignment is documented in [ROLE-UI-AUDIT.md](ROLE-UI-AUDIT.md). Admin and Pengajar now share a route-safe responsive workspace shell; Peserta profile, certificates, notes, and dashboard use the established learning surface rhythm.
- Instructor assignment authoring now sends the required file size/type rules; participant upload UI derives its accepted formats and size guidance from the same server-side configuration. Draft/private instructor courses no longer expose broken public preview links. The demo web course now includes four stable publishable assignments with participant status actions.
- Assignment uploads now use the centralized 5120 KB (5 MiB) application cap, explicit MIME/content plus extension validation for `pdf`, `zip`, and `docx`, safe private storage, and a friendly `PostTooLargeException` response. The active local CLI PHP still reports `upload_max_filesize=2M` and `post_max_size=8M`; server configuration must be raised to the documented 8M/10M values before claiming real oversized-file remediation.
- Guarded account seeder that creates only the three named demo accounts when absent, preserves existing IDs/credentials, and stores new development passwords through Laravel hashing; complete course data is handled by the separate `DemoCourseSeeder`.
- Server-side PDF certificate generation using `barryvdh/laravel-dompdf` 3.1.2/Dompdf 3.1.6, with A4 landscape template, owner authorization, safe filename, no remote resource loading, and HTML/print fallback retained.
- Development participant acceptance is complete on the active `eduka_rebuild` database: all four demo courses are enrolled and completed at 100%, with server-scored quiz attempts, instructor-graded assignments, and one persisted certificate/PDF download per course. The exact certificate codes and timestamps are recorded in [FUNCTIONAL-AUDIT.md](FUNCTIONAL-AUDIT.md).
- Vercel/TiDB preparation: serverless `api/index.php`, pinned `vercel-php@0.7.4` PHP 8.3 runtime, Vercel routing/static asset rules, Node 22 build engine, PDO MySQL TLS hooks, and S3-compatible private assignment disk abstraction.

## Verification

Run:

```bash
php artisan test
php artisan view:cache
php artisan route:list
npm run build
```

Latest local results: `php artisan test --compact` passed **103 tests and 720 assertions**; `php artisan route:list --no-ansi` resolved **69 routes** with no duplicate named routes; `php artisan migrate:status --no-ansi` shows all 20 migrations ran; `php artisan view:cache` succeeded; and `npm run build` succeeded with Vite 7.3.7. HTTP smoke reached public pages, Admin, Pengajar, and Peserta entry points with status 200. PHP tests use MySQL because this environment does not expose the SQLite PDO extension. The test database is separate from the development database.

## Known limitations and follow-up

- Chrome/CDP browser smoke is available and covered login/landing for all three roles plus a 390px Peserta overflow check. Homepage, catalog, and role dashboard screenshots were inspected locally; this is not a pixel-level comparison with Superdesign. The complete HTML/draft audit and local route/build checks are recorded in [SUPERDESIGN-UI-AUDIT.md](SUPERDESIGN-UI-AUDIT.md).
- SMTP, HTTPS, server permissions, backups, and actual hosting deployment require environment-specific configuration and have not been claimed as live.
- Vercel project `eduka-learning-portal` is created and linked to `aslambari07/eduka`; latest protected Preview `dpl_J1CS21m5RHPiT2dV2A5ytAf8aNgZ` is `READY`, with health and built asset checks returning `200`. The remote build command is `npm run build` on Node `22.x`. Authenticated `/` and `/masuk` still return `500` because TiDB application schema, rotated credential, CA certificate, and DB environment variables are absent; private object storage is also required before feature verification or Production promotion.
- The current lesson content is plain text with escaped rendering; rich-text editing and video hosting are intentionally outside the implemented scope. PDF certificate generation is implemented with on-demand Dompdf rendering; separate persistent PDF storage and QR/signature assets are not.
- Certificate output has an owner-authorized, on-demand server-generated PDF plus the existing HTML/print presentation fallback. The PDF is not persisted as a public file.
- Queue workers are not required by the current synchronous workflows; add one only if future notifications or heavy processing need it.
