---
title: Using a Middleware
weight: 11
---

## Default Middleware

For checking against a single permission (see Best Practices) using `can`, you can use the built-in Laravel middleware provided by `\Illuminate\Auth\Middleware\Authorize::class` like this:

```php
Route::group(['middleware' => ['can:publish articles']], function () {
    //
});
```

Since Laravel v10.9, you can also call this middleware with a static method.

```php
Route::group(['middleware' => [\Illuminate\Auth\Middleware\Authorize::using('publish articles')]], function () {
    //
});
```

## Package Middleware

This package comes with `RoleMiddleware`, `PermissionMiddleware` and `RoleOrPermissionMiddleware` middleware.

You can register their aliases for easy reference elsewhere in your app:

>  See a typo? Note that since v6 the 'Middleware' namespace is singular. Prior to v6 it was 'Middlewares'. Time to upgrade your app!

In Laravel 11 open `/bootstrap/app.php` and register it there:

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

In Laravel 11 you can add them in `bootstrap/app.php` under 
`->withMiddleware(function (Middleware $middleware) {`

```php
    $middleware->alias([
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    ]);
```

### Middleware Priority
If your app is triggering *404 Not Found* responses when a *403 Not Authorized* response might be expected, it might be a middleware priority clash. Explore reordering priorities so that this package's middleware runs before Laravel's `SubstituteBindings` middleware. (See [Middleware docs](https://laravel.com/docs/master/middleware#sorting-middleware) ). In Laravel 11 you could explore `$middleware->prependToGroup()` instead.


## Middleware via Routes

Then you can protect your routes using middleware rules:

```php
Route::group(['middleware' => ['role:manager']], function () {
    //
});

// for a specific guard:
Route::group(['middleware' => ['role:manager,api']], function () {
    //
});

Route::group(['middleware' => ['permission:publish articles']], function () {
    //
});

Route::group(['middleware' => ['role:manager','permission:publish articles']], function () {
    //
});

Route::group(['middleware' => ['role_or_permission:publish articles']], function () {
    //
});
```

You can specify multiple roles or permissions with a `|` (pipe) character, which is treated as `OR`:

```php
Route::group(['middleware' => ['role:manager|writer']], function () {
    //
});

Route::group(['middleware' => ['permission:publish articles|edit articles']], function () {
    //
});

// for a specific guard
Route::group(['middleware' => ['permission:publish articles|edit articles,api']], function () {
    //
});

Route::group(['middleware' => ['role_or_permission:manager|edit articles']], function () {
    //
});
```

## Middleware with Controllers

You can protect your controllers similarly, by setting desired middleware in the constructor:

```php
public function __construct()
{
    $this->middleware(['role:manager','permission:publish articles|edit articles']);
}
```

```php
public function __construct()
{
    $this->middleware(['role_or_permission:manager|edit articles']);
}
```

(You can use Laravel's Model Policy feature with your controller methods. See the Model Policies section of these docs.)

## Use middleware static methods

All of the middleware can also be applied by calling the static `using` method,
which accepts either a `|`-separated string or an array as input.

```php
Route::group(['middleware' => [\Spatie\Permission\Middleware\RoleMiddleware::using('manager')]], function () {
    //
});

Route::group(['middleware' => [\Spatie\Permission\Middleware\PermissionMiddleware::using('publish articles|edit articles')]], function () {
    //
});

Route::group(['middleware' => [\Spatie\Permission\Middleware\RoleOrPermissionMiddleware::using(['manager', 'edit articles'])]], function () {
    //
});
```
