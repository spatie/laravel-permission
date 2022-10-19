<?php

namespace Spatie\Permission\Test;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\TeamHasRoles;

class Team extends Model
{
    use TeamHasRoles;

    public $timestamps = false;

    protected $table = 'teams';
}
