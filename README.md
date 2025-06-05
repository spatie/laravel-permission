<div align="left">
    <a href="https://spatie.be/open-source?utm_source=github&utm_medium=banner&utm_campaign=laravel-permission">
      <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://spatie.be/packages/header/laravel-permission/html/dark.webp">
        <img alt="Logo for laravel-permission" src="https://spatie.be/packages/header/laravel-permission/html/light.webp">
      </picture>
    </a>

<h1>Associate users with permissions and roles</h1>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-permission.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-permission)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/spatie/laravel-permission/run-tests-L8.yml?branch=main&label=Tests)](https://github.com/spatie/laravel-permission/actions?query=workflow%3ATests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-permission.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-permission)
    
</div>

## Documentation, Installation, and Usage Instructions

See the [documentation](https://spatie.be/docs/laravel-permission/) for detailed installation and usage instructions.

## What It Does
This package allows you to manage user permissions and roles in a database.

Once installed you can do stuff like this:

```php
// Adding permissions to a user
$user->givePermissionTo('edit articles');

// Adding permissions via a role
$user->assignRole('writer');

$role->givePermissionTo('edit articles');
```

Because all permissions will be registered on [Laravel's gate](https://laravel.com/docs/authorization), you can check if a user has a permission with Laravel's default `can` function:

```php
$user->can('edit articles');
```

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-permission.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-permission)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

### Testing

``` bash
composer test
```

### Security

If you discover any security-related issues, please email [security@spatie.be](mailto:security@spatie.be) instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Kruikstraat 22, 2018 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Chris Brown](https://github.com/drbyte)
- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

This package is heavily based on [Jeffrey Way](https://twitter.com/jeffrey_way)'s awesome [Laracasts](https://laracasts.com) lessons
on [permissions and roles](https://laracasts.com/series/whats-new-in-laravel-5-1/episodes/16). His original code
can be found [in this repo on GitHub](https://github.com/laracasts/laravel-5-roles-and-permissions-demo).

Special thanks to [Alex Vanderbist](https://github.com/AlexVanderbist) who greatly helped with `v2`, and to [Chris Brown](https://github.com/drbyte) for his longtime support  helping us maintain the package.

Special thanks to [Caneco](https://twitter.com/caneco) for the original logo.

## Alternatives

- [Povilas Korop](https://twitter.com/@povilaskorop) did an excellent job listing the alternatives [in an article on Laravel News](https://laravel-news.com/two-best-roles-permissions-packages). In that same article, he compares laravel-permission to [Joseph Silber](https://github.com/JosephSilber)'s [Bouncer]((https://github.com/JosephSilber/bouncer)), which in our book is also an excellent package.
- [santigarcor/laratrust](https://github.com/santigarcor/laratrust) implements team support
- [ultraware/roles](https://github.com/ultraware/roles) (archived) takes a slightly different approach to its features.
- [zizaco/entrust](https://github.com/zizaco/entrust) offers some wildcard pattern matching

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
