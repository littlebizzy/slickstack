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
* [ss-clean-database](/bash/ss-clean-database.txt)
* [ss-clean-files](/bash/ss-clean-files.txt)
* [ss-config-sample](/bash/ss-config-sample.txt)
* [ss-delete-database](/bash/ss-delete-database.txt)
* [ss-delete-files](/bash/ss-delete-files.txt)
* [ss-dump-database](/bash/ss-dump-database.txt)
* [ss-dump-files](/bash/ss-dump-files.txt)
* [ss-empty-logs](/bash/ss-empty-logs.txt)
* [ss-encrypt-certbot](/bash/ss-encrypt-certbot.txt)
* [ss-encrypt-openssl](/bash/ss-encrypt-openssl.txt)
* [ss-functions](/bash/ss-functions.txt)
* [ss-import-database](/bash/ss-import-database.txt)
* [ss-import-files](/bash/ss-import-files.txt)
* [ss-install](/bash/ss-install.txt)
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
* [ss-install-mysql-config](/bash/ss-install-mysql-config.txt)
* [ss-install-mysql-packages](/bash/ss-install-mysql-packages.txt)
* [ss-install-nginx-config](/bash/ss-install-nginx-config.txt)
* [ss-install-nginx-letsencrypt](/bash/ss-install-nginx-letsencrypt.txt)
* [ss-install-nginx-openssl](/bash/ss-install-nginx-openssl.txt)
* [ss-install-nginx-packages](/bash/ss-install-nginx-packages.txt)
* [ss-install-nginx-ssl](/bash/ss-install-nginx-ssl.txt)
* [ss-install-opencart-config](/bash/ss-install-opencart-config.txt)
* [ss-install-opencart-core](/bash/ss-install-opencart-core.txt)
* [ss-install-php-config](/bash/ss-install-php-config.txt)
* [ss-install-php-packages](/bash/ss-install-php-packages.txt)
* [ss-install-prestashop-config](/bash/ss-install-prestashop-config.txt)
* [ss-install-prestashop-core](/bash/ss-install-prestashop-core.txt)
* [ss-install-redis-config](/bash/ss-install-redis-config.txt)
* [ss-install-redis-packages](/bash/ss-install-redis-packages.txt)
* [ss-install-ubuntu-bash](/bash/ss-install-ubuntu-bash.txt)
* [ss-install-ubuntu-crontab](/bash/ss-install-ubuntu-crontab.txt)
* [ss-install-ubuntu-kernel](/bash/ss-install-ubuntu-kernel.txt)
* [ss-install-ubuntu-ssh](/bash/ss-install-ubuntu-ssh.txt)
* [ss-install-ubuntu-swapfile](/bash/ss-install-ubuntu-swapfile.txt)
* [ss-install-ubuntu-users](/bash/ss-install-ubuntu-users.txt)
* [ss-install-ubuntu-utils](/bash/ss-install-ubuntu-utils.txt)
* [ss-install-ufw-config](/bash/ss-install-ufw-config.txt)
* [ss-install-ufw-packages](/bash/ss-install-ufw-packages.txt)
* [ss-install-wordpress-cli](/bash/ss-install-wordpress-cli.txt)
* [ss-install-wordpress-config](/bash/ss-install-wordpress-config.txt)
* [ss-install-wordpress-core](/bash/ss-install-wordpress-core.txt)
* [ss-install-wordpress-mu-plugins](/bash/ss-install-wordpress-mu-plugins.txt)
* [ss-maintenance-disable](/bash/ss-maintenance-disable.txt)
* [ss-maintenance-enable](/bash/ss-maintenance-enable.txt)
* [ss-monitor-resources](/bash/ss-monitor-resources.txt)
* [ss-optimize-database](/bash/ss-optimize-database.txt)
* [ss-optimize-files](/bash/ss-optimize-files.txt)
* [ss-perms](/bash/ss-perms.txt)
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
* [ss-perms-mysql-config](/bash/ss-perms-mysql-config.txt)
* [ss-perms-mysql-packages](/bash/ss-perms-mysql-packages.txt)
* [ss-perms-nginx-config](/bash/ss-perms-nginx-config.txt)
* [ss-perms-nginx-packages](/bash/ss-perms-nginx-packages.txt)
* [ss-perms-nginx-ssl](/bash/ss-perms-nginx-ssl.txt)
* [ss-perms-opencart-config](/bash/ss-perms-opencart-config.txt)
* [ss-perms-opencart-core](/bash/ss-perms-opencart-core.txt)
* [ss-perms-php-config](/bash/ss-perms-php-config.txt)
* [ss-perms-php-packages](/bash/ss-perms-php-packages.txt)
* [ss-perms-prestashop-config](/bash/ss-perms-prestashop-config.txt)
* [ss-perms-prestashop-core](/bash/ss-perms-prestashop-core.txt)
* [ss-perms-redis-config](/bash/ss-perms-redis-config.txt)
* [ss-perms-redis-packages](/bash/ss-perms-redis-packages.txt)
* [ss-perms-ubuntu-bash](/bash/ss-perms-ubuntu-bash.txt)
* [ss-perms-ubuntu-crontab](/bash/ss-perms-ubuntu-crontab.txt)
* [ss-perms-ubuntu-kernel](/bash/ss-perms-ubuntu-kernel.txt)
* [ss-perms-ubuntu-ssh](/bash/ss-perms-ubuntu-ssh.txt)
* [ss-perms-ubuntu-swapfile](/bash/ss-perms-ubuntu-swapfile.txt)
* [ss-perms-ubuntu-users](/bash/ss-perms-ubuntu-users.txt)
* [ss-perms-ubuntu-utils](/bash/ss-perms-ubuntu-utils.txt)
* [ss-perms-ufw-config](/bash/ss-perms-ufw-config.txt)
* [ss-perms-ufw-packages](/bash/ss-perms-ufw-packages.txt)
* [ss-perms-wordpress-cli](/bash/ss-perms-wordpress-cli.txt)
* [ss-perms-wordpress-config](/bash/ss-perms-wordpress-config.txt)
* [ss-perms-wordpress-core](/bash/ss-perms-wordpress-core.txt)
* [ss-perms-wordpress-mu-plugins](/bash/ss-perms-wordpress-mu-plugins.txt)
* [ss-purge-nginx](/bash/ss-purge-nginx.txt)
* [ss-purge-opcache](/bash/ss-purge-opcache.txt)
* [ss-purge-redis](/bash/ss-purge-redis.txt)
* [ss-purge-transients](/bash/ss-purge-transients.txt)
* [ss-reboot-machine](/bash/ss-reboot-machine.txt)
* [ss-remote-backup](/bash/ss-remote-backup.txt)
* [ss-reset-password-sftp](/bash/ss-reset-password-sftp.txt)
* [ss-restart-mysql](/bash/ss-restart-mysql.txt)
* [ss-restart-nginx](/bash/ss-restart-nginx.txt)
* [ss-restart-php](/bash/ss-restart-php.txt)
* [ss-restart-redis](/bash/ss-restart-redis.txt)
* [ss-restart-ufw](/bash/ss-restart-ufw.txt)
* [ss-scan-malware](/bash/ss-scan-malware.txt)
* [ss-stack-overview](/bash/ss-stack-overview.txt)
* [ss-sync-staging](/bash/ss-sync-staging.txt)
* [ss-update-config](/bash/ss-update-config.txt)
* [ss-update-modules](/bash/ss-update-modules.txt)
* [ss-worker](/bash/ss-worker.txt)

### Core Modules

*Main modules of SlickStack's optimized WordPress LEMP stack, including Ubuntu LTS, Nginx, MySQL, PHP-FPM, Redis, and optional LittleBizzy MU plugins.*

* [adminer](/modules/adminer/)
* [clamav](/modules/clamav/)
* [cloudflare](/modules/cloudflare/)
* [craft](/modules/craft/)
* [git](/modules/git/)
* [letsencrypt](/modules/letsencrypt/)
* [magento](/modules/magento/)
* [mediawiki](/modules/mediawiki/)
* [moodle](/modules/moodle/)
* [mysql](/modules/mysql/)
* [nginx](/modules/nginx/)
    * [fastcgi-cache](/modules/fastcgi-cache/)
* [opencart](/modules/opencart/)
* [openssl](/modules/openssl/)
* [php-fpm](/modules/php-fpm/)
    * [opcache](/modules/opcache/)
* [prestashop](/modules/prestashop/)
* [redis](/modules/redis/)
* [ubuntu](/modules/ubuntu/)
    * [ufw-firewall](/modules/ufw-firewall/)
* [wordpress](/modules/wordpress/)
    * [wp-cli](/modules/wordpress/wp-cli/)
    * [mu-plugins](/modules/wordpress/mu-plugins/)
    * [starter-themes](/modules/wordpress/starter-themes/)

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
* https://shkspr.mobi/blog/2021/01/the-unreasonable-effectiveness-of-simple-html/
* https://jvns.ca/blog/2021/01/23/firecracker--start-a-vm-in-less-than-a-second/
* https://unixsheikh.com/articles/sqlite-the-only-database-you-will-ever-need-in-most-cases.html

----

*Last updated: Aug 6, 2021*
