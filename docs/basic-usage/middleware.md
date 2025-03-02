---
title: Middleware
weight: 11
---

## Default Middleware

For checking against a single permission (see Best Practices) using `can`, you can use the built-in Laravel middleware provided by `\Illuminate\Auth\Middleware\Authorize::class` like this:

```php
Route::group(['middleware' => ['can:publish articles']], function () { ... });

// or with static method (requires Laravel 10.9+)
Route::group(['middleware' => [\Illuminate\Auth\Middleware\Authorize::using('publish articles')]], function () { ... });
```

## Package Middleware

**See a typo? Note that since v6 the _'Middleware'_ namespace is singular. Prior to v6 it was _'Middlewares'_. Time to upgrade your implementation!**

This package comes with `RoleMiddleware`, `PermissionMiddleware` and `RoleOrPermissionMiddleware` middleware.

You can register their aliases for easy reference elsewhere in your app:

In Laravel 11 open `/bootstrap/app.php` and register them there:

```php
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
```

In Laravel 9 and 10 you can add them in `app/Http/Kernel.php`:

```php
// Laravel 9 uses $routeMiddleware = [
//protected $routeMiddleware = [
// Laravel 10+ uses $middlewareAliases = [
protected $middlewareAliases = [
    // ...
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
];
```

### Middleware Priority
If your app is triggering *404 Not Found* responses when a *403 Not Authorized* response might be expected, it might be a middleware priority clash. Explore reordering priorities so that this package's middleware runs before Laravel's `SubstituteBindings` middleware. (See [Middleware docs](https://laravel.com/docs/master/middleware#sorting-middleware) ). 

In Laravel 11 you could explore `$middleware->prependToGroup()` instead. See the Laravel Documentation for details.


## Using Middleware in Routes and Controllers

After you have registered the aliases as shown above, you can use them in your Routes and Controllers much the same way you use any other middleware: 

### Routes

```php
Route::group(['middleware' => ['role:manager']], function () { ... });
Route::group(['middleware' => ['permission:publish articles']], function () { ... });
Route::group(['middleware' => ['role_or_permission:publish articles']], function () { ... });

// for a specific guard:
Route::group(['middleware' => ['role:manager,api']], function () { ... });

// multiple middleware
Route::group(['middleware' => ['role:manager','permission:publish articles']], function () { ... });
```

You can specify multiple roles or permissions with a `|` (pipe) character, which is treated as `OR`:

```php
Route::group(['middleware' => ['role:manager|writer']], function () { ... });
Route::group(['middleware' => ['permission:publish articles|edit articles']], function () { ... });
Route::group(['middleware' => ['role_or_permission:manager|edit articles']], function () { ... });

// for a specific guard
Route::group(['middleware' => ['permission:publish articles|edit articles,api']], function () { ... });
```

### Controllers

In Laravel 11, if your controller implements the `HasMiddleware` interface, you can register [controller middleware](https://laravel.com/docs/11.x/controllers#controller-middleware) using the `middleware()` method:

```php
public static function middleware(): array
{
    return [
        // examples with aliases, pipe-separated names, guards, etc:
        'role_or_permission:manager|edit articles',
        new Middleware('role:author', only: ['index']),
        new Middleware(\Spatie\Permission\Middleware\RoleMiddleware::using('manager'), except:['show']),
        new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('delete records,api'), only:['destroy']),
    ];
}
```

In Laravel 10 and older, you can register it in the constructor:
```php
public function __construct()
{
    // examples:
    $this->middleware(['role:manager','permission:publish articles|edit articles']);
    $this->middleware(['role_or_permission:manager|edit articles']);
    // or with specific guard
    $this->middleware(['role_or_permission:manager|edit articles,api']);
}
```

You can also use Laravel's Model Policy feature in your controller methods. See the Model Policies section of these docs.

## Middleware via Static Methods

All of the middleware can also be applied by calling the static `using` method, which accepts either an array or a `|`-separated string as input.

```php
Route::group(['middleware' => [\Spatie\Permission\Middleware\RoleMiddleware::using('manager')]], function () { ... });
Route::group(['middleware' => [\Spatie\Permission\Middleware\PermissionMiddleware::using('publish articles|edit articles')]], function () { ... });
Route::group(['middleware' => [\Spatie\Permission\Middleware\RoleOrPermissionMiddleware::using(['manager', 'edit articles'])]], function () { ... });
```

