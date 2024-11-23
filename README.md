# ArtisanWizard is a user-friendly Laravel package that brings interactivity to your artisan commands.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/meego47/artisan-wizard.svg?style=flat-square)](https://packagist.org/packages/meego47/artisan-wizard)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/meego47/artisan-wizard/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/meego47/artisan-wizard/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/meego47/artisan-wizard/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/meego47/artisan-wizard/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/meego47/artisan-wizard.svg?style=flat-square)](https://packagist.org/packages/meego47/artisan-wizard)

Artisan Wizard is a Laravel package that provides an interactive interface for running Artisan commands. It helps developers streamline their workflow by allowing them to interactively select, configure, and execute Artisan commands within a simple, guided process. This package is ideal for those looking to automate repetitive tasks, fill in required command arguments and options step-by-step, and execute commands with ease—all from within the Laravel environment.

## Installation (TBD)

You can install the package via composer:

```bash
composer require meego47/artisan-wizard
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="artisan-wizard-config"
```

This is the contents of the published config file:

```php
return [
];
```
## Usage

```sh
php artisan artisan-wizard:run
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ruslan Mironcenco](https://github.com/meego47)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
