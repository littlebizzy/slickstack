# Iptables

SlickStack uses hardcoded Iptables rules as the server's local network firewall. The policy is intentionally small and predictable for a standard single-server WordPress stack rather than exposing every firewall option through `ss-config`.

The main rule templates are:

```text
modules/iptables/rules-v4.txt
modules/iptables/rules-v6.txt
```

They are installed as:

```text
/etc/iptables/rules.v4
/etc/iptables/rules.v6
```

Both installed files use mode `0600`.

## Firewall layers

Iptables is only one security layer:

```text
Internet
   |
   +--> provider firewall or security group
   |
   +--> Cloudflare, when proxied
   |
   +--> SlickStack Iptables rules
   |
   +--> Nginx request and connection limits
   |
   `--> WordPress and application authorization
```

These controls are related but not interchangeable:

- provider firewalls filter traffic before it reaches the server
- Cloudflare filters proxied HTTP and HTTPS traffic at the edge
- Iptables filters traffic on the server by protocol, address, port, and connection state
- Nginx limits web requests and connections after traffic reaches the web server
- Fail2ban currently adds temporary bans based on selected logs, but it is planned for removal

A public port allowed by a provider firewall can still be blocked by Iptables. A port allowed by Iptables can still be blocked by the provider.

## Default policy

The IPv4 and IPv6 templates use:

```text
INPUT DROP
FORWARD DROP
OUTPUT ACCEPT
```

This means:

- inbound traffic is blocked unless a later rule allows it
- forwarded traffic is blocked because SlickStack is not intended to route traffic for other machines
- outbound traffic is allowed for updates, packages, certificates, DNS, backups, APIs, and other remote services

The rules use connection tracking to allow established and related inbound traffic after the original connection has already been permitted.

## Allowed inbound traffic

The default rules allow only the traffic expected for a normal SlickStack server:

```text
loopback traffic
established and related connections
HTTP and HTTPS on TCP ports 80 and 443
essential ICMP or ICMPv6
DHCPv6 client traffic on UDP port 546, when applicable
SSH and SFTP on TCP port 22, with rate limiting
```

The firewall does not publicly expose normal internal services such as:

```text
MySQL on 3306
Memcached on 11211
PHP-FPM on 9000
```

Those services also normally bind to local interfaces, but the firewall remains an additional boundary.

## Installation and persistence

Install or reconcile the Iptables packages with:

```bash
sudo bash /var/www/ss-install-iptables-packages
```

This installs:

```text
iptables
iptables-persistent
```

The current installer explicitly selects the legacy command backend:

```text
iptables-legacy
ip6tables-legacy
```

Install and immediately apply the SlickStack rule files with:

```bash
sudo bash /var/www/ss-install-iptables-config
```

The alias is:

```bash
ss install iptables config
```

Before promotion, the installer:

1. downloads both rule templates
2. verifies their SlickStack end markers
3. tests them with `iptables-restore -t` and `ip6tables-restore -t`
4. replaces both installed files only when both tests succeed
5. applies the IPv4 and IPv6 rules immediately
6. enables and restarts `netfilter-persistent`
7. restores file permissions

Invalid or incomplete downloaded rules are not intentionally promoted.

The complete SlickStack installer also reconciles the firewall:

```bash
sudo bash /var/www/ss-install
```

Use the narrow Iptables installer when only the firewall layer needs repair.

## Persistence across reboots

`iptables-persistent` and `netfilter-persistent` load the saved files during startup.

Check the service with:

```bash
systemctl status netfilter-persistent --no-pager
systemctl is-enabled netfilter-persistent
```

The active in-memory rules and the saved files are separate states. A temporary command can alter the running firewall without changing the files that load after reboot. Conversely, editing a saved file does not apply it until it is restored or the persistence service reloads it.

SlickStack's installer updates both states together.

## Inspecting the active firewall

Show active IPv4 rules:

```bash
sudo iptables -L -n -v --line-numbers
```

Show active IPv6 rules:

```bash
sudo ip6tables -L -n -v --line-numbers
```

Display the complete restorable syntax:

```bash
sudo iptables-save
sudo ip6tables-save
```

Check the selected command backend:

```bash
sudo update-alternatives --display iptables
sudo update-alternatives --display ip6tables
```

Test saved files without applying them:

```bash
sudo iptables-restore -t < /etc/iptables/rules.v4
sudo ip6tables-restore -t < /etc/iptables/rules.v6
```

Check expected listeners separately:

```bash
sudo ss -lntup
```

A listening service is not necessarily reachable through every firewall layer.

## ICMP and ICMPv6

IPv4 and IPv6 use different ICMP policies because IPv6 depends on ICMPv6 for more fundamental network behavior.

The IPv4 rules allow:

```text
echo-request
echo-reply
destination-unreachable
time-exceeded
```

This supports normal ping, basic diagnostics, traceroute-style behavior, and IPv4 Path MTU feedback through destination-unreachable messages.

The IPv6 rules allow:

```text
echo-request
echo-reply
destination-unreachable
packet-too-big
time-exceeded
parameter-problem
router-advertisement
neighbor-solicitation
neighbor-advertisement
```

IPv6 requires additional ICMPv6 handling for Path MTU Discovery, router advertisements, and neighbor discovery.

Do not blindly copy the IPv4 ICMP allowlist into IPv6. Blocking required ICMPv6 can cause connectivity failures that appear unrelated to the firewall.

## SSH rate limiting

SSH remains public because administrators, SFTP clients, backup tools, and deployment tools may need it.

Both IPv4 and IPv6 use layered limiting:

```text
INPUT -> SSH_GLOBAL_LIMIT -> SSH_SOURCE_LIMIT -> ACCEPT or REJECT
```

The default limits are:

```text
global SSH cap: 30/min with burst 60
per-source SSH cap: 6/min with burst 12
hashlimit source entry expiry: 60000 ms
```

The per-source hashlimit names are:

```text
IPv4: ssh4_per_ip
IPv6: ssh6_per_ip
```

The global limit protects a small server from excessive aggregate connection attempts. The per-source limit prevents one address from consuming the entire normal allowance.

Rejected SSH traffic inside these limiter chains is intentionally not sent to the generic firewall log rule. Constant public bot traffic would otherwise create noisy kernel logs.

The default firewall assumes SSH and SFTP use TCP port `22`. Changing SSH to another port requires coordinated changes to SSH configuration, provider controls, Iptables templates, automation, and recovery access.

## Denied-packet logging

Remaining denied inbound packets are logged with rate limiting.

The prefixes are:

```text
IPv4: [IPTABLES BLOCK]
IPv6: [IP6TABLES BLOCK]
```

Search the kernel journal:

```bash
journalctl -k | grep 'IPTABLES BLOCK'
journalctl -k | grep 'IP6TABLES BLOCK'
```

Depending on the Ubuntu logging configuration, entries may also appear in:

```text
/var/log/kern.log
/var/log/syslog
```

The generic log rule is limited to reduce disk and journal noise. Absence of a log entry does not prove that no firewall rejected the traffic; provider controls, Cloudflare, SSH limiter chains, or logging limits may account for the missing entry.

## Custom rules and managed-file boundaries

The standard SlickStack rules are hardcoded and are not generated from a general firewall schema in `ss-config`.

Direct edits to these files can be overwritten:

```text
/etc/iptables/rules.v4
/etc/iptables/rules.v6
```

They can be replaced by:

- `ss-install-iptables-config`
- the complete `ss-install`
- future SlickStack firewall template updates

For persistent policy changes, prefer the layer that naturally owns the requirement:

- provider firewall for broad source-address or port restrictions before traffic reaches the machine
- Cloudflare for public web edge controls
- Nginx for HTTP path, request, connection, and application-facing limits
- a reviewed SlickStack source-template change when the local host firewall itself must differ

Do not casually append runtime rules and assume they will survive reboot or reconciliation.

SlickStack should not hardcode vendor-specific allowlists for individual backup, deployment, or SFTP services. Normal SSH automation should work within the default limits. Stricter source restrictions are deployment-specific and require an independently maintained policy.

## Safe firewall changes

A firewall mistake can immediately terminate SSH and block recovery.

Before changing local rules:

1. keep an existing SSH session open
2. confirm provider-console or out-of-band access works
3. preserve the active and saved rules
4. verify both IPv4 and IPv6 behavior
5. test syntax before applying
6. ensure SSH remains allowed before changing the default policy
7. coordinate provider firewall rules with local rules

Create temporary backups:

```bash
sudo iptables-save > /root/iptables-v4.before
sudo ip6tables-save > /root/iptables-v6.before
sudo cp /etc/iptables/rules.v4 /root/rules.v4.before
sudo cp /etc/iptables/rules.v6 /root/rules.v6.before
```

Do not publish these files without reviewing them for custom addresses or infrastructure details.

## Recovery

When SSH is blocked, use the cloud provider console or recovery environment rather than repeatedly attempting remote changes.

From a working console, inspect the active rules first:

```bash
sudo iptables -L -n -v --line-numbers
sudo ip6tables -L -n -v --line-numbers
```

Restore known-good saved rules:

```bash
sudo iptables-restore < /etc/iptables/rules.v4
sudo ip6tables-restore < /etc/iptables/rules.v6
```

Reinstall the SlickStack defaults when the saved rules are damaged:

```bash
sudo bash /var/www/ss-install-iptables-config
```

Then verify:

```bash
systemctl status netfilter-persistent --no-pager
sudo iptables -L -n -v
sudo ip6tables -L -n -v
sudo ss -lntup
```

A provider firewall can still block access after the local rules are repaired.

## IPv4 and IPv6 parity

IPv4 and IPv6 should follow the same security intent for:

- default inbound and forwarding policies
- HTTP and HTTPS access
- SSH rate limiting
- established connections
- denied-packet logging

Their exact rule lists should not be identical because ICMP and address-family behavior differ.

Whenever the firewall policy changes, review and test both templates. Fixing only IPv4 can leave a service unexpectedly available or broken over IPv6.

## Scope

The standard SlickStack firewall is designed for:

- one WordPress-oriented server
- public web traffic on ports 80 and 443
- administrative SSH and SFTP on port 22
- local application services
- simple inbound filtering
- persistent IPv4 and IPv6 rules
- predictable regeneration from repository templates

It is not intended to provide:

- a general firewall rule builder
- per-user or per-application firewall policy
- multi-server routing or NAT management
- container-network policy
- VPN configuration
- dynamic vendor allowlists
- geo-blocking
- a replacement for Cloudflare or provider firewalls
- a full intrusion-detection or security-monitoring platform

See [Security](security.md), [Cloudflare](cloudflare.md), [Nginx](nginx.md), [Fail2ban](fail2ban.md), [Ubuntu](ubuntu.md), and [Troubleshooting](troubleshooting.md).
