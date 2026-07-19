# Cloudflare

SlickStack is designed to work behind Cloudflare for public DNS, reverse-proxy traffic, edge security, and HTTPS between visitors and the origin server.

SlickStack manages the origin-side Nginx configuration. It does not create Cloudflare zones, DNS records, SSL/TLS dashboard settings, WAF rules, cache rules, redirects, or account credentials.

## Table of Contents

- [Recommended baseline](#recommended-baseline)
- [SlickStack settings](#slickstack-settings)
- [DNS and proxy setup](#dns-and-proxy-setup)
- [SSL/TLS encryption mode](#ssltls-encryption-mode)
- [Restoring visitor IP addresses](#restoring-visitor-ip-addresses)
- [Cloudflare-only origin access](#cloudflare-only-origin-access)
- [Authenticated Origin Pulls](#authenticated-origin-pulls)
- [Cloudflare credentials and Certbot DNS validation](#cloudflare-credentials-and-certbot-dns-validation)
- [Cloudflare IP range refreshes](#cloudflare-ip-range-refreshes)
- [Nginx, Cloudflare, and request limits](#nginx-cloudflare-and-request-limits)
- [Caching boundaries](#caching-boundaries)
- [Interaction with other SlickStack components](#interaction-with-other-slickstack-components)
- [Managed files](#managed-files)
- [Verification](#verification)
- [Troubleshooting](#troubleshooting)
- [Scope](#scope)
- [References](#references)

## Recommended baseline

A normal SlickStack deployment should begin with:

```bash
CLOUDFLARE_REAL_IPS="true"
CLOUDFLARE_IPS_ONLY="false"
CLOUDFLARE_AUTHENTICATED_ORIGIN="false"
```

Then:

1. point the required Cloudflare DNS records to the SlickStack origin
2. enable Cloudflare proxying for the public website records
3. use Cloudflare **Full** mode with SlickStack's default self-signed OpenSSL certificate
4. use **Full (strict)** after activating a valid Certbot, Cloudflare Origin CA, or suitable third-party certificate
5. verify the site, logs, SSL, WordPress, and Certbot behavior
6. enable stricter origin protections only after the basic proxy setup is stable

Do not begin by enabling every restriction at once. Real-IP restoration, Cloudflare-only origin access, Authenticated Origin Pulls, and certificate validation solve different problems and fail differently.

> **Current limitation:** Do not enable `CLOUDFLARE_IPS_ONLY="true"` together with `CLOUDFLARE_REAL_IPS="true"`. Nginx replaces the client address with `CF-Connecting-IP` before its `allow` and `deny` access checks run. The current SlickStack `allowed-ips.conf` then compares Cloudflare's network allowlist against the restored visitor address and can reject legitimate proxied traffic. Treat `CLOUDFLARE_IPS_ONLY` as experimental until the implementation is changed to evaluate the original network peer address.

## SlickStack settings

The Cloudflare-related `ss-config` values are:

```bash
CLOUDFLARE_EMAIL=""
CLOUDFLARE_API_KEY=""
CLOUDFLARE_IPS_ONLY="false"
CLOUDFLARE_AUTHENTICATED_ORIGIN="false"
CLOUDFLARE_REAL_IPS="true"
```

| Setting | Default | Purpose |
| :-- | :--: | :-- |
| `CLOUDFLARE_REAL_IPS` | `true` | Trust Cloudflare proxy ranges and restore the visitor address from `CF-Connecting-IP` |
| `CLOUDFLARE_IPS_ONLY` | `false` | Installs a Cloudflare network allowlist, but currently conflicts with real-IP restoration |
| `CLOUDFLARE_AUTHENTICATED_ORIGIN` | `false` | Validate Cloudflare's client certificate for the origin gate used by the standard production server block |
| `CLOUDFLARE_EMAIL` | empty | Cloudflare account email used by the current Certbot DNS credentials format |
| `CLOUDFLARE_API_KEY` | empty | Cloudflare Global API Key used by the current Certbot DNS credentials format |

After changing these values, rebuild the managed Nginx configuration:

```bash
sudo bash /var/www/ss-install-nginx-config
```

The complete installer also reapplies the configuration:

```bash
sudo bash /var/www/ss-install
```

## DNS and proxy setup

Cloudflare DNS remains an administrator responsibility.

At minimum, the hostname represented by:

```bash
SITE_FULL_DOMAIN="example.com"
```

must resolve to the intended SlickStack origin. Depending on the selected canonical domain and enabled environments, additional records can include:

```text
example.com
www.example.com
staging.example.com
dev.example.com
```

Use proxied records—the orange cloud—for hostnames that should receive Cloudflare caching, WAF, DDoS, TLS, and origin-protection benefits.

DNS-only records connect visitors directly to the origin and can expose its address. They also bypass Cloudflare's WAF and other edge controls.

SlickStack does not automatically:

- create or update A, AAAA, or CNAME records
- enable proxying
- remove obsolete DNS records
- hide origin addresses exposed by unrelated DNS records
- manage Cloudflare nameservers
- validate that staging and development records exist

Confirm DNS before enabling stricter origin controls:

```bash
dig +short example.com
dig +short www.example.com
```

A proxied record normally returns Cloudflare edge addresses rather than the origin address.

## SSL/TLS encryption mode

Cloudflare's SSL/TLS mode controls the connection from Cloudflare to the SlickStack origin.

| SlickStack certificate | Recommended Cloudflare mode |
| :-- | :-- |
| Self-signed OpenSSL | **Full** |
| Certbot / Let's Encrypt | **Full (strict)** |
| Valid publicly trusted third-party certificate | **Full (strict)** |
| Cloudflare Origin CA installed as a third-party certificate | **Full (strict)** |

### Full mode

The default SlickStack OpenSSL certificate is self-signed. Cloudflare Full mode encrypts traffic to the origin without requiring a publicly trusted certificate.

This is the expected starting mode for a new SlickStack server using:

```bash
SSL_TYPE="openssl"
```

### Full (strict) mode

Full (strict) validates the origin certificate. Use it after SlickStack is actively presenting a valid certificate matching the requested hostname.

For Certbot:

```bash
SSL_TYPE="certbot"
```

Then run:

```bash
sudo bash /var/www/ss-install-nginx-config
```

See [SSL (Certificates)](ssl.md) for certificate issuance, activation, paths, and fallback behavior.

### Flexible and Off modes

Cloudflare Flexible mode is not supported for SlickStack. Flexible sends plaintext HTTP from Cloudflare to the origin, while SlickStack is HTTPS-only and redirects HTTP requests to HTTPS. That combination can create redirect loops and removes encryption between Cloudflare and the origin.

Cloudflare Off mode is also incompatible with SlickStack's HTTPS-only design.

Use Full or Full (strict).

## Restoring visitor IP addresses

When Cloudflare proxies a request, the network connection reaching Nginx normally comes from a Cloudflare address rather than the visitor's address.

With:

```bash
CLOUDFLARE_REAL_IPS="true"
```

SlickStack downloads Cloudflare's current IPv4 and IPv6 ranges and generates:

```text
/var/www/sites/includes/cloudflare.conf
```

The generated file contains one trusted proxy directive per Cloudflare network:

```nginx
set_real_ip_from 192.0.2.0/24;
```

and activates:

```nginx
real_ip_header CF-Connecting-IP;
```

The live Nginx configuration includes this file in the global `http` context.

After Nginx processes a trusted Cloudflare request, `$remote_addr` represents the visitor address. The original network peer remains available to Nginx as `$realip_remote_addr`.

Real-IP restoration affects:

- `/var/www/logs/nginx-access.log`
- Nginx per-IP request and connection limits
- WordPress and PHP applications reading the remote address
- Adminer rate limiting
- the current Fail2ban Nginx filters
- any Nginx access rules that evaluate the client address

Without real-IP restoration, many visitors can appear to come from the same Cloudflare proxy address. That can make logs less useful and cause per-IP security controls to group unrelated users together.

### Security boundary

SlickStack trusts `CF-Connecting-IP` only from addresses listed in the generated Cloudflare configuration. A direct visitor should not be able to spoof the header merely by sending it to the origin from an untrusted address.

The trusted range list must therefore remain current.

### Verification

Check the generated configuration:

```bash
grep -c "set_real_ip_from" /var/www/sites/includes/cloudflare.conf
grep "real_ip_header" /var/www/sites/includes/cloudflare.conf
sudo nginx -t
```

Then inspect requests:

```bash
tail -n 50 /var/www/logs/nginx-access.log
```

The first address should normally be the visitor address rather than a Cloudflare proxy address.

When `CLOUDFLARE_REAL_IPS="false"`, the full Nginx installer removes the managed Cloudflare real-IP include.

## Cloudflare-only origin access

SlickStack currently exposes this setting:

```bash
CLOUDFLARE_IPS_ONLY="true"
```

It generates:

```text
/var/www/sites/includes/allowed-ips.conf
```

The file contains Cloudflare's IPv4 and IPv6 ranges as Nginx `allow` directives followed by:

```nginx
deny all;
```

The file is included in Nginx's global `http` context.

### Current incompatibility with real-IP restoration

The current implementation does not safely combine with the default:

```bash
CLOUDFLARE_REAL_IPS="true"
```

Nginx's real-IP module changes the client address before access-module rules evaluate it. Therefore the `allow` directives in `allowed-ips.conf` see the restored visitor address rather than the Cloudflare proxy address.

A normal visitor address does not match Cloudflare's network ranges, so the final `deny all` can reject the request even though it arrived through Cloudflare.

The original proxy address is available through `$realip_remote_addr`, but the current `allow` and `deny` template does not use that variable.

### Current recommendation

Leave:

```bash
CLOUDFLARE_IPS_ONLY="false"
```

on normal production servers.

Do not use this setting as the primary origin-protection control until SlickStack changes the implementation to evaluate the original peer address through a compatible `geo`, `map`, network-firewall, or equivalent design.

Setting `CLOUDFLARE_REAL_IPS="false"` avoids the direct conflict because `$remote_addr` remains the Cloudflare proxy address, but then access logs and per-IP Nginx limits no longer operate on the real visitor address. That tradeoff is usually worse than leaving `CLOUDFLARE_IPS_ONLY` disabled.

### Intended protection and limits

The intended purpose is to prevent direct web requests from bypassing Cloudflare and reaching the origin through Nginx.

Even after the implementation is corrected, an Nginx Cloudflare allowlist would not:

- change Iptables rules
- close ports 80 or 443 at the network layer
- restrict SSH or SFTP
- protect MySQL, Memcached, or PHP-FPM directly
- hide an origin address that has already been exposed
- prove that the request belongs to your specific Cloudflare account

Network-layer Cloudflare allowlisting belongs in Iptables or the cloud provider firewall, where the original TCP peer address is still available.

## Authenticated Origin Pulls

Authenticated Origin Pulls uses mutual TLS between Cloudflare and the origin. Cloudflare presents a client certificate, and the origin evaluates that certificate before serving the request.

Enable the origin-side SlickStack configuration with:

```bash
CLOUDFLARE_AUTHENTICATED_ORIGIN="true"
```

The installer downloads Cloudflare's shared client-certificate authority and manages:

```text
/var/www/certs/cloudflare.pem
/var/www/sites/includes/auth-origin.conf
/var/www/sites/includes/origin-gate.conf
```

The Nginx configuration:

- requests a client certificate without failing the TLS handshake immediately
- allows loopback addresses in the authentication map
- accepts successfully verified Cloudflare client certificates
- sets an internal allow/deny variable
- returns `403` through the origin gate when neither condition is satisfied

### Cloudflare dashboard requirement

The Cloudflare zone must also have Authenticated Origin Pulls enabled. Setting only the SlickStack value does not make Cloudflare present the required client certificate.

The safest order is:

1. confirm proxied HTTPS works in Full or Full (strict) mode
2. enable Authenticated Origin Pulls for the Cloudflare zone
3. set `CLOUDFLARE_AUTHENTICATED_ORIGIN="true"`
4. run `ss-install-nginx-config`
5. test the public proxied site immediately

Cloudflare presenting a client certificate to an origin that does not yet validate it is normally harmless. Enabling the origin gate first can cause public requests to receive `403` until Cloudflare begins presenting the certificate.

### Current certificate model

SlickStack downloads Cloudflare's shared global Authenticated Origin Pull certificate. This verifies that the connection came from the Cloudflare network; it does not prove that the request came from only your Cloudflare account.

Cloudflare zone-level and per-hostname Authenticated Origin Pull certificates use account-specific certificates. SlickStack does not currently generate, upload, or manage those custom AOP certificates.

### Current server-block limitation

The current standard single-site production server block includes:

```nginx
include /var/www/sites/includes/origin-gate[.]conf;
```

The current staging, development, and Multisite server-block templates do not include the same origin gate.

Therefore `CLOUDFLARE_AUTHENTICATED_ORIGIN="true"` should not be described as universal enforcement across every SlickStack hostname or server-block mode.

## Cloudflare credentials and Certbot DNS validation

Normal Cloudflare proxying and real-IP restoration do not require Cloudflare API credentials.

The credentials are used by SlickStack's Certbot DNS workflow for WordPress Multisite wildcard certificates.

The current implementation expects:

```bash
CLOUDFLARE_EMAIL="account@example.com"
CLOUDFLARE_API_KEY="global-api-key"
```

When both values exist, `ss-encrypt-certbot` generates:

```text
/var/www/meta/cloudflare.ini
```

with:

```ini
dns_cloudflare_email = account@example.com
dns_cloudflare_api_key = global-api-key
```

The file is owned by `root:root` and uses mode `0600`.

Certbot receives it through:

```text
--dns-cloudflare-credentials /var/www/meta/cloudflare.ini
--dns-cloudflare-propagation-seconds 30
```

### API token limitation

The sample `ss-config` comment mentions an API key or token, but the active generation script always writes the Global API Key fields shown above.

Cloudflare API tokens require the separate Certbot field:

```ini
dns_cloudflare_api_token = token-value
```

SlickStack does not currently generate that format. Use the Global API Key for the current managed wildcard workflow, or treat a manual token-based credentials file as unsupported because a later SlickStack run can overwrite it.

Never publish `/var/www/meta/cloudflare.ini`, include it in public backups, or paste its contents into support tickets.

See [SSL (Certificates)](ssl.md) for the complete Certbot workflow.

## Cloudflare IP range refreshes

The full Nginx configuration installer downloads Cloudflare's current IPv4 and IPv6 lists whenever it rebuilds the Cloudflare-related includes:

```bash
sudo bash /var/www/ss-install-nginx-config
```

The sample configuration also retains:

```bash
INTERVAL_SS_INSTALL_NGINX_CLOUDFLARE_IPS="half-weekly"
```

and the half-weekly, weekly, and half-monthly cron wrappers still reference a standalone Cloudflare IP refresh task.

However, the current standalone script remains under `bash/archive/`, and current `ss-functions` does not define an active `PATH_SS_INSTALL_NGINX_CLOUDFLARE_IPS` path alongside the normal installer paths.

Do not rely exclusively on the legacy scheduled standalone refresh. The authoritative current workflow is:

```bash
sudo bash /var/www/ss-install-nginx-config
```

This rebuilds both Cloudflare range files and validates that the downloaded templates contain the expected directives before replacing the installed files.

## Nginx, Cloudflare, and request limits

SlickStack's Nginx request limits use variables based on the remote address, including:

```nginx
$binary_remote_addr
```

With real-IP restoration enabled, per-IP Nginx limits apply to the visitor address. Without it, limits can apply to Cloudflare proxy addresses and unintentionally combine many visitors into shared buckets.

Cloudflare also has independent WAF, rate-limiting, bot, and DDoS controls at its edge. SlickStack does not create or synchronize those rules.

A sensible division is:

- Cloudflare handles broad edge filtering and DDoS absorption
- Nginx handles origin-level request and connection limits
- Iptables handles the persistent server firewall
- WordPress handles application permissions and authentication

Do not assume one layer replaces every other layer.

## Caching boundaries

Cloudflare edge caching and SlickStack FastCGI caching are separate systems.

SlickStack manages the Nginx FastCGI cache but does not create Cloudflare Cache Rules or automatically purge Cloudflare's edge cache.

When creating Cloudflare rules, avoid caching sensitive or user-specific paths such as:

```text
/wp-admin/
/wp-login.php
/adminer
cart
checkout
account pages
```

WordPress plugins that purge Cloudflare through its API are outside the standard SlickStack configuration and require their own credentials and security review.

## Interaction with other SlickStack components

### Nginx logs

`CLOUDFLARE_REAL_IPS` determines whether access logs normally show visitor addresses or Cloudflare proxy addresses.

### Nginx rate limiting

Real-IP restoration is important because SlickStack's rate zones are keyed by the remote address.

### Fail2ban

The current Fail2ban Nginx filters assume the first address in the Nginx access log is the visitor address. Incorrect real-IP handling can cause Cloudflare proxy addresses to be evaluated or banned instead.

Fail2ban is planned for removal from SlickStack, but this interaction matters while it remains installed. See [Fail2ban](fail2ban.md).

### Adminer

Adminer's Nginx rate limit also uses the remote address. Cloudflare proxying does not replace Adminer authentication, and a randomized URL does not make public database administration risk-free. See [Adminer](adminer.md).

### Certbot

Single-site certificates normally use HTTP webroot validation. Multisite wildcard certificates use Cloudflare DNS validation and require the managed credentials file.

Incorrect records, strict origin controls, or missing credentials can prevent issuance.

### Iptables

Iptables sees the original network peer address, which is normally a Cloudflare proxy when traffic is proxied. Nginx real-IP restoration happens later at the HTTP layer and does not rewrite the source address seen by Iptables.

That makes Iptables or the provider firewall a better layer for a future Cloudflare network allowlist. See [Iptables](iptables.md).

## Managed files

Cloudflare-related managed files can include:

```text
/var/www/sites/includes/cloudflare.conf
/var/www/sites/includes/allowed-ips.conf
/var/www/sites/includes/auth-origin.conf
/var/www/sites/includes/origin-gate.conf
/var/www/certs/cloudflare.pem
/var/www/meta/cloudflare.ini
```

Direct edits can be replaced by `ss-install-nginx-config`, `ss-encrypt-certbot`, permission resets, or the complete installer.

Persistent behavior should be controlled through supported `ss-config` values. Current unsupported gaps should be fixed in the SlickStack source rather than patched only on one server.

## Verification

Useful checks include:

```bash
sudo nginx -t
systemctl status nginx
grep '^CLOUDFLARE_' /var/www/ss-config
grep -c 'set_real_ip_from' /var/www/sites/includes/cloudflare.conf
grep -c '^allow ' /var/www/sites/includes/allowed-ips.conf
grep 'ssl_verify_client' /var/www/sites/includes/auth-origin.conf
ls -l /var/www/certs/cloudflare.pem
ls -l /var/www/meta/cloudflare.ini
tail -n 100 /var/www/logs/nginx-error.log
tail -n 50 /var/www/logs/nginx-access.log
```

Check the public route:

```bash
curl -I https://example.com
```

When strict origin restrictions are enabled, a direct origin test can fail even while the public Cloudflare route works.

## Troubleshooting

### Redirect loop

Confirm Cloudflare is not using Flexible mode. SlickStack redirects HTTP to HTTPS and expects Cloudflare to connect to the origin over HTTPS.

Use Full with the self-signed OpenSSL certificate or Full (strict) with a valid origin certificate.

### Error 521

Cloudflare reached the origin network but the web server refused the connection.

Check:

```bash
systemctl status nginx
ss -lntp | grep -E ':80|:443'
tail -n 100 /var/www/logs/nginx-error.log
```

Also confirm Cloudflare ranges are not being rejected by stale Nginx, Iptables, provider-firewall, or external-security rules.

### Error 522

Cloudflare timed out connecting to the origin.

Check:

- the Cloudflare DNS record points to the current origin address
- the cloud provider firewall allows ports 80 and 443
- Nginx and the server are responsive
- current Cloudflare ranges are not blocked or rate-limited
- the server is not overloaded

### Error 525

The TLS handshake between Cloudflare and Nginx failed.

Check:

```bash
sudo nginx -t
openssl s_client -connect 127.0.0.1:443 -servername example.com
```

Verify Nginx is listening on port 443 and has a usable certificate, key, protocol, and cipher configuration.

### Error 526

Full (strict) could not validate the certificate presented by the origin.

Confirm:

- the certificate is unexpired
- the hostname is included
- the chain is complete
- `SSL_TYPE` activates the intended certificate
- `/var/www/certs/fullchain.pem` and its private-key symlink resolve correctly for Certbot

Use Full temporarily with SlickStack's self-signed certificate, or install and activate a certificate suitable for Full (strict).

### Site returns 403 after enabling Cloudflare-only access

Disable the current setting:

```bash
CLOUDFLARE_IPS_ONLY="false"
```

Then run:

```bash
sudo bash /var/www/ss-install-nginx-config
```

When `CLOUDFLARE_REAL_IPS="true"`, the current Nginx access allowlist evaluates the restored visitor address and can reject legitimate Cloudflare traffic.

### Public site returns 403 after enabling Authenticated Origin Pulls

Confirm Authenticated Origin Pulls is enabled in the Cloudflare dashboard and that the standard production server block includes the origin gate.

Check:

```bash
grep 'ssl_verify_client' /var/www/sites/includes/auth-origin.conf
grep 'origin-gate' /var/www/sites/production.conf
ls -l /var/www/certs/cloudflare.pem
```

Remember that current staging, development, and Multisite templates do not enforce the same gate.

### Logs show Cloudflare addresses

Confirm:

```bash
grep '^CLOUDFLARE_REAL_IPS=' /var/www/ss-config
grep 'real_ip_header' /var/www/sites/includes/cloudflare.conf
grep -c 'set_real_ip_from' /var/www/sites/includes/cloudflare.conf
sudo nginx -t
```

Then confirm the request actually passed through a proxied Cloudflare DNS record.

### Wildcard Certbot request fails

Confirm:

```bash
ls -l /var/www/meta/cloudflare.ini
sudo certbot certificates
```

The current managed credentials file requires the account email and Global API Key format. An API token pasted into `CLOUDFLARE_API_KEY` will not be converted into the token field expected by the Certbot Cloudflare plugin.

## Scope

The standard SlickStack Cloudflare integration assumes:

- Cloudflare hosts the public DNS zone
- public website records are proxied
- Nginx receives HTTP and HTTPS traffic from Cloudflare
- the administrator configures the Cloudflare dashboard
- SlickStack manages one primary domain per server
- Cloudflare ranges are downloaded from Cloudflare's public endpoints
- `CF-Connecting-IP` supplies the visitor address
- Authenticated Origin Pulls are optional and currently limited by server-block coverage
- Certbot wildcard validation uses the current Global API Key credentials format
- `CLOUDFLARE_IPS_ONLY` remains disabled until its real-IP conflict is corrected

Cloudflare Workers, Pages, Tunnels, Load Balancing, Spectrum, Argo, SaaS custom hostnames, account-specific Authenticated Origin Pull certificates, automatic DNS management, API-token generation, WAF rule deployment, Cache Rule deployment, Logpush, edge cache purging, and multi-origin failover are outside the standard SlickStack configuration.

## References

- [Cloudflare SSL/TLS encryption modes](https://developers.cloudflare.com/ssl/origin-configuration/ssl-modes/)
- [Cloudflare Full mode](https://developers.cloudflare.com/ssl/origin-configuration/ssl-modes/full/)
- [Cloudflare Full (strict) mode](https://developers.cloudflare.com/ssl/origin-configuration/ssl-modes/full-strict/)
- [Restoring original visitor IPs](https://developers.cloudflare.com/support/troubleshooting/restoring-visitor-ips/restoring-original-visitor-ips/)
- [Authenticated Origin Pulls](https://developers.cloudflare.com/ssl/origin-configuration/authenticated-origin-pull/)
- [Protecting the origin server](https://developers.cloudflare.com/fundamentals/security/protect-your-origin-server/)
- [Cloudflare 5xx errors](https://developers.cloudflare.com/support/troubleshooting/http-status-codes/cloudflare-5xx-errors/)
- [Nginx real-IP module](https://nginx.org/en/docs/http/ngx_http_realip_module.html)
- [Nginx access module](https://nginx.org/en/docs/http/ngx_http_access_module.html)
- [Certbot DNS Cloudflare credentials](https://certbot-dns-cloudflare.readthedocs.io/en/stable/)
