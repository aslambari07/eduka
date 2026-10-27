# EDUKA MySQL XAMPP Migration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to execute this plan task-by-task.

**Goal:** Make EDUKA use the verified MySQL XAMPP application database `eduka_v1`, preserve the existing `eduka_rebuild` data, and provide importable SQL exports without operational dependence on SQLite.

**Architecture:** Keep Laravel's MySQL connection as the only local application/test path, while retaining the unused SQLite connection definition only where framework compatibility requires it. Export the verified `eduka_rebuild` schema/data into SQL files that target `eduka_v1`; do not import them until the XAMPP instance and target database are independently verified.

**Tech Stack:** Laravel 13, PHP 8.3, Eloquent migrations, MySQL 8 source, XAMPP MariaDB target, PHPUnit, Vite.

**Spec:** `/home/aslam/.codex/attachments/75b817d5-9edb-4afd-b00e-94a9f34a1fdd/Pasted text.txt`

## Global Constraints

- Preserve `eduka_rebuild` and all existing user, course, learning, submission, progress, and certificate data.
- Never run `migrate:fresh`, `db:wipe`, `DROP DATABASE`, `TRUNCATE TABLE`, destructive seeders, or an unverified import.
- Never print passwords, tokens, APP_KEY, or other secrets.
- Use a separate test database and never allow tests to point at `eduka_v1` accidentally.
- Do not delete `database/database.sqlite`; remove only operational references that are no longer needed.
- Do not stop or reconfigure another MySQL/MariaDB service without explicit need and evidence.

## Review Focus

- Instance identity: prove port, binary, datadir, and database target before using XAMPP.
- Source preservation: verify a non-empty, readable backup before any target write.
- Schema compatibility: compare all migration tables/columns/keys with the verified source export.
- Test isolation: prove PHPUnit uses `eduka_rebuild_testing`, never `eduka_v1` or `eduka_rebuild`.
- Runtime configuration: prove `php artisan serve` reads the local `.env` and uses MySQL after the target is ready.

### Task 1: Audit and backup

- [x] Inspect Laravel, test, migration, seeder, server, and environment configuration without printing secrets.
- [x] Identify MySQL 3306 and MariaDB 3307 as separate running services.
- [x] Create and verify `/home/aslam/Desktop/eduka_rebuild_backup_20261027_2148.sql`.
- [ ] Confirm XAMPP service identity and credentials through local operator configuration.

### Task 2: Produce safe SQL exports

- [x] Generate `database/exports/create_eduka_v1.sql` with only `CREATE DATABASE IF NOT EXISTS` and `USE` for `eduka_v1`.
- [x] Generate `database/exports/eduka_v1_schema.sql` from the verified migrations/source schema.
- [x] Generate `database/exports/eduka_v1_full.sql` from verified `eduka_rebuild` data, preserving password hashes but no plaintext application secrets.
- [x] Validate export headers, table count, insert count, database target, and absence of destructive database/table commands.
- [x] Document phpMyAdmin import and backup/restore procedure in `database/exports/README.md`.

### Task 3: Remove SQLite operational paths

- [x] Set the Laravel default connection fallback to MySQL.
- [x] Remove the Composer post-create SQLite file creation hook.
- [x] Set `.env.example` and README local instructions to MySQL/XAMPP with a separate MySQL test database.
- [x] Keep the empty SQLite artifact untouched until all references are audited.

### Task 4: Prepare and verify target

- [ ] Confirm XAMPP's actual port, user authentication, and `eduka_v1` state. Blocked: XAMPP MySQL is stopped and privileged service control is unavailable in this session.
- [ ] If the target is absent, create it without touching other databases.
- [ ] If the target contains data, inspect and back it up before any import or migration.
- [ ] Show migration/schema plan before any target schema-changing command.
- [ ] Import only after target verification and confirm row counts against the source.

### Task 5: Application verification

- [ ] Point local `.env` at verified `eduka_v1` without regenerating `APP_KEY`.
- [ ] Clear Laravel caches and verify `php artisan about`, migration status, and read-only row counts.
- [ ] Run PHPUnit, view cache, asset build, PHP lint, route list, and HTTP smoke tests.
- [ ] Record actual results and unresolved hosting/target blockers in the project documentation.
