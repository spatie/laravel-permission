---
title: Direct Permissions
weight: 2
---

## Best Practice

It's better to assign permissions to Roles, and then assign Roles to Users.

See the [Roles vs Permissions](../best-practices/roles-vs-permissions) section of the docs for a deeper explanation.

HOWEVER, If you have reason to directly assign individual permissions to specific users (instead of to roles assigned to those users), you can do that as described below:

## Direct Permissions to Users

A permission can be given to any user:

```php
$user->givePermissionTo('edit articles');

// You can also give multiple permission at once
$user->givePermissionTo('edit articles', 'delete articles');

// You may also pass an array
$user->givePermissionTo(['edit articles', 'delete articles']);
```

A permission can be revoked from a user:

```php
$user->revokePermissionTo('edit articles');
```

Or revoke & add new permissions in one go:

```php
$user->syncPermissions(['edit articles', 'delete articles']);
```

You can check if a user has a permission:

```php
$user->hasPermissionTo('edit articles');
```

Or you may pass an integer representing the permission id

```php
$user->hasPermissionTo('1');
$user->hasPermissionTo(Permission::find(1)->id);
$user->hasPermissionTo($somePermission->id);
```

You can check if a user has Any of an array of permissions:

```php
$user->hasAnyPermission(['edit articles', 'publish articles', 'unpublish articles']);
```

...or if a user has All of an array of permissions:

```php
$user->hasAllPermissions(['edit articles', 'publish articles', 'unpublish articles']);
```

You may also pass integers to lookup by permission id

```php
$user->hasAnyPermission(['edit articles', 1, 5]);
```

Like all permissions assigned via roles, you can check if a user has a permission by using Laravel's default `can` function:

```php
$user->can('edit articles');
```
