<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authorization Models
    |--------------------------------------------------------------------------
    */

    'models' => [
        /*
         * The class name of the permission model to be used.
         */
        'permission' => Spatie\Permission\Models\Permission::class,

        /*
         * The class name of the role model to be used.
         */
        'role' => Spatie\Permission\Models\Role::class,

        /*
         * The class name of the user model to be used.
         */
        'user' => App\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization Tables
    |--------------------------------------------------------------------------
    */

    'tableNames' => [
        /*
         * The name of the "users" table to be used.
         */
        'users' => 'users',

        /*
         * The name of the "roles" table to be used.
         */
        'roles' => 'roles',

        /*
         * The name of the "permissions" table to be used.
         */
        'permissions' => 'permissions',

        /*
         * The name of the "user_has_permissions" table to be used.
         */
        'user_has_permissions' => 'user_has_permissions',

        /*
         * The name of the "user_has_roles" table to be used.
         */
        'user_has_roles' => 'user_has_roles',

        /*
         * The name of the "role_has_permissions" table to be used.
         */
        'role_has_permissions' => 'role_has_permissions',
    ],

];
