<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class RoleDoesNotExist extends InvalidArgumentException
{
    public static function named(string $roleName, ?string $guardName)
    {
        return new static("There is no role named `{$roleName}` for guard `{$guardName}`.");
    }

    /**
     * @param  int|string  $roleId
     * @return static
     */
    public static function withId($roleId, ?string $guardName)
    {
        return new static("There is no role with ID `{$roleId}` for guard `{$guardName}`.");
    }
}
