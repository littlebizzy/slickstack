---
title: MU (Must-Use) Plugins (Public Mirrors)
---

# MU (Must-Use) WordPress Plugins

This directory stores source files and vendored ZIP mirrors used by SlickStack's WordPress MU plugin installer.

The installer determines which files are actually deployed. See the [WordPress MU Plugins guide](../../../docs/mu-plugins.md) for current installation behavior, environment differences, custom plugin mode, and update workflows.

## Required SlickStack files

These files are installed independently from the selectable default or custom plugin set:

- [000-common.txt](000-common.txt)
- [autoloader.txt](autoloader.txt)

The production Memcached `object-cache.php` drop-in is downloaded separately by the installer and is not stored as a ZIP mirror in this directory.

## Default MU plugin ZIPs

Unless valid custom mode is configured, SlickStack installs these packages in production and each enabled test environment:

- [clear-caches.zip](clear-caches.zip)
- [disable-empty-trash.zip](disable-empty-trash.zip)
- [disable-image-compression.zip](disable-image-compression.zip)
- [disable-xml-rpc.zip](disable-xml-rpc.zip)
- [force-https.zip](force-https.zip)
- [plugin-blacklist.zip](plugin-blacklist.zip)
- [repoman.zip](repoman.zip)

## Staging and development safeguards

SlickStack additionally installs these packages in enabled staging and development environments:

- [disable-emails.zip](disable-emails.zip)
- [disable-default-runner.zip](disable-default-runner.zip)

## Related links

- [WordPress MU Plugins guide](../../../docs/mu-plugins.md)
- [WordPress must-use plugins](https://wordpress.org/documentation/article/must-use-plugins/)
- [WooCommerce Action Scheduler default runner](https://github.com/woocommerce/action-scheduler-disable-default-runner)

---

*Last updated: Jul 20, 2026*
