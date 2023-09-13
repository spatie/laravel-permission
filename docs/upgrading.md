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

4. Models. If you have made any custom Models by extending them into your own app, compare the package's old and new models and apply any relevant updates to your custom models.

5. Custom Methods/Traits. If you have overridden any methods from this package's Traits, compare the old and new traits, and apply any relevant updates to your overridden methods.

6. Contract/Interface updates. If you have implemented this package's contracts in any models, check to see if there were any changes to method signatures. Mismatches will trigger PHP errors.

7. Apply any version-specific special updates as outlined below...

8. Review the changelog, which details all the changes: [CHANGELOG](https://github.com/spatie/laravel-permission/blob/main/CHANGELOG.md)
and/or consult the [Release Notes](https://github.com/spatie/laravel-permission/releases)


## Upgrading from v5 to v6
There are a few breaking-changes when upgrading to v6, but most of them won't affect you unless you have been customizing things.

1. If you have overridden the `getPermissionClass()` or `getRoleClass()` methods or have custom Models, you will need to revisit those customizations. See PR #2368 for details. 
eg: if you have a custom model you will need to make changes, including accessing the model using `$this->permissionClass::` syntax (eg: using `::` instead of `->`) in all the overridden methods that make use of the models.

    Be sure to compare your custom models with originals to see what else may have changed.

2. Model and Contract/Interface updates. The Role and Permission Models and Contracts/Interfaces have been updated with syntax changes to method signatures. Update any models you have extended, or contracts implemented, accordingly. See PR #2380 and #2480 for some of the specifics. 

3. Migrations will need to be upgraded. (They have been updated to anonymous-class syntax that was introduced in Laravel 8, AND some structural coding changes in the registrar class changed the way we extracted configuration settings in the migration files.) If you had not customized it from the original then replacing the contents of the file should be straightforward. Usually the only customization is if you've switched to UUIDs or customized MySQL index name lengths. 
**If you get the following error, it means your migration file needs upgrading: `Error: Access to undeclared static property Spatie\Permission\PermissionRegistrar::$pivotPermission`**

4. MIDDLEWARE:

    1. The `\Spatie\Permission\Middlewares\` namespace has been renamed to `\Spatie\Permission\Middleware\` (singular). Update your references to them in your `/app/Http/Kernel.php` and any routes that have the fully qualified path. 

    2. NOTE: For consistency with `PermissionMiddleware`, the `RoleOrPermissionMiddleware` has switched from only checking permissions provided by this package to using `canAny()` to check against any abilities registered by your application. This may have the effect of granting those other abilities (such as Super Admin) when using the `RoleOrPermissionMiddleware`, which previously would have failed silently.

    3. In the unlikely event that you have customized the Wildcard Permissions feature by extending the `WildcardPermission` model, please note that the public interface has changed and you will need to update your extended model with the new method signatures.

5. Test suites. If you have tests which manually clear the permission cache and re-register permissions, you no longer need to call `\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();`. In fact, **calls to `->registerPermissions()` MUST be deleted from your tests**. 
    
    (Calling `app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();` after creating roles and permissions is still okay and encouraged.) 


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
