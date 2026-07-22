# Cron Jobs

SlickStack uses a root-owned cron system to run recurring maintenance, recovery, WordPress, backup, synchronization, certificate, cache, and update tasks. The schedule is intentionally standardized so every SlickStack server uses the same wrapper intervals while individual tasks are selected through `ss-config`.

Do not edit the root crontab or the standard SlickStack cron wrappers directly. Use `INTERVAL_SS_*` settings for bundled tasks and the reserved custom cron files for your own commands.

## Table of Contents

- [Architecture](#architecture)
- [Fixed wrapper frequencies](#fixed-wrapper-frequencies)
- [Interval settings](#interval-settings)
- [Current default task intervals](#current-default-task-intervals)
- [WP-Cron](#wp-cron)
- [Overlap protection](#overlap-protection)
- [Stale lock cleanup](#stale-lock-cleanup)
- [Custom cron files](#custom-cron-files)
- [Managed files and self-healing](#managed-files-and-self-healing)
- [Running wrappers manually](#running-wrappers-manually)
- [Output and logging](#output-and-logging)
- [Installation and repair](#installation-and-repair)
- [Troubleshooting](#troubleshooting)
- [Scope](#scope)

## Architecture

The cron system has three layers:

1. the root crontab launches one of 14 fixed interval wrappers
2. each wrapper sources SlickStack configuration and runs tasks assigned to that interval
3. each wrapper sources a matching custom file for administrator-defined commands

Important paths are:

```text
/var/www/crons/00-crontab
/var/www/crons/01-cron-minutely
/var/www/crons/02-cron-often
...
/var/www/crons/14-cron-sometimes
/var/www/crons/custom/
```

The installed root crontab is generated from `/var/www/crons/00-crontab` and stored by Ubuntu under:

```text
/var/spool/cron/crontabs/root
```

SlickStack installs and repairs it with:

```bash
sudo bash /var/www/ss-install-ubuntu-crontab
```

That workflow installs Cron when needed, replaces the root schedule, reloads the Cron service, and restores managed permissions.

## Fixed wrapper frequencies

The root schedule launches these wrappers:

| Number | Name | Actual schedule |
| :--: | :-- | :-- |
| `01` | `minutely` | Every minute |
| `02` | `often` | Every 2 minutes |
| `03` | `regular` | Every 5 minutes |
| `04` | `quarter-hourly` | Every 15 minutes |
| `05` | `half-hourly` | Every 30 minutes |
| `06` | `hourly` | At minute 0 of every hour |
| `07` | `quarter-daily` | Every 6 hours |
| `08` | `half-daily` | Every 12 hours |
| `09` | `daily` | Daily at 00:00 |
| `10` | `half-weekly` | At 00:00 every third day |
| `11` | `weekly` | Sunday at 00:00 |
| `12` | `half-monthly` | Day 13 of each month at 00:00 |
| `13` | `monthly` | Day 1 of each month at 00:00 |
| `14` | `sometimes` | Day 1 every second month at 00:00 |

The names `half-weekly` and `half-monthly` are historical labels. They do not represent exact 3.5-day or 15-day rolling intervals.

All times use the server timezone configured by:

```bash
SS_TIMEZONE="UTC"
```

UTC is recommended so daylight-saving changes do not shift maintenance windows.

## Interval settings

Bundled tasks are assigned to wrappers with `INTERVAL_SS_*` values in `ss-config`.

Examples:

```bash
INTERVAL_SS_DUMP_DATABASE="hourly"
INTERVAL_SS_PERMS="half-daily"
INTERVAL_SS_OPTIMIZE_DATABASE="weekly"
INTERVAL_SS_REBOOT_MACHINE="never"
```

When the matching wrapper runs, it checks the task's configured interval and sources the related SlickStack script.

Use:

```text
never
```

to disable an optional scheduled task. Do not leave a value blank and assume that means disabled: some wrappers deliberately fall back to a default interval when a required setting is missing or invalid.

After changing interval settings, the root crontab itself normally does not need modification because all 14 wrappers already have fixed schedules.

## Current default task intervals

The current sample configuration includes these defaults:

| Task | Default interval |
| :-- | :-- |
| Clean database | `never` |
| Clean files | `half-daily` |
| Dump database | `hourly` |
| Dump files | `never` |
| Empty logs | `never` |
| Renew Certbot certificate | `weekly` |
| Regenerate OpenSSL certificate | `never` |
| Refresh Cloudflare IPs | `half-weekly` |
| Refresh Ubuntu Bash configuration | `half-weekly` |
| Refresh WP-CLI | `half-monthly` |
| Rebuild WordPress configuration | `weekly` |
| Refresh WordPress MU plugins | `half-weekly` |
| Refresh WordPress packages | `never` |
| Optimize database | `weekly` |
| Optimize files | `half-weekly` |
| Reset permissions | `half-daily` |
| Purge Memcached | `half-monthly` |
| Purge WordPress transients | `monthly` |
| Reboot server | `never` |
| Remote backup | `never` |
| Synchronize staging | `half-daily` |
| Update `ss-config` | `never` |
| Update Ubuntu modules | `never` |

Defaults may change between SlickStack builds. Treat the active `/var/www/ss-config` file as the source of truth for a particular server.

## WP-Cron

WordPress scheduled events use separate settings:

```bash
WP_CRON_METHOD="wpcli"
WP_CRON_INTERVAL="often"
```

Supported WordPress intervals are:

```text
minutely
often
regular
quarter-hourly
half-hourly
hourly
```

The default `often` value runs due WordPress events every two minutes. WP-CLI is the preferred method, and Multisite forces the WP-CLI path so due events can run across network sites.

WP-CLI errors are appended to:

```text
/var/www/logs/wp-cli.log
```

WooCommerce and many extensions use WP-Cron or Action Scheduler for email, webhooks, renewals, inventory synchronization, and other background work. SlickStack runs due WordPress events, but it does not separately monitor Action Scheduler queue size, failed actions, or application-specific completion. See [WooCommerce](woocommerce.md) for the relevant queue checks and staging safeguards.

See [WordPress](wordpress.md) for the broader WP-Cron behavior.

## Overlap protection

Every standard wrapper is launched through `flock` with a separate lock file under `/tmp`.

For example:

```bash
/usr/bin/flock -w 0 /tmp/06-cron-hourly.lock /var/www/crons/06-cron-hourly
```

The `-w 0` behavior means Cron does not wait or queue another copy when that same interval wrapper is already running. The overlapping invocation exits immediately.

This prevents two copies of one wrapper from running simultaneously, but it does not prevent different wrappers from overlapping. For example, an hourly task can still run at the same time as a daily task at midnight.

Long-running or resource-intensive work should therefore be assigned carefully even when each individual wrapper has its own lock.

## Stale lock cleanup

The root crontab removes matching lock files older than six hours every 30 minutes:

```text
/tmp/*.lock
```

Standard wrappers also attempt to remove their own associated lock file after completing.

Deleting a lock file does not terminate an active process. Before removing a lock manually, confirm that its wrapper is not still running.

## Custom cron files

Custom recurring commands belong under:

```text
/var/www/crons/custom/
```

Each standard wrapper sources a matching custom file. For example:

```text
/var/www/crons/06-cron-hourly
/var/www/crons/custom/06-cron-hourly-custom
```

Add hourly commands only to the custom file:

```bash
#!/bin/bash

/usr/local/bin/example-command
```

Custom files source `/var/www/ss-config` and `/var/www/ss-functions`, so SlickStack variables and helper functions are available.

Custom code runs as root and is sourced into the parent wrapper rather than launched as an isolated process. Avoid:

- `exit`, which can terminate the parent wrapper
- changing the working directory without restoring it
- redefining shared variables or functions unnecessarily
- interactive commands that wait for input
- long foreground tasks that can block the wrapper
- commands without predictable absolute paths

Redirect custom output to a dedicated log when it needs to be retained.

## Managed files and self-healing

The standard files under `/var/www/crons/` are managed by SlickStack and can be replaced during installation or repair.

The root crontab also includes a self-healing process that periodically downloads each standard wrapper from SlickStack's public mirrors. A replacement is promoted only when the downloaded file is non-empty and contains the expected `SS_EOF` marker.

This is another reason not to edit standard wrappers directly. Custom files under `/var/www/crons/custom/` are the supported extension point.

The frequent wrappers also contain recovery logic for critical SlickStack files. Current behavior includes:

- restoring an invalid `ss-config` from the newest validated local backup when possible
- refreshing missing or stale `ss-functions`
- refreshing missing or stale `ss-check` and `ss-worker`
- running `ss-check` or `ss-worker` when their timestamps are overdue

The `often` wrapper additionally performs lightweight service recovery checks for PHP-FPM, Nginx, and local MySQL.

## Running wrappers manually

Standard wrappers can be run directly:

```bash
sudo bash /var/www/crons/06-cron-hourly
```

or through a SlickStack alias:

```bash
ss cron hourly
ss cron 06
```

Custom files can also be run with aliases such as:

```bash
ss cron custom hourly
ss cron custom 06
```

Manual execution bypasses the `flock` command used by the installed root crontab. Check that the same wrapper is not already active before running it manually.

Running a standard wrapper manually executes every bundled and custom task currently assigned to that interval; it is not limited to one selected task.

## Output and logging

The managed root crontab sets:

```text
MAILTO=""
```

and redirects wrapper output to `/dev/null`. Routine Cron output is therefore silent and is not emailed to the administrator.

Tasks that require retained diagnostics must write to their own logs. Common locations include:

```text
/var/www/logs/
/var/www/logs/wp-cli.log
```

System Cron messages may be available through:

```bash
journalctl -u cron
```

Do not assume a task succeeded merely because no Cron email or terminal error appeared.

## Installation and repair

Restore the managed root schedule with:

```bash
sudo bash /var/www/ss-install-ubuntu-crontab
```

The full installer also reapplies the Cron configuration:

```bash
sudo bash /var/www/ss-install
```

Useful checks are:

```bash
systemctl status cron
sudo crontab -l
ls -la /var/www/crons/
ls -la /var/www/crons/custom/
```

Standard wrappers and custom files are normally owned by `root:root` and executable only by root or sudo administrators.

## Troubleshooting

### A bundled task does not run

Confirm:

- the Cron service is active
- the expected wrapper exists and is executable
- root's crontab contains the wrapper schedule
- the task's `INTERVAL_SS_*` value matches that wrapper name
- the value is not `never`
- the related SlickStack script exists
- another copy of the same wrapper is not holding its lock

Run the wrapper manually only after checking for an active process.

### A custom task does not run

Confirm:

- the code is in the correctly numbered custom file
- the custom file is readable and executable by root
- commands use valid paths in Cron's restricted environment
- the task does not require an interactive shell
- output and errors are redirected to a log you can inspect

Test the custom file manually before waiting for its next scheduled interval.

### Tasks overlap at midnight

This can be expected. Hourly, daily, weekly, monthly, and other wrappers may share the same start time. Their locks prevent duplicate copies of the same wrapper, not contention between different wrappers.

Move optional custom work to another interval or make the custom command implement its own locking when resource contention matters.

### A standard wrapper keeps reverting

This is expected when a managed wrapper was edited directly. The root crontab's self-healing process and `ss-install` can restore the public version.

Move custom logic to the matching file under `/var/www/crons/custom/`.

### Cron appears silent

The managed schedule intentionally disables Cron email and discards normal wrapper output. Inspect task-specific logs and the system Cron journal instead.

## Scope

The standard SlickStack Cron design assumes:

- one root-owned schedule
- 14 fixed interval wrappers
- task selection through `INTERVAL_SS_*`
- separate WP-Cron settings
- one matching custom file per interval
- `flock` protection for each standard wrapper
- silent routine output
- managed self-healing

Arbitrary user crontabs, a web-based scheduler, per-task time-of-day settings, second-level schedules, distributed job queues, background workers, dependency graphs, centralized logs, failure notifications, and multi-server orchestration are outside the standard SlickStack Cron system.
