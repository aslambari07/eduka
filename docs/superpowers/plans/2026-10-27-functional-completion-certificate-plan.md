# EDUKA Functional Completion, Demo Content, and Production Readiness Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make EDUKA’s real database workflows calculate required-content progress server-side, issue one automatic certificate at completion, provide four complete local demo courses, and document functional/hosting evidence.

**Architecture:** Additive schema flags (`is_required`) feed a single synchronous `CourseCompletionService`. State-changing controllers call the service after their existing transaction; the service updates enrollment completion and creates certificates idempotently under the existing unique course/participant constraint. A guarded `DemoCourseSeeder` upserts stable content without touching unrelated data.

**Tech Stack:** Laravel 13, PHP 8.3+, Eloquent, MySQL/MariaDB, PHPUnit feature tests, Blade/Vite/Tailwind.

**Spec:** `docs/superpowers/specs/2026-10-27-functional-completion-certificate-design.md`

## Global Constraints

- Preserve the existing Laravel 13 architecture, role middleware, route names, authentication, and Superdesign visual language.
- Add no dependency unless the existing code cannot provide the required behavior.
- Never use `migrate:fresh`, `db:wipe`, destructive production seeders, or production deployment commands.
- Demo data is created only by explicit `DemoCourseSeeder` execution in `local` or `testing`.
- Do not trust browser-supplied progress or score; use persisted lesson progress, quiz attempts, and assignment grading.
- Existing rows receive `is_required = true` through safe additive migration defaults.
- All certificate creation must remain idempotent under repeated calls and protected by database uniqueness.

## Review Focus

- A required lesson marked complete through a direct request must be rejected unless it is published and belongs to the course; test the request and database state.
- A participant with 100% lesson progress but an unpassed required quiz/assignment must remain incomplete and certificate-free; test server-derived progress.
- Repeated completion evaluation and concurrent-like repeated requests must leave exactly one certificate; test unique persistence and stable code.
- A submitted/pending or below-pass assignment must block completion, while the instructor grading the final assignment triggers completion; test both statuses.
- A participant must not read another participant’s private certificate route, while a public verification surface must expose only documented minimal data; test owner/non-owner/guest behavior.

---

### Task 1: Add required-content schema and model contracts

**Files:**
- Create: `database/migrations/2026_10_27_000017_add_required_flags_to_learning_content.php`
- Modify: `app/Models/Lesson.php`, `app/Models/Quiz.php`, `app/Models/Assignment.php`
- Test: `tests/Feature/DomainSchemaTest.php` or a new `tests/Feature/CompletionSchemaTest.php`

**Interfaces:**
- Produces boolean `is_required` attributes on Lesson, Quiz, and Assignment with `true` defaults.
- Existing records remain required after migration; new records may explicitly set `false`.

- [ ] Write a failing migration/model test that asserts the three columns exist, are cast to boolean, default to true, and allow an optional record to be persisted.
- [ ] Run the focused test and confirm it fails because the columns/casts are absent.
- [ ] Add the additive migration with `boolean('is_required')->default(true)` and safe down behavior; add fillable/casts to the three models.
- [ ] Run the focused test and the full existing domain schema tests; confirm persistence and legacy defaults.

### Task 2: Build the central completion service

**Files:**
- Create: `app/Services/CourseCompletionResult.php`
- Create: `app/Services/CourseCompletionService.php`
- Modify: `app/Models/Enrollment.php` if a progress accessor is needed
- Test: `tests/Feature/CourseCompletionServiceTest.php`

**Interfaces:**
- `CourseCompletionService::evaluate(User $user, Course $course): CourseCompletionResult`.
- `CourseCompletionResult` exposes `progressPercent(): int`, `isComplete(): bool`, and `certificate(): ?Certificate`.
- Required published lessons pass from `lesson_progress.completed_at`; required published quizzes pass from any passed attempt; required published assignments pass only from graded score >= pass score.

- [ ] Add focused failing tests for lesson-only incomplete progress, failed/pending required content, optional-content exclusion, empty required lesson behavior, and all-requirements completion.
- [ ] Run the focused tests and verify they fail for the missing service/rules rather than test setup errors.
- [ ] Implement the result value object and service with server-side queries, no controller-specific duplicate calculation, and a transaction/row lock around enrollment update and certificate creation.
- [ ] Ensure `Certificate::firstOrCreate` is paired with the existing unique `(course_id, user_id)` constraint and only generates a code for a new certificate.
- [ ] Run `CourseCompletionServiceTest` and the full suite; confirm progress is 100 only when every required published item passes.

### Task 3: Integrate lesson and quiz state transitions

**Files:**
- Modify: `app/Http/Controllers/LearningController.php`
- Modify: `app/Services/QuizScoringService.php`
- Test: `tests/Feature/ParticipantWorkflowTest.php`, `tests/Feature/CertificateWorkflowTest.php`

**Interfaces:**
- Lesson completion calls `CourseCompletionService` after a valid published lesson progress update.
- Quiz scoring persists the attempt transactionally and invokes completion evaluation after the attempt is finalized.

- [ ] Add failing request tests for unpublished lesson completion rejection, automatic certificate after the final lesson/quiz when no assignment blocks it, and score spoofing being ignored.
- [ ] Run the focused tests and verify the failures identify the missing publication check/automatic evaluation.
- [ ] Add the publication check to the completion endpoint and inject/use the completion service after lesson and quiz state changes; preserve existing quiz scoring and attempt limits.
- [ ] Run participant and certificate workflow tests, then the full suite.

### Task 4: Integrate assignment submission and grading transitions

**Files:**
- Modify: `app/Http/Controllers/LearningController.php`
- Modify: `app/Http/Controllers/InstructorWorkspaceController.php`
- Test: `tests/Feature/ParticipantWorkflowTest.php`, `tests/Feature/InstructorAdminWorkflowTest.php`, `tests/Feature/CertificateWorkflowTest.php`

**Interfaces:**
- Submission remains `submitted` until an instructor grades it.
- Grading preserves the existing owner check, stores score/status/feedback, then evaluates completion for the submission owner and course.
- A below-pass grade remains a blocking/needs-revision state; a passing grade is `graded` and can satisfy the required assignment.

- [ ] Add failing tests for pending submission blocking, below-pass grading blocking, final passing grading issuing the certificate, and another instructor being unable to grade/re-trigger another course.
- [ ] Run focused tests and verify they fail before integration.
- [ ] Call the central service only after the existing database write succeeds; keep upload storage private and deadline validation intact.
- [ ] Run all affected workflow tests and the full suite.

### Task 5: Harden certificate ownership and public verification

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/CertificateController.php`
- Create or modify: `resources/views/certificates/verify.blade.php`, `resources/views/certificates/show.blade.php`
- Modify: `resources/views/certificates/index.blade.php`
- Test: `tests/Feature/CertificateWorkflowTest.php`

**Interfaces:**
- Existing owner certificate route remains usable from the participant certificate list and is owner-scoped.
- Add a separate named public verification route only if needed, keyed by code and returning minimal verification data.
- Manual issue route delegates to `CourseCompletionService`; it cannot bypass requirements.

- [ ] Add failing tests for non-owner participant denial, owner access, guest public verification, invalid code, and minimal public response data.
- [ ] Run focused tests and verify the authorization/public-surface failures.
- [ ] Implement owner scoping and separate verification behavior without exposing private learner data; update links/forms to use the correct route.
- [ ] Run certificate tests and the full suite.

### Task 6: Add guarded idempotent four-course demo seeder

**Files:**
- Create: `database/seeders/DemoCourseSeeder.php`
- Modify: `README.md`, `database/seeders/DatabaseSeeder.php` only if needed to keep default seeding safe
- Test: `tests/Feature/DemoCourseSeederTest.php`

**Interfaces:**
- Explicit command: `php artisan db:seed --class=DemoCourseSeeder`.
- Seeder aborts outside `local`/`testing`, upserts stable slugs, creates four courses, at least four meaningful published lessons per course, one quiz with persisted questions/options, and one assignment per course.
- Re-running produces the same course/content counts and does not delete unrelated rows or reset passwords.

- [ ] Add failing seeder tests for environment guard, four course slugs, minimum lesson/content counts, quiz options, assignment rules, idempotent rerun, and unrelated-row preservation.
- [ ] Run the focused seeder tests and verify they fail because the class is absent/incomplete.
- [ ] Implement the seeder with stable nested upserts and explicit environment protection; do not call it automatically from production boot or migrations.
- [ ] Run the seeder tests and an explicit local seed dry-run against the separate testing database only.

### Task 7: Expand CRUD, authorization, and security coverage

**Files:**
- Modify: `tests/Feature/AdminManagementTest.php`, `tests/Feature/InstructorAuthoringTest.php`, `tests/Feature/ParticipantWorkflowTest.php`, `tests/Feature/InstructorAdminWorkflowTest.php`
- Modify application code only where a failing test identifies a real behavior defect.

**Interfaces:**
- Tests must assert database persistence, validation errors, ownership/role denial, publication visibility, private file access, and relationship integrity.

- [ ] Add real request/database tests for category create/validation, user role/status protection, course publication visibility, instructor content field persistence, and grading persistence.
- [ ] Add negative tests for admin/instructor/participant cross-role direct endpoint access, cross-course ownership, unpublished content, IDOR certificate/file access, and forged progress/score input.
- [ ] Run focused red tests, fix only root causes, then run the full suite.

### Task 8: Add functional and production-readiness documentation

**Files:**
- Create: `docs/FUNCTIONAL-AUDIT.md`
- Create: `docs/PRODUCTION-READINESS.md`
- Modify: `docs/PROJECT-STATUS.md`, `README.md`

**Interfaces:**
- Functional audit records actual route/workflow coverage, bugs found and fixed, demo content counts, role results, certificate evidence, and unresolved limitations.
- Production checklist separates verified evidence, unverified items, hosting configuration requirements, and blockers.

- [ ] Document exact commands and outputs only after final verification, including `php artisan migrate:status`, test database identity, route duplicate check, error-log review, view cache, build, and HTTP smoke.
- [ ] Document cPanel requirements: PHP/extensions, APP_ENV/DEBUG/KEY, HTTPS, `.env`/vendor protection, storage permissions, build assets, session/cache/queue/mail, private uploads, backups, and migration safety.
- [ ] Link the completion flow and seeder instructions from README without suggesting production demo seeding.

### Task 9: Final verification and review

**Files:**
- Test/build artifacts only; no source changes expected unless verification exposes a defect.

- [ ] Run `php artisan test` against `eduka_rebuild_testing` and record exact tests/assertions.
- [ ] Run `php artisan view:cache`.
- [ ] Run `npm run build`.
- [ ] Run `php artisan route:list --no-ansi`, `php artisan migrate:status`, duplicate-route inspection, and error-log inspection.
- [ ] Run authenticated HTTP smoke for public, Admin, Pengajar, Peserta, learning, grading, certificate owner, and public verification flows.
- [ ] Request a fresh code review, resolve Critical/Important findings, and rerun all required verification after fixes.
