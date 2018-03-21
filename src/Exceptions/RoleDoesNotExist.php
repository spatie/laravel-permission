<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class RoleDoesNotExist extends InvalidArgumentException
{
    public static function named(string $roleName)
    {
        return new static(trans('permission::exceptions.role_does_not_exist-named', [
            'roleName' => $roleName,
        ]));
    }

    public static function withId(int $roleId)
    {
        return new static(trans('permission::exceptions.role_does_not_exist-withId', [
            'roleId' => $roleId,
        ]));
    }
}
