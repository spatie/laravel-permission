---
title: Basic Usage
weight: 1
---

First, add the `Spatie\Permission\Traits\HasRoles` trait to your `User` model(s):

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    // ...
}
```

This package allows for users to be associated with permissions and roles. Every role is associated with multiple permissions.
A `Role` and a `Permission` are regular Eloquent models. They require a `name` and can be created like this:

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$role = Role::create(['name' => 'writer']);
$permission = Permission::create(['name' => 'edit articles']);
```


A permission can be assigned to a role using 1 of these methods:

```php
$role->givePermissionTo($permission);
$permission->assignRole($role);
```

Multiple permissions can be synced to a role using 1 of these methods:

```php
$role->syncPermissions($permissions);
$permission->syncRoles($roles);
```

A permission can be removed from a role using 1 of these methods:

```php
$role->revokePermissionTo($permission);
$permission->removeRole($role);
```

If you're using multiple guards the `guard_name` attribute needs to be set as well. Read about it in the [using multiple guards](../multiple-guards) section of the readme.

The `HasRoles` trait adds Eloquent relationships to your models, which can be accessed directly or used as a base query:

```php
// get a list of all permissions directly assigned to the user
$permissionNames = $user->getPermissionNames(); // collection of name strings
$permissions = $user->permissions; // collection of permission objects

// get all permissions for the user, either directly, or from roles, or from both
$permissions = $user->getDirectPermissions();
$permissions = $user->getPermissionsViaRoles();
$permissions = $user->getAllPermissions();

// get the names of the user's roles
$roles = $user->getRoleNames(); // Returns a collection
```

The `HasRoles` trait also adds the following scopes to your models to scope the query by roles or permissions. 

The `role` scope is used to only get users with a certain role.

```php
// get a list of users with the 'writer' role
$users = User::role('writer')->get(); 

// get a list of users with the 'writer' or 'editor' role
$users = User::role(['editor', 'writer'])->get();
```

The `role` scope has an inverse `withoutRole` scope to only get users without a certain role.

```php
// get a list of users without the 'writer' role
$users = User::withoutRole('writer')->get();

// get a list of users without the 'writer' or 'editor' role.  
$users = User::withoutRole(['writer', 'editor'])->get();
```

The same trait also adds a `permission` scope to only get users with a certain permission.

```php
// get a list of users with the 'edit articles' permission. (inherited or directly)
$users = User::permission('edit articles')->get(); 

// get a list of users with the 'edit articles' or 'delete articles' permission. (inherited or directly)
$users = User::permission(['edit articles', 'delete articles'])->get(); 
```

The `permission` scope also has an inverse `withoutPermission` scope to only get users without a certain permission.

```php
// get a list of users without the 'edit articles' permission
$users = User::withoutPermission('edit articles')->get();

// get a list of users without the 'edit articles' or 'delete articles' permission
$users = User::withoutPermission(['edit articles', 'delete articles'])->get();
```

These scopes can all accept a string, a `\Spatie\Permission\Models\Permission` object or an `\Illuminate\Support\Collection` object.

### Eloquent
Since Role and Permission models are extended from Eloquent models, basic Eloquent calls can be used as well:

```php
$all_users_with_all_their_roles = User::with('roles')->get();
$all_users_with_all_direct_permissions = User::with('permissions')->get();
$all_roles_in_database = Role::all()->pluck('name');
$users_without_any_roles = User::doesntHave('roles')->get();
```

