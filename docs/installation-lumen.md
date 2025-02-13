---
title: Installation in Lumen
weight: 5
---

NOTE: Lumen is **not** officially supported by this package. And Lumen is no longer under active development.

However, the following are some steps which may help get you started.

Lumen installation instructions can be found in the [Lumen documentation](https://lumen.laravel.com/docs/master).

## Installing

Install the permissions package via Composer:

``` bash
composer require spatie/laravel-permission
```

Copy the required files:

```bash
mkdir -p config
cp vendor/spatie/laravel-permission/config/permission.php config/permission.php
cp vendor/spatie/laravel-permission/database/migrations/create_permission_tables.php.stub database/migrations/2018_01_01_000000_create_permission_tables.php
```

You will also need the `config/auth.php` file. If you don't already have it, copy it from the vendor folder:

```bash
cp vendor/laravel/lumen-framework/config/auth.php config/auth.php
```

Next, if you wish to use this package's middleware, clone whichever ones you want from `Spatie\Permission\Middleware` namespace into your own `App\Http\Middleware` namespace AND replace the `canAny()` call with `hasAnyPermission()` (because Lumen doesn't support `canAny()`).

Then, in `bootstrap/app.php`, uncomment the `auth` middleware, and register the middleware you've created. For example:

```php
$app->routeMiddleware([
    'auth'       => App\Http\Middleware\Authenticate::class,
    'permission' => App\Http\Middleware\PermissionMiddleware::class, // cloned from Spatie\Permission\Middleware
    'role'       => App\Http\Middleware\RoleMiddleware::class,  // cloned from Spatie\Permission\Middleware
]);
```

... and also in `bootstrap/app.php`, in the ServiceProviders section, register the package configuration, service provider, and cache alias:

```php
$app->configure('permission');
$app->alias('cache', \Illuminate\Cache\CacheManager::class);  // if you don't have this already
$app->register(Spatie\Permission\PermissionServiceProvider::class);
```

... and in the same file, since the Authorization layer uses guards you will need to uncomment the AuthServiceProvider line:
```php
$app->register(App\Providers\AuthServiceProvider::class);
```

Ensure the application's database name/credentials are set in your `.env` (or `config/database.php` if you have one), and that the database exists.

NOTE: If you are going to use teams feature, you have to update your [`config/permission.php` config file](https://github.com/spatie/laravel-permission/blob/main/config/permission.php) and set `'teams' => true,`, if you want to use a custom foreign key for teams you must change `team_foreign_key`.

Run the migrations to create the tables for this package:

```bash
php artisan migrate
```

---
## User Model
NOTE: Remember that Laravel's authorization layer requires that your `User` model implement the `Illuminate\Contracts\Auth\Access\Authorizable` contract. In Lumen you will then also need to use the `Laravel\Lumen\Auth\Authorizable` trait. Note that Lumen does not support the `User::canAny()` authorization method.

---
## User Table
NOTE: If you are working with a fresh install of Lumen, then you probably also need a migration file for your Users table. You can create your own, or you can copy a basic one from Laravel:

[https://github.com/laravel/laravel/blob/master/database/migrations/0001_01_01_000000_create_users_table.php](https://github.com/laravel/laravel/blob/master/database/migrations/0001_01_01_000000_create_users_table.php)

(You will need to run `php artisan migrate` after adding this file.)

Remember to update your `UserFactory.php` to match the fields in the migration you create/copy.
