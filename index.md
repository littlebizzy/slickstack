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

* [ss-check](/bash/ss-check.txt)
* [ss-clean-files](/bash/ss-clean-files.txt)
* [ss-delete-database](/bash/ss-delete-database.txt)
* [ss-delete-files](/bash/ss-delete-files.txt)
* [ss-dos2unix](/bash/ss-dos2unix.txt)
* [ss-dump-database](/bash/ss-dump-database.txt)
* [ss-encrypt](/bash/ss-encrypt.txt)
* [ss-functions](/bash/ss-functions.txt)
* [ss-import](/bash/ss-import.txt)
* [ss-install](/bash/ss-install.txt)
* [ss-maintenance-disable](/bash/ss-maintenance-disable.txt)
* [ss-maintenance-enable](/bash/ss-maintenance-enable.txt)
* [ss-monitor](/bash/ss-monitor.txt)
* [ss-optimize-database](/bash/ss-optimize-database.txt)
* [ss-overview](/bash/ss-overview.txt)
* [ss-perms](/bash/ss-perms.txt)
* [ss-purge](/bash/ss-purge.txt)
* [ss-reboot](/bash/ss-reboot.txt)
* [ss-remote](/bash/ss-remote.txt)
* [ss-reset-logs](/bash/ss-reset-logs.txt)
* [ss-reset-password-sftp](/bash/ss-reset-password-sftp.txt)
* [ss-restart-services](/bash/ss-restart-services.txt)
* [ss-scan-malware](/bash/ss-scan-malware.txt)
* [ss-sync-staging](/bash/ss-sync-staging.txt)
* [ss-update](/bash/ss-update.txt)
* [ss-worker](/bash/ss-worker.txt)

#### ss-config-sample (used by ss-install)

* [ss-config-sample](/bash/ss-config-sample.txt)

#### ss-encrypt subscripts

* [ss-encrypt-acme](/bash/ss-encrypt-acme.txt)
* [ss-encrypt-certbot](/bash/ss-encrypt-certbot.txt)
* [ss-encrypt-openssl](/bash/ss-encrypt-openssl.txt)

#### ss-import subscripts

* [ss-import-database](/bash/ss-import-database.txt)
* [ss-import-files](/bash/ss-import-files.txt)

#### ss-install subscripts

* [ss-install-adminer](/bash/ss-install-adminer.txt)
* [ss-install-clamav](/bash/ss-install-clamav.txt)
* [ss-install-craft-config](/bash/ss-install-craft-config.txt)
* [ss-install-craft-core](/bash/ss-install-craft-core.txt)
* [ss-install-magento-config](/bash/ss-install-magento-config.txt)
* [ss-install-magento-core](/bash/ss-install-magento-core.txt)
* [ss-install-mediawiki-config](/bash/ss-install-mediawiki-config.txt)
* [ss-install-mediawiki-core](/bash/ss-install-mediawiki-core.txt)
* [ss-install-moodle-config](/bash/ss-install-moodle-config.txt)
* [ss-install-moodle-core](/bash/ss-install-moodle-core.txt)
* [ss-install-mysql](/bash/ss-install-mysql.txt)
* [ss-install-nginx](/bash/ss-install-nginx.txt)
* [ss-install-nginx-config](/bash/ss-install-nginx-config.txt)
* [ss-install-nginx-core](/bash/ss-install-nginx-core.txt)
* [ss-install-nginx-ssl](/bash/ss-install-nginx-ssl.txt)
* [ss-install-opencart-config](/bash/ss-install-opencart-config.txt)
* [ss-install-opencart-core](/bash/ss-install-opencart-core.txt)
* [ss-install-php](/bash/ss-install-php.txt)
* [ss-install-php-config](/bash/ss-install-php-config.txt)
* [ss-install-php-core](/bash/ss-install-php-core.txt)
* [ss-install-postfix](/bash/ss-install-postfix.txt)
* [ss-install-prestashop-config](/bash/ss-install-prestashop-config.txt)
* [ss-install-prestashop-core](/bash/ss-install-prestashop-core.txt)
* [ss-install-redis](/bash/ss-install-redis.txt)
* [ss-install-ubuntu-bash](/bash/ss-install-ubuntu-bash.txt)
* [ss-install-ubuntu-crontab](/bash/ss-install-ubuntu-crontab.txt)
* [ss-install-ubuntu-kernel](/bash/ss-install-ubuntu-kernel.txt)
* [ss-install-ubuntu-ssh](/bash/ss-install-ubuntu-ssh.txt)
* [ss-install-ubuntu-swap](/bash/ss-install-ubuntu-swap.txt)
* [ss-install-ubuntu-users](/bash/ss-install-ubuntu-users.txt)
* [ss-install-ubuntu-utils](/bash/ss-install-ubuntu-utils.txt)
* [ss-install-ufw](/bash/ss-install-ufw.txt)
* [ss-install-wordpress-cli](/bash/ss-install-wordpress-cli.txt)
* [ss-install-wordpress-config](/bash/ss-install-wordpress-config.txt)
* [ss-install-wordpress-core](/bash/ss-install-wordpress-core.txt)
* [ss-install-wordpress-mu-plugins](/bash/ss-install-wordpress-mu-plugins.txt)

#### ss-perms subscripts

* [ss-perms-adminer](/bash/ss-perms-adminer.txt)
* [ss-perms-clamav](/bash/ss-perms-clamav.txt)
* [ss-perms-craft-config](/bash/ss-perms-craft-config.txt)
* [ss-perms-craft-core](/bash/ss-perms-craft-core.txt)
* [ss-perms-magento-config](/bash/ss-perms-magento-config.txt)
* [ss-perms-magento-core](/bash/ss-perms-magento-core.txt)
* [ss-perms-mediawiki-config](/bash/ss-perms-mediawiki-config.txt)
* [ss-perms-mediawiki-core](/bash/ss-perms-mediawiki-core.txt)
* [ss-perms-moodle-config](/bash/ss-perms-moodle-config.txt)
* [ss-perms-moodle-core](/bash/ss-perms-magento-core.txt)
* [ss-perms-mysql](/bash/ss-perms-mysql.txt)
* [ss-perms-nginx](/bash/ss-perms-nginx.txt)
* [ss-perms-nginx-config](/bash/ss-perms-nginx-config.txt)
* [ss-perms-nginx-core](/bash/ss-perms-nginx-core.txt)
* [ss-perms-nginx-ssl](/bash/ss-perms-nginx-ssl.txt)
* [ss-perms-opencart-config](/bash/ss-perms-opencart-config.txt)
* [ss-perms-opencart-core](/bash/ss-perms-opencart-core.txt)
* [ss-perms-php](/bash/ss-perms-php.txt)
* [ss-perms-php-config](/bash/ss-perms-php-config.txt)
* [ss-perms-php-core](/bash/ss-perms-php-core.txt)
* [ss-perms-postfix](/bash/ss-perms-postfix.txt)
* [ss-perms-prestashop-config](/bash/ss-perms-prestashop-config.txt)
* [ss-perms-prestashop-core](/bash/ss-perms-prestashop-core.txt)
* [ss-perms-redis](/bash/ss-perms-redis.txt)
* [ss-perms-ubuntu-bash](/bash/ss-perms-ubuntu-bash.txt)
* [ss-perms-ubuntu-crontab](/bash/ss-perms-ubuntu-crontab.txt)
* [ss-perms-ubuntu-kernel](/bash/ss-perms-ubuntu-kernel.txt)
* [ss-perms-ubuntu-ssh](/bash/ss-perms-ubuntu-ssh.txt)
* [ss-perms-ubuntu-swap](/bash/ss-perms-ubuntu-swap.txt)
* [ss-perms-ubuntu-users](/bash/ss-perms-ubuntu-users.txt)
* [ss-perms-ubuntu-utils](/bash/ss-perms-ubuntu-utils.txt)
* [ss-perms-ufw](/bash/ss-perms-ufw.txt)
* [ss-perms-ufw-config](/bash/ss-perms-ufw-config.txt)
* [ss-perms-ufw-core](/bash/ss-perms-ufw-core.txt)
* [ss-perms-wordpress-cli](/bash/ss-perms-wordpress-cli.txt)
* [ss-perms-wordpress-config](/bash/ss-perms-wordpress-config.txt)
* [ss-perms-wordpress-core](/bash/ss-perms-wordpress-core.txt)
* [ss-perms-wordpress-mu-plugins](/bash/ss-perms-wordpress-mu-plugins.txt)

#### ss-purge subscripts

* [ss-purge-nginx](/bash/ss-purge-nginx.txt)
* [ss-purge-opcache](/bash/ss-purge-opcache.txt)
* [ss-purge-redis](/bash/ss-purge-redis.txt)
* [ss-purge-transients](/bash/ss-purge-transients.txt)

#### ss-restart subscripts

* [ss-restart-services-mysql](/bash/ss-restart-services-mysql.txt)
* [ss-restart-services-nginx](/bash/ss-restart-services-nginx.txt)
* [ss-restart-services-php](/bash/ss-restart-services-php.txt)
* [ss-restart-services-redis](/bash/ss-restart-services-redis.txt)

#### ss-update subscripts

* [ss-update-config](/bash/ss-update-config.txt)
* [ss-update-packages](/bash/ss-update-packages.txt)

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
* https://searchengineland.com/why-websites-should-be-using-hsts-to-improve-security-and-seo-304380

----

*Last updated: Feb 1, 2021*
