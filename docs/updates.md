# Updates

SlickStack separates updates into several different workflows. Updating the SlickStack Bash scripts, migrating `ss-config`, upgrading Ubuntu packages, reinstalling generated service configuration, and rebooting the server are related tasks, but they are not the same operation.

The main commands are:

```bash
sudo bash /var/www/ss-check
sudo bash /var/www/ss-worker
sudo bash /var/www/ss-update-config
sudo bash /var/www/ss-update-modules
sudo bash /var/www/ss-install
```

Using the installed Bash helpers, the same workflows can also be reached through commands such as:

```bash
ss update
ss update config
ss update modules
ss upgrade
ss install
```

These aliases do not all perform the same sequence. Review the distinctions below before using them on a production server.

## Update workflow summary

| Workflow | Primary purpose | Does not normally do |
| :-- | :-- | :-- |
| `ss-check` | Downloads and installs current SlickStack Bash scripts and cron wrappers | Migrate `ss-config`, upgrade Ubuntu packages, or reinstall LEMP configuration |
| `ss-worker` | Performs recurring maintenance, backs up configuration and custom cron files, refreshes `ss-check`, and applies selected SlickStack support files | Upgrade Ubuntu packages or fully reinstall the stack |
| `ss-update-config` | Rebuilds `/var/www/ss-config` from the current upstream boilerplate while transferring supported existing values | Apply the new settings to Nginx, PHP-FPM, MySQL, WordPress, or other modules |
| `ss-update-modules` | Upgrades installed Ubuntu packages, including the Linux kernel, and removes unused dependencies | Migrate `ss-config`, regenerate module configuration, explicitly restart all services, or reboot |
| `ss-install` | Reinstalls and reapplies the complete SlickStack environment and managed configuration | Preserve unsupported direct edits to generated files |

A normal maintenance sequence is therefore different from a full reinstall.

## `ss-check`: SlickStack scripts and cron wrappers

Run:

```bash
sudo bash /var/www/ss-check
```

or:

```bash
ss check
```

`ss-check` retrieves current SlickStack Bash scripts and cron files from the configured public mirrors. It validates downloaded files before installing them.

It updates:

- the root crontab boilerplate
- the 14 standard cron wrappers
- missing custom cron boilerplates
- SlickStack Bash scripts other than `ss-check` itself

It does not update:

- `/var/www/ss-config`
- installed Ubuntu packages
- generated Nginx, PHP-FPM, MySQL, or WordPress configuration
- the active `ss-check` script itself

`ss-check` is part of SlickStack's normal self-maintenance model. Cron wrappers also contain recovery logic for restoring critical scripts when they become missing, damaged, or stale.

Direct edits to standard SlickStack Bash scripts or cron wrappers are not persistent. They can be replaced by `ss-check`, cron self-healing, `ss-install`, or another maintenance run.

See [Cron Jobs](cron.md) for the scheduling and custom-cron boundaries.

## `ss-worker`: maintenance and support files

Run:

```bash
sudo bash /var/www/ss-worker
```

or:

```bash
ss worker
```

`ss-worker` performs several maintenance tasks around the current installation. Its responsibilities include:

- backing up the active `ss-config`
- backing up custom cron files
- retrieving and installing the latest `ss-check`
- refreshing the WordPress plugin blacklist
- importing supported values from an optional pilot file
- regenerating SlickStack's PHP constants file
- installing selected default support files when missing
- applying urgent SlickStack patches
- restoring SlickStack script permissions

The current configuration backup is written beneath:

```text
/var/www/backups/config/
```

Custom cron files are copied beneath:

```text
/var/www/backups/crons/
```

`ss-worker` is not a replacement for either `ss-update-config` or `ss-update-modules`.

## `ss-update-config`: migrate `ss-config`

Run:

```bash
sudo bash /var/www/ss-update-config
```

or:

```bash
ss update config
```

The aliases below currently invoke the same script:

```text
ss update config
ss update ss-config
ss upgrade config
ss upgrade ss-config
```

### What the script does

The config updater:

1. sources the active `ss-config` and `ss-functions`
2. creates a timestamped backup of the active config
3. verifies the active file contains SlickStack's end marker
4. downloads the latest `ss-config` boilerplate
5. falls back from GitHub to GitLab when the first download is invalid
6. verifies that the downloaded config build matches the updater's required build
7. transfers explicitly supported non-empty values from the active file
8. applies compatibility patches and fallback values
9. validates the generated file
10. replaces `/var/www/ss-config`
11. restores `root:root 0700` ownership and mode

The backup filename follows this pattern:

```text
/var/www/backups/config/ss-config.bak.TIMESTAMP
```

Older config backups are later subject to SlickStack's normal cleanup policy.

### Build compatibility

The update script has its own build identifier. The downloaded boilerplate must advertise the matching `SS_BUILD` value.

When the versions do not match, the temporary file is removed and the active config remains unchanged. This prevents a config template from being activated with an incompatible migration script.

`ss-install` also verifies that its build matches the active `SS_BUILD`. When they differ, the installer exits and instructs the administrator to update `ss-config` first.

### Values that are transferred

The updater does not copy the old file wholesale. It starts from the latest boilerplate and transfers a long explicit list of recognized variables, including settings for:

- the application, language, timezone, synchronization, lockdown, and backups
- sudo, SSH, and SFTP access
- domains and Cloudflare
- local or remote databases
- Adminer
- staging and development
- WordPress and WP-Cron
- whitelabeling
- Rclone and Rsync
- SSL, OpenSSL, Certbot, and CSR values
- Nginx, rate limiting, and FastCGI cache
- MySQL, PHP-FPM, OPcache, and pool tuning
- Fail2ban while it remains present
- scheduled task intervals
- the custom MU-plugin list

Only values that are both recognized by the migration script and non-empty are transferred.

This means arbitrary custom variables, comments, reordered sections, and unsupported additions in the old `ss-config` are not preserved automatically.

Before updating, review any local additions and move persistent custom logic to a supported file or source-level SlickStack change.

### Applying migrated settings

`ss-update-config` replaces the config file, but it does not reinstall the stack or regenerate every module configuration.

After a successful migration, run:

```bash
sudo bash /var/www/ss-install
```

when the new boilerplate or changed settings need to be applied across Ubuntu, Nginx, PHP-FPM, MySQL, WordPress, certificates, cron, permissions, or other modules.

For a narrowly scoped setting, the relevant module installer may be sufficient, but the full installer is the most complete reconciliation workflow.

### Restoring boilerplate defaults

The updater accepts:

```bash
sudo bash /var/www/ss-update-config --restore
```

With `--restore`, the normal old-value transfer block is skipped. The active config is still backed up first, but the replacement file uses current boilerplate defaults plus the script's placeholder and empty-value fallback logic.

This is destructive to existing customized values. Treat it as a deliberate reset rather than a normal update.

After using `--restore`:

1. inspect the new `/var/www/ss-config`
2. restore required domains, credentials, access settings, and integration values
3. verify `SS_BUILD`
4. run `ss-install` only after the file is correct

### Current config-updater limitations

#### Explicit transfer list

A setting is retained only when the updater explicitly transfers it. New, renamed, deprecated, or custom variables can revert to the new boilerplate value.

Compare the backup and active files after migration:

```bash
diff -u /var/www/backups/config/ss-config.bak.TIMESTAMP /var/www/ss-config
```

Replace `TIMESTAMP` with the actual backup filename.

#### Empty values

Empty existing values are not transferred. The new boilerplate value or a hardcoded fallback is used instead.

Some legacy fallback replacements currently differ from the latest sample config. For example:

- the sample config uses numeric seconds such as `SS_REBOOT_MIN_UPTIME="3600"`
- the empty-value fallback still writes `SS_REBOOT_MIN_UPTIME="1 hour"`
- the active reboot script performs a numeric comparison and expects seconds

Keep `SS_REBOOT_MIN_UPTIME` numeric after updating.

The empty-value fallback for `CLOUDFLARE_REAL_IPS` also currently uses `false`, while the current sample config uses `true`. Review security and proxy-related values rather than relying on empty entries.

#### Success message build number

The current updater reads `SS_BUILD_ACTIVE` before replacing the config and then uses that value in its success message. The message can therefore display the previous build even when the new file was installed successfully.

Verify the active value directly:

```bash
grep '^SS_BUILD=' /var/www/ss-config
```

## `ss-update-modules`: Ubuntu packages and kernel

Run:

```bash
sudo bash /var/www/ss-update-modules
```

or:

```bash
ss update modules
```

The following aliases currently invoke the same package update script:

```text
ss update modules
ss update packages
ss upgrade modules
ss upgrade packages
```

### What the script does

The module updater:

1. conditionally requests a production database dump
2. cleans the APT cache
3. refreshes the APT package index
4. runs a noninteractive `full-upgrade`
5. removes unused dependencies
6. restores SlickStack script and cron permissions

The shared APT wrapper uses `apt-get` with noninteractive behavior and `--force-confold` plus `--force-confdef`. Package upgrades therefore normally retain existing local package configuration when Debian package prompts would otherwise appear.

The update includes installed Ubuntu packages and can install a newer Linux kernel.

### What it does not do

`ss-update-modules` does not itself:

- run `ss-update-config`
- retrieve every SlickStack script
- regenerate Nginx, PHP-FPM, MySQL, or WordPress configuration
- run the complete permissions workflow
- explicitly restart all SlickStack services
- test the site after upgrading
- reboot the server
- verify whether `/var/run/reboot-required` exists

Package maintainer scripts may restart individual services as part of an Ubuntu package upgrade, but that is separate from SlickStack explicitly restarting them.

### Current database-backup limitation

The module updater's backup step is conditional on this file already existing:

```text
/var/www/backups/mysql/production.sql
```

Only when that path exists does it call `ss-dump-database` before upgrading packages.

Therefore, a server without an existing production dump file may not receive a new pre-upgrade database dump from `ss-update-modules` itself.

Before important package upgrades, create and verify a backup explicitly:

```bash
sudo bash /var/www/ss-dump-database
ls -lh /var/www/backups/mysql/
```

For business-critical sites, also confirm the remote backup and restore process rather than relying only on a local file. See [Backups](backups.md).

## `ss update` versus `ss upgrade`

The Bash helper defines different compound commands.

### `ss update`

The current `ss update` sequence runs:

1. `ss-check`
2. `ss-worker`
3. `ss-check`
4. `ss-update-config`
5. `ss-check`
6. `ss-worker`
7. `ss-check`
8. `ss-install-ubuntu-crontab`
9. `ss-update-modules`
10. PHP-FPM restart
11. Nginx restart
12. MySQL restart
13. Memcached restart

This is broader than either standalone updater. It refreshes scripts repeatedly, migrates config, repairs cron, upgrades Ubuntu packages, and explicitly restarts the main services.

However, it does not run the full `ss-install` workflow after migrating `ss-config`. A new or changed config value that requires regenerated module files may still need:

```bash
sudo bash /var/www/ss-install
```

### `ss upgrade`

The current `ss upgrade` alias runs only:

1. `ss-update-config`
2. `ss-update-modules`

It does not include the repeated script refreshes, crontab reinstall, or explicit service restarts performed by `ss update`.

For predictable production maintenance, running the individual scripts deliberately is clearer than assuming `update` and `upgrade` are synonyms.

## `ss-install`: full reconciliation

Run:

```bash
sudo bash /var/www/ss-install
```

or:

```bash
ss install
```

The installer is designed to be idempotent and reinstalls the complete SlickStack environment. Its workflow includes:

- build and environment validation
- script retrieval
- Ubuntu package and kernel upgrades
- Ubuntu configuration
- PHP-FPM, Nginx, SSL, MySQL, Memcached, WordPress, Rclone, Iptables, and currently Fail2ban
- staging synchronization where enabled
- maintenance mode handling
- complete permission reconciliation
- cache purges
- service restarts
- crontab validation and repair

Use `ss-install` when:

- the active `ss-config` has changed
- a config migration introduced new settings
- generated service files are stale or damaged
- server resources changed and tuning should be recalculated
- a package or manual edit left the stack inconsistent
- a complete reconciliation is more appropriate than a narrow module installer

Because many files are generated, direct edits to SlickStack-managed configuration can be replaced.

See the relevant module guide before maintaining a local exception.

## Scheduled updates

The sample config defaults both update tasks to `never`:

```bash
INTERVAL_SS_UPDATE_CONFIG="never"
INTERVAL_SS_UPDATE_MODULES="never"
```

This avoids silently migrating configuration or upgrading system packages without an administrator reviewing the result.

### Config migration intervals

The current sample advertises:

```text
weekly
half-monthly
monthly
sometimes
never
```

The wrappers map those values to:

| Value | Actual schedule |
| :-- | :-- |
| `weekly` | Sunday at 00:00 |
| `half-monthly` | The 13th of each month at 00:00 |
| `monthly` | The 1st of each month at 00:00 |
| `sometimes` | The 1st day of every second month at 00:00 |
| `never` | Disabled |

### Package update intervals

The current sample advertises:

```text
half-monthly
monthly
sometimes
never
```

The active weekly cron wrapper also contains a branch for `INTERVAL_SS_UPDATE_MODULES="weekly"`, but the sample config does not list weekly as a supported package-update value. Treat weekly module updates as an undocumented implementation path rather than a supported configuration choice.

For most production servers, manual package updates with a maintenance window, current backup, and post-update verification are safer than unattended `full-upgrade` runs.

See [Cron Jobs](cron.md) for the exact wrapper model and overlap protection.

## Reboots after updates

Package and kernel updates do not automatically call SlickStack's reboot script.

Check whether Ubuntu requests a reboot:

```bash
if [ -f /var/run/reboot-required ]; then
    cat /var/run/reboot-required
fi
```

Review pending service and kernel state:

```bash
uname -r
uptime
systemctl --failed
```

SlickStack's separate reboot command is:

```bash
sudo bash /var/www/ss-reboot-machine
```

or:

```bash
ss reboot
```

The script allows a reboot only when system uptime meets `SS_REBOOT_MIN_UPTIME`. That value must be numeric seconds, for example:

```bash
SS_REBOOT_MIN_UPTIME="3600"
```

Scheduled reboots are controlled independently:

```bash
INTERVAL_SS_REBOOT_MACHINE="never"
```

Supported reboot schedules are half-monthly, monthly, and sometimes. The update scripts do not automatically change this value or link a reboot to `/var/run/reboot-required`.

Plan the reboot separately and confirm that SSH access, Cloudflare, Nginx, PHP-FPM, MySQL, Memcached, and the public website recover afterward.

## Recommended production procedure

Before a significant update:

```bash
sudo bash /var/www/ss-dump-database
sudo bash /var/www/ss-dump-files
sudo bash /var/www/ss-stack-overview
```

Then:

1. confirm local and remote backups are current
2. inspect free disk space and memory
3. note the active `SS_BUILD`, Ubuntu release, kernel, PHP version, and service state
4. run `ss-check`
5. run `ss-update-config`
6. inspect the config diff and correct migrated values
7. run `ss-update-modules`
8. run `ss-install` when the config or generated files need reconciliation
9. verify services and logs
10. reboot only when required and appropriate

Useful pre-update checks:

```bash
grep '^SS_BUILD=' /var/www/ss-config
lsb_release -ds
uname -r
df -h
free -h
systemctl --failed
sudo nginx -t
```

Useful post-update checks:

```bash
grep '^SS_BUILD=' /var/www/ss-config
sudo nginx -t
systemctl status nginx
systemctl status mysql
systemctl status memcached
systemctl status php*-fpm.service
curl -I https://example.com
```

Replace `example.com` with the configured public hostname.

## Recovery

### Restore a previous `ss-config`

List available backups:

```bash
ls -lt /var/www/backups/config/ss-config.bak.*
```

Inspect the intended file, then restore it:

```bash
sudo cp /var/www/backups/config/ss-config.bak.TIMESTAMP /var/www/ss-config
sudo chown root:root /var/www/ss-config
sudo chmod 0700 /var/www/ss-config
```

Verify the build before running the installer:

```bash
grep '^SS_BUILD=' /var/www/ss-config
```

An older config build may not match the current installer. In that case, restore the values carefully through the current `ss-update-config` workflow rather than forcing an incompatible `ss-install` run.

### Repair interrupted APT or dpkg

Check for package-manager processes and locks before deleting anything:

```bash
ps aux | grep -E 'apt|dpkg'
```

After confirming no legitimate package operation is still running, common recovery commands include:

```bash
sudo dpkg --configure -a
sudo apt-get -f install
sudo apt-get update
```

Then rerun:

```bash
sudo bash /var/www/ss-update-modules
```

Do not remove APT or dpkg lock files while an active package process is using them.

### Mirror or download failure

`ss-update-config` validates the retrieved boilerplate and falls back from GitHub to GitLab. If neither valid file is available, the active config is retained.

`ss-check` uses GitHub, GitLab, and SourceForge fallbacks for many SlickStack scripts and cron files.

When downloads fail:

- verify DNS and outbound HTTPS connectivity
- verify system time and CA certificates
- inspect whether GitHub, GitLab, or SourceForge is reachable
- rerun the narrow command instead of repeatedly running the complete installer
- confirm the active files still contain the `SS_EOF` validation marker

### Services fail after package upgrades

Check configuration and logs before reinstalling everything:

```bash
sudo nginx -t
systemctl --failed
journalctl -u nginx --no-pager -n 100
journalctl -u mysql --no-pager -n 100
journalctl -u php*-fpm.service --no-pager -n 100
```

Then run the relevant SlickStack installer or restart script. When several managed modules are inconsistent, run:

```bash
sudo bash /var/www/ss-install
```

### Scripts appear stale or damaged

Run:

```bash
sudo bash /var/www/ss-check
sudo bash /var/www/ss-worker
sudo bash /var/www/ss-check
```

This refreshes the standard scripts and allows `ss-worker` to replace `ss-check` itself.

## Managed-file boundaries

Update and reinstall workflows can replace:

- `/var/www/ss-config` during config migration
- `/var/www/ss*` scripts through `ss-check`
- standard cron wrappers and the managed root crontab
- generated files under `/var/www/sites/`
- generated WordPress configuration and MU plugins
- managed Ubuntu, Nginx, PHP-FPM, MySQL, Memcached, Iptables, and currently Fail2ban configuration
- generated certificates, credentials, metadata, and permissions where the relevant installer manages them

Package updates can also install new upstream defaults, but SlickStack's APT wrapper normally keeps existing package configuration during prompts. A later `ss-install` can then regenerate SlickStack-managed files from its own templates.

Persistent customization belongs in supported `ss-config` values, custom cron files, documented optional include files, WordPress custom files, or the SlickStack source itself.

## Scope

The standard SlickStack update model assumes:

- one primary server and application stack
- Ubuntu LTS packages managed through APT
- SlickStack scripts retrieved from public mirrors
- a current and valid `ss-config`
- deliberate administrator review before system upgrades
- backups before risky changes
- service and website verification after maintenance
- full reinstall as the supported reconciliation path

Major Ubuntu release upgrades, automatic distribution upgrades, third-party package repositories outside SlickStack, container image updates, clustered rolling deployments, blue-green releases, live kernel patching, custom package pinning, commercial patch management, and fleet orchestration are outside the standard SlickStack update workflow.
