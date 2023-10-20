![WP tested 6.3.2](https://img.shields.io/badge/WP-Tested_v6.3.2-blue)
![QM tested up to 3.13.1](https://img.shields.io/badge/QM-Tested_v3.13.1-blue)
![License: GPL v3](https://img.shields.io/badge/License-GPL_v3-blue)
[![PHPCS](https://github.com/crstauf/query-monitor-extend/actions/workflows/phpcs.yml/badge.svg)](https://github.com/crstauf/query-monitor-extend/actions/workflows/phpcs.yml)
[![PHPStan](https://github.com/crstauf/query-monitor-extend/actions/workflows/phpstan.yml/badge.svg)](https://github.com/crstauf/query-monitor-extend/actions/workflows/phpstan.yml)

# Query Monitor Extend

> WordPress plugin with customizations to enhance/extend the already awesome [Query Monitor](https://github.com/johnbillion/query-monitor) plugin by [John Blackbourn](https://github.com/johnbillion/).


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