# NOT READY YET!!! USE AT YOUR OWN RISK!!!

# SlickStack [SS]

> SlickStack is a free LEMP stack automation script written in bash designed to enhance and simplify WordPress provisioning, performance, and security.

&#10142; No email please! Post all questions &amp; comments in our free [Facebook group](https://www.facebook.com/groups/littlebizzy/).

## Table Of Contents

1. [Compatibility](#compatibility)
2. [Installation](#installation)
3. [Structure](#structure)
4. [Philosophy](#philosophy)
5. [Comparison](#comparison)

## Compatibility

SlickStack [SS] works best on VPS servers with KVM virtualization that have at least 2GB RAM from quality network providers such as Vultr, DigitalOcean, Linode, and other non-AWS networks. The underlying LEMP configuration is meant specifically for single-site WordPress installations, and does not support [Multisite](https://codex.wordpress.org/Create_A_Network) installs. SlickStack [SS] supports WordPress, WooCommerce, bbPress, and BuddyPress "out of the box" with pre-optimized settings that scale.

## Installation

Because it’s written purely in [bash](https://en.wikipedia.org/wiki/Bash_(Unix_shell)) (Unix shell), SlickStack [SS] has no dependencies and works on any Ubuntu Linux machine. Unlike heavier provisioning tools like EasyEngine or Ansible, there are no third party languages required such as Python, meaning a lighter and simpler approach to launching WordPress servers.

The below installation steps presume that you've already spun up a dedicated Ubuntu 16.04 VPS server (KVM) with at least 2GB RAM memory and that you are logged in already via SSH.

1. Create the `/var/www/` directory:

`sudo mkdir /var/www/ && sudo chown root:root /var/www/ && sudo chmod 755 /var/www/`

2. Create an [ss-config](http://mirrors.slickstack.io/ss-config-sample.txt) file with desired variables:

`cd /var/www/`

`sudo nano ss-config`

3. Complete the SlickStack [SS] installation:

`sudo wget -O ss slick.fyi && sudo chmod 755 ss && sudo bash ss`

## Structure

After completing the installation steps above, your `/var/www/` directory should look like this:

    /var/www/1-cron-often
    /var/www/2-cron-regular
    /var/www/3-cron-hourly
    /var/www/4-cron-daily
    /var/www/5-cron-weekly
    /var/www/6-cron-monthly
    /var/www/7-cron-sometimes
    /var/www/html/
    /var/www/ss-check
    /var/www/ss-clean
    /var/www/ss-config
    /var/www/ss-config-sample
    /var/www/ss-install
    /var/www/ss-muplugs
    /var/www/ss-perms
    /var/www/ss-update
    /var/www/ss-worker
    
...and your `/var/www/html/` directory should look like this:    
    
    /var/www/html/wp-admin/
    /var/www/html/wp-includes/
    /var/www/html/wp-content/
    /var/www/html/wp-...

## Philosophy

Outside of the so-called [Application Layer](https://en.wikipedia.org/wiki/Application_layer), so much of the way computers and servers now work has been moved away from in-house teams and specialists and onto "the cloud" that terms like [DevOps](https://www.reddit.com/r/devops/comments/3rpzem/devops_vs_sysadmin/cwqmlnd/) have become standard among recruiters, companies, and developers alike. Modern web development trends have begun to revolve entirely around concepts such as automation, APIs, [cloud services](http://www.lsainsider.com/infographic-63-of-smbs-have-adopted-a-cloud-based-service/archives), and beyond — a phenomenon we might refer to as *Web 3.0*.

While this shift is exciting, there is now a massive and growing disconnect between these emerging technologies and the humans that are expected to implement or benefit from them. Typical small business owners (SMBs), along with independent agencies or freelancers, now face a virtually impossible learning curve if they wish to maintain not only a competitive "webdev" edge, but even to keep up with basic standards in website security, etc.

Telling these sorts of people to learn how to use Configuration Management (CM) tools like Ansible — or hire somebody who does — completely misses the point; it's the equivalent of telling someone who doesn't speak Spanish to go study Latin to better prepare for their exam, which happens to be tomorrow. Meanwhile, there's a cheeky student (e.g. Shopify or Wix) waving at them from across the way, trying to sell them the answers (which look a bit shoddy).


IDEALISM / SHOWBOATING ####                  GRAND CANYON OF DISCONNECT                 PRAGMATISM / SIMPLICITY ####
###########################                                                             ############################

Typical SMB owner thought process:

* Why is my website loading so slowly?
* Which web hosting company should I choose?
* What cache/security plugin is the best?
* When is my freelancer going to reply?
* @#$%! THIS... BACK TO SHOPIFY!

TL;DR: while "the wheel" might not need re-inventing, it surely [can be improved](https://www.scientificamerican.com/article/greener-tires/).

That is, while Silicon Valley bigwigs and corporate players pump out new terms and services on a daily basis -- like Configuration Management (CM) -- the typical small business website is still trying to figure out how to make their contact forms work correctly. The "old guard" shared web hosting monopoly -- think EIG/GoDaddy -- also has little motivation to education their audience, as perpetuating confusion seems to be a core pillar of their business model.

Before the likes of Google and Amazon and Shopify and Wix take over the entire web and turn it into Wall Street-backed website builders that feed into their private ecosystems, SlickStack hopes to bridge the knowledge gap between emerging technology and old-school web development to empower SMBs to achieve top notch website performance and security by offering a "controlled" LEMP-stack environment with limited options that is perfectly suited to the world's most popular open-source CMS: WordPress.

## Instructions

Much of modern computing history can be traced back to one thing: [Unix](https://en.wikipedia.org/wiki/Unix). Indeed, one of the only things about web servers tbat hasn't changed in several decades is the shell (bash) language. Keeping the same pragmatism and simplicity in mind that inspired LittleBizzy's managed hosting approach, SlickStack [SS] is coded entirely in bash.

While there are [clear benefits](https://medium.com/capital-one-developers/bashing-the-bash-replacing-shell-scripts-with-python-d8d201bc0989) to programming languages like Python or Ruby, provisioning a server with WordPress isn't very complicated, and every Linux server comes with [shell built into it](https://www.infoworld.com/article/2893519/linux/perl-python-ruby-are-nice-bash-is-where-its-at.html). Besides, let's not forget [what happens](https://discourse.roots.io/t/updated-to-ansible-2-4-deploys-broken-now-what/10588) to amateurs when relying on dependencies like Ansible... yikes!

After you've installed SlickStack [SS] using the one-line command mentioned above, proceed as follows:

1. Manually edit the ss-config file.

`sudo nano /var/www/ss-config`

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

## FAQ

* Why don't use you env-vars? Teams who use GitHub repos to develop their sites (e.g. dev/stage/production branches) have started using env-vars with systems like Roots Trellis so that their entire codebase can be safely open-source. However, this requires defining the env-vars within the server stack (hidden from public root) so it's not so friendly for many agencies. Since our goal is to support "typical" agencies with mostly frontend knowledge, we felt that reflecting the WordPress setup was a simpler approach and so if using SlickStack there will be two config files (ss-config and wp-config.php)

* But shouldn't configs be outside the public root? There's lots of "should" and theorizing in computer programming, but one reason for the success of WordPress is its pragmatism and user-friendliness. There's plenty to criticize about WordPress from the perspective of things like "12-factor apps" but let's remember that WordPress was around before many of these guidelines and even after better approaches have been founded, WordPress is still the king of CMS software for a reason.
