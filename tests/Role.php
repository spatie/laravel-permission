<?php

namespace Spatie\Permission\Test;

class Role extends \Spatie\Permission\Models\Role
{
    protected $primaryKey = 'role_test_id';

    protected $visible = [
        'role_test_id',
        'name',
    ];
}
