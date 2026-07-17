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

## Authentication

Public WordPress REST API endpoints normally do not require authentication.

Private content, drafts, previews, and write requests require authentication. For remote server-to-server requests, WordPress Application Passwords are the recommended starting point.

Application Passwords are generated from a WordPress user profile and are separate from the user’s normal login password. They should only be used over HTTPS and must never be exposed in browser-side frontend code.

An authenticated request can be tested with:

```bash
curl --user "WORDPRESS_USERNAME:WORDPRESS_APPLICATION_PASSWORD" \
    https://cms.example.com/wp-json/wp/v2/users/me
```

The `/users/me` endpoint should return the authenticated WordPress user when the credentials are accepted.

SlickStack has not yet verified Application Password authentication with a real remote headless deployment. This should be tested before more advanced preview or publishing integrations are documented.

## Media and URLs

WordPress media remains hosted on the SlickStack domain, for example:

```text
https://cms.example.com/wp-content/uploads/
```

The remote frontend must allow images and other media to load from this hostname.

REST API responses may also contain backend URLs for posts, pages, categories, and media. The remote frontend is responsible for using, replacing, or redirecting those URLs where necessary.

SlickStack does not currently rewrite WordPress URLs to the remote frontend domain.

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
