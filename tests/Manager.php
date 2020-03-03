<?php

namespace Spatie\Permission\Test;

use Illuminate\Auth\Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Manager extends Model implements AuthorizableContract, AuthenticatableContract
{
    use HasRoles, Authorizable, Authenticatable;

    protected $fillable = ['email'];

    public $timestamps = false;

    protected $table = 'users';

    // this function is added here to support the unit tests verifying it works
    // When present, it takes precedence over the $guard_name property.
    public function guardName()
    {
        return 'api';
    }

    // intentionally different property value for the sake of unit tests
    protected $guard_name = 'web';
}
