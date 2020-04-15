<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return [

    'models' => [

        /*
        |--------------------------------------------------------------------------
        | Permission Model
        |--------------------------------------------------------------------------
        |
        | When using the "HasPermissions" trait from this package, we need to know which
        | Eloquent model should be used to retrieve your permissions. Of course, it
        | is often just the "Permission" model but you may use whatever you like.
        |
        */

        'permission' => Permission::class,

        /*
        |--------------------------------------------------------------------------
        | Role Model
        |--------------------------------------------------------------------------
        |
        | When using the "HasRoles" trait from this package, we need to know which
        | Eloquent model should be used to retrieve your roles. Of course, it
        | is often just the "Role" model but you may use whatever you like.
        |
        */

        'role' => Role::class,

    ],

    'table_names' => [

        /*
        |--------------------------------------------------------------------------
        | Role Table
        |--------------------------------------------------------------------------
        |
        | When using the "HasRoles" trait from this package, we need to know which
        | table should be used to retrieve your roles. We have chosen a basic
        | default value but you may easily change it to any table you like.
        |
        */

        'roles' => 'roles',

        /*
        |--------------------------------------------------------------------------
        | Permission Table
        |--------------------------------------------------------------------------
        |
        | When using the "HasPermissions" trait from this package, we need to know which
        | table should be used to retrieve your permissions. We have chosen a basic
        | default value but you may easily change it to any table you like.
        |
        */

        'permissions' => 'permissions',

        /*
        |--------------------------------------------------------------------------
        | Model Permission
        |--------------------------------------------------------------------------
        |
        | When using the "HasPermissions" trait from this package, we need to know which
        | table should be used to retrieve your models permissions. We have chosen a
        | basic default value but you may easily change it to any table you like.
        |
        */

        'model_has_permissions' => 'model_has_permissions',

        /*
        |--------------------------------------------------------------------------
        | Model Role
        |--------------------------------------------------------------------------
        |
        | When using the "HasRoles" trait from this package, we need to know which
        | table should be used to retrieve your models roles. We have chosen a
        | basic default value but you may easily change it to any table you like.
        |
        */

        'model_has_roles' => 'model_has_roles',

        /*
        |--------------------------------------------------------------------------
        | Role Permission
        |--------------------------------------------------------------------------
        |
        | When using the "HasRoles" trait from this package, we need to know which
        | table should be used to retrieve your roles permissions. We have chosen a
        | basic default value but you may easily change it to any table you like.
        |
        */

        'role_has_permissions' => 'role_has_permissions',

    ],

    'column_names' => [

        /*
        |--------------------------------------------------------------------------
        | Model Morph Key
        |--------------------------------------------------------------------------
        |
        | Change this if you want to name the related model primary key other than
        | `model_id`.
        |
        */

        'model_morph_key' => 'model_id',

    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Exception
    |--------------------------------------------------------------------------
    |
    | When set to true, the required permission/role names are added to the exception
    | message. This could be considered an information leak in some contexts, so
    | the default setting is false here for optimum safety.
    |
    */

    'display_permission_in_exception' => false,

    /*
    |--------------------------------------------------------------------------
    | Permission Wildcard
    |--------------------------------------------------------------------------
    |
    | By default wildcard permission lookups are disabled.
    |
    */

    'enable_wildcard_permission' => false,

    'cache' => [

        /*
        |--------------------------------------------------------------------------
        | Expiration Time
        |--------------------------------------------------------------------------
        |
        | By default all permissions are cached for 24 hours to speed up performance.
        | When permissions or roles are updated the cache is flushed automatically.
        |
        */

        'expiration_time' => \DateInterval::createFromDateString('24 hours'),

        /*
        |--------------------------------------------------------------------------
        | Cache Key
        |--------------------------------------------------------------------------
        |
        | The cache key used to store all permissions.
        |
        */

        'key' => 'spatie.permission.cache',

        /*
        |--------------------------------------------------------------------------
        | Model Key
        |--------------------------------------------------------------------------
        |
        | When checking for a permission against a model by passing a Permission
        | instance to the check, this key determines what attribute on the
        | Permissions model is used to cache against.
        |
        */

        'model_key' => 'name',

        /*
        |--------------------------------------------------------------------------
        | Store
        |--------------------------------------------------------------------------
        |
        | You may optionally indicate a specific cache driver to use for permission and
        | role caching using any of the `store` drivers listed in the cache.php config
        | file. Using 'default' here means to use the `default` set in cache.php.
        |
        */

        'store' => 'default',

    ],

];
