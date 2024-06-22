---
title: Using multiple guards
weight: 9
---

When using the default Laravel auth configuration all of the core methods of this package will work out of the box, with no extra configuration required.

However, when using multiple guards they will act like namespaces for your permissions and roles: Every guard has its own set of permissions and roles that can be assigned to its user model.

## The Downside To Multiple Guards

Note that this package requires you to register a permission name (same for roles) for each guard you want to authenticate with. So, "edit-article" would have to be created multiple times for each guard your app uses. An exception will be thrown if you try to authenticate against a non-existing permission+guard combination. Same for roles.

> **Tip**: If your app uses only a single guard, but it is not `web` (Laravel's default, which shows "first" in the auth config file) then change the order of your listed guards in your `config/auth.php` to list your primary guard as the default and as the first in the list of defined guards. While you're editing that file, it is best to remove any guards you don't use, too.
> 
> OR you could use the suggestion below to force the use of a single guard:

## Forcing Use Of A Single Guard

If your app structure does NOT differentiate between guards when it comes to roles/permissions, (ie: if ALL your roles/permissions are the SAME for ALL guards), you can override the `getDefaultGuardName` function by adding it to your User model, and specifying your desired `$guard_name`. Then you only need to create roles/permissions for that single `$guard_name`, not duplicating them. The example here sets it to `web`, but use whatever your application's default is:

```php
    protected function getDefaultGuardName(): string { return 'web'; }
````


## Using permissions and roles with multiple guards

When creating new permissions and roles, if no guard is specified, then the **first** defined guard in `auth.guards` config array will be used. 

```php
// Create a manager role for users authenticating with the admin guard:
$role = Role::create(['guard_name' => 'admin', 'name' => 'manager']);

// Define a `publish articles` permission for the admin users belonging to the admin guard
$permission = Permission::create(['guard_name' => 'admin', 'name' => 'publish articles']);

// Define a *different* `publish articles` permission for the regular users belonging to the web guard
$permission = Permission::create(['guard_name' => 'web', 'name' => 'publish articles']);
```

To check if a user has permission for a specific guard:

```php
$user->hasPermissionTo('publish articles', 'admin');
```

> **Note**: When determining whether a role/permission is valid on a given model, it checks against the first matching guard in this order (it does NOT check role/permission for EACH possibility, just the first match):
- first the guardName() method if it exists on the model (may return a string or array);
- then the `$guard_name` property if it exists on the model (may return a string or array);
- then the first-defined guard/provider combination in the `auth.guards` config array that matches the loaded model's guard;
- then the `auth.defaults.guard` config (which is the user's guard if they are logged in, else the default in the file).


## Assigning permissions and roles to guard users

You can use the same core methods to assign permissions and roles to users; just make sure the `guard_name` on the permission or role matches the guard of the user, otherwise a `GuardDoesNotMatch` or `Role/PermissionDoesNotExist` exception will be thrown. 

If your User is able to consume multiple roles or permissions from different guards; make sure the User class's `$guard_name` property or `guardName()` method returns all allowed guards as an array:

```php
    protected $guard_name = ['web', 'admin'];
````
or
```php
    public function guardName() { return ['web', 'admin']; }
````

## Using blade directives with multiple guards

You can use all of the blade directives offered by this package by passing in the guard you wish to use as the second argument to the directive:

```php
@role('super-admin', 'admin')
    I am a super-admin!
@else
    I am not a super-admin...
@endrole
```
