---
title: Enums
weight: 4
---

## Enum Prerequisites

Requires `version 6` of this package.

Requires PHP 8.1 or higher.

If you are using PHP 8.1+ you can implement Enums as native types. 

Internally, Enums implicitly implement `\BackedEnum`, which is how this package recognizes that you're passing an Enum.


## Code Requirements

You can create your Enum object for use with Roles and/or Permissions. You will probably create separate Enums for Roles and for Permissions, although if your application needs are simple you might choose a single Enum for both.

Usually the list of application Roles is much shorter than the list of Permissions, so having separate objects for them can make them easier to manage.

Here is an example Enum for Roles. You would do similarly for Permissions.

```php
namespace App\Enums;

enum RolesEnum: string
{
    // case NAMEINAPP = 'name-in-database';

    case WRITER = 'writer';
    case EDITOR = 'editor';
    case USERMANAGER = 'user-manager';

    // extra helper to allow for greater customization of displayed values, without disclosing the name/value data directly
    public function label(): string
    {
        return match ($this) {
            static::WRITER => 'Writers',
            static::EDITOR => 'Editors',
            static::USERMANAGER => 'User Managers',
        };
    }
}
```

## Creating Roles/Permissions using Enums

When creating roles/permissions, you cannot pass a Enum name directly, because Eloquent expects a string for the name.

You must manually convert the name to its value in order to pass the correct string to Eloquent for the role/permission name.

eg: use `RolesEnum::WRITER->value` when specifying the role/permission name

```php
  $role = app(Role::class)->findOrCreate(RolesEnum::WRITER->value, 'web');
```
Same with creating Permissions.

### Authorizing using Enums

In your application code, when checking for authorization using features of this package, you can use `MyEnum::NAME` directly in most cases, without passing `->value` to convert to a string.

There will be times where you will need to manually fallback to adding `->value` (eg: `MyEnum::NAME->value`) when using features that aren't aware of Enum support. This will occur when you need to pass `string` values instead of an `Enum`, such as when interacting with Laravel's Gate via the `can()` methods/helpers (eg: `can`, `canAny`, etc).

Examples:
```php
// the following are identical because `hasPermissionTo` is aware of `BackedEnum` support:
$user->hasPermissionTo(PermissionsEnum::VIEWPOSTS);
$user->hasPermissionTo(PermissionsEnum::VIEWPOSTS->value);

// when calling Gate features, such as Model Policies, etc
$user->can(PermissionsEnum::VIEWPOSTS->value);
$model->can(PermissionsEnum::VIEWPOSTS->value);

// Blade directives:
@can(PermissionsEnum::VIEWPOSTS->value)
```


## Package methods supporting BackedEnums:
The following methods of this package support passing `BackedEnum` parameters directly:

```php
	$user->assignRole(RolesEnum::WRITER);
	$user->removeRole(RolesEnum::EDITOR);

    $role->givePermissionTo(PermissionsEnum::EDITPOSTS);
    $role->revokePermissionTo(PermissionsEnum::EDITPOSTS);

    $user->givePermissionTo(PermissionsEnum::EDITPOSTS);
    $user->revokePermissionTo(PermissionsEnum::EDITPOSTS);

	$user->hasPermissionTo(PermissionsEnum::EDITPOSTS);
	$user->hasAnyPermission([PermissionsEnum::EDITPOSTS, PermissionsEnum::VIEWPOSTS]);
	$user->hasDirectPermission(PermissionsEnum::EDITPOSTS);
    
    $user->hasRole(RolesEnum::WRITER);
    $user->hasAllRoles([RolesEnum::WRITER, RolesEnum::EDITOR]);
    $user->hasExactRoles([RolesEnum::WRITER, RolesEnum::EDITOR, RolesEnum::MANAGER]);

```
