<?php

namespace Spatie\Permission\Test;

use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends \Spatie\Permission\Models\Permission
{
    use SoftDeletes;

    protected $primaryKey = 'permission_test_id';

    protected $visible = [
        'permission_test_id',
        'name',
    ];
}
