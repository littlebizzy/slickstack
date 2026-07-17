# Ubuntu

SlickStack uses Ubuntu LTS as the operating-system foundation for the entire stack. It manages system users, SSH and SFTP access, sudo privileges, kernel settings, swap, utilities, Bash aliases, root cron scheduling, package updates, and controlled reboots.

Because these settings affect server access and every installed service, review `ss-config` carefully before running `ss-install` or an individual Ubuntu installer.

## Supported Ubuntu releases

The current installer accepts these Ubuntu LTS versions:

```text
18.04
20.04
22.04
24.04
```

The active SlickStack module baseline and README target Ubuntu 24.04 LTS. New servers should normally use 24.04 rather than starting with an older accepted release.

SlickStack also requires a normal physical or virtual machine. Containerized environments such as Docker, LXC, OpenVZ, Podman, and systemd-nspawn are rejected by the installer.

The main installation command is:

```bash
sudo bash /var/www/ss-install
```

The installer also requires at least 1 GB of free disk space before it proceeds.

## Managed Ubuntu components

SlickStack manages or replaces several important system files:

```text
/etc/ssh/sshd_config
/etc/sudoers
/etc/sysctl.conf
/var/spool/cron/crontabs/root
/var/www/meta/.bashrc
/etc/profile.d/custom-shell-prompt.sh
```

It can also create:

```text
/var/www/cache/system/swapfile
/var/www/auth/authorized_keys
```

Direct changes to managed files may be overwritten the next time the related installer or the complete `ss-install` workflow runs.

Use `ss-config`, the reserved custom cron files, or another documented customization point instead of editing generated system files directly.

## Server users

SlickStack creates two primary administrative identities in addition to the existing root account:

| User | Purpose |
| :------------- | :---------- |
| Sudo user | Full SSH shell access and server administration |
| SFTP user | Jailed file-transfer access under `/var/www` without shell access |

These values are defined in `ss-config`:

```bash
SUDO_USER="exampleadmin"
SUDO_PASSWORD="example-password"
SFTP_USER="examplefiles"
SFTP_PASSWORD="example-password"
```

The sudo and SFTP usernames must be different.

The installer creates and maintains these groups:

```text
sudo-ssh
sftp-only
slickstack
```

The sudo user is added to `sudo-ssh`, `slickstack`, and `www-data`. The SFTP user is added to `sftp-only`, `slickstack`, and `www-data`. The `www-data` user is also added to the `slickstack` group.

## Root account

During user installation, SlickStack sets the root password to the configured `SUDO_PASSWORD` and disables password expiration for root.

Root remains available for local system administration, recovery consoles, and commands already running with elevated privileges, but direct root SSH login is disabled by the managed SSH configuration.

Do not rely on root SSH access after installation. Confirm that the configured sudo user can successfully open a new SSH session before closing the original provider or root session.

## Sudo privileges

SlickStack replaces `/etc/sudoers` with its managed template.

The configured sudo user receives unrestricted passwordless sudo privileges. Members of the `sudo-ssh` group also receive passwordless sudo access.

The template additionally:

- uses Nano as the sudo editor
- sets a standard secure command path
- disables the sudo lecture
- uses a 30-minute global sudo timestamp
- retains the normal Ubuntu `sudo` group rule

Because `/etc/sudoers` is managed, custom privilege rules added directly to that file may be lost. SlickStack does not currently use the normal `/etc/sudoers.d` include directory in its template.

## SSH access

The SSH installer is:

```bash
sudo bash /var/www/ss-install-ubuntu-ssh
```

It installs an Ubuntu-version-specific `/etc/ssh/sshd_config`, resets permissions, and restarts the SSH service.

The standard configuration:

- listens on TCP port `22`
- accepts IPv4 connections
- disables root login
- allows only the `sudo-ssh` and `sftp-only` groups
- limits authentication attempts
- disables empty passwords and keyboard-interactive authentication
- disables X11 forwarding
- keeps TCP forwarding available for the sudo user
- disables DNS lookups for faster connections

Port 22 is intentionally hardcoded by the current installer for compatibility. Changing an old `SSH_PORT` value elsewhere does not change the active SSH listener created by this script.

Keep an existing SSH or provider-console session open while testing a changed SSH configuration. A bad IP restriction, key, or username can otherwise lock you out of the server.

## Password and key authentication

Authentication behavior is controlled by:

```bash
SSH_KEYS="false"
SSH_RESTRICT_IP="false"
SSH_IPV4="192.0.2.1"
```

With the default `SSH_KEYS="false"` setting, the sudo user logs in with a password and public-key authentication is disabled by the installer.

When `SSH_KEYS="true"`:

- password authentication is disabled
- public-key authentication is enabled
- SlickStack generates a 4096-bit RSA key pair if its expected key does not already exist
- the detected public key is appended to the centralized authorized-keys file

The centralized authorized-keys file is:

```text
/var/www/auth/authorized_keys
```

SlickStack owns that file as `root:root` with mode `0644`.

Administrators using their own key should place the public key in the expected authorized-keys file before enabling key-only authentication and should verify access in a second session before disconnecting.

## Optional SSH IP restriction

When enabled, `SSH_RESTRICT_IP` restricts sudo-user SSH access to the IPv4 address defined by `SSH_IPV4`.

The SFTP account remains separately matched so file-transfer services can still connect according to the generated SSH rules.

Use IP restriction cautiously. Dynamic residential IP addresses, VPN changes, travel, and provider network changes can immediately prevent SSH access.

## Jailed SFTP access

The SFTP user has `/usr/sbin/nologin` as its shell and is matched by the `sftp-only` group.

The SSH template applies:

```text
ChrootDirectory /var/www
ForceCommand internal-sftp
AllowTcpForwarding no
X11Forwarding no
PermitTTY no
```

This means the account can transfer files within the `/var/www` jail but cannot open a normal shell, run arbitrary commands, request a terminal, or create SSH tunnels.

The jailed SFTP account is suitable for trusted developers, agencies, and compatible remote backup services that only require file access.

## System timezone

The system timezone is configured through:

```bash
SS_TIMEZONE="UTC"
```

During Ubuntu utility installation, SlickStack runs `timedatectl set-timezone` with this value. If the setting is missing, it falls back to UTC.

UTC is the recommended server timezone because it avoids daylight-saving changes and keeps logs and scheduled jobs predictable across regions.

## Ubuntu utilities

The utility installer is:

```bash
sudo bash /var/www/ss-install-ubuntu-utils
```

It updates the APT cache, upgrades installed packages, installs required command-line utilities, sets the system timezone, installs a custom shell prompt, and makes Nano the default editor.

Core packages include:

```text
coreutils
cron
update-manager-core
software-properties-common
debconf-utils
```

Additional tools are installed when their commands are missing, including:

```text
dig
virt-what
sshpass
exiftool
nano
wget
bash
curl
openssl
sed
rsync
zip
unzip
dos2unix
gzip
tar
```

Some services install their own additional packages through their dedicated module installers.

## Bash environment and aliases

The Bash installer is:

```bash
sudo bash /var/www/ss-install-ubuntu-bash
```

It installs the shared SlickStack Bash configuration at:

```text
/var/www/meta/.bashrc
```

The shared file is sourced by the root, sudo, and SFTP user Bash configuration files. The SFTP user still cannot open a normal shell because its login shell is disabled.

The managed Bash environment provides:

- a standard system `PATH`
- Nano as the default editor
- a customized shell prompt
- the `ss` command aliases
- WP-CLI completion and environment support
- Rclone environment configuration when applicable
- `/var/www` as the normal working area for management tasks

For example:

```bash
ss check
ss install
ss backup
ss cron hourly
ss reboot
```

These aliases are conveniences for the underlying scripts. The explicit form remains valid:

```bash
sudo bash /var/www/ss-check
```

Do not edit `/var/www/meta/.bashrc` directly if the change must survive future installer runs.

## Kernel and sysctl tuning

The kernel configuration installer is:

```bash
sudo bash /var/www/ss-install-ubuntu-kernel
```

Despite the script name, this workflow primarily installs SlickStack's Ubuntu-version-specific `sysctl` configuration at:

```text
/etc/sysctl.conf
```

It then applies the file with:

```bash
sysctl -p /etc/sysctl.conf
```

The managed settings cover:

- kernel logging and basic hardening
- process and memory behavior
- file-descriptor and inotify limits
- swap and filesystem-cache behavior
- IPv4 and IPv6 networking
- redirect and source-routing protection
- TCP buffers, retries, timeouts, and queues
- socket and network-processing limits

Current Ubuntu 24.04 defaults include low swap preference, increased file limits, disabled IP forwarding, enabled TCP syncookies, and larger connection queues suitable for a standalone web server.

These settings are designed for SlickStack's normal single-server WordPress architecture. Clustering, container networking, VPN gateways, routers, custom forwarding, and unusual network appliances may require different kernel values and are outside the standard managed configuration.

Direct changes to `/etc/sysctl.conf` may be overwritten by `ss-install-ubuntu-kernel` or the complete installer.

## Swapfile

The swap installer is:

```bash
sudo bash /var/www/ss-install-ubuntu-swapfile
```

When no swapfile entry is detected in `/etc/fstab` and more than approximately 5 GB of disk space is free, SlickStack creates a 2 GB swapfile at:

```text
/var/www/cache/system/swapfile
```

The file is owned by `root:root` with mode `0600`, activated with `swapon`, and appended to `/etc/fstab` for future boots.

The installer does not currently resize an existing swapfile when server RAM or disk capacity changes. It also does not replace an existing swap configuration detected in `/etc/fstab`.

Useful checks include:

```bash
swapon --show
free -h
grep swap /etc/fstab
```

Swap is an emergency buffer, not a replacement for adequate RAM. Sustained swap usage usually indicates that the server needs resource tuning or a larger virtual machine.

## Root crontab

The root crontab installer is:

```bash
sudo bash /var/www/ss-install-ubuntu-crontab
```

It installs the Cron package if needed, replaces root's crontab from SlickStack's template, reloads Cron, and restores managed permissions.

The root schedule runs SlickStack cron wrappers at these fixed frequencies:

```text
minutely
often (every 2 minutes)
regular (every 5 minutes)
quarter-hourly
half-hourly
hourly
quarter-daily
half-daily
daily
half-weekly
weekly
half-monthly
monthly
sometimes (every 2 months)
```

Each wrapper uses `flock` so another copy of the same interval does not begin while its lock is active.

The root crontab also removes lock files older than six hours and periodically repairs missing or damaged SlickStack cron wrapper files from public mirrors.

Do not edit root's crontab or the standard files under `/var/www/crons/`. The crontab installer replaces the root schedule, and the self-healing workflow can replace standard cron wrappers.

## Scheduled task configuration

Normal task timing is controlled with `INTERVAL_SS_*` values inside `ss-config`, for example:

```bash
INTERVAL_SS_DUMP_DATABASE="hourly"
INTERVAL_SS_PERMS="half-daily"
INTERVAL_SS_UPDATE_MODULES="never"
INTERVAL_SS_REBOOT_MACHINE="never"
```

A task runs when its configured interval matches the wrapper currently executing.

Use the reserved files under:

```text
/var/www/crons/custom/
```

for custom shell tasks. These files are sourced by the matching standard cron wrappers and are the supported place for custom recurring commands.

Custom tasks run as root. Validate commands carefully, use absolute paths where practical, avoid long blocking operations, and account for the resources required by other scheduled SlickStack tasks.

## Package updates

To update installed Ubuntu packages and the Linux kernel without reinstalling all SlickStack configuration, run:

```bash
sudo bash /var/www/ss-update-modules
```

The script:

1. creates a current database dump when the expected production dump already exists
2. cleans the APT cache
3. updates package metadata
4. runs an APT full upgrade, including kernel packages
5. removes unused dependencies

The default scheduled interval is:

```bash
INTERVAL_SS_UPDATE_MODULES="never"
```

This means module updates are manual unless the administrator deliberately schedules them.

A kernel or low-level library update may require a reboot before the newly installed version becomes active.

## Reinstallation

SlickStack's Ubuntu installers are designed to be idempotent and can be run again to restore the managed configuration:

```bash
sudo bash /var/www/ss-install-ubuntu-users
sudo bash /var/www/ss-install-ubuntu-ssh
sudo bash /var/www/ss-install-ubuntu-utils
sudo bash /var/www/ss-install-ubuntu-bash
sudo bash /var/www/ss-install-ubuntu-kernel
sudo bash /var/www/ss-install-ubuntu-swapfile
sudo bash /var/www/ss-install-ubuntu-crontab
```

The complete installer re-applies these components together:

```bash
sudo bash /var/www/ss-install
```

Reinstallation can restore missing or damaged managed files, but it also overwrites unsupported direct customizations.

## Reboots

The controlled reboot command is:

```bash
sudo bash /var/www/ss-reboot-machine
```

or:

```bash
ss reboot
```

The script checks server uptime against:

```bash
SS_REBOOT_MIN_UPTIME="3600"
```

With the default value, it refuses to reboot a server that has been running for less than one hour. This helps prevent accidental reboot loops when the command is called from another script.

The scheduled reboot interval defaults to:

```bash
INTERVAL_SS_REBOOT_MACHINE="never"
```

For an intentional emergency reboot that must ignore the SlickStack uptime guard, use the normal operating-system reboot command directly from an active sudo or provider-console session.

## Troubleshooting

### SSH access fails after installation

Confirm:

- the client is connecting to port 22
- the username matches `SUDO_USER`
- the user belongs to `sudo-ssh`
- password authentication matches `SSH_KEYS`
- the public key exists in `/var/www/auth/authorized_keys` when keys are enabled
- `SSH_RESTRICT_IP` is not blocking the current address
- the SSH service is running

Use a provider console if no SSH session remains available.

### SFTP login works but shell commands fail

This is expected. The SFTP account uses `/usr/sbin/nologin` and is forced into the internal SFTP subsystem under `/var/www`.

Use the sudo user for shell administration.

### Cron tasks are not running

Check:

```bash
systemctl status cron
sudo crontab -l
ls -la /var/www/crons/
ls -la /var/www/crons/custom/
```

Also confirm that the relevant `INTERVAL_SS_*` value is not set to `never`.

To restore the root schedule, run:

```bash
sudo bash /var/www/ss-install-ubuntu-crontab
```

### Kernel settings do not persist

Confirm that the expected values exist in `/etc/sysctl.conf`, then reapply the managed template:

```bash
sudo bash /var/www/ss-install-ubuntu-kernel
```

Custom values added directly to the managed file may be replaced.

### Swap was not created

The installer skips creation when an existing swap entry is found or free disk space is not greater than approximately 5 GB.

Check `/etc/fstab`, `swapon --show`, and available disk space before rerunning the swap installer.

### Package update changed a configuration file

Run the relevant SlickStack installer or the complete `ss-install` workflow to restore managed configuration after reviewing `ss-config` and current backups.

## Scope

The standard SlickStack Ubuntu design assumes:

- one Ubuntu LTS virtual or physical server
- one unrestricted sudo administrator
- one jailed SFTP account
- SSH on port 22
- SlickStack-managed sudoers, SSH, sysctl, Bash, and root cron files
- optional key-only SSH authentication
- a single 2 GB emergency swapfile when needed
- APT-based package management
- systemd-managed services
- custom recurring commands only through reserved cron files

Multiple independent admin privilege policies, custom SSH ports, LDAP or centralized identity, immutable operating systems, configuration-management convergence, containers, Kubernetes, custom routing, clustered kernels, automatic distribution upgrades, and multi-server orchestration are outside the standard managed configuration.