# WordPress MU Plugins

SlickStack installs a small set of WordPress MU plugins during `ss-install-wordpress-mu-plugins`.

The installer does not fetch most LittleBizzy plugin packages directly from their standalone plugin repositories during server installs. Instead, SlickStack keeps approved ZIP mirrors under:

```bash
modules/wordpress/mu-plugins/
```

The public download variables in `ss-functions` point to those vendored ZIP files inside the SlickStack repo. This keeps SlickStack installs simple, repeatable, and independent from live release API lookups on end-user servers.

## Install behavior

The MU plugin installer downloads the ZIP files from the SlickStack mirror path, extracts them under `/tmp`, and copies the extracted plugin directories into the active WordPress MU plugin directory.

For the default LittleBizzy MU plugins, the installer expects each ZIP to extract into a clean top-level directory matching the plugin slug, for example:

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

The default LittleBizzy MU plugin ZIPs currently include packages such as:

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

## Updating vendored ZIPs

Vendored ZIP files should be updated inside the SlickStack repo before they are used by servers. This avoids doing live GitHub API release lookups during server installs, which can be unreliable because of rate limits and network conditions.

The preferred approach is to use the manual GitHub Actions workflow documented in `docs/workflows.md`. That workflow can pull the latest tagged release archive from an approved LittleBizzy plugin repo, normalize the extracted folder name, rebuild the ZIP under `modules/wordpress/mu-plugins/`, and commit the changed ZIP back to SlickStack.

For the first version, the workflow only supports `force-https.zip`. Other plugin repos can be added later by extending the workflow plugin list.

## Rules for ZIP mirrors

Each vendored MU plugin ZIP should follow these rules:

- the ZIP filename should match the plugin slug, such as `force-https.zip`
- the top-level directory inside the ZIP should match the plugin slug, such as `force-https/`
- the main plugin file should be inside that directory, such as `force-https/force-https.php`
- the ZIP should be committed only after the source plugin repo has an intended tagged version
- server install scripts should keep using SlickStack mirror paths instead of querying plugin repo release APIs directly

## Custom MU plugins

SlickStack also supports custom MU plugin sources through `ss-config` variables such as `WP_MU_PLUGINS`, `MU_PLUGIN_01_SOURCE`, and `MU_PLUGIN_01_DIR`.

Custom MU plugin installs are separate from the default vendored LittleBizzy ZIP mirrors. The vendored ZIP workflow is intended for SlickStack-maintained default packages, not arbitrary custom plugin sources.
