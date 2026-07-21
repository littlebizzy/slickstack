# Scope

SlickStack is an opinionated, open-source server stack for running one primary WordPress site on one Ubuntu LTS virtual machine. It uses Bash automation to install, configure, maintain, and reconcile a predictable LEMP environment without a hosting control panel, containers, or a proprietary cloud platform.

Its purpose is not to make self-hosting effortless for everyone. SlickStack exists for technical site owners, developers, and small agencies that need more control than hosted website builders or managed WordPress plans provide, but do not need enterprise infrastructure.

## Table of Contents

- [Core use case](#core-use-case)
- [Intended users](#intended-users)
- [Suitable workloads](#suitable-workloads)
- [Poor-fit workloads](#poor-fit-workloads)
- [Managed boundaries](#managed-boundaries)
- [External responsibilities](#external-responsibilities)
- [Design principles](#design-principles)
- [Deliberate non-goals](#deliberate-non-goals)
- [Choosing SlickStack](#choosing-slickstack)
- [Related guides](#related-guides)

## Core use case

The standard SlickStack deployment is:

```text
one primary domain
one Ubuntu LTS virtual machine
one WordPress production site
optional staging and development environments
one local Nginx, PHP-FPM, MySQL, and Memcached stack
```

SlickStack is designed to provide managed-hosting discipline on infrastructure controlled by the administrator. The server should remain understandable, reproducible, portable between ordinary KVM providers, and recoverable without depending on a proprietary hosting dashboard.

## Intended users

SlickStack is primarily intended for:

- WordPress developers comfortable with SSH and Linux administration
- small agencies running important client sites on separate virtual machines
- technical founders operating WordPress-based products or communities
- WooCommerce stores with custom plugins, workflows, or performance requirements
- publishers, memberships, forums, directories, and other dynamic WordPress sites
- site owners leaving restrictive or expensive managed WordPress hosting
- administrators who prefer explicit configuration and command-line tooling over control panels

SlickStack is not aimed at beginners who want a visual website builder, bundled support, or a fully managed service with no server responsibility.

## Suitable workloads

SlickStack is a reasonable fit when a WordPress site benefits from:

- source-level control over WordPress, plugins, themes, and server configuration
- predictable Nginx, PHP-FPM, MySQL, Memcached, cron, and permission behavior
- custom WooCommerce or membership business logic
- staging and development copies on the same server
- provider portability and ordinary off-server backups
- direct access to logs, WP-CLI, database tools, and operating-system services
- a simple single-server architecture instead of clustered enterprise infrastructure

A site does not need to be large to justify SlickStack, but it should be important enough that its administrator accepts responsibility for updates, backups, monitoring, and recovery.

## Poor-fit workloads

SlickStack is usually the wrong choice for:

- users who want Wix, Squarespace, Shopify, or similar fully hosted products
- simple stores whose needs are completely covered by a hosted commerce platform
- shared hosting or reseller hosting with many unrelated customer domains
- general-purpose PHP, Node.js, Python, or container hosting
- applications requiring automatic horizontal scaling or zero-downtime deployment
- systems requiring clustered databases, automatic failover, or regional redundancy
- teams expecting a browser-based hosting panel and vendor support desk
- organizations requiring a certified compliance platform or complete security operations service

Using a simpler hosted product is often the better decision when it satisfies the business requirements without meaningful loss of control or flexibility.

## Managed boundaries

SlickStack manages the WordPress origin server and its generated configuration, including:

- Ubuntu packages and selected system configuration
- Nginx and WordPress request routing
- PHP-FPM and PHP extensions
- local or configured remote MySQL connectivity
- Memcached and the production WordPress object cache
- WordPress Core, WP-CLI, selected MU plugins, and generated `wp-config.php` files
- root cron wrappers and scheduled maintenance tasks
- local backups and configured Rclone or Rsync transfers
- filesystem ownership and permissions
- Iptables rules, certificates, logs, caches, and selected service recovery
- production, staging, and development environment synchronization

SlickStack intentionally limits configuration choices where a smaller supported surface produces safer and more predictable servers.

## External responsibilities

The administrator remains responsible for systems outside the SlickStack origin, including:

- domain registration and authoritative DNS
- cloud-provider accounts, virtual machines, snapshots, and network firewalls
- Cloudflare account settings, WAF rules, bot controls, and billing
- off-server backup accounts, retention policies, and restore testing
- transactional email services and mailbox hosting
- external uptime monitoring and alert delivery
- WordPress users, roles, content, plugins, themes, and custom application code
- payment gateways, taxes, privacy obligations, and commerce operations
- security incident response, malware investigation, and business continuity planning

SlickStack can integrate with external services, but it should not attempt to replace every service surrounding a website.

## Design principles

SlickStack favors:

- one important WordPress site per virtual machine
- explicit generated configuration instead of a hosting control panel
- Bash and standard Linux services instead of an additional orchestration runtime
- narrow, understandable components over open-ended extensibility
- reliable defaults over endless configuration choices
- rebuildable infrastructure and clearly identified authoritative data
- provider-neutral servers and backups where practical
- compatibility with external monitoring, DNS, CDN, email, and backup providers
- honest documentation of operational risks and unsupported architectures
- incremental maintenance instead of fashionable platform expansion

Performance matters, but predictable maintenance and recovery are more important than benchmark marketing.

## Deliberate non-goals

SlickStack does not aim to become:

- a managed hosting company
- a browser-based hosting control panel
- a shared-hosting or reseller platform
- a multi-tenant system for unrelated primary domains
- a Shopify, Wix, or Squarespace-style site builder
- a generic application platform for multiple languages and frameworks
- a Docker or Kubernetes orchestration layer
- a clustered database or automatic failover system
- an automatic horizontal-scaling platform
- a proprietary fleet-management cloud
- a billing, customer-support, or agency CRM product
- a complete observability, SIEM, or malware-scanning service
- an email server or DNS management platform
- an AI-generated server configuration product

Headless WordPress can be supported as an application pattern, but SlickStack remains responsible only for the WordPress origin and does not become a frontend hosting platform.

## Choosing SlickStack

Choose SlickStack when the site is too customized, operationally important, or constrained by hosted platforms, and when someone is prepared to administer a Linux server responsibly.

Choose managed WordPress hosting when WordPress flexibility is required but server administration is not desirable.

Choose Shopify, Wix, Squarespace, or another hosted builder when that product already satisfies the business requirements and infrastructure ownership would add cost without meaningful value.

Choose enterprise infrastructure when the system genuinely requires clustering, automatic failover, regional redundancy, formal deployment pipelines, or specialized operations staff.

SlickStack occupies the middle ground: more control than hosted platforms, less complexity than enterprise DevOps.

## Related guides

- [Architecture](architecture.md)
- [Installation](installation.md)
- [Roadmap](roadmap.md)
- [Security](security.md)
- [Backups](backups.md)
- [Monitoring](monitoring.md)
- [WordPress](wordpress.md)
- [Headless](headless.md)
