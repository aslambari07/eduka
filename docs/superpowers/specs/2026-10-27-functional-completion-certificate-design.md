# EDUKA Functional Completion, Demo Content, and Production Readiness Design

## Intent

Make the existing EDUKA Laravel application functionally coherent from enrollment through course completion and certificate issuance, while preserving the existing routes, role boundaries, database data, and Superdesign visual language.

The primary correctness rule is that server-side course completion is derived from required content and evaluated outcomes, not from page visits or client-provided progress values.

## Goals

- Centralize completion and progress calculation in `CourseCompletionService`.
- Add a minimal `is_required` flag to lessons, quizzes, and assignments with a backward-compatible default of `true`.
- Re-evaluate completion after lesson completion, quiz submission, and instructor grading.
- Automatically issue one certificate per participant/course when all required conditions pass.
- Preserve idempotence under repeated requests and transaction retries.
- Harden direct requests for unpublished content, certificate ownership, and instructor ownership.
- Add an explicit, guarded, idempotent `DemoCourseSeeder` with four complete Indonesian-language courses.
- Add behavior-focused feature tests for CRUD, authorization, progress, scoring, automatic certificate issuance, idempotence, and failure states.
- Document actual verification results and cPanel/hosting blockers without claiming deployment readiness without evidence.

## Non-goals

- No production deployment or server mutation.
- No redesign of the existing Superdesign-aligned UI.
- No queue requirement; completion evaluation remains synchronous for the current application.
- No rich-text editor, video hosting, payment processing, or new external dependency.
- No destructive reset, fresh migration, wipe, or automatic demo data insertion in production.
- No fabricated learner metrics or demo data in public views beyond the explicit local/testing seeder.

## Constraints and existing architecture

- Laravel 13 with Eloquent, Blade, Vite/Tailwind, and MySQL/MariaDB-compatible migrations.
- Existing roles are `admin`, `pengajar`, and `peserta`, enforced by `RoleMiddleware`.
- Existing route names and learning URLs remain stable. New public certificate verification may be added only if needed to separate owner access from public verification.
- Existing unique constraints on enrollment, lesson progress, and certificates are preserved and strengthened only through safe additive migrations.
- Existing `CertificateService` is the starting point; its rules move into the central completion service rather than being duplicated in controllers.
- Existing quiz scoring remains server-side and continues to use submitted option IDs only to look up persisted options.

## Decisions

### Completion semantics

`is_required = true` is the backward-compatible default for existing and newly created content. Only required, published content participates in completion:

- Required lessons pass when a participant has a non-null `completed_at` for that lesson.
- Required quizzes pass when the participant has at least one persisted attempt with `passed = true`.
- Required assignments pass only when a persisted submission has `status = graded` and `score >= pass_score`.
- A required assignment with no submission, submitted/pending status, or a below-threshold score blocks completion.
- A course with no required published lesson has no completable learning path and cannot issue a certificate; this remains explicit and test-covered.

Progress is the percentage of required published items satisfied across lessons, quizzes, and assignments. It is `0` when there are no required items, and `100` only when every required item passes. The service updates the enrollment to `completed` and sets `completed_at` only at 100%; otherwise an existing completed enrollment is not silently regressed.

### Automatic certificate issuance

`CourseCompletionService::evaluate(User $user, Course $course): CourseCompletionResult` is the single entry point. It runs inside a database transaction when called from a state-changing workflow, locks the enrollment row where appropriate, updates progress/status, and uses `Certificate::firstOrCreate` backed by the existing unique `(course_id, user_id)` constraint. Certificate codes remain unique and are generated only for a newly created record.

The service returns a small result object/array containing progress percentage, completion boolean, and certificate. Controllers do not calculate completion independently. The existing manual certificate route delegates to the same service and remains a safe compatibility path; it cannot issue a certificate before all required conditions pass.

### Trigger points

- Lesson completion: update lesson progress, then evaluate.
- Quiz submission: persist the scored attempt, then evaluate.
- Assignment grading: persist score/status/feedback, then evaluate.
- Manual certificate issue: evaluate and return the existing certificate or validation error.

All trigger calls are synchronous and idempotent. The service must not trust a score/progress value sent by the browser.

### Certificate access

Participant certificate listing/detail is owner-scoped. A participant cannot use the owner route to view another participant's certificate. Public verification is a separate intentionally limited surface keyed by the unique certificate code and exposes only course title, issue date, code, and validity—not the participant's email or private account details. The existing HTML certificate/print behavior is retained; PDF generation is not added without an existing PDF capability.

### Content visibility and legacy behavior

Quiz and assignment content is eligible only when it is published and, when attached to a lesson, that lesson is also published. A course with no required published lesson has progress `0%` and cannot complete, even if optional or non-lesson content exists. Certificates are immutable achievements: the app does not revoke an existing certificate when an instructor later edits course requirements; authoring changes after issuance are documented as an operational risk.

### Authoring and CRUD scope

Existing instructor authoring is extended to real update/delete actions for content that has safe ownership boundaries. Deletion is guarded by relationship integrity: content with learner attempts, progress, submissions, or certificates is deactivated/unpublished rather than hard-deleted; otherwise it may be deleted within a transaction. Admin keeps its existing user/category/course moderation boundaries and does not gain destructive certificate mutation.

### Demo content

`DemoCourseSeeder` is a separate explicit seeder. It aborts outside `local` and `testing`, creates a development instructor only when the stable account is absent, upserts by stable course slug and nested stable slugs/positions, and never deletes unrelated rows. It creates four courses, at least four meaningful published lessons per course, one published quiz with persisted questions/options, and one published assignment with valid submission rules. It does not update or reset existing users/passwords or unrelated courses. Running it repeatedly preserves counts and stable relationships.

## Approaches considered

1. **Selected: synchronous central service plus additive required flags.** Fits the current synchronous Laravel workflows, supports optional content correctly, is easy to test, and avoids queue configuration risk.
2. **All published content implicitly required.** Smaller migration surface, but cannot express optional lessons/quizzes/tasks and would make future course authoring misleading.
3. **Events/listeners/jobs.** More decoupled, but introduces queue/retry/operational complexity that the current app does not need for a reliable local/cPanel baseline.

## Implementation boundaries

- `app/Services/CourseCompletionService.php`: completion result and all progress/certificate rules.
- New additive migration: `is_required` on lessons, quizzes, assignments; existing rows default to `true`.
- Learning/instructor/certificate controllers: call the service only at state transitions and enforce ownership/publication checks.
- Models and views: expose required state and real status without changing the established visual system.
- `database/seeders/DemoCourseSeeder.php`: guarded idempotent four-course seed.
- Feature tests: database-backed request workflows and negative/security cases.
- `docs/FUNCTIONAL-AUDIT.md` and `docs/PRODUCTION-READINESS.md`: evidence-based audit and hosting checklist.

## Acceptance evidence

The implementation is only reportable as functionally complete when the fresh suite demonstrates CRUD persistence, role authorization, enrollment and protected learning, server-side quiz scoring, assignment statuses, required/optional progress, automatic certificate issuance, duplicate prevention, certificate ownership/verification behavior, idempotent demo seeding, migration status, view cache, frontend build, route inventory, and documented hosting limitations.
