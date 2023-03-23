<?php

namespace Spatie\Permission\Tests\TestModels;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Spatie\Permission\Traits\HasRoles;

class Manager extends Model implements AuthorizableContract, AuthenticatableContract
{
    use HasRoles;
    use Authorizable;
    use Authenticatable;

    protected $fillable = ['email'];

    public $timestamps = false;

    protected $table = 'users';

    // this function is added here to support the unit tests verifying it works
    // When present, it takes precedence over the $guard_name property.
    public function guardName()
    {
        return 'jwt';
    }

    // intentionally different property value for the sake of unit tests
    protected $guard_name = 'web';
}
