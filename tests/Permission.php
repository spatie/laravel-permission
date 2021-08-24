<?php

namespace Spatie\Permission\Test;

class Permission extends \Spatie\Permission\Models\Permission
{
    protected $visible = [
      'id', 
      'name',
    ];
}
