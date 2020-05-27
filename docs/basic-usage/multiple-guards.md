---
title: Using multiple guards
weight: 6
---

When using the default Laravel auth configuration all of the core methods of this package will work out of the box, no extra configuration required.

However, when using multiple guards they will act like namespaces for your permissions and roles. Meaning every guard has its own set of permissions and roles that can be assigned to their user model.

### The Downside To Multiple Guards

Note that this package requires you to register a permission name for each guard you want to authenticate with. So, "edit-article" would have to be created multiple times for each guard your app uses. An exception will be thrown if you try to authenticate against a non-existing permission+guard combination. Same for roles.

> **Tip**: If your app uses only a single guard, but is not `web` (Laravel's default, which shows "first" in the auth config file) then change the order of your listed guards in your `config/auth.php` to list your primary guard as the default and as the first in the list of defined guards. While you're editing that file, best to remove any guards you don't use, too.


### Using permissions and roles with multiple guards

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
- first the guardName() method if it exists on the model;
- then the `$guard_name` property if it exists on the model;
- then the first-defined guard/provider combination in the `auth.guards` config array that matches the logged-in user's guard;
- then the `auth.defaults.guard` config (which is the user's guard if they are logged in, else the default in the file).


### Assigning permissions and roles to guard users

You can use the same core methods to assign permissions and roles to users; just make sure the `guard_name` on the permission or role matches the guard of the user, otherwise a `GuardDoesNotMatch` or `Role/PermissionDoesNotExist` exception will be thrown.


### Using blade directives with multiple guards

You can use all of the blade directives offered by this package by passing in the guard you wish to use as the second argument to the directive:

```php
@role('super-admin', 'admin')
    I am a super-admin!
@else
    I am not a super-admin...
@endrole
```
