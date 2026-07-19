# Nginx

SlickStack uses Nginx as the public origin web server, HTTPS terminator, WordPress router, static-file server, FastCGI cache, request-limiting layer, and gateway to PHP-FPM.

The standard request path is:

```text
Cloudflare or direct visitor
        |
        v
Iptables
        |
        v
Nginx on ports 80 and 443
        |
        +--> redirect, static file, error page, or FastCGI cache hit
        |
        `--> approved PHP request
                |
                v
            PHP-FPM on 127.0.0.1:9000
                |
                v
            WordPress
```

Nginx is one layer of SlickStack. Cloudflare, Iptables, PHP-FPM, WordPress, MySQL, Memcached, and browser caching have separate responsibilities and failure boundaries.

## Table of Contents

- [Main configuration files](#main-configuration-files)
- [Installation and reconciliation](#installation-and-reconciliation)
- [Main Nginx configuration](#main-nginx-configuration)
- [Production, staging, and development](#production-staging-and-development)
- [HTTP, HTTPS, and canonical routing](#http-https-and-canonical-routing)
- [WordPress request routing](#wordpress-request-routing)
- [FastCGI cache](#fastcgi-cache)
- [Static files and compression](#static-files-and-compression)
- [Request and connection limits](#request-and-connection-limits)
- [Headers and indexing](#headers-and-indexing)
- [Cloudflare integration](#cloudflare-integration)
- [Logging](#logging)
- [Approved include files only](#approved-include-files-only)
- [Optional loading behavior](#optional-loading-behavior)
- [/etc/nginx/conf.d/ is ignored](#etcnginxconfd-is-ignored)
- [Generated files and persistent customization](#generated-files-and-persistent-customization)
- [Validation and service control](#validation-and-service-control)
- [Common problems](#common-problems)
- [Scope](#scope)
- [Related guides](#related-guides)

## Main configuration files

SlickStack generates the active main configuration at:

```text
/etc/nginx/nginx.conf
```

Generated site and include files are stored under:

```text
/var/www/sites/
```

Important paths include:

| Path | Purpose |
| :-- | :-- |
| `/etc/nginx/nginx.conf` | Main worker, HTTP, cache, buffer, timeout, compression, header, limit, log, and include configuration |
| `/var/www/sites/production.conf` | Production server block |
| `/var/www/sites/staging.conf` | Optional staging server block |
| `/var/www/sites/development.conf` | Optional development server block |
| `/var/www/sites/includes/` | Explicit SlickStack-managed optional includes |
| `/var/www/sites/error_pages/` | Managed error-page files |
| `/var/www/logs/nginx-access.log` | HTTP access log |
| `/var/www/logs/nginx-error.log` | Nginx error log |
| `/var/www/cache/nginx/` | FastCGI response cache |

Repository templates are stored under:

```text
modules/nginx/
```

The templates are the upstream design. Installed files are generated copies with placeholders replaced from `ss-config` and automatic tuning.

## Installation and reconciliation

The narrow Nginx configuration installer is:

```bash
sudo bash /var/www/ss-install-nginx-config
```

The Bash alias is:

```bash
ss install nginx config
```

This operation does more than replace `nginx.conf`. Depending on the current configuration, it can:

- restore Nginx configuration permissions
- generate the self-signed fallback certificate
- generate DH parameters when missing
- create the staging and development guest-password file
- replace the main Nginx configuration
- replace the production server block and create or remove optional staging and development server blocks
- request or refresh a Certbot certificate
- activate the selected SSL include
- refresh Cloudflare real-IP ranges
- create or remove Cloudflare origin-control includes
- create or remove the Adminer include
- configure optional FastCGI cache storage on tmpfs
- forcefully restart Nginx
- rebuild generated WordPress configuration

Changing `/var/www/ss-config` alone does not change the running Nginx configuration. Run the Nginx installer or a deliberately broader SlickStack installation after changing relevant values.

The complete installer also rebuilds Nginx:

```bash
sudo bash /var/www/ss-install
```

Do not use the full installer merely because one Nginx file needs reconciliation. A full run also updates packages, touches other services, may synchronize staging, truncates SlickStack logs, resets permissions, purges caches, and restarts the broader stack.

See [Installation](installation.md), [Commands](commands.md), and [SS-Config](ss-config.md).

## Main Nginx configuration

The generated `/etc/nginx/nginx.conf` manages shared behavior for every enabled site environment.

Its main responsibilities include:

- running workers as `www-data`
- automatically selecting the worker-process count
- using Linux `epoll` event handling
- configuring worker connections and open-file limits
- enabling optimized kernel-level file serving
- disabling directory autoindexing
- defining FastCGI cache storage and behavior
- defining open-file cache behavior
- setting request buffers and timeouts
- enabling Gzip compression
- applying global HTTP headers
- defining request and connection limit zones
- writing Nginx logs under `/var/www/logs/`
- loading approved optional includes and generated server blocks

Many values are populated from `ss-config`. Other values remain hardcoded or are calculated automatically where SlickStack favors a predictable server-wide policy over another user option.

## Production, staging, and development

Production is always present. Staging and development are optional.

| Environment | Normal hostname | Web root | Server block |
| :-- | :-- | :-- | :-- |
| Production | `SITE_FULL_DOMAIN` | `/var/www/html/` | `/var/www/sites/production.conf` |
| Staging | `staging.<root-domain>` | `/var/www/html/staging/` | `/var/www/sites/staging.conf` |
| Development | `dev.<root-domain>` | `/var/www/html/dev/` | `/var/www/sites/development.conf` |

The Nginx installer removes the optional server block when its environment is disabled.

All enabled environments share:

- the same Nginx service
- the same PHP-FPM pool
- the same machine resources
- the same main `nginx.conf`
- the same certificate mode
- many of the same generated rules

They are separate server blocks, not isolated containers or virtual machines.

Staging and development can use shared HTTP Basic Authentication generated from `GUEST_USER` and `GUEST_PASSWORD`. Their noindex and access controls reduce accidental exposure but do not make copied production data harmless.

See [Staging & Dev](staging-dev.md).

## HTTP, HTTPS, and canonical routing

SlickStack is HTTPS-only.

The standard production configuration includes:

- an HTTP listener on port `80`
- an HTTPS listener on port `443`
- an ACME challenge exception for certificate validation
- redirects from normal HTTP requests to HTTPS
- a catch-all default server that redirects unexpected hostnames to the configured production hostname
- canonical production routing based on `SITE_FULL_DOMAIN`

The main configuration also sends HSTS by default:

```text
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
```

Because HSTS applies to subdomains and can be cached by browsers, domain and certificate changes should be planned carefully.

The default self-signed certificate is suitable as an encrypted Cloudflare origin certificate in Full mode, but it is not trusted directly by normal browsers. Certbot or a supported third-party certificate is required when the origin must present a publicly trusted certificate directly.

See [SSL (Certs)](ssl.md) and [Cloudflare](cloudflare.md).

## WordPress request routing

The normal WordPress route uses:

```nginx
try_files $uri $uri/ /index.php?$args;
```

Nginx therefore attempts, in order:

1. a real file
2. a real directory
3. WordPress through `index.php`

PHP requests are passed to:

```text
127.0.0.1:9000
```

SlickStack does not generally allow arbitrary PHP files to execute through the production server block. The standard configuration permits selected WordPress entry points and uses dedicated handling for important routes such as:

```text
/index.php
/wp-login.php
/wp-comments-post.php
/wp-signup.php
/wp-activate.php
/wp-admin/*.php
/wp-admin/admin-ajax.php
```

Other PHP requests matching the general PHP location can receive Nginx status `444` instead of being passed to PHP-FPM.

This restriction improves security, but a plugin or custom application that expects a directly callable custom PHP endpoint may require a reviewed source-level Nginx change.

See [PHP-FPM](php-fpm.md) and [WordPress](wordpress.md).

## FastCGI cache

SlickStack defines one Nginx FastCGI cache zone named:

```text
WORDPRESS
```

Its normal storage path is:

```text
/var/www/cache/nginx/
```

Eligible PHP responses can be served from this cache without repeating PHP, WordPress, or MySQL work.

The generated configuration normally bypasses FastCGI cache for dynamic or sensitive conditions such as:

- POST requests
- requests with query strings
- WordPress administration and login routes
- account, cart, checkout, order, profile, registration, and settings routes
- WooCommerce AJAX and session activity
- logged-in WordPress users
- password-protected content
- recent commenters
- REST API requests under `/wp-json/`

The response header:

```text
X-FastCGI-Cache
```

can help identify whether Nginx reports a cache hit, miss, bypass, or other cache state.

A custom membership, commerce, API, localized, or authenticated route may require an additional bypass rule. Test uncached and authenticated behavior before relying on the default patterns.

Clear only the Nginx FastCGI cache with:

```bash
sudo bash /var/www/ss-purge-nginx
```

or:

```bash
ss purge nginx
```

This does not clear Cloudflare, browser caches, PHP OPcache, Memcached, or WordPress transients.

See [Caching](caching.md).

## Static files and compression

Nginx serves eligible static files directly without sending them through PHP-FPM.

The managed configuration includes:

- MIME type handling
- long browser-cache rules for many static asset types
- Gzip compression for common text, script, font, SVG, XML, and JSON responses
- open-file caching for repeated filesystem lookups
- disabled directory listings

Cloudflare can independently cache or compress responses at its edge. Clearing Nginx cache or restarting Nginx does not force Cloudflare or visitor browsers to discard stored assets.

## Request and connection limits

The main Nginx configuration defines shared limits for:

- total server requests
- PHP requests
- WordPress login requests
- WordPress search requests
- Admin AJAX requests
- Adminer requests
- total server connections
- per-address connections

These limits are keyed partly by the remote address. Correct Cloudflare real-IP restoration is therefore important; otherwise many visitors can appear to share a Cloudflare proxy address and consume the same limit bucket.

The current shared configuration normally uses:

- Nginx status `444` for request-limit rejection
- Nginx status `503` for connection-limit rejection

Some bursts and the production FastCGI read timeout are automatically selected from server memory unless explicitly configured.

Nginx limits are origin controls. They do not replace Cloudflare WAF, bot, rate-limit, or DDoS controls.

## Headers and indexing

The main generated configuration can send headers including:

- `Strict-Transport-Security`
- `Content-Security-Policy` for normal single-site mode
- `X-Frame-Options`
- `X-Content-Type-Options`
- `X-XSS-Protection`
- `Referrer-Policy`
- `Permissions-Policy`
- `X-Robots-Tag`
- `X-FastCGI-Cache`
- `X-Powered-By`

The global noindex behavior is controlled through:

```bash
SS_NOINDEX="true"
```

The normal Content Security Policy is removed for WordPress Multisite because the current installer cannot determine one correct frame-ancestor domain pattern for every mapped hostname.

Headers may also be changed or supplemented by Cloudflare, WordPress, plugins, upstream PHP responses, or a browser policy. Inspect the final public response rather than assuming the Nginx template is the only layer involved.

## Cloudflare integration

SlickStack can generate Nginx includes for:

- restoring visitor addresses from Cloudflare proxy headers
- optionally allowing Cloudflare network ranges
- Authenticated Origin Pulls
- origin access gates

The normal baseline is:

```bash
CLOUDFLARE_REAL_IPS="true"
CLOUDFLARE_IPS_ONLY="false"
CLOUDFLARE_AUTHENTICATED_ORIGIN="false"
```

The current `CLOUDFLARE_IPS_ONLY` implementation conflicts with real-IP restoration because Nginx access rules can evaluate the restored visitor address rather than the original Cloudflare peer address. Leave it disabled on normal servers unless the implementation has been deliberately corrected and tested.

Authenticated Origin Pulls must be enabled in the Cloudflare zone as well as on the SlickStack origin. Enabling only the origin gate can cause public `403` responses.

See [Cloudflare](cloudflare.md) and [Security](security.md).

## Logging

The managed log paths are:

```text
/var/www/logs/nginx-access.log
/var/www/logs/nginx-error.log
```

The access log is the primary source for:

- requested hostnames and paths
- response status codes
- client addresses after active real-IP handling
- user agents and referrers in the active log format
- repeated request or abuse patterns

The error log records messages at `error` severity and above under the current configuration.

View recent entries:

```bash
sudo tail -n 100 /var/www/logs/nginx-access.log
sudo tail -n 100 /var/www/logs/nginx-error.log
```

Follow them live:

```bash
sudo tail -f /var/www/logs/nginx-access.log
sudo tail -f /var/www/logs/nginx-error.log
```

The aliases are:

```bash
ss tail nginx access log
ss tail nginx error log
```

Nginx startup and service failures can appear only in the systemd journal:

```bash
journalctl -u nginx --no-pager -n 100
```

A full SlickStack installation truncates managed `.log` files. Preserve relevant evidence before reinstalling during an outage or security investigation.

See [Logging](logging.md).

## Approved include files only

SlickStack does **not** support open-ended or arbitrary Nginx subconfiguration files. A file is eligible to load only when its filename is explicitly referenced by SlickStack's templates.

Approved optional includes are stored under:

```text
/var/www/sites/includes/
```

Examples can include:

```text
cloudflare.conf
allowed-ips.conf
auth-origin.conf
origin-gate.conf
perms-policy.conf
adminer.conf
openssl.conf
letsencrypt.conf
thirdparty.conf
```

The exact active set depends on `ss-config` and the selected environment.

Dropping a random `.conf` file into `/var/www/sites/includes/` does not make Nginx load it.

## Optional loading behavior

Approved optional files use exact include patterns such as:

```nginx
include /var/www/sites/includes/cloudflare[.]conf;
```

The `[.]` pattern matches a literal dot while still allowing the include to match nothing. When the approved file exists, Nginx loads it. When it does not exist, Nginx continues without that optional module.

This lets SlickStack create or remove optional features without leaving broken unconditional includes.

## `/etc/nginx/conf.d/` is ignored

SlickStack does not use the default Nginx subconfiguration directory:

```text
/etc/nginx/conf.d/
```

Files placed there are outside SlickStack's supported configuration flow and are ignored by the managed `nginx.conf`.

SlickStack also does not use the conventional `sites-available` and `sites-enabled` workflow for its managed server blocks. `/var/www/sites/` is the canonical SlickStack site-configuration area.

## Generated files and persistent customization

Most active Nginx files are generated, removed, or replaced by SlickStack scripts. Direct edits can be overwritten by:

- `ss-install-nginx-config`
- the complete `ss-install`
- certificate operations
- Cloudflare include refreshes
- permission resets
- future template changes

Persistent changes should use:

- an existing supported `ss-config` option
- an explicitly referenced and approved include
- a reviewed source-level SlickStack template change
- Cloudflare or provider controls when the behavior belongs outside Nginx

Do not introduce broad wildcard loading merely to preserve one custom snippet. Open-ended loading makes stale package files and forgotten experiments part of the production request path.

When adding a new supported include to SlickStack itself:

1. choose one explicit filename
2. reference it deliberately from the appropriate template
3. define when the installer creates or removes it
4. validate its contents before promotion
5. document its interaction with existing request, cache, and security behavior

## Validation and service control

Validate the effective Nginx configuration with:

```bash
sudo nginx -t
```

Display the complete active configuration with:

```bash
sudo nginx -T
```

Check service state and recent journal output:

```bash
systemctl status nginx --no-pager
journalctl -u nginx --no-pager -n 100
```

Check listeners:

```bash
sudo ss -lntp | grep -E ':(80|443)\b'
```

Reload Nginx gracefully after a verified manual change:

```bash
sudo nginx -t && sudo systemctl reload nginx
```

Restart through SlickStack when a full Nginx service restart is intended:

```bash
sudo bash /var/www/ss-restart-nginx
```

or:

```bash
ss restart nginx
```

The SlickStack restart script performs a forceful service restart rather than a graceful configuration reload.

The Nginx configuration installer ultimately restarts Nginx and is not a dry-run command. Preserve the current configuration and keep provider-console access available when testing risky network, certificate, or server-block changes.

## Common problems

### Nginx will not start or reload

Check:

```bash
sudo nginx -t
systemctl status nginx --no-pager
journalctl -u nginx --no-pager -n 150
sudo tail -n 150 /var/www/logs/nginx-error.log
```

Common causes include:

- invalid generated or manually edited syntax
- missing certificate files
- unreadable private keys
- duplicate listeners or server names
- missing explicitly referenced files
- unsupported directives or modules
- incorrect ownership or permissions

When generated state is damaged, rebuild only the Nginx layer:

```bash
sudo bash /var/www/ss-install-nginx-config
```

### `403 Forbidden`

Check Cloudflare origin controls, Authenticated Origin Pulls, staging/development guest protection, file permissions, approved PHP entry points, and application authorization.

### `404 Not Found`

Confirm the requested hostname uses the correct server block and web root. Check whether the path should be a real static file or a WordPress route handled through `index.php`.

### `444` response or closed connection

SlickStack uses status `444` for several denied or rate-limited requests. Check the requested PHP entry point, request rate, client address, Cloudflare real-IP behavior, and Nginx access log.

### `502 Bad Gateway`

Nginx usually could not obtain a valid PHP-FPM response. Check:

```bash
systemctl status php*-fpm.service --no-pager
sudo ss -lntp | grep ':9000'
sudo tail -n 150 /var/www/logs/php-error.log
```

### `503 Service Unavailable`

Check whether managed maintenance mode remains enabled:

```bash
ls -l /var/www/html/maintenance.html
```

Disable it when appropriate:

```bash
sudo bash /var/www/ss-maintenance-disable
```

A `503` can also come from connection limiting, overload, or another upstream failure.

### `504 Gateway Timeout`

Investigate PHP-FPM saturation, long PHP execution, database latency, remote API delays, and the generated FastCGI read timeout. Increasing a timeout can hide a slow application rather than solve it.

### Public request fails but local Nginx works

Compare the public path with a direct local-origin request:

```bash
curl -I https://example.com
curl -kI --resolve example.com:443:127.0.0.1 https://example.com/
```

When the local request works but the public request fails, investigate DNS, Cloudflare, provider networking, firewalls, origin restrictions, or certificate mode rather than rebuilding WordPress immediately.

See [Troubleshooting](troubleshooting.md) for the complete first-response runbook.

## Scope

The standard SlickStack Nginx design assumes:

- one primary production domain per server
- optional staging and development subdomains
- HTTPS-only server blocks
- one local Nginx service
- one local PHP-FPM upstream on `127.0.0.1:9000`
- explicitly generated server blocks
- explicitly approved optional includes
- one shared SSL mode for managed environments
- one Nginx FastCGI cache zone

The standard architecture does not aim to provide:

- unrelated primary domains on one server
- shared-hosting multi-tenancy
- open-ended virtual-host discovery
- arbitrary `/etc/nginx/conf.d/` loading
- per-site PHP-FPM pools
- remote PHP-FPM upstreams
- Nginx clustering or load balancing
- container ingress management
- automatic custom reverse-proxy application hosting
- zero-downtime configuration deployment

These are architectural boundaries, not merely undocumented features.

## Related guides

- [Architecture](architecture.md)
- [Installation](installation.md)
- [Commands](commands.md)
- [Troubleshooting](troubleshooting.md)
- [SS-Config](ss-config.md)
- [Cloudflare](cloudflare.md)
- [SSL (Certs)](ssl.md)
- [Caching](caching.md)
- [PHP-FPM](php-fpm.md)
- [WordPress](wordpress.md)
- [Logging](logging.md)
- [Security](security.md)
- [Permissions](permissions.md)
- [Staging & Dev](staging-dev.md)
