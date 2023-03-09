<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class RoleDoesNotExist extends InvalidArgumentException
{
    public static function named(string $roleName): RoleDoesNotExist
    {
        return new static("There is no role named `{$roleName}`.");
    }

    public static function withId(int $roleId): RoleDoesNotExist
    {
        return new static("There is no role with id `{$roleId}`.");
    }
}
