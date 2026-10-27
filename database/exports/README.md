# EDUKA MySQL/XAMPP exports

These files were generated from the verified local MySQL database
`eduka_rebuild` after a non-empty SQL backup was created. They target only the
database name `eduka_v1`; they do not contain application `.env` values,
database credentials, or plaintext passwords. User password hashes are
preserved in the full data export so existing local accounts can continue to
log in.

## Files

- `create_eduka_v1.sql` creates `eduka_v1` with `utf8mb4` and does not drop
  anything.
- `eduka_v1_schema.sql` contains the 20 migration tables and their keys,
  indexes, constraints, and column definitions. It is generated without
  `DROP TABLE` statements.
- `eduka_v1_full.sql` contains the same schema plus verified rows from
  `eduka_rebuild`. It is intended for an empty, newly created `eduka_v1`.
  It does not drop existing tables; if a non-empty target conflicts, the
  import must stop and the target must be inspected rather than overwritten.

Private assignment files are not stored in SQL. Back up the application's
private storage directory separately if those files must be preserved.

## Import through phpMyAdmin

1. Start the XAMPP MySQL/MariaDB service and verify its actual host and port.
2. Create `eduka_v1` by importing `create_eduka_v1.sql`, or create it from
   phpMyAdmin with `utf8mb4_unicode_ci`.
3. Confirm the target is empty before using `eduka_v1_full.sql`. Do not select
   an existing application database and do not enable an option that drops or
   overwrites tables.
4. Import `eduka_v1_full.sql` into `eduka_v1`.
5. Verify the `migrations` table contains all 20 migration names and compare
   row counts with the source backup before changing the Laravel `.env`.

For a schema-only installation, import `eduka_v1_schema.sql`, then run only
the explicitly approved data-import procedure. Do not run a demo seeder when
preserving existing data.

## Backup and restore

The source backup used for this export is outside the repository at
`/home/aslam/Desktop/eduka_rebuild_backup_20261027_2148.sql`. Keep it until
the target has been independently verified. A future backup should use
`mysqldump --single-transaction` against the verified host/port and must be
checked for a non-zero size and readable SQL before any target change.

Never use `migrate:fresh`, `db:wipe`, `DROP DATABASE`, `TRUNCATE TABLE`, or a
destructive SQL import for this project.
