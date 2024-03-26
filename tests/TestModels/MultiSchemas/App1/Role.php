<?php

namespace Spatie\Permission\Tests\TestModels\MultiSchemas\App1;

use Spatie\Permission\PermissionRegistrar;

class Role extends \Spatie\Permission\Models\Role
{
    protected $connection = 'sqlite';

    public static function getPermissionRegistrar(): PermissionRegistrar
    {
        return app('PermissionRegistrarApp1');
    }
}
