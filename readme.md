# SlickStack (Beta)

SlickStack is a free LEMP stack automation script written in Bash designed to enhance and simplify WordPress provisioning, performance, and security.

## Thank you to our sponsors!

[backamblock](https://github.com/backamblock), [yamanmucahit](https://github.com/yamanmucahit), [maxalerator](https://github.com/maxalerator), [konkova](https://github.com/konkova), [HDBear](https://github.com/HDBear), [Politicalite](https://github.com/politicalite), [@liwernyap](https://github.com/liwernyap), [vivdev](https://github.com/vivdev), [hamzah](https://github.com/hamzah), [gingibash](https://github.com/gingibash), [damiafaw](https://github.com/damiafaw), [trevplaig](https://github.com/trevplaig), [hargums](https://github.com/hargums), [volneanschi](https://github/volneanschi)

[**Become a sponsor**](https://github.com/sponsors/jessuppi) and receive access to our private Discord server and changelog on the **#announce** channel. Your donations and public display of support for SlickStack are what keep this project going. Thank you very much!

## Core Modules

*Last updated: Feb 22, 2023*

| LEMP Module | Mirrors | Version | What does SlickStack optimize? |
| :------------- | :----------: | :----------: | :----------: |
| **Ubuntu LTS** | [mirrors](https://mirrors.slickstack.io/modules/ubuntu/) | 22.04 | `crontab` + `gai.conf` + `sshd_config` + `sudoers` + `sysctl.conf` |
| **Nginx** | [mirrors](https://mirrors.slickstack.io/modules/nginx/) | 1.18.x | `nginx.conf` + server blocks |
| **OpenSSL** | [mirrors](https://mirrors.slickstack.io/modules/openssl/) | 3.0.x | `slickstack.crt` + `slickstack.key` + `dhparam.pem` |
| **Lets Encrypt** | [mirrors](https://mirrors.slickstack.io/modules/letsencrypt/) | 1.21.x | `cert.perm` + `chain.pem` + `fullchain.pem` + `privkey.pem` |
| **MySQL** | [mirrors](https://mirrors.slickstack.io/modules/mysql/) | 8.0.x | `my.cnf` |
| **PHP-FPM** | [mirrors](https://mirrors.slickstack.io/modules/php-fpm/) | 8.1.x | `php.ini` + `php-fpm.conf` + `www.conf` |
| **Redis** | [mirrors](https://mirrors.slickstack.io/modules/redis/) | 6.0.x | `redis.conf` + `object-cache.php` |
| **WordPress** | [mirrors](https://mirrors.slickstack.io/modules/wordpress/) | 6.1.x | some WP Core junk files are removed by `ss-clean` |
| **WP-CLI** | [mirrors](https://mirrors.slickstack.io/modules/wordpress/wp-cli/) | 2.7.x | some commands are disabled |
| **Adminer** | [mirrors](https://mirrors.slickstack.io/modules/adminer/) | 4.8.1 | default config |
| **Git** | [mirrors](https://mirrors.slickstack.io/modules/git/) | 2.34.x | default config |
| **UFW Firewall** | [mirrors](https://mirrors.slickstack.io/modules/ufw-firewall/) | 0.36.x | `ufw` + `ufw.conf` + `user-rules` |

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
