## Changelog

### Alpha (all releases)
* support for `Ubuntu 18.04 LTS` (only)
* `ss-config` file contains all SlickStack config settings
* public web root hardcoded to `/var/www/html` (supports single domain only)
* all critical logs under `/var/www/logs` (can be accessed by SFTP user or otherwise)
* FastCGI cache files under `/var/www/cache` (OPcache File Cache planned as a subdirectory here)
* general meta directory introduced under `/var/www/meta` for future uses
