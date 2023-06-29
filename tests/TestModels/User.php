<?php

namespace Spatie\Permission\Tests\TestModels;

use Spatie\Permission\Traits\HasBlockedPermission;
use Spatie\Permission\Traits\HasRoles;

class User extends UserWithoutHasRoles
{
    use HasRoles;
    use HasBlockedPermission;
}
