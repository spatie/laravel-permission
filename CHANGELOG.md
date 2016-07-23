# Changelog

All Notable changes to `laravel-permission` will be documented in this file

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
