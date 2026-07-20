<img src="https://repository-images.githubusercontent.com/104382627/49a17307-d8e9-49f3-a320-b3bd9c0f5e70" />

# SlickStack

SlickStack is a free LEMP stack automation script written in Bash designed to enhance and simplify WordPress provisioning, performance, and security.

<a href="https://www.capterra.com/p/211436/SlickStack">Capterra</a> • <a href="https://www.g2.com/products/slickstack/reviews">G2 Crowd</a> • <a href="https://www.producthunt.com/posts/slickstack">Product Hunt</a> • <a href="https://sourceforge.net/software/product/SlickStack/">SourceForge</a>

## Thank you to our sponsors!

[**Become a sponsor**](https://github.com/sponsors/jessuppi) and receive access to our **#perma-lounge** channel on Discord. Your donations and public displays of support for SlickStack are what keep this project going. Thank you very much!

&nbsp;

<a href="https://emplibot.com"><img width="150" style="padding: 20px" src="https://slickstack.io/wp-content/uploads/2024/09/emplibot-square.png" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://www.osoelectric.com"><img width="150" src="https://slickstack.io/wp-content/uploads/2023/04/oso-logo-color-480x467.webp" /></a>

&nbsp;

Our sponsors: [alexbohariuc](https://github.com/alexbohariuc), [backamblock](https://github.com/backamblock), [gingibash](https://github.com/gingibash), [hamzah](https://github.com/hamzah), [liwernyap](https://github.com/liwernyap), [OSO Electric Equipment](https://github.com/Oso-Electric-Equipment), [romfeo](https://github.com/romfeo), [strxno](https://github.com/strxno), [trevplaig](https://github.com/trevplaig), [vivdev](https://github.com/vivdev), [vladbejenaru](https://github.com/vladbejenaru), [volneanschi](https://github.com/volneanschi)

## Installation

Because it’s written purely in Bash (Unix shell), SlickStack has no third-party provisioning dependencies and works on Ubuntu LTS machines. Unlike heavier provisioning tools like EasyEngine or Ansible, it does not require external runtimes such as Python or Docker, meaning a lighter and simpler approach to WordPress servers.

The following installation steps assume that you've already spun up a [KVM cloud server](https://slickstack.io/hosting) on Ubuntu LTS, with at least 1GB+ RAM, and that you are logged in via SSH as `root`:

```
cd /tmp/ && wget -O ss slick.fyi/ss && bash ss
```

After installation, you can manage your SlickStack server by running the bundled scripts located within the `/var/www/` directory using `sudo bash`, as needed. In most cases, however, there shouldn't be much hands-on management because SlickStack runs scheduled cron jobs that connect to this GitHub repo.

Reinstallation through `sudo bash /var/www/ss-install` is designed to reconcile the managed stack without deleting normal WordPress content, but it is a broad maintenance operation that upgrades packages, rewrites managed configuration, may synchronize staging, truncates SlickStack logs, resets permissions, purges caches, and restarts services. Review the [Installation](docs/installation.md) guide before rerunning it on production.

**Note:** SlickStack uses a self-signed OpenSSL certificate by default, so Cloudflare should be active on your domain before SSL (HTTPS) will appear fully secure in browsers. If you wish to use Let's Encrypt instead, be sure to change your settings in `ss-config` before running the installation.

## Requirements

*NOTE: SlickStack is designed for one primary domain per server. It will never support installing multiple TLDs (multi-tenancy) on a single server, and it will not include a built-in UI. This keeps the stack focused on speed, stability, security, and predictable integration with third-party management tools.*

SlickStack works best on [KVM cloud servers](https://slickstack.io/hosting) such as DigitalOcean, Vultr, and Linode. Basic WordPress sites can run on 1GB+ RAM, while 2GB+ RAM is recommended for WooCommerce, Multisite, bbPress, BuddyPress, membership sites, forums, or other dynamic/high-traffic workloads. As your site grows, you can upgrade the server and run `sudo bash /var/www/ss-install` again so SlickStack can re-apply optimized settings for the available resources.

By default, MySQL connects locally over TCP to `127.0.0.1:3306` using databases named `production`, `staging`, and `development`, depending on whether staging/dev sites are enabled. Remote databases can also be used, but server clustering and load balancing are not a SlickStack goal; the stack is focused on simple, high-performance WordPress servers for the majority of sites that do not need complex enterprise architecture.

SlickStack is HTTPS-only and enables HSTS by default, so plain HTTP sites are not supported. Because SlickStack uses self-signed OpenSSL certificates by default, Cloudflare should be active in front of your server before HTTPS will appear fully secure in browsers. After the first `ss-install` has completed, you can switch to Certbot / Let's Encrypt if preferred.

## Documentation

Use these guides for detailed setup, configuration, maintenance, troubleshooting, and architecture information.

| Document | Description |
| :------------- | :---------- |
| [Architecture](docs/architecture.md) | Maps SlickStack's request flow, control plane, service boundaries, filesystem layout, environments, cache layers, generated configuration, external responsibilities, and deliberate non-goals. |
| [Installation](docs/installation.md) | Covers server requirements, first-run setup, preflight checks, the full installer sequence, production reinstallation effects, backups, verification, recovery, and managed-file boundaries. |
| [SS-Config](docs/ss-config.md) | Documents SlickStack's central configuration file, secure editing, build compatibility, schema migration, automatic tuning, pilot files, supported boundaries, rollback, and troubleshooting. |
| [Commands](docs/commands.md) | Provides a curated reference for the `ss` Bash aliases, direct script equivalents, narrow versus compound operations, destructive commands, and known legacy or broken shortcuts. |
| [Troubleshooting](docs/troubleshooting.md) | Provides a first-response runbook for isolating edge, service, application, resource, access, cron, update, and recovery failures while preserving evidence and using narrow repairs. |
| [Updates](docs/updates.md) | Explains script refreshes, `ss-config` migration, Ubuntu package and kernel upgrades, compound update aliases, scheduling, reboots, recovery, and managed-file boundaries. |
| [Cron Jobs](docs/cron.md) | Documents the 14 fixed wrapper intervals, task selection through `ss-config`, WP-Cron, overlap protection, custom cron files, self-healing, logging, and troubleshooting. |
| [Monitoring](docs/monitoring.md) | Covers interactive resource checks, stack overview limits, service status, timestamps, and the automatic PHP-FPM, Nginx, and local MySQL recovery listeners in `02-cron-often`. |
| [Logging](docs/logging.md) | Covers SlickStack and systemd log locations, service-specific behavior, WordPress debug logs, permissions, destructive clearing, retention limits, redaction, and troubleshooting. |
| [Backups](docs/backups.md) | Covers local database and file dumps, backup schedules, production restores, Rclone and Rsync transfers, retention limits, credentials, and recovery verification. |
| [Migrations](docs/migrations.md) | Covers SlickStack and third-party WordPress migrations, dump and import scripts, secure transfer, domain changes, DNS cutover, verification, rollback, and current limitations. |
| [Permissions](docs/permissions.md) | Documents ownership and file modes across SlickStack, WordPress permission zones, SFTP and `www-data` responsibilities, scheduled resets, protected credentials, and troubleshooting. |
| [Ubuntu](docs/ubuntu.md) | Documents supported Ubuntu LTS releases, managed users, SSH and SFTP access, sudoers, utilities, sysctl tuning, swap, cron, package updates, reinstallation, and reboots. |
| [Nginx](docs/nginx.md) | Describes SlickStack-managed Nginx configuration, approved optional include files, supported customization boundaries, and safe reload behavior. |
| [PHP-FPM](docs/php-fpm.md) | Documents PHP version selection, package and configuration installation, Nginx routing, RAM-based process tuning, extensions, OPcache, restarts, and service recovery. |
| [MySQL](docs/mysql.md) | Covers local and remote database modes, managed MySQL configuration, InnoDB tuning, users and databases, dumps, imports, optimization, and service recovery. |
| [Memcached](docs/memcached.md) | Explains the loopback-only Memcached service, WordPress object-cache integration, cache layers, purging schedules, restarts, and troubleshooting. |
| [Caching](docs/caching.md) | Explains Nginx FastCGI cache, PHP OPcache, Memcached, WordPress transients, browser and Cloudflare cache boundaries, purge commands, schedules, bypass rules, and troubleshooting. |
| [Cloudflare](docs/cloudflare.md) | Covers DNS and proxy setup, SSL modes, real visitor IPs, Authenticated Origin Pulls, Certbot DNS credentials, IP range refreshes, origin restrictions, current limitations, and troubleshooting. |
| [SSL (Certs)](docs/ssl.md) | Covers self-signed OpenSSL certificates, Certbot issuance and renewal, Cloudflare wildcard validation, third-party certificates, permissions, and Nginx activation. |
| [Iptables](docs/iptables.md) | Documents the default firewall policies, allowed inbound traffic, installed rule files, persistence behavior, and the limits of custom firewall changes. |
| [Fail2ban](docs/fail2ban.md) | Documents the current SSH and Nginx jails, thresholds, bans, troubleshooting, and planned removal in favor of a simpler Iptables-only approach. |
| [Security](docs/security.md) | Connects SlickStack's SSH, firewall, Cloudflare, TLS, permissions, WordPress, credentials, monitoring, backup, and incident-recovery security boundaries. |
| [WordPress](docs/wordpress.md) | Covers managed WordPress Core installation, generated configuration, WP-Cron, WP-CLI, permissions, updates, Multisite, and staging and development synchronization. |
| [Email](docs/email.md) | Covers WordPress delivery, SMTP and API providers, SPF, DKIM, DMARC, staging safeguards, WooCommerce queues, mailboxes, testing, troubleshooting, and external responsibilities. |
| [MU Plugins](docs/mu-plugins.md) | Explains which WordPress MU plugins SlickStack installs, how vendored ZIP mirrors are managed, and how the installer updates plugin files. |
| [Adminer](docs/adminer.md) | Covers Adminer installation, randomized and environment-specific URLs, database credentials, Nginx and PHP-FPM routing, rate limits, disabling access, and security limitations. |
| [Staging & Dev](docs/staging-dev.md) | Covers environment setup, guest protection, production-to-staging and development syncs, shared uploads, overwrite risks, production push commands, and recovery boundaries. |
| [Headless](docs/headless.md) | Covers using SlickStack as a WordPress backend for a remote frontend, including APIs, authentication, SEO, media, caching, redirects, and previews. |
| [Workflows](docs/workflows.md) | Explains the GitHub Actions used for repository maintenance, SourceForge mirroring, and manual updates of approved WordPress MU plugin ZIP files. |

## Modules

SlickStack installs, configures, or integrates the following core components. Older supported Ubuntu LTS releases can use different distribution packages, while externally maintained tools may update independently.

*Last updated: Jul 20, 2026*

| Module | Version / source | What SlickStack manages |
| :------------- | :----------: | :---------- |
| **[SlickStack](docs/architecture.md)** | `JUL2026C` | Bash control plane under `/var/www`, `ss-config`, installers, aliases, 14 fixed cron wrappers, timestamps, backups, synchronization, permissions, cache purges, service recovery, and full-stack reconciliation. |
| **[Ubuntu LTS](docs/ubuntu.md)** | 18.04-24.04 | Package and kernel upgrades, required utilities, managed users and groups, SSH and jailed SFTP, sudoers, sysctl tuning, swap, shell configuration, root cron, and system permissions. |
| **[Nginx](docs/nginx.md)** | 1.24.x | `nginx.conf`, HTTPS redirects, production and test server blocks, WordPress routing, static-file handling, FastCGI cache, security headers, rate and connection limits, Cloudflare includes, and maintenance or error responses. |
| **[OpenSSL](docs/ssl.md)** | 3.0.x | Default self-signed origin certificate, private key, Diffie-Hellman parameters, stable certificate paths, permissions, and Nginx activation. |
| **[Certbot](docs/ssl.md)** | 2.9.x | Optional Let's Encrypt issuance and renewal, HTTP validation for normal sites, Cloudflare DNS validation for wildcard certificates, certificate links, permissions, and Nginx activation. |
| **[Cloudflare](docs/cloudflare.md)** | External service | Origin-side DNS and proxy guidance, visitor-IP restoration, IP range refreshes, Authenticated Origin Pulls, optional origin restrictions, cache boundaries, and wildcard-certificate credentials; SlickStack does not manage the Cloudflare account itself. |
| **[PHP-FPM](docs/php-fpm.md)** | 8.3.x | PHP packages and extensions, version selection, `php.ini`, `php-fpm.conf`, the shared `www` pool, OPcache, RAM-based worker tuning, logging, restarts, and service recovery. |
| **[MySQL](docs/mysql.md)** | 8.0.x | Local or remote mode, package and service setup, `my.cnf`, InnoDB and RAM-based tuning, WordPress users and databases, dumps, imports, optimization, restarts, and local service recovery. |
| **[Memcached](docs/memcached.md)** | 1.6.x | Loopback-only service configuration, memory allocation, the production WordPress `object-cache.php` drop-in, cache purges, restarts, and permissions. |
| **[WordPress](docs/wordpress.md)** | 7.0 | Core installation and refreshes, generated `wp-config.php`, selected constants, MU plugins, WP-Cron execution, permissions, maintenance mode, Multisite handling, and staging or development synchronization. |
| **[WP-CLI](docs/wordpress.md)** | 2.12.x | Installation and updates, SlickStack aliases, WordPress cron execution, database operations, search and replace, Core and extension maintenance, cache operations, and selected command restrictions. |
| **[Adminer](docs/adminer.md)** | 5.4.2 | Installation, randomized production and test URLs, Nginx and PHP-FPM routing, database credential integration, request limits, permissions, and optional removal. |
| **[Rclone](docs/backups.md)** | Varies | Installation, protected configuration, scheduled off-server backups, provider transfers, logs, timestamps, and credential permissions. |
| **[Rsync](docs/backups.md)** | Varies | Server-to-server backup transfers, staging and development file synchronization, production push workflows, migration support, exclusions, ownership, and permissions. |
| **[Iptables](docs/iptables.md)** | 1.8.x | IPv4 and IPv6 policies, public HTTP and HTTPS access, rate-limited SSH, loopback and established traffic, persistent rule files, rebuilds, and permission controls. |
| **[Fail2ban](docs/fail2ban.md)** | 1.0.x | Current SSH and Nginx jails, custom filters, thresholds, ban state, logging, and service control pending its planned removal in favor of a simpler Iptables-only baseline. |

## Philosophy

Outside of the so-called Application Layer, much of the way computers and servers now work has been pushed away from in-house teams and specialists and onto "the cloud." Terms like DevOps have become standard across companies and developers, while modern web development keeps drifting toward automation, APIs, dashboards, managed platforms, and whatever the latest cloud vendor wants to sell.

While this shift is exciting, there is now a massive and growing disconnect between these emerging technologies and the humans expected to implement or benefit from them. Typical small business owners, independent agencies, and freelancers face a steep learning curve if they want to maintain a competitive web development edge, let alone keep up with basic standards in website performance and security.

Silicon Valley gurus and corporations pump out new SaaS services, cloud platforms, and complex automation tools on a daily basis, while the typical small business website is still trying to figure out how to make contact forms, caching, SSL, email, backups, and basic security work correctly. Legacy shared hosting companies also have little motivation to make things clearer, since confusion keeps customers dependent on dashboards, support tickets, and upsells.

Thus, before the likes of Google, Amazon, Shopify, Wix, and other Wall Street-backed website builders take over more of the open web and pull users deeper into their private ecosystems, SlickStack hopes to bridge the knowledge gap between emerging technology and old-school web development to empower SMBs to achieve top notch website performance and security by offering a "controlled" LEMP-stack environment with limited options that is perfectly suited to the world's most popular open-source CMS: WordPress.