<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\RoleTrait;
use Spatie\Permission\Contracts\RoleContract;
use Spatie\Permission\Contracts\HasPermissionsContract;

class Role extends Model implements RoleContract, HasPermissionsContract
{
    use RoleTrait;

    public $guarded = ['id'];
}
