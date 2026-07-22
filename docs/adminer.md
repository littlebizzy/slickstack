# Adminer

SlickStack includes Adminer as a lightweight browser-based MySQL management tool. It is intended for occasional database inspection, imports, exports, and manual administration without installing phpMyAdmin or a separate database control panel.

Adminer is powerful and can modify or delete database content. Keep its URL private, use the least-privileged database account that fits the task, and disable public access when it is not needed.

## Table of Contents

- [Current version](#current-version)
- [Configuration](#configuration)
- [Production URL](#production-url)
- [Staging and development URLs](#staging-and-development-urls)
- [Installation behavior](#installation-behavior)
- [Nginx and PHP-FPM routing](#nginx-and-php-fpm-routing)
- [Rate limiting](#rate-limiting)
- [Authentication](#authentication)
- [Local database credentials](#local-database-credentials)
- [Root login](#root-login)
- [Remote databases](#remote-databases)
- [Database selection](#database-selection)
- [Permissions](#permissions)
- [Caching and sessions](#caching-and-sessions)
- [Logs](#logs)
- [Disabling Adminer](#disabling-adminer)
- [Updating Adminer](#updating-adminer)
- [Adminer is not a backup system](#adminer-is-not-a-backup-system)
- [Security recommendations](#security-recommendations)
- [Troubleshooting](#troubleshooting)
- [Scope](#scope)

## Current version

The current SlickStack module contains Adminer:

```text
5.4.2
```

The vendored source is stored in the repository at:

```text
modules/adminer/adminer.txt
```

and installed on a SlickStack server as:

```text
/var/www/meta/adminer.php
```

The file is managed by SlickStack and may be replaced whenever PHP configuration or the complete stack is reinstalled.

## Configuration

Adminer access is controlled by these `ss-config` values:

```bash
ADMINER_PUBLIC="true"
ADMINER_URL="long-random-string"
```

The current defaults enable Adminer and generate a randomized URL during initial setup.

`ADMINER_URL` should be long, unique, and difficult to guess. It is part of the public URL and should not contain spaces, slashes, or characters that can interfere with an Nginx location path.

After changing either setting, run the complete installer:

```bash
sudo bash /var/www/ss-install
```

or reinstall both related components:

```bash
sudo bash /var/www/ss-install-php-config
sudo bash /var/www/ss-install-nginx-config
```

The PHP installer manages the Adminer application file, while the Nginx installer manages frontend routing.

## Production URL

When `ADMINER_PUBLIC="true"`, the production URL is:

```text
https://SITE_FULL_DOMAIN/ADMINER_URL/
```

For example:

```text
https://example.com/a-long-random-adminer-path/
```

The trailing slash should be included because the active Nginx location is created as a directory-style prefix.

The production server block loads the optional file:

```text
/var/www/sites/includes/adminer.conf
```

That include is generated from the SlickStack Adminer Nginx template and contains the randomized path.

## Staging and development URLs

The staging and development server blocks load the same optional Adminer include as production. When `ADMINER_PUBLIC="true"`, every enabled environment therefore uses the same configured `ADMINER_URL` path:

```text
https://SITE_FULL_DOMAIN/ADMINER_URL/
https://STAGING_HOST/ADMINER_URL/
https://DEVELOPMENT_HOST/ADMINER_URL/
```

The path is shared rather than generated separately for each environment. Keep it private, protect staging and development when they are publicly reachable, and disable Adminer when it is not actively required.

Setting `ADMINER_PUBLIC="false"` removes both the managed Adminer PHP file and the shared Nginx include during the related installers, disabling Adminer routing across production, staging, and development.

## Installation behavior

Adminer is installed as part of PHP configuration rather than through a separate active top-level module installer.

The PHP configuration workflow:

1. downloads the mirrored Adminer PHP file
2. validates that the file contains the expected Adminer author marker
3. installs it to `/var/www/meta/adminer.php` when public access is enabled
4. removes the installed file when `ADMINER_PUBLIC="false"`
5. resets permissions
6. restarts PHP-FPM

Run it directly with:

```bash
sudo bash /var/www/ss-install-php-config
```

The Nginx configuration workflow:

1. downloads the Adminer include template
2. validates the expected SlickStack marker
3. replaces the URL placeholder with `ADMINER_URL`
4. installs `/var/www/sites/includes/adminer.conf`
5. forcefully restarts Nginx

When `ADMINER_PUBLIC="false"`, it removes the shared include instead.

Run it directly with:

```bash
sudo bash /var/www/ss-install-nginx-config
```

Older standalone Adminer installer scripts remain under the repository archive, but the active implementation is integrated into the PHP and Nginx configuration workflows.

## Nginx and PHP-FPM routing

The shared Adminer include uses a dedicated Nginx location that:

- aliases requests to `/var/www/meta/adminer.php`
- routes PHP execution to `127.0.0.1:9000`
- uses the shared PHP FastCGI configuration
- allows a 300-second FastCGI read timeout
- disables FastCGI caching
- returns an error when the target file is missing

Adminer bypasses the normal WordPress PHP-entry-point allowlist because it has its own explicit location.

The production, staging, and development server blocks all load the same optional Adminer include.

## Rate limiting

SlickStack defines an Nginx request zone for Adminer:

```nginx
limit_req_zone $binary_remote_addr zone=adminer:10m rate=100r/s;
```

The Adminer locations apply:

```nginx
limit_req zone=adminer burst=1 nodelay;
```

Requests exceeding the active Nginx limit receive the configured rate-limit response, currently status `444`.

The sample `ss-config` also contains:

```bash
LIMIT_REQUESTS_ADMINER_SECOND="300"
LIMIT_REQUESTS_ADMINER_MINUTE="5000"
```

These values are currently marked as unsupported. The active Nginx Adminer zone is hardcoded to `100r/s`, and the template does not currently consume those two settings. Changing them does not presently tune Adminer's effective rate limit.

## Authentication

The randomized URL is not database authentication. Anyone who discovers an active Adminer path can reach the Adminer login screen.

Adminer then requires valid database connection credentials. SlickStack does not automatically log users in, expose passwords in the page, or add a separate Adminer account system.

Do not share the URL and database credentials together in insecure messages or public issue trackers.

## Local database credentials

For a local SlickStack MySQL server, the normal Adminer connection host is:

```text
127.0.0.1
```

SlickStack creates two useful database identities.

### Full database administration

Use:

```text
Server: 127.0.0.1
Username: admin
Password: DB_PASSWORD_ROOT
```

The local `admin` account receives privileges across all databases and is specifically created for tools such as Adminer.

Use this account only when the task genuinely requires cross-database or administrative access.

### WordPress database access

For more limited access, use:

```text
Server: 127.0.0.1
Username: DB_USER
Password: DB_PASSWORD
Database: DB_NAME
```

The normal WordPress database user receives access to the production database and, when enabled, the SlickStack staging and development databases.

This is usually safer for ordinary WordPress inspection, imports, and exports.

## Root login

The local MySQL `root@localhost` account uses Ubuntu socket authentication in the current SlickStack setup.

A browser-based PHP application does not authenticate through the administrator's operating-system root session, so entering the Linux root username or `SUDO_PASSWORD` in Adminer is not the normal login method.

Use the database `admin` account with `DB_PASSWORD_ROOT` when full local database privileges are required.

## Remote databases

When `DB_REMOTE="true"`, enter the remote database details configured for the site:

```text
Server: DB_HOST
Username: DB_USER
Password: DB_PASSWORD
Database: DB_NAME
```

Include the remote port in the server value when Adminer requires it and the service does not use the default MySQL port.

SlickStack does not create the local `admin` superuser on a remote database server. Remote grants, network allowlists, TLS requirements, database availability, and provider-specific connection rules remain the responsibility of the remote database operator.

## Database selection

Common SlickStack database names are:

```text
production
staging
development
```

The production name can be customized with `DB_NAME`. Staging and development databases exist only when those environments are enabled and a local database is being managed by SlickStack.

Always confirm the selected database before importing, dropping tables, running SQL, or changing values. Adminer does not know which environment you intended to modify.

## Permissions

When the Adminer PHP file exists, the PHP configuration permission script initially sets:

```text
www-data:www-data 0664
```

The Nginx configuration permission script then applies the final managed state:

```text
root:www-data 0640
```

Because the Nginx reset runs after the PHP reset during `ss-perms`, the complete permission workflow leaves Adminer root-owned and readable by PHP-FPM through the `www-data` group. Running only `ss-install-php-config` can temporarily leave the intermediate PHP permissions until the Nginx or full permission reset runs.

Do not move Adminer into the public WordPress document root. The managed file belongs under `/var/www/meta/`, with access provided only through explicit Nginx locations.

## Caching and sessions

The Adminer Nginx locations explicitly disable FastCGI caching:

```nginx
fastcgi_cache off;
```

Database administration pages must remain dynamic and must not share cached authentication or query responses.

Browser cookies and Adminer session behavior are handled by Adminer and PHP. Closing a tab is not a substitute for logging out when a browser or workstation is shared.

## Logs

Adminer requests use the normal SlickStack Nginx and PHP logs:

```text
/var/www/logs/nginx-access.log
/var/www/logs/nginx-error.log
/var/www/logs/php-error.log
```

Useful checks include:

```bash
tail -n 100 /var/www/logs/nginx-error.log
tail -n 100 /var/www/logs/php-error.log
grep adminer /var/www/logs/nginx-access.log | tail -n 50
```

The randomized path may not literally contain the word `adminer`, so search the access log for the configured `ADMINER_URL` when needed.

## Disabling Adminer

Set:

```bash
ADMINER_PUBLIC="false"
```

Then run:

```bash
sudo bash /var/www/ss-install-php-config
sudo bash /var/www/ss-install-nginx-config
```

or the complete installer.

Afterward, verify:

```bash
test ! -f /var/www/meta/adminer.php
test ! -f /var/www/sites/includes/adminer.conf
```

Disabling Adminer does not remove MySQL users, databases, backup scripts, WP-CLI database commands, or normal WordPress database connectivity.

## Updating Adminer

The active Adminer file is refreshed whenever `ss-install-php-config` successfully retrieves and validates the mirrored source.

Do not replace `/var/www/meta/adminer.php` manually if the change must survive SlickStack reinstallations. Update the managed repository source and installation workflow instead.

Adminer updates should be reviewed like other privileged server-management software because the application can execute SQL and access sensitive database content.

## Adminer is not a backup system

Adminer can export and import database content, but it is not SlickStack's scheduled backup mechanism.

Use the managed dump and restoration scripts for normal recovery workflows:

```bash
sudo bash /var/www/ss-dump-database
sudo bash /var/www/ss-import-database
```

See [Backups](backups.md) and [MySQL](mysql.md).

A browser upload can fail because of PHP upload limits, execution timeouts, browser interruptions, proxy limits, or large SQL files. Command-line imports are more predictable for substantial databases.

## Security recommendations

For normal SlickStack deployments:

- keep `ADMINER_URL` long and private
- do not treat a random path as authentication
- use HTTPS only
- use `DB_USER` instead of `admin` when full privileges are unnecessary
- disable Adminer when no database work is planned
- protect enabled staging and development hosts
- treat the shared Adminer URL as sensitive across every enabled host
- avoid saving database passwords in shared browsers
- verify the selected database before destructive actions
- retain a current database dump before major changes

Cloudflare, Iptables, Nginx rate limiting, and a randomized URL reduce exposure but do not make a publicly reachable database administration tool risk-free.

## Troubleshooting

### Adminer URL returns 404

Confirm:

```bash
grep '^ADMINER_PUBLIC=' /var/www/ss-config
grep '^ADMINER_URL=' /var/www/ss-config
ls -l /var/www/meta/adminer.php
ls -l /var/www/sites/includes/adminer.conf
```

Use the exact configured `ADMINER_URL` with a trailing slash on production, staging, or development, then rerun both installers when files are missing.

A 404 is expected when:

- `ADMINER_PUBLIC="false"`
- `/var/www/meta/adminer.php` is missing
- the requested environment is disabled
- PHP or Nginx configuration is incomplete

### The page returns 502 or 504

Check PHP-FPM and Nginx:

```bash
systemctl status nginx
systemctl status php8.3-fpm
tail -n 100 /var/www/logs/nginx-error.log
tail -n 100 /var/www/logs/php-error.log
```

Use the PHP service version matching the server's Ubuntu release.

### Login fails on a local database

Confirm that:

- the server is `127.0.0.1`
- the username is `admin` or the configured `DB_USER`
- the password matches `DB_PASSWORD_ROOT` or `DB_PASSWORD`
- MySQL is active
- the selected database exists

Do not use the operating-system `SUDO_PASSWORD` unless it was intentionally configured as the database password too.

### Login fails on a remote database

Confirm the remote host, port, user, password, database grants, provider firewall, and whether the remote server accepts connections from the SlickStack server.

### Requests suddenly close without a normal error page

Nginx currently returns status `444` for requests rejected by its request limiter. Check the Nginx logs and avoid repeatedly refreshing or submitting many Adminer requests in a short burst.

The `LIMIT_REQUESTS_ADMINER_*` values do not currently change the hardcoded active rate zone.

### Adminer reappears after manual deletion

The PHP configuration installer can restore the managed file while `ADMINER_PUBLIC="true"`.

Set the option to `false` and rerun both related installers instead of deleting only one file.

## Scope

The standard SlickStack Adminer design assumes:

- one managed Adminer PHP file
- browser access through SlickStack Nginx and PHP-FPM
- one randomized path shared across production, staging, and development
- manual database authentication
- local MySQL access through `admin` or `DB_USER`
- optional remote MySQL access
- no FastCGI caching
- occasional administrative use

phpMyAdmin, PostgreSQL management, automatic login, single sign-on, two-factor authentication, a separate Adminer domain, per-user access policies, Cloudflare Access integration, permanent public exposure, automated SQL deployment, and using Adminer as a backup platform are outside the standard SlickStack implementation.