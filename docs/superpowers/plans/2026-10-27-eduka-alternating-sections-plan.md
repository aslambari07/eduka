# EDUKA Alternating Sections Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restore visual rhythm by composing EDUKA pages from explicit dark and light section contexts without changing application behavior.

**Architecture:** Add reusable semantic section/card/text tokens in `resources/css/app.css`, with light-section selectors strong enough to override the existing dark-shell utility normalization. Apply those classes to the homepage and public catalog/detail flow first; keep dashboards and learning workspaces dark by default, using only a small number of deliberate light blocks where the page structure benefits from it. Add response contract tests so future CSS refactors cannot collapse the public composition back into one background.

**Tech Stack:** Laravel 13, Blade, Tailwind CSS 4, Vite 7, PHPUnit feature tests.

**Spec:** User request “EDUKA — REVISI BACKGROUND SECTION SELANG-SELING DARK & LIGHT” provided in the current conversation.

## Global Constraints

- Preserve routes, controllers, models, passwords, roles, middleware, database, content, and existing actions.
- Keep the dark theme as a primary surface; do not add a theme switcher.
- Use full-block section backgrounds, not random card-by-card color changes.
- On light sections use `#FFFFFF`/`#F8FAFC`, dark text, `#E5E7EB` borders, and `#15803D` accents.
- On dark sections use `#0B1120`/`#111827`, light text, `#2B3648` borders, and `#22C55E` accents.
- Preserve responsive layout, reduced-motion support, and readable focus/disabled/feedback states.
- Do not run destructive database commands.

## Review Focus

- Light section with legacy `bg-white`/`text-zinc-*` utilities must render as a light block with dark copy — covered by the homepage/catalog response contract and CSS selectors.
- Dark section with legacy utility classes must remain dark and readable — covered by the existing dark shell contract and full feature suite.
- Public cards inside a light section must not inherit dark card text/background accidentally — covered by the `eduka-card-light` contract.
- Filter/search controls inside light catalog content must stay light and readable — covered by light-section form selectors and catalog smoke test.
- Authenticated dashboards must not be forced into a white marketing layout — covered by role smoke tests and existing dark dashboard markup.

---

### Task 1: Pin the alternating-section contract

**Files:**
- Modify: `tests/Feature/UiUxContractTest.php`

- [x] **Step 1: Write failing feature assertions** for homepage dark hero, light classes section, dark benefits section, light CTA section, dark footer, and catalog/detail light content.
- [x] **Step 2: Run the focused UI contract test** and confirm it fails because the semantic section classes are not yet present.
- [x] **Step 3: Keep the test assertions scoped to rendered Blade contracts**, not implementation details such as exact utility ordering.

### Task 2: Create semantic light/dark section tokens

**Files:**
- Modify: `resources/css/app.css`

- [x] **Step 1: Add semantic section, card, text, link, button, border, and form-control tokens for light surfaces.**
- [x] **Step 2: Ensure scoped selectors override legacy dark normalization only within `.eduka-section-light` and do not change workspace defaults.
- [x] **Step 3: Preserve dark tokens, focus states, reduced motion, alerts, and form behavior.
- [x] **Step 4: Run the focused UI contract test after view classes are added in Task 3.

### Task 3: Compose public pages with intentional alternation

**Files:**
- Modify: `resources/views/home.blade.php`
- Modify: `resources/views/courses/index.blade.php`
- Modify: `resources/views/courses/show.blade.php`
- Modify: `resources/views/components/class-card.blade.php`
- Modify: `resources/views/components/footer.blade.php`

- [x] **Step 1: Keep homepage hero dark, make featured classes light, benefits dark, CTA light, and footer dark.**
- [x] **Step 2: Add a light variant for reusable class cards and use it only in public light catalog sections.
- [x] **Step 3: Give catalog/detail content a light reading surface after the dark hero while preserving dark hero/navigation/actions.
- [x] **Step 4: Normalize headings, descriptions, links, filters, curriculum rows, and sidebar copy for each section context.

### Task 4: Tune auth and workspace block composition

**Files:**
- Modify: `resources/views/auth/*.blade.php` as needed
- Modify: `resources/views/dashboard.blade.php`
- Modify: `resources/views/instructor/dashboard.blade.php`
- Modify: `resources/views/admin/dashboard.blade.php`
- Modify: relevant learning/profile/certificate views only where a whole section needs a light context

- [x] **Step 1: Keep auth and workspaces dark-first and introduce light blocks only where they improve reading hierarchy.
- [x] **Step 2: Verify navbar, footer, form, table, alert, and mobile menu contrast in both contexts.
- [x] **Step 3: Avoid changing backend behavior or introducing a theme switcher.

### Task 5: Verify implementation

- [x] **Step 1: Run `php artisan test`.
- [x] **Step 2: Run `php artisan view:cache`.
- [x] **Step 3: Run `npm run build`.
- [x] **Step 4: Run `php artisan route:list`.
- [x] **Step 5: Run HTTP smoke checks for homepage/catalog/detail/login and all role dashboards; report browser validation only if a browser runner exists.
