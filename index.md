---
title: SlickStack (Public Mirrors)
---

# Public Mirrors (SlickStack)

----

### Core Cron Jobs

*These cron jobs are the most critical part of SlickStack's setup, as they allow your LEMP stack to receive updates to modules and scripts via GitHub.*

* [00-crontab](00-crontab.txt)
* [01-cron-often](01-cron-often.txt)
* [02-cron-regular](02-cron-regular.txt)
* [03-cron-quarter-hourly](03-cron-quarter-hourly.txt)
* [04-cron-half-hourly](04-cron-half-hourly.txt)
* [05-cron-hourly](05-cron-hourly.txt)
* [06-cron-quarter-daily](06-cron-quarter-daily.txt)
* [07-cron-half-daily](07-cron-half-daily.txt)
* [08-cron-daily](08-cron-daily.txt)
* [09-cron-half-weekly](09-cron-half-weekly.txt)
* [10-cron-weekly](10-cron-weekly.txt)
* [11-cron-half-monthly](11-cron-half-monthly.txt)
* [12-cron-monthly](12-cron-monthly.txt)
* [13-cron-sometimes](13-cron-sometimes.txt)

### Core Bash Scripts

*Bash scripts that perform SlickStack's main functions as an active LEMP environment, which are called via cron jobs, and can also be run manually.*

* [ss-check](ss-check.txt)
* [ss-clean](ss-clean.txt)
* [ss-dos2unix](ss-dos2unix.txt)
* [ss-dump](ss-dump.txt)
* [ss-encrypt](ss-encrypt.txt)
* [ss-import](ss-import.txt)
* [ss-install](ss-install.txt)
* [ss-monitor](ss-monitor.txt)
* [ss-optimize](ss-optimize.txt)
* [ss-overview](ss-overview.txt)
* [ss-perms](ss-perms.txt)
* [ss-purge](ss-purge.txt)
* [ss-remote](ss-remote.txt)
* [ss-restart](ss-restart.txt)
* [ss-scan](ss-scan.txt)
* [ss-sync](ss-sync.txt)
* [ss-update](ss-update.txt)
* [ss-worker](ss-worker.txt)

#### ss-install subscripts (to install modules)

* [ss-install-adminer](ss-install-adminer.txt)
* [ss-install-clamav](ss-install-clamav.txt)
* [ss-install-craft-config](ss-install-craft-config.txt)
* [ss-install-craft-core](ss-install-craft-core.txt)
* [ss-install-magento-config](ss-install-magento-config.txt)
* [ss-install-magento-core](ss-install-magento-core.txt)
* [ss-install-mediawiki-config](ss-install-mediawiki-config.txt)
* [ss-install-mediawiki-core](ss-install-mediawiki-core.txt)
* [ss-install-misc](ss-install-misc.txt)
* [ss-install-moodle-config](ss-install-moodle-config.txt)
* [ss-install-moodle-core](ss-install-moodle-core.txt)
* [ss-install-mysql](ss-install-mysql.txt)
* [ss-install-nginx](ss-install-nginx.txt)
* [ss-install-opencart-config](ss-install-opencart-config.txt)
* [ss-install-opencart-core](ss-install-opencart-core.txt)
* [ss-install-php](ss-install-php.txt)
* [ss-install-postfix](ss-install-postfix.txt)
* [ss-install-prestashop-config](ss-install-prestashop-config.txt)
* [ss-install-prestashop-core](ss-install-prestashop-core.txt)
* [ss-install-redis](ss-install-redis.txt)
* [ss-install-ubuntu-aliases](ss-install-ubuntu-aliases.txt)
* [ss-install-ubuntu-crontab](ss-install-ubuntu-crontab.txt)
* [ss-install-ubuntu-kernel](ss-install-ubuntu-kernel.txt)
* [ss-install-ubuntu-ssh](ss-install-ubuntu-ssh.txt)
* [ss-install-ubuntu-users](ss-install-ubuntu-users.txt)
* [ss-install-ufw](ss-install-ufw.txt)
* [ss-install-wordpress-cli](ss-install-wordpress-cli.txt)
* [ss-install-wordpress-config](ss-install-wordpress-config.txt)
* [ss-install-wordpress-core](ss-install-wordpress-core.txt)
* [ss-install-wordpress-mu-plugins](ss-install-wordpress-mu-plugins.txt)

#### ss-config-sample (used by ss-install to create ss-config)

* [ss-config-sample](ss-config-sample.txt)

### Core Modules

*Main modules of SlickStack's optimized WordPress LEMP stack, including Ubuntu LTS, Nginx, MySQL, PHP-FPM, Redis, and optional LittleBizzy MU plugins.*

* [adminer](/adminer/)
* [clamav](/clamav/)
* [cloudflare](/cloudflare/)
* [craft-cms](/craft-cms/)
* [git](/git/)
* [letsencrypt](/letsencrypt/)
* magento
* mediawiki
* [moodle](/moodle/)
* [mysql](/mysql/)
* [nginx](/nginx/)
* [nginx-fastcgi-cache](/fastcgi-cache/)
* [opencart](/opencart/)
* [openssl](/openssl/)
* [php-fpm](/php-fpm/)
* [php-fpm opcache](/opcache/)
* [postfix](/postfix/)
* [prestashop](/prestashop/)
* [redis](/redis/)
* [ubuntu](/ubuntu/)
* [ufw-firewall](/ufw-firewall/)
* [wordpress-cli](/wp-cli/)
* [wordpress-core](/wordpress/)
* [wordpress-mu-plugins](/mu-plugins/)
* [wordpress-starter-themes](/starter-themes/)

### Core Meta Files

*Various meta files that are not part of SlickStack's functionality but provide key information about licensing, general "readme" summary files, etc.*

* [changelog](changelog.md)
* [license](license.md)
* [readme](readme.md)

### References

* https://blog.coinbase.com/container-technologies-at-coinbase-d4ae118dcb6c
* https://www.cyberciti.biz/tips/finding-bash-perl-python-portably-using-env.html
* https://unix.stackexchange.com/questions/119655/when-is-it-important-to-write-portable-scripts
* https://unix.stackexchange.com/questions/603286/how-to-make-a-shell-script-as-portable-as-possible-posix-and-non-posix
* https://www.infoq.com/articles/serverless-stalled/
* https://utkusen.com/blog/security-by-obscurity-is-underrated.html

----

*Last updated: Oct 21, 2020*
