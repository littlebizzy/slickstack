<img src="https://repository-images.githubusercontent.com/104382627/49a17307-d8e9-49f3-a320-b3bd9c0f5e70" />

# SlickStack

SlickStack is a free LEMP stack automation script written in Bash designed to enhance and simplify WordPress provisioning, performance, and security.

<a href="https://www.capterra.com/p/211436/SlickStack">Capterra</a> • <a href="https://www.g2.com/products/slickstack/reviews">G2 Crowd</a> • <a href="https://www.producthunt.com/posts/slickstack">Product Hunt</a> • <a href="https://sourceforge.net/software/product/SlickStack/">SourceForge</a>

| Document | Description |
| :------------- | :---------- |
| [Backups](docs/backups.md) | Covers local database and file dumps, backup schedules, production restores, Rclone and Rsync transfers, retention limits, credentials, and recovery verification. |
| [Caching](docs/caching.md) | Explains Nginx FastCGI cache, PHP OPcache, Memcached, WordPress transients, browser and Cloudflare cache boundaries, purge commands, schedules, bypass rules, and troubleshooting. |
| [Cron Jobs](docs/cron.md) | Documents the 14 fixed wrapper intervals, task selection through `ss-config`, WP-Cron, overlap protection, custom cron files, self-healing, logging, and troubleshooting. |
| [Adminer](docs/adminer.md) | Covers Adminer installation, randomized and environment-specific URLs, database credentials, Nginx and PHP-FPM routing, rate limits, disabling access, and security limitations. |
| [Cloudflare](docs/cloudflare.md) | Covers DNS and proxy setup, SSL modes, real visitor IPs, Authenticated Origin Pulls, Certbot DNS credentials, IP range refreshes, origin restrictions, current limitations, and troubleshooting. |
| [Fail2ban](docs/fail2ban.md) | Documents the current SSH and Nginx jails, thresholds, bans, troubleshooting, and planned removal in favor of a simpler Iptables-only approach. |
| [Workflows](docs/workflows.md) | Explains the GitHub Actions used for repository maintenance, SourceForge mirroring, and manual updates of approved WordPress MU plugin ZIP files. |
| [Headless](docs/headless.md) | Covers using SlickStack as a WordPress backend for a remote frontend, including APIs, authentication, SEO, media, caching, redirects, and previews. |
| [Installation](docs/installation.md) | Covers server requirements, first-run setup, preflight checks, the full installer sequence, production reinstallation effects, backups, verification, recovery, and managed-file boundaries. |
| [Iptables](docs/iptables.md) | Documents the default firewall policies, allowed inbound traffic, installed rule files, persistence behavior, and the limits of custom firewall changes. |
| [Logging](docs/logging.md) | Covers SlickStack and systemd log locations, service-specific behavior, WordPress debug logs, permissions, destructive clearing, retention limits, redaction, and troubleshooting. |
| [Memcached](docs/memcached.md) | Explains the loopback-only Memcached service, WordPress object-cache integration, cache layers, purging schedules, restarts, and troubleshooting. |
| [Monitoring](docs/monitoring.md) | Covers interactive resource checks, stack overview limits, service status, timestamps, and the automatic PHP-FPM, Nginx, and local MySQL recovery listeners in `02-cron-often`. |
| [MySQL](docs/mysql.md) | Covers local and remote database modes, managed MySQL configuration, InnoDB tuning, users and databases, dumps, imports, optimization, and service recovery. |
| [Nginx](docs/nginx.md) | Describes SlickStack-managed Nginx configuration, approved optional include files, supported customization boundaries, and safe reload behavior. |
| [PHP-FPM](docs/php-fpm.md) | Documents PHP version selection, package and configuration installation, Nginx routing, RAM-based process tuning, extensions, OPcache, restarts, and service recovery. |
| [Permissions](docs/permissions.md) | Documents ownership and file modes across SlickStack, WordPress permission zones, SFTP and `www-data` responsibilities, scheduled resets, protected credentials, and troubleshooting. |
| [SSL (Certs)](docs/ssl.md) | Covers self-signed OpenSSL certificates, Certbot issuance and renewal, Cloudflare wildcard validation, third-party certificates, permissions, and Nginx activation. |
| [SS-Config](docs/ss-config.md) | Documents SlickStack's central configuration file, secure editing, build compatibility, schema migration, automatic tuning, pilot files, supported boundaries, rollback, and troubleshooting. |
| [Staging & Dev](docs/staging-dev.md) | Covers environment setup, guest protection, production-to-staging and development syncs, shared uploads, overwrite risks, production push commands, and recovery boundaries. |
| [Ubuntu](docs/ubuntu.md) | Documents supported Ubuntu LTS releases, managed users, SSH and SFTP access, sudoers, utilities, sysctl tuning, swap, cron, package updates, reinstallation, and reboots. |
| [Updates](docs/updates.md) | Explains script refreshes, `ss-config` migration, Ubuntu package and kernel upgrades, compound update aliases, scheduling, reboots, recovery, and managed-file boundaries. |
| [WordPress](docs/wordpress.md) | Covers managed WordPress Core installation, generated configuration, WP-Cron, WP-CLI, permissions, updates, Multisite, and staging and development synchronization. |
| [MU Plugins](docs/mu-plugins.md) | Explains which WordPress MU plugins SlickStack installs, how vendored ZIP mirrors are managed, and how the installer updates plugin files. |

## Thank you to our sponsors!

[**Become a sponsor**](https://github.com/sponsors/jessuppi) and receive access to our **#perma-lounge** channel on Discord. Your donations and public displays of support for SlickStack are what keep this project going. Thank you very much!

&nbsp;

<a href="https://emplibot.com"><img width="150" style="padding: 20px" src="https://slickstack.io/wp-content/uploads/2024/09/emplibot-square.png" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://www.osoelectric.com"><img width="150" src="https://slickstack.io/wp-content/uploads/2023/04/oso-logo-color-480x467.webp" /></a>

&nbsp;

Our sponsors: [alexbohariuc](https://github.com/alexbohariuc), [backamblock](https://github.com/backamblock), [gingibash](https://github.com/gingibash), [hamzah](https://github.com/gingibash), [liwernyap](https://github.com/liwernyap), [OSO Electric Equipment](https://github.com/Oso-Electric-Equipment), [romfeo](https://github.com/romfeo), [strxno](https://github.com/strxno), [trevplaig](https://github.com/trevplaig), [vivdev](https://github.com/vivdev), [vladbejenaru](https://github.com/vladbejenaru), [volneanschi](https://github.com/volneanschi)

## Installation

Because it’s written purely in Bash (Unix shell), SlickStack has no third-party provisioning dependencies and works on Ubuntu LTS machines. Unlike heavier provisioning tools like EasyEngine or Ansible, it does not require external runtimes such as Python or Docker, meaning a lighter and simpler approach to WordPress servers.

The following installation steps assume that you've already spun up a [KVM cloud server](https://slickstack.io/hosting) on Ubuntu LTS, with at least 1GB+ RAM, and that you are logged in via SSH as `root`:

```
cd /tmp/ && wget -O ss slick.fyi/ss && bash ss
```

After installation, you can manage your SlickStack server by running the bundled scripts located within the `/var/www/` directory using `sudo bash`, as needed. In most cases, however, there shouldn't be much hands-on management because SlickStack runs scheduled cron jobs that connect to this GitHub repo.

Reinstallation through `sudo bash /var/www/ss-install` is designed to reconcile the managed stack without deleting normal WordPress content, but it is a broad maintenance operation that upgrades packages, rewrites managed configuration, may synchronize staging, truncates SlickStack logs, resets permissions, purges caches, and restarts services. Review the [Installation](docs/installation.md) guide before rerunning it on production.

**Note:** SlickStack uses a self-signed OpenSSL certificate by default, so Cloudflare should be active on your domain before SSL (HTTPS) will appear fully secure in browsers. If you wish to use Let's Encrypt instead, be sure to change your settings in `ss-config` before running the installation.

## Modules

*Last updated: Jun 26, 2026*

| Module | Version | What does SlickStack optimize? |
| :------------- | :----------: | :----------: |
| **Ubuntu LTS** | 24.04 | `crontab` + `gai.conf` + `sshd_config` + `sudoers` + `sysctl.conf` |
| **Nginx** | 1.24.x | `nginx.conf` + `cloudflare.conf` + server blocks |
| **OpenSSL** | 3.0.x | `slickstack.crt` + `slickstack.key` + `dhparam.pem` |
| **Certbot** | 2.9.x | `cert.perm` + `chain.pem` + `fullchain.pem` + `privkey.pem` |
| **MySQL** | 8.0.x | `my.cnf` |
| **PHP-FPM** | 8.3.x | `php.ini` + `php-fpm.conf` + `www.conf` |
| **Memcached** | 1.6.x | `memcached.conf` + `object-cache.php` |
| **WordPress** | 7.0 | some WP Core junk files removed by `ss-clean-files` |
| **WP-CLI** | 2.12.x | some `wp` commands disabled |
| **Adminer** | 5.4.2 | default config |
| **Iptables** | 1.8.x | `rules.v4` + `rules.v6` |
| **Fail2ban** | 1.0.x | `jail.local` + custom filters |

## Requirements

*NOTE: SlickStack is designed for one primary domain per server. It will never support installing multiple TLDs (multi-tenancy) on a single server, and it will not include a built-in UI. This keeps the stack focused on speed, stability, security, and predictable integration with third-party management tools.*

SlickStack works best on [KVM cloud servers](https://slickstack.io/hosting) such as DigitalOcean, Vultr, and Linode. Basic WordPress sites can run on 1GB+ RAM, while 2GB+ RAM is recommended for WooCommerce, Multisite, bbPress, BuddyPress, membership sites, forums, or other dynamic/high-traffic workloads. As your site grows, you can upgrade the server and run `sudo bash /var/www/ss-install` again so SlickStack can re-apply optimized settings for the available resources.

By default, MySQL connects locally over TCP to `127.0.0.1:3306` using databases named `production`, `staging`, and `development`, depending on whether staging/dev sites are enabled. Remote databases can also be used, but server clustering and load balancing are not a SlickStack goal; the stack is focused on simple, high-performance WordPress servers for the majority of sites that do not need complex enterprise architecture.

SlickStack is HTTPS-only and enables HSTS by default, so plain HTTP sites are not supported. Because SlickStack uses self-signed OpenSSL certificates by default, Cloudflare should be active in front of your server before HTTPS will appear fully secure in browsers. After the first `ss-install` has completed, you can switch to Certbot / Let's Encrypt if preferred.

## Philosophy

Outside of the so-called Application Layer, much of the way computers and servers now work has been pushed away from in-house teams and specialists and onto "the cloud." Terms like DevOps have become standard across companies and developers, while modern web development keeps drifting toward automation, APIs, dashboards, managed platforms, and whatever the latest cloud vendor wants to sell.

While this shift is exciting, there is now a massive and growing disconnect between these emerging technologies and the humans expected to implement or benefit from them. Typical small business owners, independent agencies, and freelancers face a steep learning curve if they want to maintain a competitive web development edge, let alone keep up with basic standards in website performance and security.

Silicon Valley gurus and corporations pump out new SaaS services, cloud platforms, and complex automation tools on a daily basis, while the typical small business website is still trying to figure out how to make contact forms, caching, SSL, email, backups, and basic security work correctly. Legacy shared hosting companies also have little motivation to make things clearer, since confusion keeps customers dependent on dashboards, support tickets, and upsells.

Thus, before the likes of Google, Amazon, Shopify, Wix, and other Wall Street-backed website builders take over more of the open web and pull users deeper into their private ecosystems, SlickStack hopes to bridge the knowledge gap between emerging technology and old-school web development to empower SMBs to achieve top notch website performance and security by offering a "controlled" LEMP-stack environment with limited options that is perfectly suited to the world's most popular open-source CMS: WordPress.
