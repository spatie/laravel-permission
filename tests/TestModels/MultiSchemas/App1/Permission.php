<?php

namespace Spatie\Permission\Tests\TestModels\MultiSchemas\App1;

use Spatie\Permission\PermissionRegistrar;

class Permission extends \Spatie\Permission\Models\Permission
{
    protected $connection = 'sqlite';

    public static function getPermissionRegistrar(): PermissionRegistrar
    {
        return app('PermissionRegistrarApp1');
    }
}
