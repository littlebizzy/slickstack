# Email Delivery

SlickStack does not install or manage an email server, mailbox service, SMTP relay, or transactional email provider.

WordPress, WooCommerce, contact forms, membership plugins, and other applications can generate messages, but reliable delivery requires a separately configured email service. SlickStack manages the web stack; it does not manage the complete email delivery chain.

## Table of Contents

- [Current scope](#current-scope)
- [WordPress outgoing mail](#wordpress-outgoing-mail)
- [Production delivery](#production-delivery)
- [SMTP and API providers](#smtp-and-api-providers)
- [Domain authentication](#domain-authentication)
- [Sender addresses and alignment](#sender-addresses-and-alignment)
- [Staging and development](#staging-and-development)
- [WooCommerce and background jobs](#woocommerce-and-background-jobs)
- [Inbound mail and mailboxes](#inbound-mail-and-mailboxes)
- [Server notifications](#server-notifications)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)
- [Backups and migrations](#backups-and-migrations)
- [Security](#security)
- [Recommended baseline](#recommended-baseline)
- [Scope](#scope)
- [Related guides](#related-guides)

## Current scope

SlickStack currently does not provide or configure:

- Postfix, Exim, Sendmail, or another mail transfer agent
- a local SMTP relay
- hosted inboxes or webmail
- transactional email accounts
- SMTP or API credentials
- SPF, DKIM, or DMARC records
- bounce, complaint, or suppression-list handling
- email queues or delivery dashboards
- automated email alerts for SlickStack jobs

The old Postfix installer is deprecated and removed by the SlickStack cleanup workflow. The managed PHP configuration also leaves `sendmail_path` unset.

Do not assume a fresh SlickStack server can deliver production email merely because WordPress can call `wp_mail()`.

## WordPress outgoing mail

WordPress normally sends application messages through `wp_mail()`. Examples include:

- administrator and password-reset messages
- contact-form notifications
- WooCommerce order email
- membership and subscription notices
- security alerts
- plugin-generated reports

`wp_mail()` generating or accepting a message does not prove that the message reached the recipient. Delivery still depends on the configured transport, the provider accepting the message, domain authentication, reputation, filtering, and the destination mailbox.

A fresh SlickStack installation derives its initial WordPress administrator address from the configured domain. That address is only application data; SlickStack does not create a corresponding mailbox.

## Production delivery

For production sites, use a dedicated transactional email provider or another professionally managed relay.

The normal architecture is:

```text
WordPress or plugin
        ↓
SMTP plugin or provider API integration
        ↓
Transactional email provider
        ↓
Recipient mail server
```

SlickStack does not select, install, configure, monitor, or support a particular provider. The WordPress plugin or custom application integration remains responsible for passing messages to that provider.

Running a complete mail server directly on the same web server is outside SlickStack's standard scope. Self-hosted email introduces separate requirements involving reverse DNS, IP reputation, queue management, spam filtering, abuse handling, TLS, updates, monitoring, and deliverability.

## SMTP and API providers

Most WordPress email integrations use one of two approaches.

### SMTP

An SMTP plugin authenticates with a provider using a hostname, port, username, password, and encryption mode.

Common submission ports include `465` and `587`, depending on the provider. Hosting companies may restrict outbound SMTP traffic, and provider-specific requirements still apply.

### Provider API

An API integration sends messages over HTTPS using an API key or token. This can avoid some SMTP port restrictions and may provide richer delivery, bounce, and event reporting.

Neither approach is inherently managed by SlickStack. Choose a maintained integration that supports the provider, WordPress version, PHP version, and required application features.

Avoid installing multiple SMTP or email-routing plugins at the same time. Competing integrations can modify the same WordPress mail hooks and make failures difficult to diagnose.

## Domain authentication

Reliable sending normally requires DNS records supplied by the selected email provider.

### SPF

SPF identifies servers or services permitted to send mail for a domain. Maintain one valid SPF policy rather than publishing multiple competing SPF records.

### DKIM

DKIM signs outgoing mail so recipient systems can verify that the authorized provider handled the message and that signed content was not modified in transit.

### DMARC

DMARC evaluates alignment between the visible sender domain and authenticated SPF or DKIM domains. It can also provide aggregate reports and define how receivers should treat failed messages.

A cautious deployment normally begins with monitoring and is tightened only after legitimate sending sources have been identified.

### MX records

MX records control inbound mailbox delivery. They do not configure WordPress transactional sending by themselves.

SlickStack and Cloudflare integration do not automatically create or validate any of these email records. Publish the exact records required by the mailbox or transactional provider in the authoritative DNS zone.

## Sender addresses and alignment

Use a sender address on a domain authorized by the selected provider, for example:

```text
notifications@example.com
```

Do not use a visitor's submitted address as the message's `From` address. Contact forms should normally use an authenticated site-domain sender and place the visitor's address in `Reply-To` after appropriate validation.

This improves SPF, DKIM, and DMARC alignment and reduces spoofing and deliverability problems.

The visible sender, return path, reply-to address, and provider-authenticated domain may serve different purposes. Follow the provider's documentation instead of forcing them all to an arbitrary value.

## Staging and development

SlickStack automatically installs the following safeguards in staging and development:

```text
disable-emails
disable-default-runner
```

`disable-emails` suppresses normal WordPress application email from test environments. Copied password resets, order messages, form notifications, subscription notices, and similar messages should therefore not behave like production.

`disable-default-runner` suppresses the default Action Scheduler runner. Queued or recurring plugin jobs may also remain pending even when their production equivalents run normally.

These protections do not guarantee that every external message or request is blocked. Custom code can call a provider API directly, plugins can use their own background systems, and external webhooks may bypass normal WordPress mail hooks.

Prefer provider sandbox modes, test inboxes, address allowlists, or non-customer test data when validating email workflows.

The safeguards are managed files. Rebuilding the SlickStack MU plugin layer restores them when the corresponding environment is enabled.

## WooCommerce and background jobs

WooCommerce and related extensions can generate email immediately or through scheduled background work.

In staging or development:

- an order status can change without its normal email being delivered
- a message can remain queued because the default Action Scheduler runner is disabled
- a manual resend can still be suppressed by the email safeguard
- a plugin using a direct external API may behave differently from one using `wp_mail()`

When troubleshooting WooCommerce email, separate these questions:

1. Was the WordPress or WooCommerce event generated?
2. Was the email created or queued?
3. Did a scheduler run the queued action?
4. Did WordPress pass the message to the configured transport?
5. Did the provider accept it?
6. Did the destination server deliver, reject, defer, quarantine, or classify it as spam?

Do not remove test-environment safeguards merely to make staging imitate production without first protecting real customers and recipients.

## Inbound mail and mailboxes

SlickStack does not host inboxes for addresses such as:

```text
admin@example.com
support@example.com
orders@example.com
```

Use an external mailbox provider and configure its MX and authentication records in DNS.

Transactional sending and mailbox hosting can use the same provider or separate providers. Their DNS records must remain compatible, especially the domain's single SPF policy.

WordPress features that retrieve email through IMAP, POP3, webhooks, or provider APIs require their own plugin and credentials. SlickStack does not configure those connections.

## Server notifications

SlickStack does not currently send managed email notifications for:

- cron failures
- watchdog restarts
- backup completion or failure
- package updates
- certificate renewal results
- disk-space thresholds
- security incidents

Use the SlickStack and system logs together with an external monitoring or notification service when automated operational alerts are required.

Installing a WordPress SMTP plugin does not automatically enable server-level Bash, systemd, Nginx, MySQL, or PHP-FPM notifications.

## Testing

Test production delivery only after the provider integration and DNS authentication are configured.

A direct WordPress test can be run with WP-CLI:

```bash
sudo -u www-data wp --path=/var/www/html eval 'var_dump( wp_mail( "you@example.com", "SlickStack email test", "This is a test message." ) );'
```

Replace the recipient before running the command.

A `true` result means WordPress accepted the request through its current mail path. It does not prove inbox delivery. Confirm the message in the provider's activity log and at the destination mailbox.

Also test the actual application path that matters, such as:

- password reset
- contact form
- WooCommerce order
- membership renewal
- scheduled report

A generic test message can succeed while a specific plugin fails because of its own sender, template, queue, or event configuration.

## Troubleshooting

### WordPress reports success but no message arrives

Check the transactional provider's activity log first. Determine whether the provider accepted, rejected, deferred, bounced, or never received the message.

Then verify:

- the active SMTP or API integration
- sender-domain verification
- SPF, DKIM, and DMARC results
- the `From` and `Reply-To` addresses
- provider account status and sending limits
- recipient suppression or bounce lists
- spam and quarantine folders

### WordPress reports failure

Check:

- plugin configuration and credentials
- provider hostname, port, encryption, or API endpoint
- outbound network restrictions
- PHP errors
- plugin-specific logs
- whether multiple email plugins are competing

Relevant SlickStack logs may include:

```text
/var/www/logs/php-error.log
/var/www/logs/wp-cli.log
/var/www/html/wp-content/debug.log
```

The WordPress debug log exists only when WordPress debugging is enabled and the application writes to it.

A standard SlickStack server does not provide a managed mail queue or normal Postfix mail log. Do not spend time looking for local MTA delivery records unless an administrator separately installed and configured one.

### Email works in production but not staging or development

This is normally expected because SlickStack installs `disable-emails` in both test environments. Scheduled email can also remain pending because `disable-default-runner` suppresses the normal Action Scheduler runner.

### Some messages work but others fail

Compare the sender, recipient, plugin, queue method, message size, attachments, and provider event log. Different plugins can use different transports or bypass the standard WordPress mail path.

### Email stopped after a migration

Verify that:

- the email plugin and its database settings migrated
- custom integration files migrated
- provider credentials are still valid
- the new server can reach the provider
- the sender domain remains verified
- DNS records still match the active provider
- domain search-and-replace did not corrupt serialized plugin settings

## Backups and migrations

Email configuration can exist in several places:

- WordPress database options
- plugin configuration files
- `custom-functions.php`
- environment variables or external secret stores
- the transactional provider account
- authoritative DNS records

Database and file backups do not preserve external provider accounts, DNS zones, delivery history, suppression lists, or provider-side templates.

Before migrating a site, record the current provider, sender domains, DNS records, credentials strategy, plugin configuration, webhooks, and required IP or domain allowlists. Test delivery from the new server before the final cutover.

Do not copy production credentials into staging or development unless the provider is configured to prevent delivery to real recipients.

## Security

Treat SMTP passwords, API keys, webhook secrets, and provider tokens as production credentials.

- do not commit them to public repositories
- do not place them in themes or downloadable files
- restrict access to WordPress administrators who require it
- use narrowly scoped provider credentials when available
- rotate exposed or abandoned credentials
- enable multi-factor authentication on provider accounts
- review provider activity and suppression events

When a plugin supports constants or another file-based secret method, use a protected custom configuration mechanism rather than editing SlickStack's generated `wp-config.php` directly. Support varies by plugin, so follow its documented method.

## Recommended baseline

A practical production baseline is:

1. choose a dedicated transactional provider
2. install one maintained SMTP or API integration
3. verify the sending domain
4. publish valid SPF and DKIM records
5. deploy and monitor DMARC
6. use a domain-aligned sender address
7. test important application workflows
8. review provider delivery logs and limits
9. retain staging and development safeguards
10. document the configuration for migration and recovery

For inbound business email, use a dedicated mailbox provider rather than attempting to turn the SlickStack web server into a general-purpose mail server.

## Scope

SlickStack manages the web server and WordPress environment. It deliberately leaves email transport, reputation, mailbox hosting, provider billing, delivery analytics, DNS authentication, compliance, retention, and abuse handling to specialized services.

A future SlickStack integration could document or simplify selected provider workflows, but SlickStack does not currently include a managed Postfix replacement, SMTP relay, transactional service, or email dashboard.

## Related guides

- [WordPress](wordpress.md)
- [Staging & Development](staging-dev.md)
- [MU Plugins](mu-plugins.md)
- [Logging](logging.md)
- [Troubleshooting](troubleshooting.md)
- [Migrations](migrations.md)
- [Cloudflare](cloudflare.md)
- [Security](security.md)
