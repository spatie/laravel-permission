---
title: Defining a Super-Admin
weight: 5
---

We strongly recommend that a Super-Admin be handled by setting a global `Gate::before` or `Gate::after` rule which checks for the desired role. 

Then you can implement the best-practice of primarily using permission-based controls (@can and $user->can, etc) throughout your app, without always having to check for "is this a super-admin" everywhere. Best not to use role-checking (ie: `hasRole`) when you have Super Admin features like this.


## `Gate::before`
If you want a "Super Admin" role to respond `true` to all permissions, without needing to assign all those permissions to a role, you can use Laravel's `Gate::before()` method. For example:

```php
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPolicies();

        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
```

NOTE: `Gate::before` rules need to return `null` rather than `false`, else it will interfere with normal policy operation. [See more.](https://laracasts.com/discuss/channels/laravel/policy-gets-never-called#reply=492526)


## `Gate::after`

Alternatively you might want to move the Super Admin check to the `Gate::after` phase instead, particularly if your Super Admin shouldn't be allowed to do things your app doesn't want "anyone" to do, such as writing more than 1 review, or bypassing unsubscribe rules, etc.

The following code snippet is inspired from [Freek's blog article](https://murze.be/when-to-use-gateafter-in-laravel) where this topic is discussed further.

```php
// somewhere in a service provider

Gate::after(function ($user, $ability) {
   return $user->hasRole('Super Admin'); // note this returns boolean
});
```
