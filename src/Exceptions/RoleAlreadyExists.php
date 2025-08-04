<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class RoleAlreadyExists extends InvalidArgumentException
{
    public static function create(string $roleName, string $guardName)
    {
        return new static(__('A role `:role` already exists for guard `:guard`.', [
            'role' => $roleName,
            'guard' => $guardName,
        ]));
    }
}
