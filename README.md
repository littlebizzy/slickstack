# SlickStack (NOT READY YET)

SlickStack is a free collection of LEMP server automation scripts written in bash designed to enhance and simplify WordPress performance and security.

1. [Installation](https://github.com/littlebizzy/slickstack#install)
2. [Introduction](https://github.com/littlebizzy/slickstack#intro)
3. [The Basics](https://github.com/littlebizzy/slickstack#basics)
4. [Comparison](https://github.com/littlebizzy/slickstack#compare)

## Compatibility

SlickStack works best on VPS servers with KVM virtualization that have at least 2GB RAM and 2 CPU cores from cloud providers such as Vultr, DigitalOcean, Linode, and other non-AWS networks. The underlying LEMP configuration is meant specifically for single-site WordPress installations, and does not support Multi-Site installs. SlickStack supports WordPress, WooCommerce, bbPress, and BuddyPress "out of the box" with pre-optimized LEMP stack configurations that scale.

## Installation

Because it’s written purely in [bash](https://en.wikipedia.org/wiki/Bash_(Unix_shell)), SlickStack has no dependencies and works “out of the box” on any Ubuntu machine. Unlike provisioning tools like EasyEngine or Ansible, there is no third party language used such as Python.


`sudo wget mirrors.littlebizzy.com/slickstack/installer && sudo bash ss`

## Introduction

Outside of the so-called [Application Layer](https://en.wikipedia.org/wiki/Application_layer), so much of the way computers and servers now work has been moved away from in-house teams and specialists and onto "the cloud" that terms like [DevOps](https://www.reddit.com/r/devops/comments/3rpzem/devops_vs_sysadmin/cwqmlnd/) have become standard among recruiters, companies, and developers alike. Modern web development trends have begun to revolve entirely around concepts such as automation, APIs, [cloud services](http://www.lsainsider.com/infographic-63-of-smbs-have-adopted-a-cloud-based-service/archives), and beyond — a phenomemon we might refer to as *Web 3.0*.

While this shift is exciting, there is now a massive and growing disconnect between these emerging technologies and the humans that are expected to implement or benefit from them. Typical small business owners (SMBs), along with independent agencies or freelancers, now face a virtually impossible learning curve if they wish to maintain not only a competitive "webdev" edge, but even to keep up with basic standards in website security, etc.

Telling these sorts of people to learn how to use Configuration Management (CM) tools like Ansible — or hire somebody who does — completely misses the point; it's the equivalent of telling someone who doesn't speak Spanish to go study Latin to better prepare for their exam, which happens to be tomorrow. Meanwhile, there's a cheeky student (e.g. Shopify) waving at them from across the room trying to sell them the answers for a low monthly fee.


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

## The Basics

Much of modern computing history can be traced back to one thing: [Unix](https://en.wikipedia.org/wiki/Unix). Indeed, one of the only things about web servers tbat hasn't changed in several decades is the shell (bash) language. Keeping the same pragmatism and simplicity in mind that inspired LittleBizzy's managed hosting approach, SlickStack is coded entirely in bash. While there are certainly arguments to be made about [the benefits](https://medium.com/capital-one-developers/bashing-the-bash-replacing-shell-scripts-with-python-d8d201bc0989) of programming languages such as Python or Ruby, the truth is that provisioning a server with WordPress isn't very complicated, and every server always comes with [shell built into it](https://www.infoworld.com/article/2893519/linux/perl-python-ruby-are-nice-bash-is-where-its-at.html).

## Comparison

| ... | SlickStack | Ansible | Puppet | Salt | Chef | EasyEngine | Trellis | AnsiPress | Centminmod | VPSSIM |
|:---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Dependencies | (Unix) | Python | Ruby | Python | Python | Python | Ansible | Ansible | (Unix) | (Unix) |
| Standard Shell Commands | Yes | No | No | No | No | No | No | No | Yes | Yes |
| WordPress Focus | Yes | No | No | No | No | Yes | Yes | No | No | Yes |
| Single Sites Focus | Yes | N/A | No | No | N/A | N/A | No |
| Email APIs | Yes | N/A | N/A | N/A | No | No | No | No | No | No |
| Monitoring App | Monit | N/A | None | None | None | None | None |
|  |  |  |  |  |  |  |  |
