# Project Instructions

This repository is a CodeIgniter 4 application for a pencak silat registration and administration system.

## Stack

- PHP `^8.2`
- CodeIgniter `^4.7`
- Composer for dependencies
- PHPUnit `^10.5.16` for tests
- XAMPP/local Apache environment
- No Node/package frontend build is configured in this repository

## Working Rules

- Prefer small, focused changes that match the existing CodeIgniter structure.
- Keep controllers thin when practical; put reusable database logic in models or services already present in the codebase.
- Use CodeIgniter helpers and services consistently with existing files.
- Do not introduce a new framework, build system, or large abstraction without explicit approval.
- Do not change `.env`, credentials, local database settings, or writable runtime files unless explicitly requested.
- Treat authentication, role filters, payments, participant registration, and generated PDFs as security-sensitive code paths.
- Preserve existing Indonesian labels, route names, and domain terminology unless the task explicitly asks to rename them.

## CI3-to-CI4 Migration Rules

- Migrate one bounded module at a time; avoid broad rewrites across unrelated admin areas.
- Track every migrated module in `docs/migration-ci4-status.md` before marking it complete.
- Preserve route behavior unless a route change is explicitly part of the migration plan.
- Check `app/Config/Routes.php` and `app/Config/Filters.php` whenever migrating controller entry points.
- Prefer CI4 models, services, validation, request objects, and response helpers over legacy CI3 patterns.
- When moving database logic, verify `allowedFields`, primary keys, timestamps, soft delete assumptions, return types, and transaction boundaries.
- For migrations, document whether the change is schema-only, data backfill, destructive, reversible, or requires manual production steps.
- Keep public pendaftaran flows, kontingen flows, admin roles, payment confirmation, and PDF generation behaviorally equivalent unless explicitly changed.
- Do not mark a module complete without syntax checks, route/filter review, and either automated tests or documented manual QA.

## Verification

- Preferred full test command: `composer test`.
- Direct PHPUnit command: `vendor/bin/phpunit`.
- For quick PHP syntax checks, run `php -l <file>` on touched PHP files.
- For CodeIgniter CLI checks, use `php spark` commands when relevant.
- If database-backed tests require local test database configuration, report the blocker instead of inventing credentials.

## Security Checklist

- Validate and normalize all request input before persistence.
- Use query builder/model APIs rather than string-concatenated SQL.
- Ensure admin and kontingen routes keep the correct filters and role checks.
- Keep CSRF expectations consistent with the existing form handling.
- Escape user-controlled output in views.
- Validate uploaded files by size, MIME/type, extension, and storage path.
- Do not log passwords, tokens, NIK, KK, or payment-sensitive data.

## Review Style

- Findings first, ordered by severity.
- Include file and line references when possible.
- Call out missing tests or unverified assumptions.
- Keep summaries brief and actionable.
