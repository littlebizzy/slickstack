# SlickStack [ss] - "Alpha ss3c"

SlickStack is a free LEMP stack automation script written in Bash designed to enhance and simplify WordPress provisioning, performance, and security.

&#10142; **NEW!** SlickStack is now "HTTPS Only" and includes our popular Force HTTPS mu-plugin...

&#10142; **NEW!** SlickStack now includes a Redis-based `object-cache.php` by default...

&#10142; Browse the SlickStack public mirrors: **http://mirrors.slickstack.io**

## Abstract

Most of modern computing history can be traced back to one thing: [Unix](https://en.wikipedia.org/wiki/Unix). Indeed, one of the only things about web servers that hasn't changed much in several decades is the Unix shell (Bash) command language. Keeping the same pragmatism and simplicity in mind that inspired LittleBizzy's managed hosting, SlickStack [ss] is coded entirely in Bash.

While there are [clear benefits](https://medium.com/capital-one-developers/bashing-the-bash-replacing-shell-scripts-with-python-d8d201bc0989) to programming languages like Python or Ruby, provisioning a server with WordPress isn't very complicated, and every Linux server comes with [shell built into it](https://www.infoworld.com/article/2893519/linux/perl-python-ruby-are-nice-bash-is-where-its-at.html). Plus, let's not forget [what happens](https://discourse.roots.io/t/updated-to-ansible-2-4-deploys-broken-now-what/10588) when typical web agencies rely on advanced dependencies like Ansible... yikes! Onward, then...

## Compatibility

SlickStack [ss] works best on VPS servers with KVM virtualization that have at least 2GB RAM from quality network providers such as DigitalOcean, Linode, Vultr, and Amazon Lightsail. The underlying LEMP stack configuration is meant specifically for single-site WordPress installations, and does not support [Multisite](https://codex.wordpress.org/Create_A_Network) installations. SlickStack [ss] supports WordPress, WooCommerce, bbPress, and BuddyPress "out of the box" with optimized settings that scale.

*Last updated Mar 5, 2019*

| LEMP Module | Current Version | What does SlickStack [ss] customize? |
| :------------- | :----------: | :----------: |
| **Ubuntu** | 18.04 (LTS) | `crontab` + `gai.conf` + `sshd_config` + `sudoers` |
| **Nginx** | 1.15.8 | `nginx.conf` + `server block` + `fastcgi-cache.conf` |
| **MySQL** | 5.7 | default config (will be customized slightly in future) |
| **PHP-FPM** | 7.2 | `php.ini` + `php-fpm.conf` + `www.conf` |
| **WordPress** | 5.0.3 | optional `wplite` pre-optimized configuration (adds custom `mu-plugins`) |
| **WP-CLI** | 1.5.1 | default config |
| **Redis** | 4.0.9 | `redis.conf` |
| **Monit** | 5.25.12 | `monitrc` |
| **Git** | 2.17.1 | default config |
| **UFW** | 0.35 | `ufw` + `ufw.conf` + `user-rules` |

Default Ports: 80 (HTTP), 443 (HTTPS), 6969 (SSH)

## Installation

Because it’s written purely in [Bash](https://en.wikipedia.org/wiki/Bash_(Unix_shell)) (Unix shell), SlickStack [ss] has no dependencies and works on any [Ubuntu Linux](https://www.ubuntu.com) machine. Unlike heavier provisioning tools like EasyEngine or Ansible, there are no third party languages required such as Python or Docker, meaning a lighter and simpler approach to launching WordPress servers.

The below installation steps assume that you've already spun up a dedicated Ubuntu Linux VPS server (KVM) with at least 2GB RAM memory and that you are now logged in via SSH:

1. `sudo mkdir /var/www/ && sudo chown root:root /var/www/ && sudo chmod 755 /var/www/`

2. `sudo nano /var/www/ss-config`  [*configure as desired*](http://mirrors.slickstack.io/ss-config-sample.txt)

3. `cd /var/www/ && sudo wget -O ss slick.fyi && sudo chmod 755 ss && sudo bash ss`

4. `sudo reboot`

From this point forward, you can manage your SlickStack [ss] server by simply using the `sudo bash` command on any one of the included **ss** scripts located within the `/var/www/` directory, as needed. However, in most cases there shouldn't be any need for much hands-on management as the server will intelligently run various cron jobs which connect to this GitHub repo (or whichever fork of this repo that your team has setup... be sure to modify all `wget` sources).

You can also safely re-install SlickStack [ss] using `sudo bash ss-install` without causing any conflicts or data loss; the installation process is completely [idempotent](https://en.wikipedia.org/wiki/Idempotence).

## Structure

After completing the installation steps above, your `/var/www/` directory should look exactly as below. Keep in mind that you should never directly modify the crontab on any SlickStack [ss] server, nor should you modify any of the files appearing below with the exception of `ss-config` (this does not apply to "WordPress" files found under `/var/www/html/`)...

    /var/www/0-crontab
    /var/www/1-cron-often
    /var/www/2-cron-regular
    /var/www/3-cron-hourly
    /var/www/4-cron-quarter-daily
    /var/www/5-cron-half-daily
    /var/www/6-cron-daily
    /var/www/7-cron-weekly
    /var/www/8-cron-monthly
    /var/www/9-cron-sometimes
    /var/www/cache/
    /var/www/html/
    /var/www/logs/
    /var/www/ss-check
    /var/www/ss-clean
    /var/www/ss-config
    /var/www/ss-config-sample
    /var/www/ss-dump
    /var/www/ss-install
    /var/www/ss-muplugs
    /var/www/ss-perms
    /var/www/ss-update
    /var/www/ss-worker
    /var/www/wp.sql
    
...likewise, your `/var/www/html/` (WordPress) directory should look like this:    
    
    /var/www/html/wp-admin/
    /var/www/html/wp-content/
    /var/www/html/wp-includes/
    /var/www/html/wp-...
    
## Must Use Plugins (WPLite Boilerplate)
    
If you choose to deploy a SlickStack [ss] server using our free WPLite boilerplate, the installation process will include several [Must Use plugins](http://mirrors.slickstack.io/mu-plugins/) inside your WordPress structure. If you do not wish for these Must Use plugins to be installed, and want a default "vanilla" WordPress installation, choose "wordpress" instead of "wplite" when setting up your ss-config file during the SlickStack setup process:

* [Autoloader](https://github.com/littlebizzy/autoloader): Enables standard WordPress plugins contained in a folder to be placed in the mu-plugins directory and loaded prior to others (forked from Bedrock).
* [Clear Caches](https://github.com/littlebizzy/clear-caches): The easiest way to clear caches including WordPress cache, PHP Opcache, Nginx cache, Transient cache, Varnish cache, and object cache (e.g. Redis).
* CloudFlare: Easily connect your WordPress website to free optimization features from CloudFlare, including one-click options to purge cache and enable dev mode.
* Custom Functions:
* Dashboard Cleanup:
* Delete Expired Transients:
* Disable Attachment Pages:
* Disable Embeds:
* Disable Emojis:
* Disable Empty Trash:
* Disable Gutenberg:
* Disable Image Compression:
* Disable Post Via Email:
* Disable XML-RPC:
* Error Log Monitor:
* Force Strong Hashing:
* Header Cleanup:
* Index Autoload:
* Limit Heartbeat:
* Minify HTML:
* Plugin Blacklist:
* Remove Query Strings:
* Server Status:
* SFTP Details:
* Virtual Robotstxt:
* XXX Notices:
    
## Defined Constants

The included [Must Use plugins](http://mirrors.slickstack.io/mu-plugins/) that SlickStack [ss] bundles as part of a so-called `wplite` boilerplate support the following defined constants (add these to the Custom Functions file under `/wp-content/functions.php` and customize as desired):

    /* Plugin Meta */
    define('AUTOMATIC_UPDATE_PLUGINS', false); // default = false (only supported by LittleBizzy plugins)
    define('DISABLE_NAG_NOTICES', true); // default = true (only supported by LittleBizzy plugins)

    /* Clear Caches Functions */
    define('CLEAR_CACHES', true); // default = true
    define('CLEAR_CACHES_NGINX', true); // default = true
    define('CLEAR_CACHES_NGINX_PATH', '/var/www/cache'); // default = /var/www/cache
    define('CLEAR_CACHES_OBJECT', true); // default = true
    define('CLEAR_CACHES_OPCACHE', true); // default = true
    
    /* CloudFlare Functions */
    define('CLOUDFLARE', true); // default = true
    define('CLOUDFLARE_API_EMAIL', 'user@example.com'); // *must be unique*
    define('CLOUDFLARE_API_KEY', '123456789'); // *must be unique*
    
    define('CUSTOM_FUNCTIONS', true); // default = true
    define('DASHBOARD_CLEANUP, true); // default = true
    define('DASHBOARD_CLEANUP_ADD_PLUGIN_TABS', true); // default = true
    define('DASHBOARD_CLEANUP_EVENTS_AND_NEWS', true); // default = true
    define('DASHBOARD_CLEANUP_LINK_MANAGER_MENU', true); // default = true
    define('DASHBOARD_CLEANUP_QUICK_DRAFT', true); // default = true
    define('DASHBOARD_CLEANUP_THANKS_FOOTER', true); // default = true
    define('DASHBOARD_CLEANUP_WELCOME_TO_WORDPRESS', true); // default = true
    define('DASHBOARD_CLEANUP_WOOCOMMERCE_CONNECT_STORE', true); // default = true
    define('DASHBOARD_CLEANUP_WOOCOMMERCE_PRODUCTS_BLOCK', true); // default = true
    define('DASHBOARD_CLEANUP_WP_ORG_SHORTCUT_LINKS', true); // default = true
    define('DELETE_EXPIRED_TRANSIENTS', true); // default = true
    define('DELETE_EXPIRED_TRANSIENTS_HOURS', '6'); // default = 6
    define('DELETE_EXPIRED_TRANSIENTS_MAX_EXECUTION_TIME', '10'); // default = 10
    define('DELETE_EXPIRED_TRANSIENTS_MAX_BATCH_RECORDS', '50'); // default = 50
    define('DISABLE_ATTACHMENT_PAGES', true); // default = true
    define('DISABLE_EMBEDS', true); // default = true
    define('DISABLE_EMBEDS_ALLOWED_SOURCES', 'twitter,facebook,youtube,soundcloud,scribd,etc'); // default = none
    define('DISABLE_EMOJIS', true); // default = true
    define('DISABLE_EMPTY_TRASH', true); // default = true
    define('DISABLE_GUTENBERG', true); // default = true
    define('DISABLE_IMAGE_COMPRESSION', true); // default = true
    define('DISABLE_POST_VIA_EMAIL', true); // default = true
    define('DISABLE_JQUERY_MIGRATE', true); // default = true
    define('DISABLE_POST_VIA_EMAIL', true); // default = true
    define('DISABLE_XML_RPC', true); // default = true
    define('FORCE_HTTPS', true); // default = true
    define('FORCE_HTTPS_EXTERNAL_LINKS', false); // default = false
    define('FORCE_HTTPS_EXTERNAL_RESOURCES', true); // default = true
    define('FORCE_HTTPS_INTERNAL_LINKS', true); // default = true
    define('FORCE_HTTPS_INTERNAL_RESOURCES', true); // default = true
    define('FORCE_STRONG_HASHING', true); // default = true
    define('HEADER_CLEANUP', true); // default = true
    define('INDEX_AUTOLOAD', true); // default = true
    define('INDEX_AUTOLOAD_REGENERATE', false); // default = false
    define('LIMIT_HEARTBEAT', true); // default = true
    define('LIMIT_HEARTBEAT_DISABLE_DASHBOARD', false); // default = false
    define('LIMIT_HEARTBEAT_DISABLE_EDITOR', false); // default = false
    define('LIMIT_HEARTBEAT_DISABLE_FRONTEND', true); // default = true
    define('LIMIT_HEARTBEAT_INTERVAL_DASHBOARD', 600); // default = 600
    define('LIMIT_HEARTBEAT_INTERVAL_EDITOR', 30); // default = 30
    define('LIMIT_HEARTBEAT_INTERVAL_FRONTEND', 300); // default = 300
    define('MINIFY_HTML', true); // default = true
    define('MINIFY_HTML_INLINE_STYLES', true); // default = true
    define('MINIFY_HTML_INLINE_STYLES_COMMENTS', true); // default = true
    define('MINIFY_HTML_REMOVE_COMMENTS', true); // default = true
    define('MINIFY_HTML_REMOVE_CONDITIONALS', true); // default = true
    define('MINIFY_HTML_REMOVE_EXTRA_SPACING', true); // default = true
    define('MINIFY_HTML_REMOVE_HTML5_SELF_CLOSING', false); // default = false
    define('MINIFY_HTML_REMOVE_LINE_BREAKS', true); // default = true
    define('MINIFY_HTML_INLINE_SCRIPTS', false); // default = false
    define('MINIFY_HTML_INLINE_SCRIPTS_COMMENTS', false); // default = false
    define('MINIFY_HTML_UTF8_SUPPORT', true); // default = true
    define('PLUGIN_BLACKLIST', true); // default = true
    define('REMOVE_QUERY_STRINGS', true); // default = true
    define('REMOVE_QUERY_STRINGS_ARGS', 'v,ver,version'); // default = v,ver,version
    define('SERVER_STATUS', true); // default = true
    define('SERVER_STATUS_DISPLAY', 'widefat'); // default = none
    define('SFTP_DETAILS', true); // default = true
    define('SFTP_DETAILS_SERVER', '123.123.123.123'); // *must be unique*
    define('SFTP_DETAILS_USER', 'username'); // *must be unique*
    define('SFTP_DETAILS_PASSWORD', 'password'); // *must be unique*
    define('SFTP_DETAILS_PORT', '6969'); // default = 6969
    define('SFTP_DETAILS_ROOT_DIR', '/var/www'); // default = /var/www
    define('SFTP_DETAILS_PUBLIC_DIR', '/var/www/html'); // default = /var/www/html
    define('VIRTUAL_ROBOTSTXT', true); // default = true

## Philosophy

Outside of the so-called [Application Layer](https://en.wikipedia.org/wiki/Application_layer), so much of the way computers and servers now work has been moved away from in-house teams and specialists and onto "the cloud" that terms like [DevOps](https://www.reddit.com/r/devops/comments/3rpzem/devops_vs_sysadmin/cwqmlnd/) have become standard among recruiters, companies, and developers alike. Modern web development trends have begun to revolve entirely around concepts such as automation, APIs, [cloud services](https://www.bcsg.com/wp-content/uploads/2015/03/The-small-business-revolution-trends-in-SMB-cloud-adoption.pdf), and beyond — a phenomenon we might refer to as *Web 3.0*.

While this shift is exciting, there is now a massive and growing disconnect between these emerging technologies and the humans that are expected to implement or benefit from them. Typical small business owners (SMBs), along with independent agencies or freelancers, now face a virtually impossible learning curve if they wish to maintain not only a competitive "webdev" edge, but even to keep up with basic standards in website performance and security.

While Silicon Valley "gurus" and corporations pump out new SaaS services (or incredibly complex Configuration Management tools like Ansible) on a daily basis, the typical small business website is still trying to figure out how to make their contact forms work correctly. The "legacy" shared web hosting monopolies — think EIG or GoDaddy — also have little motivation to education their audience, as perpetuating confusion seems to be a core pillar of their business model.

Thus, before the likes of Google and Amazon and Shopify and Wix take over the entire web and turn it into Wall Street-backed website builders that feed into their private ecosystems, SlickStack hopes to bridge the knowledge gap between emerging technology and old-school web development to empower SMBs to achieve top notch website performance and security by offering a "controlled" LEMP-stack environment with limited options that is perfectly suited to the world's most popular open-source CMS: WordPress.

## Comparison

| ... | SlickStack | EasyEngine | ServerPilot | Runcloud | Ansible | Puppet | Salt | Chef | Trellis | AnsiPress | VVV | VCCW | Centminmod | VPSSIM |
|:---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| 100% Free | Yes | Yes | No | No |
| Dependencies | None | Python | Ruby | Python | Python | Python | Ansible | Ansible | Vagrant | Vagrant | (Unix) | (Unix) |
| Direct Management | Yes | Yes | No | No |
| Shell Commands | Yes | No | No | No | No | No | No | No | No | No | Yes | Yes |
| WordPress Focus | Yes | No | No | No | No | Yes | Yes | No | Yes | Yes | No | Yes |
| Single Site Focus | Yes | N/A | No | No | N/A | N/A | No |
| Email APIs | Yes | N/A | N/A | N/A | No | No | No | No | No | No |
| Monitoring | Monit | N/A | None | None | None | None | None |
|  |  |  |  |  |  |  |  |

Others: Moss.sh, Webinoly, VestaCP, OneInStack, OpenResty, ServerPilot, RunCloud

## FAQ

* **Can I use your technical SEO features on non-SlickStack sites?** Yes, you can. If you or your client has a WordPress website on a web host somewhere and can't move it to a SlickStack server for the time being, you can still make use of all our `mu-plugins` (micro plugins) that are included in SlickStack by simply installing them one-by-one. Or you can install "less" plugins by installing the "parent" plugins instead, such as Speed Demon, SEO Genius, Security Guard.

* **Why don't use you env-vars?** Teams who use GitHub repos to develop their sites (e.g. dev/stage/production branches) have started using env-vars with systems like Roots Trellis so that their entire codebase can be safely open-source. However, this requires defining the env-vars within the server stack (hidden from public root) so it's not so friendly for many agencies. Since our goal is to support "typical" agencies with mostly frontend knowledge, we felt that reflecting the WordPress setup was a simpler approach and so if using SlickStack there will be two config files (ss-config and wp-config.php)

* **But shouldn't configs be outside the public root?** There's lots of "should" and theorizing in computer programming, but one reason for the success of WordPress is its pragmatism and user-friendliness. There's plenty to criticize about WordPress from the perspective of things like "12-factor apps" but let's remember that WordPress was around before many of these guidelines and even after better approaches have been founded, WordPress is still the king of CMS software for a reason.

* **Why ss-config-sample and not ss-config-example?** Because we aim to mirror WordPress Core as much as possible. Because the misnomer has existed in WordPress since the beginning, the WP Core team has [no intention](https://core.trac.wordpress.org/ticket/43827) of changing it.

* **Why don't you use TMPFS or similar?** For stability reasons, we don't use any tpmfs (memory-based storage) for caching or otherwise, as it introduces more instability, possible data loss, and doesn't necessarily improve performance. What people people don't realize is Linux already uses tmpfs for its own purposes, and already stores many "requests" in RAM. Best to let the operating system do its thing, and we optimize the software packages installed.

## Thanks

* [Alex Georgiou](https://www.alexgeorgiou.gr)
* [rtCamp](https://rtcamp.com)
* [Roots](https://roots.io)

## Ref

* https://www.alexgeorgiou.gr/wp-cli-www-data-user-permissions-linux/

## Keywords

slickstack, slick stack, nginx auto installer, optimize lemp stack, best wordpress stack, lemp install script, trellis wordpress, easy engine, lemp auto installer, nginx install script, stackscript

