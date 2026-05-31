# Project Instructions for Hermes/Codex

## Project Context

- This repository is the CodeIgniter 4 migration target for the legacy DPS project.
- The legacy parity/reference project is located at `/Applications/XAMPP/xamppfiles/htdocs/dps`.
- Every migration task must preserve the core business flow from the legacy project, while improving structure, query performance, and UI/UX quality in this CI4 project.

## Mandatory Workflow for Migration Requests

When the user asks to migrate, rebuild, fix, or improve a module from the legacy DPS project, always follow this workflow:

1. Understand the legacy flow deeply before editing.
   - Inspect the relevant legacy controller, model, view, routes, helpers, JavaScript, and database usage in `/Applications/XAMPP/xamppfiles/htdocs/dps`.
   - Identify the exact user flow, request methods, redirects, flash messages, validation rules, session/auth assumptions, uploaded files, and edge cases.
   - Use the legacy view as the visual and functional reference, but do not copy it blindly.

2. Map the feature into CodeIgniter 4 architecture.
   - Put request handling in CI4 controllers under `app/Controllers`.
   - Put database access in CI4 models under `app/Models` or service classes under `app/Services` when business logic is non-trivial.
   - Put presentation in `app/Views`, following the existing project layout and shared components.
   - Register or adjust routes in `app/Config/Routes.php` using the existing route style.

3. Preserve parity first, then improve.
   - Match the legacy business rules and data results first.
   - Keep compatibility with the existing DPS database schema unless the user explicitly asks for schema changes.
   - Maintain existing role/session constraints and access behavior.
   - After parity is clear, improve code structure, query efficiency, validation, and UI/UX.

4. Optimize queries and data access.
   - Avoid N+1 queries.
   - Select only required columns where practical.
   - Use joins, grouping, eager loading patterns, or aggregate queries when they reduce repeated database calls.
   - Keep query conditions explicit and readable.
   - Prefer model/service methods over raw query duplication in controllers or views.

5. Improve UI/UX intentionally.
   - Treat legacy views as the reference for content, fields, actions, and user flow.
   - Modernize layout, spacing, hierarchy, forms, tables, empty states, loading/error states, and responsive behavior.
   - Preserve the existing CI4 project visual language unless the user requests a redesign.
   - Use Bootstrap 5, DataTables, Toastr, SweetAlert2, and existing shared components consistently when already used in the module.

6. Validate the implementation.
   - Run relevant PHP syntax checks for changed PHP files.
   - Run available tests or targeted manual checks when practical.
   - Check routes, controller methods, view variables, form actions, CSRF expectations, redirects, and uploaded-file paths.
   - If full validation is not possible, clearly state what was checked and what still needs manual testing.

7. Protect existing work.
   - Do not revert unrelated user changes.
   - Before changing files that already have modifications, inspect them carefully and preserve unrelated edits.
   - Avoid destructive git commands unless explicitly requested by the user.

## Expected Response Style

For each completed migration/improvement request, respond in Indonesian and include:

- What legacy flow was reviewed.
- What CI4 files were changed.
- What query or architecture improvements were made.
- What UI/UX improvements were made.
- What validation/testing was performed.
- Any remaining manual QA steps or risks.

Keep responses concise but specific, with clickable file paths where relevant.
