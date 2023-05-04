---
title: Using a middleware
weight: 11
---

## Default Middleware

For checking against a single permission (see Best Practices) using `can`, you can use the built-in Laravel middleware provided by `\Illuminate\Auth\Middleware\Authorize::class` like this:

```php
Route::group(['middleware' => ['can:publish articles']], function () {
    //
});
```

In Laravel v10.9 and up, you can also call this middleware with a static method.

```php
Route::group(['middleware' => [\Illuminate\Auth\Middleware\Authorize::using('publish articles')]], function () {
    //
});
```

## Package Middleware

This package comes with `RoleMiddleware`, `PermissionMiddleware` and `RoleOrPermissionMiddleware` middleware.
You can add them inside your `app/Http/Kernel.php` file to be able to use them through aliases.

Note the property name difference between Laravel 10 and older versions of Laravel:

```php
// Laravel 9 uses $routeMiddleware = [
//protected $routeMiddleware = [
// Laravel 10+ uses $middlewareAliases = [
protected $middlewareAliases = [
    // ...
    'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
    'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
];
```

## Middleware via Routes

Then you can protect your routes using middleware rules:

```php
Route::group(['middleware' => ['role:super-admin']], function () {
    //
});

Route::group(['middleware' => ['permission:publish articles']], function () {
    //
});

Route::group(['middleware' => ['role:super-admin','permission:publish articles']], function () {
    //
});

Route::group(['middleware' => ['role_or_permission:publish articles']], function () {
    //
});
```

You can specify multiple roles or permissions with a `|` (pipe) character, which is treated as `OR`:

```php
Route::group(['middleware' => ['role:super-admin|writer']], function () {
    //
});

Route::group(['middleware' => ['permission:publish articles|edit articles']], function () {
    //
});

Route::group(['middleware' => ['role_or_permission:super-admin|edit articles']], function () {
    //
});
```

## Middleware with Controllers

You can protect your controllers similarly, by setting desired middleware in the constructor:

```php
public function __construct()
{
    $this->middleware(['role:super-admin','permission:publish articles|edit articles']);
}
```

```php
public function __construct()
{
    $this->middleware(['role_or_permission:super-admin|edit articles']);
}
```

(You can use Laravel's Model Policy feature with your controller methods. See the Model Policies section of these docs.)

## Use middleware static methods

All of the middlewares can also be applied by calling the static `using` method,
which accepts either a `|`-separated string or an array as input.

```php
Route::group(['middleware' => [\Spatie\Permission\Middlewares\RoleMiddleware::using('super-admin')]], function () {
    //
});

Route::group(['middleware' => [\Spatie\Permission\Middlewares\PermissionMiddleware::using('publish articles|edit articles')]], function () {
    //
});

Route::group(['middleware' => [\Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::using(['super-admin', 'edit articles'])]], function () {
    //
});
```
