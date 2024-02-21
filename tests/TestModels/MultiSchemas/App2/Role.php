<?php

namespace Spatie\Permission\Tests\TestModels\MultiSchemas\App2;

class Role extends \Spatie\Permission\Models\Role
{
    protected $connection = 'sqlite2';

    public function getPermissionClass(): string
    {
        return Permission::class;
    }
}
