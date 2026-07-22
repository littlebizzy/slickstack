# Headless WordPress

SlickStack can be used as the WordPress backend for a remote frontend built with Next.js, Astro, Nuxt, SvelteKit, or another framework that can retrieve WordPress content.

There is no separate headless mode. WordPress continues running normally on the SlickStack server while the remote frontend retrieves content through the WordPress REST API, WPGraphQL, WooCommerce APIs, or another API plugin.

## Table of Contents

- [Recommended setup](#recommended-setup)
- [Frontend frameworks and rendering](#frontend-frameworks-and-rendering)
- [Common API patterns](#common-api-patterns)
- [SEO and indexing](#seo-and-indexing)
- [WordPress API](#wordpress-api)
- [WPGraphQL](#wpgraphql)
- [Headless WooCommerce](#headless-woocommerce)
- [Authentication](#authentication)
- [CORS and request location](#cors-and-request-location)
- [Media and URLs](#media-and-urls)
- [Backend page redirects](#backend-page-redirects)
- [Frontend caching and revalidation](#frontend-caching-and-revalidation)
- [Draft previews](#draft-previews)
- [Scope](#scope)
- [Related guides](#related-guides)

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

## Frontend frameworks and rendering

SlickStack is frontend-neutral. Common choices include:

- Next.js for React applications and a large WordPress-oriented ecosystem
- Astro for content-heavy sites that benefit from mostly static output and selective interactive components
- Nuxt for Vue applications with server rendering and hybrid route behavior
- SvelteKit for Svelte applications with server rendering and deployment adapters

Other frameworks can work when they support the required API requests, rendering model, authentication, preview flow, and deployment target.

The remote frontend may combine several rendering strategies:

- static generation for stable editorial pages
- incremental or on-demand regeneration for published content and product pages
- server rendering for private, personalized, or rapidly changing data
- client-side components for selected interactive behavior

Framework choice is less important than correctly handling authentication, previews, cache invalidation, customer sessions, dynamic data, and secrets.

## Common API patterns

A headless WordPress site does not need to use one API for everything.

Common patterns include:

### REST-first

```text
WordPress REST API  → posts, pages, media, taxonomies, and custom content
Woo Store API       → products, cart, shipping, and checkout when WooCommerce is used
```

### GraphQL-heavy

```text
WPGraphQL   → WordPress content
WooGraphQL  → WooCommerce data and mutations when WooCommerce is used
```

### Hybrid

```text
WPGraphQL or WordPress REST API  → editorial content
WooCommerce Store API            → customer-facing commerce
WooCommerce REST API             → trusted server-side administration and integrations
```

The hybrid approach is often practical because public content retrieval, customer storefront actions, and privileged store management have different authentication and caching requirements.

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

The WordPress REST API is a normal starting point for posts, pages, media, taxonomies, users, and registered custom post types. Additional plugins may expose custom fields, SEO metadata, menus, or plugin-specific data.

## WPGraphQL

WPGraphQL may be installed as a normal WordPress plugin when a GraphQL API is preferred over the standard REST API.

With WordPress pretty permalinks enabled, the default endpoint is:

```text
https://cms.example.com/graphql
```

WPGraphQL uses normal WordPress rewrite rules, so it should work with SlickStack's existing WordPress routing. However, SlickStack has not yet specifically tested public queries, authenticated queries, mutations, or high-volume GraphQL traffic.

WPGraphQL does not include its own complete authentication system. Private data and mutations require a compatible authentication method and appropriate WordPress user permissions.

SlickStack does not currently install, configure, cache, rate-limit, or otherwise optimize WPGraphQL separately.

## Headless WooCommerce

WooCommerce adds separate storefront, session, checkout, order, payment, and background-job concerns that do not apply to a content-only headless site.

Common API pieces include:

| API | Normal role |
| :-- | :-- |
| WordPress REST API | Editorial WordPress content |
| WooCommerce Store API | Public product data and customer-facing cart, shipping, and checkout flows |
| WooCommerce REST API | Authenticated server-side management of products, orders, customers, coupons, and other store data |
| WPGraphQL | Optional GraphQL access to WordPress content |
| WooGraphQL | Optional WooCommerce extension for WPGraphQL |

WPGraphQL and WooGraphQL are valid options, but they are not mandatory. A storefront may use the WordPress REST API or WPGraphQL for content while using the WooCommerce Store API for cart and checkout.

The Store API uses customer session state. Headless carts may use WooCommerce cookies or cart tokens, and protected Store API writes require a valid nonce or cart token. Privileged WooCommerce REST API credentials must remain in trusted server-side code and must never be exposed in browser JavaScript.

Product and category pages may be cached or generated, but stock, price, cart, account, checkout, order, and personalized data require an appropriate dynamic or refresh strategy.

Some payment, subscription, membership, tax, and checkout extensions assume a WordPress-rendered checkout. Confirm required extension compatibility before building a completely custom checkout. Redirecting to the normal WooCommerce checkout can be a pragmatic fallback.

See [WooCommerce](woocommerce.md) for caching, sessions, Action Scheduler, email, payments, webhooks, staging safety, backups, APIs, and troubleshooting.

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

Customer login for a headless WooCommerce storefront is a separate application concern. WordPress Application Passwords are not a browser-side customer-login system.

## CORS and request location

Remote frontends should normally retrieve WordPress content from server-side code. This keeps API credentials private and avoids browser-enforced cross-origin restrictions.

WordPress REST API endpoints support cross-origin browser requests, but Application Passwords and other secrets must never be included in browser-side JavaScript.

SlickStack does not add a separate headless-specific CORS policy. Custom CORS rules should only be added when the frontend must request WordPress directly from a visitor's browser.

Storefront cart or checkout requests made directly from a browser require carefully limited origins, headers, methods, credentials, session state, and preflight behavior. Do not enable unrestricted CORS merely to make development requests succeed.

## Media and URLs

WordPress media remains hosted on the SlickStack domain, for example:

```text
https://cms.example.com/wp-content/uploads/
```

The remote frontend must allow images and other media to load from this hostname.

REST API, GraphQL, and WooCommerce responses may contain backend URLs for posts, pages, products, categories, and media. The remote frontend is responsible for using, replacing, or redirecting those URLs where necessary.

SlickStack does not currently rewrite WordPress URLs to the remote frontend domain.

## Backend page redirects

WordPress continues serving normal frontend pages on the SlickStack domain unless another plugin or configuration changes that behavior.

A fully headless setup may redirect public post, page, archive, product, category, and homepage requests to the remote frontend.

Redirects must preserve required WordPress routes such as:

```text
/wp-admin/
/wp-login.php
/wp-json/
/wp-content/uploads/
```

GraphQL, webhook, payment callback, preview, and plugin callback URLs may also need to remain accessible.

SlickStack does not currently redirect WordPress-rendered pages to the remote frontend.

A future optional SlickStack MU plugin could use a configured frontend URL to manage these redirects without interfering with required administration, API, media, webhook, preview, payment callback, or plugin callback routes.

## Frontend caching and revalidation

SlickStack does not FastCGI-cache WordPress REST API responses. The remote frontend should manage its own API caching, generated pages, and content refresh behavior.

Publishing or updating content in WordPress does not automatically clear caches or rebuild pages on the remote frontend.

Depending on the frontend platform, this may require scheduled refreshes, build hooks, publishing webhooks, or on-demand page and tag revalidation.

WooCommerce product, price, stock, and category changes may also require frontend revalidation. Cart, account, checkout, order, and personalized responses should not be stored in a shared public cache.

Avoid making a new uncached WordPress API request for every public page view. Excessive requests from a remote hosting provider may encounter SlickStack's normal Nginx or PHP rate limits.

## Draft previews

WordPress preview links normally open the WordPress-rendered site on the SlickStack domain rather than the remote frontend.

A headless frontend must provide its own preview route and securely retrieve draft or revision content from WordPress using an authenticated request.

SlickStack does not currently rewrite WordPress preview links, create preview tokens, or configure preview routes on the remote frontend.

A future optional SlickStack MU plugin could use a configured frontend URL to redirect WordPress preview links to the appropriate frontend preview route.

## Scope

SlickStack manages the WordPress and WooCommerce origin, LEMP stack, and related server behavior documented in this repository.

The remote frontend is responsible for:

- framework and runtime deployment
- frontend hosting
- API authentication and secret isolation
- customer sessions where applicable
- frontend caching
- previews
- content and product revalidation
- rewriting WordPress URLs where necessary
- payment and checkout compatibility where applicable

SlickStack does not install or manage frontend frameworks, JavaScript runtimes, deployment platforms, or remote frontend hosting.

## Related guides

- [WordPress](wordpress.md)
- [WooCommerce](woocommerce.md)
- [Nginx](nginx.md)
- [Caching](caching.md)
- [Security](security.md)
- [SSL Certificates](ssl.md)
