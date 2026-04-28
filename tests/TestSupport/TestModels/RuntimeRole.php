<?php

namespace Spatie\Permission\Tests\TestSupport\TestModels;

use Spatie\Permission\Models\Role;

class RuntimeRole extends Role
{
    protected $visible = [
        'id',
        'name',
    ];
}
