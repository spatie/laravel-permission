# Changelog

All notable changes to `laravel-permission` will be documented in this file

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

- cache expiration is now configurable and set to one day by default
- roles and permissions can now be assigned to any model through the `HasRoles` trait
- removed deprecated `hasPermission` method
- renamed config file from `laravel-permission` to `permission`.

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
- moved some things to `boot` method in SP to solve some compatibilty problems with other packages

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

- added compatiblity for Laravel 5.2

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
