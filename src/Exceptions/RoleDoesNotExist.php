<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class RoleDoesNotExist extends InvalidArgumentException
{
    public static function named(string $roleName, ?string $guardName)
    {
        return new static(__('There is no role named `:role` for guard `:guard`.', [
            'role' => $roleName,
            'guard' => $guardName,
        ]));
    }

    /**
     * @param  int|string  $roleId
     * @return static
     */
    public static function withId($roleId, ?string $guardName)
    {
        return new static(__('There is no role with ID `:id` for guard `:guard`.', [
            'id' => $roleId,
            'guard' => $guardName,
        ]));
    }
}
