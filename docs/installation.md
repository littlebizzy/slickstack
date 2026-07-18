# Installation

SlickStack installs and reconciles an entire WordPress-focused LEMP server through:

```bash
sudo bash /var/www/ss-install
```

The installer is designed to be idempotent, meaning it can normally be rerun to restore the managed SlickStack state. It is not a read-only check or a harmless configuration reload. A full run upgrades packages, rewrites managed files, synchronizes enabled environments, truncates logs, resets permissions, purges caches, and restarts services.

## Server requirements

The current installer accepts these Ubuntu LTS releases:

```text
18.04
20.04
22.04
24.04
```

New installations should normally use Ubuntu 24.04 LTS, which is the current module baseline.

SlickStack expects:

- a normal physical machine or full virtual machine, preferably KVM
- root or provider-console access for the first installation
- at least 1 GB of free disk space
- one primary production domain per server
- working network connectivity and DNS
- no Docker, LXC, OpenVZ, Podman, systemd-nspawn, or similar container environment

Basic WordPress sites can run with approximately 1 GB of RAM, while 2 GB or more is recommended for WooCommerce, membership sites, forums, Multisite, and other dynamic workloads.

## DNS and Cloudflare preparation

The first-run wizard expects the production domain to be ready before installation.

The current instructions recommend:

- adding the root and `www` DNS records
- pointing them to the server address
- adding staging or development records when those environments will be used
- enabling the Cloudflare proxy
- using Cloudflare Full SSL

SlickStack uses a self-signed OpenSSL certificate by default. Without Cloudflare or another trusted proxy in front of the origin, browsers will not normally trust that certificate.

A temporary subdomain can be used for testing. Domain values can later be changed in [`ss-config`](ss-config.md), followed by another full installation to regenerate the managed configuration.

See [Cloudflare](cloudflare.md), [SSL (Certs)](ssl.md), and [SS-Config](ss-config.md) before installing a production domain.

## Initial bootstrap

The README bootstrap command is:

```bash
cd /tmp/ && wget -O ss slick.fyi/ss && bash ss
```

This downloads the installer into `/tmp` and runs it as the current root shell.

After SlickStack has been installed, the normal command is:

```bash
sudo bash /var/www/ss-install
```

or:

```bash
ss install
```

Do not execute an installer downloaded from an untrusted URL or an unreviewed mirror.

## First-run setup wizard

When `/var/www/ss-config` does not exist, `ss-install` downloads the current `ss-config` sample from the SlickStack mirrors and starts an interactive wizard.

The wizard asks for:

- the production full domain
- the production root domain

Each domain prompt currently waits up to 30 seconds. If no response is entered, the detected server hostname or root-domain value is used automatically. Review these defaults carefully; a cloud-provider hostname is not necessarily the intended website domain.

The wizard also generates randomized values for:

- the sudo username and password
- the SFTP username and password
- guest HTTP authentication
- the MySQL application user and password
- the MySQL root password
- the randomized Adminer URL

The resulting file is activated as:

```text
/var/www/ss-config
```

with:

```text
root:root 0700
```

Advanced administrators can prepare a valid current `ss-config` before starting the installer, which skips the interactive generation step.

Never publish the generated file. It contains server, database, backup, Cloudflare, and access credentials.

## Preflight validation

Before continuing with an existing configuration, the installer checks several conditions.

### Build compatibility

The active `SS_BUILD` must match the build expected by the current `ss-install` script.

When the values differ, run:

```bash
sudo bash /var/www/ss-update-config
```

Review the migrated configuration, then rerun:

```bash
sudo bash /var/www/ss-install
```

Do not manually change `SS_BUILD` merely to bypass the check.

### Domain formats

The installer validates both:

```bash
SITE_ROOT_DOMAIN="example.com"
SITE_FULL_DOMAIN="www.example.com"
```

Invalid or incomplete domain syntax causes the installer to exit.

### Ubuntu and disk space

The installer exits when:

- the Ubuntu release is outside its accepted LTS list
- less than 1 GB of free disk space remains
- a supported container marker is detected

Disk-space validation is only a minimum preflight threshold. Package downloads, database dumps, caches, and temporary files can require considerably more space during a real installation.

### Unresolved placeholders

The installer scans the active config for quoted placeholders beginning with:

```text
"@
```

Any remaining match causes installation to stop. This does not validate every enum, number, path, credential, or service-specific value.

Check the config manually before proceeding:

```bash
sudo bash -n /var/www/ss-config
grep -n '"@' /var/www/ss-config
grep '^SS_BUILD=' /var/www/ss-config
```

### SSH keys and third-party certificates

When key-only SSH access or third-party certificates are selected, prepare the expected files before installation.

Current expected paths include:

```text
/var/www/auth/id_rsa.pub
/var/www/certs/thirdparty.pem
/var/www/certs/keys/thirdparty.key
```

> **Current limitation:** the corresponding installer blocks print messages saying installation is exiting when these files are absent, but the current blocks do not execute an `exit` command. Do not rely on those warnings to stop the process. Verify the files manually before continuing.

## Installation sequence

A full installation currently performs these broad stages:

1. restores `ss-functions` from public mirrors when missing or stale
2. sources the active `ss-config`
3. validates the environment, domains, build, disk space, and placeholders
4. creates or activates `ss-config` during a first run
5. retrieves and runs `ss-check`
6. creates the SlickStack directory structure and baseline ownership
7. runs the Ubuntu package and kernel update workflow
8. installs Ubuntu utilities, users, Bash configuration, SSH, crontab, swap, and kernel settings
9. installs PHP-FPM and generated PHP configuration
10. installs Nginx, server blocks, and certificate configuration
11. enables the production maintenance response
12. installs and configures MySQL
13. installs and configures Memcached
14. installs WordPress, WP-CLI, generated WordPress configuration, and MU plugins
15. invokes the production-to-staging synchronization script
16. installs Rclone support
17. installs Iptables rules
18. installs Fail2ban while it remains part of SlickStack
19. truncates SlickStack-managed log files
20. resets the complete permissions policy
21. disables maintenance mode
22. purges Nginx, OPcache, Memcached, and WordPress transients
23. restarts PHP-FPM, Nginx, Memcached, and MySQL
24. validates and repairs the root crontab
25. displays the SlickStack stack overview

The order matters. For example, PHP-FPM is installed before Nginx, maintenance mode begins only after Nginx is available, and caches are purged before the final service restarts.

## What idempotent means

SlickStack uses idempotent installation to converge a server toward its current managed templates and settings.

A successful rerun should generally:

- recreate missing managed files
- replace outdated managed files
- reapply the current `ss-config` choices
- restore expected permissions
- reinstall or update required packages
- restart services into the expected state

It does **not** mean that nothing changes on a working server.

A rerun can deliberately:

- upgrade Ubuntu packages and the Linux kernel
- replace direct edits to managed configuration
- refresh WordPress Core files
- overwrite the staging database and selected staging files
- truncate current SlickStack log contents
- invalidate caches
- interrupt active requests during service restarts
- require a later reboot for a newly installed kernel

Treat every production rerun as a maintenance operation.

## Reinstallation effects

### Ubuntu package upgrades

Every full installation sources the package-update workflow and runs a noninteractive Ubuntu `full-upgrade`.

This can install:

- security updates
- service updates
- dependency changes
- a newer Linux kernel

`ss-install` does not automatically reboot the machine merely because a new kernel was installed. Check for a reboot requirement after installation.

See [Updates](updates.md) and [Ubuntu](ubuntu.md).

### Managed configuration replacement

The installer regenerates or reinstalls managed files for Ubuntu, SSH, Nginx, PHP-FPM, MySQL, Memcached, WordPress, SSL, Iptables, Fail2ban, cron, and permissions.

Direct edits to those generated files are not persistent customization points and can be lost.

Use:

- supported `ss-config` values
- approved Nginx includes
- reserved custom cron files
- approved WordPress custom files or MU plugins
- source-level SlickStack changes

See the relevant service guide before modifying generated configuration.

### WordPress Core refresh

The WordPress package installer refreshes Core across production and enabled environments. It replaces Core directories and most top-level PHP files while preserving the managed `wp-config.php` and existing custom `wp-content` content unless an incoming path directly replaces it.

This can repair modified Core files, but it is not a complete malware scanner or incident-response workflow.

See [WordPress](wordpress.md).

### Staging synchronization

The full installer always invokes `ss-sync-staging`, but a destructive synchronization occurs only when all of these conditions are true:

- `STAGING_SITE="true"`
- `SS_SYNC_STAGING` is any value other than `"false"`
- `WP_MULTISITE` is any value other than `"true"`

When those conditions are met, the script:

- creates a fresh production database dump
- imports production into the staging database
- copies most production `wp-content` files into staging
- skips uploads, MU plugins, the object-cache drop-in, the blacklist, and files larger than 25 MB
- replaces production URLs with the staging domain
- resets staging WordPress permissions

Unique staging database content and most unique staging files can therefore be overwritten by a full installation.

Staging uploads are connected to production through the managed uploads behavior, so staging media operations can also affect the production uploads tree.

Set:

```bash
SS_SYNC_STAGING="false"
```

before a production reinstall when staging must not be refreshed.

### Log truncation

Every full installation runs:

```bash
sudo bash /var/www/ss-empty-logs
```

This truncates files matching:

```text
/var/www/logs/*.log
```

It does not archive or rotate them first. Preserve relevant evidence before reinstalling a server during an outage or security investigation.

See [Logging](logging.md).

### Permission resets

The installer reapplies the complete SlickStack ownership and mode policy across scripts, credentials, service files, web roots, WordPress, logs, caches, and related paths.

Custom ownership or permissions applied directly to managed paths can be replaced.

See [Permissions](permissions.md).

### Cache purges

Near the end of installation, SlickStack clears:

- Nginx FastCGI cache
- PHP OPcache disk cache and attempted runtime reset
- the local Memcached instance
- production WordPress database transients

Maintenance disable also purges Nginx before the explicit all-cache phase, so Nginx cache is currently cleared twice during a full run.

Cold caches can temporarily increase PHP, MySQL, disk, and network load after the site returns.

See [Caching](caching.md).

### Service restarts and availability

The installer restarts:

- PHP-FPM
- Nginx
- Memcached
- MySQL

The site can return a maintenance response or experience brief connection interruptions while configuration is rewritten and services restart.

Use a real maintenance window for business-critical or high-traffic sites.

### Stack overview output

At completion, the installer runs `ss-stack-overview`.

Its output can contain sensitive information, including usernames, passwords, database credentials, private URLs, and key material. Do not paste the complete output into a public issue or support channel.

See [Monitoring](monitoring.md).

## Before reinstalling production

Use this minimum checklist:

1. create and verify a fresh production database dump
2. confirm that important files are backed up independently
3. verify any remote backup is current and restorable
4. preserve logs needed for troubleshooting or security review
5. review `/var/www/ss-config` and its `SS_BUILD`
6. validate Bash syntax and unresolved placeholders
7. confirm sufficient disk space and inodes
8. decide whether staging may be overwritten
9. confirm the correct SSL files are present
10. keep provider-console or root access available
11. keep the current SSH session open
12. confirm the sudo user can connect in a second session
13. choose a maintenance window appropriate for traffic

Useful checks include:

```bash
sudo bash -n /var/www/ss-config
grep -n '"@' /var/www/ss-config
df -h /
df -i /
sudo bash /var/www/ss-dump-database
sudo ls -lh /var/www/backups/mysql/
```

For critical sites, local dumps are not enough. Verify an independent remote backup and a realistic restore path.

See [Backups](backups.md).

## Full installer versus a narrow installer

Do not rerun the full stack merely because one cache, permission, or service needs attention.

Examples of narrower operations include:

| Goal | Narrow command |
| :-- | :-- |
| Rebuild Nginx configuration | `sudo bash /var/www/ss-install-nginx-config` |
| Rebuild PHP configuration | `sudo bash /var/www/ss-install-php-config` |
| Rebuild WordPress configuration | `sudo bash /var/www/ss-install-wordpress-config` |
| Restore managed permissions | `sudo bash /var/www/ss-perms` |
| Clear all SlickStack origin caches | `ss purge` |
| Restart Nginx | `ss restart nginx` |
| Restart PHP-FPM | `ss restart php` |
| Refresh SlickStack scripts | `sudo bash /var/www/ss-check` |
| Migrate `ss-config` | `sudo bash /var/www/ss-update-config` |
| Upgrade Ubuntu packages | `sudo bash /var/www/ss-update-modules` |

A module installer can still overwrite its managed files and restart its service. Review the relevant guide before running it.

Use the complete installer when:

- provisioning a new server
- applying broad `ss-config` changes
- repairing multiple missing or damaged modules
- resizing a server and reapplying automatic tuning
- deliberately reconciling the entire managed stack

## SSH safety

SlickStack installs a managed SSH configuration, creates the sudo and SFTP users, and disables direct root SSH login.

During installation:

- keep the original root or provider-console session open
- verify the configured sudo username and authentication method
- open a second SSH session as the sudo user
- confirm `sudo` works before disconnecting the original session

The active Ubuntu 24.04 template listens on TCP port `22`. An older `SSH_PORT` value elsewhere does not currently change the listener.

When `SSH_KEYS="true"`, password authentication is disabled while public-key authentication remains enabled. Verify the authorized key before enabling key-only access.

See [Ubuntu](ubuntu.md).

## Post-install verification

Do not rely only on the final success message or a recent timestamp.

### Services

Check failed services and the main stack:

```bash
systemctl --failed
systemctl status nginx --no-pager
systemctl status mysql --no-pager
systemctl status memcached --no-pager
systemctl list-units --type=service --all | grep -E 'php.*fpm'
```

### Configuration

Validate Nginx and confirm the active config build:

```bash
sudo nginx -t
grep '^SS_BUILD=' /var/www/ss-config
sudo crontab -l | grep 'SS_EOF'
```

### Website and WordPress

Check the public response and WordPress:

```bash
curl -I "https://${SITE_FULL_DOMAIN}"
sudo -u www-data wp --path=/var/www/html core version
sudo -u www-data wp --path=/var/www/html option get siteurl
```

Run the `curl` command after sourcing the config or replace the variable with the actual production domain.

Verify:

- the production homepage
- WordPress login
- an uncached dynamic page
- uploads and static assets
- staging and development when enabled
- scheduled WordPress events
- Adminer only when intentionally enabled

### Logs and journals

Inspect recent failures:

```bash
sudo tail -n 100 /var/www/logs/nginx-error.log
sudo tail -n 100 /var/www/logs/php-error.log
journalctl -u nginx --no-pager -n 100
journalctl -u mysql --no-pager -n 100
```

Remember that the full installation truncated SlickStack-managed `.log` files before its final service restarts. Systemd journals may retain earlier installation failures even when `/var/www/logs` does not.

See [Logging](logging.md) and [Monitoring](monitoring.md).

## Failure recovery

When installation stops or the site does not recover:

1. identify the last visible installer stage
2. preserve the current terminal output and relevant journals
3. check disk space, inodes, memory, and failed services
4. validate `ss-config` syntax, placeholders, domains, and build
5. correct the specific package, certificate, credential, or configuration problem
6. rerun the failed module installer when the failure is isolated
7. rerun the complete installer only when broad reconciliation is appropriate
8. verify the site and services after recovery

Useful checks include:

```bash
sudo bash -n /var/www/ss-config
df -h /
df -i /
free -m
systemctl --failed
sudo nginx -t
```

If the site remains in maintenance mode after an interrupted installation, disable it after confirming the stack is ready:

```bash
ss maintenance off
```

Do not remove maintenance mode merely to expose a broken PHP, database, or WordPress installation to visitors.

If access is lost, use the hosting provider console rather than repeatedly changing SSH files through an unverified session.

## Managed-file boundary

SlickStack owns the server configuration generated by its installers. A full installation can replace direct changes to:

- SSH and sudo configuration
- Nginx and server blocks
- PHP-FPM, PHP, pool, and OPcache configuration
- MySQL and Memcached configuration
- WordPress Core and generated `wp-config.php` files
- SSL files and includes
- Iptables and Fail2ban configuration
- root cron and SlickStack wrappers
- permissions and ownership

The supported customization boundary is deliberately narrower than the underlying services' complete feature sets.

## Scope

The standard installation model assumes:

- one primary production domain per server
- one managed WordPress application
- optional staging and development environments
- a local Nginx and PHP-FPM stack
- local MySQL unless remote mode is explicitly configured
- one local Memcached instance
- Cloudflare or equivalent proxying for the default self-signed origin certificate

Multi-tenant hosting, container deployment, server clusters, load balancing, automatic blue-green deployment, transactional rollback, zero-downtime service replacement, and arbitrary provisioning plugins are outside the standard SlickStack installation model.
