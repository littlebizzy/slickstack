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

The workflow also uses a concurrency lock so two manual MU plugin ZIP update runs do not overlap.

## Purpose

The workflow updates approved vendored MU plugin ZIPs inside:

```bash
modules/wordpress/mu-plugins/
```

This keeps SlickStack server installs simple. Servers download static ZIP files from the SlickStack repo instead of querying the GitHub release API for each plugin during install.

That distinction is important because live release API lookups can fail or be rate-limited. The workflow performs the release lookup in GitHub Actions ahead of time, then commits the resulting ZIP to SlickStack.

## Current plugin support

The workflow currently supports:

```text
littlebizzy/clear-caches
littlebizzy/disable-empty-trash
littlebizzy/disable-image-compression
littlebizzy/disable-xml-rpc
littlebizzy/force-https
littlebizzy/plugin-blacklist
littlebizzy/repoman
afragen/git-updater
```

This covers every default plugin ZIP downloaded by the current MU plugin installer, plus the separately approved Git Updater mirror. Other optional or legacy ZIPs in the module directory are not updated by this workflow unless their source repositories are explicitly approved and added.

The workflow uses explicit `owner/repo` entries so approved external plugin repositories can be included without changing the packaging logic.

## How the MU plugin update works

For each supported plugin, the workflow:

1. validates the `owner/repo` entry before using it in URLs or paths
2. finds the latest Git tag in the source plugin repo, normalizing an optional leading v before version sorting
3. compares that tag against the plugin version inside the existing vendored ZIP
4. supports both plain `Version:` headers and standard docblock `* Version:` headers
5. logs the current vendored ZIP version and latest source tag
6. skips the plugin if the vendored ZIP already matches the latest tagged version
7. downloads the GitHub tag archive when an update is needed
8. extracts the archive into a temporary work directory
9. renames the extracted GitHub archive folder to the exact plugin slug
10. verifies the expected main plugin file exists before packaging
11. rebuilds the package as a temporary ZIP
12. verifies the temporary ZIP contains the expected main plugin file
13. replaces the vendored ZIP under `modules/wordpress/mu-plugins/` only after verification passes
14. commits and pushes only if Git detects ZIP changes

For example, GitHub tag archives normally extract into names like:

```text
force-https-4.0.0/
```

SlickStack needs the ZIP to contain:

```text
force-https/
```

The workflow handles that normalization before rebuilding `force-https.zip`.

If a download, extraction, normalization, packaging, or verification step fails, the workflow exits with an error. The existing committed ZIP is not replaced until the newly built temporary ZIP passes verification.

The version and verification messages appear in the GitHub Actions run logs for the manual run. They are not committed to the repo and are not saved as artifacts.

## Before running the MU plugin workflow

Before running the workflow for a plugin, the source plugin repo should already have the intended release tag.

For example, if `force-https.php` has been updated to version `4.0.0`, the `littlebizzy/force-https` repo should have a matching `4.0.0` or `v4.0.0` tag before the workflow is run.

If the new version has not been tagged yet, the workflow will keep using the latest existing tag instead of the latest source code on the repository's default branch.

## Adding more plugins later

To add more supported MU plugin ZIP mirrors later, add approved `owner/repo` entries to the workflow plugin list.

Each plugin should follow the same packaging convention:

```text
plugin-slug.zip
└── plugin-slug/
    └── plugin-slug.php
```

The workflow intentionally assumes this simple convention because the SlickStack MU plugin installer expects predictable extracted folder names.

Repository entries should use the plain `owner/repo` form, such as `littlebizzy/force-https` or `afragen/git-updater`. Do not include URLs, tags, paths, or ZIP filenames in the plugin list.

## Related docs

See `docs/mu-plugins.md` for the server install behavior, vendored ZIP mirror rules, and custom MU plugin notes.
