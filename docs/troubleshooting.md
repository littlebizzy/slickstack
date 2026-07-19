# Troubleshooting

This guide is a first-response runbook for diagnosing a SlickStack server. It helps isolate the failing layer, preserve useful evidence, and choose the narrowest safe repair.

SlickStack is a layered system:

```text
DNS / Cloudflare
        |
        v
Provider network and Iptables
        |
        v
Nginx
        |
        v
PHP-FPM
        |
        v
WordPress
        |
        +--> MySQL
        `--> Memcached
```

A visible website failure does not prove that WordPress is the cause. Start at the outermost failing boundary and work inward.

## Table of Contents

- [Troubleshooting principles](#troubleshooting-principles)
- [Preserve evidence first](#preserve-evidence-first)
- [Five-minute triage](#five-minute-triage)
- [Separate edge, origin, and application failures](#separate-edge-origin-and-application-failures)
- [Site unreachable or Cloudflare-branded errors](#site-unreachable-or-cloudflare-branded-errors)
- [Nginx configuration failure](#nginx-configuration-failure)
- [HTTP response problems](#http-response-problems)
- [PHP-FPM failures and timeouts](#php-fpm-failures-and-timeouts)
- [WordPress and application failures](#wordpress-and-application-failures)
- [MySQL connection and service failures](#mysql-connection-and-service-failures)
- [Memcached and object-cache problems](#memcached-and-object-cache-problems)
- [Stale content and cache confusion](#stale-content-and-cache-confusion)
- [Disk-space and inode exhaustion](#disk-space-and-inode-exhaustion)
- [Memory, load, and out-of-memory events](#memory-load-and-out-of-memory-events)
- [SSL and certificate failures](#ssl-and-certificate-failures)
- [SSH access problems](#ssh-access-problems)
- [SFTP access problems](#sftp-access-problems)
- [Permission problems](#permission-problems)
- [Cron tasks not running](#cron-tasks-not-running)
- [Installation or update failure](#installation-or-update-failure)
- [Staging and development problems](#staging-and-development-problems)
- [Backup or restore problems](#backup-or-restore-problems)
- [Narrow repair reference](#narrow-repair-reference)
- [When a full installation is appropriate](#when-a-full-installation-is-appropriate)
- [Diagnostic sharing and redaction](#diagnostic-sharing-and-redaction)
- [External monitoring](#external-monitoring)
- [Final escalation checklist](#final-escalation-checklist)
- [Related guides](#related-guides)

## Troubleshooting principles

Use these rules during an incident:

1. preserve logs and current configuration before changing the server
2. confirm the exact symptom, hostname, time, and affected environment
3. check disk, memory, failed services, and Nginx syntax first
4. separate Cloudflare behavior from direct origin behavior
5. inspect the relevant service log and systemd journal
6. repair only the affected layer when possible
7. verify the public website after every change
8. use a full `ss-install` only when broad reconciliation is actually required

Do not repeatedly restart every service, purge every cache, reset all permissions, or reinstall the complete stack without first collecting evidence. Those actions can hide the original cause and create new load or availability problems.

## Preserve evidence first

A full SlickStack installation truncates files matching:

```text
/var/www/logs/*.log
```

The `ss empty logs` command does the same without archiving them first.

Before reinstalling, clearing logs, or making broad changes, preserve the current evidence:

```bash
sudo mkdir -p /root/slickstack-incident
sudo cp -a /var/www/logs /root/slickstack-incident/logs
sudo journalctl -b > /root/slickstack-incident/journal-current-boot.log
sudo systemctl --failed > /root/slickstack-incident/failed-services.txt
sudo ss -lntup > /root/slickstack-incident/listeners.txt
sudo nginx -T > /root/slickstack-incident/nginx-config.txt 2>&1
sudo cp -a /var/www/ss-config /root/slickstack-incident/ss-config
```

The copied files can contain domains, internal paths, client addresses, credentials, and private configuration. Keep them root-only and redact them before sharing.

When compromise is suspected, avoid modifying more state than necessary. Preserve provider logs, Cloudflare events, database dumps, WordPress files, and any available previous-boot journal before attempting repair.

See [Logging](logging.md), [Security](security.md), and [Backups](backups.md).

## Five-minute triage

Start with these commands:

```bash
date
uptime
free -h
df -h /
df -i /
systemctl --failed
systemctl status nginx --no-pager
systemctl status mysql --no-pager
systemctl status memcached --no-pager
systemctl status cron --no-pager
systemctl list-units --type=service --all | grep -E 'php.*fpm'
sudo nginx -t
sudo ss -lntup
```

Then inspect recent logs:

```bash
sudo tail -n 100 /var/www/logs/nginx-error.log
sudo tail -n 100 /var/www/logs/php-error.log
sudo journalctl -p err --since '1 hour ago' --no-pager
```

Check the public response:

```bash
curl -I https://example.com
```

Replace `example.com` with the affected production, staging, or development hostname.

This first pass answers several important questions:

- Is the root filesystem or inode table full?
- Did the kernel kill a process because of memory pressure?
- Is a service inactive or failed?
- Does Nginx configuration parse successfully?
- Are expected ports listening?
- Does the public request reach Cloudflare or the origin?
- Do the Nginx or PHP logs show a current error?

## Separate edge, origin, and application failures

A proxied public request normally tests Cloudflare and the origin together:

```bash
curl -I https://example.com
```

Test local Nginx and hostname routing directly from the server:

```bash
curl -kI --resolve example.com:443:127.0.0.1 https://example.com/
```

The `-k` option is appropriate only for this local diagnostic because the origin may use SlickStack's self-signed fallback certificate. It disables certificate verification for that one command.

Interpret the results broadly:

| Public request | Local origin request | Likely boundary |
| :-- | :-- | :-- |
| Fails | Works | DNS, Cloudflare, provider network, or origin-access restriction |
| Fails | Fails | Nginx, TLS, PHP-FPM, WordPress, local database, or server resources |
| Works | Fails | The public response may be cached at Cloudflare or another edge layer |
| Works | Works | Intermittent, path-specific, authenticated, browser-cache, or application issue |

A successful `curl -I` only proves that one request returned headers. It does not verify login, checkout, REST API, WP-Cron, database writes, uploads, or uncached PHP behavior.

See [Architecture](architecture.md), [Cloudflare](cloudflare.md), and [Caching](caching.md).

## Site unreachable or Cloudflare-branded errors

When the browser shows a Cloudflare error page, first determine whether Cloudflare can reach a healthy origin.

Check:

```bash
sudo nginx -t
systemctl status nginx --no-pager
sudo ss -lntp | grep -E ':(80|443)\b'
sudo iptables -L INPUT -n -v --line-numbers
curl -kI --resolve example.com:443:127.0.0.1 https://example.com/
```

Also confirm:

- the production DNS record points to the intended server
- the Cloudflare proxy state is intentional
- the server public address has not changed
- provider firewall rules allow the intended traffic
- ports `80` and `443` are reachable
- Nginx is listening on IPv4 and, where expected, IPv6
- the active certificate files exist and Nginx can read them
- Authenticated Origin Pulls is enabled on both sides when used
- `CLOUDFLARE_IPS_ONLY` is not causing the known real-IP conflict

The normal recommendation is:

```bash
CLOUDFLARE_REAL_IPS="true"
CLOUDFLARE_IPS_ONLY="false"
```

The current Nginx Cloudflare-only allowlist conflicts with real visitor IP restoration. Do not enable it casually as an emergency fix.

When Authenticated Origin Pulls is enabled in SlickStack but not yet enabled for the Cloudflare zone, public requests can receive `403` responses. Enable the Cloudflare side first, then rebuild and test the Nginx configuration.

Rebuild Cloudflare-related Nginx files only when needed:

```bash
sudo bash /var/www/ss-install-nginx-config
```

This operation replaces managed Nginx files and restarts or reloads Nginx. Run `sudo nginx -t` afterward and test both the public and local origin paths.

## Nginx configuration failure

Symptoms can include:

- Nginx fails to start or reload
- the website is unreachable
- a recent configuration change causes immediate errors
- `nginx -t` reports an invalid directive, missing file, duplicate listener, or certificate problem

Start with:

```bash
sudo nginx -t
systemctl status nginx --no-pager
journalctl -u nginx --no-pager -n 150
sudo tail -n 150 /var/www/logs/nginx-error.log
```

Inspect the generated configuration and approved includes:

```bash
sudo ls -la /var/www/sites/
sudo ls -la /var/www/sites/includes/
sudo nginx -T > /tmp/nginx-effective.txt 2>&1
```

Do not add arbitrary files to `/etc/nginx/conf.d/` or assume random files under `/var/www/sites/includes/` will load. SlickStack uses explicit approved include names.

When a direct edit to generated Nginx configuration caused the problem, restore the managed state with:

```bash
sudo bash /var/www/ss-install-nginx-config
```

Use the complete installer only when other layers are also missing or inconsistent.

See [Nginx](nginx.md).

## HTTP response problems

Status codes identify the response layer but do not always identify the root cause.

### `403 Forbidden`

Possible causes include:

- Authenticated Origin Pulls enabled only at the origin
- a managed origin gate or access rule
- Nginx rate or security rules
- guest protection on staging or development
- denied PHP entry points
- application authorization
- file or directory permissions

Check:

```bash
sudo tail -n 100 /var/www/logs/nginx-access.log
sudo tail -n 100 /var/www/logs/nginx-error.log
grep '^CLOUDFLARE_' /var/www/ss-config
```

### `404 Not Found`

Confirm whether the missing path is:

- a real static file
- a WordPress route expected to reach `index.php`
- an environment hostname using the wrong web root
- a blocked or unsupported PHP file
- a cached stale response

Check the Nginx access log and WordPress permalink behavior before changing the server block.

### `429 Too Many Requests`

SlickStack includes Nginx request limits. Confirm the request path, client address, and real-IP handling. Without correct Cloudflare real-IP restoration, many visitors can share one apparent proxy address and consume the same limit bucket.

### `500 Internal Server Error`

Check PHP and WordPress first:

```bash
sudo tail -n 150 /var/www/logs/php-error.log
sudo tail -n 150 /var/www/logs/nginx-error.log
journalctl -u 'php*-fpm.service' --no-pager -n 150
```

### `502 Bad Gateway`

This usually indicates that Nginx could not obtain a valid response from PHP-FPM. Confirm the PHP-FPM service and local listener:

```bash
systemctl status php*-fpm.service --no-pager
sudo ss -lntp | grep ':9000'
journalctl -u 'php*-fpm.service' --no-pager -n 150
```

### `503 Service Unavailable`

SlickStack deliberately returns `503` when the production maintenance file exists. Check:

```bash
ls -l /var/www/html/maintenance.html
```

Disable managed maintenance mode when it was left active unintentionally:

```bash
sudo bash /var/www/ss-maintenance-disable
```

A `503` can also come from overload or an upstream failure, so inspect logs and service state rather than deleting files blindly.

### `504 Gateway Timeout`

Check PHP-FPM saturation, long PHP execution, database latency, remote API waits, and the configured FastCGI timeout. A restart can temporarily clear symptoms without fixing the slow request.

## PHP-FPM failures and timeouts

SlickStack normally uses one PHP-FPM service and one local `www` pool for production, staging, development, Adminer, and Multisite requests.

Check the service and listener:

```bash
systemctl status php*-fpm.service --no-pager
journalctl -u 'php*-fpm.service' --no-pager -n 150
sudo ss -lntp | grep ':9000'
sudo tail -n 150 /var/www/logs/php-error.log
```

Look for:

- invalid PHP or pool configuration
- missing extensions
- memory exhaustion or out-of-memory kills
- process-manager worker limits
- request termination timeouts
- permission-denied errors
- plugin or theme fatal errors
- slow external requests
- PHP-FPM service active but no listener on `127.0.0.1:9000`

Check recent kernel memory events:

```bash
journalctl -k --since '1 hour ago' | grep -Ei 'out of memory|oom|killed process'
```

A narrow restart is:

```bash
sudo bash /var/www/ss-restart-php
```

Rebuild the managed PHP configuration when it is missing or invalid:

```bash
sudo bash /var/www/ss-install-php-config
```

The installer recalculates PHP memory, OPcache, and pool values from physical RAM. Direct edits under `/etc/php/` are not persistent.

The two-minute SlickStack watchdog checks only whether the PHP-FPM systemd unit is active. It does not test the listener, worker availability, response latency, or WordPress execution. An active but overloaded PHP-FPM service can therefore remain unhealthy.

See [PHP-FPM](php-fpm.md) and [Monitoring](monitoring.md).

## WordPress and application failures

When Nginx and PHP-FPM are healthy but WordPress fails, check:

```bash
sudo -u www-data wp --path=/var/www/html core version
sudo -u www-data wp --path=/var/www/html option get siteurl
sudo -u www-data wp --path=/var/www/html plugin status
sudo -u www-data wp --path=/var/www/html theme status
sudo tail -n 150 /var/www/logs/php-error.log
```

Production WordPress debugging is disabled by default. Staging and development normally log WordPress debugging to:

```text
/var/www/html/staging/wp-content/debug.log
/var/www/html/dev/wp-content/debug.log
```

Use those environments to reproduce application problems when practical. Do not leave verbose production debugging enabled indefinitely; debug logs can expose sensitive data.

Common application causes include:

- plugin or theme fatal errors
- incompatible PHP versions or extensions
- invalid generated `wp-config.php`
- database connection failure
- stale object or page cache
- incorrect `home` or `siteurl`
- file permissions preventing updates or uploads
- exhausted disk space
- remote API or payment-provider latency

Rebuild only generated WordPress configuration with:

```bash
sudo bash /var/www/ss-install-wordpress-config
```

Refresh WordPress Core packages with:

```bash
sudo bash /var/www/ss-install-wordpress-packages
```

Refreshing Core does not diagnose malicious plugins, compromised themes, altered uploads, or database compromise.

See [WordPress](wordpress.md).

## MySQL connection and service failures

For the default local database, check:

```bash
systemctl status mysql --no-pager
journalctl -u mysql --no-pager -n 150
mysqladmin --user=root --host=localhost --protocol=socket ping
sudo ss -lntp | grep ':3306'
df -h /
df -i /
```

The MySQL files under `/var/www/logs/` may remain empty because the current MySQL 8 template does not enable those custom file targets. Use the systemd journal first.

Common causes include:

- full disk or inode exhaustion
- MySQL crash recovery
- invalid generated `my.cnf`
- insufficient memory
- damaged tables or storage
- incorrect WordPress credentials
- local MySQL not listening on the configured address
- remote database networking or provider restrictions

Restart local MySQL narrowly with:

```bash
sudo bash /var/www/ss-restart-mysql
```

Rebuild its managed configuration with:

```bash
sudo bash /var/www/ss-install-mysql-config
```

Create a database dump before risky repair or reconfiguration whenever the service can still respond:

```bash
sudo bash /var/www/ss-dump-database
```

When `DB_REMOTE="true"`, SlickStack does not install, tune, restart, monitor, or recover the remote server. Check the remote provider, DNS, network access, TLS requirements, credentials, and service status separately.

The local MySQL watchdog waits for three failed two-minute checks before attempting a restart. It does not monitor remote MySQL or query performance.

See [MySQL](mysql.md).

## Memcached and object-cache problems

Memcached is non-authoritative. Permanent WordPress data remains in MySQL.

Check:

```bash
systemctl status memcached --no-pager
journalctl -u memcached --no-pager -n 100
sudo ss -lntp | grep ':11211'
printf 'stats\r\nquit\r\n' | nc 127.0.0.1 11211
ls -l /var/www/html/wp-content/object-cache.php
```

Clear Memcached when cached objects appear stale or corrupt:

```bash
sudo bash /var/www/ss-purge-memcached
```

Restart it when the daemon itself is unhealthy:

```bash
sudo bash /var/www/ss-restart-memcached
```

A purge or restart can temporarily increase MySQL and PHP load while WordPress repopulates cached objects. The SlickStack two-minute watchdog does not monitor Memcached.

Staging and development do not receive the production `object-cache.php` drop-in under the standard configuration.

See [Memcached](memcached.md).

## Stale content and cache confusion

SlickStack uses independent cache layers:

- Cloudflare edge cache
- browser cache
- Nginx FastCGI cache
- Nginx open-file cache
- PHP OPcache
- Memcached
- WordPress transients

Clearing one layer does not clear the others.

For a broad origin-side purge:

```bash
ss purge
```

This clears OPcache, Memcached, Nginx FastCGI cache, and WordPress transients. It does not purge Cloudflare or visitor browser caches.

Prefer a targeted purge when the stale layer is known:

```bash
ss purge nginx
ss purge opcache
ss purge memcached
ss purge transients
```

Before purging, compare:

```bash
curl -I https://example.com
curl -kI --resolve example.com:443:127.0.0.1 https://example.com/
```

When the origin is fresh but the public response is stale, investigate Cloudflare. When one browser is stale but command-line requests are fresh, investigate browser caching.

Purging every cache during high traffic can create a cold-cache load spike.

See [Caching](caching.md).

## Disk-space and inode exhaustion

A full filesystem can break MySQL, PHP sessions, uploads, package operations, logs, caches, certificate renewal, and backups.

Check both space and inodes:

```bash
df -h
df -i
sudo du -xhd1 /var /home 2>/dev/null | sort -h
sudo du -xhd1 /var/www 2>/dev/null | sort -h
sudo find /var/www -xdev -type f -printf '%s %p\n' 2>/dev/null | sort -n | tail -n 30
```

Check for deleted files still held open:

```bash
sudo lsof +L1
```

Common growth areas include:

```text
/var/www/backups/
/var/www/cache/
/var/www/logs/
/var/www/html/wp-content/uploads/
/var/lib/mysql/
/var/log/journal/
/tmp/
```

Do not delete database files, active certificates, or unknown system paths merely to recover space. Move or remove verified disposable backups, caches, temporary files, and obsolete artifacts first.

After restoring space, verify MySQL, PHP-FPM, Nginx, Cron, and the public site. Services that failed while the disk was full may need a targeted restart.

## Memory, load, and out-of-memory events

Check:

```bash
free -h
uptime
nproc
ps aux --sort=-%mem | head -n 20
ps aux --sort=-%cpu | head -n 20
journalctl -k --since today | grep -Ei 'out of memory|oom|killed process'
```

Linux intentionally uses available RAM for filesystem cache. Focus on `available` memory, swap activity, OOM events, sustained load relative to CPU cores, and the largest processes.

SlickStack's `ss monitor` display labels load average as CPU and appends a percent sign. Use `uptime`, `top`, and process lists for accurate interpretation.

Possible responses include:

- stop an unexpected runaway process
- identify a slow plugin, query, bot, import, or backup
- reduce concurrent expensive work
- schedule heavy cron tasks at different times
- resize the server and rerun PHP/MySQL automatic tuning
- restart only the affected service after preserving evidence

Repeated OOM kills are not solved reliably by repeatedly restarting PHP or MySQL.

See [Monitoring](monitoring.md) and [PHP-FPM](php-fpm.md).

## SSL and certificate failures

Start with:

```bash
sudo nginx -t
systemctl status nginx --no-pager
openssl x509 -in /var/www/certs/slickstack.crt -noout -subject -issuer -dates
sudo certbot certificates
```

When Certbot mode is intended, also check:

```bash
openssl x509 -in /var/www/certs/fullchain.pem -noout -subject -issuer -dates
ls -la /etc/letsencrypt/live/slickstack/
ls -la /var/www/certs/
ls -la /var/www/certs/keys/
grep '^SSL_TYPE=' /var/www/ss-config
```

Verify:

- DNS resolves to the intended server or Cloudflare zone
- the requested hostnames match SlickStack's supported domain layout
- the HTTP ACME challenge path is reachable for normal single-site issuance
- Cloudflare credentials are correct for Multisite wildcard validation
- the Certbot source files exist
- SlickStack's stable certificate symlinks resolve correctly
- the private key matches the certificate
- `SSL_TYPE` matches the intended active mode
- Nginx syntax succeeds before reload

When Certbot issuance fails, the Nginx installer intentionally retains or falls back to the self-signed OpenSSL certificate rather than installing broken paths.

Regenerate the fallback certificate with:

```bash
sudo bash /var/www/ss-encrypt-openssl
```

Request or renew Certbot with:

```bash
sudo bash /var/www/ss-encrypt-certbot
```

Rebuild and activate the selected Nginx certificate mode with:

```bash
sudo bash /var/www/ss-install-nginx-config
```

Do not loosen private-key permissions to solve an Nginx error.

See [SSL (Certificates)](ssl.md) and [Cloudflare](cloudflare.md).

## SSH access problems

Keep an existing working SSH session open while testing changes. Use the cloud provider console when normal SSH access is unavailable.

The current managed SSH design:

- listens on TCP port `22`
- disables direct root login
- allows the managed sudo and SFTP groups
- uses key authentication at all times
- disables password authentication when `SSH_KEYS="true"`

Check from the provider console:

```bash
sudo sshd -t
systemctl status ssh --no-pager
journalctl -u ssh --no-pager -n 150
sudo ss -lntp | grep ':22'
sudo iptables -L INPUT -n -v --line-numbers
getent passwd
getent group sudo-ssh
```

When key-only access is active, verify:

```bash
ls -la /var/www/auth/
cat /var/www/auth/authorized_keys
```

Do not publish private keys or the contents of `ss-config`.

`SSH_RESTRICT_IP` currently does not enforce a source-IP restriction because the active SSH template lacks the related placeholder. Check provider firewall rules separately when access appears restricted.

Rebuild managed SSH configuration only with provider-console access and a working backup session:

```bash
sudo bash /var/www/ss-install-ubuntu-ssh
sudo sshd -t
```

Verify the sudo user in a second connection before ending the console or original session.

See [Ubuntu](ubuntu.md) and [Security](security.md).

## SFTP access problems

The SFTP account is intentionally jailed under:

```text
/var/www
```

It uses `internal-sftp` and cannot open a normal shell or terminal.

Confirm:

```bash
getent passwd "$(grep '^SFTP_USER=' /var/www/ss-config | cut -d '"' -f 2)"
getent group sftp-only
sudo sshd -t
systemctl status ssh --no-pager
```

Permission or chroot ownership changes can break SFTP. Restore the managed policy with:

```bash
sudo bash /var/www/ss-perms
```

Reset the SFTP password when password authentication is intended:

```bash
sudo bash /var/www/ss-reset-password-sftp
```

Do not give the SFTP account a normal shell merely to bypass the managed jail.

## Permission problems

Symptoms include:

- WordPress cannot upload or update
- Nginx or PHP cannot read generated files
- SFTP cannot edit expected content
- services cannot write logs
- Cron refuses to load the root schedule
- certificate keys become unreadable

Inspect the specific path first:

```bash
stat -c '%U:%G %a %n' /var/www
stat -c '%U:%G %a %n' /var/www/html
stat -c '%U:%G %a %n' /var/www/html/wp-config.php
stat -c '%U:%G %a %n' /var/www/logs
stat -c '%U:%G %a %n' /var/spool/cron/crontabs/root
```

Restore the complete managed policy with:

```bash
sudo bash /var/www/ss-perms
```

Avoid broad emergency commands such as:

```bash
chmod -R 777 /var/www
chown -R root:root /var/www/html
chown -R www-data:www-data /var/www
```

Those commands can expose PHP files and credentials, break SFTP, prevent service writes, and be reverted by scheduled permission maintenance.

Use module-specific permission scripts when only one layer is affected.

See [Permissions](permissions.md).

## Cron tasks not running

Check the daemon, root schedule, files, timestamps, and journals:

```bash
systemctl status cron --no-pager
journalctl -u cron --since '2 hours ago' --no-pager
sudo crontab -l
sudo ls -la /var/www/crons/
sudo ls -la /var/www/crons/custom/
sudo ls -lt /var/www/meta/timestamps/ | head -n 30
```

Confirm:

- the expected wrapper is present and executable
- root's crontab contains the wrapper
- the task's `INTERVAL_SS_*` value matches the wrapper name
- the task is not configured as `never`
- the related script exists
- another copy of the same wrapper is not holding its `flock`
- a custom task uses absolute paths and does not wait for input
- output is redirected to a retained log

Check active wrapper processes before deleting a lock or running the wrapper manually:

```bash
ps aux | grep '/var/www/crons/'
```

Manual execution runs every bundled and custom task assigned to the wrapper and bypasses the root crontab's normal `flock` invocation:

```bash
sudo bash /var/www/crons/06-cron-hourly
```

Restore only the managed root schedule with:

```bash
sudo bash /var/www/ss-install-ubuntu-crontab
```

Cron output is normally redirected to `/dev/null`, so silence does not prove success. WP-CLI cron errors are appended to:

```text
/var/www/logs/wp-cli.log
```

See [Cron Jobs](cron.md).

## Installation or update failure

Do not immediately rerun the full installer in a loop.

Record:

- the last visible stage
- the exact error
- the affected script
- recent package-manager output
- service status
- disk and inode capacity
- the current `SS_BUILD`
- whether `ss-config` still parses

Check:

```bash
sudo bash -n /var/www/ss-config
grep -n '"@' /var/www/ss-config
grep '^SS_BUILD=' /var/www/ss-config
df -h /
df -i /
systemctl --failed
sudo nginx -t
```

When the installer reports a build mismatch, use the normal migration sequence:

```bash
sudo bash /var/www/ss-update-config
sudo bash /var/www/ss-install
```

Review the migrated file before installing. Do not manually change `SS_BUILD` merely to bypass compatibility checks.

When one module failed, rerun its narrow installer after correcting the cause. Examples:

```bash
sudo bash /var/www/ss-install-nginx-config
sudo bash /var/www/ss-install-php-config
sudo bash /var/www/ss-install-mysql-config
sudo bash /var/www/ss-install-wordpress-config
sudo bash /var/www/ss-install-ubuntu-crontab
```

Every full installation:

- upgrades Ubuntu packages and may install a new kernel
- rewrites managed configuration
- may overwrite staging
- truncates SlickStack logs
- resets permissions
- purges caches
- restarts services

Preserve evidence and backups before rerunning it.

See [Installation](installation.md), [Updates](updates.md), and [SS-Config](ss-config.md).

## Staging and development problems

Confirm the environment settings and generated paths:

```bash
grep -E '^(STAGING_SITE|DEV_SITE|SS_SYNC_STAGING|SS_SYNC_DEVELOPMENT|SS_LOCKDOWN|WP_MULTISITE)=' /var/www/ss-config
ls -la /var/www/html/staging/
ls -la /var/www/html/dev/
ls -la /var/www/sites/
```

Check the local hostname directly:

```bash
curl -kI --resolve staging.example.com:443:127.0.0.1 https://staging.example.com/
curl -kI --resolve dev.example.com:443:127.0.0.1 https://dev.example.com/
```

Staging and development share the same Nginx service, PHP-FPM pool, server resources, and much of the same generated configuration as production. A resource-heavy test can affect production.

Staging normally shares production uploads. Do not troubleshoot staging media by deleting or rewriting uploads without recognizing the production impact.

Synchronization commands overwrite databases and selected files. Push commands overwrite production. Preserve current data before using them as repair tools.

See [Staging & Dev](staging-dev.md).

## Backup or restore problems

A backup file existing is not proof that it is complete or restorable.

Check:

```bash
sudo ls -lh /var/www/backups/mysql/
sudo ls -lh /var/www/backups/html/
df -h /
```

Validate compressed archives before trusting them:

```bash
unzip -t archive.zip
gzip -t archive.sql.gz
tar -tf archive.tar.gz > /dev/null
```

For database dumps, inspect the file size and initial SQL structure without publishing its contents.

Remote backup failures can involve:

- missing or invalid credentials
- provider-side permissions
- unavailable remote storage
- network or DNS failures
- insufficient local temporary space
- a scheduled wrapper not running
- an incomplete local dump

Do not test a production restore casually. Use a disposable server or isolated environment when possible.

See [Backups](backups.md).

## Narrow repair reference

Prefer the narrowest command that matches the diagnosed layer:

| Problem | Narrow operation |
| :-- | :-- |
| Refresh SlickStack scripts | `sudo bash /var/www/ss-check` |
| Rebuild Nginx configuration | `sudo bash /var/www/ss-install-nginx-config` |
| Restart Nginx | `sudo bash /var/www/ss-restart-nginx` |
| Rebuild PHP configuration | `sudo bash /var/www/ss-install-php-config` |
| Restart PHP-FPM | `sudo bash /var/www/ss-restart-php` |
| Rebuild MySQL configuration | `sudo bash /var/www/ss-install-mysql-config` |
| Restart local MySQL | `sudo bash /var/www/ss-restart-mysql` |
| Rebuild Memcached configuration | `sudo bash /var/www/ss-install-memcached-config` |
| Restart Memcached | `sudo bash /var/www/ss-restart-memcached` |
| Rebuild WordPress configuration | `sudo bash /var/www/ss-install-wordpress-config` |
| Refresh WordPress Core | `sudo bash /var/www/ss-install-wordpress-packages` |
| Restore permissions | `sudo bash /var/www/ss-perms` |
| Restore root Cron schedule | `sudo bash /var/www/ss-install-ubuntu-crontab` |
| Rebuild SSH configuration | `sudo bash /var/www/ss-install-ubuntu-ssh` |
| Purge Nginx cache | `sudo bash /var/www/ss-purge-nginx` |
| Purge OPcache | `sudo bash /var/www/ss-purge-opcache` |
| Purge Memcached | `sudo bash /var/www/ss-purge-memcached` |
| Purge WordPress transients | `sudo bash /var/www/ss-purge-transients` |
| Disable maintenance mode | `sudo bash /var/www/ss-maintenance-disable` |
| Migrate `ss-config` | `sudo bash /var/www/ss-update-config` |

A narrow installer can still replace managed files and restart its service. Review the relevant component guide first.

## When a full installation is appropriate

Use:

```bash
sudo bash /var/www/ss-install
```

when:

- provisioning a new server
- applying broad `ss-config` changes
- repairing multiple missing or damaged modules
- resizing the server and regenerating automatic tuning
- deliberately reconciling the entire managed stack

Do not use a full installation merely because:

- one cache is stale
- one service needs a restart
- one log contains an application error
- one path has incorrect permissions
- one cron wrapper is missing
- one generated component file needs rebuilding

Before a full production run:

1. create and verify a fresh database dump
2. confirm important files have an independent backup
3. preserve current logs and journals
4. validate `ss-config`
5. confirm disk space and inodes
6. decide whether staging may be overwritten
7. keep provider-console and a working SSH session available
8. choose an appropriate maintenance window

## Diagnostic sharing and redaction

Do not publicly share the complete output of:

```bash
ss status
ss overview
sudo bash /var/www/ss-stack-overview
cat /var/www/ss-config
```

These can expose passwords, database credentials, addresses, private URLs, backup credentials, and key material.

Before sharing diagnostics, remove:

- passwords and API keys
- private and public SSH key material
- database credentials
- Cloudflare credentials
- Rclone and Rsync credentials
- Adminer paths
- private hostnames and addresses where necessary
- cookies, authorization headers, tokens, and customer data

Useful safer evidence usually includes:

- exact time and timezone
- affected hostname and path
- public and local-origin status results
- `nginx -t` output
- relevant redacted log lines
- service state
- Ubuntu and SlickStack build
- recent change or command
- whether production, staging, or development is affected

## External monitoring

SlickStack's built-in listeners can restart inactive PHP-FPM and Nginx services and can restart local MySQL after repeated failed pings. They do not provide complete monitoring.

Use external monitoring for:

- public HTTP and HTTPS availability
- response content and latency
- SSL expiration
- DNS failures
- Cloudflare and origin reachability
- disk, memory, load, and process thresholds
- backup completion
- restart and incident alerts

A service can remain active while returning errors, hanging, exhausting workers, or serving stale content. External checks should test the actual user-facing path.

## Final escalation checklist

Before escalating an unresolved issue, collect:

```bash
date
uptime
free -h
df -h /
df -i /
systemctl --failed
sudo nginx -t
sudo ss -lntup
```

Also preserve:

- recent Nginx error lines
- recent PHP error lines
- the relevant service journal
- the affected URL and response status
- the result of the local `--resolve` origin test
- the last successful and failed operation
- any recent `ss-config`, plugin, theme, DNS, firewall, certificate, or provider change

Then identify the narrowest responsible layer:

```text
DNS / Cloudflare / provider
Iptables / network
Nginx / TLS
PHP-FPM
WordPress application
MySQL
Memcached / cache
filesystem / resources
cron / automation
```

That layer should determine the next repair, not the visibility of the symptom alone.

## Related guides

- [Architecture](architecture.md)
- [Commands](commands.md)
- [Installation](installation.md)
- [Logging](logging.md)
- [Monitoring](monitoring.md)
- [Security](security.md)
- [Cloudflare](cloudflare.md)
- [Nginx](nginx.md)
- [PHP-FPM](php-fpm.md)
- [MySQL](mysql.md)
- [Memcached](memcached.md)
- [Caching](caching.md)
- [SSL (Certificates)](ssl.md)
- [Permissions](permissions.md)
- [Cron Jobs](cron.md)
- [Backups](backups.md)
- [Staging & Dev](staging-dev.md)
- [Ubuntu](ubuntu.md)
- [WordPress](wordpress.md)
