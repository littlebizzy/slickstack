# Backups

SlickStack provides local database and file dump scripts plus optional remote transfer through Rclone or Rsync.

The local dump files are current working archives stored on the same server. They are useful for migrations, staging workflows, and quick recovery, but they are not a complete disaster-recovery strategy until copies are stored and retained off the server.

## Backup layers

SlickStack separates backups into three layers:

| Layer | Purpose |
| :------------- | :---------- |
| Database dumps | Logical SQL exports of production and enabled staging/development databases |
| File dumps | Compressed archives of production and enabled staging/development web files |
| Remote backup | Transfers selected local data to Rclone cloud storage or an Rsync server |

The database and files must both be available to reconstruct a normal WordPress site. A database dump alone does not contain uploads, themes, plugins, or configuration files, while a file archive alone does not contain posts, settings, users, orders, or other database content.

## Local backup paths

SlickStack stores local archives under:

```text
/var/www/backups/
```

Database dumps use:

```text
/var/www/backups/mysql/production.sql
/var/www/backups/mysql/staging.sql
/var/www/backups/mysql/development.sql
```

File dumps use:

```text
/var/www/backups/html/production.tar.gz
/var/www/backups/html/staging.tar.gz
/var/www/backups/html/development.tar.gz
```

Staging and development files are created only when those environments are enabled.

These are fixed filenames. Each successful dump replaces the previous local file, so SlickStack does not maintain timestamped generations or local retention history by default.

## Database dumps

To create the database dumps manually, run:

```bash
sudo bash /var/www/ss-dump-database
```

The script uses `mysqldump` to export the configured production database. It also exports the fixed `staging` and `development` databases when those sites are enabled.

For a local database, SlickStack authenticates with its managed administrative database account. For a remote database, it attempts the dump using the configured WordPress database user over TCP.

Remote providers may restrict the privileges required by `mysqldump`, so remote database dumps should be tested rather than assumed to work.

The generated SQL files are assigned to the configured SFTP user and the `www-data` group with mode `0440`. They can be downloaded through SFTP but are not exposed through the public web root.

## File dumps

To create the file archives manually, run:

```bash
sudo bash /var/www/ss-dump-files
```

The production archive contains `/var/www/html/` while excluding its `staging` and `dev` directories. When enabled, staging and development are archived separately.

The generated archives are assigned to the configured SFTP user and the `www-data` group with mode `0440`.

File dumps are disabled by default because large WordPress directories can consume substantial CPU, disk I/O, and temporary disk space:

```bash
INTERVAL_SS_DUMP_FILES="never"
```

The current file dump script does not preflight available disk space before creating the archive. Confirm sufficient free space before enabling scheduled file dumps on large sites.

## Scheduling local dumps

Backup scheduling is controlled in `ss-config`:

```bash
INTERVAL_SS_DUMP_DATABASE="hourly"
INTERVAL_SS_DUMP_FILES="never"
```

Supported database dump intervals are:

```text
half-hourly
hourly
quarter-daily
half-daily
daily
never
```

Supported file dump intervals are:

```text
hourly
quarter-daily
half-daily
daily
never
```

The default configuration creates an hourly database dump and does not schedule file dumps.

Because each local dump overwrites the previous file, increasing the frequency improves the freshness of the single local snapshot but does not create additional recovery points.

## Database restores

`ss-import-database` restores the configured production database from one of these files:

```text
/tmp/import.sql
/tmp/import.sql.zip
/tmp/import.sql.gz
```

Run:

```bash
sudo bash /var/www/ss-import-database
```

The script:

1. creates a fresh production database dump
2. asks for interactive confirmation
3. validates supported compressed files
4. streams or imports the SQL into the configured production database

This operation overwrites production database content. Verify the source file and the newly created safety dump before confirming the import.

The managed database import does not separately restore the staging or development databases.

## File restores

`ss-import-files` accepts:

```text
/tmp/import.tar
/tmp/import.tar.gz
```

Run:

```bash
sudo bash /var/www/ss-import-files
```

The script asks for confirmation and extracts the archive over `/var/www/html/`, stripping the archive's first path component. It then regenerates the WordPress configuration and restores managed WordPress permissions.

Unlike the database import, the current file import script does not automatically create a fresh file dump before extraction. Create and verify a file backup manually before running it.

The file import targets the production HTML directory. It does not provide separate staging or development restore selection.

## Remote backups

Remote transfers are disabled by default:

```bash
SS_REMOTE_BACKUPS="none"
INTERVAL_SS_REMOTE_BACKUP="never"
```

Supported remote methods are:

```text
rclone
rsync
```

Supported remote backup intervals are:

```text
half-daily
daily
half-weekly
weekly
never
```

To run the configured remote workflow manually:

```bash
sudo bash /var/www/ss-remote-backup
```

Configure only the intended method and leave the unused method's user credentials empty. The current script can run a branch when its user field is populated even if another value is selected in `SS_REMOTE_BACKUPS`.

## Rclone backups

The intended Rclone workflow transfers the local backup directory to cloud storage:

```bash
SS_REMOTE_BACKUPS="rclone"
RCLONE_TYPE="b2"
RCLONE_MODE="copy"
RCLONE_BACKUP_PATH="/var/www/backups/"
RCLONE_REMOTE_PATH="/bucketname/example.com"
RCLONE_PARALLEL_TRANSFERS="15"
```

SlickStack currently documents Backblaze B2 as the supported Rclone provider and creates one Rclone remote named:

```text
slickstack
```

The generated credentials file is stored at:

```text
/var/www/meta/rclone.conf
```

It is owned by `root` with mode `0600`.

The default `copy` mode uploads new and changed files without making the destination an exact mirror. A manually selected `sync` mode can remove remote files that no longer exist locally, so provider-side versioning or lifecycle rules should be configured before using it.

Because SlickStack's local dump filenames are replaced in place, long-term recovery history depends on the remote provider's versioning, lifecycle, or snapshot features. SlickStack does not create dated remote generations itself.

High `RCLONE_PARALLEL_TRANSFERS` values can consume significant CPU and memory, especially with large files. The default value of `15` is safer for typical virtual servers.

## Rsync backups

The Rsync workflow currently sends the production HTML directory over SSH:

```bash
SS_REMOTE_BACKUPS="rsync"
RSYNC_REMOTE_HOST="example.rsync.net"
RSYNC_USER="example"
RSYNC_PASSWORD="example-password"
RSYNC_PORT="22"
```

The current script transfers:

```text
/var/www/html/
```

to a remote path structured like:

```text
slickstack/example.com/html/
```

Although `RSYNC_BACKUP_PATH` exists in `ss-config`, the current remote backup script does not read it and keeps `/var/www/html/` hardcoded.

The Rsync workflow therefore does not upload the local SQL dumps under `/var/www/backups/mysql/`. A separate database backup method is required for a complete remote recovery set.

The current implementation uses password-based SSH through `sshpass` and disables strict SSH host-key checking. Administrators requiring key-based authentication or pinned host verification should use their own backup workflow instead.

## Encryption and credentials

Local SQL dumps and tar archives are not encrypted by SlickStack. Anyone with sufficient server or SFTP access to those files can read their contents.

Rclone credentials are stored in the root-only `rclone.conf`. Rsync credentials remain in the root-controlled `ss-config` and are passed to `sshpass` during transfer.

Remote transport security and storage encryption depend on the chosen provider and protocol. Sensitive sites should use a provider with encryption, account protections, restricted credentials, and retention controls.

## Retention and verification

SlickStack does not currently provide:

- timestamped local backup generations
- automatic local retention or rotation
- incremental or deduplicated archives
- encrypted local archives
- backup success notifications
- automated restore verification
- provider lifecycle configuration
- full server image or block-device snapshots

A practical backup plan should retain multiple off-server recovery points and periodically test both database and file restoration.

At minimum, verify that:

- the local database dump is non-empty and recent
- the file archive opens successfully
- the remote destination contains the expected files
- remote retention preserves older recovery points
- credentials have only the permissions they require
- a test restoration can produce a working WordPress site

## Recommended baseline

A reasonable baseline for a typical production site is:

```bash
INTERVAL_SS_DUMP_DATABASE="hourly"
INTERVAL_SS_DUMP_FILES="daily"
SS_REMOTE_BACKUPS="rclone"
INTERVAL_SS_REMOTE_BACKUP="daily"
RCLONE_MODE="copy"
RCLONE_BACKUP_PATH="/var/www/backups/"
```

The remote provider should then retain historical versions independently of SlickStack. File dump frequency may need to be reduced for large sites or low-resource servers.

## Scope

The standard SlickStack backup design assumes:

- one current local SQL dump per enabled environment
- one current local file archive per enabled environment
- manual production database and file imports
- one configured Rclone remote or one Rsync destination
- provider-managed retention for historical backups

Continuous replication, immutable backup repositories, encrypted archive creation, incremental backups, snapshot orchestration, automated disaster recovery, multi-destination replication, and managed retention policies are outside the standard SlickStack workflow.