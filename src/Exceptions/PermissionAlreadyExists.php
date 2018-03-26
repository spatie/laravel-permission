<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class PermissionAlreadyExists extends InvalidArgumentException
{
    public static function create(string $permissionName)
    {
        return new static("A `{$permissionName}` permission already exists.");
    }
}
