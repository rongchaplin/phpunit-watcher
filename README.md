
# Automatically rerun PHPUnit tests when source code changes

[![Latest Version on Packagist](https://img.shields.io/packagist/v/t73biz/phpunit-watcher.svg?style=flat-square)](https://packagist.org/packages/spatie/phpunit-watcher)
![Tests](https://github.com/rongchaplin/phpunit-watcher/workflows/Tests/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/t73biz/phpunit-watcher.svg?style=flat-square)](https://packagist.org/packages/spatie/phpunit-watcher)

Wouldn't it be great if your PHPUnit tests would be automatically rerun whenever you change some code? This package can do exactly that.

With the package installed you can do this:

```bash
phpunit-watcher watch
```

Here's how it looks like:

![watcher screenshot](https://spatie.github.io/phpunit-watcher/screenshots/watcher.jpg)

This will run the tests and rerun them whenever a file in the `app`, `src` or `tests` directory is modified.

Want to pass some arguments to PHPUnit? No problem, just tack them on:

```bash
phpunit-watcher watch --filter=it_can_run_a_single_test
```

In his excellent talk at Laracon EU 2017 [Amo Chohan](https://github.com/amochohan) shows our phpunit-watcher in action.

[![Amo Chohan demo](https://spatie.github.io/phpunit-watcher/videothumb.png)](https://youtu.be/CF1UhUj9LG0?t=26m13s)

## Installation

You can install this package globally like this

```bash
composer global require t73biz/phpunit-watcher
```

After that `phpunit-watcher watch` can be run in any directory on your system.

Alternatively you can install the package locally as a dev dependency in your project

```bash
composer require t73biz/phpunit-watcher --dev
```

Locally installed you can run it with `vendor/bin/phpunit-watcher watch`

## Usage

All the examples assume you've installed the package globally. If you opted for the local installation prepend `vendor/bin/` everywhere where `phpunit-watcher` is mentioned.

You can start the watcher with:

```bash
phpunit-watcher watch
```

This will run the tests and rerun them whenever a file in the `src` or `tests` directory is modified.

Want to pass some arguments to PHPUnit? No problem, just tack them on:

```bash
phpunit-watcher watch --filter=it_can_run_a_single_test
```

#### Notes on interactive commands

When running `phpunit-watcher` from a Composer script, you may need to [redirect input](https://github.com/spatie/phpunit-watcher/issues/54) in order for the interactive commands to work and [disabled the default timeout](https://getcomposer.org/doc/06-config.md#process-timeout):

```json
{
    "scripts": {
        "test:watch": [
            "Composer\\Config::disableProcessTimeout",
            "phpunit-watcher watch < /dev/tty"
        ]
    }
}
```

On Windows, Currently, TTY is not being supported, so any interaction has been disabled. While watching for changes works,
any arguments for PHPUnit have to be provided when initially calling `phpunit-watcher`.

## Customization

Certain aspects of the behaviour of the tool can be modified. The file for options may be named `.phpunit-watcher.yml`, `phpunit-watcher.yml` or `phpunit-watcher.yml.dist`. The tool will look for a file in that order.

If a config file does not exist in the project directory, the tool will check if a file exists in any of the parent directories of the project directory.

Here's some example content. Read on for a more detailed explanation of all the options.

```yaml
watch:
  directories:
    - src
    - tests
  fileMask: '*.php'
notifications:
  passingTests: false
  failingTests: false
phpunit:
  binaryPath: vendor/bin/phpunit
  arguments: '--stop-on-failure'
  timeout: 180
```

### Customize watched directories and files

You can customize the directories being watched by creating a file named `.phpunit-watcher.yml` in your project directory. Here's some example content:

```yaml
watch:
  directories:
    - src
    - tests
  exclude:
    - lib
  fileMask: '*.php'
  ignoreDotFiles: true
  ignoreVCS: true
  ignoreVCSIgnored: false
```

See [the documentation for Finder](https://symfony.com/doc/current/components/finder.html) for more details.

If you experience performance delays with large repositories, try adding `exclude` entries for any large subdirectories that you don't need to watch. Enabling the `ignore...` options can also be helpful. It's also important to ensure you're also using the `'*.php'` file mask.

### Desktop notifications

By default the tool will display desktop notifications whenever the tests pass or fail. If you want to disable certain desktop notifications update `.phpunit-watcher.yml` by adding a `notifications` key.

```yaml
notifications:
  passingTests: false
  failingTests: false
```

### Help messages
By default the tool will display a helper for keyboard actions after each run. You can hide these help messages by adding a `hideManual` key in the `.phpunit-watcher.yml`.

```yaml
hideManual: true
```

### Customize PHPUnit

#### Binary

By default the tool use `vendor/bin/phpunit` as default PHPUnit binary file, however, it may be useful to be able to customize this value for people who have a binary file in a different location. 

You can specificy it in the `.phpunit-watcher.yml` config file. Here's an example:

```yaml
phpunit:
  binaryPath: ./vendor/phpunit/phpunit/phpunit
```

#### Initial arguments

If you want to use pass the same arguments to PHPUnit everytime to watcher starts, you can specify those in the `.phpunit-watcher.yml` config file. Here's an example:

```yaml
phpunit:
  arguments: '--stop-on-failure'
```

When starting the tool with some arguments (eg `phpunit-watcher watch --filter=my_favourite_test`) those arguments will get used instead of the ones specified in the config file.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](https://github.com/t73biz/.github/blob/main/CONTRIBUTING.md) for details.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [Ron Chaplin](https://github.com/t73biz)
- [All Contributors](../../contributors)

We started creating this package after reading [this excellent article](https://www.sitepoint.com/write-javascript-style-test-watchers-php/) by [Christoper Pitt](https://twitter.com/assertchris)

Interactive commands were inspired by [Jest](https://facebook.github.io/jest/).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
