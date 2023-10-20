![WP tested 6.3.2](https://img.shields.io/badge/WP-Tested_v6.3.2-blue)
![QM tested up to 3.13.1](https://img.shields.io/badge/QM-Tested_v3.13.1-blue)
![License: GPL v3](https://img.shields.io/badge/License-GPL_v3-blue)
[![PHPCS](https://github.com/crstauf/query-monitor-extend/actions/workflows/phpcs.yml/badge.svg)](https://github.com/crstauf/query-monitor-extend/actions/workflows/phpcs.yml)
[![PHPStan](https://github.com/crstauf/query-monitor-extend/actions/workflows/phpstan.yml/badge.svg)](https://github.com/crstauf/query-monitor-extend/actions/workflows/phpstan.yml)

# Query Monitor Extend

> WordPress plugin with customizations to enhance and extend the already awesome [Query Monitor](https://github.com/johnbillion/query-monitor) plugin by [John Blackbourn](https://github.com/johnbillion/).

## Panels

| Panel       | Description |
| :---------- | :---------- |
| ACF         | Calls to `the_field()` and `get_field()`, and [Local JSON](https://www.advancedcustomfields.com/resources/local-json/) configuration |
| Constants   | User defined constants: [`get_defined_constants( true )['user']`](https://www.php.net/manual/en/function.get-defined-constants.php) |
| Files       | Included files: [`get_included_files()`](https://www.php.net/manual/en/function.get-included-files.php) |
| $_SERVER    | Dump of `$_SERVER` |
| $_GET       | Dump of `$_GET` if set |
| $_POST      | Dump of `$_POST` if set |
| Heartbeats  | Monitors [WordPress' Heartbeat](https://developer.wordpress.org/plugins/javascript/heartbeat-api/) |
| Image Sizes | Names, count, width, height, ratio, cropped, and source of registered [image sizes](https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/) |
| Paths       | Constants and functions for WordPress URLs and paths |
| Time        | Current time in timezones: UTC, server, WordPress, and browser |

## Installing

Recent [releases](https://github.com/crstauf/query-monitor-extend/releases) contain zip files for installation as a WordPress [plugin](https://github.com/crstauf/query-monitor-extend/releases/latest/download/plugin.zip) and [mu-plugin](https://github.com/crstauf/query-monitor-extend/releases/latest/download/mu-plugin.zip).

### Composer

If you prefer to use [Composer](https://getcomposer.org/), you can install by using this repository as the source.

Add the following to your project's `composer.json`:

```json
"repositories": [
	{
		"type": "vcs",
		"url": "https://github.com/crstauf/query-monitor-extend.git"
	}
]
```

And then install:

```
composer require --dev crstauf/query-monitor-extend
```