<?php

namespace Spatie\Permission\Tests\TestModels\MultiSchemas\App2;

use Spatie\Permission\PermissionRegistrar;

class Role extends \Spatie\Permission\Models\Role
{
    protected $connection = 'sqlite2';

    public static function getPermissionRegistrar(): PermissionRegistrar
    {
        return app('PermissionRegistrarApp2');
    }
}
