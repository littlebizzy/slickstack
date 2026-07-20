# WordPress

SlickStack installs and manages WordPress Core, environment-specific configuration files, WP-CLI, server-driven WP-Cron, permissions, and optional staging and development sites.

The standard design uses one production WordPress site per server, with optional staging and development environments under the same primary domain.

## Table of Contents

- [Environment layout](#environment-layout)
- [WordPress Core installation](#wordpress-core-installation)
- [Fresh installations](#fresh-installations)
- [HoverCraft and stock extensions](#hovercraft-and-stock-extensions)
- [Managed wp-config.php](#managed-wp-configphp)
- [Authentication salts](#authentication-salts)
- [Custom constants and code](#custom-constants-and-code)
- [Domains and HTTPS](#domains-and-https)
- [Database settings](#database-settings)
- [WordPress settings in ss-config](#wordpress-settings-in-ss-config)
- [Updates and file modifications](#updates-and-file-modifications)
- [Media and uploads](#media-and-uploads)
- [WP-Cron](#wp-cron)
- [WP-CLI](#wp-cli)
- [Staging behavior](#staging-behavior)
- [Development behavior](#development-behavior)
- [WordPress Multisite](#wordpress-multisite)
- [Permissions](#permissions)
- [Object caching and cache clearing](#object-caching-and-cache-clearing)
- [Backups and imports](#backups-and-imports)
- [Recommended management commands](#recommended-management-commands)
- [Scope](#scope)
- [Related guides](#related-guides)

## Environment layout

SlickStack uses these web roots:

| Environment | Web root | Default URL |
| :------------- | :---------- | :---------- |
| Production | `/var/www/html/` | `https://SITE_FULL_DOMAIN` |
| Staging | `/var/www/html/staging/` | `https://staging.SITE_DOMAIN_EXCLUDING_WWW` |
| Development | `/var/www/html/dev/` | `https://dev.SITE_DOMAIN_EXCLUDING_WWW` |

Production is always present. Staging and development are enabled in `ss-config`:

```bash
STAGING_SITE="false"
DEV_SITE="false"
```

When enabled, SlickStack creates the corresponding Nginx server block, database, WordPress files, and managed `wp-config.php` file.

Staging and development can also be protected with the shared guest credentials:

```bash
STAGING_SITE_PROTECT="false"
DEV_SITE_PROTECT="false"
GUEST_USER="example"
GUEST_PASSWORD="example-password"
```

## WordPress Core installation

The main WordPress package installer is:

```bash
sudo bash /var/www/ss-install-wordpress-packages
```

It downloads the SlickStack WordPress archive and refreshes WordPress Core across production and any enabled staging or development environments.

For each environment, the installer:

1. removes the existing `wp-admin` and `wp-includes` directories
2. removes top-level PHP files except `wp-config.php`
3. extracts a fresh WordPress archive
4. removes Akismet, Hello Dolly, and bundled Twenty Twenty themes from the fresh archive
5. copies the refreshed Core files into the environment
6. leaves existing custom `wp-content` files in place unless the new archive replaces a matching path
7. resets managed WordPress permissions
8. purges PHP OPcache

Production Core checksums are verified with WP-CLI after the refresh.

This process is useful for normal Core maintenance and can replace several common modified or infected Core targets, but it is not a complete malware scanner or incident-response system.

## Fresh installations

When the production database does not yet contain an installed WordPress site, SlickStack runs `wp core install` automatically.

The initial values are derived from `ss-config`:

- the site URL uses `SITE_FULL_DOMAIN`
- the site language uses `SS_LANGUAGE`
- the initial administrator username uses `SFTP_USER`
- the initial administrator password uses `SFTP_PASSWORD`
- the initial administrator email uses `SFTP_USER@SITE_DOMAIN_EXCLUDING_WWW`

The WordPress administrator password can be changed later from WordPress without changing the server’s SFTP password.

SlickStack does not create a mailbox for the generated administrator address or configure outgoing delivery. See [Email](email.md).

A new development environment is also installed independently when `DEV_SITE="true"` and its database is empty.

Staging is normally populated by copying production rather than creating a separate blank WordPress installation.

## HoverCraft and stock extensions

SlickStack installs the HoverCraft theme as part of the WordPress package workflow and configures it as the default fallback theme in the current generated WordPress configuration.

The managed Core refresh removes these stock extensions from the newly downloaded WordPress archive:

```text
Akismet
Hello Dolly
Twenty Twenty themes
```

Existing third-party plugins and themes already under `wp-content` are not broadly deleted by the Core refresh.

The separate [MU Plugins](mu-plugins.md) guide documents the must-use plugins installed and updated by SlickStack.

## Managed wp-config.php

SlickStack generates a separate configuration file for each enabled environment:

```text
/var/www/html/wp-config.php
/var/www/html/staging/wp-config.php
/var/www/html/dev/wp-config.php
```

Do not edit these files directly. They are rebuilt by:

```bash
sudo bash /var/www/ss-install-wordpress-config
```

The configuration installer uses current `ss-config` values and managed templates to set database credentials, domains, security salts, environment behavior, update settings, file permissions, revisions, autosave behavior, uploads, and related WordPress constants.

It also updates the `home` and `siteurl` database options for each enabled environment and purges PHP OPcache after installing the files.

Direct edits may disappear the next time `ss-install`, `ss-install-nginx-config`, or `ss-install-wordpress-config` runs.

## Authentication salts

The WordPress configuration installer generates new random values for all WordPress authentication keys and salts every time it rebuilds an environment’s `wp-config.php`.

Regenerating salts invalidates existing WordPress login cookies. Users may therefore need to sign in again after the configuration installer runs.

## Custom constants and code

For persistent custom constants or small configuration additions, use:

```text
/var/www/html/wp-content/custom-functions.php
```

The production `wp-config.php` loads that file when it exists. Environment-specific copies can also exist under the staging and development `wp-content` directories.

SlickStack protects these files as configuration files using ownership `root:www-data` and mode `0440`.

Use this mechanism carefully. Syntax errors or conflicting constants can prevent WordPress from loading.

## Domains and HTTPS

SlickStack hardcodes the production WordPress URLs from `SITE_FULL_DOMAIN` and uses HTTPS-only URLs.

The production template defines WordPress content, plugin, language, temporary, and uploads paths under `/var/www/html/`. It also supports HTTPS detection behind Cloudflare or another reverse proxy through `HTTP_X_FORWARDED_PROTO`.

Production, staging, and development use separate cookie domains to reduce login conflicts between environments.

Certificate behavior is documented in [SSL Certificates](ssl.md). Headless domain arrangements are documented in [Headless](headless.md).

## Database settings

Production uses the database configured through:

```bash
DB_NAME="production"
DB_USER="example"
DB_PASSWORD="example-password"
DB_HOST="127.0.0.1"
DB_PORT="3306"
DB_CHARSET="utf8mb4"
DB_COLLATE=""
DB_PREFIX="wp_"
```

Staging and development use databases named `staging` and `development` in the standard managed layout.

Database configuration and maintenance are documented in [MySQL](mysql.md).

## WordPress settings in ss-config

The current WordPress settings include:

```bash
WP_CRON_METHOD="wpcli"
WP_CRON_INTERVAL="often"
WP_MULTISITE="false"
WP_MULTISITE_SUBDOMAINS="true"
WP_MULTISITE_DOMAIN_MAPPING="true"
WP_POST_REVISIONS="3"
WP_AUTOSAVE_INTERVAL="60"
WP_DISALLOW_FILE_EDIT="false"
WP_DISALLOW_FILE_MODS="false"
WP_ALLOW_UNFILTERED_UPLOADS="true"
WP_OBJECT_CACHE="true"
WP_MU_PLUGINS="default"
WP_PLUGIN_BLACKLIST="true"
```

Several other WordPress constants are intentionally hardcoded in the generated templates for predictable behavior.

## Updates and file modifications

The production configuration allows WordPress updates and direct filesystem access:

```text
AUTOMATIC_UPDATER_DISABLED = false
WP_AUTO_UPDATE_CORE = minor
FS_METHOD = direct
```

Minor WordPress Core releases can update automatically. Plugin and theme installation or updates remain available unless `WP_DISALLOW_FILE_MODS` is enabled.

The WordPress editor remains available unless `WP_DISALLOW_FILE_EDIT` is enabled. Disabling the editor is generally safer on production sites, but SlickStack leaves the choice configurable.

The managed WordPress package installer is separate from WordPress’s own updater. Running it deliberately refreshes Core files and should be treated as a server-management operation rather than a normal dashboard update.

## Media and uploads

Production WordPress media is stored normally under:

```text
/var/www/html/wp-content/uploads/
```

WordPress normally organizes uploaded files into year and month subdirectories unless that application setting is changed. Development uses its own uploads directory under `/var/www/html/dev/wp-content/uploads/`.

The managed PHP-FPM configuration currently sets:

```text
upload_max_filesize = 512M
post_max_size = 512M
max_file_uploads = 100
```

These are PHP request limits, not guarantees that every 512 MB upload will succeed. WordPress, Nginx, Cloudflare, plugins, available memory, execution time, and the client connection can impose additional limits. Changes to generated PHP configuration may be replaced by future SlickStack configuration installs.

The default SlickStack MU plugin set includes `disable-image-compression`. It sets WordPress JPEG quality to `100` during image upload and editing. This avoids the normal lossy JPEG quality reduction, but it does not disable image resizing, thumbnail creation, metadata generation, or every optimization performed by WordPress, a theme, another plugin, or an external service.

Staging does not keep an independent uploads tree. Its media requests use the production uploads directory, so uploads, deletions, filename changes, and destructive media tools used through staging can affect production files. Development maintains its own uploads directory, although a production-to-development sync can copy eligible production uploads into it. See [Staging & Development](staging-dev.md) for the full shared-upload warning and synchronization behavior.

Uploads are writable application data. SlickStack normally keeps writable `wp-content` directories owned for the web process, with directories generally using mode `0775` and files using `0664`. Run `sudo bash /var/www/ss-perms` to restore the managed permissions policy instead of applying broad manual ownership or `0777` permissions.

A database dump alone does not preserve media files. File backups and migrations must include the uploads tree, and restores should keep the database attachment records and corresponding files from a compatible recovery point. Because staging shares production uploads, a production file backup also protects the files served by staging; development uploads require their own file coverage when they contain unique media.

SlickStack does not configure or manage object storage, CDN media libraries, or WordPress media-offloading plugins. When uploads are moved to Amazon S3, Cloudflare R2, Backblaze B2, or another external service, credentials, synchronization, URL rewriting, lifecycle rules, backups, restores, and migration behavior remain the responsibility of the selected plugin and storage provider.

## WP-Cron

SlickStack disables request-triggered WP-Cron in generated WordPress configuration:

```text
DISABLE_WP_CRON = true
ALTERNATE_WP_CRON = false
```

Instead, the root cron system runs due WordPress events at the configured interval.

The defaults are:

```bash
WP_CRON_METHOD="wpcli"
WP_CRON_INTERVAL="often"
```

`often` runs every two minutes.

Supported intervals are:

```text
minutely
often
regular
quarter-hourly
half-hourly
hourly
```

The recommended `wpcli` method runs:

```bash
wp cron event run --due-now
```

For Multisite, SlickStack enumerates every network site and runs due events for each URL. WP-CLI is therefore required for the managed Multisite cron workflow.

The optional `server` method executes `wp-cron.php` directly with PHP for single-site installations.

Errors from the WP-CLI cron workflow are appended to:

```text
/var/www/logs/wp-cli.log
```

## WP-CLI

SlickStack installs WP-CLI at:

```text
/usr/local/bin/wp
```

Its managed configuration is stored at:

```text
/var/www/meta/wp-cli.yml
```

Production is the default target. Environment aliases include:

```text
@production
@prod
@staging
@development
@dev
@both
@all
```

Examples:

```bash
wp option get siteurl
wp @staging option get siteurl
wp @development plugin list
wp @all core version
```

Run WordPress operations as the web user when using explicit server commands:

```bash
sudo -u www-data wp --path=/var/www/html plugin list
```

SlickStack disables several WP-CLI commands that can conflict with its managed configuration:

```text
config create
config delete
config edit
config set
db cli
db create
db drop
db reset
package
server
shell
```

Use SlickStack scripts for server provisioning, database creation, and managed configuration. Use WP-CLI for normal WordPress application tasks.

## Staging behavior

Staging is intended as a disposable copy of production for short-term testing.

The standard sync command is:

```bash
sudo bash /var/www/ss-sync-staging
```

When staging is enabled, synchronization:

1. creates a fresh production SQL dump
2. imports the production database into the `staging` database
3. copies most production `wp-content` files into staging
4. skips uploads, MU plugins, the object-cache drop-in, the plugin blacklist, and files larger than 25 MB
5. updates production URLs to the staging domain
6. restores managed WordPress configuration and permissions

Staging uploads are linked to the production uploads directory instead of being copied. Media added or removed through staging can therefore affect the shared production uploads tree.

The staging object-cache drop-in is removed and object caching is disabled in its generated `wp-config.php`.

Automatic staging synchronization is controlled by:

```bash
SS_SYNC_STAGING="true"
INTERVAL_SS_SYNC_STAGING="half-daily"
```

Do not store unique content or configuration only in staging when synchronization is enabled. The next sync can overwrite its database and most copied files.

Staging synchronization is skipped for WordPress Multisite.

## Development behavior

Development is intended as a more independent environment for new designs or larger changes.

It is not automatically synchronized from production by default:

```bash
SS_SYNC_DEVELOPMENT="false"
```

To permit the managed production-to-development copy, set that value to `true`, then run:

```bash
sudo bash /var/www/ss-sync-development
```

The development sync:

1. copies the production database into `development`
2. copies most production `wp-content`, including uploads
3. skips MU plugins, the object-cache drop-in, temporary upgrade directories, and files larger than 25 MB
4. replaces production URLs with the development domain
5. restores managed WordPress configuration and permissions

The command is skipped when development is disabled, development syncing is not enabled, Multisite is active, or `SS_LOCKDOWN="true"`.

Development uses its own uploads directory and does not share production uploads through the staging symlink behavior.

## WordPress Multisite

Multisite is enabled with:

```bash
WP_MULTISITE="true"
WP_MULTISITE_SUBDOMAINS="true"
```

The standard SlickStack configuration recommends the subdomain approach. Domain mapping is intended for subdomain-based networks.

Staging and development workflows are not supported as normal managed copies when Multisite is enabled. The staging and development sync scripts explicitly skip Multisite.

Subdomain Multisite also affects certificate requirements and normally requires a wildcard certificate. See [SSL Certificates](ssl.md).

Multisite introduces additional operational complexity and should be tested carefully before use on business-critical or high-traffic installations.

## Permissions

SlickStack divides WordPress files into explicit ownership zones:

- web-root directories are owned by the SFTP user with group `www-data`
- top-level WordPress PHP files and Core directories are owned by `www-data`
- plugins, themes, languages, uploads, and upgrade directories are writable by `www-data`
- `wp-config.php`, `ss-constants.php`, and detected `custom-functions.php` files are owned by `root:www-data` with mode `0440`
- Core directories generally use mode `0755` and Core files use `0644`
- writable `wp-content` directories generally use `0775` and files use `0664`

To restore the complete managed permissions policy, run:

```bash
sudo bash /var/www/ss-perms
```

For WordPress-specific repair, the component scripts are:

```bash
sudo bash /var/www/ss-perms-wordpress-packages
sudo bash /var/www/ss-perms-wordpress-config
sudo bash /var/www/ss-perms-wordpress-mu-plugins
```

Manual ownership changes may be reverted by installation, import, synchronization, or scheduled permission tasks.

## Object caching and cache clearing

Production can use the managed Memcached object-cache drop-in. Staging and development remove that drop-in during synchronization and disable their generated object-cache setting.

The production configuration also integrates the Clear Caches MU plugin with Nginx cache, PHP OPcache, object cache, and WordPress transients.

See [Memcached](memcached.md), [PHP-FPM](php-fpm.md), and [Nginx](nginx.md) for the separate cache layers.

## Backups and imports

Before refreshing Core, synchronizing environments, importing content, or changing major configuration, confirm that current database and file backups exist.

SlickStack’s dump, restore, and off-server transfer behavior is documented in [Backups](backups.md).

## Recommended management commands

Common WordPress management commands include:

```bash
sudo bash /var/www/ss-install-wordpress-packages
sudo bash /var/www/ss-install-wordpress-config
sudo bash /var/www/ss-install-wordpress-cli
sudo bash /var/www/ss-install-wordpress-mu-plugins
sudo bash /var/www/ss-sync-staging
sudo bash /var/www/ss-sync-development
sudo bash /var/www/ss-perms
```

A complete `ss-install` can safely reapply the broader managed stack when `ss-config` is current:

```bash
sudo bash /var/www/ss-install
```

## Scope

The standard SlickStack WordPress design assumes:

- one production WordPress site per server
- optional staging and development subdomains
- managed WordPress Core and generated `wp-config.php` files
- server-driven WP-Cron
- WP-CLI for application management
- SlickStack-controlled permissions and environment synchronization
- WordPress files under `/var/www/html/`

Multiple independent production domains, arbitrary WordPress directory layouts, unmanaged `wp-config.php` ownership, Composer-based WordPress roots, containerized WordPress, Bedrock-style structures, and custom environment orchestration are outside the standard managed configuration.

## Related guides

- [Installation](installation.md)
- [SS-Config](ss-config.md)
- [MU Plugins](mu-plugins.md)
- [Staging & Development](staging-dev.md)
- [Email](email.md)
- [Backups](backups.md)
- [Headless](headless.md)
