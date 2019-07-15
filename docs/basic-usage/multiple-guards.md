---
title: Using multiple guards
weight: 6
---

When using the default Laravel auth configuration all of the above methods will work out of the box, no extra configuration required.

However, when using multiple guards they will act like namespaces for your permissions and roles. Meaning every guard has its own set of permissions and roles that can be assigned to their user model.

### Using permissions and roles with multiple guards

When creating new permissions and roles, if no guard is specified, then the **first** defined guard in `auth.guards` config array will be used. When creating permissions and roles for specific guards you'll have to specify their `guard_name` on the model:

```php
// Create a superadmin role for the admin users
$role = Role::create(['guard_name' => 'admin', 'name' => 'superadmin']);

// Define a `publish articles` permission for the admin users belonging to the admin guard
$permission = Permission::create(['guard_name' => 'admin', 'name' => 'publish articles']);

// Define a *different* `publish articles` permission for the regular users belonging to the web guard
$permission = Permission::create(['guard_name' => 'web', 'name' => 'publish articles']);
```

To check if a user has permission for a specific guard:

```php
$user->hasPermissionTo('publish articles', 'admin');
```

> **Note**: When determining whether a role/permission is valid on a given model, it chooses the guard in this order: first the `$guard_name` property of the model; then the guard in the config (through a provider); then the first-defined guard in the `auth.guards` config array; then the `auth.defaults.guard` config.

> **Note**: When using other than the default `web` guard, you will need to declare which `guard_name` you wish each model to use by setting the `$guard_name` property in your model. One per model is simplest. 

> **Note**: If your app uses only a single guard, but is not `web` then change the order of your listed guards in your `config/auth.php` to list your primary guard as the default and as the first in the list of defined guards.

### Assigning permissions and roles to guard users

You can use the same methods to assign permissions and roles to users as described above in [using permissions via roles](#using-permissions-via-roles). Just make sure the `guard_name` on the permission or role matches the guard of the user, otherwise a `GuardDoesNotMatch` exception will be thrown.

### Using blade directives with multiple guards

You can use all of the blade directives listed in [using blade directives](#using-blade-directives) by passing in the guard you wish to use as the second argument to the directive:

```php
@role('super-admin', 'admin')
    I am a super-admin!
@else
    I am not a super-admin...
@endrole
```
