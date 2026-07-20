# WordPress MU Plugins

SlickStack manages WordPress must-use plugins, supporting loader files, and the production object-cache drop-in through:

```bash
sudo bash /var/www/ss-install-wordpress-mu-plugins
```

This workflow is also run by the complete `ss-install` process. It rebuilds the managed MU plugin directories rather than preserving arbitrary files already placed there.

## Table of Contents

- [How MU plugins work](#how-mu-plugins-work)
- [Installation behavior](#installation-behavior)
- [Environment layout](#environment-layout)
- [Required SlickStack files](#required-slickstack-files)
- [Production object cache](#production-object-cache)
- [Staging and development safeguards](#staging-and-development-safeguards)
- [Default plugin set](#default-plugin-set)
- [Custom plugin mode](#custom-plugin-mode)
- [Managed directories and conflicts](#managed-directories-and-conflicts)
- [Vendored ZIP mirrors](#vendored-zip-mirrors)
- [Updating vendored ZIPs](#updating-vendored-zips)
- [Troubleshooting](#troubleshooting)
- [Scope](#scope)
- [Related guides](#related-guides)

## How MU plugins work

WordPress loads must-use plugins automatically from:

```text
wp-content/mu-plugins/
```

They do not use the normal activation and deactivation controls in WP Admin. SlickStack installs an `autoloader.php` file because most managed plugins are stored in their own subdirectories rather than as top-level PHP files.

The MU plugin workflow also manages `object-cache.php`. That file is technically a WordPress drop-in rather than a must-use plugin, but it is installed by the same SlickStack script because it is part of the managed WordPress cache layer.

## Installation behavior

At the beginning of the workflow, SlickStack creates the required MU plugin directories and removes their existing contents for production and any enabled staging or development environment.

It then:

1. installs the required `000-common.php` file
2. installs `autoloader.php`
3. installs or removes the production `object-cache.php` drop-in
4. installs staging and development safeguards when those environments are enabled
5. installs either the default plugin set or the configured custom plugin set
6. runs `ss-worker` so generated SlickStack constants and support files are current
7. restores managed MU plugin permissions
8. purges PHP OPcache
9. removes temporary download and extraction files

Run the workflow directly with:

```bash
sudo bash /var/www/ss-install-wordpress-mu-plugins
```

The installed Bash aliases also include:

```bash
ss install mu plugins
ss install wordpress mu plugins
```

Because the workflow clears the MU plugin directories first, do not store unmanaged custom files there and expect them to survive a reinstall.

## Environment layout

The managed locations are:

| Environment | MU plugin directory | Object cache |
| :-- | :-- | :-- |
| Production | `/var/www/html/wp-content/mu-plugins/` | Optional managed `/var/www/html/wp-content/object-cache.php` |
| Staging | `/var/www/html/staging/wp-content/mu-plugins/` | Not installed |
| Development | `/var/www/html/dev/wp-content/mu-plugins/` | Not installed |

Production is always present. Staging and development paths are managed when their corresponding environments are enabled in `ss-config`.

## Required SlickStack files

The following files are installed independently from the selectable default or custom plugin set.

### `000-common.php`

SlickStack installs:

```text
wp-content/mu-plugins/000-common.php
```

in production and each enabled test environment.

This required plugin provides shared SlickStack behavior inside WordPress, including environment-aware login and admin information, the SlickStack status dashboard, selected warnings and toolbar features, generated server constants, and other common integration behavior.

The installer replaces domain and whitelabel placeholders before copying the file. `ss-worker` then refreshes the generated SlickStack constants used by the plugin.

Do not remove or edit the installed copy directly. The next MU plugin installation can replace it.

### `autoloader.php`

SlickStack installs:

```text
wp-content/mu-plugins/autoloader.php
```

in production and each enabled test environment.

The autoloader supports the directory-based plugin packages used by SlickStack, such as:

```text
mu-plugins/force-https/force-https.php
```

Without the managed loader, WordPress normally loads only top-level PHP files directly from `mu-plugins`.

## Production object cache

The MU plugin workflow downloads and manages the Memcached object-cache drop-in at:

```text
/var/www/html/wp-content/object-cache.php
```

Installation is controlled by:

```bash
WP_OBJECT_CACHE="true"
```

When the value is anything other than `false`, the drop-in is installed for production. When it is `false`, the managed production drop-in is removed.

Before installing the drop-in, SlickStack removes potentially conflicting production object-cache, Redis, and Memcached plugin paths from the normal `wp-content/plugins` directory.

The drop-in is not installed in staging or development. This avoids sharing a persistent object-cache namespace while test environments are synchronized or modified.

See [Memcached](memcached.md) and [Caching](caching.md) for cache behavior and purge commands.

## Staging and development safeguards

When staging or development is enabled, SlickStack installs these additional MU plugin packages into that environment:

```text
disable-emails
disable-default-runner
```

`disable-emails` suppresses normal WordPress application email from test environments. This reduces the risk of copied production sites sending password resets, order messages, form notifications, or other email to real recipients.

`disable-default-runner` suppresses the default Action Scheduler runner in test environments. This reduces the risk of copied scheduled queues performing background application work as though the environment were production.

Before installing these packages, SlickStack removes normal-plugin copies with the same or older related names from the staging and development `wp-content/plugins` directories.

These safeguards do not make copied production data harmless. Protect test environments, remove sensitive data when appropriate, and verify important plugin behavior separately.

See [Staging & Development](staging-dev.md) for synchronization, shared uploads, indexing, and production-push risks.

## Default plugin set

Unless valid custom mode is selected, SlickStack installs these seven vendored plugin packages into production and each enabled test environment:

| Package | Managed purpose |
| :-- | :-- |
| `clear-caches` | Connects WordPress cache-clearing behavior to the managed cache layers |
| `disable-empty-trash` | Prevents WordPress from automatically emptying Trash |
| `disable-image-compression` | Keeps generated image quality at the configured uncompressed baseline |
| `disable-xml-rpc` | Blocks the WordPress XML-RPC interface and related pingback behavior |
| `force-https` | Keeps WordPress URLs and redirects aligned with HTTPS |
| `plugin-blacklist` | Applies SlickStack's managed plugin blacklist behavior |
| `repoman` | Supports managed plugin updates from approved Git repositories |

The installed directories are:

```text
clear-caches/
disable-empty-trash/
disable-image-compression/
disable-xml-rpc/
force-https/
plugin-blacklist/
repoman/
```

The installer also removes normal-plugin copies and older conflicting namespaces before copying these packages into `mu-plugins`.

## Custom plugin mode

SlickStack can replace the default seven-package set with custom ZIP sources configured in `ss-config`.

Custom mode begins with:

```bash
WP_MU_PLUGINS="custom"
MU_PLUGIN_01_SOURCE="https://example.com/plugin.zip"
MU_PLUGIN_01_DIR="plugin-slug"
```

Additional numbered source and directory variables exist through slot `20`.

The required SlickStack files remain managed in custom mode:

- `000-common.php`
- `autoloader.php`
- the production `object-cache.php` behavior
- staging and development safeguards

Custom mode replaces only the normal default seven-package branch.

Each custom source should be a directly downloadable ZIP whose extracted top-level directory matches the configured `MU_PLUGIN_XX_DIR` value. The directory name is also used as the temporary filename and final directory name.

> **Current limitation:** The custom installer is an older, less defensive implementation. It iterates through all 20 numbered slots and copies custom packages into production, staging, and development paths. Partially populated configurations and servers without both test environments should therefore be tested carefully before relying on custom mode in production.

SlickStack does not validate arbitrary custom plugin code, compatibility, licensing, update safety, or trustworthiness. Use only sources you control or have reviewed.

## Managed directories and conflicts

The MU plugin directories are generated SlickStack state. Reinstallation can remove:

- manually uploaded MU plugins
- direct edits to managed plugins
- abandoned files from an older plugin version
- malware or unauthorized files placed in the managed directory

This replacement behavior keeps the expected plugin set predictable, but it means manual customization inside the directory is not persistent.

The installer also removes selected matching plugins from normal WordPress plugin directories to avoid loading the same functionality twice. This includes the default SlickStack packages, object-cache plugins, and staging or development safeguards.

Use the `ss-config` custom-plugin variables for supported custom packages rather than manually placing files into the managed MU plugin directory.

## Vendored ZIP mirrors

Most default plugin packages are not fetched directly from their standalone repositories during server installation. SlickStack keeps approved ZIP mirrors under:

```text
modules/wordpress/mu-plugins/
```

The public download variables in `bash/ss-functions.txt` point to those committed ZIP files. This keeps server installation independent from live GitHub release API lookups, which can fail because of rate limits or temporary network conditions.

Each default ZIP is expected to contain a clean matching top-level directory, for example:

```text
force-https.zip
└── force-https/
    └── force-https.php
```

The module directory also contains additional optional or historical packages that are not necessarily installed by the current default workflow.

`git-updater.zip` is an explicitly approved external mirror sourced from `afragen/git-updater`, but it is not part of the default seven-package installer set.

## Updating vendored ZIPs

Vendored ZIP files should be updated in the SlickStack repository before servers consume them.

The manual GitHub Actions workflow documented in [GitHub Workflows](workflows.md):

1. finds the latest tagged version from each approved source repository
2. compares it with the version in the committed ZIP
3. downloads the tagged source archive when an update is needed
4. normalizes the top-level directory name
5. rebuilds and verifies the ZIP
6. commits only changed packages

The workflow currently covers the seven default plugin ZIPs plus the approved `afragen/git-updater` mirror. Other optional or historical packages remain outside that workflow unless their source is explicitly reviewed and added.

Server installs should continue using the committed SlickStack mirrors rather than performing live release lookups.

## Troubleshooting

List the managed files:

```bash
sudo find /var/www/html/wp-content/mu-plugins -maxdepth 2 -type f -print
```

Check staging or development when enabled:

```bash
sudo find /var/www/html/staging/wp-content/mu-plugins -maxdepth 2 -type f -print
sudo find /var/www/html/dev/wp-content/mu-plugins -maxdepth 2 -type f -print
```

Confirm the production object-cache drop-in:

```bash
sudo ls -l /var/www/html/wp-content/object-cache.php
```

Rebuild only the managed MU plugin layer:

```bash
sudo bash /var/www/ss-install-wordpress-mu-plugins
```

After rebuilding:

- review the command output for failed downloads or extraction errors
- confirm `000-common.php` and `autoloader.php` exist
- confirm the expected default or custom directories exist
- confirm staging and development safeguards exist when those environments are enabled
- confirm WordPress and WP Admin load normally
- purge or rebuild additional caches only when the affected layer requires it

Do not run the complete `ss-install` merely to repair one missing MU plugin unless broader stack reconciliation is also required.

## Scope

SlickStack manages a specific MU plugin layout for its WordPress environments. It is not a general WordPress plugin marketplace, dependency manager, Composer workflow, or arbitrary release API client.

Normal plugins installed through WP Admin or WP-CLI remain separate from this managed MU plugin layer, except when SlickStack removes known conflicting copies during reconciliation.

## Related guides

- [WordPress](wordpress.md)
- [Staging & Development](staging-dev.md)
- [Memcached](memcached.md)
- [Caching](caching.md)
- [Permissions](permissions.md)
- [GitHub Workflows](workflows.md)
