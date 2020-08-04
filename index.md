---
title: SlickStack (Public Mirrors)
---

# Public Mirrors (SlickStack)

----

### Core Modules

*Main modules of SlickStack's optimized WordPress LEMP stack, including Ubuntu LTS, Nginx, MySQL, PHP-FPM, Redis, and several LittleBizzy MU plugins.*

* [adminer](/adminer/)
* [clamav](/clamav/)
* [cloudflare](/cloudflare/)
* [fastcgi-cache](/fastcgi-cache/)
* [git](/git/)
* [letsencrypt](/letsencrypt/)
* [mu-plugins](/mu-plugins/)
* [mysql](/mysql/)
* [nginx](/nginx/)
* [opcache](/opcache/)
* [openssl](/openssl/)
* [php-fpm](/php-fpm/)
* [postfix](/postfix/)
* [redis](/redis/)
* [starter-themes](/starter-themes/)
* [ubuntu](/ubuntu/)
* [ufw-firewall](/ufw-firewall/)
* [wordpress](/wordpress/)
* [wp-cli](/wp-cli/)

### Core Cron Jobs

*These cron jobs are the most critical part of SlickStack's setup, as they allow your LEMP stack to receive updates to modules and scripts via GitHub.*

* [00-crontab.txt](00-crontab.txt)
* [01-cron-often.txt](01-cron-often.txt)
* [02-cron-regular.txt](02-cron-regular.txt)
* [03-cron-quarter-hourly](03-cron-quarter-hourly)
* [04-cron-half-hourly](04-cron-half-hourly)
* [05-cron-hourly](05-cron-hourly)
* [06-cron-quarter-daily](06-cron-quarter-daily)
* [07-cron-half-daily](07-cron-half-daily)
* [08-cron-daily](08-cron-daily)
* [09-cron-half-weekly](09-cron-half-weekly)
* [10-cron-weekly](10-cron-weekly)
* [11-cron-half-monthly](11-cron-half-monthly)
* [12-cron-monthly](12-cron-monthly)
* [13-cron-sometimes](13-cron-sometimes)

### Core Bash Scripts

*Bash scripts that perform SlickStack's main functions as an active LEMP environment, which are called via cron jobs, and can also be run manually.*

* [ss-check.txt](ss-check.txt)
* [ss-clean.txt](ss-clean.txt)
* [ss-config-sample.txt](ss-config-sample.txt)
* [ss-dump.txt](ss-dump.txt)
* [ss-encrypt.txt](ss-encrypt.txt)
* [ss-import.txt](ss-import.txt)
* [ss-install.txt](ss-install.txt)
* [ss-muplugs.txt](ss-muplugs.txt)
* [ss-perms.txt](ss-perms.txt)
* [ss-purge.txt](ss-purge.txt)
* [ss-restart.txt](ss-restart.txt)
* [ss-scan.txt](ss-scan.txt)
* [ss-update.txt](ss-update.txt)
* [ss-worker.txt](ss-worker.txt)

### Core Meta Files

*Various meta files that are not part of SlickStack's functionality but provide key information about licensing, general "readme" summary files, etc.*

* [changelog.md](changelog.md)
* [license.md](license.md)
* [readme.md](readme.md)

----

*Last updated: Aug 4, 2020*
