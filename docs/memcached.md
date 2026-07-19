# Memcached

SlickStack uses Memcached as a local persistent object cache for the production WordPress site.

Memcached stores frequently reused WordPress objects in server memory so repeated database queries can often be avoided. It is separate from the Nginx FastCGI page cache and PHP OPcache, which cache different parts of the request lifecycle.

## Table of Contents

- [Packages](#packages)
- [Configuration](#configuration)
- [WordPress object cache](#wordpress-object-cache)
- [Cache behavior](#cache-behavior)
- [Cache layers](#cache-layers)
- [Purging Memcached](#purging-memcached)
- [Restarting Memcached](#restarting-memcached)
- [Troubleshooting](#troubleshooting)
- [Scope](#scope)

## Packages

SlickStack installs the Memcached service together with command-line tools and PHP extensions:

```text
memcached
libmemcached-tools
php-memcached
php-memcache
```

The relevant installation commands are:

```bash
sudo bash /var/www/ss-install-memcached-packages
sudo bash /var/www/ss-install-memcached-config
```

Both scripts are idempotent and restart Memcached after installing packages or configuration.

## Configuration

SlickStack manages the Memcached configuration at:

```text
/etc/memcached.conf
```

The standard configuration:

- runs Memcached as the `memcache` user
- allocates 256 MB for cached objects
- listens on TCP port `11211`
- binds only to `127.0.0.1` and `::1`
- disables the UDP listener
- writes its PID under `/var/run/memcached/`
- writes logs to `/var/www/logs/memcached.log`

Memcached is therefore available only to local processes on the SlickStack server. SlickStack does not expose port `11211` publicly or configure a remote Memcached service.

Direct edits to `/etc/memcached.conf` may be replaced the next time `ss-install` or `ss-install-memcached-config` runs. The current Memcached memory allocation, port, listener addresses, and daemon settings are fixed in the managed boilerplate rather than generated from `ss-config` variables.

## WordPress object cache

SlickStack installs a WordPress `object-cache.php` drop-in at:

```text
/var/www/html/wp-content/object-cache.php
```

The drop-in is installed only for the production site. Staging and development sites do not receive it, which avoids cache conflicts while those environments are synchronized or tested.

Installation is controlled by:

```bash
WP_OBJECT_CACHE="true"
```

When object caching is enabled, SlickStack downloads the approved Memcached drop-in and installs it in the production `wp-content` directory. When disabled, the drop-in is removed.

The installer also removes potentially conflicting object-cache drop-ins and normal Redis or Memcached cache plugins before installing the managed file. The production domain is used as `WP_CACHE_KEY_SALT` so cache keys are namespaced for the site.

## Cache behavior

Memcached is an in-memory cache and is never the authoritative source for WordPress content. Posts, options, users, metadata, and other permanent data remain stored in MySQL.

Cached objects can disappear because of expiration, eviction, a service restart, a server reboot, or a manual purge. WordPress should then retrieve fresh data from MySQL and repopulate the cache.

WordPress transients may also be stored in Memcached while the persistent object cache is active. Purging Memcached can therefore remove both ordinary cached objects and cached transients.

The default Clear Caches MU plugin is configured to clear the WordPress object cache as part of its broader cache-clearing workflow.

## Cache layers

SlickStack uses several independent cache layers:

| Cache | Purpose | Purge command |
| :------------- | :---------- | :---------- |
| Nginx FastCGI | Cached HTTP page responses | `sudo bash /var/www/ss-purge-nginx` |
| Memcached | WordPress objects and cached transients | `sudo bash /var/www/ss-purge-memcached` |
| PHP OPcache | Compiled PHP bytecode | `sudo bash /var/www/ss-purge-opcache` |

Purging one layer does not automatically mean every other layer has been cleared unless a SlickStack or WordPress cache-clearing workflow explicitly invokes them together.

## Purging Memcached

To clear every cached object from the local Memcached instance, run:

```bash
sudo bash /var/www/ss-purge-memcached
```

The purge script sends `flush_all` to `127.0.0.1:11211` and also runs `memcflush` as a second clearing method.

A full purge forces WordPress to retrieve and rebuild cached data. Running it during a traffic spike may temporarily increase database and PHP load.

Memcached purges can also be scheduled through the `INTERVAL_SS_PURGE_MEMCACHED` setting in `ss-config`. SlickStack includes supported cron hooks for half-weekly, weekly, half-monthly, and monthly purges.

## Restarting Memcached

To forcefully restart the service, run:

```bash
sudo bash /var/www/ss-restart-memcached
```

Restarting Memcached clears its in-memory contents. SlickStack also restarts the service after reinstalling Memcached packages or configuration.

The current two-minute SlickStack service watchdog checks PHP-FPM, Nginx, and MySQL, but it does not perform a dedicated Memcached health check or automatic Memcached restart.

## Troubleshooting

The main Memcached log is:

```text
/var/www/logs/memcached.log
```

Useful local checks include:

```bash
systemctl status memcached
ss -lntp | grep 11211
printf "stats\r\nquit\r\n" | nc 127.0.0.1 11211
```

If WordPress object caching is not active, verify that:

- the Memcached service is running
- port `11211` is listening on loopback
- the required PHP Memcached extension is loaded
- `/var/www/html/wp-content/object-cache.php` exists
- `WP_OBJECT_CACHE` is not set to `false` in `ss-config`

Re-running the managed package, configuration, and MU plugin installers is safer than manually replacing service or WordPress cache files.

## Scope

The standard SlickStack configuration assumes:

- one local Memcached instance
- one fixed TCP port on loopback
- one production WordPress object-cache drop-in
- no remote access or public listener
- no separate cache instance for staging or development

Memcached clustering, remote Memcached servers, SASL authentication, per-site instances, custom ports, and custom memory allocation are outside the standard managed configuration. Such changes require coordinated service, firewall, PHP, and WordPress modifications and may be overwritten by future SlickStack installations.