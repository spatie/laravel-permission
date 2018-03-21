<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class RoleAlreadyExists extends InvalidArgumentException
{
    public static function create(string $roleName, string $guardName)
    {
        return new static(trans('permission::exceptions.role_already_exists-create', [
            'roleName' => $roleName,
            'guardName' => $guardName,
        ]));
    }
}
