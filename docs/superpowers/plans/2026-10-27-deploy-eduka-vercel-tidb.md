# EDUKA Vercel/TiDB Deployment Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to execute this plan task-by-task.

**Goal:** Deploy EDUKA to a verified Vercel project with a verified TiDB Cloud application schema and durable private assignment storage, then prove the production URL works.

**Architecture:** Vercel serves Laravel through the PHP community runtime and `api/index.php`; TiDB Cloud is the MySQL-compatible application database; database-backed session/cache and private S3-compatible object storage provide persistence across serverless invocations.

**Tech Stack:** Laravel 13, PHP 8.3, Vercel CLI/community PHP runtime, TiDB Cloud MySQL protocol, Composer, Vite, Flysystem S3.

**Spec:** `/home/aslam/.codex/attachments/f1574177-18fe-4646-a4d7-d564fa7897dc/Pasted text.txt`

## Global Constraints

- Never use the TiDB `sys` schema as the application database.
- Never print, commit, or transmit passwords, private keys, or storage secrets.
- Never reset, wipe, or import development data without explicit approval.
- Do not deploy until a real HTTPS URL and production status can be checked.
- Use Preview before Production and keep other Vercel projects untouched.
- Use a separate testing database and preserve `eduka_rebuild` and `eduka_rebuild_testing`.

## Review Focus

- Unlinked/uppercase local directory: verify the exact Vercel project rather than deriving a guessed project name.
- TiDB schema and TLS: reject `sys`, verify the application schema, CA, and certificate validation before migration.
- Serverless persistence: verify database session/cache and private S3 upload/download rather than local filesystem writes.
- Production secrets: confirm values are configured through Vercel settings without exposing them in logs or repository files.
- Feature integrity: smoke-test authentication, role authorization, assignments, certificates, and private-file access through the real URL.

### Task 1: Audit repository and platform targets

**Files:**
- Read: `vercel.json`, `api/index.php`, `.vercelignore`, `composer.json`, `package.json`, `.env.example`
- Read: `docs/DEPLOYMENT.md`, `docs/PRODUCTION-READINESS.md`

- [ ] Verify Git branch, remote, commit, Vercel account, project list, and repository availability without exposing secrets.
- [ ] Verify the Vercel project slug and repository/branch before linking.
- [ ] Verify the PHP runtime, build command, routes, and static asset rules.

### Task 2: Link or create the exact Vercel project

**Files:**
- Modify only local Vercel link metadata when the exact project is confirmed.

- [ ] Link the confirmed project with `vercel link` or create the explicitly approved slug.
- [ ] Deploy Preview and inspect the build/function logs.
- [ ] Fix only verified build/runtime failures, then repeat Preview.

### Task 3: Verify TiDB and persistent storage prerequisites

**Files:**
- Modify: Vercel Project Settings only after the schema, rotated credential, CA, and bucket are confirmed.

- [ ] Confirm a non-system TiDB application schema and restricted user.
- [ ] Confirm TLS CA and server verification.
- [ ] Confirm private S3-compatible bucket, region/endpoint, and credentials.
- [ ] Stop and request the missing operator inputs if any prerequisite is unavailable.

### Task 4: Migrate only the verified cloud schema

**Files:**
- Read: `database/migrations/*.php`

- [ ] Show the verified target and migration summary without passwords.
- [ ] Obtain explicit approval before `php artisan migrate --force` if the cloud schema is not already approved.
- [ ] Run only additive/safe migrations; never run `migrate:fresh`, `db:wipe`, or demo seeding.

### Task 5: Production verification and handoff

**Files:**
- Modify: `docs/DEPLOYMENT.md`, `docs/PRODUCTION-READINESS.md`, `docs/PROJECT-STATUS.md`, `docs/FUNCTIONAL-AUDIT.md`

- [ ] Verify the production URL, status, commit, logs, database connectivity, login/session, all roles, enrollment, quiz, assignment upload/grading, certificate PDF, private-file authorization, and persistence.
- [ ] Record verified, configured-but-unverified, and blocked items with actual evidence.
- [ ] Do not claim production readiness unless all required checks pass.
