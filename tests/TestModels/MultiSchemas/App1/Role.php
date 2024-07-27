<?php

namespace Spatie\Permission\Tests\TestModels\MultiSchemas\App1;

class Role extends \Spatie\Permission\Models\Role
{
    protected $connection = 'sqlite';

    public function getPermissionClass(): string
    {
        return Permission::class;
    }
}
