---
title: UUID
weight: 7
---

If you're using UUIDs or GUIDs for your User models there are a few considerations to note.

> THIS IS NOT A FULL LESSON ON HOW TO IMPLEMENT UUIDs IN YOUR APP.

Since each UUID implementation approach is different, some of these may or may not benefit you. As always, your implementation may vary.


## Migrations
You will probably want to update the `create_permission_tables.php` migration:

If your User models are using `uuid` instead of `unsignedBigInteger` then you'll need to reflect the change in the migration provided by this package. Something like this would be typical, for both `model_has_permissions` and `model_has_roles` tables:

```diff
-  $table->unsignedBigInteger($columnNames['model_morph_key'])
+  $table->uuid($columnNames['model_morph_key'])
```

OPTIONAL: If you also want the roles and permissions to use a UUID for their `id` value, then you'll need to also change the id fields accordingly, and manually set the primary key. LEAVE THE FIELD NAME AS `id` unless you also change it in dozens of other places.

```diff
    Schema::create($tableNames['permissions'], function (Blueprint $table) {
-        $table->bigIncrements('id'); // permission id
+        $table->uuid('id')->primary()->unique(); // permission id
        $table->string('name');
        $table->string('guard_name');
        $table->timestamps();

        $table->unique(['name', 'guard_name']);
    });

    Schema::create($tableNames['roles'], function (Blueprint $table) {
-        $table->bigIncrements('id'); // role id
+        $table->uuid('id')->primary()->unique(); // role id
        if ($teams || config('permission.testing')) { // permission.testing is a fix for sqlite testing
            $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
            $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
        }
        $table->string('name');       // For MySQL 8.0 use string('name', 125);
        $table->string('guard_name'); // For MySQL 8.0 use string('guard_name', 125);
        $table->timestamps();
        if ($teams || config('permission.testing')) {
            $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
        } else {
            $table->unique(['name', 'guard_name']);
        }
});

    Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames) {
-        $table->unsignedBigInteger(PermissionRegistrar::$pivotPermission);
+        $table->uuid(PermissionRegistrar::$pivotPermission);
    ...

    Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames) {
-        $table->unsignedBigInteger(PermissionRegistrar::$pivotRole);
+        $table->uuid(PermissionRegistrar::$pivotRole);
        $table->string('model_type');
-       $table->unsignedBigInteger($columnNames['model_morph_key']);
+       $table->uuid($columnNames['model_morph_key']);
    ...

    Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
-        $table->unsignedBigInteger(PermissionRegistrar::$pivotPermission);
-        $table->unsignedBigInteger(PermissionRegistrar::$pivotRole);
+        $table->uuid(PermissionRegistrar::$pivotPermission);
+        $table->uuid(PermissionRegistrar::$pivotRole);
```


## Configuration (OPTIONAL)
You might want to change the pivot table field name from `model_id` to `model_uuid`, just for semantic purposes.
For this, in the configuration file edit `column_names.model_morph_key`:

- OPTIONAL: Change to `model_uuid` instead of the default `model_id`. (The default of `model_id` is shown in this snippet below. Change it to match your needs.)
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
- If you extend the models into your app, be sure to list those models in your configuration file. See the Extending section of the documentation and the Models section below.

## Models
If you want all the role/permission objects to have a UUID instead of an integer, you will need to Extend the default Role and Permission models into your own namespace in order to set some specific properties. (See the Extending section of the docs, where it explains requirements of Extending, as well as the configuration settings you need to update.)

- You likely want to set `protected $keyType = 'string';` so Laravel handles joins as strings and doesn't cast to integer.
- OPTIONAL: If you changed the field name in your migrations, you must set `protected $primaryKey = 'uuid';` to match.
- Usually for UUID you will also set `public $incrementing = false;`. Remove it if it causes problems for you.

Examples:

Create new models:
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
    use HasFactory, HasUuids;
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
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
    use HasFactory, HasUuids;
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
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
+        'permission' => App\Models\Permission::class,

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your roles. Of course, it
         * is often just the "Role" model but you may use whatever you like.
         *
         * The model you want to use as a Role model needs to implement the
         * `Spatie\Permission\Contracts\Role` contract.
         */

-        'role' => Spatie\Permission\Models\Role::class,
+        'role' => App\Models\Role::class,

    ],
```


It is common to use a trait to handle the $keyType and $incrementing settings, as well as add a boot event trigger to ensure new records are assigned a uuid. You would `use` this trait in your User and extended Role/Permission models. An example `UuidTrait` is shown here for inspiration. Adjust to suit your needs.

```php
    <?php
    namespace App;

    use Facades\Str;

    trait UuidTrait
    {
        public static function bootUuidTrait()
        {
            static::creating(function ($model) {
                $model->keyType = 'string';
                $model->incrementing = false;

                $model->{$model->getKeyName()} = $model->{$model->getKeyName()} ?: (string) Str::orderedUuid();
            });
        }
        
        public function getIncrementing()
        {
            return false;
        }
        
        public function getKeyType()
        {
            return 'string';
        }
    }
```


## User Models
> Troubleshooting tip: In the ***Prerequisites*** section of the docs we remind you that your User model must implement the `Illuminate\Contracts\Auth\Access\Authorizable` contract so that the Gate features are made available to the User object.
In the default User model provided with Laravel, this is done by extending another model (aliased to `Authenticatable`), which extends the base Eloquent model. 
However, your app's UUID implementation may need to override that in order to set some of the properties mentioned in the Models section above. 

If you are running into difficulties, you may want to double-check whether your User model is doing UUIDs consistent with other parts of your app.


# REMINDER:

> THIS IS NOT A FULL LESSON ON HOW TO IMPLEMENT UUIDs IN YOUR APP.

Again, since each UUID implementation approach is different, some of these may or may not benefit you. As always, your implementation may vary.



## Packages
There are many packages offering UUID features for Eloquent models. You may want to explore whether these are of value to you in your study of implementing UUID in your applications:

https://github.com/JamesHemery/laravel-uuid
https://github.com/jamesmills/eloquent-uuid
https://github.com/goldspecdigital/laravel-eloquent-uuid
https://github.com/michaeldyrynda/laravel-model-uuid

Remember: always make sure you understand what a package is doing before you use it! If it's doing "more than what you need" then you're adding more complexity to your application, as well as more things to test and support!
