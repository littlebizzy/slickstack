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

## SEO and indexing

In a fully headless setup, the remote frontend should provide the public site's metadata, canonical URLs, social sharing tags, `robots.txt`, and XML sitemaps.

`SS_NOINDEX="true"` helps keep WordPress-rendered pages on the SlickStack domain out of search results, but it does not configure SEO for the remote frontend.

SEO metadata stored in WordPress must be retrieved and rendered by the remote frontend where required. Public URLs should use the remote frontend domain rather than the WordPress backend domain.

SlickStack does not manage metadata, canonical URLs, robots rules, or sitemap generation on the remote frontend.

## WordPress API

The standard WordPress REST API is available at:

```text
https://cms.example.com/wp-json/
```

SlickStack already routes REST API requests through WordPress and excludes them from FastCGI page caching.

## WPGraphQL

WPGraphQL may be installed as a normal WordPress plugin when a GraphQL API is preferred over the standard REST API.

With WordPress pretty permalinks enabled, the default endpoint is:

```text
https://cms.example.com/graphql
```

WPGraphQL uses normal WordPress rewrite rules, so it should work with SlickStack's existing WordPress routing. However, SlickStack has not yet specifically tested public queries, authenticated queries, mutations, or high-volume GraphQL traffic.

WPGraphQL does not include its own complete authentication system. Private data and mutations require a compatible authentication method and appropriate WordPress user permissions.

SlickStack does not currently install, configure, cache, rate-limit, or otherwise optimize WPGraphQL separately.

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

## CORS and request location

Remote frontends should normally retrieve WordPress content from server-side code. This keeps API credentials private and avoids browser-enforced cross-origin restrictions.

WordPress REST API endpoints support cross-origin browser requests, but Application Passwords and other secrets must never be included in browser-side JavaScript.

SlickStack does not add a separate headless-specific CORS policy. Custom CORS rules should only be added when the frontend must request WordPress directly from a visitor's browser.

## Media and URLs

WordPress media remains hosted on the SlickStack domain, for example:

```text
https://cms.example.com/wp-content/uploads/
```

The remote frontend must allow images and other media to load from this hostname.

REST API responses may also contain backend URLs for posts, pages, categories, and media. The remote frontend is responsible for using, replacing, or redirecting those URLs where necessary.

SlickStack does not currently rewrite WordPress URLs to the remote frontend domain.

## Backend page redirects

WordPress continues serving normal frontend pages on the SlickStack domain unless another plugin or configuration changes that behavior.

A fully headless setup may redirect public post, page, archive, and homepage requests to the remote frontend.

Redirects must preserve required WordPress routes such as:

```text
/wp-admin/
/wp-login.php
/wp-json/
/wp-content/uploads/
```

Webhook endpoints, preview routes, and plugin callback URLs may also need to remain accessible.

SlickStack does not currently redirect WordPress-rendered pages to the remote frontend.

A future optional SlickStack MU plugin could use a configured frontend URL to manage these redirects without interfering with required administration, API, media, webhook, preview, or plugin callback routes.

## Frontend caching and revalidation

SlickStack does not FastCGI-cache WordPress REST API responses. The remote frontend should manage its own API caching, generated pages, and content refresh behavior.

Publishing or updating content in WordPress does not automatically clear caches or rebuild pages on the remote frontend.

Depending on the frontend platform, this may require scheduled refreshes, build hooks, publishing webhooks, or on-demand page and tag revalidation.

Avoid making a new uncached WordPress API request for every public page view. Excessive requests from a remote hosting provider may encounter SlickStack's normal Nginx or PHP rate limits.

## Draft previews

WordPress preview links normally open the WordPress-rendered site on the SlickStack domain rather than the remote frontend.

A headless frontend must provide its own preview route and securely retrieve draft or revision content from WordPress using an authenticated request.

SlickStack does not currently rewrite WordPress preview links, create preview tokens, or configure preview routes on the remote frontend.

A future headless integration plugin could use a configured frontend URL to redirect WordPress preview links to the appropriate frontend preview route.

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
