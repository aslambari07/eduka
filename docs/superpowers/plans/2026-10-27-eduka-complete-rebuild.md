# EDUKA Complete Rebuild Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the complete Laravel 13 EDUKA learning portal in the empty target directory, using the existing Superdesign project for visual fidelity and real database-backed workflows.

**Architecture:** Blade/Vite Laravel application with a shared dark/emerald shell, Eloquent domain models, role middleware plus policies, form requests, focused services for quiz scoring and certificate eligibility, and private assignment storage. SQLite drives repeatable tests while migrations remain MySQL/MariaDB-compatible.

**Tech Stack:** Laravel 13, PHP 8.3+, Blade, Vite, Tailwind-compatible CSS, Eloquent, PHPUnit, MySQL test/development database, MySQL/MariaDB production database. SQLite is unavailable in this PHP build because `pdo_sqlite` is not installed.

**Spec:** `docs/superpowers/specs/2026-10-27-eduka-complete-rebuild-design.md`

## Global Constraints

- Exactly three roles: `admin`, `pengajar`, `peserta`.
- Blade is the server-rendered frontend; React/Next.js is not introduced.
- Scores, progress, grades, and certificate eligibility are calculated server-side.
- Do not reset, drop, or truncate production data.
- Use Archivo and Manrope with the approved emerald/glass EDUKA visual system.
- Development seed data is idempotent and clearly non-production.

## Review Focus

- A participant attempting another user’s lesson, note, submission, or certificate must receive denial; tests live in participant workflow tests.
- An instructor attempting another instructor’s course/submission must receive denial; tests live in instructor authorization tests.
- A quiz submission with tampered score/correct-answer payload must use server answers; tests live in quiz scoring tests.
- A certificate must not appear before all required lessons/quizzes/assignments pass; tests live in certificate eligibility tests.
- An invalid/oversized assignment upload must be rejected without exposing private storage; tests live in assignment tests.

### Task 1: Laravel foundation and visual documentation

**Files:** Laravel scaffold files, `.env.example`, `vite.config.js`, `resources/css/app.css`, `resources/views/layouts/app.blade.php`, existing `.superdesign` docs, `docs/PROJECT-STATUS.md`, `README.md`.

- [ ] Scaffold Laravel 13 in `/home/aslam/Desktop/EDUKA` and initialize a feature branch.
- [ ] Add a failing feature test for `GET /` returning the branded homepage shell.
- [ ] Run the test and verify failure because the route/view is absent.
- [ ] Implement the shared layout, fonts, tokens, noise/glow backgrounds, navigation, footer, and homepage shell.
- [ ] Run the feature test and the asset build; verify the homepage response and compiled assets.

### Task 2: Domain schema, models, and seed data

**Files:** migrations, `app/Models/*`, factories, `database/seeders/DatabaseSeeder.php`.

- [ ] Add failing model/feature tests for role users, categories/courses, prerequisites, enrollments, lessons, progress, quizzes, assignments, submissions, notes, and certificates.
- [ ] Run tests and verify missing tables/relationships fail.
- [ ] Implement normalized migrations with foreign keys, indexes, and uniqueness constraints plus Eloquent relationships and idempotent development seeders.
- [ ] Run migrations, seed, and the model test group successfully.

### Task 3: Authentication and role authorization

**Files:** auth controllers/requests/views, `app/Http/Middleware/RoleMiddleware.php`, policies, routes, tests.

- [ ] Add failing tests for registration validation, login/logout, role redirects, and denial of cross-role routes.
- [ ] Verify the tests fail before auth implementation.
- [ ] Implement session auth, throttled login, profile/password updates, role middleware, and policies.
- [ ] Run the auth and authorization suite successfully.

### Task 4: Public catalog and shared UI pages

**Files:** public controllers/requests, catalog/detail/auth Blade views, reusable Blade components, routes, tests.

- [ ] Add failing tests for public homepage, searchable/paginated catalog, course detail, public material visibility, and auth screens.
- [ ] Implement database-backed pages mapped to the homepage/catalog/detail drafts with responsive Blade components.
- [ ] Run public feature tests and the production asset build.

### Task 5: Participant learning workflows

**Files:** participant controllers/services/requests/policies/views, quiz/assignment/note/certificate migrations if needed, tests.

- [ ] Add failing tests for prerequisite-aware enrollment, lesson access/progress, server-scored quizzes/attempt limits, private assignment submissions, owned notes, and certificate eligibility/verification.
- [ ] Implement transactional services and protected routes mapped to dashboard, player, quiz, assignment, and certificate drafts.
- [ ] Run the participant workflow suite and verify private file authorization.

### Task 6: Instructor and administrator workflows

**Files:** instructor/admin controllers/requests/views/policies, routes, tests.

- [ ] Add failing tests for owned course CRUD, lesson/material ordering, quiz/assignment authoring, grading/feedback, admin user/category/content management, and reports.
- [ ] Implement role-specific dashboards and management pages with database-derived metrics and pagination.
- [ ] Run instructor/admin authorization and CRUD tests.

### Task 7: Responsive fidelity and QA hardening

**Files:** all relevant Blade/CSS/JS components, browser smoke scripts if available, `docs/PROJECT-STATUS.md`.

- [ ] Add failing route/smoke assertions for all mapped screen routes, empty states, validation states, and mobile navigation.
- [ ] Implement responsive behavior, focus states, alerts, empty/loading/error states, and no-overflow layout checks.
- [ ] Run route listing, full PHPUnit suite, asset build, and browser/screenshot checks when tooling exists; record gaps honestly.

### Task 8: Hosting and final documentation

**Files:** `README.md`, `docs/DEPLOYMENT.md`, `docs/PROJECT-STATUS.md`, `.env.example`.

- [ ] Add documentation checks for required environment keys and safe deployment commands.
- [ ] Document cPanel/VPS setup, MySQL, storage, HTTPS, SMTP, caching, backups, rollback, and smoke tests.
- [ ] Run the final verification commands and record actual results, limitations, and any required external configuration.
