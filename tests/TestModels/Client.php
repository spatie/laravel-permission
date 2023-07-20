<?php

namespace Spatie\Permission\Tests\TestModels;

use Laravel\Passport\Client as BaseClient;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;

class Client extends BaseClient implements AuthorizableContract
{
    use HasRoles;
    use Authorizable;
}
