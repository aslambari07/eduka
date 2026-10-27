# EDUKA Role UI Alignment Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Align all implemented Admin, Pengajar, and Peserta interfaces with the existing Superdesign language while preserving every Laravel action.

**Architecture:** Introduce one reusable `workspace-shell` Blade component for Admin and Pengajar navigation, with real route maps and a responsive mobile disclosure. Keep page bodies in their existing views, using semantic dark/light surface classes for participant/profile/learning pages rather than changing backend contracts.

**Tech Stack:** Laravel 13, Blade components, Tailwind CSS v4, Vite, PHPUnit feature tests.

**Spec:** `docs/superpowers/specs/2026-10-27-role-ui-audit-design.md`

## Global Constraints

- Preserve route, controller, model, middleware, authentication, role, authorization, validation, upload, filter, pagination, and database behavior.
- Do not add dependency, fake data, unsupported feature links, password changes, or destructive seed/database commands.
- Use existing Superdesign project `da5e3497-9e37-4e76-ad20-e0f61058dc06` and existing EDUKA tokens.
- State honestly whether local browser screenshot comparison is available.

## Review Focus

- Role navigation must expose only real Laravel routes and preserve active state on desktop and mobile; test the rendered hrefs for both roles.
- Admin tables and forms must remain readable and horizontally scrollable at narrow widths; preserve POST actions and pagination.
- Pengajar authoring/grading forms must retain CSRF, multipart/validation contracts, and ownership links; preserve route assertions.
- Peserta light surfaces must not inherit white text or dark form controls; test semantic classes and CSS light color scheme.
- Empty states and alert/validation surfaces must remain visible after shell changes; assert representative responses and run the full suite.

---

### Task 1: Add failing role-surface contracts

**Files:**
- Modify: `tests/Feature/UiUxContractTest.php`

- [ ] Add a test that an admin response includes the workspace shell, real Admin links, and active dashboard state.
- [ ] Add a test that an instructor response includes the workspace shell, real Pengajar links, and active dashboard state.
- [ ] Add a source/response contract for participant profile, certificate index, and note views using the established light reading surface.
- [ ] Run the targeted tests and confirm they fail because the shell/page contracts are not implemented yet.

### Task 2: Implement reusable role workspace shell

**Files:**
- Create: `resources/views/components/workspace-shell.blade.php`
- Modify: `resources/css/app.css`
- Modify: `resources/js/app.js` only if the mobile disclosure requires it

- [ ] Define Admin/Pengajar route maps using only named routes in `routes/web.php`.
- [ ] Render desktop sidebar, active state, profile/logout actions, and a responsive mobile disclosure.
- [ ] Add minimal semantic workspace shell styles using existing tokens and visible focus states.
- [ ] Keep the component free of fake metrics, unsupported links, or new backend behavior.

### Task 3: Wrap Admin and Pengajar pages with the shell

**Files:**
- Modify: `resources/views/admin/dashboard.blade.php`
- Modify: `resources/views/admin/users.blade.php`
- Modify: `resources/views/admin/categories.blade.php`
- Modify: `resources/views/admin/courses.blade.php`
- Modify: `resources/views/admin/reports.blade.php`
- Modify: `resources/views/instructor/dashboard.blade.php`
- Modify: `resources/views/instructor/courses.blade.php`
- Modify: `resources/views/instructor/quizzes.blade.php`
- Modify: `resources/views/instructor/content.blade.php`
- Modify: `resources/views/instructor/submissions.blade.php`

- [ ] Replace duplicated public navigation wrappers with the reusable shell while keeping every form/table/action body intact.
- [ ] Normalize primary/secondary button, heading, copy, input, table, and empty-state classes to the existing dark workspace tokens.
- [ ] Ensure authoring and grading forms remain usable on narrow screens with stacked controls or horizontal scroll where appropriate.

### Task 4: Align remaining Peserta surfaces

**Files:**
- Modify: `resources/views/dashboard.blade.php`
- Modify: `resources/views/profile/edit.blade.php`
- Modify: `resources/views/certificates/index.blade.php`
- Modify: `resources/views/learning/note.blade.php`
- Modify: `resources/views/components/navigation.blade.php` only if role-aware mobile behavior needs a scoped adjustment

- [ ] Preserve the participant dashboard’s dynamic metrics and learning actions while fixing light-section button/link contrast.
- [ ] Convert profile, certificate index, and note editor to dark hero + light content surfaces with real data and existing actions.
- [ ] Keep certificate display, note update/delete, pagination, and profile/password forms unchanged at the route level.

### Task 5: Document role audit and verify

**Files:**
- Create or modify: `docs/ROLE-UI-AUDIT.md`
- Modify: `docs/PROJECT-STATUS.md` if final counts or limitations change

- [ ] Document all implemented Admin, Pengajar, and Peserta pages, mapped views/routes, status, findings, corrections, and visual limitations.
- [ ] Run `php artisan test` and record the exact count.
- [ ] Run `php artisan view:cache`, `npm run build`, and `php artisan route:list`.
- [ ] Run public and authenticated HTTP smoke tests for every role route that is safe to GET.
- [ ] Inspect available Superdesign screenshots and report local screenshot-runner availability without overclaiming.
