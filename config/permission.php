<?php

return [

    'models' => [

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your permissions. Of course, it
         * is often just the "Permission" model but you may use whatever you like.
         *
         * The model you want to use as a Permission model needs to implement the
         * `Spatie\Permission\Contracts\Permission` contract.
         */

        'permission' => Spatie\Permission\Models\Permission::class,

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your roles. Of course, it
         * is often just the "Role" model but you may use whatever you like.
         *
         * The model you want to use as a Role model needs to implement the
         * `Spatie\Permission\Contracts\Role` contract.
         */

        'role' => Spatie\Permission\Models\Role::class,

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your roles. Of course, it
         * is often just the "Role" model but you may use whatever you like.
         *
         * The model you want to use as a Tenant model needs to implement the
         * `Spatie\Permission\Contracts\Tenant` contract.
         */

        'tenant' => Spatie\Permission\Models\Tenant::class,


        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your roles. Of course, it
         * is often just the "Role" model but you may use whatever you like.
         */

        'role_tenant_pivot' => \Spatie\Permission\Models\RoleTenantUserPivot::class,

    ],

    'table_names' => [

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your roles. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */

        'roles' => 'roles',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your permissions. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */

        'permissions' => 'permissions',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your models permissions. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */

        'model_has_permissions' => 'model_has_permissions',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your models roles. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */

        'model_has_roles' => 'model_has_roles',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your roles permissions. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */

        'role_has_permissions' => 'role_has_permissions',

        /*
         * When using the "HasTentants" trait from this package, we need to know which
         * table should be used to retrieve your roles_tenant_user permissions. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */
        'role_tenant_user' => 'role_tenant_user',

        /*
         * When using the "HasTentants" trait from this package, we need to know which
         * table should be used to retrieve your roles_tenant_user permissions. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */
        'tenants' => 'tenants',

        /*
         * When using the "HasTentants" trait from this package, we need to know which
        * table should be used to retrieve your roles_tenant_user permissions. We have chosen a
        * basic default value but you may easily change it to any table you like.
        */
        'users' => 'users',
    ],

    /*
     * Configure the foreign keys for the role_tenant_user table.
     */
    'foreign_keys' => [
        'tenants' => [
            'id' => 'id',
            'key_type' => 'int',
            'str_length' => null,
        ], //Primary key for tenant table
    ],

    /*
     * By default all permissions will be cached for 24 hours unless a permission or
     * role is updated. Then the cache will be flushed immediately.
     */

    'cache_expiration_time' => 60 * 24,
];
