---
title: Blade directives
weight: 4
---

## Permissions
This package doesn't add any **permission**-specific Blade directives. 
Instead, use Laravel's native `@can` directive to check if a user has a certain permission.

```php
@can('edit articles')
  //
@endcan
```
or
```php
@if(auth()->user()->can('edit articles') && $some_other_condition)
  //
@endif
```

You can use `@can`, `@cannot`, `@canany`, and `@guest` to test for permission-related access.


## Roles 
As discussed in the Best Practices section of the docs, **it is strongly recommended to always use permission directives**, instead of role directives.

Additionally, if your reason for testing against Roles is for a Super-Admin, see the *Defining A Super-Admin* section of the docs.

If you actually need to test for Roles, this package offers some Blade directives to verify whether the currently logged in user has all or any of a given list of roles. 

Optionally you can pass in the `guard` that the check will be performed on as a second argument.

#### Blade and Roles
Check for a specific role:
```php
@role('writer')
    I am a writer!
@else
    I am not a writer...
@endrole
```
is the same as
```php
@hasrole('writer')
    I am a writer!
@else
    I am not a writer...
@endhasrole
```

Check for any role in a list:
```php
@hasanyrole($collectionOfRoles)
    I have one or more of these roles!
@else
    I have none of these roles...
@endhasanyrole
// or
@hasanyrole('writer|admin')
    I am either a writer or an admin or both!
@else
    I have none of these roles...
@endhasanyrole
```
Check for all roles:

```php
@hasallroles($collectionOfRoles)
    I have all of these roles!
@else
    I do not have all of these roles...
@endhasallroles
// or
@hasallroles('writer|admin')
    I am both a writer and an admin!
@else
    I do not have all of these roles...
@endhasallroles
```

Alternatively, `@unlessrole` gives the reverse for checking a singular role, like this:

```php
@unlessrole('does not have this role')
    I do not have the role
@else
    I do have the role
@endunlessrole
```

You can also determine if a user has exactly all of a given list of roles:

```php
@hasexactroles('writer|admin')
    I am both a writer and an admin and nothing else!
@else
    I do not have all of these roles or have more other roles...
@endhasexactroles
```
