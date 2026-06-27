---
title: Introduction
weight: 1
---

This package allows you to manage user permissions and roles in a database.

Think of a `Permission` as a specific ability or capability your app checks for, such as `edit articles`. Think of a `Role` as a named group of permissions that can be assigned to users or other models.

Once installed you can do stuff like this:

```php
// Adding permissions to a user
$user->givePermissionTo('edit articles');

// Adding permissions via a role
$user->assignRole('writer');

$role->givePermissionTo('edit articles');
```

If you're using multiple guards we've got you covered as well. Every guard will have its own set of permissions and roles that can be assigned to the guard's users. Read about it in the [using multiple guards](./basic-usage/multiple-guards/) section.

Because all permissions will be registered on [Laravel's gate](https://laravel.com/docs/authorization), you can check if a user has a permission with Laravel's default `can` function:

```php
$user->can('edit articles');
```

and Blade directives:

```blade
@can('edit articles')
...
@endcan
```
