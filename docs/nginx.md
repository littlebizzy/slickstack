# Nginx

SlickStack manages Nginx configuration using explicit, pre-approved include files. This keeps server behavior predictable while still allowing selected SlickStack modules to load their own Nginx snippets when enabled.

## Approved include files only

SlickStack does **not** support open-ended or arbitrary Nginx subconfig files. Files are only supported when their filename is explicitly approved and referenced by SlickStack's Nginx templates.

For example, SlickStack may reference approved files under:

```text
/var/www/sites/includes/
```

Only the specific include filenames referenced by SlickStack are eligible to load. Dropping a random `.conf` file into this directory does not make Nginx load it.

This approach avoids unexpected behavior from manual snippets, third-party packages, or stale configuration files.

## Optional loading behavior

Approved include files are loaded only when they exist. SlickStack uses explicit optional include patterns so that disabled modules do not break Nginx reloads.

For example, an approved include may be referenced like this:

```nginx
include /var/www/sites/includes/cloudflare[.]conf;
```

If the matching file exists, Nginx can load it. If it does not exist, the include pattern matches nothing and Nginx continues without that module-specific config.

## `/etc/nginx/conf.d/` is ignored

SlickStack does not rely on the default Nginx subconfig directory:

```text
/etc/nginx/conf.d/
```

Files placed there are not part of SlickStack's supported configuration flow and should be considered ignored by SlickStack-managed Nginx.

## Do not edit generated include files manually

Many approved include files are created, removed, or replaced by SlickStack scripts based on the active SlickStack configuration. Manual changes to generated files may be overwritten by future SlickStack runs.

When a new Nginx subconfig is needed, it should be added as an explicit, pre-approved SlickStack include instead of relying on open-ended wildcard loading.

## Design goals

SlickStack's Nginx include policy is designed to:

- keep Nginx behavior deterministic
- avoid unsupported third-party subconfigs
- prevent accidental loading of stale or random `.conf` files
- allow selected SlickStack features to provide their own isolated snippets
- keep `/var/www/sites/` as the canonical SlickStack-managed site config location
