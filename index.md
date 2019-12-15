---
title: SlickStack (Public Mirrors)
---

# Public Mirrors (SlickStack)

----

### Core Modules

*Main modules of SlickStack's optimized WordPress LEMP stack, including Ubuntu LTS, Nginx, MySQL, PHP-FPM, Redis, and several LittleBizzy MU plugins.*

* [ubuntu](/ubuntu/)
* [nginx](/nginx/)
* [mysql](/mysql/)
* [php-fpm](/php-fpm/)
* [openssl](/openssl/)
* [fastcgi-cache](/fastcgi-cache/)
* [letsencrypt (certbot)](/letsencrypt/)
* [redis](/redis/)
* [opcache](/opcache/)
* [wordpress](/wordpress/)
* [mu-plugins](/mu-plugins/)
* [starter-themes](/starter-themes/)
* [wp-cli](/wp-cli/)
* [ufw-firewall](/ufw-firewall/)
* [clamav](/clamav/)
* [git](/git/)

### Core Cron Jobs

*These cron jobs are the most critical part of SlickStack's setup, as they allow your LEMP stack to receive updates to modules and scripts via GitHub.*

* [0-crontab.txt](0-crontab.txt)
* [1-cron-often.txt](1-cron-often.txt)
* [2-cron-regular.txt](2-cron-regular.txt)
* [3-cron-hourly.txt](3-cron-hourly.txt)
* [4-cron-quarter-daily.txt](4-cron-quarter-daily.txt)
* [5-cron-half-daily.txt](5-cron-half-daily.txt)
* [6-cron-daily.txt](6-cron-daily.txt)
* [7-cron-weekly.txt](7-cron-weekly.txt)
* [8-cron-monthly.txt](8-cron-monthly.txt)
* [9-cron-sometimes.txt](9-cron-sometimes.txt)

### Core Bash Scripts

*Bash scripts that perform SlickStack's main functions as an active LEMP environment, which are called via cron jobs, and can also be run manually.*

* [ss-check.txt](ss-check.txt)
* [ss-clean.txt](ss-clean.txt)
* [ss-config-sample.txt](ss-config-sample.txt)
* [ss-dump.txt](ss-dump.txt)
* [ss-encrypt.txt](ss-encrypt.txt)
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

* [readme.md](readme.md)
* [license.md](license.md)
* [changelog.md](changelog.md)

----

*Last updated: Dec 11, 2019*
