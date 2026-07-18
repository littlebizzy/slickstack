# Logging

SlickStack uses a combination of application log files under `/var/www/logs`, WordPress debug logs inside each site, service-specific files elsewhere under `/var/log`, and the systemd journal.

These sources are related, but they are not interchangeable. A service can write to a SlickStack-managed file, the systemd journal, both, or neither depending on its active configuration.

## Main log locations

The primary SlickStack log directory is:

```text
/var/www/logs/
```

The main files are:

| Log | Default purpose | Current status |
| :-- | :-- | :-- |
| `/var/www/logs/nginx-access.log` | Nginx HTTP request history | Active by default |
| `/var/www/logs/nginx-error.log` | Nginx errors at `error` level and above | Active by default |
| `/var/www/logs/php-error.log` | PHP and PHP-FPM errors | Active by default |
| `/var/www/logs/error.log` | OPcache error output | Configured target; may remain empty or absent until needed |
| `/var/www/logs/memcached.log` | Memcached daemon output | Active when Memcached is running |
| `/var/www/logs/wp-cli.log` | Standard error from SlickStack WP-CLI cron execution | Active when WP-Cron uses WP-CLI and produces errors |
| `/var/www/logs/mysql-error.log` | Reserved custom MySQL error log | Created by permissions workflow, but not enabled in the current MySQL 8 template |
| `/var/www/logs/mysql-gen.log` | Reserved MySQL general-query log | Created by permissions workflow, but not enabled in the current MySQL 8 template |
| `/var/www/logs/mysql-slow.log` | Reserved MySQL slow-query log | Created by permissions workflow, but not enabled in the current MySQL 8 template |

Other important logging sources include:

```text
/var/log/fail2ban.log
/var/www/html/wp-content/debug.log
/var/www/html/staging/wp-content/debug.log
/var/www/html/dev/wp-content/debug.log
```

The WordPress staging and development paths exist only when those environments are enabled.

## Nginx logs

The managed Nginx configuration hardcodes:

```nginx
access_log /var/www/logs/nginx-access.log;
error_log /var/www/logs/nginx-error.log error;
log_not_found off;
```

The access log records normal requests. It is the primary source for:

- requested paths
- response status codes
- client addresses after any active real-IP handling
- user agents and referrers included by the active Nginx log format
- traffic patterns used by the current Fail2ban Nginx jails

The error log records messages at the `error` severity or higher. Warnings and notices below that threshold are not included by default.

View recent entries:

```bash
sudo tail -n 100 /var/www/logs/nginx-access.log
sudo tail -n 100 /var/www/logs/nginx-error.log
```

Follow them in real time:

```bash
sudo tail -f /var/www/logs/nginx-access.log
sudo tail -f /var/www/logs/nginx-error.log
```

The installed Bash helpers also support:

```bash
ss tail nginx access log
ss tail nginx error log
```

Check Nginx configuration and journal output separately:

```bash
sudo nginx -t
systemctl status nginx
journalctl -u nginx --no-pager -n 100
```

An empty Nginx file does not prove that Nginx is healthy. Confirm the configured path, service status, file permissions, current traffic, and systemd journal.

See [Nginx](nginx.md) and [Cloudflare](cloudflare.md).

## PHP and PHP-FPM logs

Both the active PHP configuration and PHP-FPM configuration target:

```text
/var/www/logs/php-error.log
```

PHP is configured with:

```ini
error_reporting = E_ERROR | E_WARNING | E_PARSE
display_errors = Off
log_errors = On
error_log = /var/www/logs/php-error.log
```

PHP-FPM is configured with:

```ini
error_log = /var/www/logs/php-error.log
log_level = error
```

This deliberately avoids displaying PHP errors to visitors while retaining serious errors and warnings in the server log.

View or follow the log:

```bash
sudo tail -n 100 /var/www/logs/php-error.log
sudo tail -f /var/www/logs/php-error.log
```

The Bash helper is:

```bash
ss tail php error log
```

Check the PHP-FPM journal when the file does not explain startup, process-manager, socket, or service failures:

```bash
systemctl status php*-fpm.service
journalctl -u 'php*-fpm.service' --no-pager -n 100
```

### OPcache log

The active PHP template separately configures:

```ini
opcache.error_log=/var/www/logs/error.log
opcache.log_verbosity_level=1
```

This generic filename is specifically the OPcache error target in the current template. It is not a replacement for `php-error.log`.

Check it when troubleshooting OPcache startup, file-cache, shared-memory, or invalidation problems:

```bash
sudo tail -n 100 /var/www/logs/error.log
```

The file can remain empty or may not exist until OPcache has something to record.

See [PHP-FPM](php-fpm.md).

## WordPress debug logs

The production WordPress template uses:

```php
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );
```

Therefore, production does not create or populate `wp-content/debug.log` through the standard WordPress debug system by default.

The staging and development templates use:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Their default debug logs are:

```text
/var/www/html/staging/wp-content/debug.log
/var/www/html/dev/wp-content/debug.log
```

View them when those environments exist:

```bash
sudo tail -n 100 /var/www/html/staging/wp-content/debug.log
sudo tail -n 100 /var/www/html/dev/wp-content/debug.log
```

Do not enable verbose production debugging indefinitely. WordPress debug logs can expose paths, queries, plugin details, request data, tokens, email addresses, and other sensitive information.

SlickStack-generated `wp-config.php` files are managed and can be replaced. Persistent debugging-policy changes should be implemented through the SlickStack source or another supported workflow rather than relying on a one-time direct edit.

See [WordPress](wordpress.md).

## WP-CLI log

SlickStack creates:

```text
/var/www/logs/wp-cli.log
```

The standard cron wrappers append standard error from WP-CLI-based WP-Cron commands to this file:

```text
2>> /var/www/logs/wp-cli.log
```

Normal successful command output is not appended by that redirection. The file can therefore remain empty even while WP-Cron is running successfully.

View it with:

```bash
sudo tail -n 100 /var/www/logs/wp-cli.log
sudo tail -f /var/www/logs/wp-cli.log
```

When it contains repeated errors, also check:

```bash
sudo -u www-data wp --path=/var/www/html cron event run --due-now
sudo -u www-data wp --path=/var/www/html cron event list
```

For Multisite, inspect each affected site URL rather than assuming one successful network command proves every site is healthy.

## Memcached log

The managed Memcached configuration uses:

```text
logfile /var/www/logs/memcached.log
```

The permission workflow creates the file with:

```text
memcache:memcache 0640
```

View it with:

```bash
sudo tail -n 100 /var/www/logs/memcached.log
```

Also check the service journal and status:

```bash
systemctl status memcached
journalctl -u memcached --no-pager -n 100
```

An empty Memcached log is not necessarily an error. Confirm that the daemon is active, listening only on the expected loopback addresses, and responding to local requests.

See [Memcached](memcached.md).

## MySQL logging

The permissions workflow creates:

```text
/var/www/logs/mysql-error.log
/var/www/logs/mysql-gen.log
/var/www/logs/mysql-slow.log
```

and assigns them to `mysql:mysql` with mode `0640`.

However, the current MySQL 8 template leaves these directives commented out:

```ini
# general_log = 0
# slow_query_log = 1
# log_error = /var/www/logs/mysql-error.log
# general_log_file = /var/www/logs/mysql-gen.log
# slow_query_log_file = /var/www/logs/mysql-slow.log
```

Therefore, the presence of those files does not mean MySQL is writing to them. They may remain empty indefinitely under the default configuration.

Use the service journal first for current MySQL startup and runtime failures:

```bash
systemctl status mysql
journalctl -u mysql --no-pager -n 100
```

Also inspect MySQL's active runtime variables before assuming a file path:

```bash
sudo mysql -NBe "SHOW VARIABLES WHERE Variable_name IN ('log_error','general_log','general_log_file','slow_query_log','slow_query_log_file');"
```

The general query log can grow rapidly and expose SQL statements, user data, and application behavior. Do not enable it permanently as a casual troubleshooting measure.

See [MySQL](mysql.md).

## Fail2ban log

Fail2ban normally uses:

```text
/var/log/fail2ban.log
```

View it with:

```bash
sudo tail -n 100 /var/log/fail2ban.log
sudo tail -f /var/log/fail2ban.log
```

The Bash helper is:

```bash
ss tail fail2ban log
```

Also check:

```bash
systemctl status fail2ban
journalctl -u fail2ban --no-pager -n 100
sudo fail2ban-client status
```

`ss-empty-logs` does not truncate `/var/log/fail2ban.log` because that command only targets `/var/www/logs/*.log`.

Fail2ban is planned for removal in favor of a simpler Iptables-only approach. Do not build new long-term logging or monitoring workflows around its current jails.

See [Fail2ban](fail2ban.md).

## Systemd journals

Many service events never appear in `/var/www/logs`. Package startup failures, permission errors, crashes, restart loops, missing dependencies, unit configuration problems, and kernel interactions are often clearer in the systemd journal.

Common commands include:

```bash
systemctl --failed
journalctl -p err --since today
journalctl -u nginx --since today
journalctl -u mysql --since today
journalctl -u memcached --since today
journalctl -u fail2ban --since today
journalctl -u 'php*-fpm.service' --since today
```

Follow one service live:

```bash
journalctl -fu nginx
```

Show messages from the current boot:

```bash
journalctl -b
```

Show the previous boot when persistent journal storage is available:

```bash
journalctl -b -1
```

SlickStack does not merge systemd journals into `/var/www/logs`. Clearing SlickStack logs does not clear the journal, and journal cleanup does not truncate SlickStack files.

## Ownership and permissions

The main directory uses:

```text
/var/www/logs root:www-data 0775
```

Important file policies include:

```text
nginx-access.log root:www-data 0660
nginx-error.log  root:www-data 0660
php-error.log    www-data:www-data 0660
wp-cli.log       root:www-data 0660
memcached.log    memcache:memcache 0640
mysql*.log       mysql:mysql 0640
```

Different owners are intentional because each daemon writes under a different Unix identity.

Do not broadly fix logging with commands such as:

```bash
chmod -R 777 /var/www/logs
chown -R www-data:www-data /var/www/logs
```

Those changes can expose sensitive data, prevent a service from writing, and be replaced by the next permission reset.

Use:

```bash
sudo bash /var/www/ss-perms
```

Then verify specific paths:

```bash
stat -c '%U:%G %a %n' /var/www/logs
stat -c '%U:%G %a %n' /var/www/logs/*.log
```

See [Permissions](permissions.md).

## Clearing logs

Run the managed clearing script with:

```bash
sudo bash /var/www/ss-empty-logs
```

or:

```bash
ss empty logs
ss null logs
```

The script performs:

```text
truncate /var/www/logs/*.log
```

It preserves the files but reduces every matching `.log` file in `/var/www/logs` to zero bytes.

It does not:

- archive the previous contents
- compress old logs
- create dated rotations
- preserve a copy elsewhere
- clear systemd journals
- clear `/var/log/fail2ban.log`
- clear WordPress `wp-content/debug.log` files
- selectively retain the newest entries

Treat it as destructive. Copy required evidence before running it:

```bash
sudo mkdir -p /var/www/backups/logs
sudo cp -a /var/www/logs/. /var/www/backups/logs/
```

A full `ss-install` unconditionally runs `ss-empty-logs` before the final permission reset and service restarts. Therefore, rerunning the installer destroys the current contents of `/var/www/logs/*.log` even when `INTERVAL_SS_EMPTY_LOGS="never"`.

Collect or copy relevant logs before using `ss-install` during an incident.

## Scheduled clearing

The default is:

```bash
INTERVAL_SS_EMPTY_LOGS="never"
```

Supported values are:

```text
half-daily
 daily
half-weekly
weekly
half-monthly
never
```

The actual schedules are:

| Value | Actual schedule |
| :-- | :-- |
| `half-daily` | Every 12 hours |
| `daily` | Every day at 00:00 |
| `half-weekly` | Every three days at 00:00 |
| `weekly` | Sunday at 00:00 |
| `half-monthly` | The 13th of each month at 00:00 |
| `never` | Disabled |

Because clearing is truncation rather than rotation, frequent schedules significantly reduce the available incident history.

See [Cron Jobs](cron.md).

## Rotation and retention

The current SlickStack implementation does not install a managed Logrotate policy for `/var/www/logs`.

The Ubuntu utilities installer currently leaves Logrotate installation commented out, and the repository's Logrotate template belongs to an obsolete Ubuntu 16.04 path rather than the active Ubuntu 24.04 workflow.

Current retention therefore depends mainly on:

- natural file growth
- optional manual copying or external collection
- optional `ss-empty-logs` truncation
- unconditional truncation during `ss-install`

For long-term retention, copy logs to a separate protected location or use an external logging platform before enabling automated clearing. Any custom rotation must preserve the correct owners and ensure daemons reopen or continue writing to the intended files.

Do not assume renaming a live log is sufficient. Some processes keep the old file descriptor open until reloaded or restarted.

## Searching and filtering

Find recent errors:

```bash
grep -iE 'error|critical|fatal|failed|panic' /var/www/logs/*.log
```

Count Nginx status codes:

```bash
awk '{print $9}' /var/www/logs/nginx-access.log | sort | uniq -c | sort -nr
```

Show recent 5xx responses:

```bash
awk '$9 ~ /^5/' /var/www/logs/nginx-access.log | tail -n 100
```

Search one request path:

```bash
grep -F '/wp-login.php' /var/www/logs/nginx-access.log | tail -n 100
```

Search one client address:

```bash
grep -F '192.0.2.10' /var/www/logs/nginx-access.log
```

Search within a time window in the systemd journal:

```bash
journalctl --since '2026-07-18 10:00:00' --until '2026-07-18 11:00:00'
```

Adjust the date and time for the incident and server timezone.

## Sensitive data and redaction

Logs can contain:

- IP addresses
- domains and internal hostnames
- request paths and query strings
- referrers and user agents
- email addresses and usernames
- database queries
- plugin and theme paths
- filesystem paths
- authentication tokens or reset links
- cookies or headers when custom logging is enabled
- customer or order information

Before sharing a log publicly:

1. copy the relevant portion rather than the entire file
2. remove credentials, cookies, tokens, and private keys
3. redact customer information and private URLs
4. replace public IP addresses when they are not necessary
5. keep timestamps and error context intact
6. state which environment and service produced the log

Do not publish output from `ss-stack-overview` without careful redaction. That command currently prints sudo, SFTP, guest, database, Rsync, Adminer, domain, and other sensitive settings.

## Troubleshooting

### Log file is missing

Run:

```bash
sudo bash /var/www/ss-perms
ls -la /var/www/logs
```

Then verify that the relevant service is installed, its active configuration targets that path, and the service has started.

Some files are created only when needed. Others, such as the reserved MySQL files, can exist without being active logging targets.

### Log file is empty

Check:

- whether requests or errors occurred after the last truncation
- whether `ss-install` recently ran
- whether `INTERVAL_SS_EMPTY_LOGS` is enabled
- whether the service writes to the systemd journal instead
- whether the configured log path is active
- whether a process still has an older renamed file open
- whether the log level excludes the event being investigated

Useful commands:

```bash
stat /var/www/logs/example.log
lsof /var/www/logs/example.log
systemctl status SERVICE
journalctl -u SERVICE --no-pager -n 100
```

Replace the placeholders with the actual file and service.

### Service cannot write to its log

Check the directory and file separately:

```bash
namei -l /var/www/logs/example.log
stat -c '%U:%G %a %n' /var/www/logs /var/www/logs/example.log
```

Run `ss-perms`, then review the service user and active configuration. Do not make the file world-writable.

### Disk space is low

Find the largest SlickStack logs:

```bash
sudo du -ah /var/www/logs | sort -h | tail -n 20
```

Review system journal use:

```bash
journalctl --disk-usage
```

Preserve required incident evidence, then truncate or rotate deliberately. Also confirm that backups, caches, database dumps, uploads, and system journals are not the actual disk-space cause.

### Errors disappeared after reinstalling

This is expected because `ss-install` runs `ss-empty-logs` before completing. Check the systemd journal, WordPress debug logs, external monitoring, and any copies made before the reinstall.

## Recommended incident sequence

Before changing configuration or restarting several services:

```bash
date
uptime
df -h
free -h
systemctl --failed
sudo nginx -t
sudo tail -n 200 /var/www/logs/nginx-error.log
sudo tail -n 200 /var/www/logs/php-error.log
journalctl -p err --since '1 hour ago'
```

Then:

1. note the exact start time and affected URL or operation
2. reproduce the issue once when safe
3. inspect Nginx access and error logs
4. inspect PHP and WordPress logs
5. inspect the relevant service journal
6. check MySQL or Memcached only when the request depends on them
7. copy the evidence before running `ss-install` or `ss-empty-logs`
8. make one narrow change
9. retest and compare timestamps

Avoid clearing logs before understanding the incident.

## Managed-file boundaries

SlickStack manages:

- the main `/var/www/logs` directory
- Nginx, PHP-FPM, Memcached, and WP-CLI log targets
- permissions for several active and reserved log files
- the optional log-clearing schedule
- unconditional clearing during the full installer

Direct edits to Nginx, PHP-FPM, MySQL, Memcached, WordPress, cron, or permission files can be replaced by later SlickStack runs.

Persistent logging changes require a supported `ss-config` option where available, a documented optional include, an external collection layer, or a source-level SlickStack change.

## Scope

The standard SlickStack logging model assumes:

- one primary server and application stack
- local files for basic service diagnosis
- systemd journals for service lifecycle events
- manual administrator review during incidents
- no guaranteed long-term retention
- no centralized log aggregation by default

Managed ELK/OpenSearch stacks, Loki, Splunk, Datadog, remote syslog, cloud-vendor agents, SIEM pipelines, auditd policy, per-customer log isolation, immutable archives, compliance retention, and fleet-wide alerting are outside the standard SlickStack implementation.
