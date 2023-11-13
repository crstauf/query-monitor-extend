![WP tested 6.3.2](https://img.shields.io/badge/WP-Tested_v6.3.2-blue)
![QM tested up to 3.14.0](https://img.shields.io/badge/QM-Tested_v3.14.0-blue)
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
| $_SERVER    | Dump of [`$_SERVER`](https://www.php.net/manual/en/reserved.variables.server.php) |
| $_GET       | Dump of [`$_GET`](https://www.php.net/manual/en/reserved.variables.get.php) (if set) |
| $_POST      | Dump of [`$_POST`](https://www.php.net/manual/en/reserved.variables.post.php) (if set) |
| Heartbeats  | Monitors [WordPress' Heartbeat](https://developer.wordpress.org/plugins/javascript/heartbeat-api/) |
| Image Sizes | Names, count, width, height, ratio, cropped, and source of registered [image sizes](https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/) |
| Paths       | Constants and functions for WordPress URLs and paths |
| Time        | Current time in timezones: UTC, server, WordPress, and browser |

## Demo

Demos of QMX are available via the [WordPress Playground](https://developer.wordpress.org/playground/):

- [Install as plugin](https://playground.wordpress.net/#%7B%22landingPage%22:%22/wp-admin/plugins.php%22,%22steps%22:%5B%7B%22step%22:%22login%22,%22username%22:%22admin%22,%22password%22:%22password%22%7D,%7B%22step%22:%22installPlugin%22,%22pluginZipFile%22:%7B%22resource%22:%22wordpress.org/plugins%22,%22slug%22:%22query-monitor%22%7D%7D,%7B%22step%22:%22installPlugin%22,%22pluginZipFile%22:%7B%22resource%22:%22url%22,%22url%22:%22https://calebstauffer.wpengine.com/plugin-proxy.php?repo=crstauf/query-monitor-extend&name=plugin.zip%22,%22caption%22:%22Installing%20Query%20Monitor%20Extend%22%7D%7D%5D%7D)
- [Install as mu-plugin](https://playground.wordpress.net/#%7B%22landingPage%22:%22/wp-admin/plugins.php?plugin_status=mustuse%22,%22steps%22:%5B%7B%22step%22:%22login%22,%22username%22:%22admin%22,%22password%22:%22password%22%7D,%7B%22step%22:%22installPlugin%22,%22pluginZipFile%22:%7B%22resource%22:%22wordpress.org/plugins%22,%22slug%22:%22query-monitor%22%7D%7D,%7B%22step%22:%22mkdir%22,%22path%22:%22/wordpress/qmx%22%7D,%7B%22step%22:%22writeFile%22,%22path%22:%22/wordpress/qmx/mu-plugin.zip%22,%22data%22:%7B%22resource%22:%22url%22,%22url%22:%22https://calebstauffer.wpengine.com/plugin-proxy.php?repo=crstauf/query-monitor-extend&name=mu-plugin.zip%22,%22caption%22:%22Downloading%20Query%20Monitor%20Extend%22%7D,%22progress%22:%7B%22weight%22:2,%22caption%22:%22Installing%20Query%20Monitor%20Extend%22%7D%7D,%7B%22step%22:%22unzip%22,%22zipPath%22:%22/wordpress/qmx/mu-plugin.zip%22,%22extractToPath%22:%22/wordpress/qmx%22%7D,%7B%22step%22:%22mv%22,%22fromPath%22:%22/wordpress/qmx/mu-plugins/query-monitor-extend%22,%22toPath%22:%22/wordpress/wp-content/mu-plugins/query-monitor-extend%22%7D,%7B%22step%22:%22mv%22,%22fromPath%22:%22/wordpress/qmx/mu-plugins/load-qmx.php%22,%22toPath%22:%22/wordpress/wp-content/mu-plugins/load-qmx.php%22%7D%5D%7D)

See [Demos document](.github/demos.md) for more info.

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