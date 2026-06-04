---
title: Middleware
weight: 11
---

## Default Middleware

For checking against a single permission (see Best Practices) using `can`, you can use the built-in Laravel middleware provided by `\Illuminate\Auth\Middleware\Authorize::class` like this:

```php
use Illuminate\Support\Facades\Route;

Route::middleware('can:publish articles')->get(...);

// or with static method
use Illuminate\Auth\Middleware\Authorize;
Route::middleware(Authorize::using('publish articles'))->get(...);
```

## Package Middleware

**See a typo? Note that since v6 the _'Middleware'_ namespace is singular. Prior to v6 it was _'Middlewares'_. Time to upgrade your implementation!**

This package comes with `RoleMiddleware`, `PermissionMiddleware` and `RoleOrPermissionMiddleware` middleware.

You can register their aliases for easy reference elsewhere in your app:

Open `/bootstrap/app.php` and register them there:

```php
// ...
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    // ...
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    // ...
```

### Middleware Priority
If your app is triggering *404 Not Found* responses when a *403 Not Authorized* response might be expected, it might be a middleware priority clash. Explore reordering priorities so that this package's middleware runs before Laravel's `SubstituteBindings` middleware. (See [Middleware docs](https://laravel.com/docs/master/middleware#sorting-middleware) ). 

If needed, you could optionally explore `$middleware->prependToGroup()` instead. See the Laravel Documentation for details.


## Using Middleware in Routes and Controllers

After you have registered the aliases as shown above, you can use them in your Routes and Controllers much the same way you use any other middleware: 

### Routes

```php
// You can apply middleware to a group of routes:
Route::middleware('role:manager')->group(function () {
    // ...
});

// Or, for individual routes, apply the middleware directly:
Route::middleware('role:manager')->get('/admin', ...);
Route::middleware('permission:publish articles')->get('/articles/create', ...);
Route::middleware('role_or_permission:publish articles')->get('/articles/{id}', ...);

// for a specific guard:
Route::middleware('role:manager,api')->get('/api/admin', ...);

// multiple middleware
Route::middleware([
    'role:manager',
    'permission:publish articles'
])->get('/admin/publish', ...);
```

You can specify multiple roles or permissions with a `|` (pipe) character, which is treated as `OR`:

```php
Route::middleware('role:manager|writer')
Route::middleware('permission:publish articles|edit articles')
Route::middleware('role_or_permission:manager|edit articles')

// for a specific guard
Route::middleware('permission:publish articles|edit articles,api')
```

### Controllers

If your controller implements the `HasMiddleware` interface, you can register [controller middleware](https://laravel.com/docs/12.x/controllers#controller-middleware) using the `middleware()` method:

```php 
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class ArticleController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // examples with aliases, pipe-separated names, guards, etc:
            'role_or_permission:manager|edit articles',
            new Middleware('role:author', only: ['index']),
            new Middleware(RoleMiddleware::using('manager'), except:['show']),
            new Middleware(PermissionMiddleware::using('delete records,api'), only:['destroy']),
        ];
    }
}
```

Alternatively, you can use the [middleware attribute](https://laravel.com/docs/13.x/controllers#middleware-attributes) or the [authorization attribute](https://laravel.com/docs/13.x/controllers#authorization-attributes) to apply middleware to your controller classes or methods:

```php
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Routing\Attributes\Controllers\Authorize;

use Spatie\Permission\Middleware\RoleMiddleware;

class ArticleController
{
    #[Middleware(RoleMiddleware::using('manager'), only: ['index'])]
    public function index()
    {
        // ...
    }

    #[Authorize('publish articles')]
    public function store()
    {
        // ...
    }
}
```

You can also use Laravel's Model Policy feature in your controller methods. See the Model Policies section of these docs.

## Middleware via Static Methods

All of the middleware can also be applied by calling the static `using` method, which accepts either an array or a `|`-separated string as input.

```php
use Spatie\Permission\Middleware\RoleMiddleware;
Route::middleware(RoleMiddleware::using('manager'))

use Spatie\Permission\Middleware\PermissionMiddleware;
Route::middleware(
    PermissionMiddleware::using('publish articles|edit articles')
)

use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
Route::middleware(
    RoleOrPermissionMiddleware::using(['manager', 'edit articles'])
)
```
