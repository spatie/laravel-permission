<?php

namespace Spatie\Permission\Tests\TestModels\MultiSchemas\App1;

class Permission extends \Spatie\Permission\Models\Permission
{
    protected $connection = 'sqlite';

    public function getRoleClass(): string
    {
        return Role::class;
    }
}
