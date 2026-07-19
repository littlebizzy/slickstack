# MySQL

SlickStack uses MySQL as the primary database service for WordPress production, staging, and development environments.

A local MySQL server is installed and managed by default. Remote MySQL servers are also supported, but SlickStack does not install, tune, restart, or monitor the remote service.

## Table of Contents

- [MySQL versions](#mysql-versions)
- [Database settings](#database-settings)
- [Local database mode](#local-database-mode)
- [Remote database mode](#remote-database-mode)
- [Managed configuration](#managed-configuration)
- [MySQL and InnoDB tuning](#mysql-and-innodb-tuning)
- [Installation commands](#installation-commands)
- [Database dumps](#database-dumps)
- [Database imports](#database-imports)
- [Database optimization](#database-optimization)
- [Restarts and recovery](#restarts-and-recovery)
- [Troubleshooting](#troubleshooting)
- [Scope](#scope)

## MySQL versions

SlickStack selects the MySQL package version from the detected Ubuntu LTS release:

| Ubuntu | MySQL |
| :------------- | :----------: |
| 24.04 | 8.0 |
| 22.04 | 8.0 |
| 20.04 | 8.0 |
| 18.04 | 5.7 |

When `DB_REMOTE="false"`, SlickStack installs the matching MySQL server package. When `DB_REMOTE="true"`, it installs only the matching MySQL client package.

## Database settings

The main database settings are stored in `ss-config`:

```bash
DB_REMOTE="false"
DB_HOST="127.0.0.1"
DB_PORT="3306"
DB_NAME="production"
DB_PREFIX="wp_"
DB_USER="example"
DB_PASSWORD="example-password"
DB_PASSWORD_ROOT="different-admin-password"
DB_CHARSET="utf8mb4"
DB_COLLATE=""
```

`DB_USER` and `DB_PASSWORD` are used by WordPress. `DB_PASSWORD_ROOT` is used for the separate administrative database account and should not match the WordPress database password.

After changing database settings, run the main installer so SlickStack can regenerate the dependent MySQL and WordPress configuration.

## Local database mode

With the default local database mode, WordPress connects over TCP using the configured host and port, normally:

```text
127.0.0.1:3306
```

SlickStack creates the production database using `DB_NAME`. When enabled, the staging and development databases use the fixed names:

```text
staging
development
```

The installer creates a limited WordPress database user and grants it access to the required WordPress databases. It also creates an administrative user for database management and retains local socket authentication for root and the SlickStack sudo user.

On current MySQL 8.0 installations, the managed TCP users use `caching_sha2_password` authentication.

## Remote database mode

Set the following to use an external MySQL provider:

```bash
DB_REMOTE="true"
DB_HOST="database.example.com"
DB_PORT="3306"
```

In remote mode, SlickStack:

- skips installation of the local MySQL server
- installs the matching MySQL client
- skips the managed local `my.cnf`
- skips local database and user creation
- skips the local MySQL watchdog

The remote provider must supply the database, users, permissions, networking, encryption, backups, tuning, monitoring, and service recovery.

Some SlickStack database utilities attempt to support remote databases through the configured WordPress user, but provider permissions or networking restrictions may prevent dumps, imports, staging, development, or synchronization features from working exactly like a local database.

## Managed configuration

For a local database, SlickStack installs and manages:

```text
/etc/mysql/my.cnf
```

`ss-install-mysql-config` retrieves the version-matched boilerplate, applies supported `ss-config` values, installs the generated file, restarts MySQL, and resets permissions.

Direct edits to `/etc/mysql/my.cnf` may be replaced the next time `ss-install` or `ss-install-mysql-config` runs. Persistent changes should use supported `ss-config` variables whenever possible.

The managed file disables the normal MySQL include directories so settings from `/etc/mysql/conf.d/` and `/etc/mysql/mysql.conf.d/` do not silently override SlickStack’s configuration.

## MySQL and InnoDB tuning

Supported local MySQL settings include:

- SQL mode
- maximum packet size
- connection, idle, interactive, read, and write timeouts
- InnoDB buffer pool size
- InnoDB log file size and log file count
- transaction log flush behavior
- InnoDB flush method

When `INNODB_BUFFER_POOL_SIZE="auto"`, SlickStack calculates the buffer pool as approximately 50% of detected system RAM during configuration installation.

The standard MySQL 8.0 configuration also:

- uses InnoDB as the default storage engine
- disables DNS hostname resolution for connections
- disables external locking
- disables binary logging
- uses `caching_sha2_password` as the default authentication plugin
- configures liberal packet and timeout values for WordPress migrations, staging, and database dumps

Binary logging, replication, and point-in-time recovery are therefore not part of the standard SlickStack database design.

## Installation commands

The main MySQL management commands are:

```bash
sudo bash /var/www/ss-install-mysql-packages
sudo bash /var/www/ss-install-mysql-config
sudo bash /var/www/ss-install-mysql-database
sudo bash /var/www/ss-restart-mysql
```

The package installer installs either the local server or remote client based on `DB_REMOTE`. The database installer creates the local databases, users, and privileges when local mode is enabled.

## Database dumps

To create database dumps, run:

```bash
sudo bash /var/www/ss-dump-database
```

SlickStack writes dumps under:

```text
/var/www/backups/mysql/
```

The production database is always dumped. Staging and development databases are also dumped when those environments are enabled.

Dump files are assigned to the configured SFTP user and the `www-data` group with read-only permissions so they can be retrieved without exposing them publicly.

Database dumps are logical backups created with `mysqldump`; SlickStack does not copy the live MySQL data directory as a backup method.

## Database imports

`ss-import-database` overwrites the configured production database using one of these files:

```text
/tmp/import.sql
/tmp/import.sql.zip
/tmp/import.sql.gz
```

Run:

```bash
sudo bash /var/www/ss-import-database
```

The script first creates a fresh database dump, asks for interactive confirmation, validates compressed files, and then imports the selected file into `DB_NAME`.

This is a destructive production database replacement. The import files and database dump should be verified before confirming the operation.

## Database optimization

To run the managed database optimization workflow:

```bash
sudo bash /var/www/ss-optimize-database
```

The script creates a fresh database dump before making changes, converts remaining MyISAM tables in the production database to InnoDB, and purges Memcached afterward so WordPress does not retain stale cached objects.

This command modifies database tables and should be run manually or on a conservative schedule rather than during peak traffic.

## Restarts and recovery

To forcefully restart the local MySQL service, run:

```bash
sudo bash /var/www/ss-restart-mysql
```

The SlickStack cron that runs every two minutes performs a local socket ping with a five-second timeout. A successful ping clears the failure counter. After three consecutive failed checks, SlickStack restarts MySQL and resets the counter.

This watchdog is disabled when `DB_REMOTE="true"`. It tests actual local MySQL responsiveness rather than relying only on the systemd service state.

## Troubleshooting

Useful local checks include:

```bash
systemctl status mysql
mysqladmin --user=root --host=localhost --protocol=socket ping
sudo mysql
```

For WordPress connection problems, verify that:

- `DB_REMOTE`, `DB_HOST`, and `DB_PORT` identify the intended server
- `DB_NAME`, `DB_USER`, and `DB_PASSWORD` match the database account
- the database user has privileges on the production database
- the MySQL service is running in local mode
- network and provider access rules allow the connection in remote mode
- the generated WordPress configuration matches the current `ss-config`

Re-running the managed package, configuration, and database installers is safer than manually recreating SlickStack-managed local users or configuration files.

## Scope

The standard SlickStack database design assumes:

- one local MySQL server or one externally managed remote server
- one production database with optional staging and development databases
- one WordPress database user shared across enabled local environments
- local administrative access through socket-authenticated users
- InnoDB as the primary storage engine
- logical SQL dumps for database backups

MySQL replication, clustering, load balancing, automatic failover, binary log management, point-in-time recovery, encrypted remote database configuration, and multiple database servers are outside the standard managed configuration.