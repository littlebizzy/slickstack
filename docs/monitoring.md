# Monitoring

SlickStack provides lightweight local health checks, interactive resource output, timestamp files, service status shortcuts, and basic automatic service recovery. These features help an administrator inspect one server and recover a few common service failures, but they are not a replacement for external uptime monitoring, alerting, metrics retention, or application-level health checks.

Logging is documented separately. See [Logging](logging.md) when investigating request history, service errors, PHP failures, WordPress debugging, or systemd journals.

## Table of Contents

- [Main monitoring commands](#main-monitoring-commands)
- [Interactive resource monitor](#interactive-resource-monitor)
- [Stack overview](#stack-overview)
- [Automatic service monitoring and recovery](#automatic-service-monitoring-and-recovery)
- [Recovery listeners versus monitoring](#recovery-listeners-versus-monitoring)
- [Timestamp files](#timestamp-files)
- [Manual service checks](#manual-service-checks)
- [Cron and watchdog verification](#cron-and-watchdog-verification)
- [Troubleshooting](#troubleshooting)
- [Recommended incident sequence](#recommended-incident-sequence)
- [Managed-file boundaries](#managed-file-boundaries)
- [Scope](#scope)

## Main monitoring commands

The primary commands are:

```bash
sudo bash /var/www/ss-monitor-resources
sudo bash /var/www/ss-stack-overview
sudo bash /var/www/crons/02-cron-often
```

The installed Bash helpers include:

```bash
ss monitor
ss monitor resources
ss monitor server
ss info
ss overview
ss status
ss summary
ss status nginx
ss status php
ss cron often
```

These commands have different purposes:

| Command | Purpose |
| :-- | :-- |
| `ss monitor` | Displays changing memory, root-disk, and load values in the current terminal |
| `ss info`, `ss overview`, `ss status`, `ss summary` | Runs `ss-stack-overview` and prints important SlickStack configuration and limited file-status checks |
| `ss status nginx` | Displays the Nginx service status |
| `ss status php` | Displays matching PHP-FPM systemd service status |
| `ss cron often` | Manually runs the complete `02-cron-often` wrapper, including its recovery listeners, WP-Cron path, custom tasks, and `ss-check` |

Do not treat `ss status` as a general service-health dashboard. It is currently an alias for `ss-stack-overview`, which mainly prints configuration values.

## Interactive resource monitor

Run:

```bash
sudo bash /var/www/ss-monitor-resources
```

or:

```bash
ss monitor
```

The script prints one row every five seconds for up to one hour:

```text
Memory          Disk            CPU
```

Press `Ctrl+C` to stop it early.

### Memory value

The memory field is calculated from the used and total values reported by:

```bash
free -m
```

It is displayed as a percentage. Use these commands for fuller context:

```bash
free -h
cat /proc/meminfo
ps aux --sort=-%mem | head -n 20
```

A high used-memory percentage does not automatically mean the server is failing. Linux intentionally uses available RAM for caches. Review the `available` memory, swap activity, OOM events, and the largest processes before restarting services.

### Disk value

The disk field shows the percentage used on the root filesystem mounted at `/`.

Use:

```bash
df -h
df -i
sudo du -xhd1 /var /home 2>/dev/null | sort -h
```

The interactive script does not inspect every mounted filesystem, inode exhaustion, deleted files still held open, or which directory is consuming space.

### CPU field limitation

Despite being labeled `CPU`, the current script reads the first load-average value from `top` and appends a percent sign. It is therefore not a true CPU-utilization percentage.

Use these commands when CPU behavior matters:

```bash
uptime
top
ps aux --sort=-%cpu | head -n 20
nproc
```

Load average should be interpreted in relation to the number of available CPU cores. A load of `2.00` means something different on a one-core server than on an eight-core server.

### What the resource monitor does not provide

The script does not:

- retain historical samples
- calculate trends or baselines
- send alerts
- define warning or critical thresholds
- identify which process caused a spike
- measure network throughput or latency
- test Nginx, PHP, MySQL, Memcached, WordPress, DNS, SSL, or Cloudflare
- restart services

It is an interactive snapshot tool rather than a monitoring daemon.

## Stack overview

Run:

```bash
sudo bash /var/www/ss-stack-overview
```

or one of:

```bash
ss info
ss overview
ss status
ss summary
```

The current overview reports values such as:

- SlickStack build
- server IPv4 and IPv6 addresses
- configured domains
- Adminer URL
- sudo and SFTP users
- database settings
- staging and development status
- Multisite settings
- Rsync settings
- whether the root crontab contains SlickStack's validation marker
- whether expected OpenSSL and Certbot certificate files exist

The certificate checks only verify file existence. They do not verify expiration, hostname coverage, private-key matching, certificate-chain validity, Nginx activation, or successful public delivery.

The crontab check only verifies SlickStack's marker in the root crontab. It does not prove that the cron daemon is active or that every wrapper completes successfully.

### Sensitive output warning

`ss-stack-overview` currently prints passwords, database credentials, infrastructure addresses, the Adminer URL, Rsync credentials, and potentially a generated SSH private key.

Do not paste its output into a public issue, chat, support ticket, screenshot, or monitoring platform without careful redaction.

See [Logging](logging.md) for safe diagnostic sharing guidance.

## Automatic service monitoring and recovery

SlickStack's basic automatic service listeners are located inside:

```text
/var/www/crons/02-cron-often
```

The root crontab runs this wrapper every two minutes with `flock` overlap protection:

```cron
*/2 * * * * /usr/bin/flock -w 0 /tmp/02-cron-often.lock /var/www/crons/02-cron-often > /dev/null 2>&1
```

This wrapper performs more than service recovery. It also helps restore critical SlickStack scripts, runs overdue `ss-check` and `ss-worker` tasks, touches its timestamp, may run WP-Cron, sources the custom often file, and runs `ss-check` again.

### PHP-FPM listener and restarter

The wrapper maps the supported Ubuntu release to its expected PHP-FPM systemd unit:

| Ubuntu | PHP-FPM unit |
| :-- | :-- |
| 24.04 | `php8.3-fpm.service` |
| 22.04 | `php8.1-fpm.service` |
| 20.04 | `php7.4-fpm.service` |
| 18.04 | `php7.2-fpm.service` |

It first verifies that the selected unit exists with `systemctl cat`. It then checks:

```bash
systemctl is-active --quiet PHP_FPM_SERVICE
```

When the service is not active, it sources SlickStack's PHP restart script. That script performs a forceful restart through the shared `ss_restart` helper rather than a graceful PHP-FPM reload.

Current limitations:

- it checks the systemd active state, not whether the PHP-FPM socket accepts requests
- it does not test a PHP page or WordPress
- it does not validate PHP configuration before restarting
- an active but hung, overloaded, misconfigured, or socket-mismatched service can pass
- the listener does not send an alert when a restart occurs

### Nginx listener and restarter

The wrapper verifies that `nginx.service` exists and checks:

```bash
systemctl is-active --quiet nginx.service
```

When Nginx is inactive, it sources SlickStack's Nginx restart script. The restart script performs a forceful service restart.

Current limitations:

- it does not run `nginx -t` before the automatic restart
- it checks systemd state rather than making an HTTP or HTTPS request
- an active Nginx service can still return errors, route to the wrong upstream, use an invalid certificate, or be unreachable through Cloudflare
- it does not verify DNS, firewall, origin, or external network availability
- it does not alert the administrator

### MySQL listener and restarter

The MySQL listener runs only when:

```bash
DB_REMOTE!="true"
```

Remote database servers are deliberately excluded because the local SlickStack server should not try to restart them.

For a local database, the wrapper verifies that `mysql.service` exists and runs a local socket health check with a five-second timeout:

```bash
timeout 5 mysqladmin --user=root --host=localhost --protocol=socket ping --silent
```

The check uses local root socket authentication rather than the WordPress database password.

Failures are counted in:

```text
/var/www/meta/mysql-watchdog.count
```

Behavior:

1. a successful ping removes the counter file
2. a failed or timed-out ping increments the counter
3. MySQL is restarted after three consecutive failed checks
4. the counter file is removed after the restart attempt

Because `02-cron-often` normally runs every two minutes, the restart threshold requires three wrapper executions rather than reacting to one temporary failure.

The MySQL check is stronger than the PHP-FPM and Nginx checks because it tests a local database response instead of only the systemd active state. It still does not run a WordPress query, verify a remote database, test replication, inspect query latency, or send an alert.

### Services not covered by these listeners

The current `02-cron-often` recovery section does not automatically health-check and restart:

- Memcached
- Fail2ban
- Cron itself
- SSH
- Iptables
- Cloudflare
- Certbot or certificate renewal
- WordPress
- Adminer
- external DNS or network services

A service can also fail in a way that leaves its systemd state active. External request monitoring remains necessary when availability matters.

## Recovery listeners versus monitoring

The `02-cron-often` listeners are automatic restart guards. They are not a complete monitoring system.

They do not provide:

- email, SMS, push, Slack, or webhook alerts
- an incident timeline
- restart counters or long-term restart history
- dashboards or charts
- uptime percentages
- latency measurements
- HTTP content checks
- SSL-expiration alerts
- database performance metrics
- process, socket, or queue thresholds
- remote monitoring from outside the server

A service that briefly fails and is restarted may leave little obvious evidence unless its service journal, SlickStack logs, or restart timestamp is reviewed before retention is lost.

## Timestamp files

SlickStack scripts and cron wrappers touch files beneath:

```text
/var/www/meta/timestamps/
```

Relevant examples include:

```text
02-cron-often.timestamp
ss-monitor-resources.timestamp
ss-stack-overview.timestamp
ss-restart-php.timestamp
ss-restart-nginx.timestamp
ss-restart-mysql.timestamp
```

Inspect them with:

```bash
ls -lt /var/www/meta/timestamps | head -n 30
stat /var/www/meta/timestamps/02-cron-often.timestamp
stat /var/www/meta/timestamps/ss-restart-nginx.timestamp
```

These are activity markers, not success records.

Important limitations:

- `02-cron-often.timestamp` is touched before its service listeners run
- restart timestamps are touched before the restart command executes
- a recent timestamp does not prove that the service became healthy
- an old timestamp can indicate a scheduling problem, a missing script, an early failure, or a disabled cron daemon
- timestamps do not record why a script ran or what it changed

Use timestamps together with service state, logs, journals, and an actual application request.

## Manual service checks

Start with:

```bash
systemctl --failed
systemctl status nginx
systemctl status mysql
systemctl status memcached
systemctl status cron
systemctl status php*-fpm.service
```

Check whether expected listeners exist:

```bash
sudo ss -lntp
sudo ss -lxnp
```

Common local checks include:

```bash
sudo nginx -t
mysqladmin --user=root --host=localhost --protocol=socket ping
curl -I https://example.com
```

Replace `example.com` with the configured public hostname.

A public `curl` request may test Cloudflare rather than the origin when the domain is proxied. Review [Cloudflare](cloudflare.md) and [Nginx](nginx.md) when separating edge, DNS, network, origin, and application failures.

## Cron and watchdog verification

Verify the root schedule:

```bash
sudo grep '02-cron-often' /var/spool/cron/crontabs/root
```

Verify the cron daemon:

```bash
systemctl status cron
journalctl -u cron --since '1 hour ago'
```

Verify recent wrapper activity:

```bash
stat /var/www/meta/timestamps/02-cron-often.timestamp
```

Manually running the wrapper is possible:

```bash
sudo bash /var/www/crons/02-cron-often
```

or:

```bash
ss cron often
```

This is not a read-only health check. It can refresh SlickStack scripts, run WP-Cron, execute custom often tasks, and restart services when its listeners detect failures.

See [Cron Jobs](cron.md) for the full wrapper architecture and recovery behavior.

## Troubleshooting

### Nginx is active but the website is down

The automatic listener only checks `systemctl is-active`.

Check:

```bash
sudo nginx -t
curl -I https://example.com
sudo tail -n 100 /var/www/logs/nginx-error.log
journalctl -u nginx --no-pager -n 100
```

Also verify DNS, Cloudflare, certificates, firewall rules, PHP-FPM, and the upstream application.

### PHP-FPM is active but requests return 502

The listener does not test the PHP-FPM socket or a PHP request.

Check:

```bash
systemctl status php*-fpm.service
sudo ss -lxnp | grep php
sudo tail -n 100 /var/www/logs/php-error.log
sudo tail -n 100 /var/www/logs/nginx-error.log
journalctl -u 'php*-fpm.service' --no-pager -n 100
```

Review the active Nginx FastCGI socket and PHP-FPM pool configuration.

### MySQL keeps restarting

Inspect:

```bash
cat /var/www/meta/mysql-watchdog.count 2>/dev/null
stat /var/www/meta/timestamps/ss-restart-mysql.timestamp
systemctl status mysql
journalctl -u mysql --no-pager -n 200
mysqladmin --user=root --host=localhost --protocol=socket ping
```

Repeated failures can indicate memory pressure, disk exhaustion, damaged tables, InnoDB recovery, permissions, package problems, or an invalid MySQL configuration. Do not repeatedly restart MySQL without reviewing its journal and database state.

### Remote MySQL is not being restarted

This is expected when:

```bash
DB_REMOTE="true"
```

SlickStack skips its local MySQL watchdog for remote databases. Monitor and recover the remote database from the system that manages it.

### The often timestamp is stale

Check:

```bash
systemctl status cron
sudo grep '02-cron-often' /var/spool/cron/crontabs/root
ls -l /var/www/crons/02-cron-often
ls -l /tmp/02-cron-often.lock
journalctl -u cron --since '1 hour ago'
```

The root crontab removes lock files older than six hours, but a current lock can still indicate an active or stuck wrapper. Do not delete a lock without checking whether the process is still running.

### Resource monitor shows strange CPU percentages

The value is the one-minute load average with a percent sign appended. Compare it with:

```bash
uptime
nproc
top
```

Do not interpret the displayed value as direct CPU utilization.

## Recommended incident sequence

Before restarting the full stack or running `ss-install`:

```bash
date
uptime
free -h
df -h
df -i
systemctl --failed
sudo nginx -t
```

Then:

1. identify the exact failing request or service
2. check the relevant systemd state
3. inspect its SlickStack log and systemd journal
4. review the `02-cron-often` and restart timestamps
5. test the local service directly
6. test the public website separately
7. preserve relevant evidence before clearing logs or reinstalling
8. make one narrow correction
9. verify recovery from both the origin and the public hostname

See [Logging](logging.md) before running `ss-install`, because the full installer truncates the current `/var/www/logs/*.log` contents.

## Managed-file boundaries

SlickStack manages:

- `ss-monitor-resources`
- `ss-stack-overview`
- the `02-cron-often` wrapper
- root crontab scheduling
- service restart scripts
- watchdog counter and timestamp locations
- automatic script self-healing

Direct edits to these files can be replaced by `ss-check`, cron self-healing, `ss-worker`, or `ss-install`.

Persistent monitoring changes require a supported custom cron file, an external monitoring service, or a source-level SlickStack change.

## Scope

The standard SlickStack monitoring model assumes:

- one primary server
- local systemd services
- simple command-line diagnosis
- basic automatic recovery for PHP-FPM, Nginx, and local MySQL
- manual administrator review after incidents
- separate logging and external availability monitoring

Prometheus, Grafana, Netdata, Monit, Nagios, Zabbix, Datadog, New Relic, cloud-vendor agents, centralized metrics, distributed tracing, synthetic browser testing, multi-server failover, automatic incident escalation, and fleet orchestration are outside the standard SlickStack implementation.
