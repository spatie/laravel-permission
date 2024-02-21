<?php

namespace Spatie\Permission\Tests\TestModels\MultiSchemas\App2;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Customer extends Model
{
    use HasRoles;

    protected string $guard_name = 'web';
    protected $connection = 'sqlite2';
    protected $guarded = [];
    public $timestamps = false;

    public function getRoleClass(): string
    {
        return Role::class;
    }

    public function getPermissionClass(): string
    {
        return Permission::class;
    }
}
