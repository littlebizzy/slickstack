# SlickStack

## Compatibility

SlickStack works best on VPS servers with KVM virtualization that have at least 2GB RAM and 2 CPU cores from cloud providers such as Vultr, DigitalOcean, Linode, and other non-AWS networks. The underlying LEMP configuration is meant specifically for single-site WordPress installations, and does not support Multi-Site installs.

## Introduction

Outside of the so-called [Application Layer](https://en.wikipedia.org/wiki/Application_layer), so much of the way computers and servers now work has been moved away from in-house teams and specialists and onto "the cloud" that terms like [DevOps](https://www.reddit.com/r/devops/comments/3rpzem/devops_vs_sysadmin/cwqmlnd/) have become standard among recruiters, companies, and developers alike. Modern web development trends have begun to revolve entirely around concepts such as automation, APIs, cloud services, and beyond — a phenomemon we might refer to as *Web 3.0*.

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

Much of modern computing history can be traced back to one thing: [Unix](https://en.wikipedia.org/wiki/Unix). Indeed, one of the only things about web servers tbat hasn't changed in several decades is the shell (bash) language. Keeping long-term support and simplicity in mind, SlickStack is coded entirely in bash scripts.
