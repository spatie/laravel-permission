# Upgrading to v7

## Requirements

- PHP 8.4 or higher
- Laravel 12 or higher

## Service Provider

The service provider now extends `PackageServiceProvider` from `spatie/laravel-package-tools`. If you have published or extended the service provider, update your references accordingly.

Lumen support has been removed.

## Event Class Renames

All event classes now have an `Event` suffix:

| v6 | v7 |
|---|---|
| `Spatie\Permission\Events\PermissionAttached` | `Spatie\Permission\Events\PermissionAttachedEvent` |
| `Spatie\Permission\Events\PermissionDetached` | `Spatie\Permission\Events\PermissionDetachedEvent` |
| `Spatie\Permission\Events\RoleAttached` | `Spatie\Permission\Events\RoleAttachedEvent` |
| `Spatie\Permission\Events\RoleDetached` | `Spatie\Permission\Events\RoleDetachedEvent` |

Update any event listeners that reference these classes.

## Command Class Renames

All command classes now have a `Command` suffix:

| v6 | v7 |
|---|---|
| `Spatie\Permission\Commands\CacheReset` | `Spatie\Permission\Commands\CacheResetCommand` |
| `Spatie\Permission\Commands\CreateRole` | `Spatie\Permission\Commands\CreateRoleCommand` |
| `Spatie\Permission\Commands\CreatePermission` | `Spatie\Permission\Commands\CreatePermissionCommand` |
| `Spatie\Permission\Commands\Show` | `Spatie\Permission\Commands\ShowCommand` |
| `Spatie\Permission\Commands\UpgradeForTeams` | `Spatie\Permission\Commands\UpgradeForTeamsCommand` |
| `Spatie\Permission\Commands\AssignRole` | `Spatie\Permission\Commands\AssignRoleCommand` |

The artisan command signatures remain unchanged.

## Removed Deprecated Methods

- `PermissionRegistrar::clearClassPermissions()` has been removed. Use `clearPermissionsCollection()` instead.

## Type Hints

Return types and parameter types have been added throughout the codebase. If you have extended any of the following classes or traits, you may need to update your method signatures:

- `HasPermissions` trait: `givePermissionTo()`, `syncPermissions()`, `revokePermissionTo()` now return `static`
- `HasRoles` trait: `assignRole()`, `removeRole()`, `syncRoles()` now return `static`
- Exception factory methods now return `static` instead of `self`
- `PermissionRegistrar::setPermissionClass()` and `setRoleClass()` now return `static`
- `PermissionRegistrar::forgetCachedPermissions()` now returns `bool`
- `Contracts\PermissionsTeamResolver::setPermissionsTeamId()` now has typed parameter `int|string|Model|null $id`
- `Contracts\Role::hasPermissionTo()` now has typed parameter and optional `$guardName`

## Wildcard Contract

The `__construct(Model $record)` method has been removed from the `Spatie\Permission\Contracts\Wildcard` interface. If you implement this contract, you can remove the constructor from the interface requirement (your concrete class should still accept a `Model` in its constructor).
