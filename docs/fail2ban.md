# Fail2ban

SlickStack currently installs Fail2ban as an additional log-based banning layer for SSH and selected Nginx traffic patterns.

> **Planned removal:** SlickStack plans to remove Fail2ban in a future release if its useful protection can be replaced safely by the simpler managed Iptables firewall. New SlickStack features should not depend on Fail2ban, and administrators should treat this module as transitional rather than a permanent extension point.

Fail2ban remains active in the current stack until that migration is completed. This document describes the present implementation so existing servers can be inspected and maintained safely.

## Current role

Fail2ban watches authentication and Nginx access logs for repeated matching events. When a jail reaches its configured threshold, Fail2ban adds temporary firewall rules that reject traffic from the detected source address.

This is separate from SlickStack's persistent firewall rules:

- [Iptables](iptables.md) defines the baseline inbound firewall policy and SSH connection rate limits.
- Fail2ban reads log entries and creates temporary bans after matching repeated behavior.
- Nginx also applies its own connection and request-rate limits before or while serving HTTP traffic.

Fail2ban does not replace the Iptables firewall, Cloudflare security controls, Nginx rate limiting, or WordPress application security.

## Installation

The package installer is:

```bash
sudo bash /var/www/ss-install-fail2ban-packages
```

It updates the APT package index, upgrades installed packages, and installs the Ubuntu `fail2ban` package.

On Ubuntu 24.04, the installer first checks whether Python can import `asynchat`. When it cannot, SlickStack installs `python3-pip` and the `pyasynchat` compatibility package before installing Fail2ban.

The complete SlickStack installer currently runs the Fail2ban package and configuration workflows automatically:

```bash
sudo bash /var/www/ss-install
```

Because Fail2ban is planned for removal, avoid adding unrelated Python packages, custom services, or deployment dependencies that assume this module will always exist.

## Managed configuration

The configuration installer is:

```bash
sudo bash /var/www/ss-install-fail2ban-config
```

It installs or replaces:

```text
/etc/fail2ban/jail.local
/etc/fail2ban/filter.d/nginx-get-dos.conf
/etc/fail2ban/filter.d/nginx-post-dos.conf
/etc/fail2ban/filter.d/nginx-4xx.conf
```

It then replaces the threshold placeholders in `jail.local` with values from `ss-config` and restarts Fail2ban.

Direct edits to these files can be overwritten by the configuration installer or the complete `ss-install` workflow.

## Enabled jails

The current managed `jail.local` enables four jails:

| Jail | Log | Purpose |
| :------------- | :---------- | :---------- |
| `sshd` | `/var/log/auth.log` | Repeated failed SSH authentication |
| `nginx-get-dos` | `/var/www/logs/nginx-access.log` | Repeated HTTP GET requests |
| `nginx-post-dos` | `/var/www/logs/nginx-access.log` | Repeated HTTP POST requests |
| `nginx-4xx` | `/var/www/logs/nginx-access.log` | Repeated selected client-error responses |

An `nginx-5xx` jail and filter substitutions remain commented out and are not active.

## Default thresholds

### SSH

The SSH jail is hardcoded to:

```text
findtime = 3600
maxretry = 5
bantime = 86400
```

This means five matching SSH failures within one hour can produce a one-day ban.

The SSH jail ignores the loopback address:

```text
ignoreip = 127.0.0.1
```

SlickStack's persistent Iptables rules also rate-limit new SSH connections before Fail2ban evaluates authentication failures. See [Iptables](iptables.md).

### Nginx GET requests

The default settings are:

```bash
FAIL2BAN_GET_DOS_FINDTIME="60"
FAIL2BAN_GET_DOS_MAXRETRY="100"
FAIL2BAN_GET_DOS_BANTIME="3600"
```

The filter matches access-log lines beginning with a source address and containing a GET request.

The default permits up to 100 matching GET requests during a 60-second window before applying a one-hour ban.

This is a broad request-counting rule. It does not determine whether the requested URL was malicious, expensive, cached, or legitimate.

### Nginx POST requests

The default settings are:

```bash
FAIL2BAN_POST_DOS_FINDTIME="30"
FAIL2BAN_POST_DOS_MAXRETRY="10"
FAIL2BAN_POST_DOS_BANTIME="3600"
```

The filter matches access-log lines beginning with a source address and containing a POST request.

Ten matching POST requests during 30 seconds can therefore produce a one-hour ban.

POST traffic can include login attempts, API requests, WooCommerce activity, forms, background requests, and legitimate application traffic. Changes to this threshold should be tested carefully.

### Nginx 4xx responses

The default settings are:

```bash
FAIL2BAN_4XX_FINDTIME="60"
FAIL2BAN_4XX_MAXRETRY="10"
FAIL2BAN_4XX_BANTIME="3600"
```

The filter matches GET, POST, or HEAD requests that return one of these statuses:

```text
400
403
404
410
444
```

Ten matching responses during 60 seconds can produce a one-hour ban.

Repeated 4xx responses can indicate scanning or abusive traffic, but they can also result from broken links, missing assets, browser extensions, API clients, outdated bookmarks, or application errors.

### Inactive 5xx settings

`ss-config` currently contains:

```bash
FAIL2BAN_5XX_FINDTIME="600"
FAIL2BAN_5XX_MAXRETRY="100"
FAIL2BAN_5XX_BANTIME="21600"
```

However, the `nginx-5xx` jail, filter download, and placeholder substitutions are commented out. These settings currently have no effect.

## Cloudflare and reverse proxies

The Nginx filters assume the first address written to `/var/www/logs/nginx-access.log` is the actual client address.

When Cloudflare or another reverse proxy is in front of the origin, Nginx must restore the real visitor address correctly before writing the log. Otherwise Fail2ban can evaluate or ban a proxy address instead of the visitor responsible for the requests.

Before relying on an HTTP jail, inspect recent log entries:

```bash
tail -n 50 /var/www/logs/nginx-access.log
```

Confirm that the first field contains expected visitor addresses rather than only Cloudflare or load-balancer addresses.

Fail2ban rules exist on the origin server. They do not create bans inside the Cloudflare dashboard or propagate blocks to Cloudflare's edge network.

## Relationship with Iptables

Fail2ban normally enforces bans by creating temporary firewall chains and rules. These coexist with the persistent SlickStack Iptables rules installed from:

```text
/etc/iptables/rules.v4
/etc/iptables/rules.v6
```

The persistent SlickStack rules define the normal firewall after reboot or firewall reinstallation. Fail2ban recreates its own temporary state when its service starts and processes its jail database.

Avoid manually editing Fail2ban-generated chains as a permanent configuration method. Use the Fail2ban client for current bans and the managed SlickStack firewall files for the baseline firewall.

The planned direction is to remove this dynamic log-based layer and rely only on the simpler SlickStack-managed Iptables approach, provided equivalent practical protection can be achieved without creating fragile or overly aggressive rules.

## Service management

Restart Fail2ban with:

```bash
sudo bash /var/www/ss-restart-fail2ban
```

or:

```bash
ss restart fail2ban
```

Useful service checks include:

```bash
systemctl status fail2ban
systemctl is-active fail2ban
journalctl -u fail2ban --no-pager -n 100
```

Validate the configuration before assuming the service is protecting traffic:

```bash
sudo fail2ban-client -t
```

Display general status:

```bash
sudo fail2ban-client status
```

Display one jail:

```bash
sudo fail2ban-client status sshd
sudo fail2ban-client status nginx-get-dos
sudo fail2ban-client status nginx-post-dos
sudo fail2ban-client status nginx-4xx
```

## Inspecting and removing bans

The status output for a jail includes its currently banned addresses.

To remove an address from one jail:

```bash
sudo fail2ban-client set sshd unbanip 192.0.2.10
```

Replace `sshd` with the relevant jail name when the ban came from an Nginx filter.

To ban an address manually inside one jail:

```bash
sudo fail2ban-client set sshd banip 192.0.2.10
```

Manual jail bans are operational state, not persistent SlickStack firewall policy. They can disappear after service resets, package changes, database cleanup, or future removal of the Fail2ban module.

## Testing filters

Fail2ban provides `fail2ban-regex` for checking a filter against a log file.

Examples:

```bash
sudo fail2ban-regex /var/www/logs/nginx-access.log /etc/fail2ban/filter.d/nginx-get-dos.conf
sudo fail2ban-regex /var/www/logs/nginx-access.log /etc/fail2ban/filter.d/nginx-post-dos.conf
sudo fail2ban-regex /var/www/logs/nginx-access.log /etc/fail2ban/filter.d/nginx-4xx.conf
```

Test filters before lowering thresholds. Broad expressions can match legitimate traffic, and a syntactically valid regular expression can still represent the wrong security policy.

## Troubleshooting

### Fail2ban does not start

Check:

```bash
sudo fail2ban-client -t
systemctl status fail2ban
journalctl -u fail2ban --no-pager -n 100
```

On Ubuntu 24.04, also verify that Python can import the compatibility module expected by the current package workflow:

```bash
python3 -c "import asynchat"
```

Rerun the package and configuration installers when required:

```bash
sudo bash /var/www/ss-install-fail2ban-packages
sudo bash /var/www/ss-install-fail2ban-config
```

### A jail is missing

Confirm that the jail is enabled in:

```text
/etc/fail2ban/jail.local
```

Then verify its filter exists and the referenced log file is readable.

The `nginx-5xx` jail is intentionally inactive in the current configuration.

### Legitimate users are banned

Inspect the jail status and relevant log entries, then unban the address with `fail2ban-client`.

For HTTP jails, verify:

- the logged address is the real visitor address
- the request volume is genuinely abusive
- a broken application or missing asset is not generating repeated 4xx responses
- WooCommerce, API, login, form, or background POST traffic is not exceeding the configured threshold

Raise the appropriate `FAIL2BAN_*` threshold in `ss-config`, rerun the configuration installer, and retest when the current values are too aggressive.

### Fail2ban is active but does not ban

Check that:

- the expected jail appears in `fail2ban-client status`
- the log path exists and receives new entries
- `fail2ban-regex` matches current log lines
- the first log field contains the expected client address
- the threshold has actually been reached within `findtime`
- another service restart has not reset relevant state

## Migration direction

Fail2ban is documented because it is part of the current SlickStack installation, not because SlickStack intends to expand it.

The intended future direction is:

1. confirm which current Fail2ban protections still provide meaningful value
2. determine whether simple Iptables limits can replace them safely
3. avoid duplicating Nginx, Cloudflare, and Iptables controls
4. remove Fail2ban packages, configuration, scripts, settings, and documentation only after the replacement is verified

Until that work is complete, removing Fail2ban manually can leave a server with different protection than the current SlickStack release expects.

## Scope

The current SlickStack Fail2ban design assumes:

- the Ubuntu `fail2ban` package
- SSH authentication logs at `/var/log/auth.log`
- Nginx access logs at `/var/www/logs/nginx-access.log`
- the current managed log format with the source address first
- one standalone SlickStack server
- four enabled managed jails
- temporary local firewall bans

Custom jail collections, permanent IP reputation databases, distributed ban synchronization, Cloudflare API bans, container networking, external log pipelines, SIEM integration, and long-term dependence on Fail2ban are outside the standard managed configuration.