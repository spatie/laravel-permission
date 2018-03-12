# Changelog

All notable changes to `laravel-permission` will be documented in this file

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
NOTE: This Dynamic field naming was a breaking change, so we've removed it for now. 

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
The 403 response is backward compatible

## 2.7.2 - 2017-10-18
- refactor `PermissionRegistrar` to use `$gate->before()`
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



## 1.16.0 - 2018-02-07
- added support for Laravel 5.6

## 1.15 - 2017-12-08
- allow `hasAnyPermission` to take an array of permissions

## 1.14.1 - 2017-10-26
- fixed `Gate::before` for custom gate callbacks

## 1.14.0 - 2017-10-18
- refactor `PermissionRegistrar` to use `$gate->before()`
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
-  added `hasPermissionTo` function to the `Role` model

## 1.3.4 - 2016-02-27
-  `hasAnyRole` can now properly process an array

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
