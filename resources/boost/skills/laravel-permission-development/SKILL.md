---
name: laravel-permission-development
description: Build and work with Spatie Laravel Permission features, including roles, permissions, middleware, policies, teams, and Blade directives.
---

# Laravel Permission Development

## When to use this skill

Use this skill when working with authorization, roles, permissions, access control, middleware guards, or Blade permission directives using spatie/laravel-permission.

## Core Concepts

- **Users have Roles, Roles have Permissions, Apps check Permissions** (not Roles).
- Direct permissions on users are an anti-pattern; assign permissions to roles instead.
- Use `$user->can('permission-name')` for all authorization checks (supports Super Admin via Gate).
- The `HasRoles` trait (which includes `HasPermissions`) is added to User models.

## Setup

Add the `HasRoles` trait to your User model:

```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
}
```

## Creating Roles and Permissions

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$role = Role::create(['name' => 'writer']);
$permission = Permission::create(['name' => 'edit articles']);

// findOrCreate is idempotent (safe for seeders)
$role = Role::findOrCreate('writer', 'web');
$permission = Permission::findOrCreate('edit articles', 'web');
```

## Assigning Roles and Permissions

```php
// Assign roles to users
$user->assignRole('writer');
$user->assignRole('writer', 'admin');
$user->assignRole(['writer', 'admin']);
$user->syncRoles(['writer', 'admin']); // replaces all
$user->removeRole('writer');

// Assign permissions to roles (preferred)
$role->givePermissionTo('edit articles');
$role->givePermissionTo(['edit articles', 'delete articles']);
$role->syncPermissions(['edit articles', 'delete articles']);
$role->revokePermissionTo('edit articles');

// Reverse assignment
$permission->assignRole('writer');
$permission->syncRoles(['writer', 'editor']);
$permission->removeRole('writer');
```

## Checking Roles and Permissions

```php
// Permission checks (preferred - supports Super Admin via Gate)
$user->can('edit articles');
$user->canAny(['edit articles', 'delete articles']);

// Direct package methods (bypass Gate, no Super Admin support)
$user->hasPermissionTo('edit articles');
$user->hasAnyPermission(['edit articles', 'publish articles']);
$user->hasAllPermissions(['edit articles', 'publish articles']);
$user->hasDirectPermission('edit articles');

// Role checks
$user->hasRole('writer');
$user->hasAnyRole(['writer', 'editor']);
$user->hasAllRoles(['writer', 'editor']);
$user->hasExactRoles(['writer', 'editor']);

// Get assigned roles and permissions
$user->getRoleNames();           // Collection of role name strings
$user->getPermissionNames();     // Collection of permission name strings
$user->getDirectPermissions();   // Direct permissions only
$user->getPermissionsViaRoles(); // Inherited via roles
$user->getAllPermissions();      // Both direct and inherited
```

## Query Scopes

```php
$users = User::role('writer')->get();
$users = User::withoutRole('writer')->get();
$users = User::permission('edit articles')->get();
$users = User::withoutPermission('edit articles')->get();
```

## Middleware

Register middleware aliases in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    ]);
})
```

Use in routes (pipe `|` for OR logic):

```php
Route::middleware(['permission:edit articles'])->group(function () { ... });
Route::middleware(['role:manager|writer'])->group(function () { ... });
Route::middleware(['role_or_permission:manager|edit articles'])->group(function () { ... });

// With specific guard
Route::middleware(['role:manager,api'])->group(function () { ... });
```

For single permissions, Laravel's built-in `can` middleware also works:

```php
Route::middleware(['can:edit articles'])->group(function () { ... });
```

## Blade Directives

Prefer `@can` (permission-based) over `@role` (role-based):

```blade
@can('edit articles')
    {{-- User can edit articles (supports Super Admin) --}}
@endcan

@canany(['edit articles', 'delete articles'])
    {{-- User can do at least one --}}
@endcanany

@role('admin')
    {{-- Only use for super-admin type checks --}}
@endrole

@hasanyrole('writer|admin')
    {{-- Has writer or admin --}}
@endhasanyrole
```

## Super Admin

Use `Gate::before` in `AppServiceProvider::boot()`:

```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::before(function ($user, $ability) {
        return $user->hasRole('Super Admin') ? true : null;
    });
}
```

This makes `$user->can()` and `@can` always return true for Super Admins. Must return `null` (not `false`) to allow normal checks for other users.

## Policies

Use `$user->can()` inside policy methods to check permissions:

```php
class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        if ($user->can('edit all posts')) {
            return true;
        }

        return $user->can('edit own posts') && $user->id === $post->user_id;
    }
}
```

## Enums

```php
enum RolesEnum: string
{
    case WRITER = 'writer';
    case EDITOR = 'editor';
}

enum PermissionsEnum: string
{
    case EDIT_POSTS = 'edit posts';
    case DELETE_POSTS = 'delete posts';
}

// Creation requires ->value
Permission::findOrCreate(PermissionsEnum::EDIT_POSTS->value, 'web');

// Most methods accept enums directly
$user->assignRole(RolesEnum::WRITER);
$user->hasRole(RolesEnum::WRITER);
$role->givePermissionTo(PermissionsEnum::EDIT_POSTS);
$user->hasPermissionTo(PermissionsEnum::EDIT_POSTS);
```

## Seeding

Always flush the permission cache when seeding:

```php
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::findOrCreate('edit articles', 'web');
        Permission::findOrCreate('delete articles', 'web');

        // Create roles and assign permissions
        Role::findOrCreate('writer', 'web')
            ->givePermissionTo(['edit articles']);

        Role::findOrCreate('admin', 'web')
            ->givePermissionTo(Permission::all());
    }
}
```

## Teams (Multi-Tenancy)

Enable in `config/permission.php` before running migrations:

```php
'teams' => true,
```

Set the active team in middleware:

```php
setPermissionsTeamId($teamId);
```

When switching teams, unset cached relations:

```php
$user->unsetRelation('roles')->unsetRelation('permissions');
```

## Events

Enable in `config/permission.php`:

```php
'events_enabled' => true,
```

Available events: `RoleAttachedEvent`, `RoleDetachedEvent`, `PermissionAttachedEvent`, `PermissionDetachedEvent` in the `Spatie\Permission\Events` namespace.

## Performance

- Permissions are cached automatically. The cache is flushed when roles/permissions change via package methods.
- After direct DB operations, flush manually: `app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions()`
- For bulk seeding, use `Permission::insert()` for speed, but flush the cache afterward.
