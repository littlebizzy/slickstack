# SlickStack [ss] - "Alpha"

> SlickStack is a free LEMP stack automation script written in Bash designed to enhance and simplify WordPress provisioning, performance, and security.

&#10142; No email please! Post all questions &amp; comments in our free [Facebook group](https://www.facebook.com/groups/littlebizzy/).

## Table Of Contents

1. [Abstract](#abstract)
2. [Compatibility](#compatibility)
3. [Installation](#installation)
4. [Structure](#structure)
5. [Philosophy](#philosophy)
6. [Comparison](#comparison)
7. [FAQ](#faq)

## Abstract

Most of modern computing history can be traced back to one thing: [Unix](https://en.wikipedia.org/wiki/Unix). Indeed, one of the only things about web servers that hasn't changed much in several decades is the Unix shell (Bash) command language. Keeping the same pragmatism and simplicity in mind that inspired LittleBizzy's managed hosting, SlickStack [ss] is coded entirely in Bash.

While there are [clear benefits](https://medium.com/capital-one-developers/bashing-the-bash-replacing-shell-scripts-with-python-d8d201bc0989) to programming languages like Python or Ruby, provisioning a server with WordPress isn't very complicated, and every Linux server comes with [shell built into it](https://www.infoworld.com/article/2893519/linux/perl-python-ruby-are-nice-bash-is-where-its-at.html). Plus, let's not forget [what happens](https://discourse.roots.io/t/updated-to-ansible-2-4-deploys-broken-now-what/10588) when typical web agencies rely on advanced dependencies like Ansible... yikes! Onward, then...

## Compatibility

SlickStack [ss] works best on VPS servers with KVM virtualization that have at least 2GB RAM from quality network providers such as Linode, Vultr, DigitalOcean, and other non-AWS networks. The underlying LEMP configuration is meant specifically for single-site WordPress installations, and does not support [Multisite](https://codex.wordpress.org/Create_A_Network) installs. SlickStack [ss] supports WordPress, WooCommerce, bbPress, and BuddyPress "out of the box" with pre-optimized settings that scale.

*Updated October 4, 2018*

* Ubuntu 18.04 - (cloud provider repo)
* Nginx 1.15.0 - custom `nginx.conf` + `server block`
* PHP-FPM 7.2 - custom `php.ini` + `php-fpm.conf` + `www.conf`
* MySQL 5.7 - 
* WordPress (latest version) - optional `wplite` optimized configuration (re: `mu-plugins`)
* WP-CLI 1.5.1
* Redis 4.0.9 - tweak `redis.conf`
* Monit 5.25.1
* Git 2.17.1
* UFW 0.35 - custom firewall rules

Default ports: 80 (HTTP), 443 (HTTPS), 6969 (SSH)

## Installation

Because it’s written purely in [Bash](https://en.wikipedia.org/wiki/Bash_(Unix_shell)) (Unix shell), SlickStack [ss] has no dependencies and works on any Ubuntu Linux machine. Unlike heavier provisioning tools like EasyEngine or Ansible, there are no third party languages required such as Python, meaning a lighter and simpler approach to launching WordPress servers.

The below installation steps presume that you've already spun up a dedicated Ubuntu 18.04 VPS server (KVM) with at least 2GB RAM memory and that you are logged in via SSH.

1. `sudo mkdir /var/www/ && sudo chown root:root /var/www/ && sudo chmod 755 /var/www/`

2. `sudo nano /var/www/ss-config`  [*configure as desired*](http://mirrors.slickstack.io/ss-config-sample.txt)

3. `cd /var/www/ && sudo wget -O ss slick.fyi && sudo chmod 755 ss && sudo bash ss`

From this point forward, you can manage your SlickStack [ss] server by using the `sudo bash` command on any one of the included **ss** scripts located within the `/var/www/` directory as needed. However, in most cases there shouldn't be any need for much management as the server will intelligently run various cron jobs which connect to this GitHub repo (or whichever fork of this repo that your team has setup... be sure to alter all Bash source links). If desired, you can also safely re-install the entire stack using the installation steps above without encountering any conflicts or data loss.

## Structure

After completing the installation steps above, your `/var/www/` directory should look exactly as below. Keep in mind that you should never directly modify the crontab on any SlickStack [ss] server, nor should you modify any files appearing in the below list with the exception of `ss-config` (this does not apply to WordPress files found under `/var/www/html/`) ...

    /var/www/0-crontab
    /var/www/1-cron-often
    /var/www/2-cron-regular
    /var/www/3-cron-hourly
    /var/www/4-cron-daily
    /var/www/5-cron-weekly
    /var/www/6-cron-monthly
    /var/www/7-cron-sometimes
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
    
...and if deploying as `wplite` your `/var/www/html/wp-content/mu-plugins/` directory should look like this:

    /var/www/html/wp-content/mu-plugins/autoloader.php
    /var/www/html/wp-content/mu-plugins/clear-caches/
    /var/www/html/wp-content/mu-plugins/cloudflare/
    /var/www/html/wp-content/mu-plugins/custom-functions/
    /var/www/html/wp-content/mu-plugins/delete-expired-transients/
    /var/www/html/wp-content/mu-plugins/disable-embeds/
    /var/www/html/wp-content/mu-plugins/disable-emojis/
    /var/www/html/wp-content/mu-plugins/disable-empty-trash/
    /var/www/html/wp-content/mu-plugins/disable-gutenberg/
    /var/www/html/wp-content/mu-plugins/disable-image-compression/
    /var/www/html/wp-content/mu-plugins/disable-post-via-email/
    /var/www/html/wp-content/mu-plugins/disable-xml-rpc/
    /var/www/html/wp-content/mu-plugins/error-log-monitor/
    /var/www/html/wp-content/mu-plugins/force-strong-hashing/
    /var/www/html/wp-content/mu-plugins/header-cleanup/
    /var/www/html/wp-content/mu-plugins/index-autoload/
    /var/www/html/wp-content/mu-plugins/limit-heartbeat/
    /var/www/html/wp-content/mu-plugins/plugin-blacklist/
    /var/www/html/wp-content/mu-plugins/remove-query-strings/
    /var/www/html/wp-content/mu-plugins/server-status/
    /var/www/html/wp-content/mu-plugins/sftp-details/
    /var/www/html/wp-content/mu-plugins/virtual-robotstxt/
    /var/www/html/wp-content/mu-plugins/xxx-notices.php

## Philosophy

Outside of the so-called [Application Layer](https://en.wikipedia.org/wiki/Application_layer), so much of the way computers and servers now work has been moved away from in-house teams and specialists and onto "the cloud" that terms like [DevOps](https://www.reddit.com/r/devops/comments/3rpzem/devops_vs_sysadmin/cwqmlnd/) have become standard among recruiters, companies, and developers alike. Modern web development trends have begun to revolve entirely around concepts such as automation, APIs, [cloud services](http://www.lsainsider.com/infographic-63-of-smbs-have-adopted-a-cloud-based-service/archives), and beyond — a phenomenon we might refer to as *Web 3.0*.

While this shift is exciting, there is now a massive and growing disconnect between these emerging technologies and the humans that are expected to implement or benefit from them. Typical small business owners (SMBs), along with independent agencies or freelancers, now face a virtually impossible learning curve if they wish to maintain not only a competitive "webdev" edge, but even to keep up with basic standards in website security, etc.

> "The innovation that this industry talks about so much is bullshit. Anybody can innovate. Don't do this big, 'Think Different' innovation thing. Screw that; it's meaningless. 99% of it is 'Get the work done.' That's my least favorite part of the technology news cycle: the constant innovation and new ideas, 'This will revolutionize,' all that hype – that's not where the real work is. The real work is in the details."
>
> — **Linus Torvalds**

Telling these sorts of people to learn how to use Configuration Management (CM) tools like Ansible — or hire somebody who does — completely misses the point; it's the equivalent of telling someone who doesn't speak Spanish to go study Latin to better prepare for their exam, which happens to be tomorrow. Meanwhile, there's a cheeky student (e.g. Shopify or Wix) across the way, trying to sell them the answers (which look a bit shoddy).

While Silicon Valley "gurus" and corporations pump out new buzzwords (and SaaS services) on a daily basis, the typical small business website is still trying to figure out how to make their contact forms work correctly. The "legacy" shared web hosting monopolies — think EIG or GoDaddy — also have little motivation to education their audience, as perpetuating confusion seems to be a core pillar of their business model.

Before the likes of Google and Amazon and Shopify and Wix take over the entire web and turn it into Wall Street-backed website builders that feed into their private ecosystems, SlickStack hopes to bridge the knowledge gap between emerging technology and old-school web development to empower SMBs to achieve top notch website performance and security by offering a "controlled" LEMP-stack environment with limited options that is perfectly suited to the world's most popular open-source CMS: WordPress.

TL;DR: while "the wheel" might not need re-inventing, it can surely [always be improved](https://www.scientificamerican.com/article/greener-tires/).

--

IDEALISM / SHOWBOATING ####                  GRAND CANYON OF DISCONNECT                 PRAGMATISM / SIMPLICITY ####
###########################                                                             ############################

Typical SMB owner thought process:

* Why is my website loading so slowly?
* Which web hosting company should I choose?
* What cache/security plugin is the best?
* When is my freelancer going to reply?
* @#$%! THIS... BACK TO SHOPIFY!

## Comparison

| ... | SlickStack | Ansible | Puppet | Salt | Chef | EasyEngine | Trellis | AnsiPress | VVV | VCCW | Centminmod | VPSSIM |
|:---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Dependencies | (Unix) | Python | Ruby | Python | Python | Python | Ansible | Ansible | Vagrant | Vagrant | (Unix) | (Unix) |
| Standard Shell Commands | Yes | No | No | No | No | No | No | No | No | No | Yes | Yes |
| WordPress Focus | Yes | No | No | No | No | Yes | Yes | No | Yes | Yes | No | Yes |
| Single Sites Focus | Yes | N/A | No | No | N/A | N/A | No |
| Email APIs | Yes | N/A | N/A | N/A | No | No | No | No | No | No |
| Monitoring App | Monit | N/A | None | None | None | None | None |
|  |  |  |  |  |  |  |  |

Others: Moss.sh, Webinoly, VestaCP, OneInStack, OpenResty, ServerPilot, RunCloud

## FAQ

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

