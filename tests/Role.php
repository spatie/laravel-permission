<?php

namespace Spatie\Permission\Test;

class Role extends \Spatie\Permission\Models\Role
{
    protected $visible = [
      'id', 
      'name',
    ];
}
