<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class PermissionDoesNotExist extends InvalidArgumentException
{
    public static function create(string $permissionName, ?string $guardName)
    {
        return new static("There is no permission named `{$permissionName}` for guard `{$guardName}`.");
    }

    /**
     * @param  int|string  $permissionId
     * @return static
     */
    public static function withId($permissionId, ?string $guardName)
    {
        return new static("There is no [permission] with ID `{$permissionId}` for guard `{$guardName}`.");
    }
}
