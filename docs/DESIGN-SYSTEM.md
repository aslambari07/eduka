# EDUKA design system

The visual source of truth is the existing Superdesign project `da5e3497-9e37-4e76-ad20-e0f61058dc06`. The project was inspected through the authenticated Superdesign CLI and its 13 drafts, reusable components, and Archivo/Manrope font assets were inventoried before implementation.

## Visual language

- Display type: Archivo, primarily 600–800, with tight tracking for large headings.
- Body type: Manrope, 400–700, with generous line height for learning content.
- Application background: `#0B1120`; dark sections: `#0B1120`/`#111827`; cards/panels: `#172033`; hover: `#1E293B`.
- Public section rhythm alternates full-width dark and light blocks: dark hero, light catalog and benefits, dark learning steps, light CTA/content, and dark footer.
- Text tokens: primary `#F9FAFB`, secondary `#CBD5E1`, metadata `#94A3B8`.
- Action green: `#22C55E`, hover `#16A34A`; input background `#111827`, input border `#374151`.
- Light sections use `#FFFFFF`/`#F8FAFC`, heading `#111827`, copy `#4B5563`, border `#E5E7EB`, and green `#15803D`.
- Feedback tokens use readable dark backgrounds: success `#12321F`/`#BBF7D0`, error `#3A1720`/`#FECDD3`.
- Borders use `#2B3648`; muted text is not the only signal for actions or status.
- Radius scale: 12px controls, 16px navigation, 24px cards, 32px feature surfaces.
- Atmospheric treatment: emerald radial glows, black-to-zinc gradients, subtle noise, glass panels only where the approved workspace compositions use them.

## Layout and behavior

The shared shell uses a maximum content width of 1440px with 20–64px responsive gutters. Homepage, catalog/detail, certificate, public lesson, and protected learning pages use a dark hero followed by deliberate light reading/content sections; authentication and instructor/admin workspaces remain dark-first, while participant/instructor dashboard bodies use one coherent light section below the dark hero. Desktop navigation collapses into a keyboard-visible mobile menu below the large breakpoint, including authenticated dashboard/profile/logout actions. Same-origin links prefetch on focus/pointer and the shell opts into View Transitions where supported. Grids collapse to one column, action buttons remain usable at narrow widths, and admin tables use horizontal scrolling rather than shrinking text below a readable size.

Admin and Pengajar workspace pages use the reusable `resources/views/components/workspace-shell.blade.php` with real role-specific route maps, active states, desktop sidebar, mobile disclosure, profile, catalog, and logout actions. Unsupported Superdesign-only analytics, calendar, and revenue items are intentionally not rendered.

## Component mapping

| Superdesign component | Blade implementation |
|---|---|
| `EDUKANavigationBar` | `resources/views/components/navigation.blade.php` |
| `PageFooter` | `resources/views/components/footer.blade.php` |
| `ClassCard` | `resources/views/components/class-card.blade.php` |
| `LearningDashboardCard` | Dashboard course progress card |
| `FeatureCardGlass` | Shared `.glass` panel treatment in workspace views |

Tokens and utility classes are centralized in `resources/css/app.css`. Blade output escapes user content by default; the only intentional rich-text-like output is escaped line-break rendering for stored lesson/course text.

## Fidelity status

The approved hierarchy, typography, dark/green palette, alternating public section rhythm, card language, and responsive composition are implemented. Legacy utility classes are normalized inside `.eduka-app`, with explicit `.eduka-section-light` opt-in contexts so existing dark workspaces remain readable without changing application behavior. Automated route/response tests and the production Vite build are available. Chrome/CDP browser smoke is available for role login/landing and selected responsive checks; viewport-by-viewport pixel comparison remains an explicit follow-up rather than an unverified claim.
