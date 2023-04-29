---
title: Upgrading
weight: 6
---

## Upgrade Essentials

ALL upgrades of this package should follow these steps:

1. Composer. Upgrading between major versions of this package always require the usual Composer steps:
   - Update your `composer.json` to specify the new major version, such as `^6.0`
   - Then run `composer update`. 

2. Migrations. Compare the `migration` file stubs in the NEW version of this package against the migrations you've already run inside your app. If necessary, create a new migration (by hand) to apply any new database changes.

3. Config file. Incorporate any changes to the permission.php config file, updating your existing file. (It may be easiest to make a backup copy of your existing file, re-publish it from this package, and then re-make your customizations to it.)

3. Models. If you have made any custom Models from this package into your own app, compare the old and new models and apply any relevant updates to your custom models.

4. Custom Methods/Traits. If you have overridden any methods from this package's Traits, compare the old and new traits, and apply any relevant updates to your overridden methods.

5. Apply any version-specific special updates as outlined below...

6. Review the changelog, which details all the changes: [CHANGELOG](https://github.com/spatie/laravel-permission/blob/main/CHANGELOG.md)
and/or consult the [Release Notes](https://github.com/spatie/laravel-permission/releases)


## Upgrading to v6
1. If you have overridden the `getPermissionClass()` or `getRoleClass()` methods or have custom Models, you will need to revisit those customizations. See PR #2368 for details. 
eg: if you have a custom model you will need to make changes, including accessing the model using `$this->permissionClass::` syntax (eg: using `::` instead of `->`) in all the overridden methods that make use of the models.
Be sure to compare your custom models with originals to see what else may have changed.

2. If you have a custom Role model and (in the rare case that you might) have overridden the `hasPermissionTo()` method in it, you will need to update its method signature to `hasPermissionTo($permission, $guardName = null):bool`. See PR #2380.

3. Migrations. Migrations have changed in 2 ways:
  - The migrations have been updated to anonymous-class syntax that was introduced in Laravel 8.
  - Some structural coding changes in the registrar class changed the way we extracted configuration settings in the migration files.
  - THEREFORE: you will need to upgrade your migrations, especially if you get the following error: 

      `Error: Access to undeclared static property Spatie\Permission\PermissionRegistrar::$pivotPermission`


4. NOTE: For consistency with `PermissionMiddleware`, the `RoleOrPermissionMiddleware` has switched from only checking permissions provided by this package to using `canAny()` to check against any abilities registered by your application. This may have the effect of granting those other abilities (such as Super Admin) when using the `RoleOrPermissionMiddleware`, which previously would have failed silently.

5. Test suites. If you have tests which manually clear the permission cache and re-register permissions, you no longer need to call `\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();`. Such calls MUST be deleted from your tests.


## Upgrading from v4 to v5

Follow the instructions described in "Essentials" above.

## Upgrading from v3 to v4

Update `composer.json` as described in "Essentials" above.

## Upgrading from v2 to v3

Update `composer.json` as described in "Essentials" above.


## Upgrading from v1 to v2
There were significant database and code changes between v1 to v2.

If you're upgrading from v1 to v2, there's no built-in automatic migration/conversion of your data to the new structure. 
You will need to carefully adapt your code and your data manually.

Tip: @fabricecw prepared [a gist which may make your data migration easier](https://gist.github.com/fabricecw/58ee93dd4f99e78724d8acbb851658a4). 

You will also need to remove your old `laravel-permission.php` config file and publish the new one `permission.php`, and edit accordingly (setting up your custom settings again in the new file, where relevant).
