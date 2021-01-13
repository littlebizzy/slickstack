---
title: SlickStack (Public Mirrors)
---

# Public Mirrors (SlickStack)

----

### Core Cron Jobs

*These cron jobs are the most critical part of SlickStack's setup, as they allow your LEMP stack to receive updates to scripts and modules via GitHub.*

* [00-crontab](/crons/00-crontab.txt)
* [01-cron-often](/crons/01-cron-often.txt)
* [02-cron-regular](/crons/02-cron-regular.txt)
* [03-cron-quarter-hourly](/crons/03-cron-quarter-hourly.txt)
* [04-cron-half-hourly](/crons/04-cron-half-hourly.txt)
* [05-cron-hourly](/crons/05-cron-hourly.txt)
* [06-cron-quarter-daily](/crons/06-cron-quarter-daily.txt)
* [07-cron-half-daily](/crons/07-cron-half-daily.txt)
* [08-cron-daily](/crons/08-cron-daily.txt)
* [09-cron-half-weekly](/crons/09-cron-half-weekly.txt)
* [10-cron-weekly](/crons/10-cron-weekly.txt)
* [11-cron-half-monthly](/crons/11-cron-half-monthly.txt)
* [12-cron-monthly](/crons/12-cron-monthly.txt)
* [13-cron-sometimes](/crons/13-cron-sometimes.txt)

### Core Bash Scripts

*Bash scripts that perform SlickStack's main functions as an active LEMP environment, which are called via cron jobs, and can also be run manually.*

* [ss-check](ss-check.txt)
* [ss-clean-files](ss-clean-files.txt)
* [ss-dos2unix](ss-dos2unix.txt)
* [ss-dump](ss-dump.txt)
* [ss-encrypt](ss-encrypt.txt)
* [ss-functions](ss-functions.txt)
* [ss-import](ss-import.txt)
* [ss-install](ss-install.txt)
* [ss-monitor](ss-monitor.txt)
* [ss-optimize-database](ss-optimize-database.txt)
* [ss-overview](ss-overview.txt)
* [ss-perms](ss-perms.txt)
* [ss-purge](ss-purge.txt)
* [ss-reboot](ss-reboot.txt)
* [ss-remote](ss-remote.txt)
* [ss-reset-password-sftp](ss-reset-password-sftp.txt)
* [ss-restart-services](ss-restart-services.txt)
* [ss-scan-malware](ss-scan-malware.txt)
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
* [ss-install-ubuntu-utils](ss-install-ubuntu-utils.txt)
* [ss-install-ufw](ss-install-ufw.txt)
* [ss-install-wordpress-cli](ss-install-wordpress-cli.txt)
* [ss-install-wordpress-config](ss-install-wordpress-config.txt)
* [ss-install-wordpress-core](ss-install-wordpress-core.txt)
* [ss-install-wordpress-mu-plugins](ss-install-wordpress-mu-plugins.txt)

#### ss-perms subscripts (to reset module permissions)

* [ss-perms-adminer](ss-perms-adminer.txt)
* [ss-perms-clamav](ss-perms-clamav.txt)
* [ss-perms-craft-config](ss-perms-craft-config.txt)
* [ss-perms-craft-core](ss-perms-craft-core.txt)
* [ss-perms-magento-config](ss-perms-magento-config.txt)
* [ss-perms-magento-core](ss-perms-magento-core.txt)
* [ss-perms-mediawiki-config](ss-perms-mediawiki-config.txt)
* [ss-perms-mediawiki-core](ss-perms-mediawiki-core.txt)
* [ss-perms-moodle-config](ss-perms-moodle-config.txt)
* [ss-perms-moodle-core](ss-perms-magento-core.txt)
* [ss-perms-mysql](ss-perms-mysql.txt)
* [ss-perms-nginx](ss-perms-nginx.txt)
* [ss-perms-opencart-config](ss-perms-opencart-config.txt)
* [ss-perms-opencart-core](ss-perms-opencart-core.txt)
* [ss-perms-php](ss-perms-php.txt)
* [ss-perms-postfix](ss-perms-postfix.txt)
* [ss-perms-prestashop-config](ss-perms-prestashop-config.txt)
* [ss-perms-prestashop-core](ss-perms-prestashop-core.txt)
* [ss-perms-redis](ss-perms-redis.txt)
* [ss-perms-ubuntu-aliases](ss-perms-ubuntu-aliases.txt)
* [ss-perms-ubuntu-crontab](ss-perms-ubuntu-crontab.txt)
* [ss-perms-ubuntu-kernel](ss-perms-ubuntu-kernel.txt)
* [ss-perms-ubuntu-ssh](ss-perms-ubuntu-ssh.txt)
* [ss-perms-ubuntu-users](ss-perms-ubuntu-users.txt)
* [ss-perms-ubuntu-utils](ss-perms-ubuntu-utils.txt)
* [ss-perms-ufw](ss-perms-ufw.txt)
* [ss-perms-wordpress-cli](ss-perms-wordpress-cli.txt)
* [ss-perms-wordpress-config](ss-perms-wordpress-config.txt)
* [ss-perms-wordpress-core](ss-perms-wordpress-core.txt)
* [ss-perms-wordpress-mu-plugins](ss-perms-wordpress-mu-plugins.txt)

#### ss-purge subscripts (to clear module caches)

* [ss-purge-nginx](ss-purge-nginx.txt)
* [ss-purge-opcache](ss-purge-opcache.txt)
* [ss-purge-redis](ss-purge-redis.txt)
* [ss-purge-transients](ss-purge-transients.txt)

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
* [magento](/magento/)
* [mediawiki](/mediawiki/)
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
* https://opensource.com/article/20/6/bash-source-command
* https://medium.com/@gabriel_wilkes/why-tech-groups-can-get-away-with-using-slack-for-free-but-open-source-projects-and-businesses-of-96c427aaefbb
* https://alexgaynor.net/2020/nov/30/why-software-ends-up-complex/
* https://www.brandonsmith.ninja/blog/write-code-not-too-much-mostly-functions

----

*Last updated: Jan 13, 2021*
