# A Filament plugin to inspect Composer and NPM dependencies, check available updates, and provide suggested upgrade commands.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/daljo25/filament-dependency-manager.svg?style=flat-square)](https://packagist.org/packages/daljo25/filament-dependency-manager)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/daljo25/filament-dependency-manager/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/daljo25/filament-dependency-manager/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/daljo25/filament-dependency-manager/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/daljo25/filament-dependency-manager/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/daljo25/filament-dependency-manager.svg?style=flat-square)](https://packagist.org/packages/daljo25/filament-dependency-manager)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require daljo25/filament-dependency-manager
```

> [!IMPORTANT]
> If you have not set up a custom theme and are using Filament Panels follow the instructions in the [Filament Docs](https://filamentphp.com/docs/4.x/styling/overview#creating-a-custom-theme) first.

After setting up a custom theme add the plugin's views to your theme css file or your app's css file if using the standalone packages.

```css
@source '../../../../vendor/daljo25/filament-dependency-manager/resources/**/*.blade.php';
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-dependency-manager-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-dependency-manager-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-dependency-manager-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$filamentDependencyManager = new Daljo25\FilamentDependencyManager();
echo $filamentDependencyManager->echoPhrase('Hello, Daljo25!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Daljomar Morillo](https://github.com/daljo25)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
