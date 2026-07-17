# Headless WordPress

SlickStack can be used as the WordPress backend for a remote frontend such as Next.js.

There is no separate headless mode. WordPress continues running normally on the SlickStack server while the remote frontend retrieves content through the WordPress REST API or another API plugin.

## Recommended setup

Use separate hostnames for the backend and frontend:

```text
cms.example.com  → WordPress on SlickStack
example.com      → Remote frontend
```

Your SlickStack configuration would normally use:

```bash
SITE_ROOT_DOMAIN="example.com"
SITE_FULL_DOMAIN="cms.example.com"
SS_NOINDEX="true"
```

`SITE_FULL_DOMAIN` should remain the hostname where WordPress is installed.

For a fully headless setup, `SS_NOINDEX="true"` is recommended so the WordPress-rendered backend does not compete with the remote frontend in search results.

## WordPress API

The standard WordPress REST API is available at:

```text
https://cms.example.com/wp-json/
```

SlickStack already routes REST API requests through WordPress and excludes them from FastCGI page caching.

Plugins such as WPGraphQL may also be used, but SlickStack does not install or configure them.

## Scope

SlickStack manages the WordPress backend and LEMP stack.

The remote frontend is responsible for:

- deployment and hosting
- API authentication
- frontend caching
- previews
- content revalidation
- rewriting WordPress URLs where necessary

SlickStack does not install or manage Node.js, Next.js, or the remote frontend.
