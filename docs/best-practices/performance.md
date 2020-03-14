---
title: Performance Tips
weight: 10
---

Often we think in terms of "roles have permissions" so we lookup a Role, and call `$role->givePermissionTo()` 
to indicate what users with that role are allowed to do. This is perfectly fine!

And yet, in some situations, particularly if your app is deleting and adding new permissions frequently,
you may find that things are more performant if you lookup the permission and assign it to the role, like: 
`$permission->assignRole($role)`.
The end result is the same, but sometimes it runs quite a lot faster.

Also, because of the way this package enforces some protections for you, on large databases you may find
that instead of creating permissions with `Permission::create([attributes])` it might be faster to
`$permission = Permission::make([attributes]); $permission->saveOrFail();`

On small apps, most of the above will be moot, and unnecessary.

As always, if you choose to bypass the provided object methods for adding/removing/syncing roles and permissions 
by manipulating Role and Permission objects directly in the database,
you will need to manually reset the cache with the PermissionRegistrar's method for that,
as described in the Cache section of the docs.
