# Roadmap

SlickStack's roadmap is intentionally narrow. The project is not trying to become a general hosting platform, control panel, or enterprise orchestration system. Future work should make the existing single-server WordPress model safer, clearer, easier to recover, and more useful for serious dynamic sites.

This roadmap is directional rather than a promise of dates or specific releases. Priorities may change when reliability, security, upstream compatibility, or maintenance burden requires it.

## Table of Contents

- [Roadmap principles](#roadmap-principles)
- [Current priorities](#current-priorities)
- [Next priorities](#next-priorities)
- [Later possibilities](#later-possibilities)
- [WordPress and WooCommerce awareness](#wordpress-and-woocommerce-awareness)
- [Agency and automation support](#agency-and-automation-support)
- [Version support](#version-support)
- [Not planned](#not-planned)
- [How roadmap items are evaluated](#how-roadmap-items-are-evaluated)
- [Related guides](#related-guides)

## Roadmap principles

Roadmap work should generally improve one or more of these outcomes:

- safer installation, updates, and reconciliation
- faster diagnosis of production problems
- more reliable backups, restoration, and server replacement
- clearer supported boundaries and administrator responsibilities
- better compatibility with current Ubuntu, PHP, MySQL, Nginx, and WordPress releases
- predictable operation for WooCommerce and other dynamic WordPress workloads
- easier management of separate SlickStack servers without creating a proprietary control plane
- lower maintenance burden for both users and project maintainers

New features should not be accepted merely because another hosting product includes them.

## Current priorities

The immediate focus is the reliability and clarity of the existing stack.

### Safer reconciliation

- favor narrow component installers when a full `ss-install` is unnecessary
- improve preflight validation before package, configuration, database, or filesystem changes
- make destructive or disruptive side effects clearer before execution
- preserve useful evidence during failures instead of immediately truncating logs or replacing state
- validate generated configuration before service reloads and restarts
- improve post-operation checks for Nginx, PHP-FPM, MySQL, Memcached, cron, and WordPress
- continue separating administrator policy from values that can be tuned safely from server resources

### Documentation accuracy

- keep documentation aligned with the current scripts and generated configuration
- document managed-file boundaries and overwrite behavior
- clearly distinguish narrow maintenance commands from broad reconciliation operations
- remove stale instructions and unsupported assumptions
- keep architecture, scope, security, backup, and recovery guidance consistent

### Simplification

- remove unreliable, redundant, or low-value components where simpler controls are adequate
- avoid retaining legacy behavior solely to preserve theoretical flexibility
- prefer explicit supported paths over arbitrary configuration discovery
- reduce options when automatic behavior is safer and easier to maintain

## Next priorities

These are the strongest candidates after current reliability and documentation work.

### Diagnostics and health reporting

Improve the existing overview and monitoring tools toward a clearer diagnostic report covering:

- failed or inactive services
- CPU, memory, swap, disk, and inode pressure
- Nginx and PHP-FPM configuration validity
- MySQL and Memcached connectivity
- pending reboots and package state
- certificate expiration
- cron execution timestamps and stale scheduled tasks
- local and off-server backup age
- WordPress maintenance mode and Core state
- cache and permission inconsistencies

Diagnostic output should be useful interactively and safe to redact for support discussions.

### Backup and recovery confidence

- verify that backups are recent, readable, and transferred successfully
- make restoration to a replacement virtual machine easier to understand and test
- preserve copies of important generated configuration before broad reconciliation
- improve documentation for complete server loss, migration, and rollback
- distinguish clearly between authoritative data and rebuildable server state
- encourage periodic restore testing rather than treating backup creation as sufficient

### Safer updates

- improve visibility into which SlickStack, Ubuntu, PHP, MySQL, WordPress, and plugin components will change
- consider clearer stable and development update expectations
- reduce surprise changes caused by broad compound commands
- make compatibility failures stop early with actionable explanations
- improve narrow recovery after interrupted updates

## Later possibilities

These ideas may be useful but should remain secondary to reliability.

- machine-readable health or status output for external tools
- optional outbound alerts through administrator-selected services
- improved server manifests describing versions, features, and important paths
- better migration checks between supported Ubuntu LTS releases
- more provider-neutral examples for backups, monitoring, and DNS
- clearer support for agencies operating multiple independent SlickStack servers
- carefully selected additions to staging safeguards and operational reporting

Later possibilities should not create a required SlickStack cloud account or centralized service.

## WordPress and WooCommerce awareness

SlickStack should remain a WordPress server stack rather than becoming a WooCommerce application product. However, important dynamic workloads deserve better operational awareness.

Useful directions include:

- verifying that cart, checkout, account, administration, REST API, and webhook traffic bypasses page caching correctly
- identifying delayed WP-Cron execution
- reporting unusually large Action Scheduler queues when WooCommerce or another plugin uses them
- warning about PHP worker exhaustion, database pressure, disk exhaustion, or stale maintenance mode
- improving staging safeguards against customer email, payment, webhook, and indexing mistakes
- documenting object-cache, page-cache, and background-job behavior for dynamic sites
- keeping transactional email and external payment processing outside the server stack

SlickStack should report infrastructure conditions without pretending to manage products, orders, taxes, subscriptions, refunds, fulfillment, or payment compliance.

## Agency and automation support

The one-primary-domain-per-server architecture should remain unchanged. Agency support should focus on making separate servers easier to inspect and automate.

Potential improvements include:

- consistent command exit codes
- noninteractive status and validation commands
- machine-readable summaries
- stable server identifiers and manifests
- documented SSH-based inventory patterns
- external monitoring and alert integrations
- predictable backup naming and retention behavior

SlickStack should integrate with third-party management tools rather than building a proprietary fleet dashboard.

## Version support

The project should increasingly distinguish between:

| Tier | Meaning |
| :-- | :-- |
| **Recommended** | Best-tested combination for new installations |
| **Supported** | Expected to work and maintained within the normal compatibility surface |
| **Legacy** | Retained mainly for existing installations or migration, but discouraged for new servers |
| **Unsupported** | Outside the tested architecture and expected to stop with a clear error |

Broad compatibility is useful only when it does not hide obsolete or risky deployment choices. Documentation should make the preferred path obvious while allowing responsible migration from older servers.

## Not planned

The following directions are outside the roadmap:

- browser-based hosting control panel
- shared hosting or reseller billing
- multiple unrelated primary domains on one server
- Shopify, Wix, or Squarespace-style site building
- generic hosting for arbitrary application frameworks
- Docker or Kubernetes orchestration
- clustered Nginx, PHP-FPM, Memcached, or MySQL
- automatic horizontal scaling or regional failover
- zero-downtime application deployment platform
- proprietary monitoring or fleet-management cloud
- bundled email hosting, DNS management, or domain registration
- complete malware scanning, SIEM, or compliance platform
- AI-generated server administration
- plugin marketplace or SaaS app ecosystem

Headless WordPress documentation may improve, but SlickStack will not become a frontend framework, Node.js host, or enterprise composable-commerce platform.

## How roadmap items are evaluated

A proposed change should answer these questions:

1. Does it solve a recurring problem for the intended SlickStack user?
2. Does it fit the single-server WordPress architecture?
3. Can it be maintained without creating excessive configuration or support burden?
4. Does it improve reliability, recovery, portability, or operational clarity?
5. Can administrators understand what it changes and how to reverse it?
6. Is an external provider or existing WordPress tool already better suited to the job?
7. Does the benefit justify expanding the supported surface?

Features that mainly imitate competitors, follow infrastructure fashion, or increase lock-in should normally be rejected.

## Related guides

- [Scope](scope.md)
- [Architecture](architecture.md)
- [Installation](installation.md)
- [Updates](updates.md)
- [Monitoring](monitoring.md)
- [Troubleshooting](troubleshooting.md)
- [Backups](backups.md)
- [Migrations](migrations.md)
- [WordPress](wordpress.md)
