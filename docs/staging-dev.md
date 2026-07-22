# Staging & Development

SlickStack can create optional staging and development WordPress environments under the same primary domain as production.

The standard roles are:

- **staging**: a disposable production copy for short-term testing
- **development**: a more independent workspace for larger changes or new designs

These environments are convenient, but their sync and push commands can overwrite databases and files. Treat them as server-management tools rather than a full deployment platform.

## Table of Contents

- [Environment layout](#environment-layout)
- [DNS, HTTPS, and indexing](#dns-https-and-indexing)
- [Guest protection](#guest-protection)
- [Environment safeguards](#environment-safeguards)
- [Staging behavior](#staging-behavior)
- [Shared production uploads](#shared-production-uploads)
- [Automatic staging synchronization](#automatic-staging-synchronization)
- [Development behavior](#development-behavior)
- [Development synchronization effects](#development-synchronization-effects)
- [Lockdown and Multisite](#lockdown-and-multisite)
- [Pushing staging to production](#pushing-staging-to-production)
- [Pushing development to production](#pushing-development-to-production)
- [Safe workflow](#safe-workflow)
- [Troubleshooting](#troubleshooting)
- [Managed boundaries](#managed-boundaries)
- [Related guides](#related-guides)

## Environment layout

| Environment | URL | Web root | Database |
| :-- | :-- | :-- | :-- |
| Production | `https://SITE_FULL_DOMAIN` | `/var/www/html/` | `DB_NAME`, normally `production` |
| Staging | `https://staging.SITE_DOMAIN_EXCLUDING_WWW` | `/var/www/html/staging/` | `staging` |
| Development | `https://dev.SITE_DOMAIN_EXCLUDING_WWW` | `/var/www/html/dev/` | `development` |

Production is always present. Enable the optional environments in `/var/www/ss-config`:

```bash
STAGING_SITE="true"
DEV_SITE="true"
```

After changing these values, run:

```bash
sudo bash /var/www/ss-install
```

The installer creates the corresponding database, Nginx server block, WordPress files, generated `wp-config.php`, and permissions.

See [SS-Config](ss-config.md) and [Installation](installation.md) before changing production environment settings.

## DNS, HTTPS, and indexing

Create the relevant DNS records before enabling an environment:

```text
staging.example.com
dev.example.com
```

The domains must also be covered by the active certificate arrangement. Wildcard certificates are normally the simplest option when staging or development subdomains are enabled.

The generated Nginx server blocks:

- redirect HTTP to HTTPS
- use separate environment web roots
- send an `X-Robots-Tag` header that blocks indexing and archiving
- support shared guest HTTP authentication
- use the production `.well-known` directory for verification files

Noindex headers and password protection reduce accidental exposure, but they are not substitutes for removing real customer data, private messages, API credentials, or payment information from test environments.

## Guest protection

Configure optional shared credentials with:

```bash
STAGING_SITE_PROTECT="true"
DEV_SITE_PROTECT="true"
GUEST_USER="example"
GUEST_PASSWORD="example-password"
```

The same guest credentials are used for both protected subdomains.

Use unique credentials and do not reuse an administrator, SFTP, database, or Cloudflare password. Anyone with access to a protected environment may still see copied production content.

## Environment safeguards

SlickStack automatically installs two additional MU plugins in both staging and development:

```text
disable-emails
disable-default-runner
```

`disable-emails` suppresses normal WordPress application email from test environments. This helps prevent copied sites from sending password resets, contact-form notifications, order emails, subscription messages, or other production-style email to real recipients.

`disable-default-runner` suppresses the default Action Scheduler runner in test environments. WooCommerce and many other plugins use Action Scheduler for queued and recurring background jobs, so scheduled tasks may intentionally behave differently from production.

These safeguards reduce accidental side effects but do not make staging or development fully isolated. Plugins may use their own cron systems, direct API requests, external webhooks, or custom email delivery methods. Verify important integrations before assuming they are disabled.

The safeguards are managed by `ss-install-wordpress-mu-plugins`. Reinstalling or refreshing the managed MU plugin layer restores them when staging or development is enabled. See [MU Plugins](mu-plugins.md) for the complete installer behavior and [Email](email.md) for delivery behavior and test-environment precautions.

## Staging behavior

Staging is designed to be refreshed from production rather than maintained as a separate long-lived site.

Run a manual synchronization with:

```bash
sudo bash /var/www/ss-sync-staging
```

or:

```bash
ss sync staging
```

A synchronization occurs only when:

- `STAGING_SITE="true"`
- `SS_SYNC_STAGING` is any value other than `"false"`
- `WP_MULTISITE` is any value other than `"true"`

When those conditions are met, SlickStack:

1. creates a fresh production database dump
2. creates the staging database when missing
3. imports production into the staging database
4. copies selected production `wp-content` files into staging
5. fixes the staging `home` and `siteurl` values
6. runs a production-to-staging WP-CLI search and replace
7. replaces hardcoded production URLs found in staging theme files
8. restores managed WordPress permissions

### Files copied to staging

The staging sync copies production `wp-content` while excluding:

- uploads
- MU plugins
- `object-cache.php`
- `blacklist.txt`
- files larger than 25 MB

Rsync does not broadly delete unmatched staging files, but matching files can be overwritten. The staging database is imported from production and therefore replaced.

Do not keep unique content, orders, settings, or other important state only in staging when synchronization is enabled.

## Shared production uploads

Staging does not maintain a normal independent media library. Its Nginx server block maps:

```text
/wp-content/uploads/
```

to the production directory:

```text
/var/www/html/wp-content/uploads/
```

This reduces disk usage and makes staging synchronization faster, but it creates an important boundary:

> Staging media requests use production files, and media operations performed through staging can affect the production uploads tree.

Do not use staging to test destructive media cleanup, filename migrations, bulk attachment deletion, or untrusted upload tools.

## Automatic staging synchronization

Automatic staging synchronization is controlled by:

```bash
SS_SYNC_STAGING="true"
INTERVAL_SS_SYNC_STAGING="half-daily"
```

Supported scheduled values currently include:

```text
hourly
quarter-daily
half-daily
daily
```

Set:

```bash
SS_SYNC_STAGING="false"
```

when staging must remain unchanged.

The full `ss-install` workflow always invokes the staging sync script. If staging and synchronization are enabled and Multisite is not active, a normal production reinstall can overwrite the staging database and selected files.

See [Cron Jobs](cron.md) for the fixed wrapper schedules.

## Development behavior

Development is intended to be more independent than staging.

Enable it with:

```bash
DEV_SITE="true"
```

Production-to-development synchronization is disabled by default:

```bash
SS_SYNC_DEVELOPMENT="false"
```

To allow a deliberate manual refresh, set:

```bash
SS_SYNC_DEVELOPMENT="true"
```

Then run:

```bash
sudo bash /var/www/ss-sync-development
```

or:

```bash
ss sync development
```

The development sync runs only when all of these conditions are met:

- `DEV_SITE="true"`
- `SS_SYNC_DEVELOPMENT="true"`
- `WP_MULTISITE` is not `"true"`
- `SS_LOCKDOWN` is not `"true"`

Unlike staging synchronization, development synchronization does not currently have a normal recurring interval option. It must be run manually.

## Development synchronization effects

A production-to-development sync:

1. creates a fresh production database dump
2. creates the development database when missing
3. imports production into the development database
4. copies selected production `wp-content` files, including uploads
5. fixes the development `home` and `siteurl` values
6. runs a production-to-development WP-CLI search and replace
7. replaces hardcoded production URLs found in development theme files
8. restores managed WordPress permissions

The file copy excludes:

- MU plugins
- `object-cache.php`
- `blacklist.txt`
- temporary and upgrade directories
- files larger than 25 MB

Development uses its own uploads directory. A sync copies eligible production uploads into that directory rather than mapping requests directly to production as staging does.

The development database and matching files are overwritten by a sync. Keep independent work under version control and back up any database-only changes before refreshing development.

## Lockdown and Multisite

`SS_LOCKDOWN="true"` currently blocks production-to-development synchronization. The option has limited functionality and should not be treated as a complete server freeze or deployment lock.

Normal staging and development sync workflows are not supported for WordPress Multisite. Both scripts skip their database-copy operations when `WP_MULTISITE="true"`.

Do not attempt to bypass these checks without reviewing domain mapping, network tables, uploads, cookies, and certificate behavior.

## Pushing staging to production

The reverse command is:

```bash
sudo bash /var/www/ss-push-staging
```

or:

```bash
ss push staging
```

This is a high-risk production overwrite operation. It requires typing `yes` within the current 30-second prompt.

When confirmed, the script:

- creates the normal SlickStack database dumps
- imports the staging database into the production database
- copies selected staging `wp-content` files into production
- excludes uploads, MU plugins, temporary directories, upgrade directories, the blacklist, and files larger than 5 MB
- replaces staging URLs with the production URL across database tables
- resets WordPress permissions
- purges OPcache, transients, Memcached, and Nginx cache

The copy does not broadly delete unmatched production files, but the production database is replaced. Orders, users, posts, settings, form entries, and other production changes made after staging was copied can be lost.

The script's hardcoded theme-file URL replacement section is currently disabled and marked as needing work.

## Pushing development to production

The development promotion command is:

```bash
sudo bash /var/www/ss-push-development
```

or:

```bash
ss push development
```

The script labels this workflow experimental. It accepts `y` or `yes` at its confirmation prompt and then:

- creates the normal SlickStack database dumps
- imports the development database into production
- copies selected development `wp-content` files into production
- includes uploads up to 512 MB
- excludes MU plugins, temporary directories, upgrade directories, and the blacklist
- replaces development URLs with the production URL across database tables
- resets permissions and purges all SlickStack origin caches

As with the staging push, the production database is overwritten and the hardcoded theme-file replacement section is currently disabled.

Do not treat either push command as transactional deployment, database merging, selective content promotion, or automatic rollback.

## Safe workflow

Before any sync or push:

1. identify the source and destination clearly
2. verify the current `ss-config` values
3. create and inspect fresh database dumps
4. verify an independent off-server backup
5. preserve unique destination content
6. confirm available disk space
7. use a maintenance window for production pushes
8. keep provider-console access available

Useful checks include:

```bash
grep -E '^(STAGING_SITE|DEV_SITE|SS_SYNC_STAGING|SS_SYNC_DEVELOPMENT|SS_LOCKDOWN|WP_MULTISITE)=' /var/www/ss-config
sudo ls -lh /var/www/backups/mysql/
df -h /
```

After a production push, verify:

- the production URL and WordPress login
- recent orders, users, posts, and form entries
- uploads and static assets
- plugin and theme versions
- Nginx and PHP-FPM status
- cache behavior
- scheduled WordPress events

## Troubleshooting

### A sync says conditions were not met

Check:

```bash
grep -E '^(STAGING_SITE|DEV_SITE|SS_SYNC_STAGING|SS_SYNC_DEVELOPMENT|SS_LOCKDOWN|WP_MULTISITE)=' /var/www/ss-config
```

Development synchronization prints the failed condition names. Staging synchronization currently skips quietly after its initial status message when its conditions are not satisfied.

### The environment redirects to the wrong domain

Check the generated WordPress configuration and database values:

```bash
sudo -u www-data wp --path=/var/www/html/staging option get siteurl
sudo -u www-data wp --path=/var/www/html/dev option get siteurl
```

Then inspect remaining URLs:

```bash
sudo -u www-data wp --path=/var/www/html/staging search-replace 'https://production.example.com' 'https://staging.example.com' --all-tables --dry-run
sudo -u www-data wp --path=/var/www/html/dev search-replace 'https://production.example.com' 'https://dev.example.com' --all-tables --dry-run
```

Replace the example domains before running these commands.

### Staging media changes affected production

This is expected under the shared uploads model. Restore the affected production uploads from backup and avoid destructive media testing in staging.

### A production push removed recent data

Stop making changes, preserve current files and database state, and restore the correct production database from a verified backup. The push scripts do not merge newer production records into the source environment.

See [Backups](backups.md), [MySQL](mysql.md), and [Logging](logging.md) for recovery guidance.

## Managed boundaries

SlickStack owns the generated staging and development Nginx blocks, WordPress configuration files, environment databases, permissions, and sync scripts.

Persistent customization should use supported `ss-config` values, approved WordPress custom files, version control, and external deployment tooling where appropriate.

SlickStack's environment tools are designed for a simple single-server workflow. Database merging, selective table promotion, media offloading, content conflict resolution, zero-downtime deployment, and automated rollback remain outside their standard scope.

## Related guides

- [WooCommerce](woocommerce.md)
