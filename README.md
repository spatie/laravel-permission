# Associate users with roles and permissions

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
//adding permissions to a user
$user->givePermissionTo('edit articles');

//adding permissions via a role
$user->assignRole('writer');
$user2->assignRole('writer');

$role->givePermissionTo('edit articles');
```

You can test if a user has a permission with Laravel's default `can`-function.
```php
$user->can('edit articles');
```

Spatie is webdesign agency in Antwerp, Belgium. You'll find an overview of all 
our open source projects [on our website](https://spatie.be/opensource).

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

You can publish the config-file with:
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Authorization Models
    |--------------------------------------------------------------------------
    */

    'models' => [
        /*
         * The class name of the permission model to be used.
         */
        'permission' => 'Spatie\Permission\Models\Permission',

        /*
         * The class name of the role model to be used.
         */
        'role' => 'Spatie\Permission\Models\Role',

        /*
         * The class name of the user model to be used.
         */
        'user' => 'App\User',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization Tables
    |--------------------------------------------------------------------------
    */

    'tableNames' => [
        /*
         * The name of the "users" table to be used.
         */
        'users' => 'users',

        /*
         * The name of the "roles" table to be used.
         */
        'roles' => 'roles',

        /*
         * The name of the "permissions" table to be used.
         */
        'permissions' => 'permissions',

        /*
         * The name of the "user_has_permissions" table to be used.
         */
        'user_has_permissions' => 'user_has_permissions',

        /*
         * The name of the "user_has_roles" table to be used.
         */
        'user_has_roles' => 'user_has_roles',

        /*
         * The name of the "role_has_permissions" table to be used.
         */
        'role_has_permissions' => 'role_has_permissions',
    ],

];
```

And finally add the `Spatie\Permission\Traits\HasRoles`-trait to the User model.



## Usage

This package allows for users to be associated with roles. Permissions can be 
associated with roles.
A `Role` and a `Permission` are regular Eloquent-models. They can have a name 
and can be created like this:

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$role = Role::create(['name' => 'writer']);
$permission = Permission::create(['name' => 'edit articles']);
```

###Using permissions
A permission can be given to a user:

```php
$user->givePermissionTo('edit articles');
```

A permission can be revoked from a user:

```php
$user->revokePermissionTo('edit articles');
```

You can test if a user has a permission:
```php
$user->hasPermissionTo('edit articles');
```

Saved permissions will be registered with the `Illuminate\Auth\Access\Gate`-class. So you can
test if a user has a permission with Laravel's default `can`-function.
```php
$user->can('edit articles');
```

###Using roles and permissions
A role can be assigned to a user:

```php
$user->assignRole('writer');
```

A role can be removed from a user:

```php
$user->removeRole('writer');
```
You can determine if a user has a certain role:

```php
$user->hasRole('writer');
```

You can also determine if a user has any of a given list of roles:
```php
$user->hasAnyRole(Role::all());
```
You can also determine if a user has all of a given list of roles:

```php
$user->hasAllRoles(Role::all());
```

The `assignRole`, `hasRole`, `hasAnyRole`, `hasAllRoles`  and `removeRole`-functions can accept a
 string, a `Spatie\Permission\Models\Role`-object or an `\Illuminate\Support\Collection`-object.

A permission can be given to a role:

```php
$role->givePermissionTo('edit articles');
```

A permission can be revoked from a role:

```php
$role->revokePermissionTo('edit articles');
```

The `givePermissionTo` and `revokePermissionTo`-functions can accept a 
string or a `Spatie\Permission\Models\Permission`-object.

Saved permission and roles are also registered with the `Illuminate\Auth\Access\Gate`-class.
```php
$user->can('edit articles');
```

###Using blade directives
This package also adds Blade directives to verify whether the
currently logged in user has all or any of a given list of roles.

```php
@role('writer')
I'm a writer!
@else
I'm not a writer...
@endrole
```

```php
@hasrole('writer')
I'm a writer!
@else
I'm not a writer...
@endhasrole
```

```php
@hasanyrole(Role::all())
I have one or more of these roles!
@else
I have none of these roles...
@endhasanyrole
```

```php
@hasallroles(Role::all())
I have all of these roles!
@else
I don't have all of these roles...
@endhasallroles
```

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
on [roles and permissions](https://laracasts.com/series/whats-new-in-laravel-5-1/episodes/16). His original code
can be found [in this repo on GitHub](https://github.com/laracasts/laravel-5-roles-and-permissions-demo).

## Alternatives

- [BeatSwitch/lock-laravel](https://github.com/BeatSwitch/lock-laravel)
- [Zizaco/entrust](https://github.com/Zizaco/entrust)
- [JosephSilber/bouncer](https://github.com/JosephSilber/bouncer)

## About Spatie
Spatie is webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
