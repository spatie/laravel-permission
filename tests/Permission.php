<?php

namespace Spatie\Permission\Test;

class Permission extends \Spatie\Permission\Models\Permission
{
    protected $primaryKey = 'permission_test_id';

    protected $visible = [
      'permission_test_id',
      'name',
    ];
}
