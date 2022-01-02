#🔌 Autowire for Laravel

[![Latest Version on Packagist][ico-version]][link-packagist]
[![CI][ico-actions]][link-actions]

Autowire interfaces to implementations using PHP 8 Attributes.

## Installation

Via Composer

``` bash
composer require jeroen-g/autowire
```

You will need the configuration file to change where it should look:

```bash
php artisan vendor:publish --tag=autowire.config
```

## Usage

Are you tired of binding abstract interfaces to concrete classes all the time?

```php
$this-app->bind(HelloInterface::class, WorldClass::class);
```

Use the PHP 8 attribute of this package to autowire any of your interfaces:

```php
namespace App\Contracts;

use JeroenG\Autowire\Attribute\Autowire;

#[Autowire]
interface HelloInterface
{
    public function hello(): string;
}
```

The class that implements that interface does not need any changes:

```php
namespace App;

use App\Contracts\HelloInterface;

class WorldClass implements HelloInterface
{
    public function hello(): string
    {
        return 'world';
    }
}
```

The Autowire package will crawl through the classes and bind the abstract interface to the concrete class.
If there already is a binding in the container it will skip the autowiring.

### Configuration

The package's configuration can be found in `config/autowire.php`.
It should contain the list of directories where Autowire should look for both interfaces and implementations. 

## Changelog

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Credits

- [Jeroen][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/jeroen-g/autowire.svg?style=flat-square
[ico-actions]: https://img.shields.io/github/workflow/status/Jeroen-G/autowire/CI?label=CI%2FCD&style=flat-square

[link-actions]: https://github.com/Jeroen-G/autowire/actions?query=workflow:CI
[link-packagist]: https://packagist.org/packages/jeroen-g/autowire
[link-author]: https://github.com/jeroen-g
[link-contributors]: ../../contributors
