---
title: Upgrading
weight: 6
---

ALL upgrades of this package should follow these steps:

1. Upgrading between major versions of this package always require the usual Composer steps:
   > 1. Update your `composer.json` to specify the new major version, such as `^6.0`
   > 2. Then run `composer update`. 

2. Compare the `migration` file stubs in the NEW version of this package against the migrations you've already run inside your app. If necessary, create a new migration (by hand) to apply any new changes.

3. If you have made any custom Models from this package into your own app, compare the old and new models and apply any relevant updates to your custom models.

4. If you have overridden any methods from this package's Traits, compare the old and new traits, and apply any relevant updates to your overridden methods.

5. Apply any version-specific special updates as outlined below...

6. Review the changelog, which details all the changes: https://github.com/spatie/laravel-permission/blob/main/CHANGELOG.md


### Upgrading to v6
1. If you have overridden the `getPermissionClass()` or `getRoleClass()` methods or have custom Models, you will need to revisit those customizations. See PR #2368 for details. 
eg: if you have a custom model you will need to make changes, including accessing the model using `$this->permissionClass::` syntax (eg: using `::` instead of `->`) in all the overridden methods that make use of the models.
Be sure to compare your custom models with originals to see what else may have changed.

2. If you have a custom Role model and (in the rare case that you might) have overridden the `hasPermissionTo()` method in it, you will need to update its method signature to `hasPermissionTo($permission, $guardName = null):bool`. See PR #2380.

3. Also note that the migrations have been updated to anonymous-class syntax that was introduced in Laravel 8. You may optionally update your original migration files accordingly.

4. NOTE: For consistency with the `PermissionMiddleware`, the `RoleOrPermissionMiddleware` has switched from only checking permissions provided by this package to using `canAny()` to check against any abilities registered by your application. This may have the effect of granting those other abilities (such as Super Admin) when using the `RoleOrPermissionMiddleware`, which previously would have failed silently.

### Upgrading from v1 to v2
If you're upgrading from v1 to v2, there's no built-in automatic migration/conversion of your data to the new structure. 
You will need to carefully adapt your code and your data manually.

Tip: @fabricecw prepared [a gist which may make your data migration easier](https://gist.github.com/fabricecw/58ee93dd4f99e78724d8acbb851658a4). 

You will also need to remove your old `laravel-permission.php` config file and publish the new one `permission.php`, and edit accordingly (setting up your custom settings again in the new file, where relevant).
