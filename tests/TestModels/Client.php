<?php

namespace Spatie\Permission\Tests\TestModels;

use Laravel\Passport\Client as BaseClient;
use Spatie\Permission\Traits\HasRoles;

class Client extends BaseClient
{
    use HasRoles;
}
