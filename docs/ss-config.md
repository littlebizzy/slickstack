# SS-Config

`/var/www/ss-config` is SlickStack's central user-editable configuration file. It stores installation choices, credentials, domains, service settings, maintenance intervals, and other values used by SlickStack's Bash scripts.

`ss-config` is the source of truth for user-selectable policy, but it is not intended to expose every low-level value. SlickStack installation scripts also calculate and hardcode derived settings when automatic tuning is safer and more predictable than another user option.

## Table of Contents

- [File locations](#file-locations)
- [Bash syntax and security](#bash-syntax-and-security)
- [Configuration lifecycle](#configuration-lifecycle)
- [Initial setup](#initial-setup)
- [Build compatibility](#build-compatibility)
- [Configuration sections](#configuration-sections)
- [User options versus automatic optimization](#user-options-versus-automatic-optimization)
- [Boolean and enum values](#boolean-and-enum-values)
- [Scheduled intervals](#scheduled-intervals)
- [Limited and transitional options](#limited-and-transitional-options)
- [Updating the schema](#updating-the-schema)
- [Pilot file](#pilot-file)
- [Backups and rollback](#backups-and-rollback)
- [Troubleshooting](#troubleshooting)
- [Managed-file boundaries](#managed-file-boundaries)
- [Scope](#scope)

## File locations

The active configuration is:

```text
/var/www/ss-config
```

The upstream reference boilerplate is:

```text
bash/ss-config-sample.txt
```

The active file is owned by `root:root` with mode `0700`. It contains passwords, API credentials, database credentials, private backup settings, and other sensitive values.

Do not:

- publish the file
- paste it into public issues or support forums
- store it in a public repository
- make it readable by the SFTP user or web server
- replace its ownership or mode with normal web-file permissions

See [Permissions](permissions.md) for SlickStack's broader ownership model.

## Bash syntax and security

`ss-config` is a Bash file, not an inert INI or JSON document. SlickStack scripts load it with `source`, commonly while running as `root`.

Use simple quoted assignments:

```bash
OPTION_NAME="value"
```

Important syntax rules:

- do not place spaces around `=`
- preserve the surrounding quotes unless a specific option requires otherwise
- keep each normal setting on one line
- preserve the final `SS_EOF` marker
- do not insert untrusted shell commands, command substitutions, aliases, functions, or downloaded code

Because the file is sourced, malicious or accidental shell code can execute with root privileges. Treat every change as a privileged server-configuration change.

Check Bash syntax without executing the file:

```bash
sudo bash -n /var/www/ss-config
```

Check the end marker and unresolved quoted placeholders:

```bash
grep 'SS_EOF' /var/www/ss-config
grep -n '"@' /var/www/ss-config
```

Check ownership and mode:

```bash
sudo stat -c '%U:%G %a %n' /var/www/ss-config
```

The expected result is approximately:

```text
root:root 700 /var/www/ss-config
```

## Configuration lifecycle

A typical configuration change follows this sequence:

1. back up or update the active `ss-config`
2. edit only supported values
3. validate its Bash syntax and required placeholders
4. run `ss-install` to regenerate managed configuration
5. verify the affected services and website behavior

Edit the file as a sudo user:

```bash
sudo nano /var/www/ss-config
```

After changing settings, the complete reconciliation command is:

```bash
sudo bash /var/www/ss-install
```

or:

```bash
ss install
```

Changing `ss-config` alone does not rewrite active Nginx, PHP-FPM, MySQL, WordPress, SSL, cron, firewall, or other generated files.

A narrowly scoped module installer can sometimes apply one category of settings, but the full installer is the safest way to reconcile the entire managed stack. A full installation is broader than a configuration reload: it updates packages and managed files, uses maintenance mode, resets logs and permissions, purges caches, restarts services, and performs other installation tasks.

See [Updates](updates.md) before using a full installation merely to refresh one component.

## Initial setup

When no active `ss-config` exists, `ss-install` downloads the current sample and runs its interactive setup wizard.

The wizard populates required installation values such as:

- sudo and SFTP users
- passwords
- production domains
- database credentials
- randomized Adminer information

Advanced administrators can prepare `/var/www/ss-config` manually before installation, but all required placeholders must be replaced and the active build must match the installer.

The installer currently rejects remaining placeholders matching the quoted `"@...` pattern. It also validates the production root and full domain formats before continuing.

Do not assume placeholder validation proves every setting is valid. Most values are consumed later by individual module scripts, and many are not centrally type-checked or range-checked.

## Build compatibility

The active file contains an `SS_BUILD` value:

```bash
SS_BUILD="..."
```

The build identifier coordinates compatibility between:

- `ss-config`
- `ss-update-config`
- `ss-install`
- the current public boilerplate

`ss-install` exits when its required build differs from the active `SS_BUILD`. The normal recovery sequence is:

```bash
sudo bash /var/www/ss-update-config
sudo bash /var/www/ss-install
```

Do not manually change `SS_BUILD` merely to bypass a mismatch. The schema or migration logic may have changed even when the visible values look similar.

## Configuration sections

The current boilerplate groups settings approximately as follows:

| Section | Examples |
| :-- | :-- |
| General | application, language, timezone, noindex, synchronization, lockdown, reboot threshold, backup method |
| Access | sudo user, SSH keys and restrictions, SFTP user and passwords |
| Domains | production root and full domains |
| Cloudflare | API credentials, real visitor IPs, Authenticated Origin Pulls, origin restrictions |
| Database | local or remote database mode, host, port, names, users, passwords, charset, prefix |
| Adminer | public access and randomized URL |
| Pilot file | optional remote settings source for selected multi-server values |
| Environments | staging, development, guest credentials, synchronization behavior |
| WordPress | WP-Cron, Multisite, revisions, autosave, file changes, object cache, MU plugins, blacklist |
| Whitelabel | brand and support information |
| Remote backups | Rclone and Rsync settings |
| SSL | certificate type, protocols, ciphers, OpenSSL, Certbot, and CSR values |
| Nginx | workers, connections, buffers, timeouts, open-file cache, rate limits, FastCGI cache |
| MySQL | SQL and InnoDB settings for local databases |
| PHP-FPM | selected PHP, FPM, pool, and OPcache settings |
| Intervals | scheduled SlickStack maintenance task selection |
| Custom MU plugins | approved source URLs and destination directories |

The presence of an option in the sample does not always mean it is fully supported. Read its comments and the relevant service guide before changing it.

## User options versus automatic optimization

SlickStack intentionally avoids turning `ss-config` into a complete mirror of every Nginx, PHP, MySQL, or Linux directive.

Some lower-level PHP-FPM and OPcache options have already been removed from the user-facing configuration as installation scripts gained automatic optimization. The current PHP configuration installer calculates values from physical server RAM, including:

- `PHP_MEMORY_LIMIT`
- `OPCACHE_MEMORY_CONSUMPTION`
- `OPCACHE_INTERNED_STRINGS_BUFFER`
- the global FPM process limit
- pool worker counts
- start and spare worker counts
- spawn rate
- idle timeout
- maximum requests
- request termination timeout

The current installer uses conservative RAM tiers for approximately 1 GB, 2 GB, 4 GB, and 8 GB-or-larger servers. These generated values are applied while building PHP-FPM configuration and are not normal persistent choices in `ss-config`.

Some surviving values can also be overridden by automatic tuning. For example, `WWW_PM_MODE` remains visible in the current sample, but the RAM autotuner currently assigns its own process-manager mode during installation.

This is intentional direction rather than an accidental loss of configurability:

- `ss-config` should hold meaningful administrator choices.
- Installation scripts should own predictable values that can be safely derived from the machine.
- Redundant or dangerous tuning options may continue to be removed when automatic optimization is demonstrably more reliable.

Do not re-add a removed variable and assume SlickStack will honor it. Unknown variables may be ignored by installers and are not preserved by the normal config migration process.

See [PHP-FPM](php-fpm.md) for the generated PHP and pool configuration.

## Boolean and enum values

Most feature switches use quoted lowercase values:

```bash
FEATURE_NAME="true"
FEATURE_NAME="false"
```

Do not substitute values such as `yes`, `on`, `1`, or uppercase variants unless the option's comments explicitly permit them. Most scripts compare exact strings.

Other settings use a fixed set of names, such as:

```bash
SS_REMOTE_BACKUPS="none"
SSL_TYPE="openssl"
WP_CRON_METHOD="wpcli"
```

An unsupported value may be ignored, trigger a fallback, or select an unintended branch. SlickStack does not use one universal schema validator for all options.

## Scheduled intervals

Interval settings select which fixed cron wrapper sources a task. They do not create arbitrary cron expressions.

Examples include:

```bash
INTERVAL_SS_DUMP_DATABASE="hourly"
INTERVAL_SS_PERMS="half-daily"
INTERVAL_SS_PURGE_MEMCACHED="half-monthly"
INTERVAL_SS_UPDATE_CONFIG="never"
```

Supported interval names vary by task. Common values include:

```text
hourly
quarter-daily
half-daily
daily
half-weekly
weekly
half-monthly
monthly
sometimes
never
```

Do not assume `never` disables every task. Some required maintenance conditions treat unsupported values—including `never`—as invalid and fall back to a default schedule. The permissions reset is one current example.

The sample also marks `INTERVAL_SS_CLEAN_DATABASE` as not yet functional.

See [Cron Jobs](cron.md) for the actual schedules, required tasks, overlap protection, and custom cron files.

## Limited and transitional options

Some current values are intentionally limited, transitional, or reserved for future behavior. Examples include:

- `SS_APP` currently supports WordPress as the standard application.
- `SS_LOCKDOWN` is marked as having limited functionality.
- `WHITELABEL_BILLING_METHOD` is present but not currently supported.
- Adminer-specific Nginx rate-limit variables are marked as not yet supported.
- query-specific FastCGI cache options remain commented out.
- `INTERVAL_SS_CLEAN_DATABASE` is marked as not functional.
- Fail2ban settings remain present while Fail2ban is planned for removal.
- some cleanup settings are marked for future deletion.

Treat comments such as `coming soon`, `not supported yet`, `limited functionality`, and `deleting soon` literally. Do not build critical automation around them.

## Updating the schema

Update the active file to the latest boilerplate with:

```bash
sudo bash /var/www/ss-update-config
```

The updater:

1. sources the active `ss-config`
2. creates a timestamped backup
3. verifies the active end marker
4. downloads the latest boilerplate
5. verifies build compatibility
6. transfers an explicit list of recognized non-empty values
7. applies compatibility and fallback patches
8. validates and activates the replacement
9. restores `root:root 0700`

Backups use this pattern:

```text
/var/www/backups/config/ss-config.bak.TIMESTAMP
```

The updater starts from the latest boilerplate rather than copying the old file wholesale. It does not normally preserve:

- custom variables
- arbitrary shell logic
- custom comments
- unsupported legacy names
- deleted settings
- intentionally empty values unless the new boilerplate is also empty

Review the diff after every schema migration:

```bash
sudo diff -u /var/www/backups/config/ss-config.bak.TIMESTAMP /var/www/ss-config
```

Replace `TIMESTAMP` with the selected backup filename.

### Current `--restore` limitation

The updater accepts:

```bash
sudo bash /var/www/ss-update-config --restore
```

The intended behavior is to skip migration and restore current boilerplate defaults. However, the current transfer commands are outside the flag's conditional block, so recognized non-empty values are still transferred.

Do not currently rely on `--restore` as a clean-default reset. To rebuild carefully from defaults, preserve the backup, prepare a fresh current boilerplate, restore all required credentials and domains, validate it, and only then run `ss-install`.

### Other current updater limitations

- The success message reads the active build before replacing the file, so it can display the previous build instead of the newly activated one.
- Empty values are not transferred and can be replaced by fallback logic.
- The fallback for an empty `SS_REBOOT_MIN_UPTIME` currently writes `"1 hour"`, while the active reboot logic expects numeric seconds such as `3600`.
- Some fallback names and defaults reflect older schema names or values.
- A recent updater timestamp proves the script started, not that every migration result was correct.

See [Updates](updates.md) for the wider script and package update model.

## Pilot file

`SS_PILOT_FILE` can point to a remote file used by `ss-worker` to copy selected values across multiple SlickStack servers:

```bash
SS_PILOT_FILE="https://example.com/private-pilot"
```

The current worker downloads the file to a temporary path, sources it, reads a limited list of variables, and edits matching values in the active `ss-config`.

Current pilot-controlled areas include selected values for:

- Cloudflare credentials
- PHP extensions
- whitelabel information
- the WordPress plugin blacklist
- a small number of legacy or optional Nginx-related values

The pilot file is not a full remote replacement for `ss-config`.

### Pilot-file security boundary

The pilot file is sourced as shell code by a root-run SlickStack process. Control of its contents can therefore become root code execution on every connected server.

Use it only when:

- the URL and hosting account are fully trusted
- transport is HTTPS
- access is private where possible
- changes are reviewed before deployment
- the remote file is protected from public edits and account takeover

The current pilot logic includes some legacy variable names that do not exactly match the latest sample. Verify each intended value on a test server before using the feature across a fleet.

## Backups and rollback

List recent config backups:

```bash
sudo ls -lt /var/www/backups/config/ss-config.bak.*
```

Inspect a backup without exposing it publicly:

```bash
sudo less /var/www/backups/config/ss-config.bak.TIMESTAMP
```

Restore a selected backup:

```bash
sudo cp /var/www/backups/config/ss-config.bak.TIMESTAMP /var/www/ss-config
sudo chown root:root /var/www/ss-config
sudo chmod 0700 /var/www/ss-config
sudo bash -n /var/www/ss-config
```

Confirm that its `SS_BUILD` is compatible with the current scripts before running `ss-install`. An old configuration can be syntactically valid but incompatible with a newer installer.

Config backups are subject to SlickStack's normal cleanup behavior. They are not a substitute for independent server backups.

## Troubleshooting

### `ss-install` reports a build mismatch

Run:

```bash
sudo bash /var/www/ss-update-config
```

Then review the migrated file and run:

```bash
sudo bash /var/www/ss-install
```

Do not bypass the check by editing `SS_BUILD` manually.

### The config is missing or corrupt

Check:

```bash
sudo bash -n /var/www/ss-config
grep 'SS_EOF' /var/www/ss-config
sudo ls -lt /var/www/backups/config/
```

Restore a known-good compatible backup or rebuild from the current sample.

### A setting changed but nothing happened

The active generated file may not have been rebuilt. Run the relevant module installer or the complete:

```bash
sudo bash /var/www/ss-install
```

Then inspect the generated configuration and service state.

### A setting reverts after an update

Possible causes include:

- the variable is not in `ss-update-config`'s explicit migration list
- the old value was empty
- the setting was renamed or removed
- a pilot file overwrote it
- an installer now calculates the value automatically
- the option is not supported by the active module

Compare the active file with the latest timestamped backup and inspect the relevant installation script.

### A manually added option has no effect

The variable may not be consumed anywhere. Search the source repository for the exact name and verify that the active installer or template uses it.

Do not assume that adding a familiar Nginx, PHP, MySQL, or WordPress directive name to `ss-config` automatically connects it to generated configuration.

### `ss-stack-overview` output is needed for support

The overview can expose passwords and other credentials. Redact all secrets, private URLs, origin addresses, usernames, and tokens before sharing any output.

## Managed-file boundaries

`ss-config` controls supported inputs, while SlickStack owns the generated outputs. Direct edits to managed files can be replaced by `ss-check`, `ss-worker`, scheduled tasks, module installers, or `ss-install`.

Common generated destinations include:

- Nginx configuration and server blocks
- PHP-FPM, pool, PHP, and OPcache configuration
- MySQL configuration
- Memcached configuration
- WordPress `wp-config.php` files and supporting constants
- SSL includes and certificates
- firewall rules
- root crontab and cron wrappers
- service and permissions files

Persistent behavior should be implemented through:

- a supported `ss-config` option
- an approved custom include
- reserved custom cron files
- approved WordPress custom files or MU plugins
- a source-level SlickStack change
- an external service's own configuration

Do not modify generated files and then expect `ss-config` migration to preserve those edits.

## Scope

The standard configuration model assumes one primary production domain per server, optional staging and development environments, one managed WordPress application, and a controlled set of server modules.

`ss-config` is not intended to become a universal control panel, arbitrary provisioning language, or complete interface for every underlying service option. Its schema may continue to shrink or change as SlickStack replaces fragile manual tuning with safe automatic behavior.