# Iptables

SlickStack uses hardcoded iptables rules for the default server firewall. These rules are intended to be simple, repeatable, and suitable for common WordPress LEMP servers with minimal per-server customization.

The main rule templates live under:

```bash
modules/iptables/rules-v4.txt
modules/iptables/rules-v6.txt
```

They are installed to the server paths:

```bash
/etc/iptables/rules.v4
/etc/iptables/rules.v6
```

## Default policy

The default inbound policy is strict:

```text
INPUT DROP
FORWARD DROP
OUTPUT ACCEPT
```

This means inbound traffic is blocked unless a later rule explicitly allows it. Forwarded traffic is blocked because SlickStack servers are not intended to route traffic for other machines. Outbound traffic is allowed so the server can fetch updates, certificates, packages, and remote resources.

## Allowed inbound traffic

The default rules allow only the traffic SlickStack expects for a normal web server:

```text
loopback traffic
established and related connections
HTTP and HTTPS on ports 80 and 443
essential ICMP or ICMPv6
SSH on port 22, with rate limiting
```

The firewall does not expose MySQL, Memcached, PHP-FPM, or other internal services to the public internet.

## SSH rate limiting

SSH is public because administrators and some backup, SFTP, and deployment tools need to connect to the server.

SlickStack rate limits new SSH connections instead of leaving SSH wide open. The IPv4 rules use layered SSH limiting:

```text
INPUT -> SSH_GLOBAL_LIMIT -> SSH_SOURCE_LIMIT -> ACCEPT or REJECT
```

The global SSH limit protects small cloud servers from excessive aggregate SSH connection attempts. The per-source limit then tracks each source IP separately.

This avoids relying on one low shared global bucket for every SSH client. A low global-only SSH limit can be consumed by bot traffic before legitimate SFTP or backup tools get a chance to connect. The layered approach keeps a global safety cap while still allowing normal short bursts from trusted automation, admins, or SFTP clients.

The IPv4 default values are:

```text
global SSH cap: 30/min with burst 60
per-source SSH cap: 6/min with burst 12
hashlimit source entry expiry: 60000 ms
```

These values are conservative for common 1 GB to 2 GB WordPress VPS servers while being less fragile than a very low global-only SSH limit.

## Third-party SFTP and backup tools

SlickStack should not hardcode firewall allowlists for specific third-party services such as backup providers, deploy services, or SFTP clients.

The default firewall should work for ordinary SSH/SFTP automation without requiring vendor-specific IPs. If a server owner needs stricter access control, they can add their own trusted SSH allowlist outside the default shared SlickStack rules.

## Logging

Remaining denied inbound packets are logged with a rate-limited kernel log rule after normal allowed and rejected services have been handled.

The log prefix is:

```text
[IPTABLES BLOCK]
```

These logs usually appear in the kernel journal:

```bash
journalctl -k | grep "\[IPTABLES BLOCK\]"
```

Depending on the server's logging configuration, they may also appear in `/var/log/kern.log` or `/var/log/syslog`.

SSH traffic rejected inside the SSH limiter chains is intentionally not logged by the generic denied-packet rule. Public SSH receives constant bot traffic, and logging every rejected SSH attempt can quickly create noisy logs on small servers.

## IPv6 notes

The IPv6 rules live in `modules/iptables/rules-v6.txt` and should follow the same security intent as IPv4, while preserving IPv6-specific requirements such as ICMPv6 neighbor discovery and DHCPv6 client support.

When SSH rate limiting logic changes in IPv4, review IPv6 separately instead of blindly copying every IPv4 ICMP rule. IPv6 requires different ICMP handling than IPv4.
