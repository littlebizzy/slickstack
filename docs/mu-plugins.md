# WordPress MU Plugins

SlickStack installs a small set of WordPress MU plugins during `ss-install-wordpress-mu-plugins`.

The installer does not fetch most plugin packages directly from their standalone repositories during server installs. Instead, SlickStack keeps approved ZIP mirrors under:

```bash
modules/wordpress/mu-plugins/
```

The public download variables in `ss-functions` point to those vendored ZIP files inside the SlickStack repo. This keeps SlickStack installs simple, repeatable, and independent from live release API lookups on end-user servers.

## Install behavior

The MU plugin installer downloads the ZIP files from the SlickStack mirror path, extracts them under `/tmp`, and copies the extracted plugin directories into the active WordPress MU plugin directory.

For the default MU plugins, the installer expects each ZIP to extract into a clean top-level directory matching the plugin slug, for example:

```text
force-https.zip
└── force-https/
    └── force-https.php
```

That extracted directory name matters because the installer copies paths such as:

```bash
/tmp/force-https
```

into the production, staging, or development MU plugin directories as needed.

## Default plugin ZIPs

The current default installer downloads these vendored ZIPs:

```text
clear-caches.zip
disable-empty-trash.zip
disable-image-compression.zip
disable-xml-rpc.zip
force-https.zip
plugin-blacklist.zip
repoman.zip
```

The related remote path variables are defined in `bash/ss-functions.txt`, and the install/copy logic is handled in `bash/ss-install-wordpress-mu-plugins.txt`.

## Additional vendored ZIPs

The module directory also contains additional optional or legacy plugin ZIPs. These files are available as public mirrors but are not necessarily downloaded by the default installer.

`git-updater.zip` is an explicitly approved external mirror sourced from `afragen/git-updater`. It is not part of the default installer set.

See `modules/wordpress/mu-plugins/index.md` for the module directory listing.

## Updating vendored ZIPs

Vendored ZIP files should be updated inside the SlickStack repo before they are used by servers. This avoids doing live GitHub API release lookups during server installs, which can be unreliable because of rate limits and network conditions.

The preferred approach is to use the manual GitHub Actions workflow documented in `docs/workflows.md`. That workflow pulls the latest tagged archive from each approved plugin repository, normalizes the extracted folder name, rebuilds the ZIP under `modules/wordpress/mu-plugins/`, and commits changed ZIPs back to SlickStack.

The current workflow covers every default installer ZIP plus the explicitly approved `afragen/git-updater` mirror. Other optional or legacy ZIPs remain outside the workflow until their source repositories are reviewed and added to its approved list.

## Rules for ZIP mirrors

Each vendored MU plugin ZIP should follow these rules:

- the ZIP filename should match the plugin slug, such as `force-https.zip`
- the top-level directory inside the ZIP should match the plugin slug, such as `force-https/`
- the main plugin file should be inside that directory, such as `force-https/force-https.php`
- the ZIP should be committed only after the source plugin repo has an intended tagged version
- server install scripts should keep using SlickStack mirror paths instead of querying plugin repo release APIs directly

## Custom MU plugins

SlickStack also supports custom MU plugin sources through `ss-config` variables such as `WP_MU_PLUGINS`, `MU_PLUGIN_01_SOURCE`, and `MU_PLUGIN_01_DIR`.

Custom MU plugin installs are separate from the default vendored ZIP mirrors. The vendored ZIP workflow is intended for explicitly approved SlickStack-maintained packages, not arbitrary custom plugin sources.
