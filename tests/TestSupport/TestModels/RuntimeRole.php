<?php

namespace Spatie\Permission\Tests\TestSupport\TestModels;

class RuntimeRole extends \Spatie\Permission\Models\Role
{
    protected $visible = [
        'id',
        'name',
    ];
}
