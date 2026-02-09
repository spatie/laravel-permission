<?php

namespace Spatie\Permission\Tests\TestSupport\TestModels;

use Spatie\Permission\Traits\HasRoles;

class User extends UserWithoutHasRoles
{
    use HasRoles;
}
