# EDUKA Complete Rebuild Design

## Intent

Build a production-shaped Laravel 13 education portal from the intentionally empty `/home/aslam/Desktop/EDUKA` repository. The application must support public discovery, authenticated participant learning, instructor authoring/grading, and administrator management with database-backed workflows and server-side authorization.

## Source of truth

The existing Superdesign project `da5e3497-9e37-4e76-ad20-e0f61058dc06` is accessible and authenticated. Its 13 drafts are the visual source of truth:

- Homepage: `d68d3110-2f9d-4529-9766-7a8bcb267b0e`
- Minimalist premium variant: `fda6697f-abf5-408d-8509-9f1b7f86bfb0`
- Navigation/flow reference: `b0e05683-8393-44fb-a263-7a7eee0db1da`
- Class catalog: `7dfde64b-e6d0-4d5f-ad25-2e18cbd8a87a`
- Class detail: `59a85cd4-1fa2-498f-ba27-fa955856e153`
- Learner dashboard: `8212ca5a-2db0-4c58-8938-2dce5759f64b`
- Lesson player: `e5d40a27-6694-44e4-bb00-ee4308ad0a77`
- Quiz interface: `bbc03665-45f7-4011-8bbf-c0878b30629b`
- Assignment submission: `bebdc068-dcf5-47dd-afd8-01dd396e1234`
- Instructor dashboard: `42c9f944-c709-4ef8-9116-14d3775be27e`
- Quiz builder: `8c9b7db4-ea68-407b-8637-6c90116a277b`
- Grading workspace: `57a0ff92-fc2a-4bcf-9755-ae66f523b565`
- Certificate: `552783e4-1183-45e9-bda5-5bef53d66bd6`

The project has five reusable components: `EDUKANavigationBar`, `PageFooter`, `ClassCard`, `LearningDashboardCard`, and `FeatureCardGlass`. The available durable assets are Archivo and Manrope Google fonts. The implementation will use the same dark atmospheric/emerald visual language, glass surfaces, rounded 2xl/3xl cards, Archivo display headings, and Manrope body copy.

## Goals

- Install a real Laravel 13 application with PHP 8.3+, Blade, Vite, SQLite for local tests, and MySQL/MariaDB-compatible migrations.
- Implement the three roles exactly: `admin`, `pengajar`, and `peserta`.
- Provide public catalog/detail/auth screens and protected learner/instructor/admin workflows.
- Persist classes, lessons, enrollment, progress, quizzes, assignments, notes, grading, and certificates with foreign keys and unique constraints.
- Enforce all sensitive access in middleware/policies and validate all writes server-side.
- Provide idempotent development seed data without embedding production credentials.
- Document the design mapping, deployment, test results, and known limitations.

## Non-goals

- No React/Next.js frontend migration.
- No payment processing, live video hosting, or external LMS integration.
- No production deployment or claim of live hosting.
- No generated “fake” statistics disconnected from database records.

## Anti-goals

- Do not use a generic admin template in place of the approved visual system.
- Do not trust client-provided scores, completion, grades, or certificate eligibility.
- Do not expose private assignment files, notes, quiz answers, or instructor-owned data across users.
- Do not reset, drop, or truncate databases as part of normal setup.

## Approaches considered

1. **Laravel + Blade + Tailwind/Vite (selected).** It matches the required stack, is compatible with shared hosting, renders authenticated pages server-side, and keeps authorization close to routes/controllers. The tradeoff is more Blade markup than a component SPA.
2. Laravel API plus React/Next.js. This would improve client-side interaction but conflicts with the Blade-first requirement, adds deployment complexity, and makes cPanel hosting less predictable.
3. Static Blade mockups with in-memory/demo content. This would be faster visually but fails the database, authorization, and end-to-end workflow requirements.

## Architecture decisions

- Use Laravel’s built-in session authentication with a `role` string on `users`; route groups use `role` middleware and model policies enforce ownership.
- Use Eloquent models with explicit relationships and form requests for validation. Controllers stay thin; quiz scoring and certificate eligibility live in services.
- Use a single shared dark/emerald layout plus role-specific shells. Reusable Blade components cover navigation, footer, cards, badges, alerts, and form controls.
- Use SQLite in the test environment and keep schema types/functions compatible with MySQL/MariaDB.
- Use local private storage for assignment files and authorize downloads through controller actions.

## Builder decisions

- The empty workspace is a deliberate clean rebuild; scaffold in the exact requested target directory.
- The 13 Superdesign drafts map to the closest functional routes; admin user/category/report screens use the same established shell because no separate admin draft exists.
- The existing Superdesign HTML will guide tokens and component structure; no new Superdesign generation or replacement project is needed.
- Development accounts are created by the seeder with documented local-only credentials sourced from the seeder, never production secrets.

## Acceptance evidence

The builder must run migrations, seeders, route checks, the full automated suite, and the production asset build where available. Final documentation must distinguish verified behavior from configuration that still depends on a real MySQL/SMTP/hosting environment.
