# SlickStack [ss] - "Alpha ss6e"

SlickStack is a free LEMP stack automation script written in Bash designed to enhance and simplify WordPress provisioning, performance, and security.

| Google PageSpeed | GTMetrix | Pingdom | Security Headers | Qualys SSL Labs |
| :--------------: | :------: | :-----: | :--------------: | :-------------: |
| [**A**](https://developers.google.com/speed/pagespeed/insights/?url=https%3A%2F%2Fslickstack.io%2F) | [**A**](https://gtmetrix.com/reports/slickstack.io/zpLMZ1eb) | [**A**](https://tools.pingdom.com/#5aeba9dea8000000) | [**A**](https://securityheaders.com/?q=https%3A%2F%2Fslickstack.io%2F&followRedirects=on) | [**A**](https://www.ssllabs.com/ssltest/analyze.html?d=slickstack.io&latest) |

* **NEW!** SlickStack now supports "disabling" our MU plugins via `ss-config` ... remember that currently, this means no CloudFlare, Clear Caches, and other valuable plugins, so be sure to setup your own if needed...

* **NEW!** SlickStack now supports remote databases and better `DB_` customization...

* **NEW!** SlickStack now supports ClassicPress (experimental drop-in alternative to WordPress)...

* **NEW!** SlickStack now supports loading FastCGI Cache as `tmpfs` (optional)...

* **NEW!** SlickStack better `ss-config` formatting (variables) now live...

* **NEW!** SlickStack now supports custom `wp-config.php` boilerplates using `WP_CONFIG_SOURCE` variable... make sure that your boilerplate is using variables supported in the latest `ss-config` and `ss-install` builds...

* **NEW!** SlickStack `ss-install` now verifies that `ss-config` is up-to-date before running the installation...

* **NEW!** SlickStack now supports custom plugin blacklists using `PLUGIN_BLACKLIST_SOURCE` variable...

* **NEW!** SlickStack now does `include_once` within wp-config.php on the Custom Functions (MU plugin) file `/var/www/html/wp-content/functions.php` meaning much more reliable PHP functions...

## Abstract

Most of modern computing history can be traced back to one thing: [Unix](https://en.wikipedia.org/wiki/Unix). Indeed, one of the only things about web servers that hasn't changed much in several decades is the Unix shell (Bash) command language. Keeping the same pragmatism and simplicity in mind that inspired LittleBizzy's managed hosting, SlickStack [ss] is coded entirely in Bash.

While there are [clear benefits](https://medium.com/capital-one-developers/bashing-the-bash-replacing-shell-scripts-with-python-d8d201bc0989) to programming languages like Python or Ruby, provisioning a server with WordPress isn't very complicated, and every Linux server comes with [shell built into it](https://www.infoworld.com/article/2893519/linux/perl-python-ruby-are-nice-bash-is-where-its-at.html). Plus, let's not forget [what happens](https://discourse.roots.io/t/updated-to-ansible-2-4-deploys-broken-now-what/10588) when typical web agencies rely on advanced dependencies like Ansible... yikes! Onward, then...

## Requirements (Compatibility)

SlickStack [ss] works best on VPS servers with KVM virtualization that have at least 2GB RAM from [quality network providers](https://slickstack.io/hosting/) such as DigitalOcean, Vultr, Linode, and Amazon Lightsail. The underlying LEMP stack configuration is meant specifically for single-site WordPress installations, and does not support [Multisite](https://codex.wordpress.org/Create_A_Network) installations. SlickStack [ss] supports WordPress, WooCommerce, bbPress, and BuddyPress "out of the box" with optimized settings that scale.

Currently, SlickStack [ss] is meant for a single origin server with a `localhost` database, although remote databases should also work fine. Server "clustering" or "load balancing" has not been tested, and is not the goal here; complex enterprise-style configurations for WordPress are rarely needed (and can be expensive and difficult to manage), thus SlickStack [ss] aims to to provide a simple solution for the 99% of WordPress sites that don't need such.

It should also be noted that SlickStack [ss] is HTTPS-only, meaning that HTTP sites are not supported. Because OpenSSL generates self-signed certificates, SlickStack [ss] servers require CloudFlare to be active in front of your server in order for SSL certificates to be properly CA-signed and loaded by your browser.

## Core Modules

*Last updated: Jul 22, 2019*

| LEMP Module | Mirrors | Version | What does SlickStack [ss] customize? |
| :------------- | :----------: | :----------: | :----------: |
| **Ubuntu** | [mirrors](http://mirrors.slickstack.io/ubuntu/) | 18.04 (LTS) | `crontab` + `gai.conf` + `sshd_config` + `sudoers` + `sysctl.conf` |
| **Nginx (Extras)** | [mirrors](http://mirrors.slickstack.io/nginx/) | 1.15.8 | `nginx.conf` + `default` (server block) |
| **FastCGI Cache** | [mirrors](http://mirrors.slickstack.io/fastcgi-cache/) | 1.15.8 | `fastcgi-cache.conf` |
| **OpenSSL** | [mirrors](http://mirrors.slickstack.io/openssl/) | 1.1.0g | default config |
| **Let's Encrypt†** | [mirrors](http://mirrors.slickstack.io/letsencrypt/) | 0.23.0 | custom config |
| **MySQL** | [mirrors](http://mirrors.slickstack.io/mysql/) | 5.7.25 | default config (will be customized slightly in future) |
| **PHP-FPM** | [mirrors](http://mirrors.slickstack.io/php-fpm/) | 7.2.17 | `php.ini` + `php-fpm.conf` + `www.conf` |
| **Zend / OPcache** | [mirrors](http://mirrors.slickstack.io/opcache/) | 3.2.0 / 7.2.17 | (same as PHP-FPM) |
| **WordPress** | [mirrors](http://mirrors.slickstack.io/wordpress/) | 5.2.2 | some WP Core junk files are removed by `ss-clean` |
| **ClassicPress** | [mirrors](http://mirrors.slickstack.io/classicpress/) | 1.0.1 | some CP Core junk files are removed by `ss-clean` |
| **MU Plugins** | [mirrors](http://mirrors.slickstack.io/mu-plugins/) | N/A | several `mu-plugins` by LittleBizzy |
| **WP-CLI** | [mirrors](http://mirrors.slickstack.io/wp-cli/) | 2.2.0 | default config |
| **Redis (Obj Cache)** | [mirrors](http://mirrors.slickstack.io/redis/) | 4.0.9 | `redis.conf` + `object-cache.php` |
| **Git** | [mirrors](http://mirrors.slickstack.io/git/) | 2.17.1 | default config |
| **UFW Firewall** | [mirrors](http://mirrors.slickstack.io/ufw-firewall/) | 0.36 | `ufw` + `ufw.conf` + `user-rules` |
| **ClamAV** | [mirrors](http://mirrors.slickstack.io/clamav/) | 0.100.x | `freshclam.conf` |

†Not yet supported (pending)

Default Ports: 80 (HTTP), 443 (HTTPS), 6969 (SSH)

## Installation

Because it’s written purely in [Bash](https://en.wikipedia.org/wiki/Bash_(Unix_shell)) (Unix shell), SlickStack [ss] has no dependencies and works on any [Ubuntu Linux](https://www.ubuntu.com) machine. Unlike heavier provisioning tools like EasyEngine or Ansible, there are no third party languages required such as Python or Docker, meaning a lighter and simpler approach to launching WordPress servers.

The below installation steps assume that you've already spun up a dedicated Ubuntu Linux VPS server (KVM) with at least 2GB RAM memory and that you are now logged in via SSH:

1. `sudo mkdir /var/www/ && sudo chown root:root /var/www/ && sudo chmod 755 /var/www/`

2. `sudo nano /var/www/ss-config`  [*configure as desired*](http://mirrors.slickstack.io/ss-config-sample.txt)

3. `cd /var/www/ && sudo wget -O ss slick.fyi && sudo chmod 755 ss && sudo bash ss`

4. `sudo reboot`

**NOTE:** SlickStack [ss] requires CloudFlare to be activated on your domain before SSL (HTTPS) will be recognized as a fully secure and CA-signed domain, because of its self-signed OpenSSL certificate.

From this point forward, you can manage your SlickStack [ss] server by simply using the `sudo bash` command on any one of the included **ss** scripts located within the `/var/www/` directory, as needed. However, in most cases there shouldn't be any need for much hands-on management as the server will intelligently run various cron jobs which connect to this GitHub repo (or whichever fork of this repo that your team has setup... be sure to modify all `wget` sources).

You can safely re-install SlickStack [ss] anytime via `sudo bash /var/www/ss-install` without causing any conflicts or data loss since the installation process is completely [idempotent](https://en.wikipedia.org/wiki/Idempotence).

## Directory Structure

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
    /var/www/meta/
    /var/www/ss-check
    /var/www/ss-clean
    /var/www/ss-config
    /var/www/ss-config-sample
    /var/www/ss-dump
    /var/www/ss-encrypt
    /var/www/ss-install
    /var/www/ss-muplugs
    /var/www/ss-perms
    /var/www/ss-scan
    /var/www/ss-update
    /var/www/ss-worker
    /var/www/wp.sql
    
...likewise, your `/var/www/html/` (WordPress) directory should look like this:    
    
    /var/www/html/wp-admin/
    /var/www/html/wp-content/
    /var/www/html/wp-includes/
    /var/www/html/wp-...
    
## MU (Must Use) Plugins
    
If you choose to deploy a SlickStack [ss] server using our free WPLite boilerplate, the installation process will include several [Must Use plugins](https://wordpress.org/support/article/must-use-plugins/) inside your WordPress structure (`/var/www/html/wp-content/mu-plugins/`) that are maintained by LittleBizzy. If you do not wish for these Must Use plugins to be installed, and want a default "vanilla" WordPress installation, choose "wordpress" instead of "wplite" when setting up your `ss-config` options:

* [**Autoloader**](https://github.com/littlebizzy/autoloader): Enables standard WordPress plugins contained in a folder to be placed in the mu-plugins directory and loaded prior to others (forked from Bedrock).
* [**Clear Caches**](https://github.com/littlebizzy/clear-caches): The easiest way to clear caches including WordPress cache, PHP Opcache, Nginx cache, Transient cache, Varnish cache, and object cache (e.g. Redis).
* [**CloudFlare**](https://github.com/littlebizzy/cloudflare): Easily connect your WordPress website to free optimization features from CloudFlare, including one-click options to purge cache and enable dev mode.
* [**Custom Functions**](https://github.com/littlebizzy/custom-functions): Enables the ability to input custom WordPress functions such as filters in a centralized place to avoid the dependence on a theme functions.php file.
* [**Dashboard Cleanup**](https://github.com/littlebizzy/dashboard-cleanup): Cleans up the WP Admin backend by disabling various bloat features including nag notices, Automattic spam, and other outdated and pointless items.
* [**Delete Expired Transients**](https://github.com/littlebizzy/delete-expired-transients): Deletes all expired transients upon activation and on a daily basis thereafter via WP-Cron to maintain a cleaner database and improve performance.
* [**Disable Attachment Pages**](https://github.com/littlebizzy/disable-attachment-pages): Completely disables media attachment pages which then become 404 errors to avoid thin content SEO issues and better guard against snoopers and bots.
* [**Disable Embeds**](https://github.com/littlebizzy/disable-embeds): Disables both external and internal embedding functions to avoid slow page render, instability and SEO issues, and to improve overall loading speed.
* [**Disable Emojis**](https://github.com/littlebizzy/disable-emojis): Completely disables both the old and new versions of WordPress emojis, removes the corresponding javascript calls, and improves page loading times.
* [**Disable Empty Trash**](https://github.com/littlebizzy/disable-empty-trash): Completely disables the automatic trash empty for WordPress posts, custom posts, pages, and comments to avoid data loss and encourage manual emptying.
* [**Disable Gutenberg**](https://github.com/littlebizzy/disable-gutenberg): Completely disables the Gutenberg block editor and enables the classic WordPress post editor (TinyMCE aka WYSIWYG) for lighter coding and simplicity.
* [**Disable Image Compression**](https://github.com/littlebizzy/disable-image-compression): Completely disables all JPEG compression in WordPress including image uploads, thumbnails, and image editing tools, thus retaining original quality.
* [**Disable Post Via Email**](https://github.com/littlebizzy/disable-post-via-email): Completely disables and hides the Post Via Email feature included in WordPress core for stronger security and to simplify the backend settings page.
* [**Disable XML-RPC**](https://github.com/littlebizzy/disable-xml-rpc): Completely disables all XML-RPC related functions in WordPress including pingbacks and trackbacks, and helps prevent attacks on the xmlrpc.php file.
* [**Error Log Monitor**]():
* [**Force Strong Hashing**](https://github.com/littlebizzy/force-strong-hashing): Forces all user passwords generated by WordPress to be hashed using Bcrypt, the most secure and popular PHP hashing algorithm currently available.
* [**Header Cleanup**](https://github.com/littlebizzy/header-cleanup): Cleans up most of the unnecessary junk meta included by default in the WordPress header including generator, RSD, shortlink, previous and next, etc.
* [**Index Autoload**](https://github.com/littlebizzy/index-autoload): Adds an index to the autoload in wp_options table and verifies it exists on a daily basis (using WP Cron), resulting in a more efficient database.
* [**Limit Heartbeat**](https://github.com/littlebizzy/limit-heartbeat): Limits the Heartbeat API in WordPress to certain areas of the site (and a longer pulse interval) to reduce AJAX queries and improve resource usage.
* [**Minify HTML**](https://github.com/littlebizzy/minify-html): Tactfully minifies HTML output and markup to remove line breaks, whitespace, comments, and other code bloat to cleanup source code and improve speed.
* [**Plugin Blacklist**](https://github.com/littlebizzy/plugin-blacklist): Allows web hosts, agencies, or other WordPress site managers to disallow a custom list of plugins from being activated for security or other reasons.
* [**Remove Query Strings**](https://github.com/littlebizzy/remove-query-strings): Removes all query strings from static resources meaning that proxy servers and beyond can better cache your site content (plus, better SEO scores).
* [**Server Status**](https://github.com/littlebizzy/server-status): Useful statistics about the server OS, CPU, RAM, load average, memory usage, IP address, hostname, timezone, disk space, PHP, MySQL, caches, etc.
* [**SFTP Details**](https://github.com/littlebizzy/sftp-details): Displays a small Dashboard widget to remind logged-in Admin users of their server SFTP login information for easy reference (uses defined constants).
* [**Virtual Robotstxt**](https://github.com/littlebizzy/virtual-robotstxt): Replaces the default virtual robots.txt generated by WordPress with an editable one, and deletes any physical robots.txt file that may already exist.
* [**XXX Notices**](https://github.com/littlebizzy/slickstack/blob/master/mu-plugins/xxx-notices.txt): Occasional notices designed to appear in the WP Admin Dashboard that mention important changes regarding the SlickStack environment (white-labeled).
    
## Defined Constants

The included Must Use plugins that SlickStack [ss] bundles as part of the `wplite` boilerplate support the following defined constants. Some of them are hard-coded in the `wp-config.php` file in order to optimize performance, however they can otherwise be customized using the Custom Functions file at `/wp-content/functions.php` ... please note that some of these are "planned" and not yet functional, we are hurrying to update the documentation accordingly.

    /** Plugin Meta (Limited Support) */
    define('AUTOMATIC_UPDATE_PLUGINS', false); // default = false
    define('DISABLE_NAG_NOTICES', false); // default = false

    /** Clear Caches Functions (v1.2.1) */
    define('CLEAR_CACHES', true); // default = true
    define('CLEAR_CACHES_NGINX', true); // default = true
    define('CLEAR_CACHES_NGINX_PATH', '/var/www/cache'); // default = /var/www/cache
    define('CLEAR_CACHES_OBJECT', true); // default = true
    define('CLEAR_CACHES_OPCACHE', true); // default = true
    
    /** CloudFlare Functions (v1.5.0) */
    define('CLOUDFLARE', true); // default = true
    define('CLOUDFLARE_API_KEY', '@CLOUDFLAREAPIKEY'); // *must be unique*
    define('CLOUDFLARE_API_EMAIL', '@CLOUDFLAREAPIEMAIL'); // *must be unique*
    // define('CLOUDFLARE_WIDGET_DNS', true); // default = true
    // define('CLOUDFLARE_WIDGET_ANALYTICS', true); // default = true

    /** Custom Functions Functions (v1.0.0) */
    // define('CUSTOM_FUNCTIONS', true); // default = true
    // define('CUSTOM_FUNCTIONS_PATH', '/var/www/html/wp-content/functions.php'); // default = /var/www/html/wp-content/functions.php
    
    /** Dashboard Cleanup Functions (v1.1.2) */
    define('DASHBOARD_CLEANUP', true); // default = true
    define('DASHBOARD_CLEANUP_ADD_PLUGIN_TABS', true); // default = true
    define('DASHBOARD_CLEANUP_ADD_THEME_TABS', true); // default = true
    define('DASHBOARD_CLEANUP_CSS_ADMIN_NOTICE', true); // default = true
    define('DASHBOARD_CLEANUP_DISABLE_SEARCH', true); // default = true
    define('DASHBOARD_CLEANUP_EVENTS_AND_NEWS', true); // default = true
    define('DASHBOARD_CLEANUP_IMPORT_EXPORT_MENU', true); // default = true
    define('DASHBOARD_CLEANUP_LINK_MANAGER_MENU', true); // default = true
    define('DASHBOARD_CLEANUP_QUICK_DRAFT', true); // default = true
    define('DASHBOARD_CLEANUP_THANKS_FOOTER', true); // default = true
    define('DASHBOARD_CLEANUP_WELCOME_TO_WORDPRESS', true); // default = true
    define('DASHBOARD_CLEANUP_WOOCOMMERCE_CONNECT_STORE', true); // default = true
    define('DASHBOARD_CLEANUP_WOOCOMMERCE_FOOTER_TEXT', true); // default = true
    define('DASHBOARD_CLEANUP_WOOCOMMERCE_MARKETPLACE_SUGGESTIONS', true); // default = true
    define('DASHBOARD_CLEANUP_WOOCOMMERCE_PRODUCTS_BLOCK', true); // default = true
    define('DASHBOARD_CLEANUP_WOOCOMMERCE_TRACKER', true); // default = true
    define('DASHBOARD_CLEANUP_WP_ORG_SHORTCUT_LINKS', true);  // default = true
    
    /** Delete Expired Transients Functions */
    define('DELETE_EXPIRED_TRANSIENTS', true); // default = true
    define('DELETE_EXPIRED_TRANSIENTS_HOURS', '6'); // default = 6
    define('DELETE_EXPIRED_TRANSIENTS_MAX_EXECUTION_TIME', '10'); // default = 10
    define('DELETE_EXPIRED_TRANSIENTS_MAX_BATCH_RECORDS', '50'); // default = 50
    
    /** Disable Attachment Pages Functions */
    // define('DISABLE_ATTACHMENT_PAGES', true); // default = true
    
    /** Disable Embeds Functions (v1.3.0) */
    define('DISABLE_EMBEDS', true); // default = true
    define('DISABLE_EMBEDS_ALLOWED_SOURCES', 'twitter,facebook,youtube,soundcloud,etc'); // default = none
    
    /** Disable Emojis Functions */
    // define('DISABLE_EMOJIS', true); // default = true
    
    /** Disable Empty Trash Functions */
    // define('DISABLE_EMPTY_TRASH', true); // default = true
    
    /** Disable Gutenberg Functions (v1.1.0) */
    define('DISABLE_GUTENBERG', true); // default = true
    
    /* Disable Image Compression Functions */
    // define('DISABLE_IMAGE_COMPRESSION', true); // default = true
    
    /** Disable jQuery Migrate Functions */
    // define('DISABLE_JQUERY_MIGRATE', true); // default = true
    
    /** Disable Post Via Email Functions */
    // define('DISABLE_POST_VIA_EMAIL', true); // default = true
    
    /** Disable XML-RPC Functions */
    // define('DISABLE_XML_RPC', true); // default = true
    
    /** Force HTTPS Functions (v1.4.0) */
    define('FORCE_HTTPS', true); // default = true
    define('FORCE_HTTPS_EXTERNAL_LINKS', false); // default = false
    define('FORCE_HTTPS_EXTERNAL_RESOURCES', true); // default = true
    define('FORCE_HTTPS_INTERNAL_LINKS', true); // default = true
    define('FORCE_HTTPS_INTERNAL_RESOURCES', true); // default = true
    
    /** Force Strong Hashing Functions */
    // define('FORCE_STRONG_HASHING', true); // default = true
    
    /** Header Cleanup Functions */
    // define('HEADER_CLEANUP', true); // default = true
    
    /** Index Autoload Functions (v1.1.1) */
    // define('INDEX_AUTOLOAD', true); // default = true
    define('INDEX_AUTOLOAD_REGENERATE', false); // default = false
    
    /** Limit Heartbeat Functions (v1.1.0) */
    define('LIMIT_HEARTBEAT', true); // default = true
    define('LIMIT_HEARTBEAT_DISABLE_DASHBOARD', false); // default = false
    define('LIMIT_HEARTBEAT_DISABLE_EDITOR', false); // default = false
    define('LIMIT_HEARTBEAT_DISABLE_FRONTEND', true); // default = true
    define('LIMIT_HEARTBEAT_INTERVAL_DASHBOARD', 600); // default = 600
    define('LIMIT_HEARTBEAT_INTERVAL_EDITOR', 30); // default = 30
    define('LIMIT_HEARTBEAT_INTERVAL_FRONTEND', 300); // default = 300
    
    /** Minify HTML Functions (v1.0.1) */
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
    
    /* Plugin Blacklist Functions */
    define('PLUGIN_BLACKLIST', true); // default = true
    
    /* Remove Query Strings Functions */
    define('REMOVE_QUERY_STRINGS', true); // default = true
    define('REMOVE_QUERY_STRINGS_ARGS', 'v,ver,version'); // default = v,ver,version
    
    /* Server Status Functions */
    define('SERVER_STATUS', true); // default = true
    define('SERVER_STATUS_DISPLAY', 'widefat'); // default = none
    
    /* SFTP Details Functions */
    define('SFTP_DETAILS', true); // default = true
    define('SFTP_DETAILS_SERVER', '123.123.123.123'); // *must be unique*
    define('SFTP_DETAILS_USER', 'username'); // *must be unique*
    define('SFTP_DETAILS_PASSWORD', 'password'); // *must be unique*
    define('SFTP_DETAILS_PORT', '6969'); // default = 6969
    define('SFTP_DETAILS_ROOT_DIR', '/var/www'); // default = /var/www
    define('SFTP_DETAILS_PUBLIC_DIR', '/var/www/html'); // default = /var/www/html
    
    /* Virtual Robots.txt Functions */
    define('VIRTUAL_ROBOTSTXT', true); // default = true

## Philosophy

Outside of the so-called [Application Layer](https://en.wikipedia.org/wiki/Application_layer), so much of the way computers and servers now work has been moved away from in-house teams and specialists and onto "the cloud" that terms like [DevOps](https://www.reddit.com/r/devops/comments/3rpzem/devops_vs_sysadmin/cwqmlnd/) have become standard among recruiters, companies, and developers alike. Modern web development trends have begun to revolve entirely around concepts such as automation, APIs, [cloud services](https://www.bcsg.com/wp-content/uploads/2015/03/The-small-business-revolution-trends-in-SMB-cloud-adoption.pdf), and beyond — a phenomenon we might refer to as *Web 3.0*.

While this shift is exciting, there is now a massive and growing disconnect between these emerging technologies and the humans that are expected to implement or benefit from them. Typical small business owners (SMBs), along with independent agencies or freelancers, now face a virtually impossible learning curve if they wish to maintain a competitive "webdev" edge, let alone keep up with basic standards in website performance and security.

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

**Can I use your technical SEO features on non-SlickStack sites?**

Yes, you can. If you or your client has a WordPress website on a web host somewhere and can't move it to a SlickStack server for the time being, you can still make use of all our `mu-plugins` (micro plugins) that are included in SlickStack by simply installing them one-by-one. Or you can install "less" plugins by installing the "parent" plugins instead, such as Speed Demon, SEO Genius, Security Guard.

**Why do you use Ubuntu instead of other distros?**

Many geeky distro fanboys love to hate on Ubuntu for one reason or another, but the truth remains that besides RedHat, Ubuntu has been the only stand out and dominant Linux distro for many years now. They have beyond proven themselves when it comes to regularl and reliable versioning and release schedules, and incredible package maintenance. Ubuntu has their finger on the pulse of industry changes and has a solid idea of what packages should be included -- and what versions of those packages. Their unique and impressive focus on stability makes them a perfect OS to base a LEMP stack script on. Their decisions end up widely impacting the greater web hosting and web dev community, and they have a corporate sponsor that is financially stable, while still maintaining and promoting a truly open source community that is the perfect answer to avoiding Big Tech monopolies.

More: https://slickstack.io/features/ubuntu/

**What version of WordPress does SlickStack install?**

Generally it installs the latest (current) version of WordPress, as long its not a major version. In other words, since our goal is stability and security, SlickStack will always bundle the most recent "minor" version of WordPress. So if the latest release of WordPress over at WordPress.org is currently 5.2.0 then SlickStack will usually wait to bundle version 5.2.1 because these minor versions (as per semantic versioning standards) means that several bugs and patches have now been fixed. So in this scenario if 5.2.1 is not yet available, then SlickStack will install 5.1.1 because its the latest stable "minor" patched release.

More: https://slickstack.io/features/wordpress/

**Why do you use MySQL instead of MariaDB?**

The original reason for the MariaDB fork was because a handful of engineers feared that Oracle would neglect MySQL after they acquired Sun Microsystems in favor of the Oracle Database systems. This turned out to be far from true, and MySQL has maintained its market dominance without blinking an eye, while continue to be open source. While its true that MariaDB is more transparent about bug patches, and perhaps is more open source minded, the argument that MariaDB has better performance is really just not true when you compare typical WordPress hosting scenarios. Plus, MySQL continues to come out with new revolutionary features that the smaller team at MariaDB will likely find it impossible to compete with. Ultimately, the point is that Ubuntu still ships with MySQL being the default on their distributions, and until that changes, we will be sticking with MySQL. Our goal is stability and trusting the "upstream" stack as much as possible, which means not fixing things that aren't broken. We fully expect many companies might be moving back to MySQL in the next few years as well... time will tell.

More: https://slickstack.io/features/mysql/

**Why do you use OpenSSL instead of Let's Encrypt?** 

This is largely a decision that is based on LittleBizzy's web hosting approach, which requires our clients to be behind CloudFlare. As the fastest DNS resolver in the world, it simply doesn't make any sense right now NOT to use CloudFlare, especially since its completely free. By using OpenSSL, which is included in Ubuntu Linux, it means easier installation and configuration, and it never expires either. In contrast, Let's Encrypt is much more complicated to setup and maintain, and their certificates expire constantly after 90 days, meaning your server management software must constantly renew it and manage it. There is really no point in our view to introduce more potential conflicts and failures into SlickStack, when OpenSSL pairs perfectly with CloudFlare's free SSL network, soon to be the largest SSL provider in the world. By using self-signed certs, SlickStack [ss] also avoids the potential of Man-In-The-Middle attacks, meaning even stronger security. This decision might change in the future, but for now we will only be supporting OpenSSL self-signed certicates and assuming an integration with CloudFlare.

More: https://slickstack.io/features/openssl/

**What is the complete list of Nginx modules that are installed?** 

    nginx -V
    nginx version: nginx/1.15.8
    built with OpenSSL 1.1.0g  2 Nov 2017
    TLS SNI support enabled
    configure arguments: --with-cc-opt='-g -O2 -fdebug-prefix-map=/build/nginx-hFnDHF/nginx-1.15.8=. -fstack-protector-strong -Wformat -Werror=format-security -fPIC -Wdate-time -D_FORTIFY_SOURCE=2' --with-ld-opt='-Wl,-Bsymbolic-functions -Wl,-z,relro -Wl,-z,now -fPIC' --prefix=/usr/share/nginx --conf-path=/etc/nginx/nginx.conf --http-log-path=/var/log/nginx/access.log --error-log-path=/var/log/nginx/error.log --lock-path=/var/lock/nginx.lock --pid-path=/run/nginx.pid --modules-path=/usr/lib/nginx/modules --http-client-body-temp-path=/var/lib/nginx/body --http-fastcgi-temp-path=/var/lib/nginx/fastcgi --http-proxy-temp-path=/var/lib/nginx/proxy --http-scgi-temp-path=/var/lib/nginx/scgi --http-uwsgi-temp-path=/var/lib/nginx/uwsgi --with-compat --with-debug --with-pcre-jit --with-http_ssl_module --with-http_stub_status_module --with-http_realip_module --with-http_auth_request_module --with-http_v2_module --with-http_dav_module --with-http_slice_module --with-threads --with-http_addition_module --with-http_flv_module --with-http_geoip_module=dynamic --with-http_gunzip_module --with-http_gzip_static_module --with-http_image_filter_module=dynamic --with-http_mp4_module --with-http_perl_module=dynamic --with-http_random_index_module --with-http_secure_link_module --with-http_sub_module --with-http_xslt_module=dynamic --with-mail=dynamic --with-mail_ssl_module --with-stream=dynamic --with-stream_ssl_module --with-stream_ssl_preread_module --add-dynamic-module=/build/nginx-hFnDHF/nginx-1.15.8/debian/modules/http-headers-more-filter --add-dynamic-module=/build/nginx-hFnDHF/nginx-1.15.8/debian/modules/http-auth-pam --add-dynamic-module=/build/nginx-hFnDHF/nginx-1.15.8/debian/modules/http-cache-purge --add-dynamic-module=/build/nginx-hFnDHF/nginx-1.15.8/debian/modules/http-dav-ext --add-dynamic-module=/build/nginx-hFnDHF/nginx-1.15.8/debian/modules/http-ndk --add-dynamic-module=/build/nginx-hFnDHF/nginx-1.15.8/debian/modules/http-echo --add-dynamic-module=/build/nginx-hFnDHF/nginx-1.15.8/debian/modules/http-fancyindex --add-dynamic-module=/build/nginx-hFnDHF/nginx-1.15.8/debian/modules/nchan --add-dynamic-module=/build/nginx-hFnDHF/nginx-1.15.8/debian/modules/http-lua --add-dynamic-module=/build/nginx-hFnDHF/nginx-1.15.8/debian/modules/rtmp --add-dynamic-module=/build/nginx-hFnDHF/nginx-1.15.8/debian/modules/http-uploadprogress --add-dynamic-module=/build/nginx-hFnDHF/nginx-1.15.8/debian/modules/http-upstream-fair --add-dynamic-module=/build/nginx-hFnDHF/nginx-1.15.8/debian/modules/http-subs-filter
    
**What is the complete list of Nginx packages installed?**

    dpkg --get-selections | grep -i nginx
    libnginx-mod-http-auth-pam                      install
    libnginx-mod-http-cache-purge                   install
    libnginx-mod-http-dav-ext                       install
    libnginx-mod-http-echo                          install
    libnginx-mod-http-fancyindex                    install
    libnginx-mod-http-geoip                         install
    libnginx-mod-http-headers-more-filter           install
    libnginx-mod-http-image-filter                  install
    libnginx-mod-http-lua                           install
    libnginx-mod-http-ndk                           install
    libnginx-mod-http-perl                          install
    libnginx-mod-http-subs-filter                   install
    libnginx-mod-http-uploadprogress                install
    libnginx-mod-http-upstream-fair                 install
    libnginx-mod-http-xslt-filter                   install
    libnginx-mod-mail                               install
    libnginx-mod-nchan                              install
    libnginx-mod-stream                             install
    nginx-common                                    install
    nginx-extras                                    install
    
**What is the complete list of PHP-FPM modules installed?**

    php -m
    [PHP Modules]
    bcmath
    calendar
    Core
    ctype
    curl
    date
    dom
    exif
    fileinfo
    filter
    ftp
    gd
    gettext
    hash
    iconv
    igbinary
    json
    libxml
    mbstring
    mysqli
    mysqlnd
    openssl
    pcntl
    pcre
    PDO
    pdo_mysql
    Phar
    posix
    readline
    redis
    Reflection
    session
    shmop
    SimpleXML
    soap
    sockets
    sodium
    SPL
    standard
    sysvmsg
    sysvsem
    sysvshm
    tokenizer
    wddx
    xml
    xmlreader
    xmlwriter
    xsl
    Zend OPcache
    zip
    zlib

    [Zend Modules]
    Zend OPcache
    
**What is the complete list of PHP-FPM packages installed?**

    dpkg --get-selections | grep -i php
    php-common                                      install
    php-igbinary                                    install
    php-redis                                       install
    php7.2                                          install
    php7.2-bcmath                                   install
    php7.2-cli                                      install
    php7.2-common                                   install
    php7.2-curl                                     install
    php7.2-fpm                                      install
    php7.2-gd                                       install
    php7.2-json                                     install
    php7.2-mbstring                                 install
    php7.2-mysql                                    install
    php7.2-opcache                                  install
    php7.2-readline                                 install
    php7.2-soap                                     install
    php7.2-xml                                      install
    php7.2-zip                                      install

**Why don't use you env-vars (environment variables)?**

Teams who use GitHub repos to develop their sites (e.g. dev/stage/production branches) have started using env-vars with systems like Roots Trellis so that their entire codebase can be safely open-source. However, this requires defining the env-vars within the server stack (hidden from public root) so it's not so friendly for many agencies. Since our goal is to support "typical" agencies with mostly frontend knowledge, we felt that reflecting the WordPress setup was a simpler approach and so if using SlickStack there will be two config files (ss-config and wp-config.php)

**But shouldn't configs be outside the public root?**

There's lots of "should" and theorizing in computer programming, but one reason for the success of WordPress is its pragmatism and user-friendliness. There's plenty to criticize about WordPress from the perspective of things like "12-factor apps" but let's remember that WordPress was around before many of these guidelines and even after better approaches have been founded, WordPress is still the king of CMS software for a reason.

**Why ss-config-sample and not ss-config-example?**

Because we aim to mirror WordPress Core as much as possible. Because the misnomer has existed in WordPress since the beginning, the WP Core team has [no intention](https://core.trac.wordpress.org/ticket/43827) of changing it.

**Why don't you use TMPFS or similar?**

For stability reasons, we don't use any tpmfs (memory-based storage) for caching or otherwise, as it introduces more instability, possible data loss, and doesn't necessarily improve performance. What people people don't realize is Linux already uses tmpfs for its own purposes, and already stores many "requests" in RAM. Best to let the operating system do its thing, and we optimize the software packages installed.

## Thanks

* [rtCamp](https://rtcamp.com) -- various inspiration
* [Roots](https://roots.io) -- various inspiration and original author of Autoloader script
* [Centminmod](https://centminmod.com) -- various inspiration
* [Alex Georgiou](https://www.alexgeorgiou.gr) -- feedback on WP-CLI configuration
* [Janis Elsts](https://w-shadow.com) -- author of Error Log Monitor plugin
* [PressJitsu](http://pressjitsu.com) -- various inspuration and original author of Object Cache script

## Ref

* https://www.alexgeorgiou.gr/wp-cli-www-data-user-permissions-linux/

## Keywords

slickstack, slick stack, nginx auto installer, optimize lemp stack, best wordpress stack, lemp install script, trellis wordpress, easy engine, lemp auto installer, nginx install script, stackscript
