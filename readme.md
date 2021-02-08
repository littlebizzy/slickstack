# SlickStack (Beta)

⮕ ⮕ ⮕ [**Gab group (private)**](https://gab.com/groups/7116)
⮕ ⮕ ⮕ [**Discord server**](https://discord.gg/nGskJdg)
⮕ ⮕ ⮕ [**Skype group chat**](https://join.skype.com/NdpqKrN2BHdN)

| PageSpeed | GTMetrix | Pingdom | SecHeaders | SSL Labs | WebPageTest | ImmuniWeb |
| :--------------: | :------: | :-----: | :--------------: | :-------------: | :-------------: | :-------------: |
| [**A**](https://developers.google.com/speed/pagespeed/insights/?url=https%3A%2F%2Fslickstack.io%2F) | [**A**](https://gtmetrix.com/reports/slickstack.io/zpLMZ1eb) | [**A**](https://tools.pingdom.com/#5aeba9dea8000000) | [**A**](https://securityheaders.com/?q=https%3A%2F%2Fslickstack.io%2F&followRedirects=on) | [**A**](https://www.ssllabs.com/ssltest/analyze.html?d=slickstack.io&latest) | [**A**](https://www.webpagetest.org/result/190920_68_a4a541db9847ce601ef264b41df9d0f3/) | [**A**](https://www.immuniweb.com/websec/?id=pmqYyXDB) |

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

## Core Modules [[read more](https://slickstack.io/modules)]

*Last updated: Feb 9, 2021*

*Default Ports: 80 (HTTP), 443 (HTTPS), 6969 (SSH)*

| LEMP Module | Mirrors | Version | What does SlickStack [ss] customize? |
| :------------- | :----------: | :----------: | :----------: |
| **Ubuntu LTS** | [mirrors](http://mirrors.slickstack.io/ubuntu/) | 20.04 | `crontab` + `gai.conf` + `sshd_config` + `sudoers` + `sysctl.conf` |
| **Nginx** | [mirrors](http://mirrors.slickstack.io/nginx/) | 1.18.x | `nginx.conf` + `default` (server block) |
| **FCGI Cache** | [mirrors](http://mirrors.slickstack.io/fastcgi-cache/) | 1.18.x | `fastcgi-cache.conf` (moved to `nginx.conf`) |
| **OpenSSL** | [mirrors](http://mirrors.slickstack.io/openssl/) | 1.1.1x | `nginx.crt` + `nginx.key` |
| **Lets Encrypt** | [mirrors](http://mirrors.slickstack.io/letsencrypt/) | 0.40.x | `cert.perm` + `privkey.pem` + `chain.pem` + `fullchain.pem` |
| **MySQL** | [mirrors](http://mirrors.slickstack.io/mysql/) | 8.0.x | `my.cnf` |
| **PHP-FPM** | [mirrors](http://mirrors.slickstack.io/php-fpm/) | 7.4.x | `php.ini` + `php-fpm.conf` + `www.conf` |
| **Zend / OPcache** | [mirrors](http://mirrors.slickstack.io/opcache/) | 3.4.x / 7.4.x | (same as PHP-FPM) |
| **Redis (Obj Cache)** | [mirrors](http://mirrors.slickstack.io/redis/) | 5.0.x | `redis.conf` + `object-cache.php` |
| **WordPress** | [mirrors](http://mirrors.slickstack.io/wordpress/) | 5.6.1 | some WP Core junk files are removed by `ss-clean` |
| **MU Plugins** | [mirrors](http://mirrors.slickstack.io/mu-plugins/) | (n/a) | optional `mu-plugins` by LittleBizzy |
| **WP-CLI** | [mirrors](http://mirrors.slickstack.io/wp-cli/) | 2.4.0 | default config |
| **Adminer** | [mirrors](http://mirrors.slickstack.io/adminer/) | 4.7.9 | default config |
| **Git** | [mirrors](http://mirrors.slickstack.io/git/) | 2.25.x | default config |
| **UFW Firewall** | [mirrors](http://mirrors.slickstack.io/ufw-firewall/) | 0.36 | `ufw` + `ufw.conf` + `user-rules` |
| **ClamAV** | [mirrors](http://mirrors.slickstack.io/clamav/) | 0.102.x | `freshclam.conf` |

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
