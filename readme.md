# SlickStack (Beta)

SlickStack is a free LEMP stack automation script written in Bash designed to enhance and simplify WordPress provisioning, performance, and security.

* [**Discord server**](https://discord.gg/nGskJdg)
* [**Skype group chat**](https://join.skype.com/NdpqKrN2BHdN)
* [**Matrix/Element room**](https://app.element.io/#/room/#general:matrix.slickstack.io)

| PageSpeed | GTMetrix | Pingdom | SecHeaders | SSL Labs | WebPageTest | ImmuniWeb |
| :--------------: | :------: | :-----: | :--------------: | :-------------: | :-------------: | :-------------: |
| [**A**](https://developers.google.com/speed/pagespeed/insights/?url=https%3A%2F%2Fslickstack.io%2F) | [**A**](https://gtmetrix.com/reports/slickstack.io/zpLMZ1eb) | [**A**](https://tools.pingdom.com/#5aeba9dea8000000) | [**A**](https://securityheaders.com/?q=https%3A%2F%2Fslickstack.io%2F&followRedirects=on) | [**A**](https://www.ssllabs.com/ssltest/analyze.html?d=slickstack.io&latest) | [**A**](https://www.webpagetest.org/result/190920_68_a4a541db9847ce601ef264b41df9d0f3/) | [**A**](https://www.immuniweb.com/websec/?id=pmqYyXDB) |

## Core Modules

*Last updated: Feb 26, 2022*

| LEMP Module | Mirrors | Version | What does SlickStack [ss] customize? |
| :------------- | :----------: | :----------: | :----------: |
| **Ubuntu LTS** | [mirrors](https://mirrors.slickstack.io/modules/ubuntu/) | 20.04 | `crontab` + `gai.conf` + `sshd_config` + `sudoers` + `sysctl.conf` |
| **Nginx** | [mirrors](https://mirrors.slickstack.io/modules/nginx/) | 1.18.x | `nginx.conf` + server blocks |
| **OpenSSL** | [mirrors](https://mirrors.slickstack.io/modules/openssl/) | 1.1.1x | `slickstack.crt` + `slickstack.key` + `dhparam.pem` |
| **Lets Encrypt** | [mirrors](https://mirrors.slickstack.io/modules/letsencrypt/) | 0.40.x | `cert.perm` + `chain.pem` + `fullchain.pem` + `privkey.pem` |
| **MySQL** | [mirrors](https://mirrors.slickstack.io/modules/mysql/) | 8.0.x | `my.cnf` |
| **PHP-FPM** | [mirrors](https://mirrors.slickstack.io/modules/php-fpm/) | 7.4.x | `php.ini` + `php-fpm.conf` + `www.conf` |
| **Redis** | [mirrors](https://mirrors.slickstack.io/modules/redis/) | 5.0.x | `redis.conf` + `object-cache.php` |
| **WordPress** | [mirrors](https://mirrors.slickstack.io/modules/wordpress/) | 5.9.1 | some WP Core junk files are removed by `ss-clean` |
| **WP-CLI** | [mirrors](https://mirrors.slickstack.io/modules/wordpress/wp-cli/) | 2.6.0 | some commands are disabled |
| **Adminer** | [mirrors](https://mirrors.slickstack.io/modules/adminer/) | 4.8.1 | default config |
| **Git** | [mirrors](https://mirrors.slickstack.io/modules/git/) | 2.25.x | default config |
| **UFW Firewall** | [mirrors](https://mirrors.slickstack.io/modules/ufw-firewall/) | 0.36 | `ufw` + `ufw.conf` + `user-rules` |
| **ClamAV** | [mirrors](https://mirrors.slickstack.io/modules/clamav/) | 0.102.x | `freshclam.conf` |

## Changelog

* **NEW!** After playing around with various approaches, we are moving forward with permanently blocking public access to /wp-admin/install.php on the dev/staging domains and embracing the approach that the staging database should only be populated by syncing from production via `ss-sync-staging` and the development database minimum required options  (admin_email, blogname, etc) will be populated by `ss-install-wordpress-core` if not exists. The default username/password for dev sites will be populated based on the SFTP username/password for fresh installations. These approaches should vastly improve security, and avoid the need for additional credentials (variables) management by SlickStack.

* **NEW!** New option in ss-config `NGINX_ACCESS_LOG` for temporarily enabling the access log (not a good idea for long-term stability). Log file names have changed now for Nginx being `nginx-error.log` and `nginx-access.log` respectively.

* **NEW!** Our wp-config.php boilerplates no longer include_once the (deprecated) /wp-content/functions.php file... if you were experiencing any slow loading times in WP Admin on new installations, please run ss-install-wp-config again now.
 
* **NEW!** Rclone integration should be working now thanks to work by @backamblock ... however, we may remove it later (and avoid having integrated backup services) because of the huge array of services and user preferences... we need more community feedback on backups, thank you!

* **NEW!** `DISALLOW_UNFILTERED_HTML` is now set to `false` for all single site installs going forward (to stop stripping HTML from the WordPress post editor)... it will still be set to `true` for Multisite installations for stronger security.

* **NEW!** With community help we have begun the super fun task of adding international language support, so that things like skip cache rules make more sense (for example, English users skip the FastCGI cache for /cart/ and /checkout/ pages but German users call this /warenkorb/ and /kasse/ ... pleae get involved to help us add support for more language in SlickStack while evangelizing "best" URL slugs.

* **NEW!** It looks like some providers such as Vultr have taken our advice and started installing 2GB swapfiles by default on their cloud servers... this is cool but it started creating errors when SlickStack tried to install our own swapfile. We tried adding an if statement to the script, but it was a bit janky so we removed that if statement for now and will make it better later.

UPDATE: we have temporarily removed the swapfile check function and will try to improve this fix soon

* **NEW!** We discovered that enabling object-cache.php on the staging subdomain was causing cross-conflicts with production on some actions, such as activating themes and sometimes login/logout due to Redis conflicts. Rather than complicating our Redis config to support staging, SlickStack will simply no longer install object-cache.php (or related future plugins) on the staging (nor dev) sites anymore.

* **NEW!** A few more of our default MU plugins have been removed due to annoyance/confusion among some users. We continue to transition SlickStack away from being LittleBizzy-hosting-specific and into a more universal/standard environment. We have plans to continue our plugin development under new plugin names in a more streamlined (combined) codebase e.g. Speed Demon, Security Guard, and SEO Genius.. however for now (and getting ready for PHP 8.1) we continue to remove more of these default MU plugins from SlickStack for the time being. We might also expand the SlickStack dashboard to include some of these options natively perhaps. Feedback wanted!

* **NEW!** bug fixed that was preventing the dev site (subdomain) HTTP auth user/pass feature from working properly.

* **NEW!** Dev/staging sites are now hardcode "noindexed" in the Nginx server blocks to prevent search engine indexing of those subdomains... a bug was fixed in the staging site block too, so now all staging media file links properly load from the production site instead (this avoids the need to sync/copy media files from production into the staging folders thus saving tons of space and making staging media files load automagically from production folders).

* **NEW!** New variable `SS_OBJECT_CACHE` in ss-config now for an easy central location to disable object caching if desired... if you change the value to "false" then ss-install-wordpress-mu-plugins will skip adding object-cache.php to your production and staging sites (dev site is now ALWAYS disabled object-cache.php)

* **NEW!** `ss-clean-files` will no longer clean/delete/touch anything under /wp-content/ or /wp-content/plugins/ if WORDPRESS_PLUGIN_BLACKLIST is set to `false` in your ss-config file... this was causing a lot of confusion and annoyance to users who didn't realize certain "risky" plugins were being deleted behind the scenes even when they had already disabled the plugin blacklist feature. Should help alleviate a lot of pain for now, although admittedly `ss-clean-files` still needs more code cleanup and general feedback from the community.

* **NEW!** Swapfile size has a new `auto` option in ss-config... we are still optimizing how swapfile is created based on community feedback, please test the latest updates to `ss-install-ubuntu-swapfile` and let us know your comments.

* **NEW!** Several minor errors/warnings are now fixed -- some users have thought their install was broken, this is not the case. SlickStack is BETA (ongoing development) status so minor warnings will sometimes happen as we optimize our scripts. Regardless, many of these are now fixed. Also, if you were having trouble using Certbot in the past few weeks, this is now fixed too. The reason was a bug in our variables (which are now working)... `SITE_DOMAIN_EXCLUDING_WWW` and `SITE_DOMAIN_INCLUDING_WWW` which give us a more more flexibility now (before we had janky "www.SITE_TLD" which was not intuitive).

* **NEW!** We have abandoned the "custom functions.php file" concept previously under /wp-content/functions.php because it created too much confusion. To align with standard WordPress behavior we will no longer install this file. For the time being our wp-config.php template will still call this file if exists to avoid breaking websites but you should stop using this file and use your theme's functions.php file instead.

* **NEW!** SlickStack now force redirects (301) all AMP-related URL patterns to the parent content. AMP was a ridiculous feature created by Google to gain more control over web designers, and it has been a political hot point for years. In 2021 Google finally gave in and began de-emphasizing AMP technology.  Besides the politics, AMP creates an absolutely mess with your site URLs and causes more problems than it solves in our experience. The answer to a slow loading website is server optimization and using lightweight WordPress themse and plugins (not handing over the keys to Google). With this latest patch, say goodbye to thousands of 404 errors and Google GSC warnings in just an instant. Upgrade now!

* **NEW!** WordPress Multisite can now be enabled during ss-install wizard (or via ss-config), however it is experimental. Specifically, we need feedback from the community re: the Nginx server block configuration for Multisite which is different from our default server block boilerplate for single sites. Also, going forward we are planning to ONLY support the "subdomains" version of WP Multisite and probably not the "subdirectories" version... TLD domain mapping is not yet supported and will also require feedback re: Nginx configuration along with community testing and debugging.

* **NEW!** `ss-sync-staging` has now been fixed to work properly with the new subdomain and dedicated database approach we've settled on for staging sites. So, now this script will briefly duplicate the MySQL database dump (that ss-dump-database generates) and upload it to the `staging` database. Then this script will rsync all files from production to staging directory but NOT the media uploads, which staging automagically loads from the production directory using Nginx magic.

* **NEW!** `ss-dump-files` can now export/archive your dev/staging sites if you enable in ss-config (along with production site).
 
* **NEW!** Staging and dev sites (subdomains) can now be password protected and a new user `GUEST_USER` and `GUEST_PASSWORD` is supported who can login and view either site.

* **NEW!** You can now prevent ss-clean-files from deleting or cleaning WordPress files if you prefer not using the new ss-config options `SS_CLEAN_FILES_WORDPRESS_PLUGINS` and `SS_CLEAN_FILES_WORDPRESS_THEMES` and `SS_CLEAN_FILES_WORDPRESS_CONTENT` ...

* **NEW!** `ss-optimize-files` will now strip EXIF data from all public JPEG and JPG files under /var/www/html for security and privacy reasons.

* **NEW!** Staging/dev sites are now working! This is still experimental and needs more review of all settings so please keep that in mind and please offer your detailed feedback to our team in the chat rooms so we can get this feature totally stable... woo hoo!

* **NEW!** Dedicated script `ss-install-mysql-database` being created for better staging/dev/prod clarity. Will update this description soon.

* **NEW!** Staging/Dev preparation continues: `ss-install-clean-files` will now delete non-production Nginx server blocks if staging/dev are set to `false` in your ss-config and will also delete `/staging` and `/dev` directories within those conditions, too. `ss-install-nginx-config` will only install staging/dev blocks going forward if stag/dev are explicity set to `true` in your ss-config. `ss-install-wordpress-core` will only install WordPress to staging/dev directories within those conditions, too. Likewise the staging/dev directories will be auto-created from a variety of methods if set to `true` in your ss-config.

* **NEW!** A major new (experimental) feature is called the SlickStack pilot file. This file can be specified (linked) during new installations using the setup wizard, and/or within your ss-config file. The purpose of the pilot file is to allow teams to easily/rapidly customize settings across all their SlickStack servers using a file that can be retrieved from the public web e.g. via Secret Gist files on GitHub, etc. For example your team could change a PHP setting across 100+ servers within 5-10 minutes or less by using the pilot file.  We are excited about this feature but also cautious about security, so please offer your feedback regarding which settings you think we should support or not support when it comes to the new pilot file concept.

* **NEW!** The script `ss-dump-files` is now functional, and will TAR gzip your entire `html` directory to `/var/www/backups/html/export.tar.gz` whenever run... by default we will not enable this script on any cron schedule because many servers might not have the space for it.

* **NEW!** SSH keys are now finally working! We are using a "centralized" auth keys file to avoid data loss and for easier management and backup purposes. The public key file is located under `/var/www/auth/authorized_keys` and a private/public key pair will be generated automatically for you during `ss-install` and/or `ss-install-ubuntu-ssh` ... the private key will automatically be deleted after a few hours, and the public key is automatically installed for you. At the end of the `ss-install` process (when it runs `ss-stack-overview`) the shell will display your private key hash briefly so you can copy it to your local computer. Please note that you can use a different key pair if you prefer, just be sure to upload your public key file to `/var/www/auth/id_rsa.pub` before `ss-install` runs. We are looking for feedback on our current default settings in `sshd_config` so please let us know if you're an SSH guru!

* **NEW!** Support for third-party SSL certificates is here, by popular demand! We have not tested this, but it should now work. For those interested in using third-party SSL please test the feature and offer us your feedback. You will need to upload your cert bundle to `/var/www/certs/thirdparty.pem` and your private key to `/var/www/certs/keys/thirdparty.key` ... if any conflicts or problems please tell us so we can expand features here as required.

* **NEW!** Early support for whitelabel branding now exists, at least for changing the WP Admin menu name from "SlickStack" to a name you choose, such as your agency's name, using the new ss-config variable `WHITELABEL_BRAND`... this variable will likely be used elsewhere, in the future.

* **NEW!** `ss-install-php-packages` will now verify that your "custom" PHP extensions are the correct version in `ss-config` before installing them, otherwise it will revert to using our default PHP extensions. We've also cleaned up the script to begin our goal of automatic compatibility with any Ubuntu LTS (and PHP) version so that SlickStack will automatically install the default PHP version preferred by each Ubuntu LTS version. At this time, we have no plans to support multiple PHP versions per server or non-standard PHP versions per server, since predictability is a major goal for us.

* **NEW!** More generic filenames now exist by default for database dumps, and we have prepared (not fully supported yet) to support dumping any of the 3 databases e.g. production.sql, staging.sql, and development.sql into the `/var/www/backups/mysql/` directory whenever `ss-dump-database` runs. Keep in mind that by default only the production database will be dumped at scheduled intervals. For large databases, be sure you have disk space!

* **NEW!** We have decided to not proceed with support for Postfix (email module) at this time, probably indefinitely. There are too many security and configuration issues to worry about and maintain. Also, modern cloud platforms have their own email alert systems in place for server resources, so there is less reason to support the server OS to be able to send out email alerts (which was the main reason we were considering Postfix support). Alternatively, we will continue to improve the SlickStack panel in WP Admin which may eventually support having WordPress sending out email alerts based on SLickStack status reports.

* **NEW!** The self-signed OpenSSL certificate has been modernized to use 4096 bit key (previously only 2048) and to support the full array of domain names required by a typical SlickStack server including example.com, www.example.com, staging.example.com, and dev.example.com (previously just a single FQDN). This improvement is crucial to preparing SlickStack to properly and securely handle staging sites, etc. Going forward SlickStack will assume: no subdirectory installs, staging/dev subdomains are ALWAYS sub-FQDN meaning if you install SS on a subdomain like blog.example.com the staging site will ONLY support staging.blog.example.com. It will be your responsibility to upgrade Cloudflare (etc) to ensure proper support for sub-subdomains at the proxy level. The ability for SlickStack to properly redirect e.g. reverse IP address or (non)www queries to canonical will depend on your team configuring the proper DNS records.

* **NEW!** A new variable `SS_DATABASE_REMOTE` in the `ss-config` now indicates whether your origin server is using a remote MySQL database or not. If this variable is set to `true` than `ss-install` will skip installation of MySQL module on the origin server.

* **NEW!** All Nginx server blocks are now installed under `/var/www/sites` instead of the default Debian folder `/etc/nginx/sites-available` and/or `/etc/nginx/sites-enabled` ... this means that you can now easily backup your server blocks by setting your remote SFTP backup service to sync everything under the `/var/www/` parent directory... it also means one more thing you don't need to `cd` around Linux to find anymore... since SlickStack only supports a single domain and everything is automated using our `ss-config` settings, there was no need to keep the Debian approach to Nginx server blocks anymore.

* **NEW!** Due to timeouts from GitHub servers, some `wget` calls have resulted in broken SS files in recent months, often totally breaking websites that are hosted on SS servers. We are beginning a process of implementing fail-safe features in all our cron jobs and scripts so that if files are retrieved from GitHub that do not contain a specific string `SS_EOF` then the file is assumed corrupt and the install task (etc) is skipped. Please offer your feedback for how to best implement this process or alternative ideas for avoiding file corruption due to `wget` timeouts and related issues, thanks!

* **NEW!** Organization of our GitHub repo is now complete with a much easier structure to understand. All bash scripts are under the `bash` folder and all cron jobs are under the `crons` folder... custom cron job templates can be found under the `/crons/custom/` folder. The rest of LEMP stack module config boilerplates can be found in the relevant child folders under `modules` folder... this should make things much simpler to browse going forward.

* **NEW!** If you have noticed problems before with ss core cron jobs or ss core bash scripts being suddenly "null" (empty of content) and thus your entire stack becoming frozen out of receiving updates... fear not, we have discovered this is because GitHub servers sometimes timeout and result in `wget` overwriting core scripts with blank files... to solve this problem permanently, SlickStack `crontab` will now forcefully retrieve ss core cron jobs a few times each day, regardless of settings in `ss-config` and regardless of if all your other files are damaged or missing... a massive improvement to your SS server's longevity.

* **NEW!** If you have struggled with MySQL 8.0 performance on smaller servers, we realized that binary logging (now enabled by default) sometimes creates a massive amount of files and exhausts disk space and otherwise hurts performance. We have now disabled this feature by default, please run `ss-install-mysql-config` or reinstall your SlickStack server with `ss-install` if you have problems with disk space.

* **NEW!** We have changed the way custom cron jobs can be added. Instead of adding to `ss-config` which was rather janky, you can now create a separate bash script under the `/var/www/crons/custom/` folder for every single custom cron job you wish to activate. Much cleaner and stable way to run custom jobs!

* **NEW!** A 2GB swapfile is now installed by default due to issues with MySQL 8.0 we have seen on low memory VPS servers. The new swapfile should help guarantee stability and keep MySQL from crashing due to intensive processes like remote backup and restore (e.g. CodeGuard), etc.

* **NEW!** Another long-requested feature is here, custom WP cron schedules using the server... in our case, you can change `WP_CONFIG_METHOD` to be `server` and then SlickStack will disable wp-cron in the `wp-config.php` file next time you run `ss-install-wordpress-config` and our core cron jobs will then takeover running WP-CRON on the interval you have chosen using `WP_CRON_INTERVAL` inside your `ss-config` file... phew!

* **NEW!** PHP extensions can now be fully customized using the `PHP_EXTENSIONS` option in `ss-config`. Remember to include the `php7.4-fpm` extension as it is NOT included by default in our installation process (nor are any other PHP extensions). Also remember that `php7.4` should NOT be installed on LEMP stack servers, because all you need is the fpm extension which includes are relevant dependencies. If the `PHP_EXTENSIONS` option is missing or commented out, our installer will simply choose the most common required PHP extensions for WordPress that SlickStack recommends using.

* **NEW!** Custom cron jobs are here! A much requested feature is finally here, and simpler than we expected. Simply edit the `SS_CUSTOM_CRON_` options in your `ss-config` file in a nice central location for all editing. These custom commands should be written in normal bash style and NOT crontab syntax, because these variables are simply sourced into our ss core cron job files (bash scripts).

* **NEW!** Default bash prompt has now been prettified to a nice pink color and displays `user@hostname` (FQDN) along with the current working directory. This helps improve consistency across cloud networks, as some providers will alter the prompt to something unrecognizable. We have also introduced `ss` alias commands (not yet fully complete) so that spaced-out-commands now work. Instead of typing `sudo bash /var/www/ss-check` you can now simply type `ss check` when logged in to your server as the sudo or root user. Other longer examples will work soon such as `ss install wordpress` or `ss perms nginx`.

* **NEW!** We continue to split `ss-install` and `ss-perms` and `ss-purge` into subscripts for easier management, and will probably split a few other core bash scripts into subscripts as well to continue fine-tuning script organization and naming. We also continue to add more and more "intervals" to `ss-config` meaning that you can choose when to run any of the core bash scripts on the cron schedules (or not)... there are too many to list here, so just review the latest template for `ss-config` to see all the latest options that can be configured. A new script `ss-reboot` has also been launched to automate and schedule server reboots, if desired.

* **NEW!** A new variable `SS_PLUGIN_BLACKLIST` has been added to `ss-config` options that can be set to "true" or "false" in order to NULL (empty) the blacklist.txt file to effectively turn off the plugin blacklist feature. We still consider that file to be a core file for SlickStack, however, and the Plugin Blacklist is a default MU plugin for SlickStack as well, so we are not going to provide a default way to delete that file for the time being. In any case this new option should make it easier for developers to setup new SS servers without having to worry about modifying the default MU plugin list (or default source URL for blacklist.txt). This change is a bit janky, admittedly, and we welcome all feedback... but for now, it is a simple and easy way to address this common question.

* **NEW!** WordPress Core will now be reinstalled automatically every 2 months as per the default "sometimes" cron job... modify this schedule in `ss-config` by use ing the `SS_INTERVAL_INSTALL_WORDPRESS_CORE` variable (or disable it completely). We will continue to add more interval schedules so that every core SS script can be set to automatically run on any of our 12 core cron job schedules!

* **NEW!** We are experimenting with a new "dashboard" that lets admin-level WOrdPress users easily review their SlickStack related settings... if you want to disable this new feature for now, you can run `ss-update` to get the latest boilerplate then set `SS_DASHBOARD` to "false" in `ss-config` then run `ss-install-wordpress-config` again and the feature should then disappear.

* **NEW!** There is now an interactive Bash wizard when you run `ss-install` on SlickStack servers (new servers) that don't yet have `ss-config` files. This helps admins save time in setting up new LEMP stacks by simply running thru a few options using the wizard and proceeding with the installation process without having to spend any time configuring the `ss-config` file manually as was previously required. This wizard now also has "defaults" for all prompts, meaning that for most options you can simply hit the ENTER key (besides critical fields like `SITE_DOMAIN` or `SITE_TLD` and so forth, which always require unique values to be input).

* **NEW!** We continue to better organize SlickStack filenames including various new sub-scripts that can be run indendently and/or sourced within other Core scripts. For example, `ss-install` has been broken down into various scripts such as `ss-install-php` etc. This means that our Github repo (or third party sysadmins) can easily reinstall the PHP-FPM module without having to go through the entire installation process. For better scaling and organizing of Core cron jobs, we have also moved to a double-digit prefix for all cron jobs starting from `00` for crontab and going up from there e.g. `01-cron-...` etc. This means that cron jobs will remain clearly ordered while viewing them in the shell terminal above the core directories and core Bash scripts installed by SlickStack.

* **NEW!** Staging sites are now included by default on the `/staging/` subdirectory as a separate WordPress installation... by default, we include special MU plugins like Disable Emails and Disable Default Runner (WooCommerce Scheduler) and we also disable WP Cron as well as part of our distinct `wp-config` boilerplate for staging sites... running `ss-muplugs` now manages MU plugins for both production and staging sites... and the new `ss-sync` script is what duplicates all content from production to staging (including the database to hardcoded `staging_` tables)... by default `ss-sync` will run every 12 hours (half-daily cron)... staging sites are now live and active for all SlickStack environments but still considered BETA so please offer feedback in our Facebook/Spectrum groups...

* **NEW!** `ss-optimize` converts all MyISAM tables to InnoDB, removes junk SQL data, and more. We recommend running this script manually, or only occassionally, to avoid confusion and to allow for easily restoring potential data loss (although we are extremely careful/limited about removing SQL data).

* **NEW!** (Experimental) SSH keys are now supported (save public key into `/var/www/meta/.ssh/authorized_keys`)

* **NEW!** We briefly disabled `SQL_MODE` in the `my.cnf` boilerplate due to a conflict with `ss-config` default settings for the recommended MySQL mode. After realizing that MySQL 8.0 no longer supports NO_AUTO_CREATE_USER, we removed that submode from the default SQL_MODE in the `ss-config` boilerplate default settings, and re-enabled SQL_MODE in the `my.cnf` boilerplate.

* **NEW!** SlickStack is now considered Beta (no longer Alpha) and has been moved to supporting Ubuntu 20.04 LTS only, along with PHP 7.4 and MySQL 8.0 which are the new Ubuntu defaults. We have ensured no critical conflicts exist, but some of the documentation and configuration are still being fully optimized for these updated module versions.

* **NEW!** To avoid potential conflicts we've added a `SS_MU_PLUGINS` variable that should be set to either `default` or `custom` in order to activate the custom list of MU plugins that you can edit in your `ss-config` file...

* **NEW!** `ss-update` now performs a "safety" check before running to ensure that it is compatible with the latest version of `ss-config` in order to avoid overwriting the current `ss-config` version with an outdated boilerplate...

* **NEW!** SlickStack now supports fully customizing the MU (Must-Use) plugins that are installed. This can be done easily by defining the download link and desired directory name of each MU plugin in the `ss-config` advanced settings (in case these variables are missing, SlickStack will default to the LittleBizzy plugins). Keep in mind that the Autoloader, Object Cache, Custom Functions, and XXX Notices plugins are required and cannot be disabled at this time.

* **NEW!** SlickStack now has self-healing functions in the root Crontab and `1-cron-often` and `2-cron-regular` to ensure that Core Cron Jobs will be reinstalled fresh in case they are missing or damaged. This self-healing function also ensures that the critical Core Bash Scripts `ss-check` and `ss-worker` also exist and are intact every single day! 

* **NEW!** Running `ss-update` will now automagically update your `ss-config` to latest template... any variables that are missing or undefined will simply be setup using the default (recommended) values for those variables...

* **NEW!** Long-awaited Let's Encrypt (Certbot) support is now live using `SSL_TYPE` option in `ss-config`... those who do not wish to use CloudFlare can now use this approach instead... OpenSSL + CloudFlare is still always our recommended approach however... also, keep in mind that during initial setup (the first time that you request an SSL cert via Certbot) you will still need to have CloudFlare active for `.well-known` domain verification to work properly over HTTPS (otherwise Certbot will complain re: the self-signed OpenSSL cert)...

* **NEW!** All Nginx functionality is now via TCP-only (127.0.0.1) including FastCGI cache for more robust scaling... many Nginx settings can now be customized using `ss-config`... check back for more options soon...

* **NEW!** All MySQL functionality is now via TCP-only (127.0.0.1) including during setup and when purging transient cache via `ss-purge` for better database performance and smoother traffic scaling...

* **NEW!** Our new default object cache (forked from PressJitsu) supports a `OBJECT_CACHE` defined constant set to either `true` (default) or `false` to easily deactivate object caching without needing to delete the `object-cache.php` file...

* **NEW!** SlickStack now supports custom plugin blacklists using `PLUGIN_BLACKLIST_SOURCE` variable...

* **NEW!** SlickStack now does `include_once` within wp-config.php on the Custom Functions (MU plugin) file `/var/www/html/wp-content/functions.php` meaning much more reliable PHP functions...

## Abstract [[read more](https://slickstack.io/about)]

Most of modern computing history can be traced back to one thing: [Unix](https://en.wikipedia.org/wiki/Unix). Indeed, one of the only things about web servers that hasn't changed much in several decades is the Unix shell (Bash) command language. Keeping the same pragmatism and simplicity in mind that inspired LittleBizzy's managed hosting, SlickStack [ss] is coded entirely in Bash.

While there are [clear benefits](https://medium.com/capital-one-developers/bashing-the-bash-replacing-shell-scripts-with-python-d8d201bc0989) to programming languages like Python or Ruby, provisioning a server with WordPress isn't very complicated, and every Linux machine comes with [Bash built into it](https://www.infoworld.com/article/2893519/linux/perl-python-ruby-are-nice-bash-is-where-its-at.html). Plus, let's not forget [what happens](https://discourse.roots.io/t/updated-to-ansible-2-4-deploys-broken-now-what/10588) when typical web agencies rely on advanced dependencies like Ansible... yikes! Onward, then...

## Requirements (Compatibility) [[read more](https://slickstack.io/requirements)]

**NOTE: SlickStack [ss] will never support installing multiple TLD domains on a single server. This is to ensure top speed, stability, and security (i.e. technical SEO). We will also never include any type of UI interface, to allow third party applications to integrate SlickStack [ss] with management tools as they best see fit.**

SlickStack [ss] works best on cloud servers with KVM virtualization that have at least 2GB RAM from [quality network providers](https://slickstack.io/hosting) such as DigitalOcean, Vultr, Hostwinds, and AWS Lightsail. The underlying LEMP stack configuration is meant primarily for high-traffic single-site WordPress installations, although support for [Multisite](https://codex.wordpress.org/Create_A_Network) installations is being planned. SlickStack [ss] supports WordPress, WooCommerce, bbPress, and BuddyPress "out of the box" with optimized settings that scale -- what this means is that you can upgrade your cloud server to a bigger or better instance, and run `ss-install` again, and most settings will (re)optimize themselves.

Currently, SlickStack [ss] is meant for a single origin server with a single `127.0.0.1` database, although remote databases should also work fine. Server "clustering" or "load balancing" has not been tested, and is not the goal here; complex enterprise-style configurations for WordPress are rarely needed (and can be expensive and difficult to manage), thus SlickStack [ss] aims to to provide a simple solution for the 99% of WordPress sites that don't need such complexity.

It should also be noted that SlickStack [ss] is HTTPS-only, and that HSTS is enabled by default, meaning that HTTP sites are not supported. Because OpenSSL generates self-signed certificates, SlickStack [ss] servers require CloudFlare to be active in front of your server in order for SSL certificates to be properly CA-signed and loaded by your browser, at least until the first `ss-install` has been completed (after that, you can switch to Certbot / Let's Encrypt).

## Installation [[read more](https://slickstack.io/install)]

Because it’s written purely in [Bash](https://en.wikipedia.org/wiki/Bash_(Unix_shell)) (Unix shell), SlickStack [ss] has no dependencies and works on any [Ubuntu Linux](https://www.ubuntu.com) machine. Unlike heavier provisioning tools like EasyEngine or Ansible, there are no third party languages required such as Python or Docker, meaning a lighter and simpler approach to launching WordPress servers.

The below installation steps assume that you've already spun up a dedicated Ubuntu Linux VPS server (KVM) with at least 2GB RAM memory and that you are now logged in via SSH:

`cd /tmp/ && wget -O ss slick.fyi && bash ss`

**NOTE:** SlickStack [ss] requires CloudFlare to be activated on your domain before SSL (HTTPS) will be recognized as a fully secure and CA-signed domain, because of its self-signed OpenSSL certificate.

From this point forward, you can manage your SlickStack [ss] server by simply using the `sudo bash` command on any one of the included **ss** scripts located within the `/var/www/` directory, as needed. However, in most cases there shouldn't be any need for much hands-on management as the server will intelligently run various cron jobs which connect to this GitHub repo (or whichever fork of this repo that your team has setup... be sure to modify all `wget` sources).

You can safely re-install SlickStack [ss] anytime via `sudo bash /var/www/ss-install` without causing any conflicts or data loss since the installation process is completely [idempotent](https://en.wikipedia.org/wiki/Idempotence).

## Philosophy

Outside of the so-called [Application Layer](https://en.wikipedia.org/wiki/Application_layer), so much of the way computers and servers now work has been moved away from in-house teams and specialists and onto "the cloud" that terms like [DevOps](https://www.reddit.com/r/devops/comments/3rpzem/devops_vs_sysadmin/cwqmlnd/) have become standard among recruiters, companies, and developers alike. Modern web development trends have begun to revolve entirely around concepts such as automation, APIs, [cloud services](https://www.bcsg.com/wp-content/uploads/2015/03/The-small-business-revolution-trends-in-SMB-cloud-adoption.pdf), and beyond — a phenomenon we might refer to as *Web 3.0*.

While this shift is exciting, there is now a massive and growing disconnect between these emerging technologies and the humans that are expected to implement or benefit from them. Typical small business owners (SMBs), along with independent agencies or freelancers, now face a virtually impossible learning curve if they wish to maintain a competitive "webdev" edge, let alone keep up with basic standards in website performance and security.

While Silicon Valley "gurus" and corporations pump out new SaaS services (or incredibly complex Configuration Management tools like Ansible) on a daily basis, the typical small business website is still trying to figure out how to make their contact forms work correctly. The "legacy" shared web hosting monopolies — think EIG or GoDaddy — also have little motivation to education their audience, as perpetuating confusion seems to be a core pillar of their business model.

Thus, before the likes of Google and Amazon and Shopify and Wix take over the entire web and turn it into Wall Street-backed website builders that feed into their private ecosystems, SlickStack hopes to bridge the knowledge gap between emerging technology and old-school web development to empower SMBs to achieve top notch website performance and security by offering a "controlled" LEMP-stack environment with limited options that is perfectly suited to the world's most popular open-source CMS: WordPress.
