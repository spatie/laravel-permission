<?php

namespace Spatie\Permission\Test;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\Restrictable;
use Spatie\Permission\Traits\RestrictableTrait;

class Department extends Model implements Restrictable
{
    use RestrictableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    public $timestamps = false;

    protected $table = 'departments';
}
