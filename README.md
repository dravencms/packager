# DravenCMS Packager

Composer package synchronizer for DravenCMS applications. It discovers packages of type `dravencms-package`, installs their configuration and public assets from Composer metadata, and removes artifacts belonging to packages that are no longer installed.

## Installation

```bash
composer require dravencms/packager
```

The package installs the `bin/packager` executable. The `dravencms/dravencms` metapackage runs synchronization automatically after Composer install and update operations.

## Package Metadata

Feature packages declare installable files in `composer.json`:

```json
{
  "type": "dravencms-package",
  "extra": {
    "dravencms": {
      "configuration": "dravencms.config.neon",
      "files": {
        "assets/*": "%wwwDir%/assets/example"
      }
    }
  }
}
```

## Commands

Run commands through the package executable:

```bash
php vendor/dravencms/packager/bin/packager packager:sync
php vendor/dravencms/packager/bin/packager packager:install dravencms/example
php vendor/dravencms/packager/bin/packager packager:uninstall dravencms/example
```

Use `packager:uninstall --purge dravencms/example` to remove the installed configuration as well as package assets.

When synchronization finds a locally modified generated configuration, it prompts to keep it, display a diff, overwrite it, or stop. Review the diff before overwriting project-specific changes.

## License

This package is licensed under the LGPL-3.0 license.
