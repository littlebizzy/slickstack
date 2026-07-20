# Permissions

SlickStack manages ownership and file modes across the server so Nginx, PHP-FPM, WordPress, WP-CLI, SFTP, cron jobs, and system services can work together without making the entire web root publicly writable.

The main permission reset is:

```bash
sudo bash /var/www/ss-perms
```

The Bash aliases `ss perms` and `ss reset perms` run the same managed workflow.

## Table of Contents

- [What ss-perms does](#what-ss-perms-does)
- [Schedule](#schedule)
- [User and group model](#user-and-group-model)
- [Core SlickStack directories](#core-slickstack-directories)
- [SlickStack scripts and cron files](#slickstack-scripts-and-cron-files)
- [WordPress permission zones](#wordpress-permission-zones)
- [Nginx, logs, and metadata](#nginx-logs-and-metadata)
- [Certificates and private credentials](#certificates-and-private-credentials)
- [PHP-FPM and WP-CLI](#php-fpm-and-wp-cli)
- [MySQL](#mysql)
- [Ubuntu and SSH files](#ubuntu-and-ssh-files)
- [Manual permission changes](#manual-permission-changes)
- [Persistent custom files](#persistent-custom-files)
- [Verification](#verification)
- [Troubleshooting](#troubleshooting)
- [Scope](#scope)

## What `ss-perms` does

`ss-perms` sources the current `ss-config` and `ss-functions`, then runs the permission scripts for:

- SlickStack directories and scripts
- Ubuntu Bash, cron, kernel, SSH, swap, users, and utilities
- MySQL
- PHP-FPM
- Nginx and certificates
- the selected CMS, normally WordPress
- Memcached
- Iptables

Each module also has its own permission script. Installers commonly run the relevant module-specific reset immediately after installing or regenerating configuration.

Running `ss-perms` is intended to be repeatable. It may recreate required directories or empty files, restore expected owners and groups, and replace manual permission changes that conflict with SlickStack policy.

## Schedule

The default setting is:

```bash
INTERVAL_SS_PERMS="half-daily"
```

Supported scheduled values are:

```text
quarter-daily
half-daily
daily
half-weekly
```

The default half-daily wrapper runs the task twice per day.

> **Current limitation:** `ss-perms` is treated as a required maintenance task. The current cron conditions consider unsupported values invalid and fall back to running it half-daily. This includes `INTERVAL_SS_PERMS="never"`, so `never` does not currently disable the permission reset.

See [Cron Jobs](cron.md) for wrapper schedules, task selection, and custom cron files.

## User and group model

SlickStack uses several accounts for different responsibilities.

| Account or group | Normal responsibility |
| :-- | :-- |
| `root` | Owns SlickStack scripts, managed configuration, system files, private keys, and protected WordPress files |
| `SUDO_USER` | Administrative SSH user that can run SlickStack commands through `sudo` |
| `SFTP_USER` | Jailed file-transfer user for approved web-root files; shell access is disabled |
| `www-data` | Nginx/PHP-FPM group and the owner of content WordPress must update at runtime |
| `slickstack` | Shared supplementary group used by SlickStack-managed users |

The sudo user, SFTP user, and `www-data` are added to the `slickstack` group. The sudo and SFTP users are also added to `www-data` where required by the managed file model.

Do not replace this structure with a single shared owner for every file. Different paths intentionally use different owners and modes.

## Core SlickStack directories

The main reset creates required directories and applies approximately the following policy:

| Path | Expected ownership | Typical mode | Purpose |
| :-- | :-- | :--: | :-- |
| `/var/www` | `root:root` | `0755` | SlickStack base directory |
| `/var/www/cache` | `www-data:www-data` | `0775` | Shared cache root |
| `/var/www/cache/opcache` | `www-data:www-data` | `0755` | OPcache-related files |
| `/var/www/crons` | `root:root` | `0755` | Managed cron wrappers |
| `/var/www/html` | `SFTP_USER:www-data` | `0775` | Production web root |
| `/var/www/logs` | `root:www-data` | `0775` | Shared service logs |
| `/var/www/meta` | `root:www-data` | `0755` | Managed metadata and credentials |

Module scripts can apply more restrictive permissions to individual files inside these directories.

## SlickStack scripts and cron files

SlickStack repeatedly enforces:

```text
/var/www/ss*                 root:root 0700
/var/www/crons/*cron*        root:root 0700
/var/www/crons/custom/*cron* root:root 0700
```

Only root or a sudo user should execute these scripts.

Although the custom cron files are the supported place for persistent custom scheduled commands, the files themselves remain root-owned and executable only through privileged cron or sudo access.

The root crontab must remain:

```text
root:root 0600
```

Cron can refuse to load it when its ownership or mode is incorrect.

## WordPress permission zones

SlickStack intentionally avoids one broad recursive `chown` or `chmod` across the complete WordPress tree. Each production, staging, or development tree is divided into policy zones.

### Web-root directory

Each enabled WordPress root uses:

```text
SFTP_USER:www-data 0775
```

This keeps the root navigable and allows approved SFTP file management.

Top-level non-PHP files normally use:

```text
SFTP_USER:www-data 0664
```

Top-level WordPress PHP files, except `wp-config.php`, normally use:

```text
www-data:www-data 0644
```

This allows WordPress Core updates without giving the SFTP user ownership of Core PHP files.

### Writable `wp-content` areas

WordPress-managed content uses `www-data:www-data`.

The following directories are created when needed and normally use directory mode `0775` and file mode `0664`:

```text
wp-content/languages
wp-content/plugins
wp-content/temp
wp-content/themes
wp-content/upgrade
wp-content/upgrade-temp-backup
wp-content/uploads
```

This supports:

- plugin and theme installation or updates
- language-pack updates
- media uploads
- temporary upgrade files
- WordPress rollback backups

The top-level `wp-content` directory also uses `www-data:www-data 0775`.

### WordPress Core directories

The managed Core directories use:

```text
wp-admin/     www-data:www-data; directories 0755; files 0644
wp-includes/  www-data:www-data; directories 0755; files 0644
```

These paths are intentionally not SFTP-owned.

### Configuration files

Sensitive generated files use read-only modes:

```text
/var/www/meta/ss-constants.php               root:www-data 0440
/var/www/html/wp-config.php                  root:www-data 0440
/var/www/html/wp-content/custom-functions.php root:www-data 0440
```

The equivalent files under enabled staging and development trees receive the same policy.

`wp-config.php` is generated from `ss-config`. Direct edits are not the supported configuration workflow and can be replaced by later SlickStack runs.

### MU plugins and object cache

Managed MU-plugin directories use `root:www-data` ownership. Their directories normally use `0755`, while files use `0640` so PHP can read them but SFTP cannot edit them.

Other protected files include:

```text
wp-content/blacklist.txt   root:www-data 0440
wp-content/object-cache.php root:www-data 0640
```

See [MU Plugins](mu-plugins.md) for the installation and update model.

### Staging uploads

Staging normally shares production media through a symbolic link:

```text
/var/www/html/staging/wp-content/uploads
```

The symlink itself is reset to `root:root`. The production uploads directory remains managed by `www-data`.

Development retains its own uploads directory unless another workflow explicitly changes it.

## Nginx, logs, and metadata

Nginx-managed configuration under `/var/www/sites` is root-owned. Directories normally use `0755` and files use `0644`.

The primary Nginx logs use:

```text
/var/www/logs/nginx-access.log root:www-data 0660
/var/www/logs/nginx-error.log  root:www-data 0660
```

The generated password file uses:

```text
/var/www/meta/.htpasswd root:www-data 0640
```

Adminer ultimately uses:

```text
/var/www/meta/adminer.php root:www-data 0640
```

The Nginx permission reset runs after the PHP-FPM reset inside `ss-perms`, so the final Nginx policy takes precedence when more than one module touches the same file.

## Certificates and private credentials

Certificate directories use:

```text
/var/www/certs/      root:root 0755
/var/www/certs/keys/ root:root 0700
```

Public certificate and DH-parameter files normally use `0644`. Private-key files use `0600`.

Sensitive generated service credentials also use root-only access:

```text
/var/www/meta/cloudflare.ini root:root 0600
/var/www/meta/rclone.conf    root:root 0600
```

Never loosen private-key or credential files to fix a web-server error. Check the Nginx configuration, certificate paths, ownership, and service logs instead.

See [SSL Certificates](ssl.md), [Cloudflare](cloudflare.md), and [Backups](backups.md).

## PHP-FPM and WP-CLI

PHP configuration is root-owned. The PHP error log is created when missing and uses:

```text
/var/www/logs/php-error.log www-data:www-data 0660
```

WP-CLI uses:

```text
/usr/local/bin/wp            root:www-data 0750
/var/www/meta/wp-cli.yml     root:www-data 0640
/var/www/meta/.wp-completion root:www-data 0640
/var/www/logs/wp-cli.log     root:www-data 0660
```

WP-CLI is intended to be run by a sudo user through SlickStack's supported commands, not directly by the jailed SFTP account.

## MySQL

For local MySQL installations, SlickStack restores root ownership on managed configuration and `mysql:mysql` ownership on runtime and log files.

The MySQL logs under `/var/www/logs/` normally use:

```text
mysql:mysql 0640
```

The runtime directory uses `mysql:mysql` ownership. The main permission reset does not broadly recurse through `/var/lib/mysql`; MySQL package and service ownership remain responsible for the database data directory.

When `DB_REMOTE="true"`, the local MySQL configuration reset skips its local-server block.

See [MySQL](mysql.md).

## Ubuntu and SSH files

Important system files include:

```text
/etc/sudoers                     root:root 0440
/etc/ssh/sshd_config             root:root 0644
/var/spool/cron/crontabs/root    root:root 0600
/var/www/auth                    root:root 0755
/var/www/auth/authorized_keys    root:root 0644
```

The centralized authorized-keys file is intentionally root-owned. Do not move or replace it without also updating the managed SSH configuration.

See [Ubuntu](ubuntu.md).

## Manual permission changes

Avoid emergency commands such as:

```bash
chmod -R 777 /var/www/html
chown -R root:root /var/www/html
chown -R www-data:www-data /var/www
```

These can:

- expose writable PHP files
- prevent SFTP access
- break WordPress updates or uploads
- make generated configuration editable by the wrong account
- stop cron jobs from loading
- expose private credentials or keys
- break service log access
- be reverted automatically by the next permission reset

Use the managed reset first:

```bash
sudo bash /var/www/ss-perms
```

Then investigate the specific path and process that still fails.

## Persistent custom files

Files placed in WordPress-managed plugin, theme, upload, or top-level web-root zones inherit SlickStack's existing policy on the next reset.

Files that must remain root-protected should be added through a supported SlickStack workflow or source change. Arbitrary permission exceptions are not currently configurable through `ss-config`.

Direct changes to module configuration, SlickStack scripts, cron wrappers, generated WordPress configuration, certificates, or metadata are likely to be overwritten.

## Verification

Useful checks include:

```bash
sudo bash /var/www/ss-perms
stat -c '%U:%G %a %n' /var/www /var/www/html /var/www/logs /var/www/meta
stat -c '%U:%G %a %n' /var/www/html/wp-config.php
stat -c '%U:%G %a %n' /var/www/html/wp-content/plugins
stat -c '%U:%G %a %n' /var/www/html/wp-content/mu-plugins
stat -c '%U:%G %a %n' /usr/local/bin/wp
stat -c '%U:%G %a %n' /etc/sudoers /etc/ssh/sshd_config
stat -c '%U:%G %a %n' /var/spool/cron/crontabs/root
```

Check group membership:

```bash
id "${SUDO_USER}"
id "${SFTP_USER}"
id www-data
```

When running those commands outside a shell that sourced `ss-config`, replace the variables with the configured usernames.

Find unexpectedly world-writable paths:

```bash
find /var/www -xdev -perm -0002 -print
```

Review the exact path before changing it. Some shared directories intentionally allow group writes, but world-writable content is not part of the normal SlickStack policy.

## Troubleshooting

### Media uploads fail

Check:

```bash
stat -c '%U:%G %a %n' /var/www/html/wp-content/uploads
sudo -u www-data test -w /var/www/html/wp-content/uploads && echo writable
```

Run `ss-perms`, then verify disk space, PHP upload limits, Nginx request limits, and whether the filesystem is mounted read-only.

### Plugin or theme updates fail

Check ownership and modes under:

```text
/var/www/html/wp-content/plugins
/var/www/html/wp-content/themes
/var/www/html/wp-content/upgrade
/var/www/html/wp-content/upgrade-temp-backup
```

These should be writable by `www-data` after the reset. Also confirm `WP_DISALLOW_FILE_MODS` is not enabled.

### SFTP cannot edit a file

Not every WordPress file is intended to be SFTP-editable.

The SFTP user normally owns the web-root directory and top-level non-PHP files, but WordPress Core PHP files, plugins, themes, MU plugins, generated configuration, and protected metadata use other owners deliberately.

Use a deployment or WordPress update workflow for protected areas rather than changing their ownership permanently.

### WP-CLI returns permission denied

Confirm `/usr/local/bin/wp` is `root:www-data 0750` and that the administrative user belongs to `www-data`. Run WP-CLI through the configured sudo account.

### Nginx or PHP cannot read a configuration file

Do not make the file world-readable immediately. Run the relevant module permission script or `ss-perms`, then check the expected group, parent-directory execute permissions, service user, and logs.

### Permission changes keep reverting

This is expected for managed paths. `ss-install`, `ss-update-modules`, module installers, cron wrappers, and the scheduled `ss-perms` task can restore the SlickStack policy.

Persistent exceptions require a source-level SlickStack change or an approved workflow rather than a one-time `chmod` or `chown` command.

## Scope

The standard SlickStack permissions model assumes:

- one primary application and domain per server
- a dedicated sudo user and jailed SFTP user
- Nginx and PHP-FPM running as `www-data`
- root-owned automation and generated configuration
- WordPress-managed plugin, theme, language, update, and upload directories
- scheduled permission reconciliation
- no shared-hosting multi-tenancy

Per-customer Unix isolation, ACL-based deployment teams, container user mapping, SELinux policy management, network filesystems, immutable files, custom umasks, arbitrary per-path exceptions, and control-panel permission schemes are outside the standard SlickStack implementation.
