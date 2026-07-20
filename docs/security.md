# Security

SlickStack applies a layered security model across Ubuntu, SSH, Nginx, TLS, Cloudflare integration, Iptables, file permissions, WordPress, and scheduled maintenance.

These layers reduce common risks, but they do not turn a server into a fully managed security service. Administrators remain responsible for credentials, Cloudflare configuration, WordPress users and extensions, backups, update decisions, incident response, and any external monitoring or compliance requirements.

## Table of Contents

- [Security model](#security-model)
- [Recommended baseline](#recommended-baseline)
- [SSH access](#ssh-access)
- [Network firewall](#network-firewall)
- [Fail2ban](#fail2ban)
- [Cloudflare boundary](#cloudflare-boundary)
- [TLS and security headers](#tls-and-security-headers)
- [Nginx request controls](#nginx-request-controls)
- [Credentials and ss-config](#credentials-and-ss-config)
- [File ownership and permissions](#file-ownership-and-permissions)
- [WordPress security](#wordpress-security)
- [Adminer exposure](#adminer-exposure)
- [Staging and development data](#staging-and-development-data)
- [Logs, monitoring, and alerting](#logs-monitoring-and-alerting)
- [Updates and patching](#updates-and-patching)
- [Backups and recovery](#backups-and-recovery)
- [Incident response](#incident-response)
- [Verification commands](#verification-commands)
- [Managed security boundaries](#managed-security-boundaries)
- [Current limitations](#current-limitations)

## Security model

The standard SlickStack security boundary assumes:

- one primary WordPress site per server
- a normal Ubuntu LTS virtual machine, preferably KVM
- Nginx and PHP-FPM running locally
- local MySQL unless remote mode is explicitly configured
- Cloudflare proxying in front of the public site
- only trusted administrators receiving sudo access
- only trusted users or services receiving SFTP access
- generated configuration remaining under SlickStack management

Security comes from combining several controls rather than relying on one feature:

1. Cloudflare can absorb and filter public traffic at the edge.
2. Iptables limits which inbound network services are reachable.
3. Nginx terminates HTTPS, applies headers, rate limits sensitive routes, and controls PHP execution.
4. SSH restricts shell access to approved groups and disables direct root login.
5. SlickStack permissions separate root-owned configuration from web-writable content.
6. WordPress configuration and MU plugins reduce selected application-level risks.
7. Logs, timestamps, backups, and service recovery support troubleshooting and recovery.

A failure or misconfiguration in one layer can weaken the others. Verify the complete path from Cloudflare to Nginx, PHP-FPM, WordPress, MySQL, and the filesystem.

## Recommended baseline

For a normal production server:

- use Ubuntu 24.04 LTS on a supported VM
- keep provider-console access available
- create a separate sudo user and verify it before closing the original session
- use a long unique sudo password or correctly tested SSH keys
- keep direct root SSH login disabled
- keep the managed Iptables rules active
- proxy public DNS records through Cloudflare
- use Cloudflare Full mode with the default self-signed certificate
- move to Full (strict) after activating a valid Certbot, Origin CA, or third-party certificate
- leave Cloudflare real-IP restoration enabled
- leave the current Cloudflare-only Nginx allowlist disabled until its incompatibility is fixed
- disable public Adminer access when it is not actively needed
- protect or disable staging and development environments
- keep `ss-config`, certificates, keys, and database dumps private
- maintain verified off-server backups
- review Ubuntu, WordPress, plugin, theme, and dependency updates regularly
- use external uptime and security monitoring for important sites

## SSH access

The managed Ubuntu 24.04 SSH configuration listens on TCP port `22` over IPv4 and permits only members of:

```text
sudo-ssh
sftp-only
```

Direct root SSH login is disabled:

```text
PermitRootLogin no
```

Public-key authentication remains enabled. Password authentication depends on the generated value selected from `ss-config`.

When:

```bash
SSH_KEYS="false"
```

password authentication remains available in addition to public keys.

When:

```bash
SSH_KEYS="true"
```

SlickStack disables password authentication and expects a working authorized key.

Keep the original SSH or provider-console session open while changing authentication. Verify a second login and confirm that `sudo` works before disconnecting the recovery session.

See [Ubuntu](ubuntu.md) for the complete SSH, user, key, and SFTP behavior.

### Sudo access

The configured sudo user is a full server administrator. SlickStack grants broad passwordless sudo access rather than a narrow command allowlist.

Compromise of that account should therefore be treated as compromise of the entire server, including:

- WordPress files and database credentials
- `ss-config`
- TLS private keys
- backup credentials
- Cloudflare credentials
- MySQL data
- root-run SlickStack scripts and cron jobs

Do not share the sudo account with clients, contractors, or automated services that only require file access.

### SFTP access

The SFTP user is jailed beneath:

```text
/var/www
```

It has no normal shell and cannot use SSH forwarding. This is safer than providing sudo access, but it is not harmless.

An SFTP user who can modify writable WordPress paths may still be able to:

- replace plugin or theme code
- upload malicious PHP into a writable executable path
- damage media or application files
- expose secrets found within accessible directories
- disrupt the site through deletion or permission changes

Share SFTP only with trusted parties and remove access when the task is complete.

### SSH IP restriction limitation

`SSH_RESTRICT_IP` and `SSH_IPV4` remain in `ss-config`, but the active Ubuntu 24.04 SSH template does not currently contain the expected replacement placeholder. The setting does not presently enforce an SSH source-IP allowlist.

Use a provider firewall, cloud firewall, VPN, or deliberately maintained Iptables rule when SSH must be restricted by source address.

## Network firewall

SlickStack installs persistent IPv4 and IPv6 Iptables rules.

The current IPv4 policy:

- drops unsolicited inbound traffic by default
- drops forwarded traffic by default
- allows outbound traffic
- allows loopback traffic
- allows established and related connections
- allows new HTTP and HTTPS connections on ports `80` and `443`
- allows essential ICMP diagnostics
- permits SSH on port `22` through global and per-source rate limits
- logs remaining denied packets with rate limiting

This reduces the exposed network surface, but it is not an application firewall. Iptables does not understand WordPress accounts, vulnerable plugins, malicious HTTP payloads, stolen cookies, or SQL injection.

The standard rules also allow public connections to ports `80` and `443` from any source. Cloudflare-only origin restriction, when desired, must be implemented separately and verified carefully.

See [Iptables](iptables.md).

## Fail2ban

Fail2ban currently remains installed and includes SSH and Nginx-oriented jails. It is transitional and planned for removal in favor of a simpler Iptables-centered approach.

Do not make Fail2ban the only protection for:

- SSH brute force
- WordPress login attacks
- Adminer discovery
- application abuse
- denial-of-service traffic

Use strong authentication, Iptables, Nginx rate limits, Cloudflare controls, and external monitoring together.

See [Fail2ban](fail2ban.md).

## Cloudflare boundary

SlickStack manages origin-side Cloudflare integration, but it does not manage the Cloudflare account or dashboard.

It does not automatically create or maintain:

- DNS records
- proxy status
- WAF rules
- bot controls
- rate-limit rules
- cache rules
- redirects
- account members
- API-token permissions
- nameserver delegation

DNS-only records connect users directly to the origin and bypass Cloudflare's WAF, DDoS mitigation, TLS edge, and other proxy controls. Remove unnecessary records that expose the origin address.

### Recommended origin settings

The current baseline is:

```bash
CLOUDFLARE_REAL_IPS="true"
CLOUDFLARE_IPS_ONLY="false"
CLOUDFLARE_AUTHENTICATED_ORIGIN="false"
```

Real-IP restoration trusts `CF-Connecting-IP` only from generated Cloudflare network ranges. Keep those ranges current so logs, rate limits, Fail2ban, and applications see accurate visitor addresses.

### Cloudflare-only access limitation

Do not currently combine:

```bash
CLOUDFLARE_IPS_ONLY="true"
CLOUDFLARE_REAL_IPS="true"
```

The current Nginx access rules evaluate the restored visitor address rather than the original Cloudflare network peer. Legitimate proxied visitors can therefore fail the Cloudflare range allowlist and be denied.

Treat `CLOUDFLARE_IPS_ONLY` as experimental until this implementation is changed. A provider firewall or carefully designed rule based on the actual connection peer is a safer origin restriction.

### Authenticated Origin Pulls

Authenticated Origin Pulls verify that a TLS client presents Cloudflare's expected client certificate before reaching the standard production origin.

Enable this only after:

- Cloudflare proxying is confirmed
- the dashboard feature is active
- the origin include is installed
- direct origin and certificate-recovery needs are understood
- a provider console remains available

A mismatch can make the production origin unavailable even when DNS and the server are otherwise healthy.

See [Cloudflare](cloudflare.md).

## TLS and security headers

SlickStack is HTTPS-only. HTTP requests are redirected to HTTPS, and the managed Nginx configuration adds several security-related headers, including:

```text
Strict-Transport-Security
X-Frame-Options
X-Content-Type-Options
Referrer-Policy
```

The current HSTS value includes subdomains and the `preload` token. Once a browser has learned HSTS, certificate or HTTPS failures can prevent users from bypassing the warning.

Before exposing a domain broadly:

- verify every required subdomain supports HTTPS
- verify the active certificate covers production and enabled environments
- avoid adding a domain to the browser preload list until long-term HTTPS support is certain
- keep certificate-renewal and recovery access tested

The template also contains a conditional Content Security Policy for standard non-Multisite setups and a customizable Permissions Policy include. Test application compatibility before making these policies stricter.

### Certificate choices

The normal choices are:

| Certificate | Cloudflare mode | Notes |
| :-- | :-- | :-- |
| Self-signed OpenSSL | Full | Default origin encryption; not normally browser-trusted directly |
| Certbot / Let's Encrypt | Full (strict) | Publicly trusted when issuance and renewal are working |
| Cloudflare Origin CA | Full (strict) | Trusted by Cloudflare, not normal direct browsers |
| Other valid third-party certificate | Full (strict) | Administrator manages issuance and replacement |

Never publish certificate private keys. Verify ownership and permissions after copying any third-party certificate.

See [SSL Certificates](ssl.md).

## Nginx request controls

Nginx provides application-aware controls that Iptables cannot provide, including:

- global request and connection limits
- PHP request limits
- WordPress login limits
- WordPress search limits
- Adminer limits
- cache bypass for logged-in and sensitive requests
- disabled directory autoindexing
- controlled PHP routing
- hidden Nginx version tokens

These controls reduce basic abuse and accidental overload. They are not a replacement for a Cloudflare WAF, vulnerability patching, secure application code, or capacity planning.

Aggressive limits can also block legitimate traffic. Verify checkout, login, API, webhook, import, search, and administrative workflows after changing rate-limit settings.

See [Nginx](nginx.md) and [Caching](caching.md).

## Credentials and `ss-config`

`/var/www/ss-config` contains sensitive values and is sourced as Bash by root-run scripts.

The active file should remain:

```text
root:root 0700
```

It can contain:

- sudo and SFTP credentials
- guest credentials
- MySQL credentials
- Cloudflare credentials
- backup destinations and secrets
- domain and infrastructure details
- pilot-file configuration

Do not:

- publish it
- attach it to public issues
- paste it into chat without redaction
- store it in a public repository
- make it readable by the web server
- insert untrusted shell code

Because the file is executed as Bash, command substitutions, functions, or malicious shell statements can run as root.

### Pilot-file risk

`SS_PILOT_FILE` is downloaded and sourced by a root-run process. Control of the pilot file can become remote root-code execution across every connected SlickStack server.

Use it only from a private, tightly controlled HTTPS location. Protect the hosting account with strong authentication and review changes before distribution.

### Stack overview output

`ss-stack-overview` can display passwords, database credentials, private URLs, and key material. A support request should contain only the minimum redacted lines required to diagnose the issue.

See [SS-Config](ss-config.md) and [Monitoring](monitoring.md).

## File ownership and permissions

SlickStack applies a managed ownership and mode model across:

- root-run scripts and cron jobs
- `ss-config`
- certificates and private keys
- backup files
- service configuration
- WordPress Core
- `wp-config.php`
- plugins, themes, uploads, and MU plugins
- logs and cache directories

Scheduled permission resets and full installations can replace direct `chown` or `chmod` changes.

Do not solve an access problem by recursively applying broad modes such as:

```bash
chmod -R 777 /var/www
```

That can expose credentials, allow application code replacement, and break the intended separation between root, `www-data`, the sudo user, and the SFTP user.

Use the documented permission commands and investigate the exact path, owner, group, and process identity instead.

See [Permissions](permissions.md).

## WordPress security

SlickStack manages WordPress Core files, generated configuration, selected constants, WP-Cron behavior, permissions, and MU plugins. It does not remove the need to secure WordPress itself.

Administrators remain responsible for:

- strong administrator passwords
- least-privilege WordPress roles
- two-factor authentication when appropriate
- trusted and maintained plugins and themes
- removing abandoned extensions
- reviewing new administrator accounts
- protecting API keys and webhook secrets
- safe WooCommerce and membership configuration
- application-level backups and recovery testing

### File modification settings

The current sample allows these administrator choices:

```bash
WP_DISALLOW_FILE_EDIT="false"
WP_DISALLOW_FILE_MODS="false"
WP_ALLOW_UNFILTERED_UPLOADS="true"
```

Those defaults favor compatibility and administrator control rather than maximum lockdown.

For higher-risk or tightly managed sites, consider whether to:

- disable the built-in plugin and theme editors
- prevent dashboard-driven code installation and updates
- disallow unfiltered uploads

Test changes carefully. `WP_DISALLOW_FILE_MODS="true"` can also block normal updates and installation workflows, transferring more maintenance responsibility to the administrator.

### Plugins and themes

A secure Nginx and Ubuntu configuration cannot compensate for a vulnerable WordPress plugin, theme, or custom PHP file.

Before installing an extension, review:

- current maintenance status
- update history
- required permissions
- public vulnerabilities
- code source and ownership
- data-handling behavior

SlickStack's plugin blacklist and MU plugins are additional controls, not guarantees that every installed extension is safe.

See [WordPress](wordpress.md) and [MU Plugins](mu-plugins.md).

## Adminer exposure

Adminer provides direct database administration and should be treated as a sensitive management interface.

The current defaults enable it:

```bash
ADMINER_PUBLIC="true"
```

Production uses a randomized URL, but secrecy of a URL is not strong authentication. Adminer still requires database credentials, and the route has Nginx rate limiting, but the safest state when it is not needed is:

```bash
ADMINER_PUBLIC="false"
```

A current limitation is that enabled staging and development server blocks expose the predictable path:

```text
/adminer
```

The randomized production path does not protect those hosts. Keep test environments protected and disable Adminer when it is not actively required.

See [Adminer](adminer.md).

## Staging and development data

Staging and development can contain copies of production databases, users, orders, forms, messages, and configuration secrets.

Both environments receive noindex headers and can use shared HTTP Basic Authentication, but those controls do not anonymize copied data.

Important boundaries include:

- staging can be automatically overwritten from production
- staging maps media requests to the production uploads tree
- development sync can copy production uploads
- production push commands replace the production database
- guest credentials are shared between protected test environments

Do not provide broad access to an environment merely because search engines are told not to index it. Remove or anonymize sensitive data when contractors or external testers do not require it.

See [Staging & Development](staging-dev.md).

## Logs, monitoring, and alerting

SlickStack records Nginx, PHP, MySQL, WordPress, Fail2ban, Iptables, and script activity in several locations. It also contains automatic service-recovery listeners for PHP-FPM, Nginx, and local MySQL.

These features support availability and diagnosis, but they are not a complete security-monitoring system.

SlickStack does not provide built-in:

- centralized log shipping
- SIEM correlation
- malware scanning
- file-integrity alerting
- rootkit detection
- user-behavior analytics
- external uptime probes
- incident paging
- vulnerability scanning
- compliance reporting

Use external services when the site's risk or business importance requires them.

### Log preservation

A full `ss-install` truncates SlickStack-managed `.log` files. Do not rerun the installer during a suspected compromise before preserving relevant evidence.

Copy logs and collect systemd journals first. A recent timestamp proves that a script started, not that it completed successfully or that the resulting state is secure.

See [Logging](logging.md) and [Monitoring](monitoring.md).

## Updates and patching

Security depends on keeping Ubuntu packages, PHP, Nginx, MySQL, WordPress, plugins, themes, and SlickStack scripts current.

The update commands have different scopes:

- `ss-check` refreshes SlickStack scripts and cron wrappers
- `ss-update-config` migrates `ss-config`
- `ss-update-modules` upgrades Ubuntu packages and the kernel
- `ss-install` reconciles the complete managed stack

A package update can require service restarts or a later reboot. A full installation also rewrites configuration, truncates logs, purges caches, and may overwrite staging.

Use verified backups and a maintenance window for important production changes.

See [Updates](updates.md) and [Installation](installation.md).

## Backups and recovery

Security includes the ability to recover from deletion, corruption, compromise, ransomware, operator mistakes, and failed updates.

Maintain:

- recent local database dumps
- off-server database backups
- off-server file backups
- protected backup credentials
- tested restore procedures
- retention that preserves a version from before the incident

Backups stored only on the same server can be deleted or encrypted by the same compromise. A successful upload is not proof that the backup is complete or restorable.

See [Backups](backups.md).

## Incident response

When compromise is suspected:

1. preserve provider-console access
2. avoid destroying logs or evidence
3. record the current time, symptoms, processes, connections, and failed services
4. take provider snapshots when appropriate
5. isolate the origin or place the site behind a controlled maintenance response
6. rotate Cloudflare, sudo, SFTP, database, WordPress, backup, API, and application credentials
7. review administrator accounts, SSH keys, cron jobs, systemd units, PHP files, plugins, themes, MU plugins, and database changes
8. rebuild from trusted sources or restore a verified clean backup
9. patch the original entry point before returning the site to service
10. continue monitoring for renewed access

Do not assume that rerunning `ss-install` removes malware. It can refresh managed files, but it does not inspect every custom file, upload, database row, user account, scheduled task, or external integration.

## Verification commands

Review listening services:

```bash
sudo ss -lntup
```

Check firewall rules:

```bash
sudo iptables -S
sudo ip6tables -S
```

Validate SSH configuration:

```bash
sudo sshd -t
sudo sshd -T | grep -E 'port|permitrootlogin|passwordauthentication|pubkeyauthentication|allowgroups'
```

Validate Nginx and inspect security headers:

```bash
sudo nginx -t
curl -I "https://example.com"
```

Check important ownership and permissions:

```bash
sudo stat -c '%U:%G %a %n' /var/www/ss-config
sudo stat -c '%U:%G %a %n' /var/www/certs/keys/* 2>/dev/null
sudo stat -c '%U:%G %a %n' /var/www/html/wp-config.php
```

Check failed services and recent authentication events:

```bash
systemctl --failed
journalctl -u ssh --no-pager -n 100
journalctl -u nginx --no-pager -n 100
```

Review WordPress administrators:

```bash
sudo -u www-data wp --path=/var/www/html user list --role=administrator
```

Replace the example domain before using the `curl` command. Review output before sharing it because usernames, addresses, file paths, and infrastructure details can be sensitive.

## Managed security boundaries

SlickStack owns its generated security configuration. Direct changes may be replaced by module installers, scheduled tasks, `ss-check`, `ss-worker`, or `ss-install`.

Common managed files include:

- `/etc/ssh/sshd_config`
- `/etc/sudoers`
- `/etc/iptables/rules.v4`
- `/etc/iptables/rules.v6`
- `/etc/nginx/nginx.conf`
- generated Nginx server blocks and includes
- TLS certificates and permission files
- PHP-FPM and MySQL configuration
- generated WordPress configuration
- root cron and SlickStack scripts

Persistent changes should use:

- supported `ss-config` options
- approved Nginx include files
- reserved custom cron files
- approved WordPress custom files or MU plugins
- provider firewalls and Cloudflare controls
- external monitoring or security services
- a reviewed source-level SlickStack change

See the component-specific guide before changing a managed security control.

## Current limitations

Important current limitations include:

- `SSH_RESTRICT_IP` does not presently alter the active Ubuntu 24.04 SSH template
- `CLOUDFLARE_IPS_ONLY` conflicts with default real-IP restoration
- Adminer is enabled by default and uses predictable `/adminer` routes on staging and development
- Fail2ban remains transitional and is planned for removal
- passwordless sudo gives the sudo user complete administrative control
- `ss-stack-overview` can expose credentials
- pilot files are sourced as root shell code
- full installation truncates SlickStack-managed logs
- no built-in malware scanner, SIEM, external alerting, or transactional rollback exists

These limitations do not necessarily make a normal SlickStack server unsafe, but they define where additional operational controls or external services may be required.
