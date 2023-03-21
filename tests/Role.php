<?php

namespace Spatie\Permission\Test;

use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends \Spatie\Permission\Models\Role
{
    use SoftDeletes;

    protected $primaryKey = 'role_test_id';

    protected $visible = [
        'role_test_id',
        'name',
    ];
}
