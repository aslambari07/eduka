# EDUKA Role UI Audit & Alignment Design

## Intent

Align the implemented Admin, Pengajar, and Peserta surfaces with the existing Superdesign project `da5e3497-9e37-4e76-ad20-e0f61058dc06`, using the project’s dark workspace shell, light reading surfaces, Archivo/Manrope type scale, emerald actions, and reusable card/form language.

## Goals

- Audit every implemented role route, Blade view, shared component, form, table, pagination block, empty state, and mobile navigation path.
- Give Admin and Pengajar a reusable role-aware workspace shell whose links point only to routes that exist in Laravel.
- Keep Peserta dashboard and learning pages on the established dark-hero/light-content rhythm, including profile, notes, certificates, quiz, and assignment surfaces.
- Preserve all existing route names, controllers, models, middleware, authorization, form methods, validation, uploads, filters, and pagination.
- Record per-role audit status and the remaining visual limitation in `docs/ROLE-UI-AUDIT.md`.

## Non-goals and anti-goals

- Do not create mock analytics, schedules, notifications, messages, or other Superdesign-only features without application routes/data.
- Do not replace the existing Superdesign project or change the public homepage identity.
- Do not change authentication, passwords, roles, database schema, or seed data.
- Do not claim local pixel comparison when no local browser screenshot runner is available.

## Constraints

- Laravel Blade + Tailwind CSS v4; no new dependency.
- Use the existing `.eduka-*` semantic tokens in `resources/css/app.css`.
- Keep mobile navigation keyboard-visible and usable at narrow widths; tables may scroll horizontally.
- Use only real dynamic data already supplied by controllers.

## Approaches considered

1. **Utility-only normalization.** Retain the public navbar on all role pages and only change utility classes. This is low risk but leaves the largest Superdesign mismatch—the instructor workspace sidebar—and duplicates role navigation decisions across views.
2. **Reusable role workspace shell (chosen).** Add one Blade component with real Admin/Pengajar route maps, desktop sidebar, mobile disclosure, profile/logout actions, and active state. Wrap role workspace pages with it, then normalize Peserta reading pages with existing semantic surfaces. This best matches the available Instructor Workspace draft while keeping unsupported mockup features out.
3. **Per-page visual rewrites.** Rebuild every page independently from each draft. This would duplicate shell logic, increase regression risk, and encourage invented content where no backend data exists, so it is rejected.

## Implementation decisions

- `workspace-shell` owns only navigation chrome; page-specific forms, tables, empty states, and data remain in their existing views.
- Admin navigation includes Dashboard, Pengguna, Kategori, Kelas, and Laporan.
- Pengajar navigation includes Dashboard, Kelas Saya, Kuis, and Penilaian.
- Peserta keeps the public navigation pattern because the available participant drafts use a top navigation rather than a persistent sidebar.
- Profile, certificate index, and note editor are moved to the same dark-hero/light-content composition as the learning flow.

## Verification

- Add view-contract tests for role shell links, active role state, and participant light-surface coverage before implementation.
- Run the complete Laravel test suite, Blade cache, Vite build, route list, and authenticated HTTP smoke tests for all three roles.
- Inspect available Superdesign screenshots and explicitly report the absence of a local browser screenshot runner.
