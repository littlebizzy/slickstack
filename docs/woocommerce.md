# WooCommerce

SlickStack can host WooCommerce stores that need direct control over WordPress, plugins, server resources, caching, background jobs, backups, and operational troubleshooting.

SlickStack manages the WordPress origin and LEMP stack. WooCommerce remains responsible for store data and application behavior, while payment gateways, transactional email, taxes, shipping providers, fraud controls, fulfillment, and compliance remain external responsibilities.

## Table of Contents

- [Recommended baseline](#recommended-baseline)
- [Caching and customer sessions](#caching-and-customer-sessions)
- [WP-Cron and Action Scheduler](#wp-cron-and-action-scheduler)
- [High-Performance Order Storage](#high-performance-order-storage)
- [Staging and development](#staging-and-development)
- [Email](#email)
- [WooCommerce logs](#woocommerce-logs)
- [Payments and webhooks](#payments-and-webhooks)
- [Backups and migrations](#backups-and-migrations)
- [Headless WooCommerce](#headless-woocommerce)
- [Troubleshooting](#troubleshooting)
- [Scope](#scope)
- [Related guides](#related-guides)

## Recommended baseline

WooCommerce can run on the same standard SlickStack architecture as other WordPress sites:

```text
Cloudflare or public DNS
        ↓
Nginx
        ↓
PHP-FPM
        ↓
WordPress + WooCommerce
        ↓
MySQL and Memcached
```

At least 2GB RAM is recommended for WooCommerce and other dynamic WordPress workloads. Larger catalogs, subscription stores, import jobs, high checkout traffic, extensive extensions, or busy administration may require more PHP workers, database capacity, disk space, and memory.

The useful capacity signals are not only page-load benchmarks. Monitor:

- PHP-FPM worker exhaustion and request timeouts
- MySQL latency, connections, and disk pressure
- memory and swap pressure
- Action Scheduler and WP-Cron delays
- disk space and inode usage
- failed webhooks and payment callbacks
- transactional email delivery
- backup age and restoration confidence

SlickStack does not automatically size a server from product count or revenue. Test the real catalog, plugins, checkout flow, imports, scheduled jobs, and expected concurrency.

## Caching and customer sessions

WooCommerce mixes cacheable public pages with customer-specific cart, account, checkout, and order state. Incorrect page caching can expose stale or incorrect customer data.

The standard SlickStack production Nginx configuration bypasses FastCGI cache for important dynamic requests, including:

- POST requests
- requests containing query strings
- WooCommerce AJAX requests using `wc-ajax`
- account, cart, checkout, order, and related sensitive paths
- logged-in WordPress users
- WooCommerce cart cookies
- WooCommerce session cookies
- WordPress REST API requests under `/wp-json/`

WooCommerce session data is also included in the FastCGI cache key when detected.

These protections are pattern-based. They cannot predict every custom plugin, translated route, renamed endpoint, membership flow, payment callback, or proprietary API. Confirm that custom customer-specific routes bypass page caching before launch.

The WordPress object cache is separate from FastCGI page caching. Memcached can reduce repeated database work, but plugins must use WordPress object-cache APIs correctly. Clearing Memcached does not clear Nginx, browser, frontend-platform, or Cloudflare caches.

Avoid broad cache purges during traffic spikes unless necessary. A complete purge can cause many simultaneous PHP and MySQL requests while caches rebuild.

## WP-Cron and Action Scheduler

SlickStack disables request-triggered WP-Cron and runs due WordPress events through a root cron wrapper. The default `often` interval runs every two minutes.

WooCommerce and its extensions commonly use WP-Cron or Action Scheduler for:

- queued and scheduled email
- subscription renewals
- inventory synchronization
- webhook retries
- payment follow-up work
- imports and exports
- analytics processing
- cleanup tasks

A healthy web server does not guarantee that background work is current. When WooCommerce appears delayed, inspect the scheduled-action queue, failed actions, WordPress cron state, PHP errors, and `/var/www/logs/wp-cli.log`.

Useful WP-CLI checks may include:

```bash
sudo -u www-data wp --path=/var/www/html cron event list
sudo -u www-data wp --path=/var/www/html action-scheduler status
sudo -u www-data wp --path=/var/www/html action-scheduler action list --status=failed
```

The Action Scheduler commands are available only when the installed WooCommerce or plugin version provides them.

Do not repeatedly run every pending action without first identifying why the queue grew. Large queues can reflect external API failures, payment problems, email transport failures, PHP timeouts, database pressure, or a broken plugin callback.

## High-Performance Order Storage

WooCommerce High-Performance Order Storage, commonly called HPOS, stores orders in dedicated WooCommerce order tables instead of relying only on the traditional WordPress posts and post-meta tables.

HPOS is an application-level WooCommerce feature rather than a SlickStack server option. SlickStack installs and manages MySQL normally, while WooCommerce controls whether HPOS is enabled, whether compatibility synchronization is active, and which order datastore is authoritative.

Before enabling or changing HPOS on an established store:

- confirm that every payment, subscription, fulfillment, reporting, export, accounting, and order-management extension declares HPOS compatibility
- create and verify a fresh database backup
- review the WooCommerce HPOS synchronization status
- allow any required table synchronization to finish before changing the authoritative datastore
- test order creation, payment callbacks, refunds, administration, reports, webhooks, and scheduled actions
- avoid custom SQL or integrations that assume every order remains in `wp_posts` and `wp_postmeta`

A normal SlickStack database dump includes the dedicated WooCommerce order tables because it exports the complete configured WordPress database. Restoration still requires a compatible WooCommerce version and extension set, not merely the presence of the tables.

Staging copies can contain HPOS order data, compatibility tables, live credentials, and background tasks. Treat HPOS migrations and synchronization changes as production database operations rather than harmless WooCommerce settings.

## Staging and development

SlickStack staging and development environments require special care for stores because copied production databases can contain:

- customer accounts and personal data
- orders and order notes
- subscriptions and renewal schedules
- payment and API credentials
- webhook destinations
- transactional email settings
- analytics identifiers
- inventory and fulfillment integrations

Production-to-staging synchronization replaces the staging database. Do not keep unique orders, settings, or other important state only in staging.

SlickStack installs safeguards that disable normal WordPress email and the default Action Scheduler runner in staging and development. These protections reduce accidental customer contact and background processing, but they do not guarantee complete isolation.

Custom code and plugins can still:

- call external APIs directly
- send through a provider-specific API outside `wp_mail()`
- contact production webhook endpoints
- use live payment credentials
- modify shared external inventory or fulfillment systems

Use gateway sandbox modes, test provider accounts, non-customer data, and restricted webhook destinations. Review credentials after every production-to-test synchronization.

Staging also serves media from the production uploads directory. Avoid destructive media cleanup, attachment deletion, filename migration, or untrusted upload tools in staging.

## Email

WooCommerce can generate order, account, refund, subscription, and administrative messages, but SlickStack does not provide an email server or transactional delivery service.

Use one maintained SMTP or provider API integration with a dedicated transactional provider. Configure valid sender-domain authentication and verify important WooCommerce message paths instead of relying only on a generic test email.

When troubleshooting, separate these stages:

1. WooCommerce generated the event.
2. The message was created or queued.
3. WP-Cron or Action Scheduler processed the job.
4. WordPress passed the message to the configured transport.
5. The provider accepted the message.
6. The destination delivered, rejected, deferred, quarantined, or classified it as spam.

Provider activity logs are normally more useful than looking for a local mail queue because SlickStack does not install a standard production mail transfer agent.

## WooCommerce logs

WooCommerce application logs are separate from SlickStack's Nginx, PHP, WP-CLI, MySQL, and systemd logs. Review them in WordPress under:

```text
WooCommerce → Status → Logs
```

Depending on the active WooCommerce logging configuration and version, logs can be stored as files—typically beneath `wp-content/uploads/wc-logs/`—or in WooCommerce database tables. The WooCommerce interface is the safest starting point because it identifies the active source, severity, timestamp, and storage handler.

WooCommerce logs can include information from:

- payment gateways
- webhooks and callback processing
- Action Scheduler jobs
- imports, exports, and background tasks
- subscriptions and other extensions
- fatal errors and application-specific diagnostics

A gateway or extension may also keep its own logging switch, provider dashboard, or external event history. Enabling WooCommerce logging does not guarantee that every integration records the same events or level of detail.

Treat these logs as sensitive. They can contain order identifiers, customer information, request data, callback URLs, provider responses, and partial credential or token details. Review retention and severity settings, avoid leaving verbose debug logging enabled indefinitely, and redact logs before sharing them.

SlickStack staging normally shares the production uploads directory. When WooCommerce uses file-based logs beneath uploads, production and staging can therefore write into the same `wc-logs` tree. Use database logging or another deliberately isolated strategy when separate test-environment logs are required.

See [Logging](logging.md) for the server-level logs that should be checked alongside WooCommerce application logs.

## Payments and webhooks

Payment gateways and webhook integrations run inside or alongside WooCommerce, but their accounts, credentials, compatibility, compliance, and external availability are not managed by SlickStack.

Before launch, verify:

- HTTPS and the public callback domain
- live versus sandbox credentials
- gateway support for the chosen checkout implementation
- successful and failed payment flows
- delayed and asynchronous payment confirmation
- refunds and cancellations
- webhook signature validation
- retries, duplicate events, and idempotent handling
- firewall, Cloudflare, and bot-protection rules that could block callbacks
- order state after a timeout or interrupted browser session

Do not assume that a successful browser redirect proves the gateway callback or webhook completed. Review both WooCommerce logs and the provider dashboard.

Test environments must not retain live payment credentials or production webhook targets unless the provider account is explicitly restricted from real charges and production actions.

## Backups and migrations

The production database is authoritative for products, customers, orders, coupons, settings, and most plugin state. File backups are also required for uploads, plugins, themes, custom integrations, and protected configuration files.

A complete store recovery plan should account for:

- the WordPress database
- `wp-content` and uploads
- custom code and plugin files
- external payment and email accounts
- API keys and webhook secrets
- DNS and Cloudflare configuration
- object-storage or media-offload state
- fulfillment, tax, shipping, analytics, and search services
- provider-side templates, logs, and suppression lists

External provider state is not preserved by a normal database and file backup. Document the active providers, credentials strategy, callback URLs, DNS records, and test procedure before a migration.

During a final migration, minimize the period in which orders can be written to both old and new origins. Confirm checkout, payment callbacks, scheduled actions, email, webhooks, inventory, and administrator access before declaring the cutover complete.

## Headless WooCommerce

SlickStack can serve as the WordPress and WooCommerce origin for a remote storefront. There is no separate headless mode, and SlickStack does not install or host the frontend application.

Common API pieces include:

| API | Normal role |
| :-- | :-- |
| WordPress REST API | Posts, pages, media, taxonomies, and custom WordPress content |
| WooCommerce Store API | Public product data and customer-facing cart, shipping, and checkout flows |
| WooCommerce REST API | Authenticated server-side management of products, orders, customers, coupons, and other store data |
| WPGraphQL | Optional GraphQL access to WordPress content |
| WooGraphQL | Optional WooCommerce extension for WPGraphQL |

The Store API is available under:

```text
https://cms.example.com/wp-json/wc/store/v1/
```

Examples include:

```text
/wp-json/wc/store/v1/products
/wp-json/wc/store/v1/cart
/wp-json/wc/store/v1/checkout
```

The Store API is intended for customer-facing storefront behavior. It reflects the current customer session and does not expose arbitrary customers, orders, or administrative store settings.

Cart and checkout requests require correct session handling. WooCommerce supports cookie-based customer sessions as well as `Cart-Token` headers for headless carts. Protected Store API writes require a valid nonce or cart token. The frontend must securely retain and forward the relevant session state.

The authenticated WooCommerce REST API uses the `/wp-json/wc/v3/` namespace and should normally be called only from trusted server-side code. Consumer secrets and other privileged credentials must never be exposed in browser JavaScript.

A practical headless architecture may use several APIs together:

```text
WPGraphQL or WordPress REST API  → editorial content
WooCommerce Store API           → products, cart, shipping, checkout
WooCommerce REST API            → trusted administrative integrations
```

WPGraphQL and WooGraphQL are optional. They can simplify complex GraphQL queries, but they are not required for a headless WooCommerce storefront.

Frontend frameworks such as Next.js, Astro, Nuxt, or SvelteKit can all consume these APIs. The important architecture decisions are rendering, session handling, checkout compatibility, cache invalidation, preview support, and secret isolation rather than the framework name alone.

Product and category pages may be statically generated or cached, but price, stock, cart, account, checkout, and personalized data require an appropriate refresh or server-rendering strategy. Publishing content or changing products in WordPress does not automatically rebuild or invalidate a remote frontend.

Some payment, subscription, membership, tax, and checkout extensions assume a traditional WordPress-rendered checkout. Verify each required extension before committing to a completely custom checkout. A pragmatic fallback is a headless catalog or cart that redirects customers to the normal WooCommerce checkout on the WordPress origin.

See [Headless WordPress](headless.md) for hostnames, SEO, authentication, media, redirects, previews, frontend caching, and revalidation.

## Troubleshooting

### Cart or checkout content appears stale

Confirm that the request is not being served from Nginx, Cloudflare, a frontend platform, a service worker, or browser cache. Check custom route names and customer cookies against the active cache-bypass rules.

### Checkout fails but product pages work

Inspect WooCommerce status logs, PHP errors, gateway logs, browser network requests, Store API responses, webhooks, and provider callbacks. Product browsing and checkout exercise very different application paths.

### Orders remain pending or background work is delayed

Check WP-Cron execution, Action Scheduler pending and failed queues, `/var/www/logs/wp-cli.log`, PHP-FPM capacity, MySQL health, and external API failures.

### Email is missing

Determine whether WooCommerce generated the event, Action Scheduler processed it, WordPress handed it to the transport, and the provider accepted it. Check the transactional provider dashboard before assuming a local server problem.

### Staging contacted real customers or services

Disable the affected integration, rotate exposed credentials when necessary, review copied production settings, and restore explicit sandbox or test endpoints. The normal staging safeguards do not intercept every custom external request.

### Headless cart state disappears

Verify that the frontend consistently stores and forwards the WooCommerce session cookie or `Cart-Token`, preserves updated response headers, and does not mix carts across users or cached responses.

## Scope

SlickStack manages the Ubuntu, Nginx, PHP-FPM, MySQL, Memcached, WordPress, cron, permissions, cache, backup, and service-recovery layers described in its documentation.

It does not manage:

- products, orders, customers, refunds, or fulfillment
- payment gateway accounts or compliance
- tax, shipping, inventory, fraud, or accounting providers
- transactional email accounts and deliverability
- frontend frameworks or deployment platforms
- external search, analytics, CDN, or media services
- custom plugin and storefront compatibility

SlickStack should provide a predictable origin and honest operational guidance without becoming a WooCommerce application product or composable-commerce platform.

## Related guides

- [WordPress](wordpress.md)
- [Headless WordPress](headless.md)
- [Caching](caching.md)
- [Cron Jobs](cron.md)
- [Email](email.md)
- [Staging & Development](staging-dev.md)
- [Backups](backups.md)
- [Migrations](migrations.md)
- [Monitoring](monitoring.md)
- [Logging](logging.md)
- [Security](security.md)
