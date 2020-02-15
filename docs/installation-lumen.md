---
title: Installation in Lumen
weight: 5
---

NOTE: Lumen is not officially supported by this package. However, the following are some steps which may help get you started.

First, install the package via Composer:

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

Then, in `bootstrap/app.php`, uncomment the `auth` middleware, and register this package's middleware:

```php
$app->routeMiddleware([
    'auth'       => App\Http\Middleware\Authenticate::class,
    'permission' => Spatie\Permission\Middlewares\PermissionMiddleware::class,
    'role'       => Spatie\Permission\Middlewares\RoleMiddleware::class,
]);
```

... and in the same file, in the ServiceProviders section, register the package configuration, service provider, and cache alias:

```php
$app->configure('permission');
$app->alias('cache', \Illuminate\Cache\CacheManager::class);  // if you don't have this already
$app->register(Spatie\Permission\PermissionServiceProvider::class);
```

... and in the same file, since the Authorization layer uses guards you will need to uncomment the AuthServiceProvider line:
```php
$app->register(App\Providers\AuthServiceProvider::class);
```

Ensure your database configuration is set in your `.env` (or `config/database.php` if you have one).

Run the migrations to create the tables for this package:

```bash
php artisan migrate
```

---
### User Model
NOTE: Remember that Laravel's authorization layer requires that your `User` model implement the `Illuminate\Contracts\Auth\Access\Authorizable` contract. In Lumen you will then also need to use the `Laravel\Lumen\Auth\Authorizable` trait.

---
### User Table
NOTE: If you are working with a fresh install of Lumen, then you probably also need a migration file for your Users table. You can create your own, or you can copy a basic one from Laravel:

https://github.com/laravel/laravel/blob/master/database/migrations/2014_10_12_000000_create_users_table.php

(You will need to run `php artisan migrate` after adding this file.)

Remember to update your ModelFactory.php to match the fields in the migration you create/copy.
