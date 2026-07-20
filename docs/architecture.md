# Architecture

SlickStack is a single-server, WordPress-focused LEMP architecture built around Ubuntu, Nginx, PHP-FPM, MySQL, and Bash automation.

It is designed for one primary production domain per server, with optional staging and development subdomains. SlickStack manages the origin server and its generated configuration; external services such as DNS, the Cloudflare dashboard, email delivery, provider firewalls, and off-server monitoring remain administrator responsibilities.

## Table of Contents

- [System overview](#system-overview)
- [Design goals](#design-goals)
- [Control plane and request plane](#control-plane-and-request-plane)
- [Source configuration and generated state](#source-configuration-and-generated-state)
- [Templates, generated files, and persistent customization](#templates-generated-files-and-persistent-customization)
- [Main filesystem layout](#main-filesystem-layout)
- [Ownership model](#ownership-model)
- [Network boundaries](#network-boundaries)
- [Cloudflare and the origin](#cloudflare-and-the-origin)
- [Nginx layer](#nginx-layer)
- [PHP-FPM layer](#php-fpm-layer)
- [WordPress layer](#wordpress-layer)
- [MySQL layer](#mysql-layer)
- [Memcached layer](#memcached-layer)
- [Cache architecture](#cache-architecture)
- [Production, staging, and development](#production-staging-and-development)
- [Cron and scheduled maintenance](#cron-and-scheduled-maintenance)
- [Installation and reconciliation lifecycle](#installation-and-reconciliation-lifecycle)
- [State and recovery boundaries](#state-and-recovery-boundaries)
- [External responsibilities](#external-responsibilities)
- [Deliberate non-goals](#deliberate-non-goals)
- [Architecture verification](#architecture-verification)
- [Related guides](#related-guides)

## System overview

A normal production request follows this path:

```text
Visitor
  |
  v
Cloudflare edge and public DNS
  |
  v
Provider network / optional provider firewall
  |
  v
SlickStack Iptables firewall
  |  allows public HTTP/HTTPS and rate-limited SSH
  v
Nginx on ports 80 and 443
  |
  +--> HTTP redirect or ACME challenge
  |
  +--> static file response
  |
  +--> eligible Nginx FastCGI cache hit
  |
  `--> approved PHP request
          |
          v
      PHP-FPM on 127.0.0.1:9000
          |
          v
      WordPress
          |
          +--> Memcached on 127.0.0.1:11211
          |      production object cache only
          |
          `--> MySQL on 127.0.0.1:3306
                 or a configured remote database
```

Cloudflare is normally the public edge, but the SlickStack server remains the origin. Nginx, PHP-FPM, WordPress, Memcached, and local MySQL communicate on the same machine in the standard layout.

## Design goals

The architecture favors:

- one predictable WordPress origin per virtual machine
- explicit generated configuration instead of a control panel
- simple local service communication
- repeatable Bash installation and reconciliation
- aggressive but layered caching
- fixed, root-owned maintenance schedules
- clear ownership boundaries between root, `www-data`, SFTP, and sudo users
- limited configuration choices where automatic tuning is safer
- compatibility with external DNS, CDN, backup, monitoring, and server-management services

It intentionally avoids becoming a general-purpose hosting platform or orchestration system.

## Control plane and request plane

SlickStack has two broad architectural sides.

### Control plane

The control plane changes and maintains the server:

```text
/var/www/ss-config
/var/www/ss-functions
/var/www/ss-*
/var/www/crons/
```

Root-run Bash scripts:

- read `ss-config`
- source common paths and functions from `ss-functions`
- download current templates and scripts from public mirrors
- calculate derived values such as PHP-FPM memory and worker limits
- generate service and application configuration
- install packages
- create users and directories
- reset permissions
- purge caches
- restart services
- run scheduled maintenance

The control plane normally runs through `sudo`, root cron, or the initial root installation shell.

### Request plane

The request plane serves the website:

```text
Iptables -> Nginx -> PHP-FPM -> WordPress -> MySQL / Memcached
```

Nginx handles public HTTP and HTTPS connections. PHP-FPM executes approved PHP entry points. WordPress reads persistent data from MySQL and may reuse temporary objects from Memcached. Independent cache layers can satisfy work before a request reaches every downstream component.

A control-plane operation such as `ss-install` can rewrite or restart the request plane. Treat full reconciliation as production maintenance rather than a harmless configuration reload.

## Source configuration and generated state

The central user-editable policy file is:

```text
/var/www/ss-config
```

It contains domains, credentials, enabled features, service choices, maintenance intervals, and supported tuning values.

Changing `ss-config` alone does not change the running server. An installer must consume those values and regenerate the relevant active files.

The usual lifecycle is:

```text
ss-config
    |
    v
SlickStack installer and templates
    |
    +--> /etc/nginx/nginx.conf
    +--> /var/www/sites/*.conf
    +--> /etc/php/<version>/...
    +--> /etc/mysql/my.cnf
    +--> /etc/memcached.conf
    +--> /etc/iptables/rules.v4 and rules.v6
    +--> generated wp-config.php files
    +--> root crontab and SlickStack cron wrappers
    `--> service permissions and runtime directories
```

`ss-config` should contain meaningful administrator policy. Installation scripts increasingly own low-level values that can be derived safely from the machine, such as PHP-FPM and OPcache memory limits. Redundant options may continue to be removed when automatic optimization is more reliable.

## Templates, generated files, and persistent customization

Repository templates are the upstream design. Installers download them, replace placeholders, and install generated copies on the server.

Direct edits to generated files may be replaced by:

- a narrow component installer
- `ss-install`
- `ss-check`
- `ss-worker`
- a scheduled maintenance script
- a permission reset

Persistent customization should use one of these supported paths:

- an existing `ss-config` option
- an explicitly approved Nginx include
- a reserved custom cron file
- an approved WordPress custom file or MU plugin
- a reviewed source-level SlickStack change
- an external provider or Cloudflare control

SlickStack does not load arbitrary Nginx files from `/etc/nginx/conf.d/`, and placing a random file under `/var/www/sites/includes/` does not make Nginx include it. Include filenames must be explicitly referenced by the managed templates.

## Main filesystem layout

The main architecture is organized beneath:

```text
/var/www/
```

Important paths include:

| Path | Architectural role |
| :-- | :-- |
| `/var/www/ss-config` | Central user-selectable policy and credentials |
| `/var/www/ss-functions` | Shared Bash functions, variables, remote URLs, and paths |
| `/var/www/ss-*` | Root-run operational and installation scripts |
| `/var/www/crons/` | Fixed root-owned cron wrappers |
| `/var/www/crons/custom/` | Reserved administrator cron extensions |
| `/var/www/html/` | Production WordPress web root |
| `/var/www/html/staging/` | Optional staging WordPress root |
| `/var/www/html/dev/` | Optional development WordPress root |
| `/var/www/sites/` | Generated Nginx server blocks, includes, and error pages |
| `/var/www/cache/nginx/` | Nginx FastCGI response cache |
| `/var/www/cache/opcache/` | OPcache file cache alongside PHP shared memory |
| `/var/www/cache/wp-cli/` | WP-CLI cache data |
| `/var/www/logs/` | SlickStack-managed service and script logs |
| `/var/www/backups/` | Local database and file backup data |
| `/var/www/certs/` | Certificates and certificate-related files |
| `/var/www/certs/keys/` | Restricted private keys |
| `/var/www/auth/` | SSH and other authentication material |
| `/var/www/meta/` | Generated metadata, helper files, credentials, Adminer, and shell configuration |

Not every directory is public. Nginx serves the configured web roots, not the complete `/var/www/` tree.

## Ownership model

The filesystem is intentionally divided between identities.

| Identity | Main responsibility |
| :-- | :-- |
| `root` | SlickStack scripts, credentials, generated service configuration, private keys, cron, and protected application files |
| `SUDO_USER` | Administrative SSH access and root operations through `sudo` |
| `SFTP_USER` | Jailed file-transfer access to approved portions of `/var/www/` |
| `www-data` | Nginx, PHP-FPM, WordPress runtime writes, plugins, themes, uploads, caches, and selected logs |
| `memcache` | Local Memcached service process |
| `mysql` | Local MySQL service process and internal data directory |

The production root is normally owned by the SFTP user and `www-data` group, while WordPress runtime directories use `www-data`. Sensitive files such as `wp-config.php`, MU plugins, private keys, and `ss-config` use more restrictive ownership and modes.

Do not flatten the architecture with a recursive `chmod 777` or one shared owner. SlickStack periodically reapplies its permission model and can replace incompatible manual changes.

## Network boundaries

### Public services

The standard Iptables policy exposes:

```text
TCP 80   HTTP redirect and certificate validation
TCP 443  HTTPS website traffic
TCP 22   rate-limited SSH and jailed SFTP
```

Other unsolicited inbound traffic is dropped by the managed firewall.

The public HTTP and HTTPS ports remain origin-accessible unless a provider firewall, Cloudflare-origin control, or another carefully tested restriction is applied.

### Local services

The standard internal listeners are:

| Service | Address | Purpose |
| :-- | :-- | :-- |
| PHP-FPM | `127.0.0.1:9000` | Executes PHP requests from Nginx |
| MySQL | `127.0.0.1:3306` | Stores WordPress production, staging, and development data |
| Memcached | `127.0.0.1:11211` and `::1:11211` | Stores production WordPress objects in memory |

Memcached disables UDP and is not intended for remote access. PHP-FPM uses one local `www` pool shared by production, staging, development, Adminer, and Multisite templates.

Remote MySQL is optional. In remote mode, the external provider owns server installation, network access, encryption, backups, tuning, monitoring, and recovery.

## Cloudflare and the origin

SlickStack is designed to operate behind Cloudflare, but it does not manage the Cloudflare account.

Cloudflare can provide:

- public authoritative DNS
- reverse proxying
- edge TLS
- CDN caching
- WAF and bot controls
- DDoS mitigation
- edge redirects and rate limits

SlickStack manages only the origin-side integration, including generated real-IP trust ranges, certificate configuration, and optional origin controls.

The normal starting architecture is:

```bash
CLOUDFLARE_REAL_IPS="true"
CLOUDFLARE_IPS_ONLY="false"
CLOUDFLARE_AUTHENTICATED_ORIGIN="false"
```

The current Cloudflare-only Nginx allowlist conflicts with real-IP restoration and should not be treated as a reliable default origin firewall. Provider firewall rules or a corrected peer-address design are safer when direct-origin access must be restricted.

DNS-only records bypass the Cloudflare request path and expose the origin directly.

## Nginx layer

Nginx is the public origin server and central request router.

The production architecture includes:

- port `80` redirect behavior and ACME challenge handling
- canonical HTTPS on port `443`
- a catch-all redirect to the configured production hostname
- TLS and security headers
- global and route-specific rate limits
- static-file handling and browser cache headers
- approved PHP-entry-point routing
- WordPress rewrites
- Nginx FastCGI page caching
- maintenance and error responses
- optional Cloudflare and certificate includes
- separate production, staging, and development server blocks

The canonical production root is:

```text
/var/www/html
```

Normal WordPress requests use:

```nginx
try_files $uri $uri/ /index.php?$args;
```

Approved PHP requests are passed to:

```text
127.0.0.1:9000
```

Arbitrary PHP files are not generally executable through the standard production PHP location. Nginx allows selected WordPress PHP entry points and returns status `444` for other matching requests.

## PHP-FPM layer

SlickStack installs one PHP version based on the Ubuntu LTS release and uses one local `www` pool.

The standard pool:

- runs as `www-data`
- listens on `127.0.0.1:9000`
- accepts only local clients
- handles production, staging, development, Adminer, and Multisite requests
- restricts executable script extensions to PHP
- uses RAM-based process and memory tuning
- writes PHP errors to `/var/www/logs/php-error.log`

PHP-FPM is not separated into per-environment pools. A PHP resource spike in one enabled environment can therefore compete with the others for the same pool and machine resources.

Multiple PHP versions, remote PHP-FPM, per-site pools, containers, and custom socket layouts are outside the standard architecture.

## WordPress layer

WordPress is the application layer.

SlickStack manages:

- WordPress Core installation and refreshes
- generated `wp-config.php`
- selected constants
- WP-CLI
- WP-Cron execution through fixed SlickStack wrappers
- MU plugins
- permissions
- object-cache integration
- production, staging, and development synchronization

Administrators remain responsible for application security, users, roles, plugins, themes, custom PHP, content, privacy, commerce settings, and external integrations.

WordPress Core and generated configuration can be reconciled by SlickStack, but uploads, database content, plugin state, theme state, and custom application data remain authoritative site data that must be backed up.

## MySQL layer

Local MySQL is the authoritative data store by default.

The normal layout uses:

| Environment | Database |
| :-- | :-- |
| Production | `DB_NAME`, normally `production` |
| Staging | `staging` |
| Development | `development` |

WordPress normally connects over:

```text
127.0.0.1:3306
```

The managed database architecture uses a limited WordPress database account plus separate administrative access.

SlickStack does not enable the standard architecture needed for replication or point-in-time recovery. Binary logging is disabled, and clustering, replicas, automatic failover, and database proxies are outside the normal design.

Local database dumps are written under:

```text
/var/www/backups/mysql/
```

Off-server copies and restore testing remain essential because local backups share the same server failure and compromise boundary.

## Memcached layer

Memcached is a local, non-authoritative object cache.

It listens on loopback port `11211` and stores data only in memory. The production WordPress site receives the managed `object-cache.php` drop-in when object caching is enabled.

Staging and development do not receive the production object-cache drop-in. This avoids key conflicts while test environments are synchronized.

A Memcached restart, purge, eviction, or reboot can discard all cached objects. WordPress must be able to reconstruct them from MySQL.

## Cache architecture

SlickStack uses independent cache layers:

```text
Cloudflare edge cache       external
Browser cache               visitor device
Nginx FastCGI cache         /var/www/cache/nginx/
Nginx open-file cache       Nginx worker memory
PHP OPcache                 PHP shared memory and /var/www/cache/opcache/
Memcached object cache      local memory on 127.0.0.1:11211
WordPress transients        MySQL or Memcached
```

A request may be satisfied before reaching PHP, WordPress, or MySQL.

Production FastCGI cache is bypassed for common dynamic conditions, including POST requests, query strings, logged-in cookies, carts, checkout, accounts, administration, REST API routes, and other sensitive paths.

Each cache has a separate invalidation boundary. Clearing Nginx does not clear Cloudflare, browsers, OPcache, Memcached, or database transients.

## Production, staging, and development

The normal environment layout is:

| Environment | URL | Web root | Database | Intended role |
| :-- | :-- | :-- | :-- | :-- |
| Production | `SITE_FULL_DOMAIN` | `/var/www/html/` | `DB_NAME` | Authoritative live site |
| Staging | `staging.<root-domain>` | `/var/www/html/staging/` | `staging` | Disposable production copy |
| Development | `dev.<root-domain>` | `/var/www/html/dev/` | `development` | More independent workspace |

All enabled environments share:

- the same machine
- the same Nginx service
- the same PHP-FPM pool
- the same CPU, RAM, storage, and network
- the same certificate and DNS boundary
- many common generated settings

They are not isolated virtual machines or containers.

Staging normally maps its uploads path to production uploads. Development uses its own uploads directory. Staging can synchronize automatically from production, while development synchronization is normally manual and disabled by default.

Sync and push scripts copy databases and files; they are not transactional deployment or merge systems. Staging and development are not supported by the standard Multisite synchronization workflow.

## Cron and scheduled maintenance

SlickStack uses a root-owned fixed-wrapper cron architecture.

```text
root crontab
  |
  +--> 01-cron-minutely
  +--> 02-cron-often
  +--> ...
  `--> 14-cron-sometimes
          |
          +--> bundled task selected by INTERVAL_SS_*
          `--> matching administrator custom wrapper
```

The root crontab launches 14 fixed interval wrappers. Each wrapper sources SlickStack configuration, evaluates task intervals, and then sources a reserved custom file.

`flock` prevents overlapping copies of the same wrapper, but different wrappers can still run simultaneously.

Scheduled tasks can:

- refresh scripts and selected packages
- run WP-Cron through WP-CLI
- create database dumps
- send remote backups
- reset permissions
- synchronize staging
- purge selected caches
- renew certificates
- optimize files or databases
- monitor and restart selected failed services

Cron is part of the root control plane. Custom wrapper files are privileged code and must remain trusted.

## Installation and reconciliation lifecycle

A full `ss-install` broadly performs:

```text
validate configuration
        |
        v
refresh SlickStack scripts
        |
        v
update Ubuntu and install base services
        |
        v
build PHP-FPM and Nginx
        |
        v
enable maintenance response
        |
        v
build MySQL, Memcached, and WordPress
        |
        v
synchronize eligible staging state
        |
        v
install firewall and remaining modules
        |
        v
truncate logs and reset permissions
        |
        v
disable maintenance response
        |
        v
purge caches and restart services
        |
        v
repair cron and display overview
```

The architecture is idempotent in the convergence sense: rerunning should restore current managed templates and settings. It is not side-effect free. A full run can update the kernel, replace configuration, refresh WordPress Core, overwrite staging, truncate logs, purge caches, and interrupt requests.

Use narrow installers when only one layer needs reconciliation.

## State and recovery boundaries

### Authoritative state

Protect and back up:

- `/var/www/ss-config`
- WordPress databases
- `wp-content/uploads/`
- custom plugins, themes, and application files
- approved custom functions and MU plugins
- certificates and private keys
- SSH keys and access material
- remote-backup credentials
- Cloudflare and external-service credentials
- provider configuration and DNS records

### Rebuildable or derived state

Normally rebuildable from authoritative inputs and current SlickStack sources:

- generated Nginx, PHP-FPM, MySQL, Memcached, WordPress, firewall, and cron configuration
- downloaded SlickStack scripts
- WordPress Core files
- Nginx FastCGI cache
- PHP OPcache
- Memcached objects
- WordPress transients
- generated permission state

Rebuildable does not mean disposable during an incident. Preserve logs and current configuration before reconciliation when investigating compromise, data loss, or service failure.

## External responsibilities

SlickStack does not automatically manage:

- domain registration
- Cloudflare nameservers, DNS records, proxy status, WAF, or dashboard rules
- provider firewalls and security groups
- provider snapshots
- outbound transactional email or mailbox hosting
- external uptime monitoring and alerting
- centralized log storage or SIEM
- malware, rootkit, or vulnerability scanning
- off-server backup provider accounts and retention
- remote database infrastructure
- application-specific API credentials
- WordPress user governance and two-factor authentication
- business continuity planning and compliance

These systems sit outside the SlickStack origin and should be documented separately by the administrator.

## Deliberate non-goals

The standard architecture does not aim to provide:

- a browser-based hosting control panel
- multiple unrelated primary TLDs on one server
- shared-hosting multi-tenancy
- Docker or container orchestration
- Kubernetes
- clustered Nginx or PHP-FPM
- load balancing
- remote or per-site PHP-FPM pools
- Memcached clustering
- automatic MySQL replication or failover
- zero-downtime deployment
- transactional database merging
- automated deployment rollback
- arbitrary service-plugin discovery
- open-ended Nginx configuration loading
- complete security monitoring
- fully managed Cloudflare or DNS configuration

WordPress Multisite is a separate application feature and does not change SlickStack into a general multi-tenant hosting platform.

## Architecture verification

Inspect public listeners:

```bash
sudo ss -lntup
```

Expected local service listeners normally include:

```text
127.0.0.1:9000   PHP-FPM
127.0.0.1:3306   local MySQL
127.0.0.1:11211  Memcached
```

Validate Nginx and PHP-FPM:

```bash
sudo nginx -t
systemctl status nginx --no-pager
systemctl list-units --type=service --all | grep -E 'php.*fpm'
```

Check local services:

```bash
systemctl status mysql --no-pager
systemctl status memcached --no-pager
```

Review generated site configuration:

```bash
sudo ls -la /var/www/sites/
sudo grep -R "fastcgi_pass" /var/www/sites/
```

Review the main layout:

```bash
sudo find /var/www -maxdepth 2 -type d | sort
```

Check failed services and disk capacity:

```bash
systemctl --failed
df -h /
```

Review output before sharing it because domains, usernames, paths, addresses, and credentials can be sensitive.

## Related guides

- [Installation](installation.md)
- [SS-Config](ss-config.md)
- [Commands](commands.md)
- [Security](security.md)
- [Email](email.md)
- [Nginx](nginx.md)
- [PHP-FPM](php-fpm.md)
- [MySQL](mysql.md)
- [Memcached](memcached.md)
- [Caching](caching.md)
- [Cron Jobs](cron.md)
- [Permissions](permissions.md)
- [Staging & Development](staging-dev.md)
- [Cloudflare](cloudflare.md)
- [WordPress](wordpress.md)
