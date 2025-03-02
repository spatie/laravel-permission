---
title: UUID/ULID
weight: 7
---

If you're using UUIDs (ULID, GUID, etc) for your User models or Role/Permission models there are a few considerations to note.

> NOTE: THIS IS NOT A FULL LESSON ON HOW TO IMPLEMENT UUIDs IN YOUR APP.

Since each UUID implementation approach is different, some of these may or may not benefit you. As always, your implementation may vary.

We use "uuid" in the examples below. Adapt for ULID or GUID as needed.

## Migrations
You will need to update the `create_permission_tables.php` migration after creating it with `php artisan vendor:publish`. After making your edits, be sure to run the migration!

**User Models using UUIDs**
If your User models are using `uuid` instead of `unsignedBigInteger` then you'll need to reflect the change in the migration provided by this package. Something like the following would be typical, for **both** `model_has_permissions` and `model_has_roles` tables:

```diff
// note: this is done in two places in the default migration file, so edit both places:
-  $table->unsignedBigInteger($columnNames['model_morph_key'])
+  $table->uuid($columnNames['model_morph_key'])
```

**Roles and Permissions using UUIDS**
If you also want the roles and permissions to use a UUID for their `id` value, then you'll need to change the id fields accordingly, and manually set the primary key.

```diff
    Schema::create($tableNames['permissions'], function (Blueprint $table) {
-        $table->bigIncrements('id'); // permission id
+        $table->uuid('uuid')->primary()->unique(); // permission id
//...
    });

    Schema::create($tableNames['roles'], function (Blueprint $table) {
-        $table->bigIncrements('id'); // role id
+        $table->uuid('uuid')->primary()->unique(); // role id
//...
    });

    Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames) {
-        $table->unsignedBigInteger($pivotPermission);
+        $table->uuid($pivotPermission);
        $table->string('model_type');
//...
        $table->foreign($pivotPermission)
-            ->references('id') // permission id
+            ->references('uuid') // permission id
            ->on($tableNames['permissions'])
            ->onDelete('cascade');
//...

    Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames) {
-        $table->unsignedBigInteger($pivotRole);
+        $table->uuid($pivotRole);
//...
        $table->foreign($pivotRole)
-            ->references('id') // role id
+            ->references('uuid') // role id
            ->on($tableNames['roles'])
            ->onDelete('cascade');//...

    Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
-        $table->unsignedBigInteger($pivotPermission);
-        $table->unsignedBigInteger($pivotRole);
+        $table->uuid($pivotPermission);
+        $table->uuid($pivotRole);

         $table->foreign($pivotPermission)
-            ->references('id') // permission id
+            ->references('uuid') // permission id
            ->on($tableNames['permissions'])
            ->onDelete('cascade');

         $table->foreign($pivotRole)
-            ->references('id') // role id
+            ->references('uuid') // role id
            ->on($tableNames['roles'])
            ->onDelete('cascade'); 
```


## Configuration (OPTIONAL)
You might want to change the pivot table field name from `model_id` to `model_uuid`, just for semantic purposes.
For this, in the `permission.php` configuration file edit `column_names.model_morph_key`:

- OPTIONAL: Change to `model_uuid` instead of the default `model_id`.
```diff
        'column_names' => [    
        /*
         * Change this if you want to name the related pivots other than defaults
         */
        'role_pivot_key' => null, //default 'role_id',
        'permission_pivot_key' => null, //default 'permission_id',

        /*
         * Change this if you want to name the related model primary key other than
         * `model_id`.
         *
         * For example, this would be nice if your primary keys are all UUIDs. In
         * that case, name this `model_uuid`.
         */
-            'model_morph_key' => 'model_id',
+            'model_morph_key' => 'model_uuid',
        ],
```
- If you extend the models into your app, be sure to list those models in your `permissions.php` configuration file. See the Extending section of the documentation and the Models section below.

## Models
If you want all the role/permission objects to have a UUID instead of an integer, you will need to Extend the default Role and Permission models into your own namespace in order to set some specific properties. (See the Extending section of the docs, where it explains requirements of Extending, as well as the `permissions.php` configuration settings you need to update.)

Examples:

Create new models, which extend the Role and Permission models of this package, and add Laravel's `HasUuids` trait (available since Laravel 9):
```bash
php artisan make:model Role
php artisan make:model Permission
```

`App\Model\Role.php`
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;
    use HasUuids;
    protected $primaryKey = 'uuid';
}
```

`App\Model\Permission.php`
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory;
    use HasUuids;
    protected $primaryKey = 'uuid';
}
```
And edit `config/permission.php`
```diff
    'models' => [

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your permissions. Of course, it
         * is often just the "Permission" model but you may use whatever you like.
         *
         * The model you want to use as a Permission model needs to implement the
         * `Spatie\Permission\Contracts\Permission` contract.
         */

-        'permission' => Spatie\Permission\Models\Permission::class
+        'permission' => \App\Models\Permission::class,

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your roles. Of course, it
         * is often just the "Role" model but you may use whatever you like.
         *
         * The model you want to use as a Role model needs to implement the
         * `Spatie\Permission\Contracts\Role` contract.
         */

-        'role' => Spatie\Permission\Models\Role::class,
+        'role' => \App\Models\Role::class,

    ],
```
