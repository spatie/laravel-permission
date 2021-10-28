<?php

namespace Spatie\Permission\Test;

class RuntimeRole extends \Spatie\Permission\Models\Role
{
    protected $visible = [
      'id',
      'name',
    ];
}
