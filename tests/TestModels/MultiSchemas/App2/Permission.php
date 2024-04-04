<?php

namespace Spatie\Permission\Tests\TestModels\MultiSchemas\App2;

use Spatie\Permission\PermissionRegistrar;

class Permission extends \Spatie\Permission\Models\Permission
{
    protected $connection = 'sqlite2';

    public static function getPermissionRegistrar(): PermissionRegistrar
    {
        return app('PermissionRegistrarApp2');
    }
}
