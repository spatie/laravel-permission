<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class PermissionAlreadyExists extends InvalidArgumentException
{
    public static function create(string $permissionName, string $guardName): static
    {
        return new static(__('A `:permission` permission already exists for guard `:guard`.', [
            'permission' => $permissionName,
            'guard' => $guardName,
        ]));
    }
}
