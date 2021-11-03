<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class TeamsNotAllowed extends InvalidArgumentException
{
    public static function create(string $permissionName, string $guardName = '')
    {
        return new static("Teams feature not available on model.");
    }
}
