<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\PermissionTrait;
use Spatie\Permission\Contracts\PermissionContract;

class Permission extends Model implements PermissionContract
{
    use PermissionTrait;

    public $guarded = ['id'];
}
