---
title: Using Permissions via Roles
weight: 3
---

## Assigning Roles

A role can be assigned to any user:

```php
$user->assignRole('writer');

// You can also assign multiple roles at once
$user->assignRole('writer', 'admin');
// or as an array
$user->assignRole(['writer', 'admin']);
```

A role can be removed from a user:

```php
$user->removeRole('writer');
```

Roles can also be synced:

```php
// All current roles will be removed from the user and replaced by the array given
$user->syncRoles(['writer', 'admin']);
```

## Checking Roles

You can determine if a user has a certain role:

```php
$user->hasRole('writer');

// or at least one role from an array of roles:
$user->hasRole(['editor', 'moderator']);
```

You can also determine if a user has any of a given list of roles:

```php
$user->hasAnyRole(['writer', 'reader']);
// or
$user->hasAnyRole('writer', 'reader');
```

You can also determine if a user has all of a given list of roles:

```php
$user->hasAllRoles(Role::all());
```

You can also determine if a user has exactly all of a given list of roles:

```php
$user->hasExactRoles(Role::all());
```

The `assignRole`, `hasRole`, `hasAnyRole`, `hasAllRoles`, `hasExactRoles`  and `removeRole` functions can accept a
 string, a `\Spatie\Permission\Models\Role` object or an `\Illuminate\Support\Collection` object.


## Assigning Permissions to Roles

A permission can be given to a role:

```php
$role->givePermissionTo('edit articles');
```

You can determine if a role has a certain permission:

```php
$role->hasPermissionTo('edit articles');
```

A permission can be revoked from a role:

```php
$role->revokePermissionTo('edit articles');
```

The `givePermissionTo` and `revokePermissionTo` functions can accept a
string or a `Spatie\Permission\Models\Permission` object.


**NOTE: Permissions are inherited from roles automatically.**


### What Permissions Does A Role Have?

The `permissions` property on any given role returns a collection with all the related permission objects. This collection can respond to usual Eloquent Collection operations, such as count, sort, etc.

```php
// get collection
$role->permissions;

// return only the permission names:
$role->permissions->pluck('name');

// count the number of permissions assigned to a role
count($role->permissions);
// or
$role->permissions->count();
```

## Assigning Direct Permissions To A User

Additionally, individual permissions can be assigned to the user too. 
For instance:

```php
$role = Role::findByName('writer');
$role->givePermissionTo('edit articles');

$user->assignRole('writer');

$user->givePermissionTo('delete articles');
```

In the above example, a role is given permission to edit articles and this role is assigned to a user. 
Now the user can edit articles and additionally delete articles. The permission of 'delete articles' is the user's direct permission because it is assigned directly to them.
When we call `$user->hasDirectPermission('delete articles')` it returns `true`, 
but `false` for `$user->hasDirectPermission('edit articles')`.

This method is useful if one builds a form for setting permissions for roles and users in an application and wants to restrict or change inherited permissions of roles of the user, i.e. allowing to change only direct permissions of the user.


You can check if the user has a Specific or All or Any of a set of permissions directly assigned:

```php
// Check if the user has Direct permission
$user->hasDirectPermission('edit articles')

// Check if the user has All direct permissions
$user->hasAllDirectPermissions(['edit articles', 'delete articles']);

// Check if the user has Any permission directly
$user->hasAnyDirectPermission(['create articles', 'delete articles']);
```
By following the previous example, when we call `$user->hasAllDirectPermissions(['edit articles', 'delete articles'])` 
it returns `true`, because the user has all these direct permissions. 
When we call
`$user->hasAnyDirectPermission('edit articles')`, it returns `true` because the user has one of the provided permissions.


You can examine all of these permissions:

```php
// Direct permissions
$user->getDirectPermissions() // Or $user->permissions;

// Permissions inherited from the user's roles
$user->getPermissionsViaRoles();

// All permissions which apply on the user (inherited and direct)
$user->getAllPermissions();
```

All these responses are collections of `Spatie\Permission\Models\Permission` objects.

If we follow the previous example, the first response will be a collection with the `delete article` permission and 
the second will be a collection with the `edit article` permission and the third will contain both.



### NOTE about using permission names in policies

When calling `authorize()` for a policy method, if you have a permission named the same as one of those policy methods, your permission "name" will take precedence and not fire the policy. For this reason it may be wise to avoid naming your permissions the same as the methods in your policy. While you can define your own method names, you can read more about the defaults Laravel offers in Laravel's documentation at https://laravel.com/docs/authorization#writing-policies
