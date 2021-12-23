<p align="center"><img src="/art/socialcard.png" alt="Social Card of Laravel Permission"></p>

# Associate users with permissions and roles

### Sponsor

<table>
   <tr>
      <td><img src="https://user-images.githubusercontent.com/6287961/92889815-d64c3d80-f416-11ea-894a-b4de7e8f7eef.png"></td>
      <td>If you want to quickly add authentication and authorization to Laravel projects, feel free to check Auth0's Laravel SDK and free plan at <a href="https://auth0.com/developers?utm_source=GHsponsor&utm_medium=GHsponsor&utm_campaign=laravel-permission&utm_content=auth">https://auth0.com/developers</a>.</td>
   </tr>
</table>


[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-permission.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-permission)
![](https://github.com/spatie/laravel-permission/workflows/Run%20Tests/badge.svg?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-permission.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-permission)

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

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security-related issues, please email [freek@spatie.be](mailto:freek@spatie.be) instead of using the issue tracker.

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

And a special thanks to [Caneco](https://twitter.com/caneco) for the logo ✨

## Alternatives

- [Povilas Korop](https://twitter.com/@povilaskorop) did an excellent job listing the alternatives [in an article on Laravel News](https://laravel-news.com/two-best-roles-permissions-packages). In that same article, he compares laravel-permission to [Joseph Silber](https://github.com/JosephSilber)'s [Bouncer]((https://github.com/JosephSilber/bouncer)), which in our book is also an excellent package.
- [ultraware/roles](https://github.com/ultraware/roles) takes a slightly different approach to its features.
- [santigarcor/laratrust](https://github.com/santigarcor/laratrust) implements team support
- [zizaco/entrust](https://github.com/zizaco/entrust) offers some wildcard pattern matching

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
