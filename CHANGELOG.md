# Changelog

All notable changes to `laravel-permission` will be documented in this file

## 5.4.0 - 2021-11-17

## What's Changed

- Add support for PHP 8.1 by @freekmurze in https://github.com/spatie/laravel-permission/pull/1926

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.3.2...5.4.0

## 5.3.2 - 2021-11-17

## What's Changed

- [V5] Support for custom key names on Role,Permission by @erikn69 in https://github.com/spatie/laravel-permission/pull/1913

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.3.1...5.3.2

## 5.3.1 - 2021-11-04

- Fix hints, support int on scopePermission (#1908)

## 5.3.0 - 2021-10-29

- Option for custom logic for checking permissions (#1891)

## 5.2.0 - 2021-10-28

- [V5] Fix detaching on all teams intstead of only current #1888 by @erikn69 in https://github.com/spatie/laravel-permission/pull/1890
- [V5] Add uuid compatibility support on teams by @erikn69 in https://github.com/spatie/laravel-permission/pull/1857
- Adds setRoleClass method to PermissionRegistrar by @timschwartz in https://github.com/spatie/laravel-permission/pull/1867
- Load permissions for preventLazyLoading by @bahramsadin in https://github.com/spatie/laravel-permission/pull/1884
- [V5] Doc for `Super Admin` on teams by @erikn69 in https://github.com/spatie/laravel-permission/pull/1845

## 5.1.1 - 2021-09-01

- Avoid Roles over-hydration #1834

## 5.1.0 - 2021-08-31

- No longer flush cache on User role/perm assignment changes #1832
- NOTE:  You should test your app to be sure that you don't accidentally have deep dependencies on cache resets happening automatically in these cases.
- ALSO NOTE: If you have added custom code which depended on these flush operations, you may need to add your own cache-reset calls.

## 5.0.0 - 2021-08-31

- Change default-guard-lookup to prefer current user's guard (see BC note in #1817 )
- Teams/Groups feature (see docs, or PR #1804)
- Customized pivots instead of `role_id`,`permission_id` #1823

## 4.4.1 - 2021-09-01

- Avoid Roles over-hydration #1834

## 4.4.0 - 2021-08-28

- Avoid BC break (removed interface change) on cache change added in 4.3.0 #1826
- Made cache even smaller #1826
- Avoid re-sync on non-persisted objects when firing Eloquent::saved #1819

## 4.3.0 - 2021-08-17

- Speed up permissions cache lookups, and make cache smaller #1799

## 4.2.0 - 2021-06-04

- Add hasExactRoles method #1696

## 4.1.0 - 2021-06-01

- Refactor to resolve guard only once during middleware
- Refactor service provider by extracting some methods

## 4.0.1 - 2021-03-22

- Added note in migration for field lengths on MySQL 8. (either shorten the columns to 125 or use InnoDB)

## 4.0.0 - 2021-01-27

- Drop support on Laravel 5.8 #1615
- Fix bug when adding roles to a model that doesn't yet exist #1663
- Enforce unique constraints on database level #1261
- Changed PermissionRegistrar::initializeCache() public to allow reinitializing cache in custom situations. #1521
- Use Eloquent\Collection instead of Support\Collection for consistency, collection merging, etc #1630

This package now requires PHP 7.2.5 and Laravel 6.0 or higher.
If you are on a PHP version below 7.2.5 or a Laravel version below 6.0 you can use an older version of this package.

## 3.18.0 - 2020-11-27

- Allow PHP 8.0

## 3.17.0 - 2020-09-16

- Optional `$guard` parameter may be passed to `RoleMiddleware`, `PermissionMiddleware`, and `RoleOrPermissionMiddleware`. See #1565

## 3.16.0 - 2020-08-18

- Added Laravel 8 support

## 3.15.0 - 2020-08-15

- Change `users` relationship type to BelongsToMany

## 3.14.0 - 2020-08-15

- Declare table relations earlier to improve guarded/fillable detection accuracy (relates to Aug 2020 Laravel security patch)

## 3.13.0 - 2020-05-19

- Provide migration error text to stop caching local config when installing packages.

## 3.12.0 - 2020-05-14

- Add missing config setting for `display_role_in_exception`
- Ensure artisan `permission:show` command uses configured models

## 3.11.0 - 2020-03-03

- Allow guardName() as a function with priority over $guard_name property #1395

## 3.10.1 - 2020-03-03

- Update patch to handle intermittent error in #1370

## 3.10.0 - 2020-03-02

- Ugly patch to handle intermittent error: `Trying to access array offset on value of type null` in #1370

## 3.9.0 - 2020-02-26

- Add Wildcard Permissions feature #1381 (see PR or docs for details)

## 3.8.0 - 2020-02-18

- Clear in-memory permissions on boot, for benefit of long running processes like Swoole. #1378

## 3.7.2 - 2020-02-17

- Refine test for Lumen dependency. Ref #1371, Fixes #1372.

## 3.7.1 - 2020-02-15

- Internal refactoring of scopes to use whereIn instead of orWhere #1334, #1335
- Internal refactoring to flatten collection on splat #1341

## 3.7.0 - 2020-02-15

- Added methods to check any/all when querying direct permissions #1245
- Removed older Lumen dependencies #1371

## 3.6.0 - 2020-01-17

- Added Laravel 7.0 support
- Allow splat operator for passing roles to `hasAnyRole()`

## 3.5.0 - 2020-01-07

- Added missing `guardName` to Exception `PermissionDoesNotExist` #1316

## 3.4.1 - 2019-12-28

- Fix 3.4.0 for Lumen

## 3.4.0 - 2019-12-27

- Make compatible with Swoole - ie: for long-running Laravel instances

## 3.3.1 - 2019-12-24

- Expose Artisan commands to app layer, not just to console

## 3.3.0 - 2019-11-22

- Remove duplicate and unreachable code
- Remove checks for older Laravel versions

## 3.2.0 - 2019-10-16

- Implementation of optional guard check for hasRoles and hasAllRoles - See #1236

## 3.1.0 - 2019-10-16

- Use bigIncrements/bigInteger in migration - See #1224

## 3.0.0 - 2019-09-02

- Update dependencies to allow for Laravel 6.0
- Drop support for Laravel 5.7 and older, and PHP 7.1 and older. (They can use v2 of this package until they upgrade.)
- To be clear: v3 requires minimum Laravel 5.8 and PHP 7.2

## 2.38.0 - 2019-09-02

- Allow support for multiple role/permission models
- Load roles relationship only when missing
- Wrap helpers in function_exists() check

## 2.37.0 - 2019-04-09

- Added `permission:show` CLI command to display a table of roles/permissions
- `removeRole` now returns the model, consistent with other methods
- model `$guarded` properties updated to `protected`
- README updates

## 2.36.1 - 2019-03-05

- reverts the changes made in 2.36.0 due to some reported breaks.

## 2.36.0 - 2019-03-04

- improve performance by reducing another iteration in processing query results and returning earlier

## 2.35.0 - 2019-03-01

- overhaul internal caching strategy for better performance and fix cache miss when permission names contained spaces
- deprecated hasUncachedPermissionTo() (use hasPermissionTo() instead)
- added getPermissionNames() method

## 2.34.0 - 2019-02-26

- Add explicit pivotKeys to roles/permissions BelongsToMany relationships

## 2.33.0 - 2019-02-20

- Laravel 5.8 compatibility

## 2.32.0 - 2019-02-13

- Fix duplicate permissions being created through artisan command

## 2.31.0 - 2019-02-03

- Add custom guard query to role scope
- Remove use of array_wrap helper function due to future deprecation

## 2.30.0 - 2019-01-28

- Change cache config time to DateInterval instead of integer

This is in preparation for compatibility with Laravel 5.8's cache TTL change to seconds instead of minutes.

NOTE: If you leave your existing `config/permission.php` file alone, then with Laravel 5.8 the `60 * 24` will change from being treated as 24 hours to just 24 minutes. Depending on your app, this may or may not make a significant difference.  Updating your config file to a specific DateInterval will add specificity and insulate you from the TTL change in Laravel 5.8.

Refs:

https://laravel-news.com/cache-ttl-change-coming-to-laravel-5-8
https://github.com/laravel/framework/commit/fd6eb89b62ec09df1ffbee164831a827e83fa61d

## 2.29.0 - 2018-12-15

- Fix bound `saved` event from firing on all subsequent models when calling assignRole or givePermissionTo on unsaved models. However, it is preferable to save the model first, and then add roles/permissions after saving. See #971.

## 2.28.2 - 2018-12-10

- Use config settings for cache reset in migration stub

## 2.28.1 - 2018-12-07

- Remove use of Cache facade, for Lumen compatibility

## 2.28.0 - 2018-11-30

- Rename `getCacheKey` method in HasPermissions trait to `getPermissionCacheKey` for clearer specificity.

## 2.27.0 - 2018-11-21

- Add ability to specify a cache driver for roles/permissions caching

## 2.26.2 - 2018-11-20

- Added the ability to reset the permissions cache via an Artisan command:
- `php artisan permission:cache-reset`

## 2.26.1 - 2018-11-19

- minor update to de-duplicate code overhead
- numerous internal updates to cache tests infrastructure

## 2.26.0 - 2018-11-19

- Substantial speed increase by caching the associations between models and permissions

### NOTES:

The following changes are not "breaking", but worth making the updates to your app for consistency.

1. Config file: The `config/permission.php` file changed to move cache-related settings into a sub-array. **You should review the changes and merge the updates into your own config file.** Specifically the `expiration_time` value has moved into a sub-array entry, and the old top-level entry is no longer used.
2. See the original config file here:
3. https://github.com/spatie/laravel-permission/blob/main/config/permission.php
4. 
5. Cache Resets: If your `app` or `tests` are clearing the cache by specifying the cache key, **it is better to use the built-in forgetCachedPermissions() method** so that it properly handles tagged cache entries. Here is the recommended change:
6. 

```diff
- app()['cache']->forget('spatie.permission.cache');
+ $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();


```
1. Also this is a good time to point out that now with v2.25.0 and v2.26.0 most permission-cache-reset scenarios may no longer be needed in your app, so it's worth reviewing those cases, as you may gain some app speed improvement by removing unnecessary cache resets.

## 2.25.0 - 2018-11-07

- A model's `roles` and `permissions` relations (respectively) are now automatically reloaded after an Assign/Remove role or Grant/Revoke of permissions. This means there's no longer a need to call `-&amp;gt;fresh()` on the model if the only reason is to reload the role/permission relations. (That said, you may want to call it for other reasons.)
- Added support for passing id to HasRole()

## 2.24.0 - 2018-11-06

- Fix operator used on RoleOrPermissionMiddleware, and avoid throwing PermissionDoesNotExist if invalid permission passed
- Auto-reload model role relation after using AssignRole
- Avoid empty permission creation when using the CreateRole command

## 2.23.0 - 2018-10-15

- Avoid unnecessary queries of user roles when fetching all permissions

## 2.22.1 - 2018-10-15

- Fix Lumen issue with Route helper added in 2.22.0

## 2.22.0 - 2018-10-11

- Added `Route::role()` and `Route::permission()` middleware helper functions
- Added new `role_or_permission` middleware to allow specifying "or" combinations

## 2.21.0 - 2018-09-29

- Revert changes from 2.17.1 in order to support Lumen 5.7

## 2.20.0 - 2018-09-19

- It will sync roles/permissions to models that are not persisted, by registering a `saved` callback.
- (It would previously throw an Integrity constraint violation QueryException on the pivot table insertion.)

## 2.19.2 - 2018-09-19

- add `@elserole` directive:
- Usage:

```php
@role('roleA')
 // user hasRole 'roleA'
@elserole('roleB')
 // user hasRole 'roleB' but not 'roleA'
@endrole


```
## 2.19.1 - 2018-09-14

- Spark-related fix to accommodate missing guard[providers] config

## 2.19.0 - 2018-09-10

- Add ability to pass in IDs or mixed values to `role` scope
- Add `@unlessrole`/`@endunlessrole` Blade directives

## 2.18.0 - 2018-09-06

- Expanded CLI `permission:create-role` command to create optionally create-and-link permissions in one command. Also now no longer throws an error if the role already exists.

## 2.17.1 - 2018-08-28

- Require laravel/framework instead of illuminate/* starting from ~5.4.0
- Removed old dependency for illuminate/database@~5.3.0 (Laravel 5.3 is not supported)

## 2.17.0 - 2018-08-24

- Laravel 5.7 compatibility

## 2.16.0 - 2018-08-20

- Replace static Permission::class and Role::class with dynamic value (allows custom models more easily)
- Added type checking in hasPermissionTo and hasDirectPermission

## 2.15.0 - 2018-08-15

- Make assigning the same role or permission twice not throw an exception

## 2.14.0 - 2018-08-13

- Allow using another key name than `model_id` by defining new `columns` array with `model_morph_key` key in config file. This improves UUID compatibility as discussed in #777.

## 2.13.0 - 2018-08-02

- Fix issue with null values passed to syncPermissions & syncRoles

## 2.12.2 - 2018-06-13

- added hasAllPermissions method

## 2.12.1 - 2018-04-23

- Reverted 2.12.0. REVERTS: "Add ability to pass guard name to gate methods like can()". Requires reworking of guard handling if we're going to add this feature.

## 2.12.0 - 2018-04-22

- Add ability to pass guard name to gate methods like can()

## 2.11.0 - 2018-04-16

- Improve speed of permission lookups with findByName, findById, findOrCreate

## 2.10.0 - 2018-04-15

- changes the type-hinted Authenticatable to Authorizable in the PermissionRegistrar.
- (Previously it was expecting models to implement the Authenticatable contract; but really that should have been Authorizable, since that's where the Gate functionality really is.)

## 2.9.2 - 2018-03-12

- Now findOrCreate() exists for both Roles and Permissions
- Internal code refactoring for future dev work

## 2.9.1 - 2018-02-23

- Permissions now support passing integer id for sync, find, hasPermissionTo and hasDirectPermissionTo

## 2.9.0 - 2018-02-07

- add compatibility with Laravel 5.6
- Allow assign/sync/remove Roles from Permission model

## 2.8.2 - 2018-02-07

- Allow a collection containing a model to be passed to role/permission scopes

## 2.8.1 - 2018-02-03

- Fix compatibility with Spark v2.0 to v5.0

## 2.8.0 - 2018-01-25

- Support getting guard_name from extended model when using static methods

## 2.7.9 - 2018-01-23

Changes related to throwing UnauthorizedException:

- When UnauthorizedException is thrown, a property is added with the expected role/permission which triggered it
- A configuration option may be set to include the list of required roles/permissions in the message

## 2.7.8 - 2018-01-02

- REVERTED: Dynamic permission_id and role_id columns according to tables name
- NOTE: This Dynamic field naming was a breaking change, so we've removed it for now.

BEST NOT TO USE v2.7.7 if you've changed tablenames in the config file.

## 2.7.7 - 2017-12-31

- updated `HasPermissions::getStoredPermission` to allow a collection to be returned, and to fix query when passing multiple permissions
- Give and revoke multiple permissions
- Dynamic permission_id and role_id columns according to tables name
- Add findOrCreate function to Permission model
- Improved Lumen support
- Allow guard name to be null for find role by id

## 2.7.6 - 2017-11-27

- added Lumen support
- updated `HasRole::assignRole` and `HasRole::syncRoles` to accept role id's in addition to role names as arguments

## 2.7.5 - 2017-10-26

- fixed `Gate::before` for custom gate callbacks

## 2.7.4 - 2017-10-26

- added cache clearing command in `up` migration for permission tables
- use config_path helper for better Lumen support

## 2.7.3 - 2017-10-21

- refactor middleware to throw custom `UnauthorizedException` (which raises an HttpException with 403 response)
- The 403 response is backward compatible

## 2.7.2 - 2017-10-18

- refactor `PermissionRegistrar` to use `$gate-&amp;gt;before()`
- removed `log_registration_exception` as it is no longer relevant

## 2.7.1 - 2017-10-12

- fixed a bug where `Role`s and `Permission`s got detached when soft deleting a model

## 2.7.0 - 2017-09-27

- add support for L5.3

## 2.6.0 - 2017-09-10

- add `permission` scope

## 2.5.4 - 2017-09-07

- register the blade directives in the register method of the service provider

## 2.5.3 - 2017-09-07

- register the blade directives in the boot method of the service provider

## 2.5.2 - 2017-09-05

- let middleware use caching

## 2.5.1 - 2017-09-02

- add getRoleNames() method to return a collection of assigned roles

## 2.5.0 - 2017-08-30

- add compatibility with Laravel 5.5

## 2.4.2 - 2017-08-11

- automatically detach roles and permissions when a user gets deleted

## 2.4.1 - 2017-08-05

- fix processing of pipe symbols in `@hasanyrole` and `@hasallroles` Blade directives

## 2.4.0 -2017-08-05

- add `PermissionMiddleware` and `RoleMiddleware`

## 2.3.2 - 2017-07-28

- allow `hasAnyPermission` to take an array of permissions

## 2.3.1 - 2017-07-27

- fix commands not using custom models

## 2.3.0 - 2017-07-25

- add `create-permission` and `create-role` commands

## 2.2.0 - 2017-07-01

- `hasanyrole` and `hasallrole` can accept multiple roles

## 2.1.6 - 2017-06-06

- fixed a bug where `hasPermissionTo` wouldn't use the right guard name

## 2.1.5 - 2017-05-17

- fixed a bug that didn't allow you to assign a role or permission when using multiple guards

## 2.1.4 - 2017-05-10

- add `model_type` to the primary key of tables that use a polymorphic relationship

## 2.1.3 - 2017-04-21

- fixed a bug where the role()/permission() relation to user models would be saved incorrectly
- added users() relation on Permission and Role

## 2.1.2 - 2017-04-20

- fix a bug where the `role()`/`permission()` relation to user models would be saved incorrectly
- add `users()` relation on `Permission` and `Role`

## 2.0.2 - 2017-04-13

- check for duplicates when adding new roles and permissions

## 2.0.1 - 2017-04-11

- fix the order of the `foreignKey` and `relatedKey` in the relations

## 2.0.0 - 2017-04-10

- Requires minimum Laravel 5.4
- cache expiration is now configurable and set to one day by default
- roles and permissions can now be assigned to any model through the `HasRoles` trait
- removed deprecated `hasPermission` method
- renamed config file from `laravel-permission` to `permission`.

## 1.17.0 - 2018-08-24

- added support for Laravel 5.7

## 1.16.0 - 2018-02-07

- added support for Laravel 5.6

## 1.15 - 2017-12-08

- allow `hasAnyPermission` to take an array of permissions

## 1.14.1 - 2017-10-26

- fixed `Gate::before` for custom gate callbacks

## 1.14.0 - 2017-10-18

- refactor `PermissionRegistrar` to use `$gate-&amp;gt;before()`
- removed `log_registration_exception` as it is no longer relevant

## 1.13.0 - 2017-08-31

- added compatibility for Laravel 5.5

## 1.12.0

- made foreign key name to users table configurable

## 1.11.1

- `hasPermissionTo` uses the cache to avoid extra queries when it is called multiple times

## 1.11.0

- add `getDirectPermissions`, `getPermissionsViaRoles`, `getAllPermissions`

## 1.10.0 - 2017-02-22

- add `hasAnyPermission`

## 1.9.0 - 2017-02-20

- add `log_registration_exception` in settings file
- fix for ambiguous column name `id` when using the role scope

## 1.8.0 - 2017-02-09

- `hasDirectPermission` method is now public

## 1.7.0 - 2016-01-23

- added support for Laravel 5.4

## 1.6.1 - 2016-01-19

- make exception logging more verbose

## 1.6.0 - 2016-12-27

- added `Role` scope

## 1.5.3 - 2016-12-15

- moved some things to `boot` method in SP to solve some compatibility problems with other packages

## 1.5.2 - 2016-08-26

- make compatible with L5.3

## 1.5.1 - 2016-07-23

- fixes `givePermissionTo` and `assignRole` in Laravel 5.1

## 1.5.0 - 2016-07-23

** this version does not work in Laravel 5.1, please upgrade to version 1.5.1 of this package

- allowed `givePermissonTo` to accept multiple permissions
- allowed `assignRole` to accept multiple roles
- added `syncPermissions`-method
- added `syncRoles`-method
- dropped support for PHP 5.5 and HHVM

## 1.4.0 - 2016-05-08

- added `hasPermissionTo` function to the `Role` model

## 1.3.4 - 2016-02-27

- `hasAnyRole` can now properly process an array

## 1.3.3 - 2016-02-24

- `hasDirectPermission` can now accept a string

## 1.3.2 - 2016-02-23

- fixed user table configuration

## 1.3.1 - 2016-01-10

- fixed bug when testing for non existing permissions

## 1.3.0 - 2015-12-25

- added compatibility for Laravel 5.2

## 1.2.1 - 2015-12-22

- use database_path to publish migrations

## 1.2.0 - 2015-10-28

###Added

- support for custom models

## 1.1.0 - 2015-10-12

### Added

- Blade directives
- `hasAllRoles()`- and `hasAnyRole()`-functions

## 1.0.2 - 2015-10-11

### Fixed

- Fix for running phpunit locally

## 1.0.1 - 2015-09-30

### Fixed

- Fixed the inconsistent naming of the `hasPermission`-method.

## 1.0.0 - 2015-09-16

### Added

- Everything, initial release
