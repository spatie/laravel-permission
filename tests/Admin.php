<?php

namespace Spatie\Permission\Test;

use Illuminate\Auth\Authenticatable;
use Spatie\Permission\Traits\HasGroups;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Admin extends Model implements AuthorizableContract, AuthenticatableContract
{
    use HasGroups, Authorizable, Authenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email'];

    public $timestamps = false;

    protected $table = 'admins';
}
