# laravel-permission

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-permission.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-permission)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/laravel-permission/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-permission)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/a25f93ac-5e8f-48c8-a9a1-5d3ef3f9e8f2.svg?style=flat-square)](https://insight.sensiolabs.com/projects/a25f93ac-5e8f-48c8-a9a1-5d3ef3f9e8f2)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-permission.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-permission)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-permission.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-permission)

This package allows to save permissions and roles in a database. It is built upon the [Laravel's
authorization functionality](http://laravel.com/docs/5.1/authorization) that 
was [introduced in version 5.1.11](http://christoph-rumpel.com/2015/09/new-acl-features-in-laravel/)

Once installed you can do stuff like this:

```php
//writer is an instance of Spatie\Permission\Models\Role
$role->givePermissionTo('edit articles');

$user->assignRole('writer');

$user->can('edit articles');
```

Spatie is webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Install

You can install the package via composer:
``` bash
$ composer require spatie/laravel-permission
```

This service provider must be installed.
```php
// config/app.php
'providers' => [
    ...
    Spatie\Permission\PermissionServiceProvider::class,
];
```

You can publish the migration with:
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"
```

After the migration has been published you can create the role- and permission-tables by
running the migrations:

```bash
php artisan migrate
```

## Usage



## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email [freek@spatie.be](mailto:freek@spatie.be) instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

This package is heavily based on [Jeffrey Way](https://twitter.com/jeffrey_way)'s awesome [Laracasts](https://laracasts.com)-lesson 
on [roles and persmissions](https://laracasts.com/series/whats-new-in-laravel-5-1/episodes/16).
 

## About Spatie
Spatie is webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
