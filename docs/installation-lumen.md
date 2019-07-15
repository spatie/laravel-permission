---
title: Installation in Lumen
weight: 4
---

You can install the package via Composer:

``` bash
composer require spatie/laravel-permission
```

Copy the required files:

```bash
mkdir -p config
cp vendor/spatie/laravel-permission/config/permission.php config/permission.php
cp vendor/spatie/laravel-permission/database/migrations/create_permission_tables.php.stub database/migrations/2018_01_01_000000_create_permission_tables.php
```

You will also need to create another configuration file at `config/auth.php`. Get it on the Laravel repository or just run the following command:

```bash
curl -Ls https://raw.githubusercontent.com/laravel/lumen-framework/5.7/config/auth.php -o config/auth.php
```

Then, in `bootstrap/app.php`, register the middlewares:

```php
$app->routeMiddleware([
    'auth'       => App\Http\Middleware\Authenticate::class,
    'permission' => Spatie\Permission\Middlewares\PermissionMiddleware::class,
    'role'       => Spatie\Permission\Middlewares\RoleMiddleware::class,
]);
```

As well as the config file, service provider, and cache alias:

```php
$app->configure('permission');
$app->alias('cache', \Illuminate\Cache\CacheManager::class);  // if you don't have this already
$app->register(Spatie\Permission\PermissionServiceProvider::class);
```

Now, run your migrations:

```bash
php artisan migrate
```
