---
title: Database Seeding
weight: 2
---

## Flush cache before/after seeding

You may discover that it is best to flush this package's cache **BEFORE seeding, to avoid cache conflict errors**.

And if you use the `WithoutModelEvents` trait in your seeders, flush it **AFTER seeding as well**.

```php
// reset cached roles and permissions
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
```

You can optionally flush the cache before seeding by using the `SetUp()` method of your test suite (see the Testing page in the docs).

Or it can be done directly in a seeder class, as shown below.

## Roles/Permissions Seeder

Here is a sample seeder, which first clears the cache, creates permissions and then assigns permissions to roles (the order of these steps is intentional):

```php
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'edit articles']);
        Permission::create(['name' => 'delete articles']);
        Permission::create(['name' => 'publish articles']);
        Permission::create(['name' => 'unpublish articles']);

        // create roles and assign created permissions

        // this can be done as separate statements
        $role = Role::create(['name' => 'writer']);
        $role->givePermissionTo('edit articles');

        // or may be done by chaining
        $role = Role::create(['name' => 'moderator'])
            ->givePermissionTo(['publish articles', 'unpublish articles']);

        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());
    }
}
```

## User Seeding with Factories and States

To use Factory States to assign roles after creating users:

```php
// Factory:
    public function definition() {...}

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
            ])
            ->afterCreating(function (User $user) {
                $user->assignRole('ActiveMember');
            });
    }

// Seeder:
// To create 4 users using this 'active' state in a Seeder:
User::factory(4)->active()->create();
```

To seed multiple users and then assign each of them a role, WITHOUT using Factory States:

```php
// Seeder:
User::factory()
    ->count(50)
    ->create()
    ->each(function ($user) {
        $user->assignRole('Member');
    });
```


## Speeding up seeding for large data sets

When seeding large quantities of roles or permissions you may consider using Eloquent's `insert` command instead of `create`, as this bypasses all the internal checks that this package does when calling `create` (including extra queries to verify existence, test guards, etc).

```php
    $arrayOfPermissionNames = ['writer', 'editor'];
    $permissions = collect($arrayOfPermissionNames)->map(function ($permission) {
        return ['name' => $permission, 'guard_name' => 'web'];
    });

    Permission::insert($permissions->toArray());
```

Alternatively you could use `DB::insert`, as long as you also provide all the required data fields. One example of this is shown below ... but note that this example hard-codes the table names and field names, thus does not respect any customizations you may have in your permissions config file.

```php
$permissionsByRole = [
    'admin' => ['restore posts', 'force delete posts'],
    'editor' => ['create a post', 'update a post', 'delete a post'],
    'viewer' => ['view all posts', 'view a post']
];

$insertPermissions = fn ($role) => collect($permissionsByRole[$role])
    ->map(fn ($name) => DB::table('permissions')->insertGetId(['name' => $name, 'guard_name' => 'web']))
    ->toArray();

$permissionIdsByRole = [
    'admin' => $insertPermissions('admin'),
    'editor' => $insertPermissions('editor'),
    'viewer' => $insertPermissions('viewer')
];

foreach ($permissionIdsByRole as $role => $permissionIds) {
    $role = Role::whereName($role)->first();

    DB::table('role_has_permissions')
        ->insert(
            collect($permissionIds)->map(fn ($id) => [
                'role_id' => $role->id,
                'permission_id' => $id
            ])->toArray()
        );
}

// and also add the command to flush the cache again now after doing all these inserts
```

**CAUTION**: ANY TIME YOU DIRECTLY RUN DB QUERIES you are bypassing cache-control features. So you will need to manually flush the package cache AFTER running direct DB queries, even in a seeder.
