# Migrations

SlickStack migrations normally move two separate types of WordPress state:

- the MySQL database
- the production WordPress files

A complete migration requires both. The database contains posts, users, settings, orders, and other application data, while the files contain WordPress Core, plugins, themes, uploads, and other filesystem content.

SlickStack currently provides separate dump and import scripts rather than one complete migration command. Administrators must still transfer the generated files, prepare the new server correctly, handle domain changes when applicable, and verify the final site.

## Table of Contents

- [Migration types](#migration-types)
- [Before starting](#before-starting)
- [SlickStack to SlickStack](#slickstack-to-slickstack)
- [Create dumps on the old server](#create-dumps-on-the-old-server)
- [Prepare the new server](#prepare-the-new-server)
- [Transfer the migration files](#transfer-the-migration-files)
- [Import files on the new server](#import-files-on-the-new-server)
- [Import the database on the new server](#import-the-database-on-the-new-server)
- [Domain changes](#domain-changes)
- [DNS and Cloudflare cutover](#dns-and-cloudflare-cutover)
- [Migrating from another hosting stack](#migrating-from-another-hosting-stack)
- [Staging and development environments](#staging-and-development-environments)
- [Multisite](#multisite)
- [Verification](#verification)
- [Rollback](#rollback)
- [Security and cleanup](#security-and-cleanup)
- [Future migration script](#future-migration-script)
- [Scope](#scope)
- [Related guides](#related-guides)

## Migration types

Common migration paths include:

| Migration | Normal approach |
| :-- | :-- |
| Existing SlickStack server to a new SlickStack server | Use the SlickStack dump scripts on the old server and the import scripts on the new server |
| Another WordPress host to SlickStack | Create compatible database and file archives manually, then use the SlickStack import scripts where appropriate |
| Same server with a new domain | Update `ss-config`, regenerate managed configuration, and perform a serialized database search and replace |
| SlickStack to another hosting platform | Export the database and files, then follow the destination platform's migration process |

The SlickStack import scripts target the production site. They do not provide a complete interactive migration wizard or automatically migrate every external dependency.

## Before starting

Before changing either server:

1. confirm the old site is healthy enough to produce valid backups
2. record the old domain, new domain, database prefix, PHP version, and important plugin requirements
3. identify external services such as Cloudflare, transactional email, object storage, remote databases, license servers, webhooks, payment gateways, and DNS records
4. reduce or pause content changes during the final migration window
5. retain provider-console access to both servers
6. create independent provider snapshots or off-server backups when available
7. verify enough free disk space exists for both the site and temporary archives

Do not destroy the old server immediately after the new site begins responding. Keep it available until the new server has been verified and a rollback window has passed.

## SlickStack to SlickStack

The normal SlickStack-to-SlickStack workflow is:

1. install and configure SlickStack on the new server
2. create fresh database and file dumps on the old server
3. transfer the production dump files securely to the new server
4. rename or place them using the filenames expected by the import scripts
5. run the file import on the new server
6. run the database import on the new server
7. perform any required domain search and replace
8. regenerate or verify managed configuration and permissions
9. test the new origin before changing public DNS
10. cut over Cloudflare or DNS and monitor the site

The dump and import scripts are intentionally separate. This allows each stage to be inspected, transferred, and verified before production data is overwritten.

## Create dumps on the old server

### Database dump

Run on the old SlickStack server:

```bash
sudo bash /var/www/ss-dump-database
```

or:

```bash
ss dump database
```

The production database dump is written to:

```text
/var/www/backups/mysql/production.sql
```

The script also dumps staging and development databases when those environments are enabled, but the standard migration path uses the production dump.

Confirm that the production file exists, is recent, and is not empty:

```bash
sudo ls -lh /var/www/backups/mysql/production.sql
sudo head -n 20 /var/www/backups/mysql/production.sql
```

Remote database providers may restrict the permissions required by `mysqldump`. Do not assume the dump succeeded merely because the command completed without visible output.

### File dump

Run:

```bash
sudo bash /var/www/ss-dump-files
```

or:

```bash
ss dump files
```

The production archive is written to:

```text
/var/www/backups/html/production.tar.gz
```

The production archive contains `/var/www/html/` while excluding the staging and development directories. This is the format expected by the current SlickStack file import workflow.

Confirm that the archive exists and passes a basic integrity check:

```bash
sudo ls -lh /var/www/backups/html/production.tar.gz
sudo tar -tzf /var/www/backups/html/production.tar.gz | head
```

The file dump script does not currently preflight available disk space. Large sites can temporarily exhaust the old server's disk while the archive is created.

## Prepare the new server

Install SlickStack on the destination server before importing production data. The new server needs a valid current `ss-config`, working packages, databases, users, directories, and generated service configuration.

For a same-domain migration, configure the intended production domain on the new server. For a domain change, configure the new domain from the beginning.

Before import, verify:

```bash
sudo bash /var/www/ss-stack-overview
sudo nginx -t
systemctl status nginx --no-pager
systemctl status mysql --no-pager
systemctl list-units --type=service --all | grep -E 'php.*fpm'
```

The destination database represented by `DB_NAME` must already exist and be accessible through the configured WordPress database user.

A fresh SlickStack installation may contain a temporary WordPress site. The import scripts will overwrite its production data, so do not add content to the temporary site that needs to be preserved.

## Transfer the migration files

Transfer the old production dump files to the new server using SFTP, SCP, Rsync, or another secure method.

The new server's database import script accepts:

```text
/tmp/import.sql
/tmp/import.sql.zip
/tmp/import.sql.gz
```

For a normal SlickStack SQL dump, place or rename:

```text
production.sql -> /tmp/import.sql
```

The file import script accepts:

```text
/tmp/import.tar
/tmp/import.tar.gz
```

For a normal SlickStack file dump, place or rename:

```text
production.tar.gz -> /tmp/import.tar.gz
```

For example, when the files have already been uploaded to the new server:

```bash
sudo mv /path/to/production.sql /tmp/import.sql
sudo mv /path/to/production.tar.gz /tmp/import.tar.gz
```

Restrict the temporary files while they are present:

```bash
sudo chown root:root /tmp/import.sql /tmp/import.tar.gz
sudo chmod 0600 /tmp/import.sql /tmp/import.tar.gz
```

Before importing, verify the transferred files again:

```bash
sudo test -s /tmp/import.sql
sudo tar -tzf /tmp/import.tar.gz | head
```

Do not transfer database archives through public web directories or leave them accessible through a browser.

## Import files on the new server

Run:

```bash
sudo bash /var/www/ss-import-files
```

or:

```bash
ss import files
```

The script asks for interactive confirmation and then extracts `/tmp/import.tar` or `/tmp/import.tar.gz` over:

```text
/var/www/html/
```

It strips the archive's first path component, which matches the `html/` directory stored by the SlickStack production dump.

After extraction, the script regenerates the managed WordPress configuration and restores WordPress package, MU plugin, and configuration permissions.

Important boundaries:

- the operation overwrites matching production files
- the current script does not create a fresh file dump before extraction
- it does not separately import staging or development files
- it expects a compatible archive structure
- it does not validate WordPress application behavior after extraction

Create and preserve a destination file backup or provider snapshot before confirming the import.

## Import the database on the new server

Run:

```bash
sudo bash /var/www/ss-import-database
```

or:

```bash
ss import database
```

The script first runs `ss-dump-database` on the new server as a safety measure. It then asks for confirmation before importing the first supported file it finds under `/tmp`.

The import overwrites the configured production database represented by:

```bash
DB_NAME="production"
```

or the custom database name in the new server's `ss-config`.

The script does not separately import staging or development databases. It also does not automatically perform a domain search and replace after import.

Before confirming, verify that:

- `/tmp/import.sql`, `/tmp/import.sql.zip`, or `/tmp/import.sql.gz` is the intended source
- the new server's automatic safety dump was created successfully
- `DB_NAME`, `DB_PREFIX`, and database credentials are correct
- the import file is compatible with the destination MySQL version and permissions

## Domain changes

When the domain remains unchanged, a serialized search and replace is usually unnecessary. WordPress URLs stored in the database should already match the destination domain.

When the domain changes, update the SlickStack domain settings in `/var/www/ss-config` and regenerate managed configuration. The important values normally include:

```bash
SITE_ROOT_DOMAIN="example.com"
SITE_FULL_DOMAIN="www.example.com"
```

After importing the old database, perform a serialized WordPress search and replace with WP-CLI:

```bash
wp search-replace 'https://old.example.com' 'https://new.example.com' --all-tables-with-prefix --precise --dry-run
```

Review the dry-run output, then run the actual replacement:

```bash
wp search-replace 'https://old.example.com' 'https://new.example.com' --all-tables-with-prefix --precise
```

Production is the default WP-CLI target in SlickStack. Explicit server commands can also be run as the web user:

```bash
sudo -u www-data wp --path=/var/www/html search-replace 'https://old.example.com' 'https://new.example.com' --all-tables-with-prefix --precise --dry-run
```

Search-and-replace operations can change serialized application data. Always run a dry run first and preserve a fresh database dump.

Review additional variants when relevant, including:

```text
http://old.example.com
https://old.example.com
http://www.old.example.com
https://www.old.example.com
```

Do not blindly replace unrelated email addresses, external API URLs, storage domains, or historical text merely because they contain the old domain.

After domain changes, verify the generated `wp-config.php`, WordPress `home` and `siteurl` options, Nginx server blocks, certificates, Cloudflare settings, redirects, callback URLs, and plugin licenses.

## DNS and Cloudflare cutover

Test the new server before changing public DNS whenever possible.

Useful approaches include:

- a local hosts-file override
- a temporary testing hostname
- direct origin checks with the correct `Host` header
- Cloudflare origin rules prepared but not yet activated

During final cutover:

1. create one last database dump after content changes are paused
2. transfer and import that final database
3. verify files and database state on the destination
4. update the required DNS records or Cloudflare origin address
5. confirm the active SSL mode matches the destination certificate
6. test public HTTPS, WP Admin, forms, scheduled tasks, APIs, and dynamic account or checkout paths
7. monitor Nginx, PHP, WordPress, and MySQL logs

DNS-only records can expose the origin and bypass Cloudflare. Confirm that public website records use the intended proxy status after migration.

Do not delete the old server or its DNS records until the destination has remained stable and rollback is no longer required.

## Migrating from another hosting stack

A non-SlickStack source can still be migrated, but its exports must be compatible with the SlickStack destination.

### Database

Create a normal MySQL SQL dump without relying on provider-specific restore metadata. Transfer it as one of:

```text
/tmp/import.sql
/tmp/import.sql.zip
/tmp/import.sql.gz
```

The current import script sends the SQL into the configured production database using the SlickStack WordPress database user. Imports requiring elevated privileges, database creation, unsupported SQL modes, or provider-specific statements may require manual cleanup or an administrative import method.

### Files

The safest source content is usually the WordPress site files, especially:

```text
wp-content/plugins/
wp-content/themes/
wp-content/uploads/
wp-content/languages/
```

The SlickStack file import script expects an archive with a first directory component that can be stripped before extraction into `/var/www/html/`. A SlickStack-generated archive naturally uses:

```text
html/
```

For a third-party archive, inspect its layout before import:

```bash
tar -tzf archive.tar.gz | head -n 30
```

Do not import another host's server-level configuration, PHP-FPM files, Nginx configuration, database credentials, cache drop-ins, or generated `wp-config.php` over SlickStack's managed files without understanding the consequences.

After importing third-party files, allow SlickStack to regenerate its managed WordPress configuration and MU plugin layer. Review normal plugins for caching, security, backup, SMTP, staging, or hosting-specific behavior that conflicts with SlickStack.

## Staging and development environments

The standard dump scripts can create separate staging and development database and file archives when those environments are enabled. However, the current import scripts target only production.

For a normal server migration, migrate production first. Then recreate staging and development from the verified destination production site using SlickStack's managed synchronization workflows where appropriate.

Staging and development are not equal to independent production sites:

- staging may use production uploads
- synchronization can overwrite test databases and files
- test environments include safeguards that suppress email and background queues
- production push commands are destructive

Do not manually import production archives into staging or development paths without reviewing their environment-specific configuration and safeguards.

## Multisite

WordPress Multisite migrations require additional care.

Review:

- all network domains and mapped domains
- wildcard or SAN certificate coverage
- Cloudflare DNS records for subdomains
- serialized network values
- upload paths
- database tables for every site
- plugin and theme network activation
- Nginx Multisite routing

The normal SlickStack staging and development sync workflows are not supported for Multisite. Do not assume a successful single-site migration procedure covers every Multisite domain and table.

Run WP-CLI searches and verification with explicit Multisite awareness, and preserve a full database backup before changing network URLs.

## Verification

Before declaring the migration complete, verify at least:

### Server and services

```bash
sudo nginx -t
systemctl --failed
systemctl status nginx --no-pager
systemctl status mysql --no-pager
systemctl list-units --type=service --all | grep -E 'php.*fpm'
```

### WordPress

```bash
wp core version
wp core verify-checksums
wp option get home
wp option get siteurl
wp plugin list
wp theme list
```

### Website behavior

- homepage and representative frontend pages
- WP Admin login
- media and downloadable files
- forms and transactional email
- WooCommerce cart, checkout, account, and webhook flows when applicable
- REST API or headless frontend requests
- scheduled events and background queues
- redirects and canonical URLs
- cache bypass for logged-in and dynamic routes
- external storage, payment, license, analytics, and API integrations

### Logs

Review:

```text
/var/www/logs/nginx-error.log
/var/www/logs/php-error.log
/var/www/html/wp-content/debug.log
```

Also inspect the relevant systemd journals when a service behaves unexpectedly.

## Rollback

A migration rollback should be planned before cutover.

A practical rollback path is:

1. keep the old server unchanged and available
2. retain the old DNS or Cloudflare origin information
3. preserve the destination's pre-import database dump and file snapshot
4. record the exact cutover time
5. if the new server fails, restore traffic to the old origin
6. reconcile any orders, registrations, form submissions, or content changes created after cutover

Changing DNS or the Cloudflare origin back does not automatically merge new data from the failed destination into the old server. Dynamic sites should use a short maintenance or content-freeze window to reduce split data.

## Security and cleanup

Migration files contain private production data.

After successful verification and the rollback window:

```bash
sudo rm -f /tmp/import.sql
sudo rm -f /tmp/import.sql.zip
sudo rm -f /tmp/import.sql.gz
sudo rm -f /tmp/import.tar
sudo rm -f /tmp/import.tar.gz
```

Also remove temporary transfer copies from administrator workstations, public storage, and intermediate servers when they are no longer required.

Do not publish:

- SQL dumps
- `wp-config.php`
- `ss-config`
- private keys or certificates
- backup credentials
- customer uploads or personal data

Rotate credentials when the migration involved untrusted intermediaries or when the old server may have been compromised.

## Future migration script

SlickStack is considering a dedicated migration script for a future release.

A unified migration workflow could eventually coordinate tasks such as:

- validating source archives
- checking destination disk space
- creating safety backups
- importing files and the database in the correct order
- applying domain-aware search and replace
- rebuilding managed configuration and permissions
- purging caches
- running verification checks
- preserving clear rollback information

No such complete migration command exists currently. Until one is implemented and tested, use the separate dump, transfer, import, domain-update, and verification steps documented here.

## Scope

SlickStack manages the destination WordPress server and provides dump and import utilities. It does not automatically migrate or reconfigure:

- Cloudflare accounts or DNS zones
- domain registration
- transactional email providers
- external object storage
- remote database providers
- payment gateways
- plugin licenses
- analytics or advertising accounts
- third-party backups
- remote frontend deployments
- provider snapshots or firewalls

Every migration should be treated as a production change with verified backups, a maintenance window appropriate to the site's activity, and a tested rollback path.

## Related guides

- [Installation](installation.md)
- [SS-Config](ss-config.md)
- [Backups](backups.md)
- [WordPress](wordpress.md)
- [MySQL](mysql.md)
- [Staging & Development](staging-dev.md)
- [Cloudflare](cloudflare.md)
- [SSL Certificates](ssl.md)
- [Permissions](permissions.md)
- [Caching](caching.md)
- [Troubleshooting](troubleshooting.md)
