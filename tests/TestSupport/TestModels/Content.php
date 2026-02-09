<?php

namespace Spatie\Permission\Tests\TestSupport\TestModels;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $guarded = [];

    protected $table = 'content';
}
