# Commands

SlickStack installs an `ss` Bash function that provides shorter aliases for commonly used scripts under `/var/www/`.

For example:

```bash
ss check
```

runs:

```bash
sudo bash /var/www/ss-check
```

The aliases are conveniences, not a separate command-line application. The underlying scripts remain the clearest and most stable way to understand exactly what will run.

## Table of Contents

- [Loading the aliases](#loading-the-aliases)
- [Recommended core commands](#recommended-core-commands)
- [Script refreshes and updates](#script-refreshes-and-updates)
- [Full installation and narrow installers](#full-installation-and-narrow-installers)
- [Status and monitoring](#status-and-monitoring)
- [Cache commands](#cache-commands)
- [Service restarts](#service-restarts)
- [Backups and database operations](#backups-and-database-operations)
- [Staging and development](#staging-and-development)
- [Maintenance mode](#maintenance-mode)
- [SSL commands](#ssl-commands)
- [Configuration shortcuts](#configuration-shortcuts)
- [Cron wrappers](#cron-wrappers)
- [Logs](#logs)
- [Permissions and passwords](#permissions-and-passwords)
- [Rebooting](#rebooting)
- [Worker command](#worker-command)
- [Historical and inconsistent aliases](#historical-and-inconsistent-aliases)
- [Safe command workflow](#safe-command-workflow)
- [Source of truth](#source-of-truth)

## Loading the aliases

The managed alias function is stored in:

```text
/var/www/meta/.bashrc
```

and sourced by the managed Bash configuration for the root, sudo, and SFTP users.

After SlickStack updates or reinstalls the Bash configuration, open a new shell session or reload the current user's Bash configuration:

```bash
source ~/.bashrc
```

Confirm that the function is available:

```bash
type ss
```

The current implementation compares the complete argument string against hardcoded values. It does not provide normal subcommand parsing, flags, autocomplete, validation, or a built-in help screen.

An unknown command currently exits silently without an error:

```bash
ss something-unknown
```

When precision matters, run the underlying script directly.

## Recommended core commands

| Command | Underlying action | Scope |
| :-- | :-- | :-- |
| `ss check` | `/var/www/ss-check` | Refreshes SlickStack scripts and cron wrappers |
| `ss install` | `/var/www/ss-install` | Reconciles the complete managed stack |
| `ss update config` | `/var/www/ss-update-config` | Migrates `ss-config` to the current schema |
| `ss update modules` | `/var/www/ss-update-modules` | Upgrades Ubuntu packages and the kernel |
| `ss status` | `/var/www/ss-stack-overview` | Displays the stack overview |
| `ss monitor` | `/var/www/ss-monitor-resources` | Opens the interactive resource monitor |
| `ss perms` | `/var/www/ss-perms` | Reapplies the complete managed permission policy |
| `ss purge` | Four cache purge scripts | Clears all SlickStack origin cache layers |
| `ss restart` | Five service restart scripts | Restarts the current main stack services |
| `ss dump database` | `/var/www/ss-dump-database` | Creates local database dumps |
| `ss dump files` | `/var/www/ss-dump-files` | Creates a local files backup |
| `ss backup` | `/var/www/ss-remote-backup` | Runs the configured remote backup method |
| `ss maintenance on` | `/var/www/ss-maintenance-enable` | Enables the production maintenance response |
| `ss maintenance off` | `/var/www/ss-maintenance-disable` | Disables the production maintenance response |
| `ss sync staging` | `/var/www/ss-sync-staging` | Copies production state into staging when allowed |
| `ss sync development` | `/var/www/ss-sync-development` | Copies production state into development when allowed |

Read the component guide before using a command on production. A short alias does not imply a small or reversible operation.

## Script refreshes and updates

### Refresh SlickStack scripts

Use:

```bash
ss check
```

Equivalent:

```bash
sudo bash /var/www/ss-check
```

This refreshes SlickStack-managed scripts and cron wrappers. It is not an Ubuntu package upgrade and does not reconcile the entire stack.

### Migrate `ss-config`

Use:

```bash
ss update config
```

Equivalent:

```bash
sudo bash /var/www/ss-update-config
```

Review the resulting configuration and backup before running a full installation.

### Upgrade Ubuntu packages

Use:

```bash
ss update modules
```

Equivalent:

```bash
sudo bash /var/www/ss-update-modules
```

The aliases `ss update packages`, `ss upgrade modules`, and `ss upgrade packages` currently invoke the same script.

Package upgrades can restart services indirectly or install a kernel that requires a later reboot.

### Avoid the compound `ss update` shortcut

The plain command:

```bash
ss update
```

currently performs a broad compound workflow:

1. runs `ss-check`
2. runs `ss-worker`
3. repeats `ss-check`
4. migrates `ss-config`
5. repeats worker and check operations
6. reinstalls the root crontab
7. upgrades Ubuntu packages and the kernel
8. restarts PHP-FPM, Nginx, MySQL, and Memcached

This is much broader than its name suggests. Prefer the narrow commands individually so each change is deliberate and easier to troubleshoot.

The plain command:

```bash
ss upgrade
```

is not equivalent to `ss update`. It currently runs only `ss-update-config` followed by `ss-update-modules`.

See [Updates](updates.md), [SS-Config](ss-config.md), and [Installation](installation.md).

## Full installation and narrow installers

Run the complete installer with:

```bash
ss install
```

or:

```bash
sudo bash /var/www/ss-install
```

A full installation upgrades packages, rewrites managed configuration, may synchronize staging, truncates SlickStack logs, resets permissions, purges caches, and restarts services.

Use a narrow installer when only one managed component needs reconciliation:

| Goal | Recommended direct command |
| :-- | :-- |
| Rebuild Nginx configuration | `sudo bash /var/www/ss-install-nginx-config` |
| Rebuild PHP configuration | `sudo bash /var/www/ss-install-php-config` |
| Rebuild WordPress configuration | `sudo bash /var/www/ss-install-wordpress-config` |
| Refresh WordPress packages | `sudo bash /var/www/ss-install-wordpress-packages` |
| Refresh WordPress MU plugins | `sudo bash /var/www/ss-install-wordpress-mu-plugins` |
| Rebuild MySQL configuration | `sudo bash /var/www/ss-install-mysql-config` |
| Rebuild Memcached configuration | `sudo bash /var/www/ss-install-memcached-config` |
| Rebuild Iptables configuration | `sudo bash /var/www/ss-install-iptables-config` |
| Repair the root crontab | `sudo bash /var/www/ss-install-ubuntu-crontab` |
| Rebuild SSH configuration | `sudo bash /var/www/ss-install-ubuntu-ssh` |

Some matching `ss install ...` aliases exist, but direct script names are clearer and avoid confusion with historical aliases that remain in the Bash template.

See [Installation](installation.md).

## Status and monitoring

These commands all invoke the stack overview:

```bash
ss status
ss overview
ss info
ss summary
```

The overview can reveal usernames, passwords, database credentials, private URLs, and key material. Do not paste complete output into a public issue or support channel.

For an interactive resource view:

```bash
ss monitor
```

Equivalent aliases include:

```bash
ss monitor resources
ss monitor server
```

Basic service shortcuts include:

```bash
ss status nginx
ss status php
```

For more precise service checks, use systemd directly:

```bash
systemctl status nginx --no-pager
systemctl status mysql --no-pager
systemctl status memcached --no-pager
systemctl list-units --type=service --all | grep -E 'php.*fpm'
```

See [Monitoring](monitoring.md).

## Cache commands

Clear all SlickStack origin cache layers with:

```bash
ss purge
```

The alias currently runs these scripts in order:

1. `ss-purge-opcache`
2. `ss-purge-memcached`
3. `ss-purge-nginx`
4. `ss-purge-transients`

Individual commands are:

```bash
ss purge opcache
ss purge memcached
ss purge nginx
ss purge transients
```

These do not purge browser caches or Cloudflare edge caches.

See [Caching](caching.md).

## Service restarts

Restart the main local services with:

```bash
ss restart
```

The alias currently restarts:

1. Fail2ban
2. Memcached
3. MySQL
4. PHP-FPM
5. Nginx

Individual aliases are:

```bash
ss restart fail2ban
ss restart memcached
ss restart mysql
ss restart php
ss restart nginx
```

Restart only the service that requires it. A restart interrupts active work and can expose invalid configuration.

Validate configuration first where appropriate:

```bash
sudo nginx -t
sudo sshd -t
```

Fail2ban remains transitional and is planned for removal from SlickStack.

## Backups and database operations

### Local dumps

Create database dumps:

```bash
ss dump database
```

The shorter alias also works:

```bash
ss dump db
```

Create a files backup:

```bash
ss dump files
```

### Remote backups

Run the configured remote backup workflow:

```bash
ss backup
```

Equivalent:

```bash
ss remote backup
```

This invokes `ss-remote-backup`; it is not a replacement for verifying fresh local dumps and off-server restore capability.

### Imports

These commands can overwrite active content:

```bash
ss import database
ss import db
ss import files
```

Review the expected backup paths, preserve the current production state, and use a maintenance window before importing.

### Cleaning and optimization

Available aliases include:

```bash
ss clean database
ss clean db
ss clean files
ss optimize
ss optimize database
ss optimize db
ss optimize files
```

Cleaning and optimization can alter files or database rows. Review the underlying script before using them on production.

### Deletion

These are destructive commands:

```bash
ss delete database
ss delete db
ss delete files
ss delete html
```

Do not run them as routine troubleshooting steps.

See [Backups](backups.md) and [MySQL](mysql.md).

## Staging and development

Copy production into staging:

```bash
ss sync staging
```

Copy production into development:

```bash
ss sync development
```

The shorter development form also works:

```bash
ss sync dev
```

Reverse push commands overwrite the production database and selected files:

```bash
ss push staging
ss push development
ss push dev
```

`ss push development` is marked experimental by its script. Neither push command merges newer production records or provides automatic rollback.

See [Staging & Dev](staging-dev.md).

## Maintenance mode

Use:

```bash
ss maintenance on
ss maintenance off
```

Equivalent forms include:

```bash
ss maintenance enable
ss maintenance disable
ss main on
ss main off
ss main enable
ss main disable
```

The production maintenance switch is file-based and purges Nginx cache when it changes state.

## SSL commands

Generate or renew the default OpenSSL certificate:

```bash
ss encrypt openssl
```

Run the Certbot workflow:

```bash
ss certbot
```

or:

```bash
ss encrypt certbot
ss encrypt letsencrypt
```

Avoid the generic command:

```bash
ss encrypt
```

It currently runs both the OpenSSL and Certbot scripts sequentially, regardless of which certificate type is intended.

Inspect the current SlickStack OpenSSL certificate with:

```bash
ss test openssl
```

Changing the selected certificate type through an alias only edits `ss-config`:

```bash
ss config openssl
ss config certbot
ss config letsencrypt
```

A related installer must still regenerate and activate the managed Nginx configuration.

See [SSL (Certs)](ssl.md) and [Cloudflare](cloudflare.md).

## Configuration shortcuts

The alias map contains shortcuts that modify selected `ss-config` lines with `sed`, including staging, development, synchronization, and certificate settings.

Examples include:

```bash
ss config enable staging
ss config disable staging
ss config enable staging sync
ss config disable staging sync
ss config enable dev
ss config disable dev
ss config enable dev sync
ss config disable dev sync
```

These shortcuts:

- do not validate the complete file
- do not create a backup
- do not migrate the schema
- do not run `ss-install`
- do not confirm that the requested setting exists exactly once
- can become stale when the schema changes

Prefer editing the file deliberately:

```bash
sudo nano /var/www/ss-config
sudo bash -n /var/www/ss-config
```

Then run the appropriate installer.

> **Current bug:** `ss config disable dev site sync` writes `SS_SYNC_DEVELOPMENT="true"` and therefore enables development synchronization instead of disabling it. Do not use that alias.

See [SS-Config](ss-config.md).

## Cron wrappers

Run a standard cron wrapper manually with either its number or interval name:

```bash
ss cron 01
ss cron minutely
ss cron 06
ss cron hourly
ss cron 09
ss cron daily
```

The supported wrapper numbers are `01` through `14`.

Run the reserved custom wrapper with:

```bash
ss cron custom 01
ss cron custom minutely
ss cron custom 09
ss cron custom daily
```

Manual execution runs every task currently assigned to that wrapper. It is not a dry run.

See [Cron Jobs](cron.md).

## Logs

Follow common logs with:

```bash
ss tail nginx access log
ss tail nginx error log
ss tail php error log
ss tail fail2ban log
```

The first three read SlickStack-managed logs under `/var/www/logs/`. The Fail2ban command reads `/var/log/fail2ban.log`.

Clear SlickStack-managed logs with:

```bash
ss empty logs
```

The alias `ss null logs` performs the same destructive operation. The script truncates managed `.log` files without archiving them first.

Preserve relevant logs before troubleshooting an outage or suspected compromise.

See [Logging](logging.md).

## Permissions and passwords

Reapply the complete SlickStack permission policy:

```bash
ss perms
```

Use the full command only when broad reconciliation is intended. Narrow permission scripts are safer when one component is affected, for example:

```bash
sudo bash /var/www/ss-perms-wordpress-packages
sudo bash /var/www/ss-perms-wordpress-config
sudo bash /var/www/ss-perms-nginx-config
sudo bash /var/www/ss-perms-php-config
```

Reset the SFTP password with:

```bash
ss reset sftp password
```

Equivalent word-order aliases also exist, but the form above is the clearest.

See [Permissions](permissions.md) and [Ubuntu](ubuntu.md).

## Rebooting

Use SlickStack's reboot wrapper with:

```bash
ss reboot
```

Equivalent:

```bash
sudo bash /var/www/ss-reboot-machine
```

The wrapper uses SlickStack's configured reboot behavior rather than calling `reboot` directly. Keep provider-console access available and verify services after the machine returns.

## Worker command

The advanced command:

```bash
ss worker
```

runs:

```bash
sudo bash /var/www/ss-worker
```

The worker can retrieve and source a configured pilot file as root. Use it only when the pilot-file trust and fleet-management implications are fully understood.

See [SS-Config](ss-config.md) and [Security](security.md).

## Historical and inconsistent aliases

The active Bash template contains more aliases than SlickStack's current supported WordPress stack requires. Some remain from older experiments or unsupported application modules.

Do not treat the presence of an alias as proof that the referenced script is installed, current, or supported.

Notable current issues include:

- unknown commands silently do nothing
- `ss update` and `ss upgrade` have different scopes
- `ss config disable dev site sync` enables the setting instead
- `ss encrypt` runs both certificate workflows
- `ss private` passes the literal command arguments to `mv` and should not be relied on
- `ss kernel list` runs `sysctl -a`, despite its misleading name
- old aliases remain for Craft CMS, MediaWiki, Moodle, OpenCart, PrestaShop, Hovercraft, ClamAV, and other archived or unsupported modules
- `ss install adminer` references an older standalone installer model, while active Adminer installation is integrated into PHP and Nginx configuration
- duplicate and alternative word-order aliases increase the chance of documentation drift

Use the curated commands in this guide or run a verified `/var/www/ss-*` script directly.

## Safe command workflow

Before a production command:

1. identify the exact underlying script
2. read its header and current implementation
3. confirm the source and destination environments
4. review `ss-config`
5. preserve relevant logs
6. create and verify backups when data can change
7. validate available disk space and service configuration
8. use a maintenance window when availability may be affected
9. verify the result instead of relying only on a success message or timestamp

Useful checks include:

```bash
type ss
sudo bash -n /var/www/ss-config
systemctl --failed
sudo nginx -t
df -h /
```

## Source of truth

The active alias implementation is generated from:

```text
modules/ubuntu/bashrc.txt
```

and installed as:

```text
/var/www/meta/.bashrc
```

The aliases can change after `ss-check` or a Bash configuration reinstall. Component scripts and their dedicated guides remain the source of truth for actual behavior.
