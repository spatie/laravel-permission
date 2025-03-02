---
title: Defining a Super-Admin
weight: 8
---

We strongly recommend that a Super-Admin be handled by setting a global `Gate::before` or `Gate::after` rule which checks for the desired role. 

Then you can implement the best-practice of primarily using permission-based controls (@can and $user->can, etc) throughout your app, without always having to check for "is this a super-admin" everywhere. **Best not to use role-checking (ie: `hasRole`) (except here in Gate/Policy rules) when you have Super Admin features like this.**

## Gate::before/Policy::before vs HasPermissionTo / HasAnyPermission / HasDirectPermission / HasAllPermissions
IMPORTANT:
The Gate::before is the best approach for Super-Admin functionality, and aligns well with the described "Best Practices" of using roles as a way of grouping permissions, and assigning that access to Users. Using this approach, you can/must call Laravel's standard `can()`, `canAny()`, `cannot()`, etc checks for permission authorization to get a correct Super response. 

### HasPermissionTo, HasAllPermissions, HasAnyPermission, HasDirectPermission
Calls to this package's internal API which bypass Laravel's Gate (such as a direct call to `->hasPermissionTo()`) will not go through the Gate, and thus will not get the Super response, unless you have actually added that specific permission to the Super-Admin "role".

The only reason for giving specific permissions to a Super-Admin role is if you intend to call the `has` methods directly instead of the Gate's `can()` methods.


## `Gate::before`
If you want a "Super Admin" role to respond `true` to all permissions, without needing to assign all those permissions to a role, you can use [Laravel's `Gate::before()` method](https://laravel.com/docs/master/authorization#intercepting-gate-checks). For example:

In Laravel 11 this would go in the `boot()` method of `AppServiceProvider`:
In Laravel 10 and below it would go in the `boot()` method of `AuthServiceProvider.php`:
```php
use Illuminate\Support\Facades\Gate;
// ...
public function boot()
{
    // Implicitly grant "Super Admin" role all permissions
    // This works in the app by using gate-related functions like auth()->user->can() and @can()
    Gate::before(function ($user, $ability) {
        return $user->hasRole('Super Admin') ? true : null;
    });
}
```

NOTE: `Gate::before` rules need to return `null` rather than `false`, else it will interfere with normal policy operation. [See more.](https://laracasts.com/discuss/channels/laravel/policy-gets-never-called#reply=492526)

Jeffrey Way explains the concept of a super-admin (and a model owner, and model policies) in the [Laravel 6 Authorization Filters](https://laracasts.com/series/laravel-6-from-scratch/episodes/51) video and some related lessons in that chapter.

## Policy `before()`

If you aren't using `Gate::before()` as described above, you could alternatively grant super-admin control by checking the role in individual Policy classes, using the `before()` method.

Here is an example from the [Laravel Documentation on Policy Filters](https://laravel.com/docs/master/authorization#policy-filters), where you can define `before()` in your Policy where needed:

```php
use App\Models\User; // could be any Authorizable model

/**
 * Perform pre-authorization checks on the model.
 */
public function before(User $user, string $ability): ?bool
{
    if ($user->hasRole('Super Admin')) {
        return true;
    }
 
    return null; // see the note above in Gate::before about why null must be returned here.
}
```

## `Gate::after`

Alternatively you might want to move the Super Admin check to the `Gate::after` phase instead, particularly if your Super Admin shouldn't be allowed to do things your app doesn't want "anyone" to do, such as writing more than 1 review, or bypassing unsubscribe rules, etc.

The following code snippet is inspired from [Freek's blog article](https://freek.dev/1325-when-to-use-gateafter-in-laravel) where this topic is discussed further. You can also consult the [Laravel Docs on gate interceptions](https://laravel.com/docs/master/authorization#intercepting-gate-checks)

```php
// somewhere in a service provider

Gate::after(function ($user, $ability) {
   return $user->hasRole('Super Admin'); // note this returns boolean
});
```
