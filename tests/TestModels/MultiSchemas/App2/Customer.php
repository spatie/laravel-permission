<?php

namespace Spatie\Permission\Tests\TestModels\MultiSchemas\App2;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;

class Customer extends Model
{
    use HasRoles;

    protected string $guard_name = 'web';
    protected $connection = 'sqlite2';
    protected $guarded = [];
    public $timestamps = false;

    public static function getPermissionRegistrar(): PermissionRegistrar
    {
        return app('PermissionRegistrarApp2');
    }
}
