# SSL Certificates

SlickStack is HTTPS-only and manages the certificate files and Nginx SSL include used by the production, staging, and development sites.

A self-signed OpenSSL certificate is generated as the default and fallback certificate. SlickStack can also activate certificates issued through Certbot or certificate files supplied by a third party.

## Table of Contents

- [Certificate modes](#certificate-modes)
- [Shared SSL settings](#shared-ssl-settings)
- [Default OpenSSL certificate](#default-openssl-certificate)
- [Cloudflare and self-signed certificates](#cloudflare-and-self-signed-certificates)
- [Certbot certificates](#certbot-certificates)
- [Domain validation](#domain-validation)
- [Multisite wildcard certificates](#multisite-wildcard-certificates)
- [Requesting and renewing certificates](#requesting-and-renewing-certificates)
- [Third-party certificates](#third-party-certificates)
- [Certificate permissions](#certificate-permissions)
- [DH parameters](#dh-parameters)
- [Troubleshooting](#troubleshooting)
- [Scope](#scope)

## Certificate modes

The active certificate mode is controlled in `ss-config`:

```bash
SSL_TYPE="openssl"
```

Supported values are:

| Value | Active certificate |
| :------------- | :---------- |
| `openssl` | SlickStack-generated self-signed certificate |
| `certbot` | Let’s Encrypt certificate requested through Certbot |
| `thirdparty` | Certificate and private key supplied manually |

The installer keeps only one corresponding Nginx SSL include active under `/var/www/sites/includes/`.

## Shared SSL settings

The following `ss-config` settings apply to the generated Nginx SSL include:

```bash
SSL_PROTOCOLS="TLSv1.2 TLSv1.3"
SSL_CIPHERS="..."
SSL_BUFFER_SIZE="16k"
SSL_SESSION_CACHE="shared:SSL:64m"
SSL_SESSION_TIMEOUT="720m"
```

SlickStack currently enables TLS 1.2 and TLS 1.3 by default, disables session tickets, and uses the configured session cache, timeout, and buffer size.

Direct edits to the generated SSL include files may be replaced the next time `ss-install` or `ss-install-nginx-config` runs. Persistent changes should use supported `ss-config` settings whenever possible.

## Default OpenSSL certificate

Every Nginx configuration installation regenerates the SlickStack self-signed certificate before attempting Certbot. This ensures Nginx always has a usable fallback certificate even when a public certificate is missing or issuance fails.

The generated files are:

```text
/var/www/certs/slickstack.crt
/var/www/certs/keys/slickstack.key
```

The certificate:

- uses a 4096-bit RSA key
- uses SHA-256
- is valid for 10 years
- includes configured production, staging, and development domains where applicable
- may include `localhost` and `127.0.0.1`
- may optionally include detected public server IP addresses

The relevant settings are:

```bash
OPENSSL_CERT_INCLUDE_DOMAINS="true"
OPENSSL_CERT_INCLUDE_IPS="false"
OPENSSL_CERT_INCLUDE_LOCALHOST="true"
```

Including public IP addresses is disabled by default because it exposes the origin addresses inside the certificate. Domain inclusion should normally remain enabled for production sites.

To regenerate the self-signed certificate manually, run:

```bash
sudo bash /var/www/ss-encrypt-openssl
```

The OpenSSL script replaces the existing self-signed certificate and key each time it runs.

## Cloudflare and self-signed certificates

A self-signed origin certificate is not trusted directly by normal web browsers. SlickStack therefore expects Cloudflare to be active in front of the origin when the default OpenSSL mode is used.

Cloudflare can establish an encrypted connection to an origin using the self-signed certificate in Full mode. Full (strict) requires a certificate Cloudflare can validate, such as a valid Let’s Encrypt certificate or another suitable origin certificate.

The generated OpenSSL certificate is also added to the server’s local CA trust store so local tools such as `curl` and WP-CLI can trust HTTPS requests to the SlickStack origin.

## Certbot certificates

SlickStack installs Certbot with the Nginx and Cloudflare DNS support available for the detected Ubuntu release.

To use Let’s Encrypt, set:

```bash
SSL_TYPE="certbot"
```

Then run:

```bash
sudo bash /var/www/ss-install-nginx-config
```

During that workflow, SlickStack:

1. regenerates the OpenSSL fallback certificate
2. temporarily keeps OpenSSL active when no Let’s Encrypt certificate exists
3. requests the required domains through Certbot
4. creates stable certificate symlinks under `/var/www/certs/`
5. activates the Let’s Encrypt Nginx include only when the expected certificate exists
6. falls back to OpenSSL when certificate issuance is unsuccessful
7. reloads or restarts Nginx with the selected configuration

The Certbot request uses the certificate name `slickstack` and stores the source files under:

```text
/etc/letsencrypt/live/slickstack/
```

SlickStack exposes stable paths to Nginx:

```text
/var/www/certs/cert.pem
/var/www/certs/chain.pem
/var/www/certs/fullchain.pem
/var/www/certs/keys/privkey.pem
```

These are symbolic links to the current Certbot certificate files. Nginx uses `fullchain.pem` and `privkey.pem` when Certbot mode is active.

## Domain validation

Normal single-site certificates use Certbot’s webroot validation against:

```text
/var/www/html/.well-known/acme-challenge/
```

The requested certificate includes the production domain and may also include its `www` alternative plus enabled staging and development subdomains.

The domain must resolve correctly and the validation path must remain reachable during certificate issuance. Cloudflare proxying, redirects, firewall restrictions, or incorrect DNS can prevent validation.

SlickStack does not support nested subdomains such as `www.blog.example.com` in its normal certificate request logic.

## Multisite wildcard certificates

WordPress Multisite using subdomains requires a wildcard certificate. SlickStack requests this through the Certbot Cloudflare DNS plugin.

The current workflow expects both of these `ss-config` values:

```bash
CLOUDFLARE_EMAIL="account@example.com"
CLOUDFLARE_API_KEY="cloudflare-api-key"
```

When both exist, SlickStack generates:

```text
/var/www/meta/cloudflare.ini
```

The file is owned by `root`, uses permission mode `0600`, and is passed to Certbot for DNS validation.

Ubuntu 18.04 does not install the Cloudflare DNS plugin in the current SlickStack package workflow, so the managed wildcard request is not supported there.

## Requesting and renewing certificates

To request or refresh the Certbot certificate manually, run:

```bash
sudo bash /var/www/ss-encrypt-certbot
```

This command requests the relevant domains, updates the SlickStack symlinks, resets certificate permissions, and reloads Nginx. It does not by itself change the configured `SSL_TYPE` or install a different Nginx SSL include.

To switch an existing server from OpenSSL to Certbot, set `SSL_TYPE="certbot"` and run `ss-install-nginx-config` after the certificate can be issued successfully.

Certbot checks whether the named certificate needs renewal. SlickStack schedules `ss-encrypt-certbot` weekly by default through:

```bash
INTERVAL_SS_ENCRYPT_CERTBOT="weekly"
```

The supported scheduled intervals are weekly, half-monthly, and monthly. The OpenSSL regeneration schedule is disabled by default because the main Nginx configuration installer already regenerates its fallback certificate.

The `CERTBOT_REQUEST` setting currently exists in `ss-config`, but active SlickStack scripts do not read it. Changing that value does not currently enable or disable Certbot requests; `SSL_TYPE` controls which certificate Nginx activates.

## Third-party certificates

To use certificate files obtained outside SlickStack, set:

```bash
SSL_TYPE="thirdparty"
```

Provide the files at:

```text
/var/www/certs/thirdparty.pem
/var/www/certs/keys/thirdparty.key
```

Then run:

```bash
sudo bash /var/www/ss-install-nginx-config
```

SlickStack installs the third-party Nginx SSL include and removes the conflicting OpenSSL and Certbot include files.

SlickStack does not request, validate, renew, replace, or monitor third-party certificates. The administrator is responsible for supplying a correct certificate chain and matching private key before expiration.

## Certificate permissions

SlickStack maintains these certificate directory permissions:

```text
/var/www/certs/       root:root 0755
/var/www/certs/keys/  root:root 0700
```

Public certificate and DH parameter files use mode `0644`. Private-key files use mode `0600`.

The Cloudflare credentials file also uses mode `0600`. These permissions are restored by the Nginx configuration permissions script during installation and certificate operations.

## DH parameters

SlickStack generates a 2048-bit Diffie-Hellman parameter file when it does not already exist:

```text
/var/www/certs/dhparam.pem
```

The Certbot Nginx include references this file. SlickStack retains it between installations instead of regenerating it every time.

## Troubleshooting

Useful checks include:

```bash
sudo nginx -t
systemctl status nginx
openssl x509 -in /var/www/certs/slickstack.crt -noout -subject -issuer -dates
openssl x509 -in /var/www/certs/fullchain.pem -noout -subject -issuer -dates
sudo certbot certificates
```

For Certbot issuance failures, verify that:

- the production domain and requested alternatives resolve to the intended server or proxy
- the ACME challenge path is reachable for webroot validation
- Cloudflare credentials are correct for wildcard validation
- `/etc/letsencrypt/live/slickstack/` contains the expected files
- the SlickStack symlinks under `/var/www/certs/` resolve correctly
- `SSL_TYPE` matches the certificate mode intended for Nginx
- `sudo nginx -t` succeeds before reloading Nginx

When `SSL_TYPE="certbot"` is configured but the expected Certbot certificate is missing, the Nginx installer intentionally reverts to the OpenSSL include rather than leaving Nginx with broken certificate paths.

## Scope

The standard SlickStack SSL design assumes:

- HTTPS-only Nginx sites
- one certificate mode shared by the managed site environments
- one self-signed fallback certificate
- one Certbot certificate named `slickstack`
- stable certificate paths under `/var/www/certs/`
- Cloudflare DNS validation for Multisite wildcard certificates

Multiple independently managed certificates, custom Certbot certificate names, custom ACME clients, custom challenge handlers, automated third-party certificate renewal, hardware security modules, and external certificate-management platforms are outside the standard managed configuration.