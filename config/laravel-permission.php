<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Role Model
    |--------------------------------------------------------------------------
    |
    | We need to know which model contains Role functionality.
    | Of course, it is often just the supplied "Role" model but you may use
    | whatever you like, e.g. in case of additional attributes or class extension.
    |
    | Be sure to have it implement the following interfaces:
    |  - Spatie\Permissions\Contracts\RoleContract
    |  - Spatie\Permissions\Contracts\HasPermissionsContract
    |
    */

    'role'       => Spatie\Permission\Models\Role::class,

    /*
    |--------------------------------------------------------------------------
    | Permission Model
    |--------------------------------------------------------------------------
    |
    | We need to know which model contains Permission functionality.
    | Of course, it is often just the supplied "Permission" model but you may use
    | whatever you like, e.g. in case of additional attributes or class extension.
    |
    | Be sure to have it implement the following interfaces:
    |  - Spatie\Permissions\Contracts\PermissionContract
    |
    */

    'permission' => Spatie\Permission\Models\Permission::class,

];