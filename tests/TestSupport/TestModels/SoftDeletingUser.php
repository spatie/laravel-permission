<?php

namespace Spatie\Permission\Tests\TestSupport\TestModels;

use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletingUser extends User
{
    use SoftDeletes;

    protected string $guard_name = 'web';
}
