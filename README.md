# Laravel Cart Package (private)

This is a package that can be easily plugged into our current Laravel projects that require cart functionality.

## Installation

You can install the package via composer:

```bash
composer require niladam/cart
```



### Migrate and publish the config files

```php
php artisan vendor:publish --provider="Niladam\Cart\CartServiceProvider"
```



### Run the migration

```php
php artisan migrate
```



## Configuration

The package has a configuration file that needs to be edited before it'll be fully functional.

## Usage

Note the **$product** needs to be a package named eloquent.

### Add a product to the cart
``` php
cart()->add($product, $quantity, $source);
```

```$product``` needs to be an Eloquent model. The quantity needs to be integer and the source is really optional.

### Remove a product from the cart

``` php
cart()->remove($product);
```

`$product` needs to be an Eloquent model.

### Get the cart items.

```php
cart()->getCart();
```



### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email niladam@gmail.com instead of using the issue tracker.

## Credits

- [Madalin Tache](https://github.com/niladam)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
