<?php

namespace Spatie\Permission\Tests\TestModels;

class RuntimeRole extends \Spatie\Permission\Models\Role
{
    protected $visible = [
        'id',
        'name',
    ];
}
