# PHP-FPM

SlickStack uses PHP-FPM to execute WordPress and other approved PHP requests passed from Nginx.

PHP-FPM packages, extensions, configuration files, process limits, OPcache settings, permissions, restarts, and basic service recovery are managed by SlickStack. The standard layout uses one local PHP version and one `www` pool for the production, staging, and development sites on the server.

## PHP versions

SlickStack selects the PHP version from the detected Ubuntu LTS version:

| Ubuntu | PHP-FPM |
| :------------- | :----------: |
| 24.04 | 8.3 |
| 22.04 | 8.1 |
| 20.04 | 7.4 |
| 18.04 | 7.2 |

The installer also sets the matching PHP CLI binary as the system default.

## Configuration files

For the active PHP version, SlickStack installs and manages:

```text
/etc/php/<version>/fpm/php.ini
/etc/php/<version>/cli/php.ini
/etc/php/<version>/fpm/php-fpm.conf
/etc/php/<version>/fpm/pool.d/www.conf
```

PHP 8.3 also uses a separate managed OPcache file:

```text
/etc/php/8.3/mods-available/opcache.ini
```

PHP-FPM and PHP runtime errors are written to:

```text
/var/www/logs/php-error.log
```

The same generated `php.ini` is installed for both PHP-FPM and PHP CLI so common resource, upload, serialization, socket, and security settings remain aligned.

## Installation behavior

`ss-install-php-config` retrieves the appropriate boilerplates, applies current `ss-config` values, autotunes resource settings, installs the generated files, resets permissions, and restarts PHP-FPM.

The relevant commands are:

```bash
sudo bash /var/www/ss-install-php-packages
sudo bash /var/www/ss-install-php-config
sudo bash /var/www/ss-restart-php
sudo bash /var/www/ss-purge-opcache
```

Direct edits under `/etc/php/` may be replaced the next time `ss-install` or `ss-install-php-config` runs. Persistent changes should use supported variables in `ss-config` whenever possible.

## Nginx connection

The standard `www` pool runs as `www-data` and listens locally over TCP:

```text
127.0.0.1:9000
```

Nginx sends approved PHP requests to the same address using FastCGI. The PHP-FPM pool only allows local clients, clears inherited environment variables, and restricts executable script extensions to `.php`.

SlickStack uses this TCP listener instead of a Unix socket so the connection remains consistent across its production, staging, development, Adminer, and Multisite Nginx templates.

## RAM autotuning

During `ss-install-php-config`, SlickStack detects total physical RAM and applies conservative PHP-FPM and OPcache limits. These values are designed to prevent small servers from spawning more PHP workers than their memory can safely support.

| Server RAM | PHP memory | OPcache memory | FPM process cap | Pool children | Max requests | Request timeout |
| :------------- | :----------: | :----------: | :----------: | :----------: | :----------: | :----------: |
| Up to ~1.2 GB | 256M | 128M | 8 | 6 | 250 | 60s |
| Up to ~2.4 GB | 384M | 192M | 12 | 10 | 350 | 120s |
| Up to ~4.8 GB | 512M | 256M | 24 | 20 | 500 | 300s |
| More than ~4.8 GB | 768M | 384M | 48 | 40 | 750 | 300s |

The current autotuner uses `ondemand` process management for every RAM tier, with a 10-second idle timeout. Worker and memory values are regenerated during installation, so manually editing those generated values is not persistent.

## PHP settings

Common PHP settings can be controlled through `ss-config`, including:

- execution and input time limits
- memory, POST, and upload limits
- input variables and multipart body limits
- disabled PHP functions and classes
- `allow_url_fopen` and socket timeout behavior
- additional PHP extensions

Several baseline settings are intentionally enforced in the templates. User-level `.user.ini` files are disabled, PHP version exposure and displayed errors are disabled, errors are logged centrally, `allow_url_include` is disabled, and PHP uploads use the WordPress temporary directory under `/var/www/html/wp-content/temp`.

## PHP extensions

SlickStack installs PHP-FPM with the common extensions expected by WordPress, including database, image, internationalization, XML, archive, SOAP, SQLite, cURL, and multibyte string support.

Memcached extensions are also installed for WordPress object caching. Additional version-matched Ubuntu PHP packages may be requested with the `PHP_EXTENSIONS` variable in `ss-config`.

Custom extensions must exist in the configured Ubuntu repositories and must be compatible with the PHP version selected for that Ubuntu release.

## OPcache

OPcache is installed and enabled as part of the managed PHP environment. Its memory allocation is autotuned by RAM, while settings such as revalidation frequency, accelerated file limits, wasted-memory percentage, huge code pages, preload, and blacklist paths can be configured through `ss-config`.

PHP 8.3 includes configurable JIT settings, but the JIT buffer is set to `0` by default, which leaves JIT disabled.

To clear cached PHP scripts after a deployment or troubleshooting change, run:

```bash
sudo bash /var/www/ss-purge-opcache
```

Purging OPcache during a traffic spike can temporarily increase CPU and PHP-FPM load while scripts are compiled again.

## Restarts and recovery

`ss-restart-php` forcefully restarts the installed `php*-fpm` service. PHP-FPM is also restarted automatically after SlickStack installs new PHP packages or configuration files.

The SlickStack cron that runs every two minutes checks the version-specific PHP-FPM systemd service. If systemd reports that the service is inactive or failed, SlickStack runs `ss-restart-php` to recover from failures such as an out-of-memory kill.

This watchdog checks service state rather than application response quality. A PHP-FPM service that remains active but responds slowly or has exhausted its workers will not be restarted solely by this check.

## Scope

The standard SlickStack configuration assumes:

- one PHP version selected by Ubuntu release
- one local `www` PHP-FPM pool
- one TCP listener on `127.0.0.1:9000`
- shared PHP-FPM service for production, staging, and development

Multiple PHP versions, separate per-site pools, remote PHP-FPM servers, containerized PHP, and custom socket layouts are outside the standard managed configuration. Such changes require coordinated PHP-FPM and Nginx edits and may be overwritten by future SlickStack installations.