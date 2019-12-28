---
title: Installation in Lumen
weight: 4
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

Also in `bootstrap/app.php` register the config file, service provider, and cache alias:

```php
$app->configure('permission');
$app->alias('cache', \Illuminate\Cache\CacheManager::class);  // if you don't have this already
$app->register(Spatie\Permission\PermissionServiceProvider::class);
```

If you are using guards you will need to uncomment the AuthServiceProvider line:
```php
$app->register(App\Providers\AuthServiceProvider::class);
```

Now, ensure your database configuration is set.

Then run the migrations:

```bash
php artisan migrate
```


NOTE: Remember that Laravel's authorization layer requires that your `User` model implement the `Illuminate\Contracts\Auth\Access\Authorizable` contract. In Lumen you will then also need to use the `Laravel\Lumen\Auth\Authorizable` trait.

