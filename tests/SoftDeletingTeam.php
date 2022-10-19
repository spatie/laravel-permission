<?php

namespace Spatie\Permission\Test;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\TeamHasRoles;

class SoftDeletingTeam extends Model
{
    use SoftDeletes;
    use TeamHasRoles;

    public $timestamps = false;

    protected $table = 'teams';
}
