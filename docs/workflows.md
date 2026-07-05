# GitHub Workflows

SlickStack uses GitHub Actions for repository maintenance tasks that should happen inside GitHub instead of during end-user server installs.

Workflows live under:

```bash
.github/workflows/
```

## SourceForge mirror workflow

The main workflow mirrors the SlickStack `master` branch to SourceForge after pushes to `master`.

That workflow is intentionally separate from maintenance workflows. It checks out the repository, configures the SourceForge SSH remote, and force-pushes `master` to the SourceForge mirror.

## MU plugin ZIP update workflow

The MU plugin ZIP update workflow is:

```bash
.github/workflows/update-mu-plugin-zips.yml
```

This workflow is manual-only. It uses:

```yaml
on:
  workflow_dispatch:
```

There is no schedule trigger and no push trigger. It should only run when a maintainer manually starts it from the GitHub Actions tab.

## Purpose

The workflow updates approved vendored LittleBizzy MU plugin ZIPs inside:

```bash
modules/wordpress/mu-plugins/
```

This keeps SlickStack server installs simple. Servers download static ZIP files from the SlickStack repo instead of querying the GitHub release API for each plugin during install.

That distinction is important because live release API lookups can fail or be rate-limited. The workflow performs the release lookup in GitHub Actions ahead of time, then commits the resulting ZIP to SlickStack.

## Current plugin support

The first version supports only:

```text
force-https
```

The workflow is structured with a plugin list so more approved LittleBizzy plugin repos can be added later.

## How the MU plugin update works

For each supported plugin, the workflow:

1. finds the latest Git tag in the source plugin repo
2. compares that tag against the plugin version inside the existing vendored ZIP
3. skips the plugin if the vendored ZIP already matches the latest tagged version
4. downloads the GitHub tag archive when an update is needed
5. extracts the archive into a temporary work directory
6. renames the extracted GitHub archive folder to the exact plugin slug
7. verifies the expected main plugin file exists
8. rebuilds the ZIP under `modules/wordpress/mu-plugins/`
9. commits and pushes only if Git detects ZIP changes

For example, GitHub tag archives normally extract into names like:

```text
force-https-4.0.0/
```

SlickStack needs the ZIP to contain:

```text
force-https/
```

The workflow handles that normalization before rebuilding `force-https.zip`.

## Before running the MU plugin workflow

Before running the workflow for a plugin, the source plugin repo should already have the intended release tag.

For example, if `force-https.php` has been updated to version `4.0.0`, the `littlebizzy/force-https` repo should have a matching `4.0.0` or `v4.0.0` tag before the workflow is run.

If the new version has not been tagged yet, the workflow will keep using the latest existing tag instead of the latest source code on `master`.

## Adding more plugins later

To add more supported MU plugin ZIP mirrors later, add plugin repo slugs to the workflow plugin list.

Each plugin should follow the same packaging convention:

```text
plugin-slug.zip
└── plugin-slug/
    └── plugin-slug.php
```

The workflow intentionally assumes this simple convention because the SlickStack MU plugin installer expects predictable extracted folder names.

## Related docs

See `docs/mu-plugins.md` for the server install behavior, vendored ZIP mirror rules, and custom MU plugin notes.
